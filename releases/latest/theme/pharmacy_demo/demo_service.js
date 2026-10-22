(function (window) {
  'use strict';

  // Mock service seam. Replace these functions with fetch() calls to MartPoint
  // endpoints later; app.js only depends on this contract.
  var bootstrap = window.PHARMACY_DEMO_CONFIG || {};
  var KEY = 'nevari_pharmacy_demo_v2' + (bootstrap.storeId ? '_' + String(bootstrap.storeId) : '');
  var seed = {
    partnerMarketplaceEnabled: true,
    brand: {
      name: 'Nevari Health',
      strap: 'Pharmacy, wellness and everyday care',
      phone: '+234 800 000 2026',
      email: 'care@nevari.demo'
    },
    categories: [
      { id: 'medicines', label: 'Medicines', icon: 'pill' },
      { id: 'vitamins', label: 'Vitamins', icon: 'spark' },
      { id: 'wellness', label: 'Wellness', icon: 'heart' },
      { id: 'skincare', label: 'Skincare', icon: 'drop' },
      { id: 'first-aid', label: 'First aid', icon: 'plus' },
      { id: 'devices', label: 'Health devices', icon: 'device' }
    ],
    locations: [
      { id: 'branch-ikeja', name: 'Ikeja GRA Pharmacy', kind: 'branch', city: 'Lagos', area: 'Ikeja GRA', address: '14 Isaac John Street, Ikeja GRA', hours: 'Mon–Sat · 8:00am–8:00pm', coverage: 'Ikeja, Alausa and Maryland', deliveryFee: 1800, pickup: true, note: 'Collect from the pharmacy counter. Bring your order reference.' },
      { id: 'branch-vi', name: 'Victoria Island Pharmacy', kind: 'branch', city: 'Lagos', area: 'Victoria Island', address: '22 Adeola Odeku Street, Victoria Island', hours: 'Mon–Sat · 8:00am–7:00pm', coverage: 'Victoria Island, Ikoyi and Lekki Phase 1', deliveryFee: 2500, pickup: true, note: 'Collection is available after the order is marked ready.' },
      { id: 'hub-wuse', name: 'Wuse Partner Fulfilment Hub', kind: 'partner', city: 'Abuja', area: 'Wuse 2', address: '8 Aminu Kano Crescent, Wuse 2', hours: 'Mon–Fri · 9:00am–6:00pm', coverage: 'Wuse, Maitama and Central Area', deliveryFee: 2200, pickup: true, note: 'Partner hub team prepares assigned orders and confirms handover.' },
      { id: 'hub-surulere', name: 'Surulere Collection Hub', kind: 'pickup_hub', city: 'Lagos', area: 'Surulere', address: '5 Adeniran Ogunsanya Street, Surulere', hours: 'Mon–Sat · 9:00am–6:00pm', coverage: 'Pickup only · prepared by Ikeja Pharmacy', deliveryFee: 0, pickup: true, pickupOnly: true, sourceLocationId: 'branch-ikeja', note: 'This hub does not own stock. Orders are prepared by Ikeja Pharmacy and transferred here for collection.' }
    ],
    products: [
      { id: 'vit-c', name: 'Vitamin C 1000mg', category: 'vitamins', price: 6800, oldPrice: 7600, unit: 'tube of 20 effervescent tablets', strength: '1000mg', form: 'Effervescent tablet', pack: '20 tablets', prescription: false, colour: 'coral', description: 'A simple daily wellness essential in a single tube format.', tags: ['popular', 'deal'] },
      { id: 'omega-3', name: 'Omega 3 Fish Oil', category: 'wellness', price: 12500, unit: 'bottle of 60 softgels', strength: '1000mg', form: 'Softgel', pack: '60 softgels', prescription: false, colour: 'blue', description: 'A clearly labelled wellness supplement for everyday routines.', tags: ['wellness'] },
      { id: 'ceramide', name: 'Ceramide Barrier Cream', category: 'skincare', price: 9800, oldPrice: 11200, unit: 'tube of 50ml', strength: '50ml', form: 'Cream', pack: '1 tube', prescription: false, colour: 'peach', description: 'A fragrance-free moisturising cream for a calm, simple skincare routine.', tags: ['deal'] },
      { id: 'paracetamol', name: 'Paracetamol 500mg', category: 'medicines', price: 900, unit: 'strip of 12 tablets', strength: '500mg', form: 'Tablet', pack: '12 tablets', prescription: false, colour: 'green', description: 'Standard over-the-counter tablet pack. Follow the label and pharmacist guidance.', tags: ['everyday'] },
      { id: 'amox', name: 'Amoxicillin 500mg', category: 'medicines', price: 4200, unit: 'box of 21 capsules', strength: '500mg', form: 'Capsule', pack: '21 capsules', prescription: true, colour: 'navy', description: 'Prescription medicine. A pharmacist must review a valid prescription before supply.', tags: ['prescription'] },
      { id: 'first-aid', name: 'Everyday First Aid Kit', category: 'first-aid', price: 7200, unit: 'kit', strength: 'Standard kit', form: 'Kit', pack: '1 kit', prescription: false, colour: 'yellow', description: 'A compact kit with basic first-aid supplies for the home or travel bag.', tags: ['everyday'] },
      { id: 'bp-monitor', name: 'Upper Arm BP Monitor', category: 'devices', price: 28500, unit: 'one device', strength: 'Digital', form: 'Device', pack: '1 monitor', prescription: false, colour: 'lavender', description: 'A home blood-pressure monitor with a clear digital display.', tags: ['device'] },
      { id: 'magnesium', name: 'Magnesium 250mg', category: 'vitamins', price: 8300, unit: 'bottle of 60 tablets', strength: '250mg', form: 'Tablet', pack: '60 tablets', prescription: false, colour: 'mint', description: 'A clearly labelled mineral supplement for everyday wellness routines.', tags: ['wellness'] }
    ],
    stock: {
      'branch-ikeja': { 'vit-c': 18, 'omega-3': 9, ceramide: 12, paracetamol: 42, amox: 8, 'first-aid': 6, 'bp-monitor': 4, magnesium: 20 },
      'branch-vi': { 'vit-c': 8, 'omega-3': 4, ceramide: 5, paracetamol: 18, amox: 0, 'first-aid': 3, 'bp-monitor': 2, magnesium: 0 },
      // Wuse is a partner location. Its stock is represented by partner offers below,
      // not by the brand's own warehouse balance.
      'hub-wuse': {},
      'hub-surulere': {}
    },
    partners: [
      { id: 'partner-wuse-care', name: 'Wuse Care Partner', locationId: 'hub-wuse', status: 'approved', catalogueAccess: 'controlled' }
    ],
    partnerOffers: [
      { id: 'offer-wuse-vit-c', productId: 'vit-c', partnerId: 'partner-wuse-care', locationId: 'hub-wuse', price: 6500, stock: 7, status: 'approved' },
      { id: 'offer-wuse-omega-3', productId: 'omega-3', partnerId: 'partner-wuse-care', locationId: 'hub-wuse', price: 12100, stock: 3, status: 'approved' },
      { id: 'offer-wuse-paracetamol', productId: 'paracetamol', partnerId: 'partner-wuse-care', locationId: 'hub-wuse', price: 850, stock: 20, status: 'approved' },
      { id: 'offer-wuse-amox', productId: 'amox', partnerId: 'partner-wuse-care', locationId: 'hub-wuse', price: 4200, stock: 4, status: 'approved' },
      { id: 'offer-wuse-first-aid', productId: 'first-aid', partnerId: 'partner-wuse-care', locationId: 'hub-wuse', price: 7000, stock: 2, status: 'approved' },
      { id: 'offer-wuse-bp-monitor', productId: 'bp-monitor', partnerId: 'partner-wuse-care', locationId: 'hub-wuse', price: 27900, stock: 1, status: 'approved' },
      { id: 'offer-wuse-magnesium', productId: 'magnesium', partnerId: 'partner-wuse-care', locationId: 'hub-wuse', price: 7900, stock: 8, status: 'approved' }
    ],
    seededOrder: {
      id: 'NEV-10042',
      items: [{ productId: 'ceramide', qty: 1 }, { productId: 'paracetamol', qty: 2 }],
      locationId: 'branch-ikeja',
      fulfilment: 'delivery',
      address: 'Demo address, Ikeja GRA, Lagos',
      paymentMethod: 'online',
      paymentStatus: 'successful',
      status: 'preparing',
      customer: 'Demo customer',
      createdAt: '2026-10-20T09:30:00.000Z',
      history: [
        { status: 'confirmed', label: 'Order confirmed', at: '2026-10-20T09:30' },
        { status: 'accepted', label: 'Hub accepted', at: '2026-10-20T09:34' },
        { status: 'preparing', label: 'Preparing order', at: '2026-10-20T09:42' }
      ]
    }
  };

  // The standalone route keeps its fictional seed. A store-backed route
  // replaces only the catalogue/location layer with the server bootstrap,
  // leaving the presentation and workflow contract unchanged.
  if (bootstrap.brand && bootstrap.storeId) seed.brand = Object.assign({}, seed.brand, bootstrap.brand);
  if (Array.isArray(bootstrap.categories) && bootstrap.categories.length && bootstrap.storeId) seed.categories = bootstrap.categories;
  if (Array.isArray(bootstrap.products) && bootstrap.storeId) seed.products = bootstrap.products;
  if (Array.isArray(bootstrap.locations) && bootstrap.locations.length && bootstrap.storeId) seed.locations = bootstrap.locations;
  if (bootstrap.stock && bootstrap.storeId) seed.stock = bootstrap.stock;
  if (Array.isArray(bootstrap.partnerOffers) && bootstrap.storeId) seed.partnerOffers = bootstrap.partnerOffers;
  if (bootstrap.storeId) {
    seed.partnerMarketplaceEnabled = bootstrap.partnerMarketplaceEnabled !== false;
    seed.partners = bootstrap.partners || [];
    seed.seededOrder = null;
  }

  function clone(value) { return JSON.parse(JSON.stringify(value)); }
  function loadState() {
    try {
      var saved = JSON.parse(localStorage.getItem(KEY) || 'null');
    if (saved && saved.version === 2 && location(saved.locationId)) return saved;
    } catch (e) {}
    var firstLocation = seed.locations[0] ? seed.locations[0].id : 'branch-ikeja';
    var seededOrders = seed.seededOrder ? [clone(seed.seededOrder)] : [];
    return { version: 2, locationId: firstLocation, cart: [], orders: seededOrders, hubQueue: [], prescriptions: [], lastOrder: null };
  }
  var state = loadState();
  function save() { localStorage.setItem(KEY, JSON.stringify(state)); }
  function product(id) { return seed.products.find(function (p) { return p.id === id; }); }
  function location(id) { return seed.locations.find(function (l) { return l.id === id; }); }
  function partnerEnabled() {
    var config = window.PHARMACY_DEMO_CONFIG || {};
    return !!seed.partnerMarketplaceEnabled && config.partnerMarketplaceEnabled !== false;
  }
  function partner(id) { return seed.partners.find(function (p) { return p.id === id; }); }
  function offers(productId, locationId) {
    if (!partnerEnabled()) return [];
    return seed.partnerOffers.filter(function (offer) { return offer.productId === productId && offer.locationId === locationId && offer.status === 'approved'; }).map(function (offer) {
      var seller = partner(offer.partnerId);
      return Object.assign({}, offer, { partnerName: seller ? seller.name : 'Approved partner' });
    });
  }
  function itemAvailability(productId, locationId, offerId) {
    var loc = location(locationId);
    var selectedOffer = offerId ? seed.partnerOffers.find(function (offer) { return offer.id === offerId; }) : null;
    if (selectedOffer && partnerEnabled() && selectedOffer.productId === productId && selectedOffer.locationId === locationId && selectedOffer.status === 'approved') {
      var selectedSeller = partner(selectedOffer.partnerId);
      return { qty: Number(selectedOffer.stock || 0), price: Number(selectedOffer.price || product(productId).price), offerId: selectedOffer.id, sellerType: 'partner', sellerName: selectedSeller ? selectedSeller.name : 'Approved partner', available: Number(selectedOffer.stock || 0) > 0, reason: Number(selectedOffer.stock || 0) > 0 ? 'Available from ' + (selectedSeller ? selectedSeller.name : 'approved partner') : 'Partner offer is out of stock' };
    }
    var availableOffers = offers(productId, locationId);
    if (availableOffers.length) {
      var offer = availableOffers[0];
      return { qty: Number(offer.stock || 0), price: Number(offer.price || product(productId).price), offerId: offer.id, sellerType: 'partner', sellerName: offer.partnerName, available: Number(offer.stock || 0) > 0, reason: Number(offer.stock || 0) > 0 ? 'Available from ' + offer.partnerName : 'Partner offer is out of stock' };
    }
    var stockLocationId = loc && loc.pickupOnly ? loc.sourceLocationId : locationId;
    var qty = (seed.stock[stockLocationId] || {})[productId] || 0;
    if (loc && loc.pickupOnly) return { qty: qty, price: product(productId).price, offerId: null, sellerType: 'client', sellerName: seed.brand.name, available: qty > 0, reason: qty > 0 ? 'Available · prepared by the linked pharmacy source' : 'Unavailable from the preparing pharmacy' };
    if (loc && loc.kind === 'partner') return { qty: qty, price: product(productId).price, offerId: null, sellerType: 'partner', sellerName: loc.name, available: qty > 0, reason: qty > 0 ? 'Available from the controlled partner hub' : 'Unavailable at this partner hub' };
    return { qty: qty, price: product(productId).price, offerId: null, sellerType: 'client', sellerName: seed.brand.name, available: qty > 0, reason: qty > 0 ? 'Available here' : 'Unavailable at this location' };
  }
  function cartLines() {
    return state.cart.map(function (line) { return { line: line, product: product(line.productId), availability: itemAvailability(line.productId, state.locationId, line.offerId), unitPrice: Number(line.unitPrice || itemAvailability(line.productId, state.locationId, line.offerId).price || product(line.productId).price) }; }).filter(function (x) { return x.product; });
  }
  function subtotal() { return cartLines().reduce(function (sum, x) { return sum + x.unitPrice * x.line.qty; }, 0); }
  function orderTotal(order) {
    return order.items.reduce(function (sum, line) { var p = product(line.productId); return sum + (p ? Number(line.unitPrice || p.price) * line.qty : 0); }, 0) + (order.deliveryFee || 0);
  }
  function nextRef(prefix) { return prefix + '-' + String(Date.now()).slice(-6); }
  function storeBacked() { return !!(bootstrap.storeId && bootstrap.prescriptionRequestUrl); }
  function formPost(url, form) {
    return fetch(url, { method: 'POST', body: form, credentials: 'same-origin' }).then(function (response) {
      return response.json().catch(function () { return { status: false, message: 'The pharmacy service returned an unreadable response.' }; });
    }).then(function (result) {
      if (result && result.csrf_hash && bootstrap.csrfHash) bootstrap.csrfHash = result.csrf_hash;
      return result;
    });
  }
  function mergeOrder(order) {
    if (!order) return null;
    var local = state.orders.find(function (item) { return item.id === order.id; });
    var visibleStatus = order.deliveryStatus || order.status;
    if (visibleStatus === 'pending' || visibleStatus === 'paid') visibleStatus = 'confirmed';
    var mapped = Object.assign({}, local || {}, order, { status: visibleStatus });
    var index = state.orders.findIndex(function (item) { return item.id === order.id; });
    if (index === -1) state.orders.unshift(mapped); else state.orders[index] = mapped;
    save();
    return clone(mapped);
  }

  window.PharmacyDemoService = {
    brand: seed.brand,
    categories: seed.categories,
    products: seed.products,
    locations: seed.locations,
    partners: seed.partners,
    partnerMarketplaceEnabled: partnerEnabled(),
    partnerOffers: function (productId, locationId) { return clone(offers(productId, locationId)); },
    getState: function () { return clone(state); },
    getLocation: location,
    getProduct: product,
    getCart: cartLines,
    getSubtotal: subtotal,
    getOrderTotal: orderTotal,
    availability: itemAvailability,
    selectedLocation: function () { return location(state.locationId); },
    setLocation: function (id) {
      if (!location(id)) return { ok: false, message: 'Location not found.' };
      var previous = state.locationId;
      state.locationId = id;
      save();
      var unavailable = cartLines().filter(function (x) { return !x.availability.available; }).map(function (x) { return x.product.name; });
      return { ok: true, changed: previous !== id, unavailable: unavailable };
    },
    addToCart: function (productId, qty, offerId) {
      qty = Math.max(1, Number(qty) || 1);
      var p = product(productId);
      if (!p) return { ok: false, message: 'Product not found.' };
      if (p.prescription) return { ok: false, requiresPrescription: true, message: 'This medicine requires a prescription review.' };
      var available = itemAvailability(productId, state.locationId, offerId);
      var existing = state.cart.find(function (line) { return line.productId === productId && (line.offerId || null) === (available.offerId || null); });
      var nextQty = (existing ? existing.qty : 0) + qty;
      if (!available.available || nextQty > available.qty) return { ok: false, message: available.available ? 'Only ' + available.qty + ' available at this location.' : available.reason };
      if (existing) existing.qty = nextQty; else state.cart.push({ productId: productId, qty: qty, offerId: available.offerId || null, unitPrice: available.price, sellerType: available.sellerType, sellerName: available.sellerName });
      save();
      return { ok: true };
    },
    updateCart: function (productId, qty) {
      qty = Number(qty) || 0;
      var line = state.cart.find(function (x) { return x.productId === productId; });
      if (!line) return { ok: false };
      if (qty <= 0) state.cart = state.cart.filter(function (x) { return x.productId !== productId; });
      else {
        var available = itemAvailability(productId, state.locationId, line.offerId);
        if (qty > available.qty) return { ok: false, message: 'Only ' + available.qty + ' available at this location.' };
        line.qty = qty;
      }
      save(); return { ok: true };
    },
    clearCart: function () { state.cart = []; save(); },
    createPrescription: function (payload) {
      if (storeBacked()) {
        var form = new FormData();
        form.append('customer_name', payload.customer || 'Demo patient');
        form.append('customer_phone', payload.phone || '08000002026');
        form.append('customer_email', payload.email || 'demo.patient@example.com');
        form.append('note', payload.note || 'Online prescription request');
        if (payload.file) form.append('prescription_file', payload.file, payload.file.name);
        if (bootstrap.csrfName && bootstrap.csrfHash) form.append(bootstrap.csrfName, bootstrap.csrfHash);
        return formPost(bootstrap.prescriptionRequestUrl, form).then(function (result) {
          if (!result.status) return { ok: false, message: result.message || 'Prescription request failed.' };
          var request = result.request; request.locationId = payload.locationId || state.locationId; request.serverBacked = true;
          state.prescriptions.unshift(request); save(); return { ok: true, request: clone(request) };
        });
      }
      var request = { id: nextRef('RX'), status: 'awaiting_review', fileName: payload.fileName || 'fictional-prescription-demo.pdf', note: payload.note || '', locationId: payload.locationId || state.locationId, createdAt: new Date().toISOString(), items: payload.items || [] };
      state.prescriptions.unshift(request); save(); return Promise.resolve({ ok: true, request: clone(request) });
    },
    approvePrescription: function (id) {
      if (storeBacked() && bootstrap.prescriptionApproveUrl) {
        var form = new FormData(); form.append('id', String(id));
        if (bootstrap.csrfName && bootstrap.csrfHash) form.append(bootstrap.csrfName, bootstrap.csrfHash);
        return formPost(bootstrap.prescriptionApproveUrl, form).then(function (result) {
          if (!result.status) return { ok: false, message: result.message || 'Prescription review failed.' };
          var approved = result.request; approved.locationId = state.locationId; approved.serverBacked = true;
          var existingIndex = state.prescriptions.findIndex(function (item) { return String(item.id) === String(id); });
          if (existingIndex === -1) state.prescriptions.unshift(approved); else state.prescriptions[existingIndex] = approved;
          save(); return { ok: true, request: clone(approved) };
        });
      }
      var request = state.prescriptions.find(function (x) { return x.id === id; });
      if (request) { request.status = 'approved'; request.approvedItems = [{ productId: 'amox', qty: 1 }]; request.approvedTotal = product('amox').price; save(); }
      return Promise.resolve(request ? { ok: true, request: clone(request) } : { ok: false, message: 'Request not found.' });
    },
    refreshPrescription: function (id) {
      var request = state.prescriptions.find(function (item) { return String(item.id) === String(id); });
      if (!storeBacked() || !bootstrap.prescriptionStatusUrl || !request || !request.phone) return Promise.resolve(request ? clone(request) : null);
      var url = bootstrap.prescriptionStatusUrl + '?id=' + encodeURIComponent(request.id) + '&phone=' + encodeURIComponent(request.phone);
      return fetch(url, { credentials: 'same-origin' }).then(function (response) { return response.json(); }).then(function (result) {
        if (!result.status) return request;
        var updated = Object.assign({}, result.request, { locationId: request.locationId, serverBacked: true });
        var index = state.prescriptions.findIndex(function (item) { return String(item.id) === String(id); });
        if (index === -1) state.prescriptions.unshift(updated); else state.prescriptions[index] = updated;
        save(); return clone(updated);
      });
    },
    addApprovedPrescriptionToCart: function (id) {
      var request = state.prescriptions.find(function (x) { return x.id === id; });
      if (!request || request.status !== 'approved' || !request.approvedItems || !request.approvedItems.length) return { ok: false, message: 'This prescription is not approved yet.' };
      var unavailable = [];
      request.approvedItems.forEach(function (approved) {
        var available = itemAvailability(approved.productId, state.locationId);
        var existing = state.cart.find(function (line) { return line.productId === approved.productId; });
        if (!available.available || (existing ? existing.qty : 0) + approved.qty > available.qty) unavailable.push(product(approved.productId).name);
      });
      if (unavailable.length) return { ok: false, message: 'Approved items are unavailable at this location.', unavailable: unavailable };
      request.approvedItems.forEach(function (approved) {
        var existing = state.cart.find(function (line) { return line.productId === approved.productId; });
        if (existing) existing.qty += approved.qty; else { var approvedAvailability = itemAvailability(approved.productId, state.locationId); state.cart.push({ productId: approved.productId, qty: approved.qty, approvedPrescriptionId: id, offerId: approvedAvailability.offerId || null, unitPrice: approvedAvailability.price, sellerType: approvedAvailability.sellerType, sellerName: approvedAvailability.sellerName }); }
      });
      save(); return { ok: true };
    },
    /** Store-backed checkout uses the existing CI3 order engine. The browser
     * only sends a cart request; prices, stock reservation, fees and payment
     * verification remain server-owned. */
    createBackendOrder: function (payload) {
      var config = window.PHARMACY_DEMO_CONFIG || {};
      if (!config.storeId || !config.placeOrderUrl) return Promise.resolve({ ok: false, message: 'This standalone demo has no backend order endpoint.' });
      var lines = cartLines();
      var unavailable = lines.filter(function (x) { return !x.availability.available || x.line.qty > x.availability.qty; });
      if (!lines.length) return Promise.resolve({ ok: false, message: 'Your cart is empty.' });
      if (unavailable.length) return Promise.resolve({ ok: false, message: 'Some items are no longer available at this location.', unavailable: unavailable.map(function (x) { return x.product.name; }) });
      var loc = location(state.locationId);
      var form = new FormData();
      form.append('store_id', String(config.storeId));
      form.append('customer_name', payload.customer || '');
      form.append('customer_phone', payload.phone || '');
      form.append('customer_email', payload.email || '');
      form.append('customer_address', payload.address || (payload.fulfilment === 'pickup' ? loc.note : ''));
      form.append('payment_method', payload.paymentMethod === 'online' ? 'paystack' : 'pay_on_delivery');
      form.append('shipping_method', payload.fulfilment === 'pickup' ? 'Pickup' : 'Delivery');
      form.append('warehouse_id', String(loc.warehouseId || 0));
      form.append('cart', JSON.stringify(lines.map(function (x) { return { type: 'product', id: Number(x.line.productId), qty: Number(x.line.qty) }; })));
      form.append('idempotency_key', payload.idempotencyKey || ('pharmacy-' + Date.now()));
      if (config.csrfName && config.csrfHash) form.append(config.csrfName, config.csrfHash);
      return fetch(config.placeOrderUrl, { method: 'POST', body: form, credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } }).then(function (response) {
        return response.json().catch(function () { return { status: false, message: response.status === 403 ? 'Your checkout session expired. Please refresh the store and try again.' : 'The pharmacy order service returned an unreadable response.' }; });
      }).then(function (result) {
        if (result && result.csrf_hash && config.csrfHash) config.csrfHash = result.csrf_hash;
        if (!result || !result.status) return { ok: false, message: (result && result.message) || 'The order could not be created.', unavailable: result && result.unavailable };
        var order = { id: result.order_code, backendOrderId: result.order_id, idempotencyKey: payload.idempotencyKey, items: lines.map(function (x) { return { productId: x.line.productId, qty: x.line.qty, unitPrice: x.unitPrice, sellerType: x.availability.sellerType || 'client', sellerName: x.availability.sellerName || seed.brand.name }; }), locationId: state.locationId, sourceLocationId: loc.sourceLocationId || loc.id, fulfilment: payload.fulfilment, address: payload.address || (payload.fulfilment === 'pickup' ? loc.note : ''), customer: payload.customer, phone: payload.phone, paymentMethod: payload.paymentMethod, paymentStatus: result.payment_required ? 'pending' : 'pending', status: 'confirmed', deliveryStatus: 'confirmed', deliveryFee: payload.fulfilment === 'delivery' ? Number(loc.deliveryFee || 0) : 0, createdAt: new Date().toISOString(), history: [{ status: 'confirmed', label: 'Order confirmed', at: new Date().toISOString().slice(0, 16) }] };
        state.orders.unshift(order); state.lastOrder = order.id; state.cart = []; save();
        return { ok: true, order: clone(order), paymentRequired: !!result.payment_required, payment: result };
      }).catch(function () { return { ok: false, message: 'We could not reach the pharmacy order service. Your cart is still available.' }; });
    },
    createOrder: function (payload) {
      var duplicate = state.orders.find(function (o) { return o.idempotencyKey && o.idempotencyKey === payload.idempotencyKey; });
      if (duplicate) return { ok: true, duplicate: true, order: clone(duplicate) };
      var lines = cartLines();
      var unavailable = lines.filter(function (x) { return !x.availability.available || x.line.qty > x.availability.qty; });
      if (!lines.length) return { ok: false, message: 'Your cart is empty.' };
      if (unavailable.length) return { ok: false, message: 'Some items are no longer available at this location.', unavailable: unavailable.map(function (x) { return x.product.name; }) };
      var loc = location(state.locationId);
      if (loc.pickupOnly && payload.fulfilment !== 'pickup') return { ok: false, message: 'This location is pickup-only. Choose pickup to continue.' };
      var handoverAddress = payload.address || (payload.fulfilment === 'pickup' ? loc.note : 'Demo delivery address, ' + loc.area + ', ' + loc.city);
      var order = { id: nextRef('NEV'), idempotencyKey: payload.idempotencyKey, items: state.cart.map(function (x) { return { productId: x.productId, qty: x.qty, offerId: x.offerId || null, unitPrice: x.unitPrice, sellerType: x.sellerType || 'client', sellerName: x.sellerName || seed.brand.name }; }), locationId: state.locationId, sourceLocationId: loc.sourceLocationId || loc.id, fulfilment: payload.fulfilment, address: handoverAddress, customer: payload.customer || 'Demo customer', phone: payload.phone || '', paymentMethod: payload.paymentMethod, paymentStatus: payload.paymentMethod === 'online' ? 'successful' : 'pending', deliveryFee: payload.fulfilment === 'delivery' ? loc.deliveryFee : 0, status: 'confirmed', createdAt: new Date().toISOString(), history: [{ status: 'confirmed', label: 'Order confirmed', at: new Date().toISOString().slice(0, 16) }] };
      state.orders.unshift(order); state.lastOrder = order.id; state.cart = []; save(); return { ok: true, order: clone(order) };
    },
    orders: function () { return clone(state.orders); },
    order: function (id) { var found = state.orders.find(function (x) { return x.id === id; }); return found ? clone(found) : null; },
    loadHubOrders: function () {
      if (!storeBacked() || !bootstrap.hubOrdersUrl) return Promise.resolve(clone(state.orders));
      return fetch(bootstrap.hubOrdersUrl, { credentials: 'same-origin' }).then(function (response) { return response.json(); }).then(function (result) {
        if (!result || !result.status) {
          state.hubQueue = [];
          save();
          return [];
        }
        var serverOrders = result.orders || [];
        state.hubQueue = serverOrders.map(function (order) {
          var visibleStatus = order.deliveryStatus || order.status;
          if (visibleStatus === 'pending' || visibleStatus === 'paid') visibleStatus = 'confirmed';
          var local = state.orders.find(function (item) { return item.id === order.id; });
          if (local) Object.assign(local, order, { status: visibleStatus });
          return Object.assign({}, local || {}, order, { status: visibleStatus });
        });
        save();
        return clone(state.hubQueue);
      }).catch(function () {
        // A store-backed hub must not fall back to stale local orders when the
        // authoritative server cannot be reached.
        state.hubQueue = [];
        save();
        return [];
      });
    },
    hubOrders: function () { return clone(storeBacked() ? (state.hubQueue || []) : state.orders.filter(function (o) { return o.locationId === 'hub-wuse' || o.locationId === 'branch-ikeja' || o.locationId === 'hub-surulere' || o.backendOrderId; })); },
    updateHubOrder: function (id, action) {
      if (storeBacked() && bootstrap.hubActionUrl) {
        var backendForm = new FormData(); backendForm.append('order_id', String((state.orders.find(function (x) { return x.id === id; }) || {}).backendOrderId || id)); backendForm.append('action', action);
        if (bootstrap.csrfName && bootstrap.csrfHash) backendForm.append(bootstrap.csrfName, bootstrap.csrfHash);
        return formPost(bootstrap.hubActionUrl, backendForm).then(function (result) {
          if (!result.status) return { ok: false, message: result.message || 'Hub update failed.' };
          var updated = result.order;
          var local = state.orders.find(function (item) { return item.id === updated.id; });
          if (local) Object.assign(local, updated);
          var queueIndex = (state.hubQueue || []).findIndex(function (item) { return item.id === updated.id; });
          if (queueIndex === -1) {
            state.hubQueue = state.hubQueue || [];
            state.hubQueue.unshift(updated);
          } else {
            state.hubQueue[queueIndex] = Object.assign({}, state.hubQueue[queueIndex], updated);
          }
          save();
          return { ok: true, order: clone(state.hubQueue.find(function (item) { return item.id === updated.id; }) || updated) };
        });
      }
      var order = state.orders.find(function (x) { return x.id === id; });
      if (!order) return { ok: false, message: 'Order not found.' };
      var map = { accept: ['accepted', 'Hub accepted'], unable: ['reassignment_requested', 'Unable to fulfil · reassignment requested'], prepare: ['preparing', 'Preparing order'], ready: ['ready', 'Ready for pickup'], dispatch: ['out_for_delivery', 'Out for delivery'], handover: [order.fulfilment === 'pickup' ? 'collected' : 'delivered', order.fulfilment === 'pickup' ? 'Collected by customer' : 'Delivered'] };
      if (!map[action]) return { ok: false, message: 'Unknown hub action.' };
      order.status = map[action][0]; order.history = order.history || []; order.history.push({ status: order.status, label: map[action][1], at: new Date().toISOString().slice(0, 16) }); save(); return { ok: true, order: clone(order) };
    },
    refreshOrder: function (id) {
      var order = state.orders.find(function (x) { return x.id === id; });
      if (!storeBacked() || !bootstrap.orderStatusUrl || !order || !order.phone) return Promise.resolve(order ? clone(order) : null);
      var url = bootstrap.orderStatusUrl + '?order_code=' + encodeURIComponent(order.id) + '&phone=' + encodeURIComponent(order.phone);
      return fetch(url, { credentials: 'same-origin' }).then(function (response) { return response.json(); }).then(function (result) {
        if (result.status) return mergeOrder(result.order);
        state.orders = state.orders.filter(function (item) { return item.id !== id; });
        save();
        return null;
      }).catch(function () {
        state.orders = state.orders.filter(function (item) { return item.id !== id; });
        save();
        return null;
      });
    },
    requestCancellation: function (id) {
      var order = state.orders.find(function (x) { return x.id === id; });
      if (!order) return { ok: false, message: 'Order not found.' };
      if (['delivered', 'collected', 'cancelled'].indexOf(order.status) !== -1) return { ok: false, message: 'This order can no longer be cancelled.' };
      order.status = 'cancellation_requested'; order.history = order.history || []; order.history.push({ status: order.status, label: 'Cancellation requested', at: new Date().toISOString().slice(0, 16) }); save(); return { ok: true, order: clone(order) };
    },
    retryPayment: function (id) {
      var order = state.orders.find(function (x) { return x.id === id; });
      if (!order) return { ok: false, message: 'Order not found.' };
      order.paymentStatus = 'successful'; order.history = order.history || []; order.history.push({ status: 'payment_successful', label: 'Payment successful', at: new Date().toISOString().slice(0, 16) }); save(); return { ok: true, order: clone(order) };
    },
    reset: function () { localStorage.removeItem(KEY); window.location.reload(); }
  };
})(window);
