# Phase 2 — Feature Flag Wiring Summary

## Cleanup Fixes Applied Before Smoke Test

| Fix | File | Details |
|-----|------|---------|
| DB column safety | `helpers/business_profile_helper.php` | Added `$CI->db->field_exists('feature_flags_json', 'db_store')` guard before querying. Prevents fatal DB errors when the column is missing (e.g. migration not yet run). |
| Controller consistency | `controllers/Approvals.php` | Changed `show_404()` to `$this->show_access_denied_page()` and moved `$this->load_global()` before the gate to match all other gated controllers. |

---

## 1. `manager_approvals`

| # | File | What Was Gated |
|---|------|----------------|
| 1 | `controllers/Approvals.php` | Constructor blocks entire controller when disabled |
| 2 | `views/sidebar.php` | Approval Logs menu item (Reports section) |
| 3 | `views/sidebar.php` | Security & Approvals settings menu item (Settings section) |
| 4 | `views/users.php` | Approval PIN field in user add/edit form |
| 5 | `views/role.php` | Entire "Security & Approvals" permissions row |

**Test Steps:**
1. Enable `manager_approvals` in Business Profile → confirm Approvals pages load.
2. Disable `manager_approvals` → confirm sidebar menus disappear, Approvals controller returns access-denied page, Approval PIN is hidden in user form, and the permissions row is absent in Role editor.

**Test Result:** Pending user verification

---

## 2. `customer_notes`

| # | File | What Was Gated |
|---|------|----------------|
| 1 | `views/online_store/services.php` | "Requires Customer Note" checkbox in service modal |
| 2 | `views/online_store/services.php` | JavaScript `editService()` no longer tries to set checked state on hidden checkbox |
| 3 | `views/online_store/services.php` | JavaScript save payload defaults `requires_note` to `0` when flag is disabled |

**Test Steps:**
1. Disable `customer_notes` → open Online Store > Services > edit a service → confirm "Requires Customer Note" checkbox is absent.
2. Save the service → confirm no JS errors and the value saves correctly as `0`.
3. Enable `customer_notes` → confirm checkbox reappears and saves correctly.

**Test Result:** Pending user verification

---

## 3. `batch_tracking`

| # | File | What Was Gated |
|---|------|----------------|
| 1 | `views/items.php` | Entire Barcode / Batch / Lot section on the Items add/edit form |

**Test Steps:**
1. Disable `batch_tracking` → open Items > Add Item → confirm the Barcode/Batch/Lot box is completely absent.
2. Enable `batch_tracking` → confirm the section appears and adding rows works.

**Test Result:** Pending user verification

---

## 4. `expiry_tracking` (nested inside `batch_tracking`)

| # | File | What Was Gated |
|---|------|----------------|
| 1 | `views/items.php` | Expiry and MFG Date columns inside the barcode table header |
| 2 | `views/items.php` | Expiry and MFG Date inputs on existing/new barcode rows |
| 3 | `views/items.php` | JavaScript `addBarcodeRow()` no longer appends expiry/mfg columns when disabled |

**Test Steps:**
1. Enable both `batch_tracking` and `expiry_tracking` → confirm Expiry and MFG Date columns appear.
2. Disable `expiry_tracking` (keep `batch_tracking` on) → confirm columns disappear but Barcode/Batch/Lot section still works.
3. Add a new barcode row → confirm no JS errors.
4. Save the item → confirm the form submits without missing-field errors.

**Test Result:** Pending user verification

---

## 5. `table_management`

| # | File | What Was Gated |
|---|------|----------------|
| 1 | `views/online_store/qr_codes.php` | "Table QR" option in the QR Type dropdown |
| 2 | `views/online_store/qr_codes.php` | Table Number input field |
| 3 | `views/online_store/qr_codes.php` | JavaScript `toggleQrOptions()` no longer toggles the hidden table input |
| 4 | `views/online_store/qr_codes.php` | JavaScript save POST defaults `table_number` to empty string when disabled |

**Test Steps:**
1. Disable `table_management` → open Online Store > QR Codes → confirm "Table QR" option is absent.
2. Select other QR types (product, category, attendance) → confirm they still work.
3. Generate a QR → confirm no JS errors.
4. Enable `table_management` → confirm "Table QR" returns and table input appears when selected.

**Test Result:** Pending user verification

---

## 6. `bundles`

| # | File | What Was Gated |
|---|------|----------------|
| 1 | `views/sidebar.php` | Variants List menu item under Items & Services |
| 2 | `views/modals/modal_item.php` | "Variants" option in the Item Group dropdown (Quick Add Item modal) |
| 3 | `views/role.php` | Entire Variants permissions row |
| 4 | `controllers/Variants.php` | Constructor blocks entire controller when disabled |

**Test Steps:**
1. Disable `bundles` → confirm Variants menu is gone from sidebar.
2. Try to visit `/variants/view` directly → confirm access-denied page.
3. Open Quick Add Item modal → confirm "Variants" option is absent.
4. Open Role editor → confirm Variants permissions row is absent.
5. Enable `bundles` → confirm all of the above reappear.

