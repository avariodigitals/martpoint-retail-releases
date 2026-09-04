<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<?php
$crud = $crud ?? [];
$page_title = $crud['page_title'] ?? ($page_title ?? 'List');
$page_sub = $crud['page_sub'] ?? '';
$add_url = $crud['add_url'] ?? '';
$add_label = $crud['add_label'] ?? 'New';
$add_permission = $crud['add_permission'] ?? '';
$columns = $crud['columns'] ?? [];
$rows = $rows ?? [];
$table_id = $crud['table_id'] ?? 'crudTable';
$ajax_url = $crud['ajax_url'] ?? '';
$module = $crud['module'] ?? 'crud';
$status_url = $crud['status_url'] ?? '';
$delete_url = $crud['delete_url'] ?? '';
$delete_param = $crud['delete_param'] ?? 'q_id';
$multi_delete_url = $crud['multi_delete_url'] ?? '';
$edit_url = $crud['edit_url'] ?? '';
$delete_permission = $crud['delete_permission'] ?? '';
$edit_permission = $crud['edit_permission'] ?? '';
$bulk_delete = $crud['bulk_delete'] ?? false;

$orderable_targets = [];
$center_targets = [];
$export_columns = [];
$has_checkbox = false;
foreach ($columns as $idx => $col) {
    $type = $col['type'] ?? 'text';
    if (in_array($type, ['checkbox', 'actions'])) {
        $orderable_targets[] = $idx;
    }
    if ($type === 'checkbox' || $type === 'actions' || $type === 'status') {
        $center_targets[] = $idx;
    }
    if ($type !== 'checkbox' && $type !== 'actions') {
        $export_columns[] = $idx;
    }
    if ($type === 'checkbox' && $bulk_delete) {
        $has_checkbox = true;
    }
}
?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub"><?= htmlspecialchars($page_sub); ?></div>
  </div>
  <?php if ($add_url && (empty($add_permission) || $CI->permissions($add_permission))): ?>
  <a href="<?= $add_url; ?>" class="mp-qa-btn green"><i class="fa fa-plus"></i> <?= htmlspecialchars($add_label); ?></a>
  <?php endif; ?>
</div>

<div class="mp-table-wrap">
  <div class="mp-card-head">
    <h3><?= htmlspecialchars($page_title); ?></h3>
  </div>
  <div class="box-body">
    <div class="mp-dt-scroll">
      <?= form_open('#', ['id' => 'table_form']); ?>
      <input type="hidden" id="base_url" value="<?= $base_url; ?>">
      <table id="<?= $table_id; ?>" class="table mp-dt-table" width="100%">
        <thead>
          <tr>
            <?php foreach ($columns as $col): ?>
              <?php if (($col['type'] ?? 'text') === 'checkbox' && $bulk_delete): ?>
                <th class="text-center"><input type="checkbox" class="group_check checkbox"></th>
              <?php else: ?>
                <th><?= htmlspecialchars($col['title']); ?></th>
              <?php endif; ?>
            <?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $row): ?>
          <tr>
            <?php foreach ($columns as $col):
              $type = $col['type'] ?? 'text';
              $field = $col['field'] ?? '';
              $value = $field ? ($row->{$field} ?? '') : '';
            ?>
              <?php if ($type === 'checkbox' && $bulk_delete): ?>
                <td class="text-center"><input type="checkbox" name="checkbox[]" value="<?= (int)$row->id; ?>" class="checkbox column_checkbox"></td>
              <?php elseif ($type === 'status'): ?>
                <td class="text-center">
                  <?php if ($value == 1): ?>
                    <?php if ($status_url): ?>
                      <span onclick="update_status(<?= (int)$row->id; ?>,0)" id="span_<?= (int)$row->id; ?>" class="label label-success" style="cursor:pointer">Active</span>
                    <?php else: ?>
                      <span class="label label-success">Active</span>
                    <?php endif; ?>
                  <?php else: ?>
                    <?php if ($status_url): ?>
                      <span onclick="update_status(<?= (int)$row->id; ?>,1)" id="span_<?= (int)$row->id; ?>" class="label label-danger" style="cursor:pointer">Inactive</span>
                    <?php else: ?>
                      <span class="label label-danger">Inactive</span>
                    <?php endif; ?>
                  <?php endif; ?>
                </td>
              <?php elseif ($type === 'actions'): ?>
                <td>
                  <div class="mp-actions">
                    <?php if ($edit_url && (empty($edit_permission) || $CI->permissions($edit_permission))): ?>
                      <a href="<?= str_replace('{id}', (int)$row->id, $edit_url); ?>" class="mp-edit" title="Edit"><i class="fa fa-pencil"></i></a>
                    <?php endif; ?>
                    <?php if ($delete_url && (empty($delete_permission) || $CI->permissions($delete_permission))): ?>
                      <button type="button" class="mp-delete" title="Delete" onclick="delete_<?= $module; ?>(<?= (int)$row->id; ?>)"><i class="fa fa-trash"></i></button>
                    <?php endif; ?>
                  </div>
                </td>
              <?php elseif ($type === 'custom'): ?>
                <td>
                  <?php if (isset($col['callback']) && is_callable($col['callback'])): ?>
                    <?= $col['callback']($row); ?>
                  <?php endif; ?>
                </td>
              <?php elseif ($type === 'raw'): ?>
                <td><?= $value; ?></td>
              <?php else: ?>
                <td><?= htmlspecialchars($value); ?></td>
              <?php endif; ?>
            <?php endforeach; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?= form_close(); ?>
    </div>
  </div>
