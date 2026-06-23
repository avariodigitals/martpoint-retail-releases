<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <title>Payment <?= $success ? 'Successful' : 'Failed'; ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Inter',sans-serif; background:#F8FAFC; display:flex; align-items:center; justify-content:center; min-height:100vh; padding:20px; }
    .card { background:#fff; border-radius:16px; padding:40px 24px; text-align:center; max-width:400px; width:100%; box-shadow:0 4px 24px rgba(0,0,0,0.08); }
    .icon { font-size:64px; margin-bottom:16px; }
    .title { font-size:22px; font-weight:800; margin-bottom:8px; }
    .msg { font-size:15px; color:#64748B; margin-bottom:24px; line-height:1.5; }
    .ref { background:#F1F5F9; padding:10px 16px; border-radius:8px; font-size:13px; font-family:monospace; color:#0F172A; margin-bottom:24px; word-break:break-all; }
    .btn { display:inline-block; padding:12px 32px; border-radius:8px; font-size:15px; font-weight:700; text-decoration:none; }
    .btn-primary { background:#3B82F6; color:#fff; }
    .success .icon { color:#10B981; }
    .success .title { color:#065F46; }
    .fail .icon { color:#EF4444; }
    .fail .title { color:#991B1B; }
  </style>
</head>
<body>
  <div class="card <?= $success ? 'success' : 'fail'; ?>">
    <div class="icon"><?= $success ? '&#9989;' : '&#10060;'; ?></div>
    <div class="title"><?= $success ? 'Payment Successful!' : 'Payment Failed'; ?></div>
    <div class="msg"><?= htmlspecialchars($message); ?></div>
    <?php if($reference): ?>
    <div class="ref">Reference: <?= htmlspecialchars($reference); ?></div>
    <?php endif; ?>
    <a href="<?= base_url(); ?>" class="btn btn-primary">Back to Store</a>
  </div>
</body>
</html>
