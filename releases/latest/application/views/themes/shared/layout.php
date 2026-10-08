<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
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
  <link rel="icon" href="<?= $favicon_url ?? base_url('uploads/site/icon.webp'); ?>">
  <link rel="manifest" href="<?= base_url('manifest.json'); ?>">
  <meta name="theme-color" content="<?= htmlspecialchars($settings->theme_color ?? '#0B1120'); ?>">
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
  // First-party event funnel also reads consent — expose the policy even
  // when no third-party tracker is configured.
  window.MP_NEED_CONSENT = <?= $mpNeedConsent ? 'true' : 'false'; ?>;
  var loaded = false;
  window.mpLoadTrackers = function(){
    if(loaded) return; loaded = true;
    <?php if($mpGaId !== ''): ?>
    // Google Analytics
    var gs=document.createElement('script');gs.async=true;
    gs.src='https://www.googletagmanager.com/gtag/js?id=<?= htmlspecialchars($mpGaId); ?>';
    document.head.appendChild(gs);
    window.dataLayer=window.dataLayer||[];window.gtag=function(){dataLayer.push(arguments);};
    gtag('js',new Date());gtag('config','<?= htmlspecialchars($mpGaId); ?>');
    <?php endif; ?>
    <?php if($mpFbId !== ''): ?>
    // Meta Pixel
    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');
    fbq('init','<?= htmlspecialchars($mpFbId); ?>');fbq('track','PageView');
    <?php endif; ?>
    <?php if($mpTtId !== ''): ?>
    // TikTok Pixel
    !function(w,d,t){w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"];ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e};ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{};ttq._i[e]=[];ttq._i[e]._u=i;ttq._t=ttq._t||{};ttq._t[e]=+new Date;ttq._o=ttq._o||{};ttq._o[e]=n||{};var o=document.createElement("script");o.type="text/javascript";o.async=!0;o.src=i+"?sdkid="+e+"&lib="+t;var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};ttq.load('<?= htmlspecialchars($mpTtId); ?>');ttq.page();}(window,document,'ttq');
    <?php endif; ?>
  };
  window.mpTrackConsent = {
    granted: function(){ try{return localStorage.getItem(KEY)==='yes';}catch(e){return false;} },
    grant: function(){ try{localStorage.setItem(KEY,'yes');}catch(e){} window.mpLoadTrackers(); var b=document.getElementById('mp-consent-bar'); if(b) b.remove(); },
    decline: function(){ try{localStorage.setItem(KEY,'no');}catch(e){} var b=document.getElementById('mp-consent-bar'); if(b) b.remove(); },
    // Withdraw after a grant: stored state flips to 'no', which stops every
    // subsequent event (mpTrackingAllowed re-reads localStorage) and tells
    // loaded pixels to stop processing where the platform supports it.
    withdraw: function(){
      try{localStorage.setItem(KEY,'no');}catch(e){}
      try{ if(window.fbq) fbq('consent','revoke'); }catch(e){}
      try{ if(window.gtag) gtag('consent','update',{ad_storage:'denied',analytics_storage:'denied'}); }catch(e){}
      try{ if(window.ttq && ttq.disableCookie) ttq.disableCookie(); }catch(e){}
      var b=document.getElementById('mp-consent-bar'); if(b) b.remove();
    },
    showBar: function(){ var b=document.getElementById('mp-consent-bar'); if(b) b.style.display='block'; }
  };
  <?php if($mpNeedConsent): ?>
  // Trackers load only after explicit consent
  if(window.mpTrackConsent.granted()){ window.mpLoadTrackers(); }
  <?php else: ?>
  window.mpLoadTrackers();
  <?php endif; ?>
})();
</script>
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
<?php $themeKey = $theme_key ?? 'general_retail'; ?>
<?php if(file_exists(FCPATH . 'theme/dist/css/' . $themeKey . '.css')): ?>
  <link rel="stylesheet" href="<?= base_url('theme/dist/css/' . $themeKey . '.css?v=1'); ?>">
