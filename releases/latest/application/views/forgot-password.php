<?php
$siteTitle = trim((string)($SITE_TITLE ?? 'MartPoint'));
if($siteTitle === '') $siteTitle = 'MartPoint';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#f5f7fb">
  <title>Reset password | <?= htmlspecialchars($siteTitle, ENT_QUOTES, 'UTF-8'); ?></title>
  <link rel="shortcut icon" href="<?= base_url('uploads/site/icon.webp'); ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.5.0/css/font-awesome.min.css">
  <style>
    :root { color-scheme: light; --ink: #14213d; --muted: #68758b; --blue: #1459d9; --line: #e1e7f0; }
    * { box-sizing: border-box; }
    body { min-height: 100vh; margin: 0; padding: 28px 18px; display: grid; place-items: center; background: radial-gradient(circle at 50% 0, #e8efff 0, #f5f7fb 46%, #f5f7fb 100%); color: var(--ink); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
    .reset-card { width: min(100%, 440px); padding: 38px 40px 30px; background: #fff; border: 1px solid rgba(217,225,238,.9); border-radius: 20px; box-shadow: 0 20px 60px rgba(34,55,91,.10); }
    .brand { display: flex; justify-content: center; margin-bottom: 32px; }
    .brand img { display: block; width: 190px; max-width: 75%; height: auto; }
    .icon { width: 48px; height: 48px; margin: 0 auto 18px; display: grid; place-items: center; border-radius: 15px; background: #edf3ff; color: var(--blue); font-size: 21px; }
    h1 { margin: 0; text-align: center; font-size: 25px; letter-spacing: -.5px; }
    .intro { margin: 10px 0 27px; text-align: center; color: var(--muted); line-height: 1.55; font-size: 14px; }
    label { display: block; margin-bottom: 8px; font-size: 13px; font-weight: 650; }
    input { width: 100%; height: 48px; padding: 0 14px; border: 1px solid var(--line); border-radius: 10px; color: var(--ink); background: #fff; font-size: 15px; outline: none; }
    input:focus { border-color: #7da5f6; box-shadow: 0 0 0 3px rgba(20,89,217,.12); }
    .hint { margin: 8px 0 20px; color: var(--muted); font-size: 12px; }
    .alert { margin: 0 0 18px; padding: 11px 13px; border-radius: 9px; font-size: 13px; line-height: 1.45; }
    .alert.error { color: #9f2735; background: #fff0f1; border: 1px solid #f7d4d8; }
    .submit { width: 100%; height: 48px; border: 0; border-radius: 10px; color: #fff; background: var(--blue); font-size: 14px; font-weight: 650; cursor: pointer; box-shadow: 0 7px 16px rgba(20,89,217,.18); }
    .submit:hover { background: #104fc4; }
    .back { display: block; margin-top: 22px; color: var(--muted); text-align: center; font-size: 13px; text-decoration: none; }
    .back i { margin-right: 6px; }
    .back:hover { color: var(--blue); }
    @media (max-width: 480px) { body { padding: 16px; } .reset-card { padding: 30px 23px 25px; border-radius: 16px; } .brand { margin-bottom: 27px; } }
  </style>
</head>
<body>
  <main class="reset-card">
    <a class="brand" href="<?= base_url('login'); ?>" aria-label="MartPoint sign in">
      <img src="<?= base_url('uploads/site/applogo_martpointretail.png'); ?>" alt="MartPoint Retail" onerror="this.onerror=null;this.src='<?= base_url('uploads/site/default.png'); ?>';">
    </a>
    <div class="icon" aria-hidden="true"><i class="fa fa-lock"></i></div>
    <h1>Reset your password</h1>
    <p class="intro">Enter your username or email. We’ll send a one-time code to the email address on your account so you can choose a new password.</p>

    <?php if($this->session->flashdata('failed')): ?>
      <div class="alert error" role="alert"><?= html_escape($this->session->flashdata('failed')); ?></div>
    <?php endif; ?>

    <form action="<?= base_url('login/send_otp'); ?>" method="post" autocomplete="on">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
      <label for="identifier">Username or email</label>
      <input type="text" id="identifier" name="identifier" placeholder="Enter your username or email" autocomplete="username" autocapitalize="none" required autofocus>
      <p class="hint">The reset code is sent to the email saved on your account.</p>
      <button class="submit" type="submit">Send reset code <i class="fa fa-arrow-right" aria-hidden="true"></i></button>
    </form>
    <a class="back" href="<?= base_url('login'); ?>"><i class="fa fa-arrow-left" aria-hidden="true"></i>Back to sign in</a>
  </main>
</body>
</html>
