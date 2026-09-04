# MartPoint Runtime Schema Removal Report

## Objective
Eliminate all runtime `CREATE TABLE IF NOT EXISTS` and runtime `ALTER TABLE` schema creation from MartPoint application code so that the fresh installer and versioned migrations are the single source of truth for schema.

## Changes Made

### 1. Migration orchestration
- Updated `application/models/Updates_model.php` to run both `4.0.0_to_4.0.1_purchase_batch.sql` and `4.0.1_to_4.0.2.sql` automatically before login when the installed database version is below the target version.
- Added `_run_sql_file()` to execute migrations via `mysqli_multi_query`.

### 2. Consolidated migration (`4.0.1_to_4.0.2.sql`)
Created / updated the authoritative migration in both `updates/migrations/` and `release_build/migrations/` to create every table that was previously created at runtime, including:
- Storefront tables
- Delivery tables
- Membership / customer packages
- Custom orders
- Treatment notes
- Email / report tables
- Debt reminders
- Attendance and shifts
- Recipe and production-batch tables
- Service packages and redemptions
- Laundry orders
- Subscription / license / OTP / history tables
- Brevo table

Also added idempotent `ALTER TABLE ... ADD COLUMN IF NOT EXISTS` for columns that had been added at runtime:
- `db_items`: `accept_custom_order`, `custom_order_fields_json`, `requires_quote`, `requires_deposit`, `workflow_template_key`, `recipe_id`, `recipe_margin_pct`
- `db_services`: `industry_fields_json`
- `db_store`: `industry_type`, `business_model`, `feature_flags_json`, `workflow_template_key`, `dashboard_template_key`, `storefront_theme_key`, `label_overrides_json`, `industry_settings_json`
- `db_subscription_license`: `installation_fingerprint`

### 3. Installer schema updated
- `setup/install/includes/db.txt`:
  - Added custom-order and recipe columns to `db_items`.
  - Added industry columns to `db_store`.
- `setup/install/includes/db_install_extensions.sql`:
  - Added `industry_fields_json` to `db_services`.
  - Added `installation_fingerprint` to `db_subscription_license`.

### 4. Runtime schema creation removed from application code
The following models/controllers now only log a missing-table error and instruct the user to run the 4.0.2 migration via login, instead of creating tables at runtime:
- `application/models/Production_batches_model.php`
- `application/models/Email_template_model.php`
- `application/models/Email_settings_model.php`
- `application/models/Email_log_model.php`
- `application/models/Delivery_model.php`
- `application/models/Custom_orders_model.php`
- `application/models/Debt_reminder_model.php`
- `application/models/Report_schedule_model.php`
- `application/models/Treatment_notes_model.php`
- `application/models/Membership_model.php`
- `application/models/Attendance_model.php`
- `application/models/Service_package_model.php`
- `application/models/Storefront_model.php`
- `application/models/Recipe_model.php`
- `application/controllers/Operations.php` (laundry tables)

The redundant `CREATE TABLE IF NOT EXISTS` statements were also removed from `application/controllers/Updates.php` for:
- `db_subscription_license`
- `db_license_otps`
- `db_license_history`
- `db_brevo`
- `db_recipe_categories`

## Verification
- `grep` of `CREATE TABLE IF NOT EXISTS` in `application/` returns no matches.
- Fresh installer and upgrade migrations now contain the full schema for all tables that were previously created at runtime.
- The auto-migration runner is triggered before login in `MY_Controller::update_db()`, so missing tables are created before normal business operations run.

## Remaining Work
- Low-severity `ALTER TABLE` findings (e.g. `AUTO_INCREMENT = 1` and version-specific upgrade controller changes) remain in `Updates.php` and some models, but these are not runtime schema creation during normal operation and are out of scope for this pass.
- Run the production-readiness audit after the next fresh install / upgrade to confirm the medium finding is resolved.
