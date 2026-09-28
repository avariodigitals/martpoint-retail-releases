<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); $r = $report; ?>
<style>
.ny-stage{border:1px solid var(--mp-border);border-radius:10px;margin-bottom:14px;overflow:hidden}
.ny-stage-head{display:flex;justify-content:space-between;align-items:center;padding:10px 16px;background:var(--mp-bg);cursor:pointer}
.ny-stage-head .t{font-weight:700;font-size:14px}
.ny-stage-body{padding:14px 16px;display:none}
.ny-stage.open .ny-stage-body{display:block}
.ny-log-form .form-control{margin-bottom:6px}
.ny-mini{display:grid;grid-template-columns:repeat(4,1fr);gap:10px}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($job->job_code); ?></h2>
    <div class="mp-page-sub">
      <?= htmlspecialchars($job->item_name); ?> · planned <?= format_qty($job->planned_qty); ?>
      <?= $job->order_code ? '· order <a href="'.base_url('nylon/order_view/'.$job->custom_order_id).'">'.htmlspecialchars($job->order_code).'</a> ('.htmlspecialchars($job->customer_name).')' : '· stock replenishment'; ?>
    </div>
  </div>
  <div style="display:flex;gap:8px;align-items:center;">
    <span class="label label-<?= Nylon_model::job_status_badge($job->status); ?>" style="font-size:13px;"><?= Nylon_model::job_status_label($job->status); ?></span>
    <a href="<?= base_url('nylon/jobs'); ?>" class="mp-qa-btn" style="background:var(--mp-muted);">← Jobs</a>
    <?php if ($can_approve && !in_array($job->status,['completed','cancelled'])): ?>
      <button class="mp-qa-btn" style="background:var(--mp-success);" onclick="nyCompleteJob(<?= $job->id; ?>)"><i class="fa fa-flag-checkered"></i> Complete Job</button>
    <?php endif; ?>
    <?php if ($can_edit && !in_array($job->status,['completed','cancelled'])): ?>
      <button class="mp-qa-btn" style="background:#B45309;" onclick="nyHoldJob(<?= $job->id; ?>)"><?= $job->status==='on_hold'?'Resume':'Hold'; ?></button>
    <?php endif; ?>
  </div>
</div>

<?php if ($order && $order->artwork_required && !$order->artwork_approved): ?>
<div class="mp-card-form" style="margin-bottom:16px;border-left:4px solid #B45309;">
  <div class="mp-card-body" style="padding:12px 16px;"><i class="fa fa-warning" style="color:#B45309;"></i>
    <strong>Artwork not approved.</strong> This order requires an approved design — the printing stage is blocked until a supervisor approves the artwork on the <a href="<?= base_url('nylon/order_view/'.$order->id); ?>">order</a>.
  </div>
</div>
<?php endif; ?>

