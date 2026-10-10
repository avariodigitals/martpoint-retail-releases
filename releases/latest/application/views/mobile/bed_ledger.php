<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?> — Bed accounts</title>
<style>:root{--mp-primary:#176753;--mp-surface:#fff;--mp-text:#1e2d28;--mp-muted:#687a72;--mp-border:#d8e2dc}body{margin:0;background:#eef3f0;color:var(--mp-text);font-family:Arial,sans-serif}.screen{padding:18px 14px 110px}.store-name{font-size:12px;color:var(--mp-muted)}h1{font-size:22px}.screen h2{display:none}a{color:var(--mp-primary)}</style></head><body>
<main class="screen"><header><div class="store-name"><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?></div><h1>Bed accounts</h1><a href="<?= base_url('mobile/more'); ?>">Back to menu</a></header><?= $content; ?></main>
<?php $this->load->view('mobile/bottom_nav',array('active'=>'more')); ?>
<?php $this->load->view('mobile/chat'); ?>
</body></html>
