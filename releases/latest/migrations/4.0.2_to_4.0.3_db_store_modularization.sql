-- MartPoint 4.0.2 -> 4.0.3 Migration
-- db_store Modularization
--
-- Goal: split the oversized db_store "God table" into focused modular tables
-- while keeping the application stable for existing and fresh installations.
--
-- Properties:
--   - Idempotent (safe to run more than once)
--   - Does NOT drop or alter db_store columns (backward compatibility)
--   - Copies existing data from db_store into new modular tables
--   - Uses information_schema + PREPARE/EXECUTE for dynamic column detection
--   - Does not fail if some columns do not exist
--   - Adds ROW_FORMAT=DYNAMIC where useful
--
-- Run this via Updates_model::index() or manually in phpMyAdmin.

SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- ---------------------------------------------------------------------------
-- Backup db_store before touching anything
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS db_store_backup_pre_modularization AS
SELECT * FROM db_store WHERE 1=0;

SET @backup_count = (SELECT COUNT(*) FROM db_store_backup_pre_modularization);
SET @sql = IF(@backup_count = 0,
  'INSERT INTO db_store_backup_pre_modularization SELECT * FROM db_store',
  'SELECT 1');
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------------------
-- Helper procedure: mp_copy_store_column
-- Copies a single source column from db_store to a destination column in a
-- destination table if the source column exists. The destination table must
-- have a store_id column.
-- ---------------------------------------------------------------------------
DELIMITER $$
DROP PROCEDURE IF EXISTS mp_copy_store_column$$
CREATE PROCEDURE mp_copy_store_column(IN p_dest_table VARCHAR(64), IN p_src_col VARCHAR(64), IN p_dst_col VARCHAR(64))
BEGIN
  DECLARE v_src_exists INT;
  DECLARE v_dest_exists INT;

  SET v_dest_exists = (SELECT 1 FROM information_schema.TABLES
                       WHERE TABLE_SCHEMA = DATABASE()
                         AND TABLE_NAME = p_dest_table);
  IF v_dest_exists IS NULL THEN
    SELECT CONCAT('Destination table ', p_dest_table, ' does not exist; skipping') AS msg;
  ELSE
    SET v_src_exists = (SELECT 1 FROM information_schema.COLUMNS
                        WHERE TABLE_SCHEMA = DATABASE()
                          AND TABLE_NAME = 'db_store'
                          AND COLUMN_NAME = p_src_col);
    IF v_src_exists IS NOT NULL THEN
      SET @ins_sql = CONCAT(
        'INSERT INTO ', p_dest_table, ' (store_id, ', p_dst_col, ') ',
        'SELECT id, `', p_src_col, '` FROM db_store ',
        'ON DUPLICATE KEY UPDATE ', p_dst_col, ' = VALUES(', p_dst_col, ')'
      );
      PREPARE ins_stmt FROM @ins_sql;
      EXECUTE ins_stmt;
      DEALLOCATE PREPARE ins_stmt;
    END IF;
  END IF;
END$$
DELIMITER ;

-- ---------------------------------------------------------------------------
-- Create modular tables
-- ---------------------------------------------------------------------------

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

-- ---------------------------------------------------------------------------
-- Migrate data from db_store to modular tables
-- Each call copies one column map. The procedure silently skips columns that
-- do not exist in db_store, so this is safe across schema variants.
-- ---------------------------------------------------------------------------

-- Theme settings
CALL mp_copy_store_column('db_store_theme_settings', 'store_logo', 'store_logo');
CALL mp_copy_store_column('db_store_theme_settings', 'logo', 'logo');
CALL mp_copy_store_column('db_store_theme_settings', 'fav_icon', 'fav_icon');

-- Storefront settings
CALL mp_copy_store_column('db_store_storefront_settings', 'store_website', 'store_website');
CALL mp_copy_store_column('db_store_storefront_settings', 'website', 'website');
CALL mp_copy_store_column('db_store_storefront_settings', 'storefront_theme_key', 'storefront_theme_key');

