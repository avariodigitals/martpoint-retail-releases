<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?> — Appointments</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-ink: #1E293B; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: var(--mp-bg); text-decoration: none; }
    .topbar-titles { flex: 1; min-width: 0; }
    .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .datebar { display: flex; gap: 8px; margin-bottom: 14px; }
    .datebar input { flex: 1; padding: 11px 14px; border: 1px solid var(--mp-border); border-radius: 10px; font-size: 14px; }
    .ap-card { background: #fff; border-radius: 16px; border: 1px solid var(--mp-border); padding: 14px 16px; margin-bottom: 10px; }
    .ap-top { display: flex; justify-content: space-between; gap: 10px; align-items: flex-start; }
    .ap-time { font-size: 18px; font-weight: 800; color: var(--mp-ink); }
    .ap-name { font-size: 15px; font-weight: 700; margin-top: 2px; }
    .ap-meta { font-size: 12px; color: var(--mp-muted); margin-top: 5px; line-height: 1.6; }
    .badge { font-size: 10px; font-weight: 700; padding: 4px 10px; border-radius: 20px; text-transform: capitalize; white-space: nowrap; }
    .badge.requested { background: #DBEAFE; color: #1D4ED8; }
    .badge.proposed { background: #E0E7FF; color: #4338CA; }
    .badge.confirmed { background: #D1FAE5; color: #065F46; }
    .badge.checked_in { background: #CCFBF1; color: #0F766E; }
    .badge.completed { background: #E2E8F0; color: #334155; }
    .badge.cancelled { background: #FEE2E2; color: #B91C1C; }
    .badge.no_show { background: #FEF3C7; color: #B45309; }
    .ap-acts { display: flex; gap: 8px; margin-top: 10px; flex-wrap: wrap; }
    .ap-acts button { flex: 1; min-width: 90px; padding: 9px; border-radius: 10px; border: 1px solid var(--mp-border); background: var(--mp-bg); font-size: 12px; font-weight: 600; cursor: pointer; font-family: inherit; }
    .ap-acts button.primary { background: var(--mp-primary); color: #fff; border-color: var(--mp-primary); }
    .ap-acts button.danger { color: var(--mp-danger); border-color: #FECACA; }
    .empty { text-align: center; padding: 60px 24px; color: var(--mp-muted); font-size: 14px; }
    @media (min-width: 600px) { #app { max-width: 100%; } .screen { padding: 16px 16px 120px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/more'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Appointments</h1>
        </div>
      </div>

      <form method="get" action="<?= base_url('mobile/appointments'); ?>" class="datebar">
        <input type="date" name="date" value="<?= htmlspecialchars($filter_date); ?>" onchange="this.form.submit()">
      </form>

      <?php if(!empty($appointments)): ?>
        <?php foreach($appointments as $a): ?>
        <div class="ap-card">
          <div class="ap-top">
            <div>
              <div class="ap-time"><?= $a->scheduled_at ? date('H:i', strtotime($a->scheduled_at)) : '—'; ?></div>
              <div class="ap-name"><?= htmlspecialchars($a->customer_name ?: '—'); ?></div>
            </div>
            <span class="badge <?= $a->status; ?>"><?= str_replace('_',' ',$a->status); ?></span>
          </div>
          <div class="ap-meta">
            <?= htmlspecialchars($a->service_name ?: '—'); ?> · <?= htmlspecialchars($a->staff_name ?: 'Unassigned'); ?><?= $a->branch_name ? ' · ' . htmlspecialchars($a->branch_name) : ''; ?>
            <br><span style="font-family:monospace;"><?= htmlspecialchars($a->booking_ref ?: ''); ?></span> <?= htmlspecialchars($a->mobile ?: ''); ?>
          </div>
          <div class="ap-acts">
            <?php if($can_checkin && in_array($a->status, array('requested','proposed','confirmed')) && !$a->arrived_at): ?>
              <button class="primary" onclick="checkIn(<?= (int)$a->id; ?>)">Check In</button>
            <?php endif; ?>
            <?php if($can_edit && in_array($a->status, array('requested','proposed'))): ?>
              <button onclick="apptDo(<?= (int)$a->id; ?>,'confirmed')">Confirm</button>
            <?php endif; ?>
            <?php if($can_edit && $a->status === 'confirmed'): ?>
              <button onclick="apptDo(<?= (int)$a->id; ?>,'no_show')">No-Show</button>
            <?php endif; ?>
            <?php if($can_cancel && in_array($a->status, array('requested','proposed','confirmed'))): ?>
              <button class="danger" onclick="apptDo(<?= (int)$a->id; ?>,'cancelled', true)">Cancel</button>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="empty"><i class="fa fa-calendar-o" style="font-size:36px;opacity:.3;"></i><br><br>No appointments this day.</div>
      <?php endif; ?>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
  <script>
    var CSRF_NAME = '<?= $this->security->get_csrf_token_name(); ?>';
    var CSRF_HASH = '<?= $this->security->get_csrf_hash(); ?>';
    function apptDo(id, to, askReason){
      var note = '';
      if(askReason){ note = prompt('Reason (recorded):', '') || ''; }
      var fd = new FormData(); fd.append(CSRF_NAME, CSRF_HASH); fd.append('to', to); fd.append('note', note);
      mpFetchJson('<?= base_url('mobile/appt_transition/'); ?>' + id, { method: 'POST', body: fd })
        .then(function(d){ if(d && d.status === 'success'){ location.reload(); } else { mpError((d && d.message) || 'Failed'); } });
    }
    function checkIn(id){
      var fd = new FormData(); fd.append(CSRF_NAME, CSRF_HASH); fd.append('appointment_id', id);
      mpFetchJson('<?= base_url('mobile/care_checkin'); ?>', { method: 'POST', body: fd })
        .then(function(d){
          if(d && d.status === 'success'){ location.href = '<?= base_url('mobile/care_queue'); ?>'; }
          else { mpError((d && d.message) || 'Failed'); }
        });
    }
  </script>
</body>
</html>
