<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= $SITE_TITLE ?? 'MartPoint'; ?> — Quotation Report</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-purple: #7C3AED; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0;}
    .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 16px; }
    .stat-card { background: #fff; border-radius: 14px; padding: 14px; border: 1px solid var(--mp-border); }
    .stat-card .label { font-size: 12px; color: var(--mp-muted); margin-bottom: 4px; }
    .stat-card .value { font-size: 20px; font-weight: 700; }
    .quote-card { background: #fff; border-radius: 14px; padding: 14px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .quote-title { font-size: 15px; font-weight: 700; margin-bottom: 4px; }
    .quote-meta { font-size: 13px; color: var(--mp-muted); margin: 3px 0; }
    .quote-meta i { margin-right: 4px; color: var(--mp-primary); width: 16px; }
    .badge { display: inline-block; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 20px; }
    .badge.converted { background: #D1FAE5; color: #065F46; }
    .badge.active { background: #E0E7FF; color: var(--mp-primary); }
    .badge.expired { background: #FEE2E2; color: #B91C1B; }
    .empty-state { text-align: center; padding: 32px; color: var(--mp-muted); }
    .bottom-nav { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 430px; background: #fff; border-top: 1px solid var(--mp-border); display: flex; justify-content: space-around; padding: 8px 0 calc(8px + var(--safe-bottom)); z-index: 1000; }
    .nav-item { display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 6px 14px; border: none; background: transparent; color: var(--mp-muted); font-size: 10px; font-weight: 500; text-decoration: none; }
    .nav-item .icon { font-size: 20px; }
    .nav-item.active { color: var(--mp-primary); }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .bottom-nav { max-width: 100%; left: 0; right: 0; transform: none; } .screen { padding: 16px 16px 100px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 120px; } }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Quotation Report</h1>
        </div>
      </div>

      <div class="stats-grid">
        <div class="stat-card">
          <div class="label">Total</div>
          <div class="value"><?= store_number_format($total); ?></div>
        </div>
        <div class="stat-card">
          <div class="label">Converted</div>
          <div class="value" style="color:var(--mp-success);"><?= $converted; ?></div>
        </div>
        <div class="stat-card">
          <div class="label">Active</div>
          <div class="value" style="color:var(--mp-primary);"><?= $active; ?></div>
        </div>
        <div class="stat-card">
          <div class="label">Expired</div>
          <div class="value" style="color:var(--mp-danger);"><?= $expired; ?></div>
        </div>
      </div>

      <div id="quoteList">
        <?php if(!empty($records)): ?>
          <?php
            $now = date('Y-m-d');
            $totals = ['converted' => 0, 'active' => 0, 'expired' => 0];
            foreach($records as $r){
              if(!empty($r->sales_status)){ $totals['converted'] += $r->grand_total; }
              elseif(!empty($r->expire_date) && $r->expire_date < $now){ $totals['expired'] += $r->grand_total; }
              else { $totals['active'] += $r->grand_total; }
            }
          ?>
          <div class="stat-card" style="margin-bottom:16px;">
            <div class="label" style="margin-bottom:6px;">Value by Status</div>
            <div class="quote-meta" style="color:var(--mp-success);">Converted: <?= store_number_format($totals['converted']); ?></div>
            <div class="quote-meta" style="color:var(--mp-primary);">Active: <?= store_number_format($totals['active']); ?></div>
            <div class="quote-meta" style="color:var(--mp-danger);">Expired: <?= store_number_format($totals['expired']); ?></div>
          </div>
          <?php foreach($records as $r):
            $status = 'Active';
            $badge = 'active';
            if(!empty($r->sales_status)){ $status = 'Converted'; $badge = 'converted'; }
            elseif(!empty($r->expire_date) && $r->expire_date < $now){ $status = 'Expired'; $badge = 'expired'; }
          ?>
            <div class="quote-card">
              <div style="display:flex; justify-content:space-between; align-items:flex-start;">
                <div class="quote-title"><?= $r->quotation_code; ?></div>
                <span class="badge <?= $badge; ?>"><?= $status; ?></span>
              </div>
              <div class="quote-meta"><i class="fa fa-user"></i> <?= $r->customer_name ?: 'Walk-in'; ?></div>
              <div class="quote-meta"><i class="fa fa-calendar"></i> <?= show_date($r->quotation_date); ?></div>
              <?php if(!empty($r->expire_date)): ?>
                <div class="quote-meta"><i class="fa fa-hourglass"></i> Expires <?= show_date($r->expire_date); ?></div>
              <?php endif; ?>
              <div class="quote-meta"><i class="fa fa-money"></i> <?= store_number_format($r->grand_total); ?></div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-state">
            <i class="fa fa-quote-left" style="font-size:48px; margin-bottom:12px;"></i>
            <div>No quotations found.</div>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </div>

  <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
