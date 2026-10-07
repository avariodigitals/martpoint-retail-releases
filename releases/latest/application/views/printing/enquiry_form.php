<?php
/**
 * Printing service enquiry form — cart-free.
 *
 * Printing is a service business: a customer cannot "add a service to cart".
 * They describe the job and it becomes a LEAD, which staff can turn into a
 * print job and then a quotation.
 *
 * Captures the specification a print shop actually needs to quote, and offers
 * the same details by WhatsApp so nothing is lost when email is not used.
 */
$slug = $settings->store_slug ?? '';
$wa   = preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? '');
$svcs = $featured_services ?? [];
// This partial is included from theme views, so obtain the CI instance here
// rather than assuming a $CI variable is already in scope.
$ENQ_CI =& get_instance();
$enq_csrf_name = $ENQ_CI->security->get_csrf_token_name();
$enq_csrf_hash = $ENQ_CI->security->get_csrf_hash();

/**
 * Themes that already provide a card and a heading set $sq_embedded = true
 * before including this partial. Without it the form would draw a second
 * card inside the theme's card — nested boxes, which reads as broken.
 */
$sq_embedded = !empty($sq_embedded);
?>
<style>
/* Colour comes from the host theme when one is present, so the form blends
   with branding instead of imposing its own teal. */
