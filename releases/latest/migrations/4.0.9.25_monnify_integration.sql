-- ============================================================================
-- MartPoint 4.0.9.25 — Monnify (Moniepoint) Integration
--
-- Adds first-party Monnify gateway support for collections and disbursements:
--   1. db_monnify_settings   — per-store API credentials (API key, secret key,
--      contract code), sandbox/live toggle, cached OAuth token and the payout
--      source wallet account number.
--   2. db_monnify_payments   — collection transactions (hosted checkout links
--      and pay-with-transfer dynamic accounts) linked to sales/customers.
--   3. db_monnify_transfers  — disbursement (NIP transfer) records with the
--      OTP / status lifecycle.
--   4. Grants `monnify_settings` and `monnify_transfers` to every role that
--      already holds `paystack_settings`.
-- ============================================================================

SET FOREIGN_KEY_CHECKS = 0;

-- 1. Monnify Settings (per store)
CREATE TABLE IF NOT EXISTS `db_monnify_settings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `store_id` INT NOT NULL DEFAULT 1,
  `api_key` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Monnify API Key (MK_PROD_.../MK_TEST_...)',
  `secret_key` VARCHAR(255) NOT NULL DEFAULT '' COMMENT 'Monnify Secret Key',
  `contract_code` VARCHAR(50) NOT NULL DEFAULT '' COMMENT 'Monnify Contract Code',
  `wallet_account_number` VARCHAR(20) NOT NULL DEFAULT '' COMMENT 'Monnify wallet account number — sourceAccountNumber for disbursements',
  `enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `disbursements_enabled` TINYINT(1) NOT NULL DEFAULT 0 COMMENT 'Requires Monnify approval + IP whitelist',
  `test_mode` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1=Sandbox, 0=Live',
  `access_token` TEXT DEFAULT NULL COMMENT 'Cached OAuth2 bearer token',
  `token_expires_at` DATETIME DEFAULT NULL,
  `callback_url` VARCHAR(500) DEFAULT NULL,
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_date` DATE DEFAULT NULL,
  `created_time` TIME DEFAULT NULL,
  `created_by` VARCHAR(50) DEFAULT NULL,
  UNIQUE KEY `uk_store` (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Monnify Collections tracking (checkout + pay-with-transfer)
CREATE TABLE IF NOT EXISTS `db_monnify_payments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `store_id` INT NOT NULL,
  `sales_id` INT DEFAULT NULL,
  `customer_id` INT DEFAULT NULL,
  `customer_name` VARCHAR(255) DEFAULT NULL,
  `customer_email` VARCHAR(255) DEFAULT NULL,
  `customer_phone` VARCHAR(50) DEFAULT NULL,
  `amount` DECIMAL(18,2) NOT NULL,
  `currency` VARCHAR(10) DEFAULT 'NGN',
  `payment_reference` VARCHAR(255) NOT NULL COMMENT 'Merchant-generated payment reference',
  `transaction_reference` VARCHAR(255) DEFAULT NULL COMMENT 'Monnify transaction reference',
  `checkout_url` VARCHAR(500) DEFAULT NULL,
  `transfer_account_number` VARCHAR(20) DEFAULT NULL COMMENT 'Dynamic account for pay-with-transfer',
  `transfer_bank_name` VARCHAR(100) DEFAULT NULL,
  `transfer_bank_code` VARCHAR(20) DEFAULT NULL,
  `transfer_account_name` VARCHAR(255) DEFAULT NULL,
  `transfer_account_expiry` VARCHAR(50) DEFAULT NULL,
  `payment_status` VARCHAR(50) DEFAULT 'PENDING' COMMENT 'PENDING, PAID, OVERPAID, PARTIALLY_PAID, FAILED, EXPIRED',
  `payment_method` VARCHAR(50) DEFAULT NULL,
  `amount_paid` DECIMAL(18,2) DEFAULT NULL,
  `paid_at` DATETIME DEFAULT NULL,
  `meta_data` TEXT DEFAULT NULL COMMENT 'JSON of extra metadata',
  `created_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_date` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_reference` (`payment_reference`),
  INDEX `idx_sales` (`sales_id`),
  INDEX `idx_status` (`payment_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Monnify Disbursements tracking (single transfers)
CREATE TABLE IF NOT EXISTS `db_monnify_transfers` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `store_id` INT NOT NULL,
  `reference` VARCHAR(255) NOT NULL COMMENT 'Merchant-generated transfer reference',
  `amount` DECIMAL(18,2) NOT NULL,
  `currency` VARCHAR(10) DEFAULT 'NGN',
  `narration` VARCHAR(100) DEFAULT NULL,
  `destination_bank_code` VARCHAR(20) NOT NULL,
  `destination_bank_name` VARCHAR(100) DEFAULT NULL,
  `destination_account_number` VARCHAR(20) NOT NULL,
  `destination_account_name` VARCHAR(255) DEFAULT NULL COMMENT 'Verified by name enquiry',
  `source_account_number` VARCHAR(20) DEFAULT NULL,
  `transfer_status` VARCHAR(50) DEFAULT 'PENDING' COMMENT 'PENDING_AUTHORIZATION, PENDING, SUCCESS, FAILED, REVERSED',
  `session_id` VARCHAR(100) DEFAULT NULL,
  `response_message` VARCHAR(255) DEFAULT NULL,
  `meta_data` TEXT DEFAULT NULL,
  `initiated_by` VARCHAR(50) DEFAULT NULL,
  `created_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_date` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY `uk_reference` (`reference`),
  INDEX `idx_status` (`transfer_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. PERMISSIONS — grant Monnify perms to roles that manage payment settings
INSERT INTO db_permissions (store_id, role_id, permissions)
SELECT DISTINCT d.store_id, d.role_id, 'monnify_settings'
FROM db_permissions d
LEFT JOIN db_permissions p2
  ON p2.store_id = d.store_id AND p2.role_id = d.role_id AND p2.permissions = 'monnify_settings'
WHERE d.permissions = 'paystack_settings' AND p2.id IS NULL;

INSERT INTO db_permissions (store_id, role_id, permissions)
SELECT DISTINCT d.store_id, d.role_id, 'monnify_transfers'
FROM db_permissions d
LEFT JOIN db_permissions p2
  ON p2.store_id = d.store_id AND p2.role_id = d.role_id AND p2.permissions = 'monnify_transfers'
WHERE d.permissions = 'paystack_settings' AND p2.id IS NULL;

SET FOREIGN_KEY_CHECKS = 1;
