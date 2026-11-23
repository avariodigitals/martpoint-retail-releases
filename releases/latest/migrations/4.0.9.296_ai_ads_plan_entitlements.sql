-- Snapshot the purchased AI Ads allowances in Fleet and record monthly usage.
ALTER TABLE `db_ai_ads_billing`
  ADD COLUMN `plan_limits_json` MEDIUMTEXT DEFAULT NULL AFTER `amount_kobo`;

ALTER TABLE `db_ai_ads_subscriptions`
  ADD COLUMN `plan_limits_json` MEDIUMTEXT DEFAULT NULL AFTER `months_paid`;

CREATE TABLE IF NOT EXISTS `db_ai_ads_usage` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT UNSIGNED NOT NULL,
  `subscription_id` BIGINT UNSIGNED NOT NULL,
  `cycle_start` DATETIME NOT NULL,
  `cycle_end` DATETIME NOT NULL,
  `copy_generations` INT UNSIGNED NOT NULL DEFAULT 0,
  `ai_images` INT UNSIGNED NOT NULL DEFAULT 0,
  `video_exports` INT UNSIGNED NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ai_ads_usage_cycle` (`store_id`,`subscription_id`,`cycle_start`),
  KEY `idx_ai_ads_usage_store_cycle` (`store_id`,`cycle_start`,`cycle_end`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
