<div class="mp-breadcrumb">
  <a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>">Home</a> &rsaquo; Cart
</div>

<div class="mp-section" id="cart-container"></div>

<style>
  .mp-sticky-cart { display:none !important; }
</style>

<script src="https://js.paystack.co/v1/inline.js"></script>
<script>
let CSRF_NAME = '<?= $csrf_name ?? ''; ?>';
let CSRF_HASH = '<?= $csrf_hash ?? ''; ?>';
let cartData = JSON.parse(localStorage.getItem('sf_cart_' + STORE_ID) || '[]');
let selectedPayment = 'pay_on_delivery';

function renderCart(){
  const c = document.getElementById('cart-container');
  if(cartData.length === 0){
    c.innerHTML = '<div class="mp-empty"><div style="margin-bottom:12px;color:#CBD5E1;"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg></div><div>Your cart is empty</div><a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>" style="display:inline-block;margin-top:16px;padding:12px 24px;background:var(--mp-primary);color:#fff;border-radius:var(--mp-radius-sm);font-weight:600;">Start Shopping</a></div>';
    return;
  }
  let html = '<div id="cart-items"></div><div class="mp-cart-summary">';
  html += '<div class="mp-cart-row"><span>Subtotal</span><span id="summary-subtotal"></span></div>';
  html += '<div class="mp-cart-row mp-cart-total"><span>Total</span><span id="summary-total"></span></div></div>';
  html += '<div style="margin-top:16px;">';
  html += '<label class="mp-cart-label">Full Name *</label><input type="text" class="mp-cart-input" id="cust-name" placeholder="John Doe">';
  html += '<label class="mp-cart-label">Phone Number *</label><input type="tel" class="mp-cart-input" id="cust-phone" placeholder="08012345678">';
  html += '<label class="mp-cart-label">Email (optional)</label><input type="email" class="mp-cart-input" id="cust-email" placeholder="john@example.com">';
  html += '<label class="mp-cart-label">Delivery / Service Address</label><textarea class="mp-cart-input" style="min-height:80px;resize:vertical;" id="cust-address" placeholder="Enter your address..."></textarea>';

  let hasAppt=false, hasNote=false;
  cartData.forEach(i=>{ if(i.type==='service'){ if(i.requires_appointment)hasAppt=true; if(i.requires_note)hasNote=true; }});
  if(hasAppt){ html += '<label class="mp-cart-label">Preferred Service Date</label><input type="date" class="mp-cart-input" id="service-date">'; html += '<label class="mp-cart-label">Preferred Time</label><input type="time" class="mp-cart-input" id="service-time">'; }
  if(hasNote){ html += '<label class="mp-cart-label">Service Request Details</label><textarea class="mp-cart-input" style="min-height:80px;resize:vertical;" id="service-note" placeholder="Describe what you need..."></textarea>'; }

  html += '<label class="mp-cart-label" style="margin-top:8px;">Payment Method</label><div class="mp-payment-options">';
  <?php if($settings->allow_paystack && $paystack_enabled): ?>
  html += '<div class="mp-payment-option active" onclick="selPay(this,\'paystack\')"><input type="radio" name="paymethod" value="paystack" checked><div><div style="font-size:14px;font-weight:600;">Pay Online (Paystack)</div><div style="font-size:12px;color:var(--mp-gray);">Pay securely with card, bank or USSD</div></div></div>';
  <?php endif; ?>
  <?php if($settings->allow_whatsapp && $settings->whatsapp_number): ?>
  html += '<div class="mp-payment-option<?= (!$settings->allow_paystack||!$paystack_enabled)?' active':''; ?>" onclick="selPay(this,\'whatsapp\')"><input type="radio" name="paymethod" value="whatsapp"<?= (!$settings->allow_paystack||!$paystack_enabled)?' checked':''; ?>><div><div style="font-size:14px;font-weight:600;">Order via WhatsApp</div><div style="font-size:12px;color:var(--mp-gray);">Send order to store on WhatsApp</div></div></div>';
  <?php endif; ?>
  <?php if($settings->allow_pay_on_delivery): ?>
  html += '<div class="mp-payment-option" onclick="selPay(this,\'pay_on_delivery\')"><input type="radio" name="paymethod" value="pay_on_delivery"><div><div style="font-size:14px;font-weight:600;">Pay on Delivery</div><div style="font-size:12px;color:var(--mp-gray);">Pay when your order arrives</div></div></div>';
  <?php endif; ?>
  html += '</div><button class="mp-cart-checkout" id="checkout-btn" onclick="placeOrder()">Place Order</button></div>';
  c.innerHTML = html;

  let subtotal=0, itemsHtml='';
  cartData.forEach((item,idx)=>{
    let it=item.price*item.qty; subtotal+=it;
    itemsHtml+='<div class="mp-cart-item"><div class="mp-cart-item-img">'+(item.image?'<img src="<?= base_url(); ?>'+item.image+'" alt="'+item.name+'" style="width:100%;height:100%;object-fit:cover;border-radius:6px;">':'')+'</div>';
    itemsHtml+='<div class="mp-cart-item-info"><div class="mp-cart-item-name">'+item.name+'</div><div class="mp-cart-item-price">'+formatMoney(item.price)+' x '+item.qty+'</div>';
    itemsHtml+='<div class="mp-cart-item-qty"><button onclick="upQty('+idx+',-1)">-</button><span>'+item.qty+'</span><button onclick="upQty('+idx+',1)">+</button></div>';
    if(item.service_note) itemsHtml+='<div style="font-size:12px;color:var(--mp-gray);margin-top:4px;background:var(--mp-light-gray);padding:6px 8px;border-radius:4px;">'+item.service_note+'</div>';
    itemsHtml+='<span class="mp-cart-item-remove" onclick="rmItem('+idx+')">Remove</span></div></div>';
  });
  document.getElementById('cart-items').innerHTML=itemsHtml;
  document.getElementById('summary-subtotal').textContent=formatMoney(subtotal);
  document.getElementById('summary-total').textContent=formatMoney(subtotal);
  selectedPayment=document.querySelector('input[name="paymethod"]:checked')?.value||'pay_on_delivery';
}

