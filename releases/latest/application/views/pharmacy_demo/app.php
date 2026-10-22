<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?= htmlspecialchars($page_title ?? 'Nevari Health pharmacy demo'); ?></title>
  <meta name="description" content="A simulated Nigerian pharmacy and partner fulfilment experience for product and workflow testing.">
  <meta name="robots" content="noindex,nofollow">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Sora:wght@500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('theme/pharmacy_demo/style.css?v=4'); ?>">
</head>
<body data-demo-mode="<?= htmlspecialchars($mode ?? 'customer'); ?>">
  <div id="app"></div>
  <script>window.PHARMACY_DEMO_MODE = <?= json_encode($mode ?? 'customer'); ?>; window.PHARMACY_DEMO_CONFIG = <?= json_encode(array_merge(['partnerMarketplaceEnabled' => !empty($partner_marketplace_enabled), 'basePath' => $pharmacy_base_path ?? '/pharmacy-demo'], $pharmacy_config ?? [])); ?>;</script>
  <?php if (!empty($pharmacy_config['storeId'])): ?><script src="https://js.paystack.co/v1/inline.js"></script><?php endif; ?>
  <script src="<?= base_url('theme/pharmacy_demo/demo_service.js?v=6'); ?>"></script>
  <script src="<?= base_url('theme/pharmacy_demo/app.js?v=8'); ?>"></script>
</body>
</html>
