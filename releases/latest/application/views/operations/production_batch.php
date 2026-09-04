<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.mp-section-divider{font-size:14px!important;font-weight:700!important;color:var(--mp-ink)!important;margin:24px 0 12px!important;padding-bottom:8px!important;border-bottom:1px solid var(--mp-border)!important;display:flex!important;align-items:center!important;gap:8px!important}
.mp-section-divider i{color:var(--mp-primary)!important}
.mp-batch-items{width:100%!important;border-collapse:collapse!important;margin-bottom:8px!important}
.mp-batch-items th{font-size:11px!important;text-transform:uppercase!important;font-weight:700!important;color:var(--mp-muted)!important;padding:10px 12px!important;border-bottom:1px solid var(--mp-border)!important;background:var(--mp-bg)!important;text-align:left!important}
.mp-batch-items td{padding:10px 12px!important;border-bottom:1px solid var(--mp-border)!important;font-size:13px!important;vertical-align:middle!important}
.mp-batch-items input[type=number],.mp-batch-items input[type=text]{border:1px solid var(--mp-border)!important;border-radius:8px!important;padding:7px 10px!important;font-size:13px!important;width:100%!important}
.mp-status-btn{display:flex!important;align-items:center!important;gap:10px!important;width:100%!important;padding:12px 16px!important;border:1px solid var(--mp-border)!important;border-radius:10px!important;background:var(--mp-surface)!important;color:var(--mp-ink)!important;font-size:14px!important;font-weight:600!important;cursor:pointer!important;margin-bottom:8px!important;transition:all .15s ease!important;text-align:left!important}
.mp-status-btn:hover{background:var(--mp-bg)!important}
.mp-status-btn.current{border-color:var(--mp-primary)!important;background:rgba(0,87,255,.04)!important}
.mp-status-btn .dot{width:10px;height:10px;border-radius:50%;flex-shrink:0}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Group orders into a production run</div>
  </div>
  <a href="<?= base_url('operations/production_schedule'); ?>" class="mp-qa-btn blue"><i class="fa fa-arrow-left"></i> Back to Schedule</a>
</div>

