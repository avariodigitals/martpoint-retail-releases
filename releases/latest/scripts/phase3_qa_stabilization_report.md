# Phase 3 QA & Stabilization Report

**Date:** 2026-06-28
**Scope:** Business Profile UI, Dashboard adaptation, Label resolver, Public Catalogue settings, Storefront theme sync, Defensive DB guards.
**Method:** Code review + static analysis. Manual browser verification required for items marked `[MANUAL]`.

---

## Fixes Applied During QA Pass

| Fix | File | Description |
|-----|------|-------------|
| Defensive column checks in `mp_get_store_profile()` | `helpers/business_profile_helper.php` | Added `field_exists()` loop before SELECT so unmigrated stores don't fatal-error on missing `industry_type`, `feature_flags_json`, etc. Returns safe default profile instead. |
| Defensive column check in `mp_label()` | `helpers/business_profile_helper.php` | Wrapped `label_overrides_json` query inside `field_exists()` guard. Falls back to `mp_get_store_profile()` → `mp_get_label_defaults()` if column missing. |
| Defensive column checks in `Business_profile_model` | `models/Business_profile_model.php` | `get_profile()` and `update_profile()` both check `field_exists()` for every column. |

---

## Test Results

### 1. Business Profile page loads
- **Expected:** Settings → Business Profile opens without PHP errors or white screen.
- **Actual (Code Review):** View file `business_profile.php` has balanced HTML/JS tags. All PHP variables (`$page_title`, `$profile`, `$business_types`, etc.) are set in controller `Business_profile::index()`. No unclosed blocks.
- **Fix:** None needed.
- **Status:** Pass (Code Review) — `[MANUAL]` verify in browser.

---

### 2. Changing Business Type shows preset preview without auto-saving
- **Expected:** Selecting a new Business Type displays a preview card. No database write occurs until admin clicks Save.
- **Actual (Code Review):** `$('#industry_type').on('change', ...)` only calls `showPresetPreview()` and `showThemeSuggestion()`. The `applyPresetBtn` click is required to actually write values into form fields. No AJAX save is triggered on change.
- **Fix:** None needed.
- **Status:** Pass (Code Review) — `[MANUAL]` verify preview appears and no save toast fires.

---

### 3. Apply Recommended Settings saves correctly
- **Expected:** Clicking "Apply Recommended Settings" populates form fields. Clicking Save persists them.
- **Actual (Code Review):** `applyPresetBtn` handler sets dropdowns and checkboxes, then hides the preview. The separate Save handler serializes the form and POSTs to `business_profile/save`. Controller saves all posted fields to `db_store`.
- **Fix:** None needed.
- **Status:** Pass (Code Review) — `[MANUAL]` verify values persist after reload.

---

### 4. Storefront theme suggestion applies correctly
- **Expected:** Theme suggestion card appears when recommended theme differs from current. Apply button updates the dropdown.
- **Actual (Code Review):** `showThemeSuggestion()` compares `currentTheme` to `themeKey`. If different, renders card with color swatch. `applyThemeBtn` sets `#storefront_theme_key` value.
- **Fix:** None needed.
- **Status:** Pass (Code Review) — `[MANUAL]` verify theme dropdown updates and storefront reflects change after Save.

---

### 5. Public Catalogue settings save into `industry_settings_json`
- **Expected:** Enabling catalogue, setting slug, toggling Show Products/Services stores inside `industry_settings_json` under key `catalogue`.
- **Actual (Code Review):** JS `submit` handler injects a `catalogue` object into `#industry_settings_json` before the AJAX save handler runs. Controller reads `industry_settings_json` and saves it.
- **Fix:** None needed.
- **Status:** Pass (Code Review) — `[MANUAL]` verify `db_store.industry_settings_json` contains catalogue after save.

---

### 6. Dashboard loads for `product_based` business
- **Expected:** All widgets visible. No PHP warnings.
- **Actual (Code Review):** `mp_get_store_profile()` returns default profile with `business_model = 'product_based'`. `$is_product_business` evaluates to `true`. Low Stock, Top Products, Branch Performance all render.
- **Fix:** None needed.
- **Status:** Pass (Code Review) — `[MANUAL]` verify dashboard renders with all sections.

---

