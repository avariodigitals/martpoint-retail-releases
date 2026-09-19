<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.perf-flow{display:flex;align-items:stretch;gap:0;margin:20px 0 4px;flex-wrap:wrap}
.perf-step{flex:1;min-width:120px;text-align:center;padding:14px 8px;position:relative}
.perf-step .icon{width:44px;height:44px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:17px;margin-bottom:8px;background:rgba(0,87,255,.08);color:var(--mp-primary)}
.perf-step .name{font-size:12px;font-weight:700;color:var(--mp-ink)}
.perf-step .desc{font-size:11px;color:var(--mp-muted);margin-top:2px;line-height:1.35}
.perf-step:not(:last-child)::after{content:'\f105';font-family:FontAwesome;position:absolute;right:-6px;top:24px;color:var(--mp-border);font-size:18px}
.perf-progress{height:6px;border-radius:3px;background:var(--mp-bg);overflow:hidden;margin-top:8px}
.perf-progress > span{display:block;height:100%;border-radius:3px;background:var(--mp-primary)}
</style>

<div class="mp-page-head">
  <div>
    <h2>Perfume Lab</h2>
    <div class="mp-page-sub">Blending pipeline, maceration &amp; wastage control</div>
  </div>
</div>

<div class="mp-form-grid" style="grid-template-columns: repeat(6, 1fr); gap: 16px; margin: 20px 0;">
  <div class="mp-card-form" style="text-align:center;"><div class="mp-card-body">
    <h1 style="margin:0;color:var(--mp-primary);"><?= (int)$stats['active_batches']; ?></h1>
    <p class="mp-muted">Active Batches</p></div></div>
  <div class="mp-card-form" style="text-align:center;"><div class="mp-card-body">
    <h1 style="margin:0;color:#B45309;"><?= (int)$stats['macerating']; ?></h1>
    <p class="mp-muted">Macerating</p></div></div>
  <div class="mp-card-form" style="text-align:center;"><div class="mp-card-body">
    <h1 style="margin:0;color:var(--mp-success);"><?= (int)$stats['ready_this_week']; ?></h1>
    <p class="mp-muted">Ready ≤ 7 Days</p></div></div>
  <div class="mp-card-form" style="text-align:center;"><div class="mp-card-body">
    <h1 style="margin:0;color:var(--mp-primary);"><?= (int)$stats['formulas']; ?></h1>
    <p class="mp-muted">Formulas</p></div></div>
  <div class="mp-card-form" style="text-align:center;"><div class="mp-card-body">
    <h1 style="margin:0;color:<?= $stats['waste_cost_month'] > 0 ? 'var(--mp-danger)' : 'var(--mp-muted)'; ?>;"><?= $CI->currency($stats['waste_cost_month']); ?></h1>
    <p class="mp-muted">Losses This Month</p></div></div>
  <div class="mp-card-form" style="text-align:center;"><div class="mp-card-body">
    <h1 style="margin:0;color:<?= $stats['low_materials'] > 0 ? 'var(--mp-danger)' : 'var(--mp-muted)'; ?>;"><?= (int)$stats['low_materials']; ?></h1>
    <p class="mp-muted">Low Materials</p></div></div>
</div>

<div class="mp-card-form" style="margin-bottom:20px;">
  <div class="mp-card-head"><h3><i class="fa fa-flask"></i> How a Fragrance Is Made</h3></div>
  <div class="mp-card-body" style="padding-top:6px;">
    <div class="perf-flow">
      <div class="perf-step"><span class="icon"><i class="fa fa-tint"></i></span><div class="name">Raw Materials</div><div class="desc">Oils, alcohol, solvents &amp; packaging kept as not-for-sale stock</div></div>
      <div class="perf-step"><span class="icon"><i class="fa fa-book"></i></span><div class="name">Formula</div><div class="desc">Concentration recipe (Extrait / EDP / EDT) with a loss allowance per ingredient</div></div>
      <div class="perf-step"><span class="icon"><i class="fa fa-flask"></i></span><div class="name">Blend</div><div class="desc">Batch consumes raw materials from stock when it completes</div></div>
      <div class="perf-step"><span class="icon"><i class="fa fa-hourglass-half"></i></span><div class="name">Macerate</div><div class="desc">Compound rests days–weeks; expect a small evaporation loss</div></div>
      <div class="perf-step"><span class="icon"><i class="fa fa-tag"></i></span><div class="name">Bottle &amp; Sell</div><div class="desc">Filter, fill bottles, sell bottles &amp; decants at the POS</div></div>
    </div>
  </div>
</div>