-- Tax settings
CALL mp_copy_store_column('db_store_tax_settings', 'gst_no', 'gst_no');
CALL mp_copy_store_column('db_store_tax_settings', 'vat_no', 'vat_no');
CALL mp_copy_store_column('db_store_tax_settings', 'pan_no', 'pan_no');
CALL mp_copy_store_column('db_store_tax_settings', 'regno_key', 'regno_key');
CALL mp_copy_store_column('db_store_tax_settings', 'currencysymbol_id', 'currencysymbol_id');

-- Receipt settings
CALL mp_copy_store_column('db_store_receipt_settings', 'invoice_view', 'invoice_view');
CALL mp_copy_store_column('db_store_receipt_settings', 'sales_invoice_format_id', 'sales_invoice_format_id');
CALL mp_copy_store_column('db_store_receipt_settings', 'pos_invoice_format_id', 'pos_invoice_format_id');
CALL mp_copy_store_column('db_store_receipt_settings', 'sales_invoice_footer_text', 'sales_invoice_footer_text');
CALL mp_copy_store_column('db_store_receipt_settings', 'invoice_terms', 'invoice_terms');
CALL mp_copy_store_column('db_store_receipt_settings', 'previous_balance_bit', 'previous_balance_bit');
CALL mp_copy_store_column('db_store_receipt_settings', 'round_off', 'round_off');
CALL mp_copy_store_column('db_store_receipt_settings', 'change_return', 'change_return');
CALL mp_copy_store_column('db_store_receipt_settings', 'decimals', 'decimals');
CALL mp_copy_store_column('db_store_receipt_settings', 'qty_decimals', 'qty_decimals');
CALL mp_copy_store_column('db_store_receipt_settings', 'number_to_words', 'number_to_words');
CALL mp_copy_store_column('db_store_receipt_settings', 't_and_c_status', 't_and_c_status');
CALL mp_copy_store_column('db_store_receipt_settings', 't_and_c_status_pos', 't_and_c_status_pos');

-- POS settings
CALL mp_copy_store_column('db_store_pos_settings', 'sales_discount', 'sales_discount');
CALL mp_copy_store_column('db_store_pos_settings', 'mrp_column', 'mrp_column');
CALL mp_copy_store_column('db_store_pos_settings', 'show_signature', 'show_signature');
CALL mp_copy_store_column('db_store_pos_settings', 'previous_balance_bit', 'previous_balance_bit');
CALL mp_copy_store_column('db_store_pos_settings', 'default_account_id', 'default_account_id');
CALL mp_copy_store_column('db_store_pos_settings', 'cash_account_id', 'cash_account_id');

-- Inventory settings
CALL mp_copy_store_column('db_store_inventory_settings', 'category_init', 'category_init');
CALL mp_copy_store_column('db_store_inventory_settings', 'item_init', 'item_init');
CALL mp_copy_store_column('db_store_inventory_settings', 'supplier_init', 'supplier_init');
CALL mp_copy_store_column('db_store_inventory_settings', 'purchase_init', 'purchase_init');
CALL mp_copy_store_column('db_store_inventory_settings', 'purchase_return_init', 'purchase_return_init');
CALL mp_copy_store_column('db_store_inventory_settings', 'customer_init', 'customer_init');
CALL mp_copy_store_column('db_store_inventory_settings', 'sales_init', 'sales_init');
CALL mp_copy_store_column('db_store_inventory_settings', 'sales_return_init', 'sales_return_init');
CALL mp_copy_store_column('db_store_inventory_settings', 'expense_init', 'expense_init');
CALL mp_copy_store_column('db_store_inventory_settings', 'accounts_init', 'accounts_init');
CALL mp_copy_store_column('db_store_inventory_settings', 'journal_init', 'journal_init');
CALL mp_copy_store_column('db_store_inventory_settings', 'cust_advance_init', 'cust_advance_init');
CALL mp_copy_store_column('db_store_inventory_settings', 'quotation_init', 'quotation_init');
CALL mp_copy_store_column('db_store_inventory_settings', 'money_transfer_init', 'money_transfer_init');
CALL mp_copy_store_column('db_store_inventory_settings', 'sales_payment_init', 'sales_payment_init');
CALL mp_copy_store_column('db_store_inventory_settings', 'sales_return_payment_init', 'sales_return_payment_init');
CALL mp_copy_store_column('db_store_inventory_settings', 'purchase_payment_init', 'purchase_payment_init');
CALL mp_copy_store_column('db_store_inventory_settings', 'purchase_return_payment_init', 'purchase_return_payment_init');
CALL mp_copy_store_column('db_store_inventory_settings', 'expense_payment_init', 'expense_payment_init');
CALL mp_copy_store_column('db_store_inventory_settings', 'purchase_code', 'purchase_code');

