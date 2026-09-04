-- ============================================================================
-- MartPoint v4.0.9 - Database Update Script (4.0.8 -> 4.0.9)
-- ============================================================================
-- Run this SINGLE file in phpMyAdmin or MySQL to apply all v4.0.9 migrations.
--
-- USAGE:
--   mysql -u USER -p DBNAME < martpoint-4.0.9-database.sql
--   OR open phpMyAdmin -> SQL tab -> paste this file -> Go
--
-- Safe to run multiple times. Uses information_schema checks instead of
-- ADD COLUMN IF NOT EXISTS so it works on MySQL 5.5/5.7 and MariaDB.
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Migration 1: Compatibility Layer
-- Adds columns that may be missing on older databases running v4.0.8+ code.
-- Source: updates/migrations/4.0.8_to_4.0.9_compatibility.sql
-- ----------------------------------------------------------------------------

-- db_salespayments: columns added after v4.0.2
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_salespayments' AND column_name = 'payment_reference');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_salespayments` ADD COLUMN `payment_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_salespayments' AND column_name = 'confirmation_status');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_salespayments` ADD COLUMN `confirmation_status` tinyint(1) DEFAULT 1 COMMENT ''0=Pending, 1=Confirmed''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_salespayments' AND column_name = 'payment_mode_id');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_salespayments` ADD COLUMN `payment_mode_id` int(11) DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_salespayments' AND column_name = 'advance_adjusted');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_salespayments` ADD COLUMN `advance_adjusted` double(20,4) DEFAULT 0.0000', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_salespayments' AND column_name = 'cheque_number');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_salespayments` ADD COLUMN `cheque_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_salespayments' AND column_name = 'cheque_period');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_salespayments` ADD COLUMN `cheque_period` int(10) DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_salespayments' AND column_name = 'cheque_status');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_salespayments` ADD COLUMN `cheque_status` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- db_salesitems: columns added for barcode/serial and staff commission
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_salesitems' AND column_name = 'barcode_id');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_salesitems` ADD COLUMN `barcode_id` int(11) DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_salesitems' AND column_name = 'sold_serial_number');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_salesitems` ADD COLUMN `sold_serial_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_salesitems' AND column_name = 'sold_imei_number');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_salesitems` ADD COLUMN `sold_imei_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_salesitems' AND column_name = 'price_type');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_salesitems` ADD COLUMN `price_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT ''wholesale''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_salesitems' AND column_name = 'staff_id');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_salesitems` ADD COLUMN `staff_id` int(10) DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_salesitems' AND column_name = 'commission_amount');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_salesitems` ADD COLUMN `commission_amount` double(20,2) DEFAULT 0.00', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- db_holditems: make sure barcode/serial/price/staff columns exist
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_holditems' AND column_name = 'barcode_id');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_holditems` ADD COLUMN `barcode_id` int(11) DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_holditems' AND column_name = 'sold_serial_number');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_holditems` ADD COLUMN `sold_serial_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_holditems' AND column_name = 'sold_imei_number');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_holditems` ADD COLUMN `sold_imei_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_holditems' AND column_name = 'price_type');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_holditems` ADD COLUMN `price_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT ''wholesale''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_holditems' AND column_name = 'staff_id');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_holditems` ADD COLUMN `staff_id` int(10) DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_holditems' AND column_name = 'commission_amount');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_holditems` ADD COLUMN `commission_amount` double(20,2) DEFAULT 0.00', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- db_sitesettings: add sales_target column for dashboard daily target
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_sitesettings' AND column_name = 'sales_target');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_sitesettings` ADD COLUMN `sales_target` DOUBLE(20,4) DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ----------------------------------------------------------------------------
-- Migration 2: Staff Commission Tracking for Held Sale Items
-- Source: updates/migrations/4.0.8_to_4.0.9_holditems_staff_commission.sql
-- ----------------------------------------------------------------------------

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_holditems' AND column_name = 'staff_id');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_holditems` ADD COLUMN `staff_id` int(10) DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_holditems' AND column_name = 'commission_amount');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_holditems` ADD COLUMN `commission_amount` double(20,2) DEFAULT 0.00', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ----------------------------------------------------------------------------
-- ----------------------------------------------------------------------------
-- Migration 3: Promotions Advanced Features + Loyalty Referral Program
-- Source: updates/migrations/4.0.9_to_4.0.9.2_promotions_loyalty.sql
-- ----------------------------------------------------------------------------

-- db_promotions: advanced mode, min spend, usage limits
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_promotions' AND column_name = 'mode');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_promotions` ADD COLUMN `mode` VARCHAR(10) NOT NULL DEFAULT ''simple'' COMMENT ''simple or advanced''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_promotions' AND column_name = 'min_spend');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_promotions` ADD COLUMN `min_spend` DECIMAL(20,2) NULL DEFAULT NULL COMMENT ''Minimum cart total for code-based promo''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_promotions' AND column_name = 'usage_limit_per_customer');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_promotions` ADD COLUMN `usage_limit_per_customer` INT NULL DEFAULT NULL COMMENT ''Max uses per customer, NULL=unlimited''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_promotions' AND column_name = 'usage_limit_total');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_promotions` ADD COLUMN `usage_limit_total` INT NULL DEFAULT NULL COMMENT ''Max total uses, NULL=unlimited''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- db_promotion_usage: track per-customer promotion usage
CREATE TABLE IF NOT EXISTS `db_promotion_usage` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `promotion_id` INT NOT NULL,
  `customer_id` INT NOT NULL,
  `sales_id` INT NOT NULL,
  `store_id` INT NOT NULL,
  `used_date` DATE DEFAULT NULL,
  `used_time` VARCHAR(50) DEFAULT NULL,
  KEY `promotion_id` (`promotion_id`),
  KEY `customer_id` (`customer_id`),
  KEY `sales_id` (`sales_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- db_loyalty_settings: referral program columns
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_loyalty_settings' AND column_name = 'referral_enabled');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_loyalty_settings` ADD COLUMN `referral_enabled` TINYINT(1) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_loyalty_settings' AND column_name = 'referrer_reward_type');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_loyalty_settings` ADD COLUMN `referrer_reward_type` ENUM(''points'',''credit'',''discount'') NOT NULL DEFAULT ''points''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_loyalty_settings' AND column_name = 'referrer_reward_value');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_loyalty_settings` ADD COLUMN `referrer_reward_value` DECIMAL(15,2) NOT NULL DEFAULT 100.00', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_loyalty_settings' AND column_name = 'new_customer_reward_type');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_loyalty_settings` ADD COLUMN `new_customer_reward_type` ENUM(''points'',''credit'',''discount'') NOT NULL DEFAULT ''points''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_loyalty_settings' AND column_name = 'new_customer_reward_value');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_loyalty_settings` ADD COLUMN `new_customer_reward_value` DECIMAL(15,2) NOT NULL DEFAULT 50.00', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_loyalty_settings' AND column_name = 'referral_approval_required');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_loyalty_settings` ADD COLUMN `referral_approval_required` TINYINT(1) NOT NULL DEFAULT 1', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ----------------------------------------------------------------------------
-- Verification (optional - run to confirm)
-- ----------------------------------------------------------------------------

-- SHOW COLUMNS FROM db_holditems LIKE 'commission_amount';
-- SHOW COLUMNS FROM db_salesitems LIKE 'commission_amount';
-- SHOW COLUMNS FROM db_salespayments LIKE 'payment_mode_id';
-- SHOW COLUMNS FROM db_promotions LIKE 'mode';
-- SHOW COLUMNS FROM db_loyalty_settings LIKE 'referral_enabled';

-- ============================================================================
-- End of v4.0.9 database update
-- ============================================================================
