<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Public Catalogue</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root {
      --mp-primary: #0057FF;
      --mp-bg: #F1F5F9;
      --mp-surface: #FFFFFF;
      --mp-text: #0F172A;
      --mp-muted: #64748B;
      --mp-border: #E2E8F0;
      --mp-success: #10B981;
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
    .info-banner { background: #DBEAFE; color: #1E40AF; border: 1px solid #93C5FD; border-radius: 14px; padding: 12px 14px; font-size: 13px; line-height: 1.5; margin-bottom: 16px; }
    .settings-card { background: #fff; border: 1px solid var(--mp-border); border-radius: 18px; overflow: hidden; margin-bottom: 20px; box-shadow: 0 2px 6px rgba(0,0,0,0.04); }
    .settings-card .card-header { padding: 16px; border-bottom: 1px solid var(--mp-border); font-size: 15px; font-weight: 700; }
    .setting-row { display: flex; align-items: center; justify-content: space-between; padding: 16px; border-bottom: 1px solid var(--mp-border); }
    .setting-row:last-child { border-bottom: none; }
    .setting-label { font-size: 14px; color: var(--mp-muted); }
    .setting-value { font-size: 14px; font-weight: 600; color: var(--mp-ink); display: flex; align-items: center; gap: 8px; }
    .badge { display: inline-flex; align-items: center; padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600; }
    .badge-yes { background: #D1FAE5; color: #065F46; }
    .badge-no { background: #F1F5F9; color: var(--mp-muted); }
    .slug-text { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 13px; }
    .actions { display: flex; flex-direction: column; gap: 10px; margin-top: 10px; }
    .btn { display: flex; align-items: center; justify-content: center; gap: 8px; padding: 14px; border-radius: 14px; text-decoration: none; font-size: 14px; font-weight: 600; border: none; }
    .btn-primary { background: var(--mp-primary); color: #fff; }
    .btn-success { background: #10B981; color: #fff; }
    .empty-notice { text-align: center; padding: 40px 24px; color: var(--mp-muted); font-size: 14px; }
    @media (min-width: 430px) { .screen { padding: 16px 20px 120px; } }
    @media (min-width: 600px) { .screen { padding: 16px 24px 120px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/operations'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Public Catalogue</h1>
        </div>
      </div>
      <div class="greeting">Hello, <?= $display_name; ?></div>

      <div class="info-banner">
        <i class="fa fa-info-circle"></i> These settings are managed from Business Profile → Templates &amp; Labels.
      </div>

      <?php if(!empty($settings)): ?>
        <div class="settings-card">
          <div class="card-header">Catalogue Settings</div>
          <div class="setting-row">
            <div class="setting-label">Enabled</div>
            <div class="setting-value">
              <span class="badge <?= (!empty($settings['enabled']) && $settings['enabled'] == '1') ? 'badge-yes' : 'badge-no'; ?>">
                <?= (!empty($settings['enabled']) && $settings['enabled'] == '1') ? 'Yes' : 'No'; ?>
              </span>
            </div>
          </div>
          <div class="setting-row">
            <div class="setting-label">Slug</div>
            <div class="setting-value slug-text"><?= !empty($settings['slug']) ? htmlspecialchars($settings['slug']) : 'catalogue'; ?></div>
          </div>
          <div class="setting-row">
            <div class="setting-label">Show Products</div>
            <div class="setting-value">
              <span class="badge <?= (!empty($settings['show_products']) && $settings['show_products'] == '1') ? 'badge-yes' : 'badge-no'; ?>">
                <?= (!empty($settings['show_products']) && $settings['show_products'] == '1') ? 'Yes' : 'No'; ?>
              </span>
            </div>
          </div>
          <div class="setting-row">
            <div class="setting-label">Show Services</div>
            <div class="setting-value">
              <span class="badge <?= (!empty($settings['show_services']) && $settings['show_services'] == '1') ? 'badge-yes' : 'badge-no'; ?>">
                <?= (!empty($settings['show_services']) && $settings['show_services'] == '1') ? 'Yes' : 'No'; ?>
              </span>
            </div>
          </div>
        </div>
      <?php else: ?>
        <div class="empty-notice">Public catalogue has not been configured yet.</div>
      <?php endif; ?>

      <div class="actions">
        <a href="<?= base_url('mobile/business_profile'); ?>" class="btn btn-primary"><i class="fa fa-cog"></i> Edit in Business Profile</a>
        <a href="<?= base_url('store/' . ($store_slug ?? '') . '/catalogue'); ?>" class="btn btn-success" target="_blank"><i class="fa fa-eye"></i> Preview Public Catalogue</a>
      </div>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
