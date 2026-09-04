-- =====================================================
-- MARTPOINT RETAIL: CUSTOMER LOYALTY & REWARDS MODULE
-- =====================================================

-- Add loyalty columns to db_customers
ALTER TABLE db_customers
    ADD COLUMN loyalty_points DECIMAL(15,2) NOT NULL DEFAULT 0 AFTER nin_waived_at,
    ADD COLUMN lifetime_spend DECIMAL(15,2) NOT NULL DEFAULT 0,
    ADD COLUMN loyalty_tier VARCHAR(50) DEFAULT 'Bronze',
    ADD COLUMN store_credit_balance DECIMAL(15,2) NOT NULL DEFAULT 0,
    ADD COLUMN gift_card_balance DECIMAL(15,2) NOT NULL DEFAULT 0,
    ADD COLUMN referral_code VARCHAR(20) DEFAULT NULL,
    ADD COLUMN referred_by INT DEFAULT NULL,
    ADD COLUMN referral_count INT NOT NULL DEFAULT 0,
    ADD COLUMN birthday DATE DEFAULT NULL,
    ADD COLUMN last_purchase_date DATE DEFAULT NULL,
    ADD COLUMN average_order_value DECIMAL(15,2) DEFAULT 0,
    ADD COLUMN favourite_products TEXT DEFAULT NULL,
    ADD COLUMN photo VARCHAR(255) DEFAULT NULL;

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
