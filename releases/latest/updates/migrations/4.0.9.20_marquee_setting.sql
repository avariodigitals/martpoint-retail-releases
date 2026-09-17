-- ============================================================================
-- MartPoint 4.0.9.20 — Editable announcement marquee items
-- Adds db_storefront_settings.marquee_items (newline-separated ticker items)
-- used by urban_fashion header; falls back to theme defaults when empty.
-- ============================================================================

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_storefront_settings' AND column_name = 'marquee_items');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_storefront_settings` ADD COLUMN `marquee_items` TEXT DEFAULT NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
