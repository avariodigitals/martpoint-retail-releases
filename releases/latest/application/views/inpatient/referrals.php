<?php $this->load->view('admin/desktop/_styles'); $CI =& get_instance(); ?>
<style>
.rf-box{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:14px!important;margin-bottom:14px!important}
.rf-box h4{font-size:11px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:var(--mp-muted)!important;margin:0 0 10px 0!important}
.rf-table{width:100%!important;border-collapse:collapse!important;font-size:12px!important}
.rf-table th{text-align:left!important;padding:5px 8px!important;border-bottom:1px solid var(--mp-border)!important;font-size:10px!important;text-transform:uppercase!important;color:var(--mp-muted)!important}
.rf-table td{padding:6px 8px!important;border-bottom:1px solid #F1F5F9!important;vertical-align:top!important}
.rf-badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:800;text-transform:uppercase}
.rf-open{background:#DBEAFE;color:#1E40AF}.rf-departed{background:#FEF3C7;color:#92400E}
.rf-procedure{background:#E0E7FF;color:#3730A3}.rf-returned{background:#FEF3C7;color:#92400E}
.rf-reviewed{background:#D1FAE5;color:#065F46}.rf-cancelled{background:#F3F4F6;color:#6B7280}
.rf-exc{background:#FEE2E2;color:#991B1B}
.rf-inp{padding:6px 9px!important;border:1px solid var(--mp-border)!important;border-radius:8px!important;font-size:12px!important;margin-right:6px!important;background:var(--mp-bg,#fff)!important;color:inherit!important}
</style>

<div class="mp-page-head">
  <div><h2>External Referrals</h2>
  <div class="mp-page-sub">Transfers out — destination, handover, procedure progress and reviewed return. Emergency departures without handover documents are flagged exceptions until reviewed.</div></div>
</div>

<div class="rf-box">
  <table class="rf-table">
    <tr><th>#</th><th>Patient</th><th>Destination</th><th>Reason</th><th>Urgency</th><th>Status</th><th>Departed</th><th>Procedure</th><th>Returned</th><th>Review</th><th></th></tr>
    <?php foreach($referrals as $r): ?>
    <tr>
      <td>R-<?= (int)$r->id; ?></td>
      <td><?= htmlspecialchars(($r->patient_code ?? '') . ' ' . ($r->patient_name ?? '')); ?></td>
      <td><?= htmlspecialchars($r->destination); ?></td>
      <td><?= htmlspecialchars($r->reason ?? ''); ?></td>
      <td><?= htmlspecialchars($r->urgency); ?><?= $r->exception_flag ? ' <span class="rf-badge rf-exc">exception</span>' : ''; ?></td>
      <td><span class="rf-badge rf-<?= htmlspecialchars($r->status); ?>"><?= htmlspecialchars($r->status); ?></span></td>
      <td><?= htmlspecialchars($r->departed_at ?? '—'); ?></td>
      <td><?= htmlspecialchars($r->procedure_status ?? '—'); ?></td>
      <td><?= htmlspecialchars($r->returned_at ?? '—'); ?></td>
      <td><?= $r->reviewed_by ? htmlspecialchars($r->reviewed_by . ' ' . $r->reviewed_at) : '—'; ?></td>
      <td>
        <?php if($can['manage'] && $r->status === 'open'): ?><button class="mp-qa-btn blue" onclick="rfDepart(<?= (int)$r->id; ?>,'<?= $r->urgency; ?>')">Depart</button><?php endif; ?>
        <?php if($can['manage'] && $r->status === 'departed'): ?><button class="mp-qa-btn" onclick="rfUpd(<?= (int)$r->id; ?>,'procedure')">Procedure</button> <button class="mp-qa-btn green" onclick="rfUpd(<?= (int)$r->id; ?>,'returned')">Returned</button><?php endif; ?>
        <?php if($can['manage'] && $r->status === 'procedure'): ?><button class="mp-qa-btn green" onclick="rfUpd(<?= (int)$r->id; ?>,'returned')">Returned</button><?php endif; ?>
        <?php if($can['manage'] && $r->status === 'returned'): ?><button class="mp-qa-btn green" onclick="rfReview(<?= (int)$r->id; ?>)">Review return</button><?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if(empty($referrals)): ?><tr><td colspan="11" style="color:var(--mp-muted)">No external referrals.</td></tr><?php endif; ?>
  </table>
</div>

<?php if($can['manage']): ?>
<div class="rf-box">
  <h4>New referral</h4>
  <form onsubmit="return rfNew(event)">
    <select class="rf-inp" name="patient_id" required><option value="">— Patient —</option>
      <?php foreach($patients as $p): ?><option value="<?= (int)$p->id; ?>"><?= htmlspecialchars($p->patient_code . ' — ' . $p->full_name); ?></option><?php endforeach; ?></select>
    <input class="rf-inp" name="destination" placeholder="Destination facility" required style="width:200px">
    <input class="rf-inp" name="reason" placeholder="Reason" style="width:200px">
    <select class="rf-inp" name="urgency"><option>routine</option><option>urgent</option><option>emergency</option></select>
    <input class="rf-inp" name="handover_doc_id" placeholder="Handover doc ID (routine requires)" style="width:190px">
    <button class="mp-qa-btn green">Create</button>
  </form>
</div>
<?php endif; ?>

<script>
function rfDepart(id, urg){ var ex='';
  if(urg==='emergency'){ ex = prompt('Emergency exception note (required — departing without handover doc)')||''; if(!ex) return; }
  var fd = new FormData(); fd.append('exception_note', ex);
  fetch('<?= base_url('inpatient/referral_depart/'); ?>'+id, {method:'POST', body:fd})
    .then(r=>r.json()).then(d=>{ alert(d.message||''); if(d.status==='success') location.reload(); }); }
function rfUpd(id, st){ var fd = new FormData(); fd.append('status', st);
  if(st==='procedure') fd.append('procedure_status', prompt('Procedure status','')||'');
  fetch('<?= base_url('inpatient/referral_update/'); ?>'+id, {method:'POST', body:fd})
    .then(r=>r.json()).then(d=>{ alert(d.message||''); if(d.status==='success') location.reload(); }); }
function rfReview(id){ var fd = new FormData(); fd.append('assessment_id', prompt('Linked return-assessment ID (optional)','')||'');
  fetch('<?= base_url('inpatient/referral_review/'); ?>'+id, {method:'POST', body:fd})
    .then(r=>r.json()).then(d=>{ alert(d.message||''); if(d.status==='success') location.reload(); }); }
function rfNew(e){ e.preventDefault(); var fd = new FormData(e.target);
  fetch('<?= base_url('inpatient/create_referral'); ?>', {method:'POST', body:fd})
    .then(r=>r.json()).then(d=>{ alert(d.message||''); if(d.status==='success') location.reload(); }); return false; }
</script>