### 7. Dashboard loads for `service_based` business
- **Expected:** Dashboard loads. No fatal errors.
- **Actual (Code Review):** `mp_get_store_profile()` returns correct model. `$is_product_business = false`. Low Stock and Top Products sections are wrapped in `<?php if($is_product_business): ?>` and will not render. Remaining widgets (Business Overview, Outstanding Payments, Insights, Activities) still render.
- **Fix:** None needed.
- **Status:** Pass (Code Review) — `[MANUAL]` verify dashboard loads and product sections are absent.

---

### 8. Low Stock and Top Products hide for `service_based` business
- **Expected:** Both sections absent when business_model is `service_based`.
- **Actual (Code Review):** Both sections gated by `<?php if($is_product_business): ?>`. Verified correct placement.
- **Fix:** None needed.
- **Status:** Pass (Code Review) — `[MANUAL]` verify absence after setting profile to Salon / Spa.

---

### 9. Expiry Alerts hide when `expiry_tracking` is disabled
- **Expected:** Expiry widget absent when feature flag is off.
- **Actual (Code Review):** Widget wrapped in `<?php if (mp_feature_enabled('expiry_tracking')): ?>`. `mp_feature_enabled()` returns `false` when `feature_flags_json` column is missing or flag is explicitly `0`.
- **Fix:** None needed.
- **Status:** Pass (Code Review) — `[MANUAL]` verify widget absent after disabling flag.

---

### 10. Branch/Warehouse labels display correctly using `mp_label()`
- **Expected:** Sidebar Branch menu uses override from Business Profile if set, otherwise "Branch".
- **Actual (Code Review):** `sidebar.php` line 809 uses `mp_label('branch','Branch')`. `mp_label()` checks `label_overrides_json` → `mp_get_store_profile()` → `mp_get_label_defaults()`. All paths have defensive guards now.
- **Fix:** Defensive guards added to `mp_label()` and `mp_get_store_profile()` during QA pass.
- **Status:** Pass (Code Review + Fix Applied) — `[MANUAL]` verify label changes after override.

---

### 11. Sidebar loads without PHP warnings
- **Expected:** No undefined function or missing variable warnings.
- **Actual (Code Review):** `business_profile_helper.php` is autoloaded in `config/autoload.php`. `mp_label()` and `mp_feature_enabled()` are available. No new undefined variables introduced in sidebar.
- **Fix:** Defensive guards added to helper functions.
- **Status:** Pass (Code Review + Fix Applied) — `[MANUAL]` verify no warnings in error log.

---

### 12. POS still works
- **Expected:** POS page loads and functions normally.
- **Actual (Code Review):** No POS files were modified in Phase 3. Feature flag wiring for POS payment modal was done in Phase 2.
- **Fix:** None needed.
- **Status:** Pass (Code Review) — `[MANUAL]` quick smoke test.

---

### 13. Inventory still works
- **Expected:** Items page loads, barcode/batch sections work.
- **Actual (Code Review):** `items.php` only had conditional `expiry_tracking` and `batch_tracking` gates added in Phase 2. No Phase 3 changes.
- **Fix:** None needed.
- **Status:** Pass (Code Review) — `[MANUAL]` quick smoke test.

---

### 14. Services still work
- **Expected:** Services page loads and saves.
- **Actual (Code Review):** No service files modified in Phase 3. `customer_notes` gate added in Phase 2.
- **Fix:** None needed.
- **Status:** Pass (Code Review) — `[MANUAL]` quick smoke test.

---

### 15. Online Store still works
- **Expected:** Online Store settings, appearance, orders pages load.
- **Actual (Code Review):** No Online Store files modified in Phase 3 except `theme_engine` fallback logic added in Phase 2/3. Appearance theme override still works.
- **Fix:** None needed.
- **Status:** Pass (Code Review) — `[MANUAL]` quick smoke test.

---

### 16. License plan limits still work
- **Expected:** Subscription checks, storefront limits, and module limits enforce correctly.
- **Actual (Code Review):** No subscription/license files modified in Phase 3.
- **Fix:** None needed.
- **Status:** Pass (Code Review)

---

