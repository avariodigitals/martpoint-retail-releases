-- Merchant-owned presentation copy and editorial assets, namespaced by theme.
-- No catalogue, commerce or payment data changes. Idempotent on upgraded stores.
SET @mp_content_sql := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_storefront_settings' AND COLUMN_NAME='theme_content_json')=0,
 'ALTER TABLE db_storefront_settings ADD COLUMN theme_content_json MEDIUMTEXT NULL', 'SELECT 1');
PREPARE mp_content_stmt FROM @mp_content_sql;
EXECUTE mp_content_stmt;
DEALLOCATE PREPARE mp_content_stmt;
