<!DOCTYPE html>
<html>
<head><?php $this->load->view('comman/code_css.php'); ?></head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar'); ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1><?= $page_title; ?><small>Group orders into a production run</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li><li><a href="<?= base_url('operations/production_schedule'); ?>">Production Schedule</a></li><li class="active"><?= $page_title; ?></li></ol>
  </section>
  <section class="content">
    <div class="row">
      <div class="col-md-8">
        <div class="box box-warning">
          <div class="box-header with-border"><h3 class="box-title"><?= $edit_batch ? 'Edit Batch' : 'New Production Batch'; ?></h3></div>
          <form id="batch-form">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
            <input type="hidden" name="id" value="<?= $edit_batch->id ?? ''; ?>">
            <div class="box-body">
              <div class="row">
                <div class="form-group col-md-6">
                  <label>Batch Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="batch_name" value="<?= isset($edit_batch) ? htmlspecialchars($edit_batch->batch_name) : ''; ?>" placeholder="e.g. Friday Morning Cakes" required>
                </div>
                <div class="form-group col-md-3">
                  <label>Batch Type</label>
                  <select class="form-control" name="batch_type">
                    <option value="bakery" <?= (isset($edit_batch) && $edit_batch->batch_type=='bakery')?'selected':''; ?>>Bakery</option>
                    <option value="kitchen" <?= (isset($edit_batch) && $edit_batch->batch_type=='kitchen')?'selected':''; ?>>Kitchen</option>
                    <option value="general" <?= (isset($edit_batch) && $edit_batch->batch_type=='general')?'selected':''; ?>>General</option>
                  </select>
                </div>
                <div class="form-group col-md-3">
                  <label>Status</label>
                  <select class="form-control" name="status">
                    <?php foreach(Production_batches_model::get_statuses() as $st): ?>
                    <option value="<?= $st; ?>" <?= (isset($edit_batch) && $edit_batch->status==$st)?'selected':''; ?>><?= Production_batches_model::status_label($st); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="row">
                <div class="form-group col-md-4">
                  <label>Scheduled Date <span class="text-danger">*</span></label>
                  <input type="date" class="form-control" name="scheduled_date" value="<?= isset($edit_batch) ? $edit_batch->scheduled_date : date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group col-md-4">
                  <label>Scheduled Time</label>
                  <input type="time" class="form-control" name="scheduled_time" value="<?= isset($edit_batch) ? $edit_batch->scheduled_time : ''; ?>">
                </div>
                <div class="form-group col-md-4">
                  <label>Equipment / Location</label>
                  <input type="text" class="form-control" name="equipment" value="<?= isset($edit_batch) ? htmlspecialchars($edit_batch->equipment) : ''; ?>" placeholder="e.g. Oven 1, Kitchen A">
                </div>
              </div>
              <div class="row">
                <div class="form-group col-md-6">
                  <label>Assigned Staff</label>
                  <select class="form-control select2" name="staff_id">
                    <option value="">-- Select --</option>
                    <?php foreach($staff as $s): ?>
                    <option value="<?= $s->id; ?>" <?= (isset($edit_batch) && $edit_batch->staff_id==$s->id)?'selected':''; ?>><?= htmlspecialchars($s->first_name . ' ' . $s->last_name); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group col-md-6">
                  <label>Notes</label>
                  <input type="text" class="form-control" name="notes" value="<?= isset($edit_batch) ? htmlspecialchars($edit_batch->notes) : ''; ?>" placeholder="Any special instructions">
                </div>
              </div>

              <hr>
              <h4><i class="fa fa-list"></i> Items in this Batch</h4>
              <div class="row">
                <div class="col-md-12">
                  <table class="table table-bordered table-condensed" id="batch-items-table">
                    <thead class="bg-gray"><tr><th>Item</th><th>Type</th><th>Qty</th><th>Notes</th><th style="width:40px;"></th></tr></thead>
                    <tbody id="batch-items-body">
                      <?php if(!empty($batch_items)): foreach($batch_items as $bi): ?>
                      <tr class="bi-row">
                        <td><input type="hidden" name="item_id[]" value="<?= $bi->item_id; ?>"><input type="hidden" name="item_name[]" value="<?= htmlspecialchars($bi->item_name); ?>"><span class="form-control-static"><?= htmlspecialchars($bi->item_name); ?></span></td>
                        <td><input type="hidden" name="item_type[]" value="<?= $bi->item_type; ?>"><span class="form-control-static"><?= $bi->item_type == 'recipe_product' ? 'Recipe' : ucfirst($bi->item_type); ?></span></td>
                        <td><input type="number" step="1" class="form-control input-sm" name="quantity[]" value="<?= $bi->quantity; ?>" style="width:80px;"></td>
                        <td><input type="text" class="form-control input-sm" name="item_notes[]" value="<?= htmlspecialchars($bi->notes); ?>"></td>
                        <td><button type="button" class="btn btn-xs btn-danger" onclick="$(this).closest('tr').remove()"><i class="fa fa-trash"></i></button></td>
                      </tr>
                      <?php endforeach; endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>

              <hr>
              <h4><i class="fa fa-plus-circle"></i> Add Pending Orders</h4>
              <div class="row">
                <div class="col-md-12">
                  <?php if(!empty($pending_orders)): ?>
                  <table class="table table-condensed table-striped">
                    <thead class="bg-gray"><tr><th>Order #</th><th>Customer</th><th>Item</th><th>Due Date</th><th style="width:60px;"></th></tr></thead>
                    <tbody>
                    <?php foreach($pending_orders as $po):
                      $already_in = false;
                      foreach($batch_items as $bi){ if($bi->item_id==$po->id && $bi->item_type=='custom_order'){ $already_in = true; break; } }
                      if($already_in) continue;
                    ?>
                      <tr>
                        <td><span class="label label-default"><?= htmlspecialchars($po->order_code); ?></span></td>
                        <td><?= htmlspecialchars($po->customer_name ?: '-'); ?></td>
                        <td><?= htmlspecialchars($po->item_name ?: '-'); ?></td>
                        <td><?= show_date($po->due_date); ?></td>
                        <td><button type="button" class="btn btn-xs btn-success" onclick="addBatchItem('custom_order', <?= $po->id; ?>, '<?= addslashes($po->item_name ?: $po->order_code); ?>')"><i class="fa fa-plus"></i></button></td>
                      </tr>
                    <?php endforeach; ?>
                    </tbody>
                  </table>
                  <?php else: ?>
                    <p class="text-muted">No pending custom orders. <a href="<?= base_url('operations/custom_orders'); ?>" target="_blank">Create a custom order</a> to add it here.</p>
                  <?php endif; ?>
                </div>
              </div>

              <hr>
              <h4><i class="fa fa-flask"></i> Produce from Recipe</h4>
              <div class="row">
                <div class="col-md-12">
                  <?php if(!empty($active_recipes)): ?>
                  <table class="table table-condensed table-striped">
                    <thead class="bg-gray"><tr><th>Recipe</th><th>Category</th><th>Yield</th><th style="width:60px;"></th></tr></thead>
                    <tbody>
                    <?php foreach($active_recipes as $rec):
                      $already_in = false;
                      foreach($batch_items as $bi){ if($bi->item_id==$rec->id && $bi->item_type=='recipe_product'){ $already_in = true; break; } }
                      if($already_in) continue;
                    ?>
                      <tr>
                        <td><?= htmlspecialchars($rec->name); ?></td>
                        <td><?= htmlspecialchars($rec->category ?: '-'); ?></td>
                        <td><?= $rec->yield_qty . ' ' . htmlspecialchars($rec->yield_unit); ?></td>
                        <td><button type="button" class="btn btn-xs btn-success" onclick="addBatchItem('recipe_product', <?= $rec->id; ?>, '<?= addslashes($rec->name); ?>')"><i class="fa fa-plus"></i></button></td>
                      </tr>
                    <?php endforeach; ?>
                    </tbody>
                  </table>
                  <?php else: ?>
                    <p class="text-muted">No active recipes available.</p>
                  <?php endif; ?>
                </div>
              </div>

            </div>
            <div class="box-footer">
              <button type="button" id="btn-save" class="btn btn-success">Save Batch</button>
              <a href="<?= base_url('operations/production_schedule'); ?>" class="btn btn-warning">Back to Schedule</a>
            </div>
          </form>
        </div>
      </div>

      <?php if(isset($edit_batch)): ?>
      <div class="col-md-4">
        <div class="box box-default">
          <div class="box-header"><h3 class="box-title">Quick Status Update</h3></div>
          <div class="box-body">
            <div class="btn-group-vertical" style="width:100%;">
              <?php foreach(Production_batches_model::get_statuses() as $st): ?>
              <button type="button" class="btn btn-default text-left status-btn" data-status="<?= $st; ?>" onclick="updateBatchStatus('<?= $st; ?>')">
                <i class="fa fa-circle text-<?= Production_batches_model::status_badge($st); ?>"></i> <?= Production_batches_model::status_label($st); ?>
                <?php if(isset($edit_batch) && $edit_batch->status==$st): ?><span class="pull-right"><i class="fa fa-check text-success"></i></span><?php endif; ?>
              </button>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </section>
