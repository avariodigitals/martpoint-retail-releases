<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $this->load->view('components/dialog'); ?>
<?php $this->load->view('components/card_lanes'); ?>
<style>
.enc-card{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:14px!important}
.enc-full{margin:0 0 14px 0!important}
.enc-card h4{font-size:11px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:var(--mp-muted)!important;margin:0 0 10px 0!important}
.enc-pill{font-size:10px!important;font-weight:700!important;padding:2px 8px!important;border-radius:12px!important}
.enc-pill.draft{background:#FEF3C7!important;color:#92400E!important}
.enc-pill.final,.enc-pill.completed,.enc-pill.reviewed{background:#D1FAE5!important;color:#065F46!important}
.enc-pill.result_received{background:#DBEAFE!important;color:#1E40AF!important}
.enc-pill.requested,.enc-pill.pending{background:#F1F5F9!important;color:#475569!important}
.enc-pill.cancelled,.enc-pill.declined{background:#FEE2E2!important;color:#991B1B!important}
.enc-pill.superseded{background:#F5F3FF!important;color:#5B21B6!important}
.enc-pill.awaiting_verification{background:#FFEDD5!important;color:#9A3412!important}
.vt-table{width:100%!important;font-size:12px!important}
.vt-table td,.vt-table th{padding:4px 6px!important;border-bottom:1px solid var(--mp-border)!important}
.vt-nm{color:#991B1B!important;font-size:10px!important;font-weight:700!important}
.enc-mini-btn{font-size:11px!important;padding:4px 9px!important;border-radius:6px!important;border:1px solid var(--mp-border)!important;background:#fff!important;cursor:pointer!important}
.enc-mini-btn.primary{background:var(--mp-primary)!important;color:#fff!important;border-color:var(--mp-primary)!important}
.enc-row{display:flex!important;justify-content:space-between!important;align-items:center!important;padding:6px 0!important;border-bottom:1px dashed var(--mp-border)!important;font-size:12px!important}
.enc-note{background:#EFF6FF!important;border:1px solid #BFDBFE!important;color:#1E40AF!important;font-size:11px!important;padding:6px 10px!important;border-radius:8px!important;margin-bottom:8px!important}
.enc-warn{background:#FEF3C7!important;border:1px solid #F59E0B!important;color:#92400E!important;font-size:11px!important;padding:6px 10px!important;border-radius:8px!important;margin-bottom:8px!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($enc->encounter_code); ?> — <?= htmlspecialchars($patient->customer_name ?? 'Patient'); ?></h2>
    <div class="mp-page-sub">
      <?= htmlspecialchars($patient->patient_code ?? ''); ?> · Stage: <b><?= htmlspecialchars(mp_code_label($enc->queue_stage)); ?></b>
      · Arrived <?= $enc->checkin_at ? date('d M H:i', strtotime($enc->checkin_at)) : '—'; ?>
      <?php if($enc->clinician_user_id): ?> · Clinician: <?= htmlspecialchars(mp_first_name($enc->clinician_user_id, '—')); ?><?php endif; ?>
    </div>
  </div>
  <div style="display:flex;gap:10px;">
    <a href="<?= base_url('care_queue'); ?>" class="mp-qa-btn"><i class="fa fa-arrow-left"></i> Queue</a>
    <a href="<?= base_url('care_queue/queue_ticket/' . (int)$enc->id); ?>" target="_blank" class="mp-qa-btn"><i class="fa fa-ticket"></i> Queue Ticket</a>
    <?php if(!empty($session_ent)): ?>
      <?php $sLeft = (int)((float)$session_ent->units_total - (float)$session_ent->units_used); ?>
      <a href="<?= base_url('care_queue/session_ticket/' . (int)$enc->id); ?>" target="_blank" class="mp-qa-btn" title="<?= $sLeft; ?> paid session(s) still to be drawn on <?= htmlspecialchars($session_ent->treatment_name ?: 'this package'); ?> — today’s counts when the physio completes the visit"><i class="fa fa-credit-card"></i> Session Ticket (<?= $sLeft; ?> left)</a>
    <?php else: ?>
      <button class="mp-qa-btn" disabled style="opacity:.55;cursor:not-allowed;" title="This patient has no paid sessions left on a session package"><i class="fa fa-credit-card"></i> No active sessions</button>
    <?php endif; ?>
    <?php if($can['assign']): ?>
      <select id="assignSel" class="mp-form-control" style="width:auto;">
        <option value="">Assign to…</option>
        <?php foreach($staff as $s): ?><option value="<?= (int)$s->id; ?>"><?= htmlspecialchars($s->username); ?></option><?php endforeach; ?>
      </select>
      <button class="mp-qa-btn blue" onclick="doAssign()"><i class="fa fa-user-md"></i> Assign</button>
    <?php endif; ?>
  </div>
</div>

<?php $view_only = ($stage === 'closed' && !physio_can('encounters_edit_closed')); ?>
<div class="enc-card enc-full" id="encNotes" style="<?= ($business_only || $stage === 'waiting_nurse' || $view_only) ? '' : 'display:none;'; ?>">
<?php if($view_only): ?>
  <div class="enc-note"><i class="fa fa-lock"></i> This visit is closed — the record is view only. Editing it needs the Edit Closed Visits right, which a Store Admin can grant from the Roles screen.</div>
<?php endif; ?>
<?php if($business_only): ?>
  <div class="enc-note"><i class="fa fa-briefcase"></i> Business view — bills and payments only. Clinical records are not shown to finance.</div>
<?php endif; ?>
<?php if($stage === 'waiting_nurse'): ?>
  <div class="enc-note"><i class="fa fa-user-plus"></i> Check-in desk — register and bill the patient here. Vitals and visit history begin at Nursing Intake.</div>
<?php endif; ?>
</div>

<div class="mp-lanes">

<?php
/*
 * Stage contract:
 *   waiting_nurse    — registration + billing only; no vitals, no history.
 *   nursing_intake+  — clinical panels per role.
 *   business_only    — a finance viewer sees bills, never clinical data.
 */
$show_clinical = !$business_only && $stage !== 'waiting_nurse';
?>

<!-- ============ VISIT BILLS ============ -->
<?php if(!empty($can['bill_view'])): ?>
<div class="enc-card">
  <h4>Visit Bills</h4>
  <?php foreach($bills as $b): $bDue = round((float)$b->grand_total - (float)$b->paid_amount, 2); ?>
    <div class="enc-row">
      <span>
        <a href="<?= base_url('patient_billing/view/' . (int)$b->id); ?>"><?= htmlspecialchars($b->sales_code); ?></a>
        <?php $bCare = $b->care_type ?? null; ?>
        <span class="enc-pill <?= $bCare === 'inpatient' ? 'draft' : 'final'; ?>" style="<?= $bCare === 'inpatient' ? 'background:#EEF2FF;color:#4338CA;' : ''; ?>"><?= $bCare ? ucfirst($bCare) : 'Care type not marked'; ?></span>
        <span class="enc-pill <?= $bDue <= 0.009 ? 'final' : 'draft'; ?>"><?= $bDue <= 0.009 ? 'Paid' : ('Due ' . number_format($bDue, 2)); ?></span><br>
        <small style="color:#94a3b8;"><?= htmlspecialchars($b->sales_note ?: ''); ?> · <?= htmlspecialchars($b->sales_date); ?></small>
      </span>
      <span style="display:flex;gap:4px;">
        <?php if(!empty($can['bill_view']) && $bDue > 0.009): ?>
          <button class="enc-mini-btn primary" onclick="location.href='<?= base_url('patient_billing/view/' . (int)$b->id); ?>'"><i class="fa fa-money"></i> Pay / Waive</button>
        <?php endif; ?>
        <a class="enc-mini-btn" href="<?= base_url('patient_billing/view/' . (int)$b->id); ?>" title="Open bill"><i class="fa fa-arrow-right"></i></a>
      </span>
    </div>
  <?php endforeach; ?>
  <?php if(empty($bills)): ?><div style="font-size:12px;color:var(--mp-muted);">No bills on this visit yet.</div><?php endif; ?>
  <?php if(!empty($can['bill_add']) && $stage !== 'closed'): ?>
    <button class="enc-mini-btn primary" style="margin-top:8px;" onclick="billModal()"><i class="fa fa-plus"></i> Raise visit bill</button>
  <?php endif; ?>
  <?php if($enc->assessment_remark): ?>
    <div class="enc-row" style="margin-top:8px;">
      <span><b>Physio recommendation</b><br>
        <?= number_format((float)$enc->recommended_amount, 2); ?> · <?= (int)$enc->recommended_sessions; ?> session(s)<br>
        <small style="color:#94a3b8;"><?= nl2br(htmlspecialchars($enc->assessment_remark)); ?></small></span>
    </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- ============ ASSESSMENT REMARK (physio → finance) ============ -->
<?php if(!empty($can['remark'])): ?>
<div class="enc-card">
  <h4>Assessment Remark &mdash; hand to Finance</h4>
  <?php if($enc->assessment_remark): ?>
    <div class="enc-row"><span><b>Remark</b><br><?= nl2br(htmlspecialchars($enc->assessment_remark)); ?></span></div>
    <div class="enc-row"><span>Recommended</span><span><b><?= number_format((float)$enc->recommended_amount, 2); ?></b> · <?= (int)$enc->recommended_sessions; ?> session(s)</span></div>
    <div style="font-size:11px;color:#94a3b8;">Recorded <?= htmlspecialchars($enc->remark_at ?: ''); ?></div>
  <?php else: ?>
    <div class="enc-warn"><i class="fa fa-exclamation-triangle"></i> An assessment remark is required before this visit can go to Finance.</div>
  <?php endif; ?>
  <form onsubmit="return saveRemark(false);" style="margin-top:8px;">
    <div class="enc-bill-lbl">What kind of care is this? *</div>
    <?php $curCare = null; foreach(($bills ?? array()) as $b){ if(!empty($b->care_type)) $curCare = $b->care_type; } ?>
    <div class="enc-care-row" id="encCare">
      <button type="button" data-care="outpatient" class="<?= $curCare === 'outpatient' ? 'on-out' : ''; ?>" onclick="encPickCare(this)">Outpatient</button>
      <button type="button" data-care="inpatient" class="<?= $curCare === 'inpatient' ? 'on-in' : ''; ?>" onclick="encPickCare(this)">Inpatient</button>
    </div>
    <p class="hint" style="margin:6px 0 8px;">The physiotherapist's call after assessment &mdash; Outpatient: seen and goes home. Inpatient: admitted and stays. It marks this visit and its bills.</p>
    <div class="mp-form-group"><textarea class="mp-form-control" id="cq_remark" rows="3" placeholder="What was found and the treatment approach *"><?= htmlspecialchars($enc->assessment_remark ?: ''); ?></textarea></div>
    <div class="enc-bill-lbl">Treatment course (from Services) — needed to recommend</div>
    <select class="mp-form-control" id="cq_item" style="margin-bottom:8px;">
      <option value="">— choose the treatment —</option>
      <?php foreach(($services ?? array()) as $s): ?>
      <option value="<?= (int)$s->id; ?>" <?= (int)($enc_plan_item ?? 0) === (int)$s->id ? 'selected' : ''; ?>><?= htmlspecialchars($s->item_name); ?> — <?= number_format((float)$s->sales_price, 2); ?></option>
      <?php endforeach; ?>
    </select>
    <div style="display:flex;gap:8px;">
      <input type="number" step="0.01" min="0" class="mp-form-control" id="cq_amount" placeholder="Recommended amount" value="<?= $enc->recommended_amount !== null ? htmlspecialchars((string)$enc->recommended_amount) : ''; ?>">
      <input type="number" min="1" class="mp-form-control" id="cq_sessions" placeholder="Sessions" value="<?= $enc->recommended_sessions !== null ? (int)$enc->recommended_sessions : ''; ?>">
    </div>
    <div style="display:flex;gap:6px;margin-top:8px;flex-wrap:wrap;">
      <button type="submit" class="enc-mini-btn">Save remark</button>
      <button type="button" class="enc-mini-btn" onclick="addRecToBill()"><i class="fa fa-plus"></i> Add to bill</button>
      <?php if($enc->queue_stage === 'with_physio'): ?>
        <button type="button" class="enc-mini-btn primary" onclick="saveRemark(true)">Save &amp; push to Finance</button>
      <?php endif; ?>
    </div>
  </form>
</div>
<?php endif; ?>

<!-- ============ NURSING INTAKE / VITALS ============ -->
<?php if($show_clinical && physio_can('vitals_view')): ?>
<div class="enc-card">
  <h4>Nursing Intake — Vitals</h4>
  <?php foreach($vitals_sets as $vs): ?>
    <div class="enc-row">
      <span>Set #<?= (int)$vs->id; ?> <span class="enc-pill <?= $vs->status; ?>"><?= htmlspecialchars(mp_code_label($vs->status)); ?></span>
        by <?= htmlspecialchars($vs->recorded_by_name ?: '—'); ?> · <?= $vs->created_at; ?></span>
    </div>
    <table class="vt-table" style="margin-bottom:8px;">
      <?php foreach($vs->entries as $e): ?>
      <tr><td><?= htmlspecialchars($e->label ?: $e->vital_key); ?></td>
        <td><?= $e->not_measured ? '<span class="vt-nm">NOT MEASURED</span>' : htmlspecialchars($e->value_text . ' ' . $e->unit); ?></td>
        <td style="color:#94a3b8;"><?= $e->measured_at ? date('H:i', strtotime($e->measured_at)) : ''; ?></td></tr>
      <?php endforeach; ?>
      <?php if($vs->notes): ?><tr><td colspan="3" style="color:#64748b;font-style:italic;"><?= htmlspecialchars($vs->notes); ?></td></tr><?php endif; ?>
    </table>
  <?php endforeach; ?>
  <?php if(empty($vitals_sets)): ?><div style="font-size:12px;color:var(--mp-muted);">No vitals recorded yet.</div><?php endif; ?>
  <?php if($can['vitals_add'] && $enc->queue_stage !== 'closed'): ?>
    <a class="enc-mini-btn primary" href="<?= base_url('care_queue'); ?>" onclick="return true;" style="display:inline-block;margin-top:6px;">Record vitals from queue board</a>
  <?php endif; ?>
  <?php if($can['intake'] && $enc->queue_stage === 'nursing_intake'): ?>
    <button class="enc-mini-btn primary" style="margin-top:6px;" onclick="completeIntake()">Complete intake → hand to <?= htmlspecialchars(mp_label('staff')); ?></button>
  <?php endif; ?>
  <?php if($enc->intake_completed_at): ?>
    <div style="font-size:11px;color:#065F46;margin-top:6px;"><i class="fa fa-check"></i> Intake completed <?= $enc->intake_completed_at; ?></div>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- ============ ASSESSMENTS ============ -->
<?php if($show_clinical && physio_can('assessments_view')): ?>
<div class="enc-card">
  <h4>Assessments</h4>
  <?php foreach($assessments as $a): ?>
    <div class="enc-row">
      <span><a href="<?= base_url('assessments/form/' . (int)$a->id); ?>"><?= $a->template_key === 'physio_initial' ? 'Physiotherapy Initial Assessment' : htmlspecialchars($a->template_key); ?> · #<?= (int)$a->id; ?></a>
        <span class="enc-pill <?= $a->status; ?>"><?= $a->status; ?></span><br>
        <small style="color:#94a3b8;"><?= htmlspecialchars($a->assessor_name ?: ''); ?> · <?= $a->created_at; ?> · v<?= (int)$a->template_version; ?></small></span>
      <a class="enc-mini-btn" href="<?= base_url('assessments/download/' . (int)$a->id); ?>" target="_blank" title="Download printable copy"><i class="fa fa-print"></i></a>
    </div>
  <?php endforeach; ?>
  <?php if(empty($assessments)): ?><div style="font-size:12px;color:var(--mp-muted);">No assessments yet.</div><?php endif; ?>
  <?php if($can['assessments']): ?>
    <form method="get" action="<?= base_url('assessments/form'); ?>" style="margin-top:8px;display:flex;gap:6px;">
      <input type="hidden" name="encounter_id" value="<?= (int)$enc->id; ?>">
      <select name="template_id" class="mp-form-control" required>
        <option value="">New assessment — choose template…</option>
        <?php foreach($templates as $t): ?>
          <option value="<?= (int)$t->id; ?>"><?= htmlspecialchars($t->name); ?> (v<?= (int)$t->version; ?><?= $t->provisional ? ', provisional' : ''; ?>)</option>
        <?php endforeach; ?>
      </select>
      <button class="enc-mini-btn primary">Start</button>
    </form>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- ============ INVESTIGATIONS ============ -->
<?php if($show_clinical && physio_can('investigations_view')): ?>
<div class="enc-card">
  <h4>Investigations</h4>
  <?php foreach($investigations as $i): ?>
    <div class="enc-row">
      <span><?= htmlspecialchars($i->request_ref); ?> — <?= htmlspecialchars($i->test_name); ?>
        <span class="enc-pill <?= $i->status; ?>"><?= str_replace('_',' ',$i->status); ?></span>
        <?= $i->facility_name ? '<br><small style="color:#94a3b8;">At: ' . htmlspecialchars($i->facility_name) . '</small>' : ''; ?>
      </span>
      <span style="display:flex;gap:4px;">
        <?php if($i->status === 'requested' && $can['inv_request']): ?>
          <button class="enc-mini-btn" onclick="invPending(<?= (int)$i->id; ?>)">Mark sent</button>
        <?php endif; ?>
        <?php if(in_array($i->status, array('requested','pending')) && $can['inv_result']): ?>
          <button class="enc-mini-btn primary" onclick="invResult(<?= (int)$i->id; ?>)">Enter result</button>
          <button class="enc-mini-btn" onclick="invCancel(<?= (int)$i->id; ?>)">Cancel</button>
        <?php endif; ?>
        <?php if($i->status === 'result_received' && $can['inv_review']): ?>
          <button class="enc-mini-btn primary" onclick="invReview(<?= (int)$i->id; ?>)">Review</button>
        <?php endif; ?>
      </span>
    </div>
  <?php endforeach; ?>
  <?php if(empty($investigations)): ?><div style="font-size:12px;color:var(--mp-muted);">No investigations on this visit.</div><?php endif; ?>
  <?php if($can['inv_request']): ?>
  <form onsubmit="return invRequest(this);" style="margin-top:8px;">
    <input type="hidden" name="encounter_id" value="<?= (int)$enc->id; ?>">
    <input type="hidden" name="patient_id" value="<?= (int)$enc->patient_id; ?>">
    <div class="mp-form-group"><input type="text" name="test_name" class="mp-form-control" placeholder="Test / investigation *" required></div>
    <div style="display:flex;gap:6px;">
      <select name="category" class="mp-form-control"><option value="">Category…</option><option value="imaging">Imaging</option><option value="lab">Laboratory</option><option value="functional">Functional</option><option value="other">Other</option></select>
      <select name="priority" class="mp-form-control"><option value="routine">Routine</option><option value="urgent">Urgent</option></select>
    </div>
    <div class="mp-form-group" style="margin-top:6px;"><input type="text" name="facility_name" class="mp-form-control" placeholder="External facility (blank = in-house)"></div>
    <div class="mp-form-group"><input type="text" name="external_ref" class="mp-form-control" placeholder="External / referral reference"></div>
    <button class="enc-mini-btn primary">Request investigation</button>
  </form>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- ============ CONSENTS ============ -->
<?php if($can['docs_view']): ?>
<div class="enc-card">
  <h4>Consents (paper)</h4>
  <?php foreach($consents as $c): ?>
    <div class="enc-row">
      <span><?= htmlspecialchars($c->title); ?> <span class="enc-pill <?= $c->status; ?>"><?= str_replace('_',' ',$c->status); ?></span><br>
        <small style="color:#94a3b8;"><?= $c->consent_type; ?> · generated <?= $c->created_date; ?></small></span>
      <span style="display:flex;gap:4px;">
        <a class="enc-mini-btn" target="_blank" href="<?= base_url('consents/print_form/' . (int)$c->id); ?>"><i class="fa fa-print"></i></a>
        <?php if(in_array($c->status, array('pending','awaiting_verification')) && $can['consent_manage']): ?>
          <button class="enc-mini-btn" onclick="consentUpload(<?= (int)$c->id; ?>)">Upload signed</button>
        <?php endif; ?>
        <?php if($c->status === 'awaiting_verification' && $can['docs_release']): ?>
          <button class="enc-mini-btn primary" onclick="consentVerify(<?= (int)$c->id; ?>)">Verify</button>
        <?php endif; ?>
      </span>
    </div>
  <?php endforeach; ?>
  <?php if(empty($consents)): ?><div style="font-size:12px;color:var(--mp-muted);">No consents on file.</div><?php endif; ?>
  <?php if($can['consent_manage']): ?>
    <button class="enc-mini-btn primary" style="margin-top:8px;" onclick="consentGen()">Generate consent form</button>
  <?php endif; ?>
  <div style="font-size:10px;color:#94a3b8;margin-top:6px;">Digital signing is pending the agreed identity/signature method — paper workflow only.</div>
</div>
<?php endif; ?>

<!-- ============ DOCUMENTS ============ -->
<?php if($can['docs_view']): ?>
<div class="enc-card">
  <h4>Patient Documents <small style="text-transform:none;">(private storage)</small></h4>
  <?php foreach($documents as $d): ?>
    <div class="enc-row">
      <span><?= htmlspecialchars($d->title); ?> <span class="enc-pill <?= $d->status; ?>"><?= str_replace('_',' ',$d->status); ?></span><br>
        <small style="color:#94a3b8;"><?= $d->category; ?> · v<?= (int)$d->version_no; ?> · <?= $d->file_size ? round($d->file_size/1024) . ' KB' : ''; ?></small></span>
      <span style="display:flex;gap:4px;">
        <a class="enc-mini-btn" href="<?= base_url('patient_docs/download/' . (int)$d->id); ?>"><i class="fa fa-download"></i></a>
        <button class="enc-mini-btn" onclick="docLog(<?= (int)$d->id; ?>)"><i class="fa fa-list"></i></button>
      </span>
    </div>
  <?php endforeach; ?>
  <?php if(empty($documents)): ?><div style="font-size:12px;color:var(--mp-muted);">No documents.</div><?php endif; ?>
  <?php if($can['docs_upload']): ?>
  <form onsubmit="return docUpload(this);" enctype="multipart/form-data" style="margin-top:8px;">
    <input type="hidden" name="patient_id" value="<?= (int)$enc->patient_id; ?>">
    <input type="hidden" name="episode_id" value="<?= (int)$enc->episode_id; ?>">
    <div class="mp-form-group"><input type="text" name="title" class="mp-form-control" placeholder="Document title *" required></div>
    <div class="mp-form-group">
      <select name="category" class="mp-form-control"><option value="clinical">Clinical</option><option value="investigation">Investigation</option><option value="consent">Consent</option><option value="id">Identification</option><option value="other">Other</option></select>
    </div>
    <div class="mp-form-group"><input type="file" name="doc_file" class="mp-form-control" required></div>
    <button class="enc-mini-btn primary">Upload (private)</button>
  </form>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- ============ HISTORY ============ -->
<?php if($show_clinical): ?>
<div class="enc-card">
  <h4>Visit History</h4>
  <ul class="list-unstyled" style="font-size:12px;margin:0;">
    <?php foreach($events as $ev): ?>
      <li style="padding:3px 0;border-bottom:1px dashed var(--mp-border);">
        <b><?= htmlspecialchars(mp_event_label($ev->event)); ?></b>
        <?php if($ev->from_stage && $ev->to_stage): ?>
          — <?= htmlspecialchars(mp_stage_label($ev->from_stage) . ' → ' . mp_stage_label($ev->to_stage)); ?>
        <?php elseif($ev->to_stage): ?>
          — <?= htmlspecialchars(mp_stage_label($ev->to_stage)); ?>
        <?php endif; ?>
        <?= $ev->note ? ' — ' . htmlspecialchars($ev->note) : ''; ?>
        <span style="color:#94a3b8;"><?= htmlspecialchars($ev->created_by_name ?: 'System'); ?> · <?= $ev->created_at; ?></span>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

</div>

<!-- ============ REFERRAL PARTNERS ============ -->
<?php if(!empty($referral_panel)): ?>
<div style="margin-top:14px;"><?= $referral_panel; ?></div>
<?php endif; ?>

<!-- hidden upload form for signed consent -->
<form id="consentUploadForm" style="display:none;" enctype="multipart/form-data">
  <input type="file" name="doc_file" id="consentFile" onchange="consentFileChosen()">
</form>

<!-- Raise visit bill -->
<style>
  .enc-bill-wrap{position:fixed;inset:0;display:none;align-items:center;justify-content:center;padding:20px 14px;z-index:100050;}
  .enc-bill-wrap.open{display:flex;}
  .enc-bill-back{position:absolute;inset:0;background:rgba(15,23,42,.45);backdrop-filter:blur(3px);-webkit-backdrop-filter:blur(3px);}
  .enc-bill-card{position:relative;width:100%;max-width:480px;background:#fff;border-radius:18px;padding:20px;box-shadow:0 28px 70px rgba(15,23,42,.28);font-family:inherit;max-height:86vh;overflow-y:auto;}
  .enc-bill-card h4{margin:0 0 4px;font-size:17px;font-weight:700;color:#0F172A;}
  .enc-bill-card p.hint{margin:0 0 14px;font-size:12px;color:#64748B;line-height:1.5;}
  .enc-bill-lbl{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.4px;color:#64748B;margin:12px 0 6px;}
  .enc-care-row{display:grid;grid-template-columns:1fr 1fr;gap:8px;}
  .enc-care-row button{padding:12px 8px;border-radius:12px;border:1.5px solid #E2E8F0;background:#fff;font-size:13px;font-weight:700;cursor:pointer;font-family:inherit;color:#0F172A;}
  .enc-care-row button.on-out{background:#ECFDF5;border-color:#10B981;color:#065F46;}
  .enc-care-row button.on-in{background:#EEF2FF;border-color:#6366F1;color:#4338CA;}
  .enc-bill-line{display:flex;gap:6px;margin-bottom:8px;}
  .enc-bill-line select,.enc-bill-line input{padding:10px 12px;border:1px solid #E2E8F0;border-radius:10px;font-size:13px;font-family:inherit;}
  .enc-bill-line select{flex:3;}
  .enc-bill-line input.qty{width:62px;flex:none;}
  .enc-bill-line input.price{flex:2;}
  .enc-bill-acts{display:flex;gap:10px;margin-top:16px;}
  .enc-bill-acts button{flex:1;padding:13px 0;border:none;border-radius:12px;font-size:14px;font-weight:600;cursor:pointer;font-family:inherit;}
  .enc-bill-cancel{background:#F1F5F9;color:#334155;}
  .enc-bill-save{background:#0D9488;color:#fff;}
</style>
<div class="enc-bill-wrap" id="encBillWrap">
  <div class="enc-bill-back" onclick="encBillClose()"></div>
  <div class="enc-bill-card">
    <h4>Raise Visit Bill</h4>
    <p class="hint">Assessment fee or any counter charge for this visit. Outpatient vs Inpatient is marked by the physio after assessment (or automatically when the patient is admitted) &mdash; the bill follows that mark.</p>
    <div class="enc-bill-lbl">Lines</div>
    <div id="encBillLines"></div>
    <button type="button" class="enc-mini-btn" onclick="encAddLine()"><i class="fa fa-plus"></i> Add line</button>
    <div class="enc-bill-lbl" style="margin-top:16px;">Discount (optional)</div>
    <div class="enc-bill-line">
      <input type="number" min="0" step="0.01" class="price eb-discount" placeholder="Amount to discount" style="flex:2;">
      <input type="text" class="eb-discount-reason" placeholder="Reason (required if you enter a discount)" style="flex:3;padding:10px 12px;border:1px solid #E2E8F0;border-radius:10px;font-size:13px;font-family:inherit;">
    </div>
    <p class="hint" style="margin:6px 0 0;">Medical bills carry <b>no tax</b>. A discount is sent to the Clinical Director (MD) to approve first &mdash; it is not applied until it is approved.</p>
    <div class="enc-bill-acts">
      <button type="button" class="enc-bill-cancel" onclick="encBillClose()">Cancel</button>
      <button type="button" class="enc-bill-save" onclick="encBillSave()">Create Bill</button>
    </div>
  </div>
</div>

<script>
var CSRF_BODY = { '<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>' };
var ENC_ID = <?= (int)$enc->id; ?>;
<?php
  $__ci =& get_instance(); $__srv = array();
  foreach(($services ?? array()) as $s){ $__srv[] = array('id'=>(int)$s->id,'name'=>$s->item_name,'price'=>(float)$s->sales_price,'price_label'=>$__ci->currency($s->sales_price)); }
?>
var BILL_SERVICES = <?= json_encode($__srv); ?>;

function billModal(){
  document.getElementById('encBillLines').innerHTML = '';
  var d = document.querySelector('.eb-discount'), dr = document.querySelector('.eb-discount-reason');
  if(d) d.value = ''; if(dr) dr.value = '';
  encAddLine();
  document.getElementById('encBillWrap').classList.add('open');
}
function encBillClose(){ document.getElementById('encBillWrap').classList.remove('open'); }
function encPickCare(btn){
  document.querySelectorAll('#encCare button').forEach(function(b){ b.className = ''; });
  btn.className = btn.dataset.care === 'inpatient' ? 'on-in' : 'on-out';
}
function encAddLine(){
  var wrap = document.getElementById('encBillLines');
  var row = document.createElement('div');
  row.className = 'enc-bill-line';
  var opts = BILL_SERVICES.map(function(s){ return '<option value="'+s.id+'" data-price="'+s.price+'">'+String(s.name).replace(/</g,'&lt;')+' — '+(s.price_label||s.price)+'</option>'; }).join('');
  row.innerHTML = '<select class="eb-item">'+(opts || '<option value="">— no service items —</option>')+'</select>' +
    '<input type="number" min="1" step="1" value="1" class="qty eb-qty">' +
    '<input type="number" min="0" step="0.01" class="price eb-price" placeholder="Price">' +
    '<button type="button" class="enc-mini-btn" onclick="this.parentNode.remove()">&times;</button>';
  var sel = row.querySelector('.eb-item');
  sel.addEventListener('change', function(){
    var p = sel.options[sel.selectedIndex].getAttribute('data-price');
    if(p !== null) row.querySelector('.eb-price').value = p;
  });
  wrap.appendChild(row);
  if(BILL_SERVICES.length) sel.dispatchEvent(new Event('change'));
}
function encBillSave(){
  var disc = parseFloat((document.querySelector('.eb-discount')||{}).value);
  var discReason = ((document.querySelector('.eb-discount-reason')||{}).value || '').trim();
  if(!isNaN(disc) && disc > 0 && !discReason){ toastr.error('Add a reason for the discount — the MD needs it to approve'); return; }
  var data = $.extend({}, CSRF_BODY, {
    discount_amount: (!isNaN(disc) && disc > 0) ? disc : '', discount_reason: discReason
  });
  var i = 0;
  document.querySelectorAll('#encBillLines .enc-bill-line').forEach(function(r){
    var price = parseFloat(r.querySelector('.eb-price').value);
    if(isNaN(price) || price < 0) return;
    data['line_item_id['+i+']'] = r.querySelector('.eb-item').value || '';
    data['line_qty['+i+']']      = r.querySelector('.eb-qty').value || 1;
    data['line_price['+i+']']    = price;
    i++;
  });
  if(i === 0){ toastr.error('Add at least one priced line'); return; }
  $.post('<?= base_url('care_queue/bill/'); ?>' + ENC_ID, data, function(res){
    if(res.status==='success'){ toastr.success(res.message); setTimeout(function(){ location.reload(); }, 700); }
    else toastr.error(res.message || res.error || 'Failed');
  }, 'json');
}

function completeIntake(){
  $.post('<?= base_url('care_queue/intake_complete/'); ?>' + ENC_ID, CSRF_BODY, function(res){
    if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(),700); } else toastr.error(res.message||'Failed');
  }, 'json');
}
function saveRemark(push){
  var remark = $('#cq_remark').val().trim();
  if(!remark){ toastr.error('The assessment remark is required'); return false; }
  var careBtn = document.querySelector('#encCare button.on-in, #encCare button.on-out');
  if(!careBtn){ toastr.error('Mark this visit Outpatient or Inpatient — that is the physiotherapist\'s call'); return false; }
  var amtV = $('#cq_amount').val(), sesV = $('#cq_sessions').val();
  if(((amtV !== '' && parseFloat(amtV) > 0) || (sesV !== '' && parseInt(sesV) > 0)) && !$('#cq_item').val()){
    toastr.error('Choose the treatment service being recommended'); return false;
  }
  $.post('<?= base_url('care_queue/remark/'); ?>' + ENC_ID, $.extend({}, CSRF_BODY, {
    remark: remark,
    recommended_amount: amtV,
    recommended_sessions: sesV,
    recommended_item_id: $('#cq_item').val(),
    care_type: careBtn.dataset.care,
    push: push ? 1 : 0
  }), function(res){
    if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(),700); } else toastr.error(res.message||'Failed');
  }, 'json');
  return false;
}
function addRecToBill(){
  var amt = parseFloat($('#cq_amount').val());
  if(isNaN(amt) || amt <= 0){ toastr.error('Enter the recommended amount first'); return; }
  if(!$('#cq_item').val()){ toastr.error('Choose the treatment service being recommended'); return; }
  $.post('<?= base_url('care_queue/recommend_to_bill/'); ?>' + ENC_ID, $.extend({}, CSRF_BODY, {
    amount: amt, sessions: $('#cq_sessions').val(), note: $('#cq_remark').val(),
    recommended_item_id: $('#cq_item').val()
  }), function(res){
    if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(),700); } else toastr.error(res.message||'Failed');
  }, 'json');
}
function doAssign(){
  var uid = $('#assignSel').val(); if(!uid) return;
  $.post('<?= base_url('care_queue/assign/'); ?>' + ENC_ID, $.extend({}, CSRF_BODY, {user_id:uid}), function(res){
    if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(),700); } else toastr.error(res.message||'Failed');
  }, 'json');
}
function invRequest(f){
  var fd = $(f).serializeArray().concat([{name:'<?= $this->security->get_csrf_token_name(); ?>', value:'<?= $this->security->get_csrf_hash(); ?>'}]);
  $.post('<?= base_url('investigations/request'); ?>', $.param(fd), function(res){
    if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(),700); } else toastr.error(res.message||'Failed');
  }, 'json');
  return false;
}
function invPending(id){
  $.post('<?= base_url('investigations/pending/'); ?>'+id, CSRF_BODY, function(res){
    if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(),700); } else toastr.error(res.message||'Failed');
  }, 'json');
}
function invResult(id){
  cqPrompt({
    title: 'Record result',
    message: 'Leave the summary blank if you want to attach the report file next.',
    placeholder: 'Result summary',
    multiline: true,
    okText: 'Save Result'
  }).then(function(summary){
    if(summary === null) return;
    $.post('<?= base_url('investigations/result/'); ?>'+id, $.extend({}, CSRF_BODY, {result_summary:summary}), function(res){
      if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(),700); } else toastr.error(res.message||'Failed');
    }, 'json');
  });
}
function invReview(id){
  cqPrompt({
    title: 'Review investigation',
    message: 'Add a note about what the result shows. Optional.',
    placeholder: 'Review note',
    multiline: true,
    okText: 'Save Review'
  }).then(function(note){
    if(note === null) return;
    $.post('<?= base_url('investigations/review/'); ?>'+id, $.extend({}, CSRF_BODY, {note:note}), function(res){
      if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(),700); } else toastr.error(res.message||'Failed');
    }, 'json');
  });
}
function invCancel(id){
  cqPrompt({
    title: 'Cancel investigation',
    message: 'A cancellation reason is required and stays on the record.',
    placeholder: 'Reason for cancelling',
    multiline: true,
    required: true,
    okText: 'Cancel Investigation',
    danger: true
  }).then(function(reason){
    if(reason === null) return;
    $.post('<?= base_url('investigations/cancel/'); ?>'+id, $.extend({}, CSRF_BODY, {reason:reason}), function(res){
      if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(),700); } else toastr.error(res.message||'Failed');
    }, 'json');
  });
}
var _consentId = 0;
function consentGen(){
  cqPrompt({
    title: 'Generate consent form',
    message: 'Choose the kind of consent to prepare for signature.',
    okText: 'Generate',
    options: [
      { value: 'general_treatment', label: 'General treatment' },
      { value: 'procedure', label: 'Procedure' },
      { value: 'home_visit', label: 'Home visit' },
      { value: 'data_sharing', label: 'Data sharing' }
    ]
  }).then(function(type){
    if(type === null) return;
    $.post('<?= base_url('consents/generate'); ?>', $.extend({}, CSRF_BODY, {patient_id:<?= (int)$enc->patient_id; ?>, encounter_id:ENC_ID, consent_type:type}), function(res){
      if(res.status==='success'){ toastr.success(res.message); window.open(res.print_url, '_blank'); setTimeout(()=>location.reload(),800); }
      else toastr.error(res.message||'Failed');
    }, 'json');
  });
}
function consentUpload(id){ _consentId = id; $('#consentFile').click(); }
function consentFileChosen(){
  var fd = new FormData(document.getElementById('consentUploadForm'));
  fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
  $.ajax({ url:'<?= base_url('consents/upload_signed/'); ?>' + _consentId, type:'POST', data:fd, processData:false, contentType:false, dataType:'json',
    success:function(res){ if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(),700); } else toastr.error(res.message||'Failed'); },
    error:function(){ toastr.error('Upload failed'); }
  });
}
function consentVerify(id){
  cqPrompt({
    title: 'Verify signed consent',
    message: 'Tick every signatory whose signature is visibly on the scanned form.',
    okText: 'Mark Verified',
    multi: true,
    required: true,
    options: [
      { value: 'patient_or_guardian', label: 'Patient or guardian' },
      { value: 'clinician', label: 'Clinician' }
    ]
  }).then(function(signed){
    if(signed === null) return;
    var arr = [].concat(signed);
    var data = $.extend({}, CSRF_BODY); arr.forEach(function(s,i){ data['signed['+i+']'] = s; });
    $.post('<?= base_url('consents/verify/'); ?>'+id, data, function(res){
      if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(),700); } else toastr.error(res.message||'Failed');
    }, 'json');
  });
}
function docUpload(f){
  var fd = new FormData(f);
  fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
  $.ajax({ url:'<?= base_url('patient_docs/upload'); ?>', type:'POST', data:fd, processData:false, contentType:false, dataType:'json',
    success:function(res){ if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(),700); } else toastr.error(res.message||'Failed'); },
    error:function(){ toastr.error('Upload failed'); }
  });
  return false;
}
function docLog(id){
  $.get('<?= base_url('patient_docs/log/'); ?>'+id, function(res){
    var msg = (res.versions||[]).map(function(v){
      return v.version_label + ' · ' + v.file_type + ' · ' + v.by_name + ' · ' + v.created_at;
    }).join('\n');
    if((res.log||[]).length){
      msg += '\n\nAccess:\n' + res.log.map(function(l){
        return l.label + ' by ' + l.by_name + ' · ' + l.created_at;
      }).join('\n');
    }
    var safe = $('<div>').text(msg || 'No history').html().replace(/\n/g, '<br>');
    toastr.info(safe, 'Document history', {escapeHtml:false, timeOut:0, extendedTimeOut:0, closeButton:true});
  }, 'json');
}
</script>
<script>$(".care_queue-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
