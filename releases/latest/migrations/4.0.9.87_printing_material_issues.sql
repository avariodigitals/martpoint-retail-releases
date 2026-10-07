-- ============================================================================
-- MartPoint 4.0.9.87 — Printing: material issuance ledger (one posting method)
--
-- ONE consistent stock posting method for print jobs:
--   reserve (no stock move) → issue to WIP (deduct once) → consume / return.
-- This table is the authoritatative per-line material ledger so nothing deducts
-- twice at production logging or invoice conversion.
--
--   status: reserved -> issued -> partially_consumed -> consumed | returned | released
--   issued_qty   = deducted from stock when issued to WIP
--   consumed_qty = portion of the issued qty actually used
--   returned_qty = unused portion returned to stock (counter-posting)
--   wastage_qty  = recorded waste (no stock credit)
-- Every posting records its db_stockadjustment id so edits/reversals reconcile.
--
-- Idempotent. MySQL 5.7+/MariaDB compatible.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

CREATE TABLE IF NOT EXISTS `db_print_material_issues` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `job_id` INT(11) NOT NULL,
  `line_id` INT(11) NOT NULL,
  `plan_id` INT(11) NULL COMMENT 'db_print_item_plans row this issues for',
  `item_id` INT(11) NULL,
  `warehouse_id` INT(11) NULL,
  `status` VARCHAR(24) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'reserved' COMMENT 'reserved|issued|partially_consumed|consumed|returned|released',
  -- quantities in the item BASE unit (already converted via to_base_qty)
  `planned_qty` DECIMAL(15,4) NOT NULL DEFAULT 0 COMMENT 'planned requirement incl. wastage',
  `reserved_qty` DECIMAL(15,4) NOT NULL DEFAULT 0 COMMENT 'soft reservation (no stock move)',
  `issued_qty` DECIMAL(15,4) NOT NULL DEFAULT 0 COMMENT 'deducted from stock at issue',
  `consumed_qty` DECIMAL(15,4) NOT NULL DEFAULT 0,
  `returned_qty` DECIMAL(15,4) NOT NULL DEFAULT 0,
  `wastage_qty` DECIMAL(15,4) NOT NULL DEFAULT 0,
  -- historic cost captured at issue time (never rewritten by price changes)
  `unit_cost` DECIMAL(15,4) NOT NULL DEFAULT 0,
  `issued_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
  `consumed_cost` DECIMAL(15,2) NOT NULL DEFAULT 0,
  -- ledger links
  `reserve_ref` VARCHAR(80) COLLATE utf8mb4_unicode_ci NULL,
  `issue_adjustment_id` INT(11) NULL COMMENT 'db_stockadjustment id for the issue (deduction)',
  `consume_adjustment_id` INT(11) NULL COMMENT 'WIP consumption (no stock move; kept for trace)',
  `return_adjustment_id` INT(11) NULL COMMENT 'db_stockadjustment id for the return (credit back)',
  `reversal_adjustment_id` INT(11) NULL,
  `issued_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci NULL,
  `issued_at` DATETIME NULL,
  `consumed_by` VARCHAR(60) COLLATE utf8mb4_unicode_ci NULL,
  `consumed_at` DATETIME NULL,
  `note` VARCHAR(255) COLLATE utf8mb4_unicode_ci NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_job` (`job_id`),
  KEY `idx_line` (`line_id`),
  KEY `idx_item` (`item_id`),
  KEY `idx_status` (`status`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Category calculation templates: configurable per category how quantities are
-- derived (ream/pack, roll→area, DI imposition, garment size reconcile).
CREATE TABLE IF NOT EXISTS `db_print_category_calcs` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `category_id` INT(11) NOT NULL,
  `calc_key` VARCHAR(40) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'sheet|roll_area|imposition|garment_sizes|none',
  `config_json` TEXT COLLATE utf8mb4_unicode_ci NULL COMMENT 'e.g. {sheets_per_pack:500,roll_width_cm:106,ups_per_sheet:4}',
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_cat` (`store_id`,`category_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
