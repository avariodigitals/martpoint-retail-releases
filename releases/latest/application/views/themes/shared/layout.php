<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
<?php
  $seoTitle = $seo_title ?? ($settings->meta_title ?? ($store->store_name ?? 'Store'));
  $seoDesc = $seo_description ?? ($settings->meta_description ?? ($settings->store_description ?? 'Browse our online store'));
  $seoImage = $seo_image ?? ($logo_url ?? base_url('uploads/site/icon.webp'));
  $seoUrl = $seo_canonical ?? current_url();
  $seoType = $seo_type ?? 'website';
  $storeName = $store->store_name ?? 'Online Store';
?>
  <title><?= htmlspecialchars($seoTitle); ?> | <?= htmlspecialchars($storeName); ?></title>
  <meta name="description" content="<?= htmlspecialchars($seoDesc); ?>">
<?php if(!empty($settings->meta_keywords)): ?>
  <meta name="keywords" content="<?= htmlspecialchars($settings->meta_keywords); ?>">
<?php endif; ?>
  <meta name="robots" content="<?= ($settings->robots_index ?? '1') == '1' ? 'index, follow' : 'noindex, nofollow'; ?>">
  <link rel="canonical" href="<?= $seoUrl; ?>">
  <link rel="shortcut icon" href="<?= $favicon_url ?? base_url('uploads/site/icon.webp'); ?>">
  <link rel="manifest" href="<?= base_url('manifest.json'); ?>">
  <meta name="theme-color" content="<?= htmlspecialchars($settings->theme_color ?? '#0B1120'); ?>">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="<?= htmlspecialchars($storeName); ?>">
  <link rel="apple-touch-icon" href="<?= $favicon_url ?? base_url('uploads/site/icon.webp'); ?>">
<?php if(!empty($settings->google_analytics_id)): ?>
  <!-- Google Analytics -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($settings->google_analytics_id); ?>"></script>
  <script>window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config','<?= htmlspecialchars($settings->google_analytics_id); ?>');</script>
<?php endif; ?>
<?php if(!empty($settings->facebook_pixel_id)): ?>
  <!-- Facebook Pixel -->
  <script>!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init','<?= htmlspecialchars($settings->facebook_pixel_id); ?>');fbq('track','PageView');</script>
  <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=<?= htmlspecialchars($settings->facebook_pixel_id); ?>&ev=PageView&noscript=1"/></noscript>
<?php endif; ?>
<?php if(!empty($settings->custom_head_scripts)): ?>
  <!-- Custom Head Scripts -->
  <?= $settings->custom_head_scripts; ?>
