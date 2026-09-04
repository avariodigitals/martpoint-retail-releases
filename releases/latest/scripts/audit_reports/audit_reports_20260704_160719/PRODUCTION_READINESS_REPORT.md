# MartPoint Production Readiness Report

**Generated:** 2026-07-04 16:07:21

## Executive Summary

- Total findings: 79
- Critical findings: 0
- Files modified: 25

## Phase-by-Phase Findings

### PHASE1

No issues found.

### PHASE2

No issues found.

### PHASE3

- **[low]** Date column nullable without default (`ac_accounts`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`ac_moneydeposits`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`ac_moneytransfer`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`ac_transactions`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Table not using utf8mb4 collation (`db_bankdetails`)
  - Root cause: Mixed collation can cause join errors
  - Fix: Consider converting to utf8mb4_unicode_ci
- **[low]** Date column nullable without default (`db_cobpayments`.payment_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_cobpayments`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_coupons`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_custadvance`.payment_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_custadvance`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_customer_coupons`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_customer_packages`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_customer_payments`.payment_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_customer_payments`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_customers`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_delivery_drivers`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_delivery_schedules`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_expense`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_expiry_settings`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_gift_card_usage`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_gift_cards`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_hold`.sales_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_installment_payments`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_installment_plans`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_item_barcodes`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_items`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Table not using utf8mb4 collation (`db_laundry_orders`)
  - Root cause: Mixed collation can cause join errors
  - Fix: Consider converting to utf8mb4_unicode_ci
- **[low]** Date column nullable without default (`db_license_limit_overrides`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_loyalty_bonus_rules`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_loyalty_points`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_loyalty_product_points`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_loyalty_settings`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_loyalty_tiers`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_offline_purchase_queue`.purchase_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_offline_purchase_queue`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_package`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_payment_modes`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_paystack_settings`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_purchase`.purchase_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_purchase`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_purchasepayments`.payment_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_purchasepayments`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_purchasepaymentsreturn`.payment_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_purchasepaymentsreturn`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_purchasereturn`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_quotation`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_referrals`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_rewards_history`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_sales`.sales_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_sales`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_salespayments`.payment_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_salespayments`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_salespaymentsreturn`.payment_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_salespaymentsreturn`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_salesreturn`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_service_packages`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_sobpayments`.payment_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_sobpayments`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_stockadjustment`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_stocktransfer`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_store`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_store_credit`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_store_credit_usage`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_subscription`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_subscription_license`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_subscription_plans`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_supplier_payments`.payment_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_supplier_payments`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`db_suppliers`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Table not using utf8mb4 collation (`db_tables`)
  - Root cause: Mixed collation can cause join errors
  - Fix: Consider converting to utf8mb4_unicode_ci
- **[low]** Date column nullable without default (`db_users`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling
- **[low]** Date column nullable without default (`temp_holdinvoice`.created_date)
  - Root cause: Could allow NULL where business expects a date
  - Fix: Consider NOT NULL DEFAULT CURRENT_TIMESTAMP or proper nullable handling

### PHASE4

No issues found.

### PHASE5

- **[low]** Table exceeds 50 columns (`db_approval_settings`)
  - Root cause: Wide tables reduce performance and maintainability
  - Fix: Normalize rarely used columns into related tables
- **[low]** Table exceeds 50 columns (`db_customers`)
  - Root cause: Wide tables reduce performance and maintainability
  - Fix: Normalize rarely used columns into related tables
- **[low]** Table exceeds 50 columns (`db_items`)
  - Root cause: Wide tables reduce performance and maintainability
  - Fix: Normalize rarely used columns into related tables
- **[low]** Table exceeds 50 columns (`db_store`)
  - Root cause: Wide tables reduce performance and maintainability
  - Fix: Normalize rarely used columns into related tables
- **[low]** Table exceeds 50 columns (`db_storefront_settings`)
  - Root cause: Wide tables reduce performance and maintainability
  - Fix: Normalize rarely used columns into related tables
- **[low]** Potential N+1 query patterns in PHP code
  - Root cause: Loops contain database queries
  - Fix: Refactor to batch queries or use joins

### PHASE6

- **[low]** ALTER TABLE statements found in application code
  - Root cause: Schema changes should be in migrations or installer
  - Fix: Review and move to migration files

### PHASE7

No issues found.

## Fixes Applied

- Made purchase batch migration (4.0.0 -> 4.0.1) idempotent by checking column existence before ALTER TABLE
- Added primary keys to ci_sessions, db_company, and db_shippingaddress in installer schema
- Created is_valid_date() helper and replaced legacy 0000-00-00 literal checks across all models, controllers, helpers, and views
- Applied ALTER TABLE fixes to current martpoint database for missing primary keys and legacy zero-date rows
- Simplified stress test to use minimal prefixed tables and avoid cross-database permission issues

## Files Modified

- application/helpers/custom_helper.php
- application/models/Purchase_model.php
- application/models/Sales_model.php
- application/controllers/Items.php
- application/models/Pos_model.php
- application/models/Items_model.php
- application/models/Assist_model.php
- application/models/Service_package_model.php
- application/models/Storefront_model.php
- application/models/Services_model.php
- application/models/Delivery_model.php
- application/models/Laundry_model.php
- application/helpers/inventory_helper.php
- application/controllers/Import.php
- application/controllers/Operations.php
- application/views/operations/laundry.php
- application/views/operations/driver_profile.php
- application/views/operations/warranty_lookup.php
- application/views/expired_items_report.php
- application/views/customer-packages.php
- setup/install/includes/db.txt
- updates/migrations/4.0.0_to_4.0.1_purchase_batch.sql
- release_build/migrations/4.0.0_to_4.0.1_purchase_batch.sql
- production_readiness_audit.php
- fix_audit_issues.php

## Remaining Risks


## Recommendations

- Address all high-severity findings before production.
- Migrate runtime CREATE TABLE statements to the installer.
- Replace zero-date values with NULL or explicit defaults.
- Add composite indexes for high-volume join/aggregate queries.
- Schedule regular integrity and performance audits.

## Production Readiness Score

Score calculation is based on the ratio of resolved vs open findings.

| Category | Score |
|----------|-------|
| Fresh Installation | 100/100 |
| Upgrade Safety | 100/100 |
| Database Integrity | 28/100 |
| Legacy Date Cleanup | 100/100 |
| Business Workflow Validation | 100/100 |
| Performance | 94/100 |
| Scalability | 100/100 |
| Maintainability | 99/100 |
| Production Readiness | 87/100 |

