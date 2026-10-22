<?php
$ci =& get_instance();
$d = $garden_daily;
$counts = array_column($d['pipeline'], 'total', 'stage');
$hour = (int) date('G');
$greeting = $hour < 12 ? 'Good morning' : ($hour < 17 ? 'Good afternoon' : 'Good evening');
$greetingName = function_exists('mp_current_user_first_name') ? mp_current_user_first_name('there') : 'there';

$cardMap = [];
foreach ($garden_pulse['cards'] as $card) { $cardMap[$card[0]] = $card; }
$salesCard = $cardMap['Sales'] ?? null;
$profitCard = $cardMap['Gross profit'] ?? null;
$balanceCard = $cardMap['Customer balances'] ?? null;
$invoiceCard = $cardMap['Invoices'] ?? null;

$hasSales = $ci->permissions('sales_view');
$salesInfo = $hasSales && isset($ci->dashboard_model) ? $ci->dashboard_model->get_sales_by_range($garden_pulse['range']) : null;
$invoiceInfo = $hasSales && isset($ci->dashboard_model) ? $ci->dashboard_model->get_invoices_by_range($garden_pulse['range']) : null;
$profitInfo = $hasSales && $ci->permissions('profit_report') && isset($ci->dashboard_model) ? $ci->dashboard_model->get_profit_by_range($garden_pulse['range']) : null;

$trend = function ($change) {
    $change = (float) $change;
    if ($change == 0.0) return null;
    $up = $change > 0;
    return ['up' => $up, 'label' => ($up ? '↑' : '↓') . ' ' . rtrim(rtrim(number_format(abs($change), 1), '0'), '.') . '%'];
};

$kpis = [];
if ($salesCard) {
    $kpis[] = ['label' => 'Sales today', 'value' => store_number_format($salesCard[1]), 'note' => 'From ' . (int) ($invoiceInfo['count'] ?? 0) . ' transactions', 'icon' => 'fa-line-chart', 'tone' => 'green', 'trend' => $trend($salesInfo['change'] ?? 0), 'url' => $salesCard[4]];
}
if ($profitCard) {
    $margin = $profitInfo['margin'] ?? 0;
    $kpis[] = ['label' => 'Gross profit', 'value' => store_number_format($profitCard[1]), 'note' => rtrim(rtrim(number_format((float) $margin, 1), '0'), '.') . '% profit margin', 'icon' => 'fa-pie-chart', 'tone' => 'violet', 'trend' => $trend($profitInfo['profit'] ?? 0), 'url' => $profitCard[4]];
}
if ($balanceCard) {
    $kpis[] = ['label' => 'Outstanding balances', 'value' => store_number_format($balanceCard[1]), 'note' => htmlspecialchars($balanceCard[3], ENT_QUOTES, 'UTF-8'), 'icon' => 'fa-money', 'tone' => 'amber', 'trend' => null, 'url' => $balanceCard[4], 'sr' => 'Customer balances'];
}
if ($invoiceCard) {
    $kpis[] = ['label' => 'Orders today', 'value' => number_format((int) $invoiceCard[1]), 'note' => (int) ($invoiceInfo['count'] ?? 0) . ' final invoices this period', 'icon' => 'fa-shopping-bag', 'tone' => 'blue', 'trend' => $trend($invoiceInfo['change'] ?? 0), 'url' => $invoiceCard[4]];
}

// Sales overview chart. The two panel controls are real dropdowns that drive
// these GET params (metric + period), so the chart always reloads with the
// chosen view instead of sitting on a fixed "7 days / Sales Amount".
$barMetrics = ['sales' => 'Sales Amount', 'purchase' => 'Purchases', 'expense' => 'Expenses'];
$barMetricShort = ['sales' => 'Sales', 'purchase' => 'Purchases', 'expense' => 'Expenses'];
$barRanges = ['Today' => 'Today', '7Days' => 'Last 7 days', '30Days' => 'Last 30 days', 'ThisMonth' => 'This month', 'LastMonth' => 'Last month', 'ThisYear' => 'This year'];
$barRange = (string) $ci->input->get('bar_range');
if (!isset($barRanges[$barRange])) $barRange = '7Days';
$barMetric = (string) $ci->input->get('bar_metric');
if (!isset($barMetrics[$barMetric])) $barMetric = 'sales';
$barUrl = function (array $overrides) use ($garden_pulse) {
    return base_url('garden') . '?' . http_build_query(array_merge(['range' => $garden_pulse['range']], $overrides)) . '#business-performance';
};

