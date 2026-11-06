<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
<?php $this->load->view('comman/app_icons'); ?>
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?> — <?= $page_title; ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-ink: #1E293B; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; width: 36px; height: 36px; display: flex; align-items: center; justify-content: center; border-radius: 12px; background: var(--mp-bg); }
    .topbar .back:active { background: #E2E8F0; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .topbar-titles { flex: 1; min-width: 0; }
    .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .status-row { display: flex; gap: 8px; margin-bottom: 16px; }
    .status-pill { flex: 1; padding: 10px; border-radius: 12px; text-align: center; font-size: 13px; font-weight: 600; text-transform: capitalize; background: #fff; border: 1px solid var(--mp-border); }
    .order-status-pending { background: #FEF3C7; color: #92400E; }
    .order-status-paid { background: #D1FAE5; color: #065F46; }
    .order-status-processing { background: #DBEAFE; color: #1E40AF; }
    .order-status-ready { background: #E0E7FF; color: #3730A3; }
    .order-status-completed { background: #D1FAE5; color: #065F46; }
    .order-status-cancelled { background: #FEF2F2; color: #991B1B; }
    .payment-status-unpaid { background: #FEF3C7; color: #92400E; }
    .payment-status-paid { background: #D1FAE5; color: #065F46; }
    .payment-status-partially_paid { background: #DBEAFE; color: #1E40AF; }
    .payment-status-failed { background: #FEF2F2; color: #991B1B; }
    .payment-status-refunded { background: #F1F5F9; color: #475569; }
    .card { background: #fff; border-radius: 16px; border: 1px solid var(--mp-border); padding: 16px; margin-bottom: 16px; box-shadow: 0 1px 3px rgba(15,23,42,0.04); }
    .card-title { font-size: 15px; font-weight: 700; margin-bottom: 12px; color: var(--mp-ink); }
    .row-pair { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid var(--mp-border); font-size: 14px; }
    .row-pair:last-child { border-bottom: none; }
    .row-pair .label { color: var(--mp-muted); }
    .row-pair .value { font-weight: 600; text-align: right; max-width: 60%; }
    .items-title { font-size: 15px; font-weight: 700; margin-bottom: 12px; }
    .item { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-bottom: 1px solid var(--mp-border); font-size: 14px; }
    .item:last-child { border-bottom: none; }
    .item-info { flex: 1; }
    .item-name { font-weight: 600; }
    .item-meta { font-size: 12px; color: var(--mp-muted); margin-top: 2px; }
    .item-total { font-weight: 700; }
    .section-title { font-size: 13px; font-weight: 600; color: var(--mp-muted); margin: 16px 0 8px; text-transform: uppercase; }
    .actions { display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 16px; }
    .action-btn { padding: 8px 12px; border-radius: 20px; border: 1px solid var(--mp-border); background: #fff; font-size: 12px; font-weight: 600; cursor: pointer; }
    .action-btn:active { background: #F8FAFC; }
    .total-row { display: flex; justify-content: space-between; font-size: 16px; font-weight: 700; padding-top: 12px; border-top: 2px solid var(--mp-border); margin-top: 12px; }
    .money-line { padding: 10px 0; border-top: 1px solid var(--mp-border); }
    .money-line .row-pair { border-bottom: none; padding: 4px 0; }
    .money-line .actions { margin: 8px 0 0; }
    .money-meta { font-size: 12px; color: var(--mp-muted); }
    .money-note { font-style: italic; margin-top: 3px; }
    .toast { position: fixed; top: 16px; left: 16px; right: 16px; padding: 14px; border-radius: 12px; text-align: center; color: #fff; font-weight: 600; z-index: 1000; display: none; }
    .toast.error { background: var(--mp-danger); }
    .toast.success { background: var(--mp-success); }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 120px; } }
  </style>
</head>
<body>
  <div id="toast" class="toast"></div>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= $back_url; ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= $page_title; ?></h1>
        </div>
      </div>

      <div class="status-row">
        <div class="status-pill order-status-<?= $order->order_status; ?>"><?= ucfirst($order->order_status); ?></div>
        <div class="status-pill payment-status-<?= $order->payment_status; ?>"><?= ucfirst(str_replace('_',' ',$order->payment_status)); ?></div>
      </div>

      <?php $money = (isset($money_box) && is_array($money_box)) ? $money_box : null; ?>
      <?php if($money): ?>
      <div class="card">
        <div class="card-title">Money in</div>
        <div class="row-pair"><span class="label">Invoiced</span><span class="value"><?= store_number_format($money['due']); ?></span></div>
        <div class="row-pair"><span class="label">Confirmed</span><span class="value"><?= store_number_format($money['verified']); ?></span></div>
        <?php if($money['pending'] > 0): ?>
          <div class="row-pair"><span class="label">Waiting to confirm</span><span class="value"><?= store_number_format($money['pending']); ?></span></div>
        <?php endif; ?>
        <div class="row-pair"><span class="label">Still owing</span><span class="value"><?= $money['outstanding'] > 0 ? store_number_format($money['outstanding']) : 'Nothing'; ?></span></div>
        <?php foreach($money['lines'] as $mline): ?>
          <div class="money-line">
            <div class="row-pair"><span class="label"><?= htmlspecialchars($mline->channel_name); ?></span><span class="value"><?= store_number_format($mline->amount); ?></span></div>
            <div class="money-meta">
              <?php if((string)$mline->status === 'verified'): ?>
                Confirmed<?= $mline->person_name ? ' by ' . htmlspecialchars($mline->person_name) : ''; ?>
              <?php elseif((string)$mline->status === 'rejected'): ?>
                <span style="color:#B91C1C;">Dismissed<?= $mline->person_name ? ' by ' . htmlspecialchars($mline->person_name) : ''; ?></span>
              <?php else: ?>
                <span style="color:#92400E;">Not confirmed yet</span>
              <?php endif; ?>
              <?php if(!empty($mline->reference)): ?> &middot; ref <?= htmlspecialchars($mline->reference); ?><?php endif; ?>
              <?php if(!empty($mline->note)): ?><div class="money-note"><?= htmlspecialchars($mline->note); ?></div><?php endif; ?>
            </div>
            <?php if((string)$mline->status === 'pending' && $can_edit): ?>
              <div class="actions">
                <button class="action-btn" onclick="decideMoney(<?= (int)$mline->id; ?>,'verified')">I have seen it</button>
                <button class="action-btn" onclick="decideMoney(<?= (int)$mline->id; ?>,'rejected')">Dismiss</button>
              </div>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
        <?php if($can_edit): ?>
          <div class="actions"><button class="action-btn" onclick="recordMoney()">Record money in</button></div>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <?php if($can_edit): ?>
        <div class="card">
          <div class="card-title">Update Order Status</div>
          <div class="actions">
            <?php foreach(['pending','paid','processing','ready','completed','cancelled'] as $s): ?>
              <?php if($order->order_status !== $s): ?>
                <button class="action-btn" onclick="updateOrderStatus('<?= $s; ?>')"><?= ucfirst($s); ?></button>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="card">
          <div class="card-title">Update Payment Status</div>
          <div class="actions">
            <?php foreach(['unpaid','paid','partially_paid','failed','refunded'] as $s): ?>
              <?php if($order->payment_status !== $s): ?>
                <button class="action-btn" onclick="updatePaymentStatus('<?= $s; ?>')"><?= ucfirst(str_replace('_',' ',$s)); ?></button>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <?php
        $trail_rows = (isset($trail) && is_array($trail)) ? $trail : [];
        $dv = (isset($delivery_box) && is_array($delivery_box)) ? $delivery_box : null;
      ?>
      <?php if($dv || $trail_rows): ?>
      <div class="card">
        <div class="card-title">Where this order has got to</div>
        <?php if($dv && !$dv['collection']): ?>
          <div class="row-pair"><span class="label">Delivery</span><span class="value"><?= htmlspecialchars($dv['word']); ?></span></div>
          <?php if(!empty($can_move_delivery)): ?>
            <div class="actions">
              <?php if($dv['status'] !== 'out_for_delivery' && $dv['status'] !== 'delivered'): ?>
                <button class="action-btn" onclick="sendOut()"><?= $dv['status'] === 'failed' ? 'Try sending it again' : 'Send it out'; ?></button>
              <?php endif; ?>
              <?php if($dv['status'] === 'out_for_delivery'): ?>
                <button class="action-btn" onclick="markDelivery('delivered')">It has arrived</button>
                <button class="action-btn" onclick="markDelivery('failed')">It did not arrive</button>
              <?php endif; ?>
            </div>
          <?php endif; ?>
        <?php elseif($dv && $dv['collection']): ?>
          <p style="margin:0;font-size:13px;color:#64748b;">This order is for collection at the shop — mark it Ready when it is packed, and Completed once the customer has picked it up.</p>
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
      <?php endif; ?>

      <div class="card">
        <div class="card-title">Customer</div>
        <div class="row-pair"><span class="label">Name</span><span class="value"><?= htmlspecialchars($order->customer_name); ?></span></div>
        <div class="row-pair"><span class="label">Phone</span><span class="value"><?= htmlspecialchars($order->customer_phone); ?></span></div>
        <div class="row-pair"><span class="label">Email</span><span class="value"><?= htmlspecialchars($order->customer_email); ?></span></div>
        <div class="row-pair"><span class="label">Type</span><span class="value"><?= ucfirst($order->order_type); ?></span></div>
        <?php if($order->shipping_method): ?>
          <div class="row-pair"><span class="label">Shipping</span><span class="value"><?= htmlspecialchars($order->shipping_method); ?></span></div>
        <?php endif; ?>
        <?php if($order->service_date): ?>
          <div class="row-pair"><span class="label">Service</span><span class="value"><?= show_date($order->service_date); ?> <?= $order->service_time; ?></span></div>
        <?php endif; ?>
        <div class="row-pair"><span class="label">Placed</span><span class="value"><?= show_date($order->created_at); ?> <?= date('H:i', strtotime($order->created_at)); ?></span></div>
      </div>

      <div class="items-title">Items</div>
      <div class="card">
        <?php foreach($items as $it): ?>
          <div class="item">
            <div class="item-info">
              <div class="item-name"><?= htmlspecialchars($it->item_name); ?></div>
              <div class="item-meta"><?= (int)$it->qty; ?> × <?= store_number_format($it->unit_price); ?></div>
            </div>
            <div class="item-total"><?= store_number_format($it->total_price); ?></div>
          </div>
        <?php endforeach; ?>
        <?php if($order->delivery_fee > 0): ?>
          <div class="item" style="justify-content:space-between;">
            <span class="item-meta"><?= $order->shipping_method ? 'Shipping' : 'Delivery'; ?></span>
            <span class="item-total"><?= store_number_format($order->delivery_fee); ?></span>
          </div>
        <?php endif; ?>
        <div class="total-row"><span>Grand Total</span><span><?= store_number_format($order->grand_total); ?></span></div>
      </div>
    </section>
    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    function showToast(msg, isError){
      const t = document.getElementById('toast');
      t.textContent = msg;
      t.className = 'toast ' + (isError ? 'error' : 'success');
      t.style.display = 'block';
      setTimeout(() => t.style.display = 'none', 3000);
    }
    function updateOrderStatus(status){
      var fd = new FormData();
      fd.append('order_id', <?= (int)$order->id; ?>);
      fd.append('status', status);
      <?php if($this->security): ?>
      fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
      <?php endif; ?>
      mpFetchJson('<?= base_url('online_store/update_order_status'); ?>', {method:'POST', body:fd})
        .then(d => { showToast(d.message || 'Done', d.status !== 'success'); if(d.status === 'success') setTimeout(()=>location.reload(), 800); })
        .catch(err => showToast(mpErrorText(err, 'The order status could not be updated. Please try again.'), true));
    }
    function updatePaymentStatus(status){
      var fd = new FormData();
      fd.append('order_id', <?= (int)$order->id; ?>);
      fd.append('status', status);
      <?php if($this->security): ?>
      fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
      <?php endif; ?>
      mpFetchJson('<?= base_url('online_store/update_payment_status'); ?>', {method:'POST', body:fd})
        .then(d => { showToast(d.message || 'Done', d.status !== 'success'); if(d.status === 'success') setTimeout(()=>location.reload(), 800); })
        .catch(err => showToast(mpErrorText(err, 'The payment status could not be updated. Please try again.'), true));
    }
    function postMoney(fd, done){
      mpFetchJson('<?= base_url('online_store/record_order_payment'); ?>', {method:'POST', body:fd})
        .then(d => { showToast(d.message || 'Done', d.status !== 'success'); if(d.status === 'success') setTimeout(()=>location.reload(), 900); })
        .catch(err => showToast(mpErrorText(err, 'MartPoint could not be reached. Nothing was recorded.'), true));
    }
    /* Money in, asked in the order a person actually knows it: how much, which
       channel, and the bank reference if there is one. A transfer they have not
       checked stays unconfirmed so a second pair of eyes is still required. */
    function recordMoney(){
      mpPrompt({title:'Amount that came in', message:'Numbers only, for example 3500.', placeholder:'Amount', required:true, okText:'Next'})
        .then(function(raw){
          if(raw === null) return;
          var amount = (raw || '').trim();
          if(!amount || parseFloat(amount) <= 0){ showToast('Enter the amount in numbers.', true); return; }
          mpPrompt({title:'How did it come in?', okText:'Next', options:[
            {value:'transfer_seen', label:'Bank transfer — I have seen it in the account'},
            {value:'transfer_wait', label:'Bank transfer — waiting for somebody to check'},
            {value:'cash', label:'Cash at the counter'},
            {value:'pay_at_collection', label:'Promised at collection'},
            {value:'pay_on_delivery', label:'Cash on delivery'},
            {value:'card', label:'Card or another payment gateway'}
          ]}).then(function(pick){
            if(!pick) return;
            var channel = pick.indexOf('transfer') === 0 ? 'transfer' : pick;
            var seen = pick !== 'transfer_wait';
            var needsRef = channel === 'transfer' || channel === 'card';
            var finish = function(reference){
              var fd = new FormData();
              fd.append('order_id', <?= (int)$order->id; ?>);
              fd.append('amount', amount);
              fd.append('channel', channel);
              fd.append('reference', reference || '');
              fd.append('note', '');
              fd.append('seen', seen ? 1 : 0);
              <?php if($this->security): ?>
              fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
              <?php endif; ?>
              postMoney(fd);
            };
            if(!needsRef){ finish(''); return; }
            mpPrompt({title:'Transfer reference', message:'The bank or gateway transaction ID. Leave it empty if you do not have it yet.', placeholder:'e.g. 800123456789', okText:'Save'})
              .then(function(ref){ if(ref === null) return; finish((ref || '').trim()); });
          });
        });
    }
    function decideMoney(paymentId, decision){
      var confirming = decision === 'verified';
      mpConfirmAsync(confirming
        ? 'Only confirm this if you have looked and the money is really in.'
        : 'Dismissing keeps this money off the books, and the customer still owes it.',
        {title: confirming ? 'Confirm this payment' : 'Dismiss this payment',
         okText: confirming ? 'Confirm it' : 'Dismiss it', danger: !confirming}
      ).then(function(ok){
        if(!ok) return;
        var fd = new FormData();
        fd.append('payment_id', paymentId);
        fd.append('decision', decision);
        <?php if($this->security): ?>
        fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
        <?php endif; ?>
        mpFetchJson('<?= base_url('online_store/decide_order_payment'); ?>', {method:'POST', body:fd})
          .then(d => { showToast(d.message || 'Done', d.status !== 'success'); if(d.status === 'success') setTimeout(()=>location.reload(), 900); })
          .catch(err => showToast(mpErrorText(err, 'MartPoint could not be reached. Nothing was changed.'), true));
      });
    }
    function markDelivery(status, rider){
      var fd = new FormData();
      fd.append('order_id', <?= (int)$order->id; ?>);
      fd.append('status', status);
      fd.append('rider', rider || '');
      fd.append('note', '');
      <?php if($this->security): ?>
      fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
      <?php endif; ?>
      mpFetchJson('<?= base_url('online_store/update_delivery_status'); ?>', {method:'POST', body:fd})
        .then(d => {
          showToast((d.message || 'Done') + (d.notified ? ' — the customer was ' + d.notified : ''), d.status !== 'success');
          if(d.status === 'success') setTimeout(()=>location.reload(), 900);
        })
        .catch(err => showToast(mpErrorText(err, 'MartPoint could not be reached. Nothing was changed.'), true));
    }
    function sendOut(){
      mpPrompt({title:'Who is taking it?', message:'The rider's name if you know it. You can leave this empty.', placeholder:'e.g. Chidi', required:false, okText:'Send it out'})
        .then(function(raw){
          if(raw === null) return;
          markDelivery('out_for_delivery', (raw || '').trim());
        });
    }
  </script>
</body>
</html>