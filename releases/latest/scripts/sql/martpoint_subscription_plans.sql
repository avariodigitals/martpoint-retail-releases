-- ============================================================
-- MartPoint Retail: Subscription Plans Presets
-- ============================================================

-- Create plans table
CREATE TABLE IF NOT EXISTS `db_subscription_plans` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `plan_name` varchar(50) NOT NULL,
  `plan_code` varchar(20) NOT NULL,
  `description` text DEFAULT NULL,
  `branch_limit` int(11) NOT NULL DEFAULT 1,
  `user_limit` int(11) NOT NULL DEFAULT 3,
  `product_limit` int(11) NOT NULL DEFAULT 500,
  `service_limit` int(11) NOT NULL DEFAULT 100,
  `media_storage_limit_mb` int(11) NOT NULL DEFAULT 2048,
  `storefront_limit` int(11) NOT NULL DEFAULT 1,
  `custom_domain_limit` int(11) NOT NULL DEFAULT 1,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `display_order` int(11) NOT NULL DEFAULT 0,
  `created_date` date DEFAULT NULL,
  `created_time` time DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `plan_code` (`plan_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed default plans
INSERT INTO `db_subscription_plans`
  (`plan_name`, `plan_code`, `description`, `branch_limit`, `user_limit`, `product_limit`, `service_limit`, `media_storage_limit_mb`, `storefront_limit`, `custom_domain_limit`, `is_active`, `display_order`, `created_date`, `created_time`, `created_by`)
VALUES
  ('Basic', 'basic', 'Starter plan for single-location retailers', 1, 3, 500, 100, 2048, 1, 1, 1, 1, CURDATE(), CURTIME(), 'system'),
  ('Standard', 'standard', 'Growing business with multiple branches', 3, 10, 2000, 300, 5120, 1, 1, 1, 2, CURDATE(), CURTIME(), 'system'),
  ('Premium', 'premium', 'Advanced features for established businesses', 5, 25, 5000, 500, 10240, 2, 2, 1, 3, CURDATE(), CURTIME(), 'system'),
  ('Enterprise', 'enterprise', 'Unlimited scale for large operations', 10, 50, 10000, 1000, 20480, 3, 3, 1, 4, CURDATE(), CURTIME(), 'system');
