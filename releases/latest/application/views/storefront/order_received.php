<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <title>Order Received | <?= htmlspecialchars($store->store_name ?? 'Store'); ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root { --primary:#3B82F6; --primary-dark:#2563EB; --success:#10B981; --warning:#F59E0B; --danger:#EF4444; --dark:#0F172A; --gray:#64748B; --light-gray:#F1F5F9; --border:#E2E8F0; --white:#fff; --radius:12px; --radius-sm:8px; }
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Inter',sans-serif; background:#F8FAFC; color:var(--dark); -webkit-font-smoothing:antialiased; }
    a { text-decoration:none; color:inherit; }
    img { max-width:100%; display:block; }

    .sf-header { background:var(--white); border-bottom:1px solid var(--border); position:sticky; top:0; z-index:100; }
    .sf-header-inner { max-width:600px; margin:0 auto; padding:12px 16px; display:flex; align-items:center; gap:12px; }
    .sf-back { font-size:22px; color:var(--dark); }
    .sf-header-title { font-size:16px; font-weight:700; flex:1; }

    .sf-section { max-width:600px; margin:0 auto; padding:16px; }

    .sf-success-card { background:var(--white); border-radius:var(--radius); border:1px solid var(--border); padding:32px 20px; text-align:center; margin-bottom:16px; }
    .sf-success-icon { width:72px; height:72px; border-radius:50%; background:#D1FAE5; display:flex; align-items:center; justify-content:center; margin:0 auto 16px; }
    .sf-success-icon svg { width:36px; height:36px; color:var(--success); }
    .sf-success-title { font-size:22px; font-weight:800; color:#065F46; margin-bottom:6px; }
    .sf-success-msg { font-size:15px; color:var(--gray); line-height:1.5; margin-bottom:16px; }
    .sf-order-code { display:inline-block; background:#EFF6FF; color:var(--primary-dark); padding:8px 16px; border-radius:var(--radius-sm); font-size:14px; font-weight:700; font-family:monospace; }

    .sf-card { background:var(--white); border-radius:var(--radius-sm); border:1px solid var(--border); padding:16px; margin-bottom:12px; }
    .sf-card-title { font-size:14px; font-weight:700; margin-bottom:12px; color:var(--dark); }

    .sf-item { display:flex; gap:12px; padding:10px 0; border-bottom:1px solid var(--light-gray); }
    .sf-item:last-child { border-bottom:none; }
    .sf-item-img { width:48px; height:48px; border-radius:8px; object-fit:cover; background:var(--light-gray); flex-shrink:0; }
    .sf-item-info { flex:1; min-width:0; }
    .sf-item-name { font-size:14px; font-weight:600; margin-bottom:2px; }
    .sf-item-qty { font-size:12px; color:var(--gray); }
    .sf-item-price { font-size:14px; font-weight:700; color:var(--primary); white-space:nowrap; }

    .sf-row { display:flex; justify-content:space-between; font-size:14px; margin-bottom:8px; }
    .sf-row.total { font-size:18px; font-weight:700; border-top:1px solid var(--border); padding-top:10px; margin-top:6px; }
    .sf-row .label { color:var(--gray); }
    .sf-row .value { font-weight:600; }

    .sf-info-row { display:flex; justify-content:space-between; font-size:13px; padding:6px 0; border-bottom:1px solid var(--light-gray); }
    .sf-info-row:last-child { border-bottom:none; }
    .sf-info-row .label { color:var(--gray); }
    .sf-info-row .value { font-weight:600; text-align:right; max-width:60%; word-break:break-word; }

    .sf-actions { display:flex; gap:10px; margin-top:16px; }
    .sf-btn { flex:1; padding:14px; border-radius:var(--radius-sm); font-weight:700; border:none; cursor:pointer; font-size:15px; text-align:center; display:block; }
    .sf-btn-primary { background:var(--primary); color:#fff; }
    .sf-btn-secondary { background:var(--white); color:var(--dark); border:1px solid var(--border); }
    .sf-btn-whatsapp { background:#25D366; color:#fff; }

    .sf-badge { display:inline-block; padding:4px 10px; border-radius:20px; font-size:11px; font-weight:700; text-transform:uppercase; }
    .sf-badge-pending { background:#FEF3C7; color:#92400E; }
    .sf-badge-paid { background:#D1FAE5; color:#065F46; }
    .sf-badge-unpaid { background:#FEE2E2; color:#991B1B; }

    .sf-footer-note { text-align:center; font-size:12px; color:var(--gray); margin-top:20px; padding:16px; }
  </style>
</head>
<body>

<div class="sf-header">
  <div class="sf-header-inner">
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>" class="sf-back">&#8592;</a>
    <div class="sf-header-title">Order Received</div>
  </div>
</div>

<div class="sf-section">

  <!-- Success Card -->
  <div class="sf-success-card">
    <div class="sf-success-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="20 6 9 17 4 12"></polyline>
      </svg>
    </div>
    <div class="sf-success-title">Thank You, <?= htmlspecialchars($order->customer_name); ?>!</div>
    <div class="sf-success-msg">
      Your order has been received and is now being processed.<br>
      We've sent a confirmation to your email<?php if(!empty($order->customer_email)): ?> (<?= htmlspecialchars($order->customer_email); ?>)<?php endif; ?>.
    </div>
    <div class="sf-order-code">Order #<?= htmlspecialchars($order->order_code); ?></div>
  </div>

  <!-- Order Items -->
  <div class="sf-card">
    <div class="sf-card-title">Order Items (<?= count($items); ?>)</div>
    <?php
    $cur = $store_currency['symbol'] ?? '';
    $placement = $store_currency['placement'] ?? 'Left';
    foreach($items as $item):
      $lineTotal = number_format($item->total_price, 2);
      $unitPrice = number_format($item->unit_price, 2);
      if($placement === 'Left') {
        $lineTotalStr = $cur . $lineTotal;
        $unitPriceStr = $cur . $unitPrice;
      } else {
        $lineTotalStr = $lineTotal . ' ' . $cur;
        $unitPriceStr = $unitPrice . ' ' . $cur;
      }
    ?>
    <div class="sf-item">
      <?php if(!empty($item->item_image) && file_exists($item->item_image)): ?>
        <img src="<?= base_url($item->item_image); ?>" class="sf-item-img" alt="" loading="lazy">
      <?php else: ?>
        <div class="sf-item-img" style="display:flex;align-items:center;justify-content:center;color:#CBD5E1;font-size:18px;">&#128722;</div>
      <?php endif; ?>
      <div class="sf-item-info">
        <div class="sf-item-name"><?= htmlspecialchars($item->item_name); ?></div>
        <div class="sf-item-qty"><?= $item->qty; ?> x <?= $unitPriceStr; ?></div>
        <?php if(!empty($item->service_note)): ?>
          <div class="sf-item-qty" style="margin-top:2px;font-style:italic;"><?= htmlspecialchars($item->service_note); ?></div>
        <?php endif; ?>
      </div>
      <div class="sf-item-price"><?= $lineTotalStr; ?></div>
    </div>
    <?php endforeach; ?>

    <div style="margin-top:12px;">
      <div class="sf-row">
        <span class="label">Subtotal</span>
        <span class="value"><?= $placement === 'Left' ? $cur . number_format($order->subtotal, 2) : number_format($order->subtotal, 2) . ' ' . $cur; ?></span>
      </div>
      <div class="sf-row">
        <span class="label">Delivery Fee</span>
        <span class="value"><?= $order->delivery_fee > 0 ? ($placement === 'Left' ? $cur . number_format($order->delivery_fee, 2) : number_format($order->delivery_fee, 2) . ' ' . $cur) : 'Free'; ?></span>
      </div>
      <div class="sf-row total">
        <span class="label">Grand Total</span>
        <span class="value"><?= $placement === 'Left' ? $cur . number_format($order->grand_total, 2) : number_format($order->grand_total, 2) . ' ' . $cur; ?></span>
      </div>
    </div>
  </div>

  <!-- Order Details -->
  <div class="sf-card">
    <div class="sf-card-title">Order Details</div>
    <div class="sf-info-row">
      <span class="label">Payment Method</span>
      <span class="value"><?= ucfirst(str_replace('_', ' ', $order->payment_method)); ?></span>
    </div>
    <div class="sf-info-row">
      <span class="label">Payment Status</span>
      <span class="value">
        <span class="sf-badge sf-badge-<?= $order->payment_status == 'paid' ? 'paid' : 'unpaid'; ?>"><?= ucfirst($order->payment_status); ?></span>
      </span>
    </div>
    <div class="sf-info-row">
      <span class="label">Order Status</span>
      <span class="value">
        <span class="sf-badge sf-badge-pending"><?= ucfirst($order->order_status); ?></span>
      </span>
    </div>
    <?php if(!empty($order->shipping_method)): ?>
    <div class="sf-info-row">
      <span class="label">Shipping</span>
      <span class="value"><?= htmlspecialchars($order->shipping_method); ?></span>
    </div>
    <?php endif; ?>
    <?php if(!empty($order->service_date)): ?>
    <div class="sf-info-row">
      <span class="label">Service Date</span>
      <span class="value"><?= htmlspecialchars($order->service_date); ?></span>
    </div>
    <?php endif; ?>
    <?php if(!empty($order->service_time)): ?>
    <div class="sf-info-row">
      <span class="label">Service Time</span>
      <span class="value"><?= htmlspecialchars($order->service_time); ?></span>
    </div>
    <?php endif; ?>
  </div>

  <!-- Customer Info -->
  <div class="sf-card">
    <div class="sf-card-title">Customer Information</div>
    <div class="sf-info-row">
      <span class="label">Name</span>
      <span class="value"><?= htmlspecialchars($order->customer_name); ?></span>
    </div>
    <div class="sf-info-row">
      <span class="label">Phone</span>
      <span class="value"><?= htmlspecialchars($order->customer_phone); ?></span>
    </div>
    <?php if(!empty($order->customer_email)): ?>
    <div class="sf-info-row">
      <span class="label">Email</span>
      <span class="value"><?= htmlspecialchars($order->customer_email); ?></span>
    </div>
    <?php endif; ?>
    <?php if(!empty($order->customer_address)): ?>
    <div class="sf-info-row">
      <span class="label">Address</span>
      <span class="value"><?= htmlspecialchars($order->customer_address); ?></span>
    </div>
    <?php endif; ?>
  </div>

  <!-- Actions -->
  <div class="sf-actions">
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/track'); ?>" class="sf-btn sf-btn-secondary">Track Order</a>
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>" class="sf-btn sf-btn-primary">Continue Shopping</a>
  </div>

  <div class="sf-footer-note">
    A confirmation email has been sent<?php if(!empty($order->customer_email)): ?> to <?= htmlspecialchars($order->customer_email); ?><?php endif; ?>.<br>
    If you have any questions, please contact <?= htmlspecialchars($store->store_name ?? 'the store'); ?>.
  </div>

</div>

</body>
</html>
