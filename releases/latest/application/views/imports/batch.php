<?php $this->load->view('admin/desktop/_styles'); $CI =& get_instance(); ?>
<style>
.imp-box{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:14px!important;margin-bottom:14px!important}
.imp-box h4{font-size:11px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:var(--mp-muted)!important;margin:0 0 10px 0!important}
.imp-table{width:100%!important;border-collapse:collapse!important;font-size:12px!important}
.imp-table th{text-align:left!important;padding:5px 8px!important;border-bottom:1px solid var(--mp-border)!important;font-size:10px!important;text-transform:uppercase!important;color:var(--mp-muted)!important}
.imp-table td{padding:6px 8px!important;border-bottom:1px solid #F1F5F9!important;vertical-align:top!important}
.imp-badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:10px!important;font-weight:800!important;text-transform:uppercase}
.imp-inserted{background:#D1FAE5;color:#065F46}.imp-skipped{background:#F1F5F9;color:#475569}
.imp-conflict{background:#FEF3C7;color:#92400E}.imp-failed{background:#FEE2E2;color:#991B1B}
.imp-rolled_back{background:#FEE2E2;color:#991B1B}
</style>

<div class="mp-page-head">
  <div><h2><?= htmlspecialchars($page_title); ?></h2>
  <div class="mp-page-sub"><a href="<?= base_url('imports'); ?>">&larr; All batches</a> · <?= htmlspecialchars($batch->source_system); ?> · <?= htmlspecialchars($batch->mode); ?> · started <?= htmlspecialchars($batch->started_at ?: '—'); ?> by <?= htmlspecialchars($batch->started_by_name ?: '—'); ?></div></div>
  <div>
    <?php if($can['run'] && $batch->status === 'paused'): ?>
      <button class="mp-qa-btn blue" onclick="impAct('resume')">Resume</button>
    <?php endif; ?>
    <?php if($can['run'] && $batch->status === 'running'): ?>
      <button class="mp-qa-btn" onclick="impAct('pause')">Pause</button>
    <?php endif; ?>
    <?php if($can['rollback'] && in_array($batch->status, array('completed','completed_with_exceptions','paused')) && $batch->mode === 'import'): ?>
      <button class="mp-qa-btn" style="background:#B91C1C;color:#fff;" onclick="impRollback()">Roll back batch</button>
    <?php endif; ?>
  </div>
</div>

<div class="imp-box">
  <h4>Reconciliation</h4>
  <div style="display:flex;gap:18px;font-size:13px;flex-wrap:wrap;">
    <div><b><?= (int)$reconciliation['patients_created']; ?></b> patients created</div>
    <?php foreach($reconciliation['by_action'] as $a=>$n): if(!$n) continue; ?>
      <div><b><?= (int)$n; ?></b> <?= htmlspecialchars($a); ?></div>
    <?php endforeach; ?>
    <div>Approved openings — debt <b><?= number_format($reconciliation['opening']['debt'],2); ?></b>,
      funds <b><?= number_format($reconciliation['opening']['funds'],2); ?></b>,
      session units <b><?= (int)$reconciliation['opening']['units']; ?></b></div>
    <?php if($reconciliation['opening']['pending']): ?>
      <div><b><?= (int)$reconciliation['opening']['pending']; ?></b> positions still pending approval</div>
    <?php endif; ?>
  </div>
</div>

<?php if(!empty($reconciliation['conflicts'])): ?>
<div class="imp-box">
  <h4>Duplicate candidates — review required (never auto-merged)</h4>
  <table class="imp-table">
    <tr><th>Source row</th><th>Why flagged</th><th>Imported patient</th></tr>
    <?php foreach($reconciliation['conflicts'] as $r):
      $targets = json_decode($r->targets_json, true) ?: array(); $pid = null;
      foreach($targets as $t) if(($t['table'] ?? '') === 'db_patients') $pid = (int)$t['id']; ?>
    <tr>
      <td><?= htmlspecialchars($r->source_table . '#' . $r->source_id); ?></td>
      <td><?= htmlspecialchars($r->dupe_hint ?: 'possible duplicate'); ?></td>
      <td><?= $pid ? '<a href="' . base_url('patients/profile/' . $pid) . '">Patient #' . $pid . '</a>' : '—'; ?></td>
    </tr>
    <?php endforeach; ?>
  </table>
</div>
<?php endif; ?>

<div class="imp-box">
  <h4>Row ledger</h4>
  <table class="imp-table">
    <tr><th>Source</th><th>Source ID</th><th>Action</th><th>Targets</th><th>Note / error</th><th>At</th></tr>
    <?php foreach($rows as $r):
      $targets = json_decode($r->targets_json, true) ?: array(); ?>
    <tr>
      <td><?= htmlspecialchars($r->source_table); ?></td>
      <td><?= htmlspecialchars($r->source_id); ?></td>
      <td><span class="imp-badge imp-<?= htmlspecialchars($r->action); ?>"><?= htmlspecialchars($r->action); ?></span></td>
      <td><small><?= htmlspecialchars(implode(', ', array_map(function($t){ return ($t['table'] ?? '?') . '#' . ($t['id'] ?? '?'); }, $targets))); ?></small></td>
      <td><small><?= htmlspecialchars($r->dupe_hint ?: ($r->error ?: '—')); ?></small></td>
      <td><small><?= htmlspecialchars($r->created_at); ?></small></td>
    </tr>
    <?php endforeach; ?>
    <?php if(empty($rows)): ?><tr><td colspan="6" style="color:var(--mp-muted);">No rows recorded.</td></tr><?php endif; ?>
  </table>
</div>

<script>
var CSRF = <?= json_encode(array($this->security->get_csrf_token_name() => $this->security->get_csrf_hash())); ?>;
function impAct(act){
  $.post('<?= base_url('imports'); ?>/'+act+'/<?= (int)$batch->id; ?>', CSRF, function(r){
    if(r.status==='success'){ toastr.success(r.message); setTimeout(()=>location.reload(),700); } else toastr.error(r.message||'Failed');
  }, 'json');
}
function impRollback(){
  if(!confirm('Roll back this batch? Every target row it created (patients, customers) is deleted in reverse order. This cannot be undone.')) return;
  $.post('<?= base_url('imports/rollback/' . (int)$batch->id); ?>', CSRF, function(r){
    if(r.status==='success'){ toastr.success(r.message); setTimeout(()=>location.reload(),900); } else toastr.error(r.message||'Failed');
  }, 'json');
}
</script>
