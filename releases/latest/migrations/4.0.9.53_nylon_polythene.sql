-- ============================================================================
-- MartPoint 4.0.9.53 — Nylon & Polythene Manufacturing Module
-- Factory pipeline: customer job orders with artwork approval, multi-stage
-- production jobs (material allocation → extrusion → printing → cutting →
-- packing → QC), per-item nylon specs, machines, shift-level output logs and
-- job costing. Stock moves ride the existing db_stockadjustment engine so the
-- inventory ledger and audit trail stay consistent.
-- Idempotent: safe to run more than once. MySQL 5.7+/MariaDB compatible.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- ----------------------------------------------------------------------------
-- Item specs (db_nylon_item_specs)
-- One row per db_items row that participates in the nylon pipeline.
-- item_class drives how the item enters/exists in production:
--   raw_material  resin, masterbatch, ink — consumed, never produced
--   film_roll     WIP/traded roll — produced by extrusion OR purchased
--   finished_good bags/film packs — produced by the final QC-approved stage
--   consumable    packing materials, solvents (consumed, no stage output)
-- Conversion figures are per-item and optional — the system NEVER assumes a
-- universal bags-per-kg rate.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_nylon_item_specs` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `item_id` INT(11) NOT NULL,
  `item_class` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'finished_good' COMMENT 'raw_material|film_roll|finished_good|consumable',
  `material` VARCHAR(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'LDPE|HDPE|LLDPE|PP|Recycled|Other',
  `product_form` VARCHAR(30) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'bag|film_roll|sheet|tubing|liners',
  `bag_type` VARCHAR(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'vest|flat|punch-handle|zip|garbage|bread|other',
  `width_cm` DECIMAL(10,2) DEFAULT NULL,
  `length_cm` DECIMAL(10,2) DEFAULT NULL,
  `thickness_micron` DECIMAL(10,2) DEFAULT NULL,
  `colour` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `print_type` VARCHAR(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'none' COMMENT 'none|surface|flexo|gravure|custom',
  `design_ref` VARCHAR(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Customer-specific design / artwork reference',
  `kg_per_piece` DECIMAL(12,6) DEFAULT NULL COMMENT 'Explicit per-product conversion; NULL = unknown, never assumed',
  `kg_per_roll` DECIMAL(12,4) DEFAULT NULL COMMENT 'For film rolls: weight of one standard roll',
  `pieces_per_roll` DECIMAL(12,2) DEFAULT NULL COMMENT 'For converted rolls',
  `notes` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_item` (`store_id`,`item_id`),
  KEY `idx_class` (`item_class`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Machines (db_nylon_machines) — extruders, printers, cutters, sealers.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_nylon_machines` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `machine_code` VARCHAR(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `machine_name` VARCHAR(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `machine_type` VARCHAR(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other' COMMENT 'extruder|printer|cutter|sealer|puncher|packer|other',
  `notes` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  KEY `idx_type` (`machine_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Order-level nylon fields on the existing custom-orders module.
-- ----------------------------------------------------------------------------
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_custom_orders' AND column_name = 'order_qty');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_custom_orders` ADD COLUMN `order_qty` DECIMAL(15,3) DEFAULT NULL COMMENT ''Quantity in the agreed selling unit'' AFTER `specifications_json`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_custom_orders' AND column_name = 'order_unit_id');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_custom_orders` ADD COLUMN `order_unit_id` INT(11) DEFAULT NULL COMMENT ''db_units id the customer ordered in (kg/roll/piece/bundle/carton)'' AFTER `order_qty`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_custom_orders' AND column_name = 'artwork_required');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_custom_orders` ADD COLUMN `artwork_required` TINYINT(1) NOT NULL DEFAULT 0 AFTER `order_unit_id`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_custom_orders' AND column_name = 'design_ref');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_custom_orders` ADD COLUMN `design_ref` VARCHAR(120) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `artwork_required`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_custom_orders' AND column_name = 'repeat_of_id');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_custom_orders` ADD COLUMN `repeat_of_id` INT(11) DEFAULT NULL COMMENT ''custom order this repeat run was cloned from'' AFTER `design_ref`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_custom_orders' AND column_name = 'dispatched_qty');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_custom_orders` ADD COLUMN `dispatched_qty` DECIMAL(15,3) NOT NULL DEFAULT 0 COMMENT ''Delivered quantity (order selling unit) for partial dispatches'' AFTER `repeat_of_id`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ----------------------------------------------------------------------------
-- Artwork files & approval history (db_nylon_artworks)
-- Every uploaded design revision is kept; only the latest approved version is
-- "printable". A job with a printed spec cannot pass the printing stage until
-- an approved artwork exists on its linked order.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_nylon_artworks` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `custom_order_id` INT(11) NOT NULL,
  `file_name` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `file_path` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `version_no` INT(5) NOT NULL DEFAULT 1,
  `status` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending|approved|rejected',
  `note` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `uploaded_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_order` (`custom_order_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Production jobs (db_nylon_jobs)
