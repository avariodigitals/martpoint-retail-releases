<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Default Data Seeder for MartPoint Retail
 * Creates default roles, permissions, and expense categories
 * during installation or first setup.
 * 
 * Rules:
 * - Idempotent: checks before creating to avoid duplicates
 * - Safe for existing installations: skips if data already exists
 * - Does NOT modify sales, POS, stock, invoice, payment logic
 */
class Default_data_model extends CI_Model {

    public function __construct() {
        parent::__construct();
        $this->load->helper('custom_helper');
    }

    // ============================================================
    // MAIN ENTRY POINT
    // ============================================================

    /**
     * Create all default data for a store
     * @param int $store_id
     * @return array Results
     */
    public function seed_store_defaults($store_id = null) {
        if (empty($store_id)) {
            $store_id = get_current_store_id();
        }

        $results = array(
            'roles_created'     => 0,
            'permissions_created' => 0,
            'categories_created'  => 0,
            'errors'            => array()
        );

        // 1. Create default roles
        $role_results = $this->create_default_roles($store_id);
        $results['roles_created'] = $role_results['count'];
        if (!empty($role_results['errors'])) {
            $results['errors'] = array_merge($results['errors'], $role_results['errors']);
        }

        // 2. Create default expense categories
        $cat_results = $this->create_default_expense_categories($store_id);
        $results['categories_created'] = $cat_results['count'];
        if (!empty($cat_results['errors'])) {
            $results['errors'] = array_merge($results['errors'], $cat_results['errors']);
        }

        return $results;
    }

    // ============================================================
    // DEFAULT ROLES & PERMISSIONS
    // ============================================================

    /**
     * Create default roles with mapped permissions for a store
     */
    public function create_default_roles($store_id) {
        $results = array('count' => 0, 'errors' => array());

        // Define roles
        $roles_config = array(
            'Business Owner' => array(
                'description' => 'Store Owner / Founder / Managing Director. Full access except Super Admin.',
                'permissions' => $this->get_business_owner_permissions()
            ),
            'Partner' => array(
                'description' => 'Implementation / Setup Partner. Can configure business settings, users and master data.',
                'permissions' => $this->get_partner_permissions()
            ),
            'Manager' => array(
                'description' => 'Store Manager. Access to operations, no Users/Roles/Settings.',
                'permissions' => $this->get_manager_permissions()
            ),
            'Cashier' => array(
                'description' => 'Sales & Checkout Staff. POS and Sales only.',
                'permissions' => $this->get_cashier_permissions()
            ),
            'Accountant' => array(
                'description' => 'Finance & Accounts Officer. View sales/purchases, manage expenses & accounts.',
                'permissions' => $this->get_accountant_permissions()
            ),
            'Inventory Officer' => array(
                'description' => 'Stock & Inventory Staff. Manage items, purchases, stock transfers and adjustments. No POS/Sales, no FSTR reports.',
                'permissions' => $this->get_inventory_officer_permissions()
            )
        );

        foreach ($roles_config as $role_name => $config) {
            // Check if role already exists for this store
            $exists = $this->db->query(
                "SELECT id FROM db_roles WHERE store_id = ? AND UPPER(role_name) = UPPER(?)",
                array($store_id, $role_name)
            )->num_rows();

            if ($exists > 0) {
                continue; // Skip existing
            }

            // Insert role
            $role_data = array(
                'store_id'    => $store_id,
                'role_name'   => $role_name,
                'description' => $config['description'],
                'status'      => 1
            );

            if ($this->db->insert('db_roles', $role_data)) {
                $role_id = $this->db->insert_id();
                $results['count']++;

                // Assign permissions
                $perm_count = $this->assign_permissions($role_id, $store_id, $config['permissions']);
            } else {
                $results['errors'][] = "Failed to create role: {$role_name}";
            }
        }

        return $results;
    }

    /**
     * Assign permissions to a role
     */
    private function assign_permissions($role_id, $store_id, $permissions) {
        if (empty($permissions)) return 0;

        $batch = array();
        foreach ($permissions as $perm) {
            $batch[] = array(
                'store_id'    => $store_id,
                'role_id'     => $role_id,
                'permissions' => $perm
            );
        }

        if (!empty($batch)) {
            $this->db->insert_batch('db_permissions', $batch);
            return count($batch);
        }
        return 0;
    }

    /**
     * Re-seed missing default permissions for existing standard roles.
     * For each standard role (Business Owner, Manager, Cashier, Accountant, Inventory Officer),
     * adds any permissions from the default set that are not already in db_permissions.
     * This fixes roles that were created before new permissions were added.
     *
     * @param int $store_id
     * @return int Number of permissions added
     */
    public function reseed_missing_permissions($store_id = null) {
        if (empty($store_id)) {
            $store_id = get_current_store_id();
        }
        if (empty($store_id)) {
            return 0;
        }

        $added = 0;

        // Create any missing standard roles first (idempotent: skips existing)
        $this->create_default_roles($store_id);

        // Get all roles for this store (except Super Admin role id 1)
        $roles = $this->db->where('store_id', $store_id)->where('id !=', 1)->get('db_roles')->result();

        foreach ($roles as $role) {
            $default_perms = $this->get_role_default_permissions($role->role_name);
            if (empty($default_perms)) {
                continue; // Not a standard role
            }

            // Get existing permissions for this role
            $existing = $this->db->where('role_id', $role->id)->get('db_permissions')->result_array();
            $existing_keys = array_column($existing, 'permissions');

            // Do not overwrite existing role permissions (admin changes must persist).
            // Only seed defaults when the role has no permissions at all.
            if (!empty($existing_keys)) {
                continue;
            }

            // Find missing permissions
            $missing = array_diff($default_perms, $existing_keys);
            if (empty($missing)) {
                continue;
            }

            // Insert missing permissions
            $batch = array();
            foreach ($missing as $perm) {
                $batch[] = array(
                    'store_id'    => $store_id,
                    'role_id'     => $role->id,
                    'permissions' => $perm
                );
            }
            if (!empty($batch)) {
                $this->db->insert_batch('db_permissions', $batch);
                $added += count($batch);
            }
        }

        return $added;
    }

    // ============================================================
    // PERMISSION MAPPINGS
    // ============================================================

