<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
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
<style>
.os-detail-grid{display:grid!important;grid-template-columns:minmax(0,1.6fr) minmax(0,1fr)!important;gap:20px!important}
@media(max-width:1024px){.os-detail-grid{grid-template-columns:1fr!important}}
.os-info-table{width:100%!important;border-collapse:collapse!important;font-size:13px!important}
.os-info-table th{font-size:12px!important;font-weight:700!important;color:var(--mp-muted)!important;padding:12px 20px!important;border-bottom:1px solid var(--mp-border)!important;text-align:left!important;width:170px!important;vertical-align:top!important;background:var(--mp-bg)!important}
.os-info-table td{padding:12px 20px!important;border-bottom:1px solid var(--mp-border)!important;color:var(--mp-text)!important;vertical-align:top!important}
.os-info-table tr:last-child th,.os-info-table tr:last-child td{border-bottom:none!important}
.os-summary-total{font-size:30px!important;font-weight:800!important;color:var(--mp-primary)!important;text-align:center!important;padding:24px 0 8px!important}
.os-summary-label{font-size:12px!important;color:var(--mp-muted)!important;text-align:center!important;text-transform:uppercase!important;letter-spacing:.05em!important}
.os-status-row{display:flex!important;gap:8px!important;align-items:center!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">
      <a href="<?= base_url('online_store/orders'); ?>"><i class="fa fa-arrow-left"></i> Back to Orders</a>
    </div>
  </div>
  <div class="os-status-row">
    <span class="label <?= $statusLabels[$order->order_status] ?? 'label-default'; ?>" style="font-size:13px;padding:6px 14px;"><?= ucfirst($order->order_status); ?></span>
    <span class="label <?= $paymentLabels[$order->payment_status] ?? 'label-default'; ?>" style="font-size:13px;padding:6px 14px;"><?= ucfirst(str_replace('_',' ',$order->payment_status)); ?></span>
  </div>
</div>

<div class="os-detail-grid">
  <div>
    <div class="mp-card-form">
      <div class="mp-card-head"><h3>Order Details</h3></div>
      <div class="mp-card-body" style="padding:0!important;">
        <table class="os-info-table">
          <tr><th>Order Code</th><td><?= htmlspecialchars($order->order_code); ?></td></tr>
          <tr><th>Customer</th><td><?= htmlspecialchars($order->customer_name); ?></td></tr>
          <tr><th>Email</th><td><?= htmlspecialchars($order->customer_email); ?></td></tr>
          <tr><th>Phone</th><td><?= htmlspecialchars($order->customer_phone); ?></td></tr>
          <tr><th>Address</th><td><?= nl2br(htmlspecialchars($order->customer_address)); ?></td></tr>
          <tr><th>Order Type</th><td><?= ucfirst($order->order_type); ?></td></tr>
          <tr><th>Payment Method</th><td><?= ucfirst(str_replace('_',' ',$order->payment_method)); ?></td></tr>
          <?php if($order->shipping_method): ?><tr><th>Shipping Method</th><td><?= htmlspecialchars($order->shipping_method); ?></td></tr><?php endif; ?>
          <?php if($order->paystack_reference): ?><tr><th>Paystack Ref</th><td><code><?= htmlspecialchars($order->paystack_reference); ?></code></td></tr><?php endif; ?>
          <?php if($order->service_date): ?><tr><th>Service Date</th><td><?= show_date($order->service_date); ?> <?= htmlspecialchars($order->service_time); ?></td></tr><?php endif; ?>
          <?php if($order->service_note): ?><tr><th>Service Note</th><td><?= nl2br(htmlspecialchars($order->service_note)); ?></td></tr><?php endif; ?>
          <?php if($order->table_number): ?><tr><th>Table</th><td><?= htmlspecialchars($order->table_number); ?></td></tr><?php endif; ?>
          <tr><th>IP Address</th><td><?= htmlspecialchars($order->ip_address); ?></td></tr>
          <tr><th>Placed At</th><td><?= show_date($order->created_at); ?> <?= date('H:i:s', strtotime($order->created_at)); ?></td></tr>
        </table>
      </div>
    </div>

    <div class="mp-card-form" style="margin-top:20px!important;">
      <div class="mp-card-head"><h3>Items</h3></div>
      <div class="mp-card-body" style="padding:0!important;">
        <div class="mp-dt-scroll">
          <table class="table mp-dt-table" width="100%">
            <thead><tr><th>Item</th><th>Type</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr></thead>
            <tbody>
              <?php foreach($items as $it): ?>
              <tr>
                <td><?= htmlspecialchars($it->item_name); ?></td>
                <td><?= ucfirst($it->item_type); ?></td>
                <td><?= (int)$it->qty; ?></td>
                <td class="amt"><?= $CI->currency($it->unit_price); ?></td>
                <td class="amt"><?= $CI->currency($it->total_price); ?></td>
              </tr>
              <?php endforeach; ?>
            </tbody>
            <tfoot>
              <tr><th colspan="4" class="text-right">Subtotal</th><th class="amt"><?= $CI->currency($order->subtotal); ?></th></tr>
              <?php if($order->delivery_fee > 0): ?>
              <tr><th colspan="4" class="text-right"><?= $order->shipping_method ? 'Shipping Fee' : 'Delivery Fee'; ?></th><th class="amt"><?= $CI->currency($order->delivery_fee); ?></th></tr>
              <?php endif; ?>
              <tr><th colspan="4" class="text-right">Grand Total</th><th class="amt"><?= $CI->currency($order->grand_total); ?></th></tr>
            </tfoot>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div>
    <div class="mp-card-form">
      <div class="mp-card-head"><h3>Order Summary</h3></div>
      <div class="mp-card-body">
        <div class="os-summary-total"><?= $CI->currency($order->grand_total); ?></div>
        <div class="os-summary-label">Grand Total</div>
      </div>
    </div>

    <div class="mp-card-form" style="margin-top:20px!important;">
      <div class="mp-card-head"><h3>Actions</h3></div>
      <div class="mp-card-body">
        <div class="mp-form-group">
          <label for="order_status">Update Order Status</label>
          <select class="mp-form-control" id="order_status" onchange="updateStatus('order')">
            <option value="pending" <?= $order->order_status=='pending'?'selected':''; ?>>Pending</option>
            <option value="paid" <?= $order->order_status=='paid'?'selected':''; ?>>Paid</option>
            <option value="processing" <?= $order->order_status=='processing'?'selected':''; ?>>Processing</option>
            <option value="ready" <?= $order->order_status=='ready'?'selected':''; ?>>Ready</option>
            <option value="completed" <?= $order->order_status=='completed'?'selected':''; ?>>Completed</option>
            <option value="cancelled" <?= $order->order_status=='cancelled'?'selected':''; ?>>Cancelled</option>
          </select>
        </div>
        <div class="mp-form-group">
          <label for="payment_status">Update Payment Status</label>
          <select class="mp-form-control" id="payment_status" onchange="updateStatus('payment')">
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

<script>
function updateStatus(type){
  var url = type === 'order' ? '<?=base_url("online_store/update_order_status");?>' : '<?=base_url("online_store/update_payment_status");?>';
  var status = type === 'order' ? $('#order_status').val() : $('#payment_status').val();
  $.post(url, {
    order_id: <?= (int)$order->id; ?>,
    status: status,
    '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
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
<script>$(".online_store-orders-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
