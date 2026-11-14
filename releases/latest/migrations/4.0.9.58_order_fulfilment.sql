-- MartPoint Order Fulfilment Tracking (v4.0.9.58)
-- Adds fulfilled_at to db_online_orders so paid-order fulfilment can be
-- retried safely: the unpaid->paid claim is atomic, but the fulfilment
-- steps that follow it can fail mid-flight. fulfilled_at marks completion
-- and gates self-healing retries on later verify/callback/webhook calls.
-- Idempotent. Safe to re-run.
-- --------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_online_orders' AND column_name = 'fulfilled_at');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_online_orders` ADD COLUMN `fulfilled_at` DATETIME NULL DEFAULT NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Backfill: orders already paid/completed under the old flow are
-- considered fulfilled so they are not re-processed.
SET @sql = "UPDATE `db_online_orders` SET `fulfilled_at` = `updated_at` WHERE `payment_status` = 'paid' AND `fulfilled_at` IS NULL";
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS = 1;
