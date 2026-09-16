-- v4.0.9.6: Multi-Unit Selling
-- Adds per-product selling units with conversion, unit shortcodes,
-- and sales/purchase line unit tracking for supermarkets/wholesalers.
-- Idempotent and safe to re-run.
-- --------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- 1. Unit shortcode for receipts and POS labels
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_units' AND column_name = 'shortcode');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_units` ADD COLUMN `shortcode` VARCHAR(20) NULL DEFAULT NULL AFTER `unit_name`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. Customer type for wholesale/distributor handling (Retail/Wholesale/Distributor)
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_customers' AND column_name = 'customer_type');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_customers` ADD COLUMN `customer_type` VARCHAR(50) NOT NULL DEFAULT ''Retail'' AFTER `customer_name`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3. Item selling units (per-product selling group)
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

-- 4. Track the unit actually sold and the base quantity converted for stock
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

-- 5. Track the unit actually purchased and the base quantity converted for stock
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

-- 6. Migration bookkeeping
CREATE TABLE IF NOT EXISTS `db_schema_migrations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `version` VARCHAR(20) NOT NULL,
  `applied_at` DATETIME NOT NULL,
  UNIQUE KEY `uk_version` (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `db_schema_migrations` (`version`, `applied_at`) VALUES ('4.0.9.6', NOW());

SET FOREIGN_KEY_CHECKS = 1;
