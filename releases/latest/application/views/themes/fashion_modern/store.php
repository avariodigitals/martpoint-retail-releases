<?php
/**
 * Modern Minimal — Fashion Homepage
 * Shopify-style design with full-bleed hero, product grids,
 * editorial sections and premium spacing.
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
  :root { --fm-ink:#0F172A; --fm-primary:#4F46E5; --fm-cream:#F8FAFC; --fm-soft:#F1F5F9; --fm-border:#E2E8F0; }

  .theme-fashion_modern .mp-topbar,
  .theme-fashion_modern .mp-announcement,
  .theme-fashion_modern .mp-nav,
  .theme-fashion_modern .mp-header,
  .theme-fashion_modern .mp-mobile-menu-btn,
  .theme-fashion_modern .mp-footer-space { display:none !important; }

  .fm-container { max-width:1400px; margin:0 auto; padding:0 24px; }
  @media(max-width:767px){ .fm-container { padding:0 16px; } }

  /* Hero */
  .fm-hero { position:relative; background:var(--fm-ink); color:#fff; overflow:hidden; min-height:560px; display:flex; align-items:center; }
  @media(max-width:767px){ .fm-hero { min-height:420px; } }
  .fm-hero-media { position:absolute; inset:0; opacity:0.55; }
  .fm-hero-media img { width:100%; height:100%; object-fit:cover; }
  .fm-hero-gradient { position:absolute; inset:0; background:linear-gradient(90deg, rgba(15,23,42,0.82) 0%, rgba(15,23,42,0.4) 55%, rgba(15,23,42,0.1) 100%); }
  .fm-hero-content { position:relative; z-index:2; padding:100px 24px 120px; max-width:1400px; margin:0 auto; width:100%; }
  @media(max-width:767px){ .fm-hero-content { padding:60px 16px 80px; } }
  .fm-hero-kicker { font-size:12px; text-transform:uppercase; letter-spacing:0.16em; color:#C7D2FE; margin-bottom:16px; font-weight:600; }
  .fm-hero-title { font-family:'Playfair Display',serif; font-size:clamp(36px,5.5vw,64px); line-height:1.05; font-weight:700; margin:0 0 18px; max-width:680px; letter-spacing:-0.02em; }
  .fm-hero-lead { font-size:clamp(15px,2vw,18px); line-height:1.6; opacity:0.9; max-width:480px; margin-bottom:32px; }
  .fm-hero-actions { display:flex; gap:14px; flex-wrap:wrap; }

  /* Buttons */
  .fm-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:15px 32px; border-radius:999px; font-size:14px; font-weight:600; transition:transform .15s, background .2s; cursor:pointer; border:none; text-decoration:none; }
  .fm-btn:active { transform:scale(0.98); }
  .fm-btn-primary { background:var(--fm-primary); color:#fff; }
  .fm-btn-primary:hover { background:#4338CA; }
  .fm-btn-ghost { background:rgba(255,255,255,0.12); color:#fff; border:1px solid rgba(255,255,255,0.3); backdrop-filter:blur(4px); }
  .fm-btn-ghost:hover { background:rgba(255,255,255,0.2); }
  .fm-btn-dark { background:var(--fm-ink); color:#fff; }
  .fm-btn-dark:hover { background:#1E293B; }
  .fm-btn-outline { background:#fff; color:var(--fm-ink); border:1.5px solid var(--fm-ink); }
  .fm-btn-outline:hover { background:var(--fm-ink); color:#fff; }
  .fm-btn-wa { background:#25D366; color:#fff; }
  .fm-btn-wa:hover { background:#1DA851; }

  /* Sections */
  .fm-section { padding:64px 0; }
  @media(max-width:767px){ .fm-section { padding:40px 0; } }
  .fm-section-head { display:flex; align-items:flex-end; justify-content:space-between; gap:16px; margin-bottom:32px; }
  .fm-section-label { font-size:12px; text-transform:uppercase; letter-spacing:0.12em; color:var(--fm-primary); font-weight:700; margin-bottom:6px; }
  .fm-section-title { font-family:'Playfair Display',serif; font-size:clamp(28px,3.5vw,40px); margin:0; font-weight:700; letter-spacing:-0.02em; color:var(--fm-ink); }
  .fm-section-link { font-size:13px; font-weight:600; border-bottom:1.5px solid var(--fm-ink); padding-bottom:2px; transition:color .2s; color:var(--fm-ink); text-decoration:none; }
  .fm-section-link:hover { color:var(--fm-primary); border-color:var(--fm-primary); }

  /* Product grid */
  .fm-product-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:24px; }
  @media(max-width:1023px){ .fm-product-grid { grid-template-columns:repeat(3,1fr); } }
  @media(max-width:767px){ .fm-product-grid { grid-template-columns:repeat(2,1fr); gap:14px; } }
  .fm-product-card { position:relative; background:#fff; border-radius:16px; overflow:hidden; border:1px solid var(--fm-border); transition:transform .2s, box-shadow .2s; cursor:pointer; }
  .fm-product-card:hover { transform:translateY(-4px); box-shadow:0 16px 40px rgba(15,23,42,0.1); }
  .fm-product-wishlist { position:absolute; top:14px; right:14px; width:36px; height:36px; border-radius:50%; background:#fff; display:flex; align-items:center; justify-content:center; z-index:2; transition:color .2s, transform .2s; box-shadow:0 2px 8px rgba(0,0,0,0.06); border:none; cursor:pointer; }
  .fm-product-wishlist:hover { color:var(--fm-primary); transform:scale(1.1); }
  .fm-product-badge { position:absolute; top:14px; left:14px; background:var(--fm-primary); color:#fff; font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:0.06em; padding:5px 10px; border-radius:999px; z-index:2; }
  .fm-product-media { aspect-ratio:4/5; overflow:hidden; background:var(--fm-cream); }
  .fm-product-media img { width:100%; height:100%; object-fit:cover; transition:transform .45s ease; }
  .fm-product-card:hover .fm-product-media img { transform:scale(1.06); }
  .fm-product-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--fm-cream); color:#CBD5E1; }
  .fm-product-placeholder span { font-family:'Playfair Display',serif; font-size:36px; font-weight:700; }
  .fm-product-body { padding:18px; }
  .fm-product-brand { font-size:11px; text-transform:uppercase; letter-spacing:0.08em; color:#94A3B8; font-weight:600; margin-bottom:4px; }
  .fm-product-name { font-size:14px; font-weight:600; margin-bottom:10px; line-height:1.35; display:-webkit-box; -webkit-line-clamp:2; line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; min-height:38px; color:var(--fm-ink); }
  .fm-product-footer { display:flex; align-items:center; justify-content:space-between; gap:10px; }
  .fm-product-price { font-size:17px; font-weight:700; color:var(--fm-ink); }
  .fm-product-price .old { font-size:13px; color:#94A3B8; text-decoration:line-through; margin-left:6px; font-weight:500; }
  .fm-add-btn { width:40px; height:40px; border-radius:50%; background:var(--fm-ink); color:#fff; display:flex; align-items:center; justify-content:center; transition:background .2s, transform .15s; border:none; cursor:pointer; flex-shrink:0; }
  .fm-add-btn:hover { background:var(--fm-primary); }
  .fm-add-btn:active { transform:scale(0.95); }
  .fm-product-stock { font-size:11px; color:#EF4444; font-weight:600; margin-top:6px; text-transform:uppercase; letter-spacing:0.04em; }

  /* Category cards — stylish */
  .fm-cat-grid { display:grid; grid-template-columns:repeat(6,1fr); gap:16px; }
  @media(max-width:1023px){ .fm-cat-grid { grid-template-columns:repeat(4,1fr); } }
  @media(max-width:767px){ .fm-cat-grid { grid-template-columns:repeat(3,1fr); gap:12px; } }
  .fm-cat-card { position:relative; border-radius:12px; overflow:hidden; aspect-ratio:3/2; text-decoration:none; color:inherit; display:block; transition:transform .25s, box-shadow .25s; }
  .fm-cat-card:hover { transform:translateY(-4px); box-shadow:0 16px 40px rgba(15,23,42,0.12); }
  .fm-cat-card-media { position:absolute; inset:0; background:var(--fm-soft); }
  .fm-cat-card-media img { width:100%; height:100%; object-fit:cover; transition:transform .5s ease; }
  .fm-cat-card:hover .fm-cat-card-media img { transform:scale(1.08); }
  .fm-cat-card-overlay { position:absolute; inset:0; background:linear-gradient(180deg, transparent 40%, rgba(15,23,42,0.75) 100%); }
  .fm-cat-card-body { position:absolute; bottom:0; left:0; right:0; padding:20px; z-index:2; }
  .fm-cat-card-name { font-family:'Playfair Display',serif; font-size:16px; font-weight:700; color:#fff; margin-bottom:4px; }
  .fm-cat-card-count { font-size:12px; color:rgba(255,255,255,0.8); }
  .fm-cat-card-arrow { position:absolute; top:16px; right:16px; width:32px; height:32px; border-radius:50%; background:rgba(255,255,255,0.9); display:flex; align-items:center; justify-content:center; opacity:0; transform:translateY(-4px); transition:opacity .25s, transform .25s; }
  .fm-cat-card:hover .fm-cat-card-arrow { opacity:1; transform:translateY(0); }
  .fm-cat-card-arrow svg { width:16px; height:16px; color:var(--fm-ink); }
  .fm-cat-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--fm-soft); }
  .fm-cat-placeholder span { font-family:'Playfair Display',serif; font-size:42px; font-weight:700; color:var(--fm-primary); }

  /* Trust badges / values */
  .fm-values { background:var(--fm-cream); padding:48px 0; }
  .fm-values-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:24px; }
  @media(max-width:767px){ .fm-values-grid { grid-template-columns:1fr; } }
  .fm-value { text-align:center; padding:16px; }
  .fm-value-icon { width:56px; height:56px; border-radius:50%; background:rgba(99,102,241,0.1); display:flex; align-items:center; justify-content:center; font-size:24px; margin:0 auto 16px; }
  .fm-value-title { font-family:'Playfair Display',serif; font-size:16px; font-weight:700; color:var(--fm-ink); margin-bottom:8px; }
  .fm-value-text { font-size:14px; color:#64748B; line-height:1.6; }

  /* Promo banner */
  .fm-promo { background:var(--fm-soft); border-radius:16px; overflow:hidden; display:flex; align-items:center; min-height:200px; }
  @media(max-width:767px){ .fm-promo { flex-direction:column; min-height:auto; } }
  .fm-promo-media { flex:1; min-height:200px; background:var(--fm-cream); }
  .fm-promo-media img { width:100%; height:100%; object-fit:cover; }
  .fm-promo-body { flex:1; padding:40px; }
  @media(max-width:767px){ .fm-promo-body { padding:24px; } }
  .fm-promo-kicker { font-size:12px; text-transform:uppercase; letter-spacing:0.12em; color:var(--fm-primary); font-weight:700; margin-bottom:8px; }
  .fm-promo-title { font-family:'Playfair Display',serif; font-size:clamp(22px,3vw,32px); font-weight:700; color:var(--fm-ink); margin-bottom:10px; letter-spacing:-0.02em; }
  .fm-promo-text { font-size:15px; color:#64748B; line-height:1.6; margin-bottom:20px; }

  /* WhatsApp CTA */
  .fm-wa-cta { background:linear-gradient(135deg, #25D366, #1DA851); color:#fff; padding:48px 24px; text-align:center; border-radius:16px; }
  .fm-wa-cta-title { font-family:'Playfair Display',serif; font-size:clamp(22px,3vw,30px); font-weight:700; margin-bottom:10px; }
  .fm-wa-cta-text { opacity:0.9; max-width:440px; margin:0 auto 24px; font-size:15px; line-height:1.6; }
  .fm-wa-cta .fm-btn { background:#fff; color:#1DA851; }
  .fm-wa-cta .fm-btn:hover { background:var(--fm-ink); color:#fff; }

  /* Store info */
  .fm-store-info { background:var(--fm-cream); padding:48px 0; }
  .fm-store-info-grid { display:grid; grid-template-columns:1fr 1fr; gap:32px; align-items:center; }
  @media(max-width:767px){ .fm-store-info-grid { grid-template-columns:1fr; } }
  .fm-store-info-title { font-family:'Playfair Display',serif; font-size:clamp(24px,3vw,34px); font-weight:700; color:var(--fm-ink); margin-bottom:14px; letter-spacing:-0.02em; }
  .fm-store-info-text { font-size:15px; color:#64748B; line-height:1.7; margin-bottom:20px; }
  .fm-store-info-detail { display:flex; align-items:center; gap:10px; margin-bottom:10px; font-size:14px; color:var(--fm-ink); }
  .fm-store-info-detail svg { width:18px; height:18px; color:var(--fm-primary); flex-shrink:0; }

  /* Newsletter */
  .fm-newsletter { background:var(--fm-ink); color:#fff; padding:48px 24px; text-align:center; border-radius:16px; }
  .fm-newsletter-title { font-family:'Playfair Display',serif; font-size:clamp(22px,3vw,30px); font-weight:700; margin-bottom:10px; letter-spacing:-0.02em; }
  .fm-newsletter-text { opacity:0.8; max-width:440px; margin:0 auto 24px; font-size:15px; }
  .fm-newsletter-form { display:flex; gap:10px; max-width:440px; margin:0 auto; }
  .fm-newsletter-form input { flex:1; padding:14px 18px; border:none; border-radius:999px; font-size:14px; outline:none; }
  .fm-newsletter-form button { padding:14px 24px; border:none; border-radius:999px; background:var(--fm-primary); color:#fff; font-weight:600; cursor:pointer; font-size:14px; }

  /* Testimonials */
  .fm-testimonials-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; }
  @media(max-width:767px){ .fm-testimonials-grid { grid-template-columns:1fr; } }
  .fm-testimonial { background:#fff; border:1px solid var(--fm-border); border-radius:16px; padding:24px; }
  .fm-testimonial-stars { color:var(--fm-primary); margin-bottom:12px; font-size:16px; }
  .fm-testimonial-text { font-size:14px; color:#475569; line-height:1.7; margin-bottom:16px; }
  .fm-testimonial-author { font-family:'Playfair Display',serif; font-size:13px; font-weight:700; color:var(--fm-ink); }

  /* Brands */
  .fm-brands-grid { display:flex; flex-wrap:wrap; gap:20px; align-items:center; justify-content:center; }
  .fm-brand { padding:12px 24px; background:#fff; border:1px solid var(--fm-border); border-radius:999px; font-size:14px; font-weight:600; color:var(--fm-ink); }

  /* Instagram */
  .fm-insta-grid { display:grid; grid-template-columns:repeat(5,1fr); gap:12px; }
  @media(max-width:767px){ .fm-insta-grid { grid-template-columns:repeat(3,1fr); } }
  .fm-insta-item { aspect-ratio:1; border-radius:16px; overflow:hidden; position:relative; }
  .fm-insta-item img { width:100%; height:100%; object-fit:cover; transition:transform .3s; }
  .fm-insta-item:hover img { transform:scale(1.08); }

  /* FAQs */
  .fm-faq-list { max-width:760px; margin:0 auto; }
  .fm-faq-item { background:#fff; border:1px solid var(--fm-border); border-radius:16px; margin-bottom:10px; overflow:hidden; }
  .fm-faq-q { padding:18px 24px; font-size:15px; font-weight:600; color:var(--fm-ink); cursor:pointer; display:flex; justify-content:space-between; align-items:center; }
  .fm-faq-q::after { content:'+'; font-size:20px; color:var(--fm-primary); }
  .fm-faq-item.open .fm-faq-q::after { content:'\2212'; }
  .fm-faq-a { max-height:0; overflow:hidden; transition:max-height .3s ease; padding:0 24px; }
  .fm-faq-item.open .fm-faq-a { max-height:300px; padding:0 24px 18px; }
  .fm-faq-a p { font-size:14px; color:#64748B; line-height:1.7; margin:0; }

  /* Contact */
  .fm-contact-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; }
  @media(max-width:767px){ .fm-contact-grid { grid-template-columns:1fr; } }
  .fm-contact-card { background:#fff; border:1px solid var(--fm-border); border-radius:16px; padding:24px; text-align:center; }
  .fm-contact-icon { width:48px; height:48px; border-radius:50%; background:rgba(99,102,241,0.1); display:flex; align-items:center; justify-content:center; margin:0 auto 14px; color:var(--fm-primary); }
  .fm-contact-icon svg { width:22px; height:22px; }
  .fm-contact-label { font-size:12px; text-transform:uppercase; letter-spacing:0.08em; color:var(--fm-primary); font-weight:700; margin-bottom:4px; }
  .fm-contact-value { font-size:15px; font-weight:600; color:var(--fm-ink); }

  /* Store hours */
  .fm-hours-grid { max-width:520px; margin:0 auto; }
  .fm-hours-row { display:flex; justify-content:space-between; padding:12px 0; border-bottom:1px solid var(--fm-border); font-size:14px; }
  .fm-hours-row:last-child { border-bottom:none; }
  .fm-hours-day { color:var(--fm-ink); font-weight:600; }
  .fm-hours-time { color:#64748B; }
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
<div class="fm-hero">
  <div class="fm-hero-media"><img src="<?= $heroImg; ?>" alt="<?= htmlspecialchars($hero->banner_title ?? ($store->store_name ?? 'Store')); ?>"></div>
  <div class="fm-hero-gradient"></div>
  <div class="fm-hero-content">
    <p class="fm-hero-kicker"><?= htmlspecialchars($hero->banner_subtitle ?? 'New Collection'); ?></p>
    <h1 class="fm-hero-title"><?= htmlspecialchars($hero->banner_title ?? ($settings->store_headline ?: ($store->store_name ?? 'Welcome'))); ?></h1>
    <p class="fm-hero-lead"><?= htmlspecialchars($settings->store_subheadline ?: 'Curated pieces for the modern wardrobe. Premium fabrics, clean silhouettes, and statement essentials.'); ?></p>
    <div class="fm-hero-actions">
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="fm-btn fm-btn-primary">Shop the Collection</a>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="fm-btn fm-btn-ghost">View Lookbook</a>
    </div>
  </div>
</div>
<?php else: ?>
<div class="fm-hero">
  <div class="fm-hero-gradient"></div>
  <div class="fm-hero-content">
    <p class="fm-hero-kicker"><?= htmlspecialchars($settings->store_subheadline ?: 'Welcome'); ?></p>
    <h1 class="fm-hero-title"><?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Store')); ?></h1>
    <p class="fm-hero-lead"><?= htmlspecialchars($settings->store_description ?: 'Discover our curated collection of premium pieces.'); ?></p>
    <div class="fm-hero-actions">
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="fm-btn fm-btn-primary">Shop Now</a>
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
      $fmBadges = json_decode($settings->trust_badges_json ?? '', true);
      if(empty($fmBadges) || !is_array($fmBadges)){
        $fmBadges = [
          ['icon' => '&#10024;', 'title' => 'Premium Quality', 'desc' => 'Carefully selected fabrics and finishes for lasting comfort.'],
          ['icon' => '&#128230;', 'title' => 'Fast Delivery', 'desc' => 'Quick and reliable shipping to your doorstep.'],
          ['icon' => '&#129309;', 'title' => 'Personal Service', 'desc' => 'Reach us anytime on WhatsApp for styling help and orders.']
        ];
      }
?>
<!-- TRUST BADGES -->
<div class="fm-values">
  <div class="fm-container">
    <div class="fm-values-grid">
      <?php foreach(array_slice($fmBadges, 0, 3) as $b): ?>
      <div class="fm-value">
        <div class="fm-value-icon"><?= !empty($b['icon']) ? $b['icon'] : '&#10024;'; ?></div>
        <div class="fm-value-title"><?= htmlspecialchars($b['title'] ?? ''); ?></div>
        <div class="fm-value-text"><?= htmlspecialchars($b['desc'] ?? ''); ?></div>
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
<div class="fm-section">
  <div class="fm-container">
    <div class="fm-promo">
      <?php if($promoImg): ?>
      <div class="fm-promo-media"><img src="<?= $promoImg; ?>" alt="<?= htmlspecialchars($promo->banner_title ?? 'Promo'); ?>"></div>
      <?php endif; ?>
      <div class="fm-promo-body">
        <div class="fm-promo-kicker"><?= htmlspecialchars($promo->banner_subtitle ?? 'Limited Time'); ?></div>
        <h3 class="fm-promo-title"><?= htmlspecialchars($promo->banner_title ?? 'Special Offer'); ?></h3>
        <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="fm-btn fm-btn-dark">Shop Now</a>
      </div>
    </div>
  </div>
</div>
<?php
      endif;
      break;

    // =====================================================
    // FEATURED CATEGORIES — stylish cards
    // =====================================================
    case 'featured_categories':
      if(!empty($categories) && count($categories) > 1):
?>
<!-- CATEGORIES -->
<div class="fm-section" style="background:var(--fm-cream);">
  <div class="fm-container">
    <div class="fm-section-head">
      <div>
        <div class="fm-section-label">Browse</div>
        <h2 class="fm-section-title">Shop by Category</h2>
      </div>
    </div>
    <div class="fm-cat-grid">
      <?php foreach(array_slice($categories, 0, 8) as $cat):
        $catImg = (!empty($cat->category_image) && file_exists($cat->category_image)) ? base_url($cat->category_image) : '';
        $itemCount = $cat->item_count ?? 0;
      ?>
      <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="fm-cat-card">
        <div class="fm-cat-card-media">
          <?php if($catImg): ?>
          <img src="<?= $catImg; ?>" alt="<?= htmlspecialchars($cat->category_name); ?>" loading="lazy">
          <?php else: ?>
          <div class="fm-cat-placeholder"><span><?= htmlspecialchars(substr($cat->category_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="fm-cat-card-overlay"></div>
        <div class="fm-cat-card-arrow">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </div>
        <div class="fm-cat-card-body">
          <div class="fm-cat-card-name"><?= htmlspecialchars($cat->category_name); ?></div>
          <?php if($itemCount > 0): ?>
          <div class="fm-cat-card-count"><?= $itemCount; ?> items</div>
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
<div class="fm-section">
  <div class="fm-container">
    <div class="fm-section-head">
      <div>
        <div class="fm-section-label">Trending Now</div>
        <h2 class="fm-section-title">Featured Collection</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="fm-section-link">Shop All</a>
    </div>
    <div class="fm-product-grid">
      <?php foreach(array_slice($featured_products, 0, 8) as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $oldPrice = $p->original_price ?? $p->sales_price;
        $hasDiscount = $oldPrice > $price;
        $discountPct = $hasDiscount ? round((($oldPrice - $price) / $oldPrice) * 100) : 0;
        $img = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
      ?>
      <div class="fm-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, <?= $hasDiscount ? $oldPrice : 0; ?>)">
        <?php if($hasDiscount && $discountPct > 0): ?>
        <span class="fm-product-badge">-<?= $discountPct; ?>%</span>
        <?php endif; ?>
        <button class="fm-product-wishlist" onclick="event.stopPropagation();"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></button>
        <div class="fm-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
          <?php else: ?>
          <div class="fm-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="fm-product-body">
          <?php if(!empty($p->category_name)): ?>
          <div class="fm-product-brand"><?= htmlspecialchars($p->category_name); ?></div>
          <?php endif; ?>
          <div class="fm-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="fm-product-footer">
            <div class="fm-product-price"><?= sf_currency($price, $cur); ?><?php if($hasDiscount): ?><span class="old"><?= sf_currency($oldPrice, $cur); ?></span><?php endif; ?></div>
            <button class="fm-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add to cart"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></button>
          </div>
          <?php if($p->stock <= 0 && !($settings->allow_backorder ?? false)): ?>
          <div class="fm-product-stock">Out of Stock</div>
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
<div class="fm-section" style="background:var(--fm-cream);">
  <div class="fm-container">
    <div class="fm-section-head">
      <div>
        <div class="fm-section-label">Services</div>
        <h2 class="fm-section-title">Our Services</h2>
      </div>
    </div>
    <div class="fm-product-grid">
      <?php foreach(array_slice($featured_services, 0, 4) as $s):
        $sPrice = $s->effective_price ?? $s->sales_price ?? $s->price ?? 0;
        $sImg = (!empty($s->item_image) && file_exists($s->item_image)) ? base_url($s->item_image) : (!empty($s->service_image) && file_exists($s->service_image) ? base_url($s->service_image) : '');
      ?>
      <div class="fm-product-card" onclick="openProductModal(<?= $s->id; ?>, '<?= htmlspecialchars(addslashes($s->item_name ?? $s->service_name ?? '')); ?>', <?= $sPrice; ?>, '<?= $s->item_image ?? $s->service_image ?? ''; ?>', '<?= htmlspecialchars(addslashes($s->description ?? '')); ?>', 999, 0)">
        <div class="fm-product-media">
          <?php if($sImg): ?>
          <img src="<?= $sImg; ?>" alt="<?= htmlspecialchars($s->item_name ?? $s->service_name ?? ''); ?>" loading="lazy">
          <?php else: ?>
          <div class="fm-product-placeholder"><span><?= htmlspecialchars(substr($s->item_name ?? $s->service_name ?? 'S', 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="fm-product-body">
          <div class="fm-product-name"><?= htmlspecialchars($s->item_name ?? $s->service_name ?? ''); ?></div>
          <div class="fm-product-footer">
            <div class="fm-product-price"><?= sf_currency($sPrice, $cur); ?></div>
            <button class="fm-add-btn" onclick="event.stopPropagation();addToCart(<?= $s->id; ?>,'service','<?= htmlspecialchars(addslashes($s->item_name ?? $s->service_name ?? '')); ?>',<?= $sPrice; ?>,'<?= $s->item_image ?? $s->service_image ?? ''; ?>',1,999)" aria-label="Add to cart"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></button>
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
<div class="fm-section">
  <div class="fm-container">
    <div class="fm-section-head">
      <div>
        <div class="fm-section-label">Customer Favorites</div>
        <h2 class="fm-section-title">Best Sellers</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="fm-section-link">View All</a>
    </div>
    <div class="fm-product-grid">
      <?php foreach(array_slice($best_sellers, 0, 4) as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $img = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
      ?>
      <div class="fm-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, 0)">
        <div class="fm-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
          <?php else: ?>
          <div class="fm-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="fm-product-body">
          <div class="fm-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="fm-product-footer">
            <div class="fm-product-price"><?= sf_currency($price, $cur); ?></div>
            <button class="fm-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add to cart"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></button>
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
<div class="fm-section" style="background:var(--fm-cream);">
  <div class="fm-container">
    <div class="fm-section-head">
      <div>
        <div class="fm-section-label">Just In</div>
        <h2 class="fm-section-title">New Arrivals</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="fm-section-link">View All</a>
    </div>
    <div class="fm-product-grid">
      <?php foreach(array_slice($new_arrivals, 0, 4) as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $img = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
      ?>
      <div class="fm-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, 0)">
        <div class="fm-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
          <?php else: ?>
          <div class="fm-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="fm-product-body">
          <div class="fm-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="fm-product-footer">
            <div class="fm-product-price"><?= sf_currency($price, $cur); ?></div>
            <button class="fm-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add to cart"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg></button>
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
<div class="fm-section">
  <div class="fm-container">
    <div class="fm-section-head" style="justify-content:center;text-align:center;">
      <h2 class="fm-section-title">Our Brands</h2>
    </div>
    <div class="fm-brands-grid">
      <?php foreach($brands as $brand): ?>
      <div class="fm-brand"><?= htmlspecialchars($brand->brand_name ?? $brand->name ?? ''); ?></div>
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
<div class="fm-section" style="background:var(--fm-cream);">
  <div class="fm-container">
    <div class="fm-section-head">
      <div>
        <div class="fm-section-label">Reviews</div>
        <h2 class="fm-section-title">What Customers Say</h2>
      </div>
    </div>
    <div class="fm-testimonials-grid">
      <?php foreach(array_slice($testimonials, 0, 3) as $t): ?>
      <div class="fm-testimonial">
        <div class="fm-testimonial-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
        <div class="fm-testimonial-text"><?= htmlspecialchars($t->testimonial_text ?? $t->message ?? ''); ?></div>
        <div class="fm-testimonial-author"><?= htmlspecialchars($t->customer_name ?? $t->author ?? ''); ?></div>
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
<div class="fm-section">
  <div class="fm-container">
    <div class="fm-section-head" style="justify-content:center;text-align:center;">
      <h2 class="fm-section-title">Follow Us</h2>
    </div>
    <div class="fm-insta-grid">
      <?php foreach(array_slice($instagram_posts, 0, 10) as $post): ?>
      <a href="<?= htmlspecialchars($post->permalink ?? $post->link ?? '#'); ?>" target="_blank" class="fm-insta-item">
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
<div class="fm-store-info">
  <div class="fm-container">
    <div class="fm-store-info-grid">
      <div>
        <div class="fm-section-label">About Us</div>
        <h2 class="fm-store-info-title"><?= htmlspecialchars($store->store_name ?? 'Our Store'); ?></h2>
        <p class="fm-store-info-text"><?= htmlspecialchars($settings->store_description ?? 'We are committed to bringing you the best products with exceptional service.'); ?></p>
        <?php if(!empty($settings->store_phone)): ?>
        <div class="fm-store-info-detail">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
          <?= htmlspecialchars($settings->store_phone); ?>
        </div>
        <?php endif; ?>
        <?php if(!empty($settings->store_email)): ?>
        <div class="fm-store-info-detail">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          <?= htmlspecialchars($settings->store_email); ?>
        </div>
        <?php endif; ?>
        <?php if(!empty($settings->store_address)): ?>
        <div class="fm-store-info-detail">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          <?= htmlspecialchars($settings->store_address); ?>
        </div>
        <?php endif; ?>
      </div>
      <div>
        <?php if($logo_url): ?>
        <img src="<?= $logo_url; ?>" alt="<?= htmlspecialchars($store->store_name ?? 'Store'); ?>" style="max-width:280px;width:100%;border-radius:16px;">
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
<div class="fm-section">
  <div class="fm-container">
    <div class="fm-section-head" style="justify-content:center;text-align:center;">
      <h2 class="fm-section-title">Frequently Asked Questions</h2>
    </div>
    <div class="fm-faq-list">
      <?php foreach($faqs as $faq): ?>
      <div class="fm-faq-item" onclick="this.classList.toggle('open')">
        <div class="fm-faq-q"><?= htmlspecialchars($faq->question ?? $faq->faq_question ?? ''); ?></div>
        <div class="fm-faq-a"><p><?= htmlspecialchars($faq->answer ?? $faq->faq_answer ?? ''); ?></p></div>
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
<div class="fm-section" style="background:var(--fm-cream);">
  <div class="fm-container">
    <div class="fm-section-head" style="justify-content:center;text-align:center;">
      <h2 class="fm-section-title">Get In Touch</h2>
    </div>
    <div class="fm-contact-grid">
      <?php if(!empty($settings->store_phone)): ?>
      <div class="fm-contact-card">
        <div class="fm-contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></div>
        <div class="fm-contact-label">Phone</div>
        <div class="fm-contact-value"><?= htmlspecialchars($settings->store_phone); ?></div>
      </div>
      <?php endif; ?>
      <?php if(!empty($settings->store_email)): ?>
      <div class="fm-contact-card">
        <div class="fm-contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
        <div class="fm-contact-label">Email</div>
        <div class="fm-contact-value"><?= htmlspecialchars($settings->store_email); ?></div>
      </div>
      <?php endif; ?>
      <?php if($waNumber): ?>
      <div class="fm-contact-card">
        <div class="fm-contact-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg></div>
        <div class="fm-contact-label">WhatsApp</div>
        <div class="fm-contact-value"><?= htmlspecialchars($settings->whatsapp_number); ?></div>
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
<div class="fm-section">
  <div class="fm-container">
    <div class="fm-wa-cta">
      <div class="fm-wa-cta-title">Need Help? Chat With Us</div>
      <p class="fm-wa-cta-text">Have a question about a product or your order? Our team is ready to help you on WhatsApp.</p>
      <a href="https://wa.me/<?= $waNumber; ?>" target="_blank" class="fm-btn">Start WhatsApp Chat</a>
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
<div class="fm-section">
  <div class="fm-container">
    <div class="fm-newsletter">
      <div class="fm-newsletter-title">Stay In The Know</div>
      <p class="fm-newsletter-text">Subscribe to get updates on new arrivals, exclusive offers and more.</p>
      <form class="fm-newsletter-form" onsubmit="event.preventDefault();showToast('Thank you for subscribing!');this.reset();">
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
<div class="fm-section" style="background:var(--fm-cream);">
  <div class="fm-container">
    <div class="fm-section-head" style="justify-content:center;text-align:center;">
      <h2 class="fm-section-title">Opening Hours</h2>
    </div>
    <div class="fm-hours-grid">
      <?php foreach($business_hours as $day => $hours): ?>
      <div class="fm-hours-row">
        <span class="fm-hours-day"><?= htmlspecialchars(ucfirst($day)); ?></span>
        <span class="fm-hours-time"><?= htmlspecialchars($hours); ?></span>
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
