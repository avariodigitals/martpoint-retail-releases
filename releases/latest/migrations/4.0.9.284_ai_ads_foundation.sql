-- MartPoint AI Ads: merchant-owned Meta connections, MartPoint subscriptions,
-- campaign drafts/state, and an operational audit trail. Ad spend is never stored
-- as a MartPoint payment.
CREATE TABLE IF NOT EXISTS `db_ai_ads_connections` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `meta_user_id` VARCHAR(80) DEFAULT NULL,
  `meta_business_id` VARCHAR(80) DEFAULT NULL,
  `ad_account_id` VARCHAR(80) DEFAULT NULL,
  `ad_account_name` VARCHAR(190) DEFAULT NULL,
  `page_id` VARCHAR(80) DEFAULT NULL,
  `page_name` VARCHAR(190) DEFAULT NULL,
  `access_token_ciphertext` MEDIUMTEXT DEFAULT NULL,
  `token_expires_at` DATETIME DEFAULT NULL,
  `status` VARCHAR(24) NOT NULL DEFAULT 'disconnected',
  `last_checked_at` DATETIME DEFAULT NULL,
  `last_error` VARCHAR(500) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`), UNIQUE KEY `uq_ai_ads_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `db_ai_ads_subscriptions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `plan_code` VARCHAR(32) NOT NULL,
  `status` VARCHAR(24) NOT NULL DEFAULT 'pending',
  `starts_at` DATETIME DEFAULT NULL,
  `expires_at` DATETIME DEFAULT NULL,
  `grace_days` TINYINT UNSIGNED NOT NULL DEFAULT 3,
  `payment_provider` VARCHAR(24) DEFAULT NULL,
  `payment_reference` VARCHAR(120) DEFAULT NULL,
  `months_paid` SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`), KEY `idx_ai_ads_subscription_store` (`store_id`,`status`,`expires_at`),
  UNIQUE KEY `uq_ai_ads_payment_reference` (`payment_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `db_ai_ads_campaigns` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `created_by` INT DEFAULT NULL,
  `request_key` CHAR(64) NOT NULL,
  `status` VARCHAR(32) NOT NULL DEFAULT 'draft',
  `prompt` TEXT NOT NULL,
  `objective` VARCHAR(40) DEFAULT NULL,
  `product_id` INT DEFAULT NULL,
  `headline` VARCHAR(255) DEFAULT NULL,
  `body` TEXT DEFAULT NULL,
  `destination_url` VARCHAR(1000) DEFAULT NULL,
  `location_text` VARCHAR(190) DEFAULT NULL,
  `daily_budget_minor` BIGINT UNSIGNED DEFAULT NULL,
  `currency` CHAR(3) NOT NULL DEFAULT 'NGN',
  `starts_at` DATETIME DEFAULT NULL,
  `ends_at` DATETIME DEFAULT NULL,
  `subscription_policy_accepted_at` DATETIME DEFAULT NULL,
  `meta_campaign_id` VARCHAR(100) DEFAULT NULL,
  `meta_adset_id` VARCHAR(100) DEFAULT NULL,
  `meta_ad_id` VARCHAR(100) DEFAULT NULL,
  `last_error` VARCHAR(500) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`), UNIQUE KEY `uq_ai_ads_request` (`store_id`,`request_key`),
  KEY `idx_ai_ads_campaign_store` (`store_id`,`status`,`ends_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `db_ai_ads_audit` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT DEFAULT NULL,
  `campaign_id` BIGINT UNSIGNED DEFAULT NULL,
  `actor_id` INT DEFAULT NULL,
  `event_key` VARCHAR(64) NOT NULL,
  `details` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`), KEY `idx_ai_ads_audit_store` (`store_id`,`created_at`),
  KEY `idx_ai_ads_audit_campaign` (`campaign_id`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
