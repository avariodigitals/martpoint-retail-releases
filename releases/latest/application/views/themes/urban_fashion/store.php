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
