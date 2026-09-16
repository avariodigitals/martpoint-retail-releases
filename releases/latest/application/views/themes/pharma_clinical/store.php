<?php
/**
 * Pharma Clinical — Pharmacy Homepage
 * Medical-grade clinical design with deep trust blues, crisp typography
 * and professional healthcare-chain aesthetics.
 * Respects the homepage builder section order and visibility.
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
if($hero && $hero->desktop_image) $heroImg = base_url($hero->desktop_image);
elseif($hero && $hero->mobile_image) $heroImg = base_url($hero->mobile_image);

$waNumber = preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? '');
?>
<style>
  :root {
    --pc-blue:#005EB8;
    --pc-blue-dark:#004A94;
    --pc-teal:#00A86B;
    --pc-teal-dark:#008A5C;
    --pc-dark:#0F172A;
    --pc-gray:#64748B;
    --pc-light:#F1F5F9;
    --pc-white:#FFFFFF;
    --pc-border:#E2E8F0;
    --pc-accent:#38BDF8;
  }

  .theme-pharma_clinical .mp-topbar,
  .theme-pharma_clinical .mp-announcement,
  .theme-pharma_clinical .mp-nav,
  .theme-pharma_clinical .mp-header,
  .theme-pharma_clinical .mp-mobile-menu-btn,
  .theme-pharma_clinical .mp-footer-space { display:none !important; }

  .pc-container { max-width:1400px; margin:0 auto; padding:0 24px; }
  @media(max-width:767px){ .pc-container { padding:0 16px; } }

  /* Hero */
  .pc-hero { position:relative; background:linear-gradient(135deg, #0F172A 0%, #005EB8 50%, #0069D1 100%); color:#fff; overflow:hidden; min-height:580px; display:flex; align-items:center; }
  @media(max-width:767px){ .pc-hero { min-height:520px; } }
  .pc-hero::before { content:''; position:absolute; inset:0; background-image:url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M36 20h8v16h16v8H44v16h-8V44H20v-8h16z'/%3E%3C/g%3E%3C/svg%3E"); background-size:80px 80px; }
  .pc-hero-media { position:absolute; inset:0; opacity:0.25; }
  .pc-hero-media img { width:100%; height:100%; object-fit:cover; }
  .pc-hero-gradient { position:absolute; inset:0; background:linear-gradient(90deg, rgba(15,23,42,0.92) 0%, rgba(15,23,42,0.6) 55%, rgba(15,23,42,0.15) 100%); }
  .pc-hero-content { position:relative; z-index:2; padding:100px 24px 120px; max-width:1400px; margin:0 auto; width:100%; display:flex; align-items:center; gap:60px; }
  @media(max-width:1023px){ .pc-hero-content { flex-direction:column; gap:40px; padding:80px 24px 100px; } }
  @media(max-width:767px){ .pc-hero-content { padding:60px 16px 80px; } }
  .pc-hero-text { flex:1; max-width:640px; }
  .pc-hero-kicker { font-family:'Inter',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.18em; color:#38BDF8; margin-bottom:16px; font-weight:600; }
  .pc-hero-title { font-family:'Inter',sans-serif; font-size:clamp(36px,4.5vw,58px); line-height:1.08; font-weight:800; margin:0 0 20px; letter-spacing:-0.02em; }
  .pc-hero-lead { font-family:'Inter',sans-serif; font-size:clamp(15px,1.8vw,18px); line-height:1.7; opacity:0.9; margin-bottom:32px; }
  .pc-hero-actions { display:flex; gap:14px; flex-wrap:wrap; }
  .pc-hero-visual { flex:1; max-width:480px; position:relative; }
  .pc-hero-visual img { width:100%; height:400px; object-fit:cover; border-radius:16px; box-shadow:0 24px 60px rgba(0,0,0,0.3); }
  @media(max-width:1023px){ .pc-hero-visual { max-width:100%; } .pc-hero-visual img { height:280px; } }
  @media(max-width:767px){ .pc-hero-visual img { height:200px; } }

  /* Trust badge card */
  .pc-trust-card { position:absolute; bottom:-20px; left:-20px; background:#fff; color:#0F172A; padding:16px 20px; border-radius:12px; box-shadow:0 12px 32px rgba(0,0,0,0.15); display:flex; align-items:center; gap:10px; font-family:'Inter',sans-serif; font-size:12px; font-weight:600; }
  .pc-trust-card svg { width:20px; height:20px; color:#00A86B; flex-shrink:0; }
  @media(max-width:767px){ .pc-trust-card { left:12px; bottom:-12px; padding:12px 16px; } }

  /* Buttons */
  .pc-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:15px 34px; border-radius:8px; font-family:'Inter',sans-serif; font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; transition:transform .15s, background .2s, color .2s; cursor:pointer; border:none; text-decoration:none; }
  .pc-btn:active { transform:scale(0.98); }
  .pc-btn-blue { background:var(--pc-blue); color:#fff; }
  .pc-btn-blue:hover { background:var(--pc-blue-dark); }
  .pc-btn-white { background:#fff; color:var(--pc-dark); }
  .pc-btn-white:hover { background:var(--pc-light); }
  .pc-btn-ghost { background:transparent; color:#fff; border:1px solid rgba(255,255,255,0.3); }
  .pc-btn-ghost:hover { background:rgba(255,255,255,0.1); border-color:#fff; }
  .pc-btn-outline { background:#fff; color:var(--pc-blue); border:1px solid var(--pc-blue); }
  .pc-btn-outline:hover { background:var(--pc-blue); color:#fff; }
  .pc-btn-wa { background:#25D366; color:#fff; }
  .pc-btn-wa:hover { background:#1DA851; }
  .pc-btn-teal { background:var(--pc-teal); color:#fff; }
  .pc-btn-teal:hover { background:var(--pc-teal-dark); }

  /* Sections */
  .pc-section { padding:72px 0; }
  @media(max-width:767px){ .pc-section { padding:44px 0; } }
  .pc-section-head { display:flex; align-items:flex-end; justify-content:space-between; gap:16px; margin-bottom:36px; }
  .pc-section-label { font-family:'Inter',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.16em; color:var(--pc-blue); font-weight:600; margin-bottom:8px; }
  .pc-section-title { font-family:'Inter',sans-serif; font-size:32px; margin:0; font-weight:800; color:var(--pc-dark); letter-spacing:-0.01em; }
  .pc-section-link { font-family:'Inter',sans-serif; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; border-bottom:1px solid var(--pc-dark); padding-bottom:2px; transition:color .2s, border-color .2s; color:var(--pc-dark); text-decoration:none; }
  .pc-section-link:hover { color:var(--pc-blue); border-color:var(--pc-blue); }

  /* Product grid */
  .pc-product-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:20px; }
  @media(max-width:1023px){ .pc-product-grid { grid-template-columns:repeat(3,1fr); } }
  @media(max-width:767px){ .pc-product-grid { grid-template-columns:repeat(2,1fr); gap:12px; } }
  .pc-product-card { position:relative; background:#fff; border-radius:12px; overflow:hidden; border:1px solid var(--pc-border); transition:transform .25s, box-shadow .25s; cursor:pointer; }
  .pc-product-card:hover { transform:translateY(-4px); box-shadow:0 12px 32px rgba(0,94,184,0.1); }
  .pc-product-wishlist { position:absolute; top:12px; right:12px; width:36px; height:36px; border-radius:8px; background:#fff; display:flex; align-items:center; justify-content:center; z-index:2; transition:color .2s, transform .2s; box-shadow:0 2px 8px rgba(0,0,0,0.08); border:none; cursor:pointer; }
  .pc-product-wishlist:hover { color:var(--pc-blue); transform:scale(1.08); }
  .pc-product-badge { position:absolute; top:12px; left:12px; background:var(--pc-teal); color:#fff; font-family:'Inter',sans-serif; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; padding:5px 10px; border-radius:6px; z-index:2; }
  .pc-product-badge.rx { background:var(--pc-blue); }
  .pc-product-media { aspect-ratio:1/1; overflow:hidden; background:var(--pc-light); }
  .pc-product-media img { width:100%; height:100%; object-fit:cover; transition:transform .4s ease; }
  .pc-product-card:hover .pc-product-media img { transform:scale(1.05); }
  .pc-product-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--pc-light); color:var(--pc-blue); }
  .pc-product-placeholder span { font-family:'Inter',sans-serif; font-size:36px; font-weight:800; }
  .pc-product-body { padding:16px; }
  .pc-product-brand { font-family:'Inter',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.08em; color:var(--pc-teal); font-weight:600; margin-bottom:4px; }
  .pc-product-name { font-family:'Inter',sans-serif; font-size:14px; font-weight:600; margin-bottom:10px; line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; min-height:40px; color:var(--pc-dark); }
  .pc-product-footer { display:flex; flex-direction:column; gap:8px; }
  .pc-product-price { font-family:'Inter',sans-serif; font-size:18px; font-weight:800; color:var(--pc-dark); }
  .pc-product-price .old { font-family:'Inter',sans-serif; font-size:13px; color:var(--pc-gray); text-decoration:line-through; margin-left:6px; font-weight:500; }
  .pc-card-actions { display:flex; gap:8px; }
  @media(max-width:767px){ .pc-card-actions { flex-direction:column; gap:6px; } }
  .pc-add-btn { flex:1; padding:11px 14px; border-radius:8px; background:var(--pc-blue); color:#fff; display:flex; align-items:center; justify-content:center; gap:6px; transition:background .2s, transform .15s; border:none; cursor:pointer; font-family:'Inter',sans-serif; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; }
  .pc-add-btn:hover { background:var(--pc-blue-dark); }
  .pc-add-btn:active { transform:scale(0.97); }
  .pc-wa-btn { flex:1; padding:11px 14px; border-radius:8px; background:#25D366; color:#fff; display:flex; align-items:center; justify-content:center; gap:6px; transition:background .2s, transform .15s; border:none; cursor:pointer; font-family:'Inter',sans-serif; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; }
  .pc-wa-btn:hover { background:#1FB855; }
  .pc-wa-btn:active { transform:scale(0.97); }
  .pc-product-stock { font-family:'Inter',sans-serif; font-size:11px; color:#DC2626; font-weight:600; margin-top:6px; text-transform:uppercase; letter-spacing:0.06em; }
  .pc-product-stock.in { color:var(--pc-teal); }

  /* Category cards */
  .pc-cat-grid { display:grid; grid-template-columns:repeat(6,1fr); gap:14px; }
  @media(max-width:1023px){ .pc-cat-grid { grid-template-columns:repeat(4,1fr); } }
  @media(max-width:767px){ .pc-cat-grid { grid-template-columns:repeat(3,1fr); gap:10px; } }
  .pc-cat-card { position:relative; border-radius:12px; overflow:hidden; aspect-ratio:1/1; text-decoration:none; color:inherit; display:block; transition:transform .25s, box-shadow .25s; background:#fff; border:1px solid var(--pc-border); }
  .pc-cat-card:hover { transform:translateY(-4px); box-shadow:0 12px 32px rgba(0,94,184,0.12); }
  .pc-cat-card-media { position:absolute; inset:0; background:var(--pc-light); }
  .pc-cat-card-media img { width:100%; height:100%; object-fit:cover; transition:transform .4s ease; }
  .pc-cat-card:hover .pc-cat-card-media img { transform:scale(1.08); }
  .pc-cat-card-overlay { position:absolute; inset:0; background:linear-gradient(180deg, transparent 30%, rgba(15,23,42,0.75) 100%); }
  .pc-cat-card-body { position:absolute; bottom:0; left:0; right:0; padding:14px; z-index:2; }
  .pc-cat-card-name { font-family:'Inter',sans-serif; font-size:13px; font-weight:700; color:#fff; margin-bottom:2px; }
  .pc-cat-card-count { font-family:'Inter',sans-serif; font-size:11px; color:#38BDF8; }
  .pc-cat-card-icon { position:absolute; top:12px; right:12px; width:32px; height:32px; border-radius:8px; background:var(--pc-blue); display:flex; align-items:center; justify-content:center; opacity:0; transform:translateY(-4px); transition:opacity .25s, transform .25s; }
  .pc-cat-card:hover .pc-cat-card-icon { opacity:1; transform:translateY(0); }
  .pc-cat-card-icon svg { width:16px; height:16px; color:#fff; }
  .pc-cat-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--pc-light); }
  .pc-cat-placeholder span { font-family:'Inter',sans-serif; font-size:36px; font-weight:800; color:var(--pc-blue); }

  /* Trust badges */
  .pc-trust { background:var(--pc-light); padding:48px 0; }
  .pc-trust-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:20px; }
  @media(max-width:1023px){ .pc-trust-grid { grid-template-columns:repeat(2,1fr); } }
  @media(max-width:767px){ .pc-trust-grid { grid-template-columns:1fr; } }
  .pc-trust-item { text-align:center; padding:20px 16px; background:#fff; border-radius:12px; border:1px solid var(--pc-border); }
  .pc-trust-item-icon { width:48px; height:48px; border-radius:12px; background:rgba(0,94,184,0.08); display:flex; align-items:center; justify-content:center; font-size:22px; margin:0 auto 14px; color:var(--pc-blue); }
  .pc-trust-item-title { font-family:'Inter',sans-serif; font-size:14px; font-weight:700; color:var(--pc-dark); margin-bottom:6px; }
  .pc-trust-item-text { font-family:'Inter',sans-serif; font-size:12px; color:var(--pc-gray); line-height:1.5; }

  /* Promo banner */
  .pc-promo { background:linear-gradient(135deg, var(--pc-blue) 0%, #0069D1 100%); border-radius:16px; overflow:hidden; display:flex; align-items:center; min-height:200px; color:#fff; }
  @media(max-width:767px){ .pc-promo { flex-direction:column; min-height:auto; } }
  .pc-promo-media { flex:1; min-height:200px; background:rgba(255,255,255,0.1); }
  .pc-promo-media img { width:100%; height:100%; object-fit:cover; }
  .pc-promo-body { flex:1; padding:40px; }
  @media(max-width:767px){ .pc-promo-body { padding:28px; } }
  .pc-promo-kicker { font-family:'Inter',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.16em; color:#38BDF8; font-weight:600; margin-bottom:8px; }
  .pc-promo-title { font-family:'Inter',sans-serif; font-size:clamp(22px,2.5vw,30px); font-weight:800; margin-bottom:10px; }
  .pc-promo-text { font-family:'Inter',sans-serif; font-size:15px; opacity:0.9; line-height:1.6; margin-bottom:20px; }

  /* Prescription CTA */
  .pc-rx-cta { background:linear-gradient(135deg, var(--pc-teal) 0%, var(--pc-teal-dark) 100%); color:#fff; padding:48px 24px; text-align:center; border-radius:16px; }
  .pc-rx-cta-title { font-family:'Inter',sans-serif; font-size:clamp(22px,2.5vw,30px); font-weight:800; margin-bottom:10px; }
  .pc-rx-cta-text { font-family:'Inter',sans-serif; opacity:0.9; max-width:440px; margin:0 auto 24px; font-size:15px; line-height:1.6; }
  .pc-rx-cta .pc-btn { background:#fff; color:var(--pc-teal); }
  .pc-rx-cta .pc-btn:hover { background:var(--pc-dark); color:#fff; }

  /* WhatsApp CTA */
  .pc-wa-cta { background:linear-gradient(135deg, #25D366, #1DA851); color:#fff; padding:48px 24px; text-align:center; border-radius:16px; }
  .pc-wa-cta-title { font-family:'Inter',sans-serif; font-size:clamp(22px,2.5vw,30px); font-weight:800; margin-bottom:10px; }
  .pc-wa-cta-text { font-family:'Inter',sans-serif; opacity:0.9; max-width:440px; margin:0 auto 24px; font-size:15px; line-height:1.6; }
  .pc-wa-cta .pc-btn { background:#fff; color:#1DA851; }
  .pc-wa-cta .pc-btn:hover { background:var(--pc-dark); color:#fff; }

  /* Newsletter */
  .pc-newsletter { background:var(--pc-dark); color:#fff; padding:48px 24px; text-align:center; border-radius:16px; }
  .pc-newsletter-title { font-family:'Inter',sans-serif; font-size:clamp(22px,2.5vw,30px); font-weight:800; margin-bottom:10px; }
  .pc-newsletter-text { font-family:'Inter',sans-serif; opacity:0.8; max-width:440px; margin:0 auto 24px; font-size:15px; }
  .pc-newsletter-form { display:flex; gap:10px; max-width:440px; margin:0 auto; }
  .pc-newsletter-form input { flex:1; padding:14px 18px; border:none; border-radius:8px; font-family:'Inter',sans-serif; font-size:14px; outline:none; }
  .pc-newsletter-form button { padding:14px 24px; border:none; border-radius:8px; background:var(--pc-blue); color:#fff; font-family:'Inter',sans-serif; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; cursor:pointer; font-size:13px; transition:background .2s; }
  .pc-newsletter-form button:hover { background:var(--pc-blue-dark); }

  /* Testimonials */
  .pc-testimonials-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; }
  @media(max-width:767px){ .pc-testimonials-grid { grid-template-columns:1fr; } }
  .pc-testimonial { background:#fff; border:1px solid var(--pc-border); border-radius:12px; padding:24px; }
  .pc-testimonial-stars { color:var(--pc-accent); margin-bottom:12px; font-size:16px; letter-spacing:2px; }
  .pc-testimonial-text { font-family:'Inter',sans-serif; font-size:14px; color:var(--pc-gray); line-height:1.7; margin-bottom:16px; }
  .pc-testimonial-author { font-family:'Inter',sans-serif; font-size:13px; font-weight:700; color:var(--pc-dark); }
  .pc-testimonial-role { font-family:'Inter',sans-serif; font-size:11px; color:var(--pc-teal); margin-top:2px; }

  /* Brands */
  .pc-brands-grid { display:flex; flex-wrap:wrap; gap:14px; align-items:center; justify-content:center; }
  .pc-brand { padding:12px 24px; background:#fff; border:1px solid var(--pc-border); border-radius:8px; font-family:'Inter',sans-serif; font-size:14px; font-weight:600; color:var(--pc-dark); transition:border-color .2s; }
  .pc-brand:hover { border-color:var(--pc-blue); }

  /* Instagram */
  .pc-insta-grid { display:grid; grid-template-columns:repeat(5,1fr); gap:12px; }
  @media(max-width:767px){ .pc-insta-grid { grid-template-columns:repeat(3,1fr); } }
  .pc-insta-item { aspect-ratio:1; border-radius:12px; overflow:hidden; position:relative; }
  .pc-insta-item img { width:100%; height:100%; object-fit:cover; transition:transform .3s; }
  .pc-insta-item:hover img { transform:scale(1.08); }

  /* FAQs */
  .pc-faq-list { max-width:760px; margin:0 auto; }
  .pc-faq-item { background:#fff; border:1px solid var(--pc-border); border-radius:12px; margin-bottom:10px; overflow:hidden; }
  .pc-faq-q { padding:18px 24px; font-family:'Inter',sans-serif; font-size:15px; font-weight:600; color:var(--pc-dark); cursor:pointer; display:flex; justify-content:space-between; align-items:center; }
  .pc-faq-q::after { content:'+'; font-size:20px; color:var(--pc-blue); font-weight:700; }
  .pc-faq-item.open .pc-faq-q::after { content:'\2212'; }
  .pc-faq-a { max-height:0; overflow:hidden; transition:max-height .3s ease; padding:0 24px; }
  .pc-faq-item.open .pc-faq-a { max-height:300px; padding:0 24px 18px; }
  .pc-faq-a p { font-family:'Inter',sans-serif; font-size:14px; color:var(--pc-gray); line-height:1.7; margin:0; }

  /* Contact */
  .pc-contact-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; }
  @media(max-width:767px){ .pc-contact-grid { grid-template-columns:1fr; } }
  .pc-contact-card { background:#fff; border:1px solid var(--pc-border); border-radius:12px; padding:24px; text-align:center; transition:transform .2s, box-shadow .2s; }
  .pc-contact-card:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,94,184,0.08); }
  .pc-contact-icon { width:48px; height:48px; border-radius:12px; background:rgba(0,94,184,0.08); display:flex; align-items:center; justify-content:center; margin:0 auto 14px; color:var(--pc-blue); }
  .pc-contact-icon svg { width:22px; height:22px; }
  .pc-contact-label { font-family:'Inter',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.12em; color:var(--pc-teal); font-weight:600; margin-bottom:4px; }
  .pc-contact-value { font-family:'Inter',sans-serif; font-size:15px; font-weight:600; color:var(--pc-dark); }

  /* Store hours */
  .pc-hours-grid { max-width:520px; margin:0 auto; background:#fff; border:1px solid var(--pc-border); border-radius:12px; padding:0 24px; }
  .pc-hours-row { display:flex; justify-content:space-between; align-items:center; padding:14px 0; border-bottom:1px solid var(--pc-border); font-family:'Inter',sans-serif; font-size:15px; }
  .pc-hours-row:last-child { border-bottom:none; }
  .pc-hours-day { color:var(--pc-dark); font-weight:600; }
  .pc-hours-time { color:var(--pc-gray); }
  .pc-hours-full { width:100%; text-align:center; color:var(--pc-dark); }

  /* WhatsApp order modal */
  .wa-order-modal { display:none; position:fixed; inset:0; z-index:9999; }
  .wa-order-modal.open { display:block; }
  .wa-order-modal-overlay { position:absolute; inset:0; background:rgba(15,23,42,0.5); }
  .wa-order-modal-card { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:440px; max-width:calc(100vw - 32px); max-height:90vh; overflow-y:auto; background:#fff; border-radius:16px; box-shadow:0 24px 60px rgba(15,23,42,0.25); border:1px solid var(--pc-border); }
  .wa-order-modal-header { display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid var(--pc-border); }
  .wa-order-modal-title { font-family:'Inter',sans-serif; font-size:20px; font-weight:800; color:var(--pc-dark); margin:0; }
  .wa-order-modal-close { width:32px; height:32px; border-radius:8px; border:none; background:var(--pc-light); color:var(--pc-dark); cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background .2s; }
  .wa-order-modal-close:hover { background:var(--pc-border); }
  .wa-order-modal-body { padding:24px; }
  .wa-order-modal-product { display:flex; gap:14px; align-items:center; padding:14px; background:var(--pc-light); border-radius:8px; margin-bottom:20px; }
  .wa-order-modal-product img { width:56px; height:56px; border-radius:8px; object-fit:cover; flex-shrink:0; }
  .wa-order-modal-product-info { flex:1; min-width:0; }
  .wa-order-modal-product-name { font-family:'Inter',sans-serif; font-size:14px; font-weight:600; color:var(--pc-dark); margin:0 0 4px; line-height:1.3; }
  .wa-order-modal-product-price { font-family:'Inter',sans-serif; font-size:16px; font-weight:800; color:var(--pc-blue); }
  .wa-order-modal-fields { display:flex; flex-direction:column; gap:14px; }
  .wa-order-modal-fields label { font-family:'Inter',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.08em; font-weight:600; color:var(--pc-dark); margin-bottom:5px; display:block; }
  .wa-order-modal-fields input, .wa-order-modal-fields textarea { width:100%; padding:12px 14px; border:1px solid var(--pc-border); border-radius:8px; font-family:'Inter',sans-serif; font-size:14px; background:var(--pc-light); outline:none; transition:border-color .2s; color:var(--pc-dark); box-sizing:border-box; }
  .wa-order-modal-fields input:focus, .wa-order-modal-fields textarea:focus { border-color:var(--pc-blue); background:#fff; }
  .wa-order-modal-fields textarea { resize:vertical; min-height:64px; }
  .wa-order-modal-actions { display:flex; gap:10px; margin-top:20px; }
  .wa-order-modal-send { flex:1; padding:14px; border-radius:8px; border:none; background:#25D366; color:#fff; font-family:'Inter',sans-serif; font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; cursor:pointer; transition:background .2s; display:flex; align-items:center; justify-content:center; gap:8px; }
  .wa-order-modal-send:hover { background:#1FB855; }
  .wa-order-modal-cancel { padding:14px 20px; border-radius:8px; border:1px solid var(--pc-border); background:#fff; color:var(--pc-dark); font-family:'Inter',sans-serif; font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; cursor:pointer; transition:background .2s; }
  .wa-order-modal-cancel:hover { background:var(--pc-light); }

  /* Health services banner */
  .pc-services-strip { background:var(--pc-light); padding:40px 0; }
  .pc-services-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
  @media(max-width:1023px){ .pc-services-grid { grid-template-columns:repeat(2,1fr); } }
  @media(max-width:767px){ .pc-services-grid { grid-template-columns:1fr; } }
  .pc-service-card { background:#fff; border:1px solid var(--pc-border); border-radius:12px; padding:20px; text-align:center; transition:transform .2s, box-shadow .2s; }
  .pc-service-card:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,94,184,0.08); }
  .pc-service-icon { width:48px; height:48px; border-radius:12px; background:rgba(0,94,184,0.08); display:flex; align-items:center; justify-content:center; margin:0 auto 12px; color:var(--pc-blue); font-size:22px; }
  .pc-service-title { font-family:'Inter',sans-serif; font-size:14px; font-weight:700; color:var(--pc-dark); margin-bottom:4px; }
  .pc-service-text { font-family:'Inter',sans-serif; font-size:12px; color:var(--pc-gray); }
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
<div class="pc-hero">
  <div class="pc-hero-media"><img src="<?= $heroImg; ?>" alt="<?= htmlspecialchars($hero->banner_title ?? ($store->store_name ?? 'Pharmacy')); ?>"></div>
  <div class="pc-hero-gradient"></div>
  <div class="pc-hero-content">
    <div class="pc-hero-text">
      <p class="pc-hero-kicker"><?= htmlspecialchars($hero->banner_subtitle ?? 'Your Trusted Pharmacy'); ?></p>
      <h1 class="pc-hero-title"><?= htmlspecialchars($hero->banner_title ?? ($settings->store_headline ?: ($store->store_name ?? 'Your Health, Our Priority'))); ?></h1>
      <p class="pc-hero-lead"><?= htmlspecialchars($settings->store_subheadline ?: 'Quality medicines, expert advice, and caring service — everything your family needs to stay healthy.'); ?></p>
      <div class="pc-hero-actions">
        <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pc-btn pc-btn-white">Shop Medicines</a>
        <a href="https://wa.me/<?= $waNumber; ?>" target="_blank" class="pc-btn pc-btn-ghost">Upload Prescription</a>
      </div>
    </div>
    <div class="pc-hero-visual">
      <img src="<?= $heroImg; ?>" alt="<?= htmlspecialchars($hero->banner_title ?? 'Pharmacy'); ?>" style="width:100%;height:400px;object-fit:cover;border-radius:16px;">
      <div class="pc-trust-card">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        Licensed Pharmacy
      </div>
    </div>
  </div>
</div>
<?php else: ?>
<div class="pc-hero">
  <div class="pc-hero-gradient"></div>
  <div class="pc-hero-content">
    <div class="pc-hero-text">
      <p class="pc-hero-kicker"><?= htmlspecialchars($settings->store_subheadline ?: 'Your Trusted Pharmacy'); ?></p>
      <h1 class="pc-hero-title"><?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Your Health, Our Priority')); ?></h1>
      <p class="pc-hero-lead"><?= htmlspecialchars($settings->store_description ?: 'Quality medicines, expert advice, and caring service — everything your family needs to stay healthy.'); ?></p>
      <div class="pc-hero-actions">
        <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pc-btn pc-btn-white">Shop Medicines</a>
        <a href="https://wa.me/<?= $waNumber; ?>" target="_blank" class="pc-btn pc-btn-ghost">Upload Prescription</a>
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
      $pcBadges = json_decode($settings->trust_badges_json ?? '', true);
      if(empty($pcBadges) || !is_array($pcBadges)) break;
?>
<!-- TRUST BADGES -->
<div class="pc-trust">
  <div class="pc-container">
    <div class="pc-trust-grid">
      <?php foreach(array_slice($pcBadges, 0, 4) as $b): ?>
      <div class="pc-trust-item">
        <div class="pc-trust-item-icon"><?= !empty($b['icon']) ? $b['icon'] : '&#10004;'; ?></div>
        <div class="pc-trust-item-title"><?= htmlspecialchars($b['title'] ?? ''); ?></div>
        <div class="pc-trust-item-text"><?= htmlspecialchars($b['desc'] ?? ''); ?></div>
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
<div class="pc-section">
  <div class="pc-container">
    <div class="pc-promo">
      <?php if($promoImg): ?>
      <div class="pc-promo-media"><img src="<?= $promoImg; ?>" alt="<?= htmlspecialchars($promo->banner_title ?? 'Promo'); ?>"></div>
      <?php endif; ?>
      <div class="pc-promo-body">
        <div class="pc-promo-kicker"><?= htmlspecialchars($promo->banner_subtitle ?? 'Special Offer'); ?></div>
        <h3 class="pc-promo-title"><?= htmlspecialchars($promo->banner_title ?? 'Special Offer'); ?></h3>
        <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pc-btn pc-btn-white">Shop Now</a>
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
<div class="pc-section" style="background:var(--pc-light);">
  <div class="pc-container">
    <div class="pc-section-head">
      <div>
        <div class="pc-section-label">Browse</div>
        <h2 class="pc-section-title">Shop by Health Category</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pc-section-link">View All</a>
    </div>
    <div class="pc-cat-grid">
      <?php foreach(array_slice($categories, 0, 8) as $cat):
        $catImg = (!empty($cat->category_image) && file_exists($cat->category_image)) ? base_url($cat->category_image) : '';
        $itemCount = $cat->item_count ?? 0;
      ?>
      <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="pc-cat-card">
        <div class="pc-cat-card-media">
          <?php if($catImg): ?>
          <img src="<?= $catImg; ?>" alt="<?= htmlspecialchars($cat->category_name); ?>" loading="lazy">
          <?php else: ?>
          <div class="pc-cat-placeholder"><span><?= htmlspecialchars(substr($cat->category_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="pc-cat-card-overlay"></div>
        <div class="pc-cat-card-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </div>
        <div class="pc-cat-card-body">
          <div class="pc-cat-card-name"><?= htmlspecialchars($cat->category_name); ?></div>
          <?php if($itemCount > 0): ?>
          <div class="pc-cat-card-count"><?= $itemCount; ?> items</div>
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
<div class="pc-section">
  <div class="pc-container">
    <div class="pc-section-head">
      <div>
        <div class="pc-section-label">Popular Now</div>
        <h2 class="pc-section-title">Featured Products</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pc-section-link">Shop All</a>
    </div>
    <div class="pc-product-grid">
      <?php foreach(array_slice($featured_products, 0, 8) as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $oldPrice = $p->original_price ?? $p->sales_price;
        $hasDiscount = $oldPrice > $price;
        $discountPct = $hasDiscount ? round((($oldPrice - $price) / $oldPrice) * 100) : 0;
        $img = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
      ?>
      <div class="pc-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, <?= $hasDiscount ? $oldPrice : 0; ?>)">
        <?php if($hasDiscount && $discountPct > 0): ?>
        <span class="pc-product-badge">-<?= $discountPct; ?>%</span>
        <?php endif; ?>
        <button class="pc-product-wishlist" onclick="event.stopPropagation();" aria-label="Wishlist"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></button>
        <div class="pc-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
          <?php else: ?>
          <div class="pc-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="pc-product-body">
          <?php if(!empty($p->category_name)): ?>
          <div class="pc-product-brand"><?= htmlspecialchars($p->category_name); ?></div>
          <?php endif; ?>
          <div class="pc-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="pc-product-footer">
            <div class="pc-product-price"><?= sf_currency($price, $cur); ?><?php if($hasDiscount): ?><span class="old"><?= sf_currency($oldPrice, $cur); ?></span><?php endif; ?></div>
            <div class="pc-card-actions">
              <button class="pc-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add to cart"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="pc-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $p->id; ?>,'<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',<?= $p->stock; ?>)" aria-label="Order via WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
            </div>
          </div>
          <?php if($p->stock <= 0 && !($settings->allow_backorder ?? false)): ?>
          <div class="pc-product-stock">Out of Stock</div>
          <?php else: ?>
          <div class="pc-product-stock in">In Stock</div>
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
<div class="pc-section" style="background:var(--pc-light);">
  <div class="pc-container">
    <div class="pc-section-head">
      <div>
        <div class="pc-section-label">Services</div>
        <h2 class="pc-section-title">Pharmacy Services</h2>
      </div>
    </div>
    <div class="pc-product-grid">
      <?php foreach(array_slice($featured_services, 0, 4) as $s):
        $sPrice = $s->effective_price ?? $s->sales_price ?? $s->price ?? 0;
        $sImg = (!empty($s->item_image) && file_exists($s->item_image)) ? base_url($s->item_image) : (!empty($s->service_image) && file_exists($s->service_image) ? base_url($s->service_image) : '');
      ?>
      <div class="pc-product-card" onclick="openProductModal(<?= $s->id; ?>, '<?= htmlspecialchars(addslashes($s->item_name ?? $s->service_name ?? '')); ?>', <?= $sPrice; ?>, '<?= $s->item_image ?? $s->service_image ?? ''; ?>', '<?= htmlspecialchars(addslashes($s->description ?? '')); ?>', 999, 0)">
        <div class="pc-product-media">
          <?php if($sImg): ?>
          <img src="<?= $sImg; ?>" alt="<?= htmlspecialchars($s->item_name ?? $s->service_name ?? ''); ?>" loading="lazy">
          <?php else: ?>
          <div class="pc-product-placeholder"><span><?= htmlspecialchars(substr($s->item_name ?? $s->service_name ?? 'S', 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="pc-product-body">
          <div class="pc-product-name"><?= htmlspecialchars($s->item_name ?? $s->service_name ?? ''); ?></div>
          <div class="pc-product-footer">
            <div class="pc-product-price"><?= sf_currency($sPrice, $cur); ?></div>
            <div class="pc-card-actions">
              <button class="pc-add-btn" onclick="event.stopPropagation();addToCart(<?= $s->id; ?>,'service','<?= htmlspecialchars(addslashes($s->item_name ?? $s->service_name ?? '')); ?>',<?= $sPrice; ?>,'<?= $s->item_image ?? $s->service_image ?? ''; ?>',1,999)" aria-label="Add to cart"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="pc-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $s->id; ?>,'<?= htmlspecialchars(addslashes($s->item_name ?? $s->service_name ?? '')); ?>',<?= $sPrice; ?>,'<?= $s->item_image ?? $s->service_image ?? ''; ?>',999)" aria-label="Order via WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
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
<div class="pc-section">
  <div class="pc-container">
    <div class="pc-section-head">
      <div>
        <div class="pc-section-label">Customer Favorites</div>
        <h2 class="pc-section-title">Best Sellers</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pc-section-link">View All</a>
    </div>
    <div class="pc-product-grid">
      <?php foreach(array_slice($best_sellers, 0, 4) as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $img = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
      ?>
      <div class="pc-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, 0)">
        <div class="pc-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
          <?php else: ?>
          <div class="pc-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="pc-product-body">
          <div class="pc-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="pc-product-footer">
            <div class="pc-product-price"><?= sf_currency($price, $cur); ?></div>
            <div class="pc-card-actions">
              <button class="pc-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add to cart"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="pc-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $p->id; ?>,'<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',<?= $p->stock; ?>)" aria-label="Order via WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
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
<div class="pc-section" style="background:var(--pc-light);">
  <div class="pc-container">
    <div class="pc-section-head">
      <div>
        <div class="pc-section-label">Just In</div>
        <h2 class="pc-section-title">New Arrivals</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pc-section-link">View All</a>
    </div>
    <div class="pc-product-grid">
      <?php foreach(array_slice($new_arrivals, 0, 4) as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $img = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
      ?>
      <div class="pc-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, 0)">
        <span class="pc-product-badge">New</span>
        <div class="pc-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
          <?php else: ?>
          <div class="pc-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="pc-product-body">
          <div class="pc-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="pc-product-footer">
            <div class="pc-product-price"><?= sf_currency($price, $cur); ?></div>
            <div class="pc-card-actions">
              <button class="pc-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add to cart"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="pc-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $p->id; ?>,'<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',<?= $p->stock; ?>)" aria-label="Order via WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
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
<div class="pc-section">
  <div class="pc-container">
    <div class="pc-section-head" style="justify-content:center;text-align:center;">
      <h2 class="pc-section-title">Our Trusted Brands</h2>
    </div>
    <div class="pc-brands-grid">
      <?php foreach($brands as $brand): ?>
      <div class="pc-brand"><?= htmlspecialchars($brand->brand_name ?? $brand->name ?? ''); ?></div>
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
<div class="pc-section" style="background:var(--pc-light);">
  <div class="pc-container">
    <div class="pc-section-head">
      <div>
        <div class="pc-section-label">Reviews</div>
        <h2 class="pc-section-title">What Our Customers Say</h2>
      </div>
    </div>
    <div class="pc-testimonials-grid">
      <?php foreach(array_slice($testimonials, 0, 3) as $t): ?>
      <div class="pc-testimonial">
        <div class="pc-testimonial-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
        <div class="pc-testimonial-text"><?= htmlspecialchars($t->testimonial_text ?? $t->message ?? ''); ?></div>
        <div class="pc-testimonial-author"><?= htmlspecialchars($t->customer_name ?? $t->author ?? ''); ?></div>
        <?php if(!empty($t->role ?? $t->customer_role)): ?>
        <div class="pc-testimonial-role"><?= htmlspecialchars($t->role ?? $t->customer_role); ?></div>
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
<div class="pc-section">
  <div class="pc-container">
    <div class="pc-section-head" style="justify-content:center;text-align:center;">
      <h2 class="pc-section-title">Follow Us</h2>
    </div>
    <div class="pc-insta-grid">
      <?php foreach(array_slice($instagram_posts, 0, 10) as $post): ?>
      <a href="<?= htmlspecialchars($post->permalink ?? $post->link ?? '#'); ?>" target="_blank" class="pc-insta-item">
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
    // FAQS
    // =====================================================
    case 'faqs':
      if(!empty($faqs)):
?>
<!-- FAQS -->
<div class="pc-section">
  <div class="pc-container">
    <div class="pc-section-head" style="justify-content:center;text-align:center;">
      <h2 class="pc-section-title">Frequently Asked Questions</h2>
    </div>
    <div class="pc-faq-list">
      <?php foreach($faqs as $faq): ?>
      <div class="pc-faq-item" onclick="this.classList.toggle('open')">
        <div class="pc-faq-q"><?= htmlspecialchars($faq->question ?? $faq->faq_question ?? ''); ?></div>
        <div class="pc-faq-a"><p><?= htmlspecialchars($faq->answer ?? $faq->faq_answer ?? ''); ?></p></div>
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
<div class="pc-section" style="background:var(--pc-light);">
  <div class="pc-container">
    <div class="pc-section-head" style="justify-content:center;text-align:center;">
      <h2 class="pc-section-title">Get In Touch</h2>
    </div>
    <div class="pc-contact-grid">
      <?php if(!empty($settings->store_phone)): ?>
      <div class="pc-contact-card">
        <div class="pc-contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></div>
        <div class="pc-contact-label">Phone</div>
        <div class="pc-contact-value"><?= htmlspecialchars($settings->store_phone); ?></div>
      </div>
      <?php endif; ?>
      <?php if(!empty($settings->store_email)): ?>
      <div class="pc-contact-card">
        <div class="pc-contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
        <div class="pc-contact-label">Email</div>
        <div class="pc-contact-value"><?= htmlspecialchars($settings->store_email); ?></div>
      </div>
      <?php endif; ?>
      <?php if($waNumber): ?>
      <div class="pc-contact-card">
        <div class="pc-contact-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg></div>
        <div class="pc-contact-label">WhatsApp</div>
        <div class="pc-contact-value"><?= htmlspecialchars($settings->whatsapp_number); ?></div>
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
<div class="pc-section">
  <div class="pc-container">
    <div class="pc-wa-cta">
      <div class="pc-wa-cta-title">Need a Prescription Refilled?</div>
      <p class="pc-wa-cta-text">Send us your prescription on WhatsApp and we'll have it ready for pickup or delivery.</p>
      <a href="https://wa.me/<?= $waNumber; ?>" target="_blank" class="pc-btn">Chat with a Pharmacist</a>
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
<div class="pc-section">
  <div class="pc-container">
    <div class="pc-newsletter">
      <div class="pc-newsletter-title">Stay Healthy, Stay Informed</div>
      <p class="pc-newsletter-text">Get health tips, new product alerts, and exclusive offers delivered to your inbox.</p>
      <form class="pc-newsletter-form" onsubmit="return mpNewsletterSubmit(event)">
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
<div class="pc-section" style="background:var(--pc-light);">
  <div class="pc-container">
    <div class="pc-section-head" style="justify-content:center;text-align:center;">
      <h2 class="pc-section-title">Opening Hours</h2>
    </div>
    <div class="pc-hours-grid">
      <?php foreach($business_hours as $line):
        $line = trim($line);
        if(strpos($line, ':') !== false):
          list($day, $time) = array_map('trim', explode(':', $line, 2)); ?>
      <div class="pc-hours-row">
        <span class="pc-hours-day"><?= htmlspecialchars(ucfirst($day)); ?></span>
        <span class="pc-hours-time"><?= htmlspecialchars($time); ?></span>
      </div>
      <?php else: ?>
      <div class="pc-hours-row">
        <span class="pc-hours-full"><?= htmlspecialchars($line); ?></span>
      </div>
      <?php endif; endforeach; ?>
    </div>
  </div>
</div>
<?php
      endif;
      break;

    // =====================================================
    // HEALTH SERVICES STRIP
    // =====================================================
    case 'health_services':
?>
<!-- HEALTH SERVICES -->
<div class="pc-services-strip">
  <div class="pc-container">
    <div class="pc-services-grid">
      <div class="pc-service-card">
        <div class="pc-service-icon">&#128137;</div>
        <div class="pc-service-title">Prescription Refills</div>
        <div class="pc-service-text">Easy online refills</div>
      </div>
      <div class="pc-service-card">
        <div class="pc-service-icon">&#129657;</div>
        <div class="pc-service-title">Free Consultation</div>
        <div class="pc-service-text">Talk to a pharmacist</div>
      </div>
      <div class="pc-service-card">
        <div class="pc-service-icon">&#128230;</div>
        <div class="pc-service-title">Fast Delivery</div>
        <div class="pc-service-text">Same-day available</div>
      </div>
      <div class="pc-service-card">
        <div class="pc-service-icon">&#128138;</div>
        <div class="pc-service-title">Genuine Products</div>
        <div class="pc-service-text">100% authentic</div>
      </div>
    </div>
  </div>
</div>
<?php
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
  const storeName = '<?= htmlspecialchars(addslashes($store->store_name ?? 'Pharmacy')); ?>';
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
