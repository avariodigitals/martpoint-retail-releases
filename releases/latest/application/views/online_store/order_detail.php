<!DOCTYPE html>
<html>
<head>
<?php $this->load->view('comman/code_css.php');?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar');?>

  <div class="content-wrapper">
    <?php
      $CI =& get_instance();
      $statusLabels = [
        'pending' => 'label-warning', 'paid' => 'label-success', 'processing' => 'label-info',
        'ready' => 'label-primary', 'completed' => 'label-success', 'cancelled' => 'label-danger'
      ];
      $paymentLabels = [
        'unpaid' => 'label-warning', 'paid' => 'label-success', 'partially_paid' => 'label-info',
        'failed' => 'label-danger', 'refunded' => 'label-default'
      ];
    ?>
    <section class="content-header" style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;">
      <div>
        <h1><?= $page_title ?></h1>
        <ol class="breadcrumb" style="margin:0;">
          <li><a href="<?=base_url('dashboard');?>"><i class="fa fa-dashboard"></i> Home</a></li>
          <li><a href="<?=base_url('online_store/orders');?>">Online Orders</a></li>
          <li class="active"><?= $page_title; ?></li>
        </ol>
      </div>
      <div style="display:flex;gap:8px;align-items:center;">
        <span class="label <?= $statusLabels[$order->order_status] ?? 'label-default'; ?>" style="font-size:13px;padding:6px 12px;"><?= ucfirst($order->order_status); ?></span>
        <span class="label <?= $paymentLabels[$order->payment_status] ?? 'label-default'; ?>" style="font-size:13px;padding:6px 12px;"><?= ucfirst(str_replace('_',' ',$order->payment_status)); ?></span>
      </div>
    </section>

    <section class="content">
      <div class="row">
        <div class="col-md-8">
          <div class="box box-primary">
            <div class="box-header with-border">
              <h3 class="box-title">Order Details</h3>
            </div>
            <div class="box-body">
              <table class="table table-bordered">
                <tr><th style="width:160px;">Order Code</th><td><?= $order->order_code; ?></td></tr>
                <tr><th>Customer</th><td><?= htmlspecialchars($order->customer_name); ?></td></tr>
                <tr><th>Email</th><td><?= htmlspecialchars($order->customer_email); ?></td></tr>
                <tr><th>Phone</th><td><?= htmlspecialchars($order->customer_phone); ?></td></tr>
                <tr><th>Address</th><td><?= nl2br(htmlspecialchars($order->customer_address)); ?></td></tr>
                <tr><th>Order Type</th><td><?= ucfirst($order->order_type); ?></td></tr>
                <tr><th>Payment Method</th><td><?= ucfirst(str_replace('_',' ',$order->payment_method)); ?></td></tr>
                <?php if($order->shipping_method): ?>
                <tr><th>Shipping Method</th><td><?= htmlspecialchars($order->shipping_method); ?></td></tr>
                <?php endif; ?>
                <?php if($order->paystack_reference): ?>
                <tr><th>Paystack Ref</th><td><code><?= $order->paystack_reference; ?></code></td></tr>
                <?php endif; ?>
                <?php if($order->service_date): ?>
                <tr><th>Service Date</th><td><?= show_date($order->service_date); ?> <?= $order->service_time; ?></td></tr>
                <?php endif; ?>
                <?php if($order->service_note): ?>
                <tr><th>Service Note</th><td><?= nl2br(htmlspecialchars($order->service_note)); ?></td></tr>
                <?php endif; ?>
                <?php if($order->table_number): ?>
                <tr><th>Table</th><td><?= htmlspecialchars($order->table_number); ?></td></tr>
                <?php endif; ?>
                <tr><th>IP Address</th><td><?= $order->ip_address; ?></td></tr>
                <tr><th>Placed At</th><td><?= show_date($order->created_at); ?> <?= date('H:i:s', strtotime($order->created_at)); ?></td></tr>
              </table>

              <h4 style="margin-top:20px;">Items</h4>
              <table class="table table-bordered">
                <thead>
                  <tr><th>Item</th><th>Type</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr>
                </thead>
                <tbody>
                  <?php foreach($items as $it): ?>
                  <tr>
                    <td><?= htmlspecialchars($it->item_name); ?></td>
                    <td><?= ucfirst($it->item_type); ?></td>
                    <td><?= $it->qty; ?></td>
                    <td><?= $CI->currency($it->unit_price); ?></td>
                    <td><?= $CI->currency($it->total_price); ?></td>
                  </tr>
                  <?php endforeach; ?>
                </tbody>
                <tfoot>
                  <tr><th colspan="4" class="text-right">Subtotal</th><th><?= $CI->currency($order->subtotal); ?></th></tr>
                  <?php if($order->delivery_fee > 0): ?>
                  <tr><th colspan="4" class="text-right"><?= $order->shipping_method ? 'Shipping Fee' : 'Delivery Fee'; ?></th><th><?= $CI->currency($order->delivery_fee); ?></th></tr>
                  <?php endif; ?>
                  <tr><th colspan="4" class="text-right">Grand Total</th><th><?= $CI->currency($order->grand_total); ?></th></tr>
                </tfoot>
              </table>
            </div>
          </div>
        </div>

        <div class="col-md-4">
          <!-- Order Summary first so it is always visible -->
          <div class="box box-default">
            <div class="box-header with-border">
              <h3 class="box-title">Order Summary</h3>
            </div>
            <div class="box-body">
              <div class="text-center" style="padding:20px;">
                <div style="font-size:28px;font-weight:700;color:#3B82F6;"><?= $CI->currency($order->grand_total); ?></div>
                <div style="font-size:13px;color:#64748B;margin-top:4px;">Grand Total</div>
              </div>
            </div>
          </div>

          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title">Actions</h3>
            </div>
            <div class="box-body">
              <div class="form-group">
                <label>Update Order Status</label>
                <select class="form-control" id="order_status" onchange="updateStatus('order')">
                  <option value="pending" <?= $order->order_status=='pending'?'selected':''; ?>>Pending</option>
                  <option value="paid" <?= $order->order_status=='paid'?'selected':''; ?>>Paid</option>
                  <option value="processing" <?= $order->order_status=='processing'?'selected':''; ?>>Processing</option>
                  <option value="ready" <?= $order->order_status=='ready'?'selected':''; ?>>Ready</option>
                  <option value="completed" <?= $order->order_status=='completed'?'selected':''; ?>>Completed</option>
                  <option value="cancelled" <?= $order->order_status=='cancelled'?'selected':''; ?>>Cancelled</option>
                </select>
              </div>
              <div class="form-group">
                <label>Update Payment Status</label>
                <select class="form-control" id="payment_status" onchange="updateStatus('payment')">
                  <option value="unpaid" <?= $order->payment_status=='unpaid'?'selected':''; ?>>Unpaid</option>
                  <option value="paid" <?= $order->payment_status=='paid'?'selected':''; ?>>Paid</option>
                  <option value="partially_paid" <?= $order->payment_status=='partially_paid'?'selected':''; ?>>Partially Paid</option>
                  <option value="failed" <?= $order->payment_status=='failed'?'selected':''; ?>>Failed</option>
                  <option value="refunded" <?= $order->payment_status=='refunded'?'selected':''; ?>>Refunded</option>
                </select>
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
<script>
function updateStatus(type){
  var url = type === 'order' ? '<?=base_url("online_store/update_order_status");?>' : '<?=base_url("online_store/update_payment_status");?>';
  var status = type === 'order' ? $('#order_status').val() : $('#payment_status').val();
  $.post(url, {
    order_id: <?= $order->id; ?>,
    status: status,
    <?= $this->security->get_csrf_token_name(); ?>: '<?= $this->security->get_csrf_hash(); ?>'
  }, function(res){
    if(res.status === 'success'){
      toastr.success(res.message);
      setTimeout(function(){ location.reload(); }, 800);
    } else {
      toastr.error(res.message);
    }
  }, 'json');
}
</script>
<script>$(".online-store-orders-active-li").addClass("active");</script>
</body>
</html>
