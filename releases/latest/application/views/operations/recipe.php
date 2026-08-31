<!DOCTYPE html>
<html>
<head><?php $this->load->view('comman/code_css.php'); ?></head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar'); ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1><?= $page_title; ?><small>Recipe builder & costing</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li><li><a href="<?= base_url('operations/recipes'); ?>">Recipes</a></li><li class="active"><?= $page_title; ?></li></ol>
  </section>
  <section class="content">
    <form id="recipe-form">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
      <input type="hidden" name="id" value="<?= $edit_recipe->id ?? ''; ?>">
      <div class="row">
        <div class="col-md-8">
          <div class="box box-warning">
            <div class="box-header with-border"><h3 class="box-title">Recipe Details</h3></div>
            <div class="box-body">
              <div class="row">
                <div class="form-group col-md-6">
                  <label>Recipe Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" name="name" value="<?= isset($edit_recipe) ? htmlspecialchars($edit_recipe->name) : ''; ?>" placeholder="e.g. Chocolate Fudge Cake" required>
                </div>
                <div class="form-group col-md-3">
                  <label>Category</label>
                  <select class="form-control select2" name="category" id="category-select">
                    <option value="">-- Select Category --</option>
                    <?php foreach(($recipe_categories ?? []) as $cat): ?>
                    <option value="<?= htmlspecialchars($cat->name); ?>" <?= (isset($edit_recipe) && $edit_recipe->category == $cat->name) ? 'selected' : ''; ?>><?= htmlspecialchars($cat->name); ?></option>
                    <?php endforeach; ?>
                  </select>
                  <p style="font-size:10px;margin-top:4px;"><a href="<?= base_url('operations/recipe_categories'); ?>" target="_blank"><i class="fa fa-cog"></i> Configure Categories</a></p>
                </div>
                <div class="form-group col-md-3">
                  <label>Status</label>
                  <select class="form-control" name="status">
                    <option value="1" <?= (isset($edit_recipe) && $edit_recipe->status==1)?'selected':''; ?>>Active</option>
                    <option value="0" <?= (isset($edit_recipe) && $edit_recipe->status==0)?'selected':''; ?>>Inactive</option>
                  </select>
                </div>
              </div>
              <div class="row">
                <div class="form-group col-md-12">
                  <label>Description / Instructions</label>
                  <textarea class="form-control" name="description" rows="2" placeholder="Short description or method notes"><?= isset($edit_recipe) ? htmlspecialchars($edit_recipe->description) : ''; ?></textarea>
                </div>
              </div>
              <div class="row">
                <div class="form-group col-md-4">
                  <label>Final Product <small class="text-muted">(what this recipe makes)</small></label>
                  <select class="form-control select2" name="product_item_id" id="product_item_id">
                    <option value="">-- Select Product Item --</option>
                    <?php foreach(($product_items ?? []) as $pi): ?>
                    <option value="<?= $pi->id; ?>" data-unit="<?= htmlspecialchars($pi->unit_name ?? 'piece'); ?>" <?= (isset($edit_recipe) && $edit_recipe->product_item_id == $pi->id) ? 'selected' : ''; ?>><?= htmlspecialchars($pi->item_name); ?></option>
                    <?php endforeach; ?>
                  </select>
                  <p class="text-muted" style="font-size:10px;margin-top:4px;">Links recipe to the product item it produces</p>
                </div>
                <div class="form-group col-md-2">
                  <label>Yield Qty <span class="text-danger">*</span></label>
                  <input type="number" step="0.01" class="form-control" name="yield_qty" id="yield_qty" value="<?= isset($edit_recipe) ? $edit_recipe->yield_qty : '1'; ?>" required>
                </div>
                <div class="form-group col-md-2">
                  <label>Yield Unit</label>
                  <input type="text" class="form-control" name="yield_unit" id="yield_unit" value="<?= isset($edit_recipe) ? htmlspecialchars($edit_recipe->yield_unit) : 'piece'; ?>" placeholder="auto from product" readonly style="background:#f9f9f9;">
                </div>
                <div class="form-group col-md-2">
                  <label>Prep Time (mins)</label>
                  <input type="number" class="form-control" name="prep_time" value="<?= isset($edit_recipe) ? $edit_recipe->prep_time : ''; ?>" placeholder="30">
                </div>
                <div class="form-group col-md-2">
                  <label>Cook Time (mins)</label>
                  <input type="number" class="form-control" name="cook_time" value="<?= isset($edit_recipe) ? $edit_recipe->cook_time : ''; ?>" placeholder="45">
                </div>
              </div>
              <div class="row">
                <div class="form-group col-md-3">
                  <label>Sales Margin % <small class="text-muted">(auto-sets product price)</small></label>
                  <input type="number" step="0.1" class="form-control" name="margin_pct" value="<?= isset($edit_recipe) ? $edit_recipe->margin_pct : '30'; ?>" placeholder="e.g. 30">
                </div>
                <div class="form-group col-md-9" style="padding-top:25px;">
                  <span class="text-muted">When production completes, product sales price = production cost + this margin %</span>
                </div>
              </div>
            </div>
          </div>

          <div class="box box-default">
            <div class="box-header with-border">
              <h3 class="box-title"><i class="fa fa-list"></i> Ingredients</h3>
              <div class="box-tools pull-right">
                <button type="button" class="btn btn-sm btn-success" onclick="addIngredientRow()"><i class="fa fa-plus"></i> Add Ingredient</button>
              </div>
            </div>
            <div class="box-body">
              <table class="table table-bordered table-condensed" id="ingredients-table">
                <thead class="bg-gray"><tr><th style="min-width:220px;">Ingredient</th><th style="width:110px;">Qty</th><th style="width:140px;">Unit</th><th style="width:110px;">Cost/Unit</th><th style="width:120px;">Waste %</th><th style="width:100px;" class="text-right">Subtotal</th><th style="width:50px;"></th></tr></thead>
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
                <tfoot class="bg-gray">
                  <tr><td colspan="5" class="text-right"><strong>Total Ingredient Cost:</strong></td><td class="text-right"><strong id="total-cost">0.00</strong></td><td></td></tr>
                  <tr><td colspan="5" class="text-right"><strong>Cost Per <?= isset($edit_recipe) ? htmlspecialchars($edit_recipe->yield_unit) : 'Unit'; ?>:</strong></td><td class="text-right"><strong class="text-success" id="cost-per-unit">0.00</strong></td><td></td></tr>
                </tfoot>
              </table>
            </div>
          </div>

          <div class="box box-default">
            <div class="box-header"><h3 class="box-title">Notes</h3></div>
            <div class="box-body">
              <textarea class="form-control" name="notes" rows="2" placeholder="Any notes about this recipe"><?= isset($edit_recipe) ? htmlspecialchars($edit_recipe->notes) : ''; ?></textarea>
            </div>
          </div>

          <div class="box-footer">
            <button type="button" id="btn-save" class="btn btn-success">Save Recipe</button>
            <a href="<?= base_url('operations/recipes'); ?>" class="btn btn-warning">Back to Recipes</a>
          </div>
        </div>

        <?php if(isset($edit_recipe)): ?>
        <div class="col-md-4">
          <div class="box box-info">
            <div class="box-header"><h3 class="box-title">Cost Summary</h3></div>
            <div class="box-body">
              <div class="info-box bg-aqua">
                <span class="info-box-icon"><i class="fa fa-money"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Total Ingredient Cost</span>
                  <span class="info-box-number" id="summary-total"><?= number_format($total_cost ?? 0, 2); ?></span>
                </div>
              </div>
              <div class="info-box bg-green">
                <span class="info-box-icon"><i class="fa fa-calculator"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Cost Per <?= htmlspecialchars($edit_recipe->yield_unit); ?></span>
                  <span class="info-box-number" id="summary-unit"><?= number_format($cost_per_unit ?? 0, 2); ?></span>
                </div>
              </div>
              <div class="info-box bg-yellow">
                <span class="info-box-icon"><i class="fa fa-clock-o"></i></span>
                <div class="info-box-content">
                  <span class="info-box-text">Total Time</span>
                  <span class="info-box-number"><?= (($edit_recipe->prep_time ?? 0) + ($edit_recipe->cook_time ?? 0)) . ' mins'; ?></span>
                </div>
              </div>
            </div>
          </div>

          <div class="box box-default">
            <div class="box-header"><h3 class="box-title"><i class="fa fa-industry"></i> Production History</h3></div>
            <div class="box-body" style="max-height:400px;overflow-y:auto;">
              <?php if(!empty($production_runs)): ?>
              <table class="table table-condensed table-striped">
                <thead><tr><th>Date</th><th>Planned</th><th>Actual</th><th>Cost</th></tr></thead>
                <tbody>
                <?php foreach($production_runs as $run): ?>
                  <tr><td><?= show_date($run->run_date); ?></td><td><?= number_format($run->planned_qty, 0); ?></td><td><?= $run->actual_yield ? number_format($run->actual_yield, 0) : '-'; ?></td><td><?= $run->actual_cost ? number_format($run->actual_cost, 2) : '-'; ?></td></tr>
                <?php endforeach; ?>
                </tbody>
              </table>
              <?php else: ?>
                <p class="text-muted text-center">No production runs yet.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </form>
  </section>
