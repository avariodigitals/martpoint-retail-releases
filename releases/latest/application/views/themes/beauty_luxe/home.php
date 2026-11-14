<?php
$theme_css_extra = '<style>
/* === Beauty Luxe Premium Theme Overrides === */
:root{ --blush-50:#FFF0F5; --blush-100:#FCE7F3; --blush-200:#FBCFE8; --blush-300:#F9A8D4; --blush-400:#F472B6; --blush-500:#EC4899; --blush-600:#DB2777; --rose-gold:#B76E79; --champagne:#F7E7CE; --pearl:#F8F6F0; }

body{ background:linear-gradient(180deg, #FFF0F5 0%, #F8FAFC 200px); }

/* Hero */
.mp-hero-overlay{ background:linear-gradient(105deg, rgba(236,72,153,0.55) 0%, rgba(251,207,232,0.25) 50%, rgba(255,255,255,0) 100%)!important; }
.mp-hero-btn{ background:linear-gradient(135deg, #B76E79, #D4AF37)!important; color:#fff!important; border-radius:50px!important; padding:14px 36px!important; box-shadow:0 8px 24px rgba(183,110,121,0.35)!important; transition:all .3s ease!important; }
.mp-hero-btn:hover{ transform:translateY(-2px)!important; box-shadow:0 12px 32px rgba(183,110,121,0.45)!important; }
.mp-hero-title{ font-family:"Playfair Display",serif!important; font-weight:700!important; }

/* Section Titles */
.mp-section-title{ font-family:"Playfair Display",serif!important; font-size:28px!important; font-weight:700!important; color:#831843!important; letter-spacing:-0.5px!important; }
.mp-section-title::after{ content:""; display:block; width:48px; height:3px; background:linear-gradient(90deg, #B76E79, #F472B6); border-radius:3px; margin-top:10px; }

/* Cards */
.mp-card{ border-radius:20px!important; border:none!important; box-shadow:0 4px 20px rgba(0,0,0,0.04)!important; transition:all .35s cubic-bezier(0.34, 1.56, 0.64, 1)!important; overflow:hidden!important; }
.mp-card:hover{ transform:translateY(-6px)!important; box-shadow:0 20px 40px rgba(236,72,153,0.12)!important; }
.mp-card-img{ height:220px!important; transition:transform .5s ease!important; }
.mp-card:hover .mp-card-img{ transform:scale(1.06)!important; }
.mp-card-body{ padding:16px!important; }
.mp-card-name{ font-weight:600!important; color:#4a044e!important; }
.mp-card-price{ color:#B76E79!important; font-size:17px!important; }
.mp-card-add{ border-radius:12px!important; background:linear-gradient(135deg, #B76E79, #EC4899)!important; transition:all .2s ease!important; }
.mp-card-add:hover{ filter:brightness(1.1)!important; }

/* Chips / Categories */
.mp-chip{ border-radius:24px!important; border:1.5px solid #FBCFE8!important; background:#fff!important; transition:all .2s ease!important; }
.mp-chip:hover, .mp-chip.active{ background:linear-gradient(135deg, #B76E79, #EC4899)!important; color:#fff!important; border-color:transparent!important; box-shadow:0 4px 12px rgba(236,72,153,0.25)!important; }

/* Promo Cards */
.mp-promo-card{ border-radius:20px!important; box-shadow:0 8px 24px rgba(0,0,0,0.06)!important; }

/* Newsletter */
.mp-newsletter{ background:linear-gradient(135deg, #B76E79, #DB2777)!important; border-radius:20px!important; }

/* Footer */
.mp-footer{ background:linear-gradient(180deg, #4a044e, #2e0230)!important; }

/* Trust Badges */
.mp-trust-item{ background:rgba(255,255,255,0.7)!important; backdrop-filter:blur(8px)!important; border:1px solid rgba(251,207,232,0.6)!important; border-radius:16px!important; }

/* Testimonials */
.mp-testi-card{ background:rgba(255,255,255,0.8)!important; backdrop-filter:blur(10px)!important; border:1px solid rgba(251,207,232,0.5)!important; border-radius:20px!important; box-shadow:0 4px 16px rgba(0,0,0,0.03)!important; }

/* Contact Cards */
.mp-contact-card{ background:rgba(255,255,255,0.85)!important; backdrop-filter:blur(8px)!important; border-radius:16px!important; border:1px solid rgba(251,207,232,0.5)!important; }

/* Beauty Tips Premium Section */
.bl-tips-section{ max-width:1280px; margin:0 auto; padding:64px 24px; }
.bl-tips-header{ text-align:center; margin-bottom:40px; }
.bl-tips-header h2{ font-family:"Playfair Display",serif; font-size:32px; font-weight:700; color:#831843; margin-bottom:8px; }
.bl-tips-header p{ color:#9D174D; font-size:15px; }
.bl-tips-grid{ display:grid; grid-template-columns:repeat(1, 1fr); gap:20px; }
@media(min-width:640px){ .bl-tips-grid{ grid-template-columns:repeat(2, 1fr); } }
@media(min-width:1024px){ .bl-tips-grid{ grid-template-columns:repeat(3, 1fr); } }
.bl-tip-card{ position:relative; background:#fff; border-radius:20px; padding:28px; border:1px solid #FBCFE8; box-shadow:0 4px 20px rgba(236,72,153,0.06); transition:all .35s cubic-bezier(0.34, 1.56, 0.64, 1); overflow:hidden; }
.bl-tip-card::before{ content:""; position:absolute; top:0; left:0; right:0; height:4px; background:linear-gradient(90deg, #B76E79, #F472B6, #FBCFE8); border-radius:20px 20px 0 0; }
.bl-tip-card:hover{ transform:translateY(-6px); box-shadow:0 20px 40px rgba(236,72,153,0.12); }
.bl-tip-icon{ width:52px; height:52px; border-radius:16px; background:linear-gradient(135deg, #FFF0F5, #FCE7F3); display:flex; align-items:center; justify-content:center; margin-bottom:18px; color:#B76E79; box-shadow:0 4px 12px rgba(236,72,153,0.08); }
.bl-tip-icon svg{ width:26px; height:26px; stroke-width:1.8; }
.bl-tip-title{ font-family:"Playfair Display",serif; font-size:18px; font-weight:700; color:#831843; margin-bottom:10px; }
.bl-tip-text{ font-size:14px; line-height:1.7; color:#6B213E; }
.bl-tip-badge{ position:absolute; top:16px; right:16px; width:28px; height:28px; border-radius:50%; background:linear-gradient(135deg, #B76E79, #EC4899); color:#fff; font-size:12px; font-weight:700; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 8px rgba(236,72,153,0.25); }

@media(max-width:767px){
  body{ background:linear-gradient(180deg, #FFF0F5 0%, #F8FAFC 100px)!important; }
  .mp-hero-title{ font-size:28px!important; line-height:1.2!important; }
  .mp-hero-subtitle{ font-size:15px!important; }
  .mp-hero-btn{ padding:12px 24px!important; font-size:14px!important; }
  .mp-section-title{ font-size:22px!important; }
  .mp-card{ border-radius:16px!important; }
  .mp-card-img{ height:160px!important; }
  .mp-section{ padding:32px 16px!important; }
  .bl-tips-section{ padding:40px 16px!important; }
  .bl-tips-header h2{ font-size:24px!important; }
  .bl-tip-card{ padding:20px!important; }
  .mp-newsletter{ padding:32px 16px!important; }
  .mp-newsletter h3{ font-size:20px!important; }
  .mp-trust-item{ padding:14px 12px!important; }
  .mp-testi-card{ padding:14px!important; }
  .mp-contact-card{ padding:14px!important; }
  .mp-promo-grid{ grid-template-columns:1fr!important; gap:12px!important; }
  .mp-promo-card{ border-radius:16px!important; }
  .mp-promo-overlay{ padding:16px!important; }
  .mp-promo-title{ font-size:16px!important; }
  .mp-hero-btns{ gap:8px!important; margin-top:16px!important; }
  .mp-hero-btn-secondary{ padding:10px 20px!important; font-size:13px!important; }
  .mp-hero-trust{ gap:10px!important; margin-top:16px!important; }
}
</style>';
?>

<?php foreach($homepage_sections as $key => $section){ if(!$section->is_enabled) continue; $baseKey = preg_replace('/_\d+$/', '', $key); switch($baseKey){
  case 'hero_banner': include(APPPATH.'views/themes/shared/sections/hero.php'); break;
  case 'promo_banner': include(APPPATH.'views/themes/shared/sections/promo.php'); break;
  case 'featured_categories': include(APPPATH.'views/themes/shared/sections/featured_categories.php'); break;
  case 'featured_products': include(APPPATH.'views/themes/shared/sections/featured_products.php'); break;
  case 'featured_services': include(APPPATH.'views/themes/shared/sections/featured_services.php'); break;
  case 'best_sellers': include(APPPATH.'views/themes/shared/sections/best_sellers.php'); break;
  case 'new_arrivals': include(APPPATH.'views/themes/shared/sections/new_arrivals.php'); break;
  case 'store_info': include(APPPATH.'views/themes/shared/sections/store_info.php'); break;
  case 'contact_section': include(APPPATH.'views/themes/shared/sections/contact.php'); break;
  case 'whatsapp_cta': include(APPPATH.'views/themes/shared/sections/whatsapp_cta.php'); break;
  case 'newsletter': include(APPPATH.'views/themes/shared/sections/newsletter.php'); break;
  case 'store_hours': include(APPPATH.'views/themes/shared/sections/store_hours.php'); break;
  case 'brands': include(APPPATH.'views/themes/shared/sections/brands.php'); break;
  case 'testimonials': include(APPPATH.'views/themes/shared/sections/testimonials.php'); break;
  case 'instagram_gallery': include(APPPATH.'views/themes/shared/sections/instagram.php'); break;
  case 'faqs': include(APPPATH.'views/themes/shared/sections/faqs.php'); break;
}} ?>

<!-- Beauty Tips Premium -->
<div class="bl-tips-section">
  <div class="bl-tips-header">
    <h2>Beauty Tips</h2>
    <p>Expert advice for your daily glow routine</p>
  </div>
  <div class="bl-tips-grid">
    <div class="bl-tip-card">
      <div class="bl-tip-badge">1</div>
      <div class="bl-tip-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>
      </div>
      <div class="bl-tip-title">Skincare Routine</div>
      <div class="bl-tip-text">Cleanse, tone, moisturize, and protect with SPF daily for glowing, healthy skin that radiates confidence.</div>
    </div>
    <div class="bl-tip-card">
      <div class="bl-tip-badge">2</div>
      <div class="bl-tip-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4.5 16.5c-1.5 1.26-2 5-2 5s3.74-.5 5-2c.71-.84.7-2.13-.09-2.91a2.18 2.18 0 0 0-2.91-.09z"></path><path d="m12 15-3-3a22 22 0 0 1 2-3.95A12.88 12.88 0 0 1 22 2c0 2.72-.78 7.5-6 11a22.35 22.35 0 0 1-4 2z"></path><path d="M9 12H4s.55-3.03 2-4c1.62-1.08 5 0 5 0"></path><path d="M12 15v5s3.03-.55 4-2c1.08-1.62 0-5 0-5"></path></svg>
      </div>
      <div class="bl-tip-title">Hair Care</div>
      <div class="bl-tip-text">Deep condition weekly, avoid excessive heat styling, and trim ends regularly to maintain strong, luscious hair.</div>
    </div>
    <div class="bl-tip-card">
      <div class="bl-tip-badge">3</div>
      <div class="bl-tip-icon">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"></path></svg>
      </div>
      <div class="bl-tip-title">Fragrance Guide</div>
      <div class="bl-tip-text">Apply perfume to pulse points like wrists and neck for a longer-lasting scent that evolves beautifully throughout the day.</div>
    </div>
  </div>
</div>
