<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Add a new item to the kitchen menu</div>
  </div>
  <a href="<?= base_url('operations/kitchen'); ?>" class="mp-qa-btn blue"><i class="fa fa-arrow-left"></i> Back to Kitchen</a>
</div>

<div style="display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:24px;align-items:start;">
  <div class="mp-card-form" style="margin-bottom:0">
    <div class="mp-card-head"><h3><i class="fa fa-plus-circle"></i> Add New Menu Item</h3></div>
    <div class="mp-card-body">
      <?php if(!empty($error)): ?>
      <div class="alert alert-danger"><?= $error; ?></div>
      <?php endif; ?>

      <?= form_open('operations/menu_items', ['id'=>'menu-item-form']); ?>
        <input type="hidden" name="save_menu_item" value="1">
        <input type="hidden" name="command" value="save">
        <input type="hidden" id="hidden_price" name="price" value="">

        <div class="mp-form-group full">
          <label>Item Name <span class="text-danger">*</span></label>
          <input type="text" name="item_name" class="mp-form-control" placeholder="e.g. Jollof Rice, Grilled Chicken" required>
        </div>

        <div class="mp-form-grid" style="margin-top:16px">
          <div class="mp-form-group">
            <label>Menu Price <span class="text-danger">*</span></label>
            <div class="input-group">
              <span class="input-group-addon"><i class="fa fa-money"></i></span>
              <input type="number" name="sales_price" id="sales_price" class="form-control" placeholder="2500" step="0.01" min="0" required oninput="$('#hidden_price').val(this.value)">
            </div>
          </div>
          <div class="mp-form-group">
            <label>Category <span class="text-danger">*</span></label>
            <select name="category_id" class="mp-form-control select2" required>
              <option value="">-- Select Category --</option>
              <?php foreach($categories as $cat): ?>
              <option value="<?= $cat->id; ?>"><?= htmlspecialchars($cat->category_name); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="mp-form-grid" style="margin-top:16px">
          <div class="mp-form-group">
            <label>Kitchen Station</label>
            <select name="kitchen_station" class="mp-form-control">
              <option value="">-- Select Station --</option>
              <option value="grill">Grill</option>
              <option value="fryer">Fryer</option>
              <option value="cold">Cold Prep / Salad</option>
              <option value="barista">Barista / Drinks</option>
              <option value="bakery">Bakery</option>
              <option value="main">Main Kitchen</option>
            </select>
          </div>
          <div class="mp-form-group">
            <label>Prep Time (minutes)</label>
            <input type="number" name="prep_time_min" class="mp-form-control" placeholder="15" min="0">
          </div>
        </div>

        <div class="mp-form-grid" style="margin-top:16px">
          <div class="mp-form-group">
            <label>Unit</label>
            <select name="unit_id" class="mp-form-control select2">
              <option value="">-- Select Unit --</option>
              <?php foreach($units as $u): ?>
              <option value="<?= $u->id; ?>" <?= $u->id==1 ? 'selected' : ''; ?>><?= htmlspecialchars($u->unit_name); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mp-form-group">
            <label>Tax</label>
            <select name="tax_id" class="mp-form-control select2">
              <option value="">-- Select Tax --</option>
              <?php foreach($taxes as $t): ?>
              <option value="<?= $t->id; ?>" <?= $t->id==1 ? 'selected' : ''; ?>><?= htmlspecialchars($t->tax_name); ?> (<?= $t->tax; ?>%)</option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="mp-form-group full" style="margin-top:16px">
          <label>Item Code (optional)</label>
          <input type="text" name="item_code" class="mp-form-control" placeholder="Auto-generated if left blank">
        </div>

        <div class="mp-form-actions" style="margin-top:20px">
          <button type="submit" class="mp-btn-primary"><i class="fa fa-save"></i> Save Menu Item</button>
          <a href="<?= base_url('operations/kitchen'); ?>" class="mp-btn-secondary"><i class="fa fa-arrow-left"></i> Back to Kitchen</a>
        </div>
      <?= form_close(); ?>
    </div>
  </div>

  <div class="mp-card-form" style="margin-bottom:0">
    <div class="mp-card-head"><h3><i class="fa fa-lightbulb-o"></i> Tips</h3></div>
    <div class="mp-card-body">
      <ul style="font-size:13px;color:var(--mp-text);line-height:1.7;padding-left:18px;margin:0">
        <li><strong>Item Name:</strong> Keep it simple — what customers see on the menu.</li>
        <li><strong>Menu Price:</strong> The price charged to customers.</li>
        <li><strong>Kitchen Station:</strong> Helps route orders to the right prep area on the KDS.</li>
        <li><strong>Prep Time:</strong> Estimates how long the kitchen needs. Shown on KDS cards.</li>
        <li>Items saved here appear in POS search immediately.</li>
      </ul>
    </div>
  </div>
</div>

<script>
$(function(){
  $('.select2').select2();
});
</script>
