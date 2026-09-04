-- Compatibility migration for older MartPoint databases being used with v4.0.8+ code.
-- Run this in phpMyAdmin / MySQL if you see 'Unknown column' errors on POS/mobile sales.
-- Idempotent and safe to re-run on MySQL/MariaDB that do not support ADD COLUMN IF NOT EXISTS.

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
