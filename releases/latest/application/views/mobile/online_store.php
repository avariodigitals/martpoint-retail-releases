<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Online Store</title>
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
    .intro { font-size: 14px; color: var(--mp-muted); margin: 4px 0 18px; line-height: 1.45; }
    .menu-card { background: #fff; border-radius: 20px; border: 1px solid var(--mp-border); overflow: hidden; box-shadow: 0 1px 3px rgba(15,23,42,0.04); }
    .menu-item { display: flex; align-items: center; gap: 14px; padding: 15px 16px; border-bottom: 1px solid var(--mp-border); text-decoration: none; color: var(--mp-ink); transition: background 0.12s ease; }
    .menu-item:last-child { border-bottom: none; }
    .menu-item:active { background: #F8FAFC; }
    .menu-item .icon { width: 40px; height: 40px; border-radius: 12px; background: #EFF6FF; color: var(--mp-primary); display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
    .menu-item .label { flex: 1; font-size: 15px; font-weight: 600; }
    .menu-item .chevron { color: #94A3B8; font-size: 13px; }
    .empty { text-align: center; padding: 40px 20px; color: var(--mp-muted); font-size: 14px; }
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
          <h1>Online Store</h1>
        </div>
      </div>
      <p class="intro">Manage your storefront, orders, products and content from one place.</p>

      <?php if(!empty($menu_items)): ?>
      <div class="menu-card">
        <?php foreach($menu_items as $item): ?>
          <a href="<?= base_url($item['url']); ?>" class="menu-item">
            <div class="icon"><i class="fa <?= $item['icon']; ?>"></i></div>
            <div class="label"><?= $item['title']; ?></div>
            <div class="chevron"><i class="fa fa-chevron-right"></i></div>
          </a>
        <?php endforeach; ?>
      </div>
      <?php else: ?>
        <div class="empty">No Online Store options are available.</div>
      <?php endif; ?>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
