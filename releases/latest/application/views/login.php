<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title><?php print $SITE_TITLE; ?> | Log in</title>
  <link rel='shortcut icon' href='<?php echo base_url('uploads/site/icon.webp'); ?>' />
  <link rel="manifest" href="<?php echo base_url('manifest.json'); ?>">
  <meta name="theme-color" content="#0B1120">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="MartPoint">
  <link rel="apple-touch-icon" href="<?php echo base_url('uploads/site/icon.webp'); ?>">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
  <!-- Bootstrap 3.3.6 -->
  <link rel="stylesheet" href="<?php echo $theme_link; ?>bootstrap/css/bootstrap.min.css">
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <!-- Ionicons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/ionicons/2.0.1/css/ionicons.min.css">
  <!-- Theme style -->
  <link rel="stylesheet" href="<?php echo $theme_link; ?>dist/css/AdminLTE.min.css">
  <!-- iCheck -->
  <link rel="stylesheet" href="<?php echo $theme_link; ?>plugins/iCheck/square/blue.css">
  <!-- MartPoint Retail Reskin -->
  <link rel="stylesheet" href="<?php echo $theme_link; ?>dist/css/martpoint-reskin.css?v=5">
  <?php
      $lang = trim(strtoupper($this->session->userdata('language')));
      if($lang==strtoupper('arabic') || $lang==strtoupper('urdu')) {?>
  <!-- RTL For arabic styles -->
  <link rel="stylesheet" href="<?php echo $theme_link; ?>bootstrap/css/bootstrap.rtl.min.css">
  <link rel="stylesheet" href="<?php echo $theme_link; ?>dist/css/AdminLTE.rtl.min.css">
  <?php } ?>

  <!-- Login Portal Styles -->
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap');

    * { box-sizing: border-box; }

    .mp-login-wrapper {
      display: flex;
      min-height: 100vh;
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: linear-gradient(135deg, #ffffff 0%, #F0F9FF 50%, #E0F2FE 100%);
      position: relative;
      overflow: hidden;
    }
    .mp-login-wrapper::before {
      content: '';
      position: absolute;
      top: -100px; right: 20%;
      width: 500px; height: 500px;
      border-radius: 50%;
      filter: blur(100px);
      pointer-events: none;
      opacity: 0.22;
      background: radial-gradient(circle, #0057FF 0%, transparent 70%);
      animation: flowWave 12s ease-in-out infinite alternate;
      will-change: transform;
    }
    .mp-login-wrapper::after {
      content: '';
      position: absolute;
      bottom: -120px; left: 10%;
      width: 400px; height: 400px;
      border-radius: 50%;
      filter: blur(110px);
      pointer-events: none;
      opacity: 0.14;
      background: radial-gradient(circle, #3B82F6 0%, transparent 70%);
      animation: flowWave 16s ease-in-out infinite alternate-reverse;
      will-change: transform;
    }
    .mp-login-wrapper .flow-orb {
      position: absolute;
      top: 40%; left: -5%;
      width: 350px; height: 350px;
      border-radius: 50%;
      filter: blur(90px);
      pointer-events: none;
      opacity: 0.12;
      background: radial-gradient(circle, #60A5FA 0%, transparent 70%);
      animation: flowWave 20s ease-in-out infinite alternate;
      will-change: transform;
    }

    /* ── Artistic Sky ── */
    .mp-login-sky {
      position: absolute;
      top: 0; left: 0; right: 0; bottom: 0;
      pointer-events: none;
      z-index: 0;
      overflow: hidden;
    }
    /* Clouds */
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
    /* Birds - right facing, flock of 8 */
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
    @keyframes flowWave {
      0%   { transform: translate(0, 0) scale(1); }
      33%  { transform: translate(40px, -30px) scale(1.08); }
      66%  { transform: translate(-20px, 20px) scale(0.95); }
      100% { transform: translate(30px, -10px) scale(1.05); }
    }

    /* ── LEFT PANEL ── */
    .mp-login-left {
      flex: 0 0 50%;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 56px 48px 56px 120px;
      color: #111827;
      background: transparent;
      position: relative;
      overflow: hidden;
    }

    .mp-login-left .brand {
      display: flex;
      align-items: center;
      gap: 12px;
      position: relative; z-index: 1;
    }
    .mp-login-left .brand img {
      max-height: 60px;
      filter: none;
    }
    /* Brand text removed per request */

    .mp-login-left .hero {
      position: relative; z-index: 1;
      margin-top: auto;
      margin-bottom: auto;
      padding-top: 40px;
      padding-bottom: 40px;
    }
    .mp-login-left h1 {
      font-size: 42px;
      font-weight: 800;
      line-height: 1.15;
      margin-bottom: 20px;
      letter-spacing: -1px;
      color: #111827;
    }
    .mp-login-left h1 .accent {
      color: #0057FF;
    }
    .mp-login-left p.lead {
      font-size: 15px;
      color: #6B7280;
      line-height: 1.7;
      max-width: 380px;
      margin-bottom: 40px;
    }

    .mp-login-features {
      display: flex;
      flex-direction: column;
      gap: 14px;
      position: relative; z-index: 1;
    }
    .mp-login-feature {
      display: flex;
      align-items: center;
      gap: 14px;
    }
    .mp-login-feature .icon {
      width: 40px; height: 40px;
      border-radius: 10px;
      background: rgba(0,87,255,0.06);
      border: 1px solid rgba(0,87,255,0.08);
      color: #0057FF;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 15px;
      flex-shrink: 0;
    }
    .mp-login-feature .text .title {
      font-size: 13px;
      font-weight: 600;
      color: #111827;
      margin-bottom: 2px;
    }
    .mp-login-feature .text .desc {
      font-size: 12px;
      color: #6B7280;
    }

    .mp-login-sectors {
      position: relative; z-index: 1;
      margin-top: 0;
      padding-top: 0;
    }
    .mp-login-sectors .label {
      font-size: 12px;
      font-weight: 500;
      letter-spacing: 0.2px;
      color: #6B7280;
      margin-bottom: 10px;
      display: block;
      text-align: left;
    }
    .mp-login-sectors .grid {
      display: flex;
      gap: 18px;
      flex-wrap: nowrap;
    }
    .mp-login-sectors .sector {
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      gap: 4px;
      background: none;
      border: none;
      padding: 0;
    }
    .mp-login-sectors .sector .icon {
      width: 28px; height: 28px;
      border-radius: 6px;
      background: rgba(0,87,255,0.08);
      color: #0057FF;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 11px;
    }
    .mp-login-sectors .sector .name {
      font-size: 10px;
      font-weight: 500;
      color: #6B7280;
      white-space: nowrap;
    }

    /* ── RIGHT PANEL ── */
    .mp-login-right {
      flex: 0 0 50%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 48px;
      background: transparent;
      position: relative;
    }

    .mp-login-topbar {
      display: none;
    }
    .mp-login-topbar .lang-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      padding: 6px 14px;
      border-radius: 8px;
      border: 1px solid #E5E7EB;
      background: #fff;
      color: #374151;
      font-size: 13px;
      font-weight: 500;
      text-decoration: none;
      cursor: pointer;
    }
    .mp-login-topbar .lang-btn i {
      font-size: 14px;
      color: #6B7280;
    }

    .mp-login-card {
      width: 100%;
      max-width: 400px;
      background: #fff;
      border-radius: 16px;
      padding: 36px 32px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 8px 40px rgba(0,0,0,0.06);
      position: relative;
    }

    .mp-login-card-header {
      margin-bottom: 28px;
    }
    .mp-login-card-header h2 {
      font-size: 24px;
      font-weight: 700;
      color: #111827;
      margin-bottom: 6px;
      letter-spacing: -0.3px;
    }
    .mp-login-card-header p {
      font-size: 14px;
      color: #6B7280;
      margin: 0;
    }

    .mp-login-form .form-group {
      margin-bottom: 16px;
    }
    .mp-login-form label {
      display: block;
      font-size: 13px;
      font-weight: 500;
      color: #374151;
      margin-bottom: 6px;
    }
    .mp-login-form .form-control {
      height: 48px;
      border-radius: 10px;
      border: 1.5px solid #E5E7EB;
      background: #fff;
      color: #111827;
      font-size: 14px;
      padding: 10px 14px;
      box-shadow: none;
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    .mp-login-form .form-control:focus {
      border-color: #0057FF;
      box-shadow: 0 0 0 3px rgba(0,87,255,0.1);
      outline: none;
    }
    .mp-login-form .form-control::placeholder {
      color: #9CA3AF;
    }
    .mp-login-form .input-icon-wrap {
      position: relative;
    }
    .mp-login-form .input-icon-wrap .form-control {
      padding-left: 42px;
    }
    .mp-login-form .input-icon-wrap .icon {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: #9CA3AF;
      font-size: 14px;
    }
    .mp-login-form .input-icon-wrap .toggle-pw {
      position: absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: #9CA3AF;
      font-size: 14px;
      cursor: pointer;
      background: none;
      border: none;
      padding: 0;
    }
    .mp-login-form .input-icon-wrap .toggle-pw:hover {
      color: #6B7280;
    }

    .mp-login-form .btn-signin {
      height: 48px;
      border-radius: 10px;
      background: #0057FF;
      border: none;
      color: #fff;
      font-size: 15px;
      font-weight: 600;
      width: 100%;
      letter-spacing: 0.2px;
      transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
      box-shadow: 0 4px 14px rgba(0,87,255,0.3);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }
    .mp-login-form .btn-signin:hover {
      background: #0047D9;
      transform: translateY(-1px);
      box-shadow: 0 6px 20px rgba(0,87,255,0.35);
    }

    .mp-login-options {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 20px;
    }
    .mp-login-options .remember {
      display: flex;
      align-items: center;
      gap: 8px;
      font-size: 13px;
      color: #374151;
      cursor: pointer;
    }
    .mp-login-options .remember input {
      width: 16px; height: 16px;
      accent-color: #0057FF;
      cursor: pointer;
    }
    .mp-login-options .forgot {
      font-size: 13px;
      color: #0057FF;
      text-decoration: none;
      font-weight: 500;
    }
    .mp-login-options .forgot:hover {
      text-decoration: underline;
    }

    .mp-login-divider {
      display: flex;
      align-items: center;
      gap: 12px;
      margin: 20px 0;
      color: #9CA3AF;
      font-size: 12px;
    }
    .mp-login-divider::before,
    .mp-login-divider::after {
      content: '';
      flex: 1;
      height: 1px;
      background: #E5E7EB;
    }

    .mp-login-form .btn-google {
      height: 48px;
      border-radius: 10px;
      background: #fff;
      border: 1.5px solid #E5E7EB;
      color: #374151;
      font-size: 14px;
      font-weight: 500;
      width: 100%;
      transition: background 0.2s, border-color 0.2s;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }
    .mp-login-form .btn-google:hover {
      background: #F9FAFB;
      border-color: #D1D5DB;
    }
    .mp-login-form .btn-google img {
      width: 18px; height: 18px;
    }

    .mp-login-footer {
      margin-top: 20px;
      text-align: center;
      font-size: 13px;
      color: #6B7280;
    }
    .mp-login-footer a {
      color: #0057FF;
      font-weight: 600;
      text-decoration: none;
    }
    .mp-login-footer a:hover {
      text-decoration: underline;
    }

    .mp-login-powered {
      position: absolute;
      bottom: -130px;
      right: 32px;
      font-size: 11px;
      color: #9CA3AF;
      text-align: right;
      z-index: 2;
    }
    .mp-login-powered a {
      color: #6B7280;
      text-decoration: none;
      font-weight: 500;
    }
    .mp-login-powered a:hover {
      color: #0057FF;
    }

    .mp-login-alert {
      padding: 12px 16px;
      border-radius: 10px;
      font-size: 13px;
      margin-bottom: 18px;
      font-weight: 500;
    }
    .mp-login-alert.danger {
      background: #FEF2F2;
      color: #DC2626;
      border: 1px solid #FECACA;
    }
    .mp-login-alert.success {
      background: #ECFDF5;
      color: #059669;
      border: 1px solid #A7F3D0;
    }

    .mp-demo-box {
      margin-top: 24px;
      width: 100%;
      max-width: 400px;
    }
    .mp-demo-box label {
      color: #6B7280;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      margin-bottom: 10px;
      display: block;
    }
    .mp-demo-box .table {
      background: #fff;
      border: 1px solid #E5E7EB;
      border-radius: 12px;
      overflow: hidden;
      margin: 0;
    }
    .mp-demo-box .table td {
      border-color: #F3F4F6;
      color: #4B5563;
      font-size: 12px;
      padding: 10px 14px;
      vertical-align: middle;
    }
    .mp-demo-box .btn-info {
      background: #EFF6FF;
      border: 1px solid #BFDBFE;
      color: #0057FF;
      font-size: 11px;
      padding: 5px 12px;
      border-radius: 8px;
      font-weight: 600;
    }
    .mp-demo-box .btn-info:hover {
      background: #DBEAFE;
    }
    .mp-demo-note {
      color: #9CA3AF;
      font-size: 11px;
      margin-top: 10px;
    }

    @media (max-width: 991px) {
      .mp-login-left { display: none; }
      .mp-login-right { width: 100%; min-width: auto; background: #fff; padding: 24px; }
      .mp-login-card { box-shadow: none; padding: 24px 0; }
      .mp-login-topbar { right: 16px; top: 16px; }
    }
  </style>
</head>
<body>

  <input type="hidden" id="base_url" value="<?=base_url()?>">

  <div class="mp-login-wrapper">
    <div class="flow-orb"></div>
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
    <!-- LEFT: Branding -->
    <div class="mp-login-left">
      <div class="brand">
        <img src="<?php echo base_url(get_site_logo());?>" onerror="this.onerror=null; this.src='<?php echo base_url('uploads/site/default.png'); ?>';" alt="MartPoint Retail">
      </div>

      <div class="hero">
        <h1>Run your business.<br><span class="accent">Grow without limits.</span></h1>
        <p class="lead">
          MartPoint Retail gives you complete control of sales, inventory, payments, staff and every branch — all from one powerful dashboard.
        </p>

        <div class="mp-login-features">
          <div class="mp-login-feature">
            <div class="icon"><i class="fa fa-line-chart"></i></div>
            <div class="text">
              <div class="title">Real-time Insights</div>
              <div class="desc">Track sales, stock and performance in real time.</div>
            </div>
          </div>
          <div class="mp-login-feature">
            <div class="icon"><i class="fa fa-cubes"></i></div>
            <div class="text">
              <div class="title">Inventory Control</div>
              <div class="desc">Never run out or overstock again.</div>
            </div>
          </div>
          <div class="mp-login-feature">
            <div class="icon"><i class="fa fa-credit-card"></i></div>
            <div class="text">
              <div class="title">Accept Every Payment</div>
              <div class="desc">Cash, Card, Transfer and more.</div>
            </div>
          </div>
          <div class="mp-login-feature">
            <div class="icon"><i class="fa fa-users"></i></div>
            <div class="text">
              <div class="title">Manage Your Team</div>
              <div class="desc">Roles, permissions and performance — all in one place.</div>
            </div>
          </div>
        </div>
      </div>

      <div class="mp-login-sectors">
        <span class="label">Join other businesses in different sectors, use MartPoint today.</span>
        <div class="grid">
          <div class="sector">
            <div class="icon"><i class="fa fa-shopping-cart"></i></div>
            <div class="name">Retail</div>
          </div>
          <div class="sector">
            <div class="icon"><i class="fa fa-medkit"></i></div>
            <div class="name">Pharmacy</div>
          </div>
          <div class="sector">
            <div class="icon"><i class="fa fa-cutlery"></i></div>
            <div class="name">Restaurant</div>
          </div>
          <div class="sector">
            <div class="icon"><i class="fa fa-shopping-bag"></i></div>
            <div class="name">Boutique</div>
          </div>
          <div class="sector">
            <div class="icon"><i class="fa fa-laptop"></i></div>
            <div class="name">Electronics</div>
          </div>
          <div class="sector">
            <div class="icon"><i class="fa fa-tint"></i></div>
            <div class="name">Laundry</div>
          </div>
          <div class="sector">
            <div class="icon"><i class="fa fa-cubes"></i></div>
            <div class="name">Wholesale</div>
          </div>
          <div class="sector">
            <div class="icon"><i class="fa fa-wrench"></i></div>
            <div class="name">Hardware</div>
          </div>
        </div>
      </div>
    </div>

    <!-- RIGHT: Login Form -->
    <div class="mp-login-right">
      <div class="mp-login-topbar">
        <a href="#" class="lang-btn"><i class="fa fa-globe"></i> English <i class="fa fa-angle-down"></i></a>
      </div>

      <div class="mp-login-card">
        <div class="mp-login-card-header">
          <h2>Welcome back</h2>
          <p>Login to your MartPoint Retail account</p>
        </div>

        <?php if($this->session->flashdata('failed')){ ?>
        <div class="mp-login-alert danger"><?php echo $this->session->flashdata('failed'); ?></div>
        <?php } ?>
        <?php if($this->session->flashdata('success')){ ?>
        <div class="mp-login-alert success"><?php echo $this->session->flashdata('success'); ?></div>
        <?php } ?>

        <form id="login-form" action="<?php echo $base_url; ?>login/verify" method="post" class="mp-login-form">
          <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>">

          <div class="form-group">
            <label>Email address</label>
            <div class="input-icon-wrap">
              <span class="icon"><i class="fa fa-envelope-o"></i></span>
              <input type="text" class="form-control" placeholder="Enter your email address" id="email" name="email" autofocus>
            </div>
          </div>

          <div class="form-group">
            <label>Password</label>
            <div class="input-icon-wrap">
              <span class="icon"><i class="fa fa-lock"></i></span>
              <input type="password" class="form-control" placeholder="Enter your password" id="pass" name="pass">
              <button type="button" class="toggle-pw" onclick="const p=document.getElementById('pass'),i=this.querySelector('i');p.type=p.type==='password'?'text':'password';i.classList.toggle('fa-eye');i.classList.toggle('fa-eye-slash');"><i class="fa fa-eye"></i></button>
            </div>
          </div>

          <div class="mp-login-options">
            <label class="remember">
              <input type="checkbox" checked> Remember me
            </label>
            <a href="<?=base_url('login/forgot_password')?>" class="forgot">Forgot password?</a>
          </div>

          <div class="form-group">
            <button type="submit" class="btn btn-signin"><?= $this->lang->line('sign_in'); ?> <i class="fa fa-arrow-right"></i></button>
          </div>

          <div class="mp-login-divider">or</div>

          <div class="form-group">
            <button type="button" class="btn btn-google" disabled>
              <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google"> Continue with Google
            </button>
          </div>

          <div class="mp-login-footer">
            Need help? <a href="<?=base_url('login/forgot_password')?>">Contact support</a>
          </div>
        </form>

        <div class="mp-login-powered">
          Powered by <a href="https://avariodigitals.com" target="_blank">Avario Digitals</a>
        </div>
      </div>

      <?php if(demo_app()){ ?>
      <div class="mp-demo-box">
        <label>Click to Start Session!</label>
        <table class="table table-bordered table-condensed text-center">
          <tbody>
            <tr>
              <td>adminmng@martpoint.com.ng</td>
              <td>Quarter25ile</td>
              <td><button type="button" class="btn btn-info btn-block btn-flat admin">Copy</button></td>
            </tr>
            <tr>
              <td>accounts@example.com</td>
              <td>123456</td>
              <td><button type="button" class="btn btn-info btn-block btn-flat accounts">Copy</button></td>
            </tr>
            <tr>
              <td>seller@example.com</td>
              <td>123456</td>
              <td><button type="button" class="btn btn-info btn-block btn-flat seller">Copy</button></td>
            </tr>
            <tr>
              <td>purchase@example.com</td>
              <td>123456</td>
              <td><button type="button" class="btn btn-info btn-block btn-flat purchase">Copy</button></td>
            </tr>
          </tbody>
        </table>
        <p class="mp-demo-note"><i class="fa fa-info-circle text-warning"></i> Some features are disabled in demo. Resets hourly.</p>
      </div>
      <?php } ?>

    </div>
  </div>

<!-- jQuery 2.2.3 -->

<!-- jQuery 2.2.3 -->
<script src="<?php echo $theme_link; ?>plugins/jQuery/jquery-2.2.3.min.js"></script>
<!-- Bootstrap 3.3.6 -->
<script src="<?php echo $theme_link; ?>bootstrap/js/bootstrap.min.js"></script>
<!-- iCheck -->
<script src="<?php echo $theme_link; ?>plugins/iCheck/icheck.min.js"></script>
<script src="<?php echo $theme_link; ?>js/language.js"></script>
<script>
  $(function () {
    $('input').iCheck({
      checkboxClass: 'icheckbox_square-blue',
      radioClass: 'iradio_square-blue',
      increaseArea: '20%' // optional
    });
  });
</script>
<script type="text/javascript" >
$(function($) { // this script needs to be loaded on every page where an ajax POST may happen
    $.ajaxSetup({ data: {'<?php echo $this->security->get_csrf_token_name(); ?>' : '<?php echo $this->security->get_csrf_hash(); ?>' }  }); });
</script>
<script type="text/javascript">
  $(".admin").on("click",function(event) {
    $("input[name='email']").val("adminmng@martpoint.com.ng");
    $("input[name='pass']").val("Quarter25ile");
    $("#login-form").submit();
  });

  $(".accounts").on("click",function(event) {
    $("input[name='email']").val("accounts@example.com");
    $("input[name='pass']").val("123456");
    $("#login-form").submit();
  });

  $(".seller").on("click",function(event) {
    $("input[name='email']").val("seller@example.com");
    $("input[name='pass']").val("123456");
    $("#login-form").submit();
  });

  $(".purchase").on("click",function(event) {
      $("input[name='email']").val("purchase@example.com");
      $("input[name='pass']").val("123456");
      $("#login-form").submit();
    });

</script>
<script>
  if('serviceWorker' in navigator){
    window.addEventListener('load', function(){
      navigator.serviceWorker.register('<?= base_url("sw.js"); ?>').then(function(reg){
        console.log('PWA SW registered:', reg.scope);
      }).catch(function(err){
        console.log('PWA SW registration failed:', err);
      });
    });
  }
</script>
</body>
</html>
