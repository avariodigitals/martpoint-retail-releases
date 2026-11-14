-- Optional footer-only logo for the shared retail storefront shell.
-- Existing stores keep using their current store logo until a footer logo is
-- uploaded; this is deliberately additive and safe for existing fleets.

SET @db := DATABASE();

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS
  WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_storefront_settings'
    AND COLUMN_NAME='footer_logo')=0,
  'ALTER TABLE db_storefront_settings ADD COLUMN `footer_logo` VARCHAR(255) NULL DEFAULT NULL',
  'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
