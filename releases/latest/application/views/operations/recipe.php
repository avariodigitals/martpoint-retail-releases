<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.mp-section-divider{font-size:14px!important;font-weight:700!important;color:var(--mp-ink)!important;margin:24px 0 12px!important;padding-bottom:8px!important;border-bottom:1px solid var(--mp-border)!important;display:flex!important;align-items:center!important;gap:8px!important}
.mp-section-divider i{color:var(--mp-primary)!important}
.mp-ing-table{width:100%!important;border-collapse:collapse!important;font-size:13px!important}
.mp-ing-table th{font-size:11px!important;text-transform:uppercase!important;font-weight:700!important;color:var(--mp-muted)!important;padding:10px 12px!important;border-bottom:1px solid var(--mp-border)!important;background:var(--mp-bg)!important;text-align:left!important}
.mp-ing-table td{padding:10px 12px!important;border-bottom:1px solid var(--mp-border)!important;vertical-align:middle!important}
.mp-ing-table input[type=number],.mp-ing-table select,.mp-ing-table input[type=text]{border:1px solid var(--mp-border)!important;border-radius:8px!important;padding:7px 10px!important;font-size:13px!important;width:100%!important}
.mp-ing-table tfoot td{background:var(--mp-bg)!important;font-weight:700!important;font-size:13px!important;padding:12px!important}
.mp-cost-box{background:var(--mp-bg);border:1px solid var(--mp-border);border-radius:12px;padding:16px;display:flex;align-items:center;gap:14px;margin-bottom:12px}
.mp-cost-box .icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0}
.mp-cost-box .label{font-size:12px;color:var(--mp-muted);font-weight:600}
.mp-cost-box .value{font-size:20px;font-weight:700;color:var(--mp-text)}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Recipe builder & costing</div>
  </div>
  <a href="<?= base_url('operations/recipes'); ?>" class="mp-qa-btn blue"><i class="fa fa-arrow-left"></i> Back to Recipes</a>
</div>

