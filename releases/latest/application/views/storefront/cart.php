<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <title>Cart | <?= htmlspecialchars($store->store_name ?? 'Store'); ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root { --primary:#3B82F6; --primary-dark:#2563EB; --success:#10B981; --warning:#F59E0B; --danger:#EF4444; --dark:#0F172A; --gray:#64748B; --light-gray:#F1F5F9; --border:#E2E8F0; --white:#fff; --radius:12px; --radius-sm:8px; }
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Inter',sans-serif; background:#F8FAFC; color:var(--dark); -webkit-font-smoothing:antialiased; }
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
  let hasServiceAppointment = false;
  let hasServiceNote = false;

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
    document.getElementById('summary-total').textContent = formatMoney(subtotal);

    // Set default payment
    selectedPayment = document.querySelector('input[name="paymethod"]:checked')?.value || 'pay_on_delivery';
  }

  function selectPayment(el, method){
    document.querySelectorAll('.sf-pay-opt').forEach(o => o.classList.remove('active'));
    el.classList.add('active');
    el.querySelector('input').checked = true;
    selectedPayment = method;
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
      msg += '\nTotal: ' + formatMoney(total);
      msg += '\n\nName: ' + name;
      msg += '\nPhone: ' + phone;
      if(email) msg += '\nEmail: ' + email;
      if(address) msg += '\nAddress: ' + address;
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
    const data = new URLSearchParams();
    data.append('store_id', STORE_ID);
    data.append('customer_name', name);
    data.append('customer_phone', phone);
    data.append('customer_email', email);
    data.append('customer_address', address);
    data.append('payment_method', paymentMethod);
    data.append('service_date', serviceDate);
    data.append('service_time', serviceTime);
    data.append('service_note', serviceNote);
    data.append('cart', JSON.stringify(cartPayload));
    if(CSRF_NAME && CSRF_HASH) data.append(CSRF_NAME, CSRF_HASH);

    fetch('<?= base_url('storefront/place_order'); ?>', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: data.toString()
    })
    .then(r => {
      if(!r.ok) return r.text().then(text => { throw new Error('Server error ' + r.status + (text?': '+text.substring(0,200):'')); });
      return r.json();
    })
    .then(res => {
      if(res.csrf_hash) CSRF_HASH = res.csrf_hash;
      if(res.status){
        if(res.payment_required && res.public_key){
          payWithPaystack(res.public_key, res.email, res.amount_kobo, res.reference, res.order_id);
        } else {
          showToast('Order placed! Order #' + res.order_code);
          cart = [];
          localStorage.removeItem('sf_cart_' + STORE_ID);
          setTimeout(() => { window.location.href = '<?= base_url('store/' . ($settings->store_slug ?? '')); ?>'; }, 2000);
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

  function payWithPaystack(key, email, amount, reference, orderId){
    const handler = PaystackPop.setup({
      key: key,
      email: email,
      amount: amount,
      currency: 'NGN',
      ref: reference,
      callback: function(response){
        showToast('Payment successful!');
        cart = [];
        localStorage.removeItem('sf_cart_' + STORE_ID);
        setTimeout(() => { window.location.href = '<?= base_url('store/' . ($settings->store_slug ?? '')); ?>'; }, 1500);
      },
      onClose: function(){
        showToast('Payment cancelled');
        const btn = document.getElementById('checkout-btn');
        if(btn){ btn.disabled = false; btn.textContent = 'Place Order'; }
      }
    });
    handler.openIframe();
  }

  renderCart();
</script>

</body>
</html>
