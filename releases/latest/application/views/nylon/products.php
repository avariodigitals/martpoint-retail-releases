<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $e_item = $edit['item'] ?? null; $e_spec = $edit['spec'] ?? null; ?>

<div class="mp-page-head">
  <div>
    <h2>Materials &amp; Products</h2>
    <div class="mp-page-sub">Raw materials, film rolls and finished goods with nylon specifications</div>
  </div>
</div>

<?php if ($can_edit): ?>
<div class="mp-card-form" style="margin-bottom:20px;">
  <div class="mp-card-head"><h3><i class="fa fa-cube"></i> <?= $e_item ? 'Edit' : 'New'; ?> Material / Product</h3></div>
  <div class="mp-card-body">
    <form id="nyProductForm" onsubmit="return nySaveProduct(event);">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
      <input type="hidden" name="item_id" value="<?= $e_item->id ?? ''; ?>">
      <div class="mp-form-grid" style="grid-template-columns:repeat(4,1fr);gap:14px;">
        <div><label>Name *</label><input type="text" name="item_name" class="form-control" required value="<?= htmlspecialchars($e_item->item_name ?? ''); ?>" placeholder="e.g. LDPE Resin (Virgin)"></div>
        <div><label>Class *</label>
          <select name="item_class" class="form-control" required>
            <?php foreach ($classes as $k=>$l): ?>
              <option value="<?= $k; ?>" <?= ($e_spec->item_class ?? '') === $k ? 'selected' : ''; ?>><?= $l; ?></option>
            <?php endforeach; ?>
          </select></div>
        <div><label>Category</label>
          <select name="category_id" class="form-control">
            <option value="">—</option>
            <?php foreach ($categories as $c): ?>
              <option value="<?= $c->id; ?>" <?= ($e_item->category_id ?? '') == $c->id ? 'selected' : ''; ?>><?= htmlspecialchars($c->category_name); ?></option>
            <?php endforeach; ?>
          </select></div>
        <div><label>Base (stock) unit *</label>
          <select name="unit_id" class="form-control" required>
            <?php foreach ($units as $u): ?>
              <option value="<?= $u->id; ?>" <?= ($e_item->unit_id ?? '') == $u->id ? 'selected' : ''; ?>><?= htmlspecialchars($u->unit_name); ?></option>
            <?php endforeach; ?>
          </select></div>
        <div><label>Purchase cost</label><input type="number" step="any" name="purchase_price" class="form-control" value="<?= $e_item->purchase_price ?? ''; ?>"></div>
        <div><label>Sales price (base unit)</label><input type="number" step="any" name="sales_price" class="form-control" value="<?= $e_item->sales_price ?? ''; ?>"></div>
        <div><label>Alert qty</label><input type="number" step="any" name="alert_qty" class="form-control" value="<?= $e_item->alert_qty ?? ''; ?>"></div>
        <div><label>Material</label>
          <select name="material" class="form-control">
            <option value="">—</option>
            <?php foreach ($materials as $k=>$l): ?><option value="<?= $k; ?>" <?= ($e_spec->material ?? '') === $k ? 'selected' : ''; ?>><?= $l; ?></option><?php endforeach; ?>
          </select></div>
        <div><label>Product form</label>
          <select name="product_form" class="form-control">
            <option value="">—</option>
            <?php foreach ($forms as $k=>$l): ?><option value="<?= $k; ?>" <?= ($e_spec->product_form ?? '') === $k ? 'selected' : ''; ?>><?= $l; ?></option><?php endforeach; ?>
          </select></div>
        <div><label>Bag / film type</label>
          <select name="bag_type" class="form-control">
            <option value="">—</option>
            <?php foreach ($bag_types as $k=>$l): ?><option value="<?= $k; ?>" <?= ($e_spec->bag_type ?? '') === $k ? 'selected' : ''; ?>><?= $l; ?></option><?php endforeach; ?>
          </select></div>
        <div><label>Width (cm)</label><input type="number" step="any" name="width_cm" class="form-control" value="<?= $e_spec->width_cm ?? ''; ?>"></div>
        <div><label>Length (cm)</label><input type="number" step="any" name="length_cm" class="form-control" value="<?= $e_spec->length_cm ?? ''; ?>"></div>
        <div><label>Thickness (micron)</label><input type="number" step="any" name="thickness_micron" class="form-control" value="<?= $e_spec->thickness_micron ?? ''; ?>"></div>
        <div><label>Colour</label><input type="text" name="colour" class="form-control" value="<?= htmlspecialchars($e_spec->colour ?? ''); ?>" placeholder="White / Clear / Black…"></div>
        <div><label>Print status</label>
          <select name="print_type" class="form-control">
            <?php foreach ($print_types as $k=>$l): ?><option value="<?= $k; ?>" <?= ($e_spec->print_type ?? 'none') === $k ? 'selected' : ''; ?>><?= $l; ?></option><?php endforeach; ?>
          </select></div>
        <div><label>Design reference</label><input type="text" name="design_ref" class="form-control" value="<?= htmlspecialchars($e_spec->design_ref ?? ''); ?>" placeholder="Customer design code"></div>
      </div>

      <div class="mp-card-head" style="padding-left:0;border-bottom:none;"><h3 style="font-size:14px;"><i class="fa fa-balance-scale"></i> Conversions &amp; Selling Units</h3></div>
      <p class="mp-muted" style="font-size:12px;">Conversions are per product — e.g. <em>1 KG = 40 pieces</em> for one bag and a different rate for another. Blank means unknown; the system never guesses a bags-per-kg rate.</p>
      <div class="mp-form-grid" style="grid-template-columns:repeat(4,1fr);gap:14px;">
        <div><label>kg per piece</label><input type="number" step="any" name="kg_per_piece" class="form-control" value="<?= $e_spec->kg_per_piece ?? ''; ?>" placeholder="e.g. 0.025"></div>
        <div><label>kg per roll</label><input type="number" step="any" name="kg_per_roll" class="form-control" value="<?= $e_spec->kg_per_roll ?? ''; ?>" placeholder="e.g. 25"></div>
        <div><label>pieces per roll</label><input type="number" step="any" name="pieces_per_roll" class="form-control" value="<?= $e_spec->pieces_per_roll ?? ''; ?>"></div>
        <div><label>Spec notes</label><input type="text" name="spec_notes" class="form-control" value="<?= htmlspecialchars($e_spec->notes ?? ''); ?>"></div>
      </div>

      <table class="mp-static-table" id="nyUnitsTable" style="margin-top:6px;">
        <thead><tr><th>Selling unit</th><th>Qty per base unit</th><th>Selling price</th><th>Wholesale price</th><th>Purchase price</th><th>Default</th><th></th></tr></thead>
        <tbody></tbody>
      </table>
      <button type="button" class="mp-btn-sm" onclick="nyAddUnitRow()"><i class="fa fa-plus"></i> Add selling unit</button>

      <div style="margin-top:16px;"><button type="submit" class="mp-qa-btn"><i class="fa fa-save"></i> Save Product</button>
      <?php if ($e_item): ?><a href="<?= base_url('nylon/products'); ?>" class="mp-qa-btn" style="background:var(--mp-muted);">Cancel edit</a><?php endif; ?></div>
      <div id="nyProductMsg" style="margin-top:8px;font-size:13px;"></div>
    </form>
  </div>
