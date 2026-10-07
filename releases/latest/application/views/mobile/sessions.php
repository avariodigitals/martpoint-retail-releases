<?php $CI =& get_instance(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?> — Sessions</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-teal: #0D9488; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-ink: #1E293B; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-danger: #EF4444; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: var(--mp-bg); text-decoration: none; }
    .topbar-titles { flex: 1; min-width: 0; }
    .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .ss-card { background: #fff; border-radius: 14px; border: 1px solid var(--mp-border); padding: 12px 14px; margin-bottom: 10px; }
    .ss-name { font-size: 15px; font-weight: 700; }
    .ss-meta { font-size: 11px; color: var(--mp-muted); margin-top: 4px; line-height: 1.6; }
    .ss-badge { font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 10px; }
    .b-scheduled { background: #E0E7FF; color: #4338CA; } .b-checked_in { background: #FEF3C7; color: #92400E; }
    .b-in_progress { background: #DBEAFE; color: #1E40AF; } .b-completed { background: #DCFCE7; color: #166534; }
    .b-cancelled, .b-no_show { background: #FEE2E2; color: #991B1B; } .b-interrupted { background: #FFEDD5; color: #9A3412; }
    .ss-acts { display: flex; gap: 8px; margin-top: 10px; flex-wrap: wrap; }
    .ss-acts button, .ss-acts a { flex: 1; min-width: 80px; padding: 10px; border-radius: 10px; border: 1px solid var(--mp-border); background: var(--mp-bg); font-size: 12px; font-weight: 700; cursor: pointer; font-family: inherit; text-align: center; text-decoration: none; color: var(--mp-ink); }
    .ss-acts button.primary { background: var(--mp-teal); color: #fff; border-color: var(--mp-teal); }
    .empty { text-align: center; padding: 50px 24px; color: var(--mp-muted); font-size: 14px; }
  </style>
</head>
<body>
<div id="app">
  <div class="screen">
    <div class="topbar">
      <a class="back" href="<?= base_url('mobile/more'); ?>"><i class="fa fa-arrow-left"></i></a>
      <div class="topbar-titles">
        <div class="store-name"><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?></div>
        <h1>Sessions</h1>
      </div>
    </div>

    <?php if(empty($sessions)): ?>
      <div class="empty"><i class="fa fa-clock-o" style="font-size:30px;display:block;margin-bottom:10px;"></i>No sessions scheduled.</div>
    <?php endif; ?>

    <?php foreach($sessions as $s): ?>
    <div class="ss-card">
      <div class="ss-name"><?= htmlspecialchars($s->patient_name); ?>
        <span class="ss-badge b-<?= $s->status; ?>"><?= strtoupper(str_replace('_',' ',$s->status)); ?></span></div>
      <div class="ss-meta">
        <?= htmlspecialchars($s->patient_code); ?> · Session <?= (int)$s->session_no; ?> of <?= (int)$s->units_total; ?><br>
        <?= htmlspecialchars($s->scheduled_at); ?> · Fee <?= $CI->currency($s->fee); ?><?= $s->fee_posted ? ' · fee posted' : ''; ?>
      </div>
      <div class="ss-acts">
        <?php if($s->status === 'scheduled' && $can['checkin']): ?>
          <button class="primary" onclick="sact(<?= (int)$s->id; ?>,'checkin')">Check in</button>
        <?php endif; ?>
        <?php if($s->status === 'checked_in' && $can['checkin']): ?>
          <button class="primary" onclick="sact(<?= (int)$s->id; ?>,'start')">Start</button>
        <?php endif; ?>
        <?php if(in_array($s->status,['in_progress','interrupted']) && $can['complete']): ?>
          <button class="primary" onclick="sact(<?= (int)$s->id; ?>,'complete')">Complete</button>
        <?php endif; ?>
        <?php if(in_array($s->status,['scheduled','checked_in']) && $can['checkin']): ?>
          <button onclick="sns(<?= (int)$s->id; ?>)">No-show</button>
        <?php endif; ?>
        <a href="<?= base_url('sessions/ticket/' . $s->id); ?>" target="_blank">Ticket</a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
</div>
<?php $this->load->view('mobile/chat'); ?>
<script>
var CSRF = {'<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'};
function sact(id, to){
  fetch('<?= base_url('mobile/session_action/'); ?>' + id, {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: new URLSearchParams(Object.assign({to: to}, CSRF))
  }).then(function(r){ return r.json(); }).then(function(x){ alert(x.message); if(x.status==='success') location.reload(); });
}
function sns(id){
  var r = prompt('No-show reason:'); if(!r) return;
  fetch('<?= base_url('mobile/session_action/'); ?>' + id, {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: new URLSearchParams(Object.assign({to: 'no_show', reason: r}, CSRF))
  }).then(function(x){ return x.json(); }).then(function(x){ alert(x.message); if(x.status==='success') location.reload(); });
}
</script>
</body>
</html>
