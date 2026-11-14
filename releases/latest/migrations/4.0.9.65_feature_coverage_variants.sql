-- ---------------------------------------------------------------------------
-- Phase 0.5: business-type feature coverage + variants flag separation
--
-- 1. Widen general commerce capabilities (loyalty, gift_cards, store_credit,
--    qr_ordering, manual_shipping) so they are available to every applicable
--    business type. Stored feature_flags_json values are only flipped where
--    the flag was NOT part of the industry's previous preset — a stored '0'
--    on a flag the preset already recommended is a deliberate merchant
--    opt-out and is preserved.
--
-- 2. Migrate the legacy 'bundles' flag (which actually controls Item
--    Variants) to the new 'item_variants' key. The 'bundles' key is kept in
--    stored JSON and still readable as a fallback; true composite bundles are
--    a separate future capability.
--
-- Idempotent: JSON_SET re-applies the same values on a re-run.
-- ---------------------------------------------------------------------------

-- Loyalty: enable where the flag was not previously recommended
UPDATE db_store_industry_settings SET feature_flags_json = JSON_SET(feature_flags_json,'$.loyalty','1')
WHERE feature_flags_json IS NOT NULL AND industry_type IN
  ('physiotherapy_rehabilitation','distributor','wholesaler','agro_dealer','feed_store','auto_parts','tyre_shop','car_dealership','manufacturer','nylon_polythene','multi_branch_retail','scientific_equipment');
UPDATE db_store_business_profile SET feature_flags_json = JSON_SET(feature_flags_json,'$.loyalty','1')
WHERE feature_flags_json IS NOT NULL AND industry_type IN
  ('physiotherapy_rehabilitation','distributor','wholesaler','agro_dealer','feed_store','auto_parts','tyre_shop','car_dealership','manufacturer','nylon_polythene','multi_branch_retail','scientific_equipment');
UPDATE db_store SET feature_flags_json = JSON_SET(feature_flags_json,'$.loyalty','1')
WHERE feature_flags_json IS NOT NULL AND industry_type IN
  ('physiotherapy_rehabilitation','distributor','wholesaler','agro_dealer','feed_store','auto_parts','tyre_shop','car_dealership','manufacturer','nylon_polythene','multi_branch_retail','scientific_equipment');

-- Gift cards
UPDATE db_store_industry_settings SET feature_flags_json = JSON_SET(feature_flags_json,'$.gift_cards','1')
WHERE feature_flags_json IS NOT NULL AND industry_type IN
  ('mini_mart','pharmacy','restaurant','building_materials','grocery_store','provision_store','convenience_store','fast_food','cafe','pizza_shop','shawarma','juice_bar','buka','canteen','butcher','frozen_foods_retailer','medical_store','physiotherapy_rehabilitation','distributor','wholesaler','agro_dealer','feed_store','auto_parts','tyre_shop','car_dealership','manufacturer','nylon_polythene','multi_branch_retail','scientific_equipment');
UPDATE db_store_business_profile SET feature_flags_json = JSON_SET(feature_flags_json,'$.gift_cards','1')
WHERE feature_flags_json IS NOT NULL AND industry_type IN
  ('mini_mart','pharmacy','restaurant','building_materials','grocery_store','provision_store','convenience_store','fast_food','cafe','pizza_shop','shawarma','juice_bar','buka','canteen','butcher','frozen_foods_retailer','medical_store','physiotherapy_rehabilitation','distributor','wholesaler','agro_dealer','feed_store','auto_parts','tyre_shop','car_dealership','manufacturer','nylon_polythene','multi_branch_retail','scientific_equipment');
UPDATE db_store SET feature_flags_json = JSON_SET(feature_flags_json,'$.gift_cards','1')
WHERE feature_flags_json IS NOT NULL AND industry_type IN
  ('mini_mart','pharmacy','restaurant','building_materials','grocery_store','provision_store','convenience_store','fast_food','cafe','pizza_shop','shawarma','juice_bar','buka','canteen','butcher','frozen_foods_retailer','medical_store','physiotherapy_rehabilitation','distributor','wholesaler','agro_dealer','feed_store','auto_parts','tyre_shop','car_dealership','manufacturer','nylon_polythene','multi_branch_retail','scientific_equipment');

-- Store credit
UPDATE db_store_industry_settings SET feature_flags_json = JSON_SET(feature_flags_json,'$.store_credit','1')
WHERE feature_flags_json IS NOT NULL AND industry_type IN
  ('mini_mart','pharmacy','restaurant','makeup_artist','laundry','grocery_store','provision_store','convenience_store','fast_food','cafe','pizza_shop','shawarma','juice_bar','buka','canteen','butcher','frozen_foods_retailer','medical_store','building_materials','paint_store','plumbing_store');
UPDATE db_store_business_profile SET feature_flags_json = JSON_SET(feature_flags_json,'$.store_credit','1')
WHERE feature_flags_json IS NOT NULL AND industry_type IN
  ('mini_mart','pharmacy','restaurant','makeup_artist','laundry','grocery_store','provision_store','convenience_store','fast_food','cafe','pizza_shop','shawarma','juice_bar','buka','canteen','butcher','frozen_foods_retailer','medical_store','building_materials','paint_store','plumbing_store');
UPDATE db_store SET feature_flags_json = JSON_SET(feature_flags_json,'$.store_credit','1')
WHERE feature_flags_json IS NOT NULL AND industry_type IN
  ('mini_mart','pharmacy','restaurant','makeup_artist','laundry','grocery_store','provision_store','convenience_store','fast_food','cafe','pizza_shop','shawarma','juice_bar','buka','canteen','butcher','frozen_foods_retailer','medical_store','building_materials','paint_store','plumbing_store');

