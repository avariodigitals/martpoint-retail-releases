<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Store Credit</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-info: #3B82F6; --mp-purple: #7C3AED; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); min-height: 100vh; overscroll-behavior: none; }
    body { display: flex; flex-direction: column; }
    #app { max-width: 430px; width: 100%; align-self: center; background: var(--mp-surface); flex: 1 0 auto; position: relative; }
    .screen { padding: 12px 12px 100px; flex: 1 0 auto; }
    body .mp-mobile-footer { position: relative !important; width: 100% !important; max-width: 430px !important; align-self: center; left: auto !important; right: auto !important; border-top: 1px solid var(--mp-border); }
    body #app .screen { padding-bottom: 24px !important; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0; }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .section-title { font-size: 15px; font-weight: 700; margin: 0 0 14px; color: var(--mp-text); display: flex; align-items: center; gap: 8px; }
    .card { background: #fff; border-radius: 14px; padding: 14px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .form-group { margin-bottom: 14px; }
    .form-group:last-child { margin-bottom: 0; }
    label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--mp-text); }
    label .req { color: var(--mp-danger); }
    input[type="text"], input[type="number"], textarea, select { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; font-family: inherit; background: #fff; color: var(--mp-text); outline: none; }
    input:focus, textarea:focus, select:focus { border-color: var(--mp-primary); }
    .help-text { font-size: 11px; color: var(--mp-muted); margin-top: 4px; }
    .btn { width: 100%; padding: 14px; border: none; border-radius: 12px; background: var(--mp-primary); color: #fff; font-size: 16px; font-weight: 700; cursor: pointer; }
    .btn:active { background: var(--mp-primary-dark); }
    .btn:disabled { opacity: 0.7; }
    .summary-card { background: linear-gradient(135deg, var(--mp-purple) 0%, #5B21B6 100%); border-radius: 16px; padding: 20px; color: #fff; margin-bottom: 16px; }
    .summary-card .label { font-size: 13px; opacity: 0.9; margin-bottom: 6px; }
    .summary-card .value { font-size: 28px; font-weight: 700; }
    .credit-list { display: flex; flex-direction: column; gap: 12px; }
    .credit-card { background: #fff; border-radius: 14px; padding: 14px; border: 1px solid var(--mp-border); }
    .credit-header { display: flex; justify-content: space-between; align-items: flex-start; gap: 10px; }
    .credit-title { flex: 1; min-width: 0; }
    .credit-title .name { font-weight: 600; font-size: 16px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .credit-title .code { font-size: 12px; color: var(--mp-muted); margin-top: 3px; }
    .badge { display: inline-block; font-size: 11px; font-weight: 600; padding: 3px 8px; border-radius: 20px; flex-shrink: 0; }
    .badge.active { background: #D1FAE5; color: #065F46; }
    .badge.used { background: #DBEAFE; color: #1E40AF; }
    .badge.expired { background: #FEF3C7; color: #92400E; }
    .badge.cancelled { background: #FEE2E2; color: #991B1B; }
    .credit-stats { display: grid; grid-template-columns: repeat(2, 1fr); gap: 10px; margin-top: 14px; padding-top: 14px; border-top: 1px solid var(--mp-border); }
    .stat-label { font-size: 11px; color: var(--mp-muted); text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 2px; }
    .stat-value { font-size: 15px; font-weight: 700; }
    .stat-value.text-muted { color: var(--mp-muted); }
    .expiry { font-size: 12px; color: var(--mp-muted); margin-top: 8px; }
    .credit-actions { display: flex; gap: 8px; margin-top: 14px; }
    .action { flex: 1; text-align: center; padding: 9px 0; border: none; border-radius: 10px; background: var(--mp-bg); color: var(--mp-text); text-decoration: none; font-size: 12px; font-weight: 600; cursor: pointer; }
    .action i { margin-right: 4px; }
    .action.view { background: #E0E7FF; color: var(--mp-primary); }
    .action.cancel { background: #FEF2F2; color: var(--mp-danger); }
    .empty-state { text-align: center; padding: 40px 20px; color: var(--mp-muted); }
    .empty-state i { font-size: 48px; margin-bottom: 12px; display: block; color: var(--mp-border); }
    .mp-select-wrap { position: relative; width: 100%; }
    select.mp-select { display: none !important; }
    .mp-select-trigger { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; background: #fff; color: var(--mp-text); cursor: pointer; display: flex; align-items: center; justify-content: space-between; min-height: 46px; }
    .mp-select-trigger::after { content: '\f0d7'; font-family: 'FontAwesome'; color: var(--mp-muted); font-size: 14px; }
    .mp-select-trigger.placeholder { color: var(--mp-muted); }
    .mp-select-options { display: none; border: 1px solid var(--mp-border); border-top: none; border-radius: 0 0 12px 12px; background: #fff; max-height: 220px; overflow-y: auto; position: absolute; left: 0; right: 0; top: 100%; z-index: 100; }
    .mp-select-wrap.open .mp-select-options { display: block; }
    .mp-select-wrap.open .mp-select-trigger { border-radius: 12px 12px 0 0; }
    .mp-select-option { padding: 12px 14px; cursor: pointer; border-bottom: 1px solid var(--mp-border); font-size: 15px; }
    .mp-select-option:last-child { border-bottom: none; }
    .mp-select-option.active { background: #EFF6FF; color: var(--mp-primary); font-weight: 600; }
    @media (max-width: 360px) { .form-row { grid-template-columns: 1fr; } }
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 100px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 120px; } }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Store Credit</h1>
        </div>
      </div>

      <?php
        $active_credits = array_filter($credits, function($c){ return $c->status == 'active'; });
        $total_balance = array_sum(array_column($active_credits, 'balance'));
      ?>
      <div class="summary-card">
        <div class="label">Active Credit Balance</div>
        <div class="value" title="<?= strip_tags(mp_format_money($total_balance)); ?>"><?= mp_format_money_compact($total_balance); ?></div>
      </div>

      <?php if(permissions('store_credit_add')): ?>
      <div class="card">
        <div class="section-title"><i class="fa fa-plus-circle"></i> Issue Store Credit</div>
        <form id="credit-form">
          <div class="form-group">
            <label>Customer <span class="req">*</span></label>
            <select name="customer_id" id="customer_id" required>
              <option value="">-- Select Customer --</option>
              <?php foreach($customers as $c): ?>
              <option value="<?= $c->id; ?>"><?= htmlspecialchars($c->customer_name); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Amount <span class="req">*</span></label>
              <input type="number" name="amount" step="0.01" min="0.01" required placeholder="0.00">
            </div>
            <div class="form-group">
              <label>Source</label>
              <select name="source" id="source">
                <option value="refund">Refund</option>
                <option value="return">Product Return</option>
                <option value="compensation">Compensation</option>
                <option value="manual">Manual Credit</option>
                <option value="promotion">Promotion</option>
                <option value="loyalty_conversion">Loyalty Conversion</option>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Expiry (Days)</label>
              <input type="number" name="expiry_days" min="0" value="0" placeholder="0 = Never expires">
              <div class="help-text">0 = Never expires</div>
            </div>
            <div class="form-group">
              <label>Notes</label>
              <textarea name="notes" rows="3" placeholder="Optional note"></textarea>
            </div>
          </div>
          <button type="submit" class="btn" id="save-btn">Issue Credit</button>
        </form>
      </div>
      <?php endif; ?>

      <div class="credit-list">
        <?php if(!empty($credits)): ?>
          <?php foreach($credits as $c): ?>
            <div class="credit-card" data-status="<?= $c->status; ?>">
              <div class="credit-header">
                <div class="credit-title">
                  <div class="name"><?= htmlspecialchars($c->customer_name ?? 'Walk-in'); ?></div>
                  <div class="code"><?= $c->credit_code; ?> · <?= ucfirst(str_replace('_',' ',$c->source)); ?></div>
                </div>
                <span class="badge <?= $c->status; ?>"><?= ucfirst($c->status); ?></span>
              </div>
              <div class="credit-stats">
                <div>
                  <div class="stat-label">Amount</div>
                  <div class="stat-value"><?= $currency_code . ' ' . number_format($c->amount, 2); ?></div>
                </div>
                <div>
                  <div class="stat-label">Balance</div>
                  <div class="stat-value <?= $c->status == 'active' ? '' : 'text-muted'; ?>"><?= $currency_code . ' ' . number_format($c->balance, 2); ?></div>
                </div>
              </div>
              <?php if(!empty($c->expiry_date)): ?>
              <div class="expiry">Expires <?= show_date($c->expiry_date); ?></div>
              <?php endif; ?>
              <div class="credit-actions">
                <?php if(permissions('store_credit_view') && $c->status == 'active'): ?>
                <a href="<?= base_url('store_credit/print_credit/'.$c->id); ?>" target="_blank" class="action view"><i class="fa fa-print"></i> Print</a>
                <?php endif; ?>
                <?php if(permissions('store_credit_delete') && $c->status == 'active'): ?>
                <button class="action cancel" data-id="<?= $c->id; ?>"><i class="fa fa-ban"></i> Cancel</button>
                <?php endif; ?>
              </div>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="empty-state"><i class="fa fa-credit-card"></i><p>No store credit issued yet.</p></div>
        <?php endif; ?>
      </div>
    </section>
  </div>

  <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
  (function(){
    var tokenName = '<?= $this->security->get_csrf_token_name(); ?>';
    var token = '<?= $this->security->get_csrf_hash(); ?>';

    function buildMpSelect(sel){
      if(sel.dataset.mpBuilt) return;
      sel.dataset.mpBuilt = '1';
      sel.classList.add('mp-select');
      sel.style.display = 'none';
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
      function render(){
        list.innerHTML = '';
        Array.from(sel.options).forEach(function(opt, idx){
          var div = document.createElement('div');
          div.className = 'mp-select-option';
          div.textContent = opt.textContent;
          if(sel.selectedIndex === idx) div.classList.add('active');
          div.addEventListener('click', function(e){
            e.stopPropagation();
            sel.selectedIndex = idx;
            sel.dispatchEvent(new Event('change', {bubbles:true}));
            sel.dispatchEvent(new Event('mpupdate', {bubbles:true}));
            closeAll();
          });
          list.appendChild(div);
        });
        var selected = sel.options[sel.selectedIndex];
        trigger.textContent = (selected && selected.textContent) ? selected.textContent : 'Select';
        trigger.classList.toggle('placeholder', !sel.value);
        wrap.classList.remove('open');
      }
      trigger.addEventListener('click', function(e){
        e.stopPropagation();
        closeAll();
        wrap.classList.add('open');
      });
      sel.addEventListener('mpupdate', render);
      render();
    }

    function closeAll(){ document.querySelectorAll('.mp-select-wrap.open').forEach(function(w){ w.classList.remove('open'); }); }
    document.addEventListener('click', closeAll);
    document.querySelectorAll('select').forEach(buildMpSelect);

    var form = document.getElementById('credit-form');
    if(form){
      form.addEventListener('submit', function(e){
        e.preventDefault();
        var btn = document.getElementById('save-btn');
        btn.disabled = true;
        btn.textContent = 'Saving...';
        var fd = new FormData(form);
        fd.append(tokenName, token);
        fetch('<?= base_url('store_credit/save'); ?>', { method: 'POST', body: fd })
        .then(function(r){ return r.text(); })
        .then(function(res){
          btn.disabled = false;
          btn.textContent = 'Issue Credit';
          if(res === 'success'){
            if(typeof mpSuccess !== 'undefined') mpSuccess('Store credit issued.');
            else alert('Store credit issued.');
            setTimeout(function(){ window.location.reload(); }, 700);
          } else {
            if(typeof mpError !== 'undefined') mpError(res);
            else alert(res);
          }
        })
        .catch(function(){
          btn.disabled = false;
          btn.textContent = 'Issue Credit';
          if(typeof mpError !== 'undefined') mpError('Network error. Please try again.');
          else alert('Network error. Please try again.');
        });
      });
    }

    document.querySelectorAll('.action.cancel').forEach(function(btn){
      btn.addEventListener('click', function(){
        if(!confirm('Cancel this store credit?')) return;
        var id = this.dataset.id;
        var self = this;
        self.disabled = true;
        var fd = new FormData();
        fd.append('id', id);
        fd.append(tokenName, token);
        fetch('<?= base_url('store_credit/cancel_credit'); ?>', { method: 'POST', body: fd })
        .then(function(r){ return r.text(); })
        .then(function(res){
          if(res === 'success'){
            if(typeof mpSuccess !== 'undefined') mpSuccess('Credit cancelled.');
            else alert('Credit cancelled.');
            setTimeout(function(){ window.location.reload(); }, 500);
          } else {
            if(typeof mpError !== 'undefined') mpError(res);
            else alert(res);
            self.disabled = false;
          }
        })
        .catch(function(){
          if(typeof mpError !== 'undefined') mpError('Network error.');
          else alert('Network error.');
          self.disabled = false;
        });
      });
    });
  })();
  </script>
</body>
</html>
