<?php
/**
 * Mobile — Printing Quotations
 * Lists print jobs that have a linked quotation, with its status and expiry.
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
    :root { --mp-primary:#0e7490; --mp-bg:#F5F4F0; --mp-surface:#FFFFFF; --mp-text:#292524; --mp-muted:#78716C; --mp-border:#E7E5E4; --mp-danger:#DC2626; --mp-warning:#F59E0B; --mp-success:#059669; --mp-ink:#44403C; }
    * { box-sizing:border-box; }
    html, body { margin:0; padding:0; font-family:Inter,-apple-system,BlinkMacSystemFont,sans-serif; background:var(--mp-bg); color:var(--mp-text); height:100%; overscroll-behavior:none; -webkit-tap-highlight-color:transparent; }
    #app { max-width:430px; margin:0 auto; background:var(--mp-surface); min-height:100vh; position:relative; }
    .screen { padding:12px 12px 130px; }
    .topbar { display:flex; align-items:center; gap:12px; margin-bottom:12px; padding-top:8px; }
    .topbar .back { color:var(--mp-primary); font-size:20px; text-decoration:none; width:36px; height:36px; display:flex; align-items:center; justify-content:center; border-radius:10px; background:var(--mp-bg); }
    .topbar .topbar-titles { flex:1; min-width:0; }
    .topbar .store-name { font-size:11px; color:var(--mp-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:2px; }
    .topbar h1 { font-size:20px; font-weight:700; margin:0; }
    .card { background:var(--mp-surface); border:1px solid var(--mp-border); border-radius:14px; padding:14px; margin-bottom:10px; box-shadow:0 1px 3px rgba(0,0,0,0.04); text-decoration:none; color:var(--mp-ink); display:block; }
    .card .top { display:flex; justify-content:space-between; align-items:flex-start; gap:10px; }
    .nm { font-size:14px; font-weight:700; }
    .sub { font-size:12px; color:var(--mp-muted); margin-top:2px; }
    .badge { display:inline-block; padding:3px 10px; border-radius:12px; font-size:11px; font-weight:700; white-space:nowrap; }
    .badge-draft    { background:#E7E5E4; color:#57534E; }
    .badge-sent     { background:#DBEAFE; color:#1D4ED8; }
    .badge-accepted { background:#D1FAE5; color:#047857; }
    .badge-converted{ background:#E0E7FF; color:#4338CA; }
    .badge-expired  { background:#FEE2E2; color:#B91C1C; }
    .badge-declined { background:#FEE2E2; color:#B91C1C; }
    .badge-none     { background:#F5F4F0; color:#78716C; }
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

      <?php if(!empty($quotes)): ?>
        <div class="count-note"><?= count($quotes); ?> quotation<?= count($quotes) === 1 ? '' : 's'; ?></div>
        <?php foreach($quotes as $q): ?>
        <?php
          // db_quotation.quotation_status stores the DOC TYPE ("Quotation");
          // the real state is lifecycle_status (issued/sent/accepted/…).
          $qs = strtolower($q['lifecycle_status'] ?? 'none');
          $days = $q['days_to_expiry'];
        ?>
        <a class="card" href="<?= base_url('mobile/printing_job/'.$q['job_id']); ?>">
          <div class="top">
            <div style="min-width:0;">
              <div class="nm"><?= htmlspecialchars($q['quotation_code'] ?: $q['job_code']); ?></div>
              <div class="sub"><?= htmlspecialchars($q['customer_name'] ?: 'Walk-in'); ?></div>
              <div class="sub"><?= store_number_format($q['grand_total']); ?>
                <?php if($days !== null): ?>
                  <?php if($days < 0): ?>
                    · <span style="color:var(--mp-danger);font-weight:700;">expired</span>
                  <?php elseif($days <= 3): ?>
                    · <span style="color:var(--mp-warning);font-weight:700;">expires in <?= (int)$days; ?>d</span>
                  <?php else: ?>
                    · expires <?= show_date($q['expire_date']); ?>
                  <?php endif; ?>
                <?php endif; ?>
              </div>
            </div>
            <span class="badge badge-<?= htmlspecialchars($qs); ?>"><?= htmlspecialchars(ucwords(str_replace('_',' ',$qs))); ?></span>
          </div>
        </a>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty-state"><i class="fa fa-quote-left"></i><div>No quotations raised yet.</div></div>
      <?php endif; ?>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
