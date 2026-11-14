<?php
$CI =& get_instance();
$restaurant_name = (string) ($this->session->userdata('store_name') ?: ($SITE_TITLE ?? 'MartPoint'));
$restaurant_user = trim((string) ($this->session->userdata('display_name')
    ?: $this->session->userdata('inv_username')
    ?: 'Staff'));
$restaurant_initials = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $restaurant_user), 0, 2));
if ($restaurant_initials === '') $restaurant_initials = 'MP';

$restaurant_logo = '';
try {
    $store_details = get_store_details();
    $candidate_logo = (string) ($store_details->store_logo ?? '');
    $candidate_logo = ltrim(trim($candidate_logo), '/');
    if ($candidate_logo && is_file(FCPATH . $candidate_logo)) {
        $restaurant_logo = base_url($candidate_logo);
    }
} catch (Throwable $e) {
    $restaurant_logo = '';
}

$restaurant_path = trim((string) uri_string(), '/');
$restaurant_active = function ($keys) use ($restaurant_path) {
    foreach ((array) $keys as $key) {
        if ($key === 'restaurantos' && ($restaurant_path === 'restaurantos' || $restaurant_path === 'restaurantos/')) return true;
        if ($key !== 'restaurantos' && ($restaurant_path === $key || strpos($restaurant_path, $key . '/') === 0)) return true;
    }
    return false;
};
$restaurant_can = function ($permission) use ($CI) {
    try { return $CI->permissions($permission); } catch (Throwable $e) { return true; }
};
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<?php $this->load->view('comman/app_icons'); ?>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title><?= htmlspecialchars(($restaurant_name ?: 'Restaurant') . ' — ' . ($page_title ?? 'Workspace'), ENT_QUOTES, 'UTF-8'); ?></title>
<meta name="theme-color" content="#f7f6f2">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<link rel="stylesheet" href="<?= $theme_link; ?>bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="<?= $theme_link; ?>plugins/select2/select2.min.css">
<link rel="stylesheet" href="<?= $theme_link; ?>plugins/DataTables-1.10.18/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="<?= $theme_link; ?>plugins/DataTables-1.10.18/extensions/Responsive-2.2.2/css/responsive.bootstrap.min.css">
<link rel="stylesheet" href="<?= $theme_link; ?>plugins/DataTables-1.10.18/extensions/Buttons-1.5.4/css/buttons.bootstrap.min.css">
<link rel="stylesheet" href="<?= $theme_link; ?>toastr/toastr.css">
<link rel="stylesheet" href="<?= $theme_link; ?>plugins/datepicker/datepicker3.css">
<link rel="stylesheet" href="<?= $theme_link; ?>plugins/daterangepicker/daterangepicker.css">
<link rel="stylesheet" href="<?= $theme_link; ?>css/assist.css?v=25">
<link rel="stylesheet" href="<?= $theme_link; ?>plugins/pace/pace.min.css">
<?php if (!empty($extra_css_files) && is_array($extra_css_files)): ?>
  <?php foreach ($extra_css_files as $css): ?><link rel="stylesheet" href="<?= $theme_link . $css; ?>"><?php endforeach; ?>
<?php endif; ?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url('theme/dist/css/restaurant_workspace.css?v=' . (is_file(FCPATH . 'theme/dist/css/restaurant_workspace.css') ? filemtime(FCPATH . 'theme/dist/css/restaurant_workspace.css') : 1)); ?>">
<link rel="stylesheet" href="<?= base_url('theme/dist/css/restaurant_shell.css?v=' . (is_file(FCPATH . 'theme/dist/css/restaurant_shell.css') ? filemtime(FCPATH . 'theme/dist/css/restaurant_shell.css') : 1)); ?>">
<script src="<?= $theme_link; ?>plugins/jQuery/jquery-2.2.3.min.js"></script>
</head>
<body class="mp-restaurant restaurant-app">

