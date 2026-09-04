<?php
/**
 * Shared CSS + CSRF bootstrap for Finance desktop content views.
 * Include at the top of each view: <?php $this->load->view('finance/desktop/_styles'); ?>
 */
?>
<script>
// Set up CSRF token for ALL AJAX calls BEFORE any DataTables init runs.
// mp_footer.php's code_js.php loads after content, so $.ajaxSetup isn't ready yet.
if(typeof jQuery !== 'undefined'){
  jQuery.ajaxSetup({
    data: {
      '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>'
    }
  });
}
</script>
<style>
/* ===== MODERN FORMS ===== */
.mp-card-form{background:var(--mp-surface)!important;border:1px solid var(--mp-border)!important;border-radius:16px!important;box-shadow:var(--mp-shadow-sm)!important;overflow:hidden!important;margin-bottom:24px!important}
.mp-card-form .mp-card-head{display:flex!important;align-items:center!important;justify-content:space-between!important;padding:18px 20px 14px!important;border-bottom:1px solid var(--mp-border)!important}
.mp-card-form .mp-card-head h3{font-size:15px!important;font-weight:700!important;margin:0!important;color:var(--mp-text)!important}
.mp-card-form .mp-card-body{padding:24px!important}
.mp-card-form .mp-card-foot{padding:16px 20px!important;border-top:1px solid var(--mp-border)!important;display:flex!important;align-items:center!important;gap:12px!important}

.mp-form-grid{display:grid!important;grid-template-columns:minmax(0,1fr) minmax(0,1fr)!important;gap:20px 24px!important}
.mp-form-grid .mp-form-group.full{grid-column:1/-1!important}
.mp-form-group{display:flex!important;flex-direction:column!important;gap:6px!important}
.mp-form-group>label{font-size:13px!important;font-weight:600!important;color:var(--mp-ink)!important;margin:0!important}
.mp-form-group>label .text-danger{color:var(--mp-danger)!important}
.mp-form-hint{font-size:12px!important;color:var(--mp-muted)!important;margin:0!important}

