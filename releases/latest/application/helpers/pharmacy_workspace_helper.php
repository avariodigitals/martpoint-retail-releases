<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Permission-filtered pharmacy workspace directory, modelled on Building Materials More. */
function pharmacy_workspace_tools() {
    $ci =& get_instance();
    $messagePermission=$ci->permissions('send_email')?'send_email':'send_sms';
    $catalog = [
        'Messaging & customer engagement' => [
            [$messagePermission,'messaging?screen=mobile','Messaging balance & packages','Add MartPoint credit, view usage and control spending'],
            [$messagePermission,'marketing/campaigns?screen=mobile','SMS & email campaigns','Review costs and send customer campaigns through MartPoint'],
        ],
        'Patient care' => [
            ['sales_add', 'pos', 'Dispensing & checkout', 'Sell, counsel and print the patient invoice'],
            ['sales_view', 'sales', 'Sales history', 'Find invoices, payments and dispensing sales'],
            ['sales_return_view', 'sales_return', 'Sales returns', 'Review returned medicines and credit records'],
            ['medical_notes_view', 'operations/medical_notes', 'Prescription queue', 'Review prescriptions and dispensing notes', 'medical_notes'],
            ['customers_view', 'customers', 'Patients', 'Patient contacts and purchase history'],
            ['online_store_orders', 'online_store/orders', 'Patient orders', 'Orders placed through the pharmacy website', 'online_store'],
        ],
        'Online pharmacy' => [
            ['online_store_view', 'online_store', 'Website dashboard', 'Catalogue, enquiries and online order activity', 'online_store'],
            ['online_store_view', 'online_store/messaging?screen=mobile', 'Messaging', 'Reply to WhatsApp, Instagram and Facebook conversations', 'online_store'],
            ['online_store_orders', 'online_store/quick_wa?screen=mobile', 'Create WhatsApp order', 'Create a pharmacy order from a direct patient chat', 'online_store'],
            ['online_store_edit', 'online_store/products_online', 'Online catalogue', 'Choose medicines and wellness products shown online', 'online_store'],
            ['online_store_edit', 'online_store/settings', 'Store settings', 'Payment, delivery and prescription intake configuration', 'online_store'],
            ['online_store_edit', 'online_store/appearance', 'Appearance', 'Pharmacy storefront colours, type and presentation', 'online_store'],
            ['online_store_edit', 'online_store/journal', 'Care journal', 'Useful pharmacy updates and educational content', 'online_store'],
        ],
        'Locations & fulfilment' => [
            ['store_view', 'pharmacy_workspace/locations', 'Locations & fulfilment', 'Branches, partner stock hubs and pickup-only hubs'],
            ['items_view', 'items', 'Medicines & inventory', 'Products, batches, expiry and on-hand stock'],
            ['stock_transfer_view', 'stock_transfer/view', 'Branch transfers', 'Move stock between controlled locations', 'warehouse'],
            ['purchase_view', 'purchase', 'Purchasing', 'Supplier orders and medicine receipts'],
            ['suppliers_view', 'suppliers', 'Suppliers', 'Supplier contacts and purchase history'],
            ['purchase_return_view', 'purchase_return', 'Purchase returns', 'Return received stock to suppliers'],
            ['stock_adjustment_view', 'stock_adjustment/view', 'Stock adjustments', 'Record and review stock corrections'],
            ['expired_items_report', 'expired_items_report', 'Expiry watch', 'Review expired and near-expiry medicines', 'expiry_tracking'],
            ['expiry_settings', 'expiry_settings?screen=mobile', 'Expiry & stock rotation', 'Block expired sales and choose FEFO or FIFO', 'expiry_tracking'],
        ],
        'Finance & reports' => [
            ['expense_view', 'expense', 'Expenses', 'Record and review pharmacy operating costs'],
            ['sales_report', 'reports/sales_and_payments', 'Sales report', 'All sales and payment activity'],
            ['stock_report', 'reports/stock', 'Stock report', 'Medicine availability and stock value'],
            ['profit_report', 'reports/profit_loss', 'Profit & loss', 'Recorded income, costs and profitability'],
            ['purchase_report', 'reports/purchase', 'Purchase report', 'Supplier purchasing and receipts'],
            ['expense_report', 'reports/expense', 'Expense report', 'Recorded pharmacy operating expenses'],
        ],
        'My workspace' => [
            ['', 'attendance/workspace', 'My attendance', 'Clock in or out and review your own attendance'],
            ['', 'users/password_reset', 'Change password', 'Update your own sign-in password'],
        ],
        'Catalogue setup' => [
            ['items_category_view', 'category/view', 'Medicine categories', 'Organise the medicine catalogue'],
            ['brand_view', 'brands/view', 'Brands', 'Manage medicine and product brands'],
            ['attributes_view', 'attributes', 'Product attributes', 'Manage sizes, strengths and catalogue options'],
            ['units_view', 'units', 'Units', 'Manage packs, tablets and stock units'],
            ['import_items', 'import/items', 'Import medicines', 'Import inventory records from a spreadsheet'],
        ],
        'Critical settings' => [
            ['license_management', 'subscription_license', 'License & subscription', 'View the license, renewal status, plan and usage limits'],
            ['users_view', 'users/view', 'Teams & users', 'Staff accounts and access'],
            ['roles_view', 'roles/view', 'Roles & permissions', 'Control access to patient and pharmacy workflows'],
            ['business_profile', 'pharmacy_workspace/configuration', 'Pharmacy configuration', 'Choose Standard or Advanced Pharmacy'],
            ['business_profile', 'business_profile', 'Business profile & industry', 'Change business type, industry, workflows and enabled features'],
            ['store_profile', 'store_profile/update/'.(int)get_current_store_id(), 'Business settings', 'Business identity, document and payment configuration'],
            ['audit_trail_view', 'audit_trail', 'Audit trail', 'Review recorded business changes'],
            ['tax_view', 'tax', 'Tax rates', 'Manage invoice tax rates'],
            ['payment_types_view', 'payment_types', 'Payment types', 'Configure accepted payment types'],
            ['payment_modes_view', 'payment_modes', 'Payment modes', 'Manage payment methods'],
            ['expiry_settings', 'expiry_settings', 'Expiry settings', 'Configure near-expiry alerts and selling rules', 'expiry_tracking'],
            ['database_backup', 'users/dbbackup', 'Database backup', 'Download a backup of the business database'],
            ['system_updates', 'system_updates', 'System updates', 'Review and apply available application updates'],
        ],
    ];
    $groups = [];
    $permissions = [];
    $admin = pharmacy_workspace_is_admin();
    $advanced = mp_pharmacy_advanced();
    foreach ($catalog as $name => $tools) {
        if ($name === 'Critical settings' && !$admin) continue;
        foreach ($tools as $tool) {
            if (!$advanced && in_array($tool[1], ['pharmacy_workspace/locations', 'stock_transfer/view'], true)) continue;
            $permission = $tool[0];
            if (!array_key_exists($permission, $permissions)) $permissions[$permission] = $permission === '' || $ci->permissions($permission);
            if (!$permissions[$permission] || (!empty($tool[4]) && !mp_feature_enabled($tool[4]))) continue;
            $group = !$advanced && $name === 'Locations & fulfilment' ? 'Stock & purchasing' : $name;
            $groups[$group][] = ['label' => $tool[2], 'url' => base_url($tool[1]), 'description' => $tool[3]];
        }
    }
    return $groups;
}

