<?php
$this->load->view('admin/desktop/_styles');

$CI =& get_instance();
$store_name = $this->session->userdata('store_name') ?: 'MartPoint';
$export_columns = [1,2,3,4];
$export_columns_json = json_encode($export_columns);
$non_sortable = json_encode([0,5]);
?>

<style>
.it-table-wrap{background:var(--mp-surface);border:1px solid var(--mp-border);border-radius:16px;overflow:visible;width:100%;box-sizing:border-box;box-shadow:var(--mp-shadow-sm)}
.it-table-wrap .box-body{padding:0;overflow:visible}
#data-list-table{font-size:13px!important;width:100%!important;border-collapse:collapse!important}

/* Status labels → mp-pill style override (controller emits legacy .label classes) */
#data-list-table .label{display:inline-flex!important;align-items:center!important;gap:4px!important;font-size:11px!important;font-weight:700!important;padding:3px 10px!important;border-radius:6px!important;border:none!important;text-transform:capitalize!important}
#data-list-table .label.label-success{background:rgba(5,150,105,.1)!important;color:var(--mp-success)!important}
#data-list-table .label.label-danger{background:rgba(220,38,38,.1)!important;color:var(--mp-danger)!important}

/* Action dropdown */
#data-list-table .btn-group .btn{border:1px solid var(--mp-border)!important;border-radius:8px!important;background:var(--mp-surface)!important;color:var(--mp-ink)!important;padding:6px 12px!important;font-size:12px!important;font-weight:600!important;cursor:pointer!important}
#data-list-table .btn-group .btn:hover{background:var(--mp-bg)!important}
#data-list-table .dropdown-menu{border-radius:10px!important;border:1px solid var(--mp-border)!important;box-shadow:var(--mp-shadow)!important;padding:6px!important;min-width:160px!important}
#data-list-table .dropdown-menu > li > a{padding:9px 12px!important;font-size:13px!important;color:var(--mp-ink)!important;border-radius:6px!important;display:flex!important;align-items:center!important;gap:8px!important}
#data-list-table .dropdown-menu > li > a:hover{background:var(--mp-bg)!important;color:var(--mp-primary)!important}

.dataTables_wrapper .dt-buttons .delete_btn{background:rgba(220,38,38,.08)!important;color:var(--mp-danger)!important;border-color:rgba(220,38,38,.2)!important}
.dataTables_wrapper .dt-buttons .delete_btn:hover{background:var(--mp-danger)!important;color:#fff!important;border-color:var(--mp-danger)!important}
</style>

<div class="mp-section">
  <?php include "comman/code_flashdata.php"; ?>
</div>

<!-- Page Header -->
<div class="mp-section">
  <div class="mp-page-head">
    <div>
      <h2><?= $page_title; ?></h2>
      <div class="mp-page-sub"><?= htmlspecialchars($store_name); ?> &mdash; Manage product attributes (size, colour, material)</div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <?php if($CI->permissions('attributes_add')): ?>
      <a class="mp-qa-btn green" href="<?= base_url('attributes/add'); ?>">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        <?= $this->lang->line('attributes_add'); ?>
      </a>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Table -->
<div class="mp-section">
  <div class="it-table-wrap">
    <div class="box-body">
      <table class="table" id="data-list-table" width="100%">
        <thead>
        <tr>
          <th class="text-center"><input type="checkbox" id="select_all" class="group_check checkbox"></th>
          <th>Type</th>
          <th>Value</th>
          <th>Sort</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
        </thead>
        <tbody id="tbodyid"></tbody>
      </table>
    </div>
  </div>
</div>


<script type="text/javascript">
var base_url = "<?= $base_url; ?>";
var csrfName = "<?= $this->security->get_csrf_token_name(); ?>";
var csrfHash = "<?= $this->security->get_csrf_hash(); ?>";

$(document).ready(function(){
    $('#data-list-table').DataTable({
        "aLengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        dom:'<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
        buttons: {
            buttons: [
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
        "responsive": true,
        "order": [],
        "ajax": {
            url: base_url + "attributes/ajax_list",
            type: "POST",
            data: function(d){ d[csrfName] = csrfHash; },
            complete: function(){
                $('.column_checkbox').iCheck({ checkboxClass: 'icheckbox_square-orange', radioClass: 'iradio_square-orange' });
                if (typeof window.csrfHash !== 'undefined') csrfHash = window.csrfHash;
            }
        },
        "columnDefs": [{ "orderable": false, "targets": <?= $non_sortable; ?> }, { "targets": [0], "className": "text-center" }],
        "drawCallback": function(){
            $('.column_checkbox').iCheck({ checkboxClass: 'icheckbox_square-orange', radioClass: 'iradio_square-orange' });
        }
    });
    $("#select_all").on("ifChanged ifClicked", function(){
        $(".column_checkbox").iCheck(this.checked ? 'check' : 'uncheck');
    });
});

function delete_attribute(id){
    if(!confirm("Delete this attribute?")) return;
    var data = {q_id: id};
    data[csrfName] = (typeof window.csrfHash !== 'undefined') ? window.csrfHash : csrfHash;
    $.post(base_url + "attributes/delete", data, function(res){
        if(res.indexOf("success") !== -1){ $('#data-list-table').DataTable().ajax.reload(); }
        else { toastr["error"](res); }
    });
}
</script>
<script>$('.attributes-active-li').addClass('active');</script>
