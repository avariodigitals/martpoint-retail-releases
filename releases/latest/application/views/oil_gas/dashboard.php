<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<?php
// Plain-English meaning of each capability the business has switched on.
$og_registry = function_exists('mp_get_feature_registry') ? mp_get_feature_registry() : array();
$og_blurb = function($key) use ($og_registry) {
    if (!empty($og_registry[$key]['hint'])) return $og_registry[$key]['hint'];
    return '';
};
$og_labels = function_exists('mp_get_feature_flags') ? mp_get_feature_flags() : array();

// What this company actually runs. The workspace itself is not a piece of daily
// work, so it is left out of the list a reader would act on.
$og_caps = array();
foreach ($og_registry as $og_key => $og_meta) {
    if (($og_meta['section'] ?? '') !== 'fuel') continue;
    if ($og_key === 'oil_gas_workspace') continue;
    if (!function_exists('mp_feature_enabled') || !mp_feature_enabled($og_key)) continue;
    $og_caps[$og_key] = $og_labels[$og_key] ?? $og_key;
}

$og_summary   = is_array($summary ?? null) ? $summary : array('locations'=>0, 'stock_value'=>0, 'arrivals_waiting'=>0, 'arrivals_held'=>0, 'tolerance'=>0);
$og_sites     = is_array($locations ?? null) ? $locations : array();
$og_rollup    = is_array($rollup ?? null) ? $rollup : array();
$og_shown     = array_slice($og_sites, 0, 6);
$og_more      = count($og_sites) - count($og_shown);
$og_loads     = is_array($open_loads ?? null) ? $open_loads : array();
$og_by_site   = is_array($forecourt_by_site ?? null) ? $forecourt_by_site : array();
$og_forecourt = is_array($forecourt ?? null) ? $forecourt : array('awaiting'=>0, 'gaps'=>0, 'open_duties'=>0);
$og_dip       = (float)($forecourt_dip ?? 0);
$og_lpg_site  = is_array($lpg_by_site ?? null) ? $lpg_by_site : array();
$og_lpg       = is_array($lpg ?? null) ? $lpg : array('shells'=>0, 'full'=>0, 'out'=>0, 'waiting'=>0, 'deposits'=>0.0);
$og_site_word = htmlspecialchars($site_label ?? 'location');
?>
<style>
.og-hero{background:linear-gradient(135deg,#0f2a33,#1d6274);border-radius:16px;padding:24px;color:#fff;margin-bottom:20px;display:flex;justify-content:space-between;gap:24px;align-items:center}
.og-hero h2{margin:0 0 6px;font-size:25px;font-weight:800}.og-hero p{margin:0;opacity:.85;max-width:740px;line-height:1.45}
.og-hero-actions{display:flex;flex-wrap:wrap;gap:10px;align-items:center}.og-ghost{display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border:1px solid rgba(255,255,255,.6);border-radius:10px;color:#fff!important;font-size:13px;font-weight:800;text-decoration:none!important;white-space:nowrap}.og-ghost:hover{background:rgba(255,255,255,.12)}
.og-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:14px;margin-bottom:16px}
.og-kpi{background:var(--mp-surface);border:1px solid var(--mp-border);border-radius:14px;padding:17px;text-decoration:none!important;min-width:0}
.og-kpi strong{display:block;font-size:25px;color:var(--mp-ink);line-height:1.2;overflow:hidden;text-overflow:ellipsis}
.og-kpi span{font-size:12px;color:var(--mp-muted);display:block;margin-top:5px;line-height:1.35}.og-kpi small{display:block;color:var(--mp-muted);font-size:11px;margin-top:8px}
.og-kpi.watch strong{color:#92400e}.og-kpi.alert strong{color:#991b1b}
.og-note{display:flex;gap:10px;flex-wrap:wrap;margin:0 0 20px}.og-note a,.og-note span{padding:9px 12px;border-radius:10px;font-size:12px;font-weight:700;text-decoration:none}
.og-note .warn{background:#fff7ed;color:#9a3412;border:1px solid #fed7aa}.og-note .info{background:#f0f9ff;color:#0c4a6e;border:1px solid #bae6fd}
.og-wrap{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;align-items:start;margin-bottom:20px}
.og-panel{background:var(--mp-surface);border:1px solid var(--mp-border);border-radius:14px;margin:0;min-width:0}
.og-panel.spaced{margin-bottom:20px}
.og-panel .mp-card-head{padding:16px 18px}.og-panel .mp-card-body{padding:0!important}
.og-panel .mp-card-head h3{margin:0;font-size:15px}
.og-loc{display:block;padding:15px 18px;border-bottom:1px solid var(--mp-border);text-decoration:none!important;color:var(--mp-ink)}
.og-loc:last-child{border-bottom:0}.og-loc-head{display:flex;justify-content:space-between;gap:12px;align-items:baseline;min-width:0}
.og-loc b{font-size:14px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}.og-loc .town{font-size:11px;color:var(--mp-muted);white-space:nowrap}
.og-loc .figures{font-size:11px;color:var(--mp-muted);margin-top:6px;line-height:1.45}
.og-chips{display:flex;flex-wrap:wrap;gap:6px;margin-top:8px}.og-chip{font-size:10px;font-weight:800;letter-spacing:.02em;padding:4px 8px;border-radius:999px;background:#e8f4f7;color:#0f4a5c}
.og-chip.warn{background:#fff7ed;color:#9a3412}
.og-plain{padding:14px 18px;font-size:12px;color:var(--mp-muted);line-height:1.7}
.og-plain a{font-weight:700}
.og-empty{padding:18px;color:var(--mp-muted);font-size:12px;line-height:1.5}
.og-caps{list-style:none;margin:0;padding:0}.og-caps li{padding:12px 18px;border-bottom:1px solid var(--mp-border)}.og-caps li:last-child{border-bottom:0}
.og-caps b{display:block;font-size:13px;margin-bottom:3px}.og-caps small{color:var(--mp-muted);font-size:11px;line-height:1.4;display:block}
.og-actions{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:14px;padding:18px}
.og-action{display:flex;gap:13px;align-items:flex-start;padding:17px;border:1px solid var(--mp-border);border-radius:13px;background:var(--mp-surface);text-decoration:none!important;color:var(--mp-ink)}
.og-action i{width:38px;height:38px;border-radius:10px;background:#e8f4f7;color:#155e75;display:flex;align-items:center;justify-content:center;font-size:17px;flex:0 0 38px}
.og-action b{display:block;margin:1px 0 4px}.og-action small{color:var(--mp-muted);line-height:1.35;display:block;font-size:11px}
@media(max-width:1100px){.og-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}.og-wrap{grid-template-columns:1fr}}
@media(max-width:900px){.og-actions{grid-template-columns:1fr 1fr}.og-hero{align-items:flex-start;flex-direction:column}}
</style>

<div class="og-hero">
  <div>
    <h2>Oil &amp; Gas Workspace</h2>
    <p>Your stations, gas outlets, depot and supply work in one account. Head office sees the whole business; each location runs only the operations it is set up for.</p>
  </div>
  <div class="og-hero-actions">
    <a href="<?= base_url('oil_gas/locations'); ?>" class="og-ghost"><i class="fa fa-map-marker"></i> Operating Locations</a>
    <?php if(is_store_admin() || $CI->permissions('business_profile')): ?><a href="<?= base_url('business_profile/capabilities'); ?>" class="og-ghost"><i class="fa fa-sliders"></i> Capabilities</a><?php endif; ?>
  </div>
</div>

<div class="og-kpis">
  <a class="og-kpi" href="<?= base_url('warehouse'); ?>"><strong><?= (int)$og_summary['locations']; ?></strong><span><?= $og_site_word ?>s in view</span><small><?= $og_summary['locations'] ? 'Shared with Branches' : 'Add your first site under Branches'; ?></small></a>
  <a class="og-kpi" href="<?= base_url('inventory'); ?>"><strong><?= htmlspecialchars(mp_format_money($og_summary['stock_value'])); ?></strong><span>Stock on hand</span><small>Across the <?= $og_site_word ?>s you can see</small></a>
  <?php if(!empty($arrival_mode)): ?>
    <a class="og-kpi watch" href="<?= base_url('stock_transfer/view'); ?>"><strong><?= (int)$og_summary['arrivals_waiting']; ?></strong><span>Loads waiting to be counted in</span><small>Listed one by one under "On the road"</small></a>
    <a class="og-kpi <?= $og_summary['arrivals_held'] ? 'alert' : ''; ?>" href="<?= base_url('stock_transfer/view'); ?>"><strong><?= (int)$og_summary['arrivals_held']; ?></strong><span>Short loads held for a manager</span><small>Outside the <?= (float)($og_summary['tolerance'] ?? 0); ?>% transport limit</small></a>
  <?php endif; ?>
</div>

<?php if(!$og_sites): ?>
  <div class="og-note"><a class="warn" href="<?= base_url('warehouse'); ?>"><i class="fa fa-exclamation-triangle"></i> No operating locations yet — add each station, outlet or depot under Branches</a></div>
<?php elseif(!$og_caps): ?>
  <div class="og-note"><a class="info" href="<?= base_url('business_profile/capabilities'); ?>"><i class="fa fa-sliders"></i> The workspace is open but no fuel operation is switched on yet — turn on gas refills, tanks, pumps or bulk dispatch for this business</a></div>
<?php endif; ?>

<div class="og-wrap">
  <div class="og-panel mp-card-form">
    <div class="mp-card-head"><h3>On the road</h3><a class="mp-qa-btn" href="<?= base_url('stock_transfer/view'); ?>">All transfers</a></div>
    <div class="mp-card-body">
      <?php if($og_loads): ?>
        <?php foreach($og_loads as $load): ?>
          <a class="og-loc" href="<?= base_url('stock_transfer/receive/' . (int)$load->id); ?>">
            <div class="og-loc-head"><b><?= htmlspecialchars(get_warehouse_name($load->warehouse_from)); ?> &rarr; <?= htmlspecialchars(get_warehouse_name($load->warehouse_to)); ?></b><span class="town">Sent <?= show_date($load->transfer_date); ?></span></div>
            <div class="figures">Count in what actually arrived, then approve any difference before it posts.</div>
            <div class="og-chips"><?php if($load->receipt_status === 'Awaiting Approval'): ?><span class="og-chip warn">Short — needs a manager</span><?php else: ?><span class="og-chip">Waiting to be counted</span><?php endif; ?></div>
          </a>
        <?php endforeach; ?>
      <?php elseif(!empty($arrival_mode)): ?>
        <div class="og-empty">Nothing in transit. Every load that left a site has been counted in.</div>
      <?php else: ?>
        <div class="og-empty">Arrival counting is switched off for this company, so transfers post straight away.</div>
      <?php endif; ?>
    </div>
  </div>

  <div class="og-panel mp-card-form">
    <div class="mp-card-head"><h3>Your <?= $og_site_word ?>s</h3><a class="mp-qa-btn" href="<?= base_url('oil_gas/locations'); ?>">Set up</a></div>
    <div class="mp-card-body">
      <?php if($og_shown): ?>
        <?php foreach($og_shown as $loc): $figures = isset($og_rollup[(int)$loc->id]) ? $og_rollup[(int)$loc->id] : null; ?>
          <a class="og-loc" href="<?= base_url('oil_gas/locations'); ?>">
            <div class="og-loc-head"><b><?= htmlspecialchars($loc->warehouse_name !== '' ? $loc->warehouse_name : 'Unnamed location'); ?><?= !empty($loc->is_distribution_center) ? ' · depot' : ''; ?></b><span class="town"><?= htmlspecialchars(trim((string)$loc->city)); ?></span></div>
            <div class="figures">
              <?= $figures ? (int)$figures['stock_lines'].' stock line'.((int)$figures['stock_lines'] === 1 ? '' : 's').' holding quantity · '.htmlspecialchars(mp_format_money($figures['stock_value'])) : 'No stock counted at this site yet'; ?>
              <?php if($figures && $figures['dispatches_month']): ?> · <?= (int)$figures['dispatches_month']; ?> load<?= $figures['dispatches_month'] === 1 ? '' : 's'; ?> sent this month<?php endif; ?>
            </div>
          </a>
        <?php endforeach; ?>
        <?php if($og_more > 0): ?><div class="og-empty"><?= (int)$og_more; ?> more location<?= $og_more === 1 ? '' : 's'; ?> not shown here — open Set up to see all of them.</div><?php endif; ?>
      <?php else: ?>
        <div class="og-empty">No operating locations are visible to you. Add each station, gas outlet, depot or shop under Branches, then come back to choose what each one runs.</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if($og_by_site): ?>
<div class="og-panel mp-card-form spaced">
  <div class="mp-card-head"><h3>Forecourt</h3><a class="mp-qa-btn" href="<?= base_url('oil_gas/forecourt'); ?>">Open forecourt</a></div>
  <div class="mp-card-body">
    <?php foreach($og_by_site as $site_id => $site): ?>
      <a class="og-loc" href="<?= base_url('oil_gas/forecourt?site='.(int)$site_id); ?>">
        <div class="og-loc-head"><b><?= htmlspecialchars($site['name']); ?></b><span class="town"><?= (int)$site['tanks']; ?> tank<?= $site['tanks'] === 1 ? '' : 's'; ?></span></div>
        <div class="figures"><?= $site['running'] ? 'A duty is open on this site.' : 'No duty open right now.'; ?> The pumps are read at the counter and checked against the dip at the tank.</div>
        <?php if($site['waiting'] || $site['gaps']): ?>
          <div class="og-chips"><span class="og-chip warn"><?= (int)$site['waiting'] + (int)$site['gaps']; ?> waiting for a manager</span></div>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>
    <div class="og-plain">
      <a href="<?= base_url('oil_gas/duty'); ?>">Duty sheet</a> ·
      <a href="<?= base_url('oil_gas/tanks'); ?>">Tanks and dip readings</a> ·
      <a href="<?= base_url('oil_gas/pumps'); ?>">Pumps and nozzles</a> ·
      <a href="<?= base_url('oil_gas/fill'); ?>">Book a fill or a delivery</a>
      <?php if($og_forecourt['awaiting'] || $og_forecourt['gaps']): ?> — what the pumps measured is compared with what the till sold and what the book holds, and a gap outside the <?= (float)$og_dip; ?>% rule goes to a manager<?php endif; ?>.
    </div>
  </div>
</div>
<?php endif; ?>

<?php if($og_lpg_site): ?>
<div class="og-panel mp-card-form spaced">
  <div class="mp-card-head"><h3>LPG &amp; cylinders</h3><a class="mp-qa-btn" href="<?= base_url('oil_gas/lpg'); ?>">Open LPG board</a></div>
  <div class="mp-card-body">
    <?php foreach($og_lpg_site as $site_id => $site): ?>
      <a class="og-loc" href="<?= base_url('oil_gas/lpg?site='.(int)$site_id); ?>">
        <div class="og-loc-head"><b><?= htmlspecialchars($site['name']); ?></b><span class="town"><?= (int)$site['shells']; ?> shell<?= $site['shells'] === 1 ? '' : 's'; ?> on the register</span></div>
        <div class="figures">
          <?= (int)$site['full']; ?> full<?= $site['out'] ? ', '.(int)$site['out'].' out with customers' : ''; ?>
          <?php if($site['today_kg'] > 0): ?> · <?= mp_format_litres($site['today_kg']); ?> kg filled today<?php endif; ?>
        </div>
        <?php if(!$site['refills'] || !$site['tracking']): ?>
          <div class="og-chips"><span class="og-chip"><?= $site['refills'] ? 'Register only — no weighing at this site' : 'Weighing only — no local register'; ?></span></div>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>
    <div class="og-plain">
      <a href="<?= base_url('oil_gas/cylinders'); ?>">Cylinder register</a> ·
      <a href="<?= base_url('oil_gas/refill'); ?>">Weigh a fill</a> ·
      <a href="<?= base_url('oil_gas/exchange'); ?>">One shell out, one back</a>
      <?php if(!empty($deposit_flag)): ?> · <a href="<?= base_url('oil_gas/deposits'); ?>">Deposits held</a><?php endif; ?>.
      The gas you charge for is the kilograms that went out through the till, so weighing a shell never deducts stock a second time.
      <?php if($og_lpg['waiting']): ?> <?= (int)$og_lpg['waiting']; ?> fill<?= $og_lpg['waiting'] === 1 ? ' has' : 's have'; ?> no till receipt against it in the last week.<?php endif; ?>
      <?php if(!empty($deposit_flag) && $og_lpg['deposits'] > 0): ?> <?= htmlspecialchars(mp_format_money($og_lpg['deposits'])); ?> is held against shells that have not come home — money owed, not income.<?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="og-panel mp-card-form spaced">
  <div class="mp-card-head"><h3>What this business runs</h3></div>
  <div class="mp-card-body">
    <?php if($og_caps): ?>
      <ul class="og-caps">
        <?php foreach($og_caps as $og_key => $og_label): ?>
          <li><b><?= htmlspecialchars($og_label); ?></b><?php $hint = $og_blurb($og_key); if($hint !== ''): ?><small><?= htmlspecialchars($hint); ?></small><?php endif; ?></li>
        <?php endforeach; ?>
      </ul>
    <?php else: ?>
      <div class="og-empty">Nothing switched on beyond the workspace itself. Open Capabilities and turn on the operations this company actually runs — a small gas outlet only needs refills and cylinders, while a station adds tanks, pumps and shift reconciliation.</div>
    <?php endif; ?>
  </div>
</div>

<div class="og-panel mp-card-form spaced">
  <div class="mp-card-head"><h3>Daily work</h3></div>
  <div class="mp-card-body"><div class="og-actions">
    <?php if($CI->permissions('sales_add')): ?><a class="og-action" href="<?= base_url('sales/add'); ?>"><i class="fa fa-file-text"></i><span><b>New sale or invoice</b><small>Bill a customer for fuel, gas, lubricants or parts.</small></span></a><?php endif; ?>
    <?php if($CI->permissions('pos_view') || $CI->permissions('sales_add')): ?><a class="og-action" href="<?= base_url('pos'); ?>"><i class="fa fa-dashboard"></i><span><b>Counter</b><small>Ring up the forecourt or shop counter.</small></span></a><?php endif; ?>
    <?php if($CI->permissions('purchase_add')): ?><a class="og-action" href="<?= base_url('purchase/add'); ?>"><i class="fa fa-truck"></i><span><b>Supplier purchase</b><small>Record a load of fuel, gas or stock as it arrives.</small></span></a><?php endif; ?>
    <a class="og-action" href="<?= base_url('inventory'); ?>"><i class="fa fa-database"></i><span><b>Stock by location</b><small>What each site is holding right now.</small></span></a>
    <?php if($CI->permissions('warehouse_view')): ?><a class="og-action" href="<?= base_url('warehouse'); ?>"><i class="fa fa-map-marker"></i><span><b>Locations</b><small>Add a site or change its details.</small></span></a><?php endif; ?>
    <?php if($CI->permissions('quotation_add')): ?><a class="og-action" href="<?= base_url('quotation/add'); ?>"><i class="fa fa-file-text-o"></i><span><b>Quotation</b><small>Price a bulk order or a supply job before committing.</small></span></a><?php endif; ?>
    <?php if($CI->permissions('customers_view')): ?><a class="og-action" href="<?= base_url('customers'); ?>"><i class="fa fa-users"></i><span><b>Customers</b><small>Trade accounts, balances and delivery sites.</small></span></a><?php endif; ?>
    <?php if($CI->permissions('stock_transfer_report')): ?><a class="og-action" href="<?= base_url('reports/stock_transfer'); ?>"><i class="fa fa-exchange"></i><span><b>Transfer report</b><small>What moved between locations and what was received.</small></span></a><?php endif; ?>
    <?php if(!empty($can_see_jobs)): ?><a class="og-action" href="<?= base_url('oil_gas/jobs'); ?>"><i class="fa fa-wrench"></i><span><b>Service work</b><small>Book pump, generator and filling-scale visits at the site they happen at.</small></span></a><?php endif; ?>
    <a class="og-action" href="<?= base_url('oil_gas/report'); ?>"><i class="fa fa-building"></i><span><b>Company report</b><small>Every location on one page, with a company line that is their sum.</small></span></a>
    <a class="og-action" href="<?= base_url('oil_gas/locations'); ?>"><i class="fa fa-sliders"></i><span><b>Location operations</b><small>Choose which site runs gas refills, pumps, shifts or dispatch.</small></span></a>
  </div></div>
</div>
