<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= $SITE_TITLE ?? 'MartPoint'; ?> — Purchase View</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-info: #3B82F6; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 140px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 18px; font-weight: 700; margin: 0; }
    .card { background: #fff; border-radius: 14px; padding: 14px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .card-title { font-size: 15px; font-weight: 700; margin-bottom: 10px; }
    .row { display: flex; justify-content: space-between; margin: 8px 0; font-size: 14px; }
    .row .label { color: var(--mp-muted); }
    .row .value { font-weight: 600; text-align: right; }
    .badge { display: inline-block; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 20px; }
    .badge.draft { background: #F1F5F9; color: #334155; }
    .badge.ordered { background: #DBEAFE; color: #1E40AF; }
    .badge.partial { background: #FEF3C7; color: #B45309; }
    .badge.received { background: #D1FAE5; color: #065F46; }
    .badge.unpaid { background: #FEE2E2; color: #B91C1B; }
    .badge.partial-paid { background: #FEF3C7; color: #B45309; }
    .badge.paid { background: #D1FAE5; color: #065F46; }
    .option-chips { display: flex; flex-wrap: nowrap; border: 1px solid var(--mp-border); border-radius: 14px; overflow: hidden; background: var(--mp-surface); margin-bottom: 12px; }
    .option-chips button { flex: 1; min-width: 0; padding: 12px 6px; border: none; border-left: 1px solid var(--mp-border); border-radius: 0; background: var(--mp-surface); color: var(--mp-text); font-size: 13px; font-weight: 600; cursor: pointer; white-space: nowrap; }
    .option-chips button:first-child { border-left: none; border-top-left-radius: 14px; border-bottom-left-radius: 14px; }
    .option-chips button:last-child { border-top-right-radius: 14px; border-bottom-right-radius: 14px; }
    .option-chips button.active { background: var(--mp-primary); color: #fff; border-left-color: var(--mp-primary); }
    .btn { width: 100%; padding: 12px; border: none; border-radius: 12px; background: var(--mp-primary); color: #fff; font-size: 14px; font-weight: 700; cursor: pointer; }
    .btn.secondary { background: var(--mp-bg); color: var(--mp-text); }
    .actions { display: flex; gap: 10px; margin-top: 16px; flex-wrap: wrap; }
    .action { flex: 1; min-width: 80px; text-align: center; padding: 12px 0; border-radius: 12px; font-size: 14px; font-weight: 600; text-decoration: none; color: #fff; }
    .action.primary { background: var(--mp-primary); }
    .action.success { background: var(--mp-success); }
    .action.warning { background: var(--mp-warning); }
    .action.danger { background: var(--mp-danger); }
    .item-row { border-bottom: 1px solid var(--mp-border); padding: 10px 0; }
    .item-row:last-child { border-bottom: none; }
    .item-name { font-weight: 600; font-size: 14px; margin-bottom: 4px; }
    .item-detail { font-size: 12px; color: var(--mp-muted); }
    .total-box { background: #E0E7FF; border-radius: 14px; padding: 14px; margin-top: 12px; }
    .total-box .grand { font-size: 22px; font-weight: 700; color: var(--mp-primary); text-align: right; }
    .payment-row { border-bottom: 1px solid var(--mp-border); padding: 10px 0; }
    .payment-row:last-child { border-bottom: none; }
    .bottom-nav { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 430px; background: #fff; border-top: 1px solid var(--mp-border); display: flex; justify-content: space-around; padding: 8px 0 calc(8px + var(--safe-bottom)); z-index: 1000; }
    .nav-item { display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 6px 14px; border: none; background: transparent; color: var(--mp-muted); font-size: 10px; font-weight: 500; text-decoration: none; }
    .nav-item .icon { font-size: 20px; }
    .nav-item.active { color: var(--mp-primary); }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .bottom-nav { max-width: 100%; left: 0; right: 0; transform: none; } .screen { padding: 16px 16px 120px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 140px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/purchase'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Purchase <?= $p->purchase_code; ?></h1>
        </div>
      </div>

      <?php
        $status_map = ['received' => 'received', 'partially received' => 'partial', 'ordered' => 'ordered'];
        $status_class = $status_map[strtolower($p->purchase_status ?? '')] ?? 'draft';
        $payment_map = ['paid' => 'paid', 'partial' => 'partial-paid'];
        $payment_class = $payment_map[strtolower($p->payment_status ?? '')] ?? 'unpaid';
      ?>

      <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
          <div class="card-title" style="margin:0;"><?= $p->purchase_code; ?></div>
          <div>
            <span class="badge <?= $status_class; ?>" style="margin-right:4px;"><?= $p->purchase_status; ?></span>
            <span class="badge <?= $payment_class; ?>"><?= $p->payment_status; ?></span>
          </div>
        </div>
        <div class="row"><span class="label">Supplier</span><span class="value"><?= $p->supplier_name ?: 'Unknown'; ?></span></div>
        <div class="row"><span class="label">Date</span><span class="value"><?= show_date($p->purchase_date); ?></span></div>
        <?php if(!empty($p->reference_no)): ?><div class="row"><span class="label">Reference</span><span class="value"><?= $p->reference_no; ?></span></div><?php endif; ?>
        <?php if(!empty($p->created_by)): ?><div class="row"><span class="label">Created by</span><span class="value"><?= $p->created_by; ?></span></div><?php endif; ?>

        <div class="actions">
          <?php if(permissions('purchase_edit')): ?>
            <a href="<?= base_url('mobile/purchase_form/'.$p->id); ?>" class="action primary"><i class="fa fa-edit"></i> Edit</a>
          <?php endif; ?>
          <?php if($p->grand_total - $p->paid_amount > 0 && permissions('purchase_payment_add')): ?>
            <a href="<?= base_url('mobile/purchase_payment/'.$p->id); ?>" class="action success"><i class="fa fa-money"></i> Pay</a>
          <?php endif; ?>
          <a href="<?= base_url('purchase/print_invoice/'.$p->id); ?>" target="_blank" class="action warning"><i class="fa fa-print"></i> Print</a>
          <a href="<?= base_url('purchase/pdf/'.$p->id); ?>" target="_blank" class="action danger"><i class="fa fa-file-pdf-o"></i> PDF</a>
        </div>
      </div>

      <?php if(permissions('purchase_edit')): ?>
        <div class="card">
          <div class="card-title">Quick Status Update</div>
          <form id="status-form" method="post" action="<?= base_url('mobile/purchase_status/'.$p->id); ?>">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
            <input type="hidden" id="purchase_status" name="purchase_status" value="<?= $p->purchase_status; ?>">
            <div class="option-chips" id="status_chips">
              <button type="button" data-value="Draft" class="<?= $p->purchase_status == 'Draft' ? 'active' : ''; ?>">Draft</button>
              <button type="button" data-value="Ordered" class="<?= $p->purchase_status == 'Ordered' ? 'active' : ''; ?>">Ordered</button>
              <button type="button" data-value="Partially Received" class="<?= $p->purchase_status == 'Partially Received' ? 'active' : ''; ?>">Partially</button>
              <button type="button" data-value="Received" class="<?= $p->purchase_status == 'Received' ? 'active' : ''; ?>">Received</button>
            </div>
            <button type="submit" class="btn secondary">Update Status</button>
          </form>
        </div>
      <?php endif; ?>

      <div class="card">
        <div class="card-title">Items</div>
        <?php if(!empty($items)): ?>
          <?php foreach($items as $it): ?>
            <div class="item-row">
              <div class="item-name"><?= $it->item_name; ?></div>
              <div class="item-detail"><?= store_number_format($it->purchase_qty); ?> x <?= store_number_format($it->price_per_unit); ?><?php if(!empty($it->tax_amt) && $it->tax_amt > 0): ?> + tax <?= store_number_format($it->tax_amt); ?><?php endif; ?></div>
              <div class="item-detail" style="font-weight:600; color:var(--mp-text);">Total: <?= store_number_format($it->total_cost); ?></div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="item-detail">No items.</div>
        <?php endif; ?>

        <div class="total-box">
          <div class="row"><span class="label">Subtotal</span><span class="value"><?= store_number_format($p->subtotal); ?></span></div>
          <?php if(!empty($p->tot_discount_to_all_amt) && $p->tot_discount_to_all_amt != 0): ?>
            <div class="row"><span class="label">Discount</span><span class="value">- <?= store_number_format($p->tot_discount_to_all_amt); ?></span></div>
          <?php endif; ?>
          <?php if(!empty($p->other_charges_amt) && $p->other_charges_amt != 0): ?>
            <div class="row"><span class="label">Other charges</span><span class="value">+ <?= store_number_format($p->other_charges_amt); ?></span></div>
          <?php endif; ?>
          <?php if(!empty($p->round_off) && $p->round_off != 0): ?>
            <div class="row"><span class="label">Round off</span><span class="value"><?= store_number_format($p->round_off); ?></span></div>
          <?php endif; ?>
          <div class="grand"><?= store_number_format($p->grand_total); ?></div>
        </div>
      </div>

      <div class="card">
        <div class="card-title">Payments</div>
        <?php if(!empty($payments)): ?>
          <?php foreach($payments as $pay): ?>
            <div class="payment-row">
              <div class="row"><span class="label"><?= $pay->payment_type; ?></span><span class="value"><?= store_number_format($pay->payment); ?></span></div>
              <?php if(!empty($pay->payment_note)): ?><div class="item-detail"><?= $pay->payment_note; ?></div><?php endif; ?>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="item-detail">No payments recorded.</div>
        <?php endif; ?>
      </div>
    </section>
  </div>

  <?php $this->load->view('mobile/bottom_nav', ['active' => 'purchase']); ?>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    var statusChips = document.getElementById('status_chips');
    if(statusChips){
      statusChips.querySelectorAll('button').forEach(function(btn){
        btn.addEventListener('click', function(){
          statusChips.querySelectorAll('button').forEach(function(b){ b.classList.remove('active'); });
          this.classList.add('active');
          document.getElementById('purchase_status').value = this.getAttribute('data-value');
        });
      });
    }
  </script>
</body>
</html>
