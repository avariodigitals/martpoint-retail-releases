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
        <li><a href="<?=base_url('online_store');?>">Online Store</a></li>
        <li class="active"><?= $page_title; ?></li>
      </ol>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Orders</h3>
              <div class="box-tools">
                <div class="btn-group">
                  <a href="<?=base_url('online_store/orders');?>" class="btn btn-default btn-sm <?= !$current_status ? 'active' : '' ?>">All</a>
                  <a href="<?=base_url('online_store/orders?status=pending');?>" class="btn btn-default btn-sm <?= $current_status=='pending' ? 'active' : '' ?>">Pending</a>
                  <a href="<?=base_url('online_store/orders?status=paid');?>" class="btn btn-default btn-sm <?= $current_status=='paid' ? 'active' : '' ?>">Paid</a>
                  <a href="<?=base_url('online_store/orders?status=processing');?>" class="btn btn-default btn-sm <?= $current_status=='processing' ? 'active' : '' ?>">Processing</a>
                  <a href="<?=base_url('online_store/orders?status=ready');?>" class="btn btn-default btn-sm <?= $current_status=='ready' ? 'active' : '' ?>">Ready</a>
                  <a href="<?=base_url('online_store/orders?status=completed');?>" class="btn btn-default btn-sm <?= $current_status=='completed' ? 'active' : '' ?>">Completed</a>
                  <a href="<?=base_url('online_store/orders?status=cancelled');?>" class="btn btn-default btn-sm <?= $current_status=='cancelled' ? 'active' : '' ?>">Cancelled</a>
                </div>
              </div>
            </div>
            <div class="box-body table-responsive">
              <table class="table table-bordered table-striped">
                <thead>
                  <tr>
                    <th>Order #</th>
                    <th>Date</th>
                    <th>Customer</th>
                    <th>Phone</th>
                    <th>Type</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Action</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if(!empty($orders)): foreach($orders as $o): ?>
                  <tr>
                    <td><a href="<?=base_url('online_store/order/'.$o->id);?>"><strong><?= $o->order_code; ?></strong></a></td>
                    <td><?= show_date($o->created_at); ?> <small class="text-muted"><?= date('H:i', strtotime($o->created_at)); ?></small></td>
                    <td><?= htmlspecialchars($o->customer_name); ?></td>
                    <td><?= htmlspecialchars($o->customer_phone); ?></td>
                    <?php
                      $statusLabels = [
                        'pending' => 'label-warning', 'paid' => 'label-success', 'processing' => 'label-info',
                        'ready' => 'label-primary', 'completed' => 'label-success', 'cancelled' => 'label-danger'
                      ];
                      $paymentLabels = [
                        'unpaid' => 'label-warning', 'paid' => 'label-success', 'partially_paid' => 'label-info',
                        'failed' => 'label-danger', 'refunded' => 'label-default'
                      ];
                    ?>
                    <td><span class="label label-default"><?= ucfirst($o->order_type); ?></span></td>
                    <td><?= store_number_format($o->grand_total); ?></td>
                    <td><span class="label <?= $paymentLabels[$o->payment_status] ?? 'label-default'; ?>"><?= ucfirst(str_replace('_',' ',$o->payment_status)); ?></span></td>
                    <td><span class="label <?= $statusLabels[$o->order_status] ?? 'label-default'; ?>"><?= ucfirst($o->order_status); ?></span></td>
                    <td>
                      <a href="<?=base_url('online_store/order/'.$o->id);?>" class="btn btn-xs btn-primary"><i class="fa fa-eye"></i> View</a>
                    </td>
                  </tr>
                  <?php endforeach; else: ?>
                  <tr><td colspan="9" class="text-center text-muted">No orders found.</td></tr>
                  <?php endif; ?>
                </tbody>
              </table>
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
<script>$(".online-store-orders-active-li").addClass("active");</script>
</body>
</html>
