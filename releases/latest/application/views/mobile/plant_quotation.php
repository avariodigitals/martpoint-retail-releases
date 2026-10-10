<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint', ENT_QUOTES, 'UTF-8') ?> — <?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?></title>
  <link rel="stylesheet" href="<?= htmlspecialchars($theme_link, ENT_QUOTES, 'UTF-8') ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    *{box-sizing:border-box}html,body{margin:0;min-height:100%;font-family:Inter,-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;background:#f2f6f3;color:#173128}
    .plant-mobile{max-width:600px;margin:auto;min-height:100vh;padding:0 0 125px;background:#f2f6f3}
    .plant-mobile-head{background:#fff;border-bottom:1px solid #e1e9e5;padding:16px;display:flex;align-items:center;gap:12px}
    .plant-mobile-head .back{font-size:19px;color:#176534;text-decoration:none}.plant-mobile-head .titles{flex:1;min-width:0}
    .plant-mobile-head .store-name{font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
    .plant-mobile-head h1{font-size:17px;margin:2px 0 0}.plant-mobile-head .avatar{width:34px;height:34px;border-radius:50%;background:#dcf5e5;color:#166534;display:grid;place-items:center;font-weight:800}
    .plant-mobile-status{display:flex;justify-content:space-between;padding:8px 17px;color:#64748b;font-size:11px}.plant-mobile-status .online{color:#16803d}
    .plant-mobile .pq-shell{padding:4px 12px 20px}.plant-mobile .pq-card{margin-bottom:14px}.plant-mobile .pq-grid{grid-template-columns:1fr}.plant-mobile .pq-actions{align-items:center}.plant-mobile .pq-actions>*{flex:1;text-align:center}.plant-mobile .pq-summary{gap:12px;justify-content:space-between}
  </style>
</head>
<body>
<div class="plant-mobile">
  <header class="plant-mobile-head">
    <a class="back" href="<?= base_url('mobile/quotations') ?>" aria-label="Back to quotations"><i class="fa fa-chevron-left"></i></a>
    <div class="titles"><div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint', ENT_QUOTES, 'UTF-8') ?></div><h1><?= htmlspecialchars($page_title, ENT_QUOTES, 'UTF-8') ?></h1></div>
    <div class="avatar" aria-label="Profile"><?= htmlspecialchars(strtoupper(mb_substr((string)($this->session->userdata('display_name') ?: $this->session->userdata('username') ?: 'U'), 0, 1)), ENT_QUOTES, 'UTF-8') ?></div>
  </header>
  <div class="plant-mobile-status"><span><i class="fa fa-clock-o"></i> <span id="plant-quote-clock"></span></span><span id="plant-quote-sync" class="online"><i class="fa fa-circle"></i> Online</span></div>
  <?php $this->load->view('quotation/plant_quotation', ['quotation_id' => $quotation_id ?? null, 'plant_mobile' => true]); ?>
</div>
<?php $this->load->view('mobile/bottom_nav', ['active' => 'sale']); ?>
<?php $this->load->view('mobile/chat'); ?>
<script>
(function(){var clock=document.getElementById('plant-quote-clock'),sync=document.getElementById('plant-quote-sync');function tick(){clock.textContent=new Date().toLocaleTimeString([], {hour:'2-digit',minute:'2-digit'});sync.innerHTML=navigator.onLine?'<i class="fa fa-circle"></i> Online':'<i class="fa fa-circle"></i> Offline';sync.className=navigator.onLine?'online':'';}tick();setInterval(tick,30000);window.addEventListener('online',tick);window.addEventListener('offline',tick);})();
</script>
</body>
</html>