</div>
<?php endif; ?>

<div class="mp-card-form">
  <div class="mp-card-head"><h3><i class="fa fa-list"></i> Nylon Catalogue</h3></div>
  <div class="mp-card-body" style="padding:0!important;">
    <table class="mp-static-table">
      <thead><tr><th>Item</th><th>Class</th><th>Spec</th><th>Print</th><th class="text-right">Stock</th><th class="text-right">Cost</th><th class="text-right">Price</th><th></th></tr></thead>
      <tbody>
      <?php if (empty($spec_items)): ?>
        <tr><td colspan="8" style="padding:20px;color:var(--mp-muted);">No nylon items yet — create resin, film rolls and finished products above.</td></tr>
      <?php endif; ?>
      <?php foreach ($spec_items as $s): ?>
        <tr>
          <td><strong><?= htmlspecialchars($s->item_name); ?></strong><br><small class="text-muted"><?= htmlspecialchars($s->item_code); ?></small></td>
          <td><span class="label label-<?= ['raw_material'=>'default','film_roll'=>'info','finished_good'=>'success','consumable'=>'warning'][$s->item_class] ?? 'default'; ?>"><?= $classes[$s->item_class] ?? $s->item_class; ?></span></td>
          <td><small><?= htmlspecialchars(trim(($s->material ?: '').' '.($s->width_cm ? $s->width_cm.'cm' : '').($s->length_cm ? '×'.$s->length_cm.'cm' : '').($s->thickness_micron ? ' '.$s->thickness_micron.'µ' : '').($s->colour ? ' '.$s->colour : ''))) ?: '—'; ?><?= $s->design_ref ? '<br>Ref: '.htmlspecialchars($s->design_ref) : ''; ?></small></td>
          <td><small><?= $print_types[$s->print_type] ?? 'No printing'; ?></small></td>
          <td class="text-right"><?= format_qty($s->stock); ?> <small><?= htmlspecialchars($s->unit_name ?: ''); ?></small></td>
          <td class="text-right"><?= store_number_format($s->purchase_price); ?></td>
          <td class="text-right"><?= store_number_format($s->sales_price); ?></td>
          <td><?php if ($can_edit): ?><a href="<?= base_url('nylon/products?edit='.$s->item_id); ?>" class="btn btn-xs btn-primary"><i class="fa fa-pencil"></i></a><?php endif; ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