    /**
     * Business Owner: Full access except Super Admin functions and Help
     */
    private function get_business_owner_permissions() {
        return array(
            // Dashboard
            'dashboard_view','dashboard_info_box_1','dashboard_info_box_2',
            'dashboard_pur_sal_chart','dashboard_recent_items',
            'dashboard_stock_alert','dashboard_trending_items_chart',
            'recent_sales_invoice_list',
            // Users
            'users_add','users_edit','users_delete','users_view',
            // Tax
            'tax_add','tax_edit','tax_delete','tax_view',
            // Units
            'units_add','units_edit','units_delete','units_view',
            // Payment Types
            'payment_types_add','payment_types_edit','payment_types_delete','payment_types_view',
            // Store
            'store_edit',
            // Items
            'items_add','items_edit','items_delete','items_view',
            'items_category_add','items_category_edit','items_category_delete','items_category_view',
            'brand_add','brand_edit','brand_delete','brand_view',
            'attributes_add','attributes_edit','attributes_delete','attributes_view',
            'variant_add','variant_edit','variant_delete','variant_view',
            'print_labels',
            'import_items',
            // Suppliers
            'suppliers_add','suppliers_edit','suppliers_delete','suppliers_view',
            'import_suppliers',
            // Customers
            'customers_add','customers_edit','customers_delete','customers_view',
            'import_customers',
            // Purchases
            'purchase_add','purchase_edit','purchase_delete','purchase_view',
            'purchase_return_add','purchase_return_edit','purchase_return_delete','purchase_return_view',
            'purchase_payment_view','purchase_payment_add','purchase_payment_delete',
            'purchase_return_payment_view','purchase_return_payment_add','purchase_return_payment_delete',
            // Sales
            'sales_add','sales_edit','sales_delete','sales_view',
            'sales_return_add','sales_return_edit','sales_return_delete','sales_return_view',
            'sales_payment_view','sales_payment_add','sales_payment_delete',
            'sales_return_payment_view','sales_return_payment_add','sales_return_payment_delete',
            // POS
            'pos',
            // Expenses
            'expense_add','expense_edit','expense_delete','expense_view',
            'expense_category_add','expense_category_edit','expense_category_delete','expense_category_view',
            'show_all_users_expenses',
            // Stock
            'stock_transfer_add','stock_transfer_edit','stock_transfer_delete','stock_transfer_view',
            'stock_adjustment_add','stock_adjustment_edit','stock_adjustment_delete','stock_adjustment_view',
            // Warehouse
            'warehouse_add','warehouse_edit','warehouse_delete','warehouse_view',
            // Accounts
            'accounts_add','accounts_edit','accounts_delete','accounts_view',
            'money_transfer_add','money_transfer_edit','money_transfer_delete','money_transfer_view',
            'money_deposit_add','money_deposit_edit','money_deposit_delete','money_deposit_view',
            'cash_transactions',
            'tills_view','tills_add','tills_edit','tills_delete',
            'cashier_shifts_manage','z_report',
            // Services
            'services_add','services_edit','services_delete','services_view',
            'import_services',
            // Quotations
            'quotation_add','quotation_edit','quotation_delete','quotation_view',
            'show_all_users_quotations',
            // Messaging
            'send_sms','sms_template_view','sms_template_edit',
            'send_email','email_template_view','email_template_edit',
            // Coupons
            'discountCouponAdd','discountCouponEdit','discountCouponDelete','discountCouponView',
            'customerCouponAdd','customerCouponEdit','customerCouponDelete','customerCouponView',
            // Reports
            'sales_report','purchase_report','expense_report','profit_report',
            'stock_report','item_sales_report','expired_items_report',
            'purchase_payments_report','sales_payments_report',
            'sales_tax_report','purchase_tax_report',
            'supplier_items_report','seller_points_report',
                        'return_items_report','stock_transfer_report',
            'sales_summary_report','sales_return_payments',
            'purchase_return_report','sales_return_report',
            'customer_orders_report',
            // FSTR (tax) reports are intentionally NOT granted here —
            // they stay flagged for the Store Admin role only.
            // Fashion Intelligence reports
            'variant_attribute_report','sell_through_report','reorder_suggestion_report',
            'promotions_manage',
            // Advanced
            'cust_adv_payments_add','cust_adv_payments_edit','cust_adv_payments_delete','cust_adv_payments_view',
            'show_all_users_sales_invoices','show_all_users_sales_return_invoices',
            'show_all_users_purchase_invoices','show_all_users_purchase_return_invoices',
            'show_purchase_price',
            // Settings
            'subscription','sms_settings','sms_api_view','sms_api_edit',
            // Online Store (Business Owner can manage online store)
            'online_store_view','online_store_edit',
            // Leads / CRM
            'leads_view','leads_add','leads_edit','leads_delete',
        );
    }

    /**
     * Manager: Operations access, NO Users/Roles/Settings
     */
    private function get_manager_permissions() {
        return array(
            // Dashboard
            'dashboard_view','dashboard_info_box_1','dashboard_info_box_2',
            'dashboard_pur_sal_chart','dashboard_recent_items',
            'dashboard_stock_alert','dashboard_trending_items_chart',
            'recent_sales_invoice_list',
            // Tax (view only)
            'tax_view',
            // Units (view only)
            'units_view',
            // Payment Types (view only)
            'payment_types_view',
            // Items
            'items_add','items_edit','items_delete','items_view',
            'items_category_add','items_category_edit','items_category_delete','items_category_view',
            'brand_add','brand_edit','brand_delete','brand_view',
            'attributes_add','attributes_edit','attributes_delete','attributes_view',
            'variant_add','variant_edit','variant_delete','variant_view',
            'print_labels',
            'import_items',
            // Suppliers
            'suppliers_add','suppliers_edit','suppliers_delete','suppliers_view',
            'import_suppliers',
            // Customers
            'customers_add','customers_edit','customers_delete','customers_view',
            'import_customers',
            // Purchases
            'purchase_add','purchase_edit','purchase_delete','purchase_view',
            'purchase_return_add','purchase_return_edit','purchase_return_delete','purchase_return_view',
            'purchase_payment_view','purchase_payment_add','purchase_payment_delete',
            'purchase_return_payment_view','purchase_return_payment_add','purchase_return_payment_delete',
            // Sales
            'sales_add','sales_edit','sales_delete','sales_view',
            'sales_return_add','sales_return_edit','sales_return_delete','sales_return_view',
            'sales_payment_view','sales_payment_add','sales_payment_delete',
            'sales_return_payment_view','sales_return_payment_add','sales_return_payment_delete',
            // POS
            'pos',
            // Expenses
            'expense_add','expense_edit','expense_delete','expense_view',
            'expense_category_add','expense_category_edit','expense_category_delete','expense_category_view',
            'show_all_users_expenses',
            // Stock
            'stock_transfer_add','stock_transfer_edit','stock_transfer_delete','stock_transfer_view',
            'stock_adjustment_add','stock_adjustment_edit','stock_adjustment_delete','stock_adjustment_view',
            // Warehouse
            'warehouse_add','warehouse_edit','warehouse_delete','warehouse_view',
            // Accounts
            'accounts_add','accounts_edit','accounts_delete','accounts_view',
            'money_transfer_add','money_transfer_edit','money_transfer_delete','money_transfer_view',
            'money_deposit_add','money_deposit_edit','money_deposit_delete','money_deposit_view',
            'cash_transactions',
            'tills_view','tills_add','tills_edit','tills_delete',
            'cashier_shifts_manage','z_report',
            // Services
            'services_add','services_edit','services_delete','services_view',
            'import_services',
            // Quotations
            'quotation_add','quotation_edit','quotation_delete','quotation_view',
            'show_all_users_quotations',
            // Messaging (view only)
            'send_sms','sms_template_view',
            'send_email','email_template_view',
            // Coupons (view only)
            'discountCouponView','customerCouponView',
            // Reports
            'sales_report','purchase_report','expense_report','profit_report',
            'stock_report','item_sales_report',
            'purchase_payments_report','sales_payments_report',
            'sales_tax_report','purchase_tax_report',
            'supplier_items_report','seller_points_report',
            'return_items_report','stock_transfer_report',
            'sales_summary_report','sales_return_payments',
            'purchase_return_report','sales_return_report',
            'customer_orders_report',
            // FSTR (tax) reports are intentionally NOT granted here —
            // they stay flagged for the Store Admin role only.
            // Fashion Intelligence reports
            'variant_attribute_report','sell_through_report','reorder_suggestion_report',
            'promotions_manage',
            // Advanced
            'cust_adv_payments_view',
            'show_all_users_sales_invoices','show_all_users_sales_return_invoices',
            'show_all_users_purchase_invoices','show_all_users_purchase_return_invoices',
            'show_purchase_price',
            // Online Store (Manager can view and fulfill orders, but not edit store settings)
            'online_store_orders',
            // Leads / CRM (Manager can work the pipeline but not delete)
            'leads_view','leads_add','leads_edit',
        );
    }

    /**
     * Cashier: POS, Sales, Customers, View Items only
     */
    private function get_cashier_permissions() {
        return array(
            // Dashboard
            'dashboard_view','dashboard_info_box_1','dashboard_info_box_2',
            'dashboard_pur_sal_chart','dashboard_recent_items',
            'dashboard_stock_alert','dashboard_trending_items_chart',
            // Items (view only)
            'items_view','items_category_view','brand_view',
            'print_labels',
            // Customers
            'customers_add','customers_edit','customers_view',
            // Sales (no delete)
            'sales_add','sales_edit','sales_view',
            'sales_return_add','sales_return_edit','sales_return_view',
            'sales_payment_view','sales_payment_add',
            'sales_return_payment_view','sales_return_payment_add',
            // POS
            'pos',
            'cashier_shifts_manage',
            // Quotations (view only)
            'quotation_view',
            // Messaging (view only)
            'send_sms','sms_template_view',
            // Coupons (view only)
            'discountCouponView','customerCouponView',
            // Limited Reports
            'sales_report','sales_payments_report',
            'recent_sales_invoice_list'
        );
    }

