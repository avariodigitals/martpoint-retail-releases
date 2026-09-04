<?php $this->load->view('finance/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Manage expense categories</div>
  </div>
  <?php if($CI->permissions('expense_category_add')) { ?>
  <a class="mp-qa-btn green" href="<?= base_url('expense/category_add'); ?>">
    <i class="fa fa-plus"></i> New Category
  </a>
  <?php } ?>
</div>

<div class="mp-table-wrap">
  <div class="box-body">
    <?= form_open('#', array('class' => '', 'id' => 'categories-list-form')); ?>
    <input type="hidden" id="base_url" value="<?= htmlspecialchars($base_url); ?>">
    <?php $this->load->view('comman/code_flashdata.php'); ?>
    <div class="mp-dt-scroll">
      <table id="example2" class="table mp-dt-table custom_hover" width="100%">
        <thead>
          <tr>
            <th class="text-center"><input type="checkbox" class="group_check checkbox"></th>
            <th><?= $this->lang->line('category_name'); ?></th>
            <th><?= $this->lang->line('description'); ?></th>
            <th><?= $this->lang->line('status'); ?></th>
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
$(document).ready(function() {
   var table = $('#example2').DataTable({
      "aLengthMenu": [[10, 25, 50, 100, 500], [10, 25, 50, 100, 500]],
      dom:'<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
      buttons: {
        buttons: [
            { className: 'btn bg-red color-palette btn-flat hidden delete_btn pull-left', text: 'Delete', action: function ( e, dt, node, config ) { multi_delete(); } },
            { extend: 'copy', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3]} },
            { extend: 'excel', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3]} },
            { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3]} },
            { extend: 'print', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3]} },
            { extend: 'csv', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2,3]} },
            { extend: 'colvis', className: 'btn bg-teal color-palette btn-flat', text:'Columns' }
        ]
      },
      "processing": true,
      "serverSide": true,
      "order": [],
      "responsive": false,
      language: { processing: '<div class="text-primary bg-primary" style="position: relative;z-index:100;overflow: visible;">Processing...</div>' },
      "ajax": {
          "url": "<?= base_url('expense/ajax_list_expense'); ?>",
          "type": "POST",
          complete: function (data) {
           $('.column_checkbox').iCheck({ checkboxClass: 'icheckbox_square-orange', radioClass: 'iradio_square-orange', increaseArea: '10%' });
           call_code();
          }
      },
      "columnDefs": [
        { "targets": [0,3,4], "orderable": false },
        { "targets": [0], "className": "text-center" }
      ]
  });
});
</script>
<script src="<?= htmlspecialchars($theme_link); ?>js/expense_category.js"></script>
<script>
  $('.mp-nav-item').removeClass('active');
  $('.expense-category-list-active-li').addClass('active');
  $('.expense-category-list-active-li').closest('.mp-nav-group').addClass('open');
</script>
