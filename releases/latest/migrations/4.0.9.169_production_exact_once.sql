-- RestaurantOS / production accountability (v4.0.9.169)
-- Trace made-to-stock movements to their originating production batch.
-- This extends the existing stock ledger; it does not create a second ledger.

SET @db := DATABASE();

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_stockadjustment' AND COLUMN_NAME='source_production_batch_id')=0,
  'ALTER TABLE db_stockadjustment ADD COLUMN `source_production_batch_id` INT(11) NULL DEFAULT NULL AFTER `source_order_id`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_stockadjustment' AND INDEX_NAME='idx_stock_source_production')=0,
  'ALTER TABLE db_stockadjustment ADD INDEX `idx_stock_source_production` (`store_id`,`source_production_batch_id`,`source_action`)', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_stockadjustment' AND INDEX_NAME='uk_stock_source_production')=0,
  'ALTER TABLE db_stockadjustment ADD UNIQUE KEY `uk_stock_source_production` (`store_id`,`source_production_batch_id`,`source_action`)', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
