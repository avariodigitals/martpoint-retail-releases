SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `db_approval_logs`;
CREATE TABLE `db_approval_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL DEFAULT '1',
  `branch_id` int DEFAULT NULL,
  `action_type` varchar(50) NOT NULL,
  `approval_type` varchar(50) NOT NULL,
  `requesting_user_id` int NOT NULL,
  `requesting_user_name` varchar(100) DEFAULT NULL,
  `approving_user_id` int DEFAULT NULL,
  `approving_user_name` varchar(100) DEFAULT NULL,
  `reason` text,
  `previous_value` text,
  `new_value` text,
  `status` enum('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  `approval_method_used` varchar(30) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `threshold` decimal(15,2) DEFAULT NULL,
  `device_info` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `approved_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store` (`store_id`),
  KEY `idx_type` (`approval_type`),
  KEY `idx_status` (`status`),
  KEY `idx_date` (`created_at`),
  KEY `idx_requester` (`requesting_user_id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `db_approval_settings`;
CREATE TABLE `db_approval_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL DEFAULT '1',
  `approval_system_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `business_control_mode` enum('simple','controlled') NOT NULL DEFAULT 'simple',
  `allow_self_approval` tinyint(1) NOT NULL DEFAULT '0',
  `discount_approval_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `discount_approval_method` varchar(30) NOT NULL DEFAULT 'none',
  `discount_limit` decimal(10,2) NOT NULL DEFAULT '0.00',
  `price_override_approval_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `price_override_approval_method` varchar(30) NOT NULL DEFAULT 'none',
  `void_sale_approval_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `void_sale_approval_method` varchar(30) NOT NULL DEFAULT 'none',
  `sale_return_approval_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `sale_return_approval_method` varchar(30) NOT NULL DEFAULT 'none',
  `edit_completed_sale_approval_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `edit_completed_sale_approval_method` varchar(30) NOT NULL DEFAULT 'none',
  `credit_sale_approval_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `credit_sale_approval_method` varchar(30) NOT NULL DEFAULT 'none',
  `credit_limit_override_approval_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `credit_limit_override_approval_method` varchar(30) NOT NULL DEFAULT 'none',
  `customer_balance_adjustment_approval_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `customer_balance_adjustment_approval_method` varchar(30) NOT NULL DEFAULT 'none',
  `negative_stock_sale_approval_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `negative_stock_sale_approval_method` varchar(30) NOT NULL DEFAULT 'none',
  `stock_adjustment_approval_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `stock_adjustment_approval_method` varchar(30) NOT NULL DEFAULT 'none',
  `inventory_transfer_approval_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `inventory_transfer_approval_method` varchar(30) NOT NULL DEFAULT 'none',
  `cost_price_change_approval_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `cost_price_change_approval_method` varchar(30) NOT NULL DEFAULT 'none',
  `selling_price_change_approval_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `selling_price_change_approval_method` varchar(30) NOT NULL DEFAULT 'none',
  `expense_approval_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `expense_approval_method` varchar(30) NOT NULL DEFAULT 'none',
  `expense_threshold` decimal(15,2) NOT NULL DEFAULT '0.00',
  `cash_variance_approval_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `cash_variance_approval_method` varchar(30) NOT NULL DEFAULT 'none',
  `reopen_shift_approval_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `reopen_shift_approval_method` varchar(30) NOT NULL DEFAULT 'none',
  `online_refund_approval_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `online_refund_approval_method` varchar(30) NOT NULL DEFAULT 'none',
  `cancel_online_order_approval_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `cancel_online_order_approval_method` varchar(30) NOT NULL DEFAULT 'none',
  `manual_payment_confirmation_approval_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `manual_payment_confirmation_approval_method` varchar(30) NOT NULL DEFAULT 'none',
  `purchase_approval_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `purchase_approval_method` varchar(30) NOT NULL DEFAULT 'none',
  `purchase_price_override_approval_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `purchase_price_override_approval_method` varchar(30) NOT NULL DEFAULT 'none',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `hold_delete_approval_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `hold_delete_approval_method` varchar(30) NOT NULL DEFAULT 'none',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_store` (`store_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `db_brevo`;
