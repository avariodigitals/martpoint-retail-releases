<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.oil-hero{background:linear-gradient(135deg,#123c2b,#1f6b45);border-radius:16px;padding:24px;color:#fff;margin-bottom:20px;display:flex;justify-content:space-between;gap:24px;align-items:center}
.oil-hero h2{margin:0 0 6px;font-size:25px;font-weight:800}.oil-hero p{margin:0;opacity:.82;max-width:720px}.oil-hero .mp-qa-btn{background:#fff;color:#185b3d;border:0}
.oil-hero-actions{display:flex;flex-wrap:wrap;gap:10px;align-items:center}.oil-guide-btn{display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border:1px solid rgba(255,255,255,.6);border-radius:10px;color:#fff!important;font-size:13px;font-weight:800;text-decoration:none!important;white-space:nowrap}.oil-guide-btn:hover{background:rgba(255,255,255,.12)}
.oil-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:14px}.oil-kpi{background:var(--mp-surface);border:1px solid var(--mp-border);border-radius:14px;padding:17px;text-decoration:none!important;min-width:0}.oil-kpi strong{display:block;font-size:27px;color:var(--mp-ink);line-height:1.2;overflow:hidden;text-overflow:ellipsis}.oil-kpi span{font-size:12px;color:var(--mp-muted);display:block;margin-top:5px}.oil-kpi small{display:block;color:var(--mp-muted);font-size:11px;margin-top:8px}.oil-kpi.alert strong{color:#b54708}.oil-kpi.money strong{font-size:22px}.oil-period{font-size:12px;color:var(--mp-muted);margin:-5px 0 18px}.oil-alerts{display:flex;gap:10px;flex-wrap:wrap;margin:0 0 20px}.oil-alerts a{padding:9px 12px;border-radius:10px;background:#fff7ed;color:#9a3412;border:1px solid #fed7aa;text-decoration:none;font-size:12px;font-weight:700}
.oil-actions{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.oil-action{display:flex;gap:13px;align-items:flex-start;padding:17px;border:1px solid var(--mp-border);border-radius:13px;background:var(--mp-surface);text-decoration:none!important;color:var(--mp-ink)}.oil-action i{width:38px;height:38px;border-radius:10px;background:#eaf7ef;color:#197548;display:flex;align-items:center;justify-content:center;font-size:17px}.oil-action b{display:block;margin:1px 0 4px}.oil-action small{color:var(--mp-muted);line-height:1.35;display:block}
.oil-insight-runs{display:grid;grid-template-columns:minmax(0,1.1fr) minmax(0,.9fr);gap:16px;align-items:start;margin-bottom:20px}.oil-insight-runs.only-runs{grid-template-columns:1fr}.oil-insight-runs .mp-workspace-intel,.oil-insight-runs .mp-card-form{margin:0;min-width:0}.oil-insight-runs .mp-static-table{width:100%}
@media(max-width:1100px){.oil-kpis{grid-template-columns:repeat(3,minmax(0,1fr))}.oil-insight-runs{grid-template-columns:1fr}}@media(max-width:900px){.oil-kpis{grid-template-columns:repeat(2,1fr)}.oil-actions{grid-template-columns:1fr 1fr}.oil-hero{align-items:flex-start;flex-direction:column}}
</style>

<div class="oil-hero">
  <div>
    <h2>Processing Plant</h2>
    <p>Procurement, raw materials, processing runs, finished oil, quotations and customer invoices in one workspace.</p>
  </div>
  <div class="oil-hero-actions">
    <a href="<?= base_url('docs/product-design/customer-guide/oil-processing-plant-zonat.html'); ?>" target="_blank" rel="noopener" class="oil-guide-btn"><i class="fa fa-book"></i> Open Plant Guide</a>
    <?php if($CI->permissions('purchase_add')): ?><a href="<?= base_url('purchase/add'); ?>" class="mp-qa-btn"><i class="fa fa-truck"></i> Record Material Purchase</a><?php endif; ?>
  </div>
</div>

<div class="oil-kpis">
  <a class="oil-kpi" href="<?= base_url('operations/production_schedule'); ?>"><strong><?= (int)$stats['open_runs']; ?></strong><span>Open processing runs</span><small><?= (int)$stats['today_runs']; ?> scheduled today</small></a>
  <a class="oil-kpi" href="<?= base_url('operations/production_schedule'); ?>"><strong><?= (int)$stats['completed_month']; ?></strong><span>Runs completed this month</span><small>Since <?= date('M 1'); ?></small></a>
  <a class="oil-kpi <?= $stats['low_materials'] ? 'alert' : ''; ?>" href="<?= base_url('items'); ?>"><strong><?= (int)$stats['low_materials']; ?></strong><span>Inputs at/below reorder level</span><small><?= (int)$stats['inputs']; ?> active input items</small></a>
  <a class="oil-kpi" href="<?= base_url('purchase'); ?>"><strong><?= (int)$stats['awaiting_receipt']; ?></strong><span>Purchases awaiting receipt</span><small>Draft, ordered or partial</small></a>
  <a class="oil-kpi" href="<?= base_url('quotation'); ?>"><strong><?= (int)$stats['open_quotes']; ?></strong><span>Active customer quotations</span><small>Not converted or expired</small></a>
  <?php if(!empty($can_sales_view)): ?>
  <a class="oil-kpi money" href="<?= base_url('sales'); ?>"><strong><?= function_exists('store_number_format') ? store_number_format($stats['sales_month']) : number_format($stats['sales_month'], 2); ?></strong><span>Customer invoices this month</span><small>Final invoices, month to date</small></a>
  <a class="oil-kpi money" href="<?= base_url('sales'); ?>"><strong><?= function_exists('store_number_format') ? store_number_format($stats['customer_due']) : number_format($stats['customer_due'], 2); ?></strong><span>Customer balance outstanding</span><small>On this month's final invoices</small></a>
  <?php endif; ?>
  <a class="oil-kpi" href="<?= base_url('items'); ?>"><strong><?= (int)$stats['finished_products']; ?></strong><span>Active saleable products</span><small>Available product catalogue</small></a>
  <?php if(!empty($commercial)): ?>
  <a class="oil-kpi money" href="<?= base_url('sales'); ?>"><strong><?= htmlspecialchars(mp_format_money($commercial['billed_today'])); ?></strong><span>Customer invoices today</span><small><?= (int)$commercial['invoices_today']; ?> final invoices</small></a>
  <a class="oil-kpi money" href="<?= base_url('sales'); ?>"><strong><?= htmlspecialchars(mp_format_money($commercial['collected_today'])); ?></strong><span>Payments received today</span><small>Against final customer invoices</small></a>
  <a class="oil-kpi <?= $commercial['overdue_invoices'] ? 'alert' : ''; ?>" href="<?= base_url('sales'); ?>"><strong><?= (int)$commercial['overdue_invoices']; ?></strong><span>Overdue customer invoices</span><small>Past due date with a balance</small></a>
  <?php endif; ?>
</div>
<div class="oil-period">Plant performance · <?= date('F Y'); ?> · refreshed when this page loads</div>
<?php $this->load->view('comman/license_usage_card'); ?>
<?php if($stats['low_materials']): ?><div class="oil-alerts"><a href="<?= base_url('items'); ?>"><i class="fa fa-exclamation-triangle"></i> <?= (int)$stats['low_materials']; ?> input items need replenishment</a></div><?php endif; ?>

<div class="oil-insight-runs<?= empty($intel) ? ' only-runs' : ''; ?>">
  <?php $this->load->view('comman/workspace_intelligence', array('intel'=>$intel ?? array())); ?>
  <div class="mp-card-form">
    <div class="mp-card-head"><h3>Recent processing runs</h3><a class="mp-qa-btn" href="<?= base_url('operations/production_schedule'); ?>">View all</a></div>
    <div class="mp-card-body" style="padding:0!important">
      <?php if($recent_runs): ?><table class="mp-static-table"><thead><tr><th>Batch</th><th>Name</th><th>Scheduled</th><th>Status</th></tr></thead><tbody>
        <?php foreach($recent_runs as $run): ?><tr><td><?= htmlspecialchars($run->batch_code); ?></td><td><?= htmlspecialchars($run->batch_name); ?></td><td><?= !empty($run->scheduled_date) ? show_date($run->scheduled_date) : '—'; ?></td><td><span class="label label-default"><?= htmlspecialchars(ucwords(str_replace('_',' ',$run->status))); ?></span></td></tr><?php endforeach; ?>
      </tbody></table><?php else: ?><p style="padding:18px;margin:0;color:var(--mp-muted)">No processing runs have been recorded yet. Start by defining materials and a process formula.</p><?php endif; ?>
    </div>
  </div>
</div>

<div class="mp-card-form" style="margin-bottom:20px">
  <div class="mp-card-head"><h3>Daily work</h3></div>
  <div class="mp-card-body"><div class="oil-actions">
    <?php if($CI->permissions('purchase_add')): ?><a class="oil-action" href="<?= base_url('purchase/add'); ?>"><i class="fa fa-truck"></i><span><b>Material purchase</b><small>Order or receive production inputs from a supplier.</small></span></a><?php endif; ?>
    <?php if($CI->permissions('purchase_add')): ?><a class="oil-action" href="<?= base_url('purchase/add?document=supplier_proforma'); ?>"><i class="fa fa-file-text-o"></i><span><b>Supplier proforma</b><small>Capture a supplier's preliminary invoice as a draft purchase.</small></span></a><?php endif; ?>
    <a class="oil-action" href="<?= base_url('operations/production_schedule'); ?>"><i class="fa fa-industry"></i><span><b>Processing runs</b><small>Plan batches and turn recorded inputs into finished stock.</small></span></a>
    <a class="oil-action" href="<?= base_url('operations/recipes'); ?>"><i class="fa fa-list-alt"></i><span><b>Process formulas</b><small>Define the expected inputs used for each finished product.</small></span></a>
    <?php if($CI->permissions('quotation_add')): ?><a class="oil-action" href="<?= base_url('quotation/add'); ?>"><i class="fa fa-file-text-o"></i><span><b>Customer quotation</b><small>Prepare a priced offer for a buyer before invoicing.</small></span></a><?php endif; ?>
    <?php if($CI->permissions('sales_add')): ?><a class="oil-action" href="<?= base_url('sales/add'); ?>"><i class="fa fa-file-text"></i><span><b>Customer invoice</b><small>Invoice bulk or packaged product sales.</small></span></a><?php endif; ?>
    <a class="oil-action" href="<?= base_url('docs/product-design/customer-guide/oil-processing-plant-zonat.html'); ?>" target="_blank" rel="noopener"><i class="fa fa-book"></i><span><b>Plant walkthrough</b><small>Follow a full working day at Zonat Processing LTD, screen by screen.</small></span></a>
    <a class="oil-action" href="<?= base_url('inventory'); ?>"><i class="fa fa-database"></i><span><b>Plant stock</b><small>Review materials and products held at plants and depots.</small></span></a>
  </div></div>
</div>

