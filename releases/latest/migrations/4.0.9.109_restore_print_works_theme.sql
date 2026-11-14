-- ============================================================================
-- MartPoint 4.0.9.109 — restore PrintWorks as the fourth printing theme
--
-- HISTORY, because this looks like a reversal without it.
--
--   .96 registered print_works as a printing theme (status = 1, sort 19).
--   .97 retired it: "The old fourth printing design is replaced by the
--       approved three-theme set" — it set status = 0 and migrated every
--       printing store onto print_inkpress, repointing both
--       db_store.storefront_theme_key and
--       db_store_industry_settings.storefront_theme_key.
--
-- That retirement is now itself reversed. PrintWorks is wanted back as the
-- fourth theme alongside InkPress / PaperCraft / NeonPrint, so this migration
-- switches it back on.
--
-- WHAT THIS DOES NOT DO: it does not move any store back onto print_works.
-- .97 already moved stores to press-and-co; re-enabling the theme only makes
-- it AVAILABLE in the picker. A store that chose another theme keeps it —
-- silently swapping a live storefront would be a worse bug than the one this
-- fixes. Stores that still have no theme, or that were moved off print_works
-- by .97, are handled by .97's own logic and stay where they are.
--
-- Idempotent: safe to re-run.
-- ============================================================================

-- 1. Re-enable the theme, restoring the registration .96 intended.
--
-- Guarded with an existence check because a fresh install runs every
-- migration in order: .96 creates the row, .97 disables it, and this file
-- re-enables it. If a future install ever seeds the row directly, the UPDATE
-- still applies and the INSERT is skipped.
INSERT INTO `db_storefront_themes`
  (`theme_key`, `theme_name`, `industry`, `description`,
   `default_primary_color`, `default_secondary_color`, `default_font_family`,
   `status`, `sort_order`)
SELECT
  'print_works', 'PrintWorks', 'printing',
  'Restrained modern print-shop theme. One accent colour that adapts to your branding, a single-row header, services-first layout and a clear quote path. No blog, no pricing tables, no team grid.',
  '#0E7490', '#0F172A', 'Inter', 1, 19
WHERE NOT EXISTS (
  SELECT 1 FROM `db_storefront_themes` WHERE `theme_key` = 'print_works'
);

UPDATE `db_storefront_themes`
SET `status`     = 1,
    `industry`   = 'printing',
    `sort_order` = 19
WHERE `theme_key` = 'print_works';

-- 2. Make sure the industry is set even if an older row drifted.
--
-- A theme with the wrong industry never appears for a printing store, which
-- would look exactly like the migration having failed.
UPDATE `db_storefront_themes`
SET `industry` = 'printing'
WHERE `theme_key` = 'print_works' AND (`industry` IS NULL OR `industry` <> 'printing');
