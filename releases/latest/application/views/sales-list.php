<?php $this->load->view('finance/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<?php
/*Total Points*/
$this->db->where("upper(a.created_by)", strtoupper($this->session->userdata('inv_username')));
$this->db->select("coalesce(sum(seller_points)) as seller_points");
$this->db->join("db_salesitems b", "b.sales_id=a.id", "inner");
$this->db->from("db_sales a");
$this->db->where("a.store_id", get_current_store_id());
$seller_points = $this->db->get()->row()->seller_points;
$seller_points = store_number_format($seller_points);

/*Total Invoices*/
if (!is_admin()) {
	if ($this->session->userdata('role_id') != '2') {
		$this->db->where("upper(created_by)", strtoupper($this->session->userdata('inv_username')));
	}
}
$total_invoice = $this->db->select("COUNT(*) as total")->from("db_sales")->where("store_id", get_current_store_id())->get()->row()->total;

/*Total Invoices Total*/
if (!is_admin()) {
	if ($this->session->userdata('role_id') != '2') {
		$this->db->where("upper(created_by)", strtoupper($this->session->userdata('inv_username')));
	}
}
$sal_total = $this->db->select("COALESCE(sum(grand_total),0) AS tot_sal_grand_total")->from("db_sales")->where("sales_status", 'Final')->where("store_id", get_current_store_id())->get()->row()->tot_sal_grand_total;

/*Total Received*/
if (!is_admin()) {
	if ($this->session->userdata('role_id') != '2') {
		$this->db->where("upper(created_by)", strtoupper($this->session->userdata('inv_username')));
	}
}
$tot_received_amt = $this->db->select("COALESCE(SUM(paid_amount),0) AS paid_amount")->from("db_sales")->where("store_id", get_current_store_id())->get()->row()->paid_amount;

/*Total Sales Due*/
if (!is_admin()) {
	if ($this->session->userdata('role_id') != '2') {
		$this->db->where("upper(created_by)", strtoupper($this->session->userdata('inv_username')));
	}
}
$sales_due_total = $this->db->select("COALESCE(SUM(sales_due),0) AS sales_due")->from("db_customers")->where("store_id", get_current_store_id())->get()->row()->sales_due;

$store_name = $this->session->userdata('store_name') ?: 'MartPoint';
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
    <div class="mp-page-sub"><?= htmlspecialchars($store_name); ?> &mdash; All Sales Invoices</div>
  </div>
  <?php if ($CI->permissions('sales_add')) { ?>
  <a class="mp-qa-btn green" href="<?= base_url('sales/add'); ?>"><i class="fa fa-plus"></i> <?= $this->lang->line('create_invoice'); ?></a>
  <?php } ?>
</div>

<div class="mp-kpi-grid">
  <div class="mp-kpi-card sales">
    <div class="mp-kpi-icon"><i class="fa fa-file-text-o"></i></div>
    <div class="mp-kpi-label">Total Invoices</div>
    <div class="mp-kpi-value"><?= number_format($total_invoice); ?></div>
  </div>
  <div class="mp-kpi-card profit">
    <div class="mp-kpi-icon"><i class="fa fa-money"></i></div>
    <div class="mp-kpi-label">Total Invoice Amount</div>
    <div class="mp-kpi-value"><?= $CI->currency(kmb($sal_total)); ?></div>
  </div>
  <div class="mp-kpi-card cash">
    <div class="mp-kpi-icon"><i class="fa fa-check-circle"></i></div>
    <div class="mp-kpi-label">Total Received</div>
    <div class="mp-kpi-value"><?= $CI->currency(kmb($tot_received_amt)); ?></div>
  </div>
  <div class="mp-kpi-card debt">
    <div class="mp-kpi-icon"><i class="fa fa-clock-o"></i></div>
    <div class="mp-kpi-label">Total Sales Due</div>
    <div class="mp-kpi-value"><?= $CI->currency(kmb($sales_due_total)); ?></div>
  </div>
</div>


<div class="pay_now_modal"></div>
<div class="view_payments_modal"></div>

<?= form_open('#', array('class' => '', 'id' => 'table_form')); ?>
<input type="hidden" id="base_url" value="<?= htmlspecialchars($base_url); ?>">

<div class="mp-filter-bar">
  <?php if (warehouse_module() && warehouse_count() > 1) { ?>
  <div class="mp-filter-item">
    <label>Branch</label>
    <?php
    $this->load->view('warehouse/warehouse_code', array('show_warehouse_select_box' => true, 'div_length' => '',
      'label_length' => '', 'show_all' => 'true', 'show_all_option' => true, 'remove_star' => true));
    ?>
  </div>
  <?php } else { ?>
  <input type="hidden" name="warehouse_id" id="warehouse_id" value="<?= get_store_warehouse_id(); ?>">
  <?php } ?>

  <div class="mp-filter-item">
    <label for="search_customer_id"><?= $this->lang->line('customers'); ?></label>
    <select class="form-control select2" id="search_customer_id" name="search_customer_id" style="width:100%;"></select>
  </div>

  <div class="mp-filter-item">
    <label for="users"><?= $this->lang->line('users'); ?></label>
    <select class="form-control select2" id="users" name="users" style="width:100%;">
      <?= get_users_select_list($this->session->userdata("role_id"), get_current_store_id()); ?>
    </select>
  </div>

  <div class="mp-filter-item">
    <label for="sales_from_date"><?= $this->lang->line('from_date'); ?></label>
    <div class="input-group date">
      <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
      <input type="text" class="form-control datepicker" id="sales_from_date" name="sales_from_date">
    </div>
  </div>

  <div class="mp-filter-item">
    <label for="sales_to_date"><?= $this->lang->line('to_date'); ?></label>
    <div class="input-group date">
      <div class="input-group-addon"><i class="fa fa-calendar"></i></div>
      <input type="text" class="form-control datepicker" id="sales_to_date" name="sales_to_date">
    </div>
  </div>
</div>

<div class="mp-table-wrap">
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table id="example2" class="table mp-dt-table" width="100%">
        <thead>
          <tr>
            <th class="text-center"><input type="checkbox" class="group_check checkbox"></th>
            <th><?= $this->lang->line('sales_date'); ?></th>
            <th><?= $this->lang->line('due_date'); ?></th>
            <th><?= $this->lang->line('sales_code'); ?></th>
            <th><?= $this->lang->line('reference_no'); ?></th>
            <th><?= $this->lang->line('customer_name'); ?></th>
            <th><?= $this->lang->line('total'); ?></th>
            <th><?= $this->lang->line('paid_amount'); ?></th>
            <th><?= $this->lang->line('payment_status'); ?></th>
            <th><?= $this->lang->line('payment_type'); ?></th>
            <th><?= $this->lang->line('created_by'); ?></th>
            <th><?= $this->lang->line('action'); ?></th>
          </tr>
        </thead>
        <tbody></tbody>
        <tfoot>
          <tr id="overdiv">
            <th></th><th></th><th></th><th></th><th></th>
            <th style="text-align:right">Total</th>
            <th></th><th></th><th></th><th></th><th></th><th></th>
          </tr>
        </tfoot>
      </table>
    </div>
  </div>
</div>

<?= form_close(); ?>

<script src="<?= htmlspecialchars($theme_link); ?>plugins/datepicker/bootstrap-datepicker.js"></script>
<script src="<?= htmlspecialchars($theme_link); ?>js/ajaxselect/customer_select_ajax.js"></script>
<script type="text/javascript">
  $('.datepicker').datepicker({
    autoclose: true,
    format: 'dd-mm-yyyy',
    todayHighlight: true
  });

  function getCustomerSelectionId() {
    return '#search_customer_id';
  }

  function load_datatable(){
    var table = $('#example2').DataTable({
      "aLengthMenu": [[10, 25, 50, 100, 500], [10, 25, 50, 100, 500]],
      dom: '<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
      buttons: {
        buttons: [
          { className: 'btn bg-red color-palette btn-flat hidden delete_btn pull-left', text: 'Delete', action: function (e, dt, node, config) { multi_delete(); } },
          { extend: 'copy', className: 'btn bg-teal color-palette btn-flat', footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10] } },
          { extend: 'excel', className: 'btn bg-teal color-palette btn-flat', footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10] } },
          { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat', footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10] } },
          { extend: 'print', className: 'btn bg-teal color-palette btn-flat', footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10] } },
          { extend: 'csv', className: 'btn bg-teal color-palette btn-flat', footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10] } },
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
        "url": "<?= site_url('sales/ajax_list'); ?>",
        "type": "POST",
        "data": {
          warehouse_id: $("#warehouse_id").val(),
          sales_from_date: $("#sales_from_date").val(),
          sales_to_date: $("#sales_to_date").val(),
          users: $("#users").val(),
          customer_id: $("#search_customer_id").val()
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
        { "targets": [0,9,11], "orderable": false },
        { "targets": [0], "className": "text-center" }
      ],
      "footerCallback": function ( row, data, start, end, display ) {
        var api = this.api(), data;
        var intVal = function ( i ) {
          return typeof i === 'string' ? i.replace(/[\$,]/g, '')*1 : typeof i === 'number' ? i : 0;
        };
        var total = api.column( 6, { page: 'none'} ).data().reduce( function (a, b) { return intVal(a) + intVal(b); }, 0 );
        var paid = api.column( 7, { page: 'none'} ).data().reduce( function (a, b) { return intVal(a) + intVal(b); }, 0 );
        $( api.column( 6 ).footer() ).html(to_Fixed(total));
        $( api.column( 7 ).footer() ).html(to_Fixed(paid));
      }
    });
  }

  $(document).ready(function() {
    load_datatable();
  });

  $("#warehouse_id,#sales_from_date,#sales_to_date,#users,#search_customer_id").on("change",function(){
    $('#example2').DataTable().destroy();
    load_datatable();
  });

  $("#payment_type").on("change",function(){
    show_cheque_details();
  });
  function show_cheque_details(){
    var payment_type = $("#payment_type").val();
    if(payment_type.toUpperCase()=='<?= strtoupper(cheque_name()); ?>'){
      $(".cheque_div").show();
    } else {
      $(".cheque_div").hide();
      $("#cheque_period,#cheque_number").val('');
    }
  }

  function print_invoice(id){
    window.open("<?= base_url('pos/print_invoice_pos/'); ?>"+id, "_blank", "scrollbars=1,resizable=1,height=500,width=500");
  }
  function show_receipt(id){
    window.open("<?= base_url('sales/print_show_receipt/'); ?>"+id, "_blank", "scrollbars=1,resizable=1,height=500,width=500");
  }
</script>
<script src="<?= htmlspecialchars($theme_link); ?>js/sales.js"></script>
<script>$(".sales-list-active-li").addClass("active");</script>
