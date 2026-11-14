<?php
  $active = $active ?? '';
  $is_pos = in_array($active, ['pos','holds']);

  $CI =& get_instance();
  $role_name = strtolower($CI->session->userdata('role_name') ?: '');
  $is_cashier = (strpos($role_name, 'cashier') !== false);
  $can_home = !$is_cashier;
  $can_pos = $CI->permissions('pos');
  $can_sale = $CI->permissions('sales_add') && !$is_cashier;
  $can_holds = $is_pos && $CI->permissions('sales_add');
  $can_sales_list = $is_cashier && $CI->permissions('sales_view');
  $can_purchase = $CI->permissions('purchase_view') && !$is_cashier;
  $can_more = !$is_cashier;
  if(!function_exists('physio_enabled')) $CI->load->helper('physio');
  $is_physio = function_exists('physio_enabled') && physio_enabled();
  $is_oil_processing = function_exists('mp_get_store_profile')
    && ((mp_get_store_profile()['industry_type'] ?? '') === 'oil_processing_plant');
  if($is_oil_processing) $can_pos = false;
  $is_garden = function_exists('mp_get_store_profile')
    && ((mp_get_store_profile()['industry_type'] ?? '') === 'garden_centre_landscaping');
  $is_building = function_exists('mp_get_store_profile') && ((mp_get_store_profile()['industry_type'] ?? '') === 'building_materials');
  if($is_building){$can_pos=false;$can_holds=false;}
  $user_id = get_current_user_id();
  $store_id = get_current_store_id();
  $display_name = $CI->session->userdata('display_name') ?: $CI->session->userdata('username') ?: 'User';
  if($is_building) $display_name = mp_current_user_full_name();
  $profile_picture = '';
  $user = null;
  if($CI->db->table_exists('db_users') && !empty($user_id)){
    try {
      $userResult = $CI->db->select('profile_picture')->where('id', $user_id)->get('db_users');
      if($userResult && is_object($userResult)){
        $user = $userResult->row();
      }
      if($user && !empty($user->profile_picture) && file_exists(FCPATH . $user->profile_picture)){
        $profile_picture = $user->profile_picture;
      }
    } catch (Throwable $e) {
      $profile_picture = '';
      log_message('error', 'bottom_nav profile/logo lookup failed: ' . $e->getMessage());
    }
  }
  $needs_clock_out = false;
  if($user_id && $store_id){
    try {
      $CI->load->model('attendance_model');
      $shift = $CI->attendance_model->isOnDuty($user_id, $store_id);
      if($shift || $is_building){
        $needs_clock_out = $CI->attendance_model->needsClockOut($user_id, date('Y-m-d'),$is_building ? $store_id : null);
      }
    } catch (Throwable $e) {
      log_message('error', 'bottom_nav attendance check failed: ' . $e->getMessage());
      $needs_clock_out = false;
    }
  }
