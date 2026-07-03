-- MartPoint Retail v4.0.0 -> v4.0.1
-- Purchase Batch Tracking & Partial Receipt Migration
-- Run this SQL manually via phpMyAdmin or MySQL CLI before using the new purchase workflow

ALTER TABLE `db_purchaseitems`
  ADD COLUMN `received_qty` DOUBLE(20,4) NULL AFTER `purchase_qty`,
  ADD COLUMN `barcode` VARCHAR(100) NULL AFTER `batch_lot`,
  ADD COLUMN `expire_date` DATE NULL AFTER `barcode`,
  ADD COLUMN `mfg_date` DATE NULL AFTER `expire_date`;

-- Optional: update version marker (only if you want to bump the app version check)
-- UPDATE `db_sitesettings` SET `version` = '4.0.1' WHERE `id` = '1';