/** Use the established admin roles; a delegated permission is not admin identity. */
function pharmacy_workspace_is_admin() {
    return is_admin() || is_store_admin();
}

/** Visible desktop entry points; retain the same admin identity and grants as More. */
function pharmacy_workspace_admin_shortcuts() {
    if (!pharmacy_workspace_is_admin()) return [];
    $ci =& get_instance();
    $links = [];
    if ($ci->permissions('users_view')) {
        $links[] = ['path' => 'users/view', 'label' => 'Teams & users', 'icon' => 'users'];
    }
    if ($ci->permissions('business_profile')) {
        $links[] = ['path' => 'business_profile', 'label' => 'Business profile & industry', 'icon' => 'settings'];
        $links[] = ['path' => 'pharmacy_workspace/configuration', 'label' => 'Pharmacy configuration', 'icon' => 'settings'];
    }
    return $links;
}

/** Shared by navigation and the authenticated server-side route guard. */
function pharmacy_workspace_admin_route($controller, $method, $self_update = false) {
    $controller = strtolower((string)$controller);
    $method = strtolower((string)$method);
    if ($controller === 'users') {
        if (in_array($method, ['profile', 'password_reset', 'password_update'], true)) return false;
        if ($method === 'save_or_update' && $self_update) return false;
        return true;
    }
    // The keyless fleet check-in does not load the authenticated settings page.
    if ($controller === 'system_updates' && $method === 'auto_tick') return false;
    if ($controller === 'pharmacy_workspace' && in_array($method, ['configuration', 'save_configuration'], true)) return true;
    return in_array($controller, ['roles', 'business_profile', 'store_profile', 'audit_trail',
        'tax', 'payment_types', 'payment_modes', 'system_updates', 'subscription_license'], true);
}

function pharmacy_workspace_render(array $data, $view) {
    $ci =& get_instance();
    $screen = $ci->input->get('screen');
    $mobile = $screen === 'mobile' || ($screen !== 'desktop' && is_mobile());
    $data['pharmacy_mobile'] = $mobile;
    if ($mobile) $data['SITE_TITLE'] = $data['store_name'] ?? $ci->session->userdata('store_name') ?: 'MartPoint';
    $data['content'] = $ci->load->view($view, $data, true);
    if ($mobile) $ci->load->view('mobile/pharmacy_workspace', $data);
    else $ci->load->view('mp_layout', $data);
}
