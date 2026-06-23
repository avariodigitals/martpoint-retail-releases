<?php
$theme_css_extra = '<style>
.mp-hero-overlay{background:linear-gradient(to right,rgba(10,37,64,0.9),rgba(10,37,64,0.3))!important;}
.mp-hero-btn{background:#635BFF!important;}
.mp-card:hover{box-shadow:0 12px 32px rgba(99,91,255,0.15)!important;}
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

<!-- Tech Specs Promo -->
<div class="mp-section" style="background:#0A2540; color:#fff; border-radius:var(--mp-radius);">
  <div style="display:grid; grid-template-columns:repeat(2, 1fr); gap:24px; text-align:center;">
    <div>
      <div style="font-size:32px; font-weight:800; color:#635BFF;">12</div>
      <div style="font-size:13px; opacity:0.8;">Months Warranty</div>
    </div>
    <div>
      <div style="font-size:32px; font-weight:800; color:#635BFF;">24h</div>
      <div style="font-size:13px; opacity:0.8;">Delivery</div>
    </div>
    <div>
      <div style="font-size:32px; font-weight:800; color:#635BFF;">100%</div>
      <div style="font-size:13px; opacity:0.8;">Genuine Products</div>
    </div>
    <div>
      <div style="font-size:32px; font-weight:800; color:#635BFF;">7-Day</div>
      <div style="font-size:13px; opacity:0.8;">Return Policy</div>
    </div>
  </div>
</div>