$chart = $hasSales && isset($ci->dashboard_model) ? $ci->dashboard_model->get_bar_chart($barRange) : [];
$chartSales = array_reverse(array_values($chart[$barMetric] ?? []));
$chartDates = array_reverse(array_values($chart['date'] ?? []));
$chartMaxValue = 0.0;
foreach ($chartSales as $value) { if ((float) $value > $chartMaxValue) $chartMaxValue = (float) $value; }
$chartCeil = $chartMaxValue > 0 ? $chartMaxValue * 1.15 : 100000;
$axis = [];
for ($i = 4; $i >= 0; $i--) {
    $axisValue = $chartCeil * $i / 4;
    $axis[] = $axisValue >= 1000000 ? number_format($axisValue / 1000000, 1) . 'M' : ($axisValue >= 1000 ? number_format($axisValue / 1000, 0) . 'K' : number_format($axisValue, 0));
}
$barColumns = max(1, count($chartSales) ?: 7);
$schedule = $d['jobs'] ?? [];
$inventory = $d['watchlist'] ?? [];

$projects = [];
if (!empty($d['projects']) && $ci->permissions('service_jobs_view') && mp_feature_enabled('service_jobs') && mp_garden_has_mode($garden_profile, 'landscaping')) {
    $ci->load->model('service_project_model');
    $projects = array_slice((array) $ci->service_project_model->listing(), 0, 4);
}
$stageProgress = ['enquiry' => 15, 'inspection' => 30, 'planning' => 25, 'quoted' => 40, 'approved' => 55, 'deposit' => 65, 'execution' => 75, 'in_progress' => 60, 'near_completion' => 90, 'completed' => 90, 'paid' => 100];
$stageBadge = ['enquiry' => ['Planning', 'gw-badge-purple'], 'inspection' => ['Planning', 'gw-badge-purple'], 'planning' => ['Planning', 'gw-badge-purple'], 'quoted' => ['In Progress', 'gw-badge-green'], 'approved' => ['In Progress', 'gw-badge-green'], 'deposit' => ['In Progress', 'gw-badge-green'], 'execution' => ['In Progress', 'gw-badge-green'], 'in_progress' => ['In Progress', 'gw-badge-green'], 'near_completion' => ['Near Completion', 'gw-badge-blue'], 'completed' => ['Near Completion', 'gw-badge-blue'], 'paid' => ['Completed', 'gw-badge-green']];

$jobBadge = function ($title) {
    if (stripos($title, 'meeting') !== false) return ['Meeting', 'gw-badge-purple'];
    if (stripos($title, 'maintenance') !== false) return ['Maintenance', 'gw-badge-blue'];
    return ['Site Visit', 'gw-badge-green'];
};

