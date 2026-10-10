<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.oil-hero{background:linear-gradient(135deg,#123c2b,#1f6b45);border-radius:16px;padding:24px;color:#fff;margin-bottom:20px;display:flex;justify-content:space-between;gap:24px;align-items:center}
.oil-hero h2{margin:0 0 6px;font-size:25px;font-weight:800}.oil-hero p{margin:0;opacity:.82;max-width:720px}.oil-hero .mp-qa-btn{background:#fff;color:#185b3d;border:0}
.oil-kpis{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:14px}.oil-kpi{background:var(--mp-surface);border:1px solid var(--mp-border);border-radius:14px;padding:17px;text-decoration:none!important;min-width:0}.oil-kpi strong{display:block;font-size:27px;color:var(--mp-ink);line-height:1.2;overflow:hidden;text-overflow:ellipsis}.oil-kpi span{font-size:12px;color:var(--mp-muted);display:block;margin-top:5px}.oil-kpi small{display:block;color:var(--mp-muted);font-size:11px;margin-top:8px}.oil-kpi.alert strong{color:#b54708}.oil-kpi.money strong{font-size:22px}.oil-period{font-size:12px;color:var(--mp-muted);margin:-5px 0 18px}.oil-alerts{display:flex;gap:10px;flex-wrap:wrap;margin:0 0 20px}.oil-alerts a{padding:9px 12px;border-radius:10px;background:#fff7ed;color:#9a3412;border:1px solid #fed7aa;text-decoration:none;font-size:12px;font-weight:700}
.oil-actions{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}.oil-action{display:flex;gap:13px;align-items:flex-start;padding:17px;border:1px solid var(--mp-border);border-radius:13px;background:var(--mp-surface);text-decoration:none!important;color:var(--mp-ink)}.oil-action i{width:38px;height:38px;border-radius:10px;background:#eaf7ef;color:#197548;display:flex;align-items:center;justify-content:center;font-size:17px}.oil-action b{display:block;margin:1px 0 4px}.oil-action small{color:var(--mp-muted);line-height:1.35;display:block}
.oil-flow{display:flex;gap:0;align-items:stretch;overflow-x:auto;padding:8px 0}.oil-step{min-width:145px;flex:1;text-align:center;padding:12px 10px;position:relative}.oil-step:not(:last-child):after{content:'\f105';font-family:FontAwesome;position:absolute;right:-4px;top:24px;color:var(--mp-border);font-size:18px}.oil-step i{display:flex;width:42px;height:42px;border-radius:50%;background:#eaf7ef;color:#197548;align-items:center;justify-content:center;margin:0 auto 8px}.oil-step b{display:block;font-size:12px}.oil-step small{color:var(--mp-muted);font-size:10px;line-height:1.3;display:block;margin-top:3px}
@media(max-width:1100px){.oil-kpis{grid-template-columns:repeat(3,minmax(0,1fr))}}@media(max-width:900px){.oil-kpis{grid-template-columns:repeat(2,1fr)}.oil-actions{grid-template-columns:1fr 1fr}.oil-hero{align-items:flex-start;flex-direction:column}}
</style>

<div class="oil-hero">
  <div>
    <h2>Processing Plant</h2>
    <p>Procurement, raw materials, processing runs, finished oil, quotations and customer invoices in one workspace.</p>
  </div>
  <?php if($CI->permissions('purchase_add')): ?><a href="<?= base_url('purchase/add'); ?>" class="mp-qa-btn"><i class="fa fa-truck"></i> Record Material Purchase</a><?php endif; ?>
</div>

<div class="oil-kpis">
  <a class="oil-kpi" href="<?= base_url('operations/production_schedule'); ?>"><strong><?= (int)$stats['open_runs']; ?></strong><span>Open processing runs</span><small><?= (int)$stats['today_runs']; ?> scheduled today</small></a>
  <a class="oil-kpi" href="<?= base_url('operations/production_schedule'); ?>"><strong><?= (int)$stats['completed_month']; ?></strong><span>Runs completed this month</span><small>Since <?= date('M 1'); ?></small></a>
  <a class="oil-kpi <?= $stats['low_materials'] ? 'alert' : ''; ?>" href="<?= base_url('items'); ?>"><strong><?= (int)$stats['low_materials']; ?></strong><span>Inputs at/below reorder level</span><small><?= (int)$stats['inputs']; ?> active input items</small></a>
  <a class="oil-kpi" href="<?= base_url('purchase'); ?>"><strong><?= (int)$stats['awaiting_receipt']; ?></strong><span>Purchases awaiting receipt</span><small>Draft, ordered or partial</small></a>
  <a class="oil-kpi" href="<?= base_url('quotation'); ?>"><strong><?= (int)$stats['open_quotes']; ?></strong><span>Active customer quotations</span><small>Not converted or expired</small></a>
  <a class="oil-kpi money" href="<?= base_url('sales'); ?>"><strong><?= function_exists('store_number_format') ? store_number_format($stats['sales_month']) : number_format($stats['sales_month'], 2); ?></strong><span>Customer invoices this month</span><small>Final invoices, month to date</small></a>
  <a class="oil-kpi money" href="<?= base_url('sales'); ?>"><strong><?= function_exists('store_number_format') ? store_number_format($stats['customer_due']) : number_format($stats['customer_due'], 2); ?></strong><span>Customer balance outstanding</span><small>On this month's final invoices</small></a>
  <a class="oil-kpi" href="<?= base_url('items'); ?>"><strong><?= (int)$stats['finished_products']; ?></strong><span>Active saleable products</span><small>Available product catalogue</small></a>
</div>
<div class="oil-period">Plant performance · <?= date('F Y'); ?> · refreshed when this page loads</div>
<?php if($stats['low_materials'] || $stats['awaiting_receipt']): ?><div class="oil-alerts"><?php if($stats['low_materials']): ?><a href="<?= base_url('items'); ?>"><i class="fa fa-exclamation-triangle"></i> <?= (int)$stats['low_materials']; ?> input items need replenishment</a><?php endif; ?><?php if($stats['awaiting_receipt']): ?><a href="<?= base_url('purchase'); ?>"><i class="fa fa-truck"></i> <?= (int)$stats['awaiting_receipt']; ?> supplier purchases need receiving</a><?php endif; ?></div><?php endif; ?>

<div class="mp-card-form" style="margin-bottom:20px">
  <div class="mp-card-head"><h3><i class="fa fa-industry"></i> Plant workflow</h3></div>
  <div class="mp-card-body">
    <div class="oil-flow">
      <div class="oil-step"><i class="fa fa-shopping-basket"></i><b>Procure</b><small>Seed, fruit, chemicals, packaging and spares</small></div>
      <div class="oil-step"><i class="fa fa-cubes"></i><b>Receive &amp; Store</b><small>Record quantities, batches and plant location</small></div>
      <div class="oil-step"><i class="fa fa-cogs"></i><b>Process</b><small>Create and complete a processing run</small></div>
      <div class="oil-step"><i class="fa fa-flask"></i><b>Quality Check</b><small>Review the run before finished stock is released</small></div>
      <div class="oil-step"><i class="fa fa-tint"></i><b>Finished Stock</b><small>Bulk oil, packaged oil and recorded by-products</small></div>
      <div class="oil-step"><i class="fa fa-file-text-o"></i><b>Quote &amp; Invoice</b><small>Price buyer orders and issue invoices</small></div>
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

<div class="mp-card-form">
  <div class="mp-card-head"><h3>Recent processing runs</h3><a class="mp-qa-btn" href="<?= base_url('operations/production_schedule'); ?>">View all</a></div>
  <div class="mp-card-body" style="padding:0!important">
    <?php if($recent_runs): ?><table class="mp-static-table"><thead><tr><th>Batch</th><th>Name</th><th>Scheduled</th><th>Status</th></tr></thead><tbody>
      <?php foreach($recent_runs as $run): ?><tr><td><?= htmlspecialchars($run->batch_code); ?></td><td><?= htmlspecialchars($run->batch_name); ?></td><td><?= !empty($run->scheduled_date) ? show_date($run->scheduled_date) : '—'; ?></td><td><span class="label label-default"><?= htmlspecialchars(ucwords(str_replace('_',' ',$run->status))); ?></span></td></tr><?php endforeach; ?>
    </tbody></table><?php else: ?><p style="padding:18px;margin:0;color:var(--mp-muted)">No processing runs have been recorded yet. Start by defining materials and a process formula.</p><?php endif; ?>
  </div>
</div>
