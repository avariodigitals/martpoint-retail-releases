/* Shared customer pricing and stock quotes for every storefront presentation. */
(function (root) {
  'use strict';
  if (root.MPCommerceClient) return;
  function Client(config) { this.config = config; }
  Client.prototype.quote = async function (items, warehouse) {
    var c = this.config;
    var body = new URLSearchParams({store_id: c.storeId, warehouse_id: warehouse || '', cart: JSON.stringify(items)});
    if (c.csrfName) body.set(c.csrfName, c.csrfHash);
    var response = await fetch(c.quoteUrl, {method: 'POST', credentials: 'same-origin', headers: {'Content-Type': 'application/x-www-form-urlencoded'}, body: body.toString()});
    if (!response.ok) throw new Error('We could not refresh your cart. Please try again.');
    var result = await response.json();
    if (result.csrf_hash) c.csrfHash = result.csrf_hash;
    if (!result.status) throw new Error(result.message || 'We could not refresh your cart.');
    if (!Array.isArray(result.items) || result.items.length !== items.length) throw new Error('Please refresh your cart.');
    this.showIdentity(result);
    return result;
  };
  Client.prototype.changed = function (before, after) {
    return before.some(function (item, index) {
      var next = after[index];
      return !next || Number(item.price) !== Number(next.price)
        || Number(item.stock || 0) !== Number(next.stock || 0)
        || item.available !== next.available
        || (item.pricing_version && item.pricing_version !== next.pricing_version)
        || JSON.stringify((item.addons || []).map(function (a) { return [a.id, Number(a.qty), Number(a.price)]; })) !== JSON.stringify((next.addons || []).map(function (a) { return [a.id, Number(a.qty), Number(a.price)]; }));
    });
  };
  Client.prototype.showIdentity = function (result) {
    var host = document.getElementById('mp-commerce-identity');
    if (!host) return;
    host.replaceChildren();
    var message = document.createElement('span');
    message.textContent = result.member ? 'Signed in as ' + result.customer_name + (result.has_group_price ? ' · Member prices applied' : ' · Your current prices are shown') : 'Shopping at public prices. ';
    host.appendChild(message);
    if (!result.member && this.config.signInUrl) {
      var link = document.createElement('a'); link.href = this.config.signInUrl;
      link.textContent = 'Sign in for your member price'; host.appendChild(link);
    }
  };
  root.MPCommerceClient = Client;
})(window);
