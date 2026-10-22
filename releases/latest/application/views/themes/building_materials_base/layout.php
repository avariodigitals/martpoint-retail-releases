<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php
  $seoTitle = $seo_title ?? ($settings->meta_title ?? ($store->store_name ?? 'Store'));
  $seoDesc = $seo_description ?? ($settings->meta_description ?? ($settings->store_description ?? 'Browse our building systems'));
  $seoImage = $seo_image ?? ($logo_url ?? base_url('uploads/site/icon.webp'));
  $seoUrl = $seo_canonical ?? current_url();
  $seoType = $seo_type ?? 'website';
  $storeName = $store->store_name ?? 'Online Store';
  $bmBrand = $bm_brand ?? $storeName;
?>
  <title><?= htmlspecialchars($seoTitle . ' | ' . $storeName); ?></title>
  <meta name="description" content="<?= htmlspecialchars($seoDesc); ?>">
<?php if(!empty($settings->meta_keywords)): ?>
  <meta name="keywords" content="<?= htmlspecialchars($settings->meta_keywords); ?>">
<?php endif; ?>
  <meta name="robots" content="<?= ($settings->robots_index ?? '1') == '1' ? 'index, follow' : 'noindex, nofollow'; ?>">
  <link rel="canonical" href="<?= htmlspecialchars($seoUrl); ?>">
  <link rel="icon" href="<?= $favicon_url ?? base_url('uploads/site/icon.webp'); ?>">
  <link rel="manifest" href="<?= base_url('manifest.json'); ?>">
  <meta name="theme-color" content="<?= htmlspecialchars($settings->theme_color ?? '#142438'); ?>">
  <meta name="mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-capable" content="yes">
  <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
  <meta name="apple-mobile-web-app-title" content="<?= htmlspecialchars($storeName); ?>">
  <link rel="apple-touch-icon" href="<?= $favicon_url ?? base_url('uploads/site/icon.webp'); ?>">
<?php
  $mpGaId = trim((string)($settings->google_analytics_id ?? ''));
  $mpFbId = trim((string)($settings->facebook_pixel_id ?? ''));
  $mpTtId = trim((string)($settings->tiktok_pixel_id ?? ''));
  $mpNeedConsent = ((int)($settings->require_tracking_consent ?? 1) === 1);
  $mpConsentKey = 'mp_track_consent_' . (int)($settings->store_id ?? 0);
