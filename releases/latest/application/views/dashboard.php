<!DOCTYPE html>
<html>
<head>
<!-- FORM CSS CODE -->
<?php include"comman/code_css.php"; ?>
<!-- </copy> -->  

</head>
<body class="hold-transition skin-blue sidebar-mini  ">

<div class="wrapper">
  
  
  <?php include"sidebar.php"; ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1>
        <?=$page_title;?>
      </h1>     
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <!-- ********** ALERT MESSAGE START******* -->
          <?php include"comman/code_flashdata.php"; ?>
          <!-- ********** ALERT MESSAGE END******* -->

          <!-- Subscription Warning Banner -->
          <?php if(!special_access() && isset($subscription_status) && $subscription_status['status'] !== 'ACTIVE' && $subscription_status['status'] !== 'NOT_ACTIVATED'): ?>
          <div class="alert alert-<?= ($subscription_status['status'] === 'SUSPENDED') ? 'danger' : 'warning'; ?> alert-dismissible">
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
        </div>
      </div>

      <!-- ====== MARTPOINT RETAIL DASHBOARD V2 ====== -->
      <?php
      $is_product_business = true;
      $is_service_business = false;
      $industry_type = 'general_retail';
      try {
        $bp_profile = mp_get_store_profile();
        $is_product_business = empty($bp_profile['business_model']) || in_array($bp_profile['business_model'], ['product_based','product_and_service']);
        $is_service_business = in_array($bp_profile['business_model'] ?? '', ['service_based','product_and_service']);
        $industry_type = $bp_profile['industry_type'] ?? 'general_retail';
      } catch (Exception $e) { /* fallback to product-based if helper fails */ }
      $item_label = mp_label('item','Product');
      $customer_label = mp_label('customer','Customer');
      $branch_label = mp_label('branch','Branch');
      ?>
      <div class="mp-dashboard-wrapper">

      <?php if(!is_user() && !empty($needs_clock_in)): ?>
      <!-- Clock Status -->
      <div class="mp-section">
        <div id="dashClockStatusCard" style="display:flex;align-items:center;gap:12px;padding:12px 16px;border-radius:8px;background:<?= (!empty($needs_clock_in)) ? '#FFF3CD' : '#D1FAE5'; ?>;border:1px solid <?= (!empty($needs_clock_in)) ? '#F59E0B' : '#10B981'; ?>;">
          <i class="fa <?= (!empty($needs_clock_in)) ? 'fa-clock-o text-warning' : 'fa-check-circle text-success'; ?>" style="font-size:20px;"></i>
          <div style="flex:1;">
            <strong style="font-size:14px;color:<?= (!empty($needs_clock_in)) ? '#92400E' : '#065F46'; ?>;">
              <?= (!empty($needs_clock_in)) ? 'You are not clocked in.' : 'You are clocked in.'; ?>
            </strong>
            <span style="font-size:13px;color:#666;display:block;margin-top:2px;">
              <?= (!empty($needs_clock_in)) ? 'Clock in before processing sales.' : 'Remember to clock out at the end of your shift.'; ?>
            </span>
          </div>
          <button type="button" class="btn btn-sm <?= (!empty($needs_clock_in)) ? 'btn-warning' : 'btn-success'; ?>" onclick="$('#appClockInBtn').trigger('click');">
            <i class="fa <?= (!empty($needs_clock_in)) ? 'fa-sign-in' : 'fa-sign-out'; ?>"></i> <?= (!empty($needs_clock_in)) ? 'Clock In' : 'Clock Out'; ?>
          </button>
        </div>
      </div>
      <?php endif; ?>

      <!-- SECTION: QUICK ACTIONS + BRANCH SELECTOR -->
      <div class="mp-section">
        <div style="display:flex;align-items:center;justify-content:space-between;gap:20px;flex-wrap:wrap;">
          <!-- Quick Actions -->
          <div class="mp-quick-actions" style="flex:1;min-width:0;">
            <a href="<?=base_url('pos');?>" class="mp-quick-btn green"><i class="fa fa-shopping-cart"></i> New Sale</a>
            <a href="<?=base_url('customers/add');?>" class="mp-quick-btn blue"><i class="fa fa-user-plus"></i> Add <?= $customer_label; ?></a>
            <a href="<?=base_url('expense/add');?>" class="mp-quick-btn orange"><i class="fa fa-minus-square"></i> Add Expense</a>
            <?php if($is_product_business): ?>
            <a href="<?=base_url('purchase/add');?>" class="mp-quick-btn purple"><i class="fa fa-plus-square"></i> Purchase Stock</a>
            <?php endif; ?>
            <?php if($is_service_business): ?>
            <a href="<?=base_url('services/add');?>" class="mp-quick-btn purple"><i class="fa fa-scissors"></i> Add Service</a>
            <?php endif; ?>
            <a href="<?=base_url('sales/add');?>" class="mp-quick-btn red"><i class="fa fa-file-text-o"></i> New Invoice</a>
            <a href="<?=base_url('dashboard/daily_summary');?>" class="mp-quick-btn teal"><i class="fa fa-file-text"></i> Today's Summary</a>
            <a href="<?= base_url('accounts/cash_ledger'); ?>" class="mp-cash-inline" title="Click to view cash ledger" style="display:inline-flex;align-items:center;gap:8px;background:linear-gradient(135deg,#10B981 0%,#059669 100%);color:#fff;padding:6px 14px;border-radius:8px;font-size:13px;white-space:nowrap;box-shadow:0 2px 6px rgba(16,185,129,0.25);margin-left:4px;text-decoration:none;">
              <i class="fa fa-money" style="opacity:0.85;"></i>
              <span>Cash: <strong><?= $CI->currency($cash_in_hand); ?></strong></span>
            </a>
          </div>

          <!-- BRANCH / WAREHOUSE SELECTOR -->
          <?php if(warehouse_module() && warehouse_count() > 1): ?>
          <div style="display:flex;align-items:center;gap:8px;white-space:nowrap;flex-shrink:0;">
            <i class="fa fa-building-o text-muted"></i>
            <span style="font-size:13px;color:#666;">Current Branch</span>
            <form method="get" action="<?= base_url('dashboard'); ?>" style="display:inline;margin:0;padding:0;">
              <?php if($range !== 'Today'): ?><input type="hidden" name="range" value="<?= $range; ?>"><?php endif; ?>
              <select name="branch_id" id="branch-selector" class="form-control" style="display:inline-block;width:auto;min-width:140px;height:34px;font-size:13px;" onchange="this.form.submit();">
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
        <div style="display:flex;align-items:center;justify-content:space-between;gap:15px;flex-wrap:wrap;margin-bottom:12px;">
          <div class="mp-section-title" style="margin:0;">Business Overview</div>
          <div class="mp-chart-tabs" style="display:flex;gap:4px;flex-wrap:wrap;">
            <?php $range_tabs = ['Today'=>'Today','7Days'=>'7 Days','30Days'=>'30 Days','LastMonth'=>'Last Month','ThisMonth'=>'This Month','ThisYear'=>'This Year']; ?>
            <?php foreach($range_tabs as $rk=>$rv):
              $range_url = base_url('dashboard?range=' . $rk);
              if(!empty($selected_branch)){ $range_url .= '&branch_id=' . $selected_branch; }
            ?>
            <a href="<?= $range_url; ?>" class="tab <?= ($range === $rk) ? 'active' : ''; ?>" style="text-decoration:none;"><?= $rv; ?></a>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="mp-kpi-grid">
          <div class="mp-kpi-card sales">
            <div class="mp-kpi-label"><?= $range_label; ?> Sales</div>
            <div class="mp-kpi-value"><?= $CI->currency($today_sales['today']); ?></div>
            <div class="mp-kpi-sub <?= $today_sales['change'] >= 0 ? 'up' : 'down' ?>"><?= $today_sales['change'] >= 0 ? '&uarr;' : '&darr;' ?> <?= abs($today_sales['change']) ?>% vs Previous</div>
          </div>
          <div class="mp-kpi-card profit">
            <div class="mp-kpi-label"><?= $range_label; ?> Profit</div>
            <div class="mp-kpi-value"><?= $CI->currency($today_profit['profit']); ?></div>
            <div class="mp-kpi-sub up">Margin <?= $today_profit['margin'] ?>%</div>
            <div style="margin-top:8px;">
              <a href="<?=base_url('dashboard/daily_summary');?>" class="btn btn-xs" style="background:#10B981;color:#fff;border:none;"><i class="fa fa-file-text-o"></i> Daily Summary</a>
            </div>
          </div>
          <div class="mp-kpi-card target">
            <div class="mp-kpi-label">Daily Target</div>
            <div class="mp-kpi-value"><?= $daily_target_progress; ?>%</div>
            <div class="mp-kpi-sub"><?= $CI->currency($today_sales['today']); ?> / <?= $CI->currency($daily_target); ?></div>
            <div style="margin-top:8px;height:8px;background:#E2E8F0;border-radius:4px;overflow:hidden;">
              <div style="height:100%;width:<?= $daily_target_progress; ?>%;background:#0057FF;border-radius:4px;"></div>
            </div>
          </div>
          <div class="mp-kpi-card debt">
            <div class="mp-kpi-label">Outstanding Debts</div>
            <div class="mp-kpi-value"><?= $CI->currency($outstanding['total']); ?></div>
            <div class="mp-kpi-sub"><?= number_format($outstanding['count']) ?> <?= $customer_label; ?><?= $outstanding['count'] != 1 ? 's' : '' ?> Owing</div>
          </div>
          <?php if($is_product_business): ?>
          <div class="mp-kpi-card stock">
            <div class="mp-kpi-label">Low Stock Items</div>
            <div class="mp-kpi-value"><?= $low_stock_count ?></div>
            <div class="mp-kpi-sub <?= $low_stock_count > 0 ? 'down' : 'up' ?>"><?= $low_stock_count > 0 ? 'Need Attention' : 'Stock is Healthy' ?></div>
          </div>
          <?php endif; ?>
          <div class="mp-kpi-card summary">
            <div class="mp-kpi-label"><?= $range_label; ?> Summary</div>
            <div style="display:flex;gap:12px;align-items:center;margin-top:6px;">
              <div style="font-size:13px;color:#475569;">
                <div>Sales: <strong><?= $CI->currency($today_sales['today']); ?></strong></div>
                <div>Profit: <strong><?= $CI->currency($today_profit['profit']); ?></strong></div>
                <div>Expenses: <strong><?= $CI->currency($today_expenses); ?></strong></div>
              </div>
            </div>
            <div style="margin-top:10px;display:flex;gap:6px;">
              <a href="<?=base_url('dashboard/daily_summary');?>" class="btn btn-xs" style="background:#F97316;color:#fff;border:none;"><i class="fa fa-eye"></i> View</a>
              <a href="<?=base_url('dashboard/daily_summary');?>" class="btn btn-xs" style="background:#F1F5F9;color:#475569;border:1px solid #E2E8F0;"><i class="fa fa-whatsapp" style="color:#25D366;"></i> Share</a>
            </div>
          </div>
        </div>
      </div>

      <!-- SECTION: SALES TREND -->
      <?php if(!is_user() && $CI->permissions('dashboard_bar_chart')){ ?>
      <div class="mp-section">
        <div class="mp-card">
          <div class="mp-card-header">
            <div class="mp-card-title">Sales Trend</div>
            <small class="text-muted"><?= $range_label; ?></small>
          </div>
          <div class="mp-card-body" style="padding:20px;position:relative;height:260px;">
            <canvas id="salesTrendChart" class="bar-chartcanvas" style="height:260px;"></canvas>
          </div>
        </div>
      </div>
      <?php } ?>

      <!-- SECTION: TOP SELLING / LOW STOCK / RECENT SALES (3-column) -->
      <div class="mp-section">
        <div class="row mp-three-col">
          <!-- Top Selling -->
          <?php if($is_product_business || $is_service_business): ?>
          <div class="col-md-4 col-sm-12 mp-col">
            <div class="mp-card">
              <div class="mp-card-header">
                <div class="mp-card-title">Top Selling <?= $is_service_business && !$is_product_business ? 'Services' : $item_label . 's'; ?></div>
                <small class="text-muted"><?= $range_label; ?></small>
              </div>
              <div class="mp-card-body" style="padding:0;">
                <?php if(!empty($top_products)){ ?>
                <ul class="mp-list-clean">
                  <?php $i=1; foreach(array_slice($top_products, 0, 5) as $prod){ ?>
                  <li class="mp-list-row">
                    <div class="mp-row-left">
                      <span class="mp-row-icon"><?= $i; ?></span>
                      <span class="mp-row-name"><?= $prod['name']; ?></span>
                    </div>
                    <span class="mp-row-value"><?= $CI->currency($prod['revenue']); ?></span>
                  </li>
                  <?php $i++; } ?>
                </ul>
                <?php } else { ?><div class="mp-empty-state">Not Enough Data Yet</div><?php } ?>
              </div>
            </div>
          </div>
          <?php endif; ?>
          <!-- Low Stock -->
          <?php if($is_product_business && !empty($low_stock_items)){ ?>
          <div class="col-md-4 col-sm-12 mp-col">
            <div class="mp-card">
              <div class="mp-card-header">
                <div class="mp-card-title">Low Stock Alerts</div>
                <a href="<?=base_url('items');?>" class="btn btn-xs mp-btn-outline">View Inventory</a>
              </div>
              <div class="mp-card-body" style="padding:0;">
                <ul class="mp-list-clean">
                  <?php foreach(array_slice($low_stock_items, 0, 5) as $item){ ?>
                  <li class="mp-list-row">
                    <span class="mp-row-name"><?= $item['name']; ?></span>
                    <span class="mp-row-value mp-text-orange"><?= $item['qty']; ?> left</span>
                  </li>
                  <?php } ?>
                </ul>
              </div>
            </div>
          </div>
          <?php } ?>
          <!-- Recent Sales -->
          <?php
            $this->db->select("*");
            $this->db->where("store_id", get_current_store_id());
            $this->db->from("db_sales");
            if(!is_admin() && !is_store_admin()){ $this->db->where("created_by", $this->session->userdata('inv_username')); }
            if(!empty($selected_branch)){ $this->db->where("warehouse_id", $selected_branch); }
            $this->db->order_by('id','desc')->limit(5);
            $q_recent = $this->db->get();
          ?>
          <?php if($q_recent->num_rows() > 0){ ?>
          <div class="col-md-4 col-sm-12 mp-col">
            <div class="mp-card">
              <div class="mp-card-header">
                <div class="mp-card-title">Recent Sales</div>
              </div>
              <div class="mp-card-body" style="padding:0;">
                <ul class="mp-list-clean">
                  <?php foreach($q_recent->result() as $res5){ ?>
                  <li class="mp-list-row">
                    <div class="mp-row-left">
                      <span class="mp-row-name"><?= get_customer_details($res5->customer_id)->customer_name; ?></span>
                      <small class="mp-row-meta"><?= show_date($res5->sales_date); ?></small>
                    </div>
                    <span class="mp-row-value"><?= $CI->currency($res5->grand_total, true); ?></span>
                  </li>
                  <?php } ?>
                </ul>
              </div>
            </div>
          </div>
          <?php } ?>
        </div>
      </div>

      <!-- SECTION: BRANCH PERFORMANCE (secondary) -->
      <?php if(warehouse_module() && warehouse_count() > 1 && !empty($branch_performance)): ?>
      <div class="mp-section mp-section--secondary">
        <div class="mp-card" style="border:1px solid #E2E8F0;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
          <div class="mp-card-header">
            <div class="mp-card-title"><i class="fa fa-building-o"></i> <?= $branch_label; ?> Performance</div>
            <div class="mp-chart-tabs"><small class="text-muted"><?= $range_label; ?> Sales</small></div>
          </div>
          <div class="mp-card-body">
            <div class="row">
              <?php foreach($branch_performance as $branch):
                $branch_url = base_url('dashboard?branch_id=' . $branch['id']);
                if($range !== 'Today'){ $branch_url .= '&range=' . $range; }
              ?>
              <div class="col-md-3 col-sm-6 col-xs-12">
                <a href="<?= $branch_url; ?>" style="text-decoration:none;">
                  <div class="small-box" style="background:linear-gradient(135deg,#3b82f6 0%,#2563eb 100%);color:#fff;border:none;border-radius:12px;margin-bottom:12px;box-shadow:0 2px 8px rgba(59,130,246,0.25);">
                    <div class="inner"><h3 style="font-size:22px;font-weight:800;"><?= $CI->currency($branch['sales']); ?></h3><p style="font-weight:600;"><?= $branch['name']; ?></p></div>
                    <div class="icon" style="color:rgba(255,255,255,0.3);"><i class="fa fa-building-o"></i></div>
                  </div>
                </a>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- SECTION 2: ATTENTION REQUIRED -->
      <div class="mp-section mp-section--secondary">
        <div class="mp-content-grid equal">
          <?php if($is_product_business): ?>
          <div class="mp-card">
            <div class="mp-card-header">
              <div class="mp-card-title"><i class="fa fa-exclamation-triangle text-orange" style="margin-right:6px;"></i> Low Stock Alert</div>
              <a href="<?=base_url('items');?>" class="btn btn-xs btn-warning">View Inventory</a>
            </div>
            <div class="mp-card-body">
              <?php if(!empty($low_stock_items)){ ?>
              <ul class="mp-alert-list">
                <?php foreach($low_stock_items as $item){ ?>
                <li class="mp-alert-item"><div><div class="name"><?= $item['name'] ?></div><div class="meta">Min: <?= $item['min'] ?></div></div><span class="qty"><?= $item['qty'] ?> Remaining</span></li>
                <?php } ?>
              </ul>
              <?php } else { ?><div class="mp-empty-state"><i class="fa fa-check-circle" style="font-size:32px;color:#10B981;margin-bottom:8px;display:block;"></i>All stock levels are healthy</div><?php } ?>
            </div>
          </div>
          <?php endif; ?>
          <div class="mp-card">
            <div class="mp-card-header">
              <div class="mp-card-title"><i class="fa fa-money text-red" style="margin-right:6px;"></i> Outstanding Payments</div>
              <a href="<?=base_url('customers');?>" class="btn btn-xs btn-danger">View <?= $customer_label; ?>s</a>
            </div>
            <div class="mp-card-body">
              <?php if(!empty($top_debtors)){ ?>
              <ul class="mp-alert-list">
                <?php foreach($top_debtors as $debtor){ ?>
                <li class="mp-alert-item"><div><div class="name"><?= $debtor['name'] ?></div></div><span class="amount"><?= $CI->currency($debtor['amount']) ?></span></li>
                <?php } ?>
              </ul>
              <?php } else { ?><div class="mp-empty-state"><i class="fa fa-check-circle" style="font-size:32px;color:#10B981;margin-bottom:8px;display:block;"></i>No outstanding payments</div><?php } ?>
            </div>
          </div>
        </div>
      </div>

      <!-- TOP SELLING PRODUCTS / SERVICES -->
      <?php if($is_product_business || $is_service_business): ?>
      <div class="mp-section mp-section--secondary">
        <div class="mp-card" style="border:1px solid #E2E8F0;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
          <div class="mp-card-header">
            <div class="mp-card-title"><i class="fa fa-trophy text-yellow" style="margin-right:6px;"></i> Top Selling <?= $is_service_business && !$is_product_business ? 'Services' : $item_label . 's'; ?></div>
            <small class="text-muted"><?= $range_label; ?></small>
          </div>
          <div class="mp-card-body">
            <?php if(!empty($top_products)){ ?>
            <ul class="mp-product-list">
              <?php $i=1; foreach($top_products as $prod){ ?>
              <li class="mp-product-item"><span class="mp-product-rank <?= $i <= 3 ? 'top' : '' ?>"><?= $i ?></span><span class="mp-product-name"><?= $prod['name'] ?></span><span class="mp-product-revenue"><?= $CI->currency($prod['revenue']) ?></span></li>
              <?php $i++; } ?>
            </ul>
            <?php } else { ?><div class="mp-empty-state">Not Enough Data Yet</div><?php } ?>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- BEST SELLING SIZE / COLOUR WIDGET (Fashion Intelligence) -->
      <?php
      $show_variant_widget = false;
      $is_fashion = ($industry_type === 'fashion');
      $bsv = $best_selling_variant ?? null;
      if($is_fashion && $is_product_business && $bsv && !empty($bsv['top_value'])){
        $show_variant_widget = true;
      }
      ?>
      <?php if($show_variant_widget): ?>
      <div class="mp-section mp-section--secondary">
        <div class="mp-card" style="border:1px solid #E2E8F0;box-shadow:0 2px 8px rgba(0,0,0,0.04);">
          <div class="mp-card-header">
            <div class="mp-card-title"><i class="fa fa-tshirt text-purple" style="margin-right:6px;"></i> Best Selling <?= htmlspecialchars($bsv['top_attribute']); ?></div>
            <a href="<?= base_url('reports/variant_attribute'); ?>" class="btn btn-xs btn-default" style="border-radius:6px;font-weight:600;"><i class="fa fa-external-link"></i> Full Report</a>
          </div>
          <div class="mp-card-body">
            <div class="mp-kpi-grid" style="margin-bottom:12px;">
              <div class="mp-kpi-card" style="background:linear-gradient(135deg,#7C3AED 0%,#6D28D9 100%);color:#fff;min-height:auto;">
                <div class="mp-kpi-label" style="color:rgba(255,255,255,0.95);font-weight:700;font-size:16px;">Top <?= htmlspecialchars($bsv['top_attribute']); ?></div>
                <div class="mp-kpi-value" style="color:#fff;font-weight:800;font-size:30px;text-shadow:0 1px 3px rgba(0,0,0,0.2);"><?= htmlspecialchars($bsv['top_value']); ?></div>
                <div class="mp-kpi-sub" style="color:rgba(255,255,255,0.95);font-weight:700;font-size:14.5px;"><i class="fa fa-shopping-bag"></i> <?= number_format($bsv['top_qty']); ?> units sold</div>
              </div>
            </div>
            <?php foreach($bsv['by_attribute'] as $attr_type => $items): ?>
              <?php if(count($items) < 1) continue; ?>
              <div style="margin-bottom:10px;">
                <div style="font-size:13px;font-weight:700;color:#475569;text-transform:capitalize;margin-bottom:4px;"><?= htmlspecialchars(ucfirst($attr_type)); ?> ranking</div>
                <ul class="mp-product-list">
                  <?php $rank=1; foreach(array_slice($items, 0, 5) as $item): ?>
                  <li class="mp-product-item"><span class="mp-product-rank <?= $rank <= 3 ? 'top' : '' ?>"><?= $rank ?></span><span class="mp-product-name"><?= htmlspecialchars($item['value']); ?></span><span class="mp-product-revenue"><?= number_format($item['qty']); ?> units</span></li>
                  <?php $rank++; endforeach; ?>
                </ul>
              </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- EXPIRY ALERTS WIDGET -->
      <?php
      try {
        if (mp_feature_enabled('expiry_tracking')):
        $CI->load->model('expiry_settings_model');
        $expired_count = $CI->expiry_settings_model->count_expired();
        $expiring_count = $CI->expiry_settings_model->count_expiring();
        $total_alerted = $expired_count + $expiring_count;
        if($total_alerted > 0):
          $exp_settings = $CI->expiry_settings_model->get_settings();
      ?>
      <div class="mp-section mp-section--secondary">
        <div class="mp-card">
          <div class="mp-card-header">
            <div class="mp-card-title"><i class="fa fa-exclamation-triangle text-red" style="margin-right:6px;"></i> Expiry Alerts</div>
            <a href="<?= base_url('expired_items_report'); ?>" class="btn btn-xs btn-danger" style="border-radius:6px;font-weight:600;"><i class="fa fa-external-link"></i> View Report</a>
          </div>
          <div class="mp-card-body">
            <div class="mp-kpi-grid" style="margin-bottom:0;">
              <a href="<?= base_url('expired_items_report'); ?>" style="text-decoration:none;">
                <div class="mp-kpi-card" style="background:linear-gradient(135deg,#EF4444 0%,#DC2626 100%);color:#fff;min-height:auto;">
                  <div class="mp-kpi-label" style="color:rgba(255,255,255,0.95);font-weight:700;font-size:16px;">Expired Items</div>
                  <div class="mp-kpi-value" style="color:#fff;font-weight:800;font-size:30px;text-shadow:0 1px 3px rgba(0,0,0,0.2);"><?= $expired_count; ?></div>
                  <div class="mp-kpi-sub" style="color:rgba(255,255,255,0.95);font-weight:700;font-size:14.5px;"><i class="fa fa-calendar-times-o"></i> Action Required</div>
                </div>
              </a>
              <a href="<?= base_url('expired_items_report'); ?>" style="text-decoration:none;">
                <div class="mp-kpi-card" style="background:linear-gradient(135deg,#F59E0B 0%,#D97706 100%);color:#fff;min-height:auto;">
                  <div class="mp-kpi-label" style="color:rgba(255,255,255,0.95);font-weight:700;font-size:16px;">Expiring Soon</div>
                  <div class="mp-kpi-value" style="color:#fff;font-weight:800;font-size:30px;text-shadow:0 1px 3px rgba(0,0,0,0.2);"><?= $expiring_count; ?></div>
                  <div class="mp-kpi-sub" style="color:rgba(255,255,255,0.95);font-weight:700;font-size:14.5px;"><i class="fa fa-clock-o"></i> Within <?= $exp_settings->alert_before_days; ?> Days</div>
                </div>
              </a>
              <a href="<?= base_url('expiry_settings'); ?>" style="text-decoration:none;">
                <div class="mp-kpi-card" style="background:linear-gradient(135deg,#10B981 0%,#059669 100%);color:#fff;min-height:auto;">
                  <div class="mp-kpi-label" style="color:rgba(255,255,255,0.95);font-weight:700;font-size:16px;">Alert Window</div>
                  <div class="mp-kpi-value" style="color:#fff;font-weight:800;font-size:30px;text-shadow:0 1px 3px rgba(0,0,0,0.2);"><?= $exp_settings->alert_before_days; ?> <span style="font-size:16px;font-weight:600;">days</span></div>
                  <div class="mp-kpi-sub" style="color:rgba(255,255,255,0.95);font-weight:700;font-size:14.5px;"><i class="fa fa-bell-o"></i> Early Warning</div>
                </div>
              </a>
              <a href="<?= base_url('expiry_settings'); ?>" style="text-decoration:none;">
                <div class="mp-kpi-card" style="background:#FFFFFF;border:1px solid #E2E8F0;min-height:auto;">
                  <div class="mp-kpi-label" style="font-size:15px;color:#64748B;font-weight:600;">Configure</div>
                  <div class="mp-kpi-value" style="font-size:22px;font-weight:700;color:#1E293B;"><i class="fa fa-sliders text-blue"></i> Settings</div>
                  <div class="mp-kpi-sub" style="font-size:14px;font-weight:600;color:#3B82F6;">Adjust Alerts &rarr;</div>
                </div>
              </a>
            </div>
          </div>
        </div>
      </div>
      <?php
        endif; // total_alerted > 0
        endif; // mp_feature_enabled('expiry_tracking')
      } catch (Exception $e) { /* Expiry table not ready yet */ }
      ?>

      <!-- SECTION 3: BUSINESS PERFORMANCE (hidden - moved filter to Business Overview) -->

      <!-- SECTION 4: INSIGHTS + ACTIVITIES + CASH -->
      <div class="mp-section mp-section--secondary">
        <div class="mp-content-grid equal">
          <div class="mp-card mp-insights-card">
            <div class="mp-card-header"><div class="mp-card-title">MartPoint Insights</div></div>
            <div class="mp-card-body">
              <?php if(!empty($insights)){ ?>
              <ul class="mp-insight-list">
                <?php foreach($insights as $insight){ ?><li class="mp-insight-item"><?= $insight ?></li><?php } ?>
              </ul>
              <?php } else { ?><div class="mp-empty-state">Keep selling to receive business insights.</div><?php } ?>
            </div>
          </div>
          <div>
            <div class="mp-card">
              <div class="mp-card-header"><div class="mp-card-title">Recent Activities</div></div>
              <div class="mp-card-body">
                <?php if(!empty($recent_activities)){ ?>
                <ul class="mp-activity-list">
                  <?php foreach($recent_activities as $act){ ?>
                  <li class="mp-activity-item">
                    <div class="mp-activity-icon <?= $act['type'] ?>">
                      <?php if($act['type']=='sale'){ ?><i class="fa fa-shopping-cart"></i>
                      <?php } elseif($act['type']=='customer'){ ?><i class="fa fa-user"></i>
                      <?php } else { ?><i class="fa fa-cube"></i><?php } ?>
                    </div>
                    <div class="mp-activity-text"><div class="title"><?= $act['title'] ?></div><div class="meta"><?= $act['amount'] > 0 ? $CI->currency($act['amount']).' &middot; ' : '' ?><?= show_date($act['date']) ?></div></div>
                  </li>
                  <?php } ?>
                </ul>
                <?php } else { ?><div class="mp-empty-state">No recent activity</div><?php } ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Original Stock Alert Table (preserved) -->
      <?php if($is_product_business && $CI->permissions('dashboard_stock_alert') && !is_user()) { ?>
      <div class="mp-section mp-section--secondary">
        <div class="mp-card">
          <div class="mp-card-header"><div class="mp-card-title"><?= $this->lang->line('stock_alert'); ?></div></div>
          <div class="mp-card-body" style="padding:0;">
            <table id="example2" class="table table-bordered table-hover" style="margin:0;"><thead><tr class='bg-warning'><th>#</th><th><?= $this->lang->line('item_name'); ?></th><th><?= $this->lang->line('category_name'); ?></th><th><?= $this->lang->line('brand_name'); ?></th><th><?= $this->lang->line('stock'); ?></th></tr></thead><tbody></tbody></table>
          </div>
        </div>
      </div>
      <?php } ?>

      <!-- Original Trending Items + Recent Sales (preserved) -->
      <?php if(!is_user()){ ?>
      <div class="mp-section mp-section--secondary">
        <div class="mp-content-grid equal">
          <?php if($CI->permissions('dashboard_trending_items_chart')){ ?>
          <div class="mp-card">
            <div class="mp-card-header"><div class="mp-card-title"><?= $this->lang->line('top_10_trending_items'); ?></div></div>
            <div class="mp-card-body"><canvas id="doughnut-chart" width="100%" height="200"></canvas></div>
          </div>
          <?php } ?>
          <?php if($CI->permissions('recent_sales_invoice_list')){ ?>
          <div class="mp-card">
            <div class="mp-card-header"><div class="mp-card-title"><?= $this->lang->line('recentl_sales_invoices'); ?></div></div>
            <div class="mp-card-body" style="padding:0;">
              <div class="table-responsive">
                <table class="table" style="margin:0;"><thead><tr><th>Sl.No</th><th><?= $this->lang->line('date'); ?></th><th><?= $this->lang->line('invoice_id'); ?></th><th><?= $customer_label; ?></th><th><?= $this->lang->line('total'); ?></th><th><?= $this->lang->line('status'); ?></th></tr></thead><tbody>
                <?php $i=1; if(!is_admin() && !is_store_admin()){ $this->db->where("created_by",$this->session->userdata('inv_username')); } $this->db->select("*")->where("store_id",get_current_store_id())->from("db_sales"); if(!empty($selected_branch)){ $this->db->where("warehouse_id", $selected_branch); } $this->db->order_by('id','desc')->limit(10); $q5=$this->db->get(); if($q5->num_rows() >0){ foreach($q5->result() as $res5){ ?><tr><td><?php echo $i++; ?></td><td><?php echo show_date($res5->sales_date); ?></td><td><?php echo $res5->sales_code; ?></td><td><?php echo get_customer_details($res5->customer_id)->customer_name; ?></td><td><?php echo $CI->currency($res5->grand_total,$with_comma=true); ?></td><td><?php echo $res5->payment_status; ?></td></tr><?php } } else { ?><tr><td colspan="6" class="text-center text-muted">No sales invoices for this branch</td></tr><?php } ?></tbody></table>
              </div>
            </div>
          </div>
          <?php } ?>
        </div>
      </div>
      <?php } ?>

      <!-- Admin Store Details (preserved) -->
      <?php if(is_admin() && store_module()){ ?>
      <div class="mp-section mp-section--secondary">
        <div class="mp-card">
          <div class="mp-card-header"><div class="mp-card-title"><?= $this->lang->line('stores_details'); ?></div></div>
          <div class="mp-card-body" style="padding:0;">
            <div class="table-responsive">
              <table id="stores_details" class="table" style="margin:0;"><thead><tr><th>#</th><th><?= $this->lang->line('store_name'); ?></th><th><?= $this->lang->line('total_sales'); ?></th><th><?= $this->lang->line('total_expense'); ?></th><th><?= $this->lang->line('sales_due'); ?></th></tr></thead><tbody><?= $CI->get_storewise_details(); ?></tbody></table>
            </div>
          </div>
        </div>
      </div>
      <?php } ?>

    </div><!-- /mp-dashboard-wrapper -->
    </section><!-- /.content -->
  </div><!-- /.content-wrapper -->

  <?php $this->load->view('footer'); ?>
  <div class="control-sidebar-bg"></div>
