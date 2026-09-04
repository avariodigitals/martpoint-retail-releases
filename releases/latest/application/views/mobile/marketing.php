<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Marketing</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .greeting { font-size: 13px; color: var(--mp-muted); margin: -4px 0 16px; }
    .menu-list { display: flex; flex-direction: column; gap: 10px; }
    .menu-card { background: #fff; border-radius: 16px; border: 1px solid var(--mp-border); overflow: hidden; box-shadow: 0 1px 3px rgba(0,0,0,0.04); }
    .menu-item { display: flex; align-items: center; gap: 14px; padding: 14px 16px; border-bottom: 1px solid var(--mp-border); text-decoration: none; color: var(--mp-ink); }
    .menu-item:last-child { border-bottom: none; }
    .menu-item .icon { width: 38px; height: 38px; border-radius: 12px; background: var(--mp-bg); color: var(--mp-primary); display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
    .menu-item .icon.blue { background: #EFF6FF; color: #2563EB; }
    .menu-item .icon.primary { background: #DBEAFE; color: var(--mp-primary); }
    .menu-item .icon.green { background: #D1FAE5; color: #059669; }
    .menu-item .icon.orange { background: #FFEDD5; color: #EA580C; }
    .menu-item .icon.red { background: #FEF2F2; color: #DC2626; }
    .menu-item .icon.purple { background: #F3E8FF; color: #7C3AED; }
    .menu-item .icon.teal { background: #CCFBF1; color: #0F766E; }
    .menu-item .icon.yellow { background: #FFFBEB; color: #D97706; }
    .menu-item .text { flex: 1; min-width: 0; }
    .menu-item .title { font-weight: 600; font-size: 15px; }
    .menu-item .desc { font-size: 13px; color: var(--mp-muted); margin-top: 2px; }
    .menu-item .arrow { color: var(--mp-muted); flex-shrink: 0; }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 120px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/more'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Marketing</h1>
        </div>
      </div>
      <div class="greeting">Hello, <?= $display_name; ?></div>

      <div class="menu-list">
        <div class="menu-card">
          <?php foreach($marketing_items as $item): ?>
            <a href="<?= base_url($item['url_mobile']); ?>" class="menu-item">
              <div class="icon <?= $item['color']; ?>"><i class="fa <?= $item['icon']; ?>"></i></div>
              <div class="text">
                <div class="title"><?= $item['title']; ?></div>
                <div class="desc"><?= $item['desc']; ?></div>
              </div>
              <div class="arrow"><i class="fa fa-chevron-right"></i></div>
            </a>
          <?php endforeach; ?>
        </div>
      </div>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
