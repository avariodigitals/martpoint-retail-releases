<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <title>Order Confirmed | <?= htmlspecialchars($store->store_name ?? 'Store'); ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <style>
    :root { --primary:#3B82F6; --primary-dark:#2563EB; --success:#059669; --success-light:#D1FAE5; --warning:#F59E0B; --danger:#EF4444; --dark:#0F172A; --gray:#64748B; --light-gray:#F1F5F9; --border:#E2E8F0; --white:#fff; --radius:16px; --radius-sm:10px; }
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Inter',sans-serif; background:#F8FAFC; color:var(--dark); -webkit-font-smoothing:antialiased; }
    a { text-decoration:none; color:inherit; }
    img { max-width:100%; display:block; }

    .sf-header { background:var(--white); border-bottom:1px solid var(--border); position:sticky; top:0; z-index:100; }
    .sf-header-inner { max-width:640px; margin:0 auto; padding:14px 16px; display:flex; align-items:center; gap:12px; }
    .sf-back { font-size:22px; color:var(--dark); display:flex; align-items:center; }
    .sf-header-title { font-size:16px; font-weight:700; flex:1; }
    .sf-header-logo { font-weight:800; font-size:15px; color:var(--dark); }

    .sf-section { max-width:640px; margin:0 auto; padding:24px 16px 48px; }

    /* Success hero */
    .sf-success-hero { background:var(--white); border-radius:var(--radius); border:1px solid var(--border); padding:48px 28px; text-align:center; margin-bottom:16px; position:relative; overflow:hidden; }
    .sf-success-hero::before { content:''; position:absolute; top:0; left:0; right:0; height:4px; background:linear-gradient(90deg, var(--success), #34D399); }
    .sf-success-icon { width:80px; height:80px; border-radius:50%; background:var(--success-light); display:flex; align-items:center; justify-content:center; margin:0 auto 20px; animation:sfPop .5s ease .15s both; }
    @keyframes sfPop { 0%{transform:scale(0);} 60%{transform:scale(1.15);} 100%{transform:scale(1);} }
    .sf-success-icon svg { width:40px; height:40px; color:var(--success); }
    .sf-success-title { font-size:24px; font-weight:900; color:var(--dark); margin-bottom:8px; }
    .sf-success-msg { font-size:15px; color:var(--gray); line-height:1.6; margin-bottom:20px; max-width:400px; margin-left:auto; margin-right:auto; }
    .sf-order-code { display:inline-block; background:#EFF6FF; color:var(--primary-dark); padding:10px 24px; border-radius:var(--radius-sm); font-size:16px; font-weight:800; font-family:monospace; letter-spacing:0.02em; }

    /* Cards */
    .sf-card { background:var(--white); border-radius:var(--radius-sm); border:1px solid var(--border); padding:20px; margin-bottom:12px; }
    .sf-card-title { font-size:15px; font-weight:700; margin-bottom:16px; color:var(--dark); display:flex; align-items:center; gap:8px; }
    .sf-card-title svg { width:18px; height:18px; color:var(--gray); }

    /* Items */
    .sf-item { display:flex; gap:12px; padding:12px 0; border-bottom:1px solid var(--light-gray); }
    .sf-item:last-child { border-bottom:none; }
    .sf-item-img { width:52px; height:52px; border-radius:10px; object-fit:cover; background:var(--light-gray); flex-shrink:0; overflow:hidden; }
    .sf-item-info { flex:1; min-width:0; }
    .sf-item-name { font-size:14px; font-weight:600; margin-bottom:3px; color:var(--dark); }
    .sf-item-qty { font-size:12px; color:var(--gray); }
    .sf-item-price { font-size:14px; font-weight:700; color:var(--dark); white-space:nowrap; }

    /* Totals */
    .sf-totals { margin-top:12px; }
    .sf-row { display:flex; justify-content:space-between; font-size:14px; margin-bottom:8px; }
    .sf-row.total { font-size:18px; font-weight:800; border-top:1px solid var(--border); padding-top:12px; margin-top:8px; color:var(--dark); }
    .sf-row .label { color:var(--gray); }
    .sf-row .value { font-weight:600; color:var(--dark); }
    .sf-row.total .value { color:var(--primary); }

    /* Info rows */
    .sf-info-row { display:flex; justify-content:space-between; font-size:13px; padding:8px 0; border-bottom:1px solid var(--light-gray); }
    .sf-info-row:last-child { border-bottom:none; }
    .sf-info-row .label { color:var(--gray); }
    .sf-info-row .value { font-weight:600; text-align:right; max-width:60%; word-break:break-word; color:var(--dark); }

    /* Badges */
    .sf-badge { display:inline-block; padding:4px 12px; border-radius:20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.04em; }
    .sf-badge-pending { background:#FEF3C7; color:#92400E; }
    .sf-badge-paid { background:#D1FAE5; color:#065F46; }
    .sf-badge-unpaid { background:#FEE2E2; color:#991B1B; }
    .sf-badge-processing { background:#DBEAFE; color:#1E40AF; }

    /* Actions */
    .sf-actions { display:flex; gap:10px; margin-top:20px; }
    .sf-btn { flex:1; padding:15px; border-radius:var(--radius-sm); font-weight:700; border:none; cursor:pointer; font-size:15px; text-align:center; display:flex; align-items:center; justify-content:center; gap:8px; transition:transform .15s, background .2s; }
    .sf-btn:active { transform:scale(0.98); }
    .sf-btn-primary { background:var(--primary); color:#fff; }
    .sf-btn-primary:hover { background:var(--primary-dark); }
    .sf-btn-secondary { background:var(--white); color:var(--dark); border:1px solid var(--border); }
    .sf-btn-secondary:hover { background:var(--light-gray); }
    .sf-btn-whatsapp { background:#25D366; color:#fff; }
    .sf-btn-whatsapp:hover { background:#1DA851; }
    .sf-btn svg { width:18px; height:18px; }

    /* Footer */
    .sf-footer-note { text-align:center; font-size:13px; color:var(--gray); margin-top:24px; padding:20px; line-height:1.6; }
    .sf-footer-powered { text-align:center; font-size:12px; color:#94A3B8; margin-top:8px; }
    .sf-footer-powered a { color:#64748B; font-weight:600; }

    /* WhatsApp note */
    .sf-wa-note { margin-top:16px; padding:14px; background:#F0FDF4; border:1px solid #BBF7D0; border-radius:var(--radius-sm); font-size:13px; color:#166534; display:flex; align-items:center; gap:10px; }
    .sf-wa-note svg { width:20px; height:20px; flex-shrink:0; }
  </style>
</head>
<body>

<div class="sf-header">
  <div class="sf-header-inner">
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>" class="sf-back">
      <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"></polyline></svg>
    </a>
    <div class="sf-header-title">Order Confirmed</div>
    <?php if(!empty($logo_url)): ?>
    <img src="<?= $logo_url; ?>" style="max-height:28px;max-width:100px;object-fit:contain;" alt="<?= htmlspecialchars($store->store_name ?? 'Store'); ?>">
    <?php else: ?>
    <div class="sf-header-logo"><?= htmlspecialchars($store->store_name ?? 'Store'); ?></div>
    <?php endif; ?>
  </div>
</div>

<div class="sf-section">

  <!-- Success Hero -->
  <div class="sf-success-hero">
    <div class="sf-success-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
    </div>
    <div class="sf-success-title">Thank You, <?= htmlspecialchars($order->customer_name); ?>!</div>
    <div class="sf-success-msg">
      Your order has been received and is now being processed.<br>
      <?php if(!empty($order->customer_email)): ?>A confirmation has been sent to <?= htmlspecialchars($order->customer_email); ?>.<br><?php endif; ?>
      We'll contact you at <?= htmlspecialchars($order->customer_phone); ?> with updates.
    </div>
    <div class="sf-order-code">Order #<?= htmlspecialchars($order->order_code); ?></div>

    <div class="sf-actions" style="margin-top:24px; justify-content:center;">
      <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/track?code=' . urlencode($order->order_code)); ?>" class="sf-btn sf-btn-secondary" style="max-width:160px;">
        Track Order
      </a>
      <?php
        $accountUrl = base_url('store/' . ($settings->store_slug ?? '') . '/account');
        if(!empty($order->customer_phone) && !isset($_COOKIE['customer_token'])){
          $accountUrl = base_url('store/' . ($settings->store_slug ?? '') . '/verify?phone=' . urlencode(preg_replace('/[^0-9]/', '', $order->customer_phone)));
        }
      ?>
      <a href="<?= $accountUrl; ?>" class="sf-btn sf-btn-primary" style="max-width:160px;">
        View My Account
      </a>
    </div>

    <?php if($order->payment_method === 'whatsapp'): ?>
    <div class="sf-wa-note">
      <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg>
      <span>Your order details have been sent to the store on WhatsApp. The store will confirm your order shortly.</span>
    </div>
    <?php endif; ?>
  </div>

  <!-- Order Items -->
  <div class="sf-card">
    <div class="sf-card-title">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
      Order Items (<?= count($items); ?>)
    </div>
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

    <div class="sf-totals">
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
    <div class="sf-card-title">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
      Order Details
    </div>
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
        <span class="sf-badge sf-badge-processing"><?= ucfirst($order->order_status); ?></span>
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
    <div class="sf-card-title">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
      Customer Information
    </div>
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
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/track'); ?>" class="sf-btn sf-btn-secondary">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
      Track Order
    </a>
    <?php if(!empty($settings->whatsapp_number)): ?>
    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number); ?>?text=<?= urlencode('Hello, I just placed order #' . $order->order_code . ' and would like to follow up.'); ?>" target="_blank" class="sf-btn sf-btn-whatsapp">
      <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg>
      Contact Store
    </a>
    <?php endif; ?>
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>" class="sf-btn sf-btn-primary">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/></svg>
      Continue Shopping
    </a>
  </div>

  <div class="sf-footer-note">
    A confirmation has been sent<?php if(!empty($order->customer_email)): ?> to <?= htmlspecialchars($order->customer_email); ?><?php endif; ?>.<br>
    If you have any questions, please contact <?= htmlspecialchars($store->store_name ?? 'the store'); ?>.
  </div>
  <div class="sf-footer-powered">
    &copy; <?= date('Y'); ?> <?= htmlspecialchars($store->store_name ?? 'Store'); ?>. All rights reserved. Business operations powered by MartPoint.
  </div>

</div>

</body>
</html>
