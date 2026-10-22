<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Permission-filtered pharmacy workspace directory, modelled on Building Materials More. */
function pharmacy_workspace_tools() {
    $ci =& get_instance();
    $catalog = [
        'Patient care' => [
            ['sales_add', 'pos', 'Dispensing & checkout', 'Sell, counsel and print the patient invoice'],
            ['medical_notes_view', 'operations/medical_notes', 'Prescription queue', 'Review prescriptions and dispensing notes', 'medical_notes'],
            ['customers_view', 'customers', 'Patients', 'Patient contacts and purchase history'],
            ['online_store_orders', 'online_store/orders', 'Patient orders', 'Orders placed through the pharmacy website', 'online_store'],
        ],
        'Online pharmacy' => [
            ['online_store_view', 'online_store', 'Website dashboard', 'Catalogue, enquiries and online order activity', 'online_store'],
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
            ['expired_items_report', 'expired_items_report', 'Expiry watch', 'Review expired and near-expiry medicines', 'expiry_tracking'],
        ],
        'Finance & reports' => [
            ['sales_report', 'reports/sales_and_payments', 'Sales report', 'All sales and payment activity'],
            ['stock_report', 'reports/stock', 'Stock report', 'Medicine availability and stock value'],
            ['profit_report', 'reports/profit_loss', 'Profit & loss', 'Recorded income, costs and profitability'],
            ['purchase_report', 'reports/purchase', 'Purchase report', 'Supplier purchasing and receipts'],
            ['expense_report', 'reports/expense', 'Expense report', 'Recorded pharmacy operating expenses'],
        ],
        'Team & settings' => [
            ['', 'attendance/workspace', 'My attendance', 'Clock in or out and review your own attendance'],
            ['users_view', 'users/view', 'Teams & users', 'Staff accounts and access'],
            ['roles_view', 'roles/view', 'Roles & permissions', 'Control access to patient and pharmacy workflows'],
            ['business_profile', 'business_profile', 'Pharmacy configuration', 'Industry labels and enabled pharmacy capabilities'],
            ['store_profile', 'store_profile/update/'.(int)get_current_store_id(), 'Business settings', 'Business identity, document and payment configuration'],
            ['audit_trail_view', 'audit_trail', 'Audit trail', 'Review recorded business changes'],
        ],
    ];
    $groups = [];
    $permissions = [];
    foreach ($catalog as $name => $tools) foreach ($tools as $tool) {
        $permission = $tool[0];
        if (!array_key_exists($permission, $permissions)) $permissions[$permission] = $permission === '' || $ci->permissions($permission);
        if (!$permissions[$permission] || (!empty($tool[4]) && !mp_feature_enabled($tool[4]))) continue;
        $groups[$name][] = ['label' => $tool[2], 'url' => base_url($tool[1]), 'description' => $tool[3]];
    }
    return $groups;
}

function pharmacy_workspace_render(array $data, $view) {
    $ci =& get_instance();
    $mobile = $ci->input->get('screen') === 'mobile';
    $data['pharmacy_mobile'] = $mobile;
    if ($mobile) $data['SITE_TITLE'] = $data['store_name'] ?? $ci->session->userdata('store_name') ?: 'MartPoint';
    $data['content'] = $ci->load->view($view, $data, true);
    if ($mobile) $ci->load->view('mobile/pharmacy_workspace', $data);
    else $ci->load->view('mp_layout', $data);
}