?>
<style>
  .mp-mobile-bottom-nav {
    display: flex;
    justify-content: space-around;
    padding: 10px 0 0;
    background: #fff;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
  }
  .mp-mobile-bottom-nav .nav-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    padding: 6px 16px;
    border: none;
    background: transparent;
    color: #64748B;
    font-size: 11px;
    font-weight: 500;
    text-decoration: none;
  }
  .mp-mobile-bottom-nav .nav-item .icon { font-size: 22px; }
  .mp-mobile-bottom-nav:not(.physio-mobile-bottom-nav) { display:grid; grid-auto-flow:column; grid-auto-columns:minmax(0,1fr); }
  .mp-mobile-bottom-nav:not(.physio-mobile-bottom-nav) .nav-item { min-width:0; min-height:48px; padding:6px 2px; }
  .mp-mobile-bottom-nav:not(.physio-mobile-bottom-nav) .nav-item.hold { padding:6px 2px; }
  .physio-mobile-bottom-nav { display:grid; grid-template-columns:repeat(auto-fit,minmax(40px,1fr)); gap:1px; padding:6px 2px 0; }
  .physio-mobile-bottom-nav .nav-item { min-width:0; gap:3px; padding:5px 1px; font-size:8px; }
  .physio-mobile-bottom-nav .nav-item .icon { font-size:15px; }
  .mp-mobile-bottom-nav .nav-item.active { color: #0057FF; }
  /* Garden workspace footer: brand the active tab with the workspace green and
     hide the retail hold pill (the garden menu has no POS/Hold). */
  .mp-mobile-bottom-nav.mp-garden-nav .nav-item.active,
  .mp-mobile-bottom-nav.mp-garden-nav .nav-item.active .icon { color: #235b46; }
  .mp-mobile-bottom-nav.mp-garden-nav .nav-item { font-weight: 600; }
  .mp-mobile-bottom-nav.mp-building-nav{font-family:'Manrope',-apple-system,BlinkMacSystemFont,sans-serif}
  .mp-mobile-bottom-nav.mp-building-nav .nav-item.active{color:#202124}
  .mp-mobile-bottom-nav .nav-item.hold {
    background: #FFF7ED;
    color: #EA580C;
    border-radius: 12px;
    padding: 6px 18px;
    font-weight: 700;
  }
  .mp-mobile-bottom-nav .nav-item.hold .icon { color: #EA580C; }
  .mp-mobile-footer {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: #fff;
    border-top: 1px solid #E2E8F0;
    z-index: 300;
  }
  .mp-mobile-copyright {
    width: 100%;
    display: flex;
    justify-content: center;
    align-items: center;
    text-align: center;
    padding: 6px 0 calc(6px + env(safe-area-inset-bottom, 0px));
    font: 11px/1.4 system-ui, sans-serif;
    overflow-wrap: anywhere;
    color: #94A3B8;
    background: #fff;
  }
  .mp-mobile-copyright-text {
    display: block;
    width: 100%;
    max-width: 430px;
    padding: 0 12px;
    text-align: center;
  }
  .screen { padding-bottom: calc(160px + env(safe-area-inset-bottom, 0px)) !important; }
  .topbar { position: sticky; top: 0; z-index: 400; background: #fff; border-bottom: 1px solid var(--mp-border); padding-top: calc(8px + env(safe-area-inset-top, 0px)) !important; padding-bottom: 8px; }
  .topbar h1 { font-size: clamp(16px, 4.5vw, 22px); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .clock-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 12px; background: var(--mp-success); color: #fff; font-size: 13px; font-weight: 600; text-decoration: none; white-space: nowrap; }
  .clock-btn.out { background: var(--mp-danger); }
  .mp-time-pill { display: inline-flex; align-items: center; gap: 6px; padding: 8px 12px; border-radius: 12px; background: var(--mp-bg); border: 1px solid var(--mp-border); color: var(--mp-ink); font-size: 13px; font-weight: 600; white-space: nowrap; }
  .mp-time-pill i { color: var(--mp-muted); font-size: 13px; }
  .avatar { width: 36px; height: 36px; border-radius: 50%; background: #E0E7FF; color: var(--mp-primary); display: inline-flex; align-items: center; justify-content: center; font-weight: 600; font-size: 13px; overflow: hidden; text-decoration: none; flex-shrink: 0; }
  .avatar img { width: 100%; height: 100%; object-fit: cover; }
  .mp-avatar-wrap { position: relative; display: inline-flex; margin-left: 16px; }
  .mp-avatar-trigger { border: none; background: transparent; padding: 0; cursor: pointer; }
  .mp-avatar-menu { position: absolute; top: calc(100% + 6px); right: -4px; min-width: 170px; background: #fff; border: 1px solid var(--mp-border); border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); padding: 6px; z-index: 1000; display: none; }
  .mp-avatar-menu.open { display: block; }
  .mp-avatar-item { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 8px; text-decoration: none; color: var(--mp-ink, #1E293B); font-size: 14px; font-weight: 500; white-space: nowrap; }
  .mp-avatar-item:active, .mp-avatar-item:hover { background: var(--mp-bg, #F1F5F9); }
  .mp-avatar-item.logout { color: var(--mp-danger); }
  .mp-avatar-item i { width: 18px; text-align: center; }
  .main-footer { display: none !important; }
  /* Hide the fixed footer while the virtual keyboard is open so it never
     covers the field being typed into */
  .mp-kb-open .mp-mobile-footer { display: none !important; }
  /* Pull-to-refresh indicator — drops from under the topbar while pulling */
  #mpPullRefresh {
    position: fixed;
    top: calc(60px + env(safe-area-inset-top, 0px));
    left: 50%;
    margin-left: -17px;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 6px 18px rgba(15,23,42,.18);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--mp-primary, #0057FF);
    font-size: 15px;
    z-index: 500;
    transform: translateY(-70px);
    opacity: 0;
    transition: transform .12s ease, opacity .12s ease, background .15s ease, color .15s ease;
    pointer-events: none;
  }
  #mpPullRefresh.ready { background: var(--mp-primary, #0057FF); color: #fff; }
  #mpPullRefresh.loading { transform: translateY(0) !important; opacity: 1 !important; }
  #mpPullRefresh.loading i { animation: mpPtrSpin .7s linear infinite; }
  @keyframes mpPtrSpin { to { transform: rotate(360deg); } }
  @media (min-width: 1025px), (min-width: 1024px) and (orientation: landscape) {
    .mp-mobile-footer { display: none !important; }
    .screen { padding-bottom: 24px !important; }
  }

  /* ===== In-app select (mp-select) hardening =====
  .mp-select-wrap { position: relative; width: 100%; max-width: 100%; min-width: 0; }
  .mp-select-trigger { width: 100%; max-width: 100%; min-width: 0; box-sizing: border-box;
    display: flex; align-items: center; justify-content: space-between; gap: 8px; }
  .mp-select-trigger > span,
  .mp-select-trigger .mp-select-value { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .mp-select-options { box-sizing: border-box; max-width: 100%; }
  .mp-select-option { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  /* Grid columns must be allowed to shrink (default min-width:auto sizes a
     track to its content and overflows the form). */
  .form-row, .mp-form-row { min-width: 0; }
  .form-row > *, .mp-form-row > * { min-width: 0; }

  /* ===== Mobile header: stop the title being crushed =====
     The topbar is back button + titles + page action + injected Clock In +
     avatar. On a phone that stack overflowed the bar, and because
     .topbar-titles was the only flexible child it absorbed ALL the squeeze:
     measured on /mobile/patients at 390px the title had 42px for a heading
     that needs 62px (clipped), and at 320px it was 0px — the screen name
     vanished entirely.
     The store and screen names stay visible; long text ellipsises while the
     avatar and tap controls keep their own space. At narrow widths Clock In
     becomes icon-only and page actions can compact to keep the header usable. */
  .topbar-titles { flex: 1 1 auto !important; min-width: 0 !important; }
  .topbar .store-name { display: block !important; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
  .topbar h1 { min-width: 0; }
  .topbar .back, .topbar .clock-btn, .topbar .mp-time-pill, .topbar .mp-avatar-wrap { flex: 0 0 auto; }

  @media (max-width: 480px) {
    /* Keep both store and screen names visible; let long names ellipsize. */
    .topbar { gap: 8px !important; }
    .topbar .store-name { font-size: 9px !important; margin-bottom: 1px; }
    .topbar h1 { font-size: 16px !important; line-height: 1.2; }
  }
  @media (max-width: 460px) {
    /* Clock In becomes icon-only, keeping a comfortable 44px tap target. */
    .topbar .clock-btn { padding: 0 12px !important; min-height: 40px; gap: 0 !important; }
    .topbar .clock-btn .clock-label { display: none !important; }
    .topbar .clock-btn i { font-size: 15px !important; }
    .topbar .mp-avatar-wrap { margin-left: 8px !important; }
    /* Time pill keeps its text (the time is the whole point) but drops the
       icon and tightens up so the screen name keeps its room. */
    .topbar .mp-time-pill { padding: 6px 8px !important; gap: 0 !important; font-size: 12px !important; }
    .topbar .mp-time-pill i { display: none !important; }
  }
  @media (max-width: 360px) {
    .topbar { gap: 6px !important; }
    .topbar .mp-avatar-wrap { margin-left: 4px !important; }
    .topbar .mp-time-pill { padding: 5px 7px !important; font-size: 11px !important; }
    .topbar .avatar { width: 34px !important; height: 34px !important; }
    .topbar .add, .topbar .topbar-action { padding: 8px 10px !important; font-size: 12px !important; }
  }

  /* ===== In-app select (mp-select) hardening =====
     Every mobile form builds its own mp-select from the same snippet, and the
     custom control replaces a hidden <select>. Two failure modes made fields
     look "folded" or cut off, so both are corrected once, here, for all of
     them:
       1. The custom control is a plain div, so it does NOT inherit the width
          the hidden <select> would have taken. Inside a CSS grid the track
          then sized to the longest option text, overflowing the column and
          clipping content. Pin the wrapper and trigger to the column width.
       2. A long option (e.g. a country or state name) was clamped to one line
          with no ellipsis, so it spilled past the field edge. Give the label
          an ellipsis instead of letting it clip.
     Fixing it here beats editing 39 near-identical copies. */
</style>
<link rel="stylesheet" href="<?= base_url('theme/dist/css/application-controls.css?v=1'); ?>">
<div id="mpPullRefresh" aria-hidden="true"><i class="fa fa-refresh"></i></div>
<?php $this->load->view('comman/incident_banner', ['context' => 'mobile']); ?>
<div class="mp-mobile-footer">
  <nav class="mp-mobile-bottom-nav <?= $is_physio ? 'physio-mobile-bottom-nav' : ''; ?><?= $is_garden ? ' mp-garden-nav' : ''; ?><?= $is_building ? ' mp-building-nav' : ''; ?>" aria-label="Mobile workspace navigation">
  <?php if($is_building): ?>
    <a href="<?= base_url('mobile'); ?>" class="nav-item <?= $active === 'home' ? 'active' : ''; ?>"><i class="fa fa-home icon"></i><span>Home</span></a>
    <?php if($CI->permissions('service_jobs_view')): ?><a href="<?= base_url('building_projects?screen=mobile'); ?>" class="nav-item"><i class="fa fa-tasks icon"></i><span>Projects</span></a><?php endif; ?>
    <button type="button" data-bm-clock-trigger aria-haspopup="dialog" class="nav-item"><i class="fa fa-clock-o icon"></i><span data-bm-clock-label><?= $needs_clock_out ? 'Clock Out' : 'Clock In'; ?></span></button>
    <?php if($CI->permissions('sales_view')): ?><a href="<?= base_url('mobile/sales_list'); ?>" class="nav-item <?= $active === 'sales' ? 'active' : ''; ?>"><i class="fa fa-file-text-o icon"></i><span>Invoices</span></a><?php endif; ?>
    <a href="<?= base_url('building_workspace/more?screen=mobile'); ?>" class="nav-item <?= $active === 'more' ? 'active' : ''; ?>"><i class="fa fa-ellipsis-h icon"></i><span>More</span></a>
  <?php elseif($is_physio): ?>
    <?php if(physio_can_any(array('patients_view','appointments_view','care_queue_view','admissions_view'))): ?><a href="<?= base_url('mobile'); ?>" class="nav-item <?= $active === 'home' ? 'active' : ''; ?>"><i class="fa fa-inbox icon"></i><span>Home</span></a><?php endif; ?>
    <?php if(physio_can('patients_view')): ?><a href="<?= base_url('mobile/patients'); ?>" class="nav-item <?= $active === 'patients' ? 'active' : ''; ?>"><i class="fa fa-address-book-o icon"></i><span>Patients</span></a><?php endif; ?>
    <?php if(physio_can('care_queue_view')): ?><a href="<?= base_url('mobile/care_queue'); ?>" class="nav-item <?= $active === 'care_queue' ? 'active' : ''; ?>"><i class="fa fa-list-ol icon"></i><span>Queue</span></a><?php endif; ?>
    <?php if(physio_can('sessions_view')): ?><a href="<?= base_url('mobile/sessions'); ?>" class="nav-item <?= $active === 'sessions' ? 'active' : ''; ?>"><i class="fa fa-stethoscope icon"></i><span>Sessions</span></a><?php endif; ?>
    <?php
      /*
       * Ward tasks entry.
       *
       * One screen serves both boards, but a PORTER was shown the label
       * "Nursing" with a heartbeat icon — so the one person whose job is moving
       * patients could not find their own work. The label and icon now follow
       * the viewer's primary duty: a porter-only role sees "Porter" with the
       * exchange icon, anyone with nursing duties sees "Nursing".
       */
      $wardCanNurse  = physio_can('nursing_tasks_view');
      $wardCanPorter = physio_can('porter_tasks_view');
      if($wardCanNurse || $wardCanPorter):
        $wardIcon  = $wardCanNurse ? 'fa-heartbeat' : 'fa-exchange';
        $wardLabel = $wardCanNurse ? 'Nursing' : 'Porter';
    ?>
    <a href="<?= base_url('mobile/ward_tasks'); ?>" class="nav-item <?= $active === 'ward_tasks' ? 'active' : ''; ?>"><i class="fa <?= $wardIcon; ?> icon"></i><span><?= $wardLabel; ?></span></a>
    <?php endif; ?>
    <?php if(physio_can('admissions_view')): ?><a href="<?= base_url('mobile/admissions'); ?>" class="nav-item <?= $active === 'admissions' ? 'active' : ''; ?>"><i class="fa fa-bed icon"></i><span>Beds</span></a><?php endif; ?>
    <?php if(physio_can_any(array('patient_billing_view','patient_funds_view'))): ?><a href="<?= base_url('mobile/patient_accounts'); ?>" class="nav-item <?= $active === 'accounts' ? 'active' : ''; ?>"><i class="fa fa-file-text-o icon"></i><span>Accounts</span></a><?php endif; ?>
    <?php if($can_more): ?><a href="<?= base_url('mobile/more'); ?>" class="nav-item <?= $active === 'more' ? 'active' : ''; ?>"><i class="fa fa-ellipsis-h icon"></i><span>More</span></a><?php endif; ?>
  <?php elseif($is_garden): ?>
  <?php
    /*
     * Garden Centre & Landscaping workspace.
     *
     * This is not a retail storefront, so the generic Home/POS/Sale/Hold menu is
     * wrong here: it leads a plant & landscaping business straight to a checkout
     * till. The footer mirrors the garden workspace instead (overview, stock,
     * projects, sales) and everything else stays one tap away in the workspace
     * menu / More screen.
     */
    if(!function_exists('mp_garden_navigation_visible')){ $CI->load->helper('garden'); }
    $garden_profile = function_exists('mp_get_store_profile') ? (mp_get_store_profile() ?: array()) : array();
    $garden_path    = trim((string)$CI->uri->uri_string(), '/');
    // Longest match first so e.g. mobile/sales_list wins over mobile/sales.
    $garden_tabs    = array(
      'more'     => array('mobile/more'),
      'stock'    => array('garden/catalogue','garden/nursery','garden/materials','garden/production','garden/plant','mobile/catalogue','mobile/nursery','mobile/materials','mobile/production','mobile/stock','mobile/product','mobile/items','mobile/price_catalogue'),
      'projects' => array('garden/projects','garden/project','garden/work','garden/maintenance','mobile/projects','mobile/project','mobile/work','mobile/maintenance','mobile/service_jobs'),
      'sales'    => array('garden/sales_report','garden/daily_summary','mobile/sales','mobile/sale','mobile/pos','mobile/holds','mobile/quotations','mobile/quotation','mobile/leads','mobile/customers','mobile/due'),
    );
    $garden_tab = 'home';
    foreach($garden_tabs as $garden_name => $garden_prefixes){
      foreach($garden_prefixes as $garden_prefix){
        if($garden_path === $garden_prefix || strpos($garden_path, $garden_prefix.'/') === 0){ $garden_tab = $garden_name; break 2; }
      }
    }
    $garden_can = array(
      'home'     => $CI->permissions('dashboard_view'),
      'stock'    => $CI->permissions('items_view'),
      'projects' => function_exists('mp_garden_navigation_visible')
        && mp_garden_navigation_visible(array('service_jobs_view','garden/projects','Landscaping projects','fa-map-o','landscaping','service_jobs'), $garden_profile),
      'sales'    => $CI->permissions('sales_view'),
    );
  ?>
  <?php if($garden_can['home']): ?>
  <a href="<?= base_url('garden?screen=mobile'); ?>" class="nav-item <?= $garden_tab === 'home' ? 'active' : ''; ?>">
    <i class="fa fa-th-large icon"></i>
    <span>Home</span>
  </a>
  <?php endif; ?>
  <?php if($garden_can['stock']): ?>
  <a href="<?= base_url('garden/catalogue?screen=mobile'); ?>" class="nav-item <?= $garden_tab === 'stock' ? 'active' : ''; ?>">
    <i class="fa fa-pagelines icon"></i>
    <span>Stock</span>
  </a>
  <?php endif; ?>
  <?php if($garden_can['projects']): ?>
  <a href="<?= base_url('garden/projects?screen=mobile'); ?>" class="nav-item <?= $garden_tab === 'projects' ? 'active' : ''; ?>">
    <i class="fa fa-map-o icon"></i>
    <span>Projects</span>
  </a>
  <?php endif; ?>
  <?php if($garden_can['sales']): ?>
  <a href="<?= base_url('garden/sales_report?screen=mobile'); ?>" class="nav-item <?= $garden_tab === 'sales' ? 'active' : ''; ?>">
    <i class="fa fa-file-text-o icon"></i>
    <span>Sales</span>
  </a>
  <?php endif; ?>
  <a href="<?= base_url('mobile/more'); ?>" class="nav-item <?= $garden_tab === 'more' ? 'active' : ''; ?>">
    <i class="fa fa-ellipsis-h icon"></i>
    <span>More</span>
  </a>
  <?php else: ?>
  <?php if($can_home): ?>
  <a href="<?= base_url('mobile'); ?>" class="nav-item <?= ($active == 'home') ? 'active' : ''; ?>">
    <i class="fa fa-home icon"></i>
    <span>Home</span>
  </a>
  <?php endif; ?>
  <?php if($can_pos): ?>
  <a href="<?= base_url('mobile/pos'); ?>" class="nav-item <?= ($active == 'pos') ? 'active' : ''; ?>">
    <i class="fa fa-calculator icon"></i>
    <span>POS</span>
  </a>
  <?php endif; ?>
  <?php if($can_sale): ?>
  <a href="<?= base_url('mobile/sale'); ?>" class="nav-item <?= ($active == 'sale') ? 'active' : ''; ?>">
    <i class="fa fa-file-text-o icon"></i>
    <span>Sale</span>
  </a>
  <?php endif; ?>
  <?php if($can_holds): ?>
    <a href="<?= base_url('mobile/holds'); ?>" class="nav-item hold <?= ($active == 'holds') ? 'active' : ''; ?>">
      <i class="fa fa-pause-circle-o icon"></i>
      <span>Hold</span>
    </a>
  <?php elseif($can_purchase): ?>
    <a href="<?= base_url('mobile/purchase'); ?>" class="nav-item <?= ($active == 'purchase') ? 'active' : ''; ?>">
      <i class="fa fa-cart-arrow-down icon"></i>
      <span>Purchase</span>
    </a>
  <?php endif; ?>
  <?php if($can_sales_list): ?>
  <a href="<?= base_url('mobile/sales_list'); ?>" class="nav-item <?= ($active == 'sales_list') ? 'active' : ''; ?>">
    <i class="fa fa-list icon"></i>
    <span>Sales</span>
  </a>
  <?php endif; ?>
  <?php if($can_more): ?>
  <a href="<?= base_url('mobile/more'); ?>" class="nav-item <?= ($active == 'more') ? 'active' : ''; ?>">
    <i class="fa fa-user icon"></i>
    <span>More</span>
  </a>
  <?php endif; ?>
  <?php endif; ?>
</nav>
<footer class="mp-mobile-copyright">
  <?php $this->load->view('mobile/copyright'); ?>
</footer>
</div>

<script>
(function(){var footer=document.querySelector('.mp-mobile-footer');if(!footer)return;function measure(){var height=footer.getBoundingClientRect().height;if(height>0){document.documentElement.style.setProperty('--mp-mobile-footer-height',height+'px');document.documentElement.style.setProperty('--mp-mobile-assist-bottom',(height+12)+'px');}}measure();window.addEventListener('resize',measure);if(window.ResizeObserver)new ResizeObserver(measure).observe(footer);})();
</script>
<?php $this->load->view('idle_lock'); ?>

<template id="mpTopbarExtras">
  <?php if($is_building || !is_store_admin()): ?>
  <?php if($is_building): ?><button type="button" data-bm-clock-trigger aria-haspopup="dialog" class="clock-btn <?= $needs_clock_out ? 'out' : ''; ?>"><?php else: ?><a href="<?= base_url('mobile/clock'); ?>" class="clock-btn <?= $needs_clock_out ? 'out' : ''; ?>"><?php endif; ?>
    <i class="fa <?= $needs_clock_out ? 'fa-sign-out' : 'fa-sign-in'; ?>"></i>
    <?php /* Wrapped in a span so it can be hidden on a narrow phone — a bare
             text node cannot be targeted by CSS, which is why the label stayed
             visible and kept crushing the screen title. */ ?>
    <span class="clock-label" <?= $is_building ? 'data-bm-clock-label' : ''; ?>><?= $needs_clock_out ? 'Clock Out' : 'Clock In'; ?></span>
  <?php if($is_building): ?></button><?php else: ?></a><?php endif; ?>
  <?php else: ?>
  <div class="mp-time-pill" aria-label="Current time">
    <i class="fa fa-clock-o"></i>
    <span class="mp-time-val">--:--</span>
  </div>
  <?php endif; ?>
  <div class="mp-avatar-wrap">
    <button type="button" class="avatar mp-avatar-trigger" aria-haspopup="true" aria-label="Open account menu" style="color:var(--mp-primary);">
      <?php if(!empty($profile_picture)): ?>
        <img src="<?= base_url($profile_picture); ?>" alt="Profile">
      <?php else: ?>
        <?= strtoupper(substr($display_name,0,1)); ?>
      <?php endif; ?>
    </button>
    <div class="mp-avatar-menu">
      <a href="<?= base_url('mobile/profile'); ?>" class="mp-avatar-item">
        <i class="fa fa-user"></i>
        <span>My Profile</span>
      </a>
      <a href="<?= base_url('logout'); ?>" class="mp-avatar-item logout" onclick="return mpLogout(this, event);">
        <i class="fa fa-sign-out"></i>
        <span>Log Out</span>
      </a>
    </div>
  </div>
</template>
<?php $this->load->view('components/dialog'); ?>
<script>
  (function(){
    if(typeof window.mpLogout !== 'function'){
      window.mpLogout = function(el, ev){
        if(ev && ev.preventDefault) ev.preventDefault();
        if(ev && ev.stopPropagation) ev.stopPropagation();
        mpConfirmAsync('Are you sure you want to log out?', { title:'Confirm', okText:'Log Out' }).then(function(ok){
          if(!ok) return;
          window.location.href = el.getAttribute('href') || el.href;
        });
        return false;
      };
    }

    var tpl = document.getElementById('mpTopbarExtras');
    if(tpl){
      var html = tpl.innerHTML;
      document.querySelectorAll('.topbar').forEach(function(tb){
        if(tb.querySelector('.mp-avatar-wrap')) return;
        var oldAv = tb.querySelector('.avatar');
        if(oldAv) oldAv.remove();
        tb.insertAdjacentHTML('beforeend', html);
      });
    }

    (function(){
      if(!document.querySelector('.mp-time-val')) return;
      function tick(){
        var t = new Date().toLocaleTimeString([], {hour: 'numeric', minute: '2-digit'});
        document.querySelectorAll('.mp-time-val').forEach(function(el){ el.textContent = t; });
      }
      tick();
      setInterval(tick, 15000);
    })();

    function closeAllAvatarMenus(){
      document.querySelectorAll('.mp-avatar-menu').forEach(function(m){ m.classList.remove('open'); });
    }

    document.addEventListener('click', function(e){
      var trigger = e.target.closest('.mp-avatar-trigger');
      if(trigger){
        e.preventDefault();
        e.stopPropagation();
        var menu = trigger.parentNode.querySelector('.mp-avatar-menu');
        if(menu){
          var wasOpen = menu.classList.contains('open');
          closeAllAvatarMenus();
          if(!wasOpen) menu.classList.add('open');
        }
        return;
      }
      if(!e.target.closest('.mp-avatar-menu')){
        closeAllAvatarMenus();
      }
    });

    // Virtual keyboard: hide the fixed footer while typing so it can't
    // cover the focused field (item creation, POS, forms).
    function mpKbSync(){
      var vv = window.visualViewport;
      var open = vv ? (vv.height < window.innerHeight * 0.75) : false;
      document.body.classList.toggle('mp-kb-open', open);
    }
    if(window.visualViewport){
      window.visualViewport.addEventListener('resize', mpKbSync);
      window.visualViewport.addEventListener('scroll', mpKbSync);
    }
    document.addEventListener('focusin', function(e){
      if(e.target && e.target.matches && e.target.matches('input, textarea, select, [contenteditable="true"]')){
        document.body.classList.add('mp-kb-open');
      }
    });
    document.addEventListener('focusout', function(){
      setTimeout(function(){
        mpKbSync();
        if(!document.querySelector('input:focus, textarea:focus, select:focus, [contenteditable="true"]:focus')){
          document.body.classList.remove('mp-kb-open');
        }
      }, 200);
    });

    // Pull-to-refresh: a deliberate downward drag at the very top of the page
    // reloads it (native PTR is disabled app-wide via overscroll-behavior).
    // Screens holding unsaved transactional state opt out with
    // window.MP_NO_PULL_REFRESH = true or <body data-no-ptr>.
    (function(){
      if(window.MP_NO_PULL_REFRESH || (document.body && document.body.hasAttribute('data-no-ptr'))) return;
      var ind = document.getElementById('mpPullRefresh');
      if(!ind || !('ontouchstart' in window)) return;
      var THRESHOLD = 64, MAXPULL = 130;
      var armed = false, startY = 0, shown = 0, refreshing = false;
      var icon = ind.querySelector('i');
      function top(){ return window.pageYOffset || document.documentElement.scrollTop || document.body.scrollTop || 0; }
      function hide(){ ind.classList.remove('ready'); ind.style.transform = ''; ind.style.opacity = ''; if(icon) icon.style.transform = ''; }
      document.addEventListener('touchstart', function(e){
        if(refreshing || e.touches.length !== 1){ armed = false; return; }
        armed = top() <= 0;
        startY = e.touches[0].clientY;
        shown = 0;
      }, {passive: true});
      document.addEventListener('touchmove', function(e){
        if(!armed || refreshing) return;
        var dy = e.touches[0].clientY - startY;
        if(dy <= 4 || top() > 0){ shown = 0; hide(); return; }
        shown = Math.min(dy * 0.5, MAXPULL);
        ind.style.transform = 'translateY(' + (shown - 70) + 'px)';
        ind.style.opacity = Math.min(1, shown / THRESHOLD);
        ind.classList.toggle('ready', shown >= THRESHOLD);
        if(icon) icon.style.transform = 'rotate(' + Math.min(dy, 360) + 'deg)';
      }, {passive: true});
      document.addEventListener('touchend', function(){
        if(!armed || refreshing) return;
        armed = false;
        if(shown >= THRESHOLD){
          refreshing = true;
          ind.classList.remove('ready');
          ind.classList.add('loading');
          setTimeout(function(){ window.location.reload(); }, 220);
        } else {
          hide();
        }
      }, {passive: true});
    })();
  })();
</script>
