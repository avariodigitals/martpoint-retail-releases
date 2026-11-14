<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.waste-bar{height:8px;border-radius:4px;background:var(--mp-bg);overflow:hidden;min-width:80px}
.waste-bar > span{display:block;height:100%;background:var(--mp-danger)}
</style>

<div class="mp-page-head">
  <div>
    <h2>Wastage &amp; Losses</h2>
    <div class="mp-page-sub">Log every loss — it posts to stock automatically — and see what waste really costs</div>
  </div>
  <a href="<?= base_url('perfume'); ?>" class="mp-qa-btn blue"><i class="fa fa-arrow-left"></i> Perfume Lab</a>
</div>

<?php if (!empty($message)): ?>
<div class="alert alert-info" style="margin-bottom:16px;"><?= htmlspecialchars($message); ?></div>
<?php endif; ?>

<div class="mp-form-grid" style="grid-template-columns: repeat(4, 1fr); gap: 16px; margin: 20px 0;">
  <div class="mp-card-form" style="text-align:center;"><div class="mp-card-body">
    <h1 style="margin:0;color:var(--mp-danger);"><?= $CI->currency($summary['total_cost']); ?></h1>
    <p class="mp-muted">Total Losses (All Time)</p></div></div>
  <div class="mp-card-form" style="text-align:center;"><div class="mp-card-body">
    <h1 style="margin:0;color:var(--mp-danger);"><?= $CI->currency($summary['month_cost']); ?></h1>
    <p class="mp-muted">This Month</p></div></div>
  <div class="mp-card-form" style="text-align:center;"><div class="mp-card-body">
    <h1 style="margin:0;color:var(--mp-primary);"><?= !empty($summary['by_stage']) ? htmlspecialchars(Perfume_model::stage_label($summary['by_stage'][0]->stage)) : '—'; ?></h1>
    <p class="mp-muted">Costliest Stage</p></div></div>
  <div class="mp-card-form" style="text-align:center;"><div class="mp-card-body">
    <h1 style="margin:0;color:var(--mp-primary);"><?= count($wastage); ?></h1>
    <p class="mp-muted">Logged Events</p></div></div>
</div>

