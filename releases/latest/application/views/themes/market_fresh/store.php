<?php
/**
 * Market Fresh — Supermarket Homepage
 * Vibrant produce-forward design with fresh green and warm orange accents.
 * Clean Inter typography, category tiles, deal cards, quick-add product grid.
 * Respects the homepage builder section order and visibility.
 * Uses mp_minified_image_url() for all imagery (on-demand resize + cache).
 */
$slug = $settings->store_slug ?? '';
$cur = $store_currency ?? null;

// Sort homepage sections by display_order
$orderedSections = [];
if(!empty($homepage_sections)){
  $orderedSections = $homepage_sections;
  uasort($orderedSections, function($a, $b){ return ($a->display_order ?? 0) <=> ($b->display_order ?? 0); });
}

// Hero banner (minified for speed)
$hero = !empty($hero_banners) ? $hero_banners[0] : null;
$heroImg = '';
if($hero && $hero->desktop_image) $heroImg = mp_minified_image_url($hero->desktop_image, 1600);
elseif($hero && $hero->mobile_image) $heroImg = mp_minified_image_url($hero->mobile_image, 900);

// WhatsApp number
$waNumber = preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? '');
?>
<style>
  :root {
    --mf-green:#16A34A;
    --mf-green-dark:#15803D;
    --mf-green-deep:#14532D;
    --mf-orange:#F97316;
    --mf-orange-dark:#EA580C;
    --mf-cream:#F0FDF4;
    --mf-soft:#DCFCE7;
    --mf-dark:#1C2B25;
    --mf-gray:#5B6B63;
    --mf-border:#E3EDE7;
    --mf-white:#FFFFFF;
  }

  .theme-market_fresh .mp-topbar,
  .theme-market_fresh .mp-announcement,
  .theme-market_fresh .mp-nav,
  .theme-market_fresh .mp-header,
  .theme-market_fresh .mp-mobile-menu-btn,
  .theme-market_fresh .mp-footer-space { display:none !important; }

  .mf-container { max-width:1400px; margin:0 auto; padding:0 24px; }
  @media(max-width:767px){ .mf-container { padding:0 16px; } }

  /* Hero */
  .mf-hero { position:relative; background:linear-gradient(135deg, #14532D 0%, #166534 50%, #15803D 100%); color:#fff; overflow:hidden; min-height:560px; display:flex; align-items:center; }
  @media(max-width:767px){ .mf-hero { min-height:480px; } }
  .mf-hero::before { content:''; position:absolute; inset:0; background-image:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Ccircle cx='30' cy='30' r='4'/%3E%3C/g%3E%3C/svg%3E"); background-size:60px 60px; }
  .mf-hero-media { position:absolute; inset:0; opacity:0.28; }
  .mf-hero-media img { width:100%; height:100%; object-fit:cover; }
  .mf-hero-gradient { position:absolute; inset:0; background:linear-gradient(90deg, rgba(20,83,45,0.85) 0%, rgba(20,83,45,0.5) 55%, rgba(20,83,45,0.1) 100%); }
  .mf-hero-content { position:relative; z-index:2; padding:100px 24px 110px; max-width:1400px; margin:0 auto; width:100%; display:flex; align-items:center; gap:56px; }
  @media(max-width:1023px){ .mf-hero-content { flex-direction:column; gap:36px; padding:72px 24px 88px; } }
  @media(max-width:767px){ .mf-hero-content { padding:56px 16px 72px; } }
  .mf-hero-text { flex:1; max-width:600px; }
  .mf-hero-kicker { font-family:'Inter',sans-serif; font-size:12px; text-transform:uppercase; letter-spacing:0.16em; color:#BBF7D0; margin-bottom:16px; font-weight:600; }
  .mf-hero-title { font-family:'Inter',sans-serif; font-size:clamp(36px,5vw,58px); line-height:1.08; font-weight:800; margin:0 0 18px; letter-spacing:-0.02em; }
  .mf-hero-lead { font-family:'Inter',sans-serif; font-size:clamp(15px,1.8vw,18px); line-height:1.65; opacity:0.95; margin-bottom:30px; max-width:480px; }
  .mf-hero-actions { display:flex; gap:12px; flex-wrap:wrap; }
  .mf-hero-visual { flex:1; max-width:460px; position:relative; }
  .mf-hero-visual img { width:100%; height:380px; object-fit:cover; border-radius:20px; box-shadow:0 24px 60px rgba(0,0,0,0.3); }
  @media(max-width:1023px){ .mf-hero-visual { max-width:100%; } .mf-hero-visual img { height:280px; } }
  @media(max-width:767px){ .mf-hero-visual img { height:220px; } }
  .mf-hero-badge { position:absolute; bottom:-18px; left:-18px; background:#fff; color:var(--mf-dark); padding:14px 18px; border-radius:14px; box-shadow:0 12px 32px rgba(0,0,0,0.2); display:flex; align-items:center; gap:10px; font-family:'Inter',sans-serif; font-size:13px; font-weight:600; }
  .mf-hero-badge svg { width:22px; height:22px; color:var(--mf-orange); flex-shrink:0; }
  @media(max-width:767px){ .mf-hero-badge { left:12px; bottom:-12px; padding:12px 16px; } }

  /* Buttons */
  .mf-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:14px 32px; border-radius:999px; font-family:'Inter',sans-serif; font-size:14px; font-weight:600; transition:transform .15s, background .2s, color .2s; cursor:pointer; border:none; text-decoration:none; }
  .mf-btn:active { transform:scale(0.98); }
  .mf-btn-orange { background:var(--mf-orange); color:#fff; }
  .mf-btn-orange:hover { background:var(--mf-orange-dark); }
  .mf-btn-green { background:var(--mf-green); color:#fff; }
  .mf-btn-green:hover { background:var(--mf-green-dark); }
  .mf-btn-ghost { background:rgba(255,255,255,0.12); color:#fff; border:1.5px solid rgba(255,255,255,0.4); backdrop-filter:blur(4px); }
  .mf-btn-ghost:hover { background:rgba(255,255,255,0.2); }
  .mf-btn-outline { background:#fff; color:var(--mf-green); border:1.5px solid var(--mf-green); }
  .mf-btn-outline:hover { background:var(--mf-green); color:#fff; }
  .mf-btn-wa { background:#25D366; color:#fff; }
  .mf-btn-wa:hover { background:#1DA851; }

  /* Sections */
  .mf-section { padding:64px 0; }
  @media(max-width:767px){ .mf-section { padding:40px 0; } }
  .mf-section-head { display:flex; align-items:flex-end; justify-content:space-between; gap:16px; margin-bottom:32px; }
  .mf-section-label { font-family:'Inter',sans-serif; font-size:12px; text-transform:uppercase; letter-spacing:0.14em; color:var(--mf-orange); font-weight:600; margin-bottom:6px; }
  .mf-section-title { font-family:'Inter',sans-serif; font-size:30px; margin:0; font-weight:800; color:var(--mf-dark); letter-spacing:-0.02em; }
  .mf-section-link { font-family:'Inter',sans-serif; font-size:14px; font-weight:600; color:var(--mf-green); text-decoration:none; transition:color .2s; }
  .mf-section-link:hover { color:var(--mf-green-dark); }

  /* Product grid */
  .mf-product-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:20px; }
  @media(max-width:1023px){ .mf-product-grid { grid-template-columns:repeat(3,1fr); } }
  @media(max-width:767px){ .mf-product-grid { grid-template-columns:repeat(2,1fr); gap:12px; } }
  .mf-product-card { position:relative; background:#fff; border-radius:16px; overflow:hidden; border:1px solid var(--mf-border); transition:transform .25s, box-shadow .25s; cursor:pointer; }
  .mf-product-card:hover { transform:translateY(-6px); box-shadow:0 16px 40px rgba(22,163,74,0.12); }
  .mf-product-wishlist { position:absolute; top:12px; right:12px; width:36px; height:36px; border-radius:999px; background:#fff; display:flex; align-items:center; justify-content:center; z-index:2; transition:color .2s, transform .2s; box-shadow:0 2px 8px rgba(0,0,0,0.06); border:none; cursor:pointer; color:var(--mf-gray); }
  .mf-product-wishlist:hover { color:var(--mf-orange); transform:scale(1.08); }
  .mf-product-badge { position:absolute; top:12px; left:12px; background:var(--mf-orange); color:#fff; font-family:'Inter',sans-serif; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.04em; padding:5px 11px; border-radius:999px; z-index:2; }
  .mf-product-badge.new { background:var(--mf-green); }
  .mf-product-media { aspect-ratio:1/1; overflow:hidden; background:var(--mf-cream); }
  .mf-product-media img { width:100%; height:100%; object-fit:cover; transition:transform .4s ease; }
  .mf-product-card:hover .mf-product-media img { transform:scale(1.05); }
  .mf-product-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--mf-soft); color:var(--mf-green); }
  .mf-product-placeholder span { font-family:'Inter',sans-serif; font-size:38px; font-weight:800; }
  .mf-product-body { padding:16px; }
  .mf-product-brand { font-family:'Inter',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.08em; color:var(--mf-green); font-weight:600; margin-bottom:4px; }
  .mf-product-name { font-family:'Inter',sans-serif; font-size:15px; font-weight:600; margin-bottom:10px; line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; min-height:42px; color:var(--mf-dark); }
  .mf-product-footer { display:flex; flex-direction:column; gap:8px; }
  .mf-product-price { font-family:'Inter',sans-serif; font-size:18px; font-weight:800; color:var(--mf-dark); }
  .mf-product-price .old { font-family:'Inter',sans-serif; font-size:13px; color:var(--mf-gray); text-decoration:line-through; margin-left:6px; font-weight:400; }
  .mf-card-actions { display:flex; flex-direction:column; gap:8px; }
  .mf-add-btn { width:100%; padding:11px 14px; border-radius:999px; background:var(--mf-green); color:#fff; display:flex; align-items:center; justify-content:center; gap:6px; transition:background .2s, transform .15s; border:none; cursor:pointer; font-family:'Inter',sans-serif; font-size:13px; font-weight:600; }
  .mf-add-btn:hover { background:var(--mf-green-dark); }
  .mf-add-btn:active { transform:scale(0.97); }
  .mf-wa-btn { width:100%; padding:11px 14px; border-radius:999px; background:#25D366; color:#fff; display:flex; align-items:center; justify-content:center; gap:6px; transition:background .2s, transform .15s; border:none; cursor:pointer; font-family:'Inter',sans-serif; font-size:13px; font-weight:600; }
  .mf-wa-btn:hover { background:#1FB855; }
  .mf-wa-btn:active { transform:scale(0.97); }
  .mf-product-stock { font-family:'Inter',sans-serif; font-size:11px; color:#DC2626; font-weight:600; margin-top:6px; text-transform:uppercase; letter-spacing:0.04em; }
  .mf-product-stock.in { color:var(--mf-green); }

  /* Category cards — tile style */
  .mf-cat-grid { display:grid; grid-template-columns:repeat(6,1fr); gap:14px; }
  @media(max-width:1023px){ .mf-cat-grid { grid-template-columns:repeat(4,1fr); } }
  @media(max-width:767px){ .mf-cat-grid { grid-template-columns:repeat(3,1fr); gap:10px; } }
  .mf-cat-card { position:relative; border-radius:16px; overflow:hidden; aspect-ratio:1/1; text-decoration:none; color:inherit; display:block; transition:transform .25s, box-shadow .25s; background:#fff; border:1px solid var(--mf-border); }
  .mf-cat-card:hover { transform:translateY(-6px); box-shadow:0 16px 40px rgba(22,163,74,0.14); }
  .mf-cat-card-media { position:absolute; inset:0; background:var(--mf-cream); }
  .mf-cat-card-media img { width:100%; height:100%; object-fit:cover; transition:transform .4s ease; }
  .mf-cat-card:hover .mf-cat-card-media img { transform:scale(1.08); }
  .mf-cat-card-overlay { position:absolute; inset:0; background:linear-gradient(180deg, transparent 35%, rgba(20,43,37,0.8) 100%); }
  .mf-cat-card-body { position:absolute; bottom:0; left:0; right:0; padding:14px; z-index:2; }
  .mf-cat-card-name { font-family:'Inter',sans-serif; font-size:14px; font-weight:700; color:#fff; margin-bottom:2px; }
  .mf-cat-card-count { font-family:'Inter',sans-serif; font-size:11px; color:#BBF7D0; }
  .mf-cat-card-icon { position:absolute; top:12px; right:12px; width:32px; height:32px; border-radius:999px; background:var(--mf-green); display:flex; align-items:center; justify-content:center; opacity:0; transform:translateY(-4px); transition:opacity .25s, transform .25s; }
  .mf-cat-card:hover .mf-cat-card-icon { opacity:1; transform:translateY(0); }
  .mf-cat-card-icon svg { width:16px; height:16px; color:#fff; }
  .mf-cat-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--mf-soft); }
  .mf-cat-placeholder span { font-family:'Inter',sans-serif; font-size:38px; font-weight:800; color:var(--mf-green); }

  /* Trust badges */
  .mf-trust { background:var(--mf-cream); padding:52px 0; }
  .mf-trust-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:18px; }
  @media(max-width:1023px){ .mf-trust-grid { grid-template-columns:repeat(2,1fr); } }
  @media(max-width:767px){ .mf-trust-grid { grid-template-columns:1fr; } }
  .mf-trust-item { text-align:center; padding:24px 16px; background:#fff; border-radius:16px; border:1px solid var(--mf-border); transition:transform .2s; }
  .mf-trust-item:hover { transform:translateY(-4px); }
  .mf-trust-item-icon { width:52px; height:52px; border-radius:999px; background:rgba(22,163,74,0.08); display:flex; align-items:center; justify-content:center; font-size:24px; margin:0 auto 14px; color:var(--mf-green); }
  .mf-trust-item-title { font-family:'Inter',sans-serif; font-size:16px; font-weight:700; color:var(--mf-dark); margin-bottom:6px; }
  .mf-trust-item-text { font-family:'Inter',sans-serif; font-size:13px; color:var(--mf-gray); line-height:1.5; }

  /* Promo banner */
  .mf-promo { background:linear-gradient(135deg, var(--mf-orange) 0%, var(--mf-orange-dark) 100%); border-radius:20px; overflow:hidden; display:flex; align-items:center; min-height:220px; color:#fff; }
  @media(max-width:767px){ .mf-promo { flex-direction:column; min-height:auto; } }
  .mf-promo-media { flex:1; min-height:220px; background:rgba(255,255,255,0.1); }
  .mf-promo-media img { width:100%; height:100%; object-fit:cover; }
  .mf-promo-body { flex:1; padding:40px; }
  @media(max-width:767px){ .mf-promo-body { padding:28px; } }
  .mf-promo-kicker { font-family:'Inter',sans-serif; font-size:12px; text-transform:uppercase; letter-spacing:0.14em; color:#FFE4D1; font-weight:600; margin-bottom:8px; }
  .mf-promo-title { font-family:'Inter',sans-serif; font-size:clamp(22px,2.5vw,30px); font-weight:800; margin-bottom:10px; letter-spacing:-0.02em; }
  .mf-promo-text { font-family:'Inter',sans-serif; font-size:15px; opacity:0.95; line-height:1.6; margin-bottom:20px; }

  /* Deal of the week CTA */
  .mf-deal-cta { background:var(--mf-green-deep); color:#fff; padding:48px 24px; text-align:center; border-radius:20px; position:relative; overflow:hidden; }
  .mf-deal-cta::before { content:''; position:absolute; inset:0; background-image:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Ccircle cx='30' cy='30' r='4'/%3E%3C/g%3E%3C/svg%3E"); }
  .mf-deal-cta > * { position:relative; z-index:1; }
  .mf-deal-cta-title { font-family:'Inter',sans-serif; font-size:clamp(22px,2.5vw,30px); font-weight:800; margin-bottom:10px; letter-spacing:-0.02em; }
  .mf-deal-cta-text { font-family:'Inter',sans-serif; opacity:0.9; max-width:440px; margin:0 auto 24px; font-size:15px; line-height:1.6; }
  .mf-deal-cta .mf-btn { background:var(--mf-orange); color:#fff; }
  .mf-deal-cta .mf-btn:hover { background:var(--mf-orange-dark); }

  /* WhatsApp CTA */
  .mf-wa-cta { background:linear-gradient(135deg, #25D366, #1DA851); color:#fff; padding:48px 24px; text-align:center; border-radius:20px; }
  .mf-wa-cta-title { font-family:'Inter',sans-serif; font-size:clamp(22px,2.5vw,30px); font-weight:800; margin-bottom:10px; letter-spacing:-0.02em; }
  .mf-wa-cta-text { font-family:'Inter',sans-serif; opacity:0.95; max-width:440px; margin:0 auto 24px; font-size:15px; line-height:1.6; }
  .mf-wa-cta .mf-btn { background:#fff; color:#1DA851; }
  .mf-wa-cta .mf-btn:hover { background:var(--mf-dark); color:#fff; }

  /* Newsletter */
  .mf-newsletter { background:var(--mf-green); color:#fff; padding:48px 24px; text-align:center; border-radius:20px; }
  .mf-newsletter-title { font-family:'Inter',sans-serif; font-size:clamp(22px,2.5vw,30px); font-weight:800; margin-bottom:10px; letter-spacing:-0.02em; }
  .mf-newsletter-text { font-family:'Inter',sans-serif; opacity:0.9; max-width:440px; margin:0 auto 24px; font-size:15px; }
  .mf-newsletter-form { display:flex; gap:10px; max-width:440px; margin:0 auto; }
  .mf-newsletter-form input { flex:1; padding:14px 18px; border:none; border-radius:999px; font-family:'Inter',sans-serif; font-size:14px; outline:none; }
  .mf-newsletter-form button { padding:14px 24px; border:none; border-radius:999px; background:var(--mf-orange); color:#fff; font-family:'Inter',sans-serif; font-weight:600; cursor:pointer; font-size:14px; transition:background .2s; }
  .mf-newsletter-form button:hover { background:var(--mf-orange-dark); }

  /* Testimonials */
  .mf-testimonials-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:18px; }
  @media(max-width:767px){ .mf-testimonials-grid { grid-template-columns:1fr; } }
  .mf-testimonial { background:#fff; border:1px solid var(--mf-border); border-radius:16px; padding:24px; }
  .mf-testimonial-stars { color:var(--mf-orange); margin-bottom:12px; font-size:16px; letter-spacing:2px; }
  .mf-testimonial-text { font-family:'Inter',sans-serif; font-size:14px; color:var(--mf-gray); line-height:1.7; margin-bottom:16px; }
  .mf-testimonial-author { font-family:'Inter',sans-serif; font-size:15px; font-weight:700; color:var(--mf-dark); }
  .mf-testimonial-role { font-family:'Inter',sans-serif; font-size:12px; color:var(--mf-green); margin-top:2px; }

  /* Brands */
  .mf-brands-grid { display:flex; flex-wrap:wrap; gap:14px; align-items:center; justify-content:center; }
  .mf-brand { padding:12px 24px; background:#fff; border:1px solid var(--mf-border); border-radius:999px; font-family:'Inter',sans-serif; font-size:14px; font-weight:600; color:var(--mf-dark); transition:border-color .2s, color .2s; }
  .mf-brand:hover { border-color:var(--mf-green); color:var(--mf-green); }

  /* Instagram */
  .mf-insta-grid { display:grid; grid-template-columns:repeat(5,1fr); gap:12px; }
  @media(max-width:767px){ .mf-insta-grid { grid-template-columns:repeat(3,1fr); } }
  .mf-insta-item { aspect-ratio:1; border-radius:16px; overflow:hidden; position:relative; }
  .mf-insta-item img { width:100%; height:100%; object-fit:cover; transition:transform .3s; }
  .mf-insta-item:hover img { transform:scale(1.08); }

  /* FAQs */
  .mf-faq-list { max-width:760px; margin:0 auto; }
  .mf-faq-item { background:#fff; border:1px solid var(--mf-border); border-radius:16px; margin-bottom:10px; overflow:hidden; }
  .mf-faq-q { padding:18px 24px; font-family:'Inter',sans-serif; font-size:15px; font-weight:600; color:var(--mf-dark); cursor:pointer; display:flex; justify-content:space-between; align-items:center; }
  .mf-faq-q::after { content:'+'; font-size:20px; color:var(--mf-green); font-weight:700; }
  .mf-faq-item.open .mf-faq-q::after { content:'\2212'; }
  .mf-faq-a { max-height:0; overflow:hidden; transition:max-height .3s ease; padding:0 24px; }
  .mf-faq-item.open .mf-faq-a { max-height:300px; padding:0 24px 18px; }
  .mf-faq-a p { font-family:'Inter',sans-serif; font-size:14px; color:var(--mf-gray); line-height:1.7; margin:0; }

  /* Contact */
  .mf-contact-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:18px; }
  @media(max-width:767px){ .mf-contact-grid { grid-template-columns:1fr; } }
  .mf-contact-card { background:#fff; border:1px solid var(--mf-border); border-radius:16px; padding:24px; text-align:center; transition:transform .2s, box-shadow .2s; }
  .mf-contact-card:hover { transform:translateY(-4px); box-shadow:0 12px 32px rgba(22,163,74,0.1); }
  .mf-contact-icon { width:52px; height:52px; border-radius:999px; background:rgba(22,163,74,0.08); display:flex; align-items:center; justify-content:center; margin:0 auto 14px; color:var(--mf-green); }
  .mf-contact-icon svg { width:22px; height:22px; }
  .mf-contact-label { font-family:'Inter',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.12em; color:var(--mf-orange); font-weight:600; margin-bottom:4px; }
  .mf-contact-value { font-family:'Inter',sans-serif; font-size:15px; font-weight:600; color:var(--mf-dark); }

  /* Store hours */
  .mf-hours-grid { max-width:520px; margin:0 auto; background:#fff; border:1px solid var(--mf-border); border-radius:16px; padding:0 24px; }
  .mf-hours-row { display:flex; justify-content:space-between; align-items:center; padding:14px 0; border-bottom:1px solid var(--mf-border); font-family:'Inter',sans-serif; font-size:15px; }
  .mf-hours-row:last-child { border-bottom:none; }
  .mf-hours-day { color:var(--mf-dark); font-weight:600; }
  .mf-hours-time { color:var(--mf-gray); }
  .mf-hours-full { width:100%; text-align:center; color:var(--mf-dark); }

  /* Modal */
  .wa-order-modal { display:none; position:fixed; inset:0; z-index:9999; }
  .wa-order-modal.open { display:block; }
  .wa-order-modal-overlay { position:absolute; inset:0; background:rgba(20,83,45,0.4); }
  .wa-order-modal-card { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:440px; max-width:calc(100vw - 32px); max-height:90vh; overflow-y:auto; background:#fff; border-radius:20px; box-shadow:0 24px 60px rgba(22,163,74,0.25); border:1px solid var(--mf-border); }
  .wa-order-modal-header { display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid var(--mf-border); }
  .wa-order-modal-title { font-family:'Inter',sans-serif; font-size:20px; font-weight:800; color:var(--mf-dark); margin:0; }
  .wa-order-modal-close { width:32px; height:32px; border-radius:999px; border:none; background:var(--mf-cream); color:var(--mf-dark); cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background .2s; }
  .wa-order-modal-close:hover { background:var(--mf-border); }
  .wa-order-modal-body { padding:24px; }
  .wa-order-modal-product { display:flex; gap:14px; align-items:center; padding:14px; background:var(--mf-cream); border-radius:12px; margin-bottom:20px; }
  .wa-order-modal-product img { width:56px; height:56px; border-radius:8px; object-fit:cover; flex-shrink:0; }
  .wa-order-modal-product-info { flex:1; min-width:0; }
  .wa-order-modal-product-name { font-family:'Inter',sans-serif; font-size:15px; font-weight:600; color:var(--mf-dark); margin:0 0 4px; line-height:1.3; }
  .wa-order-modal-product-price { font-family:'Inter',sans-serif; font-size:16px; font-weight:800; color:var(--mf-green); }
  .wa-order-modal-fields { display:flex; flex-direction:column; gap:14px; }
  .wa-order-modal-fields label { font-family:'Inter',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.08em; font-weight:600; color:var(--mf-dark); margin-bottom:5px; display:block; }
  .wa-order-modal-fields input, .wa-order-modal-fields textarea { width:100%; padding:12px 14px; border:1px solid var(--mf-border); border-radius:10px; font-family:'Inter',sans-serif; font-size:14px; background:var(--mf-cream); outline:none; transition:border-color .2s; color:var(--mf-dark); box-sizing:border-box; }
  .wa-order-modal-fields input:focus, .wa-order-modal-fields textarea:focus { border-color:var(--mf-green); background:#fff; }
  .wa-order-modal-fields textarea { resize:vertical; min-height:64px; }
  .wa-order-modal-actions { display:flex; gap:10px; margin-top:20px; }
  .wa-order-modal-send { flex:1; padding:14px; border-radius:999px; border:none; background:#25D366; color:#fff; font-family:'Inter',sans-serif; font-size:14px; font-weight:600; cursor:pointer; transition:background .2s; display:flex; align-items:center; justify-content:center; gap:8px; }
  .wa-order-modal-send:hover { background:#1FB855; }
  .wa-order-modal-cancel { padding:14px 20px; border-radius:999px; border:1px solid var(--mf-border); background:#fff; color:var(--mf-dark); font-family:'Inter',sans-serif; font-size:14px; font-weight:600; cursor:pointer; transition:background .2s; }
  .wa-order-modal-cancel:hover { background:var(--mf-cream); }

  /* Services strip */
  .mf-services-strip { background:var(--mf-cream); padding:48px 0; }
  .mf-services-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
  @media(max-width:1023px){ .mf-services-grid { grid-template-columns:repeat(2,1fr); } }
  @media(max-width:767px){ .mf-services-grid { grid-template-columns:1fr; } }
  .mf-service-card { background:#fff; border:1px solid var(--mf-border); border-radius:16px; padding:24px; text-align:center; transition:transform .2s, box-shadow .2s; }
  .mf-service-card:hover { transform:translateY(-4px); box-shadow:0 12px 32px rgba(22,163,74,0.1); }
  .mf-service-icon { font-size:28px; margin-bottom:10px; }
  .mf-service-title { font-family:'Inter',sans-serif; font-size:15px; font-weight:700; color:var(--mf-dark); margin-bottom:4px; }
  .mf-service-text { font-family:'Inter',sans-serif; font-size:13px; color:var(--mf-gray); }

  /* Small-screen polish */
  @media(max-width:480px){
    .mf-product-body { padding:12px; }
    .mf-product-name { font-size:14px; min-height:38px; }
    .mf-product-price { font-size:16px; }
    .mf-add-btn, .mf-wa-btn { padding:10px 12px; font-size:12px; }
    .mf-newsletter-form { flex-direction:column; }
    .mf-newsletter-form button { width:100%; }
    .mf-section-head { flex-direction:column; align-items:flex-start; gap:8px; }
    .mf-cat-card-name { font-size:12px; }
    .mf-cat-card-count { font-size:10px; }
    .mf-hero-actions .mf-btn { width:100%; }
    .mf-promo-body { padding:24px 20px; }
  }
</style>

<?php
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
<div class="mf-hero">
  <div class="mf-hero-media"><img src="<?= $heroImg; ?>" alt="<?= htmlspecialchars($hero->banner_title ?? ($store->store_name ?? 'Market Fresh')); ?>" decoding="async"></div>
  <div class="mf-hero-gradient"></div>
  <div class="mf-hero-content">
    <div class="mf-hero-text">
      <p class="mf-hero-kicker"><?= htmlspecialchars($hero->banner_subtitle ?? 'Fresh groceries delivered daily'); ?></p>
      <h1 class="mf-hero-title"><?= htmlspecialchars($hero->banner_title ?? ($settings->store_headline ?: ($store->store_name ?? 'Fresh Groceries, Delivered'))); ?></h1>
      <p class="mf-hero-lead"><?= htmlspecialchars($settings->store_subheadline ?: 'Shop fresh produce, pantry essentials and household items from your neighbourhood supermarket. Quality you can trust, prices you will love.'); ?></p>
      <div class="mf-hero-actions">
        <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="mf-btn mf-btn-orange">Shop Groceries</a>
        <a href="https://wa.me/<?= $waNumber; ?>" target="_blank" class="mf-btn mf-btn-ghost">Order on WhatsApp</a>
      </div>
    </div>
    <div class="mf-hero-visual">
      <img src="<?= $heroImg; ?>" alt="<?= htmlspecialchars($hero->banner_title ?? 'Market Fresh'); ?>" decoding="async">
      <div class="mf-hero-badge">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a8 8 0 0 0-8 8c0 4 3 7 8 9 5-2 8-5 8-9a8 8 0 0 0-8-8z"/><path d="M12 6v6"/></svg>
        Fresh Daily
      </div>
    </div>
  </div>
</div>
<?php else: ?>
<div class="mf-hero">
  <div class="mf-hero-content">
    <div class="mf-hero-text">
      <p class="mf-hero-kicker"><?= htmlspecialchars($settings->store_subheadline ?? 'Fresh groceries delivered daily'); ?></p>
      <h1 class="mf-hero-title"><?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Fresh Groceries, Delivered')); ?></h1>
      <p class="mf-hero-lead"><?= htmlspecialchars($settings->store_description ?: 'Shop fresh produce, pantry essentials and household items from your neighbourhood supermarket. Quality you can trust, prices you will love.'); ?></p>
      <div class="mf-hero-actions">
        <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="mf-btn mf-btn-orange">Shop Groceries</a>
        <a href="https://wa.me/<?= $waNumber; ?>" target="_blank" class="mf-btn mf-btn-ghost">Order on WhatsApp</a>
      </div>
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
      $mfBadges = json_decode($settings->trust_badges_json ?? '', true);
      if(empty($mfBadges) || !is_array($mfBadges)) break;
?>
<!-- TRUST BADGES -->
<div class="mf-trust">
  <div class="mf-container">
    <div class="mf-trust-grid">
      <?php foreach(array_slice($mfBadges, 0, 4) as $b): ?>
      <div class="mf-trust-item">
        <div class="mf-trust-item-icon"><?= !empty($b['icon']) ? $b['icon'] : '&#10004;'; ?></div>
        <div class="mf-trust-item-title"><?= htmlspecialchars($b['title'] ?? ''); ?></div>
        <div class="mf-trust-item-text"><?= htmlspecialchars($b['desc'] ?? ''); ?></div>
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
        $promoImg = ($promo->desktop_image && file_exists($promo->desktop_image)) ? mp_minified_image_url($promo->desktop_image, 1200) : '';
?>
<!-- PROMO BANNER -->
<div class="mf-section">
  <div class="mf-container">
    <div class="mf-promo">
      <?php if($promoImg): ?>
      <div class="mf-promo-media"><img src="<?= $promoImg; ?>" alt="<?= htmlspecialchars($promo->banner_title ?? 'Promo'); ?>" decoding="async"></div>
      <?php endif; ?>
      <div class="mf-promo-body">
        <div class="mf-promo-kicker"><?= htmlspecialchars($promo->banner_subtitle ?? 'Weekly Special'); ?></div>
        <h3 class="mf-promo-title"><?= htmlspecialchars($promo->banner_title ?? 'Fresh Deals This Week'); ?></h3>
        <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="mf-btn" style="background:#fff;color:var(--mf-orange-dark);">Shop Deals</a>
      </div>
    </div>
  </div>
</div>
<?php
      endif;
      break;

    // =====================================================
    // FEATURED CATEGORIES
    // =====================================================
    case 'featured_categories':
      if(!empty($categories) && count($categories) > 1):
?>
<!-- CATEGORIES -->
<div class="mf-section" style="background:var(--mf-cream);">
  <div class="mf-container">
    <div class="mf-section-head">
      <div>
        <div class="mf-section-label">Browse</div>
        <h2 class="mf-section-title">Shop by Category</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="mf-section-link">View All &rarr;</a>
    </div>
    <div class="mf-cat-grid">
      <?php foreach(array_slice($categories, 0, 8) as $cat):
        $catImg = (!empty($cat->category_image) && file_exists($cat->category_image)) ? mp_minified_image_url($cat->category_image, 400) : '';
        $itemCount = $cat->item_count ?? 0;
      ?>
      <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="mf-cat-card">
        <div class="mf-cat-card-media">
          <?php if($catImg): ?>
          <img src="<?= $catImg; ?>" alt="<?= htmlspecialchars($cat->category_name); ?>" loading="lazy" decoding="async">
          <?php else: ?>
          <div class="mf-cat-placeholder"><span><?= htmlspecialchars(substr($cat->category_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="mf-cat-card-overlay"></div>
        <div class="mf-cat-card-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </div>
        <div class="mf-cat-card-body">
          <div class="mf-cat-card-name"><?= htmlspecialchars($cat->category_name); ?></div>
          <?php if($itemCount > 0): ?>
          <div class="mf-cat-card-count"><?= $itemCount; ?> items</div>
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
<div class="mf-section">
  <div class="mf-container">
    <div class="mf-section-head">
      <div>
        <div class="mf-section-label">Top Picks</div>
        <h2 class="mf-section-title">Featured Products</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="mf-section-link">View All &rarr;</a>
    </div>
    <div class="mf-product-grid">
      <?php foreach(array_slice($featured_products, 0, 8) as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $oldPrice = $p->original_price ?? $p->sales_price;
        $hasDiscount = $oldPrice > $price;
        $discountPct = $hasDiscount ? round((($oldPrice - $price) / $oldPrice) * 100) : 0;
        $img = ($p->item_image && file_exists($p->item_image)) ? mp_minified_image_url($p->item_image, 600) : '';
      ?>
      <div class="mf-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, <?= $hasDiscount ? $oldPrice : 0; ?>)">
        <?php if($hasDiscount && $discountPct > 0): ?>
        <span class="mf-product-badge">-<?= $discountPct; ?>%</span>
        <?php endif; ?>
        <button class="mf-product-wishlist" onclick="event.stopPropagation();" aria-label="Wishlist"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></button>
        <div class="mf-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy" decoding="async">
          <?php else: ?>
          <div class="mf-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="mf-product-body">
          <?php if(!empty($p->category_name)): ?>
          <div class="mf-product-brand"><?= htmlspecialchars($p->category_name); ?></div>
          <?php endif; ?>
          <div class="mf-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="mf-product-footer">
            <div class="mf-product-price"><?= sf_currency($price, $cur); ?><?php if($hasDiscount): ?><span class="old"><?= sf_currency($oldPrice, $cur); ?></span><?php endif; ?></div>
            <div class="mf-card-actions">
              <button class="mf-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add to cart"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="mf-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $p->id; ?>,'<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',<?= $p->stock; ?>)" aria-label="Order via WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
            </div>
          </div>
          <?php if($p->stock <= 0 && !($settings->allow_backorder ?? false)): ?>
          <div class="mf-product-stock">Out of Stock</div>
          <?php else: ?>
          <div class="mf-product-stock in">In Stock</div>
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
<div class="mf-services-strip">
  <div class="mf-container">
    <div class="mf-section-head">
      <div>
        <div class="mf-section-label">Services</div>
        <h2 class="mf-section-title">Store Services</h2>
      </div>
    </div>
    <div class="mf-product-grid">
      <?php foreach(array_slice($featured_services, 0, 4) as $s):
        $sPrice = $s->effective_price ?? $s->sales_price ?? $s->price ?? 0;
        $sImg = (!empty($s->item_image) && file_exists($s->item_image)) ? mp_minified_image_url($s->item_image, 600) : (!empty($s->service_image) && file_exists($s->service_image) ? mp_minified_image_url($s->service_image, 600) : '');
      ?>
      <div class="mf-product-card" onclick="openProductModal(<?= $s->id; ?>, '<?= htmlspecialchars(addslashes($s->item_name ?? $s->service_name ?? '')); ?>', <?= $sPrice; ?>, '<?= $s->item_image ?? $s->service_image ?? ''; ?>', '<?= htmlspecialchars(addslashes($s->description ?? '')); ?>', 999, 0)">
        <div class="mf-product-media">
          <?php if($sImg): ?>
          <img src="<?= $sImg; ?>" alt="<?= htmlspecialchars($s->item_name ?? $s->service_name ?? ''); ?>" loading="lazy" decoding="async">
          <?php else: ?>
          <div class="mf-product-placeholder"><span><?= htmlspecialchars(substr($s->item_name ?? $s->service_name ?? 'S', 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="mf-product-body">
          <div class="mf-product-name"><?= htmlspecialchars($s->item_name ?? $s->service_name ?? ''); ?></div>
          <div class="mf-product-footer">
            <div class="mf-product-price"><?= sf_currency($sPrice, $cur); ?></div>
            <div class="mf-card-actions">
              <button class="mf-add-btn" onclick="event.stopPropagation();addToCart(<?= $s->id; ?>,'service','<?= htmlspecialchars(addslashes($s->item_name ?? $s->service_name ?? '')); ?>',<?= $sPrice; ?>,'<?= $s->item_image ?? $s->service_image ?? ''; ?>',1,999)" aria-label="Add to cart"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="mf-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $s->id; ?>,'<?= htmlspecialchars(addslashes($s->item_name ?? $s->service_name ?? '')); ?>',<?= $sPrice; ?>,'<?= $s->item_image ?? $s->service_image ?? ''; ?>',999)" aria-label="Order via WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
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
<div class="mf-section">
  <div class="mf-container">
    <div class="mf-section-head">
      <div>
        <div class="mf-section-label">Customer Favourites</div>
        <h2 class="mf-section-title">Best Sellers</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="mf-section-link">View All &rarr;</a>
    </div>
    <div class="mf-product-grid">
      <?php foreach(array_slice($best_sellers, 0, 4) as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $img = ($p->item_image && file_exists($p->item_image)) ? mp_minified_image_url($p->item_image, 600) : '';
      ?>
      <div class="mf-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, 0)">
        <div class="mf-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy" decoding="async">
          <?php else: ?>
          <div class="mf-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="mf-product-body">
          <div class="mf-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="mf-product-footer">
            <div class="mf-product-price"><?= sf_currency($price, $cur); ?></div>
            <div class="mf-card-actions">
              <button class="mf-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add to cart"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="mf-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $p->id; ?>,'<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',<?= $p->stock; ?>)" aria-label="Order via WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
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
<div class="mf-section" style="background:var(--mf-cream);">
  <div class="mf-container">
    <div class="mf-section-head">
      <div>
        <div class="mf-section-label">Just In</div>
        <h2 class="mf-section-title">New Arrivals</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="mf-section-link">View All &rarr;</a>
    </div>
    <div class="mf-product-grid">
      <?php foreach(array_slice($new_arrivals, 0, 4) as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $img = ($p->item_image && file_exists($p->item_image)) ? mp_minified_image_url($p->item_image, 600) : '';
      ?>
      <div class="mf-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, 0)">
        <span class="mf-product-badge new">New</span>
        <div class="mf-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy" decoding="async">
          <?php else: ?>
          <div class="mf-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="mf-product-body">
          <div class="mf-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="mf-product-footer">
            <div class="mf-product-price"><?= sf_currency($price, $cur); ?></div>
            <div class="mf-card-actions">
              <button class="mf-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add to cart"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="mf-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $p->id; ?>,'<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',<?= $p->stock; ?>)" aria-label="Order via WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
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
<div class="mf-section">
  <div class="mf-container">
    <div class="mf-section-head" style="justify-content:center;text-align:center;">
      <h2 class="mf-section-title">Brands We Stock</h2>
    </div>
    <div class="mf-brands-grid">
      <?php foreach($brands as $brand): ?>
      <div class="mf-brand"><?= htmlspecialchars($brand->brand_name ?? $brand->name ?? ''); ?></div>
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
<div class="mf-section" style="background:var(--mf-cream);">
  <div class="mf-container">
    <div class="mf-section-head">
      <div>
        <div class="mf-section-label">Customer Reviews</div>
        <h2 class="mf-section-title">What Shoppers Say</h2>
      </div>
    </div>
    <div class="mf-testimonials-grid">
      <?php foreach(array_slice($testimonials, 0, 3) as $t): ?>
      <div class="mf-testimonial">
        <div class="mf-testimonial-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
        <div class="mf-testimonial-text"><?= htmlspecialchars($t->testimonial_text ?? $t->message ?? ''); ?></div>
        <div class="mf-testimonial-author"><?= htmlspecialchars($t->customer_name ?? $t->author ?? ''); ?></div>
        <?php if(!empty($t->role ?? $t->customer_role)): ?>
        <div class="mf-testimonial-role"><?= htmlspecialchars($t->role ?? $t->customer_role); ?></div>
        <?php endif; ?>
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
<div class="mf-section">
  <div class="mf-container">
    <div class="mf-section-head" style="justify-content:center;text-align:center;">
      <h2 class="mf-section-title">Follow Us</h2>
    </div>
    <div class="mf-insta-grid">
      <?php foreach(array_slice($instagram_posts, 0, 10) as $post): ?>
      <a href="<?= htmlspecialchars($post->permalink ?? $post->link ?? '#'); ?>" target="_blank" class="mf-insta-item">
        <img src="<?= htmlspecialchars($post->media_url ?? $post->image ?? ''); ?>" alt="Instagram post" loading="lazy" decoding="async">
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php
      endif;
      break;

    // =====================================================
    // STORE INFO (covered by contact + hours sections)
    // =====================================================
    case 'store_info':
      break;

    // =====================================================
    // FAQS
    // =====================================================
    case 'faqs':
      if(!empty($faqs)):
?>
<!-- FAQS -->
<div class="mf-section">
  <div class="mf-container">
    <div class="mf-section-head" style="justify-content:center;text-align:center;">
      <h2 class="mf-section-title">Frequently Asked Questions</h2>
    </div>
    <div class="mf-faq-list">
      <?php foreach($faqs as $faq): ?>
      <div class="mf-faq-item" onclick="this.classList.toggle('open')">
        <div class="mf-faq-q"><?= htmlspecialchars($faq->question ?? $faq->faq_question ?? ''); ?></div>
        <div class="mf-faq-a"><p><?= htmlspecialchars($faq->answer ?? $faq->faq_answer ?? ''); ?></p></div>
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
<div class="mf-section" style="background:var(--mf-cream);">
  <div class="mf-container">
    <div class="mf-section-head" style="justify-content:center;text-align:center;">
      <h2 class="mf-section-title">Visit or Contact Us</h2>
    </div>
    <div class="mf-contact-grid">
      <?php if(!empty($settings->store_phone)): ?>
      <div class="mf-contact-card">
        <div class="mf-contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></div>
        <div class="mf-contact-label">Phone</div>
        <div class="mf-contact-value"><?= htmlspecialchars($settings->store_phone); ?></div>
      </div>
      <?php endif; ?>
      <?php if(!empty($settings->store_email)): ?>
      <div class="mf-contact-card">
        <div class="mf-contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
        <div class="mf-contact-label">Email</div>
        <div class="mf-contact-value"><?= htmlspecialchars($settings->store_email); ?></div>
      </div>
      <?php endif; ?>
      <?php if($waNumber): ?>
      <div class="mf-contact-card">
        <div class="mf-contact-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg></div>
        <div class="mf-contact-label">WhatsApp</div>
        <div class="mf-contact-value"><?= htmlspecialchars($settings->whatsapp_number); ?></div>
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
<div class="mf-section">
  <div class="mf-container">
    <div class="mf-wa-cta">
      <div class="mf-wa-cta-title">Need Help With Your Order?</div>
      <p class="mf-wa-cta-text">Chat with us on WhatsApp for quick assistance, product enquiries and same-day delivery arrangements.</p>
      <a href="https://wa.me/<?= $waNumber; ?>" target="_blank" class="mf-btn">Chat on WhatsApp</a>
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
<div class="mf-section">
  <div class="mf-container">
    <div class="mf-newsletter">
      <div class="mf-newsletter-title">Get Fresh Deals in Your Inbox</div>
      <p class="mf-newsletter-text">Subscribe for weekly specials, new arrivals and exclusive supermarket offers.</p>
      <form class="mf-newsletter-form" onsubmit="return mpNewsletterSubmit(event)">
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
<div class="mf-section" style="background:var(--mf-cream);">
  <div class="mf-container">
    <div class="mf-section-head" style="justify-content:center;text-align:center;">
      <h2 class="mf-section-title">Opening Hours</h2>
    </div>
    <div class="mf-hours-grid">
      <?php foreach($business_hours as $line):
        $line = trim($line);
        if(strpos($line, ':') !== false):
          list($day, $time) = array_map('trim', explode(':', $line, 2)); ?>
      <div class="mf-hours-row">
        <span class="mf-hours-day"><?= htmlspecialchars(ucfirst($day)); ?></span>
        <span class="mf-hours-time"><?= htmlspecialchars($time); ?></span>
      </div>
      <?php else: ?>
      <div class="mf-hours-row">
        <span class="mf-hours-full"><?= htmlspecialchars($line); ?></span>
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
  const storeName = '<?= htmlspecialchars(addslashes($store->store_name ?? 'Market Fresh')); ?>';
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
