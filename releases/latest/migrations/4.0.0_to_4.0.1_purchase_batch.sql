-- MartPoint Retail v4.0.0 -> v4.0.1
-- Purchase Batch Tracking & Partial Receipt Migration
-- Run this SQL manually via phpMyAdmin or MySQL CLI before using the new purchase workflow
-- Idempotent: safe to re-run on installations that already have these columns.

SET @col_exists = (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = DATABASE()
  AND table_name = 'db_purchaseitems'
  AND column_name = 'received_qty'
);

SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_purchaseitems` ADD COLUMN `received_qty` DOUBLE(20,4) NULL AFTER `purchase_qty`, ADD COLUMN `barcode` VARCHAR(100) NULL AFTER `batch_lot`, ADD COLUMN `expire_date` DATE NULL AFTER `barcode`, ADD COLUMN `mfg_date` DATE NULL AFTER `expire_date`',
  'SELECT 1'
);

PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Optional: update version marker (only if you want to bump the app version check)
-- UPDATE `db_sitesettings` SET `version` = '4.0.1' WHERE `id` = '1';
