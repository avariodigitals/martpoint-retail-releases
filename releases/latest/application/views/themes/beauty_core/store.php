<?php
/* Beauty Core — shared storefront engine for the premium beauty / makeup-studio
 * theme family. A skin file sets $BEAUTY_SKIN ('atelier'|'velvet'|'blanc') and
 * includes this file; every skin shares the markup below and differs only by
 * palette, typography and surface treatment. */

$BEAUTY_SKIN = isset($BEAUTY_SKIN) ? $BEAUTY_SKIN : 'atelier';

$bc_skins = array(
    'atelier' => array(
        'display_font'    => '"Cormorant Garamond","Playfair Display",serif',
        'body_font'       => '"Jost","Inter",sans-serif',
        'page_bg'         => 'linear-gradient(180deg,#F6EDE6 0%,#FAF6F2 340px)',
        'ink'             => '#241B18',
        'heading'         => '#2B211E',
        'muted'           => '#7A655B',
        'accent'          => '#B76E79',
        'accent_2'        => '#C9A227',
        'accent_deep'     => '#7C4450',
        'hero_overlay'    => 'linear-gradient(100deg,rgba(30,21,18,0.74) 0%,rgba(124,68,80,0.38) 52%,rgba(255,255,255,0) 100%)',
        'hero_text'       => '#FBF3EC',
        'btn_bg'          => 'linear-gradient(135deg,#2B211E,#53373B)',
        'btn_text'        => '#F5E4D4',
        'chip_border'     => '#E3D2C4',
        'card_radius'     => '10px',
        'btn_radius'      => '3px',
        'card_shadow'     => '0 2px 14px rgba(43,33,30,0.06)',
        'card_shadow_hover'=> '0 22px 44px rgba(124,68,80,0.16)',
        'title_line'      => 'linear-gradient(90deg,#B76E79,#C9A227)',
        'newsletter_bg'   => 'linear-gradient(135deg,#241B18,#7C4450)',
        'footer_bg'       => 'linear-gradient(180deg,#241B18,#120D0B)',
        'panel_bg'        => 'rgba(255,255,255,0.78)',
        'panel_border'    => 'rgba(227,210,196,0.8)',
        'edit_bg'         => '#FFFFFF',
        'edit_border'     => '#EADFD3',
        'edit_icon_bg'    => 'linear-gradient(135deg,#F6EDE6,#EDDCCB)',
        'edit_icon_color' => '#B76E79',
        'edit_badge_bg'   => 'linear-gradient(135deg,#B76E79,#C9A227)',
        'title_transform' => 'none',
        'title_spacing'   => '0',
        'title_weight'    => '600',
    ),
    'velvet' => array(
        'display_font'    => '"Playfair Display",serif',
        'body_font'       => '"Poppins","Inter",sans-serif',
        'page_bg'         => 'linear-gradient(180deg,#FBECEF 0%,#FDF6F1 340px)',
        'ink'             => '#3E1E2E',
        'heading'         => '#5C2340',
        'muted'           => '#94657A',
        'accent'          => '#8E3B5E',
        'accent_2'        => '#E9B8C4',
        'accent_deep'     => '#5C2340',
        'hero_overlay'    => 'linear-gradient(105deg,rgba(92,35,64,0.62) 0%,rgba(233,184,196,0.28) 55%,rgba(255,255,255,0) 100%)',
        'hero_text'       => '#FFF3F6',
        'btn_bg'          => 'linear-gradient(135deg,#8E3B5E,#C2668B)',
        'btn_text'        => '#FFF3F6',
        'chip_border'     => '#EFC6D3',
        'card_radius'     => '24px',
        'btn_radius'      => '50px',
        'card_shadow'     => '0 6px 24px rgba(142,59,94,0.07)',
        'card_shadow_hover'=> '0 24px 48px rgba(142,59,94,0.18)',
        'title_line'      => 'linear-gradient(90deg,#8E3B5E,#E9B8C4)',
        'newsletter_bg'   => 'linear-gradient(135deg,#5C2340,#A85377)',
        'footer_bg'       => 'linear-gradient(180deg,#3E1E2E,#250F1B)',
        'panel_bg'        => 'rgba(255,255,255,0.75)',
        'panel_border'    => 'rgba(239,198,211,0.7)',
        'edit_bg'         => '#FFF9FB',
        'edit_border'     => '#F2D6E0',
        'edit_icon_bg'    => 'linear-gradient(135deg,#FBECEF,#F2D6E0)',
        'edit_icon_color' => '#8E3B5E',
        'edit_badge_bg'   => 'linear-gradient(135deg,#8E3B5E,#C2668B)',
        'title_transform' => 'none',
        'title_spacing'   => '-0.3px',
        'title_weight'    => '700',
    ),
    'blanc' => array(
        'display_font'    => '"Jost","Inter",sans-serif',
        'body_font'       => '"Jost","Inter",sans-serif',
        'page_bg'         => 'linear-gradient(180deg,#F7F3EE 0%,#FFFFFF 300px)',
        'ink'             => '#161412',
        'heading'         => '#161412',
        'muted'           => '#6E675F',
        'accent'          => '#C98A6B',
        'accent_2'        => '#161412',
        'accent_deep'     => '#161412',
        'hero_overlay'    => 'linear-gradient(100deg,rgba(22,20,18,0.60) 0%,rgba(22,20,18,0.20) 55%,rgba(255,255,255,0) 100%)',
        'hero_text'       => '#FFFFFF',
        'btn_bg'          => '#161412',
        'btn_text'        => '#FFFFFF',
        'chip_border'     => '#E4DED6',
        'card_radius'     => '4px',
        'btn_radius'      => '4px',
        'card_shadow'     => '0 1px 3px rgba(22,20,18,0.05)',
        'card_shadow_hover'=> '0 18px 40px rgba(22,20,18,0.12)',
        'title_line'      => 'linear-gradient(90deg,#161412,#C98A6B)',
        'newsletter_bg'   => '#161412',
        'footer_bg'       => '#161412',
        'panel_bg'        => '#FFFFFF',
        'panel_border'    => '#E4DED6',
        'edit_bg'         => '#F7F3EE',
        'edit_border'     => '#E4DED6',
        'edit_icon_bg'    => '#161412',
        'edit_icon_color' => '#C98A6B',
        'edit_badge_bg'   => '#C98A6B',
        'title_transform' => 'uppercase',
        'title_spacing'   => '2px',
        'title_weight'    => '500',
    ),
);

