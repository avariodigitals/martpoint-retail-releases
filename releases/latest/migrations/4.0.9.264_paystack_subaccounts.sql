-- Paystack merchant payout destinations and platform split configuration.
-- Forward migration: 4.0.9.197 remains a historical no-op marker and is not
-- edited, so installs that already recorded it still receive this schema.

CREATE TABLE IF NOT EXISTS `db_payment_gateway_accounts` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `provider` VARCHAR(32) NOT NULL,
  `api_key` VARCHAR(255) DEFAULT NULL,
  `secret_key` VARCHAR(255) DEFAULT NULL,
  `public_key` VARCHAR(255) DEFAULT NULL,
  `contract_code` VARCHAR(64) DEFAULT NULL,
  `wallet_account_number` VARCHAR(32) DEFAULT NULL,
  `access_token` TEXT DEFAULT NULL,
  `token_expires_at` DATETIME DEFAULT NULL,
  `webhook_secret` VARCHAR(255) DEFAULT NULL,
  `callback_url` VARCHAR(500) DEFAULT NULL,
  `enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `disbursements_enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `test_mode` TINYINT(1) NOT NULL DEFAULT 1,
  `platform_fee_type` VARCHAR(16) NOT NULL DEFAULT 'none',
  `platform_fee_value` DECIMAL(18,2) NOT NULL DEFAULT 0,
  `fee_bearer` VARCHAR(16) NOT NULL DEFAULT 'platform',
  `status` TINYINT(1) NOT NULL DEFAULT 1,
  `created_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `created_time` TIME DEFAULT NULL,
  `created_by` VARCHAR(64) DEFAULT NULL,
  `updated_date` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_payment_gateway_provider` (`provider`),
  KEY `idx_payment_gateway_enabled` (`enabled`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `db_payment_subaccounts` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `provider` VARCHAR(32) NOT NULL,
  `store_id` INT(11) NOT NULL,
  `business_name` VARCHAR(255) DEFAULT NULL,
  `contact_email` VARCHAR(255) DEFAULT NULL,
  `contact_phone` VARCHAR(64) DEFAULT NULL,
  `bank_code` VARCHAR(32) NOT NULL,
  `bank_name` VARCHAR(128) DEFAULT NULL,
  `account_number` VARCHAR(32) NOT NULL,
  `account_name` VARCHAR(255) DEFAULT NULL,
  `provider_subaccount_code` VARCHAR(128) DEFAULT NULL,
  `provider_account_id` VARCHAR(128) DEFAULT NULL,
  `status` VARCHAR(32) NOT NULL DEFAULT 'PENDING',
  `provider_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `last_error` TEXT DEFAULT NULL,
  `provider_payload` MEDIUMTEXT DEFAULT NULL,
  `created_date` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_date` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_payment_subaccount_store` (`provider`, `store_id`),
  KEY `idx_payment_subaccount_status` (`provider`, `status`, `enabled`),
  KEY `idx_payment_subaccount_code` (`provider`, `provider_subaccount_code`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Payment rows are created by earlier Paystack/Monnify migrations. Guard each
-- additive column so this migration remains safe on fleets with partial schema.
SET @t = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_paystack_payments');
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_paystack_payments' AND COLUMN_NAME='amount_paid');
SET @s = IF(@t=1 AND @c=0, 'ALTER TABLE `db_paystack_payments` ADD COLUMN `amount_paid` DECIMAL(18,2) DEFAULT NULL AFTER `amount`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @t = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_paystack_payments');
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_paystack_payments' AND COLUMN_NAME='platform_fee');
SET @s = IF(@t=1 AND @c=0, 'ALTER TABLE `db_paystack_payments` ADD COLUMN `platform_fee` DECIMAL(18,2) DEFAULT NULL AFTER `amount_paid`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @t = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_monnify_payments');
SET @c = (SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='db_monnify_payments' AND COLUMN_NAME='platform_fee');
SET @s = IF(@t=1 AND @c=0, 'ALTER TABLE `db_monnify_payments` ADD COLUMN `platform_fee` DECIMAL(18,2) DEFAULT NULL AFTER `amount_paid`', 'SELECT 1');
PREPARE stmt FROM @s; EXECUTE stmt; DEALLOCATE PREPARE stmt;
