<?php
$this->load->view('admin/desktop/_styles');

$CI =& get_instance();
$store_name = $this->session->userdata('store_name') ?: 'MartPoint';
$export_columns = [1,2,3,4,5,6,7];
$export_columns_json = json_encode($export_columns);
$non_sortable = json_encode([0,8]);
?>

<style>
.it-table-wrap{background:var(--mp-surface);border:1px solid var(--mp-border);border-radius:16px;overflow:visible;width:100%;box-sizing:border-box;box-shadow:var(--mp-shadow-sm)}
.it-table-wrap .box-body{padding:0;overflow:visible}
#package_table{font-size:13px!important;width:100%!important;border-collapse:collapse!important}

/* Status labels → mp-pill style override (controller emits legacy .label classes) */
#package_table .label{display:inline-flex!important;align-items:center!important;gap:4px!important;font-size:11px!important;font-weight:700!important;padding:3px 10px!important;border-radius:6px!important;border:none!important;text-transform:capitalize!important}
#package_table .label.label-success{background:rgba(5,150,105,.1)!important;color:var(--mp-success)!important}
#package_table .label.label-danger{background:rgba(220,38,38,.1)!important;color:var(--mp-danger)!important}
#package_table .label.label-info{background:rgba(0,87,255,.1)!important;color:var(--mp-primary)!important}
#package_table .label.label-warning{background:rgba(245,158,11,.1)!important;color:var(--mp-warning)!important}

/* Action dropdown */
#package_table .btn-group .btn{border:1px solid var(--mp-border)!important;border-radius:8px!important;background:var(--mp-surface)!important;color:var(--mp-ink)!important;padding:6px 12px!important;font-size:12px!important;font-weight:600!important;cursor:pointer!important}
#package_table .btn-group .btn:hover{background:var(--mp-bg)!important}
#package_table .dropdown-menu{border-radius:10px!important;border:1px solid var(--mp-border)!important;box-shadow:var(--mp-shadow)!important;padding:6px!important;min-width:160px!important}
#package_table .dropdown-menu > li > a{padding:9px 12px!important;font-size:13px!important;color:var(--mp-ink)!important;border-radius:6px!important;display:flex!important;align-items:center!important;gap:8px!important}
#package_table .dropdown-menu > li > a:hover{background:var(--mp-bg)!important;color:var(--mp-primary)!important}
</style>

<div class="mp-section">
  <?php $this->load->view('comman/code_flashdata'); ?>
</div>

<!-- Page Header -->
<div class="mp-section">
  <div class="mp-page-head">
    <div>
      <h2><?= $page_title; ?></h2>
      <div class="mp-page-sub"><?= htmlspecialchars($store_name); ?> &mdash; Bundle services and products into sellable packages</div>
    </div>
    <div style="display:flex;gap:10px;flex-wrap:wrap;">
      <?php if($CI->permissions('service_packages_add')): ?>
      <a class="mp-qa-btn blue" href="<?php echo base_url('service_packages/add'); ?>">
        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New Package
      </a>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Table -->
<div class="mp-section">
  <div class="it-table-wrap">
    <div class="box-body">
      <table id="package_table" class="table" width="100%">
        <thead>
          <tr>
            <th>#</th>
            <th>Code</th>
            <th>Package Name</th>
            <th>Pricing</th>
            <th>Price</th>
            <th>Type</th>
            <th>Expiry</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>


<script>
var csrfName = "<?= $this->security->get_csrf_token_name(); ?>";
var csrfHash = "<?= $this->security->get_csrf_hash(); ?>";

$(document).ready(function() {
  var table = $('#package_table').DataTable({
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
    "ajax": {
      url: "<?php echo base_url('service_packages/ajax_list'); ?>",
      type: "POST",
      data: function(d){ d[csrfName] = (typeof window.csrfHash !== 'undefined') ? window.csrfHash : csrfHash; },
      complete: function(){ if (typeof window.csrfHash !== 'undefined') csrfHash = window.csrfHash; }
    },
    "columnDefs": [
      { "orderable": false, "targets": <?= $non_sortable; ?> },
      { "className": "text-center", "targets": [0, 3, 5, 6, 7, 8] }
    ],
    "order": [[1, 'asc']]
  });
});

function update_status(id, status) {
  var data = { id: id, status: status };
  data[csrfName] = (typeof window.csrfHash !== 'undefined') ? window.csrfHash : csrfHash;
  $.post("<?php echo base_url('service_packages/update_status'); ?>", data, function(result) {
    if(result == 'success') {
      toastr['success']('Status updated.');
      $('#package_table').DataTable().ajax.reload();
    } else {
      toastr['error']('Failed to update status.');
    }
  });
}

function delete_package(id) {
  if(confirm("Are you sure?\nYou won't be able to revert this!")) {
    var data = { q_id: id };
    data[csrfName] = (typeof window.csrfHash !== 'undefined') ? window.csrfHash : csrfHash;
    $.post("<?php echo base_url('service_packages/delete_package'); ?>", data, function(result) {
      if(result == 'success') {
        toastr['success']('Package deleted successfully.');
        $('#package_table').DataTable().ajax.reload();
      } else {
        toastr['error']('Failed to delete package.');
      }
    });
  }
}
</script>
<script>$('.service-packages-list-active-li,.service_packages-active-li').addClass('active');</script>
