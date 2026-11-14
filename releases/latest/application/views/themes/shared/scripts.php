<?php $this->load->view('storefront/commerce'); ?>
<script>
  let cart = JSON.parse(localStorage.getItem('sf_cart_' + STORE_ID) || '[]');
  let modalProduct = null;
  let modalQty = 1;
  const SF_OUTLETS = <?= json_encode(array_map(function($o){ return ['id'=>(int)$o->id,'name'=>(string)$o->warehouse_name,'address'=>(string)($o->address??''),'city'=>(string)($o->city??'')]; }, (isset($outlets)&&is_array($outlets))?$outlets:[]), JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;
  const SF_LOCATION_KEY = 'sf_outlet_' + STORE_ID;
  const SF_LOCATION_URL = '<?= base_url('storefront/location_availability'); ?>';
  const SF_LOCATION_CSRF_NAME = <?= json_encode($this->security->get_csrf_token_name()); ?>;
  let sfLocationCsrfHash = <?= json_encode($this->security->get_csrf_hash()); ?>;
  let sfPendingAdd = null;

  function sfSelectedLocation(){
    const id=parseInt(localStorage.getItem(SF_LOCATION_KEY)||'0',10);
    return SF_OUTLETS.find(o=>o.id===id) || null;
  }
  function sfLocationEsc(value){return String(value==null?'':value).replace(/[&<>"']/g,c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));}
  function sfLocationCart(pending){
    const rows=cart.map(i=>({id:i.id,type:i.type==='service'?'service':'product',qty:i.qty||1}));
    if(pending) rows.push({id:pending.id,type:pending.type==='service'?'service':'product',qty:pending.qty||1});
    return rows;
  }
  function sfShowLocationPicker(pending){
    if(SF_OUTLETS.length<2){if(SF_OUTLETS.length===1)localStorage.setItem(SF_LOCATION_KEY,String(SF_OUTLETS[0].id));return false;}
    sfPendingAdd=pending||null;
    let overlay=document.getElementById('sf-location-picker-overlay');
    if(!overlay){
      overlay=document.createElement('div'); overlay.id='sf-location-picker-overlay'; overlay.setAttribute('role','presentation');
      overlay.style.cssText='position:fixed;inset:0;z-index:100100;background:rgba(15,23,42,.62);display:flex;align-items:center;justify-content:center;padding:16px;';
      overlay.innerHTML='<section role="dialog" aria-modal="true" aria-labelledby="sf-location-picker-title" style="width:min(100%,620px);max-height:90vh;overflow:auto;background:#fff;border-radius:18px;padding:24px;color:#172033;box-shadow:0 24px 80px rgba(15,23,42,.3);font:inherit"><h2 id="sf-location-picker-title" style="margin:0 0 8px;font-size:21px">Choose a store location</h2><p style="margin:0 0 16px;color:#64748b">Check item availability at each location before choosing where your order will be fulfilled.</p><div id="sf-location-picker-list"></div><div id="sf-location-picker-error" role="status" style="color:#b91c1c;margin-top:10px"></div><div style="display:flex;justify-content:flex-end;margin-top:16px"><button type="button" id="sf-location-picker-confirm" disabled style="border:0;border-radius:9px;padding:12px 18px;background:var(--mp-primary,#0f766e);color:#fff;font-weight:700;opacity:.55">Use this location</button></div></section>';
      document.body.appendChild(overlay);
      document.getElementById('sf-location-picker-confirm').addEventListener('click',function(){
        const chosen=overlay.querySelector('input[name="sf-location-choice"]:checked');
        if(!chosen){document.getElementById('sf-location-picker-error').textContent='Choose a location to continue.';return;}
        localStorage.setItem(SF_LOCATION_KEY,chosen.value);
        overlay.remove();
        const pendingItem=sfPendingAdd;sfPendingAdd=null;
        document.dispatchEvent(new CustomEvent('sf:location-selected',{detail:{id:Number(chosen.value)}}));
        if(pendingItem){window.SF_LOCATION_BYPASS=true;addToCart(pendingItem.id,pendingItem.type,pendingItem.name,pendingItem.price,pendingItem.image,pendingItem.qty,pendingItem.stock,pendingItem.options);window.SF_LOCATION_BYPASS=false;}
      });
    }
    const list=document.getElementById('sf-location-picker-list');
    const confirm=document.getElementById('sf-location-picker-confirm');
    const err=document.getElementById('sf-location-picker-error');
    const hasSavedLocation=!!sfSelectedLocation();
    confirm.disabled=!hasSavedLocation;confirm.style.opacity=hasSavedLocation?'1':'.55';err.textContent='Checking availability…';
    list.innerHTML=SF_OUTLETS.map((o,i)=>'<label style="display:flex;gap:12px;align-items:flex-start;border:1px solid #dbe3ee;border-radius:12px;padding:14px;margin:10px 0;cursor:pointer"><input type="radio" name="sf-location-choice" value="'+o.id+'" '+(sfSelectedLocation()&&sfSelectedLocation().id===o.id?'checked':'')+' style="margin-top:4px"><span style="flex:1"><strong>'+sfLocationEsc(o.name)+'</strong><small style="display:block;color:#64748b;margin-top:3px">'+sfLocationEsc([o.address,o.city].filter(Boolean).join(' · '))+'</small><span id="sf-location-status-'+o.id+'" style="display:block;color:#64748b;font-size:13px;margin-top:7px">Checking item availability…</span></span></label>').join('');
    list.onchange=function(e){if(e.target&&e.target.name==='sf-location-choice'){confirm.disabled=false;confirm.style.opacity='1';err.textContent='';}};
    overlay.style.display='flex';
    const form=new URLSearchParams({store_id:String(STORE_ID),cart:JSON.stringify(sfLocationCart(sfPendingAdd))});
    if(SF_LOCATION_CSRF_NAME&&sfLocationCsrfHash)form.set(SF_LOCATION_CSRF_NAME,sfLocationCsrfHash);
    fetch(SF_LOCATION_URL,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:form.toString()}).then(r=>r.json()).then(res=>{
      if(res.csrf_hash){sfLocationCsrfHash=res.csrf_hash;if(typeof CSRF_HASH!=='undefined')CSRF_HASH=res.csrf_hash;if(typeof MPCommerce!=='undefined'&&MPCommerce.config)MPCommerce.config.csrfHash=res.csrf_hash;}
      if(!res.status)throw new Error(res.message||'Availability is temporarily unavailable.');
      (res.locations||[]).forEach(loc=>{const node=document.getElementById('sf-location-status-'+loc.id);if(!node)return;const unavailable=(loc.items||[]).filter(x=>!x.available);node.textContent=loc.note||(!loc.items||!loc.items.length?'Availability will be confirmed by the store.':unavailable.length?'Unavailable here: '+unavailable.map(x=>x.name).join(', '):(loc.items.length===1?'Available for this order':'All '+loc.items.length+' cart items available'));node.style.color=unavailable.length?'#b45309':'#047857';});
      err.textContent='';
    }).catch(e=>{err.textContent=e.message||'Could not check stock. You can still choose a location; stock will be checked again before ordering.';});
    return true;
  }
  function sfRefreshLocationAvailability(items){
    if(SF_OUTLETS.length<2||!Array.isArray(items)||!items.length)return;
    const form=new URLSearchParams({store_id:String(STORE_ID),cart:JSON.stringify(items.map(i=>({id:i.id,type:i.type==='service'?'service':'product',qty:i.qty||1})))});
    if(SF_LOCATION_CSRF_NAME&&sfLocationCsrfHash)form.set(SF_LOCATION_CSRF_NAME,sfLocationCsrfHash);
    fetch(SF_LOCATION_URL,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/x-www-form-urlencoded'},body:form.toString()}).then(r=>r.json()).then(res=>{
      if(res.csrf_hash){sfLocationCsrfHash=res.csrf_hash;if(typeof CSRF_HASH!=='undefined')CSRF_HASH=res.csrf_hash;}
      if(!res.status)return;
      (res.locations||[]).forEach(loc=>{const nodes=document.querySelectorAll('[data-sf-outlet-availability="'+loc.id+'"]');const unavailable=(loc.items||[]).filter(x=>!x.available);const label=loc.note||(!loc.items||!loc.items.length?'Availability will be confirmed by the store.':unavailable.length?'Unavailable: '+unavailable.map(x=>x.name).join(', '):(loc.items.length===1?'All cart items available':'All '+loc.items.length+' cart items available'));nodes.forEach(node=>{node.textContent=label;node.style.color=unavailable.length?'#b45309':'#047857';});});
    }).catch(function(){});
  }
  window.sfRefreshLocationAvailability=sfRefreshLocationAvailability;

  document.addEventListener('DOMContentLoaded',function(){
    if(SF_OUTLETS.length>1 && !sfSelectedLocation() && /\/(cart|checkout)\/?$/.test(location.pathname) && cart.length){
      sfRefreshLocationAvailability(cart);
      sfShowLocationPicker(null);
    }
  });

  // Persisted-cart token — identifies this browser session server-side so
  // the merchant can list and recover abandoned checkouts. Always 128-bit
  // crypto-random; browsers without crypto.getRandomValues simply don't
  // persist a cart (server rejects weak formats anyway).
  function mpCartToken(){
    try{
      let t = localStorage.getItem('sf_cart_token_' + STORE_ID);
      if(!t || !/^[0-9a-f]{32,64}$/.test(t)){
        if(!(window.crypto && crypto.getRandomValues)) return '';
        t = Array.from(crypto.getRandomValues(new Uint8Array(16))).map(b => b.toString(16).padStart(2,'0')).join('');
        localStorage.setItem('sf_cart_token_' + STORE_ID, t);
      }
      return t;
    }catch(e){ return ''; }
  }

  // Stable per-event id — dedups client retries against server records.
  function mpEvtId(){
    try{
      return 'evt_' + Array.from(crypto.getRandomValues(new Uint8Array(8))).map(b => b.toString(16).padStart(2,'0')).join('');
    }catch(e){ return 'evt_' + Date.now().toString(16) + Math.random().toString(16).slice(2, 10); }
  }

  // Consent state re-read on every call — withdrawal stops tracking
  // immediately even if a tracker script is already loaded.
  function mpTrackingAllowed(){
    if(!window.MP_NEED_CONSENT) return true;
    return !!(window.mpTrackConsent && window.mpTrackConsent.granted());
  }

  // Forward a shopping event to whichever third-party pixels are loaded.
  // eventId lets platforms dedupe retries; purchase uses 'purchase_<code>'.
  function mpFirePixels(type, itemId, value, eventId){
    if(!mpTrackingAllowed()) return;
    try{
      const meta = {value: value, currency: (window.SF_CURRENCY || 'NGN')};
      if(itemId) meta.content_ids = [String(itemId)];
      if(window.gtag) gtag('event', type, {value: value, currency: meta.currency, items: itemId ? [{item_id: String(itemId)}] : undefined});
      if(window.fbq){
        const m = {view_item:'ViewContent', add_to_cart:'AddToCart', begin_checkout:'InitiateCheckout', purchase:'Purchase'}[type];
        if(m) fbq('track', m, meta, eventId ? {eventID: eventId} : undefined);
      }
      if(window.ttq){
        const m = {view_item:'ViewContent', add_to_cart:'AddToCart', begin_checkout:'InitiateCheckout', purchase:'CompletePayment'}[type];
        if(m) ttq.track(m, {content_id: itemId ? String(itemId) : undefined, value: value, currency: meta.currency, event_id: eventId});
      }
    }catch(e){}
  }

  // First-party funnel event. Dispatched only when the consent policy
  // allows — required consent that was never granted sends nothing.
  function mpTrackEvent(type, itemId, value, eventId){
    if(!mpTrackingAllowed()) return;
    const eid = eventId || mpEvtId();
    mpFirePixels(type, itemId, value, eid);
    try{
      const body = new URLSearchParams({
        store_id: STORE_ID, event: type, event_id: eid,
        '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>',
        consent: (window.mpTrackConsent && window.mpTrackConsent.granted()) ? '1' : '0',
        page: window.location.pathname,
        cart_token: mpCartToken()
      });
      if(itemId) body.set('item_id', itemId);
      if(value !== undefined) body.set('value', value);
      fetch('<?= base_url('storefront/track_event'); ?>', {method:'POST', body:body.toString(),
        headers:{'Content-Type':'application/x-www-form-urlencoded'}, keepalive:true}).catch(()=>{});
    }catch(e){}
  }

  // Debounced server-side cart snapshot — powers abandoned-cart recovery.
  let _mpCartSaveTimer = null;
  function mpSaveCart(extra){
    if(_mpCartSaveTimer) clearTimeout(_mpCartSaveTimer);
    _mpCartSaveTimer = setTimeout(function(){
      try{
        const items = (typeof cartData !== 'undefined' ? cartData : cart) || [];
        const subtotal = items.reduce(function(sum, item){
          return sum + (typeof sfCartLineTotal === 'function'
            ? sfCartLineTotal(item)
            : (Number(item.price)||0) * (Number(item.qty)||0));
        }, 0);
        const body = new URLSearchParams({
          store_id: STORE_ID, cart_token: mpCartToken(),
          '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>',
          items_json: JSON.stringify(items), subtotal: String(subtotal)
        });
        [['cust-name','customer_name'],['cust-phone','customer_phone'],['cust-email','customer_email']].forEach(function(pair){
          const input = document.getElementById(pair[0]);
          if(input) body.set(pair[1], input.value || '');
        });
        if(extra) for(const k in extra) body.set(k, extra[k]);
        fetch('<?= base_url('storefront/save_cart'); ?>', {method:'POST', body:body.toString(),
          headers:{'Content-Type':'application/x-www-form-urlencoded'}})
          .then(r => r.json())
          .then(res => {
            // Server rejected the token format (e.g. upgraded client) —
            // clear it so the next save mints a fresh crypto token.
            if(res && (res.message === 'bad_token' || res.message === 'ordered_token')){
              try{ localStorage.removeItem('sf_cart_token_' + STORE_ID); }catch(e){}
              if(res.message === 'ordered_token' && !window.mpCheckoutInProgress) mpSaveCart(extra);
            }
          }).catch(()=>{});
      }catch(e){}
    }, 800);
  }

  function saveCart(){
    localStorage.setItem('sf_cart_' + STORE_ID, JSON.stringify(cart));
    updateCartUI();
    mpSaveCart();
  }

  function updateCartUI(){
    let qty = 0, total = 0;
    cart.forEach(i => { qty += i.qty; total += (typeof sfCartLineTotal === 'function' ? sfCartLineTotal(i) : i.price * i.qty); });
    const cartCountEl = document.getElementById('cart-count');
    if(cartCountEl) cartCountEl.textContent = qty;
    const headerAmt = document.getElementById('header-cart-amount');
    if(headerAmt) headerAmt.textContent = formatMoney(total);
    const stickyQtyEl = document.getElementById('sticky-qty');
    if(stickyQtyEl) stickyQtyEl.textContent = qty;
    const stickyTotalEl = document.getElementById('sticky-total');
    if(stickyTotalEl) stickyTotalEl.textContent = formatMoney(total);
    const sticky = document.getElementById('sticky-cart');
    if(sticky){
      if(qty > 0 && !window.location.pathname.endsWith('/cart')) sticky.classList.add('show');
      else sticky.classList.remove('show');
    }
  }

  function orderProductOnWhatsApp(name, price){
    let msg = 'Hello, I would like to order: ' + name + ' — ' + formatMoney(price);
    const wnum = '<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? ''); ?>';
    if(wnum) window.open('https://wa.me/' + wnum + '?text=' + encodeURIComponent(msg), '_blank');
  }

  function addToCart(id, type, name, price, image, qty, stock, options){
    qty = Math.max(1, parseInt(qty, 10) || 1);
    // Variant parents are catalogue shells — never orderable directly.
    if(window.SF_PARENT_IDS && window.SF_PARENT_IDS[id] && !(window.sfPickedVariant && window.sfPickedVariant.id === id)){
      showToast('Please choose an option first');
      const pk = document.querySelector('.sf-variant-picker');
      if(pk) pk.scrollIntoView({behavior:'smooth', block:'center'});
      return false;
    }
    if(SF_OUTLETS.length>1 && !window.SF_LOCATION_BYPASS && !sfSelectedLocation()){
      sfShowLocationPicker({id:id,type:type,name:name,price:price,image:image,qty:qty,stock:stock,options:options});
      return false;
    }
    const cartType = type === 'physical' ? 'product' : type;
    const isPhysical = (cartType === 'product');
    if(isPhysical && stock !== undefined && stock <= 0 && !<?= ($settings->allow_backorder ?? false) ? 'true' : 'false'; ?>){
      showToast('Out of stock'); return false;
    }
    const addonPanel = document.querySelector('.mp-product-addons[data-parent-type="'+cartType+'"][data-parent-id="'+id+'"]');
    const addons = options && Array.isArray(options.addons) ? options.addons : (addonPanel ? Array.from(addonPanel.querySelectorAll('.mp-addon-choice:checked')).map(input=>({id:parseInt(input.value,10),qty:Math.min(parseInt(input.dataset.max,10)||1,Math.max(1,parseInt(input.closest('.mp-product-addon').querySelector('.mp-addon-qty').value,10)||1)),name:input.dataset.name||'',price:parseFloat(input.dataset.price)||0})) : []);
    const spice = options && ['mild','medium','hot','extra'].includes(options.spice_level) ? options.spice_level : '';
    const note = options ? String(options.note || '').slice(0,500) : '';
    const addonKey = addons.map(addon=>addon.id+'x'+addon.qty).join(',');
    // Event tickets: one line per session date, session carried to checkout.
    const evSession = (cartType === 'product' && options && options.session_id) ? parseInt(options.session_id, 10) : 0;
    const key = cartType + '_' + id + (addonKey ? '_' + addonKey : '') + (spice || note ? '_' + JSON.stringify([spice,note]) : '') + (evSession ? '_s' + evSession : '');
    if(isPhysical && stock !== undefined && !<?= ($settings->allow_backorder ?? false) ? 'true' : 'false'; ?> && cart.filter(i=>i.id===id && i.type===cartType).reduce((sum,i)=>sum+Number(i.qty),qty)>stock){
      showToast('Not enough stock'); return false;
    }
    const existing = cart.find(i => i.key === key);
    if(existing){
      if(isPhysical && stock !== undefined && !<?= ($settings->allow_backorder ?? false) ? 'true' : 'false'; ?>){
        if(existing.qty + qty > stock){ showToast('Not enough stock'); return false; }
      }
      existing.qty += qty;
    } else {
      cart.push(Object.assign({key, id, type:cartType, name, price, image, qty: qty, stock: stock ?? 999, addons:addons, spice_level:spice, note:note}, evSession ? {session_id: evSession} : {}));
    }
    saveCart();
    const unitPrice = price + addons.reduce((sum,addon)=>sum+(addon.price*addon.qty),0);
    mpTrackEvent('add_to_cart', id, unitPrice * qty);
    showToast(name + ' added to cart');
    return true;
  }

  function openProductModal(id, name, price, image, desc, stock, oldPrice, type){
    modalProduct = {id, name, price, image, desc, stock, type: type || 'product'};
    modalQty = 1;
    document.getElementById('modal-title').textContent = name;
    document.getElementById('modal-price').innerHTML = formatMoney(price) + (oldPrice > 0 ? ' <span style="text-decoration:line-through;font-size:16px;color:#94A3B8;margin-left:8px;">' + formatMoney(oldPrice) + '</span>' : '');
    document.getElementById('modal-desc').textContent = desc || '';
    document.getElementById('modal-img').src = image ? '<?= base_url(); ?>' + image : '';
    document.getElementById('modal-qty').textContent = '1';
    const mPhys = (modalProduct.type === 'product' || modalProduct.type === 'physical');
    const mOos = mPhys && stock !== undefined && stock <= 0 && !<?= ($settings->allow_backorder ?? false) ? 'true' : 'false'; ?>;
    const mAdd = document.getElementById('modal-add-btn');
    if(mAdd){ mAdd.disabled = mOos; mAdd.textContent = mOos ? 'Out of Stock' : 'Add to Cart'; mAdd.style.opacity = mOos ? '.5' : ''; mAdd.style.cursor = mOos ? 'not-allowed' : ''; }
    document.getElementById('product-modal').classList.add('show');
  }

  function closeModal(){
    document.getElementById('product-modal').classList.remove('show');
  }

  function adjustModalQty(delta){
    modalQty = Math.max(1, modalQty + delta);
    document.getElementById('modal-qty').textContent = modalQty;
  }

  function addModalToCart(){
    if(!modalProduct) return;
    if(addToCart(modalProduct.id, modalProduct.type || 'product', modalProduct.name, modalProduct.price, modalProduct.image, modalQty, modalProduct.stock)) closeModal();
  }

  // Shared variant-picker contract: sfPickVariant records the chosen child
  // item and notifies the page (sf:variant-picked) so detail templates can
  // swap price/image/stock and point add-to-cart at the selected variant.
  window.sfPickedVariant = null;
  function sfPickVariant(el){
    if(!el || el.disabled) return;
    el.closest('.sf-variant-chips').querySelectorAll('.sf-variant-chip').forEach(function(c){ c.classList.remove('active'); });
    el.classList.add('active');
    window.sfPickedVariant = {
      id: parseInt(el.dataset.id, 10),
      name: el.dataset.name,
      price: parseFloat(el.dataset.price),
      image: el.dataset.image,
      stock: parseInt(el.dataset.stock, 10)
    };
    document.dispatchEvent(new CustomEvent('sf:variant-picked', {detail: window.sfPickedVariant}));
  }
  // Preselect a chip already marked active (e.g. current variant page).
  (function(){
    const pre = document.querySelector('.sf-variant-chip.active');
    if(pre) sfPickVariant(pre);
  })();

  function doSearch(value){
    const q = (value !== undefined ? value : (document.getElementById('search-input')?.value || document.getElementById('search-input-mobile')?.value || '')).trim();
    if(q) window.location.href = '<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>?search=' + encodeURIComponent(q);
  }

  function doHeroSearch(){
    doSearch(document.getElementById('hero-search-input')?.value || '');
  }

  function sendWhatsAppOrder(){
    let msg = 'Hello, I would like to place an order from <?= htmlspecialchars(addslashes($store->store_name ?? 'your store')); ?>';
    if(cart.length > 0){
      msg += '\n\nItems:\n';
      let total = 0;
      cart.forEach(i => {
        msg += i.qty + ' x ' + i.name + ' — ' + formatMoney(i.price * i.qty) + '\n';
        total += i.price * i.qty;
      });
      msg += '\nTotal: ' + formatMoney(total);
    }
    msg += '\n\nThank you.';
    const phone = '<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? ''); ?>';
    if(phone){
      window.open('https://wa.me/' + phone + '?text=' + encodeURIComponent(msg), '_blank');
    } else {
      showToast('WhatsApp number not configured');
    }
  }

  function toggleMobileMenu(){
    const menu = document.getElementById('mobile-menu');
    const overlay = document.getElementById('mobile-menu-overlay');
    if(!menu) return;
    if(menu.style.display === 'block'){
      menu.style.display = 'none';
      if(overlay) overlay.style.display = 'none';
      document.body.style.overflow = '';
    } else {
      menu.style.display = 'block';
      if(overlay) overlay.style.display = 'block';
      document.body.style.overflow = 'hidden';
    }
  }

  document.addEventListener('click', function(e){
    const modal = document.getElementById('product-modal');
    if(e.target === modal) closeModal();
  });

  updateCartUI();

  // Highlight active mobile nav item
  (function(){
    const path = window.location.pathname;
    document.querySelectorAll('.mp-mobile-nav-item').forEach(function(el){
      const href = el.getAttribute('href');
      if(!href) return;
      try{
        const linkPath = new URL(href, window.location.href).pathname;
        const isHome = linkPath === path || (linkPath.replace(/\/$/, '') === path.replace(/\/$/, ''));
        const isNested = path.startsWith(linkPath + '/') && linkPath !== '/';
        if(isHome || isNested) el.classList.add('active');
        else el.classList.remove('active');
      }catch(e){}
    });
  })();

  // Analytics tracking
  (function(){
    if(!STORE_ID) return;
    const tracked = sessionStorage.getItem('sf_tracked_' + STORE_ID);
    if(tracked) return;
    const fd = new FormData();
    fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
    fd.append('store_id', STORE_ID);
    fd.append('page_url', window.location.href);
    fd.append('referrer', document.referrer);
    // Extract search term from URL query string
    const urlParams = new URLSearchParams(window.location.search);
    const searchTerm = urlParams.get('search') || '';
    if(searchTerm) fd.append('search_term', searchTerm);
    fetch('<?= base_url('online_store/track_visit'); ?>', {method:'POST', body:fd, keepalive:true})
    .then(() => { sessionStorage.setItem('sf_tracked_' + STORE_ID, '1'); })
    .catch(() => {});
  })();

  // Newsletter signup — posts email to the server so it can be exported later
  function mpNewsletterSubmit(e){
    e.preventDefault();
    const form = e.target;
    const input = form.querySelector('input[type=email]');
    const email = (input && input.value || '').trim();
    if(!email){ showToast('Please enter your email'); return false; }
    const btn = form.querySelector('button');
    if(btn) btn.disabled = true;
    const fd = new FormData();
    fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
    fd.append('email', email);
    fd.append('source', form.getAttribute('data-source') || 'newsletter');
    fetch('<?= base_url('store/' . ($settings->store_slug ?? '') . '/subscribe'); ?>', {method:'POST', body:fd})
      .then(r => r.json())
      .then(res => {
        showToast(res.message || 'Thank you for subscribing!');
        if(res.status) form.reset();
      })
      .catch(() => showToast('Could not subscribe right now. Please try again.'))
      .finally(() => { if(btn) btn.disabled = false; });
    return false;
  }

  // Lead capture — enquiry / quote request form
  function mpLeadSubmit(e){
    e.preventDefault();
    const form = e.target;
    const name = (form.querySelector('[name=name]')?.value || '').trim();
    const phone = (form.querySelector('[name=phone]')?.value || '').trim();
    const email = (form.querySelector('[name=email]')?.value || '').trim();
    if(!name || (!phone && !email)){ showToast('Please leave your name and a phone number or email'); return false; }
    const btn = form.querySelector('button[type=submit]');
    if(btn) btn.disabled = true;
    const fd = new FormData(form);
    fd.append('<?= $this->security->get_csrf_token_name(); ?>', '<?= $this->security->get_csrf_hash(); ?>');
    fetch('<?= base_url('store/' . ($settings->store_slug ?? '') . '/lead'); ?>', {method:'POST', body:fd})
      .then(r => r.json())
      .then(res => {
        showToast(res.message || 'Thank you!');
        if(res.status) form.reset();
      })
      .catch(() => showToast('Could not send your enquiry. Please try again.'))
      .finally(() => { if(btn) btn.disabled = false; });
    return false;
  }

  // Back to top toggle
  (function(){
    const backtop = document.getElementById('backtop');
    if(!backtop) return;
    window.addEventListener('scroll', function(){
      if(window.scrollY > 400) backtop.classList.add('show');
      else backtop.classList.remove('show');
    });
  })();
</script>
