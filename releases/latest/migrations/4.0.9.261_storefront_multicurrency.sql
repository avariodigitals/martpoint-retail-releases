-- Storefront checkout currency configuration and immutable order snapshots.
SET @db := DATABASE();

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_storefront_settings' AND COLUMN_NAME='multicurrency_enabled')=0,
  'ALTER TABLE db_storefront_settings ADD COLUMN multicurrency_enabled TINYINT(1) NOT NULL DEFAULT 0, ADD COLUMN checkout_currencies_json TEXT NULL, ADD COLUMN currency_rates_json TEXT NULL, ADD COLUMN currency_country_defaults_json TEXT NULL, ADD COLUMN currency_gateway_map_json TEXT NULL, ADD COLUMN currency_default_code VARCHAR(3) NULL, ADD COLUMN currency_rates_updated_at DATETIME NULL, ADD COLUMN currency_rates_updated_by INT NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_storefront_settings' AND COLUMN_NAME='storefront_tax_enabled')=0,
  'ALTER TABLE db_storefront_settings ADD COLUMN storefront_tax_enabled TINYINT(1) NOT NULL DEFAULT 0, ADD COLUMN storefront_tax_rate DECIMAL(8,4) NOT NULL DEFAULT 0, ADD COLUMN storefront_tax_mode ENUM(''inclusive'',''exclusive'') NOT NULL DEFAULT ''exclusive''', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_online_orders' AND COLUMN_NAME='base_currency')=0,
  'ALTER TABLE db_online_orders ADD COLUMN base_currency VARCHAR(3) NULL, ADD COLUMN transaction_currency VARCHAR(3) NULL, ADD COLUMN exchange_rate DECIMAL(20,8) NULL, ADD COLUMN exchange_rate_convention VARCHAR(80) NULL, ADD COLUMN exchange_rate_source VARCHAR(32) NULL, ADD COLUMN exchange_rate_snapshot_at DATETIME NULL, ADD COLUMN base_subtotal DECIMAL(20,4) NULL, ADD COLUMN transaction_subtotal DECIMAL(20,4) NULL, ADD COLUMN base_delivery_fee DECIMAL(20,4) NULL, ADD COLUMN transaction_delivery_fee DECIMAL(20,4) NULL, ADD COLUMN base_tax_amount DECIMAL(20,4) NULL, ADD COLUMN transaction_tax_amount DECIMAL(20,4) NULL, ADD COLUMN base_coupon_discount DECIMAL(20,4) NULL, ADD COLUMN transaction_coupon_discount DECIMAL(20,4) NULL, ADD COLUMN base_grand_total DECIMAL(20,4) NULL, ADD COLUMN transaction_grand_total DECIMAL(20,4) NULL, ADD COLUMN currency_snapshot_json MEDIUMTEXT NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_online_order_items' AND COLUMN_NAME='base_unit_price')=0,
  'ALTER TABLE db_online_order_items ADD COLUMN base_unit_price DECIMAL(20,4) NULL, ADD COLUMN transaction_unit_price DECIMAL(20,4) NULL, ADD COLUMN base_total_price DECIMAL(20,4) NULL, ADD COLUMN transaction_total_price DECIMAL(20,4) NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_online_order_items' AND COLUMN_NAME='base_tax_amount')=0,
  'ALTER TABLE db_online_order_items ADD COLUMN base_tax_amount DECIMAL(20,4) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_online_order_items' AND COLUMN_NAME='transaction_tax_amount')=0,
  'ALTER TABLE db_online_order_items ADD COLUMN transaction_tax_amount DECIMAL(20,4) NOT NULL DEFAULT 0', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_online_orders' AND COLUMN_NAME='tax_rate_snapshot')=0,
  'ALTER TABLE db_online_orders ADD COLUMN tax_rate_snapshot DECIMAL(8,4) NOT NULL DEFAULT 0, ADD COLUMN tax_mode_snapshot VARCHAR(12) NOT NULL DEFAULT ''exclusive''', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_online_orders' AND COLUMN_NAME='tax_mode_snapshot')=0,
  'ALTER TABLE db_online_orders ADD COLUMN tax_mode_snapshot VARCHAR(12) NOT NULL DEFAULT ''exclusive''', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

