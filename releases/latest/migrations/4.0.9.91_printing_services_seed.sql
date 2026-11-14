-- ============================================================================
-- MartPoint 4.0.9.91 — Printing: service-led storefront
--
-- A printing company sells SERVICES (large format, digital print, DTF, design,
-- finishing), not retail categories. Store 2 still carries leftover demo
-- services from beauty / auto / physiotherapy ("Bridal Makeup", "Test Drive
-- Booking"), which make the printing storefront look wrong.
--
-- This migration:
--   1. Retires the non-printing demo services for printing stores (status = 0,
--      so nothing is destroyed and they can be reactivated if ever needed).
--   2. Seeds a proper printing service catalogue, grouped by category so the
--      storefront can present Services rather than product categories.
--
-- Only touches stores whose industry_type = 'printing'. Idempotent: services
-- are matched by name, so re-running does not duplicate them.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- 1. Retire demo services that do not belong to a printing business -----------
UPDATE `db_services` s
JOIN `db_store_industry_settings` i ON i.store_id = s.store_id
SET s.status = 0
WHERE i.industry_type = 'printing'
  AND s.service_name IN (
    'Bespoke Blend Consultation','Vehicle Inspection','Test Drive Booking',
    'Bridal Makeup (Full)','Bridal Trial Session','Occasion Glam',
    'Photoshoot / Editorial','Everyday Soft Glam','Brow Shaping & Tinting',
    'Lash Extensions','Gele & Headwrap Styling','Makeup Class (1-on-1)',
    'Studio Consultation','Skin Consultation','Facial Treatment',
    'Custom Formulation','Initial Assessment','Physiotherapy Session',
    'Review / Re-assessment','Home Visit','Inpatient Day Care'
  );

-- 2. Seed the printing service catalogue -------------------------------------
-- Only for printing stores that do not already have these services.
INSERT INTO `db_services`
  (`store_id`, `service_name`, `price`, `service_duration`, `description`,
   `available_online`, `requires_appointment`, `requires_note`, `location_type`,
   `sort_order`, `status`)
SELECT * FROM (
  SELECT i.store_id, v.service_name, v.price, v.service_duration, v.description,
         v.available_online, v.requires_appointment, v.requires_note,
         v.location_type, v.sort_order, 1
  FROM `db_store_industry_settings` i
  JOIN (
    SELECT 'Large Format Printing'  AS service_name, 0.00  AS price, 'Quoted'  AS service_duration, 'Banners, billboards, mesh, vehicle graphics and site signage. Priced per square metre after we confirm size and material.' AS description, 1 AS available_online, 0 AS requires_appointment, 1 AS requires_note, 'in-store' AS location_type, 10 AS sort_order
    UNION ALL SELECT 'Digital Printing', 0.00, 'Quoted', 'Business cards, flyers, letterheads, stickers and short-run documents.', 1, 0, 1, 'in-store', 20
    UNION ALL SELECT 'Booklets & Binding', 0.00, 'Quoted', 'Saddle stitch, perfect bind, spiral and wire-o for reports, menus and catalogues.', 1, 0, 1, 'in-store', 30
    UNION ALL SELECT 'Apparel & DTF Printing', 0.00, 'Quoted', 'Branded tees, polos, hoodies and workwear. Front, back and sleeve positions with durable transfers.', 1, 0, 1, 'in-store', 40
    UNION ALL SELECT 'Graphic Design', 0.00, 'Quoted', 'Logo, layout and artwork preparation. We can design from scratch or work from your supplied files.', 1, 0, 1, 'online', 50
    UNION ALL SELECT 'Signage & Fabrication', 0.00, 'Quoted', 'Acrylic, PVC and composite signs, mounting and installation.', 1, 1, 1, 'customer-location', 60
    UNION ALL SELECT 'Finishing Services', 0.00, 'Quoted', 'Lamination, mounting, hemming, eyeleting, trimming and folding.', 1, 0, 1, 'in-store', 70
    UNION ALL SELECT 'Vehicle Graphics', 0.00, 'Quoted', 'Full and partial wraps, fleet branding and window graphics.', 1, 1, 1, 'customer-location', 80
    UNION ALL SELECT 'Same-Day / Rush Printing', 0.00, 'Same day', 'Priority production for urgent jobs, subject to capacity. Rush surcharge applies.', 1, 0, 1, 'in-store', 90
  ) v ON 1 = 1
  WHERE i.industry_type = 'printing'
) seed
WHERE NOT EXISTS (
  SELECT 1 FROM `db_services` d
  WHERE d.store_id = seed.store_id AND d.service_name = seed.service_name
);

-- 3. Backfill descriptions on printing services that were created earlier with
--    none (the storefront renders the description, so an empty one looks broken).
UPDATE `db_services` s
JOIN `db_store_industry_settings` i ON i.store_id = s.store_id
SET s.description = CASE s.service_name
  WHEN 'Graphic Design'         THEN 'Logo, layout and artwork preparation. We can design from scratch or work from your supplied files.'
  WHEN 'Large Format Printing'  THEN 'Banners, billboards, mesh, vehicle graphics and site signage. Priced per square metre after we confirm size and material.'
  WHEN 'Digital Printing'       THEN 'Business cards, flyers, letterheads, stickers and short-run documents.'
  WHEN 'Booklets & Binding'     THEN 'Saddle stitch, perfect bind, spiral and wire-o for reports, menus and catalogues.'
  WHEN 'Apparel & DTF Printing' THEN 'Branded tees, polos, hoodies and workwear. Front, back and sleeve positions with durable transfers.'
  WHEN 'Signage & Fabrication'  THEN 'Acrylic, PVC and composite signs, mounting and installation.'
  WHEN 'Finishing Services'     THEN 'Lamination, mounting, hemming, eyeleting, trimming and folding.'
  WHEN 'Vehicle Graphics'       THEN 'Full and partial wraps, fleet branding and window graphics.'
  WHEN 'Same-Day / Rush Printing' THEN 'Priority production for urgent jobs, subject to capacity. Rush surcharge applies.'
  ELSE s.description
END
WHERE i.industry_type = 'printing'
  AND s.status = 1
  AND (s.description IS NULL OR TRIM(s.description) = '')
  AND s.service_name IN (
    'Graphic Design','Large Format Printing','Digital Printing','Booklets & Binding',
    'Apparel & DTF Printing','Signage & Fabrication','Finishing Services',
    'Vehicle Graphics','Same-Day / Rush Printing'
  );

SET FOREIGN_KEY_CHECKS = 1;