<div class="mp-form-grid" style="grid-template-columns: 380px 1fr; gap: 20px; align-items:start;">

  <div class="mp-card-form" style="margin-bottom:0;">
    <div class="mp-card-head"><h3><i class="fa fa-plus-circle"></i> Log a Loss</h3></div>
    <div class="mp-card-body">
      <form method="post" action="<?= base_url('perfume/wastage'); ?>">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <div class="mp-form-group">
          <label>Material <span class="text-danger">*</span></label>
          <select class="mp-form-control select2" name="item_id" id="waste-item" required>
            <option value="">-- Select item --</option>
            <?php foreach ($items as $it): ?>
            <option value="<?= $it->id; ?>" data-unit="<?= htmlspecialchars($it->unit_name ?: ''); ?>" data-cost="<?= $it->purchase_price; ?>" data-stock="<?= $it->stock; ?>"><?= htmlspecialchars($it->item_name); ?><?= $it->not_for_sale ? ' (raw material)' : ''; ?> — <?= format_qty($it->stock); ?> <?= htmlspecialchars($it->unit_name ?: ''); ?> in stock</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mp-form-group" style="margin-top:14px;">
          <label>Stage <span class="text-danger">*</span></label>
          <select class="mp-form-control" name="stage" required>
            <?php foreach ($stages as $sk => $sl): ?>
            <option value="<?= $sk; ?>"><?= htmlspecialchars($sl); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mp-form-grid" style="margin-top:14px;">
          <div class="mp-form-group"><label>Qty Lost <span class="text-danger">*</span></label><input type="number" step="0.001" min="0.001" class="mp-form-control" name="qty" required placeholder="e.g. 25"></div>
          <div class="mp-form-group"><label>Unit</label><input type="text" class="mp-form-control" name="unit_name" id="waste-unit" placeholder="ml"></div>
        </div>
        <div class="mp-form-grid" style="margin-top:14px;">
          <div class="mp-form-group"><label>Cost / Unit</label><input type="number" step="0.01" min="0" class="mp-form-control" name="unit_cost" id="waste-cost" placeholder="auto"></div>
          <div class="mp-form-group"><label>Blend Batch <span class="mp-form-hint">(optional)</span></label>
            <select class="mp-form-control" name="batch_id">
              <option value="">-- None --</option>
              <?php foreach ($batches as $b): ?>
              <option value="<?= $b->id; ?>"><?= htmlspecialchars($b->batch_code); ?> — <?= htmlspecialchars($b->batch_name); ?></option>
              <?php endforeach; ?>
            </select></div>
        </div>
        <div class="mp-form-group" style="margin-top:14px;">
          <label>Reason</label>
          <input type="text" class="mp-form-control" name="reason" placeholder="e.g. Bottle slipped while filling, evaporation after 4-week rest">
        </div>
        <div class="mp-form-actions" style="margin-top:18px;">
          <button type="submit" class="mp-btn-primary"><i class="fa fa-check"></i> Record Loss &amp; Adjust Stock</button>
        </div>
      </form>
    </div>
  </div>

  <div>
    <div class="mp-card-form" style="margin-bottom:20px;">
      <div class="mp-card-head"><h3><i class="fa fa-pie-chart"></i> Cost by Stage</h3></div>
      <div class="mp-card-body" style="padding:0!important;">
        <?php if (!empty($summary['by_stage'])): $max = max(array_map(function($s){ return (float)$s->cost; }, $summary['by_stage'])); ?>
        <table class="mp-static-table">
          <thead><tr><th>Stage</th><th>Events</th><th>Qty Lost</th><th style="min-width:120px;"></th><th class="text-right">Cost</th></tr></thead>
          <tbody>
          <?php foreach ($summary['by_stage'] as $s): $w = $max > 0 ? round($s->cost / $max * 100) : 0; ?>
            <tr>
              <td><span class="label label-<?= Perfume_model::stage_badge($s->stage); ?>"><?= Perfume_model::stage_label($s->stage); ?></span></td>
              <td><?= (int)$s->events; ?></td>
              <td><?= format_qty($s->qty_lost); ?></td>
              <td><div class="waste-bar"><span style="width:<?= $w; ?>%"></span></div></td>
              <td class="text-right"><?= $CI->currency($s->cost); ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php else: ?>
          <p style="padding:18px;color:var(--mp-muted);font-size:13px;margin:0;">No losses yet — this breakdown fills in as you log them.</p>
        <?php endif; ?>
      </div>
    </div>

    <div class="mp-card-form" style="margin-bottom:20px;">
      <div class="mp-card-head"><h3><i class="fa fa-balance-scale"></i> Blend Yield Variance</h3></div>
      <div class="mp-card-body" style="padding:0!important;">
        <?php if (!empty($variance)): ?>
        <table class="mp-static-table">
          <thead><tr><th>Date</th><th>Batch</th><th>Formula</th><th>Planned</th><th>Actual</th><th class="text-right">Variance</th></tr></thead>
          <tbody>
          <?php foreach ($variance as $r): ?>
            <tr>
              <td><small><?= show_date($r->run_date); ?></small></td>
              <td><span class="label label-default"><?= htmlspecialchars($r->batch_code ?: '—'); ?></span></td>
              <td><small><?= htmlspecialchars($r->recipe_name ?: '—'); ?></small></td>
              <td><?= format_qty($r->planned_qty); ?> <?= htmlspecialchars($r->yield_unit ?: ''); ?></td>
              <td><?= $r->actual_yield !== null ? format_qty($r->actual_yield) . ' ' . htmlspecialchars($r->yield_unit ?: '') : '—'; ?></td>
              <td class="text-right">
                <?php if ($r->variance_pct !== null): ?>
                <span class="text-<?= $r->variance_qty < 0 ? 'danger' : 'success'; ?>">
                  <?= ($r->variance_qty > 0 ? '+' : '') . format_qty($r->variance_qty); ?> (<?= ($r->variance_pct > 0 ? '+' : '') . $r->variance_pct; ?>%)
                </span>
                <?php else: ?>—<?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <p style="padding:10px 14px;margin:0;font-size:12px;color:var(--mp-muted);">Negative variance is product lost between blending and bottling — evaporation, transfer residue, filtering hold-up. Persistent losses beyond the formula's waste allowance deserve investigation.</p>
        <?php else: ?>
          <p style="padding:18px;color:var(--mp-muted);font-size:13px;margin:0;">No production runs yet. Once a blend batch completes, planned vs actual yield shows here.</p>
        <?php endif; ?>
      </div>
    </div>

    <div class="mp-card-form" style="margin-bottom:0;">
      <div class="mp-card-head"><h3><i class="fa fa-history"></i> Loss History</h3></div>
      <div class="mp-card-body" style="padding:0!important;">
        <?php if (!empty($wastage)): ?>
        <table class="mp-static-table">
          <thead><tr><th>Date</th><th>Item</th><th>Stage</th><th>Batch</th><th>Qty</th><th>Reason</th><th class="text-right">Cost</th></tr></thead>
          <tbody>
          <?php foreach ($wastage as $w): ?>
            <tr>
              <td><small><?= show_date($w->created_date); ?></small></td>
              <td><?= htmlspecialchars($w->item_name); ?></td>
              <td><span class="label label-<?= Perfume_model::stage_badge($w->stage); ?>"><?= Perfume_model::stage_label($w->stage); ?></span></td>
              <td><span class="label label-default"><?= htmlspecialchars($w->batch_code ?: '—'); ?></span></td>
              <td><?= format_qty($w->qty); ?> <?= htmlspecialchars($w->unit_name ?: ''); ?></td>
              <td><small><?= htmlspecialchars($w->reason ?: '—'); ?></small></td>
              <td class="text-right"><?= $CI->currency($w->total_cost); ?></td>
            </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php else: ?>
          <p style="padding:18px;color:var(--mp-muted);font-size:13px;margin:0;">Nothing logged yet.</p>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
$(function(){
  $('.select2').select2();
  $('#waste-item').on('change', function(){
    var $o = $(this).find('option:selected');
    if (!$('#waste-unit').val()) $('#waste-unit').val($o.data('unit') || '');
    if (!$('#waste-cost').val()) $('#waste-cost').val($o.data('cost') || '');
  });
});
</script>