CREATE TABLE IF NOT EXISTS `db_online_order_refunds` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `online_order_id` INT NOT NULL,
  `payment_id` BIGINT UNSIGNED NULL,
  `provider` VARCHAR(20) NOT NULL,
  `provider_payment_reference` VARCHAR(120) NULL,
  `provider_refund_reference` VARCHAR(120) NULL,
  `idempotency_reference` VARCHAR(64) NOT NULL,
  `transaction_currency` VARCHAR(3) NOT NULL,
  `transaction_amount` DECIMAL(20,4) NOT NULL,
  `base_currency` VARCHAR(3) NOT NULL,
  `base_amount` DECIMAL(20,4) NOT NULL,
  `exchange_rate` DECIMAL(20,8) NOT NULL,
  `provider_status` VARCHAR(32) NOT NULL DEFAULT 'requested',
  `provider_response_json` MEDIUMTEXT NULL,
  `disposition` VARCHAR(24) NULL,
  `restocked` TINYINT(1) NOT NULL DEFAULT 0,
  `requested_by` INT NULL,
  `requested_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_order_refund_idempotency` (`store_id`,`idempotency_reference`),
  KEY `idx_order_refunds` (`store_id`,`online_order_id`,`provider_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_online_orders' AND COLUMN_NAME='refund_currency')=0,
  'ALTER TABLE db_online_orders ADD COLUMN refund_currency VARCHAR(3) NULL, ADD COLUMN refund_base_amount DECIMAL(20,4) NULL, ADD COLUMN refund_provider_status VARCHAR(24) NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

-- Credentials are per store, separate from legacy subscription PayPal settings.
CREATE TABLE IF NOT EXISTS `db_storefront_paypal_accounts` (
  `store_id` INT NOT NULL,
  `client_id` VARCHAR(255) NULL,
  `client_secret` TEXT NULL,
  `webhook_id` VARCHAR(255) NULL,
  `environment` VARCHAR(12) NOT NULL DEFAULT 'sandbox',
  `enabled` TINYINT(1) NOT NULL DEFAULT 0,
  `updated_at` DATETIME NULL,
  `updated_by` INT NULL,
  PRIMARY KEY (`store_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_online_order_payments' AND COLUMN_NAME='base_amount')=0,
  'ALTER TABLE db_online_order_payments ADD COLUMN base_amount DECIMAL(20,4) NULL, ADD COLUMN transaction_currency VARCHAR(3) NULL, ADD COLUMN provider_reference VARCHAR(120) NULL, ADD COLUMN provider_status VARCHAR(24) NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;
SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_online_order_payments' AND COLUMN_NAME='transaction_amount')=0,
  'ALTER TABLE db_online_order_payments ADD COLUMN transaction_amount DECIMAL(20,4) NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_online_orders' AND COLUMN_NAME='paypal_order_id')=0,
  'ALTER TABLE db_online_orders ADD COLUMN paypal_order_id VARCHAR(120) NULL', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

SET @s := IF((SELECT COUNT(*) FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=@db AND TABLE_NAME='db_online_orders' AND COLUMN_NAME='payment_method' AND COLUMN_TYPE LIKE '%enum%')>0,
  'ALTER TABLE db_online_orders MODIFY COLUMN payment_method ENUM(''paystack'',''whatsapp'',''pay_on_delivery'',''appointment'',''paypal'') DEFAULT ''pay_on_delivery''', 'SELECT 1');
PREPARE st FROM @s; EXECUTE st; DEALLOCATE PREPARE st;

CREATE TABLE IF NOT EXISTS `db_online_order_payment_attempts` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `store_id` INT NOT NULL,
  `online_order_id` INT NOT NULL,
  `provider` VARCHAR(20) NOT NULL,
  `provider_order_id` VARCHAR(120) NOT NULL,
  `approval_url` TEXT NULL,
  `transaction_currency` VARCHAR(3) NOT NULL,
  `transaction_amount` DECIMAL(20,4) NOT NULL,
  `base_currency` VARCHAR(3) NOT NULL,
  `base_amount` DECIMAL(20,4) NOT NULL,
  `exchange_rate` DECIMAL(20,8) NOT NULL,
  `rate_snapshot_at` DATETIME NOT NULL,
  `status` VARCHAR(24) NOT NULL,
  `capture_reference` VARCHAR(120) NULL,
  `created_at` DATETIME NOT NULL,
  `updated_at` DATETIME NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_online_order_provider_order` (`provider`,`provider_order_id`),
  UNIQUE KEY `uq_online_order_capture` (`provider`,`capture_reference`),
  KEY `idx_online_order_attempt` (`store_id`,`online_order_id`,`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
