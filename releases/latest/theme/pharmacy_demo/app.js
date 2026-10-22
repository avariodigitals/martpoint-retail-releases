(function (window, document) {
  'use strict';

  var service = window.PharmacyDemoService;
  var paymentConfig = (window.PHARMACY_DEMO_CONFIG || {}).paymentMethods || {};
  var ui = { search: '', category: 'all', checkout: { fulfilment: 'delivery', paymentMethod: paymentConfig.online === false ? 'cod' : 'online', customer: '', phone: '', email: '', address: '', notes: '' }, toast: '', toastTimer: null, hubLoaded: false, refreshedOrder: '', refreshedPrescription: '' };

  function esc(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (char) {
      return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' })[char];
    });
  }
  function money(value) { return '₦' + Number(value || 0).toLocaleString('en-NG'); }
  function icon(name) {
    var paths = {
      bag: '<path d="M5 8h14l-1 11H6L5 8Z"/><path d="M8 8a4 4 0 0 1 8 0"/>',
      arrow: '<path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>',
      pin: '<path d="M20 10c0 5-8 11-8 11S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/>',
      search: '<circle cx="11" cy="11" r="6.5"/><path d="m16 16 5 5"/>',
      menu: '<path d="M4 7h16M4 12h16M4 17h16"/>',
      close: '<path d="m6 6 12 12M18 6 6 18"/>',
      plus: '<path d="M12 5v14M5 12h14"/>',
      minus: '<path d="M5 12h14"/>',
      heart: '<path d="M20 8.5c0 5-8 10-8 10s-8-5-8-10a4.5 4.5 0 0 1 8-2.7 4.5 4.5 0 0 1 8 2.7Z"/>',
      pill: '<path d="m8 8 8 8M7 17a4.2 4.2 0 0 1 0-6l4-4a4.2 4.2 0 0 1 6 6l-4 4a4.2 4.2 0 0 1-6 0Z"/>',
      spark: '<path d="m12 3 1.7 5.3L19 10l-5.3 1.7L12 17l-1.7-5.3L5 10l5.3-1.7L12 3ZM19 16l.7 2.3L22 19l-2.3.7L19 22l-.7-2.3L16 19l2.3-.7L19 16Z"/>',
      drop: '<path d="M12 3s6 6.1 6 11a6 6 0 0 1-12 0c0-4.9 6-11 6-11Z"/>',
      device: '<rect x="6" y="4" width="12" height="16" rx="2"/><path d="M9 8h6M9 12h6M10 16h4"/>',
      truck: '<path d="M3 6h11v10H3zM14 10h4l3 3v3h-7z"/><circle cx="7" cy="18" r="2"/><circle cx="18" cy="18" r="2"/>',
      clock: '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
      check: '<path d="m5 12 4 4L19 6"/>',
      file: '<path d="M6 3h8l4 4v14H6zM14 3v5h5M9 13h6M9 17h6"/>',
      refresh: '<path d="M20 11a8 8 0 0 0-14-4L4 9"/><path d="M4 4v5h5M4 13a8 8 0 0 0 14 4l2-2"/><path d="M20 20v-5h-5"/>',
      external: '<path d="M14 4h6v6M20 4l-9 9"/><path d="M18 13v6H4V5h6"/>',
      pharmacy: '<path d="M12 3v18M3 12h18"/><circle cx="12" cy="12" r="9"/>',
      box: '<path d="m4 7 8-4 8 4-8 4-8-4Z"/><path d="M4 7v10l8 4 8-4V7M12 11v10"/>'
    };
    return '<svg class="icon" aria-hidden="true" viewBox="0 0 24 24">' + (paths[name] || paths.pharmacy) + '</svg>';
  }
  function route() {
    var raw = (window.location.hash || '#home').slice(1);
    var parts = raw.split('/');
    return { page: parts[0] || 'home', id: parts[1] || null };
  }
  function setToast(message) {
    ui.toast = message;
    if (ui.toastTimer) window.clearTimeout(ui.toastTimer);
    ui.toastTimer = window.setTimeout(function () { ui.toast = ''; render(); }, 3200);
  }
  function go(target) { window.location.hash = target; }
  function selectedLocation() { return service.selectedLocation(); }
  function basePath() { return (window.PHARMACY_DEMO_CONFIG || {}).basePath || '/pharmacy-demo'; }
  function locationName(id) { var loc = service.getLocation(id); return loc ? loc.name : 'Selected location'; }
  function productCard(product) {
    var availability = service.availability(product.id, selectedLocation().id);
    var partnerOffers = service.partnerOffers(product.id, selectedLocation().id);
    var sellerLine = availability.sellerType === 'partner' ? '<span class="product-source">Partner offer · ' + esc(availability.sellerName) + '</span>' : '<span class="product-source">' + esc(availability.sellerName || service.brand.name) + '</span>';
    var visual = product.image ? '<img src="' + esc(product.image) + '" alt="" loading="lazy">' : '';
    return '<article class="product-card">' +
      '<a href="#product/' + esc(product.id) + '" aria-label="View ' + esc(product.name) + '">' +
        '<div class="product-art ' + esc(product.colour) + '">' + visual + (product.oldPrice ? '<span class="sale-badge">Deal</span>' : '') + '</div>' +
      '</a>' +
      '<div class="product-meta"><span class="product-category">' + esc(categoryLabel(product.category)) + '</span>' +
      '<a class="product-name" href="#product/' + esc(product.id) + '">' + esc(product.name) + '</a>' +
      '<span class="product-spec">' + esc(product.unit) + ' · ' + (availability.available ? esc(availability.qty + ' · ' + availability.reason) : 'Unavailable here') + '</span>' + sellerLine +
      (partnerOffers.length && availability.sellerType !== 'partner' ? '<span class="product-spec">Partner offer available at ' + money(partnerOffers[0].price) + '</span>' : '') +
      '<div class="product-bottom"><span class="price">' + money(availability.price || product.price) + (product.oldPrice ? '<span class="old-price">' + money(product.oldPrice) + '</span>' : '') + '</span>' +
      (product.prescription ? '<a class="add-button" href="#prescription">' + icon('file') + ' Prescription</a>' : '<button class="add-button" type="button" data-add="' + esc(product.id) + '" data-offer="' + esc(availability.offerId || '') + '">' + icon('plus') + ' Add</button>') +
      '</div></div></article>';
  }
  function categoryLabel(id) {
    var found = service.categories.find(function (category) { return category.id === id; });
    return found ? found.label : id;
  }
  function header(isHub) {
    var state = service.getState();
    var cartQty = state.cart.reduce(function (sum, line) { return sum + Number(line.qty || 0); }, 0);
    var currentLocation = service.selectedLocation();
    var connected = (window.PHARMACY_DEMO_CONFIG || {}).storeId;
    return '<div class="demo-ribbon"><strong>' + (connected ? 'MartPoint-connected pharmacy demo' : 'Interactive demo') + '</strong> · ' + (connected ? 'catalogue, stock, prescriptions and fulfilment updates use the pharmacy backend · role switching is a demo access shortcut' : 'fictional Nigerian locations and data · payments, prescription review and role switching are simulated') + '</div>' +
      '<header class="site-header"><div class="header-inner">' +
      '<a class="brand" href="#home"><span class="brand-mark">' + icon('pharmacy') + '</span><span><span class="brand-name">' + esc(service.brand.name) + '</span><span class="brand-sub">' + esc(service.brand.strap) + '</span></span></a>' +
      '<nav class="main-nav" aria-label="Main navigation"><a href="#shop">Shop</a><a href="#locations">Locations</a><a href="#prescription">Prescription</a><a href="#track">Track order</a><a href="#journal">Journal</a>' + (isHub ? '<a href="#hub">Partner hub</a>' : '<a href="' + esc(basePath() + '/hub') + '">Partner hub</a>') + '</nav>' +
      '<div class="header-actions">' + (!isHub ? '<a class="header-location" href="#locations" aria-label="Change fulfilment location"><span>' + icon('pin') + '</span><span>' + esc(currentLocation.area) + '</span></a>' : '') + '<form class="search-box" data-search-form><label class="sr-only" for="store-search">Search medicines and wellness</label>' + icon('search') + '<input id="store-search" name="q" value="' + esc(ui.search) + '" placeholder="Search products" autocomplete="off"></form>' +
      '<a class="icon-button" href="#cart" aria-label="Cart">' + icon('bag') + '<span class="count">' + cartQty + '</span></a><button class="icon-button mobile-toggle" type="button" data-mobile-nav aria-label="Open menu">' + icon('menu') + '</button></div>' +
      '</div></header>';
  }
  function footer() {
    return '<footer class="app-footer"><div><strong>' + esc(service.brand.name) + '</strong><span> ' + esc(service.brand.strap) + '.</span></div><div>Frontend workflow demo · Backend, stock, prescription approval and payment verification remain server-owned.</div></footer>';
  }
  function categoryTiles() {
    return service.categories.map(function (category) { return '<a class="category-tile" href="#shop/' + esc(category.id) + '"><span class="tile-icon">' + icon(category.icon) + '</span><span><strong>' + esc(category.label) + '</strong><small>Browse collection ' + icon('arrow') + '</small></span></a>'; }).join('');
  }
  function home() {
    var products = service.products.slice(0, 4);
    var loc = selectedLocation();
    return '<main class="app-main"><section class="hero"><div class="hero-stage"><div class="hero-copy"><div class="eyebrow">Care that fits your day · Nigeria</div><h1>Pharmacy</h1><p class="hero-lead">Order everyday medicines, wellness essentials and personal care with a clear handoff from pharmacy to your door.</p><div class="hero-actions"><a class="button button-primary" href="#shop">Shop essentials ' + icon('arrow') + '</a><a class="button button-light" href="#prescription">' + icon('file') + ' Send a prescription</a></div></div><div class="hero-visual"><div class="hero-bottle"></div><div class="hero-card"><strong>Upload prescription</strong><p>Keep prescription requests separate from ordinary shopping.</p><a href="#prescription">Start a request ' + icon('arrow') + '</a></div></div></div></section>' +
      '<div class="location-strip"><strong>' + icon('pin') + ' Delivering from ' + esc(loc.name) + '</strong><span>' + esc(loc.city + ' · ' + loc.area + ' · ' + loc.hours) + '</span> <a class="text-link" href="#locations">Change location</a></div>' +
      '<div class="trust-row"><div class="trust-item"><span class="trust-icon">' + icon('pin') + '</span>Choose your area</div><div class="trust-item"><span class="trust-icon">' + icon('truck') + '</span>Delivery or pickup</div><div class="trust-item"><span class="trust-icon">' + icon('file') + '</span>Prescription review</div><div class="trust-item"><span class="trust-icon">' + icon('check') + '</span>Clear order tracking</div></div>' +
      '<section class="section"><div class="section-heading"><div><div class="eyebrow">Browse by need</div><h2>Care, made<br>easy to find.</h2></div><p>Explore a focused catalogue across medicines, vitamins, wellness, skincare, first aid and home health devices.</p></div><div class="category-row">' + categoryTiles() + '</div></section>' +
      '<section class="section"><div class="section-heading"><div><div class="eyebrow">Selected for your routine</div><h2>Everyday essentials.</h2></div><a class="text-link" href="#shop">See all products ' + icon('arrow') + '</a></div><div class="product-grid">' + products.map(productCard).join('') + '</div></section>' +
      '<section class="info-band"><div><div class="eyebrow">One brand, local fulfilment</div><h2>Your nearest<br>care point.</h2><p>Choose a pharmacy branch or partner hub before checkout. Pickup-only hubs can receive prepared orders without holding their own stock.</p></div><div class="info-points"><div class="info-point">' + icon('pin') + ' See opening hours and delivery coverage</div><div class="info-point">' + icon('box') + ' Keep one clear fulfilment location per order</div><div class="info-point">' + icon('refresh') + ' Follow the order from confirmed to handover</div><a class="button button-primary" href="#locations">Find a location ' + icon('arrow') + '</a></div></section>' +
      '</main>';
  }
  function shop(categoryFromHash) {
    if (categoryFromHash && service.categories.some(function (category) { return category.id === categoryFromHash; })) ui.category = categoryFromHash;
    var query = ui.search.toLowerCase().trim();
    var products = service.products.filter(function (product) { return (ui.category === 'all' || product.category === ui.category) && (!query || (product.name + ' ' + product.description + ' ' + product.category).toLowerCase().indexOf(query) !== -1); });
    return '<main class="app-main"><div class="page-top"><div><div class="eyebrow">Catalogue</div><h1>Shop health<br>with clarity.</h1></div><p>Prices and availability shown for <strong>' + esc(selectedLocation().name) + '</strong>. <a class="text-link" href="#locations">Change location</a> to recheck stock and delivery options.</p></div>' +
      '<div class="filter-row"><button class="filter ' + (ui.category === 'all' ? 'active' : '') + '" data-category="all">All products</button>' + service.categories.map(function (category) { return '<button class="filter ' + (ui.category === category.id ? 'active' : '') + '" data-category="' + esc(category.id) + '">' + esc(category.label) + '</button>'; }).join('') + '</div>' +
      (products.length ? '<div class="product-grid">' + products.map(productCard).join('') + '</div>' : '<div class="empty-state"><h2>No products found</h2><p>Try another search or browse all products.</p><button class="button button-outline" data-clear-search>Clear filters</button></div>') +
      '<div class="notice">Prescription medicines are not added directly to cart. Send a separate request so a pharmacist can review it before approved items are offered for checkout.</div></main>';
  }
  function productDetail(id) {
    var p = service.getProduct(id);
    if (!p) return '<main class="app-main"><div class="empty-state"><h2>Product not found</h2><a class="button button-primary" href="#shop">Back to shop</a></div></main>';
    var availability = service.availability(p.id, selectedLocation().id);
    var detailVisual = p.image ? '<img src="' + esc(p.image) + '" alt="" loading="lazy">' : '';
    return '<main class="app-main"><div class="page-top"><a class="text-link" href="#shop">' + icon('arrow') + ' Back to shop</a></div><section class="detail-layout"><div class="detail-art product-art ' + esc(p.colour) + '">' + detailVisual + '</div><div class="detail-copy"><div class="eyebrow">' + esc(categoryLabel(p.category)) + '</div><h1>' + esc(p.name) + '</h1><div class="price">' + money(availability.price || p.price) + (p.oldPrice ? '<span class="old-price">' + money(p.oldPrice) + '</span>' : '') + '</div><p class="detail-description">' + esc(p.description) + '</p>' +
      '<div class="detail-line"><span>Strength / format</span><span>' + esc(p.strength + ' · ' + p.form) + '</span></div><div class="detail-line"><span>Pack / selling unit</span><span>' + esc(p.pack + ' · ' + p.unit) + '</span></div><div class="detail-line"><span>Availability</span><span>' + (availability.available ? esc(availability.qty + ' available at ' + selectedLocation().area) : 'Unavailable at this location') + '</span></div><div class="detail-line"><span>Catalogue source</span><span>' + esc(availability.sellerType === 'partner' ? 'Approved partner offer · ' + availability.sellerName : service.brand.name + ' catalogue') + '</span></div>' +
      (p.prescription ? '<div class="notice">' + icon('file') + ' Prescription review required. This request is separate from ordinary shopping and is not an instant approval.</div><a class="button button-primary" href="#prescription">Send prescription request ' + icon('arrow') + '</a>' : '<div class="quantity"><button type="button" data-detail-qty="-1" aria-label="Decrease quantity">' + icon('minus') + '</button><span data-detail-qty-value>1</span><button type="button" data-detail-qty="1" aria-label="Increase quantity">' + icon('plus') + '</button></div><br><button class="button button-primary" data-detail-add="' + esc(p.id) + '" data-detail-offer="' + esc(availability.offerId || '') + '">' + icon('bag') + ' Add to cart</button>') +
      '<div class="location-strip"><strong>' + icon('pin') + ' Fulfilled from ' + esc(selectedLocation().name) + '</strong><span>' + esc(selectedLocation().address + ' · ' + selectedLocation().hours) + '</span> <a class="text-link" href="#locations">Change</a></div></div></section></main>';
  }
  function summary(withCheckout) {
    var loc = selectedLocation();
    var subtotal = service.getSubtotal();
    var fee = ui.checkout.fulfilment === 'delivery' ? loc.deliveryFee : 0;
    return '<aside class="summary"><h2>Order summary</h2><div class="summary-row"><span>Subtotal</span><strong>' + money(subtotal) + '</strong></div><div class="summary-row"><span>' + (ui.checkout.fulfilment === 'delivery' ? 'Delivery fee' : 'Pickup') + '</span><strong>' + (fee ? money(fee) : 'Free') + '</strong></div><div class="summary-row total"><span>Total</span><strong>' + money(subtotal + fee) + '</strong></div>' + (withCheckout ? '<button class="button button-primary" data-route="checkout" type="button">Continue to checkout ' + icon('arrow') + '</button>' : '') + '</aside>';
  }
  function cart() {
    var lines = service.getCart();
    var unavailable = lines.filter(function (line) { return !line.availability.available || line.line.qty > line.availability.qty; });
    return '<main class="app-main"><div class="page-top"><div><div class="eyebrow">Your selection</div><h1>Your cart.</h1></div><p>One order uses one fulfilment location. If you switch location, stock is checked again before checkout.</p></div>' +
      '<div class="cart-layout"><div>' + (lines.length ? '<div class="location-strip"><strong>' + icon('pin') + ' ' + esc(selectedLocation().name) + '</strong><span>' + esc(selectedLocation().city + ' · ' + selectedLocation().hours) + '</span> <a class="text-link" href="#locations">Change location</a></div><div class="line-list">' + lines.map(function (line) { return '<div class="line-item"><div class="line-art product-art ' + esc(line.product.colour) + '">' + (line.product.image ? '<img src="' + esc(line.product.image) + '" alt="" loading="lazy">' : '') + '</div><div><h3>' + esc(line.product.name) + '</h3><p>' + esc(line.product.unit) + ' · ' + esc(line.availability.sellerType === 'partner' ? 'Partner offer · ' + line.availability.sellerName : service.brand.name + ' catalogue') + '</p>' + (line.availability.available ? '' : '<p class="status danger">Unavailable here</p>') + '</div><div class="line-actions"><div class="quantity"><button type="button" data-qty="' + esc(line.product.id) + '" data-delta="-1">' + icon('minus') + '</button><span>' + line.line.qty + '</span><button type="button" data-qty="' + esc(line.product.id) + '" data-delta="1">' + icon('plus') + '</button></div><strong>' + money(line.unitPrice * line.line.qty) + '</strong><button class="remove-button" type="button" data-remove="' + esc(line.product.id) + '" aria-label="Remove ' + esc(line.product.name) + '">' + icon('close') + '</button></div></div>'; }).join('') + '</div>' + (unavailable.length ? '<div class="notice">' + unavailable.map(function (line) { return esc(line.product.name); }).join(', ') + ' must be removed or resolved at this location before checkout.</div>' : '') : '<div class="empty-state"><h2>Your cart is quiet.</h2><p>Add a few essentials and they will stay here while you browse.</p><a class="button button-primary" href="#shop">Browse products ' + icon('arrow') + '</a></div>') + '</div>' + (lines.length ? summary(!unavailable.length) : '') + '</div></main>';
  }
  function checkout() {
    var lines = service.getCart();
    if (!lines.length) return '<main class="app-main"><div class="empty-state"><h2>Nothing to check out</h2><p>Your cart is empty.</p><a class="button button-primary" href="#shop">Browse products</a></div></main>';
    var loc = selectedLocation();
    if (loc.pickupOnly) ui.checkout.fulfilment = 'pickup';
    var unavailable = lines.filter(function (line) { return !line.availability.available || line.line.qty > line.availability.qty; });
    return '<main class="app-main"><div class="page-top"><div><div class="eyebrow">Demo checkout</div><h1>Complete your<br>order.</h1></div><p>Payment is simulated. The backend must own final price, stock reservation, payment verification and refunds.</p></div>' +
      '<div class="checkout-layout"><form class="checkout-form" data-checkout-form><div class="location-strip"><strong>' + icon('pin') + ' ' + esc(loc.name) + '</strong><span>' + esc(loc.address + ' · ' + loc.hours) + '</span> <a class="text-link" href="#locations">Change location</a></div>' + (unavailable.length ? '<div class="notice">Checkout is paused because ' + unavailable.map(function (line) { return esc(line.product.name); }).join(', ') + ' is unavailable at ' + esc(loc.name) + '. <a href="#locations">Choose another location.</a></div>' : '') +
      '<section class="form-section"><h2>How should we fulfil it?</h2><div class="choice-row">' + (loc.pickupOnly ? '' : '<button class="choice ' + (ui.checkout.fulfilment === 'delivery' ? 'active' : '') + '" type="button" data-choice="fulfilment" data-value="delivery"><strong>' + icon('truck') + ' Delivery</strong><small>' + money(loc.deliveryFee) + ' · eligible areas only</small></button>') + '<button class="choice ' + (ui.checkout.fulfilment === 'pickup' ? 'active' : '') + '" type="button" data-choice="fulfilment" data-value="pickup"><strong>' + icon('pin') + ' Pickup</strong><small>' + esc(loc.name) + ' · ' + esc(loc.hours) + '</small></button></div>' + (ui.checkout.fulfilment === 'pickup' ? '<div class="location-strip"><strong>Collection instructions</strong><span>' + esc(loc.note) + '</span></div>' : '') + '</section>' +
      '<section class="form-section"><h2>Customer details</h2><div class="field-grid"><div class="field"><label for="checkout-name">Full name</label><input id="checkout-name" name="customer" required value="' + esc(ui.checkout.customer) + '" placeholder="Demo customer"></div><div class="field"><label for="checkout-phone">Phone</label><input id="checkout-phone" name="phone" required value="' + esc(ui.checkout.phone) + '" placeholder="0800 000 0000"></div><div class="field"><label for="checkout-email">Email</label><input id="checkout-email" name="email" type="email" value="' + esc(ui.checkout.email) + '" placeholder="you@example.com"></div><div class="field"><label for="checkout-address">' + (ui.checkout.fulfilment === 'delivery' ? 'Delivery address' : 'Pickup note') + '</label><input id="checkout-address" name="address" value="' + esc(ui.checkout.address) + '" ' + (ui.checkout.fulfilment === 'delivery' ? 'required' : '') + ' placeholder="' + (ui.checkout.fulfilment === 'delivery' ? 'Street, area, city' : 'Optional handover note') + '"></div><div class="field full"><label for="checkout-notes">Order note</label><textarea id="checkout-notes" name="notes" rows="3" placeholder="Optional note for the pharmacy team">' + esc(ui.checkout.notes) + '</textarea></div></div></section>' +
      '<section class="form-section"><h2>Payment method</h2><div class="choice-row">' + (((window.PHARMACY_DEMO_CONFIG || {}).paymentMethods || {}).online !== false ? '<button class="choice ' + (ui.checkout.paymentMethod === 'online' ? 'active' : '') + '" type="button" data-choice="paymentMethod" data-value="online"><strong>Online payment</strong><small>Use the configured payment gateway</small></button>' : '') + '<button class="choice ' + (ui.checkout.paymentMethod === 'cod' ? 'active' : '') + '" type="button" data-choice="paymentMethod" data-value="cod"><strong>Pay on ' + (ui.checkout.fulfilment === 'pickup' ? 'collection' : 'delivery') + '</strong><small>Payment stays pending until handover</small></button></div></section>' +
      '<button class="button button-primary" type="submit" data-checkout-submit ' + (unavailable.length ? 'disabled' : '') + '>Confirm order ' + icon('arrow') + '</button><p class="product-spec">Prices, fees, stock reservation and payment verification are handled by MartPoint. Duplicate submissions use an idempotency key.</p></form>' + summary(false) + '</div></main>';
  }
  function prescription(id) {
    var state = service.getState();
    var request = id ? state.prescriptions.find(function (item) { return String(item.id) === String(id); }) : state.prescriptions[0];
    if (request) return prescriptionStatus(request);
    return '<main class="app-main"><div class="page-top"><div><div class="eyebrow">Separate pharmacy review</div><h1>Send a<br>prescription.</h1></div><p>Use fictional data for this demo. A pharmacy team reviews the request before any medicine can enter checkout.</p></div><div class="checkout-layout"><form class="checkout-form" data-prescription-form><section class="form-section"><h2>Request details</h2><div class="field-grid"><div class="field"><label for="rx-name">Patient name</label><input id="rx-name" name="customer" required placeholder="Demo patient"></div><div class="field"><label for="rx-phone">Phone</label><input id="rx-phone" name="phone" required placeholder="0800 000 0000"></div><div class="field"><label for="rx-email">Email</label><input id="rx-email" name="email" type="email" placeholder="you@example.com"></div><div class="field"><label for="rx-file">Prescription file</label><input id="rx-file" name="file" type="file" accept=".pdf,.jpg,.jpeg,.png" required><small class="product-spec">Fictional demo file only.</small></div><div class="field full"><label for="rx-note">Short note</label><textarea id="rx-note" name="note" rows="4" placeholder="Example: Please review this fictional request"></textarea></div></div></section><section class="form-section"><h2>Review location</h2><div class="location-strip"><strong>' + icon('pin') + ' ' + esc(selectedLocation().name) + '</strong><span>' + esc(selectedLocation().address + ' · ' + selectedLocation().hours) + '</span> <a class="text-link" href="#locations">Change location</a></div><p class="product-spec">Partner hubs cannot approve prescriptions. A pharmacy team must review the request first.</p></section><button class="button button-primary" type="submit">Submit for pharmacy review ' + icon('arrow') + '</button></form><aside class="summary"><h2>What happens next</h2><div class="summary-row"><span>1</span><strong>Awaiting pharmacy review</strong></div><div class="summary-row"><span>2</span><strong>Approved items and price</strong></div><div class="summary-row"><span>3</span><strong>Your confirmation</strong></div><div class="summary-row"><span>4</span><strong>Checkout and fulfilment</strong></div></aside></div></main>';
  }
  function prescriptionStatus(request) {
    var approved = request.status === 'approved';
    var item = approved && request.approvedItems && request.approvedItems[0] ? service.getProduct(request.approvedItems[0].productId) : null;
    return '<main class="app-main"><div class="success-panel"><div class="eyebrow">Prescription request ' + esc(request.reference || request.id) + '</div><h1>' + (approved ? 'Review complete.' : 'Request received.') + '</h1><p>' + (approved ? 'The pharmacy review is complete. Confirm the approved item to move this request into the ordinary checkout flow.' : 'This request is awaiting pharmacy review. A partner hub cannot approve it.') + '</p><span class="status ' + (approved ? '' : 'pending') + '">' + (approved ? 'Approved for customer confirmation' : 'Awaiting pharmacy review') + '</span></div><div class="tracking"><div><div class="form-section"><h2>Request details</h2><div class="detail-line"><span>Filename</span><span>' + esc(request.fileName) + '</span></div><div class="detail-line"><span>Review location</span><span>' + esc(locationName(request.locationId)) + '</span></div><div class="detail-line"><span>Note</span><span>' + esc(request.note || 'No note added') + '</span></div></div>' + (approved && item ? '<div class="location-strip"><strong>' + icon('check') + ' Approved item</strong><span>' + esc(item.name + ' · ' + item.unit) + ' · ' + money(request.approvedTotal) + '</span></div><button class="button button-primary" data-prescription-add="' + esc(request.id) + '">Confirm item and continue to checkout ' + icon('arrow') + '</button>' : '<button class="button button-outline" data-prescription-approve="' + esc(request.id) + '">Review prescription</button>') + '</div><div class="timeline"><div class="timeline-item done"><strong>Submitted</strong><span>Recorded in the pharmacy queue</span></div><div class="timeline-item ' + (approved ? 'done' : '') + '"><strong>Pharmacy review</strong><span>' + (approved ? 'Approved by the pharmacy team' : 'Awaiting pharmacy review') + '</span></div><div class="timeline-item ' + (approved ? 'done' : '') + '"><strong>Customer confirmation</strong><span>' + (approved ? 'Ready for your confirmation' : 'Next step after approval') + '</span></div><div class="timeline-item"><strong>Checkout</strong><span>Only after confirmation</span></div></div></div></main>';
  }
  function locations() {
    var current = selectedLocation();
    return '<main class="app-main"><div class="page-top"><div><div class="eyebrow">Delivery and collection</div><h1>Choose your<br>care point.</h1></div><p>Browse freely, then choose where your order should be prepared. A pickup-only hub receives prepared orders and does not own stock.</p></div><div class="location-grid">' + service.locations.map(function (loc) { var chosen = loc.id === current.id; return '<article class="location-card"><div class="location-kind">' + (loc.kind === 'partner' ? 'Partner fulfilment hub' : loc.kind === 'pickup_hub' ? 'Pickup-only collection hub' : 'Pharmacy branch') + '</div><h3>' + esc(loc.name) + '</h3><p>' + esc(loc.address) + '</p><p><strong>' + icon('clock') + ' ' + esc(loc.hours) + '</strong></p><p>' + esc(loc.coverage) + '</p><p>' + (loc.deliveryFee ? money(loc.deliveryFee) + ' delivery · ' : 'Pickup only · ') + (loc.pickup ? 'pickup available' : 'delivery only') + '</p>' + (loc.pickupOnly ? '<div class="notice">' + esc(loc.note) + '</div>' : '') + '<button class="button ' + (chosen ? 'button-outline' : 'button-primary') + '" data-location="' + esc(loc.id) + '" ' + (chosen ? 'disabled' : '') + '>' + (chosen ? 'Current location' : 'Choose this location') + ' ' + icon('arrow') + '</button></article>'; }).join('') + '</div></main>';
  }
  function journal() {
    var articles = [
      { label: 'How it works', title: 'A calmer route from browse to handover.', body: 'See how a single fulfilment location keeps delivery, pickup and customer tracking easy to understand.' },
      { label: 'Locations', title: 'Delivery or pickup: choose what fits.', body: 'Compare opening hours, delivery coverage and collection notes before you place a demo order.' },
      { label: 'Prescription requests', title: 'Why prescription requests stay separate.', body: 'A pharmacy team reviews a request before approved items and pricing move into checkout.' }
    ];
    return '<main class="app-main"><div class="page-top"><div><div class="eyebrow">' + esc(service.brand.name) + ' journal</div><h1>Useful, clear<br>care notes.</h1></div><p>Short product and service notes for this pharmacy experience. This demo does not provide medical advice.</p></div><div class="location-grid">' + articles.map(function (article) { return '<article class="location-card"><div class="location-kind">' + esc(article.label) + '</div><h3>' + esc(article.title) + '</h3><p>' + esc(article.body) + '</p><a class="text-link" href="#' + (article.label === 'Locations' ? 'locations' : article.label === 'Prescription requests' ? 'prescription' : 'shop') + '">Explore the flow ' + icon('arrow') + '</a></article>'; }).join('') + '</div></main>';
  }
  function tracking(id) {
    var orders = service.orders();
    var order = id ? service.order(id) : (orders[0] || null);
    if (!order) return '<main class="app-main"><div class="empty-state"><h2>No demo orders yet</h2><p>Complete checkout or use the seeded order to see tracking.</p><a class="button button-primary" href="#shop">Start shopping</a></div></main>';
    var loc = service.getLocation(order.locationId);
    var statusOrder = ['confirmed', 'accepted', 'preparing', 'ready', 'out_for_delivery', 'collected', 'delivered'];
    var currentIndex = statusOrder.indexOf(order.status);
    if (currentIndex < 0) currentIndex = order.status === 'cancellation_requested' ? 1 : 0;
    var labels = { confirmed: 'Order confirmed', accepted: 'Hub accepted', preparing: 'Preparing order', ready: order.fulfilment === 'pickup' ? 'Ready for pickup' : 'Ready for dispatch', out_for_delivery: 'Out for delivery', collected: 'Collected by customer', delivered: 'Delivered' };
    return '<main class="app-main"><div class="page-top"><div><div class="eyebrow">Order tracking</div><h1>' + esc(order.id) + '</h1></div><p>Follow the handoff from the selected fulfilment location. Status updates in the partner hub appear here too.</p></div><div class="success-panel"><div class="eyebrow">Next step</div><h1>' + esc(labels[order.status] || order.status.replace(/_/g, ' ')) + '</h1><p>' + (order.fulfilment === 'pickup' ? 'Wait for the hub to confirm the order is ready, then bring your reference for collection.' : 'The fulfilment team will update this order before it leaves for delivery.') + '</p><span class="status">Payment: ' + esc(order.paymentStatus) + '</span></div><div class="tracking"><div><section class="form-section"><h2>Order items</h2><div class="line-list">' + order.items.map(function (line) { var p = service.getProduct(line.productId); return '<div class="line-item"><div class="line-art product-art ' + esc(p.colour) + '"></div><div><h3>' + esc(p.name) + '</h3><p>' + esc(p.unit) + ' · ' + esc(line.sellerName || service.brand.name + ' catalogue') + ' · Qty ' + line.qty + '</p></div><strong>' + money(Number(line.unitPrice || p.price) * line.qty) + '</strong></div>'; }).join('') + '</div><div class="detail-line"><span>Fulfilment</span><span>' + esc(order.fulfilment === 'pickup' ? 'Pickup' : 'Delivery') + ' · ' + esc(loc.name) + '</span></div><div class="detail-line"><span>Handover details</span><span>' + esc(order.address || loc.note) + '</span></div><div class="detail-line"><span>Total</span><span>' + money(service.getOrderTotal(order)) + '</span></div></section>' + (order.paymentStatus === 'pending' ? '<div class="notice">Payment is pending. In the demo, retrying marks it successful; production must verify payment server-side.</div><button class="button button-primary" data-retry-payment="' + esc(order.id) + '">' + icon('refresh') + ' Retry demo payment</button>' : '') + (['delivered', 'collected', 'cancelled', 'cancellation_requested'].indexOf(order.status) === -1 ? '<button class="button button-danger" data-request-cancel="' + esc(order.id) + '">Request cancellation</button>' : '') + '</div><div class="timeline">' + (order.history || []).map(function (item) { return '<div class="timeline-item done"><strong>' + esc(item.label) + '</strong><span>' + esc(item.at || '') + '</span></div>'; }).join('') + '</div></div></main>';
  }
  function hubActionButtons(order) {
    var actions = [];
    if (order.status === 'confirmed') actions.push(['accept', 'Accept order', 'button-primary']);
    if (['confirmed', 'accepted'].indexOf(order.status) !== -1) actions.push(['unable', 'Unable to fulfil', 'button-danger']);
    if (order.status === 'accepted') actions.push(['prepare', 'Start preparing', 'button-primary']);
    if (order.status === 'preparing') actions.push(['ready', 'Mark ready', 'button-primary']);
    if (order.status === 'ready' && order.fulfilment === 'delivery') actions.push(['dispatch', 'Dispatch', 'button-primary']);
    if ((order.status === 'ready' && order.fulfilment === 'pickup') || order.status === 'out_for_delivery') actions.push(['handover', order.fulfilment === 'pickup' ? 'Confirm handover' : 'Confirm delivery', 'button-primary']);
    return actions.map(function (action) { return '<button class="button ' + action[2] + '" type="button" data-hub-action="' + action[0] + '" data-order="' + esc(order.id) + '">' + action[1] + ' ' + icon('arrow') + '</button>'; }).join('');
  }
  function hub() {
    var orders = service.hubOrders();
    var counts = { assigned: orders.length, active: orders.filter(function (o) { return ['delivered', 'collected', 'cancellation_requested'].indexOf(o.status) === -1; }).length, ready: orders.filter(function (o) { return o.status === 'ready'; }).length, handover: orders.filter(function (o) { return ['delivered', 'collected'].indexOf(o.status) !== -1; }).length };
    return '<main class="app-main"><section class="hub-hero"><span class="hub-kicker">' + icon('box') + ' Partner operations · demo role</span><h1>Fulfil the next<br>care handoff.</h1><p>This separate partner-hub portal shows assigned orders only. Role switching is a demo access shortcut; order updates are written to the MartPoint order record. Prescription approval remains with a pharmacy.</p><a class="button button-light" href="' + esc(basePath()) + '">' + icon('arrow') + ' Customer store</a></section><div class="hub-stats"><div class="hub-stat"><strong>' + counts.assigned + '</strong><span>Assigned orders</span></div><div class="hub-stat"><strong>' + counts.active + '</strong><span>Needs action</span></div><div class="hub-stat"><strong>' + counts.ready + '</strong><span>Ready</span></div><div class="hub-stat"><strong>' + counts.handover + '</strong><span>Handed over</span></div></div><div class="notice">Backend guardrail: assignment, stock reservation, payment status and prescription approval remain server-owned. This demo role is intentionally labelled and must be replaced by partner authentication before production.</div>' + (orders.length ? orders.map(function (order) { var loc = service.getLocation(order.locationId) || { name: order.locationName || 'Assigned pharmacy', note: '' }; return '<article class="hub-order"><div class="hub-order-head"><div><h2>' + esc(order.id) + '</h2><div class="hub-order-meta">Assigned to ' + esc(loc.name) + '</div></div><span class="status ' + (order.status === 'cancellation_requested' ? 'danger' : order.paymentStatus === 'pending' ? 'pending' : '') + '">' + esc(order.status.replace(/_/g, ' ')) + '</span></div><div class="hub-order-grid"><div><strong>Items</strong><span>' + order.items.map(function (line) { var p = service.getProduct(line.productId) || { name: line.name || 'Product' }; return esc(line.qty + ' × ' + p.name); }).join('<br>') + '</span></div><div><strong>Handover</strong><span>' + esc(order.fulfilment === 'pickup' ? 'Pickup · ' + (order.address || loc.note) : 'Delivery · ' + (order.address || 'Address supplied at checkout')) + '</span></div><div><strong>Payment</strong><span>' + esc(order.paymentStatus) + ' · ' + money(service.getOrderTotal(order)) + '</span></div></div><div class="hub-actions">' + hubActionButtons(order) + '<a class="button button-outline" href="#track/' + esc(order.id) + '">View customer tracking ' + icon('external') + '</a></div></article>'; }).join('') : '<div class="empty-state"><h2>No assigned orders</h2><p>Orders routed to this pharmacy location will appear here.</p></div>') + '</main>';
  }
  function render() {
    var current = route();
    var isHub = window.PHARMACY_DEMO_MODE === 'hub' || current.page === 'hub';
    var view = isHub ? hub() : (current.page === 'shop' ? shop(current.id) : current.page === 'product' ? productDetail(current.id) : current.page === 'cart' ? cart() : current.page === 'checkout' ? checkout() : current.page === 'prescription' ? prescription(current.id) : current.page === 'track' ? tracking(current.id) : current.page === 'locations' ? locations() : current.page === 'journal' ? journal() : home());
    document.getElementById('app').innerHTML = (isHub ? '<div class="hub-shell">' + header(true) + view + '</div>' : header(false) + view + footer()) + (ui.toast ? '<div class="toast show">' + esc(ui.toast) + '</div>' : '');
    var config = window.PHARMACY_DEMO_CONFIG || {};
    if (isHub && config.storeId && !ui.hubLoaded) { ui.hubLoaded = true; service.loadHubOrders().then(render); }
    if (!isHub && current.page === 'track' && config.storeId) { var tracked = service.order(current.id); var refreshKey = tracked && tracked.backendOrderId ? String(tracked.backendOrderId) + ':' + String(tracked.status) : ''; if (refreshKey && ui.refreshedOrder !== refreshKey) { ui.refreshedOrder = refreshKey; service.refreshOrder(current.id).then(render); } }
    if (!isHub && current.page === 'prescription' && config.storeId) { var rx = service.getState().prescriptions.find(function (item) { return String(item.id) === String(current.id); }); var rxKey = rx && rx.serverBacked ? String(rx.id) + ':' + String(rx.status) : ''; if (rxKey && ui.refreshedPrescription !== rxKey) { ui.refreshedPrescription = rxKey; service.refreshPrescription(rx.id).then(render); } }
  }
  function formData(form) { var data = {}; Array.prototype.forEach.call(new FormData(form).entries(), function (entry) { data[entry[0]] = entry[1]; }); return data; }
  function rememberCheckout(form) {
    if (!form) return;
    ['customer', 'phone', 'email', 'address', 'notes'].forEach(function (name) {
      if (form.elements[name]) ui.checkout[name] = form.elements[name].value || '';
    });
  }
  function submitCheckout(form) {
    if (!form || ui.submitting) return;
    rememberCheckout(form);
    var checkout = {
      customer: form.elements.customer ? form.elements.customer.value : '',
      phone: form.elements.phone ? form.elements.phone.value : '',
      email: form.elements.email ? form.elements.email.value : '',
      address: form.elements.address ? form.elements.address.value : '',
      notes: form.elements.notes ? form.elements.notes.value : ''
    };
    var key = 'pharmacy-' + Date.now();
    var request = { idempotencyKey: key, fulfilment: ui.checkout.fulfilment, paymentMethod: ui.checkout.paymentMethod, customer: checkout.customer, phone: checkout.phone, email: checkout.email, address: checkout.address, notes: checkout.notes };
    ui.submitting = true;
    var backend = (window.PHARMACY_DEMO_CONFIG || {}).storeId ? service.createBackendOrder(request) : Promise.resolve(service.createOrder(request));
    backend.then(function (result) {
      ui.submitting = false;
      if (!result.ok) { setToast((result.message || 'Unable to create order.') + (result.unavailable ? ' ' + result.unavailable.join(', ') : '')); return; }
      if (result.paymentRequired && result.payment && result.payment.public_key && window.PaystackPop) {
        var handler = PaystackPop.setup({ key: result.payment.public_key, email: result.payment.email, amount: result.payment.amount_kobo, currency: result.payment.currency || 'NGN', ref: result.payment.reference, callback: function (response) { fetch((window.PHARMACY_DEMO_CONFIG || {}).verifyPaymentUrl + '?reference=' + encodeURIComponent(response.reference), { credentials: 'same-origin' }).then(function (r) { return r.json(); }).then(function (verified) { setToast(verified.status ? 'Payment verified by the pharmacy service.' : (verified.message || 'Payment verification is still pending.')); go('track/' + result.order.id); }).catch(function () { setToast('Payment callback received. Verification is pending.'); go('track/' + result.order.id); }); }, onClose: function () { setToast('Payment is pending. You can retry from order tracking.'); go('track/' + result.order.id); } });
        handler.openIframe(); return;
      }
      setToast(result.paymentRequired ? 'Order received. Payment is pending verification.' : (result.duplicate ? 'Existing order reopened.' : 'Order received by the pharmacy.'));
      go('track/' + result.order.id);
    }).catch(function () {
      ui.submitting = false;
      setToast('We could not reach the pharmacy order service. Your cart is still available.');
    });
  }

  document.addEventListener('click', function (event) {
    var target = event.target.closest('button, a');
    if (!target) return;
    if (target.hasAttribute('data-route')) { go(target.getAttribute('data-route')); return; }
    if (target.hasAttribute('data-mobile-nav')) { document.body.classList.toggle('mobile-nav-open'); return; }
    if (target.hasAttribute('data-add')) {
      var added = service.addToCart(target.getAttribute('data-add'), 1, target.getAttribute('data-offer') || null); if (added.ok) setToast('Added to cart.'); else setToast(added.message); render(); return;
    }
    if (target.hasAttribute('data-detail-add')) {
      var detailQty = Number(document.querySelector('[data-detail-qty-value]') && document.querySelector('[data-detail-qty-value]').textContent) || 1;
      var detailAdded = service.addToCart(target.getAttribute('data-detail-add'), detailQty, target.getAttribute('data-detail-offer') || null); if (detailAdded.ok) { setToast('Added to cart.'); go('cart'); } else setToast(detailAdded.message); return;
    }
    if (target.hasAttribute('data-detail-qty')) {
      var valueNode = document.querySelector('[data-detail-qty-value]'); var value = Math.max(1, (Number(valueNode.textContent) || 1) + Number(target.getAttribute('data-detail-qty'))); valueNode.textContent = value; return;
    }
    if (target.hasAttribute('data-qty')) {
      var lineId = target.getAttribute('data-qty'); var line = service.getCart().find(function (item) { return item.line.productId === lineId; }); var result = service.updateCart(lineId, (line ? line.line.qty : 0) + Number(target.getAttribute('data-delta'))); if (!result.ok && result.message) setToast(result.message); render(); return;
    }
    if (target.hasAttribute('data-remove')) { service.updateCart(target.getAttribute('data-remove'), 0); setToast('Removed from cart.'); render(); return; }
    if (target.hasAttribute('data-category')) { ui.category = target.getAttribute('data-category'); render(); return; }
    if (target.hasAttribute('data-clear-search')) { ui.search = ''; ui.category = 'all'; render(); return; }
    if (target.hasAttribute('data-choice')) { rememberCheckout(document.querySelector('[data-checkout-form]')); ui.checkout[target.getAttribute('data-choice')] = target.getAttribute('data-value'); render(); return; }
    if (target.hasAttribute('data-checkout-submit')) { submitCheckout(target.form); return; }
    if (target.hasAttribute('data-location')) { var locationResult = service.setLocation(target.getAttribute('data-location')); if (locationResult.unavailable && locationResult.unavailable.length) setToast('Location changed. ' + locationResult.unavailable.join(', ') + ' needs attention.'); else setToast('Fulfilment location updated.'); render(); return; }
    if (target.hasAttribute('data-prescription-approve')) { service.approvePrescription(target.getAttribute('data-prescription-approve')).then(function (approved) { setToast(approved && approved.ok ? 'Pharmacy review complete.' : (approved.message || 'Request not found.')); render(); }); return; }
    if (target.hasAttribute('data-prescription-add')) { var rxAdded = service.addApprovedPrescriptionToCart(target.getAttribute('data-prescription-add')); if (rxAdded.ok) { setToast('Approved item added to checkout.'); go('checkout'); } else setToast(rxAdded.message); return; }
    if (target.hasAttribute('data-request-cancel')) { var cancelled = service.requestCancellation(target.getAttribute('data-request-cancel')); setToast(cancelled.ok ? 'Cancellation request sent in demo mode.' : cancelled.message); render(); return; }
    if (target.hasAttribute('data-retry-payment')) { var retried = service.retryPayment(target.getAttribute('data-retry-payment')); setToast(retried.ok ? 'Demo payment marked successful.' : retried.message); render(); return; }
    if (target.hasAttribute('data-hub-action')) { service.updateHubOrder(target.getAttribute('data-order'), target.getAttribute('data-hub-action')).then(function (hubResult) { setToast(hubResult.ok ? 'Order status updated for customer tracking.' : hubResult.message); render(); }); return; }
  });
  document.addEventListener('submit', function (event) {
    if (event.target.matches('[data-search-form]')) { event.preventDefault(); ui.search = event.target.q.value || ''; ui.category = 'all'; go('shop'); return; }
    if (event.target.matches('[data-prescription-form]')) { event.preventDefault(); var rx = formData(event.target); var file = event.target.file.files && event.target.file.files[0]; service.createPrescription({ file: file, fileName: file ? file.name : 'fictional-prescription-demo.pdf', customer: rx.customer, phone: rx.phone, email: rx.email, note: rx.note, locationId: selectedLocation().id }).then(function (result) { if (!result.ok) { setToast(result.message || 'Prescription request failed.'); return; } go('prescription/' + result.request.id); setToast('Prescription request sent to the pharmacy queue.'); }); return; }
    if (event.target.matches('[data-checkout-form]')) {
      event.preventDefault(); submitCheckout(event.target);
    }
  });
  window.addEventListener('hashchange', render);
  render();
})(window, document);
