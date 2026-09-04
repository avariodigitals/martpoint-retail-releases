# MartPoint db_store Modularization Plan

## 1. Objective

`db_store` has become a God table with too many unrelated columns. It is near the InnoDB 8126-byte row-size limit, which is blocking future migrations. This plan modularizes store configuration into focused tables while keeping the application stable for both existing and fresh installations.

## 2. Current `db_store` Column Inventory

Current schema from `/Users/ralphmore/Herd/martpointretailapp/setup/install/includes/db.txt` (lines 2772-2865):

```sql
CREATE TABLE `db_store` (
  `id` int(5) NOT NULL,
  `store_code` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_website` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `website` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_logo` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `upi_id` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `upi_code` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location_lat` decimal(10,8) DEFAULT NULL,
  `location_lng` decimal(11,8) DEFAULT NULL,
  `postcode` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `gst_no` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `vat_no` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `pan_no` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `bank_details` mediumtext COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cid` int(50) DEFAULT NULL,
  `category_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `item_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'INITAL CODE',
  `supplier_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'INITAL CODE',
  `purchase_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'INITAL CODE',
  `purchase_return_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `customer_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'INITAL CODE',
  `sales_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'INITAL CODE',
  `sales_return_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expense_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `accounts_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `journal_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cust_advance_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `invoice_view` int(5) DEFAULT NULL COMMENT '1=Standard,2=Indian GST',
  `sms_status` int(1) DEFAULT NULL COMMENT '1=Enable 0=Disable',
  `status` int(1) DEFAULT NULL,
  `language_id` int(5) DEFAULT NULL,
  `currency_id` int(5) DEFAULT NULL,
  `currency_placement` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `timezone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_format` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_format` int(5) DEFAULT NULL,
  `sales_discount` double(20,4) DEFAULT NULL,
  `currencysymbol_id` int(5) DEFAULT NULL,
  `regno_key` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fav_icon` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `purchase_code` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `change_return` int(2) DEFAULT NULL,
  `sales_invoice_format_id` int(5) DEFAULT NULL,
  `pos_invoice_format_id` int(5) DEFAULT NULL,
  `sales_invoice_footer_text` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `round_off` int(1) DEFAULT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_ip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `quotation_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `decimals` int(1) DEFAULT 2,
  `money_transfer_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sales_payment_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `sales_return_payment_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `purchase_payment_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `purchase_return_payment_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `expense_payment_init` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `current_subscriptionlist_id` int(10) DEFAULT 0,
  `smtp_host` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_port` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_user` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_pass` varchar(250) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `smtp_status` int(1) DEFAULT 0,
  `sms_url` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int(5) NOT NULL,
  `mrp_column` int(1) DEFAULT 0,
  `invoice_terms` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `previous_balance_bit` int(1) DEFAULT 1 COMMENT '1=Show, 0=Hide - Shows on sales invoice',
  `nin_api_enabled` tinyint(1) NOT NULL DEFAULT 0,
  `nin_api_url` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nin_api_key` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `nin_api_provider` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'ninbvnportal',
  `nin_api_cost` decimal(10,2) NOT NULL DEFAULT 50.00,
  `industry_type` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'general_retail',
  `business_model` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'product_based',
  `feature_flags_json` json DEFAULT NULL,
  `workflow_template_key` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'retail_standard',
  `dashboard_template_key` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'general_retail',
  `storefront_theme_key` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT 'general_retail',
  `label_overrides_json` json DEFAULT NULL,
  `industry_settings_json` json DEFAULT NULL
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### Column Grouping

