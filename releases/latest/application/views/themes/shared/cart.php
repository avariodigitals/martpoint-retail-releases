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
  /* The floating WhatsApp bubble overlaps the sticky Place Order button on
     mobile checkout — hide it here, the shopper is already ordering. */
  .mp-sticky-wa { display:none !important; }
  .mp-cart-section { padding-top:24px !important; }

  /* Checkout UI always renders in a clean sans face — theme display fonts
     (serif headlines etc.) must not leak into forms and controls */
  #cart-container, #cart-container input, #cart-container textarea,
  #cart-container select, #cart-container button, #cart-container label {
    font-family: var(--mp-font, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif);
  }

  /* Two-column layout — spacious item list + form on the left,
     compact sticky summary on the right */
  .mp-cart-layout { display:grid; grid-template-columns:minmax(0,1fr) 360px; gap:32px; align-items:start; }
  @media(max-width:959px){
    .mp-cart-layout { grid-template-columns:1fr; gap:24px; }
  }
  .mp-cart-aside { position:sticky; top:80px; }
  @media(max-width:959px){ .mp-cart-aside { position:static; } }

  /* Item list — one bordered container, rows separated by hairlines */
  .mp-items-card { background:var(--mp-white); border:1px solid var(--mp-border); border-radius:var(--mp-radius); margin-bottom:24px; overflow:hidden; }
  .mp-items-head { display:flex; justify-content:space-between; align-items:baseline; padding:18px 24px; border-bottom:1px solid var(--mp-border); }
  .mp-items-title { font-size:15px; font-weight:700; color:var(--mp-dark); }
  .mp-items-count { font-size:13px; color:var(--mp-gray); }
  .mp-item { display:grid; grid-template-columns:72px minmax(0,1fr) auto; grid-template-areas:'img body side'; gap:16px; padding:18px 24px; align-items:center; }
  .mp-item + .mp-item { border-top:1px solid var(--mp-border); }
  .mp-item-img { grid-area:img; width:72px; height:72px; border-radius:10px; background:var(--mp-light-gray); overflow:hidden; }
  .mp-item-img img { width:100%; height:100%; object-fit:cover; display:block; }
  .mp-item-body { grid-area:body; min-width:0; }
  .mp-item-name { font-size:15px; font-weight:600; color:var(--mp-dark); line-height:1.35; }
  .mp-item-meta { display:flex; align-items:center; gap:8px; margin-top:4px; font-size:12.5px; color:var(--mp-gray); flex-wrap:wrap; }
  .mp-item-type { font-size:10.5px; font-weight:700; letter-spacing:.04em; text-transform:uppercase; padding:2px 8px; border-radius:999px; background:var(--mp-light-gray); color:var(--mp-gray); }
  .mp-item-stockwarn { font-size:12px; color:var(--mp-warning); font-weight:600; margin-top:4px; }
  .mp-item-side { grid-area:side; display:flex; flex-direction:column; align-items:flex-end; gap:10px; }
  .mp-item-price { font-size:15px; font-weight:700; color:var(--mp-dark); white-space:nowrap; }
  .mp-item-actions { display:flex; align-items:center; gap:8px; }
  .mp-stepper { display:inline-flex; align-items:center; border:1px solid var(--mp-border); border-radius:999px; }
  .mp-stepper button { width:36px; height:36px; border:none; background:transparent; font-size:17px; line-height:1; cursor:pointer; color:var(--mp-dark); display:flex; align-items:center; justify-content:center; }
  .mp-stepper button:hover { background:var(--mp-light-gray); }
  .mp-stepper button:first-child { border-radius:999px 0 0 999px; }
  .mp-stepper button:last-child { border-radius:0 999px 999px 0; }
  .mp-stepper span { min-width:30px; text-align:center; font-weight:600; font-size:14px; color:var(--mp-dark); }
  .mp-item-remove { background:none; border:none; color:var(--mp-gray); cursor:pointer; padding:8px; border-radius:8px; display:flex; align-items:center; }
  .mp-item-remove:hover { color:var(--mp-danger); background:rgba(239,68,68,.08); }
  .mp-item-remove svg { width:16px; height:16px; }
  @media(max-width:959px){
    .mp-item { grid-template-columns:56px minmax(0,1fr); grid-template-areas:'img body' 'side side'; padding:16px; }
    .mp-item-img { width:56px; height:56px; }
    .mp-item-side { flex-direction:row; align-items:center; justify-content:space-between; }
  }

  /* Checkout sections — restrained cards, no shadows */
  .mp-checkout-card { background:var(--mp-white); border-radius:var(--mp-radius); border:1px solid var(--mp-border); padding:22px 24px; margin-bottom:16px; }
  .mp-checkout-card-title { font-size:15px; font-weight:700; margin-bottom:16px; display:flex; align-items:center; gap:10px; color:var(--mp-dark); }
  .mp-checkout-card-title .mp-step-num { width:22px; height:22px; border-radius:50%; background:var(--mp-primary); color:#fff; font-size:12px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
  .mp-checkout-fields { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
  .mp-checkout-fields .mp-field-full { grid-column:1 / -1; }
  @media(max-width:560px){ .mp-checkout-fields { grid-template-columns:1fr; } }
  .mp-cart-input { width:100%; padding:12px 14px; border:1px solid var(--mp-border); border-radius:var(--mp-radius-sm); font-size:14px; outline:none; transition:border-color .2s, box-shadow .2s; background:var(--mp-white); color:var(--mp-dark); }
  .mp-cart-input:focus { border-color:var(--mp-primary); box-shadow:0 0 0 3px rgba(59,130,246,0.1); }
  .mp-cart-input.mp-error { border-color:var(--mp-danger); box-shadow:0 0 0 3px rgba(239,68,68,0.08); }
  .mp-field-error { font-size:12px; color:var(--mp-danger); margin-top:4px; display:none; }
  .mp-field-error.show { display:block; }
  .mp-cart-label { font-size:12.5px; font-weight:600; color:var(--mp-gray); margin-bottom:6px; display:block; letter-spacing:.01em; }

  /* Shipping notice */
  .mp-ship-notice { background:#FEF3C7; border:1px solid #FCD34D; border-radius:var(--mp-radius-sm); padding:12px 14px; margin-bottom:16px; font-size:13px; color:#92400E; line-height:1.5; display:flex; gap:8px; align-items:flex-start; }
  .mp-ship-notice i { margin-top:2px; flex-shrink:0; }

  /* Inline city selector — expands within the page, never an OS dropdown */
  .mp-citysel { position:relative; }
  .mp-citysel-btn { width:100%; display:flex; align-items:center; justify-content:space-between; padding:12px 14px; border:1px solid var(--mp-border); border-radius:var(--mp-radius-sm); font-size:14px; background:var(--mp-white); color:var(--mp-dark); cursor:pointer; text-align:left; }
  .mp-citysel-btn i { color:var(--mp-gray); font-size:12px; }
  .mp-citysel-btn.mp-error { border-color:var(--mp-danger); }
  .mp-citysel-list { display:none; margin-top:4px; border:1px solid var(--mp-border); border-radius:var(--mp-radius-sm); background:var(--mp-white); max-height:240px; overflow-y:auto; }
  .mp-citysel.open .mp-citysel-list { display:block; }
  .mp-citysel-group { padding:8px 14px 4px; font-size:11px; font-weight:700; letter-spacing:.06em; text-transform:uppercase; color:var(--mp-gray); }
  .mp-citysel-opt { padding:10px 14px; font-size:14px; color:var(--mp-dark); cursor:pointer; }
  .mp-citysel-opt:hover { background:var(--mp-light-gray); }
  .mp-citysel-opt.disabled { color:var(--mp-gray); cursor:not-allowed; opacity:.7; }

  /* Payment/Shipping option rows — hairline borders, tinted active state */
  .mp-payment-options { display:flex; flex-direction:column; gap:8px; }
  .mp-payment-option { display:flex; align-items:center; gap:12px; padding:14px 16px; border:1px solid var(--mp-border); border-radius:var(--mp-radius-sm); cursor:pointer; background:var(--mp-white); transition:border-color .15s, background .15s; }
  .mp-payment-option:hover { border-color:var(--mp-primary); }
  .mp-payment-option.active { border-color:var(--mp-primary); background:#EFF6FF; }
  .mp-pay-divider { font-size:11px; font-weight:700; letter-spacing:.08em; text-transform:uppercase; color:var(--mp-gray); margin:14px 0 4px; display:flex; align-items:center; gap:10px; }
  .mp-pay-divider::after { content:''; flex:1; height:1px; background:var(--mp-border); }
  .mp-payment-option input { width:18px; height:18px; flex-shrink:0; accent-color:var(--mp-primary); }
  .mp-payment-option > div { flex:1; min-width:0; }
  .mp-pay-label { font-size:14px; font-weight:600; color:var(--mp-dark); }
  .mp-pay-desc { font-size:12px; color:var(--mp-gray); margin-top:2px; line-height:1.45; }
  .mp-pay-fee { font-size:13.5px; font-weight:700; color:var(--mp-primary); white-space:nowrap; }

  /* Primary action — consistent radius, no heavy shadow */
  .mp-cart-checkout { width:100%; padding:15px; border-radius:var(--mp-radius-sm); background:var(--mp-button); color:#fff; font-weight:700; border:none; cursor:pointer; font-size:15px; margin-top:16px; transition:background .2s, transform .1s; }
  .mp-cart-checkout:hover { background:var(--mp-button-dark); }
  .mp-cart-checkout:active { transform:scale(0.99); }
  .mp-cart-checkout:disabled { background:#CBD5E1; cursor:not-allowed; }
  .mp-cart-checkout .mp-btn-spinner { display:inline-block; width:16px; height:16px; border:2px solid rgba(255,255,255,.4); border-top-color:#fff; border-radius:50%; vertical-align:-3px; margin-right:8px; animation:mpSpin .7s linear infinite; }
  @keyframes mpSpin { to { transform:rotate(360deg); } }
  /* On mobile the summary sits below the form in one column — the
     button stays in normal flow so nothing is obstructed. */

  /* Compact summary — mini item list + totals only */
  .mp-order-summary { background:var(--mp-white); border-radius:var(--mp-radius); border:1px solid var(--mp-border); padding:20px 22px; }
  .mp-order-summary-title { font-size:15px; font-weight:700; margin-bottom:14px; color:var(--mp-dark); }
  .mp-summary-items { max-height:220px; overflow-y:auto; margin-bottom:14px; }
  .mp-summary-item { display:flex; align-items:center; gap:10px; padding:7px 0; }
  .mp-summary-item + .mp-summary-item { border-top:1px solid var(--mp-border); }
  .mp-summary-item-img { width:40px; height:40px; border-radius:8px; background:var(--mp-light-gray); overflow:hidden; flex-shrink:0; }
  .mp-summary-item-img img { width:100%; height:100%; object-fit:cover; display:block; }
  .mp-summary-item-body { flex:1; min-width:0; }
  .mp-summary-item-name { font-size:12.5px; font-weight:600; color:var(--mp-dark); line-height:1.3; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
  .mp-summary-item-qty { font-size:11.5px; color:var(--mp-gray); margin-top:1px; }
  .mp-summary-item-price { font-size:12.5px; font-weight:700; color:var(--mp-dark); white-space:nowrap; }
  .mp-continue-shopping { display:inline-flex; align-items:center; gap:6px; margin-top:12px; font-size:13px; font-weight:600; color:var(--mp-primary); }
  .mp-continue-shopping:hover { color:var(--mp-primary-dark); }
  .mp-quote-note { font-size:12px; color:var(--mp-gray); line-height:1.5; margin-top:10px; padding:10px 12px; background:var(--mp-light-gray); border-radius:var(--mp-radius-sm); }

  .mp-summary-totals { border-top:1px solid var(--mp-border); padding-top:14px; }
  .mp-cart-row { display:flex; justify-content:space-between; font-size:13.5px; margin-bottom:8px; color:var(--mp-gray); }
  .mp-cart-row span:last-child { color:var(--mp-dark); font-weight:600; }
  .mp-cart-total { display:flex; justify-content:space-between; align-items:center; font-size:19px; font-weight:800; border-top:1px solid var(--mp-border); padding-top:12px; margin-top:10px; color:var(--mp-dark); }
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
  @media(max-width:480px){ .mp-success-actions { flex-direction:column; } }
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
let selectedCityIdx = -1;
let cityShipMode = false;
const SHIPPING_NOTICE = <?= json_encode($settings->shipping_notice ?? ''); ?>;
const SHIPPING_METHODS = <?= json_encode(array_values(array_filter(json_decode($settings->shipping_methods_json ?? '[]', true) ?? [], function($m){ return !empty($m['enabled']); }))); ?>;
const CITY_SHIPPING = <?= json_encode(!empty($settings->city_shipping_enabled) ? array_values(array_filter(json_decode($settings->city_shipping_json ?? '[]', true) ?? [], function($z){ return is_array($z) && trim($z['city'] ?? '') !== ''; })) : []); ?>;
const TABLE_NUMBER = <?= json_encode($table_number ?? ''); ?>;
const ALLOW_BACKORDER = <?= !empty($settings->allow_backorder) ? 'true' : 'false'; ?>;

// A cart needs delivery only if it contains something physically
// deliverable — merchandise or a service. Digital downloads, courses
// and memberships never trigger the shipping section.
const MP_DELIVERABLE_TYPES = ['product','physical','service'];
function cartNeedsDelivery(){
  if(cartData.length === 0) return false;
  return cartData.some(i => MP_DELIVERABLE_TYPES.indexOf(i.type || 'product') !== -1 || i.product_type === 'physical' || i.product_type === 'service');
}

function isCartPhysicalOnly(){
  if(cartData.length === 0) return false;
  // Cart 'type' carries the item kind: shared sections pass the product_type
  // ('physical','digital',...) while theme overrides pass the literal
  // 'product' for merchandise. Both mean a physical deliverable here.
  return cartData.every(i => ['physical','product'].indexOf(i.type || 'product') !== -1 || i.product_type === 'physical');
}

function renderCart(){
  const c = document.getElementById('cart-container');
  if(cartData.length === 0){
    c.innerHTML = '<div class="mp-empty"><div style="margin-bottom:12px;color:#CBD5E1;"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg></div><div>Your cart is empty</div><a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>" style="display:inline-block;margin-top:16px;padding:12px 24px;background:var(--mp-primary);color:#fff;border-radius:var(--mp-radius-sm);font-weight:600;">Start Shopping</a></div>';
    return;
  }

  const needsDelivery = cartNeedsDelivery();

  // Build two-column layout
  let html = '<div class="mp-cart-layout">';

  // === LEFT COLUMN: item list, then checkout sections ===
  html += '<div class="mp-cart-main">';

  // Items — spacious rows, quantity/remove handled here; the summary
  // on the right mirrors the totals only.
  html += '<div class="mp-items-card">';
  html += '<div class="mp-items-head"><span class="mp-items-title">Your items</span><span class="mp-items-count">'+cartData.length+' item'+(cartData.length>1?'s':'')+'</span></div>';
  html += '<div id="cart-items"></div>';
  html += '</div>';
  html += '<div class="mp-checkout-card" id="mp-cart-upsell-wrap" style="display:none"><div class="mp-checkout-card-title">You may also like</div><div id="mp-cart-upsells" class="mp-cart-upsells"></div></div>';

  // Table QR banner
  if(TABLE_NUMBER){
    html += '<div style="background:#EFF6FF;border:1px solid var(--mp-primary);border-radius:var(--mp-radius);padding:14px 16px;margin-bottom:16px;display:flex;align-items:center;gap:10px;font-size:14px;font-weight:600;color:var(--mp-primary);"><i class="fa fa-qrcode"></i> Ordering for Table: ' + TABLE_NUMBER + '</div>';
  }

  // Step numbering adjusts as sections appear
  let stepNum = 1;

  // Contact details
  html += '<div class="mp-checkout-card">';
  html += '<div class="mp-checkout-card-title"><span class="mp-step-num">'+stepNum+'</span> ' + (TABLE_NUMBER ? 'Your Name for Table ' + TABLE_NUMBER + ' *' : 'Contact Details') + '</div>';
  stepNum++;
  html += '<div class="mp-checkout-fields">';
  html += '<div><label class="mp-cart-label">Full Name *</label><input type="text" class="mp-cart-input" id="cust-name" placeholder="John Doe" oninput="clearErr(this)"><div class="mp-field-error" id="err-cust-name">Please enter your name</div></div>';
  html += '<div><label class="mp-cart-label">Phone Number *</label><input type="tel" class="mp-cart-input" id="cust-phone" placeholder="08012345678" oninput="clearErr(this)"><div class="mp-field-error" id="err-cust-phone">Please enter your phone number</div></div>';
  html += '<div class="mp-field-full"><label class="mp-cart-label">Email (optional)</label><input type="email" class="mp-cart-input" id="cust-email" placeholder="john@example.com"></div>';
  html += '<div class="mp-field-full"><label class="mp-cart-label">' + (needsDelivery ? 'Delivery / Service Address' : 'Address (optional)') + '</label><textarea class="mp-cart-input" style="min-height:72px;resize:vertical;" id="cust-address" placeholder="Enter your address..."></textarea></div>';
  html += '</div>';
  html += '</div>';

  // Delivery — only when the cart contains a deliverable item
  cityShipMode = needsDelivery && !TABLE_NUMBER && CITY_SHIPPING.length > 0;
  if(needsDelivery && (cityShipMode || SHIPPING_METHODS.length > 0 || SHIPPING_NOTICE)){
    html += '<div class="mp-checkout-card">';
    html += '<div class="mp-checkout-card-title"><span class="mp-step-num">'+stepNum+'</span> ' + (cityShipMode ? 'Delivery City' : 'Shipping Method') + '</div>';
    stepNum++;
    if(SHIPPING_NOTICE){
      html += '<div class="mp-ship-notice"><i class="fa fa-info-circle"></i><span>' + SHIPPING_NOTICE.replace(/</g,'&lt;') + '</span></div>';
    }
    if(cityShipMode){
      html += '<label class="mp-cart-label">Where should we deliver? *</label>';
      // Inline custom select — the hidden input keeps the posted semantics
      // while the list expands inside the page (no OS dropdown).
      html += '<input type="hidden" id="ship-city" value="">';
      html += '<div class="mp-citysel">';
      html += '<button type="button" class="mp-citysel-btn" id="ship-city-btn" onclick="toggleCitySel(event)"><span id="ship-city-label">Select your city...</span><i class="fa fa-chevron-down"></i></button>';
      html += '<div class="mp-citysel-list" id="ship-city-list">';
      const cartSubtotal = cartData.reduce((sum,item)=>sum+sfCartLineTotal(item),0);
      let czGroups = {};
      CITY_SHIPPING.forEach((z,idx)=>{ const st=(z.state||'').trim()||'Other'; (czGroups[st]=czGroups[st]||[]).push(idx); });
      Object.keys(czGroups).sort().forEach(st=>{
        html += '<div class="mp-citysel-group">' + String(st).replace(/</g,'&lt;') + '</div>';
        czGroups[st].forEach(idx=>{
          const z = CITY_SHIPPING[idx];
          const freeOver = parseFloat(z.free_over)||0;
          const minOrder = parseFloat(z.min_order)||0;
          let lbl = (z.city === '*' ? 'Other areas' : z.city);
          if(minOrder > 0 && cartSubtotal < minOrder){
            lbl += ' — min order ' + formatMoney(minOrder);
            html += '<div class="mp-citysel-opt disabled" data-idx="' + idx + '">' + String(lbl).replace(/</g,'&lt;') + '</div>';
            return;
          }
          lbl += (freeOver > 0 && cartSubtotal < freeOver)
            ? ' — ' + formatMoney(parseFloat(z.fee)||0) + ' (free over ' + formatMoney(freeOver) + ')'
            : (parseFloat(z.fee) > 0 ? ' — ' + formatMoney(z.fee) : ' — Free');
          html += '<div class="mp-citysel-opt" data-idx="' + idx + '" onclick="pickCity(' + idx + ')">' + String(lbl).replace(/</g,'&lt;') + '</div>';
        });
      });
      html += '</div></div>';
      html += '<div class="mp-field-error" id="err-ship-city">Please select your delivery city</div>';
      html += '<div style="font-size:12px;color:var(--mp-gray);margin-top:6px;">Delivery fee is set by your city.</div>';
    }
    else if(SHIPPING_METHODS.length > 0){
      html += '<div class="mp-payment-options">';
      SHIPPING_METHODS.forEach((m,idx)=>{
        const feeLabel = (m.quote ? 'Quoted after order' : (m.fee > 0 ? formatMoney(m.fee) : 'Free'));
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
    html += '<div class="mp-checkout-card-title"><span class="mp-step-num">'+stepNum+'</span> Service Details</div>';
    stepNum++;
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

  // Order & Payment
  html += '<div class="mp-checkout-card">';
  html += '<div class="mp-checkout-card-title"><span class="mp-step-num">'+stepNum+'</span> Order &amp; Payment</div>';
  html += '<div class="mp-payment-options">';
  <?php if($settings->allow_paystack && $paystack_enabled): ?>
  html += '<div class="mp-payment-option active" onclick="selPay(this,\'paystack\')"><input type="radio" name="paymethod" value="paystack" checked><div><div class="mp-pay-label">Pay Online (Paystack)</div><div class="mp-pay-desc">Pay securely with card, bank or USSD</div></div></div>';
  <?php endif; ?>
  <?php if($settings->allow_pay_on_delivery): ?>
  if(isCartPhysicalOnly()){
    html += '<div class="mp-payment-option" onclick="selPay(this,\'pay_on_delivery\')"><input type="radio" name="paymethod" value="pay_on_delivery"><div><div class="mp-pay-label">Pay on Delivery</div><div class="mp-pay-desc">Pay when your order arrives</div></div></div>';
  }
  <?php endif; ?>
  <?php if($settings->allow_whatsapp && $settings->whatsapp_number): ?>
  html += '<div class="mp-pay-divider">Or send your order to the store</div>';
  html += '<div class="mp-payment-option<?= (!$settings->allow_paystack||!$paystack_enabled)?' active':''; ?>" onclick="selPay(this,\'whatsapp\')"><input type="radio" name="paymethod" value="whatsapp"<?= (!$settings->allow_paystack||!$paystack_enabled)?' checked':''; ?>><div><div class="mp-pay-label">Order via WhatsApp</div><div class="mp-pay-desc">Submit your order on WhatsApp — the store confirms payment &amp; delivery with you</div></div></div>';
  <?php endif; ?>
  html += '</div>';
  html += '</div>'; // end payment card

  html += '</div>'; // end left column

  // === RIGHT COLUMN: compact order summary ===
  html += '<div class="mp-cart-aside">';
  html += '<div class="mp-order-summary">';
  html += '<div class="mp-order-summary-title">Order summary</div>';
  html += '<div class="mp-summary-items" id="summary-items"></div>';
  html += '<div class="mp-coupon-box" id="coupon-box">'
       +  '<div style="display:flex;gap:8px;">'
       +  '<input type="text" class="mp-cart-input" id="coupon-code" placeholder="Coupon code" style="flex:1;text-transform:uppercase;">'
       +  '<button type="button" class="mp-cart-checkout" id="coupon-btn" onclick="applyCoupon()" style="margin-top:0;width:auto;padding:10px 16px;font-size:13px;">Apply</button>'
       +  '</div>'
       +  '<div id="coupon-msg" style="font-size:12px;margin-top:6px;display:none;"></div>'
       +  '</div>';
  html += '<div class="mp-summary-totals">';
  html += '<div class="mp-cart-row"><span>Subtotal</span><span id="summary-subtotal"></span></div>';
  html += '<div class="mp-cart-row" id="summary-discount-row" style="display:none;color:#059669;"><span>Coupon <span id="summary-coupon-code"></span> <a href="javascript:void(0)" onclick="removeCoupon()" style="color:#991B1B;font-size:11px;">[remove]</a></span><span id="summary-discount"></span></div>';
  html += '<div class="mp-cart-row" id="summary-shipping-row" style="display:none;"><span>Shipping</span><span id="summary-shipping"></span></div>';
  html += '<div class="mp-cart-total"><span id="summary-total-label">Total</span><span id="summary-total"></span></div>';
  html += '<div class="mp-quote-note" id="summary-quote-note" style="display:none;"><i class="fa fa-info-circle"></i> Delivery for the selected method is priced by the store — the delivery fee will be confirmed when your order is processed. This total covers your items only.</div>';
  html += '</div>';
  html += '<button class="mp-cart-checkout" id="checkout-btn" onclick="placeOrder()">Place Order</button>';
  html += '<a class="mp-continue-shopping" href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>">&larr; Continue shopping</a>';
  html += '</div>';
  html += '</div>'; // end right column

  html += '</div>'; // end layout
  c.innerHTML = html;
  loadCartUpsells();

  // Render item rows (left column) + compact summary list (right column)
  const MP_TYPE_LABELS = {digital:'Digital download',course:'Course',membership:'Membership',service:'Service'};
  const trashSvg = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>';
  let subtotal=0, itemsHtml='', miniHtml='';
  cartData.forEach((item,idx)=>{
    let it=sfCartLineTotal(item); subtotal+=it;
    const unitTotal=sfCartUnitTotal(item);
    const addonText=(item.addons||[]).map(addon=>String(addon.qty)+' × '+String(addon.name||'Extra')+' ('+formatMoney((Number(addon.price)||0)*(Number(addon.qty)||0))+')').join(', ');
    const isPhys = MP_DELIVERABLE_TYPES.indexOf(item.type || 'product') !== -1 || item.product_type === 'physical';
    const typeLabel = MP_TYPE_LABELS[item.type] || (MP_TYPE_LABELS[item.product_type] || '');
    const lowStock = (item.type !== 'service') && isPhys && !ALLOW_BACKORDER && typeof item.stock === 'number' && item.stock > 0 && item.qty >= item.stock;
    const imgHtml = item.image ? '<img src="<?= base_url(); ?>'+item.image+'" alt="'+String(item.name).replace(/"/g,'&quot;')+'">' : '';

    itemsHtml+='<div class="mp-item">';
    itemsHtml+='<div class="mp-item-img">'+imgHtml+'</div>';
    itemsHtml+='<div class="mp-item-body">';
    itemsHtml+='<div class="mp-item-name">'+String(item.name).replace(/</g,'&lt;')+'</div>';
    itemsHtml+='<div class="mp-item-meta">'+(typeLabel?'<span class="mp-item-type">'+typeLabel+'</span>':'')+'<span>'+formatMoney(unitTotal)+' each</span></div>';
    if(addonText) itemsHtml+='<div class="mp-item-meta">Extras: '+addonText.replace(/</g,'&lt;')+'</div>';
    if(lowStock) itemsHtml+='<div class="mp-item-stockwarn">Only '+item.stock+' in stock</div>';
    itemsHtml+='</div>';
    itemsHtml+='<div class="mp-item-side">';
    itemsHtml+='<div class="mp-item-price">'+formatMoney(it)+'</div>';
    itemsHtml+='<div class="mp-item-actions">';
    itemsHtml+='<div class="mp-stepper"><button onclick="upQty('+idx+',-1)" aria-label="Decrease quantity">&minus;</button><span>'+item.qty+'</span><button onclick="upQty('+idx+',1)" aria-label="Increase quantity">+</button></div>';
    itemsHtml+='<button class="mp-item-remove" onclick="rmItem('+idx+')" aria-label="Remove '+String(item.name).replace(/"/g,'&quot;')+'" title="Remove">'+trashSvg+'</button>';
    itemsHtml+='</div>';
    itemsHtml+='</div>';
    itemsHtml+='</div>';

    miniHtml+='<div class="mp-summary-item">';
    miniHtml+='<div class="mp-summary-item-img">'+imgHtml+'</div>';
    miniHtml+='<div class="mp-summary-item-body"><div class="mp-summary-item-name">'+String(item.name).replace(/</g,'&lt;')+'</div><div class="mp-summary-item-qty">'+item.qty+' × '+formatMoney(unitTotal)+(addonText?'<br>Extras: '+addonText.replace(/</g,'&lt;'):'')+'</div></div>';
    miniHtml+='<div class="mp-summary-item-price">'+formatMoney(it)+'</div>';
    miniHtml+='</div>';
  });
  document.getElementById('cart-items').innerHTML=itemsHtml;
  document.getElementById('summary-items').innerHTML=miniHtml;
  document.getElementById('summary-subtotal').textContent=formatMoney(subtotal);
  selectedPayment=document.querySelector('input[name="paymethod"]:checked')?.value||'pay_on_delivery';
  selectedShippingMethod=document.querySelector('input[name="shipmethod"]:checked')?.value||((needsDelivery && !cityShipMode && SHIPPING_METHODS.length>0)?SHIPPING_METHODS[0].name:'');
  selectedCityIdx=-1;
  updateShipSummary(subtotal);
}

let mpCartUpsellItems = [];
function loadCartUpsells(){
  const wrap=document.getElementById('mp-cart-upsell-wrap');
  const target=document.getElementById('mp-cart-upsells');
  if(!wrap||!target||!cartData.length)return;
  const body=new URLSearchParams({store_id:STORE_ID,cart_ids:JSON.stringify(cartData.map(i=>i.id)),[CSRF_NAME]:CSRF_HASH});
  fetch('<?= base_url('storefront/cart_upsells'); ?>',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body.toString()})
    .then(r=>r.json()).then(res=>{
      mpCartUpsellItems=Array.isArray(res.items)?res.items:[];
      if(!mpCartUpsellItems.length)return;
      const esc=value=>String(value??'').replace(/[&<>"']/g,ch=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[ch]));
      target.innerHTML=mpCartUpsellItems.map((item,index)=>'<div class="mp-cart-upsell">'+(item.image?'<img src="'+esc(item.image)+'" alt="">':'')+'<div class="mp-cart-upsell-info"><strong>'+esc(item.name)+'</strong><span>'+formatMoney(item.price)+'</span></div><button type="button" aria-label="Add '+esc(item.name)+'" onclick="addUpsellToCart('+index+')"><i class="fa fa-plus"></i></button></div>').join('');
      wrap.style.display='block';
    }).catch(()=>{});
}
function addUpsellToCart(index){
  const item=mpCartUpsellItems[index];if(!item)return;
  const existing=cartData.find(row=>String(row.id)===String(item.id)&&(row.type==='product'||row.type==='physical'));
  if(existing){existing.qty=(parseInt(existing.qty,10)||1)+1;}
  else{cartData.push({key:'product_'+item.id,id:item.id,type:'product',name:item.name,price:item.price,image:item.image,qty:1,stock:item.stock});}
  cart=cartData;saveCartState();
}

function getShipFee(){
  if(cityShipMode){
    const z=CITY_SHIPPING[selectedCityIdx];
    if(!z) return 0;
    const freeOver = parseFloat(z.free_over)||0;
    const subtotal = cartData.reduce((sum,item)=>sum+sfCartLineTotal(item),0);
    if(freeOver > 0 && subtotal >= freeOver) return 0;
    return parseFloat(z.fee)||0;
  }
  if(!selectedShippingMethod) return 0;
  const sm=SHIPPING_METHODS.find(m=>m.name===selectedShippingMethod);
  return sm?(sm.quote?0:(parseFloat(sm.fee)||0)):0;
}
function selectedShipIsQuote(){
  if(cityShipMode) return false;
  const sm=SHIPPING_METHODS.find(m=>m.name===selectedShippingMethod);
  return !!(sm && sm.quote);
}
function toggleCitySel(e){
  if(e) e.stopPropagation();
  const box=document.querySelector('.mp-citysel');
  if(box) box.classList.toggle('open');
}
document.addEventListener('click',function(e){
  const box=document.querySelector('.mp-citysel');
  if(box && !box.contains(e.target)) box.classList.remove('open');
});
function pickCity(idx){
  const hidden=document.getElementById('ship-city');
  const lbl=document.getElementById('ship-city-label');
  const z=CITY_SHIPPING[idx];
  if(!hidden||!z) return;
  hidden.value=idx;
  const freeOver=parseFloat(z.free_over)||0;
  const subtotal=cartData.reduce((sum,item)=>sum+sfCartLineTotal(item),0);
  let name=(z.city==='*'?'Other areas':z.city);
  if(freeOver>0&&subtotal>=freeOver) name+=' — Free delivery';
  else if(freeOver>0) name+=' — '+formatMoney(parseFloat(z.fee)||0)+' (free over '+formatMoney(freeOver)+')';
  else name+=(parseFloat(z.fee)>0?' — '+formatMoney(z.fee):' — Free');
  if(lbl) lbl.textContent=name;
  const box=document.querySelector('.mp-citysel');
  if(box) box.classList.remove('open');
  clearErr(document.getElementById('ship-city-btn'));
  selCity();
}
function selCity(){
  const sel=document.getElementById('ship-city');
  selectedCityIdx=sel&&sel.value!==''?parseInt(sel.value):-1;
  if(selectedCityIdx>=0){
    const z=CITY_SHIPPING[selectedCityIdx];
    selectedShippingMethod='Delivery - '+(z.city==='*'?'Other areas':z.city)+((z.state||'').trim()?', '+z.state.trim():'');
  } else {
    selectedShippingMethod='';
  }
  const subtotal=cartData.reduce((sum,item)=>sum+sfCartLineTotal(item),0);
  updateShipSummary(subtotal);
}
// Coupon state — the displayed discount is a preview; place_order recomputes
// it server-side before saving the order.
var appliedCoupon='', couponDiscountPreview=0;
function applyCoupon(){
  const code=(document.getElementById('coupon-code').value||'').trim().toUpperCase();
  const msg=document.getElementById('coupon-msg');
  if(!code){ msg.style.display='none'; return; }
  const subtotal=cartData.reduce((sum,item)=>sum+sfCartLineTotal(item),0);
  const data=new URLSearchParams();
  data.append('store_id',STORE_ID); data.append('coupon_code',code); data.append('subtotal',subtotal);
  if(CSRF_NAME && CSRF_HASH) data.append(CSRF_NAME, CSRF_HASH);
  const btn=document.getElementById('coupon-btn'); btn.disabled=true;
  fetch('<?= base_url('storefront/validate_coupon'); ?>',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:data.toString()})
  .then(r=>r.json()).then(res=>{
    btn.disabled=false;
    if(res.csrf_hash) CSRF_HASH = res.csrf_hash;
    if(res.status){
      appliedCoupon=code; couponDiscountPreview=parseFloat(res.discount)||0;
      msg.style.display='block'; msg.style.color='#059669';
      msg.textContent=(res.promo_name||'Coupon')+' applied — you save '+formatMoney(couponDiscountPreview);
      document.getElementById('coupon-code').disabled=true;
      btn.textContent='Applied';
    } else {
      appliedCoupon=''; couponDiscountPreview=0;
      msg.style.display='block'; msg.style.color='#991B1B';
      msg.textContent=res.message||'Invalid coupon';
    }
    updateShipSummary(subtotal);
  }).catch(()=>{ btn.disabled=false; showToast('Could not validate coupon'); });
}
function removeCoupon(){
  appliedCoupon=''; couponDiscountPreview=0;
  const inp=document.getElementById('coupon-code'); inp.disabled=false; inp.value='';
  const btn=document.getElementById('coupon-btn'); btn.disabled=false; btn.textContent='Apply';
  document.getElementById('coupon-msg').style.display='none';
  const subtotal=cartData.reduce((sum,item)=>sum+sfCartLineTotal(item),0);
  updateShipSummary(subtotal);
}
function updateShipSummary(subtotal){
  const fee=getShipFee();
  const row=document.getElementById('summary-shipping-row');
  if(row){
    if(fee>0||selectedShippingMethod||selectedCityIdx>=0){
      row.style.display='flex';
      const lbl=row.querySelector('span');
      if(lbl) lbl.textContent=(cityShipMode&&selectedCityIdx>=0)?'Delivery ('+CITY_SHIPPING[selectedCityIdx].city+')':'Shipping';
      document.getElementById('summary-shipping').textContent=(fee>0?formatMoney(fee):(selectedShipIsQuote()?'Quoted after order':(selectedCityIdx>=0||selectedShippingMethod?'Free':'—')));
    }
    else { row.style.display='none'; }
  }
  const quotePending = !cityShipMode && selectedShipIsQuote();
  const note=document.getElementById('summary-quote-note');
  const totalLbl=document.getElementById('summary-total-label');
  if(note) note.style.display = quotePending ? 'block' : 'none';
  if(totalLbl) totalLbl.textContent = quotePending ? 'Items total' : 'Total';
  const dRow=document.getElementById('summary-discount-row');
  if(dRow){
    if(appliedCoupon && couponDiscountPreview>0){
      dRow.style.display='flex';
      document.getElementById('summary-coupon-code').textContent=appliedCoupon;
      document.getElementById('summary-discount').textContent='−'+formatMoney(couponDiscountPreview);
    } else { dRow.style.display='none'; }
  }
  document.getElementById('summary-total').textContent=formatMoney(Math.max(0,subtotal+fee-couponDiscountPreview));
  updateCheckoutBtn();
}

// The action button describes the selected order channel — pay now,
// pay on delivery, or send to the store on WhatsApp.
function updateCheckoutBtn(){
  const btn=document.getElementById('checkout-btn');
  if(!btn || btn.disabled) return;
  const subtotal=cartData.reduce((sum,item)=>sum+sfCartLineTotal(item),0);
  const total=Math.max(0,subtotal+getShipFee()-couponDiscountPreview);
  if(selectedPayment==='paystack') btn.textContent='Pay '+formatMoney(total)+' securely';
  else if(selectedPayment==='whatsapp') btn.textContent='Send order via WhatsApp';
  else if(selectedPayment==='pay_on_delivery') btn.textContent='Place order — pay on delivery';
  else btn.textContent='Place Order';
}
function selShip(el,method){
  document.querySelectorAll('input[name="shipmethod"]').forEach(o=>o.closest('.mp-payment-option').classList.remove('active'));
  el.classList.add('active'); el.querySelector('input').checked=true; selectedShippingMethod=method;
  const subtotal=cartData.reduce((sum,item)=>sum+sfCartLineTotal(item),0);
  updateShipSummary(subtotal);
}

function selPay(el,m){
  document.querySelectorAll('input[name="paymethod"]').forEach(o=>o.closest('.mp-payment-option').classList.remove('active'));
  el.classList.add('active'); el.querySelector('input').checked=true; selectedPayment=m;
  updateCheckoutBtn();
}

function setErr(id){
  const input=document.getElementById(id);
  const err=document.getElementById('err-'+id);
  if(input) input.classList.add('mp-error');
  const btn=document.getElementById(id+'-btn');
  if(btn) btn.classList.add('mp-error');
  if(err) err.classList.add('show');
  const target=btn||input;
  if(target && target.scrollIntoView) target.scrollIntoView({behavior:'smooth',block:'center'});
}
function clearErr(el){
  if(!el) return;
  el.classList.remove('mp-error');
  const err=document.getElementById('err-'+(el.id||'').replace(/-btn$/,''));
  if(err) err.classList.remove('show');
}
function upQty(idx,delta){
  const it=cartData[idx];
  const isPhys=['product','physical'].indexOf(it.type||'product')!==-1||it.product_type==='physical';
  let q=it.qty+delta;
  if(q<1) q=1;
  if(isPhys && !ALLOW_BACKORDER && typeof it.stock==='number' && it.stock>0 && q>it.stock){
    q=it.stock; showToast('Only '+it.stock+' in stock');
  }
  it.qty=q; saveCartState();
}
function rmItem(idx){ cartData.splice(idx,1); saveCartState(); }
function saveCartState(){ localStorage.setItem('sf_cart_'+STORE_ID,JSON.stringify(cartData)); renderCart(); updateCartUI(); mpSaveCart(); }

function placeOrder(){
  const name=document.getElementById('cust-name').value.trim();
  const phone=document.getElementById('cust-phone').value.trim();
  const email=document.getElementById('cust-email').value.trim();
  const address=document.getElementById('cust-address').value.trim();
  const sDate=document.getElementById('service-date')?.value||'';
  const sTime=document.getElementById('service-time')?.value||'';
  const sNote=document.getElementById('service-note')?.value||'';
  let firstBad=null;
  if(!name){ setErr('cust-name'); firstBad=firstBad||'cust-name'; }
  if(!phone){ setErr('cust-phone'); firstBad=firstBad||'cust-phone'; }
  if(cityShipMode && selectedCityIdx<0){ setErr('ship-city'); firstBad=firstBad||'ship-city'; }
  if(firstBad){ const el=document.getElementById(firstBad); if(el&&el.focus) el.focus(); return; }
  if(cartData.length===0){ showToast('Cart is empty'); return; }
  const btn=document.getElementById('checkout-btn'); btn.disabled=true; btn.innerHTML='<span class="mp-btn-spinner"></span>'+(selectedPayment==='whatsapp'?'Sending order...':selectedPayment==='paystack'?'Opening payment...':'Placing order...');
  const payload=cartData.map(i=>({id:i.id,type:i.type==='physical'?'product':i.type,name:i.name,price:i.price,qty:i.qty,image:i.image,note:i.service_note||'',addons:(i.addons||[]).map(addon=>({id:addon.id,qty:addon.qty})),requires_appointment:i.requires_appointment||false,requires_note:i.requires_note||false}));
  if(selectedPayment==='whatsapp'){
    let msg='Hello, I would like to place an order from <?= htmlspecialchars(addslashes($store->store_name ?? 'your store')); ?>';
    msg+='\n\nItems:\n'; let total=0;
    cartData.forEach(i=>{ msg+=i.qty+' x '+i.name+' — '+formatMoney(sfCartLineTotal(i))+'\n'; (i.addons||[]).forEach(addon=>{msg+='   + '+addon.qty+' x '+addon.name+'\n';}); total+=sfCartLineTotal(i); });
    const shipFee=getShipFee();
    msg+='\nSubtotal: '+formatMoney(total);
    if(selectedShippingMethod){ msg+='\nShipping: '+selectedShippingMethod+(shipFee>0?' ('+formatMoney(shipFee)+')':(selectedShipIsQuote()?' (fee quoted by store)':' (Free)')); } else if(cityShipMode&&selectedCityIdx>=0){ msg+='\nDelivery City: '+CITY_SHIPPING[selectedCityIdx].city+(shipFee>0?' ('+formatMoney(shipFee)+')':' (Free)'); }
    msg+='\nTotal: '+formatMoney(total+shipFee); msg+='\n\nName: '+name; msg+='\nPhone: '+phone;
    if(email) msg+='\nEmail: '+email; if(address) msg+='\nAddress: '+address; if(selectedShippingMethod) msg+='\nShipping Method: '+selectedShippingMethod; if(sDate) msg+='\nService Date: '+sDate; if(sTime) msg+='\nService Time: '+sTime; if(sNote) msg+='\nService Note: '+sNote; if(TABLE_NUMBER){ msg+='\nTable: '+TABLE_NUMBER; } msg+='\n\nThank you.';
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
  if(cityShipMode && selectedCityIdx>=0){
    const z=CITY_SHIPPING[selectedCityIdx];
    data.append('shipping_city',(z.city||'')+'|'+(z.state||'').trim());
  }
  data.append('service_date',sDate); data.append('service_time',sTime); data.append('service_note',sNote);
  data.append('table_number',TABLE_NUMBER);
  data.append('coupon_code',appliedCoupon||'');
  data.append('cart',JSON.stringify(payload));
  data.append('cart_token',mpCartToken());
  data.append('consent',(window.mpTrackConsent && window.mpTrackConsent.granted())?'1':'0');
  if(CSRF_NAME && CSRF_HASH) data.append(CSRF_NAME, CSRF_HASH);
  fetch('<?= base_url('storefront/place_order'); ?>',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:data.toString()})
  .then(r=>{
    if(!r.ok) return r.text().then(text=>{ throw new Error('Server error ' + r.status + (text?': '+text.substring(0,200):'')); });
    return r.json();
  }).then(res=>{
    if(res.csrf_hash) CSRF_HASH = res.csrf_hash;
    if(res.status){
      if(res.payment_required&&res.public_key){
        payWithPaystack(res.public_key,res.email,res.amount_kobo,res.reference,res.order_id,res.currency||'NGN');
      } else {
        showOrderSuccess(res.order_code, res.redirect_url, pm);
        cartData=[]; cart=[]; localStorage.removeItem('sf_cart_'+STORE_ID); updateCartUI();
      }
    } else { showToast(res.message||'Failed to place order'); btn.disabled=false; updateCheckoutBtn(); }
  }).catch(err=>{ showToast(err.message || 'Network error. Please try again.'); btn.disabled=false; updateCheckoutBtn(); });
}

function payWithPaystack(key,email,amount,reference,orderId,currency){
  const handler=PaystackPop.setup({
    key:key, email:email, amount:amount, currency:currency||'NGN', ref:reference,
    callback:function(response){
      // The popup callback is client-side only — ask the server to verify
      // the reference against the order (store, amount, currency, status)
      // before telling the customer the payment succeeded.
      fetch('<?= base_url('storefront/verify_payment'); ?>?reference='+encodeURIComponent(response.reference))
      .then(r=>r.json())
      .then(v=>{
        if(v.status){
          showOrderSuccess(v.order_code||response.reference, v.redirect_url||('<?= base_url('store/' . ($settings->store_slug ?? '') . '/order_received/'); ?>'+(v.order_code||response.reference)), 'paystack');
          cartData=[]; cart=[]; localStorage.removeItem('sf_cart_'+STORE_ID); updateCartUI();
        } else {
          showToast(v.message||'Payment could not be verified');
          const btn=document.getElementById('checkout-btn'); if(btn){ btn.disabled=false; updateCheckoutBtn(); }
        }
      })
      .catch(()=>{
        // Verify endpoint unreachable — hand off to the server-side callback,
        // which verifies the reference itself before showing any outcome.
        window.location.href = '<?= base_url('storefront/paystack_callback'); ?>?reference=' + encodeURIComponent(response.reference);
      });
    },
    onClose:function(){ showToast('Payment cancelled'); const btn=document.getElementById('checkout-btn'); if(btn){ btn.disabled=false; updateCheckoutBtn(); } }
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

// Recover a persisted cart when the page is opened via a recovery link
// (?cart=TOKEN — the merchant shares it with the customer directly).
// The server revalidates every line (live price/stock/availability) and
// returns only items — saved contact details are never exposed here.
(function(){
  const params = new URLSearchParams(window.location.search);
  const token = params.get('cart');
  if(!token || cartData.length) return;
  const body = new URLSearchParams({store_id: STORE_ID, cart_token: token});
  if(CSRF_NAME && CSRF_HASH) body.append(CSRF_NAME, CSRF_HASH);
  fetch('<?= base_url('storefront/get_cart'); ?>',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:body.toString()})
    .then(r=>r.json()).then(res=>{
      if(res.csrf_hash) CSRF_HASH = res.csrf_hash;
      if(res.status && Array.isArray(res.items) && res.items.length){
        cartData = res.items; cart = res.items;
        localStorage.setItem('sf_cart_'+STORE_ID, JSON.stringify(cartData));
        // Restored cart adopts the recovered token so further snapshots
        // keep updating the same server row.
        try{ localStorage.setItem('sf_cart_token_'+STORE_ID, token); }catch(e){}
        renderCart(); updateCartUI();
        showToast('Your saved cart has been restored with current prices');
        if(Array.isArray(res.dropped) && res.dropped.length){
          showToast('No longer available: ' + res.dropped.join(', '));
        }
        // Opt-out affordance — recovery link holders can stop reminders.
        const box = document.createElement('div');
        box.id = 'mp-cart-optout';
        box.style.cssText = 'margin:8px auto 0;max-width:640px;text-align:center;font-size:12px;color:#64748B;';
        box.innerHTML = '<a href="#" id="mp-cart-optout-link" style="color:inherit;text-decoration:underline;">Don\'t remind me about this cart</a>';
        const host = document.querySelector('.cart-container, .sf-section, main, body');
        if(host && host.insertBefore) host.insertBefore(box, host.firstChild);
        const link = document.getElementById('mp-cart-optout-link');
        if(link) link.addEventListener('click', function(e){
          e.preventDefault();
          const b2 = new URLSearchParams({store_id: STORE_ID, cart_token: token});
          if(CSRF_NAME && CSRF_HASH) b2.append(CSRF_NAME, CSRF_HASH);
          fetch('<?= base_url('storefront/cart_optout'); ?>',{method:'POST',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:b2.toString()})
            .then(r=>r.json()).then(r2=>{ if(r2.csrf_hash) CSRF_HASH = r2.csrf_hash; box.innerHTML='Reminders stopped.'; }).catch(()=>{});
        });
      }
    }).catch(()=>{});
})();

// Begin-checkout funnel event + initial persisted snapshot (consent-gated
// by mpTrackEvent). Persisted carts are order-adjacent, not ads — they are
// always saved so the merchant can see abandonment.
if(cartData.length){
  if(typeof mpTrackEvent === 'function') mpTrackEvent('begin_checkout', 0, cartData.reduce((sum,item)=>sum+sfCartLineTotal(item),0));
  if(typeof mpSaveCart === 'function') mpSaveCart();
}

// Contact fields flow into the persisted cart — that's what makes an
// abandoned checkout reachable for a manual reminder.
['cust-name','cust-phone','cust-email'].forEach(function(id){
  document.addEventListener('input', function(e){
    if(e.target && e.target.id === id){
      mpSaveCart({
        customer_name: (document.getElementById('cust-name')||{}).value || '',
        customer_phone: (document.getElementById('cust-phone')||{}).value || '',
        customer_email: (document.getElementById('cust-email')||{}).value || ''
      });
    }
  });
});

renderCart();
</script>
