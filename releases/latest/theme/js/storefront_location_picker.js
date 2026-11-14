(function (w, d) {
  'use strict';
  if (w.MPThemeLocationPicker) return;
  var cfg = w.MPThemeLocationConfig || {};
  var api = cfg.availabilityUrl || '';
  var storeId = Number(cfg.storeId || (w.MartPointTheme && w.MartPointTheme.storeId) || 0);
  var key = 'sf_outlet_' + storeId;
  var pending = null;
  var locations = [];

  function escapeHtml(value) {
    return String(value == null ? '' : value).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' }[c];
    });
  }
  function current() {
    var id = Number(localStorage.getItem(key) || 0);
    return locations.find(function (location) { return Number(location.id) === id; }) || (id ? { id: id, name: localStorage.getItem(key + '_name') || 'Selected location' } : null);
  }
  function cartRows(item) {
    var rows = w.MartPointTheme && typeof w.MartPointTheme.cart === 'function' ? w.MartPointTheme.cart() : [];
    rows = rows.map(function (row) { return Object.assign({}, row, { type: row.type === 'service' ? 'service' : 'product', qty: row.qty || 1 }); });
    if (item) rows.push(Object.assign({}, item, { type: item.type === 'service' ? 'service' : 'product', qty: item.qty || 1 }));
    return rows;
  }
  function fetchAvailability(rows) {
    var body = new URLSearchParams({ store_id: String(storeId), cart: JSON.stringify(rows) });
    if (w.MartPointThemeConfig) cfg.csrfHash = w.MartPointThemeConfig.csrfHash;
    if (cfg.csrfName && cfg.csrfHash) body.set(cfg.csrfName, cfg.csrfHash);
    return fetch(api, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded' }, body: body.toString() })
      .then(function (response) { return response.json(); })
      .then(function (result) {
        if (!result.status || !Array.isArray(result.locations)) throw new Error(result.message || 'Locations are unavailable.');
        if (result.csrf_hash) {
          cfg.csrfHash = result.csrf_hash;
          if (w.MartPointThemeConfig) w.MartPointThemeConfig.csrfHash = result.csrf_hash;
        }
        locations = result.locations;
        return locations;
      });
  }
  function availabilityText(location) {
    if (!location) return 'Availability could not be confirmed. Try again.';
    if (location.note) return location.note;
    var rows = location.items || [];
    if (!rows.length) return 'Availability will be confirmed by the store.';
    var unavailable = rows.filter(function (line) { return !line.available; });
    if (unavailable.length) return 'Unavailable: ' + unavailable.map(function (line) { return line.name; }).join(', ');
    return rows.length === 1 ? 'Your item is available here.' : 'All ' + rows.length + ' cart items are available here.';
  }
  function confirm() {
    var choice = d.querySelector('input[name="mp-theme-location"]:checked');
    var error = d.getElementById('mp-theme-location-error');
    if (!choice) { if (error) error.textContent = 'Choose a location to continue.'; return; }
    localStorage.setItem(key, choice.value);
    var chosenLocation = locations.find(function (location) { return Number(location.id) === Number(choice.value); });
    if (chosenLocation) localStorage.setItem(key + '_name', chosenLocation.name || '');
    var overlay = d.getElementById('mp-theme-location-overlay');
    if (overlay) overlay.remove();
    var item = pending; pending = null;
    if (item && w.MartPointTheme && typeof w.MartPointTheme.addToCart === 'function') w.MartPointTheme.addToCart(item);
    renderBanner();
    d.dispatchEvent(new w.CustomEvent('martpoint:location'));
  }
  function open(item) {
    if (cfg.preview || cfg.selectionEnabled === false) return;
    pending = item || null;
    var overlay = d.getElementById('mp-theme-location-overlay');
    if (!overlay) {
      overlay = d.createElement('div');
      overlay.id = 'mp-theme-location-overlay';
      overlay.style.cssText = 'position:fixed;inset:0;z-index:100100;background:rgba(15,23,42,.62);display:flex;align-items:center;justify-content:center;padding:16px;';
      overlay.innerHTML = '<section role="dialog" aria-modal="true" aria-labelledby="mp-theme-location-title" style="width:min(100%,620px);max-height:90vh;overflow:auto;background:#fff;border-radius:18px;padding:24px;color:#172033;box-shadow:0 24px 80px rgba(15,23,42,.3);font:inherit"><h2 id="mp-theme-location-title" style="margin:0 0 8px;font-size:21px">Choose a store location</h2><p id="mp-theme-location-help" style="margin:0 0 16px;color:#64748b">Check availability at each location before adding this item or continuing to checkout.</p><div id="mp-theme-location-list"></div><div id="mp-theme-location-error" role="status" style="color:#b91c1c;margin-top:10px"></div><div style="display:flex;justify-content:flex-end;margin-top:16px"><button type="button" id="mp-theme-location-confirm" style="border:0;border-radius:9px;padding:12px 18px;background:#0f766e;color:#fff;font-weight:700">Use this location</button></div></section>';
      d.body.appendChild(overlay);
      d.getElementById('mp-theme-location-confirm').addEventListener('click', confirm);
      d.getElementById('mp-theme-location-list').addEventListener('change', function (event) {
        if (event.target && event.target.name === 'mp-theme-location') d.getElementById('mp-theme-location-error').textContent = '';
      });
    }
    var title = d.getElementById('mp-theme-location-title');
    var help = d.getElementById('mp-theme-location-help');
    var hasCurrent = !!current();
    if (title) title.textContent = hasCurrent ? 'Switch fulfilment location' : 'Choose a store location';
    if (help) help.textContent = hasCurrent ? 'We’ll check availability for your cart at each location before you switch.' : 'Check availability at each location before adding this item or continuing to checkout.';
    var list = d.getElementById('mp-theme-location-list');
    var error = d.getElementById('mp-theme-location-error');
    list.innerHTML = '<p style="color:#64748b">Checking stock availability…</p>';
    error.textContent = '';
    fetchAvailability(cartRows(pending))
      .then(function () {
        if (locations.length === 0) {
          var noLocationOverlay = d.getElementById('mp-theme-location-overlay'); if (noLocationOverlay) noLocationOverlay.remove();
          var noLocationItem = pending; pending = null;
          if (noLocationItem && w.MartPointTheme && typeof w.MartPointTheme.addToCart === 'function') w.MartPointTheme.addToCart(noLocationItem);
          return;
        }
        if (locations.length === 1) {
          localStorage.setItem(key, String(locations[0].id)); localStorage.setItem(key + '_name', locations[0].name || '');
          var oneLocationOverlay = d.getElementById('mp-theme-location-overlay'); if (oneLocationOverlay) oneLocationOverlay.remove();
          var oneLocationItem = pending; pending = null;
          if (oneLocationItem && w.MartPointTheme && typeof w.MartPointTheme.addToCart === 'function') w.MartPointTheme.addToCart(oneLocationItem);
          renderBanner(); d.dispatchEvent(new w.CustomEvent('martpoint:location')); return;
        }
        var chosen = Number(localStorage.getItem(key) || 0);
        list.innerHTML = locations.map(function (location) {
          var unavailable = (location.items || []).filter(function (line) { return !line.available; });
          var note = availabilityText(location);
          return '<label style="display:flex;gap:12px;align-items:flex-start;border:1px solid #dbe3ee;border-radius:12px;padding:14px;margin:10px 0;cursor:pointer"><input type="radio" name="mp-theme-location" value="' + Number(location.id) + '" ' + (chosen === Number(location.id) ? 'checked' : '') + ' style="margin-top:4px"><span><strong>' + escapeHtml(location.name) + '</strong><small style="display:block;color:#64748b;margin-top:3px">' + escapeHtml([location.address, location.city].filter(Boolean).join(' · ')) + '</small><small style="display:block;color:' + (unavailable.length ? '#b45309' : '#047857') + ';margin-top:7px">' + escapeHtml(note) + '</small></span></label>';
        }).join('');
      })
      .catch(function (e) { locations = []; error.textContent = e.message || 'Could not confirm availability. Try again.'; });
  }
  function renderBanner() {
    if (cfg.preview || cfg.selectionEnabled === false) return;
    if (!/\/(cart|checkout)\/?$/.test(w.location.pathname) || !d.body) return;
    var cart = w.MartPointTheme && typeof w.MartPointTheme.cart === 'function' ? w.MartPointTheme.cart() : [];
    if (!cart.length) return;
    var host = d.querySelector('[data-mp-cart]') || d.querySelector('main') || d.body;
    var banner = d.getElementById('mp-theme-location-banner');
    if (!banner) {
      banner = d.createElement('section'); banner.id = 'mp-theme-location-banner';
      banner.style.cssText = 'margin:12px auto;max-width:1100px;padding:14px 16px;border:1px solid #dbe3ee;border-radius:12px;background:#fff;color:#172033;';
      host.parentNode.insertBefore(banner, host);
    }
    var selected = current();
    var selectedRecord = selected && locations.find(function (location) { return Number(location.id) === Number(selected.id); });
    var status = selectedRecord ? availabilityText(selectedRecord) : (selected ? 'Checking availability…' : 'Choose a location before checkout.');
    banner.innerHTML = '<div><strong>Fulfilment location: </strong>' + escapeHtml(selected ? selected.name : 'Not selected') + '</div><small id="mp-theme-location-status" role="status" style="display:block;margin-top:5px;color:#64748b">' + escapeHtml(status) + '</small><button type="button" id="mp-theme-location-change" style="margin-top:10px;border:1px solid #b7e4de;border-radius:8px;padding:8px 12px;background:#f0fdfa;color:#0f766e;font:inherit;font-weight:700;cursor:pointer">' + (selected ? 'Switch location' : 'Choose location') + '</button>';
    var statusNode = d.getElementById('mp-theme-location-status');
    if (statusNode && selectedRecord) statusNode.style.color = selectedRecord.available === false ? '#b45309' : '#047857';
    d.getElementById('mp-theme-location-change').onclick = function () { open(null); };
  }

  d.addEventListener('click', function (event) {
    var button = event.target && event.target.closest ? event.target.closest('[data-mp-add-to-cart]') : null;
    if (!button || !storeId || cfg.preview || cfg.selectionEnabled === false) return;
    var item = { id: Number(button.dataset.productId || 0), type: button.dataset.productType || 'product', name: button.dataset.productName || 'Product', price: Number(button.dataset.productPrice || 0), image: button.dataset.productImage || '', qty: Number(button.dataset.productQty || 1), stock: button.dataset.productStock == null ? null : Number(button.dataset.productStock), product_type: button.dataset.productKind || 'physical', is_variant_parent: button.dataset.variantParent === '1', requires_quote: button.dataset.requiresQuote === '1' };
    if (!item.id || current()) return;
    event.preventDefault(); event.stopImmediatePropagation();
    open(item);
  }, true);

  d.addEventListener('submit', function (event) {
    var form = event.target && event.target.closest ? event.target.closest('[data-mp-checkout]') : null;
    if (!form || !storeId || cfg.preview || cfg.selectionEnabled === false) return;
    var selected = Number(localStorage.getItem(key) || 0);
    if (!selected) { event.preventDefault(); event.stopImmediatePropagation(); open(null); return; }
    var field = form.querySelector('input[name="warehouse_id"]');
    if (!field) { field = d.createElement('input'); field.type = 'hidden'; field.name = 'warehouse_id'; form.appendChild(field); }
    field.value = String(selected);
  }, true);

  d.addEventListener('DOMContentLoaded', function () {
    renderBanner();
    var cart = w.MartPointTheme && typeof w.MartPointTheme.cart === 'function' ? w.MartPointTheme.cart() : [];
    if (!cart.length || !/\/(cart|checkout)\/?$/.test(w.location.pathname)) return;
    if (!current()) { open(null); return; }
    fetchAvailability(cartRows(null)).then(function () {
      var selectedId = Number(localStorage.getItem(key) || 0);
      var selected = locations.find(function (location) { return Number(location.id) === selectedId; });
      if (!selected) {
        localStorage.removeItem(key);
        localStorage.removeItem(key + '_name');
        renderBanner();
        open(null);
        return;
      }
      renderBanner();
    }).catch(function () { renderBanner(); });
  });
  w.MPThemeLocationPicker = { open: open, current: current };
})(window, document);
