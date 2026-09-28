<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.ny-flow{display:flex;align-items:stretch;gap:0;margin:20px 0 4px;flex-wrap:wrap}
.ny-step{flex:1;min-width:110px;text-align:center;padding:14px 8px;position:relative}
.ny-step .icon{width:44px;height:44px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:17px;margin-bottom:8px;background:rgba(0,87,255,.08);color:var(--mp-primary)}
.ny-step.off{opacity:.35}
.ny-step .name{font-size:12px;font-weight:700;color:var(--mp-ink)}
.ny-step .desc{font-size:11px;color:var(--mp-muted);margin-top:2px;line-height:1.35}
.ny-step:not(:last-child)::after{content:'\f105';font-family:FontAwesome;position:absolute;right:-6px;top:24px;color:var(--mp-border);font-size:18px}
.ny-step:last-child::after{content:none}
</style>

<div class="mp-page-head">
  <div>
    <h2>Nylon Factory</h2>
    <div class="mp-page-sub"><?= htmlspecialchars($mode_label); ?> — production jobs, film stock &amp; margins</div>
  </div>
  <div>
    <form method="get" action="<?= base_url('nylon'); ?>" style="display:flex;gap:8px;align-items:center;">
      <select name="branch_id" class="form-control" onchange="this.form.submit()">
        <option value="">All <?= htmlspecialchars(mp_label('warehouse','Factories')); ?></option>
        <?php foreach ($warehouses as $w): ?>
          <option value="<?= $w->id; ?>" <?= ($warehouse_id == $w->id) ? 'selected' : ''; ?>><?= htmlspecialchars($w->warehouse_name); ?></option>
        <?php endforeach; ?>
      </select>
    </form>
  </div>
</div>

<div class="mp-form-grid" style="grid-template-columns: repeat(4, 1fr); gap: 16px; margin: 20px 0;">
  <a href="<?= base_url('nylon/jobs'); ?>" class="mp-card-form" style="text-align:center;text-decoration:none;"><div class="mp-card-body">
    <h1 style="margin:0;color:var(--mp-primary);"><?= (int)$stats['open_jobs']; ?></h1>
    <p class="mp-muted">Open Jobs</p></div></a>
  <a href="<?= base_url('nylon/jobs'); ?>" class="mp-card-form" style="text-align:center;text-decoration:none;"><div class="mp-card-body">
    <h1 style="margin:0;color:<?= $stats['due_soon'] > 0 ? 'var(--mp-danger)' : 'var(--mp-muted)'; ?>;"><?= (int)$stats['due_soon']; ?></h1>
    <p class="mp-muted">Due ≤ 7 Days</p></div></a>
  <div class="mp-card-form" style="text-align:center;"><div class="mp-card-body">
    <h1 style="margin:0;color:<?= $stats['pending_qc'] > 0 ? '#B45309' : 'var(--mp-muted)'; ?>;"><?= (int)$stats['pending_qc']; ?></h1>
    <p class="mp-muted">QC Awaiting Approval</p></div></div>
  <div class="mp-card-form" style="text-align:center;"><div class="mp-card-body">
    <h1 style="margin:0;color:var(--mp-primary);"><?= count($balances); ?></h1>
    <p class="mp-muted">Orders With Balance</p></div></div>
</div>

<div class="mp-card-form" style="margin-bottom:20px;">
  <div class="mp-card-head"><h3><i class="fa fa-industry"></i> Factory Pipeline</h3></div>
  <div class="mp-card-body" style="padding-top:6px;">
    <div class="ny-flow">
      <div class="ny-step"><span class="icon"><i class="fa fa-cubes"></i></span><div class="name">Material</div><div class="desc">Resin &amp; masterbatch allocated, or purchased rolls received</div></div>
      <div class="ny-step <?= $mode['extrusion'] ? '' : 'off'; ?>"><span class="icon"><i class="fa fa-industry"></i></span><div class="name">Extrusion</div><div class="desc"><?= $mode['extrusion'] ? 'Blown film wound into rolls' : 'Off — this factory buys film'; ?></div></div>
      <div class="ny-step"><span class="icon"><i class="fa fa-print"></i></span><div class="name">Printing</div><div class="desc">Optional — needs an approved artwork on the order</div></div>
      <div class="ny-step <?= $mode['conversion'] ? '' : 'off'; ?>"><span class="icon"><i class="fa fa-cut"></i></span><div class="name">Cut &amp; Seal</div><div class="desc"><?= $mode['conversion'] ? 'Film converted to bags / packs' : 'Off — rolls sold as-is'; ?></div></div>
      <div class="ny-step"><span class="icon"><i class="fa fa-archive"></i></span><div class="name">Packing</div><div class="desc">Bundles &amp; cartons, counted per product conversion</div></div>
      <div class="ny-step"><span class="icon"><i class="fa fa-check-circle"></i></span><div class="name">QC</div><div class="desc">Supervisor sign-off releases saleable stock</div></div>
    </div>
  </div>
</div>

