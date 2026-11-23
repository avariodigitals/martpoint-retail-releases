-- Track generated/uploaded creative media and enforce its included capacity.
ALTER TABLE `db_ai_ads_usage`
  ADD COLUMN `ai_video_generations` INT UNSIGNED NOT NULL DEFAULT 0 AFTER `ai_images`;

CREATE TABLE IF NOT EXISTS `db_ai_ads_media_assets` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `subscription_id` BIGINT UNSIGNED NOT NULL,
  `cycle_start` DATETIME NOT NULL,
  `asset_type` VARCHAR(16) NOT NULL,
  `source_type` VARCHAR(16) NOT NULL DEFAULT 'upload',
  `file_name` VARCHAR(190) NOT NULL,
  `relative_path` VARCHAR(500) NOT NULL,
  `mime_type` VARCHAR(100) NOT NULL,
  `size_bytes` BIGINT UNSIGNED NOT NULL,
  `created_by` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_ai_ads_media_store` (`store_id`,`subscription_id`,`created_at`),
  KEY `idx_ai_ads_media_cycle` (`store_id`,`subscription_id`,`cycle_start`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

ALTER TABLE `db_ai_ads_campaigns`
  ADD COLUMN `media_asset_id` BIGINT UNSIGNED DEFAULT NULL AFTER `product_id`;
