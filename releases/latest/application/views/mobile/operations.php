<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Operations</title>
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
    #app { max-width: 100%; margin: 0; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 16px 110px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 10px; background: var(--mp-bg); }
    .topbar-titles { flex: 1; min-width: 0; }
    .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .greeting { font-size: 13px; color: var(--mp-muted); margin: -4px 0 16px; }
    .ops-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; margin-bottom: 16px; }
    .op-card { background: #fff; border: 1px solid var(--mp-border); border-radius: 18px; padding: 16px; text-decoration: none; color: var(--mp-ink); display: flex; flex-direction: column; gap: 14px; min-height: 118px; transition: transform 0.08s ease, box-shadow 0.08s ease; box-shadow: 0 2px 6px rgba(0,0,0,0.05); }
    .op-card:active { transform: scale(0.98); }
    .op-card .icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
    .op-card .title { font-size: 15px; font-weight: 700; line-height: 1.25; }
    .op-card .desc { font-size: 12px; color: var(--mp-muted); line-height: 1.4; margin-top: auto; }
    .icon-blue { background: #EFF6FF; color: #2563EB; }
    .icon-primary { background: #DBEAFE; color: var(--mp-primary); }
    .icon-green { background: #D1FAE5; color: #059669; }
    .icon-orange { background: #FFEDD5; color: #EA580C; }
    .icon-red { background: #FEF2F2; color: #DC2626; }
    .icon-purple { background: #F3E8FF; color: #7C3AED; }
    .icon-teal { background: #CCFBF1; color: #0F766E; }
    .icon-yellow { background: #FFFBEB; color: #D97706; }
    .empty-state { text-align: center; padding: 48px 24px; color: var(--mp-muted); font-size: 14px; }
    .empty-state .empty-icon { font-size: 48px; margin-bottom: 12px; color: var(--mp-border); }
    .setup-link { display: inline-block; margin-top: 16px; padding: 10px 18px; border-radius: 12px; background: var(--mp-primary); color: #fff; text-decoration: none; font-weight: 600; font-size: 13px; }
    @media (min-width: 430px) { .screen { padding: 16px 20px 120px; } }
    @media (min-width: 600px) { .screen { padding: 16px 24px 120px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/more'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Operations</h1>
        </div>
      </div>
      <div class="greeting">Hello, <?= $display_name; ?></div>

      <?php if(!empty($operations)): ?>
        <div class="ops-grid">
          <?php foreach($operations as $op): ?>
            <a href="<?= base_url($op['url']); ?>" class="op-card">
              <div class="icon icon-<?= $op['color']; ?>"><i class="fa <?= $op['icon']; ?>"></i></div>
              <div class="title"><?= $op['title']; ?></div>
              <div class="desc"><?= $op['desc']; ?></div>
            </a>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="empty-state">
          <div class="empty-icon"><i class="fa fa-cogs"></i></div>
          <div>No operations workflows are enabled for this business.</div>
          <a href="<?= base_url('mobile/business_profile'); ?>" class="setup-link">Open Business Setup</a>
        </div>
      <?php endif; ?>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