.sq-wrap,.sq-card{--sq-accent:#0e7490;--sq-tint:#e6f4f8}

.sq-wrap{max-width:860px;margin:0 auto}
.sq-card{background:#fff;border:1px solid #e2edf1;border-radius:14px;padding:24px 26px}
.sq-card h2{margin:0 0 6px;font-size:21px;font-weight:800;color:#0f172a;letter-spacing:-.01em}
.sq-sub{margin:0 0 18px;font-size:13.5px;color:#5b7280;line-height:1.65}

/* min(220px,100%) keeps the track from demanding more width than the phone
   has, which is what pushed the panel outside its own box. */
.sq-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(min(220px,100%),1fr));gap:12px}
.sq-f{display:flex;flex-direction:column;gap:5px;min-width:0}
.sq-f label{font-size:11.5px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:.04em}
.sq-f input,.sq-f select,.sq-f textarea{width:100%;max-width:100%;padding:11px 13px;border:1px solid #d7e3e8;border-radius:9px;font-size:14px;font-family:inherit;background:#fff}
.sq-f textarea{min-height:104px;resize:vertical}
.sq-f.full{grid-column:1/-1}
.sq-hint{font-size:11.5px;color:#94a3b8;margin-top:-1px}
.sq-actions{display:flex;gap:10px;flex-wrap:wrap;margin-top:18px}
.sq-btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:12px 22px;border-radius:9px;font-weight:700;font-size:14px;border:none;cursor:pointer;text-decoration:none}
.sq-btn.primary{background:var(--sq-accent);color:#fff}
.sq-btn.primary:hover{filter:brightness(.92)}
/* WhatsApp is the fallback, not a competing brand — restrained, not neon. */
.sq-btn.line{background:#fff;color:var(--sq-accent);border:1px solid color-mix(in srgb,var(--sq-accent) 32%,#fff)}
.sq-btn.line:hover{background:var(--sq-tint)}
.sq-msg{margin-top:14px;padding:11px 14px;border-radius:9px;font-size:13px;display:none}
.sq-msg.ok{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46;display:block}
.sq-msg.err{background:#fef2f2;border:1px solid #fecaca;color:#b91c1c;display:block}
.sq-aside{margin-top:16px;font-size:12.5px;color:#64748b;line-height:1.7}
.sq-aside b{color:#334155}
.sq-chips{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}
/* 44px minimum tap target — these are the primary shortcuts on a phone. */
.sq-chip{font-size:12.5px;background:var(--sq-tint);color:var(--sq-accent);border-radius:999px;padding:12px 16px;border:none;cursor:pointer;min-height:44px;line-height:1.2;display:inline-flex;align-items:center}
.sq-chip:hover{filter:brightness(.95)}

/* Embedded in a theme card: strip our own chrome so there is exactly one box. */
.sq-wrap.is-embedded{max-width:none;margin:0}
.sq-wrap.is-embedded .mp-section{padding:0;margin:0}
.sq-wrap.is-embedded .sq-card{background:none;border:none;border-radius:0;padding:0;box-shadow:none}
/* The host theme supplies its own heading and lede above the form. */
.sq-wrap.is-embedded .sq-card h2,.sq-wrap.is-embedded .sq-sub{display:none}

/* Comfortable touch targets on small screens for the main actions too. */
@media (max-width:640px){
  .sq-btn{width:100%;justify-content:center;min-height:48px}
  .sq-actions{flex-direction:column}
  .sq-f input,.sq-f select,.sq-f textarea{min-height:46px;font-size:16px} /* 16px prevents iOS zoom-on-focus */
  .sq-card{padding:18px 16px}
  .sq-card h2{font-size:19px}
  .sq-grid{grid-template-columns:1fr}
}
</style>

<div class="sq-wrap<?= $sq_embedded ? ' is-embedded' : '' ?>">
  <div class="mp-section">
    <div class="sq-card">
      <h2>Request a printing quote</h2>
      <p class="sq-sub">
        Tell us what you need printed and we will come back with a price and turnaround.
        No account needed — we only ask for what we need to quote accurately.
      </p>

      <form id="sq-form" onsubmit="return sqSubmit(event)">
        <input type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true"
               style="position:absolute;left:-9999px;height:0;opacity:0;">

        <div class="sq-grid">
          <div class="sq-f">
            <label for="sq-name">Your name *</label>
            <input type="text" id="sq-name" name="name" required placeholder="e.g. Daniel Okafor">
          </div>
          <div class="sq-f">
            <label for="sq-phone">Phone / WhatsApp *</label>
            <input type="tel" id="sq-phone" name="phone" placeholder="e.g. 0803 000 0000">
          </div>
          <div class="sq-f">
            <label for="sq-email">Email</label>
            <input type="email" id="sq-email" name="email" placeholder="For the quotation document">
          </div>

          <div class="sq-f">
            <label for="sq-service">What do you need? *</label>
            <select id="sq-service" name="service" required>
              <option value="">Choose a service…</option>
              <?php foreach ($svcs as $s): ?>
              <option value="<?= htmlspecialchars($s->service_name) ?>"><?= htmlspecialchars($s->service_name) ?></option>
              <?php endforeach; ?>
              <option value="Other">Something else</option>
            </select>
          </div>

          <div class="sq-f">
            <label for="sq-qty">Quantity</label>
            <input type="text" id="sq-qty" name="qty" placeholder="e.g. 24 pieces, 3 banners">
          </div>
          <div class="sq-f">
            <label for="sq-size">Size / dimensions</label>
            <input type="text" id="sq-size" name="size" placeholder="e.g. 3m × 1m, A5, 24pp">
          </div>
          <div class="sq-f">
            <label for="sq-material">Material / finish</label>
            <input type="text" id="sq-material" name="material" placeholder="e.g. PVC flex, matte 170gsm">
          </div>
          <div class="sq-f">
            <label for="sq-needed">Needed by</label>
            <input type="date" id="sq-needed" name="needed_by">
          </div>

          <div class="sq-f full">
            <label for="sq-details">Job details</label>
            <textarea id="sq-details" name="details"
              placeholder="Anything else we should know — colour, print positions, artwork status, delivery address…"></textarea>
            <div class="sq-hint">If you already have artwork, mention the file name — you can send the file after we reply.</div>
            <div class="sq-chips">
              <button type="button" class="sq-chip" onclick="sqAppend('I have artwork ready')">I have artwork ready</button>
              <button type="button" class="sq-chip" onclick="sqAppend('I need design help')">I need design help</button>
              <button type="button" class="sq-chip" onclick="sqAppend('Needed urgently')">Needed urgently</button>
              <button type="button" class="sq-chip" onclick="sqAppend('Please advise on material')">Advise on material</button>
            </div>
          </div>
        </div>

        <div class="sq-actions">
          <button type="submit" class="sq-btn primary" id="sq-send">
            <span>Send request</span>
          </button>
          <?php if ($wa !== ''): ?>
          <button type="button" class="sq-btn line" onclick="sqWhatsApp()">
            <span>Send on WhatsApp instead</span>
          </button>
          <?php endif; ?>
        </div>

        <div id="sq-msg" class="sq-msg"></div>

        <div class="sq-aside">
          <b>What happens next:</b> we review your request, confirm the specification,
          and issue a formal quotation with a validity date. Nothing is printed until you approve it.
        </div>
      </form>
    </div>
  </div>
</div>

<script>
function sqAppend(text){
  var t = document.getElementById('sq-details');
  t.value = t.value ? (t.value.replace(/\s+$/,'') + '\n' + text) : text;
  t.focus();
}
function sqCollect(){
  var g = function(id){ var e = document.getElementById(id); return e ? e.value.trim() : ''; };
  return {
    name: g('sq-name'), phone: g('sq-phone'), email: g('sq-email'),
    service: g('sq-service'), qty: g('sq-qty'), size: g('sq-size'),
    material: g('sq-material'), needed_by: g('sq-needed'), details: g('sq-details')
  };
}
function sqMessage(d){
  var lines = ['*Printing request*'];
  if (d.service)   lines.push('Service: ' + d.service);
  if (d.qty)       lines.push('Quantity: ' + d.qty);
  if (d.size)      lines.push('Size: ' + d.size);
  if (d.material)  lines.push('Material: ' + d.material);
  if (d.needed_by) lines.push('Needed by: ' + d.needed_by);
  if (d.details)   lines.push('Details: ' + d.details);
  lines.push('');
  lines.push('Name: ' + (d.name || '-'));
  if (d.phone) lines.push('Phone: ' + d.phone);
  if (d.email) lines.push('Email: ' + d.email);
  return lines.join('\n');
}
function sqShow(kind, text){
  var m = document.getElementById('sq-msg');
  m.className = 'sq-msg ' + kind;
  m.textContent = text;
}
function sqWhatsApp(){
  var d = sqCollect();
  if (!d.name || (!d.phone && !d.email)) { sqShow('err', 'Please add your name and a phone number or email.'); return; }
  var url = 'https://wa.me/<?= $wa ?>?text=' + encodeURIComponent(sqMessage(d));
  window.open(url, '_blank');
}
function sqSubmit(e){
  e.preventDefault();
  var d = sqCollect();
  if (!d.name || (!d.phone && !d.email)) { sqShow('err', 'Please add your name and a phone number or email.'); return false; }
  var btn = document.getElementById('sq-send');
  btn.disabled = true;
  var fd = new FormData();
  for (var k in d) fd.append(k, d[k]);
  fd.append('website', document.querySelector('#sq-form [name=website]').value);
  fd.append('<?= $enq_csrf_name ?>', '<?= $enq_csrf_hash ?>');
  fetch('<?= base_url('store/' . $slug . '/submit_lead') ?>', {
    method:'POST', body: fd, credentials:'same-origin',
    headers:{'X-Requested-With':'XMLHttpRequest'}
  })
  .then(function(r){ return r.json(); })
  .then(function(res){
    btn.disabled = false;
    if (res.status) {
      sqShow('ok', res.message || 'Thank you — we will be in touch shortly.');
      document.getElementById('sq-form').reset();
    } else {
      sqShow('err', res.message || 'Could not send your request. Please try again.');
    }
  })
  .catch(function(){
    btn.disabled = false;
    sqShow('err', 'Request failed. Please try again, or send it on WhatsApp.');
  });
  return false;
}
</script>
