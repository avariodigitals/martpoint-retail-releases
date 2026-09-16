<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <title>Sign In | <?= htmlspecialchars($store->store_name ?? 'Store'); ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
  <?php $primary = $settings->primary_color ?? '#7C3AED'; $primaryDark = $settings->primary_dark_color ?? '#6D28D9'; ?>
  <style>
    :root { --primary:<?= $primary;?>; --primary-dark:<?= $primaryDark;?>; --success:#059669; --danger:#EF4444; --dark:#0F172A; --gray:#64748B; --light-gray:#F1F5F9; --border:#E2E8F0; --white:#fff; --radius:20px; --radius-sm:12px; }
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Inter',sans-serif; color:var(--dark); -webkit-font-smoothing:antialiased; background:#F8FAFC; }
    a { text-decoration:none; color:inherit; }

    .crv-wrapper { min-height:100vh; display:grid; grid-template-columns:1fr 1.2fr; }
    .crv-hero { background:linear-gradient(135deg, var(--primary), var(--primary-dark)); color:#fff; padding:48px 44px; display:flex; flex-direction:column; justify-content:center; position:relative; overflow:hidden; }
    .crv-hero::before { content:''; position:absolute; top:-120px; right:-120px; width:320px; height:320px; border-radius:50%; background:rgba(255,255,255,.08); }
    .crv-hero::after { content:''; position:absolute; bottom:-80px; left:-80px; width:280px; height:280px; border-radius:50%; background:rgba(255,255,255,.06); }
    .crv-hero-content { position:relative; z-index:1; max-width:420px; }
    .crv-logo { margin-bottom:28px; }
    .crv-logo img { max-height:48px; max-width:160px; object-fit:contain; }
    .crv-logo .crv-logo-text { font-size:24px; font-weight:900; display:flex; align-items:center; gap:10px; }
    .crv-hero h1 { font-size:clamp(30px,3.5vw,42px); font-weight:900; line-height:1.08; margin-bottom:16px; }
    .crv-hero p { font-size:16px; line-height:1.6; opacity:.92; margin-bottom:32px; }
    .crv-hero-list { display:flex; flex-direction:column; gap:14px; }
    .crv-hero-list div { display:flex; align-items:center; gap:12px; font-size:14px; opacity:.95; }
    .crv-hero-list span { width:26px; height:26px; border-radius:50%; background:rgba(255,255,255,.18); display:flex; align-items:center; justify-content:center; font-size:12px; font-weight:800; }

    .crv-panel { display:flex; align-items:center; justify-content:center; padding:48px 24px; }
    .crv-card { background:var(--white); border-radius:var(--radius); box-shadow:0 24px 64px rgba(15,23,42,.08); width:100%; max-width:440px; padding:40px; }
    .crv-card-top { text-align:center; margin-bottom:28px; }
    .crv-card-top .crv-avatar { width:56px; height:56px; border-radius:16px; background:var(--primary); color:#fff; display:flex; align-items:center; justify-content:center; font-size:22px; font-weight:800; margin:0 auto 16px; }
    .crv-card-top h2 { font-size:24px; font-weight:900; margin-bottom:8px; }
    .crv-card-top p { font-size:14px; color:var(--gray); line-height:1.6; }

    .crv-tabs { display:flex; gap:8px; margin-bottom:24px; background:var(--light-gray); padding:4px; border-radius:var(--radius-sm); }
    .crv-tab { flex:1; padding:12px; border-radius:10px; border:none; background:transparent; cursor:pointer; font-weight:700; font-size:14px; color:var(--gray); transition:all .15s; }
    .crv-tab.active { background:var(--white); color:var(--dark); box-shadow:0 2px 8px rgba(0,0,0,.06); }

    .crv-label { font-size:13px; font-weight:700; color:var(--dark); margin-bottom:8px; display:block; }
    .crv-input { width:100%; padding:14px 16px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:15px; margin-bottom:18px; outline:none; transition:border .15s, box-shadow .15s; }
    .crv-input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(124,58,237,.1); }
    .crv-error { color:var(--danger); font-size:13px; margin-top:-10px; margin-bottom:14px; display:none; }
    .crv-btn { width:100%; padding:15px; border-radius:var(--radius-sm); background:var(--primary); color:#fff; font-weight:800; border:none; cursor:pointer; font-size:15px; display:flex; align-items:center; justify-content:center; gap:8px; transition:background .15s, transform .1s; }
    .crv-btn:hover { background:var(--primary-dark); }
    .crv-btn:disabled { background:#CBD5E1; cursor:not-allowed; }
    .crv-btn-secondary { background:var(--white); color:var(--dark); border:1px solid var(--border); margin-top:12px; }
    .crv-otp { display:none; }
    .hidden { display:none; }
    .crv-footer { text-align:center; font-size:13px; color:var(--gray); margin-top:24px; }
    .crv-footer a { color:var(--primary); font-weight:700; }

    @media(max-width:900px){
      .crv-wrapper { grid-template-columns:1fr; }
      .crv-hero { display:none; }
      .crv-panel { padding:40px 16px; }
      .crv-card { padding:32px 24px; box-shadow:0 12px 40px rgba(15,23,42,.06); }
    }
  </style>
</head>
<body>

<div class="crv-wrapper">
  <div class="crv-hero">
    <div class="crv-hero-content">
      <div class="crv-logo">
        <?php if(!empty($settings->store_logo) && file_exists($settings->store_logo)): ?>
          <img src="<?= base_url($settings->store_logo); ?>" alt="<?= htmlspecialchars($store->store_name ?? 'Store'); ?>">
        <?php else: ?>
          <div class="crv-logo-text">
            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
            <?= htmlspecialchars($store->store_name ?? 'Store'); ?>
          </div>
        <?php endif; ?>
      </div>
      <h1>Access your library</h1>
      <p>Sign in to see your courses, downloads, memberships and order history in one place.</p>
      <div class="crv-hero-list">
        <div><span>✓</span>Instant access to purchased content</div>
        <div><span>✓</span>Track progress and resume courses</div>
        <div><span>✓</span>Download files and manage memberships</div>
        <div><span>✓</span>One-time code, no password needed</div>
      </div>
    </div>
  </div>

  <div class="crv-panel">
    <div class="crv-card">
      <div class="crv-card-top">
        <div class="crv-avatar">&#128274;</div>
        <h2>Sign In to Your Account</h2>
        <p id="step-subtitle">Choose how you'd like to receive your one-time code and view your order history.</p>
      </div>

      <div class="crv-tabs" id="method-tabs">
        <button type="button" class="crv-tab active" id="tab-phone" onclick="setMethod('phone')">Phone</button>
        <button type="button" class="crv-tab" id="tab-email" onclick="setMethod('email')">Email</button>
      </div>

      <div id="phone-step">
        <div id="phone-fields">
          <label class="crv-label">Phone Number</label>
          <input type="tel" class="crv-input" id="phone" value="<?= htmlspecialchars($prefill_phone ?? ''); ?>" placeholder="08012345678">
        </div>

        <div id="email-fields" class="hidden">
          <label class="crv-label">Full Name</label>
          <input type="text" class="crv-input" id="name" placeholder="John Doe">
          <label class="crv-label">Email Address</label>
          <input type="email" class="crv-input" id="email" placeholder="john@example.com">
        </div>

        <input type="hidden" id="csrf-name" value="<?= $csrf_name ?? ''; ?>">
        <input type="hidden" id="csrf-hash" value="<?= $csrf_hash ?? ''; ?>">

        <div class="crv-error" id="contact-error"></div>
        <button class="crv-btn" id="send-otp-btn" onclick="sendOtp()">Send Code</button>
      </div>

      <div id="otp-step" class="crv-otp">
        <label class="crv-label">6-Digit Code</label>
        <input type="text" class="crv-input" id="otp" maxlength="6" placeholder="000000" inputmode="numeric">
        <div class="crv-error" id="otp-error"></div>
        <button class="crv-btn" id="verify-otp-btn" onclick="verifyOtp()">Verify &amp; Continue</button>
        <button class="crv-btn crv-btn-secondary" id="resend-btn" onclick="sendOtp()">Resend Code</button>
      </div>

      <div class="crv-footer">
        <a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>">Continue as guest</a>
      </div>
    </div>
  </div>
</div>

<script>
  const STORE_SLUG = '<?= $settings->store_slug ?? ''; ?>';
  const CSRF_NAME = document.getElementById('csrf-name').value;
  const CSRF_HASH = document.getElementById('csrf-hash').value;
  let currentMethod = 'phone';

  function showError(el, msg){
    const e = document.getElementById(el);
    e.textContent = msg; e.style.display = 'block';
  }
  function clearErrors(){
    document.querySelectorAll('.crv-error').forEach(el => { el.textContent = ''; el.style.display = 'none'; });
  }
  function disableBtn(id, text){
    const b = document.getElementById(id);
    b.disabled = true; b.textContent = text;
  }
  function enableBtn(id, text){
    const b = document.getElementById(id);
    b.disabled = false; b.textContent = text;
  }

  function setMethod(method){
    currentMethod = method;
    document.getElementById('tab-phone').classList.toggle('active', method === 'phone');
    document.getElementById('tab-email').classList.toggle('active', method === 'email');
    document.getElementById('phone-fields').classList.toggle('hidden', method !== 'phone');
    document.getElementById('email-fields').classList.toggle('hidden', method !== 'email');
    document.getElementById('step-subtitle').textContent = method === 'email' ? 'Enter your name and email to receive a one-time code.' : 'Enter your phone number to receive a one-time code.';
  }

  function postData(url, body){
    const data = new URLSearchParams(body);
    if(CSRF_NAME && CSRF_HASH) data.append(CSRF_NAME, CSRF_HASH);
    return fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: data.toString()
    }).then(r => r.json());
  }

  function getPostBody(){
    const body = { method: currentMethod };
    if(currentMethod === 'email'){
      body.name = document.getElementById('name').value.trim();
      body.email = document.getElementById('email').value.trim();
    } else {
      body.phone = document.getElementById('phone').value.trim();
    }
    return body;
  }

  function sendOtp(){
    clearErrors();
    const body = getPostBody();

    if(currentMethod === 'email'){
      if(!body.name){ showError('contact-error', 'Enter your name'); return; }
      if(!body.email || !body.email.includes('@')){ showError('contact-error', 'Enter a valid email address'); return; }
    } else {
      if(body.phone.length < 7){ showError('contact-error', 'Enter a valid phone number'); return; }
    }

    disableBtn('send-otp-btn', 'Sending...');
    postData('<?= base_url('store/'); ?>' + STORE_SLUG + '/send_otp', body)
      .then(res => {
        if(res.csrf_hash) document.getElementById('csrf-hash').value = res.csrf_hash;
        if(res.status){
          document.getElementById('phone-step').style.display = 'none';
          document.getElementById('method-tabs').style.display = 'none';
          document.getElementById('otp-step').style.display = 'block';
        } else {
          showError('contact-error', res.message || 'Could not send code');
          enableBtn('send-otp-btn', 'Send Code');
        }
      })
      .catch(() => { showError('contact-error', 'Network error. Try again.'); enableBtn('send-otp-btn', 'Send Code'); });
  }

  function verifyOtp(){
    clearErrors();
    const body = getPostBody();
    body.otp = document.getElementById('otp').value.trim();
    if(body.otp.length !== 6){ showError('otp-error', 'Enter the 6-digit code'); return; }

    disableBtn('verify-otp-btn', 'Verifying...');
    postData('<?= base_url('store/'); ?>' + STORE_SLUG + '/verify_otp', body)
      .then(res => {
        if(res.csrf_hash) document.getElementById('csrf-hash').value = res.csrf_hash;
        if(res.status){
          window.location.href = '<?= base_url('store/'); ?>' + STORE_SLUG + '/account';
        } else {
          showError('otp-error', res.message || 'Invalid code');
          enableBtn('verify-otp-btn', 'Verify & Continue');
        }
      })
      .catch(() => { showError('otp-error', 'Network error. Try again.'); enableBtn('verify-otp-btn', 'Verify & Continue'); });
  }
</script>

</body>
</html>
