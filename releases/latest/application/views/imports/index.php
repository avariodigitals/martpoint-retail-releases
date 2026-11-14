<?php $this->load->view('admin/desktop/_styles'); $CI =& get_instance(); ?>
<style>
.imp-box{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:14px!important;margin-bottom:14px!important}
.imp-box h4{font-size:11px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:var(--mp-muted)!important;margin:0 0 10px 0!important}
.imp-table{width:100%!important;border-collapse:collapse!important;font-size:12px!important}
.imp-table th{text-align:left!important;padding:5px 8px!important;border-bottom:1px solid var(--mp-border)!important;font-size:10px!important;text-transform:uppercase!important;color:var(--mp-muted)!important}
.imp-table td{padding:6px 8px!important;border-bottom:1px solid #F1F5F9!important;vertical-align:top!important}
.imp-badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:10px!important;font-weight:800!important;text-transform:uppercase}
.imp-running{background:#DBEAFE;color:#1E40AF}.imp-completed{background:#D1FAE5;color:#065F46}
.imp-completed_with_exceptions{background:#FEF3C7;color:#92400E}.imp-paused{background:#F1F5F9;color:#475569}
.imp-rolled_back{background:#FEE2E2;color:#991B1B}.imp-failed{background:#FEE2E2;color:#991B1B}
.imp-pending{background:#FEF3C7!important;color:#92400E!important;border:1px dashed #F59E0B!important;border-radius:8px;padding:8px 10px;font-size:12px;margin-bottom:10px}
textarea.imp-json{width:100%!important;min-height:110px;font-family:monospace;font-size:11px;border:1px solid var(--mp-border);border-radius:8px;padding:8px}
</style>

<div class="mp-page-head">
  <div><h2><?= htmlspecialchars($page_title); ?></h2>
  <div class="mp-page-sub">Repeatable, resumable, rollbackable migration runs. Source IDs are preserved; nothing is auto-merged; no portal invitations are sent.</div></div>
</div>

<?php if(empty($source_ready)): ?>
<div class="imp-pending">
  <b>Source mapping pending:</b> the Smart Hospital 4.0 SQL dump has not yet been supplied.
  The framework below is complete — table-level column mapping and the extractors are
  filled in once the structure-only export is inspected. The interim intake accepts a
  normalized JSON extract (same contract the SH4 extractor will produce).
</div>
<?php endif; ?>

<div class="imp-box">
  <h4>Reconciliation — this store</h4>
  <div style="display:flex;gap:18px;font-size:13px;">
    <div><b><?= (int)($reconciliation['patients_imported'] ?? 0); ?></b> patients imported</div>
    <div><b><?= (int)($reconciliation['positions_pending'] ?? 0); ?></b> opening positions pending review</div>
    <div><b><?= (int)($reconciliation['positions_approved'] ?? 0); ?></b> opening positions approved/posted</div>
  </div>
</div>

<?php if($can['run']): ?>
<div class="imp-box">
  <h4>Patient-only import</h4>
  <p>Imports patient identities and details only. Debts, credits, visits, admissions and documents are excluded.</p>
  <form onsubmit="return impRun(this);">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
    <div style="display:flex;gap:8px;margin-bottom:8px;">
      <input name="label" placeholder="Batch label (e.g. SH4 export 2026-10-02)" style="flex:2;padding:6px 8px;border:1px solid var(--mp-border);border-radius:8px;">
      <select name="mode" style="padding:6px 8px;border:1px solid var(--mp-border);border-radius:8px;">
        <option value="dry_run">Dry run (validate only)</option>
        <option value="import">Import</option>
      </select>
    </div>
    <textarea class="imp-json" name="extract_json" placeholder='{"patients":[{"legacy_id":"SH-1001","name":"Jane Doe","phone":"080...","dob":"1980-04-12","gender":"female","deceased":0,"inactive":0}]}'></textarea>
    <label style="display:block;margin-top:8px">Or choose a reviewed patient JSON extract <input type="file" accept=".json,application/json" onchange="impLoadFile(this)"></label>
    <div style="margin-top:8px;">
      <button class="mp-qa-btn blue" type="submit">Run batch</button>
      <small style="color:var(--mp-muted);">Dry run first. Identity columns map to db_customers + db_patients; legacy IDs are kept in legacy_ids_json and db_migration_rows.</small>
    </div>
  </form>
</div>
<?php endif; ?>

<div class="imp-box">
  <h4>Batches</h4>
  <table class="imp-table">
    <tr><th>#</th><th>Source</th><th>Label</th><th>Mode</th><th>Seen</th><th>Imported</th><th>Skipped</th><th>Conflicts</th><th>Failed</th><th>Status</th><th>Started</th><th></th></tr>
    <?php foreach($batches as $b): ?>
    <tr>
      <td><?= (int)$b->id; ?></td>
      <td><?= htmlspecialchars($b->source_system); ?></td>
      <td><?= htmlspecialchars($b->label ?: '—'); ?></td>
      <td><?= htmlspecialchars($b->mode); ?></td>
      <td><?= (int)$b->rows_seen; ?></td>
      <td><?= (int)$b->rows_imported; ?></td>
      <td><?= (int)$b->rows_skipped; ?></td>
      <td><?= (int)$b->rows_conflicts; ?></td>
      <td><?= (int)$b->rows_failed; ?></td>
      <td><span class="imp-badge imp-<?= htmlspecialchars($b->status); ?>"><?= htmlspecialchars(str_replace('_',' ',$b->status)); ?></span></td>
      <td><small><?= htmlspecialchars($b->started_at ?: ''); ?> · <?= htmlspecialchars($b->started_by_name ?: ''); ?></small></td>
      <td><a href="<?= base_url('imports/batch/' . (int)$b->id); ?>">View</a></td>
    </tr>
    <?php endforeach; ?>
    <?php if(empty($batches)): ?><tr><td colspan="12" style="color:var(--mp-muted);">No batches yet.</td></tr><?php endif; ?>
  </table>
</div>

<script>
function impLoadFile(input){ if(!input.files[0]) return;var reader=new FileReader();
  reader.onload=function(){try{var data=JSON.parse(reader.result);if(!Array.isArray(data.patients)) throw Error();input.form.extract_json.value=reader.result;}catch(e){alert('Choose a patient JSON extract. SQL files cannot be imported here.');input.value='';}};
  reader.readAsText(input.files[0]);
}
function impRun(f){
  $.post('<?= base_url('imports/run'); ?>', $(f).serialize(), function(r){
    if(r.status==='success'){ toastr.success(r.message); setTimeout(()=>location.href='<?= base_url('imports/batch/'); ?>'+r.batch_id,700); }
    else toastr.error(r.message||'Failed');
  }, 'json');
  return false;
}
</script>
