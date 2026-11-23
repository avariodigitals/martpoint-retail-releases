-- Repair Central subscription tier columns when .297 was recorded but its
-- schema changes were not fully applied. Safe to run if both columns exist.
SET @mp_aiads_tier_ddl := IF(
  EXISTS(SELECT 1 FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='db_central_ai_ads_subscriptions')
  AND NOT EXISTS(SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_central_ai_ads_subscriptions' AND column_name='plan_code'),
  'ALTER TABLE `db_central_ai_ads_subscriptions` ADD COLUMN `plan_code` VARCHAR(64) NOT NULL DEFAULT ''ai_ads_monthly'' AFTER `store_id`',
  'SELECT 1'
);
PREPARE mp_aiads_tier_stmt FROM @mp_aiads_tier_ddl;
EXECUTE mp_aiads_tier_stmt;
DEALLOCATE PREPARE mp_aiads_tier_stmt;

SET @mp_aiads_tier_ddl := IF(
  EXISTS(SELECT 1 FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='db_central_ai_ads_subscriptions')
  AND NOT EXISTS(SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_central_ai_ads_subscriptions' AND column_name='product_code'),
  'ALTER TABLE `db_central_ai_ads_subscriptions` ADD COLUMN `product_code` VARCHAR(80) NOT NULL DEFAULT ''ai-ads-monthly'' AFTER `plan_code`',
  'SELECT 1'
);
PREPARE mp_aiads_tier_stmt FROM @mp_aiads_tier_ddl;
EXECUTE mp_aiads_tier_stmt;
DEALLOCATE PREPARE mp_aiads_tier_stmt;

SET @mp_aiads_tier_ddl := IF(
  EXISTS(SELECT 1 FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='db_central_ai_ads_subscriptions')
  AND EXISTS(SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_central_ai_ads_subscriptions' AND column_name='plan_code')
  AND EXISTS(SELECT 1 FROM information_schema.columns WHERE table_schema=DATABASE() AND table_name='db_central_ai_ads_subscriptions' AND column_name='product_code'),
  'UPDATE `db_central_ai_ads_subscriptions` SET `plan_code`=CASE WHEN `plan_code` IS NULL OR `plan_code`='''' OR (`plan_code`=''ai_ads_monthly'' AND `billing_period`=''annual'') THEN IF(`billing_period`=''annual'',''ai_ads_annual'',''ai_ads_monthly'') ELSE `plan_code` END, `product_code`=CASE WHEN `product_code` IS NULL OR `product_code`='''' OR (`product_code`=''ai-ads-monthly'' AND `billing_period`=''annual'') THEN IF(`billing_period`=''annual'',''ai-ads-annual'',''ai-ads-monthly'') ELSE `product_code` END WHERE `plan_code` IS NULL OR `plan_code`='''' OR `product_code` IS NULL OR `product_code`='''' OR (`billing_period`=''annual'' AND (`plan_code`=''ai_ads_monthly'' OR `product_code`=''ai-ads-monthly''))',
  'SELECT 1'
);
PREPARE mp_aiads_tier_stmt FROM @mp_aiads_tier_ddl;
EXECUTE mp_aiads_tier_stmt;
DEALLOCATE PREPARE mp_aiads_tier_stmt;
