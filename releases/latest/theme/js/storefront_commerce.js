/* Shared customer pricing and stock quotes for every storefront presentation. */
(function (root) {
  'use strict';
  if (root.MPCommerceClient) return;
  function Client(config) { this.config = config; this.noticeShown = false; }
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
    if (host) host.hidden = true;
    if (this.config.pricingNoticeEnabled === false || result.member || this.noticeShown) return;
    this.noticeShown = true;
    this.showPricingNotice();
  };
  Client.prototype.showPricingNotice = function () {
    var c = this.config;
    if (!c.signInUrl || !document.body) return;
    var overlay = document.createElement('div');
    overlay.setAttribute('role', 'presentation');
    overlay.style.cssText = 'position:fixed;inset:0;z-index:100000;display:flex;align-items:center;justify-content:center;padding:20px;background:rgba(15,23,42,.56);';
    var dialog = document.createElement('section');
    dialog.setAttribute('role', 'dialog'); dialog.setAttribute('aria-modal', 'true');
    dialog.setAttribute('aria-labelledby', 'mp-pricing-notice-title');
    dialog.style.cssText = 'position:relative;width:min(100%,460px);padding:30px;border-radius:18px;background:#fff;color:#172033;box-shadow:0 24px 80px rgba(15,23,42,.28);font:inherit;';
    var close = document.createElement('button');
    close.type = 'button'; close.setAttribute('aria-label', 'Close'); close.textContent = '×';
    close.style.cssText = 'position:absolute;right:14px;top:10px;width:36px;height:36px;border:0;border-radius:50%;background:#f1f5f9;color:#334155;font-size:24px;line-height:1;cursor:pointer;';
    var title = document.createElement('h2'); title.id = 'mp-pricing-notice-title';
    title.textContent = c.pricingNoticeTitle || 'Customer pricing';
    title.style.cssText = 'margin:0 34px 12px 0;font-size:21px;line-height:1.3;font-weight:750;';
    var message = document.createElement('p');
    message.textContent = c.pricingNoticeText || 'Sign in to My Account to check whether the store has assigned your account to a customer pricing group.';
    message.style.cssText = 'margin:0 0 22px;color:#475569;font-size:15px;line-height:1.6;';
    var link = document.createElement('a'); link.href = c.signInUrl;
    link.textContent = c.pricingNoticeLinkText || 'Sign in to My Account';
    link.style.cssText = 'display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:0 20px;border-radius:10px;background:var(--mp-primary,#0f766e);color:#fff;font-weight:700;text-decoration:none;';
    dialog.appendChild(close); dialog.appendChild(title); dialog.appendChild(message); dialog.appendChild(link); overlay.appendChild(dialog);
    document.body.appendChild(overlay);
    var dismiss = function () { overlay.remove(); window.removeEventListener('keydown', onKey); };
    var onKey = function (event) { if (event.key === 'Escape') dismiss(); };
    close.addEventListener('click', dismiss);
    overlay.addEventListener('click', function (event) { if (event.target === overlay) dismiss(); });
    window.addEventListener('keydown', onKey);
    close.focus();
  };
  root.MPCommerceClient = Client;
})(window);
