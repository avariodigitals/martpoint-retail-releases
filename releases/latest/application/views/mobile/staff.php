<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= $SITE_TITLE ?? 'MartPoint'; ?> — Staff Status</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0; }
    .summary-card { background: linear-gradient(135deg, var(--mp-primary) 0%, var(--mp-primary-dark) 100%); border-radius: 16px; padding: 20px; color: #fff; margin-bottom: 16px; }
    .summary-card .label { font-size: 13px; opacity: 0.9; margin-bottom: 6px; }
    .summary-card .value { font-size: 28px; font-weight: 700; }
    .card { background: #fff; border-radius: 14px; padding: 12px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .staff-item { display: flex; align-items: center; gap: 12px; padding: 12px 0; border-bottom: 1px solid var(--mp-border); }
    .staff-item:last-child { border-bottom: none; }
    .staff-item .avatar { width: 40px; height: 40px; border-radius: 50%; background: #E0E7FF; color: var(--mp-primary); display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; }
    .staff-item .info { flex: 1; }
    .staff-item .name { font-weight: 600; font-size: 15px; }
    .staff-item .role { font-size: 12px; color: var(--mp-muted); }
    .staff-item .status { width: 10px; height: 10px; border-radius: 50%; background: var(--mp-success); }
    .staff-item .status.inactive { background: var(--mp-danger); }
    .empty-state { text-align: center; padding: 40px 20px; color: var(--mp-muted); }
    .insight-card { background: #fff; border-radius: 14px; padding: 16px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .insight-card .title { font-size: 13px; font-weight: 600; color: var(--mp-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.3px; }
    .insight-card .message { font-size: 15px; font-weight: 500; color: var(--mp-text); line-height: 1.4; }
    .insight-card .metric { font-size: 24px; font-weight: 700; color: var(--mp-primary); margin-bottom: 4px; }
    .breakdown-card { background: #fff; border-radius: 14px; padding: 16px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .breakdown-card .title { font-size: 13px; font-weight: 600; color: var(--mp-muted); margin-bottom: 12px; text-transform: uppercase; letter-spacing: 0.3px; }
    .breakdown-card .big-rate { font-size: 40px; font-weight: 700; color: var(--mp-text); margin-bottom: 16px; line-height: 1; }
    .breakdown-card .big-rate .sub { font-size: 15px; font-weight: 500; color: var(--mp-muted); margin-left: 6px; }
    .bar-track { display: flex; height: 32px; border-radius: 14px; overflow: hidden; background: var(--mp-bg); margin-bottom: 16px; }
    .bar-segment { display: flex; align-items: center; justify-content: center; color: #fff; font-size: 12px; font-weight: 700; min-width: 0; }
    .bar-segment.on-time { background: var(--mp-success); }
    .bar-segment.late { background: var(--mp-warning); }
    .bar-segment.absent { background: #E2E8F0; color: var(--mp-muted); }
    .legend { display: flex; gap: 16px; flex-wrap: wrap; font-size: 13px; color: var(--mp-muted); }
    .legend span { display: inline-flex; align-items: center; }
    .legend .dot { width: 8px; height: 8px; border-radius: 50%; margin-right: 6px; }
    .bottom-nav { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 430px; background: #fff; border-top: 1px solid var(--mp-border); display: flex; justify-content: space-around; padding: 8px 0 calc(8px + var(--safe-bottom)); z-index: 1000; }
    .nav-item { display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 6px 14px; border: none; background: transparent; color: var(--mp-muted); font-size: 10px; font-weight: 500; text-decoration: none; }
    .nav-item .icon { font-size: 20px; }
    .nav-item.active { color: var(--mp-primary); }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .bottom-nav { max-width: 100%; left: 0; right: 0; transform: none; } .screen { padding: 16px 16px 100px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 120px; } }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Staff Status</h1>
        </div>
      </div>

      <div class="summary-card">
        <div class="label">Clocked In Today</div>
        <div class="value"><?= count($clocked_in); ?> / <?= count($staff_list); ?></div>
      </div>

      <?php
      $total_staff = count($staff_list);
      $clocked_count = count($clocked_in);
      $rate = $total_staff > 0 ? round(($clocked_count / $total_staff) * 100) : 0;
      if ($rate == 100) {
          $insight = "Full house. All staff are on deck today.";
      } elseif ($rate >= 75) {
          $insight = "Strong attendance. Most of your team is on shift.";
      } elseif ($rate >= 50) {
          $insight = "Half the team is in. Plan coverage accordingly.";
      } elseif ($rate > 0) {
          $insight = "Light crew today. Consider checking who is scheduled.";
      } else {
          $insight = "No one has clocked in yet today.";
      }
      ?>
      <div class="insight-card">
        <div class="title">Shift Insight</div>
        <div class="metric"><?= $rate; ?>%</div>
        <div class="message"><?= $insight; ?></div>
      </div>

      <?php
      $summary = $attendance_summary ?? [];
      $total = $summary['total_staff'] ?? 0;
      $on_time_count = $summary['on_time'] ?? 0;
      $late_count = $summary['late'] ?? 0;
      $absent_count = $total - count($clocked_in);
      $on_time_pct = $total > 0 ? round(($on_time_count / $total) * 100) : 0;
      $late_pct = $total > 0 ? round(($late_count / $total) * 100) : 0;
      $absent_pct = $total > 0 ? round(($absent_count / $total) * 100) : 0;
      ?>
      <div class="breakdown-card">
        <div class="title">Attendance Quality</div>
        <div class="big-rate"><?= $on_time_pct; ?>%<span class="sub">on time</span></div>
        <div class="bar-track">
          <div class="bar-segment on-time" style="width: <?= $on_time_pct; ?>%;"></div>
          <div class="bar-segment late" style="width: <?= $late_pct; ?>%;"></div>
          <div class="bar-segment absent" style="width: <?= $absent_pct; ?>%;"></div>
        </div>
        <div class="legend">
          <span><i class="dot" style="background: var(--mp-success);"></i> On time <?= $on_time_count; ?></span>
          <span><i class="dot" style="background: var(--mp-warning);"></i> Late <?= $late_count; ?></span>
          <span><i class="dot" style="background: #E2E8F0;"></i> Not in <?= $absent_count; ?></span>
        </div>
      </div>

      <div class="card">
        <?php if(!empty($staff_list)): ?>
          <?php foreach($staff_list as $s): ?>
            <?php $is_clocked = in_array($s->id, $clocked_in); ?>
            <div class="staff-item">
              <div class="avatar"><?= strtoupper(substr(($s->first_name ?: $s->username),0,1)); ?></div>
              <div class="info">
                <div class="name"><?= trim(($s->first_name ?: '') . ' ' . ($s->last_name ?: '')) ?: $s->username; ?></div>
                <div class="role"><?= $s->role_name; ?></div>
              </div>
              <div class="status <?= $is_clocked ? '' : 'inactive'; ?>"></div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-state">No staff found.</div>
        <?php endif; ?>
      </div>
    </section>


  </div>
  <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
