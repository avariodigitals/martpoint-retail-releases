<?php $this->load->view('admin/desktop/_styles'); ?>
<style>
.inv-tbl{width:100%!important;font-size:12px!important}
.inv-tbl th{font-size:10px!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:var(--mp-muted)!important;padding:6px 8px!important;border-bottom:2px solid var(--mp-border)!important}
.inv-tbl td{padding:7px 8px!important;border-bottom:1px solid var(--mp-border)!important}
.inv-pill{font-size:10px!important;font-weight:700!important;padding:2px 8px!important;border-radius:12px!important}
.inv-pill.requested,.inv-pill.pending{background:#F1F5F9!important;color:#475569!important}
.inv-pill.result_received{background:#DBEAFE!important;color:#1E40AF!important}
.inv-pill.reviewed{background:#D1FAE5!important;color:#065F46!important}
.inv-pill.cancelled{background:#FEE2E2!important;color:#991B1B!important}
.inv-btn{font-size:11px!important;padding:3px 8px!important;border-radius:6px!important;border:1px solid var(--mp-border)!important;background:#fff!important;cursor:pointer!important}
.inv-btn.primary{background:var(--mp-primary)!important;color:#fff!important;border-color:var(--mp-primary)!important}
</style>

<div class="mp-page-head">
  <div>
    <h2>Investigations</h2>
    <div class="mp-page-sub">Requests, external referrals, results and clinician review — a result is not reviewed until a clinician signs it off</div>
  </div>
</div>

<div style="margin-bottom:10px;">
  <?php foreach($statuses as $s): ?>
    <a class="inv-btn" style="text-decoration:none;" href="<?= base_url('investigations?status=' . $s); ?>"><?= str_replace('_',' ',$s); ?></a>
  <?php endforeach; ?>
  <a class="inv-btn" style="text-decoration:none;" href="<?= base_url('investigations'); ?>">all</a>
</div>

<table class="inv-tbl">
  <thead><tr><th>Ref</th><th>Patient</th><th>Investigation</th><th>Priority</th><th>Facility</th><th>Status</th><th>Requested</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach($rows as $r): ?>
    <tr>
      <td><b><?= htmlspecialchars($r->request_ref); ?></b></td>
      <td><?= htmlspecialchars($r->customer_name ?: '—'); ?><br><small style="color:#94a3b8;"><?= htmlspecialchars($r->patient_code ?: ''); ?></small></td>
      <td><?= htmlspecialchars($r->test_name); ?><br><small style="color:#94a3b8;"><?= htmlspecialchars($r->category ?: ''); ?></small></td>
      <td><?= htmlspecialchars($r->priority); ?></td>
      <td><?= htmlspecialchars($r->facility_name ?: 'In-house'); ?></td>
      <td><span class="inv-pill <?= $r->status; ?>"><?= str_replace('_',' ',$r->status); ?></span>
        <?php $verN = (int)($result_versions[$r->id] ?? 0); if($verN > 0): ?><span class="inv-pill" style="background:#F1F5F9;color:#475569;">result v<?= $verN; ?></span><?php endif; ?>
        <?php if($r->status==='cancelled' && $r->cancel_reason): ?><br><small style="color:#991B1B;"><?= htmlspecialchars($r->cancel_reason); ?></small><?php endif; ?>
        <?php if($r->status==='reviewed'): ?><br><small style="color:#065F46;">reviewed <?= $r->reviewed_at; ?></small><?php endif; ?>
      </td>
      <td><small><?= htmlspecialchars($r->requested_by_name ?: ''); ?><br><?= $r->requested_at; ?></small></td>
      <td>
        <?php if($r->status === 'requested' && $can['request']): ?><button class="inv-btn" onclick="invPending(<?= (int)$r->id; ?>)">Sent</button><?php endif; ?>
        <?php if(in_array($r->status, array('requested','pending')) && $can['result']): ?>
          <button class="inv-btn primary" onclick="invResult(<?= (int)$r->id; ?>)">Result</button>
          <button class="inv-btn" onclick="invCancel(<?= (int)$r->id; ?>)">Cancel</button>
        <?php endif; ?>
        <?php if($r->status === 'result_received' && $can['review']): ?><button class="inv-btn primary" onclick="invReview(<?= (int)$r->id; ?>)">Review</button><?php endif; ?>
        <?php if($r->status === 'reviewed' && $can['result']): ?><button class="inv-btn" onclick="invAmend(<?= (int)$r->id; ?>)">Amend</button><?php endif; ?>
        <?php if($r->result_document_id): ?><a class="inv-btn" href="<?= base_url('patient_docs/download/' . (int)$r->result_document_id); ?>"><i class="fa fa-download"></i></a><?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if(empty($rows)): ?><tr><td colspan="8" style="color:var(--mp-muted);">No investigations.</td></tr><?php endif; ?>
  </tbody>
</table>

<script>
var CSRF_BODY = { '<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>' };
function invPending(id){ $.post('<?= base_url('investigations/pending/'); ?>'+id, CSRF_BODY, function(r){ done(r); }, 'json'); }
function invResult(id){
  var summary = prompt('Result summary (a result file can be uploaded from the visit workspace):','');
  if(summary === null) return;
  $.post('<?= base_url('investigations/result/'); ?>'+id, $.extend({}, CSRF_BODY, {result_summary:summary}), function(r){ done(r); }, 'json');
}
function invReview(id){
  var note = prompt('Review note (optional):','');
  if(note === null) return;
  $.post('<?= base_url('investigations/review/'); ?>'+id, $.extend({}, CSRF_BODY, {note:note}), function(r){ done(r); }, 'json');
}
function invAmend(id){
  var reason = prompt('Amendment reason (required — the reviewed original is preserved):','');
  if(!reason) return;
  var summary = prompt('Corrected result summary (a corrected file can be uploaded from the visit workspace):','');
  if(summary === null) return;
  $.post('<?= base_url('investigations/amend/'); ?>'+id, $.extend({}, CSRF_BODY, {reason:reason, result_summary:summary}), function(r){ done(r); }, 'json');
}
function invCancel(id){
  var reason = prompt('Cancellation reason (required):','');
  if(reason === null) return;
  $.post('<?= base_url('investigations/cancel/'); ?>'+id, $.extend({}, CSRF_BODY, {reason:reason}), function(r){ done(r); }, 'json');
}
function done(r){ if(r.status==='success'){ toastr.success(r.message); setTimeout(()=>location.reload(),700); } else toastr.error(r.message||'Failed'); }
</script>
<script>$(".care-active-li, .investigations-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
