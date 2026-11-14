-- ============================================================================
-- MartPoint 4.0.9.98 — Service deposit columns
--
-- db_services was created by the 4.0.1→4.0.2 migration WITHOUT the deposit
-- columns that the online-store service editor writes (deposit_required,
-- deposit_percent). db_items received them; db_services did not, so saving a
-- service from Online Store → Services failed with:
--   Unknown column 'deposit_required' in 'field list'
--
-- Idempotent: safe to run more than once. MySQL 5.7+/MariaDB compatible.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- db_services.deposit_required
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_services' AND column_name = 'deposit_required');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_services` ADD COLUMN `deposit_required` TINYINT(1) NOT NULL DEFAULT 0',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- db_services.deposit_percent
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_services' AND column_name = 'deposit_percent');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_services` ADD COLUMN `deposit_percent` DECIMAL(10,2) NOT NULL DEFAULT 0.00',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Defensive: db_items should already carry these, but installs that skipped
-- the 4.0.1→4.0.2 migration may not.
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'deposit_required');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_items` ADD COLUMN `deposit_required` TINYINT(1) NOT NULL DEFAULT 0',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'deposit_percent');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_items` ADD COLUMN `deposit_percent` DECIMAL(10,2) NOT NULL DEFAULT 0.00',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS = 1;
