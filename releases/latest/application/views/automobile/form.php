<?php $this->load->helper('form'); ?>
<style>
  .automobile-form .mp-card { background: #fff; border: 1px solid #E5E7EB; border-radius: 16px; overflow: hidden; margin-top: 24px; }
  .automobile-form .mp-card-head { padding: 16px 20px; border-bottom: 1px solid #E5E7EB; background: #F9FAFB; }
  .automobile-form .mp-card-head h3 { margin: 0; font-size: 16px; font-weight: 700; color: #111827; }
  .automobile-form .mp-card-body { padding: 20px; }
  .automobile-form .mp-form-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; }
  .automobile-form .mp-form-grid .form-group { display: flex; flex-direction: column; gap: 6px; margin-bottom: 0; }
  .automobile-form .form-group label { font-size: 13px; font-weight: 600; color: #374151; }
  .automobile-form .form-control { border-radius: 10px; border: 1px solid #D1D5DB; padding: 10px 12px; font-size: 14px; }
  .automobile-form .form-control:focus { border-color: #2563EB; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); }
  .automobile-form .mp-form-actions { margin-top: 24px; display: flex; gap: 12px; }
  .automobile-form .mp-btn { display: inline-flex; align-items: center; gap: 6px; padding: 10px 22px; border-radius: 8px; border: 1px solid #D1D5DB; background: #fff; color: #374151; font-size: 14px; font-weight: 600; text-decoration: none; cursor: pointer; transition: all .15s; }
  .automobile-form .mp-btn:hover { background: #F3F4F6; color: #111827; }
  .automobile-form .mp-btn.green { background: #10B981; border-color: #10B981; color: #fff; }
  .automobile-form .mp-btn.green:hover { background: #059669; border-color: #059669; color: #fff; }
  .automobile-form .vd-btn { display: inline-flex; align-items: center; gap: 4px; padding: 4px 8px; border-radius: 6px; background: #2563EB; color: #fff; border: none; font-size: 11px; font-weight: 600; text-decoration: none; }
  .automobile-form .vd-btn.danger { background: #DC2626; }
  @media(max-width: 1024px){ .automobile-form .mp-form-grid { grid-template-columns: repeat(2, 1fr); } }
  @media(max-width: 767px){ .automobile-form .mp-form-grid { grid-template-columns: 1fr; } }
</style>
<div class="automobile-form">
<div class="mp-page-head">
  <div>
    <h2><?= !empty($vehicle) ? 'Edit Vehicle' : 'Add Vehicle'; ?></h2>
    <div class="mp-page-sub">Record the vehicle details, pictures, and price</div>
  </div>
</div>

<?php $is_edit = !empty($vehicle); ?>

<?= form_open_multipart('automobile/save', ['class' => 'mp-form']); ?>
<input type="hidden" name="id" value="<?= $is_edit ? (int) $vehicle->id : ''; ?>">

<div class="mp-card" style="margin-top:24px;">
  <div class="mp-card-head"><h3>Vehicle Details</h3></div>
  <div class="mp-card-body">
    <div class="mp-form-grid" style="grid-template-columns: repeat(3, 1fr); gap: 16px;">
      <div class="form-group">
        <label>Vehicle Code</label>
        <input type="text" name="vehicle_code" class="form-control" value="<?= $is_edit ? htmlspecialchars($vehicle->vehicle_code) : ''; ?>" placeholder="e.g. VH-001">
      </div>
      <div class="form-group">
        <label>Make <span class="text-danger">*</span></label>
        <select name="make" id="make" class="form-control" required>
          <option value="">Select Make</option>
          <?php foreach ($makes as $m): ?>
          <option value="<?= htmlspecialchars($m->name); ?>" data-make-id="<?= $m->id; ?>" <?= $is_edit && $vehicle->make === $m->name ? 'selected' : ''; ?>><?= htmlspecialchars($m->name); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Model <span class="text-danger">*</span></label>
        <select name="model" id="model" class="form-control" required data-selected="<?= $is_edit ? htmlspecialchars($vehicle->model) : ''; ?>">
          <option value="">Select Make First</option>
        </select>
      </div>
      <div class="form-group">
        <label>Year</label>
        <input type="number" name="year" class="form-control" value="<?= $is_edit ? htmlspecialchars($vehicle->year) : ''; ?>" placeholder="e.g. 2020">
      </div>
      <div class="form-group">
        <label>Color</label>
        <select name="color" class="form-control">
          <option value="">Select</option>
          <?php foreach (($attributes['color'] ?? []) as $a): ?>
          <option value="<?= htmlspecialchars($a->attribute_value); ?>" <?= $is_edit && $vehicle->color === $a->attribute_value ? 'selected' : ''; ?>><?= htmlspecialchars($a->attribute_value); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Mileage (km)</label>
        <input type="number" name="mileage" class="form-control" value="<?= $is_edit ? htmlspecialchars($vehicle->mileage) : ''; ?>" placeholder="e.g. 45000">
      </div>
      <div class="form-group">
        <label>Fuel Type</label>
        <select name="fuel_type" class="form-control">
          <option value="">Select</option>
          <?php foreach (($attributes['fuel_type'] ?? []) as $a): ?>
          <option value="<?= htmlspecialchars($a->attribute_value); ?>" <?= $is_edit && $vehicle->fuel_type === $a->attribute_value ? 'selected' : ''; ?>><?= htmlspecialchars($a->attribute_value); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Transmission</label>
        <select name="transmission" class="form-control">
          <option value="">Select</option>
          <?php foreach (($attributes['transmission'] ?? []) as $a): ?>
          <option value="<?= htmlspecialchars($a->attribute_value); ?>" <?= $is_edit && $vehicle->transmission === $a->attribute_value ? 'selected' : ''; ?>><?= htmlspecialchars($a->attribute_value); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Condition</label>
        <select name="vehicle_condition" class="form-control">
          <option value="">Select</option>
          <?php foreach (($attributes['condition'] ?? []) as $a): ?>
          <option value="<?= htmlspecialchars($a->attribute_value); ?>" <?= $is_edit && $vehicle->vehicle_condition === $a->attribute_value ? 'selected' : ''; ?>><?= htmlspecialchars($a->attribute_value); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>VIN</label>
        <input type="text" name="vin" class="form-control" value="<?= $is_edit ? htmlspecialchars($vehicle->vin) : ''; ?>" placeholder="Vehicle Identification Number">
      </div>
      <div class="form-group">
        <label>License Plate</label>
        <input type="text" name="license_plate" class="form-control" value="<?= $is_edit ? htmlspecialchars($vehicle->license_plate) : ''; ?>" placeholder="e.g. ABC-1234">
      </div>
    </div>

    <div class="form-group" style="margin-top:16px;">
      <label>Description / Specifications</label>
      <textarea name="description" class="form-control" rows="3" placeholder="Engine, trim, features, etc."><?= $is_edit ? htmlspecialchars($vehicle->description) : ''; ?></textarea>
    </div>
  </div>
</div>

<div class="mp-card" style="margin-top:16px;">
  <div class="mp-card-head"><h3>Specifications</h3></div>
  <div class="mp-card-body">
    <div class="mp-form-grid" style="grid-template-columns: repeat(3, 1fr); gap: 16px;">
      <div class="form-group">
        <label>Body Type</label>
        <select name="body_type" class="form-control">
          <option value="">Select</option>
          <?php foreach (($attributes['body_type'] ?? []) as $a): ?>
          <option value="<?= htmlspecialchars($a->attribute_value); ?>" <?= $is_edit && $vehicle->body_type === $a->attribute_value ? 'selected' : ''; ?>><?= htmlspecialchars($a->attribute_value); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Engine Capacity</label>
        <input type="text" name="engine_capacity" class="form-control" value="<?= $is_edit ? htmlspecialchars($vehicle->engine_capacity) : ''; ?>" placeholder="e.g. 2.0L">
      </div>
      <div class="form-group">
        <label>Drivetrain</label>
        <select name="drivetrain" class="form-control">
          <option value="">Select</option>
          <?php foreach (($attributes['drivetrain'] ?? []) as $a): ?>
          <option value="<?= htmlspecialchars($a->attribute_value); ?>" <?= $is_edit && $vehicle->drivetrain === $a->attribute_value ? 'selected' : ''; ?>><?= htmlspecialchars($a->attribute_value); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label>Trim Level</label>
        <input type="text" name="trim_level" class="form-control" value="<?= $is_edit ? htmlspecialchars($vehicle->trim_level) : ''; ?>" placeholder="e.g. XLE Sport">
      </div>
      <div class="form-group">
        <label>Number of Previous Owners</label>
        <input type="number" name="number_of_owners" class="form-control" value="<?= $is_edit ? (int) $vehicle->number_of_owners : ''; ?>">
      </div>
      <div class="form-group">
        <label>Registration Date</label>
        <input type="date" name="registration_date" class="form-control" value="<?= $is_edit ? htmlspecialchars($vehicle->registration_date) : ''; ?>">
      </div>
    </div>
  </div>
</div>

<div class="mp-card" style="margin-top:16px;">
  <div class="mp-card-head"><h3>Pricing &amp; Status</h3></div>
  <div class="mp-card-body">
    <div class="mp-form-grid" style="grid-template-columns: repeat(3, 1fr); gap: 16px;">
      <div class="form-group">
        <label>Price <span class="text-danger">*</span></label>
        <input type="number" step="0.01" name="price" class="form-control" value="<?= $is_edit ? number_format($vehicle->price, 2, '.', '') : ''; ?>" required>
      </div>
      <div class="form-group">
        <label>Cost / Purchase Price</label>
        <input type="number" step="0.01" name="cost" class="form-control" value="<?= $is_edit ? number_format($vehicle->cost, 2, '.', '') : ''; ?>">
      </div>
      <div class="form-group">
        <label>Status</label>
        <select name="status" class="form-control">
          <option value="available" <?= $is_edit && $vehicle->status === 'available' ? 'selected' : ''; ?>>Available</option>
          <option value="reserved" <?= $is_edit && $vehicle->status === 'reserved' ? 'selected' : ''; ?>>Reserved</option>
          <option value="sold" <?= $is_edit && $vehicle->status === 'sold' ? 'selected' : ''; ?>>Sold</option>
        </select>
      </div>
    </div>
    <div class="form-group" style="margin-top:16px;">
      <label>Customer / Buyer Name</label>
      <input type="text" name="customer_name" class="form-control" value="<?= $is_edit ? htmlspecialchars($vehicle->customer_name) : ''; ?>" placeholder="For reserved or sold units">
    </div>
  </div>
</div>

<div class="mp-card" style="margin-top:16px;">
  <div class="mp-card-head"><h3>Vehicle Gallery</h3></div>
  <div class="mp-card-body">
    <div class="form-group">
      <input type="file" name="vehicle_images[]" class="form-control" accept="image/png,image/jpeg,image/webp" multiple>
      <p class="help-block">Max 2MB each. JPG, PNG, or WebP. First image uploaded becomes primary.</p>
      <?php if (!empty($images)): ?>
      <div style="display:grid; grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); gap: 12px; margin-top: 16px;">
        <?php foreach ($images as $img): ?>
        <div style="position:relative; border:1px solid #E5E7EB; border-radius:10px; overflow:hidden; <?= $img->is_primary ? 'box-shadow:0 0 0 3px #2563EB;' : ''; ?>">
          <img src="<?= base_url($img->image_path); ?>" style="width:100%; height:120px; object-fit:cover;">
          <div style="display:flex; gap:4px; justify-content:center; padding:6px; background:#F9FAFB; font-size:11px;">
            <?php if (!$img->is_primary): ?>
            <a href="<?= base_url('automobile/set_primary_image/' . $img->id); ?>" class="vd-btn" style="padding:4px 8px;">Primary</a>
            <?php endif; ?>
            <a href="<?= base_url('automobile/delete_image/' . $img->id); ?>" class="vd-btn danger" style="padding:4px 8px;" onclick="return confirm('Delete this image?')">Delete</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="mp-form-actions" style="margin-top:24px;">
  <button type="submit" class="mp-btn green"><i class="fa fa-save"></i> Save Vehicle</button>
  <a href="<?= base_url('automobile/list'); ?>" class="mp-btn">Cancel</a>
</div>

</div>
<?= form_close(); ?>

<script>
(function($) {
  var $make = $('#make');
  var $model = $('#model');
  var baseUrl = '<?= base_url(); ?>';
  var selectedModel = $model.data('selected') || '';

  function loadModels(makeId, callback) {
    $model.prop('disabled', true).html('<option value="">Loading...</option>');
    $.get(baseUrl + 'vehicle_data/models_json?make_id=' + makeId, function(res) {
      $model.prop('disabled', false).html('<option value="">Select Model</option>');
      if (res.models && res.models.length) {
        $.each(res.models, function(i, m) {
          var opt = $('<option></option>').val(m.name).text(m.name);
          if (m.name === selectedModel) { opt.prop('selected', true); }
          $model.append(opt);
        });
      }
      if (callback) callback();
    }, 'json');
  }

  $make.on('change', function() {
    var makeId = $(this).find(':selected').data('make-id');
    selectedModel = '';
    if (makeId) { loadModels(makeId); }
    else { $model.html('<option value="">Select Make First</option>'); }
  });

  // Load on edit
  var initialMakeId = $make.find(':selected').data('make-id');
  if (initialMakeId) { loadModels(initialMakeId); }
})(jQuery);
</script>
