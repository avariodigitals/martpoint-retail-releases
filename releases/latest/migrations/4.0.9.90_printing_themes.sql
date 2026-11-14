-- ============================================================================
-- MartPoint 4.0.9.90 — Printing storefront themes
--
-- Three modern themes for service-based printing businesses. Printing is
-- service-based (quote + portfolio), not cart-based, so these themes lead with
-- work, capabilities and a request-quote flow while keeping the retail
-- catalogue available when the shop also sells materials offline.
--
-- Registered under industry = 'printing' so Theme_engine only offers them to
-- printing stores. Existing themes for other industries are untouched.
-- Idempotent (INSERT IGNORE on the unique theme_key).
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

INSERT IGNORE INTO `db_storefront_themes`
  (`theme_key`, `theme_name`, `industry`, `description`, `default_primary_color`, `default_secondary_color`, `default_font_family`, `status`, `sort_order`)
VALUES
  ('print_inkpress', 'InkPress Studio', 'printing',
   'Bold press-room aesthetic. Strong typographic hierarchy, work-first grid and a prominent quote request. Best for commercial printers and large-format shops.',
   '#0E7490', '#F59E0B', 'Inter', 1, 20),

  ('print_papercraft', 'PaperCraft Atelier', 'printing',
   'Calm, editorial and craft-led. Warm paper tones, generous whitespace and a portfolio grid that suits stationery, packaging and bespoke finishing.',
   '#7C5C3E', '#2F6F5F', 'Playfair Display', 1, 21),

  ('print_neonprint', 'NeonPrint Works', 'printing',
   'High-contrast, contemporary and energetic. Suits DTF, apparel decoration and short-run print studios that want to feel fast and modern.',
   '#111827', '#22D3EE', 'Montserrat', 1, 22);

SET FOREIGN_KEY_CHECKS = 1;