</div><!-- ./wrapper -->

<!-- SOUND CODE -->
<?php include"comman/code_js_sound.php"; ?>
<!-- TABLES CODE -->
<?php include"comman/code_js.php"; ?>

<!-- ChartJS 1.0.1 -->
<script src="<?php echo $theme_link; ?>plugins/chartjs/Chart.min.js"></script>
<script>
'use strict';
window.chartColors = {
  red: 'rgb(255, 50, 10)', orange: 'rgb(255, 102, 64)', yellow: 'rgb(230, 184, 0)',
  green: 'rgb(0, 179, 0)', blue: 'rgb(0, 0, 230)', purple: 'rgb(134, 0, 179)', grey: 'rgb(117, 117, 163)'
};
(function(global) {
  var MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
  var COLORS = ['#4dc9f6','#f67019','#f53794','#537bc4','#acc236','#166a8f','#00a950','#58595b','#8549ba'];
  var Samples = global.Samples || (global.Samples = {});
  var Color = global.Color;
  Samples.utils = {
    srand: function(seed) { this._seed = seed; },
    rand: function(min, max) { var seed = this._seed; min = min === undefined ? 0 : min; max = max === undefined ? 1 : max; this._seed = (seed * 9301 + 49297) % 233280; return min + (this._seed / 233280) * (max - min); },
    numbers: function(config) { var cfg = config || {}; var min = cfg.min || 0, max = cfg.max || 1; var from = cfg.from || [], count = cfg.count || 8; var decimals = cfg.decimals || 8; var dfactor = Math.pow(10, decimals) || 0; var data = []; for (var i = 0; i < count; ++i) { var value = (from[i] || 0) + this.rand(min, max); if (this.rand() <= (cfg.continuity || 1)) { data.push(Math.round(dfactor * value) / dfactor); } else { data.push(null); } } return data; },
    labels: function(config) { var cfg = config || {}; var min = cfg.min || 0, max = cfg.max || 100; var count = cfg.count || 8; var step = (max - min) / count; var decimals = cfg.decimals || 8; var dfactor = Math.pow(10, decimals) || 0; var values = []; for (var i = min; i < max; i += step) { values.push('' + Math.round(dfactor * i) / dfactor); } return values; },
    months: function(config) { var cfg = config || {}; var count = cfg.count || 12; var section = cfg.section; var values = []; for (var i = 0; i < count; ++i) { var value = MONTHS[Math.ceil(i) % 12]; values.push(value.substring(0, section)); } return values; },
    color: function(index) { return COLORS[index % COLORS.length]; },
    transparentize: function(color, opacity) { var alpha = opacity === undefined ? 0.5 : 1 - opacity; return Color(color).alpha(alpha).rgbString(); }
  };
  window.randomScalingFactor = function() { return Math.round(Samples.utils.rand(-100, 100)); };
  Samples.utils.srand(Date.now());
}(this));

