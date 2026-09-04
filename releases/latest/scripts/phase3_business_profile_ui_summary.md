# Phase 3: Business Profile UI & Adaptation Summary

## Overview
Phase 3 focused on polishing the Business Profile experience, adding safe UI adaptation foundations, and integrating the label resolver in high-visibility areas. No full feature engines were built.

---

## 1. Business Profile UI Polish

### File: `application/views/business_profile.php`

**Changes:**
- Added **Preset Preview Panel** that appears when Business Type changes.
  - Shows: Recommended Features (tag pills), Recommended Theme (swatch + name), Templates (workflow + dashboard).
  - Does **NOT** auto-apply. Admin must click **"Apply Recommended Settings"**.
  - Includes **Dismiss** button to hide preview.
- Added **Theme Suggestion Card** next to Storefront Theme dropdown.
  - Shows recommended theme with color swatch.
  - **"Apply"** button sets the dropdown without touching other fields.
- Updated help text: "Changing this shows a recommended preset. Click Apply Recommended to use it."
- Added CSS animations and improved visual hierarchy.

**Tabs:**
1. **Business Identity** — Business Type, Model, Workflow Template, Dashboard Template, Storefront Theme
2. **Feature Flags** — Grouped by category (Sales & Storefront, Products & Inventory, Services & Appointments, Workflows & Operations, Management)
3. **Templates & Labels** — Public Catalogue settings + Label Overrides
4. **Advanced** — Industry Settings JSON

### File: `application/controllers/Business_profile.php`

**Changes:**
- Save method already handles `industry_settings_json`.
- Theme sync logic preserved (only syncs `storefront_theme_key` → `theme_id` when `theme_id` is empty).

---

## 2. Storefront Theme Auto-Suggestion

### File: `application/views/business_profile.php`

**Behavior:**
- When Business Type changes, AJAX fetches preset → extracts `theme_key`.
- If current theme selection differs from recommended, a suggestion card appears with:
  - Color swatch (mapped from hardcoded JS palette)
  - Theme name
  - "Apply" button

**Theme Color Mapping (JS):**
```
general_retail → #3B82F6
fresh_market   → #2E7D32
healthcare_pro → #005EB8
food_express   → #D32F2F
tech_hub       → #0A2540
urban_fashion  → #111111
beauty_luxe    → #F8A4C8
service_pro    → #1A237E
```

**Industry ↔ Theme Mapping (from presets):**
- beauty_spa, beauty_cosmetics, salon_barbershop, makeup_artist → `beauty_luxe`
- pharmacy → `healthcare_pro`
- restaurant → `food_express`
- electronics → `tech_hub`
- fashion → `urban_fashion`
- supermarket, mini_mart → `fresh_market` (grocery)
- laundry, service_business → `service_pro`
- general_retail (default) → `general_retail`

---

## 3. Dashboard Widget Adaptation Foundation

### File: `application/views/dashboard.php`

**Gated Sections:**
| Section | Gate Condition | Reason |
|---------|---------------|--------|
| Low Stock Alert | `$is_product_business` | Only relevant for product-based or hybrid businesses |
| Top Selling Products | `$is_product_business` | Only relevant for product-based or hybrid businesses |
| Expiry Alerts | `mp_feature_enabled('expiry_tracking')` | Only show when feature flag is enabled |
| Branch Performance | `warehouse_module() && warehouse_count() > 1` | Existing gate preserved |

**Business Model Detection:**
```php
$bp_profile = mp_get_store_profile();
$is_product_business = empty($bp_profile['business_model']) || 
                       in_array($bp_profile['business_model'], ['product_based','product_and_service']);
```

**Service-based businesses** (salon, spa, makeup artist, laundry) will see:
- Business Overview KPIs
- Outstanding Payments
- MartPoint Insights
- Recent Activities
- Recent Sales (if applicable)
- But **NOT** Low Stock Alerts or Top Selling Products

---

## 4. Label Resolver Integration

### File: `application/helpers/business_profile_helper.php`

**Existing Function:** `mp_label($key, $fallback = null)`
- Reads `label_overrides_json` from `db_store`
- Falls back to `mp_get_label_defaults()`
- Falls back to ucwords of key

**Priority Labels in `mp_get_label_defaults()`:**
- `warehouse` → `Warehouse`
- `branch` → `Branch`
- `item` → `Item`
- `product` → `Product`
- `service` → `Service`
- `customer` → `Customer`
- `client` → `Client`
- (and 30+ more)

