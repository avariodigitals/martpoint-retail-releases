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
            <select class="mp-form-control" name="batch_type" id="batch_type">
              <?php
                $pb_profile = function_exists('mp_get_store_profile') ? mp_get_store_profile() : [];
                $pb_is_perfumery = (($pb_profile['industry_type'] ?? '') === 'perfume_shop');
              ?>
              <?php if ($pb_is_perfumery): ?>
              <option value="blending" <?= (!isset($edit_batch) || $edit_batch->batch_type=='blending')?'selected':''; ?>>Blending</option>
              <option value="bottling" <?= (isset($edit_batch) && $edit_batch->batch_type=='bottling')?'selected':''; ?>>Bottling Run</option>
              <?php else: ?>
              <option value="bakery" <?= (isset($edit_batch) && $edit_batch->batch_type=='bakery')?'selected':''; ?>>Bakery</option>
              <option value="kitchen" <?= (isset($edit_batch) && $edit_batch->batch_type=='kitchen')?'selected':''; ?>>Kitchen</option>
              <?php endif; ?>
              <option value="general" <?= (isset($edit_batch) && $edit_batch->batch_type=='general')?'selected':''; ?>>General</option>
            </select>
          </div>
          <div class="mp-form-group">
            <label>Status</label>
            <select class="mp-form-control" name="status" id="batch_status">
              <?php foreach(Production_batches_model::get_statuses(null, isset($edit_batch) ? $edit_batch->batch_type : 'blending') as $st): ?>
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
        <?php if(isset($edit_batch) && $edit_batch->status === 'completed'): ?>
        <div class="alert alert-info" style="font-size:13px">This batch is completed — its stock was already posted and quantities can no longer change it. To produce more, create a new batch.</div>
        <?php endif; ?>
        <table class="mp-batch-items" id="batch-items-table">
          <thead><tr><th>Item</th><th>Type</th><th>Qty</th><th>Notes</th><th style="width:40px;"></th></tr></thead>
          <tbody id="batch-items-body">
            <?php if(!empty($batch_items)): foreach($batch_items as $bi):
              $bi_is_completed = isset($edit_batch) && $edit_batch->status === 'completed';
              $bi_unit_hint = '';
              if ($bi->item_type == 'recipe_product') {
                $bi_recipe = $this->db->select('yield_unit')->where('id', $bi->item_id)->get('db_recipes')->row();
                if ($bi_recipe && $bi_recipe->yield_unit) $bi_unit_hint = ' <small class="text-muted">'.htmlspecialchars($bi_recipe->yield_unit).' to produce</small>';
              }
            ?>
            <tr class="bi-row">
              <td><input type="hidden" name="item_id[]" value="<?= $bi->item_id; ?>"><input type="hidden" name="item_name[]" value="<?= htmlspecialchars($bi->item_name); ?>"><?= htmlspecialchars($bi->item_name); ?></td>
              <td><input type="hidden" name="item_type[]" value="<?= $bi->item_type; ?>"><?= $bi->item_type == 'recipe_product' ? 'Recipe' : ucfirst($bi->item_type); ?></td>
              <td><input type="number" step="0.01" name="quantity[]" value="<?= $bi->quantity; ?>" style="width:80px" <?= $bi_is_completed ? 'readonly' : ''; ?>><?= $bi_unit_hint; ?></td>
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
              <td><div class="mp-actions"><button type="button" class="mp-edit" onclick="addBatchItem('recipe_product', <?= $rec->id; ?>, '<?= addslashes($rec->name); ?>', <?= (float)($rec->yield_qty ?? 1); ?>, '<?= addslashes($rec->yield_unit ?: ''); ?>')"><i class="fa fa-plus"></i></button></div></td>
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
      <?php foreach(Production_batches_model::get_statuses(null, $edit_batch->batch_type) as $st):
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

