-- Three skincare storefront presets matching the Lumière / Skinora / Bella Rêve reference direction.
INSERT IGNORE INTO db_storefront_themes
  (theme_key, theme_name, industry, description, default_primary_color, default_secondary_color, default_font_family, sort_order, status)
VALUES
  ('lumiere','Lumière','skincare','Cream editorial skincare boutique with dark forest accents, serif headlines and clean product-led sections.','#173A2B','#151713','Cormorant Garamond',43,1),
  ('skinora','Skinora','skincare','Blush premium skincare storefront with editorial serif type, soft rose surfaces and conversion-focused collections.','#6A2730','#311D1E','Playfair Display',44,1),
  ('bella_reve','Bella Rêve','skincare','Dark luxury spa storefront with warm gold accents, treatment booking and premium product merchandising.','#D3A74C','#10130D','Cormorant Garamond',45,1);

UPDATE db_storefront_themes SET industry='skincare' WHERE theme_key IN ('lumiere','skinora','bella_reve');
