<?php
// App-owned assets live outside uploads/ so fleet updates can replace them.
// Use PNG for iOS touch icons and versioned URLs to avoid the old letter tile.
?>
<link rel="icon" type="image/png" sizes="192x192" href="<?= base_url('theme/images/app/martpoint-192-v1.png'); ?>">
<link rel="apple-touch-icon" sizes="180x180" href="<?= base_url('theme/images/app/martpoint-180-v1.png'); ?>">
<link rel="manifest" href="<?= base_url('manifest.json?v=martpoint-icon-v1'); ?>">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="MartPoint">
