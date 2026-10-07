-- Migration: Add allow_products_online flag to storefront settings
-- Purpose: Allow service-based stores (printing, tailoring) to optionally sell products online
-- Version: 4.0.9.96
-- Safe to re-run: MySQL lacks "ADD COLUMN IF NOT EXISTS", so the column is
-- created by a prepared statement that no-ops when it already exists.

SET @col_exists := (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME   = 'db_storefront_settings'
    AND COLUMN_NAME  = 'allow_products_online'
);
SET @ddl := IF(@col_exists = 0,
  'ALTER TABLE `db_storefront_settings`
     ADD COLUMN `allow_products_online` TINYINT(1) NOT NULL DEFAULT 0
     COMMENT ''For service stores: allow selling products online (enables cart)''',
  'SELECT 1');
PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- Service stores default to no online product sales.
UPDATE `db_storefront_settings` s
INNER JOIN `db_store_industry_settings` sid ON s.store_id = sid.store_id
SET s.allow_products_online = 0
WHERE sid.industry_type IN ('printing', 'tailoring')
  AND s.catalogue_mode = 'services';
