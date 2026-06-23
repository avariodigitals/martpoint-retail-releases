<!DOCTYPE html>
<html>
<head>
  <title>Paystack Payment</title>
  <?php include"comman/code_css.php"; ?>
  <style>
    .status-box { max-width: 500px; margin: 60px auto; text-align: center; padding: 40px; border-radius: 8px; }
    .success-box { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
    .error-box { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    .status-icon { font-size: 64px; margin-bottom: 20px; }
  </style>
</head>
<body class="hold-transition skin-blue">
<div class="wrapper">
  <div class="content-wrapper" style="margin-left:0;">
    <section class="content">
      <div class="status-box <?= (strpos($message, 'successful') !== false) ? 'success-box' : 'error-box'; ?>">
        <div class="status-icon">
          <i class="fa <?= (strpos($message, 'successful') !== false) ? 'fa-check-circle text-success' : 'fa-times-circle text-danger'; ?>"></i>
        </div>
        <h2><?= htmlspecialchars($message); ?></h2>
        <p class="text-muted">Reference: <strong><?= htmlspecialchars($reference); ?></strong></p>
        <br>
        <a href="<?= base_url('dashboard'); ?>" class="btn btn-primary">Back to Dashboard</a>
      </div>
    </section>
  </div>
</div>
</body>
</html>