</div>
</div>
<?php $this->load->view('comman/code_js.php'); ?>
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
  // Rebuild unit dropdown
  $unitSel.html(buildUnitOptions(baseUnit, alternates));
  // Preserve selected unit if it exists in new options, otherwise default to base unit
  if ($unitSel.find('option[value="'+selectedUnit+'"]').length === 0) {
    selectedUnit = baseUnit;
  }
  $unitSel.val(selectedUnit);
  // Read equivalent qty directly from the selected option's data-equiv
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
  // Also bind if user types custom name
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
  // Bind existing rows and populate their unit dropdowns
  $('#ingredients-body tr.ing-row').each(function(){
    bindRowEvents($(this));
    var $sel = $(this).find('.ingredient-select');
    var opt = $sel.find('option:selected');
    if(opt.val()){
      updateUnitCost($(this), opt);
    }
  });
  $('#yield_qty').on('input', recalc);
  recalc();

  // Auto-populate yield unit from selected final product
  $('#product_item_id').on('change', function(){
    var unit = $(this).find('option:selected').data('unit') || 'piece';
    $('#yield_unit').val(unit);
  });
});

$('#btn-save').on('click', function(){
  var $btn = $(this); $btn.prop('disabled', true).text('Saving...');
  // Ensure all custom ingredient names are captured even if not selected from dropdown
  $('#ingredients-body tr.ing-row').each(function(){
    var $sel = $(this).find('.ingredient-select');
    var val = $sel.val();
    var opt = $sel.find('option:selected');
    var name = opt.data('name') || '';
    // If custom name typed but no item selected, use the text
    if(!name && !val) {
      // Try to get the text content
      name = $sel.find('option:selected').text() || '';
    }
    $(this).find('input[name="item_name[]"]').val(name);
  });

  $.post('<?= base_url('operations/recipe_save'); ?>', $('#recipe-form').serialize(), function(res){
    if(res.success){
      toastr.success(res.message);
      setTimeout(function(){ window.location.href = '<?= base_url('operations/recipes'); ?>'; }, 800);
    } else {
      toastr.error(res.message || 'Failed to save');
      $btn.prop('disabled', false).text('Save Recipe');
    }
  }, 'json').fail(function(){ toastr.error('Server error'); $btn.prop('disabled', false).text('Save Recipe'); });
});
</script>
</body>
</html>
