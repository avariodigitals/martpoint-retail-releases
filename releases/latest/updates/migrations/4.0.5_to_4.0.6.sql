-- MartPoint 4.0.5 -> 4.0.6 Migration
-- Adds the new Laundry "Fresh" storefront theme (laundry_fresh) to the theme registry.
-- Also fixes the store_status ENUM to include 'deactivated' (was silently dropped on save).
-- Idempotent: INSERT IGNORE prevents errors on re-runs or if the theme already exists.
-- --------------------------------------------------------

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- 1. Add the new Laundry Fresh theme to the registry
-- --------------------------------------------------------
INSERT IGNORE INTO `db_storefront_themes`
(`theme_key`, `theme_name`, `industry`, `description`, `default_primary_color`, `default_secondary_color`, `default_font_family`, `status`, `sort_order`)
VALUES
('laundry_fresh', 'Fresh', 'Laundry & Dry Cleaning', 'A clean, modern storefront designed for laundries, dry cleaners and garment-care businesses.', '#102A43', '#2F80ED', 'Inter', 1, 10);

-- 2. Fix store_status ENUM: add 'deactivated' so the Store Settings form can save it
--    Without this, selecting "Deactivated" silently stores '' and the save appears to fail.
-- --------------------------------------------------------
SET @col_type = (SELECT COLUMN_TYPE FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_storefront_settings' AND column_name = 'store_status');
SET @sql = IF(@col_type = 'enum(\'active\',\'maintenance\')',
  'ALTER TABLE `db_storefront_settings` MODIFY COLUMN `store_status` ENUM(\'active\',\'maintenance\',\'deactivated\') DEFAULT \'active\'',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3. Record that this migration has run
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `db_schema_migrations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `version` VARCHAR(20) NOT NULL,
  `applied_at` DATETIME NOT NULL,
  UNIQUE KEY `uk_version` (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `db_schema_migrations` (`version`, `applied_at`) VALUES ('4.0.6', NOW());

-- 3. Bump the application version
-- --------------------------------------------------------
UPDATE `db_sitesettings` SET `version` = '4.0.6';

SET FOREIGN_KEY_CHECKS = 1;
