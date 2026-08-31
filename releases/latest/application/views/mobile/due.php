<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= $SITE_TITLE ?? 'MartPoint'; ?> — Due Payments</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-danger: #EF4444; --mp-success: #10B981; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0; }
    .summary-card { background: linear-gradient(135deg, var(--mp-danger) 0%, #B91C1C 100%); border-radius: 16px; padding: 20px; color: #fff; margin-bottom: 16px; }
    .summary-card .label { font-size: 13px; opacity: 0.9; margin-bottom: 6px; }
    .summary-card .value { font-size: 28px; font-weight: 700; }
    .card { background: #fff; border-radius: 14px; padding: 12px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .due-item { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--mp-border); }
    .due-item:last-child { border-bottom: none; }
    .due-item .left { flex: 1; }
    .due-item .name { font-weight: 600; font-size: 15px; }
    .due-item .desc { font-size: 12px; color: var(--mp-muted); margin-top: 3px; }
    .due-item .amount { font-weight: 700; color: var(--mp-danger); font-size: clamp(13px, 4vw, 15px); word-break: keep-all; overflow-wrap: break-word; }
    .due-item .pay-btn { margin-left: 10px; padding: 8px 14px; border-radius: 10px; background: var(--mp-primary); color: #fff; text-decoration: none; font-size: 13px; font-weight: 600; }
    .empty-state { text-align: center; padding: 40px 20px; color: var(--mp-muted); }
    .empty-state i { font-size: 48px; margin-bottom: 12px; display: block; color: var(--mp-border); }
    .bottom-nav { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 430px; background: #fff; border-top: 1px solid var(--mp-border); display: flex; justify-content: space-around; padding: 8px 0 calc(8px + var(--safe-bottom)); z-index: 1000; }
    .nav-item { display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 6px 14px; border: none; background: transparent; color: var(--mp-muted); font-size: 10px; font-weight: 500; text-decoration: none; }
    .nav-item .icon { font-size: 20px; }
    .nav-item.active { color: var(--mp-primary); }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .bottom-nav { max-width: 100%; left: 0; right: 0; transform: none; } .screen { padding: 16px 16px 100px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 120px; } }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Due Payments</h1>
        </div>
      </div>

      <div class="summary-card">
        <div class="label">Total Due</div>
        <div class="value" title="<?= strip_tags(mp_format_money($total_due)); ?>"><?= mp_format_money_compact($total_due); ?></div>
      </div>

      <div class="card">
        <?php if(!empty($due_list)): ?>
          <?php foreach($due_list as $row): ?>
            <div class="due-item">
              <div class="left">
                <div class="name"><?= !empty($row->customer_name) ? $row->customer_name : 'Walk-in Customer'; ?></div>
                <div class="desc"><?= $row->sales_code; ?> · Due <?= !empty($row->due_date) ? date('M j, Y', strtotime($row->due_date)) : 'N/A'; ?></div>
              </div>
              <div style="text-align:right">
                <div class="amount"><?= mp_format_money($row->due); ?></div>
              </div>
              <a href="<?= base_url('mobile/pay_due/' . $row->id); ?>" class="pay-btn">Pay</a>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-state">
            <i class="fa fa-check-circle"></i>
            <div>No due payments. All caught up!</div>
          </div>
        <?php endif; ?>
      </div>
    </section>


  </div>
  <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