<div class="mp-form-grid" style="grid-template-columns:2fr 1fr;gap:20px;align-items:start;">
  <div>
    <div class="mp-card-form" style="margin-bottom:20px;">
      <div class="mp-card-head"><h3><i class="fa fa-tasks"></i> Stages &amp; Shift Reports</h3></div>
      <div class="mp-card-body">
        <?php foreach ($stages as $s):
          $t = $r['stages'][$s->id];
          $blocked = $s->requires_artwork && $order && !$order->artwork_approved;
        ?>
        <div class="ny-stage <?= $s->status==='in_progress'?'open':''; ?>">
          <div class="ny-stage-head" onclick="this.parentNode.classList.toggle('open')">
            <span class="t"><i class="fa <?= $stage_defs[$s->stage_key]['icon'] ?? 'fa-circle'; ?>"></i> <?= $s->seq; ?>. <?= Nylon_model::stage_label($s->stage_key); ?>
              <?php if ($s->requires_artwork): ?><span class="label label-<?= $blocked?'warning':'success'; ?>" style="font-size:10px;">artwork <?= $blocked?'pending':'ok'; ?></span><?php endif; ?></span>
            <span>
              <span class="label label-<?= Nylon_model::stage_status_badge($s->status); ?>"><?= ucfirst($s->status); ?></span>
              <?php if ($t['pending_approval'] > 0): ?><span class="label label-warning" style="font-size:10px;"><?= $t['pending_approval']; ?> awaiting approval</span><?php endif; ?>
              <i class="fa fa-chevron-down" style="color:var(--mp-muted);"></i>
            </span>
          </div>
          <div class="ny-stage-body">
            <table class="mp-static-table" style="margin-bottom:10px;">
              <tr>
                <td class="text-muted">Input</td><td><?= $s->input_item_id ? htmlspecialchars($CI->db->select('item_name')->where('id',$s->input_item_id)->get('db_items')->row()->item_name ?? '') : '—'; ?> · logged <?= format_qty($t['in']); ?></td>
                <td class="text-muted">Output</td><td><?= $s->output_item_id ? htmlspecialchars($CI->db->select('item_name')->where('id',$s->output_item_id)->get('db_items')->row()->item_name ?? '') : '—'; ?> · good <?= format_qty($t['good']); ?></td>
              </tr>
              <tr>
                <td class="text-muted">Rejects</td><td class="<?= $t['reject']>0?'text-danger':''; ?>"><?= format_qty($t['reject']); ?></td>
                <td class="text-muted">Scrap / Waste</td><td><?= format_qty($t['scrap']); ?> reusable · <?= format_qty($t['waste']); ?> lost</td>
              </tr>
            </table>

            <?php if ($can_report && !in_array($s->status,['done','skipped']) && !in_array($job->status,['completed','cancelled'])): ?>
            <form class="ny-log-form" onsubmit="return nyReport(event, <?= $job->id; ?>, <?= $s->id; ?>);">
              <div class="ny-mini">
                <input type="date" name="work_date" class="form-control" value="<?= date('Y-m-d'); ?>">
                <input type="text" name="shift_label" class="form-control" placeholder="Shift (Morning/Night)">
                <select name="machine_id" class="form-control"><option value="">Machine —</option><?php foreach ($machines as $m): ?><option value="<?= $m->id; ?>"><?= htmlspecialchars($m->machine_name); ?></option><?php endforeach; ?></select>
                <select name="operator_id" class="form-control"><option value="">Operator —</option><?php foreach ($operators as $op): ?><option value="<?= $op->id; ?>"><?= htmlspecialchars(trim($op->first_name.' '.$op->last_name)); ?></option><?php endforeach; ?></select>
                <input type="number" step="any" name="qty_in" class="form-control" placeholder="Input qty">
                <input type="number" step="any" name="good_qty" class="form-control" placeholder="Good output">
                <input type="number" step="any" name="reject_qty" class="form-control" placeholder="Rejects">
                <input type="number" step="any" name="scrap_qty" class="form-control" placeholder="Reusable scrap">
                <input type="number" step="any" name="waste_qty" class="form-control" placeholder="Waste (lost)">
                <select name="scrap_item_id" class="form-control"><option value="">Scrap → item —</option><?php foreach ($scrap_items as $si): ?><option value="<?= $si->item_id; ?>"><?= htmlspecialchars($si->item_name); ?></option><?php endforeach; ?></select>
                <input type="text" name="notes" class="form-control" placeholder="Notes" style="grid-column:span 2;">
              </div>
              <button type="submit" class="mp-qa-btn" <?= $blocked ? 'disabled title="Artwork not approved"' : ''; ?>><?= $s->stage_key==='qc' ? 'Submit QC report (posts on approval)' : 'Report output'; ?></button>
            </form>
            <?php elseif ($blocked): ?>
              <p class="mp-muted" style="font-size:12px;"><i class="fa fa-lock"></i> Printing is blocked — artwork approval pending on the order.</p>
            <?php endif; ?>

            <?php if ($can_approve && in_array($s->status,['in_progress','pending'])): ?>
              <div style="margin-top:8px;">
                <button class="btn btn-xs btn-success" onclick="nyStage(<?= $s->id; ?>,'done')"><i class="fa fa-check"></i> Mark done</button>
                <?php if ($s->status === 'pending'): ?><button class="btn btn-xs btn-default" onclick="nyStage(<?= $s->id; ?>,'skip')"><i class="fa fa-forward"></i> Skip</button><?php endif; ?>
              </div>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="mp-card-form">
      <div class="mp-card-head"><h3><i class="fa fa-list-alt"></i> Report Log</h3></div>
      <div class="mp-card-body" style="padding:0!important;">
        <table class="mp-static-table">
          <thead><tr><th>When</th><th>Stage</th><th>Shift / Machine / Operator</th><th class="text-right">In</th><th class="text-right">Good</th><th class="text-right">Reject</th><th class="text-right">Scrap</th><th class="text-right">Waste</th><th>Status</th><th></th></tr></thead>
          <tbody>
          <?php $all_logs = $this->nylon->get_logs($job->id); // includes reversed rows — the audit trail stays visible ?>
          <?php if (empty($all_logs)): ?><tr><td colspan="10" style="padding:16px;color:var(--mp-muted);">No reports yet.</td></tr><?php endif; ?>
          <?php foreach ($all_logs as $l): ?>
            <tr>
              <td><small><?= show_date($l->work_date); ?><br><span class="text-muted"><?= htmlspecialchars($l->submitted_by); ?></span></small></td>
              <td><small><?= Nylon_model::stage_label($l->stage_key); ?></small></td>
              <td><small><?= htmlspecialchars(trim(($l->shift_label ?: '').' '.($l->machine_name ?: '').' '.trim(($l->first_name ?: '').' '.($l->last_name ?: '')))); ?></small></td>
              <td class="text-right"><?= format_qty($l->qty_in); ?></td>
              <td class="text-right text-success"><?= format_qty($l->good_qty); ?></td>
              <td class="text-right <?= $l->reject_qty>0?'text-danger':''; ?>"><?= format_qty($l->reject_qty); ?></td>
              <td class="text-right"><?= format_qty($l->scrap_qty); ?></td>
              <td class="text-right"><?= format_qty($l->waste_qty); ?></td>
              <td><span class="label label-<?= ['submitted'=>'warning','approved'=>'success','reversed'=>'danger'][$l->status] ?? 'default'; ?>"><?= ucfirst($l->status); ?></span>
                <?= $l->reversal_reason ? '<br><small class="text-muted">'.htmlspecialchars($l->reversal_reason).'</small>' : ''; ?></td>
              <td style="white-space:nowrap;">
                <?php if ($can_approve && $l->status === 'submitted'): ?>
                  <button class="btn btn-xs btn-success" title="Approve" onclick="nyApproveLog(<?= $l->id; ?>)"><i class="fa fa-check"></i></button>
                <?php endif; ?>
                <?php if ($can_approve && $l->status !== 'reversed'): ?>
                  <button class="btn btn-xs btn-danger" title="Reverse" onclick="nyReverseLog(<?= $l->id; ?>)"><i class="fa fa-undo"></i></button>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div>
    <?php if ($can_costing): ?>
    <div class="mp-card-form" style="margin-bottom:20px;">
      <div class="mp-card-head"><h3><i class="fa fa-calculator"></i> Cost &amp; Margin</h3></div>
      <div class="mp-card-body">
        <table class="mp-static-table">
          <tr><td class="text-muted">Material (actual)</td><td class="text-right"><?= $CI->currency($r['material_cost']); ?></td></tr>
          <tr><td class="text-muted">Other (actual)</td><td class="text-right"><?= $CI->currency($r['act_other']); ?></td></tr>
          <tr><td class="text-muted"><strong>Actual total</strong></td><td class="text-right"><strong><?= $CI->currency($r['act_total']); ?></strong></td></tr>
          <tr><td class="text-muted">Estimated total</td><td class="text-right"><?= $CI->currency($r['est_total']); ?></td></tr>
          <?php if ($r['order']): ?>
          <tr><td class="text-muted">Order value</td><td class="text-right"><?= $CI->currency($r['revenue']); ?></td></tr>
          <tr><td class="text-muted"><strong>Gross margin</strong></td><td class="text-right"><strong class="<?= $r['margin']>=0?'text-success':'text-danger'; ?>"><?= $CI->currency($r['margin']); ?><?= $r['margin_pct']!==null?' ('.$r['margin_pct'].'%)':''; ?></strong></td></tr>
          <?php endif; ?>
          <tr><td class="text-muted">Yield</td><td class="text-right"><?= $r['yield_pct']!==null ? $r['yield_pct'].'% of '.format_qty($r['planned_qty']).' planned' : '—'; ?></td></tr>
          <tr><td class="text-muted">Rejects / scrap / waste</td><td class="text-right"><?= format_qty($r['reject_qty']); ?> / <?= format_qty($r['scrap_qty']); ?> / <?= format_qty($r['waste_qty']); ?></td></tr>
        </table>
        <form onsubmit="return nyAddCost(event, <?= $job->id; ?>);" style="margin-top:10px;">
          <div class="ny-mini" style="grid-template-columns:1fr 1fr;">
            <select name="cost_type" class="form-control"><option value="labour">Labour</option><option value="machine">Machine time</option><option value="power">Power / diesel</option><option value="packaging">Packaging</option><option value="overhead">Overhead</option><option value="other">Other</option></select>
            <input type="number" step="any" name="amount" class="form-control" placeholder="Amount" required>
            <input type="text" name="description" class="form-control" placeholder="Description">
            <label style="font-size:12px;font-weight:400;"><input type="checkbox" name="estimated" value="1"> estimate only</label>
          </div>
          <button type="submit" class="mp-qa-btn" style="font-size:12px;"><i class="fa fa-plus"></i> Add cost</button>
        </form>
        <?php if (!empty($r['costs'])): ?>
        <table class="mp-static-table" style="margin-top:10px;">
          <?php foreach ($r['costs'] as $c): ?>
            <tr><td><small><?= ucfirst($c->cost_type); ?><?= $c->estimated ? ' <em>(est)</em>' : ''; ?><?= $c->description ? ' — '.htmlspecialchars($c->description) : ''; ?></small></td>
              <td class="text-right"><small><?= $CI->currency($c->amount); ?></small></td>
              <td><button class="btn btn-xs btn-danger" onclick="nyDelCost(<?= $c->id; ?>)"><i class="fa fa-trash"></i></button></td></tr>
          <?php endforeach; ?>
        </table>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="mp-card-form">
      <div class="mp-card-head"><h3><i class="fa fa-info-circle"></i> Job Info</h3></div>
      <div class="mp-card-body">
        <table class="mp-static-table">
          <tr><td class="text-muted">Kind</td><td><?= $job->job_kind === 'order' ? 'Customer order' : 'Stock replenishment'; ?></td></tr>
          <tr><td class="text-muted">Due</td><td><?= $job->due_date ? show_date($job->due_date) : '—'; ?></td></tr>
          <tr><td class="text-muted">Priority</td><td><?= ucfirst($job->priority); ?></td></tr>
          <tr><td class="text-muted">Created by</td><td><?= htmlspecialchars($job->created_by); ?></td></tr>
          <?php if ($job->completed_at): ?><tr><td class="text-muted">Completed</td><td><?= show_date($job->completed_at); ?> by <?= htmlspecialchars($job->approved_by); ?></td></tr><?php endif; ?>
          <?php if ($job->notes): ?><tr><td class="text-muted">Notes</td><td><?= nl2br(htmlspecialchars($job->notes)); ?></td></tr><?php endif; ?>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
