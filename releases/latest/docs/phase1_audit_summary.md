# Phase 1: Application Audit Summary
## MartPoint Industry Adaptation Engine

---

## 1. Database Schema Audit

### 1.1 Core Product / Service Tables

#### `db_items` (Products & Services Unified)
| Key Fields | Type | Notes |
|-----------|------|-------|
| `id` | int PK | |
| `store_id` | int | Scoped to store |
| `count_id` | int | Used to auto-generate item_code |
| `item_code` | varchar | Unique SKU/code |
| `item_name` | varchar | |
| `category_id` | int | Links to `db_category` |
| `sku`, `hsn`, `sac` | varchar | Tax / regulatory codes |
| `unit_id` | int | Links to `db_units` |
| `brand_id` | int | Links to `db_brands` |
| `lot_number`, `expire_date`, `mfg_date` | date/varchar | Batch / expiry tracking |
| `price`, `purchase_price`, `sales_price`, `online_price` | decimal | Multi-tier pricing |
| `tax_id`, `tax_type` | int/varchar | Inclusive / Exclusive |
| `profit_margin` | double | |
| `stock` | double | Current quantity |
| `item_image` | text | Path to image |
| `discount_type`, `discount` | varchar/double | Percentage or Fixed |
| `service_bit` | int DEFAULT 0 | **0 = Product, 1 = Service** |
| `seller_points` | double | Loyalty points awarded |
| `custom_barcode` | varchar | |
| `description` | text | |
| `item_group` | varchar | 'Single', 'Variants' |
| `parent_id`, `variant_id`, `child_bit` | int/int/int | Variant hierarchy |
| `mrp` | double | Maximum retail price |
| `batch_lot` | varchar | |
| `publish_online` | tinyint DEFAULT 1 | Online storefront visibility |
| `status` | int | Active / Inactive |

**Extension Point:** `service_bit` is the primary discriminator. Items with `service_bit=1` are treated as services throughout the application (POS, sales, reports). The `db_services` table provides *enriched* service-specific fields.

#### `db_services` (Enriched Service Details)
| Key Fields | Type | Notes |
|-----------|------|-------|
| `id` | int PK | |
| `store_id` | int | |
| `service_name` | varchar | |
| `service_image` | varchar | |
| `category_id` | int | |
| `price`, `discount_price` | decimal | |
| `service_duration` | varchar | e.g. "30 mins", "1 hour" |
| `description` | text | |
| `available_online` | tinyint DEFAULT 1 | |
| `requires_appointment` | tinyint DEFAULT 0 | **Booking flag** |
| `requires_note` | tinyint DEFAULT 0 | |
| `location_type` | enum('in-store','customer-location','online') | **Service delivery model** |
| `sort_order` | int | |
| `status` | tinyint | |

**Extension Point:** `location_type` and `requires_appointment` are critical for industry adaptation (e.g., home services vs. in-salon). Currently no foreign key to `db_items` - linkage is implicit via `service_name` / `category_id`.

---

### 1.2 Storefront / Online Store Tables

#### `db_storefront_settings` (Online Store Configuration)
| Key Fields | Type | Notes |
|-----------|------|-------|
| `store_id` | int UNIQUE | One record per store |
| `store_slug` | varchar | URL slug |
| `store_description`, `store_banner`, `store_logo` | text/varchar | |
| `whatsapp_number`, `store_email`, `store_phone`, `store_address` | varchar | Contact info |
| `default_branch_id` | int | Links to `db_warehouse` |
| `store_status` | enum('active','maintenance') | |
| `allow_paystack`, `allow_whatsapp`, `allow_pay_on_delivery` | tinyint | **Payment method toggles** |
| `allow_services` | tinyint DEFAULT 1 | **Service module toggle** |
| `allow_backorder` | tinyint DEFAULT 0 | |
| `show_search`, `show_categories`, `show_whatsapp_cta` | tinyint | UI feature toggles |
| `featured_products_limit` | int | |
| `theme_id` | int | Links to `db_storefront_themes` |
| `primary_color`, `secondary_color`, `font_family`, `button_style` | varchar | **Theming** |
| `store_headline`, `store_subheadline` | varchar | |
| `meta_title`, `meta_description`, `meta_keywords` | varchar | SEO |
| `business_hours` | text | JSON or plain text |
| `announcement_bar`, `announcement_bar_color` | varchar | Promotional bar |
| `footer_style`, `footer_about_us`, `footer_text_color` | varchar/text | |
| `trust_badges_json` | text | Social proof |
| `custom_head_scripts` | text | Inject JS/CSS |
| `google_analytics_id`, `facebook_pixel_id` | varchar | Tracking |

