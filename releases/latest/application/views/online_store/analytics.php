<!DOCTYPE html>
<html>
<head>
<?php $this->load->view('comman/code_css.php');?>
<style>
.analytics-card { background:#fff; border-radius:8px; padding:20px; box-shadow:0 1px 3px rgba(0,0,0,0.08); }
.analytics-number { font-size:32px; font-weight:800; color:#3B82F6; }
.analytics-label { font-size:13px; color:#64748B; text-transform:uppercase; letter-spacing:0.5px; }
.analytics-table th { background:#F8FAFC; font-size:12px; text-transform:uppercase; letter-spacing:0.5px; }
.bar-chart { display:flex; align-items:flex-end; gap:3px; height:220px; padding-top:24px; padding-bottom:28px; overflow-x:auto; }
.bar { flex:1; min-width:14px; background:linear-gradient(to top, #3B82F6, #60A5FA); border-radius:4px 4px 0 0; position:relative; transition:opacity .2s; }
.bar:hover { opacity:.85; }
.bar-label { position:absolute; bottom:-22px; left:50%; transform:translateX(-50%); font-size:9px; color:#64748B; white-space:nowrap; }
.bar-value { position:absolute; top:-16px; left:50%; transform:translateX(-50%); font-size:9px; font-weight:700; color:#3B82F6; }
.heatmap-grid { display:grid; grid-template-columns: 80px repeat(24, 1fr); gap:2px; }
.heatmap-cell { padding:4px; text-align:center; font-size:10px; border-radius:3px; }
.heatmap-cell.label { background:#F1F5F9; font-weight:600; color:#475569; }
.heatmap-cell.hour { background:#F8FAFC; color:#94A3B8; font-size:9px; }
.device-pie { width:120px; height:120px; border-radius:50%; margin:0 auto; position:relative; }
.device-legend { display:flex; justify-content:center; gap:16px; margin-top:12px; flex-wrap:wrap; }
.device-item { display:flex; align-items:center; gap:6px; font-size:13px; }
.device-dot { width:12px; height:12px; border-radius:50%; }
.filter-btn { border-radius:4px; }
.range-badge { background:#E2E8F0; color:#475569; padding:4px 12px; border-radius:16px; font-size:13px; font-weight:600; }
</style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar');?>
  <div class="content-wrapper">
    <section class="content-header">
      <h1><?= $page_title ?></h1>
      <ol class="breadcrumb">
        <li><a href="<?=base_url('dashboard');?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?=base_url('online_store');?>">Online Store</a></li>
        <li class="active"><?= $page_title; ?></li>
      </ol>
    </section>

    <section class="content">
      <!-- Filters & Export -->
      <div class="row" style="margin-bottom:8px;">
        <div class="col-md-12">
          <div class="box box-solid" style="margin-bottom:0;">
            <div class="box-body" style="padding:12px 15px;">
              <div class="pull-left">
                <div class="btn-group">
                  <a href="<?= base_url('online_store/analytics?filter=today'); ?>" class="btn btn-sm btn-default filter-btn <?= $filter == 'today' ? 'active btn-primary' : ''; ?>">Today</a>
                  <a href="<?= base_url('online_store/analytics?filter=week'); ?>" class="btn btn-sm btn-default filter-btn <?= $filter == 'week' ? 'active btn-primary' : ''; ?>">Week</a>
                  <a href="<?= base_url('online_store/analytics?filter=month'); ?>" class="btn btn-sm btn-default filter-btn <?= $filter == 'month' ? 'active btn-primary' : ''; ?>">Month</a>
                  <a href="<?= base_url('online_store/analytics?filter=year'); ?>" class="btn btn-sm btn-default filter-btn <?= $filter == 'year' ? 'active btn-primary' : ''; ?>">Year</a>
                  <a href="<?= base_url('online_store/analytics?filter=custom'); ?>" class="btn btn-sm btn-default filter-btn <?= $filter == 'custom' ? 'active btn-primary' : ''; ?>">Custom</a>
                </div>
                <span class="range-badge" style="margin-left:12px;">
                  <i class="fa fa-calendar"></i> <?= htmlspecialchars($range_label); ?>
                </span>
              </div>
              <div class="pull-right">
                <a href="<?= base_url('online_store/export_analytics?filter=' . $filter . '&start=' . $start_date . '&end=' . $end_date); ?>" class="btn btn-sm btn-success" target="_blank">
                  <i class="fa fa-file-pdf-o"></i> Export PDF
                </a>
              </div>
              <div class="clearfix"></div>
            </div>
          </div>
        </div>
      </div>

      <?php if($filter == 'custom'): ?>
      <!-- Custom Date Range Picker -->
      <div class="row" style="margin-bottom:20px;">
        <div class="col-md-12">
          <div class="box box-default">
            <div class="box-body">
              <form method="get" action="<?= base_url('online_store/analytics'); ?>" class="form-inline">
                <input type="hidden" name="filter" value="custom">
                <div class="form-group" style="margin-right:12px;">
                  <label style="margin-right:6px;">Start Date:</label>
                  <input type="date" name="start" class="form-control" value="<?= $start_date; ?>" required>
                </div>
                <div class="form-group" style="margin-right:12px;">
                  <label style="margin-right:6px;">End Date:</label>
                  <input type="date" name="end" class="form-control" value="<?= $end_date; ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Apply</button>
              </form>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>
      <!-- KPI Cards -->
      <div class="row">
        <div class="col-md-2 col-sm-4">
          <div class="analytics-card text-center">
            <div class="analytics-number"><?= number_format($summary['total']); ?></div>
            <div class="analytics-label">Total Visits</div>
          </div>
        </div>
        <div class="col-md-2 col-sm-4">
          <div class="analytics-card text-center">
            <div class="analytics-number"><?= number_format($summary['unique']); ?></div>
            <div class="analytics-label">Unique Sessions</div>
          </div>
        </div>
        <div class="col-md-2 col-sm-4">
          <div class="analytics-card text-center">
            <div class="analytics-number"><?= number_format($summary['today']); ?></div>
            <div class="analytics-label">Today</div>
          </div>
        </div>
        <div class="col-md-2 col-sm-4">
          <div class="analytics-card text-center">
            <div class="analytics-number"><?= number_format($summary['yesterday']); ?></div>
            <div class="analytics-label">Yesterday</div>
          </div>
        </div>
        <div class="col-md-2 col-sm-4">
          <div class="analytics-card text-center">
            <div class="analytics-number" style="color:#10B981;"><?= number_format($summary['new_users']); ?></div>
            <div class="analytics-label">New Users</div>
          </div>
        </div>
        <div class="col-md-2 col-sm-4">
          <div class="analytics-card text-center">
            <div class="analytics-number" style="color:#F59E0B;"><?= number_format($summary['returning_users']); ?></div>
            <div class="analytics-label">Returning Users</div>
          </div>
        </div>
      </div>

      <!-- Bar Chart -->
      <div class="row" style="margin-top:20px;">
        <div class="col-md-12">
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">
                <?= $chart_type == 'hour' ? 'Hourly Visits (Today)' : ($chart_type == 'month' ? 'Monthly Visits (' . htmlspecialchars($range_label) . ')' : 'Daily Visits (' . htmlspecialchars($range_label) . ')'); ?>
              </h3>
            </div>
            <div class="box-body">
              <?php if(!empty($chart_data) && array_sum($chart_data) > 0):
                $maxChart = max($chart_data);
              ?>
              <div class="bar-chart">
                <?php foreach($chart_data as $i => $val):
                  $h = $maxChart > 0 ? round(($val / $maxChart) * 100) : 0;
                  $label = $chart_labels[$i] ?? '';
                ?>
                <div class="bar" style="height:<?= max($h, 2); ?>%;">
                  <span class="bar-value"><?= $val > 0 ? $val : ''; ?></span>
                  <span class="bar-label"><?= $label; ?></span>
                </div>
                <?php endforeach; ?>
              </div>
              <?php else: ?>
              <p class="text-muted text-center" style="padding:40px;">No visit data for this period.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>

      <!-- Heatmap -->
      <div class="row">
        <div class="col-md-12">
          <div class="box box-warning">
            <div class="box-header with-border"><h3 class="box-title">Activity Heatmap</h3></div>
            <div class="box-body table-responsive">
              <?php
                $dowNames = ['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
                $heatmapMatrix = [];
                $heatmapMax = 1;
                foreach($heatmap as $h){
                  $dow = (int)$h->dow; // MySQL DAYOFWEEK: 1=Sun, 7=Sat
                  $hour = (int)$h->hour;
                  $heatmapMatrix[$dow][$hour] = (int)$h->visits;
                  if($h->visits > $heatmapMax) $heatmapMax = $h->visits;
                }
              ?>
              <div class="heatmap-grid" style="min-width:900px;">
                <div class="heatmap-cell label"></div>
                <?php for($h=0; $h<24; $h++): ?>
                <div class="heatmap-cell hour"><?= $h; ?></div>
                <?php endfor; ?>
                <?php for($d=0; $d<7; $d++):
                  $dowIndex = $d + 1; // 1=Sun
                ?>
                <div class="heatmap-cell label"><?= $dowNames[$d]; ?></div>
                <?php for($h=0; $h<24; $h++):
                  $val = $heatmapMatrix[$dowIndex][$h] ?? 0;
                  $intensity = $heatmapMax > 0 ? ($val / $heatmapMax) : 0;
                  $alpha = 0.08 + ($intensity * 0.92);
                  $color = $val > 0 ? 'background:rgba(59,130,246,' . round($alpha, 2) . ');color:#1e40af;font-weight:700;' : 'background:#f8fafc;color:#cbd5e1;';
                ?>
                <div class="heatmap-cell" style="<?= $color; ?>" title="<?= $dowNames[$d] . ' ' . sprintf('%02d:00', $h) . ': ' . $val . ' visits'; ?>"><?= $val > 0 ? $val : '-'; ?></div>
                <?php endfor; ?>
                <?php endfor; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Device Breakdown -->
      <div class="row">
        <div class="col-md-6">
          <div class="box box-danger">
            <div class="box-header with-border"><h3 class="box-title">Device Breakdown</h3></div>
            <div class="box-body text-center">
              <?php
                $deviceColors = ['Desktop'=>'#3B82F6','Mobile'=>'#10B981','Tablet'=>'#F59E0B','Bot/Other'=>'#94A3B8'];
                $totalDevices = array_sum($devices);
                $deviceSegments = [];
                $runningDeg = 0;
                foreach($devices as $name => $count){
                  if($totalDevices > 0){
                    $pct = ($count / $totalDevices) * 100;
                    $deg = ($count / $totalDevices) * 360;
                    $deviceSegments[] = $deviceColors[$name] . ' ' . round($runningDeg, 2) . 'deg ' . round($runningDeg + $deg, 2) . 'deg';
                    $runningDeg += $deg;
                  }
                }
                $pieStyle = $totalDevices > 0 ? 'background: conic-gradient(' . implode(', ', $deviceSegments) . ');' : 'background:#e2e8f0;';
              ?>
              <div class="device-pie" style="<?= $pieStyle; ?>"></div>
              <div class="device-legend">
                <?php foreach($devices as $name => $count):
                  $pct = $totalDevices > 0 ? round(($count / $totalDevices) * 100, 1) : 0;
                ?>
                <div class="device-item"><span class="device-dot" style="background:<?= $deviceColors[$name]; ?>"></span> <?= $name; ?> <strong><?= $pct; ?>%</strong></div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="box box-success">
            <div class="box-header with-border"><h3 class="box-title">Top Sources</h3></div>
            <div class="box-body table-responsive">
              <table class="table table-bordered analytics-table">
                <thead><tr><th>Source</th><th class="text-right">Visits</th></tr></thead>
                <tbody>
                  <?php foreach($top_sources as $s): ?>
                  <tr><td><?= htmlspecialchars($s->source ?: 'Direct'); ?></td><td class="text-right"><?= number_format($s->visits); ?></td></tr>
                  <?php endforeach; ?>
                  <?php if(empty($top_sources)): ?><tr><td colspan="2" class="text-center text-muted">No data yet.</td></tr><?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Top Pages & Search Terms -->
      <div class="row">
        <div class="col-md-6">
          <div class="box box-info">
            <div class="box-header with-border"><h3 class="box-title">Top Pages</h3></div>
            <div class="box-body table-responsive">
              <table class="table table-bordered analytics-table">
                <thead><tr><th>Page</th><th class="text-right">Visits</th></tr></thead>
                <tbody>
                  <?php foreach($top_pages as $p): ?>
                  <tr><td style="max-width:400px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?= htmlspecialchars($p->page_url); ?></td><td class="text-right"><?= number_format($p->visits); ?></td></tr>
                  <?php endforeach; ?>
                  <?php if(empty($top_pages)): ?><tr><td colspan="2" class="text-center text-muted">No data yet.</td></tr><?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="box box-success">
            <div class="box-header with-border"><h3 class="box-title">Search Terms</h3></div>
            <div class="box-body table-responsive">
              <table class="table table-bordered analytics-table">
                <thead><tr><th>Search Term</th><th class="text-right">Visits</th></tr></thead>
                <tbody>
                  <?php foreach($search_terms as $st): ?>
                  <tr><td><?= htmlspecialchars($st->search_term); ?></td><td class="text-right"><?= number_format($st->visits); ?></td></tr>
                  <?php endforeach; ?>
                  <?php if(empty($search_terms)): ?><tr><td colspan="2" class="text-center text-muted">No search data yet.</td></tr><?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Visits -->
      <div class="row">
        <div class="col-md-12">
          <div class="box box-default">
            <div class="box-header with-border"><h3 class="box-title">Recent Visits</h3></div>
            <div class="box-body table-responsive">
              <table class="table table-bordered analytics-table">
                <thead><tr><th>Time</th><th>Page</th><th>Source</th><th>Referrer</th><th>IP</th></tr></thead>
                <tbody>
                  <?php foreach($recent_visits as $v): ?>
                  <tr>
                    <td><?= date('M j, g:i a', strtotime($v->created_at)); ?></td>
                    <td style="max-width:300px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?= htmlspecialchars($v->page_url); ?></td>
                    <td><span class="label label-<?= $v->source == 'Direct' ? 'default' : 'info'; ?>"><?= htmlspecialchars($v->source ?: 'Direct'); ?></span></td>
                    <td style="max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;"><?= htmlspecialchars($v->referrer ?: '-'); ?></td>
                    <td><?= htmlspecialchars($v->ip_address); ?></td>
                  </tr>
                  <?php endforeach; ?>
                  <?php if(empty($recent_visits)): ?><tr><td colspan="5" class="text-center text-muted">No visits recorded yet.</td></tr><?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
  <?php $this->load->view('footer'); ?>
</div>
<?php $this->load->view('comman/code_js.php'); ?>
</body>
</html>