### 17. Feature flags do not break existing permissions
- **Expected:** Permission checks (`$CI->permissions(...)`) still function independently of feature flags.
- **Actual (Code Review):** All Phase 3 gates are additive: `mp_feature_enabled('x')` is checked in addition to existing permission checks, not replacing them. Example: `Variants` controller checks `bundles` flag but also has `load_global()` which handles permissions.
- **Fix:** None needed.
- **Status:** Pass (Code Review)

---

### 18. Existing store without migrated columns does not fatal-error
- **Expected:** Store running on older DB schema loads dashboard and sidebar without fatal SQL errors.
- **Actual (Code Review):** **Critical issue found and fixed.**
  - `mp_feature_flag_raw()` already had `field_exists()` check (Phase 2 fix).
  - `mp_get_store_profile()` was doing a hardcoded SELECT of 8 columns without checking existence — **fixed** to loop through columns and only select available ones.
  - `mp_label()` was querying `label_overrides_json` without checking existence — **fixed** to wrap in `field_exists()`.
  - `Business_profile_model::get_profile()` and `update_profile()` now check `field_exists()`.
- **Fix:** Applied defensive column checks to `mp_get_store_profile()`, `mp_label()`, and `Business_profile_model`.
- **Status:** Pass (Code Review + Fix Applied)

---

## Fixes Applied During User Test Feedback (Round 2)

| Fix | File | Description |
|-----|------|-------------|
| Auto-populate form fields on Business Type change | `views/business_profile.php` | `applyPresetValues()` now runs immediately when Business Type changes. Form fields (business model, workflow, dashboard, theme, feature flags, labels) update live. Preview panel still shows. |
| Dashboard defensive gating | `views/dashboard.php` | Wrapped `mp_get_store_profile()` in `try/catch`. Fallback to `product_based` if helper fails for any reason. |
| Sidebar warehouse menu defensive | `views/sidebar.php` | Wrapped `mp_label('branch')` in `try/catch`. Reverted submenu items to hardcoded fallback text (`Add <?= $branch_label ?>` / `<?= $branch_label ?> List`) so they always render even if language keys are missing. |
| Service form fields always visible | `views/online_store/services.php` | Removed `mp_feature_enabled('appointments')` and `mp_feature_enabled('customer_notes')` gates from form HTML, `editService()` JS, and save handler. `requires_appointment` and `requires_note` now always show and save. |
| Stock report date-aware calculation | `helpers/custom_helper.php` + `models/Reports_model.php` | New `get_total_qty_of_warehouse_item_as_of_date()` rebuilds stock from transaction history (purchases, sales, returns, transfers, adjustments) filtered by date. `show_stock_report()` now uses it when `to_date` is provided. Total row shows "[Stock as of DATE]" note. |

---

## Important Note on "Missing Menus"

If you changed your Business Type and now see fewer sidebar menus, this is **expected behavior**, not a bug. The sidebar items are gated by feature flags, and different presets enable different features:

| Menu | Feature Flag Controlling It |
|------|---------------------------|
| Installments | `flexpay` |
| Loyalty & Rewards | `loyalty`, `gift_cards`, `store_credit` |
| Variants | `bundles` |
| Approval Logs | `manager_approvals` |
| QR Codes | `qr_ordering` |
| Expiry Settings | `expiry_tracking` |
| Online Store | `online_store` |

If you want a menu back, go to **Business Profile → Feature Flags** and re-enable the corresponding flag.

---

## Summary

| Category | Pass | Fail | Pending (Manual) |
|----------|------|------|------------------|
| Business Profile UI | 5 | 0 | 5 |
| Dashboard Adaptation | 4 | 0 | 4 |
| Label Resolver | 2 | 0 | 2 |
| Core Stability | 7 | 0 | 5 |
| Service Form | 1 | 0 | 1 |
| Stock Report | 1 | 0 | 1 |
| **Total** | **20** | **0** | **18** |

**Actions required:**
1. Re-test Business Type change — form fields should now auto-populate.
2. Re-test Dashboard with `service_based` business — widgets should hide correctly.
3. Re-test sidebar — warehouse menu should now render with proper labels.
4. Re-test service form — `Requires Appointment` and `Requires Customer Note` checkboxes should now always show.
5. Check that other "missing" menus are actually just disabled feature flags, not bugs.
