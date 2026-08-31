-- --------------------------------------------------------
-- MartPoint 4.0.3 -> 4.0.4 Migration
-- Feature 11: Serial/IMEI/Warranty tracking hardening
-- Feature 12: Price Catalogue foundation
-- Feature 13: Public Catalogue foundation
-- No runtime schema changes - all changes are folded into the installer.
-- --------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- --------------------------------------------------------
-- 1. Per-product tracking flags (F11)
-- --------------------------------------------------------
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'track_serial');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_items` ADD COLUMN `track_serial` TINYINT(1) NOT NULL DEFAULT 0 AFTER `warranty_months`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'track_imei');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_items` ADD COLUMN `track_imei` TINYINT(1) NOT NULL DEFAULT 0 AFTER `track_serial`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- --------------------------------------------------------
-- 2. Return table must mirror sales item serial/IMEI (F11)
-- --------------------------------------------------------
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_salesitemsreturn' AND column_name = 'sold_serial_number');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_salesitemsreturn` ADD COLUMN `sold_serial_number` VARCHAR(100) NULL AFTER `barcode_id`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_salesitemsreturn' AND column_name = 'sold_imei_number');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_salesitemsreturn` ADD COLUMN `sold_imei_number` VARCHAR(50) NULL AFTER `sold_serial_number`', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- --------------------------------------------------------
-- 3. Serial/IMEI uniqueness enforcement (F11)
-- Convert empty strings to NULL so unique indexes can be applied safely.
-- --------------------------------------------------------
UPDATE `db_item_barcodes` SET `serial_number` = NULL WHERE `serial_number` = '';
UPDATE `db_item_barcodes` SET `imei_number` = NULL WHERE `imei_number` = '';

SET @idx_exists = (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'db_item_barcodes' AND index_name = 'uk_serial_number');
SET @sql = IF(@idx_exists = 0, 'ALTER TABLE `db_item_barcodes` ADD UNIQUE KEY `uk_serial_number` (`serial_number`)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'db_item_barcodes' AND index_name = 'uk_imei_number');
SET @sql = IF(@idx_exists = 0, 'ALTER TABLE `db_item_barcodes` ADD UNIQUE KEY `uk_imei_number` (`imei_number`)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- --------------------------------------------------------
-- 4. Performance indexes for warranty lookup / serial/IMEI search
-- --------------------------------------------------------

SET @idx_exists = (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'db_sales' AND index_name = 'sales_code');
SET @sql = IF(@idx_exists = 0, 'ALTER TABLE `db_sales` ADD KEY `sales_code` (`sales_code`)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'db_sales' AND index_name = 'store_sales_status');
SET @sql = IF(@idx_exists = 0, 'ALTER TABLE `db_sales` ADD KEY `store_sales_status` (`store_id`, `sales_status`)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'db_salesitems' AND index_name = 'item_id');
SET @sql = IF(@idx_exists = 0, 'ALTER TABLE `db_salesitems` ADD KEY `item_id` (`item_id`)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'db_salesitems' AND index_name = 'sold_serial_number');
SET @sql = IF(@idx_exists = 0, 'ALTER TABLE `db_salesitems` ADD KEY `sold_serial_number` (`sold_serial_number`)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'db_salesitems' AND index_name = 'sold_imei_number');
SET @sql = IF(@idx_exists = 0, 'ALTER TABLE `db_salesitems` ADD KEY `sold_imei_number` (`sold_imei_number`)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'db_customers' AND index_name = 'customer_name');
SET @sql = IF(@idx_exists = 0, 'ALTER TABLE `db_customers` ADD KEY `customer_name` (`customer_name`)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'db_customers' AND index_name = 'mobile');
SET @sql = IF(@idx_exists = 0, 'ALTER TABLE `db_customers` ADD KEY `mobile` (`mobile`)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'db_items' AND index_name = 'item_name');
SET @sql = IF(@idx_exists = 0, 'ALTER TABLE `db_items` ADD KEY `item_name` (`item_name`)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'db_item_barcodes' AND index_name = 'idx_item_status');
SET @sql = IF(@idx_exists = 0, 'ALTER TABLE `db_item_barcodes` ADD KEY `idx_item_status` (`item_id`, `status`)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- --------------------------------------------------------
-- 5. Record migration
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_schema_migrations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `version` VARCHAR(20) NOT NULL,
  `applied_at` DATETIME NOT NULL,
  UNIQUE KEY `uk_version` (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT IGNORE INTO `db_schema_migrations` (`version`, `applied_at`) VALUES ('4.0.4', NOW());

-- --------------------------------------------------------
-- 5. Bump application version
-- --------------------------------------------------------
UPDATE `db_sitesettings` SET `version` = '4.0.4';

SET FOREIGN_KEY_CHECKS = 1;
