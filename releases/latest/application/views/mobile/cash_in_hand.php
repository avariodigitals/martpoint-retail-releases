<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — <?= htmlspecialchars($page_title ?? 'Cash in Hand'); ?></title>
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
      --mp-danger: #EF4444;
      --safe-bottom: env(safe-area-inset-bottom, 0px);
    }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; }
    #app { width: 100%; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 0 0 100px; }
    .topbar { display: flex; align-items: center; justify-content: space-between; padding: 16px; background: #fff; border-bottom: 1px solid var(--mp-border); }
    .topbar a { color: var(--mp-text); text-decoration: none; font-size: 20px; }
    .topbar h1 { font-size: 18px; font-weight: 700; margin: 0; }
    .hero { background: linear-gradient(135deg, var(--mp-success) 0%, #059669 100%); color: #fff; padding: 28px 20px 22px; margin: 0 0 20px; border-radius: 0 0 24px 24px; text-align: center; box-shadow: 0 10px 25px rgba(16,185,129,0.25); }
    .hero .label { font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px; opacity: 0.85; margin-bottom: 8px; }
    .hero .amount { font-size: 44px; font-weight: 800; line-height: 1.1; margin-bottom: 20px; word-break: break-word; }
    .hero .hero-summary { display: flex; justify-content: center; gap: 32px; border-top: 1px solid rgba(255,255,255,0.25); padding-top: 14px; margin-bottom: 10px; }
    .hero .hero-summary > div { display: flex; flex-direction: column; align-items: center; gap: 2px; }
    .hero .mini-label { font-size: 11px; text-transform: uppercase; opacity: 0.8; }
    .hero .mini-amount { font-size: 16px; font-weight: 700; }
    .hero .mini-amount.in { color: #D1FAE5; }
    .hero .mini-amount.out { color: #FECACA; }
    .hero .updated { font-size: 11px; opacity: 0.75; }
    .date-filter { padding: 16px; background: #fff; border-bottom: 1px solid var(--mp-border); }
    .date-form { display: flex; gap: 10px; }
    .date-form input[type="date"] { flex: 1; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; font-family: inherit; background: #fff; color: var(--mp-text); outline: none; }
    .date-form button { padding: 12px 18px; border: none; border-radius: 12px; background: var(--mp-primary); color: #fff; font-size: 14px; font-weight: 600; }
    .section { padding: 0 16px; margin-bottom: 20px; }
    .section-title { font-size: 15px; font-weight: 700; margin: 0 0 12px; }
    .card { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 16px; overflow: hidden; }
    .breakdown-list { display: flex; flex-direction: column; }
    .breakdown-row { display: flex; align-items: center; justify-content: space-between; padding: 14px 16px; border-bottom: 1px solid var(--mp-border); }
    .breakdown-row:last-child { border-bottom: none; }
    .breakdown-row .left { display: flex; align-items: center; gap: 12px; }
    .breakdown-row .icon { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; }
    .breakdown-row.in .icon { background: #D1FAE5; color: #047857; }
    .breakdown-row.out .icon { background: #FECACA; color: #B91C1C; }
    .breakdown-row .label { font-size: 14px; font-weight: 500; color: var(--mp-ink); }
    .breakdown-row .amount { font-size: 15px; font-weight: 700; }
    .breakdown-row.in .amount { color: #047857; }
    .breakdown-row.out .amount { color: #B91C1C; }
    .report-card { display: flex; align-items: center; justify-content: space-between; padding: 16px; margin: 0 16px 16px; background: #EFF6FF; border: 1px solid #BFDBFE; border-radius: 14px; color: var(--mp-primary); text-decoration: none; font-weight: 600; font-size: 14px; }
    .back-link { display: inline-flex; align-items: center; gap: 6px; color: var(--mp-primary); text-decoration: none; font-weight: 600; font-size: 14px; margin: 0 16px 16px; }
    @media (min-width: 600px) {
      .screen { padding: 0 0 120px; }
      .topbar { padding: 20px 24px; }
      .topbar h1 { font-size: 22px; }
      .hero { padding: 40px 32px 28px; border-radius: 0 0 32px 32px; }
      .hero .amount { font-size: 56px; }
      .hero .hero-summary { gap: 48px; }
      .section { padding: 0 24px; margin-bottom: 24px; }
      .date-filter { padding: 20px 24px; }
      .report-card { margin: 0 24px 24px; padding: 18px; }
      .back-link { margin: 0 24px 24px; }
      .breakdown-row { padding: 16px; }
      .breakdown-row .label { font-size: 16px; }
      .breakdown-row .amount { font-size: 18px; }
    }
    @media (min-width: 900px) {
      .screen { padding: 0 0 140px; }
      .topbar { padding: 24px 40px; }
      .hero { padding: 48px 40px 32px; }
      .hero .amount { font-size: 64px; }
      .hero .hero-summary { gap: 64px; }
      .section { padding: 0 40px; }
      .date-filter { padding: 24px 40px; }
      .report-card { margin: 0 40px 24px; }
      .back-link { margin: 0 40px 24px; }
    }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
  </style>
</head>
<body>
  <div id="app">
    <div class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile'); ?>" aria-label="Back"><i class="fa fa-arrow-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= $page_title; ?></h1>
        </div>
        <span></span>
      </div>

      <div class="date-filter">
        <form method="get" action="<?= base_url('reports/cash_in_hand'); ?>" class="date-form">
          <input type="date" name="date" value="<?= $selected_date; ?>" max="<?= date('Y-m-d'); ?>" required>
          <button type="submit">Go</button>
        </form>
      </div>

      <div class="hero">
        <div class="label">Net cash in hand</div>
        <div class="amount"><?= $cash_in_hand; ?></div>
        <div class="hero-summary">
          <div>
            <span class="mini-label">Cash In</span>
            <span class="mini-amount in"><?= $cash_in_total; ?></span>
          </div>
          <div>
            <span class="mini-label">Cash Out</span>
            <span class="mini-amount out"><?= $cash_out_total; ?></span>
          </div>
        </div>
        <div class="updated"><?= $updated_at; ?></div>
      </div>

      <div class="section">
        <div class="section-title">Cash movement breakdown</div>
        <div class="card">
          <div class="breakdown-list">
            <?php foreach($breakdown as $row): ?>
              <div class="breakdown-row <?= $row['type']; ?>">
                <div class="left">
                  <div class="icon"><i class="fa fa-<?= $row['type'] === 'in' ? 'arrow-down' : 'arrow-up'; ?>"></i></div>
                  <div class="label"><?= $row['label']; ?></div>
                </div>
                <div class="amount"><?= $row['amount']; ?></div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <a href="<?= base_url('mobile/report/cash_flow'); ?>" class="report-card">
        <span>View Cash Flow Report</span>
        <i class="fa fa-arrow-right"></i>
      </a>

      <a href="<?= base_url('mobile'); ?>" class="back-link"><i class="fa fa-home"></i> Back to Dashboard</a>
    </div>
    <?php $this->load->view('mobile/bottom_nav', ['active' => 'home']); ?>
  </div>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
