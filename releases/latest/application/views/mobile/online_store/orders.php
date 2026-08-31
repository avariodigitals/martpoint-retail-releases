<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Orders</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-ink: #1E293B; --mp-muted: #64748B; --mp-border: #E2E8F0; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: var(--mp-bg); }
    .topbar .back:active { background: #E2E8F0; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .topbar-titles { flex: 1; min-width: 0; }
    .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .status-pills { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 12px; -ms-overflow-style: none; scrollbar-width: none; }
    .status-pills::-webkit-scrollbar { display: none; }
    .pill { flex-shrink: 0; padding: 8px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; background: #fff; border: 1px solid var(--mp-border); color: var(--mp-muted); text-decoration: none; }
    .pill.active { background: var(--mp-primary); color: #fff; border-color: var(--mp-primary); }
    .order-card { background: #fff; border-radius: 16px; border: 1px solid var(--mp-border); overflow: hidden; box-shadow: 0 1px 3px rgba(15,23,42,0.04); margin-bottom: 12px; text-decoration: none; color: var(--mp-ink); display: block; }
    .order-card:active { background: #F8FAFC; }
    .order-header { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; border-bottom: 1px solid var(--mp-border); }
    .order-code { font-size: 15px; font-weight: 700; }
    .order-status { font-size: 11px; font-weight: 700; padding: 4px 10px; border-radius: 20px; text-transform: capitalize; }
    .order-body { padding: 14px 16px; }
    .order-row { display: flex; justify-content: space-between; margin-bottom: 8px; font-size: 14px; }
    .order-row:last-child { margin-bottom: 0; }
    .order-label { color: var(--mp-muted); }
    .order-value { font-weight: 600; }
    .payment-paid { background: #D1FAE5; color: #065F46; }
    .payment-unpaid { background: #FEF3C7; color: #92400E; }
    .payment-partially_paid { background: #DBEAFE; color: #1E40AF; }
    .payment-failed { background: #FEF2F2; color: #991B1B; }
    .payment-refunded { background: #F1F5F9; color: #475569; }
    .empty { text-align: center; padding: 50px 24px; color: var(--mp-muted); font-size: 14px; }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 120px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= $back_url; ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Orders</h1>
        </div>
      </div>

      <div class="status-pills">
        <?php $statuses = ['' => 'All', 'pending' => 'Pending', 'paid' => 'Paid', 'processing' => 'Processing', 'ready' => 'Ready', 'completed' => 'Completed', 'cancelled' => 'Cancelled']; ?>
        <?php foreach($statuses as $key => $label): ?>
          <a href="<?= base_url('mobile/online_store/orders' . ($key ? '?status=' . $key : '')); ?>" class="pill <?= ($current_status === $key || ($key === '' && !$current_status)) ? 'active' : ''; ?>"><?= $label; ?></a>
        <?php endforeach; ?>
      </div>

      <?php if(!empty($orders)): ?>
        <?php
          $statusClass = [
            'pending' => 'payment-unpaid', 'paid' => 'payment-paid', 'processing' => 'payment-partially_paid',
            'ready' => 'payment-partially_paid', 'completed' => 'payment-paid', 'cancelled' => 'payment-failed'
          ];
          $paymentClass = [
            'unpaid' => 'payment-unpaid', 'paid' => 'payment-paid', 'partially_paid' => 'payment-partially_paid',
            'failed' => 'payment-failed', 'refunded' => 'payment-refunded'
          ];
        ?>
        <?php foreach($orders as $o): ?>
          <a href="<?= base_url('mobile/online_store/order/' . $o->id); ?>" class="order-card">
            <div class="order-header">
              <span class="order-code">#<?= $o->order_code; ?></span>
              <span class="order-status <?= $statusClass[$o->order_status] ?? 'payment-unpaid'; ?>"><?= $o->order_status; ?></span>
            </div>
            <div class="order-body">
              <div class="order-row">
                <span class="order-label">Customer</span>
                <span class="order-value"><?= htmlspecialchars($o->customer_name); ?> · <?= htmlspecialchars($o->customer_phone); ?></span>
              </div>
              <div class="order-row">
                <span class="order-label">Date</span>
                <span class="order-value"><?= show_date($o->created_at); ?> <?= date('H:i', strtotime($o->created_at)); ?></span>
              </div>
              <div class="order-row">
                <span class="order-label">Type</span>
                <span class="order-value"><?= ucfirst($o->order_type); ?></span>
              </div>
              <div class="order-row">
                <span class="order-label">Total</span>
                <span class="order-value"><?= store_number_format($o->grand_total); ?></span>
              </div>
              <div class="order-row">
                <span class="order-label">Payment</span>
                <span class="order-status <?= $paymentClass[$o->payment_status] ?? 'payment-unpaid'; ?>"><?= ucfirst(str_replace('_',' ',$o->payment_status)); ?></span>
              </div>
            </div>
          </a>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty">No orders found for this status.</div>
      <?php endif; ?>
    </section>
    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