<form id="recipe-form">
  <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
  <input type="hidden" name="id" value="<?= $edit_recipe->id ?? ''; ?>">
  <div style="display:grid;grid-template-columns:minmax(0,1fr) <?= isset($edit_recipe) ? '320px' : '0'; ?>;gap:24px;align-items:start;">

    <div>
      <div class="mp-card-form" style="margin-bottom:24px">
        <div class="mp-card-head"><h3>Recipe Details</h3></div>
        <div class="mp-card-body">
          <div class="mp-form-grid">
            <div class="mp-form-group" style="grid-column:span 2">
              <label>Recipe Name <span class="text-danger">*</span></label>
              <input type="text" class="mp-form-control" name="name" value="<?= isset($edit_recipe) ? htmlspecialchars($edit_recipe->name) : ''; ?>" placeholder="e.g. Chocolate Fudge Cake" required>
            </div>
            <div class="mp-form-group">
              <label>Category</label>
              <select class="mp-form-control select2" name="category" id="category-select">
                <option value="">-- Select Category --</option>
                <?php foreach(($recipe_categories ?? []) as $cat): ?>
                <option value="<?= htmlspecialchars($cat->name); ?>" <?= (isset($edit_recipe) && $edit_recipe->category == $cat->name) ? 'selected' : ''; ?>><?= htmlspecialchars($cat->name); ?></option>
                <?php endforeach; ?>
              </select>
              <p class="mp-form-hint"><a href="<?= base_url('operations/recipe_categories'); ?>" target="_blank"><i class="fa fa-cog"></i> Configure Categories</a></p>
            </div>
            <div class="mp-form-group">
              <label>Status</label>
              <select class="mp-form-control" name="status">
                <option value="1" <?= (isset($edit_recipe) && $edit_recipe->status==1)?'selected':''; ?>>Active</option>
                <option value="0" <?= (isset($edit_recipe) && $edit_recipe->status==0)?'selected':''; ?>>Inactive</option>
              </select>
            </div>
          </div>

          <div class="mp-form-group full" style="margin-top:20px">
            <label>Description / Instructions</label>
            <textarea class="mp-form-control" name="description" rows="2" placeholder="Short description or method notes"><?= isset($edit_recipe) ? htmlspecialchars($edit_recipe->description) : ''; ?></textarea>
          </div>

          <div class="mp-form-grid" style="margin-top:20px">
            <div class="mp-form-group" style="grid-column:span 2">
              <label>Final Product <span class="mp-form-hint">(what this recipe makes)</span></label>
              <select class="mp-form-control select2" name="product_item_id" id="product_item_id">
                <option value="">-- Select Product Item --</option>
                <?php foreach(($product_items ?? []) as $pi): ?>
                <option value="<?= $pi->id; ?>" data-unit="<?= htmlspecialchars($pi->unit_name ?? 'piece'); ?>" <?= (isset($edit_recipe) && $edit_recipe->product_item_id == $pi->id) ? 'selected' : ''; ?>><?= htmlspecialchars($pi->item_name); ?></option>
                <?php endforeach; ?>
              </select>
              <p class="mp-form-hint">Links recipe to the product item it produces</p>
            </div>
            <div class="mp-form-group"><label>Yield Qty <span class="text-danger">*</span></label><input type="number" step="0.01" class="mp-form-control" name="yield_qty" id="yield_qty" value="<?= isset($edit_recipe) ? $edit_recipe->yield_qty : '1'; ?>" required></div>
            <div class="mp-form-group"><label>Yield Unit</label><input type="text" class="mp-form-control" name="yield_unit" id="yield_unit" value="<?= isset($edit_recipe) ? htmlspecialchars($edit_recipe->yield_unit) : 'piece'; ?>" placeholder="auto from product" readonly></div>
            <div class="mp-form-group"><label>Prep Time (mins)</label><input type="number" class="mp-form-control" name="prep_time" value="<?= isset($edit_recipe) ? $edit_recipe->prep_time : ''; ?>" placeholder="30"></div>
            <div class="mp-form-group"><label>Cook Time (mins)</label><input type="number" class="mp-form-control" name="cook_time" value="<?= isset($edit_recipe) ? $edit_recipe->cook_time : ''; ?>" placeholder="45"></div>
          </div>

          <div class="mp-form-grid" style="margin-top:20px">
            <div class="mp-form-group"><label>Sales Margin % <span class="mp-form-hint">(auto-sets product price)</span></label><input type="number" step="0.1" class="mp-form-control" name="margin_pct" value="<?= isset($edit_recipe) ? $edit_recipe->margin_pct : '30'; ?>" placeholder="e.g. 30"></div>
            <div class="mp-form-group full" style="justify-content:flex-end"><p class="mp-form-hint" style="margin-top:auto;padding-top:25px">When production completes, product sales price = production cost + this margin %</p></div>
          </div>
        </div>
      </div>

      <div class="mp-card-form" style="margin-bottom:24px">
        <div class="mp-card-head"><h3><i class="fa fa-list"></i> Ingredients</h3><button type="button" class="mp-qa-btn green" onclick="addIngredientRow()"><i class="fa fa-plus"></i> Add Ingredient</button></div>
        <div class="mp-card-body" style="padding:0!important">
          <div class="mp-dt-scroll">
            <table class="mp-ing-table" id="ingredients-table">
              <thead><tr><th style="min-width:220px;">Ingredient</th><th style="width:110px;">Qty</th><th style="width:140px;">Unit</th><th style="width:110px;">Cost/Unit</th><th style="width:120px;">Waste %</th><th style="width:100px;" class="text-right">Subtotal</th><th style="width:50px;"></th></tr></thead>
              <tbody id="ingredients-body">
                <?php if(!empty($ingredients)): foreach($ingredients as $ing): ?>
                <tr class="ing-row">
                  <td>
                    <select class="form-control select2 ingredient-select" style="width:100%;">
                      <option value="">-- Type or Select --</option>
                      <?php foreach($items as $it):
                        $unit_descendants = [];
                        if (!empty($it->unit_id) && isset($unit_hierarchy[$it->unit_id])) {
                          $unit_descendants = get_unit_descendants($it->unit_id, $it->store_id);
                        }
                      ?>
                      <option value="<?= $it->id; ?>" data-name="<?= htmlspecialchars($it->item_name); ?>" data-cost="<?= $it->purchase_price ?? 0; ?>" data-unit="<?= htmlspecialchars($it->unit_name ?? 'gram'); ?>" data-unit-id="<?= $it->unit_id ?? ''; ?>" data-alternates='<?= json_encode($unit_descendants); ?>' <?= ($ing->item_id==$it->id)?'selected':''; ?>><?= htmlspecialchars($it->item_name); ?></option>
                      <?php endforeach; ?>
                    </select>
                    <input type="hidden" name="item_id[]" value="<?= $ing->item_id; ?>">
                    <input type="hidden" name="item_name[]" value="<?= htmlspecialchars($ing->item_name); ?>">
                  </td>
                  <td><input type="number" step="0.01" class="form-control input-sm qty-input" name="qty[]" value="<?= $ing->qty; ?>"></td>
                  <td><select class="form-control input-sm unit-select" name="unit[]"><option value="<?= htmlspecialchars($ing->unit); ?>" selected><?= htmlspecialchars($ing->unit); ?></option></select></td>
                  <td>
                    <div class="input-group input-group-sm">
                      <input type="number" step="0.01" class="form-control cost-input" name="cost_per_unit[]" value="<?= $ing->cost_per_unit; ?>" readonly title="Auto-calculated from item purchase price and selected unit" style="background:#f9f9f9;">
                      <span class="input-group-addon" style="padding:2px 6px;font-size:10px;" title="Auto-calculated from item purchase price and selected unit"><i class="fa fa-lock"></i></span>
                    </div>
                  </td>
                  <td><input type="number" step="0.1" class="form-control input-sm wastage-input" name="wastage_pct[]" value="<?= $ing->wastage_pct; ?>" placeholder="e.g. 5"></td>
                  <td class="text-right subtotal-cell">0.00</td>
                  <td><button type="button" class="btn btn-xs btn-danger" onclick="$(this).closest('tr').remove(); recalc()"><i class="fa fa-trash"></i></button></td>
                </tr>
                <?php endforeach; else: ?>
                <tr class="empty-row"><td colspan="7" class="text-center text-muted">No ingredients added yet. Click "Add Ingredient" to start.</td></tr>
                <?php endif; ?>
              </tbody>
              <tfoot>
                <tr><td colspan="5" class="text-right"><strong>Total Ingredient Cost:</strong></td><td class="text-right"><strong id="total-cost">0.00</strong></td><td></td></tr>
                <tr><td colspan="5" class="text-right"><strong>Cost Per <?= isset($edit_recipe) ? htmlspecialchars($edit_recipe->yield_unit) : 'Unit'; ?>:</strong></td><td class="text-right"><strong class="text-success" id="cost-per-unit">0.00</strong></td><td></td></tr>
              </tfoot>
            </table>
          </div>
        </div>
      </div>

      <div class="mp-card-form" style="margin-bottom:24px">
        <div class="mp-card-head"><h3>Notes</h3></div>
        <div class="mp-card-body">
          <textarea class="mp-form-control" name="notes" rows="2" placeholder="Any notes about this recipe"><?= isset($edit_recipe) ? htmlspecialchars($edit_recipe->notes) : ''; ?></textarea>
        </div>
      </div>

      <div class="mp-form-actions">
        <button type="button" id="btn-save" class="mp-btn-primary"><i class="fa fa-check"></i> Save Recipe</button>
        <a href="<?= base_url('operations/recipes'); ?>" class="mp-btn-secondary">Back to Recipes</a>
      </div>
    </div>

    <?php if(isset($edit_recipe)): ?>
    <div>
      <div class="mp-card-form" style="margin-bottom:24px">
        <div class="mp-card-head"><h3>Cost Summary</h3></div>
        <div class="mp-card-body">
          <div class="mp-cost-box">
            <div class="icon" style="background:rgba(0,87,255,.1);color:var(--mp-primary)"><i class="fa fa-money"></i></div>
            <div><div class="label">Total Ingredient Cost</div><div class="value" id="summary-total"><?= number_format($total_cost ?? 0, 2); ?></div></div>
          </div>
          <div class="mp-cost-box">
            <div class="icon" style="background:rgba(5,150,105,.1);color:var(--mp-success)"><i class="fa fa-calculator"></i></div>
            <div><div class="label">Cost Per <?= htmlspecialchars($edit_recipe->yield_unit); ?></div><div class="value" id="summary-unit"><?= number_format($cost_per_unit ?? 0, 2); ?></div></div>
          </div>
          <div class="mp-cost-box">
            <div class="icon" style="background:rgba(245,158,11,.1);color:var(--mp-warning)"><i class="fa fa-clock-o"></i></div>
            <div><div class="label">Total Time</div><div class="value"><?= (($edit_recipe->prep_time ?? 0) + ($edit_recipe->cook_time ?? 0)) . ' mins'; ?></div></div>
          </div>
        </div>
      </div>

      <div class="mp-card-form" style="margin-bottom:0">
        <div class="mp-card-head"><h3><i class="fa fa-industry"></i> Production History</h3></div>
        <div class="mp-card-body" style="padding:0!important;max-height:400px;overflow-y:auto">
          <?php if(!empty($production_runs)): ?>
          <table class="mp-static-table">
            <thead><tr><th>Date</th><th>Planned</th><th>Actual</th><th>Cost</th></tr></thead>
            <tbody>
            <?php foreach($production_runs as $run): ?>
              <tr><td><?= show_date($run->run_date); ?></td><td><?= number_format($run->planned_qty, 0); ?></td><td><?= $run->actual_yield ? number_format($run->actual_yield, 0) : '-'; ?></td><td><?= $run->actual_cost ? number_format($run->actual_cost, 2) : '-'; ?></td></tr>
            <?php endforeach; ?>
            </tbody>
          </table>
          <?php else: ?>
            <div class="mp-empty-state"><div class="mp-empty-icon"><i class="fa fa-inbox"></i></div><p>No production runs yet.</p></div>
          <?php endif; ?>
        </div>
      </div>
    </div>
    <?php endif; ?>
  </div>
