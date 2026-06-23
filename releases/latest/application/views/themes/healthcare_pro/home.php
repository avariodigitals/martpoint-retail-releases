<?php
$theme_css_extra = '<style>
.mp-hero-overlay{background:linear-gradient(to right,rgba(0,94,184,0.85),rgba(0,94,184,0.3))!important;}
.mp-hero-btn{background:#00A86B!important;}
.mp-trust-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:12px;}
.mp-trust-item{display:flex;align-items:center;gap:10px;padding:14px;background:#fff;border-radius:var(--mp-radius-sm);border:1px solid var(--mp-border);}
@media(min-width:768px){.mp-trust-grid{grid-template-columns:repeat(4,1fr);}}
</style>';
?>

<?php foreach($homepage_sections as $key => $section){ if(!$section->is_enabled) continue; $baseKey = preg_replace('/_\d+$/', '', $key); switch($baseKey){
  case 'hero_banner': include(APPPATH.'views/themes/shared/sections/hero.php'); break;
  case 'featured_categories': include(APPPATH.'views/themes/shared/sections/featured_categories.php'); break;
  case 'promo_banner': include(APPPATH.'views/themes/shared/sections/promo.php'); break;
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

<!-- Healthcare Trust Badges -->
<div class="mp-section" style="padding-top:24px;">
  <div class="mp-trust-grid">
    <div class="mp-trust-item"><span>&#128657;</span><div>Licensed Pharmacy</div></div>
    <div class="mp-trust-item"><span>&#128138;</span><div>Authentic Products</div></div>
    <div class="mp-trust-item"><span>&#128230;</span><div>Fast Delivery</div></div>
    <div class="mp-trust-item"><span>&#128104;&#8205;&#128657;</span><div>Expert Support</div></div>
  </div>
</div>

<!-- Prescription Upload CTA -->
<div class="mp-section" style="text-align:center;">
  <div style="background:linear-gradient(135deg,#005EB8,#00A86B); border-radius:var(--mp-radius); padding:40px 24px; color:#fff;">
    <h3 style="font-size:24px; font-weight:700; margin-bottom:8px;">Have a Prescription?</h3>
    <p style="opacity:0.9; margin-bottom:20px;">Upload your prescription and we will prepare your medicines.</p>
    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? ''); ?>?text=I%20have%20a%20prescription%20to%20upload" target="_blank" class="mp-hero-btn" style="background:#fff!important; color:#005EB8!important;">Upload via WhatsApp</a>
  </div>
</div>