$quickActions = [];
if ($ci->permissions('sales_add')) $quickActions[] = ['New Sale', 'fa-shopping-cart', 'pos'];
if ($ci->permissions('service_jobs_add') && mp_feature_enabled('service_jobs') && mp_garden_has_mode($garden_profile, 'landscaping')) $quickActions[] = ['New Project', 'fa-file-text-o', 'garden/projects#new-project'];
if ($ci->permissions('items_add')) $quickActions[] = ['Add Product', 'fa-cube', 'items/add'];
if ($ci->permissions('customers_add')) $quickActions[] = ['Add Customer', 'fa-user-plus', 'customers/add'];
if ($ci->permissions('expense_view')) $quickActions[] = ['Record Expense', 'fa-credit-card', 'expense'];
if ($ci->permissions('quotation_add')) $quickActions[] = ['Create Quote', 'fa-file-text', 'quotation/add'];
$quickActions = array_slice($quickActions, 0, 6);
?>
<div class="gw-hub" id="garden-overview">
  <header class="gw-hub-head">
    <div class="gw-hub-title">
      <p class="garden-kicker">GARDEN CENTRE &amp; LANDSCAPING</p>
      <h1><?= htmlspecialchars($greeting); ?><span class="gw-hub-welcome">, <?= htmlspecialchars($greetingName); ?></span></h1>
      <p class="gw-hub-sub"><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?></p>
    </div>
    <div class="gw-hub-aside">
      <div class="gw-hub-date"><strong><?= date('l, j F Y'); ?></strong><span><i class="fa fa-line-chart" aria-hidden="true"></i> Keep your business growing</span></div>
      <div class="gw-hub-actions">
        <?php if ($ci->permissions('sales_add')): ?><a class="gw-btn gw-btn-primary" href="<?= base_url('pos'); ?>"><i class="fa fa-plus" aria-hidden="true"></i> New sale</a><?php endif; ?>
        <?php if ($ci->permissions('service_jobs_add') && mp_feature_enabled('service_jobs') && mp_garden_has_mode($garden_profile, 'landscaping')): ?><a class="gw-btn gw-btn-light" href="<?= base_url('garden/projects#new-project'); ?>"><i class="fa fa-plus" aria-hidden="true"></i> New project</a><?php endif; ?>
      </div>
    </div>
  </header>

  <section class="gw-attention" aria-label="What needs your attention today">
    <div class="gw-attention-lead">
      <span class="gw-attention-icon gw-tone-amber"><i class="fa fa-bell-o" aria-hidden="true"></i></span>
      <strong>What needs your<br>attention today?</strong>
    </div>
    <?php if ($d['low_plants'] !== null): ?><a class="gw-attention-item" href="<?= base_url('garden/catalogue'); ?>"><span class="gw-attention-icon gw-tone-green"><i class="fa fa-leaf" aria-hidden="true"></i></span><span class="gw-attention-copy"><strong><?= (int) $d['low_plants']; ?> plants to restock</strong><small>Items are below minimum stock</small><em>View low stock →</em></span></a><?php endif; ?>
    <?php if ($d['visits'] !== null): ?><a class="gw-attention-item" href="<?= base_url('garden/work'); ?>"><span class="gw-attention-icon gw-tone-blue"><i class="fa fa-calendar-check-o" aria-hidden="true"></i></span><span class="gw-attention-copy"><strong><?= (int) $d['visits']; ?> visits due today</strong><small>Landscaping or maintenance visits</small><em>View schedule →</em></span></a><?php endif; ?>
    <?php if ($d['projects'] !== null): ?><a class="gw-attention-item" href="<?= base_url('garden/projects'); ?>"><span class="gw-attention-icon gw-tone-violet"><i class="fa fa-map-o" aria-hidden="true"></i></span><span class="gw-attention-copy"><strong><?= (int) $d['projects']; ?> open projects</strong><small>Projects in progress</small><em>View projects →</em></span></a><?php endif; ?>
  </section>

  <section class="garden-performance gw-performance" id="business-performance" aria-labelledby="garden-performance-title">
    <h2 class="gw-visually-hidden" id="garden-performance-title">Business performance</h2>
    <nav class="gw-visually-hidden" aria-label="Business performance period"><?php foreach (['Today' => 'Today', '7Days' => '7 days', '30Days' => '30 days', 'LastMonth' => 'Last month', 'ThisMonth' => 'This month', 'ThisYear' => 'This year'] as $key => $label): ?><a href="<?= base_url('garden?range=' . $key . '#business-performance'); ?>" class="<?= $garden_pulse['range'] === $key ? 'is-selected' : ''; ?>" <?= $garden_pulse['range'] === $key ? 'aria-current="true"' : ''; ?>><?= $label; ?></a><?php endforeach; ?></nav>
    <div class="gw-kpis">
      <?php foreach ($kpis as $kpi): ?><a class="gw-kpi" href="<?= base_url($kpi['url']); ?>"><span class="gw-kpi-top"><span class="gw-kpi-icon gw-tone-<?= $kpi['tone']; ?>"><i class="fa <?= $kpi['icon']; ?>" aria-hidden="true"></i></span><?php if (!empty($kpi['trend'])): ?><span class="gw-trend <?= $kpi['trend']['up'] ? 'is-up' : 'is-down'; ?>"><?= $kpi['trend']['label']; ?></span><?php endif; ?></span><span class="gw-kpi-label"><?= htmlspecialchars($kpi['label']); ?><?php if (!empty($kpi['sr'])): ?><span class="gw-visually-hidden"><?= htmlspecialchars($kpi['sr']); ?></span><?php endif; ?></span><strong><?= $kpi['value']; ?></strong><small><?= $kpi['note']; ?></small></a><?php endforeach; ?>
    </div>
  </section>

  <div class="gw-main-grid">
    <section class="gw-panel gw-chart-panel">
      <div class="gw-panel-head"><h2><i class="fa fa-bar-chart" aria-hidden="true"></i> <?= htmlspecialchars($barMetricShort[$barMetric]); ?> Overview</h2><div class="gw-panel-controls">
        <label class="gw-select"><span class="gw-visually-hidden">Chart metric</span><select onchange="if(this.value)location.href=this.value" aria-label="Chart metric">
          <?php foreach ($barMetrics as $key => $label): ?><option value="<?= htmlspecialchars($barUrl(['bar_metric' => $key, 'bar_range' => $barRange])); ?>" <?= $barMetric === $key ? 'selected' : ''; ?>><?= htmlspecialchars($label); ?></option><?php endforeach; ?>
        </select></label>
        <label class="gw-select"><span class="gw-visually-hidden">Chart period</span><select onchange="if(this.value)location.href=this.value" aria-label="Chart period">
          <?php foreach ($barRanges as $key => $label): ?><option value="<?= htmlspecialchars($barUrl(['bar_metric' => $barMetric, 'bar_range' => $key])); ?>" <?= $barRange === $key ? 'selected' : ''; ?>><?= htmlspecialchars($label); ?></option><?php endforeach; ?>
        </select></label>
      </div></div>
      <div class="gw-chart">
        <div class="gw-chart-axis"><?php foreach ($axis as $label): ?><span><?= $label; ?></span><?php endforeach; ?></div>
        <div class="gw-chart-plot">
          <div class="gw-chart-grid"><?php for ($i = 0; $i < 5; $i++): ?><i></i><?php endfor; ?></div>
          <div class="gw-bars" style="grid-template-columns:repeat(<?= (int) $barColumns; ?>,1fr)">
            <?php if ($chartSales): foreach ($chartSales as $index => $value): $height = $chartCeil > 0 ? max(4, round(100 * (float) $value / $chartCeil)) : 4; $stamp = strtotime((string) ($chartDates[$index] ?? 'today')); ?>
            <div class="gw-bar"><i style="height:<?= $height; ?>%" title="<?= htmlspecialchars(store_number_format($value)); ?>"></i><span><?= date('D', $stamp); ?><small><?= date('j M', $stamp); ?></small></span></div>
            <?php endforeach; else: for ($i = 0; $i < 7; $i++): ?><div class="gw-bar"><i style="height:4%"></i><span><?= date('D', strtotime('-' . (6 - $i) . ' days')); ?><small><?= date('j M', strtotime('-' . (6 - $i) . ' days')); ?></small></span></div><?php endfor; endif; ?>
          </div>
        </div>
      </div>
    </section>

    <section class="gw-panel gw-schedule-panel">
      <div class="gw-panel-head"><h2><i class="fa fa-calendar" aria-hidden="true"></i> Today&rsquo;s Schedule</h2><a href="<?= base_url('garden/work'); ?>">View all →</a></div>
      <div class="gw-schedule">
        <?php if ($schedule): foreach ($schedule as $index => $job): $badge = $jobBadge($job->title); $tones = ['gw-dot-green', 'gw-dot-blue', 'gw-dot-amber', 'gw-dot-violet', 'gw-dot-teal']; ?>
        <a class="gw-schedule-row" href="<?= base_url('garden/work#job-' . (int) $job->id); ?>"><span class="gw-schedule-time"><?= htmlspecialchars($job->scheduled_date ? date('D j M', strtotime($job->scheduled_date)) : 'Unscheduled'); ?></span><span class="gw-schedule-line"><i class="gw-dot <?= $tones[$index % 5]; ?>"></i></span><span class="gw-schedule-copy"><strong><?= htmlspecialchars($job->title); ?></strong><small><?= htmlspecialchars($job->customer_name ?: 'Customer not linked'); ?></small></span><span class="gw-badge <?= $badge[1]; ?>"><?= $badge[0]; ?></span></a>
        <?php endforeach; else: ?><div class="gw-empty-line"><i class="fa fa-calendar-check-o" aria-hidden="true"></i> No visits scheduled. <a href="<?= base_url('garden/work'); ?>">Plan team work →</a></div><?php endif; ?>
      </div>
    </section>
  </div>

  <div class="gw-bottom-grid">
    <section class="gw-panel">
      <div class="gw-panel-head"><h2><i class="fa fa-cube" aria-hidden="true"></i> Inventory Health</h2><a href="<?= base_url('garden/catalogue'); ?>">View all →</a></div>
      <div class="gw-inventory">
        <?php if ($inventory): foreach ($inventory as $plant): $attrs = json_decode($plant->attributes_json, true) ?: []; ?>
        <a class="gw-inventory-row" href="<?= base_url($ci->permissions('items_edit') ? 'garden/plant/' . (int) $plant->id : 'garden/catalogue'); ?>"><span class="gw-thumb"><i class="fa fa-pagelines" aria-hidden="true"></i></span><span class="gw-inventory-copy"><strong><?= htmlspecialchars($plant->item_name); ?></strong><small><?= htmlspecialchars($attrs['botanical_name'] ?? 'Living plant'); ?></small></span><span class="gw-inventory-qty"><b><?= htmlspecialchars($plant->stock); ?> left</b><small>Min: <?= htmlspecialchars($plant->alert_qty); ?></small></span></a>
        <?php endforeach; else: ?><div class="gw-empty-line"><i class="fa fa-pagelines" aria-hidden="true"></i> No plant stock alerts.</div><?php endif; ?>
      </div>
    </section>

    <section class="gw-panel">
      <div class="gw-panel-head"><h2><i class="fa fa-folder-open-o" aria-hidden="true"></i> Active Projects</h2><a href="<?= base_url('garden/projects'); ?>">View all →</a></div>
      <div class="gw-projects">
        <?php if ($projects): foreach ($projects as $project): $stage = (string) ($project->stage ?? 'enquiry'); $percent = $stageProgress[$stage] ?? 25; $badge = $stageBadge[$stage] ?? ['In Progress', 'gw-badge-green']; ?>
        <a class="gw-project-row" href="<?= base_url('garden/project/' . (int) $project->id); ?>"><span class="gw-project-copy"><strong><?= htmlspecialchars($project->title); ?></strong><small><?= htmlspecialchars($project->customer_name ?: 'Project'); ?></small></span><span class="gw-progress"><i style="width:<?= (int) $percent; ?>%"></i></span><b class="gw-project-percent"><?= (int) $percent; ?>%</b><span class="gw-badge <?= $badge[1]; ?>"><?= $badge[0]; ?></span></a>
        <?php endforeach; else: ?><div class="gw-empty-line"><i class="fa fa-map-o" aria-hidden="true"></i> No active projects. <a href="<?= base_url('garden/projects#new-project'); ?>">Start one →</a></div><?php endif; ?>
      </div>
    </section>

    <section class="gw-panel">
      <div class="gw-panel-head"><h2><i class="fa fa-bolt" aria-hidden="true"></i> Quick Actions</h2></div>
      <div class="gw-quick-actions">
        <?php foreach ($quickActions as $action): ?><a class="gw-quick-action" href="<?= base_url($action[2]); ?>"><i class="fa <?= $action[1]; ?>" aria-hidden="true"></i><span><?= htmlspecialchars($action[0]); ?></span></a><?php endforeach; ?>
      </div>
    </section>
  </div>

  <?php if ($d['projects'] !== null): ?><section class="garden-panel garden-project-pipeline"><div class="garden-panel-heading"><div><span class="garden-kicker">FROM IDEA TO OUTDOORS</span><h2>Landscaping pipeline</h2></div><a href="<?= base_url('garden/projects'); ?>">Open projects ↗</a></div><div class="garden-pipeline">
  <?php foreach (['Enquiry & inspection' => ['enquiry', 'inspection'], 'Quote & approval' => ['quoted', 'approved'], 'Ready to start' => ['deposit'], 'On site' => ['execution'], 'Completion & payment' => ['completed', 'paid']] as $label => $stages): $total = 0; foreach ($stages as $stage) $total += (int) ($counts[$stage] ?? 0); ?><a href="<?= base_url('garden/projects'); ?>"><span><?= $label; ?></span><strong><?= $total; ?></strong><div class="garden-pipeline-track"><i style="width:<?= $d['projects'] ? max(0, round(100 * $total / $d['projects'])) : 0; ?>%"></i></div></a><?php endforeach; ?></div></section><?php endif; ?>
  <section class="garden-intelligence" id="business-intelligence" aria-label="Business Intelligence">
  <?php $this->load->view('comman/intelligence_card', [
      'intel' => $garden_intel, 'intel_title' => 'Business Intelligence',
      'intel_subtitle' => 'Current alerts and rolling-history insights. The KPI period filter applies to Business performance.',
      'intel_empty_message' => 'No actionable insights yet. As sales, stock and customer history build, this report will highlight trends and work that needs attention.',
  ]); ?></section>

  <?php $this->load->view('garden/license_cards'); ?>
</div>
