<?php
/**
 * Shared Event Ticketing widget (migration 4.0.9.221).
 *
 * Injected by Theme_engine on product pages whose items are tickets
 * (product_type='event') — for built-in PHP themes AND uploaded HTML
 * themes — so no individual template carries event code.
 *
 * It registers ticket items with window.MP_EVENTS, wraps addToCart and
 * intercepts data-mp-add-to-cart buttons so a session date is chosen before
 * a ticket enters the cart. Seats are re-validated server-side at checkout;
 * this widget is a selection contract, not a trust boundary.
 */
$ev_items = (isset($event_ticket_items) && is_array($event_ticket_items)) ? $event_ticket_items : [];
if (!$ev_items) return;
?>
<div id="mp-event-modal" class="mp-event-modal" hidden>
  <div class="mp-event-sheet" role="dialog" aria-modal="true" aria-labelledby="mp-event-title">
    <div class="mp-event-head">
      <div>
        <div class="mp-event-kicker">Event ticket</div>
        <div id="mp-event-title" class="mp-event-name"></div>
      </div>
      <button type="button" class="mp-event-x" onclick="mpEventTicket.close()" aria-label="Close">&times;</button>
    </div>
    <div id="mp-event-list" class="mp-event-list"></div>
    <div id="mp-event-empty" class="mp-event-empty" hidden>No dates are on sale right now. Please check back soon.</div>
    <div class="mp-event-note">Seats are confirmed at checkout. One ticket = one seat.</div>
  </div>
</div>

