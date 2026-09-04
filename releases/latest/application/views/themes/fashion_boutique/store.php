<?php
/**
 * Boutique Luxe — Fashion Homepage
 * Serif-driven, elegant design with gold accents, ornamental dividers
 * and editorial sections for a refined storefront experience.
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
  :root {
    --bl-ink:#3D2817;
    --bl-gold:#C9A961;
    --bl-cream:#FBF7F0;
    --bl-soft:#F5EFE6;
  }

  .theme-fashion_boutique .mp-topbar,
  .theme-fashion_boutique .mp-announcement,
  .theme-fashion_boutique .mp-nav,
  .theme-fashion_boutique .mp-header,
  .theme-fashion_boutique .mp-mobile-menu-btn,
  .theme-fashion_boutique .mp-footer-space { display:none !important; }

  /* Boutique homepage styles */
  .bl-container { max-width:1400px; margin:0 auto; padding:0 24px; }
  @media(max-width:767px){ .bl-container { padding:0 16px; } }

  /* Hero */
  .bl-hero { position:relative; background:var(--bl-ink); color:#fff; overflow:hidden; min-height:600px; display:flex; align-items:center; }
  @media(max-width:767px){ .bl-hero { min-height:460px; } }
  .bl-hero-media { position:absolute; inset:0; opacity:0.5; }
  .bl-hero-media img { width:100%; height:100%; object-fit:cover; }
  .bl-hero-gradient { position:absolute; inset:0; background:linear-gradient(90deg, rgba(61,40,23,0.88) 0%, rgba(61,40,23,0.55) 55%, rgba(61,40,23,0.15) 100%); }
  .bl-hero-content { position:relative; z-index:2; padding:110px 24px 130px; max-width:1400px; margin:0 auto; width:100%; }
  @media(max-width:767px){ .bl-hero-content { padding:70px 16px 90px; } }
  .bl-hero-kicker { font-family:'Lora',serif; font-size:11px; text-transform:uppercase; letter-spacing:0.18em; color:var(--bl-gold); margin-bottom:20px; }
  .bl-hero-title { font-family:'Playfair Display',serif; font-style:italic; font-size:clamp(38px,5.5vw,66px); line-height:1.08; font-weight:700; margin:0 0 22px; max-width:700px; letter-spacing:-0.01em; }
  .bl-hero-lead { font-family:'Lora',serif; font-size:clamp(15px,2vw,18px); line-height:1.7; opacity:0.92; max-width:500px; margin-bottom:36px; }
  .bl-hero-actions { display:flex; gap:14px; flex-wrap:wrap; }

  /* Buttons */
  .bl-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:15px 34px; border-radius:2px; font-family:'Lora',serif; font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; transition:transform .15s, background .2s, color .2s; cursor:pointer; border:none; text-decoration:none; }
  .bl-btn:active { transform:scale(0.98); }
  .bl-btn-gold { background:var(--bl-gold); color:var(--bl-ink); }
  .bl-btn-gold:hover { background:#B8974F; }
  .bl-btn-ink { background:var(--bl-ink); color:#fff; }
  .bl-btn-ink:hover { background:#2D1B0F; }
  .bl-btn-ghost { background:transparent; color:#fff; border:1px solid rgba(255,255,255,0.4); }
  .bl-btn-ghost:hover { background:rgba(255,255,255,0.1); border-color:#fff; }
  .bl-btn-outline { background:#fff; color:var(--bl-ink); border:1px solid var(--bl-ink); }
  .bl-btn-outline:hover { background:var(--bl-ink); color:#fff; }
  .bl-btn-wa { background:#25D366; color:#fff; }
  .bl-btn-wa:hover { background:#1DA851; }

  /* Sections */
  .bl-section { padding:72px 0; }
  @media(max-width:767px){ .bl-section { padding:44px 0; } }
  .bl-section-head { display:flex; align-items:flex-end; justify-content:space-between; gap:16px; margin-bottom:36px; }
  .bl-section-label { font-family:'Lora',serif; font-size:11px; text-transform:uppercase; letter-spacing:0.18em; color:var(--bl-gold); font-weight:600; margin-bottom:8px; }
  .bl-section-title { font-family:'Playfair Display',serif; font-style:italic; font-size:32px; margin:0; font-weight:700; color:var(--bl-ink); }
  .bl-section-link { font-family:'Lora',serif; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; border-bottom:1px solid var(--bl-ink); padding-bottom:2px; transition:color .2s, border-color .2s; color:var(--bl-ink); text-decoration:none; }
  .bl-section-link:hover { color:var(--bl-gold); border-color:var(--bl-gold); }

  /* Product grid */
  .bl-product-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:24px; }
  @media(max-width:1023px){ .bl-product-grid { grid-template-columns:repeat(3,1fr); } }
  @media(max-width:767px){ .bl-product-grid { grid-template-columns:repeat(2,1fr); gap:14px; } }
  .bl-product-card { position:relative; background:#fff; border-radius:4px; overflow:hidden; border:1px solid var(--bl-soft); transition:transform .25s, box-shadow .25s; cursor:pointer; }
  .bl-product-card:hover { transform:translateY(-4px); box-shadow:0 12px 36px rgba(61,40,23,0.12); }
  .bl-product-wishlist { position:absolute; top:14px; right:14px; width:36px; height:36px; border-radius:4px; background:#fff; display:flex; align-items:center; justify-content:center; z-index:2; transition:color .2s, transform .2s; box-shadow:0 2px 8px rgba(0,0,0,0.06); border:none; cursor:pointer; }
  .bl-product-wishlist:hover { color:var(--bl-gold); transform:scale(1.08); }
  .bl-product-badge { position:absolute; top:14px; left:14px; background:var(--bl-gold); color:var(--bl-ink); font-family:'Lora',serif; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; padding:5px 10px; border-radius:2px; z-index:2; }
  .bl-product-media { aspect-ratio:4/5; overflow:hidden; background:var(--bl-soft); }
  .bl-product-media img { width:100%; height:100%; object-fit:cover; transition:transform .5s ease; }
  .bl-product-card:hover .bl-product-media img { transform:scale(1.06); }
  .bl-product-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--bl-soft); color:var(--bl-gold); }
  .bl-product-placeholder span { font-family:'Playfair Display',serif; font-style:italic; font-size:38px; font-weight:700; }
  .bl-product-body { padding:20px; }
  .bl-product-brand { font-family:'Lora',serif; font-size:11px; text-transform:uppercase; letter-spacing:0.1em; color:#9B8B7A; font-weight:600; margin-bottom:5px; }
  .bl-product-name { font-family:'Lora',serif; font-size:14px; font-weight:600; margin-bottom:12px; line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; min-height:40px; color:var(--bl-ink); }
  .bl-product-footer { display:flex; align-items:center; justify-content:space-between; gap:10px; }
  .bl-product-price { font-family:'Playfair Display',serif; font-size:18px; font-weight:700; color:var(--bl-ink); }
  .bl-product-price .old { font-family:'Lora',serif; font-size:13px; color:#9B8B7A; text-decoration:line-through; margin-left:6px; font-weight:500; }
  .bl-add-btn { width:40px; height:40px; border-radius:2px; background:var(--bl-ink); color:#fff; display:flex; align-items:center; justify-content:center; transition:background .2s, transform .15s; border:none; cursor:pointer; flex-shrink:0; }
  .bl-add-btn:hover { background:var(--bl-gold); color:var(--bl-ink); }
  .bl-add-btn:active { transform:scale(0.95); }
  .bl-product-stock { font-family:'Lora',serif; font-size:11px; color:#B23A3A; font-weight:600; margin-top:8px; text-transform:uppercase; letter-spacing:0.06em; }

  /* Category cards — stylish elegant */
  .bl-cat-grid { display:grid; grid-template-columns:repeat(6,1fr); gap:16px; }
  @media(max-width:1023px){ .bl-cat-grid { grid-template-columns:repeat(4,1fr); } }
  @media(max-width:767px){ .bl-cat-grid { grid-template-columns:repeat(3,1fr); gap:12px; } }
  .bl-cat-card { position:relative; border-radius:4px; overflow:hidden; aspect-ratio:3/2; text-decoration:none; color:inherit; display:block; transition:transform .25s, box-shadow .25s; }
  .bl-cat-card:hover { transform:translateY(-4px); box-shadow:0 12px 36px rgba(61,40,23,0.15); }
  .bl-cat-card-media { position:absolute; inset:0; background:var(--bl-soft); }
  .bl-cat-card-media img { width:100%; height:100%; object-fit:cover; transition:transform .5s ease; }
  .bl-cat-card:hover .bl-cat-card-media img { transform:scale(1.08); }
  .bl-cat-card-overlay { position:absolute; inset:0; background:linear-gradient(180deg, transparent 40%, rgba(61,40,23,0.8) 100%); }
  .bl-cat-card-body { position:absolute; bottom:0; left:0; right:0; padding:20px; z-index:2; }
  .bl-cat-card-name { font-family:'Playfair Display',serif; font-style:italic; font-size:16px; font-weight:700; color:#fff; margin-bottom:4px; }
  .bl-cat-card-count { font-family:'Lora',serif; font-size:12px; color:var(--bl-gold); }
  .bl-cat-card-arrow { position:absolute; top:16px; right:16px; width:32px; height:32px; border-radius:2px; background:var(--bl-gold); display:flex; align-items:center; justify-content:center; opacity:0; transform:translateY(-4px); transition:opacity .25s, transform .25s; }
  .bl-cat-card:hover .bl-cat-card-arrow { opacity:1; transform:translateY(0); }
  .bl-cat-card-arrow svg { width:16px; height:16px; color:var(--bl-ink); }
  .bl-cat-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--bl-soft); }
  .bl-cat-placeholder span { font-family:'Playfair Display',serif; font-style:italic; font-size:42px; font-weight:700; color:var(--bl-gold); }

  /* Trust badges / values */
  .bl-values { background:var(--bl-soft); padding:48px 0; }
  .bl-values-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:24px; }
  @media(max-width:767px){ .bl-values-grid { grid-template-columns:1fr; } }
  .bl-value { text-align:center; padding:16px; }
  .bl-value-icon { width:56px; height:56px; border-radius:50%; background:rgba(201,169,97,0.12); display:flex; align-items:center; justify-content:center; font-size:24px; margin:0 auto 16px; color:var(--bl-gold); }
  .bl-value-title { font-family:'Playfair Display',serif; font-style:italic; font-size:16px; font-weight:700; color:var(--bl-ink); margin-bottom:8px; }
  .bl-value-text { font-family:'Lora',serif; font-size:14px; color:#6B5B4A; line-height:1.6; }

  /* Promo banner */
  .bl-promo { background:var(--bl-cream); border-radius:4px; overflow:hidden; display:flex; align-items:center; min-height:200px; border:1px solid var(--bl-soft); }
  @media(max-width:767px){ .bl-promo { flex-direction:column; min-height:auto; } }
  .bl-promo-media { flex:1; min-height:200px; background:var(--bl-soft); }
  .bl-promo-media img { width:100%; height:100%; object-fit:cover; }
  .bl-promo-body { flex:1; padding:40px; }
  @media(max-width:767px){ .bl-promo-body { padding:24px; } }
  .bl-promo-kicker { font-family:'Lora',serif; font-size:11px; text-transform:uppercase; letter-spacing:0.18em; color:var(--bl-gold); font-weight:600; margin-bottom:8px; }
  .bl-promo-title { font-family:'Playfair Display',serif; font-style:italic; font-size:clamp(22px,3vw,32px); font-weight:700; color:var(--bl-ink); margin-bottom:10px; }
  .bl-promo-text { font-family:'Lora',serif; font-size:15px; color:#6B5B4A; line-height:1.7; margin-bottom:20px; }

  /* WhatsApp CTA */
  .bl-wa-cta { background:linear-gradient(135deg, #25D366, #1DA851); color:#fff; padding:48px 24px; text-align:center; border-radius:4px; }
  .bl-wa-cta-title { font-family:'Playfair Display',serif; font-style:italic; font-size:clamp(22px,3vw,30px); font-weight:700; margin-bottom:10px; }
  .bl-wa-cta-text { font-family:'Lora',serif; opacity:0.9; max-width:440px; margin:0 auto 24px; font-size:15px; line-height:1.7; }
  .bl-wa-cta .bl-btn { background:#fff; color:#1DA851; }
  .bl-wa-cta .bl-btn:hover { background:var(--bl-ink); color:#fff; }

  /* Store info */
  .bl-store-info { background:var(--bl-cream); padding:48px 0; }
  .bl-store-info-grid { display:grid; grid-template-columns:1fr 1fr; gap:32px; align-items:center; }
  @media(max-width:767px){ .bl-store-info-grid { grid-template-columns:1fr; } }
  .bl-store-info-title { font-family:'Playfair Display',serif; font-style:italic; font-size:clamp(24px,3vw,34px); font-weight:700; color:var(--bl-ink); margin-bottom:14px; }
  .bl-store-info-text { font-family:'Lora',serif; font-size:15px; color:#6B5B4A; line-height:1.7; margin-bottom:20px; }
  .bl-store-info-detail { display:flex; align-items:center; gap:10px; margin-bottom:10px; font-family:'Lora',serif; font-size:14px; color:var(--bl-ink); }
  .bl-store-info-detail svg { width:18px; height:18px; color:var(--bl-gold); flex-shrink:0; }

  /* Newsletter */
  .bl-newsletter { background:var(--bl-ink); color:#fff; padding:48px 24px; text-align:center; border-radius:4px; }
  .bl-newsletter-title { font-family:'Playfair Display',serif; font-style:italic; font-size:clamp(22px,3vw,30px); font-weight:700; margin-bottom:10px; }
  .bl-newsletter-text { font-family:'Lora',serif; opacity:0.8; max-width:440px; margin:0 auto 24px; font-size:15px; }
  .bl-newsletter-form { display:flex; gap:10px; max-width:440px; margin:0 auto; }
  .bl-newsletter-form input { flex:1; padding:14px 18px; border:none; border-radius:2px; font-family:'Lora',serif; font-size:14px; outline:none; }
  .bl-newsletter-form button { padding:14px 24px; border:none; border-radius:2px; background:var(--bl-gold); color:var(--bl-ink); font-family:'Lora',serif; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; cursor:pointer; font-size:13px; }

  /* Testimonials */
  .bl-testimonials-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; }
  @media(max-width:767px){ .bl-testimonials-grid { grid-template-columns:1fr; } }
  .bl-testimonial { background:#fff; border:1px solid var(--bl-soft); border-radius:4px; padding:24px; }
  .bl-testimonial-stars { color:var(--bl-gold); margin-bottom:12px; font-size:16px; }
  .bl-testimonial-text { font-family:'Lora',serif; font-size:14px; color:#6B5B4A; line-height:1.7; margin-bottom:16px; }
  .bl-testimonial-author { font-family:'Playfair Display',serif; font-style:italic; font-size:13px; font-weight:700; color:var(--bl-ink); }

  /* Brands */
  .bl-brands-grid { display:flex; flex-wrap:wrap; gap:20px; align-items:center; justify-content:center; }
  .bl-brand { padding:12px 24px; background:#fff; border:1px solid var(--bl-soft); border-radius:2px; font-family:'Lora',serif; font-size:14px; font-weight:600; color:var(--bl-ink); }

  /* Instagram */
  .bl-insta-grid { display:grid; grid-template-columns:repeat(5,1fr); gap:12px; }
  @media(max-width:767px){ .bl-insta-grid { grid-template-columns:repeat(3,1fr); } }
  .bl-insta-item { aspect-ratio:1; border-radius:4px; overflow:hidden; position:relative; }
  .bl-insta-item img { width:100%; height:100%; object-fit:cover; transition:transform .3s; }
  .bl-insta-item:hover img { transform:scale(1.08); }

  /* FAQs */
  .bl-faq-list { max-width:760px; margin:0 auto; }
  .bl-faq-item { background:#fff; border:1px solid var(--bl-soft); border-radius:4px; margin-bottom:10px; overflow:hidden; }
  .bl-faq-q { padding:18px 24px; font-family:'Lora',serif; font-size:15px; font-weight:600; color:var(--bl-ink); cursor:pointer; display:flex; justify-content:space-between; align-items:center; }
  .bl-faq-q::after { content:'+'; font-size:20px; color:var(--bl-gold); }
  .bl-faq-item.open .bl-faq-q::after { content:'\2212'; }
  .bl-faq-a { max-height:0; overflow:hidden; transition:max-height .3s ease; padding:0 24px; }
  .bl-faq-item.open .bl-faq-a { max-height:300px; padding:0 24px 18px; }
  .bl-faq-a p { font-family:'Lora',serif; font-size:14px; color:#6B5B4A; line-height:1.7; margin:0; }

  /* Contact */
  .bl-contact-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; }
  @media(max-width:767px){ .bl-contact-grid { grid-template-columns:1fr; } }
  .bl-contact-card { background:#fff; border:1px solid var(--bl-soft); border-radius:4px; padding:24px; text-align:center; }
  .bl-contact-icon { width:48px; height:48px; border-radius:50%; background:rgba(201,169,97,0.12); display:flex; align-items:center; justify-content:center; margin:0 auto 14px; color:var(--bl-gold); }
  .bl-contact-icon svg { width:22px; height:22px; }
  .bl-contact-label { font-family:'Lora',serif; font-size:11px; text-transform:uppercase; letter-spacing:0.12em; color:var(--bl-gold); font-weight:600; margin-bottom:4px; }
  .bl-contact-value { font-family:'Lora',serif; font-size:15px; font-weight:600; color:var(--bl-ink); }

  /* Store hours */
  .bl-hours-grid { max-width:520px; margin:0 auto; }
  .bl-hours-row { display:flex; justify-content:space-between; padding:12px 0; border-bottom:1px solid var(--bl-soft); font-family:'Lora',serif; font-size:14px; }
  .bl-hours-row:last-child { border-bottom:none; }
  .bl-hours-day { color:var(--bl-ink); font-weight:600; }
  .bl-hours-time { color:#6B5B4A; }
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
<div class="bl-hero">
  <div class="bl-hero-media"><img src="<?= $heroImg; ?>" alt="<?= htmlspecialchars($hero->banner_title ?? ($store->store_name ?? 'Store')); ?>"></div>
  <div class="bl-hero-gradient"></div>
  <div class="bl-hero-content">
    <p class="bl-hero-kicker"><?= htmlspecialchars($hero->banner_subtitle ?? 'New Collection'); ?></p>
    <h1 class="bl-hero-title"><?= htmlspecialchars($hero->banner_title ?? ($settings->store_headline ?: ($store->store_name ?? 'Welcome'))); ?></h1>
    <p class="bl-hero-lead"><?= htmlspecialchars($settings->store_subheadline ?: 'Curated pieces for the discerning wardrobe. Fine fabrics, timeless silhouettes, and statement essentials.'); ?></p>
    <div class="bl-hero-actions">
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="bl-btn bl-btn-gold">Shop the Collection</a>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="bl-btn bl-btn-ghost">View Lookbook</a>
    </div>
  </div>
</div>
<?php else: ?>
<div class="bl-hero">
  <div class="bl-hero-gradient"></div>
  <div class="bl-hero-content">
    <p class="bl-hero-kicker"><?= htmlspecialchars($settings->store_subheadline ?: 'Welcome'); ?></p>
    <h1 class="bl-hero-title"><?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Store')); ?></h1>
    <p class="bl-hero-lead"><?= htmlspecialchars($settings->store_description ?: 'Discover our curated collection of refined pieces.'); ?></p>
    <div class="bl-hero-actions">
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="bl-btn bl-btn-gold">Shop Now</a>
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
      $blBadges = json_decode($settings->trust_badges_json ?? '', true);
      if(empty($blBadges) || !is_array($blBadges)){
        $blBadges = [
          ['icon' => '&#10024;', 'title' => 'Premium Quality', 'desc' => 'Carefully selected fabrics and finishes for lasting comfort.'],
          ['icon' => '&#128230;', 'title' => 'Fast Delivery', 'desc' => 'Quick and reliable shipping to your doorstep.'],
          ['icon' => '&#129309;', 'title' => 'Personal Service', 'desc' => 'Reach us anytime on WhatsApp for styling help and orders.']
        ];
      }
?>
<!-- TRUST BADGES -->
<div class="bl-values">
  <div class="bl-container">
    <div class="bl-values-grid">
      <?php foreach(array_slice($blBadges, 0, 3) as $b): ?>
      <div class="bl-value">
        <div class="bl-value-icon"><?= !empty($b['icon']) ? $b['icon'] : '&#10024;'; ?></div>
        <div class="bl-value-title"><?= htmlspecialchars($b['title'] ?? ''); ?></div>
        <div class="bl-value-text"><?= htmlspecialchars($b['desc'] ?? ''); ?></div>
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
<div class="bl-section">
  <div class="bl-container">
    <div class="bl-promo">
      <?php if($promoImg): ?>
      <div class="bl-promo-media"><img src="<?= $promoImg; ?>" alt="<?= htmlspecialchars($promo->banner_title ?? 'Promo'); ?>"></div>
      <?php endif; ?>
      <div class="bl-promo-body">
        <div class="bl-promo-kicker"><?= htmlspecialchars($promo->banner_subtitle ?? 'Limited Time'); ?></div>
        <h3 class="bl-promo-title"><?= htmlspecialchars($promo->banner_title ?? 'Special Offer'); ?></h3>
        <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="bl-btn bl-btn-ink">Shop Now</a>
      </div>
    </div>
  </div>
</div>
<?php
      endif;
      break;

    // =====================================================
    // FEATURED CATEGORIES — stylish elegant cards
    // =====================================================
    case 'featured_categories':
      if(!empty($categories) && count($categories) > 1):
?>
<!-- CATEGORIES -->
<div class="bl-section" style="background:var(--bl-cream);">
  <div class="bl-container">
    <div class="bl-section-head">
      <div>
        <div class="bl-section-label">Browse</div>
        <h2 class="bl-section-title">Shop by Category</h2>
      </div>
    </div>
    <div class="bl-cat-grid">
      <?php foreach(array_slice($categories, 0, 8) as $cat):
        $catImg = (!empty($cat->category_image) && file_exists($cat->category_image)) ? base_url($cat->category_image) : '';
        $itemCount = $cat->item_count ?? 0;
      ?>
      <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="bl-cat-card">
        <div class="bl-cat-card-media">
          <?php if($catImg): ?>
          <img src="<?= $catImg; ?>" alt="<?= htmlspecialchars($cat->category_name); ?>" loading="lazy">
          <?php else: ?>
          <div class="bl-cat-placeholder"><span><?= htmlspecialchars(substr($cat->category_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="bl-cat-card-overlay"></div>
        <div class="bl-cat-card-arrow">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </div>
        <div class="bl-cat-card-body">
          <div class="bl-cat-card-name"><?= htmlspecialchars($cat->category_name); ?></div>
          <?php if($itemCount > 0): ?>
          <div class="bl-cat-card-count"><?= $itemCount; ?> items</div>
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
<div class="bl-section">
  <div class="bl-container">
    <div class="bl-section-head">
      <div>
        <div class="bl-section-label">Trending Now</div>
        <h2 class="bl-section-title">Featured Collection</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="bl-section-link">Shop All</a>
    </div>
    <div class="bl-product-grid">
      <?php foreach(array_slice($featured_products, 0, 8) as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $oldPrice = $p->original_price ?? $p->sales_price;
        $hasDiscount = $oldPrice > $price;
        $discountPct = $hasDiscount ? round((($oldPrice - $price) / $oldPrice) * 100) : 0;
        $img = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
      ?>
      <div class="bl-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, <?= $hasDiscount ? $oldPrice : 0; ?>)">
        <?php if($hasDiscount && $discountPct > 0): ?>
        <span class="bl-product-badge">-<?= $discountPct; ?>%</span>
        <?php endif; ?>
        <button class="bl-product-wishlist" onclick="event.stopPropagation();"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></button>
        <div class="bl-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
          <?php else: ?>
          <div class="bl-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="bl-product-body">
          <?php if(!empty($p->category_name)): ?>
          <div class="bl-product-brand"><?= htmlspecialchars($p->category_name); ?></div>
          <?php endif; ?>
          <div class="bl-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="bl-product-footer">
            <div class="bl-product-price"><?= sf_currency($price, $cur); ?><?php if($hasDiscount): ?><span class="old"><?= sf_currency($oldPrice, $cur); ?></span><?php endif; ?></div>
            <button class="bl-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add to cart"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></button>
          </div>
          <?php if($p->stock <= 0 && !($settings->allow_backorder ?? false)): ?>
          <div class="bl-product-stock">Out of Stock</div>
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
<div class="bl-section" style="background:var(--bl-cream);">
  <div class="bl-container">
    <div class="bl-section-head">
      <div>
        <div class="bl-section-label">Services</div>
        <h2 class="bl-section-title">Our Services</h2>
      </div>
    </div>
    <div class="bl-product-grid">
      <?php foreach(array_slice($featured_services, 0, 4) as $s):
        $sPrice = $s->effective_price ?? $s->sales_price ?? $s->price ?? 0;
        $sImg = (!empty($s->item_image) && file_exists($s->item_image)) ? base_url($s->item_image) : (!empty($s->service_image) && file_exists($s->service_image) ? base_url($s->service_image) : '');
      ?>
      <div class="bl-product-card" onclick="openProductModal(<?= $s->id; ?>, '<?= htmlspecialchars(addslashes($s->item_name ?? $s->service_name ?? '')); ?>', <?= $sPrice; ?>, '<?= $s->item_image ?? $s->service_image ?? ''; ?>', '<?= htmlspecialchars(addslashes($s->description ?? '')); ?>', 999, 0)">
        <div class="bl-product-media">
          <?php if($sImg): ?>
          <img src="<?= $sImg; ?>" alt="<?= htmlspecialchars($s->item_name ?? $s->service_name ?? ''); ?>" loading="lazy">
          <?php else: ?>
          <div class="bl-product-placeholder"><span><?= htmlspecialchars(substr($s->item_name ?? $s->service_name ?? 'S', 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="bl-product-body">
          <div class="bl-product-name"><?= htmlspecialchars($s->item_name ?? $s->service_name ?? ''); ?></div>
          <div class="bl-product-footer">
            <div class="bl-product-price"><?= sf_currency($sPrice, $cur); ?></div>
            <button class="bl-add-btn" onclick="event.stopPropagation();addToCart(<?= $s->id; ?>,'service','<?= htmlspecialchars(addslashes($s->item_name ?? $s->service_name ?? '')); ?>',<?= $sPrice; ?>,'<?= $s->item_image ?? $s->service_image ?? ''; ?>',1,999)" aria-label="Add to cart"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></button>
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
<div class="bl-section">
  <div class="bl-container">
    <div class="bl-section-head">
      <div>
        <div class="bl-section-label">Customer Favorites</div>
        <h2 class="bl-section-title">Best Sellers</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="bl-section-link">View All</a>
    </div>
    <div class="bl-product-grid">
      <?php foreach(array_slice($best_sellers, 0, 4) as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $img = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
      ?>
      <div class="bl-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, 0)">
        <div class="bl-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
          <?php else: ?>
          <div class="bl-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="bl-product-body">
          <div class="bl-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="bl-product-footer">
            <div class="bl-product-price"><?= sf_currency($price, $cur); ?></div>
            <button class="bl-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add to cart"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></button>
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
<div class="bl-section" style="background:var(--bl-cream);">
  <div class="bl-container">
    <div class="bl-section-head">
      <div>
        <div class="bl-section-label">Just In</div>
        <h2 class="bl-section-title">New Arrivals</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="bl-section-link">View All</a>
    </div>
    <div class="bl-product-grid">
      <?php foreach(array_slice($new_arrivals, 0, 4) as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $img = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
      ?>
      <div class="bl-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, 0)">
        <div class="bl-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
          <?php else: ?>
          <div class="bl-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="bl-product-body">
          <div class="bl-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="bl-product-footer">
            <div class="bl-product-price"><?= sf_currency($price, $cur); ?></div>
            <button class="bl-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add to cart"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></button>
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
<div class="bl-section">
  <div class="bl-container">
    <div class="bl-section-head" style="justify-content:center;text-align:center;">
      <h2 class="bl-section-title">Our Brands</h2>
    </div>
    <div class="bl-brands-grid">
      <?php foreach($brands as $brand): ?>
      <div class="bl-brand"><?= htmlspecialchars($brand->brand_name ?? $brand->name ?? ''); ?></div>
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
<div class="bl-section" style="background:var(--bl-cream);">
  <div class="bl-container">
    <div class="bl-section-head">
      <div>
        <div class="bl-section-label">Reviews</div>
        <h2 class="bl-section-title">What Customers Say</h2>
      </div>
    </div>
    <div class="bl-testimonials-grid">
      <?php foreach(array_slice($testimonials, 0, 3) as $t): ?>
      <div class="bl-testimonial">
        <div class="bl-testimonial-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
        <div class="bl-testimonial-text"><?= htmlspecialchars($t->testimonial_text ?? $t->message ?? ''); ?></div>
        <div class="bl-testimonial-author"><?= htmlspecialchars($t->customer_name ?? $t->author ?? ''); ?></div>
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
<div class="bl-section">
  <div class="bl-container">
    <div class="bl-section-head" style="justify-content:center;text-align:center;">
      <h2 class="bl-section-title">Follow Us</h2>
    </div>
    <div class="bl-insta-grid">
      <?php foreach(array_slice($instagram_posts, 0, 10) as $post): ?>
      <a href="<?= htmlspecialchars($post->permalink ?? $post->link ?? '#'); ?>" target="_blank" class="bl-insta-item">
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
<div class="bl-store-info">
  <div class="bl-container">
    <div class="bl-store-info-grid">
      <div>
        <div class="bl-section-label">About Us</div>
        <h2 class="bl-store-info-title"><?= htmlspecialchars($store->store_name ?? 'Our Store'); ?></h2>
        <p class="bl-store-info-text"><?= htmlspecialchars($settings->store_description ?? 'We are committed to bringing you the best products with exceptional service.'); ?></p>
        <?php if(!empty($settings->store_phone)): ?>
        <div class="bl-store-info-detail">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
          <?= htmlspecialchars($settings->store_phone); ?>
        </div>
        <?php endif; ?>
        <?php if(!empty($settings->store_email)): ?>
        <div class="bl-store-info-detail">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          <?= htmlspecialchars($settings->store_email); ?>
        </div>
        <?php endif; ?>
        <?php if(!empty($settings->store_address)): ?>
        <div class="bl-store-info-detail">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          <?= htmlspecialchars($settings->store_address); ?>
        </div>
        <?php endif; ?>
      </div>
      <div>
        <?php if($logo_url): ?>
        <img src="<?= $logo_url; ?>" alt="<?= htmlspecialchars($store->store_name ?? 'Store'); ?>" style="max-width:280px;width:100%;border-radius:4px;">
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
<div class="bl-section">
  <div class="bl-container">
    <div class="bl-section-head" style="justify-content:center;text-align:center;">
      <h2 class="bl-section-title">Frequently Asked Questions</h2>
    </div>
    <div class="bl-faq-list">
      <?php foreach($faqs as $faq): ?>
      <div class="bl-faq-item" onclick="this.classList.toggle('open')">
        <div class="bl-faq-q"><?= htmlspecialchars($faq->question ?? $faq->faq_question ?? ''); ?></div>
        <div class="bl-faq-a"><p><?= htmlspecialchars($faq->answer ?? $faq->faq_answer ?? ''); ?></p></div>
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
<div class="bl-section" style="background:var(--bl-cream);">
  <div class="bl-container">
    <div class="bl-section-head" style="justify-content:center;text-align:center;">
      <h2 class="bl-section-title">Get In Touch</h2>
    </div>
    <div class="bl-contact-grid">
      <?php if(!empty($settings->store_phone)): ?>
      <div class="bl-contact-card">
        <div class="bl-contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></div>
        <div class="bl-contact-label">Phone</div>
        <div class="bl-contact-value"><?= htmlspecialchars($settings->store_phone); ?></div>
      </div>
      <?php endif; ?>
      <?php if(!empty($settings->store_email)): ?>
      <div class="bl-contact-card">
        <div class="bl-contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
        <div class="bl-contact-label">Email</div>
        <div class="bl-contact-value"><?= htmlspecialchars($settings->store_email); ?></div>
      </div>
      <?php endif; ?>
      <?php if($waNumber): ?>
      <div class="bl-contact-card">
        <div class="bl-contact-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg></div>
        <div class="bl-contact-label">WhatsApp</div>
        <div class="bl-contact-value"><?= htmlspecialchars($settings->whatsapp_number); ?></div>
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
<div class="bl-section">
  <div class="bl-container">
    <div class="bl-wa-cta">
      <div class="bl-wa-cta-title">Need Help? Chat With Us</div>
      <p class="bl-wa-cta-text">Have a question about a product or your order? Our team is ready to help you on WhatsApp.</p>
      <a href="https://wa.me/<?= $waNumber; ?>" target="_blank" class="bl-btn">Start WhatsApp Chat</a>
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
<div class="bl-section">
  <div class="bl-container">
    <div class="bl-newsletter">
      <div class="bl-newsletter-title">Stay In The Know</div>
      <p class="bl-newsletter-text">Subscribe to get updates on new arrivals, exclusive offers and more.</p>
      <form class="bl-newsletter-form" onsubmit="event.preventDefault();showToast('Thank you for subscribing!');this.reset();">
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
<div class="bl-section" style="background:var(--bl-cream);">
  <div class="bl-container">
    <div class="bl-section-head" style="justify-content:center;text-align:center;">
      <h2 class="bl-section-title">Opening Hours</h2>
    </div>
    <div class="bl-hours-grid">
      <?php foreach($business_hours as $day => $hours): ?>
      <div class="bl-hours-row">
        <span class="bl-hours-day"><?= htmlspecialchars(ucfirst($day)); ?></span>
        <span class="bl-hours-time"><?= htmlspecialchars($hours); ?></span>
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
