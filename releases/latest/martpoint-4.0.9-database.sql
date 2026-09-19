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

-- ----------------------------------------------------------------------------
-- Migration 12: Leads / CRM (v4.0.9.11)
-- Source: updates/migrations/4.0.9.11_leads_crm.sql
-- ----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `db_leads` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) UNSIGNED NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `phone` VARCHAR(30) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `source` VARCHAR(50) NOT NULL DEFAULT 'manual',
  `status` VARCHAR(20) NOT NULL DEFAULT 'new',
  `interest` VARCHAR(255) DEFAULT NULL,
  `notes` TEXT,
  `assigned_to` INT(11) UNSIGNED DEFAULT NULL,
  `converted_customer_id` INT(11) UNSIGNED DEFAULT NULL,
  `converted_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store_status` (`store_id`,`status`),
  KEY `idx_converted_customer` (`converted_customer_id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Migration 13: Industry Modules (v4.0.9.12)
-- Source: updates/migrations/4.0.9.12_industry_modules.sql
-- ----------------------------------------------------------------------------
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- ----------------------------------------------------------------------------
-- Kitchen / table ordering (CI 011 + 012)
-- ----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `db_kitchen_orders` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `sales_id` INT NOT NULL,
  `store_id` INT NOT NULL,
  `kds_status` ENUM('new','preparing','ready','served') NOT NULL DEFAULT 'new',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_sales` (`sales_id`),
  KEY `idx_store_status` (`store_id`,`kds_status`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_sales' AND column_name = 'table_id');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_sales` ADD COLUMN `table_id` INT(11) NOT NULL DEFAULT 0 COMMENT ''Restaurant table id (0 = no table)'' AFTER `customer_id`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_kitchen_orders' AND column_name = 'table_id');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_kitchen_orders` ADD COLUMN `table_id` INT(11) NOT NULL DEFAULT 0 COMMENT ''Restaurant table id copied from sale'' AFTER `sales_id`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_kitchen_orders' AND column_name = 'online_order_id');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_kitchen_orders` ADD COLUMN `online_order_id` INT(11) NOT NULL DEFAULT 0 COMMENT ''Linked db_online_orders.id for table QR orders'' AFTER `sales_id`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ----------------------------------------------------------------------------
-- Production / recipe fields on items (CI 013 + 014)
-- ----------------------------------------------------------------------------

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'deplete_recipe_on_sale');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_items` ADD COLUMN `deplete_recipe_on_sale` TINYINT(1) NOT NULL DEFAULT 0 COMMENT ''Deduct recipe ingredients directly at POS sale'' AFTER `recipe_margin_pct`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'item_production_mode');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_items` ADD COLUMN `item_production_mode` VARCHAR(20) NOT NULL DEFAULT ''batch'' COMMENT ''batch | sale_deplete | component'' AFTER `recipe_margin_pct`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ----------------------------------------------------------------------------
-- Butchery / frozen item fields on db_items (CI 015)
-- ----------------------------------------------------------------------------

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'carcass_template_id');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_items` ADD COLUMN `carcass_template_id` INT(11) NOT NULL DEFAULT 0 COMMENT ''Link to db_item_carcass_templates''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'is_carcass');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_items` ADD COLUMN `is_carcass` TINYINT(1) NOT NULL DEFAULT 0 COMMENT ''Whole carcass before cutting''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'portion_of_item_id');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_items` ADD COLUMN `portion_of_item_id` INT(11) NOT NULL DEFAULT 0 COMMENT ''Parent item this cut was produced from''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'storage_temp_min');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_items` ADD COLUMN `storage_temp_min` DECIMAL(5,2) DEFAULT NULL COMMENT ''Min storage temp C''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'storage_temp_max');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_items` ADD COLUMN `storage_temp_max` DECIMAL(5,2) DEFAULT NULL COMMENT ''Max storage temp C''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'thaw_time_hours');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_items` ADD COLUMN `thaw_time_hours` INT(11) NOT NULL DEFAULT 0 COMMENT ''Hours required to thaw safely''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'use_within_hours_after_thaw');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_items` ADD COLUMN `use_within_hours_after_thaw` INT(11) NOT NULL DEFAULT 0 COMMENT ''Shelf life in hours after thawing''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'frozen_shelf_life_days');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_items` ADD COLUMN `frozen_shelf_life_days` INT(11) NOT NULL DEFAULT 0 COMMENT ''Shelf life in days while frozen''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ----------------------------------------------------------------------------
-- Butchery / frozen tables (CI 016)
-- ----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `db_item_carcass_templates` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `template_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `template_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `source_item_id` int(11) NOT NULL DEFAULT 0 COMMENT 'Parent carcass item',
  `expected_total_weight` decimal(10,2) NOT NULL DEFAULT 0.00,
  `expected_total_yield_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_date` date DEFAULT NULL,
  `created_time` time DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  KEY `source_item_id` (`source_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_item_carcass_template_cuts` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `template_id` int(11) UNSIGNED NOT NULL,
  `cut_item_id` int(11) NOT NULL DEFAULT 0,
  `cut_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expected_qty` decimal(10,2) NOT NULL DEFAULT 1.00,
  `expected_weight_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `expected_weight_kg` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `template_id` (`template_id`),
  KEY `cut_item_id` (`cut_item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_carcass_shares` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `carcass_item_id` int(11) NOT NULL DEFAULT 0,
  `batch_id` int(11) UNSIGNED DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `share_fraction` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1/4' COMMENT '1/4, 1/8, custom',
  `reserved_weight` decimal(10,2) NOT NULL DEFAULT 0.00,
  `reserved_amount` decimal(18,2) NOT NULL DEFAULT 0.00,
  `deposit_amount` decimal(18,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'reserved' COMMENT 'reserved|paid|cut|delivered|cancelled',
  `created_date` date DEFAULT NULL,
  `created_time` time DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  KEY `carcass_item_id` (`carcass_item_id`),
  KEY `customer_id` (`customer_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_freezer_locations` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `location_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `temp_min` decimal(5,2) DEFAULT NULL,
  `temp_max` decimal(5,2) DEFAULT NULL,
  `capacity_volume` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_date` date DEFAULT NULL,
  `created_time` time DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_received_carcasses` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `lot_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `supplier_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `carcass_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `receiving_weight` decimal(10,2) NOT NULL DEFAULT 0.00,
  `slaughter_date` date DEFAULT NULL,
  `expected_yield_pct` decimal(5,2) NOT NULL DEFAULT 0.00,
  `freezer_location_id` int(11) UNSIGNED DEFAULT NULL,
  `carcass_template_id` int(11) UNSIGNED DEFAULT NULL,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'received' COMMENT 'received|cutting|completed|written_off',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `created_date` date DEFAULT NULL,
  `created_time` time DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  KEY `status` (`status`),
  KEY `carcass_template_id` (`carcass_template_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_carcass_cut_records` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `received_carcass_id` int(11) UNSIGNED NOT NULL,
  `cut_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expected_weight` decimal(10,2) NOT NULL DEFAULT 0.00,
  `actual_weight` decimal(10,2) NOT NULL DEFAULT 0.00,
  `packs` int(11) NOT NULL DEFAULT 1,
  `waste` decimal(10,2) NOT NULL DEFAULT 0.00,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `received_carcass_id` (`received_carcass_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_temperature_logs` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `freezer_location_id` int(11) UNSIGNED NOT NULL,
  `recorded_at` datetime NOT NULL,
  `temperature_c` decimal(5,2) NOT NULL,
  `recorded_by` int(11) DEFAULT NULL,
  `notes` text COLLATE utf8mb4_unicode_ci,
  `alert_sent` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'normal' COMMENT 'normal|warning|critical',
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  KEY `freezer_location_id` (`freezer_location_id`),
  KEY `recorded_at` (`recorded_at`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Vehicle module (CI 017 + 019 + 020 + 024)
-- ----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `db_vehicles` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `vehicle_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `make` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `model` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `year` int(4) DEFAULT NULL,
  `color` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mileage` int(11) DEFAULT NULL COMMENT 'Kilometers',
  `fuel_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `transmission` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vehicle_condition` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'used' COMMENT 'new|used',
  `vin` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `license_plate` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `price` decimal(18,2) NOT NULL DEFAULT 0.00,
  `cost` decimal(18,2) NOT NULL DEFAULT 0.00,
  `status` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'available' COMMENT 'available|reserved|sold',
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `image_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `created_date` date DEFAULT NULL,
  `created_time` time DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  KEY `status` (`status`),
  KEY `customer_id` (`customer_id`),
  KEY `make_model` (`make`,`model`),
  KEY `vehicle_code` (`vehicle_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payment fields (CI 019)
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_vehicles' AND column_name = 'sold_date');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_vehicles` ADD COLUMN `sold_date` datetime DEFAULT NULL AFTER `customer_name`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_vehicles' AND column_name = 'amount_paid');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_vehicles` ADD COLUMN `amount_paid` decimal(18,2) NOT NULL DEFAULT 0.00 AFTER `sold_date`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_vehicles' AND column_name = 'payment_method');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_vehicles` ADD COLUMN `payment_method` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `amount_paid`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_vehicles' AND column_name = 'sold_by');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_vehicles` ADD COLUMN `sold_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL AFTER `payment_method`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Spec fields (CI 020 + 024)
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_vehicles' AND column_name = 'body_type');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_vehicles` ADD COLUMN `body_type` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_vehicles' AND column_name = 'engine_capacity');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_vehicles` ADD COLUMN `engine_capacity` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_vehicles' AND column_name = 'drivetrain');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_vehicles` ADD COLUMN `drivetrain` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_vehicles' AND column_name = 'trim_level');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_vehicles` ADD COLUMN `trim_level` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_vehicles' AND column_name = 'number_of_owners');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_vehicles` ADD COLUMN `number_of_owners` INT(11) DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_vehicles' AND column_name = 'registration_date');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_vehicles` ADD COLUMN `registration_date` DATE DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ----------------------------------------------------------------------------
-- Vehicle master data (CI 022)
-- ----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `db_vehicle_makes` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `name` VARCHAR(128) NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_vehicle_models` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `make_id` INT(11) UNSIGNED NOT NULL,
  `name` VARCHAR(128) NOT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  KEY `make_id` (`make_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_vehicle_attribute_options` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `attribute_type` VARCHAR(64) NOT NULL,
  `attribute_value` VARCHAR(128) NOT NULL,
  `sort_order` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  KEY `attribute_type` (`attribute_type`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ----------------------------------------------------------------------------
-- Vehicle images gallery (CI 023)
-- ----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `db_vehicle_images` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `vehicle_id` INT(11) UNSIGNED NOT NULL,
  `image_path` VARCHAR(255) NOT NULL,
  `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
  `sort_order` INT(11) UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME DEFAULT NULL,
  `updated_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `vehicle_id` (`vehicle_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Migrate existing single-image vehicles into the gallery (guarded)
INSERT INTO `db_vehicle_images` (`vehicle_id`,`image_path`,`is_primary`,`sort_order`,`created_at`,`updated_at`)
SELECT v.`id`, v.`image_path`, 1, 0, NOW(), NOW() FROM `db_vehicles` v
WHERE v.`image_path` IS NOT NULL AND v.`image_path` != ''
  AND NOT EXISTS (SELECT 1 FROM `db_vehicle_images` vi WHERE vi.`vehicle_id` = v.`id`);

-- ----------------------------------------------------------------------------
-- Automotive storefront themes (CI 021)
-- ----------------------------------------------------------------------------

INSERT IGNORE INTO `db_storefront_themes` (`theme_key`,`theme_name`,`industry`,`description`,`default_primary_color`,`default_secondary_color`,`default_font_family`,`sort_order`,`status`) VALUES
('auto_modern','Auto Modern','automotive','Clean, world-class car dealership theme with a blue and white hero, fast minified images, mobile-first grids and WhatsApp leads.','#2563EB','#0B1220','Inter',26,1),
('auto_luxe','Auto Luxe','automotive','Dark, premium luxury vehicle theme with gold accents, dramatic hero, and a premium buying experience.','#C9A961','#0B0F1A','Inter',27,1),
('auto_garage','Auto Garage','automotive','Rugged, high-energy auto theme for trucks, SUVs and performance vehicles with bold red and charcoal styling.','#DC2626','#1F2937','Inter',28,1);

-- Default vehicle makes (guarded: only when absent)
INSERT INTO `db_vehicle_makes` (`store_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,'Toyota',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_makes` WHERE `name`='Toyota' AND `store_id`=0);
INSERT INTO `db_vehicle_makes` (`store_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,'Honda',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_makes` WHERE `name`='Honda' AND `store_id`=0);
INSERT INTO `db_vehicle_makes` (`store_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,'Ford',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_makes` WHERE `name`='Ford' AND `store_id`=0);
INSERT INTO `db_vehicle_makes` (`store_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,'BMW',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_makes` WHERE `name`='BMW' AND `store_id`=0);
INSERT INTO `db_vehicle_makes` (`store_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,'Mercedes-Benz',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_makes` WHERE `name`='Mercedes-Benz' AND `store_id`=0);
INSERT INTO `db_vehicle_makes` (`store_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,'Nissan',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_makes` WHERE `name`='Nissan' AND `store_id`=0);
INSERT INTO `db_vehicle_makes` (`store_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,'Hyundai',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_makes` WHERE `name`='Hyundai' AND `store_id`=0);
INSERT INTO `db_vehicle_makes` (`store_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,'Volkswagen',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_makes` WHERE `name`='Volkswagen' AND `store_id`=0);
INSERT INTO `db_vehicle_makes` (`store_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,'Kia',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_makes` WHERE `name`='Kia' AND `store_id`=0);
INSERT INTO `db_vehicle_makes` (`store_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,'Mazda',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_makes` WHERE `name`='Mazda' AND `store_id`=0);
INSERT INTO `db_vehicle_makes` (`store_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,'Lexus',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_makes` WHERE `name`='Lexus' AND `store_id`=0);
INSERT INTO `db_vehicle_makes` (`store_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,'Audi',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_makes` WHERE `name`='Audi' AND `store_id`=0);
INSERT INTO `db_vehicle_makes` (`store_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,'Chevrolet',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_makes` WHERE `name`='Chevrolet' AND `store_id`=0);
INSERT INTO `db_vehicle_makes` (`store_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,'Tesla',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_makes` WHERE `name`='Tesla' AND `store_id`=0);
INSERT INTO `db_vehicle_makes` (`store_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,'Peugeot',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_makes` WHERE `name`='Peugeot' AND `store_id`=0);
INSERT INTO `db_vehicle_makes` (`store_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,'Subaru',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_makes` WHERE `name`='Subaru' AND `store_id`=0);
INSERT INTO `db_vehicle_makes` (`store_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,'Land Rover',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_makes` WHERE `name`='Land Rover' AND `store_id`=0);
INSERT INTO `db_vehicle_makes` (`store_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,'Jeep',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_makes` WHERE `name`='Jeep' AND `store_id`=0);

-- Default vehicle models (guarded)
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Toyota' AND `store_id`=0 LIMIT 1),'Corolla',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Toyota' AND mk.store_id=0 AND vm.name='Corolla' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Toyota' AND `store_id`=0 LIMIT 1),'Camry',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Toyota' AND mk.store_id=0 AND vm.name='Camry' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Toyota' AND `store_id`=0 LIMIT 1),'RAV4',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Toyota' AND mk.store_id=0 AND vm.name='RAV4' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Toyota' AND `store_id`=0 LIMIT 1),'Hilux',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Toyota' AND mk.store_id=0 AND vm.name='Hilux' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Toyota' AND `store_id`=0 LIMIT 1),'Prado',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Toyota' AND mk.store_id=0 AND vm.name='Prado' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Toyota' AND `store_id`=0 LIMIT 1),'Land Cruiser',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Toyota' AND mk.store_id=0 AND vm.name='Land Cruiser' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Toyota' AND `store_id`=0 LIMIT 1),'Yaris',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Toyota' AND mk.store_id=0 AND vm.name='Yaris' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Toyota' AND `store_id`=0 LIMIT 1),'Highlander',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Toyota' AND mk.store_id=0 AND vm.name='Highlander' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Toyota' AND `store_id`=0 LIMIT 1),'Avalon',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Toyota' AND mk.store_id=0 AND vm.name='Avalon' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Toyota' AND `store_id`=0 LIMIT 1),'Sienna',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Toyota' AND mk.store_id=0 AND vm.name='Sienna' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Honda' AND `store_id`=0 LIMIT 1),'Civic',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Honda' AND mk.store_id=0 AND vm.name='Civic' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Honda' AND `store_id`=0 LIMIT 1),'Accord',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Honda' AND mk.store_id=0 AND vm.name='Accord' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Honda' AND `store_id`=0 LIMIT 1),'CR-V',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Honda' AND mk.store_id=0 AND vm.name='CR-V' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Honda' AND `store_id`=0 LIMIT 1),'HR-V',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Honda' AND mk.store_id=0 AND vm.name='HR-V' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Honda' AND `store_id`=0 LIMIT 1),'Pilot',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Honda' AND mk.store_id=0 AND vm.name='Pilot' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Honda' AND `store_id`=0 LIMIT 1),'Odyssey',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Honda' AND mk.store_id=0 AND vm.name='Odyssey' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Honda' AND `store_id`=0 LIMIT 1),'Jazz',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Honda' AND mk.store_id=0 AND vm.name='Jazz' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Honda' AND `store_id`=0 LIMIT 1),'City',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Honda' AND mk.store_id=0 AND vm.name='City' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Honda' AND `store_id`=0 LIMIT 1),'Ridgeline',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Honda' AND mk.store_id=0 AND vm.name='Ridgeline' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Honda' AND `store_id`=0 LIMIT 1),'Insight',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Honda' AND mk.store_id=0 AND vm.name='Insight' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Ford' AND `store_id`=0 LIMIT 1),'Focus',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Ford' AND mk.store_id=0 AND vm.name='Focus' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Ford' AND `store_id`=0 LIMIT 1),'Fusion',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Ford' AND mk.store_id=0 AND vm.name='Fusion' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Ford' AND `store_id`=0 LIMIT 1),'Mustang',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Ford' AND mk.store_id=0 AND vm.name='Mustang' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Ford' AND `store_id`=0 LIMIT 1),'F-150',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Ford' AND mk.store_id=0 AND vm.name='F-150' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Ford' AND `store_id`=0 LIMIT 1),'Explorer',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Ford' AND mk.store_id=0 AND vm.name='Explorer' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Ford' AND `store_id`=0 LIMIT 1),'Escape',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Ford' AND mk.store_id=0 AND vm.name='Escape' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Ford' AND `store_id`=0 LIMIT 1),'Edge',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Ford' AND mk.store_id=0 AND vm.name='Edge' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Ford' AND `store_id`=0 LIMIT 1),'Ranger',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Ford' AND mk.store_id=0 AND vm.name='Ranger' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Ford' AND `store_id`=0 LIMIT 1),'Bronco',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Ford' AND mk.store_id=0 AND vm.name='Bronco' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Ford' AND `store_id`=0 LIMIT 1),'Expedition',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Ford' AND mk.store_id=0 AND vm.name='Expedition' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='BMW' AND `store_id`=0 LIMIT 1),'3 Series',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='BMW' AND mk.store_id=0 AND vm.name='3 Series' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='BMW' AND `store_id`=0 LIMIT 1),'5 Series',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='BMW' AND mk.store_id=0 AND vm.name='5 Series' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='BMW' AND `store_id`=0 LIMIT 1),'7 Series',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='BMW' AND mk.store_id=0 AND vm.name='7 Series' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='BMW' AND `store_id`=0 LIMIT 1),'X3',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='BMW' AND mk.store_id=0 AND vm.name='X3' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='BMW' AND `store_id`=0 LIMIT 1),'X5',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='BMW' AND mk.store_id=0 AND vm.name='X5' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='BMW' AND `store_id`=0 LIMIT 1),'X6',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='BMW' AND mk.store_id=0 AND vm.name='X6' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='BMW' AND `store_id`=0 LIMIT 1),'X7',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='BMW' AND mk.store_id=0 AND vm.name='X7' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='BMW' AND `store_id`=0 LIMIT 1),'M3',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='BMW' AND mk.store_id=0 AND vm.name='M3' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='BMW' AND `store_id`=0 LIMIT 1),'M5',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='BMW' AND mk.store_id=0 AND vm.name='M5' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='BMW' AND `store_id`=0 LIMIT 1),'Z4',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='BMW' AND mk.store_id=0 AND vm.name='Z4' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Mercedes-Benz' AND `store_id`=0 LIMIT 1),'A-Class',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Mercedes-Benz' AND mk.store_id=0 AND vm.name='A-Class' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Mercedes-Benz' AND `store_id`=0 LIMIT 1),'C-Class',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Mercedes-Benz' AND mk.store_id=0 AND vm.name='C-Class' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Mercedes-Benz' AND `store_id`=0 LIMIT 1),'E-Class',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Mercedes-Benz' AND mk.store_id=0 AND vm.name='E-Class' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Mercedes-Benz' AND `store_id`=0 LIMIT 1),'S-Class',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Mercedes-Benz' AND mk.store_id=0 AND vm.name='S-Class' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Mercedes-Benz' AND `store_id`=0 LIMIT 1),'GLA',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Mercedes-Benz' AND mk.store_id=0 AND vm.name='GLA' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Mercedes-Benz' AND `store_id`=0 LIMIT 1),'GLC',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Mercedes-Benz' AND mk.store_id=0 AND vm.name='GLC' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Mercedes-Benz' AND `store_id`=0 LIMIT 1),'GLE',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Mercedes-Benz' AND mk.store_id=0 AND vm.name='GLE' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Mercedes-Benz' AND `store_id`=0 LIMIT 1),'GLS',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Mercedes-Benz' AND mk.store_id=0 AND vm.name='GLS' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Mercedes-Benz' AND `store_id`=0 LIMIT 1),'CLA',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Mercedes-Benz' AND mk.store_id=0 AND vm.name='CLA' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Mercedes-Benz' AND `store_id`=0 LIMIT 1),'G-Class',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Mercedes-Benz' AND mk.store_id=0 AND vm.name='G-Class' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Nissan' AND `store_id`=0 LIMIT 1),'Altima',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Nissan' AND mk.store_id=0 AND vm.name='Altima' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Nissan' AND `store_id`=0 LIMIT 1),'Sentra',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Nissan' AND mk.store_id=0 AND vm.name='Sentra' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Nissan' AND `store_id`=0 LIMIT 1),'Maxima',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Nissan' AND mk.store_id=0 AND vm.name='Maxima' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Nissan' AND `store_id`=0 LIMIT 1),'Rogue',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Nissan' AND mk.store_id=0 AND vm.name='Rogue' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Nissan' AND `store_id`=0 LIMIT 1),'Pathfinder',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Nissan' AND mk.store_id=0 AND vm.name='Pathfinder' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Nissan' AND `store_id`=0 LIMIT 1),'Murano',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Nissan' AND mk.store_id=0 AND vm.name='Murano' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Nissan' AND `store_id`=0 LIMIT 1),'Frontier',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Nissan' AND mk.store_id=0 AND vm.name='Frontier' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Nissan' AND `store_id`=0 LIMIT 1),'Titan',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Nissan' AND mk.store_id=0 AND vm.name='Titan' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Nissan' AND `store_id`=0 LIMIT 1),'Armada',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Nissan' AND mk.store_id=0 AND vm.name='Armada' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Nissan' AND `store_id`=0 LIMIT 1),'Juke',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Nissan' AND mk.store_id=0 AND vm.name='Juke' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Hyundai' AND `store_id`=0 LIMIT 1),'Elantra',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Hyundai' AND mk.store_id=0 AND vm.name='Elantra' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Hyundai' AND `store_id`=0 LIMIT 1),'Sonata',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Hyundai' AND mk.store_id=0 AND vm.name='Sonata' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Hyundai' AND `store_id`=0 LIMIT 1),'Tucson',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Hyundai' AND mk.store_id=0 AND vm.name='Tucson' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Hyundai' AND `store_id`=0 LIMIT 1),'Santa Fe',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Hyundai' AND mk.store_id=0 AND vm.name='Santa Fe' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Hyundai' AND `store_id`=0 LIMIT 1),'Palisade',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Hyundai' AND mk.store_id=0 AND vm.name='Palisade' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Hyundai' AND `store_id`=0 LIMIT 1),'Kona',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Hyundai' AND mk.store_id=0 AND vm.name='Kona' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Hyundai' AND `store_id`=0 LIMIT 1),'Creta',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Hyundai' AND mk.store_id=0 AND vm.name='Creta' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Hyundai' AND `store_id`=0 LIMIT 1),'Accent',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Hyundai' AND mk.store_id=0 AND vm.name='Accent' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Hyundai' AND `store_id`=0 LIMIT 1),'Venue',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Hyundai' AND mk.store_id=0 AND vm.name='Venue' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Hyundai' AND `store_id`=0 LIMIT 1),'Ioniq',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Hyundai' AND mk.store_id=0 AND vm.name='Ioniq' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Volkswagen' AND `store_id`=0 LIMIT 1),'Golf',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Volkswagen' AND mk.store_id=0 AND vm.name='Golf' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Volkswagen' AND `store_id`=0 LIMIT 1),'Passat',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Volkswagen' AND mk.store_id=0 AND vm.name='Passat' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Volkswagen' AND `store_id`=0 LIMIT 1),'Jetta',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Volkswagen' AND mk.store_id=0 AND vm.name='Jetta' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Volkswagen' AND `store_id`=0 LIMIT 1),'Tiguan',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Volkswagen' AND mk.store_id=0 AND vm.name='Tiguan' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Volkswagen' AND `store_id`=0 LIMIT 1),'Atlas',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Volkswagen' AND mk.store_id=0 AND vm.name='Atlas' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Volkswagen' AND `store_id`=0 LIMIT 1),'Arteon',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Volkswagen' AND mk.store_id=0 AND vm.name='Arteon' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Volkswagen' AND `store_id`=0 LIMIT 1),'ID.4',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Volkswagen' AND mk.store_id=0 AND vm.name='ID.4' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Volkswagen' AND `store_id`=0 LIMIT 1),'Beetle',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Volkswagen' AND mk.store_id=0 AND vm.name='Beetle' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Volkswagen' AND `store_id`=0 LIMIT 1),'Touareg',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Volkswagen' AND mk.store_id=0 AND vm.name='Touareg' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Volkswagen' AND `store_id`=0 LIMIT 1),'Amarok',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Volkswagen' AND mk.store_id=0 AND vm.name='Amarok' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Kia' AND `store_id`=0 LIMIT 1),'Rio',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Kia' AND mk.store_id=0 AND vm.name='Rio' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Kia' AND `store_id`=0 LIMIT 1),'Cerato',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Kia' AND mk.store_id=0 AND vm.name='Cerato' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Kia' AND `store_id`=0 LIMIT 1),'Optima',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Kia' AND mk.store_id=0 AND vm.name='Optima' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Kia' AND `store_id`=0 LIMIT 1),'Sorento',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Kia' AND mk.store_id=0 AND vm.name='Sorento' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Kia' AND `store_id`=0 LIMIT 1),'Sportage',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Kia' AND mk.store_id=0 AND vm.name='Sportage' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Kia' AND `store_id`=0 LIMIT 1),'Telluride',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Kia' AND mk.store_id=0 AND vm.name='Telluride' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Kia' AND `store_id`=0 LIMIT 1),'Seltos',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Kia' AND mk.store_id=0 AND vm.name='Seltos' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Kia' AND `store_id`=0 LIMIT 1),'Carnival',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Kia' AND mk.store_id=0 AND vm.name='Carnival' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Kia' AND `store_id`=0 LIMIT 1),'Stinger',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Kia' AND mk.store_id=0 AND vm.name='Stinger' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Kia' AND `store_id`=0 LIMIT 1),'EV6',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Kia' AND mk.store_id=0 AND vm.name='EV6' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Mazda' AND `store_id`=0 LIMIT 1),'Mazda2',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Mazda' AND mk.store_id=0 AND vm.name='Mazda2' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Mazda' AND `store_id`=0 LIMIT 1),'Mazda3',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Mazda' AND mk.store_id=0 AND vm.name='Mazda3' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Mazda' AND `store_id`=0 LIMIT 1),'Mazda6',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Mazda' AND mk.store_id=0 AND vm.name='Mazda6' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Mazda' AND `store_id`=0 LIMIT 1),'CX-3',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Mazda' AND mk.store_id=0 AND vm.name='CX-3' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Mazda' AND `store_id`=0 LIMIT 1),'CX-5',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Mazda' AND mk.store_id=0 AND vm.name='CX-5' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Mazda' AND `store_id`=0 LIMIT 1),'CX-9',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Mazda' AND mk.store_id=0 AND vm.name='CX-9' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Mazda' AND `store_id`=0 LIMIT 1),'MX-5',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Mazda' AND mk.store_id=0 AND vm.name='MX-5' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Mazda' AND `store_id`=0 LIMIT 1),'CX-30',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Mazda' AND mk.store_id=0 AND vm.name='CX-30' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Mazda' AND `store_id`=0 LIMIT 1),'CX-50',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Mazda' AND mk.store_id=0 AND vm.name='CX-50' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Mazda' AND `store_id`=0 LIMIT 1),'BT-50',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Mazda' AND mk.store_id=0 AND vm.name='BT-50' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Lexus' AND `store_id`=0 LIMIT 1),'IS',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Lexus' AND mk.store_id=0 AND vm.name='IS' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Lexus' AND `store_id`=0 LIMIT 1),'ES',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Lexus' AND mk.store_id=0 AND vm.name='ES' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Lexus' AND `store_id`=0 LIMIT 1),'GS',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Lexus' AND mk.store_id=0 AND vm.name='GS' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Lexus' AND `store_id`=0 LIMIT 1),'LS',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Lexus' AND mk.store_id=0 AND vm.name='LS' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Lexus' AND `store_id`=0 LIMIT 1),'RX',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Lexus' AND mk.store_id=0 AND vm.name='RX' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Lexus' AND `store_id`=0 LIMIT 1),'NX',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Lexus' AND mk.store_id=0 AND vm.name='NX' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Lexus' AND `store_id`=0 LIMIT 1),'UX',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Lexus' AND mk.store_id=0 AND vm.name='UX' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Lexus' AND `store_id`=0 LIMIT 1),'GX',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Lexus' AND mk.store_id=0 AND vm.name='GX' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Lexus' AND `store_id`=0 LIMIT 1),'LX',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Lexus' AND mk.store_id=0 AND vm.name='LX' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Lexus' AND `store_id`=0 LIMIT 1),'LC',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Lexus' AND mk.store_id=0 AND vm.name='LC' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Audi' AND `store_id`=0 LIMIT 1),'A3',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Audi' AND mk.store_id=0 AND vm.name='A3' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Audi' AND `store_id`=0 LIMIT 1),'A4',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Audi' AND mk.store_id=0 AND vm.name='A4' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Audi' AND `store_id`=0 LIMIT 1),'A6',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Audi' AND mk.store_id=0 AND vm.name='A6' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Audi' AND `store_id`=0 LIMIT 1),'A8',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Audi' AND mk.store_id=0 AND vm.name='A8' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Audi' AND `store_id`=0 LIMIT 1),'Q3',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Audi' AND mk.store_id=0 AND vm.name='Q3' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Audi' AND `store_id`=0 LIMIT 1),'Q5',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Audi' AND mk.store_id=0 AND vm.name='Q5' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Audi' AND `store_id`=0 LIMIT 1),'Q7',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Audi' AND mk.store_id=0 AND vm.name='Q7' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Audi' AND `store_id`=0 LIMIT 1),'Q8',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Audi' AND mk.store_id=0 AND vm.name='Q8' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Audi' AND `store_id`=0 LIMIT 1),'TT',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Audi' AND mk.store_id=0 AND vm.name='TT' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Audi' AND `store_id`=0 LIMIT 1),'e-tron',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Audi' AND mk.store_id=0 AND vm.name='e-tron' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Chevrolet' AND `store_id`=0 LIMIT 1),'Cruze',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Chevrolet' AND mk.store_id=0 AND vm.name='Cruze' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Chevrolet' AND `store_id`=0 LIMIT 1),'Malibu',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Chevrolet' AND mk.store_id=0 AND vm.name='Malibu' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Chevrolet' AND `store_id`=0 LIMIT 1),'Camaro',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Chevrolet' AND mk.store_id=0 AND vm.name='Camaro' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Chevrolet' AND `store_id`=0 LIMIT 1),'Silverado',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Chevrolet' AND mk.store_id=0 AND vm.name='Silverado' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Chevrolet' AND `store_id`=0 LIMIT 1),'Equinox',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Chevrolet' AND mk.store_id=0 AND vm.name='Equinox' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Chevrolet' AND `store_id`=0 LIMIT 1),'Traverse',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Chevrolet' AND mk.store_id=0 AND vm.name='Traverse' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Chevrolet' AND `store_id`=0 LIMIT 1),'Tahoe',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Chevrolet' AND mk.store_id=0 AND vm.name='Tahoe' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Chevrolet' AND `store_id`=0 LIMIT 1),'Suburban',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Chevrolet' AND mk.store_id=0 AND vm.name='Suburban' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Chevrolet' AND `store_id`=0 LIMIT 1),'Trailblazer',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Chevrolet' AND mk.store_id=0 AND vm.name='Trailblazer' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Chevrolet' AND `store_id`=0 LIMIT 1),'Blazer',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Chevrolet' AND mk.store_id=0 AND vm.name='Blazer' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Tesla' AND `store_id`=0 LIMIT 1),'Model S',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Tesla' AND mk.store_id=0 AND vm.name='Model S' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Tesla' AND `store_id`=0 LIMIT 1),'Model 3',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Tesla' AND mk.store_id=0 AND vm.name='Model 3' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Tesla' AND `store_id`=0 LIMIT 1),'Model X',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Tesla' AND mk.store_id=0 AND vm.name='Model X' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Tesla' AND `store_id`=0 LIMIT 1),'Model Y',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Tesla' AND mk.store_id=0 AND vm.name='Model Y' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Tesla' AND `store_id`=0 LIMIT 1),'Cybertruck',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Tesla' AND mk.store_id=0 AND vm.name='Cybertruck' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Tesla' AND `store_id`=0 LIMIT 1),'Roadster',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Tesla' AND mk.store_id=0 AND vm.name='Roadster' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Peugeot' AND `store_id`=0 LIMIT 1),'208',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Peugeot' AND mk.store_id=0 AND vm.name='208' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Peugeot' AND `store_id`=0 LIMIT 1),'308',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Peugeot' AND mk.store_id=0 AND vm.name='308' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Peugeot' AND `store_id`=0 LIMIT 1),'508',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Peugeot' AND mk.store_id=0 AND vm.name='508' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Peugeot' AND `store_id`=0 LIMIT 1),'3008',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Peugeot' AND mk.store_id=0 AND vm.name='3008' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Peugeot' AND `store_id`=0 LIMIT 1),'5008',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Peugeot' AND mk.store_id=0 AND vm.name='5008' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Peugeot' AND `store_id`=0 LIMIT 1),'Rifter',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Peugeot' AND mk.store_id=0 AND vm.name='Rifter' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Peugeot' AND `store_id`=0 LIMIT 1),'2008',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Peugeot' AND mk.store_id=0 AND vm.name='2008' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Peugeot' AND `store_id`=0 LIMIT 1),'Boxer',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Peugeot' AND mk.store_id=0 AND vm.name='Boxer' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Peugeot' AND `store_id`=0 LIMIT 1),'Traveller',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Peugeot' AND mk.store_id=0 AND vm.name='Traveller' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Peugeot' AND `store_id`=0 LIMIT 1),'Landtrek',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Peugeot' AND mk.store_id=0 AND vm.name='Landtrek' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Subaru' AND `store_id`=0 LIMIT 1),'Impreza',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Subaru' AND mk.store_id=0 AND vm.name='Impreza' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Subaru' AND `store_id`=0 LIMIT 1),'Legacy',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Subaru' AND mk.store_id=0 AND vm.name='Legacy' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Subaru' AND `store_id`=0 LIMIT 1),'Outback',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Subaru' AND mk.store_id=0 AND vm.name='Outback' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Subaru' AND `store_id`=0 LIMIT 1),'Forester',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Subaru' AND mk.store_id=0 AND vm.name='Forester' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Subaru' AND `store_id`=0 LIMIT 1),'Crosstrek',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Subaru' AND mk.store_id=0 AND vm.name='Crosstrek' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Subaru' AND `store_id`=0 LIMIT 1),'Ascent',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Subaru' AND mk.store_id=0 AND vm.name='Ascent' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Subaru' AND `store_id`=0 LIMIT 1),'BRZ',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Subaru' AND mk.store_id=0 AND vm.name='BRZ' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Subaru' AND `store_id`=0 LIMIT 1),'WRX',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Subaru' AND mk.store_id=0 AND vm.name='WRX' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Subaru' AND `store_id`=0 LIMIT 1),'Solterra',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Subaru' AND mk.store_id=0 AND vm.name='Solterra' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Subaru' AND `store_id`=0 LIMIT 1),'XV',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Subaru' AND mk.store_id=0 AND vm.name='XV' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Land Rover' AND `store_id`=0 LIMIT 1),'Defender',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Land Rover' AND mk.store_id=0 AND vm.name='Defender' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Land Rover' AND `store_id`=0 LIMIT 1),'Discovery',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Land Rover' AND mk.store_id=0 AND vm.name='Discovery' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Land Rover' AND `store_id`=0 LIMIT 1),'Discovery Sport',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Land Rover' AND mk.store_id=0 AND vm.name='Discovery Sport' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Land Rover' AND `store_id`=0 LIMIT 1),'Range Rover',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Land Rover' AND mk.store_id=0 AND vm.name='Range Rover' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Land Rover' AND `store_id`=0 LIMIT 1),'Range Rover Sport',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Land Rover' AND mk.store_id=0 AND vm.name='Range Rover Sport' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Land Rover' AND `store_id`=0 LIMIT 1),'Range Rover Velar',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Land Rover' AND mk.store_id=0 AND vm.name='Range Rover Velar' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Land Rover' AND `store_id`=0 LIMIT 1),'Range Rover Evoque',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Land Rover' AND mk.store_id=0 AND vm.name='Range Rover Evoque' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Land Rover' AND `store_id`=0 LIMIT 1),'Freelander',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Land Rover' AND mk.store_id=0 AND vm.name='Freelander' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Jeep' AND `store_id`=0 LIMIT 1),'Wrangler',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Jeep' AND mk.store_id=0 AND vm.name='Wrangler' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Jeep' AND `store_id`=0 LIMIT 1),'Grand Cherokee',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Jeep' AND mk.store_id=0 AND vm.name='Grand Cherokee' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Jeep' AND `store_id`=0 LIMIT 1),'Cherokee',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Jeep' AND mk.store_id=0 AND vm.name='Cherokee' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Jeep' AND `store_id`=0 LIMIT 1),'Compass',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Jeep' AND mk.store_id=0 AND vm.name='Compass' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Jeep' AND `store_id`=0 LIMIT 1),'Renegade',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Jeep' AND mk.store_id=0 AND vm.name='Renegade' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Jeep' AND `store_id`=0 LIMIT 1),'Gladiator',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Jeep' AND mk.store_id=0 AND vm.name='Gladiator' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Jeep' AND `store_id`=0 LIMIT 1),'Wagoneer',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Jeep' AND mk.store_id=0 AND vm.name='Wagoneer' AND vm.store_id=0);
INSERT INTO `db_vehicle_models` (`store_id`,`make_id`,`name`,`status`,`created_at`,`updated_at`) SELECT 0,(SELECT `id` FROM `db_vehicle_makes` WHERE `name`='Jeep' AND `store_id`=0 LIMIT 1),'Grand Wagoneer',1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_models` vm JOIN `db_vehicle_makes` mk ON mk.id=vm.make_id WHERE mk.name='Jeep' AND mk.store_id=0 AND vm.name='Grand Wagoneer' AND vm.store_id=0);

-- Default vehicle attribute options (guarded)
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'body_type','Sedan',1,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='body_type' AND `attribute_value`='Sedan' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'body_type','SUV',2,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='body_type' AND `attribute_value`='SUV' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'body_type','Truck',3,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='body_type' AND `attribute_value`='Truck' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'body_type','Coupe',4,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='body_type' AND `attribute_value`='Coupe' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'body_type','Hatchback',5,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='body_type' AND `attribute_value`='Hatchback' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'body_type','Wagon',6,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='body_type' AND `attribute_value`='Wagon' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'body_type','Van',7,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='body_type' AND `attribute_value`='Van' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'body_type','Bus',8,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='body_type' AND `attribute_value`='Bus' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'body_type','Convertible',9,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='body_type' AND `attribute_value`='Convertible' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'fuel_type','Petrol',1,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='fuel_type' AND `attribute_value`='Petrol' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'fuel_type','Diesel',2,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='fuel_type' AND `attribute_value`='Diesel' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'fuel_type','Hybrid',3,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='fuel_type' AND `attribute_value`='Hybrid' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'fuel_type','Electric',4,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='fuel_type' AND `attribute_value`='Electric' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'transmission','Automatic',1,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='transmission' AND `attribute_value`='Automatic' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'transmission','Manual',2,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='transmission' AND `attribute_value`='Manual' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'transmission','CVT',3,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='transmission' AND `attribute_value`='CVT' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'drivetrain','FWD',1,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='drivetrain' AND `attribute_value`='FWD' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'drivetrain','RWD',2,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='drivetrain' AND `attribute_value`='RWD' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'drivetrain','AWD',3,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='drivetrain' AND `attribute_value`='AWD' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'drivetrain','4WD',4,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='drivetrain' AND `attribute_value`='4WD' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'condition','New',1,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='condition' AND `attribute_value`='New' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'condition','Foreign Used',2,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='condition' AND `attribute_value`='Foreign Used' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'condition','Locally Used',3,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='condition' AND `attribute_value`='Locally Used' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'condition','Accident Free',4,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='condition' AND `attribute_value`='Accident Free' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'condition','Certified Pre-Owned',5,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='condition' AND `attribute_value`='Certified Pre-Owned' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'color','White',1,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='color' AND `attribute_value`='White' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'color','Black',2,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='color' AND `attribute_value`='Black' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'color','Silver',3,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='color' AND `attribute_value`='Silver' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'color','Grey',4,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='color' AND `attribute_value`='Grey' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'color','Blue',5,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='color' AND `attribute_value`='Blue' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'color','Red',6,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='color' AND `attribute_value`='Red' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'color','Green',7,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='color' AND `attribute_value`='Green' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'color','Gold',8,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='color' AND `attribute_value`='Gold' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'color','Brown',9,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='color' AND `attribute_value`='Brown' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'color','Beige',10,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='color' AND `attribute_value`='Beige' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'color','Yellow',11,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='color' AND `attribute_value`='Yellow' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'color','Orange',12,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='color' AND `attribute_value`='Orange' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'color','Purple',13,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='color' AND `attribute_value`='Purple' AND `store_id`=0);
INSERT INTO `db_vehicle_attribute_options` (`store_id`,`attribute_type`,`attribute_value`,`sort_order`,`status`,`created_at`,`updated_at`) SELECT 0,'color','Maroon',14,1,NOW(),NOW() FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM `db_vehicle_attribute_options` WHERE `attribute_type`='color' AND `attribute_value`='Maroon' AND `store_id`=0);