<?php if(is_user()){ ?>
  function createConfig(position) {
    return {
      type: 'line',
      data: {
        labels: ["<?=$sub_month[6].'-'.$sub_year[6]?>","<?=$sub_month[5].'-'.$sub_year[5]?>","<?=$sub_month[4].'-'.$sub_year[4]?>","<?=$sub_month[3].'-'.$sub_year[3]?>","<?=$sub_month[2].'-'.$sub_year[2]?>","<?=$sub_month[1].'-'.$sub_year[1]?>","<?=$sub_month[0].'-'.$sub_year[0]?>"],
        datasets: [{ label: 'Total Subscriptions', borderColor: window.chartColors.red, backgroundColor: window.chartColors.red, data: ["<?=$tot_subscribes[6]?>","<?=$tot_subscribes[5]?>","<?=$tot_subscribes[4]?>","<?=$tot_subscribes[3]?>","<?=$tot_subscribes[2]?>","<?=$tot_subscribes[1]?>","<?=$tot_subscribes[0]?>"], fill: false }]
      },
      options: { responsive: true, title: { display: true, text: 'Tooltip Position: ' + position }, tooltips: { position: position, mode: 'index', intersect: false } }
    };
  }
  window.onload = function() {
    var container = document.querySelector('.subscription_chart');
    ['average'].forEach(function(position) {
      var div = document.createElement('div'); div.classList.add('chart-container');
      var canvas = document.createElement('canvas'); div.appendChild(canvas); container.appendChild(div);
      var ctx = canvas.getContext('2d'); var config = createConfig(position); new Chart(ctx, config);
    });
  };
<?php } ?>

