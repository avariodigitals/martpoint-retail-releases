(function () {
  'use strict';
  var root = document.querySelector('[data-mp-auth]'); if (!root) return;
  var contact = root.querySelector('[data-auth-contact]'), code = root.querySelector('[data-auth-code]');
  var status = root.querySelector('[data-auth-status]'), error = root.querySelector('[data-auth-error]');
  if (root.dataset.preview === '1') { status.textContent = 'Sign-in is disabled in theme preview.'; root.querySelectorAll('button').forEach(function (b) { b.disabled = true; }); return; }
  var method = 'phone', busy = false;
  function choose(next) {
    if (busy) return; method = next;
    root.querySelectorAll('[data-auth-method]').forEach(function (b) { b.setAttribute('aria-pressed', String(b.dataset.authMethod === method)); });
    ['phone', 'email'].forEach(function (name) {
      var box = root.querySelector('[data-auth-' + name + ']'); box.hidden = name !== method;
      box.querySelectorAll('input').forEach(function (input) { input.disabled = box.hidden; });
    });
  }
  function fields() {
    var body = new URLSearchParams(new FormData(contact)); body.set('method', method); return body;
  }
  async function submit(url, body) {
    if (busy) return null; busy = true; error.hidden = true;
    root.querySelectorAll('button').forEach(function (b) { b.disabled = true; });
    if (root.dataset.csrfName) body.set(root.dataset.csrfName, root.dataset.csrfHash);
    try {
      var response = await fetch(url, { method: 'POST', credentials: 'same-origin', headers: { 'Content-Type': 'application/x-www-form-urlencoded', Accept: 'application/json' }, body: body.toString() });
      var result = await response.json();
      if (result.csrf_hash) root.dataset.csrfHash = result.csrf_hash;
      if (!response.ok || !result.status) throw new Error(result.message || 'Please try again.');
      return result;
    } catch (e) { error.textContent = e.message || 'Connection failed. Please retry.'; error.hidden = false; return null; }
    finally { busy = false; root.querySelectorAll('button').forEach(function (b) { b.disabled = false; }); }
  }
  root.querySelectorAll('[data-auth-method]').forEach(function (b) { b.addEventListener('click', function () { choose(b.dataset.authMethod); }); });
  contact.addEventListener('submit', async function (e) {
    e.preventDefault(); if (!contact.reportValidity()) return;
    var result = await submit(root.dataset.send, fields());
    if (result) { contact.hidden = true; code.hidden = false; status.textContent = result.message || 'Enter the code sent to your contact.'; code.querySelector('input').focus(); }
  });
  code.addEventListener('submit', async function (e) {
    e.preventDefault(); if (!code.reportValidity()) return;
    var body = fields(); body.set('otp', code.querySelector('[name=otp]').value.trim());
    if (await submit(root.dataset.verify, body)) window.location.href = root.dataset.return;
  });
  root.querySelector('[data-auth-change]').addEventListener('click', function () { if (!busy) { contact.hidden = false; code.hidden = true; error.hidden = true; status.textContent = 'Choose where to receive a new code.'; } });
})();
