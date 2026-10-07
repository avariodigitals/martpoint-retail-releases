<?php $this->load->view('admin/desktop/_styles'); ?>
<style>
.enc-grid{display:grid!important;grid-template-columns:repeat(auto-fit,minmax(340px,1fr))!important;gap:14px!important}
.enc-card{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:14px!important}
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
.prov-banner{background:#FEF3C7!important;border:1px solid #F59E0B!important;color:#92400E!important;font-size:11px!important;padding:6px 10px!important;border-radius:8px!important;margin-bottom:8px!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($enc->encounter_code); ?> — <?= htmlspecialchars($patient->customer_name ?? 'Patient'); ?></h2>
    <div class="mp-page-sub">
      <?= htmlspecialchars($patient->patient_code ?? ''); ?> · Stage: <b><?= htmlspecialchars(mp_code_label($enc->queue_stage)); ?></b>
      · Arrived <?= $enc->checkin_at ? date('d M H:i', strtotime($enc->checkin_at)) : '—'; ?>
      <?php if($enc->clinician_user_id): ?> · Clinician: <?= (int)$enc->clinician_user_id; ?><?php endif; ?>
    </div>
  </div>
  <div style="display:flex;gap:10px;">
    <a href="<?= base_url('care_queue'); ?>" class="mp-qa-btn"><i class="fa fa-arrow-left"></i> Queue</a>
    <?php if($can['assign']): ?>
      <select id="assignSel" class="mp-form-control" style="width:auto;">
        <option value="">Assign to…</option>
        <?php foreach($staff as $s): ?><option value="<?= (int)$s->id; ?>"><?= htmlspecialchars($s->username); ?></option><?php endforeach; ?>
      </select>
      <button class="mp-qa-btn blue" onclick="doAssign()"><i class="fa fa-user-md"></i> Assign</button>
    <?php endif; ?>
  </div>
</div>

<div class="enc-grid">

<!-- ============ NURSING INTAKE / VITALS ============ -->
<?php if(physio_can('vitals_view')): ?>
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
<?php if(physio_can('assessments_view')): ?>
<div class="enc-card">
  <h4>Assessments</h4>
  <div class="prov-banner"><i class="fa fa-info-circle"></i> The bundled template is a <b>provisional</b> form pending the agreed five-page clinical assessment.</div>
  <?php foreach($assessments as $a): ?>
    <div class="enc-row">
      <span><a href="<?= base_url('assessments/form/' . (int)$a->id); ?>"><?= htmlspecialchars($a->template_key); ?> v<?= (int)$a->template_version; ?></a>
        <span class="enc-pill <?= $a->status; ?>"><?= $a->status; ?></span><br>
        <small style="color:#94a3b8;"><?= htmlspecialchars($a->assessor_name ?: ''); ?> · <?= $a->created_at; ?></small></span>
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
<?php if(physio_can('investigations_view')): ?>
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
    <div style="display:flex;gap:6px;">
      <select name="category" class="mp-form-control"><option value="clinical">Clinical</option><option value="investigation">Investigation</option><option value="consent">Consent</option><option value="id">Identification</option><option value="other">Other</option></select>
      <input type="file" name="doc_file" class="mp-form-control" required>
    </div>
    <button class="enc-mini-btn primary" style="margin-top:6px;">Upload (private)</button>
  </form>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- ============ HISTORY ============ -->
<div class="enc-card">
  <h4>Visit History</h4>
  <ul class="list-unstyled" style="font-size:12px;margin:0;">
    <?php foreach($events as $ev): ?>
      <li style="padding:3px 0;border-bottom:1px dashed var(--mp-border);">
        <b><?= htmlspecialchars(mp_code_label($ev->event)); ?></b>
        <?= $ev->from_stage ? ' ' . htmlspecialchars($ev->from_stage . ' → ' . $ev->to_stage) : ''; ?>
        <?= $ev->note ? ' — ' . htmlspecialchars($ev->note) : ''; ?>
        <span style="color:#94a3b8;"><?= htmlspecialchars($ev->created_by_name ?: 'system'); ?> · <?= $ev->created_at; ?></span>
      </li>
    <?php endforeach; ?>
  </ul>
</div>

</div>

<!-- hidden upload form for signed consent -->
<form id="consentUploadForm" style="display:none;" enctype="multipart/form-data">
  <input type="file" name="doc_file" id="consentFile" onchange="consentFileChosen()">
</form>

<script>
var CSRF_BODY = { '<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>' };
var ENC_ID = <?= (int)$enc->id; ?>;

function completeIntake(){
  $.post('<?= base_url('care_queue/intake_complete/'); ?>' + ENC_ID, CSRF_BODY, function(res){
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
  var summary = prompt('Result summary (or leave blank to attach a file next):','');
  if(summary === null) return;
  $.post('<?= base_url('investigations/result/'); ?>'+id, $.extend({}, CSRF_BODY, {result_summary:summary}), function(res){
    if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(),700); } else toastr.error(res.message||'Failed');
  }, 'json');
}
function invReview(id){
  var note = prompt('Review note (optional):','');
  if(note === null) return;
  $.post('<?= base_url('investigations/review/'); ?>'+id, $.extend({}, CSRF_BODY, {note:note}), function(res){
    if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(),700); } else toastr.error(res.message||'Failed');
  }, 'json');
}
function invCancel(id){
  var reason = prompt('Cancellation reason (required):','');
  if(reason === null) return;
  $.post('<?= base_url('investigations/cancel/'); ?>'+id, $.extend({}, CSRF_BODY, {reason:reason}), function(res){
    if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(),700); } else toastr.error(res.message||'Failed');
  }, 'json');
}
var _consentId = 0;
function consentGen(){
  var type = prompt('Consent type (general_treatment | procedure | home_visit | data_sharing):','general_treatment');
  if(type === null) return;
  $.post('<?= base_url('consents/generate'); ?>', $.extend({}, CSRF_BODY, {patient_id:<?= (int)$enc->patient_id; ?>, encounter_id:ENC_ID, consent_type:type}), function(res){
    if(res.status==='success'){ toastr.success(res.message); window.open(res.print_url, '_blank'); setTimeout(()=>location.reload(),800); }
    else toastr.error(res.message||'Failed');
  }, 'json');
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
  var signed = prompt('Which signatories are visibly signed? (comma-separated: patient_or_guardian, clinician)','');
  if(signed === null) return;
  var arr = signed.split(',').map(function(s){ return s.trim(); }).filter(Boolean);
  var data = $.extend({}, CSRF_BODY); arr.forEach(function(s,i){ data['signed['+i+']'] = s; });
  $.post('<?= base_url('consents/verify/'); ?>'+id, data, function(res){
    if(res.status==='success'){ toastr.success(res.message); setTimeout(()=>location.reload(),700); } else toastr.error(res.message||'Failed');
  }, 'json');
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
    var msg = (res.versions||[]).map(function(v){ return 'v'+v.version_no+' · '+v.mime+' · '+(v.uploaded_by||'')+' '+v.created_at; }).join('\n');
    msg += '\n\nAccess:\n' + (res.log||[]).map(function(l){ return l.action+' by '+(l.username||'?')+' '+l.created_at; }).join('\n');
    alert(msg || 'No history');
  }, 'json');
}
</script>
<script>$(".care_queue-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