<?php endif; ?>

  <!-- Open Graph -->
  <meta property="og:type" content="<?= $seoType; ?>">
  <meta property="og:site_name" content="<?= htmlspecialchars($storeName); ?>">
  <meta property="og:title" content="<?= htmlspecialchars($seoTitle); ?>">
  <meta property="og:description" content="<?= htmlspecialchars($seoDesc); ?>">
  <meta property="og:url" content="<?= $seoUrl; ?>">
  <meta property="og:image" content="<?= $seoImage; ?>">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= htmlspecialchars($seoTitle); ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars($seoDesc); ?>">
  <meta name="twitter:image" content="<?= $seoImage; ?>">

  <link href="<?= $this->theme_engine->googleFontLink(); ?>" rel="stylesheet">
  <style>
    <?= $this->theme_engine->cssVariables(); ?>
    * { margin:0; padding:0; box-sizing:border-box; }
    html { scroll-behavior:smooth; }
    body { font-family:var(--mp-font); background:#F8FAFC; color:var(--mp-dark); -webkit-font-smoothing:antialiased; }
    a { text-decoration:none; color:inherit; }
    img { max-width:100%; display:block; }
    button { font-family:var(--mp-font); }

    /* Announcement Bar */
    .mp-announcement { background:var(--mp-dark); color:#fff; text-align:center; padding:8px 16px; font-size:13px; font-weight:500; }

    /* Top Header */
    .mp-header { background:var(--mp-white); border-bottom:1px solid var(--mp-border); position:sticky; top:0; z-index:1000; }
    .mp-header-inner { max-width:1280px; margin:0 auto; padding:12px 24px; display:flex; align-items:center; justify-content:space-between; gap:16px; }
    .mp-logo { font-weight:800; font-size:18px; color:var(--mp-primary); display:flex; align-items:center; gap:8px; }
    .mp-logo img { height:36px; width:auto; object-fit:contain; }
    .mp-header-actions { display:flex; align-items:center; gap:12px; }
    .mp-search-bar { flex:1; max-width:480px; position:relative; }
    .mp-search-bar input { width:100%; padding:10px 14px 10px 40px; border:1px solid var(--mp-border); border-radius:var(--mp-radius-sm); font-size:14px; background:var(--mp-light-gray); outline:none; }
    .mp-search-bar input:focus { border-color:var(--mp-primary); }
    .mp-search-icon { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:var(--mp-gray); font-size:15px; }
    .mp-cart-count { position:absolute; top:-4px; right:-4px; background:var(--mp-danger); color:#fff; font-size:11px; font-weight:700; width:20px; height:20px; border-radius:50%; display:flex; align-items:center; justify-content:center; }
    .mp-mobile-menu-btn { display:none; font-size:22px; background:none; border:none; cursor:pointer; color:var(--mp-dark); }

    /* Navigation */
    .mp-nav { background:var(--mp-white); border-bottom:1px solid var(--mp-border); }
    .mp-nav-inner { max-width:1280px; margin:0 auto; padding:0 24px; display:flex; gap:24px; overflow-x:auto; scrollbar-width:none; }
    .mp-nav-inner::-webkit-scrollbar { display:none; }
    .mp-nav-link { padding:12px 0; font-size:14px; font-weight:600; color:var(--mp-gray); white-space:nowrap; border-bottom:2px solid transparent; transition:all .2s; }
    .mp-nav-link:hover, .mp-nav-link.active { color:var(--mp-primary); border-bottom-color:var(--mp-primary); }

    /* Hero Banner */
    .mp-hero { position:relative; overflow:hidden; }
    .mp-hero-track { display:flex; transition:transform .6s ease; }
    .mp-hero-slide { position:relative; flex:0 0 100%; }
    .mp-hero-img { width:100%; height:380px; object-fit:cover; }
    .mp-hero-dots { position:absolute; bottom:16px; left:50%; transform:translateX(-50%); display:flex; gap:8px; z-index:10; }
    .mp-hero-dot { width:10px; height:10px; border-radius:50%; border:2px solid rgba(255,255,255,0.6); background:transparent; cursor:pointer; transition:all .3s; padding:0; }
    .mp-hero-dot.active { background:#fff; border-color:#fff; }
    .mp-hero-dot:hover { background:rgba(255,255,255,0.5); }
    .mp-hero-overlay { position:absolute; inset:0; background:linear-gradient(to right, rgba(0,0,0,0.6), rgba(0,0,0,0.1)); display:flex; align-items:center; }
    .mp-hero-content { max-width:1280px; margin:0 auto; padding:0 24px; width:100%; color:#fff; }
    .mp-hero-title { font-size:42px; font-weight:800; line-height:1.15; margin-bottom:12px; max-width:600px; }
    .mp-hero-subtitle { font-size:18px; font-weight:400; opacity:0.9; margin-bottom:24px; max-width:500px; }
    .mp-hero-btn { display:inline-block; padding:14px 32px; background:var(--mp-primary); color:#fff; font-weight:700; border-radius:var(--mp-radius); font-size:15px; border:none; cursor:pointer; }
    .mp-hero-btn:hover { background:var(--mp-primary-dark); }

    /* Section System */
    .mp-section { max-width:1280px; margin:0 auto; padding:48px 24px; }
    .mp-section-title { font-size:24px; font-weight:700; margin-bottom:24px; display:flex; align-items:center; justify-content:space-between; }
    .mp-section-title a { font-size:14px; color:var(--mp-primary); font-weight:600; }

    /* Product Grid */
    .mp-grid { display:grid; grid-template-columns:repeat(2, 1fr); gap:16px; }
    .mp-card { background:var(--mp-white); border-radius:var(--mp-radius-sm); overflow:hidden; border:1px solid var(--mp-border); transition:transform .15s ease, box-shadow .15s ease; }
    .mp-card:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,0,0,0.08); }
    .mp-card:active { transform:scale(.98); }
    .mp-card-img { width:100%; height:180px; object-fit:cover; background:var(--mp-light-gray); }
    .mp-card-body { padding:12px; }
    .mp-card-name { font-size:14px; font-weight:600; color:var(--mp-dark); line-height:1.3; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; margin-bottom:6px; }
    .mp-card-price { font-size:16px; font-weight:700; color:var(--mp-primary); }
    .mp-card-old { font-size:12px; color:var(--mp-gray); text-decoration:line-through; margin-left:4px; }
    .mp-card-stock { font-size:11px; color:var(--mp-danger); font-weight:600; margin-top:4px; }
    .mp-card-add { width:100%; margin-top:8px; padding:8px; border:none; border-radius:var(--mp-radius-sm); background:var(--mp-primary); color:#fff; font-size:13px; font-weight:600; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:4px; }
    .mp-card-add:disabled { background:#CBD5E1; cursor:not-allowed; }

    /* Service Card */
    .mp-service-card { background:var(--mp-white); border-radius:var(--mp-radius-sm); border:1px solid var(--mp-border); padding:12px; display:flex; gap:12px; align-items:center; }
    .mp-service-img { width:60px; height:60px; border-radius:8px; object-fit:cover; background:var(--mp-light-gray); flex-shrink:0; }
    .mp-service-info { flex:1; min-width:0; }
    .mp-service-name { font-size:14px; font-weight:600; margin-bottom:4px; }
    .mp-service-meta { font-size:12px; color:var(--mp-gray); }
    .mp-service-price { font-size:15px; font-weight:700; color:var(--mp-primary); }

    /* Categories */
    .mp-categories { display:flex; gap:10px; overflow-x:auto; scrollbar-width:none; padding-bottom:4px; }
    .mp-categories::-webkit-scrollbar { display:none; }
    .mp-chip { white-space:nowrap; padding:8px 16px; border-radius:20px; background:var(--mp-white); border:1px solid var(--mp-border); font-size:13px; font-weight:500; color:var(--mp-gray); cursor:pointer; transition:all .2s; }
    .mp-chip:hover, .mp-chip.active { background:var(--mp-primary); color:#fff; border-color:var(--mp-primary); }

    /* Category Grid (large desktop sections) */
    .mp-cat-grid { display:grid; grid-template-columns:repeat(2, 1fr); gap:12px; }
    .mp-cat-card { position:relative; border-radius:var(--mp-radius-sm); overflow:hidden; height:160px; }
    .mp-cat-card img { width:100%; height:100%; object-fit:cover; }
    .mp-cat-card-overlay { position:absolute; inset:0; background:rgba(0,0,0,0.4); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; font-size:18px; }

    /* Promo Banner */
    .mp-promo-section { background:var(--mp-light); }
    .mp-promo-grid { display:grid; grid-template-columns:repeat(1, 1fr); gap:20px; }
    .mp-promo-card { border-radius:var(--mp-radius); overflow:hidden; position:relative; display:block; aspect-ratio:16/9; }
    .mp-promo-card img { width:100%; height:100%; object-fit:cover; display:block; transition:transform .4s; }
    .mp-promo-card:hover img { transform:scale(1.05); }
    .mp-promo-overlay { position:absolute; inset:0; background:linear-gradient(to top, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.1) 60%); display:flex; flex-direction:column; justify-content:flex-end; padding:24px; color:#fff; }
    .mp-promo-title { font-size:20px; font-weight:700; margin-bottom:4px; }
    .mp-promo-subtitle { font-size:14px; opacity:0.9; margin-bottom:12px; }
    .mp-promo-btn { display:inline-flex; align-self:flex-start; padding:8px 18px; background:var(--mp-primary); color:#fff; font-size:13px; font-weight:600; border-radius:var(--mp-radius); }
    .mp-promo-btn:hover { background:var(--mp-primary-dark); }

    /* Sticky Mobile Bottom Nav */
    .mp-mobile-nav { display:none; position:fixed; bottom:0; left:0; right:0; background:var(--mp-white); border-top:1px solid var(--mp-border); z-index:300; padding:6px 0; }
    .mp-mobile-nav-inner { display:flex; justify-content:space-around; align-items:center; }
    .mp-mobile-nav-item { display:flex; flex-direction:column; align-items:center; gap:2px; font-size:11px; color:var(--mp-gray); padding:4px 8px; border:none; background:none; cursor:pointer; }
    .mp-mobile-nav-item.active { color:var(--mp-primary); }
    .mp-mobile-nav-item span { font-size:20px; }

    /* Sticky WhatsApp */
    .mp-sticky-wa { display:none; position:fixed; bottom:72px; right:16px; z-index:250; width:52px; height:52px; border-radius:50%; background:#25D366; color:#fff; font-size:24px; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 12px rgba(0,0,0,0.2); border:none; cursor:pointer; }

    /* Sticky Cart Bar */
    .mp-sticky-cart { position:fixed; bottom:0; left:0; right:0; background:var(--mp-white); border-top:1px solid var(--mp-border); padding:12px 16px; z-index:200; display:none; }
    .mp-sticky-cart.show { display:block; }
    .mp-sticky-cart-inner { max-width:1280px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; }
    .mp-sticky-cart-info { font-size:14px; }
    .mp-sticky-cart-info span { font-weight:700; }
    .mp-sticky-cart-btn { padding:10px 24px; border-radius:var(--mp-radius-sm); background:var(--mp-primary); color:#fff; font-weight:700; border:none; cursor:pointer; font-size:14px; }

    /* Footer */
    .mp-footer { background:var(--mp-dark); color:#CBD5E1; padding:48px 24px 24px; }
    .mp-footer-inner { max-width:1280px; margin:0 auto; display:grid; grid-template-columns:repeat(4, 1fr); gap:32px; }
    .mp-footer-brand { color:#fff; font-weight:800; font-size:18px; margin-bottom:12px; }
    .mp-footer-desc { font-size:14px; line-height:1.6; margin-bottom:16px; }
    .mp-footer-heading { color:#fff; font-weight:700; font-size:14px; margin-bottom:12px; text-transform:uppercase; letter-spacing:0.5px; }
    .mp-footer-links { list-style:none; }
    .mp-footer-links li { margin-bottom:8px; }
    .mp-footer-links a { font-size:14px; transition:color .2s; }
    .mp-footer-links a:hover { color:#fff; }
    .mp-footer-social { display:flex; gap:12px; margin-top:12px; }
    .mp-footer-social a { width:36px; height:36px; border-radius:50%; background:rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center; font-size:14px; transition:background .2s; }
    .mp-footer-social a:hover { background:rgba(255,255,255,0.2); }
    .mp-footer-bottom { max-width:1280px; margin:24px auto 0; padding-top:24px; border-top:1px solid rgba(255,255,255,0.1); text-align:center; font-size:13px; }
    .mp-footer-contact-item { display:flex; align-items:center; gap:8px; font-size:13px; margin-bottom:8px; }

    /* Toast */
    .mp-toast { position:fixed; top:16px; left:50%; transform:translateX(-50%) translateY(-80px); background:var(--mp-dark); color:#fff; padding:12px 20px; border-radius:var(--mp-radius-sm); font-size:14px; font-weight:500; z-index:1000; opacity:0; transition:all .3s ease; }
    .mp-toast.show { transform:translateX(-50%) translateY(0); opacity:1; }

    /* Empty */
    .mp-empty { text-align:center; padding:60px 16px; color:var(--mp-gray); }

    /* Modal */
    .mp-modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:900; display:none; align-items:flex-end; justify-content:center; }
    .mp-modal-overlay.show { display:flex; }
    .mp-modal { background:var(--mp-white); width:100%; max-width:600px; border-radius:var(--mp-radius) var(--mp-radius) 0 0; padding:20px; max-height:80vh; overflow-y:auto; }
    .mp-modal-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; }
    .mp-modal-title { font-size:18px; font-weight:700; }
    .mp-modal-close { font-size:24px; color:var(--mp-gray); background:none; border:none; cursor:pointer; }
    .mp-modal-img { width:100%; height:200px; object-fit:cover; border-radius:var(--mp-radius-sm); margin-bottom:16px; background:var(--mp-light-gray); }
    .mp-modal-price { font-size:22px; font-weight:700; color:var(--mp-primary); margin-bottom:8px; }
    .mp-modal-desc { font-size:14px; color:var(--mp-gray); line-height:1.5; margin-bottom:16px; }
    .mp-modal-qty { display:flex; align-items:center; gap:12px; margin-bottom:16px; }
    .mp-modal-qty button { width:36px; height:36px; border-radius:50%; border:1px solid var(--mp-border); background:var(--mp-white); font-size:18px; cursor:pointer; }
    .mp-modal-qty span { font-size:16px; font-weight:700; min-width:30px; text-align:center; }
    .mp-modal-add { width:100%; padding:14px; border-radius:var(--mp-radius-sm); background:var(--mp-primary); color:#fff; font-weight:700; border:none; cursor:pointer; font-size:16px; }

    /* Cart page */
    .mp-cart-item { background:var(--mp-white); border-radius:var(--mp-radius-sm); border:1px solid var(--mp-border); padding:12px; display:flex; gap:12px; margin-bottom:8px; }
    .mp-cart-item-img { width:60px; height:60px; border-radius:6px; object-fit:cover; background:var(--mp-light-gray); flex-shrink:0; }
    .mp-cart-item-info { flex:1; min-width:0; }
    .mp-cart-item-name { font-size:14px; font-weight:600; margin-bottom:4px; }
    .mp-cart-item-price { font-size:14px; font-weight:700; color:var(--mp-primary); }
    .mp-cart-item-qty { display:flex; align-items:center; gap:8px; margin-top:8px; }
    .mp-cart-item-qty button { width:28px; height:28px; border-radius:50%; border:1px solid var(--mp-border); background:var(--mp-white); font-size:14px; cursor:pointer; }
    .mp-cart-item-remove { color:var(--mp-danger); font-size:12px; cursor:pointer; margin-top:4px; display:inline-block; }
    .mp-cart-summary { background:var(--mp-white); border-radius:var(--mp-radius-sm); border:1px solid var(--mp-border); padding:16px; margin-top:12px; }
    .mp-cart-row { display:flex; justify-content:space-between; font-size:14px; margin-bottom:8px; }
    .mp-cart-total { font-size:18px; font-weight:700; border-top:1px solid var(--mp-border); padding-top:8px; margin-top:8px; }
    .mp-cart-checkout { width:100%; padding:14px; border-radius:var(--mp-radius-sm); background:var(--mp-primary); color:#fff; font-weight:700; border:none; cursor:pointer; font-size:16px; margin-top:12px; }
    .mp-cart-input { width:100%; padding:12px; border:1px solid var(--mp-border); border-radius:var(--mp-radius-sm); font-size:14px; margin-bottom:12px; outline:none; }
    .mp-cart-input:focus { border-color:var(--mp-primary); }
    .mp-cart-label { font-size:13px; font-weight:600; color:var(--mp-gray); margin-bottom:4px; display:block; }
    .mp-payment-options { display:flex; flex-direction:column; gap:8px; margin-bottom:16px; }
    .mp-payment-option { display:flex; align-items:center; gap:10px; padding:12px; border:1px solid var(--mp-border); border-radius:var(--mp-radius-sm); cursor:pointer; background:var(--mp-white); }
    .mp-payment-option.selected { border-color:var(--mp-primary); background:#EFF6FF; }
    .mp-payment-option input { width:18px; height:18px; }

    /* Trust badges / info blocks */
    .mp-trust-grid { display:grid; grid-template-columns:repeat(2, 1fr); gap:12px; }
    .mp-trust-item { display:flex; align-items:center; gap:10px; padding:14px; background:var(--mp-white); border-radius:var(--mp-radius-sm); border:1px solid var(--mp-border); }
    .mp-trust-item span { font-size:22px; }
    .mp-trust-item div { font-size:13px; font-weight:600; }

    /* Testimonials */
    .mp-testi-grid { display:grid; grid-template-columns:repeat(1, 1fr); gap:16px; }
    .mp-testi-card { background:var(--mp-white); border-radius:var(--mp-radius-sm); border:1px solid var(--mp-border); padding:16px; }
    .mp-testi-text { font-size:14px; line-height:1.6; margin-bottom:12px; font-style:italic; }
    .mp-testi-author { font-size:13px; font-weight:700; }

    /* Newsletter */
    .mp-newsletter { background:var(--mp-primary); color:#fff; padding:40px 24px; text-align:center; border-radius:var(--mp-radius); }
    .mp-newsletter h3 { font-size:22px; font-weight:700; margin-bottom:8px; }
    .mp-newsletter p { margin-bottom:16px; opacity:0.9; }
    .mp-newsletter-form { display:flex; gap:8px; max-width:480px; margin:0 auto; flex-direction:column; }
    .mp-newsletter-form input { flex:1; padding:12px 16px; border:none; border-radius:var(--mp-radius-sm); font-size:14px; }
    .mp-newsletter-form button { padding:12px 24px; background:var(--mp-dark); color:#fff; border:none; border-radius:var(--mp-radius-sm); font-weight:700; cursor:pointer; }

    /* Contact section */
    .mp-contact-grid { display:grid; grid-template-columns:1fr; gap:16px; }
    .mp-contact-card { background:var(--mp-white); border-radius:var(--mp-radius-sm); border:1px solid var(--mp-border); padding:16px; display:flex; align-items:center; gap:12px; }
    .mp-contact-card span { font-size:22px; }
    .mp-contact-card div { font-size:14px; }

    /* WhatsApp CTA Section */
    .mp-wa-section { text-align:center; padding:40px 24px; }
    .mp-wa-btn { display:inline-flex; align-items:center; gap:8px; padding:14px 32px; background:#25D366; color:#fff; font-weight:700; border-radius:var(--mp-radius); font-size:15px; border:none; cursor:pointer; }

    /* Store Hours */
    .mp-hours-list { background:var(--mp-white); border-radius:var(--mp-radius-sm); border:1px solid var(--mp-border); padding:16px; }
    .mp-hours-row { display:flex; justify-content:space-between; padding:8px 0; border-bottom:1px solid var(--mp-border); font-size:14px; }
    .mp-hours-row:last-child { border-bottom:none; }

    /* Breadcrumb */
    .mp-breadcrumb { max-width:1280px; margin:0 auto; padding:16px 24px; font-size:13px; color:var(--mp-gray); }
    .mp-breadcrumb a { color:var(--mp-primary); }

    /* Pagination */
    .mp-pagination { display:flex; justify-content:center; gap:8px; margin-top:24px; }
    .mp-pagination a, .mp-pagination span { padding:8px 14px; border-radius:var(--mp-radius-sm); background:var(--mp-white); border:1px solid var(--mp-border); font-size:14px; font-weight:600; color:var(--mp-dark); }
    .mp-pagination a:hover { background:var(--mp-primary); color:#fff; border-color:var(--mp-primary); }

    /* === New Premium Components === */
    /* Top Bar */
    .mp-topbar { background:#F1F5F9; border-bottom:1px solid var(--mp-border); font-size:12px; color:var(--mp-gray); }
    .mp-topbar-inner { max-width:1280px; margin:0 auto; padding:6px 24px; display:flex; align-items:center; justify-content:space-between; }
    .mp-topbar-left, .mp-topbar-right { display:flex; align-items:center; gap:16px; }
    .mp-topbar-left { white-space:nowrap; }
    .mp-topbar-right { flex-shrink:0; }
    .mp-topbar a { color:var(--mp-gray); transition:color .2s; }
    .mp-topbar a:hover { color:var(--mp-primary); }

    /* Enhanced Header */
    .mp-header-main { max-width:1280px; margin:0 auto; padding:14px 24px; display:flex; align-items:center; justify-content:space-between; gap:16px; }
    .mp-logo-text { display:flex; flex-direction:column; }
    .mp-logo-name { font-weight:800; font-size:18px; color:var(--mp-dark); line-height:1.2; }
    .mp-logo-tagline { font-size:11px; color:var(--mp-gray); line-height:1.2; }
    .mp-header-search { flex:1; max-width:520px; position:relative; }
    .mp-header-search input { width:100%; padding:11px 14px 11px 42px; border:1.5px solid var(--mp-border); border-radius:var(--mp-radius-sm); font-size:14px; background:#F8FAFC; outline:none; transition:border-color .2s, box-shadow .2s; }
    .mp-header-search input:focus { border-color:var(--mp-primary); box-shadow:0 0 0 3px rgba(59,130,246,0.08); }
    .mp-header-search-icon { position:absolute; left:13px; top:50%; transform:translateY(-50%); color:var(--mp-gray); font-size:15px; }
    .mp-header-actions { display:flex; align-items:center; gap:10px; }
    .mp-header-btn { display:flex; align-items:center; gap:6px; padding:8px 14px; border-radius:var(--mp-radius-sm); background:transparent; border:none; cursor:pointer; color:var(--mp-dark); font-size:13px; font-weight:600; transition:background .2s; }
    .mp-header-btn:hover { background:var(--mp-light-gray); }
    .mp-header-btn .icon { font-size:20px; }
    .mp-header-cart { display:flex; align-items:center; gap:6px; position:relative; white-space:nowrap; }
    .mp-header-cart .mp-cart-count { position:static; display:inline-flex; }
    .mp-header-cart .cart-amount { font-size:13px; font-weight:700; color:var(--mp-dark); }

    /* Enhanced Navigation */
    .mp-nav { background:var(--mp-white); border-bottom:1px solid var(--mp-border); }
    .mp-nav-inner { max-width:1280px; margin:0 auto; padding:0 24px; display:flex; gap:4px; overflow-x:auto; scrollbar-width:none; }
    .mp-nav-inner::-webkit-scrollbar { display:none; }
    .mp-nav-link { padding:12px 16px; font-size:13px; font-weight:600; color:var(--mp-gray); white-space:nowrap; border-radius:0; transition:color .15s, background .15s; position:relative; }
    .mp-nav-link:hover, .mp-nav-link.active { color:var(--mp-primary); background:var(--mp-light-gray); }
    .mp-nav-link::after { content:''; position:absolute; bottom:0; left:16px; right:16px; height:2px; background:var(--mp-primary); transform:scaleX(0); transition:transform .2s ease; }
    .mp-nav-link:hover::after, .mp-nav-link.active::after { transform:scaleX(1); }
    .mp-nav-link .nav-label { background:var(--mp-secondary); color:#fff; font-size:9px; font-weight:700; padding:2px 6px; border-radius:10px; margin-left:4px; vertical-align:middle; text-transform:uppercase; }

    /* Enhanced Product Card */
    .mp-card { background:var(--mp-white); border-radius:var(--mp-radius-sm); overflow:hidden; border:1px solid var(--mp-border); transition:transform .2s ease, box-shadow .2s ease; position:relative; }
    .mp-card:hover { transform:translateY(-4px); box-shadow:0 12px 32px rgba(0,0,0,0.1); }
    .mp-card:active { transform:scale(.98); }
    .mp-card-img-wrap { position:relative; width:100%; aspect-ratio:1 / 1; overflow:hidden; background:var(--mp-light-gray); }
    .mp-card-img { width:100%; height:100%; object-fit:cover; transition:transform .4s ease; }
    .mp-card:hover .mp-card-img { transform:scale(1.05); }
    .mp-card-badge { position:absolute; top:10px; left:10px; padding:4px 10px; border-radius:20px; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.5px; z-index:2; }
    .mp-card-badge.new { background:var(--mp-secondary); color:#fff; }
    .mp-card-badge.bestseller { background:#F59E0B; color:#fff; }
    .mp-card-badge.discount { background:var(--mp-danger); color:#fff; }
    .mp-card-wishlist { position:absolute; top:10px; right:10px; width:32px; height:32px; border-radius:50%; background:#fff; border:none; display:flex; align-items:center; justify-content:center; font-size:16px; color:var(--mp-gray); cursor:pointer; box-shadow:0 2px 8px rgba(0,0,0,0.1); z-index:2; transition:all .2s; }
    .mp-card-wishlist:hover { color:var(--mp-danger); transform:scale(1.1); }
    .mp-card-body { padding:14px; }
    .mp-card-name { font-size:14px; font-weight:600; color:var(--mp-dark); line-height:1.35; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; margin-bottom:8px; }
    .mp-card-price { font-size:16px; font-weight:700; color:var(--mp-primary); }
    .mp-card-old { font-size:12px; color:var(--mp-gray); text-decoration:line-through; margin-left:6px; }
    .mp-card-stock { font-size:11px; color:var(--mp-danger); font-weight:600; margin-top:4px; }
    .mp-card-add { width:100%; margin-top:10px; padding:10px; border:none; border-radius:var(--mp-radius-sm); background:var(--mp-primary); color:#fff; font-size:13px; font-weight:600; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:6px; transition:background .2s, transform .15s; }
    .mp-card-add:hover { background:var(--mp-primary-dark); }
    .mp-card-add:active { transform:scale(.97); }
    .mp-card-add:disabled { background:#CBD5E1; cursor:not-allowed; }
    .mp-card-wa { width:100%; margin-top:6px; padding:8px; border:none; border-radius:var(--mp-radius-sm); background:#25D366; color:#fff; font-size:12px; font-weight:600; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:4px; transition:background .2s, transform .15s; }
    .mp-card-wa:hover { background:#1DA851; }
    .mp-card-wa:active { transform:scale(.97); }
    .mp-card-placeholder { width:100%; height:100%; display:flex; flex-direction:column; align-items:center; justify-content:center; color:#94A3B8; gap:8px; }
    .mp-card-placeholder svg { width:48px; height:48px; opacity:0.4; }
    .mp-card-placeholder span { font-size:11px; font-weight:500; letter-spacing:0.5px; text-transform:uppercase; }

    /* Service Grid Card (vertical) */
    .mp-service-grid { display:grid; grid-template-columns:repeat(1, 1fr); gap:16px; }
    .mp-service-card-v { background:var(--mp-white); border-radius:var(--mp-radius-sm); border:1px solid var(--mp-border); overflow:hidden; transition:transform .2s ease, box-shadow .2s ease; }
    .mp-service-card-v:hover { transform:translateY(-4px); box-shadow:0 12px 32px rgba(0,0,0,0.1); }
    .mp-service-img-wrap { position:relative; width:100%; aspect-ratio:16 / 9; overflow:hidden; background:var(--mp-light-gray); }
    .mp-service-img-wrap img { width:100%; height:100%; object-fit:cover; transition:transform .4s ease; }
    .mp-service-card-v:hover .mp-service-img-wrap img { transform:scale(1.05); }
    .mp-service-body { padding:16px; }
    .mp-service-name { font-size:15px; font-weight:700; margin-bottom:6px; color:var(--mp-dark); }
    .mp-service-meta { font-size:12px; color:var(--mp-gray); margin-bottom:10px; display:flex; align-items:center; gap:8px; flex-wrap:wrap; }
    .mp-service-meta span { display:flex; align-items:center; gap:4px; }
    .mp-service-price { font-size:18px; font-weight:700; color:var(--mp-primary); margin-bottom:12px; }
    .mp-service-btn { display:inline-flex; align-items:center; gap:6px; padding:10px 18px; border-radius:var(--mp-radius-sm); background:var(--mp-primary); color:#fff; font-size:13px; font-weight:600; border:none; cursor:pointer; transition:background .2s, transform .15s; }
    .mp-service-btn:hover { background:var(--mp-primary-dark); }
    .mp-service-btn:active { transform:scale(.97); }

    /* Circular Category Cards */
    .mp-cat-circles { display:flex; gap:20px; overflow-x:auto; scrollbar-width:none; padding-bottom:8px; }
    .mp-cat-circles::-webkit-scrollbar { display:none; }
    .mp-cat-circle { display:flex; flex-direction:column; align-items:center; gap:8px; text-align:center; min-width:100px; cursor:pointer; }
    .mp-cat-circle-img { width:100px; height:100px; border-radius:50%; overflow:hidden; background:linear-gradient(135deg, var(--mp-primary), var(--mp-secondary)); display:flex; align-items:center; justify-content:center; color:#fff; font-weight:700; font-size:28px; transition:transform .2s, box-shadow .2s; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
    .mp-cat-circle-img img { width:100%; height:100%; object-fit:cover; }
    .mp-cat-circle:hover .mp-cat-circle-img { transform:scale(1.08); box-shadow:0 8px 20px rgba(0,0,0,0.15); }
    .mp-cat-circle-label { font-size:12px; font-weight:600; color:var(--mp-dark); white-space:nowrap; }

    /* Category Grid Cards */
    .mp-cat-grid-card { position:relative; border-radius:var(--mp-radius-sm); overflow:hidden; aspect-ratio:4 / 3; background:linear-gradient(135deg, var(--mp-primary), var(--mp-secondary)); display:flex; align-items:flex-end; padding:16px; transition:transform .2s, box-shadow .2s; cursor:pointer; }
    .mp-cat-grid-card:hover { transform:translateY(-4px); box-shadow:0 12px 32px rgba(0,0,0,0.12); }
    .mp-cat-grid-card img { position:absolute; inset:0; width:100%; height:100%; object-fit:cover; }
    .mp-cat-grid-card-overlay { position:absolute; inset:0; background:linear-gradient(to top, rgba(0,0,0,0.7), rgba(0,0,0,0.1)); }
    .mp-cat-grid-card-title { position:relative; color:#fff; font-weight:700; font-size:16px; z-index:1; }

    /* Trust / Benefits */
    .mp-trust-section { background:var(--mp-white); border-top:1px solid var(--mp-border); border-bottom:1px solid var(--mp-border); }
    .mp-trust-grid { display:grid; grid-template-columns:repeat(2, 1fr); gap:12px; }
    .mp-trust-item { display:flex; align-items:center; gap:14px; padding:18px 16px; background:transparent; border-radius:var(--mp-radius-sm); }
    .mp-trust-icon { width:44px; height:44px; border-radius:50%; background:rgba(59,130,246,0.08); display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; }
    .mp-trust-text { display:flex; flex-direction:column; }
    .mp-trust-title { font-size:13px; font-weight:700; color:var(--mp-dark); }
    .mp-trust-desc { font-size:12px; color:var(--mp-gray); }

    /* Hero Enhancements */
    .mp-hero-btns { display:flex; gap:12px; flex-wrap:wrap; margin-top:24px; }
    .mp-hero-btn-secondary { display:inline-flex; align-items:center; gap:8px; padding:14px 32px; background:rgba(255,255,255,0.15); color:#fff; font-weight:700; border-radius:var(--mp-radius); font-size:15px; border:1.5px solid rgba(255,255,255,0.4); cursor:pointer; backdrop-filter:blur(4px); transition:all .2s; }
    .mp-hero-btn-secondary:hover { background:rgba(255,255,255,0.25); border-color:rgba(255,255,255,0.6); }
    .mp-hero-trust { display:flex; gap:20px; margin-top:28px; flex-wrap:wrap; }
    .mp-hero-trust-item { display:flex; align-items:center; gap:8px; color:#fff; font-size:13px; font-weight:500; }
    .mp-hero-trust-item .dot { width:6px; height:6px; border-radius:50%; background:var(--mp-secondary); }

    /* Promo Banner */
    .mp-promo-section { background:var(--mp-light); }
    .mp-promo-grid { display:grid; grid-template-columns:repeat(2, 1fr); gap:20px; }
    .mp-promo-card { border-radius:var(--mp-radius); overflow:hidden; position:relative; display:block; aspect-ratio:16/9; }
    .mp-promo-card img { width:100%; height:100%; object-fit:cover; display:block; transition:transform .4s; }
    .mp-promo-card:hover img { transform:scale(1.05); }
    .mp-promo-overlay { position:absolute; inset:0; background:linear-gradient(to top, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.1) 60%); display:flex; flex-direction:column; justify-content:flex-end; padding:24px; color:#fff; }
    .mp-promo-title { font-size:20px; font-weight:700; margin-bottom:4px; }
    .mp-promo-subtitle { font-size:14px; opacity:0.9; margin-bottom:12px; }
    .mp-promo-btn { display:inline-flex; align-self:flex-start; padding:8px 18px; background:var(--mp-primary); color:#fff; font-size:13px; font-weight:600; border-radius:var(--mp-radius); }
    .mp-promo-btn:hover { background:var(--mp-primary-dark); }

    /* Store Info */
    .mp-store-info { background:var(--mp-white); border-top:1px solid var(--mp-border); border-bottom:1px solid var(--mp-border); }
    .mp-store-info-inner { max-width:800px; }

    /* Contact Section */
    .mp-contact-card { background:var(--mp-white); border-radius:var(--mp-radius-sm); border:1px solid var(--mp-border); padding:18px; display:flex; align-items:center; gap:14px; transition:transform .2s, box-shadow .2s; }
    .mp-contact-card:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,0,0,0.06); }
    .mp-contact-icon { width:44px; height:44px; border-radius:12px; background:rgba(59,130,246,0.08); display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; }

    /* WhatsApp CTA */
    .mp-wa-section { text-align:center; padding:48px 24px; background:linear-gradient(135deg, #25D366, #128C7E); color:#fff; border-radius:var(--mp-radius); margin:24px auto; max-width:1280px; }
    .mp-wa-section h3 { font-size:24px; font-weight:700; margin-bottom:8px; }
    .mp-wa-section p { opacity:0.9; margin-bottom:20px; font-size:15px; }
    .mp-wa-btn { display:inline-flex; align-items:center; gap:8px; padding:14px 32px; background:#fff; color:#25D366; font-weight:700; border-radius:var(--mp-radius); font-size:15px; border:none; cursor:pointer; transition:transform .2s, box-shadow .2s; }
    .mp-wa-btn:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,0,0,0.15); }

    /* Newsletter */
    .mp-newsletter { background:linear-gradient(135deg, var(--mp-primary), var(--mp-primary-dark)); color:#fff; padding:48px 24px; text-align:center; border-radius:var(--mp-radius); }
    .mp-newsletter h3 { font-size:24px; font-weight:700; margin-bottom:8px; }
    .mp-newsletter p { margin-bottom:20px; opacity:0.9; font-size:15px; }
    .mp-newsletter-form { display:flex; gap:10px; max-width:480px; margin:0 auto; flex-direction:column; }
    .mp-newsletter-form input { flex:1; padding:14px 18px; border:none; border-radius:var(--mp-radius-sm); font-size:14px; }
    .mp-newsletter-form button { padding:14px 28px; background:var(--mp-dark); color:#fff; border:none; border-radius:var(--mp-radius-sm); font-weight:700; cursor:pointer; transition:background .2s; }
    .mp-newsletter-form button:hover { background:#000; }

    /* Enhanced Footer */
    .mp-footer { background:var(--mp-footer-bg); color:var(--mp-footer-text); padding:56px 24px 24px; }
    .mp-footer-inner { max-width:1280px; margin:0 auto; display:grid; grid-template-columns:repeat(4, 1fr); gap:40px; }
    .mp-footer-brand { color:#fff; font-weight:800; font-size:20px; margin-bottom:12px; }
    .mp-footer-desc { font-size:14px; line-height:1.7; margin-bottom:20px; }
    .mp-footer-heading { color:#fff; font-weight:700; font-size:13px; margin-bottom:16px; text-transform:uppercase; letter-spacing:1px; }
    .mp-footer-links li { margin-bottom:10px; }
    .mp-footer-links a { font-size:14px; color:var(--mp-footer-text); transition:color .2s, padding-left .2s; display:inline-block; }
    .mp-footer-links a:hover { color:#fff; padding-left:4px; }
    .mp-footer-social { display:flex; gap:10px; margin-top:16px; }
    .mp-footer-social a { width:36px; height:36px; border-radius:50%; background:rgba(255,255,255,0.08); display:flex; align-items:center; justify-content:center; font-size:14px; color:#fff; transition:background .2s, transform .2s; }
    .mp-footer-social a:hover { background:var(--mp-primary); transform:translateY(-2px); }
    .mp-footer-bottom { max-width:1280px; margin:32px auto 0; padding-top:24px; border-top:1px solid rgba(255,255,255,0.08); text-align:center; font-size:13px; color:var(--mp-footer-text); }
    .mp-footer-contact-item { display:flex; align-items:center; gap:10px; font-size:13px; color:var(--mp-footer-text); margin-bottom:10px; }
    .mp-footer-contact-item a { color:var(--mp-footer-text); text-decoration:underline; text-underline-offset:3px; }
    .mp-footer-contact-item a:hover { color:#fff; }

    /* Footer Style: Compact */
    .mp-footer-compact { display:flex; flex-direction:column; align-items:center; text-align:center; max-width:900px; margin:0 auto; gap:0; }
    .mp-footer-compact-top { margin-bottom:28px; }
    .mp-footer-compact-top .mp-footer-brand { font-size:22px; margin-bottom:10px; }
    .mp-footer-compact-top .mp-footer-desc { max-width:520px; margin:0 auto 16px; font-size:14px; line-height:1.7; color:var(--mp-footer-text); }
    .mp-footer-compact-top .mp-footer-social { justify-content:center; margin-top:0; }
    .mp-footer-compact-row { display:flex; align-items:center; gap:20px; flex-wrap:wrap; justify-content:center; width:100%; padding:14px 0; border-top:1px solid rgba(255,255,255,0.08); }
    .mp-footer-compact-shop a { font-size:14px; color:var(--mp-footer-text); transition:color .2s; }
    .mp-footer-compact-shop a:hover { color:#fff; }
    .mp-footer-compact-support, .mp-footer-compact-contact { display:flex; align-items:center; gap:10px; flex-wrap:wrap; justify-content:center; }
    .mp-footer-compact-support a, .mp-footer-compact-contact a, .mp-footer-compact-contact span { font-size:13px; color:var(--mp-footer-text); line-height:1.5; }
    .mp-footer-compact-support a:hover, .mp-footer-compact-contact a:hover { color:#fff; }
    .mp-footer-compact-contact .sep { color:#475569; font-size:13px; }
    .mp-footer-compact-label { display:none; }
    .mp-footer[data-footer-style="compact"] .mp-footer-bottom { margin-top:20px; }
    @media(max-width:767px){
      .mp-footer-compact-label { display:block; font-size:11px; font-weight:700; color:#fff; text-transform:uppercase; letter-spacing:1px; margin-bottom:6px; }
      .mp-footer-compact-support, .mp-footer-compact-contact { flex-direction:column; align-items:center; text-align:center; }
      .mp-footer-compact-contact .sep { display:none; }
    }

    /* Footer Style: About-Focused */
    .mp-footer[data-footer-style="about_focused"] .mp-footer-inner { grid-template-columns:2fr 1fr 1fr 1fr; }
    .mp-footer[data-footer-style="about_focused"] .mp-footer-desc { font-size:15px; line-height:1.8; max-width:360px; }

    /* Enhanced Sticky Cart */
    .mp-sticky-cart { position:fixed; bottom:0; left:0; right:0; background:var(--mp-white); border-top:1px solid var(--mp-border); padding:12px 16px; z-index:200; display:none; box-shadow:0 -4px 20px rgba(0,0,0,0.06); }
    .mp-sticky-cart.show { display:block; }
    .mp-sticky-cart-inner { max-width:1280px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; }
    .mp-sticky-cart-info { display:flex; align-items:center; gap:12px; }
    .mp-sticky-cart-items { font-size:13px; color:var(--mp-gray); }
    .mp-sticky-cart-total { font-size:18px; font-weight:800; color:var(--mp-dark); }
    .mp-sticky-cart-btn { padding:12px 28px; border-radius:var(--mp-radius-sm); background:var(--mp-primary); color:#fff; font-weight:700; border:none; cursor:pointer; font-size:14px; transition:background .2s, transform .15s; }
    .mp-sticky-cart-btn:hover { background:var(--mp-primary-dark); }
    .mp-sticky-cart-btn:active { transform:scale(.97); }

    /* Mobile Bottom Nav */
    .mp-mobile-nav { display:none; position:fixed; bottom:0; left:0; right:0; background:var(--mp-white); border-top:1px solid var(--mp-border); z-index:300; padding:6px 0 8px; box-shadow:0 -2px 12px rgba(0,0,0,0.04); }
    .mp-mobile-nav-inner { display:flex; justify-content:space-around; align-items:center; }
    .mp-mobile-nav-item { display:flex; flex-direction:column; align-items:center; gap:2px; font-size:10px; color:var(--mp-gray); padding:4px 8px; border:none; background:none; cursor:pointer; transition:color .2s; }
    .mp-mobile-nav-item.active { color:var(--mp-primary); }
    .mp-mobile-nav-item .nav-icon { font-size:22px; line-height:1; }

    /* Sticky WhatsApp */
    .mp-sticky-wa { position:fixed; bottom:80px; right:16px; z-index:250; width:52px; height:52px; border-radius:50%; background:linear-gradient(135deg, #25D366, #128C7E); color:#fff; display:flex; align-items:center; justify-content:center; box-shadow:0 4px 16px rgba(37,211,102,0.3); border:none; cursor:pointer; transition:transform .2s, box-shadow .2s; }
    .mp-sticky-wa:hover { transform:scale(1.08); box-shadow:0 6px 20px rgba(37,211,102,0.4); }

    /* Back to Top */
    .mp-backtop { position:fixed; bottom:140px; right:16px; z-index:240; width:44px; height:44px; border-radius:50%; background:var(--mp-white); color:var(--mp-dark); display:none; align-items:center; justify-content:center; box-shadow:0 4px 12px rgba(0,0,0,0.15); border:1px solid var(--mp-border); cursor:pointer; transition:transform .2s, background .2s; }
    .mp-backtop:hover { transform:translateY(-2px); background:var(--mp-light-gray); }
    .mp-backtop.show { display:flex; }
    @media(min-width:768px){ .mp-backtop { bottom:24px; right:24px; } }

    /* Utility */
    .mp-footer-space { height:100px; }
    .mp-mb-1 { margin-bottom:8px; }
    .mp-mt-1 { margin-top:8px; }

    /* Desktop Overrides */
    @media(min-width:768px){
      .mp-grid { grid-template-columns:repeat(3, 1fr); gap:20px; }
      .mp-service-grid { grid-template-columns:repeat(2, 1fr); }
      .mp-cat-grid { grid-template-columns:repeat(4, 1fr); }
      .mp-testi-grid { grid-template-columns:repeat(3, 1fr); }
      .mp-contact-grid { grid-template-columns:repeat(2, 1fr); }
      .mp-trust-grid { grid-template-columns:repeat(4, 1fr); }
      .mp-footer-inner { grid-template-columns:repeat(4, 1fr); }
      .mp-newsletter-form { flex-direction:row; }
      .mp-hero-img { height:500px; }
      .mp-hero-title { font-size:52px; }
      .mp-mobile-menu-btn { display:none; }
      .mp-modal-overlay { align-items:center; }
      .mp-modal { border-radius:var(--mp-radius); max-height:90vh; }
      .mp-promo-grid { grid-template-columns:repeat(2, 1fr); }
      .mp-cat-circles { justify-content:center; flex-wrap:wrap; }
    }
    @media(min-width:1024px){
      .mp-grid { grid-template-columns:repeat(4, 1fr); gap:24px; }
      .mp-service-grid { grid-template-columns:repeat(3, 1fr); }
      .mp-card-img-wrap { aspect-ratio:4 / 5; }
      .mp-hero-img { height:560px; }
    }
    @media(min-width:1280px){
      .mp-grid { grid-template-columns:repeat(5, 1fr); gap:24px; }
      .mp-service-grid { grid-template-columns:repeat(4, 1fr); }
    }
    /* Button color override */
    .mp-card-add, .mp-hero-btn, .mp-service-btn, .mp-sticky-cart-btn, .mp-cart-checkout, .mp-modal-add { background:var(--mp-button); }
    .mp-card-add:hover, .mp-hero-btn:hover, .mp-service-btn:hover, .mp-sticky-cart-btn:hover, .mp-cart-checkout:hover, .mp-modal-add:hover { background:var(--mp-button-dark); }

    @media(max-width:767px){
      .mp-header-main { padding:10px 16px; }
      .mp-header-search { display:none; }
      .mp-nav-inner { padding:0 16px; }
      .mp-section { padding:32px 16px; }
      .mp-hero-title { font-size:28px; }
      .mp-hero-subtitle { font-size:15px; }
      .mp-hero-img { height:320px; }
      .mp-footer-inner { grid-template-columns:1fr 1fr; gap:24px; }
      .mp-footer-compact-support, .mp-footer-compact-contact { gap:4px; }
      .mp-footer[data-footer-style="about_focused"] .mp-footer-inner { grid-template-columns:1fr; }
      .mp-mobile-nav { display:block; }
      .mp-sticky-wa { display:flex; }
      .mp-footer-space { height:120px; }
      .mp-mobile-menu-btn { display:block; }
      .mp-topbar { display:none; }
      .mp-header-cart .cart-amount { display:none; }
      .mp-hero-trust { gap:12px; }
      .mp-hero-trust-item { font-size:12px; }
      .mp-cat-circles { gap:14px; }
      .mp-cat-circle-img { width:80px; height:80px; font-size:22px; }
      .mp-cat-circle-label { font-size:11px; }
    }
  </style>
  <?php if(!empty($settings->header_text_color)): ?>
  <style>.mp-logo-name, .mp-logo-tagline { color: <?= htmlspecialchars($settings->header_text_color); ?> !important; }</style>
  <?php endif; ?>
  <script>
    const STORE_SLUG = '<?= $settings->store_slug ?? ''; ?>';
    const STORE_ID = <?= $settings->store_id ?? 0; ?>;
    const CURRENCY = '<?= htmlspecialchars($store_currency['symbol'] ?? '&#8358;'); ?>';
    const CURRENCY_PLACE = '<?= htmlspecialchars($store_currency['placement'] ?? 'Left'); ?>';
    function formatMoney(amount){
      const formatted = amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
      return CURRENCY_PLACE === 'Right' ? formatted + ' ' + CURRENCY : CURRENCY + ' ' + formatted;
    }
    function showToast(msg){
      const t = document.getElementById('toast');
      if(!t) return;
      t.textContent = msg;
      t.classList.add('show');
      setTimeout(() => t.classList.remove('show'), 2500);
    }
  </script>
</head>
<body>

<?php $this->load->view('themes/shared/header'); ?>

<?= $content; ?>

<?php $this->load->view('themes/shared/footer'); ?>

<?php $this->load->view('themes/shared/scripts'); ?>

<?php if(!empty($seo_jsonld)): ?>
<script type="application/ld+json"><?= json_encode($seo_jsonld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
<?php else: ?>
<script type="application/ld+json"><?= json_encode([
  '@context' => 'https://schema.org',
  '@type' => 'Organization',
  'name' => $store->store_name ?? 'Store',
  'url' => base_url('store/' . ($settings->store_slug ?? '')),
  'logo' => $logo_url ?? '',
  'description' => $settings->meta_description ?? ($settings->store_description ?? ''),
  'sameAs' => array_values(array_filter([
    $social_links['facebook'] ?? '',
    $social_links['instagram'] ?? '',
    $social_links['x'] ?? '',
    $social_links['youtube'] ?? '',
    $social_links['tiktok'] ?? ''
  ]))
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
<?php endif; ?>

<script>
  if('serviceWorker' in navigator){
    window.addEventListener('load', function(){
      navigator.serviceWorker.register('<?= base_url("sw.js"); ?>').then(function(reg){
        console.log('PWA SW registered:', reg.scope);
      }).catch(function(err){
        console.log('PWA SW registration failed:', err);
      });
    });
  }
</script>
</body>
</html>