-- QR ordering: enable where the online store is actually on for the store and
-- the flag was not previously recommended
UPDATE db_store_industry_settings SET feature_flags_json = JSON_SET(feature_flags_json,'$.qr_ordering','1')
WHERE feature_flags_json IS NOT NULL
  AND industry_type IN ('pharmacy','beauty_spa','salon_barbershop','makeup_artist','bookshop','furniture','distributor','wholesaler','service_business','medical_store','clinic','hospital','diagnostic_centre','building_materials','paint_store','plumbing_store','agro_dealer','feed_store','auto_parts','tyre_shop','car_dealership','manufacturer','scientific_equipment','printing','tailoring')
  AND JSON_UNQUOTE(JSON_EXTRACT(feature_flags_json,'$.online_store')) = '1';
UPDATE db_store_business_profile SET feature_flags_json = JSON_SET(feature_flags_json,'$.qr_ordering','1')
WHERE feature_flags_json IS NOT NULL
  AND industry_type IN ('pharmacy','beauty_spa','salon_barbershop','makeup_artist','bookshop','furniture','distributor','wholesaler','service_business','medical_store','clinic','hospital','diagnostic_centre','building_materials','paint_store','plumbing_store','agro_dealer','feed_store','auto_parts','tyre_shop','car_dealership','manufacturer','scientific_equipment','printing','tailoring')
  AND JSON_UNQUOTE(JSON_EXTRACT(feature_flags_json,'$.online_store')) = '1';
UPDATE db_store SET feature_flags_json = JSON_SET(feature_flags_json,'$.qr_ordering','1')
WHERE feature_flags_json IS NOT NULL
  AND industry_type IN ('pharmacy','beauty_spa','salon_barbershop','makeup_artist','bookshop','furniture','distributor','wholesaler','service_business','medical_store','clinic','hospital','diagnostic_centre','building_materials','paint_store','plumbing_store','agro_dealer','feed_store','auto_parts','tyre_shop','car_dealership','manufacturer','scientific_equipment','printing','tailoring')
  AND JSON_UNQUOTE(JSON_EXTRACT(feature_flags_json,'$.online_store')) = '1';

-- Manual shipping (POS delivery fee): product-bearing businesses only,
-- excluding scientific_equipment where it was already recommended
UPDATE db_store_industry_settings SET feature_flags_json = JSON_SET(feature_flags_json,'$.manual_shipping','1')
WHERE feature_flags_json IS NOT NULL
  AND industry_type <> 'scientific_equipment'
  AND COALESCE(business_model,'') <> 'service_based';
UPDATE db_store_business_profile SET feature_flags_json = JSON_SET(feature_flags_json,'$.manual_shipping','1')
WHERE feature_flags_json IS NOT NULL
  AND industry_type <> 'scientific_equipment'
  AND COALESCE(business_model,'') <> 'service_based';
UPDATE db_store SET feature_flags_json = JSON_SET(feature_flags_json,'$.manual_shipping','1')
WHERE feature_flags_json IS NOT NULL
  AND industry_type <> 'scientific_equipment'
  AND COALESCE(business_model,'') <> 'service_based';

-- Item variants: carry over the legacy 'bundles' value wherever it was
-- explicitly stored (preserves deliberate enable/disable choices)
UPDATE db_store_industry_settings SET feature_flags_json = JSON_SET(feature_flags_json,'$.item_variants',
    JSON_UNQUOTE(JSON_EXTRACT(feature_flags_json,'$.bundles')))
WHERE JSON_CONTAINS_PATH(feature_flags_json,'one','$.bundles');
UPDATE db_store_business_profile SET feature_flags_json = JSON_SET(feature_flags_json,'$.item_variants',
    JSON_UNQUOTE(JSON_EXTRACT(feature_flags_json,'$.bundles')))
WHERE JSON_CONTAINS_PATH(feature_flags_json,'one','$.bundles');
UPDATE db_store SET feature_flags_json = JSON_SET(feature_flags_json,'$.item_variants',
    JSON_UNQUOTE(JSON_EXTRACT(feature_flags_json,'$.bundles')))
WHERE JSON_CONTAINS_PATH(feature_flags_json,'one','$.bundles');

-- Enable variants for product-bearing stores outside the industries where
-- variants were previously recommended (a stored '0' there was preset-derived)
UPDATE db_store_industry_settings SET feature_flags_json = JSON_SET(feature_flags_json,'$.item_variants','1')
WHERE feature_flags_json IS NOT NULL
  AND industry_type NOT IN ('fashion','boutique','shoe_store','perfume_shop','skincare')
  AND COALESCE(business_model,'') <> 'service_based';
UPDATE db_store_business_profile SET feature_flags_json = JSON_SET(feature_flags_json,'$.item_variants','1')
WHERE feature_flags_json IS NOT NULL
  AND industry_type NOT IN ('fashion','boutique','shoe_store','perfume_shop','skincare')
  AND COALESCE(business_model,'') <> 'service_based';
UPDATE db_store SET feature_flags_json = JSON_SET(feature_flags_json,'$.item_variants','1')
WHERE feature_flags_json IS NOT NULL
  AND industry_type NOT IN ('fashion','boutique','shoe_store','perfume_shop','skincare')
  AND COALESCE(business_model,'') <> 'service_based';