    /**
     * Accountant: View sales/purchases, full expenses & accounts, reports
     */
    private function get_accountant_permissions() {
        return array(
            // Dashboard
            'dashboard_view','dashboard_info_box_1','dashboard_info_box_2',
            'dashboard_pur_sal_chart','dashboard_recent_items',
            'dashboard_stock_alert','dashboard_trending_items_chart',
            // Items (view only)
            'items_view','items_category_view','brand_view',
            // Customers (view only)
            'customers_view',
            // Suppliers (view only)
            'suppliers_view',
            // Sales (view only)
            'sales_view',
            'sales_return_view',
            'sales_payment_view',
            'sales_return_payment_view',
            // Purchases (view only)
            'purchase_view',
            'purchase_return_view',
            'purchase_payment_view',
            'purchase_return_payment_view',
            // Expenses (full)
            'expense_add','expense_edit','expense_delete','expense_view',
            'expense_category_add','expense_category_edit','expense_category_delete','expense_category_view',
            'show_all_users_expenses',
            // Accounts (full)
            'accounts_add','accounts_edit','accounts_delete','accounts_view',
            'money_transfer_add','money_transfer_edit','money_transfer_delete','money_transfer_view',
            'money_deposit_add','money_deposit_edit','money_deposit_delete','money_deposit_view',
            'cash_transactions',
            // Customer Advance
            'cust_adv_payments_add','cust_adv_payments_edit','cust_adv_payments_delete','cust_adv_payments_view',
            // Quotations (view only)
            'quotation_view',
            // Messaging (view only)
            'send_sms','sms_template_view',
            'send_email','email_template_view',
            // Reports
            'sales_report','purchase_report','expense_report','profit_report',
            'stock_report',
            'purchase_payments_report','sales_payments_report',
            'sales_tax_report','purchase_tax_report',
            'supplier_items_report','seller_points_report',
                        'return_items_report','stock_transfer_report',
            'sales_summary_report','sales_return_payments',
            'purchase_return_report','sales_return_report',
            'customer_orders_report',
            // Advanced
            'show_all_users_sales_invoices','show_all_users_sales_return_invoices',
            'show_all_users_purchase_invoices','show_all_users_purchase_return_invoices',
            'show_all_users_expenses','show_all_users_quotations',
            'show_purchase_price'
        );
    }

    /**
     * Inventory Officer: Items, Purchases, Stock, Warehouse, Suppliers.
     * No POS, no Sales, no Expenses/Accounts, no GST reports.
     */
    private function get_inventory_officer_permissions() {
        return array(
            // Dashboard
            'dashboard_view','dashboard_info_box_1','dashboard_info_box_2',
            'dashboard_recent_items','dashboard_stock_alert',
            'dashboard_expired_items',
            // Tax (view only)
            'tax_view',
            // Units (view only)
            'units_view',
            // Items (full)
            'items_add','items_edit','items_delete','items_view',
            'items_category_add','items_category_edit','items_category_delete','items_category_view',
            'brand_add','brand_edit','brand_delete','brand_view',
            'attributes_add','attributes_edit','attributes_delete','attributes_view',
            'variant_add','variant_edit','variant_delete','variant_view',
            'print_labels',
            'import_items',
            // Suppliers
            'suppliers_add','suppliers_edit','suppliers_delete','suppliers_view',
            'import_suppliers',
            // Purchases
            'purchase_add','purchase_edit','purchase_delete','purchase_view',
            'purchase_return_add','purchase_return_edit','purchase_return_delete','purchase_return_view',
            'purchase_payment_view','purchase_payment_add',
            'purchase_return_payment_view','purchase_return_payment_add',
            // Stock
            'stock_transfer_add','stock_transfer_edit','stock_transfer_delete','stock_transfer_view',
            'stock_adjustment_add','stock_adjustment_edit','stock_adjustment_delete','stock_adjustment_view',
            // Warehouse
            'warehouse_add','warehouse_edit','warehouse_delete','warehouse_view',
            // Reports (inventory-focused, NO GST reports)
            'purchase_report','stock_report','expired_items_report',
            'purchase_payments_report',
            'supplier_items_report',
            'return_items_report','stock_transfer_report',
            'purchase_return_report',
            // Fashion Intelligence reports
            'variant_attribute_report','reorder_suggestion_report',
            // Advanced
            'show_purchase_price'
        );
    }

    /**
     * Partner: Implementation and setup partner.
     * Full setup access (users, roles, business setup, master data) plus operational view.
     */
    private function get_partner_permissions() {
        return array(
            // Dashboard
            'dashboard_view','dashboard_info_box_1','dashboard_info_box_2',
            'dashboard_pur_sal_chart','dashboard_recent_items',
            'dashboard_stock_alert','dashboard_trending_items_chart',
            'recent_sales_invoice_list',
            // Users
            'users_add','users_edit','users_delete','users_view',
            // Business Setup
            'business_setup',
            // Store
            'store_edit','store_view',
            // Tax
            'tax_add','tax_edit','tax_delete','tax_view',
            // Units
            'units_add','units_edit','units_delete','units_view',
            // Payment Types / Modes
            'payment_types_add','payment_types_edit','payment_types_delete','payment_types_view',
            'payment_modes_add','payment_modes_edit','payment_modes_delete','payment_modes_view',
            // Items
            'items_add','items_edit','items_delete','items_view',
            'items_category_add','items_category_edit','items_category_delete','items_category_view',
            'brand_add','brand_edit','brand_delete','brand_view',
            'attributes_add','attributes_edit','attributes_delete','attributes_view',
            'variant_add','variant_edit','variant_delete','variant_view',
            'print_labels',
            'import_items',
            // Suppliers / Customers
            'suppliers_add','suppliers_edit','suppliers_delete','suppliers_view',
            'import_suppliers',
            'customers_add','customers_edit','customers_delete','customers_view',
            'import_customers',
            // Purchases / Sales (view and manage)
            'purchase_add','purchase_edit','purchase_delete','purchase_view',
            'purchase_return_add','purchase_return_edit','purchase_return_delete','purchase_return_view',
            'purchase_payment_view','purchase_payment_add','purchase_payment_delete',
            'purchase_return_payment_view','purchase_return_payment_add','purchase_return_payment_delete',
            'sales_add','sales_edit','sales_delete','sales_view',
            'sales_return_add','sales_return_edit','sales_return_delete','sales_return_view',
            'sales_payment_view','sales_payment_add','sales_payment_delete',
            'sales_return_payment_view','sales_return_payment_add','sales_return_payment_delete',
            // Stock / Warehouse
            'stock_transfer_add','stock_transfer_edit','stock_transfer_delete','stock_transfer_view',
            'stock_adjustment_add','stock_adjustment_edit','stock_adjustment_delete','stock_adjustment_view',
            'warehouse_add','warehouse_edit','warehouse_delete','warehouse_view',
            // Accounts
            'accounts_add','accounts_edit','accounts_delete','accounts_view',
            'money_transfer_add','money_transfer_edit','money_transfer_delete','money_transfer_view',
            'money_deposit_add','money_deposit_edit','money_deposit_delete','money_deposit_view',
            'cash_transactions',
            'tills_view','tills_add','tills_edit','tills_delete',
            'cashier_shifts_manage','z_report',
            // Services / Quotations
            'services_add','services_edit','services_delete','services_view',
            'import_services',
            'quotation_add','quotation_edit','quotation_delete','quotation_view',
            // Messaging (sending + store-facing templates only — no API keys)
            'send_sms','sms_template_view','sms_template_edit',
            'send_email',
            // Coupons
            'discountCouponAdd','discountCouponEdit','discountCouponDelete','discountCouponView',
            'customerCouponAdd','customerCouponEdit','customerCouponDelete','customerCouponView',
            // Reports
            'sales_report','purchase_report','expense_report','profit_report',
            'stock_report','item_sales_report','expired_items_report',
            'purchase_payments_report','sales_payments_report',
            'sales_tax_report','purchase_tax_report',
            'supplier_items_report','seller_points_report',
                        'return_items_report','stock_transfer_report',
            'sales_summary_report','sales_return_payments',
            'purchase_return_report','sales_return_report',
            'customer_orders_report',
            // FSTR (tax) reports are intentionally NOT granted here —
            // they stay flagged for the Store Admin role only.
            // Fashion Intelligence
            'variant_attribute_report','sell_through_report','reorder_suggestion_report',
            'promotions_manage',
            // Advanced
            'cust_adv_payments_add','cust_adv_payments_edit','cust_adv_payments_delete','cust_adv_payments_view',
            'show_all_users_sales_invoices','show_all_users_sales_return_invoices',
            'show_all_users_purchase_invoices','show_all_users_purchase_return_invoices',
            'show_all_users_expenses','show_all_users_quotations',
            'show_purchase_price',
            // Settings — store-affecting only. Technical settings stay with
            // the vendor/admin: subscription (license), email provider
            // (smtp_settings), SMS API keys, payment gateways, NIN and
            // system_settings are deliberately NOT granted to partners.
            'expiry_settings',
            'approval_settings_edit','approval_logs_view','can_approve',
            'online_store_view','online_store_edit','online_store_orders',
            'attendance_edit','attendance_view'
        );
    }

