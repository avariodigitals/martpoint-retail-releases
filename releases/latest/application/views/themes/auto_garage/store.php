<?php
$slug = $settings->store_slug ?? '';
$waNumber = preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? '');
$hero = !empty($hero_banners) ? $hero_banners[0] : null;
$heroImg = ($hero && !empty($hero->desktop_image) && file_exists($hero->desktop_image)) ? mp_minified_image_url($hero->desktop_image, 1600) : '';
$logo = $logo_url ?? base_url('uploads/site/icon.webp');
?>
<style>
  :root {
    --primary:#DC2626;
    --primary-dark:#B91C1C;
    --text:#111827;
    --muted:#6B7280;
    --bg:#F9FAFB;
    --surface:#FFFFFF;
    --border:#E5E7EB;
    --dark:#1F2937;
  }
  .mp-topbar, .mp-announcement, .mp-nav, .mp-header, .mp-mobile-menu-btn, .mp-footer-space { display:none !important; }

  .ag-header { background:var(--dark); color:#fff; }
  .ag-header-inner { max-width:1280px; margin:0 auto; padding:14px 24px; display:flex; align-items:center; justify-content:space-between; }
  .ag-logo { display:flex; align-items:center; gap:10px; font-weight:800; font-size:18px; }
  .ag-logo img { height:36px; width:auto; }
  .ag-nav { display:flex; align-items:center; gap:24px; }
  .ag-nav a { font-size:14px; font-weight:500; color:#D1D5DB; text-decoration:none; }
  .ag-nav a:hover { color:#fff; }
  .ag-wa, .ag-nav .ag-wa { padding:10px 18px; border-radius:4px; background:#25D366; color:#fff !important; font-size:14px; font-weight:600; }
  .ag-wa:hover, .ag-nav .ag-wa:hover { background:#1FB855; color:#fff !important; }

  .ag-hero { position:relative; background:var(--dark); color:#fff; min-height:580px; display:flex; align-items:center; overflow:hidden; }
  .ag-hero-bg { position:absolute; inset:0; z-index:1; }
  .ag-hero-bg img { width:100%; height:100%; object-fit:cover; opacity:0.5; }
  .ag-hero-inner { position:relative; z-index:2; max-width:1280px; margin:0 auto; padding:90px 24px; width:100%; }
  .ag-hero-grid { display:grid; grid-template-columns:1.2fr 1fr; gap:48px; align-items:center; }
  @media(max-width:1023px){ .ag-hero-grid { grid-template-columns:1fr; } }
  .ag-hero h1 { font-family:var(--mp-font); font-size:clamp(44px,5vw,72px); font-weight:900; margin:0 0 20px; line-height:1; letter-spacing:-0.03em; }
  .ag-hero h1 span { color:var(--primary); }
  .ag-hero p { font-size:18px; color:#D1D5DB; margin-bottom:32px; line-height:1.6; }
  .ag-hero-actions { display:flex; gap:14px; flex-wrap:wrap; }
  .ag-btn { padding:16px 32px; border-radius:4px; font-size:16px; font-weight:700; text-decoration:none; display:inline-flex; align-items:center; gap:8px; }
  .ag-btn-primary { background:var(--primary); color:#fff; }
  .ag-btn-primary:hover { background:var(--primary-dark); color:#fff; }
  .ag-btn-ghost { background:transparent; color:#fff; border:1.5px solid #4B5563; }
  .ag-btn-ghost:hover { background:#fff; color:var(--dark); }
  .ag-hero-badges { display:flex; gap:14px; flex-wrap:wrap; }
  .ag-badge { padding:10px 18px; background:rgba(255,255,255,0.08); border-left:3px solid var(--primary); font-weight:600; }

  .ag-trust { background:var(--surface); border-top:1px solid var(--border); border-bottom:1px solid var(--border); }
  .ag-trust-inner { max-width:1280px; margin:0 auto; padding:42px 24px; display:grid; grid-template-columns:repeat(3,1fr); gap:24px; }
  @media(max-width:767px){ .ag-trust-inner { grid-template-columns:1fr; } }
  .ag-trust-item { display:flex; gap:14px; }
  .ag-trust-icon { color:var(--primary); font-size:28px; }
  .ag-trust-title { font-weight:800; color:var(--text); margin-bottom:4px; }
  .ag-trust-desc { font-size:13px; color:var(--muted); }

  @media(max-width:767px){
    .ag-hero h1 { font-size:40px; }
    .ag-nav a:not(.ag-wa) { display:none; }
    .ag-hero-badges { display:none; }
  }
</style>

<header class="ag-header">
  <div class="ag-header-inner">
    <div class="ag-logo">
      <?php if($logo): ?><img src="<?= $logo; ?>" alt="<?= htmlspecialchars($store->store_name); ?>"><?php endif; ?>
      <span><?= htmlspecialchars($store->store_name); ?></span>
    </div>
    <nav class="ag-nav">
      <a href="<?= base_url('store/' . $slug . '/vehicles'); ?>">All Rides</a>
      <?php if(!empty($waNumber)): ?>
        <a href="https://wa.me/<?= $waNumber; ?>" target="_blank" class="ag-wa"><i class="fa fa-whatsapp"></i> Chat</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<section class="ag-hero">
  <div class="ag-hero-bg">
    <?php if($heroImg): ?>
      <?= mp_image_tag($hero->desktop_image, ['width' => 1600, 'alt' => 'Hero', 'style' => 'width:100%;height:100%;']); ?>
    <?php endif; ?>
  </div>
  <div class="ag-hero-inner">
    <div class="ag-hero-grid">
      <div>
        <h1>Find your <span>next ride.</span></h1>
        <p>Tough, clean, and ready to drive. Trucks, SUVs, sedans and more with full specs and dealer WhatsApp support. No pressure, just real cars.</p>
        <div class="ag-hero-actions">
          <a href="<?= base_url('store/' . $slug . '/vehicles'); ?>" class="ag-btn ag-btn-primary">Browse All Rides</a>
          <?php if(!empty($waNumber)): ?>
            <a href="https://wa.me/<?= $waNumber; ?>?text=<?= rawurlencode('Hi, show me your available rides at ' . base_url('store/' . $slug . '/vehicles')); ?>" target="_blank" class="ag-btn ag-btn-ghost"><i class="fa fa-whatsapp"></i> WhatsApp</a>
          <?php endif; ?>
        </div>
      </div>
      <div class="ag-hero-badges">
        <div class="ag-badge"><i class="fa fa-check"></i> Inspected</div>
        <div class="ag-badge"><i class="fa fa-wrench"></i> Service Ready</div>
        <div class="ag-badge"><i class="fa fa-money"></i> Fair Pricing</div>
      </div>
    </div>
  </div>
</section>

<section class="ag-trust">
  <div class="ag-trust-inner">
    <div class="ag-trust-item">
      <div class="ag-trust-icon"><i class="fa fa-search"></i></div>
      <div><div class="ag-trust-title">Full Inspections</div><div class="ag-trust-desc">Every vehicle is checked before listing.</div></div>
    </div>
    <div class="ag-trust-item">
      <div class="ag-trust-icon"><i class="fa fa-road"></i></div>
      <div><div class="ag-trust-title">Test Drives</div><div class="ag-trust-desc">Book a drive before you decide.</div></div>
    </div>
    <div class="ag-trust-item">
      <div class="ag-trust-icon"><i class="fa fa-comments"></i></div>
      <div><div class="ag-trust-title">Fast Replies</div><div class="ag-trust-desc">Dealer chat on WhatsApp.</div></div>
    </div>
  </div>
</section>

<?php include(APPPATH . 'views/themes/shared/sections/vehicle_featured.php'); ?>
