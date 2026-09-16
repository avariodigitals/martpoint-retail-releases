<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Butchery</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-info: #3B82F6; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 120px; }
    .topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .kpi-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 16px; }
    .kpi-card { padding: 16px 10px; border-radius: 16px; text-align: center; text-decoration: none; color: var(--mp-ink); }
    .kpi-card.green { background: #ECFDF5; }
    .kpi-card.yellow { background: #FFFBEB; }
    .kpi-card.blue { background: #EFF6FF; }
    .kpi-card .label { font-size: 11px; color: var(--mp-muted); margin-bottom: 6px; text-transform: uppercase; font-weight: 600; }
    .kpi-card .value { font-size: 22px; font-weight: 700; color: var(--mp-ink); }
    .section-title { font-size: 14px; font-weight: 600; color: var(--mp-muted); margin: 20px 0 10px; text-transform: uppercase; }
    .carcass-card { background: #fff; border: 1px solid var(--mp-border); border-radius: 14px; padding: 12px; margin-bottom: 10px; }
    .carcass-card .title { font-size: 15px; font-weight: 700; margin-bottom: 4px; }
    .carcass-card .meta { font-size: 12px; color: var(--mp-muted); margin-bottom: 6px; }
    .carcass-card .badges { display: flex; justify-content: space-between; align-items: center; margin-top: 10px; }
    .badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 600; }
    .badge-success { background: #ECFDF5; color: #065F46; }
    .badge-info { background: #EFF6FF; color: #1E40AF; }
    .btn-small { padding: 8px 14px; border-radius: 10px; background: var(--mp-primary); color: #fff; text-decoration: none; font-size: 13px; font-weight: 600; }
    .empty-state { text-align: center; padding: 40px 20px; color: var(--mp-muted); }
    .fab { position: fixed; bottom: calc(72px + var(--safe-bottom)); right: 16px; width: 56px; height: 56px; border-radius: 50%; background: var(--mp-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 24px; text-decoration: none; box-shadow: 0 6px 16px rgba(0,87,255,0.3); z-index: 50; }
    .share-card { display: flex; justify-content: space-between; align-items: center; background: #fff; border: 1px solid var(--mp-border); border-radius: 14px; padding: 12px; margin-bottom: 10px; }
    .share-card .customer { font-weight: 600; font-size: 14px; }
    .share-card .fraction { font-size: 12px; color: var(--mp-muted); }
    @media (min-width: 430px) { .fab { right: calc(50% - 199px); } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Butchery</h1>
        </div>
        <a href="<?= base_url('mobile/butchery_coldchain'); ?>" style="color:var(--mp-primary); font-size:13px; font-weight:600;">Cold Chain</a>
      </div>

      <div class="kpi-grid">
        <div class="kpi-card green">
          <div class="label">Received</div>
          <div class="value"><?= number_format($received_count ?? 0); ?></div>
        </div>
        <div class="kpi-card yellow">
          <div class="label">Completed</div>
          <div class="value"><?= number_format($completed_count ?? 0); ?></div>
        </div>
        <div class="kpi-card blue">
          <div class="label">Shares</div>
          <div class="value"><?= number_format($shares_count ?? 0); ?></div>
        </div>
      </div>

      <div class="section-title">Recent Carcasses</div>
      <?php if (!empty($carcasses)): ?>
        <?php foreach ($carcasses as $c): ?>
        <div class="carcass-card">
          <div class="title"><?= htmlspecialchars($c->carcass_name); ?></div>
          <div class="meta"><?= htmlspecialchars($c->supplier_name ?: '---'); ?> · Lot <?= htmlspecialchars($c->lot_number ?: '---'); ?> · <?= number_format($c->receiving_weight, 2); ?> kg</div>
          <div class="badges">
            <span class="badge badge-<?= $c->status === 'completed' ? 'success' : 'info'; ?>"><?= ucfirst($c->status); ?></span>
            <?php if ($c->status !== 'completed'): ?>
            <a href="<?= base_url('mobile/butchery_cut/' . $c->id); ?>" class="btn-small"><i class="fa fa-cut"></i> Cut</a>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state">No carcasses yet. Tap + to receive one.</div>
      <?php endif; ?>

      <?php if (!empty($shares)): ?>
      <div class="section-title">Recent Shares</div>
        <?php foreach (array_slice($shares, 0, 5) as $s): ?>
        <div class="share-card">
          <div>
            <div class="customer"><?= htmlspecialchars($s->customer_name ?: '---'); ?></div>
            <div class="fraction"><?= htmlspecialchars($s->share_fraction); ?> · <?= number_format($s->reserved_weight, 2); ?> kg</div>
          </div>
          <span class="badge badge-<?= $s->status === 'delivered' ? 'success' : 'info'; ?>"><?= ucfirst($s->status); ?></span>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>

    <a href="<?= base_url('mobile/butchery_form'); ?>" class="fab">+</a>
  </div>

  <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
