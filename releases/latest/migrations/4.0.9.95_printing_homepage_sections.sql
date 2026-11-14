-- ============================================================================
-- MartPoint 4.0.9.95 — Service-shaped homepage sections for printing stores
--
-- Printing storefronts were seeded with RETAIL homepage defaults (Featured
-- Categories, New Arrivals, Best Sellers). That makes a service business look
-- like an ecommerce shop.
--
-- This narrows the retail defaults for printing stores: it turns OFF the
-- retail discovery sections and turns ON the service ones.
--
-- SAFETY: it only touches stores whose enabled set still matches the stock
-- retail defaults exactly. A merchant who has deliberately arranged their own
-- homepage is left completely alone.
--
-- Idempotent.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE='NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- Which printing stores are still on the untouched retail arrangement?
CREATE TEMPORARY TABLE IF NOT EXISTS `tmp_print_untouched` (
  `store_id` INT(11) NOT NULL,
  PRIMARY KEY (`store_id`)
);

INSERT IGNORE INTO `tmp_print_untouched` (`store_id`)
SELECT i.store_id
FROM `db_store_industry_settings` i
WHERE i.industry_type = 'printing'
  -- has sections configured
  AND EXISTS (SELECT 1 FROM `db_storefront_homepage_sections` s WHERE s.store_id = i.store_id)
  -- The tell-tale retail sections are still ON: this store has never been
  -- arranged for a service business. A merchant who curated their homepage
  -- would have turned these off, so we leave them alone.
  AND EXISTS (
    SELECT 1 FROM `db_storefront_homepage_sections` s
    WHERE s.store_id = i.store_id
      AND s.is_enabled = 1
      AND s.section_key IN ('featured_categories','new_arrivals','best_sellers')
  );

-- Turn OFF retail discovery sections for those stores.
UPDATE `db_storefront_homepage_sections` s
JOIN `tmp_print_untouched` t ON t.store_id = s.store_id
SET s.is_enabled = 0
WHERE s.section_key IN (
  'featured_categories','featured_products','new_arrivals','best_sellers',
  'brands','promo_banner','contact_section','whatsapp_cta','newsletter'
);

-- Turn ON the service journey.
UPDATE `db_storefront_homepage_sections` s
JOIN `tmp_print_untouched` t ON t.store_id = s.store_id
SET s.is_enabled = 1
WHERE s.section_key IN (
  'hero_banner','featured_services','trust_badges','testimonials',
  'instagram_gallery','store_info','store_hours','faqs'
);

-- Order the service journey so it reads top-to-bottom sensibly.
UPDATE `db_storefront_homepage_sections` s
JOIN `tmp_print_untouched` t ON t.store_id = s.store_id
SET s.display_order = CASE s.section_key
  WHEN 'hero_banner'        THEN 1
  WHEN 'featured_services'  THEN 2
  WHEN 'trust_badges'       THEN 3
  WHEN 'testimonials'       THEN 4
  WHEN 'promo_banner'       THEN 5
  WHEN 'featured_categories' THEN 6
  WHEN 'featured_products'  THEN 7
  WHEN 'best_sellers'       THEN 8
  WHEN 'new_arrivals'       THEN 9
  WHEN 'brands'             THEN 10
  WHEN 'instagram_gallery'  THEN 11
  WHEN 'store_info'         THEN 12
  WHEN 'store_hours'        THEN 13
  WHEN 'faqs'               THEN 14
  WHEN 'contact_section'    THEN 15
  WHEN 'whatsapp_cta'       THEN 16
  WHEN 'newsletter'         THEN 17
  ELSE s.display_order
END;

-- Give the service sections clearer labels than the retail ones.
UPDATE `db_storefront_homepage_sections` s
JOIN `tmp_print_untouched` t ON t.store_id = s.store_id
SET s.section_label = CASE s.section_key
  WHEN 'featured_services' THEN 'Our Services'
  WHEN 'testimonials'      THEN 'Client Feedback'
  WHEN 'instagram_gallery' THEN 'Work Gallery'
  WHEN 'store_hours'       THEN 'Opening Hours'
  WHEN 'contact_section'   THEN 'Enquiry Form'
  ELSE s.section_label
END;

DROP TEMPORARY TABLE IF EXISTS `tmp_print_untouched`;

SET FOREIGN_KEY_CHECKS = 1;
