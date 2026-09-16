<?php
/**
 * Daily Cart — Mini Mart / Convenience Homepage
 * Friendly neighbourhood design with deep blue and warm amber accents.
 * Poppins typography, rounded cards, quick essentials focus.
 * Respects the homepage builder section order and visibility.
 * Uses mp_minified_image_url() for all imagery (on-demand resize + cache).
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
    --dc-blue:#2563EB;
    --dc-blue-dark:#1D4ED8;
    --dc-blue-deep:#1E3A8A;
    --dc-amber:#F59E0B;
    --dc-amber-dark:#D97706;
    --dc-cream:#EFF6FF;
    --dc-soft:#DBEAFE;
    --dc-dark:#1E293B;
    --dc-gray:#64748B;
    --dc-border:#E2E8F0;
    --dc-white:#FFFFFF;
  }

  .theme-daily_cart .mp-topbar,
  .theme-daily_cart .mp-announcement,
  .theme-daily_cart .mp-nav,
  .theme-daily_cart .mp-header,
  .theme-daily_cart .mp-mobile-menu-btn,
  .theme-daily_cart .mp-footer-space { display:none !important; }

  .dc-container { max-width:1400px; margin:0 auto; padding:0 24px; }
  @media(max-width:767px){ .dc-container { padding:0 16px; } }

  .dc-hero { position:relative; background:linear-gradient(135deg, #1E3A8A 0%, #2563EB 50%, #1D4ED8 100%); color:#fff; overflow:hidden; min-height:520px; display:flex; align-items:center; }
  @media(max-width:767px){ .dc-hero { min-height:460px; } }
  .dc-hero::before { content:''; position:absolute; inset:0; background-image:url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.04'%3E%3Ccircle cx='30' cy='30' r='4'/%3E%3C/g%3E%3C/svg%3E"); }
  .dc-hero-media { position:absolute; inset:0; opacity:0.22; }
  .dc-hero-media img { width:100%; height:100%; object-fit:cover; }
  .dc-hero-gradient { position:absolute; inset:0; background:linear-gradient(90deg, rgba(30,58,138,0.86) 0%, rgba(37,99,235,0.55) 60%, rgba(29,78,216,0.15) 100%); }
  .dc-hero-content { position:relative; z-index:2; padding:90px 24px 110px; max-width:1400px; margin:0 auto; width:100%; }
  .dc-hero-kicker { font-family:'Poppins',sans-serif; font-size:12px; text-transform:uppercase; letter-spacing:0.16em; color:#BFDBFE; margin-bottom:16px; font-weight:600; }
  .dc-hero-title { font-family:'Poppins',sans-serif; font-size:clamp(34px,5vw,56px); line-height:1.08; font-weight:800; margin:0 0 18px; max-width:640px; letter-spacing:-0.02em; }
  .dc-hero-lead { font-family:'Poppins',sans-serif; font-size:clamp(15px,1.8vw,18px); line-height:1.65; opacity:0.95; margin-bottom:32px; max-width:500px; }
  .dc-hero-actions { display:flex; gap:12px; flex-wrap:wrap; }

  .dc-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:14px 32px; border-radius:999px; font-family:'Poppins',sans-serif; font-size:14px; font-weight:600; transition:transform .15s, background .2s, color .2s; cursor:pointer; border:none; text-decoration:none; }
  .dc-btn:active { transform:scale(0.98); }
  .dc-btn-amber { background:var(--dc-amber); color:var(--dc-dark); }
  .dc-btn-amber:hover { background:var(--dc-amber-dark); }
  .dc-btn-blue { background:#fff; color:var(--dc-blue); }
  .dc-btn-blue:hover { background:var(--dc-cream); }
  .dc-btn-ghost { background:rgba(255,255,255,0.12); color:#fff; border:1.5px solid rgba(255,255,255,0.4); backdrop-filter:blur(4px); }
  .dc-btn-ghost:hover { background:rgba(255,255,255,0.2); }
  .dc-btn-outline { background:#fff; color:var(--dc-blue); border:1.5px solid var(--dc-blue); }
  .dc-btn-outline:hover { background:var(--dc-blue); color:#fff; }

  .dc-section { padding:64px 0; }
  @media(max-width:767px){ .dc-section { padding:40px 0; } }
  .dc-section-head { display:flex; align-items:flex-end; justify-content:space-between; gap:16px; margin-bottom:32px; }
  .dc-section-label { font-family:'Poppins',sans-serif; font-size:12px; text-transform:uppercase; letter-spacing:0.14em; color:var(--dc-amber); font-weight:600; margin-bottom:6px; }
  .dc-section-title { font-family:'Poppins',sans-serif; font-size:30px; margin:0; font-weight:800; color:var(--dc-dark); letter-spacing:-0.02em; }
  .dc-section-link { font-family:'Poppins',sans-serif; font-size:14px; font-weight:600; color:var(--dc-blue); text-decoration:none; transition:color .2s; }
  .dc-section-link:hover { color:var(--dc-blue-dark); }

  .dc-product-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:20px; }
  @media(max-width:1023px){ .dc-product-grid { grid-template-columns:repeat(3,1fr); } }
  @media(max-width:767px){ .dc-product-grid { grid-template-columns:repeat(2,1fr); gap:12px; } }
  .dc-product-card { position:relative; background:#fff; border-radius:20px; overflow:hidden; border:1px solid var(--dc-border); transition:transform .25s, box-shadow .25s; cursor:pointer; }
  .dc-product-card:hover { transform:translateY(-6px); box-shadow:0 16px 40px rgba(37,99,235,0.12); }
  .dc-product-badge { position:absolute; top:12px; left:12px; background:var(--dc-amber); color:var(--dc-dark); font-family:'Poppins',sans-serif; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.04em; padding:5px 11px; border-radius:999px; z-index:2; }
  .dc-product-badge.new { background:var(--dc-blue); color:#fff; }
  .dc-product-media { aspect-ratio:1/1; overflow:hidden; background:var(--dc-cream); }
  .dc-product-media img { width:100%; height:100%; object-fit:cover; transition:transform .4s ease; }
  .dc-product-card:hover .dc-product-media img { transform:scale(1.05); }
  .dc-product-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--dc-soft); color:var(--dc-blue); }
  .dc-product-placeholder span { font-family:'Poppins',sans-serif; font-size:38px; font-weight:800; }
  .dc-product-body { padding:16px; }
  .dc-product-brand { font-family:'Poppins',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.08em; color:var(--dc-blue); font-weight:600; margin-bottom:4px; }
  .dc-product-name { font-family:'Poppins',sans-serif; font-size:15px; font-weight:600; margin-bottom:10px; line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; min-height:42px; color:var(--dc-dark); }
  .dc-product-footer { display:flex; flex-direction:column; gap:8px; }
  .dc-product-price { font-family:'Poppins',sans-serif; font-size:18px; font-weight:800; color:var(--dc-dark); }
  .dc-product-price .old { font-family:'Poppins',sans-serif; font-size:13px; color:var(--dc-gray); text-decoration:line-through; margin-left:6px; font-weight:400; }
  .dc-card-actions { display:flex; flex-direction:column; gap:8px; }
  .dc-add-btn { width:100%; padding:11px 14px; border-radius:999px; background:var(--dc-blue); color:#fff; display:flex; align-items:center; justify-content:center; gap:6px; transition:background .2s, transform .15s; border:none; cursor:pointer; font-family:'Poppins',sans-serif; font-size:13px; font-weight:600; }
  .dc-add-btn:hover { background:var(--dc-blue-dark); }
  .dc-add-btn:active { transform:scale(0.97); }
  .dc-wa-btn { width:100%; padding:11px 14px; border-radius:999px; background:#25D366; color:#fff; display:flex; align-items:center; justify-content:center; gap:6px; transition:background .2s, transform .15s; border:none; cursor:pointer; font-family:'Poppins',sans-serif; font-size:13px; font-weight:600; }
  .dc-wa-btn:hover { background:#1FB855; }
  .dc-wa-btn:active { transform:scale(0.97); }
  .dc-product-stock { font-family:'Poppins',sans-serif; font-size:11px; color:#DC2626; font-weight:600; margin-top:6px; text-transform:uppercase; letter-spacing:0.04em; }
  .dc-product-stock.in { color:var(--dc-blue); }

  .dc-cat-grid { display:grid; grid-template-columns:repeat(5,1fr); gap:16px; }
  @media(max-width:1023px){ .dc-cat-grid { grid-template-columns:repeat(3,1fr); } }
  @media(max-width:767px){ .dc-cat-grid { grid-template-columns:repeat(2,1fr); gap:10px; } }
  .dc-cat-card { background:#fff; border:1px solid var(--dc-border); border-radius:18px; padding:20px; text-align:center; text-decoration:none; color:inherit; transition:transform .25s, box-shadow .25s, border-color .2s; }
  .dc-cat-card:hover { transform:translateY(-6px); box-shadow:0 16px 40px rgba(37,99,235,0.12); border-color:var(--dc-blue); }
  .dc-cat-icon { width:60px; height:60px; border-radius:999px; background:var(--dc-cream); display:flex; align-items:center; justify-content:center; margin:0 auto 14px; color:var(--dc-blue); font-size:24px; }
  .dc-cat-name { font-family:'Poppins',sans-serif; font-size:15px; font-weight:700; color:var(--dc-dark); }
  .dc-cat-count { font-family:'Poppins',sans-serif; font-size:12px; color:var(--dc-gray); margin-top:2px; }

  .dc-trust { background:var(--dc-cream); padding:52px 0; }
  .dc-trust-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:18px; }
  @media(max-width:1023px){ .dc-trust-grid { grid-template-columns:repeat(2,1fr); } }
  @media(max-width:767px){ .dc-trust-grid { grid-template-columns:1fr; } }
  .dc-trust-item { text-align:center; padding:24px 16px; background:#fff; border-radius:20px; border:1px solid var(--dc-border); transition:transform .2s; }
  .dc-trust-item:hover { transform:translateY(-4px); }
  .dc-trust-item-icon { width:52px; height:52px; border-radius:999px; background:rgba(37,99,235,0.08); display:flex; align-items:center; justify-content:center; font-size:24px; margin:0 auto 14px; color:var(--dc-blue); }
  .dc-trust-item-title { font-family:'Poppins',sans-serif; font-size:16px; font-weight:700; color:var(--dc-dark); margin-bottom:6px; }
  .dc-trust-item-text { font-family:'Poppins',sans-serif; font-size:13px; color:var(--dc-gray); line-height:1.5; }

  .dc-promo { background:linear-gradient(135deg, var(--dc-amber) 0%, var(--dc-amber-dark) 100%); border-radius:24px; overflow:hidden; display:grid; grid-template-columns:1fr 1fr; align-items:center; min-height:240px; color:var(--dc-dark); }
  @media(max-width:767px){ .dc-promo { grid-template-columns:1fr; min-height:auto; } }
  .dc-promo-media { min-height:240px; background:rgba(255,255,255,0.15); }
  .dc-promo-media img { width:100%; height:100%; object-fit:cover; }
  .dc-promo-body { padding:40px; }
  .dc-promo-kicker { font-family:'Poppins',sans-serif; font-size:12px; text-transform:uppercase; letter-spacing:0.14em; color:var(--dc-blue-deep); font-weight:600; margin-bottom:8px; }
  .dc-promo-title { font-family:'Poppins',sans-serif; font-size:clamp(22px,2.5vw,30px); font-weight:800; margin-bottom:10px; letter-spacing:-0.02em; }
  .dc-promo-text { font-family:'Poppins',sans-serif; font-size:15px; color:var(--dc-dark); line-height:1.6; margin-bottom:20px; }

  .dc-essentials { background:#fff; }
  .dc-deal-cta { background:var(--dc-blue-deep); color:#fff; padding:48px 24px; text-align:center; border-radius:24px; }
  .dc-deal-cta-title { font-family:'Poppins',sans-serif; font-size:clamp(22px,2.5vw,30px); font-weight:800; margin-bottom:10px; letter-spacing:-0.02em; }
  .dc-deal-cta-text { font-family:'Poppins',sans-serif; opacity:0.9; max-width:440px; margin:0 auto 24px; font-size:15px; line-height:1.6; }
  .dc-wa-cta { background:linear-gradient(135deg, #25D366, #1DA851); color:#fff; padding:48px 24px; text-align:center; border-radius:24px; }
  .dc-wa-cta-title { font-family:'Poppins',sans-serif; font-size:clamp(22px,2.5vw,30px); font-weight:800; margin-bottom:10px; letter-spacing:-0.02em; }
  .dc-wa-cta-text { font-family:'Poppins',sans-serif; opacity:0.95; max-width:440px; margin:0 auto 24px; font-size:15px; }
  .dc-newsletter { background:var(--dc-blue); color:#fff; padding:48px 24px; text-align:center; border-radius:24px; }
  .dc-newsletter-title { font-family:'Poppins',sans-serif; font-size:clamp(22px,2.5vw,30px); font-weight:800; margin-bottom:10px; letter-spacing:-0.02em; }
  .dc-newsletter-text { font-family:'Poppins',sans-serif; opacity:0.9; max-width:440px; margin:0 auto 24px; font-size:15px; }
  .dc-newsletter-form { display:flex; gap:10px; max-width:440px; margin:0 auto; }
  .dc-newsletter-form input { flex:1; padding:14px 18px; border:none; border-radius:999px; font-family:'Poppins',sans-serif; font-size:14px; outline:none; }
  .dc-newsletter-form button { padding:14px 24px; border:none; border-radius:999px; background:var(--dc-amber); color:var(--dc-dark); font-family:'Poppins',sans-serif; font-weight:600; cursor:pointer; font-size:14px; transition:background .2s; }
  .dc-newsletter-form button:hover { background:var(--dc-amber-dark); }

  .dc-testimonials-grid, .dc-contact-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:18px; }
  @media(max-width:767px){ .dc-testimonials-grid, .dc-contact-grid { grid-template-columns:1fr; } }
  .dc-testimonial, .dc-contact-card { background:#fff; border:1px solid var(--dc-border); border-radius:20px; padding:24px; }
  .dc-testimonial-stars { color:var(--dc-amber); margin-bottom:12px; font-size:16px; letter-spacing:2px; }
  .dc-testimonial-text { font-family:'Poppins',sans-serif; font-size:14px; color:var(--dc-gray); line-height:1.7; margin-bottom:16px; }
  .dc-testimonial-author { font-family:'Poppins',sans-serif; font-size:15px; font-weight:700; color:var(--dc-dark); }
  .dc-testimonial-role { font-family:'Poppins',sans-serif; font-size:12px; color:var(--dc-blue); margin-top:2px; }
  .dc-contact-card { text-align:center; transition:transform .2s; }
  .dc-contact-card:hover { transform:translateY(-4px); }
  .dc-contact-icon { width:52px; height:52px; border-radius:999px; background:rgba(37,99,235,0.08); display:flex; align-items:center; justify-content:center; margin:0 auto 14px; color:var(--dc-blue); }
  .dc-contact-label { font-family:'Poppins',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.12em; color:var(--dc-amber); font-weight:600; margin-bottom:4px; }
  .dc-contact-value { font-family:'Poppins',sans-serif; font-size:15px; font-weight:600; color:var(--dc-dark); }

  .dc-brands-grid { display:flex; flex-wrap:wrap; gap:14px; align-items:center; justify-content:center; }
  .dc-brand { padding:12px 24px; background:#fff; border:1px solid var(--dc-border); border-radius:999px; font-family:'Poppins',sans-serif; font-size:14px; font-weight:600; color:var(--dc-dark); transition:border-color .2s, color .2s; }
  .dc-brand:hover { border-color:var(--dc-blue); color:var(--dc-blue); }
  .dc-insta-grid { display:grid; grid-template-columns:repeat(5,1fr); gap:12px; }
  @media(max-width:767px){ .dc-insta-grid { grid-template-columns:repeat(3,1fr); } }
  .dc-insta-item { aspect-ratio:1; border-radius:16px; overflow:hidden; }
  .dc-insta-item img { width:100%; height:100%; object-fit:cover; transition:transform .3s; }
  .dc-insta-item:hover img { transform:scale(1.08); }

  .dc-faq-list { max-width:760px; margin:0 auto; }
  .dc-faq-item { background:#fff; border:1px solid var(--dc-border); border-radius:16px; margin-bottom:10px; overflow:hidden; }
  .dc-faq-q { padding:18px 24px; font-family:'Poppins',sans-serif; font-size:15px; font-weight:600; color:var(--dc-dark); cursor:pointer; display:flex; justify-content:space-between; align-items:center; }
  .dc-faq-q::after { content:'+'; font-size:20px; color:var(--dc-blue); font-weight:700; }
  .dc-faq-item.open .dc-faq-q::after { content:'\2212'; }
  .dc-faq-a { max-height:0; overflow:hidden; transition:max-height .3s ease; padding:0 24px; }
  .dc-faq-item.open .dc-faq-a { max-height:300px; padding:0 24px 18px; }
  .dc-faq-a p { font-family:'Poppins',sans-serif; font-size:14px; color:var(--dc-gray); line-height:1.7; margin:0; }

  .dc-hours-grid { max-width:520px; margin:0 auto; background:#fff; border:1px solid var(--dc-border); border-radius:20px; padding:0 24px; }
  .dc-hours-row { display:flex; justify-content:space-between; align-items:center; padding:14px 0; border-bottom:1px solid var(--dc-border); font-family:'Poppins',sans-serif; font-size:15px; }
  .dc-hours-row:last-child { border-bottom:none; }
  .dc-hours-day { color:var(--dc-dark); font-weight:600; }
  .dc-hours-time { color:var(--dc-gray); }
  .dc-hours-full { width:100%; text-align:center; color:var(--dc-dark); }

  .wa-order-modal { display:none; position:fixed; inset:0; z-index:9999; }
  .wa-order-modal.open { display:block; }
  .wa-order-modal-overlay { position:absolute; inset:0; background:rgba(30,58,138,0.4); }
  .wa-order-modal-card { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:440px; max-width:calc(100vw - 32px); max-height:90vh; overflow-y:auto; background:#fff; border-radius:20px; box-shadow:0 24px 60px rgba(37,99,235,0.25); border:1px solid var(--dc-border); }
  .wa-order-modal-header { display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid var(--dc-border); }
  .wa-order-modal-title { font-family:'Poppins',sans-serif; font-size:20px; font-weight:800; color:var(--dc-dark); margin:0; }
  .wa-order-modal-close { width:32px; height:32px; border-radius:999px; border:none; background:var(--dc-cream); color:var(--dc-dark); cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background .2s; }
  .wa-order-modal-close:hover { background:var(--dc-border); }
  .wa-order-modal-body { padding:24px; }
  .wa-order-modal-product { display:flex; gap:14px; align-items:center; padding:14px; background:var(--dc-cream); border-radius:12px; margin-bottom:20px; }
  .wa-order-modal-product img { width:56px; height:56px; border-radius:8px; object-fit:cover; flex-shrink:0; }
  .wa-order-modal-product-info { flex:1; min-width:0; }
  .wa-order-modal-product-name { font-family:'Poppins',sans-serif; font-size:15px; font-weight:600; color:var(--dc-dark); margin:0 0 4px; }
  .wa-order-modal-product-price { font-family:'Poppins',sans-serif; font-size:16px; font-weight:800; color:var(--dc-blue); }
  .wa-order-modal-fields { display:flex; flex-direction:column; gap:14px; }
  .wa-order-modal-fields label { font-family:'Poppins',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.08em; font-weight:600; color:var(--dc-dark); margin-bottom:5px; display:block; }
  .wa-order-modal-fields input, .wa-order-modal-fields textarea { width:100%; padding:12px 14px; border:1px solid var(--dc-border); border-radius:10px; font-family:'Poppins',sans-serif; font-size:14px; background:var(--dc-cream); outline:none; transition:border-color .2s; color:var(--dc-dark); box-sizing:border-box; }
  .wa-order-modal-fields input:focus, .wa-order-modal-fields textarea:focus { border-color:var(--dc-blue); background:#fff; }
  .wa-order-modal-fields textarea { resize:vertical; min-height:64px; }
  .wa-order-modal-actions { display:flex; gap:10px; margin-top:20px; }
  .wa-order-modal-send { flex:1; padding:14px; border-radius:999px; border:none; background:#25D366; color:#fff; font-family:'Poppins',sans-serif; font-size:14px; font-weight:600; cursor:pointer; transition:background .2s; display:flex; align-items:center; justify-content:center; gap:8px; }
  .wa-order-modal-send:hover { background:#1FB855; }
  .wa-order-modal-cancel { padding:14px 20px; border-radius:999px; border:1px solid var(--dc-border); background:#fff; color:var(--dc-dark); font-family:'Poppins',sans-serif; font-size:14px; font-weight:600; cursor:pointer; transition:background .2s; }
  .wa-order-modal-cancel:hover { background:var(--dc-cream); }

  /* Small-screen polish */
  @media(max-width:480px){
    .dc-product-body { padding:12px; }
    .dc-product-name { font-size:14px; min-height:38px; }
    .dc-product-price { font-size:16px; }
    .dc-add-btn, .dc-wa-btn { padding:10px 12px; font-size:12px; }
    .dc-newsletter-form { flex-direction:column; }
    .dc-newsletter-form button { width:100%; }
    .dc-section-head { flex-direction:column; align-items:flex-start; gap:8px; }
    .dc-cat-card { padding:14px 10px; }
    .dc-cat-name { font-size:13px; }
    .dc-hero-actions .dc-btn { width:100%; }
    .dc-promo-body { padding:24px 20px; }
  }