$bc = isset($bc_skins[$BEAUTY_SKIN]) ? $bc_skins[$BEAUTY_SKIN] : $bc_skins['atelier'];

$bc_edits = array(
    'atelier' => array(
        'title' => 'The Atelier Standard',
        'sub'   => 'Artistry, hygiene and detail in every appointment',
        'items' => array(
            array('t' => 'Master Artists',    'd' => 'Every booking is handled by a certified artist — consult, prep and finish in one seamless session.'),
            array('t' => 'Premium Kit Only',  'd' => 'We work exclusively with pro-grade, skin-safe products matched to your tone and skin type.'),
            array('t' => 'Signature Finish',  'd' => 'From soft glam to full editorial — looks are designed to photograph beautifully and last all day.'),
        ),
    ),
    'velvet' => array(
        'title' => 'Glow Rituals',
        'sub'   => 'Little luxuries that make every visit feel special',
        'items' => array(
            array('t' => 'Skin-First Prep',   'd' => 'Every look begins with gentle prep — cleanse, prime and protect for a flawless, lasting base.'),
            array('t' => 'Day-to-Night Glam', 'd' => 'Soft daytime polish that transitions effortlessly into evening drama with a few pro touches.'),
            array('t' => 'Bridal Glow',       'd' => 'Trials, touch-up kits and on-location artistry so your morning is as calm as it is beautiful.'),
        ),
    ),
    'blanc' => array(
        'title' => 'The Studio Edit',
        'sub'   => 'Curated essentials, honest advice, zero clutter',
        'items' => array(
            array('t' => 'Curated Kit',       'd' => 'A tightly edited shelf — every product is tested by our artists before it earns a place.'),
            array('t' => 'Shade Matching',    'd' => 'In-studio shade matching across skin tones so you buy once and buy right.'),
            array('t' => 'Book in Seconds',   'd' => 'Pick a service, choose a time, done. Appointments confirmed instantly online.'),
        ),
    ),
);

$bc_edit = isset($bc_edits[$BEAUTY_SKIN]) ? $bc_edits[$BEAUTY_SKIN] : $bc_edits['atelier'];
?>

<style>
/* === Beauty Core [<?= $BEAUTY_SKIN; ?>] === */
body{ font-family:<?= $bc['body_font']; ?>; background:<?= $bc['page_bg']; ?>; color:<?= $bc['ink']; ?>; }

