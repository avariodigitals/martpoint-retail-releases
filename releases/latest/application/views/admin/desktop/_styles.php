<?php
/**
 * Shared styles + CSRF bootstrap for Administration desktop views.
 * Loads the finance desktop foundation and adds admin-specific components.
 */
$this->load->view('finance/desktop/_styles');
?>
<style>
/* Admin dashboard menu grid */
.admin-menu-grid{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(220px,1fr))!important;gap:16px!important;margin-bottom:24px!important}
.admin-menu-card{background:var(--mp-surface)!important;border:1px solid var(--mp-border)!important;border-radius:16px!important;padding:18px!important;box-shadow:var(--mp-shadow-sm)!important;transition:all .15s ease!important;text-decoration:none!important;color:var(--mp-ink)!important;display:flex!important;align-items:flex-start!important;gap:14px!important}
.admin-menu-card:hover{border-color:var(--mp-primary)!important;box-shadow:0 4px 12px rgba(0,87,255,.08)!important;text-decoration:none!important;color:var(--mp-ink)!important}
.admin-menu-card .icon{width:44px!important;height:44px!important;border-radius:12px!important;display:flex!important;align-items:center!important;justify-content:center!important;font-size:18px!important;flex-shrink:0!important;background:rgba(0,87,255,.08)!important;color:var(--mp-primary)!important}
.admin-menu-card .body{flex:1!important;min-width:0!important}
.admin-menu-card .title{font-size:14px!important;font-weight:700!important;color:var(--mp-text)!important;margin:0 0 2px!important}
.admin-menu-card .meta{font-size:12px!important;color:var(--mp-muted)!important;line-height:1.35!important}

.admin-section-title{font-size:13px!important;font-weight:700!important;color:var(--mp-muted)!important;text-transform:uppercase!important;letter-spacing:.05em!important;margin:0 0 14px!important}

