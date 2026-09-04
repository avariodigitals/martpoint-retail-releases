# MartPoint db_store Modularization Report

## Objective

Redesign the monolithic `db_store` table into feature-specific modular tables so that MartPoint can grow without hitting MySQL row-size limits, while keeping every existing feature intact for African business owners.

## What changed

### 1. Modular table schema

New tables were created for grouped settings:

| Table | Purpose |
|-------|---------|
| `db_store_inventory_settings` | Document init codes (category, item, purchase, sales, payments, etc.) |
| `db_store_receipt_settings` | Invoice formats, footer, terms, round-off, decimals, change-return |
| `db_store_pos_settings` | POS-specific toggles: discount, MRP column, signature, previous balance, default account |
| `db_store_notification_settings` | SMS status, SMTP settings, e-invoice status |
| `db_store_tax_settings` | GST, VAT, PAN, TIN numbers |
| `db_store_theme_settings` | Store logo, signature image, theme key |
| `db_store_storefront_settings` | Store website URL, storefront theme, SEO flags |
| `db_store_payment_settings` | Bank details, payment preferences |
| `db_store_industry_settings` | Industry type, business model, feature flags, templates, label overrides |
| `db_store_settings` | Key/value store for extension settings (NIN API, etc.) |

### 2. Migration for existing installations

- File: `updates/migrations/4.0.2_to_4.0.3_db_store_modularization.sql`
- Also copied to: `release_build/migrations/4.0.2_to_4.0.3_db_store_modularization.sql`
- Uses `IF NOT EXISTS` for idempotent runs.
- Uses a helper stored procedure `mp_copy_store_column` to copy each column only if it exists in the source `db_store`.
- Old `db_store` columns are **not dropped** so the application keeps working during the transition.

### 3. Fresh installer

- `setup/install/includes/db.txt` now creates a slim `db_store` with only core identity fields.
- `setup/install/includes/db_install_extensions.sql` creates the modular tables and seeds default rows for the first two stores.

### 4. Read compatibility helper

- `application/helpers/store_settings_helper.php` provides `mp_get_store_*_setting()` and `mp_get_store_settings()` functions.
- These read from the modular table first, then fall back to `db_store` columns, so existing installs and third-party code keep working.
- Autoloaded via `application/config/autoload.php`.

### 5. Application code updates

Files updated to read/write modular settings:

- `application/models/Email_settings_model.php` - SMTP settings now save to `db_store_notification_settings`.
- `application/models/Sms_model.php` - `sms_status` read from modular notification settings with fallback.
- `application/helpers/custom_helper.php` - `change_return_status`, invoice format helpers, round-off helper use modular receipt settings.
- `application/models/Business_profile_model.php` - reads/writes `db_store_industry_settings` first, then `db_store_business_profile`, then `db_store`.
- `application/helpers/business_profile_helper.php` - feature flags, store profile, and label overrides use modular industry settings first.

### 6. Store creation and profile update

`Store_model.php` and `Store_profile_model.php` are the two places that write the largest number of settings to `db_store`. They were prepared to be patched by the helper script:

- `apply_store_modular_patch_v2.py`

Running that script will:

- Trim `Store_model::store_making_codes()` to core identity only.
- Add `store_modular_defaults()` and `seed_modular_settings()` to seed the new tables.
- Update `save_registration()` to insert only core fields into `db_store` and then seed modular tables.
- Update `Store_profile_model::update_store()` to write only core fields to `db_store` and upsert modular tables.

## How to apply

### Fresh install

1. Run the installer as normal; the slim `db_store` and modular tables will be created automatically.
2. Run the patch to update the two PHP models:

```bash
python3 /Users/ralphmore/Herd/martpointretailapp/apply_store_modular_patch_v2.py
```

3. Register the first store. `save_registration()` will seed the modular tables with defaults.

### Existing install

1. Backup the database.
2. Run the migration:

```bash
mysql -u user -p database_name < updates/migrations/4.0.2_to_4.0.3_db_store_modularization.sql
```

3. Run the PHP model patch:

```bash
python3 /Users/ralphmore/Herd/martpointretailapp/apply_store_modular_patch_v2.py
```

4. Verify with:

```bash
php /Users/ralphmore/Herd/martpointretailapp/verify_db_store_modularization.php
```

## Verification

- `verify_db_store_modularization.php` checks that all modular tables exist, that seed rows exist for each store, and that the migration copied data for store 1 when legacy columns are present.

## Constraints respected

- No business features removed.
- No old `db_store` columns deleted.
- No active columns renamed without compatibility handling.
- No new feature-specific columns added to `db_store`.

## Risk assessment

| Risk | Mitigation |
|------|------------|
| Legacy install without modular tables | Helper functions fall back to `db_store` columns |
| Migration fails on custom schema | Procedure checks column existence before copying |
| Fresh install fails if model patch not applied | Patch script is documented and required in install steps |
| Third-party code reads old columns | Old columns remain present; helper functions provide fallback |

## Next steps

1. Run the PHP model patch (`apply_store_modular_patch_v2.py`) to apply the changes to `Store_model.php` and `Store_profile_model.php`.
2. Run the migration on a staging copy of a legacy database.
3. Run the verification script.
4. Update the admin store-settings UI to load/save modular fields individually if needed.

---
Generated during the db_store modularization task.