<style>
.mp-event-modal{position:fixed;inset:0;z-index:99999;background:rgba(15,23,42,.55);display:flex;align-items:flex-end;justify-content:center;padding:0}
.mp-event-modal[hidden]{display:none}
.mp-event-sheet{background:#fff;color:#0f172a;width:100%;max-width:480px;max-height:70vh;border-radius:16px 16px 0 0;display:flex;flex-direction:column;box-shadow:0 -8px 30px rgba(0,0,0,.25)}
@media(min-width:640px){.mp-event-modal{align-items:center;padding:20px}.mp-event-sheet{border-radius:16px}}
.mp-event-head{display:flex;justify-content:space-between;align-items:flex-start;gap:12px;padding:16px 18px 8px}
.mp-event-kicker{font-size:11px;letter-spacing:.08em;text-transform:uppercase;color:var(--mp-primary,#2563EB);font-weight:700}
.mp-event-name{font-size:16px;font-weight:700;margin-top:2px}
.mp-event-x{border:0;background:none;font-size:24px;line-height:1;cursor:pointer;color:#64748b;padding:2px 6px}
.mp-event-list{overflow-y:auto;padding:6px 12px 10px;display:flex;flex-direction:column;gap:8px}
.mp-event-session{display:flex;justify-content:space-between;align-items:center;gap:12px;width:100%;text-align:left;border:1.5px solid #e2e8f0;background:#fff;border-radius:12px;padding:12px 14px;cursor:pointer;font:inherit}
.mp-event-session:hover,.mp-event-session:focus-visible{border-color:var(--mp-primary,#2563EB)}
.mp-event-date{font-weight:700;font-size:14px}
.mp-event-time{font-size:12px;color:#475569;margin-top:2px}
.mp-event-right{text-align:right;flex:0 0 auto}
.mp-event-seats{font-size:12px;font-weight:600}
.mp-event-seats.ok{color:#16a34a}.mp-event-seats.low{color:#d97706}
.mp-event-price{font-size:12px;color:#475569;margin-top:2px}
.mp-event-empty{padding:18px;text-align:center;color:#64748b;font-size:14px}
.mp-event-note{padding:10px 18px 16px;font-size:11px;color:#94a3b8;border-top:1px solid #f1f5f9}
.mp-event-chosen{display:inline-flex;align-items:center;gap:8px;margin:10px 0;padding:8px 12px;border:1.5px solid var(--mp-primary,#2563EB);color:var(--mp-primary,#2563EB);background:rgba(37,99,235,.06);border-radius:10px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit}
</style>

<script>
(function(){
  var EV = window.MP_EVENTS = window.MP_EVENTS || {};
<?php foreach ($ev_items as $ev): ?>
  EV['<?php echo (int)$ev['id']; ?>'] = {
    name: <?php echo json_encode($ev['name'] ?? 'Event ticket'); ?>,
    price: <?php echo json_encode(round((float)($ev['price'] ?? 0), 2)); ?>,
    sessions: <?php echo json_encode(array_values((array)($ev['sessions'] ?? []))); ?>
  };
<?php endforeach; ?>

  var modal = document.getElementById('mp-event-modal');
  var listEl = document.getElementById('mp-event-list');
  var emptyEl = document.getElementById('mp-event-empty');
  var titleEl = document.getElementById('mp-event-title');
  var pending = null;

  function fmtDate(iso){
    var d = new Date(iso + 'T12:00:00');
    return d.toLocaleDateString(undefined, {weekday:'short', day:'numeric', month:'short', year:'numeric'});
  }
  function money(v){
    try { return (typeof formatMoney === 'function') ? formatMoney(v) : Number(v).toFixed(2); }
    catch(e){ return Number(v).toFixed(2); }
  }
  function openSessions(ev){
    return (ev.sessions || []).filter(function(s){ return s.remaining > 0; });
  }

  function render(ev){
    titleEl.textContent = ev.name;
    listEl.innerHTML = '';
    var avail = openSessions(ev);
    avail.forEach(function(s){
      var btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'mp-event-session';
      var left = document.createElement('div');
      var d = document.createElement('div'); d.className = 'mp-event-date'; d.textContent = fmtDate(s.date);
      left.appendChild(d);
      var t = document.createElement('div'); t.className = 'mp-event-time';
      t.textContent = (s.time ? s.time : 'All day') + (s.venue ? ' · ' + s.venue : '');
      left.appendChild(t);
      var right = document.createElement('div'); right.className = 'mp-event-right';
      var seats = document.createElement('div');
      seats.className = 'mp-event-seats ' + (s.remaining <= 5 ? 'low' : 'ok');
      seats.textContent = s.remaining + (s.remaining === 1 ? ' seat left' : ' seats left');
      right.appendChild(seats);
      var p = document.createElement('div'); p.className = 'mp-event-price'; p.textContent = money(ev.price);
      right.appendChild(p);
      btn.appendChild(left); btn.appendChild(right);
      btn.addEventListener('click', function(){ choose(s); });
      listEl.appendChild(btn);
    });
    emptyEl.hidden = avail.length > 0;
  }

  function choose(session){
    if (pending && pending.invoke) {
      try { pending.invoke(session); } catch(e) {}
    }
    close();
  }

  window.mpEventTicket = {
    open: function(pid, invoke){
      var ev = EV[String(pid)];
      if (!ev) return false;
      pending = {pid: String(pid), invoke: invoke};
      render(ev);
      modal.hidden = false;
      return true;
    },
    close: function(){ modal.hidden = true; pending = null; }
  };
  modal.addEventListener('click', function(e){ if (e.target === modal) window.mpEventTicket.close(); });
  document.addEventListener('keydown', function(e){ if (e.key === 'Escape') window.mpEventTicket.close(); });

  function needsSession(pid){
    var ev = EV[String(pid)];
    return !!(ev && (ev.sessions || []).length);
  }

  // ── Wrap addToCart (built-in PHP themes + the html runtime's shared shape).
  // Function declarations bind to window, so replacing window.addToCart makes
  // every inline onclick in every theme call this first.
  var origAdd = window.addToCart;
  window.addToCart = function(id, type, name, price, image, qty, stock, options){
    var sid = options && options.session_id ? parseInt(options.session_id, 10) : 0;
    if (!needsSession(id)) {
      return origAdd.apply(this, arguments);
    }
    if (!sid) {
      var args = Array.prototype.slice.call(arguments);
      var ok = window.mpEventTicket.open(id, function(session){
        var a = args.slice();
        a[1] = 'product'; // tickets ride the product pipeline; server re-checks
        a[7] = Object.assign({}, a[7] || {}, {session_id: session.id});
        origAdd.apply(window, a);
        setChipLabel(id, session);
      });
      return ok;
    }
    // Re-add through the original with the session visible to line keying.
    var a2 = Array.prototype.slice.call(arguments);
    a2[1] = 'product';
    return origAdd.apply(this, a2);
  };

  // ── Uploaded HTML themes: their runtime adds via data-mp-add-to-cart
  // buttons, not addToCart. Intercept in capture so the button's own handler
  // never fires for ticket items; after a date is picked we call the
  // runtime's add directly (type normalised to product, session attached).
  document.addEventListener('click', function(e){
    var b = e.target && e.target.closest ? e.target.closest('[data-mp-add-to-cart]') : null;
    if (!b) return;
    var pid = b.getAttribute('data-product-id') || (b.dataset && b.dataset.productId);
    if (!pid || !needsSession(pid)) return;
    e.preventDefault(); e.stopPropagation();
    window.mpEventTicket.open(pid, function(session){
      var add = window.mpThemeAddToCart || (window.MartPointTheme && window.MartPointTheme.addToCart);
      if (!add) return;
      var ev = EV[String(pid)];
      add({id: parseInt(pid,10), type: 'product', name: (b.dataset.productName || ev.name),
           price: b.dataset.productPrice || ev.price, image: b.dataset.productImage || '',
           stock: 999999, qty: parseInt(b.dataset.productQty || '1', 10) || 1,
           session_id: session.id});
      setChipLabel(pid, session);
    });
  }, true);

  // ── Detail-page chip: shows the chosen date and reopens the picker.
  function setChipLabel(pid, session){
    var chip = document.querySelector('.mp-event-chosen[data-pid="'+pid+'"]');
    if (!chip) return;
    chip.dataset.sessionId = session.id;
    chip.textContent = 'When: ' + fmtDate(session.date) + (session.time ? ' · ' + session.time : '');
  }
  document.addEventListener('DOMContentLoaded', function(){
    Object.keys(EV).forEach(function(pid){
      var ev = EV[pid];
      if ((ev.sessions || []).length === 0) return;
      var chip = document.createElement('button');
      chip.type = 'button';
      chip.className = 'mp-event-chosen';
      chip.dataset.pid = pid;
      chip.textContent = 'Choose a date';
      chip.addEventListener('click', function(){
        window.mpEventTicket.open(pid, function(session){ setChipLabel(pid, session); });
      });
      // Anchor it to the product detail page, near the bottom of the content.
      var anchor = document.querySelector('.mp-event-root-'+pid);
      if (anchor && anchor.parentElement) {
        anchor.parentElement.insertBefore(chip, anchor);
      } else {
        document.body.appendChild(chip);
        chip.style.position = 'fixed';
        chip.style.left = '50%';
        chip.style.transform = 'translateX(-50%)';
        chip.style.bottom = '86px';
        chip.style.zIndex = '9998';
        chip.style.boxShadow = '0 6px 18px rgba(15,23,42,.25)';
        chip.style.background = '#fff';
      }
      if (ev.sessions.length === 1) {
        // One date only: preselect it so the customer just taps Add.
        setChipLabel(pid, ev.sessions[0]);
      }
    });
  });
})();
</script>
