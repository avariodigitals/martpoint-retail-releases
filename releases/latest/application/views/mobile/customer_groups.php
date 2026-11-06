<!doctype html>
<html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Customer Pricing Groups</title>
<?php $this->load->view('comman/app_icons'); ?>
<link rel="stylesheet" href="<?= base_url('theme/css/font-awesome-4.7.0/css/font-awesome.min.css'); ?>">
<style>*{box-sizing:border-box}body{margin:0;background:#f5f7fb;color:#172033;font:15px/1.5 -apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;padding-bottom:150px}header{padding:18px;background:#fff;border-bottom:1px solid #e2e8f0}.store-name{font-size:13px;color:#64748b}header h1{font-size:21px;margin:3px 0}main{padding:16px}a{color:#155eef}</style>
</head><body>
<header><div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div><h1>Customer Pricing Groups</h1><a href="<?= base_url('mobile/customers'); ?>">Back to customers</a></header>
<main><?php $this->load->view('customers/groups_content', get_defined_vars()); ?></main>
<?php $this->load->view('mobile/bottom_nav', ['active'=>'more']); ?>
<?php $this->load->view('mobile/chat'); ?>
</body></html>
