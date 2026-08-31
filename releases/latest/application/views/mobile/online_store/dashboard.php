<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Store Dashboard</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-ink: #1E293B; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: var(--mp-bg); }
    .topbar .back:active { background: #E2E8F0; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .topbar-titles { flex: 1; min-width: 0; }
    .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .kpi-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; margin-bottom: 20px; }
    .kpi-card { background: #fff; border-radius: 16px; padding: 16px; border: 1px solid var(--mp-border); box-shadow: 0 1px 3px rgba(15,23,42,0.04); }
    .kpi-label { font-size: 12px; color: var(--mp-muted); font-weight: 500; margin-bottom: 6px; }
    .kpi-value { font-size: 22px; font-weight: 700; color: var(--mp-ink); }
    .section { background: #fff; border-radius: 16px; border: 1px solid var(--mp-border); overflow: hidden; box-shadow: 0 1px 3px rgba(15,23,42,0.04); margin-bottom: 16px; }
    .section-header { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; border-bottom: 1px solid var(--mp-border); }
    .section-title { font-size: 15px; font-weight: 700; margin: 0; }
    .section-link { font-size: 13px; color: var(--mp-primary); font-weight: 600; text-decoration: none; }
    .list-item { display: flex; align-items: center; gap: 12px; padding: 14px 16px; border-bottom: 1px solid var(--mp-border); text-decoration: none; color: var(--mp-ink); }
    .list-item:last-child { border-bottom: none; }
    .list-item:active { background: #F8FAFC; }
    .item-rank { width: 28px; height: 28px; border-radius: 50%; background: #F1F5F9; color: var(--mp-muted); display: flex; align-items: center; justify-content: center; font-size: 12px; font-weight: 700; }
    .item-rank.top { background: #EFF6FF; color: var(--mp-primary); }
    .item-main { flex: 1; min-width: 0; }
    .item-title { font-weight: 600; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .item-sub { font-size: 12px; color: var(--mp-muted); margin-top: 2px; }
    .item-amount { font-weight: 700; font-size: 14px; color: var(--mp-ink); }
    .status { display: inline-block; padding: 3px 8px; border-radius: 20px; font-size: 11px; font-weight: 600; text-transform: capitalize; }
    .status-pending { background: #FEF3C7; color: #92400E; }
    .status-paid { background: #D1FAE5; color: #065F46; }
    .status-processing { background: #DBEAFE; color: #1E40AF; }
    .status-ready { background: #E0E7FF; color: #3730A3; }
    .status-completed { background: #D1FAE5; color: #065F46; }
    .status-cancelled { background: #FEF2F2; color: #991B1B; }
    .filter-bar { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 12px; margin-bottom: 4px; -ms-overflow-style: none; scrollbar-width: none; }
    .filter-bar::-webkit-scrollbar { display: none; }
    .filter-pill { flex-shrink: 0; padding: 8px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; background: #fff; border: 1px solid var(--mp-border); color: var(--mp-muted); text-decoration: none; }
    .filter-pill.active { background: var(--mp-primary); color: #fff; border-color: var(--mp-primary); }
    .custom-dates { display: <?= $period === 'custom' ? 'flex' : 'none'; ?>; gap: 8px; align-items: center; margin-bottom: 16px; }
    .custom-dates input { flex: 1; padding: 10px 12px; border-radius: 12px; border: 1px solid var(--mp-border); font-size: 14px; font-family: inherit; }
    .custom-dates button { padding: 10px 14px; border-radius: 12px; background: var(--mp-primary); color: #fff; border: none; font-size: 13px; font-weight: 600; }
    .empty { text-align: center; padding: 30px; color: var(--mp-muted); font-size: 13px; }
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
          <h1>Store Dashboard</h1>
        </div>
      </div>

      <div class="filter-bar">
        <?php
          $pills = [
            'today' => 'Today',
            'yesterday' => 'Yesterday',
            'week' => '7 Days',
            'month' => '30 Days',
            'custom' => 'Custom',
          ];
        ?>
        <?php foreach($pills as $key => $label): ?>
          <a href="<?= base_url('mobile/online_store/dashboard?period=' . $key . ($key === 'custom' ? '&from=' . urlencode($from ?? date('Y-m-d')) . '&to=' . urlencode($to ?? date('Y-m-d')) : '')); ?>" class="filter-pill <?= $period === $key ? 'active' : ''; ?>"><?= $label; ?></a>
        <?php endforeach; ?>
      </div>
      <form class="custom-dates" method="get" action="<?= base_url('mobile/online_store/dashboard'); ?>">
        <input type="hidden" name="period" value="custom">
        <input type="date" name="from" value="<?= htmlspecialchars($from); ?>">
        <input type="date" name="to" value="<?= htmlspecialchars($to); ?>">
        <button type="submit">Apply</button>
      </form>

      <div class="kpi-grid">
        <div class="kpi-card">
          <div class="kpi-label">Orders</div>
          <div class="kpi-value"><?= (int)($stats['total_orders'] ?? 0); ?></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">Revenue</div>
          <div class="kpi-value" title="<?= strip_tags(mp_format_money($stats['total_revenue'] ?? 0)); ?>"><?= mp_format_money_compact($stats['total_revenue'] ?? 0); ?></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">Pending</div>
          <div class="kpi-value"><?= (int)($stats['pending_orders'] ?? 0); ?></div>
        </div>
        <div class="kpi-card">
          <div class="kpi-label">Paid</div>
          <div class="kpi-value"><?= (int)($stats['paid_orders'] ?? 0); ?></div>
        </div>
      </div>

      <div class="section">
        <div class="section-header">
          <h2 class="section-title">Recent Orders</h2>
          <a href="<?= base_url('mobile/online_store/orders'); ?>" class="section-link">View all</a>
        </div>
        <?php if(!empty($recent_orders)): ?>
          <?php foreach($recent_orders as $o): ?>
            <a href="<?= base_url('mobile/online_store/order/' . $o->id); ?>" class="list-item">
              <div class="item-main">
                <div class="item-title">#<?= $o->order_code; ?> — <?= htmlspecialchars($o->customer_name); ?></div>
                <div class="item-sub"><?= show_date($o->created_at); ?> · <?= store_number_format($o->grand_total); ?></div>
              </div>
              <span class="status status-<?= $o->order_status; ?>"><?= $o->order_status; ?></span>
            </a>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty">No orders yet today.</div>
        <?php endif; ?>
      </div>

      <div class="section">
        <div class="section-header">
          <h2 class="section-title">Top Online Products</h2>
        </div>
        <?php if(!empty($top_products)): ?>
          <?php $i=1; foreach($top_products as $tp): ?>
            <div class="list-item">
              <div class="item-rank <?= $i <= 3 ? 'top' : ''; ?>"><?= $i; ?></div>
              <div class="item-main">
                <div class="item-title"><?= htmlspecialchars($tp->item_name); ?></div>
                <div class="item-sub"><?= (int)$tp->total_qty; ?> sold</div>
              </div>
              <div class="item-amount"><?= store_number_format($tp->total_revenue); ?></div>
            </div>
          <?php $i++; endforeach; ?>
        <?php else: ?>
          <div class="empty">No top products yet.</div>
        <?php endif; ?>
      </div>
    </section>
    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
