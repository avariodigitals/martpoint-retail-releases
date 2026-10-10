<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$CI =& get_instance();
if(!function_exists('physio_can')) $this->load->helper('physio');
// The report registry gate lives in one place so the rail and the reports
// controller cannot drift apart and produce a link that denies access.
if(!function_exists('cr_can_see')) $this->load->helper('clinical_report');
$nav = function($label, $route, $icon, $allowed, $feature = null, $match = null) use ($CI){
	if(!$allowed || ($feature && !mp_feature_enabled($feature))) return '';
	$path = explode('?', $route)[0];
	$here = trim((string)$CI->uri->uri_string(), '/');

	/*
	 * Active state matches the FULL route, not just its first segment.
	 * Matching only the first segment lit every sibling that shared a prefix:
	 * on /inpatient/tasks the "Admissions" link (route "inpatient") and the
	 * "Porter tasks" link (route "inpatient/tasks") both activated, and because
	 * Nursing/Porter/Admissions share the same path it highlighted four menu
	 * items at once.
	 *
	 * $match lets a link claim a narrower destination that shares a path but
	 * differs by query string — the two ward task boards are one screen split
	 * by ?board=. Pass array('path'=>'inpatient/tasks','get'=>array('board'=>'porter')).
	 */
	$wantPath = $path;
	$wantGet  = array();
	if(is_array($match)){
		$wantPath = $match['path'] ?? $path;
		$wantGet  = $match['get'] ?? array();
	} elseif(is_string($match) && $match !== ''){
		$wantPath = $match;
	}

	$active = ($here === trim($wantPath, '/'));
	if($active && $wantGet){
		foreach($wantGet as $k => $v){
			if((string)$CI->input->get($k) !== (string)$v){ $active = false; break; }
		}
	}
	// A board-specific link must not also be claimed by the plain board link:
	// when one of the two ward boards is selected, only that one highlights.
	if($active && !$wantGet && strpos($wantPath, '/') !== false
	   && $CI->input->get('board') !== null && $path === 'inpatient/tasks'){
		$active = false;
	}

	return '<a class="physio-nav-link'.($active ? ' active' : '').'" href="'.base_url($route).'">'
		.'<i class="fa '.$icon.'" aria-hidden="true"></i><span>'.htmlspecialchars($label).'</span></a>';
};
/**
 * A collapsible nav group. Returns '' when none of its links render, so empty
 * groups never appear. $items is a list of [label, route, icon, allowed, feature].
 */
$group = function($label, $icon, array $items) use ($CI, $nav){
	$html = '';
	foreach($items as $it){
		$html .= $nav($it[0], $it[1], $it[2], $it[3], $it[4] ?? null);
	}
	if($html === '') return '';
	$open = (strpos($html, 'physio-nav-link active') !== false) ? ' open' : '';
	return '<div class="physio-nav-group'.$open.'">'
		.'<div class="physio-nav-group-toggle" onclick="this.parentNode.classList.toggle(\'open\')" role="button" tabindex="0">'
		.'<i class="fa '.$icon.'" aria-hidden="true"></i><span>'.htmlspecialchars($label).'</span>'
		.'<i class="fa fa-chevron-right physio-nav-chevron" aria-hidden="true"></i></div>'
		.'<nav class="physio-nav-submenu" aria-label="'.htmlspecialchars($label).'">'.$html.'</nav></div>';
};
$canPatients = physio_can('patients_view');
$canAppointments = physio_can('appointments_view');
$canQueue = physio_can('care_queue_view');
$canSessions = physio_can('sessions_view');
$canBilling = physio_can('patient_billing_view');
$canFunds = physio_can('patient_funds_view');
// Patient accounts opens the per-patient funds dashboard, which needs a chosen
// patient; the billing register is the usable landing. Only fall back to the
// funds dashboard when billing is not granted.
$canFunding = $canBilling || $canFunds;
$canBillingRegister = physio_can('patient_billing_view');
$canAdmission = physio_can('admissions_view');
$canNursing = physio_can_any(array('nursing_tasks_view','porter_tasks_view'));
$canAdmin = $CI->permissions('business_setup') || $CI->permissions('store_edit')
	|| $CI->permissions('users_view') || $CI->permissions('roles_view')
	|| $CI->permissions('smtp_settings') || $CI->permissions('audit_trail_view')
	|| $CI->permissions('accounts_view') || $CI->permissions('payment_modes_view')
	|| $CI->permissions('approval_settings_edit')
	|| physio_can_any(array('assessment_templates_manage','imports_view','portal_manage','opening_positions_view'));
$userName = $CI->session->userdata('display_name') ?: $CI->session->userdata('inv_username') ?: 'Staff';
/*
 * The STORE name, not the installation name. $SITE_TITLE is
 * db_sitesettings.site_name — the app name, which reads "MartPoint Retail" on
 * every tenant including a clinic. MY_Controller puts the real store name
 * (db_store.store_name) in the session for exactly this purpose.
 */
