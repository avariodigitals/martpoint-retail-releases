<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Loyalty Settings</title>
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
    .field .hint { font-size: 12px; color: var(--mp-muted); }
    .field input[type="text"], .field input[type="number"], .field input[type="date"] { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 14px; background: #fff; font-family: inherit; }
    .mp-select { position: relative; }
    .mp-select .trigger { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; background: #fff; display: flex; align-items: center; justify-content: space-between; font-size: 14px; color: var(--mp-ink); }
    .mp-select .trigger i { color: var(--mp-muted); }
    .mp-select .options { display: none; border: 1px solid var(--mp-border); border-radius: 12px; background: #fff; max-height: 220px; overflow-y: auto; margin-top: 6px; z-index: 10; }
    .mp-select.open .options { display: block; }
    .mp-select .option { padding: 11px 14px; font-size: 14px; border-bottom: 1px solid var(--mp-border); cursor: pointer; }
    .mp-select .option:last-child { border-bottom: none; }
    .mp-select .option.selected { background: #EFF6FF; color: var(--mp-primary); font-weight: 600; }
    .mp-select .hidden-select { position: absolute; opacity: 0; pointer-events: none; }
    .save-btn { width: 100%; padding: 16px; border-radius: 14px; border: none; background: var(--mp-primary); color: #fff; font-size: 16px; font-weight: 700; cursor: pointer; margin-top: 8px; }
    .save-btn:disabled { opacity: 0.6; }
    .btn-link { display: inline-block; padding: 10px 14px; border-radius: 12px; text-decoration: none; font-size: 13px; font-weight: 600; background: var(--mp-bg); color: var(--mp-ink); }
    .tier-card { background: var(--mp-bg); border-radius: 14px; padding: 14px; display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
    .tier-card .name { font-weight: 700; font-size: 15px; }
    .tier-card .meta { font-size: 12px; color: var(--mp-muted); margin-top: 4px; }
    .tier-actions { display: flex; gap: 8px; margin-top: 8px; }
    .icon-btn { border: none; background: transparent; color: var(--mp-primary); font-size: 15px; cursor: pointer; padding: 4px; }
    .icon-btn.delete { color: var(--mp-danger); }
    .grid-2 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
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
          <h1>Loyalty Settings</h1>
        </div>
      </div>

      <form id="settingsForm" action="<?= base_url('loyalty/save_settings'); ?>" method="post" onsubmit="return handleSave(this, 'Settings saved.');">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <div class="section">
          <div class="section-head">Program Settings</div>
          <div class="section-body">
            <?php
              $earning = $settings->earning_type ?? 'spend_based';
              $partial = $settings->allow_partial_redemption ?? 1;
              $tiercalc = $settings->tier_calculation ?? 'lifetime_spend';
              $flexpay = $settings->flexpay_points_timing ?? 'full_payment';
              $enabled = $settings->loyalty_enabled ?? 0;
            ?>
            <div class="field">
              <label>Loyalty Enabled</label>
              <?= renderMpSelect('loyalty_enabled', $enabled ? '1' : '0', [
                '1' => 'Yes', '0' => 'No'
              ]); ?>
            </div>
            <div class="field">
              <label>Earning Type</label>
              <?= renderMpSelect('earning_type', $earning, [
                'spend_based' => 'Spend based', 'percentage_based' => 'Percentage based', 'product_specific' => 'Product specific', 'service_specific' => 'Service specific'
              ]); ?>
            </div>
            <div class="grid-2">
              <div class="field">
                <label>Spend Amount</label>
                <input type="number" step="0.01" name="spend_amount" value="<?= (float)($settings->spend_amount ?? 0); ?>">
              </div>
              <div class="field">
                <label>Points Earned</label>
                <input type="number" step="0.01" name="points_earned" value="<?= (float)($settings->points_earned ?? 0); ?>">
              </div>
              <div class="field">
                <label>Percentage Rate (%)</label>
                <input type="number" step="0.01" name="percentage_rate" value="<?= (float)($settings->percentage_rate ?? 0); ?>">
              </div>
              <div class="field">
                <label>Redemption Rate</label>
                <input type="number" step="0.01" name="redemption_rate" value="<?= (float)($settings->redemption_rate ?? 0); ?>">
              </div>
              <div class="field">
                <label>Min Redemption Points</label>
                <input type="number" name="minimum_redemption_points" value="<?= (int)($settings->minimum_redemption_points ?? 0); ?>">
              </div>
              <div class="field">
                <label>Max Redemption / Sale</label>
                <input type="number" step="0.01" name="maximum_redemption_per_sale" value="<?= (float)($settings->maximum_redemption_per_sale ?? 0); ?>">
              </div>
            </div>
            <div class="field">
              <label>Allow Partial Redemption</label>
              <?= renderMpSelect('allow_partial_redemption', $partial ? '1' : '0', [
                '1' => 'Yes', '0' => 'No'
              ]); ?>
            </div>
            <div class="field">
              <label>Tier Calculation</label>
              <?= renderMpSelect('tier_calculation', $tiercalc, [
                'lifetime_spend' => 'Lifetime spend', 'points' => 'Points balance'
              ]); ?>
            </div>
            <div class="field">
              <label>FlexPay Points Timing</label>
              <?= renderMpSelect('flexpay_points_timing', $flexpay, [
                'full_payment' => 'After full payment', 'immediately' => 'Immediately after deposit', 'disabled' => 'Disabled'
              ]); ?>
            </div>
            <button type="submit" class="save-btn" id="settingsSave"><i class="fa fa-save"></i> Save Settings</button>
          </div>
        </div>
      </form>

      <div class="section">
        <div class="section-head">Customer Tiers</div>
        <div class="section-body">
          <form id="tierForm" action="<?= base_url('loyalty/save_tier'); ?>" method="post" onsubmit="return handleSave(this, 'Tier saved.');">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
            <input type="hidden" name="tier_id" id="tier_id" value="">
            <div class="field">
              <label>Tier Name</label>
              <input type="text" name="tier_name" id="tier_name" required>
            </div>
            <div class="grid-2">
              <div class="field">
                <label>Minimum Spend</label>
                <input type="number" step="0.01" name="minimum_spend" id="minimum_spend" value="0">
              </div>
              <div class="field">
                <label>Minimum Points</label>
                <input type="number" name="minimum_points" id="minimum_points" value="0">
              </div>
              <div class="field">
                <label>Discount %</label>
                <input type="number" step="0.01" name="discount_percentage" id="discount_percentage" value="0">
              </div>
              <div class="field">
                <label>Bonus Points %</label>
                <input type="number" step="0.01" name="bonus_points_percentage" id="bonus_points_percentage" value="0">
              </div>
              <div class="field">
                <label>Birthday Reward Type</label>
                <?= renderMpSelect('birthday_reward_type', 'discount', [
                  'discount' => 'Discount %', 'voucher' => 'Voucher amount', 'points' => 'Bonus points', 'product' => 'Free product'
                ], 'brtSelect'); ?>
              </div>
              <div class="field">
                <label>Birthday Reward Value</label>
                <input type="number" step="0.01" name="birthday_reward_value" id="birthday_reward_value" value="0">
              </div>
              <div class="field">
                <label>Priority Service</label>
                <?= renderMpSelect('priority_service', '0', [
                  '1' => 'Yes', '0' => 'No'
                ], 'psSelect'); ?>
              </div>
              <div class="field">
                <label>Sort Order</label>
                <input type="number" name="sort_order" id="sort_order" value="0">
              </div>
            </div>
            <button type="submit" class="save-btn" id="tierSave"><i class="fa fa-save"></i> Save Tier</button>
            <button type="button" class="btn-link" style="width:100%; margin-top:10px; text-align:center;" onclick="resetTierForm()">Cancel / New Tier</button>
          </form>

          <div style="display:flex; flex-direction:column; gap:12px; margin-top:6px;">
            <?php if(!empty($tiers)): foreach($tiers as $t): ?>
              <div class="tier-card" id="tierRow<?= $t->id; ?>">
                <div>
                  <div class="name"><?= htmlspecialchars($t->tier_name); ?></div>
                  <div class="meta" title="<?= strip_tags(mp_format_money($t->minimum_spend ?? 0)); ?>">
                    Min spend <?= mp_format_money_compact($t->minimum_spend ?? 0); ?> &bull; Min points <?= number_format($t->minimum_points ?? 0); ?> &bull; Discount <?= (float)($t->discount_percentage); ?>%
                  </div>
                </div>
                <div class="tier-actions">
                  <button type="button" class="icon-btn" onclick='editTier(<?= json_encode($t); ?>)'><i class="fa fa-edit"></i></button>
                  <form method="post" action="<?= base_url('loyalty/delete_tier/' . $t->id); ?>" id="deleteTier<?= $t->id; ?>" style="display:inline;">
                    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
                    <button type="button" class="icon-btn delete" onclick="confirmDelete('deleteTier<?= $t->id; ?>', 'Delete this tier?')"><i class="fa fa-trash"></i></button>
                  </form>
                </div>
              </div>
            <?php endforeach; else: ?>
              <div style="text-align:center; color:var(--mp-muted); font-size:14px;">No tiers yet.</div>
            <?php endif; ?>
          </div>
        </div>
      </div>
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

    function setMpSelect(name, value){
      const select = document.querySelector('.mp-select[data-select="'+name+'"]');
      if(!select) return;
      const real = select.querySelector('.hidden-select');
      const text = select.querySelector('.selected-text');
      real.value = value;
      const opt = select.querySelector('.option[data-value="'+CSS.escape(value)+'"]') || select.querySelector('.option[data-value="'+value.replace(/"/g,'\\"')+'"]');
      if(opt){
        text.textContent = opt.textContent;
        select.querySelectorAll('.option').forEach(function(o){ o.classList.remove('selected'); });
        opt.classList.add('selected');
      }
    }

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

    function confirmDelete(formId, msg){
      const form = document.getElementById(formId);
      mpConfirm(msg, function(){
        const formData = new FormData(form);
        fetch(form.action, {method: 'POST', body: formData})
          .then(r => r.text())
          .then(d => {
            const res = d.trim();
            if(res === 'success' || res.indexOf('success') === 0){
              mpSuccess('Tier deleted.');
              const row = form.closest('.tier-card');
              if(row) row.remove();
            } else {
              mpError(res || 'Delete failed.');
            }
          })
          .catch(() => mpError('Delete failed.'));
      }, null, {title: 'Delete?', okText: 'Delete', danger: true});
    }

    function editTier(t){
      document.getElementById('tier_id').value = t.id || '';
      document.getElementById('tier_name').value = t.tier_name || '';
      document.getElementById('minimum_spend').value = t.minimum_spend || 0;
      document.getElementById('minimum_points').value = t.minimum_points || 0;
      document.getElementById('discount_percentage').value = t.discount_percentage || 0;
      document.getElementById('bonus_points_percentage').value = t.bonus_points_percentage || 0;
      document.getElementById('birthday_reward_value').value = t.birthday_reward_value || 0;
      document.getElementById('sort_order').value = t.sort_order || 0;
      setMpSelect('brtSelect', (t.birthday_reward_type || 'discount'));
      setMpSelect('psSelect', (t.priority_service ? '1' : '0'));
      document.getElementById('tier_name').focus();
    }

    function resetTierForm(){
      document.getElementById('tierForm').reset();
      document.getElementById('tier_id').value = '';
      setMpSelect('brtSelect', 'discount');
      setMpSelect('psSelect', '0');
    }
  </script>
</body>
</html>