| Group | Columns |
|-------|---------|
| **Core store identity** | `id`, `store_code`, `store_name`, `user_id`, `status` |
| **Contact/location** | `mobile`, `phone`, `email`, `country`, `state`, `city`, `address`, `postcode`, `location_lat`, `location_lng` |
| **Online presence** | `store_website`, `website`, `store_logo`, `logo`, `fav_icon` |
| **Tax/currency** | `gst_no`, `vat_no`, `pan_no`, `regno_key`, `currency_id`, `currency_placement`, `currencysymbol_id` |
| **Receipt/invoice** | `invoice_view`, `sales_invoice_format_id`, `pos_invoice_format_id`, `sales_invoice_footer_text`, `invoice_terms`, `previous_balance_bit`, `round_off`, `change_return`, `decimals` |
| **POS settings** | `change_return`, `sales_discount`, `mrp_column`, `show_signature`, `previous_balance_bit`, `round_off`, `decimals`, `qty_decimals`, `number_to_words`, `t_and_c_status`, `t_and_c_status_pos` |
| **Inventory/document init codes** | `category_init`, `item_init`, `supplier_init`, `purchase_init`, `purchase_return_init`, `customer_init`, `sales_init`, `sales_return_init`, `expense_init`, `accounts_init`, `journal_init`, `cust_advance_init`, `quotation_init`, `money_transfer_init`, `sales_payment_init`, `sales_return_payment_init`, `purchase_payment_init`, `purchase_return_payment_init`, `expense_payment_init`, `purchase_code` |
| **Notification/Email** | `sms_status`, `sms_url`, `smtp_host`, `smtp_port`, `smtp_user`, `smtp_pass`, `smtp_status` |
| **Storefront** | `store_website`, `website`, `storefront_theme_key` |
| **Industry/Workflow/Template** | `industry_type`, `business_model`, `workflow_template_key`, `dashboard_template_key`, `storefront_theme_key`, `feature_flags_json`, `label_overrides_json`, `industry_settings_json` |
| **Theme/UI** | `store_logo`, `logo`, `fav_icon`, `storefront_theme_key` |
| **NIN API** | `nin_api_enabled`, `nin_api_url`, `nin_api_key`, `nin_api_provider`, `nin_api_cost` |
| **Legacy/Unused** | `upi_id`, `upi_code`, `bank_details`, `cid` |
| **System/audit** | `created_date`, `created_time`, `created_by`, `system_ip`, `system_name`, `language_id`, `time_format`, `date_format`, `timezone` |
| **Licensing** | `current_subscriptionlist_id` |

## 3. Columns That Stay in `db_store`

To keep the row small and stable, only core identity, contact, and system audit columns remain:

- `id`
- `store_code`
- `store_name`
- `mobile`
- `phone`
- `email`
- `country`
- `state`
- `city`
- `address`
- `postcode`
- `location_lat`
- `location_lng`
- `currency_id`
- `currency_placement`
- `timezone`
- `date_format`
- `time_format`
- `status`
- `user_id`
- `created_date`
- `created_time`
- `created_by`
- `system_ip`
- `system_name`

Note: `currency_id`, `currency_placement`, `timezone`, `date_format`, `time_format` are kept because they are store-wide operational defaults that are read frequently and are stable. `user_id` is the creating/owner user and is required for store creation.

## 4. Columns That Move

