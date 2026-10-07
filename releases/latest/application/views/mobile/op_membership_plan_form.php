<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?> — <?= htmlspecialchars($page_title); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 130px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 10px; background: var(--mp-bg); }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0; }
    .section { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; margin-bottom: 12px; }
    .section h3 { font-size: 13px; font-weight: 700; margin: 0 0 12px; color: var(--mp-muted); text-transform: uppercase; letter-spacing: 0.4px; }
    .field { display: flex; flex-direction: column; gap: 6px; margin-bottom: 14px; }
    .field:last-child { margin-bottom: 0; }
    .field label { font-size: 13px; color: var(--mp-muted); font-weight: 600; }
    .field input, .field textarea { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-family: inherit; font-size: 15px; background: #fff; color: var(--mp-ink); }
    .field textarea { min-height: 80px; resize: vertical; }
    .grid2 { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .mp-select { display: none; }
    .mp-select-wrap { position: relative; }
    .mp-select-trigger { display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; background: #fff; font-size: 15px; cursor: pointer; }
    .mp-select-options { display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 200; background: #fff; border: 1px solid var(--mp-border); border-radius: 12px; max-height: 220px; overflow-y: auto; margin-top: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .mp-select-options.open { display: block; }
    .mp-option { padding: 12px 14px; border-bottom: 1px solid var(--mp-border); cursor: pointer; font-size: 14px; }
    .mp-option:last-child { border-bottom: none; }
    .mp-option.active { background: #E0E7FF; color: var(--mp-primary); font-weight: 600; }
    .check-row { display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; background: var(--mp-bg); border-radius: 12px; }
    .check-row label { font-size: 14px; color: var(--mp-ink); }
    .toggle { width: 46px; height: 26px; border-radius: 13px; background: #E2E8F0; position: relative; cursor: pointer; transition: background 0.2s; flex-shrink: 0; }
    .toggle input { opacity: 0; width: 100%; height: 100%; position: absolute; cursor: pointer; }
    .toggle .knob { position: absolute; top: 3px; left: 3px; width: 20px; height: 20px; border-radius: 50%; background: #fff; transition: transform 0.2s; }
    .toggle.on { background: var(--mp-primary); }
    .toggle.on .knob { transform: translateX(20px); }
    .save-btn { width: 100%; padding: 16px; border: none; border-radius: 14px; background: var(--mp-primary); color: #fff; font-size: 16px; font-weight: 700; cursor: pointer; margin-top: 4px; }
    .save-btn:disabled { opacity: 0.6; }
    .toast { position: fixed; left: 50%; bottom: 110px; transform: translateX(-50%); background: #111827; color: #fff; padding: 10px 18px; border-radius: 10px; font-size: 13px; display: none; z-index: 300; max-width: 90%; }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 130px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 150px; } }
  </style>
</head>
<body>
  <div id="toast" class="toast"></div>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/membership_plans'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= htmlspecialchars($page_title); ?></h1>
        </div>
      </div>

      <form id="planForm" method="post" action="<?= base_url('mobile/membership_plan_save'); ?>" onsubmit="return savePlan(event);" autocomplete="off">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <?php if($edit_plan): ?><input type="hidden" name="id" value="<?= (int)$edit_plan->id; ?>"><?php endif; ?>

        <div class="section">
          <h3>Plan</h3>
          <div class="field"><label>Plan Name *</label><input type="text" name="plan_name" value="<?= $edit_plan ? htmlspecialchars($edit_plan->plan_name) : ''; ?>" required></div>
          <div class="grid2">
            <div class="field"><label>Code</label><input type="text" name="plan_code" placeholder="e.g. VIP-M" value="<?= $edit_plan ? htmlspecialchars($edit_plan->plan_code ?? '') : ''; ?>"></div>
            <div class="field"><label>Price *</label><input type="number" step="any" name="price" value="<?= $edit_plan ? htmlspecialchars($edit_plan->price) : ''; ?>" required></div>
          </div>
          <div class="field">
            <label>Billing Cycle *</label>
            <select name="billing_cycle" class="mp-select" required>
              <?php foreach(['monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'annual' => 'Annual'] as $k => $v): ?>
              <option value="<?= $k; ?>" <?= ($edit_plan && $edit_plan->billing_cycle === $k) ? 'selected' : ''; ?>><?= $v; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="field"><label>Description</label><textarea name="description" placeholder="What this plan includes…"><?= $edit_plan ? htmlspecialchars($edit_plan->description ?? '') : ''; ?></textarea></div>
        </div>

        <div class="section">
          <h3>Perks</h3>
          <div class="grid2">
            <div class="field"><label>Discount %</label><input type="number" step="any" name="discount_percent" min="0" max="100" value="<?= $edit_plan ? htmlspecialchars($edit_plan->discount_percent ?? 0) : 0; ?>"></div>
            <div class="field"><label>Free services / period</label><input type="number" name="free_services_per_period" min="0" value="<?= $edit_plan ? (int)$edit_plan->free_services_per_period : 0; ?>"></div>
          </div>
          <div class="check-row">
            <label>Priority Booking</label>
            <span class="toggle <?= ($edit_plan && $edit_plan->priority_booking) ? 'on' : ''; ?>" id="priorityToggle">
              <input type="checkbox" name="priority_booking" value="1" <?= ($edit_plan && $edit_plan->priority_booking) ? 'checked' : ''; ?>>
              <span class="knob"></span>
            </span>
          </div>
        </div>

        <button type="submit" class="save-btn" id="saveBtn"><i class="fa fa-check"></i> <?= $edit_plan ? 'Update Plan' : 'Create Plan'; ?></button>
      </form>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    var base_url = '<?= base_url(); ?>';

    function showToast(msg){ var t = document.getElementById('toast'); t.textContent = msg; t.style.display = 'block'; setTimeout(function(){ t.style.display = 'none'; }, 3000); }

    document.querySelectorAll('select.mp-select').forEach(function(sel){
      var wrap = document.createElement('div'); wrap.className = 'mp-select-wrap';
      sel.parentNode.insertBefore(wrap, sel); wrap.appendChild(sel);
      var trigger = document.createElement('div'); trigger.className = 'mp-select-trigger';
      var opts = document.createElement('div'); opts.className = 'mp-select-options';
      var label = document.createElement('span');
      var icon = document.createElement('i'); icon.className = 'fa fa-chevron-down'; icon.style.fontSize = '12px';
      trigger.appendChild(label); trigger.appendChild(icon);
      wrap.appendChild(trigger); wrap.appendChild(opts);
      function setLabel(){ label.textContent = sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].text : 'Select'; }
      Array.from(sel.options).forEach(function(opt, idx){
        var d = document.createElement('div'); d.className = 'mp-option'; d.textContent = opt.text;
        if(idx === sel.selectedIndex) d.classList.add('active');
        d.addEventListener('click', function(e){
          e.stopPropagation(); sel.selectedIndex = idx; setLabel();
          opts.querySelectorAll('.mp-option').forEach(function(o){ o.classList.remove('active'); });
          d.classList.add('active'); opts.classList.remove('open');
        });
        opts.appendChild(d);
      });
      setLabel();
      trigger.addEventListener('click', function(e){
        e.stopPropagation();
        document.querySelectorAll('.mp-select-options.open').forEach(function(o){ if(o !== opts) o.classList.remove('open'); });
        opts.classList.toggle('open');
      });
    });
    document.addEventListener('click', function(){ document.querySelectorAll('.mp-select-options.open').forEach(function(o){ o.classList.remove('open'); }); });

    var pt = document.getElementById('priorityToggle');
    pt.querySelector('input').addEventListener('change', function(){ pt.classList.toggle('on', this.checked); });

    function savePlan(e){
      e.preventDefault();
      var btn = document.getElementById('saveBtn'); btn.disabled = true;
      var form = document.getElementById('planForm');
      mpFetchJson(form.action, { method: 'POST', body: new FormData(form) })
        .then(function(d){
          if(d && d.csrf_hash){
            var ci = form.querySelector('input[name="<?= $this->security->get_csrf_token_name(); ?>"]');
            if(ci) ci.value = d.csrf_hash;
          }
          if(d && d.success){
            showToast(d.message || 'Saved.');
            setTimeout(function(){ window.location.href = base_url + 'mobile/membership_plans'; }, 600);
          } else {
            showToast(d && d.message ? d.message : 'Could not save this plan. Please check the details and try again.');
            btn.disabled = false;
          }
        })
        .catch(function(err){ showToast(mpErrorText(err)); btn.disabled = false; });
      return false;
    }
  </script>
</body>
</html>
