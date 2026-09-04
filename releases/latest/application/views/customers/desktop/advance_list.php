<?php $this->load->view('customers/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">View and manage customer advance payments</div>
  </div>
  <?php if($CI->permissions('cust_adv_payments_add')) { ?>
  <a class="mp-qa-btn green" href="<?= base_url('customers_advance/add'); ?>">
    <i class="fa fa-plus"></i> New Advance
  </a>
  <?php } ?>
</div>

<div class="mp-table-wrap box">
  <div class="box-body">
    <?= form_open('#', array('class' => '', 'id' => 'table_form')); ?>
    <input type="hidden" id="base_url" value="<?= htmlspecialchars($base_url); ?>">
    <table id="example2" class="table mp-dt-table" width="100%">
      <thead>
        <tr>
          <th class="text-center"><input type="checkbox" class="group_check checkbox"></th>
          <th><?= $this->lang->line('id'); ?></th>
          <th><?= $this->lang->line('date'); ?></th>
          <th><?= $this->lang->line('customer_name'); ?></th>
          <th><?= $this->lang->line('amount'); ?></th>
          <th><?= $this->lang->line('payment_type'); ?></th>
          <th><?= $this->lang->line('created_by'); ?></th>
          <th><?= $this->lang->line('action'); ?></th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
    <?= form_close(); ?>
  </div>
</div>

<script src="<?= htmlspecialchars($theme_link); ?>js/customers_advance/advance.js"></script>
<script type="text/javascript">
function load_datatable(){
   var table = $('#example2').DataTable({
    "aLengthMenu": [[10, 25, 50, 100, 500], [10, 25, 50, 100, 500]],
    dom:'<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
    buttons: {
        buttons: [
            { className: 'btn bg-red color-palette btn-flat hidden delete_btn pull-left', text: 'Delete', action: function ( e, dt, node, config ) { multi_delete(); } },
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
    "responsive": true,
    language: { processing: '<div class="text-primary bg-primary" style="position: relative;z-index:100;overflow: visible;">Processing...</div>' },
    "ajax": {
        "url": "<?= base_url('customers_advance/ajax_list'); ?>",
        "type": "POST",
        "data": function(d){
            d['<?= $this->security->get_csrf_token_name(); ?>'] = '<?= $this->security->get_csrf_hash(); ?>';
        },
        complete: function (data) {
            $('.column_checkbox').iCheck({ checkboxClass: 'icheckbox_square-orange', radioClass: 'iradio_square-orange', increaseArea: '10%' });
            call_code();
        }
    },
    "columnDefs": [
        { "targets": [0,7], "orderable": false },
        { "targets": [0], "className": "text-center" }
    ]
   });
   new $.fn.dataTable.FixedHeader( table );
}
$(document).ready(function(){
   load_datatable();
});
function print_receipt(id){
  window.open("<?= base_url('customers_advance/print_receipt/'); ?>"+id, "_blank", "scrollbars=1,resizable=1,height=500,width=500");
}
</script>
<script>
  $('.mp-nav-item').removeClass('active');
  $('.customers_advance_list-active-li').addClass('active');
  $('.customers_advance_list-active-li').closest('.mp-nav-group').addClass('open');
</script>
