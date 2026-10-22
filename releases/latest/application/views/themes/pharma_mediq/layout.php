<?php
$themeKey = 'pharma_mediq';
$storeName = trim((string)($store->store_name ?? '')) ?: 'Pharmacy';
$seoTitle = $seo_title ?? ($settings->meta_title ?? $storeName);
$seoDesc = $seo_description ?? ($settings->meta_description ?? ($settings->store_description ?? 'Shop medicines and everyday health essentials from a trusted pharmacy.'));
$seoImage = $seo_image ?? ($logo_url ?? base_url('uploads/site/icon.webp'));
$seoUrl = $seo_canonical ?? current_url();
$favicon = $favicon_url ?? base_url('uploads/site/icon.webp');
$currencySymbol = $store_currency['symbol'] ?? '&#8358;';
$currencyPlacement = $store_currency['placement'] ?? 'Left';
$consentRequired = ((int)($settings->require_tracking_consent ?? 1) === 1);
$consentKey = 'mp_track_consent_' . (int)($settings->store_id ?? 0);
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
  <title><?= htmlspecialchars($seoTitle . ' | ' . $storeName); ?></title>
  <meta name="description" content="<?= htmlspecialchars($seoDesc); ?>">
  <?php if (!empty($settings->meta_keywords)): ?><meta name="keywords" content="<?= htmlspecialchars($settings->meta_keywords); ?>"><?php endif; ?>
  <meta name="robots" content="<?= ($settings->robots_index ?? '1') == '1' ? 'index, follow' : 'noindex, nofollow'; ?>">
  <link rel="canonical" href="<?= htmlspecialchars($seoUrl); ?>">
  <link rel="icon" href="<?= htmlspecialchars($favicon); ?>">
  <link rel="apple-touch-icon" href="<?= htmlspecialchars($favicon); ?>">
  <meta name="theme-color" content="#a7eb66">
  <meta property="og:type" content="<?= htmlspecialchars($seo_type ?? 'website'); ?>">
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
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Sora:wght@500;600;700&display=swap" rel="stylesheet">
  <?php if (!empty($settings->custom_head_scripts)): ?><?= $settings->custom_head_scripts; ?><?php endif; ?>
  <style>
    :root {
      --mdq-font: 'Plus Jakarta Sans', ui-sans-serif, system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
      --mdq-display: 'Sora', var(--mdq-font);
      --mp-font: var(--mdq-font);
      --mp-primary: #177a68;
      --mp-primary-dark: #0f5d50;
      --mp-button: #17244f;
      --mp-button-dark: #0e1736;
      --mp-dark: #17244f;
      --mp-gray: #69738d;
      --mp-light-gray: #f3f6f9;
      --mp-white: #fff;
      --mp-border: #dfe5ee;
      --mp-radius: 22px;
      --mp-radius-sm: 12px;
    }
    html, body { margin: 0; min-height: 100%; }
    body.theme-pharma_mediq { background: #fff; color: #17244f; font-family: var(--mdq-font); }
    body.theme-pharma_mediq *, body.theme-pharma_mediq *::before, body.theme-pharma_mediq *::after { box-sizing: border-box; }
    body.theme-pharma_mediq a { color: inherit; }
    body.theme-pharma_mediq button, body.theme-pharma_mediq input, body.theme-pharma_mediq textarea, body.theme-pharma_mediq select { font-family: inherit; }
    body.theme-pharma_mediq h1, body.theme-pharma_mediq h2, body.theme-pharma_mediq h3, body.theme-pharma_mediq h4 { font-family: var(--mdq-display); }
    .mdq-wrap { width: min(1480px, calc(100% - 72px)); margin-inline: auto; }
    .mdq-page { min-height: 50vh; }
    body.theme-pharma_mediq .mp-section { width: min(1240px, calc(100% - 32px)); margin-inline: auto; }
    body.theme-pharma_mediq .mp-breadcrumb { display: none; }
    .mdq-toast, #toast { position: fixed; left: 50%; bottom: 26px; z-index: 1000; transform: translate(-50%, 18px); opacity: 0; pointer-events: none; background: #17244f; color: #fff; border-radius: 999px; padding: 13px 18px; font-size: 13px; box-shadow: 0 14px 38px rgba(23,36,79,.22); transition: opacity .18s, transform .18s; }
    .mdq-toast.show, #toast.show { opacity: 1; transform: translate(-50%, 0); }
    @media (max-width: 760px) { .mdq-wrap { width: min(100% - 32px, 680px); } }
  </style>
  <script>
    const STORE_SLUG = <?= json_encode((string)($settings->store_slug ?? '')); ?>;
    const STORE_ID = <?= (int)($settings->store_id ?? 0); ?>;
    const CURRENCY = <?= json_encode(html_entity_decode((string)$currencySymbol, ENT_QUOTES, 'UTF-8')); ?>;
    const CURRENCY_PLACE = <?= json_encode((string)$currencyPlacement); ?>;
    window.SF_CURRENCY = <?= json_encode((string)($store_currency['code'] ?? 'NGN')); ?>;
    function formatMoney(amount){
      const formatted = Number(amount || 0).toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
      return CURRENCY_PLACE === 'Right' ? formatted + ' ' + CURRENCY : CURRENCY + ' ' + formatted;
    }
    function sfCartUnitTotal(item){ return (Number(item.price)||0) + (Array.isArray(item.addons) ? item.addons.reduce((sum, addon) => sum + ((Number(addon.price)||0) * (Number(addon.qty)||0)), 0) : 0); }
    function sfCartLineTotal(item){ return sfCartUnitTotal(item) * (Number(item.qty)||0); }
    function showToast(msg){ const toast = document.getElementById('toast'); if (!toast) return; toast.textContent = msg; toast.classList.add('show'); window.setTimeout(() => toast.classList.remove('show'), 2500); }
  </script>
</head>
<body class="theme-pharma_mediq">
<?php $this->load->view('themes/pharma_mediq/header'); ?>
<?= $content; ?>
<?php if (!empty($product_addons) || !empty($service_addons)): ?>
  <?php $this->load->view('themes/shared/product_addons', [
    'addons' => $product_addons ?? $service_addons,
    'parent_type' => !empty($product_addons) ? 'product' : 'service',
    'parent_id' => !empty($product_addons) ? (int)$product->id : (int)$service->id,
  ]); ?>
<?php endif; ?>
<?php if (!empty($product_reviews_enabled)): ?>
  <?php $this->load->view('themes/shared/product_reviews', [
    'reviews' => $product_reviews ?? [],
    'aggregate' => $product_review_aggregate ?? ['count' => 0, 'average' => null],
    'post_url' => $product_review_post_url ?? '',
    'csrf_name' => $review_csrf_name ?? '',
    'csrf_hash' => $review_csrf_hash ?? '',
    'product' => $product ?? null,
  ]); ?>
<?php endif; ?>
<?php $this->load->view('themes/pharma_mediq/footer'); ?>
<?php $this->load->view('themes/shared/scripts'); ?>
<?php if (!empty($seo_jsonld)): ?>
<script type="application/ld+json"><?= json_encode($seo_jsonld, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
<?php else: ?>
<script type="application/ld+json"><?= json_encode([
  '@context' => 'https://schema.org', '@type' => 'Organization', 'name' => $storeName,
  'url' => base_url('store/' . ($settings->store_slug ?? '')), 'logo' => $logo_url ?? '',
  'description' => $settings->meta_description ?? ($settings->store_description ?? ''),
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
<?php endif; ?>
<?php $this->load->view('themes/shared/product_image_fallbacks'); ?>
<?php if ($consentRequired && !empty($settings->google_analytics_id . $settings->facebook_pixel_id . $settings->tiktok_pixel_id)): ?>
<div id="mp-consent-bar" style="display:none;position:fixed;inset:auto 0 0;z-index:1100;background:#17244f;color:#fff;padding:14px 18px;font-size:13px;">
  <div style="max-width:1480px;margin:auto;display:flex;gap:16px;align-items:center;justify-content:space-between;flex-wrap:wrap;">
    <span>We use analytics tools to improve your shopping experience.</span>
    <span style="display:flex;gap:8px"><button type="button" onclick="mpTrackConsent.decline()" style="background:transparent;color:#fff;border:1px solid #8190bd;padding:8px 14px;border-radius:99px">Decline</button><button type="button" onclick="mpTrackConsent.grant()" style="background:#a7eb66;color:#17244f;border:0;padding:8px 14px;border-radius:99px;font-weight:700">Accept</button></span>
  </div>
</div>
<script>(function(){try{if(localStorage.getItem(<?= json_encode($consentKey); ?>)===null)document.getElementById('mp-consent-bar').style.display='block';}catch(e){document.getElementById('mp-consent-bar').style.display='block';}})();</script>
<?php endif; ?>
</body>
</html>
