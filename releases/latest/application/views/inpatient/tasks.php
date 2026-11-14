<?php $this->load->view('admin/desktop/_styles'); $CI =& get_instance(); ?>
<style>
.it-box{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:14px!important;margin-bottom:14px!important}
.it-box h4{font-size:11px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:var(--mp-muted)!important;margin:0 0 10px 0!important}
.it-table{width:100%!important;border-collapse:collapse!important;font-size:12px!important}
.it-table th{text-align:left!important;padding:5px 8px!important;border-bottom:1px solid var(--mp-border)!important;font-size:10px!important;text-transform:uppercase!important;color:var(--mp-muted)!important}
.it-table td{padding:6px 8px!important;border-bottom:1px solid #F1F5F9!important;vertical-align:top!important}
.it-badge{display:inline-block;padding:2px 8px;border-radius:10px;font-size:10px;font-weight:800;text-transform:uppercase}
.it-open{background:#DBEAFE;color:#1E40AF}.it-done,.it-completed{background:#D1FAE5;color:#065F46}
.it-overdue{background:#FEE2E2;color:#991B1B}.it-in_progress{background:#FEF3C7;color:#92400E}
.it-suppressed,.it-cancelled,.it-failed{background:#F3F4F6;color:#6B7280}
.it-tabs{display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap}
.it-tab{display:inline-flex;align-items:center;gap:8px;padding:9px 16px;border:1px solid var(--mp-border);border-radius:999px;background:var(--mp-surface);color:var(--mp-muted);font-size:13px;font-weight:600;text-decoration:none}
.it-tab:hover{border-color:var(--mp-primary);color:var(--mp-primary);text-decoration:none}
.it-tab.active{background:var(--mp-primary);border-color:var(--mp-primary);color:#fff}
</style>

<div class="mp-page-head">
  <div><h2><?= $board === 'nursing' ? 'Nursing Tasks' : 'Porter Tasks'; ?></h2>
  <div class="mp-page-sub"><?= $board === 'nursing'
      ? 'Nursing schedule for every admission — overdue work is highlighted.'
      : 'Porter movement tasks between locations — overdue work is highlighted.'; ?></div></div>
  <div><a class="mp-qa-btn" href="<?= base_url('inpatient'); ?>">Admissions</a>
       <a class="mp-qa-btn blue" href="<?= base_url('inpatient/beds'); ?>">Bed board</a></div>
</div>

<?php /* Both boards are always reachable; the selected one is shown first and marked. */ ?>
<?php if($can_porter || $can_nursing): ?>
<div class="it-tabs" role="tablist">
  <?php if($can_porter): ?>
  <a role="tab" aria-selected="<?= $board === 'porter' ? 'true' : 'false'; ?>"
     class="it-tab<?= $board === 'porter' ? ' active' : ''; ?>"
     href="<?= base_url('inpatient/tasks?board=porter'); ?>">
    <i class="fa fa-exchange"></i> Porter tasks
  </a>
  <?php endif; ?>
  <?php if($can_nursing): ?>
  <a role="tab" aria-selected="<?= $board === 'nursing' ? 'true' : 'false'; ?>"
     class="it-tab<?= $board === 'nursing' ? ' active' : ''; ?>"
     href="<?= base_url('inpatient/tasks?board=nursing'); ?>">
    <i class="fa fa-heartbeat"></i> Nursing tasks
  </a>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php if($can_porter && $board === 'porter'): ?>
<div class="it-box">
  <h4>Porter movement tasks</h4>
  <table class="it-table">
    <tr><th>#</th><th>Type</th><th>Patient / Admission</th><th>From → To</th><th>Status</th><th>Requested</th><th>Done</th><th></th></tr>
    <?php foreach($porter as $t): ?>
    <tr>
      <td>T-<?= (int)$t->id; ?></td><td><?= htmlspecialchars($t->task_type); ?></td>
      <td><?= htmlspecialchars(($t->patient_code ?? '') . ' ' . ($t->patient_name ?? '')); ?> <small><?= htmlspecialchars($t->admission_code ?? ''); ?></small></td>
      <td><?= htmlspecialchars(($t->from_location ?? '—') . ' → ' . ($t->to_location ?? '—')); ?></td>
      <td><span class="it-badge it-<?= htmlspecialchars($t->status); ?>"><?= htmlspecialchars($t->status); ?></span></td>
      <td><?= htmlspecialchars($t->requested_by ?? '—'); ?></td>
      <td><?= $t->completed_by ? 'u#' . (int)$t->completed_by . ' ' . htmlspecialchars($t->completed_at) : '—'; ?></td>
      <td>
        <?php if($can['porter'] && $t->status === 'open'): ?><button class="mp-qa-btn blue" onclick="itAct(<?= (int)$t->id; ?>,'claim_task')">Claim</button><?php endif; ?>
        <?php if($can['porter'] && in_array($t->status, array('open','in_progress'))): ?><button class="mp-qa-btn green" onclick="itAct(<?= (int)$t->id; ?>,'complete_task')">Complete</button><?php endif; ?>
        <?php if(in_array($t->status, array('open','in_progress'))): ?><button class="mp-qa-btn" onclick="itAct(<?= (int)$t->id; ?>,'cancel_task')">Cancel</button><?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if(empty($porter)): ?><tr><td colspan="8" style="color:var(--mp-muted)">No porter tasks.</td></tr><?php endif; ?>
  </table>
</div>
<?php endif; ?>

<?php if($can_nursing && $board === 'nursing'): ?>
<div class="it-box">
  <h4>Nursing tasks (all admissions)</h4>
  <table class="it-table">
    <tr><th>Admission</th><th>Patient</th><th>Task</th><th>Due</th><th>Status</th><th>Done</th><th></th></tr>
    <?php foreach($nursing as $t): ?>
    <tr>
      <td><?= htmlspecialchars($t->admission_code); ?></td><td><?= htmlspecialchars($t->patient_name); ?></td>
      <td><?= htmlspecialchars($t->label); ?></td><td><?= htmlspecialchars($t->due_at); ?></td>
      <td><span class="it-badge it-<?= $t->is_overdue ? 'overdue' : htmlspecialchars($t->status); ?>"><?= $t->is_overdue ? 'overdue' : htmlspecialchars($t->status); ?></span></td>
      <td><?= $t->done_by ? 'u#' . (int)$t->done_by : '—'; ?></td>
      <td><?php if($t->status === 'open' && $can['nurse']): ?><button class="mp-qa-btn green" onclick="itNurse(<?= (int)$t->id; ?>)">Done</button><?php endif; ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if(empty($nursing)): ?><tr><td colspan="7" style="color:var(--mp-muted)">No nursing tasks.</td></tr><?php endif; ?>
  </table>
</div>
<?php endif; ?>

<script>
function itAct(id, act){ fetch('<?= base_url('inpatient/'); ?>'+act+'/'+id, {method:'POST'})
  .then(r=>r.json()).then(d=>{ alert(d.message||''); if(d.status==='success') location.reload(); }); }
function itNurse(id){ var n = prompt('Result note (optional)')||'';
  var fd = new FormData(); fd.append('note', n);
  fetch('<?= base_url('inpatient/complete_nursing_task/'); ?>'+id, {method:'POST', body:fd})
    .then(r=>r.json()).then(d=>{ alert(d.message||''); if(d.status==='success') location.reload(); }); }
</script>
