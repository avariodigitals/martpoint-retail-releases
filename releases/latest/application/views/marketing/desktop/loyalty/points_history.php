<?php $this->load->view('marketing/desktop/_styles'); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title ?? 'Points History'); ?></h2>
    <div class="mp-page-sub">Customer Points Transaction Log</div>
  </div>
</div>

<div class="mp-table-wrap">
  <div class="box-body">
    <table id="example2" class="table mp-dt-table custom_hover" width="100%">
      <thead>
        <tr>
          <th>#</th><th>Customer</th><th>Type</th><th>Points</th><th>Balance</th><th>Description</th><th>Date</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<script>
function load_datatable(){
    var table = $('#example2').DataTable({
        "aLengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        dom:'<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10"B>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
        buttons: [
            { extend: 'copy', className: 'btn bg-teal color-palette btn-flat' },
            { extend: 'excel', className: 'btn bg-teal color-palette btn-flat' },
            { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat' },
            { extend: 'csv', className: 'btn bg-teal color-palette btn-flat' },
        ],
        "processing": true,
        "serverSide": true,
        "order": [],
        "responsive": true,
        "ajax": {
            "url": "<?= site_url('loyalty/ajax_points_history'); ?>",
            "type": "POST",
            "data": function(d){
                d['customer_id'] = "<?= htmlspecialchars($customer_id ?? ''); ?>";
                d['<?= $this->security->get_csrf_token_name(); ?>'] = '<?= $this->security->get_csrf_hash(); ?>';
            }
        },
        "columnDefs": [{ "targets": [0], "orderable": false, }]
    });
}
$(document).ready(function() { load_datatable(); });
$(".loyalty-points-history-active-li").addClass("active").closest(".mp-nav-group").addClass("open");
</script>
