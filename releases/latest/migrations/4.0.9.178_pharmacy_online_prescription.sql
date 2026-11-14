-- 4.0.9.178 — optional prescription attachment for pharmacy storefront orders
--
-- Additive and idempotent. The storefront only writes this field for pharmacy
-- orders; all existing online orders and non-pharmacy stores are unchanged.

SET @db := DATABASE();

SET @has_prescription_file := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'db_online_orders'
    AND COLUMN_NAME = 'prescription_file'
);
SET @prescription_file_sql := IF(@has_prescription_file = 0,
  'ALTER TABLE `db_online_orders` ADD COLUMN `prescription_file` VARCHAR(255) NULL AFTER `customer_address`',
  'SELECT 1');
PREPARE prescription_file_stmt FROM @prescription_file_sql;
EXECUTE prescription_file_stmt;
DEALLOCATE PREPARE prescription_file_stmt;
