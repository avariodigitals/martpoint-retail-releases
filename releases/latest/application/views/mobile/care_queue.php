<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?> — Care Queue</title>
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
    .topbar .add { padding: 9px 14px; border-radius: 10px; background: var(--mp-teal); color: #fff; font-size: 13px; font-weight: 600; border: none; cursor: pointer; }
    .stage-h { font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: .5px; color: var(--mp-muted); margin: 18px 2px 8px; }
    .stage-h .n { background: #E0E7FF; color: #4338CA; border-radius: 12px; padding: 1px 8px; margin-left: 4px; }
    .cq-card { background: #fff; border-radius: 14px; border: 1px solid var(--mp-border); padding: 12px 14px; margin-bottom: 8px; }
    .cq-name { font-size: 15px; font-weight: 700; }
    .cq-meta { font-size: 11px; color: var(--mp-muted); margin-top: 4px; line-height: 1.6; }
    .cq-acts { display: flex; gap: 8px; margin-top: 9px; flex-wrap: wrap; }
    .cq-acts button { padding: 8px 12px; border-radius: 10px; border: 1px solid var(--mp-border); background: var(--mp-bg); font-size: 12px; font-weight: 600; cursor: pointer; font-family: inherit; }
    .cq-acts button.primary { background: var(--mp-teal); color: #fff; border-color: var(--mp-teal); }
    .arrive { background: #FFFBEB; border: 1px solid #F59E0B; border-radius: 14px; padding: 12px 14px; margin-bottom: 8px; }
    .arrive .nm { font-size: 14px; font-weight: 700; }
    .arrive button { margin-top: 8px; width: 100%; padding: 10px; border-radius: 10px; border: none; background: var(--mp-teal); color: #fff; font-size: 13px; font-weight: 700; cursor: pointer; }
    .empty { text-align: center; padding: 50px 24px; color: var(--mp-muted); font-size: 14px; }
    .sheet { display: none; position: fixed; inset: 0; background: rgba(15,23,42,.45); z-index: 500; align-items: flex-end; justify-content: center; }
    .sheet.open { display: flex; }
    .sheet-box { background: #fff; width: 100%; max-width: 430px; border-radius: 20px 20px 0 0; padding: 20px 16px calc(20px + var(--safe-bottom)); }
    .sheet-box h3 { margin: 0 0 14px; font-size: 17px; }
    .field { margin-bottom: 12px; }
    .field label { font-size: 12px; font-weight: 600; color: var(--mp-muted); display: block; margin-bottom: 5px; }
    .field input, .field select { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; font-family: inherit; }
    .mp-select-wrap { position: relative; width: 100%; }
    select.mp-select { display: none !important; }
    .mp-select-trigger { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; background: #fff; cursor: pointer; display: flex; align-items: center; justify-content: space-between; min-height: 46px; }
    .mp-select-trigger::after { content: '\f0d7'; font-family: 'FontAwesome'; color: var(--mp-muted); font-size: 14px; }
    .mp-select-trigger.placeholder { color: var(--mp-muted); }
    .mp-select-options { display: none; border: 1px solid var(--mp-border); border-top: none; border-radius: 0 0 12px 12px; background: #fff; max-height: 220px; overflow-y: auto; position: absolute; left: 0; right: 0; top: 100%; z-index: 100; }
    .mp-select-wrap.open .mp-select-options { display: block; }
    .mp-select-wrap.open .mp-select-trigger { border-radius: 12px 12px 0 0; }
    .mp-select-option { padding: 12px 14px; cursor: pointer; border-bottom: 1px solid var(--mp-border); font-size: 15px; }
    .mp-select-option:last-child { border-bottom: none; }
    .mp-select-option.active { background: #EFF6FF; color: var(--mp-primary); font-weight: 600; }
    .sheet-save { width: 100%; padding: 14px; border-radius: 12px; border: none; background: var(--mp-teal); color: #fff; font-size: 15px; font-weight: 700; cursor: pointer; }
    @media (min-width: 600px) { #app { max-width: 100%; } .screen { padding: 16px 16px 120px; } .sheet-box { max-width: 480px; border-radius: 20px; margin-bottom: 40px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/more'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Care Queue</h1>
        </div>
        <?php if($can_checkin): ?><button class="add" onclick="openWalkin()">+ Walk-in</button><?php endif; ?>
      </div>

      <?php if(!empty($arrivals) && $can_checkin): ?>
        <div class="stage-h">Expected arrivals<span class="n"><?= count($arrivals); ?></span></div>
        <?php foreach($arrivals as $a): ?>
          <div class="arrive">
            <div class="nm"><?= date('H:i', strtotime($a->scheduled_at)); ?> — <?= htmlspecialchars($a->customer_name ?: 'Patient'); ?></div>
            <div style="font-size:11px;color:var(--mp-muted);margin-top:2px;"><?= htmlspecialchars($a->service_name ?: ''); ?> <?= htmlspecialchars($a->booking_ref ?: ''); ?></div>
            <button onclick="checkinAppt(<?= (int)$a->id; ?>)">Check In</button>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <?php
      $stageLabels = array(
        'waiting_nurse' => 'Waiting for Nurse',
        'nursing_intake' => 'Nursing Intake',
        'waiting_physio' => 'Waiting for ' . mp_label('staff'),
        'with_physio' => 'With ' . mp_label('staff'),
        'awaiting_finance' => 'Awaiting Finance / Next',
        'closed' => 'Visit Closed',
      );
      $anyCards = false;
      foreach($stageLabels as $stage => $label):
        $cards = isset($queue[$stage]) ? $queue[$stage] : array();
        if(empty($cards)) continue;
        $anyCards = true;
      ?>
        <div class="stage-h"><?= htmlspecialchars($label); ?><span class="n"><?= count($cards); ?></span></div>
        <?php foreach($cards as $e): ?>
          <div class="cq-card">
            <div class="cq-name"><?= htmlspecialchars($e->customer_name ?: '—'); ?></div>
            <div class="cq-meta">
              <?= htmlspecialchars($e->patient_code ?: ''); ?> · <?= htmlspecialchars($e->encounter_code ?: ''); ?> · in <?= $e->checkin_at ? date('H:i', strtotime($e->checkin_at)) : '—'; ?>
              <?= $e->clinician_name ? '<br>' . htmlspecialchars($e->clinician_name) : ''; ?>
            </div>
            <div class="cq-acts">
              <?php if($stage === 'waiting_nurse' && $can_vitals): ?>
                <button class="primary" onclick="moveEnc(<?= (int)$e->id; ?>,'nursing_intake')">Start Intake</button>
              <?php endif; ?>
              <?php if($stage === 'nursing_intake' && $can_vitals): ?>
                <button class="primary" onclick="intakeDone(<?= (int)$e->id; ?>)">Complete Intake → <?= htmlspecialchars(mp_label('staff')); ?></button>
              <?php endif; ?>
              <button onclick="location.href='<?= base_url('mobile/encounter/' . (int)$e->id); ?>'">Open</button>
              <?php if($stage === 'waiting_physio' && $can_physio): ?>
                <button class="primary" onclick="moveEnc(<?= (int)$e->id; ?>,'with_physio')">Start Consult</button>
              <?php endif; ?>
              <?php if($stage === 'with_physio' && $can_physio): ?>
                <button class="primary" onclick="moveEnc(<?= (int)$e->id; ?>,'awaiting_finance')">To Finance/Next</button>
                <button onclick="closeVisit(<?= (int)$e->id; ?>)">Close Visit</button>
              <?php endif; ?>
              <?php if($stage === 'awaiting_finance' && ($can_finance || $can_physio)): ?>
                <button class="primary" onclick="closeVisit(<?= (int)$e->id; ?>)">Close Visit</button>
              <?php endif; ?>
            </div>
          </div>
        <?php endforeach; ?>
      <?php endforeach; ?>

      <?php if(!$anyCards && empty($arrivals)): ?>
        <div class="empty"><i class="fa fa-list-ol" style="font-size:36px;opacity:.3;"></i><br><br>Queue is empty.</div>
      <?php endif; ?>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <div class="sheet" id="walkinSheet" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="sheet-box">
      <h3>Walk-in Check-in</h3>
      <form id="walkinForm" onsubmit="return false;">
        <input type="hidden" id="wk_key">
        <div class="field"><label>Patient *</label>
          <select id="wk_patient">
            <option value="">— choose —</option>
            <?php foreach($patients as $p): ?>
              <option value="<?= (int)$p->id; ?>"><?= htmlspecialchars(($p->patient_code ? $p->patient_code . ' — ' : '') . $p->customer_name); ?></option>
            <?php endforeach; ?>
          </select></div>
        <div class="field"><label>Note</label><input type="text" id="wk_note" placeholder="e.g. Walk-in, back pain"></div>
        <button type="button" class="sheet-save" onclick="doWalkin()">Check In</button>
      </form>
    </div>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
  <script>
    var CSRF_NAME = '<?= $this->security->get_csrf_token_name(); ?>';
    var CSRF_HASH = '<?= $this->security->get_csrf_hash(); ?>';

    // Custom inline select — hidden native select retains name/value
    (function(){
      function buildMpSelect(sel){
        if(sel.dataset.mpBuilt) return;
        sel.dataset.mpBuilt = '1';
        sel.classList.add('mp-select');
        var wrap = document.createElement('div');
        wrap.className = 'mp-select-wrap';
        sel.parentNode.insertBefore(wrap, sel);
        wrap.appendChild(sel);
        var trigger = document.createElement('div');
        trigger.className = 'mp-select-trigger';
        wrap.appendChild(trigger);
        var list = document.createElement('div');
        list.className = 'mp-select-options';
        wrap.appendChild(list);
        function render(){
          list.innerHTML = '';
          Array.from(sel.options).forEach(function(opt, idx){
            var div = document.createElement('div');
            div.className = 'mp-select-option';
            div.textContent = opt.textContent;
            if(sel.selectedIndex === idx) div.classList.add('active');
            div.addEventListener('click', function(e){
              e.stopPropagation();
              sel.selectedIndex = idx;
              sel.dispatchEvent(new Event('change', {bubbles:true}));
              closeAll();
            });
            list.appendChild(div);
          });
          var s = sel.options[sel.selectedIndex];
          trigger.textContent = s ? s.textContent : 'Select';
          trigger.classList.toggle('placeholder', !s || !s.value);
        }
        trigger.addEventListener('click', function(e){
          e.stopPropagation();
          closeAll();
          wrap.classList.toggle('open');
        });
        render();
      }
      function closeAll(){ document.querySelectorAll('.mp-select-wrap.open').forEach(function(w){ w.classList.remove('open'); }); }
      document.addEventListener('click', closeAll);
      document.querySelectorAll('select').forEach(buildMpSelect);
    })();

    function checkinAppt(apptId){
      var fd = new FormData(); fd.append(CSRF_NAME, CSRF_HASH); fd.append('appointment_id', apptId);
      mpFetchJson('<?= base_url('mobile/care_checkin'); ?>', { method: 'POST', body: fd })
        .then(function(d){ if(d && d.status === 'success'){ location.reload(); } else { mpError((d && d.message) || 'Failed'); } });
    }
    function openWalkin(){
      document.getElementById('wk_key').value = 'walkin:' + Date.now() + ':' + Math.random().toString(36).slice(2,10);
      document.getElementById('walkinSheet').classList.add('open');
    }
    function doWalkin(){
      var pid = document.getElementById('wk_patient').value;
      if(!pid){ mpError('Choose a patient'); return; }
      var fd = new FormData(); fd.append(CSRF_NAME, CSRF_HASH);
      fd.append('patient_id', pid);
      fd.append('checkin_key', document.getElementById('wk_key').value);
      fd.append('note', document.getElementById('wk_note').value);
      mpFetchJson('<?= base_url('mobile/care_checkin'); ?>', { method: 'POST', body: fd })
        .then(function(d){ if(d && d.status === 'success'){ location.reload(); } else { mpError((d && d.message) || 'Failed'); } });
    }
    function moveEnc(id, to){
      var fd = new FormData(); fd.append(CSRF_NAME, CSRF_HASH); fd.append('to', to);
      mpFetchJson('<?= base_url('mobile/care_move/'); ?>' + id, { method: 'POST', body: fd })
        .then(function(d){ if(d && d.status === 'success'){ location.reload(); } else { mpError((d && d.message) || 'Failed'); } });
    }
    function intakeDone(id){
      var fd = new FormData(); fd.append(CSRF_NAME, CSRF_HASH);
      mpFetchJson('<?= base_url('mobile/care_intake_complete/'); ?>' + id, { method: 'POST', body: fd })
        .then(function(d){ if(d && d.status === 'success'){ location.reload(); } else { mpError((d && d.message) || 'Failed'); } });
    }
    function closeVisit(id){
      var note = prompt('Close visit — outcome note:', '') || '';
      var fd = new FormData(); fd.append(CSRF_NAME, CSRF_HASH); fd.append('to', 'closed'); fd.append('note', note);
      mpFetchJson('<?= base_url('mobile/care_move/'); ?>' + id, { method: 'POST', body: fd })
        .then(function(d){ if(d && d.status === 'success'){ location.reload(); } else { mpError((d && d.message) || 'Failed'); } });
    }
  </script>
</body>
</html>
