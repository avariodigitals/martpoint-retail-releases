<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
<?php $this->load->view('comman/app_icons'); ?>
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
    .cq-card { background: #fff; border-radius: 14px; border: 1px solid var(--mp-border); padding: 10px 12px; margin-bottom: 8px; }
    .cq-top { display: flex; align-items: center; gap: 10px; cursor: pointer; }
    .cq-no { width: 30px; height: 30px; flex: none; border-radius: 9px; background: #E0E7FF; color: #4338CA; font-size: 13px; font-weight: 800; display: flex; align-items: center; justify-content: center; }
    .cq-who { flex: 1; min-width: 0; }
    .cq-name { font-size: 14px; font-weight: 700; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .cq-sub { font-size: 11px; color: var(--mp-muted); margin-top: 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .cq-caret { color: var(--mp-muted); font-size: 12px; transition: transform .18s; }
    .cq-card.open .cq-caret { transform: rotate(180deg); }
    .cq-body { display: none; margin-top: 10px; border-top: 1px solid var(--mp-border); padding-top: 9px; }
    .cq-card.open .cq-body { display: block; }
    .cq-meta { font-size: 11px; color: var(--mp-muted); margin-bottom: 8px; line-height: 1.6; }
    .cq-nurse-wrap { margin: 0 0 8px; }
    .cq-card .mp-select-trigger { padding: 9px 12px; font-size: 13px; min-height: 0; border-radius: 10px; }
    .cq-card .mp-select-wrap.open .mp-select-trigger { border-radius: 10px 10px 0 0; }
    .cq-card .mp-select-options { border-radius: 0 0 10px 10px; }
    .cq-card .mp-select-option { padding: 10px 12px; font-size: 13px; }
    .cq-acts { display: flex; gap: 8px; margin-top: 4px; flex-wrap: wrap; }
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
    .cq-days { display: flex; gap: 8px; flex-wrap: wrap; margin: 0 0 14px; }
    .cq-days a { font-size: 12px; font-weight: 700; padding: 7px 14px; border-radius: 999px; border: 1px solid var(--mp-border); color: var(--mp-muted); text-decoration: none; background: #fff; }
    .cq-days a.on { background: #EEF2FF; border-color: #6366F1; color: #4338CA; }
    .cq-note { font-size: 12px; line-height: 1.5; margin: 8px 0 0; padding: 8px 10px; border-radius: 10px; background: #FFFBEB; border: 1px solid #FCD34D; color: #78350F; }
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

      <?php
      /* Each chip keeps the other chip's choice, so "This year + My patients"
         can be read together instead of one filter wiping the other out. */
      $cqQs = function($over = array()) use ($queue_days, $queue_mine){
        $p = array('days' => array_key_exists('days', $over) ? $over['days'] : $queue_days);
        $mine = array_key_exists('mine', $over) ? $over['mine'] : $queue_mine;
        if($mine) $p['mine'] = 1;
        return '?' . http_build_query($p);
      };
      ?>
      <div class="cq-days">
        <a class="<?= (int)$queue_days === 1 ? 'on' : ''; ?>" href="<?= base_url('mobile/care_queue') . $cqQs(array('days' => 1)); ?>">Today</a>
        <a class="<?= (int)$queue_days === 7 ? 'on' : ''; ?>" href="<?= base_url('mobile/care_queue') . $cqQs(array('days' => 7)); ?>">7 days</a>
        <a class="<?= (int)$queue_days === 30 ? 'on' : ''; ?>" href="<?= base_url('mobile/care_queue') . $cqQs(array('days' => 30)); ?>">30 days</a>
        <a class="<?= (int)$queue_days === 365 ? 'on' : ''; ?>" href="<?= base_url('mobile/care_queue') . $cqQs(array('days' => 365)); ?>">This year</a>
        <a class="<?= (int)$queue_days === 0 ? 'on' : ''; ?>" href="<?= base_url('mobile/care_queue') . $cqQs(array('days' => 0)); ?>">All still open</a>
        <?php if(!empty($is_therapist)): ?>
          <a class="<?= (int)$queue_mine ? 'on' : ''; ?>" href="<?= base_url('mobile/care_queue') . $cqQs(array('mine' => (int)$queue_mine ? 0 : 1)); ?>">My patients</a>
        <?php endif; ?>
      </div>

      <?php if((int)($queue_hidden ?? 0) > 0 && !(int)($queue_mine ?? 0)): ?>
        <div class="cq-note">
          <b><?= (int)$queue_hidden ?> open visit(s) are older than this window</b> —
          <a href="<?= base_url('mobile/care_queue') . $cqQs(array('days' => 0)); ?>" style="color:#78350F;text-decoration:underline">show everything still open</a>.
        </div>
      <?php endif; ?>

      <?php if((int)($queue_elsewhere ?? 0) > 0): ?>
        <div class="cq-note">
          <b><?= (int)$queue_elsewhere ?> more open visit(s) are at branches you are not assigned to</b> —
          they are counted on the dashboard but not yours to work here.
        </div>
      <?php endif; ?>

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

      <?php if(!empty($booked) && $can_checkin): ?>
        <div class="stage-h">Sessions booked on the diary<span class="n"><?= count($booked); ?></span></div>
        <?php foreach($booked as $b): ?>
          <div class="arrive">
            <div class="nm"><?= date('D d M H:i', strtotime($b->scheduled_at)); ?> — <?= htmlspecialchars($b->patient_name ?: 'Patient'); ?></div>
            <div style="font-size:11px;color:var(--mp-muted);margin-top:2px;"><?= htmlspecialchars($b->treatment_name ?: 'Treatment course'); ?> · session <?= (int)$b->session_no; ?> of <?= (int)$b->units_total; ?></div>
            <button onclick="checkinSess(<?= (int)$b->id; ?>)">Check In</button>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <?php if(!empty($booked_upcoming) && $can_checkin): ?>
        <div class="stage-h">Booked for a later day<span class="n"><?= count($booked_upcoming); ?></span></div>
        <?php foreach($booked_upcoming as $b): ?>
          <div class="arrive">
            <div class="nm"><?= date('D d M H:i', strtotime($b->scheduled_at)); ?> — <?= htmlspecialchars($b->patient_name ?: 'Patient'); ?></div>
            <div style="font-size:11px;color:var(--mp-muted);margin-top:2px;"><?= htmlspecialchars($b->treatment_name ?: 'Treatment course'); ?> · session <?= (int)$b->session_no; ?> of <?= (int)$b->units_total; ?></div>
            <button onclick="checkinSess(<?= (int)$b->id; ?>)">Check In Today</button>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>

      <?php
      /* No Closed stage — a cleared visit is finished business and the queue
         lists only what is still moving. History lives on the patient page. */
      $stageLabels = array(
        'waiting_nurse' => 'Waiting for Nurse',
        'nursing_intake' => 'Nursing Intake',
        'waiting_physio' => 'Waiting for ' . mp_label('staff'),
        'with_physio' => 'With ' . mp_label('staff'),
        'awaiting_finance' => 'Awaiting Finance / Next',
      );
      /* A visit whose stage this screen has never heard of would be counted on
         the dashboard and invisible here, so it gets its own section. */
      foreach($queue as $st => $cs){
        if(!empty($cs) && !isset($stageLabels[$st])){
          $stageLabels[$st] = 'Needs attention — ' . ucwords(str_replace('_', ' ', $st));
        }
      }
      // Queue number = position in today's arrivals by check-in time — the number
      // the desk calls out, so it leads the collapsed row.
      $allCards = array();
      foreach($queue as $st => $cs){ foreach($cs as $c){ $allCards[] = $c; } }
      usort($allCards, function($a, $b){ return strcmp((string)$a->checkin_at, (string)$b->checkin_at); });
      $queueNo = array(); $seq = 0;
      foreach($allCards as $c){ $queueNo[(int)$c->id] = ++$seq; }

      $anyCards = false;
      foreach($stageLabels as $stage => $label):
        $cards = isset($queue[$stage]) ? $queue[$stage] : array();
        if(empty($cards)) continue;
        $anyCards = true;
      ?>
        <div class="stage-h"><?= htmlspecialchars($label); ?><span class="n"><?= count($cards); ?></span></div>
        <?php foreach($cards as $e):
          $eid = (int)$e->id;
          $vt  = $e->visit_type ?? null;
          $chipTxt = $vt === 'treatment' ? 'Session' : ($vt === 'assessment' ? 'Assessment' : 'Not typed');
          $chipColor = $vt === 'treatment' ? '#065F46' : ($vt === 'assessment' ? '#1E40AF' : '#64748B');
          $nurseLbl = !empty($e->nurse_name) ? physio_staff_label($e->nurse_name, $e->nurse_role_id ?? null) : '';
          $sEnt = isset($session_ent[(int)$e->patient_id]) ? $session_ent[(int)$e->patient_id] : null;
        ?>
          <div class="cq-card<?= (!$vt && $stage === 'waiting_nurse') ? ' open' : ''; ?>" id="cq<?= $eid; ?>">
            <div class="cq-top" onclick="cqTap(this)">
              <div class="cq-no"><?= (int)($queueNo[$eid] ?? 0); ?></div>
              <div class="cq-who">
                <div class="cq-name"><?= htmlspecialchars($e->customer_name ?: '—'); ?></div>
                <div class="cq-sub"><?= htmlspecialchars($e->encounter_code ?: ''); ?> · <b style="color:<?= $chipColor; ?>;"><?= $chipTxt; ?></b><?= $vt === 'treatment' && $nurseLbl ? ' · ' . htmlspecialchars($nurseLbl) : ''; ?></div>
              </div>
              <i class="fa fa-chevron-down cq-caret"></i>
            </div>
            <div class="cq-body" onclick="event.stopPropagation()">
              <div class="cq-meta">
                <?= htmlspecialchars($e->patient_code ?: ''); ?> · in <?= $e->checkin_at ? date('H:i', strtotime($e->checkin_at)) : '—'; ?>
                <?= $e->clinician_name ? '<br>' . htmlspecialchars(physio_staff_label($e->clinician_name, $e->clinician_role_id ?? null)) : ''; ?>
              </div>
              <?php if(!empty($e->handover_note)): ?>
                <div class="cq-note"><b>Nurse note<?= $e->handover_by ? ' — ' . htmlspecialchars($e->handover_by) : ''; ?>:</b> <?= htmlspecialchars($e->handover_note); ?></div>
              <?php endif; ?>
              <?php if((!$vt || $stage === 'waiting_nurse') && ($can_checkin || $can_vitals)): ?>
              <div class="cq-acts" style="margin:0 0 8px;">
                <button class="<?= $vt === 'assessment' ? 'primary' : ''; ?>" onclick="setType(<?= $eid; ?>,'assessment')">Assessment</button>
                <?php /* A treatment session draws from a paid package — with none
                         available the route is closed and the desk bills instead. */ ?>
                <button class="<?= $vt === 'treatment' ? 'primary' : ''; ?>" <?= $sEnt ? '' : 'disabled style="opacity:.55;" title="No paid sessions on a package — sell one first, or take the Assessment route"'; ?> onclick="setType(<?= $eid; ?>,'treatment')">Treatment session</button>
              </div>
              <?php endif; ?>
              <?php if($vt === 'treatment' && !empty($nurses) && $stage !== 'closed'): ?>
              <div class="cq-nurse-wrap cq-nurse-pick">
                <select onchange="setNurse(<?= $eid; ?>, this.value)">
                  <option value="">— nurse tasked —</option>
                  <?php foreach($nurses as $n): ?>
                  <option value="<?= (int)$n->id; ?>" <?= (int)($e->nurse_user_id ?? 0) === (int)$n->id ? 'selected' : ''; ?>><?= htmlspecialchars(trim($n->first_name . ' ' . $n->last_name) ?: $n->username); ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <?php endif; ?>
              <?php /* The desk sends the patient to a named therapist, or re-routes
                       them when that therapist is busy or away. */ ?>
              <?php if(!empty($can_route) && ($stage === 'waiting_physio' || $stage === 'with_physio') && !empty($therapists)): ?>
              <div class="cq-nurse-wrap">
                <label style="font-size:12px;font-weight:600;color:var(--mp-muted);display:block;margin-bottom:5px;"><?= $stage === 'with_physio' ? 'Re-route to therapist' : 'Send to therapist'; ?></label>
                <select onchange="routeTo(<?= $eid; ?>, this.value)">
                  <option value="">— choose —</option>
                  <?php foreach($therapists as $t): ?>
                  <option value="<?= (int)$t->id; ?>" <?= (int)($e->clinician_user_id ?? 0) === (int)$t->id ? 'selected' : ''; ?>><?= htmlspecialchars(trim($t->first_name . ' ' . $t->last_name) ?: $t->username); ?></option>
                  <?php endforeach; ?>
                </select>
                <div style="font-size:11px;color:var(--mp-muted);margin-top:4px;">The patient appears on that <?= htmlspecialchars(mp_label('staff', true)); ?>'s queue. Whoever starts the consult is still recorded as the one who treated them.</div>
              </div>
              <?php endif; ?>
              <div class="cq-acts">
                <?php if($stage === 'waiting_nurse' && $can_vitals): ?>
                  <button class="primary" onclick="moveEnc(<?= $eid; ?>,'nursing_intake')">Start Intake</button>
                <?php endif; ?>
                <?php if($stage === 'nursing_intake' && $can_vitals): ?>
                  <button class="primary" onclick="intakeDone(<?= $eid; ?>, <?= $vt === 'treatment' ? "'treatment'" : 'null'; ?>)">Complete Intake → <?= htmlspecialchars(mp_label('staff')); ?></button>
                <?php endif; ?>
                <button onclick="location.href='<?= base_url('mobile/encounter/' . $eid); ?>'">Open</button>
                <?php if($stage === 'waiting_physio' && $can_physio): ?>
                  <button class="primary" onclick="moveEnc(<?= $eid; ?>,'with_physio')">Start Consult</button>
                <?php endif; ?>
                <?php if($stage === 'with_physio' && $can_physio): ?>
                  <?php if($vt === 'assessment'): ?>
                    <button class="primary" onclick="moveEnc(<?= $eid; ?>,'awaiting_finance')">To Finance</button>
                  <?php endif; ?>
                  <button onclick="closeVisit(<?= $eid; ?>)"><?= $vt === 'treatment' ? 'Complete Session' : 'Close Visit'; ?></button>
                <?php endif; ?>
                <?php if($stage === 'awaiting_finance' && ($can_finance || $can_physio)): ?>
                  <button class="primary" onclick="closeVisit(<?= $eid; ?>)">Close Visit</button>
                <?php endif; ?>
              </div>
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
        .then(function(d){ if(d && d.status === 'success'){ location.reload(); } else { mpError((d && (d.message || d.error)) || 'Failed'); } });
    }
    function checkinSess(sessId){
      var fd = new FormData(); fd.append(CSRF_NAME, CSRF_HASH); fd.append('session_id', sessId);
      mpFetchJson('<?= base_url('mobile/care_checkin'); ?>', { method: 'POST', body: fd })
        .then(function(d){ if(d && d.status === 'success'){ location.reload(); } else { mpError((d && (d.message || d.error)) || 'Failed'); } });
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
        .then(function(d){ if(d && d.status === 'success'){ location.reload(); } else { mpError((d && (d.message || d.error)) || 'Failed'); } });
    }
    function cqTap(head){
      var card = head.closest('.cq-card');
      if(card) card.classList.toggle('open');
    }
    function setType(id, type){
      var fd = new FormData(); fd.append(CSRF_NAME, CSRF_HASH); fd.append('visit_type', type);
      var card = document.getElementById('cq' + id);
      var sel = card ? card.querySelector('.cq-nurse-pick select') : null;
      if(type === 'treatment' && sel && sel.value) fd.append('nurse_user_id', sel.value);
      mpFetchJson('<?= base_url('mobile/care_visit_type/'); ?>' + id, { method: 'POST', body: fd })
        .then(function(d){ if(d && d.status === 'success'){ location.reload(); } else { mpError((d && (d.message || d.error)) || 'Failed'); } });
    }
    function routeTo(id, userId){
      if(!userId) return;
      var fd = new FormData(); fd.append(CSRF_NAME, CSRF_HASH); fd.append('clinician_user_id', userId);
      mpFetchJson('<?= base_url('mobile/care_route_therapist/'); ?>' + id, { method: 'POST', body: fd })
        .then(function(d){
          if(d && d.status === 'success'){ mpSuccess(d.message || 'Done'); setTimeout(function(){ location.reload(); }, 900); }
          else { mpError((d && (d.message || d.error)) || 'Failed'); }
        });
    }
    function setNurse(id, userId){
      if(!userId) return;
      var fd = new FormData(); fd.append(CSRF_NAME, CSRF_HASH); fd.append('nurse_user_id', userId);
      mpFetchJson('<?= base_url('mobile/care_assign_nurse/'); ?>' + id, { method: 'POST', body: fd })
        .then(function(d){ if(d && d.status === 'success'){ location.reload(); } else { mpError((d && (d.message || d.error)) || 'Failed'); } });
    }
    function moveEnc(id, to){
      var fd = new FormData(); fd.append(CSRF_NAME, CSRF_HASH); fd.append('to', to);
      mpFetchJson('<?= base_url('mobile/care_move/'); ?>' + id, { method: 'POST', body: fd })
        .then(function(d){ if(d && d.status === 'success'){ location.reload(); } else { mpError((d && (d.message || d.error)) || 'Failed'); } });
    }
    function intakeDone(id, type){
      var treatment = type === 'treatment';
      mpPrompt({
        title: 'Nurse hand-over note',
        message: treatment
          ? 'What is today’s treatment for, and what must the therapist know? The therapist sees this on the patient’s card.'
          : 'Optional note for the therapist — it shows on the patient’s card.',
        placeholder: treatment ? 'e.g. Left knee treatment — tender on movement today' : 'Anything the therapist should know',
        multiline: true,
        required: treatment,
        okText: 'Complete Intake'
      }).then(function(note){
        if(note === null) return;
        var fd = new FormData(); fd.append(CSRF_NAME, CSRF_HASH); fd.append('note', note);
        mpFetchJson('<?= base_url('mobile/care_intake_complete/'); ?>' + id, { method: 'POST', body: fd })
          .then(function(d){ if(d && d.status === 'success'){ location.reload(); } else { mpError((d && (d.message || d.error)) || 'Failed'); } });
      });
    }
    function closeVisit(id){
      mpPrompt({
        title: 'Close this visit',
        message: 'Add an outcome note so the visit record is complete.',
        placeholder: 'Outcome note',
        multiline: true,
        okText: 'Close Visit',
        danger: true
      }).then(function(note){
        if(note === null) return;
        var fd = new FormData(); fd.append(CSRF_NAME, CSRF_HASH); fd.append('to', 'closed'); fd.append('note', note);
        mpFetchJson('<?= base_url('mobile/care_move/'); ?>' + id, { method: 'POST', body: fd })
          .then(function(d){ if(d && d.status === 'success'){ location.reload(); } else { mpError((d && (d.message || d.error)) || 'Failed'); } });
      });
    }
  </script>
</body>
</html>
