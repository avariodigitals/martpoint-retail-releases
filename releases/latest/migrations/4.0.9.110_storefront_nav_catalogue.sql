-- ============================================================================
-- MartPoint 4.0.9.110 — storefront nav: service-led menu and enquiry path
--
-- No schema change. This migration exists to carry the release forward with a
-- matching migration file, because a version that ships UI changes with no
-- migration cannot be distinguished from a version that shipped nothing.
--
-- WHAT CHANGED (code, not schema):
--
--   * The shared storefront header now composes its menu from two resolved
--     facts — sells products, sells services — instead of testing the raw
--     flags at each anchor.
--   * A service storefront LEADS with Services and gains a "Request a Quote"
--     enquiry link. A product store keeps All Products first and gets no
--     enquiry link.
--   * Product categories are shown only where products are the catalogue. On
--     a printing store those categories (Paper, Ink, Binding…) are browsing
--     aids for a shop the store refuses to operate, so they no longer leak
--     into the menu.
--   * Appearance gains the "What You Sell" control (Services / Products /
--     Both) inside the form that posts, and save_appearance persists it.
--
-- The data half of that change is the catalogue_mode column, already added by
-- .96. This file only ensures the column exists on installs that upgraded
-- from a build predating .96 and somehow missed it, and that no storefront is
-- left with a value the theme engine cannot resolve — an unreadable mode
-- makes every catalogue decision undefined.
-- ============================================================================

-- 1. Re-assert the column. Guarded, and safe to re-run.
SET @has_mode = (
  SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME   = 'db_storefront_settings'
    AND COLUMN_NAME  = 'catalogue_mode'
);
SET @sql_mode = IF(@has_mode = 0,
  'ALTER TABLE `db_storefront_settings` ADD COLUMN `catalogue_mode` VARCHAR(16) NOT NULL DEFAULT ''products'' COMMENT ''services|products|both''',
  'DO 0');
PREPARE st FROM @sql_mode; EXECUTE st; DEALLOCATE PREPARE st;

-- 2. Normalise anything unrecognised (including NULL / '') to 'products'.
--
-- Deliberately NOT 'services': defaulting an ordinary retail storefront to a
-- service catalogue would hide its products and remove its cart. 'products' is
-- the same fallback Theme_engine::catalogueMode() uses, so the stored value
-- and the engine's interpretation agree.
UPDATE `db_storefront_settings`
SET `catalogue_mode` = 'products'
WHERE `catalogue_mode` IS NULL
   OR `catalogue_mode` = ''
   OR `catalogue_mode` NOT IN ('services', 'products', 'both');
