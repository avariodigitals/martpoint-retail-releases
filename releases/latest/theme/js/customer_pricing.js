(function(root) {
  'use strict';
  function amount(value, fallback) {
    var n = Number(value);
    return value !== null && value !== '' && Number.isFinite(n) ? n : fallback;
  }
  function price(normal, wholesale, policy) {
    if (!policy || policy.source === 'normal') return amount(normal, 0);
    var base = policy.source === 'group' ? amount(wholesale, null) : amount(normal, 0);
    if (base === null || (policy.source === 'group' && base <= 0)) return null;
    var factor = 1 + (policy.direction === 'Decrease' ? -1 : 1) * Number(policy.percent) / 100;
    var scale = Math.pow(10, policy.decimals === undefined ? 2 : policy.decimals);
    return Math.round((base * factor + Number.EPSILON) * scale) / scale;
  }
  function label(p) {
    if (!p || p.source === 'normal') return '';
    return p.name + ' · ' + (p.source === 'group' ? 'Wholesale ' : 'Selected price ') +
      (p.direction === 'Decrease' ? '−' : '+') + p.percent + '%';
  }
  function load(base, store, customer) {
    var key = 'mp-customer-price:' + store + ':' + customer;
    return fetch(base.replace(/\/$/, '') + '/customer_groups/pricing?customer_id=' + encodeURIComponent(customer), {credentials:'same-origin', cache:'no-store'})
      .then(function(r){if(!r.ok) throw new Error('Could not check customer pricing.'); return r.json();})
      .then(function(r){if(r.error) throw new Error(r.error); try {localStorage.setItem(key, JSON.stringify(r.policy));} catch(e) {} return r.policy;})
      .catch(function(e){
        // Offline rules are only a quote. Checkout/sync validates their version and price.
        if (root.navigator && root.navigator.onLine === false) {
          try { var saved = JSON.parse(localStorage.getItem(key)); if(saved && saved.version) return saved; } catch(ignore) {}
        }
        throw e;
      });
  }
  var api = {price:price, label:label, load:load};
  root.MpCustomerPricing = api;
  if (typeof module !== 'undefined' && module.exports) module.exports = api;
})(typeof window === 'undefined' ? globalThis : window);
