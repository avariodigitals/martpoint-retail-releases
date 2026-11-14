-- ============================================================================
-- MartPoint 4.0.9.108 — Printing: machine supplies/parts issuance lifecycle,
-- maintenance visits and customer-owned material custody.
--
-- Continues 4.0.9.107 (machine register + readings + run confirmation).
--
-- REUSE, DO NOT DUPLICATE:
--   * Stock moves post through Printing_model::post_stock_moves() — the ONE
--     posting method already guarded by input_is_ledger_managed(). Each supply
--     row stores the single issue/return adjustment id it produced, and
--     installation / production logging / job allocation reference the row
--     WITHOUT deducting again.
--   * Approvals ride approval_helper + db_approval_logs (target_module,
--     target_id, target_version).
--   * Maintenance expenditure raises a db_expense row only when money actually
--     left the business; parts already issued from stores are NOT expensed
--     again and their excluded value is reported separately.
--
-- Idempotent. MySQL 5.7+/MariaDB compatible.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- ----------------------------------------------------------------------------
-- SECTION 3 — Machine consumables + replacement parts issuance lifecycle
--
--   request → approved + stock posted (ONCE) → issued → installed/loaded
--          → usage reported OR unused returned
--
-- issued_qty != installed_qty != consumed_qty. A whole toner cartridge is never
-- charged to whichever job happened to be running at replacement time:
-- allocation happens separately (allocated_cost + allocated_estimated).
--
-- opening_loaded = 1 captures consumables already in a machine at go-live, with
-- cost_known letting the shop record "cost unknown" rather than inventing one.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_print_machine_supplies` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `machine_id` INT(11) DEFAULT NULL COMMENT 'NULL only for opening_loaded rows captured before the machine is registered',
  `item_id` INT(11) DEFAULT NULL COMMENT 'db_items.id for stock-tracked consumables/parts',
  `supply_type` VARCHAR(24) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'toner'
      COMMENT 'ink|toner|printhead|drum|blade|fuser|part|other',
  `description` VARCHAR(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requested_qty` DECIMAL(15,4) NOT NULL DEFAULT 0,
  `unit_id` INT(11) DEFAULT NULL,
  `base_qty` DECIMAL(15,4) NOT NULL DEFAULT 0 COMMENT 'requested qty converted to the item base unit',
  `conversion_factor` DECIMAL(18,6) NOT NULL DEFAULT 1,
  `conversion_ok` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0 = no known conversion; flagged, never assumed',
  `issued_qty` DECIMAL(15,4) NOT NULL DEFAULT 0,
  `installed_qty` DECIMAL(15,4) NOT NULL DEFAULT 0,
  `consumed_qty` DECIMAL(15,4) NOT NULL DEFAULT 0 COMMENT 'manually reported consumption',
  `returned_qty` DECIMAL(15,4) NOT NULL DEFAULT 0 COMMENT 'unused quantity returned to stores',
  `status` VARCHAR(24) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'requested'
      COMMENT 'requested|approved|issued|partially_installed|installed|consumed|partially_returned|returned|rejected|cancelled',
  -- cost: historic, snapshotted, never rewritten by a later price change
  `unit_cost` DECIMAL(15,4) NOT NULL DEFAULT 0,
  `issued_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `consumed_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `cost_known` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0 = opening stock whose cost is explicitly unknown',
  `opening_loaded` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = captured as already loaded, not a new issue',
  -- allocation to jobs (ESTIMATED — never a whole cartridge to one job)
  `allocated_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `allocated_estimated` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = estimated allocation, 0 = measured',
  `capacity_basis` VARCHAR(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'impressions|area|volume|weight',
  `capacity_qty` DECIMAL(15,4) NOT NULL DEFAULT 0 COMMENT 'total life of this supply for allocation purposes',
  `remaining_qty` DECIMAL(15,4) NOT NULL DEFAULT 0 COMMENT 'capacity left to allocate to jobs',
  -- ledger links (each posted at most once)
  `approval_log_id` INT(11) DEFAULT NULL COMMENT 'db_approval_logs.id',
  `issue_adjustment_id` INT(11) DEFAULT NULL COMMENT 'db_stockadjustment.id — the ONE deduction',
  `return_adjustment_id` INT(11) DEFAULT NULL COMMENT 'db_stockadjustment.id — the ONE credit back',
  `reversal_adjustment_id` INT(11) DEFAULT NULL,
  -- references
  `job_id` INT(11) DEFAULT NULL,
  `stage_id` INT(11) DEFAULT NULL,
  `stage_log_id` INT(11) DEFAULT NULL,
  `warehouse_id` INT(11) DEFAULT NULL,
  `requested_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requested_at` DATETIME DEFAULT NULL,
  `approved_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_at` DATETIME DEFAULT NULL,
  `rejected_reason` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issued_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `issued_at` DATETIME DEFAULT NULL,
  `installed_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `installed_at` DATETIME DEFAULT NULL,
  `returned_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `returned_at` DATETIME DEFAULT NULL,
  `return_reason` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `note` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_machine` (`machine_id`),
  KEY `idx_status` (`status`),
  KEY `idx_job` (`job_id`),
  KEY `idx_item` (`item_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- SECTION 4 — Maintenance visits
--
-- total_cost is money the visit actually spent (labour + parts bought for it +
-- outsourced work + other). parts_issued_value records the value of parts
-- pulled from stores, which is deliberately EXCLUDED from total_cost because
-- their stock was already consumed when they were issued.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_print_machine_maintenance` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `machine_id` INT(11) NOT NULL,
  `visit_type` VARCHAR(24) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'service'
      COMMENT 'service|repair|fault|inspection|calibration|install|other',
  `performed_by_type` VARCHAR(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'internal'
      COMMENT 'internal|external',
  `technician_name` VARCHAR(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_name` VARCHAR(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_contact` VARCHAR(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `visit_date` DATE NOT NULL,
  `started_at` DATETIME DEFAULT NULL,
  `ended_at` DATETIME DEFAULT NULL,
  `downtime_minutes` INT(11) NOT NULL DEFAULT 0,
  `fault_code` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `symptom` TEXT COLLATE utf8mb4_unicode_ci,
  `work_performed` TEXT COLLATE utf8mb4_unicode_ci,
  -- expenditure
  `labour_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `parts_cost` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'parts BOUGHT for this visit, not already-issued stock',
  `outsource_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `other_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `total_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `parts_issued_value` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'value of parts already issued from stock — deliberately NOT added to total_cost',
  `expense_id` INT(11) DEFAULT NULL COMMENT 'db_expense.id when the visit raises an expense; NULL when it does not',
  -- readings at service (a visit is a legitimate reading point)
  `reading_id` INT(11) DEFAULT NULL COMMENT 'db_print_machine_readings.id recorded at service',
  `mono_at_service` DECIMAL(20,3) DEFAULT NULL,
  `colour_at_service` DECIMAL(20,3) DEFAULT NULL,
  `next_service_at` DATE DEFAULT NULL,
  `next_service_impressions` DECIMAL(20,3) DEFAULT NULL,
  `status` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'closed'
      COMMENT 'open|closed',
  `notes` TEXT COLLATE utf8mb4_unicode_ci,
  `recorded_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_machine_date` (`machine_id`,`visit_date`),
  KEY `idx_type` (`visit_type`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Which supplies/parts were installed during a maintenance visit. A supply row
-- may appear on at most one visit (uq_visit_supply) so a part cannot be
-- expensed twice by being attached to two visits.
CREATE TABLE IF NOT EXISTS `db_print_maintenance_parts` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `maintenance_id` INT(11) NOT NULL,
  `supply_id` INT(11) DEFAULT NULL COMMENT 'db_print_machine_supplies.id when the part came from stores',
  `item_id` INT(11) DEFAULT NULL,
  `description` VARCHAR(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qty` DECIMAL(15,4) NOT NULL DEFAULT 0,
  `unit_id` INT(11) DEFAULT NULL,
  `unit_cost` DECIMAL(15,4) NOT NULL DEFAULT 0,
  `total_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `from_inventory` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = already deducted by its issuance; NOT expensed again',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_visit_supply` (`maintenance_id`,`supply_id`),
  KEY `idx_maintenance` (`maintenance_id`),
  KEY `idx_supply` (`supply_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- SECTION 5 — Customer-owned material custody
--
-- A ledger SEPARATE from company inventory valuation. These goods have NO
-- company purchase cost: they must never be valued, deducted or expensed as
-- company stock. Processing cost and compensation cost still belong to the job.
--
-- Positions (each a real, reconciled holding):
--   custody       — unused, held for the customer
--   in_production — issued to production / work in progress
--   finished      — successfully processed, awaiting collection
--   damaged       — damaged or rejected
--   returned      — handed back to the customer (UNUSED material)
--   collected     — handed over as FINISHED goods
--   consumed      — genuinely consumed by production (offcut to waste)
--
-- Production ISSUANCE is not CONSUMPTION, and production COMPLETION is not
-- COLLECTION — that is exactly why these are separate columns.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_print_customer_materials` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `customer_id` INT(11) DEFAULT NULL,
  `job_id` INT(11) DEFAULT NULL COMMENT 'NULL = received before a job exists (unallocated balance)',
  `line_id` INT(11) DEFAULT NULL,
  `receipt_code` VARCHAR(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `material_name` VARCHAR(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `material_type` VARCHAR(24) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other'
      COMMENT 'garment|fabric|paper|substrate|other',
  `item_id` INT(11) DEFAULT NULL COMMENT 'link to a NON-STOCK item if the job line uses one',
  `unit_id` INT(11) DEFAULT NULL,
  `unit_label` VARCHAR(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qty_received` DECIMAL(15,3) NOT NULL DEFAULT 0,
  `condition_in` VARCHAR(24) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'good'
      COMMENT 'good|fair|damaged|mixed|unknown',
  `condition_note` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size_breakdown_json` TEXT COLLATE utf8mb4_unicode_ci COMMENT 'garments: {"S":10,"M":20,"L":5}',
  `colour_breakdown_json` TEXT COLLATE utf8mb4_unicode_ci COMMENT 'colours: {"White":30,"Navy":25}',
  `breakdown_total` DECIMAL(15,3) NOT NULL DEFAULT 0 COMMENT 'sum of the breakdowns; must reconcile to qty_received or the gap is flagged',
  `breakdown_mismatch` TINYINT(1) NOT NULL DEFAULT 0 COMMENT '1 = breakdown does not reconcile; flagged, never silently corrected',
  `received_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `received_at` DATETIME DEFAULT NULL,
  `acknowledged_by` VARCHAR(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'who signed the material in',
  `acknowledged_at` DATETIME DEFAULT NULL,
  `ack_reference` VARCHAR(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` VARCHAR(24) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open'
      COMMENT 'open|allocated|partially_processed|processed|closed|cancelled',
  -- running positions (maintained by the movement ledger, reconciled on write)
  `qty_custody` DECIMAL(15,3) NOT NULL DEFAULT 0,
  `qty_in_production` DECIMAL(15,3) NOT NULL DEFAULT 0,
  `qty_finished` DECIMAL(15,3) NOT NULL DEFAULT 0,
  `qty_damaged` DECIMAL(15,3) NOT NULL DEFAULT 0,
  `qty_returned` DECIMAL(15,3) NOT NULL DEFAULT 0,
  `qty_collected_finished` DECIMAL(15,3) NOT NULL DEFAULT 0,
  `qty_consumed` DECIMAL(15,3) NOT NULL DEFAULT 0,
  `qty_allocated_other_jobs` DECIMAL(15,3) NOT NULL DEFAULT 0 COMMENT 'moved to another job of the SAME customer',
  `notes` TEXT COLLATE utf8mb4_unicode_ci,
  `created_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_receipt_code` (`store_id`,`receipt_code`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_job` (`job_id`),
  KEY `idx_status` (`status`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Append-only custody movements. `batch_ref` groups the signed legs of ONE
-- logical movement (receive, issue, finish, damage, handover, allocate, cancel,
-- adjust) so a move is auditable as a unit and reversible by batch without
-- un-picking individual rows.
--
-- Every move carries the position it left, the position it entered, and the two
-- quantities, so reconciliation is arithmetic rather than inference.
CREATE TABLE IF NOT EXISTS `db_print_custody_moves` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `material_id` INT(11) NOT NULL COMMENT 'db_print_customer_materials.id',
  `customer_id` INT(11) DEFAULT NULL,
  `job_id` INT(11) DEFAULT NULL,
  `batch_ref` VARCHAR(60) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'groups the signed legs of one logical movement',
  `move_type` VARCHAR(24) COLLATE utf8mb4_unicode_ci NOT NULL
      COMMENT 'receive|issue|finish|damage|handover_finished|handover_unused|allocate|cancel|adjust|consume|transfer_out|transfer_in',
  `from_position` VARCHAR(24) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'NULL for the initial receipt',
  `to_position` VARCHAR(24) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `qty` DECIMAL(15,3) NOT NULL DEFAULT 0,
  `size_breakdown_json` TEXT COLLATE utf8mb4_unicode_ci COMMENT 'per-move size split when known',
  `colour_breakdown_json` TEXT COLLATE utf8mb4_unicode_ci,
  `stage_id` INT(11) DEFAULT NULL,
  `stage_log_id` INT(11) DEFAULT NULL,
  `work_date` DATE DEFAULT NULL,
  `reason` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'REQUIRED for damage, adjust, cancel',
  `authorized_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'who authorized the movement',
  `reviewed_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'authorized review of damage/adjustment/discrepancy',
  `reviewed_at` DATETIME DEFAULT NULL,
  `review_status` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'n/a'
      COMMENT 'n/a|pending|reviewed|rejected',
  -- agreed outcome for damage/discrepancy — recorded SEPARATELY from the move
  `outcome_type` VARCHAR(24) COLLATE utf8mb4_unicode_ci DEFAULT NULL
      COMMENT 'n/a|disposal|replacement|compensation|discount|no_liability|undecided',
  `outcome_note` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `outcome_cost` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'compensation/replacement cost belonging to the JOB, not to company material',
  `reverses_move_id` INT(11) DEFAULT NULL,
  `handover_to` VARCHAR(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'recipient on a handover',
  `handover_reference` VARCHAR(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `evidence_note` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recorded_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_material` (`material_id`),
  KEY `idx_batch` (`batch_ref`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_job` (`job_id`),
  KEY `idx_type` (`move_type`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Printing approval types (consumable request, machine counter reset,
-- unavailable-machine override, custody damage adjustment, management release
-- with an outstanding balance). Registered as ROWS so the existing approval
-- settings screen can switch them on per store.
-- ----------------------------------------------------------------------------
INSERT IGNORE INTO `db_approval_settings` (`store_id`)
SELECT DISTINCT `store_id` FROM `db_print_categories`;

SET FOREIGN_KEY_CHECKS = 1;
