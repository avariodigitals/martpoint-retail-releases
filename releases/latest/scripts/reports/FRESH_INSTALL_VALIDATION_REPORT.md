# MartPoint Fresh Install Validation Report

- **Date:** 2026-07-04 12:00:34
- **Database tested:** `martpoint_fresh_validate_20260704_120029`
- **Tables created:** 154
- **Installer result:** SUCCESS

## Schema files loaded
- `setup/install/includes/db.txt`
- `setup/install/includes/db_install_extensions.sql`
- `setup/install/includes/db_models_schema_part2.sql`
- `setup/install/includes/db_models_schema_part3.sql`

## Errors found
- No unhandled errors in the final run.

## Errors found during validation (all fixed before final run)
- POS / Sales and Inventory / Items: `Unknown column 'default_warehouse_id' in 'field list'` on `db_users`.
- Storefront: `Unknown column 'a.category_image' in 'field list'` on `db_category`, followed by `Incorrect DATE value: '0000-00-00'` (strict SQL mode on runtime connections).

## Fixes applied
- Added `default_warehouse_id` column to `db_users` in `setup/install/includes/db.txt` (POS/Inventory default warehouse lookup).
- Added `category_image` column to `db_category` in `setup/install/includes/db.txt` (Storefront categories).
- Enabled CodeIgniter hooks in `setup/install/includes/config_file.php` and added `mp_set_sql_mode` hook in `application/config/hooks.php` to set `ALLOW_INVALID_DATES` SQL mode, matching the installer session and preventing "Incorrect DATE value: 0000-00-00" errors.

## Admin user creation
- Username: storeadm
- Password set for verification: Quarter25ile
- Login result: SUCCESS

## Dashboard
- Result: OK

## Modules tested
- **POS / Sales:** HTTP 200, DB error: NO, OK: YES
- **Inventory / Items:** HTTP 200, DB error: NO, OK: YES
- **Customers:** HTTP 200, DB error: NO, OK: YES
- **Storefront:** HTTP 200, DB error: NO, OK: YES
- **Services:** HTTP 200, DB error: NO, OK: YES
- **Packages:** HTTP 200, DB error: NO, OK: YES
- **Loyalty:** HTTP 200, DB error: NO, OK: YES
- **Offline purchase queue:** HTTP 200, DB error: NO, OK: YES
- **Subscription/license:** HTTP 200, DB error: NO, OK: YES
- **Updates:** HTTP 200, DB error: NO, OK: YES

## Final verdict
**PASS**