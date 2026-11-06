<?php
defined('BASEPATH') OR exit('No direct script access allowed');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <?php $this->load->view('comman/app_icons'); ?>
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — <?= htmlspecialchars($page_title ?? 'Batch stock'); ?></title>
  <link rel="stylesheet" href="<?= base_url('theme/css/font-awesome-4.7.0/css/font-awesome.min.css'); ?>">
  <link rel="stylesheet" href="<?= base_url('theme/dist/css/pharmacy_directory.css?v='.filemtime(FCPATH.'theme/dist/css/pharmacy_directory.css')); ?>">
  <style>
    body{margin:0;background:#f6f8fb;color:#182230;font-family:Arial,sans-serif}
    .batch-mobile-head{padding:14px 18px;background:#fff;border-bottom:1px solid #dce3ec}
    .batch-mobile-head .store-name{font-size:12px;color:#66758a;font-weight:700}
    .batch-mobile-head h1{font-size:20px;margin:4px 0 0}
    .batch-mobile-main{padding:12px 10px 24px}
  </style>
</head>
<body>
  <header class="batch-mobile-head"><div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div><h1><?= htmlspecialchars($page_title ?? 'Batch stock'); ?></h1></header>
  <main class="batch-mobile-main"><?= $content; ?></main>
  <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
