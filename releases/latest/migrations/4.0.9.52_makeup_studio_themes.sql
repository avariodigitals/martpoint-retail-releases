-- ----------------------------------------------------------------------------
-- Beauty / makeup studio storefront themes (3 world-class designs)
-- Idempotent: theme_key is unique; INSERT IGNORE skips existing rows.
-- ----------------------------------------------------------------------------

INSERT IGNORE INTO `db_storefront_themes` (`theme_key`,`theme_name`,`industry`,`description`,`default_primary_color`,`default_secondary_color`,`default_font_family`,`sort_order`,`status`) VALUES
('glam_atelier','Glam Atelier','beauty','Editorial makeup-studio flagship — porcelain canvas, espresso ink, rose-gold hairlines and Cormorant serif for a premium artist brand.','#B76E79','#241B18','Cormorant Garamond',36,1),
('velvet_glow','Velvet Glow','beauty','Warm velvet beauty theme — deep berry, blush silk and plush glowing cards for salons and cosmetics boutiques.','#8E3B5E','#E9B8C4','Playfair Display',37,1),
('studio_blanc','Studio Blanc','beauty','Clean ivory minimalism — crisp black ink, terracotta accents and airy product grids for modern beauty retail.','#111111','#C98A6B','Jost',38,1);

-- Repoint makeup-studio businesses to the dedicated flagship theme.
UPDATE `db_store_business_profile` SET `storefront_theme_key` = 'glam_atelier'
  WHERE `industry_type` = 'makeup_studio'
    AND (`storefront_theme_key` IS NULL OR `storefront_theme_key` = '' OR `storefront_theme_key` = 'beauty_luxe' OR `storefront_theme_key` = 'general_retail');

UPDATE `db_store_industry_settings` SET `storefront_theme_key` = 'glam_atelier'
  WHERE `industry_type` = 'makeup_studio'
    AND (`storefront_theme_key` IS NULL OR `storefront_theme_key` = '' OR `storefront_theme_key` = 'beauty_luxe' OR `storefront_theme_key` = 'general_retail');
