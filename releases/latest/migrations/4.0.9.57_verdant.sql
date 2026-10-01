-- ----------------------------------------------------------------------------
-- Verdant — calm editorial skincare storefront theme (4th skincare preset)
-- Idempotent: theme_key is unique; INSERT IGNORE skips existing rows.
-- ----------------------------------------------------------------------------

INSERT IGNORE INTO `db_storefront_themes` (`theme_key`,`theme_name`,`industry`,`description`,`default_primary_color`,`default_secondary_color`,`default_font_family`,`sort_order`,`status`) VALUES
('verdant','Verdant','skincare','Calm editorial flagship — cream canvas, deep forest bands, sage accents and serif typography with concern-led shopping, journal and routine sets, all driven by the Online Store backend.','#4F7A5C','#1F3A2E','Instrument Serif',42,1);

-- New skincare businesses get Verdant as their default storefront theme.
UPDATE `db_store_business_profile` SET `storefront_theme_key` = 'verdant'
  WHERE `industry_type` = 'skincare'
    AND (`storefront_theme_key` IS NULL OR `storefront_theme_key` = '' OR `storefront_theme_key` = 'general_retail');

UPDATE `db_store_industry_settings` SET `storefront_theme_key` = 'verdant'
  WHERE `industry_type` = 'skincare'
    AND (`storefront_theme_key` IS NULL OR `storefront_theme_key` = '' OR `storefront_theme_key` = 'general_retail');

-- Editable page background (Online Store → Appearance). Empty = theme canvas.
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_storefront_settings' AND column_name = 'background_color');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_storefront_settings` ADD COLUMN `background_color` VARCHAR(20) NULL DEFAULT ''''',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
