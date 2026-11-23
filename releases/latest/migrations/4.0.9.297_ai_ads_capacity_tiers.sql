-- Keep the selected AI Ads tier attached to a paid Fleet checkout and the
-- Central delivery registry. Existing Starter subscriptions remain unchanged.
ALTER TABLE `db_ai_ads_billing`
  MODIFY COLUMN `plan_code` VARCHAR(64) NOT NULL,
  ADD COLUMN `product_code` VARCHAR(80) NOT NULL DEFAULT 'ai-ads-monthly' AFTER `plan_code`;

ALTER TABLE `db_ai_ads_subscriptions`
  MODIFY COLUMN `plan_code` VARCHAR(64) NOT NULL;

UPDATE `db_ai_ads_billing`
SET `product_code`=CASE WHEN `billing_period`='annual' THEN 'ai-ads-annual' ELSE 'ai-ads-monthly' END
WHERE `product_code`='ai-ads-monthly' AND `plan_code`='ai_ads_annual';

ALTER TABLE `db_central_ai_ads_subscriptions`
  ADD COLUMN `plan_code` VARCHAR(64) NOT NULL DEFAULT 'ai_ads_monthly' AFTER `store_id`,
  ADD COLUMN `product_code` VARCHAR(80) NOT NULL DEFAULT 'ai-ads-monthly' AFTER `plan_code`;

UPDATE `db_central_ai_ads_subscriptions`
SET `plan_code`=CONCAT('ai_ads_',`billing_period`),
    `product_code`=CASE WHEN `billing_period`='annual' THEN 'ai-ads-annual' ELSE 'ai-ads-monthly' END
WHERE `plan_code`='ai_ads_monthly';
