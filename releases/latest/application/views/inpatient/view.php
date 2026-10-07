<?php $this->load->view('admin/desktop/_styles'); $CI =& get_instance(); ?>
<style>
.iv-box{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:14px!important;margin-bottom:14px!important}
.iv-box h4{font-size:11px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:var(--mp-muted)!important;margin:0 0 10px 0!important}
.iv-table{width:100%!important;border-collapse:collapse!important;font-size:12px!important}
.iv-table th{text-align:left!important;padding:5px 8px!important;border-bottom:1px solid var(--mp-border)!important;font-size:10px!important;text-transform:uppercase!important;color:var(--mp-muted)!important}
.iv-table td{padding:6px 8px!important;border-bottom:1px solid #F1F5F9!important;vertical-align:top!important}
.iv-badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:800;text-transform:uppercase}
.iv-open{background:#DBEAFE;color:#1E40AF}.iv-done{background:#D1FAE5;color:#065F46}
.iv-overdue{background:#FEE2E2;color:#991B1B}.iv-suppressed,.iv-cancelled{background:#F3F4F6;color:#6B7280}
.iv-recommended{background:#FEF3C7;color:#92400E}.iv-approved{background:#D1FAE5;color:#065F46}
.iv-rejected{background:#FEE2E2;color:#991B1B}.iv-completed{background:#DBEAFE;color:#1E40AF}
.iv-active{background:#D1FAE5;color:#065F46}.iv-deceased{background:#111827;color:#F9FAFB}.iv-discharged{background:#DBEAFE;color:#1E40AF}
.iv-out{background:#FEF3C7;color:#92400E}.iv-returned{background:#D1FAE5;color:#065F46}.iv-requested{background:#FEF3C7;color:#92400E}
.iv-departed{background:#FEF3C7;color:#92400E}.iv-procedure{background:#E0E7FF;color:#3730A3}.iv-reviewed{background:#D1FAE5;color:#065F46}.iv-ordered{background:#DBEAFE;color:#1E40AF}.iv-provided{background:#D1FAE5;color:#065F46}
.iv-inp{padding:6px 9px!important;border:1px solid var(--mp-border)!important;border-radius:8px!important;font-size:12px!important;margin-right:6px!important;background:var(--mp-bg,#fff)!important;color:inherit!important}
.iv-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.iv-warn{background:#FEF3C7;border:1px solid #FDE68A;border-radius:9px;padding:8px 10px;font-size:12px;color:#92400E;margin-bottom:10px}
.iv-table td:first-child{width:auto;max-width:140px}
/* Narrow screens: the overview uses a two-column grid and the label column a
   fixed width, so at phone widths the tables pushed past the right edge and
   got clipped. Stack the grid and let every table scroll within its box
   instead of clipping its cells. */
@media (max-width:900px),(max-width:1024px) and (orientation:portrait){
  .iv-grid{grid-template-columns:1fr}
  .iv-box{overflow-x:auto;-webkit-overflow-scrolling:touch}
  .iv-table{min-width:340px}
  .iv-table td:first-child{width:130px}
}
</style>

<div class="mp-page-head">
  <div><h2><?= htmlspecialchars($adm->admission_code); ?> — <?= htmlspecialchars($adm->full_name); ?></h2>
  <div class="mp-page-sub">
    Admitted <?= htmlspecialchars($adm->admitted_at); ?> · Clinician: <?= htmlspecialchars($adm->clinician_name ?? '—'); ?> ·
    Package decision: <strong><?= htmlspecialchars($adm->outpatient_decision); ?></strong>
    <?php if($occupancy): ?> · Bed: <?= htmlspecialchars($occupancy->ward_name . ' / ' . $occupancy->bed_label); ?><?php endif; ?>
  </div></div>
  <div><span class="iv-badge iv-<?= htmlspecialchars($adm->status); ?>" style="font-size:13px"><?= htmlspecialchars(mp_code_label($adm->status)); ?></span>
    <?php if($adm->closed_at): ?><span style="font-size:12px;color:var(--mp-muted)">closed <?= htmlspecialchars($adm->closed_at); ?></span><?php endif; ?></div>
</div>

<?php if($adm->status === 'active'): ?>
<div class="iv-box" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
  <h4 style="margin:0!important">Actions</h4>
  <?php if($can['manage'] && $occupancy): ?>
    <form style="display:inline" onsubmit="return ivPost(event,'inpatient/request_transfer/<?= (int)$adm->id; ?>','Transfer requested — porter task open')">
      <select class="iv-inp" name="to_bed_id" required><option value="">— Transfer to bed —</option>
        <?php foreach($beds as $b): ?><option value="<?= (int)$b->id; ?>"><?= htmlspecialchars($b->bed_label); ?></option><?php endforeach; ?></select>
      <input class="iv-inp" name="reason" placeholder="reason"><button class="mp-qa-btn blue">Transfer</button>
    </form>
  <?php endif; ?>
  <?php if($can['leave']): ?><button class="mp-qa-btn" onclick="ivLeave(<?= (int)$adm->id; ?>)">Request leave</button><?php endif; ?>
  <?php if($can['referral']): ?><button class="mp-qa-btn" onclick="ivReferral(<?= (int)$adm->patient_id; ?>,<?= (int)$adm->id; ?>)">External referral</button><?php endif; ?>
  <?php if($can['recommend']): ?><button class="mp-qa-btn blue" onclick="ivRecommend(<?= (int)$adm->id; ?>)">Recommend discharge</button><?php endif; ?>
  <?php if($can['deceased']): ?><button class="mp-qa-btn" style="background:#111827;color:#fff" onclick="ivDeceased(<?= (int)$adm->patient_id; ?>)">Record deceased</button><?php endif; ?>
</div>
<?php endif; ?>

<div class="iv-box">
  <h4>Overview</h4>
  <div class="iv-grid">
    <div>
      <table class="iv-table">
        <tr><td style="color:var(--mp-muted);width:160px">Patient</td><td><a href="<?= base_url('patients/profile/'.(int)$adm->patient_id); ?>"><?= htmlspecialchars($adm->full_name); ?></a> <span style="color:var(--mp-muted)">(<?= htmlspecialchars($adm->patient_code ?: '—'); ?>)</span></td></tr>
        <tr><td style="color:var(--mp-muted)">Admission account</td><td><strong><?= htmlspecialchars($adm->admission_code); ?></strong></td></tr>
        <tr><td style="color:var(--mp-muted)">Admitted</td><td><?= htmlspecialchars($adm->admitted_at); ?></td></tr>
        <tr><td style="color:var(--mp-muted)">Ward / Bed</td><td><?= $occupancy ? htmlspecialchars($occupancy->ward_name . ' / ' . $occupancy->bed_label) : '—'; ?></td></tr>
        <tr><td style="color:var(--mp-muted)">Responsible clinician</td><td><?= htmlspecialchars($adm->clinician_name ?? '—'); ?></td></tr>
        <tr><td style="color:var(--mp-muted)">Status</td><td><span class="iv-badge iv-<?= htmlspecialchars($adm->status); ?>"><?= htmlspecialchars(mp_code_label($adm->status)); ?></span></td></tr>
      </table>
    </div>
    <div>
      <table class="iv-table">
        <tr><td style="color:var(--mp-muted)">Admission reason</td><td><?= htmlspecialchars($adm->reason ?? '—'); ?></td></tr>
        <tr><td style="color:var(--mp-muted)">Care plan</td><td><?= htmlspecialchars($adm->care_plan ?? '—'); ?></td></tr>
        <tr><td style="color:var(--mp-muted)">Package decision</td><td><strong><?= htmlspecialchars($adm->outpatient_decision); ?></strong><?= $adm->outpatient_decision_note ? ' — ' . htmlspecialchars($adm->outpatient_decision_note) : ''; ?></td></tr>
        <?php if($occupancy): ?><tr><td style="color:var(--mp-muted)">Bed since</td><td><?= htmlspecialchars($occupancy->from_at); ?></td></tr><?php endif; ?>
      </table>
    </div>
  </div>
</div>

<div class="iv-grid">
  <div>
    <div class="iv-box">
      <h4>Nursing tasks — <span style="color:#991B1B">overdue highlighted</span></h4>
      <table class="iv-table">
        <tr><th>Task</th><th>Due</th><th>Status</th><th>Done by</th><th></th></tr>
        <?php foreach($tasks as $t): ?>
        <tr>
          <td><?= htmlspecialchars($t->label); ?></td>
          <td><?= htmlspecialchars($t->due_at); ?></td>
          <td><span class="iv-badge iv-<?= $t->is_overdue ? 'overdue' : htmlspecialchars($t->status); ?>"><?= $t->is_overdue ? 'overdue' : htmlspecialchars($t->status); ?></span></td>
          <td><?= $t->done_by ? 'u#' . (int)$t->done_by . ' ' . htmlspecialchars(substr((string)$t->done_at,11,5)) : '—'; ?></td>
          <td><?php if($t->status === 'open' && $can['nursing']): ?><button class="mp-qa-btn green" onclick="ivTask(<?= (int)$t->id; ?>)">Done</button><?php endif; ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if(empty($tasks)): ?><tr><td colspan="5" style="color:var(--mp-muted)">No tasks generated yet.</td></tr><?php endif; ?>
      </table>
      <?php if($can['nursing'] && $adm->status==='active'): ?>
      <form style="margin-top:8px" onsubmit="return ivPost(event,'inpatient/add_task/<?= (int)$adm->id; ?>','Task added')">
        <input class="iv-inp" name="label" placeholder="Ad hoc task" required>
        <input class="iv-inp" name="due_at" placeholder="Y-m-d H:i (blank = +2h)" style="width:170px">
        <button class="mp-qa-btn">Add</button>
      </form>
      <?php endif; ?>
    </div>

    <div class="iv-box">
      <h4>Notes — observations / handover / weekly review</h4>
      <table class="iv-table">
        <tr><th>Type</th><th>Shift</th><th>Note</th><th>By</th><th>At</th></tr>
        <?php foreach($notes as $n): ?>
        <tr><td><?= htmlspecialchars($n->note_type); ?></td><td><?= htmlspecialchars($n->shift ?? '—'); ?></td>
            <td><?= htmlspecialchars($n->body); ?></td><td><?= htmlspecialchars($n->recorded_by ?? '—'); ?></td>
            <td><?= htmlspecialchars($n->created_at); ?></td></tr>
        <?php endforeach; ?>
        <?php if(empty($notes)): ?><tr><td colspan="5" style="color:var(--mp-muted)">No notes yet.</td></tr><?php endif; ?>
      </table>
      <?php if($can['notes'] && $adm->status==='active'): ?>
      <form style="margin-top:8px" onsubmit="return ivPost(event,'inpatient/add_note/<?= (int)$adm->id; ?>','Recorded')">
        <select class="iv-inp" name="note_type"><option value="observation">Observation</option><option value="handover">Handover</option><option value="review">Weekly review</option></select>
        <select class="iv-inp" name="shift"><option value="">—</option><option value="morning">Morning</option><option value="evening">Evening</option></select>
        <input class="iv-inp" name="body" placeholder="Note" required style="width:40%"><button class="mp-qa-btn">Record</button>
      </form>
      <?php endif; ?>
    </div>

    <div class="iv-box">
      <h4>Meals</h4>
      <table class="iv-table">
        <tr><th>Date</th><th>Slot</th><th>Meal</th><th>Diet note</th><th>Status</th><th>Charge</th><th></th></tr>
        <?php foreach($meals as $m): ?>
        <tr><td><?= htmlspecialchars($m->meal_date); ?></td><td><?= htmlspecialchars($m->slot); ?></td>
            <td><?= htmlspecialchars($m->meal_name ?? ''); ?></td><td><?= htmlspecialchars($m->diet_note ?? ''); ?></td>
            <td><span class="iv-badge iv-<?= htmlspecialchars($m->status); ?>"><?= htmlspecialchars($m->status); ?></span></td>
            <td><?= $CI->currency($m->charge_amount); ?></td>
            <td><?php if($m->status==='ordered' && $can['meals']): ?><button class="mp-qa-btn green" onclick="ivAct(<?= (int)$m->id; ?>,'inpatient/provide_meal')">Provided</button><?php endif; ?></td></tr>
        <?php endforeach; ?>
        <?php if(empty($meals)): ?><tr><td colspan="7" style="color:var(--mp-muted)">No meals ordered.</td></tr><?php endif; ?>
      </table>
      <?php if($can['meals'] && $adm->status==='active'): ?>
      <form style="margin-top:8px" onsubmit="return ivPost(event,'inpatient/order_meal/<?= (int)$adm->id; ?>','Meal ordered')">
        <input class="iv-inp" name="meal_date" value="<?= date('Y-m-d'); ?>" style="width:110px">
        <select class="iv-inp" name="slot"><option>breakfast</option><option>lunch</option><option>dinner</option><option>snack</option></select>
        <select class="iv-inp" name="meal_type_id"><?php foreach($meal_types as $mt): ?><option value="<?= (int)$mt->id; ?>"><?= htmlspecialchars($mt->name); ?><?= $mt->charge>0 ? ' ('.$CI->currency($mt->charge).')' : ''; ?></option><?php endforeach; ?></select>
        <input class="iv-inp" name="diet_note" placeholder="Dietary instructions"><button class="mp-qa-btn">Order</button>
      </form>
      <?php endif; ?>
    </div>
  </div>

  <div>
    <div class="iv-box">
      <h4>Occupancy history</h4>
      <table class="iv-table">
        <tr><th>Ward / Bed</th><th>From</th><th>To</th><th>Reason</th></tr>
        <?php foreach($history as $o): ?>
        <tr><td><?= htmlspecialchars($o->ward_name . ' / ' . $o->bed_label); ?></td>
            <td><?= htmlspecialchars($o->from_at); ?></td><td><?= htmlspecialchars($o->to_at ?? 'current'); ?></td>
            <td><?= htmlspecialchars($o->open_reason . ($o->close_reason ? ' → ' . $o->close_reason : '')); ?></td></tr>
        <?php endforeach; ?>
      </table>
    </div>

    <div class="iv-box">
      <h4>Temporary leave</h4>
      <table class="iv-table">
        <tr><th>Reason</th><th>Expected</th><th>Departed</th><th>Returned</th><th>Billing</th><th>Status</th><th></th></tr>
        <?php foreach($leaves as $l): ?>
        <tr><td><?= htmlspecialchars($l->reason ?? ''); ?></td><td><?= htmlspecialchars($l->expected_return ?? '—'); ?></td>
            <td><?= htmlspecialchars($l->departed_at ?? '—'); ?></td>
            <td><?= htmlspecialchars($l->returned_at ?? '—'); ?><?= $l->overdue ? ' <span class="iv-badge iv-overdue">late</span>' : ''; ?></td>
            <td><?= htmlspecialchars($l->billing_policy); ?> / bed <?= $l->bed_hold ? 'held' : 'released'; ?></td>
            <td><span class="iv-badge iv-<?= htmlspecialchars($l->status); ?>"><?= htmlspecialchars($l->status); ?></span></td>
            <td>
              <?php if($l->status==='requested' && $can['leave_app']): ?><button class="mp-qa-btn green" onclick="ivAct(<?= (int)$l->id; ?>,'inpatient/approve_leave')">Approve</button><?php endif; ?>
              <?php if($l->status==='approved' && $can['leave']): ?><button class="mp-qa-btn blue" onclick="ivAct(<?= (int)$l->id; ?>,'inpatient/depart_leave')">Depart</button><?php endif; ?>
              <?php if($l->status==='out' && $can['leave']): ?><button class="mp-qa-btn green" onclick="ivAct(<?= (int)$l->id; ?>,'inpatient/return_leave')">Return</button><?php endif; ?>
            </td></tr>
        <?php endforeach; ?>
        <?php if(empty($leaves)): ?><tr><td colspan="7" style="color:var(--mp-muted)">No leave records.</td></tr><?php endif; ?>
      </table>
    </div>

    <div class="iv-box">
      <h4>Daily charges (invoice <?= $adm->invoice_id ? 'S-'.(int)$adm->invoice_id : '—'; ?>)</h4>
      <table class="iv-table">
        <tr><th>Date</th><th>Charge</th><th>Description</th><th>Amount</th></tr>
        <?php foreach($charges as $c): ?>
        <tr><td><?= htmlspecialchars($c->charge_date); ?></td><td><?= htmlspecialchars($c->charge_code); ?></td>
            <td><?= htmlspecialchars($c->description); ?></td><td><?= $CI->currency($c->amount); ?></td></tr>
        <?php endforeach; ?>
        <?php if(empty($charges)): ?><tr><td colspan="4" style="color:var(--mp-muted)">No charges posted yet — daily billing runs at day end.</td></tr><?php endif; ?>
      </table>
    </div>

    <div class="iv-box">
      <h4>Account <span style="text-transform:none;font-weight:600">— this admission only</span></h4>
      <?php if($can['finance']): ?>
        <?php if($account): ?>
        <table class="iv-table">
          <tr><td style="color:var(--mp-muted)">Invoice</td><td><?= htmlspecialchars($account['sales_code']); ?></td></tr>
          <tr><td style="color:var(--mp-muted)">Charged</td><td><?= $CI->currency($account['grand_total']); ?></td></tr>
          <tr><td style="color:var(--mp-muted)">Paid</td><td><?= $CI->currency($account['paid']); ?></td></tr>
          <tr><td style="color:var(--mp-muted)"><strong>Outstanding (this admission)</strong></td><td><strong><?= $CI->currency($account['outstanding']); ?></strong></td></tr>
        </table>
        <?php if($admission_stmt && !empty($admission_stmt['lines'])): ?>
        <div style="margin-top:8px;font-size:11px;color:var(--mp-muted)">Movement</div>
        <table class="iv-table">
          <tr><th>Date</th><th>Type</th><th>Ref</th><th>Amount</th><th>Balance</th></tr>
          <?php foreach($admission_stmt['lines'] as $ln): ?>
          <tr><td><?= htmlspecialchars($ln['date'] ?? ''); ?></td>
              <td><?= htmlspecialchars($ln['kind']); ?></td>
              <td><?= htmlspecialchars($ln['ref'] ?? ''); ?></td>
              <td><?= $CI->currency($ln['amount']); ?></td>
              <td><?= $CI->currency($ln['balance']); ?></td></tr>
          <?php endforeach; ?>
        </table>
        <?php endif; ?>
        <?php else: ?>
        <div style="color:var(--mp-muted);font-size:12px">No admission invoice yet — charges open an invoice once posted.</div>
        <?php endif; ?>
      <?php else: ?>
        <div style="color:var(--mp-muted);font-size:12px">Financial detail requires billing or funds permission.</div>
      <?php endif; ?>

      <?php if($can['finance']): ?>
      <div class="iv-warn" style="margin-top:12px">Patient-wide figures below — <strong>not</strong> admission-only.</div>
      <table class="iv-table">
        <?php if($wallet): ?>
        <tr><td style="color:var(--mp-muted)">Patient wallet (available)</td><td><?= $CI->currency($wallet['available']); ?></td></tr>
        <tr><td style="color:var(--mp-muted)">Patient wallet (reserved)</td><td><?= $CI->currency($wallet['reserved']); ?></td></tr>
        <?php endif; ?>
        <tr><td style="color:var(--mp-muted)">Total patient debt (all bills)</td><td><strong><?= $CI->currency($patient_debt); ?></strong></td></tr>
      </table>
      <?php endif; ?>

      <?php if($can['reminder'] && $account && $account['outstanding'] > 0): ?>
      <div style="margin-top:10px">
        <button class="mp-qa-btn" onclick="ivReminderPause(<?= (int)$account['invoice_id']; ?>)">Pause reminders (this invoice)</button>
      </div>
      <?php endif; ?>
    </div>

    <div class="iv-box">
      <h4>Discharge</h4>
      <?php foreach($discharges as $d): ?>
      <div style="border:1px solid var(--mp-border);border-radius:10px;padding:10px;margin-bottom:8px">
        <span class="iv-badge iv-<?= htmlspecialchars($d->status); ?>"><?= htmlspecialchars($d->status); ?></span>
        recommended u#<?= (int)$d->recommended_by; ?> <?= htmlspecialchars($d->recommended_at); ?>
        <?php if($d->decided_by): ?>· decided u#<?= (int)$d->decided_by; ?> <?= htmlspecialchars($d->decided_at); ?><?php endif; ?>
        <?php if($d->actual_discharge_at): ?>· departed <strong><?= htmlspecialchars($d->actual_discharge_at); ?></strong><?php endif; ?>
        <div style="font-size:12px;margin-top:4px;color:var(--mp-muted)">
          <?= htmlspecialchars($d->discharge_reason ?? $d->recommendation_note ?? ''); ?>
          <?= $d->summary ? ' — ' . htmlspecialchars($d->summary) : ''; ?>
          <?= $d->follow_up ? ' · F/U: ' . htmlspecialchars($d->follow_up) . ' ' . htmlspecialchars($d->follow_up_date ?? '') : ''; ?>
        </div>
        <div style="margin-top:6px">
          <?php if($d->status==='recommended' && $can['decide']): ?>
            <button class="mp-qa-btn green" onclick="ivDecide(<?= (int)$d->id; ?>,1)">Approve</button>
            <button class="mp-qa-btn" onclick="ivDecide(<?= (int)$d->id; ?>,0)">Reject</button>
          <?php endif; ?>
          <?php if($d->status==='approved' && $can['manage']): ?>
            <div class="iv-warn">Approval does not end the admission — record the actual departure.</div>
            <button class="mp-qa-btn green" onclick="ivAct(<?= (int)$d->id; ?>,'inpatient/complete_discharge')">Record departure</button>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if(empty($discharges)): ?><div style="color:var(--mp-muted);font-size:12px">No discharge activity.</div><?php endif; ?>
    </div>
  </div>
</div>

<div class="iv-box">
  <h4>External referrals (this patient)</h4>
  <table class="iv-table">
    <tr><th>#</th><th>Destination</th><th>Urgency</th><th>Status</th><th>Departed</th><th>Returned</th><th>Exception</th><th></th></tr>
    <?php foreach($referrals as $r): ?>
    <tr><td>R-<?= (int)$r->id; ?></td><td><?= htmlspecialchars($r->destination); ?></td>
        <td><?= htmlspecialchars($r->urgency); ?></td>
        <td><span class="iv-badge iv-<?= htmlspecialchars($r->status); ?>"><?= htmlspecialchars($r->status); ?></span></td>
        <td><?= htmlspecialchars($r->departed_at ?? '—'); ?></td><td><?= htmlspecialchars($r->returned_at ?? '—'); ?></td>
        <td><?= $r->exception_flag ? '<span class="iv-badge iv-overdue">exception</span> ' . htmlspecialchars($r->exception_note ?? '') : '—'; ?></td>
        <td>
          <?php if($can['referral'] && $r->status==='open'): ?><button class="mp-qa-btn blue" onclick="ivDepart(<?= (int)$r->id; ?>,'<?= $r->urgency; ?>')">Depart</button><?php endif; ?>
          <?php if($can['referral'] && $r->status==='departed'): ?><button class="mp-qa-btn blue" onclick="ivRefUpdate(<?= (int)$r->id; ?>,'procedure')">Procedure</button> <button class="mp-qa-btn green" onclick="ivRefUpdate(<?= (int)$r->id; ?>,'returned')">Returned</button><?php endif; ?>
          <?php if($can['referral'] && $r->status==='procedure'): ?><button class="mp-qa-btn green" onclick="ivRefUpdate(<?= (int)$r->id; ?>,'returned')">Returned</button><?php endif; ?>
          <?php if($can['referral'] && $r->status==='returned'): ?><button class="mp-qa-btn green" onclick="ivAct(<?= (int)$r->id; ?>,'inpatient/referral_review')">Review return</button><?php endif; ?>
        </td></tr>
    <?php endforeach; ?>
    <?php if(empty($referrals)): ?><tr><td colspan="8" style="color:var(--mp-muted)">No external referrals.</td></tr><?php endif; ?>
  </table>
</div>

<script>
function ivPost(e, url, okMsg){
  e.preventDefault(); var fd = new FormData(e.target);
  fetch('<?= base_url(); ?>'+url, {method:'POST', body:fd})
    .then(r=>r.json()).then(d=>{ alert(d.message||okMsg); if(d.status==='success') location.reload(); });
  return false;
}
function ivAct(id, url){ if(!confirm('Confirm?')) return;
  fetch('<?= base_url(); ?>'+url+'/'+id, {method:'POST'}).then(r=>r.json())
    .then(d=>{ alert(d.message||''); if(d.status==='success') location.reload(); }); }
function ivTask(id){ var n = prompt('Result note (optional)')||'';
  var fd = new FormData(); fd.append('note', n);
  fetch('<?= base_url('inpatient/complete_nursing_task/'); ?>'+id, {method:'POST', body:fd})
    .then(r=>r.json()).then(d=>{ alert(d.message||''); if(d.status==='success') location.reload(); }); }
function ivLeave(id){ var r = prompt('Leave reason'); if(!r) return;
  var ret = prompt('Expected return (Y-m-d H:i)','<?= date('Y-m-d H:i', strtotime('+1 day')); ?>');
  var fd = new FormData(); fd.append('reason', r); fd.append('expected_return', ret);
  fetch('<?= base_url('inpatient/request_leave/'); ?>'+id, {method:'POST', body:fd})
    .then(r=>r.json()).then(d=>{ alert(d.message||''); if(d.status==='success') location.reload(); }); }
function ivReferral(pid, aid){ var dst = prompt('Destination facility'); if(!dst) return;
  var fd = new FormData(); fd.append('patient_id', pid); fd.append('admission_id', aid);
  fd.append('destination', dst); fd.append('reason', prompt('Reason','')||'');
  fd.append('urgency', prompt('Urgency: routine|urgent|emergency','routine')||'routine');
  fetch('<?= base_url('inpatient/create_referral'); ?>', {method:'POST', body:fd})
    .then(r=>r.json()).then(d=>{ alert(d.message||''); if(d.status==='success') location.reload(); }); }
function ivDepart(id, urg){ var ex=''; if(urg==='emergency'){ ex = prompt('Emergency exception note (required — no handover doc)')||''; if(!ex) return; }
  var fd = new FormData(); fd.append('exception_note', ex);
  fetch('<?= base_url('inpatient/referral_depart/'); ?>'+id, {method:'POST', body:fd})
    .then(r=>r.json()).then(d=>{ alert(d.message||''); if(d.status==='success') location.reload(); }); }
function ivRefUpdate(id, st){ var fd = new FormData(); fd.append('status', st);
  if(st==='procedure') fd.append('procedure_status', prompt('Procedure status','in progress')||'');
  fetch('<?= base_url('inpatient/referral_update/'); ?>'+id, {method:'POST', body:fd})
    .then(r=>r.json()).then(d=>{ alert(d.message||''); if(d.status==='success') location.reload(); }); }
function ivRecommend(id){ var n = prompt('Recommendation note','Clinically fit for discharge'); if(n===null) return;
  var fd = new FormData(); fd.append('note', n);
  fetch('<?= base_url('inpatient/recommend_discharge/'); ?>'+id, {method:'POST', body:fd})
    .then(r=>r.json()).then(d=>{ alert(d.message||''); if(d.status==='success') location.reload(); }); }
function ivDecide(id, approve){
  var fd = new FormData(); fd.append('approve', approve);
  if(approve){ fd.append('reason', prompt('Discharge reason','recovered')||''); fd.append('summary', prompt('Discharge summary')||'');
    fd.append('follow_up', prompt('Follow-up (optional)','')||''); fd.append('follow_up_date', prompt('Follow-up date (Y-m-d, optional)','')||''); }
  else { fd.append('reason', prompt('Rejection reason')||''); }
  fetch('<?= base_url('inpatient/decide_discharge/'); ?>'+id, {method:'POST', body:fd})
    .then(r=>r.json()).then(d=>{ alert(d.message||''); if(d.status==='success') location.reload(); }); }
function ivDeceased(pid){ if(!confirm('Record deceased closure? This suppresses all pending tasks and notifications.')) return;
  var fd = new FormData(); fd.append('at', prompt('Date/time (Y-m-d H:i, blank = now)','')||'');
  fd.append('notes', prompt('Documentation notes (required)')||'');
  fetch('<?= base_url('inpatient/record_deceased/'); ?>'+pid, {method:'POST', body:fd})
    .then(r=>r.json()).then(d=>{ alert(d.message||''); if(d.status==='success') location.reload(); }); }
function ivReminderPause(invoiceId){ var reason = prompt('Pause reminder reason (required)'); if(!reason) return;
  var fd = new FormData(); fd.append('scope','invoice'); fd.append('target', invoiceId); fd.append('reason', reason);
  fetch('<?= base_url('debt_reminders/pause'); ?>', {method:'POST', body:fd})
    .then(r=>r.json()).then(d=>{ alert(d.message||''); });
}
</script>
