<?php
$slug = $settings->store_slug ?? '';
$waNumber = preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? '');
$hero = !empty($hero_banners) ? $hero_banners[0] : null;
$heroImg = ($hero && !empty($hero->desktop_image) && file_exists($hero->desktop_image)) ? mp_minified_image_url($hero->desktop_image, 1600) : '';
$logo = $logo_url ?? base_url('uploads/site/icon.webp');
?>
<style>
  :root {
    --primary:#C9A961;
    --primary-dark:#B08D4B;
    --text:#FFFFFF;
    --muted:#94A3B8;
    --bg:#0B0F1A;
    --surface:#151B2B;
    --border:#273044;
    --dark:#0B0F1A;
  }
  .mp-topbar, .mp-announcement, .mp-nav, .mp-header, .mp-mobile-menu-btn, .mp-footer-space { display:none !important; }

  .al-header { position:sticky; top:0; z-index:1000; background:rgba(11,15,26,0.95); backdrop-filter:blur(10px); border-bottom:1px solid var(--border); }
  .al-header-inner { max-width:1280px; margin:0 auto; padding:16px 24px; display:flex; align-items:center; justify-content:space-between; }
  .al-logo { display:flex; align-items:center; gap:12px; font-family:var(--mp-font); font-weight:800; font-size:20px; color:#fff; }
  .al-logo img { height:38px; width:auto; }
  .al-nav { display:flex; align-items:center; gap:28px; }
  .al-nav a { font-size:14px; font-weight:500; color:var(--muted); text-decoration:none; }
  .al-nav a:hover { color:#fff; }
  .al-wa, .al-nav .al-wa { padding:10px 20px; border-radius:999px; background:#25D366; color:#fff !important; font-size:14px; font-weight:600; }
  .al-wa:hover, .al-nav .al-wa:hover { background:#1FB855; color:#fff !important; }

  .al-hero { position:relative; min-height:620px; display:flex; align-items:center; text-align:center; }
  .al-hero-bg { position:absolute; inset:0; z-index:-1; }
  .al-hero-bg img { width:100%; height:100%; object-fit:cover; }
  .al-hero-overlay { position:absolute; inset:0; background:linear-gradient(180deg, rgba(11,15,26,0.7) 0%, rgba(11,15,26,0.92) 100%); }
  .al-hero-inner { max-width:1280px; margin:0 auto; padding:120px 24px; width:100%; position:relative; }
  .al-hero-tag { display:inline-block; padding:7px 16px; border:1px solid var(--primary); border-radius:999px; font-size:12px; font-weight:700; color:var(--primary); text-transform:uppercase; letter-spacing:0.08em; margin-bottom:22px; }
  .al-hero h1 { font-family:var(--mp-font); font-size:clamp(42px,5vw,70px); font-weight:800; margin:0 0 22px; color:#fff; letter-spacing:-0.02em; }
  .al-hero p { font-size:18px; line-height:1.7; color:var(--muted); max-width:620px; margin:0 auto 36px; }
  .al-hero-actions { display:flex; justify-content:center; gap:16px; flex-wrap:wrap; }
  .al-btn { padding:16px 34px; border-radius:999px; font-size:15px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:8px; }
  .al-btn-primary { background:var(--primary); color:#0B0F1A; }
  .al-btn-primary:hover { background:var(--primary-dark); color:#0B0F1A; }
  .al-btn-ghost { background:transparent; color:#fff; border:1.5px solid var(--border); }
  .al-btn-ghost:hover { background:#fff; color:#0B0F1A; }

  .al-trust { background:var(--surface); border-bottom:1px solid var(--border); }
  .al-trust-inner { max-width:1280px; margin:0 auto; padding:50px 24px; display:grid; grid-template-columns:repeat(4,1fr); gap:24px; }
  @media(max-width:767px){ .al-trust-inner { grid-template-columns:1fr 1fr; } }
  @media(max-width:480px){ .al-trust-inner { grid-template-columns:1fr; } }
  .al-trust-item { text-align:center; }
  .al-trust-icon { width:56px; height:56px; border-radius:50%; border:1px solid var(--border); color:var(--primary); display:flex; align-items:center; justify-content:center; margin:0 auto 14px; font-size:22px; }
  .al-trust-title { font-weight:700; color:#fff; margin-bottom:6px; }
  .al-trust-desc { font-size:13px; color:var(--muted); }

  @media(max-width:767px){
    .al-hero h1 { font-size:38px; }
    .al-nav a:not(.al-wa) { display:none; }
  }
</style>

<header class="al-header">
  <div class="al-header-inner">
    <div class="al-logo">
      <?php if($logo): ?><img src="<?= $logo; ?>" alt="<?= htmlspecialchars($store->store_name); ?>"><?php endif; ?>
      <span><?= htmlspecialchars($store->store_name); ?></span>
    </div>
    <nav class="al-nav">
      <a href="<?= base_url('store/' . $slug . '/vehicles'); ?>">Collection</a>
      <?php if(!empty($waNumber)): ?>
        <a href="https://wa.me/<?= $waNumber; ?>" target="_blank" class="al-wa"><i class="fa fa-whatsapp"></i> Enquire</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<section class="al-hero">
  <div class="al-hero-bg">
    <?php if($heroImg): ?>
      <?= mp_image_tag($hero->desktop_image, ['width' => 1600, 'alt' => 'Hero', 'style' => 'width:100%;height:100%;']); ?>
    <?php endif; ?>
  </div>
  <div class="al-hero-overlay"></div>
  <div class="al-hero-inner">
    <span class="al-hero-tag">Premium Selection</span>
    <h1>Drive something exceptional.</h1>
    <p>Curated vehicles, full provenance, and a buying experience as smooth as the drive. Ask us anything on WhatsApp.</p>
    <div class="al-hero-actions">
      <a href="<?= base_url('store/' . $slug . '/vehicles'); ?>" class="al-btn al-btn-primary">View Collection</a>
      <?php if(!empty($waNumber)): ?>
        <a href="https://wa.me/<?= $waNumber; ?>?text=<?= rawurlencode('Hi, I would like to enquire about your premium vehicles at ' . base_url('store/' . $slug . '/vehicles')); ?>" target="_blank" class="al-btn al-btn-ghost"><i class="fa fa-whatsapp"></i> WhatsApp Us</a>
      <?php endif; ?>
    </div>
  </div>
</section>

<section class="al-trust">
  <div class="al-trust-inner">
    <div class="al-trust-item">
      <div class="al-trust-icon"><i class="fa fa-diamond"></i></div>
      <div class="al-trust-title">Premium Stock</div>
      <div class="al-trust-desc">Hand-picked vehicles</div>
    </div>
    <div class="al-trust-item">
      <div class="al-trust-icon"><i class="fa fa-file-text-o"></i></div>
      <div class="al-trust-title">Full History</div>
      <div class="al-trust-desc">Ownership & service records</div>
    </div>
    <div class="al-trust-item">
      <div class="al-trust-icon"><i class="fa fa-handshake-o"></i></div>
      <div class="al-trust-title">Personal Service</div>
      <div class="al-trust-desc">Dealer on WhatsApp</div>
    </div>
    <div class="al-trust-item">
      <div class="al-trust-icon"><i class="fa fa-certificate"></i></div>
      <div class="al-trust-title">Verified Value</div>
      <div class="al-trust-desc">Transparent pricing</div>
    </div>
  </div>
</section>

<?php include(APPPATH . 'views/themes/shared/sections/vehicle_featured.php'); ?>
