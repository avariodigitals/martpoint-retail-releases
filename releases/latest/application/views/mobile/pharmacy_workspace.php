<?php
$pharmacy_home = $this->uri->segment(1) === 'dashboard';
$pharmacy_more = !$pharmacy_home;
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
  <?php $this->load->view('pharmacy/screen_mode', ['pharmacy_mobile' => true]); ?>
</head>
<body class="pharmacy-workspace-shell pharmacy-mobile">
  <header class="pharma-mobile-head">
    <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
    <h1><?= htmlspecialchars($page_title); ?></h1>
  </header>
  <nav class="pharma-mobile-nav" aria-label="Pharmacy workspace">
    <a href="<?= base_url('dashboard?screen=mobile'); ?>"<?= $pharmacy_home ? ' aria-current="page"' : ''; ?>>Overview</a>
    <a href="<?= base_url('mobile/pos'); ?>">POS</a>
    <a href="<?= base_url('operations/medical_notes'); ?>">Prescriptions</a>
    <a href="<?= base_url('pharmacy_workspace/more?screen=mobile'); ?>"<?= $pharmacy_more ? ' aria-current="page"' : ''; ?>>More</a>
  </nav>
  <main><?= $content; ?></main>
  <?php $this->load->view('mobile/bottom_nav', ['active' => $pharmacy_home ? 'home' : 'more']); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