<div style="display:grid;grid-template-columns:minmax(0,1fr) <?= isset($edit_batch) ? '300px' : '0'; ?>;gap:24px;align-items:start;">
  <div class="mp-card-form" style="margin-bottom:0">
    <div class="mp-card-head"><h3><?= $edit_batch ? 'Edit Batch' : 'New Production Batch'; ?></h3></div>
    <div class="mp-card-body">
      <form id="batch-form">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" name="id" value="<?= $edit_batch->id ?? ''; ?>">

        <div class="mp-form-grid">
          <div class="mp-form-group" style="grid-column:span 2">
            <label>Batch Name <span class="text-danger">*</span></label>
            <input type="text" class="mp-form-control" name="batch_name" value="<?= isset($edit_batch) ? htmlspecialchars($edit_batch->batch_name) : ''; ?>" placeholder="e.g. Friday Morning Cakes" required>
          </div>
          <div class="mp-form-group">
            <label>Batch Type</label>
            <select class="mp-form-control" name="batch_type">
              <option value="bakery" <?= (isset($edit_batch) && $edit_batch->batch_type=='bakery')?'selected':''; ?>>Bakery</option>
              <option value="kitchen" <?= (isset($edit_batch) && $edit_batch->batch_type=='kitchen')?'selected':''; ?>>Kitchen</option>
              <option value="general" <?= (isset($edit_batch) && $edit_batch->batch_type=='general')?'selected':''; ?>>General</option>
            </select>
          </div>
          <div class="mp-form-group">
            <label>Status</label>
            <select class="mp-form-control" name="status">
              <?php foreach(Production_batches_model::get_statuses() as $st): ?>
              <option value="<?= $st; ?>" <?= (isset($edit_batch) && $edit_batch->status==$st)?'selected':''; ?>><?= Production_batches_model::status_label($st); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="mp-form-grid" style="margin-top:20px;">
          <div class="mp-form-group"><label>Scheduled Date <span class="text-danger">*</span></label><input type="date" class="mp-form-control" name="scheduled_date" value="<?= isset($edit_batch) ? $edit_batch->scheduled_date : date('Y-m-d'); ?>" required></div>
          <div class="mp-form-group"><label>Scheduled Time</label><input type="time" class="mp-form-control" name="scheduled_time" value="<?= isset($edit_batch) ? $edit_batch->scheduled_time : ''; ?>"></div>
          <div class="mp-form-group full"><label>Equipment / Location</label><input type="text" class="mp-form-control" name="equipment" value="<?= isset($edit_batch) ? htmlspecialchars($edit_batch->equipment) : ''; ?>" placeholder="e.g. Oven 1, Kitchen A"></div>
        </div>

        <div class="mp-form-grid" style="margin-top:20px;">
          <div class="mp-form-group">
            <label>Assigned Staff</label>
            <select class="mp-form-control select2" name="staff_id">
              <option value="">-- Select --</option>
              <?php foreach($staff as $s): ?>
              <option value="<?= $s->id; ?>" <?= (isset($edit_batch) && $edit_batch->staff_id==$s->id)?'selected':''; ?>><?= htmlspecialchars($s->first_name . ' ' . $s->last_name); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mp-form-group full">
            <label>Notes</label>
            <input type="text" class="mp-form-control" name="notes" value="<?= isset($edit_batch) ? htmlspecialchars($edit_batch->notes) : ''; ?>" placeholder="Any special instructions">
          </div>
        </div>

        <div class="mp-section-divider"><i class="fa fa-list"></i> Items in this Batch</div>
        <table class="mp-batch-items" id="batch-items-table">
          <thead><tr><th>Item</th><th>Type</th><th>Qty</th><th>Notes</th><th style="width:40px;"></th></tr></thead>
          <tbody id="batch-items-body">
            <?php if(!empty($batch_items)): foreach($batch_items as $bi): ?>
            <tr class="bi-row">
              <td><input type="hidden" name="item_id[]" value="<?= $bi->item_id; ?>"><input type="hidden" name="item_name[]" value="<?= htmlspecialchars($bi->item_name); ?>"><?= htmlspecialchars($bi->item_name); ?></td>
              <td><input type="hidden" name="item_type[]" value="<?= $bi->item_type; ?>"><?= $bi->item_type == 'recipe_product' ? 'Recipe' : ucfirst($bi->item_type); ?></td>
              <td><input type="number" step="1" name="quantity[]" value="<?= $bi->quantity; ?>" style="width:80px"></td>
              <td><input type="text" name="item_notes[]" value="<?= htmlspecialchars($bi->notes); ?>"></td>
              <td><div class="mp-actions"><button type="button" class="mp-delete" onclick="$(this).closest('tr').remove()"><i class="fa fa-trash"></i></button></div></td>
            </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>

        <div class="mp-section-divider"><i class="fa fa-plus-circle"></i> Add Pending Orders</div>
        <?php if(!empty($pending_orders)): ?>
        <table class="mp-static-table" style="font-size:12px">
          <thead><tr><th>Order #</th><th>Customer</th><th>Item</th><th>Due Date</th><th></th></tr></thead>
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
              <td><div class="mp-actions"><button type="button" class="mp-edit" onclick="addBatchItem('custom_order', <?= $po->id; ?>, '<?= addslashes($po->item_name ?: $po->order_code); ?>')"><i class="fa fa-plus"></i></button></div></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php else: ?>
          <p style="color:var(--mp-muted);font-size:13px">No pending custom orders. <a href="<?= base_url('operations/custom_orders'); ?>" target="_blank">Create a custom order</a> to add it here.</p>
        <?php endif; ?>

        <div class="mp-section-divider"><i class="fa fa-flask"></i> Produce from Recipe</div>
        <?php if(!empty($active_recipes)): ?>
        <table class="mp-static-table" style="font-size:12px">
          <thead><tr><th>Recipe</th><th>Category</th><th>Yield</th><th></th></tr></thead>
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
              <td><div class="mp-actions"><button type="button" class="mp-edit" onclick="addBatchItem('recipe_product', <?= $rec->id; ?>, '<?= addslashes($rec->name); ?>')"><i class="fa fa-plus"></i></button></div></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php else: ?>
          <p style="color:var(--mp-muted);font-size:13px">No active recipes available.</p>
        <?php endif; ?>

        <div class="mp-form-actions" style="margin-top:24px;">
          <button type="button" id="btn-save" class="mp-btn-primary"><i class="fa fa-check"></i> Save Batch</button>
          <a href="<?= base_url('operations/production_schedule'); ?>" class="mp-btn-secondary">Back to Schedule</a>
        </div>
      </form>
    </div>
  </div>

  <?php if(isset($edit_batch)): ?>
  <div class="mp-card-form" style="margin-bottom:0">
    <div class="mp-card-head"><h3>Quick Status</h3></div>
    <div class="mp-card-body">
      <?php foreach(Production_batches_model::get_statuses() as $st):
        $badge = Production_batches_model::status_badge($st);
        $colors = ['success'=>'#059669','danger'=>'#DC2626','info'=>'#0057FF','warning'=>'#F59E0B','default'=>'#78716C'];
        $c = $colors[$badge] ?? '#78716C';
      ?>
      <button type="button" class="mp-status-btn <?= ($edit_batch->status==$st)?'current':''; ?>" onclick="updateBatchStatus('<?= $st; ?>')">
        <span class="dot" style="background:<?= $c; ?>"></span>
        <?= Production_batches_model::status_label($st); ?>
        <?php if($edit_batch->status==$st): ?><i class="fa fa-check" style="margin-left:auto;color:var(--mp-success)"></i><?php endif; ?>
      </button>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<script>
