<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <title>Verify | <?= htmlspecialchars($store->store_name ?? 'Store'); ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root { --primary:#3B82F6; --primary-dark:#2563EB; --success:#059669; --danger:#EF4444; --dark:#0F172A; --gray:#64748B; --light-gray:#F1F5F9; --border:#E2E8F0; --white:#fff; --radius:16px; --radius-sm:10px; }
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Inter',sans-serif; background:#F8FAFC; color:var(--dark); -webkit-font-smoothing:antialiased; }
    a { text-decoration:none; color:inherit; }
    .sf-header { background:var(--white); border-bottom:1px solid var(--border); position:sticky; top:0; z-index:100; }
    .sf-header-inner { max-width:640px; margin:0 auto; padding:14px 16px; display:flex; align-items:center; gap:12px; }
    .sf-back { font-size:22px; color:var(--dark); display:flex; align-items:center; }
    .sf-header-title { font-size:16px; font-weight:700; flex:1; }
    .sf-section { max-width:640px; margin:0 auto; padding:32px 16px; }
    .sf-card { background:var(--white); border-radius:var(--radius); border:1px solid var(--border); padding:28px; }
    .sf-title { font-size:22px; font-weight:800; margin-bottom:6px; }
    .sf-subtitle { font-size:14px; color:var(--gray); margin-bottom:24px; line-height:1.5; }
    .sf-tabs { display:flex; gap:8px; margin-bottom:20px; background:var(--light-gray); padding:4px; border-radius:var(--radius-sm); }
    .sf-tab { flex:1; padding:10px; border-radius:8px; border:none; background:transparent; cursor:pointer; font-weight:600; font-size:14px; color:var(--gray); }
    .sf-tab.active { background:var(--white); color:var(--dark); box-shadow:0 1px 3px rgba(0,0,0,0.08); }
    .sf-label { font-size:13px; font-weight:600; color:var(--gray); margin-bottom:6px; display:block; }
    .sf-input { width:100%; padding:14px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:15px; margin-bottom:16px; outline:none; }
    .sf-input:focus { border-color:var(--primary); }
    .sf-btn { width:100%; padding:15px; border-radius:var(--radius-sm); background:var(--primary); color:#fff; font-weight:700; border:none; cursor:pointer; font-size:16px; display:flex; align-items:center; justify-content:center; gap:8px; }
    .sf-btn:disabled { background:#CBD5E1; cursor:not-allowed; }
    .sf-btn-secondary { background:var(--white); color:var(--dark); border:1px solid var(--border); margin-top:12px; }
    .sf-footer-note { text-align:center; font-size:13px; color:var(--gray); margin-top:20px; }
    .sf-footer-note a { color:var(--primary); font-weight:600; }
    .sf-error { color:var(--danger); font-size:13px; margin-top:-8px; margin-bottom:12px; display:none; }
    .sf-otp { display:none; }
    .hidden { display:none; }
  </style>
</head>
<body>

<div class="sf-header">
  <div class="sf-header-inner">
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>" class="sf-back">&#8592;</a>
    <div class="sf-header-title">My Account</div>
  </div>
</div>

<div class="sf-section">
  <div class="sf-card">
    <div class="sf-title">Sign In</div>
    <div class="sf-subtitle" id="step-subtitle">Choose how you'd like to receive your one-time code and view your order history.</div>

    <div class="sf-tabs" id="method-tabs">
      <button type="button" class="sf-tab active" id="tab-phone" onclick="setMethod('phone')">Phone</button>
      <button type="button" class="sf-tab" id="tab-email" onclick="setMethod('email')">Email</button>
    </div>

    <div id="phone-step">
      <div id="phone-fields">
        <label class="sf-label">Phone Number</label>
        <input type="tel" class="sf-input" id="phone" value="<?= htmlspecialchars($prefill_phone ?? ''); ?>" placeholder="08012345678">
      </div>

      <div id="email-fields" class="hidden">
        <label class="sf-label">Full Name</label>
        <input type="text" class="sf-input" id="name" placeholder="John Doe">
        <label class="sf-label">Email Address</label>
        <input type="email" class="sf-input" id="email" placeholder="john@example.com">
      </div>

      <input type="hidden" id="csrf-name" value="<?= $csrf_name ?? ''; ?>">
      <input type="hidden" id="csrf-hash" value="<?= $csrf_hash ?? ''; ?>">

      <div class="sf-error" id="contact-error"></div>
      <button class="sf-btn" id="send-otp-btn" onclick="sendOtp()">Send OTP</button>
    </div>

    <div id="otp-step" class="sf-otp">
      <label class="sf-label">6-Digit Code</label>
      <input type="text" class="sf-input" id="otp" maxlength="6" placeholder="000000" inputmode="numeric">
      <div class="sf-error" id="otp-error"></div>
      <button class="sf-btn" id="verify-otp-btn" onclick="verifyOtp()">Verify &amp; Continue</button>
      <button class="sf-btn sf-btn-secondary" id="resend-btn" onclick="sendOtp()">Resend Code</button>
    </div>

    <div class="sf-footer-note">
      <a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>">Continue as guest</a>
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
    document.querySelectorAll('.sf-error').forEach(el => { el.textContent = ''; el.style.display = 'none'; });
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
          showError('contact-error', res.message || 'Could not send OTP');
          enableBtn('send-otp-btn', 'Send OTP');
        }
      })
      .catch(() => { showError('contact-error', 'Network error. Try again.'); enableBtn('send-otp-btn', 'Send OTP'); });
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