$storeName = $CI->session->userdata('store_name');
if(empty($storeName) && function_exists('get_store_name')){ $storeName = get_store_name(); }
if(empty($storeName)){ $storeName = $SITE_TITLE ?? 'MartPoint'; }
$physioLogo = '';
if($CI->db->table_exists('db_store_theme_settings')){
	$physioLogo = (string)$CI->db->where('store_id', get_current_store_id())->get('db_store_theme_settings')->row('store_logo');
}
if(!$physioLogo){ $physioLogo = (string)(get_store_details()->store_logo ?? ''); }
if(!$physioLogo){ $physioLogo = function_exists('get_site_logo') ? get_site_logo() : ''; }
$physioLogo = ltrim($physioLogo, '/');
if(!$physioLogo || strpos($physioLogo, '..') !== false || !is_file(FCPATH.$physioLogo)){ $physioLogo = ''; }
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= htmlspecialchars($storeName); ?> — <?= htmlspecialchars($page_title ?? 'Clinic'); ?></title>
<link rel="icon" type="image/webp" href="<?= base_url('uploads/site/icon.webp'); ?>">
<link rel="manifest" href="<?= base_url('manifest.json'); ?>">
<meta name="theme-color" content="#153b32">
<link rel="stylesheet" href="<?= $theme_link; ?>bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="<?= $theme_link; ?>plugins/select2/select2.min.css">
<link rel="stylesheet" href="<?= $theme_link; ?>plugins/DataTables-1.10.18/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="<?= $theme_link; ?>plugins/DataTables-1.10.18/extensions/Responsive-2.2.2/css/responsive.bootstrap.min.css">
<link rel="stylesheet" href="<?= $theme_link; ?>plugins/DataTables-1.10.18/extensions/Buttons-1.5.4/css/buttons.bootstrap.min.css">
<link rel="stylesheet" href="<?= $theme_link; ?>toastr/toastr.css">
<link rel="stylesheet" href="<?= $theme_link; ?>plugins/pace/pace.min.css">
<link rel="stylesheet" href="<?= $theme_link; ?>plugins/datepicker/datepicker3.css">
<link rel="stylesheet" href="<?= $theme_link; ?>plugins/daterangepicker/daterangepicker.css">
<link rel="stylesheet" href="<?= $theme_link; ?>css/assist.css?v=15">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=DM+Serif+Display&display=swap" rel="stylesheet">
<style>
:root{--mp-primary:#176753;--mp-primary-dark:#104d40;--mp-bg:#eef3f0;--mp-surface:#fff;--mp-text:#1e2d28;--mp-muted:#687a72;--mp-border:#d8e2dc;--mp-success:#176753;--mp-danger:#c95f49;--mp-warning:#a96e24;--mp-ink:#1e2d28;--mp-shadow-sm:0 1px 3px rgba(27,53,43,.06);--mp-shadow:0 14px 40px rgba(27,53,43,.07)}
.pace .pace-progress{background:var(--mp-primary)!important;height:3px}
.pace .pace-progress-inner{box-shadow:0 0 8px var(--mp-primary)!important}
.pace .pace-activity{display:none!important}
.physio-shell a:hover,.physio-shell a:focus{color:var(--mp-primary-dark)}
.physio-shell .physio-rail a:hover,.physio-shell .physio-rail a:focus{color:#fff;background:rgba(255,255,255,.08)}
.physio-shell .physio-rail a.active:hover,.physio-shell .physio-rail a.active:focus{color:#104d40;background:#dcefe7}
.physio-shell .btn-primary:hover,.physio-shell .btn-primary:focus,.physio-shell .mp-btn-primary:hover,.physio-shell .mp-btn-primary:focus{color:#fff!important;background:var(--mp-primary-dark)!important;border-color:var(--mp-primary-dark)!important}
.physio-shell .mp-qa-btn{display:inline-flex;align-items:center;gap:8px;padding:10px 16px;border:1px solid var(--mp-border);border-radius:9px;text-decoration:none;font-weight:600}
.physio-shell .mp-qa-btn.green,.physio-shell .mp-qa-btn.blue{background:#e3efe9;color:var(--mp-primary-dark)}
.physio-shell .mp-qa-btn.green:hover,.physio-shell .mp-qa-btn.green:focus,.physio-shell .mp-qa-btn.blue:hover,.physio-shell .mp-qa-btn.blue:focus{background:var(--mp-primary-dark)!important;color:#fff!important;border-color:var(--mp-primary-dark)}
.physio-shell .dataTables_wrapper{position:relative}
.physio-shell .dataTables_processing{position:absolute!important;left:50%!important;top:50%!important;transform:translate(-50%,-50%);margin:0!important;width:auto!important;max-width:calc(100% - 32px);height:auto!important;background:#fff!important;color:var(--mp-primary-dark)!important;border:1px solid var(--mp-border)!important;border-radius:9px;padding:14px 20px!important;z-index:5}
*{box-sizing:border-box!important}html{margin:0!important;min-height:100%!important;overflow-x:hidden!important}body{margin:0!important;min-height:100%!important;background:var(--mp-bg)!important;color:var(--mp-text)!important;font-family:'DM Sans',sans-serif!important;font-size:15.5px!important}body{display:block!important;overflow-y:auto!important;overflow-x:hidden!important;line-height:1.6!important}
.mp-shell.physio-shell{display:grid!important;grid-template-columns:264px minmax(0,1fr)!important;min-height:100vh!important;align-items:stretch!important}
/* The rail is a sticky 100vh column, so when the page is taller than the
   viewport the column below it was empty and the sidebar appeared to "cut off"
   part-way down.

   The first attempt at this painted the WHOLE shell the rail colour and relied
   on the content area repainting over its own half — but .mp-main{background:
   transparent!important} (same specificity, declared later) won that fight, so
   the dark colour showed through the entire content area. Instead, paint ONLY
   the rail's own 264px column with a pseudo-element, and let the content area
   keep the normal page background. */
.mp-shell.physio-shell{position:relative}
.mp-shell.physio-shell::before{content:'';position:absolute;left:0;top:0;bottom:0;width:264px;background:#153b32;pointer-events:none;z-index:0}
.physio-rail{position:relative;z-index:2}
/*
 * The content background must be set on a selector that outranks
 * .mp-main{background:transparent!important}.
 *
 * NO z-index here. Giving this element a z-index makes it a STACKING CONTEXT,
 * which traps every modal inside it: Bootstrap appends .modal-backdrop to
 * <body>, so a modal rendered inside content (z-index 1050) ends up BELOW a
 * backdrop at 1040 that lives outside its context — the backdrop painted over
 * the dialog and swallowed every click. That is what made "Register Patient"
 * look broken: the modal opened, but its Save button could not be clicked.
 * Stacking is handled by the rail's own z-index instead, which only needs to
 * sit above the ::before column. */
.mp-shell.physio-shell>.physio-main{position:static;background:var(--mp-bg)!important}
.physio-rail{position:sticky;top:0;height:100vh;overflow-y:auto;display:flex;flex-direction:column;padding:22px 14px 16px;background:#153b32;color:#e7f1ec}
/* Slim, dark scrollbar so the nav does not gain a bright strip when it scrolls. */
.physio-rail{scrollbar-width:thin;scrollbar-color:rgba(230,244,237,.28) transparent}
.physio-rail::-webkit-scrollbar{width:8px}
.physio-rail::-webkit-scrollbar-track{background:transparent}
.physio-rail::-webkit-scrollbar-thumb{background:rgba(230,244,237,.22);border-radius:4px}
.physio-rail::-webkit-scrollbar-thumb:hover{background:rgba(230,244,237,.34)}
.physio-brand{display:flex;align-items:center;gap:11px;padding:2px 10px 20px;text-decoration:none;color:inherit}
.physio-brand-logo{max-width:100%;width:auto;max-height:52px;object-fit:contain;flex-shrink:0}
.physio-brand.has-wide-logo{justify-content:center}.physio-brand.has-wide-logo .physio-brand-text{display:none}
.physio-brand:not(.has-wide-logo) .physio-brand-logo{max-width:52px;max-height:44px}.physio-user{border-top:0!important}.physio-nav-list{gap:2px}.physio-nav-group{margin-bottom:3px}
.physio-brand-mark{width:38px;height:38px;flex:0 0 38px;display:grid;place-items:center;border-radius:11px;background:#b8e0cf;color:#153b32;font-size:17px}
.physio-brand strong{display:block;font-size:14px;letter-spacing:.03em}.physio-brand small{display:block;margin-top:3px;color:#a9c9bb;font-size:10px;letter-spacing:.1em}
.physio-nav-label{padding:22px 11px 9px;color:#8eb4a3;font-size:10.5px;font-weight:700;letter-spacing:.14em;text-transform:uppercase}
/* Subsection heading inside Administration (Settings / Access / Setup & tools).
   Smaller and quieter than a section label so the rail still reads as one
   section with three parts rather than four competing ones. Only rendered when
   the subsection actually contains links. */
.physio-nav-sub-label{padding:14px 11px 6px;color:#6f9784;font-size:9.5px;font-weight:700;letter-spacing:.16em;text-transform:uppercase}
.physio-nav-list{display:grid;gap:4px}.physio-nav-link{min-height:42px;display:flex;align-items:center;gap:11px;padding:0 12px;border-radius:7px;color:#c3d8ce;font-size:13.5px;text-decoration:none}.physio-nav-link i{width:19px;text-align:center;font-size:15px}.physio-nav-link:hover{background:rgba(255,255,255,.08);color:white;text-decoration:none}.physio-nav-link.active{background:#dcefe7;color:#104d40;font-weight:700}
/* Collapsible groups — a long rail of flat links is unusable, so groups
   collapse to their heading with the active one opened automatically. */
.physio-nav-group{display:block}
.physio-nav-group-toggle{min-height:42px;display:flex;align-items:center;gap:11px;padding:0 12px;border-radius:7px;color:#c3d8ce;font-size:13.5px;font-weight:600;cursor:pointer;user-select:none}
.physio-nav-group-toggle>i.fa{width:19px;text-align:center;font-size:15px}
.physio-nav-group-toggle:hover{background:rgba(255,255,255,.08);color:#fff}
.physio-nav-group-toggle .physio-nav-chevron{margin-left:auto;font-size:11px;color:#8eb4a3;transition:transform .18s ease}
.physio-nav-group.open .physio-nav-group-toggle{color:#fff}
.physio-nav-group.open>.physio-nav-group-toggle .physio-nav-chevron{transform:rotate(90deg)}
.physio-nav-submenu{display:none;padding:4px 0 8px 14px;margin-left:9px}
.physio-nav-group.open>.physio-nav-submenu{display:grid;gap:3px}
.physio-nav-submenu .physio-nav-link{min-height:36px;font-size:13px;padding:0 10px}
.physio-nav-submenu .physio-nav-link i{font-size:13px;width:17px}
.physio-nav-link .physio-nav-badge{margin-left:auto;min-width:19px;padding:1px 6px;border-radius:9px;background:#c95f49;color:#fff;font-size:11px;font-weight:700;text-align:center}
.physio-rail-spacer{flex:1}.physio-user{display:flex;align-items:center;gap:10px;padding:15px 8px 0;border-top:1px solid rgba(230,244,237,.14);color:#e7f1ec;font-size:12px}.physio-user i{color:#b8e0cf;font-size:15px}.physio-user span{min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.physio-main{min-width:0;display:flex;flex-direction:column}.physio-topbar{min-height:78px;display:flex;align-items:center;gap:18px;padding:16px 32px;border-bottom:1px solid var(--mp-border);background:#fff}
/* The actions block is pinned to the right edge and the title keeps a floor
   width, so the row cannot reflow between screens. It used to be positioned
   only by the insights band filling the middle: with no insights the actions
   sat immediately after the title (measured at x=495 on the dashboard vs
   right-aligned on a screen that had them), so the whole row "shifted" as you
   navigated. */
.physio-title{display:block;text-decoration:none;color:inherit;flex:0 0 auto;min-width:0;max-width:38%}
.physio-title:hover{text-decoration:none;color:inherit}
.physio-title small{display:block;color:var(--mp-muted);font-size:11px;font-weight:700;letter-spacing:.11em;text-transform:uppercase}.physio-title strong{display:block;margin-top:5px;font-size:20px;font-weight:600;letter-spacing:-.01em;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
/* Insights band — the standard shell puts business insights here; the clinic
   had the space empty, so a clinic user lost the same at-a-glance hints every
   other business type gets. It now absorbs the leftover space so the title and
   actions stay anchored whatever it contains. */
.physio-insights{flex:1 1 auto;min-width:0;display:flex;align-items:center;gap:12px;padding:0 14px;border-left:1px solid var(--mp-border)}
/* With no insights to show, keep the same flexible slot so the actions do not
   jump left. */
.physio-insights-empty{flex:1 1 auto;min-width:0}
.physio-insights-label{display:inline-flex;align-items:center;gap:6px;color:var(--mp-primary);font-size:11px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;white-space:nowrap}
/* Marquee. The standard shell animates the insights strip and fades both edges;
   the clinic reused the .mp-marquee markup and its own .mp-marquee-item styles,
   but never carried the track animation — so the insights just sat there while
   every other business type got a scrolling ticker. */
.physio-insights .mp-marquee{flex:1;min-width:0;overflow:hidden;white-space:nowrap;mask-image:linear-gradient(to right,transparent,black 24px,black calc(100% - 24px),transparent);-webkit-mask-image:linear-gradient(to right,transparent,black 24px,black calc(100% - 24px),transparent)}
.physio-insights .mp-marquee-track{display:inline-flex;white-space:nowrap;animation:physio-marquee 30s linear infinite}
.physio-insights .mp-marquee-track:hover{animation-play-state:paused}
@keyframes physio-marquee{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}
.physio-insights .mp-marquee-item{display:inline-flex;align-items:center;gap:6px;margin-right:48px;font-size:13px;font-weight:500;color:var(--mp-ink)}
.physio-top-actions{display:flex;align-items:center;gap:9px;color:var(--mp-muted);font-size:13px;flex:0 0 auto}
.physio-top-actions a{color:var(--mp-primary);text-decoration:none}
/* Touch/click targets. The header controls are the most-used buttons in the
   app, so they get a comfortable minimum height and consistent padding rather
   than the tight 8px they had. */
.physio-top-actions .mp-hbtn{min-height:38px;padding:8px 14px;gap:7px}
.physio-top-actions .mp-hbtn.primary{min-height:38px;padding:8px 16px}
.physio-top-actions .mp-status-pill{min-height:32px;padding:6px 12px}
/* Licence validity badge — same thresholds as the standard shell (green with
   more than 30 days left, amber inside 30, red inside 10 or expired), restyled
   for the clinic palette. */
.physio-sub-badge{display:inline-flex;align-items:center;gap:6px;min-height:32px;padding:6px 12px;border-radius:9px;font-size:12.5px;font-weight:700;text-decoration:none;white-space:nowrap}
.physio-sub-badge:hover{text-decoration:none}
.physio-sub-badge.green{background:#e3efe9;color:#104d40}
.physio-sub-badge.green:hover{background:#d5e8de;color:#104d40}
.physio-sub-badge.orange{background:#f8f0e3;color:#76572e}
.physio-sub-badge.orange:hover{background:#f2e6d2;color:#76572e}
.physio-sub-badge.red{background:#f8e5e0;color:#a3452f}
.physio-sub-badge.red:hover{background:#f3d8d1;color:#a3452f}
/* User menu — same dropdown the standard shell offers, so the profile control
   is a menu rather than a single bare link. */
.physio-user-menu{position:relative}
.physio-user-chip{min-height:38px;display:inline-flex;align-items:center;gap:8px;padding:6px 12px;border:1px solid var(--mp-border);border-radius:9px;background:var(--mp-surface);color:var(--mp-text);font-size:13px;font-weight:600;cursor:pointer;user-select:none}
.physio-user-chip:hover{background:#eef3f0;border-color:var(--mp-primary)}
.physio-user-chip .fa-user-circle{color:var(--mp-primary);font-size:17px}
.physio-user-dropdown{position:absolute;top:calc(100% + 8px);right:0;min-width:224px;display:none;padding:6px;background:var(--mp-surface);border:1px solid var(--mp-border);border-radius:12px;box-shadow:0 14px 40px rgba(27,53,43,.14);z-index:60}
.physio-user-dropdown.open{display:block}
.physio-dropdown-head{padding:10px 12px;margin-bottom:4px;border-bottom:1px solid var(--mp-border)}
.physio-dropdown-head strong{display:block;font-size:14px;color:var(--mp-text)}
.physio-dropdown-head span{display:block;margin-top:2px;font-size:11px;color:var(--mp-muted);text-transform:uppercase;letter-spacing:.05em}
.physio-dropdown-item{display:flex;align-items:center;gap:10px;padding:9px 12px;border-radius:8px;color:var(--mp-text);font-size:13.5px;text-decoration:none}
.physio-dropdown-item:hover{background:#eef3f0;color:var(--mp-primary-dark);text-decoration:none}
.physio-dropdown-item i{width:16px;text-align:center;color:var(--mp-muted)}
.physio-dropdown-item.danger{color:var(--mp-danger)}
.physio-dropdown-item.danger i{color:var(--mp-danger)}
.physio-top-actions .mp-hbtn{display:inline-flex;align-items:center;gap:6px;padding:8px 12px;border:1px solid var(--mp-border);border-radius:8px;background:var(--mp-surface);color:var(--mp-text);font-size:13px;font-weight:600;cursor:pointer;white-space:nowrap}
.physio-top-actions .mp-hbtn:hover{border-color:var(--mp-primary);color:var(--mp-primary);text-decoration:none}
.physio-top-actions .mp-hbtn.primary{background:var(--mp-primary);border-color:var(--mp-primary);color:#fff}
.physio-top-actions .mp-hbtn.primary:hover{background:var(--mp-primary-dark);color:#fff}
.physio-top-actions .mp-status-pill{display:inline-flex;align-items:center;gap:6px;padding:6px 11px;border-radius:20px;background:#e3efe9;color:#104d40;font-size:12px;font-weight:600;white-space:nowrap}
.physio-top-actions .mp-status-dot{width:7px;height:7px;border-radius:50%;background:#176753}
.physio-top-actions .mp-status-pill.offline{background:#eceff1;color:var(--mp-muted)}
.physio-top-actions .mp-status-pill.offline .mp-status-dot{background:#94a3a0}
.physio-top-actions .mp-offline-badge{display:none;align-items:center;gap:5px;padding:5px 11px;border-radius:20px;background:var(--mp-danger);color:#fff;font-size:11px;font-weight:700;letter-spacing:.04em}
/* Narrow desktop / tablet landscape: the date is the least useful item, so it
   is the first thing to go before the action buttons wrap. */
@media(max-width:1280px){.physio-insights{display:none}}
@media(max-width:1100px){.physio-top-actions>span:not(.mp-status-pill):not(.mp-offline-badge){display:none}}
@media(max-width:900px){.physio-top-actions .mp-hbtn .hidden-xs{display:none}}
.mp-main{min-width:0!important;width:auto!important;max-width:none!important;flex:1 0 auto!important;overflow:visible!important;padding:0!important;background:transparent!important}
/* The topbar must sit flush against the top and edges of the content area;
   page padding lives on this wrapper so content clears the header. */
.physio-content{flex:1;min-width:0;padding:34px 32px 44px}
/* Direct children must span the content column. Without an explicit width a
   block-level flex/grid child here collapses to its shrink-to-fit width
   (visibly to 0), which pushed headings out past the right edge — so width is
   pinned on the content root and on the shared containers those screens use. */
.physio-content>*{width:100%!important;max-width:100%!important;box-sizing:border-box!important}
.mp-main .mp-section,
.mp-main .mp-page-head,
.mp-main .mp-page-head>*,
.mp-main .mp-card,
.mp-main .box,
.mp-main .panel,
.mp-main .clinic-panel,
.mp-main .clinic-head,
.mp-main .clinic-columns{width:100%!important;max-width:100%!important;box-sizing:border-box!important}
.mp-main .clinic-columns{width:auto!important}
/* Wide data tables must scroll rather than be clipped by the shell's
   overflow-x:hidden, otherwise the right-hand columns are unreachable. */
.mp-main .it-table-wrap,
.mp-main .dataTables_wrapper,
.mp-main .table-responsive,
.mp-main .box-body,
.mp-main .mp-table-wrap{overflow-x:auto!important;-webkit-overflow-scrolling:touch}
.mp-main .it-table-wrap>table,
.mp-main .table-responsive>table{min-width:max-content}
.mp-page-head{margin-bottom:26px!important}.mp-page-head h2{font-family:'DM Serif Display',Georgia,serif!important;font-weight:400!important;color:var(--mp-text)!important;font-size:29px!important;margin-bottom:7px!important}.mp-page-head .mp-page-sub{font-size:14px!important;line-height:1.55!important}
/* The page-head action cluster must breathe: buttons that touch read as one
   malformed control. A flex row with a real gap also wraps cleanly on narrow
   desktop instead of overflowing. */
.mp-main .mp-page-head{display:flex!important;flex-wrap:wrap!important;align-items:flex-start!important;justify-content:space-between!important;gap:14px!important}
.mp-main .mp-page-head>div:last-child{display:flex!important;flex-wrap:wrap!important;gap:10px!important;align-items:center!important}
.mp-main .mp-page-head>div:last-child>*{margin:0!important}
/* Give the header buttons a comfortable target and stop them crowding. */
.mp-main .mp-qa-btn{min-height:38px;padding:9px 15px!important}
.mp-main .mp-panel-head,.mp-main .mp-card-head{display:flex!important;flex-wrap:wrap!important;align-items:center!important;gap:12px!important}
.mp-main .mp-panel-head>div:last-child,.mp-main .mp-card-head>div:last-child{display:flex!important;flex-wrap:wrap!important;gap:10px!important}
.mp-card,.box,.panel,.modal-content{border-radius:9px!important;box-shadow:var(--mp-shadow-sm)!important}
/* Match the shell's larger reading size in shared desktop components, so the
   clinical screens do not look shrunken next to the rail and page heading. */
.mp-main p,.mp-main li,.mp-main td,.mp-main th,.mp-main label,.mp-main input,.mp-main select,.mp-main textarea{font-size:14px}
.mp-main table.dataTable{font-size:14px!important}
.mp-main table.dataTable th{font-size:12.5px!important;letter-spacing:.02em}
.mp-main .mp-page-sub,.mp-main small{font-size:13px}
.mp-main h3{font-size:18px}.mp-main h4{font-size:16px}
.mp-main .btn{font-size:14px;padding:8px 15px}
.physio-mobile-nav{display:none}.physio-mobile-nav a{color:var(--mp-muted);text-decoration:none;font-size:11px;text-align:center}.physio-mobile-nav a i{display:block;margin-bottom:4px;font-size:18px}.physio-mobile-nav a.active{color:var(--mp-primary);font-weight:700}
/* Copyright sits under the whole shell and is centred across the full width,
   matching the standard shell rather than left-aligned under the rail. */
footer.copyright{margin:0!important;color:var(--mp-muted)!important;background:#fff!important;border-top:1px solid var(--mp-border)!important;font-size:13px!important;padding:16px 20px!important;text-align:center!important;width:100%!important}
@media (max-width:900px),(max-width:1024px) and (orientation:portrait){/* The rail is hidden here, so its column must not be painted either. */
.mp-shell.physio-shell::before{display:none}
.mp-shell.physio-shell{display:block!important;min-height:100vh!important;padding-bottom:calc(80px + env(safe-area-inset-bottom))!important}.physio-rail{display:none}.physio-topbar{position:sticky;top:0;z-index:400;min-height:64px;padding:12px 16px}.physio-title strong{font-size:17px;margin-top:3px}.physio-main{min-height:100vh}.physio-content{padding:20px 16px 32px}.mp-page-head{margin-bottom:20px!important}.mp-page-head h2{font-size:24px!important}.physio-mobile-nav{position:fixed;z-index:500;right:0;bottom:0;left:0;display:grid;grid-template-columns:repeat(6,minmax(0,1fr));gap:2px;padding:9px 4px calc(9px + env(safe-area-inset-bottom));border-top:1px solid var(--mp-border);background:rgba(255,255,255,.97);backdrop-filter:blur(12px)}.physio-mobile-nav a{min-width:0;padding:4px 0}.physio-mobile-nav a i{font-size:17px}.physio-mobile-nav a span{display:block;white-space:nowrap;font-size:10px}
/* Data-heavy clinical tables stay usable on a phone: scrollable, not clipped. */
.mp-main .dataTables_wrapper{overflow-x:auto!important}.mp-main table.dataTable{font-size:13px!important;min-width:520px}
.mp-page-head{align-items:flex-start!important;flex-direction:column!important;gap:10px!important}}
@media(max-width:400px){.physio-mobile-nav a span{font-size:9px}.mp-main table.dataTable{min-width:460px}}
</style>
</head>
<body>
<div class="mp-shell physio-shell">
  <aside class="physio-rail" aria-label="Physiotherapy workspace navigation">
    <a class="physio-brand" href="<?= base_url('dashboard'); ?>" aria-label="<?= htmlspecialchars($storeName); ?>">
      <?php if($physioLogo): ?><img class="physio-brand-logo" src="<?= htmlspecialchars(base_url($physioLogo)); ?>" alt="<?= htmlspecialchars($storeName); ?>" onload="this.parentNode.classList.toggle('has-wide-logo',this.naturalWidth/this.naturalHeight>=2)"><?php else: ?><span class="physio-brand-mark"><i class="fa fa-heartbeat"></i></span><?php endif; ?>
      <span class="physio-brand-text"><strong><?= htmlspecialchars($storeName); ?></strong><small>PHYSIO + REHABILITATION</small></span>
    </a>
    <div class="physio-nav-label">Clinic</div><nav class="physio-nav-list" aria-label="Clinical workspaces">
      <?= $nav('Reception','dashboard','fa-inbox',physio_can_any(array('patients_view','appointments_view','care_queue_view','admissions_view'))); ?>
      <?= $nav('Patients','patients','fa-address-book-o',physio_can('patients_view'),'patient_registry'); ?>
      <?= $nav('Appointments','appointments','fa-calendar', $canAppointments,'appointments'); ?>
      <?= $nav('Care queue','care_queue','fa-list-ol', $canQueue,'patient_registry'); ?>
      <?= $nav('Physiotherapy','sessions','fa-stethoscope', $canSessions,'treatment_plans'); ?>
      <?= $nav('Patient accounts', $canBilling ? 'patient_billing' : 'patient_funds','fa-file-text-o', $canFunding); ?>
      <?= $nav('Investigations','investigations','fa-flask',physio_can('investigations_view'),'clinical_assessments'); ?>
      <?= $nav('Documents & consent','patient_docs','fa-file-text-o',physio_can('patient_docs_view'),'patient_documents'); ?>
    </nav>
    <?php
      /**
       * Ward — every child below requires the 'inpatient_care' capability, so
       * the section heading must be gated on it too. Without the feature check
       * a store whose grants are present but whose capability flag is off
       * rendered a bare "Ward" heading with no links under it.
       */
      $canWardSection = mp_feature_enabled('inpatient_care')
          && ($canAdmission || $canNursing || physio_can('daily_billing_view'));
    ?>
    <?php if($canWardSection): ?><div class="physio-nav-label">Ward</div><nav class="physio-nav-list" aria-label="Ward workspaces">
      <?= $nav('Admissions','inpatient','fa-bed',$canAdmission,'inpatient_care'); ?>
      <?= $nav('Bed board','inpatient/beds','fa-th',$canAdmission,'inpatient_care'); ?>
      <?= $nav('Bed accounts','inpatient/bed_ledger','fa-book',physio_can('daily_billing_view'),'inpatient_care'); ?>
      <?php /* One screen, two boards: the query string is what tells them apart, so each link claims its own board. */ ?>
      <?= $nav('Nursing tasks','inpatient/tasks?board=nursing','fa-heartbeat',physio_can('nursing_tasks_view'),'inpatient_care',array('path'=>'inpatient/tasks','get'=>array('board'=>'nursing'))); ?>
      <?= $nav('Porter tasks','inpatient/tasks?board=porter','fa-exchange',physio_can('porter_tasks_view'),'inpatient_care',array('path'=>'inpatient/tasks','get'=>array('board'=>'porter'))); ?>
      <?= $nav('Inpatient billing','inpatient/billing_setup','fa-calculator',physio_can('daily_billing_view'),'inpatient_care'); ?>
    </nav><?php endif; ?>
    <?php
      /**
       * Reports — a primary destination, not an administration chore, so it sits
       * with the clinical workspaces rather than inside the Administration
       * groups. Gated on the grant the reports controller itself requires, so
       * the link never leads to a denial.
       */
      $canReports = physio_can('clinical_reports_view');
    ?>
    <?php if($canReports): ?>
    <div class="physio-nav-label">Insight</div>
    <nav class="physio-nav-list" aria-label="Clinical insight">
      <?= $nav('Clinical reports','clinical_reports','fa-bar-chart',$canReports); ?>
    </nav>
    <?php endif; ?>
    <?php
      /**
       * Workspaces — the day-to-day business screens.
       *
       * Leads (and the booking integrator) used to sit inside Administration,
       * which is why a receptionist could only reach them from a group gated on
       * admin grants. They are front-desk work, not configuration, so they get
       * their own section that anyone holding the grant can reach. The gate is
       * unchanged — only the location is.
       */
      $canLeads      = $CI->permissions('leads_view') || $CI->permissions('leads_add');
      $canFinances   = $CI->permissions('expense_view') || $CI->permissions('payment_modes_view')
                       || $CI->permissions('accounts_view') || $CI->permissions('suppliers_view')
                       || $CI->permissions('purchase_view');
      $workspaceItems = '';
      $workspaceItems .= $nav('Leads','leads','fa-bullhorn',$canLeads);
      $workspaceItems .= $nav('Purchases','purchase','fa-cart-arrow-down',$CI->permissions('purchase_view'));
      $workspaceItems .= $nav('Suppliers','suppliers','fa-truck',$CI->permissions('suppliers_view'));
      $workspaceItems .= $nav('Expenses','expense','fa-receipt',$CI->permissions('expense_view'));
      $workspaceItems .= $nav('Payment modes','payment_modes','fa-money',$CI->permissions('payment_modes_view'));
      $workspaceItems .= $nav('Accounts','accounts','fa-calculator',accounts_module() && $CI->permissions('accounts_view'));
    ?>
    <?php if($workspaceItems !== ''): ?>
    <div class="physio-nav-label">Workspaces</div>
    <nav class="physio-nav-list" aria-label="Business workspaces"><?= $workspaceItems; ?></nav>
    <?php endif; ?>
    <?php
      /**
       * Administration — configuration only, in three anchored subsections so it
       * can grow without becoming a single undifferentiated list:
       *
       *   Settings      — how the business is configured (facility, capabilities,
       *                   subscription, clinical setup)
       *   Access        — who may do what (staff, roles, approvals)
       *   Setup & tools — operational configuration and records (branches,
       *                   payments ledger, procurement config, imports, email,
       *                   audit, and the website booking integrator)
       *
       * Every entry keeps the exact gate it had — nothing here grants access a
       * controller would not already give, and the subsection headings are only
       * printed when they contain something, so a single-grant role still sees a
       * clean rail rather than three near-empty headings.
       */
      $isOwner = ($CI->session->userdata('role_id') == 1 || is_store_admin());

      // ---- Settings ----
      $gSettings = '';
      $gSettings .= $group('Facility', 'fa-building-o', array(
        array('Facility settings', 'business_profile', 'fa-sliders', $CI->permissions('business_setup') || $CI->permissions('store_edit')),
        array('Store profile', 'store_profile/update/'.get_current_store_id(), 'fa-id-card-o', $CI->permissions('store_edit')),
        array('Branches & locations', 'warehouse', 'fa-map-marker', ($CI->permissions('warehouse_view') || $CI->permissions('warehouse_add')) && warehouse_module()),
        array('Site & branding', 'site', 'fa-picture-o', $isOwner),
      ));
      $gSettings .= $group('Features & subscription', 'fa-toggle-on', array(
        array('Enabled capabilities', 'business_profile', 'fa-toggle-on', $CI->permissions('business_setup')),
        array('Subscription & licence', 'subscription_license', 'fa-id-card-o', $isOwner),
      ));
      $gSettings .= $group('Clinical configuration', 'fa-stethoscope', array(
        array('Assessment templates', 'assessment_templates', 'fa-file-text-o', physio_can('assessment_templates_manage')),
        array('Session packages', 'service_packages', 'fa-cubes', $CI->permissions('service_packages_view'), 'packages'),
        array('Billing & ward policy', 'inpatient/billing_setup', 'fa-bed', physio_can('beds_manage'), 'inpatient_care'),
        array('Document categories', 'patient_docs', 'fa-folder-open-o', physio_can('patient_docs_view'), 'patient_documents'),
      ));

      // ---- Access ----
      $gAccess = '';
      $gAccess .= $group('Staff & permissions', 'fa-users', array(
        array('Staff accounts', 'users/view', 'fa-user', $CI->permissions('users_view')),
        array('Roles & permissions', 'roles/view', 'fa-shield', $CI->permissions('roles_view')),
        array('Approvals & authority', 'approvals/settings', 'fa-check-circle-o', $CI->permissions('approval_settings_edit') || $isOwner, 'manager_approvals'),
      ));

      // ---- Setup & tools ----
      $gSetup = '';
      $gSetup .= $group('Catalogue & pricing', 'fa-list-alt', array(
        array('Services & pricing', 'items', 'fa-list-alt',
              $CI->permissions('services_view') || $CI->permissions('services_add') || $CI->permissions('items_view')),
        array('Therapy aids & material', 'items', 'fa-cubes',
              $CI->permissions('services_view') || $CI->permissions('services_add') || $CI->permissions('items_view')),
        array('Import Items', 'import/items', 'fa-upload', $CI->permissions('import_items')),
      ));
      $gSetup .= $group('Finance setup', 'fa-credit-card', array(
        array('Opening positions', 'patient_funds/openings', 'fa-sign-in', physio_can('opening_positions_view')),
      ));
      $gSetup .= $group('Website & integrations', 'fa-code', array(
        array('Booking integrator', 'leads/integrator', 'fa-code', $canLeads),
      ));
      $gSetup .= $group('Integrations & logs', 'fa-plug', array(
        array('Legacy import', 'imports', 'fa-upload', physio_can('imports_view')),
        array('Email settings', 'email_settings', 'fa-envelope-o', $isOwner),
        // Paystack was only ever linked from the retail sidebar, so a clinic
        // had no route to its own payment configuration even when the role
        // held paystack_settings. Gated on the same permission the retail
        // sidebar uses, so behaviour is identical for anyone who had it.
        array('Paystack settings', 'paystack/settings', 'fa-credit-card',
              $CI->permissions('paystack_settings')),
        array('Audit trail', 'audit_trail', 'fa-history', $CI->permissions('audit_trail_view')),
      ));
      // Reports over the catalogue and purchase ledger keep the same retail
      // permissions their working screens use.
      $gSetup .= $group('Reports', 'fa-bar-chart', array(
        array('Therapy aids report', 'clinical_reports/aids', 'fa-cubes',
              cr_can_see(array('perm'=>'items_view','retail'=>true,
                               'perm_any'=>array('services_view','services_add','items_view')))),
        array('Procurement report', 'clinical_reports/procurement', 'fa-truck',
              cr_can_see(array('perm'=>'purchase_view','retail'=>true))),
      ));
    ?>
    <?php if($gSettings !== '' || $gAccess !== '' || $gSetup !== ''): ?>
    <div class="physio-nav-label">Administration</div>
    <nav class="physio-nav-list" aria-label="Administration">
      <?php if($gSettings !== ''): ?>
        <div class="physio-nav-sub-label">Settings</div><?= $gSettings; ?>
      <?php endif; ?>
      <?php if($gAccess !== ''): ?>
        <div class="physio-nav-sub-label">Access</div><?= $gAccess; ?>
      <?php endif; ?>
      <?php if($gSetup !== ''): ?>
        <div class="physio-nav-sub-label">Setup &amp; tools</div><?= $gSetup; ?>
      <?php endif; ?>
    </nav>
    <?php endif; ?>
    <div class="physio-rail-spacer"></div><div class="physio-user"><i class="fa fa-user-circle"></i><span><?= htmlspecialchars($userName); ?> · <?= htmlspecialchars($CI->session->userdata('role_name') ?: 'Clinic staff'); ?></span></div>
  </aside>
  <main class="physio-main mp-main" id="mp-main">
    <?php
      /*
       * Topbar — mirrors the standard shell so a clinic gets the same working
       * tools as any other business type:
       *   - the brand block shows the STORE then the screen (no invented
       *     eyebrow — the screen already repeats its own title in the body, so
       *     an eyebrow plus a repeated <h1> just says everything twice);
       *   - the space gained goes to the Insights marquee;
       *   - Sync / offline badge / POS / Clock in, which the clinic was missing.
       */
      $canPos = $CI->permissions('pos');
      $posAvailable = $canPos && function_exists('pos_module') ? pos_module() : $canPos;
      $showClock = !is_store_admin() && !is_admin();
      // Creator / Digital Store sells no stock through a till, so the POS
      // shortcut is suppressed there — same rule the standard shell applies.
      $isCreator = (mp_get_store_profile()['industry_type'] ?? '') === 'creator';

      /*
       * Licence validity.
       *
       * The standard shell shows how long the subscription has left; the clinic
       * showed nothing, so a clinic admin had no warning before the licence ran
       * out. Same thresholds as the retail shell: red once expired or inside 10
       * days, amber inside 30, otherwise green.
       */
      $lic = null;
      $licDays = null;
      $licClass = 'green';
      $licLabel = '';
      $licUrl = '#';
      if($CI->db->table_exists('db_subscription_license')){
        $CI->load->model('subscription_license_model', 'sub_lic');
        $st = $CI->sub_lic->get_status();
        if(($st['status'] ?? 'NOT_ACTIVATED') !== 'NOT_ACTIVATED'){
          $lic      = $st;
          $licDays  = (int)($st['days_left'] ?? 0);
          $licClass = $licDays <= 30 ? ($licDays <= 10 ? 'red' : 'orange') : 'green';
          $licLabel = $licDays <= 0 ? 'Expired' : $licDays . ' Days';
          $licUrl   = base_url('subscription_license');
        }
      }
    ?>
    <header class="physio-topbar">
      <a class="physio-title" href="<?= base_url('dashboard'); ?>">
        <small><?= htmlspecialchars($storeName); ?></small>
        <strong><?= htmlspecialchars($page_title ?? 'Clinic workspace'); ?></strong>
      </a>
      <?php if(!empty($insights)): ?>
      <div class="physio-insights" aria-label="Business insights">
        <span class="physio-insights-label"><i class="fa fa-lightbulb-o"></i> Insights</span>
        <div class="mp-marquee"><div class="mp-marquee-track" id="clinicIntelTrack">
          <?php foreach(array_slice($insights, 0, 6) as $ins): ?>
            <span class="mp-marquee-item"><?= htmlspecialchars(strip_tags($ins)); ?></span>
          <?php endforeach; ?>
        </div></div>
      </div>
      <?php else: ?>
      <?php /* Keeps the flexible slot so the actions stay right-aligned on screens with no insights. */ ?>
      <div class="physio-insights-empty" aria-hidden="true"></div>
      <?php endif; ?>
      <div class="physio-top-actions">
        <?php if($lic): ?>
        <a href="<?= $licUrl; ?>" class="physio-sub-badge <?= $licClass; ?>"
           title="Licence validity: <?= $licDays <= 0 ? 'expired' : $licDays . ' day(s) remaining'; ?><?= !empty($lic['end_date']) ? ' — expires ' . htmlspecialchars(date('j M Y', strtotime($lic['end_date']))) : ''; ?>">
          <i class="fa fa-calendar-check-o"></i> <span class="physio-sub-label"><?= htmlspecialchars($licLabel); ?></span>
        </a>
        <?php endif; ?>
        <span class="mp-offline-badge" id="mpOfflineBadge"><i class="fa fa-wifi"></i> OFFLINE</span>
        <?php if($posAvailable): ?>
        <button class="mp-hbtn" id="syncOfflineBtn" title="Sync items for offline use"><i class="fa fa-refresh"></i> <span class="hidden-xs">Sync</span><span id="pendingSalesBadge" style="display:none;background:var(--mp-danger);color:#fff;font-size:9px;font-weight:700;padding:1px 4px;border-radius:8px;min-width:14px;text-align:center;">0</span></button>
        <?php endif; ?>
        <span class="mp-status-pill" id="mpConnectionStatus" title="Network status"><span class="mp-status-dot"></span><span class="mp-status-text">Online</span></span>
        <?php if($showClock): ?>
        <button class="mp-hbtn" id="appClockInBtn" title="Clock in"><i class="fa fa-clock-o"></i> <span class="clock-label hidden-xs">Clock In</span></button>
        <?php endif; ?>
        <?php if($posAvailable && !$isCreator): ?>
        <a class="mp-hbtn primary" href="<?= base_url('pos'); ?>"><i class="fa fa-plus-square"></i> POS</a>
        <?php endif; ?>
        <span><?= htmlspecialchars(date('D, d M Y')); ?></span>
        <?php
          /*
           * The profile control is a menu, matching the standard shell: the
           * avatar opens Dashboard / My Profile / Change Password / Log Out
           * rather than being one bare link. 'My Profile' is the self-service
           * page — users/edit/<id> requires the admin-only users_edit grant and
           * was a 403 dead end for every clinical role.
           */
          $profileUrl = base_url('users/profile');
        ?>
        <div class="physio-user-menu">
          <div class="physio-user-chip" id="physioUserChip" role="button" tabindex="0" aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-user-circle" aria-hidden="true"></i>
            <span class="hidden-xs"><?= htmlspecialchars($userName); ?></span>
            <i class="fa fa-caret-down" aria-hidden="true" style="font-size:11px;color:var(--mp-muted);"></i>
          </div>
          <div class="physio-user-dropdown" id="physioUserDropdown">
            <div class="physio-dropdown-head">
              <strong><?= htmlspecialchars($userName); ?></strong>
              <span><?= htmlspecialchars($CI->session->userdata('role_name') ?: 'Clinic staff'); ?></span>
            </div>
            <a class="physio-dropdown-item" href="<?= base_url('dashboard'); ?>"><i class="fa fa-tachometer" aria-hidden="true"></i> Dashboard</a>
            <a class="physio-dropdown-item" href="<?= $profileUrl; ?>"><i class="fa fa-user" aria-hidden="true"></i> My Profile</a>
            <a class="physio-dropdown-item" href="<?= base_url('users/password_reset'); ?>"><i class="fa fa-lock" aria-hidden="true"></i> Change Password</a>
            <a class="physio-dropdown-item danger" href="<?= base_url('logout'); ?>"><i class="fa fa-sign-out" aria-hidden="true"></i> Log Out</a>
          </div>
        </div>
      </div>
    </header>
    <div class="physio-content">
      <?php
        /*
         * jQuery must be loaded here.
         *
         * comman/code_js.php pulls in Bootstrap and every DataTables plugin but
         * NOT jQuery itself — in the standard shell jQuery comes from
         * mp_header.php, which this clinic shell does not use. Without this
         * line every script on every clinic screen threw "jQuery is not
         * defined" and anything bound with $(...) silently did nothing: the
         * Sync button, the clock-in controls, the connection pill and the
         * user dropdown were all dead. It must come BEFORE code_js.php, whose
         * Bootstrap and DataTables plugins require it.
         */
      ?>
      <script src="<?= $theme_link; ?>plugins/jQuery/jquery-2.2.3.min.js"></script>
      <?php $this->load->view('comman/code_js_sound.php'); ?>
      <?php $this->load->view('comman/code_js.php'); ?>
      <?php $this->load->view('comman/code_flashdata'); ?>
      <?php if(!empty($extra_js_files) && is_array($extra_js_files)): foreach($extra_js_files as $js): ?><script src="<?= $theme_link . $js; ?>"></script><?php endforeach; endif; ?>
      <script>window.csrfName=<?= json_encode($this->security->get_csrf_token_name()); ?>;window.csrfHash=<?= json_encode($this->security->get_csrf_hash()); ?>;</script>
      <script>
      /* User menu. Bound in plain JS rather than jQuery so it still works if a
         screen fails to load jQuery for any reason — the shell must never be
         dead again because a library is missing. */
      (function(){
        var chip = document.getElementById('physioUserChip');
        var menu = document.getElementById('physioUserDropdown');
        if(!chip || !menu) return;
        function set(open){
          menu.classList.toggle('open', open);
          chip.setAttribute('aria-expanded', open ? 'true' : 'false');
        }
        chip.addEventListener('click', function(e){
          e.stopPropagation();
          set(!menu.classList.contains('open'));
        });
        chip.addEventListener('keydown', function(e){
          if(e.key === 'Enter' || e.key === ' '){ e.preventDefault(); set(!menu.classList.contains('open')); }
          if(e.key === 'Escape'){ set(false); }
        });
        document.addEventListener('click', function(e){
          if(!menu.contains(e.target) && !chip.contains(e.target)) set(false);
        });
      })();
      </script>
      <?= $content; ?>
    </div>
<nav class="physio-mobile-nav" aria-label="Clinic mobile navigation">
  <?php if(physio_can_any(array('appointments_view','care_queue_view','patients_view'))): ?><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-inbox"></i><span>Home</span></a><?php endif; ?>
  <?php if($canPatients): ?><a href="<?= base_url('mobile/patients'); ?>"><i class="fa fa-address-book-o"></i><span>Patients</span></a><?php endif; ?>
  <?php if($canSessions): ?><a href="<?= base_url('mobile/sessions'); ?>"><i class="fa fa-stethoscope"></i><span>Sessions</span></a><?php endif; ?>
  <?php if(physio_can('nursing_tasks_view')): ?><a href="<?= base_url('mobile/ward_tasks'); ?>"><i class="fa fa-heartbeat"></i><span>Nursing</span></a><?php endif; ?>
  <?php if($canAdmission): ?><a href="<?= base_url('mobile/admissions'); ?>"><i class="fa fa-bed"></i><span>Beds</span></a><?php endif; ?>
  <a href="<?= base_url('mobile/more'); ?>"><i class="fa fa-ellipsis-h"></i><span>More</span></a>
</nav>
      <?php $this->load->view('mp_footer', array('skip_shared_assets' => true)); ?>