**Extension Point:** This table is already a **feature-flag and configuration store**. Adding an `industry_type` or `business_profile` JSON column here would be a natural extension point for the Industry Adaptation Engine.

#### `db_storefront_themes` (Theme Library with Industry Mapping)
| Key Fields | Type | Notes |
|-----------|------|-------|
| `theme_key` | varchar UNIQUE | e.g. 'general_retail', 'healthcare_pro' |
| `theme_name` | varchar | Human-readable |
| `industry` | varchar | **'pharmacy', 'beauty', 'fashion', 'electronics', 'grocery', 'restaurant', 'services', 'general'** |
| `default_primary_color`, `default_secondary_color` | varchar | |
| `default_font_family` | varchar | |

**Critical Finding:** This table ALREADY contains an `industry` column mapping themes to industries. This is a ready-made extension point for industry-specific UI theming.

---

### 1.3 Accounting & Financial Tables

#### `ac_accounts` (Chart of Accounts)
| Key Fields | Type | Notes |
|-----------|------|-------|
| `id` | int PK | |
| `store_id` | int | |
| `parent_id` | int | Hierarchical accounts |
| `sort_code`, `account_code` | varchar | |
| `account_name` | varchar | |
| `balance` | double | Real-time balance |
| `paymenttypes_id` | int | Links to `db_paymenttypes` |
| `customer_id`, `supplier_id`, `expense_id` | int | Entity-linked accounts |
| `delete_bit` | int | Soft delete |

#### `ac_transactions` (General Ledger)
| Key Fields | Type | Notes |
|-----------|------|-------|
| `transaction_type` | varchar | 'SALES PAYMENT', 'EXPENSE PAYMENT', 'JOURNAL', etc. |
| `debit_account_id`, `credit_account_id` | int | Double-entry |
| `debit_amt`, `credit_amt` | double | |
| `ref_salespayments_id`, `ref_expense_id`, `ref_purchasepayments_id`, etc. | int | **Polymorphic references** |
| `transaction_date` | date | |
| `created_by` | varchar | |

**Extension Point:** The transaction reference columns (`ref_*_id`) allow the accounting module to link to any operational module without schema changes. New industry modules can leverage this pattern.

#### `ac_moneydeposits` / `ac_moneytransfer`
- Standard cash/bank movement tracking.
- Linked to `ac_accounts` via FK.

---

### 1.4 Store / Business Configuration (`db_store`)

| Key Fields | Type | Notes |
|-----------|------|-------|
| `store_name`, `store_website`, `mobile`, `phone`, `email` | varchar | Basic info |
| `country`, `state`, `city`, `address`, `postcode` | varchar | Location |
| `location_lat`, `location_lng` | decimal | GPS coordinates |
| `gst_no`, `vat_no`, `pan_no` | varchar | Tax registration |
| `bank_details` | mediumtext | |
| `category_init`, `item_init`, `supplier_init`, `purchase_init`, `customer_init`, `sales_init`, `expense_init`, `accounts_init` | varchar | **Auto-numbering prefixes** |
| `invoice_view` | int | 1=Standard, 2=Indian GST |
| `sms_status` | int | Enable/disable SMS |
| `language_id`, `currency_id`, `currency_placement`, `timezone`, `date_format`, `time_format` | various | **Localization** |
| `sales_discount` | double | Default discount |
| `round_off` | int | |
| `decimals`, `qty_decimals` | int | Precision |
| `smtp_host`, `smtp_port`, `smtp_user`, `smtp_pass`, `smtp_status` | varchar/int | Email config |
| `sms_url` | text | SMS gateway URL |
| `mrp_column` | int | Show/hide MRP |
| `invoice_terms` | text | Default invoice footer |
| `previous_balance_bit` | int | Show previous balance on invoice |
| `sales_invoice_format_id`, `pos_invoice_format_id` | int | Invoice template selection |
| `current_subscriptionlist_id` | int | Subscription linkage |
| `quotation_init`, `money_transfer_init`, `sales_payment_init`, etc. | varchar | More numbering prefixes |

**Extension Point:** `db_store` is the primary **business profile** table. Adding `industry_type`, `business_model`, or `feature_flags_json` here would be the most natural place for store-level industry adaptation configuration.

