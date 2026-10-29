<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
<?php $this->load->view('comman/app_icons'); ?>
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Expiry Settings</title>
  <link rel="stylesheet" href="<?= base_url('theme/css/font-awesome-4.7.0/css/font-awesome.min.css'); ?>">
  <style>
    *{box-sizing:border-box}body{margin:0;background:#f5f7fb;color:#172033;font:15px/1.5 -apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;padding-bottom:145px}
    .mp-exp-head{padding:18px 18px 8px;background:#fff;border-bottom:1px solid #e5e9f1}.mp-exp-head .store-name{font-size:12px;color:#63708a;font-weight:700;letter-spacing:.04em}.mp-exp-head h1{font-size:22px;margin:2px 0 0}
    main{max-width:720px;margin:0 auto;padding:16px}.card{background:#fff;border:1px solid #e3e8f0;border-radius:16px;padding:18px;margin-bottom:14px;box-shadow:0 2px 8px rgba(20,35,60,.04)}
    h2{font-size:17px;margin:0 0 12px}.hint{font-size:13px;color:#68758b;margin:4px 0 12px}.field{margin:12px 0}.field label{display:block;font-size:13px;font-weight:650;margin-bottom:6px}.field input[type=number],.field input[type=email]{width:100%;padding:12px;border:1px solid #d8deea;border-radius:10px;font:inherit;background:#fff}
    .check{display:flex;gap:10px;align-items:flex-start;font-weight:650}.check input{width:20px;height:20px;margin:1px 0 0;accent-color:#2563eb}.actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:18px}.btn{border:0;border-radius:10px;padding:12px 16px;font:inherit;font-weight:700;background:#155eef;color:#fff}.btn.secondary{background:#edf2fa;color:#23314b}.status{min-height:20px;font-size:13px;color:#176c42;margin-top:10px}
    .rotation-option{display:flex;gap:10px;align-items:flex-start;padding:12px;border:1px solid #dfe5ef;border-radius:12px;margin:8px 0;font-weight:600}.rotation-option input{margin:3px 0 0;accent-color:#2563eb}.rotation-option small{display:block;color:#68758b;font-weight:400;margin-top:3px}
  </style>
</head>
<body>
  <header class="mp-exp-head"><div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div><h1>Expiry Settings</h1></header>
  <main>
    <form id="expiry-settings-form" class="card">
      <h2>Expiry controls</h2>
      <p class="hint">Choose how the store warns about expiry and handles expired stock at checkout.</p>
      <div class="field"><label for="alert_before_days">Warn before expiry (days)</label><input id="alert_before_days" name="alert_before_days" type="number" min="1" max="365" value="<?= (int)($settings->alert_before_days ?? 30); ?>"></div>
      <label class="check"><input type="checkbox" name="stop_selling_expired" value="1" <?= !empty($settings->stop_selling_expired) ? 'checked' : ''; ?>><span>Stop selling expired items</span></label>
      <p class="hint">When enabled, expired items or scanned expired batches are blocked from POS sales.</p>
      <h2 style="margin-top:22px">Stock rotation</h2>
      <p class="hint">Choose one method. Pharmacy recommendation: FEFO. Automatic modes require correctly received batch stock; otherwise checkout will stop rather than guess.</p>
      <?php $rotationMethod = strtoupper($settings->rotation_method ?? 'MANUAL'); ?>
      <label class="rotation-option"><input type="radio" name="rotation_method" value="MANUAL" <?= $rotationMethod === 'MANUAL' ? 'checked' : ''; ?>><span>Manual<small>Leave batch selection and stock handling manual. Safest until existing batches are reconciled.</small></span></label>
      <label class="rotation-option"><input type="radio" name="rotation_method" value="FEFO" <?= $rotationMethod === 'FEFO' ? 'checked' : ''; ?>><span>FEFO — First Expiry, First Out<small>Uses the earliest expiry first; receipt date breaks ties. The “Stop selling expired items” switch decides whether expired stock is blocked.</small></span></label>
      <label class="rotation-option"><input type="radio" name="rotation_method" value="FIFO" <?= $rotationMethod === 'FIFO' ? 'checked' : ''; ?>><span>FIFO — First In, First Out<small>Uses the oldest received batch first. The “Stop selling expired items” switch decides whether expired stock is blocked.</small></span></label>
      <div class="field"><label for="alert_email">Alert email (optional)</label><input id="alert_email" name="alert_email" type="email" value="<?= htmlspecialchars((string)($settings->alert_email ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="manager@example.com"></div>
      <label class="check"><input type="checkbox" name="email_alerts_enabled" value="1" <?= !empty($settings->email_alerts_enabled) ? 'checked' : ''; ?>><span>Enable email alerts</span></label>
      <div class="actions"><button type="submit" class="btn">Save settings</button><a class="btn secondary" href="<?= base_url('mobile/more'); ?>">Back to More</a></div>
      <div id="expiry-status" class="status" role="status"></div>
    </form>
  </main>
  <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  <?php $this->load->view('mobile/chat'); ?>
  <script>
  (function(){var f=document.getElementById('expiry-settings-form'),s=document.getElementById('expiry-status');f.addEventListener('submit',function(e){e.preventDefault();var data=new FormData(f);if(!f.querySelector('[name="stop_selling_expired"]').checked)data.set('stop_selling_expired','0');if(!f.querySelector('[name="email_alerts_enabled"]').checked)data.set('email_alerts_enabled','0');fetch('<?= base_url('expiry_settings/save'); ?>',{method:'POST',body:data,credentials:'same-origin'}).then(function(r){return r.text()}).then(function(t){s.textContent=t.trim()==='success'?'Expiry settings saved.':'Could not save settings. Please try again.';}).catch(function(){s.textContent='Network error. Please retry.';});});})();
  </script>
</body>
</html>
