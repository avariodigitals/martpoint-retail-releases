<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Configure recipe category types</div>
  </div>
  <a href="<?= base_url('operations/recipe_category_update'); ?>" class="mp-qa-btn green"><i class="fa fa-plus"></i> New Category</a>
</div>

<?= form_open('#', ['id' => 'table_form']); ?>
<div class="mp-table-wrap">
  <div class="mp-card-head"><h3><?= htmlspecialchars($page_title); ?></h3></div>
  <div class="mp-dt-scroll">
    <table id="example2" class="table mp-dt-table" width="100%">
      <thead>
        <tr>
          <th class="text-center"><input type="checkbox" class="group_check checkbox"></th>
          <th>Category Name</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>
<?= form_close(); ?>

<script>
$(document).ready(function() {
  var table = $('#example2').DataTable({
    "aLengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
    dom:'<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
    buttons: {
      buttons: [
        { className: 'btn bg-red color-palette btn-flat hidden delete_btn pull-left', text: 'Delete', action: function(e,dt,node,config){ multi_delete(); } },
        { extend: 'copy', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2]} },
        { extend: 'excel', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2]} },
        { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2]} },
        { extend: 'print', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: [1,2]} },
      ]
    },
    "processing": true, "serverSide": true, "order": [],
    "responsive": true,
    language: { processing: '<div class="text-primary bg-primary" style="position: relative;z-index:100;overflow: visible;">Processing...</div>' },
    "ajax": { "url": "<?= site_url('operations/recipe_categories_ajax'); ?>", "type": "POST",
      complete: function(data) {
        $('.column_checkbox').iCheck({ checkboxClass: 'icheckbox_square-orange', radioClass: 'iradio_square-orange', increaseArea: '10%' });
        call_code();
      }
    },
    "columnDefs": [{ "targets": [0,3], "orderable": false }, { "targets": [0], "className": "text-center" }]
  });
  new $.fn.dataTable.FixedHeader(table);
});
function update_status(id, status) {
  $.post("<?= base_url('operations/recipe_category_update_status'); ?>", { id: id, status: status, "<?= $this->security->get_csrf_token_name(); ?>": "<?= $this->security->get_csrf_hash(); ?>" }, function(data) {
    if(data.trim() == 'success') { toastr.success('Status Updated Successfully!'); $('#example2').DataTable().ajax.reload(); }
    else { toastr.error('Failed to update status'); }
  });
}
function delete_category(id) {
  if(confirm('Are you sure?')) {
    $.post("<?= base_url('operations/recipe_category_delete'); ?>", { q_id: id, "<?= $this->security->get_csrf_token_name(); ?>": "<?= $this->security->get_csrf_hash(); ?>" }, function(data) {
      if(data.trim() == 'success') { toastr.success('Deleted Successfully!'); $('#example2').DataTable().ajax.reload(); }
      else { toastr.error(data); }
    });
  }
}
function multi_delete() {
  var ids = [];
  $('.column_checkbox:checked').each(function(){ ids.push($(this).val()); });
  if(ids.length == 0) { toastr.error('Please select at least one record'); return; }
  if(confirm('Are you sure?')) {
    $.post("<?= base_url('operations/recipe_category_multi_delete'); ?>", { checkbox: ids, "<?= $this->security->get_csrf_token_name(); ?>": "<?= $this->security->get_csrf_hash(); ?>" }, function(data) {
      if(data.trim() == 'success') { toastr.success('Deleted Successfully!'); $('#example2').DataTable().ajax.reload(); }
      else { toastr.error(data); }
    });
  }
}
</script>
