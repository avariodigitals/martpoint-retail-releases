<?php
/**
 * Shared styles for Reports desktop views.
 * Loads the finance desktop base and extends it with report-specific components.
 */
$this->load->view('finance/desktop/_styles');
?>
<style>
/* ===== REPORT FILTER CARD ===== */
.mp-report-filter{
  background:var(--mp-surface)!important;
  border:1px solid var(--mp-border)!important;
  border-radius:16px!important;
  box-shadow:var(--mp-shadow-sm)!important;
  overflow:hidden!important;
  margin-bottom:24px!important;
  width:100%!important;
  box-sizing:border-box!important;
}
.mp-report-filter .mp-card-head{
  display:flex!important;
  align-items:center!important;
  justify-content:space-between!important;
  padding:18px 20px 14px!important;
  border-bottom:1px solid var(--mp-border)!important;
}
.mp-report-filter .mp-card-head h3{
  font-size:15px!important;
  font-weight:700!important;
  margin:0!important;
  color:var(--mp-text)!important;
}
.mp-report-filter .mp-card-body{padding:24px!important;width:100%!important;box-sizing:border-box!important}
.mp-report-filter .mp-form-grid{
  display:grid!important;
  grid-template-columns:repeat(auto-fit,minmax(220px,1fr))!important;
  gap:20px 24px!important;
  width:100%!important;
}
.mp-report-filter .mp-form-group{
  display:flex!important;
  flex-direction:column!important;
  gap:6px!important;
  min-width:0!important;
}
.mp-report-filter .mp-form-group.full{grid-column:1/-1!important}
.mp-report-filter .mp-form-group>label{
  font-size:13px!important;
  font-weight:600!important;
  color:var(--mp-ink)!important;
  margin:0!important;
  white-space:nowrap!important;
}
.mp-report-filter select,
.mp-report-filter input[type="text"],
.mp-report-filter input[type="date"],
.mp-report-filter .form-control{
  width:100%!important;
  min-height:42px!important;
  padding:10px 14px!important;
  border:1px solid var(--mp-border)!important;
  border-radius:10px!important;
  background:var(--mp-surface)!important;
  color:var(--mp-ink)!important;
  font-size:14px!important;
  font-weight:500!important;
  font-family:inherit!important;
  box-shadow:none!important;
}
.mp-report-filter .form-control:focus,
.mp-report-filter select:focus,
.mp-report-filter input:focus{
  outline:none!important;
  border-color:var(--mp-primary)!important;
  box-shadow:0 0 0 3px rgba(0,87,255,.1)!important;
}
.mp-report-filter .input-group{display:flex!important;width:100%!important}
.mp-report-filter .input-group .input-group-addon{
  display:flex!important;
  align-items:center!important;
  justify-content:center!important;
  padding:0 14px!important;
  background:var(--mp-bg)!important;
  border:1px solid var(--mp-border)!important;
  border-right:none!important;
  border-radius:10px 0 0 10px!important;
  color:var(--mp-muted)!important;
  cursor:pointer!important;
  flex-shrink:0!important;
}
.mp-report-filter .input-group .form-control{
  border-radius:0 10px 10px 0!important;
  border-left:none!important;
}
.mp-report-filter .form-horizontal .form-group{
  margin:0!important;
  display:flex!important;
  flex-direction:column!important;
  gap:6px!important;
}
.mp-report-filter .form-horizontal .control-label{
  font-size:13px!important;
  font-weight:600!important;
  color:var(--mp-ink)!important;
  text-align:left!important;
  padding:0!important;
}
.mp-report-filter .checkbox input[type="checkbox"]{
  width:auto!important;
  min-height:auto!important;
  margin-right:8px!important;
}
.mp-report-filter .checkbox-inline,
.mp-report-filter .checkbox label{
  display:inline-flex!important;
  align-items:center!important;
  font-size:14px!important;
  color:var(--mp-ink)!important;
  cursor:pointer!important;
}
.mp-report-filter-actions{
  display:flex!important;
  gap:12px!important;
  flex-wrap:wrap!important;
  margin-top:8px!important;
}
.mp-report-filter-actions .mp-btn-primary{
  background:var(--mp-primary)!important;
  border:1px solid var(--mp-primary)!important;
  color:#fff!important;
  border-radius:10px!important;
  padding:11px 22px!important;
  font-size:14px!important;
  font-weight:600!important;
  cursor:pointer!important;
  transition:all .15s ease!important;
}
.mp-report-filter-actions .mp-btn-primary:hover{background:var(--mp-primary-dark)!important}
.mp-report-filter-actions .mp-btn-secondary{
  background:var(--mp-surface)!important;
  border:1px solid var(--mp-border)!important;
  color:var(--mp-ink)!important;
  border-radius:10px!important;
  padding:11px 22px!important;
  font-size:14px!important;
  font-weight:600!important;
  cursor:pointer!important;
  transition:all .15s ease!important;
  text-decoration:none!important;
}
.mp-report-filter-actions .mp-btn-secondary:hover{background:var(--mp-bg)!important}

