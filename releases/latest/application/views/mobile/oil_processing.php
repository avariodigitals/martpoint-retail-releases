<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?> — Processing Plant</title>
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root{--green:#176b45;--ink:#172033;--muted:#667085;--line:#e4e9ee;--bg:#f3f6f4;--safe:env(safe-area-inset-bottom,0px)}
    *{box-sizing:border-box}body{margin:0;background:var(--bg);color:var(--ink);font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif}
    #app{max-width:520px;margin:auto;min-height:100vh;background:#fff}.screen{padding:16px 16px 112px}
    .topbar{display:flex;align-items:center;gap:10px;margin:2px 0 8px;min-width:0}.topbar-titles{flex:1 1 auto;min-width:0}.store-name{font-size:11px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--muted);margin-bottom:4px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.topbar h1{font-size:23px;line-height:1.2;margin:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
    .plant-refresh{width:38px;height:38px;flex:0 0 38px;border:1px solid var(--line);border-radius:12px;background:#fff;color:var(--green);font-size:15px;display:flex;align-items:center;justify-content:center;cursor:pointer}.plant-refresh:active{background:#eaf5ee}.plant-refresh[disabled]{opacity:.6}
    .header-status{display:flex;align-items:center;gap:7px;margin:0 0 14px;min-width:0}.status-chip{display:inline-flex;align-items:center;gap:6px;min-height:28px;padding:5px 9px;border-radius:999px;background:#f1f5f9;color:#475569;font-size:11px;font-weight:700;white-space:nowrap}.status-chip.online{background:#eaf7ef;color:#176b45}.status-chip.offline{background:#fff1f0;color:#b42318}.status-dot{width:7px;height:7px;border-radius:50%;background:#16a34a}.offline .status-dot{background:#dc2626}.status-chip.subscription{background:#eff6ff;color:#1d4ed8}
    .hero{background:linear-gradient(135deg,#123c2b,#24764d);color:#fff;border-radius:16px;padding:18px;margin-bottom:14px}.hero p{margin:6px 0 0;line-height:1.45;font-size:13px;color:#e2f1e8}.run-count{font-size:30px;font-weight:800}.run-label{font-size:12px;color:#d8eee0}
    .hero-guide{display:inline-flex;align-items:center;gap:7px;margin-top:13px;padding:9px 12px;border:1px solid rgba(255,255,255,.7);border-radius:10px;color:#fff;text-decoration:none;font-size:12px;font-weight:800}
    .dash-period{font-size:11px;color:var(--muted);margin:0 2px 10px}.kpi-grid{display:grid;grid-template-columns:1fr 1fr;gap:9px}.kpi{min-height:92px;border:1px solid var(--line);border-radius:13px;padding:12px;background:#fff;text-decoration:none;color:var(--ink);display:flex;flex-direction:column;justify-content:space-between}.kpi strong{font-size:23px;line-height:1.2;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.kpi.money strong{font-size:17px}.kpi span{font-size:11px;line-height:1.3;color:var(--muted)}.kpi.alert strong{color:#b54708}.attention{display:flex;gap:7px;overflow:auto;margin-top:9px}.attention a{flex:0 0 auto;background:#fff7ed;color:#9a3412;border:1px solid #fed7aa;border-radius:9px;padding:8px 10px;text-decoration:none;font-size:11px;font-weight:700}.run-list{border:1px solid var(--line);border-radius:13px;overflow:hidden}.run-row{padding:11px 12px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;gap:10px;font-size:12px}.run-row:last-child{border-bottom:0}.run-row small{display:block;color:var(--muted);margin-top:3px}.run-status{align-self:center;color:var(--green);font-weight:700;font-size:11px;text-align:right}
    .section-title{font-size:14px;font-weight:800;margin:20px 0 10px}.grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}.tile{min-height:112px;border:1px solid var(--line);border-radius:14px;padding:14px 12px;text-decoration:none;color:var(--ink);display:flex;flex-direction:column;justify-content:space-between;background:#fff}.tile i{width:34px;height:34px;border-radius:10px;background:#eaf5ee;color:var(--green);display:flex;align-items:center;justify-content:center;font-size:16px}.tile b{font-size:13px;line-height:1.25}.tile span{font-size:11px;color:var(--muted);margin-top:4px;line-height:1.3}.tile.primary{background:#f0f8f3;border-color:#cce6d5}
    .bottom-nav{padding-bottom:calc(8px + var(--safe))}@media(max-width:380px){.topbar{gap:6px}.plant-refresh{width:34px;height:34px;flex-basis:34px}.header-status{gap:5px}.status-chip{font-size:10px;padding:5px 7px}}@media(min-width:600px){#app{max-width:100%}.screen{max-width:900px;margin:auto;padding-top:24px}.grid{grid-template-columns:repeat(3,minmax(0,1fr))}}
  </style>
</head>
<body>
  <div id="app"><main class="screen">
    <header class="topbar">
      <div class="topbar-titles"><div class="store-name"><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?></div><h1>Processing Plant</h1></div>
      <button type="button" class="plant-refresh" aria-label="Refresh plant information" title="Refresh plant information" onclick="this.disabled=true;window.location.reload();"><i class="fa fa-refresh"></i></button>
    </header>
    <div class="header-status" aria-label="Connection and subscription status">
      <span class="status-chip online" id="plantConnection"><i class="status-dot"></i><span>Online</span></span>
      <?php if(is_store_admin()): ?><span class="status-chip" title="Store Admin accounts are exempt from attendance"><i class="fa fa-clock-o"></i>Clock exempt</span><?php endif; ?>
      <?php if(is_array($plant_subscription) && ($plant_subscription['status'] ?? 'NOT_ACTIVATED') !== 'NOT_ACTIVATED'):
        $plantDays = (int)($plant_subscription['days_left'] ?? 0);
        $plantDaysLabel = $plantDays <= 0 ? 'Expired' : $plantDays.' Days';
      ?><span class="status-chip subscription"><i class="fa fa-calendar-check-o"></i><?= htmlspecialchars($plantDaysLabel); ?></span><?php endif; ?>
    </div>
    <section class="hero"><div class="run-count"><?= (int)$open_runs; ?></div><div class="run-label">Open processing runs · <?= (int)$plant_stats['today_runs']; ?> scheduled today</div><p>Plant operations and commercial performance at a glance.</p><a class="hero-guide" href="<?= base_url('docs/product-design/customer-guide/oil-processing-plant-zonat.html'); ?>" target="_blank" rel="noopener"><i class="fa fa-book"></i> Open Plant Guide</a></section>
    <div class="section-title" style="margin-top:0">Plant performance</div>
    <div class="dash-period"><?= date('F Y'); ?> · updates when refreshed</div>
    <div class="kpi-grid">
      <a class="kpi" href="<?= base_url('mobile/production'); ?>"><strong><?= (int)$plant_stats['completed_month']; ?></strong><span>Runs completed this month</span></a>
      <a class="kpi <?= $plant_stats['low_materials'] ? 'alert' : ''; ?>" href="<?= base_url('mobile/stock'); ?>"><strong><?= (int)$plant_stats['low_materials']; ?></strong><span>Inputs at/below reorder level</span></a>
      <a class="kpi" href="<?= base_url('mobile/purchase'); ?>"><strong><?= (int)$plant_stats['awaiting_receipt']; ?></strong><span>Purchases awaiting receipt</span></a>
      <a class="kpi" href="<?= base_url('mobile/quotations'); ?>"><strong><?= (int)$plant_stats['open_quotes']; ?></strong><span>Active customer quotations</span></a>
      <?php if(!empty($can_sales_view)): ?>
      <a class="kpi money" href="<?= base_url('mobile/sale'); ?>"><strong><?= function_exists('store_number_format') ? store_number_format($plant_stats['sales_month']) : number_format($plant_stats['sales_month'], 2); ?></strong><span>Customer invoices this month</span></a>
      <a class="kpi money" href="<?= base_url('mobile/sale'); ?>"><strong><?= function_exists('store_number_format') ? store_number_format($plant_stats['customer_due']) : number_format($plant_stats['customer_due'], 2); ?></strong><span>Outstanding customer balance</span></a>
      <?php endif; ?>
      <?php if(!empty($commercial)): ?>
      <a class="kpi money" href="<?= base_url('mobile/sale'); ?>"><strong><?= htmlspecialchars(mp_format_money($commercial['billed_today'])); ?></strong><span>Customer invoices today · <?= (int)$commercial['invoices_today']; ?> final</span></a>
      <a class="kpi money" href="<?= base_url('mobile/sale'); ?>"><strong><?= htmlspecialchars(mp_format_money($commercial['collected_today'])); ?></strong><span>Payments received today</span></a>
      <a class="kpi<?= $commercial['overdue_invoices'] ? ' alert' : ''; ?>" style="grid-column:1/-1" href="<?= base_url('mobile/sale'); ?>"><strong><?= (int)$commercial['overdue_invoices']; ?></strong><span>Overdue customer invoices</span></a>
      <?php endif; ?>
    </div>
<?php if($plant_stats['low_materials']): ?><div class="attention"><a href="<?= base_url('mobile/stock'); ?>"><i class="fa fa-exclamation-triangle"></i> <?= (int)$plant_stats['low_materials']; ?> inputs need replenishment</a></div><?php endif; ?>
    <?php if(!empty($recent_plant_runs)): ?><div class="section-title">Recent processing runs</div><div class="run-list"><?php foreach($recent_plant_runs as $run): ?><div class="run-row"><div><b><?= htmlspecialchars($run->batch_code); ?></b><small><?= htmlspecialchars($run->batch_name); ?> · <?= htmlspecialchars($run->scheduled_date); ?></small></div><div class="run-status"><?= htmlspecialchars(ucwords(str_replace('_', ' ', $run->status))); ?></div></div><?php endforeach; ?></div><?php endif; ?>
    <?php $this->load->view('comman/license_usage_card', array('license_usage_mobile'=>true)); ?>
    <?php $this->load->view('comman/workspace_intelligence', array('intel'=>$intel ?? array())); ?>
    <div class="section-title">Daily work</div>
    <div class="grid">
      <?php if($can_purchase): ?><a class="tile primary" href="<?= base_url('mobile/purchase_form'); ?>"><i class="fa fa-truck"></i><div><b>New material purchase</b><span>Record a supplier purchase</span></div></a><?php endif; ?>
      <?php if($can_process): ?><a class="tile primary" href="<?= base_url('mobile/production'); ?>"><i class="fa fa-industry"></i><div><b>Processing runs</b><span>Schedule and track batches</span></div></a><?php endif; ?>
      <?php if($can_quote): ?><a class="tile primary" href="<?= base_url('mobile/quotation_form'); ?>"><i class="fa fa-file-text-o"></i><div><b>New customer quotation</b><span>Prepare a buyer price offer</span></div></a><?php endif; ?>
      <?php if($can_quote_view): ?><a class="tile" href="<?= base_url('mobile/quotations'); ?>"><i class="fa fa-list-alt"></i><div><b>Customer quotations</b><span>Review, print or email quotes</span></div></a><?php endif; ?>
      <?php if($can_sell): ?><a class="tile" href="<?= base_url('mobile/sale'); ?>"><i class="fa fa-file-text"></i><div><b>Customer invoice</b><span>Invoice a buyer order</span></div></a><?php endif; ?>
      <a class="tile" href="<?= base_url('mobile/stock'); ?>"><i class="fa fa-cubes"></i><div><b>Plant stock</b><span>Materials and finished products</span></div></a>
      <a class="tile" href="<?= base_url('mobile/suppliers'); ?>"><i class="fa fa-address-book-o"></i><div><b>Suppliers</b><span>Supplier contacts and accounts</span></div></a>
      <a class="tile" href="<?= base_url('docs/product-design/customer-guide/oil-processing-plant-zonat.html'); ?>" target="_blank" rel="noopener"><i class="fa fa-book"></i><div><b>Plant walkthrough</b><span>A day at Zonat Processing LTD</span></div></a>
      <?php if(permissions('tax_view')): ?><a class="tile" href="<?= base_url('oil_processing/tax_rates'); ?>"><i class="fa fa-percent"></i><div><b>Tax rates</b><span>Rates used on quotations</span></div></a><?php endif; ?>
      <?php if(is_store_admin() || permissions('store_edit')): ?><a class="tile" href="<?= base_url('oil_processing/document_style'); ?>"><i class="fa fa-picture-o"></i><div><b>Document banner</b><span>Quotation and invoice header</span></div></a><?php endif; ?>
    </div>
  </main></div>
  <?php $this->load->view('mobile/bottom_nav', ['active' => 'home']); ?>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
  <script>
    (function(){
      var chip = document.getElementById('plantConnection');
      function updateConnection(){
        if(!chip) return;
        var online = navigator.onLine;
        chip.classList.toggle('online', online);
        chip.classList.toggle('offline', !online);
        chip.querySelector('span:last-child').textContent = online ? 'Online' : 'Offline';
      }
      window.addEventListener('online', updateConnection);
      window.addEventListener('offline', updateConnection);
      updateConnection();
    })();
  </script>
</body>
</html>
