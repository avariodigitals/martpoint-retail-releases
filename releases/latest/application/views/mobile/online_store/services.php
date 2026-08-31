<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Services</title>
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
    .search-bar { display: flex; gap: 8px; margin-bottom: 16px; }
    .search-bar input { flex: 1; padding: 12px 14px; border-radius: 12px; border: 1px solid var(--mp-border); font-family: inherit; font-size: 14px; outline: none; }
    .search-bar button { padding: 0 16px; border-radius: 12px; background: var(--mp-primary); color: #fff; border: none; font-size: 16px; }
    .service-card { background: #fff; border-radius: 16px; border: 1px solid var(--mp-border); padding: 16px; margin-bottom: 12px; box-shadow: 0 1px 3px rgba(15,23,42,0.04); }
    .service-name { font-size: 15px; font-weight: 700; margin-bottom: 4px; }
    .service-meta { font-size: 13px; color: var(--mp-muted); margin-bottom: 10px; }
    .service-row { display: flex; justify-content: space-between; font-size: 14px; margin-bottom: 6px; }
    .service-row:last-child { margin-bottom: 0; }
    .service-label { color: var(--mp-muted); }
    .service-value { font-weight: 600; }
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
          <h1>Services</h1>
        </div>
      </div>

      <form class="search-bar" method="get" action="<?= base_url('mobile/online_store/services'); ?>">
        <input type="text" name="search" value="<?= htmlspecialchars($search ?? ''); ?>" placeholder="Search services...">
        <button type="submit"><i class="fa fa-search"></i></button>
      </form>

      <?php if(!empty($services)): ?>
        <?php foreach($services as $s): ?>
          <div class="service-card">
            <div class="service-name"><?= htmlspecialchars($s->service_name); ?></div>
            <div class="service-meta"><?= htmlspecialchars($s->category_name ?: '-'); ?></div>
            <div class="service-row">
              <span class="service-label">Duration</span>
              <span class="service-value"><?= (int)($s->duration_minutes ?? 0); ?> min</span>
            </div>
            <div class="service-row">
              <span class="service-label">Price</span>
              <span class="service-value"><?= store_number_format($s->price ?? 0); ?></span>
            </div>
            <div class="service-row">
              <span class="service-label">Online booking</span>
              <span class="service-value"><?= !empty($s->available_online) ? 'Enabled' : 'Disabled'; ?></span>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty">No services found.</div>
      <?php endif; ?>
    </section>
    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