?>
<script>
(function(){
  var KEY = <?= json_encode($mpConsentKey); ?>;
  window.MP_NEED_CONSENT = <?= $mpNeedConsent ? 'true' : 'false'; ?>;
  var loaded = false;
  window.mpLoadTrackers = function(){
    if(loaded) return; loaded = true;
    <?php if($mpGaId !== ''): ?>
    var gs=document.createElement('script');gs.async=true;
    gs.src='https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($mpGaId); ?>';
    document.head.appendChild(gs);
    window.dataLayer=window.dataLayer||[];window.gtag=function(){dataLayer.push(arguments);};
    gtag('js',new Date());gtag('config','<?= htmlspecialchars($mpGaId); ?>');
    <?php endif; ?>
    <?php if($mpFbId !== ''): ?>
    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
    fbq('init','<?= htmlspecialchars($mpFbId); ?>');fbq('track','PageView');
    <?php endif; ?>
    <?php if($mpTtId !== ''): ?>
    !function(w,d,t){w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"];ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e};ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{};ttq._i[e]=[];ttq._i[e]._u=i;ttq._t=ttq._t||{};ttq._t[e]=+new Date;ttq._o=ttq._o||{};ttq._o[e]=n||{};var o=document.createElement("script");o.type="text/javascript";o.async=!0;o.src=i+"?sdkid="+e+"&lib="+t;var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};ttq.load('<?= htmlspecialchars($mpTtId); ?>');ttq.page();}(window,document,'ttq');
    <?php endif; ?>
  };
  window.mpTrackConsent = {
    granted: function(){ try{return localStorage.getItem(KEY)==='yes';}catch(e){return false;} },
    grant: function(){ try{localStorage.setItem(KEY,'yes');}catch(e){} window.mpLoadTrackers(); var b=document.getElementById('mp-consent-bar'); if(b) b.remove(); },
    decline: function(){ try{localStorage.setItem(KEY,'no');}catch(e){} var b=document.getElementById('mp-consent-bar'); if(b) b.remove(); },
    withdraw: function(){
      try{localStorage.setItem(KEY,'no');}catch(e){}
      try{ if(window.fbq) fbq('consent','revoke'); }catch(e){}
      try{ if(window.gtag) gtag('consent','update',{ad_storage:'denied',analytics_storage:'denied'}); }catch(e){}
      try{ if(window.ttq && ttq.disableCookie) ttq.disableCookie(); }catch(e){}
      var b=document.getElementById('mp-consent-bar'); if(b) b.remove();
    }
  };
  <?php if($mpNeedConsent): ?>
  if(window.mpTrackConsent.granted()){ window.mpLoadTrackers(); }
  <?php else: ?>
  window.mpLoadTrackers();
  <?php endif; ?>
})();
</script>
<?php if(!empty($settings->custom_head_scripts)): ?>
  <?= $settings->custom_head_scripts; ?>
<?php endif; ?>

  <meta property="og:type" content="<?= htmlspecialchars($seoType); ?>">
  <meta property="og:site_name" content="<?= htmlspecialchars($storeName); ?>">
  <meta property="og:title" content="<?= htmlspecialchars($seoTitle); ?>">
  <meta property="og:description" content="<?= htmlspecialchars($seoDesc); ?>">
  <meta property="og:url" content="<?= htmlspecialchars($seoUrl); ?>">
  <meta property="og:image" content="<?= htmlspecialchars($seoImage); ?>">
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= htmlspecialchars($seoTitle); ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars($seoDesc); ?>">
  <meta name="twitter:image" content="<?= htmlspecialchars($seoImage); ?>">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&amp;family=Manrope:wght@400;500;600;700;800&amp;family=Playfair+Display:ital,wght@0,400;0,500;1,400;1,500&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= base_url('theme/css/font-awesome-4.7.0/css/font-awesome.min.css?v=4.7.0'); ?>">
  <?php $this->load->view('themes/shared/_shared_css'); ?>
  <link rel="stylesheet" href="<?= base_url('theme/dist/css/building_materials.css?v='.filemtime(FCPATH.'theme/dist/css/building_materials.css')); ?>">
  <style>
    body.bm-building{--bm-accent:<?= htmlspecialchars($bm_primary ?? '#245C94'); ?>;--bm-accent-ink:<?= htmlspecialchars(mp_building_accent_text($bm_primary ?? '#245C94')); ?>;--bm-on-accent:<?= mp_building_color_tokens($bm_primary ?? '#245C94')['foreground']; ?>;--bm-dark:<?= mp_building_color_tokens($bm_dark ?? '#142438')['dark_surface']; ?>;}
  </style>
  <script>
    const STORE_SLUG = <?= json_encode($settings->store_slug ?? ''); ?>;
    const STORE_ID = <?= (int)($settings->store_id ?? 0); ?>;
    const CURRENCY = <?= json_encode($store_currency['symbol'] ?? '&#8358;'); ?>;
    const CURRENCY_PLACE = <?= json_encode($store_currency['placement'] ?? 'Left'); ?>;
    window.SF_CURRENCY = <?= json_encode($store_currency['code'] ?? 'NGN'); ?>;
  </script>
</head>
<body class="bm-building bm-skin-<?= htmlspecialchars($bm_skin ?? 'atelier'); ?> theme-<?= htmlspecialchars($theme_key ?? 'framework_atelier'); ?>">
<a class="bm-skip" href="#bm-main">Skip to content</a>
<?php $this->load->view('themes/building_materials_base/header'); ?>
<main id="bm-main" class="bm-main">
  <?= $content; ?>
</main>
<?php $this->load->view('themes/building_materials_base/footer'); ?>

<?php $this->load->view('themes/shared/scripts'); ?>
<script defer src="<?= base_url('theme/js/building_materials.js?v='.filemtime(FCPATH.'theme/js/building_materials.js')); ?>"></script>

<?php if(!empty($seo_jsonld)): ?>
<script type="application/ld+json"><?= json_encode($seo_jsonld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
<?php else: ?>
<script type="application/ld+json"><?= json_encode([
  '@context' => 'https://schema.org',
  '@type' => 'LocalBusiness',
  'name' => $store->store_name ?? 'Store',
  'url' => base_url('store/' . ($settings->store_slug ?? '')),
  'logo' => $logo_url ?? '',
  'image' => $seoImage,
  'description' => $settings->meta_description ?? ($settings->store_description ?? ''),
  'telephone' => $settings->store_phone ?? '',
  'email' => $settings->store_email ?? '',
  'address' => $settings->store_address ?? '',
  'sameAs' => array_values(array_filter([
    $social_links['facebook'] ?? '',
    $social_links['instagram'] ?? '',
    $social_links['x'] ?? '',
    $social_links['youtube'] ?? '',
    $social_links['tiktok'] ?? ''
  ], function($url){ return filter_var($url, FILTER_VALIDATE_URL); }))
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
<?php endif; ?>

<?php if(($mpGaId !== '' || $mpFbId !== '' || $mpTtId !== '') && $mpNeedConsent): ?>
<div id="mp-consent-bar" style="display:none;position:fixed;left:0;right:0;bottom:0;z-index:99999;background:#111923;color:#E2E8F0;padding:14px 16px;font-size:13px;line-height:1.5;box-shadow:0 -2px 16px rgba(0,0,0,.35);">
  <div style="max-width:1280px;margin:0 auto;display:flex;gap:14px;align-items:center;justify-content:space-between;flex-wrap:wrap;">
    <span style="flex:1;min-width:220px;">We use cookies and analytics tools to improve the storefront. You can accept or decline tracking.</span>
    <span style="display:flex;gap:10px;">
      <button type="button" onclick="mpTrackConsent.decline()" style="background:transparent;color:#CBD5E1;border:1px solid #475569;padding:8px 18px;border-radius:8px;cursor:pointer;font-size:13px;">Decline</button>
      <button type="button" onclick="mpTrackConsent.grant()" style="background:var(--bm-accent,#E16D3B);color:#fff;border:none;padding:8px 18px;border-radius:8px;font-weight:600;cursor:pointer;font-size:13px;">Accept</button>
    </span>
  </div>
</div>
<script>
(function(){var b=document.getElementById('mp-consent-bar');try{if(b && localStorage.getItem(<?= json_encode($mpConsentKey); ?>)===null){b.style.display='block';}}catch(e){if(b)b.style.display='block';}})();
</script>
<?php endif; ?>
<?php $this->load->view('themes/shared/product_image_fallbacks'); ?>
</body>
</html>
