-- Pharmacy storefront locations and stock-source mapping.
-- Additive and shared-safe: ordinary stores keep the existing branch defaults.
SET @db := DATABASE();

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_warehouse' AND COLUMN_NAME='stock_source_id')=0,
  'ALTER TABLE db_warehouse ADD COLUMN `stock_source_id` INT(11) NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_warehouse' AND COLUMN_NAME='business_hours')=0,
  'ALTER TABLE db_warehouse ADD COLUMN `business_hours` VARCHAR(255) NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_warehouse' AND COLUMN_NAME='delivery_coverage')=0,
  'ALTER TABLE db_warehouse ADD COLUMN `delivery_coverage` VARCHAR(500) NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_warehouse' AND COLUMN_NAME='delivery_fee')=0,
  'ALTER TABLE db_warehouse ADD COLUMN `delivery_fee` DECIMAL(20,2) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_warehouse' AND COLUMN_NAME='pickup_enabled')=0,
  'ALTER TABLE db_warehouse ADD COLUMN `pickup_enabled` TINYINT(1) NOT NULL DEFAULT 1', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_warehouse' AND COLUMN_NAME='public_enabled')=0,
  'ALTER TABLE db_warehouse ADD COLUMN `public_enabled` TINYINT(1) NOT NULL DEFAULT 1', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_online_orders' AND COLUMN_NAME='source_warehouse_id')=0,
  'ALTER TABLE db_online_orders ADD COLUMN `source_warehouse_id` INT(11) NULL DEFAULT NULL AFTER `warehouse_id`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_online_orders' AND INDEX_NAME='idx_order_source_warehouse')=0,
  'ALTER TABLE db_online_orders ADD INDEX `idx_order_source_warehouse` (`store_id`, `source_warehouse_id`)', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
