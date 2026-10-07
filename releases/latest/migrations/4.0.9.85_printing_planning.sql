-- ============================================================================
-- MartPoint 4.0.9.85 — Printing: material planning, design pricing, costing
--
-- Builds on 4.0.9.84 (six-section intake). Adds:
--   * Design service on a job item, with CUSTOMER CHARGE kept separate from
--     INTERNAL DESIGN COST, and a full waiver audit (original, waived, reason,
--     approver) that never erases the internal cost.
--   * Per-item material/operation plans: inventory material, planned qty + UNIT,
--     conversion to base unit, estimated unit/total cost, wastage/setup,
--     category/product templates, and estimated design/labour/machine/outsource
--     costs. Estimates are snapshotted against the quotation version.
--   * Explicit unit handling (stock/purchase/planning units may differ, but
--     conversions must be defined) and per-material requirements.
--   * Machine consumable records (ink/toner/parts) separating physical inventory
--     movement from job-cost allocation.
--   * Spec-schema versioning + a change log, so category changes warn before
--     discarding entered specifications and history is preserved.
--
-- Idempotent. MySQL 5.7+/MariaDB compatible. Safe to run more than once.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- ----------------------------------------------------------------------------
-- Per-line design service (db_print_item_design)
-- One row per job line describing the design choice + charge/cost split.
-- design_mode: none | customer_supplied | new_design | modify_supplied | reuse_previous
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_print_item_design` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `job_id` INT(11) NOT NULL,
  `line_id` INT(11) NOT NULL,
  `design_mode` VARCHAR(24) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none' COMMENT 'none|customer_supplied|new_design|modify_supplied|reuse_previous',
  `instructions` TEXT COLLATE utf8mb4_unicode_ci NULL,
  `designer_id` INT(11) NULL COMMENT 'assigned staff / designer',
  `expected_date` DATE NULL,
  `source_artwork_id` INT(11) NULL COMMENT 'reuse_previous / modify: which artwork',
  -- customer-facing charge
  `charge_amount` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'original customer design charge',
  `charge_waived` TINYINT(1) NOT NULL DEFAULT 0,
  `waived_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `waiver_reason` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
  `waived_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci NULL,
  `waived_at` DATETIME NULL,
  -- internal cost (NEVER erased by a waiver)
  `internal_cost` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'internal design cost incl. outsourcing',
  `internal_cost_basis` VARCHAR(120) COLLATE utf8mb4_unicode_ci NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_line` (`line_id`),
  KEY `idx_job` (`job_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Material / operation plan per item (db_print_item_plans)
-- Inventory material + planned qty and UNIT + explicit conversion to base unit
-- + estimated cost. Operation rows (cutting/binding/…) carry operation costs.
-- plan_type: material | operation
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_print_item_plans` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `job_id` INT(11) NOT NULL,
  `line_id` INT(11) NOT NULL,
  `plan_type` VARCHAR(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'material' COMMENT 'material|operation',
  `item_id` INT(11) NULL COMMENT 'db_items.id for inventory materials',
  `operation_key` VARCHAR(40) COLLATE utf8mb4_unicode_ci NULL COMMENT 'cutting|trimming|binding|lamination|pressing|other',
  `description` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
  -- quantity + explicit units
  `plan_qty` DECIMAL(15,4) NOT NULL DEFAULT 0,
  `plan_unit_id` INT(11) NULL COMMENT 'unit the plan is expressed in',
  `base_qty` DECIMAL(15,4) NOT NULL DEFAULT 0 COMMENT 'plan_qty converted to the item base/stock unit',
  `base_unit_id` INT(11) NULL,
  `conversion_factor` DECIMAL(18,6) NOT NULL DEFAULT 1 COMMENT 'explicit factor applied (never assumed)',
  `conversion_ok` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0 = no known conversion; flagged for manual review',
  -- estimated cost
  `est_unit_cost` DECIMAL(15,4) NOT NULL DEFAULT 0,
  `est_total_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `wastage_pct` DECIMAL(8,3) NOT NULL DEFAULT 0 COMMENT 'setup/wastage allowance %',
  `wastage_qty` DECIMAL(15,4) NOT NULL DEFAULT 0,
  -- roll / area helpers
  `roll_width` DECIMAL(15,4) NULL,
  `roll_length` DECIMAL(15,4) NULL,
  `area_sqm` DECIMAL(15,4) NULL,
  `cost_basis` VARCHAR(40) COLLATE utf8mb4_unicode_ci NULL COMMENT 'estimated|manual|measured',
  `template_key` VARCHAR(60) COLLATE utf8mb4_unicode_ci NULL COMMENT 'which category/product template populated this',
  `quotation_version` INT(11) NOT NULL DEFAULT 1 COMMENT 'estimate is preserved against this quotation version',
  `sort_order` INT(5) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_line` (`line_id`),
  KEY `idx_job` (`job_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Estimated non-material costs per line (db_print_item_cost_estimates)
-- design / labour / machine / outsource estimates kept distinct from measured
-- actuals. Flagged so estimates never masquerade as measured costs.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_print_item_cost_estimates` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `job_id` INT(11) NOT NULL,
  `line_id` INT(11) NOT NULL,
  `cost_type` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'labour' COMMENT 'design|labour|machine|outsource|other',
  `description` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
  `estimated` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = estimated allocation, 0 = measured',
  `basis` VARCHAR(80) COLLATE utf8mb4_unicode_ci NULL COMMENT 'impressions|area|machine_reading|manual',
  `amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `created_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_line` (`line_id`),
  KEY `idx_job` (`job_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Machine consumables (db_print_machine_consumables)
-- Ink / toner / printheads / drums / blades. Physical inventory movement is
-- recorded separately; this table drives job-cost ALLOCATION so an entire toner
-- cartridge is never charged to whichever job was running at replacement time.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_print_machine_consumables` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `machine_ref` VARCHAR(80) COLLATE utf8mb4_unicode_ci NULL COMMENT 'machine name/id on the print stage',
  `item_id` INT(11) NULL COMMENT 'db_items.id if stock-tracked',
  `consumable_type` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'toner' COMMENT 'ink|toner|printhead|drum|blade|other',
  `installed_at` DATETIME NULL,
  `installed_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci NULL,
  `capacity_basis` VARCHAR(40) COLLATE utf8mb4_unicode_ci NULL COMMENT 'impressions|area|volume|weight',
  `capacity_qty` DECIMAL(15,4) NOT NULL DEFAULT 0,
  `unit_cost` DECIMAL(15,4) NOT NULL DEFAULT 0,
  `remaining_qty` DECIMAL(15,4) NOT NULL DEFAULT 0 COMMENT 'capacity left for allocation',
  `notes` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_machine` (`machine_ref`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Allocation of a consumable to a job (db_print_consumable_allocations)
CREATE TABLE IF NOT EXISTS `db_print_consumable_allocations` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `consumable_id` INT(11) NOT NULL,
  `job_id` INT(11) NOT NULL,
  `basis_qty` DECIMAL(15,4) NOT NULL DEFAULT 0 COMMENT 'impressions/area/volume used',
  `estimated` TINYINT(1) NOT NULL DEFAULT 1,
  `amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `created_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_consumable` (`consumable_id`),
  KEY `idx_job` (`job_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Spec schema version history (db_print_schema_versions)
-- Every spec-schema change is versioned; jobs keep the schema version they were
-- captured under so history renders correctly after a category schema changes.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_print_schema_versions` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `category_id` INT(11) NOT NULL,
  `category_key` VARCHAR(40) COLLATE utf8mb4_unicode_ci NULL,
  `version_no` INT(5) NOT NULL DEFAULT 1,
  `schema_json` TEXT COLLATE utf8mb4_unicode_ci NULL,
  `changed_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci NULL,
  `change_note` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cat_ver` (`category_id`,`version_no`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Spec-change log (db_print_spec_change_log)
-- Triggered when a customer-agreed specification changes after approval:
-- records original → revised, cost impact, revised deadline, and whether
-- customer reapproval + internal print reauthorization were required/obtained.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_print_spec_change_log` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `job_id` INT(11) NOT NULL,
  `line_id` INT(11) NULL,
  `change_type` VARCHAR(40) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'specification' COMMENT 'specification|material|dimension|finishing|quantity',
  `original_json` TEXT COLLATE utf8mb4_unicode_ci NULL,
  `revised_json` TEXT COLLATE utf8mb4_unicode_ci NULL,
  `cost_impact` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `original_deadline` DATE NULL,
  `revised_deadline` DATE NULL,
  `reapproval_required` TINYINT(1) NOT NULL DEFAULT 1,
  `reapproval_status` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending|approved|rejected',
  `reauthorization_status` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending|authorized|rejected',
  `reason` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
  `changed_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_job` (`job_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Per-line flags for DI calculation basis + garments supplied tracking.
-- Added to db_print_job_lines (guarded, idempotent).
-- ----------------------------------------------------------------------------
SET @col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_print_job_lines' AND column_name='schema_version');
SET @sql = IF(@col=0, 'ALTER TABLE `db_print_job_lines` ADD COLUMN `schema_version` INT(5) NOT NULL DEFAULT 1 COMMENT ''spec schema version captured with'' AFTER `spec_json`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_print_job_lines' AND column_name='customer_supplied_material');
SET @sql = IF(@col=0, 'ALTER TABLE `db_print_job_lines` ADD COLUMN `customer_supplied_material` TINYINT(1) NOT NULL DEFAULT 0 COMMENT ''1 = customer-owned stock, never deducted from inventory''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS = 1;
