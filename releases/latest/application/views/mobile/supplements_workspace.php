<?php
$supplements_home = $this->uri->segment(1) === 'dashboard';
$supplements_more = !$supplements_home;
$CI =& get_instance();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <?php $this->load->view('comman/app_icons'); ?>
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — <?= htmlspecialchars($page_title); ?></title>
  <link rel="stylesheet" href="<?= base_url('theme/css/font-awesome-4.7.0/css/font-awesome.min.css'); ?>">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap">
  <link rel="stylesheet" href="<?= base_url('theme/dist/css/pharmacy_workspace.css?v='.filemtime(FCPATH.'theme/dist/css/pharmacy_workspace.css')); ?>">
  <link rel="stylesheet" href="<?= base_url('theme/dist/css/pharmacy_directory.css?v='.filemtime(FCPATH.'theme/dist/css/pharmacy_directory.css')); ?>">
</head>
<body class="pharmacy-workspace-shell pharmacy-mobile supplements-workspace-shell">
  <header class="pharma-mobile-head"><div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div><h1><?= htmlspecialchars($page_title); ?></h1></header>
  <nav class="pharma-mobile-nav" aria-label="Supplements and wellness workspace">
    <a href="<?= base_url('dashboard?screen=mobile'); ?>"<?= $supplements_home?' aria-current="page"':''; ?>>Overview</a>
    <a href="<?= base_url('mobile/pos'); ?>">Sell</a>
    <?php if ($CI->permissions('customers_view')): ?><a href="<?= base_url('customers?screen=mobile'); ?>">Members</a><?php endif; ?>
    <a href="<?= base_url('supplements_workspace/more?screen=mobile'); ?>"<?= $supplements_more?' aria-current="page"':''; ?>>More</a>
  </nav>
  <main><?= $content; ?></main>
  <?php $this->load->view('mobile/bottom_nav', ['active'=>$supplements_home?'home':'more']); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