-- Linked to a customer job order (custom_order_id) or a stock-replenishment
-- request (job_kind='stock'). planned_qty is expressed in planned_unit_id.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_nylon_jobs` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `warehouse_id` INT(11) NOT NULL DEFAULT 0,
  `job_code` VARCHAR(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `job_kind` VARCHAR(15) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'order' COMMENT 'order|stock',
  `custom_order_id` INT(11) DEFAULT NULL,
  `product_item_id` INT(11) DEFAULT NULL COMMENT 'Finished item being produced (may be a film roll)',
  `planned_qty` DECIMAL(15,3) NOT NULL DEFAULT 0,
  `planned_unit_id` INT(11) DEFAULT NULL,
  `pipeline` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Snapshot of stage keys built at creation, comma separated',
  `status` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'planned' COMMENT 'planned|in_progress|on_hold|completed|cancelled',
  `priority` VARCHAR(10) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal',
  `due_date` DATE DEFAULT NULL,
  `est_material_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `est_other_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `act_material_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `act_other_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `notes` TEXT COLLATE utf8mb4_unicode_ci,
  `created_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approved_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `completed_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_job_code` (`store_id`,`job_code`),
  KEY `idx_order` (`custom_order_id`),
  KEY `idx_status` (`status`),
  KEY `idx_due` (`due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Job stages (db_nylon_job_stages)
-- Ordered plan per job. Stage keys:
--   material_allocation|extrusion|printing|cutting|packing|qc
-- input_item_id / output_item_id move real stock; output_item_id may be NULL
-- for the material-allocation stage (allocation reserves, does not produce).
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_nylon_job_stages` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `job_id` INT(11) NOT NULL,
  `seq` INT(3) NOT NULL DEFAULT 1,
  `stage_key` VARCHAR(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending' COMMENT 'pending|in_progress|done|skipped',
  `input_item_id` INT(11) DEFAULT NULL,
  `output_item_id` INT(11) DEFAULT NULL,
  `planned_input_qty` DECIMAL(15,3) DEFAULT NULL,
  `planned_output_qty` DECIMAL(15,3) DEFAULT NULL,
  `machine_id` INT(11) DEFAULT NULL,
  `requires_artwork` TINYINT(1) NOT NULL DEFAULT 0,
  `notes` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `started_at` DATETIME DEFAULT NULL,
  `completed_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_job` (`job_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Shift-level output logs (db_nylon_job_logs)
-- Operators report what actually happened on a shift: material in, good out,
-- rejects, reusable scrap, unrecoverable waste. Non-QC logs post their stock
-- movement on submission; QC logs hold the finished-good credit until a
-- supervisor approves them. Reversals post a counter-adjustment and keep the
-- original row for the audit trail.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_nylon_job_logs` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `job_id` INT(11) NOT NULL,
  `stage_id` INT(11) NOT NULL,
  `shift_label` VARCHAR(40) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Morning|Afternoon|Night|free text',
  `machine_id` INT(11) DEFAULT NULL,
  `operator_id` INT(11) DEFAULT NULL,
  `work_date` DATE DEFAULT NULL,
  `qty_in` DECIMAL(15,3) NOT NULL DEFAULT 0 COMMENT 'Input consumed, in the input item base unit',
  `good_qty` DECIMAL(15,3) NOT NULL DEFAULT 0 COMMENT 'Saleable/WIP output, in the output item base unit',
  `reject_qty` DECIMAL(15,3) NOT NULL DEFAULT 0,
  `scrap_qty` DECIMAL(15,3) NOT NULL DEFAULT 0 COMMENT 'Reusable scrap returned to a scrap item',
  `waste_qty` DECIMAL(15,3) NOT NULL DEFAULT 0 COMMENT 'Unrecoverable waste — recorded, no stock credit',
  `scrap_item_id` INT(11) DEFAULT NULL COMMENT 'Item credited with reusable scrap',
  `unit_cost` DECIMAL(15,4) NOT NULL DEFAULT 0 COMMENT 'Input item cost per base unit at posting time',
  `material_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `notes` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'submitted' COMMENT 'submitted|approved|reversed',
  `adjustment_id` INT(11) DEFAULT NULL COMMENT 'db_stockadjustment id that moved the stock',
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
  KEY `store_id` (`store_id`),
  KEY `idx_work_date` (`work_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Job costs (db_nylon_job_costs)
-- Estimated and actual non-material costs per job (labour, machine time,
-- power, packaging, overheads). Material cost is derived from posted logs.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_nylon_job_costs` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `job_id` INT(11) NOT NULL,
  `cost_type` VARCHAR(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other' COMMENT 'labour|machine|power|packaging|overhead|other',
  `description` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estimated` TINYINT(1) NOT NULL DEFAULT 0,
  `amount` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `created_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_job` (`job_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
