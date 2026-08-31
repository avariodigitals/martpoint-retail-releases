<?php $CI =& get_instance(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Daily Summary</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
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
    .topbar { display: flex; align-items: center; justify-content: space-between; gap: 10px; margin-bottom: 16px; }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .topbar .back { width: 36px; height: 36px; border-radius: 10px; background: var(--mp-bg); color: var(--mp-ink); display: flex; align-items: center; justify-content: center; text-decoration: none; font-size: 16px; flex-shrink: 0; }

    .period-chip { display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; background: #E0E7FF; color: var(--mp-primary); border-radius: 20px; font-size: 12px; font-weight: 600; margin-bottom: 14px; }

    .date-bar { display: flex; flex-direction: column; gap: 10px; margin-bottom: 14px; }
    .date-presets { display: flex; flex-wrap: nowrap; gap: 6px; }
    .date-presets button { flex: 1; min-width: 0; padding: 8px 4px; border: 1px solid var(--mp-border); border-radius: 10px; background: #fff; color: var(--mp-ink); font-size: 11px; font-weight: 600; white-space: nowrap; }
    .date-presets button.active { background: var(--mp-primary); color: #fff; border-color: var(--mp-primary); }
    .date-range { display: none; flex-direction: column; gap: 8px; }
    .date-range .inputs { display: flex; gap: 8px; }
    .date-range input { width: 100%; padding: 10px 12px; border: 1px solid var(--mp-border); border-radius: 10px; font-size: 14px; background: #fff; }
    .date-range button { width: 100%; padding: 10px 14px; border: none; border-radius: 10px; background: var(--mp-primary); color: #fff; font-size: 13px; font-weight: 600; }

    .actions { display: flex; gap: 8px; margin-bottom: 14px; }
    .actions .btn { flex: 1; padding: 10px 8px; border: none; border-radius: 10px; font-size: 12px; font-weight: 600; color: #fff; display: flex; align-items: center; justify-content: center; gap: 5px; }
    .actions .btn-whatsapp { background: #25D366; }
    .actions .btn-pdf { background: #EF4444; }
    .actions .btn-email { background: #3B82F6; }

    .hero { background: linear-gradient(135deg, var(--mp-primary) 0%, #0044CC 100%); border-radius: 16px; padding: 18px 16px; color: #fff; margin-bottom: 14px; }
    .hero .label { font-size: 12px; opacity: 0.9; margin-bottom: 4px; }
    .hero .value { font-size: 28px; font-weight: 800; margin-bottom: 8px; }
    .hero .sub { font-size: 13px; opacity: 0.9; display: flex; justify-content: space-between; }

    .kpi-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; margin-bottom: 14px; }
    .kpi-card { padding: 16px 12px; border-radius: 14px; background: #fff; border: 1px solid var(--mp-border); }
    .kpi-card.blue { background: #EFF6FF; border-color: #BFDBFE; }
    .kpi-card.green { background: #ECFDF5; border-color: #BBF7D0; }
    .kpi-card.yellow { background: #FFFBEB; border-color: #FDE68A; }
    .kpi-card.red { background: #FEF2F2; border-color: #FECACA; }
    .kpi-card .label { font-size: 11px; color: var(--mp-muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.3px; font-weight: 600; }
    .kpi-card .value { font-size: clamp(16px, 4.5vw, 22px); font-weight: 700; color: var(--mp-ink); line-height: 1.2; word-break: keep-all; overflow-wrap: break-word; }
    .kpi-card .value.success { color: var(--mp-success); }
    .kpi-card .value.danger { color: var(--mp-danger); }
    .kpi-card .value.warning { color: var(--mp-warning); }
    .kpi-card .sub { font-size: 11px; color: var(--mp-muted); margin-top: 4px; }

    .section-title { font-size: 15px; font-weight: 700; margin: 18px 0 10px; color: var(--mp-ink); display: flex; align-items: center; gap: 8px; }
    .card { background: var(--mp-surface); border-radius: 14px; padding: 12px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .card .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
    .card .header .title { font-size: 14px; font-weight: 700; }
    .card .header .meta { font-size: 12px; color: var(--mp-muted); }
    .list-item { display: flex; justify-content: space-between; align-items: center; padding: 10px 0; border-bottom: 1px solid var(--mp-border); }
    .list-item:last-child { border-bottom: none; }
    .list-item .left { flex: 1; min-width: 0; }
    .list-item .title { font-weight: 600; font-size: 14px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .list-item .desc { font-size: 12px; color: var(--mp-muted); margin-top: 2px; }
    .list-item .amount { font-weight: 700; font-size: 14px; text-align: right; }
    .list-item .badge { display: inline-block; padding: 3px 8px; border-radius: 12px; font-size: 11px; font-weight: 600; }
    .badge-success { background: #D1FAE5; color: #059669; }
    .badge-danger { background: #FEF2F2; color: #DC2626; }
    .badge-warning { background: #FFFBEB; color: #B45309; }
    .badge-info { background: #EFF6FF; color: #1E40AF; }

    .top-product { display: flex; align-items: center; gap: 10px; padding: 6px 0; border-bottom: 1px solid var(--mp-border); }
    .top-product:last-child { border-bottom: none; }
    .top-product .rank { width: 24px; height: 24px; border-radius: 50%; background: var(--mp-bg); color: var(--mp-primary); display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; }
    .top-product .name { flex: 1; font-size: 13px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .top-product .qty { font-size: 12px; color: var(--mp-muted); }
    .top-product .amount { font-size: 13px; font-weight: 700; }

    .insight { background: #F0FDF4; border: 1px solid #BBF7D0; color: #166534; border-radius: 10px; padding: 10px 12px; font-size: 13px; margin-bottom: 8px; }
    .insight.warning { background: #FFFBEB; border-color: #FDE68A; color: #92400E; }
    .insight.info { background: #EFF6FF; border-color: #BFDBFE; color: #1E40AF; }

    .empty-state { text-align: center; padding: 40px 20px; color: var(--mp-muted); font-size: 13px; }
    .empty-state i { font-size: 40px; margin-bottom: 12px; display: block; color: #CBD5E1; }

    .alert { padding: 10px 12px; border-radius: 10px; margin-bottom: 10px; font-size: 13px; font-weight: 500; }
    .alert-danger { background: #FEF2F2; color: #B91C1C; }
    .alert-warning { background: #FFFBEB; color: #B45309; }

    .attendance-pill { display: inline-flex; align-items: center; gap: 4px; font-size: 12px; color: var(--mp-muted); }
    .filter-input { width: 100%; padding: 10px 12px; border: 1px solid var(--mp-border); border-radius: 10px; font-size: 13px; margin-bottom: 12px; }

    .btn-sm-primary { display: inline-flex; align-items: center; gap: 4px; padding: 8px 12px; border-radius: 8px; background: var(--mp-primary); color: #fff; text-decoration: none; font-size: 12px; font-weight: 600; }

    @media (min-width: 430px) { .screen { padding: 16px 16px 100px; } }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } }

    /* Print styles */
    @media print {
      .actions, .date-bar, .topbar, .bottom-nav, .mp-mobile-footer, .actions .btn, .back { display: none !important; }
      .screen { padding: 0; }
      .card, .hero { box-shadow: none; border: 1px solid #ddd; }
    }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Daily Summary</h1>
        </div>
      </div>

      <div class="period-chip">
        <i class="fa fa-calendar-o"></i>
        <?= ($selected_date !== $selected_date_to) ? show_date($selected_date) . ' — ' . show_date($selected_date_to) : show_date($selected_date); ?>
      </div>

      <div class="date-bar">
        <div class="date-presets" id="date_presets">
          <button type="button" data-from="<?= date('Y-m-d'); ?>" data-to="<?= date('Y-m-d'); ?>">Today</button>
          <button type="button" data-from="<?= date('Y-m-d', strtotime('-1 day')); ?>" data-to="<?= date('Y-m-d', strtotime('-1 day')); ?>">Yesterday</button>
          <button type="button" data-from="<?= date('Y-m-d', strtotime('-6 days')); ?>" data-to="<?= date('Y-m-d'); ?>">7 Days</button>
          <button type="button" data-from="<?= date('Y-m-d', strtotime('-29 days')); ?>" data-to="<?= date('Y-m-d'); ?>">30 Days</button>
          <button type="button" id="preset_custom" class="active">Custom</button>
        </div>
        <form class="date-range" id="date_range" method="get" action="<?= base_url('dashboard/daily_summary'); ?>">
          <input type="hidden" name="mobile" value="1">
          <div class="inputs">
            <input type="date" name="date_from" value="<?= $selected_date; ?>" max="<?= date('Y-m-d'); ?>">
            <input type="date" name="date_to" value="<?= $selected_date_to; ?>" max="<?= date('Y-m-d'); ?>">
          </div>
          <button type="submit">View Report</button>
        </form>
      </div>

      <div class="actions">
        <button type="button" class="btn btn-whatsapp" onclick="shareToWhatsApp()"><i class="fa fa-whatsapp"></i> Share</button>
        <button type="button" class="btn btn-pdf" onclick="downloadPDF()"><i class="fa fa-file-pdf-o"></i> PDF</button>
        <button type="button" class="btn btn-email" onclick="sendEmail()"><i class="fa fa-envelope"></i> Email</button>
      </div>

      <?php if(!$summary['has_data']): ?>
      <div class="empty-state">
        <i class="fa fa-inbox"></i>
        <h3>No business activity recorded for this period.</h3>
        <p>Select a different date range or come back after making some sales.</p>
      </div>
      <?php else: ?>

      <div class="hero">
        <div class="label">Total Sales</div>
        <div class="value" title="<?= strip_tags(mp_format_money($summary['sales']['total'])); ?>"><?= mp_format_money_compact($summary['sales']['total']); ?></div>
        <div class="sub">
          <span><?= number_format($summary['sales']['transactions']); ?> transactions</span>
          <span><?= $summary['is_range'] ? 'Period total' : 'Today'; ?></span>
        </div>
      </div>

      <div class="kpi-grid">
        <div class="kpi-card green">
          <div class="label">Profit</div>
          <div class="value <?= $summary['profit']['available'] && $summary['profit']['gross_profit'] >= 0 ? 'success' : 'danger'; ?>" title="<?= $summary['profit']['available'] ? strip_tags(mp_format_money($summary['profit']['gross_profit'])) : 'N/A'; ?>">
            <?= $summary['profit']['available'] ? mp_format_money_compact($summary['profit']['gross_profit']) : 'N/A'; ?>
          </div>
          <div class="sub"><?= $summary['profit']['available'] ? $summary['profit']['margin'].'% margin' : ''; ?></div>
        </div>
        <div class="kpi-card red">
          <div class="label">Expenses</div>
          <div class="value danger" title="<?= strip_tags(mp_format_money($summary['expenses']['total'])); ?>"><?= mp_format_money_compact($summary['expenses']['total']); ?></div>
          <div class="sub"><?= $summary['is_range'] ? 'In period' : 'Today'; ?></div>
        </div>
        <div class="kpi-card yellow">
          <div class="label">Net Position</div>
          <div class="value <?= $summary['net_position'] >= 0 ? 'success' : 'danger'; ?>" title="<?= strip_tags(mp_format_money($summary['net_position'])); ?>"><?= mp_format_money_compact($summary['net_position']); ?></div>
          <div class="sub">Sales - Expenses</div>
        </div>
        <div class="kpi-card red">
          <div class="label">Outstanding Debts</div>
          <div class="value danger" title="<?= strip_tags(mp_format_money($summary['outstanding_debts']['total'])); ?>"><?= mp_format_money_compact($summary['outstanding_debts']['total']); ?></div>
          <div class="sub"><?= number_format($summary['outstanding_debts']['count']); ?> owing</div>
        </div>
        <div class="kpi-card blue">
          <div class="label">Cash Expected</div>
          <div class="value" title="<?= strip_tags(mp_format_money($summary['sales']['cash_expected'])); ?>"><?= mp_format_money_compact($summary['sales']['cash_expected']); ?></div>
          <div class="sub">Cash collected</div>
        </div>
        <div class="kpi-card yellow">
          <div class="label">Bank/POS/Transfer</div>
          <div class="value warning" title="<?= strip_tags(mp_format_money($summary['sales']['bank_pos_expected'] ?? 0)); ?>"><?= mp_format_money_compact($summary['sales']['bank_pos_expected'] ?? 0); ?></div>
          <div class="sub">Non-cash payments</div>
        </div>
        <div class="kpi-card green">
          <div class="label">Purchase Due</div>
          <div class="value danger" title="<?= strip_tags(mp_format_money($summary['purchase_due'])); ?>"><?= mp_format_money_compact($summary['purchase_due']); ?></div>
          <div class="sub">Suppliers</div>
        </div>
        <div class="kpi-card blue">
          <div class="label">Transactions</div>
          <div class="value"><?= number_format($summary['sales']['transactions']); ?></div>
          <div class="sub">Completed sales</div>
        </div>
      </div>

      <?php if(count($summary['insights']) > 0): ?>
      <div class="section-title"><i class="fa fa-lightbulb-o" style="color:var(--mp-warning)"></i> Intelligence</div>
      <div class="card" style="padding:10px 12px;">
        <?php foreach($summary['insights'] as $insight): ?>
        <div class="insight"><?= htmlspecialchars($insight); ?></div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if($summary['attendance']['total_staff'] > 0): ?>
      <div class="section-title"><i class="fa fa-users" style="color:var(--mp-success)"></i> Staff Attendance</div>
      <div class="card">
        <div class="header">
          <div class="title">On Duty <?= $summary['is_range'] ? 'Today' : ''; ?></div>
          <div class="meta"><span class="attendance-pill"><i class="fa fa-check-circle" style="color:var(--mp-success)"></i> <?= $summary['attendance']['present']; ?>/<?= $summary['attendance']['total_staff']; ?></span></div>
        </div>
        <?php foreach($summary['attendance']['staff_list'] as $staff): ?>
        <div class="list-item">
          <div class="left">
            <div class="title"><?= htmlspecialchars($staff['name']); ?></div>
            <div class="desc"><?= htmlspecialchars($staff['position']); ?></div>
          </div>
          <div>
            <?php if($staff['status'] === 'Present'): ?>
            <span class="badge badge-success"><i class="fa fa-check"></i> Present</span>
            <?php else: ?>
            <span class="badge badge-danger"><i class="fa fa-times"></i> Absent</span>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <?php if(count($summary['sales']['payment_breakdown']) > 0): ?>
      <div class="section-title"><i class="fa fa-credit-card" style="color:var(--mp-primary)"></i> Payment Breakdown</div>
      <div class="card">
        <?php foreach($summary['sales']['payment_breakdown'] as $pay): ?>
        <div class="list-item">
          <div class="left">
            <div class="title"><?= htmlspecialchars($pay['type']); ?></div>
            <div class="desc"><?= number_format($pay['txn_count']); ?> transaction<?= $pay['txn_count'] != 1 ? 's' : ''; ?><?= $pay['pending_count'] > 0 ? ' · <span style="color:var(--mp-warning);font-weight:600;">' . $pay['pending_count'] . ' pending</span>' : ''; ?></div>
          </div>
          <div style="text-align:right">
            <div class="amount"><?= mp_format_money($pay['amount']); ?></div>
            <span class="badge <?= $pay['affects_cash'] ? 'badge-success' : 'badge-info'; ?>"><?= $pay['affects_cash'] ? 'Cash' : 'Bank'; ?></span>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>

      <div class="section-title"><i class="fa fa-trophy" style="color:var(--mp-warning)"></i> Top Selling Products</div>
      <div class="card">
        <?php if(count($summary['top_products']) > 0): ?>
        <?php $i = 1; foreach($summary['top_products'] as $p): ?>
        <div class="top-product">
          <div class="rank"><?= $i++; ?></div>
          <div class="name"><?= htmlspecialchars($p['name']); ?></div>
          <div class="qty"><?= number_format($p['qty']); ?> sold</div>
          <div class="amount"><?= mp_format_money($p['revenue']); ?></div>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <div class="empty-state" style="padding:20px;">No products sold in this period.</div>
        <?php endif; ?>
      </div>

      <div class="section-title"><i class="fa fa-exclamation-triangle" style="color:var(--mp-danger)"></i> Low Stock Items</div>
      <div class="card">
        <?php if(count($summary['low_stock_items']) > 0): ?>
        <input type="text" class="filter-input" id="low-stock-filter" placeholder="Search low stock items...">
        <?php foreach($summary['low_stock_items'] as $item): ?>
        <div class="list-item low-stock-row">
          <div class="left">
            <div class="title"><?= htmlspecialchars($item['name']); ?></div>
            <div class="desc">Reorder at <?= number_format($item['min']); ?></div>
          </div>
          <div class="amount" style="color:var(--mp-danger)"><?= number_format($item['qty']); ?> left</div>
        </div>
        <?php endforeach; ?>
        <?php else: ?>
        <div class="empty-state" style="padding:20px;">No low stock items. Great job!</div>
        <?php endif; ?>
      </div>

      <?php
      try {
        $CI->load->model('expiry_settings_model');
        $exp_settings = $CI->expiry_settings_model->get_settings();
        $expired_items = $CI->expiry_settings_model->get_expired_items();
        $expiring_items = $CI->expiry_settings_model->get_expiring_items();
        if(count($expired_items) > 0 || count($expiring_items) > 0):
      ?>
      <div class="section-title"><i class="fa fa-calendar-times-o" style="color:var(--mp-danger)"></i> Expiry Alerts</div>
      <div class="card">
        <?php if(count($expired_items) > 0): ?>
        <div class="alert alert-danger"><strong><?= count($expired_items); ?></strong> Expired Items — Blocked from sale</div>
        <?php endif; ?>
        <?php if(count($expiring_items) > 0): ?>
        <div class="alert alert-warning"><strong><?= count($expiring_items); ?></strong> Items Expiring Soon (within <?= $exp_settings->alert_before_days; ?> days)</div>
        <?php endif; ?>
        <a href="<?= base_url('expired_items_report'); ?>" class="btn-sm-primary"><i class="fa fa-eye"></i> View Full Expiry Report</a>
      </div>
      <?php
        endif;
      } catch (Throwable $e) { /* Expiry table not ready yet */ }
      ?>

      <?php endif; ?>

      <div style="text-align:center;margin-top:24px;color:var(--mp-muted);font-size:11px;">
        Generated by MartPoint Retail — Powered by Avario Digitals
      </div>
    </section>
  </div>

  <?php $this->load->view('mobile/bottom_nav', ['active' => 'home']); ?>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    var summaryData = <?= json_encode($summary); ?>;
    var storeName = <?= json_encode($store_name); ?>;
    var selectedDate = <?= json_encode($summary['date_label']); ?>;
    var reportUrl = <?= json_encode(base_url('dashboard/daily_summary?date_from='.$selected_date.'&date_to='.$selected_date_to)); ?>;

    (function(){
      var fromInput = document.querySelector('input[name="date_from"]');
      var toInput = document.querySelector('input[name="date_to"]');
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

    // Low stock live filter
    var lowStockFilter = document.getElementById('low-stock-filter');
    if(lowStockFilter){
      lowStockFilter.addEventListener('input', function(){
        var term = this.value.toLowerCase();
        var rows = this.closest('.card').querySelectorAll('.low-stock-row');
        rows.forEach(function(row){
          var title = row.querySelector('.title');
          var name = title ? title.textContent.toLowerCase() : '';
          row.style.display = name.indexOf(term) > -1 ? '' : 'none';
        });
      });
    }

    function buildReportMessage(){
      var msg = "*MartPoint Business Report*\n\n";
      msg += "Store: " + storeName + "\n";
      msg += "Period: " + selectedDate + "\n\n";
      msg += "*Sales:* " + (summaryData.sales.total ? summaryData.sales.total.toLocaleString() : '0') + "\n";
      msg += "*Profit:* " + (summaryData.profit.available ? summaryData.profit.gross_profit.toLocaleString() : 'Not Available') + "\n";
      msg += "*Expenses:* " + (summaryData.expenses.total ? summaryData.expenses.total.toLocaleString() : '0') + "\n";
      msg += "*Net Position:* " + (summaryData.net_position ? summaryData.net_position.toLocaleString() : '0') + "\n\n";
      msg += "*Transactions:* " + summaryData.sales.transactions + "\n";
      if(summaryData.attendance && summaryData.attendance.total_staff > 0){
        msg += "\n*Staff Attendance:*\n";
        var staffList = summaryData.attendance.staff_list || [];
        for(var s=0; s<staffList.length; s++){
          var st = staffList[s];
          msg += st.name + " (" + st.position + ") — " + st.status + "\n";
        }
        msg += "\n";
      }
      if(summaryData.top_products.length > 0){
        msg += "\n*Best Selling:*\n" + summaryData.top_products[0].name + "\n";
      }
      if(summaryData.low_stock_items.length > 0){
        msg += "\n*Low Stock:*\n";
        var limit = Math.min(5, summaryData.low_stock_items.length);
        for(var i=0; i<limit; i++){
          msg += summaryData.low_stock_items[i].name + " - " + summaryData.low_stock_items[i].qty + " left\n";
        }
      }
      msg += "\n*Outstanding Debts:* " + (summaryData.outstanding_debts.total ? summaryData.outstanding_debts.total.toLocaleString() : '0') + "\n";
      msg += "*Cash Expected:* " + (summaryData.sales.cash_expected ? summaryData.sales.cash_expected.toLocaleString() : '0') + "\n\n";
      msg += "View Full Report:\n" + reportUrl + "\n\n";
      msg += "Powered by MartPoint Retail";
      return msg;
    }

    function shareToWhatsApp(){
      if(!summaryData.has_data){
        if(typeof toastr !== 'undefined') toastr.info('No data to share for this period.');
        return;
      }
      var msg = buildReportMessage();
      if(navigator.clipboard && navigator.clipboard.writeText){
        navigator.clipboard.writeText(msg).then(function(){
          if(typeof toastr !== 'undefined') toastr.success('Report copied! Opening WhatsApp.');
          setTimeout(openWhatsApp, 800);
        }).catch(function(){
          openWhatsApp();
        });
      } else {
        openWhatsApp();
      }
    }

    function openWhatsApp(){
      var isMobile = /iPhone|iPad|iPod|Android/i.test(navigator.userAgent);
      if(isMobile){
        window.location.href = 'https://wa.me/';
      } else {
        window.open('https://web.whatsapp.com/', '_blank');
      }
    }

    function downloadPDF(){
      window.print();
    }

    function sendEmail(){
      if(typeof swal !== 'undefined'){
        swal({
          title: "Send Summary by Email",
          text: "Enter the recipient email address:",
          icon: "info",
          content: "input",
          buttons: true
        }).then(function(email){
          if(!email) return;
          doSendEmail(email);
        });
      } else {
        var email = prompt("Enter email address to send the summary:");
        if(!email) return;
        doSendEmail(email);
      }
    }

    function doSendEmail(email){
      if(!summaryData.has_data){
        if(typeof toastr !== 'undefined') toastr.info('No data to send for this period.');
        return;
      }
      var btn = document.querySelector('.btn-email');
      if(btn) { btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Sending...'; btn.disabled = true; }
      $.ajax({
        type: 'POST',
        url: '<?= base_url('dashboard/send_summary_email'); ?>',
        data: {
          to_email: email,
          date: '<?= $selected_date; ?>',
          date_to: '<?= $selected_date_to; ?>',
          '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
        },
        dataType: 'json',
        success: function(res){
          if(btn) { btn.innerHTML = '<i class="fa fa-envelope"></i> Email'; btn.disabled = false; }
          if(res.status === 'success'){
            if(typeof toastr !== 'undefined') toastr.success('Email sent successfully to ' + email);
          } else {
            mailtoFallback(email);
          }
        },
        error: function(){
          if(btn) { btn.innerHTML = '<i class="fa fa-envelope"></i> Email'; btn.disabled = false; }
          mailtoFallback(email);
        }
      });
    }

    function mailtoFallback(email){
      var subject = "MartPoint Daily Business Summary — " + storeName + " — " + selectedDate;
      var body = "MartPoint Daily Business Summary\n\n";
      body += "Store: " + storeName + "\n";
      body += "Date: " + selectedDate + "\n\n";
      body += "Total Sales: " + (summaryData.sales.total ? summaryData.sales.total.toLocaleString() : '0') + "\n";
      body += "Profit: " + (summaryData.profit.available ? summaryData.profit.gross_profit.toLocaleString() : 'Not Available') + "\n";
      body += "Expenses: " + (summaryData.expenses.total ? summaryData.expenses.total.toLocaleString() : '0') + "\n";
      body += "Net Position: " + (summaryData.net_position ? summaryData.net_position.toLocaleString() : '0') + "\n";
      body += "Transactions: " + summaryData.sales.transactions + "\n";
      if(summaryData.attendance && summaryData.attendance.total_staff > 0){
        body += "\nStaff Attendance:\n";
        var staffList3 = summaryData.attendance.staff_list || [];
        for(var s3=0; s3<staffList3.length; s3++){
          var st3 = staffList3[s3];
          body += "- " + st3.name + " (" + st3.position + ") — " + st3.status + "\n";
        }
        body += "\n";
      }
      body += "Outstanding Debts: " + (summaryData.outstanding_debts.total ? summaryData.outstanding_debts.total.toLocaleString() : '0') + "\n";
      body += "Cash Expected: " + (summaryData.sales.cash_expected ? summaryData.sales.cash_expected.toLocaleString() : '0') + "\n\n";
      body += "View full report: " + reportUrl + "\n\n";
      body += "Powered by MartPoint Retail";
      window.open("mailto:" + encodeURIComponent(email) + "?subject=" + encodeURIComponent(subject) + "&body=" + encodeURIComponent(body), '_blank');
    }
  </script>
</body>
</html>
