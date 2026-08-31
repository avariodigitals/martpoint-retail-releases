-- Compatibility migration for older MartPoint databases being used with v4.0.8+ code.
-- Run this in phpMyAdmin / MySQL if you see 'Unknown column' errors on POS/mobile sales.

-- db_salespayments: columns added after v4.0.2
ALTER TABLE `db_salespayments`
  ADD COLUMN IF NOT EXISTS `payment_reference` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `confirmation_status` tinyint(1) DEFAULT 1 COMMENT '0=Pending, 1=Confirmed',
  ADD COLUMN IF NOT EXISTS `payment_mode_id` int(11) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `advance_adjusted` double(20,4) DEFAULT 0.0000,
  ADD COLUMN IF NOT EXISTS `cheque_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `cheque_period` int(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `cheque_status` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL;

-- db_salesitems: columns added for barcode/serial and staff commission
ALTER TABLE `db_salesitems`
  ADD COLUMN IF NOT EXISTS `barcode_id` int(11) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `sold_serial_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `sold_imei_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `price_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'wholesale',
  ADD COLUMN IF NOT EXISTS `staff_id` int(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `commission_amount` double(20,2) DEFAULT 0.00;

-- db_holditems: make sure barcode/serial/price/staff columns exist
ALTER TABLE `db_holditems`
  ADD COLUMN IF NOT EXISTS `barcode_id` int(11) DEFAULT 0,
  ADD COLUMN IF NOT EXISTS `sold_serial_number` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `sold_imei_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `price_type` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT 'wholesale',
  ADD COLUMN IF NOT EXISTS `staff_id` int(10) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `commission_amount` double(20,2) DEFAULT 0.00;

-- db_sitesettings: add sales_target column for dashboard daily target
ALTER TABLE `db_sitesettings` ADD COLUMN IF NOT EXISTS `sales_target` DOUBLE(20,4) DEFAULT 0;
