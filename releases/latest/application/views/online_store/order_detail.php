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
.os-money-lines{list-style:none!important;margin:14px 0 0!important;padding:0!important;border-top:1px solid var(--mp-border)!important}
.os-money-lines>li{padding:11px 0!important;border-bottom:1px solid var(--mp-border)!important;font-size:13px!important}
.os-money-lines>li:last-child{border-bottom:none!important}
.os-money-lines>li>div:first-child{display:flex!important;justify-content:space-between!important;gap:12px!important}
.os-money-meta{color:var(--mp-muted,#64748b)!important;font-size:12px!important;margin-top:3px!important}
.os-money-note{margin-top:3px!important;font-style:italic!important}
.os-money-empty{font-size:13px!important;color:var(--mp-muted,#64748b)!important;margin:12px 0 0!important}
.os-money-form{margin-top:16px!important;padding-top:14px!important;border-top:1px dashed #E2E8F0!important}
.os-money-hint{display:block!important;margin-top:6px!important;color:var(--mp-muted,#64748b)!important}
.os-money-check{font-weight:400!important;font-size:13px!important}
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
          <?php if(!empty($order->warehouse_id) && !empty($order->fulfilment_branch)): ?><tr><th>Fulfilment Location</th><td><?= htmlspecialchars($order->fulfilment_branch); ?></td></tr><?php endif; ?>
          <?php if(!empty($order->prescription_file)): ?><tr><th>Prescription</th><td><a href="<?= base_url($order->prescription_file); ?>" target="_blank" rel="noopener"><i class="fa fa-file-pdf-o"></i> View uploaded prescription</a></td></tr><?php endif; ?>
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

    <?php $money = (isset($money_box) && is_array($money_box)) ? $money_box : null; ?>
    <?php if($money): ?>
    <div class="mp-card-form" style="margin-top:20px!important;">
      <div class="mp-card-head"><h3>Money in</h3></div>
      <div class="mp-card-body">
        <table class="os-info-table">
          <tr><th>Invoiced</th><td class="amt"><?= $CI->currency($money['due']); ?></td></tr>
          <tr><th>Confirmed</th><td class="amt"><?= $CI->currency($money['verified']); ?></td></tr>
          <?php if($money['pending'] > 0): ?>
          <tr><th>Waiting to confirm</th><td class="amt" style="color:#92400E;"><?= $CI->currency($money['pending']); ?></td></tr>
          <?php endif; ?>
          <tr><th>Still owing</th><td class="amt" style="font-weight:700;"><?= $money['outstanding'] > 0 ? $CI->currency($money['outstanding']) : 'Nothing'; ?></td></tr>
        </table>

        <?php if($money['lines']): ?>
        <ul class="os-money-lines">
          <?php foreach($money['lines'] as $mline): ?>
          <li>
            <div>
              <strong><?= htmlspecialchars($mline->channel_name); ?></strong>
              <span class="amt"><?= $CI->currency($mline->amount); ?></span>
            </div>
            <div class="os-money-meta">
              <?php if((string)$mline->status === 'verified'): ?>
                Confirmed<?= $mline->person_name ? ' by ' . htmlspecialchars($mline->person_name) : ''; ?>
              <?php elseif((string)$mline->status === 'rejected'): ?>
                <span style="color:#991B1B;">Dismissed<?= $mline->person_name ? ' by ' . htmlspecialchars($mline->person_name) : ''; ?></span>
              <?php else: ?>
                <span style="color:#92400E;">Not confirmed yet<?= $mline->person_name ? ' (noted by ' . htmlspecialchars($mline->person_name) . ')' : ''; ?></span>
              <?php endif; ?>
              <?php if(!empty($mline->reference)): ?> &middot; ref <code><?= htmlspecialchars($mline->reference); ?></code><?php endif; ?>
              &middot; <?= htmlspecialchars((string)($mline->recorded_at ? show_date(substr((string)$mline->recorded_at, 0, 10)) : '')); ?>
              <?php if(!empty($mline->note)): ?><div class="os-money-note"><?= htmlspecialchars($mline->note); ?></div><?php endif; ?>
            </div>
            <?php if((string)$mline->status === 'pending' && !empty($can_record_money)): ?>
            <div style="display:flex;gap:8px;margin-top:6px;">
              <button type="button" class="btn btn-sm btn-success" onclick="decideMoney(<?= (int)$mline->id; ?>,'verified')">I have seen this money</button>
              <button type="button" class="btn btn-sm btn-default" onclick="decideMoney(<?= (int)$mline->id; ?>,'rejected')">Dismiss</button>
            </div>
            <?php endif; ?>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php else: ?>
          <p class="os-money-empty">Nothing has been recorded against this order yet.</p>
        <?php endif; ?>

        <?php if(!empty($can_record_money)): ?>
        <div class="os-money-form">
          <div class="mp-form-group">
            <label for="money_amount">Amount that came in</label>
            <input type="number" step="0.01" min="0.01" class="mp-form-control" id="money_amount"
              value="<?= $money['outstanding'] > 0 ? htmlspecialchars(rtrim(rtrim(number_format($money['outstanding'], 2, '.', ''), '0'), '.')) : ''; ?>" placeholder="e.g. 3500">
          </div>
          <div class="mp-form-group">
            <label for="money_channel">How did it come in?</label>
            <select class="mp-form-control" id="money_channel">
              <option value="transfer">Bank transfer</option>
              <option value="cash">Cash at the counter</option>
              <option value="pay_at_collection">Pay at collection</option>
              <option value="pay_on_delivery">Cash on delivery</option>
              <option value="card">Card / other gateway</option>
            </select>
            <small class="os-money-hint" id="money_hint">A transfer waits for the person who can see the bank account to confirm it.</small>
          </div>
          <div class="mp-form-group">
            <label for="money_reference">Transfer or receipt reference</label>
            <input type="text" class="mp-form-control" id="money_reference" placeholder="e.g. the bank's transaction ID">
          </div>
          <div class="mp-form-group">
            <label for="money_note">Anything to remember about it</label>
            <input type="text" class="mp-form-control" id="money_note" placeholder="optional">
          </div>
          <div class="mp-form-group" id="money_seen_group" style="display:none;">
            <label class="os-money-check"><input type="checkbox" id="money_seen"> I have checked the account and the money is in</label>
          </div>
          <button type="button" class="btn btn-sm btn-primary" onclick="recordMoney()">Record this money</button>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php
      $trail_rows = (isset($trail) && is_array($trail)) ? $trail : [];
      $dv = (isset($delivery_box) && is_array($delivery_box)) ? $delivery_box : null;
    ?>
    <?php if($dv || $trail_rows): ?>
    <div class="mp-card-form" style="margin-top:20px!important;">
      <div class="mp-card-head"><h3>Where this order has got to</h3></div>
      <div class="mp-card-body">
        <?php if($dv && !$dv['collection']): ?>
          <p style="margin:0 0 8px;font-size:14px;"><strong>Delivery:</strong> <?= htmlspecialchars($dv['word']); ?></p>
          <?php if(!empty($can_move_delivery)): ?>
          <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <?php if($dv['status'] !== 'out_for_delivery' && $dv['status'] !== 'delivered'): ?>
              <button type="button" class="btn btn-sm btn-primary" onclick="sendOut()">
                <?= $dv['status'] === 'failed' ? 'Try sending it again' : 'Send it out'; ?>
              </button>
            <?php endif; ?>
            <?php if($dv['status'] === 'out_for_delivery'): ?>
              <button type="button" class="btn btn-sm btn-success" onclick="markDelivery('delivered')">It has arrived</button>
              <button type="button" class="btn btn-sm btn-default" onclick="markDelivery('failed')">It did not arrive</button>
            <?php endif; ?>
          </div>
          <?php endif; ?>
        <?php elseif($dv && $dv['collection']): ?>
          <p style="margin:0 0 8px;font-size:13px;color:var(--mp-muted,#64748b);">
            This order is for collection at the shop — mark it Ready when it is packed, and Completed once the customer has picked it up.
          </p>
        <?php endif; ?>

        <?php if($trail_rows): ?>
        <ul style="margin:12px 0 0;padding:0;list-style:none;">
          <?php foreach($trail_rows as $t): ?>
          <li style="padding:7px 0;border-top:1px dashed #E2E8F0;font-size:13px;">
            <strong><?= htmlspecialchars($t['text']); ?></strong>
            <span style="color:#64748b;">
              · <?= htmlspecialchars($t['actor'] !== '' ? $t['actor'] : 'the system'); ?>
              · <?= htmlspecialchars(@date('d M Y H:i', strtotime((string)$t['when']))); ?>
            </span>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if(!empty($rx_review_box)): $rxn = $rx_review_box; ?>
    <div class="mp-card-form" style="margin-top:20px!important;border-left:4px solid <?= $rxn['approved'] ? '#16a34a' : '#d97706'; ?>;">
      <div class="mp-card-head"><h3>Prescription review</h3></div>
      <div class="mp-card-body">
        <p style="margin:0 0 8px;"><strong>Prescription-only medicine:</strong> <?= htmlspecialchars(implode(', ', $rxn['names'])); ?></p>
        <?php $rxnote = $rxn['note'] ?? null; ?>
        <?php if($rxnote && !empty($rxnote->review_status) && $rxnote->review_status !== 'pending' && !empty($rxnote->reviewed_by)): ?>
          <p style="margin:0 0 8px;color:var(--mp-muted,#64748b);">
            <?= ucfirst((string)$rxnote->review_status); ?> by <?= htmlspecialchars($rxnote->reviewed_by_name ?: 'a pharmacist here'); ?>
            <?php if(!empty($rxnote->reviewed_at)): ?> on <?= show_date(substr((string)$rxnote->reviewed_at, 0, 10)); ?><?php endif; ?>.
          </p>
        <?php endif; ?>
        <?php if(!$rxn['approved']): ?>
          <p style="margin:0 0 10px;">This order cannot move toward dispensing until a pharmacist approves the prescription.</p>
        <?php endif; ?>
        <?php $rxfile = trim((string)(($rxnote && !empty($rxnote->prescription_file)) ? $rxnote->prescription_file : ($order->prescription_file ?? ''))); ?>
        <?php if($rxfile !== ''): ?>
          <p style="margin:0 0 10px;"><a href="<?= base_url($rxfile); ?>" target="_blank" rel="noopener"><i class="fa fa-file-pdf-o"></i> View the attached prescription</a></p>
        <?php else: ?>
          <p style="margin:0 0 10px;color:var(--mp-muted,#64748b);">No prescription document is attached to this order.</p>
        <?php endif; ?>
        <?php if(!$rxn['approved']): ?>
        <div style="display:flex;gap:8px;">
          <button type="button" class="btn btn-sm btn-success" onclick="rxReview('approved')">Approve prescription</button>
          <button type="button" class="btn btn-sm btn-danger" onclick="rxReview('rejected')">Reject &amp; cancel order</button>
        </div>
        <?php else: $inv = $rxn['invoice'] ?? []; ?>
        <div style="margin-top:12px;padding-top:12px;border-top:1px dashed #E2E8F0;">
          <?php if((string)$order->payment_status === 'paid'): ?>
            <p style="margin:0;font-size:13px;font-weight:600;color:#166534;">Payment is already in, so no invoice was sent.</p>
          <?php else: ?>
            <?php if(!empty($inv['sent_at'])): ?>
              <p style="margin:0 0 8px;font-size:13px;color:#334155;">
                Invoice link sent on <?= htmlspecialchars(date('d M Y \a\t H:i', strtotime((string)$inv['sent_at']))); ?>
                <?php if(!empty($inv['expires_at'])): ?> and works until <?= htmlspecialchars(date('d M Y H:i', strtotime((string)$inv['expires_at']))); ?><?php endif; ?>.
              </p>
              <button type="button" class="btn btn-sm btn-default" onclick="sendInvoiceLink()">Send payment link again</button>
            <?php else: ?>
              <p style="margin:0 0 8px;font-size:13px;color:#92400E;">The customer has not been sent their invoice yet.</p>
              <button type="button" class="btn btn-sm btn-primary" onclick="sendInvoiceLink()">Send payment link</button>
            <?php endif; ?>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

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
          <small style="display:block;margin-top:6px;color:var(--mp-muted,#64748b);">Refunds are full-order only (<?= $CI->currency($order->grand_total); ?>). Marking an order refunded records <strong>bookkeeping only — it does not return money</strong>. You must enter the refund reference from your payment provider and confirm the money was actually sent. Stock is only restocked when the goods came back or the order never shipped. Partial returns/refunds are not supported.</small>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function revertPayment(){ $('#payment_status').val('<?= $order->payment_status; ?>'); }
function postStatus(url, payload){
  $.post(url, payload, function(res){
    if(res.status === 'success'){
      toastr.success(res.message);
      setTimeout(function(){ location.reload(); }, 1200);
    } else {
      toastr.error(res.message);
      revertPayment();
    }
  }, 'json');
}
function updateStatus(type){
  var url = type === 'order' ? '<?=base_url("online_store/update_order_status");?>' : '<?=base_url("online_store/update_payment_status");?>';
  var status = type === 'order' ? $('#order_status').val() : $('#payment_status').val();
  var payload = {
    order_id: <?= (int)$order->id; ?>,
    status: status,
    '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
  };
  if(type === 'payment' && status === 'refunded'){
    window.mpConfirmAsync(
      'Confirming this records a FULL-ORDER refund of <?= $CI->currency($order->grand_total); ?> in MartPoint. It does NOT send money back. Only continue if you have already refunded the customer through your payment provider.',
      {title:'Refund this order', okText:'Record the refund', danger:true}
    ).then(function(ok){
      if(!ok){ revertPayment(); return; }
      window.mpPrompt({
        title:'Provider refund reference',
        message:'The transaction ID from your payment provider, for the money you have already sent back.',
        placeholder:'e.g. 800123456789', required:true
      }).then(function(ref){
        if(!ref){ revertPayment(); return; }
        window.mpPrompt({
          title:'What happened to the goods?',
          message:'Restocking depends on this, so please pick the one that matches.',
          options:[
            {value:'returned', label:'Returned to us — restock the stock'},
            {value:'not_shipped', label:'Never shipped — restock the stock'},
            {value:'kept', label:'Customer kept them — do NOT restock'}
          ]
        }).then(function(disp){
          if(!disp){ revertPayment(); return; }
          payload.refund_reference = ref;
          payload.refund_disposition = disp;
          postStatus(url, payload);
        });
      });
    });
    return;
  }
  postStatus(url, payload);
}
function sendOut(){
  window.mpPrompt({
    title:'Who is taking it?',
    message:'Their name or number, so the customer knows who is arriving. You can leave it empty.',
    placeholder:'e.g. Chidi 0803…',
    required:false
  }).then(function(rider){
    if(rider === null || rider === undefined){ return; }
    markDelivery('out_for_delivery', rider);
  });
}
function markDelivery(status, rider){
  $.post('<?=base_url("online_store/update_delivery_status");?>', {
    order_id: <?= (int)$order->id; ?>,
    status: status,
    rider: rider || '',
    '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
  }, function(res){
    if(res.status === 'success'){
      toastr.success(res.message + (res.notified ? ' — the customer was ' + res.notified : ''));
      setTimeout(function(){ location.reload(); }, 1400);
    } else { toastr.error(res.message); }
  }, 'json').fail(function(){ toastr.error('The delivery step could not be saved. Please try again.'); });
}
function sendInvoiceLink(){
  window.mpConfirmAsync('This replaces the link the customer already has, then sends a fresh invoice with the same total. Send it?',
    {title:'Send payment link', okText:'Send it'}).then(function(ok){
    if(!ok){ return; }
    $.post('<?=base_url("online_store/send_payment_link/" . (int)$order->id);?>', {
      '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
    }, function(res){
      if(res.status === 'success'){ toastr.success(res.message); setTimeout(function(){ location.reload(); }, 1400); }
      else { toastr.error(res.message); }
    }, 'json');
  });
}

function rxReview(decision){
  window.mpConfirmAsync(decision === 'approved'
    ? 'Approve this prescription so the pharmacy can dispense the order?'
    : 'Reject this prescription? The order will be cancelled because the medicine cannot be dispensed.',
    {title:'Prescription review', okText: decision === 'approved' ? 'Approve' : 'Reject & cancel order', danger: decision !== 'approved'}
  ).then(function(ok){
    if(!ok){ return; }
    $.post('<?=base_url("online_store/review_prescription");?>', {
      order_id: <?= (int)$order->id; ?>,
      decision: decision,
      '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
    }, function(res){
      if(res.status === 'success'){ toastr.success(res.message); setTimeout(function(){ location.reload(); }, 1200); }
      else { toastr.error(res.message); }
    }, 'json');
  });
}

function moneyChannelHint(){
  if(!$('#money_channel').length){ return; }
  var ch = $('#money_channel').val();
  var needsAccount = ch === 'transfer' || ch === 'card';
  $('#money_seen_group').toggle(needsAccount);
  $('#money_hint').text(needsAccount
    ? 'A payment like this waits for the person who can see the account or the gateway to confirm it.'
    : 'Money or goods handed over the counter — recording it here confirms it.');
}
function recordMoney(){
  var amount = $.trim($('#money_amount').val());
  if(!amount || parseFloat(amount) <= 0){ cqError('Enter the amount that came in, in numbers.'); return; }
  $.post('<?= base_url('online_store/record_order_payment'); ?>', {
    order_id: <?= (int)$order->id; ?>,
    amount: amount,
    channel: $('#money_channel').val(),
    reference: $.trim($('#money_reference').val()),
    note: $.trim($('#money_note').val()),
    seen: $('#money_seen').is(':checked') ? 1 : 0,
    '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
  }, function(res){
    if(res.status === 'success'){ cqSuccess(res.message); setTimeout(function(){ location.reload(); }, 1200); }
    else { cqError(res.message); }
  }, 'json').fail(function(){ cqError('MartPoint could not be reached, so nothing was recorded.'); });
}
function decideMoney(paymentId, decision){
  var verifying = decision === 'verified';
  window.mpConfirmAsync(verifying
    ? 'Only confirm this if you have looked and the money is really in.'
    : 'Dismissing keeps this money off the books, and the customer still owes it.',
    {title: verifying ? 'Confirm this payment' : 'Dismiss this payment',
     okText: verifying ? 'Confirm it' : 'Dismiss it', danger: !verifying}
  ).then(function(ok){
    if(!ok){ return; }
    $.post('<?= base_url('online_store/decide_order_payment'); ?>', {
      payment_id: paymentId,
      decision: decision,
      '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
    }, function(res){
      if(res.status === 'success'){ cqSuccess(res.message); setTimeout(function(){ location.reload(); }, 1200); }
      else { cqError(res.message); }
    }, 'json');
  });
}
$(function(){ $('#money_channel').on('change', moneyChannelHint); moneyChannelHint(); });
</script>
<?php if(!empty($order->refund_confirmed_at)): ?>
<script>
(function(){
  var box = document.createElement('div');
  box.style.cssText = 'margin-top:12px;padding:12px 14px;border:1px solid #E2E8F0;border-radius:8px;background:#F8FAFC;font-size:13px;line-height:1.6;color:#334155;';
  box.innerHTML = '<strong>External refund recorded</strong><br>Provider reference: <code><?= htmlspecialchars((string)$order->refund_reference, ENT_QUOTES, 'UTF-8'); ?></code><br>'
    + 'Amount: <?= $CI->currency($order->refund_amount); ?><br>'
    + 'Goods: <?= htmlspecialchars((string)$order->refund_disposition, ENT_QUOTES, 'UTF-8'); ?> — '
    + <?= !empty($order->refund_restocked) ? "'stock was returned to inventory.'" : "'stock was NOT restocked.'" ; ?> + '<br>'
    + 'Confirmed by <?= htmlspecialchars((string)($order->refund_confirmed_by ?? 'system'), ENT_QUOTES, 'UTF-8'); ?> at <?= htmlspecialchars((string)$order->refund_confirmed_at, ENT_QUOTES, 'UTF-8'); ?>';
  var form = document.querySelector('.mp-card-form .mp-card-body');
  if(form) form.appendChild(box);
})();
</script>
<?php endif; ?>
<script>$(".online_store-orders-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
