<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Loyalty & Rewards</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root {
      --mp-primary: #0057FF;
      --mp-primary-dark: #0044CC;
      --mp-bg: #F1F5F9;
      --mp-surface: #FFFFFF;
      --mp-text: #0F172A;
      --mp-muted: #64748B;
      --mp-border: #E2E8F0;
      --mp-success: #10B981;
      --mp-danger: #EF4444;
      --mp-warning: #F59E0B;
      --mp-ink: #1E293B;
      --safe-bottom: env(safe-area-inset-bottom, 0px);
    }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 100%; margin: 0; background: var(--mp-bg); min-height: 100vh; position: relative; }
    .screen { padding: 12px 16px 110px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 10px; background: #fff; border: 1px solid var(--mp-border); }
    .topbar-titles { flex: 1; min-width: 0; }
    .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .section { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 18px; margin-bottom: 16px; overflow: hidden; box-shadow: 0 1px 3px rgba(15,23,42,0.04); }
    .section-head { padding: 16px; font-size: 16px; font-weight: 700; border-bottom: 1px solid var(--mp-border); }
    .section-body { padding: 16px; display: flex; flex-direction: column; gap: 16px; }
    .kpi-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .kpi-card { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 16px; padding: 16px; }
    .kpi-card .label { font-size: 12px; color: var(--mp-muted); font-weight: 600; margin-bottom: 8px; }
    .kpi-card .value { font-size: 22px; font-weight: 700; color: var(--mp-ink); }
    .kpi-card .value small { font-size: 12px; color: var(--mp-muted); font-weight: 500; display: block; margin-top: 4px; }
    .actions-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 12px; }
    .action-card { display: flex; align-items: center; gap: 12px; background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 16px; padding: 14px; text-decoration: none; color: var(--mp-ink); }
    .action-card i { width: 36px; height: 36px; border-radius: 10px; background: #EFF6FF; color: var(--mp-primary); display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
    .action-card .title { font-size: 14px; font-weight: 700; }
    .action-card .desc { font-size: 12px; color: var(--mp-muted); margin-top: 2px; }
    .loyalty-status { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 600; color: var(--mp-success); }
    .loyalty-status.off { color: var(--mp-muted); }
    .empty-state { text-align: center; padding: 40px 20px; color: var(--mp-muted); }
    .empty-state i { font-size: 48px; margin-bottom: 12px; display: block; color: var(--mp-border); }
    @media (min-width: 430px) { .screen { padding: 16px 20px 120px; } }
    @media (min-width: 600px) { .screen { padding: 16px 24px 120px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= $back_url; ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Loyalty &amp; Rewards</h1>
        </div>
      </div>

      <div class="section">
        <div class="section-body">
          <div class="kpi-grid">
            <div class="kpi-card">
              <div class="label">Active Members</div>
              <div class="value"><?= number_format($stats['active_members'] ?? 0); ?></div>
            </div>
            <div class="kpi-card">
              <div class="label">Points Issued</div>
              <div class="value"><?= number_format($stats['total_points_issued'] ?? 0); ?></div>
            </div>
            <div class="kpi-card">
              <div class="label">Points Redeemed</div>
              <div class="value"><?= number_format($stats['total_points_redeemed'] ?? 0); ?></div>
            </div>
            <div class="kpi-card">
              <div class="label">Points Available</div>
              <div class="value"><?= number_format($stats['points_available'] ?? 0); ?></div>
            </div>
            <div class="kpi-card">
              <div class="label">Store Credit</div>
              <div class="value" title="<?= strip_tags(mp_format_money($stats['store_credit_outstanding'] ?? 0)); ?>"><?= mp_format_money_compact($stats['store_credit_outstanding'] ?? 0); ?><small>outstanding</small></div>
            </div>
            <div class="kpi-card">
              <div class="label">Gift Card Liability</div>
              <div class="value" title="<?= strip_tags(mp_format_money($stats['gift_card_liability'] ?? 0)); ?>"><?= mp_format_money_compact($stats['gift_card_liability'] ?? 0); ?><small>liability</small></div>
            </div>
          </div>
          <div class="loyalty-status <?= !empty($settings->loyalty_enabled) ? '' : 'off'; ?>">
            <i class="fa <?= !empty($settings->loyalty_enabled) ? 'fa-toggle-on' : 'fa-toggle-off'; ?>"></i>
            Loyalty is <?= !empty($settings->loyalty_enabled) ? 'enabled' : 'disabled'; ?>
          </div>
        </div>
      </div>

      <div class="section">
        <div class="section-head">Quick Actions</div>
        <div class="section-body">
          <div class="actions-grid">
            <a href="<?= base_url('mobile/loyalty/settings'); ?>" class="action-card">
              <i class="fa fa-cog"></i>
              <div>
                <div class="title">Settings</div>
                <div class="desc">Configure rules</div>
              </div>
            </a>
            <a href="<?= base_url('mobile/loyalty/tiers'); ?>" class="action-card">
              <i class="fa fa-sitemap"></i>
              <div>
                <div class="title">Customer Tiers</div>
                <div class="desc">View tiers</div>
              </div>
            </a>
            <a href="<?= base_url('mobile/loyalty/bonus_rules'); ?>" class="action-card">
              <i class="fa fa-star"></i>
              <div>
                <div class="title">Bonus Rules</div>
                <div class="desc">Promotions</div>
              </div>
            </a>
            <a href="<?= base_url('mobile/loyalty/product_points'); ?>" class="action-card">
              <i class="fa fa-cube"></i>
              <div>
                <div class="title">Product Points</div>
                <div class="desc">Item bonuses</div>
              </div>
            </a>
            <a href="<?= base_url('mobile/loyalty/points_history'); ?>" class="action-card">
              <i class="fa fa-history"></i>
              <div>
                <div class="title">Points History</div>
                <div class="desc">Transactions</div>
              </div>
            </a>
            <a href="<?= base_url('mobile/loyalty/referral_program'); ?>" class="action-card">
              <i class="fa fa-share-alt"></i>
              <div>
                <div class="title">Referral Program</div>
                <div class="desc">Referrals</div>
              </div>
            </a>
          </div>
        </div>
      </div>

      <?php if(!empty($tiers)): ?>
      <div class="section">
        <div class="section-head">Tiers</div>
        <div class="section-body" style="gap: 10px;">
          <?php foreach($tiers as $t): ?>
            <div style="display:flex; justify-content:space-between; align-items:center; padding:12px; background:var(--mp-bg); border-radius:12px;">
              <div>
                <div style="font-weight:700; font-size:15px;"><?= htmlspecialchars($t->tier_name); ?></div>
                <div style="font-size:12px; color:var(--mp-muted);">
                  <?= !empty($t->minimum_spend) ? 'Spend '.mp_format_money($t->minimum_spend) : 'Points '.number_format($t->minimum_points); ?> minimum
                </div>
              </div>
              <span style="font-size:12px; font-weight:600; color:var(--mp-primary);"><?= (float)($t->discount_percentage); ?>% off</span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/chat'); ?>
  <?php $this->load->view('mobile/mp_alert'); ?>
</body>
</html>
