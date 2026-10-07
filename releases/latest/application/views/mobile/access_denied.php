<?php
/**
 * Mobile Access Denied (HTTP 403)
 * ===============================
 * Rendered by MY_Controller::show_access_denied_page() for phone clients
 * (is_mobile() && !is_tablet()). Desktop keeps views/errors/access_denied.php
 * through mp_layout.
 *
 * AGENTS.md contract satisfied here:
 *  - store name rendered BEFORE the screen name in the topbar
 *  - <title> is "Store Name — Screen Name"
 *  - mobile/bottom_nav renders the menu AND the screen-wide copyright strip
 *  - mobile/chat renders the MartPoint Assist launcher
 *  - no native <select>, nothing pops outside the app container
 */
$store_title = htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint');
// chat.php needs $theme_link; bottom_nav.php recomputes $is_physio itself.
$theme_link = $theme_link ?? base_url() . 'theme/';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
  <title><?= $store_title ?> — Access Denied</title>
  <link rel="shortcut icon" href="<?= base_url('uploads/site/favicon.png'); ?>">
  <link rel="stylesheet" href="<?= base_url('theme/css/font-awesome-4.7.0/css/font-awesome.min.css'); ?>">
  <link rel="stylesheet" href="<?= base_url('theme/css/mobile.css'); ?>">
  <style>
    body { background: #f4f6fb; margin: 0; }
    .mp-denied-wrap { padding: 18px 16px 28px; }
    .mp-denied-card {
      background: #fff; border: 1px solid #e6e9f0; border-radius: 16px;
      padding: 28px 20px; text-align: center;
    }
    .mp-denied-icon {
      width: 64px; height: 64px; margin: 0 auto 16px; border-radius: 50%;
      background: rgba(239,68,68,.1); color: #ef4444;
      display: flex; align-items: center; justify-content: center; font-size: 26px;
    }
    .mp-denied-card h2 { font-size: 18px; font-weight: 700; margin: 0 0 8px; color: #0f172a; }
    .mp-denied-card p { font-size: 13.5px; line-height: 1.6; color: #64748b; margin: 0 0 22px; }
    .mp-denied-actions { display: flex; flex-direction: column; gap: 10px; }
    .mp-denied-actions a {
      display: flex; align-items: center; justify-content: center; gap: 8px;
      min-height: 46px; border-radius: 11px; font-size: 14px; font-weight: 600;
      text-decoration: none !important;
    }
    .mp-denied-primary { background: #6366f1; color: #fff !important; }
    .mp-denied-primary:active { background: #4f46e5; }
    .mp-denied-secondary { background: #eef1f7; color: #475569 !important; }
  </style>
</head>
<body>

<div class="topbar">
  <a href="<?= base_url('mobile'); ?>" class="back" aria-label="Back"><i class="fa fa-chevron-left"></i></a>
  <div class="topbar-titles">
    <div class="store-name"><?= $store_title ?></div>
    <h1>Access Denied</h1>
  </div>
</div>

<div class="mp-denied-wrap">
  <div class="mp-denied-card">
    <div class="mp-denied-icon"><i class="fa fa-lock"></i></div>
    <h2>Permission Required</h2>
    <p><?= htmlspecialchars($message ?? "You don't have permission to access this feature. Contact your administrator if you believe this is a mistake."); ?></p>
    <div class="mp-denied-actions">
      <a href="<?= base_url('mobile'); ?>" class="mp-denied-primary"><i class="fa fa-arrow-left"></i> Back to Home</a>
      <a href="javascript:history.back();" class="mp-denied-secondary">Go Back</a>
    </div>
  </div>
</div>

<?php $this->load->view('mobile/bottom_nav', ['active' => 'more']); ?>
<?php $this->load->view('mobile/chat'); ?>

</body>
</html>