<div class="mp-form-grid" style="grid-template-columns: 1fr 1fr; gap: 20px; align-items:start;">
  <div class="mp-card-form" style="margin-bottom:0;">
    <div class="mp-card-head"><h3><i class="fa fa-tasks"></i> Open Jobs &amp; Due Dates</h3>
      <a href="<?= base_url('nylon/jobs'); ?>" class="mp-qa-btn" style="font-size:12px;">All jobs</a></div>
    <div class="mp-card-body" style="padding:0!important;">
      <?php if (!empty($open_jobs)): ?>
      <table class="mp-static-table">
        <thead><tr><th>Job</th><th>Product</th><th>Customer / Order</th><th>Due</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach ($open_jobs as $j): $late = $j->due_date && $j->due_date < date('Y-m-d'); ?>
          <tr>
            <td><a href="<?= base_url('nylon/job_view/'.$j->id); ?>"><span class="label label-default"><?= htmlspecialchars($j->job_code); ?></span></a></td>
            <td><small><?= htmlspecialchars($j->item_name); ?></small></td>
            <td><small><?= $j->customer_name ? htmlspecialchars($j->customer_name).'<br>'.$j->order_code : '<em>Stock run</em>'; ?></small></td>
            <td><small class="<?= $late ? 'text-danger' : ''; ?>"><?= $j->due_date ? show_date($j->due_date) : '—'; ?><?= $late ? ' (overdue)' : ''; ?></small></td>
            <td><span class="label label-<?= Nylon_model::job_status_badge($j->status); ?>"><?= Nylon_model::job_status_label($j->status); ?></span></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
        <p style="padding:18px;color:var(--mp-muted);font-size:13px;margin:0;">No open jobs. Create one from a <a href="<?= base_url('nylon/orders'); ?>">job order</a> or a stock replenishment.</p>
      <?php endif; ?>
    </div>
  </div>

  <div class="mp-card-form" style="margin-bottom:0;">
    <div class="mp-card-head"><h3><i class="fa fa-cubes"></i> Raw Material &amp; Film Roll Availability</h3>
      <a href="<?= base_url('nylon/products'); ?>" class="mp-qa-btn" style="font-size:12px;">Manage</a></div>
    <div class="mp-card-body" style="padding:0!important;">
      <?php if (!empty($materials)): ?>
      <table class="mp-static-table">
        <thead><tr><th>Material</th><th>Class</th><th class="text-right">Available</th></tr></thead>
        <tbody>
        <?php foreach ($materials as $m): ?>
          <tr>
            <td><?= htmlspecialchars($m->item_name); ?></td>
            <td><span class="label label-<?= $m->item_class === 'film_roll' ? 'info' : 'default'; ?>"><?= $m->item_class === 'film_roll' ? mp_label('film_roll','Film Roll') : 'Raw Material'; ?></span></td>
            <td class="text-right"><strong><?= format_qty($m->available); ?></strong> <small><?= htmlspecialchars($m->unit_name ?: ''); ?></small><?= $m->kg_per_roll ? '<br><small class="text-muted">'.format_qty($m->kg_per_roll).' kg/roll</small>' : ''; ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
        <p style="padding:18px;color:var(--mp-muted);font-size:13px;margin:0;">No nylon materials defined yet. Add resin, film rolls and consumables under <a href="<?= base_url('nylon/products'); ?>">Materials &amp; Products</a>.</p>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="mp-form-grid" style="grid-template-columns: 1fr 1fr; gap: 20px; align-items:start; margin-top:20px;">
  <div class="mp-card-form" style="margin-bottom:0;">
    <div class="mp-card-head"><h3><i class="fa fa-tachometer"></i> Output by Machine (30 days)</h3>
      <a href="<?= base_url('nylon/reports'); ?>" class="mp-qa-btn" style="font-size:12px;">Reports</a></div>
    <div class="mp-card-body" style="padding:0!important;">
      <?php if (!empty($machines)): ?>
      <table class="mp-static-table">
        <thead><tr><th>Machine</th><th>Stage</th><th class="text-right">Good</th><th class="text-right">Rejects</th><th class="text-right">Scrap</th><th class="text-right">Waste</th></tr></thead>
        <tbody>
        <?php foreach ($machines as $r): ?>
          <tr>
            <td><?= htmlspecialchars($r->machine_name); ?></td>
            <td><small><?= Nylon_model::stage_label($r->stage_key); ?></small></td>
            <td class="text-right text-success"><?= format_qty($r->good); ?></td>
            <td class="text-right <?= $r->rejects > 0 ? 'text-danger' : ''; ?>"><?= format_qty($r->rejects); ?></td>
            <td class="text-right"><?= format_qty($r->scrap); ?></td>
            <td class="text-right"><?= format_qty($r->waste); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
        <p style="padding:18px;color:var(--mp-muted);font-size:13px;margin:0;">No output reported in the last 30 days.</p>
      <?php endif; ?>
    </div>
  </div>

  <div class="mp-card-form" style="margin-bottom:0;">
    <div class="mp-card-head"><h3><i class="fa fa-money"></i> Outstanding Customer Balances</h3>
      <a href="<?= base_url('nylon/orders'); ?>" class="mp-qa-btn" style="font-size:12px;">Orders</a></div>
    <div class="mp-card-body" style="padding:0!important;">
      <?php if (!empty($balances)): ?>
      <table class="mp-static-table">
        <thead><tr><th>Order</th><th>Customer</th><th class="text-right">Order Value</th><th class="text-right">Balance Due</th></tr></thead>
        <tbody>
        <?php foreach ($balances as $b): ?>
          <tr>
            <td><a href="<?= base_url('nylon/order_view/'.$b->id); ?>"><?= htmlspecialchars($b->order_code); ?></a></td>
            <td><?= htmlspecialchars($b->customer_name); ?></td>
            <td class="text-right"><?= $CI->currency($b->total_amount); ?></td>
            <td class="text-right text-danger"><strong><?= $CI->currency($b->balance_due); ?></strong></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?>
        <p style="padding:18px;color:var(--mp-muted);font-size:13px;margin:0;">No outstanding balances on nylon orders.</p>
      <?php endif; ?>
    </div>
  </div>
</div>
