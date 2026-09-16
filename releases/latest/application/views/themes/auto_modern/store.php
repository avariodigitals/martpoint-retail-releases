<?php
$slug = $settings->store_slug ?? '';
$waNumber = preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? '');
$hero = !empty($hero_banners) ? $hero_banners[0] : null;
$heroImg = ($hero && !empty($hero->desktop_image) && file_exists($hero->desktop_image)) ? mp_minified_image_url($hero->desktop_image, 1600) : '';
$logo = $logo_url ?? base_url('uploads/site/icon.webp');
?>
<style>
  :root {
    --primary:#2563EB;
    --primary-dark:#1D4ED8;
    --text:#0B1220;
    --muted:#64748B;
    --bg:#FFFFFF;
    --surface:#F8FAFC;
    --border:#E2E8F0;
    --dark:#0B1220;
  }
  .mp-topbar, .mp-announcement, .mp-nav, .mp-header, .mp-mobile-menu-btn, .mp-footer-space { display:none !important; }

  .am-header { position:sticky; top:0; z-index:1000; background:rgba(255,255,255,0.96); backdrop-filter:blur(10px); border-bottom:1px solid var(--border); }
  .am-header-inner { max-width:1280px; margin:0 auto; padding:14px 24px; display:flex; align-items:center; justify-content:space-between; }
  .am-logo { display:flex; align-items:center; gap:10px; font-weight:800; font-size:18px; color:var(--text); }
  .am-logo img { height:36px; width:auto; }
  .am-nav { display:flex; align-items:center; gap:24px; }
  .am-nav a { font-size:14px; font-weight:500; color:var(--muted); text-decoration:none; }
  .am-nav a:hover { color:var(--primary); }
  .am-wa, .am-nav .am-wa { padding:10px 18px; border-radius:999px; background:#25D366; color:#fff !important; font-size:14px; font-weight:600; }
  .am-wa:hover, .am-nav .am-wa:hover { background:#1FB855; color:#fff !important; }

  .am-hero { position:relative; min-height:560px; display:flex; align-items:center; overflow:hidden; }
  .am-hero-bg { position:absolute; inset:0; z-index:-1; }
  .am-hero-bg img { width:100%; height:100%; object-fit:cover; }
  .am-hero-overlay { position:absolute; inset:0; background:linear-gradient(90deg, rgba(11,18,32,0.88) 0%, rgba(11,18,32,0.5) 100%); }
  .am-hero-inner { max-width:1280px; margin:0 auto; padding:80px 24px; width:100%; }
  .am-hero-content { max-width:620px; color:#fff; }
  .am-hero-tag { display:inline-block; padding:6px 14px; background:rgba(255,255,255,0.12); border-radius:999px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:18px; }
  .am-hero h1 { font-family:var(--mp-font); font-size:clamp(40px,5vw,64px); font-weight:800; margin:0 0 20px; line-height:1.05; letter-spacing:-0.02em; }
  .am-hero p { font-size:18px; line-height:1.6; color:#CBD5E1; margin-bottom:32px; }
  .am-hero-actions { display:flex; gap:14px; flex-wrap:wrap; }
  .am-btn { padding:15px 32px; border-radius:999px; font-size:16px; font-weight:600; text-decoration:none; display:inline-flex; align-items:center; gap:8px; }
  .am-btn-primary { background:var(--primary); color:#fff; }
  .am-btn-primary:hover { background:var(--primary-dark); color:#fff; }
  .am-btn-ghost { background:transparent; color:#fff; border:1.5px solid rgba(255,255,255,0.4); }
  .am-btn-ghost:hover { background:#fff; color:var(--text); }

  .am-trust { background:var(--surface); border-top:1px solid var(--border); border-bottom:1px solid var(--border); }
  .am-trust-inner { max-width:1280px; margin:0 auto; padding:40px 24px; display:grid; grid-template-columns:repeat(4,1fr); gap:24px; }
  @media(max-width:767px){ .am-trust-inner { grid-template-columns:1fr; } }
  .am-trust-item { display:flex; align-items:center; gap:14px; }
  .am-trust-icon { width:44px; height:44px; border-radius:50%; background:#EFF6FF; color:var(--primary); display:flex; align-items:center; justify-content:center; font-size:18px; }
  .am-trust-title { font-weight:700; color:var(--text); }
  .am-trust-desc { font-size:13px; color:var(--muted); }

  @media(max-width:767px){
    .am-nav a:not(.am-wa) { display:none; }
    .am-hero h1 { font-size:36px; }
  }
</style>

<header class="am-header">
  <div class="am-header-inner">
    <div class="am-logo">
      <?php if($logo): ?><img src="<?= $logo; ?>" alt="<?= htmlspecialchars($store->store_name); ?>"><?php endif; ?>
      <span><?= htmlspecialchars($store->store_name); ?></span>
    </div>
    <nav class="am-nav">
      <a href="<?= base_url('store/' . $slug . '/vehicles'); ?>">Vehicles</a>
      <?php if(!empty($waNumber)): ?>
        <a href="https://wa.me/<?= $waNumber; ?>" target="_blank" class="am-wa"><i class="fa fa-whatsapp"></i> Chat</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<section class="am-hero">
  <div class="am-hero-bg">
    <?php if($heroImg): ?>
      <?= mp_image_tag($hero->desktop_image, ['width' => 1600, 'alt' => 'Hero', 'style' => 'width:100%;height:100%;']); ?>
    <?php endif; ?>
  </div>
  <div class="am-hero-overlay"></div>
  <div class="am-hero-inner">
    <div class="am-hero-content">
      <span class="am-hero-tag">Premium Vehicles</span>
      <h1>Find the right car, fast.</h1>
      <p>Browse verified vehicles, get full specs, and ask the dealer directly on WhatsApp. No vague catalogues — just real cars with real details.</p>
      <div class="am-hero-actions">
        <a href="<?= base_url('store/' . $slug . '/vehicles'); ?>" class="am-btn am-btn-primary">Browse Vehicles</a>
        <?php if(!empty($waNumber)): ?>
          <a href="https://wa.me/<?= $waNumber; ?>?text=<?= rawurlencode('Hi, I want to see your available vehicles at ' . base_url('store/' . $slug . '/vehicles')); ?>" target="_blank" class="am-btn am-btn-ghost"><i class="fa fa-whatsapp"></i> Ask on WhatsApp</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</section>

<section class="am-trust">
  <div class="am-trust-inner">
    <div class="am-trust-item">
      <div class="am-trust-icon"><i class="fa fa-car"></i></div>
      <div><div class="am-trust-title">Verified Stock</div><div class="am-trust-desc">Every car has VIN, mileage & history.</div></div>
    </div>
    <div class="am-trust-item">
      <div class="am-trust-icon"><i class="fa fa-whatsapp"></i></div>
      <div><div class="am-trust-title">WhatsApp Deals</div><div class="am-trust-desc">Chat the dealer instantly.</div></div>
    </div>
    <div class="am-trust-item">
      <div class="am-trust-icon"><i class="fa fa-shield"></i></div>
      <div><div class="am-trust-title">Transparent Pricing</div><div class="am-trust-desc">No hidden fees or surprises.</div></div>
    </div>
    <div class="am-trust-item">
      <div class="am-trust-icon"><i class="fa fa-map-marker"></i></div>
      <div><div class="am-trust-title">Visit the Lot</div><div class="am-trust-desc">Book a test drive today.</div></div>
    </div>
  </div>
</section>

<?php include(APPPATH . 'views/themes/shared/sections/vehicle_featured.php'); ?>
