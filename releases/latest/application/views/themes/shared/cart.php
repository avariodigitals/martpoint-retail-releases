<div class="mp-breadcrumb">
  <a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>">Home</a> &rsaquo; Cart
</div>

<div class="mp-section mp-cart-section" id="cart-container"></div>

<!-- Order Success Overlay -->
<div class="mp-order-success-overlay" id="order-success-overlay">
  <div class="mp-order-success-card">
    <div class="mp-success-icon-wrap">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
    </div>
    <div class="mp-success-title" id="success-title">Order Received!</div>
    <div class="mp-success-msg" id="success-msg">Your order has been received and is being processed. We'll contact you shortly.</div>
    <div class="mp-success-order-code" id="success-code">Order #---</div>
    <div id="success-wa-note" style="display:none;" class="mp-success-wa-note">
      <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg>
      <span>Your order has also been sent to the store on WhatsApp.</span>
    </div>
    <div class="mp-success-actions">
      <a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>" class="mp-success-btn mp-success-btn-secondary">Continue Shopping</a>
      <a href="#" id="success-track-btn" class="mp-success-btn mp-success-btn-primary">View Order</a>
    </div>
  </div>
</div>

<style>
  .mp-sticky-cart { display:none !important; }
  .mp-cart-section { padding-top:24px !important; }

  /* Two-column checkout layout */
  .mp-cart-layout { display:flex; flex-direction:column; gap:24px; }
  @media(min-width:900px){
    .mp-cart-layout { flex-direction:row; align-items:flex-start; }
    .mp-cart-left { flex:1 1 0; max-width:560px; }
    .mp-cart-right { flex:0 0 380px; position:sticky; top:80px; }
  }

  /* Left column — checkout form */
  .mp-checkout-card { background:var(--mp-white); border-radius:var(--mp-radius); border:1px solid var(--mp-border); padding:24px; margin-bottom:16px; }
  .mp-checkout-card-title { font-size:16px; font-weight:700; margin-bottom:16px; display:flex; align-items:center; gap:8px; color:var(--mp-dark); }
  .mp-checkout-card-title .mp-step-num { width:24px; height:24px; border-radius:50%; background:var(--mp-primary); color:#fff; font-size:13px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
  .mp-checkout-fields { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
  .mp-checkout-fields .mp-field-full { grid-column:1 / -1; }
  .mp-cart-input { width:100%; padding:12px 14px; border:1px solid var(--mp-border); border-radius:var(--mp-radius-sm); font-size:14px; outline:none; transition:border-color .2s, box-shadow .2s; }
  .mp-cart-input:focus { border-color:var(--mp-primary); box-shadow:0 0 0 3px rgba(59,130,246,0.1); }
  .mp-cart-label { font-size:13px; font-weight:600; color:var(--mp-gray); margin-bottom:6px; display:block; }

  /* Shipping notice */
  .mp-ship-notice { background:#FEF3C7; border:1px solid #FCD34D; border-radius:var(--mp-radius-sm); padding:12px 14px; margin-bottom:16px; font-size:13px; color:#92400E; line-height:1.5; display:flex; gap:8px; align-items:flex-start; }
  .mp-ship-notice i { margin-top:2px; flex-shrink:0; }

  /* Payment/Shipping option cards */
  .mp-payment-options { display:flex; flex-direction:column; gap:10px; }
  .mp-payment-option { display:flex; align-items:center; gap:12px; padding:14px; border:1.5px solid var(--mp-border); border-radius:var(--mp-radius-sm); cursor:pointer; background:var(--mp-white); transition:all .2s; }
  .mp-payment-option:hover { border-color:var(--mp-primary); }
  .mp-payment-option.active { border-color:var(--mp-primary); background:#EFF6FF; }
  .mp-payment-option input { width:18px; height:18px; flex-shrink:0; }
  .mp-payment-option > div { flex:1; min-width:0; }
  .mp-pay-label { font-size:14px; font-weight:600; color:var(--mp-dark); }
  .mp-pay-desc { font-size:12px; color:var(--mp-gray); margin-top:2px; }
  .mp-pay-fee { font-size:14px; font-weight:700; color:var(--mp-primary); white-space:nowrap; }

  /* Place order button */
  .mp-cart-checkout { width:100%; padding:16px; border-radius:var(--mp-radius-sm); background:var(--mp-button); color:#fff; font-weight:700; border:none; cursor:pointer; font-size:16px; margin-top:20px; transition:background .2s, transform .1s; box-shadow:0 4px 12px rgba(0,0,0,0.12); }
  .mp-cart-checkout:hover { background:var(--mp-button-dark); }
  .mp-cart-checkout:active { transform:scale(0.98); }
  .mp-cart-checkout:disabled { background:#CBD5E1; cursor:not-allowed; box-shadow:none; }
  /* On mobile, make the button sticky at the bottom of the viewport */
  @media(max-width:899px){
    .mp-cart-checkout { position:sticky; bottom:16px; z-index:50; }
  }

  /* Right column — order summary */
  .mp-order-summary { background:var(--mp-white); border-radius:var(--mp-radius); border:1px solid var(--mp-border); padding:24px; }
  .mp-order-summary-title { font-size:16px; font-weight:700; margin-bottom:16px; color:var(--mp-dark); }
  .mp-order-items { max-height:320px; overflow-y:auto; margin-bottom:16px; }
  .mp-order-item { display:flex; gap:12px; padding:10px 0; border-bottom:1px solid var(--mp-border); }
  .mp-order-item:last-child { border-bottom:none; }
  .mp-order-item-img { width:56px; height:56px; border-radius:8px; object-fit:cover; background:var(--mp-light-gray); flex-shrink:0; overflow:hidden; }
  .mp-order-item-info { flex:1; min-width:0; }
  .mp-order-item-name { font-size:13px; font-weight:600; color:var(--mp-dark); line-height:1.3; margin-bottom:4px; }
  .mp-order-item-meta { font-size:12px; color:var(--mp-gray); display:flex; align-items:center; gap:8px; }
  .mp-order-item-qty { display:flex; align-items:center; gap:6px; }
  .mp-order-item-qty button { width:24px; height:24px; border-radius:50%; border:1px solid var(--mp-border); background:var(--mp-white); font-size:13px; cursor:pointer; display:flex; align-items:center; justify-content:center; line-height:1; }
  .mp-order-item-price { font-size:13px; font-weight:700; color:var(--mp-primary); white-space:nowrap; }
  .mp-order-item-remove { color:var(--mp-danger); font-size:11px; cursor:pointer; margin-left:4px; }

  .mp-summary-totals { border-top:1px solid var(--mp-border); padding-top:16px; }
  .mp-cart-row { display:flex; justify-content:space-between; font-size:14px; margin-bottom:8px; color:var(--mp-gray); }
  .mp-cart-row span:last-child { color:var(--mp-dark); font-weight:600; }
  .mp-cart-total { display:flex; justify-content:space-between; align-items:center; font-size:20px; font-weight:800; border-top:1px solid var(--mp-border); padding-top:12px; margin-top:12px; color:var(--mp-dark); }
  .mp-cart-total span:last-child { color:var(--mp-primary); }

  /* Empty cart */
  .mp-empty { text-align:center; padding:60px 16px; color:var(--mp-gray); }

  /* Order success overlay */
  .mp-order-success-overlay { position:fixed; inset:0; background:rgba(15,23,42,0.6); backdrop-filter:blur(4px); z-index:2000; display:none; align-items:center; justify-content:center; padding:16px; }
  .mp-order-success-overlay.show { display:flex; }
  .mp-order-success-card { background:#fff; border-radius:16px; max-width:440px; width:100%; padding:40px 28px; text-align:center; box-shadow:0 24px 64px rgba(0,0,0,0.2); animation:mpSuccessIn .4s ease; }
  @keyframes mpSuccessIn { from{opacity:0;transform:scale(0.92) translateY(12px);} to{opacity:1;transform:scale(1) translateY(0);} }
  .mp-success-icon-wrap { width:72px; height:72px; border-radius:50%; background:#D1FAE5; display:flex; align-items:center; justify-content:center; margin:0 auto 20px; animation:mpSuccessPop .5s ease .15s both; }
  @keyframes mpSuccessPop { 0%{transform:scale(0);} 60%{transform:scale(1.15);} 100%{transform:scale(1);} }
  .mp-success-icon-wrap svg { width:36px; height:36px; color:#059669; }
  .mp-success-title { font-size:22px; font-weight:800; color:#0F172A; margin-bottom:8px; }
  .mp-success-msg { font-size:15px; color:#64748B; line-height:1.6; margin-bottom:20px; }
  .mp-success-order-code { display:inline-block; background:#EFF6FF; color:#2563EB; padding:10px 20px; border-radius:10px; font-size:15px; font-weight:700; font-family:monospace; margin-bottom:20px; letter-spacing:0.02em; }
  .mp-success-actions { display:flex; gap:10px; }
  .mp-success-btn { flex:1; padding:14px; border-radius:10px; font-weight:700; border:none; cursor:pointer; font-size:14px; text-decoration:none; text-align:center; display:block; }
  .mp-success-btn-primary { background:var(--mp-primary); color:#fff; }
  .mp-success-btn-primary:hover { background:var(--mp-primary-dark); }
  .mp-success-btn-secondary { background:#fff; color:#0F172A; border:1px solid #E2E8F0; }
  .mp-success-btn-secondary:hover { background:#F8FAFC; }
  .mp-success-wa-note { margin-top:16px; padding:12px; background:#F0FDF4; border:1px solid #BBF7D0; border-radius:10px; font-size:13px; color:#166534; display:flex; align-items:center; gap:8px; justify-content:center; }
  .mp-success-wa-note svg { width:18px; height:18px; flex-shrink:0; }
</style>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
let CSRF_NAME = '<?= $csrf_name ?? ''; ?>';
let CSRF_HASH = '<?= $csrf_hash ?? ''; ?>';
let cartData = JSON.parse(localStorage.getItem('sf_cart_' + STORE_ID) || '[]');
let selectedPayment = 'pay_on_delivery';
let selectedShippingMethod = '';
const SHIPPING_NOTICE = <?= json_encode($settings->shipping_notice ?? ''); ?>;
const SHIPPING_METHODS = <?= json_encode(array_values(array_filter(json_decode($settings->shipping_methods_json ?? '[]', true) ?? [], function($m){ return !empty($m['enabled']); }))); ?>;

function renderCart(){
  const c = document.getElementById('cart-container');
  if(cartData.length === 0){
    c.innerHTML = '<div class="mp-empty"><div style="margin-bottom:12px;color:#CBD5E1;"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg></div><div>Your cart is empty</div><a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>" style="display:inline-block;margin-top:16px;padding:12px 24px;background:var(--mp-primary);color:#fff;border-radius:var(--mp-radius-sm);font-weight:600;">Start Shopping</a></div>';
    return;
  }

  // Build two-column layout
  let html = '<div class="mp-cart-layout">';

  // === LEFT COLUMN: Checkout form ===
  html += '<div class="mp-cart-left">';

  // Step 1: Contact details
  html += '<div class="mp-checkout-card">';
  html += '<div class="mp-checkout-card-title"><span class="mp-step-num">1</span> Contact Details</div>';
  html += '<div class="mp-checkout-fields">';
  html += '<div><label class="mp-cart-label">Full Name *</label><input type="text" class="mp-cart-input" id="cust-name" placeholder="John Doe"></div>';
  html += '<div><label class="mp-cart-label">Phone Number *</label><input type="tel" class="mp-cart-input" id="cust-phone" placeholder="08012345678"></div>';
  html += '<div class="mp-field-full"><label class="mp-cart-label">Email (optional)</label><input type="email" class="mp-cart-input" id="cust-email" placeholder="john@example.com"></div>';
  html += '<div class="mp-field-full"><label class="mp-cart-label">Delivery / Service Address</label><textarea class="mp-cart-input" style="min-height:72px;resize:vertical;" id="cust-address" placeholder="Enter your address..."></textarea></div>';
  html += '</div>';
  html += '</div>';

  // Step 2: Shipping
  if(SHIPPING_METHODS.length > 0 || SHIPPING_NOTICE){
    html += '<div class="mp-checkout-card">';
    html += '<div class="mp-checkout-card-title"><span class="mp-step-num">2</span> Shipping Method</div>';
    if(SHIPPING_NOTICE){
      html += '<div class="mp-ship-notice"><i class="fa fa-info-circle"></i><span>' + SHIPPING_NOTICE.replace(/</g,'&lt;') + '</span></div>';
    }
    if(SHIPPING_METHODS.length > 0){
      html += '<div class="mp-payment-options">';
      SHIPPING_METHODS.forEach((m,idx)=>{
        const feeLabel = m.fee > 0 ? formatMoney(m.fee) : 'Free';
        const active = idx === 0 ? ' active' : '';
        const checked = idx === 0 ? ' checked' : '';
        html += '<div class="mp-payment-option'+active+'" onclick="selShip(this,\''+m.name.replace(/'/g,"\\'")+'\')">';
        html += '<input type="radio" name="shipmethod" value="'+m.name.replace(/"/g,'&quot;')+'"'+checked+'>';
        html += '<div><div class="mp-pay-label">'+m.name.replace(/</g,'&lt;')+'</div>';
        if(m.description) html += '<div class="mp-pay-desc">'+m.description.replace(/</g,'&lt;')+'</div>';
        html += '</div><div class="mp-pay-fee">'+feeLabel+'</div></div>';
      });
      html += '</div>';
    }
    html += '</div>';
  }

  // Service fields (dynamic)
  let hasAppt=false, hasNote=false;
  cartData.forEach(i=>{ if(i.type==='service'){ if(i.requires_appointment)hasAppt=true; if(i.requires_note)hasNote=true; }});
  if(hasAppt || hasNote){
    html += '<div class="mp-checkout-card">';
    html += '<div class="mp-checkout-card-title"><span class="mp-step-num">' + (SHIPPING_METHODS.length > 0 || SHIPPING_NOTICE ? '3' : '2') + '</span> Service Details</div>';
    if(hasAppt){
      html += '<div class="mp-checkout-fields">';
      html += '<div><label class="mp-cart-label">Preferred Service Date</label><input type="date" class="mp-cart-input" id="service-date"></div>';
      html += '<div><label class="mp-cart-label">Preferred Time</label><input type="time" class="mp-cart-input" id="service-time"></div>';
      html += '</div>';
    }
    if(hasNote){
      html += '<label class="mp-cart-label" style="margin-top:12px;">Service Request Details</label>';
      html += '<textarea class="mp-cart-input" style="min-height:72px;resize:vertical;" id="service-note" placeholder="Describe what you need..."></textarea>';
    }
    html += '</div>';
  }

  // Step 3/4: Payment
  let payStepNum = 3;
  if(!(SHIPPING_METHODS.length > 0 || SHIPPING_NOTICE) && !hasAppt && !hasNote) payStepNum = 2;
  else if(!(SHIPPING_METHODS.length > 0 || SHIPPING_NOTICE) && (hasAppt || hasNote)) payStepNum = 3;
  else if((SHIPPING_METHODS.length > 0 || SHIPPING_NOTICE) && !(hasAppt || hasNote)) payStepNum = 3;
  else payStepNum = 4;

  html += '<div class="mp-checkout-card">';
  html += '<div class="mp-checkout-card-title"><span class="mp-step-num">'+payStepNum+'</span> Payment Method</div>';
  html += '<div class="mp-payment-options">';
  <?php if($settings->allow_paystack && $paystack_enabled): ?>
  html += '<div class="mp-payment-option active" onclick="selPay(this,\'paystack\')"><input type="radio" name="paymethod" value="paystack" checked><div><div class="mp-pay-label">Pay Online (Paystack)</div><div class="mp-pay-desc">Pay securely with card, bank or USSD</div></div></div>';
  <?php endif; ?>
  <?php if($settings->allow_whatsapp && $settings->whatsapp_number): ?>
  html += '<div class="mp-payment-option<?= (!$settings->allow_paystack||!$paystack_enabled)?' active':''; ?>" onclick="selPay(this,\'whatsapp\')"><input type="radio" name="paymethod" value="whatsapp"<?= (!$settings->allow_paystack||!$paystack_enabled)?' checked':''; ?>><div><div class="mp-pay-label">Order via WhatsApp</div><div class="mp-pay-desc">Send order to store on WhatsApp</div></div></div>';
  <?php endif; ?>
  <?php if($settings->allow_pay_on_delivery): ?>
  html += '<div class="mp-payment-option" onclick="selPay(this,\'pay_on_delivery\')"><input type="radio" name="paymethod" value="pay_on_delivery"><div><div class="mp-pay-label">Pay on Delivery</div><div class="mp-pay-desc">Pay when your order arrives</div></div></div>';
  <?php endif; ?>
  html += '</div>';
  html += '</div>'; // end payment card

  html += '</div>'; // end left column

  // === RIGHT COLUMN: Order summary ===
  html += '<div class="mp-cart-right">';
  html += '<div class="mp-order-summary">';
  html += '<div class="mp-order-summary-title">Order Summary</div>';
  html += '<div class="mp-order-items" id="cart-items"></div>';
  html += '<div class="mp-summary-totals">';
  html += '<div class="mp-cart-row"><span>Subtotal</span><span id="summary-subtotal"></span></div>';
  html += '<div class="mp-cart-row" id="summary-shipping-row" style="display:none;"><span>Shipping</span><span id="summary-shipping"></span></div>';
  html += '<div class="mp-cart-total"><span>Total</span><span id="summary-total"></span></div>';
  html += '</div>';
  html += '<button class="mp-cart-checkout" id="checkout-btn" onclick="placeOrder()">Place Order</button>';
  html += '</div>';
  html += '</div>'; // end right column

  html += '</div>'; // end layout
  c.innerHTML = html;

  // Render cart items in the right column
  let subtotal=0, itemsHtml='';
  cartData.forEach((item,idx)=>{
    let it=item.price*item.qty; subtotal+=it;
    itemsHtml+='<div class="mp-order-item">';
    itemsHtml+='<div class="mp-order-item-img">'+(item.image?'<img src="<?= base_url(); ?>'+item.image+'" alt="'+item.name+'" style="width:100%;height:100%;object-fit:cover;">':'')+'</div>';
    itemsHtml+='<div class="mp-order-item-info">';
    itemsHtml+='<div class="mp-order-item-name">'+item.name+'</div>';
    itemsHtml+='<div class="mp-order-item-meta">';
    itemsHtml+='<div class="mp-order-item-qty"><button onclick="upQty('+idx+',-1)">-</button><span>'+item.qty+'</span><button onclick="upQty('+idx+',1)">+</button></div>';
    itemsHtml+='<span class="mp-order-item-remove" onclick="rmItem('+idx+')">Remove</span>';
    itemsHtml+='</div>';
    itemsHtml+='</div>';
    itemsHtml+='<div class="mp-order-item-price">'+formatMoney(it)+'</div>';
    itemsHtml+='</div>';
  });
  document.getElementById('cart-items').innerHTML=itemsHtml;
  document.getElementById('summary-subtotal').textContent=formatMoney(subtotal);
  selectedShippingMethod=document.querySelector('input[name="shipmethod"]:checked')?.value||(SHIPPING_METHODS.length>0?SHIPPING_METHODS[0].name:'');
  updateShipSummary(subtotal);
  selectedPayment=document.querySelector('input[name="paymethod"]:checked')?.value||'pay_on_delivery';
}

function getShipFee(){
  if(!selectedShippingMethod) return 0;
  const sm=SHIPPING_METHODS.find(m=>m.name===selectedShippingMethod);
  return sm?(parseFloat(sm.fee)||0):0;
}
function updateShipSummary(subtotal){
  const fee=getShipFee();
  const row=document.getElementById('summary-shipping-row');
  if(row){
    if(fee>0||selectedShippingMethod){ row.style.display='flex'; document.getElementById('summary-shipping').textContent=fee>0?formatMoney(fee):'Free'; }
    else { row.style.display='none'; }
  }
  document.getElementById('summary-total').textContent=formatMoney(subtotal+fee);
}
function selShip(el,method){
  document.querySelectorAll('input[name="shipmethod"]').forEach(o=>o.closest('.mp-payment-option').classList.remove('active'));
  el.classList.add('active'); el.querySelector('input').checked=true; selectedShippingMethod=method;
  const subtotal=cartData.reduce((s,i)=>s+(i.price*i.qty),0);
  updateShipSummary(subtotal);
}

function selPay(el,m){
  document.querySelectorAll('input[name="paymethod"]').forEach(o=>o.closest('.mp-payment-option').classList.remove('active'));
  el.classList.add('active'); el.querySelector('input').checked=true; selectedPayment=m;
}
function upQty(idx,delta){ cartData[idx].qty=Math.max(1,cartData[idx].qty+delta); saveCartState(); }
function rmItem(idx){ cartData.splice(idx,1); saveCartState(); }
function saveCartState(){ localStorage.setItem('sf_cart_'+STORE_ID,JSON.stringify(cartData)); renderCart(); updateCartUI(); }

function placeOrder(){
  const name=document.getElementById('cust-name').value.trim();
  const phone=document.getElementById('cust-phone').value.trim();
  const email=document.getElementById('cust-email').value.trim();
  const address=document.getElementById('cust-address').value.trim();
  const sDate=document.getElementById('service-date')?.value||'';
  const sTime=document.getElementById('service-time')?.value||'';
  const sNote=document.getElementById('service-note')?.value||'';
  if(!name||!phone){ showToast('Please enter name and phone'); return; }
  if(cartData.length===0){ showToast('Cart is empty'); return; }
  const btn=document.getElementById('checkout-btn'); btn.disabled=true; btn.textContent='Processing...';
  const payload=cartData.map(i=>({id:i.id,type:i.type,name:i.name,price:i.price,qty:i.qty,image:i.image,note:i.service_note||'',requires_appointment:i.requires_appointment||false,requires_note:i.requires_note||false}));
  if(selectedPayment==='whatsapp'){
    let msg='Hello, I would like to place an order from <?= htmlspecialchars(addslashes($store->store_name ?? 'your store')); ?>';
    msg+='\n\nItems:\n'; let total=0;
    cartData.forEach(i=>{ msg+=i.qty+' x '+i.name+' — '+formatMoney(i.price*i.qty)+'\n'; total+=i.price*i.qty; });
    const shipFee=getShipFee();
    msg+='\nSubtotal: '+formatMoney(total);
    if(selectedShippingMethod){ msg+='\nShipping: '+selectedShippingMethod+(shipFee>0?' ('+formatMoney(shipFee)+')':' (Free)'); }
    msg+='\nTotal: '+formatMoney(total+shipFee); msg+='\n\nName: '+name; msg+='\nPhone: '+phone;
    if(email) msg+='\nEmail: '+email; if(address) msg+='\nAddress: '+address; if(selectedShippingMethod) msg+='\nShipping Method: '+selectedShippingMethod; if(sDate) msg+='\nService Date: '+sDate; if(sTime) msg+='\nService Time: '+sTime; if(sNote) msg+='\nService Note: '+sNote; msg+='\n\nThank you.';
    const wnum='<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? ''); ?>';
    if(wnum) window.open('https://wa.me/'+wnum+'?text='+encodeURIComponent(msg),'_blank');
    submitOrder('whatsapp',name,phone,email,address,sDate,sTime,sNote,payload,btn); return;
  }
  submitOrder(selectedPayment,name,phone,email,address,sDate,sTime,sNote,payload,btn);
}

function submitOrder(pm,name,phone,email,address,sDate,sTime,sNote,payload,btn){
  const data=new URLSearchParams();
  data.append('store_id',STORE_ID); data.append('customer_name',name); data.append('customer_phone',phone);
  data.append('customer_email',email); data.append('customer_address',address); data.append('payment_method',pm);
  data.append('shipping_method',selectedShippingMethod||'');
  data.append('service_date',sDate); data.append('service_time',sTime); data.append('service_note',sNote);
  data.append('cart',JSON.stringify(payload));
  if(CSRF_NAME && CSRF_HASH) data.append(CSRF_NAME, CSRF_HASH);
  fetch('<?= base_url('storefront/place_order'); ?>',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:data.toString()})
  .then(r=>{
    if(!r.ok) return r.text().then(text=>{ throw new Error('Server error ' + r.status + (text?': '+text.substring(0,200):'')); });
    return r.json();
  }).then(res=>{
    if(res.csrf_hash) CSRF_HASH = res.csrf_hash;
    if(res.status){
      if(res.payment_required&&res.public_key){
        payWithPaystack(res.public_key,res.email,res.amount_kobo,res.reference,res.order_id);
      } else {
        showOrderSuccess(res.order_code, res.redirect_url, pm);
        cartData=[]; cart=[]; localStorage.removeItem('sf_cart_'+STORE_ID); updateCartUI();
      }
    } else { showToast(res.message||'Failed to place order'); btn.disabled=false; btn.textContent='Place Order'; }
  }).catch(err=>{ showToast(err.message || 'Network error. Please try again.'); btn.disabled=false; btn.textContent='Place Order'; });
}

function payWithPaystack(key,email,amount,reference,orderId){
  const handler=PaystackPop.setup({
    key:key, email:email, amount:amount, currency:'NGN', ref:reference,
    callback:function(response){ showOrderSuccess(response.reference, '<?= base_url('store/' . ($settings->store_slug ?? '') . '/order_received/'); ?>' + response.reference, 'paystack'); cartData=[]; cart=[]; localStorage.removeItem('sf_cart_'+STORE_ID); updateCartUI(); },
    onClose:function(){ showToast('Payment cancelled'); const btn=document.getElementById('checkout-btn'); if(btn){ btn.disabled=false; btn.textContent='Place Order'; } }
  });
  handler.openIframe();
}

function showOrderSuccess(orderCode, redirectUrl, paymentMethod){
  const overlay = document.getElementById('order-success-overlay');
  const codeEl = document.getElementById('success-code');
  const titleEl = document.getElementById('success-title');
  const msgEl = document.getElementById('success-msg');
  const waNote = document.getElementById('success-wa-note');
  const trackBtn = document.getElementById('success-track-btn');
  if(!overlay) return;
  if(codeEl) codeEl.textContent = 'Order #' + orderCode;
  if(paymentMethod === 'whatsapp'){
    if(titleEl) titleEl.textContent = 'Order Sent!';
    if(msgEl) msgEl.innerHTML = 'Your order has been received and sent to the store on WhatsApp.<br>The store will contact you to confirm your order.';
    if(waNote) waNote.style.display = 'flex';
  } else if(paymentMethod === 'paystack'){
    if(titleEl) titleEl.textContent = 'Payment Successful!';
    if(msgEl) msgEl.innerHTML = 'Your payment has been confirmed and your order is being processed.<br>We\'ll contact you shortly with delivery details.';
    if(waNote) waNote.style.display = 'none';
  } else {
    if(titleEl) titleEl.textContent = 'Order Received!';
    if(msgEl) msgEl.innerHTML = 'Your order has been received and is being processed.<br>We\'ll contact you shortly with delivery details.';
    if(waNote) waNote.style.display = 'none';
  }
  if(trackBtn && redirectUrl) trackBtn.href = redirectUrl;
  overlay.classList.add('show');
  document.body.style.overflow = 'hidden';
}

renderCart();
</script>