-- Notification settings
CALL mp_copy_store_column('db_store_notification_settings', 'sms_status', 'sms_status');
CALL mp_copy_store_column('db_store_notification_settings', 'sms_url', 'sms_url');
CALL mp_copy_store_column('db_store_notification_settings', 'smtp_host', 'smtp_host');
CALL mp_copy_store_column('db_store_notification_settings', 'smtp_port', 'smtp_port');
CALL mp_copy_store_column('db_store_notification_settings', 'smtp_user', 'smtp_user');
CALL mp_copy_store_column('db_store_notification_settings', 'smtp_pass', 'smtp_pass');
CALL mp_copy_store_column('db_store_notification_settings', 'smtp_status', 'smtp_status');

-- Industry settings
CALL mp_copy_store_column('db_store_industry_settings', 'industry_type', 'industry_type');
CALL mp_copy_store_column('db_store_industry_settings', 'business_model', 'business_model');
CALL mp_copy_store_column('db_store_industry_settings', 'workflow_template_key', 'workflow_template_key');
CALL mp_copy_store_column('db_store_industry_settings', 'dashboard_template_key', 'dashboard_template_key');
CALL mp_copy_store_column('db_store_industry_settings', 'storefront_theme_key', 'storefront_theme_key');
CALL mp_copy_store_column('db_store_industry_settings', 'feature_flags_json', 'feature_flags_json');
CALL mp_copy_store_column('db_store_industry_settings', 'label_overrides_json', 'label_overrides_json');
CALL mp_copy_store_column('db_store_industry_settings', 'industry_settings_json', 'industry_settings_json');

-- Business profile (mirrors industry settings for verification/phase-2 split)
CALL mp_copy_store_column('db_store_business_profile', 'industry_type', 'industry_type');
CALL mp_copy_store_column('db_store_business_profile', 'business_model', 'business_model');
CALL mp_copy_store_column('db_store_business_profile', 'workflow_template_key', 'workflow_template_key');
CALL mp_copy_store_column('db_store_business_profile', 'dashboard_template_key', 'dashboard_template_key');
CALL mp_copy_store_column('db_store_business_profile', 'storefront_theme_key', 'storefront_theme_key');
CALL mp_copy_store_column('db_store_business_profile', 'feature_flags_json', 'feature_flags_json');
CALL mp_copy_store_column('db_store_business_profile', 'label_overrides_json', 'label_overrides_json');
CALL mp_copy_store_column('db_store_business_profile', 'industry_settings_json', 'industry_settings_json');

-- Payment settings (legacy/unused)
CALL mp_copy_store_column('db_store_payment_settings', 'upi_id', 'upi_id');
CALL mp_copy_store_column('db_store_payment_settings', 'upi_code', 'upi_code');
CALL mp_copy_store_column('db_store_payment_settings', 'bank_details', 'bank_details');

-- ---------------------------------------------------------------------------
-- Migrate key/value settings
-- ---------------------------------------------------------------------------

-- NIN API settings
SET @has_nin = (SELECT 1 FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'db_store'
                  AND COLUMN_NAME = 'nin_api_enabled');
