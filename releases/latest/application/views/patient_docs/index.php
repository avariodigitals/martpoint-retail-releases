<?php $this->load->view('admin/desktop/_styles'); ?>
<style>
.doc-card{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:14px!important}
.doc-row{display:flex!important;justify-content:space-between!important;align-items:center!important;padding:8px 0!important;border-bottom:1px dashed var(--mp-border)!important;font-size:13px!important}
.doc-pill{font-size:10px!important;font-weight:700!important;padding:2px 8px!important;border-radius:12px!important;background:#F1F5F9!important;color:#475569!important}
.doc-pill.missing{background:#FEE2E2!important;color:#991B1B!important}
.doc-btn{font-size:11px!important;padding:4px 9px!important;border-radius:6px!important;border:1px solid var(--mp-border)!important;background:#fff!important;cursor:pointer!important}
</style>

<div class="mp-page-head">
  <div>
    <h2>Patient Documents</h2>
    <div class="mp-page-sub">Private storage — files are served only through an authorised, logged download. No public URLs.</div>
  </div>
</div>

<?php if(!$storage_ready): ?>
<div style="background:#FEE2E2;border:1px solid #FCA5A5;color:#991B1B;border-radius:10px;padding:10px 14px;font-size:12px;font-weight:700;margin-bottom:12px;">
  Private document storage is unavailable — uploads are disabled until the directory outside the web root is writable. Contact the system administrator.
</div>
<?php elseif(!$can['clinical']): ?>
<div style="background:#FEF3C7;border:1px solid #FCD34D;color:#92400E;border-radius:10px;padding:10px 14px;font-size:12px;margin-bottom:12px;">
  You can upload scans and manage consent documents. Clinical assessments and investigation results are restricted to clinical staff.
</div>
<?php endif; ?>

<?php if($patient): ?>
<h4 style="margin:0 0 10px;"><?= htmlspecialchars($patient->customer_name ?? ''); ?> <small style="color:#94a3b8;"><?= htmlspecialchars($patient->patient_code ?? ''); ?></small></h4>
<?php endif; ?>

<div class="doc-card">
  <?php foreach($documents as $d): ?>
    <div class="doc-row">
      <?php $has_attachment = $d->status !== 'missing_attachment'; ?>
      <span><?= htmlspecialchars($d->title); ?> <span class="doc-pill<?= $has_attachment ? '' : ' missing'; ?>"><?= str_replace('_',' ',$d->status); ?></span><br>
        <small style="color:#94a3b8;"><?= $d->category; ?> · v<?= (int)($d->version_no ?? 0); ?> · <?= $d->file_size ? round($d->file_size/1024).' KB' : 'no file'; ?> · <?= $d->mime ?: ''; ?></small></span>
      <span style="display:flex;gap:4px;">
        <?php if($has_attachment): ?><a class="doc-btn" href="<?= base_url('patient_docs/download/' . (int)$d->id); ?>"><i class="fa fa-download"></i></a><?php endif; ?>
        <button class="doc-btn" onclick="docLog(<?= (int)$d->id; ?>)"><i class="fa fa-list"></i></button>
        <?php if($can['release'] && $has_attachment): ?>
          <?php if(!$d->released_to_patient): ?>
            <button class="doc-btn" onclick="docRelease(<?= (int)$d->id; ?>)">Release to patient</button>
          <?php else: ?>
            <button class="doc-btn" onclick="docUnrelease(<?= (int)$d->id; ?>)" style="background:#B91C1C;">Withdraw release</button>
          <?php endif; ?>
        <?php endif; ?>
      </span>
    </div>
  <?php endforeach; ?>
  <?php if(empty($documents)): ?><div style="color:var(--mp-muted);font-size:12px;"><?= $patient ? 'No documents for this patient.' : 'Open a patient profile or visit to manage documents.'; ?></div><?php endif; ?>

  <?php if($can['upload'] && $patient && $storage_ready): ?>
  <form onsubmit="return docUpload(this);" enctype="multipart/form-data" style="margin-top:12px;display:flex;gap:6px;align-items:center;">
    <input type="hidden" name="patient_id" value="<?= (int)$patient->id; ?>">
    <input type="text" name="title" class="mp-form-control" placeholder="Title *" required style="max-width:220px;">
    <select name="category" class="mp-form-control" style="max-width:150px;"><option value="clinical">Clinical</option><option value="investigation">Investigation</option><option value="consent">Consent</option><option value="id">Identification</option><option value="other">Other</option></select>
    <input type="file" name="doc_file" class="mp-form-control" required style="max-width:240px;">
    <button class="doc-btn">Upload</button>
  </form>
  <?php endif; ?>
</div>

<script>
var CSRF_BODY = { '<?= $this->security->get_csrf_token_name(); ?>':'<?= $this->security->get_csrf_hash(); ?>' };
function docUpload(f){
  var fd = new FormData(f);
  fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
  $.ajax({ url:'<?= base_url('patient_docs/upload'); ?>', type:'POST', data:fd, processData:false, contentType:false, dataType:'json',
    success:function(r){ if(r.status==='success'){ toastr.success(r.message); setTimeout(()=>location.reload(),700); } else toastr.error(r.message||'Failed'); },
    error:function(){ toastr.error('Upload failed'); }
  });
  return false;
}
function docRelease(id){ $.post('<?= base_url('patient_docs/release/'); ?>'+id, CSRF_BODY, function(r){ if(r.status==='success'){ toastr.success(r.message); setTimeout(()=>location.reload(),700); } else toastr.error(r.message||'Failed'); }, 'json'); }
function docUnrelease(id){
  if(!confirm('Withdraw this release? The patient loses portal access to this document immediately.')) return;
  $.post('<?= base_url('patient_docs/unrelease/'); ?>'+id, CSRF_BODY, function(r){ if(r.status==='success'){ toastr.success(r.message); setTimeout(()=>location.reload(),700); } else toastr.error(r.message||'Failed'); }, 'json');
}
function docLog(id){
  $.get('<?= base_url('patient_docs/log/'); ?>'+id, function(res){
    var msg = 'Versions:\n' + (res.versions||[]).map(function(v){ return 'v'+v.version_no+' · '+(v.file_size||0)+'B · '+(v.uploaded_by||'')+' '+v.created_at; }).join('\n');
    msg += '\n\nAccess log:\n' + (res.log||[]).map(function(l){ return l.action+' · '+(l.username||'?')+' · '+l.created_at; }).join('\n');
    alert(msg);
  }, 'json');
}
</script>
