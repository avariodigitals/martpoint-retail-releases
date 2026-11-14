-- ----------------------------------------------------------------------------
-- Skincare / organic cosmetics storefront themes (3 world-class designs)
-- Idempotent: theme_key is unique; INSERT IGNORE skips existing rows.
-- ----------------------------------------------------------------------------

INSERT IGNORE INTO `db_storefront_themes` (`theme_key`,`theme_name`,`industry`,`description`,`default_primary_color`,`default_secondary_color`,`default_font_family`,`sort_order`,`status`) VALUES
('botanica','Botanica','skincare','Editorial botanical flagship — warm cream canvas, forest ink, sage accents and arched product imagery for organic, made-from-scratch skincare brands.','#4A7C59','#22302A','Fraunces',39,1),
('derma_pure','Derma Pure','skincare','Clinical minimal lab theme — crisp white, ink and derma-teal with mono labels for science-led skincare and formulation brands.','#2F6B5E','#0F172A','Inter',40,1),
('terra_glow','Terra Glow','skincare','Warm earth-luxe theme — sand canvas, clay ink and terracotta accents with plush rounded cards for shea, butter and glow-focused brands.','#B5643C','#31221A','Cormorant Garamond',41,1);

-- Repoint skincare businesses to the dedicated flagship theme.
UPDATE `db_store_business_profile` SET `storefront_theme_key` = 'botanica'
  WHERE `industry_type` = 'skincare'
    AND (`storefront_theme_key` IS NULL OR `storefront_theme_key` = '' OR `storefront_theme_key` = 'general_retail');

UPDATE `db_store_industry_settings` SET `storefront_theme_key` = 'botanica'
  WHERE `industry_type` = 'skincare'
    AND (`storefront_theme_key` IS NULL OR `storefront_theme_key` = '' OR `storefront_theme_key` = 'general_retail');