| Column | Destination Table | Notes |
|--------|-------------------|-------|
| `store_logo` | `db_store_theme_settings` | Brand/logo asset |
| `logo` | `db_store_theme_settings` | Brand/logo asset |
| `fav_icon` | `db_store_theme_settings` | Brand/logo asset |
| `store_website` | `db_store_storefront_settings` | Online presence |
| `website` | `db_store_storefront_settings` | Online presence |
| `storefront_theme_key` | `db_store_storefront_settings` | Theme reference |
| `gst_no` | `db_store_tax_settings` | Tax registration |
| `vat_no` | `db_store_tax_settings` | Tax registration |
| `pan_no` | `db_store_tax_settings` | Tax registration |
| `regno_key` | `db_store_tax_settings` | Tax registration |
| `currencysymbol_id` | `db_store_tax_settings` | Currency symbol |
| `invoice_view` | `db_store_receipt_settings` | Invoice layout |
| `sales_invoice_format_id` | `db_store_receipt_settings` | Invoice format |
| `pos_invoice_format_id` | `db_store_receipt_settings` | POS format |
| `sales_invoice_footer_text` | `db_store_receipt_settings` | Invoice footer |
| `invoice_terms` | `db_store_receipt_settings` | Invoice terms |
| `previous_balance_bit` | `db_store_receipt_settings` | Invoice display |
| `round_off` | `db_store_receipt_settings` | Invoice rounding |
| `change_return` | `db_store_receipt_settings` | POS/receipt change |
| `decimals` | `db_store_receipt_settings` | Decimal display |
| `qty_decimals` | `db_store_receipt_settings` | Quantity decimals |
| `number_to_words` | `db_store_receipt_settings` | Print wording |
| `t_and_c_status` | `db_store_receipt_settings` | T&C display |
| `t_and_c_status_pos` | `db_store_receipt_settings` | T&C display POS |
| `sales_discount` | `db_store_pos_settings` | POS default discount |
| `mrp_column` | `db_store_pos_settings` | POS MRP column |
| `show_signature` | `db_store_pos_settings` | POS signature |
| `default_account_id` | `db_store_pos_settings` | POS default account |
| `cash_account_id` | `db_store_pos_settings` | Cash account |
| `category_init` | `db_store_inventory_settings` | Init code |
| `item_init` | `db_store_inventory_settings` | Init code |
| `supplier_init` | `db_store_inventory_settings` | Init code |
| `purchase_init` | `db_store_inventory_settings` | Init code |
| `purchase_return_init` | `db_store_inventory_settings` | Init code |
| `customer_init` | `db_store_inventory_settings` | Init code |
| `sales_init` | `db_store_inventory_settings` | Init code |
| `sales_return_init` | `db_store_inventory_settings` | Init code |
| `expense_init` | `db_store_inventory_settings` | Init code |
| `accounts_init` | `db_store_inventory_settings` | Init code |
| `journal_init` | `db_store_inventory_settings` | Init code |
| `cust_advance_init` | `db_store_inventory_settings` | Init code |
| `quotation_init` | `db_store_inventory_settings` | Init code |
| `money_transfer_init` | `db_store_inventory_settings` | Init code |
| `sales_payment_init` | `db_store_inventory_settings` | Init code |
| `sales_return_payment_init` | `db_store_inventory_settings` | Init code |
| `purchase_payment_init` | `db_store_inventory_settings` | Init code |
| `purchase_return_payment_init` | `db_store_inventory_settings` | Init code |
| `expense_payment_init` | `db_store_inventory_settings` | Init code |
| `purchase_code` | `db_store_inventory_settings` | Purchase code |
| `sms_status` | `db_store_notification_settings` | SMS enable |
| `sms_url` | `db_store_notification_settings` | SMS URL |
| `smtp_host` | `db_store_notification_settings` | SMTP host |
| `smtp_port` | `db_store_notification_settings` | SMTP port |
| `smtp_user` | `db_store_notification_settings` | SMTP user |
| `smtp_pass` | `db_store_notification_settings` | SMTP password |
| `smtp_status` | `db_store_notification_settings` | SMTP enable |
| `industry_type` | `db_store_industry_settings` | Industry preset |
| `business_model` | `db_store_industry_settings` | Business model |
| `workflow_template_key` | `db_store_industry_settings` | Workflow template |
| `dashboard_template_key` | `db_store_industry_settings` | Dashboard template |
| `storefront_theme_key` | `db_store_industry_settings` | Storefront theme |
| `feature_flags_json` | `db_store_industry_settings` | Feature flags |
| `label_overrides_json` | `db_store_industry_settings` | Label overrides |
| `industry_settings_json` | `db_store_industry_settings` | Industry JSON |
| `nin_api_enabled` | `db_store_settings` (key/value) | NIN API flag |
| `nin_api_url` | `db_store_settings` (key/value) | NIN API URL |
| `nin_api_key` | `db_store_settings` (key/value) | NIN API key |
| `nin_api_provider` | `db_store_settings` (key/value) | NIN provider |
| `nin_api_cost` | `db_store_settings` (key/value) | NIN API cost |
| `language_id` | `db_store_settings` (key/value) | Language preference |
| `current_subscriptionlist_id` | `db_store_settings` (key/value) | Subscription reference |
| `upi_id` | `db_store_payment_settings` | Legacy payment |
| `upi_code` | `db_store_payment_settings` | Legacy payment |
| `bank_details` | `db_store_payment_settings` | Bank details |
| `cid` | `db_store_settings` (key/value) | Legacy/unused |