<div class="mp-form-grid" style="grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom:24px;">
  <a href="<?= base_url('operations/production_batch'); ?>" class="mp-card-form" style="text-decoration:none;"><div class="mp-card-body" style="text-align:center;">
    <i class="fa fa-plus-circle" style="font-size:28px;color:var(--mp-primary);display:block;margin-bottom:10px;"></i>
    <h4>New Blend Batch</h4><p class="mp-muted">Schedule a production run from a formula.</p></div></a>
  <a href="<?= base_url('operations/recipes'); ?>" class="mp-card-form" style="text-decoration:none;"><div class="mp-card-body" style="text-align:center;">
    <i class="fa fa-book" style="font-size:28px;color:var(--mp-primary);display:block;margin-bottom:10px;"></i>
    <h4>Formulas</h4><p class="mp-muted">Concentration recipes &amp; ingredient costing.</p></div></a>
  <a href="<?= base_url('perfume/maceration'); ?>" class="mp-card-form" style="text-decoration:none;"><div class="mp-card-body" style="text-align:center;">
    <i class="fa fa-hourglass-half" style="font-size:28px;color:#B45309;display:block;margin-bottom:10px;"></i>
    <h4>Maceration Tracker</h4><p class="mp-muted">Batches resting, ready dates &amp; stage moves.</p></div></a>
  <a href="<?= base_url('perfume/wastage'); ?>" class="mp-card-form" style="text-decoration:none;"><div class="mp-card-body" style="text-align:center;">
    <i class="fa fa-trash-o" style="font-size:28px;color:var(--mp-danger);display:block;margin-bottom:10px;"></i>
    <h4>Wastage &amp; Losses</h4><p class="mp-muted">Log losses and see what they cost.</p></div></a>
</div>

<div class="mp-form-grid" style="grid-template-columns: 1fr 1fr; gap: 20px; align-items:start;">
  <div class="mp-card-form" style="margin-bottom:0;">
    <div class="mp-card-head"><h3><i class="fa fa-hourglass-half"></i> Maceration Watch</h3>
      <a href="<?= base_url('perfume/maceration'); ?>" class="mp-qa-btn" style="font-size:12px;">View all</a></div>
    <div class="mp-card-body" style="padding:0!important;">
      <?php if (!empty($macerating)): ?>
      <table class="mp-static-table">
        <thead><tr><th>Batch</th><th>Formula</th><th>Started</th><th>Ready</th><th>Progress</th></tr></thead>
        <tbody>
        <?php foreach (array_slice($macerating, 0, 6) as $b):
          $pct = ($b->required_days > 0 && $b->days_in !== null) ? min(100, round($b->days_in / $b->required_days * 100)) : null; ?>
          <tr>
            <td><span class="label label-default"><?= htmlspecialchars($b->batch_code); ?></span><br><small><?= htmlspecialchars($b->batch_name); ?></small></td>
            <td><small><?= htmlspecialchars(implode(', ', $b->recipes) ?: '—'); ?></small></td>
            <td><small><?= $b->maceration_started ? show_date($b->maceration_started) : '—'; ?></small></td>
            <td><small><?= $b->ready_date ? show_date($b->ready_date) : '—'; ?><?php if($b->days_left !== null): ?><br><span class="text-<?= $b->days_left <= 0 ? 'success' : 'muted'; ?>" style="font-size:11px;"><?= $b->days_left <= 0 ? 'Ready now' : $b->days_left . 'd left'; ?></span><?php endif; ?></small></td>
            <td style="min-width:90px;"><?php if($pct !== null): ?><div class="perf-progress"><span style="width:<?= $pct; ?>%"></span></div><small class="text-muted"><?= $pct; ?>%</small><?php else: ?><small class="text-muted">—</small><?php endif; ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
        <p style="padding:18px;color:var(--mp-muted);font-size:13px;margin:0;">Nothing is macerating right now. Move a finished blend into <em>Macerating</em> from the <a href="<?= base_url('perfume/maceration'); ?>">tracker</a>.</p>
      <?php endif; ?>
    </div>
  </div>

  <div class="mp-card-form" style="margin-bottom:0;">
    <div class="mp-card-head"><h3><i class="fa fa-trash-o"></i> Recent Losses</h3>
      <a href="<?= base_url('perfume/wastage'); ?>" class="mp-qa-btn" style="font-size:12px;">Log a loss</a></div>
    <div class="mp-card-body" style="padding:0!important;">
      <?php if (!empty($wastage)): ?>
      <table class="mp-static-table">
        <thead><tr><th>Item</th><th>Stage</th><th>Qty</th><th class="text-right">Cost</th></tr></thead>
        <tbody>
        <?php foreach ($wastage as $w): ?>
          <tr>
            <td><?= htmlspecialchars($w->item_name); ?><br><small class="text-muted"><?= show_date($w->created_date); ?><?= $w->batch_code ? ' · ' . htmlspecialchars($w->batch_code) : ''; ?></small></td>
            <td><span class="label label-<?= Perfume_model::stage_badge($w->stage); ?>"><?= Perfume_model::stage_label($w->stage); ?></span></td>
            <td><?= format_qty($w->qty); ?> <?= htmlspecialchars($w->unit_name ?: ''); ?></td>
            <td class="text-right"><?= $CI->currency($w->total_cost); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
        <p style="padding:18px;color:var(--mp-muted);font-size:13px;margin:0;">No losses recorded yet. Every spill, evaporation loss, breakage and tester should be logged — that is how you learn what waste really costs.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
