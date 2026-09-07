<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
  <meta http-equiv="Pragma" content="no-cache">
  <meta http-equiv="Expires" content="0">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?> — Support</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
  <style>
    :root { --mp-primary: #0057FF; --mp-primary-dark: #0044CC; --mp-bg: #F1F5F9; --mp-surface: #FFFFFF; --mp-text: #0F172A; --mp-muted: #64748B; --mp-border: #E2E8F0; --mp-success: #10B981; --mp-danger: #EF4444; --mp-warning: #F59E0B; --safe-bottom: env(safe-area-inset-bottom, 0px); }
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif; background: var(--mp-bg); color: var(--mp-text); height: 100%; overscroll-behavior: none; -webkit-tap-highlight-color: transparent; }
    #app { max-width: 430px; margin: 0 auto; background: var(--mp-surface); min-height: 100vh; position: relative; }
    .screen { padding: 12px 16px 120px; }
    .topbar { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; padding-top: 8px; }
    .topbar .back { color: var(--mp-primary); font-size: 20px; text-decoration: none; }
    .topbar h1 { font-size: 22px; font-weight: 700; margin: 0; }
    .topbar .topbar-titles { flex: 1; min-width: 0; }
    .topbar .store-name { font-size: 11px; color: var(--mp-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.4px; margin-bottom: 2px; }
    .lead { font-size: 14px; color: var(--mp-muted); margin: 0 0 20px; line-height: 1.5; }
    .support-card { display: flex; align-items: center; gap: 14px; background: #fff; border: 1px solid var(--mp-border); border-radius: 16px; padding: 16px; margin-bottom: 12px; text-decoration: none; color: var(--mp-text); }
    .support-card .icon { width: 44px; height: 44px; border-radius: 12px; background: #E0E7FF; color: var(--mp-primary); display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0; }
    .support-card .text { flex: 1; min-width: 0; }
    .support-card .title { font-weight: 600; font-size: 15px; margin-bottom: 2px; }
    .support-card .desc { font-size: 13px; color: var(--mp-muted); }
    .cta { display: block; width: 100%; padding: 16px; border: none; border-radius: 14px; background: var(--mp-primary); color: #fff; font-size: 16px; font-weight: 600; text-align: center; text-decoration: none; margin-top: 20px; }
    .cta i { margin-right: 8px; }
    .note { font-size: 12px; color: var(--mp-muted); text-align: center; margin-top: 14px; padding: 0 10px; }
  </style>
</head>
<body>
  <div id="app">
    <section class="screen">
      <div class="topbar">
        <a href="<?= base_url('mobile/more'); ?>" class="back"><i class="fa fa-chevron-left"></i></a>
        <div class="topbar-titles">
          <div class="store-name"><?= htmlspecialchars($SITE_TITLE ?? 'MartPoint'); ?></div>
          <h1>Support</h1>
        </div>
      </div>

      <p class="lead">Need help with MartPoint? Reach us through any channel below or visit the client support center.</p>

      <a href="tel:08036028069" class="support-card">
        <div class="icon"><i class="fa fa-phone"></i></div>
        <div class="text">
          <div class="title">Call us</div>
          <div class="desc">0803 602 8069</div>
        </div>
      </a>

      <a href="mailto:sales@martpoint.com.ng" class="support-card">
        <div class="icon"><i class="fa fa-envelope"></i></div>
        <div class="text">
          <div class="title">Email</div>
          <div class="desc">sales@martpoint.com.ng</div>
        </div>
      </a>

      <a href="https://wa.me/2348036028069?text=Hi%20MartPoint%20Support" target="_blank" rel="noopener" class="support-card">
        <div class="icon"><i class="fa fa-whatsapp"></i></div>
        <div class="text">
          <div class="title">WhatsApp</div>
          <div class="desc">Chat with support</div>
        </div>
      </a>

      <a href="mailto:support@martpoint.com.ng" class="support-card">
        <div class="icon"><i class="fa fa-life-ring"></i></div>
        <div class="text">
          <div class="title">Technical support</div>
          <div class="desc">support@martpoint.com.ng</div>
        </div>
      </a>

      <div class="support-card" style="cursor:default;">
        <div class="icon"><i class="fa fa-clock-o"></i></div>
        <div class="text">
          <div class="title">Office hours</div>
          <div class="desc">Mon–Fri, 9:00 AM – 5:00 PM</div>
        </div>
      </div>

      <a href="https://www.martpoint.com.ng/support" target="_blank" rel="noopener" class="cta"><i class="fa fa-globe"></i> Open client support center</a>

      <p class="note">Hardware, internet, and devices are managed by you or your IT partner. We support the software.</p>
    </section>

    <?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
  </div>
  <?php $this->load->view('mobile/mp_alert'); ?>
  <?php $this->load->view('mobile/chat'); ?>
</body>
</html>
