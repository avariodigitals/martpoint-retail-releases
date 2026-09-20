-- ----------------------------------------------------------------------------
-- Perfumery storefront themes (4 luxury designs)
-- Idempotent: theme_key is unique; INSERT IGNORE skips existing rows.
-- ----------------------------------------------------------------------------

INSERT IGNORE INTO `db_storefront_themes` (`theme_key`,`theme_name`,`industry`,`description`,`default_primary_color`,`default_secondary_color`,`default_font_family`,`sort_order`,`status`) VALUES
('noir_parfum','Noir Parfum','perfumery','Midnight luxury flagship theme — deep black, champagne gold and italic serif typography for a dramatic haute-parfumerie storefront.','#C9A961','#0B0A08','Cormorant Garamond',32,1),
('maison_blanche','Maison Blanche','perfumery','Ivory Parisian maison theme — cream canvas, black ink and old-gold hairlines for a refined French fragrance boutique.','#A98954','#1C1917','Playfair Display',33,1),
('oud_royale','Oud Royale','perfumery','Arabian opulence theme — espresso darkness, royal gold and arched gallery for oud, attar and musk houses.','#D4A24E','#150E07','Marcellus',34,1),
('atelier_essence','Atelier Essence','perfumery','Niche-lab minimalism — bone white, mono ink and stark grid for artisan perfumeries and custom formulation labs.','#161513','#9C4A2F','Inter',35,1);

-- Repoint perfume-shop businesses to the dedicated perfumery theme group.
UPDATE `db_store_business_profile` SET `storefront_theme_key` = 'noir_parfum'
  WHERE `industry_type` = 'perfume_shop'
    AND (`storefront_theme_key` IS NULL OR `storefront_theme_key` = '' OR `storefront_theme_key` = 'beauty_luxe');

UPDATE `db_store_industry_settings` SET `storefront_theme_key` = 'noir_parfum'
  WHERE `industry_type` = 'perfume_shop'
    AND (`storefront_theme_key` IS NULL OR `storefront_theme_key` = '' OR `storefront_theme_key` = 'beauty_luxe');
