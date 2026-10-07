-- MartPoint — Orphan table repair (v4.0.9.102)
-- ============================================================
-- These three tables exist on long-lived installs but were created by NO
-- installer schema file and NO registered migration. A FRESH install was
-- therefore missing them, so the Sendchamp SMS provider and the storefront
-- customer OTP / session flows would fail with "Table doesn't exist".
--
-- Detected by: php scripts/mp_release.php check  (section 3, schema coverage)
-- Idempotent. Safe to re-run.

SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE IF NOT EXISTS `db_sendchamp` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `api_key` text NOT NULL,
  `sender_id` varchar(50) NOT NULL DEFAULT 'MartPoint',
  `route` varchar(50) NOT NULL DEFAULT 'non_dnd_nigeria',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_sendchamp_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_storefront_customer_otp` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `customer_id` int DEFAULT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `otp` varchar(6) NOT NULL,
  `verified` tinyint(1) DEFAULT '0',
  `attempts` int DEFAULT '0',
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_otp_lookup` (`store_id`, `phone`),
  KEY `idx_otp_expires` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_storefront_customer_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `phone` varchar(20) NOT NULL,
  `email` varchar(120) DEFAULT NULL,
  `session_token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `last_used_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_storefront_session_token` (`session_token`),
  KEY `idx_storefront_session_store` (`store_id`, `customer_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