    /**
     * Return the default permission list for a standard role name.
     * Used as a fallback when db_permissions is empty for a role.
     */
    public function get_role_default_permissions($role_name) {
        if (empty($role_name)) {
            return array();
        }

        $role_name = trim($role_name);
        $maps = array(
            'Business Owner' => $this->get_business_owner_permissions(),
            'Partner'        => $this->get_partner_permissions(),
            'Manager'        => $this->get_manager_permissions(),
            'Cashier'        => $this->get_cashier_permissions(),
            'Accountant'     => $this->get_accountant_permissions(),
            'Inventory Officer' => $this->get_inventory_officer_permissions(),
            'Admin'          => $this->get_business_owner_permissions(),
        );

        foreach ($maps as $name => $perms) {
            if (stripos($role_name, $name) !== false) {
                return $perms;
            }
        }

        // Loose fallback for owner/manager/admin variants (e.g. "Owner", "Store Owner")
        if (stripos($role_name, 'owner') !== false || stripos($role_name, 'admin') !== false) {
            return $this->get_business_owner_permissions();
        }

        return array();
    }

    // ============================================================
    // DEFAULT EXPENSE CATEGORIES
    // ============================================================

    /**
     * Create default expense categories for a store
     */
    public function create_default_expense_categories($store_id) {
        $results = array('count' => 0, 'errors' => array());

        $categories = array(
            array('name' => 'Rent / Shop Lease',           'description' => 'Monthly rent or lease payments for the business premises'),
            array('name' => 'Staff Salaries & Wages',       'description' => 'Regular salaries and wages paid to staff members'),
            array('name' => 'Staff Allowances',             'description' => 'Additional allowances for staff such as transport, meal, etc.'),
            array('name' => 'Electricity / PHCN',           'description' => 'Electricity bills and power supply charges'),
            array('name' => 'Generator Fuel / Diesel / Petrol', 'description' => 'Fuel costs for backup generators'),
            array('name' => 'Internet & Data Subscription', 'description' => 'Monthly internet and mobile data subscriptions'),
            array('name' => 'POS / Bank Charges',           'description' => 'Transaction fees, POS charges, and bank service charges'),
            array('name' => 'Delivery / Logistics',         'description' => 'Costs for delivering goods to customers'),
            array('name' => 'Transportation',               'description' => 'General transport and fuel costs for business operations'),
            array('name' => 'Supplier Payments',            'description' => 'Payments made to suppliers for goods and stock'),
            array('name' => 'Stock Replenishment',          'description' => 'Costs associated with restocking inventory'),
            array('name' => 'Packaging Materials',          'description' => 'Bags, boxes, tapes, and other packaging supplies'),
            array('name' => 'Repairs & Maintenance',        'description' => 'Equipment and premises repairs and maintenance'),
            array('name' => 'Cleaning & Sanitation',        'description' => 'Cleaning supplies and sanitation services'),
            array('name' => 'Security',                     'description' => 'Security personnel and security system costs'),
            array('name' => 'Shop Supplies',                'description' => 'General shop supplies and consumables'),
            array('name' => 'Marketing & Advertising',    'description' => 'Promotions, flyers, social media ads, and marketing campaigns'),
            array('name' => 'Printing & Stationery',        'description' => 'Receipts, invoices, business cards, and office stationery'),
            array('name' => 'Business Registration / License', 'description' => 'Annual registration fees, licenses, and permits'),
            array('name' => 'Taxes / Levies',               'description' => 'Government taxes, levies, and statutory payments'),
            array('name' => 'Waste Disposal',               'description' => 'Garbage collection and waste management fees'),
            array('name' => 'Staff Feeding / Welfare',      'description' => 'Staff meals, gifts, celebrations, and welfare programs'),
            array('name' => 'Customer Refunds',             'description' => 'Refunds issued to customers for returns or complaints'),
            array('name' => 'Damaged / Expired Goods',      'description' => 'Loss from damaged, expired, or unsellable inventory'),
            array('name' => 'Discounts / Promotions',       'description' => 'Discounts given to customers as part of promotions'),
            array('name' => 'Software Subscription',        'description' => 'Monthly or annual software and SaaS subscriptions'),
            array('name' => 'Phone Calls / Airtime',        'description' => 'Business phone calls and mobile airtime purchases'),
            array('name' => 'Professional Fees',            'description' => 'Legal, accounting, and other professional service fees'),
            array('name' => 'Miscellaneous',              'description' => 'Other expenses not covered by specific categories')
        );

        foreach ($categories as $cat) {
            // Check if category already exists (case-insensitive)
            $exists = $this->db->query(
                "SELECT id FROM db_expense_category WHERE store_id = ? AND UPPER(category_name) = UPPER(?)",
                array($store_id, $cat['name'])
            )->num_rows();

            if ($exists > 0) {
                continue; // Skip duplicate
            }

            // Generate category code
            $maxid = $this->db->query("SELECT COALESCE(MAX(id),0)+1 AS maxid FROM db_expense_category")->row()->maxid;
            $cat_code = 'EC' . str_pad($maxid, 4, '0', STR_PAD_LEFT);

            $data = array(
                'store_id'     => $store_id,
                'category_code'=> $cat_code,
                'category_name'=> $cat['name'],
                'description'  => $cat['description'],
                'created_by'   => 'System',
                'status'       => 1
            );

            if ($this->db->insert('db_expense_category', $data)) {
                $results['count']++;
            } else {
                $results['errors'][] = "Failed to create category: {$cat['name']}";
            }
        }

        return $results;
    }

    // ============================================================
    // INDUSTRY-SPECIFIC DEFAULT DATA
    // ============================================================

    /**
     * Map every business profile to a demo seed pack. Packs preload the
     * master data that profile needs to demo its workflow on day one
     * (units, item categories, recipe categories, brands, sample items and
     * services). Transactional data (sales, purchases, stock transfers)
     * is intentionally NEVER seeded — new deployments start with a clean
     * transaction slate.
     */
    private function _industry_pack_map() {
        return array(
            'general_retail'       => 'retail_basic',
            'multi_branch_retail'  => 'retail_basic',
            'supermarket'          => 'grocery',
            'mini_mart'            => 'grocery',
            'grocery_store'        => 'grocery',
            'provision_store'      => 'grocery',
            'convenience_store'    => 'grocery',
            'pharmacy'             => 'pharma',
            'medical_store'        => 'pharma',
            'restaurant'           => 'food_service',
            'fast_food'            => 'food_service',
            'cafe'                 => 'food_service',
            'pizza_shop'           => 'food_service',
            'shawarma'             => 'food_service',
            'juice_bar'            => 'food_service',
            'buka'                 => 'food_service',
            'canteen'              => 'food_service',
            'electronics'          => 'electronics',
            'computer_store'       => 'electronics',
            'gadget_store'         => 'electronics',
            'appliance_store'      => 'electronics',
            'phone_accessories'    => 'phone_accessories',
            'fashion'              => 'fashion',
            'boutique'             => 'fashion',
            'shoe_store'           => 'fashion',
            'beauty_cosmetics'     => 'beauty_retail',
            'beauty_spa'           => 'beauty_services',
            'salon_barbershop'     => 'beauty_services',
            'makeup_artist'        => 'beauty_services',
            'laundry'              => 'laundry',
            'bakery_cake_studio'   => 'bakery',
            'bookshop'             => 'bookshop',
            'building_materials'   => 'building_materials',
            'paint_store'          => 'paint_store',
            'plumbing_store'       => 'plumbing_store',
            'furniture'            => 'furniture',
            'distributor'          => 'wholesale',
            'wholesaler'           => 'wholesale',
            'butcher'              => 'butchery',
            'frozen_foods_retailer'=> 'frozen',
            'clinic'               => 'healthcare_services',
            'hospital'             => 'healthcare_services',
            'diagnostic_centre'    => 'healthcare_services',
            'perfume_shop'         => 'perfume',
            'jewellery_store'      => 'jewellery',
            'agro_dealer'          => 'agro',
            'feed_store'           => 'agro',
            'auto_parts'           => 'auto_parts',
            'tyre_shop'            => 'auto_parts',
            'car_dealership'       => 'auto_dealer',
            'printing'             => 'printing',
            'tailoring'            => 'tailoring',
            'manufacturer'         => 'manufacturing',
            'service_business'     => 'services',
            'online_store'         => 'digital',
            'creator'              => 'digital',
        );
    }

