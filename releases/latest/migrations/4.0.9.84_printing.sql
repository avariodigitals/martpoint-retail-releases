-- ============================================================================
-- MartPoint 4.0.9.84 — Printing Industry Module
--
-- Configurable print-shop workflow: customer job intake with quotation
-- acceptance, real deposit/collection ledger, artwork file + immutable
-- versions, designer technical clearance, routed internal print authorization,
-- configurable category/stage production, materials issue/waste/reversal,
-- outsourcing, rework, QC, partial fulfilment, historic costing, job-level
-- referral entitlement and payout tracking.
--
-- Design invariants (do not collapse these states):
--   * customer artwork approval  (db_print_jobs.artwork_status / db_print_artworks)
--   * designer technical clearance (db_print_jobs.design_status + db_print_clearances)
--   * finance receipt verification (db_print_payments.payment_status)
--   * internal print authorization  (db_print_authorizations, version-fingerprinted)
--   * production state              (db_print_stages.status)
--   * fulfilment state              (db_print_jobs.fulfilment_status)
--   * payment state                 (db_print_jobs.payment_status derived)
--
-- Financial records ride the existing ledger: db_salespayments (customer money
-- in) + db_account_transactions (cash/bank posting). No money value is ever a
-- bare "deposit_paid" column on a row — it is always a traceable payment row.
--
-- Idempotent. MySQL 5.7+/MariaDB compatible. Safe to run more than once.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- ----------------------------------------------------------------------------
-- Print category presets (db_print_categories)
-- large_format | digital_imaging (DI) | dtf | apparel | screen_print | stationery
-- plus custom. Carries configurable dimensions/units, supplied-materials and
-- extendable spec template (JSON). This is "one workflow, many businesses".
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_print_categories` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `category_key` VARCHAR(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` VARCHAR(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `spec_schema_json` TEXT COLLATE utf8mb4_unicode_ci COMMENT 'Ordered field template for intake/spec',
  `stage_preset_json` TEXT COLLATE utf8mb4_unicode_ci COMMENT 'Ordered default stage keys for this category',
  `size_units_json` TEXT COLLATE utf8mb4_unicode_ci COMMENT 'Allowed dimension units, e.g. ["mm","cm","in","m"]',
  `supplies_material` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Category accepts customer-supplied materials',
  `sort_order` INT(5) NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `is_system` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'seeded preset (still configurable)',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cat` (`store_id`,`category_key`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Print jobs (db_print_jobs)
-- A stable job with mixed-category lines, a snapshot specification, deadline
-- and ownership. Links to the commercial parent db_custom_orders so quotation,
-- deposit and balance machinery can be reused via shared primitives.
-- Multiple distinct state columns — never one collapsed status.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_print_jobs` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `warehouse_id` INT(11) NOT NULL DEFAULT 0,
  `job_code` VARCHAR(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `custom_order_id` INT(11) DEFAULT NULL COMMENT 'commercial parent (quotation/balance)',
  `customer_id` INT(11) DEFAULT NULL,
  `title` VARCHAR(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `specifications_json` TEXT COLLATE utf8mb4_unicode_ci COMMENT 'snapshot spec at intake',
  `planned_qty` DECIMAL(15,3) NOT NULL DEFAULT 0,
  `unit_id` INT(11) DEFAULT NULL,
  `due_date` DATE DEFAULT NULL,
  `priority` VARCHAR(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  -- commercial / intake
  `quotation_status` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none' COMMENT 'none|draft|issued|accepted|converted',
  `quotation_id` INT(11) DEFAULT NULL,
  `quote_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `deposit_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `deposit_policy_json` TEXT COLLATE utf8mb4_unicode_ci COMMENT 'snapshot of configurable deposit rule at intake',
  -- distinct gates
  `artwork_status` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none' COMMENT 'none|uploaded|approved|rejected',
  `design_status` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none' COMMENT 'none|pending|cleared|rejected',
  `authorization_status` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none' COMMENT 'none|requested|authorized|rejected|invalidated',
  `payment_status` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'unpaid' COMMENT 'unpaid|partial|verified|paid',
  `production_status` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'planned' COMMENT 'planned|in_progress|on_hold|completed|cancelled',
  `fulfilment_status` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending|partially_collected|collected|delivered',
  -- costing
  `est_material_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `est_labour_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `est_outsource_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `act_material_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `act_labour_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `act_outsource_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `cost_complete` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'all postable costs recorded',
  -- referral
  `referral_id` INT(11) DEFAULT NULL,
  `referral_rule_json` TEXT COLLATE utf8mb4_unicode_ci COMMENT 'rule snapshot at attribution',
  `referral_eligible_value` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `referral_status` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none' COMMENT 'none|attributed|earned|payable|paid|recovered|cancelled',
  `referral_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  -- provenance
  `notes` TEXT COLLATE utf8mb4_unicode_ci,
  `created_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `owner_id` INT(11) DEFAULT NULL COMMENT 'assigned owner (staff)',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_job_code` (`store_id`,`job_code`),
  KEY `idx_order` (`custom_order_id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_production` (`production_status`),
  KEY `idx_due` (`due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Mixed-category job lines (db_print_job_lines)
-- One job may hold multiple category lines (e.g. large-format banner + DTF
-- shirts). Each line snapshots its category and dimensions/units/size counts.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_print_job_lines` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `job_id` INT(11) NOT NULL,
  `category_id` INT(11) DEFAULT NULL,
  `category_key` VARCHAR(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'snapshot key',
  `item_id` INT(11) DEFAULT NULL COMMENT 'material/output item link if stock-tracked',
  `description` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `spec_json` TEXT COLLATE utf8mb4_unicode_ci COMMENT 'per-line spec (dimensions, units, colors, size breakdown)',
  `qty` DECIMAL(15,3) NOT NULL DEFAULT 0,
  `unit_id` INT(11) DEFAULT NULL,
  `width` DECIMAL(15,3) DEFAULT NULL,
  `height` DECIMAL(15,3) DEFAULT NULL,
  `dim_unit` VARCHAR(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `size_breakdown_json` TEXT COLLATE utf8mb4_unicode_ci COMMENT 'apparel size counts {"S":10,"M":20}',
  `supplied_material` TINYINT(1) NOT NULL DEFAULT 0,
  `unit_price` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `line_total` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_job` (`job_id`),
  KEY `idx_category` (`category_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Artwork files (db_print_artworks) — private, versioned, immutable file link
-- plus approval/clearance state. Only the latest approved version is
-- "printable". File bytes live under uploads/ (permission-checked serving).
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_print_artworks` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `job_id` INT(11) NOT NULL,
  `version_no` INT(5) NOT NULL DEFAULT 1,
  `file_name` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_path` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_hash` CHAR(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'immutable content hash',
  `mime_type` VARCHAR(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'uploaded' COMMENT 'uploaded|approved|rejected',
  `customer_approved` TINYINT(1) NOT NULL DEFAULT 0,
  `customer_approved_at` DATETIME DEFAULT NULL,
  `note` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_job` (`job_id`),
  KEY `idx_version` (`job_id`,`version_no`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Designer technical clearance (db_print_clearances)
-- Separate gate from customer approval and from production authorization.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_print_clearances` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `job_id` INT(11) NOT NULL,
  `artwork_id` INT(11) DEFAULT NULL,
  `decision` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending|cleared|rejected',
  `reason` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cleared_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cleared_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_job` (`job_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Print authorization (db_print_authorizations)
-- Internal authorization to proceed to production. Version-fingerprinted to
-- the artwork (artwork_version) so changing artwork INVALIDATES any prior
-- authorization. Routed approver + optional backup; self-approval enforced
-- server-side. Reject/return recorded with reason; urgent override logged.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_print_authorizations` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `job_id` INT(11) NOT NULL,
  `artwork_id` INT(11) DEFAULT NULL,
  `artwork_version` INT(5) NOT NULL DEFAULT 0 COMMENT 'fingerprint — version change invalidates',
  `approver_id` INT(11) DEFAULT NULL,
  `backup_approver_id` INT(11) DEFAULT NULL,
  `status` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'requested' COMMENT 'requested|authorized|rejected|returned|invalidated',
  `reason` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `urgent_override` TINYINT(1) NOT NULL DEFAULT 0,
  `override_reason` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requested_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `decided_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `decided_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_job` (`job_id`),
  KEY `idx_status` (`status`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Configurable production stages (db_print_stages)
-- Category presets seed the plan, but stages are per-job snapshots so flag /
-- template changes cannot corrupt an in-flight job. Stage keys are configurable
-- (e.g. prepress, print, lamination, cutting, stitching, finishing, qc).
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_print_stages` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `job_id` INT(11) NOT NULL,
  `seq` INT(3) NOT NULL DEFAULT 1,
  `stage_key` VARCHAR(40) COLLATE utf8mb4_unicode_ci NOT NULL,
  `stage_label` VARCHAR(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending|in_progress|done|skipped|outsourced',
  `input_item_id` INT(11) DEFAULT NULL,
  `output_item_id` INT(11) DEFAULT NULL,
  `planned_input_qty` DECIMAL(15,3) DEFAULT NULL,
  `planned_output_qty` DECIMAL(15,3) DEFAULT NULL,
  `machine_id` INT(11) DEFAULT NULL,
  `outsourced` TINYINT(1) NOT NULL DEFAULT 0,
  `outsource_vendor` VARCHAR(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requires_artwork` TINYINT(1) NOT NULL DEFAULT 0,
  `requires_authorization` TINYINT(1) NOT NULL DEFAULT 0,
  `assigned_to` INT(11) DEFAULT NULL,
  `notes` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `started_at` DATETIME DEFAULT NULL,
  `completed_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_job` (`job_id`),
  KEY `idx_status` (`status`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Stage logs (db_print_stage_logs) — material issue, output, waste, rework,
-- partial quantities. Mirrors Nylon but adds rework + partial + outsourcing.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_print_stage_logs` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `job_id` INT(11) NOT NULL,
  `stage_id` INT(11) NOT NULL,
  `machine_id` INT(11) DEFAULT NULL,
  `operator_id` INT(11) DEFAULT NULL,
  `work_date` DATE DEFAULT NULL,
  `qty_in` DECIMAL(15,3) NOT NULL DEFAULT 0,
  `good_qty` DECIMAL(15,3) NOT NULL DEFAULT 0,
  `rework_qty` DECIMAL(15,3) NOT NULL DEFAULT 0,
  `partially_done_qty` DECIMAL(15,3) NOT NULL DEFAULT 0,
  `reject_qty` DECIMAL(15,3) NOT NULL DEFAULT 0,
  `waste_qty` DECIMAL(15,3) NOT NULL DEFAULT 0,
  `scrap_item_id` INT(11) DEFAULT NULL,
  `scrap_qty` DECIMAL(15,3) NOT NULL DEFAULT 0,
  `input_cost` DECIMAL(15,2) NOT NULL DEFAULT 0 COMMENT 'actual historic cost of consumed input',
  `outsource_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `labour_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `notes` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'submitted' COMMENT 'submitted|approved|reversed',
  `adjustment_id` INT(11) DEFAULT NULL,
  `reversal_adjustment_id` INT(11) DEFAULT NULL,
  `submitted_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_at` DATETIME DEFAULT NULL,
  `reversed_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reversed_at` DATETIME DEFAULT NULL,
  `reversal_reason` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_job` (`job_id`),
  KEY `idx_stage` (`stage_id`),
  KEY `idx_work_date` (`work_date`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Fulfilment / collection (db_print_fulfilments)
-- Partial collection supported with recipient evidence. Debits balance only
-- on recorded collection; delivery/provider reference captured.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_print_fulfilments` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `job_id` INT(11) NOT NULL,
  `qty` DECIMAL(15,3) NOT NULL DEFAULT 0,
  `kind` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'collection' COMMENT 'collection|delivery',
  `recipient_name` VARCHAR(160) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `recipient_signature_ref` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `evidence_note` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fulfilled_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fulfilled_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_job` (`job_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Payments on a print job (db_print_payments)
-- Real ledger rows. payment_kind = deposit|collection|refund|credit|reversal.
-- status = received|unverified|verified|reversed. Finance verification is a
-- distinct gate: an unverified transfer must NOT clear the deposit gate.
-- The actual money row rides db_salespayments/account ledger; this table is
-- the print-jobs allocation + verification state.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_print_payments` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `job_id` INT(11) NOT NULL,
  `payment_kind` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'deposit' COMMENT 'deposit|collection|refund|credit|reversal',
  `amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `method` VARCHAR(32) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reference` VARCHAR(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'received' COMMENT 'received|unverified|verified|reversed',
  `ledger_payment_id` INT(11) DEFAULT NULL COMMENT 'db_salespayments.id',
  `ledger_ref` VARCHAR(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verified_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `verified_at` DATETIME DEFAULT NULL,
  `note` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_job` (`job_id`),
  KEY `idx_status` (`status`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Job-level referral entitlements (db_print_referrals)
-- Rule snapshot at attribution, eligible value, earned/payable/paid states,
-- payout-once guard, refund recovery as a traceable adjustment.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_print_referrals` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `job_id` INT(11) NOT NULL,
  `partner_id` INT(11) DEFAULT NULL COMMENT 'optional db_users / partner',
  `source_ref` VARCHAR(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `rule_json` TEXT COLLATE utf8mb4_unicode_ci COMMENT 'snapshot: {rate_type, rate, eligible_basis, exclude_tax, exclude_pass_through}',
  `eligible_value` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `projected_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `earned_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `payable_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `paid_amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `status` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'attributed' COMMENT 'attributed|earned|payable|paid|recovered|cancelled',
  `payout_ref` VARCHAR(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `paid_at` DATETIME DEFAULT NULL,
  `paid_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_job` (`job_id`),
  KEY `idx_status` (`status`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Referral payout ledger (db_print_referral_entries)
-- Traceable adjustments: payout (+), recovery/refund-adjustment (-).
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_print_referral_entries` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `referral_id` INT(11) NOT NULL,
  `kind` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'payout' COMMENT 'payout|recovery|adjustment',
  `amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `note` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_referral` (`referral_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
