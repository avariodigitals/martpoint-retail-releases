<?php $this->load->view('suppliers/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">View and manage your suppliers</div>
  </div>
</div>

<div class="mp-quick-actions">
  <?php if($CI->permissions('suppliers_add')) { ?>
  <a class="mp-qa-btn green" href="<?= base_url('suppliers/add'); ?>">
    <i class="fa fa-plus"></i> New Supplier
  </a>
  <?php } ?>
  <?php if($CI->permissions('import_suppliers')) { ?>
  <a class="mp-qa-btn blue" href="<?= base_url('import/suppliers'); ?>">
    <i class="fa fa-arrow-circle-o-down"></i> Import Suppliers
  </a>
  <?php } ?>
</div>

<div class="mp-card" style="margin-bottom:16px;">
  <div class="mp-card-body">
    <div class="checkbox icheck">
      <label>
        <input type="checkbox" name="show_account_payble" id="show_account_payble"> <?= $this->lang->line('view_account_payble_suppliers'); ?>
      </label>
    </div>
  </div>
</div>

<div class="pay_now_modal"></div>
<div class="pay_return_due_modal"></div>

<div class="mp-table-wrap box">
  <div class="box-body">
    <?= form_open('#', array('class' => '', 'id' => 'table_form')); ?>
    <input type="hidden" id="base_url" value="<?= htmlspecialchars($base_url); ?>">
    <table id="example2" class="table mp-dt-table" width="100%">
      <thead>
        <tr>
          <th class="text-center"><input type="checkbox" class="group_check checkbox"></th>
          <th><?= $this->lang->line('supplier_id'); ?></th>
          <th><?= $this->lang->line('supplier_name'); ?></th>
          <th><?= $this->lang->line('mobile'); ?></th>
          <th><?= $this->lang->line('email'); ?></th>
          <th><?= $this->lang->line('previous_balance'); ?></th>
          <th><?= $this->lang->line('purchase_due'); ?></th>
          <th><?= $this->lang->line('purchase_return_due'); ?></th>
          <th><?= $this->lang->line('total'); ?>(+)</th>
          <th><?= $this->lang->line('status'); ?></th>
          <th><?= $this->lang->line('action'); ?></th>
        </tr>
      </thead>
      <tbody></tbody>
      <tfoot>
        <tr class="bg-gray">
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th style="text-align:right">Total</th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
          <th></th>
        </tr>
      </tfoot>
    </table>
    <?= form_close(); ?>
  </div>
</div>

<script src="<?= htmlspecialchars($theme_link); ?>js/suppliers.js"></script>
<script type="text/javascript">
function load_datatable(show_account_payble='unchecked'){
   var table = $('#example2').DataTable({
    "aLengthMenu": [[10, 25, 50, 100, 500], [10, 25, 50, 100, 500]],
    dom:'<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
    buttons: {
        buttons: [
            { className: 'btn bg-red color-palette btn-flat hidden delete_btn pull-left', text: 'Delete', action: function ( e, dt, node, config ) { multi_delete(); } },
            { extend: 'copy', className: 'btn bg-teal color-palette btn-flat', footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9]} },
            { extend: 'excel', className: 'btn bg-teal color-palette btn-flat', footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9]} },
            { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat', footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9]} },
            { extend: 'print', className: 'btn bg-teal color-palette btn-flat', footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9]} },
            { extend: 'csv', className: 'btn bg-teal color-palette btn-flat', footer: true, exportOptions: { columns: [1,2,3,4,5,6,7,8,9]} },
            { extend: 'colvis', className: 'btn bg-teal color-palette btn-flat', footer: true, text:'Columns' }
        ]
    },
    "processing": true,
    "serverSide": true,
    "order": [],
    "responsive": true,
    language: { processing: '<div class="text-primary bg-primary" style="position: relative;z-index:100;overflow: visible;">Processing...</div>' },
    "ajax": {
        "url": "<?= base_url('suppliers/ajax_list'); ?>",
        "type": "POST",
        "data": {
            show_account_payble: show_account_payble,
            '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
        },
        complete: function (data) {
            $('.column_checkbox').iCheck({ checkboxClass: 'icheckbox_square-orange', radioClass: 'iradio_square-orange', increaseArea: '10%' });
            call_code();
        }
    },
    "columnDefs": [
        { "targets": [0,10], "orderable": false },
        { "targets": [0], "className": "text-center" }
    ],
    "footerCallback": function ( row, data, start, end, display ) {
        var api = this.api(), data;
        var intVal = function ( i ) {
            return typeof i === 'string' ?
                i.replace(/[\$,]/g, '')*1 :
                typeof i === 'number' ?
                    i : 0;
        };
        var invoice_total = api.column( 5, { page: 'none'} ).data().reduce( function (a, b) { return intVal(a) + intVal(b); }, 0 );
        var sales_due = api.column( 6, { page: 'none'} ).data().reduce( function (a, b) { return intVal(a) + intVal(b); }, 0 );
        var pur_ret = api.column( 7, { page: 'none'} ).data().reduce( function (a, b) { return intVal(a) + intVal(b); }, 0 );
        var total = api.column( 8, { page: 'none'} ).data().reduce( function (a, b) { return intVal(a) + intVal(b); }, 0 );
        $( api.column( 5 ).footer() ).html(to_Fixed(invoice_total));
        $( api.column( 6 ).footer() ).html(to_Fixed(sales_due));
        $( api.column( 7 ).footer() ).html(to_Fixed(pur_ret));
        $( api.column( 8 ).footer() ).html(to_Fixed(total));
    }
   });
   new $.fn.dataTable.FixedHeader( table );
}

$(document).ready(function() {
   load_datatable();
});

$('#show_account_payble').on('ifChanged', function(event) {
   $('#example2').DataTable().destroy();
    if(event.target.checked){
      load_datatable('checked');
    } else {
      load_datatable();
    }
});
</script>
<script>
  $('.mp-nav-item').removeClass('active');
  $('.suppliers_list-active-li').addClass('active');
  $('.suppliers_list-active-li').closest('.mp-nav-group').addClass('open');
</script>
