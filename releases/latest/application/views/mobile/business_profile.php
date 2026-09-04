<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Business Profile</title>
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
    #app { max-width: 100%; margin: 0; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 16px 110px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 10px; background: var(--mp-bg); }
    .topbar-titles { flex: 1; min-width: 0; }
    .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .greeting { font-size: 13px; color: var(--mp-muted); margin: -4px 0 16px; }
    .toast { position: fixed; top: 16px; left: 16px; right: 16px; padding: 14px; border-radius: 12px; text-align: center; color: #fff; font-weight: 600; z-index: 1000; display: none; }
    .section { background: #fff; border: 1px solid var(--mp-border); border-radius: 18px; margin-bottom: 14px; overflow: hidden; box-shadow: 0 1px 3px rgba(15,23,42,0.04); }
    .section summary { display: flex; align-items: center; justify-content: space-between; padding: 16px; font-size: 15px; font-weight: 700; cursor: pointer; list-style: none; }
    .section summary::-webkit-details-marker { display: none; }
    .section summary .arrow { color: var(--mp-muted); transition: transform 0.2s ease; }
    .section[open] summary .arrow { transform: rotate(90deg); }
    .section-body { padding: 0 16px 16px; display: flex; flex-direction: column; gap: 16px; }
    .field { display: flex; flex-direction: column; gap: 6px; }
    .field label { font-size: 13px; color: var(--mp-muted); font-weight: 600; }
    .field .hint { font-size: 12px; color: var(--mp-muted); }
    .mp-select { position: relative; }
    .mp-select .trigger { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; background: #fff; display: flex; align-items: center; justify-content: space-between; font-size: 14px; color: var(--mp-ink); }
    .mp-select .trigger i { color: var(--mp-muted); }
    .mp-select .options { display: none; border: 1px solid var(--mp-border); border-radius: 12px; background: #fff; max-height: 220px; overflow-y: auto; margin-top: 6px; z-index: 10; }
    .mp-select.open .options { display: block; }
    .mp-select .option { padding: 11px 14px; font-size: 14px; border-bottom: 1px solid var(--mp-border); cursor: pointer; }
    .mp-select .option:last-child { border-bottom: none; }
    .mp-select .option.selected { background: #EFF6FF; color: var(--mp-primary); font-weight: 600; }
    .mp-select .hidden-select { position: absolute; opacity: 0; pointer-events: none; }
    .flag-list { display: flex; flex-direction: column; gap: 10px; max-height: 360px; overflow-y: auto; padding-right: 4px; }
    .flag-item { display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; background: var(--mp-bg); border-radius: 12px; }
    .flag-item label { font-size: 14px; color: var(--mp-ink); }
    .toggle { width: 46px; height: 26px; border-radius: 13px; background: #E2E8F0; position: relative; cursor: pointer; transition: background 0.2s; flex-shrink: 0; }
    .toggle input { opacity: 0; width: 100%; height: 100%; position: absolute; cursor: pointer; }
    .toggle .knob { position: absolute; top: 3px; left: 3px; width: 20px; height: 20px; border-radius: 50%; background: #fff; transition: transform 0.2s; }
    .toggle.on { background: var(--mp-primary); }
    .toggle.on .knob { transform: translateX(20px); }
    .label-list { display: grid; grid-template-columns: 1fr; gap: 12px; max-height: 400px; overflow-y: auto; padding-right: 4px; }
    .label-item { display: flex; flex-direction: column; gap: 6px; }
    .label-item .key { font-size: 12px; color: var(--mp-muted); }
    .label-item input { padding: 10px 12px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 14px; background: #fff; }
    textarea { width: 100%; padding: 12px; border: 1px solid var(--mp-border); border-radius: 12px; font-family: inherit; font-size: 13px; min-height: 120px; resize: vertical; }
    .save-btn { width: 100%; padding: 16px; border-radius: 14px; border: none; background: var(--mp-primary); color: #fff; font-size: 16px; font-weight: 700; cursor: pointer; margin-top: 8px; }
    .save-btn:disabled { opacity: 0.6; }
    .actions { display: flex; gap: 10px; margin-top: 10px; }
    .btn-link { flex: 1; padding: 14px; border-radius: 14px; text-align: center; text-decoration: none; font-size: 14px; font-weight: 600; background: var(--mp-bg); color: var(--mp-ink); }
    @media (min-width: 430px) { .screen { padding: 16px 20px 120px; } }
    @media (min-width: 600px) { .screen { padding: 16px 24px 120px; } }
  </style>
</head>
<body>
  <div id="toast" class="toast"></div>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/more'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Business Profile</h1>
        </div>
      </div>
      <div class="greeting">Hello, <?= $display_name; ?></div>

      <form id="bpForm" action="<?= base_url('business_profile/save'); ?>" method="post" onsubmit="return saveProfile(event);" autocomplete="off">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

        <details class="section" open>
          <summary>Business Identity <i class="fa fa-chevron-right arrow"></i></summary>
          <div class="section-body">
            <?php
              $selectedIndustry = $profile['industry_type'] ?? '';
              $selectedModel = $profile['business_model'] ?? '';
              $selectedTheme = $profile['storefront_theme_key'] ?? '';
            ?>
            <div class="field">
              <label>Business Type</label>
              <div class="mp-select" data-select="industry_type">
                <select name="industry_type" id="industry_type" class="hidden-select">
                  <option value="">Select Business Type</option>
                  <?php foreach($business_types as $k => $label): ?>
                    <option value="<?= $k; ?>" <?= ($selectedIndustry == $k) ? 'selected' : ''; ?>><?= $label; ?></option>
                  <?php endforeach; ?>
                </select>
                <div class="trigger"><span class="selected-text"><?= !empty($selectedIndustry) && isset($business_types[$selectedIndustry]) ? $business_types[$selectedIndustry] : 'Select Business Type'; ?></span><i class="fa fa-chevron-down"></i></div>
                <div class="options">
                  <?php foreach($business_types as $k => $label): ?>
                    <div class="option <?= ($selectedIndustry == $k) ? 'selected' : ''; ?>" data-value="<?= $k; ?>"><?= $label; ?></div>
                  <?php endforeach; ?>
                </div>
              </div>
              <div class="hint">Changing this updates the recommended theme and features on save.</div>
            </div>

            <div class="field">
              <label>Business Model</label>
              <div class="mp-select" data-select="business_model">
                <select name="business_model" id="business_model" class="hidden-select">
                  <option value="">Select Model</option>
                  <?php foreach($business_models as $k => $label): ?>
                    <option value="<?= $k; ?>" <?= ($selectedModel == $k) ? 'selected' : ''; ?>><?= $label; ?></option>
                  <?php endforeach; ?>
                </select>
                <div class="trigger"><span class="selected-text"><?= !empty($selectedModel) && isset($business_models[$selectedModel]) ? $business_models[$selectedModel] : 'Select Model'; ?></span><i class="fa fa-chevron-down"></i></div>
                <div class="options">
                  <?php foreach($business_models as $k => $label): ?>
                    <div class="option <?= ($selectedModel == $k) ? 'selected' : ''; ?>" data-value="<?= $k; ?>"><?= $label; ?></div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>

            <div class="field">
              <label>Storefront Theme</label>
              <div class="mp-select" data-select="storefront_theme_key">
                <select name="storefront_theme_key" id="storefront_theme_key" class="hidden-select">
                  <option value="">Select Theme</option>
                  <?php foreach($storefront_themes as $k => $theme):
                    $tk = isset($theme->theme_key) ? $theme->theme_key : $k; ?>
                    <option value="<?= htmlspecialchars($tk); ?>" <?= ($selectedTheme == $tk) ? 'selected' : ''; ?>><?= htmlspecialchars($theme->theme_name); ?></option>
                  <?php endforeach; ?>
                </select>
                <div class="trigger"><span class="selected-text"><?php
                  $selected_theme_name = 'Select Theme';
                  if (!empty($selectedTheme)) {
                    foreach ($storefront_themes as $t) {
                      if ((isset($t->theme_key) ? $t->theme_key : null) === $selectedTheme) {
                        $selected_theme_name = $t->theme_name;
                        break;
                      }
                    }
                  }
                  echo htmlspecialchars($selected_theme_name);
                ?></span><i class="fa fa-chevron-down"></i></div>
                <div class="options">
                  <?php foreach($storefront_themes as $k => $theme):
                    $tk = isset($theme->theme_key) ? $theme->theme_key : $k; ?>
                    <div class="option <?= ($selectedTheme == $tk) ? 'selected' : ''; ?>" data-value="<?= htmlspecialchars($tk); ?>"><?= htmlspecialchars($theme->theme_name); ?></div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div>
        </details>

        <details class="section" open>
          <summary>Templates <i class="fa fa-chevron-right arrow"></i></summary>
          <div class="section-body">
            <?php
              $selectedWorkflow = $profile['workflow_template_key'] ?? '';
              $selectedDashboard = $profile['dashboard_template_key'] ?? '';
            ?>
            <div class="field">
              <label>Workflow Template</label>
              <div class="mp-select" data-select="workflow_template_key">
                <select name="workflow_template_key" id="workflow_template_key" class="hidden-select">
                  <option value="">Select Workflow</option>
                  <?php foreach($workflow_templates as $k => $label): ?>
                    <option value="<?= $k; ?>" <?= ($selectedWorkflow == $k) ? 'selected' : ''; ?>><?= $label; ?></option>
                  <?php endforeach; ?>
                </select>
                <div class="trigger"><span class="selected-text"><?= !empty($selectedWorkflow) && isset($workflow_templates[$selectedWorkflow]) ? $workflow_templates[$selectedWorkflow] : 'Select Workflow'; ?></span><i class="fa fa-chevron-down"></i></div>
                <div class="options">
                  <?php foreach($workflow_templates as $k => $label): ?>
                    <div class="option <?= ($selectedWorkflow == $k) ? 'selected' : ''; ?>" data-value="<?= $k; ?>"><?= $label; ?></div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>

            <div class="field">
              <label>Dashboard Template</label>
              <div class="mp-select" data-select="dashboard_template_key">
                <select name="dashboard_template_key" id="dashboard_template_key" class="hidden-select">
                  <option value="">Select Dashboard</option>
                  <?php foreach($dashboard_templates as $k => $label): ?>
                    <option value="<?= $k; ?>" <?= ($selectedDashboard == $k) ? 'selected' : ''; ?>><?= $label; ?></option>
                  <?php endforeach; ?>
                </select>
                <div class="trigger"><span class="selected-text"><?= !empty($selectedDashboard) && isset($dashboard_templates[$selectedDashboard]) ? $dashboard_templates[$selectedDashboard] : 'Select Dashboard'; ?></span><i class="fa fa-chevron-down"></i></div>
                <div class="options">
                  <?php foreach($dashboard_templates as $k => $label): ?>
                    <div class="option <?= ($selectedDashboard == $k) ? 'selected' : ''; ?>" data-value="<?= $k; ?>"><?= $label; ?></div>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
          </div>
        </details>

        <details class="section">
          <summary>Feature Flags (<?= count($feature_flags); ?>) <i class="fa fa-chevron-right arrow"></i></summary>
          <div class="section-body">
            <div class="flag-list">
              <?php foreach($feature_flags as $key => $label):
                if (isset($current_flags[$key])) {
                  $is_flagged = filter_var($current_flags[$key], FILTER_VALIDATE_BOOLEAN);
                } else {
                  if ($key === 'mfg_tracking') {
                    $is_flagged = mp_feature_enabled('expiry_tracking');
                  } elseif ($key === 'accounts') {
                    $is_flagged = accounts_module();
                  } elseif ($key === 'warehouse') {
                    $is_flagged = warehouse_module();
                  } else {
                    $is_flagged = false;
                  }
                }
              ?>
                <div class="flag-item">
                  <label for="flag_<?= $key; ?>"><?= $label; ?></label>
                  <div class="toggle <?= $is_flagged ? 'on' : ''; ?>">
                    <input type="checkbox" name="feature_flags[<?= $key; ?>]" id="flag_<?= $key; ?>" value="1" <?= $is_flagged ? 'checked' : ''; ?> onchange="this.parentElement.classList.toggle('on', this.checked)">
                    <div class="knob"></div>
                  </div>
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </details>

        <details class="section">
          <summary>Label Overrides (<?= count($label_defaults); ?>) <i class="fa fa-chevron-right arrow"></i></summary>
          <div class="section-body">
            <div class="label-list">
              <?php foreach($label_defaults as $key => $default): ?>
                <div class="label-item">
                  <div class="key"><?= $default; ?></div>
                  <input type="text" name="label_overrides[<?= $key; ?>]" value="<?= htmlspecialchars($current_labels[$key] ?? ''); ?>" placeholder="<?= $default; ?>">
                </div>
              <?php endforeach; ?>
            </div>
          </div>
        </details>

        <details class="section">
          <summary>Advanced <i class="fa fa-chevron-right arrow"></i></summary>
          <div class="section-body">
            <div class="field">
              <label>Industry Settings JSON</label>
              <textarea name="industry_settings_json" placeholder="{}"><?= !empty($profile['industry_settings_json']) ? htmlspecialchars($profile['industry_settings_json']) : ''; ?></textarea>
              <div class="hint">Only edit if you know what you are doing.</div>
            </div>
          </div>
        </details>

        <button type="submit" class="save-btn" id="saveBtn"><i class="fa fa-save"></i> Save Business Profile</button>
      </form>

      <div class="actions" style="margin-top: 12px;">
        <a href="<?= base_url('mobile/operations'); ?>" class="btn-link">Back to Operations</a>
      </div>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    function showToast(msg, isError){
      const t = document.getElementById('toast');
      t.textContent = msg;
      t.style.background = isError ? 'var(--mp-danger)' : 'var(--mp-success)';
      t.style.display = 'block';
      setTimeout(() => t.style.display = 'none', 3000);
    }

    document.querySelectorAll('.mp-select').forEach(function(select){
      const trigger = select.querySelector('.trigger');
      const options = select.querySelector('.options');
      const realSelect = select.querySelector('.hidden-select');
      const selectedText = select.querySelector('.selected-text');

      trigger.addEventListener('click', function(e){
        e.stopPropagation();
        document.querySelectorAll('.mp-select').forEach(function(other){
          if(other !== select) other.classList.remove('open');
        });
        select.classList.toggle('open');
      });

      options.querySelectorAll('.option').forEach(function(opt){
        opt.addEventListener('click', function(){
          const val = this.dataset.value;
          const txt = this.textContent;
          realSelect.value = val;
          selectedText.textContent = txt;
          options.querySelectorAll('.option').forEach(function(o){ o.classList.remove('selected'); });
          this.classList.add('selected');
          select.classList.remove('open');
        });
      });
    });

    document.addEventListener('click', function(e){
      document.querySelectorAll('.mp-select').forEach(function(select){
        if(!select.contains(e.target)) select.classList.remove('open');
      });
    });

    function saveProfile(e){
      e.preventDefault();
      const btn = document.getElementById('saveBtn');
      btn.disabled = true;
      const form = document.getElementById('bpForm');
      const formData = new FormData(form);

      fetch(form.action, {method: 'POST', body: formData})
        .then(r => r.json())
        .then(d => {
          showToast(d.message, d.status !== 'success');
          btn.disabled = false;
          if(d.status === 'success'){
            // refresh csrf token if returned
            if(d.csrf_hash){
              const csrfInput = form.querySelector('input[name="<?= $this->security->get_csrf_token_name(); ?>"]');
              if(csrfInput) csrfInput.value = d.csrf_hash;
            }
          }
        })
        .catch(() => { showToast('Save failed. Please try again.', true); btn.disabled = false; });
      return false;
    }
  </script>
</body>
</html>
