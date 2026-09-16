<?php
/**
 * Grocery Plus — Premium Supermarket Homepage
 * Deep emerald and warm gold, Inter + Lora, large-format chain feel.
 * Uses mp_minified_image_url() for all imagery.
 */
$slug = $settings->store_slug ?? '';
$cur = $store_currency ?? null;

$orderedSections = [];
if(!empty($homepage_sections)){
  $orderedSections = $homepage_sections;
  uasort($orderedSections, function($a, $b){ return ($a->display_order ?? 0) <=> ($b->display_order ?? 0); });
}

$hero = !empty($hero_banners) ? $hero_banners[0] : null;
$heroImg = '';
if($hero && $hero->desktop_image) $heroImg = mp_minified_image_url($hero->desktop_image, 1600);
elseif($hero && $hero->mobile_image) $heroImg = mp_minified_image_url($hero->mobile_image, 900);

$waNumber = preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? '');
?>
<style>
  :root {
    --gp-emerald:#047857;
    --gp-emerald-dark:#065F46;
    --gp-emerald-deep:#064E3B;
    --gp-gold:#B45309;
    --gp-gold-light:#F59E0B;
    --gp-cream:#F5F5F0;
    --gp-soft:#ECFDF5;
    --gp-dark:#1F2937;
    --gp-gray:#6B7280;
    --gp-border:#E5E7EB;
    --gp-white:#FFFFFF;
  }

  .theme-grocery_plus .mp-topbar,
  .theme-grocery_plus .mp-announcement,
  .theme-grocery_plus .mp-nav,
  .theme-grocery_plus .mp-header,
  .theme-grocery_plus .mp-mobile-menu-btn,
  .theme-grocery_plus .mp-footer-space { display:none !important; }

  .gp-container { max-width:1400px; margin:0 auto; padding:0 24px; }
  @media(max-width:767px){ .gp-container { padding:0 16px; } }

  .gp-hero { position:relative; background:linear-gradient(135deg, #064E3B 0%, #047857 50%, #065F46 100%); color:#fff; overflow:hidden; min-height:540px; display:flex; align-items:center; }
  @media(max-width:767px){ .gp-hero { min-height:460px; } }
  .gp-hero-media { position:absolute; inset:0; opacity:0.22; }
  .gp-hero-media img { width:100%; height:100%; object-fit:cover; }
  .gp-hero-gradient { position:absolute; inset:0; background:linear-gradient(90deg, rgba(6,78,59,0.9) 0%, rgba(4,120,87,0.55) 55%, rgba(6,95,70,0.1) 100%); }
  .gp-hero-content { position:relative; z-index:2; padding:90px 24px 110px; max-width:1400px; margin:0 auto; width:100%; }
  .gp-hero-kicker { font-family:'Inter',sans-serif; font-size:12px; text-transform:uppercase; letter-spacing:0.18em; color:#A7F3D0; margin-bottom:16px; font-weight:600; }
  .gp-hero-title { font-family:'Lora',serif; font-size:clamp(34px,5vw,58px); line-height:1.1; font-weight:700; margin:0 0 18px; max-width:650px; letter-spacing:-0.01em; }
  .gp-hero-lead { font-family:'Inter',sans-serif; font-size:clamp(15px,1.8vw,18px); line-height:1.65; opacity:0.95; margin-bottom:32px; max-width:520px; }
  .gp-hero-actions { display:flex; gap:12px; flex-wrap:wrap; }

  .gp-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:14px 32px; border-radius:999px; font-family:'Inter',sans-serif; font-size:14px; font-weight:600; transition:transform .15s, background .2s, color .2s; cursor:pointer; border:none; text-decoration:none; }
  .gp-btn:active { transform:scale(0.98); }
  .gp-btn-gold { background:var(--gp-gold-light); color:var(--gp-dark); }
  .gp-btn-gold:hover { background:#FBBF24; }
  .gp-btn-emerald { background:var(--gp-emerald); color:#fff; }
  .gp-btn-emerald:hover { background:var(--gp-emerald-dark); }
  .gp-btn-ghost { background:rgba(255,255,255,0.1); color:#fff; border:1.5px solid rgba(255,255,255,0.4); }
  .gp-btn-ghost:hover { background:rgba(255,255,255,0.2); }
  .gp-btn-outline { background:#fff; color:var(--gp-emerald); border:1.5px solid var(--gp-emerald); }
  .gp-btn-outline:hover { background:var(--gp-emerald); color:#fff; }

  .gp-section { padding:72px 0; }
  @media(max-width:767px){ .gp-section { padding:44px 0; } }
  .gp-section-head { display:flex; align-items:flex-end; justify-content:space-between; gap:16px; margin-bottom:36px; }
  .gp-section-label { font-family:'Inter',sans-serif; font-size:12px; text-transform:uppercase; letter-spacing:0.14em; color:var(--gp-gold); font-weight:600; margin-bottom:6px; }
  .gp-section-title { font-family:'Lora',serif; font-size:32px; margin:0; font-weight:700; color:var(--gp-dark); letter-spacing:-0.01em; }
  .gp-section-link { font-family:'Inter',sans-serif; font-size:14px; font-weight:600; color:var(--gp-emerald); text-decoration:none; transition:color .2s; }
  .gp-section-link:hover { color:var(--gp-emerald-dark); }

  .gp-product-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:24px; }
  @media(max-width:1023px){ .gp-product-grid { grid-template-columns:repeat(3,1fr); } }
  @media(max-width:767px){ .gp-product-grid { grid-template-columns:repeat(2,1fr); gap:14px; } }
  .gp-product-card { position:relative; background:#fff; border-radius:12px; overflow:hidden; border:1px solid var(--gp-border); transition:transform .25s, box-shadow .25s; cursor:pointer; }
  .gp-product-card:hover { transform:translateY(-5px); box-shadow:0 16px 40px rgba(4,120,87,0.12); }
  .gp-product-badge { position:absolute; top:14px; left:14px; background:var(--gp-gold); color:#fff; font-family:'Inter',sans-serif; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.04em; padding:5px 12px; border-radius:999px; z-index:2; }
  .gp-product-badge.new { background:var(--gp-emerald); }
  .gp-product-media { aspect-ratio:1/1; overflow:hidden; background:var(--gp-cream); }
  .gp-product-media img { width:100%; height:100%; object-fit:cover; transition:transform .4s ease; }
  .gp-product-card:hover .gp-product-media img { transform:scale(1.05); }
  .gp-product-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--gp-soft); color:var(--gp-emerald); }
  .gp-product-placeholder span { font-family:'Lora',serif; font-size:38px; font-weight:700; }
  .gp-product-body { padding:18px; }
  .gp-product-brand { font-family:'Inter',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.08em; color:var(--gp-emerald); font-weight:600; margin-bottom:4px; }
  .gp-product-name { font-family:'Lora',serif; font-size:16px; font-weight:600; margin-bottom:10px; line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; min-height:44px; color:var(--gp-dark); }
  .gp-product-footer { display:flex; flex-direction:column; gap:8px; }
  .gp-product-price { font-family:'Inter',sans-serif; font-size:19px; font-weight:800; color:var(--gp-dark); }
  .gp-product-price .old { font-family:'Inter',sans-serif; font-size:13px; color:var(--gp-gray); text-decoration:line-through; margin-left:6px; font-weight:400; }
  .gp-card-actions { display:flex; flex-direction:column; gap:8px; }
  .gp-add-btn { width:100%; padding:12px 16px; border-radius:999px; background:var(--gp-emerald); color:#fff; display:flex; align-items:center; justify-content:center; gap:6px; transition:background .2s, transform .15s; border:none; cursor:pointer; font-family:'Inter',sans-serif; font-size:13px; font-weight:600; }
  .gp-add-btn:hover { background:var(--gp-emerald-dark); }
  .gp-add-btn:active { transform:scale(0.97); }
  .gp-wa-btn { width:100%; padding:12px 16px; border-radius:999px; background:#25D366; color:#fff; display:flex; align-items:center; justify-content:center; gap:6px; transition:background .2s, transform .15s; border:none; cursor:pointer; font-family:'Inter',sans-serif; font-size:13px; font-weight:600; }
  .gp-wa-btn:hover { background:#1FB855; }
  .gp-wa-btn:active { transform:scale(0.97); }
  .gp-product-stock { font-family:'Inter',sans-serif; font-size:11px; color:#DC2626; font-weight:600; margin-top:6px; text-transform:uppercase; letter-spacing:0.04em; }
  .gp-product-stock.in { color:var(--gp-emerald); }

  .gp-cat-grid { display:grid; grid-template-columns:repeat(5,1fr); gap:16px; }
  @media(max-width:1023px){ .gp-cat-grid { grid-template-columns:repeat(3,1fr); } }
  @media(max-width:767px){ .gp-cat-grid { grid-template-columns:repeat(2,1fr); gap:10px; } }
  .gp-cat-card { background:#fff; border:1px solid var(--gp-border); border-radius:14px; padding:22px; text-align:center; text-decoration:none; color:inherit; transition:transform .25s, box-shadow .25s, border-color .2s; }
  .gp-cat-card:hover { transform:translateY(-5px); box-shadow:0 16px 40px rgba(4,120,87,0.12); border-color:var(--gp-emerald); }
  .gp-cat-icon { width:64px; height:64px; border-radius:999px; background:var(--gp-cream); display:flex; align-items:center; justify-content:center; margin:0 auto 16px; color:var(--gp-emerald); overflow:hidden; }
  .gp-cat-icon img { width:48px;height:48px;object-fit:cover;border-radius:999px; }
  .gp-cat-name { font-family:'Lora',serif; font-size:16px; font-weight:700; color:var(--gp-dark); }
  .gp-cat-count { font-family:'Inter',sans-serif; font-size:12px; color:var(--gp-gray); margin-top:2px; }

  .gp-trust { background:var(--gp-cream); padding:60px 0; }
  .gp-trust-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:18px; }
  @media(max-width:1023px){ .gp-trust-grid { grid-template-columns:repeat(2,1fr); } }
  @media(max-width:767px){ .gp-trust-grid { grid-template-columns:1fr; } }
  .gp-trust-item { text-align:center; padding:24px 16px; background:#fff; border-radius:14px; border:1px solid var(--gp-border); transition:transform .2s; }
  .gp-trust-item:hover { transform:translateY(-4px); }
  .gp-trust-item-icon { width:52px; height:52px; border-radius:999px; background:rgba(4,120,87,0.08); display:flex; align-items:center; justify-content:center; font-size:24px; margin:0 auto 14px; color:var(--gp-emerald); }
  .gp-trust-item-title { font-family:'Lora',serif; font-size:18px; font-weight:700; color:var(--gp-dark); margin-bottom:6px; }
  .gp-trust-item-text { font-family:'Inter',sans-serif; font-size:13px; color:var(--gp-gray); line-height:1.5; }

  .gp-promo { background:#fff; border:1px solid var(--gp-border); border-radius:14px; overflow:hidden; display:grid; grid-template-columns:1fr 1fr; align-items:center; min-height:260px; }
  @media(max-width:767px){ .gp-promo { grid-template-columns:1fr; min-height:auto; } }
  .gp-promo-media { min-height:260px; background:var(--gp-cream); }
  .gp-promo-media img { width:100%; height:100%; object-fit:cover; }
  .gp-promo-body { padding:48px; }
  .gp-promo-kicker { font-family:'Inter',sans-serif; font-size:12px; text-transform:uppercase; letter-spacing:0.14em; color:var(--gp-gold); font-weight:600; margin-bottom:8px; }
  .gp-promo-title { font-family:'Lora',serif; font-size:clamp(24px,2.5vw,32px); font-weight:700; margin-bottom:10px; letter-spacing:-0.01em; }
  .gp-promo-text { font-family:'Inter',sans-serif; font-size:15px; color:var(--gp-gray); line-height:1.6; margin-bottom:20px; }

  .gp-wa-cta { background:linear-gradient(135deg, #25D366, #1DA851); color:#fff; padding:48px 24px; text-align:center; border-radius:14px; }
  .gp-wa-cta-title { font-family:'Lora',serif; font-size:clamp(24px,2.5vw,32px); font-weight:700; margin-bottom:10px; }
  .gp-wa-cta-text { font-family:'Inter',sans-serif; opacity:0.95; max-width:440px; margin:0 auto 24px; font-size:15px; }
  .gp-newsletter { background:var(--gp-emerald); color:#fff; padding:48px 24px; text-align:center; border-radius:14px; }
  .gp-newsletter-title { font-family:'Lora',serif; font-size:clamp(24px,2.5vw,32px); font-weight:700; margin-bottom:10px; }
  .gp-newsletter-text { font-family:'Inter',sans-serif; opacity:0.9; max-width:440px; margin:0 auto 24px; font-size:15px; }
  .gp-newsletter-form { display:flex; gap:10px; max-width:440px; margin:0 auto; }
  .gp-newsletter-form input { flex:1; padding:14px 18px; border:none; border-radius:999px; font-family:'Inter',sans-serif; font-size:14px; outline:none; }
  .gp-newsletter-form button { padding:14px 24px; border:none; border-radius:999px; background:var(--gp-gold-light); color:var(--gp-dark); font-family:'Inter',sans-serif; font-weight:600; cursor:pointer; font-size:14px; transition:background .2s; }
  .gp-newsletter-form button:hover { background:#FBBF24; }

  .gp-testimonials-grid, .gp-contact-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:18px; }
  @media(max-width:767px){ .gp-testimonials-grid, .gp-contact-grid { grid-template-columns:1fr; } }
  .gp-testimonial, .gp-contact-card { background:#fff; border:1px solid var(--gp-border); border-radius:14px; padding:24px; }
  .gp-testimonial-stars { color:var(--gp-gold-light); margin-bottom:12px; font-size:16px; letter-spacing:2px; }
  .gp-testimonial-text { font-family:'Inter',sans-serif; font-size:14px; color:var(--gp-gray); line-height:1.7; margin-bottom:16px; }
  .gp-testimonial-author { font-family:'Lora',serif; font-size:17px; font-weight:700; color:var(--gp-dark); }
  .gp-testimonial-role { font-family:'Inter',sans-serif; font-size:12px; color:var(--gp-emerald); margin-top:2px; }
  .gp-contact-card { text-align:center; transition:transform .2s; }
  .gp-contact-card:hover { transform:translateY(-4px); }
  .gp-contact-icon { width:52px; height:52px; border-radius:999px; background:rgba(4,120,87,0.08); display:flex; align-items:center; justify-content:center; margin:0 auto 14px; color:var(--gp-emerald); }
  .gp-contact-label { font-family:'Inter',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.12em; color:var(--gp-gold); font-weight:600; margin-bottom:4px; }
  .gp-contact-value { font-family:'Inter',sans-serif; font-size:15px; font-weight:600; color:var(--gp-dark); }

  .gp-brands-grid { display:flex; flex-wrap:wrap; gap:14px; align-items:center; justify-content:center; }
  .gp-brand { padding:12px 24px; background:#fff; border:1px solid var(--gp-border); border-radius:999px; font-family:'Inter',sans-serif; font-size:14px; font-weight:600; color:var(--gp-dark); transition:border-color .2s, color .2s; }
  .gp-brand:hover { border-color:var(--gp-emerald); color:var(--gp-emerald); }
  .gp-insta-grid { display:grid; grid-template-columns:repeat(5,1fr); gap:12px; }
  @media(max-width:767px){ .gp-insta-grid { grid-template-columns:repeat(3,1fr); } }
  .gp-insta-item { aspect-ratio:1; border-radius:14px; overflow:hidden; }
  .gp-insta-item img { width:100%; height:100%; object-fit:cover; transition:transform .3s; }
  .gp-insta-item:hover img { transform:scale(1.08); }

  .gp-faq-list { max-width:760px; margin:0 auto; }
  .gp-faq-item { background:#fff; border:1px solid var(--gp-border); border-radius:14px; margin-bottom:10px; overflow:hidden; }
  .gp-faq-q { padding:18px 24px; font-family:'Lora',serif; font-size:16px; font-weight:700; color:var(--gp-dark); cursor:pointer; display:flex; justify-content:space-between; align-items:center; }
  .gp-faq-q::after { content:'+'; font-size:20px; color:var(--gp-emerald); font-weight:700; }
  .gp-faq-item.open .gp-faq-q::after { content:'\2212'; }
  .gp-faq-a { max-height:0; overflow:hidden; transition:max-height .3s ease; padding:0 24px; }
  .gp-faq-item.open .gp-faq-a { max-height:300px; padding:0 24px 18px; }
  .gp-faq-a p { font-family:'Inter',sans-serif; font-size:14px; color:var(--gp-gray); line-height:1.7; margin:0; }

  .gp-hours-grid { max-width:520px; margin:0 auto; background:#fff; border:1px solid var(--gp-border); border-radius:14px; padding:0 24px; }
  .gp-hours-row { display:flex; justify-content:space-between; align-items:center; padding:14px 0; border-bottom:1px solid var(--gp-border); font-family:'Inter',sans-serif; font-size:15px; }
  .gp-hours-row:last-child { border-bottom:none; }
  .gp-hours-day { color:var(--gp-dark); font-weight:600; }
  .gp-hours-time { color:var(--gp-gray); }
  .gp-hours-full { width:100%; text-align:center; color:var(--gp-dark); }

  .wa-order-modal { display:none; position:fixed; inset:0; z-index:9999; }
  .wa-order-modal.open { display:block; }
  .wa-order-modal-overlay { position:absolute; inset:0; background:rgba(6,78,59,0.4); }
  .wa-order-modal-card { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:440px; max-width:calc(100vw - 32px); max-height:90vh; overflow-y:auto; background:#fff; border-radius:14px; box-shadow:0 24px 60px rgba(4,120,87,0.25); border:1px solid var(--gp-border); }
  .wa-order-modal-header { display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid var(--gp-border); }
  .wa-order-modal-title { font-family:'Lora',serif; font-size:20px; font-weight:700; color:var(--gp-dark); margin:0; }
  .wa-order-modal-close { width:32px; height:32px; border-radius:999px; border:none; background:var(--gp-cream); color:var(--gp-dark); cursor:pointer; display:flex; align-items:center; justify-content:center; }
  .wa-order-modal-body { padding:24px; }
  .wa-order-modal-product { display:flex; gap:14px; align-items:center; padding:14px; background:var(--gp-cream); border-radius:12px; margin-bottom:20px; }
  .wa-order-modal-product img { width:56px; height:56px; border-radius:8px; object-fit:cover; flex-shrink:0; }
  .wa-order-modal-product-name { font-family:'Lora',serif; font-size:15px; font-weight:700; color:var(--gp-dark); margin:0 0 4px; }
  .wa-order-modal-product-price { font-family:'Inter',sans-serif; font-size:16px; font-weight:800; color:var(--gp-emerald); }
  .wa-order-modal-fields { display:flex; flex-direction:column; gap:14px; }
  .wa-order-modal-fields label { font-family:'Inter',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.08em; font-weight:600; color:var(--gp-dark); margin-bottom:5px; display:block; }
  .wa-order-modal-fields input, .wa-order-modal-fields textarea { width:100%; padding:12px 14px; border:1px solid var(--gp-border); border-radius:10px; font-family:'Inter',sans-serif; font-size:14px; background:var(--gp-cream); outline:none; color:var(--gp-dark); box-sizing:border-box; }
  .wa-order-modal-fields textarea { resize:vertical; min-height:64px; }
  .wa-order-modal-actions { display:flex; gap:10px; margin-top:20px; }
  .wa-order-modal-send { flex:1; padding:14px; border-radius:999px; border:none; background:#25D366; color:#fff; font-family:'Inter',sans-serif; font-size:14px; font-weight:600; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; }
  .wa-order-modal-cancel { padding:14px 20px; border-radius:999px; border:1px solid var(--gp-border); background:#fff; color:var(--gp-dark); font-family:'Inter',sans-serif; font-size:14px; font-weight:600; cursor:pointer; }

  /* Small-screen polish */
  @media(max-width:480px){
    .gp-product-body { padding:14px; }
    .gp-product-name { font-size:14px; min-height:38px; }
    .gp-product-price { font-size:17px; }
    .gp-add-btn, .gp-wa-btn { padding:10px 12px; font-size:12px; }
    .gp-newsletter-form { flex-direction:column; }
    .gp-newsletter-form button { width:100%; }
    .gp-section-head { flex-direction:column; align-items:flex-start; gap:8px; }
    .gp-cat-card { padding:14px 10px; }
    .gp-cat-name { font-size:14px; }
    .gp-hero-actions .gp-btn { width:100%; }
    .gp-promo-body { padding:24px 20px; }
  }
</style>

<?php
foreach($orderedSections as $sectionKey => $section):
  if(!$section->is_enabled) continue;

  switch($sectionKey):
    case 'hero_banner':
?>
<?php if($heroImg): ?>
<div class="gp-hero">
  <div class="gp-hero-media"><img src="<?= $heroImg; ?>" alt="<?= htmlspecialchars($hero->banner_title ?? ($store->store_name ?? 'Grocery Plus')); ?>" decoding="async"></div>
  <div class="gp-hero-gradient"></div>
  <div class="gp-hero-content">
    <p class="gp-hero-kicker"><?= htmlspecialchars($hero->banner_subtitle ?? 'Premium grocery shopping'); ?></p>
    <h1 class="gp-hero-title"><?= htmlspecialchars($hero->banner_title ?? ($settings->store_headline ?: ($store->store_name ?? 'A Better Way to Shop Groceries'))); ?></h1>
    <p class="gp-hero-lead"><?= htmlspecialchars($settings->store_subheadline ?: 'Discover a curated selection of fresh produce, quality pantry staples and household essentials. Premium quality, delivered to your door.'); ?></p>
    <div class="gp-hero-actions">
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="gp-btn gp-btn-gold">Shop Premium</a>
      <a href="https://wa.me/<?= $waNumber; ?>" target="_blank" class="gp-btn gp-btn-ghost">Order on WhatsApp</a>
    </div>
  </div>
</div>
<?php else: ?>
<div class="gp-hero">
  <div class="gp-hero-content">
    <p class="gp-hero-kicker"><?= htmlspecialchars($settings->store_subheadline ?? 'Premium grocery shopping'); ?></p>
    <h1 class="gp-hero-title"><?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'A Better Way to Shop Groceries')); ?></h1>
    <p class="gp-hero-lead"><?= htmlspecialchars($settings->store_description ?: 'Discover a curated selection of fresh produce, quality pantry staples and household essentials. Premium quality, delivered to your door.'); ?></p>
    <div class="gp-hero-actions">
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="gp-btn gp-btn-gold">Shop Premium</a>
      <a href="https://wa.me/<?= $waNumber; ?>" target="_blank" class="gp-btn gp-btn-ghost">Order on WhatsApp</a>
    </div>
  </div>
</div>
<?php endif; ?>
<?php
      break;

    case 'trust_badges':
      $gpBadges = json_decode($settings->trust_badges_json ?? '', true);
      if(empty($gpBadges) || !is_array($gpBadges)) break;
?>
<div class="gp-trust">
  <div class="gp-container">
    <div class="gp-trust-grid">
      <?php foreach(array_slice($gpBadges, 0, 4) as $b): ?>
      <div class="gp-trust-item">
        <div class="gp-trust-item-icon"><?= !empty($b['icon']) ? $b['icon'] : '&#10004;'; ?></div>
        <div class="gp-trust-item-title"><?= htmlspecialchars($b['title'] ?? ''); ?></div>
        <div class="gp-trust-item-text"><?= htmlspecialchars($b['desc'] ?? ''); ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php
      break;

    case 'promo_banner':
      if(!empty($promo_banners)):
        $promo = $promo_banners[0];
        $promoImg = ($promo->desktop_image && file_exists($promo->desktop_image)) ? mp_minified_image_url($promo->desktop_image, 1200) : '';
?>
<div class="gp-section" style="background:var(--gp-cream);">
  <div class="gp-container">
    <div class="gp-promo">
      <?php if($promoImg): ?>
      <div class="gp-promo-media"><img src="<?= $promoImg; ?>" alt="<?= htmlspecialchars($promo->banner_title ?? 'Promo'); ?>" decoding="async"></div>
      <?php endif; ?>
      <div class="gp-promo-body">
        <div class="gp-promo-kicker"><?= htmlspecialchars($promo->banner_subtitle ?? 'Limited Offer'); ?></div>
        <h3 class="gp-promo-title"><?= htmlspecialchars($promo->banner_title ?? 'Premium Deals This Week'); ?></h3>
        <p class="gp-promo-text">Hand-picked specials for our premium members. Stock up on quality today.</p>
        <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="gp-btn gp-btn-emerald">Shop Deals</a>
      </div>
    </div>
  </div>
</div>
<?php
      endif;
      break;

    case 'featured_categories':
      if(!empty($categories) && count($categories) > 1):
?>
<div class="gp-section" style="background:#fff;">
  <div class="gp-container">
    <div class="gp-section-head">
      <div>
        <div class="gp-section-label">Browse</div>
        <h2 class="gp-section-title">Shop by Category</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="gp-section-link">View All &rarr;</a>
    </div>
    <div class="gp-cat-grid">
      <?php foreach(array_slice($categories, 0, 10) as $cat):
        $catImg = (!empty($cat->category_image) && file_exists($cat->category_image)) ? mp_minified_image_url($cat->category_image, 200) : '';
        $itemCount = $cat->item_count ?? 0;
      ?>
      <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="gp-cat-card">
        <div class="gp-cat-icon">
          <?php if($catImg): ?>
          <img src="<?= $catImg; ?>" alt="<?= htmlspecialchars($cat->category_name); ?>" loading="lazy" decoding="async">
          <?php else: ?>
          <span style="font-family:'Lora',serif;font-size:24px;font-weight:700;"><?= htmlspecialchars(substr($cat->category_name, 0, 1)); ?></span>
          <?php endif; ?>
        </div>
        <div class="gp-cat-name"><?= htmlspecialchars($cat->category_name); ?></div>
        <?php if($itemCount > 0): ?>
        <div class="gp-cat-count"><?= $itemCount; ?> items</div>
        <?php endif; ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php
      endif;
      break;

    case 'featured_products':
      if(!empty($featured_products)):
?>
<div class="gp-section" style="background:var(--gp-cream);">
  <div class="gp-container">
    <div class="gp-section-head">
      <div>
        <div class="gp-section-label">Curated</div>
        <h2 class="gp-section-title">Featured Products</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="gp-section-link">View All &rarr;</a>
    </div>
    <div class="gp-product-grid">
      <?php foreach(array_slice($featured_products, 0, 8) as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $oldPrice = $p->original_price ?? $p->sales_price;
        $hasDiscount = $oldPrice > $price;
        $discountPct = $hasDiscount ? round((($oldPrice - $price) / $oldPrice) * 100) : 0;
        $img = ($p->item_image && file_exists($p->item_image)) ? mp_minified_image_url($p->item_image, 600) : '';
      ?>
      <div class="gp-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, <?= $hasDiscount ? $oldPrice : 0; ?>)">
        <?php if($hasDiscount && $discountPct > 0): ?>
        <span class="gp-product-badge">-<?= $discountPct; ?>%</span>
        <?php endif; ?>
        <div class="gp-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy" decoding="async">
          <?php else: ?>
          <div class="gp-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="gp-product-body">
          <?php if(!empty($p->category_name)): ?>
          <div class="gp-product-brand"><?= htmlspecialchars($p->category_name); ?></div>
          <?php endif; ?>
          <div class="gp-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="gp-product-footer">
            <div class="gp-product-price"><?= sf_currency($price, $cur); ?><?php if($hasDiscount): ?><span class="old"><?= sf_currency($oldPrice, $cur); ?></span><?php endif; ?></div>
            <div class="gp-card-actions">
              <button class="gp-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add to cart"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="gp-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $p->id; ?>,'<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',<?= $p->stock; ?>)" aria-label="Order via WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
            </div>
          </div>
          <?php if($p->stock <= 0 && !($settings->allow_backorder ?? false)): ?>
          <div class="gp-product-stock">Out of Stock</div>
          <?php else: ?>
          <div class="gp-product-stock in">In Stock</div>
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

    case 'featured_services':
      if(!empty($featured_services) && ($settings->allow_services ?? false)):
?>
<div class="gp-section">
  <div class="gp-container">
    <div class="gp-section-head">
      <div>
        <div class="gp-section-label">In-Store</div>
        <h2 class="gp-section-title">Store Services</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/services'); ?>" class="gp-section-link">View All &rarr;</a>
    </div>
    <div class="gp-product-grid">
      <?php foreach(array_slice($featured_services, 0, 4) as $s):
        $sPrice = $s->effective_price ?? $s->sales_price ?? $s->price ?? 0;
        $sImg = (!empty($s->item_image) && file_exists($s->item_image)) ? mp_minified_image_url($s->item_image, 600) : (!empty($s->service_image) && file_exists($s->service_image) ? mp_minified_image_url($s->service_image, 600) : '');
      ?>
      <div class="gp-product-card" onclick="openProductModal(<?= $s->id; ?>, '<?= htmlspecialchars(addslashes($s->item_name ?? $s->service_name ?? '')); ?>', <?= $sPrice; ?>, '<?= $s->item_image ?? $s->service_image ?? ''; ?>', '<?= htmlspecialchars(addslashes($s->description ?? '')); ?>', 999, 0)">
        <div class="gp-product-media">
          <?php if($sImg): ?>
          <img src="<?= $sImg; ?>" alt="<?= htmlspecialchars($s->item_name ?? $s->service_name ?? ''); ?>" loading="lazy" decoding="async">
          <?php else: ?>
          <div class="gp-product-placeholder"><span><?= htmlspecialchars(substr($s->item_name ?? $s->service_name ?? 'S', 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="gp-product-body">
          <div class="gp-product-name"><?= htmlspecialchars($s->item_name ?? $s->service_name ?? ''); ?></div>
          <div class="gp-product-footer">
            <div class="gp-product-price"><?= sf_currency($sPrice, $cur); ?></div>
            <div class="gp-card-actions">
              <button class="gp-add-btn" onclick="event.stopPropagation();addToCart(<?= $s->id; ?>,'service','<?= htmlspecialchars(addslashes($s->item_name ?? $s->service_name ?? '')); ?>',<?= $sPrice; ?>,'<?= $s->item_image ?? $s->service_image ?? ''; ?>',1,999)" aria-label="Add to cart"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="gp-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $s->id; ?>,'<?= htmlspecialchars(addslashes($s->item_name ?? $s->service_name ?? '')); ?>',<?= $sPrice; ?>,'<?= $s->item_image ?? $s->service_image ?? ''; ?>',999)" aria-label="Order via WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
            </div>
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

    case 'best_sellers':
      if(!empty($best_sellers) && count($best_sellers) >= 4):
?>
<div class="gp-section">
  <div class="gp-container">
    <div class="gp-section-head">
      <div>
        <div class="gp-section-label">Popular</div>
        <h2 class="gp-section-title">Best Sellers</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="gp-section-link">View All &rarr;</a>
    </div>
    <div class="gp-product-grid">
      <?php foreach(array_slice($best_sellers, 0, 4) as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $img = ($p->item_image && file_exists($p->item_image)) ? mp_minified_image_url($p->item_image, 600) : '';
      ?>
      <div class="gp-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, 0)">
        <div class="gp-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy" decoding="async">
          <?php else: ?>
          <div class="gp-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="gp-product-body">
          <div class="gp-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="gp-product-footer">
            <div class="gp-product-price"><?= sf_currency($price, $cur); ?></div>
            <div class="gp-card-actions">
              <button class="gp-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add to cart"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="gp-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $p->id; ?>,'<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',<?= $p->stock; ?>)" aria-label="Order via WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
            </div>
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

    case 'new_arrivals':
      if(!empty($new_arrivals) && count($new_arrivals) >= 4):
?>
<div class="gp-section" style="background:var(--gp-cream);">
  <div class="gp-container">
    <div class="gp-section-head">
      <div>
        <div class="gp-section-label">New In</div>
        <h2 class="gp-section-title">New Arrivals</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="gp-section-link">View All &rarr;</a>
    </div>
    <div class="gp-product-grid">
      <?php foreach(array_slice($new_arrivals, 0, 4) as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $img = ($p->item_image && file_exists($p->item_image)) ? mp_minified_image_url($p->item_image, 600) : '';
      ?>
      <div class="gp-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, 0)">
        <span class="gp-product-badge new">New</span>
        <div class="gp-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy" decoding="async">
          <?php else: ?>
          <div class="gp-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="gp-product-body">
          <div class="gp-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="gp-product-footer">
            <div class="gp-product-price"><?= sf_currency($price, $cur); ?></div>
            <div class="gp-card-actions">
              <button class="gp-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add to cart"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="gp-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $p->id; ?>,'<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',<?= $p->stock; ?>)" aria-label="Order via WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
            </div>
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

    case 'brands':
      if(!empty($brands)):
?>
<div class="gp-section">
  <div class="gp-container">
    <div class="gp-section-head" style="justify-content:center;text-align:center;">
      <h2 class="gp-section-title">Brands We Stock</h2>
    </div>
    <div class="gp-brands-grid">
      <?php foreach($brands as $brand): ?>
      <div class="gp-brand"><?= htmlspecialchars($brand->brand_name ?? $brand->name ?? ''); ?></div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php
      endif;
      break;

    case 'testimonials':
      if(!empty($testimonials)):
?>
<div class="gp-section" style="background:var(--gp-cream);">
  <div class="gp-container">
    <div class="gp-section-head">
      <div>
        <div class="gp-section-label">Customer Reviews</div>
        <h2 class="gp-section-title">What Members Say</h2>
      </div>
    </div>
    <div class="gp-testimonials-grid">
      <?php foreach(array_slice($testimonials, 0, 3) as $t): ?>
      <div class="gp-testimonial">
        <div class="gp-testimonial-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
        <div class="gp-testimonial-text"><?= htmlspecialchars($t->testimonial_text ?? $t->message ?? ''); ?></div>
        <div class="gp-testimonial-author"><?= htmlspecialchars($t->customer_name ?? $t->author ?? ''); ?></div>
        <?php if(!empty($t->role ?? $t->customer_role)): ?>
        <div class="gp-testimonial-role"><?= htmlspecialchars($t->role ?? $t->customer_role); ?></div>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php
      endif;
      break;

    case 'instagram_gallery':
      if(!empty($instagram_posts)):
?>
<div class="gp-section">
  <div class="gp-container">
    <div class="gp-section-head" style="justify-content:center;text-align:center;">
      <h2 class="gp-section-title">Follow Us</h2>
    </div>
    <div class="gp-insta-grid">
      <?php foreach(array_slice($instagram_posts, 0, 10) as $post): ?>
      <a href="<?= htmlspecialchars($post->permalink ?? $post->link ?? '#'); ?>" target="_blank" class="gp-insta-item">
        <img src="<?= htmlspecialchars($post->media_url ?? $post->image ?? ''); ?>" alt="Instagram post" loading="lazy" decoding="async">
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php
      endif;
      break;

    case 'store_info':
      break;

    case 'faqs':
      if(!empty($faqs)):
?>
<div class="gp-section">
  <div class="gp-container">
    <div class="gp-section-head" style="justify-content:center;text-align:center;">
      <h2 class="gp-section-title">Frequently Asked Questions</h2>
    </div>
    <div class="gp-faq-list">
      <?php foreach($faqs as $faq): ?>
      <div class="gp-faq-item" onclick="this.classList.toggle('open')">
        <div class="gp-faq-q"><?= htmlspecialchars($faq->question ?? $faq->faq_question ?? ''); ?></div>
        <div class="gp-faq-a"><p><?= htmlspecialchars($faq->answer ?? $faq->faq_answer ?? ''); ?></p></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php
      endif;
      break;

    case 'contact_section':
?>
<div class="gp-section" style="background:var(--gp-cream);">
  <div class="gp-container">
    <div class="gp-section-head" style="justify-content:center;text-align:center;">
      <h2 class="gp-section-title">Visit or Contact Us</h2>
    </div>
    <div class="gp-contact-grid">
      <?php if(!empty($settings->store_phone)): ?>
      <div class="gp-contact-card">
        <div class="gp-contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></div>
        <div class="gp-contact-label">Phone</div>
        <div class="gp-contact-value"><?= htmlspecialchars($settings->store_phone); ?></div>
      </div>
      <?php endif; ?>
      <?php if(!empty($settings->store_email)): ?>
      <div class="gp-contact-card">
        <div class="gp-contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
        <div class="gp-contact-label">Email</div>
        <div class="gp-contact-value"><?= htmlspecialchars($settings->store_email); ?></div>
      </div>
      <?php endif; ?>
      <?php if($waNumber): ?>
      <div class="gp-contact-card">
        <div class="gp-contact-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg></div>
        <div class="gp-contact-label">WhatsApp</div>
        <div class="gp-contact-value"><?= htmlspecialchars($settings->whatsapp_number); ?></div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php
      break;

    case 'whatsapp_cta':
      if($waNumber):
?>
<div class="gp-section">
  <div class="gp-container">
    <div class="gp-wa-cta">
      <div class="gp-wa-cta-title">Personal Shopping Assistance</div>
      <p class="gp-wa-cta-text">Our team is available on WhatsApp to help with product recommendations and delivery scheduling.</p>
      <a href="https://wa.me/<?= $waNumber; ?>" target="_blank" class="gp-btn gp-btn-gold" style="color:var(--gp-dark);">Chat on WhatsApp</a>
    </div>
  </div>
</div>
<?php
      endif;
      break;

    case 'newsletter':
?>
<div class="gp-section" style="background:var(--gp-cream);">
  <div class="gp-container">
    <div class="gp-newsletter">
      <div class="gp-newsletter-title">Join the Grocery Plus Club</div>
      <p class="gp-newsletter-text">Subscribe for weekly premium specials, new arrivals and exclusive member offers.</p>
      <form class="gp-newsletter-form" onsubmit="return mpNewsletterSubmit(event)">
        <input type="email" placeholder="Enter your email" required>
        <button type="submit">Subscribe</button>
      </form>
    </div>
  </div>
</div>
<?php
      break;

    case 'store_hours':
      if(!empty($business_hours)):
?>
<div class="gp-section">
  <div class="gp-container">
    <div class="gp-section-head" style="justify-content:center;text-align:center;">
      <h2 class="gp-section-title">Opening Hours</h2>
    </div>
    <div class="gp-hours-grid">
      <?php foreach($business_hours as $line):
        $line = trim($line);
        if(strpos($line, ':') !== false):
          list($day, $time) = array_map('trim', explode(':', $line, 2)); ?>
      <div class="gp-hours-row">
        <span class="gp-hours-day"><?= htmlspecialchars(ucfirst($day)); ?></span>
        <span class="gp-hours-time"><?= htmlspecialchars($time); ?></span>
      </div>
      <?php else: ?>
      <div class="gp-hours-row">
        <span class="gp-hours-full"><?= htmlspecialchars($line); ?></span>
      </div>
      <?php endif; endforeach; ?>
    </div>
  </div>
</div>
<?php
      endif;
      break;

  endswitch;
endforeach;
?>

<div class="wa-order-modal" id="wa-order-modal">
  <div class="wa-order-modal-overlay" onclick="closeWhatsAppOrderModal()"></div>
  <div class="wa-order-modal-card">
    <div class="wa-order-modal-header">
      <h3 class="wa-order-modal-title">Order via WhatsApp</h3>
      <button class="wa-order-modal-close" onclick="closeWhatsAppOrderModal()" aria-label="Close"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div class="wa-order-modal-body">
      <div class="wa-order-modal-product">
        <img id="wa-modal-product-img" src="" alt="">
        <div class="wa-order-modal-product-info">
          <p class="wa-order-modal-product-name" id="wa-modal-product-name"></p>
          <div class="wa-order-modal-product-price" id="wa-modal-product-price"></div>
        </div>
      </div>
      <div class="wa-order-modal-fields">
        <div><label for="wa-modal-name">Your Name</label><input type="text" id="wa-modal-name" placeholder="Enter your name"></div>
        <div><label for="wa-modal-phone">Phone Number</label><input type="tel" id="wa-modal-phone" placeholder="Enter your phone number"></div>
        <div><label for="wa-modal-qty">Quantity</label><input type="number" id="wa-modal-qty" value="1" min="1"></div>
        <div><label for="wa-modal-note">Note (optional)</label><textarea id="wa-modal-note" placeholder="Any special requests..."></textarea></div>
      </div>
      <div class="wa-order-modal-actions">
        <button class="wa-order-modal-cancel" onclick="closeWhatsAppOrderModal()">Cancel</button>
        <button class="wa-order-modal-send" onclick="sendWhatsAppOrder()"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> Send via WhatsApp</button>
      </div>
    </div>
  </div>
</div>

<script>
let waOrderProduct = null;
function openWhatsAppOrderModal(id, name, price, image, stock){
  waOrderProduct = {id, name, price, image, stock};
  document.getElementById('wa-modal-product-name').textContent = name;
  document.getElementById('wa-modal-product-price').textContent = formatMoney(price);
  if(image) document.getElementById('wa-modal-product-img').src = '<?= base_url(); ?>' + image;
  document.getElementById('wa-modal-qty').value = 1;
  document.getElementById('wa-modal-name').value = '';
  document.getElementById('wa-modal-phone').value = '';
  document.getElementById('wa-modal-note').value = '';
  document.getElementById('wa-order-modal').classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeWhatsAppOrderModal(){
  document.getElementById('wa-order-modal').classList.remove('open');
  document.body.style.overflow = '';
}
function sendWhatsAppOrder(){
  if(!waOrderProduct) return;
  const name = document.getElementById('wa-modal-name').value.trim();
  const phone = document.getElementById('wa-modal-phone').value.trim();
  const qty = parseInt(document.getElementById('wa-modal-qty').value) || 1;
  const note = document.getElementById('wa-modal-note').value.trim();
  if(!name || !phone){ showToast('Please enter your name and phone number'); return; }
  const storeName = '<?= htmlspecialchars(addslashes($store->store_name ?? 'Grocery Plus')); ?>';
  const waNumber = '<?= preg_replace("/[^0-9]/", "", $settings->whatsapp_number ?? ""); ?>';
  if(!waNumber){ showToast('WhatsApp ordering is not available'); return; }
  let msg = 'Hello, I would like to order from ' + storeName;
  msg += '\n\nProduct: ' + waOrderProduct.name;
  msg += '\nQuantity: ' + qty;
  msg += '\nPrice: ' + formatMoney(waOrderProduct.price * qty);
  msg += '\n\nMy Details:\nName: ' + name + '\nPhone: ' + phone;
  if(note) msg += '\nNote: ' + note;
  msg += '\n\nThank you.';
  window.open('https://wa.me/' + waNumber + '?text=' + encodeURIComponent(msg), '_blank');
  closeWhatsAppOrderModal();
  showToast('Opening WhatsApp with your order details...');
}
</script>
