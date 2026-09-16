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

-- ----------------------------------------------------------------------------
-- Migration 4: SKU Limit + Online Product Limit (v4.0.9.3)
-- Source: updates/migrations/4.0.9.3_sku_limit.sql
-- ----------------------------------------------------------------------------

-- db_subscription_license: sku_limit
SET @col_exists = (SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_subscription_license' AND column_name = 'sku_limit');
SET @sql = IF(@col_exists IS NULL, 'ALTER TABLE `db_subscription_license` ADD COLUMN `sku_limit` int(11) DEFAULT 10000 AFTER `product_limit`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- db_subscription_license: override_sku_limit
SET @col_exists = (SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_subscription_license' AND column_name = 'override_sku_limit');
SET @sql = IF(@col_exists IS NULL, 'ALTER TABLE `db_subscription_license` ADD COLUMN `override_sku_limit` int(11) DEFAULT NULL AFTER `override_product_limit`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- db_subscription_license: online_product_limit
SET @col_exists = (SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_subscription_license' AND column_name = 'online_product_limit');
SET @sql = IF(@col_exists IS NULL, 'ALTER TABLE `db_subscription_license` ADD COLUMN `online_product_limit` int(11) DEFAULT 500 AFTER `sku_limit`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- db_subscription_license: override_online_product_limit
SET @col_exists = (SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_subscription_license' AND column_name = 'override_online_product_limit');
SET @sql = IF(@col_exists IS NULL, 'ALTER TABLE `db_subscription_license` ADD COLUMN `override_online_product_limit` int(11) DEFAULT NULL AFTER `override_sku_limit`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- db_subscription_plans: sku_limit
SET @col_exists = (SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_subscription_plans' AND column_name = 'sku_limit');
SET @sql = IF(@col_exists IS NULL, 'ALTER TABLE `db_subscription_plans` ADD COLUMN `sku_limit` int(11) NOT NULL DEFAULT 10000 AFTER `product_limit`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- db_subscription_plans: online_product_limit
SET @col_exists = (SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_subscription_plans' AND column_name = 'online_product_limit');
SET @sql = IF(@col_exists IS NULL, 'ALTER TABLE `db_subscription_plans` ADD COLUMN `online_product_limit` int(11) NOT NULL DEFAULT 500 AFTER `sku_limit`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Backfill sku_limit and online_product_limit on existing plans
UPDATE `db_subscription_plans` SET `sku_limit` = 10000,   `online_product_limit` = 500,   `user_limit` = 5  WHERE `plan_code` = 'basic';
UPDATE `db_subscription_plans` SET `sku_limit` = 50000,   `online_product_limit` = 2000  WHERE `product_limit` > 500  AND `product_limit` <= 2000;
UPDATE `db_subscription_plans` SET `sku_limit` = 150000,  `online_product_limit` = 5000  WHERE `product_limit` > 2000 AND `product_limit` <= 5000;
UPDATE `db_subscription_plans` SET `sku_limit` = 500000,  `online_product_limit` = 10000 WHERE `product_limit` > 5000;

-- Change publish_online default from 1 to 0
ALTER TABLE `db_items` MODIFY COLUMN `publish_online` tinyint(1) NOT NULL DEFAULT 0;

-- ----------------------------------------------------------------------------
-- Migration 5: Default flags for Tax, Units, Brands (v4.0.9.3)
-- Source: application/migrations/009 and 010
-- ----------------------------------------------------------------------------

-- db_tax: is_default
SET @col_exists = (SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_tax' AND column_name = 'is_default');
SET @sql = IF(@col_exists IS NULL, 'ALTER TABLE `db_tax` ADD COLUMN `is_default` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- db_units: is_default
SET @col_exists = (SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_units' AND column_name = 'is_default');
SET @sql = IF(@col_exists IS NULL, 'ALTER TABLE `db_units` ADD COLUMN `is_default` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- db_brands: is_default
SET @col_exists = (SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_brands' AND column_name = 'is_default');
SET @sql = IF(@col_exists IS NULL, 'ALTER TABLE `db_brands` ADD COLUMN `is_default` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================================
-- End of v4.0.9 database update
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Migration 6: New Arrival flag + Online Excluded flag (v4.0.9)
-- Source: updates/migrations/4.0.9_new_arrival_products.sql + 4.0.9_online_excluded.sql
-- ----------------------------------------------------------------------------

-- db_items: is_new_arrival (manual storefront "New Arrival" badge, mirrors is_featured)
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'is_new_arrival');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_items` ADD COLUMN `is_new_arrival` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_featured`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- db_items: online_excluded (prevents "Sync All" from re-publishing manually unpublished products)
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'online_excluded');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_items` ADD COLUMN `online_excluded` TINYINT(1) NOT NULL DEFAULT 0 AFTER `publish_online`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================================
-- End of v4.0.9 database update
-- ============================================================================

-- ----------------------------------------------------------------------------
-- Migration 7: Multi-Unit Selling (v4.0.9.6)
-- Source: updates/migrations/4.0.9.5_to_4.0.9.6_multi_unit_selling.sql
-- ----------------------------------------------------------------------------

-- db_units: shortcode for receipts and POS labels
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_units' AND column_name = 'shortcode');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_units` ADD COLUMN `shortcode` VARCHAR(20) NULL DEFAULT NULL AFTER `unit_name`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- db_customers: customer_type (Retail/Wholesale/Distributor)
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_customers' AND column_name = 'customer_type');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_customers` ADD COLUMN `customer_type` VARCHAR(50) NOT NULL DEFAULT ''Retail'' AFTER `customer_name`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- db_item_selling_units: per-product selling units with conversion
CREATE TABLE IF NOT EXISTS `db_item_selling_units` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `store_id` INT(11) NOT NULL,
  `item_id` INT(11) NOT NULL,
  `unit_id` INT(11) NOT NULL,
  `unit_shortcode` VARCHAR(20) DEFAULT NULL,
  `conversion_factor` DECIMAL(15,6) NOT NULL DEFAULT 1.000000,
  `selling_price` DOUBLE(20,4) DEFAULT 0.0000,
  `purchase_price` DOUBLE(20,4) DEFAULT NULL,
  `sku` VARCHAR(50) DEFAULT NULL,
  `barcode` VARCHAR(100) DEFAULT NULL,
  `is_default` TINYINT(1) NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  KEY `item_id` (`item_id`),
  KEY `unit_id` (`unit_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- db_salesitems: unit actually sold + base quantity converted for stock
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_salesitems' AND column_name = 'unit_id');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_salesitems` ADD COLUMN `unit_id` INT(11) NULL DEFAULT NULL AFTER `item_id`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_salesitems' AND column_name = 'unit_name');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_salesitems` ADD COLUMN `unit_name` VARCHAR(50) NULL DEFAULT NULL AFTER `unit_id`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_salesitems' AND column_name = 'conversion_factor');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_salesitems` ADD COLUMN `conversion_factor` DECIMAL(15,6) NULL DEFAULT NULL AFTER `unit_name`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_salesitems' AND column_name = 'base_unit_qty');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_salesitems` ADD COLUMN `base_unit_qty` DOUBLE(20,4) NULL DEFAULT NULL AFTER `sales_qty`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- db_purchaseitems: unit actually purchased + base quantity converted for stock
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_purchaseitems' AND column_name = 'unit_id');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_purchaseitems` ADD COLUMN `unit_id` INT(11) NULL DEFAULT NULL AFTER `item_id`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_purchaseitems' AND column_name = 'unit_name');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_purchaseitems` ADD COLUMN `unit_name` VARCHAR(50) NULL DEFAULT NULL AFTER `unit_id`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_purchaseitems' AND column_name = 'conversion_factor');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_purchaseitems` ADD COLUMN `conversion_factor` DECIMAL(15,6) NULL DEFAULT NULL AFTER `unit_name`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_purchaseitems' AND column_name = 'base_unit_qty');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_purchaseitems` ADD COLUMN `base_unit_qty` DOUBLE(20,4) NULL DEFAULT NULL AFTER `purchase_qty`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ----------------------------------------------------------------------------
-- Migration 8: Multi-Unit Selling for Variants (v4.0.9.7)
-- Source: updates/migrations/4.0.9.6_to_4.0.9.7_multi_unit_variants.sql
-- ----------------------------------------------------------------------------

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_item_selling_units' AND column_name = 'is_template');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_item_selling_units` ADD COLUMN `is_template` TINYINT(1) NOT NULL DEFAULT 0 AFTER `is_default`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

UPDATE `db_item_selling_units` SET `is_template` = 0 WHERE `is_template` IS NULL;

SET @idx_exists = (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'db_item_selling_units' AND index_name = 'idx_is_template');
SET @sql = IF(@idx_exists = 0,
  'ALTER TABLE `db_item_selling_units` ADD KEY `idx_is_template` (`is_template`)',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ----------------------------------------------------------------------------
-- Migration 9: Multi-Unit Wholesale Prices (v4.0.9.8)
-- Source: updates/migrations/4.0.9.7_to_4.0.9.8_multi_unit_wholesale.sql
-- ----------------------------------------------------------------------------

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_item_selling_units' AND column_name = 'wholesale_price');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_item_selling_units` ADD COLUMN `wholesale_price` DOUBLE(20,4) DEFAULT NULL AFTER `selling_price`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_salesitemsreturn' AND column_name = 'base_unit_qty');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_salesitemsreturn` ADD COLUMN `base_unit_qty` DOUBLE(20,4) NULL DEFAULT NULL AFTER `return_qty`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_purchaseitemsreturn' AND column_name = 'base_unit_qty');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_purchaseitemsreturn` ADD COLUMN `base_unit_qty` DOUBLE(20,4) NULL DEFAULT NULL AFTER `return_qty`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ----------------------------------------------------------------------------
-- Migration 10: Newsletter Subscribers (v4.0.9.9)
-- Source: updates/migrations/4.0.9.8_newsletter_subscribers.sql
-- ----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `db_newsletter_subscribers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `store_id` INT NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `source` VARCHAR(50) NOT NULL DEFAULT 'newsletter',
  `ip_address` VARCHAR(45) DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL,
  UNIQUE KEY `uk_store_email` (`store_id`, `email`),
  KEY `idx_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ----------------------------------------------------------------------------
-- Migration 11: B2B Customer/Warehouse fields
-- Source: updates/migrations/4.0.6_b2b_customer_warehouse.sql
-- ----------------------------------------------------------------------------

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_customers' AND column_name = 'payment_terms_days');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_customers` ADD COLUMN `payment_terms_days` INT(5) NULL DEFAULT 0 AFTER `credit_limit`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_warehouse' AND column_name = 'branch_type');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_warehouse` ADD COLUMN `branch_type` VARCHAR(50) NULL DEFAULT ''branch'' AFTER `warehouse_name`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_warehouse' AND column_name = 'is_distribution_center');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_warehouse` ADD COLUMN `is_distribution_center` TINYINT(1) NOT NULL DEFAULT 0 AFTER `branch_type`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ============================================================================
-- End of v4.0.9 database update
-- ============================================================================