## 5. New Architecture

### 5.1 Slim `db_store` (fresh installs)

```sql
CREATE TABLE `db_store` (
  `id` int(5) NOT NULL AUTO_INCREMENT,
  `store_code` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `store_name` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `mobile` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `country` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `state` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `city` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `postcode` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `location_lat` decimal(10,8) DEFAULT NULL,
  `location_lng` decimal(11,8) DEFAULT NULL,
  `currency_id` int(5) DEFAULT NULL,
  `currency_placement` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `timezone` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_format` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `time_format` int(5) DEFAULT NULL,
  `status` int(1) DEFAULT NULL,
  `user_id` int(5) NOT NULL,
  `created_date` date DEFAULT NULL,
  `created_time` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_by` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_ip` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `system_name` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `store_code` (`store_code`),
  KEY `status` (`status`)
) ENGINE=InnoDB ROW_FORMAT=DYNAMIC DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 5.2 Modular Tables

#### `db_store_settings` (flexible key/value)

```sql
CREATE TABLE `db_store_settings` (
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
```

#### `db_store_receipt_settings`

```sql
CREATE TABLE `db_store_receipt_settings` (
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
```

#### `db_store_pos_settings`

```sql
CREATE TABLE `db_store_pos_settings` (
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
```

#### `db_store_inventory_settings`

```sql
CREATE TABLE `db_store_inventory_settings` (
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
```

#### `db_store_storefront_settings`

```sql
CREATE TABLE `db_store_storefront_settings` (
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
```

#### `db_store_notification_settings`

```sql
CREATE TABLE `db_store_notification_settings` (
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
```

#### `db_store_theme_settings`

```sql
CREATE TABLE `db_store_theme_settings` (
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
```

#### `db_store_industry_settings`

```sql
CREATE TABLE `db_store_industry_settings` (
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
```

#### `db_store_tax_settings`

```sql
CREATE TABLE `db_store_tax_settings` (
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
```

#### `db_store_payment_settings`

```sql
CREATE TABLE `db_store_payment_settings` (
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
```

## 6. Migration Strategy

### 6.1 Migration File

Create `/Users/ralphmore/Herd/martpointretailapp/updates/migrations/4.0.2_to_4.0.3_db_store_modularization.sql` and mirror it in `/Users/ralphmore/Herd/martpointretailapp/release_build/migrations/`.

The migration must:

1. Create all modular tables with `IF NOT EXISTS`.
2. For each moved column, copy data from `db_store` to the new table using dynamic column detection (`information_schema.COLUMNS`) and `PREPARE/EXECUTE`.
3. Not fail if a column does not exist.
4. Be idempotent (use `INSERT ... ON DUPLICATE KEY UPDATE`).
5. Preserve old `db_store` columns in this first pass.
6. Use `ROW_FORMAT=DYNAMIC` on new tables.
7. Add a `db_schema_migrations` record so the migration is not re-run.

### 6.2 Example Idempotent Migration Pattern

