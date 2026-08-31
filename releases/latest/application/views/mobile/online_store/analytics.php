<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Analytics</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-ink: #1E293B; --mp-muted: #64748B; --mp-border: #E2E8F0; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 120px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: var(--mp-bg); }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .topbar-titles { flex: 1; min-width: 0; }
    .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .filter-bar { display: flex; gap: 8px; overflow-x: auto; padding-bottom: 12px; -ms-overflow-style: none; scrollbar-width: none; }
    .filter-bar::-webkit-scrollbar { display: none; }
    .filter-pill { flex-shrink: 0; padding: 8px 14px; border-radius: 20px; font-size: 12px; font-weight: 600; background: #fff; border: 1px solid var(--mp-border); color: var(--mp-muted); text-decoration: none; }
    .filter-pill.active { background: var(--mp-primary); color: #fff; border-color: var(--mp-primary); }
    .kpi-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-bottom: 16px; }
    .kpi-card { background: #fff; border-radius: 16px; padding: 16px; border: 1px solid var(--mp-border); box-shadow: 0 1px 3px rgba(15,23,42,0.04); }
    .kpi-label { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; margin-bottom: 6px; }
    .kpi-value { font-size: 22px; font-weight: 700; }
    .card { background: #fff; border-radius: 16px; border: 1px solid var(--mp-border); overflow: hidden; box-shadow: 0 1px 3px rgba(15,23,42,0.04); margin-bottom: 16px; }
    .card-title { font-size: 15px; font-weight: 700; padding: 14px 16px; border-bottom: 1px solid var(--mp-border); }
    .range { font-size: 13px; color: var(--mp-muted); padding: 14px 16px; }
    .bar-chart { display: flex; align-items: flex-end; gap: 3px; height: 180px; padding: 10px 0 28px; overflow-x: auto; }
    .bar { flex: 1; min-width: 10px; border-radius: 4px 4px 0 0; position: relative; }
    .bar-label { position: absolute; bottom: -20px; left: 50%; transform: translateX(-50%); font-size: 9px; color: var(--mp-muted); white-space: nowrap; }
    .bar-value { position: absolute; top: -14px; left: 50%; transform: translateX(-50%); font-size: 9px; font-weight: 700; color: var(--mp-primary); }
    .list { padding: 0 16px; }
    .list-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid var(--mp-border); font-size: 14px; }
    .list-row:last-child { border-bottom: none; }
    .list-name { flex: 1; min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; padding-right: 8px; }
    .list-val { font-weight: 600; color: var(--mp-muted); }
    .pie-wrap { text-align: center; padding: 16px; }
    .pie { width: 120px; height: 120px; border-radius: 50%; margin: 0 auto; }
    .legend { display: flex; justify-content: center; flex-wrap: wrap; gap: 12px; margin-top: 14px; font-size: 13px; }
    .legend-item { display: flex; align-items: center; gap: 6px; }
    .dot { width: 12px; height: 12px; border-radius: 50%; }
    .empty { text-align: center; padding: 30px; color: var(--mp-muted); font-size: 14px; }
    .custom-form { display: none; gap: 8px; padding: 0 0 14px; }
    .custom-form.show { display: grid; grid-template-columns: 1fr 1fr auto; align-items: center; }
    .custom-form input { width: 100%; min-width: 0; padding: 10px; border-radius: 12px; border: 1px solid var(--mp-border); font-size: 14px; }
    .custom-form button { padding: 10px 16px; border-radius: 12px; background: var(--mp-primary); color: #fff; border: none; font-weight: 600; white-space: nowrap; }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 140px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= $back_url; ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Analytics</h1>
        </div>
      </div>

      <div class="filter-bar">
        <?php $filters = ['today' => 'Today', 'week' => 'Week', 'month' => 'Month', 'year' => 'Year', 'custom' => 'Custom']; ?>
        <?php foreach($filters as $k => $l): ?>
          <a href="<?= base_url('mobile/online_store/analytics?filter=' . $k . ($k === 'custom' ? '&start=' . urlencode($start_date) . '&end=' . urlencode($end_date) : '')); ?>" class="filter-pill <?= $filter === $k ? 'active' : ''; ?>"><?= $l; ?></a>
        <?php endforeach; ?>
      </div>

      <?php if($filter == 'custom'): ?>
      <form class="custom-form <?= $filter === 'custom' ? 'show' : ''; ?>" method="get" action="<?= base_url('mobile/online_store/analytics'); ?>">
        <input type="hidden" name="filter" value="custom">
        <input type="date" name="start" value="<?= $start_date; ?>">
        <input type="date" name="end" value="<?= $end_date; ?>">
        <button type="submit">Apply</button>
      </form>
      <?php endif; ?>

      <div class="range"><?= htmlspecialchars($range_label); ?></div>

      <div class="kpi-grid">
        <div class="kpi-card"><div class="kpi-label">Total Visits</div><div class="kpi-value"><?= number_format($summary['total'] ?? 0); ?></div></div>
        <div class="kpi-card"><div class="kpi-label">Unique Sessions</div><div class="kpi-value"><?= number_format($summary['unique'] ?? 0); ?></div></div>
        <div class="kpi-card"><div class="kpi-label">Today</div><div class="kpi-value"><?= number_format($summary['today'] ?? 0); ?></div></div>
        <div class="kpi-card"><div class="kpi-label">Yesterday</div><div class="kpi-value"><?= number_format($summary['yesterday'] ?? 0); ?></div></div>
        <div class="kpi-card"><div class="kpi-label">New Users</div><div class="kpi-value"><?= number_format($summary['new_users'] ?? 0); ?></div></div>
        <div class="kpi-card"><div class="kpi-label">Returning</div><div class="kpi-value"><?= number_format($summary['returning_users'] ?? 0); ?></div></div>
      </div>

      <div class="card">
        <div class="card-title">
          <?= $chart_type == 'hour' ? 'Hourly Visits' : ($chart_type == 'month' ? 'Monthly Visits' : 'Daily Visits'); ?>
        </div>
        <div style="padding:0 12px;">
          <?php if(!empty($chart_data) && array_sum($chart_data) > 0):
            $maxChart = max($chart_data);
            $colors = ['#0057FF','#3B82F6','#60A5FA','#93C5FD'];
          ?>
          <div class="bar-chart">
            <?php foreach($chart_data as $i => $val):
              $h = $maxChart > 0 ? round(($val / $maxChart) * 100) : 0;
            ?>
            <div class="bar" style="height:<?= max($h, 2); ?>%;background:<?= $colors[$i % 4]; ?>">
              <?php if($val > 0): ?><span class="bar-value"><?= $val; ?></span><?php endif; ?>
              <span class="bar-label"><?= $chart_labels[$i] ?? ''; ?></span>
            </div>
            <?php endforeach; ?>
          </div>
          <?php else: ?>
            <div class="empty">No visit data for this period.</div>
          <?php endif; ?>
        </div>
      </div>

      <div class="card">
        <div class="card-title">Device Breakdown</div>
        <?php
          $deviceColors = ['Desktop' => '#3B82F6', 'Mobile' => '#10B981', 'Tablet' => '#F59E0B', 'Bot/Other' => '#94A3B8'];
          $totalDevices = array_sum($devices);
          $segments = []; $deg = 0;
          foreach($devices as $name => $count){
            if($totalDevices > 0){ $d = ($count / $totalDevices) * 360; $segments[] = ($deviceColors[$name] ?? '#94A3B8') . ' ' . round($deg, 2) . 'deg ' . round($deg + $d, 2) . 'deg'; $deg += $d; }
          }
          $pieStyle = $totalDevices > 0 ? 'background: conic-gradient(' . implode(', ', $segments) . ');' : 'background:#E2E8F0;';
        ?>
        <div class="pie-wrap">
          <div class="pie" style="<?= $pieStyle; ?>"></div>
          <div class="legend">
            <?php foreach($devices as $name => $count): $pct = $totalDevices > 0 ? round(($count / $totalDevices) * 100, 1) : 0; ?>
              <div class="legend-item"><span class="dot" style="background:<?= $deviceColors[$name] ?? '#94A3B8'; ?>"></span> <?= $name; ?> <?= $pct; ?>%</div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <div class="card">
        <div class="card-title">Top Sources</div>
        <div class="list">
          <?php if(!empty($top_sources)): ?>
            <?php foreach($top_sources as $s): ?>
              <div class="list-row"><span class="list-name"><?= htmlspecialchars($s->source ?: 'Direct'); ?></span><span class="list-val"><?= number_format($s->visits); ?></span></div>
            <?php endforeach; ?>
          <?php else: ?><div class="empty">No data.</div><?php endif; ?>
        </div>
      </div>

      <div class="card">
        <div class="card-title">Top Pages</div>
        <div class="list">
          <?php if(!empty($top_pages)): ?>
            <?php foreach($top_pages as $p): ?>
              <div class="list-row"><span class="list-name"><?= htmlspecialchars($p->page_url); ?></span><span class="list-val"><?= number_format($p->visits); ?></span></div>
            <?php endforeach; ?>
          <?php else: ?><div class="empty">No data.</div><?php endif; ?>
        </div>
      </div>

      <div class="card">
        <div class="card-title">Search Terms</div>
        <div class="list">
          <?php if(!empty($search_terms)): ?>
            <?php foreach($search_terms as $st): ?>
              <div class="list-row"><span class="list-name"><?= htmlspecialchars($st->search_term); ?></span><span class="list-val"><?= number_format($st->visits); ?></span></div>
            <?php endforeach; ?>
          <?php else: ?><div class="empty">No search data.</div><?php endif; ?>
        </div>
      </div>

      <div class="card">
        <div class="card-title">Top Customers</div>
        <div class="list">
          <?php if(!empty($customers)): ?>
            <?php foreach($customers as $c): ?>
              <div class="list-row"><span class="list-name"><?= htmlspecialchars($c->customer_name ?: $c->customer_phone ?: 'Guest'); ?></span><span class="list-val"><?= number_format($c->visits); ?></span></div>
            <?php endforeach; ?>
          <?php else: ?><div class="empty">No customer data.</div><?php endif; ?>
        </div>
      </div>

      <div class="card">
        <div class="card-title">Recent Visits</div>
        <div class="list">
          <?php if(!empty($recent_visits)): ?>
            <?php foreach($recent_visits as $v): ?>
              <div class="list-row" style="display:block;">
                <div class="list-name" style="white-space:nowrap;"><?= date('M j, g:i a', strtotime($v->created_at)); ?> · <?= htmlspecialchars($v->source ?: 'Direct'); ?></div>
                <div style="font-size:12px;color:var(--mp-muted);overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($v->page_url); ?></div>
              </div>
            <?php endforeach; ?>
          <?php else: ?><div class="empty">No visits recorded.</div><?php endif; ?>
        </div>
      </div>
    </section>
    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
