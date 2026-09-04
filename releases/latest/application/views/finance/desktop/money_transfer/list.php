<?php $this->load->view('finance/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Transfer money between accounts</div>
  </div>
  <?php if($CI->permissions('money_transfer_add')) { ?>
  <a class="mp-qa-btn green" href="<?= base_url('money_transfer/add'); ?>">
    <i class="fa fa-plus"></i> <?= $this->lang->line('create_transfer'); ?>
  </a>
  <?php } ?>
</div>

<div class="mp-table-wrap">
  <div class="box-body">
    <?= form_open('#', array('class' => '', 'id' => 'table_form')); ?>
    <input type="hidden" id="base_url" value="<?= htmlspecialchars($base_url); ?>">
    <?php $this->load->view('comman/code_flashdata.php'); ?>

    <div class="row" style="padding:16px 20px;border-bottom:1px solid var(--mp-border);display:flex;gap:16px;flex-wrap:wrap;align-items:flex-end;">
      <div class="col-md-3 col-sm-6" style="min-width:200px;">
        <label><?= $this->lang->line('transfer_date'); ?></label>
        <div class="input-group date">
          <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
          <input type="text" class="form-control pull-right datepicker" id="transfer_date" name="transfer_date">
        </div>
      </div>
      <div class="col-md-3 col-sm-6" style="min-width:200px;">
        <label><?= $this->lang->line('debit_account'); ?></label>
        <select class="form-control select2 mp-form-control" id="debit_account_id" name="debit_account_id" style="width:100%;">
          <option value="">Select</option>
          <?= get_accounts_select_list(); ?>
        </select>
      </div>
      <div class="col-md-3 col-sm-6" style="min-width:200px;">
        <label><?= $this->lang->line('credit_account'); ?></label>
        <select class="form-control select2 mp-form-control" id="credit_account_id" name="credit_account_id" style="width:100%;">
          <option value="">Select</option>
          <?= get_accounts_select_list(); ?>
        </select>
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
            <th class="text-center"><input type="checkbox" class="group_check checkbox"></th>
            <th><?= $this->lang->line('transfer_code'); ?></th>
            <th><?= $this->lang->line('transfer_date'); ?></th>
            <th><?= $this->lang->line('reference_no'); ?></th>
            <th><?= $this->lang->line('debit_account'); ?></th>
            <th><?= $this->lang->line('credit_account'); ?></th>
            <th><?= $this->lang->line('amount'); ?></th>
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
      "aLengthMenu": [[10, 25, 50, 100, 500], [10, 25, 50, 100, 500]],
      dom:'<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
      buttons: {
        buttons: [
            { className: 'btn bg-red color-palette btn-flat hidden delete_btn pull-left', text: 'Delete', action: function ( e, dt, node, config ) { multi_delete(); } },
            { extend: 'copy', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3,4,5,6,7]} },
            { extend: 'excel', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3,4,5,6,7]} },
            { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3,4,5,6,7]} },
            { extend: 'print', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3,4,5,6,7]} },
            { extend: 'csv', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3,4,5,6,7]} },
            { extend: 'colvis', className: 'btn bg-teal color-palette btn-flat', text:'Columns' }
        ]
      },
      "processing": true,
      "serverSide": true,
      "order": [],
      "responsive": false,
      language: { processing: '<div class="text-primary bg-primary" style="position: relative;z-index:100;overflow: visible;">Processing...</div>' },
      "ajax": {
          "url": "<?= base_url('money_transfer/ajax_list'); ?>",
          "type": "POST",
          "data": {
              transfer_date: $("#transfer_date").val(),
              debit_account_id: $("#debit_account_id").val(),
              credit_account_id: $("#credit_account_id").val(),
              users: $("#users").val()
          },
          complete: function (data) {
           $('.column_checkbox').iCheck({ checkboxClass: 'icheckbox_square-orange', radioClass: 'iradio_square-orange', increaseArea: '10%' });
           call_code();
          }
      },
      "columnDefs": [
        { "targets": [0,8], "orderable": false },
        { "targets": [0], "className": "text-center" }
      ]
  });
}
$(document).ready(function(){ load_datatable(); });
$("#transfer_date,#credit_account_id,#debit_account_id,#users").on("change",function(){
  $('#example2').DataTable().destroy();
  load_datatable();
});
</script>
<script src="<?= htmlspecialchars($theme_link); ?>js/accounts/money_transfer.js"></script>
<script>
  $('.mp-nav-item').removeClass('active');
  $('.money_transfer_list-active-li').addClass('active');
  $('.money_transfer_list-active-li').closest('.mp-nav-group').addClass('open');
</script>
