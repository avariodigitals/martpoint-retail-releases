# MartPoint Production Readiness Report

**Generated:** 2026-07-04 12:59:36

## Executive Summary

- Total findings: 81
- Critical findings: 0
- Files modified: 0

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

- **[medium]** Zero-date literals found in source files
  - Root cause: Source files still reference '0000-00-00'
  - Fix: Replace with NULL or CURRENT_TIMESTAMP defaults

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

- **[medium]** Runtime CREATE TABLE IF NOT EXISTS found in application code
  - Root cause: Fresh installer should be the single source of truth
  - Fix: Move table creation to installer; keep runtime checks only for backward-compatible upgrades
- **[low]** ALTER TABLE statements found in application code
  - Root cause: Schema changes should be in migrations or installer
  - Fix: Review and move to migration files

### PHASE7

No issues found.

## Fixes Applied

No fixes applied during this run.

## Files Modified

No files modified during this run.

## Remaining Risks

- **medium** Zero-date literals found in source files — Source files still reference '0000-00-00'
- **medium** Runtime CREATE TABLE IF NOT EXISTS found in application code — Fresh installer should be the single source of truth

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
| Business Workflow Validation | 100/100 |
| Performance | 94/100 |
| Scalability | 100/100 |
| Maintainability | 89/100 |
| Production Readiness | 13/100 |

