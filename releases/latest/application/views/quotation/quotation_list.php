<?php $this->load->view('finance/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<?php
$store_id = get_current_store_id();

$total_quotations = $CI->db->where('store_id', $store_id)->count_all_results('db_quotation');
$total_amount = $CI->db->where('store_id', $store_id)->select('COALESCE(SUM(grand_total),0) as total', false)->get('db_quotation')->row()->total;
$converted_count = $CI->db->where('store_id', $store_id)->where('sales_status !=', '')->count_all_results('db_quotation');
$pending_count = $CI->db->where('store_id', $store_id)->where('sales_status', '')->count_all_results('db_quotation');
?>

<style>
.mp-filter-bar{display:flex!important;flex-direction:row!important;flex-wrap:wrap!important;gap:12px!important;align-items:flex-end!important;margin-bottom:20px!important;width:100%!important;box-sizing:border-box!important}
.mp-filter-item{display:flex;flex-direction:column;gap:4px;min-width:160px;flex:1 1 180px}
.mp-filter-item label{font-size:12px;font-weight:600;color:var(--mp-muted);margin:0}
.mp-filter-item .input-group{width:100%}
.mp-filter-item input,.mp-filter-item select{border:1px solid var(--mp-border);border-radius:8px;padding:8px 12px;height:38px;font-size:14px;width:100%}
.select2-container--default .select2-selection--single{border:1px solid var(--mp-border)!important;border-radius:8px!important;height:38px!important}
.select2-container--default .select2-selection--single .select2-selection__rendered{line-height:36px!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Manage and convert quotations to invoices</div>
  </div>
  <?php if($CI->permissions('quotation_add')) { ?>
  <a class="mp-qa-btn green" href="<?= base_url('quotation/add'); ?>"><i class="fa fa-plus"></i> <?= $this->lang->line('create_quotation'); ?></a>
  <?php } ?>
</div>

<div class="mp-kpi-grid">
  <div class="mp-kpi-card sales">
    <div class="mp-kpi-icon"><i class="fa fa-file-text-o"></i></div>
    <div class="mp-kpi-label">Total Quotations</div>
    <div class="mp-kpi-value"><?= number_format($total_quotations); ?></div>
  </div>
  <div class="mp-kpi-card profit">
    <div class="mp-kpi-icon"><i class="fa fa-money"></i></div>
    <div class="mp-kpi-label">Total Amount</div>
    <div class="mp-kpi-value"><?= $CI->currency(kmb($total_amount)); ?></div>
  </div>
  <div class="mp-kpi-card cash">
    <div class="mp-kpi-icon"><i class="fa fa-check-circle"></i></div>
    <div class="mp-kpi-label">Converted</div>
    <div class="mp-kpi-value"><?= number_format($converted_count); ?></div>
  </div>
  <div class="mp-kpi-card debt">
    <div class="mp-kpi-icon"><i class="fa fa-hourglass-half"></i></div>
    <div class="mp-kpi-label">Pending</div>
    <div class="mp-kpi-value"><?= number_format($pending_count); ?></div>
  </div>
</div>


<div class="pay_now_modal"></div>
<div class="view_payments_modal"></div>

<?= form_open('#', array('class' => '', 'id' => 'table_form')); ?>
<input type="hidden" id="base_url" value="<?= htmlspecialchars($base_url); ?>">

<div class="mp-filter-bar">
  <?php if(warehouse_module() && warehouse_count()>1) {
    $this->load->view('warehouse/warehouse_code',array('show_warehouse_select_box'=>true,'div_length'=>'','label_length'=>'','show_all'=>'true','show_all_option'=>true,'remove_star'=>true));
  } else {
    echo "<input type='hidden' name='warehouse_id' id='warehouse_id' value='".get_store_warehouse_id()."'>";
  } ?>

  <div class="mp-filter-item">
    <label for="quotation_from_date"><?= $this->lang->line('from_date'); ?></label>
    <div class="input-group date">
      <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
      <input type="text" class="form-control datepicker" id="quotation_from_date" name="quotation_from_date">
    </div>
    <span id="quotation_from_date_msg" style="display:none" class="text-danger"></span>
  </div>

  <div class="mp-filter-item">
    <label for="quotation_to_date"><?= $this->lang->line('to_date'); ?></label>
    <div class="input-group date">
      <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
      <input type="text" class="form-control datepicker" id="quotation_to_date" name="quotation_to_date">
    </div>
    <span id="quotation_to_date_msg" style="display:none" class="text-danger"></span>
  </div>

  <div class="mp-filter-item">
    <label for="users"><?= $this->lang->line('users'); ?></label>
    <select class="form-control select2" id="users" name="users" style="width:100%;">
      <?= get_users_select_list($this->session->userdata("role_id"), get_current_store_id()); ?>
    </select>
    <span id="users_msg" style="display:none" class="text-danger"></span>
  </div>
</div>

<div class="mp-table-wrap">
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table id="example2" class="table mp-dt-table" width="100%">
        <thead>
          <tr>
            <th class="text-center"><input type="checkbox" class="group_check checkbox"></th>
            <th><?= $this->lang->line('quotation_date'); ?></th>
            <th><?= $this->lang->line('expire_date'); ?></th>
            <th><?= $this->lang->line('quotation_code'); ?></th>
            <th><?= $this->lang->line('reference_no'); ?></th>
            <th><?= $this->lang->line('customer_name'); ?></th>
            <th><?= $this->lang->line('total'); ?></th>
            <th><?= $this->lang->line('created_by'); ?></th>
            <th><?= $this->lang->line('action'); ?></th>
          </tr>
        </thead>
        <tbody></tbody>
        <tfoot>
          <tr class="bg-gray">
            <th></th><th></th><th></th><th></th><th></th>
            <th style="text-align:right">Total</th>
            <th></th><th></th><th></th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

<?= form_close(); ?>

<script src="<?= htmlspecialchars($theme_link); ?>plugins/datepicker/bootstrap-datepicker.js"></script>
<script type="text/javascript">
  $('.datepicker').datepicker({
    autoclose: true,
    format: 'dd-mm-yyyy',
    todayHighlight: true
  });

  function load_datatable(){
    var table = $('#example2').DataTable({
      "aLengthMenu": [[10, 25, 50, 100, 500], [10, 25, 50, 100, 500]],
      dom: '<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
      buttons: {
        buttons: [
          { className: 'btn bg-red color-palette btn-flat hidden delete_btn pull-left', text: 'Delete', action: function (e, dt, node, config) { multi_delete(); } },
          { extend: 'copy', className: 'btn bg-teal color-palette btn-flat', footer: true, exportOptions: { columns: [1,2,3,4,5,6,7]} },
          { extend: 'excel', className: 'btn bg-teal color-palette btn-flat', footer: true, exportOptions: { columns: [1,2,3,4,5,6,7]} },
          { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat', footer: true, exportOptions: { columns: [1,2,3,4,5,6,7]} },
          { extend: 'print', className: 'btn bg-teal color-palette btn-flat', footer: true, exportOptions: { columns: [1,2,3,4,5,6,7]} },
          { extend: 'csv', className: 'btn bg-teal color-palette btn-flat', footer: true, exportOptions: { columns: [1,2,3,4,5,6,7]} },
          { extend: 'colvis', className: 'btn bg-teal color-palette btn-flat', footer: true, text:'Columns' }
        ]
      },
      "processing": true,
      "serverSide": true,
      "order": [],
      "responsive": false,
      language: {
        processing: '<div class="text-primary bg-primary" style="position: relative;z-index:100;overflow: visible;">Processing...</div>'
      },
      "ajax": {
        "url": "<?= site_url('quotation/ajax_list'); ?>",
        "type": "POST",
        "data": {
          warehouse_id: $("#warehouse_id").val(),
          quotation_from_date: $("#quotation_from_date").val(),
          quotation_to_date: $("#quotation_to_date").val(),
          users: $("#users").val()
        },
        complete: function (data) {
          $('.column_checkbox').iCheck({
            checkboxClass: 'icheckbox_square-orange',
            radioClass: 'iradio_square-orange',
            increaseArea: '10%'
          });
          call_code();
        }
      },
      "columnDefs": [
        { "targets": [ 0,8 ], "orderable": false },
        { "targets" :[0], "className": "text-center" }
      ],
      "footerCallback": function ( row, data, start, end, display ) {
        var api = this.api(), data;
        var intVal = function ( i ) {
          return typeof i === 'string' ? i.replace(/[\$,]/g, '')*1 : typeof i === 'number' ? i : 0;
        };
        var total = api.column( 6, { page: 'none'} ).data().reduce( function (a, b) { return intVal(a) + intVal(b); }, 0 );
        $( api.column( 6 ).footer() ).html(to_Fixed(total));
      }
    });
  }

  $(document).ready(function() {
    load_datatable();
  });

  $("#warehouse_id,#quotation_from_date,#quotation_to_date,#users").on("change",function(){
    $('#example2').DataTable().destroy();
    load_datatable();
  });

  function print_invoice(id){
    window.open("<?= base_url('pos/print_invoice_pos/'); ?>"+id, "_blank", "scrollbars=1,resizable=1,height=500,width=500");
  }
  function show_receipt(id){
    window.open("<?= base_url('quotation/print_show_receipt/'); ?>"+id, "_blank", "scrollbars=1,resizable=1,height=500,width=500");
  }
</script>
<script src="<?= htmlspecialchars($theme_link); ?>js/quotation/quotation.js"></script>
<script>$(".quotation_list-active-li").addClass("active");</script>