</div>
<?php $this->load->view('footer'); ?>
</div>
<?php $this->load->view('comman/code_js.php'); ?>
<script>
$(function(){
  $('.select2').select2();
  <?php if(isset($preselect_order_id)): ?>
  // Auto-add preselected order from query param
  var $row = $('button[onclick*="<?= $preselect_order_id; ?>"]').closest('tr');
  if($row.length){
    var btn = $row.find('button.btn-success');
    if(btn.length) btn.trigger('click');
  }
  <?php endif; ?>
});

function addBatchItem(type, id, name){
  var typeLabel = type == 'recipe_product' ? 'Recipe' : type.charAt(0).toUpperCase()+type.slice(1);
  var html = '<tr class="bi-row">'+
    '<td><input type="hidden" name="item_id[]" value="'+id+'"><input type="hidden" name="item_name[]" value="'+name+'"><span class="form-control-static">'+name+'</span></td>'+
    '<td><input type="hidden" name="item_type[]" value="'+type+'"><span class="form-control-static">'+typeLabel+'</span></td>'+
    '<td><input type="number" step="1" class="form-control input-sm" name="quantity[]" value="1" style="width:80px;"></td>'+
    '<td><input type="text" class="form-control input-sm" name="item_notes[]" value=""></td>'+
    '<td><button type="button" class="btn btn-xs btn-danger" onclick="$(this).closest(\'tr\').remove()"><i class="fa fa-trash"></i></button></td>'+
    '</tr>';
  $('#batch-items-body').append(html);
}

