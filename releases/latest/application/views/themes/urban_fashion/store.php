<?php
/**
 * Urban Editorial — Fashion Homepage
 * Bold magazine-style layout with full-bleed hero, editorial split blocks,
 * sharp corners and high-contrast typography.
 * Respects the homepage builder section order and visibility.
 */
$slug = $settings->store_slug ?? '';
$cur = $store_currency ?? null;

// Sort homepage sections by display_order
$orderedSections = [];
if(!empty($homepage_sections)){
  $orderedSections = $homepage_sections;
  uasort($orderedSections, function($a, $b){ return ($a->display_order ?? 0) <=> ($b->display_order ?? 0); });
}

// Hero banner
$hero = !empty($hero_banners) ? $hero_banners[0] : null;
$heroImg = '';
if($hero && $hero->desktop_image) $heroImg = base_url($hero->desktop_image);
elseif($hero && $hero->mobile_image) $heroImg = base_url($hero->mobile_image);

// WhatsApp number
$waNumber = preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? '');
?>
<style>
  :root { --ue-ink:#0A0A0A; --ue-accent:#FF3B30; --ue-cream:#FAFAF8; --ue-soft:#F5F5F0; }

  .theme-urban_fashion .mp-topbar,
  .theme-urban_fashion .mp-announcement,
  .theme-urban_fashion .mp-nav,
  .theme-urban_fashion .mp-header,
  .theme-urban_fashion .mp-mobile-menu-btn,
  .theme-urban_fashion .mp-footer-space { display:none !important; }

  .ue-container { max-width:1400px; margin:0 auto; padding:0 24px; }
  @media(max-width:767px){ .ue-container { padding:0 16px; } }

  /* Marquee strip */
  .ue-marquee { background:var(--ue-ink); color:#fff; overflow:hidden; padding:10px 0; }
  .ue-marquee-track { display:flex; gap:48px; white-space:nowrap; animation:ue-marquee 30s linear infinite; }
  @keyframes ue-marquee { from{transform:translateX(0);} to{transform:translateX(-50%);} }
  .ue-marquee-item { font-family:'Montserrat',sans-serif; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.2em; color:#fff; }
  .ue-marquee-item .dot { color:var(--ue-accent); margin-right:48px; }

  /* Hero */
  .ue-hero { position:relative; background:var(--ue-ink); color:#fff; overflow:hidden; min-height:600px; display:flex; align-items:center; }
  @media(max-width:767px){ .ue-hero { min-height:440px; } }
  .ue-hero-media { position:absolute; inset:0; opacity:0.5; }
  .ue-hero-media img { width:100%; height:100%; object-fit:cover; }
  .ue-hero-gradient { position:absolute; inset:0; background:linear-gradient(90deg, rgba(10,10,10,0.85) 0%, rgba(10,10,10,0.4) 55%, rgba(10,10,10,0.1) 100%); }
  .ue-hero-content { position:relative; z-index:2; padding:100px 24px 120px; max-width:1400px; margin:0 auto; width:100%; }
  @media(max-width:767px){ .ue-hero-content { padding:60px 16px 80px; } }
  .ue-hero-kicker { font-size:11px; text-transform:uppercase; letter-spacing:0.2em; color:var(--ue-accent); margin-bottom:16px; font-weight:700; }
  .ue-hero-title { font-family:'Montserrat',sans-serif; font-size:clamp(40px,6vw,72px); line-height:1.0; font-weight:800; margin:0 0 18px; max-width:680px; text-transform:uppercase; letter-spacing:-0.02em; }
  .ue-hero-lead { font-size:clamp(15px,2vw,18px); line-height:1.6; opacity:0.85; max-width:480px; margin-bottom:32px; }
  .ue-hero-actions { display:flex; gap:14px; flex-wrap:wrap; }

  /* Buttons */
  .ue-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:16px 36px; font-size:12px; font-weight:700; transition:transform .15s, background .2s; cursor:pointer; border:none; text-decoration:none; text-transform:uppercase; letter-spacing:0.1em; border-radius:0; }
  .ue-btn:active { transform:scale(0.98); }
  .ue-btn-primary { background:var(--ue-accent); color:#fff; }
  .ue-btn-primary:hover { background:#DC2F26; }
  .ue-btn-ghost { background:transparent; color:#fff; border:1.5px solid rgba(255,255,255,0.5); }
  .ue-btn-ghost:hover { background:rgba(255,255,255,0.1); border-color:#fff; }
  .ue-btn-dark { background:var(--ue-ink); color:#fff; }
  .ue-btn-dark:hover { background:var(--ue-accent); }
  .ue-btn-outline { background:#fff; color:var(--ue-ink); border:1.5px solid var(--ue-ink); }
  .ue-btn-outline:hover { background:var(--ue-ink); color:#fff; }
  .ue-btn-wa { background:#25D366; color:#fff; }
  .ue-btn-wa:hover { background:#1DA851; }

  /* Sections */
  .ue-section { padding:64px 0; }
  @media(max-width:767px){ .ue-section { padding:40px 0; } }
  .ue-section-head { display:flex; align-items:flex-end; justify-content:space-between; gap:16px; margin-bottom:32px; }
  .ue-section-label { font-size:11px; text-transform:uppercase; letter-spacing:0.18em; color:var(--ue-accent); font-weight:700; margin-bottom:6px; }
  .ue-section-title { font-family:'Montserrat',sans-serif; font-size:clamp(28px,4vw,42px); margin:0; font-weight:800; text-transform:uppercase; letter-spacing:-0.01em; color:var(--ue-ink); }
  .ue-section-link { font-size:11px; font-weight:700; border-bottom:2px solid var(--ue-ink); padding-bottom:2px; transition:color .2s; color:var(--ue-ink); text-decoration:none; text-transform:uppercase; letter-spacing:0.1em; }
  .ue-section-link:hover { color:var(--ue-accent); border-color:var(--ue-accent); }

  /* Product grid */
  .ue-product-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:24px; }
  @media(max-width:1023px){ .ue-product-grid { grid-template-columns:repeat(3,1fr); } }
  @media(max-width:767px){ .ue-product-grid { grid-template-columns:repeat(2,1fr); gap:14px; } }
  .ue-product-card { position:relative; background:transparent; overflow:hidden; transition:transform .2s; cursor:pointer; border:none; }
  .ue-product-card:hover { transform:translateY(-6px); }
  .ue-product-wishlist { position:absolute; top:14px; right:14px; width:36px; height:36px; border-radius:50%; background:#fff; display:flex; align-items:center; justify-content:center; z-index:2; transition:color .2s, transform .2s; box-shadow:0 2px 8px rgba(0,0,0,0.06); border:none; cursor:pointer; }
  .ue-product-wishlist:hover { color:var(--ue-accent); transform:scale(1.1); }
  .ue-product-badge { position:absolute; top:14px; left:14px; background:var(--ue-accent); color:#fff; font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:0.06em; padding:5px 10px; border-radius:0; z-index:2; }
  .ue-product-media { aspect-ratio:4/5; overflow:hidden; background:var(--ue-soft); }
  .ue-product-media img { width:100%; height:100%; object-fit:cover; transition:transform .45s ease; }
  .ue-product-card:hover .ue-product-media img { transform:scale(1.06); }
  .ue-product-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--ue-soft); color:#CCC; }
  .ue-product-placeholder span { font-family:'Montserrat',sans-serif; font-size:36px; font-weight:800; }
  .ue-product-body { padding:14px 2px; }
  .ue-product-brand { font-size:10px; text-transform:uppercase; letter-spacing:0.12em; color:#999; font-weight:700; margin-bottom:4px; }
  .ue-product-name { font-size:13px; font-weight:500; margin-bottom:8px; line-height:1.35; display:-webkit-box; -webkit-line-clamp:2; line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; min-height:36px; color:#3A3A3A; }
  .ue-product-footer { display:flex; align-items:center; justify-content:space-between; gap:10px; }
  .ue-product-price { font-size:15px; font-weight:700; color:var(--ue-ink); }
  .ue-product-price .old { font-size:12px; color:#999; text-decoration:line-through; margin-left:6px; font-weight:500; }
  .ue-add-btn { width:38px; height:38px; border-radius:50%; background:var(--ue-ink); color:#fff; display:flex; align-items:center; justify-content:center; transition:background .2s, transform .15s; border:none; cursor:pointer; flex-shrink:0; }
  .ue-add-btn:hover { background:var(--ue-accent); }
  .ue-add-btn:active { transform:scale(0.95); }
  .ue-product-stock { font-size:10px; color:#EF4444; font-weight:700; margin-top:6px; text-transform:uppercase; letter-spacing:0.06em; }

  /* Category cards — stylish editorial */
  .ue-cat-grid { display:grid; grid-template-columns:repeat(6,1fr); gap:16px; }
  @media(max-width:1023px){ .ue-cat-grid { grid-template-columns:repeat(4,1fr); } }
  @media(max-width:767px){ .ue-cat-grid { grid-template-columns:repeat(3,1fr); gap:12px; } }
  .ue-cat-card { position:relative; border-radius:0; overflow:hidden; aspect-ratio:3/2; text-decoration:none; color:inherit; display:block; transition:transform .25s; }
  .ue-cat-card:hover { transform:translateY(-6px); }
  .ue-cat-card-media { position:absolute; inset:0; background:var(--ue-soft); }
  .ue-cat-card-media img { width:100%; height:100%; object-fit:cover; transition:transform .5s ease; }
  .ue-cat-card:hover .ue-cat-card-media img { transform:scale(1.08); }
  .ue-cat-card-overlay { position:absolute; inset:0; background:linear-gradient(180deg, transparent 35%, rgba(10,10,10,0.85) 100%); }
  .ue-cat-card-body { position:absolute; bottom:0; left:0; right:0; padding:20px; z-index:2; }
  .ue-cat-card-name { font-family:'Montserrat',sans-serif; font-size:14px; font-weight:800; text-transform:uppercase; letter-spacing:0.06em; color:#fff; margin-bottom:4px; }
  .ue-cat-card-count { font-size:11px; text-transform:uppercase; letter-spacing:0.1em; color:var(--ue-accent); font-weight:700; }
  .ue-cat-card-arrow { position:absolute; top:16px; right:16px; width:32px; height:32px; border-radius:0; background:var(--ue-accent); display:flex; align-items:center; justify-content:center; opacity:0; transform:translateY(-4px); transition:opacity .25s, transform .25s; }
  .ue-cat-card:hover .ue-cat-card-arrow { opacity:1; transform:translateY(0); }
  .ue-cat-card-arrow svg { width:16px; height:16px; color:#fff; }
  .ue-cat-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--ue-ink); }
  .ue-cat-placeholder span { font-family:'Montserrat',sans-serif; font-size:42px; font-weight:800; color:var(--ue-accent); }

  /* Trust badges / values */
  .ue-values { background:var(--ue-ink); color:#fff; padding:48px 0; }
  .ue-values-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:24px; }
  @media(max-width:767px){ .ue-values-grid { grid-template-columns:1fr; } }
  .ue-value { text-align:center; padding:16px; }
  .ue-value-icon { width:56px; height:56px; border-radius:0; background:var(--ue-accent); display:flex; align-items:center; justify-content:center; font-size:24px; margin:0 auto 16px; }
  .ue-value-title { font-family:'Montserrat',sans-serif; font-size:14px; font-weight:800; text-transform:uppercase; letter-spacing:0.06em; color:#fff; margin-bottom:8px; }
  .ue-value-text { font-size:13px; color:rgba(255,255,255,0.7); line-height:1.6; }

  /* Promo banner */
  .ue-promo { background:var(--ue-soft); border-radius:0; overflow:hidden; display:flex; align-items:center; min-height:200px; }
  @media(max-width:767px){ .ue-promo { flex-direction:column; min-height:auto; } }
  .ue-promo-media { flex:1; min-height:200px; background:var(--ue-cream); }
  .ue-promo-media img { width:100%; height:100%; object-fit:cover; }
  .ue-promo-body { flex:1; padding:40px; }
  @media(max-width:767px){ .ue-promo-body { padding:24px; } }
  .ue-promo-kicker { font-size:11px; text-transform:uppercase; letter-spacing:0.18em; color:var(--ue-accent); font-weight:700; margin-bottom:8px; }
  .ue-promo-title { font-family:'Montserrat',sans-serif; font-size:clamp(22px,3vw,32px); font-weight:800; text-transform:uppercase; letter-spacing:-0.01em; color:var(--ue-ink); margin-bottom:10px; }
  .ue-promo-text { font-size:15px; color:#4A4A4A; line-height:1.6; margin-bottom:20px; }

  /* WhatsApp CTA */
  .ue-wa-cta { background:linear-gradient(135deg, #25D366, #1DA851); color:#fff; padding:48px 24px; text-align:center; border-radius:0; }
  .ue-wa-cta-title { font-family:'Montserrat',sans-serif; font-size:clamp(22px,3vw,30px); font-weight:800; text-transform:uppercase; letter-spacing:-0.01em; margin-bottom:10px; }
  .ue-wa-cta-text { opacity:0.9; max-width:440px; margin:0 auto 24px; font-size:15px; line-height:1.6; }
  .ue-wa-cta .ue-btn { background:#fff; color:#1DA851; }
  .ue-wa-cta .ue-btn:hover { background:var(--ue-ink); color:#fff; }

  /* Store info */
  .ue-store-info { background:var(--ue-ink); color:#fff; padding:48px 0; }
  .ue-store-info-grid { display:grid; grid-template-columns:1fr 1fr; gap:32px; align-items:center; }
  @media(max-width:767px){ .ue-store-info-grid { grid-template-columns:1fr; } }
  .ue-store-info-title { font-family:'Montserrat',sans-serif; font-size:clamp(24px,3vw,34px); font-weight:800; text-transform:uppercase; letter-spacing:-0.01em; color:#fff; margin-bottom:14px; }
  .ue-store-info-text { font-size:15px; color:rgba(255,255,255,0.7); line-height:1.7; margin-bottom:20px; }
  .ue-store-info-detail { display:flex; align-items:center; gap:10px; margin-bottom:10px; font-size:14px; color:#fff; }
  .ue-store-info-detail svg { width:18px; height:18px; color:var(--ue-accent); flex-shrink:0; }

  /* Newsletter */
  .ue-newsletter { background:var(--ue-accent); color:#fff; padding:48px 24px; text-align:center; border-radius:0; }
  .ue-newsletter-title { font-family:'Montserrat',sans-serif; font-size:clamp(22px,3vw,30px); font-weight:800; text-transform:uppercase; letter-spacing:-0.01em; margin-bottom:10px; }
  .ue-newsletter-text { opacity:0.9; max-width:440px; margin:0 auto 24px; font-size:15px; }
  .ue-newsletter-form { display:flex; gap:10px; max-width:440px; margin:0 auto; }
  .ue-newsletter-form input { flex:1; padding:14px 18px; border:none; border-radius:0; font-size:14px; outline:none; }
  .ue-newsletter-form button { padding:14px 24px; border:none; border-radius:0; background:var(--ue-ink); color:#fff; font-weight:700; text-transform:uppercase; letter-spacing:0.1em; cursor:pointer; font-size:12px; }

  /* Testimonials */
  .ue-testimonials-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; }
  @media(max-width:767px){ .ue-testimonials-grid { grid-template-columns:1fr; } }
  .ue-testimonial { background:#fff; border:1px solid #E5E5E0; border-radius:0; padding:24px; }
  .ue-testimonial-stars { color:var(--ue-accent); margin-bottom:12px; font-size:16px; }
  .ue-testimonial-text { font-size:14px; color:#4A4A4A; line-height:1.7; margin-bottom:16px; }
  .ue-testimonial-author { font-family:'Montserrat',sans-serif; font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:0.06em; color:var(--ue-ink); }

  /* Brands */
  .ue-brands-grid { display:flex; flex-wrap:wrap; gap:20px; align-items:center; justify-content:center; }
  .ue-brand { padding:12px 24px; background:#fff; border:1px solid #E5E5E0; border-radius:0; font-family:'Montserrat',sans-serif; font-size:12px; font-weight:800; text-transform:uppercase; letter-spacing:0.06em; color:var(--ue-ink); }

  /* Instagram */
  .ue-insta-grid { display:grid; grid-template-columns:repeat(5,1fr); gap:12px; }
  @media(max-width:767px){ .ue-insta-grid { grid-template-columns:repeat(3,1fr); } }
  .ue-insta-item { aspect-ratio:1; border-radius:0; overflow:hidden; position:relative; }
  .ue-insta-item img { width:100%; height:100%; object-fit:cover; transition:transform .3s; }
  .ue-insta-item:hover img { transform:scale(1.08); }

  /* FAQs */
  .ue-faq-list { max-width:760px; margin:0 auto; }
  .ue-faq-item { background:#fff; border:1px solid #E5E5E0; border-radius:0; margin-bottom:10px; overflow:hidden; }
  .ue-faq-q { padding:18px 24px; font-family:'Montserrat',sans-serif; font-size:14px; font-weight:700; text-transform:uppercase; letter-spacing:0.04em; color:var(--ue-ink); cursor:pointer; display:flex; justify-content:space-between; align-items:center; }
  .ue-faq-q::after { content:'+'; font-size:20px; color:var(--ue-accent); }
  .ue-faq-item.open .ue-faq-q::after { content:'\2212'; }
  .ue-faq-a { max-height:0; overflow:hidden; transition:max-height .3s ease; padding:0 24px; }
  .ue-faq-item.open .ue-faq-a { max-height:300px; padding:0 24px 18px; }
  .ue-faq-a p { font-size:14px; color:#4A4A4A; line-height:1.7; margin:0; }

  /* Contact */
  .ue-contact-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; }
  @media(max-width:767px){ .ue-contact-grid { grid-template-columns:1fr; } }
  .ue-contact-card { background:#fff; border:1px solid #E5E5E0; border-radius:0; padding:24px; text-align:center; }
  .ue-contact-icon { width:48px; height:48px; border-radius:0; background:var(--ue-ink); display:flex; align-items:center; justify-content:center; margin:0 auto 14px; color:var(--ue-accent); }
  .ue-contact-icon svg { width:22px; height:22px; }
  .ue-contact-label { font-size:11px; text-transform:uppercase; letter-spacing:0.12em; color:var(--ue-accent); font-weight:700; margin-bottom:4px; }
  .ue-contact-value { font-size:15px; font-weight:700; color:var(--ue-ink); }

  /* Store hours */
  .ue-hours-grid { max-width:520px; margin:0 auto; }
  .ue-hours-row { display:flex; justify-content:space-between; padding:12px 0; border-bottom:1px solid #E5E5E0; font-size:14px; }
  .ue-hours-row:last-child { border-bottom:none; }
  .ue-hours-day { font-family:'Montserrat',sans-serif; font-weight:800; text-transform:uppercase; letter-spacing:0.06em; color:var(--ue-ink); font-size:12px; }
  .ue-hours-time { color:#4A4A4A; }
</style>

<?php
// Render sections in builder order
foreach($orderedSections as $sectionKey => $section):
  if(!$section->is_enabled) continue;

  switch($sectionKey):
    // =====================================================
    // HERO BANNER
    // =====================================================
    case 'hero_banner':
?>
<!-- HERO -->
<?php if($heroImg): ?>
<div class="ue-hero">
  <div class="ue-hero-media"><img src="<?= $heroImg; ?>" alt="<?= htmlspecialchars($hero->banner_title ?? ($store->store_name ?? 'Store')); ?>"></div>
  <div class="ue-hero-gradient"></div>
  <div class="ue-hero-content">
    <p class="ue-hero-kicker"><?= htmlspecialchars($hero->banner_subtitle ?? 'New Collection'); ?></p>
    <h1 class="ue-hero-title"><?= htmlspecialchars($hero->banner_title ?? ($settings->store_headline ?: ($store->store_name ?? 'Welcome'))); ?></h1>
    <p class="ue-hero-lead"><?= htmlspecialchars($settings->store_subheadline ?: 'Bold pieces for the confident wardrobe. Premium fabrics, clean silhouettes, statement essentials.'); ?></p>
    <div class="ue-hero-actions">
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="ue-btn ue-btn-primary">Shop the Drop</a>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="ue-btn ue-btn-ghost">View Lookbook</a>
    </div>
  </div>
</div>
<?php else: ?>
<div class="ue-hero">
  <div class="ue-hero-gradient"></div>
  <div class="ue-hero-content">
    <p class="ue-hero-kicker"><?= htmlspecialchars($settings->store_subheadline ?: 'Welcome'); ?></p>
    <h1 class="ue-hero-title"><?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Store')); ?></h1>
    <p class="ue-hero-lead"><?= htmlspecialchars($settings->store_description ?: 'Discover our curated collection.'); ?></p>
    <div class="ue-hero-actions">
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="ue-btn ue-btn-primary">Shop Now</a>
    </div>
  </div>
</div>
<?php endif; ?>
<?php
      break;

    // =====================================================
    // TRUST BADGES
    // =====================================================
    case 'trust_badges':
      $ueBadges = json_decode($settings->trust_badges_json ?? '', true);
      if(empty($ueBadges) || !is_array($ueBadges)){
        $ueBadges = [
          ['icon' => '&#128230;', 'title' => 'Fast Delivery', 'desc' => 'Quick and reliable shipping to your doorstep.'],
          ['icon' => '&#10024;', 'title' => 'Premium Quality', 'desc' => 'Carefully selected fabrics and finishes for lasting comfort.'],
          ['icon' => '&#129309;', 'title' => 'Personal Service', 'desc' => 'Reach us anytime on WhatsApp for styling help and orders.']
        ];
      }
?>
<!-- TRUST BADGES -->
<div class="ue-values">
  <div class="ue-container">
    <div class="ue-values-grid">
      <?php foreach(array_slice($ueBadges, 0, 3) as $b): ?>
      <div class="ue-value">
        <div class="ue-value-icon"><?= !empty($b['icon']) ? $b['icon'] : '&#10024;'; ?></div>
        <div class="ue-value-title"><?= htmlspecialchars($b['title'] ?? ''); ?></div>
        <div class="ue-value-text"><?= htmlspecialchars($b['desc'] ?? ''); ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php
      break;

    // =====================================================
    // PROMO BANNER
    // =====================================================
    case 'promo_banner':
      if(!empty($promo_banners)):
        $promo = $promo_banners[0];
        $promoImg = ($promo->desktop_image && file_exists($promo->desktop_image)) ? base_url($promo->desktop_image) : '';
?>
<!-- PROMO BANNER -->
<div class="ue-section">
  <div class="ue-container">
    <div class="ue-promo">
      <?php if($promoImg): ?>
      <div class="ue-promo-media"><img src="<?= $promoImg; ?>" alt="<?= htmlspecialchars($promo->banner_title ?? 'Promo'); ?>"></div>
      <?php endif; ?>
      <div class="ue-promo-body">
        <div class="ue-promo-kicker"><?= htmlspecialchars($promo->banner_subtitle ?? 'Limited Time'); ?></div>
        <h3 class="ue-promo-title"><?= htmlspecialchars($promo->banner_title ?? 'Special Offer'); ?></h3>
        <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="ue-btn ue-btn-dark">Shop Now</a>
      </div>
    </div>
  </div>
</div>
<?php
      endif;
      break;

    // =====================================================
    // FEATURED CATEGORIES — stylish editorial cards
    // =====================================================
    case 'featured_categories':
      if(!empty($categories) && count($categories) > 1):
?>
<!-- CATEGORIES -->
<div class="ue-section" style="background:var(--ue-cream);">
  <div class="ue-container">
    <div class="ue-section-head">
      <div>
        <div class="ue-section-label">Browse</div>
        <h2 class="ue-section-title">Shop by Category</h2>
      </div>
    </div>
    <div class="ue-cat-grid">
      <?php foreach(array_slice($categories, 0, 8) as $cat):
        $catImg = (!empty($cat->category_image) && file_exists($cat->category_image)) ? base_url($cat->category_image) : '';
        $itemCount = $cat->item_count ?? 0;
      ?>
      <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="ue-cat-card">
        <div class="ue-cat-card-media">
          <?php if($catImg): ?>
          <img src="<?= $catImg; ?>" alt="<?= htmlspecialchars($cat->category_name); ?>" loading="lazy">
          <?php else: ?>
          <div class="ue-cat-placeholder"><span><?= htmlspecialchars(substr($cat->category_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="ue-cat-card-overlay"></div>
        <div class="ue-cat-card-arrow">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </div>
        <div class="ue-cat-card-body">
          <div class="ue-cat-card-name"><?= htmlspecialchars($cat->category_name); ?></div>
          <?php if($itemCount > 0): ?>
          <div class="ue-cat-card-count"><?= $itemCount; ?> items</div>
          <?php endif; ?>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php
      endif;
      break;

    // =====================================================
    // FEATURED PRODUCTS
    // =====================================================
    case 'featured_products':
      if(!empty($featured_products)):
?>
<!-- FEATURED PRODUCTS -->
<div class="ue-section">
  <div class="ue-container">
    <div class="ue-section-head">
      <div>
        <div class="ue-section-label">Trending Now</div>
        <h2 class="ue-section-title">Featured Collection</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="ue-section-link">Shop All</a>
    </div>
    <div class="ue-product-grid">
      <?php foreach(array_slice($featured_products, 0, 8) as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $oldPrice = $p->original_price ?? $p->sales_price;
        $hasDiscount = $oldPrice > $price;
        $discountPct = $hasDiscount ? round((($oldPrice - $price) / $oldPrice) * 100) : 0;
        $img = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
      ?>
      <div class="ue-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, <?= $hasDiscount ? $oldPrice : 0; ?>)">
        <?php if($hasDiscount && $discountPct > 0): ?>
        <span class="ue-product-badge">-<?= $discountPct; ?>%</span>
        <?php endif; ?>
        <button class="ue-product-wishlist" onclick="event.stopPropagation();"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></button>
        <div class="ue-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
          <?php else: ?>
          <div class="ue-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="ue-product-body">
          <?php if(!empty($p->category_name)): ?>
          <div class="ue-product-brand"><?= htmlspecialchars($p->category_name); ?></div>
          <?php endif; ?>
          <div class="ue-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="ue-product-footer">
            <div class="ue-product-price"><?= sf_currency($price, $cur); ?><?php if($hasDiscount): ?><span class="old"><?= sf_currency($oldPrice, $cur); ?></span><?php endif; ?></div>
            <button class="ue-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></button>
          </div>
          <?php if($p->stock <= 0 && !($settings->allow_backorder ?? false)): ?>
          <div class="ue-product-stock">Out of Stock</div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php
      endif;
      break;

    // =====================================================
    // FEATURED SERVICES
    // =====================================================
    case 'featured_services':
      if(!empty($featured_services) && ($settings->allow_services ?? false)):
?>
<!-- FEATURED SERVICES -->
<div class="ue-section" style="background:var(--ue-cream);">
  <div class="ue-container">
    <div class="ue-section-head">
      <div>
        <div class="ue-section-label">Services</div>
        <h2 class="ue-section-title">Our Services</h2>
      </div>
    </div>
    <div class="ue-product-grid">
      <?php foreach(array_slice($featured_services, 0, 4) as $s):
        $sPrice = $s->effective_price ?? $s->sales_price ?? $s->price ?? 0;
        $sImg = (!empty($s->item_image) && file_exists($s->item_image)) ? base_url($s->item_image) : (!empty($s->service_image) && file_exists($s->service_image) ? base_url($s->service_image) : '');
      ?>
      <div class="ue-product-card" onclick="openProductModal(<?= $s->id; ?>, '<?= htmlspecialchars(addslashes($s->item_name ?? $s->service_name ?? '')); ?>', <?= $sPrice; ?>, '<?= $s->item_image ?? $s->service_image ?? ''; ?>', '<?= htmlspecialchars(addslashes($s->description ?? '')); ?>', 999, 0)">
        <div class="ue-product-media">
          <?php if($sImg): ?>
          <img src="<?= $sImg; ?>" alt="<?= htmlspecialchars($s->item_name ?? $s->service_name ?? ''); ?>" loading="lazy">
          <?php else: ?>
          <div class="ue-product-placeholder"><span><?= htmlspecialchars(substr($s->item_name ?? $s->service_name ?? 'S', 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="ue-product-body">
          <div class="ue-product-name"><?= htmlspecialchars($s->item_name ?? $s->service_name ?? ''); ?></div>
          <div class="ue-product-footer">
            <div class="ue-product-price"><?= sf_currency($sPrice, $cur); ?></div>
            <button class="ue-add-btn" onclick="event.stopPropagation();addToCart(<?= $s->id; ?>,'service','<?= htmlspecialchars(addslashes($s->item_name ?? $s->service_name ?? '')); ?>',<?= $sPrice; ?>,'<?= $s->item_image ?? $s->service_image ?? ''; ?>',1,999)" aria-label="Add"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php
      endif;
      break;

    // =====================================================
    // BEST SELLERS
    // =====================================================
    case 'best_sellers':
      if(!empty($best_sellers) && count($best_sellers) >= 4):
?>
<!-- BEST SELLERS -->
<div class="ue-section">
  <div class="ue-container">
    <div class="ue-section-head">
      <div>
        <div class="ue-section-label">Customer Favorites</div>
        <h2 class="ue-section-title">Best Sellers</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="ue-section-link">View All</a>
    </div>
    <div class="ue-product-grid">
      <?php foreach(array_slice($best_sellers, 0, 4) as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $img = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
      ?>
      <div class="ue-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, 0)">
        <div class="ue-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
          <?php else: ?>
          <div class="ue-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="ue-product-body">
          <div class="ue-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="ue-product-footer">
            <div class="ue-product-price"><?= sf_currency($price, $cur); ?></div>
            <button class="ue-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php
      endif;
      break;

    // =====================================================
    // NEW ARRIVALS
    // =====================================================
    case 'new_arrivals':
      if(!empty($new_arrivals) && count($new_arrivals) >= 4):
?>
<!-- NEW ARRIVALS -->
<div class="ue-section" style="background:var(--ue-cream);">
  <div class="ue-container">
    <div class="ue-section-head">
      <div>
        <div class="ue-section-label">Just In</div>
        <h2 class="ue-section-title">New Arrivals</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="ue-section-link">View All</a>
    </div>
    <div class="ue-product-grid">
      <?php foreach(array_slice($new_arrivals, 0, 4) as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $img = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
      ?>
      <div class="ue-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, 0)">
        <div class="ue-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
          <?php else: ?>
          <div class="ue-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="ue-product-body">
          <div class="ue-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="ue-product-footer">
            <div class="ue-product-price"><?= sf_currency($price, $cur); ?></div>
            <button class="ue-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></button>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php
      endif;
      break;

    // =====================================================
    // BRANDS
    // =====================================================
    case 'brands':
      if(!empty($brands)):
?>
<!-- BRANDS -->
<div class="ue-section">
  <div class="ue-container">
    <div class="ue-section-head" style="justify-content:center;text-align:center;">
      <h2 class="ue-section-title">Our Brands</h2>
    </div>
    <div class="ue-brands-grid">
      <?php foreach($brands as $brand): ?>
      <div class="ue-brand"><?= htmlspecialchars($brand->brand_name ?? $brand->name ?? ''); ?></div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php
      endif;
      break;

    // =====================================================
    // TESTIMONIALS
    // =====================================================
    case 'testimonials':
      if(!empty($testimonials)):
?>
<!-- TESTIMONIALS -->
<div class="ue-section" style="background:var(--ue-cream);">
  <div class="ue-container">
    <div class="ue-section-head">
      <div>
        <div class="ue-section-label">Reviews</div>
        <h2 class="ue-section-title">What Customers Say</h2>
      </div>
    </div>
    <div class="ue-testimonials-grid">
      <?php foreach(array_slice($testimonials, 0, 3) as $t): ?>
      <div class="ue-testimonial">
        <div class="ue-testimonial-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
        <div class="ue-testimonial-text"><?= htmlspecialchars($t->testimonial_text ?? $t->message ?? ''); ?></div>
        <div class="ue-testimonial-author"><?= htmlspecialchars($t->customer_name ?? $t->author ?? ''); ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php
      endif;
      break;

    // =====================================================
    // INSTAGRAM GALLERY
    // =====================================================
    case 'instagram_gallery':
      if(!empty($instagram_posts)):
?>
<!-- INSTAGRAM -->
<div class="ue-section">
  <div class="ue-container">
    <div class="ue-section-head" style="justify-content:center;text-align:center;">
      <h2 class="ue-section-title">Follow Us</h2>
    </div>
    <div class="ue-insta-grid">
      <?php foreach(array_slice($instagram_posts, 0, 10) as $post): ?>
      <a href="<?= htmlspecialchars($post->permalink ?? $post->link ?? '#'); ?>" target="_blank" class="ue-insta-item">
        <img src="<?= htmlspecialchars($post->media_url ?? $post->image ?? ''); ?>" alt="Instagram post" loading="lazy">
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php
      endif;
      break;

    // =====================================================
    // STORE INFO
    // =====================================================
    case 'store_info':
?>
<!-- STORE INFO -->
<div class="ue-store-info">
  <div class="ue-container">
    <div class="ue-store-info-grid">
      <div>
        <div class="ue-section-label">About Us</div>
        <h2 class="ue-store-info-title"><?= htmlspecialchars($store->store_name ?? 'Our Store'); ?></h2>
        <p class="ue-store-info-text"><?= htmlspecialchars($settings->store_description ?? 'We are committed to bringing you the best products with exceptional service.'); ?></p>
        <?php if(!empty($settings->store_phone)): ?>
        <div class="ue-store-info-detail">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
          <?= htmlspecialchars($settings->store_phone); ?>
        </div>
        <?php endif; ?>
        <?php if(!empty($settings->store_email)): ?>
        <div class="ue-store-info-detail">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          <?= htmlspecialchars($settings->store_email); ?>
        </div>
        <?php endif; ?>
        <?php if(!empty($settings->store_address)): ?>
        <div class="ue-store-info-detail">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          <?= htmlspecialchars($settings->store_address); ?>
        </div>
        <?php endif; ?>
      </div>
      <div>
        <?php if($logo_url): ?>
        <img src="<?= $logo_url; ?>" alt="<?= htmlspecialchars($store->store_name ?? 'Store'); ?>" style="max-width:280px;width:100%;border-radius:0;">
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
<?php
      break;

    // =====================================================
    // FAQS
    // =====================================================
    case 'faqs':
      if(!empty($faqs)):
?>
<!-- FAQS -->
<div class="ue-section">
  <div class="ue-container">
    <div class="ue-section-head" style="justify-content:center;text-align:center;">
      <h2 class="ue-section-title">Frequently Asked Questions</h2>
    </div>
    <div class="ue-faq-list">
      <?php foreach($faqs as $faq): ?>
      <div class="ue-faq-item" onclick="this.classList.toggle('open')">
        <div class="ue-faq-q"><?= htmlspecialchars($faq->question ?? $faq->faq_question ?? ''); ?></div>
        <div class="ue-faq-a"><p><?= htmlspecialchars($faq->answer ?? $faq->faq_answer ?? ''); ?></p></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php
      endif;
      break;

    // =====================================================
    // CONTACT SECTION
    // =====================================================
    case 'contact_section':
?>
<!-- CONTACT -->
<div class="ue-section" style="background:var(--ue-cream);">
  <div class="ue-container">
    <div class="ue-section-head" style="justify-content:center;text-align:center;">
      <h2 class="ue-section-title">Get In Touch</h2>
    </div>
    <div class="ue-contact-grid">
      <?php if(!empty($settings->store_phone)): ?>
      <div class="ue-contact-card">
        <div class="ue-contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></div>
        <div class="ue-contact-label">Phone</div>
        <div class="ue-contact-value"><?= htmlspecialchars($settings->store_phone); ?></div>
      </div>
      <?php endif; ?>
      <?php if(!empty($settings->store_email)): ?>
      <div class="ue-contact-card">
        <div class="ue-contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
        <div class="ue-contact-label">Email</div>
        <div class="ue-contact-value"><?= htmlspecialchars($settings->store_email); ?></div>
      </div>
      <?php endif; ?>
      <?php if($waNumber): ?>
      <div class="ue-contact-card">
        <div class="ue-contact-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg></div>
        <div class="ue-contact-label">WhatsApp</div>
        <div class="ue-contact-value"><?= htmlspecialchars($settings->whatsapp_number); ?></div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php
      break;

    // =====================================================
    // WHATSAPP CTA
    // =====================================================
    case 'whatsapp_cta':
      if($waNumber):
?>
<!-- WHATSAPP CTA -->
<div class="ue-section">
  <div class="ue-container">
    <div class="ue-wa-cta">
      <div class="ue-wa-cta-title">Need Help? Chat With Us</div>
      <p class="ue-wa-cta-text">Have a question about a product or your order? Our team is ready to help you on WhatsApp.</p>
      <a href="https://wa.me/<?= $waNumber; ?>" target="_blank" class="ue-btn">Start WhatsApp Chat</a>
    </div>
  </div>
</div>
<?php
      endif;
      break;

    // =====================================================
    // NEWSLETTER
    // =====================================================
    case 'newsletter':
?>
<!-- NEWSLETTER -->
<div class="ue-section">
  <div class="ue-container">
    <div class="ue-newsletter">
      <div class="ue-newsletter-title">Stay In The Know</div>
      <p class="ue-newsletter-text">Subscribe to get updates on new arrivals, exclusive offers and more.</p>
      <form class="ue-newsletter-form" onsubmit="event.preventDefault();showToast('Thank you for subscribing!');this.reset();">
        <input type="email" placeholder="Enter your email" required>
        <button type="submit">Subscribe</button>
      </form>
    </div>
  </div>
</div>
<?php
      break;

    // =====================================================
    // STORE HOURS
    // =====================================================
    case 'store_hours':
      if(!empty($business_hours)):
?>
<!-- STORE HOURS -->
<div class="ue-section" style="background:var(--ue-cream);">
  <div class="ue-container">
    <div class="ue-section-head" style="justify-content:center;text-align:center;">
      <h2 class="ue-section-title">Opening Hours</h2>
    </div>
    <div class="ue-hours-grid">
      <?php foreach($business_hours as $day => $hours): ?>
      <div class="ue-hours-row">
        <span class="ue-hours-day"><?= htmlspecialchars(ucfirst($day)); ?></span>
        <span class="ue-hours-time"><?= htmlspecialchars($hours); ?></span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php
      endif;
      break;

  endswitch;
endforeach;
?>
