<?php
/**
 * Shared shell for the mobile Oil & Gas screens.
 *
 * The AGENTS rules (store name above the screen name, "Store — Screen" title,
 * footer menu, copyright strip, Assist chat, no native selects) are the same on
 * every fuel screen, so they live here once instead of being copied five times.
 * Load it with gas_head, then close the page with gas_foot.
 */
$CI =& get_instance();
$gas_store = htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint');
?>
<!DOCTYPE html>
<html lang='en'>
<head>
  <meta charset='utf-8'>
  <meta http-equiv='Cache-Control' content='no-cache, no-store, must-revalidate'>
  <meta http-equiv='Pragma' content='no-cache'>
  <meta http-equiv='Expires' content='0'>
  <meta name='viewport' content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover'>
  <title><?= $gas_store; ?> — <?= htmlspecialchars($gas_title ?? 'Oil &amp; Gas'); ?></title>
  <link rel='preconnect' href='https://fonts.googleapis.com'>
  <link href='https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap' rel='stylesheet'>
  <link rel='stylesheet' href='<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css'>
  <style>
    :root { --mp-primary:#0057FF; --mp-bg:#F1F5F9; --mp-surface:#FFFFFF; --mp-text:#0F172A; --mp-muted:#64748B; --mp-border:#E2E8F0; --mp-success:#10B981; --mp-danger:#EF4444; --mp-warning:#F59E0B; --mp-ink:#1E293B; }
    * { box-sizing:border-box; }
    html, body { margin:0; padding:0; font-family:Inter,-apple-system,BlinkMacSystemFont,sans-serif; background:var(--mp-bg); color:var(--mp-text); height:100%; overscroll-behavior:none; -webkit-tap-highlight-color:transparent; }
    #app { max-width:430px; margin:0 auto; background:var(--mp-surface); min-height:100vh; }
    .screen { padding:12px 12px 110px; }
    .topbar { margin-bottom:14px; padding-top:8px; }
    .topbar .store-name { font-size:11px; color:var(--mp-muted); font-weight:600; text-transform:uppercase; letter-spacing:.4px; margin-bottom:2px; }
    .topbar h1 { font-size:21px; font-weight:700; margin:0; }
    .topbar p { font-size:12px; color:var(--mp-muted); margin:6px 0 0; line-height:1.45; }
    .card { background:#fff; border-radius:14px; padding:14px; margin-bottom:12px; border:1px solid var(--mp-border); }
    .section-title { font-size:15px; font-weight:700; margin:0 0 10px; }
    .sites { display:flex; gap:8px; overflow-x:auto; padding-bottom:4px; margin-bottom:12px; }
    .site-chip { flex:0 0 auto; padding:8px 13px; border:1px solid var(--mp-border); border-radius:999px; background:#fff; color:var(--mp-ink); font-size:12px; font-weight:700; text-decoration:none; }
    .site-chip.active { background:#1b2a4a; border-color:#1b2a4a; color:#fff; }
    .row { padding:12px 0; border-top:1px solid var(--mp-border); }
    .row:first-of-type { border-top:none; padding-top:0; }
    .row .name { font-weight:600; font-size:14px; }
    .row .meta { font-size:12px; color:var(--mp-muted); margin-top:4px; line-height:1.45; }
    .flag { display:inline-block; font-size:11px; font-weight:700; padding:2px 8px; border-radius:999px; margin-top:6px; }
    .flag.transit { background:#FFFBEB; color:#92400E; }
    .flag.held { background:#FEF2F2; color:#991B1B; }
    .flag.open { background:#EFF6FF; color:#1E40AF; }
    .flag.ok { background:#ECFDF5; color:#047857; }
    .empty { text-align:center; padding:22px 12px; color:var(--mp-muted); font-size:13px; }
    .empty i { display:block; font-size:32px; color:var(--mp-border); margin-bottom:8px; }
    .stats { display:grid; grid-template-columns:1fr 1fr; gap:10px; margin-bottom:12px; }
    .stat { background:#fff; border:1px solid var(--mp-border); border-radius:14px; padding:13px; text-decoration:none; color:var(--mp-text); min-width:0; }
    .stat strong { display:block; font-size:19px; font-weight:700; line-height:1.2; overflow:hidden; text-overflow:ellipsis; }
    .stat span { display:block; font-size:11px; color:var(--mp-muted); margin-top:4px; line-height:1.35; }
    .stat.watch strong { color:var(--mp-warning); } .stat.alert strong { color:var(--mp-danger); }
    .litre { font-weight:700; font-variant-numeric:tabular-nums; }
    .bar { height:6px; border-radius:4px; background:#EEF2F7; overflow:hidden; margin-top:7px; }
    .bar i { display:block; height:100%; background:#2f6db5; }
    .bar.over i { background:var(--mp-danger); }
    .actions { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
    .action { display:flex; align-items:center; gap:9px; background:#fff; border:1px solid var(--mp-border); border-radius:12px; padding:12px; text-decoration:none; color:var(--mp-ink); font-size:12px; font-weight:600; }
    .action i { width:28px; height:28px; border-radius:8px; background:#EAF2FB; color:#1F4E79; display:flex; align-items:center; justify-content:center; flex:0 0 auto; }
    .form-group { margin-bottom:12px; }
    .form-group label { display:block; font-size:13px; font-weight:600; margin-bottom:6px; color:var(--mp-ink); }
    .form-group .req { color:var(--mp-danger); }
    .form-group .hint { display:block; font-size:11px; color:var(--mp-muted); margin-top:5px; line-height:1.4; }
    .form-group input, .form-group textarea { width:100%; padding:12px 14px; border:1px solid var(--mp-border); border-radius:12px; font-size:15px; background:#fff; color:var(--mp-text); outline:none; }
    .form-group textarea { resize:vertical; min-height:70px; }
    .form-row { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
    .btn { display:flex; align-items:center; justify-content:center; gap:8px; width:100%; padding:14px; border:none; border-radius:12px; font-size:15px; font-weight:700; cursor:pointer; }
    .btn-primary { background:var(--mp-primary); color:#fff; }
    .btn-ghost { background:#fff; color:var(--mp-ink); border:1px solid var(--mp-border); }
    .btn-danger { background:#FEF2F2; color:#991B1B; border:1px solid #FCA5A5; }
    .btn-pair { display:grid; grid-template-columns:1fr 1fr; gap:10px; }
    .mp-select { display:none; }
    .mp-select-wrap { position:relative; width:100%; }
    .mp-select-trigger { width:100%; padding:12px 14px; border:1px solid var(--mp-border); border-radius:12px; background:#fff; font-size:15px; color:var(--mp-text); cursor:pointer; display:flex; align-items:center; justify-content:space-between; }
    .mp-select-options { display:none; position:absolute; top:100%; left:0; right:0; z-index:200; background:#fff; border:1px solid var(--mp-border); border-radius:12px; max-height:220px; overflow-y:auto; margin-top:4px; box-shadow:0 4px 12px rgba(0,0,0,.08); }
    .mp-select-options.open { display:block; }
    .mp-option { padding:12px 14px; border-bottom:1px solid var(--mp-border); cursor:pointer; font-size:14px; }
    .mp-option:last-child { border-bottom:none; }
    .mp-option.active { background:#E0E7FF; color:var(--mp-primary); font-weight:600; }
    .meter { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:11px 0; border-top:1px solid var(--mp-border); }
    .meter:first-of-type { border-top:none; padding-top:0; }
    .meter input { width:130px; padding:11px 12px; border:1px solid var(--mp-border); border-radius:10px; font-size:15px; text-align:right; outline:none; }
    .meter .pump { font-size:14px; font-weight:600; }
    .meter .sub { font-size:11px; color:var(--mp-muted); margin-top:3px; }
    .note { font-size:12px; color:var(--mp-muted); line-height:1.5; margin:10px 0 0; }
    .callout { border-radius:12px; padding:12px 14px; font-size:12.5px; line-height:1.5; margin-bottom:12px; }
    .callout.warn { background:#FFFBEB; color:#92400E; }
    .callout.bad { background:#FEF2F2; color:#991B1B; }
    .callout.info { background:#EFF6FF; color:#1E40AF; }
    @media (min-width:600px){ #app{max-width:100%;margin:0} .screen{padding:16px 16px 120px} .stats{grid-template-columns:repeat(4,1fr)} .actions{grid-template-columns:repeat(4,1fr)} }
    @media (min-width:1024px){ .screen{padding:24px 48px 140px} }
  </style>
</head>
<body>
<?php /* The phone screen must say what just happened, the way the desktop one does. */ ?>
<?php foreach(array('success' => 'success', 'error' => 'danger', 'failed' => 'danger', 'warning' => 'warning') as $flash_key => $toast_type): ?>
  <?php if($this->session->flashdata($flash_key)): ?>
    <?php $gas_flash = trim(strip_tags(str_ireplace(array('</p>','<br>','<br/>','<br />'), array(' ',' ',' ',' '), $this->session->flashdata($flash_key)))); ?>
    <script>if(!window.mpFlashMessages) window.mpFlashMessages = []; window.mpFlashMessages.push({msg: <?= json_encode($gas_flash, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>, type: '<?= $toast_type; ?>'});</script>
  <?php endif; ?>
<?php endforeach; ?>
  <div id='app'>
    <section class='screen'>
      <div class='topbar'>
        <div class='store-name'><?= $gas_store; ?></div>
        <h1><?= htmlspecialchars($gas_title ?? 'Oil &amp; Gas'); ?></h1>
        <?php if(!empty($gas_sub)): ?><p><?= htmlspecialchars($gas_sub); ?></p><?php endif; ?>
      </div>
