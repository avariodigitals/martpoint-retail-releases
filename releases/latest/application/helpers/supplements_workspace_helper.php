<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/** Permission-filtered home for supplement and wellness retailers. */
function supplements_workspace_tools() {
    $ci =& get_instance();
    $catalog = [
        'Members & selling' => [
            ['sales_add', 'pos', 'Member checkout', 'Sell products and record member purchases'],
            ['sales_view', 'sales', 'Sales history', 'Review receipts, payments and member purchases'],
            ['customers_view', 'customers', 'Members & customers', 'Manage member profiles and purchase history'],
            ['sales_return_view', 'sales_return', 'Sales returns', 'Review returned products and credit records'],
            ['sales_report', 'reports/sales_and_payments', 'Sales reports', 'Review sales and payment activity'],
        ],
        'Online store & locations' => [
            ['online_store_view', 'online_store', 'Online store', 'Manage your product catalogue and online activity', 'online_store'],
            ['online_store_orders', 'online_store/orders', 'Online orders', 'Process orders placed through your website', 'online_store'],
            ['online_store_edit', 'online_store/products_online', 'Online catalogue', 'Choose products shown in your online store', 'online_store'],
            ['online_store_edit', 'online_store/settings', 'Store settings', 'Manage checkout, delivery and pickup options', 'online_store'],
            ['store_view', 'supplements_workspace/locations', 'Locations & fulfilment', 'View branches and customer pickup locations'],
            ['stock_transfer_view', 'stock_transfer/view', 'Branch transfers', 'Move stock between locations', 'warehouse'],
        ],
        'Stock & purchasing' => [
            ['items_view', 'items', 'Products & inventory', 'Manage products, batches, expiry dates and stock'],
            ['purchase_view', 'purchase', 'Purchasing', 'Order stock from suppliers and receive deliveries'],
            ['suppliers_view', 'suppliers', 'Suppliers', 'Manage supplier contacts and purchase history'],
            ['purchase_return_view', 'purchase_return', 'Purchase returns', 'Return received stock to suppliers'],
            ['stock_adjustment_view', 'stock_adjustment/view', 'Stock adjustments', 'Record and review stock corrections'],
            ['expired_items_report', 'expired_items_report', 'Expiry watch', 'Review expired and near-expiry products', 'expiry_tracking'],
            ['batch_reconciliation_view', 'batch_reconciliation', 'Batch reconciliation', 'Review batch and stock count differences', 'batch_tracking'],
            ['batch_reconciliation_view', 'batch_reports', 'Batch reports', 'Trace product batches across purchases and sales', 'batch_tracking'],
        ],
        'Membership & engagement' => [
            ['loyalty_view', 'loyalty', 'Loyalty & rewards', 'Manage member points and rewards', 'loyalty'],
            ['memberships_view', 'operations/memberships', 'Membership plans', 'Manage paid member plans and subscriptions', 'memberships'],
            ['send_sms', 'marketing/campaigns?screen=mobile', 'Member campaigns', 'Send approved member offers and updates'],
        ],
        'Business & team' => [
            ['expense_view', 'expense', 'Expenses', 'Record and review operating costs'],
            ['purchase_report', 'reports/purchase', 'Purchase reports', 'Review supplier purchasing and receipts'],
            ['users_view', 'users/view', 'Teams & users', 'Manage staff accounts and access'],
            ['roles_view', 'roles/view', 'Roles & permissions', 'Control staff access to business tools'],
            ['business_profile', 'business_profile', 'Business profile & industry', 'Manage business type, workflows and enabled features'],
            ['store_profile', 'store_profile/update/'.(int)get_current_store_id(), 'Business settings', 'Manage business identity and payment details'],
            ['audit_trail_view', 'audit_trail', 'Audit trail', 'Review recorded business changes'],
        ],
    ];
    $permissions = [];
    $groups = [];
    foreach ($catalog as $group => $tools) {
        foreach ($tools as $tool) {
            $permission = $tool[0];
            if (!array_key_exists($permission, $permissions)) $permissions[$permission] = $permission === '' || $ci->permissions($permission);
            if (!$permissions[$permission] || (!empty($tool[4]) && !mp_feature_enabled($tool[4]))) continue;
            $groups[$group][] = ['label'=>$tool[2], 'url'=>base_url($tool[1]), 'description'=>$tool[3]];
        }
    }
    return $groups;
}

function supplements_workspace_render(array $data, $view) {
    $ci =& get_instance();
    $screen = $ci->input->get('screen');
    $mobile = $screen === 'mobile' || ($screen !== 'desktop' && is_mobile());
    $data['supplements_mobile'] = $mobile;
    if ($mobile) $data['SITE_TITLE'] = $data['store_name'] ?? $ci->session->userdata('store_name') ?: 'MartPoint';
    $data['content'] = $ci->load->view($view, $data, true);
    if ($mobile) $ci->load->view('mobile/supplements_workspace', $data);
    else $ci->load->view('mp_layout', $data);
}