<?php if(!is_user()){ ?>
$(function(){
  var canvas = document.getElementById('salesTrendChart') || document.querySelector('.bar-chartcanvas');
  if(canvas && canvas.getContext){
    var ctx = canvas.getContext('2d');
    var data = {
      labels: <?= json_encode(array_reverse($month)); ?>,
      datasets: [
        { label: "<?= $this->lang->line('purchase'); ?>", data: <?= json_encode(array_reverse($purchase)); ?>, backgroundColor: "rgba(239,68,68,0.7)", borderColor: "rgba(239,68,68,1)", borderWidth: 0, borderRadius: 6, borderSkipped: false },
        { label: "<?= $this->lang->line('sales'); ?>", data: <?= json_encode(array_reverse($sales)); ?>, backgroundColor: "rgba(16,185,129,0.7)", borderColor: "rgba(16,185,129,1)", borderWidth: 0, borderRadius: 6, borderSkipped: false },
        { label: "<?= $this->lang->line('expense'); ?>", data: <?= json_encode(array_reverse($expense)); ?>, backgroundColor: "rgba(59,130,246,0.7)", borderColor: "rgba(59,130,246,1)", borderWidth: 0, borderRadius: 6, borderSkipped: false }
      ]
    };
    var options = {
      responsive: true,
      maintainAspectRatio: false,
      interaction: { mode: 'index', intersect: false },
      plugins: {
        legend: {
          display: true,
          position: "top",
          align: "end",
          labels: { color: "#475569", font: { size: 12, weight: 600 }, usePointStyle: true, pointStyle: "circle", padding: 20, boxWidth: 8 }
        },
        tooltip: {
          backgroundColor: "#0F172A",
          titleColor: "#FFFFFF",
          bodyColor: "#FFFFFF",
          titleFont: { size: 13, weight: 600, family: "'Inter', sans-serif" },
          bodyFont: { size: 13, family: "'Inter', sans-serif" },
          padding: 12,
          cornerRadius: 8,
          displayColors: true,
          boxWidth: 8,
          boxHeight: 8,
          boxPadding: 4,
          usePointStyle: true
        }
      },
      scales: {
        x: {
          grid: { display: false, drawBorder: false },
          ticks: { color: "#64748B", font: { size: 11, family: "'Inter', sans-serif" } }
        },
        y: {
          grid: { color: "rgba(226,232,240,0.6)", drawBorder: false, borderDash: [4,4] },
          ticks: { color: "#64748B", font: { size: 11, family: "'Inter', sans-serif" }, beginAtZero: true, callback: function(value){ return value >= 1000 ? (value/1000).toFixed(0)+'k' : value; } }
        }
      }
    };
    new Chart(ctx, { type: "bar", data: data, options: options });
  }
});

new Chart(document.getElementById("doughnut-chart"), {
  type: 'doughnut',
  data: {
    labels: [<?php if($tranding_item['tot_rec'] > 0){?><?php for($i=$tranding_item['tot_rec']; $i>0; $i--){ ?>'<?= $tranding_item[$i]['name'] ?>',<?php } ?><?php } ?>],
    datasets: [{ label: "Top Items", backgroundColor: ["#2563EB","#F97316","#16A34A","#F59E0B","#DC2626","#8B5CF6"], borderWidth: 0, hoverOffset: 4, data: [<?php if($tranding_item['tot_rec'] > 0){?><?php for($i=$tranding_item['tot_rec']; $i>0; $i--){ ?>'<?= $tranding_item[$i]['sales_qty'] ?>',<?php } ?><?php } ?>] }]
  },
  options: { cutoutPercentage: 60, legend: { position: 'bottom', labels: { padding: 16, usePointStyle: true, fontColor: '#64748B', fontSize: 12, fontFamily: "-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif" } }, title: { display: false }, tooltips: { backgroundColor: '#0F172A', titleFontSize: 13, bodyFontSize: 13, cornerRadius: 8, xPadding: 12, yPadding: 12, displayColors: true, boxWidth: 10, boxHeight: 10 } }
});
<?php } ?>
</script>