</form>

<script>
var itemOptions = '';
<?php foreach($items as $it):
  $unit_descendants = [];
  if (!empty($it->unit_id) && isset($unit_hierarchy[$it->unit_id])) {
    $unit_descendants = get_unit_descendants($it->unit_id, $it->store_id);
  }
?>
itemOptions += '<option value="<?= $it->id; ?>" data-name="<?= htmlspecialchars($it->item_name); ?>" data-cost="<?= $it->purchase_price ?? 0; ?>" data-unit="<?= htmlspecialchars($it->unit_name ?? 'gram'); ?>" data-unit-id="<?= $it->unit_id ?? ''; ?>" data-alternates=\'<?= json_encode($unit_descendants); ?>\'><?= htmlspecialchars($it->item_name); ?></option>';
<?php endforeach; ?>

function buildUnitOptions(baseUnit, alternates){
  var opts = '<option value="'+baseUnit+'" data-equiv="1">'+baseUnit+'</option>';
  if(alternates && alternates.length){
    for(var i=0;i<alternates.length;i++){
      opts += '<option value="'+alternates[i].unit_name+'" data-equiv="'+alternates[i].equivalent_qty+'">'+alternates[i].unit_name+'</option>';
    }
  }
  return opts;
}

function updateUnitCost($row, opt){
  var baseCost = parseFloat(opt.data('cost')) || 0;
  var baseUnit = opt.data('unit') || 'gram';
  var alternates = opt.data('alternates') || [];
  if (typeof alternates === 'string') {
    try { alternates = JSON.parse(alternates); } catch(e) { alternates = []; }
  }
  var $unitSel = $row.find('.unit-select');
  var selectedUnit = $unitSel.val() || baseUnit;
  $unitSel.html(buildUnitOptions(baseUnit, alternates));
  if ($unitSel.find('option[value="'+selectedUnit+'"]').length === 0) {
    selectedUnit = baseUnit;
  }
  $unitSel.val(selectedUnit);
  var equiv = parseFloat($unitSel.find('option:selected').data('equiv')) || 1;
  var costPerUnit = equiv > 0 ? (baseCost / equiv) : baseCost;
  $row.find('.cost-input').val(costPerUnit.toFixed(4));
  recalc();
}

