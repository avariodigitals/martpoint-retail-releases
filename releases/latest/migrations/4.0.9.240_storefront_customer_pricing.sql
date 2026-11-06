-- Historical online prices keep the customer rule used when ordering.
-- Additive and replay-safe; never changes catalogue prices or old orders.
SET @db := DATABASE();
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_online_order_items' AND COLUMN_NAME='pricing_snapshot')=0, 'ALTER TABLE db_online_order_items ADD COLUMN pricing_snapshot TEXT NULL', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
