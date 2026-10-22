-- RestaurantOS M3 operational controls (additive / repeatable)
-- PHP 7.4 / CodeIgniter 3. Existing ledgers remain authoritative.

SET @db := DATABASE();

-- Bind payments to the exact till shift. Legacy rows remain valid and are
-- still attributable by cashier/date when the column is NULL.
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_salespayments' AND COLUMN_NAME='cashier_shift_id')=0,
  'ALTER TABLE db_salespayments ADD COLUMN `cashier_shift_id` INT(11) NULL DEFAULT NULL AFTER `sales_id`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_online_orders' AND COLUMN_NAME='delivery_status')=0,
  'ALTER TABLE db_online_orders ADD COLUMN `delivery_status` VARCHAR(24) NULL DEFAULT NULL AFTER `fulfilment_type`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_salespayments' AND INDEX_NAME='idx_salespayments_shift')=0,
  'ALTER TABLE db_salespayments ADD INDEX `idx_salespayments_shift` (`store_id`,`cashier_shift_id`,`payment_date`)', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_salespaymentsreturn' AND COLUMN_NAME='cashier_shift_id')=0,
  'ALTER TABLE db_salespaymentsreturn ADD COLUMN `cashier_shift_id` INT(11) NULL DEFAULT NULL AFTER `return_id`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- A stable client key makes a lost response safe to retry. It is intentionally
-- nullable so historical sales and ordinary non-offline POS remain unchanged.
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_sales' AND COLUMN_NAME='pos_idempotency_key')=0,
  'ALTER TABLE db_sales ADD COLUMN `pos_idempotency_key` VARCHAR(96) NULL DEFAULT NULL AFTER `source_channel`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @s := IF((SELECT COUNT(*) FROM information_schema.STATISTICS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_sales' AND INDEX_NAME='uk_sales_pos_idempotency')=0,
  'ALTER TABLE db_sales ADD UNIQUE KEY `uk_sales_pos_idempotency` (`store_id`,`pos_idempotency_key`)', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Explicit operator-visible status for queued replay conflicts.
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_sales' AND COLUMN_NAME='offline_sync_status')=0,
  'ALTER TABLE db_sales ADD COLUMN `offline_sync_status` VARCHAR(20) NULL DEFAULT NULL AFTER `pos_idempotency_key`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