</div>

<script type="text/javascript">
  $(document).ready(function() {
    var table = $('#<?= $table_id; ?>').DataTable({
      "aLengthMenu": [[10, 25, 50, 100, 500], [10, 25, 50, 100, 500]],
      dom: '<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
      buttons: {
        buttons: [
          <?php if ($bulk_delete && $multi_delete_url): ?>
          {
            className: 'btn bg-red color-palette btn-flat hidden delete_btn pull-left',
            text: 'Delete',
            action: function (e, dt, node, config) { multi_delete(); }
          },
          <?php endif; ?>
          { extend: 'copy', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: <?= json_encode($export_columns); ?> } },
          { extend: 'excel', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: <?= json_encode($export_columns); ?> } },
          { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: <?= json_encode($export_columns); ?> } },
          { extend: 'print', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: <?= json_encode($export_columns); ?> } },
          { extend: 'csv', className: 'btn bg-teal color-palette btn-flat', exportOptions: { columns: <?= json_encode($export_columns); ?> } },
          { extend: 'colvis', className: 'btn bg-teal color-palette btn-flat', text: 'Columns' }
        ]
      },
      "processing": true,
      "serverSide": <?= $ajax_url ? 'true' : 'false'; ?>,
      "order": [],
      "responsive": false,
      <?php if ($ajax_url): ?>
      "ajax": {
        "url": "<?= $ajax_url; ?>",
        "type": "POST",
        "data": function(d) {
          d.<?= $this->security->get_csrf_token_name(); ?> = "<?= $this->security->get_csrf_hash(); ?>";
        }
      },
      <?php endif; ?>
      language: {
        processing: '<div class="text-primary bg-primary" style="position: relative;z-index:100;overflow: visible;">Processing...</div>'
      },
      "columnDefs": [
        { "targets": <?= json_encode($orderable_targets); ?>, "orderable": false },
        { "targets": <?= json_encode($center_targets); ?>, "className": "text-center" }
      ]
    });
    new $.fn.dataTable.FixedHeader(table);
  });

  <?php if ($status_url): ?>
  function update_status(id, status) {
    var new_status = status == 1 ? 'Active' : 'Inactive';
    var new_class = status == 1 ? 'label label-success' : 'label label-danger';
    var next_status = status == 1 ? 0 : 1;
    $.post("<?= $base_url; ?><?= $status_url; ?>", { id: id, status: status }, function(result) {
      if (result === 'success') {
        toastr["success"]("Status updated successfully.");
        $('#span_' + id).attr('class', new_class).html(new_status).attr('onclick', 'update_status(' + id + ',' + next_status + ')');
      } else if (result === 'failed') {
        toastr["error"]("Failed to update status.");
      } else {
        toastr["error"](result);
      }
    });
  }
  <?php endif; ?>

  <?php if ($delete_url): ?>
  function delete_<?= $module; ?>(q_id) {
    if (!confirm("Are you sure you want to delete this record?")) return;
    var base_url = $("#base_url").val();
    $(".mp-table-wrap").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    var postData = {}; postData['<?= $delete_param; ?>'] = q_id;
    $.post(base_url + "<?= $delete_url; ?>", postData, function(result) {
      if (result === 'success') {
        toastr["success"]("Record deleted successfully.");
        setTimeout(function(){ window.location.reload(); }, 600);
      } else if (result === 'failed') {
        toastr["error"]("Failed to delete record.");
        $(".overlay").remove();
      } else {
        toastr["error"](result);
        $(".overlay").remove();
      }
    });
  }
  <?php endif; ?>

  <?php if ($bulk_delete && $multi_delete_url): ?>
  function multi_delete() {
    var base_url = $("#base_url").val();
    if (!confirm("Are you sure?")) return;
    var data = new FormData($('#table_form')[0]);
    if (!xss_validation(data)) { return false; }
    $(".mp-table-wrap").append('<div class="overlay"><i class="fa fa-refresh fa-spin"></i></div>');
    $.ajax({
      type: 'POST',
      url: base_url + "<?= $multi_delete_url; ?>",
      data: data,
      cache: false,
      contentType: false,
      processData: false,
      success: function(result) {
        if (result === 'success') {
          toastr["success"]("Records deleted successfully.");
          setTimeout(function(){ window.location.reload(); }, 600);
        } else if (result === 'failed') {
          toastr["error"]("Failed to delete records.");
          $(".overlay").remove();
        } else {
          toastr["error"](result);
          $(".overlay").remove();
        }
      }
    });
  }
  <?php endif; ?>
</script>

<script>
  $(document).on('change', '.group_check', function(){
    var table = $('#<?= $table_id; ?>');
    table.find('.column_checkbox').prop('checked', this.checked);
    if (this.checked) { $('.delete_btn').removeClass('hidden'); }
    else { $('.delete_btn').addClass('hidden'); }
  });
  $(document).on('change', '.column_checkbox', function(){
    var any = $('#<?= $table_id; ?> .column_checkbox:checked').length > 0;
    if (any) { $('.delete_btn').removeClass('hidden'); }
    else { $('.delete_btn').addClass('hidden'); }
  });

  $(".<?= $module; ?>-list-active-li").addClass("active");
  $(".<?= $module; ?>-list-active-li").closest(".mp-nav-group").addClass("open");
  // Fallback: if no list-specific class, use the generic module class
  if (!$(".<?= $module; ?>-list-active-li").length) {
    $(".<?= $module; ?>-active-li").addClass("active").closest(".mp-nav-group").addClass("open");
  }
</script>
