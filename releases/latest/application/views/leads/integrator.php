<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.ig-wrap{display:grid!important;grid-template-columns:minmax(0,1.5fr) minmax(0,1fr)!important;gap:16px!important;align-items:start!important}
.ig-panel{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;overflow:hidden!important;margin-bottom:16px!important}
.ig-panel>h3{margin:0!important;padding:14px 18px!important;border-bottom:1px solid var(--mp-border)!important;font-size:12px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:var(--mp-muted)!important}
.ig-body{padding:16px 18px!important}
.ig-key{display:flex!important;align-items:center!important;gap:10px!important;flex-wrap:wrap!important;padding:12px 14px!important;border:1px dashed var(--mp-border)!important;border-radius:10px!important;background:var(--mp-bg,#f8fafc)!important;font-family:ui-monospace,SFMono-Regular,Menlo,monospace!important;font-size:13px!important;word-break:break-all!important}
.ig-pill{display:inline-flex!important;align-items:center!important;gap:6px!important;padding:5px 11px!important;border-radius:20px!important;font-size:11px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.4px!important}
.ig-pill.ok{background:#D1FAE5!important;color:#065F46!important}
.ig-pill.off{background:#FEE2E2!important;color:#991B1B!important}
.ig-pill.warn{background:#FEF3C7!important;color:#92400E!important}
.ig-code{position:relative!important;margin:0!important}
.ig-code pre{margin:0!important;padding:14px!important;border-radius:10px!important;background:#0f172a!important;color:#e2e8f0!important;font-size:12.5px!important;line-height:1.6!important;overflow:auto!important;max-height:340px!important}
.ig-copy{position:absolute!important;top:10px!important;right:10px!important;padding:6px 12px!important;border-radius:8px!important;border:1px solid rgba(255,255,255,.2)!important;background:rgba(255,255,255,.1)!important;color:#fff!important;font-size:12px!important;font-weight:700!important;cursor:pointer!important}
.ig-copy:hover{background:rgba(255,255,255,.2)!important}
.ig-steps{margin:0!important;padding-left:20px!important;color:var(--mp-muted)!important;font-size:13.5px!important;line-height:1.75!important}
.ig-steps strong{color:var(--mp-text)!important}
.ig-note{padding:12px 14px!important;border-radius:10px!important;border:1px solid var(--mp-border)!important;background:var(--mp-bg,#f8fafc)!important;color:var(--mp-muted)!important;font-size:13px!important;line-height:1.6!important}
.ig-note.warn{border-color:#FDE68A!important;background:#FFFBEB!important;color:#92400E!important}
.ig-tabs{display:flex!important;gap:8px!important;margin-bottom:14px!important;flex-wrap:wrap!important}
.ig-tab{padding:8px 15px!important;border:1px solid var(--mp-border)!important;border-radius:999px!important;background:var(--mp-surface,#fff)!important;color:var(--mp-muted)!important;font-size:13px!important;font-weight:700!important;cursor:pointer!important}
.ig-tab.active{background:var(--mp-primary)!important;border-color:var(--mp-primary)!important;color:#fff!important}
.ig-pane{display:none}
.ig-pane.active{display:block}
@media(max-width:1100px){.ig-wrap{grid-template-columns:minmax(0,1fr)!important}}
</style>

<div class="mp-page-head">
  <div>
    <h2>Booking integrator</h2>
    <div class="mp-page-sub">Put a booking form on your own website. Submissions arrive as leads in this store — nothing is stored on the page.</div>
  </div>
  <div>
    <a class="mp-qa-btn" href="<?= base_url('leads'); ?>"><i class="fa fa-inbox"></i> View leads</a>
  </div>
</div>

<?php if(!$key_set): ?>
<div class="ig-note warn" style="margin-bottom:16px;">
  <strong>No store key yet.</strong> Generate one below, then paste the snippet into your website. Until then the intake endpoint will reject submissions with “Invalid credentials”.
</div>
<?php endif; ?>

<div class="ig-wrap">
  <div>
    <div class="ig-panel">
      <h3>1. Your store key</h3>
      <div class="ig-body">
        <?php if($key_set): ?>
          <div class="ig-key">
            <span id="igKey" data-full="<?= $is_owner ? htmlspecialchars($intake_key) : ''; ?>"><?= htmlspecialchars($key_masked); ?></span>
            <?php if($is_owner): ?>
              <button type="button" class="mp-qa-btn" id="igReveal"><i class="fa fa-eye"></i> Reveal</button>
            <?php endif; ?>
          </div>
          <p style="margin:12px 0 0;color:var(--mp-muted);font-size:12.5px;">
            This key identifies <strong><?= htmlspecialchars($store_name); ?></strong> and only this store.
            Anyone holding it can create leads here, so treat it like a password.
          </p>
        <?php else: ?>
          <div class="ig-key"><span>— not generated —</span></div>
        <?php endif; ?>

        <?php if($is_owner): ?>
        <div style="margin-top:14px;display:flex;gap:10px;flex-wrap:wrap;">
          <button type="button" class="mp-qa-btn green" id="igRotate">
            <i class="fa fa-refresh"></i> <?= $key_set ? 'Rotate key' : 'Generate key'; ?>
          </button>
        </div>
        <p style="margin:10px 0 0;color:var(--mp-muted);font-size:12.5px;">
          Rotating immediately invalidates the old key. Any website still using it will start failing until you paste the new one.
        </p>
        <?php else: ?>
        <p style="margin:14px 0 0;color:var(--mp-muted);font-size:12.5px;">
          Only the store owner can generate or rotate this key.
        </p>
        <?php endif; ?>
      </div>
    </div>

    <div class="ig-panel">
      <h3>2. Paste this into your website</h3>
      <div class="ig-body">
        <div class="ig-tabs">
          <button type="button" class="ig-tab active" data-pane="pane-html">Booking form</button>
          <button type="button" class="ig-tab" data-pane="pane-js">Plain JavaScript</button>
          <button type="button" class="ig-tab" data-pane="pane-react">React</button>
        </div>

        <div class="ig-pane active" id="pane-html">
          <div class="ig-code">
            <button type="button" class="ig-copy" data-target="codeHtml">Copy</button>
            <pre id="codeHtml"></pre>
          </div>
        </div>

        <div class="ig-pane" id="pane-js">
          <div class="ig-code">
            <button type="button" class="ig-copy" data-target="codeJs">Copy</button>
            <pre id="codeJs"></pre>
          </div>
        </div>

        <div class="ig-pane" id="pane-react">
          <div class="ig-code">
            <button type="button" class="ig-copy" data-target="codeReact">Copy</button>
            <pre id="codeReact"></pre>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div>
    <div class="ig-panel">
      <h3>How it works</h3>
      <div class="ig-body">
        <ol class="ig-steps">
          <li>Copy the snippet onto any page — your site, a landing page, a QR code.</li>
          <li>The patient fills it in and submits.</li>
          <li>It lands in <strong>Leads</strong> for this store, marked with the page it came from.</li>
          <li>Convert it to an appointment or a patient from the leads list.</li>
        </ol>
      </div>
    </div>

    <div class="ig-panel">
      <h3>Status</h3>
      <div class="ig-body">
        <p style="margin:0 0 10px;display:flex;align-items:center;gap:10px;font-size:13.5px;">
          <span>Lead intake</span>
          <?php if($leads_enabled): ?>
            <span class="ig-pill ok"><i class="fa fa-check"></i> Enabled</span>
          <?php else: ?>
            <span class="ig-pill off"><i class="fa fa-times"></i> Disabled</span>
          <?php endif; ?>
        </p>
        <?php if(!$leads_enabled): ?>
          <div class="ig-note warn" style="margin-bottom:12px;">
            The <strong>Leads</strong> capability is off for this business type, so the endpoint rejects every submission with a 403. Turn it on in Facility settings before you publish the form.
          </div>
        <?php endif; ?>

        <?php if(!$has_key_column): ?>
          <div class="ig-note warn" style="margin-bottom:12px;">
            This install is missing <code>db_store.intake_key</code> — run the latest migration, then reload this page.
          </div>
        <?php endif; ?>

        <p style="margin:0;font-size:13px;color:var(--mp-muted);line-height:1.7;">
          Endpoint<br><code style="font-size:12.5px;">POST <?= htmlspecialchars($intake_url); ?></code><br><br>
          Header<br><code style="font-size:12.5px;">X-MartPoint-Intake-Key: &lt;your key&gt;</code>
        </p>
      </div>
    </div>

    <div class="ig-panel">
      <h3>Taking payment</h3>
      <div class="ig-body">
        <p class="ig-note" style="margin:0;">
          This form captures a <strong>booking request</strong>; it does not take money.
          Card payment needs a Paystack key, and today that is a standard checkout
          only — there is no virtual-account (dedicated account) support, so you
          cannot yet generate a per-customer account number for a patient to pay
          into and have it swept automatically.
        </p>
      </div>
    </div>
  </div>
</div>

<script>
(function(){
  var KEY   = <?= json_encode($is_owner ? $intake_key : ''); ?>;
  var URL   = <?= json_encode($intake_url); ?>;
  var CRM   = <?= json_encode(base_url()); ?>;
  var STORE = <?= json_encode($store_name); ?>;

  function snippetHtml(){
    return [
'<!-- ' + STORE + ' — booking form (MartPoint) -->',
'<form id="mp-booking" class="mp-booking">',
'  <input name="name"        placeholder="Full name" required>',
'  <input name="phone"       placeholder="Phone / WhatsApp" required>',
'  <input name="email"       type="email" placeholder="Email (optional)">',
'  <textarea name="enquiry"  placeholder="What do you need help with?"></textarea>',
'  <input name="preferred_date" type="date" placeholder="Preferred date">',
'  <!-- honeypot: leave empty, hidden from humans -->',
'  <input name="company_website" tabindex="-1" autocomplete="off"',
'         style="position:absolute;left:-9999px" aria-hidden="true">',
'  <button type="submit">Request an appointment</button>',
'  <p id="mp-booking-status" role="status" aria-live="polite"></p>',
'</form>',
'',
'<script>',
'(function(){',
'  var f = document.getElementById("mp-booking");',
'  var s = document.getElementById("mp-booking-status");',
'  f.addEventListener("submit", function(e){',
'    e.preventDefault();',
'    s.textContent = "Sending…";',
'    var d = new FormData(f);',
'    // Must be unique per submission — it is what makes a retry safe.',
'    d.append("submission_ref", "web-" + Date.now() + "-" + Math.random().toString(36).slice(2,8));',
'    fetch(' + JSON.stringify(URL) + ', {',
'      method: "POST",',
'      headers: { "X-MartPoint-Intake-Key": ' + JSON.stringify(KEY) + ' },',
'      body: d',
'    }).then(function(r){ return r.json(); }).then(function(res){',
'      if(res && res.status === "received"){',
'        f.reset();',
'        s.textContent = "Thank you — we will be in touch shortly.";',
'      } else {',
'        s.textContent = (res && res.message) || "Sorry, that did not go through.";',
'      }',
'    }).catch(function(){ s.textContent = "Network problem — please try again."; });',
'  });',
'})();',
'<\/script>'
    ].join('\n');
  }

  function snippetJs(){
    return [
'// ' + STORE + ' — booking (MartPoint)',
'const BOOKINGS = {',
'  url: ' + JSON.stringify(URL) + ',',
'  key: ' + JSON.stringify(KEY) + ',',
'};',
'',
'async function requestAppointment({ name, phone, email, enquiry, preferred_date }) {',
'  const body = new FormData();',
'  body.append("name", name);',
'  body.append("phone", phone || "");',
'  body.append("email", email || "");',
'  body.append("enquiry", enquiry || "");',
'  if (preferred_date) body.append("preferred_date", preferred_date);',
'  body.append("submission_ref", "web-" + Date.now() + "-" + Math.random().toString(36).slice(2, 8));',
'',
'  const res = await fetch(BOOKINGS.url, {',
'    method: "POST",',
'    headers: { "X-MartPoint-Intake-Key": BOOKINGS.key },',
'    body,',
'  });',
'  return res.json();   // { status: "received", lead_id }',
'}'
    ].join('\n');
  }

  function snippetReact(){
    return [
'// ' + STORE + ' — booking (MartPoint)',
'import { useState } from "react";',
'',
'const URL = ' + JSON.stringify(URL) + ';',
'const KEY = ' + JSON.stringify(KEY) + ';',
'',
'export default function BookingForm() {',
'  const [state, setState] = useState("idle");',
'',
'  async function submit(e) {',
'    e.preventDefault();',
'    setState("sending");',
'    const d = new FormData(e.currentTarget);',
'    d.append("submission_ref", "web-" + Date.now() + "-" + Math.random().toString(36).slice(2, 8));',
'    try {',
'      const r = await fetch(URL, {',
'        method: "POST",',
'        headers: { "X-MartPoint-Intake-Key": KEY },',
'        body: d,',
'      });',
'      const res = await r.json();',
'      setState(res.status === "received" ? "done" : "error");',
'    } catch {',
'      setState("error");',
'    }',
'  }',
'',
'  return (',
'    <form onSubmit={submit}>',
'      <input name="name"  placeholder="Full name" required />',
'      <input name="phone" placeholder="Phone / WhatsApp" required />',
'      <input name="email" type="email" placeholder="Email (optional)" />',
'      <textarea name="enquiry" placeholder="What do you need help with?" />',
'      <button disabled={state === "sending"}>Request an appointment</button>',
'      {state === "done"  && <p>Thank you — we will be in touch shortly.</p>}',
'      {state === "error" && <p>Sorry, that did not go through.</p>}',
'    </form>',
'  );',
'}'
    ].join('\n');
  }

  function render(){
    var h = document.getElementById("codeHtml");
    var j = document.getElementById("codeJs");
    var r = document.getElementById("codeReact");
    if(h) h.textContent = snippetHtml();
    if(j) j.textContent = snippetJs();
    if(r) r.textContent = snippetReact();
  }
  render();

  var reveal = document.getElementById("igReveal");
  if(reveal){
    reveal.addEventListener("click", function(){
      var el = document.getElementById("igKey");
      var full = el.getAttribute("data-full") || "";
      if(!full) return;
      var showing = el.textContent.trim() === full;
      el.textContent = showing ? (full.slice(0,6) + "••••••••••••••••••" + full.slice(-4)) : full;
      reveal.innerHTML = showing ? '<i class="fa fa-eye"></i> Reveal' : '<i class="fa fa-eye-slash"></i> Hide';
    });
  }

  var rotate = document.getElementById("igRotate");
  if(rotate){
    rotate.addEventListener("click", function(){
      if(!confirm("Generate a new key? Any website still using the current one will stop working until you update it.")) return;
      rotate.disabled = true;
      var fd = new FormData();
      fd.append(window.csrfName || "csrf_test_name", window.csrfHash || "");
      fetch(window.location.href, { method: "POST", body: fd, headers: { "X-Requested-With": "XMLHttpRequest" } })
        .then(function(r){ return r.json(); })
        .then(function(res){
          rotate.disabled = false;
          if(res.status === "success"){ location.reload(); }
          else { alert(res.message || "Could not generate a key."); }
        })
        .catch(function(){ rotate.disabled = false; alert("Could not generate a key."); });
    });
  }

  document.querySelectorAll(".ig-tab").forEach(function(tab){
    tab.addEventListener("click", function(){
      document.querySelectorAll(".ig-tab").forEach(function(t){ t.classList.remove("active"); });
      document.querySelectorAll(".ig-pane").forEach(function(p){ p.classList.remove("active"); });
      tab.classList.add("active");
      var pane = document.getElementById(tab.getAttribute("data-pane"));
      if(pane) pane.classList.add("active");
    });
  });

  document.querySelectorAll(".ig-copy").forEach(function(btn){
    btn.addEventListener("click", function(){
      var pre = document.getElementById(btn.getAttribute("data-target"));
      if(!pre) return;
      navigator.clipboard.writeText(pre.textContent).then(function(){
        var old = btn.textContent;
        btn.textContent = "Copied";
        setTimeout(function(){ btn.textContent = old; }, 1500);
      });
    });
  });
})();
</script>