<?php endif; ?>
  <link rel="stylesheet" href="<?= base_url('theme/css/font-awesome-4.7.0/css/font-awesome.min.css?v=4.7.0'); ?>">
  <?php /* Shared mp-* stylesheet — kept in its own partial so the printing
           layout can include exactly the same layer for the Homepage Builder
           sections and the footer, which carry no CSS of their own. */ ?>
  <?php $this->load->view("themes/shared/_shared_css"); ?>
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
    function sfCartUnitTotal(item){
      return (Number(item.price)||0) + (Array.isArray(item.addons)?item.addons.reduce((sum,addon)=>sum+((Number(addon.price)||0)*(Number(addon.qty)||0)),0):0);
    }
    function sfCartLineTotal(item){ return sfCartUnitTotal(item) * (Number(item.qty)||0); }
    function showToast(msg){
      const t = document.getElementById('toast');
      if(!t) return;
      t.textContent = msg;
      t.classList.add('show');
      setTimeout(() => t.classList.remove('show'), 2500);
    }
  </script>
</head>
<?php $themeKey = $theme_key ?? 'general_retail'; ?>
<body class="theme-<?= htmlspecialchars($themeKey); ?>">

<?php
  $themeHeader = 'themes/' . $themeKey . '/header';
  if(file_exists(APPPATH . 'views/' . $themeHeader . '.php')){
    $this->load->view($themeHeader);
  } else {
    $this->load->view('themes/shared/header');
  }
?>

<?= $content; ?>
<?php if(!empty($product_addons) || !empty($service_addons)): ?>
  <?php $this->load->view('themes/shared/product_addons', [
    'addons' => $product_addons ?? $service_addons,
    'parent_type' => !empty($product_addons) ? 'product' : 'service',
    'parent_id' => !empty($product_addons) ? (int)$product->id : (int)$service->id,
  ]); ?>
<?php endif; ?>
<?php if(!empty($product_reviews_enabled)): ?>
  <?php $this->load->view('themes/shared/product_reviews', [
    'reviews' => $product_reviews ?? [],
    'aggregate' => $product_review_aggregate ?? ['count' => 0, 'average' => null],
    'post_url' => $product_review_post_url ?? '',
    'csrf_name' => $review_csrf_name ?? '',
    'csrf_hash' => $review_csrf_hash ?? '',
    'product' => $product ?? null,
  ]); ?>
<?php endif; ?>

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
      navigator.serviceWorker.getRegistrations().then(function(registrations){
        return Promise.all(registrations.map(function(reg){ return reg.unregister(); }));
      }).then(function(){
        if('caches' in window){
          return caches.keys().then(function(names){
            return Promise.all(names.map(function(name){ return caches.delete(name); }));
          });
        }
      }).then(function(){
        return navigator.serviceWorker.register('<?= base_url("sw.js"); ?>');
      }).then(function(reg){
        console.log('PWA SW registered:', reg.scope);
      }).catch(function(err){
        console.log('PWA SW registration failed:', err);
      });
    });
  }
</script>
<?php if(($mpGaId !== '' || $mpFbId !== '' || $mpTtId !== '') && $mpNeedConsent): ?>
<div id="mp-consent-bar" style="display:none;position:fixed;left:0;right:0;bottom:0;z-index:99999;background:#0F172A;color:#E2E8F0;padding:14px 16px;font-size:13px;line-height:1.5;box-shadow:0 -2px 16px rgba(0,0,0,.35);">
  <div style="max-width:1280px;margin:0 auto;display:flex;gap:14px;align-items:center;justify-content:space-between;flex-wrap:wrap;">
    <span style="flex:1;min-width:220px;">We use cookies and analytics tools to improve your shopping experience. You can accept or decline tracking.</span>
    <span style="display:flex;gap:10px;">
      <button type="button" onclick="mpTrackConsent.decline()" style="background:transparent;color:#CBD5E1;border:1px solid #475569;padding:8px 18px;border-radius:8px;cursor:pointer;font-size:13px;">Decline</button>
      <button type="button" onclick="mpTrackConsent.grant()" style="background:var(--mp-primary,#3B82F6);color:#fff;border:none;padding:8px 18px;border-radius:8px;font-weight:600;cursor:pointer;font-size:13px;">Accept</button>
    </span>
  </div>
</div>
<script>
(function(){var b=document.getElementById('mp-consent-bar');try{if(b && localStorage.getItem(<?= json_encode($mpConsentKey); ?>)===null){b.style.display='block';}}catch(e){if(b)b.style.display='block';}})();
</script>
<?php endif; ?>
</body>
</html>
