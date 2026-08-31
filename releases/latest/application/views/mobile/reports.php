<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= $SITE_TITLE ?? 'MartPoint'; ?> — Reports</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-purple: #7C3AED; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0;}
    .summary-card { background: linear-gradient(135deg, var(--mp-purple) 0%, #5B21B6 100%); border-radius: 16px; padding: 18px; color: #fff; margin-bottom: 16px; }
    .summary-card .label { font-size: 13px; opacity: 0.9; margin-bottom: 6px; }
    .summary-card .value { font-size: 26px; font-weight: 700; }
    .report-list { display: flex; flex-direction: column; gap: 10px; }
    .report-card { display: flex; align-items: center; gap: 14px; padding: 14px; background: #fff; border-radius: 14px; border: 1px solid var(--mp-border); text-decoration: none; color: var(--mp-ink); }
    .report-card .icon { width: 42px; height: 42px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0; }
    .report-card .icon.blue { background: #EFF6FF; color: #2563EB; }
    .report-card .icon.green { background: #ECFDF5; color: #059669; }
    .report-card .icon.orange { background: #FFEDD5; color: #EA580C; }
    .report-card .icon.red { background: #FEF2F2; color: #DC2626; }
    .report-card .icon.purple { background: #F3E8FF; color: #7C3AED; }
    .report-card .icon.teal { background: #CCFBF1; color: #0F766E; }
    .report-card .icon.yellow { background: #FFFBEB; color: #D97706; }
    .report-card .icon.primary { background: #DBEAFE; color: #2563EB; }
    .report-card .text { flex: 1; min-width: 0; }
    .report-card .title { font-weight: 600; font-size: 15px; }
    .report-card .desc { font-size: 13px; color: var(--mp-muted); margin-top: 2px; }
    .report-card .arrow { color: var(--mp-muted); }
    .empty-state { text-align: center; padding: 40px 20px; color: var(--mp-muted); }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 120px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 120px; } }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/more'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Reports</h1>
        </div>
      </div>

      <div class="summary-card">
        <div class="label">Available Reports</div>
        <div class="value"><?= count($report_types); ?></div>
      </div>

      <div class="report-list">
        <?php if(!empty($report_types)): ?>
          <?php foreach($report_types as $r): ?>
            <a href="<?= base_url($r['url'] ?? 'mobile/report/' . $r['type']); ?>" class="report-card">
              <div class="icon <?= $r['color']; ?>"><i class="fa <?= $r['icon']; ?>"></i></div>
              <div class="text">
                <div class="title"><?= $r['title']; ?></div>
                <div class="desc"><?= $r['desc']; ?></div>
              </div>
              <div class="arrow"><i class="fa fa-chevron-right"></i></div>
            </a>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-state">No reports available.</div>
        <?php endif; ?>
      </div>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