function addIngredientRow(){
  $('.empty-row').remove();
  var html = '<tr class="ing-row">'+
    '<td><select class="form-control ingredient-select" style="width:100%;"><option value="">-- Type or Select --</option>'+itemOptions+'</select>'+
    '<input type="hidden" name="item_id[]" value=""><input type="hidden" name="item_name[]" value=""></td>'+
    '<td><input type="number" step="0.01" class="form-control input-sm qty-input" name="qty[]" value="1"></td>'+
    '<td><select class="form-control input-sm unit-select" name="unit[]"><option value="gram">gram</option></select></td>'+
    '<td><div class="input-group input-group-sm"><input type="number" step="0.01" class="form-control cost-input" name="cost_per_unit[]" value="0" readonly title="Auto-calculated from item purchase price and selected unit" style="background:#f9f9f9;"><span class="input-group-addon" style="padding:2px 6px;font-size:10px;" title="Auto-calculated from item purchase price and selected unit"><i class="fa fa-lock"></i></span></div></td>'+
    '<td><input type="number" step="0.1" class="form-control input-sm wastage-input" name="wastage_pct[]" value="0" placeholder="e.g. 5"></td>'+
    '<td class="text-right subtotal-cell">0.00</td>'+
    '<td><button type="button" class="btn btn-xs btn-danger" onclick="$(this).closest(\'tr\').remove(); recalc()"><i class="fa fa-trash"></i></button></td>'+
    '</tr>';
  $('#ingredients-body').append(html);
  var $newRow = $('#ingredients-body tr.ing-row').last();
  $newRow.find('.ingredient-select').select2({width:'100%'});
  bindRowEvents($newRow);
}