**Test Result:** Pending user verification

---

## 7. `loyalty` / `gift_cards` / `store_credit` (POS Payment Modal)

| # | File | What Was Gated |
|---|------|----------------|
| 1 | `views/modals_pos_payment/modal_payments_multi.php` | Outer "Loyalty & Rewards" box only shows if at least one flag is enabled |
| 2 | `views/modals_pos_payment/modal_payments_multi.php` | Redeem Points input only shows if `loyalty` enabled |
| 3 | `views/modals_pos_payment/modal_payments_multi.php` | Store Credit input only shows if `store_credit` enabled |
| 4 | `views/modals_pos_payment/modal_payments_multi.php` | Gift Card inputs only shows if `gift_cards` enabled |

**Test Steps:**
1. Disable all three (`loyalty`, `gift_cards`, `store_credit`) → open POS > checkout → confirm the entire Loyalty & Rewards box is absent and no JS errors occur.
2. Enable only `loyalty` → confirm only Redeem Points appears.
3. Enable only `store_credit` → confirm only Store Credit appears.
4. Enable only `gift_cards` → confirm only Gift Card appears.

**Test Result:** Pending user verification

---

## 8. Previously Wired in Earlier Session (Confirmed Still in Place)

| Flag | Controller Gate | Sidebar Gate | Other UI Gate |
|------|-----------------|--------------|---------------|
| `online_store` | `Online_store.php` constructor | Full Online Store menu tree | — |
| `qr_ordering` | `Online_store.php` `qr_codes()` & `generate_qr()` | QR Codes submenu item | — |
| `loyalty` | `Loyalty.php` constructor | Loyalty submenu + parent tree | — |
| `gift_cards` | `Gift_cards.php` constructor | Gift Cards submenu + parent tree | — |
| `store_credit` | `Store_credit.php` constructor | Store Credit submenu + parent tree | — |
| `expiry_tracking` | `Expiry_settings.php` constructor | Expiry Settings menu | — |
| `flexpay` | `Installments.php` constructor | Installments menu | PayPlan button in POS modal |
| `delivery_scheduling` | `Reports.php` `delivery_sheet()` | — | — |
| `appointments` | — | — | Online Store Services "Requires Appointment" checkbox |
| `multi_unit_inventory` | — | — | Items page "Add Unit" button |

---

## Remaining Flags with No Admin UI to Gate (Wrapper Functions Ready)

These flags have `mp_feature_enabled()` checks and legacy wrapper functions in `custom_helper.php`, but there is currently no admin sidebar menu, controller, or form field to gate because the feature UI does not exist yet.

| Flag | Status | Notes |
|------|--------|-------|
| `custom_orders` | No UI | No controller or menu found |
| `packages` | No UI | No controller or menu found |
| `memberships` | No UI | No controller or menu found |
| `kitchen_workflow` | No UI | No controller or menu found |
| `laundry_workflow` | No UI | No controller or menu found |
| `treatment_notes` | No UI | No controller or menu found |
| `staff_assignment` | No UI | No controller or menu found |
| `staff_commission` | No UI | No controller or menu found |
| `production_workflow` | No UI | No controller or menu found |
| `recipe_tracking` | No UI | No controller or menu found |
| `serial_number_tracking` | No UI | No controller or menu found |
| `imei_tracking` | No UI | No controller or menu found |
| `warranty_tracking` | No UI | No controller or menu found |
| `price_catalogue` | No UI | No admin settings page found |
| `public_catalogue` | No UI | No admin settings page found |

---

## Smoke Test Checklist for User

- [ ] 1. All newly gated pages load cleanly when their feature flags are **enabled**.
- [ ] 2. Hidden modules do **not** appear in sidebar when their flags are **disabled**.
- [ ] 3. Controller-level gates return a clean **Access Denied** page (not a PHP fatal error).
- [ ] 4. POS payment modal works when `loyalty`, `gift_cards`, and `store_credit` are **all disabled**.
- [ ] 5. POS payment modal works when **only one** of loyalty/gift-card/store-credit is enabled.
- [ ] 6. Services page saves correctly when `customer_notes` is **disabled**.
- [ ] 7. Items page works when `batch_tracking` and `expiry_tracking` are **disabled**.
- [ ] 8. QR Codes page works when `table_management` is **disabled**.
- [ ] 9. Variants page/menu/permissions behave correctly when `bundles` is **disabled**.
- [ ] 10. Approval Logs, Security & Approvals, Approval PIN, and approval permissions are **all hidden** when `manager_approvals` is **disabled**.

## Next Steps

After completing the checklist above, Phase 3 can begin:
- Business Profile UI polish
- Storefront/theme auto-suggestion
- Dashboard widget adaptation foundation
- Label resolver integration in high-visibility areas
- Custom order foundation planning
