-- RestaurantOS M2 canonical order spine (v4.0.9.168)
-- Additive and repeatable. The existing db_sales ledger remains the only
-- financial ledger; these fields only link operational orders to it.

SET @db := DATABASE();

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_online_orders' AND COLUMN_NAME='idempotency_key')=0,
  'ALTER TABLE db_online_orders ADD COLUMN `idempotency_key` VARCHAR(96) NULL DEFAULT NULL AFTER `order_code`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_online_orders' AND COLUMN_NAME='fulfilment_type')=0,
  'ALTER TABLE db_online_orders ADD COLUMN `fulfilment_type` VARCHAR(20) NULL DEFAULT NULL AFTER `source_channel`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_online_orders' AND COLUMN_NAME='sales_id')=0,
  'ALTER TABLE db_online_orders ADD COLUMN `sales_id` INT(11) NOT NULL DEFAULT 0 AFTER `fulfilled_at`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_online_orders' AND INDEX_NAME='uk_store_idempotency')=0,
  'ALTER TABLE db_online_orders ADD UNIQUE KEY `uk_store_idempotency` (`store_id`,`idempotency_key`)', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_online_orders' AND INDEX_NAME='idx_order_sales')=0,
  'ALTER TABLE db_online_orders ADD INDEX `idx_order_sales` (`store_id`,`sales_id`)', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_sales' AND COLUMN_NAME='online_order_id')=0,
  'ALTER TABLE db_sales ADD COLUMN `online_order_id` INT(11) NULL DEFAULT NULL AFTER `warehouse_id`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_sales' AND COLUMN_NAME='restaurant_order_key')=0,
  'ALTER TABLE db_sales ADD COLUMN `restaurant_order_key` VARCHAR(96) NULL DEFAULT NULL AFTER `online_order_id`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_sales' AND COLUMN_NAME='source_channel')=0,
  'ALTER TABLE db_sales ADD COLUMN `source_channel` VARCHAR(20) NULL DEFAULT NULL AFTER `restaurant_order_key`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_sales' AND INDEX_NAME='uk_sales_online_order')=0,
  'ALTER TABLE db_sales ADD UNIQUE KEY `uk_sales_online_order` (`store_id`,`online_order_id`)', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_sales' AND INDEX_NAME='uk_store_restaurant_order_key')=0,
  'ALTER TABLE db_sales ADD UNIQUE KEY `uk_store_restaurant_order_key` (`store_id`,`restaurant_order_key`)', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- sales_id=0 was historically used for online tickets. A UNIQUE index on
-- sales_id therefore allowed only one online/QR ticket per store. Replace it
-- with ordinary lookup indexes; the lifecycle model prevents duplicate rows.
SET @s := IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_kitchen_orders' AND INDEX_NAME='uk_sales' AND NON_UNIQUE=0)>0,
  'ALTER TABLE db_kitchen_orders DROP INDEX `uk_sales`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_kitchen_orders' AND INDEX_NAME='idx_kitchen_sales')=0,
  'ALTER TABLE db_kitchen_orders ADD INDEX `idx_kitchen_sales` (`store_id`,`sales_id`)', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_kitchen_orders' AND INDEX_NAME='idx_kitchen_online')=0,
  'ALTER TABLE db_kitchen_orders ADD INDEX `idx_kitchen_online` (`store_id`,`online_order_id`)', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_stockadjustment' AND COLUMN_NAME='source_order_id')=0,
  'ALTER TABLE db_stockadjustment ADD COLUMN `source_order_id` INT(11) NOT NULL DEFAULT 0 AFTER `warehouse_id`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_stockadjustment' AND COLUMN_NAME='source_action')=0,
  'ALTER TABLE db_stockadjustment ADD COLUMN `source_action` VARCHAR(20) NULL DEFAULT NULL AFTER `source_order_id`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_stockadjustment' AND INDEX_NAME='idx_stock_source_order')=0,
  'ALTER TABLE db_stockadjustment ADD INDEX `idx_stock_source_order` (`store_id`,`source_order_id`,`source_action`)', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_stockadjustment' AND INDEX_NAME='uk_stock_source_action')=0,
  'ALTER TABLE db_stockadjustment ADD UNIQUE KEY `uk_stock_source_action` (`store_id`,`source_order_id`,`source_action`)', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_kitchen_orders' AND COLUMN_NAME='warehouse_id')=0,
  'ALTER TABLE db_kitchen_orders ADD COLUMN `warehouse_id` INT(11) NOT NULL DEFAULT 0 AFTER `store_id`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_kitchen_orders' AND INDEX_NAME='idx_kitchen_warehouse')=0,
  'ALTER TABLE db_kitchen_orders ADD INDEX `idx_kitchen_warehouse` (`store_id`,`warehouse_id`,`kds_status`)', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
