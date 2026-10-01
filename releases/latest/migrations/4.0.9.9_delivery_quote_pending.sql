-- MartPoint Delivery Quote-Pending Migration (v4.0.9.9)
-- Adds delivery_quote_pending flag to db_online_orders so storefront orders
-- placed with a "fee on quote" shipping method record an explicit
-- quote-pending state instead of an ambiguous delivery_fee of 0.
-- Idempotent. Safe to re-run.
-- --------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_online_orders' AND column_name = 'delivery_quote_pending');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_online_orders` ADD COLUMN `delivery_quote_pending` TINYINT(1) NOT NULL DEFAULT 0 AFTER `delivery_fee`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS = 1;
