<?php
$CI =& get_instance();
// CENTRAL MENU MODE.
//
// On the vendor's central domain the sidebar can run in two modes, driven by
// db_sitesettings.central_slim_menu (the checkbox on the Central Dashboard):
//
//   central_slim_menu = 0 (DEFAULT) -> show ALL menus. Central works like a
//                           normal install; the Central group is appended.
//   central_slim_menu = 1            -> HIDE the retail menus; only the
//                           Dashboard + Central group remain.
//
// Default is 0 = full menu. A missing column also means full, so a fresh
// Central shows everything until the vendor ticks "Slim menu". (This was
// previously inverted — it defaulted to slim, so Central came up with the
// retail menus hidden before the vendor had chosen anything.)
//
// mp_is_central() is domain-pinned, so on a client install $is_central is
// always false and this block is inert.
$is_central_install = function_exists('mp_is_central') && mp_is_central();
$hide_retail_menus  = false;
if ($is_central_install) {
  $slim = 0;                       // 0 = full menu (default)
  try {
    if ($CI->db->field_exists('central_slim_menu', 'db_sitesettings')) {
      $row = $CI->db->select('central_slim_menu')->where('id', 1)->get('db_sitesettings')->row();
      $slim = $row ? (int) $row->central_slim_menu : 0;
    }
  } catch (Exception $e) { $slim = 0; }
  $hide_retail_menus = ($slim === 1);
}
// $is_central gates the retail menus below; it means "hide them".
$is_central = $hide_retail_menus;
$industry = mp_get_store_profile()['industry_type'] ?? 'general_retail';
$is_car = $industry === 'car_dealership';
$is_creator = $industry === 'creator';
// A print shop gets its own rail. The workspace lives at /printing (the
// dashboard redirects there), so the sidebar must offer its screens rather than
// the retail set — otherwise the module is reachable but unnavigable, which is
// exactly how it behaved before: /printing loaded and the menu showed Sales,
// Inventory and Clients.
$is_printing = ($industry === 'printing') && function_exists('mp_feature_enabled') && mp_feature_enabled('production_workflow');
// NOTE on $is_printing: it adds the printing GROUPS (Print Shop, Machine
// Floor) and reorders the top of the rail. It does NOT hide the shared
// business groups.
//
// An earlier attempt did hide them, guarding every group with a $hide_retail
// flag. That was wrong: a print shop's own feature set includes accounts,
// warehouse, online_store, promotions, custom_orders, leads, staff assignment
// and delivery scheduling, so hiding those groups removed Promotions, Catalog,
// Finance, Marketing, Reports, Operations, Purchases and Clients — menus the
// business actually uses. The only thing printing genuinely should not show is
// a cart-led shop experience, and that is decided by the storefront, not here.
// SVG icon set (Feather-style, matching prototype)
$mp_icons = [
  'dashboard' => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>',
  'pos'       => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>',
  'sales'     => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>',
  'catalog'   => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>',
  'promo'     => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/><line x1="7" y1="7" x2="7.01" y2="7"/></svg>',
  'purchase'  => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>',
  'inventory' => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>',
  'customers' => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
  'finance'   => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
  'marketing' => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 11l8-8 8 8-8 8-8-8z"/><path d="M7 7l10 10"/></svg>',
  'reports'   => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M23 6l-9.5 9.5-5-5L1 18"/><polyline points="17 6 23 6 23 12"/></svg>',
  'online'    => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>',
  'ops'       => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
  'admin'     => '<svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z"/></svg>',
  'list'      => '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>',
  'plus'      => '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>',
  'chevron'   => '<svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>',
];
?>
<!-- ===== SHELL ===== -->
<div class="mp-shell">
  <nav class="mp-nav">
    <div class="mp-nav-section">
      <?php if($is_creator): ?>
      <a href="<?= base_url('creator'); ?>" class="mp-nav-item active creator-active-li"><span class="mp-nav-icon"><?= $mp_icons['dashboard']; ?></span> Dashboard</a>
      <a href="<?= base_url('dashboard?classic=1'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['reports']; ?></span> Business Overview</a>
      <?php elseif($is_printing): ?>
      <a href="<?= base_url('printing'); ?>" class="mp-nav-item printing-active-li"><span class="mp-nav-icon"><?= $mp_icons['dashboard']; ?></span> Printing Overview</a>
      <?php else: ?>
      <a href="<?= base_url('dashboard'); ?>" class="mp-nav-item active"><span class="mp-nav-icon"><?= $mp_icons['dashboard']; ?></span> Dashboard</a>
      <?php endif; ?>
    </div>

    <?php
      /*
       * Helpers shared by EVERY rail — defined before any industry branch.
       *
       * These used to be declared inside the `if($is_printing)` block, but they
       * are called from shared markup further down ("Business Management"), so
       * the moment a store was switched AWAY from printing the closure did not
       * exist and the call threw "Function name must be a string". That killed
       * the whole sidebar on every page — the header rendered and nothing else.
       *
       * A helper used by shared markup must be defined in shared scope, above
       * the branch that happens to be the first to use it.
       */
      // Which route is open right now — used to open the owning group.
      $mp_here = trim((string) $CI->uri->uri_string(), '/');
      $mp_open = function ($prefixes) use ($mp_here) {
          foreach ((array) $prefixes as $p) {
              if ($p !== '' && strpos($mp_here, $p) === 0) return true;
          }
          return false;
      };
      // $sec() prints a section caption in the rail.
      $sec = function ($label) { echo '<div class="mp-nav-label">' . htmlspecialchars($label) . '</div>'; };
    ?>

    <?php if(!$is_central): ?>
    <?php if($is_printing): ?>
    <!-- ===== PRINTING WORKSPACE MENUS =====
         Order follows the actual working day, not the module layout:
         Daily Work (quote → job → production → machines → materials),
         then Business Management (sales, customers, catalogue, purchasing,
         stock, finance), then Growth (reports, marketing, storefront), then
         System (help, settings).

         Section labels are small captions, not groups — they cannot be
         collapsed and hold no links, so they add orientation without another
         click. Groups start collapsed EXCEPT the one containing the current
         page, so the rail opens showing where you are rather than a wall of
         fifteen expanded menus.

         Every link keeps the permission the controller itself checks, so a
         destination and its gate cannot disagree. -->
    <?php $sec('Daily Work'); ?>

    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#7C3AED;"><?= $mp_icons['sales']; ?></span> Quotations <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <?php if($CI->permissions('print_quote')): ?>
        <a href="<?= base_url('printing/new_quotation'); ?>" class="mp-nav-item printing-new_quotation-active-li"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> New Quote</a>
        <?php endif; ?>
        <?php if($CI->permissions('print_quote')): ?>
        <a href="<?= base_url('printing/unlinked_quotations'); ?>" class="mp-nav-item printing-unlinked_quotations-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Awaiting a Job</a>
        <?php endif; ?>
        <?php if($CI->permissions('quotation_view')): ?>
        <a href="<?= base_url('quotation'); ?>" class="mp-nav-item quotation-list-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> All Quotes</a>
        <?php endif; ?>
      </div>
    </div></div>

    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#0E7490;"><?= $mp_icons['ops']; ?></span> Print Jobs <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <?php // No "Overview" here. It pointed at /printing — the same route as
              // the Printing Overview link at the top of the rail, so it was a
              // second door to one room. ?>
        <?php if($CI->permissions('print_view')): ?>
        <a href="<?= base_url('printing/jobs'); ?>" class="mp-nav-item printing-jobs-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> <?= mp_label('service_order'); ?>s</a>
        <?php endif; ?>
        <?php if($CI->permissions('print_artwork')): ?>
        <a href="<?= base_url('printing/artworks'); ?>" class="mp-nav-item printing-artworks-active-li"><span class="mp-nav-icon"><?= $mp_icons['catalog']; ?></span> Artwork</a>
        <?php endif; ?>
        <?php if($CI->permissions('print_authorize')): ?>
        <a href="<?= base_url('printing/authorizations'); ?>" class="mp-nav-item printing-authorizations-active-li"><span class="mp-nav-icon"><?= $mp_icons['admin']; ?></span> Authorisations</a>
        <?php endif; ?>
        <?php if($CI->permissions('print_payments')): ?>
        <a href="<?= base_url('printing/payments'); ?>" class="mp-nav-item printing-payments-active-li"><span class="mp-nav-icon"><?= $mp_icons['finance']; ?></span> Job Payments</a>
        <?php endif; ?>
      </div>
    </div></div>

    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#2563EB;"><?= $mp_icons['inventory']; ?></span> Production <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <?php if($CI->permissions('print_view')): ?>
        <a href="<?= base_url('printing/production'); ?>" class="mp-nav-item printing-production-active-li"><span class="mp-nav-icon"><?= $mp_icons['inventory']; ?></span> Production Floor</a>
        <?php endif; ?>
        <?php /* Collection & Delivery has no screen — Printing::fulfil() is a
                 POST endpoint that records a handover, not a page. Listed as
                 separate work rather than linked, because a menu item pointing
                 at a write endpoint is what caused the old Stage Report bug. */ ?>
      </div>
    </div></div>

    <?php if($CI->permissions('print_view')): ?>
    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#0891B2;"><?= $mp_icons['ops']; ?></span> Machines &amp; Maintenance <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <a href="<?= base_url('printing_ops/machines'); ?>" class="mp-nav-item operations-machines-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Machines</a>
        <a href="<?= base_url('printing_ops/readings'); ?>" class="mp-nav-item operations-readings-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Counter Readings</a>
        <a href="<?= base_url('printing_ops/maintenance'); ?>" class="mp-nav-item operations-maintenance-active-li"><span class="mp-nav-icon"><?= $mp_icons['ops']; ?></span> Maintenance</a>
        <a href="<?= base_url('printing_ops/supplies'); ?>" class="mp-nav-item operations-supplies-active-li"><span class="mp-nav-icon"><?= $mp_icons['purchase']; ?></span> Consumables</a>
      </div>
    </div></div>

    <?php /* Customer materials is NOT under machines. A client can hand over
             their own stock for a job that never touches a press — and the
             material is theirs, not the shop's asset. Filing it under equipment
             implied both. */ ?>
    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#C026D3;"><?= $mp_icons['customers']; ?></span> Customer Materials <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <a href="<?= base_url('printing_ops/custody'); ?>" class="mp-nav-item operations-custody-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Materials Held</a>
        <?php /* Receipts, allocations and returns are sections INSIDE this
                 screen, not separate destinations — the model exposes
                 get_customer_materials / unallocated_materials /
                 breakdown_mismatches and the view renders all three. */ ?>
      </div>
    </div></div>
    <?php endif; /* end printing-only rail */ ?>
    <?php endif; /* end if(!$is_central) — the shared business groups below render for printing too */ ?>

    <?php $sec('Business Management'); ?>

    <?php if($is_creator): ?>
    <!-- ===== CREATOR WORKSPACE MENUS ===== -->
    <div class="mp-nav-section"><div class="mp-nav-group open" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#7C3AED;"><?= $mp_icons['catalog']; ?></span> Products <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <a href="<?= base_url('creator/products'); ?>" class="mp-nav-item creator-products-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> All Products</a>
        <?php if(mp_feature_enabled('digital_products')): ?>
          <a href="<?= base_url('creator/products/digital'); ?>" class="mp-nav-item creator-products-digital-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Digital Products</a>
        <?php endif; ?>
        <?php if(mp_feature_enabled('courses')): ?>
          <a href="<?= base_url('courses'); ?>" class="mp-nav-item courses-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Courses &amp; Curriculum</a>
        <?php endif; ?>
        <?php if(mp_feature_enabled('memberships')): ?>
          <a href="<?= base_url('memberships'); ?>" class="mp-nav-item memberships-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Membership Plans</a>
        <?php endif; ?>
        <div class="mp-nav-subhead">Create</div>
        <?php if(mp_feature_enabled('digital_products')): ?><a href="<?= base_url('creator/create/digital'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> New Digital Product</a><?php endif; ?>
        <?php if(mp_feature_enabled('courses')): ?><a href="<?= base_url('creator/create/course'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> New Course</a><?php endif; ?>
        <?php if(mp_feature_enabled('memberships')): ?><a href="<?= base_url('creator/create/membership'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> New Membership</a><?php endif; ?>
        <?php if($CI->permissions('items_category_view')): ?><a href="<?= base_url('category/view'); ?>" class="mp-nav-item category-view-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> <?= $is_creator ? 'Collections' : 'Categories'; ?></a><?php endif; ?>
      </div>
    </div></div>

    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#F97316;"><?= $mp_icons['sales']; ?></span> Sales <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <a href="<?= base_url('online_store/orders'); ?>" class="mp-nav-item online_store-orders-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Orders</a>
        <a href="<?= base_url('creator/buyers'); ?>" class="mp-nav-item creator-buyers-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Buyers</a>
        <?php if($CI->permissions('sales_payment_view')): ?><a href="<?= base_url('sales_payments/'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Payments</a><?php endif; ?>
        <?php if($CI->permissions('sales_add')): ?><a href="<?= base_url('sales/add'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> Manual Invoice</a><?php endif; ?>
      </div>
    </div></div>

    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#059669;"><?= $mp_icons['customers']; ?></span> Audience <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <?php if(mp_feature_enabled('courses')): ?><a href="<?= base_url('creator/students'); ?>" class="mp-nav-item creator-students-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Students</a><?php endif; ?>
        <?php if(mp_feature_enabled('memberships')): ?><a href="<?= base_url('creator/members'); ?>" class="mp-nav-item creator-members-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Members</a><?php endif; ?>
        <?php if($CI->permissions('customers_view')): ?><a href="<?= base_url('customers'); ?>" class="mp-nav-item customers_list-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> All Customers</a><?php endif; ?>
        <?php if($CI->permissions('customers_add')): ?><a href="<?= base_url('customers/add'); ?>" class="mp-nav-item customers_add-active-li"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> Add Customer</a><?php endif; ?>
        <?php if(mp_feature_enabled('leads') && ($CI->permissions('leads_view') || is_store_admin() || $this->session->userdata('role_id') == 1)): ?><a href="<?= base_url('leads'); ?>" class="mp-nav-item leads-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Leads</a><?php endif; ?>
        <?php if($CI->permissions('send_sms')): ?><a href="<?= base_url('sms'); ?>" class="mp-nav-item sms-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Send SMS</a><?php endif; ?>
      </div>
    </div></div>
    <?php endif; ?>

    <!-- Sales -->
    <?php if((!$is_creator) && ($CI->permissions('sales_add') || $CI->permissions('sales_view') || $CI->permissions('sales_return_view') || $CI->permissions('quotation_add') || $CI->permissions('quotation_view'))): ?>
    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#F97316;"><?= $mp_icons['sales']; ?></span> Sales <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <?php if($CI->permissions('sales_add')): ?><a href="<?= base_url('sales/add'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> New Sale</a><?php endif; ?>
        <?php if($CI->permissions('sales_view')): ?><a href="<?= base_url('sales'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Sales History</a><?php endif; ?>
        <?php if($CI->permissions('sales_payment_view')): ?><a href="<?= base_url('sales_payments/'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Sales Payments</a><?php endif; ?>
        <?php if($CI->permissions('installment_plans') && mp_feature_enabled('payplan')): ?><a href="<?= base_url('installments'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Installments</a><?php endif; ?>
        <?php if($CI->permissions('sales_return_view')): ?><a href="<?= base_url('sales_return'); ?>" class="mp-nav-item sales-returns-active-li sales-return-list-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Sales Returns</a><?php endif; ?>
        <?php // "New Quotation" and "Quotation History" were removed from here.
              // Both pointed at routes the Quotations group already owns
              // (quotation/add and quotation), so the same two screens appeared
              // under two menus. One destination, one home.
              //
              // NOTE for a PRINT shop: Job Payments (Money against a JOB,
              // db_print_payments) and Sales Payments (money against an
              // INVOICE, sales_payments) are deliberately BOTH kept. They are
              // different tables recording different events, not two views of
              // one ledger. ?>
        <?php if(mp_feature_enabled('manual_shipping') && $CI->permissions('sales_view')): ?><a href="<?= base_url('shipping_fees'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><i class="fa fa-truck"></i></span> Shipping Fees</a><?php endif; ?>
      </div>
    </div></div>
    <?php endif; ?>

    <!-- Catalog (hidden for creator) -->
    <?php if((!$is_creator) && ($CI->permissions('items_add') || $CI->permissions('items_view') || $CI->permissions('items_category_view') || $CI->permissions('brand_view') || $CI->permissions('attributes_view') || $CI->permissions('print_labels') || $CI->permissions('import_items') || $CI->permissions('services_add') || $CI->permissions('services_view') || $CI->permissions('service_packages_view') || $CI->permissions('variant_view') || (mp_feature_enabled('price_catalogue') && (is_admin() || is_store_admin())))): ?>
    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#2563EB;"><?= $mp_icons['catalog']; ?></span> Catalog <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <?php if($CI->permissions('items_add') && mp_feature_enabled('auto_parts')): ?><a href="<?= base_url('items/add'); ?>" class="mp-nav-item items-active-li"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> New <?= mp_label('item'); ?></a><?php endif; ?>
        <?php if($CI->permissions('services_add') && service_module()): ?><a href="<?= base_url('services/add'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> New Service</a><?php endif; ?>
        <?php if($CI->permissions('service_packages_view') && service_module()): ?><a href="<?= base_url('service_packages'); ?>" class="mp-nav-item service-packages-list-active-li service-packages-view-active-li service_packages-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Service Packages</a><?php endif; ?>
        <?php if(($CI->permissions('items_view') && mp_feature_enabled('auto_parts')) || $CI->permissions('services_view') || $CI->permissions('services_add')): ?><a href="<?= base_url('items'); ?>" class="mp-nav-item items-list-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> <?= mp_label('item'); ?> List</a><?php endif; ?>
        <?php if($CI->permissions('items_category_view')): ?><a href="<?= base_url('category/view'); ?>" class="mp-nav-item category-view-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Categories</a><?php endif; ?>
        <?php if($CI->permissions('brand_view')): ?><a href="<?= base_url('brands/view'); ?>" class="mp-nav-item brand-view-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Brands</a><?php endif; ?>
        <?php if($CI->permissions('attributes_view')): ?><a href="<?= base_url('attributes'); ?>" class="mp-nav-item attributes-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Attributes</a><?php endif; ?>
        <?php if($CI->permissions('print_labels') && mp_feature_enabled('auto_parts')): ?><a href="<?= base_url('items/labels'); ?>" class="mp-nav-item labels-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Print Labels</a><?php endif; ?>
        <?php if($CI->permissions('import_items')): ?><a href="<?= base_url('import/items'); ?>" class="mp-nav-item import_items-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Import Items</a><?php endif; ?>
        <?php if($CI->permissions('items_category_add') && mp_feature_enabled('auto_parts')): ?><a href="<?= base_url('import/categories'); ?>" class="mp-nav-item import_categories-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Import Categories</a><?php endif; ?>
        <?php if($CI->permissions('brand_add') && mp_feature_enabled('auto_parts')): ?><a href="<?= base_url('import/brands'); ?>" class="mp-nav-item import_brands-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Import Brands</a><?php endif; ?>
        <?php if($CI->permissions('attributes_add') && mp_feature_enabled('auto_parts')): ?><a href="<?= base_url('import/attributes'); ?>" class="mp-nav-item import_attributes-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Import Attributes</a><?php endif; ?>
        <?php if($CI->permissions('import_services') && service_module()): ?><a href="<?= base_url('import/services'); ?>" class="mp-nav-item import_services-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Import Services</a><?php endif; ?>
        <?php if(mp_feature_enabled('price_catalogue') && (is_admin() || is_store_admin())): ?><a href="<?= base_url('operations/price_catalogue'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Price Catalogue</a><?php endif; ?>
      </div>
    </div></div>
    <?php endif; ?>

    <!-- Vehicles -->
    <?php if(mp_feature_enabled('automobile_workflow')): ?>
    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#2563EB;"><i class="fa fa-car"></i></span> Vehicles <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <a href="<?= base_url('automobile/add'); ?>" class="mp-nav-item automobile-add-active-li"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> Add Vehicle</a>
        <a href="<?= base_url('automobile'); ?>" class="mp-nav-item automobile-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Vehicle List</a>
        <a href="<?= base_url('automobile/dashboard'); ?>" class="mp-nav-item automobile-dashboard-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Vehicle Dashboard</a>
        <?php if (is_admin() || is_store_admin()): ?>
        <a href="<?= base_url('vehicle_data'); ?>" class="mp-nav-item vehicle-data-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Vehicle Data</a>
        <?php endif; ?>
      </div>
    </div></div>
    <?php endif; ?>

    <!-- Promotions -->
    <?php if((!$is_creator) && $CI->permissions('promotions_manage')): ?>
    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#E11D48;"><?= $mp_icons['promo']; ?></span> Promotions <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <a href="<?= base_url('promotions'); ?>" class="mp-nav-item promotions_list-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Promotion List</a>
        <a href="<?= base_url('promotions/add'); ?>" class="mp-nav-item promotion_form-active-li"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> Add Promotion</a>
      </div>
    </div></div>
    <?php endif; ?>

    <!-- Purchases -->
    <?php if((!$is_creator) && ($CI->permissions('purchase_add') || $CI->permissions('purchase_view') || $CI->permissions('purchase_return_view') || $CI->permissions('suppliers_view') || $CI->permissions('suppliers_add') || $CI->permissions('import_suppliers'))): ?>
    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#059669;"><?= $mp_icons['purchase']; ?></span> <?= $is_car ? 'Vehicle Purchases &amp; Sellers' : 'Purchases &amp; Suppliers'; ?> <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <?php if($CI->permissions('purchase_add')): ?><a href="<?= base_url('purchase/add'); ?>" class="mp-nav-item purchase-active-li"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> New <?= $is_car ? 'Vehicle Purchase' : 'Purchase'; ?></a><?php endif; ?>
        <?php if($CI->permissions('purchase_view')): ?><a href="<?= base_url('purchase'); ?>" class="mp-nav-item purchase-list-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> <?= $is_car ? 'Vehicle Purchase' : 'Purchase'; ?> History</a><?php endif; ?>
        <?php if($CI->permissions('purchase_return_view')): ?><a href="<?= base_url('purchase_return'); ?>" class="mp-nav-item purchase-returns-active-li purchase-returns-list-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> <?= $is_car ? 'Vehicle Purchase' : 'Purchase'; ?> Returns</a><?php endif; ?>
        <?php // Suppliers live here, beside what they supply. They were under
              // Customers, filed by "is a company" rather than by purpose. ?>
        <?php if($CI->permissions('suppliers_add')): ?><a href="<?= base_url('suppliers/add'); ?>" class="mp-nav-item suppliers_add-active-li"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> New <?= $is_car ? 'Seller' : 'Supplier'; ?></a><?php endif; ?>
        <?php if($CI->permissions('suppliers_view')): ?><a href="<?= base_url('suppliers'); ?>" class="mp-nav-item suppliers_list-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> <?= $is_car ? 'Seller' : 'Supplier'; ?> List</a><?php endif; ?>
        <?php if($CI->permissions('import_suppliers')): ?><a href="<?= base_url('import/suppliers'); ?>" class="mp-nav-item import_suppliers-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Import <?= $is_car ? 'Sellers' : 'Suppliers'; ?></a><?php endif; ?>
      </div>
    </div></div>
    <?php endif; ?>

    <!-- Inventory -->
    <?php if((!$is_creator) && ($CI->permissions('stock_adjustment_view') || $CI->permissions('stock_transfer_view'))): ?>
    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#F59E0B;"><?= $mp_icons['inventory']; ?></span> Inventory <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <a href="<?= base_url('inventory'); ?>" class="mp-nav-item inventory-active-li"><span class="mp-nav-icon"><?= $mp_icons['dashboard']; ?></span> Overview</a>
        <?php if($CI->permissions('stock_adjustment_add')): ?><a href="<?= base_url('stock_adjustment/add'); ?>" class="mp-nav-item stock_adjustment_form-active-li"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> New Adjustment</a><?php endif; ?>
        <?php if($CI->permissions('stock_adjustment_view')): ?><a href="<?= base_url('stock_adjustment'); ?>" class="mp-nav-item stock_adjustment_list-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Stock Adjustments</a><?php endif; ?>
        <?php if($CI->permissions('stock_transfer_add') && warehouse_module()): ?><a href="<?= base_url('stock_transfer/add'); ?>" class="mp-nav-item stock_transfer_form-active-li"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> New Transfer</a><?php endif; ?>
        <?php if($CI->permissions('stock_transfer_view') && warehouse_module()): ?><a href="<?= base_url('stock_transfer/view'); ?>" class="mp-nav-item stock_transfer_list-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Stock Transfers</a><?php endif; ?>
      </div>
    </div></div>
    <?php endif; ?>

    <!-- Customers -->
    <?php if((!$is_creator) && ($CI->permissions('customers_add') || $CI->permissions('customers_view') || $CI->permissions('suppliers_add') || $CI->permissions('suppliers_view') || $CI->permissions('import_customers') || $CI->permissions('import_suppliers') || $CI->permissions('cust_adv_payments_add') || $CI->permissions('cust_adv_payments_view') || (mp_feature_enabled('leads') && $CI->permissions('leads_view')))): ?>
    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#7C3AED;"><?= $mp_icons['customers']; ?></span> <?= mp_label('customer'); ?>s <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <?php if($CI->permissions('customers_add')): ?><a href="<?= base_url('customers/add'); ?>" class="mp-nav-item customers_add-active-li"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> New <?= mp_label('customer'); ?></a><?php endif; ?>
        <?php if($CI->permissions('customers_view')): ?><a href="<?= base_url('customers'); ?>" class="mp-nav-item customers_list-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> <?= mp_label('customer'); ?> List</a><?php endif; ?>
        <?php if(mp_feature_enabled('leads') && ($CI->permissions('leads_view') || is_store_admin() || $this->session->userdata('role_id') == 1)): ?><a href="<?= base_url('leads'); ?>" class="mp-nav-item leads-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Leads</a><?php endif; ?>
        <?php // Suppliers moved to the Purchases group. A supplier is who you
              // BUY from, so it belongs beside purchasing — under Customers it
              // was filed by "is a person/company" rather than by what the
              // record is for, which is why it read oddly next to client
              // advances. See the Purchases block for the links themselves. ?>
        <?php if($CI->permissions('import_customers')): ?><a href="<?= base_url('import/customers'); ?>" class="mp-nav-item import_customers-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Import Customers</a><?php endif; ?>
        <?php if($CI->permissions('cust_adv_payments_add')): ?><a href="<?= base_url('customers_advance/add'); ?>" class="mp-nav-item customers_advance_add-active-li"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> New Advance</a><?php endif; ?>
        <?php if($CI->permissions('cust_adv_payments_view')): ?><a href="<?= base_url('customers_advance'); ?>" class="mp-nav-item customers_advance_list-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Advance List</a><?php endif; ?>
      </div>
    </div></div>
    <?php endif; ?>

    <!-- Finance -->
    <?php // The whole permission list is wrapped in ONE paren group, and it must
          // stay that way. `&&` binds tighter than `||`, so without the group
          // this reads as (!$is_creator && accounts_add) || expense_view || ...
          // — every permission after the first escapes the guard, and the group
          // shows for anyone holding it. ?>
    <?php if((!$is_creator) && ((($CI->permissions('accounts_add') || $CI->permissions('accounts_view') || $CI->permissions('journal_add') || $CI->permissions('journal_view') || $CI->permissions('money_transfer_view') || $CI->permissions('money_deposit_view') || $CI->permissions('cash_transactions')) && accounts_module()) || $CI->permissions('expense_view') || $CI->permissions('expense_category_view') || $CI->permissions('tills_view'))): ?>
    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#E11D48;"><?= $mp_icons['finance']; ?></span> Finance <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <?php if(($CI->permissions('accounts_add') || $CI->permissions('accounts_view') || $CI->permissions('journal_view')) && accounts_module()): ?>
          <?php if($CI->permissions('accounts_add')): ?><a href="<?= base_url('accounts/add'); ?>" class="mp-nav-item accounts-active-li"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> New Account</a><?php endif; ?>
          <?php if($CI->permissions('accounts_view')): ?><a href="<?= base_url('accounts'); ?>" class="mp-nav-item accounts_list-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Account List</a><?php endif; ?>
          <?php if($CI->permissions('money_transfer_view')): ?><a href="<?= base_url('money_transfer'); ?>" class="mp-nav-item money_transfer_list-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Money Transfers</a><?php endif; ?>
          <?php if($CI->permissions('money_deposit_view')): ?><a href="<?= base_url('money_deposit'); ?>" class="mp-nav-item money_deposit_list-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Deposits</a><?php endif; ?>
          <?php if($CI->permissions('cash_transactions')): ?><a href="<?= base_url('accounts/cash_transactions'); ?>" class="mp-nav-item cash_transactions-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Cash Transactions</a><?php endif; ?>
        <?php endif; ?>
        <?php if($CI->permissions('tills_view')): ?><a href="<?= base_url('tills'); ?>" class="mp-nav-item tills-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Tills</a><?php endif; ?>
        <?php if($CI->permissions('expense_view')): ?><a href="<?= base_url('expense'); ?>" class="mp-nav-item expense-list-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Expense List</a><?php endif; ?>
        <?php if($CI->permissions('expense_category_view')): ?><a href="<?= base_url('expense/category'); ?>" class="mp-nav-item expense-category-list-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Expense Categories</a><?php endif; ?>
      </div>
    </div></div>
    <?php endif; ?>

    <!-- Marketing -->
    <?php if((!$is_creator) && (($CI->permissions('discountCouponView') || $CI->permissions('customerCouponView')) || ($CI->permissions('loyalty_view') && mp_feature_enabled('loyalty')) || ($CI->permissions('gift_cards_view') && mp_feature_enabled('gift_cards')) || ($CI->permissions('store_credit_view') && mp_feature_enabled('store_credit')))): ?>
    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#C026D3;"><?= $mp_icons['marketing']; ?></span> Marketing <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <?php /* Sub-grouped: 15 items in one list is a wall. Three short lists
                 — who you talk to, what you run, what you give back — read in
                 one glance. Same content, same order within each list. */ ?>
        <div class="mp-nav-subhead">Leads &amp; Segments</div>
        <a href="<?= base_url('marketing'); ?>" class="mp-nav-item marketing-overview-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Marketing Overview</a>
        <?php
          // Client segmentation. db_customer_segments and Marketing::segments()
          // have existed for a while, but nothing linked them — the screen was
          // reachable only by typing the URL, exactly like the printing module
          // was. A saved segment is how a print shop answers "who are my repeat
          // trade clients", so it belongs on the rail.
        ?>
        <?php if($CI->permissions('customers_view')): ?>
        <a href="<?= base_url('marketing/segments'); ?>" class="mp-nav-item marketing-segments-active-li"><span class="mp-nav-icon"><?= $mp_icons['customers']; ?></span> Client Segments</a>
        <?php endif; ?>
        <?php if(($CI->permissions('discountCouponView') || $CI->permissions('customerCouponView')) && !is_admin()): ?>
          <div class="mp-nav-subhead">Campaigns &amp; Promotions</div>
          <?php if($CI->permissions('customerCouponAdd')): ?><a href="<?= base_url('customer_coupon/generate'); ?>" class="mp-nav-item createCoupon-active-li"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> Create Customer Coupon</a><?php endif; ?>
          <?php if($CI->permissions('customerCouponView')): ?><a href="<?= base_url('customer_coupon'); ?>" class="mp-nav-item customerCouponsList-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Customer Coupons</a><?php endif; ?>
          <?php if($CI->permissions('discountCouponAdd')): ?><a href="<?= base_url('discount_coupon/add'); ?>" class="mp-nav-item createDiscountCoupon-active-li"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> Create Discount Coupon</a><?php endif; ?>
          <?php if($CI->permissions('discountCouponView')): ?><a href="<?= base_url('discount_coupon/view'); ?>" class="mp-nav-item discountCoupon-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Discount Coupons</a><?php endif; ?>
        <?php endif; ?>
        <?php if($CI->permissions('loyalty_view') && mp_feature_enabled('loyalty')): ?>
          <div class="mp-nav-subhead">Loyalty &amp; Rewards</div>
          <a href="<?= base_url('loyalty'); ?>" class="mp-nav-item loyalty-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Loyalty Dashboard</a>
          <a href="<?= base_url('loyalty/settings'); ?>" class="mp-nav-item loyalty-settings-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Loyalty Settings</a>
          <a href="<?= base_url('loyalty/tiers'); ?>" class="mp-nav-item loyalty-tiers-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Customer Tiers</a>
          <a href="<?= base_url('loyalty/points_history'); ?>" class="mp-nav-item loyalty-points-history-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Points History</a>
          <a href="<?= base_url('loyalty/bonus_rules'); ?>" class="mp-nav-item loyalty-bonus-rules-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Bonus Rules</a>
          <a href="<?= base_url('loyalty/product_points'); ?>" class="mp-nav-item loyalty-product-points-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Product Points</a>
          <a href="<?= base_url('loyalty/referral_program'); ?>" class="mp-nav-item loyalty-referral-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Referral Program</a>
        <?php endif; ?>
        <?php if($CI->permissions('gift_cards_view') && mp_feature_enabled('gift_cards')): ?><a href="<?= base_url('gift_cards'); ?>" class="mp-nav-item gift-cards-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Gift Cards</a><?php endif; ?>
        <?php if($CI->permissions('store_credit_view') && mp_feature_enabled('store_credit')): ?><a href="<?= base_url('store_credit'); ?>" class="mp-nav-item store-credit-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Store Credit</a><?php endif; ?>
      </div>
    </div></div>
    <?php endif; ?>

    <!-- Reports -->
    <?php // NOTE: the permission list MUST stay wrapped in its own paren group.
          // `&&` binds tighter than `||`, so without it this reads as
          // (!$is_creator && sales_report) || profit_report || ... — any other
          // permission then wins and the group shows regardless of the guard. ?>
    <?php // A print shop gets these screens inside Insights instead, so the two
          // groups do not duplicate one another on that rail. ?>
    <?php if((!$is_creator) && !$is_printing && ($CI->permissions('sales_report') || $CI->permissions('profit_report') || $CI->permissions('stock_report') || $CI->permissions('expense_report') || $CI->permissions('purchase_report') || $CI->permissions('item_sales_report') || $CI->permissions('expired_items_report') || $CI->permissions('dashboard_view'))): ?>
    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#F97316;"><?= $mp_icons['reports']; ?></span> Reports <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <?php if($CI->permissions('dashboard_view')): ?><a href="<?= base_url('dashboard/daily_summary'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Daily Business Summary</a><?php endif; ?>
        <?php if($CI->permissions('profit_report')): ?><a href="<?= base_url('reports/profit_loss'); ?>" class="mp-nav-item report-profit-loss-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Profit & Loss</a><?php endif; ?>
        <?php if($CI->permissions('z_report') && mp_feature_enabled('cashier_shifts')): ?><a href="<?= base_url('cashier_shifts'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Z-Report</a><?php endif; ?>
        <?php if($CI->permissions('cashier_shifts_manage') && mp_feature_enabled('cashier_shifts')): ?><a href="<?= base_url('cashier_shifts/manage'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Open/Close Shift</a><?php endif; ?>
        <?php if($CI->permissions('receivables_aging_report')): ?><a href="<?= base_url('reports/receivables_aging'); ?>" class="mp-nav-item report-receivables-aging-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Receivables Aging</a><?php endif; ?>
        <?php if($CI->permissions('inventory_aging_report')): ?><a href="<?= base_url('reports/inventory_aging'); ?>" class="mp-nav-item report-inventory-aging-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Inventory Aging</a><?php endif; ?>
        <?php if($CI->permissions('cash_flow_report')): ?><a href="<?= base_url('reports/cash_flow'); ?>" class="mp-nav-item report-cash-flow-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Cash Flow</a><?php endif; ?>
        <?php if($CI->permissions('variant_attribute_report')): ?><a href="<?= base_url('reports/variant_attribute'); ?>" class="mp-nav-item report-variant-attribute-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Variant Attribute</a><?php endif; ?>
        <?php if($CI->permissions('sell_through_report')): ?><a href="<?= base_url('reports/sell_through'); ?>" class="mp-nav-item report-sell-through-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Sell Through</a><?php endif; ?>
        <?php if($CI->permissions('reorder_suggestion_report')): ?><a href="<?= base_url('reports/reorder_suggestion'); ?>" class="mp-nav-item report-reorder-suggestion-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Reorder Suggestion</a><?php endif; ?>
        <?php if($CI->permissions('sales_report')): ?><a href="<?= base_url('reports/sales_and_payments'); ?>" class="mp-nav-item report-sales-and-payments-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Sales & Payments</a><?php endif; ?>
        <?php if($CI->permissions('customer_orders_report')): ?><a href="<?= base_url('reports/customer_orders'); ?>" class="mp-nav-item report-customer-orders-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Customer Orders</a><?php endif; ?>
        <?php if($CI->permissions('sales_report')): ?><a href="<?= base_url('reports/sales'); ?>" class="mp-nav-item report-sales-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Sales Report</a><?php endif; ?>
        <?php if($CI->permissions('sales_return_report')): ?><a href="<?= base_url('reports/sales_return'); ?>" class="mp-nav-item report-sales-return-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Sales Return Report</a><?php endif; ?>
        <?php if($CI->permissions('purchase_report')): ?><a href="<?= base_url('reports/purchase'); ?>" class="mp-nav-item report-purchase-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Purchase Report</a><?php endif; ?>
        <?php if($CI->permissions('purchase_return_report')): ?><a href="<?= base_url('reports/purchase_return'); ?>" class="mp-nav-item report-purchase-return-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Purchase Return Report</a><?php endif; ?>
        <?php if($CI->permissions('expense_report')): ?><a href="<?= base_url('reports/expense'); ?>" class="mp-nav-item report-expense-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Expense Report</a><?php endif; ?>
        <?php if($CI->permissions('stock_report')): ?><a href="<?= base_url('reports/stock'); ?>" class="mp-nav-item report-stock-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Stock Report</a><?php endif; ?>
        <?php if($CI->permissions('item_sales_report')): ?><a href="<?= base_url('reports/item_sales'); ?>" class="mp-nav-item report-sales-item-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Item Sales Report</a><?php endif; ?>
        <?php if($CI->permissions('purchase_payments_report')): ?><a href="<?= base_url('reports/purchase_payments'); ?>" class="mp-nav-item report-purchase-payments-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Purchase Payments</a><?php endif; ?>
        <?php if($CI->permissions('sales_payments_report')): ?><a href="<?= base_url('reports/sales_payments'); ?>" class="mp-nav-item report-sales-payments-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Sales Payments</a><?php endif; ?>
        <?php if($CI->permissions('stock_transfer_report')): ?><a href="<?= base_url('reports/stock_transfer'); ?>" class="mp-nav-item report-stock-transfer-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Stock Transfer Report</a><?php endif; ?>
        <?php if($CI->permissions('sales_summary_report')): ?><a href="<?= base_url('reports/sales_summary'); ?>" class="mp-nav-item report-sales-summary-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Sales Summary</a><?php endif; ?>
        <?php if($CI->permissions('expired_items_report')): ?><a href="<?= base_url('expired_items_report'); ?>" class="mp-nav-item report-expired-items-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Expired Items</a><?php endif; ?>
        <?php if($CI->permissions('quotation_report')): ?><a href="<?= base_url('reports/scientific/quotations'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Outstanding Quotations</a><?php endif; ?>
        <?php if($CI->permissions('procurement_report')): ?><a href="<?= base_url('reports/scientific/procurement'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Awaiting Procurement</a><?php endif; ?>
        <?php if($CI->permissions('equipment_report') && mp_feature_enabled('equipment_register')): ?><a href="<?= base_url('reports/scientific/equipment'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Equipment Register</a><?php endif; ?>
        <?php if($CI->permissions('warranty_report') && mp_feature_enabled('equipment_register')): ?><a href="<?= base_url('reports/scientific/warranty'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Warranty &amp; Expiries</a><?php endif; ?>
        <?php if($CI->permissions('service_jobs_report') && mp_feature_enabled('service_jobs')): ?><a href="<?= base_url('reports/scientific/service_jobs'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Service Jobs</a><?php endif; ?>
        <?php if($CI->permissions('calibration_report') && mp_feature_enabled('equipment_register')): ?><a href="<?= base_url('reports/scientific/calibration'); ?>" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Calibration Due</a><?php endif; ?>
        <?php if($CI->permissions('sales_return_payments')): ?><a href="<?= base_url('reports/sales_return_payments'); ?>" class="mp-nav-item report-sales-return-payments-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Sales Return Payments</a><?php endif; ?>
        <?php if($CI->permissions('supplier_items_report')): ?><a href="<?= base_url('reports/supplier_items'); ?>" class="mp-nav-item report-supplier-items-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Supplier Items</a><?php endif; ?>
        <?php if($CI->permissions('seller_points_report')): ?><a href="<?= base_url('reports/seller_points'); ?>" class="mp-nav-item report-seller-points-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Seller Points</a><?php endif; ?>
        <?php if(($CI->permissions('approval_logs_view') || is_store_admin() || $this->session->userdata('role_id') == 1) && mp_feature_enabled('manager_approvals')): ?><a href="<?= base_url('approvals/logs'); ?>" class="mp-nav-item approvals-logs-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Approval Logs</a><?php endif; ?>
      </div>
    </div></div>
    <?php endif; ?>

    <!-- Online Store / Leads Hub -->
    <?php
      // A print shop's storefront takes ENQUIRIES, not shop orders — every job
      // is quoted before it is printed, so the online side is a request desk.
      // The screens are unchanged; the name now says what arrives there. Every
      // other industry keeps "Online Store".
      $online_label = $is_printing ? 'Online Print Requests' : 'Online Store';
    ?>
    <?php if((!$is_creator) && ($CI->permissions('online_store_view') || $CI->permissions('online_store_orders') || is_store_admin() || $this->session->userdata('role_id') == 1) && mp_feature_enabled('online_store')): ?>
    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#059669;"><?= $mp_icons['online']; ?></span> <?= $online_label ?> <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <?php /* Sub-grouped so 16 items read as four short lists rather than one
                 wall — the same fix applied to Marketing and Settings. */ ?>
        <?php if($CI->permissions('online_store_view') || is_store_admin() || $this->session->userdata('role_id') == 1): ?>
          <div class="mp-nav-subhead">Overview &amp; Analytics</div>
          <a href="<?= base_url('online_store'); ?>" class="mp-nav-item online_store-active-li"><span class="mp-nav-icon"><?= $mp_icons['dashboard']; ?></span> Store Dashboard</a>
          <a href="<?= base_url('online_store/analytics'); ?>" class="mp-nav-item online_store-analytics-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Analytics</a>
          <a href="<?= base_url('online_store/subscribers'); ?>" class="mp-nav-item online_store-subscribers-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Subscribers</a>
        <?php endif; ?>
        <?php if($CI->permissions('online_store_orders') || $CI->permissions('online_store_view') || is_store_admin() || $this->session->userdata('role_id') == 1): ?>
          <div class="mp-nav-subhead"><?= $is_printing ? 'Enquiries &amp; Requests' : 'Enquiries &amp; Orders'; ?></div>
          <a href="<?= base_url('online_store/orders'); ?>" class="mp-nav-item online_store-orders-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Orders</a>
        <?php endif; ?>
        <?php if($CI->permissions('online_store_view') || is_store_admin() || $this->session->userdata('role_id') == 1): ?>
          <div class="mp-nav-subhead"><?= $is_printing ? 'Services &amp; Materials' : 'Catalogue'; ?></div>
          <a href="<?= base_url('online_store/products_online'); ?>" class="mp-nav-item online_store-products-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Online Products</a>
          <a href="<?= base_url('online_store/services'); ?>" class="mp-nav-item online_store-services-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Services</a>
          <?php if(mp_feature_enabled('qr_ordering')): ?><a href="<?= base_url('online_store/qr_codes'); ?>" class="mp-nav-item online_store-qr-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> QR Codes</a><?php endif; ?>
        <?php endif; ?>
        <?php if($CI->permissions('online_store_edit') || is_store_admin() || $this->session->userdata('role_id') == 1): ?>
          <div class="mp-nav-subhead">Content &amp; Appearance</div>
          <a href="<?= base_url('online_store/homepage_builder'); ?>" class="mp-nav-item online_store-homepage_builder-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Homepage Builder</a>
          <a href="<?= base_url('online_store/banners'); ?>" class="mp-nav-item online_store-banners-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Banners</a>
          <a href="<?= base_url('online_store/brands'); ?>" class="mp-nav-item online_store-brands-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Brands</a>
          <a href="<?= base_url('online_store/testimonials'); ?>" class="mp-nav-item online_store-testimonials-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Testimonials</a>
          <a href="<?= base_url('online_store/instagram'); ?>" class="mp-nav-item online_store-instagram-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Instagram</a>
          <a href="<?= base_url('online_store/faqs'); ?>" class="mp-nav-item online_store-faqs-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> FAQs</a>
          <div class="mp-nav-subhead">Configuration</div>
          <a href="<?= base_url('online_store/appearance'); ?>" class="mp-nav-item online_store-appearance-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Appearance</a>
          <a href="<?= base_url('online_store/domains'); ?>" class="mp-nav-item online_store-domains-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Domains</a>
          <a href="<?= base_url('online_store/settings'); ?>" class="mp-nav-item online_store-settings-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Store Settings</a>
        <?php endif; ?>
      </div>
    </div></div>
    <?php endif; ?>

    <!-- Operations -->
    <?php
      $ops_flags = ['custom_orders','memberships','treatment_notes','medical_notes','kitchen_workflow','laundry_workflow','production_workflow','recipe_tracking','public_catalogue','delivery_scheduling','serial_number_tracking','imei_tracking','warranty_tracking','expiry_tracking','meat_butchery_workflow','frozen_food_cold_chain','automobile_workflow','perfumery_workflow','nylon_workflow','equipment_register','service_jobs'];
      if($is_creator) { $ops_flags = array_diff($ops_flags, ['memberships']); } /* creator gets memberships in its own menu */
      $has_ops = false; foreach ($ops_flags as $f) { if (mp_feature_enabled($f)) { $has_ops = true; break; } }
      $has_staff = (mp_feature_enabled('staff_assignment') || mp_feature_enabled('staff_commission')) && (is_admin() || is_store_admin());
      $has_tables = mp_feature_enabled('table_management') && (is_admin() || is_store_admin());
    ?>
    <?php
      // Operations is a PRODUCTION rail, and a print shop now has its own.
      //
      // Its flags are production_workflow, recipe_tracking, delivery_scheduling,
      // custom_orders, equipment_register, service_jobs — every one of which
      // Print Shop and Machine Floor already cover, in the language of print
      // rather than generic manufacturing. Leaving it visible gave a print shop
      // two menus for one job and the wrong vocabulary in one of them.
      //
      // Suppressed for printing only. Every other industry still gets it.
    ?>
    <?php if((!$is_creator) && !$is_printing && ($has_ops || $has_staff || $has_tables)): ?>
    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#F97316;"><?= $mp_icons['ops']; ?></span> Operations <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <?php if(mp_feature_enabled('custom_orders')): ?><a href="<?= base_url('operations/custom_orders'); ?>" class="mp-nav-item operations-custom_orders-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Custom Orders</a><?php endif; ?>
        <?php if(mp_feature_enabled('production_workflow')): ?><a href="<?= base_url('operations/production_schedule'); ?>" class="mp-nav-item operations-production_schedule-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Production Schedule</a><?php endif; ?>
        <?php if(mp_feature_enabled('recipe_tracking')): ?><a href="<?= base_url('operations/recipes'); ?>" class="mp-nav-item operations-recipes-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Recipe Book</a><?php endif; ?>
        <?php if(mp_feature_enabled('memberships')): ?><a href="<?= base_url('operations/memberships'); ?>" class="mp-nav-item operations-memberships-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Memberships</a><?php endif; ?>
        <?php if(mp_feature_enabled('treatment_notes')): ?><a href="<?= base_url('operations/treatment_notes'); ?>" class="mp-nav-item operations-treatment_notes-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Treatment Notes</a><?php endif; ?>
        <?php if(mp_feature_enabled('medical_notes')): ?><a href="<?= base_url('operations/medical_notes'); ?>" class="mp-nav-item operations-medical_notes-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Medical Notes</a><?php endif; ?>
        <?php if(mp_feature_enabled('kitchen_workflow')): ?><a href="<?= base_url('operations/kitchen'); ?>" class="mp-nav-item operations-kitchen-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Kitchen Display</a><?php endif; ?>
        <?php if(mp_feature_enabled('laundry_workflow')): ?><a href="<?= base_url('operations/laundry'); ?>" class="mp-nav-item operations-laundry-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Laundry Workflow</a><?php endif; ?>
        <?php if(mp_feature_enabled('delivery_scheduling')): ?><a href="<?= base_url('operations/delivery_scheduling'); ?>" class="mp-nav-item operations-delivery_scheduling-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Delivery Scheduling</a><?php endif; ?>
        <?php if(mp_feature_enabled('public_catalogue')): ?><a href="<?= base_url('operations/public_catalogue_settings'); ?>" class="mp-nav-item operations-public_catalogue_settings-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Public Catalogue</a><?php endif; ?>
        <?php if(mp_feature_enabled('expiry_tracking')): ?><a href="<?= base_url('operations/stock_rotation'); ?>" class="mp-nav-item operations-stock_rotation-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Stock Rotation</a><?php endif; ?>
        <?php if(mp_feature_enabled('meat_butchery_workflow') || mp_feature_enabled('frozen_food_cold_chain')): ?><a href="<?= base_url('butchery'); ?>" class="mp-nav-item butchery-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Butchery &amp; Frozen</a><?php endif; ?>
        <?php if(mp_feature_enabled('perfumery_workflow')): ?><a href="<?= base_url('perfume'); ?>" class="mp-nav-item perfume-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Perfume Lab</a><?php endif; ?>
        <?php if(mp_feature_enabled('nylon_workflow')): ?><a href="<?= base_url('nylon'); ?>" class="mp-nav-item nylon-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> <?= htmlspecialchars(mp_label('production','Nylon Production')); ?></a><?php endif; ?>
        <?php if(mp_feature_enabled('serial_number_tracking') || mp_feature_enabled('imei_tracking') || mp_feature_enabled('warranty_tracking')): ?><a href="<?= base_url('operations/warranty_lookup'); ?>" class="mp-nav-item operations-warranty_lookup-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Warranty Lookup</a><?php endif; ?>
        <?php if(mp_feature_enabled('equipment_register')): ?><a href="<?= base_url('operations/equipment_register'); ?>" class="mp-nav-item operations-equipment_register-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Equipment Register</a><?php endif; ?>
        <?php if(mp_feature_enabled('service_jobs')): ?><a href="<?= base_url('operations/service_jobs'); ?>" class="mp-nav-item operations-service_jobs-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Service Jobs</a><?php endif; ?>
        <?php if($has_staff && mp_feature_enabled('staff_assignment')): ?><a href="<?= base_url('operations/staff_assignment'); ?>" class="mp-nav-item operations-staff_assignment-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Staff Assignment</a><?php endif; ?>
        <?php if($has_staff && mp_feature_enabled('staff_commission')): ?><a href="<?= base_url('operations/staff_commission'); ?>" class="mp-nav-item operations-staff_commission-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Staff Commission</a><?php endif; ?>
        <?php if($has_tables): ?><a href="<?= base_url('operations/table_management'); ?>" class="mp-nav-item operations-table_management-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Table Management</a><?php endif; ?>
      </div>
    </div></div>
    <?php endif; ?>

    <!-- Creator tools for non-creator businesses that switched the flags on -->
    <?php if((!$is_creator) && (mp_feature_enabled('digital_products') || mp_feature_enabled('courses'))): ?>
    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#7C3AED;"><?= $mp_icons['online']; ?></span> Creator <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <a href="<?= base_url('creator'); ?>" class="mp-nav-item creator-active-li"><span class="mp-nav-icon"><?= $mp_icons['dashboard']; ?></span> Creator Dashboard</a>
        <a href="<?= base_url('creator/products'); ?>" class="mp-nav-item creator-products-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Digital Products</a>
        <?php if(mp_feature_enabled('courses')): ?><a href="<?= base_url('courses'); ?>" class="mp-nav-item courses-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Courses</a><?php endif; ?>
        <?php if(mp_feature_enabled('memberships')): ?><a href="<?= base_url('memberships'); ?>" class="mp-nav-item memberships-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Membership Plans</a><?php endif; ?>
        <?php if(mp_feature_enabled('courses')): ?><a href="<?= base_url('creator/students'); ?>" class="mp-nav-item creator-students-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Students</a><?php endif; ?>
        <?php if(mp_feature_enabled('memberships')): ?><a href="<?= base_url('creator/members'); ?>" class="mp-nav-item creator-members-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Members</a><?php endif; ?>
      </div>
    </div></div>
    <?php endif; ?>

    <!-- Help -->
    <?php if(!is_cashier()): ?>
    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#0057FF;"><i class="fa fa-question-circle"></i></span> Help <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <a href="<?= base_url('dashboard/help'); ?>" class="mp-nav-item help-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Help Center</a>
        <a href="<?= base_url('docs/product-design/customer-guide/index.html'); ?>" target="_blank" rel="noopener" class="mp-nav-item"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Customer Guide</a>
        <a href="<?= base_url('dashboard/support'); ?>" class="mp-nav-item support-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Support</a>
      </div>
    </div></div>
    <?php endif; ?>

    <!-- Administration -->
    <div class="mp-nav-section"><div class="mp-nav-group" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#78716C;"><?= $mp_icons['admin']; ?></span> Settings <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <a href="<?= base_url('admin'); ?>" class="mp-nav-item admin-dashboard-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Admin Dashboard</a>
        <?php if($CI->permissions('store_edit')): ?><a href="<?= base_url('store_profile/update/'.$this->session->userdata('store_id')); ?>" class="mp-nav-item store_profile-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Store Profile</a><?php endif; ?>
        <?php if($CI->permissions('business_setup')): ?><a href="<?= base_url('business_profile'); ?>" class="mp-nav-item business_profile-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Business Profile</a><?php endif; ?>
        <?php if(($CI->permissions('warehouse_view') || $CI->permissions('warehouse_add')) && warehouse_module()):
          try { $branch_label_nav = mp_label('branch','Branches'); } catch (Exception $e) { $branch_label_nav = 'Branches'; }
        ?>
          <?php if($CI->permissions('warehouse_add')): ?><a href="<?= base_url('warehouse/add'); ?>" class="mp-nav-item warehouse-add-active-li warehouse-active-li"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> New <?= $branch_label_nav; ?></a><?php endif; ?>
          <?php if($CI->permissions('warehouse_view')): ?><a href="<?= base_url('warehouse'); ?>" class="mp-nav-item warehouse-list-active-li warehouse-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> <?= $branch_label_nav; ?> List</a><?php endif; ?>
        <?php endif; ?>
        <?php if($CI->permissions('store_view') && store_module() && ($this->session->userdata('role_id') == 1 || is_store_admin())): ?>
          <a href="<?= base_url('store/add'); ?>" class="mp-nav-item store-add-active-li store-active-li"><span class="mp-nav-icon"><?= $mp_icons['plus']; ?></span> Add Store</a>
          <a href="<?= base_url('store/view'); ?>" class="mp-nav-item store-view-active-li store-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Store List</a>
        <?php endif; ?>
        <?php if($CI->permissions('users_view')): ?><a href="<?= base_url('users/view'); ?>" class="mp-nav-item users-view-active-li users-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Users List</a><?php endif; ?>
        <?php if($CI->permissions('roles_view')): ?><a href="<?= base_url('roles/view'); ?>" class="mp-nav-item roles-view-active-li roles-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Roles List</a><?php endif; ?>
        <?php if($CI->permissions('attendance_view') || $CI->permissions('attendance_edit') || is_store_admin() || $this->session->userdata('role_id') == 1): ?>
          <?php if($CI->permissions('attendance_edit') || is_store_admin() || $this->session->userdata('role_id') == 1): ?>
            <a href="<?= base_url('attendance/shifts'); ?>" class="mp-nav-item attendance-shifts-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Shifts</a>
            <a href="<?= base_url('attendance/assign_shifts'); ?>" class="mp-nav-item attendance-assign-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Assign Shifts</a>
          <?php endif; ?>
          <?php if($CI->permissions('attendance_view') || is_store_admin() || $this->session->userdata('role_id') == 1): ?>
            <a href="<?= base_url('attendance/daily'); ?>" class="mp-nav-item attendance-daily-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Daily Attendance</a>
            <a href="<?= base_url('attendance/report'); ?>" class="mp-nav-item attendance-report-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Attendance Report</a>
          <?php endif; ?>
        <?php endif; ?>
        <?php if($CI->permissions('send_sms')): ?><a href="<?= base_url('sms'); ?>" class="mp-nav-item sms-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Send SMS</a><?php endif; ?>
        <?php if($CI->permissions('sms_template_view')): ?><a href="<?= base_url('templates/sms'); ?>" class="mp-nav-item sms-templates-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> SMS Templates</a><?php endif; ?>
        <?php if($this->session->userdata('role_id') == 1 || is_store_admin()): ?>
          <a href="<?= base_url('site'); ?>" class="mp-nav-item site-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Site Settings</a>
          <a href="<?= base_url('migrate'); ?>" class="mp-nav-item migrate-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Data Migration</a>
          <a href="<?= base_url('subscription_license'); ?>" class="mp-nav-item subscription-license-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> License Management</a>
          <a href="<?= base_url('subscription_plans'); ?>" class="mp-nav-item subscription-plans-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Subscription Plans</a>
        <?php endif; ?>
        <?php if($CI->permissions('sms_api_view')): ?><a href="<?= base_url('sms/api'); ?>" class="mp-nav-item sms-api-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> SMS API</a><?php endif; ?>
        <?php if($CI->permissions('smtp_settings') && ($this->session->userdata('role_id') == 1 || is_store_admin())): ?><a href="<?= base_url('email_settings'); ?>" class="mp-nav-item email-settings-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Email Settings</a><?php endif; ?>
        <?php if($CI->permissions('debt_reminder_view') || is_store_admin() || $this->session->userdata('role_id') == 1): ?><a href="<?= base_url('debt_reminder'); ?>" class="mp-nav-item debt-reminder-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Debt Reminder</a><?php endif; ?>
        <?php if($CI->permissions('gateway_view') && ($this->session->userdata('role_id') == 1 || is_store_admin()) && store_module()): ?><a href="<?= base_url('gateways'); ?>" class="mp-nav-item gateways-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Payment Gateways</a><?php endif; ?>
        <?php if($CI->permissions('package_view') && ($this->session->userdata('role_id') == 1 || is_store_admin()) && store_module()): ?><a href="<?= base_url('package'); ?>" class="mp-nav-item package-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Packages</a><?php endif; ?>
        <?php if($CI->permissions('subscription') && store_module()): ?><a href="<?= base_url('subscribers/list/'.get_current_store_id()); ?>" class="mp-nav-item subscription-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Subscription</a><?php endif; ?>
        <?php if($CI->permissions('tax_view')): ?><a href="<?= base_url('tax'); ?>" class="mp-nav-item tax-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Tax List</a><?php endif; ?>
        <?php if($CI->permissions('units_view')): ?><a href="<?= base_url('units/'); ?>" class="mp-nav-item units-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Units List</a><?php endif; ?>
        <?php if($CI->permissions('payment_types_view')): ?><a href="<?= base_url('payment_types/'); ?>" class="mp-nav-item payment_types-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Payment Types</a><?php endif; ?>
        <?php if($CI->permissions('payment_modes_view')): ?><a href="<?= base_url('payment_modes/'); ?>" class="mp-nav-item payment_modes-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Payment Modes</a><?php endif; ?>
        <?php if($CI->permissions('paystack_settings')): ?><a href="<?= base_url('paystack/settings'); ?>" class="mp-nav-item paystack-settings-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Paystack Settings</a><?php endif; ?>
        <?php if($CI->permissions('monnify_settings')): ?><a href="<?= base_url('monnify/settings'); ?>" class="mp-nav-item monnify-settings-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Monnify Settings</a><?php endif; ?>
        <?php if($CI->permissions('expiry_settings') && mp_feature_enabled('expiry_tracking')): ?><a href="<?= base_url('expiry_settings'); ?>" class="mp-nav-item expiry-settings-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Expiry Settings</a><?php endif; ?>
        <?php if($this->session->userdata('role_id') == 1 || is_store_admin()): ?>
          <a href="<?= base_url('currency/view'); ?>" class="mp-nav-item currency-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Currency List</a>
          <a href="<?= base_url('users/dbbackup'); ?>" class="mp-nav-item dbbackup-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Database Backup</a>
          <a href="<?= base_url('system_updates'); ?>" class="mp-nav-item system-updates-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> System Update</a>
          <a href="<?= base_url('permission_audit'); ?>" class="mp-nav-item permission-audit-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Permission Audit</a>
          <a href="<?= base_url('country'); ?>" class="mp-nav-item country-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Countries</a>
          <a href="<?= base_url('state'); ?>" class="mp-nav-item state-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> States</a>
          <a href="<?= base_url('city'); ?>" class="mp-nav-item city-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Cities</a>
        <?php endif; ?>
        <?php if(($CI->permissions('approval_settings_edit') || is_store_admin() || $this->session->userdata('role_id') == 1) && mp_feature_enabled('manager_approvals')): ?><a href="<?= base_url('approvals/settings'); ?>" class="mp-nav-item approvals-settings-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Security & Approvals</a><?php endif; ?>
        <?php if($CI->permissions('audit_trail_view')): ?><a href="<?= base_url('audit_trail'); ?>" class="mp-nav-item audit-trail-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Audit Trail</a><?php endif; ?>
        <?php if($CI->permissions('nin_usage')): ?><a href="<?= base_url('ninverify/usage'); ?>" class="mp-nav-item ninverify-usage-active-li"><i class="fa fa-bar-chart mp-nav-icon"></i> NIN Usage</a><?php endif; ?>
        <?php if($CI->permissions('nin_logs')): ?><a href="<?= base_url('ninverify/log'); ?>" class="mp-nav-item ninverify-log-active-li"><i class="fa fa-id-card mp-nav-icon"></i> NIN Verification Log</a><?php endif; ?>
        <a href="<?= base_url('users/password_reset'); ?>" class="mp-nav-item change-password-active-li"><i class="fa fa-lock mp-nav-icon"></i> Change Password</a>
      </div>
    </div></div>

    <?php endif; /* end !$is_central business menus */ ?>

    <!-- Central — vendor tooling, only renders on the central install -->
    <?php if ($is_central): ?>
    <div class="mp-nav-section"><div class="mp-nav-group open" onclick="this.classList.toggle('open')">
      <div class="mp-nav-group-toggle"><span class="mp-nav-icon" style="color:#0057FF;"><i class="fa fa-globe"></i></span> Central <span class="mp-nav-chevron"><?= $mp_icons['chevron']; ?></span></div>
      <div class="mp-nav-submenu">
        <a href="<?= base_url('fleet'); ?>" class="mp-nav-item fleet-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Fleet Manager</a>
        <a href="<?= base_url('manifest'); ?>" class="mp-nav-item manifest-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Manifest Generator</a>
        <a href="<?= base_url('release'); ?>" class="mp-nav-item release-active-li"><span class="mp-nav-icon"><?= $mp_icons['list']; ?></span> Build Release</a>
        <a href="<?= base_url('users/password_reset'); ?>" class="mp-nav-item change-password-active-li"><span class="mp-nav-icon"><i class="fa fa-lock"></i></span> Change Password</a>
      </div>
    </div></div>
    <?php endif; ?>

    <div class="mp-nav-spacer"></div>
    <div class="mp-nav-store-card">
      <div class="store-name"><?= htmlspecialchars($this->session->userdata('store_name') ?? 'MartPoint'); ?></div>
      <div class="store-meta">MartPoint Retail v<?= isset($VERSION) ? $VERSION : app_version(); ?></div>
      <?php if($CI->db->table_exists('db_subscription_license')){ $CI->load->model('subscription_license_model','sub_lic'); $sub_status_nav = $CI->sub_lic->get_status(); if($sub_status_nav['status'] === 'ACTIVE'): ?>
      <div class="store-plan"><i class="fa fa-check"></i> <?= htmlspecialchars($sub_status_nav['plan'] ?? 'Pro Plan'); ?> · Active</div>
      <?php endif; } ?>
    </div>
  </nav>

  <!-- ===== MAIN ===== -->
  <main class="mp-main">
    <div style="clear:both"></div>
