/*
 * MartPoint — Building Materials storefront behaviour.
 *
 * Small and dependency-free on purpose. Shopping itself (localStorage cart,
 * toast, tracking) lives in themes/shared/scripts.php; this file only connects
 * the building layout's controls to it.
 */
(function () {
  'use strict';

  var reducedMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ------------------------------------------------------------
     Mobile navigation
     ------------------------------------------------------------ */
  var nav = document.getElementById('bm-nav');
  var toggle = document.querySelector('.mobile-toggle');

  function closeNav() {
    if (!nav || !toggle) return;
    nav.classList.remove('shown');
    toggle.setAttribute('aria-expanded', 'false');
    toggle.textContent = 'Menu';
  }

  if (nav && toggle) {
    toggle.addEventListener('click', function () {
      var isOpen = nav.classList.toggle('shown');
      toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      toggle.textContent = isOpen ? 'Close' : 'Menu';
    });

    document.addEventListener('click', function (event) {
      if (!nav.classList.contains('shown')) return;
      if (nav.contains(event.target) || toggle.contains(event.target)) return;
      closeNav();
    });

    document.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && nav.classList.contains('shown')) {
        closeNav();
        toggle.focus();
      }
    });

    nav.addEventListener('click', function (event) {
      var link = event.target.closest('a');
      if (link && window.innerWidth <= 1200) closeNav();
    });

    window.addEventListener('resize', function () {
      if (window.innerWidth > 1200 && nav.classList.contains('shown')) closeNav();
    });
  }

  /* Inline, keyboard-accessible selects; the named select remains authoritative. */
  document.querySelectorAll('body.bm-building select').forEach(function (select, index) {
    var wrapper = document.createElement('span');
    wrapper.className = 'bm-select';
    select.parentNode.insertBefore(wrapper, select);
    wrapper.appendChild(select);
    var trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'bm-select-trigger';
    trigger.setAttribute('role', 'combobox');
    trigger.setAttribute('aria-haspopup', 'listbox');
    trigger.setAttribute('aria-expanded', 'false');
    var label = select.labels && select.labels[0];
    var labelText = select.getAttribute('aria-label') || (label && label.firstChild.textContent.trim()) || select.name;
    trigger.setAttribute('aria-label', labelText);
    var options = document.createElement('span');
    options.id = 'bm-select-options-' + index;
    options.className = 'bm-select-options';
    options.setAttribute('role', 'listbox');
    options.setAttribute('aria-label', labelText);
    options.hidden = true;
    trigger.setAttribute('aria-controls', options.id);
    function close() { options.hidden = true; trigger.setAttribute('aria-expanded', 'false'); }
    function sync() {
      trigger.textContent = select.selectedIndex < 0 ? 'Choose an option' : select.options[select.selectedIndex].text;
      trigger.disabled = select.disabled;
      options.querySelectorAll('button').forEach(function (button) {
        button.setAttribute('aria-selected', Number(button.dataset.index) === select.selectedIndex ? 'true' : 'false');
      });
    }
    function open() {
      options.hidden = false;
      trigger.setAttribute('aria-expanded', 'true');
      var active = options.querySelector('[aria-selected=true]:not(:disabled)') || options.querySelector('button:not(:disabled)');
      if (active) active.focus();
    }
    Array.prototype.forEach.call(select.options, function (option, optionIndex) {
      var button = document.createElement('button');
      button.type = 'button';
      button.setAttribute('role', 'option');
      button.tabIndex = -1;
      button.dataset.index = optionIndex;
      button.textContent = option.text;
      button.disabled = option.disabled;
      button.addEventListener('click', function (event) {
        event.preventDefault();
        select.selectedIndex = optionIndex;
        select.dispatchEvent(new Event('change', { bubbles: true }));
        close(); trigger.focus();
      });
      options.appendChild(button);
    });
    wrapper.appendChild(trigger); wrapper.appendChild(options);
    select.classList.add('bm-select-native'); select.tabIndex = -1;
    select.setAttribute('aria-hidden', 'true');
    trigger.addEventListener('click', function (event) { event.preventDefault(); if (options.hidden) open(); else close(); });
    wrapper.addEventListener('keydown', function (event) {
      if (event.key === 'Escape') { event.preventDefault(); close(); trigger.focus(); }
      if (event.key === 'ArrowDown' || event.key === 'ArrowUp' || event.key === 'Home' || event.key === 'End') {
        event.preventDefault();
        if (options.hidden) { open(); return; }
        var buttons = Array.prototype.slice.call(options.querySelectorAll('button:not(:disabled)'));
        var current = buttons.indexOf(document.activeElement);
        var next = event.key === 'Home' ? 0 : event.key === 'End' ? buttons.length - 1 : (current + (event.key === 'ArrowUp' ? -1 : 1) + buttons.length) % buttons.length;
        if (buttons[next]) buttons[next].focus();
      }
      if (event.key === 'Tab') close();
    });
    document.addEventListener('click', function (event) { if (!wrapper.contains(event.target)) close(); });
    select.addEventListener('change', sync);
    select.addEventListener('invalid', function (event) { event.preventDefault(); trigger.focus(); open(); });
    if (select.form) select.form.addEventListener('reset', function () { window.setTimeout(sync, 0); });
    sync();
  });

  /* ------------------------------------------------------------
     Product quantity + add to cart
     ------------------------------------------------------------ */
  var purchase = document.querySelector('[data-bm-purchase]');
  if (purchase) {
    var qtyInput = purchase.querySelector('#bm-qty');
    var feedback = purchase.querySelector('.purchase-feedback');

    function clampQty(value) {
      var min = parseInt(qtyInput.getAttribute('min'), 10) || 1;
      var max = parseInt(qtyInput.getAttribute('max'), 10) || 1;
      var qty = parseInt(value, 10);
      if (isNaN(qty)) qty = min;
      return Math.min(max, Math.max(min, qty));
    }

    purchase.querySelectorAll('[data-bm-qty-step]').forEach(function (button) {
      button.addEventListener('click', function () {
        var step = parseInt(button.getAttribute('data-bm-qty-step'), 10) || 0;
        qtyInput.value = clampQty((parseInt(qtyInput.value, 10) || 1) + step);
      });
    });

    if (qtyInput) {
      qtyInput.addEventListener('change', function () {
        qtyInput.value = clampQty(qtyInput.value);
      });
    }

    var addButton = purchase.querySelector('[data-bm-add-to-cart]');
    if (addButton) {
      addButton.addEventListener('click', function () {
        if (typeof window.addToCart !== 'function') {
          if (feedback) feedback.textContent = 'The cart is not available right now. Please request a quotation instead.';
          return;
        }
        var qty = clampQty(qtyInput ? qtyInput.value : 1);
        var added = window.addToCart(
          parseInt(purchase.getAttribute('data-product-id'), 10),
          purchase.getAttribute('data-product-type') || 'physical',
          purchase.getAttribute('data-product-name') || '',
          parseFloat(purchase.getAttribute('data-product-price')) || 0,
          purchase.getAttribute('data-product-image') || '',
          qty,
          parseInt(purchase.getAttribute('data-product-stock'), 10) || 0
        );
        if (feedback) {
          feedback.textContent = added
            ? qty + (qty === 1 ? ' item added to your cart.' : ' items added to your cart.')
            : '';
        }
      });
    }
  }

  /* ------------------------------------------------------------
     Enquiry attachments — mirror the server's limits before upload
     ------------------------------------------------------------ */
  var fileInput = document.getElementById('enquiry-documents');
  if (fileInput) {
    var hint = document.getElementById('enquiry-file-hint');
    var MAX_FILES = 3;
    var MAX_FILE = 8 * 1024 * 1024;
    var MAX_TOTAL = 20 * 1024 * 1024;
    var ALLOWED = ['pdf', 'doc', 'docx', 'dwg', 'dxf', 'jpg', 'jpeg', 'png', 'webp'];

    var humanSize = function (bytes) {
      if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
      return Math.max(1, Math.round(bytes / 1024)) + ' KB';
    };

    var validateFiles = function () {
      var files = Array.prototype.slice.call(fileInput.files || []);
      if (!files.length) {
        fileInput.setCustomValidity('');
        if (hint) hint.textContent = '';
        return true;
      }

      var problem = '';
      var total = 0;

      if (files.length > MAX_FILES) {
        problem = 'Attach no more than ' + MAX_FILES + ' files.';
      }

      files.forEach(function (file) {
        total += file.size;
        if (problem) return;
        var dot = file.name.lastIndexOf('.');
        var extension = dot > -1 ? file.name.slice(dot + 1).toLowerCase() : '';
        if (ALLOWED.indexOf(extension) === -1) {
          problem = '“' + file.name + '” is not an accepted file type.';
        } else if (file.size > MAX_FILE) {
          problem = '“' + file.name + '” is larger than 8 MB.';
        }
      });

      if (!problem && total > MAX_TOTAL) {
        problem = 'Attachments total ' + humanSize(total) + '. The limit is 20 MB.';
      }

      fileInput.setCustomValidity(problem);
      if (hint) {
        hint.textContent = problem
          ? problem + ' Remove the file or choose a smaller one.'
          : files.length + (files.length === 1 ? ' file selected' : ' files selected') + ' · ' + humanSize(total);
      }
      return !problem;
    };

    fileInput.addEventListener('change', validateFiles);

    var form = fileInput.closest('form');
    if (form) {
      var submitting = false;
      form.addEventListener('submit', function (event) {
        if (!validateFiles()) {
          event.preventDefault();
          fileInput.focus();
          return;
        }
        if (submitting) {
          event.preventDefault();
          return;
        }
        submitting = true;
        var submit = form.querySelector('button[type="submit"]');
        if (submit) {
          submit.disabled = true;
          submit.textContent = 'Sending…';
        }
      });
    }
  }

  // Reuse the store-scoped newsletter endpoint; keep feedback beside the form.
  document.querySelectorAll('.bm-newsletter-form').forEach(function (form) {
    var sending = false;
    form.addEventListener('submit', function (event) {
      event.preventDefault();
      if (sending || !form.reportValidity()) return;
      var button = form.querySelector('button[type="submit"]');
      var status = form.querySelector('.bm-newsletter-result');
      sending = true;
      button.disabled = true;
      status.textContent = 'Subscribing…';
      fetch(form.action, {method: 'POST', body: new FormData(form), credentials: 'same-origin'})
        .then(function (response) { if (!response.ok) throw new Error('request'); return response.json(); })
        .then(function (reply) {
          if (reply.status) form.reset();
          if (reply.csrf_hash) {
            document.querySelectorAll('[data-newsletter-csrf]').forEach(function (input) {
              input.value = reply.csrf_hash;
              input.defaultValue = reply.csrf_hash;
            });
          }
          status.textContent = reply.message || 'Could not subscribe. Please try again.';
        })
        .catch(function () { status.textContent = 'Could not subscribe right now. Refresh the page and try again.'; })
        .finally(function () { sending = false; button.disabled = false; });
    });
  });

  /* ------------------------------------------------------------
     Keep in-page anchors clear of the sticky header on mobile
     ------------------------------------------------------------ */
  if (!reducedMotion) {
    document.querySelectorAll('a[href^="#application-"]').forEach(function (link) {
      link.addEventListener('click', function (event) {
        var target = document.querySelector(link.getAttribute('href'));
        if (!target) return;
        event.preventDefault();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        if (history.replaceState) history.replaceState(null, '', link.getAttribute('href'));
      });
    });
  }
})();
