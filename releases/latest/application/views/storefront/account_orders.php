<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <title>My Orders | <?= htmlspecialchars($store->store_name ?? 'Store'); ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root { --primary:#3B82F6; --primary-dark:#2563EB; --success:#059669; --danger:#EF4444; --dark:#0F172A; --gray:#64748B; --light-gray:#F1F5F9; --border:#E2E8F0; --white:#fff; --radius:16px; --radius-sm:10px; }
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Inter',sans-serif; background:#F8FAFC; color:var(--dark); -webkit-font-smoothing:antialiased; }
    a { text-decoration:none; color:inherit; }
    .sf-header { background:var(--white); border-bottom:1px solid var(--border); position:sticky; top:0; z-index:100; }
    .sf-header-inner { max-width:640px; margin:0 auto; padding:14px 16px; display:flex; align-items:center; gap:12px; }
    .sf-back { font-size:22px; color:var(--dark); display:flex; align-items:center; }
    .sf-header-title { font-size:16px; font-weight:700; flex:1; }
    .sf-section { max-width:640px; margin:0 auto; padding:24px 16px; }
    .sf-title { font-size:22px; font-weight:800; margin-bottom:16px; }
    .sf-table { width:100%; border-collapse:collapse; background:var(--white); border-radius:var(--radius-sm); border:1px solid var(--border); overflow:hidden; }
    .sf-table th, .sf-table td { padding:14px; text-align:left; font-size:13px; border-bottom:1px solid var(--light-gray); }
    .sf-table th { background:#F8FAFC; font-weight:700; color:var(--gray); }
    .sf-table tr:last-child td { border-bottom:none; }
    .sf-code { font-family:monospace; font-weight:700; color:var(--primary); }
    .sf-link { color:var(--primary); font-weight:600; }
    .sf-badge { display:inline-block; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700; text-transform:uppercase; }
    .sf-badge-pending { background:#FEF3C7; color:#92400E; }
    .sf-badge-processing { background:#DBEAFE; color:#1E40AF; }
    .sf-badge-paid { background:#D1FAE5; color:#065F46; }
    .sf-empty { text-align:center; padding:60px 16px; color:var(--gray); background:var(--white); border-radius:var(--radius-sm); border:1px solid var(--border); }
    .sf-footer-note { text-align:center; font-size:13px; color:var(--gray); margin-top:24px; }
  </style>
</head>
<body>

<div class="sf-header">
  <div class="sf-header-inner">
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/account'); ?>" class="sf-back">&#8592;</a>
    <div class="sf-header-title">My Orders</div>
  </div>
</div>

<div class="sf-section">
  <div class="sf-title">Order History</div>

  <?php if(!empty($orders)): ?>
  <div style="overflow-x:auto;">
    <table class="sf-table">
      <thead>
        <tr>
          <th>Order #</th>
          <th>Date</th>
          <th>Status</th>
          <th>Total</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($orders as $o): ?>
        <tr>
          <td class="sf-code"><?= htmlspecialchars($o->order_code); ?></td>
          <td><?= date('M j, Y', strtotime($o->created_at ?? 'now')); ?></td>
          <td><span class="sf-badge sf-badge-<?= in_array($o->order_status, ['pending','processing']) ? 'processing' : 'paid'; ?>"><?= ucfirst($o->order_status); ?></span></td>
          <td><?= htmlspecialchars($settings->currency ?? '₦'); ?><?= number_format($o->grand_total, 2); ?></td>
          <td><a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/order_received/' . urlencode($o->order_code)); ?>" class="sf-link">View</a></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <?php else: ?>
  <div class="sf-empty">You haven't placed any orders yet.</div>
  <?php endif; ?>

  <div class="sf-footer-note">
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/account'); ?>">Back to Account</a>
  </div>
</div>

</body>
</html>