---

### 1.5 Other Notable Tables

| Table | Purpose | Relevance to Adaptation Engine |
|-------|---------|-------------------------------|
| `db_warehouse` | Branch / warehouse management | Multi-location support |
| `db_userswarehouses` | User-to-branch assignments | Role-based branch access |
| `db_stocktransfer` / `db_stocktransferitems` | Inter-branch stock movement | Supply chain workflows |
| `db_coupons` | Discount coupons | Promotions engine |
| `db_customer_coupons` | Customer-specific coupons | Loyalty integration |
| `db_custadvance` | Customer advances / deposits | Pre-payment workflows |
| `db_installment` / `db_installment_payments` | Installment plans | FlexPay / BNPL |
| `db_subscription` | Subscription billing | Recurring revenue |
| `db_debt_reminder_settings` / `db_debt_reminder_history` | Automated debt collection | Credit control |
| `db_emailtemplates` / `db_smstemplates` | Communication templates | Industry-specific messaging |
| `db_qr_codes` | QR code generation | Online store / payments |
| `db_online_orders` / `db_online_order_items` | E-commerce orders | `item_type` enum('product','service') |
| `db_report_schedules` | Scheduled report generation | Automated reporting |
| `db_attendance` / `db_shifts` / `db_user_shifts` | Staff scheduling | Service industry staffing |
| `db_approval_settings` / `db_approval_logs` | Workflow approvals | Multi-tier authorization |
| `db_expiry_settings` | Product expiry alerts | Pharmacy / food industries |
| `db_license_history` / `db_license_otps` | Subscription licensing | SaaS billing |
| `db_subscription_license` | Active subscription status | Feature gating |
| `db_system_updates` | In-app update management | Deployment |

---

## 2. Module Inventory (from Sidebar & Permissions)

### 2.1 Sales Module
- POS (Point of Sale)
- Sales Invoices (Add, Edit, Delete, View)
- Sales Payments
- Sales Returns
- Installment Plans
- Quotations

### 2.2 Purchase Module
- Purchase Orders
- Purchase Returns
- Purchase Payments

### 2.3 Contacts Module
- Customers (Add, Edit, Delete, View, Import)
- Suppliers (Add, Edit, Delete, View, Import)
- Shipping Addresses

### 2.4 Loyalty & Rewards Module
- Dashboard
- Settings
- Customer Tiers
- Points History
- Referral Program
- Gift Cards
- Store Credit

### 2.5 Items & Services Module
- Items (Products)
- Services
- Categories
- Brands
- Variants
- Barcode Labels
- Import (Items, Services)

### 2.6 Stock Module
- Stock Adjustments
- Stock Transfers (requires `warehouse_module()`)

### 2.7 Accounts Module
- Chart of Accounts
- Journal Vouchers
- Money Transfer
- Money Deposit
- Cash Transactions

### 2.8 Expenses Module
- Expense Entries
- Expense Categories

### 2.9 Reports Module
- Profit & Loss
- Sales & Payments
- Customer Orders
- GST Reports (GSTR-1, GSTR-2, Sales GST, Purchase GST)
- Sales Tax, Purchase Tax
- Supplier Items
- Seller Points
- Stock Report
- Expense Report
- Purchase Report
- Sales Return, Purchase Return
- Stock Transfer Report
- Delivery Sheet, Load Sheet (disabled)

### 2.10 Online Store Module
- Dashboard
- Orders
- Online Products
- Services
- QR Codes
- Appearance / Themes
- Banners
- Homepage Builder
- Domains
- Brands
- Testimonials
- Instagram Feed
- FAQs
- Analytics
- Settings

### 2.11 Users & Roles Module
- Users List
- Roles & Permissions

### 2.12 Attendance Module
- Shifts
- Assign Shifts
- Attendance Logs

### 2.13 Messaging Module
- Send SMS
- SMS Templates
- SMS API Configuration
- Email Settings (SMTP)
- Debt Reminder

### 2.14 Settings Module
- Store Profile
- Site Settings (Admin only)
- License Management (Subscription, Plans, Usage)
- Tax List
- Units List
- Currency List
- Payment Types / Modes
- NIN Verification
- Database Backup
- System Updates
- Change Password
- Language Selection

---

## 3. Permission System