.mp-form-control{width:100%!important;padding:11px 14px!important;border:1px solid var(--mp-border)!important;border-radius:10px!important;background:var(--mp-surface)!important;color:var(--mp-ink)!important;font-size:14px!important;font-weight:500!important;font-family:inherit!important;transition:all .15s ease!important;box-shadow:none!important}
.mp-form-control:focus{outline:none!important;border-color:var(--mp-primary)!important;box-shadow:0 0 0 3px rgba(0,87,255,.1)!important}
.mp-form-control[readonly]{background:var(--mp-bg)!important;color:var(--mp-muted)!important}
select.mp-form-control{cursor:pointer!important;-webkit-appearance:none!important;-moz-appearance:none!important;appearance:none!important;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%2378716C' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' viewBox='0 0 24 24'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E")!important;background-repeat:no-repeat!important;background-position:right 12px center!important;padding-right:38px!important;height:42px!important;min-width:0!important;max-width:100%!important}
textarea.mp-form-control{min-height:80px!important;resize:vertical!important}

/* Select2 override */
.select2-container--default .select2-selection--single{border:1px solid var(--mp-border)!important;border-radius:10px!important;height:42px!important}
.select2-container--default .select2-selection--single .select2-selection__rendered{line-height:40px!important;color:var(--mp-ink)!important;font-size:14px!important}
.select2-container--default .select2-selection--multiple{border:1px solid var(--mp-border)!important;border-radius:10px!important;min-height:42px!important}
.select2-container--default .select2-selection--multiple .select2-selection__choice{background:var(--mp-bg)!important;border:1px solid var(--mp-border)!important;border-radius:6px!important;font-size:13px!important}

/* Date picker */
.input-group.date{width:100%!important}
.input-group.date .input-group-addon{cursor:pointer!important;background:var(--mp-surface)!important;border:1px solid var(--mp-border)!important;border-right:none!important;border-radius:10px 0 0 10px!important}
.input-group.date .form-control{border-left:none!important;border-radius:0 10px 10px 0!important}

/* ===== MODERN TABLES (DataTables override) ===== */
.mp-table-wrap{background:var(--mp-surface)!important;border:1px solid var(--mp-border)!important;border-radius:16px!important;overflow:visible!important;width:100%!important;box-sizing:border-box!important;box-shadow:var(--mp-shadow-sm)!important}
.mp-table-wrap .box-body{padding:0!important;overflow:visible!important}
.mp-dt-scroll{overflow-x:auto!important;width:100%!important;-webkit-overflow-scrolling:touch}

.mp-dt-table{font-size:13px!important;width:100%!important;border-collapse:collapse!important}
.mp-dt-table th{font-size:11px!important;text-transform:uppercase!important;font-weight:700!important;color:var(--mp-muted)!important;letter-spacing:.06em!important;border-bottom:1px solid var(--mp-border)!important;padding:14px 16px!important;white-space:nowrap!important;background:var(--mp-bg)!important;background-image:none!important}
.mp-dt-table td{padding:14px 16px!important;border-bottom:1px solid var(--mp-border)!important;color:var(--mp-text)!important;vertical-align:middle!important}
.mp-dt-table tr:last-child td{border-bottom:none!important}
.mp-dt-table tbody tr:hover{background:var(--mp-bg)!important}
.mp-dt-table .row-name{font-weight:600!important;color:var(--mp-ink)!important}
.mp-dt-table .row-meta{font-size:12px!important;color:var(--mp-muted)!important}
.mp-dt-table .amt{font-weight:700!important;font-variant-numeric:tabular-nums!important}

/* DataTables toolbar */
.dataTables_wrapper{font-family:'Inter',-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif!important;padding:0!important}
.dataTables_wrapper .row{margin:0!important;align-items:center!important}
.dataTables_wrapper .row:first-child{padding:16px 20px!important;border-bottom:1px solid var(--mp-border)!important}
.dataTables_wrapper .col-sm-12{padding:0!important}
.dataTables_wrapper .row:first-child .col-sm-12{display:flex!important;flex-direction:row!important;align-items:center!important;justify-content:space-between!important;gap:16px!important;flex-wrap:wrap!important}
.dataTables_length{font-size:13px!important;color:var(--mp-muted)!important;font-weight:500!important}
.dataTables_length label{display:flex!important;align-items:center!important;gap:8px!important;margin:0!important}
.dataTables_length select{border:1px solid var(--mp-border)!important;border-radius:8px!important;padding:6px 28px 6px 12px!important;height:36px!important;font-size:13px!important;font-weight:500!important;background:var(--mp-surface)!important;color:var(--mp-text)!important;cursor:pointer!important;appearance:none!important;background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' fill='none' stroke='%2378716C' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' viewBox='0 0 24 24'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E")!important;background-repeat:no-repeat!important;background-position:right 8px center!important}
.dataTables_filter{font-size:13px!important;color:var(--mp-muted)!important}
.dataTables_filter label{display:flex!important;align-items:center!important;gap:0!important;margin:0!important;position:relative!important;font-size:0!important}
.dataTables_filter label::after{content:''!important;position:absolute!important;left:12px!important;top:50%!important;transform:translateY(-50%)!important;width:16px!important;height:16px!important;background:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='none' stroke='%2378716C' stroke-width='2' stroke-linecap='round' stroke-linejoin='round' viewBox='0 0 24 24'%3E%3Ccircle cx='11' cy='11' r='8'/%3E%3Cline x1='21' y1='21' x2='16.65' y2='16.65'/%3E%3C/svg%3E") no-repeat center!important;pointer-events:none!important}
.dataTables_filter input{border:1px solid var(--mp-border)!important;border-radius:10px!important;padding:8px 14px 8px 36px!important;height:38px!important;font-size:13px!important;font-weight:500!important;background:var(--mp-surface)!important;color:var(--mp-text)!important;min-width:240px!important;transition:all .15s ease!important}
.dataTables_filter input:focus{outline:none!important;border-color:var(--mp-primary)!important;box-shadow:0 0 0 3px rgba(0,87,255,.1)!important}

/* Info + pagination footer row */
.dataTables_wrapper .row.mp-dt-footer{padding:14px 20px!important;align-items:center!important;border-top:1px solid var(--mp-border)!important;margin:0!important}
.dataTables_wrapper .row.mp-dt-footer .col-sm-5,.dataTables_wrapper .row.mp-dt-footer .col-sm-7{display:flex!important;align-items:center!important;padding:0!important}
.dataTables_info{font-size:13px!important;color:var(--mp-muted)!important;font-weight:500!important}
.dataTables_paginate{display:flex!important;gap:4px!important;justify-content:flex-end!important}
.dataTables_paginate .pagination{margin:0!important;display:flex!important;gap:4px!important}
.dataTables_paginate .paginate_button{border:1px solid var(--mp-border)!important;border-radius:8px!important;background:var(--mp-surface)!important;color:var(--mp-ink)!important;margin:0!important;padding:7px 12px!important;font-size:13px!important;font-weight:600!important;cursor:pointer!important;transition:all .15s ease!important;list-style:none!important}
.dataTables_paginate .paginate_button a{padding:0!important;color:inherit!important;text-decoration:none!important}
.dataTables_paginate .paginate_button:hover{background:var(--mp-bg)!important;color:var(--mp-ink)!important;border-color:var(--mp-border)!important}
.dataTables_paginate .paginate_button.current{background:var(--mp-primary)!important;color:#fff!important;border-color:var(--mp-primary)!important}
.dataTables_paginate .paginate_button.disabled{color:var(--mp-muted)!important;background:var(--mp-bg)!important;cursor:not-allowed!important;opacity:.6!important}

/* Export buttons */
.dt-buttons{display:flex!important;gap:6px!important;flex-wrap:wrap!important}
.dt-buttons .btn{border:1px solid var(--mp-border)!important;border-radius:8px!important;background:var(--mp-surface)!important;color:var(--mp-ink)!important;font-size:12px!important;font-weight:600!important;padding:7px 12px!important;cursor:pointer!important;transition:all .15s ease!important;text-decoration:none!important}
.dt-buttons .btn:hover{background:var(--mp-bg)!important;text-decoration:none!important}

/* Processing */
.dataTables_processing{background:var(--mp-surface)!important;border:1px solid var(--mp-border)!important;border-radius:8px!important;padding:12px 20px!important;font-size:13px!important;color:var(--mp-muted)!important;font-weight:500!important;box-shadow:var(--mp-shadow)!important}

/* ===== STATUS PILLS ===== */
.mp-dt-table .label,.mp-status-pill-inline{font-size:11px!important;font-weight:700!important;padding:5px 12px!important;border-radius:20px!important;display:inline-flex!important;align-items:center!important;gap:5px!important;min-width:64px!important;justify-content:center!important;letter-spacing:.02em!important}
.mp-dt-table .label-success,.mp-status-pill-inline.ok{background:rgba(5,150,105,.1)!important;color:var(--mp-success)!important}
.mp-dt-table .label-danger,.mp-status-pill-inline.danger{background:rgba(220,38,38,.1)!important;color:var(--mp-danger)!important}
.mp-dt-table .label-info,.mp-status-pill-inline.info{background:rgba(0,87,255,.1)!important;color:var(--mp-primary)!important}
.mp-dt-table .label-warning,.mp-status-pill-inline.warn{background:rgba(245,158,11,.1)!important;color:var(--mp-warning)!important}
.mp-dt-table .label-default,.mp-status-pill-inline.muted{background:var(--mp-bg)!important;color:var(--mp-muted)!important}
.mp-dt-table .label::before,.mp-status-pill-inline::before{content:''!important;width:6px!important;height:6px!important;border-radius:50%!important;background:currentColor!important}

/* ===== ACTION BUTTONS ===== */
.mp-dt-table .mp-actions{display:flex!important;gap:6px!important;align-items:center!important}
.mp-dt-table .mp-actions a,.mp-dt-table .mp-actions button{display:inline-flex!important;align-items:center!important;justify-content:center!important;width:32px!important;height:32px!important;border-radius:8px!important;border:1px solid var(--mp-border)!important;background:var(--mp-surface)!important;color:var(--mp-ink)!important;cursor:pointer!important;transition:all .15s ease!important;text-decoration:none!important;padding:0!important}
.mp-dt-table .mp-actions a:hover,.mp-dt-table .mp-actions button:hover{background:var(--mp-bg)!important;text-decoration:none!important}
.mp-dt-table .mp-actions .mp-edit:hover{border-color:var(--mp-primary)!important;color:var(--mp-primary)!important;background:rgba(0,87,255,.06)!important}
.mp-dt-table .mp-actions .mp-delete:hover{border-color:var(--mp-danger)!important;color:var(--mp-danger)!important;background:rgba(220,38,38,.06)!important}
.mp-dt-table .checkbox{width:18px!important;height:18px!important}

/* ===== MODERN STATIC TABLES ===== */
.mp-static-table{width:100%!important;border-collapse:collapse!important}
.mp-static-table th{text-align:left!important;font-size:11px!important;font-weight:700!important;color:var(--mp-muted)!important;text-transform:uppercase!important;letter-spacing:.04em!important;padding:12px 16px!important;border-bottom:1px solid var(--mp-border)!important;background:var(--mp-bg)!important}
.mp-static-table td{padding:14px 16px!important;font-size:13px!important;color:var(--mp-ink)!important;border-bottom:1px solid var(--mp-border)!important}
.mp-static-table tr:last-child td{border-bottom:none!important}
.mp-static-table tr:hover td{background:var(--mp-bg)!important}

/* ===== MODERN MODALS ===== */
.modal-content{border-radius:16px!important;border:1px solid var(--mp-border)!important;box-shadow:var(--mp-shadow)!important}
.modal-header{border-bottom:1px solid var(--mp-border)!important;padding:18px 20px!important}
.modal-header .modal-title{font-size:16px!important;font-weight:700!important;color:var(--mp-text)!important}
.modal-body{padding:20px!important}
.modal-footer{border-top:1px solid var(--mp-border)!important;padding:14px 20px!important}
.modal-footer .btn-default{border:1px solid var(--mp-border)!important;background:var(--mp-surface)!important;color:var(--mp-ink)!important;border-radius:10px!important;font-weight:600!important;padding:10px 18px!important}
.modal-footer .btn-primary{background:var(--mp-primary)!important;border:1px solid var(--mp-primary)!important;color:#fff!important;border-radius:10px!important;font-weight:600!important;padding:10px 18px!important}

/* ===== EMPTY STATE ===== */
.mp-empty-state{padding:48px 32px!important;text-align:center!important}
.mp-empty-state .mp-empty-icon{width:64px!important;height:64px!important;border-radius:16px!important;background:var(--mp-bg)!important;color:var(--mp-muted)!important;display:flex!important;align-items:center!important;justify-content:center!important;margin:0 auto 18px!important;font-size:28px!important}
.mp-empty-state h4{font-size:18px!important;font-weight:700!important;margin:0 0 6px!important;color:var(--mp-text)!important}
.mp-empty-state p{font-size:14px!important;color:var(--mp-muted)!important;margin:0!important}

/* ===== FORM ACTIONS ===== */
.mp-form-actions{display:flex!important;align-items:center!important;gap:12px!important;padding-top:8px!important}
.mp-form-actions .mp-btn-primary{background:var(--mp-primary)!important;border:1px solid var(--mp-primary)!important;color:#fff!important;border-radius:10px!important;font-weight:600!important;padding:11px 22px!important;font-size:14px!important;cursor:pointer!important;transition:all .15s ease!important}
.mp-form-actions .mp-btn-primary:hover{background:var(--mp-primary-dark)!important}
.mp-form-actions .mp-btn-secondary{border:1px solid var(--mp-border)!important;background:var(--mp-surface)!important;color:var(--mp-ink)!important;border-radius:10px!important;font-weight:600!important;padding:11px 22px!important;font-size:14px!important;cursor:pointer!important;transition:all .15s ease!important;text-decoration:none!important}
.mp-form-actions .mp-btn-secondary:hover{background:var(--mp-bg)!important;text-decoration:none!important}

/* ===== QUICK LINKS GRID ===== */
.mp-quick-grid{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(180px,1fr))!important;gap:12px!important}
.mp-quick-grid .mp-qa-btn{display:flex!important;justify-content:center!important;width:100%!important}

/* Finance detail cards */
.mp-kpi-grid{display:grid!important;grid-template-columns:repeat(auto-fit,minmax(220px,1fr))!important;gap:16px!important;margin-bottom:24px!important}
.mp-kpi-card{background:var(--mp-surface)!important;border:1px solid var(--mp-border)!important;border-radius:16px!important;padding:20px!important;box-shadow:var(--mp-shadow-sm)!important;position:relative!important;overflow:hidden!important}
.mp-kpi-card .mp-kpi-icon{width:40px!important;height:40px!important;border-radius:10px!important;display:flex!important;align-items:center!important;justify-content:center!important;margin-bottom:14px!important;font-size:18px!important}
.mp-kpi-card .mp-kpi-label{font-size:12px!important;color:var(--mp-muted)!important;font-weight:600!important;margin-bottom:4px!important}
.mp-kpi-card .mp-kpi-value{font-size:22px!important;font-weight:700!important;color:var(--mp-text)!important}
.mp-kpi-card.sales .mp-kpi-icon{background:rgba(0,87,255,.1)!important;color:var(--mp-primary)!important}
.mp-kpi-card.profit .mp-kpi-icon{background:rgba(5,150,105,.1)!important;color:var(--mp-success)!important}
.mp-kpi-card.expense .mp-kpi-icon{background:rgba(217,119,6,.1)!important;color:var(--mp-pay)!important}
.mp-kpi-card.debt .mp-kpi-icon{background:rgba(220,38,38,.1)!important;color:var(--mp-danger)!important}
.mp-kpi-card.cash .mp-kpi-icon{background:rgba(13,148,136,.1)!important;color:#0D9488!important}

@media(max-width:768px){
  .mp-form-grid{grid-template-columns:minmax(0,1fr)!important}
  .mp-quick-grid{grid-template-columns:minmax(0,1fr) minmax(0,1fr)!important}
  .mp-kpi-grid{grid-template-columns:minmax(0,1fr)!important}
  .dataTables_filter,.dataTables_length{width:100%!important;margin-bottom:0!important}
  .dataTables_filter input{width:100%!important;min-width:0!important}
  .dataTables_paginate{justify-content:center!important;flex-wrap:wrap!important}
}
</style>