    /**
     * Demo seed packs. Each pack:
     *  units            => unit_name => [shortcode, parent_shortcode, factor, is_default]
     *  item_categories  => name => description
     *  recipe_categories=> [names]                       (production-style profiles)
     *  brands           => [names]                       (optional)
     *  services         => name => [price, duration, appointment] (optional)
     *  items            => name => [category, unit, sales_price, purchase_price, stock] (optional)
     */
    private function _industry_seed_packs() {
        return array(
            'retail_basic' => array(
                'units' => array(
                    'Piece'   => array('PCS', null, 1, 1),
                    'Pack'    => array('PACK', null, 1, 0),
                    'Box'     => array('BOX', null, 1, 0),
                    'Kilogram'=> array('KG',  null, 1, 0),
                ),
                'item_categories' => array(
                    'Groceries & Essentials' => 'Everyday groceries and household essentials',
                    'Beverages'              => 'Soft drinks, juices, water and other beverages',
                    'Household & Cleaning'   => 'Cleaning supplies and household items',
                    'Personal Care'          => 'Toiletries and personal care products',
                    'Stationery'             => 'Office and school stationery',
                    'General Merchandise'    => 'Miscellaneous retail items',
                ),
            ),
            'grocery' => array(
                'units' => array(
                    'Piece'   => array('PCS', null, 1, 1),
                    'Pack'    => array('PACK', null, 1, 0),
                    'Kilogram'=> array('KG',  null, 1, 0),
                    'Gram'    => array('g',   'KG', 1000, 0),
                    'Litre'   => array('L',   null, 1, 0),
                    'Crate'   => array('CRATE', null, 1, 0),
                ),
                'item_categories' => array(
                    'Beverages'             => 'Soft drinks, juices, water, malt and energy drinks',
                    'Snacks & Confectionery'=> 'Biscuits, sweets, chocolates and snacks',
                    'Grains & Staples'      => 'Rice, beans, garri, flour, pasta and other staples',
                    'Dairy & Eggs'          => 'Milk, yoghurt, butter, cheese and eggs',
                    'Fresh Produce'         => 'Fruits, vegetables and fresh foods',
                    'Frozen Foods'          => 'Frozen chicken, fish, meat and vegetables',
                    'Cooking Essentials'    => 'Oils, spices, seasonings and condiments',
                    'Household & Cleaning'  => 'Detergents, soaps and cleaning supplies',
                    'Personal Care'         => 'Toiletries and personal care products',
                    'Baby Products'         => 'Baby food, diapers and baby care',
                ),
            ),
            'pharma' => array(
                'units' => array(
                    'Tablet'  => array('TAB', null, 1, 0),
                    'Capsule' => array('CAP', null, 1, 0),
                    'Card'    => array('CARD', null, 1, 1),
                    'Pack'    => array('PACK', null, 1, 0),
                    'Bottle'  => array('BTL', null, 1, 0),
                    'Tube'    => array('TUBE', null, 1, 0),
                    'Vial'    => array('VIAL', null, 1, 0),
                    'Piece'   => array('PCS', null, 1, 0),
                ),
                'item_categories' => array(
                    'Analgesics & Pain Relief'  => 'Pain relievers and anti-inflammatory medicines',
                    'Antibiotics'               => 'Antibiotic medicines (prescription)',
                    'Cold, Flu & Allergy'       => 'Cough syrups, antihistamines and cold remedies',
                    'Vitamins & Supplements'    => 'Multivitamins, supplements and wellness products',
                    'Digestive Health'          => 'Antacids, ORS and digestive remedies',
                    'First Aid & Wound Care'    => 'Bandages, antiseptics and wound care',
                    'Mother & Baby'             => 'Mother and baby care products',
                    'Personal Care & Hygiene'   => 'Soaps, sanitizers and hygiene products',
                    'Medical Devices'           => 'Thermometers, BP monitors and devices',
                ),
            ),
            'food_service' => array(
                'units' => array(
                    'Plate'   => array('PLATE', null, 1, 1),
                    'Portion' => array('PORTION', null, 1, 0),
                    'Cup'     => array('CUP', null, 1, 0),
                    'Bottle'  => array('BTL', null, 1, 0),
                    'Piece'   => array('PCS', null, 1, 0),
                    'Kilogram'=> array('KG', null, 1, 0),
                ),
                'item_categories' => array(
                    'Mains'               => 'Main dishes and full meals',
                    'Starters & Small Chops'=> 'Appetizers, small chops and finger foods',
                    'Sides & Extras'      => 'Side dishes, extras and add-ons',
                    'Drinks'              => 'Soft drinks, juices, water and beverages',
                    'Desserts'            => 'Desserts and sweet treats',
                    'Combos & Platters'   => 'Combo meals and sharing platters',
                    'Ingredients'         => 'Raw ingredients and kitchen supplies (not for direct sale)',
                ),
                'recipe_categories' => array(
                    'Mains', 'Starters', 'Drinks', 'Desserts', 'Combos',
                ),
            ),
            'electronics' => array(
                'units' => array(
                    'Piece' => array('PCS', null, 1, 1),
                    'Box'   => array('BOX', null, 1, 0),
                    'Set'   => array('SET', null, 1, 0),
                ),
                'item_categories' => array(
                    'Phones & Tablets'        => 'Mobile phones and tablets',
                    'Laptops & Computers'     => 'Laptops, desktops and computer accessories',
                    'TVs & Audio'             => 'Televisions, speakers and audio equipment',
                    'Home Appliances'         => 'Fridges, freezers, washers and home appliances',
                    'Power & Solar'           => 'Inverters, generators, solar and power solutions',
                    'Accessories'             => 'Chargers, cables, cases and other accessories',
                ),
                'brands' => array('Samsung', 'LG', 'Sony', 'HP', 'Tecno', 'Hisense'),
            ),
            'phone_accessories' => array(
                'units' => array(
                    'Piece' => array('PCS', null, 1, 1),
                    'Pack'  => array('PACK', null, 1, 0),
                ),
                'item_categories' => array(
                    'Phone Cases & Covers'    => 'Cases, covers and pouches',
                    'Chargers & Cables'       => 'Chargers, cables and power banks',
                    'Earphones & Audio'       => 'Earphones, headsets and bluetooth audio',
                    'Screen Protectors'       => 'Tempered glass and screen protectors',
                    'Smart Devices'           => 'Smart watches and wearables',
                    'Phone Parts & Repairs'   => 'Screens, batteries and replacement parts',
                ),
            ),
            'fashion' => array(
                'units' => array(
                    'Piece' => array('PCS', null, 1, 1),
                    'Pair'  => array('PAIR', null, 1, 0),
                    'Set'   => array('SET', null, 1, 0),
                    'Yard'  => array('YD', null, 1, 0),
                ),
                'item_categories' => array(
                    'Men\'s Wear'          => 'Shirts, trousers, suits and menswear',
                    'Women\'s Wear'        => 'Dresses, tops, skirts and womenswear',
                    'Kids\' Wear'          => 'Children\'s clothing',
                    'Traditional Wear'     => 'Native and traditional attire',
                    'Shoes & Footwear'     => 'Shoes, sneakers, heels and sandals',
                    'Bags & Accessories'   => 'Bags, belts, jewellery and accessories',
                    'Underwear & Lingerie'=> 'Undergarments and lingerie',
                ),
                'brands' => array('Generic', 'Local Designer', 'Imported'),
            ),
            'beauty_retail' => array(
                'units' => array(
                    'Piece'  => array('PCS', null, 1, 1),
                    'Bottle' => array('BTL', null, 1, 0),
                    'Jar'    => array('JAR', null, 1, 0),
                    'Set'    => array('SET', null, 1, 0),
                ),
                'item_categories' => array(
                    'Skincare'            => 'Creams, serums, cleansers and skincare',
                    'Makeup'              => 'Foundations, lipsticks, palettes and makeup',
                    'Hair Care'           => 'Shampoos, conditioners, treatments and hair products',
                    'Fragrance'           => 'Perfumes and body sprays',
                    'Tools & Accessories' => 'Brushes, sponges, mirrors and tools',
                ),
                'services' => array(
                    'Product Consultation' => array('price' => 0, 'duration' => '15 min', 'appointment' => 0),
                ),
            ),
            'beauty_services' => array(
                'units' => array(
                    'Session' => array('SESS', null, 1, 1),
                    'Piece'   => array('PCS', null, 1, 0),
                ),
                'item_categories' => array(
                    'Retail Products' => 'Products sold over the counter',
                    'Supplies'        => 'Consumables and supplies used for services',
                ),
                'services' => array(
                    'Haircut / Styling' => array('price' => 5000,  'duration' => '45 min', 'appointment' => 1),
                    'Braiding / Weaving'=> array('price' => 15000, 'duration' => '2 hrs',   'appointment' => 1),
                    'Facial Treatment'  => array('price' => 20000, 'duration' => '1 hr',    'appointment' => 1),
                    'Manicure & Pedicure'=> array('price' => 8000, 'duration' => '1 hr',    'appointment' => 1),
                    'Massage Therapy'   => array('price' => 25000, 'duration' => '1 hr',    'appointment' => 1),
                    'Makeup Session'    => array('price' => 30000, 'duration' => '1.5 hrs', 'appointment' => 1),
                ),
            ),
            'laundry' => array(
                'units' => array(
                    'Piece'   => array('PCS', null, 1, 1),
                    'Kilogram'=> array('KG', null, 1, 0),
                    'Set'     => array('SET', null, 1, 0),
                ),
                'item_categories' => array(
                    'Garments'         => 'Clothing items received for cleaning',
                    'Bedding & Home'   => 'Bedsheets, duvets, curtains and home textiles',
                    'Detergents & Supplies' => 'Detergents and cleaning supplies',
                ),
                'services' => array(
                    'Wash & Iron'   => array('price' => 1000, 'duration' => '24 hrs', 'appointment' => 0),
                    'Wash Only'     => array('price' => 700,  'duration' => '24 hrs', 'appointment' => 0),
                    'Iron Only'     => array('price' => 500,  'duration' => '12 hrs', 'appointment' => 0),
                    'Dry Cleaning'  => array('price' => 2500, 'duration' => '48 hrs', 'appointment' => 0),
                    'Express Service'=> array('price' => 2000, 'duration' => '6 hrs', 'appointment' => 0),
                ),
            ),
            'bakery' => array(
                'units' => array(
                    'Piece'   => array('PCS', null, 1, 1),
                    'Kilogram'=> array('KG', null, 1, 0),
                    'Tray'    => array('TRAY', null, 1, 0),
                    'Pack'    => array('PACK', null, 1, 0),
                ),
                'item_categories' => array(
                    'Breads'               => 'Fresh breads and loaves',
                    'Cakes'                => 'Celebration and everyday cakes',
                    'Pastries & Small Chops'=> 'Meat pies, doughnuts, chin chin and pastries',
                    'Ingredients'          => 'Flour, sugar, butter and baking ingredients',
                    'Packaging'            => 'Cake boxes, bags and packaging materials',
                ),
                'recipe_categories' => array(
                    'Breads', 'Cakes', 'Pastries', 'Icings & Fillings',
                ),
            ),
            'bookshop' => array(
                'units' => array(
                    'Piece' => array('PCS', null, 1, 1),
                    'Set'   => array('SET', null, 1, 0),
                    'Pack'  => array('PACK', null, 1, 0),
                ),
                'item_categories' => array(
                    'Fiction'            => 'Novels and story books',
                    'Non-Fiction'        => 'Biographies, self-help and reference',
                    'Textbooks'          => 'School and academic textbooks',
                    'Children\'s Books'  => 'Children\'s stories and activity books',
                    'Religious'          => 'Bibles, Qurans and religious texts',
                    'Stationery'         => 'Pens, notebooks and school supplies',
                ),
            ),
            'building_materials' => array(
                'units' => array(
                    'Bag'     => array('BAG', null, 1, 1),
                    'Piece'   => array('PCS', null, 1, 0),
                    'Length'  => array('LEN', null, 1, 0),
                    'Sheet'   => array('SHEET', null, 1, 0),
                    'Kilogram'=> array('KG', null, 1, 0),
                    'Trip'    => array('TRIP', null, 1, 0),
                ),
                'item_categories' => array(
                    'Cement & Blocks'   => 'Cement, blocks and concrete products',
                    'Roofing'           => 'Roofing sheets, nails and accessories',
                    'Timber & Boards'   => 'Wood, plywood and boards',
                    'Plumbing'          => 'Pipes, fittings and plumbing materials',
                    'Electrical'        => 'Cables, fittings and electrical materials',
                    'Paint & Finishing' => 'Paints, tiles and finishing materials',
                    'Tools & Hardware'  => 'Hand tools and hardware',
                ),
            ),
            'paint_store' => array(
                'units' => array(
                    'Litre'  => array('L', null, 1, 1),
                    'Gallon' => array('GAL', null, 1, 0),
                    'Bucket' => array('BKT', null, 1, 0),
                    'Piece'  => array('PCS', null, 1, 0),
                ),
                'item_categories' => array(
                    'Emulsion Paints'        => 'Water-based interior and exterior emulsions',
                    'Gloss & Oil Paints'     => 'Oil-based gloss and enamel paints',
                    'Primers & Undercoats'   => 'Primers, sealers and undercoats',
                    'Thinners & Solvents'    => 'Thinners, turpentine and solvents',
                    'Brushes & Rollers'      => 'Paint brushes, rollers and trays',
                    'Waterproofing'          => 'Waterproofing and protective coatings',
                ),
            ),
            'plumbing_store' => array(
                'units' => array(
                    'Piece'  => array('PCS', null, 1, 1),
                    'Length' => array('LEN', null, 1, 0),
                    'Meter'  => array('M', null, 1, 0),
                    'Roll'   => array('ROLL', null, 1, 0),
                ),
                'item_categories' => array(
                    'Pipes & Fittings'    => 'PVC, PPR pipes and fittings',
                    'Valves & Taps'       => 'Taps, valves and faucets',
                    'Water Tanks'         => 'Storage tanks and drums',
                    'Bathroom Fixtures'   => 'WC, basins, showers and fixtures',
                    'Tools & Accessories' => 'Plumbing tools and accessories',
                ),
            ),
            'furniture' => array(
                'units' => array(
                    'Piece' => array('PCS', null, 1, 1),
                    'Set'   => array('SET', null, 1, 0),
                ),
                'item_categories' => array(
                    'Living Room'      => 'Sofas, centre tables and TV stands',
                    'Bedroom'          => 'Beds, wardrobes and dressers',
                    'Office'           => 'Desks, chairs and office furniture',
                    'Kitchen & Dining' => 'Dining sets and kitchen furniture',
                    'Outdoor'          => 'Outdoor and garden furniture',
                ),
                'services' => array(
                    'Custom Furniture Order' => array('price' => 0, 'duration' => '', 'appointment' => 1),
                    'Delivery & Assembly'    => array('price' => 0, 'duration' => '', 'appointment' => 0),
                ),
            ),
            'wholesale' => array(
                'units' => array(
                    'Carton'  => array('CTN', null, 1, 1),
                    'Pack'    => array('PACK', null, 1, 0),
                    'Piece'   => array('PCS', null, 1, 0),
                    'Bag'     => array('BAG', null, 1, 0),
                ),
                'item_categories' => array(
                    'Beverages'          => 'Drinks and beverages (bulk)',
                    'Food & Groceries'   => 'Packaged foods and groceries (bulk)',
                    'Household'          => 'Household products (bulk)',
                    'Personal Care'      => 'Personal care products (bulk)',
                    'General Merchandise'=> 'Other wholesale goods',
                ),
            ),
            'butchery' => array(
                'units' => array(
                    'Kilogram'=> array('KG', null, 1, 1),
                    'Gram'    => array('g', 'KG', 1000, 0),
                    'Piece'   => array('PCS', null, 1, 0),
                ),
                'item_categories' => array(
                    'Beef'             => 'Beef cuts and portions',
                    'Goat & Lamb'      => 'Goat meat and lamb cuts',
                    'Pork'             => 'Pork cuts and products',
                    'Chicken & Poultry'=> 'Whole chicken and poultry parts',
                    'Fish & Seafood'   => 'Fresh fish and seafood',
                    'Processed Meats'  => 'Sausages, suya cuts and processed meats',
                ),
            ),
            'frozen' => array(
                'units' => array(
                    'Kilogram'=> array('KG', null, 1, 1),
                    'Pack'    => array('PACK', null, 1, 0),
                    'Piece'   => array('PCS', null, 1, 0),
                ),
                'item_categories' => array(
                    'Frozen Chicken'      => 'Frozen whole chicken and parts',
                    'Frozen Fish'         => 'Frozen fish and seafood',
                    'Frozen Meat'         => 'Frozen beef, goat and other meats',
                    'Frozen Vegetables'   => 'Frozen vegetables and fries',
                    'Ice Cream & Desserts'=> 'Ice cream and frozen desserts',
                    'Ready Meals'         => 'Frozen ready-to-cook meals',
                ),
            ),
            'healthcare_services' => array(
                'units' => array(
                    'Session' => array('SESS', null, 1, 1),
                    'Pack'    => array('PACK', null, 1, 0),
                    'Piece'   => array('PCS', null, 1, 0),
                ),
                'item_categories' => array(
                    'Medical Supplies' => 'Consumable medical supplies',
                    'Pharmacy Items'   => 'Dispensed medicines and OTC items',
                    'Lab Consumables'  => 'Laboratory reagents and consumables',
                ),
                'services' => array(
                    'Consultation'        => array('price' => 10000, 'duration' => '30 min', 'appointment' => 1),
                    'Laboratory Test'     => array('price' => 0,     'duration' => '',        'appointment' => 1),
                    'Vaccination'         => array('price' => 0,     'duration' => '15 min',  'appointment' => 1),
                    'Follow-up Visit'     => array('price' => 5000,  'duration' => '15 min',  'appointment' => 1),
                ),
            ),
            'perfume' => array(
                'units' => array(
                    'Litre'      => array('L',   null, 1,        0),
                    'Millilitre' => array('ml',  'L',  1000,     1),
                    'Fluid Ounce'=> array('fl oz','L',  33.814,  0),
                    'Kilogram'   => array('KG',  null, 1,        0),
                    'Gram'       => array('g',   'KG', 1000,     0),
                    'Piece'      => array('PCS', null, 1,        0),
                    'Bottle'     => array('',    null, 1,        0),
                    'Decant'     => array('',    null, 1,        0),
                ),
                'item_categories' => array(
                    'Fragrance Oils & Concentrates' => 'Pure perfume oils, dupes and concentrates used in blending',
                    'Solvents & Bases'              => 'Perfumer\'s alcohol, DPG, IPM, distilled water, fixatives',
                    'Bottles & Packaging'           => 'Glass bottles, atomizers, caps, labels, boxes, pouches',
                    'Finished Perfumes'             => 'Bottled fragrances ready for sale',
                    'Decants & Samples'             => 'Small-volume decants and sample vials',
                    'Tester Units'                  => 'Open bottles used as counter testers',
                ),
                'recipe_categories' => array(
                    'Extrait de Parfum',
                    'Eau de Parfum',
                    'Eau de Toilette',
                    'Eau de Cologne',
                    'Perfume Oil / Attar',
                    'Home Fragrance',
                ),
                'services' => array(
                    'Bespoke Blend Consultation' => array('price' => 0, 'duration' => '45 min', 'appointment' => 1),
                ),
            ),
            'jewellery' => array(
                'units' => array(
                    'Piece' => array('PCS', null, 1, 1),
                    'Pair'  => array('PAIR', null, 1, 0),
                    'Set'   => array('SET', null, 1, 0),
                ),
                'item_categories' => array(
                    'Rings'     => 'Engagement, wedding and fashion rings',
                    'Necklaces' => 'Chains, pendants and necklaces',
                    'Earrings'  => 'Studs, hoops and drop earrings',
                    'Bracelets' => 'Bangles and bracelets',
                    'Watches'   => 'Wrist watches',
                ),
            ),
            'agro' => array(
                'units' => array(
                    'Kilogram'=> array('KG', null, 1, 1),
                    'Bag'     => array('BAG', null, 1, 0),
                    'Litre'   => array('L', null, 1, 0),
                    'Piece'   => array('PCS', null, 1, 0),
                ),
                'item_categories' => array(
                    'Seeds & Seedlings'  => 'Seeds, seedlings and planting materials',
                    'Fertilizers'        => 'Fertilizers and soil conditioners',
                    'Agro-Chemicals'     => 'Herbicides, pesticides and fungicides',
                    'Animal Feed'        => 'Livestock and poultry feed',
                    'Veterinary'         => 'Animal health and veterinary products',
                    'Farm Tools'         => 'Farm tools and equipment',
                ),
            ),
            'auto_parts' => array(
                'units' => array(
                    'Piece' => array('PCS', null, 1, 1),
                    'Set'   => array('SET', null, 1, 0),
                    'Pair'  => array('PAIR', null, 1, 0),
                    'Litre' => array('L', null, 1, 0),
                ),
                'item_categories' => array(
                    'Engine Parts'      => 'Engine components and parts',
                    'Brakes'            => 'Brake pads, discs and brake parts',
                    'Suspension'        => 'Shocks, struts and suspension parts',
                    'Electrical'        => 'Batteries, alternators and electrical parts',
                    'Filters & Fluids'  => 'Oil, filters and fluids',
                    'Tyres & Wheels'    => 'Tyres, rims and wheel accessories',
                    'Body & Accessories'=> 'Body parts and accessories',
                ),
            ),
            'auto_dealer' => array(
                'units' => array(
                    'Piece' => array('PCS', null, 1, 1),
                    'Set'   => array('SET', null, 1, 0),
                ),
                'item_categories' => array(
                    'Vehicles'    => 'Cars and vehicles for sale',
                    'Spare Parts' => 'Vehicle spare parts',
                    'Accessories' => 'Car accessories and add-ons',
                ),
                'services' => array(
                    'Vehicle Inspection' => array('price' => 0, 'duration' => '1 hr', 'appointment' => 1),
                    'Test Drive Booking' => array('price' => 0, 'duration' => '30 min', 'appointment' => 1),
                ),
            ),
            'printing' => array(
                'units' => array(
                    'Piece' => array('PCS', null, 1, 1),
                    'Ream'  => array('REAM', null, 1, 0),
                    'Sheet' => array('SHEET', null, 1, 0),
                    'Pack'  => array('PACK', null, 1, 0),
                ),
                'item_categories' => array(
                    'Business Cards'       => 'Business cards and complimentary cards',
                    'Flyers & Banners'     => 'Flyers, banners and posters',
                    'Branded Merchandise'  => 'Branded shirts, mugs and merchandise',
                    'Stationery'           => 'Letterheads, envelopes and stationery',
                    'Large Format'         => 'Billboards, signage and large format prints',
                ),
                'services' => array(
                    'Graphic Design' => array('price' => 0, 'duration' => '', 'appointment' => 0),
                ),
            ),
            'tailoring' => array(
                'units' => array(
                    'Piece' => array('PCS', null, 1, 1),
                    'Yard'  => array('YD', null, 1, 0),
                    'Set'   => array('SET', null, 1, 0),
                ),
                'item_categories' => array(
                    'Fabrics'              => 'Fabric materials by the yard',
                    'Ready-Made'           => 'Ready-made garments',
                    'Accessories & Notions'=> 'Zips, buttons, threads and notions',
                ),
                'services' => array(
                    'Bespoke / Custom Order' => array('price' => 0, 'duration' => '', 'appointment' => 1),
                    'Alterations'            => array('price' => 0, 'duration' => '', 'appointment' => 0),
                ),
            ),
            'manufacturing' => array(
                'units' => array(
                    'Kilogram'=> array('KG', null, 1, 1),
                    'Litre'   => array('L', null, 1, 0),
                    'Piece'   => array('PCS', null, 1, 0),
                    'Pack'    => array('PACK', null, 1, 0),
                ),
                'item_categories' => array(
                    'Raw Materials'   => 'Raw materials and inputs',
                    'Work In Progress'=> 'Semi-finished goods',
                    'Finished Goods'  => 'Finished products ready for sale',
                    'Packaging'       => 'Packaging materials',
                ),
                'recipe_categories' => array(
                    'Product Formulas',
                ),
            ),
            'services' => array(
                'units' => array(
                    'Session' => array('SESS', null, 1, 1),
                    'Hour'    => array('HR', null, 1, 0),
                    'Piece'   => array('PCS', null, 1, 0),
                ),
                'item_categories' => array(
                    'Service Packages' => 'Packaged service offerings',
                    'Supplies'         => 'Materials and supplies used for service delivery',
                    'Retail Products'  => 'Products sold alongside services',
                ),
                'services' => array(
                    'Consultation'     => array('price' => 0, 'duration' => '1 hr', 'appointment' => 1),
                    'Standard Service' => array('price' => 0, 'duration' => '',      'appointment' => 1),
                    'Emergency Call-out'=> array('price' => 0, 'duration' => '',    'appointment' => 1),
                ),
            ),
            'digital' => array(
                'units' => array(
                    'Download' => array('DL', null, 1, 1),
                    'License'  => array('LIC', null, 1, 0),
                    'Piece'    => array('PCS', null, 1, 0),
                ),
                'item_categories' => array(
                    'Digital Downloads' => 'Ebooks, templates and digital files',
                    'Online Courses'    => 'Courses and learning programs',
                    'Memberships'       => 'Memberships and subscriptions',
                    'Physical Merch'    => 'Physical merchandise and goods',
                ),
            ),
        );
    }

