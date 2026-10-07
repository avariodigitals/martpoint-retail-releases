-- 4.0.9.75 — Phase 3: customer segments, campaigns, back-in-stock alerts.
-- Idempotent: CREATE IF NOT EXISTS only.

-- Saved segment definitions (preset key + optional custom rules JSON)
CREATE TABLE IF NOT EXISTS `db_customer_segments` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `segment_key` VARCHAR(40) NOT NULL,
  `definition_json` TEXT NULL,
  `created_by` VARCHAR(60) NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_cseg_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Campaigns: a message sent to a resolved segment audience
CREATE TABLE IF NOT EXISTS `db_campaigns` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `segment_id` INT NULL DEFAULT NULL,
  `segment_key` VARCHAR(40) NULL DEFAULT NULL,
  `audience_json` TEXT NULL,
  `channel` VARCHAR(20) NOT NULL DEFAULT 'email',
  `subject` VARCHAR(255) NULL DEFAULT NULL,
  `message` TEXT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'draft',
  `audience_count` INT NOT NULL DEFAULT 0,
  `sent_count` INT NOT NULL DEFAULT 0,
  `failed_count` INT NOT NULL DEFAULT 0,
  `created_by` VARCHAR(60) NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  `sent_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_camp_store` (`store_id`),
  KEY `idx_camp_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Per-recipient send records — idempotent (unique campaign+recipient)
CREATE TABLE IF NOT EXISTS `db_campaign_sends` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `campaign_id` INT NOT NULL,
  `channel` VARCHAR(20) NOT NULL,
  `recipient` VARCHAR(191) NOT NULL,
  `customer_name` VARCHAR(191) NULL DEFAULT NULL,
  `status` VARCHAR(20) NOT NULL DEFAULT 'pending',
  `error` VARCHAR(255) NULL DEFAULT NULL,
  `sent_at` DATETIME NULL DEFAULT NULL,
  `created_at` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_csend` (`campaign_id`, `channel`, `recipient`),
  KEY `idx_csend_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Back-in-stock subscriptions (storefront "notify me")
CREATE TABLE IF NOT EXISTS `db_stock_alerts` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `item_id` INT NOT NULL,
  `item_name` VARCHAR(191) NULL DEFAULT NULL,
  `email` VARCHAR(191) NULL DEFAULT NULL,
  `phone` VARCHAR(50) NULL DEFAULT NULL,
  `token` VARCHAR(64) NOT NULL,
  `notified` TINYINT(1) NOT NULL DEFAULT 0,
  `send_attempts` TINYINT NOT NULL DEFAULT 0,
  `last_attempt_at` DATETIME NULL DEFAULT NULL,
  `unsubscribed` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` DATETIME NOT NULL,
  `notified_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_stock_alert` (`store_id`, `item_id`, `email`),
  KEY `idx_stock_alert_item` (`item_id`, `notified`),
  KEY `idx_stock_alert_token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
