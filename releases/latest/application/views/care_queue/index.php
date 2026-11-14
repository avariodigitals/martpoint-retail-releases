<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $this->load->view('components/dialog'); ?>
<?php $this->load->view('components/book_session'); ?>
<style>
.cq-board{margin-bottom:20px!important}
.cq-lane{margin-bottom:22px!important}
.cq-lane-head{display:flex!important;align-items:baseline!important;gap:10px!important;flex-wrap:wrap!important;margin:0 0 10px!important;padding:0 2px 8px!important;border-bottom:2px solid var(--mp-border)!important}
.cq-lane-head h3{margin:0!important;font-size:12px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.8px!important;color:var(--mp-muted)!important}
.cq-lane-note{font-size:11px!important;color:#94a3b8!important;font-weight:600!important}
.cq-lane.assessment .cq-lane-head{border-color:#3B82F6!important}
.cq-lane.assessment .cq-lane-head h3{color:#1E40AF!important}
.cq-lane.treatment .cq-lane-head{border-color:#10B981!important}
.cq-lane.treatment .cq-lane-head h3{color:#065F46!important}
.cq-lane.untyped .cq-lane-head{border-color:#F59E0B!important}
.cq-lane.untyped .cq-lane-head h3{color:#B45309!important}
.cq-row{display:grid!important;grid-template-columns:repeat(auto-fit,minmax(215px,1fr))!important;gap:12px!important;align-items:start!important}
.cq-reg-grid{display:flex!important;flex-wrap:wrap!important;gap:16px!important;justify-content:flex-start!important;align-items:flex-start!important}
.cq-reg-grid .cq-card{flex:0 0 calc(25% - 12px)!important;min-width:0!important;box-sizing:border-box!important;margin-bottom:0!important}
@media (max-width:1100px){.cq-reg-grid .cq-card{flex:0 0 calc(50% - 8px)!important}}
@media (max-width:620px){.cq-reg-grid .cq-card{flex:0 0 100%!important}}
.cq-col{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:10px!important;min-height:90px!important}
.cq-col h4{font-size:10px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:var(--mp-muted)!important;margin:0 0 8px 0!important;display:flex!important;align-items:center!important;gap:6px!important}

/* Collapsed card: queue number, name, visit id, type chip, money dot. */
.cq-card{border:1px solid var(--mp-border)!important;border-radius:10px!important;background:#fff!important;margin-bottom:6px!important;cursor:pointer!important;transition:border-color .15s, box-shadow .15s!important}
.cq-card:hover{border-color:#94a3b8!important;box-shadow:0 2px 8px rgba(15,23,42,.06)!important}
.cq-card.open{border-color:var(--mp-primary)!important;box-shadow:0 4px 14px rgba(15,23,42,.10)!important;cursor:default!important}
.cq-top{display:flex!important;align-items:center!important;gap:8px!important;padding:6px 8px!important}
.cq-no{min-width:25px!important;height:25px!important;padding:0 5px!important;border-radius:7px!important;background:#EEF2FF!important;color:#4338CA!important;font-size:11.5px!important;font-weight:800!important;display:flex!important;align-items:center!important;justify-content:center!important;flex:none!important}
.cq-who{flex:1!important;min-width:0!important}
.cq-who .nm{font-size:12px!important;font-weight:700!important;line-height:1.3!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;color:var(--mp-text,#0f172a)!important}
.cq-who .sub{font-size:9.5px!important;color:var(--mp-muted)!important;line-height:1.5!important;white-space:nowrap!important;overflow:hidden!important;text-overflow:ellipsis!important;display:flex!important;align-items:center!important;gap:5px!important}
.cq-dot{width:7px!important;height:7px!important;border-radius:50%!important;flex:none!important;background:#CBD5E1!important}
.cq-dot.paid{background:#10B981!important}
.cq-dot.partial{background:#F59E0B!important}
.cq-dot.unpaid{background:#EF4444!important}
.cq-chip{font-size:8.5px!important;font-weight:800!important;padding:1px 6px!important;border-radius:20px!important;letter-spacing:.3px!important;white-space:nowrap!important;flex:none!important;text-transform:uppercase!important}
.cq-chip.assessment{background:#EFF6FF!important;color:#1E40AF!important}
.cq-chip.treatment{background:#ECFDF5!important;color:#065F46!important}
.cq-chip.none{background:#F1F5F9!important;color:#64748B!important}
.cq-caret{font-size:9px!important;color:#CBD5E1!important;flex:none!important;transition:transform .15s!important}
.cq-card.open .cq-caret{transform:rotate(180deg)!important}
.cq-body{display:none!important;padding:2px 9px 9px!important;border-top:1px solid #F1F5F9!important}
.cq-card.open .cq-body{display:block!important}
.cq-meta{font-size:10px!important;color:var(--mp-muted)!important;line-height:1.6!important;margin:2px 0 7px!important}
.cq-meta b{color:var(--mp-text,#0f172a)!important}
.cq-card .acts{margin-top:7px!important;display:flex!important;gap:5px!important;flex-wrap:wrap!important}
.cq-card .acts .btn-xs{font-size:10px!important;padding:4px 7px!important;border-radius:6px!important;border:1px solid var(--mp-border)!important;background:var(--mp-surface,#fff)!important;cursor:pointer!important;white-space:nowrap!important}
.cq-card .acts .btn-xs.primary{background:var(--mp-primary)!important;color:#fff!important;border-color:var(--mp-primary)!important}

/* Visit-type chooser at the registration desk. */
.cq-choose{display:grid!important;grid-template-columns:1fr 1fr!important;gap:6px!important;margin:7px 0!important}
.cq-choose button{padding:9px 6px!important;border-radius:8px!important;border:1.5px solid var(--mp-border)!important;background:#fff!important;font-size:11px!important;font-weight:700!important;cursor:pointer!important;color:var(--mp-text,#0f172a)!important}
.cq-choose button:hover{border-color:#94a3b8!important}
.cq-choose button.sel-assessment{background:#EFF6FF!important;border-color:#3B82F6!important;color:#1E40AF!important}
.cq-choose button.sel-treatment{background:#ECFDF5!important;border-color:#10B981!important;color:#065F46!important}
.cq-choose button.sel-outpatient{background:#ECFDF5!important;border-color:#10B981!important;color:#065F46!important}
.cq-choose button.sel-inpatient{background:#EEF2FF!important;border-color:#6366F1!important;color:#4338CA!important}
.cq-nurse{margin:6px 0 2px!important}
.cq-nurse label{font-size:9px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.4px!important;color:var(--mp-muted)!important;display:block!important;margin:0 0 3px!important}
.cq-nurse select{width:100%!important;font-size:11px!important;padding:5px 7px!important;border:1px solid var(--mp-border)!important;border-radius:7px!important;background:#fff!important}
.cq-bell{position:relative!important;display:inline-flex!important;align-items:center!important;justify-content:center!important;width:42px!important;height:42px!important;border-radius:12px!important;border:1px solid #F59E0B!important;background:#FFFBEB!important;color:#B45309!important;font-size:16px!important;cursor:pointer!important}
.cq-bell:hover{background:#FEF3C7!important}
.cq-bell-n{position:absolute!important;top:-7px!important;right:-7px!important;min-width:19px!important;height:19px!important;padding:0 5px!important;border-radius:10px!important;background:#DC2626!important;color:#fff!important;font-size:11px!important;font-weight:800!important;display:flex!important;align-items:center!important;justify-content:center!important}
.cq-arrive-panel{display:none!important;background:#FFFBEB!important;border:1px solid #F59E0B!important;border-radius:14px!important;padding:14px!important;margin-bottom:18px!important}
.cq-arrive-panel.open{display:block!important}
.cq-arrive-sec{font-size:10px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:#92400E!important;margin:14px 0 6px!important}
.cq-arrive-sec:first-child{margin-top:0!important}
.cq-arrive-row{display:flex!important;align-items:center!important;gap:12px!important;background:#fff!important;border:1px solid #FCD34D!important;border-radius:11px!important;padding:9px 12px!important;margin-bottom:6px!important}
.cq-row-when{font-size:12px!important;font-weight:800!important;color:#92400E!important;min-width:104px!important;flex:none!important;white-space:nowrap!important}
.cq-row-who{flex:1 1 auto!important;min-width:0!important;font-size:13px!important}
.cq-row-sub{display:block!important;font-size:11px!important;color:var(--mp-muted)!important;margin-top:2px!important}
.cq-arrive-row .mp-qa-btn{flex:none!important}
.cq-arrive-foot{font-size:11px!important;color:var(--mp-muted)!important;margin-top:10px!important}
@media (max-width:640px){.cq-arrive-row{flex-wrap:wrap!important}.cq-row-when{min-width:0!important}.cq-arrive-row .mp-qa-btn{margin-left:auto!important}}
.cq-days{display:flex!important;align-items:center!important;gap:6px!important;flex-wrap:wrap!important;margin:0 0 16px!important}
.cq-days-lbl{font-size:10px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:var(--mp-muted)!important;margin-right:2px!important}
.cq-days a{font-size:11px!important;font-weight:700!important;padding:5px 11px!important;border-radius:999px!important;border:1px solid var(--mp-border)!important;color:var(--mp-muted)!important;text-decoration:none!important;background:#fff!important}
.cq-days a.on{background:#EEF2FF!important;border-color:#6366F1!important;color:#4338CA!important}
.cq-days-note{font-size:10px!important;color:#94a3b8!important;font-weight:600!important;margin-left:6px!important}
.cq-badge{font-size:10px!important;font-weight:700!important;padding:2px 8px!important;border-radius:12px!important;background:#E0E7FF!important;color:#4338CA!important}
.cq-money{font-size:10px!important;margin:5px 0!important;padding:4px 7px!important;border-radius:6px!important;border:1px solid var(--mp-border)!important;background:var(--mp-bg,#F8FAFC)!important}
.cq-money.unpaid{background:#FEF3C7!important;border-color:#F59E0B!important;color:#92400E!important}
.cq-money.paid{background:#D1FAE5!important;border-color:#10B981!important;color:#065F46!important}
.cq-money.none{color:var(--mp-muted)!important}
.cq-handover{font-size:11px!important;line-height:1.45!important;margin:6px 0!important;padding:6px 9px!important;border-radius:8px!important;background:#FFFBEB!important;border:1px solid #FCD34D!important;color:#78350F!important}
.bill-line{display:grid!important;grid-template-columns:1fr 70px 110px 30px!important;gap:6px!important;margin-bottom:6px!important;align-items:center!important}
.bill-line .btn-xs{font-size:11px!important;padding:4px 8px!important;border-radius:6px!important;border:1px solid var(--mp-border)!important;background:#fff!important;cursor:pointer!important}
.vt-dialog{width:calc(100% - 40px)!important;max-width:720px!important}
.vt-grid{display:grid!important;grid-template-columns:1fr 1fr!important;gap:10px!important}
.vt-row{border:1px solid var(--mp-border)!important;border-radius:10px!important;padding:8px 10px!important;background:var(--mp-surface,#fff)!important}
.vt-head{display:flex!important;align-items:center!important;justify-content:space-between!important;gap:8px!important;margin-bottom:5px!important}
.vt-label{font-size:12px!important;font-weight:700!important;color:#0f172a!important}
.vt-nm-lbl{font-size:10px!important;font-weight:600!important;color:var(--mp-muted)!important;display:flex!important;align-items:center!important;gap:4px!important;white-space:nowrap!important;cursor:pointer!important;margin:0!important}
.vt-nm-lbl input{margin:0!important}
.vt-line{display:flex!important;align-items:center!important;gap:8px!important}
.vt-line .vt-val{flex:1!important;min-width:0!important}
.vt-unit{font-size:11px!important;color:var(--mp-muted)!important;min-width:34px!important;text-align:right!important}
.vt-foot{display:grid!important;grid-template-columns:1fr 1fr!important;gap:12px!important;margin-top:12px!important}
.vt-foot .mp-form-group{margin-bottom:0!important}
@media (max-width:640px){.vt-grid{grid-template-columns:1fr!important}.vt-foot{grid-template-columns:1fr!important}}
</style>

<?php
/* The desk's bell: everything waiting to be checked in, counted once and read
   on click. Only care_checkin sees it — the queue board itself stays readable
   for the clinical desks, which do not check anyone in. */
$cqArrivals = $can_checkin ? $arrivals : array();
$cqDue      = $can_checkin ? $booked : array();
$cqAhead    = $can_checkin ? ($booked_upcoming ?? array()) : array();
$cqAheadAppt = $can_checkin ? ($arrivals_ahead ?? array()) : array();
$cqPending  = count($cqArrivals) + count($cqDue) + count($cqAhead) + count($cqAheadAppt);
?>
<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Check-in &rarr; register/bill &rarr; nursing intake &rarr; therapist &rarr; finance — each stage shows only what that desk does</div>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
    <?php if($cqPending > 0): ?>
    <button type="button" class="cq-bell" id="cqBell" onclick="cqToggleArrivals()" aria-expanded="false" title="Who is due to be checked in">
      <i class="fa fa-bell"></i>
      <span class="cq-bell-n"><?= (int)$cqPending; ?></span>
    </button>
    <?php endif; ?>
    <a href="<?= base_url('appointments'); ?>" class="mp-qa-btn blue"><i class="fa fa-calendar"></i> Appointments</a>
    <?php if($can_checkin): ?><button class="mp-qa-btn green" onclick="openWalkin()"><i class="fa fa-user-plus"></i> Walk-in Check-in</button><?php endif; ?>
  </div>
</div>

<?php
/* Every chip keeps the other chip's choice, so "This year + My patients" can be
   read together instead of one filter wiping the other out. */
$cqQs = function($over = array()) use ($queue_days, $queue_mine){
  $p = array('days' => array_key_exists('days', $over) ? $over['days'] : $queue_days);
  $mine = array_key_exists('mine', $over) ? $over['mine'] : $queue_mine;
  if($mine) $p['mine'] = 1;
  return '?' . http_build_query($p);
};
?>
<div class="cq-days">
  <span class="cq-days-lbl">Show</span>
  <a class="<?= (int)$queue_days === 1 ? 'on' : ''; ?>" href="<?= base_url('care_queue') . $cqQs(array('days' => 1)); ?>">Today</a>
  <a class="<?= (int)$queue_days === 7 ? 'on' : ''; ?>" href="<?= base_url('care_queue') . $cqQs(array('days' => 7)); ?>">7 days</a>
  <a class="<?= (int)$queue_days === 30 ? 'on' : ''; ?>" href="<?= base_url('care_queue') . $cqQs(array('days' => 30)); ?>">30 days</a>
  <a class="<?= (int)$queue_days === 365 ? 'on' : ''; ?>" href="<?= base_url('care_queue') . $cqQs(array('days' => 365)); ?>">This year</a>
  <a class="<?= (int)$queue_days === 0 ? 'on' : ''; ?>" href="<?= base_url('care_queue') . $cqQs(array('days' => 0)); ?>">All still open</a>
  <?php if(!empty($is_therapist)): ?>
    <a class="<?= (int)$queue_mine ? 'on' : ''; ?>" href="<?= base_url('care_queue') . $cqQs(array('mine' => (int)$queue_mine ? 0 : 1)); ?>">My patients</a>
  <?php endif; ?>
  <span class="cq-days-note">
    <?php if((int)$queue_mine): ?>
      Showing patients sent to you, plus patients nobody has been sent to yet.
    <?php else: ?>
      Visits still on the queue.
      <?php if((int)$queue_hidden > 0): ?>
        <b style="color:#B45309"><?= (int)$queue_hidden ?> open visit(s) are older than this window</b> &mdash;
        <a href="<?= base_url('care_queue') . $cqQs(array('days' => 0)); ?>" style="text-decoration:underline">show everything still open</a>.
      <?php endif; ?>
    <?php endif; ?>
    <?php if((int)$queue_elsewhere > 0): ?>
      <b style="color:#B45309"><?= (int)$queue_elsewhere ?> more open visit(s) are at branches you are not assigned to</b>.
    <?php endif; ?>
    Cleared visits are history &mdash; read them on the patient's page.</span>
</div>

<?php if($cqPending > 0): ?>
<div class="cq-arrive-panel" id="cqArrivals">
  <?php if(!empty($cqArrivals)): ?>
  <div class="cq-arrive-sec">Expected arrivals today — <?= count($cqArrivals); ?></div>
  <?php foreach($cqArrivals as $a): ?>
  <div class="cq-arrive-row">
    <span class="cq-row-when"><?= date('H:i', strtotime($a->scheduled_at)); ?></span>
    <span class="cq-row-who"><b><?= htmlspecialchars($a->customer_name ?: 'Patient'); ?></b>
      <span class="cq-row-sub"><?= htmlspecialchars($a->service_name ?: 'Visit'); ?><?= !empty($a->booking_ref) ? ' · ' . htmlspecialchars($a->booking_ref) : ''; ?></span></span>
    <button class="mp-qa-btn" onclick="checkinAppt(<?= (int)$a->id; ?>)"><i class="fa fa-sign-in"></i> Check in</button>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>

  <?php if(!empty($cqDue)): ?>
  <div class="cq-arrive-sec">Sessions due today, or waiting since an earlier day — <?= count($cqDue); ?></div>
  <?php foreach($cqDue as $b): ?>
  <div class="cq-arrive-row">
    <span class="cq-row-when"><?= date('D d M H:i', strtotime($b->scheduled_at)); ?></span>
    <span class="cq-row-who"><b><?= htmlspecialchars($b->patient_name ?: 'Patient'); ?></b>
      <span class="cq-row-sub"><?= htmlspecialchars($b->treatment_name ?: 'Treatment session'); ?> · session <?= (int)$b->session_no; ?> of <?= (int)$b->units_total; ?></span></span>
    <button class="mp-qa-btn" onclick="checkinSess(<?= (int)$b->id; ?>)" title="Check-in raises today's queue ticket and sends the patient to vitals"><i class="fa fa-sign-in"></i> Check in</button>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>

  <?php if(!empty($cqAhead)): ?>
  <div class="cq-arrive-sec">Booked for a later day — bring forward if they are here now — <?= count($cqAhead); ?></div>
  <?php foreach($cqAhead as $b): ?>
  <div class="cq-arrive-row">
    <span class="cq-row-when"><?= date('D d M H:i', strtotime($b->scheduled_at)); ?></span>
    <span class="cq-row-who"><b><?= htmlspecialchars($b->patient_name ?: 'Patient'); ?></b>
      <span class="cq-row-sub"><?= htmlspecialchars($b->treatment_name ?: 'Treatment session'); ?> · session <?= (int)$b->session_no; ?> of <?= (int)$b->units_total; ?></span></span>
    <button class="mp-qa-btn" onclick="checkinSess(<?= (int)$b->id; ?>)" title="Checks the patient in today and moves this booked session onto today's visit"><i class="fa fa-arrow-right"></i> Check in today</button>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>

  <?php if(!empty($cqAheadAppt)): ?>
  <div class="cq-arrive-sec">Appointments booked for a later day — <?= count($cqAheadAppt); ?></div>
  <?php foreach($cqAheadAppt as $a): ?>
  <div class="cq-arrive-row">
    <span class="cq-row-when"><?= date('D d M H:i', strtotime($a->scheduled_at)); ?></span>
    <span class="cq-row-who"><b><?= htmlspecialchars($a->customer_name ?: 'Patient'); ?></b>
      <span class="cq-row-sub"><?= htmlspecialchars($a->service_name ?: 'Visit'); ?><?= !empty($a->booking_ref) ? ' · ' . htmlspecialchars($a->booking_ref) : ''; ?></span></span>
    <button class="mp-qa-btn" onclick="checkinAppt(<?= (int)$a->id; ?>)" title="Checks the patient in today and frees the booking from its later slot"><i class="fa fa-arrow-right"></i> Check in today</button>
  </div>
  <?php endforeach; ?>
  <?php endif; ?>

  <div class="cq-arrive-foot">Checking a patient in raises their queue ticket and sends them to vitals. A booked session then draws exactly that session &mdash; nothing is charged at the desk.</div>
</div>
<?php endif; ?>

<?php
/* Queue number = position in today's arrivals by check-in time. The number is
   what gets called out, so it is the loudest thing on the card. */
$allCards = array();
foreach($queue as $st => $cards){ foreach($cards as $c){ $allCards[] = $c; } }
usort($allCards, function($a, $b){ return strcmp((string)$a->checkin_at, (string)$b->checkin_at); });
$queueNo = array();
$seq = 0;
foreach($allCards as $c){ $queueNo[(int)$c->id] = ++$seq; }

$regCards = isset($queue['waiting_nurse']) ? $queue['waiting_nurse'] : array();
$laneCards = array('assessment' => array(), 'treatment' => array(), 'untyped' => array());
foreach($queue as $st => $cards){
  foreach($cards as $c){
    $vt = $c->visit_type ?? null;
    // A visit with no type yet has no journey to belong to. It still has a
    // patient waiting, so it goes in the untyped lane rather than disappearing
    // — visits predating the visit-type migration all land here until chosen.
    if($vt === 'assessment' || $vt === 'treatment') $laneCards[$vt][$st][] = $c;
    else if($st !== 'waiting_nurse') $laneCards['untyped'][$st][] = $c;
  }
}
/* Stage columns per journey. No Closed column: a cleared visit is finished
   business, and the board is for what is still moving. It is read on the
   patient's page and the visit page. */
$stageLabels = array(
  'nursing_intake'   => 'Nursing Intake',
  'waiting_physio'   => 'Waiting for ' . mp_label('staff'),
  'with_physio'      => 'With ' . mp_label('staff'),
  'awaiting_finance' => 'Awaiting Finance',
);
$laneStages = array(
  'untyped'    => array('nursing_intake', 'waiting_physio', 'with_physio', 'awaiting_finance'),
  'assessment' => array('nursing_intake', 'waiting_physio', 'with_physio', 'awaiting_finance'),
  'treatment'  => array('nursing_intake', 'waiting_physio', 'with_physio'),
);
$laneHead = array(
  'untyped'    => array('Needs a visit type',
    'These visits were raised before visit type existed, or have not been classified yet. Choose <b>Assessment</b> or <b>Treatment session</b> on the card and it moves into the right journey.'),
  'assessment' => array('Assessment journey',
    'Intake &rarr; therapist &rarr; assessment remark &rarr; finance. The visit raises a bill, so it ends at the money desk.'),
  'treatment'  => array('Treatment session journey',
    'Intake &rarr; therapist &rarr; close. A session comes out of a paid package, so it never becomes a finance matter.'),
);

/* Counted means visible. Any open visit the lanes above do not place — a stage
   this build no longer uses, a treatment visit left at Finance by an old record —
   lands here instead of becoming invisible while the dashboard still counts it. */
$placedIds = array();
foreach($regCards as $c){ $placedIds[(int)$c->id] = 1; }
foreach($laneStages as $lane => $stages){
  foreach($stages as $st){
    foreach((isset($laneCards[$lane][$st]) ? $laneCards[$lane][$st] : array()) as $c){ $placedIds[(int)$c->id] = 1; }
  }
}
$otherCards = array();
foreach($queue as $st => $cards){
  foreach($cards as $c){ if(!isset($placedIds[(int)$c->id])) $otherCards[] = $c; }
}
?>
<div class="cq-board">

  <!-- ============ REGISTRATION — the desk decides what this visit is ============ -->
  <div class="cq-lane">
    <div class="cq-lane-head">
      <h3>Registration &mdash; Waiting for Nurse</h3>
      <span class="cq-lane-note">Every arrival lands here. Choose <b>Assessment</b> or <b>Treatment session</b> &mdash; that sets the route below.</span>
    </div>
    <div class="cq-row">
      <div class="cq-col" style="grid-column:1/-1!important;">
        <h4>Waiting for Nurse <span class="cq-badge"><?= count($regCards); ?></span></h4>
        <div class="cq-reg-grid">
          <?php foreach($regCards as $e): ?>
            <?php $stage = 'waiting_nurse'; include __DIR__ . '/_card.php'; ?>
          <?php endforeach; ?>
        </div>
        <?php if(empty($regCards)): ?><div style="font-size:12px;color:var(--mp-muted);">Nobody waiting</div><?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ============ THE TWO JOURNEYS ============ -->
  <?php foreach($laneStages as $laneType => $stages): ?>
  <div class="cq-lane <?= $laneType; ?>">
    <div class="cq-lane-head">
      <h3><?= $laneHead[$laneType][0]; ?></h3>
      <span class="cq-lane-note"><?= $laneHead[$laneType][1]; ?></span>
    </div>
    <div class="cq-row">
      <?php foreach($stages as $stage):
        $cards = isset($laneCards[$laneType][$stage]) ? $laneCards[$laneType][$stage] : array(); ?>
      <div class="cq-col">
        <h4><?= $stageLabels[$stage]; ?> <span class="cq-badge"><?= count($cards); ?></span></h4>
        <?php foreach($cards as $e): ?>
          <?php include __DIR__ . '/_card.php'; ?>
        <?php endforeach; ?>
        <?php if(empty($cards)): ?><div style="font-size:12px;color:var(--mp-muted);">Empty</div><?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>

  <!-- ============ ANYTHING THE BOARD CANNOT PLACE ============ -->
  <?php if(!empty($otherCards)): ?>
  <div class="cq-lane">
    <div class="cq-lane-head">
      <h3>Needs Attention <span class="cq-badge"><?= count($otherCards); ?></span></h3>
      <span class="cq-lane-note">These visits are still open, but they sit on a stage this board has no column for — usually an older record or a visit that was re-typed. Open one and move it where it belongs; the dashboard counts them until they are cleared.</span>
    </div>
    <div class="cq-row">
      <div class="cq-col" style="grid-column:1/-1!important;">
        <?php foreach($otherCards as $e): $stage = $e->queue_stage; include __DIR__ . '/_card.php'; endforeach; ?>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>

<div class="modal fade" id="walkinModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="walkinForm" method="post" onsubmit="return false;">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" name="checkin_key" id="wk_key">
        <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Walk-in Check-in</h4></div>
        <div class="modal-body">
          <div class="mp-form-group"><label>Patient *</label>
            <select class="mp-form-control" name="patient_id" id="wk_patient" required>
              <option value="">— choose patient —</option>
              <?php foreach($patients as $p): ?>
                <option value="<?= (int)$p->id; ?>"><?= htmlspecialchars(($p->patient_code ? $p->patient_code . ' — ' : '') . $p->customer_name); ?></option>
              <?php endforeach; ?>
            </select></div>
          <?php if(!empty($branches)): ?>
          <div class="mp-form-group"><label>Branch</label>
            <select class="mp-form-control" name="warehouse_id" id="wk_branch">
              <option value="">—</option>
              <?php foreach($branches as $b): ?><option value="<?= (int)$b->id; ?>"><?= htmlspecialchars($b->warehouse_name); ?></option><?php endforeach; ?>
            </select></div>
          <?php endif; ?>
          <div class="mp-form-group"><label>Note</label><input type="text" class="mp-form-control" name="note" id="wk_note" placeholder="e.g. Walk-in, knee pain"></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" onclick="doWalkin()">Check In</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="billModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="billForm" method="post" onsubmit="return false;">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Raise Visit Bill</h4></div>
        <div class="modal-body">
          <p style="font-size:11px;color:var(--mp-muted);margin:0 0 10px;">Assessment fee or any counter charge. Medical bills carry <b>no tax</b>. The patient pays now, or you enter a discount below &mdash; it goes to the Clinical Director (MD) to approve before it is applied. Outpatient vs Inpatient is marked by the physio after assessment (or automatically when the patient is admitted) &mdash; the bill follows that mark.</p>
          <div id="bl_lines"></div>
          <button type="button" class="btn btn-default btn-sm" onclick="addBillLine()"><i class="fa fa-plus"></i> Add line</button>
          <div class="mp-form-group" style="margin-top:10px;"><label>Note</label><input type="text" class="mp-form-control" id="bl_note" placeholder="e.g. Initial assessment"></div>
          <div style="display:flex;gap:10px;margin-top:10px;">
            <div class="mp-form-group" style="flex:1;"><label>Discount (optional)</label>
              <input type="number" min="0" step="0.01" class="mp-form-control" id="bl_discount" placeholder="Amount to discount"></div>
            <div class="mp-form-group" style="flex:2;"><label>Reason for the discount</label>
              <input type="text" class="mp-form-control" id="bl_discount_reason" placeholder="Required if you enter a discount"></div>
          </div>
          <p style="font-size:11px;color:var(--mp-muted);margin:6px 0 0;">A discount is never applied straight away &mdash; it is sent to the Clinical Director (MD) to approve first. The bill total stays as it is until it is approved.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary" onclick="saveBill()">Create Bill</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="payModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Take Payment</h4></div>
      <div class="modal-body">
        <p style="font-size:12px;margin:0 0 10px;">Open the visit's bills to receive a payment, request a waiver (approved by the Clinical Director / MD) or apply an approved discount.</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" onclick="goBills()">Open Bills</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="remarkModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="remarkForm" method="post" onsubmit="return false;">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" id="rm_id" value="">
        <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Assessment Remark &mdash; hand to Finance</h4></div>
        <div class="modal-body">
          <div class="mp-form-group"><label>What kind of care is this? *</label>
            <div class="cq-choose" id="rm_care">
              <button type="button" data-care="outpatient" onclick="pickCare(this)">Outpatient</button>
              <button type="button" data-care="inpatient" onclick="pickCare(this)">Inpatient</button>
            </div>
            <p style="font-size:11px;color:var(--mp-muted);margin:6px 0 0;">Your call after assessment &mdash; Outpatient: seen and goes home. Inpatient: admitted and stays. It marks this visit and its bills.</p>
          </div>
          <div class="mp-form-group"><label>Assessment remark (required)</label>
            <textarea class="mp-form-control" id="rm_remark" rows="3" placeholder="What was found and the treatment approach"></textarea></div>
          <div class="mp-form-group"><label>Treatment course (from Services) — needed to recommend</label>
            <select class="mp-form-control" id="rm_item">
              <option value="">— choose the treatment —</option>
              <?php foreach(($services ?? array()) as $s): ?>
              <option value="<?= (int)$s->id; ?>"><?= htmlspecialchars($s->item_name); ?> — <?= number_format((float)$s->sales_price, 2); ?></option>
              <?php endforeach; ?>
            </select></div>
          <div style="display:flex;gap:10px;">
            <div class="mp-form-group" style="flex:1;"><label>Recommended amount</label>
              <input type="number" step="0.01" min="0" class="mp-form-control" id="rm_amount" placeholder="e.g. 45000"></div>
            <div class="mp-form-group" style="flex:1;"><label>Recommended sessions</label>
              <input type="number" min="1" class="mp-form-control" id="rm_sessions" placeholder="e.g. 6"></div>
          </div>
          <p style="font-size:11px;color:var(--mp-muted);margin:0;">The remark is required before the visit can move to Finance. Finance sees bills only &mdash; never clinical notes.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-default" onclick="saveRemark(0)">Save remark</button>
          <button type="button" class="btn btn-primary" onclick="saveRemark(1)">Save &amp; push to Finance</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="vitalsModal" tabindex="-1">
  <div class="modal-dialog vt-dialog">
    <div class="modal-content">
      <form id="vitalsForm" method="post" onsubmit="return false;">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" id="vt_id" value="">
        <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Nursing Intake — Vitals</h4></div>
        <div class="modal-body">
          <p style="font-size:11px;color:var(--mp-muted);margin:0 0 10px;">Each reading keeps its own unit and time. Tick <b>Not measured</b> when a vital was not taken — that is recorded explicitly and is never the same as zero.</p>
          <div class="vt-grid" id="vt_rows">
            <div class="vt-row" data-key="bp_systolic">
              <div class="vt-head"><span class="vt-label">Systolic BP</span><label class="vt-nm-lbl"><input type="checkbox" class="vt-nm"> Not measured</label></div>
              <div class="vt-line"><input type="text" class="mp-form-control vt-val" placeholder="120"><span class="vt-unit">mmHg</span></div>
            </div>
            <div class="vt-row" data-key="bp_diastolic">
              <div class="vt-head"><span class="vt-label">Diastolic BP</span><label class="vt-nm-lbl"><input type="checkbox" class="vt-nm"> Not measured</label></div>
              <div class="vt-line"><input type="text" class="mp-form-control vt-val" placeholder="80"><span class="vt-unit">mmHg</span></div>
            </div>
            <div class="vt-row" data-key="pulse">
              <div class="vt-head"><span class="vt-label">Pulse</span><label class="vt-nm-lbl"><input type="checkbox" class="vt-nm"> Not measured</label></div>
              <div class="vt-line"><input type="text" class="mp-form-control vt-val" placeholder="72"><span class="vt-unit">bpm</span></div>
            </div>
            <div class="vt-row" data-key="temperature">
              <div class="vt-head"><span class="vt-label">Temperature</span><label class="vt-nm-lbl"><input type="checkbox" class="vt-nm"> Not measured</label></div>
              <div class="vt-line"><input type="text" class="mp-form-control vt-val" placeholder="36.8"><span class="vt-unit">°C</span></div>
            </div>
            <div class="vt-row" data-key="spo2">
              <div class="vt-head"><span class="vt-label">SpO2</span><label class="vt-nm-lbl"><input type="checkbox" class="vt-nm"> Not measured</label></div>
              <div class="vt-line"><input type="text" class="mp-form-control vt-val" placeholder="98"><span class="vt-unit">%</span></div>
            </div>
            <div class="vt-row" data-key="resp_rate">
              <div class="vt-head"><span class="vt-label">Resp rate</span><label class="vt-nm-lbl"><input type="checkbox" class="vt-nm"> Not measured</label></div>
              <div class="vt-line"><input type="text" class="mp-form-control vt-val" placeholder="16"><span class="vt-unit">/min</span></div>
            </div>
            <div class="vt-row" data-key="weight">
              <div class="vt-head"><span class="vt-label">Weight</span><label class="vt-nm-lbl"><input type="checkbox" class="vt-nm"> Not measured</label></div>
              <div class="vt-line"><input type="text" class="mp-form-control vt-val" placeholder="70"><span class="vt-unit">kg</span></div>
            </div>
            <div class="vt-row" data-key="pain_score">
              <div class="vt-head"><span class="vt-label">Pain score</span><label class="vt-nm-lbl"><input type="checkbox" class="vt-nm"> Not measured</label></div>
              <div class="vt-line"><input type="number" class="mp-form-control vt-val" min="0" max="10"><span class="vt-unit">/10</span></div>
            </div>
          </div>
          <div class="vt-foot">
            <div class="mp-form-group"><label>Measured at (optional — defaults to now)</label><input type="datetime-local" class="mp-form-control" id="vt_at"></div>
            <div class="mp-form-group"><label>Intake notes</label><textarea class="mp-form-control" id="vt_notes" rows="2"></textarea></div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-default" onclick="saveVitals(0)">Save Draft</button>
          <button type="button" class="btn btn-primary" onclick="saveVitals(1)">Finalise Vitals</button>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="encEventsModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header"><button type="button" class="close" data-dismiss="modal">&times;</button><h4 class="modal-title">Visit History</h4></div>
      <div class="modal-body"><ul id="encEvList" class="list-unstyled" style="font-size:13px;"></ul></div>
      <div class="modal-footer"><button type="button" class="btn btn-default" data-dismiss="modal">Close</button></div>
    </div>
  </div>
</div>

<script>
toastr.options = { positionClass: 'toast-top-center', closeButton: true, progressBar: true, timeOut: 3000 };
var CSRF_BODY = { '<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>' };
<?php
  $__ci =& get_instance(); $__srv = array();
  foreach($services as $s){ $__srv[] = array('id'=>(int)$s->id,'name'=>$s->item_name,'price'=>(float)$s->sales_price,'price_label'=>$__ci->currency($s->sales_price)); }
?>
var SERVICES = <?= json_encode($__srv); ?>;
var BILL_ENC = 0, PAY_ENC = 0;

function cqToggleArrivals(){
  var p = document.getElementById('cqArrivals');
  if(!p) return;
  var open = p.classList.toggle('open');
  var bell = document.getElementById('cqBell');
  if(bell) bell.setAttribute('aria-expanded', open ? 'true' : 'false');
}
function checkinAppt(apptId){
  $.post('<?= base_url('care_queue/checkin'); ?>', $.extend({}, CSRF_BODY, {appointment_id:apptId}), function(res){
    if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 700); } else { toastr.error(res.message || res.error || 'Failed'); }
  }, 'json');
}
function checkinSess(sessId){
  $.post('<?= base_url('care_queue/checkin'); ?>', $.extend({}, CSRF_BODY, {session_id:sessId}), function(res){
    if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 700); } else { toastr.error(res.message || res.error || 'Failed'); }
  }, 'json');
}
function openWalkin(){
  $('#wk_key').val('walkin:' + Date.now() + ':' + Math.random().toString(36).slice(2,10));
  $('#walkinModal').modal('show');
}
function doWalkin(){
  if(!$('#wk_patient').val()){ toastr.error('Choose a patient'); return; }
  const fd = new FormData(document.getElementById('walkinForm'));
  $.ajax({ url:'<?= base_url('care_queue/checkin'); ?>', type:'POST', data:fd, processData:false, contentType:false, dataType:'json',
    success:function(res){ if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 700); } else { toastr.error(res.message || res.error || 'Failed'); } },
    error:function(){ toastr.error('Request failed'); }
  });
}
/* Compact card — tap the summary row to expand the actions in place. */
function cqToggle(card, ev){
  if(ev && ev.target && ev.target.closest && ev.target.closest('button,select,a,input,label')) return;
  card.classList.toggle('open');
}
function cqType(id, type, ev){
  if(ev){ ev.preventDefault(); ev.stopPropagation(); }
  var card = ev && ev.target && ev.target.closest ? ev.target.closest('.cq-card') : null;
  /* Typing a visit Assessment when the patient is here for a treatment
     session is the one mis-click that moves money expectations — stop and
     say what it costs them before it happens. */
  if(type === 'assessment' && card){
    var booked = card.getAttribute('data-booked') === '1';
    var left = parseInt(card.getAttribute('data-pkg-left') || '0', 10) || 0;
    if(booked || left > 0){
      var msg = booked
        ? 'This patient came in for a booked treatment session. Typing today’s visit Assessment sends that booking back to the diary — they keep the paid session and nothing is charged. Carry on?'
        : 'This patient has ' + left + ' paid session(s) on a package. Typing today’s visit Assessment means it is billed as an assessment and draws none of them. Carry on?';
      cqConfirm(msg, { title: 'Type this visit Assessment?', okText: 'Yes, Assessment' }).then(function(yes){
        if(!yes) return;
        postVisitType(id, type, card);
      });
      return;
    }
  }
  postVisitType(id, type, card);
}
function postVisitType(id, type, card){
  var payload = $.extend({}, CSRF_BODY, {visit_type: type});
  var sel = card ? card.querySelector('.cq-nurse select') : null;
  if(type === 'treatment' && sel && sel.value) payload.nurse_user_id = sel.value;
  $.post('<?= base_url('care_queue/set_visit_type/'); ?>' + id, payload, function(res){
    if(res.status === 'success'){ toastr.success(res.message); setTimeout(function(){ location.reload(); }, 600); }
    else { toastr.error(res.message || res.error || 'Failed'); }
  }, 'json');
}
function cqNurse(id, userId){
  if(!userId) return;
  $.post('<?= base_url('care_queue/assign_nurse/'); ?>' + id, $.extend({}, CSRF_BODY, {nurse_user_id: userId}), function(res){
    if(res.status === 'success'){ toastr.success(res.message); setTimeout(function(){ location.reload(); }, 600); }
    else { toastr.error(res.message || res.error || 'Failed'); }
  }, 'json');
}
/* Send the patient to a named therapist, or move them to another one. The visit
   does not change stage — this only says who it is waiting for. */
function cqRoute(id, userId){
  if(!userId) return;
  $.post('<?= base_url('care_queue/route_therapist/'); ?>' + id, $.extend({}, CSRF_BODY, {clinician_user_id: userId}), function(res){
    if(res.status === 'success'){ toastr.success(res.message); setTimeout(function(){ location.reload(); }, 600); }
    else { toastr.error(res.message || res.error || 'Failed'); }
  }, 'json');
}

function moveEnc(id, to){
  $.post('<?= base_url('care_queue/move/'); ?>'+id, $.extend({}, CSRF_BODY, {to:to}), function(res){
    if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 700); } else { toastr.error(res.message || res.error || 'Failed'); }
  }, 'json');
}
function completeIntake(id, type){
  var treatment = type === 'treatment';
  cqPrompt({
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
    $.post('<?= base_url('care_queue/intake_complete/'); ?>'+id, $.extend({}, CSRF_BODY, {note: note}), function(res){
      if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 700); } else { toastr.error(res.message || res.error || 'Failed'); }
    }, 'json');
  });
}
function closeVisit(id){
  cqPrompt({
    title: 'Close this visit',
    message: 'Add an outcome note so the visit record is complete.',
    placeholder: 'Outcome note',
    multiline: true,
    okText: 'Close Visit',
    danger: true
  }).then(function(note){
    if(note === null) return;
    $.post('<?= base_url('care_queue/move/'); ?>'+id, $.extend({}, CSRF_BODY, {to:'closed', note:note}), function(res){
      if(res.status==='success'){
        toastr.success(res.message);
        /* A course is rarely one session long. When today's session is drawn
           and more are owed, book the return while the patient is still in
           front of you — that is the connect between one session and the next. */
        if(res.next){ offerNext(res.next); return; }
        setTimeout(()=>location.reload(), 700);
      } else { toastr.error(res.message || res.error || 'Failed'); }
    }, 'json');
  });
}
function offerNext(n){
  var name = n.patient_name ? n.patient_name + '’s' : 'The patient’s';
  var left = parseInt(n.sessions_left || 0, 10) || 0;
  cqConfirm(
    name + ' session is done and drawn from the package. ' + left + ' session(s) are still owed on ' + (n.treatment_name || 'the package') + ' — book the next one now?',
    { title: 'Book the next session', okText: 'Book it now' }
  ).then(function(yes){
    if(!yes){ setTimeout(()=>location.reload(), 400); return; }
    bookNext(n.entitlement_id, parseFloat(n.fee) || 0, true);
  });
}
function bookNext(entId, fee, reloadAfter){
  mpBookSession({
    title: 'Book the next session',
    message: 'Pick the day and the time the patient returns. On that day the session appears on the Care Queue, so they can be checked in for vitals and their ticket raised.'
  }).then(function(when){
    if(!when){ if(reloadAfter) setTimeout(()=>location.reload(), 400); return; }
    $.post('<?= base_url('care_queue/book_session'); ?>', $.extend({}, CSRF_BODY, {entitlement_id: entId, scheduled_at: when, fee: fee}), function(res){
      if(res.status==='success'){ toastr.success(res.message); if(reloadAfter) setTimeout(()=>location.reload(), 900); }
      else { toastr.error(res.message || res.error || 'Failed'); if(reloadAfter) setTimeout(()=>location.reload(), 1400); }
    }, 'json');
  });
}

/* ---- Visit billing (waiting-for-nurse desk) ---- */
function billPrompt(id){
  BILL_ENC = id;
  $('#bl_lines').empty(); addBillLine();
  $('#bl_note').val('');
  $('#bl_discount').val(''); $('#bl_discount_reason').val('');
  $('#billModal').modal('show');
}
function pickCare(btn){
  const $c = $(btn).closest('.cq-choose');
  $c.find('button').removeClass('sel-inpatient sel-outpatient');
  $(btn).addClass(btn.dataset.care === 'inpatient' ? 'sel-inpatient' : 'sel-outpatient');
}
function addBillLine(){
  const opts = SERVICES.map(s => '<option value="'+s.id+'" data-price="'+s.price+'">'+String(s.name).replace(/</g,'&lt;')+' — '+(s.price_label||s.price)+'</option>').join('');
  const row = $('<div class="bill-line">' +
    '<select class="mp-form-control bl-item">' + (opts || '<option value="">— no service items —</option>') + '</select>' +
    '<input type="number" min="1" step="1" value="1" class="mp-form-control bl-qty" title="Qty">' +
    '<input type="number" min="0" step="0.01" class="mp-form-control bl-price" placeholder="Price">' +
    '<button type="button" class="btn-xs" title="Remove" onclick="$(this).closest(\'.bill-line\').remove()">&times;</button>' +
    '</div>');
  row.find('.bl-item').on('change', function(){
    const p = $(this).find(':selected').data('price');
    if(p !== undefined) row.find('.bl-price').val(p);
  });
  $('#bl_lines').append(row);
  if(SERVICES.length){ row.find('.bl-item').trigger('change'); }
}
function saveBill(){
  const disc = parseFloat($('#bl_discount').val());
  const discReason = $.trim($('#bl_discount_reason').val() || '');
  if(!isNaN(disc) && disc > 0 && !discReason){ toastr.error('Add a reason for the discount — the MD needs it to approve'); return; }
  const data = $.extend({}, CSRF_BODY, {
    note: $('#bl_note').val(),
    discount_amount: (!isNaN(disc) && disc > 0) ? disc : '', discount_reason: discReason
  });
  let i = 0;
  $('#bl_lines .bill-line').each(function(){
    const $r = $(this), price = parseFloat($r.find('.bl-price').val());
    if(isNaN(price) || price < 0) return;
    data['line_item_id['+i+']'] = $r.find('.bl-item').val() || '';
    data['line_qty['+i+']']      = $r.find('.bl-qty').val() || 1;
    data['line_price['+i+']']    = price;
    i++;
  });
  if(i === 0){ toastr.error('Add at least one priced line'); return; }
  $.post('<?= base_url('care_queue/bill/'); ?>'+BILL_ENC, data, function(res){
    if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 700); } else { toastr.error(res.message || res.error || 'Failed'); }
  }, 'json');
}
function payPrompt(id){
  PAY_ENC = id;
  $('#payModal').modal('show');
}
function goBills(){ location.href = '<?= base_url('care_queue/encounter/'); ?>' + PAY_ENC; }

/* ---- Assessment remark (physio → finance) ---- */
function remarkPrompt(id){
  $('#rm_id').val(id); $('#rm_remark').val(''); $('#rm_amount').val(''); $('#rm_sessions').val(''); $('#rm_item').val('');
  $('#rm_care button').removeClass('sel-inpatient sel-outpatient');
  $('#remarkModal').modal('show');
}
function saveRemark(push){
  const remark = $('#rm_remark').val().trim();
  if(!remark){ toastr.error('The assessment remark is required'); return; }
  const careBtn = $('#rm_care button.sel-inpatient, #rm_care button.sel-outpatient').first();
  if(!careBtn.length){ toastr.error('Mark this visit Outpatient or Inpatient — that is the physiotherapist\'s call'); return; }
  const amtV = $('#rm_amount').val(), sesV = $('#rm_sessions').val();
  if(((amtV !== '' && parseFloat(amtV) > 0) || (sesV !== '' && parseInt(sesV) > 0)) && !$('#rm_item').val()){
    toastr.error('Choose the treatment service being recommended'); return;
  }
  $.post('<?= base_url('care_queue/remark/'); ?>'+$('#rm_id').val(), $.extend({}, CSRF_BODY, {
    remark: remark,
    recommended_amount: amtV,
    recommended_sessions: sesV,
    recommended_item_id: $('#rm_item').val(),
    care_type: careBtn.data('care'),
    push: push ? 1 : 0
  }), function(res){
    if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 700); } else { toastr.error(res.message || res.error || 'Failed'); }
  }, 'json');
}
function vitalsPrompt(id){ $('#vt_id').val(id); $('#vitalsForm')[0].reset(); $('#vt_id').val(id); $('#vitalsModal').modal('show'); }
function saveVitals(finalize){
  const id = $('#vt_id').val();
  const data = $.extend({}, CSRF_BODY, {finalize: finalize ? 1 : 0, vitals_notes: $('#vt_notes').val()});
  const at = $('#vt_at').val() ? $('#vt_at').val().replace('T',' ') + ':00' : '';
  let i = 0;
  $('#vt_rows .vt-row').each(function(){
    const $tr = $(this), nm = $tr.find('.vt-nm').is(':checked'), val = $tr.find('.vt-val').val();
    if(!nm && val === '') return; // untouched row — skip entirely
    data['vit_key['+i+']']   = $tr.data('key');
    data['vit_label['+i+']'] = $tr.find('.vt-label').text();
    data['vit_value['+i+']'] = val;
    data['vit_unit['+i+']']  = $tr.find('.vt-unit').text();
    data['vit_nm['+i+']']    = nm ? 1 : 0;
    data['vit_at['+i+']']    = at;
    i++;
  });
  if(i === 0){ toastr.error('Enter at least one vital or mark it not measured'); return; }
  $.post('<?= base_url('care_queue/vitals/'); ?>'+id, data, function(res){
    // Vitals belong to Nursing Intake — the patient is already in that
    // stage when this modal is offered, so there is no stage hop here.
    if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(), 700); }
    else { toastr.error(res.message || res.error || 'Failed'); }
  }, 'json');
}
function viewEncEvents(id){
  $.get('<?= base_url('care_queue/events/'); ?>'+id, function(res){
    let html = '';
    const esc = s => $('<div>').text(s == null ? '' : s).html();
    (res.events||[]).forEach(function(e){
      let line = '<b>' + esc(e.label || 'Update') + '</b>';
      if(e.from_label && e.to_label) line += ' — ' + esc(e.from_label) + ' → ' + esc(e.to_label);
      else if(e.to_label) line += ' — ' + esc(e.to_label);
      if(e.note) line += ' — ' + esc(e.note);
      html += '<li>' + line + ' <span style="color:#94a3b8">by ' + esc(e.created_by_name || 'System') + ' · ' + esc(e.created_at) + '</span></li>';
    });
    $('#encEvList').html(html || '<li>No history</li>');
    $('#encEventsModal').modal('show');
  }, 'json');
}
</script>
<script>$(".care_queue-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
