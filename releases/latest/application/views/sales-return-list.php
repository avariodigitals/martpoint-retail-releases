<?php $this->load->view('finance/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<?php
/*Total Invoices*/
if (!is_admin()) {
  if ($this->session->userdata('role_id') != '2') {
    $this->db->where("upper(created_by)", strtoupper($this->session->userdata('inv_username')));
  }
}
$total_invoice = $this->db->select("COUNT(*) as total")->from("db_salesreturn")->where("store_id", get_current_store_id())->get()->row()->total;

/*Total Invoices Total*/
if (!is_admin()) {
  if ($this->session->userdata('role_id') != '2') {
    $this->db->where("upper(created_by)", strtoupper($this->session->userdata('inv_username')));
  }
}
$sal_total = $this->db->select("COALESCE(sum(grand_total),0) AS tot_sal_grand_total")->from("db_salesreturn")->where("store_id", get_current_store_id())->get()->row()->tot_sal_grand_total;

/*Total Return Total*/
if (!is_admin()) {
  if ($this->session->userdata('role_id') != '2') {
    $this->db->where("upper(created_by)", strtoupper($this->session->userdata('inv_username')));
  }
}
$sal_return_total = $this->db->select("COALESCE(SUM(grand_total),0) AS sal_total")->from("db_salesreturn")->where("store_id", get_current_store_id())->get()->row()->sal_total;

//Sales Return Paid Total
if (!is_admin()) {
  if ($this->session->userdata('role_id') != '2') {
    $this->db->where("upper(created_by)", strtoupper($this->session->userdata('inv_username')));
  }
}
$paid_amount = $this->db->select("COALESCE(SUM(paid_amount),0) AS paid_amount")->from("db_salesreturn")->where("store_id", get_current_store_id())->get()->row()->paid_amount;

$sales_due_total = $sal_total - $paid_amount;
?>

<style>
.mp-filter-bar{display:flex!important;flex-direction:row!important;flex-wrap:wrap!important;gap:12px!important;align-items:flex-end!important;margin-bottom:20px!important;width:100%!important;box-sizing:border-box!important}
.mp-filter-item{display:flex;flex-direction:column;gap:4px;min-width:160px;flex:0 1 260px}
.mp-filter-item label{font-size:12px;font-weight:600;color:var(--mp-muted);margin:0}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">View/Search Sales Returns</div>
  </div>
  <?php if($CI->permissions('sales_return_add')) { ?>
  <a class="mp-qa-btn green" href="<?= base_url('sales_return/create'); ?>"><i class="fa fa-plus"></i> <?= $this->lang->line('create_new'); ?></a>
  <?php } ?>
</div>

<div class="mp-kpi-grid">
  <div class="mp-kpi-card sales">
    <div class="mp-kpi-icon"><i class="fa fa-files-o"></i></div>
    <div class="mp-kpi-label"><?= $this->lang->line('total_invoices'); ?></div>
    <div class="mp-kpi-value"><?= number_format($total_invoice); ?></div>
  </div>
  <div class="mp-kpi-card profit">
    <div class="mp-kpi-icon"><i class="fa fa-money"></i></div>
    <div class="mp-kpi-label"><?= $this->lang->line('total_invoices_amount'); ?></div>
    <div class="mp-kpi-value"><?= $CI->currency(kmb($sal_total)); ?></div>
  </div>
  <div class="mp-kpi-card cash">
    <div class="mp-kpi-icon"><i class="fa fa-check-circle"></i></div>
    <div class="mp-kpi-label"><?= $this->lang->line('total_returned_amount'); ?></div>
    <div class="mp-kpi-value"><?= $CI->currency(kmb($paid_amount)); ?></div>
  </div>
  <div class="mp-kpi-card debt">
    <div class="mp-kpi-icon"><i class="fa fa-minus-circle"></i></div>
    <div class="mp-kpi-label"><?= $this->lang->line('total_sales_return_due'); ?></div>
    <div class="mp-kpi-value"><?= $CI->currency(kmb($sales_due_total)); ?></div>
  </div>
</div>


<div class="pay_now_modal"></div>
<div class="view_payments_modal"></div>

<?= form_open('#', array('class' => '', 'id' => 'table_form')); ?>
<input type="hidden" id="base_url" value="<?= htmlspecialchars($base_url); ?>">

<div class="mp-filter-bar">
  <div class="mp-filter-item">
    <?php if(warehouse_module() && warehouse_count()>1) {
      $this->load->view('warehouse/warehouse_code',array('show_warehouse_select_box_2'=>true,'show_all_option'=>true));
    } else {
      echo "<input type='hidden' name='warehouse_id' id='warehouse_id' value='".get_store_warehouse_id()."'>";
    } ?>
  </div>
</div>

<div class="mp-table-wrap">
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table id="example2" class="table mp-dt-table" width="100%">
        <thead>
          <tr>
            <th class="text-center"><input type="checkbox" class="group_check checkbox"></th>
            <th><?= $this->lang->line('return_date'); ?></th>
            <th><?= $this->lang->line('sales_code'); ?></th>
            <th><?= $this->lang->line('return_code'); ?></th>
            <th><?= $this->lang->line('return_status'); ?></th>
            <th><?= $this->lang->line('reference_no'); ?></th>
            <th><?= $this->lang->line('customer_name'); ?></th>
            <th><?= $this->lang->line('total'); ?></th>
            <th><?= $this->lang->line('paid_amount'); ?></th>
            <th><?= $this->lang->line('payment_status'); ?></th>
            <th><?= $this->lang->line('created_by'); ?></th>
            <th><?= $this->lang->line('action'); ?></th>
          </tr>
        </thead>
        <tbody></tbody>
        <tfoot>
          <tr class="bg-gray">
            <th></th><th></th><th></th><th></th><th></th><th></th>
            <th style="text-align:right">Total</th>
            <th></th><th></th><th></th><th></th><th></th>
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
          { extend: 'copy', className: 'btn bg-teal color-palette btn-flat', footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10]} },
          { extend: 'excel', className: 'btn bg-teal color-palette btn-flat', footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10]} },
          { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat', footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10]} },
          { extend: 'print', className: 'btn bg-teal color-palette btn-flat', footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10]} },
          { extend: 'csv', className: 'btn bg-teal color-palette btn-flat', footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10]} },
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
        "url": "<?= site_url('sales_return/ajax_list'); ?>",
        "type": "POST",
        "data": {
          warehouse_id: $("#warehouse_id").val()
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
        { "targets": [ 0,11 ], "orderable": false },
        { "targets" :[0], "className": "text-center" }
      ],
      "footerCallback": function ( row, data, start, end, display ) {
        var api = this.api(), data;
        var intVal = function ( i ) {
          return typeof i === 'string' ? i.replace(/[\$,]/g, '')*1 : typeof i === 'number' ? i : 0;
        };
        var total = api.column( 7, { page: 'none'} ).data().reduce( function (a, b) { return intVal(a) + intVal(b); }, 0 );
        var paid = api.column( 8, { page: 'none'} ).data().reduce( function (a, b) { return intVal(a) + intVal(b); }, 0 );
        $( api.column( 7 ).footer() ).html(to_Fixed(total));
        $( api.column( 8 ).footer() ).html(to_Fixed(paid));
      }
    });
  }

  $(document).ready(function() {
    load_datatable();
  });

  $("#warehouse_id").on("change",function(){
    $('#example2').DataTable().destroy();
    load_datatable();
  });

  function print_invoice(id){
    window.open("<?= base_url('pos/print_invoice_pos/'); ?>"+id, "_blank", "scrollbars=1,resizable=1,height=500,width=500");
  }
</script>
<script src="<?= htmlspecialchars($theme_link); ?>js/sales-return.js"></script>
<script>$(".sales-return-list-active-li").addClass("active");</script>