function selPay(el,m){
  document.querySelectorAll('.mp-payment-option').forEach(o=>o.classList.remove('active'));
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
    msg+='\nTotal: '+formatMoney(total); msg+='\n\nName: '+name; msg+='\nPhone: '+phone;
    if(email) msg+='\nEmail: '+email; if(address) msg+='\nAddress: '+address; if(sDate) msg+='\nService Date: '+sDate; if(sTime) msg+='\nService Time: '+sTime; if(sNote) msg+='\nService Note: '+sNote; msg+='\n\nThank you.';
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
        showToast('Order placed! Order #'+res.order_code);
        cartData=[]; localStorage.removeItem('sf_cart_'+STORE_ID); updateCartUI();
        setTimeout(()=>{window.location.href='<?= base_url('store/' . ($settings->store_slug ?? '')); ?>';},2000);
      }
    } else { showToast(res.message||'Failed to place order'); btn.disabled=false; btn.textContent='Place Order'; }
  }).catch(err=>{ showToast(err.message || 'Network error. Please try again.'); btn.disabled=false; btn.textContent='Place Order'; });
}

function payWithPaystack(key,email,amount,reference,orderId){
  const handler=PaystackPop.setup({
    key:key, email:email, amount:amount, currency:'NGN', ref:reference,
    callback:function(response){ showToast('Payment successful!'); cartData=[]; localStorage.removeItem('sf_cart_'+STORE_ID); updateCartUI(); setTimeout(()=>{window.location.href='<?= base_url('store/' . ($settings->store_slug ?? '')); ?>';},1500); },
    onClose:function(){ showToast('Payment cancelled'); const btn=document.getElementById('checkout-btn'); if(btn){ btn.disabled=false; btn.textContent='Place Order'; } }
  });
  handler.openIframe();
}

renderCart();
</script>
