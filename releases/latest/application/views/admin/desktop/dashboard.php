<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Manage users, stores, settings and system configuration</div>
  </div>
</div>

<div class="mp-kpi-grid" style="grid-template-columns:repeat(auto-fit,minmax(160px,1fr));">
  <div class="mp-kpi-card primary">
    <div class="mp-kpi-icon"><i class="fa fa-users"></i></div>
    <div class="mp-kpi-label">Users</div>
    <div class="mp-kpi-value"><?= number_format((int)$total_users); ?></div>
  </div>
  <div class="mp-kpi-card teal">
    <div class="mp-kpi-icon"><i class="fa fa-user-shield"></i></div>
    <div class="mp-kpi-label">Roles</div>
    <div class="mp-kpi-value"><?= number_format((int)$total_roles); ?></div>
  </div>
  <div class="mp-kpi-card success">
    <div class="mp-kpi-icon"><i class="fa fa-building"></i></div>
    <div class="mp-kpi-label">Branches</div>
    <div class="mp-kpi-value"><?= number_format((int)$total_branches); ?></div>
  </div>
  <div class="mp-kpi-card purple">
    <div class="mp-kpi-icon"><i class="fa fa-percent"></i></div>
    <div class="mp-kpi-label">Taxes</div>
    <div class="mp-kpi-value"><?= number_format((int)$total_taxes); ?></div>
  </div>
  <div class="mp-kpi-card warn">
    <div class="mp-kpi-icon"><i class="fa fa-balance-scale"></i></div>
    <div class="mp-kpi-label">Units</div>
    <div class="mp-kpi-value"><?= number_format((int)$total_units); ?></div>
  </div>
  <div class="mp-kpi-card cash">
    <div class="mp-kpi-icon"><i class="fa fa-money"></i></div>
    <div class="mp-kpi-label">Payment Types</div>
    <div class="mp-kpi-value"><?= number_format((int)$total_payment_types); ?></div>
  </div>
</div>

<?php
$menu_groups = [];

if ($CI->permissions('store_edit') || $CI->permissions('store_view') || $CI->permissions('users_view') || $CI->permissions('roles_view')) {
  $menu_groups['Store & People'] = [
    ['icon' => 'fa-suitcase', 'label' => 'Store Profile', 'url' => base_url('store_profile/update/'.$this->session->userdata('store_id')), 'perm' => 'store_edit'],
    ['icon' => 'fa-industry', 'label' => 'Business Profile', 'url' => base_url('business_profile'), 'perm' => 'store_edit'],
    ['icon' => 'fa-building', 'label' => 'Branches', 'url' => base_url('warehouse'), 'perm' => 'warehouse_view'],
    ['icon' => 'fa-building-o', 'label' => 'Stores', 'url' => base_url('store/view'), 'perm' => 'store_view'],
    ['icon' => 'fa-users', 'label' => 'Users', 'url' => base_url('users/view'), 'perm' => 'users_view'],
    ['icon' => 'fa-user-shield', 'label' => 'Roles', 'url' => base_url('roles/view'), 'perm' => 'roles_view'],
  ];
}

if ($CI->permissions('attendance_view') || $CI->permissions('attendance_edit') || is_store_admin() || $this->session->userdata('role_id') == 1) {
  $menu_groups['Attendance'] = [
    ['icon' => 'fa-calendar', 'label' => 'Shifts', 'url' => base_url('attendance/shifts')],
    ['icon' => 'fa-user-plus', 'label' => 'Assign Shifts', 'url' => base_url('attendance/assign_shifts')],
    ['icon' => 'fa-calendar-check-o', 'label' => 'Daily Attendance', 'url' => base_url('attendance/daily')],
    ['icon' => 'fa-bar-chart', 'label' => 'Attendance Report', 'url' => base_url('attendance/report')],
  ];
}

if ($CI->permissions('tax_view') || $CI->permissions('units_view') || $CI->permissions('payment_types_view') || $CI->permissions('payment_modes_view') || special_access()) {
  $menu_groups['Finance & Configuration'] = [
    ['icon' => 'fa-percent', 'label' => 'Tax List', 'url' => base_url('tax'), 'perm' => 'tax_view'],
    ['icon' => 'fa-balance-scale', 'label' => 'Units List', 'url' => base_url('units'), 'perm' => 'units_view'],
    ['icon' => 'fa-credit-card', 'label' => 'Payment Types', 'url' => base_url('payment_types'), 'perm' => 'payment_types_view'],
    ['icon' => 'fa-money', 'label' => 'Payment Modes', 'url' => base_url('payment_modes'), 'perm' => 'payment_modes_view'],
    ['icon' => 'fa-usd', 'label' => 'Currency List', 'url' => base_url('currency/view'), 'special' => true],
    ['icon' => 'fa-globe', 'label' => 'Countries', 'url' => base_url('country'), 'special' => true],
    ['icon' => 'fa-map', 'label' => 'States', 'url' => base_url('state'), 'special' => true],
    ['icon' => 'fa-map-marker', 'label' => 'Cities', 'url' => base_url('city'), 'special' => true],
  ];
}