/* Generic reusable buttons */
.mp-btn-primary,.mp-btn-secondary{
  display:inline-flex!important;
  align-items:center!important;
  gap:8px!important;
  border-radius:10px!important;
  padding:10px 20px!important;
  font-size:14px!important;
  font-weight:600!important;
  cursor:pointer!important;
  transition:all .15s ease!important;
  text-decoration:none!important;
  border:1px solid transparent!important;
  line-height:1!important;
}
.mp-btn-primary{
  background:var(--mp-primary)!important;
  color:#fff!important;
  border-color:var(--mp-primary)!important;
}
.mp-btn-primary:hover{background:var(--mp-primary-dark)!important}
.mp-btn-secondary{
  background:var(--mp-surface)!important;
  color:var(--mp-ink)!important;
  border-color:var(--mp-border)!important;
}
.mp-btn-secondary:hover{background:var(--mp-bg)!important}

/* Daterangepicker trigger button should match form inputs */
.mp-report-filter #pl-daterange-btn{
  width:100%!important;
  min-height:42px!important;
  padding:10px 14px!important;
  border:1px solid var(--mp-border)!important;
  border-radius:10px!important;
  background:var(--mp-surface)!important;
  color:var(--mp-ink)!important;
  font-size:14px!important;
  font-weight:500!important;
  font-family:inherit!important;
  box-shadow:none!important;
  text-align:left!important;
  display:flex!important;
  align-items:center!important;
  justify-content:space-between!important;
  cursor:pointer!important;
}
.mp-report-filter #pl-daterange-btn:hover,
.mp-report-filter #pl-daterange-btn:focus{
  border-color:var(--mp-primary)!important;
  outline:none!important;
}

/* ===== REPORT RESULTS TABLE ===== */
.mp-report-results{
  background:var(--mp-surface)!important;
  border:1px solid var(--mp-border)!important;
  border-radius:16px!important;
  box-shadow:var(--mp-shadow-sm)!important;
  overflow:hidden!important;
  width:100%!important;
  box-sizing:border-box!important;
}
.mp-report-results .mp-card-head{
  display:flex!important;
  align-items:center!important;
  justify-content:space-between!important;
  padding:18px 20px 14px!important;
  border-bottom:1px solid var(--mp-border)!important;
  flex-wrap:wrap!important;
  gap:12px!important;
}
.mp-report-results .mp-card-head h3{
  font-size:15px!important;
  font-weight:700!important;
  margin:0!important;
  color:var(--mp-text)!important;
}
.mp-report-results .box-body{padding:0!important;width:100%!important}
[id^="report-data"],
[id^="report-data"].table,
[id^="report-data"].table-bordered,
[id^="report-data"].table-hover{
  width:100%!important;
  border-collapse:collapse!important;
  border-spacing:0!important;
  font-size:13px!important;
  background:transparent!important;
  border:none!important;
  margin:0!important;
}
[id^="report-data"] thead,
[id^="report-data"] thead tr{
  background:transparent!important;
}
[id^="report-data"] thead .bg-blue{
  background:transparent!important;
  color:var(--mp-muted)!important;
}
[id^="report-data"] thead .bg-blue th{
  background:var(--mp-bg)!important;
  color:var(--mp-muted)!important;
  border-bottom:1px solid var(--mp-border)!important;
}
[id^="report-data"] thead th{
  background:var(--mp-bg)!important;
  font-size:11px!important;
  font-weight:700!important;
  color:var(--mp-muted)!important;
  text-transform:uppercase!important;
  letter-spacing:.05em!important;
  padding:14px 16px!important;
  border:none!important;
  border-bottom:1px solid var(--mp-border)!important;
  white-space:nowrap!important;
}
[id^="report-data"] tbody td{
  padding:12px 16px!important;
  border:none!important;
  border-bottom:1px solid var(--mp-border)!important;
  color:var(--mp-ink)!important;
  background:transparent!important;
  vertical-align:middle!important;
}
[id^="report-data"] tbody tr:last-child td{border-bottom:none!important}
[id^="report-data"] tbody tr:hover,
[id^="report-data"] tbody tr:hover td{background:var(--mp-bg)!important}
[id^="report-data"] tfoot td,
[id^="report-data"] tfoot th{
  background:var(--mp-bg)!important;
  font-weight:700!important;
  padding:12px 16px!important;
  border:none!important;
  border-top:2px solid var(--mp-border)!important;
}
[id^="report-data"] .amt{font-weight:700!important;font-variant-numeric:tabular-nums!important}
[id^="report-data"] .text-center{text-align:center!important}
[id^="report-data"] .text-right{text-align:right!important}