### Wired Locations:

**1. Sidebar (`application/views/sidebar.php`)**
- Branch/Warehouse menu tree title: `mp_label('branch','Branch')`

**2. Dashboard (`application/views/dashboard.php`)**
- Branch Performance heading: `mp_label('branch','Branch') . ' Performance'`
- Outstanding Debts KPI sub-label: `mp_label('customer','Customer')`

---

## 5. Public Catalogue Settings Foundation

### File: `application/views/business_profile.php`

**New UI Section** (in Templates & Labels tab):
- **Enable Public Catalogue** — checkbox
- **Slug / URL** — text input (default: `catalogue`)
- **Show Products** — checkbox (default: checked)
- **Show Services** — checkbox (default: checked)

**Storage:**
- Saved inside `industry_settings_json` under the `catalogue` key:
  ```json
  {
    "catalogue": {
      "enabled": 1,
      "slug": "catalogue",
      "show_products": 1,
      "show_services": 1
    }
  }
  ```
- JavaScript pre-submit hook injects these values into `#industry_settings_json` before AJAX save.

**Usage:**
- The Public Catalogue feature flag (`public_catalogue`) must also be enabled in Feature Flags for the catalogue to be active.
- Future Phase: Storefront controller can read `industry_settings_json` → decode → check `catalogue.enabled`.

---

## 6. Pending Feature Flags Handling

The following flags remain **foundation-only** (stored in presets, gated where UI exists, but no full engine built):

- `custom_orders` — flag exists, no full engine
- `packages` — flag exists, no full engine
- `memberships` — flag exists, no full engine
- `kitchen_workflow` — flag exists, no full engine
- `laundry_workflow` — flag exists, no full engine
- `treatment_notes` — flag exists, no full engine
- `staff_assignment` — flag exists, no full engine
- `staff_commission` — flag exists, no full engine
- `production_workflow` — flag exists, no full engine
- `recipe_tracking` — flag exists, no full engine
- `serial_number_tracking` — flag exists, no full engine
- `imei_tracking` — flag exists, no full engine
- `warranty_tracking` — flag exists, no full engine
- `price_catalogue` — flag exists, no full engine
- `public_catalogue` — flag exists + settings UI, no full engine

They are visible in Business Profile presets but do not expose broken menus.

---

## Files Touched

| File | Changes |
|------|---------|
| `application/views/business_profile.php` | Major UI polish: preset preview, theme suggestion, public catalogue settings, enhanced CSS |
| `application/views/dashboard.php` | Widget gating: product-based checks, expiry_tracking check, mp_label() integration |
| `application/views/sidebar.php` | mp_label('branch') on warehouse menu |
| `application/helpers/business_profile_helper.php` | Already had mp_label() — no changes needed |
| `application/controllers/Business_profile.php` | Theme sync logic preserved |

---

## Test Checklist

1. **Business Profile Preset Preview:**
   - [ ] Change Business Type → preset preview panel appears
   - [ ] Preview shows correct features, theme, templates
   - [ ] Click "Apply Recommended Settings" → fields update
   - [ ] Click "Dismiss" → panel hides
   - [ ] Click "Save" → settings persist

2. **Theme Suggestion:**
   - [ ] Change Business Type → theme suggestion card appears (if different from current)
   - [ ] Click "Apply" → storefront_theme_key dropdown updates
   - [ ] Save → theme syncs to db_storefront_settings (if theme_id was empty)

3. **Dashboard Widget Gating:**
   - [ ] Set business_model = "service_based" → Low Stock Alert hidden
   - [ ] Set business_model = "service_based" → Top Selling Products hidden
   - [ ] Disable expiry_tracking → Expiry Alerts widget hidden
   - [ ] Set business_model = "product_based" → all widgets visible

4. **Label Resolver:**
   - [ ] Set label override `branch` = "Outlet" in Business Profile → Save
   - [ ] Sidebar shows "Outlet" instead of "Branch"
   - [ ] Dashboard shows "Outlet Performance" instead of "Branch Performance"

5. **Public Catalogue Settings:**
   - [ ] Enable Public Catalogue checkbox → Save
   - [ ] Verify `industry_settings_json` contains `catalogue` object
   - [ ] Change slug → Save → verify persisted

6. **Existing Functionality:**
   - [ ] POS still works
   - [ ] Items page still works
   - [ ] Services page still works
   - [ ] Online Store settings still work
   - [ ] Sales/Invoices still work
