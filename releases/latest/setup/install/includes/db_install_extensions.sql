-- MartPoint Retail Approval System Tables
-- Run this SQL to create approval tables

-- db_users.approval_pin is now folded into the db.txt CREATE TABLE definition.

CREATE TABLE IF NOT EXISTS db_approval_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  store_id INT NOT NULL DEFAULT 1,
  
  -- Global switch
  approval_system_enabled TINYINT(1) NOT NULL DEFAULT 0,
  business_control_mode ENUM('simple','controlled') NOT NULL DEFAULT 'simple',
  allow_self_approval TINYINT(1) NOT NULL DEFAULT 0,
  
  -- Approval method per type: none, manager_pin, manager_password, owner_pin, owner_password, either
  discount_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  discount_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  discount_limit DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  
  price_override_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  price_override_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  void_sale_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  void_sale_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  sale_return_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  sale_return_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  edit_completed_sale_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  edit_completed_sale_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  credit_sale_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  credit_sale_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  credit_limit_override_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  credit_limit_override_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  customer_balance_adjustment_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  customer_balance_adjustment_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  negative_stock_sale_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  negative_stock_sale_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  stock_adjustment_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  stock_adjustment_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  inventory_transfer_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  inventory_transfer_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  cost_price_change_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  cost_price_change_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  selling_price_change_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  selling_price_change_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  expense_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  expense_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  expense_threshold DECIMAL(15,2) NOT NULL DEFAULT 0.00,
  
  cash_variance_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  cash_variance_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  reopen_shift_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  reopen_shift_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  online_refund_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  online_refund_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  cancel_online_order_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  cancel_online_order_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  manual_payment_confirmation_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  manual_payment_confirmation_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  purchase_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  purchase_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',
  
  purchase_price_override_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  purchase_price_override_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',

  hold_delete_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  hold_delete_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',

  bnpl_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
  bnpl_approval_method VARCHAR(30) NOT NULL DEFAULT 'none',

  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

  UNIQUE KEY unique_store (store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_approval_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  store_id INT NOT NULL DEFAULT 1,
  branch_id INT NULL,
  
  action_type VARCHAR(50) NOT NULL,
  approval_type VARCHAR(50) NOT NULL,
  
  requesting_user_id INT NOT NULL,
  requesting_user_name VARCHAR(100) NULL,
  approving_user_id INT NULL,
  approving_user_name VARCHAR(100) NULL,
  
  reason TEXT NULL,
  previous_value TEXT NULL,
  new_value TEXT NULL,
  
  status ENUM('pending','approved','rejected','cancelled') NOT NULL DEFAULT 'pending',
  approval_method_used VARCHAR(30) NULL,
  
  amount DECIMAL(15,2) NULL,
  threshold DECIMAL(15,2) NULL,
  
  device_info VARCHAR(255) NULL,
  ip_address VARCHAR(45) NULL,
  
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  approved_at TIMESTAMP NULL,
  
  KEY idx_store (store_id),
  KEY idx_type (approval_type),
  KEY idx_status (status),
  KEY idx_date (created_at),
  KEY idx_requester (requesting_user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- =====================================================
-- MARTPOINT RETAIL: CUSTOMER LOYALTY & REWARDS MODULE
-- =====================================================

-- Add loyalty columns to db_customers (now folded into db.txt CREATE TABLE)
-- ALTER TABLE db_customers
--     ADD COLUMN loyalty_points DECIMAL(15,2) NOT NULL DEFAULT 0,
--     ADD COLUMN lifetime_spend DECIMAL(15,2) NOT NULL DEFAULT 0,
--     ADD COLUMN loyalty_tier VARCHAR(50) DEFAULT 'Bronze',
--     ADD COLUMN store_credit_balance DECIMAL(15,2) NOT NULL DEFAULT 0,
--     ADD COLUMN gift_card_balance DECIMAL(15,2) NOT NULL DEFAULT 0,
--     ADD COLUMN referral_code VARCHAR(20) DEFAULT NULL,
--     ADD COLUMN referred_by INT DEFAULT NULL,
--     ADD COLUMN referral_count INT NOT NULL DEFAULT 0,
--     ADD COLUMN birthday DATE DEFAULT NULL,
--     ADD COLUMN last_purchase_date DATE DEFAULT NULL,
--     ADD COLUMN average_order_value DECIMAL(15,2) DEFAULT 0,
--     ADD COLUMN favourite_products TEXT DEFAULT NULL,
--     ADD COLUMN photo VARCHAR(255) DEFAULT NULL;

-- Loyalty Settings (per store)
CREATE TABLE IF NOT EXISTS db_loyalty_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    loyalty_enabled TINYINT(1) NOT NULL DEFAULT 0,
    earning_type ENUM('spend_based','percentage_based','product_specific','service_specific') DEFAULT 'spend_based',
    spend_amount DECIMAL(15,2) DEFAULT 1000,
    points_earned DECIMAL(10,2) DEFAULT 1,
    percentage_rate DECIMAL(5,2) DEFAULT 2.00,
    redemption_rate DECIMAL(10,2) DEFAULT 10.00 COMMENT 'Points per currency unit discount',
    minimum_redemption_points DECIMAL(10,2) DEFAULT 100,
    maximum_redemption_per_sale DECIMAL(15,2) DEFAULT 0 COMMENT '0 = unlimited',
    allow_partial_redemption TINYINT(1) DEFAULT 1,
    tier_calculation ENUM('lifetime_spend','points') DEFAULT 'lifetime_spend',
    flexpay_points_timing ENUM('full_payment','immediately','disabled') DEFAULT 'full_payment',
    referral_enabled TINYINT(1) NOT NULL DEFAULT 0,
    referrer_reward_type ENUM('points','credit','discount') NOT NULL DEFAULT 'points',
    referrer_reward_value DECIMAL(15,2) NOT NULL DEFAULT 100.00,
    new_customer_reward_type ENUM('points','credit','discount') NOT NULL DEFAULT 'points',
    new_customer_reward_value DECIMAL(15,2) NOT NULL DEFAULT 50.00,
    referral_approval_required TINYINT(1) NOT NULL DEFAULT 1,
    created_date DATE DEFAULT NULL,
    created_time TIME DEFAULT NULL,
    created_by VARCHAR(50) DEFAULT NULL,
    UNIQUE KEY uk_store (store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Customer Tiers
CREATE TABLE IF NOT EXISTS db_loyalty_tiers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    tier_name VARCHAR(50) NOT NULL,
    minimum_spend DECIMAL(15,2) DEFAULT 0,
    minimum_points DECIMAL(15,2) DEFAULT 0,
    discount_percentage DECIMAL(5,2) DEFAULT 0,
    bonus_points_percentage DECIMAL(5,2) DEFAULT 0,
    priority_service TINYINT(1) DEFAULT 0,
    birthday_reward_type ENUM('discount','voucher','points','product') DEFAULT 'points',
    birthday_reward_value DECIMAL(10,2) DEFAULT 100,
    sort_order INT DEFAULT 0,
    status TINYINT(1) DEFAULT 1,
    created_date DATE DEFAULT NULL,
    INDEX idx_store (store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default tiers
INSERT INTO db_loyalty_tiers (store_id, tier_name, minimum_spend, minimum_points, discount_percentage, bonus_points_percentage, priority_service, birthday_reward_type, birthday_reward_value, sort_order, status, created_date)
SELECT id, 'Bronze', 0, 0, 0, 0, 0, 'points', 100, 1, 1, CURDATE() FROM db_store;

INSERT INTO db_loyalty_tiers (store_id, tier_name, minimum_spend, minimum_points, discount_percentage, bonus_points_percentage, priority_service, birthday_reward_type, birthday_reward_value, sort_order, status, created_date)
SELECT id, 'Silver', 50000, 500, 2, 5, 0, 'discount', 5, 2, 1, CURDATE() FROM db_store;

INSERT INTO db_loyalty_tiers (store_id, tier_name, minimum_spend, minimum_points, discount_percentage, bonus_points_percentage, priority_service, birthday_reward_type, birthday_reward_value, sort_order, status, created_date)
SELECT id, 'Gold', 150000, 1500, 5, 10, 1, 'discount', 10, 3, 1, CURDATE() FROM db_store;

INSERT INTO db_loyalty_tiers (store_id, tier_name, minimum_spend, minimum_points, discount_percentage, bonus_points_percentage, priority_service, birthday_reward_type, birthday_reward_value, sort_order, status, created_date)
SELECT id, 'Platinum', 500000, 5000, 10, 20, 1, 'voucher', 5000, 4, 1, CURDATE() FROM db_store;

-- Points Transactions
CREATE TABLE IF NOT EXISTS db_loyalty_points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    customer_id INT NOT NULL,
    sales_id INT DEFAULT NULL,
    transaction_type ENUM('earn','redeem','adjust','expire','bonus','birthday','referral','tier_upgrade') NOT NULL,
    points DECIMAL(10,2) NOT NULL DEFAULT 0,
    points_balance DECIMAL(10,2) NOT NULL DEFAULT 0,
    description VARCHAR(255) DEFAULT NULL,
    created_date DATE DEFAULT NULL,
    created_time TIME DEFAULT NULL,
    created_by VARCHAR(50) DEFAULT NULL,
    INDEX idx_customer (customer_id, store_id),
    INDEX idx_sales (sales_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Store Credit
CREATE TABLE IF NOT EXISTS db_store_credit (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    customer_id INT NOT NULL,
    credit_code VARCHAR(50) NOT NULL,
    amount DECIMAL(15,2) NOT NULL DEFAULT 0,
    balance DECIMAL(15,2) NOT NULL DEFAULT 0,
    source ENUM('refund','return','compensation','manual','promotion','loyalty_conversion') NOT NULL,
    sales_id INT DEFAULT NULL,
    expiry_date DATE DEFAULT NULL,
    notes TEXT DEFAULT NULL,
    status ENUM('active','used','expired','cancelled') DEFAULT 'active',
    created_date DATE DEFAULT NULL,
    created_time TIME DEFAULT NULL,
    created_by VARCHAR(50) DEFAULT NULL,
    INDEX idx_customer (customer_id, store_id),
    INDEX idx_code (credit_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Store Credit Usage
CREATE TABLE IF NOT EXISTS db_store_credit_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    credit_id INT NOT NULL,
    customer_id INT NOT NULL,
    sales_id INT NOT NULL,
    amount_used DECIMAL(15,2) NOT NULL DEFAULT 0,
    created_date DATE DEFAULT NULL,
    created_time TIME DEFAULT NULL,
    INDEX idx_credit (credit_id),
    INDEX idx_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Gift Cards
CREATE TABLE IF NOT EXISTS db_gift_cards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    card_number VARCHAR(50) NOT NULL,
    qr_code VARCHAR(255) DEFAULT NULL,
    customer_id INT DEFAULT NULL,
    initial_value DECIMAL(15,2) NOT NULL DEFAULT 0,
    current_balance DECIMAL(15,2) NOT NULL DEFAULT 0,
    issue_date DATE DEFAULT NULL,
    expiry_date DATE DEFAULT NULL,
    card_type ENUM('physical','digital') DEFAULT 'physical',
    status ENUM('active','redeemed','expired','cancelled') DEFAULT 'active',
    notes TEXT DEFAULT NULL,
    created_date DATE DEFAULT NULL,
    created_time TIME DEFAULT NULL,
    created_by VARCHAR(50) DEFAULT NULL,
    INDEX idx_store (store_id),
    INDEX idx_card (card_number),
    INDEX idx_customer (customer_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Gift Card Usage
CREATE TABLE IF NOT EXISTS db_gift_card_usage (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    card_id INT NOT NULL,
    customer_id INT NOT NULL,
    sales_id INT NOT NULL,
    amount_used DECIMAL(15,2) NOT NULL DEFAULT 0,
    created_date DATE DEFAULT NULL,
    created_time TIME DEFAULT NULL,
    INDEX idx_card (card_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Bonus Rules
CREATE TABLE IF NOT EXISTS db_loyalty_bonus_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    rule_name VARCHAR(100) NOT NULL,
    rule_type ENUM('double_points_day','weekend_bonus','holiday_bonus','campaign_bonus','birthday_bonus','referral_bonus','vip_bonus') NOT NULL,
    multiplier DECIMAL(4,2) DEFAULT 2.00,
    bonus_points DECIMAL(10,2) DEFAULT 0,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    days_of_week VARCHAR(50) DEFAULT NULL COMMENT 'e.g. 1,6 for Sat,Sun',
    status TINYINT(1) DEFAULT 1,
    created_date DATE DEFAULT NULL,
    created_by VARCHAR(50) DEFAULT NULL,
    INDEX idx_store (store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Referrals
CREATE TABLE IF NOT EXISTS db_referrals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    referrer_id INT NOT NULL,
    referred_id INT NOT NULL,
    referral_code VARCHAR(20) NOT NULL,
    status ENUM('pending','approved','rejected') DEFAULT 'pending',
    referrer_reward_type ENUM('points','credit','discount') DEFAULT 'points',
    referrer_reward_value DECIMAL(10,2) DEFAULT 100,
    new_customer_reward_type ENUM('points','credit','discount') DEFAULT 'points',
    new_customer_reward_value DECIMAL(10,2) DEFAULT 50,
    reward_issued TINYINT(1) DEFAULT 0,
    created_date DATE DEFAULT NULL,
    approved_date DATE DEFAULT NULL,
    INDEX idx_referrer (referrer_id),
    INDEX idx_referred (referred_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Rewards History (consolidated audit log)
CREATE TABLE IF NOT EXISTS db_rewards_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    customer_id INT NOT NULL,
    activity_type ENUM('points_earned','points_redeemed','store_credit_issued','store_credit_used','gift_card_issued','gift_card_redeemed','referral_reward','birthday_reward','tier_upgrade') NOT NULL,
    reference_id INT DEFAULT NULL,
    reference_type VARCHAR(50) DEFAULT NULL,
    amount DECIMAL(15,2) DEFAULT 0,
    points DECIMAL(10,2) DEFAULT 0,
    description VARCHAR(255) DEFAULT NULL,
    created_date DATE DEFAULT NULL,
    created_time TIME DEFAULT NULL,
    created_by VARCHAR(50) DEFAULT NULL,
    INDEX idx_customer (customer_id, store_id),
    INDEX idx_activity (activity_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Product/Service Specific Points
CREATE TABLE IF NOT EXISTS db_loyalty_product_points (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    item_id INT NOT NULL,
    bonus_points DECIMAL(10,2) DEFAULT 0,
    bonus_type ENUM('fixed','multiplier') DEFAULT 'fixed',
    status TINYINT(1) DEFAULT 1,
    created_date DATE DEFAULT NULL,
    INDEX idx_item (item_id, store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- MartPoint BNPL + Offline PO Database Migration
-- Run this SQL to create the required tables
-- SAFE: Uses IF NOT EXISTS to avoid breaking existing data

-- ============================================
-- BUY NOW PAY LATER (BNPL) TABLES
-- ============================================


CREATE TABLE IF NOT EXISTS `db_installment_plans` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL DEFAULT '1',
  `sales_id` int DEFAULT NULL COMMENT 'linked sale invoice',
  `customer_id` int NOT NULL,
  `plan_code` varchar(50) NOT NULL,
  `total_amount` double(20,4) NOT NULL DEFAULT '0.0000',
  `down_payment_amount` double(20,4) NOT NULL DEFAULT '0.0000',
  `down_payment_paid` tinyint(1) NOT NULL DEFAULT '0',
  `installment_count` int NOT NULL DEFAULT '1',
  `installment_amount` double(20,4) NOT NULL DEFAULT '0.0000',
  `frequency` varchar(20) NOT NULL DEFAULT 'weekly' COMMENT 'weekly, biweekly, monthly',
  `first_due_date` date NOT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'active' COMMENT 'active, completed, cancelled, defaulted',
  `late_fee_per_day` double(20,4) NOT NULL DEFAULT '0.0000',
  `total_paid` double(20,4) NOT NULL DEFAULT '0.0000',
  `total_late_fees` double(20,4) NOT NULL DEFAULT '0.0000',
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(50) DEFAULT NULL,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  KEY `sales_id` (`sales_id`),
  KEY `customer_id` (`customer_id`),
  KEY `status` (`status`),
  KEY `first_due_date` (`first_due_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_installment_payments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `plan_id` int NOT NULL,
  `installment_number` int NOT NULL DEFAULT '1',
  `due_date` date NOT NULL,
  `amount_due` double(20,4) NOT NULL DEFAULT '0.0000',
  `amount_paid` double(20,4) NOT NULL DEFAULT '0.0000',
  `late_fee` double(20,4) NOT NULL DEFAULT '0.0000',
  `paid_date` date DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'pending' COMMENT 'pending, paid, overdue, partial',
  `payment_type` varchar(50) DEFAULT NULL,
  `payment_note` text,
  `payment_reference` varchar(100) DEFAULT NULL,
  `account_id` int DEFAULT NULL,
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `plan_id` (`plan_id`),
  KEY `due_date` (`due_date`),
  KEY `status` (`status`),
  CONSTRAINT `db_installment_payments_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `db_installment_plans` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- OFFLINE PURCHASE QUEUE (server-side backup)
-- This stores purchase data that was queued offline
-- and synced when back online
-- ============================================

CREATE TABLE IF NOT EXISTS `db_offline_purchase_queue` (
  `id` int NOT NULL AUTO_INCREMENT,
  `store_id` int NOT NULL DEFAULT '1',
  `reference_no` varchar(100) DEFAULT NULL,
  `supplier_id` int NOT NULL,
  `warehouse_id` int DEFAULT NULL,
  `purchase_date` date DEFAULT NULL,
  `purchase_status` varchar(50) DEFAULT 'Draft' COMMENT 'Draft, Ordered, Received',
  `subtotal` double(20,4) DEFAULT '0.0000',
  `other_charges_amt` double(20,4) DEFAULT '0.0000',
  `discount_to_all_amt` double(20,4) DEFAULT '0.0000',
  `grand_total` double(20,4) DEFAULT '0.0000',
  `paid_amount` double(20,4) DEFAULT '0.0000',
  `payment_status` varchar(50) DEFAULT 'Unpaid',
  `purchase_note` text,
  `items_json` longtext COMMENT 'serialized cart items',
  `payments_json` longtext COMMENT 'serialized payments',
  `created_by` varchar(50) DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(50) DEFAULT NULL,
  `sync_status` varchar(20) DEFAULT 'pending' COMMENT 'pending, synced, failed',
  `synced_at` datetime DEFAULT NULL,
  `sync_error` text,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  KEY `sync_status` (`sync_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- PERMISSIONS: Add BNPL permissions to db_permissions table
-- Schema: id, store_id, role_id, permissions (varchar)
-- ============================================

INSERT INTO db_permissions (store_id, role_id, permissions)
SELECT DISTINCT d.store_id, d.role_id, 'installment_plans'
FROM db_permissions d
LEFT JOIN db_permissions p2
  ON p2.store_id = d.store_id AND p2.role_id = d.role_id AND p2.permissions = 'installment_plans'
WHERE d.permissions = 'sales_view' AND p2.id IS NULL;

INSERT INTO db_permissions (store_id, role_id, permissions)
SELECT DISTINCT d.store_id, d.role_id, 'installment_payment'
FROM db_permissions d
LEFT JOIN db_permissions p2
  ON p2.store_id = d.store_id AND p2.role_id = d.role_id AND p2.permissions = 'installment_payment'
WHERE d.permissions = 'sales_payment_view' AND p2.id IS NULL;

INSERT INTO db_permissions (store_id, role_id, permissions)
SELECT DISTINCT d.store_id, d.role_id, 'installment_report'
FROM db_permissions d
LEFT JOIN db_permissions p2
  ON p2.store_id = d.store_id AND p2.role_id = d.role_id AND p2.permissions = 'installment_report'
WHERE d.permissions = 'sales_report' AND p2.id IS NULL;

-- ============================================
-- APPROVAL SETTINGS: Add BNPL approval columns (now folded into base CREATE TABLE)
-- ============================================
-- ALTER TABLE db_approval_settings
--   ADD COLUMN bnpl_approval_enabled TINYINT(1) NOT NULL DEFAULT 0,
--   ADD COLUMN bnpl_approval_method VARCHAR(30) NOT NULL DEFAULT 'none';

-- ============================================
-- CUSTOMERS: Add NIN/BVN verification columns (now folded into db.txt CREATE TABLE)
-- ============================================
-- ALTER TABLE db_customers
--   ADD COLUMN nin_bvn VARCHAR(50) DEFAULT NULL,
--   ADD COLUMN nin_verified TINYINT(1) NOT NULL DEFAULT 0,
--   ADD COLUMN nin_verified_at DATETIME DEFAULT NULL,
--   ADD COLUMN nin_waived TINYINT(1) NOT NULL DEFAULT 0,
--   ADD COLUMN nin_waived_by VARCHAR(50) DEFAULT NULL,
--   ADD COLUMN nin_waived_at DATETIME DEFAULT NULL;

-- ============================================
-- STORE: Add NIN API settings columns (now folded into db.txt CREATE TABLE)
-- ============================================
-- ALTER TABLE db_store
--   ADD COLUMN nin_api_enabled TINYINT(1) NOT NULL DEFAULT 0,
--   ADD COLUMN nin_api_url VARCHAR(500) DEFAULT NULL,
--   ADD COLUMN nin_api_key VARCHAR(500) DEFAULT NULL,
--   ADD COLUMN nin_api_provider VARCHAR(50) DEFAULT 'ninbvnportal',
--   ADD COLUMN nin_api_cost DECIMAL(10,2) NOT NULL DEFAULT 50.00;

-- ============================================
-- CUSTOMERS: Add notes column for existing installations
-- (folded into db.txt CREATE TABLE for fresh installs)
-- ============================================
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_customers' AND column_name = 'notes');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_customers` ADD COLUMN `notes` TEXT NULL DEFAULT NULL', 'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ============================================
-- NIN VERIFICATION LOG: Track usage for billing
-- ============================================
CREATE TABLE IF NOT EXISTS db_nin_verification_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  store_id INT NOT NULL DEFAULT 1,
  user_id INT DEFAULT NULL,
  user_name VARCHAR(50) DEFAULT NULL,
  customer_id INT DEFAULT NULL,
  nin_bvn VARCHAR(50) DEFAULT NULL,
  provider VARCHAR(50) DEFAULT 'demo',
  status VARCHAR(20) DEFAULT NULL,
  response_message TEXT DEFAULT NULL,
  cost DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  is_mock TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  INDEX idx_store_created (store_id, created_at),
  INDEX idx_user_created (user_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
-- ============================================================
-- MartPoint Retail: Subscription Plans + Overrides Migration
-- ============================================================

-- 1. Plans table
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
  ('Enterprise', 'enterprise', 'Unlimited scale for large operations', 10, 50, 10000, 1000, 20480, 3, 3, 1, 4, CURDATE(), CURTIME(), 'system')
ON DUPLICATE KEY UPDATE plan_name=VALUES(plan_name), description=VALUES(description), branch_limit=VALUES(branch_limit), user_limit=VALUES(user_limit), product_limit=VALUES(product_limit), service_limit=VALUES(service_limit), media_storage_limit_mb=VALUES(media_storage_limit_mb);

-- 2. Override logging table
CREATE TABLE IF NOT EXISTS `db_license_limit_overrides` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `field_name` varchar(50) NOT NULL,
  `original_limit` int(11) NOT NULL,
  `override_limit` int(11) NOT NULL,
  `override_enabled` tinyint(1) NOT NULL DEFAULT 1,
  `override_reason` text DEFAULT NULL,
  `override_expiry` date DEFAULT NULL,
  `overridden_by` varchar(50) DEFAULT NULL,
  `overridden_at` datetime DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` time DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  KEY `field_name` (`field_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Create subscription license table if not exists
CREATE TABLE IF NOT EXISTS `db_subscription_license` (
  `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `license_code` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `plan_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT 'Basic',
  `subscription_start_date` date DEFAULT NULL,
  `subscription_end_date` date DEFAULT NULL,
  `subscription_status` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'ACTIVE',
  `branch_limit` int(11) DEFAULT 1,
  `user_limit` int(11) DEFAULT 5,
  `product_limit` int(11) DEFAULT 500,
  `service_limit` int(11) DEFAULT 100,
  `media_storage_limit_mb` int(11) DEFAULT 2048,
  `storefront_limit` int(11) DEFAULT 1,
  `custom_domain_limit` int(11) DEFAULT 1,
  `whatsapp_number` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `client_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `domain` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `installation_fingerprint` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `renewal_amount` decimal(20,2) DEFAULT NULL,
  `last_renewal_date` date DEFAULT NULL,
  `suspension_reason` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `reminder_90_sent` tinyint(1) DEFAULT 0,
  `reminder_60_sent` tinyint(1) DEFAULT 0,
  `reminder_30_last_sent` date DEFAULT NULL,
  `reminder_10_last_sent` date DEFAULT NULL,
  `expiry_notice_sent` tinyint(1) DEFAULT 0,
  `expired_followup_count` int(11) DEFAULT 0,
  `expired_followup_last_sent` date DEFAULT NULL,
  `activated_by` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` time DEFAULT NULL,
  `updated_date` date DEFAULT NULL,
  `updated_time` time DEFAULT NULL,
  `status` int(1) DEFAULT 1,
  `override_branch_limit` int(11) DEFAULT NULL,
  `override_user_limit` int(11) DEFAULT NULL,
  `override_product_limit` int(11) DEFAULT NULL,
  `override_service_limit` int(11) DEFAULT NULL,
  `override_media_storage_limit_mb` int(11) DEFAULT NULL,
  `override_reason` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `override_expiry` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `store_id` (`store_id`),
  KEY `license_code` (`license_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3b. Plan-limit/meta fields are now folded into the db_subscription_license CREATE TABLE above.
-- SET @col_exists = (SELECT 1 FROM information_schema.columns WHERE table_name = 'db_subscription_license' AND column_name = 'product_limit' AND table_schema = DATABASE());
-- SET @sql = IF(@col_exists IS NULL, 'ALTER TABLE `db_subscription_license` ADD COLUMN `product_limit` int(11) DEFAULT 500', 'SELECT 1');
-- PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
-- (service_limit, media_storage_limit_mb, storefront_limit, custom_domain_limit,
--  whatsapp_number, client_name, domain similarly folded)

-- 4. Override fields are now folded into the db_subscription_license CREATE TABLE above.
-- SET @col_exists = (SELECT 1 FROM information_schema.columns WHERE table_name = 'db_subscription_license' AND column_name = 'override_branch_limit' AND table_schema = DATABASE());
-- SET @sql = IF(@col_exists IS NULL, 'ALTER TABLE `db_subscription_license` ADD COLUMN `override_branch_limit` int(11) DEFAULT NULL', 'SELECT 1');
-- PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
-- ... (other override columns similarly folded)
-- ============================================================
-- MartPoint Retail — Auto-Update System (Phase 1)
-- Run this in phpMyAdmin once to add the update-tracking tables
-- ============================================================

-- Tracks every auto-update job (status, steps, logs, backups)
CREATE TABLE IF NOT EXISTS `db_system_updates` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL DEFAULT 1,
  `from_version` VARCHAR(20) NOT NULL,
  `to_version` VARCHAR(20) NOT NULL,
  `status` ENUM('pending','running','success','failed','restored') NOT NULL DEFAULT 'pending',
  `current_step` TINYINT UNSIGNED NOT NULL DEFAULT 0,
  `total_steps` TINYINT UNSIGNED NOT NULL DEFAULT 8,
  `step_label` VARCHAR(100) DEFAULT NULL,
  `backup_db_path` VARCHAR(500) DEFAULT NULL,
  `backup_files_path` VARCHAR(500) DEFAULT NULL,
  `log` TEXT DEFAULT NULL,
  `error_message` TEXT DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `completed_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `store_id` (`store_id`),
  KEY `status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tracks schema migrations that have already been applied
CREATE TABLE IF NOT EXISTS `db_schema_migrations` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `version` VARCHAR(20) NOT NULL,
  `filename` VARCHAR(200) NOT NULL,
  `applied_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_version_file` (`version`,`filename`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Add update channel URL to sitesettings (folded into db.txt CREATE TABLE + INSERT)
-- ALTER TABLE `db_sitesettings`
-- ADD COLUMN `update_channel_url` VARCHAR(500) DEFAULT NULL;

-- Seed update channel with default GitHub raw URL (already seeded by db.txt INSERT; change in db_sitesettings if needed)
-- UPDATE `db_sitesettings` SET `update_channel_url` = 'https://raw.githubusercontent.com/YOUR_USERNAME/martpoint-retail-releases/main/releases/latest/' WHERE `id` = 1;
-- ============================================================
-- MartPoint Location Migration
-- West Africa + UK + US focused country/state/city support
-- ============================================================

-- 1. Create db_cities table
CREATE TABLE IF NOT EXISTS db_cities (
  id INT AUTO_INCREMENT PRIMARY KEY,
  city VARCHAR(255) NOT NULL,
  state_id INT NOT NULL,
  status TINYINT(1) DEFAULT 1,
  store_id INT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Seed West African + UK + US states (if not already present)
--    so that the city subqueries below can resolve their state_id.
INSERT IGNORE INTO `db_states` (`id`, `store_id`, `state_code`, `state`, `country_code`, `country_id`, `country`, `added_on`, `company_id`, `status`) VALUES
(52, 2, 'NG0001', 'Lagos', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(53, 2, 'NG0002', 'Oyo', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(54, 2, 'NG0003', 'Ogun', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(55, 2, 'NG0004', 'FCT', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(56, 2, 'NG0005', 'Rivers', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(57, 2, 'NG0006', 'Kano', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(58, 2, 'NG0007', 'Kaduna', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(59, 2, 'NG0008', 'Enugu', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(60, 2, 'NG0009', 'Anambra', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(61, 2, 'NG0010', 'Imo', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(62, 2, 'NG0011', 'Akwa Ibom', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(63, 2, 'NG0012', 'Cross River', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(64, 2, 'NG0013', 'Edo', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(65, 2, 'NG0014', 'Delta', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(66, 2, 'NG0015', 'Plateau', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(67, 2, 'NG0016', 'Borno', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(68, 2, 'NG0017', 'Sokoto', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(69, 2, 'NG0018', 'Kwara', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(70, 2, 'NG0019', 'Ondo', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(71, 2, 'NG0020', 'Ekiti', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(72, 2, 'NG0021', 'Osun', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(73, 2, 'NG0022', 'Bauchi', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(74, 2, 'NG0023', 'Adamawa', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(75, 2, 'NG0024', 'Abia', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(76, 2, 'NG0025', 'Bayelsa', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(77, 2, 'NG0026', 'Benue', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(78, 2, 'NG0027', 'Ebonyi', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(79, 2, 'NG0028', 'Gombe', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(80, 2, 'NG0029', 'Jigawa', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(81, 2, 'NG0030', 'Katsina', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(82, 2, 'NG0031', 'Kebbi', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(83, 2, 'NG0032', 'Kogi', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(84, 2, 'NG0033', 'Nasarawa', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(85, 2, 'NG0034', 'Niger', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(86, 2, 'NG0035', 'Taraba', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(87, 2, 'NG0036', 'Yobe', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1),
(88, 2, 'NG0037', 'Zamfara', 'NG', NULL, 'Nigeria', CURDATE(), NULL, 1);

-- 3. Seed major cities for West African states + UK + US
--    (Run this after your db_states table already has matching states)

-- NIGERIA
INSERT INTO db_cities (city, state_id) VALUES
('Lagos', (SELECT id FROM db_states WHERE state = 'Lagos' LIMIT 1)),
('Ikeja', (SELECT id FROM db_states WHERE state = 'Lagos' LIMIT 1)),
('Lekki', (SELECT id FROM db_states WHERE state = 'Lagos' LIMIT 1)),
('Victoria Island', (SELECT id FROM db_states WHERE state = 'Lagos' LIMIT 1)),
('Ibadan', (SELECT id FROM db_states WHERE state = 'Oyo' LIMIT 1)),
('Abeokuta', (SELECT id FROM db_states WHERE state = 'Ogun' LIMIT 1)),
('Abuja', (SELECT id FROM db_states WHERE state = 'FCT' LIMIT 1)),
('Wuse', (SELECT id FROM db_states WHERE state = 'FCT' LIMIT 1)),
('Garki', (SELECT id FROM db_states WHERE state = 'FCT' LIMIT 1)),
('Port Harcourt', (SELECT id FROM db_states WHERE state = 'Rivers' LIMIT 1)),
('Kano', (SELECT id FROM db_states WHERE state = 'Kano' LIMIT 1)),
('Kaduna', (SELECT id FROM db_states WHERE state = 'Kaduna' LIMIT 1)),
('Enugu', (SELECT id FROM db_states WHERE state = 'Enugu' LIMIT 1)),
('Onitsha', (SELECT id FROM db_states WHERE state = 'Anambra' LIMIT 1)),
('Awka', (SELECT id FROM db_states WHERE state = 'Anambra' LIMIT 1)),
('Owerri', (SELECT id FROM db_states WHERE state = 'Imo' LIMIT 1)),
('Uyo', (SELECT id FROM db_states WHERE state = 'Akwa Ibom' LIMIT 1)),
('Calabar', (SELECT id FROM db_states WHERE state = 'Cross River' LIMIT 1)),
('Benin City', (SELECT id FROM db_states WHERE state = 'Edo' LIMIT 1)),
('Warri', (SELECT id FROM db_states WHERE state = 'Delta' LIMIT 1)),
('Asaba', (SELECT id FROM db_states WHERE state = 'Delta' LIMIT 1)),
('Jos', (SELECT id FROM db_states WHERE state = 'Plateau' LIMIT 1)),
('Maiduguri', (SELECT id FROM db_states WHERE state = 'Borno' LIMIT 1)),
('Sokoto', (SELECT id FROM db_states WHERE state = 'Sokoto' LIMIT 1)),
('Ilorin', (SELECT id FROM db_states WHERE state = 'Kwara' LIMIT 1)),
('Akure', (SELECT id FROM db_states WHERE state = 'Ondo' LIMIT 1)),
('Ado-Ekiti', (SELECT id FROM db_states WHERE state = 'Ekiti' LIMIT 1)),
('Osogbo', (SELECT id FROM db_states WHERE state = 'Osun' LIMIT 1)),
('Bauchi', (SELECT id FROM db_states WHERE state = 'Bauchi' LIMIT 1)),
('Yola', (SELECT id FROM db_states WHERE state = 'Adamawa' LIMIT 1))
ON DUPLICATE KEY UPDATE city = city;

-- GHANA
INSERT INTO db_cities (city, state_id) VALUES
('Accra', (SELECT id FROM db_states WHERE state = 'Greater Accra' LIMIT 1)),
('Tema', (SELECT id FROM db_states WHERE state = 'Greater Accra' LIMIT 1)),
('Kumasi', (SELECT id FROM db_states WHERE state = 'Ashanti' LIMIT 1)),
('Tamale', (SELECT id FROM db_states WHERE state = 'Northern' LIMIT 1)),
('Sekondi-Takoradi', (SELECT id FROM db_states WHERE state = 'Western' LIMIT 1)),
('Cape Coast', (SELECT id FROM db_states WHERE state = 'Central' LIMIT 1)),
('Sunyani', (SELECT id FROM db_states WHERE state = 'Bono' LIMIT 1)),
('Ho', (SELECT id FROM db_states WHERE state = 'Volta' LIMIT 1)),
('Wa', (SELECT id FROM db_states WHERE state = 'Upper West' LIMIT 1)),
('Bolgatanga', (SELECT id FROM db_states WHERE state = 'Upper East' LIMIT 1))
ON DUPLICATE KEY UPDATE city = city;

-- UNITED KINGDOM
INSERT INTO db_cities (city, state_id) VALUES
('London', (SELECT id FROM db_states WHERE state = 'England' LIMIT 1)),
('Manchester', (SELECT id FROM db_states WHERE state = 'England' LIMIT 1)),
('Birmingham', (SELECT id FROM db_states WHERE state = 'England' LIMIT 1)),
('Liverpool', (SELECT id FROM db_states WHERE state = 'England' LIMIT 1)),
('Leeds', (SELECT id FROM db_states WHERE state = 'England' LIMIT 1)),
('Edinburgh', (SELECT id FROM db_states WHERE state = 'Scotland' LIMIT 1)),
('Glasgow', (SELECT id FROM db_states WHERE state = 'Scotland' LIMIT 1)),
('Cardiff', (SELECT id FROM db_states WHERE state = 'Wales' LIMIT 1)),
('Swansea', (SELECT id FROM db_states WHERE state = 'Wales' LIMIT 1)),
('Belfast', (SELECT id FROM db_states WHERE state = 'Northern Ireland' LIMIT 1))
ON DUPLICATE KEY UPDATE city = city;

-- UNITED STATES
INSERT INTO db_cities (city, state_id) VALUES
('New York', (SELECT id FROM db_states WHERE state = 'New York' LIMIT 1)),
('Los Angeles', (SELECT id FROM db_states WHERE state = 'California' LIMIT 1)),
('San Francisco', (SELECT id FROM db_states WHERE state = 'California' LIMIT 1)),
('Chicago', (SELECT id FROM db_states WHERE state = 'Illinois' LIMIT 1)),
('Houston', (SELECT id FROM db_states WHERE state = 'Texas' LIMIT 1)),
('Dallas', (SELECT id FROM db_states WHERE state = 'Texas' LIMIT 1)),
('Phoenix', (SELECT id FROM db_states WHERE state = 'Arizona' LIMIT 1)),
('Philadelphia', (SELECT id FROM db_states WHERE state = 'Pennsylvania' LIMIT 1)),
('San Antonio', (SELECT id FROM db_states WHERE state = 'Texas' LIMIT 1)),
('San Diego', (SELECT id FROM db_states WHERE state = 'California' LIMIT 1)),
('Austin', (SELECT id FROM db_states WHERE state = 'Texas' LIMIT 1)),
('Jacksonville', (SELECT id FROM db_states WHERE state = 'Florida' LIMIT 1)),
('Miami', (SELECT id FROM db_states WHERE state = 'Florida' LIMIT 1)),
('Fort Worth', (SELECT id FROM db_states WHERE state = 'Texas' LIMIT 1)),
('Columbus', (SELECT id FROM db_states WHERE state = 'Ohio' LIMIT 1)),
('Charlotte', (SELECT id FROM db_states WHERE state = 'North Carolina' LIMIT 1)),
('Seattle', (SELECT id FROM db_states WHERE state = 'Washington' LIMIT 1)),
('Denver', (SELECT id FROM db_states WHERE state = 'Colorado' LIMIT 1)),
('Boston', (SELECT id FROM db_states WHERE state = 'Massachusetts' LIMIT 1)),
('Atlanta', (SELECT id FROM db_states WHERE state = 'Georgia' LIMIT 1))
ON DUPLICATE KEY UPDATE city = city;
-- ============================================================
-- MartPoint Retail — Payment Modes Module Migration
-- ============================================================

-- 1. Create db_payment_modes table
CREATE TABLE IF NOT EXISTS db_payment_modes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  store_id INT NOT NULL DEFAULT 1,
  code VARCHAR(50) NOT NULL COMMENT 'Unique code slug e.g. cash, pos, bank_transfer',
  name VARCHAR(100) NOT NULL COMMENT 'Display name e.g. Cash, Bank Transfer',
  description TEXT DEFAULT NULL,
  enabled TINYINT(1) NOT NULL DEFAULT 1,
  is_default TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Only one mode can be default',
  is_system TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Built-in modes cannot be deleted',
  sort_order INT NOT NULL DEFAULT 0,
  requires_reference TINYINT(1) NOT NULL DEFAULT 0,
  requires_confirmation TINYINT(1) NOT NULL DEFAULT 0,
  affects_cash_in_hand TINYINT(1) NOT NULL DEFAULT 0,
  icon_class VARCHAR(100) DEFAULT NULL COMMENT 'FontAwesome icon class e.g. fa-money',
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_date DATE DEFAULT NULL,
  created_time TIME DEFAULT NULL,
  created_by VARCHAR(50) DEFAULT NULL,
  system_ip VARCHAR(50) DEFAULT NULL,
  system_name VARCHAR(255) DEFAULT NULL,
  UNIQUE KEY uk_code_store (code, store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Seed default payment modes
-- First clear any existing to avoid duplicates on re-run
-- We use INSERT IGNORE because of unique constraint

INSERT IGNORE INTO db_payment_modes (store_id, code, name, description, enabled, is_default, is_system, sort_order, requires_reference, requires_confirmation, affects_cash_in_hand, icon_class, status) VALUES
(1, 'cash', 'Cash', 'Physical cash collected at counter', 1, 1, 1, 1, 0, 0, 1, 'fa-money', 1),
(1, 'pos', 'POS', 'POS terminal / card reader payment', 1, 0, 1, 2, 1, 1, 0, 'fa-credit-card', 1),
(1, 'bank_transfer', 'Bank Transfer', 'Direct bank transfer from customer', 1, 0, 1, 3, 1, 1, 0, 'fa-university', 1),
(1, 'card', 'Card', 'Debit or credit card payment', 1, 0, 1, 4, 1, 1, 0, 'fa-credit-card-alt', 1),
(1, 'paystack', 'Paystack', 'Online payment via Paystack', 1, 0, 1, 5, 1, 1, 0, 'fa-link', 1),
(1, 'credit_sale', 'Credit Sale', 'Customer pays later / on credit', 1, 0, 1, 6, 0, 0, 0, 'fa-handshake-o', 1),
(1, 'split_payment', 'Split Payment', 'Multiple payment methods combined', 1, 0, 1, 7, 0, 0, 0, 'fa-columns', 1),
(1, 'wallet', 'Wallet', 'Customer wallet / store credit payment', 0, 0, 1, 8, 0, 0, 0, 'fa-google-wallet', 1),
(1, 'cheque', 'Cheque', 'Payment by cheque', 0, 0, 1, 9, 1, 1, 0, 'fa-file-text-o', 1),
(1, 'other', 'Other', 'Any other payment method', 0, 0, 1, 10, 0, 0, 0, 'fa-ellipsis-h', 1),
(1, 'flutterwave', 'Flutterwave', 'Online payment via Flutterwave', 1, 0, 1, 11, 1, 1, 0, 'fa-link', 1),
(1, 'moniepoint', 'Moniepoint', 'Payment via Moniepoint', 1, 0, 1, 12, 1, 1, 0, 'fa-mobile', 1),
(1, 'opay', 'Opay', 'Payment via Opay', 1, 0, 1, 13, 1, 1, 0, 'fa-mobile', 1);

-- 3. Update db_salespayments to support payment_mode_id and confirmation
-- These columns are now folded into the db.txt CREATE TABLE for db_salespayments.

-- ALTER TABLE db_salespayments
--   ADD COLUMN payment_mode_id INT DEFAULT NULL AFTER payment_type,
--   ADD COLUMN payment_reference VARCHAR(255) DEFAULT NULL AFTER payment_note,
--   ADD COLUMN confirmation_status TINYINT(1) DEFAULT 1 COMMENT '0=Pending, 1=Confirmed' AFTER payment_reference,
--   ADD COLUMN confirmed_by VARCHAR(50) DEFAULT NULL AFTER confirmation_status,
--   ADD COLUMN confirmed_date DATETIME DEFAULT NULL AFTER confirmed_by;

-- 4. Map existing payment_type values to payment_mode_id
-- This runs once to link old data to new table
UPDATE db_salespayments sp
JOIN db_payment_modes pm ON sp.payment_type = pm.name
SET sp.payment_mode_id = pm.id
WHERE sp.payment_mode_id IS NULL;

-- If any old records don't match, try matching by lowercase
UPDATE db_salespayments sp
JOIN db_payment_modes pm ON LOWER(sp.payment_type) = LOWER(pm.code)
SET sp.payment_mode_id = pm.id
WHERE sp.payment_mode_id IS NULL;

-- 5. Create index for faster lookups (indexes are now folded into db.txt CREATE TABLE)
-- ALTER TABLE db_salespayments ADD INDEX idx_payment_mode (payment_mode_id);
-- ALTER TABLE db_salespayments ADD INDEX idx_confirmation (confirmation_status);
-- ============================================================
-- MartPoint Retail — Paystack Integration Migration
-- ============================================================

-- 1. Paystack Settings (per store)
CREATE TABLE IF NOT EXISTS db_paystack_settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  store_id INT NOT NULL DEFAULT 1,
  secret_key VARCHAR(255) NOT NULL COMMENT 'Paystack Secret Key (sk_...)',
  public_key VARCHAR(255) NOT NULL COMMENT 'Paystack Public Key (pk_...)',
  enabled TINYINT(1) NOT NULL DEFAULT 0,
  test_mode TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Test, 0=Live',
  webhook_secret VARCHAR(255) DEFAULT NULL COMMENT 'For webhook signature verification',
  callback_url VARCHAR(500) DEFAULT NULL,
  status TINYINT(1) NOT NULL DEFAULT 1,
  created_date DATE DEFAULT NULL,
  created_time TIME DEFAULT NULL,
  created_by VARCHAR(50) DEFAULT NULL,
  UNIQUE KEY uk_store (store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Paystack Payment Links / Transactions tracking
CREATE TABLE IF NOT EXISTS db_paystack_payments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  store_id INT NOT NULL,
  sales_id INT DEFAULT NULL,
  customer_id INT DEFAULT NULL,
  customer_email VARCHAR(255) DEFAULT NULL,
  customer_phone VARCHAR(50) DEFAULT NULL,
  amount DECIMAL(18,2) NOT NULL,
  currency VARCHAR(10) DEFAULT 'NGN',
  paystack_reference VARCHAR(255) NOT NULL COMMENT 'Paystack transaction reference',
  paystack_access_code VARCHAR(255) DEFAULT NULL,
  paystack_authorization_url VARCHAR(500) DEFAULT NULL,
  payment_status VARCHAR(50) DEFAULT 'pending' COMMENT 'pending, success, failed, abandoned',
  payment_channel VARCHAR(50) DEFAULT NULL,
  paid_at DATETIME DEFAULT NULL,
  meta_data TEXT DEFAULT NULL COMMENT 'JSON of extra metadata',
  created_date DATETIME DEFAULT CURRENT_TIMESTAMP,
  updated_date DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_reference (paystack_reference),
  INDEX idx_sales (sales_id),
  INDEX idx_status (payment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- MARTPOINT RETAIL: MISSING TABLES & COLUMNS FROM PHP MIGRATIONS
-- Auto-extracted from PHP migration scripts for clean installs
-- ============================================================

-- --------------------------------------------------------
-- Batch/Lot & Barcode Tracking (from batch_lot_mrp_migration + run_all_migrations)
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS db_item_barcodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    barcode VARCHAR(100) NOT NULL DEFAULT '',
    batch_lot VARCHAR(100) NULL,
    serial_number VARCHAR(100) NULL,
    imei_number VARCHAR(50) NULL,
    warranty_months INT(3) NULL DEFAULT 0,
    purchase_price DECIMAL(20,2) DEFAULT 0,
    sales_price DECIMAL(20,2) DEFAULT 0,
    mrp DECIMAL(20,2) DEFAULT 0,
    qty DECIMAL(20,2) DEFAULT 0,
    expire_date DATE NULL,
    mfg_date DATE NULL,
    warehouse_id INT NULL,
    status TINYINT DEFAULT 1,
    created_date DATE,
    created_time TIME,
    INDEX idx_barcode (barcode),
    INDEX idx_item_id (item_id),
    INDEX idx_batch_lot (batch_lot),
    INDEX idx_item_status (item_id, status),
    UNIQUE KEY uk_serial_number (serial_number),
    UNIQUE KEY uk_imei_number (imei_number),
    FOREIGN KEY (item_id) REFERENCES db_items(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Folded into db.txt CREATE TABLE for db_items
-- ALTER TABLE db_items ADD COLUMN batch_lot VARCHAR(100) NULL AFTER mrp;
-- ALTER TABLE db_items ADD COLUMN serial_number VARCHAR(100) NULL AFTER custom_barcode;
-- ALTER TABLE db_items ADD COLUMN imei_number VARCHAR(50) NULL AFTER serial_number;
-- ALTER TABLE db_items ADD COLUMN warranty_months INT(3) NULL DEFAULT 0 AFTER imei_number;
-- ALTER TABLE db_items ADD COLUMN track_serial TINYINT(1) NOT NULL DEFAULT 0 AFTER warranty_months;
-- ALTER TABLE db_items ADD COLUMN track_imei TINYINT(1) NOT NULL DEFAULT 0 AFTER track_serial;

-- Folded into db.txt CREATE TABLE for db_purchaseitems
-- ALTER TABLE db_purchaseitems ADD COLUMN batch_lot VARCHAR(100) NULL AFTER description;
-- ALTER TABLE db_purchaseitems ADD COLUMN sold_serial_number VARCHAR(100) NULL AFTER item_id;
-- ALTER TABLE db_purchaseitems ADD COLUMN sold_imei_number VARCHAR(50) NULL AFTER sold_serial_number;

-- Folded into db.txt CREATE TABLE for db_salesitems
-- ALTER TABLE db_salesitems ADD COLUMN batch_lot VARCHAR(100) NULL AFTER description;
-- ALTER TABLE db_salesitems ADD COLUMN price_type VARCHAR(20) NULL DEFAULT 'wholesale' AFTER batch_lot;
-- ALTER TABLE db_salesitems ADD COLUMN sold_serial_number VARCHAR(100) NULL AFTER item_id;
-- ALTER TABLE db_salesitems ADD COLUMN sold_imei_number VARCHAR(50) NULL AFTER sold_serial_number;
-- ALTER TABLE db_salesitems ADD COLUMN barcode_id INT(11) NULL DEFAULT 0 AFTER item_id;

-- Folded into db.txt CREATE TABLE for db_holditems
-- ALTER TABLE db_holditems ADD COLUMN batch_lot VARCHAR(100) NULL AFTER description;
-- ALTER TABLE db_holditems ADD COLUMN price_type VARCHAR(20) NULL DEFAULT 'wholesale' AFTER batch_lot;
-- ALTER TABLE db_holditems ADD COLUMN sold_serial_number VARCHAR(100) NULL AFTER item_id;
-- ALTER TABLE db_holditems ADD COLUMN sold_imei_number VARCHAR(50) NULL AFTER sold_serial_number;
-- ALTER TABLE db_holditems ADD COLUMN barcode_id INT(11) NULL DEFAULT 0 AFTER item_id;

-- Folded into db.txt CREATE TABLE for db_salesitemsreturn
-- ALTER TABLE db_salesitemsreturn ADD COLUMN barcode_id INT(11) NULL DEFAULT 0 AFTER item_id;
-- ALTER TABLE db_salesitemsreturn ADD COLUMN sold_serial_number VARCHAR(100) NULL AFTER barcode_id;
-- ALTER TABLE db_salesitemsreturn ADD COLUMN sold_imei_number VARCHAR(50) NULL AFTER sold_serial_number;

-- --------------------------------------------------------
-- Laundry Service Type (from run_laundry_service_type_migration.php) — folded into db.txt
-- --------------------------------------------------------
-- ALTER TABLE db_items ADD COLUMN laundry_service_type VARCHAR(30) NULL COMMENT 'wash_iron, wash_only, iron_only, dry_clean' AFTER description;

-- --------------------------------------------------------
-- Service Deposit Columns (from run_service_deposit_migration.php) — folded into db.txt
-- --------------------------------------------------------
-- ALTER TABLE db_items ADD COLUMN deposit_required TINYINT(1) NOT NULL DEFAULT 0;
-- ALTER TABLE db_items ADD COLUMN deposit_percent DECIMAL(10,2) NOT NULL DEFAULT 0;

-- Create db_services if not exists (before altering)
CREATE TABLE IF NOT EXISTS db_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL DEFAULT 0,
    service_name VARCHAR(200) NOT NULL,
    service_image VARCHAR(255),
    category_id INT DEFAULT 0,
    price DECIMAL(12,2) NOT NULL DEFAULT 0,
    discount_price DECIMAL(12,2) DEFAULT NULL,
    service_duration VARCHAR(50),
    description TEXT,
    available_online TINYINT(1) DEFAULT 1,
    requires_appointment TINYINT(1) DEFAULT 0,
    requires_note TINYINT(1) DEFAULT 0,
    location_type ENUM('in-store','customer-location','online') DEFAULT 'in-store',
    sort_order INT DEFAULT 0,
    deposit_required TINYINT(1) DEFAULT 0,
    deposit_percent DECIMAL(10,2) DEFAULT 0,
    status TINYINT(1) DEFAULT 1,
    assigned_staff_id INT(10) DEFAULT NULL,
    staff_commission_percent DECIMAL(5,2) DEFAULT 0,
    industry_fields_json JSON DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_store_status (store_id, status, available_online)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Folded into db_services CREATE TABLE above
-- ALTER TABLE db_services ADD COLUMN assigned_staff_id INT(10) NULL AFTER status;
-- ALTER TABLE db_services ADD COLUMN staff_commission_percent DECIMAL(5,2) DEFAULT 0 AFTER assigned_staff_id;

-- --------------------------------------------------------
-- Kitchen Orders (from fix_kitchen_orders_table.php)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS db_kitchen_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sales_id INT NOT NULL,
    store_id INT NOT NULL,
    kds_status ENUM('new','preparing','ready','served') NOT NULL DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_sales (sales_id),
    KEY idx_store_status (store_id, kds_status),
    KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Laundry Orders (from fix_laundry_table.php + fix_laundry_items_table.php)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS db_laundry_orders (
    id INT(10) NOT NULL AUTO_INCREMENT,
    sales_id INT(10) NOT NULL,
    store_id INT(10) NOT NULL,
    tag_number VARCHAR(50) NULL,
    service_type VARCHAR(50) NULL DEFAULT 'standard',
    notes TEXT NULL,
    status VARCHAR(30) NOT NULL DEFAULT 'dropped_off',
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    PRIMARY KEY (id),
    KEY sales_id (sales_id),
    KEY store_id (store_id),
    KEY status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS db_laundry_order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    laundry_order_id INT NOT NULL,
    salesitem_id INT NOT NULL,
    item_id INT NOT NULL,
    service_type VARCHAR(20) NOT NULL DEFAULT 'wash_iron',
    item_status VARCHAR(20) NOT NULL DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_laundry_order_id (laundry_order_id),
    INDEX idx_item_status (item_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Tables (Restaurant) (from fix_tables_table.php)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS db_tables (
    id INT(10) NOT NULL AUTO_INCREMENT,
    store_id INT(10) NOT NULL,
    table_name VARCHAR(100) NOT NULL,
    table_code VARCHAR(20) NULL,
    zone VARCHAR(50) NULL,
    capacity INT(5) NOT NULL DEFAULT 4,
    status VARCHAR(20) NOT NULL DEFAULT 'available',
    qr_code_url VARCHAR(255) NULL,
    sort_order INT(5) NOT NULL DEFAULT 0,
    created_at DATETIME NULL,
    updated_at DATETIME NULL,
    PRIMARY KEY (id),
    KEY store_id (store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------
-- Service Staff Assignment (from migrate_staff_assignment.php)
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS db_service_staff (
    id INT(10) NOT NULL AUTO_INCREMENT,
    store_id INT(10) NOT NULL,
    service_id INT(10) NOT NULL,
    staff_id INT(10) NOT NULL,
    status INT(1) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Inactive',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY unique_service_staff (store_id, service_id, staff_id),
    KEY idx_service_id (service_id),
    KEY idx_staff_id (staff_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Storefront Settings & tables auto-created by Storefront_model are
-- handled with CREATE TABLE IF NOT EXISTS in the application code.
-- They will be created on first use if not present here.
-- --------------------------------------------------------


-- ============================================================
-- MARTPOINT RETAIL: MODEL-DERIVED TABLES
-- Extracted from application models for authoritative fresh-install schema.
-- ============================================================

-- --------------------------------------------------------
-- Storefront Model Tables
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS db_storefront_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL DEFAULT 0,
    store_slug VARCHAR(100) NOT NULL DEFAULT '',
    store_description TEXT,
    store_banner VARCHAR(255),
    store_logo VARCHAR(255),
    whatsapp_number VARCHAR(20) DEFAULT '',
    store_email VARCHAR(100) DEFAULT '',
    store_phone VARCHAR(20) DEFAULT '',
    store_address TEXT,
    default_branch_id INT DEFAULT 0,
    store_status ENUM('active','maintenance','deactivated') DEFAULT 'active',
    allow_paystack TINYINT(1) DEFAULT 1,
    allow_whatsapp TINYINT(1) DEFAULT 1,
    allow_pay_on_delivery TINYINT(1) DEFAULT 1,
    allow_services TINYINT(1) DEFAULT 1,
    allow_backorder TINYINT(1) DEFAULT 0,
    show_search TINYINT(1) DEFAULT 1,
    show_categories TINYINT(1) DEFAULT 1,
    show_whatsapp_cta TINYINT(1) DEFAULT 1,
    featured_products_limit INT DEFAULT 8,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    theme_id INT DEFAULT NULL,
    primary_color VARCHAR(20) DEFAULT '#3B82F6',
    secondary_color VARCHAR(20) DEFAULT '#10B981',
    font_family VARCHAR(100) DEFAULT 'Inter',
    button_style VARCHAR(50) DEFAULT 'rounded',
    store_headline VARCHAR(255) DEFAULT NULL,
    store_subheadline VARCHAR(500) DEFAULT NULL,
    favicon VARCHAR(255) DEFAULT NULL,
    desktop_banner VARCHAR(255) DEFAULT NULL,
    mobile_banner VARCHAR(255) DEFAULT NULL,
    instagram_url VARCHAR(500) DEFAULT NULL,
    facebook_url VARCHAR(500) DEFAULT NULL,
    tiktok_url VARCHAR(500) DEFAULT NULL,
    x_url VARCHAR(500) DEFAULT NULL,
    youtube_url VARCHAR(500) DEFAULT NULL,
    business_hours TEXT DEFAULT NULL,
    announcement_bar VARCHAR(500) DEFAULT NULL,
    announcement_bar_color VARCHAR(20) DEFAULT '#0F172A',
    preview_mode TINYINT(1) DEFAULT 0,
    preview_theme_id INT DEFAULT NULL,
    meta_title VARCHAR(255) DEFAULT NULL,
    meta_description VARCHAR(500) DEFAULT NULL,
    footer_bg_color VARCHAR(20) DEFAULT '#0F172A',
    header_text_color VARCHAR(20) DEFAULT '',
    footer_style VARCHAR(50) DEFAULT 'standard',
    footer_about_us TEXT DEFAULT NULL,
    footer_text_color VARCHAR(20) DEFAULT '#94A3B8',
    footer_address_url VARCHAR(500) DEFAULT NULL,
    button_color VARCHAR(20) DEFAULT '#3B82F6',
    meta_keywords VARCHAR(255) DEFAULT NULL,
    google_analytics_id VARCHAR(50) DEFAULT NULL,
    facebook_pixel_id VARCHAR(50) DEFAULT NULL,
    robots_index TINYINT(1) DEFAULT 1,
    custom_head_scripts TEXT DEFAULT NULL,
    shipping_notice TEXT DEFAULT NULL,
    shipping_methods_json TEXT DEFAULT NULL,
    trust_badges_json TEXT DEFAULT NULL,
    newsletter_title VARCHAR(255) DEFAULT NULL,
    newsletter_subtitle VARCHAR(500) DEFAULT NULL,
    instagram_access_token VARCHAR(255) DEFAULT NULL,
    instagram_username VARCHAR(100) DEFAULT NULL,
    google_places_api_key VARCHAR(255) DEFAULT NULL,
    gmb_place_id VARCHAR(255) DEFAULT NULL,
    UNIQUE KEY uk_storefront_store (store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_online_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL DEFAULT 0,
    order_code VARCHAR(50) NOT NULL,
    customer_name VARCHAR(200),
    customer_email VARCHAR(100),
    customer_phone VARCHAR(20),
    customer_address TEXT,
    order_type ENUM('product','service','mixed') DEFAULT 'product',
    order_status ENUM('pending','paid','processing','ready','completed','cancelled') DEFAULT 'pending',
    payment_status ENUM('unpaid','paid','partially_paid','failed','refunded') DEFAULT 'unpaid',
    payment_method ENUM('paystack','whatsapp','pay_on_delivery') DEFAULT 'pay_on_delivery',
    paystack_reference VARCHAR(100),
    paystack_amount DECIMAL(12,2) DEFAULT 0,
    subtotal DECIMAL(12,2) DEFAULT 0,
    delivery_fee DECIMAL(12,2) DEFAULT 0,
    tax_amount DECIMAL(12,2) DEFAULT 0,
    grand_total DECIMAL(12,2) DEFAULT 0,
    service_date DATE,
    service_time VARCHAR(20),
    service_note TEXT,
    table_number VARCHAR(20),
    qr_code_id INT DEFAULT 0,
    whatsapp_sent TINYINT(1) DEFAULT 0,
    ip_address VARCHAR(45),
    user_agent TEXT,
    status TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_store_status (store_id, order_status),
    INDEX idx_created (created_at),
    INDEX idx_paystack (paystack_reference)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_online_order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    item_type ENUM('product','service') DEFAULT 'product',
    item_id INT NOT NULL,
    item_name VARCHAR(200),
    item_image VARCHAR(255),
    qty INT DEFAULT 1,
    unit_price DECIMAL(12,2) DEFAULT 0,
    total_price DECIMAL(12,2) DEFAULT 0,
    service_note TEXT,
    status TINYINT(1) DEFAULT 1,
    INDEX idx_order (order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_storefront_themes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    theme_key VARCHAR(50) NOT NULL UNIQUE,
    theme_name VARCHAR(100) NOT NULL,
    industry VARCHAR(50) NOT NULL,
    description TEXT,
    default_primary_color VARCHAR(20) DEFAULT '#3B82F6',
    default_secondary_color VARCHAR(20) DEFAULT '#10B981',
    default_font_family VARCHAR(100) DEFAULT 'Inter',
    preview_image VARCHAR(255),
    status TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_storefront_banners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    banner_type ENUM('hero','promo') DEFAULT 'hero',
    banner_title VARCHAR(255),
    banner_subtitle VARCHAR(500),
    desktop_image VARCHAR(255),
    mobile_image VARCHAR(255),
    button_text VARCHAR(100),
    button_url VARCHAR(500),
    display_order INT DEFAULT 0,
    status TINYINT(1) DEFAULT 1,
    start_date DATE DEFAULT NULL,
    end_date DATE DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_store_status (store_id, status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_storefront_homepage_sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    section_key VARCHAR(50) NOT NULL,
    section_label VARCHAR(100),
    is_enabled TINYINT(1) DEFAULT 1,
    display_order INT DEFAULT 0,
    config_json TEXT,
    UNIQUE KEY uk_store_section (store_id, section_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_storefront_domains (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    domain_type ENUM('subdomain','custom') DEFAULT 'subdomain',
    domain_value VARCHAR(255) NOT NULL,
    verification_status ENUM('pending','verified','failed') DEFAULT 'pending',
    ssl_status ENUM('pending','active','expired') DEFAULT 'pending',
    connection_status ENUM('pending','connected','disconnected') DEFAULT 'pending',
    dns_instructions TEXT,
    verified_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uk_domain (domain_value),
    INDEX idx_store (store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_qr_codes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL DEFAULT 0,
    qr_name VARCHAR(200),
    qr_type ENUM('store','product','service','category','table') DEFAULT 'store',
    related_id INT DEFAULT 0,
    table_number VARCHAR(20),
    qr_image VARCHAR(255),
    qr_data TEXT,
    download_count INT DEFAULT 0,
    status TINYINT(1) DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_store_type (store_id, qr_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_storefront_brands (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    brand_name VARCHAR(100) NOT NULL,
    brand_logo VARCHAR(255) DEFAULT NULL,
    brand_url VARCHAR(500) DEFAULT NULL,
    is_enabled TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_store_sort (store_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_storefront_testimonials (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    customer_name VARCHAR(100) NOT NULL,
    customer_photo VARCHAR(255) DEFAULT NULL,
    testimonial_text TEXT NOT NULL,
    rating INT DEFAULT 5,
    is_enabled TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_store_sort (store_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_storefront_instagram (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    image_url VARCHAR(255) NOT NULL,
    caption VARCHAR(255) DEFAULT NULL,
    link_url VARCHAR(500) DEFAULT NULL,
    is_enabled TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_store_sort (store_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_storefront_faqs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    question VARCHAR(255) NOT NULL,
    answer TEXT NOT NULL,
    is_enabled TINYINT(1) DEFAULT 1,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_store_sort (store_id, sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_storefront_analytics (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL,
    page_url VARCHAR(500) NOT NULL,
    source VARCHAR(100) DEFAULT NULL,
    referrer VARCHAR(500) DEFAULT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    user_agent VARCHAR(255) DEFAULT NULL,
    search_term VARCHAR(255) DEFAULT NULL,
    is_new_user TINYINT(1) NOT NULL DEFAULT 1,
    session_id VARCHAR(100) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_store_created (store_id, created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Delivery Tables
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS db_delivery_schedules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL DEFAULT 1,
    schedule_code VARCHAR(50) NOT NULL,
    route_name VARCHAR(255) NULL,
    schedule_date DATE NOT NULL,
    driver_id INT NULL,
    driver_name VARCHAR(255) NULL,
    vehicle VARCHAR(100) NULL,
    notes TEXT NULL,
    status ENUM('planned','ready','out_for_delivery','completed','cancelled') NOT NULL DEFAULT 'planned',
    created_date DATE NULL,
    created_time VARCHAR(20) NULL,
    created_by VARCHAR(50) NULL,
    system_ip VARCHAR(50) NULL,
    system_name VARCHAR(100) NULL,
    INDEX idx_store_id (store_id),
    INDEX idx_status (status),
    INDEX idx_schedule_date (schedule_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_delivery_schedule_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    schedule_id INT NOT NULL,
    sales_id INT NOT NULL,
    sales_code VARCHAR(50) NULL,
    customer_id INT NULL,
    customer_name VARCHAR(255) NULL,
    address TEXT NULL,
    phone VARCHAR(50) NULL,
    delivery_sequence INT NOT NULL DEFAULT 0,
    delivery_status ENUM('pending','out_for_delivery','delivered','failed','cancelled') NOT NULL DEFAULT 'pending',
    delivered_at DATETIME NULL,
    delivery_notes TEXT NULL,
    signature TEXT NULL,
    photo_proof VARCHAR(255) NULL,
    INDEX idx_schedule_id (schedule_id),
    INDEX idx_sales_id (sales_id),
    INDEX idx_delivery_status (delivery_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_delivery_drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL DEFAULT 1,
    name VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NULL,
    email VARCHAR(100) NULL,
    address TEXT NULL,
    emergency_contact_name VARCHAR(255) NULL,
    emergency_contact_phone VARCHAR(50) NULL,
    nin VARCHAR(50) NULL COMMENT 'National Identification Number',
    driver_license VARCHAR(100) NULL COMMENT 'FRSC Driver License Number',
    license_expiry DATE NULL,
    vehicle VARCHAR(100) NULL,
    vehicle_type ENUM('motorcycle','car','van','truck','bicycle','keke') NULL DEFAULT 'motorcycle',
    vehicle_color VARCHAR(50) NULL,
    license_plate VARCHAR(50) NULL,
    employment_type ENUM('full_time','contract','part_time','intern') NULL DEFAULT 'contract',
    hire_date DATE NULL,
    photo VARCHAR(255) NULL,
    notes TEXT NULL,
    status ENUM('active','inactive','on_leave','suspended') NOT NULL DEFAULT 'active',
    created_date DATE NULL,
    created_by VARCHAR(50) NULL,
    INDEX idx_store_id (store_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Membership Tables
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS db_membership_plans (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL DEFAULT 1,
    plan_name VARCHAR(255) NOT NULL,
    plan_code VARCHAR(100) NOT NULL,
    description TEXT NULL,
    price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    billing_cycle ENUM('monthly','quarterly','annual') NOT NULL DEFAULT 'monthly',
    discount_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    free_services_per_period INT NOT NULL DEFAULT 0,
    priority_booking TINYINT(1) NOT NULL DEFAULT 0,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_store_id (store_id),
    INDEX idx_status (status),
    INDEX idx_billing_cycle (billing_cycle)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_customer_memberships (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL DEFAULT 1,
    customer_id INT NOT NULL,
    plan_id INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    next_billing_date DATE NULL,
    auto_renew TINYINT(1) NOT NULL DEFAULT 0,
    status ENUM('active','expired','cancelled','pending') NOT NULL DEFAULT 'active',
    payment_status ENUM('paid','overdue','pending') NOT NULL DEFAULT 'paid',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_store_id (store_id),
    INDEX idx_customer_id (customer_id),
    INDEX idx_plan_id (plan_id),
    INDEX idx_status (status),
    INDEX idx_end_date (end_date),
    INDEX idx_next_billing (next_billing_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_membership_payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    membership_id INT NOT NULL,
    customer_id INT NOT NULL,
    plan_id INT NOT NULL,
    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    payment_date DATE NOT NULL,
    payment_method VARCHAR(50) NULL,
    payment_period_start DATE NOT NULL,
    payment_period_end DATE NOT NULL,
    status ENUM('success','failed','pending') NOT NULL DEFAULT 'success',
    notes TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_membership_id (membership_id),
    INDEX idx_customer_id (customer_id),
    INDEX idx_payment_date (payment_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Custom Orders Tables
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS db_custom_orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL DEFAULT 1,
    order_code VARCHAR(50) NOT NULL,
    customer_id INT NOT NULL,
    item_id INT NOT NULL,
    item_name VARCHAR(255) NULL,
    specifications_json JSON NULL,
    quoted_price DECIMAL(12,2) DEFAULT 0,
    deposit_amount DECIMAL(12,2) DEFAULT 0,
    deposit_paid DECIMAL(12,2) DEFAULT 0,
    total_amount DECIMAL(12,2) DEFAULT 0,
    balance_due DECIMAL(12,2) DEFAULT 0,
    status VARCHAR(50) NOT NULL DEFAULT 'new',
    workflow_template_key VARCHAR(50) DEFAULT 'standard',
    notes TEXT NULL,
    staff_id INT NULL DEFAULT 0,
    staff_name VARCHAR(255) NULL,
    order_date DATE NOT NULL,
    due_date DATE NULL,
    delivery_date DATE NULL,
    sales_id INT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_store_id (store_id),
    INDEX idx_customer_id (customer_id),
    INDEX idx_item_id (item_id),
    INDEX idx_status (status),
    INDEX idx_order_code (order_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_custom_order_history (
    id INT AUTO_INCREMENT PRIMARY KEY,
    custom_order_id INT NOT NULL,
    old_status VARCHAR(50) NULL,
    new_status VARCHAR(50) NOT NULL,
    note TEXT NULL,
    changed_by INT NULL,
    changed_by_name VARCHAR(255) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_custom_order_id (custom_order_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Treatment Notes Tables
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS db_treatment_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    store_id INT NOT NULL DEFAULT 1,
    customer_id INT NOT NULL,
    service_type VARCHAR(255) NOT NULL,
    notes TEXT NULL,
    treatment_date DATE NOT NULL,
    staff_id INT NULL DEFAULT 0,
    staff_name VARCHAR(255) NULL,
    products_used TEXT NULL,
    recommendations TEXT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_store_id (store_id),
    INDEX idx_customer_id (customer_id),
    INDEX idx_treatment_date (treatment_date),
    INDEX idx_service_type (service_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_treatment_note_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    treatment_note_id INT NOT NULL,
    item_id INT NOT NULL,
    qty DECIMAL(12,3) NOT NULL DEFAULT 0,
    item_name VARCHAR(255) NULL,
    consumable_unit VARCHAR(50) NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_treatment_note_id (treatment_note_id),
    INDEX idx_item_id (item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Email & Report Tables
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS db_email_logs (
    id INT(11) unsigned NOT NULL AUTO_INCREMENT,
    store_id INT(11) unsigned NOT NULL DEFAULT 1,
    email_type VARCHAR(64) NOT NULL DEFAULT '',
    provider_used VARCHAR(32) NOT NULL DEFAULT '',
    recipient VARCHAR(512) NOT NULL DEFAULT '',
    subject VARCHAR(255) NOT NULL DEFAULT '',
    status ENUM('sent','failed','pending','retrying') NOT NULL DEFAULT 'pending',
    error_message TEXT,
    triggered_by VARCHAR(64) DEFAULT NULL,
    related_module VARCHAR(64) DEFAULT NULL,
    related_record_id VARCHAR(64) DEFAULT NULL,
    provider_response TEXT,
    created_at DATETIME DEFAULT NULL,
    sent_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_status (status),
    KEY idx_type (email_type),
    KEY idx_store (store_id),
    KEY idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_email_templates (
    id INT(11) unsigned NOT NULL AUTO_INCREMENT,
    store_id INT(11) unsigned NOT NULL DEFAULT 1,
    template_key VARCHAR(64) NOT NULL,
    template_name VARCHAR(128) NOT NULL,
    subject VARCHAR(255) NOT NULL DEFAULT '',
    html_body TEXT,
    text_body TEXT,
    status TINYINT(1) NOT NULL DEFAULT 1,
    send_copy_to_owner TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT NULL,
    updated_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_template_key_store (template_key, store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_report_schedules (
    id INT(11) unsigned NOT NULL AUTO_INCREMENT,
    store_id INT(11) unsigned NOT NULL DEFAULT 1,
    report_type VARCHAR(64) NOT NULL COMMENT 'daily_summary, low_stock, overdue_debt',
    template_name VARCHAR(128) DEFAULT NULL,
    frequency VARCHAR(16) NOT NULL DEFAULT 'daily' COMMENT 'daily, weekly',
    send_time VARCHAR(8) NOT NULL DEFAULT '18:00' COMMENT 'HH:MM 24h format',
    email_enabled TINYINT(1) NOT NULL DEFAULT 1,
    email_recipients VARCHAR(500) DEFAULT NULL COMMENT 'comma-separated emails',
    email_template_key VARCHAR(64) DEFAULT 'daily_business_summary',
    whatsapp_enabled TINYINT(1) NOT NULL DEFAULT 0,
    whatsapp_numbers VARCHAR(500) DEFAULT NULL COMMENT 'comma-separated with country code',
    whatsapp_message_template TEXT DEFAULT NULL,
    last_run_at DATETIME DEFAULT NULL,
    status TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME DEFAULT NULL,
    updated_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_report_type_store (report_type, store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Debt Reminder Tables
-- --------------------------------------------------------

CREATE TABLE IF NOT EXISTS db_debt_reminder_settings (
    id INT(11) unsigned NOT NULL AUTO_INCREMENT,
    store_id INT(11) unsigned NOT NULL DEFAULT 1,
    customer_id INT(11) unsigned NOT NULL,
    enabled TINYINT(1) NOT NULL DEFAULT 1,
    frequency VARCHAR(16) NOT NULL DEFAULT 'weekly' COMMENT 'daily,3days,weekly,biweekly,monthly',
    max_reminders INT(11) NOT NULL DEFAULT 0 COMMENT '0 = unlimited',
    reminder_count INT(11) NOT NULL DEFAULT 0,
    last_reminder_sent DATETIME DEFAULT NULL,
    send_email TINYINT(1) NOT NULL DEFAULT 1,
    send_sms TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT NULL,
    updated_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uk_customer_store (customer_id, store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_debt_reminder_history (
    id INT(11) unsigned NOT NULL AUTO_INCREMENT,
    store_id INT(11) unsigned NOT NULL DEFAULT 1,
    customer_id INT(11) unsigned NOT NULL,
    customer_name VARCHAR(255) DEFAULT NULL,
    amount_due DECIMAL(18,2) NOT NULL DEFAULT 0.00,
    channel VARCHAR(16) NOT NULL DEFAULT 'email' COMMENT 'email,sms,whatsapp',
    status VARCHAR(16) NOT NULL DEFAULT 'sent' COMMENT 'sent,failed',
    error_message TEXT DEFAULT NULL,
    sent_at DATETIME DEFAULT NULL,
    PRIMARY KEY (id),
    KEY idx_customer (customer_id),
    KEY idx_sent_at (sent_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- ALTER TABLE statements from models (adding columns to existing tables)
-- --------------------------------------------------------
-- NOTE: These columns are now folded into the db.txt CREATE TABLE definitions.
-- ALTER TABLE db_items ADD COLUMN publish_online TINYINT(1) NOT NULL DEFAULT 1 AFTER status;
-- ALTER TABLE db_items ADD COLUMN online_price DECIMAL(12,2) NULL AFTER sales_price;
-- ALTER TABLE db_store ADD COLUMN location_lat DECIMAL(10,8) DEFAULT NULL AFTER address;
-- ALTER TABLE db_store ADD COLUMN location_lng DECIMAL(11,8) DEFAULT NULL AFTER location_lat;

-- NOTE: db_store has hit MySQL's InnoDB max row-size limit (8126 bytes,
-- not counting BLOBs) after years of ADD COLUMN accumulation. Email/Resend
-- provider settings therefore live in their own dedicated table below
-- instead of being bolted onto db_store.
CREATE TABLE IF NOT EXISTS db_email_settings (
	store_id INT NOT NULL PRIMARY KEY,
	email_provider VARCHAR(50) DEFAULT 'smtp',
	email_from_name VARCHAR(255) NULL DEFAULT NULL,
	email_from_email VARCHAR(255) NULL DEFAULT NULL,
	email_reply_to VARCHAR(255) NULL DEFAULT NULL,
	smtp_crypto VARCHAR(50) NULL DEFAULT NULL,
	resend_api_key VARCHAR(255) NULL DEFAULT NULL,
	resend_from_email VARCHAR(255) NULL DEFAULT NULL,
	resend_from_name VARCHAR(255) NULL DEFAULT NULL,
	updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Self-healing model tables baked into installer (audit: Jul 2026)
-- These tables were previously created lazily by model constructors
-- (Recipe_model, Production_batches_model, Service_package_model,
-- Attendance_model) or by the manual "System Update" migration chain
-- (Subscription_license_model, Updates controller). Relying on lazy
-- creation caused crashes when a view/controller queried the table
-- directly before the owning model was ever loaded (e.g. db_recipes
-- queried from report views without loading Recipe_model first).
-- Creating them upfront guarantees a fully working install on Day 0.
-- --------------------------------------------------------

-- Attendance / Shifts (Attendance_model)
CREATE TABLE IF NOT EXISTS db_shifts (
	id INT AUTO_INCREMENT PRIMARY KEY,
	store_id INT NOT NULL DEFAULT 0,
	shift_name VARCHAR(100) NOT NULL DEFAULT '',
	start_time TIME NOT NULL,
	end_time TIME NOT NULL,
	grace_minutes INT NOT NULL DEFAULT 0,
	location_lat DECIMAL(10,8) DEFAULT NULL,
	location_lng DECIMAL(11,8) DEFAULT NULL,
	location_radius_meters INT NOT NULL DEFAULT 100,
	status TINYINT(1) NOT NULL DEFAULT 1,
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_user_shifts (
	id INT AUTO_INCREMENT PRIMARY KEY,
	user_id INT NOT NULL,
	shift_id INT NOT NULL,
	store_id INT NOT NULL DEFAULT 0,
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_attendance (
	id INT AUTO_INCREMENT PRIMARY KEY,
	store_id INT NOT NULL DEFAULT 0,
	user_id INT NOT NULL,
	shift_id INT DEFAULT NULL,
	attendance_date DATE NOT NULL,
	clock_in TIME DEFAULT NULL,
	clock_out TIME DEFAULT NULL,
	clock_in_lat DECIMAL(10,8) DEFAULT NULL,
	clock_in_lng DECIMAL(11,8) DEFAULT NULL,
	clock_out_lat DECIMAL(10,8) DEFAULT NULL,
	clock_out_lng DECIMAL(11,8) DEFAULT NULL,
	face_image VARCHAR(255) DEFAULT NULL,
	face_image_out VARCHAR(255) DEFAULT NULL,
	status VARCHAR(20) NOT NULL DEFAULT 'present',
	notes TEXT,
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	INDEX idx_date (attendance_date),
	INDEX idx_user (user_id),
	INDEX idx_store (store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Recipe / Production costing (Recipe_model)
CREATE TABLE IF NOT EXISTS db_recipe_categories (
	id INT AUTO_INCREMENT PRIMARY KEY,
	store_id INT NOT NULL DEFAULT 1,
	name VARCHAR(100) NOT NULL,
	status TINYINT NOT NULL DEFAULT 1,
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	INDEX idx_store_id (store_id),
	INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_recipes (
	id INT AUTO_INCREMENT PRIMARY KEY,
	store_id INT NOT NULL DEFAULT 1,
	recipe_code VARCHAR(50) NOT NULL,
	name VARCHAR(255) NOT NULL,
	category VARCHAR(100) NULL,
	description TEXT NULL,
	product_item_id INT NULL COMMENT 'db_items id of final product',
	margin_pct DECIMAL(5,2) NOT NULL DEFAULT 30 COMMENT 'Sales margin % applied to production cost',
	yield_qty DECIMAL(10,3) NOT NULL DEFAULT 1,
	yield_unit VARCHAR(50) NOT NULL DEFAULT 'piece',
	prep_time INT NULL COMMENT 'minutes',
	cook_time INT NULL COMMENT 'minutes',
	notes TEXT NULL,
	status TINYINT NOT NULL DEFAULT 1,
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	INDEX idx_store_id (store_id),
	INDEX idx_category (category),
	INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_recipe_ingredients (
	id INT AUTO_INCREMENT PRIMARY KEY,
	recipe_id INT NOT NULL,
	item_id INT NULL COMMENT 'db_items id if linked',
	item_name VARCHAR(255) NOT NULL,
	qty DECIMAL(15,3) NOT NULL DEFAULT 0,
	unit VARCHAR(50) NOT NULL DEFAULT 'gram',
	cost_per_unit DECIMAL(15,2) NOT NULL DEFAULT 0,
	wastage_pct DECIMAL(5,2) NOT NULL DEFAULT 0,
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	INDEX idx_recipe_id (recipe_id),
	INDEX idx_item_id (item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_recipe_production_runs (
	id INT AUTO_INCREMENT PRIMARY KEY,
	store_id INT NOT NULL DEFAULT 1,
	recipe_id INT NOT NULL,
	batch_id INT NULL COMMENT 'db_production_batches id if linked',
	planned_qty DECIMAL(10,3) NOT NULL DEFAULT 0,
	actual_yield DECIMAL(10,3) NULL,
	actual_cost DECIMAL(15,2) NULL,
	staff_id INT NULL,
	notes TEXT NULL,
	run_date DATE NOT NULL,
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	INDEX idx_store_id (store_id),
	INDEX idx_recipe_id (recipe_id),
	INDEX idx_batch_id (batch_id),
	INDEX idx_run_date (run_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Production batches / bakery scheduling (Production_batches_model)
CREATE TABLE IF NOT EXISTS db_production_batches (
	id INT AUTO_INCREMENT PRIMARY KEY,
	store_id INT NOT NULL DEFAULT 1,
	batch_code VARCHAR(50) NOT NULL,
	batch_name VARCHAR(255) NOT NULL,
	batch_type VARCHAR(50) DEFAULT 'general',
	scheduled_date DATE NOT NULL,
	scheduled_time VARCHAR(20) NULL,
	equipment VARCHAR(255) NULL,
	staff_id INT NULL DEFAULT 0,
	staff_name VARCHAR(255) NULL,
	status VARCHAR(50) NOT NULL DEFAULT 'planned',
	notes TEXT NULL,
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
	INDEX idx_store_id (store_id),
	INDEX idx_scheduled_date (scheduled_date),
	INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_production_batch_items (
	id INT AUTO_INCREMENT PRIMARY KEY,
	batch_id INT NOT NULL,
	item_type VARCHAR(50) NOT NULL DEFAULT 'custom_order',
	item_id INT NOT NULL,
	item_name VARCHAR(255) NULL,
	quantity INT NOT NULL DEFAULT 1,
	notes TEXT NULL,
	status VARCHAR(50) DEFAULT 'pending',
	created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	INDEX idx_batch_id (batch_id),
	INDEX idx_item_type (item_type, item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Service packages / bundles (Service_package_model)
CREATE TABLE IF NOT EXISTS db_service_packages (
	id INT AUTO_INCREMENT PRIMARY KEY,
	store_id INT NOT NULL DEFAULT 1,
	package_code VARCHAR(50) NOT NULL,
	package_name VARCHAR(255) NOT NULL,
	description TEXT NULL,
	package_image VARCHAR(255) NULL,
	pricing_model ENUM('fixed','calculated') NOT NULL DEFAULT 'fixed',
	package_price DECIMAL(15,2) NOT NULL DEFAULT 0,
	discount_type VARCHAR(20) NULL,
	discount DECIMAL(15,2) NULL,
	redemption_type ENUM('single','multi') NOT NULL DEFAULT 'single',
	expiry_type ENUM('none','days','date') NOT NULL DEFAULT 'none',
	expiry_days INT NULL,
	expiry_date DATE NULL,
	status TINYINT NOT NULL DEFAULT 1,
	created_date DATE NULL,
	created_time VARCHAR(20) NULL,
	created_by VARCHAR(50) NULL,
	system_ip VARCHAR(50) NULL,
	system_name VARCHAR(100) NULL,
	INDEX idx_store_id (store_id),
	INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_service_package_items (
	id INT AUTO_INCREMENT PRIMARY KEY,
	package_id INT NOT NULL,
	item_type ENUM('service','product') NOT NULL DEFAULT 'service',
	item_id INT NOT NULL,
	quantity DECIMAL(10,2) NOT NULL DEFAULT 1,
	sort_order INT NOT NULL DEFAULT 0,
	INDEX idx_package_id (package_id),
	INDEX idx_item_id (item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_customer_packages (
	id INT AUTO_INCREMENT PRIMARY KEY,
	store_id INT NOT NULL DEFAULT 1,
	customer_id INT NOT NULL,
	package_id INT NOT NULL,
	sale_id INT NULL,
	sale_items_id INT NULL,
	package_code VARCHAR(50) NOT NULL,
	total_uses DECIMAL(10,2) NOT NULL DEFAULT 0,
	remaining_uses DECIMAL(10,2) NOT NULL DEFAULT 0,
	expiry_date DATE NULL,
	status ENUM('active','fully_redeemed','expired','cancelled') NOT NULL DEFAULT 'active',
	created_date DATE NULL,
	created_time VARCHAR(20) NULL,
	created_by VARCHAR(50) NULL,
	INDEX idx_customer_id (customer_id),
	INDEX idx_package_id (package_id),
	INDEX idx_store_id (store_id),
	INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_customer_package_redemptions (
	id INT AUTO_INCREMENT PRIMARY KEY,
	customer_package_id INT NOT NULL,
	item_id INT NOT NULL,
	quantity_redeemed DECIMAL(10,2) NOT NULL DEFAULT 1,
	service_order_id INT NULL,
	sale_id INT NULL,
	notes TEXT NULL,
	redeemed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
	redeemed_by VARCHAR(50) NULL,
	INDEX idx_customer_package_id (customer_package_id),
	INDEX idx_item_id (item_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Subscription license OTP + history (Subscription_license_model)
CREATE TABLE IF NOT EXISTS db_license_otps (
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	store_id INT NOT NULL DEFAULT 0,
	otp_code VARCHAR(10) NOT NULL,
	otp_type VARCHAR(20) NOT NULL DEFAULT 'generate',
	expires_at DATETIME NOT NULL,
	used TINYINT(1) NOT NULL DEFAULT 0,
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS db_license_history (
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	store_id INT NOT NULL DEFAULT 0,
	license_code VARCHAR(500) DEFAULT NULL,
	plan_name VARCHAR(100) DEFAULT NULL,
	domain VARCHAR(255) DEFAULT NULL,
	activated_at DATETIME DEFAULT NULL,
	deactivated_at DATETIME DEFAULT NULL,
	status VARCHAR(20) DEFAULT 'active',
	created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- WhatsApp (fivemojo) and Brevo email integration tables
CREATE TABLE IF NOT EXISTS db_fivemojo (
	id INT(5) NOT NULL AUTO_INCREMENT,
	store_id INT(5) DEFAULT NULL,
	url TEXT CHARACTER SET utf8mb4,
	token TEXT CHARACTER SET utf8mb4,
	instance_id TEXT CHARACTER SET utf8mb4,
	status INT(1) DEFAULT '0',
	PRIMARY KEY (id),
	KEY store_id (store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_brevo (
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	store_id INT NOT NULL DEFAULT 0,
	api_key VARCHAR(255) DEFAULT NULL,
	sender_name VARCHAR(50) DEFAULT NULL,
	status INT(1) DEFAULT '0',
	KEY store_id (store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Expiry settings table (used by Expiry_settings_model)
CREATE TABLE IF NOT EXISTS db_expiry_settings (
	id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
	store_id INT NOT NULL DEFAULT 1,
	alert_before_days INT NOT NULL DEFAULT 30,
	stop_selling_expired TINYINT(1) NOT NULL DEFAULT 1,
	email_alerts_enabled TINYINT(1) NOT NULL DEFAULT 0,
	alert_email VARCHAR(255) DEFAULT NULL,
	UNIQUE KEY uk_store_id (store_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- Modular db_store configuration tables (4.0.3)
-- ============================================================

CREATE TABLE IF NOT EXISTS db_store_settings (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` int(10) UNSIGNED NOT NULL,
  `setting_group` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'general',
  `setting_key` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `setting_value` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `value_type` enum('string','int','float','bool','json') COLLATE utf8mb4_unicode_ci DEFAULT 'string',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store_group_key` (`store_id`,`setting_group`,`setting_key`),
  KEY `idx_store_group` (`store_id`,`setting_group`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_store_receipt_settings (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` int(10) UNSIGNED NOT NULL,
  `invoice_view` int(5) DEFAULT 1,
  `sales_invoice_format_id` int(5) DEFAULT 3,
  `pos_invoice_format_id` int(5) DEFAULT 1,
  `sales_invoice_footer_text` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invoice_terms` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `previous_balance_bit` int(1) DEFAULT 1,
  `round_off` int(1) DEFAULT 1,
  `change_return` int(2) DEFAULT 1,
  `decimals` int(1) DEFAULT 2,
  `qty_decimals` int(1) DEFAULT 2,
  `number_to_words` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Default',
  `t_and_c_status` int(1) DEFAULT 1,
  `t_and_c_status_pos` int(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store` (`store_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_store_pos_settings (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` int(10) UNSIGNED NOT NULL,
  `sales_discount` double(20,4) DEFAULT 0.0000,
  `mrp_column` int(1) DEFAULT 0,
  `show_signature` int(1) DEFAULT 0,
  `previous_balance_bit` int(1) DEFAULT 1,
  `default_account_id` int(11) DEFAULT NULL,
  `cash_account_id` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store` (`store_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_store_inventory_settings (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` int(10) UNSIGNED NOT NULL,
  `category_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `item_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `supplier_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `purchase_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `purchase_return_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sales_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sales_return_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expense_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accounts_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `journal_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cust_advance_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quotation_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `money_transfer_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sales_payment_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sales_return_payment_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `purchase_payment_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `purchase_return_payment_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expense_payment_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `purchase_code` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store` (`store_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_store_storefront_settings (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` int(10) UNSIGNED NOT NULL,
  `store_website` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `storefront_theme_key` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'general_retail',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store` (`store_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_store_notification_settings (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` int(10) UNSIGNED NOT NULL,
  `sms_status` int(1) DEFAULT 0,
  `sms_url` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_host` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_port` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_user` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_pass` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_status` int(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store` (`store_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_store_theme_settings (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` int(10) UNSIGNED NOT NULL,
  `store_logo` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fav_icon` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store` (`store_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_store_industry_settings (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` int(10) UNSIGNED NOT NULL,
  `industry_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'general_retail',
  `business_model` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'product_based',
  `workflow_template_key` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'retail_standard',
  `dashboard_template_key` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'general_retail',
  `storefront_theme_key` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'general_retail',
  `feature_flags_json` json DEFAULT NULL,
  `label_overrides_json` json DEFAULT NULL,
  `industry_settings_json` json DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store` (`store_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_store_business_profile (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` int(10) UNSIGNED NOT NULL,
  `industry_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'general_retail',
  `business_model` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'product_based',
  `workflow_template_key` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'retail_standard',
  `dashboard_template_key` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'general_retail',
  `storefront_theme_key` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'general_retail',
  `feature_flags_json` json DEFAULT NULL,
  `label_overrides_json` json DEFAULT NULL,
  `industry_settings_json` json DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store_id` (`store_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_store_tax_settings (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` int(10) UNSIGNED NOT NULL,
  `gst_no` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vat_no` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pan_no` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `regno_key` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `currencysymbol_id` int(5) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store` (`store_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS db_store_payment_settings (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` int(10) UNSIGNED NOT NULL,
  `upi_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `upi_code` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_details` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_store` (`store_id`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed defaults for the two stores created in db.txt
INSERT INTO db_store_receipt_settings (store_id, invoice_view, sales_invoice_format_id, pos_invoice_format_id, sales_invoice_footer_text, invoice_terms, previous_balance_bit, round_off, change_return, decimals, qty_decimals, number_to_words, t_and_c_status, t_and_c_status_pos)
VALUES
(1, 1, 3, 1, 'This is footer text. It is in Store Management.', '', 1, 0, 1, 2, 2, 'Default', 1, 1),
(2, 1, 3, 1, 'This is footer text. It is in Store Management.', '', 1, 1, 1, 2, 2, 'Nigerian', 1, 1)
ON DUPLICATE KEY UPDATE store_id = store_id;

INSERT INTO db_store_pos_settings (store_id, sales_discount, mrp_column, show_signature, previous_balance_bit)
VALUES
(1, 0.0000, 0, 0, 1),
(2, 0.0000, 0, 0, 1)
ON DUPLICATE KEY UPDATE store_id = store_id;

INSERT INTO db_store_inventory_settings (store_id, category_init, item_init, supplier_init, purchase_init, purchase_return_init, customer_init, sales_init, sales_return_init, expense_init, accounts_init, journal_init, cust_advance_init, quotation_init, money_transfer_init, sales_payment_init, sales_return_payment_init, purchase_payment_init, purchase_return_payment_init, expense_payment_init)
VALUES
(1, 'CT/01/', 'IT01', 'SU/01/', 'PU/2020/01', 'PR/2020/01/', 'CU/01/', 'SL/2020/01/', 'SR/2020/01/', 'EX/2020/01/', 'AC/01/', 'JE', 'ADV', 'QT/2020/01/', 'MT/01/', 'SP/2020/01/', 'SRP/2020/01/', 'PP/2020/01/', 'PRP/2020/01/', 'XP/2020/01/'),
(2, 'CT', 'IT02', 'SU', 'PU', 'PR', 'CU', 'SL', 'SR', 'EX', 'AC', NULL, 'ADV', 'QT', 'MT', 'SP', 'SRP', 'PP', 'PRP', 'XP')
ON DUPLICATE KEY UPDATE store_id = store_id;

INSERT INTO db_store_storefront_settings (store_id, store_website, website, storefront_theme_key)
VALUES
(1, '', 'www', 'general_retail'),
(2, '', NULL, 'general_retail')
ON DUPLICATE KEY UPDATE store_id = store_id;

INSERT INTO db_store_theme_settings (store_id, store_logo, logo, fav_icon)
VALUES
(1, 'uploads/store/company_logo.png', NULL, NULL),
(2, 'uploads/store/logo1.png', NULL, NULL)
ON DUPLICATE KEY UPDATE store_id = store_id;

INSERT INTO db_store_industry_settings (store_id, industry_type, business_model, workflow_template_key, dashboard_template_key, storefront_theme_key)
VALUES
(1, 'general_retail', 'product_based', 'retail_standard', 'general_retail', 'general_retail'),
(2, 'general_retail', 'product_based', 'retail_standard', 'general_retail', 'general_retail')
ON DUPLICATE KEY UPDATE store_id = store_id;

INSERT INTO db_store_business_profile (store_id, industry_type, business_model, workflow_template_key, dashboard_template_key, storefront_theme_key)
VALUES
(1, 'general_retail', 'product_based', 'retail_standard', 'general_retail', 'general_retail'),
(2, 'general_retail', 'product_based', 'retail_standard', 'general_retail', 'general_retail')
ON DUPLICATE KEY UPDATE store_id = store_id;

INSERT INTO db_store_tax_settings (store_id, currencysymbol_id)
VALUES
(1, NULL),
(2, 21)
ON DUPLICATE KEY UPDATE store_id = store_id;

INSERT INTO db_store_notification_settings (store_id, sms_status, sms_url, smtp_host, smtp_port, smtp_user, smtp_pass, smtp_status)
VALUES
(1, 0, 'http://sms.proware.in/api/sendhttp.php?authkey=248050Asbku6K75bf27efc&amp;mobiles={{MOBILE}}&amp;message={{MESSAGE}}&amp;sender=WBMGIC&amp;route=4', 'ssl://smtp.gmail.com', '465', 'salmanpathanindia@gmail.com', '9632563672', 1),
(2, 2, NULL, NULL, NULL, NULL, NULL, 0)
ON DUPLICATE KEY UPDATE store_id = store_id;

INSERT INTO db_store_settings (store_id, setting_group, setting_key, setting_value, value_type)
VALUES
(1, 'general', 'language_id', '1', 'int'),
(2, 'general', 'language_id', '1', 'int'),
(1, 'general', 'current_subscriptionlist_id', '26', 'int'),
(2, 'general', 'current_subscriptionlist_id', '28', 'int')
ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value);

-- ============================================================
-- MartPoint Retail — Cashier Shift Reconciliation (Z-Report)
-- ============================================================

CREATE TABLE IF NOT EXISTS `db_cashier_shifts` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `shift_code` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cashier_user_id` INT(11) NOT NULL,
  `cashier_username` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `till_label` VARCHAR(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `opening_float` DECIMAL(20,4) NOT NULL DEFAULT 0.0000,
  `opened_at` DATETIME NOT NULL,
  `closed_at` DATETIME DEFAULT NULL,
  `status` ENUM('open','closed','void') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'open',
  `total_expected_cash` DECIMAL(20,4) NOT NULL DEFAULT 0.0000,
  `total_counted_cash` DECIMAL(20,4) NOT NULL DEFAULT 0.0000,
  `cash_variance` DECIMAL(20,4) NOT NULL DEFAULT 0.0000,
  `total_expected_other` DECIMAL(20,4) NOT NULL DEFAULT 0.0000,
  `total_counted_other` DECIMAL(20,4) NOT NULL DEFAULT 0.0000,
  `other_variance` DECIMAL(20,4) NOT NULL DEFAULT 0.0000,
  `transactions` INT(11) NOT NULL DEFAULT 0,
  `manager_user_id` INT(11) DEFAULT NULL,
  `manager_username` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approval_status` ENUM('not_required','approved','rejected','pending') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'not_required',
  `close_note` TEXT COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_store` (`store_id`),
  KEY `idx_cashier` (`cashier_user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_opened` (`opened_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_cashier_shift_counts` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `shift_id` INT(11) NOT NULL,
  `store_id` INT(11) NOT NULL,
  `payment_type` VARCHAR(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `affects_cash_in_hand` TINYINT(1) NOT NULL DEFAULT 0,
  `expected_amount` DECIMAL(20,4) NOT NULL DEFAULT 0.0000,
  `counted_amount` DECIMAL(20,4) NOT NULL DEFAULT 0.0000,
  `variance` DECIMAL(20,4) NOT NULL DEFAULT 0.0000,
  `txn_count` INT(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_shift` (`shift_id`),
  KEY `idx_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
-- ============================================================
-- MartPoint Retail — Till Accounting (per-cashier cash-in-hand)
-- Adds db_tills and links db_cashier_shifts to a real account.
-- ============================================================

CREATE TABLE IF NOT EXISTS `db_tills` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `till_name` VARCHAR(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Human-friendly till label e.g. Till 1, Front Counter',
  `cashier_user_id` INT(11) DEFAULT NULL COMMENT 'Optional: assigned cashier. NULL = shared till.',
  `account_id` INT(11) NOT NULL COMMENT 'ac_accounts.id that tracks this till\'s cash balance',
  `is_default` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Default till for a cashier if no shift open',
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_store` (`store_id`),
  KEY `idx_cashier` (`cashier_user_id`),
  KEY `idx_account` (`account_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- ============================================================
-- MartPoint Retail — Till Accounting (per-cashier cash-in-hand)
-- Adds db_tills and links db_cashier_shifts to a real account.
-- ============================================================

CREATE TABLE IF NOT EXISTS `db_tills` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `store_id` INT(11) NOT NULL,
  `till_name` VARCHAR(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Human-friendly till label e.g. Till 1, Front Counter',
  `cashier_user_id` INT(11) DEFAULT NULL COMMENT 'Optional: assigned cashier. NULL = shared till.',
  `account_id` INT(11) NOT NULL COMMENT 'ac_accounts.id that tracks this till\'s cash balance',
  `is_default` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Default till for a cashier if no shift open',
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_store` (`store_id`),
  KEY `idx_cashier` (`cashier_user_id`),
  KEY `idx_account` (`account_id`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE `db_cashier_shifts`
  ADD COLUMN `till_id` INT(11) DEFAULT NULL COMMENT 'db_tills.id for this shift' AFTER `cashier_username`,
  ADD COLUMN `cash_account_id` INT(11) DEFAULT NULL COMMENT 'Denormalised ac_accounts.id for the till' AFTER `till_id`,
  ADD KEY `idx_till` (`till_id`);

ALTER TABLE `db_cashier_shift_counts`
  ADD COLUMN `account_id` INT(11) DEFAULT NULL COMMENT 'ac_accounts.id when this payment method affects a cash account' AFTER `affects_cash_in_hand`;


-- ============================================================
-- MartPoint Fashion Intelligence Pack (v4.0.7)
-- Multi-attribute variant matrix, centralised promotions with
-- margin protection, and reorder engine parameters.
-- Tables only — column additions are baked into db.txt CREATE TABLE.
-- ============================================================

-- 1. Variant Attributes junction table (size x colour x material)
CREATE TABLE IF NOT EXISTS `db_variant_attributes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `store_id` INT(11) DEFAULT NULL,
  `variant_id` INT(5) NOT NULL,
  `attribute_type` VARCHAR(30) NOT NULL COMMENT 'size, colour, material, pattern, fit',
  `attribute_value` VARCHAR(100) NOT NULL,
  `sort_order` INT(3) DEFAULT 0,
  `created_date` DATE DEFAULT NULL,
  UNIQUE KEY `uk_variant_attr` (`variant_id`,`attribute_type`),
  KEY `store_id` (`store_id`),
  KEY `attribute_type` (`attribute_type`),
  KEY `attribute_value` (`attribute_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Centralised Promotions (with min-price / margin protection)
CREATE TABLE IF NOT EXISTS `db_promotions` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `store_id` INT(11) NOT NULL,
  `promotion_code` VARCHAR(50) DEFAULT NULL COMMENT 'Human-readable code',
  `promotion_name` VARCHAR(150) NOT NULL,
  `description` TEXT DEFAULT NULL,
  `discount_type` VARCHAR(20) NOT NULL DEFAULT 'Percentage' COMMENT 'Percentage or Fixed',
  `discount_value` DECIMAL(20,2) NOT NULL DEFAULT 0.00,
  `min_price_rule` DECIMAL(20,4) NULL DEFAULT NULL COMMENT 'Never sell below this price (margin protection)',
  `min_margin_pct` DECIMAL(10,2) NULL DEFAULT NULL COMMENT 'Never drop below this % margin over cost',
  `applies_to` VARCHAR(20) NOT NULL DEFAULT 'all' COMMENT 'all, category, brand, items',
  `category_id` INT(10) NULL DEFAULT NULL,
  `brand_id` INT(5) NULL DEFAULT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `status` INT(1) NOT NULL DEFAULT 1 COMMENT '1=Active, 0=Inactive',
  `created_date` DATE DEFAULT NULL,
  `created_time` VARCHAR(50) DEFAULT NULL,
  `created_by` VARCHAR(50) DEFAULT NULL,
  KEY `store_id` (`store_id`),
  KEY `status` (`status`),
  KEY `applies_to` (`applies_to`),
  KEY `date_range` (`start_date`,`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_promotion_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `promotion_id` INT NOT NULL,
  `item_id` INT(5) NOT NULL,
  `store_id` INT(11) DEFAULT NULL,
  KEY `promotion_id` (`promotion_id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Promotions: advanced features (simple/advanced mode, usage limits, min spend)
-- Idempotent: uses information_schema checks so it works on MySQL 5.5/5.7 and MariaDB.
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_promotions' AND column_name = 'mode');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_promotions` ADD COLUMN `mode` VARCHAR(10) NOT NULL DEFAULT ''simple'' COMMENT ''simple or advanced''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_promotions' AND column_name = 'min_spend');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_promotions` ADD COLUMN `min_spend` DECIMAL(20,2) NULL DEFAULT NULL COMMENT ''Minimum cart total for code-based promo''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_promotions' AND column_name = 'usage_limit_per_customer');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_promotions` ADD COLUMN `usage_limit_per_customer` INT NULL DEFAULT NULL COMMENT ''Max uses per customer, NULL=unlimited''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_promotions' AND column_name = 'usage_limit_total');
SET @sql = IF(@col_exists = 0, 'ALTER TABLE `db_promotions` ADD COLUMN `usage_limit_total` INT NULL DEFAULT NULL COMMENT ''Max total uses, NULL=unlimited''', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

CREATE TABLE IF NOT EXISTS `db_promotion_usage` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `promotion_id` INT NOT NULL,
  `customer_id` INT NOT NULL,
  `sales_id` INT NOT NULL,
  `store_id` INT NOT NULL,
  `used_date` DATE DEFAULT NULL,
  `used_time` VARCHAR(50) DEFAULT NULL,
  KEY `promotion_id` (`promotion_id`),
  KEY `customer_id` (`customer_id`),
  KEY `sales_id` (`sales_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Record this version in the migrations table
INSERT IGNORE INTO `db_schema_migrations` (`version`, `filename`) VALUES ('4.0.7', 'db_install_extensions.sql');

-- 4. Seed permissions for new installs — grant fashion report perms
--    to every role that already has stock_report (owners + managers).
INSERT INTO db_permissions (store_id, role_id, permissions)
SELECT DISTINCT d.store_id, d.role_id, 'variant_attribute_report'
FROM db_permissions d
LEFT JOIN db_permissions p2
  ON p2.store_id = d.store_id AND p2.role_id = d.role_id AND p2.permissions = 'variant_attribute_report'
WHERE d.permissions = 'stock_report' AND p2.id IS NULL;

INSERT INTO db_permissions (store_id, role_id, permissions)
SELECT DISTINCT d.store_id, d.role_id, 'reorder_suggestion_report'
FROM db_permissions d
LEFT JOIN db_permissions p2
  ON p2.store_id = d.store_id AND p2.role_id = d.role_id AND p2.permissions = 'reorder_suggestion_report'
WHERE d.permissions = 'stock_report' AND p2.id IS NULL;

INSERT INTO db_permissions (store_id, role_id, permissions)
SELECT DISTINCT d.store_id, d.role_id, 'promotions_manage'
FROM db_permissions d
LEFT JOIN db_permissions p2
  ON p2.store_id = d.store_id AND p2.role_id = d.role_id AND p2.permissions = 'promotions_manage'
WHERE d.permissions = 'items_edit' AND p2.id IS NULL;

INSERT INTO db_permissions (store_id, role_id, permissions)
SELECT DISTINCT d.store_id, d.role_id, 'sell_through_report'
FROM db_permissions d
LEFT JOIN db_permissions p2
  ON p2.store_id = d.store_id AND p2.role_id = d.role_id AND p2.permissions = 'sell_through_report'
WHERE d.permissions = 'stock_report' AND p2.id IS NULL;

-- ============================================================
-- MartPoint Attribute-Driven Variants (v4.0.7-attr)
-- One table for attribute types/values; product flags attribute
-- types it uses via db_items.attribute_types_json
-- ============================================================
CREATE TABLE IF NOT EXISTS `db_attributes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `store_id` INT(11) NOT NULL,
  `attribute_type` VARCHAR(30) NOT NULL COMMENT 'size, colour, length, material, storage, shade',
  `attribute_value` VARCHAR(100) NOT NULL,
  `sort_order` INT(3) DEFAULT 0,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_date` DATE DEFAULT NULL,
  `created_time` VARCHAR(50) DEFAULT NULL,
  `created_by` VARCHAR(50) DEFAULT NULL,
  UNIQUE KEY `uk_store_attr` (`store_id`,`attribute_type`,`attribute_value`),
  KEY `store_id` (`store_id`),
  KEY `attribute_type` (`attribute_type`),
  KEY `attribute_value` (`attribute_value`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_schema_migrations` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `version` VARCHAR(20) NOT NULL,
  `applied_at` DATETIME NOT NULL,
  UNIQUE KEY `uk_version` (`version`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO `db_schema_migrations` (`version`, `applied_at`) VALUES ('4.0.7-attr', NOW());

-- Ensure db_store has the decimal-format columns expected by MY_Controller.
ALTER TABLE `db_store`
  ADD COLUMN `decimals` INT(1) DEFAULT 2,
  ADD COLUMN `qty_decimals` INT(1) DEFAULT 2;

-- ===============================================
-- Flatten modular store settings into db_store
-- CodeIgniter code still reads from db_store, so
-- the canonical row must contain all columns.
-- ===============================================
SET FOREIGN_KEY_CHECKS=0;

ALTER TABLE `db_store`
  ADD COLUMN `accounts_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `category_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `cust_advance_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `customer_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `expense_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `expense_payment_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `item_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `journal_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `money_transfer_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `purchase_code` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `purchase_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `purchase_payment_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `purchase_return_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `purchase_return_payment_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `quotation_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `sales_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `sales_payment_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `sales_return_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `sales_return_payment_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `supplier_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL;
ALTER TABLE `db_store`
  ADD COLUMN `bank_details` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `upi_code` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `upi_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL;
ALTER TABLE `db_store`
  ADD COLUMN `business_model` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'product_based',
  ADD COLUMN `dashboard_template_key` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'general_retail',
  ADD COLUMN `feature_flags_json` json DEFAULT NULL,
  ADD COLUMN `industry_settings_json` json DEFAULT NULL,
  ADD COLUMN `industry_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'general_retail',
  ADD COLUMN `label_overrides_json` json DEFAULT NULL,
  ADD COLUMN `workflow_template_key` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'retail_standard';
ALTER TABLE `db_store`
  ADD COLUMN `cash_account_id` int(11) DEFAULT NULL,
  ADD COLUMN `default_account_id` int(11) DEFAULT NULL,
  ADD COLUMN `mrp_column` int(1) DEFAULT 0,
  ADD COLUMN `sales_discount` double(20,4) DEFAULT 0.0000,
  ADD COLUMN `show_signature` int(1) DEFAULT 0;
ALTER TABLE `db_store`
  ADD COLUMN `change_return` int(2) DEFAULT 1,
  ADD COLUMN `invoice_terms` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `invoice_view` int(5) DEFAULT 1,
  ADD COLUMN `number_to_words` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'Default',
  ADD COLUMN `pos_invoice_format_id` int(5) DEFAULT 1,
  ADD COLUMN `previous_balance_bit` int(1) DEFAULT 1,
  ADD COLUMN `round_off` int(1) DEFAULT 1,
  ADD COLUMN `sales_invoice_footer_text` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `sales_invoice_format_id` int(5) DEFAULT 3,
  ADD COLUMN `t_and_c_status` int(1) DEFAULT 1,
  ADD COLUMN `t_and_c_status_pos` int(1) DEFAULT 1;
ALTER TABLE `db_store`
  ADD COLUMN `currencysymbol_id` int(5) DEFAULT NULL,
  ADD COLUMN `gst_no` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `pan_no` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `regno_key` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `vat_no` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL;
ALTER TABLE `db_store`
  ADD COLUMN `current_subscriptionlist_id` INT(11) DEFAULT 0,
  ADD COLUMN `email_provider` VARCHAR(50) COLLATE utf8mb4_unicode_ci DEFAULT 'smtp',
  ADD COLUMN `language_id` INT(11) DEFAULT 1,
  ADD COLUMN `store_code_count` INT(11) DEFAULT 0;
ALTER TABLE `db_store`
  ADD COLUMN `fav_icon` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `logo` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `store_logo` text COLLATE utf8mb4_unicode_ci DEFAULT NULL;
ALTER TABLE `db_store`
  ADD COLUMN `sms_status` int(1) DEFAULT 0,
  ADD COLUMN `sms_url` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `smtp_host` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `smtp_pass` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `smtp_port` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `smtp_status` int(1) DEFAULT 0,
  ADD COLUMN `smtp_user` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL;
ALTER TABLE `db_store`
  ADD COLUMN `store_website` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  ADD COLUMN `storefront_theme_key` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'general_retail',
  ADD COLUMN `website` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL;

-- Copy seeded values from modular tables into the canonical db_store row

UPDATE `db_store` s
  INNER JOIN `db_store_receipt_settings` x ON s.`id` = x.`store_id`
SET
  s.`decimals` = x.`decimals`,
  s.`qty_decimals` = x.`qty_decimals`;

UPDATE `db_store` s
  INNER JOIN `db_store_receipt_settings` x ON s.`id` = x.`store_id`
SET
  s.`invoice_view` = x.`invoice_view`,
  s.`sales_invoice_format_id` = x.`sales_invoice_format_id`,
  s.`pos_invoice_format_id` = x.`pos_invoice_format_id`,
  s.`sales_invoice_footer_text` = x.`sales_invoice_footer_text`,
  s.`invoice_terms` = x.`invoice_terms`,
  s.`previous_balance_bit` = x.`previous_balance_bit`,
  s.`round_off` = x.`round_off`,
  s.`change_return` = x.`change_return`,
  s.`number_to_words` = x.`number_to_words`,
  s.`t_and_c_status` = x.`t_and_c_status`,
  s.`t_and_c_status_pos` = x.`t_and_c_status_pos`;
UPDATE `db_store` s
  INNER JOIN `db_store_pos_settings` x ON s.`id` = x.`store_id`
SET
  s.`sales_discount` = x.`sales_discount`,
  s.`mrp_column` = x.`mrp_column`,
  s.`show_signature` = x.`show_signature`,
  s.`previous_balance_bit` = x.`previous_balance_bit`,
  s.`default_account_id` = x.`default_account_id`,
  s.`cash_account_id` = x.`cash_account_id`;
UPDATE `db_store` s
  INNER JOIN `db_store_inventory_settings` x ON s.`id` = x.`store_id`
SET
  s.`category_init` = x.`category_init`,
  s.`item_init` = x.`item_init`,
  s.`supplier_init` = x.`supplier_init`,
  s.`purchase_init` = x.`purchase_init`,
  s.`purchase_return_init` = x.`purchase_return_init`,
  s.`customer_init` = x.`customer_init`,
  s.`sales_init` = x.`sales_init`,
  s.`sales_return_init` = x.`sales_return_init`,
  s.`expense_init` = x.`expense_init`,
  s.`accounts_init` = x.`accounts_init`,
  s.`journal_init` = x.`journal_init`,
  s.`cust_advance_init` = x.`cust_advance_init`,
  s.`quotation_init` = x.`quotation_init`,
  s.`money_transfer_init` = x.`money_transfer_init`,
  s.`sales_payment_init` = x.`sales_payment_init`,
  s.`sales_return_payment_init` = x.`sales_return_payment_init`,
  s.`purchase_payment_init` = x.`purchase_payment_init`,
  s.`purchase_return_payment_init` = x.`purchase_return_payment_init`,
  s.`expense_payment_init` = x.`expense_payment_init`,
  s.`purchase_code` = x.`purchase_code`;
UPDATE `db_store` s
  INNER JOIN `db_store_storefront_settings` x ON s.`id` = x.`store_id`
SET
  s.`store_website` = x.`store_website`,
  s.`website` = x.`website`,
  s.`storefront_theme_key` = x.`storefront_theme_key`;
UPDATE `db_store` s
  INNER JOIN `db_store_notification_settings` x ON s.`id` = x.`store_id`
SET
  s.`sms_status` = x.`sms_status`,
  s.`sms_url` = x.`sms_url`,
  s.`smtp_host` = x.`smtp_host`,
  s.`smtp_port` = x.`smtp_port`,
  s.`smtp_user` = x.`smtp_user`,
  s.`smtp_pass` = x.`smtp_pass`,
  s.`smtp_status` = x.`smtp_status`;
UPDATE `db_store` s
  INNER JOIN `db_store_theme_settings` x ON s.`id` = x.`store_id`
SET
  s.`store_logo` = x.`store_logo`,
  s.`logo` = x.`logo`,
  s.`fav_icon` = x.`fav_icon`;
UPDATE `db_store` s
  INNER JOIN `db_store_industry_settings` x ON s.`id` = x.`store_id`
SET
  s.`industry_type` = x.`industry_type`,
  s.`business_model` = x.`business_model`,
  s.`workflow_template_key` = x.`workflow_template_key`,
  s.`dashboard_template_key` = x.`dashboard_template_key`,
  s.`storefront_theme_key` = x.`storefront_theme_key`,
  s.`feature_flags_json` = x.`feature_flags_json`,
  s.`label_overrides_json` = x.`label_overrides_json`,
  s.`industry_settings_json` = x.`industry_settings_json`;
UPDATE `db_store` s
  INNER JOIN `db_store_business_profile` x ON s.`id` = x.`store_id`
SET
  s.`industry_type` = x.`industry_type`,
  s.`business_model` = x.`business_model`,
  s.`workflow_template_key` = x.`workflow_template_key`,
  s.`dashboard_template_key` = x.`dashboard_template_key`,
  s.`storefront_theme_key` = x.`storefront_theme_key`,
  s.`feature_flags_json` = x.`feature_flags_json`,
  s.`label_overrides_json` = x.`label_overrides_json`,
  s.`industry_settings_json` = x.`industry_settings_json`;
UPDATE `db_store` s
  INNER JOIN `db_store_tax_settings` x ON s.`id` = x.`store_id`
SET
  s.`gst_no` = x.`gst_no`,
  s.`vat_no` = x.`vat_no`,
  s.`pan_no` = x.`pan_no`,
  s.`regno_key` = x.`regno_key`,
  s.`currencysymbol_id` = x.`currencysymbol_id`;
UPDATE `db_store` s
  INNER JOIN `db_store_payment_settings` x ON s.`id` = x.`store_id`
SET
  s.`upi_id` = x.`upi_id`,
  s.`upi_code` = x.`upi_code`,
  s.`bank_details` = x.`bank_details`;

UPDATE `db_store` SET `language_id` = 1 WHERE `language_id` IS NULL OR `language_id` = 0;
UPDATE `db_store` SET `current_subscriptionlist_id` = 0 WHERE `current_subscriptionlist_id` IS NULL;
UPDATE `db_store` SET `store_code_count` = 0 WHERE `store_code_count` IS NULL;
UPDATE `db_store` SET `email_provider` = 'smtp' WHERE `email_provider` IS NULL OR `email_provider` = '';

-- Multi-attribute variant table referenced by Items_model
CREATE TABLE IF NOT EXISTS `db_variant_attributes` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` int(11) DEFAULT NULL,
  `variant_id` int(10) NOT NULL,
  `attribute_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `attribute_value` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sort_order` int(5) DEFAULT 0,
  `created_date` date DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `variant_id` (`variant_id`),
  KEY `idx_attr_type_value` (`attribute_type`, `attribute_value`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Make id columns auto-increment so create/insert forms work without explicit ids.
-- PRIMARY KEY is already defined inline in each CREATE TABLE, so only MODIFY is needed here.
ALTER TABLE `db_users` MODIFY COLUMN `id` int(5) NOT NULL AUTO_INCREMENT;
ALTER TABLE `db_userswarehouses` MODIFY COLUMN `id` int(5) NOT NULL AUTO_INCREMENT;
ALTER TABLE `db_items` MODIFY COLUMN `id` int(5) NOT NULL AUTO_INCREMENT;
ALTER TABLE `db_variants` MODIFY COLUMN `id` int(5) NOT NULL AUTO_INCREMENT;
ALTER TABLE `db_sales` MODIFY COLUMN `id` int(5) NOT NULL AUTO_INCREMENT;
ALTER TABLE `db_salesitems` MODIFY COLUMN `id` int(5) NOT NULL AUTO_INCREMENT;
ALTER TABLE `db_salespayments` MODIFY COLUMN `id` int(5) NOT NULL AUTO_INCREMENT;
ALTER TABLE `db_salesreturn` MODIFY COLUMN `id` int(5) NOT NULL AUTO_INCREMENT;
ALTER TABLE `db_purchase` MODIFY COLUMN `id` int(5) NOT NULL AUTO_INCREMENT;
ALTER TABLE `db_purchaseitems` MODIFY COLUMN `id` int(5) NOT NULL AUTO_INCREMENT;
ALTER TABLE `db_purchasepayments` MODIFY COLUMN `id` int(5) NOT NULL AUTO_INCREMENT;
ALTER TABLE `db_purchasereturn` MODIFY COLUMN `id` int(5) NOT NULL AUTO_INCREMENT;

-- Add missing storefront settings columns for the online store
-- (All columns below are already defined in the CREATE TABLE for db_storefront_settings
--  above, so this ALTER TABLE is redundant and has been removed to avoid
--  "Duplicate column name" errors on clean installs.)

-- Add staff_id and commission_amount to db_salesitems for POS staff commission tracking
-- (Both columns are already defined in the CREATE TABLE for db_salesitems in db.txt,
--  so this ALTER TABLE is redundant and has been removed to avoid
--  "Duplicate column name" errors on clean installs.)

SET FOREIGN_KEY_CHECKS=1;

-- ============================================================
-- Medical Notes (Pharmacy workflow) — folded into install schema
-- so a fresh install has them without relying on runtime
-- _ensure_tables() from the migration file.
-- ============================================================
CREATE TABLE IF NOT EXISTS `db_medical_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `store_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `note_date` date NOT NULL,
  `prescribing_doctor` varchar(255) DEFAULT NULL,
  `doctor_contact` varchar(50) DEFAULT NULL,
  `diagnosis` varchar(500) DEFAULT NULL,
  `prescription_ref` varchar(100) DEFAULT NULL,
  `allergies_flagged` varchar(500) DEFAULT NULL,
  `dosage_instructions` text,
  `counselling_notes` text,
  `next_refill_date` date DEFAULT NULL,
  `refills_remaining` int(11) DEFAULT 0,
  `staff_id` int(11) DEFAULT NULL,
  `staff_name` varchar(120) DEFAULT NULL,
  `sales_id` int(11) DEFAULT NULL,
  `prescription_file` varchar(255) DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `created_date` date NOT NULL,
  `created_time` time NOT NULL,
  `created_by` varchar(100) DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_store_customer` (`store_id`, `customer_id`),
  KEY `idx_note_date` (`note_date`),
  KEY `idx_next_refill` (`next_refill_date`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `db_medical_note_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `medical_note_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `qty` decimal(15,3) DEFAULT 1.000,
  `dosage` varchar(255) DEFAULT NULL,
  `duration` varchar(100) DEFAULT NULL,
  `instructions` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_note_id` (`medical_note_id`),
  KEY `idx_item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Items list selects and displays a.mfg_date but the db_items CREATE TABLE
-- in db.txt does not include it. Add it here (guarded against re-runs).
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_items' AND column_name = 'mfg_date');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_items` ADD COLUMN `mfg_date` date DEFAULT NULL',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- sales_target column for db_sitesettings (daily sales target used by dashboard)
SET @col_exists = (SELECT COUNT(*) FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'db_sitesettings' AND column_name = 'sales_target');
SET @sql = IF(@col_exists = 0,
  'ALTER TABLE `db_sitesettings` ADD COLUMN `sales_target` DOUBLE(20,4) DEFAULT 0',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