/* Hero */
.mp-hero-overlay{ background:<?= $bc['hero_overlay']; ?>!important; }
.mp-hero-title{ font-family:<?= $bc['display_font']; ?>!important; font-weight:<?= $bc['title_weight']; ?>!important; letter-spacing:<?= $bc['title_spacing']; ?>!important; }
.mp-hero-subtitle{ font-family:<?= $bc['body_font']; ?>!important; }
.mp-hero-btn{ background:<?= $bc['btn_bg']; ?>!important; color:<?= $bc['btn_text']; ?>!important; border-radius:<?= $bc['btn_radius']; ?>!important; padding:14px 38px!important; font-family:<?= $bc['body_font']; ?>!important; letter-spacing:0.6px!important; box-shadow:0 10px 26px rgba(0,0,0,0.22)!important; transition:all .3s ease!important; }
.mp-hero-btn:hover{ transform:translateY(-2px)!important; box-shadow:0 16px 34px rgba(0,0,0,0.28)!important; }
.mp-hero-btn-secondary{ border-radius:<?= $bc['btn_radius']; ?>!important; }

/* Section titles */
.mp-section-title{ font-family:<?= $bc['display_font']; ?>!important; font-size:28px!important; font-weight:<?= $bc['title_weight']; ?>!important; color:<?= $bc['heading']; ?>!important; letter-spacing:<?= $bc['title_spacing']; ?>!important; text-transform:<?= $bc['title_transform']; ?>!important; }
.mp-section-title::after{ content:""; display:block; width:52px; height:2px; background:<?= $bc['title_line']; ?>; margin-top:10px; }

/* Product / service cards */
.mp-card{ border-radius:<?= $bc['card_radius']; ?>!important; border:1px solid <?= $bc['edit_border']; ?>!important; box-shadow:<?= $bc['card_shadow']; ?>!important; overflow:hidden!important; transition:transform .35s cubic-bezier(.22,1,.36,1),box-shadow .35s ease!important; }
.mp-card:hover{ transform:translateY(-6px)!important; box-shadow:<?= $bc['card_shadow_hover']; ?>!important; }
.mp-card-img{ height:220px!important; transition:transform .5s ease!important; }
.mp-card:hover .mp-card-img{ transform:scale(1.05)!important; }
.mp-card-body{ padding:16px!important; }
.mp-card-name{ font-family:<?= $bc['body_font']; ?>!important; font-weight:600!important; color:<?= $bc['heading']; ?>!important; }
.mp-card-price{ color:<?= $bc['accent']; ?>!important; font-size:17px!important; font-weight:700!important; }
.mp-card-add{ border-radius:<?= $bc['btn_radius']; ?>!important; background:<?= $bc['accent']; ?>!important; transition:all .2s ease!important; }
.mp-card-add:hover{ filter:brightness(1.08)!important; }

