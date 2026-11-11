/* Platform-owned commerce bridge for imported HTML websites. */
(function (w, d) {
  'use strict';
  var cfg = w.MartPointThemeConfig;
  if (!cfg || w.MartPointTheme) return;
  var busy = false, revision = 0, volatileCart = [], memoryOnly = false, checkoutKeys = {}, providerLoader, pendingPayment;
  var client = new w.MPCommerceClient(cfg);
  function get(key) { try { return w.localStorage.getItem(key); } catch (e) { return null; } }
  function put(key, value) { try { w.localStorage.setItem(key, value); return true; } catch (e) { return false; } }
  function drop(key) { try { w.localStorage.removeItem(key); } catch (e) {} }
  function read() {
    try {
      var rows = memoryOnly ? volatileCart : JSON.parse(get(cfg.cartKey) || JSON.stringify(volatileCart));
      if (!Array.isArray(rows)) return [];
      return rows.filter(function (row) { return row && Number.isInteger(Number(row.id)) && Number(row.id) > 0 && Number.isInteger(Number(row.qty)) && Number(row.qty) > 0 && Number.isFinite(Number(row.price)); }).map(function (row) {
        return Object.assign({}, row, { addons: Array.isArray(row.addons) ? row.addons.filter(function (a) { return a && Number.isFinite(Number(a.price)) && Number(a.qty) > 0; }) : [] });
      });
    }
    catch (e) { return []; }
  }
  function esc(v) { return String(v == null ? '' : v).replace(/[&<>"']/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c]; }); }
  function money(v) { return (cfg.currencySymbol || cfg.currency) + ' ' + Number(v || 0).toFixed(2); }
  function notice(message) {
    var host = d.querySelector('[data-mp-status]');
    if (host) { host.textContent = message; host.setAttribute('role', 'status'); }
    else w.alert(message);
  }
  function emit(name, detail) { d.dispatchEvent(new w.CustomEvent(name, { detail: detail })); }
  function lineTotal(item) {
    var addons = (item.addons || []).reduce(function (sum, a) { return sum + Number(a.price || 0) * Number(a.qty || 1); }, 0);
    return (Number(item.price || 0) + addons) * Number(item.qty || 0);
  }
  function sync() {
    var rows = read(), total = rows.reduce(function (sum, item) { return sum + lineTotal(item); }, 0);
    d.querySelectorAll('[data-mp-cart-count]').forEach(function (x) { x.textContent = rows.reduce(function (sum, item) { return sum + Number(item.qty || 0); }, 0); });
    d.querySelectorAll('[data-mp-cart-total]').forEach(function (x) { x.textContent = total.toFixed(2); });
    d.querySelectorAll('[data-mp-cart]').forEach(function (box) {
      if (box.hasAttribute('data-mp-cart-custom')) return;
      box.innerHTML = rows.length ? rows.map(function (item, index) {
        return '<div class="mp-theme-cart-line"><span>' + esc(item.name) + '</span><label>Quantity <input type="number" min="1" step="1" value="' + Number(item.qty) + '" data-mp-cart-qty="' + index + '"></label><strong>' + esc(money(lineTotal(item))) + '</strong><button type="button" data-mp-cart-remove="' + index + '">Remove</button>' + (item.available === false ? '<span>Not available at this quantity/location</span>' : '') + '</div>';
      }).join('') : '<p>Your cart is empty.</p><a href="' + esc(cfg.homeUrl + '/products') + '">Browse products</a>';
    });
    emit('martpoint:cart', { items: rows, subtotal: total, currency: cfg.currency });
  }
  function write(rows) {
    volatileCart = rows; memoryOnly = !put(cfg.cartKey, JSON.stringify(rows)); revision++; sync();
  }
  function selectedLocation() { return cfg.locationSelectionEnabled === false ? 0 : Number(get('sf_outlet_' + cfg.storeId) || 0); }
  function editable() {
    if (cfg.preview) { notice('Shopping actions are disabled in preview.'); return false; }
    if (!cfg.cartEnabled) { notice('This store accepts enquiries, not online orders.'); return false; }
    if (busy) { notice('Please wait for checkout to finish.'); return false; }
    return true;
  }
  function add(item) {
    if (!editable()) return false;
    var qty = Number(item.qty == null ? 1 : item.qty), id = Number(item.id);
    if (!Number.isInteger(id) || id < 1 || !Number.isInteger(qty) || qty < 1 || qty > 1000000) { notice('Enter a valid item and whole quantity.'); return false; }
    if (item.is_variant_parent || item.requires_quote) { notice('Open this product to select an option or request a quotation.'); return false; }
    var type = item.type === 'service' ? 'service' : 'product';
    var rows = read(), stock = item.stock == null ? null : Number(item.stock);
    var physical = type === 'product' && (!item.product_type || item.product_type === 'physical');
    var demand = rows.filter(function (r) { return Number(r.id) === id && r.type === type; }).reduce(function (n, r) { return n + Number(r.qty); }, qty);
    if (physical && !cfg.allowBackorder && stock !== null && (!Number.isFinite(stock) || demand > stock)) { notice('This quantity is not available.'); return false; }
    if (cfg.requiresLocation && !selectedLocation() && w.MPThemeLocationPicker) { w.MPThemeLocationPicker.open(item); return false; }
    var addons = Array.isArray(item.addons) ? item.addons : [];
    var suffix = JSON.stringify([addons.map(function (a) { return [Number(a.id), Number(a.qty || 1)]; }).sort(), Number(item.session_id || 0), item.spice_level || '', item.note || item.service_note || '']);
    var key = type + '_' + id + (addons.length || item.session_id || item.note || item.service_note || item.spice_level ? '_' + suffix : '');
    var existing = rows.find(function (r) { return r.key === key; });
    if (existing) existing.qty = Number(existing.qty) + qty;
    else rows.push(Object.assign({}, item, { key: key, id: id, type: type, qty: qty, stock: stock, price: Number(item.price || 0), addons: addons, _mp_currency: cfg.currency }));
    write(rows); return true;
  }
  function setQuantity(index, qty) {
    if (!editable()) return false;
    var rows = read(); qty = Number(qty);
    if (!rows[index] || !Number.isInteger(qty) || qty < 1 || qty > 1000000) { notice('Enter a valid whole quantity.'); sync(); return false; }
    rows[index].qty = qty; write(rows); return true;
  }
  function remove(index) { if (!editable()) return false; var rows = read(); if (!rows[index]) return false; rows.splice(index, 1); write(rows); return true; }
  async function quote() {
    if (cfg.preview) throw new Error('Shopping actions are disabled in preview.');
    var rows = read(); if (!rows.length) return { changed: false, items: [], needs_location: false };
    var snapshot = JSON.stringify(rows), location = selectedLocation(), currentRevision = revision;
    // Quote endpoints return base prices. Only display/submission amounts are converted.
    var result = await client.quote(rows, location);
    if (revision !== currentRevision || JSON.stringify(read()) !== snapshot || location !== selectedLocation()) throw new Error('Your cart or location changed. Refresh the quote and try again.');
    if (result.transaction_currency && result.transaction_currency !== cfg.currency) throw new Error('Checkout currency changed. Refresh this page and review your cart.');
    var oldRate = Number(cfg.currencyRate || 1), rate = Number(result.exchange_rate || oldRate);
    if (!(rate > 0) || !Number.isFinite(rate)) throw new Error('Refresh this page to load the current currency.');
    result.items = result.items.map(function (line) {
      line.price = Math.round((Number(line.price) / rate + Number.EPSILON) * 100) / 100;
      line.addons = (line.addons || []).map(function (a) { return Object.assign({}, a, { price: Math.round((Number(a.price) / rate + Number.EPSILON) * 100) / 100 }); });
      line._mp_currency = cfg.currency; return line;
    });
    result.changed = oldRate !== rate || client.changed(rows, result.items) || rows.some(function (r) { return r._mp_currency && r._mp_currency !== cfg.currency; });
    cfg.currencyRate = rate;
    write(result.items); return result;
  }
  function randomKey() {
    if (w.crypto && w.crypto.getRandomValues) { var a = new Uint32Array(4); w.crypto.getRandomValues(a); return Array.from(a).map(function (n) { return n.toString(16); }).join(''); }
    return Date.now().toString(16) + Math.random().toString(16).slice(2);
  }
  function formFields(fd) {
    var fields = [];
    fd.forEach(function (v, k) { if (k !== cfg.csrfName) fields.push([k, typeof v === 'string' ? v : [v.name, v.size, v.lastModified]]); });
    return fields;
  }
  function paymentSignature(form) { return JSON.stringify([formFields(new w.FormData(form)), read(), cfg.currency, selectedLocation()]); }
  async function requestKey(fd) {
    var signature = JSON.stringify(formFields(fd)), digest;
    if (w.crypto && w.crypto.subtle && w.TextEncoder) {
      var hash = await w.crypto.subtle.digest('SHA-256', new w.TextEncoder().encode(signature));
      digest = Array.from(new Uint8Array(hash)).map(function (n) { return n.toString(16).padStart(2, '0'); }).join('');
      var stored; try { stored = JSON.parse(get('sf_checkout_' + cfg.storeId) || 'null'); } catch (e) {}
      if (stored && stored.digest === digest && stored.key) return stored.key;
    }
    var key = checkoutKeys[signature] || ('html_' + cfg.storeId + '_' + randomKey());
    checkoutKeys[signature] = key;
    if (digest) put('sf_checkout_' + cfg.storeId, JSON.stringify({ digest: digest, key: key }));
    return key;
  }
  function clearCart() { volatileCart = []; pendingPayment = null; drop(cfg.cartKey); drop('sf_checkout_' + cfg.storeId); revision++; sync(); }
  function loadPaystack() {
    if (w.PaystackPop) return Promise.resolve();
    if (!providerLoader) providerLoader = new Promise(function (resolve, reject) {
      var script = d.createElement('script'); script.src = 'https://js.paystack.co/v1/inline.js';
      script.onload = function () { if (w.PaystackPop) resolve(); else reject(new Error('Payment provider could not load.')); };
      script.onerror = function () { providerLoader = null; reject(new Error('Payment provider could not load. Please retry.')); };
      d.head.appendChild(script);
    });
    return providerLoader;
  }
  async function payment(result) {
    await loadPaystack();
    return new Promise(function (resolve, reject) {
      var split = result.paystack_split || {};
      var popup = w.PaystackPop.setup({ key: result.public_key, email: result.email, amount: result.amount_kobo,
        currency: result.currency || 'NGN', ref: result.reference, subaccount: split.subaccount,
        transaction_charge: split.transaction_charge, bearer: split.bearer,
        callback: function (response) {
          w.fetch(cfg.verifyUrl + '?reference=' + encodeURIComponent(response.reference), { credentials: 'same-origin', headers: { Accept: 'application/json' } })
            .then(function (r) { return r.json(); }).then(function (r) {
              if (!r.status) throw new Error(r.message || 'Payment could not be verified.');
              clearCart(); w.location.href = r.redirect_url; resolve(r);
            }).catch(function () { w.location.href = cfg.callbackUrl + '?reference=' + encodeURIComponent(response.reference); resolve(); });
        }, onClose: function () { reject(new Error('Payment window closed. Your order is awaiting payment; retry to continue.')); }
      }); popup.openIframe();
    });
  }
  async function checkout(form) {
    if (!editable()) return false;
    if (form.reportValidity && !form.reportValidity()) return false;
    busy = true;
    var buttons = Array.from(form.querySelectorAll('[type=submit]')), disabled = buttons.map(function (b) { return b.disabled; });
    buttons.forEach(function (b) { b.disabled = true; });
    try {
      // Closing a provider popup must not create another order or re-quote stock held by this one.
      if (pendingPayment && pendingPayment.signature === paymentSignature(form)) return await payment(pendingPayment.order);
      pendingPayment = null;
      if (!read().length) throw new Error('Your cart is empty.');
      var result = await quote();
      if (result.needs_location) { if (w.MPThemeLocationPicker) w.MPThemeLocationPicker.open(null); throw new Error('Choose a fulfilment location and review your cart.'); }
      if (result.changed) throw new Error('Your prices or availability have changed. Review your cart and submit again.');
      if (!cfg.allowBackorder && result.items.some(function (item) { return item.available === false; })) throw new Error('Reduce unavailable quantities or choose another location.');
      var fd = new w.FormData(form);
      fd.set('store_id', String(cfg.storeId)); fd.set('cart', JSON.stringify(read()));
      // A standard single-store pharmacy must receive no customer-selected warehouse.
      fd.set('warehouse_id', String(selectedLocation() || ''));
      if (cfg.csrfName) fd.set(cfg.csrfName, cfg.csrfHash);
      fd.set('idempotency_key', await requestKey(fd));
      var response = await w.fetch(cfg.placeOrderUrl, { method: 'POST', body: fd, credentials: 'same-origin', headers: { Accept: 'application/json' } });
      var order = await response.json(); if (order.csrf_hash) cfg.csrfHash = order.csrf_hash;
      if (!response.ok || !order.status) throw new Error(order.message || 'Could not place your order.');
      if (order.payment_required && order.public_key) { pendingPayment = { order: order, signature: paymentSignature(form) }; return await payment(order); }
      if (order.redirect_url) { if (!order.payment_required) clearCart(); w.location.href = order.redirect_url; return order; }
      if (order.payment_required) throw new Error('Your order is awaiting payment. Open the store account to continue.');
      clearCart(); notice(order.message || 'Order received.'); return order;
    } finally { busy = false; buttons.forEach(function (b, i) { b.disabled = disabled[i]; }); }
  }
  function fromButton(b) { return { id: Number(b.dataset.productId), type: b.dataset.productType || 'product', name: b.dataset.productName,
    price: Number(b.dataset.productPrice), image: b.dataset.productImage, stock: b.dataset.productStock == null ? null : Number(b.dataset.productStock),
    qty: Number(b.dataset.productQty || 1), product_type: b.dataset.productKind || 'physical',
    is_variant_parent: b.dataset.variantParent === '1', requires_quote: b.dataset.requiresQuote === '1' }; }
  w.MartPointTheme = { version: 1, storeId: cfg.storeId, addToCart: add, cart: read, refresh: sync, quote: quote, setQuantity: setQuantity, remove: remove, checkout: checkout };
  w.mpThemeAddToCart = add;
  d.addEventListener('click', function (e) {
    var b = e.target.closest ? e.target.closest('[data-mp-add-to-cart],[data-mp-cart-remove]') : null;
    if (!b) return; e.preventDefault();
    if (b.hasAttribute('data-mp-cart-remove')) remove(Number(b.dataset.mpCartRemove)); else add(fromButton(b));
  });
  d.addEventListener('change', function (e) { if (e.target.hasAttribute && e.target.hasAttribute('data-mp-cart-qty')) setQuantity(Number(e.target.dataset.mpCartQty), e.target.value); });
  d.addEventListener('submit', function (e) {
    var form = e.target.closest ? e.target.closest('[data-mp-checkout]') : null;
    if (form) { e.preventDefault(); checkout(form).catch(function (error) { notice(error.message || 'Could not place the order. Please retry.'); }); }
  });
  // Native editorial/contact forms also remain read-only during package preview.
  d.addEventListener('submit', function (e) {
    if (cfg.preview && e.target && String(e.target.method || '').toLowerCase() === 'post') { e.preventDefault(); e.stopImmediatePropagation(); notice('Form submissions are disabled in preview.'); }
  }, true);
  d.addEventListener('martpoint:location', function () { if (!cfg.preview && read().length) quote().catch(function (e) { notice(e.message); }); });
  w.addEventListener('storage', function (e) { if (e.key === cfg.cartKey) { memoryOnly = false; revision++; sync(); } });
  d.addEventListener('DOMContentLoaded', function () {
    sync();
    if (!cfg.preview && d.querySelector('[data-mp-cart],[data-mp-checkout]') && read().length) quote().catch(function (error) { notice(error.message); });
  });
  sync();
})(window, document);