```sql
SET FOREIGN_KEY_CHECKS = 0;
SET SESSION SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO,ALLOW_INVALID_DATES';

-- Helper procedure to copy a column if it exists
DELIMITER $$
CREATE PROCEDURE IF NOT EXISTS mp_copy_store_column(IN p_col VARCHAR(64), IN p_dest_table VARCHAR(64), IN p_dest_col VARCHAR(64))
BEGIN
  SET @col_exists = (SELECT 1 FROM information_schema.COLUMNS
                     WHERE TABLE_SCHEMA = DATABASE()
                       AND TABLE_NAME = 'db_store'
                       AND COLUMN_NAME = p_col);
  SET @dest_exists = (SELECT 1 FROM information_schema.TABLES
                      WHERE TABLE_SCHEMA = DATABASE()
                        AND TABLE_NAME = p_dest_table);
  IF @col_exists IS NOT NULL AND @dest_exists IS NOT NULL THEN
    SET @sql = CONCAT(
      'INSERT INTO ', p_dest_table, ' (store_id, ', p_dest_col, ') ',
      'SELECT id, `', p_col, '` FROM db_store WHERE `', p_col, '` IS NOT NULL ',
      'ON DUPLICATE KEY UPDATE ', p_dest_col, ' = VALUES(', p_dest_col, ')'
    );
    PREPARE stmt FROM @sql;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
  END IF;
END$$
DELIMITER ;

-- Create tables then call the procedure for each column mapping
-- (full mapping in the actual migration file)
```

### 6.3 Simpler Bulk Migration Strategy

Because most columns are mapped one-to-one, a more maintainable approach is a single SQL migration that:

1. Creates each new table with `CREATE TABLE IF NOT EXISTS`.
2. Uses `INSERT INTO ... SELECT ... ON DUPLICATE KEY UPDATE` guarded by column-existence checks.

Column-existence guard pattern:

```sql
SET @has_col = (SELECT 1 FROM information_schema.COLUMNS
                WHERE TABLE_SCHEMA = DATABASE()
                  AND TABLE_NAME = 'db_store'
                  AND COLUMN_NAME = 'sales_invoice_format_id');
SET @sql = IF(@has_col IS NOT NULL,
  'INSERT INTO db_store_receipt_settings (store_id, sales_invoice_format_id, ...)
   SELECT id, sales_invoice_format_id, ... FROM db_store
   ON DUPLICATE KEY UPDATE ...',
  'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
```

### 6.4 Rollback Plan

- The migration is read-only on `db_store` (old columns are preserved).
- If a new table causes issues, the old data still exists in `db_store`.
- Rollback: drop the new modular tables and revert the application code to read from `db_store` directly.
- A backup of `db_store` is taken by the migration using `CREATE TABLE IF NOT EXISTS db_store_backup_pre_modularization AS SELECT * FROM db_store`.

## 7. Application Compatibility

### 7.1 Central Service: `StoreSettingsService`

Create `/Users/ralphmore/Herd/martpointretailapp/application/libraries/StoreSettingsService.php` (or model) that exposes:

```php
public function get_setting($store_id, $group, $key, $default = null);
public function set_setting($store_id, $group, $key, $value, $type = 'string');
public function get_structured($store_id, $table, $default = []);
public function save_structured($store_id, $table, $data);
```

The service reads from the new modular tables first. For existing installations that still have data in `db_store`, it falls back to the old columns. This lets old hardcoded `db_store` reads continue to work while new code uses the modular tables.

### 7.2 Key Files to Update

