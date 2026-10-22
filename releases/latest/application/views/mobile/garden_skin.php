<?php
/*
 * Garden Workspace palette for the shared mobile screens.
 *
 * The workspace shell and every garden screen wear the workspace green, but a
 * garden store also reaches shared screens (customers, purchases, reports, the
 * business tools index) that ship the retail blue. Re-tinting those screens in
 * one place keeps the whole phone experience on-brand without editing 130
 * views: every one of them declares --mp-primary and its pale-blue tints at
 * :root, and this partial is loaded last, so a later declaration wins.
 *
 * Only the primary accent and the pale-blue tint family change here. Status
 * colours (green/amber/red) and the deliberate secondary accents are left
 * alone, so nothing ends up half-recoloured. The garden shell is skipped
 * because it already carries its own palette.
 */
if (defined('MP_GARDEN_MOBILE_SKIN')) return;
$mp_skin_path = trim((string) get_instance()->uri->uri_string(), '/');
if ($mp_skin_path === '' || strpos($mp_skin_path, 'garden') === 0) return;
if (!function_exists('mp_get_store_profile')) get_instance()->load->helper('business_profile');
if (!function_exists('mp_get_store_profile') || (mp_get_store_profile()['industry_type'] ?? '') !== 'garden_centre_landscaping') return;
define('MP_GARDEN_MOBILE_SKIN', true);
?>
<style>
  /* Workspace green replaces the retail blue that drives buttons, links,
     active tabs, focus rings and spinners. */
  :root { --mp-primary: #235b46; --mp-primary-dark: #173f30; }
  .topbar { border-bottom-color: #e6ece7; }
  .mp-mobile-bottom-nav .nav-item.active, .mp-mobile-bottom-nav .nav-item.active .icon { color: #235b46; }
  .fab { box-shadow: 0 10px 22px rgba(35, 91, 70, .30); }
  .folder-card .icon { background: #e9f2e6; }
  /* Pale blue tint capsules: indigo-50, blue-50 and blue-100 each collapse to
     the matching green tint, with the dark green ink on top. */
  .action.open, .action.primary, .action.view, .avatar, .b-scheduled, .badge-converted, .badge-primary,
  .badge.active, .badge.proposed, .badge.status, .badge.tier, .badge.variants, .cq-no, .ic-indigo,
  .mp-option.active, .period-chip, .pm-option:has(input:checked), .shift-badge, .staff-item .avatar,
  .stage-h .n, .step .here, .step.active .dot, .summary, .support-banner, .support-card .icon,
  .total-box, .totals { background: #e4eee2 !important; color: #1d4632 !important; }
  .badge-info, .group-bubble.blue, .hint, .ic-blue, .icon-blue, .insight.info, .kpi-card.blue,
  .menu-item .icon.blue, .menu-item .icon.primary, .mp-select .option.selected, .mp-select-option.active,
  .mp-toast.info, .report-card, .report-card .icon.blue, .report-card .icon.primary, .stat-pill.month,
  .stop-row.picked, .t-partial, .tx-type, .cq-days a.on, .ss-days a.on,
  .b-in_progress, .b-open, .badge-in_progress, .badge-sent, .badge.new, .badge.ordered, .badge.ready,
  .badge.requested, .badge.stock, .badge.used, .flag.refill, .group-bubble.primary, .icon-primary,
  .info-banner, .pill.awaiting_verification, .pill.result_received, .quick-action.blue .icon { background: #e9f2e6 !important; color: #1d4632 !important; }
</style>
