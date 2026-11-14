<?php
/**
 * Shared styles + CSRF bootstrap for Inventory desktop views.
 * Loads the finance desktop foundation (forms, tables, buttons) and adds
 * inventory-specific utilities.
 */
$this->load->view('finance/desktop/_styles');
?>
<style>
/* Make overlays and absolute children stay inside the modern shells */
.mp-card-form,.mp-table-wrap{position:relative!important}

/* Inventory dashboard KPI tweaks */
.inv-kpi-grid{display:grid!important;grid-template-columns:repeat(auto-fit,minmax(200px,1fr))!important;gap:16px!important;margin-bottom:24px!important}
.inv-kpi-card{background:var(--mp-surface)!important;border:1px solid var(--mp-border)!important;border-radius:16px!important;padding:20px!important;box-shadow:var(--mp-shadow-sm)!important}
.inv-kpi-card .inv-kpi-icon{width:44px!important;height:44px!important;border-radius:12px!important;display:flex!important;align-items:center!important;justify-content:center!important;margin-bottom:14px!important;font-size:20px!important}
.inv-kpi-card .inv-kpi-label{font-size:12px!important;color:var(--mp-muted)!important;font-weight:600!important;margin-bottom:4px!important}
.inv-kpi-card .inv-kpi-value{font-size:24px!important;font-weight:700!important;color:var(--mp-text)!important}
.inv-kpi-card.warn .inv-kpi-icon{background:rgba(245,158,11,.1)!important;color:var(--mp-warning)!important}
.inv-kpi-card.danger .inv-kpi-icon{background:rgba(220,38,38,.1)!important;color:var(--mp-danger)!important}
.inv-kpi-card.primary .inv-kpi-icon{background:rgba(0,87,255,.1)!important;color:var(--mp-primary)!important}
.inv-kpi-card.success .inv-kpi-icon{background:rgba(5,150,105,.1)!important;color:var(--mp-success)!important}
.inv-kpi-card.teal .inv-kpi-icon{background:rgba(13,148,136,.1)!important;color:#0D9488!important}

/* Recent activity tables inside dashboard cards */
.inv-recent-table{width:100%!important;border-collapse:collapse!important}
.inv-recent-table th{text-align:left!important;font-size:11px!important;font-weight:700!important;color:var(--mp-muted)!important;text-transform:uppercase!important;letter-spacing:.04em!important;padding:12px 16px!important;border-bottom:1px solid var(--mp-border)!important;background:var(--mp-bg)!important}
.inv-recent-table td{padding:12px 16px!important;font-size:13px!important;color:var(--mp-ink)!important;border-bottom:1px solid var(--mp-border)!important}
.inv-recent-table tr:last-child td{border-bottom:none!important}
.inv-recent-table tr:hover td{background:var(--mp-bg)!important}
.inv-recent-table a{color:var(--mp-primary)!important;font-weight:600!important;text-decoration:none!important}

/* Form item tables */
.inv-item-table{width:100%!important;border-collapse:collapse!important;margin:16px 0!important}
.inv-item-table th{text-align:left!important;font-size:11px!important;font-weight:700!important;color:var(--mp-muted)!important;text-transform:uppercase!important;letter-spacing:.04em!important;padding:12px 16px!important;border-bottom:1px solid var(--mp-border)!important;background:var(--mp-bg)!important}
.inv-item-table td{padding:10px 16px!important;font-size:13px!important;color:var(--mp-ink)!important;border-bottom:1px solid var(--mp-border)!important;vertical-align:middle!important}
.inv-item-table tr:last-child td{border-bottom:none!important}
.inv-item-table input[type="text"],.inv-item-table input[type="number"]{width:100%!important;border:1px solid var(--mp-border)!important;border-radius:8px!important;padding:8px 10px!important;font-size:13px!important;font-weight:500!important;color:var(--mp-ink)!important;background:var(--mp-surface)!important}
.inv-item-table input:focus{outline:none!important;border-color:var(--mp-primary)!important;box-shadow:0 0 0 3px rgba(0,87,255,.1)!important}
.inv-item-table .btn-remove{color:var(--mp-danger)!important;background:transparent!important;border:none!important;cursor:pointer!important;font-size:16px!important}

/* Detail invoice printable area */
.inv-printable{background:var(--mp-surface)!important;border:1px solid var(--mp-border)!important;border-radius:16px!important;padding:24px!important;box-shadow:var(--mp-shadow-sm)!important}
.inv-printable h3{margin:0 0 16px!important;font-size:18px!important;font-weight:700!important;color:var(--mp-text)!important}
.inv-meta-row{display:grid!important;grid-template-columns:repeat(auto-fit,minmax(200px,1fr))!important;gap:16px!important;margin-bottom:24px!important}
.inv-meta-group{display:flex!important;flex-direction:column!important;gap:4px!important}
.inv-meta-label{font-size:12px!important;color:var(--mp-muted)!important;font-weight:600!important;text-transform:uppercase!important}
.inv-meta-value{font-size:14px!important;color:var(--mp-ink)!important;font-weight:600!important}

@media print{
  .mp-sidebar,.mp-header,.mp-page-head,.mp-quick-actions,.no-print{display:none!important}
  .mp-main{padding:0!important;background:#fff!important}
  .inv-printable{border:none!important;box-shadow:none!important}
}
</style>
