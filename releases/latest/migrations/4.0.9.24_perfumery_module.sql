-- ============================================================================
-- MartPoint 4.0.9.24 — Perfumery Module Schema
-- Perfume Lab: maceration tracking on formulas & blend batches, and a
-- categorized wastage ledger (evaporation, spillage, breakage, testers, QC).
-- Idempotent: safe to run more than once. MySQL 5.7+/MariaDB compatible.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- ----------------------------------------------------------------------------
-- Formula-level maceration requirement (db_recipes.maceration_days)
-- How many days a compounded blend must rest before filtering & bottling.
-- ----------------------------------------------------------------------------
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_recipes' AND column_name = 'maceration_days');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_recipes` ADD COLUMN `maceration_days` INT(11) NOT NULL DEFAULT 0 COMMENT ''Days the blend must macerate/age before bottling'' AFTER `cook_time`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ----------------------------------------------------------------------------
-- Batch-level maceration tracking (db_production_batches)
-- maceration_started: date the blend entered the maceration stage.
-- maceration_days:    aging requirement copied from the formula at start.
-- ----------------------------------------------------------------------------
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_production_batches' AND column_name = 'maceration_started');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_production_batches` ADD COLUMN `maceration_started` DATE NULL COMMENT ''Date batch entered maceration stage'' AFTER `status`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_production_batches' AND column_name = 'maceration_days');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_production_batches` ADD COLUMN `maceration_days` INT(11) NOT NULL DEFAULT 0 COMMENT ''Required maceration days, copied from formula'' AFTER `maceration_started`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ----------------------------------------------------------------------------
-- Perfumery wastage ledger (db_perfume_wastage)
-- Every physical loss event in the lab: evaporation during maceration,
-- spillage/residue during transfer & decanting, breakage, testers opened for
-- the counter, QC rejects. Each row also drives a stock adjustment so the
-- ledger and the inventory always agree.
-- ----------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_perfume_wastage` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `warehouse_id` INT(11) NOT NULL DEFAULT 0,
  `batch_id` INT(11) UNSIGNED DEFAULT NULL COMMENT 'db_production_batches id when loss belongs to a blend batch',
  `item_id` INT(11) NOT NULL COMMENT 'db_items id of the material lost',
  `item_name` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `stage` VARCHAR(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'other' COMMENT 'blending|maceration|filtering|bottling|decanting|breakage|tester|expired|other',
  `qty` DECIMAL(15,3) NOT NULL DEFAULT 0.000 COMMENT 'Quantity lost, in the item base unit',
  `unit_name` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `unit_cost` DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'Cost per unit at time of loss',
  `total_cost` DECIMAL(15,2) NOT NULL DEFAULT 0.00 COMMENT 'qty x unit_cost',
  `reason` VARCHAR(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Free-text explanation e.g. Evaporation during 4-week rest',
  `stock_adjustment_id` INT(11) DEFAULT NULL COMMENT 'db_stockadjustment id created for this loss',
  `created_date` DATE DEFAULT NULL,
  `created_time` TIME DEFAULT NULL,
  `created_by` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  KEY `idx_stage` (`stage`),
  KEY `idx_batch` (`batch_id`),
  KEY `idx_item` (`item_id`),
  KEY `idx_date` (`created_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