SET @sql = IF(@has_nin IS NOT NULL,
  'INSERT INTO db_store_settings (store_id, setting_group, setting_key, setting_value, value_type)
   SELECT id, ''nin_api'', ''nin_api_enabled'', nin_api_enabled, ''int'' FROM db_store
   ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_nin = (SELECT 1 FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'db_store'
                  AND COLUMN_NAME = 'nin_api_url');
SET @sql = IF(@has_nin IS NOT NULL,
  'INSERT INTO db_store_settings (store_id, setting_group, setting_key, setting_value, value_type)
   SELECT id, ''nin_api'', ''nin_api_url'', nin_api_url, ''string'' FROM db_store WHERE nin_api_url IS NOT NULL
   ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_nin = (SELECT 1 FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'db_store'
                  AND COLUMN_NAME = 'nin_api_key');
SET @sql = IF(@has_nin IS NOT NULL,
  'INSERT INTO db_store_settings (store_id, setting_group, setting_key, setting_value, value_type)
   SELECT id, ''nin_api'', ''nin_api_key'', nin_api_key, ''string'' FROM db_store WHERE nin_api_key IS NOT NULL
   ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_nin = (SELECT 1 FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'db_store'
                  AND COLUMN_NAME = 'nin_api_provider');
SET @sql = IF(@has_nin IS NOT NULL,
  'INSERT INTO db_store_settings (store_id, setting_group, setting_key, setting_value, value_type)
   SELECT id, ''nin_api'', ''nin_api_provider'', nin_api_provider, ''string'' FROM db_store
   ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_nin = (SELECT 1 FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'db_store'
                  AND COLUMN_NAME = 'nin_api_cost');
SET @sql = IF(@has_nin IS NOT NULL,
  'INSERT INTO db_store_settings (store_id, setting_group, setting_key, setting_value, value_type)
   SELECT id, ''nin_api'', ''nin_api_cost'', nin_api_cost, ''float'' FROM db_store
   ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Legacy / other settings
SET @has_lang = (SELECT 1 FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = 'db_store'
                   AND COLUMN_NAME = 'language_id');
SET @sql = IF(@has_lang IS NOT NULL,
  'INSERT INTO db_store_settings (store_id, setting_group, setting_key, setting_value, value_type)
   SELECT id, ''general'', ''language_id'', language_id, ''int'' FROM db_store
   ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_sub = (SELECT 1 FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'db_store'
                  AND COLUMN_NAME = 'current_subscriptionlist_id');
SET @sql = IF(@has_sub IS NOT NULL,
  'INSERT INTO db_store_settings (store_id, setting_group, setting_key, setting_value, value_type)
   SELECT id, ''general'', ''current_subscriptionlist_id'', current_subscriptionlist_id, ''int'' FROM db_store
   ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_cid = (SELECT 1 FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'db_store'
                  AND COLUMN_NAME = 'cid');
SET @sql = IF(@has_cid IS NOT NULL,
  'INSERT INTO db_store_settings (store_id, setting_group, setting_key, setting_value, value_type)
   SELECT id, ''general'', ''cid'', cid, ''int'' FROM db_store WHERE cid IS NOT NULL
   ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---------------------------------------------------------------------------
-- Ensure business profile seed rows exist for every store (idempotent)
-- ---------------------------------------------------------------------------
INSERT INTO db_store_business_profile (store_id, industry_type, business_model, workflow_template_key, dashboard_template_key, storefront_theme_key)
SELECT id, 'general_retail', 'product_based', 'retail_standard', 'general_retail', 'general_retail'
FROM db_store
WHERE id NOT IN (SELECT store_id FROM db_store_business_profile)
ON DUPLICATE KEY UPDATE store_id = store_id;

-- ---------------------------------------------------------------------------
-- Drop helper procedure
-- ---------------------------------------------------------------------------
DROP PROCEDURE IF EXISTS mp_copy_store_column;

-- ---------------------------------------------------------------------------
-- Record migration
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS db_schema_migrations (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `version` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `filename` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `applied_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_version_file` (`version`,`filename`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO db_schema_migrations (version, filename)
VALUES ('4.0.3', '4.0.2_to_4.0.3_db_store_modularization.sql')
ON DUPLICATE KEY UPDATE applied_at = applied_at;

SET FOREIGN_KEY_CHECKS = 1;
