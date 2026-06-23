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
  <link rel="stylesheet" href="<?php echo $theme_link; ?>dist/css/martpoint-reskin.css">
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
    }

    /* ── LEFT PANEL ── */
    .mp-login-left {
      flex: 0 0 46%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      padding: 60px 56px;
      color: #fff;
      background: #0B1120;
      position: relative;
      overflow: hidden;
    }

    /* Animated gradient orbs */
    .mp-login-left::before,
    .mp-login-left::after {
      content: '';
      position: absolute;
      border-radius: 50%;
      filter: blur(80px);
      pointer-events: none;
      opacity: 0.45;
    }
    .mp-login-left::before {
      top: -120px; right: -80px;
      width: 420px; height: 420px;
      background: radial-gradient(circle, #3B82F6 0%, transparent 70%);
      animation: orbFloat 8s ease-in-out infinite alternate;
    }
    .mp-login-left::after {
      bottom: -100px; left: -60px;
      width: 360px; height: 360px;
      background: radial-gradient(circle, #F97316 0%, transparent 70%);
      animation: orbFloat 10s ease-in-out infinite alternate-reverse;
    }
    @keyframes orbFloat {
      0%   { transform: translate(0, 0) scale(1); }
      100% { transform: translate(30px, -20px) scale(1.1); }
    }

    .mp-login-left .brand {
      display: flex;
      align-items: center;
      gap: 14px;
      margin-bottom: 48px;
      position: relative; z-index: 1;
    }
    .mp-login-left .brand img {
      max-height: 56px;
      filter: brightness(0) invert(1);
    }
    .mp-login-left .brand-name {
      font-size: 22px;
      font-weight: 800;
      color: #fff;
      letter-spacing: -0.5px;
    }
    .mp-login-left h1 {
      font-size: 48px;
      font-weight: 800;
      line-height: 1.1;
      margin-bottom: 20px;
      letter-spacing: -1px;
      position: relative; z-index: 1;
    }
    .mp-login-left h1 span {
      color: #F97316;
    }
    .mp-login-left p.lead {
      font-size: 17px;
      color: rgba(255,255,255,0.55);
      line-height: 1.7;
      max-width: 420px;
      margin-bottom: 48px;
      position: relative; z-index: 1;
    }
    .mp-login-features {
      display: flex;
      gap: 14px;
      flex-wrap: wrap;
      position: relative; z-index: 1;
    }
    .mp-login-feature {
      background: rgba(255,255,255,0.04);
      border: 1px solid rgba(255,255,255,0.07);
      border-radius: 14px;
      padding: 18px 20px;
      min-width: 130px;
      transition: all 0.25s ease;
      backdrop-filter: blur(8px);
    }
    .mp-login-feature:hover {
      background: rgba(255,255,255,0.08);
      border-color: rgba(255,255,255,0.12);
      transform: translateY(-2px);
    }
    .mp-login-feature .icon {
      width: 38px; height: 38px;
      border-radius: 10px;
      background: rgba(249,115,22,0.12);
      color: #F97316;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 15px;
      margin-bottom: 12px;
    }
    .mp-login-feature .title {
      font-size: 13px;
      font-weight: 700;
      color: #fff;
      margin-bottom: 3px;
    }
    .mp-login-feature .desc {
      font-size: 11px;
      color: rgba(255,255,255,0.4);
    }

    /* ── RIGHT PANEL ── */
    .mp-login-right {
      flex: 0 0 54%;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 48px;
      background: #F8FAFC;
      position: relative;
    }

    .mp-login-card {
      width: 100%;
      max-width: 420px;
      background: #fff;
      border-radius: 20px;
      padding: 40px 36px;
      box-shadow: 0 1px 3px rgba(0,0,0,0.04), 0 8px 40px rgba(0,0,0,0.06);
    }

    .mp-login-card-header {
      margin-bottom: 28px;
      text-align: center;
    }
    .mp-login-card-header h2 {
      font-size: 26px;
      font-weight: 800;
      color: #0F172A;
      margin-bottom: 6px;
      letter-spacing: -0.5px;
    }
    .mp-login-card-header .brand-tag {
      font-size: 13px;
      font-weight: 700;
      color: #F97316;
      letter-spacing: 0.5px;
      text-transform: uppercase;
      margin-bottom: 4px;
    }
    .mp-login-card-header p {
      font-size: 14px;
      color: #64748B;
      margin: 0;
    }

    .mp-login-form .form-group {
      margin-bottom: 18px;
    }
    .mp-login-form .form-control {
      height: 52px;
      border-radius: 12px;
      border: 1.5px solid #E2E8F0;
      background: #fff;
      color: #0F172A;
      font-size: 15px;
      padding: 10px 16px;
      box-shadow: none;
      transition: border-color 0.2s, box-shadow 0.2s;
    }
    .mp-login-form .form-control:focus {
      border-color: #F97316;
      box-shadow: 0 0 0 4px rgba(249,115,22,0.1);
      outline: none;
    }
    .mp-login-form .form-control::placeholder {
      color: #94A3B8;
    }
    .mp-login-form .input-icon-wrap {
      position: relative;
    }
    .mp-login-form .input-icon-wrap .form-control {
      padding-left: 46px;
    }
    .mp-login-form .input-icon-wrap .icon {
      position: absolute;
      left: 16px;
      top: 50%;
      transform: translateY(-50%);
      color: #94A3B8;
      font-size: 15px;
    }
    .mp-login-form .btn-signin {
      height: 52px;
      border-radius: 12px;
      background: #F97316;
      border: none;
      color: #fff;
      font-size: 16px;
      font-weight: 700;
      width: 100%;
      letter-spacing: 0.3px;
      transition: background 0.2s, transform 0.15s, box-shadow 0.2s;
      box-shadow: 0 4px 14px rgba(249,115,22,0.35);
    }
    .mp-login-form .btn-signin:hover {
      background: #EA580C;
      transform: translateY(-1px);
      box-shadow: 0 6px 20px rgba(249,115,22,0.4);
    }
    .mp-login-footer {
      margin-top: 20px;
      text-align: center;
    }
    .mp-login-footer a {
      color: #64748B;
      font-size: 14px;
      text-decoration: none;
      font-weight: 500;
      transition: color 0.2s;
    }
    .mp-login-footer a:hover {
      color: #F97316;
    }
    .mp-login-powered {
      position: absolute;
      bottom: 20px;
      left: 0; right: 0;
      text-align: center;
      font-size: 12px;
      color: #94A3B8;
    }
    .mp-login-powered a {
      color: #64748B;
      text-decoration: none;
      font-weight: 500;
    }
    .mp-login-powered a:hover {
      color: #F97316;
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
      background: #FFF7ED;
      color: #EA580C;
      border: 1px solid #FED7AA;
    }
    .mp-demo-box {
      margin-top: 28px;
      width: 100%;
      max-width: 420px;
    }
    .mp-demo-box label {
      color: #64748B;
      font-size: 11px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.8px;
      margin-bottom: 10px;
      display: block;
    }
    .mp-demo-box .table {
      background: #fff;
      border: 1px solid #E2E8F0;
      border-radius: 12px;
      overflow: hidden;
      margin: 0;
    }
    .mp-demo-box .table td {
      border-color: #F1F5F9;
      color: #475569;
      font-size: 12px;
      padding: 10px 14px;
      vertical-align: middle;
    }
    .mp-demo-box .btn-info {
      background: #FFF7ED;
      border: 1px solid #FED7AA;
      color: #EA580C;
      font-size: 11px;
      padding: 5px 12px;
      border-radius: 8px;
      font-weight: 600;
    }
    .mp-demo-box .btn-info:hover {
      background: #FFEDD5;
    }
    .mp-demo-note {
      color: #94A3B8;
      font-size: 11px;
      margin-top: 10px;
    }

    @media (max-width: 991px) {
      .mp-login-left { display: none; }
      .mp-login-right { width: 100%; min-width: auto; background: #fff; }
      .mp-login-card { box-shadow: none; padding: 32px 24px; }
    }
  </style>
</head>
<body>

  <input type="hidden" id="base_url" value="<?=base_url()?>">

  <div class="mp-login-wrapper">
    <!-- LEFT: App Intro -->
    <div class="mp-login-left">
      <div class="brand">
        <img src="<?php echo base_url(get_site_logo());?>" onerror="this.onerror=null; this.src='<?php echo base_url('uploads/site/default.png'); ?>';" alt="MartPoint Retail">
      </div>
      <h1>Retail Management,<br><span>Simplified.</span></h1>
      <p class="lead">
        From sales to inventory, MartPoint helps you run your store smarter, faster, and with full control — no procurement headache.
      </p>
      <div class="mp-login-features">
        <div class="mp-login-feature">
          <div class="icon"><i class="fa fa-shopping-cart"></i></div>
          <div class="title">Point of Sale</div>
          <div class="desc">Fast checkout</div>
        </div>
        <div class="mp-login-feature">
          <div class="icon"><i class="fa fa-cubes"></i></div>
          <div class="title">Inventory</div>
          <div class="desc">Track stock live</div>
        </div>
        <div class="mp-login-feature">
          <div class="icon"><i class="fa fa-users"></i></div>
          <div class="title">Customers</div>
          <div class="desc">Manage debtors</div>
        </div>
      </div>
    </div>

    <!-- RIGHT: Login Form -->
    <div class="mp-login-right">
      <div class="mp-login-card">
        <div class="mp-login-card-header">
          <div class="brand-tag"><?php print $SITE_TITLE; ?></div>
          <h2>Welcome Back</h2>
          <p><?= $this->lang->line('sign_in_message'); ?></p>
        </div>

        <?php if($this->session->flashdata('failed')){ ?>
        <div class="mp-login-alert danger"><?php echo $this->session->flashdata('failed'); ?></div>
        <?php } ?>
        <?php if($this->session->flashdata('success')){ ?>
        <div class="mp-login-alert success"><?php echo $this->session->flashdata('success'); ?></div>
        <?php } ?>

        <form id="login-form" action="<?php echo $base_url; ?>login/verify" method="post" class="mp-login-form">
          <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>">
          <div class="form-group input-icon-wrap">
            <span class="icon"><i class="fa fa-envelope-o"></i></span>
            <input type="text" class="form-control" placeholder="Email / Username" id="email" name="email" autofocus>
          </div>
          <div class="form-group input-icon-wrap">
            <span class="icon"><i class="fa fa-lock"></i></span>
            <input type="password" class="form-control" placeholder="Password" id="pass" name="pass">
          </div>
          <div class="form-group">
            <button type="submit" class="btn btn-signin"><?= $this->lang->line('sign_in'); ?></button>
          </div>
          <div class="mp-login-footer">
            <a href="<?=base_url('login/forgot_password')?>"><?= $this->lang->line('forgot_password'); ?></a>
          </div>
        </form>
      </div>

      <?php if(demo_app()){ ?>
      <div class="mp-demo-box">
        <label>Click to Start Session!</label>
        <table class="table table-bordered table-condensed text-center">
          <tbody>
            <tr>
              <td>admin@example.com</td>
              <td>123456</td>
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

      <div class="mp-login-powered">
        Powered by <a href="https://avariodigitals.com" target="_blank">Avario Digitals</a>
      </div>
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
    $("input[name='email']").val("admin@example.com");
    $("input[name='pass']").val("123456");
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
