<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
<?php $this->load->view('comman/app_icons'); ?>
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?> — Help Center</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 16px 120px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .lead { font-size: 14px; color: var(--mp-muted); margin: 0 0 22px; line-height: 1.5; }
    .folder-grid { display: grid; gap: 14px; }
    .folder-section { margin: 2px 0 -4px; font-size: 15px; font-weight: 700; color: var(--mp-text); }
    .folder-card { display: flex; align-items: center; gap: 16px; background: #fff; border: 1px solid var(--mp-border); border-radius: 16px; padding: 18px 16px; text-decoration: none; color: var(--mp-text); box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .folder-card .icon { width: 52px; height: 52px; border-radius: 14px; background: rgba(0,87,255,0.08); color: var(--mp-primary); display: flex; align-items: center; justify-content: center; font-size: 22px; flex-shrink: 0; }
    .folder-card .text { flex: 1; min-width: 0; }
    .folder-card .title { font-weight: 700; font-size: 17px; margin-bottom: 2px; }
    .folder-card .desc { font-size: 13px; color: var(--mp-muted); }
    .folder-card .arrow { color: var(--mp-muted); font-size: 16px; }
    .folder-card.locked { opacity: 0.65; background: #FAFAF9; cursor: default; }
    .folder-card.locked .icon { background: #E7E5E4; color: #78716C; }
    .folder-card.locked .badge { display: inline-block; margin-left: 8px; padding: 2px 8px; border-radius: 999px; background: #E7E5E4; color: #57534E; font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }
    .support-banner { margin-top: 24px; background: #E0E7FF; border-radius: 16px; padding: 16px; display: flex; align-items: center; gap: 12px; }
    .support-banner .icon { width: 40px; height: 40px; border-radius: 50%; background: var(--mp-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 18px; }
    .support-banner .text { flex: 1; }
    .support-banner .title { font-weight: 600; font-size: 15px; }
    .support-banner .desc { font-size: 13px; color: var(--mp-muted); }
    .support-banner a { color: var(--mp-primary); font-weight: 600; text-decoration: none; }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/more'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Help Center</h1>
        </div>
      </div>

      <?php $this->load->helper('industry_guide'); $industry_guide = mp_industry_guide(); ?>
      <?php $industry_type = function_exists('mp_get_store_profile') ? (mp_get_store_profile()['industry_type'] ?? '') : ''; ?>
      <p class="lead"><?= $industry_guide ? 'Start with your industry walkthrough, then use the standard guides for shared tasks.' : 'Choose the guide that matches your role. Customer guides are for day-to-day use.'; ?></p>

      <div class="folder-grid">
        <?php if($industry_guide): ?>
        <h2 class="folder-section">Your industry walkthrough</h2>
        <a href="<?= base_url('docs/product-design/customer-guide/'.$industry_guide['file']); ?>" target="_blank" rel="noopener" class="folder-card">
          <div class="icon"><i class="fa <?= htmlspecialchars($industry_guide['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i></div>
          <div class="text"><div class="title"><?= htmlspecialchars($industry_guide['title'], ENT_QUOTES, 'UTF-8'); ?></div><div class="desc"><?= htmlspecialchars($industry_guide['description'], ENT_QUOTES, 'UTF-8'); ?></div></div>
          <div class="arrow"><i class="fa fa-external-link"></i></div>
        </a>
        <?php if($industry_guide['pdf']): ?><a href="<?= base_url('docs/product-design/customer-guide/'.$industry_guide['pdf']); ?>" target="_blank" rel="noopener" class="folder-card">
          <div class="icon"><i class="fa fa-file-pdf-o"></i></div>
          <div class="text"><div class="title">Download industry guide (PDF)</div><div class="desc">Printable walkthrough for your team</div></div>
          <div class="arrow"><i class="fa fa-external-link"></i></div>
        </a><?php endif; ?>
        <?php endif; ?>
        <h2 class="folder-section">Standard guides</h2>
        <a href="<?= base_url('docs/product-design/customer-guide/index.html?industry='.rawurlencode($industry_type)); ?>" target="_blank" rel="noopener" class="folder-card">
          <div class="icon"><i class="fa fa-users"></i></div>
          <div class="text">
            <div class="title">Customer Guide</div>
            <div class="desc">General user manual, purchasing, invoices and reports</div>
          </div>
          <div class="arrow"><i class="fa fa-external-link"></i></div>
        </a>

        <?php if($industry_guide): ?>
        <a href="<?= base_url('docs/product-design/customer-guide/user-manual.html'); ?>" target="_blank" rel="noopener" class="folder-card">
          <div class="icon"><i class="fa fa-book"></i></div>
          <div class="text"><div class="title">User Manual</div><div class="desc">Core MartPoint screens and account tasks</div></div>
          <div class="arrow"><i class="fa fa-external-link"></i></div>
        </a>
        <a href="<?= base_url('docs/product-design/customer-guide/purchase.html'); ?>" target="_blank" rel="noopener" class="folder-card">
          <div class="icon"><i class="fa fa-cart-arrow-down"></i></div>
          <div class="text"><div class="title">Purchasing Guide</div><div class="desc">Suppliers, purchase records and payments</div></div>
          <div class="arrow"><i class="fa fa-external-link"></i></div>
        </a>
        <a href="<?= base_url('docs/product-design/customer-guide/shipping-creation-guide.html'); ?>" target="_blank" rel="noopener" class="folder-card">
          <div class="icon"><i class="fa fa-truck"></i></div>
          <div class="text"><div class="title">Shipping Creation Guide</div><div class="desc">Online delivery methods and in-store shipping fees</div></div>
          <div class="arrow"><i class="fa fa-external-link"></i></div>
        </a>
        <a href="<?= base_url('docs/product-design/customer-guide/reports.html'); ?>" target="_blank" rel="noopener" class="folder-card">
          <div class="icon"><i class="fa fa-pie-chart"></i></div>
          <div class="text"><div class="title">Reports Guide</div><div class="desc">Review activity and balances</div></div>
          <div class="arrow"><i class="fa fa-external-link"></i></div>
        </a>
        <?php else: ?>
        <a href="<?= base_url('docs/product-design/customer-guide/cashier-guide.html'); ?>" target="_blank" rel="noopener" class="folder-card">
          <div class="icon"><i class="fa fa-calculator"></i></div>
          <div class="text">
            <div class="title">Cashier Guide</div>
            <div class="desc">POS, payment, receipts, holds, returns, password changes</div>
          </div>
          <div class="arrow"><i class="fa fa-external-link"></i></div>
        </a>

        <div class="folder-card locked">
          <div class="icon"><i class="fa fa-cogs"></i></div>
          <div class="text">
            <div class="title">Technical Guide <span class="badge">Internal</span></div>
            <div class="desc">Internal installation and technology documentation only</div>
          </div>
          <div class="arrow"><i class="fa fa-lock"></i></div>
        </div>
        <?php endif; ?>
      </div>

      <div class="support-banner">
        <div class="icon"><i class="fa fa-life-ring"></i></div>
        <div class="text">
          <div class="title">Need help?</div>
          <div class="desc">Go to <a href="<?= base_url('mobile/support'); ?>">Support</a> or visit <a href="https://www.martpoint.com.ng/support" target="_blank" rel="noopener">martpoint.com.ng/support</a></div>
        </div>
      </div>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
