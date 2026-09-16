<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Cold Chain</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-info: #3B82F6; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 120px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
    .topbar .back { color: var(--mp-ink); font-size: 18px; text-decoration: none; }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .section-title { font-size: 14px; font-weight: 600; color: var(--mp-muted); margin: 20px 0 10px; text-transform: uppercase; }
    .form-group { margin-bottom: 14px; }
    .form-group label { display: block; font-size: 14px; font-weight: 500; margin-bottom: 6px; }
    .form-control { width: 100%; padding: 14px 16px; border: 1px solid var(--mp-border); border-radius: 14px; font-size: 16px; background: #fff; outline: none; min-height: 54px; }
    .form-control:focus { border-color: var(--mp-primary); }
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
    .btn-primary { width: 100%; padding: 18px; border: none; border-radius: 14px; background: var(--mp-primary); color: white; font-size: 17px; font-weight: 600; cursor: pointer; }
    .btn-secondary { width: 100%; padding: 18px; border: 1px solid var(--mp-border); border-radius: 14px; background: #fff; color: var(--mp-ink); font-size: 17px; font-weight: 600; cursor: pointer; text-decoration: none; display: block; text-align: center; margin-top: 10px; }
    .freezer-card { background: #fff; border: 1px solid var(--mp-border); border-radius: 14px; padding: 12px; margin-bottom: 10px; }
    .freezer-card .name { font-weight: 600; font-size: 15px; }
    .freezer-card .meta { font-size: 12px; color: var(--mp-muted); margin-top: 4px; }
    .log-card { background: #fff; border: 1px solid var(--mp-border); border-radius: 14px; padding: 12px; margin-bottom: 10px; }
    .log-card .row { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
    .badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
    .badge-success { background: #ECFDF5; color: #065F46; }
    .badge-warning { background: #FFFBEB; color: #92400E; }
    .badge-danger { background: #FEF2F2; color: #B91C1C; }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/butchery'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Cold Chain</h1>
        </div>
      </div>

      <div class="section-title">Record Temperature</div>
      <form method="post" action="<?= base_url('mobile/butchery_coldchain'); ?>">
        <div class="form-group">
          <label>Freezer / Cold Room <span style="color:var(--mp-danger)">*</span></label>
          <select class="mp-select" name="freezer_location_id" required>
            <option value="">Select</option>
            <?php foreach ($freezers as $f): ?>
            <option value="<?= $f->id; ?>"><?= htmlspecialchars($f->location_name . ' (' . ($f->temp_min ?: '—') . ' to ' . ($f->temp_max ?: '—') . '°C)'); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Temperature (°C) <span style="color:var(--mp-danger)">*</span></label>
            <input type="number" step="0.01" name="temperature_c" class="form-control" placeholder="-18.00" required>
          </div>
          <div class="form-group">
            <label>Notes</label>
            <input type="text" name="notes" class="form-control" placeholder="Optional">
          </div>
        </div>
        <button type="submit" class="btn-primary"><i class="fa fa-thermometer"></i> Record</button>
      </form>

      <div class="section-title">Add Freezer / Cold Room</div>
      <form method="post" action="<?= base_url('mobile/butchery_coldchain'); ?>">
        <div class="form-group">
          <label>Name <span style="color:var(--mp-danger)">*</span></label>
          <input type="text" name="location_name" class="form-control" placeholder="e.g. Main Freezer" required>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Code</label>
            <input type="text" name="location_code" class="form-control" placeholder="FZ-01">
          </div>
          <div class="form-group">
            <label>Capacity</label>
            <input type="number" step="0.01" name="capacity_volume" class="form-control" placeholder="0.00">
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>Min Temp (°C)</label>
            <input type="number" step="0.1" name="temp_min" class="form-control" placeholder="-25">
          </div>
          <div class="form-group">
            <label>Max Temp (°C)</label>
            <input type="number" step="0.1" name="temp_max" class="form-control" placeholder="-18">
          </div>
        </div>
        <button type="submit" class="btn-primary" style="background:#10B981;"><i class="fa fa-snowflake-o"></i> Save Freezer</button>
      </form>

      <div class="section-title">Freezers</div>
      <?php if (!empty($freezers)): ?>
        <?php foreach ($freezers as $f): ?>
        <div class="freezer-card">
          <div class="name"><?= htmlspecialchars($f->location_name); ?></div>
          <div class="meta"><?= htmlspecialchars($f->location_code ?: '---'); ?> · <?= $f->temp_min !== null ? number_format($f->temp_min, 1) : '—'; ?> to <?= $f->temp_max !== null ? number_format($f->temp_max, 1) : '—'; ?> °C · Capacity <?= number_format($f->capacity_volume, 2); ?></div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="color:var(--mp-muted); text-align:center; padding:20px;">No freezers added.</p>
      <?php endif; ?>

      <div class="section-title">Recent Readings</div>
      <?php if (!empty($logs)): ?>
        <?php foreach ($logs as $l): ?>
        <div class="log-card">
          <div class="row">
            <strong><?= date('M d, H:i', strtotime($l->recorded_at)); ?></strong>
            <span class="badge badge-<?= $l->status === 'normal' ? 'success' : ($l->status === 'warning' ? 'warning' : 'danger'); ?>"><?= ucfirst($l->status); ?></span>
          </div>
          <div class="row" style="color:var(--mp-muted); font-size:13px;">
            <span>
              <?php
              $loc = null;
              foreach ($freezers as $f) { if ($f->id == $l->freezer_location_id) { $loc = $f; break; } }
              echo $loc ? htmlspecialchars($loc->location_name) : '---';
              ?>
            </span>
            <span><?= number_format($l->temperature_c, 2); ?> °C</span>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <p style="color:var(--mp-muted); text-align:center; padding:20px;">No readings yet.</p>
      <?php endif; ?>
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