### 3.1 Granularity
Permissions follow a strict `{module}_{action}` convention:
- `sales_add`, `sales_edit`, `sales_delete`, `sales_view`
- `items_add`, `items_edit`, `items_delete`, `items_view`
- `accounts_add`, `accounts_view`, `journal_add`, `journal_view`
- `warehouse_add`, `warehouse_edit`, `warehouse_delete`, `warehouse_view`
- `roles_add`, `roles_edit`, `roles_delete`, `roles_view`
- `expense_add`, `expense_edit`, `expense_delete`, `expense_view`
- `stock_adjustment_add`, `stock_adjustment_edit`, `stock_adjustment_delete`, `stock_adjustment_view`
- `stock_transfer_add`, `stock_transfer_edit`, `stock_transfer_delete`, `stock_transfer_view`
- `loyalty_add`, `loyalty_edit`, `loyalty_delete`, `loyalty_view`
- `store_credit_add`, `store_credit_edit`, `store_credit_delete`, `store_credit_view`
- `gift_cards_add`, `gift_cards_edit`, `gift_cards_delete`, `gift_cards_view`
- `services_add`, `services_edit`, `services_delete`, `services_view`
- `online_store_view`, `online_store_edit`
- `send_sms`, `sms_template_view`, `sms_template_edit`
- `smtp_settings`, `sms_api_view`, `sms_api_edit`
- `debt_reminder_view`
- `nin_settings`, `nin_logs`, `nin_verify`, `nin_usage`

### 3.2 Special Permissions
- `special_access()` - Gates SaaS admin features (subscription management, system updates, currency, site settings)
- `is_admin()` / `is_store_admin()` - Super admin checks
- `is_user()` - Restricts certain menus for regular users

### 3.3 Role Management
- `db_roles` stores role names and descriptions
- `db_permissions` stores granular permission assignments per role
- Super Admin (role_id = 1) is protected from modification/deletion

**Extension Point:** The permission system is already robust enough to support industry-specific modules. New adaptation engine features can use the existing `{module}_{action}` pattern.

---

## 4. Feature Flags (Helper Functions)

### 4.1 Hard-coded Toggles
```php
function service_module() { return true; }
function accounts_module() { return true; }
function warehouse_module() { return true; }
function store_module() { return false; }
function special_access() { return is_admin(); }
```

**Critical Finding:** These are currently hard-coded PHP functions. There is NO database-driven feature flag system. The Industry Adaptation Engine should replace these with a configuration-driven approach (e.g., `db_store` or `db_storefront_settings` JSON column, or a new `db_feature_flags` table).

### 4.2 Existing Configuration Toggles
- `db_storefront_settings.allow_services` - Enable/disable services in online store
- `db_storefront_settings.allow_backorder` - Allow out-of-stock orders
- `db_storefront_settings.allow_paystack`, `allow_whatsapp`, `allow_pay_on_delivery` - Payment methods
- `db_store.sms_status` - Enable/disable SMS
- `db_store.smtp_status` - Enable/disable email
- `db_store.mrp_column` - Show/hide MRP column
- `db_store.previous_balance_bit` - Show/hide previous balance
- `db_store.round_off` - Enable/disable rounding

---

## 5. Extension Points for Industry Adaptation

### 5.1 Recommended Extension: Store-Level Industry Profile
**Target:** `db_store` table
**Addition:**
```sql
ALTER TABLE db_store ADD COLUMN industry_type VARCHAR(50) DEFAULT 'general';
ALTER TABLE db_store ADD COLUMN business_model VARCHAR(50) DEFAULT 'retail'; -- retail, wholesale, service, hybrid
ALTER TABLE db_store ADD COLUMN feature_flags_json JSON NULL;
ALTER TABLE db_store ADD COLUMN industry_settings_json JSON NULL;
```

This would allow:
- Per-store industry classification
- JSON-driven feature enablement without schema migrations
- Industry-specific UI customizations

### 5.2 Recommended Extension: Industry-Specific Service Fields
**Target:** `db_services` table
**Addition:**
```sql
ALTER TABLE db_services ADD COLUMN industry_fields_json JSON NULL;
```

This would allow service industries (healthcare, beauty, logistics) to store custom fields without altering the schema for each new industry.

### 5.3 Recommended Extension: Online Store Industry Adaptation
**Target:** `db_storefront_settings` table
**Addition:**
```sql
ALTER TABLE db_storefront_settings ADD COLUMN industry_adaptation_json JSON NULL;
```

