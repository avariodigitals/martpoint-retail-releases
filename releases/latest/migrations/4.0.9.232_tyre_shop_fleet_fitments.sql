-- Tyre Shop readiness (v4.0.9.232).
--
-- A tyre shop sells rubber that belongs to a specific vehicle: the four tyres
-- fitted today should be traceable back to the car they went on, and a fleet
-- customer's invoices should read as one vehicle story. Until now the vehicle
-- register (db_vehicles) only served dealerships — it sold whole cars — and no
-- sale remembered which vehicle it was for.
--
--   * db_sales.vehicle_id records the vehicle a bill was fitted to. Nullable
--     and additive: every older invoice simply reads "no vehicle recorded".
--
--   * Existing tyre-shop stores carried the old distributor-shaped profile —
--     product-only, distributor dashboard, and a feature_flags_json full of
--     the old preset's answers. Saved flags take priority over the preset, so
--     without this repair the rebuilt Tyre Shop preset (variants, fitting
--     services, vehicles, warranty) would load OFF behind the old stored
--     defaults. Stores that customised away from the old preset are left alone.

SET @db := DATABASE();

-- ── The vehicle the tyres went on ───────────────────────────────────────────
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema=@db AND table_name='db_sales' AND column_name='vehicle_id');
SET @sql = IF(@col_exists=0, 'ALTER TABLE `db_sales` ADD COLUMN `vehicle_id` INT NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @idx_exists = (SELECT COUNT(*) FROM information_schema.STATISTICS WHERE table_schema=@db AND table_name='db_sales' AND INDEX_NAME='idx_sales_vehicle');
SET @sql = IF(@idx_exists=0, 'ALTER TABLE `db_sales` ADD INDEX `idx_sales_vehicle` (`store_id`, `vehicle_id`)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ── Move untouched tyre-shop stores onto the rebuilt preset ─────────────────
-- Two signatures qualify:
--   A) the OLD distributor-shaped preset (dashboard 'distributor' or empty,
--      business model 'product_based' or empty);
--   B) rows that were never customised at all — every profile column still at
--      its table DEFAULT and feature_flags_json NULL. Without B these would
--      keep shadowing the preset with 'general_retail'/'product_based' forever.
-- Anything else is a deliberate merchant choice and survives untouched.

-- 1. Canonical modular table (read first by mp_get_store_profile).
SET @ready = (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema=@db AND table_name='db_store_industry_settings'
    AND column_name IN ('industry_type','business_model','feature_flags_json','dashboard_template_key','workflow_template_key'));
SET @sql = IF(@ready=5,
 'UPDATE `db_store_industry_settings`
    SET `business_model`=''product_and_service'',
        `dashboard_template_key`=''tyre_shop'',
        `workflow_template_key`=''tyre_shop_standard'',
        `feature_flags_json`=NULL
  WHERE `industry_type`=''tyre_shop''
    AND ((`dashboard_template_key`=''distributor'' OR `dashboard_template_key` IS NULL OR `dashboard_template_key`='''')
      AND (`business_model`=''product_based'' OR `business_model` IS NULL OR `business_model`=''''))',
 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(@ready=5,
 'UPDATE `db_store_industry_settings`
    SET `business_model`=''product_and_service'',
        `dashboard_template_key`=''tyre_shop'',
        `workflow_template_key`=''tyre_shop_standard''
  WHERE `industry_type`=''tyre_shop''
    AND `feature_flags_json` IS NULL
    AND `dashboard_template_key`=''general_retail''
    AND `workflow_template_key`=''retail_standard''
    AND `business_model`=''product_based''',
 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 2. Legacy business-profile mirror (still read as a fallback).
SET @ready = (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema=@db AND table_name='db_store_business_profile'
    AND column_name IN ('industry_type','business_model','feature_flags_json','dashboard_template_key','workflow_template_key'));
SET @sql = IF(@ready=5,
 'UPDATE `db_store_business_profile`
    SET `business_model`=''product_and_service'',
        `dashboard_template_key`=''tyre_shop'',
        `workflow_template_key`=''tyre_shop_standard'',
        `feature_flags_json`=NULL
  WHERE `industry_type`=''tyre_shop''
    AND ((`dashboard_template_key`=''distributor'' OR `dashboard_template_key` IS NULL OR `dashboard_template_key`='''')
      AND (`business_model`=''product_based'' OR `business_model` IS NULL OR `business_model`=''''))',
 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(@ready=5,
 'UPDATE `db_store_business_profile`
    SET `business_model`=''product_and_service'',
        `dashboard_template_key`=''tyre_shop'',
        `workflow_template_key`=''tyre_shop_standard''
  WHERE `industry_type`=''tyre_shop''
    AND `feature_flags_json` IS NULL
    AND `dashboard_template_key`=''general_retail''
    AND `workflow_template_key`=''retail_standard''
    AND `business_model`=''product_based''',
 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 3. db_store mirror (only when it carries the profile columns itself).
SET @ready = (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema=@db AND table_name='db_store'
    AND column_name IN ('industry_type','business_model','feature_flags_json','dashboard_template_key','workflow_template_key'));
SET @sql = IF(@ready=5,
 'UPDATE `db_store`
    SET `business_model`=''product_and_service'',
        `dashboard_template_key`=''tyre_shop'',
        `workflow_template_key`=''tyre_shop_standard'',
        `feature_flags_json`=NULL
  WHERE `industry_type`=''tyre_shop''
    AND ((`dashboard_template_key`=''distributor'' OR `dashboard_template_key` IS NULL OR `dashboard_template_key`='''')
      AND (`business_model`=''product_based'' OR `business_model` IS NULL OR `business_model`=''''))',
 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(@ready=5,
 'UPDATE `db_store`
    SET `business_model`=''product_and_service'',
        `dashboard_template_key`=''tyre_shop'',
        `workflow_template_key`=''tyre_shop_standard''
  WHERE `industry_type`=''tyre_shop''
    AND `feature_flags_json` IS NULL
    AND `dashboard_template_key`=''general_retail''
    AND `workflow_template_key`=''retail_standard''
    AND `business_model`=''product_based''',
 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
