<?php
/**
 * Mobile — Printing Job detail
 * ============================
 * READ-ONLY on purpose for v1.
 *
 * The 7-stage strip comes from Printing_model::stage_progress(), which is the
 * SAME server-side source the desktop screen uses. Every state shown here is
 * derived from stored values (quotation/payment/artwork/design/auth/production/
 * fulfilment), so the phone can never present a gate as passed when the model
 * disagrees. Mutating actions stay on the desktop/tablet workflow for now;
 * adding them later must re-check in Printing_model, never in this view.
 */
$store_title = htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint');
$status_labels = ['planned' => 'Planned', 'in_progress' => 'In Progress', 'on_hold' => 'On Hold', 'completed' => 'Completed', 'cancelled' => 'Cancelled'];
$bal = max(0, (float)$job->quote_amount - (float)$net_paid);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= $store_title ?> — <?= htmlspecialchars($job->job_code); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { /* Printing workspace palette — matches desktop print_layout/mp_header. */
            --mp-primary:#0e7490; --mp-primary-rgb:14,116,144; --mp-primary-dark:#0b5d73;
            --mp-bg:#F5F4F0; --mp-surface:#FFFFFF; --mp-text:#292524; --mp-ink:#44403C;
            --mp-muted:#78716C; --mp-border:#E7E5E4; --mp-success:#059669; --mp-danger:#DC2626; --mp-warning:#F59E0B; }
    * { box-sizing:border-box; }
    html, body { margin:0; padding:0; font-family:Inter,-apple-system,BlinkMacSystemFont,sans-serif; background:var(--mp-bg); color:var(--mp-text); height:100%; overscroll-behavior:none; -webkit-tap-highlight-color:transparent; }
    #app { max-width:430px; margin:0 auto; background:var(--mp-surface); min-height:100vh; position:relative; }
    .screen { padding:12px 12px 130px; }
    .topbar { display:flex; align-items:center; gap:12px; margin-bottom:12px; padding-top:8px; }
    .topbar .back { color:var(--mp-primary); font-size:20px; text-decoration:none; width:36px; height:36px; display:flex; align-items:center; justify-content:center; border-radius:10px; background:var(--mp-bg); }
    .topbar .topbar-titles { flex:1; min-width:0; }
    .topbar .store-name { font-size:11px; color:var(--mp-muted); font-weight:600; text-transform:uppercase; letter-spacing:0.4px; margin-bottom:2px; }
    .topbar h1 { font-size:20px; font-weight:700; margin:0; }
    .card { background:var(--mp-surface); border:1px solid var(--mp-border); border-radius:14px; padding:14px; margin-bottom:10px; box-shadow:0 1px 3px rgba(0,0,0,0.04); }
    .section-label { font-size:12px; font-weight:700; color:var(--mp-muted); text-transform:uppercase; letter-spacing:0.4px; margin:18px 0 8px; }
    .row { display:flex; justify-content:space-between; gap:12px; padding:9px 0; border-bottom:1px solid var(--mp-border); font-size:13px; }
    .row:last-child { border-bottom:none; }
    .row .k { color:var(--mp-muted); }
    .row .v { font-weight:600; text-align:right; word-break:break-word; }
    .badge { display:inline-block; padding:3px 10px; border-radius:12px; font-size:11px; font-weight:700; white-space:nowrap; }
    .badge-planned     { background:#E7E5E4; color:#57534E; }
    .badge-in_progress { background:#DBEAFE; color:#1D4ED8; }
    .badge-on_hold     { background:#FEF3C7; color:#B45309; }
    .badge-completed   { background:#D1FAE5; color:#047857; }
    .badge-cancelled   { background:#FEE2E2; color:#B91C1C; }
    /* 7-stage strip */
    .steps { display:flex; flex-direction:column; gap:0; }
    .step { display:flex; gap:12px; align-items:flex-start; padding:10px 0; position:relative; }
    .step .dot { width:26px; height:26px; border-radius:50%; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; background:#F5F4F0; color:#A8A29E; border:1.5px solid #E7E5E4; }
    .step.done .dot { background:#D1FAE5; color:#047857; border-color:#A7F3D0; }
    .step.active .dot { background:#E0E7FF; color:#4338CA; border-color:#C7D2FE; }
    .step.blocked .dot { background:#FEE2E2; color:#B91C1C; border-color:#FECACA; }
    .step .body { flex:1; min-width:0; }
    .step .t { font-size:13.5px; font-weight:700; }
    .step .h { font-size:12px; color:var(--mp-muted); margin-top:2px; }
    .step .here { font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:.4px; color:#4338CA; background:#E0E7FF; border-radius:999px; padding:2px 8px; margin-left:6px; }
    .empty-state { text-align:center; padding:30px 20px; color:var(--mp-muted); font-size:13px; }
    .readonly-note { font-size:11.5px; color:var(--mp-muted); background:#F8FAFC; border:1px dashed var(--mp-border); border-radius:10px; padding:9px 11px; margin-top:14px; line-height:1.5; }
    @media (min-width:600px){ #app{max-width:100%;margin:0;} .screen{padding:16px 16px 130px;} }
    @media (min-width:1024px){ .screen{padding:24px 48px 150px;} }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/printing_jobs'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= $store_title ?></div>
          <h1><?= htmlspecialchars($job->job_code); ?></h1>
        </div>
      </div>

      <div class="card">
        <div class="row">
          <span class="k">Status</span>
          <span class="v"><span class="badge badge-<?= htmlspecialchars($job->production_status); ?>"><?= htmlspecialchars($status_labels[$job->production_status] ?? ucwords(str_replace('_',' ',$job->production_status))); ?></span></span>
        </div>
        <div class="row"><span class="k">Client</span><span class="v"><?= htmlspecialchars($job->customer_name ?: 'Walk-in'); ?></span></div>
        <?php if(!empty($job->mobile)): ?>
        <div class="row"><span class="k">Phone</span><span class="v"><a href="tel:<?= htmlspecialchars($job->mobile); ?>" style="color:var(--mp-primary);text-decoration:none;"><?= htmlspecialchars($job->mobile); ?></a></span></div>
        <?php endif; ?>
        <?php if(!empty($job->title)): ?>
        <div class="row"><span class="k">Job</span><span class="v"><?= htmlspecialchars($job->title); ?></span></div>
        <?php endif; ?>
        <?php if(!empty($job->due_date)): ?>
        <div class="row"><span class="k">Due</span><span class="v"><?= show_date($job->due_date); ?></span></div>
        <?php endif; ?>
        <div class="row"><span class="k">Quote</span><span class="v"><?= store_number_format($job->quote_amount); ?></span></div>
        <div class="row"><span class="k">Verified paid</span><span class="v"><?= store_number_format($net_paid); ?></span></div>
        <div class="row">
          <span class="k">Balance</span>
          <span class="v" style="<?= $bal > 0 ? 'color:var(--mp-danger);' : 'color:var(--mp-success);'; ?>"><?= store_number_format($bal); ?></span>
        </div>
      </div>

      <div class="section-label">Workflow Progress</div>
      <div class="card">
        <?php if(!empty($stages)): ?>
        <div class="steps">
          <?php foreach($stages as $s): ?>
          <?php
            $cls = 'pending';
            if(!empty($s['done'])) $cls = 'done';
            elseif(!empty($s['blocked'])) $cls = 'blocked';
            elseif(($s['state'] ?? '') === 'active') $cls = 'active';
            $icon = $cls === 'done' ? 'fa-check' : ($cls === 'blocked' ? 'fa-lock' : ($cls === 'active' ? 'fa-arrow-right' : 'fa-circle-o'));
          ?>
          <div class="step <?= $cls; ?>">
            <div class="dot"><i class="fa <?= $icon; ?>"></i></div>
            <div class="body">
              <div class="t">
                <?= htmlspecialchars($s['label']); ?>
                <?php if(($s['state'] ?? '') === 'active' && empty($s['done'])): ?><span class="here">current</span><?php endif; ?>
              </div>
              <div class="h"><?= htmlspecialchars($s['hint']); ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
          <div class="empty-state"><i class="fa fa-list-ol"></i><div>No workflow data.</div></div>
        <?php endif; ?>
      </div>

      <?php if(!empty($lines)): ?>
      <div class="section-label">Line Items</div>
      <?php foreach($lines as $l): ?>
      <div class="card">
        <div class="top" style="display:flex;justify-content:space-between;gap:10px;">
          <div style="min-width:0;">
            <div style="font-size:13.5px;font-weight:700;"><?= htmlspecialchars($l->description ?: ($l->category_key ?: 'Print item')); ?></div>
            <?php if(!empty($l->qty)): ?><div class="sub" style="font-size:12px;color:var(--mp-muted);margin-top:2px;">Qty <?= rtrim(rtrim(number_format((float)$l->qty, 2), '0'), '.'); ?></div><?php endif; ?>
          </div>
          <?php if(isset($l->line_total)): ?>
          <span style="font-weight:700;font-size:13px;white-space:nowrap;"><?= store_number_format($l->line_total); ?></span>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
      <?php endif; ?>

      <div class="readonly-note">
        <i class="fa fa-info-circle"></i>
        Progress is read-only on mobile. Stage approvals, artwork sign-off and payments
        must be recorded on the desktop/tablet workflow, where the required gates and
        approvals are enforced.
      </div>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
