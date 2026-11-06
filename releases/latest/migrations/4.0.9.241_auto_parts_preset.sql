-- Automotive Parts & Accessories preset readiness (v4.0.9.241).
--
-- The auto_parts preset was rebuilt from the old distributor shape onto the
-- shared auto family: customer-vehicle register, fitment, fitting services,
-- variants, serial/warranty tracking. Saved feature flags take priority over
-- the preset, so existing auto_parts stores carrying the old preset's stored
-- answers would load the new capabilities OFF forever. Stores that customised
-- away from the old preset are left alone. Same dual-signature repair as
-- 4.0.9.232 used for tyre_shop.

SET @db := DATABASE();

-- 1. Canonical modular table (read first by mp_get_store_profile).
SET @ready = (
  SELECT COUNT(*) FROM information_schema.columns
  WHERE table_schema=@db AND table_name='db_store_industry_settings'
    AND column_name IN ('industry_type','business_model','feature_flags_json','dashboard_template_key','workflow_template_key'));
SET @sql = IF(@ready=5,
 'UPDATE `db_store_industry_settings`
    SET `business_model`=''product_and_service'',
        `workflow_template_key`=''distributor_standard'',
        `feature_flags_json`=NULL
  WHERE `industry_type`=''auto_parts''
    AND ((`dashboard_template_key`=''distributor'' OR `dashboard_template_key` IS NULL OR `dashboard_template_key`='''')
      AND (`business_model`=''product_based'' OR `business_model` IS NULL OR `business_model`=''''))',
 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(@ready=5,
 'UPDATE `db_store_industry_settings`
    SET `business_model`=''product_and_service'',
        `dashboard_template_key`=''distributor'',
        `workflow_template_key`=''distributor_standard''
  WHERE `industry_type`=''auto_parts''
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
        `workflow_template_key`=''distributor_standard'',
        `feature_flags_json`=NULL
  WHERE `industry_type`=''auto_parts''
    AND ((`dashboard_template_key`=''distributor'' OR `dashboard_template_key` IS NULL OR `dashboard_template_key`='''')
      AND (`business_model`=''product_based'' OR `business_model` IS NULL OR `business_model`=''''))',
 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(@ready=5,
 'UPDATE `db_store_business_profile`
    SET `business_model`=''product_and_service'',
        `dashboard_template_key`=''distributor'',
        `workflow_template_key`=''distributor_standard''
  WHERE `industry_type`=''auto_parts''
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
        `workflow_template_key`=''distributor_standard'',
        `feature_flags_json`=NULL
  WHERE `industry_type`=''auto_parts''
    AND ((`dashboard_template_key`=''distributor'' OR `dashboard_template_key` IS NULL OR `dashboard_template_key`='''')
      AND (`business_model`=''product_based'' OR `business_model` IS NULL OR `business_model`=''''))',
 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF(@ready=5,
 'UPDATE `db_store`
    SET `business_model`=''product_and_service'',
        `dashboard_template_key`=''distributor'',
        `workflow_template_key`=''distributor_standard''
  WHERE `industry_type`=''auto_parts''
    AND `feature_flags_json` IS NULL
    AND `dashboard_template_key`=''general_retail''
    AND `workflow_template_key`=''retail_standard''
    AND `business_model`=''product_based''',
 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
