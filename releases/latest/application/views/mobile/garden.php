<?php
/*
 * Phone shell for the Garden Workspace.
 *
 * The app bar anchors navigation: its menu button owns the workspace menu
 * (garden/navigation.php) and the account avatar sits at the trailing edge,
 * where a phone user reaches for it. The bar carries the brand and the account,
 * so the page body does not repeat the store name and no licence badge is
 * stacked into the header.
 */
$gw_ci       =& get_instance();
$gw_uid      = function_exists('get_current_user_id') ? get_current_user_id() : 0;
$gw_store_id = function_exists('get_current_store_id') ? get_current_store_id() : 0;
$gw_store    = $store_name ?? $SITE_TITLE ?? 'MartPoint';

$gw_display = trim((string) ($gw_ci->session->userdata('display_name') ?: $gw_ci->session->userdata('username') ?: ''));
if ($gw_display === '') $gw_display = 'Account';
$gw_initials = '';
foreach (preg_split('/\s+/', $gw_display) as $gw_part) {
    if ($gw_part !== '' && strlen($gw_initials) < 2) $gw_initials .= strtoupper(substr($gw_part, 0, 1));
}
if ($gw_initials === '') $gw_initials = 'A';

$gw_avatar = '';
if ($gw_uid && $gw_ci->db->table_exists('db_users')) {
    try {
        $gw_row = $gw_ci->db->select('profile_picture')->where('id', $gw_uid)->get('db_users')->row();
        if ($gw_row && !empty($gw_row->profile_picture) && file_exists(FCPATH . ltrim($gw_row->profile_picture, '/'))) {
            $gw_avatar = ltrim($gw_row->profile_picture, '/');
        }
    } catch (Throwable $e) {
        $gw_avatar = '';
    }
}

$gw_logo = '';
try {
    if (function_exists('mp_get_store_theme_setting')) {
        $gw_logo = trim((string) mp_get_store_theme_setting($gw_store_id, 'store_logo', ''));
    }
    if ($gw_logo === '' && function_exists('get_site_logo')) {
        $gw_logo = trim((string) get_site_logo());
    }
    $gw_logo = ltrim($gw_logo, '/');
    if ($gw_logo !== '' && !preg_match('#^(https?:)?//#', $gw_logo) && !file_exists(FCPATH . $gw_logo)) {
        $gw_logo = '';
    }
} catch (Throwable $e) {
    $gw_logo = '';
}
?>
<!doctype html><html lang="en"><head>
<meta charset="utf-8">
<?php $this->load->view('comman/app_icons'); ?>
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?> — <?= htmlspecialchars($page_title); ?></title>
<link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
<?php $this->load->view('garden/styles'); ?></head><body class="garden-mobile-body">
<main class="garden-workspace garden-mobile">
<header class="gw-appbar" id="gw-appbar">
  <button type="button" class="gw-appbar-menu" id="gw-menu-toggle" aria-expanded="false" aria-controls="gw-workspace-menu">
    <i class="fa fa-bars gw-appbar-icon-open" aria-hidden="true"></i><i class="fa fa-times gw-appbar-icon-close" aria-hidden="true"></i><span class="gw-visually-hidden">Workspace menu</span>
  </button>
  <div class="gw-appbar-identity">
    <span class="gw-appbar-titles">
      <span class="gw-appbar-brand" title="<?= htmlspecialchars($gw_store, ENT_QUOTES, 'UTF-8'); ?>"><?php if ($gw_logo !== ''): ?><img class="gw-appbar-logo" src="<?= htmlspecialchars(base_url($gw_logo), ENT_QUOTES, 'UTF-8'); ?>" alt=""><?php endif; ?><span class="gw-appbar-store"><?= htmlspecialchars($gw_store); ?></span></span>
      <h1><?= htmlspecialchars($page_title); ?></h1>
    </span>
  </div>
  <div class="gw-appbar-account">
    <button type="button" class="gw-account-trigger" id="gw-account-trigger" aria-haspopup="true" aria-expanded="false" aria-controls="gw-account-menu" title="Open account menu">
    <span class="gw-appbar-avatar" aria-hidden="true"><?php if ($gw_avatar !== ''): ?><img src="<?= htmlspecialchars(base_url($gw_avatar), ENT_QUOTES, 'UTF-8'); ?>" alt=""><?php else: ?><?= htmlspecialchars($gw_initials); ?><?php endif; ?></span>
    <span class="gw-visually-hidden">Open account menu for <?= htmlspecialchars($gw_display, ENT_QUOTES, 'UTF-8'); ?></span>
    </button>
    <div class="gw-account-menu" id="gw-account-menu" role="menu" hidden>
      <a href="<?= base_url('mobile/profile'); ?>" role="menuitem"><i class="fa fa-user" aria-hidden="true"></i><span>My Profile</span></a>
      <a href="<?= base_url('logout'); ?>" role="menuitem" class="logout" onclick="return mpLogout(this, event);"><i class="fa fa-sign-out" aria-hidden="true"></i><span>Log Out</span></a>
    </div>
  </div>
</header>
<div class="gw-drawer-backdrop" id="gw-workspace-backdrop" hidden></div>
<div class="garden-mobile-menu gw-drawer" id="gw-workspace-menu" role="dialog" aria-label="Garden workspace menu" aria-hidden="true" hidden>
<?php $this->load->view('garden/navigation'); ?>
</div>
<?= $garden_content ?>
</main>
<?php $this->load->view('mobile/bottom_nav',['active'=>'home']); ?>
<?php $this->load->view('garden/notices'); ?>
<?php $this->load->view('garden/scripts',['mobile'=>true]); ?>
<?php $this->load->view('mobile/chat'); ?></body></html>
