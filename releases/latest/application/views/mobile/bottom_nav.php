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
  $user_id = get_current_user_id();
  $store_id = get_current_store_id();
  $display_name = $CI->session->userdata('display_name') ?: $CI->session->userdata('username') ?: 'User';
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
      } elseif($CI->db->table_exists('db_logos') && !empty($store_id)){
        $logoResult = $CI->db->where('store_id', $store_id)->where('status', 1)->order_by('id', 'desc')->get('db_logos');
        if($logoResult && is_object($logoResult)){
          $logo = $logoResult->row();
          if($logo && !empty($logo->logo) && file_exists(FCPATH . $logo->logo)){
            $profile_picture = $logo->logo;
          }
        }
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
      if($shift){
        $needs_clock_out = $CI->attendance_model->needsClockOut($user_id, date('Y-m-d'));
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
  .mp-mobile-bottom-nav .nav-item.active { color: #0057FF; }
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
    text-align: center;
    padding: 6px 0 calc(6px + env(safe-area-inset-bottom, 0px));
    font-size: 11px;
    color: #94A3B8;
    background: #fff;
  }
  .screen { padding-bottom: calc(160px + env(safe-area-inset-bottom, 0px)) !important; }
  .topbar { position: sticky; top: 0; z-index: 400; background: #fff; border-bottom: 1px solid var(--mp-border); padding-top: calc(8px + env(safe-area-inset-top, 0px)) !important; padding-bottom: 8px; }
  .topbar h1 { font-size: clamp(16px, 4.5vw, 22px); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
  .clock-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 12px; background: var(--mp-success); color: #fff; font-size: 13px; font-weight: 600; text-decoration: none; white-space: nowrap; }
  .clock-btn.out { background: var(--mp-danger); }
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
  @media (min-width: 1024px) {
    .mp-mobile-footer { display: none !important; }
    .screen { padding-bottom: 24px !important; }
  }
</style>
<div class="mp-mobile-footer">
  <nav class="mp-mobile-bottom-nav">
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
</nav>
<footer class="mp-mobile-copyright">
  &copy; <?= date('Y'); ?> <?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?>. All rights reserved.
</footer>
</div>

<template id="mpTopbarExtras">
  <a href="<?= base_url('mobile/clock'); ?>" class="clock-btn <?= $needs_clock_out ? 'out' : ''; ?>">
    <i class="fa <?= $needs_clock_out ? 'fa-sign-out' : 'fa-sign-in'; ?>"></i>
    <?= $needs_clock_out ? 'Clock Out' : 'Clock In'; ?>
  </a>
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
<script>
  (function(){
    if(typeof window.mpLogout !== 'function'){
      window.mpLogout = function(el, ev){
        if(ev && ev.preventDefault) ev.preventDefault();
        if(ev && ev.stopPropagation) ev.stopPropagation();
        if(confirm('Are you sure you want to log out?')){
          window.location.href = el.getAttribute('href') || el.href;
        }
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
  })();
</script>