function bindRowEvents($row){
  var $sel = $row.find('.ingredient-select');
  $sel.on('change', function(){
    var opt = $(this).find('option:selected');
    var name = opt.data('name') || $(this).val() || '';
    $row.find('input[name="item_id[]"]').val(opt.val() || '');
    $row.find('input[name="item_name[]"]').val(name);
    updateUnitCost($row, opt);
  });
  $row.find('.unit-select').on('change', function(){
    var opt = $sel.find('option:selected');
    updateUnitCost($row, opt);
  });
  $row.find('.qty-input, .cost-input, .wastage-input').on('input', recalc);
}

function recalc(){
  var total = 0;
  $('#ingredients-body tr.ing-row').each(function(){
    var qty = parseFloat($(this).find('.qty-input').val()) || 0;
    var cost = parseFloat($(this).find('.cost-input').val()) || 0;
    var waste = parseFloat($(this).find('.wastage-input').val()) || 0;
    var subtotal = qty * cost;
    if(waste > 0) subtotal = subtotal * (1 + (waste / 100));
    subtotal = Math.round(subtotal * 100) / 100;
    $(this).find('.subtotal-cell').text(subtotal.toFixed(2));
    total += subtotal;
  });
  total = Math.round(total * 100) / 100;
  var yieldQty = parseFloat($('#yield_qty').val()) || 1;
  var perUnit = yieldQty > 0 ? (total / yieldQty) : 0;
  perUnit = Math.round(perUnit * 100) / 100;
  $('#total-cost').text(total.toFixed(2));
  $('#cost-per-unit').text(perUnit.toFixed(2));
  $('#summary-total').text(total.toFixed(2));
  $('#summary-unit').text(perUnit.toFixed(2));
}

$(function(){
  $('#ingredients-body tr.ing-row').each(function(){
    bindRowEvents($(this));
    var $sel = $(this).find('.ingredient-select');
    var opt = $sel.find('option:selected');
    if(opt.val()){ updateUnitCost($(this), opt); }
  });
  $('#yield_qty').on('input', recalc);
  recalc();
  $('#product_item_id').on('change', function(){
    var unit = $(this).find('option:selected').data('unit') || 'piece';
    $('#yield_unit').val(unit);
  });
});

$('#btn-save').on('click', function(){
  var $btn = $(this); $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
  $('#ingredients-body tr.ing-row').each(function(){
    var $sel = $(this).find('.ingredient-select');
    var val = $sel.val();
    var opt = $sel.find('option:selected');
    var name = opt.data('name') || '';
    if(!name && !val) { name = $sel.find('option:selected').text() || ''; }
    $(this).find('input[name="item_name[]"]').val(name);
  });
  $.post('<?= base_url('operations/recipe_save'); ?>', $('#recipe-form').serialize(), function(res){
    if(res.success){
      toastr.success(res.message);
      setTimeout(function(){ window.location.href = '<?= base_url('operations/recipes'); ?>'; }, 800);
    } else {
      toastr.error(res.message || 'Failed to save');
      $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Save Recipe');
    }
  }, 'json').fail(function(){ toastr.error('Server error'); $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Save Recipe'); });
});
</script>