    /**
     * Seed master data a specific industry needs to be productive on day one.
     * Idempotent: rows are matched by name before insert, safe to re-run and
     * safe to call when a store switches industry mid-life.
     *
     * @param int    $store_id
     * @param string $industry_type  e.g. 'perfume_shop'
     * @return array counts created
     */
    public function seed_industry_defaults($store_id, $industry_type) {
        $results = array(
            'units_created' => 0,
            'categories_created' => 0,
            'recipe_categories_created' => 0,
            'brands_created' => 0,
            'services_created' => 0,
        );
        if (empty($store_id) || empty($industry_type)) {
            return $results;
        }

        $packs = $this->_industry_seed_packs();
        $map   = $this->_industry_pack_map();
        $pack_key = isset($map[$industry_type]) ? $map[$industry_type] : 'retail_basic';

        if (empty($packs[$pack_key])) {
            return $results;
        }
        $cfg = $packs[$pack_key];

        // --- Units (with parent/child conversions, e.g. Litre = 1000 ml) ---
        if ($this->db->table_exists('db_units')) {
            $unit_ids = array();
            foreach ($cfg['units'] as $unit_name => $u) {
                list($shortcode, $parent_sc, $factor, $is_default) = $u;
                $exists = $this->db->where('store_id', $store_id)
                    ->where('unit_name', $unit_name)->get('db_units')->row();
                $parent_id = null;
                if ($parent_sc && isset($unit_ids[$parent_sc])) {
                    $parent_id = $unit_ids[$parent_sc];
                }
                if ($exists) {
                    $unit_ids[$shortcode] = $exists->id;
                    // Repair hierarchy/conversion on existing rows — an earlier
                    // version of this map seeded the parent/child direction backwards
                    $fix = array();
                    if ($this->db->field_exists('parent_unit_id', 'db_units')
                        && (int)($exists->parent_unit_id ?? 0) !== (int)($parent_id ?? 0)) {
                        $fix['parent_unit_id'] = $parent_id;
                    }
                    if ($this->db->field_exists('conversion_factor', 'db_units')
                        && abs((float)($exists->conversion_factor ?? 0) - (float)$factor) > 0.000001) {
                        $fix['conversion_factor'] = $factor;
                    }
                    if ($this->db->field_exists('is_default', 'db_units')
                        && (int)($exists->is_default ?? 0) !== (int)$is_default) {
                        $fix['is_default'] = $is_default;
                    }
                    if ($this->db->field_exists('shortcode', 'db_units')
                        && (string)($exists->shortcode ?? '') !== (string)$shortcode) {
                        $fix['shortcode'] = $shortcode;
                    }
                    if (!empty($fix)) {
                        $this->db->where('id', $exists->id)->update('db_units', $fix);
                    }
                    continue;
                }
                $data = array(
                    'store_id'          => $store_id,
                    'unit_name'         => $unit_name,
                    'description'       => '',
                    'status'            => 1,
                );
                if ($this->db->field_exists('shortcode', 'db_units')) {
                    $data['shortcode'] = $shortcode;
                }
                if ($parent_id !== null && $this->db->field_exists('parent_unit_id', 'db_units')) {
                    $data['parent_unit_id'] = $parent_id;
                }
                if ($this->db->field_exists('conversion_factor', 'db_units')) {
                    $data['conversion_factor'] = $factor;
                }
                if ($this->db->field_exists('is_default', 'db_units')) {
                    $data['is_default'] = $is_default;
                }
                if ($this->db->insert('db_units', $data)) {
                    $unit_ids[$shortcode] = $this->db->insert_id();
                    $results['units_created']++;
                }
            }
        }

        // --- Item categories ---
        if ($this->db->table_exists('db_category')) {
            foreach ($cfg['item_categories'] as $cat_name => $desc) {
                $exists = $this->db->where('store_id', $store_id)
                    ->where('category_name', $cat_name)->get('db_category')->row();
                if ($exists) continue;
                $maxid = $this->db->query("SELECT COALESCE(MAX(count_id),0)+1 AS maxid FROM db_category WHERE store_id = ?", array($store_id))->row()->maxid;
                $data = array(
                    'store_id'      => $store_id,
                    'count_id'      => $maxid,
                    'category_name' => $cat_name,
                    'description'   => $desc,
                    'status'        => 1,
                );
                if ($this->db->field_exists('category_code', 'db_category')) {
                    $data['category_code'] = 'CT/' . str_pad($store_id, 2, '0', STR_PAD_LEFT) . '/' . str_pad($maxid, 4, '0', STR_PAD_LEFT);
                }
                if ($this->db->insert('db_category', $data)) {
                    $results['categories_created']++;
                }
            }
        }

        // --- Formula (recipe) categories ---
        if ($this->db->table_exists('db_recipe_categories') && !empty($cfg['recipe_categories'])) {
            foreach ($cfg['recipe_categories'] as $rc_name) {
                $exists = $this->db->where('store_id', $store_id)
                    ->where('name', $rc_name)->get('db_recipe_categories')->row();
                if ($exists) continue;
                if ($this->db->insert('db_recipe_categories', array(
                    'store_id' => $store_id, 'name' => $rc_name, 'status' => 1,
                ))) {
                    $results['recipe_categories_created']++;
                }
            }
        }

        // --- Brands ---
        if ($this->db->table_exists('db_brands') && !empty($cfg['brands'])) {
            foreach ($cfg['brands'] as $brand_name) {
                $exists = $this->db->where('store_id', $store_id)
                    ->where('brand_name', $brand_name)->get('db_brands')->row();
                if ($exists) continue;
                $data = array(
                    'store_id'   => $store_id,
                    'brand_name' => $brand_name,
                    'description'=> '',
                    'status'     => 1,
                );
                if ($this->db->field_exists('brand_code', 'db_brands')) {
                    $maxid = $this->db->query("SELECT COALESCE(MAX(id),0)+1 AS maxid FROM db_brands WHERE store_id = ?", array($store_id))->row()->maxid;
                    $data['brand_code'] = 'BR/' . str_pad($store_id, 2, '0', STR_PAD_LEFT) . '/' . str_pad($maxid, 4, '0', STR_PAD_LEFT);
                }
                if ($this->db->insert('db_brands', $data)) {
                    $results['brands_created']++;
                }
            }
        }

        // --- Services (service-led profiles) ---
        if ($this->db->table_exists('db_services') && !empty($cfg['services'])) {
            $sort = 0;
            foreach ($cfg['services'] as $svc_name => $svc) {
                $exists = $this->db->where('store_id', $store_id)
                    ->where('service_name', $svc_name)->get('db_services')->row();
                if ($exists) { $sort++; continue; }
                if ($this->db->insert('db_services', array(
                    'store_id'             => $store_id,
                    'service_name'         => $svc_name,
                    'price'                => isset($svc['price']) ? $svc['price'] : 0,
                    'service_duration'     => isset($svc['duration']) ? $svc['duration'] : '',
                    'description'          => isset($svc['description']) ? $svc['description'] : '',
                    'requires_appointment' => !empty($svc['appointment']) ? 1 : 0,
                    'sort_order'           => $sort,
                    'status'               => 1,
                ))) {
                    $results['services_created']++;
                }
                $sort++;
            }
        }

        return $results;
    }
}
