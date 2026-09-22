<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — <?= htmlspecialchars($page_title ?? 'Sales Return'); ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --mp-ink: #1E293B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 12px 100px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 12px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar h1 { font-size: 20px; font-weight: 700; margin: 0;}
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .card { background: #fff; border-radius: 14px; padding: 14px; margin-bottom: 12px; border: 1px solid var(--mp-border); }
    .card-title { font-size: 14px; font-weight: 700; margin-bottom: 10px; }
    .sale-banner { background: #FFF7ED; border: 1px solid #FFEDD5; border-radius: 14px; padding: 12px 14px; margin-bottom: 12px; font-size: 13px; color: #9A3412; }
    .sale-banner b { color: var(--mp-ink); }
    .form-group { margin-bottom: 12px; }
    .form-group:last-child { margin-bottom: 0; }
    .form-group label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; color: var(--mp-ink); }
    .form-control, .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; font-size: 15px; background: #fff; color: var(--mp-text); font-family: inherit; }
    .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 10px; }
    .search-wrap { position: relative; }
    .result-list { border: 1px solid var(--mp-border); border-radius: 12px; background: #fff; margin-top: 6px; overflow: hidden; display: none; }
    .result-list.open { display: block; }
    .result-item { padding: 12px 14px; border-bottom: 1px solid var(--mp-border); cursor: pointer; font-size: 14px; }
    .result-item:last-child { border-bottom: none; }
    .result-item .meta { font-size: 12px; color: var(--mp-muted); margin-top: 2px; }
    .picked { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding: 12px 14px; border: 1px solid var(--mp-border); border-radius: 12px; background: var(--mp-bg); font-size: 15px; font-weight: 600; }
    .picked .clear { border: none; background: transparent; color: var(--mp-danger); font-size: 16px; cursor: pointer; padding: 4px; }
    .cart-item { padding: 12px 0; border-bottom: 1px solid var(--mp-border); }
    .cart-item:last-child { border-bottom: none; }
    .cart-item .top { display: flex; justify-content: space-between; align-items: flex-start; gap: 8px; margin-bottom: 8px; }
    .cart-item .name { font-weight: 600; font-size: 14px; flex: 1; min-width: 0; }
    .cart-item .remove { border: none; background: transparent; color: var(--mp-danger); font-size: 16px; cursor: pointer; padding: 0 4px; }
    .cart-item .controls { display: flex; align-items: center; gap: 10px; }
    .qty-box { display: flex; align-items: center; gap: 4px; background: var(--mp-bg); border-radius: 10px; padding: 4px; }
    .qty-box button { width: 34px; height: 34px; border: none; border-radius: 8px; background: var(--mp-surface); font-size: 18px; font-weight: 600; color: var(--mp-primary); cursor: pointer; }
    .qty-box input { width: 56px; border: none; background: transparent; text-align: center; font-size: 15px; font-weight: 700; font-family: inherit; color: var(--mp-text); }
    .price-input { width: 84px; padding: 8px 10px; border: 1px solid var(--mp-border); border-radius: 10px; font-size: 14px; text-align: right; font-family: inherit; }
    .cart-item .meta { font-size: 12px; color: var(--mp-muted); margin-top: 6px; display: flex; justify-content: space-between; }
    .cart-item .meta .line-total { font-weight: 700; color: var(--mp-ink); }
    .max-hint { color: var(--mp-warning); }
    .total-row { display: flex; justify-content: space-between; padding: 8px 0; font-size: 14px; }
    .total-row .label { color: var(--mp-muted); }
    .total-row.grand { font-size: 18px; font-weight: 700; color: var(--mp-primary); border-top: 1px solid var(--mp-border); padding-top: 12px; margin-top: 4px; }
    .discount-input { width: 110px; padding: 8px 10px; border: 1px solid var(--mp-border); border-radius: 10px; font-size: 14px; text-align: right; font-family: inherit; }
    .btn-primary { width: 100%; padding: 16px; border: none; border-radius: 14px; background: var(--mp-primary); color: #fff; font-size: 16px; font-weight: 700; cursor: pointer; }
    .btn-primary:active { background: var(--mp-primary-dark); }
    .btn-primary:disabled { opacity: 0.7; cursor: not-allowed; }
    .hint { font-size: 12px; color: var(--mp-muted); margin-top: 4px; }
    .empty-state { text-align: center; padding: 24px; color: var(--mp-muted); font-size: 13px; }
    .mp-select { display: none; }
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
    @media (min-width: 600px) { #app { max-width: 100%; margin: 0; } .screen { padding: 16px 16px 100px; } }
    @media (min-width: 1024px) { .screen { padding: 24px 48px 120px; } }
  </style>
</head>
<body>
  <?php
    $is_edit = ($mode === 'edit');
    $has_sale = !empty($sale);
    $ret = $return_header;
    $back_url = $is_edit ? 'mobile/sales_return_invoice/'.$return_id : ($has_sale ? 'mobile/sales_invoice/'.$sale->id : 'mobile/sales_returns');
    $customer_locked = $has_sale;
    $date_val = $is_edit ? date('Y-m-d', strtotime($ret->return_date)) : date('Y-m-d');
    $status_val = $is_edit ? $ret->return_status : 'Return';
    $ref_val = $is_edit ? $ret->reference_no : '';
    $note_val = $is_edit ? $ret->return_note : '';
    $disc_val = $is_edit ? (float)$ret->tot_discount_to_all_amt : 0;

    $items_js = [];
    foreach($prefill_items as $p){
      $items_js[] = [
        'id' => (int)$p->id,
        'name' => $p->name ?? 'Item',
        'price' => (float)$p->price,
        'qty' => (float)$p->qty,
        'max' => isset($p->sold_qty) ? (float)$p->sold_qty : null,
        'tax_id' => (int)($p->tax_id ?? 0),
        'tax_value' => (float)($p->tax_value ?? 0),
        'tax_type' => $p->tax_type ?? 'Exclusive',
        'tax_name' => $p->tax_name ?? '',
        'discount_input' => (float)($p->discount_input ?? 0),
        'discount_type' => $p->discount_type ?? 'Percentage',
        'discount_amt' => (float)($p->discount_amt ?? 0),
        'description' => $p->description ?? '',
      ];
    }
  ?>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url($back_url); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1><?= htmlspecialchars($page_title ?? 'New Return'); ?></h1>
        </div>
      </div>

      <?php if($has_sale): ?>
        <div class="sale-banner">
          <i class="fa fa-link"></i> Return against <b>#<?= htmlspecialchars($sale->sales_code); ?></b> &middot; <?= show_date($sale->sales_date); ?>
        </div>
      <?php endif; ?>

      <div class="card">
        <div class="card-title">Customer</div>
        <?php if($customer_locked): ?>
          <div class="picked"><span><i class="fa fa-user"></i> <?= htmlspecialchars($customer_name); ?></span></div>
          <input type="hidden" id="customer_id" value="<?= (int)$customer_id; ?>">
        <?php else: ?>
          <div class="search-wrap" id="customerPicker" <?= !empty($customer_id) ? 'style="display:none;"' : ''; ?>>
            <input type="text" class="form-control" id="customer_search" placeholder="Search customer name or mobile..." autocomplete="off">
            <div class="result-list" id="customer_results"></div>
          </div>
          <div class="picked" id="customerPicked" <?= empty($customer_id) ? 'style="display:none;"' : ''; ?>>
            <span id="customerPickedName"><?= htmlspecialchars($customer_name); ?></span>
            <button type="button" class="clear" id="customerClear"><i class="fa fa-times"></i></button>
          </div>
          <input type="hidden" id="customer_id" value="<?= (int)$customer_id; ?>">
        <?php endif; ?>
      </div>

      <div class="card">
        <div class="form-row">
          <div class="form-group">
            <label for="return_date">Return Date</label>
            <input type="date" id="return_date" value="<?= $date_val; ?>">
          </div>
          <div class="form-group">
            <label for="return_status">Status</label>
            <select class="mp-select" id="return_status">
              <option value="Return" <?= $status_val == 'Return' ? 'selected' : ''; ?>>Return</option>
              <option value="Warranty" <?= $status_val == 'Warranty' ? 'selected' : ''; ?>>Warranty Claim</option>
              <option value="Cancel" <?= $status_val == 'Cancel' ? 'selected' : ''; ?>>Cancel</option>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label for="reference_no">Reference No</label>
          <input type="text" id="reference_no" value="<?= htmlspecialchars($ref_val); ?>" placeholder="Optional">
        </div>
      </div>

      <div class="card">
        <div class="card-title">Items</div>
        <?php if(!$has_sale): ?>
        <div class="search-wrap" style="margin-bottom:10px;">
          <input type="text" class="form-control" id="item_search" placeholder="Type item name, code or SKU" autocomplete="off">
          <div class="result-list" id="item_results"></div>
        </div>
        <?php endif; ?>
        <div id="cart_items"></div>
        <div class="empty-state" id="cart_empty" style="display:none;">No items yet. <?= $has_sale ? '' : 'Search above to add returned items.'; ?></div>
      </div>

      <div class="card">
        <div class="card-title">Summary</div>
        <div class="total-row"><span class="label">Subtotal</span><span id="sum_subtotal">0.00</span></div>
        <div class="total-row"><span class="label">Tax</span><span id="sum_tax">0.00</span></div>
        <div class="total-row">
          <span class="label">Discount</span>
          <input type="number" class="discount-input" id="discount" min="0" step="any" inputmode="decimal" value="<?= htmlspecialchars($disc_val); ?>">
        </div>
        <div class="total-row grand"><span>Return Total</span><span id="sum_grand">0.00</span></div>
      </div>

      <div class="card">
        <div class="card-title">Refund</div>
        <?php if($is_edit && $already_refunded > 0): ?>
          <div class="hint" style="margin-bottom:10px;">Already refunded: <b><?= store_number_format($already_refunded); ?></b></div>
        <?php endif; ?>
        <div class="form-group">
          <label for="refund_amount">Refund Amount</label>
          <input type="number" id="refund_amount" min="0" step="any" inputmode="decimal" placeholder="0.00">
        </div>
        <div class="form-row">
          <div class="form-group">
            <label for="payment_type">Payment Mode</label>
            <select class="mp-select" id="payment_type">
              <?= get_payment_modes_select_list(get_current_store_id(), get_default_payment_mode_code()); ?>
            </select>
          </div>
          <div class="form-group">
            <label for="account_id">Account</label>
            <select class="mp-select" id="account_id">
              <?= $accounts; ?>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label for="payment_note">Payment Note</label>
          <input type="text" id="payment_note" placeholder="Optional">
        </div>
        <div class="hint">Leave refund at 0 to record the return now and refund later.</div>
      </div>

      <div class="card">
        <div class="form-group">
          <label for="return_note">Return Note</label>
          <textarea id="return_note" rows="2" placeholder="Optional note"><?= htmlspecialchars($note_val); ?></textarea>
        </div>
      </div>

      <button type="button" class="btn-primary" id="save_return"><?= $is_edit ? 'Update Return' : 'Save Return'; ?></button>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>

  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>

  <script>
    var base_url = '<?= base_url(); ?>';
    var csrf_name = '<?= $this->security->get_csrf_token_name(); ?>';
    var csrf_hash = '<?= $this->security->get_csrf_hash(); ?>';
    var currency = '<?= $this->session->userdata("currency"); ?>';
    var return_id = '<?= $return_id; ?>';
    var sales_id = '<?= !empty($sale) ? (int)$sale->id : (!empty($ret) ? (int)$ret->sales_id : ''); ?>';
    var isEdit = <?= $is_edit ? 'true' : 'false'; ?>;
    var refundDirty = false;

    var cart = <?= json_encode($items_js, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE); ?>;
    cart.forEach(function(it){
      it.disc_per_unit = (it.qty > 0) ? ((it.discount_amt || 0) / it.qty) : 0;
      it.discount_amt = it.disc_per_unit * it.qty;
    });

    function formatMoney(num){
      return currency + ' ' + Number(num).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
    }
    function toast(msg, type){ if(window.mpAlert){ mpAlert(msg, type === 'error' ? 'danger' : (type || 'info')); } else { alert(msg); } }

    function lineCalc(it){
      var gross = it.qty * it.price;
      var net = gross - (it.disc_per_unit * it.qty);
      if(net < 0) net = 0;
      var tax = (it.tax_type === 'Inclusive') ? net - net / (1 + it.tax_value / 100) : net * it.tax_value / 100;
      var total = (it.tax_type === 'Inclusive') ? net : net + tax;
      return { tax: tax, total: total, pretax: (it.tax_type === 'Inclusive' ? net - tax : net) };
    }

    function getTotals(){
      var subtotal = 0, tax = 0, grand = 0;
      cart.forEach(function(it){
        if(it.qty <= 0) return;
        var c = lineCalc(it);
        subtotal += c.pretax;
        tax += c.tax;
        grand += c.total;
      });
      var disc = parseFloat(document.getElementById('discount').value) || 0;
      grand -= disc;
      if(grand < 0) grand = 0;
      return { subtotal: subtotal, tax: tax, grand: grand };
    }

    function updateTotals(){
      var t = getTotals();
      document.getElementById('sum_subtotal').textContent = formatMoney(t.subtotal);
      document.getElementById('sum_tax').textContent = formatMoney(t.tax);
      document.getElementById('sum_grand').textContent = formatMoney(t.grand);
      if(!isEdit && !refundDirty){
        document.getElementById('refund_amount').value = t.grand ? t.grand.toFixed(2) : '';
      }
    }

    function renderCart(){
      var wrap = document.getElementById('cart_items');
      wrap.innerHTML = '';
      var visible = 0;
      cart.forEach(function(it, idx){
        visible++;
        var row = document.createElement('div');
        row.className = 'cart-item';
        var c = lineCalc(it);
        var maxHint = (it.max !== null && it.max !== undefined) ? 'Sold: ' + it.max : '';
        row.innerHTML =
          '<div class="top"><span class="name">' + escapeHtml(it.name) + '</span>' +
          (it.max === null || it.max === undefined ? '<button type="button" class="remove" data-idx="' + idx + '"><i class="fa fa-times"></i></button>' : '') +
          '</div>' +
          '<div class="controls">' +
            '<div class="qty-box">' +
              '<button type="button" class="qty-minus" data-idx="' + idx + '">−</button>' +
              '<input type="number" class="qty-val" data-idx="' + idx + '" value="' + it.qty + '" min="0" step="any" inputmode="decimal">' +
              '<button type="button" class="qty-plus" data-idx="' + idx + '">+</button>' +
            '</div>' +
            '<input type="number" class="price-input" data-idx="' + idx + '" value="' + it.price + '" min="0" step="any" inputmode="decimal" title="Unit price">' +
          '</div>' +
          '<div class="meta"><span>' + (maxHint ? '<span class="max-hint">' + maxHint + '</span>' : '') + (it.tax_value ? ' &middot; Tax ' + it.tax_value + '%' : '') + '</span>' +
          '<span class="line-total">' + formatMoney(c.total) + '</span></div>';
        wrap.appendChild(row);
      });
      document.getElementById('cart_empty').style.display = visible === 0 ? 'block' : 'none';
      updateTotals();
    }

    document.addEventListener('click', function(e){
      var minus = e.target.closest('.qty-minus');
      var plus = e.target.closest('.qty-plus');
      var rem = e.target.closest('.cart-item .remove');
      if(minus){
        var i = +minus.getAttribute('data-idx');
        cart[i].qty = Math.max(0, (parseFloat(cart[i].qty) || 0) - 1);
        cart[i].discount_amt = cart[i].disc_per_unit * cart[i].qty;
        renderCart();
      } else if(plus){
        var j = +plus.getAttribute('data-idx');
        var next = (parseFloat(cart[j].qty) || 0) + 1;
        if(cart[j].max !== null && cart[j].max !== undefined && next > cart[j].max){
          toast('Only ' + cart[j].max + ' sold — cannot return more.', 'warning');
          return;
        }
        cart[j].qty = next;
        cart[j].discount_amt = cart[j].disc_per_unit * cart[j].qty;
        renderCart();
      } else if(rem){
        cart.splice(+rem.getAttribute('data-idx'), 1);
        renderCart();
      }
    });

    document.addEventListener('input', function(e){
      if(e.target.classList.contains('qty-val')){
        var i = +e.target.getAttribute('data-idx');
        var v = parseFloat(e.target.value) || 0;
        if(cart[i].max !== null && cart[i].max !== undefined && v > cart[i].max){
          v = cart[i].max;
          e.target.value = v;
          toast('Only ' + cart[i].max + ' sold — cannot return more.', 'warning');
        }
        cart[i].qty = Math.max(0, v);
        cart[i].discount_amt = cart[i].disc_per_unit * cart[i].qty;
        updateTotals();
      } else if(e.target.classList.contains('price-input')){
        var k = +e.target.getAttribute('data-idx');
        cart[k].price = Math.max(0, parseFloat(e.target.value) || 0);
        updateTotals();
      } else if(e.target.id === 'discount'){
        updateTotals();
      } else if(e.target.id === 'refund_amount'){
        refundDirty = true;
      }
    });

    /* ---------- Customer picker (direct returns) ---------- */
    var custSearch = document.getElementById('customer_search');
    if(custSearch){
      var custTimer = null;
      custSearch.addEventListener('input', function(){
        clearTimeout(custTimer);
        var q = custSearch.value.trim();
        if(q.length < 1){ document.getElementById('customer_results').classList.remove('open'); return; }
        custTimer = setTimeout(function(){
          fetch(base_url + 'mobile/customer_search?q=' + encodeURIComponent(q))
            .then(function(r){ return r.json(); })
            .catch(function(){ return []; })
            .then(function(rows){
              var box = document.getElementById('customer_results');
              box.innerHTML = '';
              rows.forEach(function(r){
                var d = document.createElement('div');
                d.className = 'result-item';
                d.innerHTML = escapeHtml(r.customer_name) + '<div class="meta">' + escapeHtml(r.mobile || '') + ' ' + escapeHtml(r.customer_code || '') + '</div>';
                d.addEventListener('click', function(){
                  document.getElementById('customer_id').value = r.id;
                  document.getElementById('customerPickedName').textContent = r.customer_name;
                  document.getElementById('customerPicked').style.display = 'flex';
                  document.getElementById('customerPicker').style.display = 'none';
                  box.classList.remove('open');
                });
                box.appendChild(d);
              });
              box.classList.toggle('open', rows.length > 0);
            });
        }, 250);
      });
      document.getElementById('customerClear').addEventListener('click', function(){
        document.getElementById('customer_id').value = '';
        document.getElementById('customerPicked').style.display = 'none';
        document.getElementById('customerPicker').style.display = 'block';
        custSearch.value = '';
        custSearch.focus();
      });
    }

    /* ---------- Item search (direct returns) ---------- */
    var itemSearch = document.getElementById('item_search');
    var itemResultsMap = {};
    if(itemSearch){
      var itemTimer = null;
      itemSearch.addEventListener('input', function(){
        clearTimeout(itemTimer);
        var q = itemSearch.value.trim();
        if(q.length < 2){ document.getElementById('item_results').classList.remove('open'); return; }
        itemTimer = setTimeout(function(){
          fetch(base_url + 'mobile/item_search?q=' + encodeURIComponent(q))
            .then(function(r){ return r.json(); })
            .catch(function(){ return []; })
            .then(function(rows){
              var box = document.getElementById('item_results');
              box.innerHTML = '';
              itemResultsMap = {};
              rows.forEach(function(r){
                itemResultsMap[r.id] = r;
                var d = document.createElement('div');
                d.className = 'result-item';
                d.setAttribute('data-id', r.id);
                d.innerHTML = escapeHtml(r.item_name) + '<div class="meta">' + escapeHtml(r.item_code || r.sku || '') + ' &middot; ' + formatMoney(r.sales_price) + ' &middot; Stock ' + (r.stock || 0) + '</div>';
                d.addEventListener('click', function(){
                  var found = null;
                  cart.forEach(function(it){ if(it.id == r.id && (it.max === null || it.max === undefined)) found = it; });
                  if(found){
                    found.qty += 1;
                    found.discount_amt = found.disc_per_unit * found.qty;
                  } else {
                    cart.push({
                      id: r.id, name: r.item_name, price: parseFloat(r.sales_price) || 0,
                      qty: 1, max: null,
                      tax_id: r.tax_id || 0, tax_value: parseFloat(r.tax_value) || 0,
                      tax_type: r.tax_type || 'Exclusive', tax_name: r.tax_name || '',
                      discount_input: 0, discount_type: 'Percentage', discount_amt: 0, disc_per_unit: 0,
                      description: ''
                    });
                  }
                  itemSearch.value = '';
                  box.classList.remove('open');
                  renderCart();
                });
                box.appendChild(d);
              });
              box.classList.toggle('open', rows.length > 0);
            });
        }, 250);
      });
    }

    /* ---------- mp-select (custom dropdowns) ---------- */
    document.querySelectorAll('select.mp-select').forEach(function(sel){
      var wrap = document.createElement('div'); wrap.className = 'mp-select-wrap';
      sel.parentNode.insertBefore(wrap, sel);
      wrap.appendChild(sel);
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
        if(idx === sel.selectedIndex) d.classList.add('active');
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

    /* ---------- Save ---------- */
    document.getElementById('save_return').addEventListener('click', function(){
      var customer_id = document.getElementById('customer_id').value;
      if(!customer_id){ toast('Please select a customer', 'warning'); return; }
      var items = cart.filter(function(it){ return it.qty > 0; });
      if(items.length === 0){ toast('Add at least one item with quantity', 'warning'); return; }
      var refund = parseFloat(document.getElementById('refund_amount').value) || 0;
      var t = getTotals();
      if(refund > t.grand + 0.01){ toast('Refund cannot exceed the return total', 'error'); return; }
      if(refund > 0 && !document.getElementById('payment_type').value){ toast('Choose a payment mode for the refund', 'warning'); return; }

      var btn = this;
      btn.disabled = true;
      var original = btn.textContent;
      btn.textContent = 'Saving...';

      var payload = {
        return_id: return_id,
        sales_id: sales_id,
        customer_id: customer_id,
        return_date: document.getElementById('return_date').value,
        return_status: document.getElementById('return_status').value,
        reference_no: document.getElementById('reference_no').value,
        return_note: document.getElementById('return_note').value,
        discount: parseFloat(document.getElementById('discount').value) || 0,
        amount: refund,
        payment_type: document.getElementById('payment_type').value,
        account_id: document.getElementById('account_id').value,
        payment_note: document.getElementById('payment_note').value,
        cart: items.map(function(it){
          return {
            id: it.id, qty: it.qty, price: it.price,
            tax_id: it.tax_id, tax_value: it.tax_value, tax_type: it.tax_type,
            discount_input: it.discount_input, discount_type: it.discount_type,
            discount_amt: it.disc_per_unit * it.qty,
            description: it.description || ''
          };
        })
      };
      payload[csrf_name] = csrf_hash;

      var headers = { 'Content-Type': 'application/json' };
      headers[csrf_name] = csrf_hash;

      var saveReq = (typeof mpFetchJson === 'function')
        ? mpFetchJson(base_url + 'mobile/save_return', { method: 'POST', headers: headers, body: JSON.stringify(payload) })
        : fetch(base_url + 'mobile/save_return', { method: 'POST', headers: headers, body: JSON.stringify(payload) }).then(function(r){ return r.json(); });
      saveReq
        .then(function(res){
          if(res.status === 'success'){
            toast('Return saved', 'success');
            window.location.href = res.redirect;
          } else {
            toast(res.message || 'The return could not be saved. Please check the details and try again.', 'error');
            btn.disabled = false;
            btn.textContent = original;
          }
        })
        .catch(function(err){
          var msg = (err && err.message) ? err.message : 'Could not reach the server. Check your connection and try again.';
          toast(msg, 'error');
          btn.disabled = false;
          btn.textContent = original;
        });
    });

    renderCart();
    <?php if($is_edit): ?>
    refundDirty = true;
    document.getElementById('refund_amount').value = '0.00';
    <?php endif; ?>
  </script>
</body>
</html>
