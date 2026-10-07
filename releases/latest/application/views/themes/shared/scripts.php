<script>
  let cart = JSON.parse(localStorage.getItem('sf_cart_' + STORE_ID) || '[]');
  let modalProduct = null;
  let modalQty = 1;

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
        if(!items.length) return;
        const body = new URLSearchParams({
          store_id: STORE_ID, cart_token: mpCartToken(),
          '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>',
          items_json: JSON.stringify(items)
        });
        if(extra) for(const k in extra) body.set(k, extra[k]);
        fetch('<?= base_url('storefront/save_cart'); ?>', {method:'POST', body:body.toString(),
          headers:{'Content-Type':'application/x-www-form-urlencoded'}})
          .then(r => r.json())
          .then(res => {
            // Server rejected the token format (e.g. upgraded client) —
            // clear it so the next save mints a fresh crypto token.
            if(res && res.message === 'bad_token'){
              try{ localStorage.removeItem('sf_cart_token_' + STORE_ID); }catch(e){}
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

  function addToCart(id, type, name, price, image, qty, stock){
    // Variant parents are catalogue shells — never orderable directly.
    if(window.SF_PARENT_IDS && window.SF_PARENT_IDS[id] && !(window.sfPickedVariant && window.sfPickedVariant.id === id)){
      showToast('Please choose an option first');
      const pk = document.querySelector('.sf-variant-picker');
      if(pk) pk.scrollIntoView({behavior:'smooth', block:'center'});
      return false;
    }
    const cartType = type === 'physical' ? 'product' : type;
    const isPhysical = (cartType === 'product');
    if(isPhysical && stock !== undefined && stock <= 0 && !<?= ($settings->allow_backorder ?? false) ? 'true' : 'false'; ?>){
      showToast('Out of stock'); return false;
    }
    const addonPanel = document.querySelector('.mp-product-addons[data-parent-type="'+cartType+'"][data-parent-id="'+id+'"]');
    const addons = addonPanel ? Array.from(addonPanel.querySelectorAll('.mp-addon-choice:checked')).map(input=>({id:parseInt(input.value,10),qty:Math.min(parseInt(input.dataset.max,10)||1,Math.max(1,parseInt(input.closest('.mp-product-addon').querySelector('.mp-addon-qty').value,10)||1)),name:input.dataset.name||'',price:parseFloat(input.dataset.price)||0})) : [];
    const addonKey = addons.map(addon=>addon.id+'x'+addon.qty).join(',');
    const key = cartType + '_' + id + (addonKey ? '_' + addonKey : '');
    const existing = cart.find(i => i.key === key);
    if(existing){
      if(isPhysical && stock !== undefined && !<?= ($settings->allow_backorder ?? false) ? 'true' : 'false'; ?>){
        if(existing.qty + qty > stock){ showToast('Not enough stock'); return false; }
      }
      existing.qty += qty;
    } else {
      cart.push({key, id, type:cartType, name, price, image, qty: qty, stock: stock ?? 999, addons:addons});
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

  function doSearch(){
    const q = document.getElementById('search-input')?.value.trim();
    if(q) window.location.href = '<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>?search=' + encodeURIComponent(q);
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