<?php if (!empty($is_perfumery) && isset($edit_batch)): ?>
<div class="mp-card-form" style="margin-top:24px">
  <div class="mp-card-head"><h3><i class="fa fa-eyedropper"></i> Bottling Runs</h3></div>
  <div class="mp-card-body">
    <p class="mp-form-hint" style="margin-top:0">Record how this blend was filled into bottles. Each run takes the empty bottles and the bulk liquid out of stock and puts the finished product in.</p>

    <form id="bottling-form">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
      <input type="hidden" name="batch_id" value="<?= $edit_batch->id; ?>">
      <div class="mp-form-grid">
        <div class="mp-form-group" style="grid-column:span 2">
          <label>Finished Product <span class="text-danger">*</span></label>
          <select class="mp-form-control select2" name="product_item_id" id="btl-product">
            <option value="">-- e.g. Oud Royale 50ml --</option>
            <?php foreach($bottle_products as $p): ?>
            <option value="<?= $p->id; ?>" data-fill="<?= htmlspecialchars($p->fill_qty ?? ''); ?>" data-bottle="<?= htmlspecialchars($p->bottle_item_id ?? ''); ?>"><?= htmlspecialchars($p->item_name); ?><?= !empty($p->fill_qty) ? ' — ' . format_qty($p->fill_qty) . ' ml fill' : ''; ?></option>
            <?php endforeach; ?>
          </select>
          <p class="mp-form-hint">Sellable SKU — picking one fills in its bottle and volume automatically (set on the item card)</p>
        </div>
        <div class="mp-form-group" style="grid-column:span 2">
          <label>Bottle / Packaging <span class="text-danger">*</span></label>
          <select class="mp-form-control select2" name="bottle_item_id" id="btl-bottle">
            <option value="">-- e.g. 50ml glass bottle --</option>
            <?php foreach($bottle_items as $p): ?>
            <option value="<?= $p->id; ?>" data-cap="<?= htmlspecialchars($p->capacity_ml ?? ''); ?>" data-stock="<?= $p->stock; ?>"><?= htmlspecialchars($p->item_name); ?><?= !empty($p->capacity_ml) ? ' ' . format_qty($p->capacity_ml) . 'ml' : ''; ?> (<?= format_qty($p->stock); ?> in stock)</option>
            <?php endforeach; ?>
          </select>
          <p class="mp-form-hint">Raw material / packaging item consumed, one per bottle</p>
        </div>
        <div class="mp-form-group" style="grid-column:span 2">
          <label>Bulk Liquid Drawn From</label>
          <select class="mp-form-control select2" name="bulk_item_id" id="btl-bulk">
            <option value="" data-unit="units" data-stock="">— No bulk deduction —</option>
            <?php foreach($bulk_items as $p): ?>
            <option value="<?= $p->id; ?>" data-unit="<?= htmlspecialchars($p->unit_name ?: 'units'); ?>" data-stock="<?= $p->available; ?>"><?= htmlspecialchars($p->item_name); ?> — <?= format_qty($p->available); ?> <?= htmlspecialchars($p->unit_name ?: ''); ?> in stock</option>
            <?php endforeach; ?>
          </select>
          <p class="mp-form-hint" id="btl-remaining">Produced by this batch's formulas — complete the batch first so bulk stock exists</p>
        </div>
        <div class="mp-form-group">
          <label>Volume per Bottle (<span id="btl-fill-unit">units</span>)</label>
          <input type="number" step="0.001" min="0" class="mp-form-control" name="fill_qty" id="btl-fill" placeholder="e.g. 50">
        </div>
        <div class="mp-form-group">
          <label>Bottles Filled <span class="text-danger">*</span></label>
          <input type="number" step="1" min="1" class="mp-form-control" name="bottles_filled" id="btl-count" placeholder="e.g. 20" required>
        </div>
        <div class="mp-form-group" style="grid-column:span 2">
          <label>Notes</label>
          <input type="text" class="mp-form-control" name="notes" placeholder="e.g. Filled after filtering, 1 tester kept aside">
        </div>
      </div>
      <div id="btl-preview" class="mp-form-hint" style="margin:4px 0 12px;font-weight:600"></div>
      <button type="button" id="btn-bottle" class="mp-btn-primary"><i class="fa fa-check"></i> Record Bottling Run</button>
    </form>

    <?php if(!empty($bottling_runs)): ?>
    <div class="mp-section-divider"><i class="fa fa-history"></i> Runs on this Batch</div>
    <table class="mp-static-table" style="font-size:12px">
      <thead><tr><th>Date</th><th>Product</th><th>Bottles</th><th>Bulk Used</th><th>Bottle Item</th><th>Cost</th></tr></thead>
      <tbody>
      <?php foreach($bottling_runs as $r): ?>
        <tr>
          <td><?= show_date($r->created_date); ?></td>
          <td><?= htmlspecialchars($r->product_name); ?></td>
          <td><?= format_qty($r->bottles_filled); ?></td>
          <td><?= $r->bulk_name ? format_qty($r->bulk_used) . ' ' . htmlspecialchars($r->bulk_name) : '-'; ?></td>
          <td><?= htmlspecialchars($r->bottle_name); ?></td>
          <td><?= number_format($r->total_cost, 2); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<script>
