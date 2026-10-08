<?php
/**
 * Mobile — Printing Jobs list
 * Status filter chips reuse the desktop production_status vocabulary.
 */
$store_title = htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint');
$status_labels = ['planned' => 'Planned', 'in_progress' => 'In Progress', 'on_hold' => 'On Hold', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
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
    :root { --mp-primary:#0e7490; --mp-bg:#F5F4F0; --mp-surface:#FFFFFF; --mp-text:#292524; --mp-muted:#78716C; --mp-border:#E7E5E4; --mp-danger:#DC2626; --mp-warning:#F59E0B; --mp-ink:#44403C; }
    * { box-sizing:border-box; }
    html, body { margin:0; padding:0; font-family:Inter,-apple-system,BlinkMacSystemFont,sans-serif; background:var(--mp-bg); color:var(--mp-text); height:100%; overscroll-behavior:none; -webkit-tap-highlight-color:transparent; }
    #app { max-width:430px; margin:0 auto; background:var(--mp-surface); min-height:100vh; position:relative; }
    .screen { padding:12px 12px 130px; }
    .topbar { display:flex; align-items:center; gap:12px; margin-bottom:12px; padding-top:8px; }
    .topbar .back { color:var(--mp-primary); font-size:20px; text-decoration:none; width:36px; height:36px; display:flex; align-items:center; justify-content:center; border-radius:10px; background:var(--mp-bg); }
    .topbar .topbar-titles { flex:1; min-width:0; }
    .topbar .store-name { font-size:11px; color:var(--mp-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:2px; }
    .topbar h1 { font-size:20px; font-weight:700; margin:0; }
    /* in-app filter chips — NOT a native <select>, per AGENTS.md */
    .chips { display:flex; gap:8px; overflow-x:auto; padding:2px 2px 10px; -webkit-overflow-scrolling:touch; }
    .chips::-webkit-scrollbar { display:none; }
    .chip { flex:0 0 auto; padding:7px 13px; border-radius:999px; background:#fff; border:1px solid var(--mp-border); font-size:12.5px; font-weight:600; color:var(--mp-ink); text-decoration:none; white-space:nowrap; }
    .chip.on { background:var(--mp-primary); border-color:var(--mp-primary); color:#fff; }
    .chip .n { opacity:.7; margin-left:4px; }
    .card { background:var(--mp-surface); border:1px solid var(--mp-border); border-radius:14px; padding:14px; margin-bottom:10px; box-shadow:0 1px 3px rgba(0,0,0,0.04); text-decoration:none; color:var(--mp-ink); display:block; }
    .card.od { border-left:3px solid var(--mp-danger); }
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
    .count-note { font-size:12px; color:var(--mp-muted); margin:2px 0 10px; }
    @media (min-width:600px){ #app{max-width:100%;margin:0;} .screen{padding:16px 16px 130px;} }
    @media (min-width:1024px){ .screen{padding:24px 48px 150px;} }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/printing'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= $store_title ?></div>
          <h1><?= htmlspecialchars($page_title); ?></h1>
        </div>
      </div>

      <div class="chips">
        <a class="chip <?= $active_status === 'all' ? 'on' : ''; ?>" href="<?= base_url('mobile/printing_jobs/all'); ?>">All <span class="n"><?= (int)$total_all; ?></span></a>
        <?php foreach($statuses as $s): ?>
        <a class="chip <?= $active_status === $s ? 'on' : ''; ?>" href="<?= base_url('mobile/printing_jobs/'.$s); ?>"><?= htmlspecialchars($status_labels[$s] ?? ucwords(str_replace('_',' ',$s))); ?> <span class="n"><?= (int)($counts[$s] ?? 0); ?></span></a>
        <?php endforeach; ?>
      </div>

      <?php if(!empty($jobs)): ?>
        <div class="count-note"><?= count($jobs); ?> job<?= count($jobs) === 1 ? '' : 's'; ?></div>
        <?php foreach($jobs as $j): ?>
        <?php $od = !empty($j->due_date) && strtotime($j->due_date) < strtotime(date('Y-m-d')) && !in_array($j->production_status, ['completed','cancelled'], true); ?>
        <a class="card <?= $od ? 'od' : ''; ?>" href="<?= base_url('mobile/printing_job/'.$j->id); ?>">
          <div class="top">
            <div style="min-width:0;">
              <div class="nm"><?= htmlspecialchars($j->job_code); ?></div>
              <div class="sub"><?= htmlspecialchars($j->customer_name ?: 'Walk-in'); ?></div>
              <div class="sub">
                <?php if(!empty($j->due_date)): ?>
                  <i class="fa fa-calendar-o"></i> <?= show_date($j->due_date); ?><?= $od ? ' · <span style="color:var(--mp-danger);font-weight:700;">overdue</span>' : ''; ?>
                <?php else: ?>
                  <span style="color:var(--mp-muted);">No due date</span>
                <?php endif; ?>
              </div>
            </div>
            <span class="badge badge-<?= htmlspecialchars($j->production_status); ?>"><?= htmlspecialchars($status_labels[$j->production_status] ?? ucwords(str_replace('_',' ',$j->production_status))); ?></span>
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
        <div class="empty-state"><i class="fa fa-print"></i><div>No jobs in this status.</div></div>
      <?php endif; ?>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
