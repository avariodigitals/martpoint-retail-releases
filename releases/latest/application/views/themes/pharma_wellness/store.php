<?php
/**
 * Pharma Wellness — Pharmacy Homepage
 * Warm, wellness-focused design with sage teal and coral accents,
 * soft rounded shapes and a friendly, holistic health aesthetic.
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
    --pw-sage:#0D9488;
    --pw-sage-dark:#0F766E;
    --pw-coral:#F97066;
    --pw-coral-dark:#E85C50;
    --pw-cream:#FFFBF5;
    --pw-cream-dark:#FEF7ED;
    --pw-dark:#134E4A;
    --pw-gray:#78716C;
    --pw-border:#E8F0EE;
    --pw-white:#FFFFFF;
  }

  .theme-pharma_wellness .mp-topbar,
  .theme-pharma_wellness .mp-announcement,
  .theme-pharma_wellness .mp-nav,
  .theme-pharma_wellness .mp-header,
  .theme-pharma_wellness .mp-mobile-menu-btn,
  .theme-pharma_wellness .mp-footer-space { display:none !important; }

  .pw-container { max-width:1400px; margin:0 auto; padding:0 24px; }
  @media(max-width:767px){ .pw-container { padding:0 16px; } }

  /* Hero */
  .pw-hero { position:relative; background:linear-gradient(135deg, #FFFBF5 0%, #E8F0EE 50%, #CFFAFE 100%); color:var(--pw-dark); overflow:hidden; min-height:580px; display:flex; align-items:center; }
  @media(max-width:767px){ .pw-hero { min-height:520px; } }
  .pw-hero::before { content:''; position:absolute; inset:0; background-image:url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M20 50 Q 50 20 80 50 Q 50 80 20 50' fill='%230D9488' fill-opacity='0.03'/%3E%3C/svg%3E"); background-size:120px 120px; }
  .pw-hero-media { position:absolute; inset:0; opacity:0.15; }
  .pw-hero-media img { width:100%; height:100%; object-fit:cover; }
  .pw-hero-content { position:relative; z-index:2; padding:100px 24px 120px; max-width:1400px; margin:0 auto; width:100%; display:flex; align-items:center; gap:60px; }
  @media(max-width:1023px){ .pw-hero-content { flex-direction:column; gap:40px; padding:80px 24px 100px; } }
  @media(max-width:767px){ .pw-hero-content { padding:60px 16px 80px; } }
  .pw-hero-text { flex:1; max-width:620px; }
  .pw-hero-kicker { font-family:'Poppins',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.18em; color:var(--pw-coral); margin-bottom:16px; font-weight:600; }
  .pw-hero-title { font-family:'Poppins',sans-serif; font-size:clamp(36px,4.5vw,58px); line-height:1.12; font-weight:700; margin:0 0 20px; letter-spacing:-0.02em; color:var(--pw-dark); }
  .pw-hero-lead { font-family:'Poppins',sans-serif; font-size:clamp(15px,1.8vw,18px); line-height:1.7; color:var(--pw-gray); margin-bottom:32px; }
  .pw-hero-actions { display:flex; gap:14px; flex-wrap:wrap; }
  .pw-hero-visual { flex:1; max-width:480px; position:relative; }
  .pw-hero-visual img { width:100%; height:420px; object-fit:cover; border-radius:28px; box-shadow:0 20px 50px rgba(13,148,136,0.15); }
  @media(max-width:1023px){ .pw-hero-visual { max-width:100%; } .pw-hero-visual img { height:300px; } }
  @media(max-width:767px){ .pw-hero-visual img { height:220px; } }

  .pw-hero-badge { position:absolute; bottom:-16px; left:-16px; background:var(--pw-white); color:var(--pw-dark); padding:16px 20px; border-radius:20px; box-shadow:0 12px 32px rgba(13,148,136,0.12); display:flex; align-items:center; gap:10px; font-family:'Poppins',sans-serif; font-size:12px; font-weight:600; }
  .pw-hero-badge svg { width:22px; height:22px; color:var(--pw-sage); flex-shrink:0; }
  @media(max-width:767px){ .pw-hero-badge { left:12px; bottom:-12px; padding:12px 16px; } }

  /* Buttons */
  .pw-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:15px 34px; border-radius:24px; font-family:'Poppins',sans-serif; font-size:13px; font-weight:600; transition:transform .15s, background .2s, color .2s; cursor:pointer; border:none; text-decoration:none; }
  .pw-btn:active { transform:scale(0.98); }
  .pw-btn-sage { background:var(--pw-sage); color:#fff; }
  .pw-btn-sage:hover { background:var(--pw-sage-dark); }
  .pw-btn-coral { background:var(--pw-coral); color:#fff; }
  .pw-btn-coral:hover { background:var(--pw-coral-dark); }
  .pw-btn-ghost { background:transparent; color:var(--pw-dark); border:1.5px solid var(--pw-sage); }
  .pw-btn-ghost:hover { background:rgba(13,148,136,0.08); }
  .pw-btn-outline { background:#fff; color:var(--pw-sage); border:1.5px solid var(--pw-sage); }
  .pw-btn-outline:hover { background:var(--pw-sage); color:#fff; }
  .pw-btn-wa { background:#25D366; color:#fff; }
  .pw-btn-wa:hover { background:#1DA851; }

  /* Sections */
  .pw-section { padding:72px 0; }
  @media(max-width:767px){ .pw-section { padding:44px 0; } }
  .pw-section-head { display:flex; align-items:flex-end; justify-content:space-between; gap:16px; margin-bottom:36px; }
  .pw-section-label { font-family:'Poppins',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.16em; color:var(--pw-coral); font-weight:600; margin-bottom:8px; }
  .pw-section-title { font-family:'Poppins',sans-serif; font-size:32px; margin:0; font-weight:700; color:var(--pw-dark); letter-spacing:-0.01em; }
  .pw-section-link { font-family:'Poppins',sans-serif; font-size:13px; font-weight:600; color:var(--pw-sage); text-decoration:none; transition:color .2s; }
  .pw-section-link:hover { color:var(--pw-sage-dark); }

  /* Product grid */
  .pw-product-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:20px; }
  @media(max-width:1023px){ .pw-product-grid { grid-template-columns:repeat(3,1fr); } }
  @media(max-width:767px){ .pw-product-grid { grid-template-columns:repeat(2,1fr); gap:12px; } }
  .pw-product-card { position:relative; background:var(--pw-white); border-radius:20px; overflow:hidden; border:1px solid var(--pw-border); transition:transform .25s, box-shadow .25s; cursor:pointer; }
  .pw-product-card:hover { transform:translateY(-6px); box-shadow:0 20px 40px rgba(13,148,136,0.12); }
  .pw-product-wishlist { position:absolute; top:12px; right:12px; width:36px; height:36px; border-radius:50%; background:#fff; display:flex; align-items:center; justify-content:center; z-index:2; transition:color .2s, transform .2s; box-shadow:0 2px 8px rgba(0,0,0,0.06); border:none; cursor:pointer; color:var(--pw-gray); }
  .pw-product-wishlist:hover { color:var(--pw-coral); transform:scale(1.08); }
  .pw-product-badge { position:absolute; top:12px; left:12px; background:var(--pw-coral); color:#fff; font-family:'Poppins',sans-serif; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; padding:5px 10px; border-radius:12px; z-index:2; }
  .pw-product-badge.new { background:var(--pw-sage); }
  .pw-product-media { aspect-ratio:1/1; overflow:hidden; background:var(--pw-cream); }
  .pw-product-media img { width:100%; height:100%; object-fit:cover; transition:transform .4s ease; }
  .pw-product-card:hover .pw-product-media img { transform:scale(1.05); }
  .pw-product-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--pw-cream); color:var(--pw-sage); }
  .pw-product-placeholder span { font-family:'Poppins',sans-serif; font-size:36px; font-weight:700; }
  .pw-product-body { padding:18px; }
  .pw-product-brand { font-family:'Poppins',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.08em; color:var(--pw-sage); font-weight:500; margin-bottom:4px; }
  .pw-product-name { font-family:'Poppins',sans-serif; font-size:14px; font-weight:600; margin-bottom:10px; line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; min-height:40px; color:var(--pw-dark); }
  .pw-product-footer { display:flex; flex-direction:column; gap:8px; }
  .pw-product-price { font-family:'Poppins',sans-serif; font-size:18px; font-weight:700; color:var(--pw-dark); }
  .pw-product-price .old { font-family:'Poppins',sans-serif; font-size:13px; color:var(--pw-gray); text-decoration:line-through; margin-left:6px; font-weight:400; }
  .pw-card-actions { display:flex; gap:8px; }
  @media(max-width:767px){ .pw-card-actions { flex-direction:column; gap:6px; } }
  .pw-add-btn { flex:1; padding:11px 14px; border-radius:24px; background:var(--pw-sage); color:#fff; display:flex; align-items:center; justify-content:center; gap:6px; transition:background .2s, transform .15s; border:none; cursor:pointer; font-family:'Poppins',sans-serif; font-size:12px; font-weight:500; }
  .pw-add-btn:hover { background:var(--pw-sage-dark); }
  .pw-add-btn:active { transform:scale(0.97); }
  .pw-wa-btn { flex:1; padding:11px 14px; border-radius:24px; background:#25D366; color:#fff; display:flex; align-items:center; justify-content:center; gap:6px; transition:background .2s, transform .15s; border:none; cursor:pointer; font-family:'Poppins',sans-serif; font-size:12px; font-weight:500; }
  .pw-wa-btn:hover { background:#1FB855; }
  .pw-wa-btn:active { transform:scale(0.97); }
  .pw-product-stock { font-family:'Poppins',sans-serif; font-size:11px; color:#DC2626; font-weight:500; margin-top:6px; text-transform:uppercase; letter-spacing:0.06em; }
  .pw-product-stock.in { color:var(--pw-sage); }

  /* Category cards */
  .pw-cat-grid { display:grid; grid-template-columns:repeat(6,1fr); gap:14px; }
  @media(max-width:1023px){ .pw-cat-grid { grid-template-columns:repeat(4,1fr); } }
  @media(max-width:767px){ .pw-cat-grid { grid-template-columns:repeat(3,1fr); gap:10px; } }
  .pw-cat-card { position:relative; border-radius:20px; overflow:hidden; aspect-ratio:1/1; text-decoration:none; color:inherit; display:block; transition:transform .25s, box-shadow .25s; background:var(--pw-white); border:1px solid var(--pw-border); }
  .pw-cat-card:hover { transform:translateY(-6px); box-shadow:0 16px 36px rgba(13,148,136,0.12); }
  .pw-cat-card-media { position:absolute; inset:0; background:var(--pw-cream); }
  .pw-cat-card-media img { width:100%; height:100%; object-fit:cover; transition:transform .4s ease; }
  .pw-cat-card:hover .pw-cat-card-media img { transform:scale(1.08); }
  .pw-cat-card-overlay { position:absolute; inset:0; background:linear-gradient(180deg, transparent 30%, rgba(19,78,74,0.75) 100%); }
  .pw-cat-card-body { position:absolute; bottom:0; left:0; right:0; padding:16px; z-index:2; }
  .pw-cat-card-name { font-family:'Poppins',sans-serif; font-size:14px; font-weight:600; color:#fff; margin-bottom:2px; }
  .pw-cat-card-count { font-family:'Poppins',sans-serif; font-size:11px; color:#CFFAFE; }
  .pw-cat-card-icon { position:absolute; top:12px; right:12px; width:32px; height:32px; border-radius:50%; background:var(--pw-sage); display:flex; align-items:center; justify-content:center; opacity:0; transform:translateY(-4px); transition:opacity .25s, transform .25s; }
  .pw-cat-card:hover .pw-cat-card-icon { opacity:1; transform:translateY(0); }
  .pw-cat-card-icon svg { width:16px; height:16px; color:#fff; }
  .pw-cat-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--pw-cream); }
  .pw-cat-placeholder span { font-family:'Poppins',sans-serif; font-size:36px; font-weight:700; color:var(--pw-sage); }

  /* Trust badges */
  .pw-trust { background:var(--pw-cream); padding:56px 0; }
  .pw-trust-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:20px; }
  @media(max-width:1023px){ .pw-trust-grid { grid-template-columns:repeat(2,1fr); } }
  @media(max-width:767px){ .pw-trust-grid { grid-template-columns:1fr; } }
  .pw-trust-item { text-align:center; padding:24px 16px; background:var(--pw-white); border-radius:20px; border:1px solid var(--pw-border); transition:transform .2s; }
  .pw-trust-item:hover { transform:translateY(-4px); }
  .pw-trust-item-icon { width:52px; height:52px; border-radius:16px; background:rgba(13,148,136,0.08); display:flex; align-items:center; justify-content:center; font-size:24px; margin:0 auto 14px; color:var(--pw-sage); }
  .pw-trust-item-title { font-family:'Poppins',sans-serif; font-size:14px; font-weight:600; color:var(--pw-dark); margin-bottom:6px; }
  .pw-trust-item-text { font-family:'Poppins',sans-serif; font-size:12px; color:var(--pw-gray); line-height:1.5; }

  /* Promo banner */
  .pw-promo { background:linear-gradient(135deg, var(--pw-sage) 0%, var(--pw-sage-dark) 100%); border-radius:24px; overflow:hidden; display:flex; align-items:center; min-height:220px; color:#fff; }
  @media(max-width:767px){ .pw-promo { flex-direction:column; min-height:auto; } }
  .pw-promo-media { flex:1; min-height:220px; background:rgba(255,255,255,0.1); }
  .pw-promo-media img { width:100%; height:100%; object-fit:cover; }
  .pw-promo-body { flex:1; padding:40px; }
  @media(max-width:767px){ .pw-promo-body { padding:28px; } }
  .pw-promo-kicker { font-family:'Poppins',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.16em; color:#CFFAFE; font-weight:600; margin-bottom:8px; }
  .pw-promo-title { font-family:'Poppins',sans-serif; font-size:clamp(22px,2.5vw,30px); font-weight:700; margin-bottom:10px; }
  .pw-promo-text { font-family:'Poppins',sans-serif; font-size:15px; opacity:0.95; line-height:1.6; margin-bottom:20px; }

  /* Wellness CTA */
  .pw-wellness-cta { background:linear-gradient(135deg, #FEF3C7 0%, #FFFBF5 100%); color:var(--pw-dark); padding:48px 24px; text-align:center; border-radius:24px; border:1px solid var(--pw-border); }
  .pw-wellness-cta-title { font-family:'Poppins',sans-serif; font-size:clamp(22px,2.5vw,30px); font-weight:700; margin-bottom:10px; }
  .pw-wellness-cta-text { font-family:'Poppins',sans-serif; color:var(--pw-gray); max-width:440px; margin:0 auto 24px; font-size:15px; line-height:1.6; }
  .pw-wellness-cta .pw-btn { background:var(--pw-sage); color:#fff; }
  .pw-wellness-cta .pw-btn:hover { background:var(--pw-sage-dark); }

  /* WhatsApp CTA */
  .pw-wa-cta { background:linear-gradient(135deg, #25D366, #1DA851); color:#fff; padding:48px 24px; text-align:center; border-radius:24px; }
  .pw-wa-cta-title { font-family:'Poppins',sans-serif; font-size:clamp(22px,2.5vw,30px); font-weight:700; margin-bottom:10px; }
  .pw-wa-cta-text { font-family:'Poppins',sans-serif; opacity:0.95; max-width:440px; margin:0 auto 24px; font-size:15px; line-height:1.6; }
  .pw-wa-cta .pw-btn { background:#fff; color:#1DA851; }
  .pw-wa-cta .pw-btn:hover { background:var(--pw-dark); color:#fff; }

  /* Newsletter */
  .pw-newsletter { background:var(--pw-sage); color:#fff; padding:48px 24px; text-align:center; border-radius:24px; }
  .pw-newsletter-title { font-family:'Poppins',sans-serif; font-size:clamp(22px,2.5vw,30px); font-weight:700; margin-bottom:10px; }
  .pw-newsletter-text { font-family:'Poppins',sans-serif; opacity:0.9; max-width:440px; margin:0 auto 24px; font-size:15px; }
  .pw-newsletter-form { display:flex; gap:10px; max-width:440px; margin:0 auto; }
  .pw-newsletter-form input { flex:1; padding:14px 18px; border:none; border-radius:24px; font-family:'Poppins',sans-serif; font-size:14px; outline:none; }
  .pw-newsletter-form button { padding:14px 24px; border:none; border-radius:24px; background:var(--pw-coral); color:#fff; font-family:'Poppins',sans-serif; font-weight:600; cursor:pointer; font-size:13px; transition:background .2s; }
  .pw-newsletter-form button:hover { background:var(--pw-coral-dark); }

  /* Testimonials */
  .pw-testimonials-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; }
  @media(max-width:767px){ .pw-testimonials-grid { grid-template-columns:1fr; } }
  .pw-testimonial { background:var(--pw-white); border:1px solid var(--pw-border); border-radius:20px; padding:24px; }
  .pw-testimonial-stars { color:#FBBF24; margin-bottom:12px; font-size:16px; letter-spacing:2px; }
  .pw-testimonial-text { font-family:'Poppins',sans-serif; font-size:14px; color:var(--pw-gray); line-height:1.7; margin-bottom:16px; }
  .pw-testimonial-author { font-family:'Poppins',sans-serif; font-size:13px; font-weight:600; color:var(--pw-dark); }
  .pw-testimonial-role { font-family:'Poppins',sans-serif; font-size:11px; color:var(--pw-sage); margin-top:2px; }

  /* Brands */
  .pw-brands-grid { display:flex; flex-wrap:wrap; gap:14px; align-items:center; justify-content:center; }
  .pw-brand { padding:12px 24px; background:var(--pw-white); border:1px solid var(--pw-border); border-radius:24px; font-family:'Poppins',sans-serif; font-size:14px; font-weight:500; color:var(--pw-dark); transition:border-color .2s, color .2s; }
  .pw-brand:hover { border-color:var(--pw-sage); color:var(--pw-sage); }

  /* Instagram */
  .pw-insta-grid { display:grid; grid-template-columns:repeat(5,1fr); gap:12px; }
  @media(max-width:767px){ .pw-insta-grid { grid-template-columns:repeat(3,1fr); } }
  .pw-insta-item { aspect-ratio:1; border-radius:16px; overflow:hidden; position:relative; }
  .pw-insta-item img { width:100%; height:100%; object-fit:cover; transition:transform .3s; }
  .pw-insta-item:hover img { transform:scale(1.08); }

  /* FAQs */
  .pw-faq-list { max-width:760px; margin:0 auto; }
  .pw-faq-item { background:var(--pw-white); border:1px solid var(--pw-border); border-radius:16px; margin-bottom:10px; overflow:hidden; }
  .pw-faq-q { padding:18px 24px; font-family:'Poppins',sans-serif; font-size:15px; font-weight:600; color:var(--pw-dark); cursor:pointer; display:flex; justify-content:space-between; align-items:center; }
  .pw-faq-q::after { content:'+'; font-size:20px; color:var(--pw-sage); font-weight:700; }
  .pw-faq-item.open .pw-faq-q::after { content:'\2212'; }
  .pw-faq-a { max-height:0; overflow:hidden; transition:max-height .3s ease; padding:0 24px; }
  .pw-faq-item.open .pw-faq-a { max-height:300px; padding:0 24px 18px; }
  .pw-faq-a p { font-family:'Poppins',sans-serif; font-size:14px; color:var(--pw-gray); line-height:1.7; margin:0; }

  /* Contact */
  .pw-contact-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; }
  @media(max-width:767px){ .pw-contact-grid { grid-template-columns:1fr; } }
  .pw-contact-card { background:var(--pw-white); border:1px solid var(--pw-border); border-radius:20px; padding:24px; text-align:center; transition:transform .2s, box-shadow .2s; }
  .pw-contact-card:hover { transform:translateY(-4px); box-shadow:0 12px 32px rgba(13,148,136,0.1); }
  .pw-contact-icon { width:52px; height:52px; border-radius:16px; background:rgba(13,148,136,0.08); display:flex; align-items:center; justify-content:center; margin:0 auto 14px; color:var(--pw-sage); }
  .pw-contact-icon svg { width:22px; height:22px; }
  .pw-contact-label { font-family:'Poppins',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.12em; color:var(--pw-coral); font-weight:600; margin-bottom:4px; }
  .pw-contact-value { font-family:'Poppins',sans-serif; font-size:15px; font-weight:600; color:var(--pw-dark); }

  /* Store hours */
  .pw-hours-grid { max-width:520px; margin:0 auto; background:var(--pw-white); border:1px solid var(--pw-border); border-radius:16px; padding:0 24px; }
  .pw-hours-row { display:flex; justify-content:space-between; align-items:center; padding:14px 0; border-bottom:1px solid var(--pw-border); font-family:'Poppins',sans-serif; font-size:15px; }
  .pw-hours-row:last-child { border-bottom:none; }
  .pw-hours-day { color:var(--pw-dark); font-weight:600; }
  .pw-hours-time { color:var(--pw-gray); }
  .pw-hours-full { width:100%; text-align:center; color:var(--pw-dark); }

  /* Modal */
  .wa-order-modal { display:none; position:fixed; inset:0; z-index:9999; }
  .wa-order-modal.open { display:block; }
  .wa-order-modal-overlay { position:absolute; inset:0; background:rgba(13,148,136,0.4); }
  .wa-order-modal-card { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:440px; max-width:calc(100vw - 32px); max-height:90vh; overflow-y:auto; background:var(--pw-white); border-radius:24px; box-shadow:0 24px 60px rgba(13,148,136,0.25); border:1px solid var(--pw-border); }
  .wa-order-modal-header { display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid var(--pw-border); }
  .wa-order-modal-title { font-family:'Poppins',sans-serif; font-size:20px; font-weight:700; color:var(--pw-dark); margin:0; }
  .wa-order-modal-close { width:32px; height:32px; border-radius:12px; border:none; background:var(--pw-cream); color:var(--pw-dark); cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background .2s; }
  .wa-order-modal-close:hover { background:var(--pw-border); }
  .wa-order-modal-body { padding:24px; }
  .wa-order-modal-product { display:flex; gap:14px; align-items:center; padding:14px; background:var(--pw-cream); border-radius:16px; margin-bottom:20px; }
  .wa-order-modal-product img { width:56px; height:56px; border-radius:12px; object-fit:cover; flex-shrink:0; }
  .wa-order-modal-product-info { flex:1; min-width:0; }
  .wa-order-modal-product-name { font-family:'Poppins',sans-serif; font-size:14px; font-weight:600; color:var(--pw-dark); margin:0 0 4px; line-height:1.3; }
  .wa-order-modal-product-price { font-family:'Poppins',sans-serif; font-size:16px; font-weight:700; color:var(--pw-sage); }
  .wa-order-modal-fields { display:flex; flex-direction:column; gap:14px; }
  .wa-order-modal-fields label { font-family:'Poppins',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.08em; font-weight:600; color:var(--pw-dark); margin-bottom:5px; display:block; }
  .wa-order-modal-fields input, .wa-order-modal-fields textarea { width:100%; padding:12px 14px; border:1px solid var(--pw-border); border-radius:12px; font-family:'Poppins',sans-serif; font-size:14px; background:var(--pw-cream); outline:none; transition:border-color .2s; color:var(--pw-dark); box-sizing:border-box; }
  .wa-order-modal-fields input:focus, .wa-order-modal-fields textarea:focus { border-color:var(--pw-sage); background:#fff; }
  .wa-order-modal-fields textarea { resize:vertical; min-height:64px; }
  .wa-order-modal-actions { display:flex; gap:10px; margin-top:20px; }
  .wa-order-modal-send { flex:1; padding:14px; border-radius:24px; border:none; background:#25D366; color:#fff; font-family:'Poppins',sans-serif; font-size:13px; font-weight:600; cursor:pointer; transition:background .2s; display:flex; align-items:center; justify-content:center; gap:8px; }
  .wa-order-modal-send:hover { background:#1FB855; }
  .wa-order-modal-cancel { padding:14px 20px; border-radius:24px; border:1px solid var(--pw-border); background:#fff; color:var(--pw-dark); font-family:'Poppins',sans-serif; font-size:13px; font-weight:600; cursor:pointer; transition:background .2s; }
  .wa-order-modal-cancel:hover { background:var(--pw-cream); }

  /* Health goals */
  .pw-goals-grid { display:grid; grid-template-columns:repeat(5,1fr); gap:12px; }
  @media(max-width:1023px){ .pw-goals-grid { grid-template-columns:repeat(3,1fr); } }
  @media(max-width:767px){ .pw-goals-grid { grid-template-columns:repeat(2,1fr); } }
  .pw-goal-card { background:var(--pw-white); border:1px solid var(--pw-border); border-radius:16px; padding:20px; text-align:center; transition:transform .2s, box-shadow .2s; cursor:pointer; text-decoration:none; color:var(--pw-dark); }
  .pw-goal-card:hover { transform:translateY(-4px); box-shadow:0 12px 32px rgba(13,148,136,0.1); }
  .pw-goal-icon { font-size:32px; margin-bottom:8px; }
  .pw-goal-title { font-family:'Poppins',sans-serif; font-size:13px; font-weight:600; }
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
<div class="pw-hero">
  <div class="pw-hero-media"><img src="<?= $heroImg; ?>" alt="<?= htmlspecialchars($hero->banner_title ?? ($store->store_name ?? 'Wellness Pharmacy')); ?>"></div>
  <div class="pw-hero-content">
    <div class="pw-hero-text">
      <p class="pw-hero-kicker"><?= htmlspecialchars($hero->banner_subtitle ?? 'Your Health, Simplified'); ?></p>
      <h1 class="pw-hero-title"><?= htmlspecialchars($hero->banner_title ?? ($settings->store_headline ?: ($store->store_name ?? 'Wellness for Every Day'))); ?></h1>
      <p class="pw-hero-lead"><?= htmlspecialchars($settings->store_subheadline ?: 'Holistic wellness essentials, expert guidance, and pharmacy care — delivered with warmth to your doorstep.'); ?></p>
      <div class="pw-hero-actions">
        <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pw-btn pw-btn-sage">Shop Wellness</a>
        <a href="https://wa.me/<?= $waNumber; ?>" target="_blank" class="pw-btn pw-btn-ghost">Talk to a Pharmacist</a>
      </div>
    </div>
    <div class="pw-hero-visual">
      <img src="<?= $heroImg; ?>" alt="<?= htmlspecialchars($hero->banner_title ?? 'Wellness'); ?>" style="width:100%;height:420px;object-fit:cover;border-radius:28px;">
      <div class="pw-hero-badge">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        Pharmacist Approved
      </div>
    </div>
  </div>
</div>
<?php else: ?>
<div class="pw-hero">
  <div class="pw-hero-content">
    <div class="pw-hero-text">
      <p class="pw-hero-kicker"><?= htmlspecialchars($settings->store_subheadline ?? 'Your Health, Simplified'); ?></p>
      <h1 class="pw-hero-title"><?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Wellness for Every Day')); ?></h1>
      <p class="pw-hero-lead"><?= htmlspecialchars($settings->store_description ?: 'Holistic wellness essentials, expert guidance, and pharmacy care — delivered with warmth to your doorstep.'); ?></p>
      <div class="pw-hero-actions">
        <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pw-btn pw-btn-sage">Shop Wellness</a>
        <a href="https://wa.me/<?= $waNumber; ?>" target="_blank" class="pw-btn pw-btn-ghost">Talk to a Pharmacist</a>
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
      $pwBadges = json_decode($settings->trust_badges_json ?? '', true);
      if(empty($pwBadges) || !is_array($pwBadges)) break;
?>
<!-- TRUST BADGES -->
<div class="pw-trust">
  <div class="pw-container">
    <div class="pw-trust-grid">
      <?php foreach(array_slice($pwBadges, 0, 4) as $b): ?>
      <div class="pw-trust-item">
        <div class="pw-trust-item-icon"><?= !empty($b['icon']) ? $b['icon'] : '&#10004;'; ?></div>
        <div class="pw-trust-item-title"><?= htmlspecialchars($b['title'] ?? ''); ?></div>
        <div class="pw-trust-item-text"><?= htmlspecialchars($b['desc'] ?? ''); ?></div>
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
<div class="pw-section">
  <div class="pw-container">
    <div class="pw-promo">
      <?php if($promoImg): ?>
      <div class="pw-promo-media"><img src="<?= $promoImg; ?>" alt="<?= htmlspecialchars($promo->banner_title ?? 'Promo'); ?>"></div>
      <?php endif; ?>
      <div class="pw-promo-body">
        <div class="pw-promo-kicker"><?= htmlspecialchars($promo->banner_subtitle ?? 'Wellness Offer'); ?></div>
        <h3 class="pw-promo-title"><?= htmlspecialchars($promo->banner_title ?? 'Start Your Wellness Journey'); ?></h3>
        <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pw-btn pw-btn-coral">Shop Now</a>
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
<div class="pw-section" style="background:var(--pw-cream);">
  <div class="pw-container">
    <div class="pw-section-head">
      <div>
        <div class="pw-section-label">Browse</div>
        <h2 class="pw-section-title">Shop by Wellness Category</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pw-section-link">View All &rarr;</a>
    </div>
    <div class="pw-cat-grid">
      <?php foreach(array_slice($categories, 0, 8) as $cat):
        $catImg = (!empty($cat->category_image) && file_exists($cat->category_image)) ? base_url($cat->category_image) : '';
        $itemCount = $cat->item_count ?? 0;
      ?>
      <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="pw-cat-card">
        <div class="pw-cat-card-media">
          <?php if($catImg): ?>
          <img src="<?= $catImg; ?>" alt="<?= htmlspecialchars($cat->category_name); ?>" loading="lazy">
          <?php else: ?>
          <div class="pw-cat-placeholder"><span><?= htmlspecialchars(substr($cat->category_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="pw-cat-card-overlay"></div>
        <div class="pw-cat-card-icon">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
        </div>
        <div class="pw-cat-card-body">
          <div class="pw-cat-card-name"><?= htmlspecialchars($cat->category_name); ?></div>
          <?php if($itemCount > 0): ?>
          <div class="pw-cat-card-count"><?= $itemCount; ?> items</div>
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
<div class="pw-section">
  <div class="pw-container">
    <div class="pw-section-head">
      <div>
        <div class="pw-section-label">Wellness Essentials</div>
        <h2 class="pw-section-title">Featured Products</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pw-section-link">View All &rarr;</a>
    </div>
    <div class="pw-product-grid">
      <?php foreach(array_slice($featured_products, 0, 8) as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $oldPrice = $p->original_price ?? $p->sales_price;
        $hasDiscount = $oldPrice > $price;
        $discountPct = $hasDiscount ? round((($oldPrice - $price) / $oldPrice) * 100) : 0;
        $img = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
      ?>
      <div class="pw-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, <?= $hasDiscount ? $oldPrice : 0; ?>)">
        <?php if($hasDiscount && $discountPct > 0): ?>
        <span class="pw-product-badge">-<?= $discountPct; ?>%</span>
        <?php endif; ?>
        <button class="pw-product-wishlist" onclick="event.stopPropagation();" aria-label="Wishlist"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></button>
        <div class="pw-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
          <?php else: ?>
          <div class="pw-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="pw-product-body">
          <?php if(!empty($p->category_name)): ?>
          <div class="pw-product-brand"><?= htmlspecialchars($p->category_name); ?></div>
          <?php endif; ?>
          <div class="pw-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="pw-product-footer">
            <div class="pw-product-price"><?= sf_currency($price, $cur); ?><?php if($hasDiscount): ?><span class="old"><?= sf_currency($oldPrice, $cur); ?></span><?php endif; ?></div>
            <div class="pw-card-actions">
              <button class="pw-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add to cart"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="pw-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $p->id; ?>,'<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',<?= $p->stock; ?>)" aria-label="Order via WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
            </div>
          </div>
          <?php if($p->stock <= 0 && !($settings->allow_backorder ?? false)): ?>
          <div class="pw-product-stock">Out of Stock</div>
          <?php else: ?>
          <div class="pw-product-stock in">In Stock</div>
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
    // HEALTH GOALS
    // =====================================================
    case 'health_goals':
?>
<!-- HEALTH GOALS -->
<div class="pw-section" style="background:var(--pw-cream);">
  <div class="pw-container">
    <div class="pw-section-head">
      <div>
        <div class="pw-section-label">Shop by Goal</div>
        <h2 class="pw-section-title">Find Your Wellness Goal</h2>
      </div>
    </div>
    <div class="pw-goals-grid">
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pw-goal-card"><div class="pw-goal-icon">&#128170;</div><div class="pw-goal-title">Immunity</div></a>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pw-goal-card"><div class="pw-goal-icon">&#127861;</div><div class="pw-goal-title">Gut Health</div></a>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pw-goal-card"><div class="pw-goal-icon">&#128161;</div><div class="pw-goal-title">Energy</div></a>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pw-goal-card"><div class="pw-goal-icon">&#128719;</div><div class="pw-goal-title">Sleep</div></a>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pw-goal-card"><div class="pw-goal-icon">&#128135;</div><div class="pw-goal-title">Beauty</div></a>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pw-goal-card"><div class="pw-goal-icon">&#127939;</div><div class="pw-goal-title">Fitness</div></a>
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
<div class="pw-section" style="background:var(--pw-cream);">
  <div class="pw-container">
    <div class="pw-section-head">
      <div>
        <div class="pw-section-label">Services</div>
        <h2 class="pw-section-title">Wellness Services</h2>
      </div>
    </div>
    <div class="pw-product-grid">
      <?php foreach(array_slice($featured_services, 0, 4) as $s):
        $sPrice = $s->effective_price ?? $s->sales_price ?? $s->price ?? 0;
        $sImg = (!empty($s->item_image) && file_exists($s->item_image)) ? base_url($s->item_image) : (!empty($s->service_image) && file_exists($s->service_image) ? base_url($s->service_image) : '');
      ?>
      <div class="pw-product-card" onclick="openProductModal(<?= $s->id; ?>, '<?= htmlspecialchars(addslashes($s->item_name ?? $s->service_name ?? '')); ?>', <?= $sPrice; ?>, '<?= $s->item_image ?? $s->service_image ?? ''; ?>', '<?= htmlspecialchars(addslashes($s->description ?? '')); ?>', 999, 0)">
        <div class="pw-product-media">
          <?php if($sImg): ?>
          <img src="<?= $sImg; ?>" alt="<?= htmlspecialchars($s->item_name ?? $s->service_name ?? ''); ?>" loading="lazy">
          <?php else: ?>
          <div class="pw-product-placeholder"><span><?= htmlspecialchars(substr($s->item_name ?? $s->service_name ?? 'S', 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="pw-product-body">
          <div class="pw-product-name"><?= htmlspecialchars($s->item_name ?? $s->service_name ?? ''); ?></div>
          <div class="pw-product-footer">
            <div class="pw-product-price"><?= sf_currency($sPrice, $cur); ?></div>
            <div class="pw-card-actions">
              <button class="pw-add-btn" onclick="event.stopPropagation();addToCart(<?= $s->id; ?>,'service','<?= htmlspecialchars(addslashes($s->item_name ?? $s->service_name ?? '')); ?>',<?= $sPrice; ?>,'<?= $s->item_image ?? $s->service_image ?? ''; ?>',1,999)" aria-label="Add to cart"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="pw-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $s->id; ?>,'<?= htmlspecialchars(addslashes($s->item_name ?? $s->service_name ?? '')); ?>',<?= $sPrice; ?>,'<?= $s->item_image ?? $s->service_image ?? ''; ?>',999)" aria-label="Order via WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
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
<div class="pw-section">
  <div class="pw-container">
    <div class="pw-section-head">
      <div>
        <div class="pw-section-label">Community Favorites</div>
        <h2 class="pw-section-title">Best Sellers</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pw-section-link">View All &rarr;</a>
    </div>
    <div class="pw-product-grid">
      <?php foreach(array_slice($best_sellers, 0, 4) as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $img = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
      ?>
      <div class="pw-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, 0)">
        <div class="pw-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
          <?php else: ?>
          <div class="pw-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="pw-product-body">
          <div class="pw-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="pw-product-footer">
            <div class="pw-product-price"><?= sf_currency($price, $cur); ?></div>
            <div class="pw-card-actions">
              <button class="pw-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add to cart"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="pw-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $p->id; ?>,'<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',<?= $p->stock; ?>)" aria-label="Order via WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
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
<div class="pw-section" style="background:var(--pw-cream);">
  <div class="pw-container">
    <div class="pw-section-head">
      <div>
        <div class="pw-section-label">Just Launched</div>
        <h2 class="pw-section-title">New Arrivals</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pw-section-link">View All &rarr;</a>
    </div>
    <div class="pw-product-grid">
      <?php foreach(array_slice($new_arrivals, 0, 4) as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $img = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
      ?>
      <div class="pw-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, 0)">
        <span class="pw-product-badge new">New</span>
        <div class="pw-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
          <?php else: ?>
          <div class="pw-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="pw-product-body">
          <div class="pw-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="pw-product-footer">
            <div class="pw-product-price"><?= sf_currency($price, $cur); ?></div>
            <div class="pw-card-actions">
              <button class="pw-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add to cart"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="pw-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $p->id; ?>,'<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',<?= $p->stock; ?>)" aria-label="Order via WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
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
<div class="pw-section">
  <div class="pw-container">
    <div class="pw-section-head" style="justify-content:center;text-align:center;">
      <h2 class="pw-section-title">Our Trusted Brands</h2>
    </div>
    <div class="pw-brands-grid">
      <?php foreach($brands as $brand): ?>
      <div class="pw-brand"><?= htmlspecialchars($brand->brand_name ?? $brand->name ?? ''); ?></div>
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
<div class="pw-section" style="background:var(--pw-cream);">
  <div class="pw-container">
    <div class="pw-section-head">
      <div>
        <div class="pw-section-label">Reviews</div>
        <h2 class="pw-section-title">What Our Community Says</h2>
      </div>
    </div>
    <div class="pw-testimonials-grid">
      <?php foreach(array_slice($testimonials, 0, 3) as $t): ?>
      <div class="pw-testimonial">
        <div class="pw-testimonial-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
        <div class="pw-testimonial-text"><?= htmlspecialchars($t->testimonial_text ?? $t->message ?? ''); ?></div>
        <div class="pw-testimonial-author"><?= htmlspecialchars($t->customer_name ?? $t->author ?? ''); ?></div>
        <?php if(!empty($t->role ?? $t->customer_role)): ?>
        <div class="pw-testimonial-role"><?= htmlspecialchars($t->role ?? $t->customer_role); ?></div>
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
<div class="pw-section">
  <div class="pw-container">
    <div class="pw-section-head" style="justify-content:center;text-align:center;">
      <h2 class="pw-section-title">Follow Our Wellness Journey</h2>
    </div>
    <div class="pw-insta-grid">
      <?php foreach(array_slice($instagram_posts, 0, 10) as $post): ?>
      <a href="<?= htmlspecialchars($post->permalink ?? $post->link ?? '#'); ?>" target="_blank" class="pw-insta-item">
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
<div class="pw-section">
  <div class="pw-container">
    <div class="pw-section-head" style="justify-content:center;text-align:center;">
      <h2 class="pw-section-title">Frequently Asked Questions</h2>
    </div>
    <div class="pw-faq-list">
      <?php foreach($faqs as $faq): ?>
      <div class="pw-faq-item" onclick="this.classList.toggle('open')">
        <div class="pw-faq-q"><?= htmlspecialchars($faq->question ?? $faq->faq_question ?? ''); ?></div>
        <div class="pw-faq-a"><p><?= htmlspecialchars($faq->answer ?? $faq->faq_answer ?? ''); ?></p></div>
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
<div class="pw-section" style="background:var(--pw-cream);">
  <div class="pw-container">
    <div class="pw-section-head" style="justify-content:center;text-align:center;">
      <h2 class="pw-section-title">Get In Touch</h2>
    </div>
    <div class="pw-contact-grid">
      <?php if(!empty($settings->store_phone)): ?>
      <div class="pw-contact-card">
        <div class="pw-contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></div>
        <div class="pw-contact-label">Phone</div>
        <div class="pw-contact-value"><?= htmlspecialchars($settings->store_phone); ?></div>
      </div>
      <?php endif; ?>
      <?php if(!empty($settings->store_email)): ?>
      <div class="pw-contact-card">
        <div class="pw-contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
        <div class="pw-contact-label">Email</div>
        <div class="pw-contact-value"><?= htmlspecialchars($settings->store_email); ?></div>
      </div>
      <?php endif; ?>
      <?php if($waNumber): ?>
      <div class="pw-contact-card">
        <div class="pw-contact-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg></div>
        <div class="pw-contact-label">WhatsApp</div>
        <div class="pw-contact-value"><?= htmlspecialchars($settings->whatsapp_number); ?></div>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php
      break;

    // =====================================================
    // WELLNESS CONSULTATION CTA
    // =====================================================
    case 'wellness_cta':
?>
<!-- WELLNESS CTA -->
<div class="pw-section">
  <div class="pw-container">
    <div class="pw-wellness-cta">
      <div class="pw-wellness-cta-title">Not Sure What You Need?</div>
      <p class="pw-wellness-cta-text">Our wellness experts are here to guide you to the right products for your health goals.</p>
      <a href="https://wa.me/<?= $waNumber; ?>" target="_blank" class="pw-btn">Chat with a Wellness Expert</a>
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
<div class="pw-section">
  <div class="pw-container">
    <div class="pw-wa-cta">
      <div class="pw-wa-cta-title">Need Wellness Advice?</div>
      <p class="pw-wa-cta-text">Message us on WhatsApp for personalized product recommendations and health tips.</p>
      <a href="https://wa.me/<?= $waNumber; ?>" target="_blank" class="pw-btn">Start WhatsApp Chat</a>
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
<div class="pw-section">
  <div class="pw-container">
    <div class="pw-newsletter">
      <div class="pw-newsletter-title">Join Our Wellness Community</div>
      <p class="pw-newsletter-text">Get wellness tips, new arrivals, and exclusive offers delivered to your inbox.</p>
      <form class="pw-newsletter-form" onsubmit="return mpNewsletterSubmit(event)">
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
<div class="pw-section" style="background:var(--pw-cream);">
  <div class="pw-container">
    <div class="pw-section-head" style="justify-content:center;text-align:center;">
      <h2 class="pw-section-title">Opening Hours</h2>
    </div>
    <div class="pw-hours-grid">
      <?php foreach($business_hours as $line):
        $line = trim($line);
        if(strpos($line, ':') !== false):
          list($day, $time) = array_map('trim', explode(':', $line, 2)); ?>
      <div class="pw-hours-row">
        <span class="pw-hours-day"><?= htmlspecialchars(ucfirst($day)); ?></span>
        <span class="pw-hours-time"><?= htmlspecialchars($time); ?></span>
      </div>
      <?php else: ?>
      <div class="pw-hours-row">
        <span class="pw-hours-full"><?= htmlspecialchars($line); ?></span>
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
  const storeName = '<?= htmlspecialchars(addslashes($store->store_name ?? 'Wellness Pharmacy')); ?>';
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