CREATE TABLE `db_brevo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL DEFAULT '0',
  `api_key` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sender_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `status` int DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `db_debt_reminder_history`;
CREATE TABLE `db_debt_reminder_history` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int unsigned NOT NULL DEFAULT '1',
  `customer_id` int unsigned NOT NULL,
  `customer_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `amount_due` decimal(18,2) NOT NULL DEFAULT '0.00',
  `channel` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'email' COMMENT 'email,sms,whatsapp',
  `status` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'sent' COMMENT 'sent,failed',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `sent_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_sent_at` (`sent_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `db_debt_reminder_settings`;
CREATE TABLE `db_debt_reminder_settings` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int unsigned NOT NULL DEFAULT '1',
  `customer_id` int unsigned NOT NULL,
  `enabled` tinyint(1) NOT NULL DEFAULT '1',
  `frequency` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'weekly' COMMENT 'daily,3days,weekly,biweekly,monthly',
  `max_reminders` int NOT NULL DEFAULT '0' COMMENT '0 = unlimited',
  `reminder_count` int NOT NULL DEFAULT '0',
  `last_reminder_sent` datetime DEFAULT NULL,
  `send_email` tinyint(1) NOT NULL DEFAULT '1',
  `send_sms` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_customer_store` (`customer_id`,`store_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `db_email_logs`;
CREATE TABLE `db_email_logs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int unsigned NOT NULL DEFAULT '1',
  `email_type` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `provider_used` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `recipient` varchar(512) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `status` enum('sent','failed','pending','retrying') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `error_message` text COLLATE utf8mb4_unicode_ci,
  `triggered_by` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `related_module` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `related_record_id` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `provider_response` text COLLATE utf8mb4_unicode_ci,
  `created_at` datetime DEFAULT NULL,
  `sent_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_status` (`status`),
  KEY `idx_type` (`email_type`),
  KEY `idx_store` (`store_id`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=37 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `db_email_templates`;
CREATE TABLE `db_email_templates` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int unsigned NOT NULL DEFAULT '1',
  `template_key` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `template_name` varchar(128) COLLATE utf8mb4_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `html_body` text COLLATE utf8mb4_unicode_ci,
  `text_body` text COLLATE utf8mb4_unicode_ci,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `send_copy_to_owner` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_template_key_store` (`template_key`,`store_id`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `db_expiry_settings`;
CREATE TABLE `db_expiry_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `alert_before_days` int NOT NULL DEFAULT '30',
  `stop_selling_expired` tinyint(1) NOT NULL DEFAULT '1',
  `email_alerts_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `alert_email` varchar(255) DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` time DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store` (`store_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `db_license_history`;
CREATE TABLE `db_license_history` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `license_code` text NOT NULL,
  `plan_name` varchar(50) DEFAULT NULL,
  `domain` varchar(255) DEFAULT NULL,
  `activated_at` datetime DEFAULT NULL,
  `deactivated_at` datetime DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store_status` (`store_id`,`status`),
  KEY `idx_created` (`created_at`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `db_license_otps`;
CREATE TABLE `db_license_otps` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `otp_code` varchar(6) NOT NULL,
  `otp_type` varchar(20) NOT NULL DEFAULT 'generate',
  `expires_at` datetime NOT NULL,
  `used` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_store_otp` (`store_id`,`otp_code`),
  KEY `idx_expires` (`expires_at`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `db_online_order_items`;
CREATE TABLE `db_online_order_items` (
  `id` int NOT NULL AUTO_INCREMENT,
  `order_id` int NOT NULL,
  `item_type` enum('product','service') DEFAULT 'product',
  `item_id` int NOT NULL,
  `item_name` varchar(200) DEFAULT NULL,
  `item_image` varchar(255) DEFAULT NULL,
  `qty` int DEFAULT '1',
  `unit_price` decimal(12,2) DEFAULT '0.00',
  `total_price` decimal(12,2) DEFAULT '0.00',
  `service_note` text,
  `status` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `idx_order` (`order_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `db_online_orders`;
CREATE TABLE `db_online_orders` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL DEFAULT '0',
  `order_code` varchar(50) NOT NULL,
  `customer_name` varchar(200) DEFAULT NULL,
  `customer_email` varchar(100) DEFAULT NULL,
  `customer_phone` varchar(20) DEFAULT NULL,
  `customer_address` text,
  `order_type` enum('product','service','mixed') DEFAULT 'product',
  `order_status` enum('pending','paid','processing','ready','completed','cancelled') DEFAULT 'pending',
  `payment_status` enum('unpaid','paid','partially_paid','failed','refunded') DEFAULT 'unpaid',
  `payment_method` enum('paystack','whatsapp','pay_on_delivery') DEFAULT 'pay_on_delivery',
  `paystack_reference` varchar(100) DEFAULT NULL,
  `paystack_amount` decimal(12,2) DEFAULT '0.00',
  `subtotal` decimal(12,2) DEFAULT '0.00',
  `delivery_fee` decimal(12,2) DEFAULT '0.00',
  `tax_amount` decimal(12,2) DEFAULT '0.00',
  `grand_total` decimal(12,2) DEFAULT '0.00',
  `service_date` date DEFAULT NULL,
  `service_time` varchar(20) DEFAULT NULL,
  `service_note` text,
  `table_number` varchar(20) DEFAULT NULL,
  `qr_code_id` int DEFAULT '0',
  `whatsapp_sent` tinyint(1) DEFAULT '0',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text,
  `status` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_store_status` (`store_id`,`order_status`),
  KEY `idx_created` (`created_at`),
  KEY `idx_paystack` (`paystack_reference`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `db_qr_codes`;
CREATE TABLE `db_qr_codes` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL DEFAULT '0',
  `qr_name` varchar(200) DEFAULT NULL,
  `qr_type` enum('store','product','service','category','table') DEFAULT 'store',
  `related_id` int DEFAULT '0',
  `table_number` varchar(20) DEFAULT NULL,
  `qr_image` varchar(255) DEFAULT NULL,
  `qr_data` text,
  `download_count` int DEFAULT '0',
  `status` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_store_type` (`store_id`,`qr_type`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `db_report_schedules`;
CREATE TABLE `db_report_schedules` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int unsigned NOT NULL DEFAULT '1',
  `report_type` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'daily_summary, low_stock, overdue_debt',
  `template_name` varchar(128) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `frequency` varchar(16) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'daily' COMMENT 'daily, weekly',
  `send_time` varchar(8) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '18:00' COMMENT 'HH:MM 24h format',
  `email_enabled` tinyint(1) NOT NULL DEFAULT '1',
  `email_recipients` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'comma-separated emails',
  `email_template_key` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT 'daily_business_summary',
  `whatsapp_enabled` tinyint(1) NOT NULL DEFAULT '0',
  `whatsapp_numbers` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'comma-separated with country code',
  `whatsapp_message_template` text COLLATE utf8mb4_unicode_ci,
  `last_run_at` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT NULL,
  `updated_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_report_type_store` (`report_type`,`store_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `db_services`;
CREATE TABLE `db_services` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL DEFAULT '0',
  `service_name` varchar(200) NOT NULL,
  `service_image` varchar(255) DEFAULT NULL,
  `category_id` int DEFAULT '0',
  `price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `discount_price` decimal(12,2) DEFAULT NULL,
  `service_duration` varchar(50) DEFAULT NULL,
  `description` text,
  `available_online` tinyint(1) DEFAULT '1',
  `requires_appointment` tinyint(1) DEFAULT '0',
  `requires_note` tinyint(1) DEFAULT '0',
  `location_type` enum('in-store','customer-location','online') DEFAULT 'in-store',
  `sort_order` int DEFAULT '0',
  `status` tinyint(1) DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_store_status` (`store_id`,`status`,`available_online`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `db_storefront_analytics`;
CREATE TABLE `db_storefront_analytics` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `page_url` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `referrer` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `search_term` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_new_user` tinyint(1) NOT NULL DEFAULT '1',
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `session_id` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `source` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_store_date` (`store_id`,`created_at`),
  KEY `idx_source` (`source`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `db_storefront_brands`;
CREATE TABLE `db_storefront_brands` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `brand_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `brand_logo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `brand_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_enabled` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_store_sort` (`store_id`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `db_storefront_faqs`;
CREATE TABLE `db_storefront_faqs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `question` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `answer` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `is_enabled` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_store_sort` (`store_id`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `db_storefront_instagram`;
CREATE TABLE `db_storefront_instagram` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `image_url` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `caption` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `link_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_enabled` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_store_sort` (`store_id`,`sort_order`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `db_storefront_settings`;
CREATE TABLE `db_storefront_settings` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL DEFAULT '0',
  `theme_id` int DEFAULT NULL,
  `store_slug` varchar(100) NOT NULL DEFAULT '',
  `store_description` text,
  `store_banner` varchar(255) DEFAULT NULL,
  `store_logo` varchar(255) DEFAULT NULL,
  `whatsapp_number` varchar(20) DEFAULT '',
  `store_email` varchar(100) DEFAULT '',
  `store_phone` varchar(20) DEFAULT '',
  `store_address` text,
  `default_branch_id` int DEFAULT '0',
  `store_status` enum('active','maintenance') DEFAULT 'active',
  `allow_paystack` tinyint(1) DEFAULT '1',
  `allow_whatsapp` tinyint(1) DEFAULT '1',
  `allow_pay_on_delivery` tinyint(1) DEFAULT '1',
  `allow_services` tinyint(1) DEFAULT '1',
  `allow_backorder` tinyint(1) DEFAULT '0',
  `show_search` tinyint(1) DEFAULT '1',
  `show_categories` tinyint(1) DEFAULT '1',
  `show_whatsapp_cta` tinyint(1) DEFAULT '1',
  `featured_products_limit` int DEFAULT '8',
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `primary_color` varchar(20) DEFAULT '#3B82F6',
  `secondary_color` varchar(20) DEFAULT '#10B981',
  `font_family` varchar(100) DEFAULT 'Inter',
  `button_style` varchar(50) DEFAULT 'rounded',
  `store_headline` varchar(255) DEFAULT NULL,
  `store_subheadline` varchar(500) DEFAULT NULL,
  `favicon` varchar(255) DEFAULT NULL,
  `desktop_banner` varchar(255) DEFAULT NULL,
  `mobile_banner` varchar(255) DEFAULT NULL,
  `instagram_url` varchar(500) DEFAULT NULL,
  `facebook_url` varchar(500) DEFAULT NULL,
  `tiktok_url` varchar(500) DEFAULT NULL,
  `x_url` varchar(500) DEFAULT NULL,
  `youtube_url` varchar(500) DEFAULT NULL,
  `business_hours` text,
  `announcement_bar` varchar(500) DEFAULT NULL,
  `announcement_bar_color` varchar(20) DEFAULT '#0F172A',
  `preview_mode` tinyint(1) DEFAULT '0',
  `preview_theme_id` int DEFAULT NULL,
  `meta_title` varchar(255) DEFAULT NULL,
  `meta_description` varchar(500) DEFAULT NULL,
  `footer_bg_color` varchar(20) DEFAULT '#0F172A',
  `header_text_color` varchar(20) DEFAULT '',
  `button_color` varchar(20) DEFAULT '#3B82F6',
  `instagram_access_token` varchar(500) DEFAULT NULL,
  `instagram_username` varchar(100) DEFAULT NULL,
  `google_places_api_key` varchar(255) DEFAULT NULL,
  `gmb_place_id` varchar(100) DEFAULT NULL,
  `testimonial_source` varchar(20) DEFAULT 'custom',
  `trust_badges_json` text,
  `newsletter_title` varchar(255) DEFAULT 'Stay in the Loop',
  `newsletter_subtitle` varchar(500) DEFAULT 'Subscribe for updates, deals and new arrivals.',
  `footer_style` varchar(50) DEFAULT 'standard',
  `footer_about_us` text,
  `footer_text_color` varchar(20) DEFAULT '#94A3B8',
  `footer_address_url` varchar(500) DEFAULT NULL,
  `meta_keywords` varchar(255) DEFAULT NULL,
  `google_analytics_id` varchar(50) DEFAULT NULL,
  `facebook_pixel_id` varchar(50) DEFAULT NULL,
  `robots_index` tinyint(1) DEFAULT '1',
  `custom_head_scripts` text,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_storefront_store` (`store_id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

DROP TABLE IF EXISTS `db_storefront_testimonials`;
CREATE TABLE `db_storefront_testimonials` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `customer_name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `customer_photo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `testimonial_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `rating` int DEFAULT '5',
  `is_enabled` tinyint(1) DEFAULT '1',
  `sort_order` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_store_sort` (`store_id`,`sort_order`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `db_subscription_license`;
CREATE TABLE `db_subscription_license` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL,
  `license_code` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plan_name` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'Basic',
  `subscription_start_date` date DEFAULT NULL,
  `subscription_end_date` date DEFAULT NULL,
  `subscription_status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVE',
  `branch_limit` int DEFAULT '1',
  `user_limit` int DEFAULT '5',
  `renewal_amount` decimal(20,2) DEFAULT NULL,
  `client_name` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `domain` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `installation_fingerprint` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `last_renewal_date` date DEFAULT NULL,
  `suspension_reason` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `reminder_90_sent` tinyint(1) DEFAULT '0',
  `reminder_60_sent` tinyint(1) DEFAULT '0',
  `reminder_30_last_sent` date DEFAULT NULL,
  `reminder_10_last_sent` date DEFAULT NULL,
  `expiry_notice_sent` tinyint(1) DEFAULT '0',
  `expired_followup_count` int DEFAULT '0',
  `expired_followup_last_sent` date DEFAULT NULL,
  `activated_by` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` time DEFAULT NULL,
  `updated_date` date DEFAULT NULL,
  `updated_time` time DEFAULT NULL,
  `status` int DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_id` (`store_id`),
  KEY `license_code` (`license_code`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
