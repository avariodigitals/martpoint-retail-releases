-- ============================================================================
-- MartPoint 4.0.9.96 — PrintWorks theme + catalogue mode
--
-- A restrained, modern printing theme: one accent colour that blends with the
-- store's own branding (no colour riot), a single-row header, and no blog /
-- pricing / team sections (those are agency tropes, not print-shop ones).
--
-- Also adds a CATALOGUE MODE so the store decides what its storefront sells:
--   services only · products only · both
-- Printing defaults to services. The storefront respects it everywhere: nav
-- labels, section defaults and cart availability.
--
-- Idempotent.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- ---- the theme -------------------------------------------------------------

INSERT IGNORE INTO `db_storefront_themes`
  (`theme_key`, `theme_name`, `industry`, `description`, `default_primary_color`, `default_secondary_color`, `default_font_family`, `status`, `sort_order`)
VALUES
  ('print_works', 'PrintWorks', 'printing',
   'Restrained modern print-shop theme. One accent colour that adapts to your branding, a single-row header, services-first layout and a clear quote path. No blog, no pricing tables, no team grid.',
   '#0E7490', '#0F172A', 'Inter', 1, 19);

-- ---- catalogue mode (what the storefront sells) ----------------------------

SET @sql = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_storefront_settings'
       AND COLUMN_NAME = 'catalogue_mode') = 0,
  'ALTER TABLE `db_storefront_settings` ADD COLUMN `catalogue_mode` VARCHAR(16) NOT NULL DEFAULT ''products'' COMMENT ''services|products|both''',
  'SELECT 1'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Printing stores default to a service-led storefront.
--
-- The guard above may SKIP the ALTER (column already present) — but on an
-- install where it is missing, the ALTER can also fail for reasons the guard
-- cannot see (a stale information_schema, a restricted account), and this
-- UPDATE then dies on an unknown column. That is a hard stop: step 6 throws,
-- the version never advances, and the install is stuck for ever.
--
-- Re-assert the column here so this file is self-sufficient and does not rely
-- on the earlier guarded ALTER having succeeded.
SET @sql2 = (SELECT IF(
  (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'db_storefront_settings'
       AND COLUMN_NAME = 'catalogue_mode') = 0,
  'ALTER TABLE `db_storefront_settings` ADD COLUMN `catalogue_mode` VARCHAR(16) NOT NULL DEFAULT ''products''',
  'DO 0'));
PREPARE stmt2 FROM @sql2; EXECUTE stmt2; DEALLOCATE PREPARE stmt2;

UPDATE `db_storefront_settings` s
JOIN `db_store_industry_settings` i ON i.store_id = s.store_id
SET s.catalogue_mode = 'services'
WHERE i.industry_type = 'printing'
  AND (s.catalogue_mode IS NULL OR s.catalogue_mode = '' OR s.catalogue_mode = 'products');

SET FOREIGN_KEY_CHECKS = 1;