$(function(){
  $('.select2').select2();
  <?php if(isset($preselect_order_id)): ?>
  var $row = $('button[onclick*="<?= $preselect_order_id; ?>"]').closest('tr');
  if($row.length){ var btn = $row.find('button.mp-edit'); if(btn.length) btn.trigger('click'); }
  <?php endif; ?>
});

function addBatchItem(type, id, name){
  var typeLabel = type == 'recipe_product' ? 'Recipe' : type.charAt(0).toUpperCase()+type.slice(1);
  var html = '<tr class="bi-row">'+
    '<td><input type="hidden" name="item_id[]" value="'+id+'"><input type="hidden" name="item_name[]" value="'+name+'">'+name+'</td>'+
    '<td><input type="hidden" name="item_type[]" value="'+type+'">'+typeLabel+'</td>'+
    '<td><input type="number" step="1" name="quantity[]" value="1" style="width:80px"></td>'+
    '<td><input type="text" name="item_notes[]" value=""></td>'+
    '<td><div class="mp-actions"><button type="button" class="mp-delete" onclick="$(this).closest(\'tr\').remove()"><i class="fa fa-trash"></i></button></div></td>'+
    '</tr>';
  $('#batch-items-body').append(html);
}

$('#btn-save').on('click', function(){
  var $btn = $(this); $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
  $.post('<?= base_url('operations/production_batch_save'); ?>', $('#batch-form').serialize(), function(res){
    if(res.success){
      toastr.success(res.message);
      setTimeout(function(){ window.location.href = '<?= base_url('operations/production_schedule'); ?>'; }, 800);
    } else {
      toastr.error(res.message || 'Failed to save');
      $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Save Batch');
    }
  }, 'json').fail(function(){ toastr.error('Server error'); $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Save Batch'); });
});

function updateBatchStatus(status){
  $.post('<?= base_url('operations/production_batch_update_status'); ?>', {
    id: <?= $edit_batch->id ?? 0; ?>, status: status
  }, function(res){
    if(res.success){ toastr.success(res.message); location.reload(); }
    else { toastr.error(res.message || 'Failed'); }
  }, 'json').fail(function(xhr){
    toastr.error('Server error: ' + (xhr.responseText ? xhr.responseText.substring(0,200) : 'Could not reach server'));
  });
}
</script>
