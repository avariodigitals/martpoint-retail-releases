-- 4.0.9.174 — Framework Atelier & Vector Systems
--
-- Two premium Building Materials catalogue themes for the existing 'hardware'
-- theme group. Additive and idempotent: existing theme selections are never
-- touched, and a store keeps whatever theme_id it already has. These rows are
-- what make the themes discoverable in Online Store → Appearance for any
-- store using the building_materials / paint / plumbing business presets.
--
-- Layout and skin live in:
--   application/views/themes/building_materials_base/   (shared, unregistered)
--   application/views/themes/framework_atelier/         (wrapper skin)
--   application/views/themes/vector_systems/            (wrapper skin)
--
-- preview_image is a real screenshot of the rendered theme. Until a merchant
-- uploads one it falls back to a CSS-drawn card, so the column may stay NULL.

INSERT INTO `db_storefront_themes`
  (`theme_key`, `theme_name`, `industry`, `description`,
   `default_primary_color`, `default_secondary_color`, `default_font_family`,
   `preview_image`, `status`, `sort_order`)
VALUES
  ('framework_atelier', 'Framework Atelier', 'hardware',
   'Editorial architectural-systems catalogue with ivory surfaces, deep ink, serif display type and project-led enquiry journeys.',
   '#245C94', '#142438', 'Playfair Display',
   'uploads/site/theme-previews/framework_atelier.jpg', 1, 49),
  ('vector_systems', 'Vector Systems', 'hardware',
   'Precision industrial catalogue with cool silver surfaces, copper accents, Swiss-grid typography and technical specification tables.',
   '#E16D3B', '#111820', 'Manrope',
   'uploads/site/theme-previews/vector_systems.jpg', 1, 50)
ON DUPLICATE KEY UPDATE
  `theme_name` = VALUES(`theme_name`),
  `industry` = VALUES(`industry`),
  `description` = VALUES(`description`),
  `default_primary_color` = VALUES(`default_primary_color`),
  `default_secondary_color` = VALUES(`default_secondary_color`),
  `default_font_family` = VALUES(`default_font_family`),
  `preview_image` = VALUES(`preview_image`),
  `status` = 1,
  `sort_order` = VALUES(`sort_order`);
