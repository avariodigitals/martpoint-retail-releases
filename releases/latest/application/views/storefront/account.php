<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <title>My Account | <?= htmlspecialchars($store->store_name ?? 'Store'); ?></title>
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
    .sf-welcome { font-size:20px; font-weight:800; margin-bottom:4px; }
    .sf-subtitle { font-size:14px; color:var(--gray); margin-bottom:24px; }
    .sf-card { background:var(--white); border-radius:var(--radius-sm); border:1px solid var(--border); padding:20px; margin-bottom:12px; display:flex; align-items:center; gap:16px; }
    .sf-card svg { width:24px; height:24px; color:var(--primary); flex-shrink:0; }
    .sf-card-content { flex:1; }
    .sf-card-title { font-size:15px; font-weight:700; margin-bottom:2px; }
    .sf-card-desc { font-size:13px; color:var(--gray); }
    .sf-card-arrow { font-size:20px; color:var(--gray); }
    .sf-link { display:block; }
    .sf-logout { display:block; text-align:center; padding:14px; border-radius:var(--radius-sm); border:1px solid var(--border); color:var(--danger); font-weight:700; font-size:14px; background:var(--white); margin-top:24px; }
    .sf-footer-note { text-align:center; font-size:13px; color:var(--gray); margin-top:24px; }
    .sf-order-list { margin-top:8px; }
    .sf-order-item { background:var(--white); border-radius:var(--radius-sm); border:1px solid var(--border); padding:14px; margin-bottom:10px; }
    .sf-order-top { display:flex; justify-content:space-between; align-items:center; margin-bottom:6px; }
    .sf-order-code { font-family:monospace; font-weight:800; font-size:14px; color:var(--primary); }
    .sf-order-total { font-weight:700; }
    .sf-order-date { font-size:13px; color:var(--gray); }
    .sf-badge { display:inline-block; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700; text-transform:uppercase; }
    .sf-badge-pending { background:#FEF3C7; color:#92400E; }
    .sf-badge-processing { background:#DBEAFE; color:#1E40AF; }
    .sf-badge-paid { background:#D1FAE5; color:#065F46; }
    .sf-empty { text-align:center; padding:40px 20px; color:var(--gray); }
  </style>
</head>
<body>

<div class="sf-header">
  <div class="sf-header-inner">
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>" class="sf-back">&#8592;</a>
    <div class="sf-header-title">My Account</div>
  </div>
</div>

<div class="sf-section">
  <div class="sf-welcome">Welcome, <?= htmlspecialchars($customer->customer_name ?? 'Customer'); ?>!</div>
  <div class="sf-subtitle">Manage your orders and account details.</div>

  <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/account/orders'); ?>" class="sf-link">
    <div class="sf-card">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
      <div class="sf-card-content">
        <div class="sf-card-title">My Orders</div>
        <div class="sf-card-desc">View all your past and current orders</div>
      </div>
      <div class="sf-card-arrow">&#8250;</div>
    </div>
  </a>

  <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/track'); ?>" class="sf-link">
    <div class="sf-card">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      <div class="sf-card-content">
        <div class="sf-card-title">Track Order</div>
        <div class="sf-card-desc">Check the status of a recent order</div>
      </div>
      <div class="sf-card-arrow">&#8250;</div>
    </div>
  </a>

  <div class="sf-card" style="cursor:default;">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
    <div class="sf-card-content">
      <div class="sf-card-title">My Details</div>
      <div class="sf-card-desc"><?= htmlspecialchars($customer->mobile ?? ''); ?><br><?= !empty($customer->email) ? htmlspecialchars($customer->email) : ''; ?></div>
    </div>
  </div>

  <?php if(!empty($orders)): ?>
  <div style="margin-top:24px;">
    <div style="font-size:15px; font-weight:700; margin-bottom:12px;">Recent Orders</div>
    <?php foreach($orders as $o): ?>
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/order_received/' . urlencode($o->order_code)); ?>" class="sf-link">
      <div class="sf-order-item">
        <div class="sf-order-top">
          <span class="sf-order-code">#<?= htmlspecialchars($o->order_code); ?></span>
          <span class="sf-order-total"><?= htmlspecialchars($settings->currency ?? '₦'); ?><?= number_format($o->grand_total, 2); ?></span>
        </div>
        <div class="sf-order-top" style="margin-bottom:0;">
          <span class="sf-order-date"><?= date('M j, Y', strtotime($o->created_at ?? 'now')); ?></span>
          <span class="sf-badge sf-badge-<?= in_array($o->order_status, ['pending','processing']) ? 'processing' : 'paid'; ?>"><?= ucfirst($o->order_status); ?></span>
        </div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="sf-empty">No orders found yet.</div>
  <?php endif; ?>

  <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/account/logout'); ?>" class="sf-logout">Log Out</a>

  <div class="sf-footer-note">
    &copy; <?= date('Y'); ?> <?= htmlspecialchars($store->store_name ?? 'Store'); ?>. All rights reserved.
  </div>
</div>

</body>
</html>
