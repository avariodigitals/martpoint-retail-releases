<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= $SITE_TITLE ?? 'MartPoint'; ?> — New Purchase</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 180px; }
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
    input[type="text"], input[type="number"], input[type="date"], input[type="search"], textarea, select.mp-select {
      width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; font-family: inherit; background: #fff; color: var(--mp-text); outline: none; appearance: none; -webkit-appearance: none;
    }
    input:focus, textarea:focus { border-color: var(--mp-primary); }
    input[readonly] { background: #F8FAFC; }
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
    .option-chips { display: flex; flex-wrap: nowrap; border: 1px solid var(--mp-border); border-radius: 14px; overflow: hidden; background: var(--mp-surface); }
    .option-chips button { flex: 1; min-width: 0; padding: 12px 6px; border: none; border-left: 1px solid var(--mp-border); border-radius: 0; background: var(--mp-surface); color: var(--mp-text); font-size: 13px; font-weight: 600; cursor: pointer; white-space: nowrap; }
    .option-chips button:first-child { border-left: none; border-top-left-radius: 14px; border-bottom-left-radius: 14px; }
    .option-chips button:last-child { border-top-right-radius: 14px; border-bottom-right-radius: 14px; }
    .option-chips button.active { background: var(--mp-primary); color: #fff; border-left-color: var(--mp-primary); }
    .option-chips button.active + button { border-left-color: var(--mp-primary); }
    .totals { background: #E0E7FF; border-radius: 12px; padding: 14px; margin: 16px 0; }
    .totals .value { font-size: 22px; font-weight: 700; color: var(--mp-primary); }
    .btn { width: 100%; padding: 14px; border: none; border-radius: 12px; background: var(--mp-primary); color: #fff; font-size: 16px; font-weight: 700; cursor: pointer; }
    .btn:active { background: var(--mp-primary-dark); }
    .btn:disabled { opacity: 0.7; cursor: not-allowed; }
    .btn-secondary { background: var(--mp-bg); color: var(--mp-text); margin-bottom: 10px; }
    .cart-item { padding: 14px 0; border-bottom: 1px solid var(--mp-border); }
    .cart-item:last-child { border-bottom: none; }
    .cart-item .cart-head { display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px; }
    .cart-item .cart-name { font-size: 15px; font-weight: 700; }
    .cart-item .cart-remove { background: #FEF2F2; color: #DC2626; border: none; border-radius: 8px; padding: 6px 10px; font-size: 12px; font-weight: 600; cursor: pointer; }
    .cart-item .cart-meta { font-size: 12px; color: var(--mp-muted); margin-top: 4px; }
    .cart-item .cart-row { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 8px; margin-bottom: 8px; }
    .cart-item .cart-row .form-group { margin-bottom: 0; }
    .cart-item .cart-row label { font-size: 11px; }
    .empty-state { text-align: center; padding: 32px; color: var(--mp-muted); }
    .bottom-nav { position: fixed; bottom: 0; left: 50%; transform: translateX(-50%); width: 100%; max-width: 430px; background: #fff; border-top: 1px solid var(--mp-border); display: flex; justify-content: space-around; padding: 8px 0 calc(8px + var(--safe-bottom)); z-index: 1000; }
    .nav-item { display: flex; flex-direction: column; align-items: center; gap: 3px; padding: 6px 14px; border: none; background: transparent; color: var(--mp-muted); font-size: 10px; font-weight: 500; text-decoration: none; }
    .nav-item .icon { font-size: 20px; }
    .nav-item.active { color: var(--mp-primary); }
    @media (max-width: 360px) { .form-row, .cart-item .cart-row { grid-template-columns: 1fr; } .option-chips button { font-size: 12px; padding: 10px 4px; } }
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
        <a href="<?= base_url('mobile/purchase'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= $page_title; ?></h1>
        </div>
      </div>

      <form id="purchase-form" onsubmit="return false;">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" name="store_id" value="<?= get_current_store_id(); ?>">
        <input type="hidden" name="purchase_id" value="<?= $purchase_id; ?>">
        <input type="hidden" name="command" value="<?= $command; ?>">
        <input type="hidden" id="rowcount" name="rowcount" value="0">
        <input type="hidden" id="purchase_status" name="purchase_status" value="<?= $purchase_status; ?>">
        <input type="hidden" id="other_charges_amt" name="other_charges_amt" value="0">
        <input type="hidden" id="subtotal" name="tot_subtotal_amt" value="0">
        <input type="hidden" id="roundoff" name="tot_round_off_amt" value="0">
        <input type="hidden" id="grandtotal" name="tot_total_amt" value="0">
        <input type="hidden" id="tot_discount" name="tot_discount_to_all_amt" value="0">
        <div id="hiddenRows"></div>

        <div class="card">
          <div class="form-group">
            <label>Supplier <span class="req">*</span></label>
            <select class="mp-select" name="supplier_id" id="supplier_id" required>
              <option value="">Select supplier</option>
              <?php foreach($suppliers as $s): ?>
                <option value="<?= $s->id; ?>" <?= ($s->id == $supplier_id) ? 'selected' : ''; ?>><?= $s->supplier_name; ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label>Purchase Date <span class="req">*</span></label>
              <input type="date" name="pur_date" value="<?= $pur_date; ?>" required>
            </div>
            <div class="form-group">
              <label>Reference</label>
              <input type="text" name="reference_no" value="<?= $reference_no; ?>" placeholder="Reference no.">
            </div>
          </div>

          <div class="form-group">
            <label>Status</label>
            <?php
              $status_values = ['Draft' => 'Draft', 'Ordered' => 'Ordered', 'Partially Received' => 'Partially', 'Received' => 'Received'];
              $current_status = $purchase_status ?? 'Draft';
            ?>
            <div class="option-chips" id="status_chips">
              <?php foreach($status_values as $val => $label): ?>
                <button type="button" data-value="<?= $val; ?>" class="<?= ($val == $current_status) ? 'active' : ''; ?>"><?= $label; ?></button>
              <?php endforeach; ?>
            </div>
          </div>

          <?php if(!empty($warehouses)): ?>
            <div class="form-group">
              <label>Branch</label>
              <select class="mp-select" name="warehouse_id" id="warehouse_id">
                <option value="">Select branch</option>
                <?php foreach($warehouses as $w): ?>
                  <option value="<?= $w->id; ?>" <?= ($w->id == $warehouse_id) ? 'selected' : ''; ?>><?= $w->warehouse_name; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          <?php else: ?>
            <input type="hidden" name="warehouse_id" value="<?= $warehouse_id; ?>">
          <?php endif; ?>
        </div>

        <div class="section-title">Add Item</div>
        <div class="card">
          <div class="form-group">
            <label>Item <span class="req">*</span></label>
            <?php
              $tax_map = [];
              foreach($taxes as $t){ $tax_map[$t->id] = $t->tax ?? 0; }
            ?>
            <select class="mp-select" id="itemSelect">
              <option value="">Select item</option>
              <?php foreach($items as $i): ?>
                <option value="<?= $i->id; ?>" data-name="<?= htmlspecialchars($i->item_name, ENT_QUOTES, 'UTF-8'); ?>" data-price="<?= (float) $i->purchase_price; ?>" data-tax="<?= $tax_map[$i->tax_id] ?? 0; ?>" data-taxtype="Exclusive" data-taxid="<?= $i->tax_id; ?>"><?= $i->item_name; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Qty</label>
              <input type="number" step="0.01" id="add_qty" value="1" min="0.01">
            </div>
            <div class="form-group">
              <label>Price / Unit</label>
              <input type="number" step="0.01" id="add_price" value="" min="0">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Tax %</label>
              <input type="number" step="0.01" id="add_tax" value="0" readonly>
            </div>
            <div class="form-group">
              <label>Discount %</label>
              <input type="number" step="0.01" id="add_discount" value="0" min="0">
            </div>
          </div>
          <button type="button" class="btn btn-secondary" id="addItemBtn"><i class="fa fa-plus"></i> Add to Purchase</button>
        </div>

        <div class="section-title">Cart</div>
        <div id="cartList" class="card" style="padding:0 14px;">
          <div class="empty-state">No items added yet.</div>
        </div>

        <div class="section-title">Charges & Discounts</div>
        <div class="card">
          <div class="form-row">
            <div class="form-group">
              <label>Other Charges</label>
              <input type="number" step="0.01" id="other_charges_input" name="other_charges_input" value="<?= $other_charges_input; ?>" min="0">
            </div>
            <div class="form-group">
              <label>Charges Tax</label>
              <select class="mp-select" name="other_charges_tax_id" id="other_charges_tax_id">
                <option value="">None</option>
                <?php foreach($taxes as $t): ?>
                  <option value="<?= $t->id; ?>" data-tax="<?= $t->tax; ?>" <?= ($t->id == $other_charges_tax_id) ? 'selected' : ''; ?>><?= $t->tax_name; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="form-row">
            <div class="form-group">
              <label>Discount on All</label>
              <input type="number" step="0.01" id="discount_to_all_input" name="discount_to_all_input" value="<?= $discount_to_all_input; ?>" min="0">
            </div>
            <div class="form-group">
              <label>Discount Type</label>
              <select class="mp-select" name="discount_to_all_type" id="discount_to_all_type">
                <option value="in_percentage" <?= ($discount_to_all_type == 'in_percentage') ? 'selected' : ''; ?>>Percentage</option>
                <option value="in_fixed" <?= ($discount_to_all_type == 'in_fixed') ? 'selected' : ''; ?>>Fixed</option>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label>Note</label>
            <textarea name="purchase_note" id="purchase_note" rows="2" placeholder="Optional note..."><?= $purchase_note; ?></textarea>
          </div>
        </div>

        <div class="card" style="background:#EFF6FF; border-color:#DBEAFE;">
          <div class="form-row" style="align-items:center; margin:0;">
            <div class="form-group" style="margin:0;">
              <div class="label" style="font-size:13px; color:var(--mp-muted);">Grand Total</div>
              <div class="value" id="grandTotalDisplay" style="font-size:24px; font-weight:700; color:var(--mp-primary);">0.00</div>
            </div>
          </div>
        </div>

        <div class="section-title">Payment (Optional)</div>
        <div class="card">
          <div class="form-row">
            <div class="form-group">
              <label>Amount Paid</label>
              <input type="number" step="0.01" id="amount" name="amount" value="0" min="0">
            </div>
            <div class="form-group">
              <label>Payment Mode</label>
              <select class="mp-select" name="payment_type" id="payment_type">
                <option value="">None</option>
                <?php foreach($payment_modes as $pm): ?>
                  <option value="<?= $pm->code; ?>" <?= ($payment_type == $pm->code) ? 'selected' : ''; ?>><?= $pm->name; ?></option>
                <?php endforeach; ?>
              </select>
            </div>
          </div>
          <div class="form-group">
            <label>Deposit Account</label>
            <select class="mp-select" name="account_id" id="account_id">
              <option value="">None</option>
              <?php foreach($accounts as $a): ?>
                <option value="<?= $a->id; ?>"><?= $a->account_name; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Payment Note</label>
            <textarea name="payment_note" id="payment_note" rows="2" placeholder="Optional..."></textarea>
          </div>
        </div>

        <button type="button" id="saveBtn" class="btn"><?= ($command == 'update') ? 'Update Purchase' : 'Save Purchase'; ?></button>
      </form>
    </section>
  </div>

  <?php $this->load->view('mobile/bottom_nav', ['active' => 'purchase']); ?>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    var base_url = '<?= base_url(); ?>';

    // Custom select setup
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

    function round2(n){ return Math.round((n + Number.EPSILON) * 100) / 100; }

    var taxMap = {};
    <?php foreach($taxes as $t): ?>
      taxMap[<?= $t->id; ?>] = <?= (float) $t->tax; ?>;
    <?php endforeach; ?>

    var cart = <?= json_encode($cart_items, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;

    function setAddLabel(sel, text){
      var wrap = sel.closest('.mp-select-wrap');
      if(wrap) wrap.querySelector('.mp-select-trigger span').textContent = text;
    }

    document.getElementById('itemSelect').addEventListener('change', function(){
      var opt = this.options[this.selectedIndex];
      if(!opt || !opt.value){
        document.getElementById('add_price').value = '';
        document.getElementById('add_tax').value = '0';
        return;
      }
      var price = parseFloat(opt.getAttribute('data-price')) || 0;
      var tax = parseFloat(opt.getAttribute('data-tax')) || 0;
      document.getElementById('add_price').value = price > 0 ? price : '';
      document.getElementById('add_tax').value = tax;
    });

    function addItem(){
      var sel = document.getElementById('itemSelect');
      var opt = sel.options[sel.selectedIndex];
      if(!opt || !opt.value){ mpAlert('Please select an item', 'danger'); return; }
      var qty = parseFloat(document.getElementById('add_qty').value) || 0;
      var price = parseFloat(document.getElementById('add_price').value) || 0;
      if(qty <= 0){ mpAlert('Quantity must be greater than 0', 'danger'); return; }
      if(price <= 0){ mpAlert('Price must be greater than 0', 'danger'); return; }

      cart.push({
        item_id: opt.value,
        name: opt.getAttribute('data-name') || 'Item',
        qty: qty,
        price: price,
        tax_value: parseFloat(opt.getAttribute('data-tax')) || 0,
        tax_id: opt.getAttribute('data-taxid') || '',
        tax_type: opt.getAttribute('data-taxtype') || 'Exclusive',
        discount: parseFloat(document.getElementById('add_discount').value) || 0
      });

      // reset add panel
      sel.selectedIndex = 0;
      setAddLabel(sel, 'Select item');
      document.getElementById('add_qty').value = '1';
      document.getElementById('add_price').value = '';
      document.getElementById('add_tax').value = '0';
      document.getElementById('add_discount').value = '0';

      renderCart();
      compute();
    }

    function removeItem(idx){
      cart.splice(idx, 1);
      renderCart();
      compute();
    }

    function updateItem(idx, field, value){
      var v = parseFloat(value) || 0;
      cart[idx][field] = v;
      compute();
    }

    function renderCart(){
      var list = document.getElementById('cartList');
      if(cart.length === 0){
        list.innerHTML = '<div class="empty-state">No items added yet.</div>';
        return;
      }
      var html = '';
      cart.forEach(function(it, idx){
        html += '<div class="cart-item" data-index="' + idx + '">' +
          '<div class="cart-head">' +
            '<div class="cart-name">' + escapeHtml(it.name) + '</div>' +
            '<button type="button" class="cart-remove" data-index="' + idx + '">Remove</button>' +
          '</div>' +
          '<div class="cart-row">' +
            '<div class="form-group"><label>Qty</label><input type="number" step="0.01" class="cart-qty" data-index="' + idx + '" value="' + it.qty + '" min="0.01"></div>' +
            '<div class="form-group"><label>Price</label><input type="number" step="0.01" class="cart-price" data-index="' + idx + '" value="' + it.price + '"></div>' +
            '<div class="form-group"><label>Disc %</label><input type="number" step="0.01" class="cart-discount" data-index="' + idx + '" value="' + it.discount + '" min="0"></div>' +
          '</div>' +
          '<div class="cart-meta">' + (it.tax_type) + ' tax ' + it.tax_value + '% · Line total <span class="cart-line" data-index="' + idx + '">0.00</span></div>' +
        '</div>';
      });
      list.innerHTML = html;

      list.querySelectorAll('.cart-remove').forEach(function(btn){
        btn.addEventListener('click', function(){ removeItem(parseInt(this.getAttribute('data-index'))); });
      });
      list.querySelectorAll('.cart-qty').forEach(function(el){
        el.addEventListener('input', function(){ updateItem(parseInt(this.getAttribute('data-index')), 'qty', this.value); });
      });
      list.querySelectorAll('.cart-price').forEach(function(el){
        el.addEventListener('input', function(){ updateItem(parseInt(this.getAttribute('data-index')), 'price', this.value); });
      });
      list.querySelectorAll('.cart-discount').forEach(function(el){
        el.addEventListener('input', function(){ updateItem(parseInt(this.getAttribute('data-index')), 'discount', this.value); });
      });
    }

    function compute(){
      var hidden = document.getElementById('hiddenRows');
      hidden.innerHTML = '';
      var itemSubtotal = 0;

      cart.forEach(function(it, idx){
        var i = idx + 1;
        var qty = round2(it.qty);
        var price = round2(it.price);
        var line = round2(qty * price);
        var tax = 0;
        if(it.tax_type === 'Exclusive' && it.tax_value > 0){
          tax = round2(line * it.tax_value / 100);
        } else if(it.tax_type === 'Inclusive' && it.tax_value > 0){
          tax = round2(line - (line / (1 + it.tax_value / 100)));
        }
        var discountBase = it.tax_type === 'Exclusive' ? (line + tax) : line;
        var discount = round2(discountBase * it.discount / 100);
        var total = round2(discountBase - discount);
        var unitCost = qty > 0 ? round2(total / qty) : 0;
        itemSubtotal += total;

        // update line display
        var lineEl = document.querySelector('.cart-line[data-index="' + idx + '"]');
        if(lineEl) lineEl.textContent = mpFormatNumber(total);

        // hidden fields for this item
        var fields = [
          ['tr_item_id_' + i, it.item_id],
          ['td_data_' + i + '_3', qty],
          ['td_data_' + i + '_4', price],
          ['tr_tax_id_' + i, it.tax_id],
          ['td_data_' + i + '_5', tax],
          ['tr_tax_type_' + i, it.tax_type],
          ['td_data_' + i + '_10', unitCost],
          ['td_data_' + i + '_9', total],
          ['item_discount_type_' + i, 'Percentage'],
          ['item_discount_input_' + i, it.discount],
          ['td_data_' + i + '_8', discount],
          ['description_' + i, it.name],
          ['received_qty_' + i, getStatus() === 'Received' ? qty : '']
        ];
        fields.forEach(function(f){
          var inp = document.createElement('input');
          inp.type = 'hidden';
          inp.name = f[0];
          inp.value = f[1];
          hidden.appendChild(inp);
        });
      });

      document.getElementById('rowcount').value = cart.length;

      // other charges
      var otherInput = parseFloat(document.getElementById('other_charges_input').value) || 0;
      var chargesTaxId = document.getElementById('other_charges_tax_id').value;
      var chargesTaxPct = taxMap[chargesTaxId] || 0;
      var otherCharges = round2(otherInput + (otherInput * chargesTaxPct / 100));
      document.getElementById('other_charges_amt').value = otherCharges;

      var subtotal = round2(itemSubtotal + otherCharges);
      document.getElementById('subtotal').value = subtotal;

      var discAllInput = parseFloat(document.getElementById('discount_to_all_input').value) || 0;
      var discAllType = document.getElementById('discount_to_all_type').value;
      var discAll = 0;
      if(discAllType === 'in_percentage' && discAllInput > 0){
        discAll = round2(subtotal * discAllInput / 100);
      } else if(discAllType === 'in_fixed'){
        discAll = round2(discAllInput);
      }
      document.getElementById('tot_discount').value = discAll;

      var grand = round2(subtotal - discAll);
      document.getElementById('grandtotal').value = grand;
      document.getElementById('grandTotalDisplay').textContent = mpFormatNumber(grand);
    }

    function getStatus(){
      return document.getElementById('purchase_status').value;
    }

    function escapeHtml(s){
      var div = document.createElement('div');
      div.textContent = s;
      return div.innerHTML;
    }

    document.getElementById('addItemBtn').addEventListener('click', addItem);

    document.getElementById('other_charges_input').addEventListener('input', compute);
    document.getElementById('other_charges_tax_id').addEventListener('change', compute);
    document.getElementById('discount_to_all_input').addEventListener('input', compute);
    document.getElementById('discount_to_all_type').addEventListener('change', compute);

    document.querySelectorAll('#status_chips button').forEach(function(btn){
      btn.addEventListener('click', function(){
        this.parentNode.querySelectorAll('button').forEach(function(b){ b.classList.remove('active'); });
        this.classList.add('active');
        document.getElementById('purchase_status').value = this.getAttribute('data-value');
        compute();
      });
    });

    document.getElementById('saveBtn').addEventListener('click', async function(){
      var form = document.getElementById('purchase-form');
      var supplier = document.getElementById('supplier_id').value;
      var paid = parseFloat(document.getElementById('amount').value) || 0;
      var grand = parseFloat(document.getElementById('grandtotal').value) || 0;

      if(!supplier){ mpAlert('Please select a supplier', 'danger'); return; }
      if(cart.length === 0){ mpAlert('Please add at least one item', 'danger'); return; }
      if(paid > 0 && !document.getElementById('payment_type').value){ mpAlert('Please select a payment type', 'danger'); return; }
      if(paid > grand){ mpAlert('Payment amount cannot exceed total', 'danger'); return; }

      compute();
      var btn = this;
      btn.disabled = true;
      try {
        var res = await fetch(base_url + 'purchase/purchase_save_and_update', {method: 'POST', body: new FormData(form)});
        var txt = await res.text();
        if(txt.trim().startsWith('success')){
          mpAlert('Purchase saved', 'success');
          setTimeout(function(){ window.location.href = base_url + 'mobile/purchase'; }, 600);
        } else {
          mpAlert(txt.replace(/<[^>]*>/g, '').trim() || 'Save failed', 'danger');
          btn.disabled = false;
        }
      } catch(err){
        mpAlert('Network error. Please try again.', 'danger');
        btn.disabled = false;
      }
    });

    renderCart();
    compute();
  </script>
</body>
</html>
