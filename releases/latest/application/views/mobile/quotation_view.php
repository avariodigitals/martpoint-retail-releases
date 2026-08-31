<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= $SITE_TITLE ?? 'MartPoint'; ?> — Quotation View</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 120px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar h1 { font-size: 18px; font-weight: 700; margin: 0;}
    .card { background: #fff; border-radius: 14px; padding: 14px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .card-title { font-size: 15px; font-weight: 700; margin-bottom: 10px; }
    .row { display: flex; justify-content: space-between; margin: 8px 0; font-size: 14px; }
    .row .label { color: var(--mp-muted); }
    .row .value { font-weight: 600; text-align: right; }
    .badge { display: inline-block; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 20px; }
    .badge.converted { background: #D1FAE5; color: #065F46; }
    .badge.active { background: #E0E7FF; color: var(--mp-primary); }
    .badge.expired { background: #FEE2E2; color: #B91C1B; }
    .actions { display: flex; gap: 10px; margin-top: 16px; }
    .action { flex: 1; text-align: center; padding: 12px 0; border-radius: 12px; font-size: 14px; font-weight: 600; text-decoration: none; color: #fff; }
    .action.primary { background: var(--mp-primary); }
    .action.success { background: var(--mp-success); }
    .action.warning { background: var(--mp-warning); }
    .action.delete { background: #FEF2F2; color: #B91C1B; }
    .item-row { border-bottom: 1px solid var(--mp-border); padding: 10px 0; }
    .item-row:last-child { border-bottom: none; }
    .item-name { font-weight: 600; font-size: 14px; margin-bottom: 4px; }
    .item-detail { font-size: 12px; color: var(--mp-muted); }
    .total-box { background: #E0E7FF; border-radius: 14px; padding: 14px; margin-top: 12px; }
    .total-box .grand { font-size: 22px; font-weight: 700; color: var(--mp-primary); text-align: right; }
    .bottom-nav { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 430px; background: #fff; border-top: 1px solid var(--mp-border); display: flex; justify-content: space-around; padding: 8px 0 calc(8px + var(--safe-bottom)); z-index: 1000; }
    .nav-item { display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 6px 14px; border: none; background: transparent; color: var(--mp-muted); font-size: 10px; font-weight: 500; text-decoration: none; }
    .nav-item .icon { font-size: 20px; }
    .nav-item.active { color: var(--mp-primary); }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .bottom-nav { max-width: 100%; left: 0; right: 0; transform: none; } .screen { padding: 16px 16px 120px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 140px; } }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/quotations'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Quotation <?= $q->quotation_code; ?></h1>
        </div>
      </div>

      <?php
        $now = date('Y-m-d');
        $status = 'Active'; $badge = 'active';
        if(!empty($q->sales_status)){ $status = 'Converted'; $badge = 'converted'; }
        elseif(!empty($q->expire_date) && $q->expire_date < $now){ $status = 'Expired'; $badge = 'expired'; }
      ?>

      <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px;">
          <div class="card-title" style="margin:0;"><?= $q->quotation_code; ?></div>
          <span class="badge <?= $badge; ?>"><?= $status; ?></span>
        </div>
        <div class="row"><span class="label">Customer</span><span class="value"><?= $q->customer_name ?: 'Walk-in'; ?></span></div>
        <?php if(!empty($q->mobile)): ?><div class="row"><span class="label">Phone</span><span class="value"><?= $q->mobile; ?></span></div><?php endif; ?>
        <div class="row"><span class="label">Date</span><span class="value"><?= show_date($q->quotation_date); ?></span></div>
        <?php if(!empty($q->expire_date)): ?><div class="row"><span class="label">Expires</span><span class="value"><?= show_date($q->expire_date); ?></span></div><?php endif; ?>
        <?php if(!empty($q->reference_no)): ?><div class="row"><span class="label">Reference</span><span class="value"><?= $q->reference_no; ?></span></div><?php endif; ?>
        <?php if(!empty($q->created_by)): ?><div class="row"><span class="label">Created by</span><span class="value"><?= $q->created_by; ?></span></div><?php endif; ?>

        <div class="actions">
          <a href="<?= base_url('quotation/print_invoice/'.$q->id); ?>" target="_blank" class="action warning"><i class="fa fa-print"></i> Print</a>
          <?php if(empty($q->sales_status) && permissions('quotation_edit')): ?>
            <a href="<?= base_url('mobile/quotation_form/'.$q->id); ?>" class="action primary"><i class="fa fa-edit"></i> Edit</a>
          <?php endif; ?>
          <?php if(empty($q->sales_status) && permissions('sales_add')): ?>
            <a href="<?= base_url('sales/quotation/'.$q->id); ?>" class="action success"><i class="fa fa-exchange"></i> Invoice</a>
          <?php endif; ?>
          <?php if(empty($q->sales_status) && permissions('quotation_delete')): ?>
            <a href="javascript:void(0)" class="action delete" onclick="deleteQuotation(<?= (int)$q->id; ?>)"><i class="fa fa-trash"></i> Delete</a>
          <?php endif; ?>
        </div>
      </div>

      <div class="card">
        <div class="card-title">Items</div>
        <?php if(!empty($items)): ?>
          <?php foreach($items as $it): ?>
            <div class="item-row">
              <div class="item-name"><?= $it->item_name; ?></div>
              <div class="item-detail"><?= store_number_format($it->quotation_qty); ?> x <?= store_number_format($it->price_per_unit); ?> <?= !empty($it->tax_amt) ? '+ tax ' . store_number_format($it->tax_amt) : ''; ?></div>
              <div class="item-detail" style="font-weight:600; color:var(--mp-text);">Total: <?= store_number_format($it->total_cost); ?></div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="item-detail">No items.</div>
        <?php endif; ?>

        <div class="total-box">
          <div class="row"><span class="label">Subtotal</span><span class="value"><?= store_number_format($q->subtotal); ?></span></div>
          <?php if(!empty($q->tot_discount_to_all_amt)): ?><div class="row"><span class="label">Discount</span><span class="value">- <?= store_number_format($q->tot_discount_to_all_amt); ?></span></div><?php endif; ?>
          <?php if(!empty($q->other_charges_amt)): ?><div class="row"><span class="label">Extra charges</span><span class="value">+ <?= store_number_format($q->other_charges_amt); ?></span></div><?php endif; ?>
          <?php if(!empty($q->round_off)): ?><div class="row"><span class="label">Round off</span><span class="value"><?= store_number_format($q->round_off); ?></span></div><?php endif; ?>
          <div class="grand"><?= store_number_format($q->grand_total); ?></div>
        </div>
      </div>

      <?php if(!empty($q->quotation_note)): ?>
        <div class="card">
          <div class="card-title">Notes</div>
          <div class="item-detail" style="white-space:pre-line;"><?= nl2br(htmlspecialchars($q->quotation_note)); ?></div>
        </div>
      <?php endif; ?>
    </section>
  </div>

  <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  <?php $this->load->view('mobile/mp_alert'); ?>

  <script>
    function deleteQuotation(id){
      mpConfirm('Delete this quotation?', function(){
        var formData = new FormData();
        formData.append('q_id', id);
        formData.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
        fetch('<?= base_url('mobile/delete_quotation'); ?>', { method: 'POST', body: formData })
        .then(function(res){ return res.json(); })
        .then(function(data){
          if(data.status === 'success'){
            mpSuccess(data.message);
            setTimeout(function(){ window.location.href = '<?= base_url('mobile/quotations'); ?>'; }, 600);
          } else {
            mpError(data.message || 'Delete failed.');
          }
        })
        .catch(function(){ mpError('Network error.'); });
      }, null, {danger: true});
    }
  </script>

  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
