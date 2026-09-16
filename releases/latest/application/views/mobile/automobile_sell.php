<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Sell Vehicle</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 120px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
    .topbar .back { color: var(--mp-ink); font-size: 18px; text-decoration: none; }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .card { background: #fff; border: 1px solid var(--mp-border); border-radius: 14px; padding: 14px; margin-bottom: 16px; }
    .card h3 { margin: 0 0 12px; font-size: 16px; }
    .vehicle-summary { font-size: 15px; margin-bottom: 6px; }
    .vehicle-price { font-size: 20px; font-weight: 700; color: var(--mp-primary); }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: 14px; font-weight: 500; margin-bottom: 6px; }
    .form-control { width: 100%; padding: 14px 16px; border: 1px solid var(--mp-border); border-radius: 14px; font-size: 16px; background: #fff; outline: none; min-height: 54px; }
    .form-control:focus { border-color: var(--mp-primary); }
    .mp-select { display: none; }
    .mp-select-wrap { position: relative; width: 100%; }
    .mp-select-trigger { width: 100%; padding: 14px 42px 14px 16px; border: 1px solid var(--mp-border); border-radius: 14px; font-size: 16px; background: #fff; color: var(--mp-text); cursor: pointer; display: flex; align-items: center; justify-content: space-between; min-height: 54px; }
    .mp-select-trigger::after { content: '\f0d7'; font-family: 'FontAwesome'; color: var(--mp-muted); font-size: 14px; }
    .mp-select-trigger.placeholder { color: var(--mp-muted); }
    .mp-select-options { display: none; border: 1px solid var(--mp-border); border-top: none; border-radius: 0 0 14px 14px; background: #fff; max-height: 220px; overflow-y: auto; position: absolute; left: 0; right: 0; top: 100%; z-index: 10; }
    .mp-select-wrap.open .mp-select-options { display: block; }
    .mp-select-wrap.open .mp-select-trigger { border-radius: 14px 14px 0 0; }
    .mp-select-option { padding: 14px 16px; cursor: pointer; border-bottom: 1px solid var(--mp-border); font-size: 16px; }
    .mp-select-option:last-child { border-bottom: none; }
    .mp-select-option:hover, .mp-select-option.active { background: var(--mp-bg); }
    .balance-row { display: flex; justify-content: space-between; padding: 14px 16px; background: var(--mp-bg); border-radius: 14px; font-weight: 600; font-size: 16px; }
    .btn-primary { width: 100%; padding: 18px; border: none; border-radius: 14px; background: var(--mp-success); color: white; font-size: 17px; font-weight: 600; cursor: pointer; margin-top: 10px; }
    .btn-secondary { width: 100%; padding: 18px; border: 1px solid var(--mp-border); border-radius: 14px; background: #fff; color: var(--mp-ink); font-size: 17px; font-weight: 600; cursor: pointer; text-decoration: none; display: block; text-align: center; margin-top: 10px; }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/automobile'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Sell Vehicle</h1>
        </div>
      </div>

      <div class="card">
        <h3><?= htmlspecialchars(($vehicle->year ? $vehicle->year . ' ' : '') . $vehicle->make . ' ' . $vehicle->model); ?></h3>
        <div class="vehicle-summary"><?= htmlspecialchars(ucfirst($vehicle->vehicle_condition)); ?> <?= $vehicle->mileage ? '· ' . number_format($vehicle->mileage) . ' km' : ''; ?></div>
        <div class="vehicle-price"><?= number_format($vehicle->price, 2); ?></div>
      </div>

      <form method="post" action="<?= base_url('mobile/save_sell_vehicle'); ?>">
        <input type="hidden" name="vehicle_id" value="<?= (int) $vehicle->id; ?>">
        <input type="hidden" id="vehicle_price" value="<?= (float) $vehicle->price; ?>">

        <div class="form-group">
          <label>Buyer / Customer Name <span style="color:var(--mp-danger)">*</span></label>
          <input type="text" name="customer_name" class="form-control" value="<?= htmlspecialchars($vehicle->customer_name); ?>" placeholder="e.g. John Doe" required>
        </div>

        <div class="form-group">
          <label>Customer ID (optional)</label>
          <input type="number" name="customer_id" class="form-control" value="<?= (int) $vehicle->customer_id; ?>" placeholder="Existing customer ID">
        </div>

        <div class="form-group">
          <label>Payment Method</label>
          <select class="mp-select" name="payment_method">
            <option value="Cash" selected>Cash</option>
            <option value="Bank Transfer">Bank Transfer</option>
            <option value="Cheque">Cheque</option>
            <option value="Mobile Money">Mobile Money</option>
            <option value="Card">Card</option>
          </select>
        </div>

        <div class="form-group">
          <label>Amount Paid <span style="color:var(--mp-danger)">*</span></label>
          <input type="number" step="0.01" name="amount_paid" id="amount_paid" class="form-control" value="<?= number_format($vehicle->price, 2, '.', ''); ?>" required>
        </div>

        <div class="balance-row">
          <span>Balance</span>
          <span id="balance">0.00</span>
        </div>

        <button type="submit" class="btn-primary"><i class="fa fa-check"></i> Complete Sale &amp; Receipt</button>
        <a href="<?= base_url('mobile/automobile'); ?>" class="btn-secondary">Cancel</a>
      </form>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
    <?php $this->load->view('mobile/mp_alert'); ?>
    <?php $this->load->view('mobile/chat'); ?>
  </div>

  <script>
    function closeAllMpSelects(){
      document.querySelectorAll('.mp-select-wrap.open').forEach(function(w){ w.classList.remove('open'); });
    }
    function initMpSelects(){
      document.querySelectorAll('select.mp-select').forEach(function(sel){
        if(sel.dataset.mpInit) return;
        sel.dataset.mpInit = '1';
        var wrap = document.createElement('div');
        wrap.className = 'mp-select-wrap';
        sel.parentNode.insertBefore(wrap, sel);
        wrap.appendChild(sel);
        var trigger = document.createElement('div');
        trigger.className = 'mp-select-trigger';
        wrap.appendChild(trigger);
        var list = document.createElement('div');
        list.className = 'mp-select-options';
        wrap.appendChild(list);
        var options = Array.from(sel.options);
        function renderOptions(){
          list.innerHTML = '';
          options.forEach(function(opt, idx){
            var div = document.createElement('div');
            div.className = 'mp-select-option';
            div.textContent = opt.textContent;
            if(sel.selectedIndex === idx) div.classList.add('active');
            div.addEventListener('click', function(e){
              e.stopPropagation();
              sel.selectedIndex = idx;
              updateTrigger();
              sel.dispatchEvent(new Event('change', {bubbles: true}));
              closeAllMpSelects();
            });
            list.appendChild(div);
          });
        }
        function updateTrigger(){
          var s = sel.options[sel.selectedIndex];
          trigger.textContent = s ? s.textContent : 'Select';
          trigger.classList.toggle('placeholder', !s || !s.value);
          renderOptions();
        }
        trigger.addEventListener('click', function(e){
          e.stopPropagation();
          closeAllMpSelects();
          wrap.classList.toggle('open');
        });
        sel.addEventListener('change', updateTrigger);
        updateTrigger();
      });
      document.addEventListener('click', closeAllMpSelects);
    }
    document.addEventListener('DOMContentLoaded', function(){
      initMpSelects();
      var price = parseFloat(document.getElementById('vehicle_price').value) || 0;
      var amountInput = document.getElementById('amount_paid');
      var balanceEl = document.getElementById('balance');
      function updateBalance(){
        var paid = parseFloat(amountInput.value) || 0;
        balanceEl.textContent = (price - paid).toFixed(2);
      }
      amountInput.addEventListener('input', updateBalance);
      updateBalance();
    });
  </script>
</body>
</html>