<script type="text/javascript">
  var base_url='<?= base_url(); ?>';
  <?php if(is_admin() && store_module()){ ?> $("#stores_details").DataTable(); <?php } ?>
  $(document).ready(function() {
    $('#example2').DataTable({
      dom:'<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10 "B>>>tip',
      buttons: { buttons: [
        { className: 'btn bg-red color-palette btn-flat hidden delete_btn pull-left', text: 'Delete', action: function(e,dt,node,config){ multi_delete(); } },
        { extend: 'copy', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [0,1,2,3,4]} },
        { extend: 'excel', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [0,1,2,3,4]} },
        { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [0,1,2,3,4]} },
        { extend: 'print', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [0,1,2,3,4]} },
        { extend: 'csv', className: 'btn bg-teal color-palette btn-flat',exportOptions: { columns: [0,1,2,3,4]} },
        { extend: 'colvis', className: 'btn bg-teal color-palette btn-flat',text:'Columns' },
      ]},
      "processing": true, "serverSide": true, "order": [], "responsive": false,
      language: { processing: '<div class="text-primary bg-primary" style="position: relative;z-index:100;overflow: visible;">Processing...</div>' },
      "ajax": { "url": "<?php echo site_url('dashboard/ajax_list')?>", "type": "POST", complete: function (data) {} },
      "columnDefs": [{ "orderable": false }]
    });
  });
</script>
<script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
</body>
</html>