/* Category chips */
.mp-chip{ border-radius:<?= $bc['btn_radius']; ?>!important; border:1.5px solid <?= $bc['chip_border']; ?>!important; background:#fff!important; font-family:<?= $bc['body_font']; ?>!important; letter-spacing:0.3px!important; transition:all .2s ease!important; }
.mp-chip:hover,.mp-chip.active{ background:<?= $bc['accent']; ?>!important; color:#fff!important; border-color:transparent!important; box-shadow:0 4px 14px <?= $bc['card_shadow_hover']; ?>!important; }

/* Promo, trust, testimonials, contact */
.mp-promo-card{ border-radius:<?= $bc['card_radius']; ?>!important; box-shadow:<?= $bc['card_shadow']; ?>!important; }
.mp-trust-item{ background:<?= $bc['panel_bg']; ?>!important; backdrop-filter:blur(8px)!important; border:1px solid <?= $bc['panel_border']; ?>!important; border-radius:<?= $bc['card_radius']; ?>!important; }
.mp-testi-card{ background:<?= $bc['panel_bg']; ?>!important; backdrop-filter:blur(10px)!important; border:1px solid <?= $bc['panel_border']; ?>!important; border-radius:<?= $bc['card_radius']; ?>!important; box-shadow:<?= $bc['card_shadow']; ?>!important; }
.mp-contact-card{ background:<?= $bc['panel_bg']; ?>!important; backdrop-filter:blur(8px)!important; border-radius:<?= $bc['card_radius']; ?>!important; border:1px solid <?= $bc['panel_border']; ?>!important; }

/* Newsletter + footer */
.mp-newsletter{ background:<?= $bc['newsletter_bg']; ?>!important; border-radius:<?= $bc['card_radius']; ?>!important; }
.mp-newsletter h3{ font-family:<?= $bc['display_font']; ?>!important; }
.mp-footer{ background:<?= $bc['footer_bg']; ?>!important; }

/* Signature edit section */
.bc-edit{ max-width:1280px; margin:0 auto; padding:64px 24px; }
.bc-edit-head{ text-align:center; margin-bottom:44px; }
.bc-edit-head h2{ font-family:<?= $bc['display_font']; ?>; font-size:32px; font-weight:<?= $bc['title_weight']; ?>; color:<?= $bc['heading']; ?>; margin-bottom:8px; letter-spacing:<?= $bc['title_spacing']; ?>; text-transform:<?= $bc['title_transform']; ?>; }
.bc-edit-head p{ color:<?= $bc['muted']; ?>; font-size:15px; font-family:<?= $bc['body_font']; ?>; }
.bc-edit-grid{ display:grid; grid-template-columns:repeat(1,1fr); gap:20px; }
@media(min-width:640px){ .bc-edit-grid{ grid-template-columns:repeat(3,1fr); } }
.bc-edit-card{ position:relative; background:<?= $bc['edit_bg']; ?>; border:1px solid <?= $bc['edit_border']; ?>; border-radius:<?= $bc['card_radius']; ?>; padding:30px 28px; transition:all .35s cubic-bezier(.22,1,.36,1); }
.bc-edit-card:hover{ transform:translateY(-6px); box-shadow:<?= $bc['card_shadow_hover']; ?>; }
.bc-edit-icon{ width:52px; height:52px; border-radius:<?= $bc['card_radius']; ?>; background:<?= $bc['edit_icon_bg']; ?>; display:flex; align-items:center; justify-content:center; margin-bottom:18px; color:<?= $bc['edit_icon_color']; ?>; }
.bc-edit-icon svg{ width:26px; height:26px; stroke-width:1.7; }
.bc-edit-title{ font-family:<?= $bc['display_font']; ?>; font-size:19px; font-weight:600; color:<?= $bc['heading']; ?>; margin-bottom:10px; letter-spacing:<?= $bc['title_spacing']; ?>; }
.bc-edit-text{ font-size:14px; line-height:1.7; color:<?= $bc['muted']; ?>; font-family:<?= $bc['body_font']; ?>; }
.bc-edit-num{ position:absolute; top:20px; right:22px; font-family:<?= $bc['display_font']; ?>; font-size:15px; font-weight:700; color:<?= $bc['accent']; ?>; opacity:.8; }

/* Mobile */
@media(max-width:767px){
  .mp-hero-title{ font-size:28px!important; line-height:1.2!important; }
  .mp-hero-subtitle{ font-size:15px!important; }
  .mp-hero-btn{ padding:12px 24px!important; font-size:14px!important; }
  .mp-section-title{ font-size:22px!important; }
  .mp-card{ border-radius:<?= $bc['card_radius']; ?>!important; }
  .mp-card-img{ height:160px!important; }
  .mp-section{ padding:32px 16px!important; }
  .bc-edit{ padding:40px 16px!important; }
  .bc-edit-head h2{ font-size:24px!important; }
  .bc-edit-card{ padding:22px 18px!important; }
  .mp-newsletter{ padding:32px 16px!important; }
  .mp-newsletter h3{ font-size:20px!important; }
  .mp-trust-item{ padding:14px 12px!important; }
  .mp-testi-card{ padding:14px!important; }
  .mp-contact-card{ padding:14px!important; }
  .mp-promo-grid{ grid-template-columns:1fr!important; gap:12px!important; }
  .mp-promo-overlay{ padding:16px!important; }
  .mp-promo-title{ font-size:16px!important; }
  .mp-hero-btns{ gap:8px!important; margin-top:16px!important; }
  .mp-hero-btn-secondary{ padding:10px 20px!important; font-size:13px!important; }
  .mp-hero-trust{ gap:10px!important; margin-top:16px!important; }
}
</style>

<?php foreach($homepage_sections as $key => $section){ if(!$section->is_enabled) continue; $baseKey = preg_replace('/_\d+$/', '', $key); switch($baseKey){
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
}} ?>

<!-- Signature edit section -->
<div class="bc-edit">
  <div class="bc-edit-head">
    <h2><?= htmlspecialchars($bc_edit['title']); ?></h2>
    <p><?= htmlspecialchars($bc_edit['sub']); ?></p>
  </div>
  <div class="bc-edit-grid">
    <?php $bc_icons = array(
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3l1.9 5.1L19 10l-5.1 1.9L12 17l-1.9-5.1L5 10l5.1-1.9z"/><path d="M19 15l.9 2.1L22 18l-2.1.9L19 21l-.9-2.1L16 18l2.1-.9z"/></svg>',
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2.69l5.66 5.66a8 8 0 1 1-11.31 0z"/></svg>',
      '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>',
    ); ?>
    <?php foreach($bc_edit['items'] as $bc_i => $bc_item): ?>
    <div class="bc-edit-card">
      <div class="bc-edit-num">0<?= $bc_i + 1; ?></div>
      <div class="bc-edit-icon"><?= $bc_icons[$bc_i % count($bc_icons)]; ?></div>
      <div class="bc-edit-title"><?= htmlspecialchars($bc_item['t']); ?></div>
      <div class="bc-edit-text"><?= htmlspecialchars($bc_item['d']); ?></div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
