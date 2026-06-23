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
            // Roles
            'roles_add','roles_edit','roles_delete','roles_view',
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
            'sales_gst_report','purchase_gst_report',
            'return_items_report','stock_transfer_report',
            'sales_summary_report','sales_return_payments',
            'purchase_return_report','sales_return_report',
            'customer_orders_report',
            'gstr_1_report','gstr_2_report',
            // Advanced
            'cust_adv_payments_add','cust_adv_payments_edit','cust_adv_payments_delete','cust_adv_payments_view',
            'show_all_users_sales_invoices','show_all_users_sales_return_invoices',
            'show_all_users_purchase_invoices','show_all_users_purchase_return_invoices',
            'show_purchase_price',
            // Settings
            'subscription','smtp_settings','sms_settings','sms_api_view','sms_api_edit'
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
            'sales_gst_report','purchase_gst_report',
            'return_items_report','stock_transfer_report',
            'sales_summary_report','sales_return_payments',
            'purchase_return_report','sales_return_report',
            'customer_orders_report',
            // Advanced
            'cust_adv_payments_view',
            'show_all_users_sales_invoices','show_all_users_sales_return_invoices',
            'show_all_users_purchase_invoices','show_all_users_purchase_return_invoices',
            'show_purchase_price'
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
            'sales_gst_report','purchase_gst_report',
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
}