| File | Change |
|------|--------|
| `application/models/Store_model.php` | `store_making_codes()` returns defaults for modular tables; `save_registration()` and `verify_and_save()` split data between `db_store` and modular tables; new store creation seeds all modular tables. |
| `application/models/Store_profile_model.php` | `update_store()` writes modular settings to new tables; reads fall back to `db_store`. |
| `application/models/Email_settings_model.php` | Stop writing `smtp_*` to `db_store`; write to `db_store_notification_settings` and `db_email_settings`. |
| `application/models/Sms_model.php` | Read `sms_status` from `db_store_notification_settings` with fallback to `db_store`. |
| `application/helpers/custom_helper.php` | Update `change_return_status()`, `get_invoice_format_id()`, `get_pos_invoice_format_id()`, `is_enabled_round_off()`, and similar helpers to read from modular tables with fallback. |
| `application/helpers/business_profile_helper.php` | Update `mp_get_store_profile()`, `mp_feature_flag_raw()`, `mp_label()` to read from `db_store_industry_settings` with fallback to `db_store` or `db_store_business_profile`. |
| `application/models/Business_profile_model.php` | Use `db_store_industry_settings` as the canonical table; keep fallback to `db_store_business_profile` and `db_store`. |
| `application/controllers/Business_profile.php` | No changes required if the model is updated. |
| `application/controllers/Store.php` / `Store_register.php` | Use the new service/models. |
| `application/controllers/Email_settings.php` | Already delegates to `Email_settings_model`; model change is sufficient. |
| `setup/install/includes/db.txt` | Replace the bloated `db_store` CREATE TABLE with the slim version. |
| `setup/install/includes/db_install_extensions.sql` | Add CREATE TABLE statements for all modular tables and seed defaults. |
| `updates/migrations/4.0.1_to_4.0.2.sql` | Add the new modular tables to the existing migration so upgrades from 4.0.1 create them. |
| `updates/migrations/4.0.2_to_4.0.3_db_store_modularization.sql` | New migration for existing databases. |
| `application/controllers/Updates.php` | Wire the new migration into the version upgrade path. |

### 7.3 Backward Compatibility

- Old columns remain in `db_store` for existing installations.
- New code reads from modular tables; if the row is missing, it falls back to `db_store`.
- `StoreSettingsService` provides a single point for both reads and writes so scattered code can be migrated incrementally.
- Fresh installations will not have old columns, so all core code paths must be updated before the slim `db_store` is used.

## 8. Risk Assessment

| Risk | Likelihood | Impact | Mitigation |
|------|-----------|--------|------------|
| Missing a `db_store` column reference in a view/helper | High | Medium | Keep old columns in `db_store` for existing installs; update the most-used helpers; test fresh install thoroughly. |
| Migration fails on a column that does not exist | Medium | High | Use dynamic `information_schema` checks in migration; never use `ALTER TABLE ... ADD COLUMN IF NOT EXISTS`. |
| Data loss during migration | Low | Critical | Backup `db_store` before migration; use `INSERT ... ON DUPLICATE KEY UPDATE`; never delete old columns. |
| Fresh install missing default settings | Medium | High | Seed all modular tables during install; verify with script. |
| Row-size errors still occur | Low | High | Use `ROW_FORMAT=DYNAMIC`; new `db_store` is tiny; each modular table is small. |
| Feature regression (business profile, POS, receipt) | Medium | High | Update models and helpers; run verification scripts for dashboard, POS, store profile, receipt, storefront. |

## 9. Verification Plan

Create `/Users/ralphmore/Herd/martpointretailapp/verify_db_store_modularization.php` that checks:

1. New tables exist (`db_store_settings`, `db_store_receipt_settings`, `db_store_pos_settings`, `db_store_inventory_settings`, `db_store_storefront_settings`, `db_store_notification_settings`, `db_store_industry_settings`, `db_store_theme_settings`, `db_store_tax_settings`, `db_store_payment_settings`).
2. `db_store` row size is below the InnoDB limit (count columns and approximate bytes).
3. Every store has a corresponding row in each required modular table (or at least the ones with defaults).
4. Old `db_store` data was copied (sample a few columns).
5. No missing-column errors by running a simulated load of store settings.
6. Application can load dashboard, POS, store profile, receipt settings, storefront settings, and industry settings.
7. For fresh installs, the slim `db_store` schema is correct and seeded.

## 10. Reporting

After implementation, create `DB_STORE_MODULARIZATION_REPORT.md` containing:

- Files changed
- Tables created
- Columns migrated
- Compatibility notes
- Verification results
- Remaining risks
