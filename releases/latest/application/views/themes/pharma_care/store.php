<?php
/**
 * Pharma Care — Pharmacy Homepage
 * Warm community pharmacy design with deep teal, amber accents,
 * Lora serif headings, and a trusted neighbourhood feel.
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
    --pcr-teal:#0F766E;
    --pcr-teal-dark:#115E59;
    --pcr-amber:#D97706;
    --pcr-amber-dark:#B45309;
    --pcr-cream:#FFFBF5;
    --pcr-cream-dark:#FEF7ED;
    --pcr-dark:#1C1917;
    --pcr-gray:#57534E;
    --pcr-border:#E7E5E4;
    --pcr-white:#FFFFFF;
  }

  .theme-pharma_care .mp-topbar,
  .theme-pharma_care .mp-announcement,
  .theme-pharma_care .mp-nav,
  .theme-pharma_care .mp-header,
  .theme-pharma_care .mp-mobile-menu-btn,
  .theme-pharma_care .mp-footer-space { display:none !important; }

  .pcr-container { max-width:1400px; margin:0 auto; padding:0 24px; }
  @media(max-width:767px){ .pcr-container { padding:0 16px; } }

  /* Hero */
  .pcr-hero { position:relative; background:linear-gradient(135deg, #0F766E 0%, #134E4A 50%, #115E59 100%); color:#fff; overflow:hidden; min-height:580px; display:flex; align-items:center; }
  @media(max-width:767px){ .pcr-hero { min-height:520px; } }
  .pcr-hero::before { content:''; position:absolute; inset:0; background-image:url("data:image/svg+xml,%3Csvg width='80' height='80' viewBox='0 0 80 80' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Cpath d='M40 20h2v10h10v2H42v10h-2V32H30v-2h10z'/%3E%3C/g%3E%3C/svg%3E"); background-size:80px 80px; }
  .pcr-hero-media { position:absolute; inset:0; opacity:0.2; }
  .pcr-hero-media img { width:100%; height:100%; object-fit:cover; }
  .pcr-hero-content { position:relative; z-index:2; padding:100px 24px 120px; max-width:1400px; margin:0 auto; width:100%; display:flex; align-items:center; gap:60px; }
  @media(max-width:1023px){ .pcr-hero-content { flex-direction:column; gap:40px; padding:80px 24px 100px; } }
  @media(max-width:767px){ .pcr-hero-content { padding:60px 16px 80px; } }
  .pcr-hero-text { flex:1; max-width:620px; }
  .pcr-hero-kicker { font-family:'Inter',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.18em; color:#FDBA74; margin-bottom:16px; font-weight:600; }
  .pcr-hero-title { font-family:'Lora',serif; font-size:clamp(38px,5vw,62px); line-height:1.1; font-weight:700; margin:0 0 20px; letter-spacing:-0.02em; }
  .pcr-hero-lead { font-family:'Inter',sans-serif; font-size:clamp(15px,1.8vw,18px); line-height:1.7; opacity:0.95; margin-bottom:32px; max-width:520px; }
  .pcr-hero-actions { display:flex; gap:14px; flex-wrap:wrap; }
  .pcr-hero-visual { flex:1; max-width:480px; position:relative; }
  .pcr-hero-visual img { width:100%; height:400px; object-fit:cover; border-radius:20px; box-shadow:0 24px 60px rgba(0,0,0,0.3); }
  @media(max-width:1023px){ .pcr-hero-visual { max-width:100%; } .pcr-hero-visual img { height:300px; } }
  @media(max-width:767px){ .pcr-hero-visual img { height:220px; } }

  .pcr-hero-badge { position:absolute; bottom:-20px; left:-20px; background:#fff; color:var(--pcr-dark); padding:16px 20px; border-radius:12px; box-shadow:0 12px 32px rgba(0,0,0,0.2); display:flex; align-items:center; gap:10px; font-family:'Inter',sans-serif; font-size:12px; font-weight:600; }
  .pcr-hero-badge svg { width:22px; height:22px; color:var(--pcr-amber); flex-shrink:0; }
  @media(max-width:767px){ .pcr-hero-badge { left:12px; bottom:-12px; padding:12px 16px; } }

  /* Buttons */
  .pcr-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:15px 34px; border-radius:8px; font-family:'Inter',sans-serif; font-size:13px; font-weight:600; transition:transform .15s, background .2s, color .2s; cursor:pointer; border:none; text-decoration:none; }
  .pcr-btn:active { transform:scale(0.98); }
  .pcr-btn-amber { background:var(--pcr-amber); color:#fff; }
  .pcr-btn-amber:hover { background:var(--pcr-amber-dark); }
  .pcr-btn-cream { background:var(--pcr-cream); color:var(--pcr-dark); }
  .pcr-btn-cream:hover { background:#fff; }
  .pcr-btn-ghost { background:transparent; color:#fff; border:1.5px solid rgba(255,255,255,0.5); }
  .pcr-btn-ghost:hover { background:rgba(255,255,255,0.1); }
  .pcr-btn-outline { background:#fff; color:var(--pcr-teal); border:1.5px solid var(--pcr-teal); }
  .pcr-btn-outline:hover { background:var(--pcr-teal); color:#fff; }
  .pcr-btn-wa { background:#25D366; color:#fff; }
  .pcr-btn-wa:hover { background:#1DA851; }
  .pcr-btn-teal { background:var(--pcr-teal); color:#fff; }
  .pcr-btn-teal:hover { background:var(--pcr-teal-dark); }

  /* Sections */
  .pcr-section { padding:72px 0; }
  @media(max-width:767px){ .pcr-section { padding:44px 0; } }
  .pcr-section-head { display:flex; align-items:flex-end; justify-content:space-between; gap:16px; margin-bottom:36px; }
  .pcr-section-label { font-family:'Inter',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.16em; color:var(--pcr-amber); font-weight:600; margin-bottom:8px; }
  .pcr-section-title { font-family:'Lora',serif; font-size:32px; margin:0; font-weight:700; color:var(--pcr-dark); letter-spacing:-0.01em; }
  .pcr-section-link { font-family:'Inter',sans-serif; font-size:13px; font-weight:600; color:var(--pcr-teal); text-decoration:none; transition:color .2s; }
  .pcr-section-link:hover { color:var(--pcr-teal-dark); }

  /* Product grid */
  .pcr-product-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:20px; }
  @media(max-width:1023px){ .pcr-product-grid { grid-template-columns:repeat(3,1fr); } }
  @media(max-width:767px){ .pcr-product-grid { grid-template-columns:repeat(2,1fr); gap:12px; } }
  .pcr-product-card { position:relative; background:#fff; border-radius:16px; overflow:hidden; border:1px solid var(--pcr-border); transition:transform .25s, box-shadow .25s; cursor:pointer; }
  .pcr-product-card:hover { transform:translateY(-6px); box-shadow:0 16px 40px rgba(15,118,110,0.12); }
  .pcr-product-wishlist { position:absolute; top:12px; right:12px; width:36px; height:36px; border-radius:8px; background:#fff; display:flex; align-items:center; justify-content:center; z-index:2; transition:color .2s, transform .2s; box-shadow:0 2px 8px rgba(0,0,0,0.06); border:none; cursor:pointer; color:var(--pcr-gray); }
  .pcr-product-wishlist:hover { color:var(--pcr-amber); transform:scale(1.08); }
  .pcr-product-badge { position:absolute; top:12px; left:12px; background:var(--pcr-amber); color:#fff; font-family:'Inter',sans-serif; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; padding:5px 10px; border-radius:8px; z-index:2; }
  .pcr-product-badge.new { background:var(--pcr-teal); }
  .pcr-product-media { aspect-ratio:1/1; overflow:hidden; background:var(--pcr-cream); }
  .pcr-product-media img { width:100%; height:100%; object-fit:cover; transition:transform .4s ease; }
  .pcr-product-card:hover .pcr-product-media img { transform:scale(1.05); }
  .pcr-product-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--pcr-cream); color:var(--pcr-teal); }
  .pcr-product-placeholder span { font-family:'Lora',serif; font-size:40px; font-weight:700; }
  .pcr-product-body { padding:18px; }
  .pcr-product-brand { font-family:'Inter',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.08em; color:var(--pcr-teal); font-weight:600; margin-bottom:4px; }
  .pcr-product-name { font-family:'Lora',serif; font-size:15px; font-weight:600; margin-bottom:10px; line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; min-height:42px; color:var(--pcr-dark); }
  .pcr-product-footer { display:flex; flex-direction:column; gap:8px; }
  .pcr-product-price { font-family:'Inter',sans-serif; font-size:18px; font-weight:700; color:var(--pcr-dark); }
  .pcr-product-price .old { font-family:'Inter',sans-serif; font-size:13px; color:var(--pcr-gray); text-decoration:line-through; margin-left:6px; font-weight:400; }
  .pcr-card-actions { display:flex; gap:8px; }
  @media(max-width:767px){ .pcr-card-actions { flex-direction:column; gap:6px; } }
  .pcr-add-btn { flex:1; padding:11px 14px; border-radius:8px; background:var(--pcr-teal); color:#fff; display:flex; align-items:center; justify-content:center; gap:6px; transition:background .2s, transform .15s; border:none; cursor:pointer; font-family:'Inter',sans-serif; font-size:12px; font-weight:600; }
  .pcr-add-btn:hover { background:var(--pcr-teal-dark); }
  .pcr-add-btn:active { transform:scale(0.97); }
  .pcr-wa-btn { flex:1; padding:11px 14px; border-radius:8px; background:#25D366; color:#fff; display:flex; align-items:center; justify-content:center; gap:6px; transition:background .2s, transform .15s; border:none; cursor:pointer; font-family:'Inter',sans-serif; font-size:12px; font-weight:600; }
  .pcr-wa-btn:hover { background:#1FB855; }
  .pcr-wa-btn:active { transform:scale(0.97); }
  .pcr-product-stock { font-family:'Inter',sans-serif; font-size:11px; color:#DC2626; font-weight:600; margin-top:6px; text-transform:uppercase; letter-spacing:0.06em; }
  .pcr-product-stock.in { color:var(--pcr-teal); }

  /* Category cards */
  .pcr-cat-grid { display:grid; grid-template-columns:repeat(6,1fr); gap:14px; }
  @media(max-width:1023px){ .pcr-cat-grid { grid-template-columns:repeat(4,1fr); } }
  @media(max-width:767px){ .pcr-cat-grid { grid-template-columns:repeat(3,1fr); gap:10px; } }
  .pcr-cat-card { position:relative; border-radius:16px; overflow:hidden; aspect-ratio:1/1; text-decoration:none; color:inherit; display:block; transition:transform .25s, box-shadow .25s; background:#fff; border:1px solid var(--pcr-border); }
  .pcr-cat-card:hover { transform:translateY(-6px); box-shadow:0 16px 40px rgba(15,118,110,0.12); }
  .pcr-cat-card-media { position:absolute; inset:0; background:var(--pcr-cream); }
  .pcr-cat-card-media img { width:100%; height:100%; object-fit:cover; transition:transform .4s ease; }
  .pcr-cat-card:hover .pcr-cat-card-media img { transform:scale(1.08); }
  .pcr-cat-card-overlay { position:absolute; inset:0; background:linear-gradient(180deg, transparent 30%, rgba(28,25,23,0.78) 100%); }
  .pcr-cat-card-body { position:absolute; bottom:0; left:0; right:0; padding:16px; z-index:2; }
  .pcr-cat-card-name { font-family:'Lora',serif; font-size:15px; font-weight:600; color:#fff; margin-bottom:2px; }
  .pcr-cat-card-count { font-family:'Inter',sans-serif; font-size:11px; color:#FDBA74; }
  .pcr-cat-card-icon { position:absolute; top:12px; right:12px; width:32px; height:32px; border-radius:8px; background:var(--pcr-teal); display:flex; align-items:center; justify-content:center; opacity:0; transform:translateY(-4px); transition:opacity .25s, transform .25s; }
  .pcr-cat-card:hover .pcr-cat-card-icon { opacity:1; transform:translateY(0); }
  .pcr-cat-card-icon svg { width:16px; height:16px; color:#fff; }
  .pcr-cat-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--pcr-cream); }
  .pcr-cat-placeholder span { font-family:'Lora',serif; font-size:40px; font-weight:700; color:var(--pcr-teal); }

  /* Trust badges */
  .pcr-trust { background:var(--pcr-cream); padding:56px 0; }
  .pcr-trust-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:20px; }
  @media(max-width:1023px){ .pcr-trust-grid { grid-template-columns:repeat(2,1fr); } }
  @media(max-width:767px){ .pcr-trust-grid { grid-template-columns:1fr; } }
  .pcr-trust-item { text-align:center; padding:24px 16px; background:#fff; border-radius:16px; border:1px solid var(--pcr-border); transition:transform .2s; }
  .pcr-trust-item:hover { transform:translateY(-4px); }
  .pcr-trust-item-icon { width:52px; height:52px; border-radius:16px; background:rgba(15,118,110,0.08); display:flex; align-items:center; justify-content:center; font-size:24px; margin:0 auto 14px; color:var(--pcr-teal); }
  .pcr-trust-item-title { font-family:'Lora',serif; font-size:16px; font-weight:700; color:var(--pcr-dark); margin-bottom:6px; }
  .pcr-trust-item-text { font-family:'Inter',sans-serif; font-size:12px; color:var(--pcr-gray); line-height:1.5; }

  /* Promo banner */
  .pcr-promo { background:linear-gradient(135deg, var(--pcr-teal) 0%, var(--pcr-teal-dark) 100%); border-radius:20px; overflow:hidden; display:flex; align-items:center; min-height:220px; color:#fff; }
  @media(max-width:767px){ .pcr-promo { flex-direction:column; min-height:auto; } }
  .pcr-promo-media { flex:1; min-height:220px; background:rgba(255,255,255,0.1); }
  .pcr-promo-media img { width:100%; height:100%; object-fit:cover; }
  .pcr-promo-body { flex:1; padding:40px; }
  @media(max-width:767px){ .pcr-promo-body { padding:28px; } }
  .pcr-promo-kicker { font-family:'Inter',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.16em; color:#FDBA74; font-weight:600; margin-bottom:8px; }
  .pcr-promo-title { font-family:'Lora',serif; font-size:clamp(22px,2.5vw,30px); font-weight:700; margin-bottom:10px; }
  .pcr-promo-text { font-family:'Inter',sans-serif; font-size:15px; opacity:0.95; line-height:1.6; margin-bottom:20px; }

  /* Prescription CTA */
  .pcr-rx-cta { background:var(--pcr-cream-dark); color:var(--pcr-dark); padding:48px 24px; text-align:center; border-radius:20px; border:1px solid var(--pcr-border); }
  .pcr-rx-cta-title { font-family:'Lora',serif; font-size:clamp(22px,2.5vw,30px); font-weight:700; margin-bottom:10px; }
  .pcr-rx-cta-text { font-family:'Inter',sans-serif; color:var(--pcr-gray); max-width:440px; margin:0 auto 24px; font-size:15px; line-height:1.6; }
  .pcr-rx-cta .pcr-btn { background:var(--pcr-amber); color:#fff; }
  .pcr-rx-cta .pcr-btn:hover { background:var(--pcr-amber-dark); }

  /* WhatsApp CTA */
  .pcr-wa-cta { background:linear-gradient(135deg, #25D366, #1DA851); color:#fff; padding:48px 24px; text-align:center; border-radius:20px; }
  .pcr-wa-cta-title { font-family:'Lora',serif; font-size:clamp(22px,2.5vw,30px); font-weight:700; margin-bottom:10px; }
  .pcr-wa-cta-text { font-family:'Inter',sans-serif; opacity:0.95; max-width:440px; margin:0 auto 24px; font-size:15px; line-height:1.6; }
  .pcr-wa-cta .pcr-btn { background:#fff; color:#1DA851; }
  .pcr-wa-cta .pcr-btn:hover { background:var(--pcr-dark); color:#fff; }

  /* Newsletter */
  .pcr-newsletter { background:var(--pcr-teal); color:#fff; padding:48px 24px; text-align:center; border-radius:20px; }
  .pcr-newsletter-title { font-family:'Lora',serif; font-size:clamp(22px,2.5vw,30px); font-weight:700; margin-bottom:10px; }
  .pcr-newsletter-text { font-family:'Inter',sans-serif; opacity:0.9; max-width:440px; margin:0 auto 24px; font-size:15px; }
  .pcr-newsletter-form { display:flex; gap:10px; max-width:440px; margin:0 auto; }
  .pcr-newsletter-form input { flex:1; padding:14px 18px; border:none; border-radius:8px; font-family:'Inter',sans-serif; font-size:14px; outline:none; }
  .pcr-newsletter-form button { padding:14px 24px; border:none; border-radius:8px; background:var(--pcr-amber); color:#fff; font-family:'Inter',sans-serif; font-weight:600; cursor:pointer; font-size:13px; transition:background .2s; }
  .pcr-newsletter-form button:hover { background:var(--pcr-amber-dark); }

  /* Testimonials */
  .pcr-testimonials-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; }
  @media(max-width:767px){ .pcr-testimonials-grid { grid-template-columns:1fr; } }
  .pcr-testimonial { background:#fff; border:1px solid var(--pcr-border); border-radius:16px; padding:24px; }
  .pcr-testimonial-stars { color:var(--pcr-amber); margin-bottom:12px; font-size:16px; letter-spacing:2px; }
  .pcr-testimonial-text { font-family:'Inter',sans-serif; font-size:14px; color:var(--pcr-gray); line-height:1.7; margin-bottom:16px; }
  .pcr-testimonial-author { font-family:'Lora',serif; font-size:15px; font-weight:700; color:var(--pcr-dark); }
  .pcr-testimonial-role { font-family:'Inter',sans-serif; font-size:12px; color:var(--pcr-teal); margin-top:2px; }

  /* Brands */
  .pcr-brands-grid { display:flex; flex-wrap:wrap; gap:14px; align-items:center; justify-content:center; }
  .pcr-brand { padding:12px 24px; background:#fff; border:1px solid var(--pcr-border); border-radius:8px; font-family:'Inter',sans-serif; font-size:14px; font-weight:600; color:var(--pcr-dark); transition:border-color .2s, color .2s; }
  .pcr-brand:hover { border-color:var(--pcr-teal); color:var(--pcr-teal); }

  /* Instagram */
  .pcr-insta-grid { display:grid; grid-template-columns:repeat(5,1fr); gap:12px; }
  @media(max-width:767px){ .pcr-insta-grid { grid-template-columns:repeat(3,1fr); } }
  .pcr-insta-item { aspect-ratio:1; border-radius:16px; overflow:hidden; position:relative; }
  .pcr-insta-item img { width:100%; height:100%; object-fit:cover; transition:transform .3s; }
  .pcr-insta-item:hover img { transform:scale(1.08); }

  /* FAQs */
  .pcr-faq-list { max-width:760px; margin:0 auto; }
  .pcr-faq-item { background:#fff; border:1px solid var(--pcr-border); border-radius:16px; margin-bottom:10px; overflow:hidden; }
  .pcr-faq-q { padding:18px 24px; font-family:'Lora',serif; font-size:15px; font-weight:600; color:var(--pcr-dark); cursor:pointer; display:flex; justify-content:space-between; align-items:center; }
  .pcr-faq-q::after { content:'+'; font-size:20px; color:var(--pcr-teal); font-weight:700; }
  .pcr-faq-item.open .pcr-faq-q::after { content:'\2212'; }
  .pcr-faq-a { max-height:0; overflow:hidden; transition:max-height .3s ease; padding:0 24px; }
  .pcr-faq-item.open .pcr-faq-a { max-height:300px; padding:0 24px 18px; }
  .pcr-faq-a p { font-family:'Inter',sans-serif; font-size:14px; color:var(--pcr-gray); line-height:1.7; margin:0; }

  /* Contact */
  .pcr-contact-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; }
  @media(max-width:767px){ .pcr-contact-grid { grid-template-columns:1fr; } }
  .pcr-contact-card { background:#fff; border:1px solid var(--pcr-border); border-radius:16px; padding:24px; text-align:center; transition:transform .2s, box-shadow .2s; }
  .pcr-contact-card:hover { transform:translateY(-4px); box-shadow:0 12px 32px rgba(15,118,110,0.1); }
  .pcr-contact-icon { width:52px; height:52px; border-radius:16px; background:rgba(15,118,110,0.08); display:flex; align-items:center; justify-content:center; margin:0 auto 14px; color:var(--pcr-teal); }
  .pcr-contact-icon svg { width:22px; height:22px; }
  .pcr-contact-label { font-family:'Inter',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.12em; color:var(--pcr-amber); font-weight:600; margin-bottom:4px; }
  .pcr-contact-value { font-family:'Inter',sans-serif; font-size:15px; font-weight:600; color:var(--pcr-dark); }

  /* Store hours */
  .pcr-hours-grid { max-width:520px; margin:0 auto; background:#fff; border:1px solid var(--pcr-border); border-radius:16px; padding:0 24px; }
  .pcr-hours-row { display:flex; justify-content:space-between; align-items:center; padding:14px 0; border-bottom:1px solid var(--pcr-border); font-family:'Inter',sans-serif; font-size:15px; }
  .pcr-hours-row:last-child { border-bottom:none; }
  .pcr-hours-day { color:var(--pcr-dark); font-weight:600; }
  .pcr-hours-time { color:var(--pcr-gray); }
  .pcr-hours-full { width:100%; text-align:center; color:var(--pcr-dark); }

  /* Pharmacist card */
  .pcr-pharmacist-card { background:var(--pcr-cream); border-radius:20px; padding:32px; display:flex; align-items:center; gap:24px; border:1px solid var(--pcr-border); }
  @media(max-width:767px){ .pcr-pharmacist-card { flex-direction:column; text-align:center; } }
  .pcr-pharmacist-avatar { width:80px; height:80px; border-radius:50%; background:var(--pcr-teal); color:#fff; display:flex; align-items:center; justify-content:center; font-family:'Lora',serif; font-size:32px; font-weight:700; flex-shrink:0; }
  .pcr-pharmacist-title { font-family:'Lora',serif; font-size:20px; font-weight:700; color:var(--pcr-dark); margin-bottom:6px; }
  .pcr-pharmacist-text { font-family:'Inter',sans-serif; font-size:14px; color:var(--pcr-gray); line-height:1.6; margin-bottom:16px; }

  /* Modal */
  .wa-order-modal { display:none; position:fixed; inset:0; z-index:9999; }
  .wa-order-modal.open { display:block; }
  .wa-order-modal-overlay { position:absolute; inset:0; background:rgba(15,118,110,0.4); }
  .wa-order-modal-card { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:440px; max-width:calc(100vw - 32px); max-height:90vh; overflow-y:auto; background:#fff; border-radius:20px; box-shadow:0 24px 60px rgba(15,118,110,0.25); border:1px solid var(--pcr-border); }
  .wa-order-modal-header { display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid var(--pcr-border); }
  .wa-order-modal-title { font-family:'Lora',serif; font-size:20px; font-weight:700; color:var(--pcr-dark); margin:0; }
  .wa-order-modal-close { width:32px; height:32px; border-radius:8px; border:none; background:var(--pcr-cream); color:var(--pcr-dark); cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background .2s; }
  .wa-order-modal-close:hover { background:var(--pcr-border); }
  .wa-order-modal-body { padding:24px; }
  .wa-order-modal-product { display:flex; gap:14px; align-items:center; padding:14px; background:var(--pcr-cream); border-radius:12px; margin-bottom:20px; }
  .wa-order-modal-product img { width:56px; height:56px; border-radius:8px; object-fit:cover; flex-shrink:0; }
  .wa-order-modal-product-info { flex:1; min-width:0; }
  .wa-order-modal-product-name { font-family:'Lora',serif; font-size:15px; font-weight:600; color:var(--pcr-dark); margin:0 0 4px; line-height:1.3; }
  .wa-order-modal-product-price { font-family:'Inter',sans-serif; font-size:16px; font-weight:700; color:var(--pcr-teal); }
  .wa-order-modal-fields { display:flex; flex-direction:column; gap:14px; }
  .wa-order-modal-fields label { font-family:'Inter',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.08em; font-weight:600; color:var(--pcr-dark); margin-bottom:5px; display:block; }
  .wa-order-modal-fields input, .wa-order-modal-fields textarea { width:100%; padding:12px 14px; border:1px solid var(--pcr-border); border-radius:8px; font-family:'Inter',sans-serif; font-size:14px; background:var(--pcr-cream); outline:none; transition:border-color .2s; color:var(--pcr-dark); box-sizing:border-box; }
  .wa-order-modal-fields input:focus, .wa-order-modal-fields textarea:focus { border-color:var(--pcr-teal); background:#fff; }
  .wa-order-modal-fields textarea { resize:vertical; min-height:64px; }
  .wa-order-modal-actions { display:flex; gap:10px; margin-top:20px; }
  .wa-order-modal-send { flex:1; padding:14px; border-radius:8px; border:none; background:#25D366; color:#fff; font-family:'Inter',sans-serif; font-size:13px; font-weight:600; cursor:pointer; transition:background .2s; display:flex; align-items:center; justify-content:center; gap:8px; }
  .wa-order-modal-send:hover { background:#1FB855; }
  .wa-order-modal-cancel { padding:14px 20px; border-radius:8px; border:1px solid var(--pcr-border); background:#fff; color:var(--pcr-dark); font-family:'Inter',sans-serif; font-size:13px; font-weight:600; cursor:pointer; transition:background .2s; }
  .wa-order-modal-cancel:hover { background:var(--pcr-cream); }

  /* Services strip */
  .pcr-services-strip { background:var(--pcr-cream); padding:48px 0; }
  .pcr-services-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
  @media(max-width:1023px){ .pcr-services-grid { grid-template-columns:repeat(2,1fr); } }
  @media(max-width:767px){ .pcr-services-grid { grid-template-columns:1fr; } }
  .pcr-service-card { background:#fff; border:1px solid var(--pcr-border); border-radius:16px; padding:24px; text-align:center; transition:transform .2s, box-shadow .2s; }
  .pcr-service-card:hover { transform:translateY(-4px); box-shadow:0 12px 32px rgba(15,118,110,0.1); }
  .pcr-service-icon { font-size:28px; margin-bottom:10px; }
  .pcr-service-title { font-family:'Lora',serif; font-size:15px; font-weight:700; color:var(--pcr-dark); margin-bottom:4px; }
  .pcr-service-text { font-family:'Inter',sans-serif; font-size:12px; color:var(--pcr-gray); }
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
<div class="pcr-hero">
  <div class="pcr-hero-media"><img src="<?= $heroImg; ?>" alt="<?= htmlspecialchars($hero->banner_title ?? ($store->store_name ?? 'Pharma Care')); ?>"></div>
  <div class="pcr-hero-content">
    <div class="pcr-hero-text">
      <p class="pcr-hero-kicker"><?= htmlspecialchars($hero->banner_subtitle ?? 'Your Family\'s Health, Our Priority'); ?></p>
      <h1 class="pcr-hero-title"><?= htmlspecialchars($hero->banner_title ?? ($settings->store_headline ?: ($store->store_name ?? 'Caring for Your Family\'s Health'))); ?></h1>
      <p class="pcr-hero-lead"><?= htmlspecialchars($settings->store_subheadline ?: 'A trusted community pharmacy delivering genuine medicines, personal care, and friendly service right to your door.'); ?></p>
      <div class="pcr-hero-actions">
        <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pcr-btn pcr-btn-amber">Shop Health Essentials</a>
        <a href="https://wa.me/<?= $waNumber; ?>" target="_blank" class="pcr-btn pcr-btn-ghost">Ask Our Pharmacist</a>
      </div>
    </div>
    <div class="pcr-hero-visual">
      <img src="<?= $heroImg; ?>" alt="<?= htmlspecialchars($hero->banner_title ?? 'Pharma Care'); ?>" style="width:100%;height:400px;object-fit:cover;border-radius:20px;">
      <div class="pcr-hero-badge">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        Trusted Care
      </div>
    </div>
  </div>
</div>
<?php else: ?>
<div class="pcr-hero">
  <div class="pcr-hero-content">
    <div class="pcr-hero-text">
      <p class="pcr-hero-kicker"><?= htmlspecialchars($settings->store_subheadline ?? 'Your Family\'s Health, Our Priority'); ?></p>
      <h1 class="pcr-hero-title"><?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Caring for Your Family\'s Health')); ?></h1>
      <p class="pcr-hero-lead"><?= htmlspecialchars($settings->store_description ?: 'A trusted community pharmacy delivering genuine medicines, personal care, and friendly service right to your door.'); ?></p>
      <div class="pcr-hero-actions">
        <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pcr-btn pcr-btn-amber">Shop Health Essentials</a>
        <a href="https://wa.me/<?= $waNumber; ?>" target="_blank" class="pcr-btn pcr-btn-ghost">Ask Our Pharmacist</a>
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
      $pcrBadges = json_decode($settings->trust_badges_json ?? '', true);
      if(empty($pcrBadges) || !is_array($pcrBadges)) break;
?>
<!-- TRUST BADGES -->
<div class="pcr-trust">
  <div class="pcr-container">
    <div class="pcr-trust-grid">
      <?php foreach(array_slice($pcrBadges, 0, 4) as $b): ?>
      <div class="pcr-trust-item">
        <div class="pcr-trust-item-icon"><?= !empty($b['icon']) ? $b['icon'] : '&#10004;'; ?></div>
        <div class="pcr-trust-item-title"><?= htmlspecialchars($b['title'] ?? ''); ?></div>
        <div class="pcr-trust-item-text"><?= htmlspecialchars($b['desc'] ?? ''); ?></div>
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
<div class="pcr-section">
  <div class="pcr-container">
    <div class="pcr-promo">
      <?php if($promoImg): ?>
      <div class="pcr-promo-media"><img src="<?= $promoImg; ?>" alt="<?= htmlspecialchars($promo->banner_title ?? 'Promo'); ?>"></div>
      <?php endif; ?>
      <div class="pcr-promo-body">
        <div class="pcr-promo-kicker"><?= htmlspecialchars($promo->banner_subtitle ?? 'Family Health Offer'); ?></div>
        <h3 class="pcr-promo-title"><?= htmlspecialchars($promo->banner_title ?? 'Special Offer for Your Family'); ?></h3>
        <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pcr-btn pcr-btn-amber">Shop Now</a>
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
<div class="pcr-section" style="background:var(--pcr-cream);">
  <div class="pcr-container">
    <div class="pcr-section-head">
      <div>
        <div class="pcr-section-label">Browse</div>
        <h2 class="pcr-section-title">Shop by Health Category</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pcr-section-link">View All &rarr;</a>
    </div>
    <div class="pcr-cat-grid">
      <?php foreach(array_slice($categories, 0, 8) as $cat):
        $catImg = (!empty($cat->category_image) && file_exists($cat->category_image)) ? base_url($cat->category_image) : '';
        $itemCount = $cat->item_count ?? 0;
      ?>
      <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="pcr-cat-card">
        <div class="pcr-cat-card-media">
          <?php if($catImg): ?>
          <img src="<?= $catImg; ?>" alt="<?= htmlspecialchars($cat->category_name); ?>" loading="lazy">
          <?php else: ?>
          <div class="pcr-cat-placeholder"><span><?= htmlspecialchars(substr($cat->category_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="pcr-cat-card-overlay"></div>
        <div class="pcr-cat-card-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </div>
        <div class="pcr-cat-card-body">
          <div class="pcr-cat-card-name"><?= htmlspecialchars($cat->category_name); ?></div>
          <?php if($itemCount > 0): ?>
          <div class="pcr-cat-card-count"><?= $itemCount; ?> items</div>
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
<div class="pcr-section">
  <div class="pcr-container">
    <div class="pcr-section-head">
      <div>
        <div class="pcr-section-label">Family Favorites</div>
        <h2 class="pcr-section-title">Featured Health Products</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pcr-section-link">View All &rarr;</a>
    </div>
    <div class="pcr-product-grid">
      <?php foreach(array_slice($featured_products, 0, 8) as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $oldPrice = $p->original_price ?? $p->sales_price;
        $hasDiscount = $oldPrice > $price;
        $discountPct = $hasDiscount ? round((($oldPrice - $price) / $oldPrice) * 100) : 0;
        $img = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
      ?>
      <div class="pcr-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, <?= $hasDiscount ? $oldPrice : 0; ?>)">
        <?php if($hasDiscount && $discountPct > 0): ?>
        <span class="pcr-product-badge">-<?= $discountPct; ?>%</span>
        <?php endif; ?>
        <button class="pcr-product-wishlist" onclick="event.stopPropagation();" aria-label="Wishlist"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></button>
        <div class="pcr-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
          <?php else: ?>
          <div class="pcr-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="pcr-product-body">
          <?php if(!empty($p->category_name)): ?>
          <div class="pcr-product-brand"><?= htmlspecialchars($p->category_name); ?></div>
          <?php endif; ?>
          <div class="pcr-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="pcr-product-footer">
            <div class="pcr-product-price"><?= sf_currency($price, $cur); ?><?php if($hasDiscount): ?><span class="old"><?= sf_currency($oldPrice, $cur); ?></span><?php endif; ?></div>
            <div class="pcr-card-actions">
              <button class="pcr-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add to cart"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="pcr-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $p->id; ?>,'<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',<?= $p->stock; ?>)" aria-label="Order via WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
            </div>
          </div>
          <?php if($p->stock <= 0 && !($settings->allow_backorder ?? false)): ?>
          <div class="pcr-product-stock">Out of Stock</div>
          <?php else: ?>
          <div class="pcr-product-stock in">In Stock</div>
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
    // PHARMACIST CARD
    // =====================================================
    case 'pharmacist_card':
?>
<!-- PHARMACIST CARD -->
<div class="pcr-section" style="background:var(--pcr-cream);">
  <div class="pcr-container">
    <div class="pcr-pharmacist-card">
      <div class="pcr-pharmacist-avatar"><?= !empty($store->store_name) ? htmlspecialchars(substr($store->store_name, 0, 1)) : 'P'; ?></div>
      <div>
        <div class="pcr-pharmacist-title">Your Local Pharmacist Is Here to Help</div>
        <p class="pcr-pharmacist-text">Whether it's a question about a prescription, finding the right over-the-counter medicine, or advice for your family's health, we're just a message away.</p>
        <a href="https://wa.me/<?= $waNumber; ?>" target="_blank" class="pcr-btn pcr-btn-teal">Ask Our Pharmacist</a>
      </div>
    </div>
  </div>
</div>
<?php
      break;

    // =====================================================
    // FEATURED SERVICES
    // =====================================================
    case 'featured_services':
      if(!empty($featured_services) && ($settings->allow_services ?? false)):
?>
<!-- FEATURED SERVICES -->
<div class="pcr-section" style="background:var(--pcr-cream);">
  <div class="pcr-container">
    <div class="pcr-section-head">
      <div>
        <div class="pcr-section-label">Services</div>
        <h2 class="pcr-section-title">Community Pharmacy Services</h2>
      </div>
    </div>
    <div class="pcr-product-grid">
      <?php foreach(array_slice($featured_services, 0, 4) as $s):
        $sPrice = $s->effective_price ?? $s->sales_price ?? $s->price ?? 0;
        $sImg = (!empty($s->item_image) && file_exists($s->item_image)) ? base_url($s->item_image) : (!empty($s->service_image) && file_exists($s->service_image) ? base_url($s->service_image) : '');
      ?>
      <div class="pcr-product-card" onclick="openProductModal(<?= $s->id; ?>, '<?= htmlspecialchars(addslashes($s->item_name ?? $s->service_name ?? '')); ?>', <?= $sPrice; ?>, '<?= $s->item_image ?? $s->service_image ?? ''; ?>', '<?= htmlspecialchars(addslashes($s->description ?? '')); ?>', 999, 0)">
        <div class="pcr-product-media">
          <?php if($sImg): ?>
          <img src="<?= $sImg; ?>" alt="<?= htmlspecialchars($s->item_name ?? $s->service_name ?? ''); ?>" loading="lazy">
          <?php else: ?>
          <div class="pcr-product-placeholder"><span><?= htmlspecialchars(substr($s->item_name ?? $s->service_name ?? 'S', 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="pcr-product-body">
          <div class="pcr-product-name"><?= htmlspecialchars($s->item_name ?? $s->service_name ?? ''); ?></div>
          <div class="pcr-product-footer">
            <div class="pcr-product-price"><?= sf_currency($sPrice, $cur); ?></div>
            <div class="pcr-card-actions">
              <button class="pcr-add-btn" onclick="event.stopPropagation();addToCart(<?= $s->id; ?>,'service','<?= htmlspecialchars(addslashes($s->item_name ?? $s->service_name ?? '')); ?>',<?= $sPrice; ?>,'<?= $s->item_image ?? $s->service_image ?? ''; ?>',1,999)" aria-label="Add to cart"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="pcr-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $s->id; ?>,'<?= htmlspecialchars(addslashes($s->item_name ?? $s->service_name ?? '')); ?>',<?= $sPrice; ?>,'<?= $s->item_image ?? $s->service_image ?? ''; ?>',999)" aria-label="Order via WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
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
<div class="pcr-section">
  <div class="pcr-container">
    <div class="pcr-section-head">
      <div>
        <div class="pcr-section-label">Family Favorites</div>
        <h2 class="pcr-section-title">Best Sellers</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pcr-section-link">View All &rarr;</a>
    </div>
    <div class="pcr-product-grid">
      <?php foreach(array_slice($best_sellers, 0, 4) as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $img = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
      ?>
      <div class="pcr-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, 0)">
        <div class="pcr-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
          <?php else: ?>
          <div class="pcr-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="pcr-product-body">
          <div class="pcr-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="pcr-product-footer">
            <div class="pcr-product-price"><?= sf_currency($price, $cur); ?></div>
            <div class="pcr-card-actions">
              <button class="pcr-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add to cart"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="pcr-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $p->id; ?>,'<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',<?= $p->stock; ?>)" aria-label="Order via WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
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
<div class="pcr-section" style="background:var(--pcr-cream);">
  <div class="pcr-container">
    <div class="pcr-section-head">
      <div>
        <div class="pcr-section-label">Just In</div>
        <h2 class="pcr-section-title">New Arrivals</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pcr-section-link">View All &rarr;</a>
    </div>
    <div class="pcr-product-grid">
      <?php foreach(array_slice($new_arrivals, 0, 4) as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $img = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
      ?>
      <div class="pcr-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, 0)">
        <span class="pcr-product-badge new">New</span>
        <div class="pcr-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
          <?php else: ?>
          <div class="pcr-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="pcr-product-body">
          <div class="pcr-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="pcr-product-footer">
            <div class="pcr-product-price"><?= sf_currency($price, $cur); ?></div>
            <div class="pcr-card-actions">
              <button class="pcr-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add to cart"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="pcr-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $p->id; ?>,'<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',<?= $p->stock; ?>)" aria-label="Order via WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
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
<div class="pcr-section">
  <div class="pcr-container">
    <div class="pcr-section-head" style="justify-content:center;text-align:center;">
      <h2 class="pcr-section-title">Trusted Brands We Carry</h2>
    </div>
    <div class="pcr-brands-grid">
      <?php foreach($brands as $brand): ?>
      <div class="pcr-brand"><?= htmlspecialchars($brand->brand_name ?? $brand->name ?? ''); ?></div>
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
<div class="pcr-section" style="background:var(--pcr-cream);">
  <div class="pcr-container">
    <div class="pcr-section-head">
      <div>
        <div class="pcr-section-label">Community Reviews</div>
        <h2 class="pcr-section-title">What Neighbours Say</h2>
      </div>
    </div>
    <div class="pcr-testimonials-grid">
      <?php foreach(array_slice($testimonials, 0, 3) as $t): ?>
      <div class="pcr-testimonial">
        <div class="pcr-testimonial-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
        <div class="pcr-testimonial-text"><?= htmlspecialchars($t->testimonial_text ?? $t->message ?? ''); ?></div>
        <div class="pcr-testimonial-author"><?= htmlspecialchars($t->customer_name ?? $t->author ?? ''); ?></div>
        <?php if(!empty($t->role ?? $t->customer_role)): ?>
        <div class="pcr-testimonial-role"><?= htmlspecialchars($t->role ?? $t->customer_role); ?></div>
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
<div class="pcr-section">
  <div class="pcr-container">
    <div class="pcr-section-head" style="justify-content:center;text-align:center;">
      <h2 class="pcr-section-title">Follow Our Community</h2>
    </div>
    <div class="pcr-insta-grid">
      <?php foreach(array_slice($instagram_posts, 0, 10) as $post): ?>
      <a href="<?= htmlspecialchars($post->permalink ?? $post->link ?? '#'); ?>" target="_blank" class="pcr-insta-item">
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
<div class="pcr-section">
  <div class="pcr-container">
    <div class="pcr-section-head" style="justify-content:center;text-align:center;">
      <h2 class="pcr-section-title">Frequently Asked Questions</h2>
    </div>
    <div class="pcr-faq-list">
      <?php foreach($faqs as $faq): ?>
      <div class="pcr-faq-item" onclick="this.classList.toggle('open')">
        <div class="pcr-faq-q"><?= htmlspecialchars($faq->question ?? $faq->faq_question ?? ''); ?></div>
        <div class="pcr-faq-a"><p><?= htmlspecialchars($faq->answer ?? $faq->faq_answer ?? ''); ?></p></div>
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
<div class="pcr-section" style="background:var(--pcr-cream);">
  <div class="pcr-container">
    <div class="pcr-section-head" style="justify-content:center;text-align:center;">
      <h2 class="pcr-section-title">Visit or Contact Us</h2>
    </div>
    <div class="pcr-contact-grid">
      <?php if(!empty($settings->store_phone)): ?>
      <div class="pcr-contact-card">
        <div class="pcr-contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></div>
        <div class="pcr-contact-label">Phone</div>
        <div class="pcr-contact-value"><?= htmlspecialchars($settings->store_phone); ?></div>
      </div>
      <?php endif; ?>
      <?php if(!empty($settings->store_email)): ?>
      <div class="pcr-contact-card">
        <div class="pcr-contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
        <div class="pcr-contact-label">Email</div>
        <div class="pcr-contact-value"><?= htmlspecialchars($settings->store_email); ?></div>
      </div>
      <?php endif; ?>
      <?php if($waNumber): ?>
      <div class="pcr-contact-card">
        <div class="pcr-contact-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg></div>
        <div class="pcr-contact-label">WhatsApp</div>
        <div class="pcr-contact-value"><?= htmlspecialchars($settings->whatsapp_number); ?></div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php
      break;

    // =====================================================
    // PRESCRIPTION CTA
    // =====================================================
    case 'prescription_cta':
?>
<!-- PRESCRIPTION CTA -->
<div class="pcr-section">
  <div class="pcr-container">
    <div class="pcr-rx-cta">
      <div class="pcr-rx-cta-title">Refill Your Prescriptions the Easy Way</div>
      <p class="pcr-rx-cta-text">Send us your prescription on WhatsApp or by email, and we'll prepare it for pickup or delivery.</p>
      <a href="https://wa.me/<?= $waNumber; ?>" target="_blank" class="pcr-btn">Send Prescription Now</a>
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
<div class="pcr-section">
  <div class="pcr-container">
    <div class="pcr-wa-cta">
      <div class="pcr-wa-cta-title">Have a Health Question?</div>
      <p class="pcr-wa-cta-text">Our pharmacist is just a WhatsApp message away. Friendly, confidential advice for you and your family.</p>
      <a href="https://wa.me/<?= $waNumber; ?>" target="_blank" class="pcr-btn">Chat with Our Pharmacist</a>
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
<div class="pcr-section">
  <div class="pcr-container">
    <div class="pcr-newsletter">
      <div class="pcr-newsletter-title">Stay Connected with Your Pharmacy</div>
      <p class="pcr-newsletter-text">Get health tips, new arrivals, and community updates delivered to your inbox.</p>
      <form class="pcr-newsletter-form" onsubmit="return mpNewsletterSubmit(event)">
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
<div class="pcr-section" style="background:var(--pcr-cream);">
  <div class="pcr-container">
    <div class="pcr-section-head" style="justify-content:center;text-align:center;">
      <h2 class="pcr-section-title">Opening Hours</h2>
    </div>
    <div class="pcr-hours-grid">
      <?php foreach($business_hours as $line):
        $line = trim($line);
        if(strpos($line, ':') !== false):
          list($day, $time) = array_map('trim', explode(':', $line, 2)); ?>
      <div class="pcr-hours-row">
        <span class="pcr-hours-day"><?= htmlspecialchars(ucfirst($day)); ?></span>
        <span class="pcr-hours-time"><?= htmlspecialchars($time); ?></span>
      </div>
      <?php else: ?>
      <div class="pcr-hours-row">
        <span class="pcr-hours-full"><?= htmlspecialchars($line); ?></span>
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
  const storeName = '<?= htmlspecialchars(addslashes($store->store_name ?? 'Pharma Care')); ?>';
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
