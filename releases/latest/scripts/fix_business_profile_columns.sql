-- Add missing Business Profile settings table (separate from db_store to avoid row-size limit)
CREATE TABLE IF NOT EXISTS `db_store_business_profile` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `industry_type` VARCHAR(50) DEFAULT NULL,
  `business_model` VARCHAR(50) DEFAULT NULL,
  `workflow_template_key` VARCHAR(50) DEFAULT NULL,
  `dashboard_template_key` VARCHAR(50) DEFAULT NULL,
  `storefront_theme_key` VARCHAR(50) DEFAULT NULL,
  `feature_flags_json` TEXT DEFAULT NULL,
  `label_overrides_json` TEXT DEFAULT NULL,
  `industry_settings_json` TEXT DEFAULT NULL,
  `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store_id` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