-- ----------------------------------------------------------------------------
-- Migration 15: Manual Shipping / POS Delivery Fee (v4.0.9.22)
-- Source: updates/migrations/4.0.9.22_manual_shipping.sql
-- ----------------------------------------------------------------------------

CREATE TABLE IF NOT EXISTS `db_shipping_fees` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `store_id` INT NOT NULL,
    `label` VARCHAR(160) NOT NULL,
    `location` VARCHAR(200) DEFAULT NULL,
    `fee` DOUBLE(20,2) NOT NULL DEFAULT 0,
    `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
    `sort_order` INT NOT NULL DEFAULT 0,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_store (store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_sales' AND column_name = 'shipping_fee');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_sales` ADD COLUMN `shipping_fee` DOUBLE(20,2) DEFAULT NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_sales' AND column_name = 'shipping_label');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_sales` ADD COLUMN `shipping_label` VARCHAR(160) DEFAULT NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_sales' AND column_name = 'shipping_fee_id');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_sales` ADD COLUMN `shipping_fee_id` INT DEFAULT NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_hold' AND column_name = 'shipping_fee');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_hold` ADD COLUMN `shipping_fee` DOUBLE(20,2) DEFAULT NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_hold' AND column_name = 'shipping_label');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_hold` ADD COLUMN `shipping_label` VARCHAR(160) DEFAULT NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_hold' AND column_name = 'shipping_fee_id');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_hold` ADD COLUMN `shipping_fee_id` INT DEFAULT NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET FOREIGN_KEY_CHECKS = 1;

-- ----------------------------------------------------------------------------
-- Migration 14: Storefront column fixes (v4.0.9.13) - fixes online store 500
-- Source: updates/migrations/4.0.9.13_storefront_columns.sql
-- ----------------------------------------------------------------------------

-- db_items.product_type (physical|service|digital|course|membership)
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'product_type');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_items` ADD COLUMN `product_type` VARCHAR(20) NOT NULL DEFAULT ''physical'' COMMENT ''physical | service | digital | course | membership''',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- db_online_order_items may be absent on installs that skipped 4.0.2
CREATE TABLE IF NOT EXISTS `db_online_order_items` (
  `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `order_id` INT(11) NOT NULL,
  `item_id` INT(11) NOT NULL,
  `item_type` ENUM('product','service','digital','course','membership') NOT NULL DEFAULT 'product',
  `qty` DECIMAL(10,2) NOT NULL DEFAULT 1.00,
  `price` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  `total_price` DECIMAL(18,2) NOT NULL DEFAULT 0.00,
  `created_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `item_id` (`item_id`),
  KEY `idx_item_type` (`item_type`,`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Widen item_type on installs where the table exists with the old 2-value enum
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_online_order_items' AND column_name = 'item_type');
SET @sql = IF(@col_exists > 0,
  'ALTER TABLE `db_online_order_items` MODIFY COLUMN `item_type` ENUM(''product'',''service'',''digital'',''course'',''membership'') NOT NULL DEFAULT ''product''',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;


