<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.os-content-grid{display:grid!important;grid-template-columns:minmax(0,1.6fr) minmax(0,1fr)!important;gap:20px!important}
@media(max-width:1024px){.os-content-grid{grid-template-columns:1fr!important}}
.os-card{background:var(--mp-surface)!important;border:1px solid var(--mp-border)!important;border-radius:16px!important;box-shadow:var(--mp-shadow-sm)!important;overflow:hidden!important}
.os-card-head{display:flex!important;align-items:center!important;justify-content:space-between!important;padding:16px 20px!important;border-bottom:1px solid var(--mp-border)!important}
.os-card-head h3{font-size:14px!important;font-weight:700!important;margin:0!important;color:var(--mp-text)!important}
.os-card-head a{font-size:12px!important;font-weight:600!important;color:var(--mp-primary)!important;text-decoration:none!important}
.os-card-body{padding:0!important}
.os-mini-table{width:100%!important;border-collapse:collapse!important;font-size:13px!important}
.os-mini-table th{font-size:11px!important;text-transform:uppercase!important;font-weight:700!important;color:var(--mp-muted)!important;letter-spacing:.06em!important;padding:12px 20px!important;border-bottom:1px solid var(--mp-border)!important;background:var(--mp-bg)!important;text-align:left!important}
.os-mini-table td{padding:12px 20px!important;border-bottom:1px solid var(--mp-border)!important;color:var(--mp-text)!important;vertical-align:middle!important}
.os-mini-table tr:last-child td{border-bottom:none!important}
.os-mini-table tr:hover td{background:var(--mp-bg)!important}
.os-mini-table a{color:var(--mp-primary)!important;font-weight:600!important;text-decoration:none!important}
.os-product-list{list-style:none!important;margin:0!important;padding:8px!important}
.os-product-item{display:flex!important;align-items:center!important;gap:12px!important;padding:10px 12px!important;border-radius:10px!important;transition:background .15s ease!important}
.os-product-item:hover{background:var(--mp-bg)!important}
.os-product-rank{width:28px!important;height:28px!important;border-radius:8px!important;background:var(--mp-bg)!important;color:var(--mp-muted)!important;font-size:12px!important;font-weight:700!important;display:flex!important;align-items:center!important;justify-content:center!important;flex-shrink:0!important}
.os-product-rank.top{background:rgba(0,87,255,.1)!important;color:var(--mp-primary)!important}
.os-product-name{flex:1!important;min-width:0!important;font-size:13px!important;font-weight:600!important;color:var(--mp-ink)!important}
.os-product-name .sub{display:block!important;font-size:11px!important;font-weight:500!important;color:var(--mp-muted)!important;margin-top:2px!important}
.os-product-revenue{font-size:13px!important;font-weight:700!important;color:var(--mp-text)!important;font-variant-numeric:tabular-nums!important}
.os-status-pill{font-size:11px!important;font-weight:700!important;padding:4px 10px!important;border-radius:20px!important;display:inline-flex!important;align-items:center!important;gap:5px!important;background:var(--mp-bg)!important;color:var(--mp-muted)!important}
.os-status-pill.pending{background:rgba(245,158,11,.1)!important;color:var(--mp-warning)!important}
.os-status-pill.paid{background:rgba(5,150,105,.1)!important;color:var(--mp-success)!important}
.os-status-pill.processing{background:rgba(0,87,255,.1)!important;color:var(--mp-primary)!important}
.os-status-pill.completed{background:rgba(5,150,105,.1)!important;color:var(--mp-success)!important}
.os-status-pill.cancelled{background:rgba(220,38,38,.1)!important;color:var(--mp-danger)!important}
.os-status-pill.ready{background:rgba(124,58,237,.1)!important;color:#7C3AED!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Today's online store performance at a glance</div>
  </div>
  <a href="<?= base_url('online_store/analytics'); ?>" class="mp-qa-btn blue"><i class="fa fa-bar-chart"></i> View Analytics</a>
</div>

<div class="mp-kpi-grid">
  <div class="mp-kpi-card sales">
    <div class="mp-kpi-icon"><i class="fa fa-shopping-cart"></i></div>
    <div class="mp-kpi-label">Today's Orders</div>
    <div class="mp-kpi-value"><?= (int)$stats['total_orders']; ?></div>
  </div>
  <div class="mp-kpi-card profit">
    <div class="mp-kpi-icon"><i class="fa fa-money"></i></div>
    <div class="mp-kpi-label">Today's Revenue</div>
    <div class="mp-kpi-value"><?= store_number_format($stats['total_revenue']); ?></div>
  </div>
  <div class="mp-kpi-card debt">
    <div class="mp-kpi-icon"><i class="fa fa-clock-o"></i></div>
    <div class="mp-kpi-label">Pending Orders</div>
    <div class="mp-kpi-value"><?= (int)$stats['pending_orders']; ?></div>
  </div>
  <div class="mp-kpi-card cash">
    <div class="mp-kpi-icon"><i class="fa fa-check-circle"></i></div>
    <div class="mp-kpi-label">Paid Orders</div>
    <div class="mp-kpi-value"><?= (int)$stats['paid_orders']; ?></div>
  </div>
</div>

<div class="os-content-grid">
  <div class="os-card">
    <div class="os-card-head">
      <h3>Recent Orders</h3>
      <a href="<?= base_url('online_store/orders'); ?>">View All <i class="fa fa-arrow-right"></i></a>
    </div>
    <div class="os-card-body">
      <div class="mp-dt-scroll">
        <table class="os-mini-table">
          <thead>
            <tr><th>Order #</th><th>Customer</th><th>Total</th><th>Status</th><th>Date</th></tr>
          </thead>
          <tbody>
            <?php foreach($recent_orders as $o): ?>
            <tr>
              <td><a href="<?= base_url('online_store/order/'.$o->id); ?>"><?= htmlspecialchars($o->order_code); ?></a></td>
              <td><?= htmlspecialchars($o->customer_name); ?></td>
              <td class="amt"><?= store_number_format($o->grand_total); ?></td>
              <td><span class="os-status-pill <?= htmlspecialchars($o->order_status); ?>"><?= ucfirst($o->order_status); ?></span></td>
              <td><?= show_date($o->created_at); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($recent_orders)): ?>
            <tr><td colspan="5" class="mp-empty-state">No orders yet.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="os-card">
    <div class="os-card-head"><h3>Top Online Products</h3></div>
    <div class="os-card-body">
      <?php if(!empty($top_products)): ?>
      <ul class="os-product-list">
        <?php $i=1; foreach($top_products as $tp): ?>
        <li class="os-product-item">
          <span class="os-product-rank <?= $i <= 3 ? 'top' : ''; ?>"><?= $i; ?></span>
          <span class="os-product-name">
            <?= htmlspecialchars($tp->item_name); ?>
            <span class="sub"><?= (int)$tp->total_qty; ?> sold</span>
          </span>
          <span class="os-product-revenue"><?= store_number_format($tp->total_revenue); ?></span>
        </li>
        <?php $i++; endforeach; ?>
      </ul>
      <?php else: ?>
      <div class="mp-empty-state">No data yet.</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>$(".online_store-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