</style>

<?php
foreach($orderedSections as $sectionKey => $section):
  if(!$section->is_enabled) continue;

  switch($sectionKey):
    case 'hero_banner':
?>
<?php if($heroImg): ?>
<div class="dc-hero">
  <div class="dc-hero-media"><img src="<?= $heroImg; ?>" alt="<?= htmlspecialchars($hero->banner_title ?? ($store->store_name ?? 'Daily Cart')); ?>" decoding="async"></div>
  <div class="dc-hero-gradient"></div>
  <div class="dc-hero-content">
    <p class="dc-hero-kicker"><?= htmlspecialchars($hero->banner_subtitle ?? 'Your neighbourhood mini mart'); ?></p>
    <h1 class="dc-hero-title"><?= htmlspecialchars($hero->banner_title ?? ($settings->store_headline ?: ($store->store_name ?? 'Daily Essentials, Always Open'))); ?></h1>
    <p class="dc-hero-lead"><?= htmlspecialchars($settings->store_subheadline ?: 'Snacks, drinks, toiletries and everyday items — ready when you need them. Quick shopping for busy lives.'); ?></p>
    <div class="dc-hero-actions">
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="dc-btn dc-btn-amber">Shop Essentials</a>
      <a href="https://wa.me/<?= $waNumber; ?>" target="_blank" class="dc-btn dc-btn-ghost">Order on WhatsApp</a>
    </div>
  </div>
