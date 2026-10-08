<?php
/**
 * Mobile — Printing Hub
 * =====================
 * Print-shop home for phones: KPI strip, quick links and the jobs needing
 * attention, mirroring the Nylon Factory mobile pattern.
 *
 * Read-first by design. Stage ACTIONS live on mobile/printing_job so the
 * approval/artwork/deposit gates stay server-enforced in Printing_model.
 */
$store_title = htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= $store_title ?> — <?= htmlspecialchars($page_title); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { /* Printing workspace palette — matches desktop print_layout/mp_header. */
            --mp-primary:#0e7490; --mp-primary-rgb:14,116,144; --mp-primary-dark:#0b5d73;
            --mp-bg:#F5F4F0; --mp-surface:#FFFFFF; --mp-text:#292524; --mp-ink:#44403C;
            --mp-muted:#78716C; --mp-border:#E7E5E4; --mp-success:#059669; --mp-danger:#DC2626; --mp-warning:#F59E0B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin:0; padding:0; font-family:Inter,-apple-system,BlinkMacSystemFont,sans-serif; background:var(--mp-bg); color:var(--mp-text); height:100%; overscroll-behavior:none; -webkit-tap-highlight-color:transparent; }
    #app { max-width:430px; margin:0 auto; background:var(--mp-surface); min-height:100vh; position:relative; }
    .screen { padding:12px 12px 130px; }
    .topbar { display:flex; align-items:center; gap:12px; margin-bottom:12px; padding-top:8px; }
    .topbar .back { color:var(--mp-primary); font-size:20px; text-decoration:none; width:36px; height:36px; display:flex; align-items:center; justify-content:center; border-radius:10px; background:var(--mp-bg); }
    .topbar .topbar-titles { flex:1; min-width:0; }
    .topbar .store-name { font-size:11px; color:var(--mp-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:2px; }
    .topbar h1 { font-size:20px; font-weight:700; margin:0; }
    .mode-line { font-size:12px; color:var(--mp-muted); margin:-4px 0 14px; }
    .stat-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:10px; margin-bottom:10px; }
    .stat { background:var(--mp-surface); border:1px solid var(--mp-border); border-radius:14px; padding:14px 10px; text-align:center; text-decoration:none; color:var(--mp-ink); }
    .stat .v { font-size:22px; font-weight:700; display:block; }
    .stat .l { font-size:11px; color:var(--mp-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.3px; }
    .stat.warn .v { color:var(--mp-warning); }
    .stat.bad  .v { color:var(--mp-danger); }
    .section-label { font-size:12px; font-weight:700; color:var(--mp-muted); text-transform:uppercase; letter-spacing:0.4px; margin:18px 0 8px; }
    .link-grid { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
    .link-card { background:#fff; border:1px solid var(--mp-border); border-radius:14px; padding:14px; text-decoration:none; color:var(--mp-ink); display:flex; align-items:center; gap:12px; }
    .link-card .ic { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:16px; flex-shrink:0; }
    .link-card .t { font-size:13px; font-weight:700; line-height:1.25; }
    .ic-indigo { background:#E0E7FF; color:#4338CA; }
    .ic-blue   { background:#EFF6FF; color:#2563EB; }
    .ic-purple { background:#F3E8FF; color:#7C3AED; }
    .ic-orange { background:#FFEDD5; color:#EA580C; }
    .ic-teal   { background:#CCFBF1; color:#0F766E; }
    .card { background:var(--mp-surface); border:1px solid var(--mp-border); border-radius:14px; padding:14px; margin-bottom:10px; box-shadow:0 1px 3px rgba(0,0,0,0.04); text-decoration:none; color:var(--mp-ink); display:block; }
    .card .top { display:flex; justify-content:space-between; align-items:flex-start; gap:10px; }
    .nm { font-size:14px; font-weight:700; }
    .sub { font-size:12px; color:var(--mp-muted); margin-top:2px; }
    .badge { display:inline-block; padding:3px 10px; border-radius:12px; font-size:11px; font-weight:700; white-space:nowrap; }
    .badge-planned     { background:#E7E5E4; color:#57534E; }
    .badge-in_progress { background:#DBEAFE; color:#1D4ED8; }
    .badge-on_hold     { background:#FEF3C7; color:#B45309; }
    .badge-completed   { background:#D1FAE5; color:#047857; }
    .badge-cancelled   { background:#FEE2E2; color:#B91C1C; }
    .jt-gates { display:flex; gap:5px; margin-top:8px; flex-wrap:wrap; }
    .jt-gate { font-size:10px; font-weight:700; padding:2px 7px; border-radius:6px; background:#F5F4F0; color:#A8A29E; }
    .jt-gate.ok { background:#D1FAE5; color:#047857; }
    .empty-state { text-align:center; padding:30px 20px; color:var(--mp-muted); font-size:13px; }
    .empty-state i { font-size:36px; margin-bottom:10px; display:block; color:var(--mp-border); }
    @media (min-width:600px){ #app{max-width:100%;margin:0;} .screen{padding:16px 16px 130px;} }
    @media (min-width:1024px){ .screen{padding:24px 48px 150px;} }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/operations'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= $store_title ?></div>
          <h1><?= htmlspecialchars($page_title); ?></h1>
        </div>
      </div>
      <div class="mode-line"><i class="fa fa-print"></i> Print shop · jobs &amp; artwork</div>

      <div class="stat-grid">
        <a class="stat" href="<?= base_url('mobile/printing_jobs'); ?>"><span class="v"><?= (int)$kpi['open']; ?></span><span class="l">Open Jobs</span></a>
        <a class="stat" href="<?= base_url('mobile/printing_jobs/in_progress'); ?>"><span class="v"><?= (int)$kpi['in_production']; ?></span><span class="l">In Production</span></a>
        <a class="stat" href="<?= base_url('mobile/printing_jobs/completed'); ?>"><span class="v"><?= (int)$kpi['awaiting_fulfilment']; ?></span><span class="l">To Collect</span></a>
      </div>
      <div class="stat-grid">
        <a class="stat <?= $kpi['due_soon'] > 0 ? 'warn' : ''; ?>" href="<?= base_url('mobile/printing_jobs'); ?>"><span class="v"><?= (int)$kpi['due_soon']; ?></span><span class="l">Due ≤ 7d</span></a>
        <a class="stat <?= $kpi['overdue'] > 0 ? 'bad' : ''; ?>" href="<?= base_url('mobile/printing_jobs'); ?>"><span class="v"><?= (int)$kpi['overdue']; ?></span><span class="l">Overdue</span></a>
        <a class="stat <?= $kpi['awaiting_artwork'] > 0 ? 'warn' : ''; ?>" href="<?= base_url('mobile/printing_jobs'); ?>"><span class="v"><?= (int)$kpi['awaiting_artwork']; ?></span><span class="l">Await Art</span></a>
      </div>

      <div class="link-grid">
        <a href="<?= base_url('mobile/printing_jobs'); ?>" class="link-card"><div class="ic ic-indigo"><i class="fa fa-list-alt"></i></div><div class="t">All Jobs</div></a>
        <a href="<?= base_url('mobile/printing_quotations'); ?>" class="link-card"><div class="ic ic-purple"><i class="fa fa-quote-left"></i></div><div class="t">Quotations</div></a>
      </div>

      <?php if(!empty($kpi['outstanding_balance']) || !empty($kpi['collections_due'])): ?>
      <div class="section-label">Money</div>
      <div class="card">
        <div class="top">
          <div>
            <div class="nm"><?= store_number_format($kpi['outstanding_balance']); ?></div>
            <div class="sub">Outstanding balance on open jobs</div>
          </div>
          <span class="badge badge-on_hold"><?= store_number_format($kpi['collections_due']); ?> due</span>
        </div>
      </div>
      <?php endif; ?>

      <div class="section-label">Jobs Needing Attention</div>
      <?php if(!empty($attention)): ?>
        <?php foreach($attention as $j): ?>
        <a class="card" href="<?= base_url('mobile/printing_job/'.$j->id); ?>">
          <div class="top">
            <div>
              <div class="nm"><?= htmlspecialchars($j->job_code); ?></div>
              <div class="sub"><?= htmlspecialchars($j->customer_name ?: 'Walk-in'); ?><?= $j->due_date ? ' · due '.show_date($j->due_date) : ''; ?></div>
            </div>
            <span class="badge badge-<?= htmlspecialchars($j->production_status); ?>"><?= htmlspecialchars(ucwords(str_replace('_',' ',$j->production_status))) ?></span>
          </div>
          <div class="jt-gates">
            <span class="jt-gate <?= in_array($j->quotation_status,['accepted','converted'],true) ? 'ok' : ''; ?>">Quote</span>
            <span class="jt-gate <?= in_array($j->payment_status,['verified','paid'],true) ? 'ok' : ''; ?>">Pay</span>
            <span class="jt-gate <?= $j->artwork_status === 'approved' ? 'ok' : ''; ?>">Art</span>
            <span class="jt-gate <?= $j->design_status === 'cleared' ? 'ok' : ''; ?>">Des</span>
            <span class="jt-gate <?= $j->authorization_status === 'authorized' ? 'ok' : ''; ?>">Auth</span>
          </div>
        </a>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state"><i class="fa fa-print"></i><div>No print jobs need attention right now.</div></div>
      <?php endif; ?>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
