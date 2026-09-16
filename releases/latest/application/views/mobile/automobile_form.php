<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — <?= !empty($vehicle) ? 'Edit Vehicle' : 'Add Vehicle'; ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root {
      --mp-primary: #0057FF;
      --mp-primary-dark: #0044CC;
      --mp-bg: #F1F5F9;
      --mp-surface: #FFFFFF;
      --mp-text: #0F172A;
      --mp-muted: #64748B;
      --mp-border: #E2E8F0;
      --mp-success: #10B981;
      --mp-danger: #EF4444;
      --mp-warning: #F59E0B;
      --mp-ink: #1E293B;
      --safe-bottom: env(safe-area-inset-bottom, 0px);
    }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 120px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
    .topbar .back { color: var(--mp-ink); font-size: 18px; text-decoration: none; }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: 14px; font-weight: 500; margin-bottom: 6px; }
    .form-control { width: 100%; padding: 14px 16px; border: 1px solid var(--mp-border); border-radius: 14px; font-size: 16px; background: #fff; outline: none; min-height: 54px; }
    .form-control:focus { border-color: var(--mp-primary); }
    textarea.form-control { min-height: 90px; resize: vertical; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .mp-select { display: none; }
    .mp-select-wrap { position: relative; width: 100%; }
    .mp-select-trigger { width: 100%; padding: 14px 42px 14px 16px; border: 1px solid var(--mp-border); border-radius: 14px; font-size: 16px; background: #fff; color: var(--mp-text); cursor: pointer; display: flex; align-items: center; justify-content: space-between; min-height: 54px; }
    .mp-select-trigger::after { content: '\f0d7'; font-family: 'FontAwesome'; color: var(--mp-muted); font-size: 14px; }
    .mp-select-trigger.placeholder { color: var(--mp-muted); }
    .mp-select-options { display: none; border: 1px solid var(--mp-border); border-top: none; border-radius: 0 0 14px 14px; background: #fff; max-height: 220px; overflow-y: auto; position: absolute; left: 0; right: 0; top: 100%; z-index: 10; }
    .mp-select-wrap.open .mp-select-options { display: block; }
    .mp-select-wrap.open .mp-select-trigger { border-radius: 14px 14px 0 0; }
    .mp-select-option { padding: 14px 16px; cursor: pointer; border-bottom: 1px solid var(--mp-border); font-size: 16px; }
    .mp-select-option:last-child { border-bottom: none; }
    .mp-select-option:hover, .mp-select-option.active { background: var(--mp-bg); }
    .image-preview { width: 100%; max-width: 200px; border-radius: 12px; margin-top: 10px; }
    .btn-primary { width: 100%; padding: 18px; border: none; border-radius: 14px; background: var(--mp-primary); color: white; font-size: 17px; font-weight: 600; cursor: pointer; }
    .btn-secondary { width: 100%; padding: 18px; border: 1px solid var(--mp-border); border-radius: 14px; background: #fff; color: var(--mp-ink); font-size: 17px; font-weight: 600; cursor: pointer; text-decoration: none; display: block; text-align: center; margin-top: 10px; }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/automobile'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= !empty($vehicle) ? 'Edit Vehicle' : 'Add Vehicle'; ?></h1>
        </div>
      </div>

      <form method="post" action="<?= base_url('mobile/save_vehicle'); ?>" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?= !empty($vehicle) ? (int) $vehicle->id : ''; ?>">

        <div class="form-group">
          <label>Vehicle Code</label>
          <input type="text" name="vehicle_code" class="form-control" value="<?= !empty($vehicle) ? htmlspecialchars($vehicle->vehicle_code) : ''; ?>" placeholder="e.g. VH-001">
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Make <span style="color:var(--mp-danger)">*</span></label>
            <input type="text" name="make" class="form-control" value="<?= !empty($vehicle) ? htmlspecialchars($vehicle->make) : ''; ?>" placeholder="Toyota" required>
          </div>
          <div class="form-group">
            <label>Model <span style="color:var(--mp-danger)">*</span></label>
            <input type="text" name="model" class="form-control" value="<?= !empty($vehicle) ? htmlspecialchars($vehicle->model) : ''; ?>" placeholder="Corolla" required>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Year</label>
            <input type="number" name="year" class="form-control" value="<?= !empty($vehicle) ? htmlspecialchars($vehicle->year) : ''; ?>" placeholder="2020">
          </div>
          <div class="form-group">
            <label>Color</label>
            <input type="text" name="color" class="form-control" value="<?= !empty($vehicle) ? htmlspecialchars($vehicle->color) : ''; ?>" placeholder="Silver">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Mileage (km)</label>
            <input type="number" name="mileage" class="form-control" value="<?= !empty($vehicle) ? htmlspecialchars($vehicle->mileage) : ''; ?>" placeholder="45000">
          </div>
          <div class="form-group">
            <label>License Plate</label>
            <input type="text" name="license_plate" class="form-control" value="<?= !empty($vehicle) ? htmlspecialchars($vehicle->license_plate) : ''; ?>" placeholder="ABC-1234">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Fuel Type</label>
            <select class="mp-select" name="fuel_type">
              <option value="">Select</option>
              <option value="Petrol" <?= !empty($vehicle) && $vehicle->fuel_type === 'Petrol' ? 'selected' : ''; ?>>Petrol</option>
              <option value="Diesel" <?= !empty($vehicle) && $vehicle->fuel_type === 'Diesel' ? 'selected' : ''; ?>>Diesel</option>
              <option value="Hybrid" <?= !empty($vehicle) && $vehicle->fuel_type === 'Hybrid' ? 'selected' : ''; ?>>Hybrid</option>
              <option value="Electric" <?= !empty($vehicle) && $vehicle->fuel_type === 'Electric' ? 'selected' : ''; ?>>Electric</option>
            </select>
          </div>
          <div class="form-group">
            <label>Transmission</label>
            <select class="mp-select" name="transmission">
              <option value="">Select</option>
              <option value="Automatic" <?= !empty($vehicle) && $vehicle->transmission === 'Automatic' ? 'selected' : ''; ?>>Automatic</option>
              <option value="Manual" <?= !empty($vehicle) && $vehicle->transmission === 'Manual' ? 'selected' : ''; ?>>Manual</option>
              <option value="CVT" <?= !empty($vehicle) && $vehicle->transmission === 'CVT' ? 'selected' : ''; ?>>CVT</option>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Condition</label>
            <select class="mp-select" name="vehicle_condition">
              <option value="new" <?= !empty($vehicle) && $vehicle->vehicle_condition === 'new' ? 'selected' : ''; ?>>New</option>
              <option value="foreign_used" <?= !empty($vehicle) && $vehicle->vehicle_condition === 'foreign_used' ? 'selected' : ''; ?>>Foreign Used</option>
              <option value="locally_used" <?= !empty($vehicle) && $vehicle->vehicle_condition === 'locally_used' ? 'selected' : ''; ?>>Locally Used</option>
              <option value="accident_free" <?= !empty($vehicle) && $vehicle->vehicle_condition === 'accident_free' ? 'selected' : ''; ?>>Accident Free</option>
              <option value="certified" <?= !empty($vehicle) && $vehicle->vehicle_condition === 'certified' ? 'selected' : ''; ?>>Certified Pre-Owned</option>
            </select>
          </div>
          <div class="form-group">
            <label>Status</label>
            <select class="mp-select" name="status">
              <option value="available" <?= !empty($vehicle) && $vehicle->status === 'available' ? 'selected' : ''; ?>>Available</option>
              <option value="reserved" <?= !empty($vehicle) && $vehicle->status === 'reserved' ? 'selected' : ''; ?>>Reserved</option>
              <option value="sold" <?= !empty($vehicle) && $vehicle->status === 'sold' ? 'selected' : ''; ?>>Sold</option>
            </select>
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Body Type</label>
            <select class="mp-select" name="body_type">
              <option value="">Select</option>
              <option value="Sedan" <?= !empty($vehicle) && $vehicle->body_type === 'Sedan' ? 'selected' : ''; ?>>Sedan</option>
              <option value="SUV" <?= !empty($vehicle) && $vehicle->body_type === 'SUV' ? 'selected' : ''; ?>>SUV</option>
              <option value="Truck" <?= !empty($vehicle) && $vehicle->body_type === 'Truck' ? 'selected' : ''; ?>>Truck</option>
              <option value="Coupe" <?= !empty($vehicle) && $vehicle->body_type === 'Coupe' ? 'selected' : ''; ?>>Coupe</option>
              <option value="Hatchback" <?= !empty($vehicle) && $vehicle->body_type === 'Hatchback' ? 'selected' : ''; ?>>Hatchback</option>
              <option value="Wagon" <?= !empty($vehicle) && $vehicle->body_type === 'Wagon' ? 'selected' : ''; ?>>Wagon</option>
              <option value="Van" <?= !empty($vehicle) && $vehicle->body_type === 'Van' ? 'selected' : ''; ?>>Van</option>
              <option value="Bus" <?= !empty($vehicle) && $vehicle->body_type === 'Bus' ? 'selected' : ''; ?>>Bus</option>
              <option value="Convertible" <?= !empty($vehicle) && $vehicle->body_type === 'Convertible' ? 'selected' : ''; ?>>Convertible</option>
            </select>
          </div>
          <div class="form-group">
            <label>Engine Capacity</label>
            <input type="text" name="engine_capacity" class="form-control" value="<?= !empty($vehicle) ? htmlspecialchars($vehicle->engine_capacity) : ''; ?>" placeholder="e.g. 2.0L">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Drivetrain</label>
            <select class="mp-select" name="drivetrain">
              <option value="">Select</option>
              <option value="FWD" <?= !empty($vehicle) && $vehicle->drivetrain === 'FWD' ? 'selected' : ''; ?>>FWD</option>
              <option value="RWD" <?= !empty($vehicle) && $vehicle->drivetrain === 'RWD' ? 'selected' : ''; ?>>RWD</option>
              <option value="AWD" <?= !empty($vehicle) && $vehicle->drivetrain === 'AWD' ? 'selected' : ''; ?>>AWD</option>
              <option value="4WD" <?= !empty($vehicle) && $vehicle->drivetrain === '4WD' ? 'selected' : ''; ?>>4WD</option>
            </select>
          </div>
          <div class="form-group">
            <label>Trim Level</label>
            <input type="text" name="trim_level" class="form-control" value="<?= !empty($vehicle) ? htmlspecialchars($vehicle->trim_level) : ''; ?>" placeholder="e.g. XLE Sport">
          </div>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Previous Owners</label>
            <input type="number" name="number_of_owners" class="form-control" value="<?= !empty($vehicle) ? (int) $vehicle->number_of_owners : ''; ?>">
          </div>
          <div class="form-group">
            <label>Registration Date</label>
            <input type="date" name="registration_date" class="form-control" value="<?= !empty($vehicle) ? htmlspecialchars($vehicle->registration_date) : ''; ?>">
          </div>
        </div>

        <div class="form-group">
          <label>VIN</label>
          <input type="text" name="vin" class="form-control" value="<?= !empty($vehicle) ? htmlspecialchars($vehicle->vin) : ''; ?>" placeholder="Vehicle Identification Number">
        </div>

        <div class="form-group">
          <label>Description / Specifications</label>
          <textarea name="description" class="form-control" placeholder="Engine, trim, features, etc."><?= !empty($vehicle) ? htmlspecialchars($vehicle->description) : ''; ?></textarea>
        </div>

        <div class="form-row">
          <div class="form-group">
            <label>Price <span style="color:var(--mp-danger)">*</span></label>
            <input type="number" step="0.01" name="price" class="form-control" value="<?= !empty($vehicle) ? number_format($vehicle->price, 2, '.', '') : ''; ?>" placeholder="0.00" required>
          </div>
          <div class="form-group">
            <label>Cost / Purchase Price</label>
            <input type="number" step="0.01" name="cost" class="form-control" value="<?= !empty($vehicle) ? number_format($vehicle->cost, 2, '.', '') : ''; ?>" placeholder="0.00">
          </div>
        </div>

        <div class="form-group">
          <label>Customer / Buyer Name</label>
          <input type="text" name="customer_name" class="form-control" value="<?= !empty($vehicle) ? htmlspecialchars($vehicle->customer_name) : ''; ?>" placeholder="For reserved or sold units">
        </div>

        <div class="form-group">
          <label>Vehicle Image</label>
          <input type="file" name="vehicle_image" class="form-control" accept="image/png,image/jpeg,image/webp">
          <p style="font-size:12px; color:var(--mp-muted); margin-top:6px;">Max 2MB. JPG, PNG, or WebP.</p>
          <?php if (!empty($vehicle) && !empty($vehicle->image_path) && file_exists(FCPATH . $vehicle->image_path)): ?>
            <img src="<?= base_url($vehicle->image_path); ?>" class="image-preview">
          <?php endif; ?>
        </div>

        <button type="submit" class="btn-primary"><i class="fa fa-save"></i> Save Vehicle</button>
        <a href="<?= base_url('mobile/automobile'); ?>" class="btn-secondary">Cancel</a>
      </form>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
    <?php $this->load->view('mobile/mp_alert'); ?>
    <?php $this->load->view('mobile/chat'); ?>
  </div>

  <script>
    function closeAllMpSelects(){
      document.querySelectorAll('.mp-select-wrap.open').forEach(function(w){ w.classList.remove('open'); });
    }
    function initMpSelects(){
      document.querySelectorAll('select.mp-select').forEach(function(sel){
        if(sel.dataset.mpInit) return;
        sel.dataset.mpInit = '1';
        var wrap = document.createElement('div');
        wrap.className = 'mp-select-wrap';
        sel.parentNode.insertBefore(wrap, sel);
        wrap.appendChild(sel);
        var trigger = document.createElement('div');
        trigger.className = 'mp-select-trigger';
        wrap.appendChild(trigger);
        var list = document.createElement('div');
        list.className = 'mp-select-options';
        wrap.appendChild(list);
        var options = Array.from(sel.options);
        function renderOptions(){
          list.innerHTML = '';
          options.forEach(function(opt, idx){
            var div = document.createElement('div');
            div.className = 'mp-select-option';
            div.textContent = opt.textContent;
            if(sel.selectedIndex === idx) div.classList.add('active');
            div.addEventListener('click', function(e){
              e.stopPropagation();
              sel.selectedIndex = idx;
              updateTrigger();
              sel.dispatchEvent(new Event('change', {bubbles: true}));
              closeAllMpSelects();
            });
            list.appendChild(div);
          });
        }
        function updateTrigger(){
          var s = sel.options[sel.selectedIndex];
          trigger.textContent = s ? s.textContent : 'Select';
          trigger.classList.toggle('placeholder', !s || !s.value);
          renderOptions();
        }
        trigger.addEventListener('click', function(e){
          e.stopPropagation();
          closeAllMpSelects();
          wrap.classList.toggle('open');
        });
        sel.addEventListener('change', updateTrigger);
        updateTrigger();
      });
      document.addEventListener('click', closeAllMpSelects);
    }
    document.addEventListener('DOMContentLoaded', initMpSelects);
  </script>
</body>
</html>
