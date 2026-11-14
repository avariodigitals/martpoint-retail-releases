-- 4.0.9.180 — store-level opt-in for controlled partner catalogue/fulfilment
-- Additive and idempotent. Default 0 keeps all existing storefronts unchanged.

SET @db := DATABASE();
SET @has_partner_marketplace_enabled := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = @db
    AND TABLE_NAME = 'db_storefront_settings'
    AND COLUMN_NAME = 'partner_marketplace_enabled'
);
SET @partner_marketplace_enabled_sql := IF(@has_partner_marketplace_enabled = 0,
  'ALTER TABLE `db_storefront_settings` ADD COLUMN `partner_marketplace_enabled` TINYINT(1) NOT NULL DEFAULT 0',
  'SELECT 1');
PREPARE partner_marketplace_enabled_stmt FROM @partner_marketplace_enabled_sql;
EXECUTE partner_marketplace_enabled_stmt;
DEALLOCATE PREPARE partner_marketplace_enabled_stmt;
