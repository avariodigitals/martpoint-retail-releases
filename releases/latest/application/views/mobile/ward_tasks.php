<?php $CI =& get_instance(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?> — Ward Tasks</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-teal: #0D9488; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-ink: #1E293B; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-danger:#EF4444; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: var(--mp-bg); text-decoration: none; }
    .topbar-titles { flex: 1; min-width: 0; }
    .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .sec { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .5px; color: var(--mp-muted); margin: 16px 0 8px; }
    .tk-card { background: #fff; border-radius: 14px; border: 1px solid var(--mp-border); padding: 12px 14px; margin-bottom: 10px; }
    .tk-card.overdue { border-color: #FCA5A5; background: #FEF2F2; }
    .tk-name { font-size: 14px; font-weight: 700; }
    .tk-meta { font-size: 11px; color: var(--mp-muted); margin-top: 4px; line-height: 1.6; }
    .tk-badge { font-size: 10px; font-weight: 800; padding: 2px 8px; border-radius: 10px; text-transform: uppercase; }
    .b-open { background: #DBEAFE; color: #1E40AF; } .b-overdue { background: #FEE2E2; color: #991B1B; }
    .b-done, .b-completed { background: #DCFCE7; color: #166534; } .b-in_progress { background: #FEF3C7; color: #92400E; }
    .b-suppressed, .b-cancelled, .b-failed { background: #F1F5F9; color: #64748B; }
    .tk-acts { display: flex; gap: 8px; margin-top: 10px; }
    .tk-acts button { flex: 1; padding: 10px; border-radius: 10px; border: 1px solid var(--mp-border); background: var(--mp-bg); font-size: 12px; font-weight: 700; cursor: pointer; font-family: inherit; color: var(--mp-ink); }
    .tk-acts button.primary { background: var(--mp-teal); color: #fff; border-color: var(--mp-teal); }
    .empty { text-align: center; padding: 40px 24px; color: var(--mp-muted); font-size: 14px; }
  </style>
</head>
<body>
<div id="app">
  <div class="screen">
    <div class="topbar">
      <a class="back" href="<?= base_url('mobile/more'); ?>"><i class="fa fa-arrow-left"></i></a>
      <div class="topbar-titles">
        <div class="store-name"><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?></div>
        <h1>Ward Tasks</h1>
      </div>
    </div>

    <?php if(!empty($porter)): ?>
    <div class="sec">Porter — movement tasks</div>
    <?php foreach($porter as $t): if(!in_array($t->status, array('open','in_progress'))) continue; ?>
    <div class="tk-card">
      <div class="tk-name"><?= htmlspecialchars($t->patient_name ?: 'Task'); ?>
        <span class="tk-badge b-<?= htmlspecialchars($t->status); ?>"><?= htmlspecialchars($t->status); ?></span></div>
      <div class="tk-meta">
        <?= htmlspecialchars($t->task_type); ?> · <?= htmlspecialchars($t->admission_code ?? ''); ?><br>
        <?= htmlspecialchars(($t->from_location ?? '—') . ' → ' . ($t->to_location ?? '—')); ?>
      </div>
      <?php if($can['porter']): ?>
      <div class="tk-acts">
        <?php if($t->status === 'open'): ?><button onclick="wtAct(<?= (int)$t->id; ?>,'claim')">Claim</button><?php endif; ?>
        <button class="primary" onclick="wtAct(<?= (int)$t->id; ?>,'complete')">Complete</button>
      </div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>

    <div class="sec">Nursing — due &amp; open tasks</div>
    <?php $anyOpen = false; foreach($nursing as $t): if($t->status !== 'open') continue; $anyOpen = true; ?>
    <div class="tk-card <?= $t->is_overdue ? 'overdue' : ''; ?>">
      <div class="tk-name"><?= htmlspecialchars($t->label); ?>
        <span class="tk-badge b-<?= $t->is_overdue ? 'overdue' : 'open'; ?>"><?= $t->is_overdue ? 'overdue' : 'due'; ?></span></div>
      <div class="tk-meta">
        <?= htmlspecialchars($t->patient_name); ?> · <?= htmlspecialchars($t->admission_code); ?><br>
        Due <?= htmlspecialchars($t->due_at); ?>
      </div>
      <?php if($can['nurse']): ?>
      <div class="tk-acts"><button class="primary" onclick="wtNurse(<?= (int)$t->id; ?>)">Mark done</button></div>
      <?php endif; ?>
    </div>
    <?php endforeach; if(!$anyOpen): ?>
      <div class="empty"><i class="fa fa-check-circle" style="font-size:26px;display:block;margin-bottom:8px;"></i>No open nursing tasks.</div>
    <?php endif; ?>
  </div>
  <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
</div>
<?php $this->load->view('mobile/chat'); ?>
<script>
var CSRF = {'<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'};
function wtAct(id, act){
  fetch('<?= base_url('mobile/ward_task_action/'); ?>' + id, {
    method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: new URLSearchParams(Object.assign({act: act}, CSRF))
  }).then(function(r){ return r.json(); }).then(function(x){ alert(x.message); if(x.status==='success') location.reload(); });
}
function wtNurse(id){
  var n = prompt('Result note (optional)') || '';
  fetch('<?= base_url('mobile/ward_task_action/'); ?>' + id, {
    method: 'POST', headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: new URLSearchParams(Object.assign({act: 'nurse_done', note: n}, CSRF))
  }).then(function(r){ return r.json(); }).then(function(x){ alert(x.message); if(x.status==='success') location.reload(); });
}
</script>
</body>
</html>