/* Remove outer AdminLTE box borders that can peek through */
.mp-report-results .box-body > .table,
.mp-report-results .box-body > .table-bordered{
  border:none!important;
  margin:0!important;
}
.mp-report-results .mp-dt-scroll{
  border-radius:0 0 16px 16px!important;
  overflow-x:auto!important;
}

/* Export and utility buttons */
.mp-report-export .btn-group,
.mp-report-actions{display:flex!important;gap:8px!important;flex-wrap:wrap!important;align-items:center!important}
.mp-report-actions .btn{
  border:1px solid var(--mp-border)!important;
  border-radius:10px!important;
  background:var(--mp-surface)!important;
  color:var(--mp-ink)!important;
  padding:8px 14px!important;
  font-size:13px!important;
  font-weight:600!important;
  cursor:pointer!important;
}
.mp-report-actions .btn:hover{background:var(--mp-bg)!important}

/* Ensure old AdminLTE helper classes work */
.text-danger{color:var(--mp-danger)!important}
.text-success{color:var(--mp-success)!important}
.text-primary{color:var(--mp-primary)!important}
.text-warning{color:var(--mp-warning)!important}
.bg-blue{background:var(--mp-primary)!important;color:#fff!important}
.bg-green{background:var(--mp-success)!important;color:#fff!important}
.bg-red{background:var(--mp-danger)!important;color:#fff!important}
.bg-orange{background:var(--mp-pay)!important;color:#fff!important}
[id^="report-data"] .bg-gray{background:var(--mp-bg)!important}

/* Empty state */
.mp-empty-state{
  text-align:center!important;
  padding:56px 24px!important;
  display:flex!important;
  flex-direction:column!important;
  align-items:center!important;
  justify-content:center!important;
}
.mp-empty-state .mp-empty-icon{
  width:80px!important;
  height:80px!important;
  border-radius:50%!important;
  background:rgba(5,150,105,.1)!important;
  color:var(--mp-success)!important;
  display:inline-flex!important;
  align-items:center!important;
  justify-content:center!important;
  font-size:34px!important;
  margin-bottom:20px!important;
}
.mp-empty-state h3{
  font-size:20px!important;
  font-weight:700!important;
  color:var(--mp-text)!important;
  margin:0 0 8px!important;
}
.mp-empty-state p{
  font-size:14px!important;
  color:var(--mp-muted)!important;
  margin:0 0 24px!important;
  max-width:420px!important;
}

/* Access denied / locked state */
.mp-access-denied{
  text-align:center!important;
  padding:64px 24px!important;
  display:flex!important;
  flex-direction:column!important;
  align-items:center!important;
  justify-content:center!important;
}
.mp-access-denied .mp-access-denied-icon{
  width:88px!important;
  height:88px!important;
  border-radius:50%!important;
  background:rgba(220,38,38,.08)!important;
  color:var(--mp-danger)!important;
  display:inline-flex!important;
  align-items:center!important;
  justify-content:center!important;
  font-size:38px!important;
  margin-bottom:24px!important;
}
.mp-access-denied h3{
  font-size:22px!important;
  font-weight:700!important;
  color:var(--mp-text)!important;
  margin:0 0 10px!important;
}
.mp-access-denied p{
  font-size:14px!important;
  color:var(--mp-muted)!important;
  margin:0 0 28px!important;
  max-width:460px!important;
}
.mp-access-denied-actions{
  display:flex!important;
  gap:12px!important;
  flex-wrap:wrap!important;
  justify-content:center!important;
}

/* Loading overlay */
.mp-report-results .overlay{
  position:absolute!important;
  top:0!important;
  left:0!important;
  right:0!important;
  bottom:0!important;
  background:rgba(255,255,255,.75)!important;
  display:flex!important;
  align-items:center!important;
  justify-content:center!important;
  z-index:10!important;
  border-radius:16px!important;
}
.mp-report-results{position:relative!important}

/* Responsive */
@media(max-width:768px){
  .mp-report-filter .mp-form-grid{grid-template-columns:1fr!important}
  .mp-report-filter .mp-form-group{grid-column:1/-1!important}
  .mp-report-filter-actions .mp-btn-primary,
  .mp-report-filter-actions .mp-btn-secondary{width:100%!important;justify-content:center!important}
  [id^="report-data"] th,
  [id^="report-data"] td{white-space:nowrap!important}
}
</style>
