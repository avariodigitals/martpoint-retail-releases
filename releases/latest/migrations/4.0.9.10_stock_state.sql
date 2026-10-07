-- MartPoint Order Stock Lifecycle Migration (v4.0.9.10)
-- Adds explicit stock state tracking to db_online_orders:
--   stock_state: none -> reserved -> committed | released
--   stock_reserved_at: when the reservation was taken (expiry sweeps use it)
-- Replaces reliance on the boolean stock_adjusted flag alone.
-- Idempotent. Safe to re-run.
-- --------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_online_orders' AND column_name = 'stock_state');
SET @sql = IF(@col_exists = 0,
  "ALTER TABLE `db_online_orders` ADD COLUMN `stock_state` ENUM('none','reserved','committed','released') NOT NULL DEFAULT 'none'",
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_online_orders' AND column_name = 'stock_reserved_at');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_online_orders` ADD COLUMN `stock_reserved_at` DATETIME NULL DEFAULT NULL AFTER `stock_state`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Backfill: orders that already consumed stock under the boolean flag
-- are treated as committed (they were decremented by the old flow).
SET @flag_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_online_orders' AND column_name = 'stock_adjusted');
SET @sql = IF(@flag_exists = 1,
  "UPDATE `db_online_orders` SET `stock_state` = 'committed' WHERE `stock_adjusted` = 1 AND `stock_state` = 'none'",
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS = 1;