Store industry-specific storefront rules (e.g., pharmacy requires prescription upload, restaurant requires table selection).

### 5.4 Leverage Existing Theme System
The `db_storefront_themes.industry` column already maps themes to industries. This can be expanded to:
- Auto-suggest themes based on `db_store.industry_type`
- Industry-specific homepage section templates in `db_storefront_homepage_sections`
- Industry-specific FAQ templates in `db_storefront_faqs`

### 5.5 Permission-Based Module Hiding
The existing permission system (`db_permissions`) can be used to hide/show industry-specific features by:
- Creating new permission keys (e.g., `pharmacy_prescription_view`, `restaurant_table_view`)
- Checking permissions in sidebar and controllers
- No new tables needed

### 5.6 Reusable Workflows
| Existing Workflow | Industry Adaptation Use |
|-------------------|------------------------|
| Installments (`db_installment`) | FlexPay / BNPL for any industry |
| Subscriptions (`db_subscription`) | Recurring billing (SaaS, memberships) |
| Debt Reminders | Credit control for B2B wholesale |
| Customer Advances (`db_custadvance`) | Deposit-based services (events, construction) |
| Stock Transfer | Multi-branch retail / pharmacy chains |
| Attendance / Shifts | Service industry scheduling |
| Approval Logs | Multi-tier PO approval for enterprise |
| Expiry Settings | Pharmacy / food batch tracking |
| QR Codes | Restaurant table ordering, asset tracking |
| Online Orders + Services | Booking engine for salons, clinics |

---

## 6. Tables NOT to Create (Extend Instead)

Instead of creating new tables, extend these existing ones:

| Instead of... | Extend... |
|---------------|-----------|
| New `db_industry_settings` | `db_store.feature_flags_json` or `db_storefront_settings` |
| New `db_service_bookings` | `db_online_orders` (use `item_type='service'`) |
| New `db_industry_templates` | `db_emailtemplates` / `db_smstemplates` (add `industry_type` column) |
| New `db_loyalty_industry_rules` | `db_coupons` / `db_customer_coupons` (add `industry_eligibility` JSON) |
| New `db_payments_industry` | `db_paymenttypes` / `db_payment_modes` (add `industry_restrictions` JSON) |
| New `db_branch_industry` | `db_warehouse` (add `branch_type`, `industry_specialization` columns) |

---

## 7. Summary of Configuration Opportunities

### 7.1 Immediate (No Schema Changes)
1. **Theme Auto-Selection:** Use `db_storefront_themes.industry` to auto-suggest themes based on store profile.
2. **Feature Flag Migration:** Move `service_module()`, `accounts_module()`, `warehouse_module()` from hard-coded PHP to `db_storefront_settings` boolean columns.
3. **Permission Expansion:** Add industry-specific permissions to `Roles_model.php` and sidebar.

### 7.2 Short-Term (Minor Schema Changes)
1. **Add `industry_type` to `db_store`**
2. **Add `feature_flags_json` to `db_store`**
3. **Add `industry_fields_json` to `db_services`**
4. **Add `industry_type` to `db_emailtemplates` and `db_smstemplates`**
5. **Add `branch_type` to `db_warehouse`**

### 7.3 Medium-Term (Workflow Extensions)
1. **Service Booking Engine:** Extend `db_online_orders` to support appointment scheduling using `db_services.requires_appointment` and `db_shifts`.
2. **Industry-Specific Reporting:** Extend existing report controllers with industry-aware filters.
3. **B2B Wholesale Adaptation:** Extend `db_customers` with `credit_limit`, `price_tier` fields; use `db_debt_reminder_settings` for automated collection.

---

## 8. Audit Conclusion

The MartPoint codebase is **highly extensible** for an Industry Adaptation Engine due to:
- **Unified Items/Services model** (`db_items.service_bit`)
- **Existing theme-industry mapping** (`db_storefront_themes.industry`)
- **Granular permission system** ready for new modules
- **Online store configuration store** (`db_storefront_settings`) ready for feature flags
- **Store-level configuration** (`db_store`) ready for business profile extensions
- **Polymorphic accounting transactions** (`ac_transactions`) ready for new financial workflows

**No new modules or databases are required** for Phase 1. All extension points can leverage existing tables with minor schema additions (JSON columns, enum values, or new permission keys).

**Next Step (Phase 2):** Design the `Business Profile` and `Feature Flags` architecture, specifying exact column additions and helper function refactors.
