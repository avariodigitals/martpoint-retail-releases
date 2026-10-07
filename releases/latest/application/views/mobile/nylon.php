<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?> — <?= htmlspecialchars($page_title); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: Inter, -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 130px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 10px; background: var(--mp-bg); }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0; }
    .mode-line { font-size: 12px; color: var(--mp-muted); margin: -4px 0 14px; }
    .stat-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-bottom: 14px; }
    .stat { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px 10px; text-align: center; text-decoration: none; color: var(--mp-ink); }
    .stat .v { font-size: 22px; font-weight: 700; display: block; }
    .stat .l { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.3px; }
    .stat.warn .v { color: var(--mp-warning); }
    .stat.bad .v { color: var(--mp-danger); }
    .section-label { font-size: 12px; font-weight: 700; color: var(--mp-muted); text-transform: uppercase; letter-spacing: 0.4px; margin: 18px 0 8px; }
    .link-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .link-card { background: #fff; border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; text-decoration: none; color: var(--mp-ink); display: flex; align-items: center; gap: 12px; }
    .link-card .ic { width: 38px; height: 38px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; flex-shrink: 0; }
    .link-card .t { font-size: 13px; font-weight: 700; line-height: 1.25; }
    .ic-teal { background: #CCFBF1; color: #0F766E; }
    .ic-blue { background: #EFF6FF; color: #2563EB; }
    .ic-purple { background: #F3E8FF; color: #7C3AED; }
    .ic-orange { background: #FFEDD5; color: #EA580C; }
    .card { background: var(--mp-surface); border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; margin-bottom: 10px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); text-decoration: none; color: var(--mp-ink); display: block; }
    .card .top { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
    .nm { font-size: 14px; font-weight: 700; }
    .sub { font-size: 12px; color: var(--mp-muted); margin-top: 2px; }
    .badge { display: inline-block; padding: 3px 10px; border-radius: 12px; font-size: 11px; font-weight: 700; white-space: nowrap; }
    .badge-default { background: #E2E8F0; color: #475569; }
    .badge-info { background: #DBEAFE; color: #1D4ED8; }
    .badge-warning { background: #FEF3C7; color: #B45309; }
    .badge-primary { background: #E0E7FF; color: #4338CA; }
    .badge-success { background: #D1FAE5; color: #047857; }
    .badge-danger { background: #FEE2E2; color: #B91C1C; }
    .mat-row { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--mp-border); font-size: 13px; }
    .mat-row:last-child { border-bottom: none; }
    .mat-row .avail { font-weight: 700; }
    .mat-row .low { color: var(--mp-danger); }
    .empty-state { text-align: center; padding: 30px 20px; color: var(--mp-muted); font-size: 13px; }
    .empty-state i { font-size: 36px; margin-bottom: 10px; display: block; color: var(--mp-border); }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 130px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 150px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/operations'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= htmlspecialchars($page_title); ?></h1>
        </div>
      </div>
      <div class="mode-line"><i class="fa fa-industry"></i> <?= htmlspecialchars($mode_label); ?></div>

      <div class="stat-grid">
        <a class="stat" href="<?= base_url('mobile/nylon_jobs'); ?>"><span class="v"><?= (int)$stats['open_jobs']; ?></span><span class="l">Open Jobs</span></a>
        <a class="stat <?= $stats['due_soon'] > 0 ? 'warn' : ''; ?>" href="<?= base_url('mobile/nylon_jobs'); ?>"><span class="v"><?= (int)$stats['due_soon']; ?></span><span class="l">Due ≤ 7d</span></a>
        <a class="stat <?= $stats['pending_qc'] > 0 ? 'bad' : ''; ?>" href="<?= base_url('mobile/nylon_jobs'); ?>"><span class="v"><?= (int)$stats['pending_qc']; ?></span><span class="l">Pending QC</span></a>
      </div>

      <div class="link-grid">
        <a href="<?= base_url('mobile/nylon_jobs'); ?>" class="link-card"><div class="ic ic-teal"><i class="fa fa-industry"></i></div><div class="t">Production Jobs</div></a>
        <?php if(!empty($can_jobs)): ?>
        <a href="<?= base_url('mobile/nylon_job_form'); ?>" class="link-card"><div class="ic ic-blue"><i class="fa fa-plus"></i></div><div class="t">New Job</div></a>
        <?php endif; ?>
        <?php if(!empty($can_orders)): ?>
        <a href="<?= base_url('mobile/nylon_orders'); ?>" class="link-card"><div class="ic ic-purple"><i class="fa fa-pencil-square-o"></i></div><div class="t">Job Orders</div></a>
        <?php endif; ?>
        <a href="<?= base_url('mobile/nylon_products'); ?>" class="link-card"><div class="ic ic-orange"><i class="fa fa-cubes"></i></div><div class="t">Materials &amp; Products</div></a>
        <a href="<?= base_url('mobile/nylon_machines'); ?>" class="link-card"><div class="ic ic-blue"><i class="fa fa-cogs"></i></div><div class="t">Machines</div></a>
      </div>

      <div class="section-label">Material Availability</div>
      <div class="card" style="padding:4px 14px;">
        <?php if(!empty($materials)): ?>
          <?php foreach($materials as $m): ?>
          <div class="mat-row">
            <span><?= htmlspecialchars($m->item_name); ?> <span style="color:var(--mp-muted);font-size:11px;"><?= $m->item_class === 'film_roll' ? '· roll' : ''; ?></span></span>
            <span class="avail <?= $m->available <= 0 ? 'low' : ''; ?>"><?= rtrim(rtrim(number_format((float)$m->available, 2), '0'), '.'); ?> <?= htmlspecialchars($m->unit_name ?: ''); ?></span>
          </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-state" style="padding:20px;"><i class="fa fa-cubes"></i><div>No materials or film rolls configured yet.</div></div>
        <?php endif; ?>
      </div>

      <div class="section-label">Open Jobs</div>
      <?php if(!empty($open_jobs)): ?>
        <?php foreach($open_jobs as $j): ?>
        <a class="card" href="<?= base_url('mobile/nylon_job/'.$j->id); ?>">
          <div class="top">
            <div>
              <div class="nm"><?= htmlspecialchars($j->job_code); ?></div>
              <div class="sub"><?= htmlspecialchars($j->item_name ?: 'Product'); ?><?= $j->customer_name ? ' · '.htmlspecialchars($j->customer_name) : ''; ?></div>
              <div class="sub"><?= rtrim(rtrim(number_format((float)$j->planned_qty, 2), '0'), '.'); ?> planned<?= $j->due_date ? ' · due '.show_date($j->due_date) : ''; ?></div>
            </div>
            <span class="badge badge-<?= Nylon_model::job_status_badge($j->status); ?>"><?= htmlspecialchars(Nylon_model::job_status_label($j->status)); ?></span>
          </div>
        </a>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state"><i class="fa fa-industry"></i><div>No open production jobs.</div></div>
      <?php endif; ?>

      <?php if(!empty($balances)): ?>
      <div class="section-label">Outstanding Balances</div>
      <?php foreach(array_slice($balances, 0, 5) as $b): ?>
      <a class="card" href="<?= base_url('mobile/nylon_order/'.$b->id); ?>">
        <div class="top">
          <div>
            <div class="nm"><?= htmlspecialchars($b->order_code); ?></div>
            <div class="sub"><?= htmlspecialchars($b->customer_name ?: ''); ?></div>
          </div>
          <span class="badge badge-warning"><?= store_number_format($b->balance_due); ?></span>
        </div>
      </a>
      <?php endforeach; ?>
      <?php endif; ?>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