-- ============================================================================
-- Migration 16: Perfumery Module (v4.0.9.24)
-- ============================================================================

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

-- ============================================================================
-- Migration 17: Audit Trail + Email Settings + Report Schedules (v4.0.9.24)
-- ============================================================================

-- ============================================================================
-- MartPoint 4.0.9.24 — Audit Trail + fresh-install settings table parity
--
-- 1. Creates db_audit_trail (operational log of user actions). The page lives
--    under Settings → Audit Trail and is gated by the `audit_trail_view`
--    permission key — assign it to roles that should see the log.
-- 2. Back-fills the modular store settings tables for installs whose schema
--    predates the 4.0.2→4.0.3 modularization migration (all IF NOT EXISTS).
-- ============================================================================

CREATE TABLE IF NOT EXISTS `db_audit_trail` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` int(11) UNSIGNED NOT NULL DEFAULT 1,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `module` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ref_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store` (`store_id`),
  KEY `idx_module` (`module`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_email_settings` (
  `store_id` int(11) NOT NULL,
  `email_provider` varchar(50) DEFAULT 'smtp',
  `email_from_name` varchar(255) DEFAULT NULL,
  `email_from_email` varchar(255) DEFAULT NULL,
  `email_reply_to` varchar(255) DEFAULT NULL,
  `smtp_crypto` varchar(50) DEFAULT NULL,
  `resend_api_key` varchar(255) DEFAULT NULL,
  `resend_from_email` varchar(255) DEFAULT NULL,
  `resend_from_name` varchar(255) DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_report_schedules` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` int(11) UNSIGNED NOT NULL DEFAULT 1,
  `report_type` varchar(64) NOT NULL COMMENT 'daily_summary, low_stock, overdue_debt',
  `template_name` varchar(128) DEFAULT NULL,
  `frequency` varchar(16) NOT NULL DEFAULT 'daily' COMMENT 'daily, weekly',
  `send_time` varchar(8) NOT NULL DEFAULT '18:00' COMMENT 'HH:MM 24h format',
  `email_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `email_recipients` varchar(500) DEFAULT NULL COMMENT 'comma-separated emails',
  `email_template_key` varchar(64) DEFAULT 'daily_business_summary',
  `whatsapp_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `whatsapp_numbers` varchar(500) DEFAULT NULL COMMENT 'comma-separated with country code',
  `whatsapp_message_template` text DEFAULT NULL,
  `last_run_at` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_report_type_store` (`report_type`,`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================================
-- Migration 18: Monnify Integration (v4.0.9.25)
-- ============================================================================

-- ============================================================================
-- MartPoint 4.0.9.25 — Monnify (Moniepoint) Integration
--
-- Adds first-party Monnify gateway support for collections and disbursements:
--   1. db_monnify_settings   — per-store API credentials (API key, secret key,
--      contract code), sandbox/live toggle, cached OAuth token and the payout
--      source wallet account number.
--   2. db_monnify_payments   — collection transactions (hosted checkout links
--      and pay-with-transfer dynamic accounts) linked to sales/customers.
--   3. db_monnify_transfers  — disbursement (NIP transfer) records with the
--      OTP / status lifecycle.
--   4. Grants `monnify_settings` and `monnify_transfers` to every role that
--      already holds `paystack_settings`.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Monnify Settings (per store)
CREATE TABLE IF NOT EXISTS `db_monnify_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `store_id` INT NOT NULL DEFAULT 1,
  `api_key` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Monnify API Key (MK_PROD_.../MK_TEST_...)',
  `secret_key` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Monnify Secret Key',
  `contract_code` VARCHAR(50) NOT NULL DEFAULT '' COMMENT 'Monnify Contract Code',
  `wallet_account_number` VARCHAR(20) NOT NULL DEFAULT '' COMMENT 'Monnify wallet account number — sourceAccountNumber for disbursements',
  `enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `disbursements_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Requires Monnify approval + IP whitelist',
  `test_mode` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Sandbox, 0=Live',
  `access_token` TEXT DEFAULT NULL COMMENT 'Cached OAuth2 bearer token',
  `token_expires_at` DATETIME DEFAULT NULL,
  `callback_url` VARCHAR(500) DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_date` DATE DEFAULT NULL,
  `created_time` TIME DEFAULT NULL,
  `created_by` VARCHAR(50) DEFAULT NULL,
  UNIQUE KEY `uk_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Monnify Collections tracking (checkout + pay-with-transfer)
CREATE TABLE IF NOT EXISTS `db_monnify_payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `store_id` INT NOT NULL,
  `sales_id` INT DEFAULT NULL,
  `customer_id` INT DEFAULT NULL,
  `customer_name` VARCHAR(255) DEFAULT NULL,
  `customer_email` VARCHAR(255) DEFAULT NULL,
  `customer_phone` VARCHAR(50) DEFAULT NULL,
  `amount` DECIMAL(18,2) NOT NULL,
  `currency` VARCHAR(10) DEFAULT 'NGN',
  `payment_reference` VARCHAR(255) NOT NULL COMMENT 'Merchant-generated payment reference',
  `transaction_reference` VARCHAR(255) DEFAULT NULL COMMENT 'Monnify transaction reference',
  `checkout_url` VARCHAR(500) DEFAULT NULL,
  `transfer_account_number` VARCHAR(20) DEFAULT NULL COMMENT 'Dynamic account for pay-with-transfer',
  `transfer_bank_name` VARCHAR(100) DEFAULT NULL,
  `transfer_bank_code` VARCHAR(20) DEFAULT NULL,
  `transfer_account_name` VARCHAR(255) DEFAULT NULL,
  `transfer_account_expiry` VARCHAR(50) DEFAULT NULL,
  `payment_status` VARCHAR(50) DEFAULT 'PENDING' COMMENT 'PENDING, PAID, OVERPAID, PARTIALLY_PAID, FAILED, EXPIRED',
  `payment_method` VARCHAR(50) DEFAULT NULL,
  `amount_paid` DECIMAL(18,2) DEFAULT NULL,
  `paid_at` DATETIME DEFAULT NULL,
  `meta_data` TEXT DEFAULT NULL COMMENT 'JSON of extra metadata',
  `created_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_date` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_reference` (`payment_reference`),
  INDEX `idx_sales` (`sales_id`),
  INDEX `idx_status` (`payment_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Monnify Disbursements tracking (single transfers)
CREATE TABLE IF NOT EXISTS `db_monnify_transfers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `store_id` INT NOT NULL,
  `reference` VARCHAR(255) NOT NULL COMMENT 'Merchant-generated transfer reference',
  `amount` DECIMAL(18,2) NOT NULL,
  `currency` VARCHAR(10) DEFAULT 'NGN',
  `narration` VARCHAR(100) DEFAULT NULL,
  `destination_bank_code` VARCHAR(20) NOT NULL,
  `destination_bank_name` VARCHAR(100) DEFAULT NULL,
  `destination_account_number` VARCHAR(20) NOT NULL,
  `destination_account_name` VARCHAR(255) DEFAULT NULL COMMENT 'Verified by name enquiry',
  `source_account_number` VARCHAR(20) DEFAULT NULL,
  `transfer_status` VARCHAR(50) DEFAULT 'PENDING' COMMENT 'PENDING_AUTHORIZATION, PENDING, SUCCESS, FAILED, REVERSED',
  `session_id` VARCHAR(100) DEFAULT NULL,
  `response_message` VARCHAR(255) DEFAULT NULL,
  `meta_data` TEXT DEFAULT NULL,
  `initiated_by` VARCHAR(50) DEFAULT NULL,
  `created_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_date` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_reference` (`reference`),
  INDEX `idx_status` (`transfer_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. PERMISSIONS — grant Monnify perms to roles that manage payment settings
INSERT INTO db_permissions (store_id, role_id, permissions)
SELECT DISTINCT d.store_id, d.role_id, 'monnify_settings'
FROM db_permissions d
LEFT JOIN db_permissions p2
  ON p2.store_id = d.store_id AND p2.role_id = d.role_id AND p2.permissions = 'monnify_settings'
WHERE d.permissions = 'paystack_settings' AND p2.id IS NULL;

INSERT INTO db_permissions (store_id, role_id, permissions)
SELECT DISTINCT d.store_id, d.role_id, 'monnify_transfers'
FROM db_permissions d
LEFT JOIN db_permissions p2
  ON p2.store_id = d.store_id AND p2.role_id = d.role_id AND p2.permissions = 'monnify_transfers'
WHERE d.permissions = 'paystack_settings' AND p2.id IS NULL;

SET FOREIGN_KEY_CHECKS = 1;

SET FOREIGN_KEY_CHECKS = 1;
