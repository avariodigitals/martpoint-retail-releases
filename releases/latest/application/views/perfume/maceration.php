<?php $this->load->view('admin/desktop/_styles'); ?>
<style>
.mac-card{background:var(--mp-surface);border:1px solid var(--mp-border);border-radius:12px;padding:16px 18px;margin-bottom:14px;display:flex;gap:18px;align-items:center;flex-wrap:wrap}
.mac-card .ring{min-width:74px;text-align:center}
.mac-card .ring .big{font-size:22px;font-weight:800;line-height:1}
.mac-card .ring .cap{font-size:10px;text-transform:uppercase;letter-spacing:.5px;color:var(--mp-muted);margin-top:3px}
.mac-card .body{flex:1;min-width:220px}
.mac-card .body h4{margin:0 0 4px;font-size:15px}
.mac-card .meta{font-size:12px;color:var(--mp-muted)}
.mac-progress{height:8px;border-radius:4px;background:var(--mp-bg);overflow:hidden;margin-top:10px;max-width:340px}
.mac-progress > span{display:block;height:100%;border-radius:4px;background:#B45309}
.mac-actions{margin-left:auto;display:flex;flex-direction:column;gap:6px;align-items:flex-end}
.mac-btn{border:1px solid var(--mp-border);background:var(--mp-surface);border-radius:8px;padding:7px 14px;font-size:12px;font-weight:600;cursor:pointer;color:var(--mp-ink)}
.mac-btn.primary{background:var(--mp-primary);border-color:var(--mp-primary);color:#fff}
.mac-btn.success{background:var(--mp-success);border-color:var(--mp-success);color:#fff}
.mac-btn:hover{opacity:.9}
.mac-group-title{font-size:13px;font-weight:700;text-transform:uppercase;letter-spacing:.4px;color:var(--mp-muted);margin:26px 0 12px;display:flex;align-items:center;gap:8px}
.mac-group-title i{color:var(--mp-primary)}
</style>

<div class="mp-page-head">
  <div>
    <h2>Maceration Tracker</h2>
    <div class="mp-page-sub">Every blend resting in the cellar — when it started, when it is ready</div>
  </div>
  <a href="<?= base_url('perfume'); ?>" class="mp-qa-btn blue"><i class="fa fa-arrow-left"></i> Perfume Lab</a>
</div>

<div class="mac-group-title"><i class="fa fa-hourglass-half"></i> Resting in Maceration (<?= count($macerating); ?>)</div>
<?php if (!empty($macerating)): foreach ($macerating as $b):
  $pct = ($b->required_days > 0 && $b->days_in !== null) ? min(100, round($b->days_in / $b->required_days * 100)) : 0;
  $overdue = ($b->days_left !== null && $b->days_left < 0);
?>
  <div class="mac-card">
    <div class="ring">
      <div class="big" style="color:<?= $overdue ? 'var(--mp-success)' : '#B45309'; ?>;"><?= $b->days_in !== null ? $b->days_in : '?'; ?></div>
      <div class="cap">days in</div>
    </div>
    <div class="body">
      <h4><?= htmlspecialchars($b->batch_name); ?> <span class="label label-default" style="font-size:11px;"><?= htmlspecialchars($b->batch_code); ?></span></h4>
      <div class="meta">
        <?= htmlspecialchars(implode(', ', $b->recipes) ?: 'No formula linked'); ?> ·
        Started <?= $b->maceration_started ? show_date($b->maceration_started) : '—'; ?> ·
        Needs <?= $b->required_days > 0 ? $b->required_days . ' days' : 'time not set on formula'; ?>
        <?php if ($b->ready_date): ?> · <strong>Ready <?= show_date($b->ready_date); ?></strong><?php endif; ?>
      </div>
      <?php if ($b->required_days > 0): ?>
      <div class="mac-progress"><span style="width:<?= $pct; ?>%"></span></div>
      <div class="meta" style="margin-top:4px;">
        <?= $overdue ? 'Aging complete — ' . abs($b->days_left) . ' day(s) past minimum. Ready to filter &amp; bottle.' : $b->days_left . ' day(s) remaining (' . $pct . '%)'; ?>
      </div>
      <?php else: ?>
      <div class="meta" style="margin-top:8px;"><i class="fa fa-info-circle"></i> Set <em>maceration days</em> on the formula to get a ready date.</div>
      <?php endif; ?>
    </div>
    <div class="mac-actions">
      <?php if ($overdue || $b->required_days === 0): ?>
      <button class="mac-btn primary" onclick="moveBatch(<?= $b->id; ?>, 'filtering')"><i class="fa fa-filter"></i> Move to Filtering</button>
      <?php else: ?>
      <button class="mac-btn" onclick="moveBatch(<?= $b->id; ?>, 'filtering')"><i class="fa fa-filter"></i> Release Early</button>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; else: ?>
  <div class="mp-card-form"><div class="mp-card-body" style="text-align:center;color:var(--mp-muted);">
    <i class="fa fa-hourglass-o" style="font-size:30px;display:block;margin-bottom:10px;"></i>
    Nothing is macerating. When a blend is done mixing, move it here from the list below.
  </div></div>
<?php endif; ?>

<div class="mac-group-title"><i class="fa fa-flask"></i> In Preparation (<?= count($pipeline); ?>)</div>
<?php if (!empty($pipeline)): foreach ($pipeline as $b): ?>
  <div class="mac-card">
    <div class="body">
      <h4><?= htmlspecialchars($b->batch_name); ?> <span class="label label-default" style="font-size:11px;"><?= htmlspecialchars($b->batch_code); ?></span></h4>
      <div class="meta">
        <span class="label label-<?= Production_batches_model::status_badge($b->status); ?>"><?= Production_batches_model::status_label($b->status); ?></span> ·
        <?= htmlspecialchars(implode(', ', $b->recipes) ?: 'No formula linked'); ?> ·
        Scheduled <?= show_date($b->scheduled_date); ?>
      </div>
    </div>
    <div class="mac-actions">
      <?php if ($b->status === 'planned'): ?>
      <button class="mac-btn" onclick="moveBatch(<?= $b->id; ?>, 'sourcing')"><i class="fa fa-shopping-basket"></i> Start Sourcing</button>
      <?php elseif ($b->status === 'sourcing'): ?>
      <button class="mac-btn" onclick="moveBatch(<?= $b->id; ?>, 'blending')"><i class="fa fa-flask"></i> Start Blending</button>
      <?php elseif ($b->status === 'blending'): ?>
      <button class="mac-btn primary" onclick="moveBatch(<?= $b->id; ?>, 'macerating')"><i class="fa fa-hourglass-start"></i> Into Maceration</button>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; else: ?>
  <p style="color:var(--mp-muted);font-size:13px;">No batches in preparation. <a href="<?= base_url('operations/production_batch'); ?>">Create a blend batch</a>.</p>
<?php endif; ?>

<div class="mac-group-title"><i class="fa fa-tag"></i> Filtering, Bottling &amp; Ready (<?= count($finishing); ?>)</div>
<?php if (!empty($finishing)): foreach ($finishing as $b): ?>
  <div class="mac-card">
    <div class="body">
      <h4><?= htmlspecialchars($b->batch_name); ?> <span class="label label-default" style="font-size:11px;"><?= htmlspecialchars($b->batch_code); ?></span></h4>
      <div class="meta">
        <span class="label label-<?= Production_batches_model::status_badge($b->status); ?>"><?= Production_batches_model::status_label($b->status); ?></span> ·
        <?= htmlspecialchars(implode(', ', $b->recipes) ?: 'No formula linked'); ?>
        <?php if ($b->maceration_started): ?> · Macerated <?= $b->days_in; ?> day(s)<?php endif; ?>
      </div>
    </div>
    <div class="mac-actions">
      <?php if ($b->status === 'filtering'): ?>
      <button class="mac-btn" onclick="moveBatch(<?= $b->id; ?>, 'bottling')"><i class="fa fa-tag"></i> Move to Bottling</button>
      <?php elseif ($b->status === 'bottling'): ?>
      <button class="mac-btn" onclick="moveBatch(<?= $b->id; ?>, 'ready')"><i class="fa fa-check"></i> Mark Ready</button>
      <?php elseif ($b->status === 'ready'): ?>
      <button class="mac-btn success" onclick="moveBatch(<?= $b->id; ?>, 'completed')"><i class="fa fa-check-circle"></i> Complete &amp; Post Stock</button>
      <?php endif; ?>
    </div>
  </div>
<?php endforeach; else: ?>
  <p style="color:var(--mp-muted);font-size:13px;">Nothing in finishing stages.</p>
<?php endif; ?>

<script>
function moveBatch(id, status){
  var notes = { 'macerating':'Start maceration now?', 'completed':'Complete this batch? Raw materials will be deducted and the finished product added to stock.' };
  if (notes[status] && !confirm(notes[status])) return;
  $.post('<?= base_url('perfume/batch_status'); ?>', {id: id, status: status}, function(res){
    if(res.success){ toastr.success(res.message); setTimeout(function(){ location.reload(); }, 600); }
    else { toastr.error(res.message || 'Failed'); }
  }, 'json').fail(function(){ toastr.error('Server error'); });
}
</script>
