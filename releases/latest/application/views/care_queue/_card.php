<?php
/* One card: queue number, name, visit id, type chip and a money dot.
   Open by default; tapping the header collapses it. $e and $stage are
   set by the board loop; the rest of the scope is the Care Queue page. */
$eid     = (int)$e->id;
$vt      = $e->visit_type ?? null;
$bs      = isset($bill_summary[$eid]) ? $bill_summary[$eid] : null;
$sEnt    = isset($session_ent[(int)$e->patient_id]) ? $session_ent[(int)$e->patient_id] : null;
$sLeft   = $sEnt ? (int)((float)$sEnt->units_total - (float)$sEnt->units_used) : 0;
$sFee    = ($sEnt && (float)$sEnt->units_total > 0) ? round((float)$sEnt->funded_amount / (float)$sEnt->units_total, 2) : 0;
/* A visit raised from the diary carries its booking on the check-in key —
   that is how the desk can see today's visit is somebody's paid slot. */
$bookedSess = !empty($e->checkin_key) && strpos((string)$e->checkin_key, 'sess:') === 0;
$dot     = $bs ? $bs['state'] : 'none';
$chip    = $vt === 'treatment' ? 'treatment' : ($vt === 'assessment' ? 'assessment' : 'none');
$chipTxt = $vt === 'treatment' ? 'Session' : ($vt === 'assessment' ? 'Assessment' : 'Not typed');
$nurseLbl = !empty($e->nurse_name) ? physio_staff_label($e->nurse_name, $e->nurse_role_id ?? null) : '';
?>
<div class="cq-card open" onclick="cqToggle(this,event)" data-booked="<?= $bookedSess ? '1' : ''; ?>" data-pkg-left="<?= (int)$sLeft; ?>">
  <div class="cq-top">
    <div class="cq-no"><?= (int)($queueNo[$eid] ?? 0); ?></div>
    <div class="cq-who">
      <div class="nm"><?= htmlspecialchars($e->customer_name ?: '—'); ?></div>
      <div class="sub">
        <span class="cq-dot <?= htmlspecialchars($dot); ?>"></span>
        <span><?= htmlspecialchars($e->encounter_code ?: ''); ?></span>
        <span class="cq-chip <?= $chip; ?>"><?= $chipTxt; ?></span>
        <?php if($vt === 'treatment' && $nurseLbl): ?>
          <span style="color:#065F46;font-weight:700;"><?= htmlspecialchars($nurseLbl); ?></span>
        <?php endif; ?>
      </div>
    </div>
    <i class="fa fa-chevron-down cq-caret"></i>
  </div>

  <div class="cq-body" onclick="event.stopPropagation()">
    <div class="cq-meta">
      In <?= $e->checkin_at ? date('H:i', strtotime($e->checkin_at)) : '—'; ?>
      <?= $e->clinician_name ? ' &middot; ' . ($stage === 'with_physio' ? 'With' : 'Sent to') . ' <b>' . htmlspecialchars(physio_staff_label($e->clinician_name, $e->clinician_role_id ?? null)) . '</b>' : ''; ?>
      <?php if($vt === 'treatment'): ?>
        &middot; Nurse tasked: <b><?= htmlspecialchars($nurseLbl ?: 'not yet chosen'); ?></b>
      <?php endif; ?>
    </div>

    <?php if(!empty($e->handover_note)): ?>
    <div class="cq-handover">
      <i class="fa fa-comment-o"></i>
      <b>Nurse note<?= $e->handover_by ? ' &mdash; ' . htmlspecialchars($e->handover_by) : ''; ?>:</b>
      <?= htmlspecialchars($e->handover_note); ?>
    </div>
    <?php endif; ?>

    <?php if($stage === 'waiting_nurse' || !$vt): ?>
    <div class="cq-choose">
      <button type="button" class="<?= $vt === 'assessment' ? 'sel-assessment' : ''; ?>" onclick="cqType(<?= $eid; ?>,'assessment',event)">Assessment</button>
      <?php /* A treatment session draws from a paid package. With no sessions
               available the route is closed — the desk raises an assessment
               bill instead of sending a patient the clinic cannot bill. */ ?>
      <button type="button" class="<?= $vt === 'treatment' ? 'sel-treatment' : ''; ?>" <?= $sEnt ? '' : 'disabled style="opacity:.55;cursor:not-allowed;" title="No paid sessions on a package — sell one first, or take the Assessment route"'; ?> onclick="cqType(<?= $eid; ?>,'treatment',event)">Treatment session</button>
    </div>
    <?php endif; ?>

    <?php if($vt === 'treatment' && $stage !== 'closed'): ?>
    <div class="cq-nurse">
      <label>Nurse tasked</label>
      <select onchange="cqNurse(<?= $eid; ?>, this.value)">
        <option value="">— choose —</option>
        <?php foreach($nurses as $n): ?>
        <option value="<?= (int)$n->id; ?>" <?= (int)($e->nurse_user_id ?? 0) === (int)$n->id ? 'selected' : ''; ?>><?= htmlspecialchars(trim($n->first_name . ' ' . $n->last_name) ?: $n->username); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <?php endif; ?>

    <?php if(!empty($can_route) && ($stage === 'waiting_physio' || $stage === 'with_physio')): ?>
    <div class="cq-nurse">
      <label><?= $stage === 'with_physio' ? 'Re-route to therapist' : 'Send to therapist'; ?></label>
      <select onchange="cqRoute(<?= $eid; ?>, this.value)">
        <option value="" disabled <?= (int)($e->clinician_user_id ?? 0) ? '' : 'selected'; ?>>— choose —</option>
        <?php foreach($therapists as $t): ?>
        <option value="<?= (int)$t->id; ?>" <?= (int)($e->clinician_user_id ?? 0) === (int)$t->id ? 'selected' : ''; ?>><?= htmlspecialchars(trim($t->first_name . ' ' . $t->last_name) ?: $t->username); ?></option>
        <?php endforeach; ?>
      </select>
      <div style="font-size:11px;color:var(--mp-muted);margin-top:4px;">
        <?= $stage === 'with_physio'
          ? 'Moves the patient to that therapist now — the visit history records who changed it.'
          : 'Puts the patient on that therapist’s queue. They still take the patient with “Start Consult”.'; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if($sEnt && $stage !== 'closed'): ?>
      <div class="cq-money">
        <i class="fa fa-credit-card"></i>
        <b><?= (int)$sLeft; ?> of <?= (int)$sEnt->units_total; ?> session(s) left</b>
        on <?= htmlspecialchars($sEnt->treatment_name ?: 'this package'); ?>
        <?= $bookedSess ? ' &middot; this visit is for a booked session' : ''; ?>
        <?= $vt === 'assessment' ? ' &middot; an Assessment visit draws none of them' : ''; ?>
      </div>
    <?php endif; ?>

    <?php if(!empty($can_bill_view) && $bs): ?>
      <div class="cq-money <?= htmlspecialchars($bs['state']); ?>">
        <i class="fa fa-file-text-o"></i>
        <?= (int)$bs['count']; ?> bill(s) &middot;
        <?= $bs['state'] === 'paid' ? 'PAID in full' : ('Due ' . number_format($bs['due'], 2)); ?>
      </div>
    <?php endif; ?>

    <div class="acts">
      <?php if($stage === 'waiting_nurse'): ?>
        <?php if(!empty($can_bill)): ?>
          <button class="btn-xs primary" onclick="billPrompt(<?= $eid; ?>)"><i class="fa fa-file-text-o"></i> Bill</button>
        <?php endif; ?>
        <a class="btn-xs" target="_blank" href="<?= base_url('care_queue/queue_ticket/' . $eid); ?>"><i class="fa fa-ticket"></i> Queue Ticket</a>
        <?php if($vt === 'treatment'): ?>
          <?php if($sEnt): ?>
            <?php $sLeft = (int)((float)$sEnt->units_total - (float)$sEnt->units_used); ?>
            <a class="btn-xs" target="_blank" href="<?= base_url('care_queue/session_ticket/' . $eid); ?>" title="<?= $sLeft; ?> paid session(s) still to be drawn on <?= htmlspecialchars($sEnt->treatment_name ?: 'this package'); ?> — today’s counts when the physio completes the visit"><i class="fa fa-credit-card"></i> Session Ticket (<?= $sLeft; ?>)</a>
          <?php else: ?>
            <button class="btn-xs" disabled style="opacity:.55;cursor:not-allowed;" title="This patient has no paid sessions left on a session package"><i class="fa fa-credit-card"></i> No active sessions</button>
          <?php endif; ?>
        <?php endif; ?>
        <?php if(!empty($can_bill_view) && $bs && $bs['state'] !== 'paid'): ?>
          <a class="btn-xs" href="<?= base_url('care_queue/encounter/' . $eid); ?>"><i class="fa fa-money"></i> Pay / Waive</a>
        <?php endif; ?>
        <?php if(!empty($can_checkin) || !empty($can_vitals)): ?>
          <button class="btn-xs <?= $vt ? 'primary' : ''; ?>" onclick="moveEnc(<?= $eid; ?>,'nursing_intake')">Call in &rarr; Intake</button>
        <?php endif; ?>
      <?php endif; ?>

      <?php if($stage === 'nursing_intake'): ?>
        <?php if(!empty($can_vitals)): ?>
          <button class="btn-xs primary" onclick="vitalsPrompt(<?= $eid; ?>)">Vitals</button>
          <button class="btn-xs" onclick="completeIntake(<?= $eid; ?>, <?= $vt === 'treatment' ? "'treatment'" : 'null'; ?>)">Complete Intake</button>
        <?php endif; ?>
        <?php if(!empty($can_bill)): ?>
          <button class="btn-xs" onclick="billPrompt(<?= $eid; ?>)"><i class="fa fa-plus"></i> Add bill</button>
        <?php endif; ?>
      <?php endif; ?>

      <?php if($stage === 'waiting_physio'): ?>
        <?php if(!empty($can_physio) || !empty($can_vitals) || !empty($can_checkin)): ?>
          <button class="btn-xs primary" onclick="moveEnc(<?= $eid; ?>,'with_physio')">Start Consult</button>
        <?php endif; ?>
        <?php if(!empty($can_bill)): ?>
          <button class="btn-xs" onclick="billPrompt(<?= $eid; ?>)"><i class="fa fa-plus"></i> Add bill</button>
        <?php endif; ?>
        <?php if(!empty($can_bill_view) && $bs && $bs['state'] !== 'paid'): ?>
          <a class="btn-xs" href="<?= base_url('care_queue/encounter/' . $eid); ?>"><i class="fa fa-money"></i> Pay / Waive</a>
        <?php endif; ?>
        <?php if(!empty($can_checkin)): ?>
          <button class="btn-xs" onclick="moveEnc(<?= $eid; ?>,'waiting_nurse')">Back to Nurse</button>
        <?php endif; ?>
      <?php endif; ?>

      <?php if($stage === 'with_physio'): ?>
        <?php if($vt === 'assessment' && !empty($can_physio)): ?>
          <button class="btn-xs primary" onclick="remarkPrompt(<?= $eid; ?>)"><i class="fa fa-stethoscope"></i> Remark &amp; &rarr; Finance</button>
        <?php endif; ?>
        <?php if(!empty($can_physio)): ?>
          <button class="btn-xs" onclick="closeVisit(<?= $eid; ?>)"><?= $vt === 'treatment' ? 'Complete Session' : 'Close Visit'; ?></button>
        <?php endif; ?>
      <?php endif; ?>

      <?php if($stage === 'awaiting_finance'): ?>
        <?php if(!empty($can_bill)): ?>
          <button class="btn-xs primary" onclick="payPrompt(<?= $eid; ?>)"><i class="fa fa-money"></i> Take payment</button>
        <?php endif; ?>
        <?php if(!empty($can_finance) || !empty($can_physio)): ?>
          <button class="btn-xs" onclick="closeVisit(<?= $eid; ?>)">Close Visit</button>
        <?php endif; ?>
      <?php endif; ?>

      <?php if($vt === 'treatment' && $stage !== 'closed' && $sEnt && !empty($can_checkin)): ?>
        <button class="btn-xs" onclick="bookNext(<?= (int)$sEnt->id; ?>, <?= $sFee; ?>)" title="Arrange the next session of this course — it goes on the diary and appears here on its day"><i class="fa fa-calendar-plus-o"></i> Book the next session</button>
      <?php endif; ?>

      <?php if($stage === 'closed'): ?>
        <?php if(!empty($can_finance) || !empty($can_physio)): ?>
          <a class="btn-xs" href="<?= base_url('care_queue/encounter/' . $eid); ?>"><i class="fa fa-file-text-o"></i> Bills</a>
        <?php endif; ?>
      <?php else: ?>
        <a class="btn-xs" href="<?= base_url('care_queue/encounter/' . $eid); ?>"><i class="fa fa-folder-open"></i> Open</a>
        <?php if(!empty($can_vitals) || !empty($can_physio)): ?>
          <button class="btn-xs" onclick="viewEncEvents(<?= $eid; ?>)" title="Visit history"><i class="fa fa-history"></i></button>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>
