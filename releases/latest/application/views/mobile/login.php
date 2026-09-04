<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Sign In</title>
  <link rel='shortcut icon' href='<?= base_url('uploads/site/icon.webp'); ?>'>
  <link rel="manifest" href="<?= base_url('manifest.json'); ?>">
  <meta name="theme-color" content="#0B1120">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="MartPoint">
  <link rel="apple-touch-icon" href="<?= base_url('uploads/site/icon.webp'); ?>">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root {
      --mp-primary: #0057FF;
      --mp-primary-dark: #0044CC;
      --mp-bg: #F8FAFC;
      --mp-surface: #FFFFFF;
      --mp-text: #0F172A;
      --mp-muted: #64748B;
      --mp-border: #E2E8F0;
      --mp-success: #10B981;
      --mp-danger: #EF4444;
      --safe-bottom: env(safe-area-inset-bottom, 0px);
      --safe-top: env(safe-area-inset-top, 0px);
    }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: #E0F2FE; color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }

    #app {
      width: 100%;
      max-width: none;
      margin: 0 auto;
      min-height: 100vh;
      min-height: 100dvh;
      display: flex;
      flex-direction: column;
      background: linear-gradient(180deg, #FFFFFF 0%, #F0F9FF 35%, #E0F2FE 100%);
      position: relative;
      overflow-x: hidden;
    }

    /* ── Topbar ── */
    .topbar {
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      padding: 16px 20px calc(8px + var(--safe-top));
      text-align: center;
      background: var(--mp-surface);
      border-bottom: 1px solid var(--mp-border);
    }
    .topbar .store-name {
      font-size: 11px;
      color: var(--mp-muted);
      font-weight: 600;
      text-transform: uppercase;
      letter-spacing: 0.5px;
      margin-bottom: 2px;
    }
    .topbar h1 {
      font-size: 18px;
      font-weight: 700;
      margin: 0;
      color: var(--mp-text);
    }

    /* ── Main content ── */
    .screen {
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 24px 20px 20px;
      width: 100%;
      position: relative;
      z-index: 1;
    }

    .brand-hero {
      text-align: center;
      margin-bottom: 36px;
    }
    .brand-hero img {
      display: block;
      width: min(70vw, 180px);
      height: auto;
      margin: 0 auto;
      border-radius: 0;
      box-shadow: none;
    }

    .login-card {
      background: var(--mp-surface);
      border: 1px solid var(--mp-border);
      border-radius: 20px;
      padding: 24px 20px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 8px 30px rgba(0,0,0,0.04);
      width: 100%;
      max-width: 460px;
      margin: 0 auto;
    }
    .login-card h2 {
      font-size: 22px;
      font-weight: 700;
      margin: 0 0 6px;
      color: var(--mp-text);
      letter-spacing: -0.2px;
    }
    .login-card .lead {
      font-size: 14px;
      color: var(--mp-muted);
      margin: 0 0 22px;
    }

    .alert {
      padding: 12px 14px;
      border-radius: 12px;
      font-size: 13px;
      font-weight: 500;
      margin-bottom: 16px;
    }
    .alert-danger { background: #FEF2F2; color: #B91C1C; border: 1px solid #FECACA; }
    .alert-success { background: #ECFDF5; color: #065F46; border: 1px solid #A7F3D0; }

    .form-group { margin-bottom: 18px; }
    .form-group label {
      display: block;
      font-size: 13px;
      font-weight: 600;
      margin-bottom: 6px;
      color: var(--mp-text);
    }
    .input-wrap {
      position: relative;
      display: flex;
      align-items: center;
    }
    .input-wrap .icon-left {
      position: absolute;
      left: 14px;
      color: var(--mp-muted);
      font-size: 16px;
      pointer-events: none;
    }
    .input-wrap .toggle-pw {
      position: absolute;
      right: 10px;
      top: 50%;
      transform: translateY(-50%);
      width: 40px;
      height: 40px;
      display: flex;
      align-items: center;
      justify-content: center;
      background: transparent;
      border: none;
      color: var(--mp-muted);
      font-size: 16px;
      cursor: pointer;
    }
    .form-control {
      width: 100%;
      min-height: 54px;
      padding: 14px 46px 14px 44px;
      border: 1.5px solid var(--mp-border);
      border-radius: 14px;
      font-size: 16px;
      font-family: inherit;
      background: #fff;
      color: var(--mp-text);
      outline: none;
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    .form-control:focus {
      border-color: var(--mp-primary);
      box-shadow: 0 0 0 3px rgba(0,87,255,0.10);
    }
    .form-control::placeholder { color: #94A3B8; }

    .options {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin: -4px 0 20px;
      font-size: 13px;
    }
    .remember {
      display: flex;
      align-items: center;
      gap: 8px;
      color: var(--mp-text);
      font-weight: 500;
      cursor: pointer;
    }
    .remember input {
      width: 18px;
      height: 18px;
      accent-color: var(--mp-primary);
      cursor: pointer;
    }
    .forgot {
      color: var(--mp-primary);
      text-decoration: none;
      font-weight: 600;
    }
    .forgot:active { opacity: 0.8; }

    .btn-signin {
      width: 100%;
      min-height: 54px;
      border: none;
      border-radius: 14px;
      background: var(--mp-primary);
      color: #fff;
      font-size: 16px;
      font-weight: 700;
      cursor: pointer;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      transition: background 0.2s, transform 0.15s;
      box-shadow: 0 4px 14px rgba(0,87,255,0.25);
    }
    .btn-signin:active { transform: translateY(1px); opacity: 0.95; }
    .btn-signin:disabled { opacity: 0.7; cursor: wait; }

    .login-footer {
      text-align: center;
      margin-top: 18px;
      font-size: 13px;
      color: var(--mp-muted);
    }
    .login-footer a { color: var(--mp-primary); font-weight: 600; text-decoration: none; }

    /* ── Demo box ── */
    .demo-box {
      margin-top: 24px;
      width: 100%;
    }
    .demo-box .demo-label {
      font-size: 11px;
      font-weight: 700;
      color: var(--mp-muted);
      text-transform: uppercase;
      letter-spacing: 0.6px;
      margin-bottom: 8px;
      display: block;
    }
    .demo-table {
      width: 100%;
      background: var(--mp-surface);
      border: 1px solid var(--mp-border);
      border-radius: 14px;
      border-collapse: collapse;
      overflow: hidden;
      font-size: 12px;
    }
    .demo-table td { padding: 10px 12px; border-bottom: 1px solid var(--mp-border); color: var(--mp-text); }
    .demo-table tr:last-child td { border-bottom: none; }
    .demo-table .copy {
      padding: 5px 10px;
      border: 1px solid #BFDBFE;
      border-radius: 8px;
      background: #EFF6FF;
      color: var(--mp-primary);
      font-size: 11px;
      font-weight: 600;
      cursor: pointer;
    }
    .demo-note {
      font-size: 11px;
      color: var(--mp-muted);
      margin-top: 8px;
    }

    /* ── Screen-wide copyright footer ── */
    .copyright-footer {
      width: 100%;
      position: relative;
      z-index: 1;
      text-align: center;
      padding: 12px 20px calc(14px + var(--safe-bottom));
      font-size: 11px;
      color: #64748B;
      margin-top: auto;
    }

    .mp-login-sky {
      position: absolute;
      top: 0; left: 0; right: 0; bottom: 0;
      pointer-events: none;
      z-index: 0;
      overflow: hidden;
    }
    .mp-login-sky .cloud {
      position: absolute;
      border-radius: 50%;
      background: radial-gradient(ellipse at center, rgba(255,255,255,0.6) 0%, rgba(219,234,254,0.3) 40%, transparent 70%);
      filter: blur(20px);
    }
    .mp-login-sky .cloud.c1 {
      width: 300px; height: 120px;
      top: 5%; left: 5%;
      animation: cloudDrift 25s ease-in-out infinite alternate;
    }
    .mp-login-sky .cloud.c2 {
      width: 220px; height: 90px;
      top: 12%; left: 35%;
      animation: cloudDrift 30s ease-in-out infinite alternate-reverse;
    }
    .mp-login-sky .cloud.c3 {
      width: 260px; height: 100px;
      top: 8%; right: 15%;
      animation: cloudDrift 22s ease-in-out infinite alternate;
    }
    .mp-login-sky .cloud.c4 {
      width: 180px; height: 70px;
      top: 18%; right: 5%;
      animation: cloudDrift 28s ease-in-out infinite alternate-reverse;
    }
    .mp-login-sky .bird {
      position: absolute;
      opacity: 0.45;
      will-change: transform;
    }
    .mp-login-sky .bird svg {
      width: 100%; height: 100%;
      fill: #1E3A5F;
      display: block;
    }
    .mp-login-sky .bird.b1 { top: 6%;  left: -5%; width: 22px; height: 14px; animation: birdFly1 20s linear infinite; }
    .mp-login-sky .bird.b2 { top: 10%; left: -5%; width: 18px; height: 11px; animation: birdFly2 24s linear infinite; animation-delay: -3s; }
    .mp-login-sky .bird.b3 { top: 4%;  left: -5%; width: 16px; height: 10px; animation: birdFly3 18s linear infinite; animation-delay: -7s; }
    .mp-login-sky .bird.b4 { top: 13%; left: -5%; width: 20px; height: 13px; animation: birdFly4 22s linear infinite; animation-delay: -10s; }
    .mp-login-sky .bird.b5 { top: 8%;  left: -5%; width: 14px; height: 9px;  animation: birdFly1 26s linear infinite; animation-delay: -5s; }
    .mp-login-sky .bird.b6 { top: 11%; left: -5%; width: 17px; height: 11px; animation: birdFly3 19s linear infinite; animation-delay: -12s; }
    .mp-login-sky .bird.b7 { top: 5%;  left: -5%; width: 19px; height: 12px; animation: birdFly2 21s linear infinite; animation-delay: -15s; }
    .mp-login-sky .bird.b8 { top: 9%;  left: -5%; width: 15px; height: 10px; animation: birdFly4 23s linear infinite; animation-delay: -8s; }

    @keyframes cloudDrift {
      0%   { transform: translateX(0) translateY(0); }
      100% { transform: translateX(40px) translateY(-15px); }
    }
    @keyframes birdFly1 {
      0%   { transform: translateX(0) translateY(0) scaleX(1); }
      20%  { transform: translateX(25vw) translateY(-8px) scaleX(1); }
      40%  { transform: translateX(50vw) translateY(4px) scaleX(1); }
      60%  { transform: translateX(75vw) translateY(-4px) scaleX(1); }
      80%  { transform: translateX(95vw) translateY(2px) scaleX(1); }
      100% { transform: translateX(115vw) translateY(0) scaleX(1); }
    }
    @keyframes birdFly2 {
      0%   { transform: translateX(0) translateY(0) scaleX(1); }
      25%  { transform: translateX(30vw) translateY(6px) scaleX(1); }
      50%  { transform: translateX(55vw) translateY(-6px) scaleX(1); }
      75%  { transform: translateX(85vw) translateY(3px) scaleX(1); }
      100% { transform: translateX(115vw) translateY(0) scaleX(1); }
    }
    @keyframes birdFly3 {
      0%   { transform: translateX(0) translateY(0) scaleX(1); }
      20%  { transform: translateX(22vw) translateY(-10px) scaleX(1); }
      45%  { transform: translateX(48vw) translateY(5px) scaleX(1); }
      70%  { transform: translateX(78vw) translateY(-3px) scaleX(1); }
      100% { transform: translateX(115vw) translateY(0) scaleX(1); }
    }
    @keyframes birdFly4 {
      0%   { transform: translateX(0) translateY(0) scaleX(1); }
      30%  { transform: translateX(35vw) translateY(-5px) scaleX(1); }
      60%  { transform: translateX(65vw) translateY(7px) scaleX(1); }
      100% { transform: translateX(115vw) translateY(0) scaleX(1); }
    }

    /* ── Orientation / Tablet / Landscape ── */
    @media (orientation: landscape) and (max-width: 991px) {
      .screen { padding: 24px 32px 20px; }
      .login-card { max-width: 520px; padding: 28px 32px; }
      .brand-hero { margin-bottom: 28px; }
      .brand-hero img { width: 160px; height: auto; }
    }

    @media (min-width: 768px) and (orientation: portrait) {
      .screen { padding: 40px 48px 24px; }
      .login-card { max-width: 600px; padding: 40px; }
      .brand-hero { margin-bottom: 44px; }
      .brand-hero img { width: 220px; height: auto; }
      .form-control { min-height: 58px; font-size: 17px; }
      .btn-signin { min-height: 58px; font-size: 17px; }
      .login-card h2 { font-size: 24px; }
    }

    @media (min-width: 992px) {
      .screen { padding: 48px 64px 24px; }
      .login-card { max-width: 680px; padding: 48px; }
      .brand-hero { margin-bottom: 48px; }
      .brand-hero img { width: 260px; height: auto; }
      .form-control { min-height: 62px; font-size: 18px; }
      .btn-signin { min-height: 62px; font-size: 18px; }
      .login-card h2 { font-size: 26px; }
    }

    @media (max-width: 360px) {
      .screen { padding: 20px 16px 16px; }
      .login-card { padding: 20px 16px; }
      .brand-hero { margin-bottom: 28px; }
      .brand-hero img { width: 120px; height: auto; }
      .login-card h2 { font-size: 20px; }
      .form-control { font-size: 16px; }
    }
  </style>
</head>
<body>
  <input type="hidden" id="base_url" value="<?= base_url(); ?>">

  <div id="app">
    <div class="mp-login-sky">
      <div class="cloud c1"></div>
      <div class="cloud c2"></div>
      <div class="cloud c3"></div>
      <div class="cloud c4"></div>
      <div class="bird b1">
        <svg viewBox="0 0 24 14"><path d="M1,7 C2,4 5,3 8,5 L11,3 C11,5 14,6 17,5 C15,8 11,7 8,8 C5,9 2,9 1,7 Z"/></svg>
      </div>
      <div class="bird b2">
        <svg viewBox="0 0 24 14"><path d="M1,7 C2,4 5,3 8,5 L11,3 C11,5 14,6 17,5 C15,8 11,7 8,8 C5,9 2,9 1,7 Z"/></svg>
      </div>
      <div class="bird b3">
        <svg viewBox="0 0 24 14"><path d="M1,7 C2,4 5,3 8,5 L11,3 C11,5 14,6 17,5 C15,8 11,7 8,8 C5,9 2,9 1,7 Z"/></svg>
      </div>
      <div class="bird b4">
        <svg viewBox="0 0 24 14"><path d="M1,7 C2,4 5,3 8,5 L11,3 C11,5 14,6 17,5 C15,8 11,7 8,8 C5,9 2,9 1,7 Z"/></svg>
      </div>
      <div class="bird b5">
        <svg viewBox="0 0 24 14"><path d="M1,7 C2,4 5,3 8,5 L11,3 C11,5 14,6 17,5 C15,8 11,7 8,8 C5,9 2,9 1,7 Z"/></svg>
      </div>
      <div class="bird b6">
        <svg viewBox="0 0 24 14"><path d="M1,7 C2,4 5,3 8,5 L11,3 C11,5 14,6 17,5 C15,8 11,7 8,8 C5,9 2,9 1,7 Z"/></svg>
      </div>
      <div class="bird b7">
        <svg viewBox="0 0 24 14"><path d="M1,7 C2,4 5,3 8,5 L11,3 C11,5 14,6 17,5 C15,8 11,7 8,8 C5,9 2,9 1,7 Z"/></svg>
      </div>
      <div class="bird b8">
        <svg viewBox="0 0 24 14"><path d="M1,7 C2,4 5,3 8,5 L11,3 C11,5 14,6 17,5 C15,8 11,7 8,8 C5,9 2,9 1,7 Z"/></svg>
      </div>
    </div>
    <section class="screen">
      <div class="brand-hero">
        <img src="<?= base_url(get_site_logo()); ?>" onerror="this.onerror=null; this.src='<?= base_url('uploads/site/default.png'); ?>';" alt="MartPoint">
      </div>

      <div class="login-card">
        <h2>Welcome back</h2>
        <p class="lead">Sign in to your store account</p>

        <?php if($this->session->flashdata('failed')): ?>
          <div class="alert alert-danger"><?= $this->session->flashdata('failed'); ?></div>
        <?php endif; ?>
        <?php if($this->session->flashdata('success')): ?>
          <div class="alert alert-success"><?= $this->session->flashdata('success'); ?></div>
        <?php endif; ?>

        <form id="login-form" action="<?= base_url('login/verify'); ?>" method="post" autocomplete="off">
          <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

          <div class="form-group">
            <label for="email">Email / Username</label>
            <div class="input-wrap">
              <span class="icon-left"><i class="fa fa-envelope-o"></i></span>
              <input type="text" id="email" name="email" class="form-control" placeholder="Enter your email or username" autofocus autocapitalize="off" autocomplete="username">
            </div>
          </div>

          <div class="form-group">
            <label for="pass">Password</label>
            <div class="input-wrap">
              <span class="icon-left"><i class="fa fa-lock"></i></span>
              <input type="password" id="pass" name="pass" class="form-control" placeholder="Enter your password" autocomplete="current-password">
              <button type="button" class="toggle-pw" aria-label="Show password" onclick="togglePassword()">
                <i class="fa fa-eye" id="pw-icon"></i>
              </button>
            </div>
          </div>

          <div class="options">
            <label class="remember">
              <input type="checkbox" name="remember" value="1" checked> Remember me
            </label>
            <a href="<?= base_url('login/forgot_password'); ?>" class="forgot">Forgot password?</a>
          </div>

          <button type="submit" class="btn-signin" id="signin-btn">
            <i class="fa fa-arrow-right"></i> Sign In
          </button>


        </form>
      </div>

      <?php if(demo_app()): ?>
      <div class="demo-box">
        <label class="demo-label">Demo Accounts</label>
        <table class="demo-table">
          <tbody>
            <tr>
              <td>adminmng@martpoint.com.ng</td>
              <td>Quarter25ile</td>
              <td><button type="button" class="copy" data-user="adminmng@martpoint.com.ng" data-pass="Quarter25ile">Copy</button></td>
            </tr>
            <tr>
              <td>accounts@example.com</td>
              <td>123456</td>
              <td><button type="button" class="copy" data-user="accounts@example.com" data-pass="123456">Copy</button></td>
            </tr>
            <tr>
              <td>seller@example.com</td>
              <td>123456</td>
              <td><button type="button" class="copy" data-user="seller@example.com" data-pass="123456">Copy</button></td>
            </tr>
            <tr>
              <td>purchase@example.com</td>
              <td>123456</td>
              <td><button type="button" class="copy" data-user="purchase@example.com" data-pass="123456">Copy</button></td>
            </tr>
          </tbody>
        </table>
        <p class="demo-note"><i class="fa fa-info-circle text-warning"></i> Some features are disabled in demo. Resets hourly.</p>
      </div>
      <?php endif; ?>
    </section>

    <footer class="copyright-footer">
      &copy; <?= date('Y'); ?> <?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?>. All rights reserved. Business operations powered by MartPoint.<br>
      Powered by Avario Digitals
    </footer>

  </div>

  <script>
    var base_url = '<?= base_url(); ?>';

    function togglePassword() {
      var p = document.getElementById('pass');
      var i = document.getElementById('pw-icon');
      if (p.type === 'password') {
        p.type = 'text';
        i.classList.remove('fa-eye');
        i.classList.add('fa-eye-slash');
      } else {
        p.type = 'password';
        i.classList.remove('fa-eye-slash');
        i.classList.add('fa-eye');
      }
    }

    document.getElementById('login-form').addEventListener('submit', function(e) {
      var email = document.getElementById('email').value.trim();
      var pass = document.getElementById('pass').value.trim();
      if (!email || !pass) return;
      var btn = document.getElementById('signin-btn');
      btn.disabled = true;
      btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Signing in...';
    });

    document.querySelectorAll('.copy').forEach(function(btn) {
      btn.addEventListener('click', function() {
        document.getElementById('email').value = this.getAttribute('data-user');
        document.getElementById('pass').value = this.getAttribute('data-pass');
      });
    });
  </script>
  <?php $this->load->view('mobile/mp_alert'); ?>
</body>
</html>