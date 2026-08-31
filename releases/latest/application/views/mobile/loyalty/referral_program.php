<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Referral Program</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 100%; margin: 0; background: var(--mp-bg); min-height: 100vh; position: relative; }
    .screen { padding: 12px 16px 110px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 10px; background: #fff; border: 1px solid var(--mp-border); }
    .topbar-titles { flex: 1; min-width: 0; }
    .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .section { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 18px; margin-bottom: 16px; overflow: hidden; box-shadow: 0 1px 3px rgba(15,23,42,0.04); }
    .section-head { padding: 16px; font-size: 16px; font-weight: 700; border-bottom: 1px solid var(--mp-border); }
    .section-body { padding: 16px; display: flex; flex-direction: column; gap: 16px; }
    .field { display: flex; flex-direction: column; gap: 6px; }
    .field label { font-size: 13px; color: var(--mp-muted); font-weight: 600; }
    .field input[type="number"] { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 14px; background: #fff; font-family: inherit; }
    .mp-select { position: relative; }
    .mp-select .trigger { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; background: #fff; display: flex; align-items: center; justify-content: space-between; font-size: 14px; color: var(--mp-ink); }
    .mp-select .trigger i { color: var(--mp-muted); }
    .mp-select .options { display: none; border: 1px solid var(--mp-border); border-radius: 12px; background: #fff; max-height: 220px; overflow-y: auto; margin-top: 6px; z-index: 10; }
    .mp-select.open .options { display: block; }
    .mp-select .option { padding: 11px 14px; font-size: 14px; border-bottom: 1px solid var(--mp-border); cursor: pointer; }
    .mp-select .option:last-child { border-bottom: none; }
    .mp-select .option.selected { background: #EFF6FF; color: var(--mp-primary); font-weight: 600; }
    .mp-select .hidden-select { position: absolute; opacity: 0; pointer-events: none; }
    .toggle { width: 46px; height: 26px; border-radius: 13px; background: #E2E8F0; position: relative; cursor: pointer; transition: background 0.2s; flex-shrink: 0; }
    .toggle input { opacity: 0; width: 100%; height: 100%; position: absolute; cursor: pointer; }
    .toggle .knob { position: absolute; top: 3px; left: 3px; width: 20px; height: 20px; border-radius: 50%; background: #fff; transition: transform 0.2s; }
    .toggle.on { background: var(--mp-primary); }
    .toggle.on .knob { transform: translateX(20px); }
    .flag-item { display: flex; align-items: center; justify-content: space-between; padding: 10px 12px; background: var(--mp-bg); border-radius: 12px; }
    .flag-item label { font-size: 14px; color: var(--mp-ink); }
    .grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .save-btn { width: 100%; padding: 16px; border-radius: 14px; border: none; background: var(--mp-primary); color: #fff; font-size: 16px; font-weight: 700; cursor: pointer; margin-top: 8px; }
    .save-btn:disabled { opacity: 0.6; }
    @media (min-width: 430px) { .screen { padding: 16px 20px 120px; } }
    @media (min-width: 600px) { .screen { padding: 16px 24px 120px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= $back_url; ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Referral Program</h1>
        </div>
      </div>

      <form id="referralForm" action="<?= base_url('loyalty/save_referral_settings'); ?>" method="post" onsubmit="return handleSave(this, 'Referral settings saved.');">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <div class="section">
          <div class="section-head">Referral Settings</div>
          <div class="section-body">
            <?php
              $enabled = !empty($settings->referral_enabled) ? 1 : 0;
              $approval = !empty($settings->referral_approval_required) ? 1 : 0;
              $rType = $settings->referrer_reward_type ?? 'points';
              $nType = $settings->new_customer_reward_type ?? 'points';
            ?>
            <div class="flag-item">
              <label for="referral_enabled">Enable Referral Program</label>
              <div class="toggle <?= $enabled ? 'on' : ''; ?>">
                <input type="checkbox" name="referral_enabled" id="referral_enabled" value="1" <?= $enabled ? 'checked' : ''; ?> onchange="this.parentElement.classList.toggle('on', this.checked)">
                <div class="knob"></div>
              </div>
            </div>

            <div class="field">
              <label>Referrer Reward Type</label>
              <?= renderMpSelect('referrer_reward_type', $rType, [
                'points' => 'Points', 'credit' => 'Store credit', 'discount' => 'Discount %'
              ], 'refType'); ?>
            </div>
            <div class="field">
              <label>Referrer Reward Value</label>
              <input type="number" step="0.01" name="referrer_reward_value" value="<?= (float)($settings->referrer_reward_value ?? 0); ?>">
            </div>

            <div class="field">
              <label>New Customer Reward Type</label>
              <?= renderMpSelect('new_customer_reward_type', $nType, [
                'points' => 'Points', 'credit' => 'Store credit', 'discount' => 'Discount %'
              ], 'newType'); ?>
            </div>
            <div class="field">
              <label>New Customer Reward Value</label>
              <input type="number" step="0.01" name="new_customer_reward_value" value="<?= (float)($settings->new_customer_reward_value ?? 0); ?>">
            </div>

            <div class="flag-item">
              <label for="referral_approval_required">Approval Required</label>
              <div class="toggle <?= $approval ? 'on' : ''; ?>">
                <input type="checkbox" name="referral_approval_required" id="referral_approval_required" value="1" <?= $approval ? 'checked' : ''; ?> onchange="this.parentElement.classList.toggle('on', this.checked)">
                <div class="knob"></div>
              </div>
            </div>

            <button type="submit" class="save-btn" id="refSave"><i class="fa fa-save"></i> Save Referral Settings</button>
          </div>
        </div>
      </form>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/chat'); ?>
  <?php $this->load->view('mobile/mp_alert'); ?>

  <?php
  function renderMpSelect($name, $selected, $options, $id=null){
    $selId = $id ?: $name;
    $html = '<div class="mp-select" data-select="'.$selId.'">';
    $html .= '<select name="'.$name.'" id="'.$selId.'" class="hidden-select">';
    foreach($options as $k => $label){
      $html .= '<option value="'.htmlspecialchars($k).'" '.($k == $selected ? 'selected' : '').'>'.htmlspecialchars($label).'</option>';
    }
    $html .= '</select>';
    $html .= '<div class="trigger"><span class="selected-text">'.htmlspecialchars($options[$selected] ?? reset($options)).'</span><i class="fa fa-chevron-down"></i></div>';
    $html .= '<div class="options">';
    foreach($options as $k => $label){
      $html .= '<div class="option '.($k == $selected ? 'selected' : '').'" data-value="'.htmlspecialchars($k).'">'.htmlspecialchars($label).'</div>';
    }
    $html .= '</div></div>';
    return $html;
  }
  ?>

  <script>
    function initMpSelect(select){
      const trigger = select.querySelector('.trigger');
      const options = select.querySelector('.options');
      const realSelect = select.querySelector('.hidden-select');
      const selectedText = select.querySelector('.selected-text');

      trigger.addEventListener('click', function(e){
        e.stopPropagation();
        document.querySelectorAll('.mp-select').forEach(function(other){ if(other !== select) other.classList.remove('open'); });
        select.classList.toggle('open');
      });

      options.querySelectorAll('.option').forEach(function(opt){
        opt.addEventListener('click', function(){
          realSelect.value = this.dataset.value;
          selectedText.textContent = this.textContent;
          options.querySelectorAll('.option').forEach(function(o){ o.classList.remove('selected'); });
          this.classList.add('selected');
          select.classList.remove('open');
        });
      });
    }

    document.querySelectorAll('.mp-select').forEach(initMpSelect);
    document.addEventListener('click', function(e){
      document.querySelectorAll('.mp-select').forEach(function(select){ if(!select.contains(e.target)) select.classList.remove('open'); });
    });

    function handleSave(form, successMsg){
      const btn = form.querySelector('.save-btn');
      if(btn) btn.disabled = true;
      const formData = new FormData(form);
      fetch(form.action, {method: 'POST', body: formData})
        .then(r => r.text())
        .then(d => {
          if(btn) btn.disabled = false;
          if(d.trim().indexOf('success') === 0 || d.trim() === 'success'){
            mpSuccess(successMsg);
          } else {
            mpError(d || 'Save failed.');
          }
        })
        .catch(() => { if(btn) btn.disabled = false; mpError('Save failed.'); });
      return false;
    }
  </script>
</body>
</html>
