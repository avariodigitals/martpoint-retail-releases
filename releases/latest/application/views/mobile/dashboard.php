<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Dashboard</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/assist.css?v=7">
  <style>
    :root {
      --mp-primary: #0057FF;
      --mp-primary-dark: #0044CC;
      --mp-bg: #F1F5F9;
      --mp-surface: #FFFFFF;
      --mp-text: #0F172A;
      --mp-muted: #64748B;
      --mp-border: #E2E8F0;
      --mp-success: #10B981;
      --mp-danger: #EF4444;
      --mp-warning: #F59E0B;
      --mp-ink: #1E293B;
      --safe-bottom: env(safe-area-inset-bottom, 0px);
    }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0; }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .greeting { font-size: 13px; color: var(--mp-muted); margin-bottom: 10px; }
    .date-section { font-size: 14px; font-weight: 600; color: var(--mp-ink); margin-bottom: 10px; }
    .date-bar { display: flex; flex-direction: column; gap: 10px; margin-bottom: 12px; margin-top: 16px; }
    .date-presets { display: flex; flex-wrap: nowrap; gap: 6px; }
    .date-presets button { flex: 1; min-width: 0; padding: 8px 6px; border: 1px solid var(--mp-border); border-radius: 12px; background: #fff; color: var(--mp-ink); font-size: 11px; font-weight: 600; white-space: nowrap; }
    .date-presets button.active { background: var(--mp-primary); color: #fff; border-color: var(--mp-primary); }
    .date-range { display: none; flex-direction: column; gap: 8px; }
    .date-range input { width: 100%; padding: 10px 12px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 14px; background: #fff; }
    .date-range button { width: 100%; padding: 10px 14px; border: none; border-radius: 12px; background: var(--mp-primary); color: #fff; font-size: 13px; font-weight: 600; }
    .clock-btn { display: inline-flex; align-items: center; gap: 6px; padding: 8px 14px; border-radius: 12px; background: var(--mp-success); color: #fff; font-size: 13px; font-weight: 600; text-decoration: none; }
    .clock-btn.out { background: var(--mp-danger); color: #fff; }
    .alert { padding: 10px 14px; border-radius: 12px; margin-bottom: 12px; font-size: 13px; font-weight: 500; }
    .alert-success { background: #ECFDF5; color: #065F46; }
    .alert-danger { background: #FEF2F2; color: #B91C1C; }
    .avatar { width: 36px; height: 36px; border-radius: 50%; background: #E0E7FF; color: var(--mp-primary); display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 13px; overflow: hidden; text-decoration: none; }
    .avatar img { width: 100%; height: 100%; object-fit: cover; }
    .mp-avatar-wrap { margin-left: 16px; }
    .hero-cta { display: flex; align-items: center; gap: 12px; padding: 14px 14px; background: var(--mp-primary); border-radius: 16px; color: #fff; margin-bottom: 12px; text-decoration: none; }
    .hero-cta .icon { width: 40px; height: 40px; border-radius: 12px; background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center; font-size: 18px; }
    .hero-cta .text { flex: 1; }
    .hero-cta .title { font-size: 16px; font-weight: 700; margin: 0; }
    .hero-cta .sub { font-size: 12px; opacity: 0.9; }
    .hero-cta .arrow { color: #fff; }
    .kpi-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px; }
    .kpi-card { padding: 18px 14px; border-radius: 16px; text-align: left; }
    .kpi-card.blue { background: #EFF6FF; }
    .kpi-card.green { background: #ECFDF5; }
    .kpi-card.yellow { background: #FFFBEB; }
    .kpi-card.red { background: #FEF2F2; }
    .kpi-card .label { font-size: 11px; color: var(--mp-muted); margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.3px; font-weight: 600; }
    .kpi-card .value { font-size: clamp(16px, 4.8vw, 24px); font-weight: 700; color: var(--mp-ink); line-height: 1.15; word-break: keep-all; overflow-wrap: break-word; }
    .insights { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 12px; }
    .insight { padding: 18px 14px; background: #fff; border: 1px solid var(--mp-border); border-radius: 16px; text-decoration: none; color: var(--mp-ink); text-align: left; position: relative; }
    .insight .label { font-size: 11px; color: var(--mp-muted); margin-bottom: 8px; text-transform: uppercase; font-weight: 600; }
    .insight .value { font-size: clamp(16px, 4.8vw, 22px); font-weight: 700; color: var(--mp-ink); line-height: 1.15; word-break: keep-all; overflow-wrap: break-word; }
    .insight .value.danger { color: var(--mp-danger); }
    .insight .value.success { color: var(--mp-success); }
    .insight .value.warning { color: var(--mp-warning); }
    .badge { position: absolute; top: -5px; right: -5px; min-width: 18px; height: 18px; border-radius: 9px; background: var(--mp-danger); color: #fff; font-size: 10px; font-weight: 700; display: flex; align-items: center; justify-content: center; padding: 0 5px; }
    .quick-actions { display: grid; grid-template-columns: repeat(4, 1fr); gap: 10px; margin-bottom: 12px; }
    .quick-action { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; padding: 12px 6px; border-radius: 12px; background: #fff; border: 1px solid var(--mp-border); text-decoration: none; color: var(--mp-ink); font-size: 11px; font-weight: 500; }
    .quick-action .icon { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 16px; }
    .quick-action.blue .icon { background: #DBEAFE; color: #2563EB; }
    .quick-action.green .icon { background: #D1FAE5; color: #059669; }
    .quick-action.purple .icon { background: #F3E8FF; color: #7C3AED; }
    .quick-action.orange .icon { background: #FFEDD5; color: #EA580C; }
    .quick-action.teal .icon { background: #CCFBF1; color: #0F766E; }
    .quick-action.extra { display: none; }
    @media (min-width: 430px) { .quick-action.extra { display: flex; } }
    .target-card { background: #fff; border: 1px solid var(--mp-border); border-radius: 14px; padding: 12px; margin-bottom: 12px; }
    .target-card .header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; font-size: 13px; }
    .target-card .title { font-weight: 600; }
    .target-card .value { color: var(--mp-muted); }
    .progress-track { height: 8px; background: var(--mp-bg); border-radius: 4px; overflow: hidden; }
    .progress-fill { height: 100%; background: var(--mp-primary); border-radius: 4px; transition: width 0.5s ease; }
    .progress-fill.behind { background: var(--mp-warning); }
    .progress-fill.meet { background: var(--mp-primary); }
    .progress-fill.surpass { background: var(--mp-success); }
    .section-title { font-size: 15px; font-weight: 700; margin: 14px 0 8px; }
    .intelligence-card { background: #fff; border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; margin-bottom: 12px; }
    .intelligence-card ul { margin: 0; padding-left: 18px; font-size: 13px; color: var(--mp-ink); line-height: 1.6; }
    .intelligence-card li { margin-bottom: 6px; }
    .intelligence-card li:last-child { margin-bottom: 0; }
    .card { background: var(--mp-surface); border-radius: 14px; padding: 12px; margin-bottom: 10px; border: 1px solid var(--mp-border); }
    .list-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--mp-border); }
    .list-item:last-child { border-bottom: none; }
    .list-item .title { font-weight: 600; font-size: 14px; }
    .list-item .desc { font-size: 12px; color: var(--mp-muted); margin-top: 2px; }
    .list-item .amount { font-weight: 700; font-size: 14px; }
    .top-product { display: flex; align-items: center; gap: 10px; padding: 6px 0; border-bottom: 1px solid var(--mp-border); }
    .top-product:last-child { border-bottom: none; }
    .top-product .rank { width: 24px; height: 24px; border-radius: 50%; background: var(--mp-bg); color: var(--mp-primary); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; }
    .top-product .name { flex: 1; font-size: 13px; }
    .top-product .qty { font-size: 12px; color: var(--mp-muted); }
    .status-badge { display: inline-block; padding: 3px 8px; border-radius: 20px; font-size: 11px; font-weight: 600; }
    .status-paid { background: #D1FAE5; color: #059669; }
    .status-due { background: #FEF2F2; color: #DC2626; }
    .bottom-nav { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 430px; background: #fff; border-top: 1px solid var(--mp-border); display: flex; justify-content: space-around; padding: 8px 0 calc(8px + var(--safe-bottom)); z-index: 1000; }
    .nav-item { display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 6px 14px; border: none; background: transparent; color: var(--mp-muted); font-size: 10px; font-weight: 500; text-decoration: none; }
    .nav-item .icon { font-size: 20px; }
    .nav-item.active { color: var(--mp-primary); }
    .fab { position: fixed; bottom: calc(90px + var(--safe-bottom)); right: 12px; width: 50px; height: 50px; border-radius: 50%; background: var(--mp-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 22px; box-shadow: 0 4px 12px rgba(0,87,255,0.35); z-index: 310; text-decoration: none; }
    .empty-state { text-align: center; padding: 24px; color: var(--mp-muted); font-size: 13px; }
    @media (min-width: 430px) { .screen { padding: 16px 16px 100px; } .quick-actions { grid-template-columns: repeat(5, 1fr); } }
    @media (max-width: 430px) { .screen { padding: 10px 10px 100px; } .topbar h1 { font-size: 18px; } .greeting { font-size: 12px; } .hero-cta { padding: 12px 12px; } .hero-cta .title { font-size: 15px; } .kpi-card, .insight { padding: 14px 12px; } .quick-action .icon { width: 32px; height: 32px; font-size: 14px; } .quick-action span { font-size: 10px; } .date-presets { flex-wrap: wrap; } .date-presets button { font-size: 10px; padding: 7px 4px; border-radius: 10px; } .clock-btn { padding: 6px 10px; font-size: 12px; } .avatar { width: 34px; height: 34px; } .fab { right: auto; left: 12px; bottom: calc(100px + var(--safe-bottom)); } }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .bottom-nav { max-width: 100%; left: 0; right: 0; transform: none; } .kpi-grid, .insights { grid-template-columns: repeat(2, 1fr); } .quick-actions { grid-template-columns: repeat(5, 1fr); } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 120px; } .kpi-card .value { font-size: 26px !important; font-weight: 800 !important; } .insight .value { font-size: 24px !important; font-weight: 800 !important; } }
    @media (orientation: landscape) and (min-width: 600px) {
      #app { max-width: 100%; margin: 0; box-shadow: none; }
      .screen { column-count: 2; column-gap: 16px; padding: 16px 16px 120px; }
      .screen > .topbar, .screen > .date-bar, .screen > .hero-cta, .screen > .quick-actions, .screen > .kpi-grid, .screen > .target-card, .screen > .card, .screen > .insights, .screen > .section-title { break-inside: avoid; -webkit-column-break-inside: avoid; }
      .topbar, .date-bar, .hero-cta, .quick-actions { column-span: all; }
      .quick-actions { grid-template-columns: repeat(5, 1fr); }
      .kpi-grid, .insights { column-span: all; grid-template-columns: repeat(4, 1fr); }
      .date-bar { justify-content: flex-end; }
      .section-title { break-after: avoid; -webkit-column-break-after: avoid; }
      .bottom-nav { max-width: 100%; left: 0; right: 0; transform: none; }
      .fab { right: 24px; }
    }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <?php
        // Avatar should only be the user's profile picture; never the store logo
        $avatar_src = (!empty($profile_picture) && file_exists(FCPATH . $profile_picture)) ? $profile_picture : '';
      ?>
      <div class="topbar">
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Dashboard</h1>
          <div class="greeting"><?= $greeting; ?>, <?= $display_name; ?></div>
        </div>
        <a href="<?= base_url('mobile/clock'); ?>" class="clock-btn <?= $needs_clock_out ? 'out' : ''; ?>">
          <i class="fa <?= $needs_clock_out ? 'fa-sign-out' : 'fa-sign-in'; ?>"></i>
          <?= $needs_clock_out ? 'Clock Out' : 'Clock In'; ?>
        </a>
        <div class="mp-avatar-wrap">
          <button type="button" class="avatar mp-avatar-trigger" aria-haspopup="true" aria-label="Open account menu" style="color:var(--mp-primary);">
            <?php if(!empty($avatar_src)): ?>
              <img src="<?= base_url($avatar_src); ?>" alt="Profile">
            <?php else: ?>
              <?= strtoupper(substr($display_name,0,1)); ?>
            <?php endif; ?>
          </button>
          <div class="mp-avatar-menu">
            <a href="<?= base_url('mobile/profile'); ?>" class="mp-avatar-item">
              <i class="fa fa-user"></i>
              <span>My Profile</span>
            </a>
            <a href="<?= base_url('logout'); ?>" class="mp-avatar-item logout" onclick="return mpLogout(this, event);">
              <i class="fa fa-sign-out"></i>
              <span>Log Out</span>
            </a>
          </div>
        </div>
      </div>

      <?php if($this->session->flashdata('success')): ?>
        <?php $flash_success = trim(strip_tags(str_ireplace(['</p>','<br>','<br/>','<br />'], [' ',' ',' ',' '], $this->session->flashdata('success')))); ?>
        <script>if(!window.mpFlashMessages) window.mpFlashMessages = []; window.mpFlashMessages.push({msg: <?= json_encode($flash_success, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>, type: 'success'});</script>
      <?php endif; ?>
      <?php if($this->session->flashdata('failed')): ?>
        <?php $flash_failed = trim(strip_tags(str_ireplace(['</p>','<br>','<br/>','<br />'], [' ',' ',' ',' '], $this->session->flashdata('failed')))); ?>
        <script>if(!window.mpFlashMessages) window.mpFlashMessages = []; window.mpFlashMessages.push({msg: <?= json_encode($flash_failed, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>, type: 'danger'});</script>
      <?php endif; ?>

      <div class="date-section">Select a date range</div>
      <form class="date-bar" method="get" action="<?= base_url('mobile'); ?>">
        <div class="date-presets" id="date_presets">
          <button type="button" data-from="<?= date('Y-m-d'); ?>" data-to="<?= date('Y-m-d'); ?>">Today</button>
          <button type="button" data-from="<?= date('Y-m-d', strtotime('-1 day')); ?>" data-to="<?= date('Y-m-d', strtotime('-1 day')); ?>">Yesterday</button>
          <button type="button" data-from="<?= date('Y-m-d', strtotime('-6 days')); ?>" data-to="<?= date('Y-m-d'); ?>">7 Days</button>
          <button type="button" data-from="<?= date('Y-m-d', strtotime('-29 days')); ?>" data-to="<?= date('Y-m-d'); ?>">30 Days</button>
          <button type="button" id="preset_custom" class="active">Custom</button>
        </div>
        <div class="date-range" id="date_range">
          <input type="date" name="from" value="<?= $from; ?>" max="<?= date('Y-m-d'); ?>" class="form-control" style="padding:10px 12px; border-radius:12px; border:1px solid var(--mp-border); font-size:14px; flex:1;">
          <input type="date" name="to" value="<?= $to; ?>" max="<?= date('Y-m-d'); ?>" class="form-control" style="padding:10px 12px; border-radius:12px; border:1px solid var(--mp-border); font-size:14px; flex:1;">
          <button type="submit" style="padding:10px 16px; border:none; border-radius:12px; background:var(--mp-primary); color:#fff; font-weight:600; white-space:nowrap;">Go</button>
        </div>
      </form>

      <a href="<?= base_url('mobile/sale'); ?>" class="hero-cta">
        <div class="icon"><i class="fa fa-shopping-cart"></i></div>
        <div class="text">
          <div class="title">Start New Sale</div>
          <div class="sub">Quick scan, pay &amp; print</div>
        </div>
        <div class="arrow"><i class="fa fa-chevron-right"></i></div>
      </a>

      <div class="quick-actions">
        <a href="<?= base_url('mobile/pos'); ?>" class="quick-action blue">
          <div class="icon"><i class="fa fa-calculator"></i></div>
          <span>POS</span>
        </a>
        <a href="<?= base_url('mobile/sale'); ?>" class="quick-action green">
          <div class="icon"><i class="fa fa-file-text-o"></i></div>
          <span>Sale</span>
        </a>
        <a href="<?= base_url('mobile/customers'); ?>" class="quick-action purple">
          <div class="icon"><i class="fa fa-users"></i></div>
          <span>Customers</span>
        </a>
        <a href="<?= base_url('mobile/stock'); ?>" class="quick-action orange">
          <div class="icon"><i class="fa fa-cubes"></i></div>
          <span>Stock</span>
        </a>
        <a href="<?= base_url('mobile/product'); ?>" class="quick-action teal extra">
          <div class="icon"><i class="fa fa-plus-circle"></i></div>
          <span>Add Product</span>
        </a>
      </div>

      <div class="kpi-grid">
        <div class="kpi-card blue">
          <div class="label">Sales</div>
          <div class="value" title="<?= strip_tags(mp_format_money(parse_amount(strip_tags($sales)))); ?>"><?= mp_format_money_compact(parse_amount(strip_tags($sales))); ?></div>
        </div>
        <div class="kpi-card green">
          <div class="label">Expenses</div>
          <div class="value" title="<?= strip_tags(mp_format_money(parse_amount(strip_tags($expenses)))); ?>"><?= mp_format_money_compact(parse_amount(strip_tags($expenses))); ?></div>
        </div>
        <div class="kpi-card yellow">
          <div class="label">Txns</div>
          <div class="value"><?= $transactions; ?></div>
        </div>
        <div class="kpi-card red">
          <div class="label">Debt</div>
          <div class="value" title="<?= strip_tags(mp_format_money(parse_amount(strip_tags($debt)))); ?>"><?= mp_format_money_compact(parse_amount(strip_tags($debt))); ?></div>
        </div>
      </div>

      <div class="target-card">
        <div class="header">
          <div class="title"><?= $target_label; ?></div>
          <div class="value" title="<?= strip_tags(mp_format_money($target)); ?>"><?= mp_format_money_compact(parse_amount(strip_tags($sales))); ?> / <?= mp_format_money_compact($target); ?></div>
        </div>
        <div class="progress-track">
          <div class="progress-fill <?= $target_status; ?>" style="width:<?= $target_progress; ?>%"></div>
        </div>
      </div>

      <a href="<?= base_url('dashboard/daily_summary?date_from=' . ($from ?? date('Y-m-d')) . '&date_to=' . ($to ?? date('Y-m-d')) . '&mobile=1'); ?>" style="display:block;text-decoration:none;">
      <div class="card" style="background:var(--mp-success); color:#fff;">
        <div class="header" style="color:#fff;">
          <div class="title" style="display:flex;align-items:center;gap:8px;">Today's Profit <i class="fa fa-chevron-right" style="font-size:12px;opacity:0.8;"></i></div>
          <div class="value" style="color:#fff; font-size:20px; font-weight:800; margin-left:auto; text-align:right;" title="<?= strip_tags(mp_format_money(parse_amount(strip_tags($profit)))); ?>"><?= mp_format_money_compact(parse_amount(strip_tags($profit))); ?></div>
        </div>
      </div>
      </a>

      <?php if(!empty($insights)): ?>
      <div class="intelligence-card">
        <div class="section-title" style="margin:0 0 10px;">Intelligence Report</div>
        <ul>
          <?php foreach($insights as $insight): ?>
            <li><?= $insight; ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <div class="insights">
        <a href="<?= base_url('mobile/due'); ?>" class="insight">
          <div class="label">Unpaid</div>
          <div class="value danger"><?= $unpaid_count; ?></div>
          <?php if($unpaid_count > 0): ?><div class="badge"><?= $unpaid_count; ?></div><?php endif; ?>
        </a>
        <a href="<?= base_url('mobile/stock'); ?>" class="insight">
          <div class="label">Low Stock</div>
          <div class="value warning"><?= $low_stock; ?></div>
          <?php if($low_stock > 0): ?><div class="badge"><?= $low_stock; ?></div><?php endif; ?>
        </a>
        <a href="<?= base_url('reports/cash_in_hand'); ?>" class="insight">
          <div class="label">Cash</div>
          <div class="value success" title="<?= strip_tags(mp_format_money(parse_amount(strip_tags($cash_in_hand)))); ?>"><?= mp_format_money_compact(parse_amount(strip_tags($cash_in_hand))); ?></div>
        </a>
        <a href="<?= base_url('mobile/staff'); ?>" class="insight">
          <div class="label">On Duty</div>
          <div class="value" style="color:var(--mp-primary)"><?= $attendance_count; ?>/<?= $staff_count; ?></div>
        </a>
      </div>

      <div class="section-title">Top Products Today</div>
      <div class="card">
        <?php if(!empty($top_products)): ?>
          <?php $i = 1; foreach($top_products as $p): ?>
            <div class="top-product">
              <div class="rank"><?= $i++; ?></div>
              <div class="name"><?= $p->item_name; ?></div>
              <div class="qty"><?= $p->qty; ?> sold</div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-state">No sales yet today</div>
        <?php endif; ?>
      </div>

      <?php if(!empty($upcoming_due)): ?>
      <div class="section-title">Upcoming Due (7 days)</div>
      <div class="card">
        <?php foreach($upcoming_due as $row): ?>
          <div class="list-item">
            <div>
              <div class="title"><?= !empty($row->customer_name) ? $row->customer_name : 'Walk-in Customer'; ?></div>
              <div class="desc"><?= $row->sales_code; ?> · <?= date('M j', strtotime($row->due_date)); ?></div>
            </div>
            <div style="text-align:right">
              <div class="amount"><?= mp_format_money($row->due); ?></div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <div class="section-title">Today’s Sales</div>
      <div class="card">
        <?php if(!empty($recent_sales)): ?>
          <?php foreach($recent_sales as $row): ?>
            <div class="list-item">
              <div>
                <div class="title"><?= !empty($row->customer_name) ? $row->customer_name : 'Walk-in Customer'; ?></div>
                <div class="desc"><?= $row->sales_code; ?> · <?= $row->created_time; ?></div>
              </div>
              <div style="text-align:right">
                <div class="amount"><?= mp_format_money($row->grand_total); ?></div>
                <span class="status-badge <?= ($row->due > 0) ? 'status-due' : 'status-paid'; ?>">
                  <?= ($row->due > 0) ? 'Due' : 'Paid'; ?>
                </span>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-state">No sales today. Tap POS or Sale to create one.</div>
        <?php endif; ?>
      </div>
    </section>

    <a href="<?= base_url('mobile/sale'); ?>" class="fab">+</a>


  </div>

  <?php // MartPoint Assist loaded via mobile/chat at bottom ?>
  <script>
    (function(){
      var fromInput = document.querySelector('input[name="from"]');
      var toInput = document.querySelector('input[name="to"]');
      var range = document.getElementById('date_range');
      var customBtn = document.getElementById('preset_custom');
      var btns = document.querySelectorAll('.date-presets button:not(#preset_custom)');
      var currentFrom = fromInput.value;
      var currentTo = toInput.value;
      var activePreset = false;

      btns.forEach(function(btn){
        if(btn.dataset.from === currentFrom && btn.dataset.to === currentTo){
          btn.classList.add('active');
          customBtn.classList.remove('active');
          activePreset = true;
        }
        btn.addEventListener('click', function(){
          fromInput.value = this.dataset.from;
          toInput.value = this.dataset.to;
          range.style.display = 'none';
          btns.forEach(function(b){ b.classList.remove('active'); });
          customBtn.classList.remove('active');
          this.classList.add('active');
          this.closest('form').submit();
        });
      });

      if(!activePreset){
        customBtn.classList.add('active');
        range.style.display = 'flex';
      } else {
        range.style.display = 'none';
      }

      customBtn.addEventListener('click', function(){
        btns.forEach(function(b){ b.classList.remove('active'); });
        this.classList.add('active');
        range.style.display = 'flex';
      });
    })();
  </script>
  <?php $this->load->view('mobile/bottom_nav', ['active' => 'home']); ?>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
