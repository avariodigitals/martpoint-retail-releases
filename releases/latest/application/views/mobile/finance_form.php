<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= $SITE_TITLE ?? 'MartPoint'; ?> — <?= $page_title; ?></title>
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
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0;}
    .section-title { font-size: 15px; font-weight: 700; margin: 20px 0 10px; color: var(--mp-text); }
    .card { background: #fff; border-radius: 14px; padding: 14px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .form-group { margin-bottom: 16px; }
    .form-group:last-child { margin-bottom: 0; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--mp-text); }
    label .req { color: var(--mp-danger); }
    input[type="text"], input[type="number"], input[type="date"], input[type="email"], textarea, select.mp-select {
      width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; font-family: inherit; background: #fff; color: var(--mp-text); outline: none; appearance: none; -webkit-appearance: none;
    }
    input:focus, textarea:focus { border-color: var(--mp-primary); }
    textarea { min-height: 80px; resize: vertical; }
    .mp-select-wrap { position: relative; }
    .mp-select-trigger {
      display: flex; align-items: center; justify-content: space-between;
      padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px;
      background: #fff; font-size: 15px; cursor: pointer;
    }
    .mp-select-options {
      display: none; position: absolute; top: 100%; left: 0; right: 0; z-index: 200;
      background: #fff; border: 1px solid var(--mp-border); border-radius: 12px;
      max-height: 220px; overflow-y: auto; margin-top: 4px; box-shadow: 0 4px 12px rgba(0,0,0,0.08);
    }
    .mp-select-options.open { display: block; }
    .mp-option { padding: 12px 14px; border-bottom: 1px solid var(--mp-border); cursor: pointer; font-size: 14px; }
    .mp-option:last-child { border-bottom: none; }
    .mp-option.active { background: #E0E7FF; color: var(--mp-primary); font-weight: 600; }
    .btn { width: 100%; padding: 14px; border: none; border-radius: 12px; background: var(--mp-primary); color: #fff; font-size: 16px; font-weight: 700; cursor: pointer; }
    .btn:active { background: var(--mp-primary-dark); }
    .alert { padding: 12px 14px; border-radius: 12px; margin-bottom: 14px; font-size: 13px; font-weight: 500; }
    .alert-success { background: #ECFDF5; color: #065F46; }
    .alert-danger { background: #FEF2F2; color: #B91C1C; }
    .bottom-nav { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 430px; background: #fff; border-top: 1px solid var(--mp-border); display: flex; justify-content: space-around; padding: 8px 0 calc(8px + var(--safe-bottom)); z-index: 1000; }
    .nav-item { display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 6px 14px; border: none; background: transparent; color: var(--mp-muted); font-size: 10px; font-weight: 500; text-decoration: none; }
    .nav-item .icon { font-size: 20px; }
    .nav-item.active { color: var(--mp-primary); }
    @media (max-width: 360px) { .form-row { grid-template-columns: 1fr; } }
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
        <a href="<?= base_url('mobile/finance/'.$type); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= $page_title; ?></h1>
        </div>
      </div>

      <div id="alertBox"></div>

      <form id="finance-form" method="post" onsubmit="return false;">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" name="store_id" value="<?= get_current_store_id(); ?>">
        <input type="hidden" name="q_id" value="<?= $q_id; ?>">

        <?php if($type == 'accounts'): ?>
          <?php
            $q_id = $q_id ?? 0;
            $account_code = $account_code ?? '';
            $account_name = $account_name ?? '';
            $parent_id = $parent_id ?? '';
            $opening_balance = $opening_balance ?? '0';
            $note = $note ?? '';
          ?>
          <div class="card">
            <div class="form-group">
              <label>Account Code <span class="req">*</span></label>
              <input type="text" name="account_code" value="<?= $account_code; ?>" required>
            </div>
            <div class="form-group">
              <label>Account Name <span class="req">*</span></label>
              <input type="text" name="account_name" value="<?= $account_name; ?>" required>
            </div>
            <div class="form-group">
              <label>Parent Account</label>
              <select class="mp-select" name="parent_id">
                <option value="">None</option>
                <?php foreach($accounts as $a): ?>
                  <option value="<?= $a->id; ?>" <?= ($parent_id == $a->id) ? 'selected' : ''; ?>><?= $a->account_name; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Opening Balance <span class="req">*</span></label>
                <input type="number" step="0.01" name="opening_balance" value="<?= $opening_balance; ?>" required>
              </div>
              <div class="form-group">
                <label>Note</label>
                <input type="text" name="note" value="<?= $note; ?>">
              </div>
            </div>
          </div>

        <?php elseif($type == 'money_transfers'): ?>
          <?php
            $q_id = $q_id ?? 0;
            $transfer_code = $transfer_code ?? get_init_code('money_transfer');
            $transfer_date = $transfer_date ?? date('Y-m-d');
            $reference_no = $reference_no ?? '';
            $debit_account_id = $debit_account_id ?? '';
            $credit_account_id = $credit_account_id ?? '';
            $amount = $amount ?? '';
            $note = $note ?? '';
          ?>
          <div class="card">
            <div class="form-row">
              <div class="form-group">
                <label>Transfer Code <span class="req">*</span></label>
                <input type="text" name="transfer_code" value="<?= $transfer_code; ?>" required>
              </div>
              <div class="form-group">
                <label>Date <span class="req">*</span></label>
                <input type="date" name="transfer_date" value="<?= $transfer_date; ?>" required>
              </div>
            </div>
            <div class="form-group">
              <label>From Account <span class="req">*</span></label>
              <select class="mp-select" name="debit_account_id" required>
                <option value="">Select</option>
                <?php foreach($accounts as $a): ?>
                  <option value="<?= $a->id; ?>" <?= ($debit_account_id == $a->id) ? 'selected' : ''; ?>><?= $a->account_name; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>To Account <span class="req">*</span></label>
              <select class="mp-select" name="credit_account_id" required>
                <option value="">Select</option>
                <?php foreach($accounts as $a): ?>
                  <option value="<?= $a->id; ?>" <?= ($credit_account_id == $a->id) ? 'selected' : ''; ?>><?= $a->account_name; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Amount <span class="req">*</span></label>
                <input type="number" step="0.01" name="amount" value="<?= $amount; ?>" required>
              </div>
              <div class="form-group">
                <label>Reference</label>
                <input type="text" name="reference_no" value="<?= $reference_no; ?>">
              </div>
            </div>
            <div class="form-group">
              <label>Note</label>
              <textarea name="note"><?= $note; ?></textarea>
            </div>
          </div>

        <?php elseif($type == 'money_deposits'): ?>
          <?php
            $q_id = $q_id ?? 0;
            $deposit_date = $deposit_date ?? date('Y-m-d');
            $reference_no = $reference_no ?? '';
            $debit_account_id = $debit_account_id ?? '';
            $credit_account_id = $credit_account_id ?? '';
            $amount = $amount ?? '';
            $note = $note ?? '';
          ?>
          <div class="card">
            <div class="form-row">
              <div class="form-group">
                <label>Date <span class="req">*</span></label>
                <input type="date" name="deposit_date" value="<?= $deposit_date; ?>" required>
              </div>
              <div class="form-group">
                <label>Reference</label>
                <input type="text" name="reference_no" value="<?= $reference_no; ?>">
              </div>
            </div>
            <div class="form-group">
              <label>From Account</label>
              <select class="mp-select" name="debit_account_id">
                <option value="">Cash / Till</option>
                <?php foreach($accounts as $a): ?>
                  <option value="<?= $a->id; ?>" <?= ($debit_account_id == $a->id) ? 'selected' : ''; ?>><?= $a->account_name; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>To Account <span class="req">*</span></label>
              <select class="mp-select" name="credit_account_id" required>
                <option value="">Select</option>
                <?php foreach($accounts as $a): ?>
                  <option value="<?= $a->id; ?>" <?= ($credit_account_id == $a->id) ? 'selected' : ''; ?>><?= $a->account_name; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Amount <span class="req">*</span></label>
              <input type="number" step="0.01" name="amount" value="<?= $amount; ?>" required>
            </div>
            <div class="form-group">
              <label>Note</label>
              <textarea name="note"><?= $note; ?></textarea>
            </div>
          </div>

        <?php elseif($type == 'tills'): ?>
          <?php
            $till = $till ?? (object)['id'=>'','till_name'=>'','cashier_user_id'=>'','account_id'=>'','is_default'=>0];
            $q_id = $till->id ?? $q_id;
          ?>
          <div class="card">
            <input type="hidden" name="id" value="<?= $q_id; ?>">
            <div class="form-group">
              <label>Till Name <span class="req">*</span></label>
              <input type="text" name="till_name" value="<?= $till->till_name; ?>" required>
            </div>
            <div class="form-group">
              <label>Cashier <span class="req">*</span></label>
              <select class="mp-select" name="cashier_user_id" required>
                <option value="">Select</option>
                <?php foreach($users as $u): ?>
                  <option value="<?= $u->id; ?>" <?= ($till->cashier_user_id == $u->id) ? 'selected' : ''; ?>><?= ($u->first_name.' '.$u->last_name); ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label>Account <span class="req">*</span></label>
              <select class="mp-select" name="account_id" required>
                <option value="">Select</option>
                <?php foreach($accounts as $a): ?>
                  <option value="<?= $a->id; ?>" <?= ($till->account_id == $a->id) ? 'selected' : ''; ?>><?= $a->account_name; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-group">
              <label style="display:flex;align-items:center;gap:8px;">
                <input type="checkbox" name="is_default" value="1" <?= ($till->is_default) ? 'checked' : ''; ?>> Default Till
              </label>
            </div>
          </div>

        <?php elseif($type == 'expenses'): ?>
          <?php
            $q_id = $q_id ?? 0;
            $expense_code = $expense_code ?? get_init_code('expense');
            $expense_date = $expense_date ?? date('Y-m-d');
            $category_id = $category_id ?? '';
            $reference_no = $reference_no ?? '';
            $expense_for = $expense_for ?? '';
            $expense_amt = $expense_amt ?? '';
            $payment_type = $payment_type ?? 'cash';
            $account_id = $account_id ?? '';
            $note = $note ?? '';
          ?>
          <div class="card">
            <div class="form-row">
              <div class="form-group">
                <label>Expense Code</label>
                <input type="text" name="expense_code" value="<?= $expense_code; ?>">
              </div>
              <div class="form-group">
                <label>Date <span class="req">*</span></label>
                <input type="date" name="expense_date" value="<?= $expense_date; ?>" required>
              </div>
            </div>
            <div class="form-group">
              <label>Category <span class="req">*</span></label>
              <select class="mp-select" name="category_id" required>
                <option value="">Select</option>
                <?php foreach($categories as $c): ?>
                  <option value="<?= $c->id; ?>" <?= ($category_id == $c->id) ? 'selected' : ''; ?>><?= $c->category_name; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Amount <span class="req">*</span></label>
                <input type="number" step="0.01" name="expense_amt" value="<?= $expense_amt; ?>" required>
              </div>
              <div class="form-group">
                <label>Expense For <span class="req">*</span></label>
                <input type="text" name="expense_for" value="<?= $expense_for; ?>" required>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Payment Mode <span class="req">*</span></label>
                <select class="mp-select" name="payment_type" required>
                  <option value="">— Select —</option>
                  <?= get_payment_modes_select_list(get_current_store_id(), $payment_type); ?>
                </select>
              </div>
              <div class="form-group">
                <label>Account</label>
                <select class="mp-select" name="account_id">
                  <option value="">Select</option>
                  <?php foreach($accounts as $a): ?>
                    <option value="<?= $a->id; ?>" <?= ($account_id == $a->id) ? 'selected' : ''; ?>><?= $a->account_name; ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
            </div>
            <div class="form-group">
              <label>Reference</label>
              <input type="text" name="reference_no" value="<?= $reference_no; ?>">
            </div>
            <div class="form-group">
              <label>Note</label>
              <textarea name="note"><?= $note; ?></textarea>
            </div>
          </div>
        <?php endif; ?>

        <button type="button" id="saveBtn" class="btn">Save</button>
      </form>
    </section>
  </div>

  <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>

  <script>
    var base_url = '<?= base_url(); ?>';
    var type = '<?= $type; ?>';
    var q_id = '<?= $q_id; ?>';

    // mp-select setup
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
      trigger.addEventListener('click', function(e){
        e.stopPropagation();
        document.querySelectorAll('.mp-select-options.open').forEach(function(o){ if(o !== opts) o.classList.remove('open'); });
        opts.classList.toggle('open');
      });
    });

    document.addEventListener('click', function(){ document.querySelectorAll('.mp-select-options.open').forEach(function(o){ o.classList.remove('open'); }); });

    function showAlert(msg, type){
      mpAlert(msg, type);
    }

    document.getElementById('saveBtn').addEventListener('click', async function(){
      var form = document.getElementById('finance-form');
      var fd = new FormData(form);

      var endpoint;
      if(type == 'accounts') endpoint = q_id > 0 ? 'accounts/update_accounts' : 'accounts/newaccounts';
      else if(type == 'money_transfers') endpoint = q_id > 0 ? 'money_transfer/update_money_transfer' : 'money_transfer/new_money_transfer';
      else if(type == 'money_deposits') endpoint = q_id > 0 ? 'money_deposit/update_money_deposit' : 'money_deposit/new_money_deposit';
      else if(type == 'expenses') endpoint = q_id > 0 ? 'expense/update_expense' : 'expense/newexpense';
      else if(type == 'tills') endpoint = 'mobile/save_till';
      else { showAlert('Invalid type', 'danger'); return; }

      try {
        var res = await fetch(base_url + endpoint, {method: 'POST', body: fd});
        var txt = await res.text();
        if(txt.trim() == 'success'){
          showAlert('Saved successfully', 'success');
          setTimeout(function(){ window.location.href = base_url + 'mobile/finance/' + type; }, 600);
        } else {
          showAlert(txt.replace(/<[^>]*>/g, '') || 'Save failed', 'danger');
        }
      } catch(err){
        showAlert('Network error. Please try again.', 'danger');
      }
    });
  </script>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
