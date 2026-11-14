<!DOCTYPE html><html lang="en"><head>
<meta charset="utf-8">
<?php $this->load->view('comman/app_icons'); ?>
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title><?= htmlspecialchars($SITE_TITLE??'MartPoint',ENT_QUOTES,'UTF-8'); ?> — <?= htmlspecialchars($page_title,ENT_QUOTES,'UTF-8'); ?></title>
<link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
<style>*{box-sizing:border-box}body{margin:0;background:#f1f5f9;color:#182b37;font:14px -apple-system,BlinkMacSystemFont,Arial,sans-serif}.history-mobile-main{padding:16px 12px 170px;max-width:1024px;margin:auto}.history-mobile-topbar{padding:12px 0}.store-name{font-size:12px;color:#526579}.history-mobile-topbar h1{font-size:22px;margin:6px 0}.history-mobile-main .history-card{padding:12px}.history-mobile-main .history-table{min-width:500px}@media print{.history-mobile-main{padding:0;max-width:none}.history-mobile-topbar{display:none}}</style></head><body>
<main class="history-mobile-main"><header class="history-mobile-topbar"><div class="store-name"><?= htmlspecialchars($SITE_TITLE??'MartPoint',ENT_QUOTES,'UTF-8'); ?></div><h1><?= htmlspecialchars($page_title,ENT_QUOTES,'UTF-8'); ?></h1></header>
<?php $this->load->view('customer_history/'.$history_view); ?>
</main>
<?php $this->load->view('mobile/bottom_nav',array('active'=>'more')); ?>
<?php $this->load->view('mobile/chat'); ?>
</body></html>
