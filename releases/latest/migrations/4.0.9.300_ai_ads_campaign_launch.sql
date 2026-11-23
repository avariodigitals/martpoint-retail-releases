-- Persist the Meta measurement/destination choices and the spend approval used
-- to create a capped, merchant-approved campaign.
ALTER TABLE `db_ai_ads_connections`
  ADD COLUMN `ad_account_currency` CHAR(3) DEFAULT NULL AFTER `ad_account_name`,
  ADD COLUMN `pixel_id` VARCHAR(80) DEFAULT NULL AFTER `page_name`;

ALTER TABLE `db_ai_ads_campaigns`
  ADD COLUMN `destination_type` VARCHAR(24) DEFAULT NULL AFTER `destination_url`,
  ADD COLUMN `pixel_id` VARCHAR(80) DEFAULT NULL AFTER `destination_type`,
  ADD COLUMN `conversion_event` VARCHAR(40) DEFAULT NULL AFTER `pixel_id`,
  ADD COLUMN `confirmed_total_minor` BIGINT UNSIGNED DEFAULT NULL AFTER `daily_budget_minor`,
  ADD COLUMN `launched_at` DATETIME DEFAULT NULL AFTER `subscription_policy_accepted_at`;

CREATE INDEX `idx_ai_ads_campaign_meta` ON `db_ai_ads_campaigns` (`meta_campaign_id`,`status`);
