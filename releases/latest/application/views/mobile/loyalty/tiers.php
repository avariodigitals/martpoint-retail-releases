<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Customer Tiers</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
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
    .section-body { padding: 16px; display: flex; flex-direction: column; gap: 12px; }
    .tier-card { background: #fff; border: 1px solid var(--mp-border); border-radius: 16px; padding: 16px; }
    .tier-card .name { font-weight: 700; font-size: 16px; margin-bottom: 4px; }
    .tier-card .meta { font-size: 13px; color: var(--mp-muted); line-height: 1.6; }
    .tier-card .badge { display: inline-block; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 20px; background: #EFF6FF; color: var(--mp-primary); margin-top: 8px; }
    .btn-primary { width: 100%; display: block; padding: 16px; border-radius: 14px; border: none; background: var(--mp-primary); color: #fff; font-size: 16px; font-weight: 700; text-align: center; text-decoration: none; cursor: pointer; }
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
          <h1>Customer Tiers</h1>
        </div>
      </div>

      <div class="section">
        <div class="section-body">
          <?php if(!empty($tiers)): foreach($tiers as $t): ?>
            <div class="tier-card">
              <div class="name"><?= htmlspecialchars($t->tier_name); ?></div>
              <div class="meta" title="<?= strip_tags(mp_format_money($t->minimum_spend ?? 0)); ?>">
                Min Spend: <?= mp_format_money_compact($t->minimum_spend ?? 0); ?><br>
                Min Points: <?= number_format($t->minimum_points ?? 0); ?><br>
                Discount: <?= (float)($t->discount_percentage); ?>%<br>
                Bonus Points: <?= (float)($t->bonus_points_percentage); ?>%<br>
                Birthday Reward: <?= htmlspecialchars(ucfirst($t->birthday_reward_type ?? 'points')); ?> <?= (float)($t->birthday_reward_value ?? 0); ?><br>
                Priority Service: <?= !empty($t->priority_service) ? 'Yes' : 'No'; ?><br>
                Sort Order: <?= (int)($t->sort_order ?? 0); ?>
              </div>
              <?php if(!empty($t->minimum_spend) || !empty($t->minimum_points)): ?>
                <span class="badge">Tier</span>
              <?php endif; ?>
            </div>
          <?php endforeach; else: ?>
            <div class="empty-state"><i class="fa fa-sitemap"></i><div>No tiers configured.</div></div>
          <?php endif; ?>
        </div>
      </div>

      <a href="<?= base_url('mobile/loyalty/settings'); ?>" class="btn-primary"><i class="fa fa-cog"></i> Manage Tiers</a>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/chat'); ?>
  <?php $this->load->view('mobile/mp_alert'); ?>
</body>
</html>
