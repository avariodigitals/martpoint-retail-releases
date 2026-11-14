-- 4.0.9.176 — idempotency for manual invoice receipts
--
-- Additive and idempotent. A nullable key is unique per store, so existing
-- rows and legacy callers that do not send a key keep working. New payment
-- forms send a stable key; a retried submission resolves to the same receipt
-- instead of creating a duplicate.

SET @db := DATABASE();

SET @has_key := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_salespayments' AND COLUMN_NAME = 'idempotency_key'
);
SET @key_sql := IF(@has_key = 0,
  'ALTER TABLE `db_salespayments` ADD COLUMN `idempotency_key` VARCHAR(80) NULL AFTER `payment_code`',
  'SELECT 1');
PREPARE key_stmt FROM @key_sql;
EXECUTE key_stmt;
DEALLOCATE PREPARE key_stmt;

SET @has_index := (
  SELECT COUNT(*) FROM information_schema.STATISTICS
  WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'db_salespayments' AND INDEX_NAME = 'uniq_store_idempotency'
);
SET @index_sql := IF(@has_index = 0,
  'ALTER TABLE `db_salespayments` ADD UNIQUE KEY `uniq_store_idempotency` (`store_id`,`idempotency_key`)',
  'SELECT 1');
PREPARE index_stmt FROM @index_sql;
EXECUTE index_stmt;
DEALLOCATE PREPARE index_stmt;
