<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Pay Purchase</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 140px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0; flex: 1; }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .card { background: #fff; border-radius: 14px; padding: 14px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .due-box { background: #FEE2E2; border-radius: 12px; padding: 14px; margin-bottom: 16px; }
    .due-box .label { font-size: 13px; color: #991B1B; font-weight: 600; }
    .due-box .value { font-size: 24px; font-weight: 700; color: #B91C1C; }
    .form-group { margin-bottom: 16px; }
    label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--mp-text); }
    label .req { color: var(--mp-danger); }
    input[type="text"], input[type="number"], input[type="date"], textarea, select.mp-select {
      width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; font-family: inherit; background: #fff; color: var(--mp-text); outline: none; appearance: none; -webkit-appearance: none;
    }
    input:focus, textarea:focus { border-color: var(--mp-primary); }
    .pm-list { display: flex; flex-wrap: wrap; gap: 8px; }
    .pm-option { display: flex; align-items: center; gap: 6px; padding: 10px 12px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 14px; cursor: pointer; background: #fff; font-weight: 500; position: relative; }
    .pm-option input { position: absolute; opacity: 0; }
    .pm-option:has(input:checked) { background: #E0E7FF; border-color: var(--mp-primary); color: var(--mp-primary); }
    .mp-select-wrap { position: relative; }
    .mp-select-trigger { display: flex; align-items: center; justify-content: space-between; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; background: #fff; font-size: 15px; cursor: pointer; }
    .mp-select-options { display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 200; background: #fff; border: 1px solid var(--mp-border); border-radius: 12px; max-height: 220px; overflow-y: auto; margin-top: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    .mp-select-options.open { display: block; }
    .mp-option { padding: 12px 14px; border-bottom: 1px solid var(--mp-border); cursor: pointer; font-size: 14px; }
    .mp-option:last-child { border-bottom: none; }
    .mp-option.active { background: #E0E7FF; color: var(--mp-primary); font-weight: 600; }
    .btn { width: 100%; padding: 14px; border: none; border-radius: 12px; background: var(--mp-success); color: #fff; font-size: 16px; font-weight: 700; cursor: pointer; }
    .btn:active { opacity: 0.9; }
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
        <a href="<?= base_url('mobile/purchase_view/' . $purchase->id); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Pay Purchase</h1>
        </div>
      </div>

      <div class="card">
        <div class="due-box">
          <div class="label">Amount Due</div>
          <div class="value" title="<?= strip_tags(mp_format_money($due)); ?>"><?= mp_format_money_compact($due); ?></div>
        </div>

        <form id="payment-form" onsubmit="return false;">
          <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
          <input type="hidden" name="purchase_id" value="<?= $purchase->id; ?>">
          <input type="hidden" name="supplier_id" value="<?= $purchase->supplier_id; ?>">

          <div class="form-group">
            <label>Amount <span class="req">*</span></label>
            <input type="number" step="0.01" name="amount" id="amount" value="<?= number_format($due, 2, '.', ''); ?>" min="0.01" max="<?= number_format($due, 2, '.', ''); ?>" required>
          </div>

          <div class="form-group">
            <label>Payment Date <span class="req">*</span></label>
            <input type="date" name="payment_date" value="<?= date('Y-m-d'); ?>" required>
          </div>

          <div class="form-group">
            <label>Payment Mode <span class="req">*</span></label>
            <div class="pm-list">
              <?php if(!empty($payment_modes)): ?>
                <?php foreach($payment_modes as $i => $pm): ?>
                  <label class="pm-option">
                    <input type="radio" name="payment_type" value="<?= $pm->code; ?>" <?= $i === 0 ? 'checked' : ''; ?> required>
                    <span><?= htmlspecialchars($pm->name); ?></span>
                  </label>
                <?php endforeach; ?>
              <?php else: ?>
                <label class="pm-option">
                  <input type="radio" name="payment_type" value="cash" checked required>
                  <span>Cash</span>
                </label>
              <?php endif; ?>
            </div>
          </div>

          <div class="form-group">
            <label>Deposit Account</label>
            <select class="mp-select" name="account_id" id="account_id">
              <option value="">Select account</option>
              <?php foreach($accounts as $a): ?>
                <option value="<?= $a->id; ?>"><?= $a->account_name; ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-group">
            <label>Payment Note</label>
            <textarea name="payment_note" rows="3" placeholder="Optional note..."></textarea>
          </div>

          <button type="button" id="saveBtn" class="btn"><i class="fa fa-save"></i> Save Payment</button>
        </form>
      </div>
    </section>
  </div>

  <?php $this->load->view('mobile/bottom_nav', ['active' => 'purchase']); ?>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    var base_url = '<?= base_url(); ?>';

    document.querySelectorAll('select.mp-select').forEach(function(sel){
      var wrap = document.createElement('div'); wrap.className = 'mp-select-wrap';
      sel.parentNode.insertBefore(wrap, sel);
      wrap.appendChild(sel);
      sel.style.display = 'none';
      var trigger = document.createElement('div'); trigger.className = 'mp-select-trigger';
      var opts = document.createElement('div'); opts.className = 'mp-select-options';
      var label = document.createElement('span');
      var icon = document.createElement('i'); icon.className = 'fa fa-chevron-down'; icon.style.fontSize = '12px';
      trigger.appendChild(label); trigger.appendChild(icon);
      wrap.appendChild(trigger); wrap.appendChild(opts);
      function setLabel(){
        var txt = sel.options[sel.selectedIndex] ? sel.options[sel.selectedIndex].text : 'Select';
        label.textContent = txt;
      }
      Array.from(sel.options).forEach(function(opt, idx){
        var d = document.createElement('div'); d.className = 'mp-option'; d.textContent = opt.text;
        if(idx == sel.selectedIndex) d.classList.add('active');
        d.addEventListener('click', function(e){
          e.stopPropagation();
          sel.selectedIndex = idx;
          setLabel();
          opts.querySelectorAll('.mp-option').forEach(function(o){ o.classList.remove('active'); });
          d.classList.add('active');
          opts.classList.remove('open');
          sel.dispatchEvent(new Event('change'));
        });
        opts.appendChild(d);
      });
      setLabel();
      trigger.addEventListener('click', function(e){ e.stopPropagation(); document.querySelectorAll('.mp-select-options.open').forEach(function(o){ if(o !== opts) o.classList.remove('open'); }); opts.classList.toggle('open'); });
    });
    document.addEventListener('click', function(){ document.querySelectorAll('.mp-select-options.open').forEach(function(o){ o.classList.remove('open'); }); });

    document.getElementById('saveBtn').addEventListener('click', async function(){
      var form = document.getElementById('payment-form');
      var amount = parseFloat(document.getElementById('amount').value) || 0;
      var typeEl = form.querySelector('input[name="payment_type"]:checked');
      var type = typeEl ? typeEl.value : '';
      var due = <?= (float) $due; ?>;

      if(amount <= 0){ mpAlert('Amount must be greater than 0', 'danger'); return; }
      if(amount > due){ mpAlert('Amount cannot exceed the due amount', 'danger'); return; }
      if(!type){ mpAlert('Please select a payment type', 'danger'); return; }

      var btn = this;
      btn.disabled = true;
      try {
        var res = await fetch(base_url + 'purchase/save_payment', {method: 'POST', body: new FormData(form)});
        var txt = await res.text();
        if(txt.trim() === 'success'){
          mpAlert('Payment saved', 'success');
          setTimeout(function(){ window.location.href = base_url + 'mobile/purchase_view/' + form.querySelector('[name="purchase_id"]').value; }, 600);
        } else {
          mpAlert(txt.replace(/<[^>]*>/g, '').trim() || 'Payment failed', 'danger');
          btn.disabled = false;
        }
      } catch(err){
        mpAlert('Network error. Please try again.', 'danger');
        btn.disabled = false;
      }
    });
  </script>
</body>
</html>
