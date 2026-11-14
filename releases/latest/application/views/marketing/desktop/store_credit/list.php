<?php $this->load->view('marketing/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title ?? 'Store Credit'); ?></h2>
    <div class="mp-page-sub">View and issue customer store credit</div>
  </div>
  <?php if($CI->permissions('store_credit_add')) { ?>
  <a class="mp-qa-btn blue" href="<?= base_url('store_credit/add'); ?>"><i class="fa fa-plus"></i> Issue Store Credit</a>
  <?php } ?>
</div>

<div class="mp-table-wrap">
  <div class="box-body">
    <table id="example2" class="table mp-dt-table custom_hover" width="100%">
      <thead>
        <tr>
          <th>#</th>
          <th>Code</th>
          <th>Customer</th>
          <th>Amount</th>
          <th>Balance</th>
          <th>Source</th>
          <th>Expiry</th>
          <th>Status</th>
          <th>Action</th>
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
        dom:'<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
        "processing": true,
        "serverSide": true,
        "order": [],
        "responsive": true,
        "ajax": {
            "url": "<?=site_url('store_credit/ajax_list')?>",
            "type": "POST",
            "data": function(d){
                d['<?= $this->security->get_csrf_token_name(); ?>'] = '<?= $this->security->get_csrf_hash(); ?>';
            }
        },
        "columnDefs": [{ "targets": [0,8], "orderable": false, }]
    });
}
$(document).ready(function() { load_datatable(); });
function cancel_credit(id){
    swal({
        title: "Are you sure?",
        text: "Cancel this store credit?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, cancel it!",
        cancelButtonText: "No",
        closeOnConfirm: true
    }, function(isConfirm){
        if(isConfirm){
            $.post(base_url + 'store_credit/cancel_credit', {id:id}, function(res){
                if(res=='success'){ success_show('Credit cancelled'); $('#example2').DataTable().ajax.reload(); }
                else{ error_show('Failed'); }
            });
        }
    });
}
$(".store-credit-active-li").addClass("active").closest(".mp-nav-group").addClass("open");
</script>
