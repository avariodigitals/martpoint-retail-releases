-- v4.0.9.8: Multi-Unit Selling Wholesale Prices
-- Adds wholesale_price to db_item_selling_units and base_unit_qty to sales/purchase returns.
-- Idempotent and safe to re-run.
-- --------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- 1. Add wholesale_price to item selling units
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_item_selling_units' AND column_name = 'wholesale_price');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_item_selling_units` ADD COLUMN `wholesale_price` DOUBLE(20,4) DEFAULT NULL AFTER `selling_price`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. Add base_unit_qty to sales returns so pack/carton returns restore correct stock
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_salesitemsreturn' AND column_name = 'base_unit_qty');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_salesitemsreturn` ADD COLUMN `base_unit_qty` DOUBLE(20,4) NULL DEFAULT NULL AFTER `return_qty`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3. Add base_unit_qty to purchase returns
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_purchaseitemsreturn' AND column_name = 'base_unit_qty');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_purchaseitemsreturn` ADD COLUMN `base_unit_qty` DOUBLE(20,4) NULL DEFAULT NULL AFTER `return_qty`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 4. Migration bookkeeping
CREATE TABLE IF NOT EXISTS `db_schema_migrations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `version` VARCHAR(20) NOT NULL,
  `applied_at` DATETIME NOT NULL,
  UNIQUE KEY `uk_version` (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `db_schema_migrations` (`version`, `applied_at`) VALUES ('4.0.9.8', NOW());

SET FOREIGN_KEY_CHECKS = 1;
