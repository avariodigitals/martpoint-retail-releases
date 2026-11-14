-- ============================================================================
-- MartPoint 4.0.9.24 — Audit Trail + fresh-install settings table parity
--
-- 1. Creates db_audit_trail (operational log of user actions). The page lives
--    under Settings → Audit Trail and is gated by the `audit_trail_view`
--    permission key — assign it to roles that should see the log.
-- 2. Back-fills the modular store settings tables for installs whose schema
--    predates the 4.0.2→4.0.3 modularization migration (all IF NOT EXISTS).
-- ============================================================================

CREATE TABLE IF NOT EXISTS `db_audit_trail` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` int(11) UNSIGNED NOT NULL DEFAULT 1,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `module` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `action` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ref_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `description` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store` (`store_id`),
  KEY `idx_module` (`module`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_email_settings` (
  `store_id` int(11) NOT NULL,
  `email_provider` varchar(50) DEFAULT 'smtp',
  `email_from_name` varchar(255) DEFAULT NULL,
  `email_from_email` varchar(255) DEFAULT NULL,
  `email_reply_to` varchar(255) DEFAULT NULL,
  `smtp_crypto` varchar(50) DEFAULT NULL,
  `resend_api_key` varchar(255) DEFAULT NULL,
  `resend_from_email` varchar(255) DEFAULT NULL,
  `resend_from_name` varchar(255) DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_report_schedules` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` int(11) UNSIGNED NOT NULL DEFAULT 1,
  `report_type` varchar(64) NOT NULL COMMENT 'daily_summary, low_stock, overdue_debt',
  `template_name` varchar(128) DEFAULT NULL,
  `frequency` varchar(16) NOT NULL DEFAULT 'daily' COMMENT 'daily, weekly',
  `send_time` varchar(8) NOT NULL DEFAULT '18:00' COMMENT 'HH:MM 24h format',
  `email_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `email_recipients` varchar(500) DEFAULT NULL COMMENT 'comma-separated emails',
  `email_template_key` varchar(64) DEFAULT 'daily_business_summary',
  `whatsapp_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `whatsapp_numbers` varchar(500) DEFAULT NULL COMMENT 'comma-separated with country code',
  `whatsapp_message_template` text DEFAULT NULL,
  `last_run_at` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_report_type_store` (`report_type`,`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