var NY_CSRF_NAME = <?= json_encode($this->security->get_csrf_token_name()); ?>;
var NY_CSRF_HASH = <?= json_encode($this->security->get_csrf_hash()); ?>;
function nyPost(url, data){
  var fd = new FormData();
  for (var k in data) fd.append(k, data[k]);
  fd.append(NY_CSRF_NAME, NY_CSRF_HASH);
  return fetch('<?= base_url(); ?>'+url, {method:'POST', body:fd}).then(function(r){return r.json();});
}
function nyHandle(d){ if (d.csrf_hash) NY_CSRF_HASH = d.csrf_hash; if (!d.success) { alert(d.message || 'Failed'); } else { location.reload(); } }
function nyReport(ev, job, stage){
  ev.preventDefault();
  var f = ev.target;
  var data = {job_id:job, stage_id:stage};
  new FormData(f).forEach(function(v,k){ data[k]=v; });
  nyPost('nylon/stage_log', data).then(nyHandle);
  return false;
}
function nyApproveLog(id){ nyPost('nylon/log_approve',{id:id}).then(nyHandle); }
function nyReverseLog(id){
  var reason = prompt('Reason for reversing this report (posts a counter-adjustment):');
  if (reason === null) return;
  nyPost('nylon/log_reverse',{id:id,reason:reason}).then(nyHandle);
}
function nyStage(id, action){ nyPost(action==='done'?'nylon/stage_done':'nylon/stage_skip',{id:id}).then(nyHandle); }
function nyCompleteJob(id){ if(confirm('Complete this job? All stages must be done and QC approved.')) nyPost('nylon/job_complete',{id:id}).then(nyHandle); }
function nyHoldJob(id){ nyPost('nylon/job_status',{id:id,status:<?= json_encode($job->status==='on_hold'?'in_progress':'on_hold'); ?>}).then(nyHandle); }
function nyAddCost(ev, job){
  ev.preventDefault();
  var data = {job_id:job};
  new FormData(ev.target).forEach(function(v,k){ data[k]=v; });
  nyPost('nylon/cost_save', data).then(nyHandle);
  return false;
}
function nyDelCost(id){ if(confirm('Remove this cost line?')) nyPost('nylon/cost_delete',{id:id}).then(nyHandle); }
</script>
