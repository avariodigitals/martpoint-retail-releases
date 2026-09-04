-- --------------------------------------------------------
-- MartPoint 4.0.4 -> 4.0.5 Migration
-- Performance hardening for Feature 11 warranty / serial / IMEI lookup
-- No runtime schema changes - all changes are folded into the installer.
-- --------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- --------------------------------------------------------
-- 1. Indexes for warranty lookup sold-items search
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

-- --------------------------------------------------------
-- 2. Indexes for customer/product name search
-- --------------------------------------------------------

SET @idx_exists = (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'db_customers' AND index_name = 'customer_name');
SET @sql = IF(@idx_exists = 0, 'ALTER TABLE `db_customers` ADD KEY `customer_name` (`customer_name`)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'db_customers' AND index_name = 'mobile');
SET @sql = IF(@idx_exists = 0, 'ALTER TABLE `db_customers` ADD KEY `mobile` (`mobile`)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'db_items' AND index_name = 'item_name');
SET @sql = IF(@idx_exists = 0, 'ALTER TABLE `db_items` ADD KEY `item_name` (`item_name`)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- --------------------------------------------------------
-- 3. Index for unsold barcode unit lookup
-- --------------------------------------------------------

SET @idx_exists = (SELECT COUNT(*) FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = 'db_item_barcodes' AND index_name = 'idx_item_status');
SET @sql = IF(@idx_exists = 0, 'ALTER TABLE `db_item_barcodes` ADD KEY `idx_item_status` (`item_id`, `status`)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- --------------------------------------------------------
-- 4. Record migration
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_schema_migrations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `version` VARCHAR(20) NOT NULL,
  `applied_at` DATETIME NOT NULL,
  UNIQUE KEY `uk_version` (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
INSERT IGNORE INTO `db_schema_migrations` (`version`, `applied_at`) VALUES ('4.0.5', NOW());

-- --------------------------------------------------------
-- 5. Bump application version
-- --------------------------------------------------------
UPDATE `db_sitesettings` SET `version` = '4.0.5';

SET FOREIGN_KEY_CHECKS = 1;
