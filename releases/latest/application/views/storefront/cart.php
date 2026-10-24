<?php
$mp_cart_profile = function_exists('mp_get_store_profile') ? mp_get_store_profile((int)($settings->store_id ?? 0)) : [];
$mp_cart_pharmacy = in_array(($mp_cart_profile['industry_type'] ?? ''), ['pharmacy', 'medical_store'], true);
$mp_cart_outlets = !empty($outlets) && is_array($outlets) ? array_values($outlets) : [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <title>Cart | <?= htmlspecialchars($store->store_name ?? 'Store'); ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <?php if($mp_cart_pharmacy): ?><link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet"><?php endif; ?>
  <style>
    :root { --primary:<?= $mp_cart_pharmacy ? '#0F766E' : '#3B82F6'; ?>; --primary-dark:<?= $mp_cart_pharmacy ? '#0B5B55' : '#2563EB'; ?>; --success:<?= $mp_cart_pharmacy ? '#0F766E' : '#10B981'; ?>; --warning:#F59E0B; --danger:<?= $mp_cart_pharmacy ? '#C94F4F' : '#EF4444'; ?>; --dark:<?= $mp_cart_pharmacy ? '#16323A' : '#0F172A'; ?>; --gray:<?= $mp_cart_pharmacy ? '#6B7F86' : '#64748B'; ?>; --light-gray:<?= $mp_cart_pharmacy ? '#F2F8F7' : '#F1F5F9'; ?>; --border:<?= $mp_cart_pharmacy ? '#D7E7E5' : '#E2E8F0'; ?>; --white:#fff; --radius:12px; --radius-sm:8px; }
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:<?= $mp_cart_pharmacy ? "'Manrope'" : "'Inter'"; ?>,sans-serif; background:<?= $mp_cart_pharmacy ? '#F2F8F7' : '#F8FAFC'; ?>; color:var(--dark); -webkit-font-smoothing:antialiased; }
    a { text-decoration:none; color:inherit; }
    img { max-width:100%; display:block; }

    .sf-header { background:var(--white); border-bottom:1px solid var(--border); position:sticky; top:0; z-index:100; }
    .sf-header-inner { max-width:600px; margin:0 auto; padding:12px 16px; display:flex; align-items:center; gap:12px; }
    .sf-back { font-size:22px; color:var(--dark); }
    .sf-header-title { font-size:16px; font-weight:700; flex:1; }

    .sf-section { max-width:600px; margin:0 auto; padding:16px; }
    .sf-cart-item { background:var(--white); border-radius:var(--radius-sm); border:1px solid var(--border); padding:12px; display:flex; gap:12px; margin-bottom:10px; }
    .sf-cart-img { width:64px; height:64px; border-radius:8px; object-fit:cover; background:var(--light-gray); flex-shrink:0; display:flex; align-items:center; justify-content:center; color:#94A3B8; font-size:10px; }
    .sf-cart-info { flex:1; min-width:0; }
    .sf-cart-name { font-size:14px; font-weight:600; margin-bottom:4px; }
    .sf-cart-price { font-size:14px; font-weight:700; color:var(--primary); }
    .sf-cart-qty { display:flex; align-items:center; gap:10px; margin-top:8px; }
    .sf-cart-qty button { width:32px; height:32px; border-radius:50%; border:1px solid var(--border); background:var(--white); font-size:16px; cursor:pointer; }
    .sf-cart-remove { font-size:12px; color:var(--danger); cursor:pointer; margin-top:6px; display:inline-block; }
    .sf-cart-note { font-size:12px; color:var(--gray); margin-top:4px; background:#F1F5F9; padding:6px 8px; border-radius:4px; }

    .sf-summary { background:var(--white); border-radius:var(--radius-sm); border:1px solid var(--border); padding:16px; margin-top:4px; }
    .sf-row { display:flex; justify-content:space-between; font-size:14px; margin-bottom:10px; }
    .sf-row.total { font-size:20px; font-weight:700; border-top:1px solid var(--border); padding-top:12px; margin-top:4px; }
    .sf-btn { width:100%; padding:14px; border-radius:var(--radius-sm); background:var(--primary); color:#fff; font-weight:700; border:none; cursor:pointer; font-size:16px; margin-top:12px; }
    .sf-btn:disabled { background:#CBD5E1; cursor:not-allowed; }
    .sf-btn-whatsapp { background:#25D366; }
    .sf-btn-cod { background:var(--warning); }

    .sf-label { font-size:13px; font-weight:600; color:var(--gray); margin-bottom:4px; display:block; }
    .sf-input { width:100%; padding:12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:15px; margin-bottom:12px; outline:none; }
    .sf-input:focus { border-color:var(--primary); }
    .sf-textarea { min-height:80px; resize:vertical; }
    .sf-pay-options { display:flex; flex-direction:column; gap:8px; margin-bottom:16px; }
    .sf-pay-opt { display:flex; align-items:center; gap:12px; padding:14px; border:2px solid var(--border); border-radius:var(--radius-sm); cursor:pointer; background:var(--white); }
    .sf-pay-opt.active { border-color:var(--primary); background:#EFF6FF; }
    .sf-pay-opt input { width:18px; height:18px; accent-color:var(--primary); }
    .sf-pay-label { font-size:14px; font-weight:600; }
    .sf-pay-desc { font-size:12px; color:var(--gray); }
    .sf-outlet-options { display:flex; flex-direction:column; gap:8px; margin:6px 0 14px; }
    .sf-outlet-option { display:flex; align-items:flex-start; gap:10px; padding:12px; border:2px solid var(--border); border-radius:var(--radius-sm); cursor:pointer; background:var(--white); }
    .sf-outlet-option.active { border-color:var(--primary); background:#ECF8F6; }
    .sf-outlet-option input { margin-top:3px; accent-color:var(--primary); }
    .sf-outlet-option strong,.sf-outlet-option small { display:block; }
    .sf-outlet-option strong { font-size:13px; }
    .sf-outlet-option small { color:var(--gray); font-size:11px; margin-top:2px; }
    .sf-city-options { display:flex; flex-direction:column; gap:8px; margin:6px 0 14px; }
    .sf-city-option { width:100%; text-align:left; padding:11px 12px; border:2px solid var(--border); border-radius:var(--radius-sm); background:var(--white); color:var(--dark); cursor:pointer; font:inherit; }
    .sf-city-option.active { border-color:var(--primary); background:#ECF8F6; }
    .sf-city-option strong,.sf-city-option small { display:block; }
    .sf-city-option strong { font-size:13px; }
    .sf-city-option small { color:var(--gray); font-size:11px; margin-top:2px; }
    .sf-rx-card { background:#ECF8F6; border:1px solid #B8DDD8; border-radius:var(--radius-sm); padding:12px; margin:8px 0 14px; }
    .sf-rx-card .sf-label { color:#0B5B55; margin-bottom:6px; }
    .sf-rx-hint { color:var(--gray); font-size:11px; margin-top:5px; }

    .sf-empty { text-align:center; padding:60px 16px; color:var(--gray); }
    .sf-empty-icon { font-size:48px; margin-bottom:12px; }
    .sf-empty-btn { display:inline-block; margin-top:16px; padding:12px 24px; background:var(--primary); color:#fff; border-radius:var(--radius-sm); font-weight:600; }

    .sf-toast { position:fixed; top:16px; left:50%; transform:translateX(-50%) translateY(-80px); background:var(--dark); color:#fff; padding:12px 20px; border-radius:var(--radius-sm); font-size:14px; font-weight:500; z-index:300; opacity:0; transition:all .3s ease; }
    .sf-toast.show { transform:translateX(-50%) translateY(0); opacity:1; }

    /* Paystack inline */
    #paystack-embed { display:none; }
  </style>
</head>
<body>

<div class="sf-header">
  <div class="sf-header-inner">
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>" class="sf-back">&#8592;</a>
    <div class="sf-header-title">Shopping Cart</div>
  </div>
</div>

<div class="sf-section" id="cart-container">
  <!-- Cart items injected here -->
</div>

<div class="sf-toast" id="toast"></div>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
  const STORE_SLUG = '<?= $settings->store_slug ?? ''; ?>';
  const STORE_ID = <?= $settings->store_id ?? 0; ?>;
  const CURRENCY = '<?= $CURRENCY ?? '&#8358;'; ?>';
  const PAYSTACK_ENABLED = <?= $paystack_enabled ? 'true' : 'false'; ?>;
  const PAYSTACK_KEY = '<?= $paystack_public_key ?? ''; ?>';
  let CSRF_NAME = '<?= $csrf_name ?? ''; ?>';
  let CSRF_HASH = '<?= $csrf_hash ?? ''; ?>';
  let cart = JSON.parse(localStorage.getItem('sf_cart_' + STORE_ID) || '[]');
  let selectedPayment = 'pay_on_delivery';
  let selectedShippingMethod = '';
  let selectedCityIdx = -1;
  let selectedWarehouseId = <?= count($mp_cart_outlets) === 1 ? (int)$mp_cart_outlets[0]->id : 0; ?>;
  let hasServiceAppointment = false;
  let hasServiceNote = false;
  const IS_PHARMACY = <?= $mp_cart_pharmacy ? 'true' : 'false'; ?>;
  const OUTLETS = <?= json_encode(array_map(function($outlet){ return ['id' => (int)$outlet->id, 'name' => (string)$outlet->warehouse_name, 'address' => (string)($outlet->address ?? ''), 'city' => (string)($outlet->city ?? '')]; }, $mp_cart_outlets), JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
  const SHIPPING_NOTICE = <?= json_encode($settings->shipping_notice ?? ''); ?>;
  const SHIPPING_METHODS = <?= json_encode(array_values(array_filter(json_decode($settings->shipping_methods_json ?? '[]', true) ?? [], function($m){ return !empty($m['enabled']); }))); ?>;
  const CITY_SHIPPING = <?= json_encode(!empty($settings->city_shipping_enabled) ? array_values(array_filter(json_decode($settings->city_shipping_json ?? '[]', true) ?? [], function($z){ return is_array($z) && trim($z['city'] ?? '') !== ''; })) : []); ?>;

  function formatMoney(amount){
    return CURRENCY + amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  }

  function showToast(msg){
    const t = document.getElementById('toast');
    t.textContent = msg; t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2500);
  }

  function saveCart(){
    localStorage.setItem('sf_cart_' + STORE_ID, JSON.stringify(cart));
    renderCart();
  }

  function renderCart(){
    const container = document.getElementById('cart-container');
    if(cart.length === 0){
      container.innerHTML = '<div class="sf-empty"><div class="sf-empty-icon">&#128722;</div><div>Your cart is empty</div><a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>" class="sf-empty-btn">Start Shopping</a></div>';
      return;
    }

    let html = '<div id="cart-items"></div>';
    html += '<div class="sf-summary">';
    html += '<div class="sf-row"><span>Subtotal</span><span id="summary-subtotal"></span></div>';
    html += '<div class="sf-row" id="summary-shipping-row" style="display:none;"><span>Shipping</span><span id="summary-shipping"></span></div>';
    html += '<div class="sf-row total"><span>Total</span><span id="summary-total"></span></div>';
    html += '</div>';

    html += '<div style="margin-top:16px;">';
    html += '<label class="sf-label">Full Name *</label>';
    html += '<input type="text" class="sf-input" id="cust-name" placeholder="John Doe">';
    html += '<label class="sf-label">Phone Number *</label>';
    html += '<input type="tel" class="sf-input" id="cust-phone" placeholder="08012345678">';
    html += '<label class="sf-label">Email (optional)</label>';
    html += '<input type="email" class="sf-input" id="cust-email" placeholder="john@example.com">';
    html += '<label class="sf-label">Delivery / Service Address</label>';
    html += '<textarea class="sf-input sf-textarea" id="cust-address" placeholder="Enter your address..."></textarea>';

    if(IS_PHARMACY && OUTLETS.length > 0){
      html += '<label class="sf-label" style="margin-top:8px;">Preferred pharmacy location</label>';
      html += '<div class="sf-outlet-options">';
      OUTLETS.forEach(function(outlet){
        const active = selectedWarehouseId === outlet.id ? ' active' : '';
        const checked = selectedWarehouseId === outlet.id ? ' checked' : '';
        const detail = [outlet.address, outlet.city].filter(Boolean).join(' · ');
        html += '<label class="sf-outlet-option' + active + '" onclick="selectOutlet(this,' + outlet.id + ')">';
        html += '<input type="radio" name="warehouse_id" value="' + outlet.id + '"' + checked + '>';
        html += '<span><strong>' + String(outlet.name).replace(/</g,'&lt;') + '</strong>';
        if(detail) html += '<small>' + String(detail).replace(/</g,'&lt;') + '</small>';
        html += '</span></label>';
      });
      html += '</div>';
    }

    if(IS_PHARMACY){
      html += '<div class="sf-rx-card"><label class="sf-label">Prescription (optional)</label><input type="file" id="prescription-file" accept="image/*,.pdf"><div class="sf-rx-hint">Upload a clear photo or PDF. The pharmacy team will review it before fulfilment.</div></div>';
    }

    // Shipping notice
    if(SHIPPING_NOTICE){
      html += '<div class="sf-shipping-notice" style="background:#FEF3C7;border:1px solid #FCD34D;border-radius:8px;padding:10px 12px;margin:8px 0;font-size:13px;color:#92400E;">';
      html += '<i class="fa fa-info-circle" style="margin-right:6px;"></i>' + SHIPPING_NOTICE.replace(/</g,'&lt;') + '</div>';
    }

    // City-based delivery fee selector (replaces flat methods when enabled)
    if(CITY_SHIPPING.length > 0){
      html += '<label class="sf-label" style="margin-top:8px;">Delivery City *</label>';
      html += '<div class="sf-city-options">';
      CITY_SHIPPING.forEach((z, idx) => {
        const active = selectedCityIdx === idx ? ' active' : '';
        const fee = parseFloat(z.fee) > 0 ? formatMoney(z.fee) : 'Free';
        const city = String(z.city || 'Delivery area');
        const state = String(z.state || '').trim();
        html += '<button type="button" class="sf-city-option' + active + '" onclick="selectCityOption(this,' + idx + ')"><strong>' + city.replace(/</g,'&lt;') + '</strong><small>' + (state ? state + ' · ' : '') + fee + '</small></button>';
      });
      html += '</div>';
    }
    // Shipping method selector
    else if(SHIPPING_METHODS.length > 0){
      html += '<label class="sf-label" style="margin-top:8px;">Shipping Method</label>';
      html += '<div class="sf-pay-options">';
      SHIPPING_METHODS.forEach((m, idx) => {
        const feeLabel = m.fee > 0 ? formatMoney(m.fee) : 'Free';
        const active = idx === 0 ? ' active' : '';
        const checked = idx === 0 ? ' checked' : '';
        html += '<div class="sf-pay-opt' + active + '" onclick="selectShipping(this,\'' + m.name.replace(/'/g,"\\'") + '\')">';
        html += '<input type="radio" name="shipmethod" value="' + m.name.replace(/"/g,'&quot;') + '"' + checked + '>';
        html += '<div><div class="sf-pay-label">' + m.name.replace(/</g,'&lt;') + ' &mdash; ' + feeLabel + '</div>';
        if(m.description) html += '<div class="sf-pay-desc">' + m.description.replace(/</g,'&lt;') + '</div>';
        html += '</div></div>';
      });
      html += '</div>';
    }

    // Service fields (dynamic)
    hasServiceAppointment = false;
    hasServiceNote = false;
    cart.forEach(i => {
      if(i.type === 'service'){
        if(i.requires_appointment) hasServiceAppointment = true;
        if(i.requires_note) hasServiceNote = true;
      }
    });

    if(hasServiceAppointment){
      html += '<label class="sf-label">Preferred Service Date</label>';
      html += '<input type="date" class="sf-input" id="service-date">';
      html += '<label class="sf-label">Preferred Time</label>';
      html += '<input type="time" class="sf-input" id="service-time">';
    }
    if(hasServiceNote){
      html += '<label class="sf-label">Service Request Details</label>';
      html += '<textarea class="sf-input sf-textarea" id="service-note" placeholder="Describe what you need..."></textarea>';
    }

    html += '<label class="sf-label" style="margin-top:8px;">Payment Method</label>';
    html += '<div class="sf-pay-options">';
    <?php if($settings->allow_paystack && $paystack_enabled): ?>
    html += '<div class="sf-pay-opt active" onclick="selectPayment(this,\'paystack\')">';
    html += '<input type="radio" name="paymethod" value="paystack" checked>';
    html += '<div><div class="sf-pay-label">Pay Online (Paystack)</div><div class="sf-pay-desc">Pay securely with card, bank or USSD</div></div>';
    html += '</div>';
    <?php endif; ?>
    <?php if($settings->allow_whatsapp && $settings->whatsapp_number): ?>
    html += '<div class="sf-pay-opt<?= (!$settings->allow_paystack || !$paystack_enabled) ? ' active' : ''; ?>" onclick="selectPayment(this,\'whatsapp\')">';
    html += '<input type="radio" name="paymethod" value="whatsapp"<?= (!$settings->allow_paystack || !$paystack_enabled) ? ' checked' : ''; ?>>';
    html += '<div><div class="sf-pay-label">Order via WhatsApp</div><div class="sf-pay-desc">Send order to store on WhatsApp</div></div>';
    html += '</div>';
    <?php endif; ?>
    <?php if($settings->allow_pay_on_delivery): ?>
    html += '<div class="sf-pay-opt" onclick="selectPayment(this,\'pay_on_delivery\')">';
    html += '<input type="radio" name="paymethod" value="pay_on_delivery">';
    html += '<div><div class="sf-pay-label">Pay on Delivery</div><div class="sf-pay-desc">Pay when your order arrives</div></div>';
    html += '</div>';
    <?php endif; ?>
    html += '</div>';

    html += '<button class="sf-btn" id="checkout-btn" onclick="placeOrder()">Place Order</button>';
    html += '</div>';

    container.innerHTML = html;

    // Render items
    let subtotal = 0;
    let itemsHtml = '';
    cart.forEach((item, idx) => {
      const itemTotal = item.price * item.qty;
      subtotal += itemTotal;
      itemsHtml += '<div class="sf-cart-item">';
      itemsHtml += '<div class="sf-cart-img">' + (item.image ? '<img src="<?= base_url(); ?>' + item.image + '" style="width:100%;height:100%;object-fit:cover;border-radius:8px;">' : 'No Image') + '</div>';
      itemsHtml += '<div class="sf-cart-info">';
      itemsHtml += '<div class="sf-cart-name">' + item.name + '</div>';
      itemsHtml += '<div class="sf-cart-price">' + formatMoney(item.price) + ' x ' + item.qty + '</div>';
      itemsHtml += '<div class="sf-cart-qty">';
      itemsHtml += '<button onclick="updateQty(' + idx + ', -1)">-</button>';
      itemsHtml += '<span>' + item.qty + '</span>';
      itemsHtml += '<button onclick="updateQty(' + idx + ', 1)">+</button>';
      itemsHtml += '</div>';
      if(item.service_note) itemsHtml += '<div class="sf-cart-note">' + item.service_note + '</div>';
      itemsHtml += '<span class="sf-cart-remove" onclick="removeItem(' + idx + ')">Remove</span>';
      itemsHtml += '</div></div>';
    });
    document.getElementById('cart-items').innerHTML = itemsHtml;
    document.getElementById('summary-subtotal').textContent = formatMoney(subtotal);

    // Set default shipping method (first one)
    selectedShippingMethod = document.querySelector('input[name="shipmethod"]:checked')?.value || ((CITY_SHIPPING.length === 0 && SHIPPING_METHODS.length > 0) ? SHIPPING_METHODS[0].name : '');
    selectedCityIdx = -1;
    updateShippingSummary(subtotal);

    // Set default payment
    selectedPayment = document.querySelector('input[name="paymethod"]:checked')?.value || 'pay_on_delivery';
  }

  function getShippingFee(){
    if(CITY_SHIPPING.length > 0){
      const z = CITY_SHIPPING[selectedCityIdx];
      return z ? (parseFloat(z.fee) || 0) : 0;
    }
    if(!selectedShippingMethod) return 0;
    const sm = SHIPPING_METHODS.find(m => m.name === selectedShippingMethod);
    return sm ? (parseFloat(sm.fee) || 0) : 0;
  }

  function selectCityOption(el, idx){
    document.querySelectorAll('.sf-city-option').forEach(function(option){ option.classList.remove('active'); });
    if(el) el.classList.add('active');
    selectedCityIdx = parseInt(idx, 10);
    selectedShippingMethod = '';
    if(selectedCityIdx >= 0){
      const z = CITY_SHIPPING[selectedCityIdx];
      selectedShippingMethod = 'Delivery - ' + z.city + ((z.state || '').trim() ? ', ' + z.state.trim() : '');
    }
    const subtotal = cart.reduce((s, i) => s + (i.price * i.qty), 0);
    updateShippingSummary(subtotal);
  }

  function updateShippingSummary(subtotal){
    const fee = getShippingFee();
    const row = document.getElementById('summary-shipping-row');
    if(row){
      if(fee > 0 || selectedShippingMethod || selectedCityIdx >= 0){
        row.style.display = 'flex';
        document.getElementById('summary-shipping').textContent = fee > 0 ? formatMoney(fee) : 'Free';
      } else {
        row.style.display = 'none';
      }
    }
    document.getElementById('summary-total').textContent = formatMoney(subtotal + fee);
  }

  function selectShipping(el, method){
    document.querySelectorAll('input[name="shipmethod"]').forEach(o => o.closest('.sf-pay-opt').classList.remove('active'));
    el.classList.add('active');
    el.querySelector('input').checked = true;
    selectedShippingMethod = method;
    const subtotal = cart.reduce((s, i) => s + (i.price * i.qty), 0);
    updateShippingSummary(subtotal);
  }

  function selectPayment(el, method){
    document.querySelectorAll('input[name="paymethod"]').forEach(o => o.closest('.sf-pay-opt').classList.remove('active'));
    el.classList.add('active');
    el.querySelector('input').checked = true;
    selectedPayment = method;
  }

  function selectOutlet(el, id){
    document.querySelectorAll('.sf-outlet-option').forEach(function(option){ option.classList.remove('active'); });
    el.classList.add('active');
    const input = el.querySelector('input');
    if(input) input.checked = true;
    selectedWarehouseId = parseInt(id, 10) || 0;
  }

  function updateQty(idx, delta){
    cart[idx].qty = Math.max(1, cart[idx].qty + delta);
    saveCart();
  }

  function removeItem(idx){
    cart.splice(idx, 1);
    saveCart();
  }

  function placeOrder(){
    const name = document.getElementById('cust-name').value.trim();
    const phone = document.getElementById('cust-phone').value.trim();
    const email = document.getElementById('cust-email').value.trim();
    const address = document.getElementById('cust-address').value.trim();
    const serviceDate = document.getElementById('service-date')?.value || '';
    const serviceTime = document.getElementById('service-time')?.value || '';
    const serviceNote = document.getElementById('service-note')?.value || '';

    if(!name || !phone){ showToast('Please enter name and phone'); return; }
    if(cart.length === 0){ showToast('Cart is empty'); return; }
    if(CITY_SHIPPING.length > 0 && selectedCityIdx < 0){ showToast('Please select your delivery city'); return; }
    if(IS_PHARMACY && OUTLETS.length > 1 && !selectedWarehouseId){ showToast('Please select a pharmacy location'); return; }

    const btn = document.getElementById('checkout-btn');
    btn.disabled = true;
    btn.textContent = 'Processing...';

    // Build cart payload
    const cartPayload = cart.map(i => ({
      id: i.id, type: i.type, name: i.name, price: i.price,
      qty: i.qty, image: i.image,
      note: i.service_note || '', requires_appointment: i.requires_appointment || false, requires_note: i.requires_note || false
    }));

    if(selectedPayment === 'whatsapp'){
      // Build WhatsApp message and open
      let msg = 'Hello, I would like to place an order from <?= htmlspecialchars(addslashes($store->store_name ?? 'your store')); ?>.';
      msg += '\n\nItems:\n';
      let total = 0;
      cart.forEach(i => { msg += i.qty + ' x ' + i.name + ' — ' + formatMoney(i.price * i.qty) + '\n'; total += i.price * i.qty; });
      const shipFee = getShippingFee();
      msg += '\nSubtotal: ' + formatMoney(total);
      if(selectedShippingMethod){
        msg += '\nShipping: ' + selectedShippingMethod + (shipFee > 0 ? ' (' + formatMoney(shipFee) + ')' : ' (Free)');
      } else if(CITY_SHIPPING.length > 0 && selectedCityIdx >= 0){
        msg += '\nDelivery City: ' + CITY_SHIPPING[selectedCityIdx].city + (shipFee > 0 ? ' (' + formatMoney(shipFee) + ')' : ' (Free)');
      }
      msg += '\nTotal: ' + formatMoney(total + shipFee);
      msg += '\n\nName: ' + name;
      msg += '\nPhone: ' + phone;
      if(email) msg += '\nEmail: ' + email;
      if(address) msg += '\nAddress: ' + address;
      if(selectedShippingMethod) msg += '\nShipping Method: ' + selectedShippingMethod;
      if(serviceDate) msg += '\nService Date: ' + serviceDate;
      if(serviceTime) msg += '\nService Time: ' + serviceTime;
      if(serviceNote) msg += '\nService Note: ' + serviceNote;
      msg += '\n\nThank you.';
      const wnum = '<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? ''); ?>';
      if(wnum){
        window.open('https://wa.me/' + wnum + '?text=' + encodeURIComponent(msg), '_blank');
      }
      // Still save order in background
      submitOrder('whatsapp', name, phone, email, address, serviceDate, serviceTime, serviceNote, cartPayload, btn);
      return;
    }

    submitOrder(selectedPayment, name, phone, email, address, serviceDate, serviceTime, serviceNote, cartPayload, btn);
  }

  function submitOrder(paymentMethod, name, phone, email, address, serviceDate, serviceTime, serviceNote, cartPayload, btn){
    const data = new FormData();
    data.append('store_id', STORE_ID);
    data.append('customer_name', name);
    data.append('customer_phone', phone);
    data.append('customer_email', email);
    data.append('customer_address', address);
    data.append('payment_method', paymentMethod);
    if(selectedWarehouseId) data.append('warehouse_id', selectedWarehouseId);
    data.append('shipping_method', selectedShippingMethod || '');
    if(CITY_SHIPPING.length > 0 && selectedCityIdx >= 0){
      const z = CITY_SHIPPING[selectedCityIdx];
      data.append('shipping_city', (z.city || '') + '|' + (z.state || '').trim());
    }
    data.append('service_date', serviceDate);
    data.append('service_time', serviceTime);
    data.append('service_note', serviceNote);
    data.append('cart', JSON.stringify(cartPayload));
    const prescriptionInput = document.getElementById('prescription-file');
    if(IS_PHARMACY && prescriptionInput && prescriptionInput.files && prescriptionInput.files[0]) data.append('prescription_file', prescriptionInput.files[0]);
    if(CSRF_NAME && CSRF_HASH) data.append(CSRF_NAME, CSRF_HASH);

    fetch('<?= base_url('storefront/place_order'); ?>', {
      method: 'POST',
      body: data
    })
    .then(r => {
      if(!r.ok) return r.text().then(text => { throw new Error('Server error ' + r.status + (text?': '+text.substring(0,200):'')); });
      return r.json();
    })
    .then(res => {
      if(res.csrf_hash) CSRF_HASH = res.csrf_hash;
      if(res.status){
        if(res.payment_required && res.public_key){
		  payWithPaystack(res.public_key, res.email, res.amount_kobo, res.reference, res.order_id, res.paystack_split || {});
        } else {
          cart = [];
          localStorage.removeItem('sf_cart_' + STORE_ID);
          if(res.redirect_url){
            window.location.href = res.redirect_url;
          } else {
            showToast('Order placed! Order #' + res.order_code);
            setTimeout(() => { window.location.href = '<?= base_url('store/' . ($settings->store_slug ?? '')); ?>'; }, 2000);
          }
        }
      } else {
        showToast(res.message || 'Failed to place order');
        btn.disabled = false;
        btn.textContent = 'Place Order';
      }
    })
    .catch(err => {
      showToast(err.message || 'Network error. Please try again.');
      btn.disabled = false;
      btn.textContent = 'Place Order';
    });
  }

  function payWithPaystack(key, email, amount, reference, orderId, split){
    const config = {
      key: key,
      email: email,
      amount: amount,
      currency: 'NGN',
      ref: reference,
      callback: function(response){
        cart = [];
        localStorage.removeItem('sf_cart_' + STORE_ID);
        window.location.href = '<?= base_url('store/' . ($settings->store_slug ?? '') . '/order_received/'); ?>' + reference;
      },
      onClose: function(){
        showToast('Payment cancelled');
        const btn = document.getElementById('checkout-btn');
        if(btn){ btn.disabled = false; btn.textContent = 'Place Order'; }
      }
    };
    if(split && split.subaccount) config.subaccount = split.subaccount;
    if(split && split.transaction_charge) config.transaction_charge = split.transaction_charge;
    if(split && split.bearer) config.bearer = split.bearer;
    const handler = PaystackPop.setup(config);
    handler.openIframe();
  }

  renderCart();
</script>

<?php $this->load->view('themes/shared/legacy_site_links'); ?>
</body>
</html>
