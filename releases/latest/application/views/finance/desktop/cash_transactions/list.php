<?php $this->load->view('finance/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Sales, purchase and expense payments</div>
  </div>
</div>

<div class="mp-table-wrap">
  <div class="box-body">
    <?= form_open('#', array('class' => '', 'id' => 'table_form')); ?>
    <input type="hidden" id="base_url" value="<?= htmlspecialchars($base_url); ?>">
    <?php $this->load->view('comman/code_flashdata.php'); ?>

    <div class="row" style="padding:16px 20px;border-bottom:1px solid var(--mp-border);display:flex;gap:16px;flex-wrap:wrap;align-items:flex-end;">
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
      <div class="col-md-3 col-sm-6" style="min-width:200px;">
        <label><?= $this->lang->line('users'); ?></label>
        <select class="form-control select2 mp-form-control" id="users" name="users" style="width:100%;">
          <?= get_users_select_list($this->session->userdata("role_id"), get_current_store_id()); ?>
        </select>
      </div>
    </div>

    <div class="mp-dt-scroll">
      <table id="example2" class="table mp-dt-table custom_hover" width="100%">
        <thead>
          <tr>
            <th><?= $this->lang->line('date'); ?></th>
            <th><?= $this->lang->line('payment_code'); ?></th>
            <th><?= $this->lang->line('payment_type'); ?></th>
            <th><?= $this->lang->line('payment'); ?></th>
            <th><?= $this->lang->line('note'); ?></th>
            <th><?= $this->lang->line('created_by'); ?></th>
            <th><?= $this->lang->line('account'); ?></th>
            <th><?= $this->lang->line('action'); ?></th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
    <?= form_close(); ?>
  </div>
</div>

<?php $this->load->view('modals/modal_account_link'); ?>

<script type="text/javascript">
function load_datatable(){
   var table = $('#example2').DataTable({
      "aLengthMenu": [[10, 25, 50, 100, 500], [10, 25, 50, 100, 500]],
      dom:'<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
      buttons: {
        buttons: [
            { extend: 'copy', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [0,1,2,3,4,5,6]} },
            { extend: 'excel', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [0,1,2,3,4,5,6]} },
            { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [0,1,2,3,4,5,6]} },
            { extend: 'print', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [0,1,2,3,4,5,6]} },
            { extend: 'csv', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [0,1,2,3,4,5,6]} },
            { extend: 'colvis', className: 'btn bg-teal color-palette btn-flat', text:'Columns' }
        ]
      },
      "processing": true,
      "serverSide": true,
      "order": [],
      "responsive": false,
      "searching": false,
      language: { processing: '<div class="text-primary bg-primary" style="position: relative;z-index:100;overflow: visible;">Processing...</div>' },
      "ajax": {
          "url": "<?= base_url('cash_transactions/ajax_list'); ?>",
          "type": "POST",
          "data": {
              from_date: $("#from_date").val(),
              to_date: $("#to_date").val(),
              users: $("#users").val()
          },
          complete: function (data) {
           $('.column_checkbox').iCheck({ checkboxClass: 'icheckbox_square-orange', radioClass: 'iradio_square-orange', increaseArea: '10%' });
           call_code();
          }
      },
      "columnDefs": [
        { "targets": [0,1,2,3,4,5,6,7], "orderable": false },
        { "targets": [0], "className": "text-center" },
        { "targets": [3], "className": "text-right" }
      ]
  });
}
$(document).ready(function(){ load_datatable(); });
$("#from_date,#to_date,#users").on("change",function(){
  $('#example2').DataTable().destroy();
  load_datatable();
});

function link_account(account_of,rec_id,prev_acc_id=''){
  if(account_of==0){
    toastr["warning"]("Can't Link");return;
  }
  $('#account-link-modal').modal('toggle');
  $("#account_id").val(prev_acc_id).select2();
  $("#prev_acc_id").val(prev_acc_id);
  $("#account_of").val(account_of);
  $("#rec_id").val(rec_id);
}

function update_account_link(){
  var base_url=$("#base_url").val();
  var flag=true;
  function check_field(id){
      if(!$("#"+id).val()){
          $('#'+id+'_msg').fadeIn(200).show().html('Required Field').addClass('required');
          flag=false;
      } else {
          $('#'+id+'_msg').fadeOut(200).hide();
      }
  }
  check_field("account_id");
  var account_id = $("#account_id").val();
  var account_of = $("#account_of").val();
  var rec_id = $("#rec_id").val();
  var prev_acc_id = $("#prev_acc_id").val();

  if(account_of==0 || account_id=='' || rec_id==''){
      toastr["error"]("Account ID or Record ID missed!!");return;
  }
  if(prev_acc_id==account_id){
     toastr["error"]("This Account Already Assigned!!");return;
  }
  $(".box").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
  $(".payment_save").attr('disabled',true);
  $.post(base_url+'cash_transactions/link_account', {account_of:account_of,account_id:account_id,rec_id:rec_id}, function(result) {
      if(result=="success"){
          $("#account_id").val('');
          $("#rec_id").val('');
          $('#account-link-modal').modal('toggle');
          toastr["success"]("Record Updated Successfully!");
          success.currentTime = 0;
          success.play();
          $('#example2').DataTable().ajax.reload();
      } else if(result=="failed"){
          toastr["error"]("Sorry! Failed to Update Record.Try again!");
          failed.currentTime = 0;
          failed.play();
      } else {
          toastr["error"](result);
          failed.currentTime = 0;
          failed.play();
      }
      $(".payment_save").attr('disabled',false);
      $(".overlay").remove();
  });
}
</script>
<script>
  $('.mp-nav-item').removeClass('active');
  $('.cash_transactions-active-li').addClass('active');
  $('.cash_transactions-active-li').closest('.mp-nav-group').addClass('open');
</script>
