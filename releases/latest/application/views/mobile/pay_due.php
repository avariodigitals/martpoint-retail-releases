<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Receive Payment</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 120px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .store-name { font-size: 12px; color: var(--mp-muted); margin-bottom: 2px; }
    .topbar h1 { font-size: 18px; font-weight: 700; margin: 0; }
    .card { background: #fff; border-radius: 14px; padding: 14px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .sale-info .row { display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid var(--mp-border); font-size: 14px; }
    .sale-info .row:last-child { border-bottom: none; }
    .sale-info .row span { color: var(--mp-muted); }
    .sale-info .row strong { font-weight: 600; }
    .sale-info .total-due strong { color: var(--mp-danger); font-size: 18px; }
    .form-group { margin-bottom: 16px; }
    .form-group:last-child { margin-bottom: 0; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--mp-text); }
    input[type="number"], input[type="date"], input[type="text"], textarea {
      width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; font-family: inherit; background: #fff; color: var(--mp-text); outline: none;
      appearance: none; -webkit-appearance: none;
    }
    input:focus, textarea:focus { border-color: var(--mp-primary); }
    textarea { min-height: 64px; resize: vertical; }
    .pm-list { display: flex; flex-wrap: wrap; gap: 8px; }
    .pm-option { display: flex; align-items: center; gap: 6px; padding: 10px 12px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 14px; cursor: pointer; background: #fff; font-weight: 500; }
    .pm-option input { position: absolute; opacity: 0; }
    .pm-option:has(input:checked) { background: #E0E7FF; border-color: var(--mp-primary); color: var(--mp-primary); }
    .btn-save { width: 100%; padding: 14px; border: none; border-radius: 12px; background: var(--mp-success); color: #fff; font-size: 16px; font-weight: 700; cursor: pointer; }
    .btn-save:active { background: #059669; }
    .alert { padding: 12px 14px; border-radius: 12px; margin-bottom: 14px; font-size: 13px; font-weight: 500; }
    .alert-success { background: #ECFDF5; color: #065F46; }
    .alert-danger { background: #FEF2F2; color: #B91C1C; }
    @media (max-width: 360px) { .form-row { grid-template-columns: 1fr; } }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 120px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 140px; } }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/due'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Receive Payment</h1>
        </div>
      </div>

      <?php if($this->session->flashdata('success')): ?>
        <div class="alert alert-success"><?= $this->session->flashdata('success'); ?></div>
      <?php endif; ?>
      <?php if($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?= $this->session->flashdata('error'); ?></div>
      <?php endif; ?>

      <div class="card">
        <div class="sale-info">
          <div class="row"><span>Customer</span><strong><?= !empty($customer->customer_name) ? htmlspecialchars($customer->customer_name) : 'Walk-in Customer'; ?></strong></div>
          <div class="row"><span>Invoice</span><strong><?= $sale->sales_code; ?></strong></div>
          <div class="row"><span>Total</span><strong><?= mp_format_money($sale->grand_total); ?></strong></div>
          <div class="row"><span>Paid</span><strong><?= mp_format_money($sale->paid_amount); ?></strong></div>
          <div class="row total-due"><span>Amount Due</span><strong><?= mp_format_money($due); ?></strong></div>
        </div>
      </div>

      <form method="post" action="<?= base_url('mobile/save_due_payment'); ?>">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" name="sales_id" value="<?= $sale->id; ?>">
        <input type="hidden" name="customer_id" value="<?= $sale->customer_id; ?>">
        <input type="hidden" name="store_id" value="<?= get_current_store_id(); ?>">

        <div class="card">
          <div class="form-group">
            <label for="amount">Amount <span style="color:var(--mp-danger)">*</span></label>
            <input type="number" id="amount" name="amount" step="0.01" min="0.01" max="<?= $due; ?>" value="<?= number_format($due, 2, '.', ''); ?>" required>
          </div>
          <div class="form-group">
            <label for="payment_date">Payment Date <span style="color:var(--mp-danger)">*</span></label>
            <input type="date" id="payment_date" name="payment_date" value="<?= date('Y-m-d'); ?>" required>
          </div>
          <div class="form-group">
            <label>Payment Mode</label>
            <div class="pm-list">
              <?php if(!empty($payment_modes)): ?>
                <?php foreach($payment_modes as $i => $pm): ?>
                  <label class="pm-option">
                    <input type="radio" name="payment_type" value="<?= $pm->code; ?>" <?= $i === 0 ? 'checked' : ''; ?>>
                    <span><?= htmlspecialchars($pm->name); ?></span>
                  </label>
                <?php endforeach; ?>
              <?php else: ?>
                <label class="pm-option">
                  <input type="radio" name="payment_type" value="cash" checked>
                  <span>Cash</span>
                </label>
              <?php endif; ?>
            </div>
          </div>
          <div class="form-group">
            <label for="payment_note">Note</label>
            <textarea id="payment_note" name="payment_note" placeholder="Optional payment note"></textarea>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label for="cheque_number">Cheque No.</label>
              <input type="text" id="cheque_number" name="cheque_number" placeholder="Optional">
            </div>
            <div class="form-group">
              <label for="cheque_period">Cheque Period</label>
              <input type="text" id="cheque_period" name="cheque_period" placeholder="Optional">
            </div>
          </div>
        </div>

        <button type="submit" class="btn-save">Save Payment</button>
      </form>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