$('#btn-save').on('click', function(){
  var $btn = $(this); $btn.prop('disabled', true).text('Saving...');
  $.post('<?= base_url('operations/production_batch_save'); ?>', $('#batch-form').serialize(), function(res){
    if(res.success){
      toastr.success(res.message);
      setTimeout(function(){ window.location.href = '<?= base_url('operations/production_schedule'); ?>'; }, 800);
    } else {
      toastr.error(res.message || 'Failed to save');
      $btn.prop('disabled', false).text('Save Batch');
    }
  }, 'json').fail(function(){ toastr.error('Server error'); $btn.prop('disabled', false).text('Save Batch'); });
});

function updateBatchStatus(status){
  $.post('<?= base_url('operations/production_batch_update_status'); ?>', {
    id: <?= $edit_batch->id ?? 0; ?>,
    status: status,
    "<?= $this->security->get_csrf_token_name(); ?>": "<?= $this->security->get_csrf_hash(); ?>"
  }, function(res){
    if(res.success){ toastr.success(res.message); location.reload(); }
    else { toastr.error(res.message || 'Failed'); }
  }, 'json').fail(function(xhr){
    toastr.error('Server error: ' + (xhr.responseText ? xhr.responseText.substring(0,200) : 'Could not reach server'));
  });
}
</script>
</body>
</html>
