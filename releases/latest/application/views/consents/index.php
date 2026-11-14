<?php $this->load->view('admin/desktop/_styles'); ?>
<style>
.con-tbl{width:100%!important;font-size:12px!important}
.con-tbl th{font-size:10px!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:var(--mp-muted)!important;padding:6px 8px!important;border-bottom:2px solid var(--mp-border)!important}
.con-tbl td{padding:7px 8px!important;border-bottom:1px solid var(--mp-border)!important}
.con-pill{font-size:10px!important;font-weight:700!important;padding:2px 8px!important;border-radius:12px!important}
.con-pill.pending{background:#F1F5F9!important;color:#475569!important}
.con-pill.awaiting_verification{background:#FFEDD5!important;color:#9A3412!important}
.con-pill.completed{background:#D1FAE5!important;color:#065F46!important}
.con-pill.declined{background:#FEE2E2!important;color:#991B1B!important}
.con-pill.superseded{background:#F5F3FF!important;color:#5B21B6!important}
.con-btn{font-size:11px!important;padding:3px 8px!important;border-radius:6px!important;border:1px solid var(--mp-border)!important;background:#fff!important;cursor:pointer!important}
.con-btn.primary{background:var(--mp-primary)!important;color:#fff!important;border-color:var(--mp-primary)!important}
</style>

<div class="mp-page-head">
  <div>
    <h2>Consents</h2>
    <div class="mp-page-sub">Paper workflow — generate, print, upload the signed copy, verify signatories. Digital signing pending agreed method.</div>
  </div>
</div>

<table class="con-tbl">
  <thead><tr><th>Patient</th><th>Consent</th><th>Type</th><th>Status</th><th>Generated</th><th>Actions</th></tr></thead>
  <tbody>
  <?php foreach($rows as $r): ?>
    <tr>
      <td><?= htmlspecialchars($r->customer_name ?: '—'); ?><br><small style="color:#94a3b8;"><?= htmlspecialchars($r->patient_code ?: ''); ?></small></td>
      <td><?= htmlspecialchars($r->title); ?></td>
      <td><?= htmlspecialchars($r->consent_type); ?></td>
      <td><span class="con-pill <?= $r->status; ?>"><?= str_replace('_',' ',$r->status); ?></span>
        <?php if($r->status==='completed'): ?><br><small style="color:#065F46;">verified <?= $r->verified_at; ?></small><?php endif; ?>
        <?php if($r->status==='declined' && $r->declined_reason): ?><br><small style="color:#991B1B;"><?= htmlspecialchars($r->declined_reason); ?></small><?php endif; ?>
      </td>
      <td><small><?= $r->created_date; ?></small></td>
      <td>
        <a class="con-btn" target="_blank" href="<?= base_url('consents/print_form/' . (int)$r->id); ?>"><i class="fa fa-print"></i></a>
        <?php if(in_array($r->status, array('pending','awaiting_verification')) && $can['manage']): ?>
          <button class="con-btn" onclick="conUpload(<?= (int)$r->id; ?>)">Upload signed</button>
          <button class="con-btn" onclick="conDecline(<?= (int)$r->id; ?>)">Decline</button>
        <?php endif; ?>
        <?php if($r->status === 'awaiting_verification' && $can['verify']): ?>
          <button class="con-btn primary" onclick="conVerify(<?= (int)$r->id; ?>)">Verify signatures</button>
        <?php endif; ?>
        <?php if($r->document_id): ?><a class="con-btn" href="<?= base_url('patient_docs/download/' . (int)$r->document_id); ?>"><i class="fa fa-download"></i></a><?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  <?php if(empty($rows)): ?><tr><td colspan="6" style="color:var(--mp-muted);">No consents.</td></tr><?php endif; ?>
  </tbody>
</table>

<form id="conUpForm" style="display:none;" enctype="multipart/form-data"><input type="file" name="doc_file" id="conFile" onchange="conFileChosen()"></form>

<script>
var CSRF_BODY = { '<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>' };
var _cid = 0;
function conUpload(id){ _cid = id; $('#conFile').click(); }
function conFileChosen(){
  var fd = new FormData(document.getElementById('conUpForm'));
  fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
  $.ajax({ url:'<?= base_url('consents/upload_signed/'); ?>'+_cid, type:'POST', data:fd, processData:false, contentType:false, dataType:'json',
    success:function(r){ if(r.status==='success'){ toastr.success(r.message); setTimeout(()=>location.reload(),700); } else toastr.error(r.message||'Failed'); },
    error:function(){ toastr.error('Upload failed'); }
  });
}
function conVerify(id){
  var signed = prompt('Signatories visibly signed (comma-separated: patient_or_guardian, clinician):','');
  if(signed === null) return;
  var data = $.extend({}, CSRF_BODY);
  signed.split(',').map(function(s){return s.trim();}).filter(Boolean).forEach(function(s,i){ data['signed['+i+']'] = s; });
  $.post('<?= base_url('consents/verify/'); ?>'+id, data, function(r){ if(r.status==='success'){ toastr.success(r.message); setTimeout(()=>location.reload(),700); } else toastr.error(r.message||'Failed'); }, 'json');
}
function conDecline(id){
  var reason = prompt('Decline reason (required):','');
  if(reason === null) return;
  $.post('<?= base_url('consents/decline/'); ?>'+id, $.extend({}, CSRF_BODY, {reason:reason}), function(r){ if(r.status==='success'){ toastr.success(r.message); setTimeout(()=>location.reload(),700); } else toastr.error(r.message||'Failed'); }, 'json');
}
</script>
<script>$(".consents-active-li, .care-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
