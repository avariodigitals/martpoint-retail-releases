<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2>Production Reports</h2>
    <div class="mp-page-sub">Output, rejects and job margins — last 90 days of reported production</div>
  </div>
  <form method="get" action="<?= base_url('nylon/reports'); ?>">
    <select name="branch_id" class="form-control" onchange="this.form.submit()">
      <option value="">All <?= htmlspecialchars(mp_label('warehouse','Factories')); ?></option>
      <?php foreach ($warehouses as $w): ?><option value="<?= $w->id; ?>" <?= $warehouse_id==$w->id?'selected':''; ?>><?= htmlspecialchars($w->warehouse_name); ?></option><?php endforeach; ?>
    </select>
  </form>
</div>

<div class="mp-form-grid" style="grid-template-columns:1fr 1fr;gap:20px;align-items:start;margin-bottom:20px;">
  <div class="mp-card-form" style="margin-bottom:0;">
    <div class="mp-card-head"><h3><i class="fa fa-tachometer"></i> Output by Machine</h3></div>
    <div class="mp-card-body" style="padding:0!important;">
      <table class="mp-static-table">
        <thead><tr><th>Machine</th><th>Stage</th><th class="text-right">Good</th><th class="text-right">Rejects</th><th class="text-right">Scrap</th><th class="text-right">Waste</th></tr></thead>
        <tbody>
        <?php if (empty($by_machine)): ?><tr><td colspan="6" style="padding:16px;color:var(--mp-muted);">No reports yet.</td></tr><?php endif; ?>
        <?php foreach ($by_machine as $r): ?>
          <tr><td><?= htmlspecialchars($r->machine_name); ?></td><td><small><?= Nylon_model::stage_label($r->stage_key); ?></small></td>
            <td class="text-right text-success"><?= format_qty($r->good); ?></td><td class="text-right <?= $r->rejects>0?'text-danger':''; ?>"><?= format_qty($r->rejects); ?></td>
            <td class="text-right"><?= format_qty($r->scrap); ?></td><td class="text-right"><?= format_qty($r->waste); ?></td></tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="mp-card-form" style="margin-bottom:0;">
    <div class="mp-card-head"><h3><i class="fa fa-cube"></i> Output by Product</h3></div>
    <div class="mp-card-body" style="padding:0!important;">
      <table class="mp-static-table">
        <thead><tr><th>Product</th><th>Stage</th><th class="text-right">Good</th><th class="text-right">Rejects</th><th class="text-right">Reject %</th></tr></thead>
        <tbody>
        <?php if (empty($by_product)): ?><tr><td colspan="5" style="padding:16px;color:var(--mp-muted);">No reports yet.</td></tr><?php endif; ?>
        <?php foreach ($by_product as $r): $tot = (float)$r->good + (float)$r->rejects; ?>
          <tr><td><?= htmlspecialchars($r->item_name ?: '—'); ?></td><td><small><?= Nylon_model::stage_label($r->stage_key); ?></small></td>
            <td class="text-right text-success"><?= format_qty($r->good); ?></td><td class="text-right <?= $r->rejects>0?'text-danger':''; ?>"><?= format_qty($r->rejects); ?></td>
            <td class="text-right"><?= $tot > 0 ? round($r->rejects/$tot*100,1).'%' : '—'; ?></td></tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php if ($can_costing): ?>
<div class="mp-card-form">
  <div class="mp-card-head"><h3><i class="fa fa-line-chart"></i> Profit by Job</h3></div>
  <div class="mp-card-body" style="padding:0!important;">
    <table class="mp-static-table">
      <thead><tr><th>Job</th><th>Product</th><th>Order</th><th>Status</th><th class="text-right">Material Cost</th><th class="text-right">Other Cost</th><th class="text-right">Actual Total</th><th class="text-right">Est. Total</th><th class="text-right">Revenue</th><th class="text-right">Margin</th><th class="text-right">Yield</th></tr></thead>
      <tbody>
      <?php if (empty($job_rows)): ?><tr><td colspan="11" style="padding:16px;color:var(--mp-muted);">No jobs yet.</td></tr><?php endif; ?>
      <?php foreach ($job_rows as $row): $j = $row['job']; $rp = $row['report']; ?>
        <tr>
          <td><a href="<?= base_url('nylon/job_view/'.$j->id); ?>"><?= htmlspecialchars($j->job_code); ?></a></td>
          <td><small><?= htmlspecialchars($j->item_name); ?></small></td>
          <td><small><?= $j->order_code ? htmlspecialchars($j->order_code) : '<em>stock</em>'; ?></small></td>
          <td><span class="label label-<?= Nylon_model::job_status_badge($j->status); ?>"><?= Nylon_model::job_status_label($j->status); ?></span></td>
          <td class="text-right"><?= $CI->currency($rp['material_cost']); ?></td>
          <td class="text-right"><?= $CI->currency($rp['act_other']); ?></td>
          <td class="text-right"><strong><?= $CI->currency($rp['act_total']); ?></strong></td>
          <td class="text-right"><?= $CI->currency($rp['est_total']); ?></td>
          <td class="text-right"><?= $rp['revenue'] ? $CI->currency($rp['revenue']) : '—'; ?></td>
          <td class="text-right"><strong class="<?= $rp['margin']>=0?'text-success':'text-danger'; ?>"><?= $rp['revenue'] ? $CI->currency($rp['margin']) : '—'; ?></strong></td>
          <td class="text-right"><?= $rp['yield_pct']!==null ? $rp['yield_pct'].'%' : '—'; ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php endif; ?>
