<!DOCTYPE html>
<html>
<head>
<?php $this->load->view('comman/code_css.php');?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar');?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1><?= $page_title ?></h1>
    </section>

    <section class="content">
      <div class="mp-dashboard-wrapper">

        <!-- Business Overview -->
        <div class="mp-section">
          <div class="mp-section-title">Business Overview</div>
          <div class="mp-kpi-grid">
            <div class="mp-kpi-card sales">
              <div class="mp-kpi-label">Today's Orders</div>
              <div class="mp-kpi-value"><?= $stats['total_orders']; ?></div>
            </div>
            <div class="mp-kpi-card profit">
              <div class="mp-kpi-label">Today's Revenue</div>
              <div class="mp-kpi-value"><?= store_number_format($stats['total_revenue']); ?></div>
            </div>
            <div class="mp-kpi-card debt">
              <div class="mp-kpi-label">Pending Orders</div>
              <div class="mp-kpi-value"><?= $stats['pending_orders']; ?></div>
            </div>
            <div class="mp-kpi-card stock">
              <div class="mp-kpi-label">Paid Orders</div>
              <div class="mp-kpi-value"><?= $stats['paid_orders']; ?></div>
            </div>
          </div>
        </div>

        <!-- Recent Orders + Top Online Products -->
        <div class="mp-section">
          <div class="mp-content-grid">
            <div class="mp-card">
              <div class="mp-card-header">
                <div class="mp-card-title">Recent Orders</div>
                <a href="<?=base_url('online_store/orders');?>" class="btn btn-xs btn-default">View All</a>
              </div>
              <div class="mp-card-body" style="padding:0;">
                <div class="table-responsive">
                  <table class="table" style="margin:0;">
                    <thead><tr><th>Order #</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr></thead>
                    <tbody>
                      <?php foreach($recent_orders as $o): ?>
                      <tr>
                        <td><a href="<?=base_url('online_store/order/'.$o->id);?>"><?= $o->order_code; ?></a></td>
                        <td><?= htmlspecialchars($o->customer_name); ?></td>
                        <td><?= store_number_format($o->grand_total); ?></td>
                        <td><span class="label label-default"><?= ucfirst($o->order_status); ?></span></td>
                        <td><?= show_date($o->created_at); ?></td>
                      </tr>
                      <?php endforeach; ?>
                      <?php if(empty($recent_orders)): ?>
                      <tr><td colspan="5" class="text-center text-muted">No orders yet.</td></tr>
                      <?php endif; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>

            <div class="mp-card">
              <div class="mp-card-header"><div class="mp-card-title">Top Online Products</div></div>
              <div class="mp-card-body">
                <?php if(!empty($top_products)): ?>
                <ul class="mp-product-list">
                  <?php $i=1; foreach($top_products as $tp): ?>
                  <li class="mp-product-item">
                    <span class="mp-product-rank <?= $i <= 3 ? 'top' : '' ?>"><?= $i; ?></span>
                    <span class="mp-product-name">
                      <?= htmlspecialchars($tp->item_name); ?>
                      <span class="mp-kpi-sub" style="display:block;font-weight:500;color:#94A3B8;margin-top:2px;"><?= (int)$tp->total_qty; ?> sold</span>
                    </span>
                    <span class="mp-product-revenue"><?= store_number_format($tp->total_revenue); ?></span>
                  </li>
                  <?php $i++; endforeach; ?>
                </ul>
                <?php else: ?>
                <div class="mp-empty-state">No data yet.</div>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

      </div>
    </section>
  </div>

  <?php $this->load->view('footer'); ?>
  <div class="control-sidebar-bg"></div>
</div>

<?php $this->load->view('comman/code_js_sound.php');?>
<?php $this->load->view('comman/code_js.php');?>
<script>$(".online-store-active-li").addClass("active");</script>
</body>
</html>