$(function(){
  $('.select2').select2();
  <?php if(isset($preselect_order_id)): ?>
  var $row = $('button[onclick*="<?= $preselect_order_id; ?>"]').closest('tr');
  if($row.length){ var btn = $row.find('button.mp-edit'); if(btn.length) btn.trigger('click'); }
  <?php endif; ?>
});

function addBatchItem(type, id, name, defaultQty, unit){
  var typeLabel = type == 'recipe_product' ? 'Recipe' : type.charAt(0).toUpperCase()+type.slice(1);
  var qty = (typeof defaultQty !== 'undefined' && defaultQty !== null && defaultQty !== '') ? defaultQty : 1;
  // For recipe rows the qty is the amount to produce in the recipe's yield unit
  // (e.g. 2000 ml for a 2-litre blend) — show the unit so it can't be misread as "number of runs"
  var unitHint = (type == 'recipe_product' && unit) ? ' <small class="text-muted">'+unit+' to produce</small>' : '';
  var html = '<tr class="bi-row">'+
    '<td><input type="hidden" name="item_id[]" value="'+id+'"><input type="hidden" name="item_name[]" value="'+name+'">'+name+'</td>'+
    '<td><input type="hidden" name="item_type[]" value="'+type+'">'+typeLabel+'</td>'+
    '<td><input type="number" step="0.01" name="quantity[]" value="'+qty+'" style="width:80px">'+unitHint+'</td>'+
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

<?php if ($pb_is_perfumery): ?>
// A Bottling Run has its own short pipeline — rebuild the status list when the type changes
var pbStatusSets = {
  blending: [['planned','Planned'],['sourcing','Sourcing Materials'],['blending','Blending'],['macerating','Macerating'],['filtering','Filtering'],['bottling','Bottling'],['ready','Ready'],['completed','Completed'],['cancelled','Cancelled']],
  bottling: [['planned','Planned'],['bottling','Bottling'],['ready','Ready'],['completed','Completed'],['cancelled','Cancelled']],
  general:  [['planned','Planned'],['sourcing','Sourcing Materials'],['blending','Blending'],['macerating','Macerating'],['filtering','Filtering'],['bottling','Bottling'],['ready','Ready'],['completed','Completed'],['cancelled','Cancelled']]
};
$('#batch_type').on('change', function(){
  var set = pbStatusSets[$(this).val()] || pbStatusSets.general;
  var $st = $('#batch_status');
  var cur = $st.val(), found = false, opts = '';
  for (var i = 0; i < set.length; i++) {
    opts += '<option value="' + set[i][0] + '">' + set[i][1] + '</option>';
    if (set[i][0] === cur) found = true;
  }
  $st.html(opts).val(found ? cur : 'planned');
});
<?php endif; ?>

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

<?php if (!empty($is_perfumery) && isset($edit_batch)): ?>
function btlPreview(){
  var count = parseFloat($('#btl-count').val()) || 0;
  var fill = parseFloat($('#btl-fill').val()) || 0;
  var $bulkOpt = $('#btl-bulk option:selected');
  var unit = $bulkOpt.data('unit') || 'units';
  var bulkStock = parseFloat($bulkOpt.data('stock'));
  var hasBulk = !!$('#btl-bulk').val();
  var parts = [];
  if (count > 0) parts.push(count + ' bottle(s) out');
  var draw = (hasBulk && fill > 0) ? fill * count : 0;
  if (draw > 0) parts.push(draw + ' ' + unit + ' bulk out');
  if (count > 0) parts.push(count + ' finished unit(s) in');
  $('#btl-preview').text(parts.length ? 'Will post: ' + parts.join(' + ') : '');

  // Live bulk-remaining readout + capacity warning
  if (hasBulk && !isNaN(bulkStock)) {
    var left = bulkStock - draw;
    $('#btl-remaining').text('Bulk remaining after this run: ' + Math.round(left * 1000) / 1000 + ' ' + unit + (left < 0 ? ' — exceeds available stock!' : ''))
      .css('color', left < 0 ? '#DC2626' : '');
  }
}

// Blocks the submit on client-side overdraw / overfill before the server sees it
function btlProblems(){
  var problems = [];
  var count = parseFloat($('#btl-count').val()) || 0;
  var fill = parseFloat($('#btl-fill').val()) || 0;
  var $bulkOpt = $('#btl-bulk option:selected');
  var bulkStock = parseFloat($bulkOpt.data('stock'));
  if ($('#btl-bulk').val() && !isNaN(bulkStock) && fill * count > bulkStock) {
    problems.push('That fill needs ' + (fill * count) + ' ' + ($bulkOpt.data('unit') || 'units') + ' but only ' + bulkStock + ' is in stock. Complete the blend batch first or reduce the count.');
  }
  var cap = parseFloat($('#btl-bottle option:selected').data('cap'));
  if (!isNaN(cap) && cap > 0 && fill > cap) {
    problems.push('Fill of ' + fill + ' ml exceeds this bottle\'s ' + cap + ' ml capacity.');
  }
  return problems;
}

// Picking a finished SKU auto-resolves its linked bottle + fill volume
$('#btl-product').on('change', function(){
  var $opt = $(this).find('option:selected');
  var bottle = $opt.data('bottle');
  var fill = $opt.data('fill');
  if (bottle) { $('#btl-bottle').val(bottle).trigger('change.select2'); }
  if (fill !== undefined && fill !== '') { $('#btl-fill').val(fill); }
  var cap = parseFloat($('#btl-bottle option:selected').data('cap'));
  if (!isNaN(cap) && cap > 0 && parseFloat($('#btl-fill').val()) > cap) {
    toastr.warning('Fill exceeds the selected bottle\'s ' + cap + ' ml capacity');
  }
  btlPreview();
});
$('#btl-bottle').on('change', btlPreview);
$('#btl-bulk').on('change', function(){
  $('#btl-fill-unit').text($(this).find('option:selected').data('unit') || 'units');
  btlPreview();
});
$('#btl-fill, #btl-count').on('input', btlPreview);

$('#btn-bottle').on('click', function(){
  var problems = btlProblems();
  if (problems.length) { toastr.error(problems.join(' ')); return; }
  var $btn = $(this); $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Recording...');
  $.post('<?= base_url('operations/production_bottle_save'); ?>', $('#bottling-form').serialize(), function(res){
    if(res.success){
      toastr.success(res.message);
      setTimeout(function(){ location.reload(); }, 800);
    } else {
      toastr.error(res.message || 'Failed to record bottling');
      $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Record Bottling Run');
    }
  }, 'json').fail(function(xhr){
    toastr.error('Server error: ' + (xhr.responseText ? xhr.responseText.substring(0,200) : 'Could not reach server'));
    $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Record Bottling Run');
  });
});
<?php endif; ?>
</script>
