<?php
/* Dashboard view — content fragment for mp_layout */
$this->load->view('admin/desktop/_styles');
?>


    <?php
    $CI =& get_instance();
    $is_product_business = true;
    $is_service_business = false;
    $industry_type = 'general_retail';
    try {
      $bp_profile = mp_get_store_profile();
      $is_product_business = empty($bp_profile['business_model']) || in_array($bp_profile['business_model'], ['product_based','product_and_service']);
      $is_service_business = in_array($bp_profile['business_model'] ?? '', ['service_based','product_and_service']);
      $industry_type = $bp_profile['industry_type'] ?? 'general_retail';
    } catch (Exception $e) {}
    $item_label = mp_label('item','Product');
    $customer_label = mp_label('customer','Customer');
    $branch_label = mp_label('branch','Branch');

    // Dashboard card counts
    $store_id = get_current_store_id();
    $today = date('Y-m-d');
    // Range-aware invoice + new-customer counts (respond to the date filter)
    $invoices_range = $invoices_range ?? ['count'=>0, 'previous'=>0, 'change'=>0];
    $new_customers_range = $new_customers_range ?? ['count'=>0, 'previous'=>0, 'change'=>0];

    // Range-aware sales target (daily goal × days in selected range)
    $range_target_days = 1;
    switch($range){
      case '7Days': $range_target_days = 7; break;
      case '30Days': $range_target_days = 30; break;
      case 'LastMonth': $range_target_days = date('t', strtotime('-1 month')); break;
      case 'ThisMonth': $range_target_days = date('t'); break;
      case 'ThisYear': $range_target_days = date('L') ? 366 : 365; break;
    }
    $range_target = $daily_target * $range_target_days;
    $range_sales = $today_sales['today'] ?? 0;
    $range_progress = ($range_target > 0) ? min(100, round(($range_sales / $range_target) * 100, 1)) : 0;
    ?>

    <!-- Subscription Warning Banner -->
    <?php if(!special_access() && isset($subscription_status) && $subscription_status['status'] !== 'ACTIVE' && $subscription_status['status'] !== 'NOT_ACTIVATED'): ?>
    <div class="mp-sub-banner alert alert-<?= ($subscription_status['status'] === 'SUSPENDED') ? 'danger' : 'warning'; ?> alert-dismissible">
      <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
      <h4><i class="fa fa-<?= ($subscription_status['status'] === 'SUSPENDED') ? 'ban' : 'warning'; ?>"></i> <?= ($subscription_status['status'] === 'SUSPENDED') ? 'Subscription Suspended' : 'Subscription Expiring'; ?></h4>
      <?php if($subscription_status['status'] === 'SUSPENDED'): ?>
        Your subscription has been suspended. Some features may be limited. Contact admin for support.
      <?php elseif($subscription_status['status'] === 'EXPIRED'): ?>
        Your subscription has expired. Please contact admin to renew.
      <?php else: ?>
        Your subscription expires on <?=show_date($subscription_status['end_date']);?> (<?=$subscription_status['days_left'];?> days left). Contact admin to renew.
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Clock-in banner -->
    <?php if(!is_user() && !empty($needs_clock_in)): ?>
    <div class="mp-section">
      <div id="dashClockStatusCard" class="mp-clock-banner">
        <div class="mp-clock-icon <?= (!empty($needs_clock_in)) ? '' : 'ok' ?>">
          <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        </div>
        <div class="mp-clock-body">
          <h4 class="mp-clock-title" style="font-size:14px;color:<?= (!empty($needs_clock_in)) ? '#92400E' : '#065F46'; ?>;"><?= (!empty($needs_clock_in)) ? 'You are not clocked in.' : 'You are clocked in.'; ?></h4>
          <div class="mp-clock-sub"><?= (!empty($needs_clock_in)) ? 'Clock in before processing sales.' : 'Remember to clock out at the end of your shift.'; ?></div>
        </div>
        <button type="button" class="mp-clock-btn <?= (!empty($needs_clock_in)) ? 'in' : 'out'; ?>" onclick="$('#appClockInBtn').trigger('click');">
          <?= (!empty($needs_clock_in)) ? 'Clock In' : 'Clock Out'; ?>
        </button>
      </div>
    </div>
    <?php endif; ?>

    <!-- Quick actions + branch selector -->
    <div class="mp-section">
      <div style="display:flex;flex-direction:row;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;width:100%;box-sizing:border-box;">
        <div class="mp-quick-actions">
          <a href="<?=base_url('pos');?>" class="mp-qa-btn green"><i class="fa fa-shopping-cart"></i> New Sale</a>
          <a href="<?=base_url('customers/add');?>" class="mp-qa-btn blue"><i class="fa fa-user-plus"></i> Add <?= $customer_label; ?></a>
          <a href="<?=base_url('expense/add');?>" class="mp-qa-btn orange"><i class="fa fa-minus-square"></i> Add Expense</a>
          <?php if($is_product_business): ?><a href="<?=base_url('purchase/add');?>" class="mp-qa-btn purple"><i class="fa fa-plus-square"></i> Purchase Stock</a><?php endif; ?>
          <?php if($is_service_business): ?><a href="<?=base_url('services/add');?>" class="mp-qa-btn purple"><i class="fa fa-scissors"></i> Add Service</a><?php endif; ?>
          <a href="<?=base_url('sales/add');?>" class="mp-qa-btn red"><i class="fa fa-file-text-o"></i> New Invoice</a>
          <a href="<?=base_url('dashboard/daily_summary');?>" class="mp-qa-btn teal"><i class="fa fa-file-text"></i> Today's Summary</a>
          <a href="<?= base_url('accounts/cash_ledger'); ?>" class="mp-cash-inline" title="Click to view cash ledger"><i class="fa fa-money" style="opacity:0.85;"></i><span>Cash: <strong><?= $CI->currency($cash_in_hand); ?></strong></span></a>
        </div>
        <?php if(warehouse_module() && warehouse_count() > 1): ?>
        <div class="mp-branch-sel">
          <i class="fa fa-building-o text-muted"></i>
          <span style="font-size:13px;color:#666;">Current Branch</span>
          <form method="get" action="<?= base_url('dashboard'); ?>" style="display:inline;margin:0;padding:0;">
            <?php if($range !== 'Today'): ?><input type="hidden" name="range" value="<?= $range; ?>"><?php endif; ?>
            <select name="branch_id" onchange="this.form.submit();">
              <option value="">All Branches</option>
              <?php
              $warehouses = $this->db->where('store_id', get_current_store_id())->where('status', 1)->get('db_warehouse')->result();
              $sel = intval($selected_branch);
              foreach($warehouses as $wh):
                $wh_id = intval($wh->id);
                $is_sel = ($sel === $wh_id && $sel > 0);
              ?>
              <option value="<?= $wh_id; ?>" <?= $is_sel ? 'selected="selected"' : ''; ?>><?= htmlspecialchars($wh->warehouse_name); ?></option>
              <?php endforeach; ?>
            </select>
          </form>
        </div>
        <?php endif; ?>
      </div>
    </div>
      <!-- SECTION 1: BUSINESS OVERVIEW -->
      <div class="mp-section">
        <div class="mp-page-head">
          <div>
            <h2>Business Overview</h2>
            <div class="mp-page-sub"><?= $range_label; ?> · <?= htmlspecialchars($this->session->userdata('store_name') ?? 'MartPoint'); ?></div>
          </div>
          <div class="mp-range-tabs">
            <?php $range_tabs = ['Today'=>'Today','7Days'=>'7 Days','30Days'=>'30 Days','LastMonth'=>'Last Month','ThisMonth'=>'This Month','ThisYear'=>'This Year']; ?>
            <?php foreach($range_tabs as $rk=>$rv):
              $range_url = base_url('dashboard?range=' . $rk);
              if(!empty($selected_branch)){ $range_url .= '&branch_id=' . $selected_branch; }
            ?>
            <a href="<?= $range_url; ?>" class="tab <?= ($range === $rk) ? 'active' : ''; ?>"><?= $rv; ?></a>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="mp-kpi-grid">
          <div class="mp-kpi-card sales">
            <div class="mp-kpi-icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg></div>
            <div class="mp-kpi-label"><?= $range_label; ?> Sales</div>
            <div class="mp-kpi-value"><?= $CI->currency($today_sales['today']); ?></div>
            <div class="mp-kpi-sub <?= $today_sales['change'] >= 0 ? 'up' : 'down' ?>"><?= $today_sales['change'] >= 0 ? '&uarr;' : '&darr;' ?> <?= abs($today_sales['change']) ?>% vs Previous</div>
          </div>
          <div class="mp-kpi-card profit">
            <div class="mp-kpi-icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M23 6l-9.5 9.5-5-5L1 18"/><polyline points="17 6 23 6 23 12"/></svg></div>
            <div class="mp-kpi-label"><?= $range_label; ?> Profit</div>
            <div class="mp-kpi-value"><?= $CI->currency($today_profit['profit']); ?></div>
            <div class="mp-kpi-sub up">Margin <?= $today_profit['margin'] ?>%</div>
          </div>
          <div class="mp-kpi-card expense">
            <div class="mp-kpi-icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg></div>
            <div class="mp-kpi-label"><?= $range_label; ?> Expenses</div>
            <div class="mp-kpi-value"><?= $CI->currency($today_expenses); ?></div>
            <div class="mp-kpi-sub neutral">Total spent</div>
          </div>
          <div class="mp-kpi-card cash">
            <div class="mp-kpi-icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><rect x="2" y="5" width="20" height="14" rx="2"/><circle cx="12" cy="12" r="3"/><line x1="2" y1="10" x2="22" y2="10"/></svg></div>
            <div class="mp-kpi-label">Cash in Hand</div>
            <div class="mp-kpi-value"><?= $CI->currency($cash_in_hand); ?></div>
            <div class="mp-kpi-sub up"><a href="<?= base_url('accounts/cash_ledger'); ?>" style="color:inherit;text-decoration:none;">View Ledger &rarr;</a></div>
          </div>
          <div class="mp-kpi-card debt">
            <div class="mp-kpi-icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/></svg></div>
            <div class="mp-kpi-label">Outstanding Debts</div>
            <div class="mp-kpi-value"><?= $CI->currency($outstanding['total']); ?></div>
            <div class="mp-kpi-sub down"><?= number_format($outstanding['count']) ?> <?= $customer_label; ?><?= $outstanding['count'] != 1 ? 's' : '' ?> Owing</div>
          </div>
          <?php if($is_product_business): ?>
          <div class="mp-kpi-card stock">
            <div class="mp-kpi-icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg></div>
            <div class="mp-kpi-label">Low Stock Items</div>
            <div class="mp-kpi-value"><?= $low_stock_count ?></div>
            <div class="mp-kpi-sub <?= $low_stock_count > 0 ? 'down' : 'up' ?>"><?= $low_stock_count > 0 ? 'Need Attention' : 'Stock is Healthy' ?></div>
          </div>
          <?php endif; ?>
          <div class="mp-kpi-card sales">
            <div class="mp-kpi-icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M3 3v18h18"/><path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"/></svg></div>
            <div class="mp-kpi-label"><?= $range_label; ?> Invoices</div>
            <div class="mp-kpi-value"><?= number_format($invoices_range['count']); ?></div>
            <div class="mp-kpi-sub <?= $invoices_range['change'] >= 0 ? 'up' : 'down' ?>"><?= $invoices_range['change'] >= 0 ? '&uarr;' : '&darr;' ?> <?= abs($invoices_range['change']) ?>% vs Previous</div>
          </div>
          <div class="mp-kpi-card profit">
            <div class="mp-kpi-icon"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M20 8v6M23 11h-6"/></svg></div>
            <div class="mp-kpi-label"><?= $range_label; ?> New Customers</div>
            <div class="mp-kpi-value"><?= number_format($new_customers_range['count']); ?></div>
            <div class="mp-kpi-sub <?= $new_customers_range['change'] >= 0 ? 'up' : 'down' ?>"><?= $new_customers_range['change'] >= 0 ? '&uarr;' : '&darr;' ?> <?= abs($new_customers_range['change']) ?>% vs Previous</div>
          </div>
        </div>
      </div>

      <!-- SECTION 2: SALES VS EXPENSES + SALES TARGET (2fr 1fr) -->
      <?php if($CI->permissions('dashboard_view')){ ?>
      <div class="mp-section">
        <div class="mp-row r-2-1">
          <div class="mp-card">
            <div class="mp-card-head">
              <h3>Sales vs Expenses</h3>
              <a href="<?=base_url('reports/profit_loss');?>" class="mp-card-link">View Report</a>
            </div>
            <div class="mp-card-body" style="padding:20px;position:relative;">
              <?php
              // Build chronological SVG chart data
              $c_labels = array_reverse($month ?? []);
              $c_sales = array_reverse($sales ?? []);
              $c_expense = array_reverse($expense ?? []);
              $c_n = count($c_labels);
              $c_w = 600; $c_h = 240;
              $c_pl = 50; $c_pr = 20; $c_pt = 20; $c_pb = 35;
              $c_pw = $c_w - $c_pl - $c_pr;
              $c_ph = $c_h - $c_pt - $c_pb;

              $all_vals = array_merge($c_sales, $c_expense);
              $c_max_raw = !empty($all_vals) ? max($all_vals) : 0;
              $c_max = 1000;
              if($c_max_raw > 0){
                $digits = strlen((string)floor($c_max_raw));
                $step = pow(10, max(0, $digits - 1));
                $c_max = ceil($c_max_raw / $step) * $step;
                if($c_max < $c_max_raw * 1.1){
                  $c_max = ceil($c_max_raw / ($step / 2)) * ($step / 2);
                }
              }
              if($c_max <= 0) $c_max = 1000;

              function mp_svg_chart_fmt($v){
                if($v >= 1000000) return round($v/1000000,1).'M';
                if($v >= 1000) return round($v/1000,0).'k';
                return number_format($v);
              }

              $c_points = [];
              for($i=0; $i<$c_n; $i++){
                $cx = $c_n <= 1 ? $c_pl + $c_pw/2 : $c_pl + ($i / ($c_n - 1)) * $c_pw;
                $cy_s = $c_h - $c_pb - (($c_sales[$i] / $c_max) * $c_ph);
                $cy_e = $c_h - $c_pb - (($c_expense[$i] / $c_max) * $c_ph);
                $c_points[] = ['x'=>$cx, 'y_s'=>$cy_s, 'y_e'=>$cy_e, 'label'=>$c_labels[$i], 'sales'=>$c_sales[$i], 'expense'=>$c_expense[$i]];
              }

              $sales_area = '';
              $sales_line = '';
              $expense_line = '';
              if(!empty($c_points)){
                $sales_area = 'M '.$c_points[0]['x'].' '.($c_h - $c_pb);
                foreach($c_points as $p) $sales_area .= ' L '.$p['x'].' '.$p['y_s'];
                $sales_area .= ' L '.$c_points[$c_n-1]['x'].' '.($c_h - $c_pb).' Z';

                $sales_line = 'M';
                foreach($c_points as $k=>$p){
                  $sales_line .= ($k ? ' L ' : ' ').$p['x'].' '.$p['y_s'];
                }

                $expense_line = 'M';
                foreach($c_points as $k=>$p){
                  $expense_line .= ($k ? ' L ' : ' ').$p['x'].' '.$p['y_e'];
                }
              }
              ?>
              <div style="aspect-ratio:600/240; width:100%;"><svg id="salesTrendChart" viewBox="0 0 600 240" width="100%" height="100%" preserveAspectRatio="xMidYMid meet" style="display:block;">
                <g stroke="var(--mp-border)" stroke-width="1">
                  <?php for($g=0; $g<5; $g++):
                    $gy = $c_h - $c_pb - ($g / 4) * $c_ph;
                    $gv = round(($g / 4) * $c_max);
                  ?>
                  <line x1="<?= $c_pl; ?>" y1="<?= $gy; ?>" x2="<?= $c_w - $c_pr; ?>" y2="<?= $gy; ?>"/>
                  <text x="<?= $c_pl - 6; ?>" y="<?= $gy + 4; ?>" text-anchor="end" font-size="10" fill="var(--mp-muted)" font-family="Inter"><?= mp_svg_chart_fmt($gv); ?></text>
                  <?php endfor; ?>
                </g>
                <?php if(!empty($c_points)): ?>
                <path d="<?= $sales_area; ?>" fill="rgba(0,87,255,.12)" stroke="none"/>
                <path d="<?= $sales_line; ?>" fill="none" stroke="var(--mp-primary)" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>
                <path d="<?= $expense_line; ?>" fill="none" stroke="var(--mp-pay)" stroke-width="2.5" stroke-dasharray="4 4" stroke-linejoin="round" stroke-linecap="round"/>
                <g fill="var(--mp-primary)">
                  <?php foreach($c_points as $pk=>$p): ?>
                  <circle cx="<?= $p['x']; ?>" cy="<?= $p['y_s']; ?>" r="<?= $pk === $c_n - 1 ? '4' : '3'; ?>"/>
                  <?php endforeach; ?>
                </g>
                <g fill="var(--mp-pay)">
                  <?php foreach($c_points as $p): ?>
                  <circle cx="<?= $p['x']; ?>" cy="<?= $p['y_e']; ?>" r="3"/>
                  <?php endforeach; ?>
                </g>
                <g fill="var(--mp-muted)" font-size="10" font-family="Inter" text-anchor="middle">
                  <?php foreach($c_points as $p): ?>
                  <text x="<?= $p['x']; ?>" y="<?= $c_h - 12; ?>"><?= htmlspecialchars($p['label']); ?></text>
                  <?php endforeach; ?>
                </g>
                <?php else: ?>
                <text x="300" y="120" text-anchor="middle" font-size="14" fill="var(--mp-muted)" font-family="Inter">No data for selected range</text>
                <?php endif; ?>
              </svg></div>
            </div>
            <div class="mp-chart-legend">
              <div class="mp-legend-item"><div class="mp-legend-dot" style="background:var(--mp-primary)"></div> Sales</div>
              <div class="mp-legend-item"><div class="mp-legend-dot" style="background:var(--mp-pay)"></div> Expenses</div>
            </div>
          </div>
          <div class="mp-card">
            <div class="mp-card-head">
              <h3>Sales Target</h3>
              <a href="<?=base_url('dashboard/daily_summary');?>" class="mp-card-link">Summary</a>
            </div>
            <div class="mp-card-body">
              <div class="mp-target-ring">
                <svg class="mp-ring-svg" width="120" height="120" viewBox="0 0 120 120" style="flex-shrink:0;">
                  <circle cx="60" cy="60" r="50" fill="none" stroke="var(--mp-bg)" stroke-width="12"/>
                  <circle cx="60" cy="60" r="50" fill="none" stroke="var(--mp-primary)" stroke-width="12"
                    stroke-dasharray="314" stroke-dashoffset="<?= 314 - (314 * min($range_progress, 100) / 100); ?>" stroke-linecap="round"
                    transform="rotate(-90 60 60)"/>
                  <text x="60" y="65" text-anchor="middle" font-size="22" font-weight="700" fill="var(--mp-primary)" font-family="Inter"><?= intval($range_progress); ?>%</text>
                </svg>
                <div class="mp-target-info">
                  <div class="mp-target-pct"><?= $CI->currency($range_sales); ?></div>
                  <div class="mp-target-amt">of <?= $CI->currency($range_target); ?> goal</div>
                  <div class="mp-target-eta"><?= $range_progress >= 100 ? 'Target reached!' : 'On track'; ?></div>
                </div>
              </div>
              <div style="margin-top:20px;border-top:1px solid var(--mp-border);padding-top:16px;">
                <div style="font-size:12px;color:var(--mp-muted);font-weight:600;margin-bottom:10px;"><?= $range_label; ?> Stats</div>
                <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;"><span style="color:var(--mp-muted);">Profit</span><span style="font-weight:700;"><?= $CI->currency($today_profit['profit']); ?></span></div>
                <div style="display:flex;justify-content:space-between;font-size:13px;margin-bottom:6px;"><span style="color:var(--mp-muted);">Expenses</span><span style="font-weight:700;"><?= $CI->currency($today_expenses); ?></span></div>
                <div style="display:flex;justify-content:space-between;font-size:13px;"><span style="color:var(--mp-muted);">Cash</span><span style="font-weight:700;"><?= $CI->currency($cash_in_hand); ?></span></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php } ?>

      <!-- SECTION 3: RECENT SALES + RECENT ACTIVITY (2fr 1fr) -->
      <div class="mp-section">
        <div class="mp-row r-2-1">
          <?php
            $this->db->select("*");
            $this->db->where("store_id", get_current_store_id());
            $this->db->from("db_sales");
            if(!is_admin() && !is_store_admin()){ $this->db->where("created_by", $this->session->userdata('inv_username')); }
            if(!empty($selected_branch)){ $this->db->where("warehouse_id", $selected_branch); }
            // Filter recent sales by the selected date range
            if(!empty($range_info)){
              if($range_info['from'] === $range_info['to']){
                $this->db->where("sales_date", $range_info['from']);
              } else {
                $this->db->where("sales_date >=", $range_info['from']);
                $this->db->where("sales_date <=", $range_info['to']);
              }
            }
            $this->db->order_by('id','desc')->limit(6);
            $q_recent = $this->db->get();
          ?>
          <div class="mp-card">
            <div class="mp-card-head">
              <h3>Recent Sales <span style="font-size:12px;color:var(--mp-muted);font-weight:400;">· <?= $range_label; ?></span></h3>
              <a href="<?=base_url('sales');?>" class="mp-card-link">View All</a>
            </div>
            <table class="mp-tbl">
              <thead>
                <tr><th>Invoice</th><th>Customer</th><th>Amount</th><th>Status</th><th>Time</th></tr>
              </thead>
              <tbody>
                <?php if($q_recent->num_rows() > 0){ ?>
                  <?php foreach($q_recent->result() as $res5){
                    $cust = get_customer_details($res5->customer_id);
                    $cust_name = ($cust && !empty($cust->customer_name)) ? $cust->customer_name : 'Walk-in';
                    $time_str = !empty($res5->created_time) ? date('g:i A', strtotime($res5->created_time)) : date('g:i A', strtotime($res5->sales_date));
                  ?>
                  <tr>
                    <td><strong><?= $res5->sales_code; ?></strong></td>
                    <td><?= htmlspecialchars($cust_name); ?></td>
                    <td class="amt"><?= $CI->currency($res5->grand_total, true); ?></td>
                    <td><span class="mp-pill <?= $res5->payment_status == 'Paid' ? 'paid' : ($res5->payment_status == 'Partial' ? 'partial' : 'unpaid'); ?>"><?= $res5->payment_status; ?></span></td>
                    <td><?= $time_str; ?></td>
                  </tr>
                  <?php } ?>
                <?php } else { ?>
                  <tr><td colspan="5" style="text-align:center;color:var(--mp-muted);padding:24px;">No sales in <?= $range_label; ?></td></tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
          <div class="mp-card">
            <div class="mp-card-head"><h3>Recent Activity</h3></div>
            <div class="mp-card-body">
              <?php if(!empty($recent_activities)){ ?>
              <?php foreach(array_slice($recent_activities, 0, 5) as $act){ ?>
              <div class="mp-activity-item">
                <div class="mp-activity-dot <?= $act['type'] ?>">
                  <?php if($act['type']=='sale'){ ?><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                  <?php } elseif($act['type']=='customer'){ ?><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/></svg>
                  <?php } else { ?><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/></svg><?php } ?>
                </div>
                <div class="mp-activity-body">
                  <div class="mp-activity-title"><?= $act['title'] ?></div>
                  <div class="mp-activity-meta"><?= show_date($act['date']) ?></div>
                </div>
                <?php if($act['amount'] > 0){ ?><div class="mp-activity-amt" style="color:var(--mp-success)">+<?= $CI->currency($act['amount']) ?></div><?php } ?>
              </div>
              <?php } ?>
              <?php } else { ?><div class="mp-empty-state">No recent activity</div><?php } ?>
            </div>
          </div>
        </div>
      </div>

      <!-- SECTION 4: TOP PRODUCTS + LOW STOCK (equal) -->
      <div class="mp-section">
        <div class="mp-row r-equal">
          <?php if($is_product_business || $is_service_business): ?>
          <div class="mp-card">
            <div class="mp-card-head">
              <h3>Top Selling <?= $is_service_business && !$is_product_business ? 'Services' : $item_label . 's'; ?></h3>
              <a href="<?=base_url('reports/item_sales');?>" class="mp-card-link"><?= $range_label; ?></a>
            </div>
            <div class="mp-card-body">
              <?php if(!empty($top_products)){ ?>
              <?php $i=1; foreach(array_slice($top_products, 0, 5) as $prod){ ?>
              <div class="mp-top-prod">
                <div class="mp-top-prod-rank <?= $i <= 3 ? 'r'.$i : '' ?>"><?= $i ?></div>
                <div class="mp-top-prod-info"><div class="mp-top-prod-name"><?= $prod['name'] ?></div><div class="mp-top-prod-meta"><?= number_format($prod['qty'] ?? 0); ?> sold</div></div>
                <div class="mp-top-prod-amt"><?= $CI->currency($prod['revenue']) ?></div>
              </div>
              <?php $i++; } ?>
              <?php } else { ?><div class="mp-empty-state">Not Enough Data Yet</div><?php } ?>
            </div>
          </div>
          <?php endif; ?>
          <?php if($is_product_business): ?>
          <div class="mp-card">
            <div class="mp-card-head">
              <h3>Low Stock Alerts</h3>
              <a href="<?=base_url('items');?>" class="mp-card-link">Restock</a>
            </div>
            <table class="mp-tbl">
              <thead><tr><th>Product</th><th>Stock</th><th>Status</th></tr></thead>
              <tbody>
                <?php if(!empty($low_stock_items)){ ?>
                <?php foreach(array_slice($low_stock_items, 0, 5) as $item){ ?>
                <tr>
                  <td><?= $item['name'] ?></td>
                  <td class="amt"><?= $item['qty'] ?></td>
                  <td><span class="mp-pill <?= $item['qty'] <= 0 ? 'out' : 'low' ?>"><?= $item['qty'] <= 0 ? 'Out' : 'Low' ?></span></td>
                </tr>
                <?php } ?>
                <?php } else { ?>
                <tr><td colspan="3" style="text-align:center;color:var(--mp-muted);padding:24px;">All stock levels are healthy</td></tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
          <?php endif; ?>
        </div>
      </div>

      <!-- SECTION 5: BRANCH PERFORMANCE + TOP DEBTORS (equal) -->
      <div class="mp-section">
        <div class="mp-row r-equal">
          <?php if(warehouse_module() && warehouse_count() > 1 && !empty($branch_performance)): ?>
          <div class="mp-card">
            <div class="mp-card-head">
              <h3><?= $branch_label; ?> Performance — <?= $range_label; ?></h3>
            </div>
            <div class="mp-card-body">
              <?php
              $max_branch_sales = max(array_column($branch_performance, 'sales')) ?: 1;
              $branch_colors = ['var(--mp-primary)', 'var(--mp-success)', 'var(--mp-pay)', 'var(--mp-warning)'];
              $bi = 0;
              foreach($branch_performance as $branch):
                $branch_url = base_url('dashboard?branch_id=' . $branch['id']);
                if($range !== 'Today'){ $branch_url .= '&range=' . $range; }
                $pct = round(($branch['sales'] / $max_branch_sales) * 100);
              ?>
              <div class="mp-branch-row">
                <div class="mp-branch-name"><?= $branch['name']; ?></div>
                <div class="mp-branch-bar"><div class="mp-branch-bar-fill" style="width:<?= $pct; ?>%;background:<?= $branch_colors[$bi % 4]; ?>;"></div></div>
                <div class="mp-branch-amt"><?= $CI->currency($branch['sales']); ?></div>
              </div>
              <?php $bi++; endforeach; ?>
            </div>
          </div>
          <?php endif; ?>
          <div class="mp-card">
            <div class="mp-card-head">
              <h3>Top Debtors</h3>
              <a href="<?=base_url('customers');?>" class="mp-card-link">View All</a>
            </div>
            <table class="mp-tbl">
              <thead><tr><th>Customer</th><th>Owing</th></tr></thead>
              <tbody>
                <?php if(!empty($top_debtors)){ ?>
                <?php foreach(array_slice($top_debtors, 0, 5) as $debtor){ ?>
                <tr>
                  <td><?= $debtor['name'] ?></td>
                  <td class="amt" style="color:var(--mp-danger);"><?= $CI->currency($debtor['amount']) ?></td>
                </tr>
                <?php } ?>
                <?php } else { ?>
                <tr><td colspan="2" style="text-align:center;color:var(--mp-muted);padding:24px;">No outstanding payments</td></tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- SECTION 6: INSIGHTS (removed per request - insights shown in header marquee) -->

      <!-- Fashion Intelligence Widget (conditional) - removed per request -->

      <!-- Expiry Alerts (conditional) -->
      <?php
      try {
        if (mp_feature_enabled('expiry_tracking')):
        $CI->load->model('expiry_settings_model');
        $expired_count = $CI->expiry_settings_model->count_expired();
        $expiring_count = $CI->expiry_settings_model->count_expiring();
        $total_alerted = $expired_count + $expiring_count;
        if($total_alerted > 0):
      ?>
      <div class="mp-section">
        <div class="mp-row r-equal">
          <div class="mp-card">
            <div class="mp-card-head"><h3>Expiry Alerts</h3><a href="<?= base_url('expired_items_report'); ?>" class="mp-card-link">View Report</a></div>
            <div class="mp-card-body" style="padding:0;">
              <div class="mp-top-prod" style="padding:14px 20px;">
                <div class="mp-top-prod-info"><div class="mp-top-prod-name" style="color:var(--mp-danger);">Expired Items</div></div>
                <div class="mp-top-prod-amt" style="color:var(--mp-danger);font-size:20px;"><?= $expired_count; ?></div>
              </div>
              <div class="mp-top-prod" style="padding:14px 20px;">
                <div class="mp-top-prod-info"><div class="mp-top-prod-name" style="color:var(--mp-warning);">Expiring Soon</div></div>
                <div class="mp-top-prod-amt" style="color:var(--mp-warning);font-size:20px;"><?= $expiring_count; ?></div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <?php
        endif;
        endif;
      } catch (Exception $e) { /* Expiry table not ready yet */ }
      ?>

      <!-- Hidden preserved elements (DataTables, charts, admin tables) -->
      <div style="display:none;">
        <?php if($is_product_business && $CI->permissions('dashboard_stock_alert') && !is_user()) { ?>
        <table id="example2" class="table table-bordered table-hover"><thead><tr class='bg-warning'><th>#</th><th><?= $this->lang->line('item_name'); ?></th><th><?= $this->lang->line('category_name'); ?></th><th><?= $this->lang->line('brand_name'); ?></th><th><?= $this->lang->line('stock'); ?></th></tr></thead><tbody></tbody></table>
        <?php } ?>
        <canvas id="doughnut-chart" width="100%" height="200"></canvas>
        <?php if(is_admin() && store_module()){ ?>
        <table id="stores_details" class="table"><thead><tr><th>#</th><th><?= $this->lang->line('store_name'); ?></th><th><?= $this->lang->line('total_sales'); ?></th><th><?= $this->lang->line('total_expense'); ?></th><th><?= $this->lang->line('sales_due'); ?></th></tr></thead><tbody><?= $CI->get_storewise_details(); ?></tbody></table>
        <?php } ?>
      </div>