<header class="restaurant-topbar">
  <a class="restaurant-brand" href="<?= base_url('restaurantos'); ?>">
    <?php if ($restaurant_logo): ?>
      <img src="<?= htmlspecialchars($restaurant_logo, ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($restaurant_name, ENT_QUOTES, 'UTF-8'); ?>">
    <?php else: ?>
      <span class="restaurant-brand-mark" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M7 3v7a3 3 0 0 0 6 0V3M10 3v4M7 3v4M13 3v4M10 13v8M18 3v18M18 3c2-2 2-5 0-7"/></svg>
      </span>
    <?php endif; ?>
    <span class="restaurant-brand-copy">
      <strong><?= htmlspecialchars($restaurant_name, ENT_QUOTES, 'UTF-8'); ?></strong>
      <small>Restaurant workspace</small>
    </span>
  </a>

  <div class="restaurant-global-search" role="search">
    <i class="fa fa-search" aria-hidden="true"></i>
    <input type="search" placeholder="Search orders, menu items or customers" aria-label="Search restaurant workspace">
    <span class="restaurant-search-key">⌘ K</span>
  </div>

  <div class="restaurant-top-actions">
    <span class="restaurant-service-status"><span class="restaurant-status-dot"></span> Service online</span>
    <?php if (!is_store_admin()): ?>
      <button class="restaurant-icon-btn" id="appClockInBtn" title="Clock in or out" type="button"><i class="fa fa-clock-o"></i></button>
    <?php endif; ?>
    <a class="restaurant-icon-btn" href="<?= base_url('restaurantos/orders?source=online&range=Today'); ?>" title="Online orders" aria-label="Open online orders"><i class="fa fa-bell-o"></i><span class="restaurant-notification-dot"></span></a>
    <div class="restaurant-user-chip">
      <span class="restaurant-avatar"><?= htmlspecialchars($restaurant_initials, ENT_QUOTES, 'UTF-8'); ?></span>
      <span class="restaurant-user-copy"><strong><?= htmlspecialchars($restaurant_user, ENT_QUOTES, 'UTF-8'); ?></strong><small>Staff account</small></span>
      <a href="<?= base_url('logout'); ?>" title="Log out" aria-label="Log out"><i class="fa fa-sign-out"></i></a>
    </div>
  </div>
</header>

<div class="mp-shell restaurant-shell">
  <aside class="restaurant-sidebar">
    <div class="restaurant-sidebar-scroll">
      <div class="restaurant-nav-group">
        <div class="restaurant-nav-label">Service</div>
        <a class="restaurant-nav-item <?= $restaurant_active(['restaurantos']) ? 'active' : ''; ?>" href="<?= base_url('restaurantos'); ?>">
          <i class="fa fa-th-large"></i><span>Overview</span>
        </a>
        <?php if ($restaurant_can('pos')): ?>
        <a class="restaurant-nav-item <?= $restaurant_active(['pos']) ? 'active' : ''; ?>" href="<?= base_url('pos'); ?>">
          <i class="fa fa-shopping-basket"></i><span>New order</span><kbd>⌘ N</kbd>
        </a>
        <?php endif; ?>
        <a class="restaurant-nav-item <?= $restaurant_active(['restaurantos/orders']) ? 'active' : ''; ?>" href="<?= base_url('restaurantos/orders'); ?>">
          <i class="fa fa-list-alt"></i><span>Orders</span>
        </a>
        <?php if (function_exists('mp_feature_enabled') && mp_feature_enabled('kitchen_workflow')): ?>
        <a class="restaurant-nav-item <?= $restaurant_active(['operations/kitchen']) ? 'active' : ''; ?>" href="<?= base_url('operations/kitchen'); ?>">
          <i class="fa fa-fire"></i><span>Kitchen display</span>
        </a>
        <?php endif; ?>
        <?php if (function_exists('mp_feature_enabled') && mp_feature_enabled('table_management')): ?>
        <a class="restaurant-nav-item <?= $restaurant_active(['operations/table_management']) ? 'active' : ''; ?>" href="<?= base_url('operations/table_management'); ?>">
          <i class="fa fa-th"></i><span>Tables &amp; floor</span>
        </a>
        <?php endif; ?>
      </div>

      <div class="restaurant-nav-group">
        <div class="restaurant-nav-label">Manage</div>
        <a class="restaurant-nav-item <?= $restaurant_active(['operations/menu_items']) ? 'active' : ''; ?>" href="<?= base_url('operations/menu_items'); ?>">
          <i class="fa fa-cutlery"></i><span>Menu</span>
        </a>
        <a class="restaurant-nav-item <?= $restaurant_active(['customers']) ? 'active' : ''; ?>" href="<?= base_url('customers'); ?>">
          <i class="fa fa-users"></i><span>Guests</span>
        </a>
        <a class="restaurant-nav-item <?= $restaurant_active(['reports']) ? 'active' : ''; ?>" href="<?= base_url('reports'); ?>">
          <i class="fa fa-line-chart"></i><span>Reports</span>
        </a>
        <a class="restaurant-nav-item <?= $restaurant_active(['items', 'warehouse']) ? 'active' : ''; ?>" href="<?= base_url('items'); ?>">
          <i class="fa fa-cubes"></i><span>Stock room</span>
        </a>
      </div>
    </div>

    <div class="restaurant-sidebar-bottom">
      <?php if ($restaurant_can('business_profile')): ?>
      <a class="restaurant-nav-item <?= $restaurant_active(['restaurantos/settings']) ? 'active' : ''; ?>" href="<?= base_url('restaurantos/settings'); ?>">
        <i class="fa fa-sliders"></i><span>Workspace settings</span>
      </a>
      <?php endif; ?>
      <div class="restaurant-help-card">
        <span class="restaurant-help-icon"><i class="fa fa-life-ring"></i></span>
        <div><strong>Need a hand?</strong><small>Ask MartPoint Assist</small></div>
        <i class="fa fa-angle-right"></i>
      </div>
    </div>
  </aside>

  <main class="mp-main restaurant-main" id="mp-main">