var NY_UNITS = <?= json_encode(array_map(function($u){ return ['id'=>$u->id,'name'=>$u->unit_name]; }, $units)); ?>;
var NY_CSRF_NAME = <?= json_encode($this->security->get_csrf_token_name()); ?>;
function nyAddUnitRow(u){
  var tb = document.querySelector('#nyUnitsTable tbody');
  var tr = document.createElement('tr');
  var opts = '<option value="">—</option>';
  NY_UNITS.forEach(function(x){ opts += '<option value="'+x.id+'"'+(u && u.unit_id==x.id?' selected':'')+'>'+x.name+'</option>'; });
  tr.innerHTML = '<td><select name="selling_unit_unit_id[]" class="form-control">'+opts+'</select></td>'
    +'<td><input type="number" step="any" name="selling_unit_conversion[]" class="form-control" value="'+(u?u.conversion_factor:'')+'" placeholder="e.g. 40"></td>'
    +'<td><input type="number" step="any" name="selling_unit_selling_price[]" class="form-control" value="'+(u?u.selling_price:'')+'"></td>'
    +'<td><input type="number" step="any" name="selling_unit_wholesale_price[]" class="form-control" value="'+(u&&u.wholesale_price?u.wholesale_price:'')+'"></td>'
    +'<td><input type="number" step="any" name="selling_unit_purchase_price[]" class="form-control" value="'+(u&&u.purchase_price?u.purchase_price:'')+'"></td>'
    +'<td><input type="radio" name="selling_unit_default" value="'+(tb.rows.length)+'"></td>'
    +'<td><button type="button" class="btn btn-xs btn-danger" onclick="this.closest(\'tr\').remove()"><i class="fa fa-trash"></i></button></td>';
  tb.appendChild(tr);
}
function nySaveProduct(ev){
  ev.preventDefault();
  var f = document.getElementById('nyProductForm');
  var msg = document.getElementById('nyProductMsg');
  fetch('<?= base_url('nylon/product_save'); ?>', {method:'POST', body:new FormData(f)})
    .then(function(r){return r.json();})
    .then(function(d){
      msg.innerHTML = '<span class="'+(d.success?'text-success':'text-danger')+'">'+d.message+'</span>';
      if (d.csrf_hash) { f.querySelector('input[name="'+NY_CSRF_NAME+'"]').value = d.csrf_hash; }
      if (d.success) setTimeout(function(){ location.href='<?= base_url('nylon/products'); ?>'; }, 800);
    });
  return false;
}
<?php if ($e_item): ?>
fetch('<?= base_url('nylon/product_units/'.$e_item->id); ?>').then(function(r){return r.json();}).then(function(u){ (u||[]).forEach(nyAddUnitRow); });
<?php endif; ?>
</script>
