<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.os-analytics-card{background:var(--mp-surface)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:18px 20px!important;box-shadow:var(--mp-shadow-sm)!important;text-align:center!important}
.os-analytics-number{font-size:28px!important;font-weight:800!important;color:var(--mp-primary)!important;line-height:1.1!important}
.os-analytics-label{font-size:11px!important;color:var(--mp-muted)!important;text-transform:uppercase!important;letter-spacing:.06em!important;margin-top:6px!important;font-weight:600!important}
.os-analytics-table th{font-size:11px!important;text-transform:uppercase!important;font-weight:700!important;color:var(--mp-muted)!important;letter-spacing:.06em!important;background:var(--mp-bg)!important;padding:12px 16px!important;border-bottom:1px solid var(--mp-border)!important;text-align:left!important}
.os-analytics-table td{padding:12px 16px!important;border-bottom:1px solid var(--mp-border)!important;font-size:13px!important;color:var(--mp-text)!important;vertical-align:middle!important}
.os-analytics-table tr:last-child td{border-bottom:none!important}
.os-bar-chart{display:flex!important;align-items:flex-end!important;gap:3px!important;height:220px!important;padding-top:24px!important;padding-bottom:28px!important;overflow-x:auto!important}
.os-bar{flex:1!important;min-width:14px!important;background:linear-gradient(to top, var(--mp-primary), #60A5FA)!important;border-radius:4px 4px 0 0!important;position:relative!important;transition:opacity .2s ease!important}
.os-bar:hover{opacity:.85!important}
.os-bar-label{position:absolute!important;bottom:-22px!important;left:50%!important;transform:translateX(-50%)!important;font-size:9px!important;color:var(--mp-muted)!important;white-space:nowrap!important}
.os-bar-value{position:absolute!important;top:-16px!important;left:50%!important;transform:translateX(-50%)!important;font-size:9px!important;font-weight:700!important;color:var(--mp-primary)!important}
.os-heatmap-grid{display:grid!important;grid-template-columns:80px repeat(24,1fr)!important;gap:2px!important;min-width:900px!important}
.os-heatmap-cell{padding:4px!important;text-align:center!important;font-size:10px!important;border-radius:3px!important}
.os-heatmap-cell.label{background:var(--mp-bg)!important;font-weight:600!important;color:var(--mp-ink)!important}
.os-heatmap-cell.hour{background:var(--mp-bg)!important;color:var(--mp-muted)!important;font-size:9px!important}
.os-device-pie{width:120px!important;height:120px!important;border-radius:50%!important;margin:0 auto!important;position:relative!important}
.os-device-legend{display:flex!important;justify-content:center!important;gap:16px!important;margin-top:12px!important;flex-wrap:wrap!important}
.os-device-item{display:flex!important;align-items:center!important;gap:6px!important;font-size:13px!important;color:var(--mp-text)!important}
.os-device-dot{width:12px!important;height:12px!important;border-radius:50%!important}
.os-range-badge{background:var(--mp-bg)!important;color:var(--mp-ink)!important;padding:5px 14px!important;border-radius:20px!important;font-size:13px!important;font-weight:600!important;border:1px solid var(--mp-border)!important}
.os-filter-tabs{display:flex!important;gap:4px!important;background:var(--mp-surface)!important;border:1px solid var(--mp-border)!important;border-radius:10px!important;padding:4px!important;flex-wrap:wrap!important}
.os-filter-tabs a{border:none!important;background:none!important;padding:7px 14px!important;border-radius:7px!important;font-size:13px!important;font-weight:600!important;color:var(--mp-muted)!important;cursor:pointer!important;text-decoration:none!important;transition:all .12s ease!important}
.os-filter-tabs a:hover{color:var(--mp-ink)!important;text-decoration:none!important}
.os-filter-tabs a.active{background:var(--mp-primary)!important;color:#fff!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Track visits, devices, sources and activity for your storefront</div>
  </div>
  <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
    <div class="os-filter-tabs">
      <a href="<?= base_url('online_store/analytics?filter=today'); ?>" class="<?= $filter == 'today' ? 'active' : ''; ?>">Today</a>
      <a href="<?= base_url('online_store/analytics?filter=week'); ?>" class="<?= $filter == 'week' ? 'active' : ''; ?>">Week</a>
      <a href="<?= base_url('online_store/analytics?filter=month'); ?>" class="<?= $filter == 'month' ? 'active' : ''; ?>">Month</a>
      <a href="<?= base_url('online_store/analytics?filter=year'); ?>" class="<?= $filter == 'year' ? 'active' : ''; ?>">Year</a>
      <a href="<?= base_url('online_store/analytics?filter=custom'); ?>" class="<?= $filter == 'custom' ? 'active' : ''; ?>">Custom</a>
    </div>
    <span class="os-range-badge"><i class="fa fa-calendar"></i> <?= htmlspecialchars($range_label); ?></span>
    <a href="<?= base_url('online_store/export_analytics?filter=' . $filter . '&start=' . $start_date . '&end=' . $end_date); ?>" target="_blank" class="mp-qa-btn green"><i class="fa fa-file-pdf-o"></i> Export PDF</a>
  </div>
</div>

<?php if($filter == 'custom'): ?>
<div class="mp-card-form" style="margin-bottom:16px!important;">
  <div class="mp-card-body">
    <form method="get" action="<?= base_url('online_store/analytics'); ?>" class="form-inline" style="display:flex;gap:12px;align-items:center;flex-wrap:wrap;">
      <input type="hidden" name="filter" value="custom">
      <div class="mp-form-group" style="margin:0!important;"><label style="margin-right:6px;">Start Date</label><input type="date" name="start" class="mp-form-control" value="<?= htmlspecialchars($start_date); ?>" required></div>
      <div class="mp-form-group" style="margin:0!important;"><label style="margin-right:6px;">End Date</label><input type="date" name="end" class="mp-form-control" value="<?= htmlspecialchars($end_date); ?>" required></div>
      <button type="submit" class="mp-btn-primary"><i class="fa fa-check"></i> Apply</button>
    </form>
  </div>
</div>
<?php endif; ?>

<div class="mp-kpi-grid">
  <div class="os-analytics-card"><div class="os-analytics-number"><?= number_format($summary['total']); ?></div><div class="os-analytics-label">Total Visits</div></div>
  <div class="os-analytics-card"><div class="os-analytics-number"><?= number_format($summary['unique']); ?></div><div class="os-analytics-label">Unique Sessions</div></div>
  <div class="os-analytics-card"><div class="os-analytics-number"><?= number_format($summary['today']); ?></div><div class="os-analytics-label">Today</div></div>
  <div class="os-analytics-card"><div class="os-analytics-number"><?= number_format($summary['yesterday']); ?></div><div class="os-analytics-label">Yesterday</div></div>
  <div class="os-analytics-card"><div class="os-analytics-number" style="color:var(--mp-success)!important;"><?= number_format($summary['new_users']); ?></div><div class="os-analytics-label">New Users</div></div>
  <div class="os-analytics-card"><div class="os-analytics-number" style="color:var(--mp-warning)!important;"><?= number_format($summary['returning_users']); ?></div><div class="os-analytics-label">Returning Users</div></div>
</div>

<div class="mp-card-form" style="margin-top:20px!important;">
  <div class="mp-card-head"><h3><?= $chart_type == 'hour' ? 'Hourly Visits (Today)' : ($chart_type == 'month' ? 'Monthly Visits (' . htmlspecialchars($range_label) . ')' : 'Daily Visits (' . htmlspecialchars($range_label) . ')'); ?></h3></div>
  <div class="mp-card-body">
    <?php if(!empty($chart_data) && array_sum($chart_data) > 0):
      $maxChart = max($chart_data);
    ?>
    <div class="os-bar-chart">
      <?php foreach($chart_data as $i => $val):
        $h = $maxChart > 0 ? round(($val / $maxChart) * 100) : 0;
        $label = $chart_labels[$i] ?? '';
      ?>
      <div class="os-bar" style="height:<?= max($h, 2); ?>%;">
        <span class="os-bar-value"><?= $val > 0 ? $val : ''; ?></span>
        <span class="os-bar-label"><?= htmlspecialchars($label); ?></span>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="mp-empty-state">No visit data for this period.</div>
    <?php endif; ?>
  </div>
</div>

<div class="mp-card-form" style="margin-top:20px!important;">
  <div class="mp-card-head"><h3>Activity Heatmap</h3></div>
  <div class="mp-card-body mp-dt-scroll">
    <?php
      $dowNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
      $heatmapMatrix = [];
      $heatmapMax = 1;
      foreach($heatmap as $h){
        $dow = (int)$h->dow;
        $hour = (int)$h->hour;
        $heatmapMatrix[$dow][$hour] = (int)$h->visits;
        if($h->visits > $heatmapMax) $heatmapMax = $h->visits;
      }
    ?>
    <div class="os-heatmap-grid">
      <div class="os-heatmap-cell label"></div>
      <?php for($h=0; $h<24; $h++): ?><div class="os-heatmap-cell hour"><?= $h; ?></div><?php endfor; ?>
      <?php for($d=0; $d<7; $d++): $dowIndex = $d + 1; ?>
      <div class="os-heatmap-cell label"><?= $dowNames[$d]; ?></div>
      <?php for($h=0; $h<24; $h++):
        $val = $heatmapMatrix[$dowIndex][$h] ?? 0;
        $intensity = $heatmapMax > 0 ? ($val / $heatmapMax) : 0;
        $alpha = 0.08 + ($intensity * 0.92);
        $color = $val > 0 ? 'background:rgba(59,130,246,' . round($alpha, 2) . ');color:#1e40af;font-weight:700;' : 'background:var(--mp-bg);color:var(--mp-muted);';
      ?>
      <div class="os-heatmap-cell" style="<?= $color; ?>" title="<?= $dowNames[$d] . ' ' . sprintf('%02d:00', $h) . ': ' . $val . ' visits'; ?>"><?= $val > 0 ? $val : '-'; ?></div>
      <?php endfor; ?>
      <?php endfor; ?>
    </div>
  </div>
</div>

<div class="os-content-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px;">
  <div class="mp-card-form">
    <div class="mp-card-head"><h3>Device Breakdown</h3></div>
    <div class="mp-card-body" style="text-align:center;">
      <?php
        $deviceColors = ['Desktop'=>'#3B82F6','Mobile'=>'#10B981','Tablet'=>'#F59E0B','Bot/Other'=>'#94A3B8'];
        $totalDevices = array_sum($devices);
        $deviceSegments = [];
        $runningDeg = 0;
        foreach($devices as $name => $count){
          if($totalDevices > 0){
            $deg = ($count / $totalDevices) * 360;
            $deviceSegments[] = $deviceColors[$name] . ' ' . round($runningDeg, 2) . 'deg ' . round($runningDeg + $deg, 2) . 'deg';
            $runningDeg += $deg;
          }
        }
        $pieStyle = $totalDevices > 0 ? 'background: conic-gradient(' . implode(', ', $deviceSegments) . ');' : 'background:var(--mp-border);';
      ?>
      <div class="os-device-pie" style="<?= $pieStyle; ?>"></div>
      <div class="os-device-legend">
        <?php foreach($devices as $name => $count):
          $pct = $totalDevices > 0 ? round(($count / $totalDevices) * 100, 1) : 0;
        ?>
        <div class="os-device-item"><span class="os-device-dot" style="background:<?= $deviceColors[$name] ?? '#94A3B8'; ?>"></span> <?= htmlspecialchars($name); ?> <strong><?= $pct; ?>%</strong></div>
        <?php endforeach; ?>
        <?php if($totalDevices == 0): ?><div class="os-device-item text-muted">No data yet.</div><?php endif; ?>
      </div>
    </div>
  </div>

  <div class="mp-card-form">
    <div class="mp-card-head"><h3>Top Sources</h3></div>
    <div class="mp-card-body" style="padding:0!important;">
      <table class="os-analytics-table" width="100%">
        <thead><tr><th>Source</th><th class="text-right">Visits</th></tr></thead>
        <tbody>
          <?php foreach($top_sources as $s): ?>
          <tr><td><?= htmlspecialchars($s->source ?: 'Direct'); ?></td><td class="text-right"><?= number_format($s->visits); ?></td></tr>
          <?php endforeach; ?>
          <?php if(empty($top_sources)): ?><tr><td colspan="2" class="mp-empty-state">No data yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="os-content-grid" style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:20px;">
  <div class="mp-card-form">
    <div class="mp-card-head"><h3>Top Pages</h3></div>
    <div class="mp-card-body" style="padding:0!important;">
      <table class="os-analytics-table" width="100%">
        <thead><tr><th>Page</th><th class="text-right">Visits</th></tr></thead>
        <tbody>
          <?php foreach($top_pages as $p): ?>
          <tr><td style="max-width:400px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($p->page_url); ?></td><td class="text-right"><?= number_format($p->visits); ?></td></tr>
          <?php endforeach; ?>
          <?php if(empty($top_pages)): ?><tr><td colspan="2" class="mp-empty-state">No data yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <div class="mp-card-form">
    <div class="mp-card-head"><h3>Search Terms</h3></div>
    <div class="mp-card-body" style="padding:0!important;">
      <table class="os-analytics-table" width="100%">
        <thead><tr><th>Search Term</th><th class="text-right">Visits</th></tr></thead>
        <tbody>
          <?php foreach($search_terms as $st): ?>
          <tr><td><?= htmlspecialchars($st->search_term); ?></td><td class="text-right"><?= number_format($st->visits); ?></td></tr>
          <?php endforeach; ?>
          <?php if(empty($search_terms)): ?><tr><td colspan="2" class="mp-empty-state">No search data yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="mp-card-form" style="margin-top:20px!important;">
  <div class="mp-card-head"><h3>Recent Visits</h3></div>
  <div class="mp-card-body" style="padding:0!important;">
    <div class="mp-dt-scroll">
      <table class="os-analytics-table" width="100%">
        <thead><tr><th>Time</th><th>Page</th><th>Source</th><th>Referrer</th><th>IP</th></tr></thead>
        <tbody>
          <?php foreach($recent_visits as $v): ?>
          <tr>
            <td><?= date('M j, g:i a', strtotime($v->created_at)); ?></td>
            <td style="max-width:300px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($v->page_url); ?></td>
            <td><span class="label label-<?= $v->source == 'Direct' ? 'default' : 'info'; ?>"><?= htmlspecialchars($v->source ?: 'Direct'); ?></span></td>
            <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= htmlspecialchars($v->referrer ?: '-'); ?></td>
            <td><?= htmlspecialchars($v->ip_address); ?></td>
          </tr>
          <?php endforeach; ?>
          <?php if(empty($recent_visits)): ?><tr><td colspan="5" class="mp-empty-state">No visits recorded yet.</td></tr><?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>$(".online_store-analytics-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
