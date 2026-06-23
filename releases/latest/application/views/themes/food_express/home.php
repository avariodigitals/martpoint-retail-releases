<?php
$theme_css_extra = '<style>
.mp-hero-overlay{background:linear-gradient(to right,rgba(211,47,47,0.85),rgba(211,47,47,0.2))!important;}
.mp-hero-btn{background:#FBC02D!important; color:#111!important;}
.mp-card{border-radius:16px!important; overflow:hidden;}
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

<!-- Chef Specials -->
<div class="mp-section">
  <div style="background:#FFF8E1; border-radius:var(--mp-radius); padding:32px 24px; text-align:center; border:2px dashed #FBC02D;">
    <div style="font-size:40px; margin-bottom:8px;">&#127858;</div>
    <h3 style="font-size:22px; font-weight:800; margin-bottom:8px;">Chef Specials</h3>
    <p style="color:var(--mp-gray); margin-bottom:16px;">Hand-picked meals prepared fresh daily.</p>
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>" class="mp-hero-btn">Order Now</a>
  </div>
</div>

<!-- Delivery Info -->
<div class="mp-section" style="padding-top:0;">
  <div style="display:grid; grid-template-columns:repeat(2, 1fr); gap:12px; text-align:center;">
    <div style="background:var(--mp-white); padding:20px; border-radius:var(--mp-radius-sm); border:1px solid var(--mp-border);">
      <div style="font-size:28px; margin-bottom:4px;">&#128640;</div>
      <div style="font-weight:700;">Fast Delivery</div>
    </div>
    <div style="background:var(--mp-white); padding:20px; border-radius:var(--mp-radius-sm); border:1px solid var(--mp-border);">
      <div style="font-size:28px; margin-bottom:4px;">&#128247;</div>
      <div style="font-weight:700;">Table QR Order</div>
    </div>
  </div>
</div>
