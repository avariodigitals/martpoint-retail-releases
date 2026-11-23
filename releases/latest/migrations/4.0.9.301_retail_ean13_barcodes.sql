-- Optional product barcode type; existing records remain internal Code 128.
-- Safe to run repeatedly on PHP 7.4 / CodeIgniter 3 installations.
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'barcode_type');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_items` ADD COLUMN `barcode_type` VARCHAR(10) NOT NULL DEFAULT ''code128'' AFTER `custom_barcode`',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
