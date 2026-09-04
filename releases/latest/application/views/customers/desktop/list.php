<?php $this->load->view('customers/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<?php
$store_id = get_current_store_id();
$total_customers = $CI->db->where('store_id', $store_id)->count_all_results('db_customers');
$active_customers = $CI->db->where('store_id', $store_id)->where('status', 1)->count_all_results('db_customers');
$inactive_customers = $CI->db->where('store_id', $store_id)->where('status', 0)->count_all_results('db_customers');
$ob_paid_row = $CI->db->select('COALESCE(SUM(payment),0) as payment', false)
  ->where('store_id', $store_id)
  ->where('short_code', 'OPENING BALANCE PAID')
  ->get('db_salespayments')->row();
$ob_paid = $ob_paid_row ? $ob_paid_row->payment : 0;

$outstanding_row = $CI->db->query("
  SELECT COALESCE(SUM(
    COALESCE(opening_balance, 0)
    + COALESCE(sales_due, 0)
    - COALESCE(sales_return_due, 0)
  ), 0) as total
  FROM db_customers
  WHERE store_id = ?
", [$store_id])->row();
$total_outstanding = $outstanding_row ? ($outstanding_row->total - $ob_paid) : 0;
?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">View/Search <?= mp_label('customer'); ?>s</div>
  </div>
</div>

<div class="mp-quick-actions">
  <?php if ($CI->permissions('customers_add')): ?>
    <a href="<?= base_url('customers/add'); ?>" class="mp-qa-btn green"><i class="fa fa-plus"></i> New <?= mp_label('customer'); ?></a>
  <?php endif; ?>
  <?php if ($CI->permissions('import_customers')): ?>
    <a href="<?= base_url('import/customers'); ?>" class="mp-qa-btn orange"><i class="fa fa-arrow-circle-o-down"></i> Import Customers</a>
  <?php endif; ?>
</div>

<div class="mp-kpi-grid">
  <div class="mp-kpi-card summary">
    <div class="mp-kpi-icon"><i class="fa fa-users"></i></div>
    <div class="mp-kpi-label">Total Customers</div>
    <div class="mp-kpi-value"><?= number_format($total_customers); ?></div>
  </div>
  <div class="mp-kpi-card sales">
    <div class="mp-kpi-icon"><i class="fa fa-check-circle"></i></div>
    <div class="mp-kpi-label">Active</div>
    <div class="mp-kpi-value"><?= number_format($active_customers); ?></div>
  </div>
  <div class="mp-kpi-card stock">
    <div class="mp-kpi-icon"><i class="fa fa-pause-circle"></i></div>
    <div class="mp-kpi-label">Inactive</div>
    <div class="mp-kpi-value"><?= number_format($inactive_customers); ?></div>
  </div>
  <div class="mp-kpi-card debt">
    <div class="mp-kpi-icon"><i class="fa fa-money"></i></div>
    <div class="mp-kpi-label">Total Outstanding</div>
    <div class="mp-kpi-value"><?= store_number_format($total_outstanding); ?></div>
  </div>
</div>

<?= form_open('#', array('class' => '', 'id' => 'table_form')); ?>
<input type="hidden" id="base_url" value="<?= $base_url; ?>">

<div class="mp-card box">
  <div class="mp-card-head" style="flex-wrap:wrap;gap:12px;">
    <h3><?= mp_label('customer'); ?> List</h3>
    <label class="checkbox" style="margin:0;">
      <input type="checkbox" name="show_account_receivable" id="show_account_receivable"> Show account receivable only
    </label>
  </div>
  <div class="mp-card-body" style="padding:0;">
    <table id="example2" class="table mp-dt-table" width="100%">
      <thead>
        <tr>
          <th class="text-center"><input type="checkbox" class="group_check checkbox"></th>
          <th><?= mp_label('customer'); ?> ID</th>
          <th><?= mp_label('customer'); ?> Name</th>
          <th><?= $this->lang->line('mobile'); ?></th>
          <th><?= $this->lang->line('email'); ?></th>
          <th><?= $this->lang->line('location'); ?></th>
          <th><?= $this->lang->line('credit_limit'); ?></th>
          <th><?= $this->lang->line('previous_due'); ?></th>
          <th><?= $this->lang->line('sales_return_due'); ?>(+)</th>
          <th><?= $this->lang->line('advance'); ?></th>
          <th>Points</th>
          <th>Tier</th>
          <th>Store Credit</th>
          <th>Gift Card</th>
          <th><?= $this->lang->line('status'); ?></th>
          <th><?= $this->lang->line('action'); ?></th>
        </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
        <tr>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th style="text-align:right">Total</th>
          <th style="text-align:right"></th>
          <th style="text-align:right"></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
        </tr>
      </tfoot>
    </table>
  </div>
</div>

<div class="pay_now_modal"></div>
<div class="pay_return_due_modal"></div>
<div class="bulk_payment_list_modal"></div>

<?= form_close(); ?>

<script src="<?= $theme_link; ?>js/customers.js"></script>
<script type="text/javascript">
function load_datatable(show_account_receivable){
  if(typeof show_account_receivable === 'undefined') show_account_receivable = 'unchecked';

  var table = $('#example2').DataTable({
    "aLengthMenu": [[10, 25, 50, 100, 500], [10, 25, 50, 100, 500]],
    dom: '<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
    buttons: {
      buttons: [
        {
          className: 'btn bg-red color-palette btn-flat hidden delete_btn pull-left',
          text: 'Delete',
          action: function (e, dt, node, config) {
            multi_delete();
          }
        },
        { extend: 'copy', className: 'btn bg-teal color-palette btn-flat', footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10] } },
        { extend: 'excel', className: 'btn bg-teal color-palette btn-flat', footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10] } },
        { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat', footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10] } },
        { extend: 'print', className: 'btn bg-teal color-palette btn-flat', footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10] } },
        { extend: 'csv', className: 'btn bg-teal color-palette btn-flat', footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9,10] } },
        { extend: 'colvis', className: 'btn bg-teal color-palette btn-flat', footer: true, text: 'Columns' }
      ]
    },
    "processing": true,
    "serverSide": true,
    "order": [],
    "responsive": true,
    language: {
      processing: '<div class="text-primary bg-primary" style="position: relative;z-index:100;overflow: visible;">Processing...</div>'
    },
    "ajax": {
      "url": "<?= base_url('customers/ajax_list'); ?>",
      "type": "POST",
      "data": {
        show_account_receivable: show_account_receivable
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
      {
        "targets": [0, 15],
        "orderable": false
      },
      {
        "targets": [0],
        "className": "text-center"
      }
    ],
    "footerCallback": function (row, data, start, end, display) {
      var api = this.api(), data;
      var intVal = function (i) {
        return typeof i === 'string' ?
          i.replace(/[\$,]/g, '') * 1 :
          typeof i === 'number' ? i : 0;
      };
      var previous_total = api
        .column(7, { page: 'none' })
        .data()
        .reduce(function (a, b) { return intVal(a) + intVal(b); }, 0);
      var return_due_total = api
        .column(8, { page: 'none' })
        .data()
        .reduce(function (a, b) { return intVal(a) + intVal(b); }, 0);
      $(api.column(7).footer()).html(to_Fixed(previous_total));
      $(api.column(8).footer()).html(to_Fixed(return_due_total));
    }
  });
  new $.fn.dataTable.FixedHeader(table);
}

$(document).ready(function() {
  load_datatable();
});

$('#show_account_receivable').on('ifChanged', function(event) {
  $('#example2').DataTable().destroy();
  if (event.target.checked) {
    load_datatable('checked');
  } else {
    load_datatable();
  }
});
</script>
<script>
  $('.mp-nav-item').removeClass('active');
  $('.customers_list-active-li').addClass('active');
  $('.customers_list-active-li').closest('.mp-nav-group').addClass('open');
</script>
