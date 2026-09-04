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
