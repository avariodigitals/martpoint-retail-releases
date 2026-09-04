<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title><?= $page_title; ?></title>
<link rel='shortcut icon' href='<?php echo base_url('uploads/site/icon.webp'); ?>' />
<link rel="manifest" href="<?php echo base_url('manifest.json'); ?>">
<meta name="theme-color" content="#0B1120">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="MartPoint">
<link rel="apple-touch-icon" href="<?php echo base_url('uploads/site/icon.webp'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<link rel="stylesheet" href="<?php echo $theme_link; ?>bootstrap/css/bootstrap.min.css">
<link rel="stylesheet" href="<?php echo $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
<link rel="stylesheet" href="<?php echo $theme_link; ?>plugins/select2/select2.min.css">
<link rel="stylesheet" href="<?php echo $theme_link; ?>plugins/DataTables-1.10.18/css/dataTables.bootstrap.min.css">
<link rel="stylesheet" href="<?php echo $theme_link; ?>plugins/DataTables-1.10.18/extensions/Responsive-2.2.2/css/responsive.bootstrap.min.css">
<link rel="stylesheet" href="<?php echo $theme_link; ?>plugins/DataTables-1.10.18/extensions/Buttons-1.5.4/css/buttons.bootstrap.min.css">
<link rel="stylesheet" href="<?php echo $theme_link; ?>toastr/toastr.css">
<link rel="stylesheet" href="<?php echo $theme_link; ?>plugins/datepicker/datepicker3.css">
<link rel="stylesheet" href="<?php echo $theme_link; ?>plugins/daterangepicker/daterangepicker.css">
<link rel="stylesheet" href="<?php echo $theme_link; ?>plugins/autocomplete/autocomplete.css">
<link rel="stylesheet" href="<?php echo $theme_link; ?>css/assist.css?v=14">
<link rel="stylesheet" href="<?php echo $theme_link; ?>plugins/pace/pace.min.css">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
:root{--mp-primary:#0057FF;--mp-primary-dark:#0044CC;--mp-pay:#D97706;--mp-pay-dark:#B45309;--mp-bg:#F5F4F0;--mp-surface:#FFFFFF;--mp-text:#292524;--mp-muted:#78716C;--mp-border:#E7E5E4;--mp-success:#059669;--mp-danger:#DC2626;--mp-warning:#F59E0B;--mp-ink:#44403C;--mp-shadow-sm:0 1px 2px rgba(41,37,36,.05);--mp-shadow:0 10px 25px -5px rgba(41,37,36,.08),0 4px 10px -4px rgba(41,37,36,.04)}
*{box-sizing:border-box!important}
html,body{margin:0!important;padding:0!important;height:100%!important}
body{font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif!important;background:var(--mp-bg)!important;color:var(--mp-text)!important;display:flex!important;flex-direction:column!important;overflow:hidden!important;font-size:14px!important;line-height:1.5!important}

/* ===== HEADER ===== */
.mp-header{display:flex!important;align-items:center!important;justify-content:space-between!important;gap:20px!important;padding:12px 24px!important;background:var(--mp-surface)!important;border-bottom:1px solid var(--mp-border)!important;box-shadow:var(--mp-shadow-sm)!important;z-index:20!important;flex-shrink:0!important}
.mp-brand{flex-shrink:0!important;text-decoration:none!important;color:inherit!important;cursor:pointer!important}
.mp-brand h1{font-size:18px!important;font-weight:700!important;margin:0!important;color:var(--mp-primary)!important}
.mp-brand .sub{font-size:12px!important;color:var(--mp-muted)!important;font-weight:500!important}
.mp-intelligence{flex:1!important;min-width:0!important;display:flex!important;align-items:center!important;gap:10px!important;background:var(--mp-bg)!important;border:1px solid var(--mp-border)!important;border-radius:12px!important;padding:8px 14px!important;overflow:hidden!important}
.mp-intel-label{display:flex!important;align-items:center!important;gap:6px!important;font-size:11px!important;font-weight:800!important;color:var(--mp-ink)!important;text-transform:uppercase!important;letter-spacing:.04em!important;white-space:nowrap!important;flex-shrink:0!important}
.mp-marquee{flex:1!important;min-width:0!important;overflow:hidden!important;white-space:nowrap!important;mask-image:linear-gradient(to right,transparent,black 24px,black calc(100% - 24px),transparent)!important}
.mp-marquee-track{display:inline-flex!important;white-space:nowrap!important;animation:mp-marquee 30s linear infinite!important}
.mp-marquee-track:hover{animation-play-state:paused!important}
@keyframes mp-marquee{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}
.mp-marquee-item{display:inline-flex!important;align-items:center!important;gap:6px!important;margin-right:48px!important;font-size:13px!important;font-weight:500!important;color:var(--mp-ink)!important}
.mp-marquee-item.up{color:var(--mp-success)!important}.mp-marquee-item.down{color:var(--mp-danger)!important}.mp-marquee-item.warn{color:var(--mp-warning)!important}
.mp-header-actions{display:flex!important;align-items:center!important;gap:12px!important;flex-shrink:0!important}
.mp-hbtn{display:inline-flex!important;align-items:center!important;gap:8px!important;padding:10px 16px!important;border:1px solid var(--mp-border)!important;border-radius:12px!important;background:var(--mp-surface)!important;color:var(--mp-ink)!important;font-size:14px!important;font-weight:600!important;cursor:pointer!important;transition:all .15s ease!important;text-decoration:none!important}
.mp-hbtn:hover{background:var(--mp-bg)!important;text-decoration:none!important;color:var(--mp-ink)!important}
.mp-hbtn.primary{background:var(--mp-primary)!important;border-color:var(--mp-primary)!important;color:#fff!important}
.mp-hbtn.primary:hover{background:var(--mp-primary-dark)!important;color:#fff!important}
.mp-sub-badge{display:inline-flex!important;align-items:center!important;gap:4px!important;padding:5px 10px!important;border-radius:8px!important;font-size:12px!important;font-weight:600!important;text-decoration:none!important}
.mp-sub-badge.green{background:rgba(5,150,105,.1)!important;color:var(--mp-success)!important}
.mp-sub-badge.orange{background:rgba(217,119,6,.1)!important;color:var(--mp-pay)!important}
.mp-sub-badge.red{background:rgba(220,38,38,.1)!important;color:var(--mp-danger)!important}
.mp-status-pill{display:inline-flex!important;align-items:center!important;gap:8px!important;padding:10px 14px!important;border:1px solid var(--mp-border)!important;border-radius:24px!important;background:var(--mp-surface)!important;color:var(--mp-ink)!important;font-size:13px!important;font-weight:600!important}
.mp-status-dot{width:10px!important;height:10px!important;border-radius:50%!important;background:var(--mp-success)!important;box-shadow:0 0 0 3px rgba(5,150,105,.15)!important}
.mp-status-pill.offline .mp-status-dot{background:#A8A29E!important;box-shadow:none!important}
.mp-status-pill.offline{color:var(--mp-muted)!important}
.mp-toast-container{position:fixed!important;top:20px!important;right:20px!important;z-index:10000!important;display:flex!important;flex-direction:column!important;gap:10px!important;pointer-events:none!important}
.mp-toast{background:var(--mp-surface)!important;color:var(--mp-text)!important;border:1px solid var(--mp-border)!important;border-radius:12px!important;box-shadow:var(--mp-shadow)!important;padding:14px 16px!important;min-width:300px!important;max-width:420px!important;display:flex!important;align-items:flex-start!important;gap:12px!important;transform:translateX(120%)!important;opacity:0!important;transition:all .45s cubic-bezier(.16,1,.3,1)!important;pointer-events:auto!important}
.mp-toast.show{transform:translateX(0)!important;opacity:1!important}
.mp-toast.hide{transform:translateX(-120%)!important;opacity:0!important}
.mp-toast-icon{width:32px!important;height:32px!important;border-radius:10px!important;display:flex!important;align-items:center!important;justify-content:center!important;flex-shrink:0!important;background:#D1FAE5!important;color:var(--mp-success)!important}
.mp-toast.danger .mp-toast-icon{background:#FEE2E2!important;color:var(--mp-danger)!important}
.mp-toast.warning .mp-toast-icon{background:#FEF3C7!important;color:#B45309!important}
.mp-toast-content{flex:1!important;min-width:0!important}
.mp-toast-title{font-size:14px!important;font-weight:700!important;margin:0 0 2px!important;color:var(--mp-ink)!important}
.mp-toast-message{font-size:13px!important;color:var(--mp-muted)!important;line-height:1.35!important}
.mp-toast-close{width:24px!important;height:24px!important;border:none!important;background:transparent!important;color:var(--mp-muted)!important;font-size:20px!important;line-height:22px!important;cursor:pointer!important;border-radius:6px!important;flex-shrink:0!important}
.mp-toast-close:hover{background:var(--mp-bg)!important}
.mp-lang-item{display:block!important;padding:8px 12px!important;border-radius:8px!important;font-size:13px!important;font-weight:500!important;color:var(--mp-ink)!important;text-decoration:none!important;cursor:pointer!important}
.mp-lang-item:hover{background:var(--mp-bg)!important;text-decoration:none!important}
.mp-lang-item.active{color:var(--mp-primary)!important;font-weight:700!important}
.mp-user-menu{position:relative!important}
.mp-user-chip{display:flex!important;align-items:center!important;gap:10px!important;padding:6px 12px 6px 6px!important;border:1px solid var(--mp-border)!important;border-radius:24px!important;font-size:14px!important;font-weight:600!important;cursor:pointer!important;user-select:none!important;background:var(--mp-surface)!important}
.mp-user-chip:hover{background:var(--mp-bg)!important}
.mp-user-avatar{width:32px!important;height:32px!important;border-radius:50%!important;background:#E0E7FF!important;color:var(--mp-primary)!important;display:flex!important;align-items:center!important;justify-content:center!important;font-weight:700!important;font-size:14px!important;overflow:hidden!important}
.mp-user-dropdown{position:absolute!important;top:calc(100% + 8px)!important;right:0!important;min-width:220px!important;background:var(--mp-surface)!important;border:1px solid var(--mp-border)!important;border-radius:12px!important;box-shadow:var(--mp-shadow)!important;padding:6px!important;z-index:50!important;display:none!important}
.mp-user-dropdown.open{display:block!important}
.mp-dropdown-item{display:flex!important;align-items:center!important;gap:10px!important;width:100%!important;padding:10px 12px!important;border:none!important;background:none!important;color:var(--mp-ink)!important;font-size:14px!important;font-weight:500!important;cursor:pointer!important;border-radius:8px!important;text-align:left!important}
.mp-dropdown-item:hover{background:var(--mp-bg)!important}
.mp-dropdown-item.danger{color:var(--mp-danger)!important}
.mp-dropdown-divider{height:1px!important;background:var(--mp-border)!important;margin:6px 0!important}

/* ===== SHELL ===== */
.mp-shell{flex:1!important;display:flex!important;min-height:0!important}

/* ===== SIDEBAR NAV ===== */
.mp-nav{width:240px!important;flex-shrink:0!important;background:var(--mp-surface)!important;border-right:1px solid var(--mp-border)!important;display:flex!important;flex-direction:column!important;overflow-y:auto!important;overflow-x:hidden!important}
.mp-nav-section{padding:8px 12px 4px!important}
.mp-nav-section:first-of-type{padding-top:18px!important}
.mp-nav-section-title{font-size:11px!important;font-weight:700!important;color:var(--mp-muted)!important;text-transform:uppercase!important;letter-spacing:.06em!important;padding:0 12px 4px!important}
.mp-nav-item{display:flex!important;align-items:center!important;gap:12px!important;padding:7px 12px!important;border-radius:10px!important;color:var(--mp-ink)!important;font-size:14px!important;font-weight:500!important;cursor:pointer!important;text-decoration:none!important;transition:all .12s ease!important;margin-bottom:1px!important}
.mp-nav-item:hover{background:var(--mp-bg)!important;color:var(--mp-ink)!important;text-decoration:none!important}
.mp-nav-item.active{background:var(--mp-primary)!important;color:#fff!important}
.mp-nav-icon{flex-shrink:0!important;color:var(--mp-muted)!important;display:inline-flex!important;align-items:center!important;justify-content:center!important}
.mp-nav-item.active .mp-nav-icon{color:#fff!important}
.mp-nav-icon svg{display:block!important}
.mp-nav-badge{margin-left:auto!important;background:var(--mp-danger)!important;color:#fff!important;font-size:11px!important;font-weight:700!important;padding:2px 7px!important;border-radius:10px!important}
.mp-nav-item.active .mp-nav-badge{background:rgba(255,255,255,.25)!important}
.mp-nav-group{margin-bottom:1px!important}
.mp-nav-group-toggle{display:flex!important;align-items:center!important;gap:12px!important;padding:7px 12px!important;border-radius:10px!important;color:var(--mp-ink)!important;font-size:14px!important;font-weight:500!important;cursor:pointer!important;transition:all .12s ease!important}
.mp-nav-group-toggle:hover{background:var(--mp-bg)!important}
.mp-nav-group-toggle .mp-nav-chevron{margin-left:auto!important;font-size:11px!important;color:var(--mp-muted)!important;transition:transform .2s ease!important}
.mp-nav-group.open .mp-nav-group-toggle .mp-nav-chevron{transform:rotate(-90deg)!important}
.mp-nav-submenu{display:none!important;padding-left:20px!important;overflow:hidden!important}
.mp-nav-group.open .mp-nav-submenu{display:block!important}
.mp-nav-submenu .mp-nav-item{font-size:13px!important;padding:6px 12px!important}
.mp-nav-subhead{font-size:10px!important;font-weight:800!important;color:var(--mp-muted)!important;text-transform:uppercase!important;letter-spacing:.06em!important;padding:10px 12px 4px!important;margin-top:6px!important;border-top:1px solid var(--mp-border)!important}
.mp-nav-subhead:first-child{border-top:none!important;margin-top:0!important}
.mp-nav-spacer{flex:1!important}
.mp-nav-store-card{margin:12px!important;padding:14px!important;background:var(--mp-bg)!important;border:1px solid var(--mp-border)!important;border-radius:12px!important}
.mp-nav-store-card .store-name{font-size:13px!important;font-weight:700!important;color:var(--mp-ink)!important}
.mp-nav-store-card .store-meta{font-size:11px!important;color:var(--mp-muted)!important;margin-top:2px!important}
.mp-nav-store-card .store-plan{display:inline-flex!important;align-items:center!important;gap:4px!important;margin-top:8px!important;font-size:11px!important;font-weight:600!important;color:var(--mp-success)!important;background:rgba(5,150,105,.1)!important;padding:3px 8px!important;border-radius:6px!important}

/* ===== MAIN CONTENT ===== */
.mp-main{flex:1!important;min-width:0!important;overflow-y:auto!important;overflow-x:hidden!important;padding:24px 32px 40px!important;width:100%!important;max-width:100%!important;box-sizing:border-box!important}
.mp-main > *{width:100%!important;max-width:100%!important;box-sizing:border-box!important}

/* Page title row */
.mp-page-head{display:flex!important;flex-direction:row!important;align-items:center!important;justify-content:space-between!important;gap:16px!important;margin-bottom:20px!important;flex-wrap:wrap!important;width:100%!important}
.mp-page-head > div:first-child{flex:0 1 auto!important;min-width:0!important}
.mp-page-head h2{font-size:24px!important;font-weight:700!important;margin:0!important;color:var(--mp-text)!important}
.mp-page-head .mp-page-sub{font-size:13px!important;color:var(--mp-muted)!important;margin-top:2px!important}
.mp-range-tabs{display:flex!important;gap:4px!important;background:var(--mp-surface)!important;border:1px solid var(--mp-border)!important;border-radius:10px!important;padding:4px!important}
.mp-range-tabs .tab{border:none!important;background:none!important;padding:7px 14px!important;border-radius:7px!important;font-size:13px!important;font-weight:600!important;color:var(--mp-muted)!important;cursor:pointer!important;transition:all .12s ease!important;text-decoration:none!important}
.mp-range-tabs .tab:hover{color:var(--mp-ink)!important;text-decoration:none!important}
.mp-range-tabs .tab.active{background:var(--mp-primary)!important;color:#fff!important}

/* Quick actions */
.mp-quick-actions{display:flex!important;flex-direction:row!important;gap:10px!important;flex-wrap:wrap!important;margin-bottom:24px!important;align-items:center!important;width:100%!important}
.mp-qa-btn{display:inline-flex!important;align-items:center!important;gap:8px!important;padding:11px 18px!important;border-radius:12px!important;font-size:14px!important;font-weight:600!important;cursor:pointer!important;text-decoration:none!important;transition:all .15s ease!important;border:1px solid transparent!important}
.mp-qa-btn:hover{text-decoration:none!important}
.mp-qa-btn.green{background:rgba(5,150,105,.1)!important;color:var(--mp-success)!important}.mp-qa-btn.green:hover{background:var(--mp-success)!important;color:#fff!important}
.mp-qa-btn.blue{background:rgba(0,87,255,.1)!important;color:var(--mp-primary)!important}.mp-qa-btn.blue:hover{background:var(--mp-primary)!important;color:#fff!important}
.mp-qa-btn.orange{background:rgba(217,119,6,.1)!important;color:var(--mp-pay)!important}.mp-qa-btn.orange:hover{background:var(--mp-pay)!important;color:#fff!important}
.mp-qa-btn.purple{background:rgba(124,58,237,.1)!important;color:#7C3AED!important}.mp-qa-btn.purple:hover{background:#7C3AED!important;color:#fff!important}
.mp-qa-btn.red{background:rgba(220,38,38,.1)!important;color:var(--mp-danger)!important}.mp-qa-btn.red:hover{background:var(--mp-danger)!important;color:#fff!important}
.mp-qa-btn.teal{background:rgba(13,148,136,.1)!important;color:#0D9488!important}.mp-qa-btn.teal:hover{background:#0D9488!important;color:#fff!important}
.mp-cash-inline{display:inline-flex!important;align-items:center!important;gap:8px!important;background:linear-gradient(135deg,#10B981 0%,#059669 100%)!important;color:#fff!important;padding:8px 16px!important;border-radius:12px!important;font-size:14px!important;font-weight:600!important;white-space:nowrap!important;box-shadow:0 2px 6px rgba(16,185,129,.25)!important;text-decoration:none!important;transition:opacity .15s!important}
.mp-cash-inline:hover{text-decoration:none!important;color:#fff!important;opacity:.9!important}
.mp-branch-sel{display:flex!important;align-items:center!important;gap:8px!important;white-space:nowrap!important;flex-shrink:0!important}
.mp-branch-sel select{display:inline-block!important;width:auto!important;min-width:140px!important;height:38px!important;font-size:13px!important;border-radius:8px!important;border:1px solid var(--mp-border)!important;padding:0 10px!important;background:var(--mp-surface)!important}

/* KPI grid */
.mp-kpi-grid{display:grid!important;grid-template-columns:repeat(4,minmax(0,1fr))!important;gap:16px!important;margin-bottom:24px!important}
.mp-kpi-card{background:var(--mp-surface)!important;border:1px solid var(--mp-border)!important;border-radius:16px!important;padding:20px!important;box-shadow:var(--mp-shadow-sm)!important;position:relative!important;overflow:hidden!important}
.mp-kpi-card .mp-kpi-icon{width:40px!important;height:40px!important;border-radius:10px!important;display:flex!important;align-items:center!important;justify-content:center!important;margin-bottom:14px!important;font-size:18px!important}
.mp-kpi-card.sales .mp-kpi-icon{background:rgba(0,87,255,.1)!important;color:var(--mp-primary)!important}
.mp-kpi-card.profit .mp-kpi-icon{background:rgba(5,150,105,.1)!important;color:var(--mp-success)!important}
.mp-kpi-card.expense .mp-kpi-icon{background:rgba(217,119,6,.1)!important;color:var(--mp-pay)!important}
.mp-kpi-card.debt .mp-kpi-icon{background:rgba(220,38,38,.1)!important;color:var(--mp-danger)!important}
.mp-kpi-card.stock .mp-kpi-icon{background:rgba(245,158,11,.1)!important;color:var(--mp-warning)!important}
.mp-kpi-card.cash .mp-kpi-icon{background:rgba(13,148,136,.1)!important;color:#0D9488!important}
.mp-kpi-card.target .mp-kpi-icon{background:rgba(0,87,255,.1)!important;color:var(--mp-primary)!important}
.mp-kpi-card.summary .mp-kpi-icon{background:rgba(120,113,108,.1)!important;color:var(--mp-muted)!important}
.mp-kpi-card .mp-kpi-label{font-size:13px!important;color:var(--mp-muted)!important;font-weight:500!important}
.mp-kpi-card .mp-kpi-value{font-size:26px!important;font-weight:700!important;color:var(--mp-text)!important;margin-top:4px!important;font-variant-numeric:tabular-nums!important}
.mp-kpi-card .mp-kpi-sub{font-size:12px!important;margin-top:8px!important;display:flex!important;align-items:center!important;gap:4px!important;font-weight:600!important}
.mp-kpi-sub.up{color:var(--mp-success)!important}.mp-kpi-sub.down{color:var(--mp-danger)!important}.mp-kpi-sub.neutral{color:var(--mp-muted)!important}
.mp-kpi-bar{margin-top:12px!important;height:6px!important;background:var(--mp-bg)!important;border-radius:3px!important;overflow:hidden!important}
.mp-kpi-bar-fill{height:100%!important;border-radius:3px!important}

/* Cards */
.mp-card{background:var(--mp-surface)!important;border:1px solid var(--mp-border)!important;border-radius:16px!important;box-shadow:var(--mp-shadow-sm)!important;overflow:hidden!important;margin-bottom:16px!important}
.mp-card-head{display:flex!important;align-items:center!important;justify-content:space-between!important;padding:18px 20px 14px!important;border-bottom:1px solid var(--mp-border)!important}
.mp-card-head h3{font-size:15px!important;font-weight:700!important;margin:0!important;color:var(--mp-text)!important}
.mp-card-head .mp-card-link{font-size:13px!important;color:var(--mp-primary)!important;font-weight:600!important;text-decoration:none!important;cursor:pointer!important}
.mp-card-head .mp-card-link:hover{text-decoration:underline!important}
.mp-card-body{padding:20px!important}

/* Layout rows */
.mp-row{display:grid!important;gap:16px!important;margin-bottom:24px!important}
.mp-row.r-2-1{grid-template-columns:minmax(0,2fr) minmax(0,1fr)!important}.mp-row.r-equal{grid-template-columns:minmax(0,1fr) minmax(0,1fr)!important}.mp-row.r-3{grid-template-columns:repeat(3,minmax(0,1fr))!important}

/* Top products */
.mp-top-prod{display:flex!important;align-items:center!important;gap:12px!important;padding:10px 0!important;border-bottom:1px solid var(--mp-border)!important}
.mp-top-prod:last-child{border-bottom:none!important}
.mp-top-prod-rank{width:28px!important;height:28px!important;border-radius:8px!important;display:flex!important;align-items:center!important;justify-content:center!important;font-size:12px!important;font-weight:700!important;background:var(--mp-bg)!important;color:var(--mp-muted)!important;flex-shrink:0!important}
.mp-top-prod-rank.r1{background:rgba(245,158,11,.15)!important;color:var(--mp-warning)!important}
.mp-top-prod-rank.r2{background:rgba(120,113,108,.15)!important;color:var(--mp-muted)!important}
.mp-top-prod-rank.r3{background:rgba(217,119,6,.15)!important;color:var(--mp-pay)!important}
.mp-top-prod-info{flex:1!important;min-width:0!important}
.mp-top-prod-name{font-size:13px!important;font-weight:600!important;color:var(--mp-ink)!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important}
.mp-top-prod-meta{font-size:11px!important;color:var(--mp-muted)!important}
.mp-top-prod-amt{font-size:13px!important;font-weight:700!important;font-variant-numeric:tabular-nums!important;flex-shrink:0!important}
.mp-branch-row{display:flex!important;align-items:center!important;gap:12px!important;padding:10px 0!important;border-bottom:1px solid var(--mp-border)!important}
.mp-branch-row:last-child{border-bottom:none!important}
.mp-branch-name{font-size:13px!important;font-weight:600!important;color:var(--mp-ink)!important;width:120px!important;flex-shrink:0!important}
.mp-branch-bar{flex:1!important;height:8px!important;background:var(--mp-bg)!important;border-radius:4px!important;overflow:hidden!important}
.mp-branch-bar-fill{height:100%!important;border-radius:4px!important}
.mp-branch-amt{font-size:13px!important;font-weight:700!important;font-variant-numeric:tabular-nums!important;width:100px!important;text-align:right!important;flex-shrink:0!important}

/* Pills */
.mp-pill{display:inline-flex!important;align-items:center!important;gap:4px!important;font-size:11px!important;font-weight:700!important;padding:3px 8px!important;border-radius:6px!important}
.mp-pill.ok,.mp-pill.paid{background:rgba(5,150,105,.1)!important;color:var(--mp-success)!important}
.mp-pill.low{background:rgba(245,158,11,.1)!important;color:var(--mp-warning)!important}
.mp-pill.out,.mp-pill.unpaid{background:rgba(220,38,38,.1)!important;color:var(--mp-danger)!important}
.mp-pill.partial{background:rgba(217,119,6,.1)!important;color:var(--mp-pay)!important}
.mp-tbl{width:100%!important;border-collapse:collapse!important}
.mp-tbl th{text-align:left!important;font-size:11px!important;font-weight:700!important;color:var(--mp-muted)!important;text-transform:uppercase!important;letter-spacing:.04em!important;padding:10px 16px!important;border-bottom:1px solid var(--mp-border)!important}
.mp-tbl td{padding:12px 16px!important;font-size:13px!important;color:var(--mp-ink)!important;border-bottom:1px solid var(--mp-border)!important}
.mp-tbl tr:last-child td{border-bottom:none!important}
.mp-tbl tr:hover td{background:var(--mp-bg)!important}
.mp-tbl .amt{font-weight:700!important;font-variant-numeric:tabular-nums!important}

/* Activity feed */
.mp-activity-item{display:flex!important;gap:12px!important;padding:12px 0!important;border-bottom:1px solid var(--mp-border)!important}
.mp-activity-item:last-child{border-bottom:none!important}
.mp-activity-dot{width:32px!important;height:32px!important;border-radius:50%!important;display:flex!important;align-items:center!important;justify-content:center!important;flex-shrink:0!important;font-size:14px!important}
.mp-activity-dot.sale{background:rgba(0,87,255,.1)!important;color:var(--mp-primary)!important}
.mp-activity-dot.expense{background:rgba(217,119,6,.1)!important;color:var(--mp-pay)!important}
.mp-activity-dot.stock{background:rgba(245,158,11,.1)!important;color:var(--mp-warning)!important}
.mp-activity-dot.customer{background:rgba(5,150,105,.1)!important;color:var(--mp-success)!important}
.mp-activity-dot.other{background:rgba(120,113,108,.1)!important;color:var(--mp-muted)!important}
.mp-activity-body{flex:1!important;min-width:0!important}
.mp-activity-title{font-size:13px!important;font-weight:600!important;color:var(--mp-ink)!important}
.mp-activity-meta{font-size:12px!important;color:var(--mp-muted)!important;margin-top:2px!important}
.mp-activity-amt{font-size:13px!important;font-weight:700!important;font-variant-numeric:tabular-nums!important}
.mp-chart-legend{display:flex!important;gap:20px!important;padding:0 20px 16px!important;flex-wrap:wrap!important}
.mp-legend-item{display:flex!important;align-items:center!important;gap:6px!important;font-size:12px!important;color:var(--mp-muted)!important;font-weight:500!important}
.mp-legend-dot{width:10px!important;height:10px!important;border-radius:3px!important}
.mp-target-ring{display:flex!important;align-items:center!important;gap:20px!important}
.mp-target-info .mp-target-pct{font-size:26px!important;font-weight:700!important;color:var(--mp-text)!important}
.mp-target-info .mp-target-amt{font-size:13px!important;color:var(--mp-muted)!important;margin-top:2px!important}
.mp-target-info .mp-target-eta{font-size:12px!important;color:var(--mp-success)!important;font-weight:600!important;margin-top:8px!important}

/* Insights */
.mp-insight-list{list-style:none!important;padding:0!important;margin:0!important}
.mp-insight-item{font-size:13px!important;color:var(--mp-ink)!important;padding:10px 0!important;border-bottom:1px solid var(--mp-border)!important;display:flex!important;align-items:flex-start!important;gap:8px!important}
.mp-insight-item:last-child{border-bottom:none!important}
.mp-insight-item::before{content:""!important;width:6px!important;height:6px!important;border-radius:50%!important;background:var(--mp-primary)!important;flex-shrink:0!important;margin-top:6px!important}

/* Empty state */
.mp-empty-state{padding:32px 20px!important;text-align:center!important;color:var(--mp-muted)!important;font-size:14px!important}
.mp-empty-state i{font-size:32px!important;color:var(--mp-success)!important;margin-bottom:8px!important;display:block!important}

/* Banners */
.mp-clock-banner{display:flex!important;align-items:center!important;gap:16px!important;padding:16px 20px!important;border-radius:16px!important;margin-bottom:20px!important;background:var(--mp-surface)!important;border:1px solid var(--mp-border)!important;box-shadow:var(--shadow-sm)!important}
.mp-clock-icon{width:44px!important;height:44px!important;border-radius:12px!important;display:flex!important;align-items:center!important;justify-content:center!important;flex-shrink:0!important;background:#FFF3CD!important;color:#92400E!important}
.mp-clock-icon.ok{background:#D1FAE5!important;color:#065F46!important}
.mp-clock-body{flex:1!important;min-width:0!important}
.mp-clock-title{font-size:15px!important;font-weight:700!important;color:var(--mp-ink)!important;margin:0!important}
.mp-clock-sub{font-size:13px!important;color:var(--mp-muted)!important;margin-top:2px!important}
.mp-clock-btn{border:none!important;border-radius:10px!important;padding:9px 18px!important;font-size:13px!important;font-weight:700!important;cursor:pointer!important;transition:all .12s ease!important;flex-shrink:0!important}
.mp-clock-btn.in{background:var(--mp-pay)!important;color:#fff!important}
.mp-clock-btn.in:hover{background:#B45309!important}
.mp-clock-btn.out{background:var(--mp-success)!important;color:#fff!important}
.mp-clock-btn.out:hover{background:#047857!important}
.mp-sub-banner{display:flex!important;align-items:center!important;gap:12px!important;padding:12px 18px!important;border-radius:12px!important;margin-bottom:20px!important;font-size:14px!important}
.mp-section{margin-bottom:24px!important;width:100%!important;box-sizing:border-box!important}

/* Footer */
footer.copyright{text-align:center!important;padding:10px 24px!important;background:var(--mp-surface)!important;border-top:1px solid var(--mp-border)!important;color:var(--mp-muted)!important;font-size:12px!important;font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif!important}

/* Offline badge */
.mp-offline-badge{display:none!important;align-items:center!important;gap:4px!important;padding:5px 12px!important;border-radius:20px!important;background:linear-gradient(135deg,#ff6b6b 0%,#ee5a5a 100%)!important;color:#fff!important;font-size:11px!important;font-weight:600!important;letter-spacing:.5px!important;box-shadow:0 2px 8px rgba(238,90,90,.35)!important}

/* DataTables — app-wide chrome (applies to every list table in the shell) */
.dataTables_wrapper{font-size:13px!important;font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif!important;padding:0!important;display:flex!important;flex-direction:column!important}
.dataTables_wrapper > .row{flex-shrink:0!important}
.dataTables_wrapper > table{flex-shrink:0!important}
.mp-dt-scroll{overflow-x:auto!important;width:100%!important;-webkit-overflow-scrolling:touch;flex-shrink:0!important}
table.dataTable{margin-top:0!important;margin-bottom:0!important}
.table{color:var(--mp-ink)!important;font-family:'Inter',sans-serif!important}
.table>thead>tr>th{border-bottom:1px solid var(--mp-border)!important;font-size:11px!important;text-transform:uppercase!important;letter-spacing:.06em!important;font-weight:700!important;color:var(--mp-muted)!important;font-family:'Inter',sans-serif!important;background:var(--mp-bg)!important;background-image:none!important;white-space:nowrap!important;padding:14px 16px!important}
.table>tbody>tr>td{border-bottom:1px solid var(--mp-border)!important;font-family:'Inter',sans-serif!important;padding:14px 16px!important;vertical-align:middle!important}
.table>tbody>tr:last-child>td{border-bottom:none!important}
.table>tbody>tr:hover{background:var(--mp-bg)!important}
.table tfoot th{border-top:2px solid var(--mp-border)!important;border-bottom:none!important;font-weight:700!important;color:var(--mp-text)!important;background:var(--mp-surface)!important;padding:14px 16px!important}

/* DataTables toolbar row (Show entries + Search + Export buttons) */
.dataTables_wrapper .row{margin:0!important}
.dataTables_wrapper .col-sm-12{padding:0!important}
.dataTables_wrapper .row:first-child{padding:16px 24px!important;border-bottom:1px solid var(--mp-border)!important}
.dataTables_wrapper .row:first-child .col-sm-12{display:flex!important;flex-direction:row!important;align-items:center!important;justify-content:space-between!important;gap:16px!important;flex-wrap:wrap!important}
.dataTables_wrapper .row:first-child .pull-left{margin-right:auto!important}
.dataTables_wrapper .row:first-child .pull-right{display:flex!important;align-items:center!important;gap:10px!important;float:none!important}
/* DataTables footer row (info text + pagination) — clean, presentable spacing */
.dataTables_wrapper .row:last-child{padding:18px 24px!important;align-items:center!important;border-top:1px solid var(--mp-border)!important;background:var(--mp-surface)!important;margin:0!important}
.dataTables_wrapper .row:last-child .col-sm-5,.dataTables_wrapper .row:last-child .col-sm-6:first-child{display:flex!important;align-items:center!important;padding:0!important}
.dataTables_wrapper .row:last-child .col-sm-7,.dataTables_wrapper .row:last-child .col-sm-6:last-child{display:flex!important;align-items:center!important;justify-content:flex-end!important;padding:0!important}
.dataTables_wrapper .row.mp-dt-footer{padding:18px 24px!important;border-top:1px solid var(--mp-border)!important;background:var(--mp-surface)!important}
.dataTables_wrapper .row.mp-dt-footer .col-sm-5,.dataTables_wrapper .row.mp-dt-footer .col-sm-7{display:flex!important;align-items:center!important;padding:0!important}
.dataTables_wrapper .row.mp-dt-footer .col-sm-7{justify-content:flex-end!important}

/* "Show N entries" dropdown */
.dataTables_length{font-size:13px!important;color:var(--mp-muted)!important;font-weight:500!important}
.dataTables_length label{display:flex!important;align-items:center!important;gap:8px!important;margin:0!important}
.dataTables_length select{border:1px solid var(--mp-border)!important;border-radius:8px!important;padding:6px 28px 6px 12px!important;height:36px!important;font-size:13px!important;font-weight:500!important;background:var(--mp-surface)!important;color:var(--mp-text)!important;cursor:pointer!important;appearance:none!important;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='none' stroke='%2378716C' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' viewBox='0 0 24 24'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E")!important;background-repeat:no-repeat!important;background-position:right 8px center!important}

/* Search box — icon inside, no "Search:" text */
.dataTables_filter{font-size:13px!important;color:var(--mp-muted)!important}
.dataTables_filter label{display:flex!important;align-items:center!important;gap:0!important;margin:0!important;position:relative!important;font-size:0!important}
.dataTables_filter label::after{content:''!important;position:absolute!important;left:12px!important;top:50%!important;transform:translateY(-50%)!important;width:16px!important;height:16px!important;background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%2378716C' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' viewBox='0 0 24 24'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cline x1='21' y1='21' x2='16.65' y2='16.65'/%3E%3C/svg%3E") no-repeat center!important;pointer-events:none!important}
.dataTables_filter input{border:1px solid var(--mp-border)!important;border-radius:10px!important;padding:8px 14px 8px 36px!important;height:38px!important;font-size:13px!important;font-weight:500!important;background:var(--mp-surface)!important;color:var(--mp-text)!important;min-width:240px!important;transition:all .15s ease!important}
.dataTables_filter input:focus{outline:none!important;border-color:var(--mp-primary)!important;box-shadow:0 0 0 3px rgba(0,87,255,.1)!important}

/* Export buttons (Copy, Excel, PDF, Print, CSV, Columns) */
.dt-buttons{display:flex!important;flex-wrap:wrap!important;gap:6px!important}
.dt-button,.buttons-copy,.buttons-excel,.buttons-pdf,.buttons-print,.buttons-csv,.buttons-colvis{border:1px solid var(--mp-border)!important;border-radius:8px!important;background:var(--mp-surface)!important;color:var(--mp-ink)!important;font-size:12px!important;font-weight:600!important;padding:7px 12px!important;box-shadow:none!important;cursor:pointer!important;transition:all .15s ease!important;display:inline-flex!important;align-items:center!important;gap:5px!important}
.dt-button:hover,.buttons-copy:hover,.buttons-excel:hover,.buttons-pdf:hover,.buttons-print:hover,.buttons-csv:hover,.buttons-colvis:hover{background:var(--mp-bg)!important;border-color:var(--mp-border)!important;color:var(--mp-text)!important}
.buttons-collection span{font-size:12px!important}

/* Info text ("Showing 1 to 10 of 139 entries") */
.dataTables_info{font-size:13px!important;color:var(--mp-muted)!important;font-weight:500!important;line-height:1.5!important;margin:0!important;padding:0!important}

/* Pagination */
.dataTables_paginate{display:flex!important;gap:4px!important;justify-content:flex-end!important;align-items:center!important}
.dataTables_paginate .paginate_button{border:1px solid var(--mp-border)!important;border-radius:8px!important;background:var(--mp-surface)!important;color:var(--mp-ink)!important;margin:0 2px!important;padding:7px 12px!important;font-size:13px!important;font-weight:600!important;cursor:pointer!important;transition:all .15s ease!important}
.dataTables_paginate .paginate_button:hover{background:var(--mp-bg)!important;color:var(--mp-ink)!important;border-color:var(--mp-border)!important}
.dataTables_paginate .paginate_button.current{background:var(--mp-primary)!important;color:#fff!important;border-color:var(--mp-primary)!important}
.dataTables_paginate .paginate_button.disabled{color:var(--mp-muted)!important;background:var(--mp-bg)!important;cursor:not-allowed!important;opacity:.6!important}

/* Bootstrap-integrated DataTables pagination (.pagination > li.paginate_button) */
.dataTables_paginate .pagination{margin:0!important;display:flex!important;gap:4px!important;justify-content:flex-end!important;align-items:center!important;border:none!important;box-shadow:none!important}
.dataTables_paginate .pagination>li{display:inline-flex!important;margin:0!important;padding:0!important;border:none!important;background:transparent!important;list-style:none!important}
.dataTables_paginate .pagination>li.paginate_button{border:1px solid var(--mp-border)!important;border-radius:8px!important;background:var(--mp-surface)!important;color:var(--mp-ink)!important;padding:7px 12px!important;font-size:13px!important;font-weight:600!important;cursor:pointer!important;transition:all .15s ease!important}
.dataTables_paginate .pagination>li.paginate_button>a,.dataTables_paginate .pagination>li.paginate_button>span{padding:0!important;color:inherit!important;text-decoration:none!important;background:transparent!important;border:none!important;float:none!important}
.dataTables_paginate .pagination>li.paginate_button:hover{background:var(--mp-bg)!important;color:var(--mp-ink)!important;border-color:var(--mp-border)!important}
.dataTables_paginate .pagination>li.paginate_button.active{background:var(--mp-primary)!important;color:#fff!important;border-color:var(--mp-primary)!important}
.dataTables_paginate .pagination>li.paginate_button.disabled{color:var(--mp-muted)!important;background:var(--mp-bg)!important;cursor:not-allowed!important;opacity:.6!important;border-color:var(--mp-border)!important}
.dataTables_paginate .pagination>li.paginate_button.disabled>a{color:inherit!important;background:transparent!important;border:none!important}

/* Processing indicator */
.dataTables_processing{background:var(--mp-surface)!important;border:1px solid var(--mp-border)!important;border-radius:8px!important;padding:12px 20px!important;font-size:13px!important;color:var(--mp-muted)!important;font-weight:500!important;box-shadow:var(--mp-shadow)!important}

/* Status pills inside tables */
.table .label{font-size:11px!important;font-weight:700!important;padding:5px 12px!important;border-radius:20px!important;display:inline-flex!important;align-items:center!important;gap:5px!important;min-width:64px!important;justify-content:center!important;letter-spacing:.02em!important}
.table .label-success{background:rgba(5,150,105,.1)!important;color:var(--mp-success)!important}
.table .label-danger{background:rgba(220,38,38,.1)!important;color:var(--mp-danger)!important}
.table .label-warning{background:rgba(245,158,11,.1)!important;color:var(--mp-warning)!important}
.table .label-info{background:rgba(0,87,255,.1)!important;color:var(--mp-primary)!important}
.table .label-default{background:var(--mp-bg)!important;color:var(--mp-muted)!important}
.table .label::before{content:''!important;width:6px!important;height:6px!important;border-radius:50%!important;background:currentColor!important}
.table .label-success::before{background:var(--mp-success)!important}
.table .label-danger::before{background:var(--mp-danger)!important}
.table .label-warning::before{background:var(--mp-warning)!important}
.table .label-info::before{background:var(--mp-primary)!important}
.table .label-default::before{background:var(--mp-muted)!important}

/* Checkbox sizing */
.table .checkbox{width:18px!important;height:18px!important}

/* DataTables Responsive child row - clean inline details panel */
table.dataTable > tbody > tr.child,
table.dataTable > tbody > tr.child:hover{background:var(--mp-surface)!important}
table.dataTable > tbody > tr.child > td{border-bottom:1px solid var(--mp-border)!important;padding:14px 16px!important}
table.dataTable > tbody > tr.child ul.dtr-details{display:grid!important;grid-template-columns:repeat(2,minmax(0,1fr))!important;gap:0 28px!important;width:100%!important;margin:0!important;padding:0!important;list-style:none!important}
table.dataTable > tbody > tr.child ul.dtr-details > li{display:flex!important;align-items:flex-start!important;gap:12px!important;padding:8px 0!important;border-bottom:1px solid var(--mp-border)!important}
table.dataTable > tbody > tr.child ul.dtr-details > li:last-child{border-bottom:none!important}
table.dataTable > tbody > tr.child span.dtr-title{flex:0 0 110px!important;display:inline-block!important;font-size:12px!important;font-weight:600!important;color:var(--mp-muted)!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important}
table.dataTable > tbody > tr.child span.dtr-data{flex:1 1 0!important;display:inline-block!important;min-width:0!important;color:var(--mp-ink)!important;font-size:13px!important;word-break:break-word!important}
table.dataTable > tbody > tr.child span.dtr-data .btn-group{white-space:normal!important}

/* Responsive */
@media (max-width:767px){
  table.dataTable > tbody > tr.child ul.dtr-details{grid-template-columns:minmax(0,1fr)!important}
  table.dataTable > tbody > tr.child span.dtr-title{flex:0 0 90px!important}
}
@media (max-width:767px){
  .dataTables_filter,.dataTables_length{width:100%!important;margin-bottom:0!important}
  .dataTables_filter input{width:100%!important;min-width:0!important}
  .dt-buttons{justify-content:flex-start!important}
  .dataTables_paginate{justify-content:center!important;flex-wrap:wrap!important}
  .dataTables_wrapper .row:last-child .col-sm-6{justify-content:center!important}
  .table>thead>tr>th,.table>tbody>tr>td{padding:10px 12px!important}
}

/* Bootstrap override for our shell */
.mp-shell .btn,.mp-shell .form-control,.mp-shell select,.mp-shell input,.mp-shell a{font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif!important}
.mp-shell .small-box{border-radius:12px!important}

/* ===== MartPoint Assist — desktop positioning overrides ===== */
/* The assist.css positions the FAB for mobile (above bottom nav). On desktop, */
/* position it at the bottom-right corner of the viewport, above the copyright footer. */
.mp-shell ~ .mp-fab-wrapper,
body > .mp-fab-wrapper{bottom:24px!important;right:24px!important;z-index:9997!important}
.mp-shell ~ .mp-assist-panel,
body > .mp-assist-panel{z-index:9999!important}
.mp-shell ~ .mp-assist-overlay,
body > .mp-assist-overlay{z-index:9998!important}
.mp-shell ~ .mp-support-modal,
body > .mp-support-modal{z-index:10000!important}

/* Responsive */
@media(max-width:1200px){.mp-kpi-grid{grid-template-columns:repeat(2,minmax(0,1fr))!important}.mp-row.r-2-1,.mp-row.r-equal,.mp-row.r-3{grid-template-columns:minmax(0,1fr)!important}}
@media(max-width:768px){.mp-nav{display:none!important}.mp-kpi-grid{grid-template-columns:minmax(0,1fr)!important}.mp-main{padding:16px!important}.mp-intelligence{display:none!important}}
/* AdminLTE neutralisers inside .mp-main */
.mp-main .content-wrapper,.mp-main .content{margin-left:0!important;min-height:auto!important;background:transparent!important;padding:0!important}
.mp-main .box{border-radius:16px!important;border:1px solid var(--mp-border)!important;background:var(--mp-surface)!important;box-shadow:var(--mp-shadow-sm)!important}
.mp-main .box-header{border-bottom:1px solid var(--mp-border)!important;padding:18px 20px 14px!important}
.mp-main .box-title{font-size:15px!important;font-weight:700!important;color:var(--mp-text)!important}
</style>
<?php if(!empty($extra_css_files) && is_array($extra_css_files)): ?>
<?php foreach($extra_css_files as $css): ?><link rel="stylesheet" href="<?php echo $theme_link . $css; ?>"><?php endforeach; ?>
<?php endif; ?>
<!-- jQuery 2.2.3 (required by Bootstrap, DataTables, toastr, and all AJAX scripts) -->
<script src="<?php echo $theme_link; ?>plugins/jQuery/jquery-2.2.3.min.js"></script>
</head>
<body>
<?php $CI =& get_instance(); ?>

<!-- ===== HEADER ===== -->
<header class="mp-header">
  <a href="<?= base_url('dashboard'); ?>" class="mp-brand">
    <h1><?= htmlspecialchars($this->session->userdata('store_name') ?: 'MartPoint'); ?></h1>
    <div class="sub"><?= $page_title ?? 'Dashboard'; ?></div>
  </a>
  <div class="mp-intelligence">
    <div class="mp-intel-label"><i class="fa fa-lightbulb-o"></i> Insights</div>
    <div class="mp-marquee"><div class="mp-marquee-track" id="intelTrack">
      <?php if(!empty($insights)): foreach(array_slice($insights, 0, 6) as $ins): ?>
        <span class="mp-marquee-item"><?= htmlspecialchars(strip_tags($ins)); ?></span>
      <?php endforeach; else: ?>
        <span class="mp-marquee-item">Keep selling to receive business insights.</span>
      <?php endif; ?>
      <?php if(!empty($insights)): foreach(array_slice($insights, 0, 6) as $ins): ?>
        <span class="mp-marquee-item"><?= htmlspecialchars(strip_tags($ins)); ?></span>
      <?php endforeach; endif; ?>
    </div></div>
  </div>
  <div class="mp-header-actions">
    <span class="mp-offline-badge" id="mpOfflineBadge"><i class="fa fa-wifi"></i> OFFLINE</span>
    <?php if($CI->permissions('pos')): ?>
    <button class="mp-hbtn" id="syncOfflineBtn" title="Sync Items for Offline Use"><i class="fa fa-refresh"></i> <span class="hidden-xs">Sync</span><span id="pendingSalesBadge" style="display:none;background:var(--mp-danger);color:#fff;font-size:9px;font-weight:700;padding:1px 4px;border-radius:8px;min-width:14px;text-align:center;">0</span></button>
    <?php endif; ?>
    <?php
    if($CI->db->table_exists('db_subscription_license')){
      $CI->load->model('subscription_license_model','sub_lic');
      $sub_status = $CI->sub_lic->get_status();
      if($sub_status['status'] !== 'NOT_ACTIVATED'):
        $badge_class = 'green';
        if($sub_status['days_left'] <= 0){ $badge_class = 'red'; }
        elseif($sub_status['days_left'] <= 10){ $badge_class = 'red'; }
        elseif($sub_status['days_left'] <= 30){ $badge_class = 'orange'; }
        $days_label = ($sub_status['days_left'] <= 0) ? 'Expired' : $sub_status['days_left'] . ' Days';
        $badge_link = special_access() ? base_url('subscription_license') : '#';
    ?>
    <a href="<?= $badge_link; ?>" class="mp-sub-badge <?= $badge_class; ?>" style="<?= (special_access() ? '' : 'cursor:default;pointer-events:none;'); ?>"><i class="fa fa-calendar-check-o"></i> <?= $days_label; ?></a>
    <?php endif; } ?>
    <div class="mp-status-pill" id="mpConnectionStatus" title="Network status">
      <span class="mp-status-dot"></span>
      <span class="mp-status-text">Online</span>
    </div>
    <button class="mp-hbtn" id="appClockInBtn" title="Clock In"><i class="fa fa-clock-o"></i> <span class="clock-label hidden-xs">Clock In</span></button>
    <?php if($CI->permissions('pos')): ?>
    <a class="mp-hbtn primary" href="<?= base_url('pos'); ?>"><i class="fa fa-plus-square"></i> POS</a>
    <?php endif; ?>
    <div class="mp-user-menu">
      <div class="mp-user-chip" onclick="document.getElementById('mpUserDropdown').classList.toggle('open')">
        <div class="mp-user-avatar"><img src="<?= get_profile_picture(); ?>" style="width:100%;height:100%;object-fit:cover;border-radius:50%;" alt="Profile" onerror="this.style.display='none'"></div>
        <span class="hidden-xs"><?= htmlspecialchars($this->session->userdata('display_name')); ?></span>
      </div>
      <div class="mp-user-dropdown" id="mpUserDropdown">
        <div style="padding:12px;border-bottom:1px solid var(--mp-border);margin-bottom:6px;">
          <div style="font-weight:700;font-size:14px;color:var(--mp-text);"><?= htmlspecialchars($this->session->userdata('display_name')); ?></div>
          <div style="font-size:11px;color:var(--mp-muted);text-transform:uppercase;"><?= htmlspecialchars($this->session->userdata('role_name')); ?></div>
        </div>
        <a class="mp-dropdown-item" href="<?= base_url('dashboard'); ?>"><i class="fa fa-tachometer"></i> Dashboard</a>
        <a class="mp-dropdown-item" href="<?= base_url('users/edit/'.$this->session->userdata('inv_userid')); ?>"><i class="fa fa-user"></i> My Profile</a>
        <div class="mp-dropdown-divider"></div>
        <a class="mp-dropdown-item" href="<?= base_url('users/password_reset'); ?>"><i class="fa fa-lock"></i> Change Password</a>
        <a class="mp-dropdown-item danger" href="<?= base_url('logout'); ?>"><i class="fa fa-sign-out"></i> Log Out</a>
      </div>
    </div>
  </div>
</header>
