<?php $this->load->view('admin/desktop/_styles'); ?>

<?php
$CI =& get_instance();
$store_name = $this->session->userdata('store_name') ?: 'MartPoint';
$export_columns = [1,2,3];
$export_columns_json = json_encode($export_columns);
$non_sortable = json_encode([0,4]);
?>

<style>
/* Table wrapper */
.it-table-wrap{background:var(--mp-surface);border:1px solid var(--mp-border);border-radius:16px;overflow:visible;width:100%;box-sizing:border-box;box-shadow:var(--mp-shadow-sm)}
.it-table-wrap .box-body{padding:0;overflow:visible}
#example2{font-size:13px!important;width:100%!important;border-collapse:collapse!important}
#example2 .item-name{font-weight:600;color:var(--mp-ink)}

/* DataTables buttons (export) — delete button variant */
.dataTables_wrapper .dt-buttons .delete_btn{background:rgba(220,38,38,.08)!important;color:var(--mp-danger)!important;border-color:rgba(220,38,38,.2)!important}
.dataTables_wrapper .dt-buttons .delete_btn:hover{background:var(--mp-danger)!important;color:#fff!important;border-color:var(--mp-danger)!important}

/* Status labels → mp-pill style override (controller emits legacy .label classes) */
#example2 .label{display:inline-flex!important;align-items:center!important;gap:4px!important;font-size:11px!important;font-weight:700!important;padding:3px 10px!important;border-radius:6px!important;border:none!important;text-transform:capitalize!important}
#example2 .label.label-success{background:rgba(5,150,105,.1)!important;color:var(--mp-success)!important}
#example2 .label.label-danger{background:rgba(220,38,38,.1)!important;color:var(--mp-danger)!important}

/* Action dropdown */
#example2 .btn-group .btn{border:1px solid var(--mp-border)!important;border-radius:8px!important;background:var(--mp-surface)!important;color:var(--mp-ink)!important;padding:6px 12px!important;font-size:12px!important;font-weight:600!important;cursor:pointer!important}
#example2 .btn-group .btn:hover{background:var(--mp-bg)!important}
#example2 .dropdown-menu{border-radius:10px!important;border:1px solid var(--mp-border)!important;box-shadow:var(--mp-shadow)!important;padding:6px!important;min-width:160px!important}
#example2 .dropdown-menu > li > a{padding:9px 12px!important;font-size:13px!important;color:var(--mp-ink)!important;border-radius:6px!important;display:flex!important;align-items:center!important;gap:8px!important}
#example2 .dropdown-menu > li > a:hover{background:var(--mp-bg)!important;color:var(--mp-primary)!important}

@media (max-width:767px){
  #example2{font-size:12px!important}
}
</style>

<div class="mp-section">
  <?php include "comman/code_flashdata.php"; ?>
</div>

<!-- Page Header -->
<div class="mp-section">
  <div class="mp-page-head">
    <div>
      <h2><?= $page_title; ?></h2>
      <div class="mp-page-sub"><?= htmlspecialchars($store_name); ?> &mdash; Organise your product categories</div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <?php if($CI->permissions('items_category_add')): ?>
      <a class="mp-qa-btn green" href="<?php echo $base_url; ?>category/add">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        <?= $this->lang->line('create_category'); ?>
      </a>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Table -->
<div class="mp-section">
  <?= form_open('#', array('class' => '', 'id' => 'table_form')); ?>
  <input type="hidden" id='base_url' value="<?=$base_url;?>">

  <div class="it-table-wrap">
    <div class="box-body">
      <table id="example2" class="table custom_hover" width="100%">
        <thead>
        <tr>
          <th class="text-center">
            <input type="checkbox" class="group_check checkbox" >
          </th>
          <th><?= $this->lang->line('category_name'); ?></th>
          <th><?= $this->lang->line('description'); ?></th>
          <th><?= $this->lang->line('status'); ?></th>
          <th><?= $this->lang->line('action'); ?></th>
        </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>

  <?= form_close(); ?>
</div>

<script type="text/javascript">
  function load_datatable(){
    var table = $('#example2').DataTable({
        "aLengthMenu": [[10, 25, 50, 100, 500], [10, 25, 50, 100, 500]],
        dom:'<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
        buttons: {
            buttons: [
                {
                    className: 'btn bg-red color-palette btn-flat hidden delete_btn pull-left',
                    text: 'Delete',
                    action: function ( e, dt, node, config ) {
                        multi_delete();
                    }
                },
                { extend: 'copy',   className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: <?= $export_columns_json; ?> } },
                { extend: 'excel',  className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: <?= $export_columns_json; ?> } },
                { extend: 'pdf',    className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: <?= $export_columns_json; ?> } },
                { extend: 'print',  className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: <?= $export_columns_json; ?> } },
                { extend: 'csv',    className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: <?= $export_columns_json; ?> } },
                { extend: 'colvis', className: 'btn bg-teal color-palette btn-flat', text:'Columns' },
            ]
        },
        "processing": true,
        "serverSide": true,
        "order": [],
        "responsive": true,
        "ajax": {
            "url": "<?php echo site_url('category/ajax_list')?>",
            "type": "POST",
            complete: function (data) {
                $('.column_checkbox').iCheck({
                    checkboxClass: 'icheckbox_square-orange',
                    radioClass: 'iradio_square-orange'
                });
                call_code();
            },
        },
        "columnDefs": [
            { "targets": <?= $non_sortable; ?>, "orderable": false },
            { "targets": [0], "className": "text-center" },
        ],
    });
  }
$(document).ready(function() {
   load_datatable();
});
</script>

<!-- Make sidebar menu highlighter/selector -->
<script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
