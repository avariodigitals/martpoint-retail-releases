-- Merchant-controlled sale and availability labels.
-- Enabled by default so existing storefronts and POS screens retain their
-- current pricing/stock visibility after the migration.

SET @db := DATABASE();

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_storefront_settings'
    AND COLUMN_NAME='show_sale_stock_labels')=0,
  'ALTER TABLE db_storefront_settings ADD COLUMN `show_sale_stock_labels` TINYINT(1) NOT NULL DEFAULT 1',
  'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
