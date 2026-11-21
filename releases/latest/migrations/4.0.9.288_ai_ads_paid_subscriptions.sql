-- Canonical Next Admin invoices drive AI Ads access. Ad spend remains payable
-- directly by the merchant to Meta; these rows are platform access only.
CREATE TABLE IF NOT EXISTS `db_ai_ads_billing` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT UNSIGNED NOT NULL,
  `plan_code` VARCHAR(32) NOT NULL,
  `billing_period` VARCHAR(12) NOT NULL,
  `status` VARCHAR(24) NOT NULL DEFAULT 'pending',
  `central_intent_id` CHAR(36) NOT NULL,
  `invoice_id` CHAR(36) NOT NULL,
  `invoice_item_id` CHAR(36) NOT NULL,
  `invoice_number` VARCHAR(80) DEFAULT NULL,
  `payment_reference` VARCHAR(64) NOT NULL,
  `checkout_url` VARCHAR(1000) DEFAULT NULL,
  `amount_kobo` BIGINT UNSIGNED NOT NULL DEFAULT 0,
  `expires_at` DATETIME DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ai_ads_billing_intent` (`central_intent_id`),
  UNIQUE KEY `uq_ai_ads_billing_invoice_item` (`invoice_item_id`),
  UNIQUE KEY `uq_ai_ads_billing_reference` (`payment_reference`),
  KEY `idx_ai_ads_billing_store_status` (`store_id`,`status`,`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_ai_ads_fulfillments` (
  `invoice_item_id` CHAR(36) NOT NULL,
  `event_id` CHAR(36) NOT NULL,
  `store_id` INT UNSIGNED NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`invoice_item_id`),
  UNIQUE KEY `uq_ai_ads_fulfillment_event` (`event_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Central keeps a payment-backed operational mirror so support can see which
-- Fleet received each entitlement and whether signed delivery was acknowledged.
CREATE TABLE IF NOT EXISTS `db_central_ai_ads_subscriptions` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `fleet_install_id` BIGINT UNSIGNED NOT NULL,
  `store_id` INT UNSIGNED NOT NULL,
  `billing_period` VARCHAR(12) NOT NULL,
  `status` VARCHAR(24) NOT NULL DEFAULT 'pending_fulfillment',
  `intent_id` CHAR(36) NOT NULL,
  `invoice_id` CHAR(36) NOT NULL,
  `invoice_item_id` CHAR(36) NOT NULL,
  `payment_reference` VARCHAR(64) DEFAULT NULL,
  `starts_at` DATETIME NOT NULL,
  `expires_at` DATETIME NOT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_central_ai_ads_invoice_item` (`invoice_item_id`),
  KEY `idx_central_ai_ads_store_status` (`fleet_install_id`,`store_id`,`status`,`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