/* KPI card colour variants used by the admin dashboard */
.mp-kpi-card.primary .mp-kpi-icon{background:rgba(0,87,255,.1)!important;color:var(--mp-primary)!important}
.mp-kpi-card.teal .mp-kpi-icon{background:rgba(20,184,166,.1)!important;color:#14B8A6!important}
.mp-kpi-card.success .mp-kpi-icon{background:rgba(5,150,105,.1)!important;color:var(--mp-success)!important}
.mp-kpi-card.purple .mp-kpi-icon{background:rgba(124,58,237,.1)!important;color:#7C3AED!important}
.mp-kpi-card.warn .mp-kpi-icon{background:rgba(245,158,11,.1)!important;color:var(--mp-warning)!important}

/* CRUD list action buttons alignment */
.mp-dt-table .mp-actions{display:flex!important;gap:8px!important;align-items:center!important}
.mp-dt-table .mp-actions a,.mp-dt-table .mp-actions button{width:32px!important;height:32px!important;border-radius:8px!important;border:1px solid var(--mp-border)!important;background:var(--mp-surface)!important;color:var(--mp-ink)!important;display:inline-flex!important;align-items:center!important;justify-content:center!important;cursor:pointer!important;transition:all .15s ease!important;text-decoration:none!important;padding:0!important}
.mp-dt-table .mp-actions a:hover,.mp-dt-table .mp-actions button:hover{background:var(--mp-bg)!important}
.mp-dt-table .mp-actions .mp-edit:hover{border-color:var(--mp-primary)!important;color:var(--mp-primary)!important;background:rgba(0,87,255,.06)!important}
.mp-dt-table .mp-actions .mp-delete:hover{border-color:var(--mp-danger)!important;color:var(--mp-danger)!important;background:rgba(220,38,38,.06)!important}

/* Status pills */
.mp-dt-table .label-success{background:rgba(5,150,105,.1)!important;color:var(--mp-success)!important}
.mp-dt-table .label-danger{background:rgba(220,38,38,.1)!important;color:var(--mp-danger)!important}
.mp-dt-table .label-info{background:rgba(0,87,255,.1)!important;color:var(--mp-primary)!important}

/* Empty state inside table */
.mp-empty-state{padding:48px 20px!important;text-align:center!important;color:var(--mp-muted)!important;font-size:14px!important}

/* ===== MODERN TAB STYLING (replaces AdminLTE nav-tabs-custom) ===== */
.nav-tabs-custom{background:var(--mp-surface)!important;border-radius:12px!important;border:1px solid var(--mp-border)!important;overflow:hidden!important;margin-bottom:20px!important}
.nav-tabs-custom>.nav-tabs{margin:0!important;padding:0 12px!important;background:var(--mp-bg)!important;border-bottom:2px solid var(--mp-border)!important;display:flex!important;flex-wrap:wrap!important;gap:0!important}
.nav-tabs-custom>.nav-tabs>li{margin:0!important;list-style:none!important;float:none!important}
.nav-tabs-custom>.nav-tabs>li>a{padding:14px 18px!important;font-size:14px!important;font-weight:600!important;color:var(--mp-muted)!important;border:none!important;border-bottom:3px solid transparent!important;margin-bottom:-2px!important;background:none!important;text-decoration:none!important;transition:all .15s ease!important;display:inline-block!important}
.nav-tabs-custom>.nav-tabs>li>a:hover{color:var(--mp-ink)!important;background:none!important;text-decoration:none!important;border-radius:0!important}
.nav-tabs-custom>.nav-tabs>li.active>a{color:var(--mp-primary)!important;border-bottom-color:var(--mp-primary)!important;background:none!important}
.nav-tabs-custom>.nav-tabs>li.active>a:hover{color:var(--mp-primary)!important;background:none!important}
.nav-tabs-custom>.tab-content{padding:24px!important;background:var(--mp-surface)!important}

/* Bootstrap tab-pane fallback (for views using bare .nav-tabs without nav-tabs-custom) */
.nav-tabs{border-bottom:2px solid var(--mp-border)!important;display:flex!important;flex-wrap:wrap!important;gap:0!important;margin-bottom:20px!important;padding-left:0!important;list-style:none!important}
.nav-tabs>li{margin:0!important;list-style:none!important}
.nav-tabs>li>a{padding:12px 18px!important;font-size:14px!important;font-weight:600!important;color:var(--mp-muted)!important;border:none!important;border-bottom:3px solid transparent!important;margin-bottom:-2px!important;background:none!important;text-decoration:none!important;transition:all .15s ease!important;display:inline-block!important}
.nav-tabs>li>a:hover{color:var(--mp-ink)!important;background:none!important;text-decoration:none!important;border-radius:0!important}
.nav-tabs>li.active>a{color:var(--mp-primary)!important;border-bottom-color:var(--mp-primary)!important;background:none!important}
.nav-tabs>li.active>a:hover{color:var(--mp-primary)!important;background:none!important}
.tab-pane{display:none!important}
.tab-pane.active{display:block!important}

/* Box fallback styling (for views still using .box) */
.mp-card-body .box{border:none!important;box-shadow:none!important;margin:0!important;background:transparent!important}
.mp-card-body .box-header{border-bottom:1px solid var(--mp-border)!important;padding:12px 0!important;margin-bottom:16px!important}
.mp-card-body .box-header h3,.mp-card-body .box-header .box-title{font-size:15px!important;font-weight:700!important;color:var(--mp-text)!important;margin:0!important}
.mp-card-body .box-body{padding:0!important}
.mp-card-body .box-footer{border-top:1px solid var(--mp-border)!important;padding:16px 0 0!important;margin-top:16px!important}
.bp-tab-pane .box-body{padding:0!important;background:transparent!important}
.bp-tab-content .box-body{padding:0!important;background:transparent!important}

/* ===== ADMINLTE BOX FALLBACK (for views with box-* outside mp-card-body) ===== */
.box{background:var(--mp-surface)!important;border:1px solid var(--mp-border)!important;border-radius:12px!important;margin-bottom:20px!important;box-shadow:var(--mp-shadow-sm)!important;position:relative!important}
.box-header{padding:16px 20px 12px!important;border-bottom:1px solid var(--mp-border)!important;display:flex!important;align-items:center!important;justify-content:space-between!important;gap:12px!important}
.box-header .box-title{font-size:15px!important;font-weight:700!important;color:var(--mp-text)!important;margin:0!important;flex:1!important}
.box-header.with-border{border-bottom:1px solid var(--mp-border)!important}
.box-body{padding:20px!important;border-radius:0 0 12px 12px!important}
.box-footer{padding:12px 20px!important;border-top:1px solid var(--mp-border)!important;border-radius:0 0 12px 12px!important}
.box-tools{flex-shrink:0!important}
.box-tools.pull-right{float:none!important}
.box.box-primary{border-top:3px solid var(--mp-primary)!important}
.box.box-success{border-top:3px solid var(--mp-success)!important}
.box.box-info{border-top:3px solid var(--mp-primary)!important}
.box.box-warning{border-top:3px solid var(--mp-warning)!important}
.box.box-danger{border-top:3px solid var(--mp-danger)!important}
.box.box-default{border-top:3px solid var(--mp-muted)!important}

/* ===== CALLOUT FALLBACK ===== */
.callout{background:var(--mp-bg)!important;border:1px solid var(--mp-border)!important;border-left:4px solid var(--mp-primary)!important;border-radius:8px!important;padding:14px 16px!important;margin-bottom:16px!important;font-size:14px!important;color:var(--mp-text)!important}
.callout.callout-info{border-left-color:var(--mp-primary)!important;background:rgba(0,87,255,.04)!important}
.callout.callout-warning{border-left-color:var(--mp-warning)!important;background:rgba(245,158,11,.04)!important}
.callout.callout-danger{border-left-color:var(--mp-danger)!important;background:rgba(220,38,38,.04)!important}
.callout.callout-success{border-left-color:var(--mp-success)!important;background:rgba(5,150,105,.04)!important}
.callout h4{font-size:14px!important;font-weight:700!important;margin:0 0 6px!important;color:var(--mp-text)!important}

/* ===== INFO-BOX FALLBACK ===== */
.info-box{background:var(--mp-surface)!important;border:1px solid var(--mp-border)!important;border-radius:12px!important;padding:16px!important;display:flex!important;align-items:center!important;gap:14px!important;margin-bottom:16px!important;box-shadow:var(--mp-shadow-sm)!important;min-height:80px!important}
.info-box-icon{width:48px!important;height:48px!important;border-radius:10px!important;display:flex!important;align-items:center!important;justify-content:center!important;font-size:22px!important;flex-shrink:0!important;background:rgba(0,87,255,.1)!important;color:var(--mp-primary)!important}
.info-box-content{flex:1!important;min-width:0!important}
.info-box-text{font-size:13px!important;font-weight:600!important;color:var(--mp-muted)!important;text-transform:uppercase!important;letter-spacing:.03em!important;display:block!important}
.info-box-number{font-size:22px!important;font-weight:800!important;color:var(--mp-text)!important;display:block!important;margin-top:2px!important}
.info-box .progress-description{font-size:12px!important;color:var(--mp-muted)!important;margin-top:4px!important;display:block!important}
.info-box.bg-aqua{background:rgba(0,87,255,.04)!important;border-color:rgba(0,87,255,.15)!important}
.info-box.bg-aqua .info-box-icon{background:rgba(0,87,255,.1)!important;color:var(--mp-primary)!important}
.info-box.bg-green{background:rgba(5,150,105,.04)!important;border-color:rgba(5,150,105,.15)!important}
.info-box.bg-green .info-box-icon{background:rgba(5,150,105,.1)!important;color:var(--mp-success)!important}
.info-box.bg-yellow{background:rgba(245,158,11,.04)!important;border-color:rgba(245,158,11,.15)!important}
.info-box.bg-yellow .info-box-icon{background:rgba(245,158,11,.1)!important;color:var(--mp-warning)!important}
.info-box.bg-red{background:rgba(220,38,38,.04)!important;border-color:rgba(220,38,38,.15)!important}
.info-box.bg-red .info-box-icon{background:rgba(220,38,38,.1)!important;color:var(--mp-danger)!important}

/* ===== FORM-HORIZONTAL FALLBACK ===== */
.form-horizontal .control-label{text-align:left!important;font-size:13px!important;font-weight:600!important;color:var(--mp-ink)!important;padding-top:0!important;margin-bottom:6px!important;display:block!important}
.form-horizontal .form-group{margin-bottom:16px!important}
.form-horizontal .col-sm-2,.form-horizontal .col-sm-3,.form-horizontal .col-sm-4{padding-left:0!important;padding-right:0!important}
.form-horizontal .col-sm-offset-2{margin-left:0!important}
.form-control{border:1px solid var(--mp-border)!important;border-radius:8px!important;padding:10px 12px!important;font-size:14px!important;color:var(--mp-text)!important;background:var(--mp-surface)!important;transition:border-color .15s ease!important;height:auto!important}
.form-control:focus{border-color:var(--mp-primary)!important;box-shadow:0 0 0 3px rgba(0,87,255,.08)!important;outline:none!important}
.form-control.input-sm{padding:6px 10px!important;font-size:13px!important}
textarea.form-control{min-height:80px!important;resize:vertical!important}
select.form-control{appearance:auto!important;-webkit-appearance:auto!important}

/* ===== TABLE FALLBACK — NO MORE EXCEL LOOK ===== */
.table{width:100%!important;border-collapse:collapse!important;font-size:13px!important}
.table thead th{font-size:11px!important;text-transform:uppercase!important;font-weight:700!important;color:var(--mp-muted)!important;letter-spacing:.06em!important;border-bottom:2px solid var(--mp-border)!important;padding:14px 16px!important;white-space:nowrap!important;background:var(--mp-bg)!important;text-align:left!important}
.table tbody td{padding:14px 16px!important;border-bottom:1px solid var(--mp-border)!important;color:var(--mp-text)!important;vertical-align:middle!important}
.table tbody tr:last-child td{border-bottom:none!important}
.table tbody tr:hover{background:var(--mp-bg)!important}
.table-bordered{border:none!important}
.table-bordered thead th{border:none!important;border-bottom:2px solid var(--mp-border)!important}
.table-bordered tbody td{border:none!important;border-bottom:1px solid var(--mp-border)!important}
.table-striped tbody tr:nth-child(odd){background:transparent!important}
.table-striped tbody tr:nth-child(even){background:rgba(245,244,240,.4)!important}
.table-striped tbody tr:hover{background:var(--mp-bg)!important}
.table-hover tbody tr:hover{background:var(--mp-bg)!important}
.table-responsive{overflow-x:auto!important;-webkit-overflow-scrolling:touch!important}

/* ===== LABEL/BADGE FALLBACK ===== */
.label{font-size:11px!important;font-weight:700!important;padding:5px 12px!important;border-radius:20px!important;display:inline-flex!important;align-items:center!important;gap:5px!important;min-width:64px!important;justify-content:center!important;letter-spacing:.02em!important}
.label-success{background:rgba(5,150,105,.1)!important;color:var(--mp-success)!important}
.label-danger{background:rgba(220,38,38,.1)!important;color:var(--mp-danger)!important}
.label-info{background:rgba(0,87,255,.1)!important;color:var(--mp-primary)!important}
.label-warning{background:rgba(245,158,11,.1)!important;color:var(--mp-warning)!important}
.label-default{background:var(--mp-bg)!important;color:var(--mp-muted)!important}
.label::before{content:''!important;width:6px!important;height:6px!important;border-radius:50%!important;background:currentColor!important}

/* ===== BUTTON FALLBACK ===== */
.btn{border-radius:8px!important;font-weight:600!important;font-size:14px!important;padding:10px 16px!important;transition:all .15s ease!important;border:1px solid transparent!important}
.btn-sm{padding:7px 12px!important;font-size:13px!important}
.btn-xs{padding:4px 8px!important;font-size:12px!important}
.btn-primary{background:var(--mp-primary)!important;border-color:var(--mp-primary)!important;color:#fff!important}
.btn-primary:hover{background:var(--mp-primary-dark)!important;border-color:var(--mp-primary-dark)!important;color:#fff!important}
.btn-success{background:var(--mp-success)!important;border-color:var(--mp-success)!important;color:#fff!important}
.btn-success:hover{background:#047857!important;border-color:#047857!important;color:#fff!important}
.btn-danger{background:var(--mp-danger)!important;border-color:var(--mp-danger)!important;color:#fff!important}
.btn-danger:hover{background:#B91C1C!important;border-color:#B91C1C!important;color:#fff!important}
.btn-warning{background:var(--mp-warning)!important;border-color:var(--mp-warning)!important;color:#fff!important}
.btn-warning:hover{background:#D97706!important;border-color:#D97706!important;color:#fff!important}
.btn-default{background:var(--mp-surface)!important;border-color:var(--mp-border)!important;color:var(--mp-ink)!important}
.btn-default:hover{background:var(--mp-bg)!important;color:var(--mp-ink)!important}
.btn-info{background:var(--mp-primary)!important;border-color:var(--mp-primary)!important;color:#fff!important}
.btn-info:hover{background:var(--mp-primary-dark)!important;color:#fff!important}

/* ===== ALERT FALLBACK ===== */
.alert{border-radius:10px!important;padding:14px 16px!important;border:1px solid var(--mp-border)!important;margin-bottom:16px!important;font-size:14px!important}
.alert-info{background:rgba(0,87,255,.04)!important;border-color:rgba(0,87,255,.15)!important;color:var(--mp-primary)!important}
.alert-success{background:rgba(5,150,105,.04)!important;border-color:rgba(5,150,105,.15)!important;color:var(--mp-success)!important}
.alert-warning{background:rgba(245,158,11,.04)!important;border-color:rgba(245,158,11,.15)!important;color:var(--mp-warning)!important}
.alert-danger{background:rgba(220,38,38,.04)!important;border-color:rgba(220,38,38,.15)!important;color:var(--mp-danger)!important}

/* ===== PROGRESS BAR FALLBACK ===== */
.progress{height:8px!important;border-radius:4px!important;background:var(--mp-bg)!important;margin-bottom:16px!important;overflow:hidden!important}
.progress-bar{background:var(--mp-primary)!important;border-radius:4px!important;transition:width .3s ease!important}
.progress-bar-success{background:var(--mp-success)!important}
.progress-bar-warning{background:var(--mp-warning)!important}
.progress-bar-danger{background:var(--mp-danger)!important}
.progress-bar-info{background:var(--mp-primary)!important}

/* ===== INPUT-GROUP FALLBACK ===== */
.input-group{display:flex!important;gap:0!important}
.input-group .form-control{flex:1!important;border-radius:8px 0 0 8px!important}
.input-group .input-group-btn .btn{border-radius:0 8px 8px 0!important;margin:0!important}
.input-group-btn:last-child>.btn{border-radius:0 8px 8px 0!important}
.input-group-btn:first-child>.btn{border-radius:8px 0 0 8px!important}

/* ===== MISC ADMINLTE CLEANUP ===== */
.pull-right{float:none!important;margin-left:auto!important}
.pull-left{float:none!important;margin-right:auto!important}
.text-muted{color:var(--mp-muted)!important}
.text-center{text-align:center!important}
.text-left{text-align:left!important}
.text-right{text-align:right!important}
.bg-aqua{background:rgba(0,87,255,.06)!important;color:var(--mp-primary)!important}
.bg-green{background:rgba(5,150,105,.06)!important;color:var(--mp-success)!important}
.bg-red{background:rgba(220,38,38,.06)!important;color:var(--mp-danger)!important}
.bg-yellow{background:rgba(245,158,11,.06)!important;color:var(--mp-warning)!important}
.bg-teal{background:rgba(13,148,136,.06)!important;color:#0D9488!important}
.bg-olive{background:rgba(5,150,105,.06)!important;color:var(--mp-success)!important}
.color-palette{color:var(--mp-ink)!important}
.margin-bottom-12{margin-bottom:12px!important}
.margin-left-10{margin-left:10px!important}
hr{border:none!important;border-top:1px solid var(--mp-border)!important;margin:20px 0!important}
.badge{font-size:11px!important;font-weight:700!important;padding:3px 8px!important;border-radius:10px!important;background:var(--mp-primary)!important;color:#fff!important}
.badge.bg-green{background:rgba(5,150,105,.15)!important;color:var(--mp-success)!important}
.badge.bg-red{background:rgba(220,38,38,.15)!important;color:var(--mp-danger)!important}
.badge.bg-aqua{background:rgba(0,87,255,.15)!important;color:var(--mp-primary)!important}
</style>
