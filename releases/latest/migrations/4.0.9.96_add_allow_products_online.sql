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
--
-- This file depends on TWO columns it does not create: allow_products_online
-- (guarded above) and catalogue_mode (created by its sibling migration). If
-- either is absent the UPDATE dies on an unknown column, step 6 throws, and
-- the install is stuck for ever — which is exactly what happened on two live
-- installs. Re-assert BOTH here so this file stands alone.
SET @sql2 = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_storefront_settings'
       AND COLUMN_NAME = 'allow_products_online') = 0,
  'ALTER TABLE `db_storefront_settings` ADD COLUMN `allow_products_online` TINYINT(1) NOT NULL DEFAULT 0',
  'DO 0'));
PREPARE stmt2 FROM @sql2; EXECUTE stmt2; DEALLOCATE PREPARE stmt2;

SET @sql3 = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_storefront_settings'
       AND COLUMN_NAME = 'catalogue_mode') = 0,
  'ALTER TABLE `db_storefront_settings` ADD COLUMN `catalogue_mode` VARCHAR(16) NOT NULL DEFAULT ''products''',
  'DO 0'));
PREPARE stmt3 FROM @sql3; EXECUTE stmt3; DEALLOCATE PREPARE stmt3;

UPDATE `db_storefront_settings` s
INNER JOIN `db_store_industry_settings` sid ON s.store_id = sid.store_id
SET s.allow_products_online = 0
WHERE sid.industry_type IN ('printing', 'tailoring')
  AND s.catalogue_mode = 'services';