</div>
<?php else: ?>
<div class="dc-hero">
  <div class="dc-hero-content">
    <p class="dc-hero-kicker"><?= htmlspecialchars($settings->store_subheadline ?? 'Your neighbourhood mini mart'); ?></p>
    <h1 class="dc-hero-title"><?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Daily Essentials, Always Open')); ?></h1>
    <p class="dc-hero-lead"><?= htmlspecialchars($settings->store_description ?: 'Snacks, drinks, toiletries and everyday items — ready when you need them. Quick shopping for busy lives.'); ?></p>
    <div class="dc-hero-actions">
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="dc-btn dc-btn-amber">Shop Essentials</a>
      <a href="https://wa.me/<?= $waNumber; ?>" target="_blank" class="dc-btn dc-btn-ghost">Order on WhatsApp</a>
    </div>
  </div>
</div>
<?php endif; ?>
<?php
      break;

    case 'trust_badges':
      $dcBadges = json_decode($settings->trust_badges_json ?? '', true);
      if(empty($dcBadges) || !is_array($dcBadges)) break;
?>
<div class="dc-trust">
  <div class="dc-container">
    <div class="dc-trust-grid">
      <?php foreach(array_slice($dcBadges, 0, 4) as $b): ?>
      <div class="dc-trust-item">
        <div class="dc-trust-item-icon"><?= !empty($b['icon']) ? $b['icon'] : '&#10004;'; ?></div>
        <div class="dc-trust-item-title"><?= htmlspecialchars($b['title'] ?? ''); ?></div>
        <div class="dc-trust-item-text"><?= htmlspecialchars($b['desc'] ?? ''); ?></div>
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
<div class="dc-section" style="background:var(--dc-cream);">
  <div class="dc-container">
    <div class="dc-promo">
      <?php if($promoImg): ?>
      <div class="dc-promo-media"><img src="<?= $promoImg; ?>" alt="<?= htmlspecialchars($promo->banner_title ?? 'Promo'); ?>" decoding="async"></div>
      <?php endif; ?>
      <div class="dc-promo-body">
        <div class="dc-promo-kicker"><?= htmlspecialchars($promo->banner_subtitle ?? 'Daily Deal'); ?></div>
        <h3 class="dc-promo-title"><?= htmlspecialchars($promo->banner_title ?? 'Today’s Special'); ?></h3>
        <p class="dc-promo-text">Grab it before it is gone. Limited stock, open-late convenience.</p>
        <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="dc-btn dc-btn-blue">Shop Now</a>
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
<div class="dc-section dc-essentials">
  <div class="dc-container">
    <div class="dc-section-head">
      <div>
        <div class="dc-section-label">Quick Shop</div>
        <h2 class="dc-section-title">Essential Categories</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="dc-section-link">View All &rarr;</a>
    </div>
    <div class="dc-cat-grid">
      <?php foreach(array_slice($categories, 0, 10) as $cat):
        $catImg = (!empty($cat->category_image) && file_exists($cat->category_image)) ? mp_minified_image_url($cat->category_image, 200) : '';
        $itemCount = $cat->item_count ?? 0;
      ?>
      <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="dc-cat-card">
        <div class="dc-cat-icon">
          <?php if($catImg): ?>
          <img src="<?= $catImg; ?>" alt="<?= htmlspecialchars($cat->category_name); ?>" loading="lazy" decoding="async" style="width:40px;height:40px;object-fit:cover;border-radius:999px;">
          <?php else: ?>
          <span style="font-family:'Poppins',sans-serif;font-size:22px;font-weight:800;"><?= htmlspecialchars(substr($cat->category_name, 0, 1)); ?></span>
          <?php endif; ?>
        </div>
        <div class="dc-cat-name"><?= htmlspecialchars($cat->category_name); ?></div>
        <?php if($itemCount > 0): ?>
        <div class="dc-cat-count"><?= $itemCount; ?> items</div>
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
<div class="dc-section" style="background:var(--dc-cream);">
  <div class="dc-container">
    <div class="dc-section-head">
      <div>
        <div class="dc-section-label">Top Picks</div>
        <h2 class="dc-section-title">Featured Products</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="dc-section-link">View All &rarr;</a>
    </div>
    <div class="dc-product-grid">
      <?php foreach(array_slice($featured_products, 0, 8) as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $oldPrice = $p->original_price ?? $p->sales_price;
        $hasDiscount = $oldPrice > $price;
        $discountPct = $hasDiscount ? round((($oldPrice - $price) / $oldPrice) * 100) : 0;
        $img = ($p->item_image && file_exists($p->item_image)) ? mp_minified_image_url($p->item_image, 600) : '';
      ?>
      <div class="dc-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, <?= $hasDiscount ? $oldPrice : 0; ?>)">
        <?php if($hasDiscount && $discountPct > 0): ?>
        <span class="dc-product-badge">-<?= $discountPct; ?>%</span>
        <?php endif; ?>
        <div class="dc-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy" decoding="async">
          <?php else: ?>
          <div class="dc-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="dc-product-body">
          <?php if(!empty($p->category_name)): ?>
          <div class="dc-product-brand"><?= htmlspecialchars($p->category_name); ?></div>
          <?php endif; ?>
          <div class="dc-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="dc-product-footer">
            <div class="dc-product-price"><?= sf_currency($price, $cur); ?><?php if($hasDiscount): ?><span class="old"><?= sf_currency($oldPrice, $cur); ?></span><?php endif; ?></div>
            <div class="dc-card-actions">
              <button class="dc-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add to cart"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="dc-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $p->id; ?>,'<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',<?= $p->stock; ?>)" aria-label="Order via WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
            </div>
          </div>
          <?php if($p->stock <= 0 && !($settings->allow_backorder ?? false)): ?>
          <div class="dc-product-stock">Out of Stock</div>
          <?php else: ?>
          <div class="dc-product-stock in">In Stock</div>
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
<div class="dc-section" style="background:var(--dc-cream);">
  <div class="dc-container">
    <div class="dc-section-head">
      <div>
        <div class="dc-section-label">Services</div>
        <h2 class="dc-section-title">Store Services</h2>
      </div>
    </div>
    <div class="dc-product-grid">
      <?php foreach(array_slice($featured_services, 0, 4) as $s):
        $sPrice = $s->effective_price ?? $s->sales_price ?? $s->price ?? 0;
        $sImg = (!empty($s->item_image) && file_exists($s->item_image)) ? mp_minified_image_url($s->item_image, 600) : (!empty($s->service_image) && file_exists($s->service_image) ? mp_minified_image_url($s->service_image, 600) : '');
      ?>
      <div class="dc-product-card" onclick="openProductModal(<?= $s->id; ?>, '<?= htmlspecialchars(addslashes($s->item_name ?? $s->service_name ?? '')); ?>', <?= $sPrice; ?>, '<?= $s->item_image ?? $s->service_image ?? ''; ?>', '<?= htmlspecialchars(addslashes($s->description ?? '')); ?>', 999, 0)">
        <div class="dc-product-media">
          <?php if($sImg): ?>
          <img src="<?= $sImg; ?>" alt="<?= htmlspecialchars($s->item_name ?? $s->service_name ?? ''); ?>" loading="lazy" decoding="async">
          <?php else: ?>
          <div class="dc-product-placeholder"><span><?= htmlspecialchars(substr($s->item_name ?? $s->service_name ?? 'S', 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="dc-product-body">
          <div class="dc-product-name"><?= htmlspecialchars($s->item_name ?? $s->service_name ?? ''); ?></div>
          <div class="dc-product-footer">
            <div class="dc-product-price"><?= sf_currency($sPrice, $cur); ?></div>
            <div class="dc-card-actions">
              <button class="dc-add-btn" onclick="event.stopPropagation();addToCart(<?= $s->id; ?>,'service','<?= htmlspecialchars(addslashes($s->item_name ?? $s->service_name ?? '')); ?>',<?= $sPrice; ?>,'<?= $s->item_image ?? $s->service_image ?? ''; ?>',1,999)" aria-label="Add to cart"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="dc-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $s->id; ?>,'<?= htmlspecialchars(addslashes($s->item_name ?? $s->service_name ?? '')); ?>',<?= $sPrice; ?>,'<?= $s->item_image ?? $s->service_image ?? ''; ?>',999)" aria-label="Order via WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
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
<div class="dc-section">
  <div class="dc-container">
    <div class="dc-section-head">
      <div>
        <div class="dc-section-label">Customer Favourites</div>
        <h2 class="dc-section-title">Best Sellers</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="dc-section-link">View All &rarr;</a>
    </div>
    <div class="dc-product-grid">
      <?php foreach(array_slice($best_sellers, 0, 4) as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $img = ($p->item_image && file_exists($p->item_image)) ? mp_minified_image_url($p->item_image, 600) : '';
      ?>
      <div class="dc-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, 0)">
        <div class="dc-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy" decoding="async">
          <?php else: ?>
          <div class="dc-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="dc-product-body">
          <div class="dc-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="dc-product-footer">
            <div class="dc-product-price"><?= sf_currency($price, $cur); ?></div>
            <div class="dc-card-actions">
              <button class="dc-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add to cart"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="dc-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $p->id; ?>,'<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',<?= $p->stock; ?>)" aria-label="Order via WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
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
<div class="dc-section" style="background:var(--dc-cream);">
  <div class="dc-container">
    <div class="dc-section-head">
      <div>
        <div class="dc-section-label">Just In</div>
        <h2 class="dc-section-title">New Arrivals</h2>
      </div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="dc-section-link">View All &rarr;</a>
    </div>
    <div class="dc-product-grid">
      <?php foreach(array_slice($new_arrivals, 0, 4) as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $img = ($p->item_image && file_exists($p->item_image)) ? mp_minified_image_url($p->item_image, 600) : '';
      ?>
      <div class="dc-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, 0)">
        <span class="dc-product-badge new">New</span>
        <div class="dc-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy" decoding="async">
          <?php else: ?>
          <div class="dc-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="dc-product-body">
          <div class="dc-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="dc-product-footer">
            <div class="dc-product-price"><?= sf_currency($price, $cur); ?></div>
            <div class="dc-card-actions">
              <button class="dc-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add to cart"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="dc-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $p->id; ?>,'<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',<?= $p->stock; ?>)" aria-label="Order via WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
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
<div class="dc-section">
  <div class="dc-container">
    <div class="dc-section-head" style="justify-content:center;text-align:center;">
      <h2 class="dc-section-title">Brands We Stock</h2>
    </div>
    <div class="dc-brands-grid">
      <?php foreach($brands as $brand): ?>
      <div class="dc-brand"><?= htmlspecialchars($brand->brand_name ?? $brand->name ?? ''); ?></div>
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
<div class="dc-section" style="background:var(--dc-cream);">
  <div class="dc-container">
    <div class="dc-section-head">
      <div>
        <div class="dc-section-label">Customer Reviews</div>
        <h2 class="dc-section-title">What Neighbours Say</h2>
      </div>
    </div>
    <div class="dc-testimonials-grid">
      <?php foreach(array_slice($testimonials, 0, 3) as $t): ?>
      <div class="dc-testimonial">
        <div class="dc-testimonial-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</div>
        <div class="dc-testimonial-text"><?= htmlspecialchars($t->testimonial_text ?? $t->message ?? ''); ?></div>
        <div class="dc-testimonial-author"><?= htmlspecialchars($t->customer_name ?? $t->author ?? ''); ?></div>
        <?php if(!empty($t->role ?? $t->customer_role)): ?>
        <div class="dc-testimonial-role"><?= htmlspecialchars($t->role ?? $t->customer_role); ?></div>
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
<div class="dc-section">
  <div class="dc-container">
    <div class="dc-section-head" style="justify-content:center;text-align:center;">
      <h2 class="dc-section-title">Follow Us</h2>
    </div>
    <div class="dc-insta-grid">
      <?php foreach(array_slice($instagram_posts, 0, 10) as $post): ?>
      <a href="<?= htmlspecialchars($post->permalink ?? $post->link ?? '#'); ?>" target="_blank" class="dc-insta-item">
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
<div class="dc-section">
  <div class="dc-container">
    <div class="dc-section-head" style="justify-content:center;text-align:center;">
      <h2 class="dc-section-title">Frequently Asked Questions</h2>
    </div>
    <div class="dc-faq-list">
      <?php foreach($faqs as $faq): ?>
      <div class="dc-faq-item" onclick="this.classList.toggle('open')">
        <div class="dc-faq-q"><?= htmlspecialchars($faq->question ?? $faq->faq_question ?? ''); ?></div>
        <div class="dc-faq-a"><p><?= htmlspecialchars($faq->answer ?? $faq->faq_answer ?? ''); ?></p></div>
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
<div class="dc-section" style="background:var(--dc-cream);">
  <div class="dc-container">
    <div class="dc-section-head" style="justify-content:center;text-align:center;">
      <h2 class="dc-section-title">Visit or Contact Us</h2>
    </div>
    <div class="dc-contact-grid">
      <?php if(!empty($settings->store_phone)): ?>
      <div class="dc-contact-card">
        <div class="dc-contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg></div>
        <div class="dc-contact-label">Phone</div>
        <div class="dc-contact-value"><?= htmlspecialchars($settings->store_phone); ?></div>
      </div>
      <?php endif; ?>
      <?php if(!empty($settings->store_email)): ?>
      <div class="dc-contact-card">
        <div class="dc-contact-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg></div>
        <div class="dc-contact-label">Email</div>
        <div class="dc-contact-value"><?= htmlspecialchars($settings->store_email); ?></div>
      </div>
      <?php endif; ?>
      <?php if($waNumber): ?>
      <div class="dc-contact-card">
        <div class="dc-contact-icon"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg></div>
        <div class="dc-contact-label">WhatsApp</div>
        <div class="dc-contact-value"><?= htmlspecialchars($settings->whatsapp_number); ?></div>
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
<div class="dc-section">
  <div class="dc-container">
    <div class="dc-wa-cta">
      <div class="dc-wa-cta-title">Need Something Quick?</div>
      <p class="dc-wa-cta-text">Chat with us on WhatsApp for quick assistance, product enquiries and late-night orders.</p>
      <a href="https://wa.me/<?= $waNumber; ?>" target="_blank" class="dc-btn dc-btn-amber" style="color:var(--dc-dark);">Chat on WhatsApp</a>
    </div>
  </div>
</div>
<?php
      endif;
      break;

    case 'newsletter':
?>
<div class="dc-section">
  <div class="dc-container">
    <div class="dc-newsletter">
      <div class="dc-newsletter-title">Get Daily Deals in Your Inbox</div>
      <p class="dc-newsletter-text">Subscribe for daily specials, new arrivals and exclusive neighbourhood offers.</p>
      <form class="dc-newsletter-form" onsubmit="return mpNewsletterSubmit(event)">
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
<div class="dc-section" style="background:var(--dc-cream);">
  <div class="dc-container">
    <div class="dc-section-head" style="justify-content:center;text-align:center;">
      <h2 class="dc-section-title">Opening Hours</h2>
    </div>
    <div class="dc-hours-grid">
      <?php foreach($business_hours as $line):
        $line = trim($line);
        if(strpos($line, ':') !== false):
          list($day, $time) = array_map('trim', explode(':', $line, 2)); ?>
      <div class="dc-hours-row">
        <span class="dc-hours-day"><?= htmlspecialchars(ucfirst($day)); ?></span>
        <span class="dc-hours-time"><?= htmlspecialchars($time); ?></span>
      </div>
      <?php else: ?>
      <div class="dc-hours-row">
        <span class="dc-hours-full"><?= htmlspecialchars($line); ?></span>
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
  const storeName = '<?= htmlspecialchars(addslashes($store->store_name ?? 'Daily Cart')); ?>';
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
