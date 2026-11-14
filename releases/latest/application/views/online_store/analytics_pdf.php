<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title><?= htmlspecialchars($page_title); ?></title>
<style>
body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size:12px; color:#333; margin:20px; }
h1 { font-size:20px; margin:0 0 8px; }
h2 { font-size:14px; margin:16px 0 8px; padding-bottom:4px; border-bottom:1px solid #ddd; }
.meta { color:#666; margin-bottom:16px; font-size:11px; }
.kpi-grid { display:table; width:100%; margin-bottom:20px; }
.kpi-cell { display:table-cell; width:25%; padding:12px; text-align:center; background:#f8fafc; border:1px solid #e2e8f0; }
.kpi-number { font-size:22px; font-weight:800; color:#3B82F6; }
.kpi-label { font-size:10px; color:#64748B; text-transform:uppercase; }
table { width:100%; border-collapse:collapse; margin-bottom:16px; }
th, td { padding:6px 8px; border:1px solid #e2e8f0; text-align:left; font-size:11px; }
th { background:#f1f5f9; font-weight:700; }
.text-right { text-align:right; }
.text-center { text-align:center; }
.device-row td { padding:8px; }
.device-bar { height:16px; background:#3B82F6; border-radius:2px; }
.footer { margin-top:30px; padding-top:10px; border-top:1px solid #ddd; font-size:10px; color:#999; }
</style>
</head>
<body>
<h1><?= htmlspecialchars($page_title); ?></h1>
<div class="meta">Period: <?= htmlspecialchars($range_label); ?> &nbsp;|&nbsp; Generated: <?= $generated_at; ?></div>

<div class="kpi-grid">
  <div class="kpi-cell">
    <div class="kpi-number"><?= number_format($summary['total']); ?></div>
    <div class="kpi-label">Total Visits</div>
  </div>
  <div class="kpi-cell">
    <div class="kpi-number"><?= number_format($summary['unique']); ?></div>
    <div class="kpi-label">Unique Sessions</div>
  </div>
  <div class="kpi-cell">
    <div class="kpi-number"><?= number_format($summary['today']); ?></div>
    <div class="kpi-label">Today</div>
  </div>
  <div class="kpi-cell">
    <div class="kpi-number"><?= number_format($summary['yesterday']); ?></div>
    <div class="kpi-label">Yesterday</div>
  </div>
  <div class="kpi-cell">
    <div class="kpi-number"><?= number_format($summary['new_users']); ?></div>
    <div class="kpi-label">New Users</div>
  </div>
  <div class="kpi-cell">
    <div class="kpi-number"><?= number_format($summary['returning_users']); ?></div>
    <div class="kpi-label">Returning Users</div>
  </div>
</div>

<h2>Device Breakdown</h2>
<table>
  <thead><tr><th>Device</th><th class="text-right">Count</th><th class="text-right">%</th></tr></thead>
  <tbody>
    <?php $totalDevices = array_sum($devices); ?>
    <?php foreach($devices as $name => $count):
      $pct = $totalDevices > 0 ? round(($count / $totalDevices) * 100, 1) : 0;
    ?>
    <tr><td><?= $name; ?></td><td class="text-right"><?= number_format($count); ?></td><td class="text-right"><?= $pct; ?>%</td></tr>
    <?php endforeach; ?>
  </tbody>
</table>

<h2>Top Sources</h2>
<table>
  <thead><tr><th>Source</th><th class="text-right">Visits</th></tr></thead>
  <tbody>
    <?php foreach($top_sources as $s): ?>
    <tr><td><?= htmlspecialchars($s->source ?: 'Direct'); ?></td><td class="text-right"><?= number_format($s->visits); ?></td></tr>
    <?php endforeach; ?>
    <?php if(empty($top_sources)): ?><tr><td colspan="2" class="text-center">No data.</td></tr><?php endif; ?>
  </tbody>
</table>

<h2>Top Pages</h2>
<table>
  <thead><tr><th>Page</th><th class="text-right">Visits</th></tr></thead>
  <tbody>
    <?php foreach($top_pages as $p): ?>
    <tr><td><?= htmlspecialchars($p->page_url); ?></td><td class="text-right"><?= number_format($p->visits); ?></td></tr>
    <?php endforeach; ?>
    <?php if(empty($top_pages)): ?><tr><td colspan="2" class="text-center">No data.</td></tr><?php endif; ?>
  </tbody>
</table>

<h2>Search Terms</h2>
<table>
  <thead><tr><th>Search Term</th><th class="text-right">Visits</th></tr></thead>
  <tbody>
    <?php foreach($search_terms as $st): ?>
    <tr><td><?= htmlspecialchars($st->search_term); ?></td><td class="text-right"><?= number_format($st->visits); ?></td></tr>
    <?php endforeach; ?>
    <?php if(empty($search_terms)): ?><tr><td colspan="2" class="text-center">No search data.</td></tr><?php endif; ?>
  </tbody>
</table>

<h2>Top Customer Sessions</h2>
<table>
  <thead><tr><th>Session ID</th><th class="text-right">Visits</th><th>First Visit</th><th>Last Visit</th></tr></thead>
  <tbody>
    <?php foreach($customers as $c): ?>
    <tr><td><?= substr(htmlspecialchars($c->session_id), 0, 24); ?></td><td class="text-right"><?= number_format($c->visits); ?></td><td><?= date('M j, g:i a', strtotime($c->first_visit)); ?></td><td><?= date('M j, g:i a', strtotime($c->last_visit)); ?></td></tr>
    <?php endforeach; ?>
    <?php if(empty($customers)): ?><tr><td colspan="4" class="text-center">No customer data.</td></tr><?php endif; ?>
  </tbody>
</table>

<div class="footer">MartPoint Analytics Report</div>
</body>
</html>
