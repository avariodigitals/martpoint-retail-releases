<?php
/**
 * Fashion Luxe — Fashion Homepage
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
    --fl-ink:#1A1A1A;
    --fl-warm:#8B6D4B;
    --fl-gold:#C9A961;
    --fl-ivory:#F9F7F2;
    --fl-soft:#E8E4DC;
  }

  .theme-fashion_luxe .mp-topbar,
  .theme-fashion_luxe .mp-announcement,
  .theme-fashion_luxe .mp-nav,
  .theme-fashion_luxe .mp-header,
  .theme-fashion_luxe .mp-mobile-menu-btn,
  .theme-fashion_luxe .mp-footer-space { display:none !important; }

  /* Fashion homepage styles */
  .fl-container { max-width:1400px; margin:0 auto; padding:0 24px; }
  @media(max-width:767px){ .fl-container { padding:0 16px; } }

  /* Hero */
  .fl-hero { position:relative; background:var(--fl-ink); color:#fff; overflow:hidden; min-height:600px; display:flex; align-items:center; }
  @media(max-width:767px){ .fl-hero { min-height:460px; } }
  .fl-hero-media { position:absolute; inset:0; opacity:0.5; }
  .fl-hero-media img { width:100%; height:100%; object-fit:cover; }
  .fl-hero-gradient { position:absolute; inset:0; background:linear-gradient(90deg, rgba(61,40,23,0.88) 0%, rgba(61,40,23,0.55) 55%, rgba(61,40,23,0.15) 100%); }
  .fl-hero-content { position:relative; z-index:2; padding:110px 24px 130px; max-width:1400px; margin:0 auto; width:100%; }
  @media(max-width:767px){ .fl-hero-content { padding:70px 16px 90px; } }
  .fl-hero-kicker { font-family:'Lora',serif; font-size:11px; text-transform:uppercase; letter-spacing:0.18em; color:var(--fl-gold); margin-bottom:20px; }
  .fl-hero-title { font-family:'Playfair Display',serif; font-style:italic; font-size:clamp(38px,5.5vw,66px); line-height:1.08; font-weight:700; margin:0 0 22px; max-width:700px; letter-spacing:-0.01em; }
  .fl-hero-lead { font-family:'Lora',serif; font-size:clamp(15px,2vw,18px); line-height:1.7; opacity:0.92; max-width:500px; margin-bottom:36px; }
  .fl-hero-actions { display:flex; gap:14px; flex-wrap:wrap; }

  /* Buttons */
  .fl-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:15px 34px; border-radius:2px; font-family:'Lora',serif; font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; transition:transform .15s, background .2s, color .2s; cursor:pointer; border:none; text-decoration:none; }
  .fl-btn:active { transform:scale(0.98); }
  .fl-btn-gold { background:var(--fl-gold); color:var(--fl-ink); }
  .fl-btn-gold:hover { background:#B8974F; }
  .fl-btn-ink { background:var(--fl-ink); color:#fff; }
  .fl-btn-ink:hover { background:#8B6D4B; }
  .fl-btn-ghost { background:transparent; color:#fff; border:1px solid rgba(255,255,255,0.4); }
  .fl-btn-ghost:hover { background:rgba(255,255,255,0.1); border-color:#fff; }
  .fl-btn-outline { background:#fff; color:var(--fl-ink); border:1px solid var(--fl-ink); }
  .fl-btn-outline:hover { background:var(--fl-ink); color:#fff; }
  .fl-btn-wa { background:#25D366; color:#fff; }
  .fl-btn-wa:hover { background:#1DA851; }

  /* Sections */
  .fl-section { padding:72px 0; }
  @media(max-width:767px){ .fl-section { padding:44px 0; } }
  .fl-section-head { display:flex; align-items:flex-end; justify-content:space-between; gap:16px; margin-bottom:36px; }
  .fl-section-label { font-family:'Lora',serif; font-size:11px; text-transform:uppercase; letter-spacing:0.18em; color:var(--fl-gold); font-weight:600; margin-bottom:8px; }
  .fl-section-title { font-family:'Playfair Display',serif; font-style:italic; font-size:32px; margin:0; font-weight:700; color:var(--fl-ink); }
  .fl-section-link { font-family:'Lora',serif; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; border-bottom:1px solid var(--fl-ink); padding-bottom:2px; transition:color .2s, border-color .2s; color:var(--fl-ink); text-decoration:none; }
  .fl-section-link:hover { color:var(--fl-gold); border-color:var(--fl-gold); }

  /* Product grid */
  .fl-product-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:24px; }
  @media(max-width:1023px){ .fl-product-grid { grid-template-columns:repeat(3,1fr); } }
  @media(max-width:767px){ .fl-product-grid { grid-template-columns:repeat(2,1fr); gap:14px; } }
  .fl-product-card { position:relative; background:#fff; border-radius:4px; overflow:hidden; border:1px solid var(--fl-soft); transition:transform .25s, box-shadow .25s; cursor:pointer; }
  .fl-product-card:hover { transform:translateY(-4px); box-shadow:0 12px 36px rgba(61,40,23,0.12); }
  .fl-product-wishlist { position:absolute; top:14px; right:14px; width:36px; height:36px; border-radius:4px; background:#fff; display:flex; align-items:center; justify-content:center; z-index:2; transition:color .2s, transform .2s; box-shadow:0 2px 8px rgba(0,0,0,0.06); border:none; cursor:pointer; }
  .fl-product-wishlist:hover { color:var(--fl-gold); transform:scale(1.08); }
  .fl-product-badge { position:absolute; top:14px; left:14px; background:var(--fl-gold); color:var(--fl-ink); font-family:'Lora',serif; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; padding:5px 10px; border-radius:2px; z-index:2; }
  .fl-product-media { aspect-ratio:4/5; overflow:hidden; background:var(--fl-soft); }
  .fl-product-media img { width:100%; height:100%; object-fit:cover; transition:transform .5s ease; }
  .fl-product-card:hover .fl-product-media img { transform:scale(1.06); }
  .fl-product-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--fl-soft); color:var(--fl-gold); }
  .fl-product-placeholder span { font-family:'Playfair Display',serif; font-style:italic; font-size:38px; font-weight:700; }
  .fl-product-body { padding:20px; }
  .fl-product-brand { font-family:'Lora',serif; font-size:11px; text-transform:uppercase; letter-spacing:0.1em; color:#8B6D4B; font-weight:600; margin-bottom:5px; }
  .fl-product-name { font-family:'Lora',serif; font-size:14px; font-weight:600; margin-bottom:12px; line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; min-height:40px; color:var(--fl-ink); }
  .fl-product-footer { display:flex; flex-direction:column; gap:8px; }
  .fl-product-price { font-family:'Playfair Display',serif; font-size:18px; font-weight:700; color:var(--fl-ink); }
  .fl-product-price .old { font-family:'Lora',serif; font-size:13px; color:#8B6D4B; text-decoration:line-through; margin-left:6px; font-weight:500; }
  .fl-card-actions { display:flex; gap:8px; }
  .fl-add-btn { flex:1; padding:11px 14px; border-radius:2px; background:var(--fl-ink); color:#fff; display:flex; align-items:center; justify-content:center; gap:6px; transition:background .2s, transform .15s; border:none; cursor:pointer; font-family:'Lora',serif; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; }
  .fl-add-btn:hover { background:var(--fl-gold); color:var(--fl-ink); }
  .fl-add-btn:active { transform:scale(0.97); }
  .fl-wa-btn { flex:1; padding:11px 14px; border-radius:2px; background:#25D366; color:#fff; display:flex; align-items:center; justify-content:center; gap:6px; transition:background .2s, transform .15s; border:none; cursor:pointer; font-family:'Lora',serif; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; }
  .fl-wa-btn:hover { background:#1FB855; }
  .fl-wa-btn:active { transform:scale(0.97); }
  .fl-product-stock { font-family:'Lora',serif; font-size:11px; color:#B23A3A; font-weight:600; margin-top:8px; text-transform:uppercase; letter-spacing:0.06em; }

  /* Category cards — stylish elegant */
  .fl-cat-grid { display:grid; grid-template-columns:repeat(6,1fr); gap:16px; }
  @media(max-width:1023px){ .fl-cat-grid { grid-template-columns:repeat(4,1fr); } }
  @media(max-width:767px){ .fl-cat-grid { grid-template-columns:repeat(3,1fr); gap:12px; } }
  .fl-cat-card { position:relative; border-radius:4px; overflow:hidden; aspect-ratio:3/2; text-decoration:none; color:inherit; display:block; transition:transform .25s, box-shadow .25s; }
  .fl-cat-card:hover { transform:translateY(-4px); box-shadow:0 12px 36px rgba(61,40,23,0.15); }
  .fl-cat-card-media { position:absolute; inset:0; background:var(--fl-soft); }
  .fl-cat-card-media img { width:100%; height:100%; object-fit:cover; transition:transform .5s ease; }
  .fl-cat-card:hover .fl-cat-card-media img { transform:scale(1.08); }
  .fl-cat-card-overlay { position:absolute; inset:0; background:linear-gradient(180deg, transparent 40%, rgba(61,40,23,0.8) 100%); }
  .fl-cat-card-body { position:absolute; bottom:0; left:0; right:0; padding:20px; z-index:2; }
  .fl-cat-card-name { font-family:'Playfair Display',serif; font-style:italic; font-size:16px; font-weight:700; color:#fff; margin-bottom:4px; }
  .fl-cat-card-count { font-family:'Lora',serif; font-size:12px; color:var(--fl-gold); }
  .fl-cat-card-arrow { position:absolute; top:16px; right:16px; width:32px; height:32px; border-radius:2px; background:var(--fl-gold); display:flex; align-items:center; justify-content:center; opacity:0; transform:translateY(-4px); transition:opacity .25s, transform .25s; }
  .fl-cat-card:hover .fl-cat-card-arrow { opacity:1; transform:translateY(0); }
  .fl-cat-card-arrow svg { width:16px; height:16px; color:var(--fl-ink); }
  .fl-cat-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--fl-soft); }
  .fl-cat-placeholder span { font-family:'Playfair Display',serif; font-style:italic; font-size:42px; font-weight:700; color:var(--fl-gold); }

  /* Trust badges / values */
  .fl-values { background:var(--fl-soft); padding:48px 0; }
  .fl-values-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:24px; }
  @media(max-width:767px){ .fl-values-grid { grid-template-columns:1fr; } }
  .fl-value { text-align:center; padding:16px; }
  .fl-value-icon { width:56px; height:56px; border-radius:50%; background:rgba(201,169,97,0.12); display:flex; align-items:center; justify-content:center; font-size:24px; margin:0 auto 16px; color:var(--fl-gold); }
  .fl-value-title { font-family:'Playfair Display',serif; font-style:italic; font-size:16px; font-weight:700; color:var(--fl-ink); margin-bottom:8px; }
  .fl-value-text { font-family:'Lora',serif; font-size:14px; color:#8B6D4B; line-height:1.6; }

  /* Promo banner */
  .fl-promo { background:var(--fl-ivory); border-radius:4px; overflow:hidden; display:flex; align-items:center; min-height:200px; border:1px solid var(--fl-soft); }
  @media(max-width:767px){ .fl-promo { flex-direction:column; min-height:auto; } }
  .fl-promo-media { flex:1; min-height:200px; background:var(--fl-soft); }
  .fl-promo-media img { width:100%; height:100%; object-fit:cover; }
  .fl-promo-body { flex:1; padding:40px; }
  @media(max-width:767px){ .fl-promo-body { padding:24px; } }
  .fl-promo-kicker { font-family:'Lora',serif; font-size:11px; text-transform:uppercase; letter-spacing:0.18em; color:var(--fl-gold); font-weight:600; margin-bottom:8px; }
  .fl-promo-title { font-family:'Playfair Display',serif; font-style:italic; font-size:clamp(22px,3vw,32px); font-weight:700; color:var(--fl-ink); margin-bottom:10px; }
  .fl-promo-text { font-family:'Lora',serif; font-size:15px; color:#8B6D4B; line-height:1.7; margin-bottom:20px; }

  /* WhatsApp CTA */
  .fl-wa-cta { background:linear-gradient(135deg, #25D366, #1DA851); color:#fff; padding:48px 24px; text-align:center; border-radius:4px; }
  .fl-wa-cta-title { font-family:'Playfair Display',serif; font-style:italic; font-size:clamp(22px,3vw,30px); font-weight:700; margin-bottom:10px; }
  .fl-wa-cta-text { font-family:'Lora',serif; opacity:0.9; max-width:440px; margin:0 auto 24px; font-size:15px; line-height:1.7; }
  .fl-wa-cta .fl-btn { background:#fff; color:#1DA851; }
  .fl-wa-cta .fl-btn:hover { background:var(--fl-ink); color:#fff; }

  /* Store info */
  .fl-store-info { background:var(--fl-ivory); padding:48px 0; }
  .fl-store-info-grid { display:grid; grid-template-columns:1fr 1fr; gap:32px; align-items:center; }
  @media(max-width:767px){ .fl-store-info-grid { grid-template-columns:1fr; } }
  .fl-store-info-title { font-family:'Playfair Display',serif; font-style:italic; font-size:clamp(24px,3vw,34px); font-weight:700; color:var(--fl-ink); margin-bottom:14px; }
  .fl-store-info-text { font-family:'Lora',serif; font-size:15px; color:#8B6D4B; line-height:1.7; margin-bottom:20px; }
  .fl-store-info-detail { display:flex; align-items:center; gap:10px; margin-bottom:10px; font-family:'Lora',serif; font-size:14px; color:var(--fl-ink); }
  .fl-store-info-detail svg { width:18px; height:18px; color:var(--fl-gold); flex-shrink:0; }

  /* Newsletter */
  .fl-newsletter { background:var(--fl-ink); color:#fff; padding:48px 24px; text-align:center; border-radius:4px; }
  .fl-newsletter-title { font-family:'Playfair Display',serif; font-style:italic; font-size:clamp(22px,3vw,30px); font-weight:700; margin-bottom:10px; }
  .fl-newsletter-text { font-family:'Lora',serif; opacity:0.8; max-width:440px; margin:0 auto 24px; font-size:15px; }
  .fl-newsletter-form { display:flex; gap:10px; max-width:440px; margin:0 auto; }
  .fl-newsletter-form input { flex:1; padding:14px 18px; border:none; border-radius:2px; font-family:'Lora',serif; font-size:14px; outline:none; }
  .fl-newsletter-form button { padding:14px 24px; border:none; border-radius:2px; background:var(--fl-gold); color:var(--fl-ink); font-family:'Lora',serif; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; cursor:pointer; font-size:13px; }

  /* Testimonials */
  .fl-testimonials-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; }
  @media(max-width:767px){ .fl-testimonials-grid { grid-template-columns:1fr; } }
  .fl-testimonial { background:#fff; border:1px solid var(--fl-soft); border-radius:4px; padding:24px; }
  .fl-testimonial-stars { color:var(--fl-gold); margin-bottom:12px; font-size:16px; }
  .fl-testimonial-text { font-family:'Lora',serif; font-size:14px; color:#8B6D4B; line-height:1.7; margin-bottom:16px; }
  .fl-testimonial-author { font-family:'Playfair Display',serif; font-style:italic; font-size:13px; font-weight:700; color:var(--fl-ink); }

  /* Brands */
  .fl-brands-grid { display:flex; flex-wrap:wrap; gap:20px; align-items:center; justify-content:center; }
  .fl-brand { padding:12px 24px; background:#fff; border:1px solid var(--fl-soft); border-radius:2px; font-family:'Lora',serif; font-size:14px; font-weight:600; color:var(--fl-ink); }

  /* Instagram */
  .fl-insta-grid { display:grid; grid-template-columns:repeat(5,1fr); gap:12px; }
  @media(max-width:767px){ .fl-insta-grid { grid-template-columns:repeat(3,1fr); } }
  .fl-insta-item { aspect-ratio:1; border-radius:4px; overflow:hidden; position:relative; }
  .fl-insta-item img { width:100%; height:100%; object-fit:cover; transition:transform .3s; }
  .fl-insta-item:hover img { transform:scale(1.08); }

  /* FAQs */
  .fl-faq-list { max-width:760px; margin:0 auto; }
  .fl-faq-item { background:#fff; border:1px solid var(--fl-soft); border-radius:4px; margin-bottom:10px; overflow:hidden; }
  .fl-faq-q { padding:18px 24px; font-family:'Lora',serif; font-size:15px; font-weight:600; color:var(--fl-ink); cursor:pointer; display:flex; justify-content:space-between; align-items:center; }
  .fl-faq-q::after { content:'+'; font-size:20px; color:var(--fl-gold); }
  .fl-faq-item.open .fl-faq-q::after { content:'\2212'; }
  .fl-faq-a { max-height:0; overflow:hidden; transition:max-height .3s ease; padding:0 24px; }
  .fl-faq-item.open .fl-faq-a { max-height:300px; padding:0 24px 18px; }
  .fl-faq-a p { font-family:'Lora',serif; font-size:14px; color:#8B6D4B; line-height:1.7; margin:0; }

  /* Contact */
  .fl-contact-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; }
  @media(max-width:767px){ .fl-contact-grid { grid-template-columns:1fr; } }
  .fl-contact-card { background:#fff; border:1px solid var(--fl-soft); border-radius:4px; padding:24px; text-align:center; }
  .fl-contact-icon { width:48px; height:48px; border-radius:50%; background:rgba(201,169,97,0.12); display:flex; align-items:center; justify-content:center; margin:0 auto 14px; color:var(--fl-gold); }
  .fl-contact-icon svg { width:22px; height:22px; }
  .fl-contact-label { font-family:'Lora',serif; font-size:11px; text-transform:uppercase; letter-spacing:0.12em; color:var(--fl-gold); font-weight:600; margin-bottom:4px; }
  .fl-contact-value { font-family:'Lora',serif; font-size:15px; font-weight:600; color:var(--fl-ink); }

  /* Store hours */
  .fl-hours-grid { max-width:520px; margin:0 auto; }
  .fl-hours-row { display:flex; justify-content:space-between; padding:12px 0; border-bottom:1px solid var(--fl-soft); font-family:'Lora',serif; font-size:14px; }
  .fl-hours-row:last-child { border-bottom:none; }
  .fl-hours-day { color:var(--fl-ink); font-weight:600; }
  .fl-hours-time { color:#8B6D4B; }

  /* WhatsApp order modal */
  .wa-order-modal { display:none; position:fixed; inset:0; z-index:9999; }
  .wa-order-modal.open { display:block; }
  .wa-order-modal-overlay { position:absolute; inset:0; background:rgba(61,40,23,0.5); }
  .wa-order-modal-card { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:440px; max-width:calc(100vw - 32px); max-height:90vh; overflow-y:auto; background:#fff; border-radius:4px; box-shadow:0 24px 60px rgba(61,40,23,0.25); border:1px solid var(--fl-soft); }
  .wa-order-modal-header { display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid var(--fl-soft); }
  .wa-order-modal-title { font-family:'Playfair Display',serif; font-style:italic; font-size:20px; font-weight:700; color:var(--fl-ink); margin:0; }
  .wa-order-modal-close { width:32px; height:32px; border-radius:2px; border:none; background:var(--fl-soft); color:var(--fl-ink); cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background .2s; }
  .wa-order-modal-close:hover { background:#F9F7F2; }
  .wa-order-modal-body { padding:24px; }
  .wa-order-modal-product { display:flex; gap:14px; align-items:center; padding:14px; background:var(--fl-ivory); border-radius:4px; margin-bottom:20px; }
  .wa-order-modal-product img { width:56px; height:56px; border-radius:4px; object-fit:cover; flex-shrink:0; }
  .wa-order-modal-product-info { flex:1; min-width:0; }
  .wa-order-modal-product-name { font-family:'Lora',serif; font-size:14px; font-weight:600; color:var(--fl-ink); margin:0 0 4px; line-height:1.3; }
  .wa-order-modal-product-price { font-family:'Playfair Display',serif; font-size:16px; font-weight:700; color:var(--fl-gold); }
  .wa-order-modal-fields { display:flex; flex-direction:column; gap:14px; }
  .wa-order-modal-fields label { font-family:'Lora',serif; font-size:11px; text-transform:uppercase; letter-spacing:0.1em; font-weight:600; color:var(--fl-ink); margin-bottom:5px; display:block; }
  .wa-order-modal-fields input, .wa-order-modal-fields textarea { width:100%; padding:12px 14px; border:1px solid var(--fl-soft); border-radius:4px; font-family:'Lora',serif; font-size:14px; background:var(--fl-ivory); outline:none; transition:border-color .2s; color:var(--fl-ink); box-sizing:border-box; }
  .wa-order-modal-fields input:focus, .wa-order-modal-fields textarea:focus { border-color:var(--fl-gold); background:#fff; }
  .wa-order-modal-fields textarea { resize:vertical; min-height:64px; }
  .wa-order-modal-actions { display:flex; gap:10px; margin-top:20px; }
  .wa-order-modal-send { flex:1; padding:14px; border-radius:4px; border:none; background:#25D366; color:#fff; font-family:'Lora',serif; font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; cursor:pointer; transition:background .2s; display:flex; align-items:center; justify-content:center; gap:8px; }
  .wa-order-modal-send:hover { background:#1FB855; }
  .wa-order-modal-cancel { padding:14px 20px; border-radius:4px; border:1px solid var(--fl-soft); background:#fff; color:var(--fl-ink); font-family:'Lora',serif; font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; cursor:pointer; transition:background .2s; }
  .wa-order-modal-cancel:hover { background:var(--fl-soft); }
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
<div class="fl-hero">
  <div class="fl-hero-media"><img src="<?= $heroImg; ?>" alt="<?= htmlspecialchars($hero->banner_title ?? ($store->store_name ?? 'Store')); ?>"></div>
  <div class="fl-hero-gradient"></div>
  <div class="fl-hero-content">
    <p class="fl-hero-kicker"><?= htmlspecialchars($hero->banner_subtitle ?? 'New Collection'); ?></p>
    <h1 class="fl-hero-title"><?= htmlspecialchars($hero->banner_title ?? ($settings->store_headline ?: ($store->store_name ?? 'Welcome'))); ?></h1>
    <p class="fl-hero-lead"><?= htmlspecialchars($settings->store_subheadline ?: 'Curated pieces for the discerning wardrobe. Fine fabrics, timeless silhouettes, and statement essentials.'); ?></p>
    <div class="fl-hero-actions">
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="fl-btn fl-btn-gold">Shop the Collection</a>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="fl-btn fl-btn-ghost">View Lookbook</a>
    </div>
  </div>
</div>
<?php else: ?>
<div class="fl-hero">
  <div class="fl-hero-gradient"></div>
  <div class="fl-hero-content">
    <p class="fl-hero-kicker"><?= htmlspecialchars($settings->store_subheadline ?: 'Welcome'); ?></p>
    <h1 class="fl-hero-title"><?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Store')); ?></h1>
    <p class="fl-hero-lead"><?= htmlspecialchars($settings->store_description ?: 'Discover our curated collection of refined pieces.'); ?></p>
    <div class="fl-hero-actions">
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="fl-btn fl-btn-gold">Shop Now</a>
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
      $flBadges = json_decode($settings->trust_badges_json ?? '', true);
      if(empty($flBadges) || !is_array($flBadges)){
        $flBadges = [
          ['icon' => '&#10024;', 'title' => 'Premium Quality', 'desc' => 'Carefully selected fabrics and finishes for lasting comfort.'],
          ['icon' => '&#128230;', 'title' => 'Fast Delivery', 'desc' => 'Quick and reliable shipping to your doorstep.'],
          ['icon' => '&#129309;', 'title' => 'Personal Service', 'desc' => 'Reach us anytime on WhatsApp for styling help and orders.']
        ];
      }
?>
<!-- TRUST BADGES -->
<div class="fl-values">
  <div class="fl-container">
    <div class="fl-values-grid">
      <?php foreach(array_slice($flBadges, 0, 3) as $b): ?>
      <div class="fl-value">
        <div class="fl-value-icon"><?= !empty($b['icon']) ? $b['icon'] : '&#10024;'; ?></div>
        <div class="fl-value-title"><?= htmlspecialchars($b['title'] ?? ''); ?></div>
        <div class="fl-value-text"><?= htmlspecialchars($b['desc'] ?? ''); ?></div>
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
<div class="fl-section">
  <div class="fl-container">
    <div class="fl-promo">
      <?php if($promoImg): ?>
      <div class="fl-promo-media"><img src="<?= $promoImg; ?>" alt="<?= htmlspecialchars($promo->banner_title ?? 'Promo'); ?>"></div>
      <?php endif; ?>
      <div class="fl-promo-body">
        <div class="fl-promo-kicker"><?= htmlspecialchars($promo->banner_subtitle ?? 'Limited Time'); ?></div>
        <h3 class="fl-promo-title"><?= htmlspecialchars($promo->banner_title ?? 'Special Offer'); ?></h3>
        <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="fl-btn fl-btn-ink">Shop Now</a>
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
<div class="fl-section" style="background:var(--fl-ivory);">
  <div class="fl-container">
    <div class="fl-section-head">
      <div>
        <div class="fl-section-label">Browse</div>
        <h2 class="fl-section-title">Shop by Category</h2>
      </div>
    </div>
    <div class="fl-cat-grid">
      <?php foreach(array_slice($categories, 0, 8) as $cat):
        $catImg = (!empty($cat->category_image) && file_exists($cat->category_image)) ? base_url($cat->category_image) : '';
        $itemCount = $cat->item_count ?? 0;
      ?>
      <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="fl-cat-card">
        <div class="fl-cat-card-media">
          <?php if($catImg): ?>
          <img src="<?= $catImg; ?>" alt="<?= htmlspecialchars($cat->category_name); ?>" loading="lazy">
          <?php else: ?>
          <div class="fl-cat-placeholder"><span><?= htmlspecialchars(substr($cat->category_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="fl-cat-card-overlay"></div>
        <div class="fl-cat-card-arrow">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </div>
        <div class="fl-cat-card-body">
          <div class="fl-cat-card-name"><?= htmlspecialchars($cat->category_name); ?></div>
          <?php if($itemCount > 0): ?>
          <div class="fl-cat-card-count"><?= $itemCount; ?> items</div>
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
<div class="fl-section">
  <div class="fl-container">
    <div class="fl-section-head">
      <div>
        <div class="fl-section-label">Trending Now</div>
        <h2 class="fl-section-title">Featured Collection</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="fl-section-link">Shop All</a>
    </div>
    <div class="fl-product-grid">
      <?php foreach(array_slice($featured_products, 0, 8) as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $oldPrice = $p->original_price ?? $p->sales_price;
        $hasDiscount = $oldPrice > $price;
        $discountPct = $hasDiscount ? round((($oldPrice - $price) / $oldPrice) * 100) : 0;
        $img = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
      ?>
      <div class="fl-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, <?= $hasDiscount ? $oldPrice : 0; ?>)">
        <?php if($hasDiscount && $discountPct > 0): ?>
        <span class="fl-product-badge">-<?= $discountPct; ?>%</span>
        <?php endif; ?>
        <button class="fl-product-wishlist" onclick="event.stopPropagation();"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></button>
        <div class="fl-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
          <?php else: ?>
          <div class="fl-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="fl-product-body">
          <?php if(!empty($p->category_name)): ?>
          <div class="fl-product-brand"><?= htmlspecialchars($p->category_name); ?></div>
          <?php endif; ?>
          <div class="fl-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="fl-product-footer">
            <div class="fl-product-price"><?= sf_currency($price, $cur); ?><?php if($hasDiscount): ?><span class="old"><?= sf_currency($oldPrice, $cur); ?></span><?php endif; ?></div>
            <div class="fl-card-actions">
              <button class="fl-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add to cart"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="fl-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $p->id; ?>,'<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',<?= $p->stock; ?>)" aria-label="Order via WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
            </div>
          </div>
          <?php if($p->stock <= 0 && !($settings->allow_backorder ?? false)): ?>
          <div class="fl-product-stock">Out of Stock</div>
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
<div class="fl-section" style="background:var(--fl-ivory);">
  <div class="fl-container">
    <div class="fl-section-head">
      <div>
        <div class="fl-section-label">Services</div>
        <h2 class="fl-section-title">Our Services</h2>
      </div>
    </div>
    <div class="fl-product-grid">
      <?php foreach(array_slice($featured_services, 0, 4) as $s):
        $sPrice = $s->effective_price ?? $s->sales_price ?? $s->price ?? 0;
        $sImg = (!empty($s->item_image) && file_exists($s->item_image)) ? base_url($s->item_image) : (!empty($s->service_image) && file_exists($s->service_image) ? base_url($s->service_image) : '');
      ?>
      <div class="fl-product-card" onclick="openProductModal(<?= $s->id; ?>, '<?= htmlspecialchars(addslashes($s->item_name ?? $s->service_name ?? '')); ?>', <?= $sPrice; ?>, '<?= $s->item_image ?? $s->service_image ?? ''; ?>', '<?= htmlspecialchars(addslashes($s->description ?? '')); ?>', 999, 0)">
        <div class="fl-product-media">
          <?php if($sImg): ?>
          <img src="<?= $sImg; ?>" alt="<?= htmlspecialchars($s->item_name ?? $s->service_name ?? ''); ?>" loading="lazy">
          <?php else: ?>
          <div class="fl-product-placeholder"><span><?= htmlspecialchars(substr($s->item_name ?? $s->service_name ?? 'S', 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="fl-product-body">
          <div class="fl-product-name"><?= htmlspecialchars($s->item_name ?? $s->service_name ?? ''); ?></div>
          <div class="fl-product-footer">
            <div class="fl-product-price"><?= sf_currency($sPrice, $cur); ?></div>
            <div class="fl-card-actions">
              <button class="fl-add-btn" onclick="event.stopPropagation();addToCart(<?= $s->id; ?>,'service','<?= htmlspecialchars(addslashes($s->item_name ?? $s->service_name ?? '')); ?>',<?= $sPrice; ?>,'<?= $s->item_image ?? $s->service_image ?? ''; ?>',1,999)" aria-label="Add to cart"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="fl-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $s->id; ?>,'<?= htmlspecialchars(addslashes($s->item_name ?? $s->service_name ?? '')); ?>',<?= $sPrice; ?>,'<?= $s->item_image ?? $s->service_image ?? ''; ?>',999)" aria-label="Order via WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
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

    // =====================================================
    // BEST SELLERS
    // =====================================================
    case 'best_sellers':
      if(!empty($best_sellers) && count($best_sellers) >= 4):
?>
<!-- BEST SELLERS -->
<div class="fl-section">
  <div class="fl-container">
    <div class="fl-section-head">
      <div>
        <div class="fl-section-label">Customer Favorites</div>
        <h2 class="fl-section-title">Best Sellers</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="fl-section-link">View All</a>
    </div>
    <div class="fl-product-grid">
      <?php foreach(array_slice($best_sellers, 0, 4) as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $img = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
      ?>
      <div class="fl-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, 0)">
        <div class="fl-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
          <?php else: ?>
          <div class="fl-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="fl-product-body">
          <div class="fl-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="fl-product-footer">
            <div class="fl-product-price"><?= sf_currency($price, $cur); ?></div>
            <div class="fl-card-actions">
              <button class="fl-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add to cart"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="fl-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $p->id; ?>,'<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',<?= $p->stock; ?>)" aria-label="Order via WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
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

    // =====================================================
    // NEW ARRIVALS
    // =====================================================
    case 'new_arrivals':
      if(!empty($new_arrivals) && count($new_arrivals) >= 4):
?>
<!-- NEW ARRIVALS -->
<div class="fl-section" style="background:var(--fl-ivory);">
  <div class="fl-container">
    <div class="fl-section-head">
      <div>
        <div class="fl-section-label">Just In</div>
        <h2 class="fl-section-title">New Arrivals</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="fl-section-link">View All</a>
    </div>
    <div class="fl-product-grid">
      <?php foreach(array_slice($new_arrivals, 0, 4) as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $img = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
      ?>
      <div class="fl-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, 0)">
        <div class="fl-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
          <?php else: ?>
          <div class="fl-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="fl-product-body">
          <div class="fl-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="fl-product-footer">
            <div class="fl-product-price"><?= sf_currency($price, $cur); ?></div>
            <div class="fl-card-actions">
              <button class="fl-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add to cart"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="fl-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $p->id; ?>,'<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',<?= $p->stock; ?>)" aria-label="Order via WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
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

    // =====================================================
    // BRANDS
    // =====================================================
    case 'brands':
      if(!empty($brands)):
?>
<!-- BRANDS -->
<div class="fl-section">
  <div class="fl-container">
    <div class="fl-section-head" style="justify-content:center;text-align:center;">
      <h2 class="fl-section-title">Our Brands</h2>
    </div>
    <div class="fl-brands-grid">
      <?php foreach($brands as $brand): ?>
      <div class="fl-brand"><?= htmlspecialchars($brand->brand_name ?? $brand->name ?? ''); ?></div>
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
<div class="fl-section" style="background:var(--fl-ivory);">
  <div class="fl-container">
    <div class="fl-section-head">
      <div>
        <div class="fl-section-label">Reviews</div>
        <h2 class="fl-section-title">What Customers Say</h2>
      </div>
    </div>
    <div class="fl-testimonials-grid">
      <?php foreach(array_slice($testimonials, 0, 3) as $t): ?>
      <div class="fl-testimonial">
        <div class="fl-testimonial-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
        <div class="fl-testimonial-text"><?= htmlspecialchars($t->testimonial_text ?? $t->message ?? ''); ?></div>
        <div class="fl-testimonial-author"><?= htmlspecialchars($t->customer_name ?? $t->author ?? ''); ?></div>
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
<div class="fl-section">
  <div class="fl-container">
    <div class="fl-section-head" style="justify-content:center;text-align:center;">
      <h2 class="fl-section-title">Follow Us</h2>
    </div>
    <div class="fl-insta-grid">
      <?php foreach(array_slice($instagram_posts, 0, 10) as $post): ?>
      <a href="<?= htmlspecialchars($post->permalink ?? $post->link ?? '#'); ?>" target="_blank" class="fl-insta-item">
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
<div class="fl-store-info">
  <div class="fl-container">
    <div class="fl-store-info-grid">
      <div>
        <div class="fl-section-label">About Us</div>
        <h2 class="fl-store-info-title"><?= htmlspecialchars($store->store_name ?? 'Our Store'); ?></h2>
        <p class="fl-store-info-text"><?= htmlspecialchars($settings->store_description ?? 'We are committed to bringing you the best products with exceptional service.'); ?></p>
        <?php if(!empty($settings->store_phone)): ?>
        <div class="fl-store-info-detail">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
          <?= htmlspecialchars($settings->store_phone); ?>
        </div>
        <?php endif; ?>
        <?php if(!empty($settings->store_email)): ?>
        <div class="fl-store-info-detail">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
          <?= htmlspecialchars($settings->store_email); ?>
        </div>
        <?php endif; ?>
        <?php if(!empty($settings->store_address)): ?>
        <div class="fl-store-info-detail">
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
<div class="fl-section">
  <div class="fl-container">
    <div class="fl-section-head" style="justify-content:center;text-align:center;">
      <h2 class="fl-section-title">Frequently Asked Questions</h2>
    </div>
    <div class="fl-faq-list">
      <?php foreach($faqs as $faq): ?>
      <div class="fl-faq-item" onclick="this.classList.toggle('open')">
        <div class="fl-faq-q"><?= htmlspecialchars($faq->question ?? $faq->faq_question ?? ''); ?></div>
        <div class="fl-faq-a"><p><?= htmlspecialchars($faq->answer ?? $faq->faq_answer ?? ''); ?></p></div>
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
<div class="fl-section" style="background:var(--fl-ivory);">
  <div class="fl-container">
    <div class="fl-section-head" style="justify-content:center;text-align:center;">
      <h2 class="fl-section-title">Get In Touch</h2>
    </div>
    <div class="fl-contact-grid">
      <?php if(!empty($settings->store_phone)): ?>
      <div class="fl-contact-card">
        <div class="fl-contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></div>
        <div class="fl-contact-label">Phone</div>
        <div class="fl-contact-value"><?= htmlspecialchars($settings->store_phone); ?></div>
      </div>
      <?php endif; ?>
      <?php if(!empty($settings->store_email)): ?>
      <div class="fl-contact-card">
        <div class="fl-contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
        <div class="fl-contact-label">Email</div>
        <div class="fl-contact-value"><?= htmlspecialchars($settings->store_email); ?></div>
      </div>
      <?php endif; ?>
      <?php if($waNumber): ?>
      <div class="fl-contact-card">
        <div class="fl-contact-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg></div>
        <div class="fl-contact-label">WhatsApp</div>
        <div class="fl-contact-value"><?= htmlspecialchars($settings->whatsapp_number); ?></div>
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
<div class="fl-section">
  <div class="fl-container">
    <div class="fl-wa-cta">
      <div class="fl-wa-cta-title">Need Help? Chat With Us</div>
      <p class="fl-wa-cta-text">Have a question about a product or your order? Our team is ready to help you on WhatsApp.</p>
      <a href="https://wa.me/<?= $waNumber; ?>" target="_blank" class="fl-btn">Start WhatsApp Chat</a>
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
<div class="fl-section">
  <div class="fl-container">
    <div class="fl-newsletter">
      <div class="fl-newsletter-title">Stay In The Know</div>
      <p class="fl-newsletter-text">Subscribe to get updates on new arrivals, exclusive offers and more.</p>
      <form class="fl-newsletter-form" onsubmit="event.preventDefault();showToast('Thank you for subscribing!');this.reset();">
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
<div class="fl-section" style="background:var(--fl-ivory);">
  <div class="fl-container">
    <div class="fl-section-head" style="justify-content:center;text-align:center;">
      <h2 class="fl-section-title">Opening Hours</h2>
    </div>
    <div class="fl-hours-grid">
      <?php foreach($business_hours as $day => $hours): ?>
      <div class="fl-hours-row">
        <span class="fl-hours-day"><?= htmlspecialchars(ucfirst($day)); ?></span>
        <span class="fl-hours-time"><?= htmlspecialchars($hours); ?></span>
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
<style>
  /* Fashion Luxe premium overrides */
  .fl-hero { min-height:85vh; justify-content:center; }
  .fl-hero-media { opacity:1; }
  .fl-hero-gradient { background:linear-gradient(180deg, rgba(26,26,26,0.35) 0%, rgba(26,26,26,0.62) 100%); }
  .fl-hero-content { max-width:1200px; text-align:center; margin:0 auto; display:flex; flex-direction:column; align-items:center; padding:140px 24px; }
  .fl-hero-kicker { letter-spacing:0.22em; }
  .fl-hero-title { max-width:900px; margin:0 auto 22px; }
  .fl-hero-lead { max-width:620px; margin:0 auto 36px; }
  .fl-product-card { background:#fff; border:1px solid var(--fl-soft); border-radius:2px; }
  .fl-product-card:hover { transform:translateY(-6px); box-shadow:0 20px 50px rgba(26,26,26,0.12); }
  .fl-product-body { padding:24px; }
  .fl-product-media { aspect-ratio:4/5; }
  .fl-cat-grid { grid-template-columns:repeat(4,1fr); gap:20px; }
  .fl-cat-card { aspect-ratio:1/1; }
  .fl-cat-card-overlay { background:linear-gradient(180deg, transparent 40%, rgba(26,26,26,0.78) 100%); }
  .fl-section { padding:88px 0; }
  .fl-section-title { font-size:36px; }
  .fl-newsletter { background:var(--fl-ink); color:var(--fl-ivory); border-radius:0; }
  .fl-newsletter-title { color:var(--fl-ivory); }
  .fl-newsletter-text { color:var(--fl-ivory); opacity:0.85; }
  .fl-newsletter-form input { background:var(--fl-ivory); color:var(--fl-ink); }
  .fl-newsletter-form button { background:var(--fl-gold); color:var(--fl-ink); }
  .fl-newsletter-form button:hover { background:#B8974F; }
  .fl-btn-ink:hover { background:var(--fl-gold); color:var(--fl-ink); }
  .fl-btn-outline:hover { background:var(--fl-gold); color:var(--fl-ink); border-color:var(--fl-gold); }
  .flh-header { background:var(--fl-ivory); border-bottom:1px solid var(--fl-soft); }
  .flh-header-inner { padding:22px 24px; }
  .flh-logo img { max-height:52px; max-width:220px; }
  .flh-logo-text { font-size:30px; }
  .flh-nav-link:hover { color:var(--fl-gold); }
  .flh-icon-btn:hover { color:var(--fl-gold); background:var(--fl-soft); }
</style>

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
        <div>
          <label for="wa-modal-name">Your Name</label>
          <input type="text" id="wa-modal-name" placeholder="Enter your name">
        </div>
        <div>
          <label for="wa-modal-phone">Phone Number</label>
          <input type="tel" id="wa-modal-phone" placeholder="Enter your phone number">
        </div>
        <div>
          <label for="wa-modal-qty">Quantity</label>
          <input type="number" id="wa-modal-qty" value="1" min="1">
        </div>
        <div>
          <label for="wa-modal-note">Note (optional)</label>
          <textarea id="wa-modal-note" placeholder="Any special requests..."></textarea>
        </div>
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
  const storeName = '<?= htmlspecialchars(addslashes($store->store_name ?? 'Store')); ?>';
  const waNumber = '<?= preg_replace("/[^0-9]/", "", $settings->whatsapp_number ?? ""); ?>';
  if(!waNumber){ showToast('WhatsApp ordering is not available'); return; }
  let msg = 'Hello, I would like to order from ' + storeName;
  msg += '\n\nProduct: ' + waOrderProduct.name;
  msg += '\nQuantity: ' + qty;
  msg += '\nPrice: ' + formatMoney(waOrderProduct.price * qty);
  msg += '\n\nMy Details:';
  msg += '\nName: ' + name;
  msg += '\nPhone: ' + phone;
  if(note) msg += '\nNote: ' + note;
  msg += '\n\nThank you.';
  window.open('https://wa.me/' + waNumber + '?text=' + encodeURIComponent(msg), '_blank');
  closeWhatsAppOrderModal();
  showToast('Opening WhatsApp with your order details...');
}
</script>
