<?php $this->load->view('finance/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<?php
$store_id = get_current_store_id();

$total_payments = $CI->db->where('store_id', $store_id)->count_all_results('db_salespayments');
$total_amount = $CI->db->where('store_id', $store_id)->select('COALESCE(SUM(payment),0) as total', false)->get('db_salespayments')->row()->total;
$cash_amount = $CI->db->where('store_id', $store_id)->where('payment_type', cash_name())->select('COALESCE(SUM(payment),0) as total', false)->get('db_salespayments')->row()->total;
$cheque_amount = $CI->db->where('store_id', $store_id)->where('payment_type', cheque_name())->select('COALESCE(SUM(payment),0) as total', false)->get('db_salespayments')->row()->total;
?>

<style>
.mp-filter-bar{display:flex!important;flex-direction:row!important;flex-wrap:wrap!important;gap:12px!important;align-items:flex-end!important;margin-bottom:20px!important;width:100%!important;box-sizing:border-box!important}
.mp-filter-item{display:flex;flex-direction:column;gap:4px;min-width:160px;flex:0 1 220px}
.mp-filter-item label{font-size:12px;font-weight:600;color:var(--mp-muted);margin:0}
.mp-filter-item .input-group{width:100%}
.mp-filter-item input,.mp-filter-item select{border:1px solid var(--mp-border);border-radius:8px;padding:8px 12px;height:38px;font-size:14px;width:100%}
.select2-container--default .select2-selection--single{border:1px solid var(--mp-border)!important;border-radius:8px!important;height:38px!important}
.select2-container--default .select2-selection--single .select2-selection__rendered{line-height:36px!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Track and manage customer payments</div>
  </div>
</div>

<div class="mp-kpi-grid">
  <div class="mp-kpi-card sales">
    <div class="mp-kpi-icon"><i class="fa fa-files-o"></i></div>
    <div class="mp-kpi-label">Total Payments</div>
    <div class="mp-kpi-value"><?= number_format($total_payments); ?></div>
  </div>
  <div class="mp-kpi-card profit">
    <div class="mp-kpi-icon"><i class="fa fa-money"></i></div>
    <div class="mp-kpi-label">Total Amount</div>
    <div class="mp-kpi-value"><?= $CI->currency(kmb($total_amount)); ?></div>
  </div>
  <div class="mp-kpi-card cash">
    <div class="mp-kpi-icon"><i class="fa fa-university"></i></div>
    <div class="mp-kpi-label">Cash Payments</div>
    <div class="mp-kpi-value"><?= $CI->currency(kmb($cash_amount)); ?></div>
  </div>
  <div class="mp-kpi-card debt">
    <div class="mp-kpi-icon"><i class="fa fa-check-square-o"></i></div>
    <div class="mp-kpi-label">Cheque Payments</div>
    <div class="mp-kpi-value"><?= $CI->currency(kmb($cheque_amount)); ?></div>
  </div>
</div>


<div class="pay_now_modal"></div>

<?= form_open('#', array('class' => '', 'id' => 'table_form')); ?>
<input type="hidden" id="base_url" value="<?= htmlspecialchars($base_url); ?>">

<div class="mp-filter-bar">
  <div class="mp-filter-item">
    <label for="payment_type_search"><?= $this->lang->line('payment_type'); ?></label>
    <select class="form-control select2" id="payment_type_search" name="payment_type_search" style="width:100%;">
      <?php
      $q1=$this->db->query("select * from db_paymenttypes where status=1 and store_id=".get_current_store_id());
      if($q1->num_rows()>0){
        echo "<option value=''>-Select-</option>";
        foreach($q1->result() as $res1){
          echo "<option value='".$res1->payment_type."'>".$res1->payment_type ."</option>";
        }
      } else {
        echo "<option>None</option>";
      }
      ?>
    </select>
    <span id="payment_type_search_msg" style="display:none" class="text-danger"></span>
  </div>

  <div class="mp-filter-item">
    <label for="cheque_status_search"><?= $this->lang->line('payment_status'); ?></label>
    <select class="form-control select2" id="cheque_status_search" name="cheque_status_search" style="width:100%;">
      <?= get_cheque_status_select_list(); ?>
    </select>
    <span id="cheque_status_search_msg" style="display:none" class="text-danger"></span>
  </div>
</div>

<div class="mp-table-wrap">
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table id="example2" class="table mp-dt-table" width="100%">
        <thead>
          <tr>
            <th><?= $this->lang->line('payment_code'); ?></th>
            <th><?= $this->lang->line('payment_date'); ?></th>
            <th><?= $this->lang->line('sales_code'); ?></th>
            <th><?= $this->lang->line('customer_name'); ?></th>
            <th><?= $this->lang->line('payment'); ?></th>
            <th><?= $this->lang->line('payment_type'); ?></th>
            <th><?= $this->lang->line('payment_note'); ?></th>
            <th><?= $this->lang->line('created_by'); ?></th>
            <th><?= $this->lang->line('action'); ?></th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<?= form_close(); ?>

<script src="<?= htmlspecialchars($theme_link); ?>plugins/datepicker/bootstrap-datepicker.js"></script>
<script src="<?= htmlspecialchars($theme_link); ?>js/sales_payments/create.js"></script>
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
          { extend: 'copy', className: 'btn bg-teal color-palette btn-flat', footer: true, exportOptions: { columns: [0,1,2,3,4,5,6,7]} },
          { extend: 'excel', className: 'btn bg-teal color-palette btn-flat', footer: true, exportOptions: { columns: [0,1,2,3,4,5,6,7]} },
          { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat', footer: true, exportOptions: { columns: [0,1,2,3,4,5,6,7]} },
          { extend: 'print', className: 'btn bg-teal color-palette btn-flat', footer: true, exportOptions: { columns: [0,1,2,3,4,5,6,7]} },
          { extend: 'csv', className: 'btn bg-teal color-palette btn-flat', footer: true, exportOptions: { columns: [0,1,2,3,4,5,6,7]} },
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
        "url": "<?= site_url('sales_payments/ajax_list'); ?>",
        "type": "POST",
        "data": {
          cheque_status_search: $("#cheque_status_search").val(),
          payment_type_search: $("#payment_type_search").val()
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
      ]
    });
  }

  $(document).ready(function() {
    load_datatable();
  });

  $("#cheque_status_search,#payment_type_search").on("change",function(){
    $('#example2').DataTable().destroy();
    load_datatable();
  });
</script>
<script>$(".sales-payments-list-active-li").addClass("active");</script>
