<?php
$theme_css_extra = '<style>
.mp-hero-overlay{background:linear-gradient(to right,rgba(46,125,50,0.85),rgba(46,125,50,0.2))!important;}
.mp-hero-btn{background:#FF6F00!important;}
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

<!-- Weekly Deals Banner -->
<div class="mp-section">
  <div style="background:linear-gradient(135deg,#FF6F00,#FFB300); border-radius:var(--mp-radius); padding:32px 24px; color:#fff; text-align:center;">
    <h3 style="font-size:24px; font-weight:800; margin-bottom:8px;">Weekly Deals</h3>
    <p style="opacity:0.95; margin-bottom:16px;">Fresh prices on groceries and household essentials.</p>
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>" style="display:inline-block; padding:12px 28px; background:#fff; color:#FF6F00; font-weight:700; border-radius:var(--mp-radius-sm);">Shop Deals</a>
  </div>
</div>

<!-- Fast Reorder -->
<div class="mp-section" style="padding-top:0;">
  <div class="mp-section-title">Popular Picks</div>
  <div class="mp-categories">
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>" class="mp-chip active">All</a>
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>" class="mp-chip">Groceries</a>
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>" class="mp-chip">Drinks</a>
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>" class="mp-chip">Frozen</a>
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>" class="mp-chip">Household</a>
  </div>
</div>