if ($CI->permissions('send_sms') || $CI->permissions('sms_template_view') || $CI->permissions('sms_api_view') || $CI->permissions('smtp_settings')) {
  $menu_groups['Communication'] = [
    ['icon' => 'fa-envelope', 'label' => 'Send SMS', 'url' => base_url('sms'), 'perm' => 'send_sms'],
    ['icon' => 'fa-commenting', 'label' => 'SMS Templates', 'url' => base_url('templates/sms'), 'perm' => 'sms_template_view'],
    ['icon' => 'fa-cube', 'label' => 'SMS API', 'url' => base_url('sms/api'), 'perm' => 'sms_api_view'],
    ['icon' => 'fa-envelope-square', 'label' => 'Email Settings', 'url' => base_url('email_settings'), 'perm' => 'smtp_settings'],
  ];
}

if (special_access() || $this->session->userdata('role_id') == 1 || is_store_admin()) {
  $menu_groups['System'] = [
    ['icon' => 'fa-shield', 'label' => 'Site Settings', 'url' => base_url('site')],
    ['icon' => 'fa-random', 'label' => 'Data Migration', 'url' => base_url('migrate')],
    ['icon' => 'fa-key', 'label' => 'License Management', 'url' => base_url('subscription_license')],
    ['icon' => 'fa-list-alt', 'label' => 'Subscription Plans', 'url' => base_url('subscription_plans')],
    ['icon' => 'fa-database', 'label' => 'Database Backup', 'url' => base_url('users/dbbackup')],
    ['icon' => 'fa-refresh', 'label' => 'System Update', 'url' => base_url('system_updates')],
    ['icon' => 'fa-file-code-o', 'label' => 'Manifest Generator', 'url' => base_url('manifest')],
    ['icon' => 'fa-code-fork', 'label' => 'Build Release', 'url' => base_url('release')],
    ['icon' => 'fa-lock', 'label' => 'Permission Audit', 'url' => base_url('permission_audit')],
  ];
}

if ($CI->permissions('gateway_view') || $CI->permissions('package_view') || $CI->permissions('subscription') || $CI->permissions('paystack_settings') || $CI->permissions('expiry_settings') || $CI->permissions('debt_reminder_view')) {
  $menu_groups['Integrations'] = [
    ['icon' => 'fa-link', 'label' => 'Payment Gateways', 'url' => base_url('gateways'), 'perm' => 'gateway_view'],
    ['icon' => 'fa-gift', 'label' => 'Packages', 'url' => base_url('package'), 'perm' => 'package_view'],
    ['icon' => 'fa-calendar-check-o', 'label' => 'Subscription', 'url' => base_url('subscribers/list/'.$store_id), 'perm' => 'subscription'],
    ['icon' => 'fa-credit-card-alt', 'label' => 'Paystack Settings', 'url' => base_url('paystack/settings'), 'perm' => 'paystack_settings'],
    ['icon' => 'fa-calendar-times-o', 'label' => 'Expiry Settings', 'url' => base_url('expiry_settings'), 'perm' => 'expiry_settings'],
    ['icon' => 'fa-bell-o', 'label' => 'Debt Reminder', 'url' => base_url('debt_reminder'), 'perm' => 'debt_reminder_view'],
  ];
}

if ($CI->permissions('approval_settings_edit') || is_store_admin() || $this->session->userdata('role_id') == 1 || $CI->permissions('nin_usage') || $CI->permissions('nin_logs')) {
  $menu_groups['Security & Compliance'] = [
    ['icon' => 'fa-check-circle', 'label' => 'Security & Approvals', 'url' => base_url('approvals/settings')],
    ['icon' => 'fa-bar-chart', 'label' => 'NIN Usage', 'url' => base_url('ninverify/usage'), 'perm' => 'nin_usage'],
    ['icon' => 'fa-id-card', 'label' => 'NIN Verification Log', 'url' => base_url('ninverify/log'), 'perm' => 'nin_logs'],
    ['icon' => 'fa-lock', 'label' => 'Change Password', 'url' => base_url('users/password_reset')],
  ];
}

foreach ($menu_groups as $group_label => $items):
  $visible = array_filter($items, function($item) use ($CI) {
    if (!empty($item['perm']) && !$CI->permissions($item['perm'])) return false;
    if (!empty($item['special']) && !special_access()) return false;
    return true;
  });
  if (empty($visible)) continue;
?>
  <h4 class="admin-section-title"><?= htmlspecialchars($group_label); ?></h4>
  <div class="admin-menu-grid">
    <?php foreach ($items as $item):
      if (!empty($item['perm']) && !$CI->permissions($item['perm'])) continue;
      if (!empty($item['special']) && !special_access()) continue;
    ?>
    <a href="<?= $item['url']; ?>" class="admin-menu-card">
      <div class="icon"><i class="fa <?= htmlspecialchars($item['icon']); ?>"></i></div>
      <div class="body">
        <div class="title"><?= htmlspecialchars($item['label']); ?></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
<?php endforeach; ?>

<script>$(".admin-dashboard-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
