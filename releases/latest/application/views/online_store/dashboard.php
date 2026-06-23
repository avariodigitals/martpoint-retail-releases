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
      <ol class="breadcrumb">
        <li><a href="<?=base_url('dashboard');?>"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active"><?= $page_title; ?></li>
      </ol>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-aqua">
            <div class="inner">
              <h3><?= $stats['total_orders']; ?></h3>
              <p>Today's Orders</p>
            </div>
            <div class="icon"><i class="fa fa-shopping-cart"></i></div>
          </div>
        </div>
        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-green">
            <div class="inner">
              <h3><?= store_number_format($stats['total_revenue']); ?></h3>
              <p>Today's Revenue</p>
            </div>
            <div class="icon"><i class="fa fa-money"></i></div>
          </div>
        </div>
        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-yellow">
            <div class="inner">
              <h3><?= $stats['pending_orders']; ?></h3>
              <p>Pending Orders</p>
            </div>
            <div class="icon"><i class="fa fa-clock-o"></i></div>
          </div>
        </div>
        <div class="col-lg-3 col-xs-6">
          <div class="small-box bg-blue">
            <div class="inner">
              <h3><?= $stats['paid_orders']; ?></h3>
              <p>Paid Orders</p>
            </div>
            <div class="icon"><i class="fa fa-check-circle"></i></div>
          </div>
        </div>
      </div>

      <div class="row">
        <div class="col-md-8">
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Recent Orders</h3>
              <div class="box-tools"><a href="<?=base_url('online_store/orders');?>" class="btn btn-sm btn-default">View All</a></div>
            </div>
            <div class="box-body table-responsive">
              <table class="table table-bordered table-striped">
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
        <div class="col-md-4">
          <div class="box box-success">
            <div class="box-header with-border">
              <h3 class="box-title">Top Online Products</h3>
            </div>
            <div class="box-body">
              <?php if(!empty($top_products)): ?>
              <ul class="products-list product-list-in-box">
                <?php foreach($top_products as $tp): ?>
                <li class="item">
                  <div class="product-img">
                    <?php if($tp->item_image && file_exists($tp->item_image)): ?>
                      <img src="<?= base_url($tp->item_image); ?>" alt="" style="width:50px;height:50px;object-fit:cover;">
                    <?php else: ?>
                      <div style="width:50px;height:50px;background:#F1F5F9;border-radius:4px;"></div>
                    <?php endif; ?>
                  </div>
                  <div class="product-info">
                    <span class="product-title"><?= htmlspecialchars($tp->item_name); ?></span>
                    <span class="product-description"><?= (int)$tp->total_qty; ?> sold &middot; <?= store_number_format($tp->total_revenue); ?></span>
                  </div>
                </li>
                <?php endforeach; ?>
              </ul>
              <?php else: ?>
              <p class="text-muted text-center">No data yet.</p>
              <?php endif; ?>
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
