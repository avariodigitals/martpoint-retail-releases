<?php $this->load->view('finance/desktop/_styles'); ?>
<?php
if(isset($account_id)){
    $q2 = $this->db->query("select * from ac_accounts where id=$account_id");
    $account_code = $q2->row()->account_code;
    $created_date = show_date($q2->row()->created_date);
    $account_name = $q2->row()->account_name;
    $balance = store_number_format($q2->row()->balance);
} else {
    $account_code = $account_name = $created_date = '';
    $balance = store_number_format(0);
}
?>
<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Account ledger for <?= htmlspecialchars($account_name); ?></div>
  </div>
  <a href="<?= base_url('accounts'); ?>" class="mp-qa-btn blue"><i class="fa fa-arrow-left"></i> Back to Accounts</a>
</div>

<div class="mp-kpi-grid" style="grid-template-columns:repeat(4,1fr);">
  <div class="mp-kpi-card sales">
    <div class="mp-kpi-icon"><i class="fa fa-barcode"></i></div>
    <div class="mp-kpi-label">Account Code</div>
    <div class="mp-kpi-value"><?= htmlspecialchars($account_code); ?></div>
  </div>
  <div class="mp-kpi-card cash">
    <div class="mp-kpi-icon"><i class="fa fa-book"></i></div>
    <div class="mp-kpi-label">Account Name</div>
    <div class="mp-kpi-value" style="font-size:16px;"><?= htmlspecialchars($account_name); ?></div>
  </div>
  <div class="mp-kpi-card <?= floatval(str_replace(',','',$balance))<0 ? 'debt' : 'profit'; ?>">
    <div class="mp-kpi-icon"><i class="fa fa-balance-scale"></i></div>
    <div class="mp-kpi-label">Current Balance</div>
    <div class="mp-kpi-value"><?= $balance; ?></div>
  </div>
  <div class="mp-kpi-card expense">
    <div class="mp-kpi-icon"><i class="fa fa-calendar"></i></div>
    <div class="mp-kpi-label">Created Date</div>
    <div class="mp-kpi-value" style="font-size:16px;"><?= htmlspecialchars($created_date); ?></div>
  </div>
</div>

<div class="mp-table-wrap">
  <div class="box-body">
    <?= form_open('#', array('class' => '', 'id' => 'table_form')); ?>
    <input type="hidden" id="base_url" value="<?= htmlspecialchars($base_url); ?>">
    <div class="row" style="padding:16px 20px;border-bottom:1px solid var(--mp-border);display:flex;gap:16px;flex-wrap:wrap;">
      <div class="col-md-3 col-sm-6" style="min-width:200px;">
        <label><?= $this->lang->line('from_date'); ?></label>
        <div class="input-group date">
          <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
          <input type="text" class="form-control pull-right datepicker" id="from_date" name="from_date">
        </div>
      </div>
      <div class="col-md-3 col-sm-6" style="min-width:200px;">
        <label><?= $this->lang->line('to_date'); ?></label>
        <div class="input-group date">
          <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
          <input type="text" class="form-control pull-right datepicker" id="to_date" name="to_date">
        </div>
      </div>
    </div>
    <div class="mp-dt-scroll">
      <table id="example2" class="table mp-dt-table custom_hover" width="100%">
        <thead>
          <tr>
            <th><?= $this->lang->line('date'); ?></th>
            <th><?= $this->lang->line('description'); ?></th>
            <th><?= $this->lang->line('debit_amount'); ?></th>
            <th><?= $this->lang->line('credit_amount'); ?></th>
            <th><?= $this->lang->line('balance'); ?></th>
            <th><?= $this->lang->line('note'); ?></th>
            <th><?= $this->lang->line('created_by'); ?></th>
            <th><?= $this->lang->line('action'); ?></th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
    <?= form_close(); ?>
  </div>
</div>

<script type="text/javascript">
function load_datatable(){
   var table = $('#example2').DataTable({
      dom:'<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
      buttons: {
        buttons: [
            { className: 'btn bg-teal color-palette btn-flat', extend: 'copy', exportOptions: { columns: [0,1,2,3,4,5,6]} },
            { className: 'btn bg-teal color-palette btn-flat', extend: 'excel', exportOptions: { columns: [0,1,2,3,4,5,6]} },
            { className: 'btn bg-teal color-palette btn-flat', extend: 'pdf', exportOptions: { columns: [0,1,2,3,4,5,6]} },
            { className: 'btn bg-teal color-palette btn-flat', extend: 'print', exportOptions: { columns: [0,1,2,3,4,5,6]} },
            { className: 'btn bg-teal color-palette btn-flat', extend: 'csv', exportOptions: { columns: [0,1,2,3,4,5,6]} },
            { className: 'btn bg-teal color-palette btn-flat', extend: 'colvis', text:'Columns' }
        ]
      },
      "processing": true,
      "serverSide": true,
      "order": [],
      "responsive": false,
      "searching": false,
      language: { processing: '<div class="text-primary bg-primary" style="position: relative;z-index:100;overflow: visible;">Processing...</div>' },
      "ajax": {
          "url": "<?= base_url('account_transactions/ajax_list'); ?>",
          "type": "POST",
          "data": {
              account_id: '<?= $account_id; ?>',
              from_date: $("#from_date").val(),
              to_date: $("#to_date").val()
          },
          complete: function (data) {
           $('.column_checkbox').iCheck({ checkboxClass: 'icheckbox_square-orange', radioClass: 'iradio_square-orange', increaseArea: '10%' });
           call_code();
          }
      },
      "columnDefs": [
        { "targets": [0,1,2,3,4,5,6,7], "orderable": false },
        { "targets": [0], "className": "text-center" },
        { "targets": [2,3,4], "className": "text-right" }
      ]
  });
}
$(document).ready(function(){ load_datatable(); });
$("#from_date,#to_date").on("change",function(){
  $('#example2').DataTable().destroy();
  load_datatable();
});

function delete_transaction(q_id,entry_of)
{
    var base_url = $("#base_url").val();
    swal({
      title: "Delete Transaction?",
      text: "This will permanently delete the payment record. Continue?",
      icon: "warning",
      buttons: ["Cancel", "Delete"],
      dangerMode: true
    }).then(function(willDelete){
      if(willDelete) doDeleteTransaction(q_id, entry_of);
    });
}
function doDeleteTransaction(q_id, entry_of){
    var base_url = $("#base_url").val();
    $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
   $.post(base_url+"account_transactions/delete_transaction",{q_id:q_id,entry_of:entry_of},function(result){
     result=result;
     if(result=="success"){
          toastr["success"]("Record Deleted Successfully!");
          success.currentTime = 0;
          success.play();
          $('#example2').DataTable().ajax.reload();
     } else if(result=="failed"){
          toastr["error"]("Failed to Delete .Try again!");
          failed.currentTime = 0;
          failed.play();
     } else {
          toastr["error"](result);
          failed.currentTime = 0;
          failed.play();
     }
     $(".overlay").remove();
     return false;
   });
}
</script>
<script>
  $('.mp-nav-item').removeClass('active');
  $('.accounts_list-active-li').addClass('active');
  $('.accounts_list-active-li').closest('.mp-nav-group').addClass('open');
</script>
