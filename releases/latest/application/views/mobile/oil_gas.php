<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='utf-8'>
  <meta http-equiv='Cache-Control' content='no-cache, no-store, must-revalidate'>
  <meta http-equiv='Pragma' content='no-cache'>
  <meta http-equiv='Expires' content='0'>
  <meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover'>
  <title><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?> — Oil &amp; Gas</title>
  <link rel='preconnect' href='https://fonts.googleapis.com'>
  <link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap' rel='stylesheet'>
  <link rel='stylesheet' href='<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css'>
  <style>
    :root { --mp-primary:#0057FF; --mp-bg:#F1F5F9; --mp-surface:#FFFFFF; --mp-text:#0F172A; --mp-muted:#64748B; --mp-border:#E2E8F0; --mp-success:#10B981; --mp-danger:#EF4444; --mp-warning:#F59E0B; --mp-ink:#1E293B; }
    * { box-sizing: border-box; }
    html, body { margin:0; padding:0; font-family:Inter,-apple-system,BlinkMacSystemFont,sans-serif; background:var(--mp-bg); color:var(--mp-text); height:100%; overscroll-behavior:none; -webkit-tap-highlight-color:transparent; }
    #app { max-width:430px; margin:0 auto; background:var(--mp-surface); min-height:100vh; }
    .screen { padding:12px 12px 110px; }
    .topbar { margin-bottom:14px; padding-top:8px; }
    .topbar .store-name { font-size:11px; color:var(--mp-muted); font-weight:600; text-transform:uppercase; letter-spacing:.4px; margin-bottom:2px; }
    .topbar h1 { font-size:21px; font-weight:700; margin:0; }
    .topbar p { font-size:12px; color:var(--mp-muted); margin:6px 0 0; line-height:1.45; }
    .kpis { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:14px; }
    .kpi { background:#fff; border:1px solid var(--mp-border); border-radius:14px; padding:14px; text-decoration:none; color:var(--mp-text); min-width:0; }
    .kpi strong { display:block; font-size:22px; font-weight:700; line-height:1.2; overflow:hidden; text-overflow:ellipsis; }
    .kpi span { display:block; font-size:11px; color:var(--mp-muted); margin-top:4px; line-height:1.35; }
    .kpi.watch strong { color:var(--mp-warning); }
    .kpi.alert strong { color:var(--mp-danger); }
    .card { background:#fff; border-radius:14px; padding:14px; margin-bottom:12px; border:1px solid var(--mp-border); }
    .section-title { font-size:15px; font-weight:700; margin:0 0 10px; }
    .row { padding:12px 0; border-top:1px solid var(--mp-border); text-decoration:none; color:var(--mp-text); display:block; }
    .row:first-of-type { border-top:none; padding-top:0; }
    .row .name { font-weight:600; font-size:14px; }
    .row .meta { font-size:12px; color:var(--mp-muted); margin-top:4px; line-height:1.45; }
    .row .flag { display:inline-block; font-size:11px; font-weight:700; padding:2px 8px; border-radius:999px; margin-top:6px; }
    .flag.transit { background:#FFFBEB; color:#92400E; }
    .flag.held { background:#FEF2F2; color:#991B1B; }
    .empty { text-align:center; padding:22px 12px; color:var(--mp-muted); font-size:13px; }
    .empty i { display:block; font-size:32px; color:var(--mp-border); margin-bottom:8px; }
    .actions { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
    .action { display:flex; align-items:center; gap:10px; background:#fff; border:1px solid var(--mp-border); border-radius:12px; padding:12px; text-decoration:none; color:var(--mp-ink); font-size:13px; font-weight:600; }
    .action i { width:30px; height:30px; border-radius:8px; background:#EAF2FB; color:#1F4E79; display:flex; align-items:center; justify-content:center; }
    @media (min-width:600px){ #app{max-width:100%;margin:0} .screen{padding:16px 16px 120px} .kpis{grid-template-columns:repeat(4,1fr)} .actions{grid-template-columns:repeat(3,1fr)} }
    @media (min-width:1024px){ .screen{padding:24px 48px 140px} }
  </style>
</head>
<body>
  <div id='app'>
    <section class='screen'>
      <div class='topbar'>
        <div class='store-name'><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?></div>
        <h1>Oil &amp; Gas</h1>
        <p><?= (int)$summary['locations']; ?> <?= htmlspecialchars(strtolower($site_label)); ?><?= $summary['locations'] === 1 ? '' : 's'; ?> in view · <?= htmlspecialchars(mp_format_money($summary['stock_value'])); ?> of stock on hand</p>
      </div>

      <div class='kpis'>
        <a class='kpi' href='<?= base_url('oil_gas/locations'); ?>'><strong><?= (int)$summary['locations']; ?></strong><span><?= htmlspecialchars($site_label); ?>s you can see</span></a>
        <a class='kpi' href='<?= base_url('mobile/stock'); ?>'><strong><?= htmlspecialchars(mp_format_money($summary['stock_value'])); ?></strong><span>Stock value on hand</span></a>
        <?php if(!empty($arrival_mode)): ?>
          <a class='kpi watch' href='<?= base_url('mobile/stock_transfers'); ?>'><strong><?= (int)$summary['arrivals_waiting']; ?></strong><span>Loads waiting to be counted in</span></a>
          <a class='kpi <?= $summary['arrivals_held'] ? 'alert' : ''; ?>' href='<?= base_url('mobile/stock_transfers'); ?>'><strong><?= (int)$summary['arrivals_held']; ?></strong><span>Short loads held for approval</span></a>
        <?php endif; ?>
      </div>

      <div class='card'>
        <div class='section-title'>On the road</div>
        <?php if(!empty($open_loads)): ?>
          <?php foreach($open_loads as $load): ?>
            <a class='row' href='<?= base_url('stock_transfer/receive/' . (int)$load->id); ?>'>
              <div class='name'><?= htmlspecialchars(get_warehouse_name($load->warehouse_from)); ?> → <?= htmlspecialchars(get_warehouse_name($load->warehouse_to)); ?></div>
              <div class='meta'>Load #<?= (int)$load->id; ?> · sent <?= show_date($load->transfer_date); ?></div>
              <?php if($load->receipt_status === 'Awaiting Approval'): ?>
                <span class='flag held'>Short — needs a manager</span>
              <?php else: ?>
                <span class='flag transit'>Waiting to be counted</span>
              <?php endif; ?>
            </a>
          <?php endforeach; ?>
        <?php elseif(!empty($arrival_mode)): ?>
          <div class='empty'><i class='fa fa-check-circle'></i>Nothing in transit. Every load that left a site has been counted in.</div>
        <?php else: ?>
          <div class='empty'><i class='fa fa-exchange'></i>Arrival counting is off for this company, so transfers post straight away.</div>
        <?php endif; ?>
      </div>

      <?php if(!empty($forecourt_by_site)): ?>
      <div class='card'>
        <div class='section-title'>Forecourt</div>
        <?php foreach($forecourt_by_site as $site_id => $site): ?>
          <a class='row' href='<?= base_url('oil_gas/forecourt?site='.(int)$site_id); ?>'>
            <div class='name'><?= htmlspecialchars($site['name']); ?></div>
            <div class='meta'>
              <?= (int)$site['tanks']; ?> tank<?= $site['tanks'] === 1 ? '' : 's'; ?> ·
              <?= $site['running'] ? 'a duty is running' : 'no duty open'; ?>
            </div>
            <?php if($site['waiting'] || $site['gaps']): ?>
              <span class='flag held'><?= (int)$site['waiting'] + (int)$site['gaps']; ?> waiting for a manager</span>
            <?php endif; ?>
          </a>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if(!empty($lpg_by_site)): ?>
      <div class='card'>
        <div class='section-title'>LPG</div>
        <?php foreach($lpg_by_site as $site_id => $site): ?>
          <a class='row' href='<?= base_url('oil_gas/lpg?site='.(int)$site_id); ?>'>
            <div class='name'><?= htmlspecialchars($site['name']); ?></div>
            <div class='meta'>
              <?= (int)$site['shells']; ?> shell<?= $site['shells'] === 1 ? '' : 's'; ?> on the register ·
              <?= (int)$site['full']; ?> full
              <?php if($site['today_kg'] > 0): ?> · <?= mp_format_litres($site['today_kg']); ?> kg filled today<?php endif; ?>
            </div>
            <?php if($site['out']): ?><span class='flag held'><?= (int)$site['out']; ?> out with customers</span><?php endif; ?>
          </a>
        <?php endforeach; ?>
        <?php if($lpg['waiting'] || (!empty($deposit_flag) && $lpg['deposits'] > 0)): ?>
          <div class='meta' style='margin-top:10px;'>
            <?php if($lpg['waiting']): ?><?= (int)$lpg['waiting']; ?> fill<?= $lpg['waiting'] === 1 ? ' has' : 's have'; ?> no till receipt against them in the last week.<?php endif; ?>
            <?php if(!empty($deposit_flag) && $lpg['deposits'] > 0): ?> <?= htmlspecialchars(mp_format_money($lpg['deposits'])); ?> is held against shells that have not come home — money owed, not income.<?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <div class='card'>
        <div class='section-title'>Your <?= htmlspecialchars($site_label); ?>s</div>
        <?php if(!empty($locations)): ?>
          <?php foreach($locations as $location): $figures = isset($rollup[$location->id]) ? $rollup[$location->id] : null; ?>
            <a class='row' href='<?= base_url('oil_gas/locations'); ?>'>
              <div class='name'><?= htmlspecialchars($location->warehouse_name); ?><?= !empty($location->is_distribution_center) ? ' · depot' : ''; ?></div>
              <div class='meta'>
                <?= $figures ? (int)$figures['stock_lines'] . ' lines · ' . htmlspecialchars(mp_format_money($figures['stock_value'])) : '—'; ?>
                <?php if($figures && $figures['dispatches_month']): ?> · <?= (int)$figures['dispatches_month']; ?> sent this month<?php endif; ?>
              </div>
            </a>
          <?php endforeach; ?>
        <?php else: ?>
          <div class='empty'><i class='fa fa-map-marker'></i>No locations are visible to you yet.</div>
        <?php endif; ?>
      </div>

      <div class='card'>
        <div class='section-title'>Daily work</div>
        <div class='actions'>
          <?php if(permissions('stock_transfer_add')): ?><a class='action' href='<?= base_url('mobile/stock_transfer_form'); ?>'><i class='fa fa-exchange'></i> Send a load</a><?php endif; ?>
          <?php if(permissions('stock_transfer_view')): ?><a class='action' href='<?= base_url('mobile/stock_transfers'); ?>'><i class='fa fa-list'></i> Transfers</a><?php endif; ?>
          <?php if(permissions('items_view')): ?><a class='action' href='<?= base_url('mobile/stock'); ?>'><i class='fa fa-flask'></i> Stock</a><?php endif; ?>
          <?php if(!empty($forecourt_sites)): ?>
            <?php if(permissions('warehouse_view')): ?><a class='action' href='<?= base_url('oil_gas/forecourt'); ?>'><i class='fa fa-tachometer'></i> Forecourt</a><?php endif; ?>
            <?php if(permissions('warehouse_view')): ?><a class='action' href='<?= base_url('oil_gas/duty'); ?>'><i class='fa fa-clock-o'></i> Duty readings</a><?php endif; ?>
          <?php endif; ?>
          <?php if(!empty($lpg_by_site) && permissions('warehouse_view')): ?>
            <a class='action' href='<?= base_url('oil_gas/lpg'); ?>'><i class='fa fa-balance-scale'></i> LPG board</a>
            <a class='action' href='<?= base_url('oil_gas/cylinders'); ?>'><i class='fa fa-circle-o'></i> Cylinders</a>
          <?php endif; ?>
          <?php if(!empty($can_see_jobs)): ?><a class='action' href='<?= base_url('oil_gas/jobs'); ?>'><i class='fa fa-wrench'></i> Service work</a><?php endif; ?>
          <a class='action' href='<?= base_url('oil_gas/report'); ?>'><i class='fa fa-building'></i> Company report</a>
          <?php if(permissions('warehouse_view')): ?><a class='action' href='<?= base_url('oil_gas/locations'); ?>'><i class='fa fa-map-marker'></i> Locations</a><?php endif; ?>
        </div>
      </div>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'home']); ?>
  </div>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
