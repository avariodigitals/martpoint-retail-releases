<?php
$theme_css_extra = '<style>
.mp-hero-overlay{background:linear-gradient(to right,rgba(26,35,126,0.9),rgba(26,35,126,0.3))!important;}
.mp-hero-btn{background:#00BCD4!important;}
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

<!-- How It Works -->
<div class="mp-section">
  <div class="mp-section-title">How It Works</div>
  <div style="display:grid; grid-template-columns:repeat(1, 1fr); gap:16px;">
    <div style="text-align:center; padding:24px; background:var(--mp-white); border-radius:var(--mp-radius-sm); border:1px solid var(--mp-border);">
      <div style="width:48px; height:48px; border-radius:50%; background:var(--mp-primary); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; margin:0 auto 12px;">1</div>
      <div style="font-weight:700; margin-bottom:4px;">Choose a Service</div>
      <div style="font-size:13px; color:var(--mp-gray);">Browse and select the service you need.</div>
    </div>
    <div style="text-align:center; padding:24px; background:var(--mp-white); border-radius:var(--mp-radius-sm); border:1px solid var(--mp-border);">
      <div style="width:48px; height:48px; border-radius:50%; background:var(--mp-primary); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; margin:0 auto 12px;">2</div>
      <div style="font-weight:700; margin-bottom:4px;">Book Appointment</div>
      <div style="font-size:13px; color:var(--mp-gray);">Pick a convenient date and time.</div>
    </div>
    <div style="text-align:center; padding:24px; background:var(--mp-white); border-radius:var(--mp-radius-sm); border:1px solid var(--mp-border);">
      <div style="width:48px; height:48px; border-radius:50%; background:var(--mp-primary); color:#fff; display:flex; align-items:center; justify-content:center; font-weight:700; margin:0 auto 12px;">3</div>
      <div style="font-weight:700; margin-bottom:4px;">Get It Done</div>
      <div style="font-size:13px; color:var(--mp-gray);">We deliver quality service to your door.</div>
    </div>
  </div>
</div>

<!-- Request Quote CTA -->
<div class="mp-section" style="text-align:center;">
  <div style="background:linear-gradient(135deg,#1A237E,#00BCD4); border-radius:var(--mp-radius); padding:40px 24px; color:#fff;">
    <h3 style="font-size:24px; font-weight:700; margin-bottom:8px;">Need a Custom Quote?</h3>
    <p style="opacity:0.9; margin-bottom:20px;">Tell us what you need and we will get back to you.</p>
    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? ''); ?>?text=I%20would%20like%20a%20quote" target="_blank" class="mp-hero-btn" style="background:#fff!important; color:#1A237E!important;">Request Quote</a>
  </div>
</div>
