<?php
foreach($homepage_sections as $key => $section){
  if(!$section->is_enabled) continue;
  switch($key){
    case 'hero_banner': include(APPPATH.'views/themes/shared/sections/hero.php'); break;
    case 'trust_badges': include(APPPATH.'views/themes/shared/sections/trust_badges.php'); break;
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
  }
}
?>

<style>
/* Urban Fashion — luxury polish over shared sections */
.theme-urban_fashion .mp-card { border-radius:16px; overflow:hidden; box-shadow:0 1px 3px rgba(0,0,0,.05); transition:transform .25s ease, box-shadow .25s ease; }
.theme-urban_fashion .mp-card:hover { transform:translateY(-5px); box-shadow:0 18px 44px rgba(0,0,0,.13); }
.theme-urban_fashion .mp-hero-btn, .theme-urban_fashion .mp-hero-btn-secondary,
.theme-urban_fashion .mp-card-add, .theme-urban_fashion .mp-service-btn,
.theme-urban_fashion .mp-sticky-cart-btn, .theme-urban_fashion .mp-cart-checkout,
.theme-urban_fashion .mp-modal-add { border-radius:12px; min-height:46px; }
.theme-urban_fashion .mp-hero-img { border-radius:0; }
.theme-urban_fashion .mp-section-title { letter-spacing:-0.01em; }
@media(max-width:767px){
  .theme-urban_fashion .mp-hero-img { height:340px; }
  .theme-urban_fashion .mp-hero-title { font-size:clamp(28px,8.5vw,40px); }
  .theme-urban_fashion .mp-hero-subtitle { font-size:15px; }
  .theme-urban_fashion .mp-hero-btns { flex-direction:column; width:100%; }
  .theme-urban_fashion .mp-hero-btns a { width:100%; text-align:center; box-sizing:border-box; }
  .theme-urban_fashion .mp-section { padding:40px 16px; }
}
</style>
