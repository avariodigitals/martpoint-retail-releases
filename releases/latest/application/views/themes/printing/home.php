<?php
$slug = (string)($settings->store_slug ?? '');
$business = (string)($store->store_name ?? '');
$primary = htmlspecialchars($settings->primary_color ?: ($theme->default_primary_color ?? '#24563b'), ENT_QUOTES, 'UTF-8');
$secondary = htmlspecialchars($settings->secondary_color ?: ($theme->default_secondary_color ?? '#d6b26c'), ENT_QUOTES, 'UTF-8');
$headline = trim((string)($settings->store_headline ?? '')) ?: 'Put your ideas in print.';
$intro = trim((string)($settings->store_subheadline ?? '')) ?: trim((string)($settings->store_description ?? ''));
$services = $featured_services ?? [];
$products = $all_products ?? [];
$printCategories = $print_categories ?? [];
$printProductCategories = [];
foreach ($printCategories as $category) {
    $printProductCategories[mb_strtolower(trim((string)$category->name))] = (int)$category->id;
}
$products = array_values(array_filter($products, function($product) use ($printProductCategories) {
    $categoryName = mb_strtolower(trim((string)($product->category_name ?? '')));
    return $categoryName !== '' && isset($printProductCategories[$categoryName]);
}));
$catalogueFilters = [];
foreach ($services as $service) {
    $id = (int)($service->category_id ?? 0);
    if ($id > 0 && !empty($service->category_name)) $catalogueFilters['service-' . $id] = $service->category_name;
}
foreach ($products as $product) {
    $id = (int)($product->category_id ?? 0);
    if ($id > 0 && !empty($product->category_name)) $catalogueFilters['product-' . $id] = $product->category_name;
}
foreach ($printCategories as $category) {
    $catalogueFilters['print-' . (int)$category->id] = $category->name;
}
$cardPrintFilters = [];
$servicePrintCategory = [];
foreach ($services as $service) {
    $serviceId = (int)($service->id ?? 0);
    $text = mb_strtolower(trim((string)($service->service_name ?? '') . ' ' . (string)($service->category_name ?? '') . ' ' . (string)($service->description ?? '')));
    $cardPrintFilters[$serviceId] = [];
    foreach ($printCategories as $category) {
        $terms = preg_split('/[^a-z0-9]+/i', mb_strtolower((string)$category->name . ' ' . (string)$category->category_key));
        foreach ($terms as $term) {
            if (strlen($term) > 2 && strpos($text, $term) !== false) {
                $cardPrintFilters[$serviceId][] = 'print-' . (int)$category->id;
                break;
            }
        }
    }
    $servicePrintCategory[$serviceId] = count($cardPrintFilters[$serviceId]) === 1
        ? (int)substr($cardPrintFilters[$serviceId][0], 6)
        : 0;
}
$currency = $store_currency ?? 'NGN';
$showPrices = !empty($settings->show_prices) || !empty($settings->show_product_prices);
$faqs = $faqs ?? [];
$csrfName = $this->security->get_csrf_token_name();
$csrfHash = $this->security->get_csrf_hash();
$themeMode = $theme_key === 'print_papercraft' ? 'atelier' : ($theme_key === 'print_neonprint' ? 'desk' : 'press');
$themeCopy = [
    'press' => ['Press & Co.', 'BIG IDEAS. BEAUTIFULLY PRINTED.', 'Your brand,\nin the real world.', 'Commercial print for everyday business and ambitious campaigns. Choose a listed service or product and tell us what your project needs.', 'Your next print project starts here.'],
    'atelier' => ['Paper Atelier', 'THOUGHTFULLY DESIGNED. CAREFULLY PRINTED.', 'For ideas\nworth keeping.', 'A considered print studio for stationery, publications, packaging and the details that make a piece feel right.', 'Print, with a point of view.'],
    'desk' => ['PrintDesk', 'YOUR PRINT PROJECT, CLEARLY SPECIFIED.', 'Find your print.\nBuild your brief.', 'Browse available print services and products, then share the specification for a clear quotation.', 'What would you like to print?'],
];
$copy = $themeCopy[$themeMode];
$specData = [];
foreach ($printCategories as $category) {
    $schema = json_decode($category->spec_schema_json ?? '[]', true);
    $specData[(string)$category->id] = is_array($schema) ? $schema : [];
}
$wa = preg_replace('/[^0-9]/', '', (string)($settings->whatsapp_number ?? ''));
$waAllowed = !empty($settings->allow_whatsapp) && $wa !== '';
$sections = $homepage_sections ?? [];
$hasSectionConfig = !empty($homepage_sections_configured);
$sectionEnabled = function($key) use ($sections, $hasSectionConfig) {
    if(!$hasSectionConfig) return true;
    if(isset($sections[$key])) return !empty($sections[$key]->is_enabled);
    if(preg_match('/_\d+$/', $key)) return false;
    foreach($sections as $sectionKey => $section){
        if(preg_replace('/_\d+$/', '', $sectionKey) === $key && !empty($section->is_enabled)) return true;
    }
    return false;
};
$showPrintServices = $sectionEnabled('featured_services');
$showPrintProducts = $sectionEnabled('featured_products');
if(!$showPrintServices) $services = [];
if(!$showPrintProducts) $products = [];
$showCatalogue = !empty($services) || !empty($products);
$showFaqs = $sectionEnabled('faqs');
$showContact = $sectionEnabled('contact_section');
$showHero = $sectionEnabled('hero_banner');
$heroBanner = $hero_banners[0] ?? null;
$bannerImageUrl = function($path) {
    $path = trim((string)$path);
    if ($path === '') return '';
    if (preg_match('#^https?://#i', $path)) return $path;
    return file_exists(FCPATH . ltrim($path, '/')) ? base_url($path) : '';
};
$heroDesktopImage = $bannerImageUrl($heroBanner->desktop_image ?? '');
$heroMobileImage = $bannerImageUrl($heroBanner->mobile_image ?? '') ?: $heroDesktopImage;
$renderImage = function($path, $alt, $class = '') {
    $path = trim((string)$path);
    $src = '';
    if (preg_match('#^https?://#i', $path)) {
        $src = $path;
    } elseif ($path !== '') {
        $root = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR;
        $relative = str_replace('\\', '/', $path);
        if (strpos($relative, str_replace('\\', '/', $root)) === 0) {
            $relative = substr($relative, strlen(str_replace('\\', '/', $root)));
        }
        $relative = ltrim(preg_replace('#^(\./)+#', '', $relative), '/');
        if (is_file($root . str_replace('/', DIRECTORY_SEPARATOR, $relative))) $src = base_url($relative);
    }
    $safeAlt = htmlspecialchars($alt, ENT_QUOTES, 'UTF-8');
    $iconPath = function_exists('mp_print_service_icon')
        ? mp_print_service_icon($alt)
        : '<path d="M7 8V3h10v5"/><rect x="4" y="8" width="16" height="8" rx="2"/><path d="M7 16h10v5H7z"/>';
    $fallback = '<div class="pr-image-fallback" role="img" aria-label="' . $safeAlt . '"><div class="pr-fallback-art"><i></i><i></i><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $iconPath . '</svg><span>PRINT WORK</span></div></div>';
    if ($src === '') return $fallback;
    return '<div class="pr-image-frame"><img class="' . htmlspecialchars($class, ENT_QUOTES, 'UTF-8') . '" src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" alt="' . $safeAlt . '" loading="lazy" onerror="this.hidden=true;this.nextElementSibling.hidden=false">' . str_replace('pr-image-fallback"', 'pr-image-fallback" hidden', $fallback) . '</div>';
};
?>
<style>
.pr-home{--pr-primary:<?= $primary ?>;--pr-accent:<?= $secondary ?>;--pr-ink:#18211c;--pr-muted:#66716a;--pr-line:#dfe5df;--pr-paper:#f7f8f4;color:var(--pr-ink)}
.pr-home *{box-sizing:border-box}.pr-wrap{max-width:1240px;margin:auto;padding:0 24px}.pr-kicker{font-size:11px;font-weight:800;letter-spacing:.12em;color:var(--pr-primary);text-transform:uppercase}.pr-navline{display:flex;align-items:center;justify-content:space-between;gap:18px;padding:18px 0;border-bottom:1px solid var(--pr-line)}.pr-brand{display:flex;align-items:center;gap:10px;font-weight:800;font-size:17px;color:var(--pr-ink)}.pr-brand img{width:auto;height:38px;max-width:160px;object-fit:contain}.pr-navlinks{display:flex;gap:22px;align-items:center;font-size:13px;font-weight:650}.pr-navlinks a:hover{color:var(--pr-primary)}.pr-button{display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:11px 18px;background:var(--pr-primary);border:1px solid var(--pr-primary);color:white!important;font-size:13px;font-weight:750;border-radius:4px;cursor:pointer}.pr-button:hover{filter:brightness(.92)}.pr-button.secondary{background:transparent;color:var(--pr-primary)!important}.pr-hero{padding:64px 0 56px;display:grid;grid-template-columns:1.05fr .95fr;gap:48px;align-items:center}.pr-hero h1{font-size:clamp(38px,4vw,58px);line-height:1.03;margin:12px 0 18px;white-space:pre-line;max-width:640px}.pr-hero p{max-width:560px;color:var(--pr-muted);font-size:16px;line-height:1.7;margin:0 0 24px}.pr-actions{display:flex;gap:10px;flex-wrap:wrap}.pr-hero-art{min-height:320px;position:relative;overflow:hidden;background:var(--pr-paper);border:1px solid var(--pr-line);display:grid;place-items:center}.pr-hero-art img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}.pr-hero-art .sheet{width:58%;aspect-ratio:1.35;background:var(--pr-primary);color:white;display:flex;flex-direction:column;justify-content:flex-end;padding:24px;box-shadow:18px 18px 0 var(--pr-accent);transform:rotate(-5deg);font-weight:800;font-size:22px}.pr-hero-art .sheet small{font-size:10px;margin-bottom:10px;letter-spacing:.12em}.pr-hero-art .swatches{position:absolute;right:10%;top:12%;display:flex;gap:6px}.pr-hero-art .swatches i{display:block;width:20px;height:42px;background:#d47a60}.pr-hero-art .swatches i:nth-child(2){background:#d6b26c}.pr-hero-art .swatches i:nth-child(3){background:#315c48}.pr-section{padding:46px 0;border-top:1px solid var(--pr-line)}.pr-heading{display:flex;justify-content:space-between;align-items:end;gap:20px;margin-bottom:22px}.pr-heading h2{font-size:30px;line-height:1.12;margin:7px 0}.pr-heading p{font-size:14px;color:var(--pr-muted);margin:0}.pr-filters{display:flex;gap:8px;overflow:auto;padding:0 0 16px}.pr-filter{white-space:nowrap;border:1px solid var(--pr-line);padding:9px 13px;background:white;border-radius:3px;color:var(--pr-ink);font-size:12px;cursor:pointer}.pr-filter[aria-pressed="true"]{background:var(--pr-ink);border-color:var(--pr-ink);color:white}.pr-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.pr-card{border:1px solid var(--pr-line);background:#fff;min-width:0}.pr-card[hidden]{display:none!important}.pr-card-media{aspect-ratio:1.55;background:var(--pr-paper);overflow:hidden}.pr-card-media img{width:100%;height:100%;object-fit:cover}.pr-image-fallback{width:100%;height:100%;min-height:150px;display:grid;place-items:center;background:linear-gradient(135deg,#edf1eb,#dfe7df);color:var(--pr-primary);font-size:42px}.pr-card-body{padding:16px}.pr-tag{font-size:10px;text-transform:uppercase;letter-spacing:.08em;color:var(--pr-primary);font-weight:800}.pr-card h3{font-size:16px;margin:8px 0 6px}.pr-card p{font-size:13px;color:var(--pr-muted);line-height:1.55;margin:0 0 12px}.pr-card-foot{display:flex;align-items:center;justify-content:space-between;gap:12px}.pr-price{font-size:13px;font-weight:800}.pr-process{background:var(--pr-ink);color:white;padding:44px}.pr-process .pr-kicker{color:var(--pr-accent)}.pr-process-grid{display:grid;grid-template-columns:.8fr 1.2fr;gap:38px;align-items:center}.pr-process h2{font-size:30px;line-height:1.15;margin:10px 0}.pr-process p{color:#d0d7d1;font-size:14px;line-height:1.7}.pr-steps{display:grid;gap:0}.pr-step{display:grid;grid-template-columns:44px 1fr;gap:12px;padding:15px 0;border-bottom:1px solid #ffffff2c}.pr-step b{font-size:14px}.pr-step p{margin:4px 0 0;font-size:12px}.pr-num{color:var(--pr-accent);font-size:15px;font-weight:800}.pr-quote{display:grid;grid-template-columns:.85fr 1.15fr;gap:42px;padding:52px 0}.pr-quote-intro h2{font-size:35px;line-height:1.1;margin:10px 0 16px}.pr-quote-intro p{font-size:14px;color:var(--pr-muted);line-height:1.7}.pr-illustration{margin:20px 0;background:var(--pr-paper);border:1px solid var(--pr-line);padding:14px}.pr-illustration svg{display:block;width:100%;height:auto}.pr-checks{padding:0;list-style:none;color:var(--pr-muted);font-size:12px;line-height:1.8}.pr-checks li{margin:4px 0}.pr-form{border:1px solid var(--pr-line);padding:22px;background:#fff}.pr-fields{display:grid;grid-template-columns:1fr 1fr;gap:12px}.pr-field{display:grid;gap:6px;min-width:0}.pr-field.full{grid-column:1/-1}.pr-field label{font-size:11px;font-weight:750;color:#3b463f}.pr-field input,.pr-field select,.pr-field textarea{width:100%;min-width:0;min-height:44px;padding:10px 11px;border:1px solid #cfd8d0;border-radius:3px;background:white;color:var(--pr-ink);font:inherit;font-size:14px}.pr-field textarea{min-height:92px;resize:vertical}.pr-fields .pr-dynamic{grid-column:1/-1;display:grid;grid-template-columns:1fr 1fr;gap:12px}.pr-notice{font-size:11px;color:var(--pr-muted);line-height:1.55;margin:12px 0}.pr-result{margin-top:12px;padding:12px;border:1px solid #a6d6b4;background:#f0faf2;color:#205a30;font-size:13px}.pr-result.error{border-color:#e7b6ae;background:#fff5f3;color:#9b2b1e}.pr-faq{max-width:800px}.pr-faq details{border-bottom:1px solid var(--pr-line);padding:15px 0}.pr-faq summary{font-weight:700;cursor:pointer}.pr-faq details p{font-size:13px;color:var(--pr-muted);line-height:1.65}.pr-contact{background:var(--pr-paper);padding:22px;display:flex;gap:24px;flex-wrap:wrap;font-size:13px}.pr-footer{background:#18211c;color:#e7ece8;padding:30px 0;margin-top:24px}.pr-footer-grid{display:flex;justify-content:space-between;gap:20px;flex-wrap:wrap;font-size:12px;color:#c2cbc4}.pr-footer strong{font-size:16px;color:white}
.pr-field select.pr-native-select{display:none}.pr-select-wrap{position:relative;width:100%}.pr-select-trigger{width:100%;min-height:44px;padding:10px 11px;border:1px solid #cfd8d0;border-radius:3px;background:#fff;color:var(--pr-ink);font:inherit;font-size:14px;text-align:left;cursor:pointer}.pr-select-trigger::after{content:'+';float:right;color:var(--pr-muted)}.pr-select-wrap.open .pr-select-trigger::after{content:'−'}.pr-select-options{display:none;max-height:220px;overflow-y:auto;border:1px solid #cfd8d0;border-top:0;background:#fff}.pr-select-wrap.open .pr-select-options{display:grid}.pr-select-option{min-height:40px;padding:9px 11px;border:0;border-bottom:1px solid var(--pr-line);background:#fff;text-align:left;font:inherit;font-size:13px;color:var(--pr-ink);cursor:pointer}.pr-select-option[aria-selected="true"],.pr-select-option:hover{background:var(--pr-paper)}
@media(max-width:800px){.pr-wrap{padding:0 16px}.pr-navlinks{gap:14px}.pr-navlinks>a:not(.pr-button){display:none}.pr-hero{grid-template-columns:1fr;gap:24px;padding:38px 0}.pr-hero h1{font-size:40px}.pr-hero-art{min-height:240px}.pr-grid{grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}.pr-card-body{padding:12px}.pr-process{padding:28px 20px}.pr-process-grid,.pr-quote{grid-template-columns:1fr;gap:22px}.pr-quote{padding:34px 0}.pr-heading{align-items:start;flex-direction:column}.pr-fields,.pr-fields .pr-dynamic{grid-template-columns:1fr}.pr-field.full,.pr-fields .pr-dynamic{grid-column:1}.pr-form{padding:16px}}
@media(max-width:480px){.pr-grid{grid-template-columns:1fr}.pr-card{display:grid;grid-template-columns:36% 1fr}.pr-card-media{height:100%;min-height:150px;aspect-ratio:auto}.pr-card-body{padding:12px}.pr-card-foot{align-items:start;flex-direction:column}.pr-navline{gap:8px}.pr-brand{font-size:14px}.pr-brand img{height:32px;max-width:100px}.pr-navlinks .pr-button{padding:9px 10px;font-size:11px}.pr-hero h1{font-size:36px}.pr-hero-art{min-height:210px}}
.pr-atelier .pr-hero h1,.pr-atelier .pr-quote-intro h2{font-family:Georgia,'Times New Roman',serif;font-weight:500}.pr-atelier .pr-hero-art{background:#f4eee6}.pr-atelier .pr-card{box-shadow:0 5px 16px rgba(37,42,36,.035)}
.pr-desk .pr-hero{padding-top:42px;padding-bottom:42px}.pr-desk .pr-hero-art{min-height:270px;border-top:5px solid var(--pr-primary)}.pr-desk .pr-card-foot .pr-button{font-size:12px}
</style>
<style>.pr-home .pr-hero h1{font-size:48px}@media(max-width:800px){.pr-home .pr-hero h1{font-size:40px}}</style>
<style>
.pr-image-frame{position:relative;width:100%;height:100%;min-height:150px}
.pr-image-frame>img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover}
.pr-image-fallback{width:100%;height:100%;min-height:150px;display:grid;place-items:center;background:linear-gradient(135deg,#edf1eb,#dfe7df);color:var(--pr-primary)}
.pr-image-fallback[hidden]{display:none!important}
.pr-fallback-art{position:relative;width:150px;height:126px;display:grid;place-items:center}
.pr-fallback-art i{position:absolute;width:88px;height:108px;left:36px;top:8px;background:#fff;border:1px solid #d4ddd4;box-shadow:0 8px 18px rgba(24,33,28,.08)}
.pr-fallback-art i:first-child{transform:translate(-16px,-7px) rotate(-8deg);background:var(--pr-accent);opacity:.72}
.pr-fallback-art i:nth-child(2){transform:translate(10px,0) rotate(7deg)}
.pr-fallback-art svg{position:relative;z-index:1;width:58px;height:58px;padding:10px;background:#fff;border:1px solid #dfe5df}
.pr-fallback-art span{position:absolute;z-index:2;bottom:4px;left:0;right:0;text-align:center;font-size:9px;font-weight:800;letter-spacing:.12em;color:var(--pr-primary)}
.pr-filter[aria-pressed="true"]{background:var(--pr-primary);border-color:var(--pr-primary);color:#fff}
.pr-process{background:var(--pr-primary)}
.pr-process .pr-button{background:#fff;border-color:#fff;color:var(--pr-primary)!important}
.pr-process .pr-button:hover{filter:brightness(.94)}
.pr-home .pr-hero-art .swatches i{background:var(--pr-primary)}
.pr-home .pr-hero-art .swatches i:nth-child(2){background:var(--pr-accent)}
.pr-home .pr-hero-art .swatches i:nth-child(3){background:#fff}
</style>
<div class="pr-home pr-<?= $themeMode ?>">
  <div class="pr-wrap">
    <nav class="pr-navline" aria-label="Store navigation">
      <a class="pr-brand" href="<?= base_url('store/' . rawurlencode($slug)) ?>"><?php if(!empty($logo_url)): ?><img src="<?= htmlspecialchars($logo_url, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($business, ENT_QUOTES, 'UTF-8') ?> logo"><?php endif; ?><span><?= htmlspecialchars($business ?: 'Print shop') ?></span></a>
      <div class="pr-navlinks"><a href="#products">Print services</a><a href="#process">How it works</a><?php if($showFaqs && $faqs): ?><a href="#faq">FAQs</a><?php endif; ?><a class="pr-button" href="#quote">Request a quote</a></div>
    </nav>
    <main>
      <div class="pr-builder-slots" id="pr-builder-slots">
      <?php if($showHero): ?><div class="pr-builder-slot" data-homepage-section="hero_banner"><section class="pr-hero">
        <div><div class="pr-kicker"><?= htmlspecialchars($copy[1]) ?></div><h1><?= nl2br(htmlspecialchars($settings->store_headline ?: $copy[2])) ?></h1><p><?= htmlspecialchars($intro ?: $copy[3]) ?></p><div class="pr-actions"><a class="pr-button" href="#products">Explore print options</a><a class="pr-button secondary" href="#quote">Build your brief</a></div></div>
        <div class="pr-hero-art" role="img" aria-label="<?= htmlspecialchars($heroBanner->banner_title ?? 'Print project illustration') ?>"><?php if($heroDesktopImage): ?><picture><?php if($heroMobileImage): ?><source media="(max-width: 600px)" srcset="<?= htmlspecialchars($heroMobileImage, ENT_QUOTES, 'UTF-8') ?>"><?php endif; ?><img src="<?= htmlspecialchars($heroDesktopImage, ENT_QUOTES, 'UTF-8') ?>" alt="<?= htmlspecialchars($heroBanner->banner_title ?? ($business . ' printing services'), ENT_QUOTES, 'UTF-8') ?>"></picture><?php else: ?><div class="sheet"><small><?= htmlspecialchars($business ?: 'PRINT PROJECT') ?></small><?= htmlspecialchars($copy[4]) ?></div><div class="swatches" aria-hidden="true"><i></i><i></i><i></i></div><?php endif; ?></div>
      </section></div><?php endif; ?>
      <?php
      // These shared sections used to be skipped by the custom print homepage.
      // Render them in the builder's saved order while keeping the print-specific
      // catalogue and quote journey below intact.
      $printSharedSections = [
        'trust_badges' => 'trust_badges.php',
        'promo_banner' => 'promo.php',
        'featured_categories' => 'featured_categories.php',
        'best_sellers' => 'best_sellers.php',
        'new_arrivals' => 'new_arrivals.php',
        'store_info' => 'store_info.php',
        'whatsapp_cta' => 'whatsapp_cta.php',
        'newsletter' => 'newsletter.php',
        'store_hours' => 'store_hours.php',
        'brands' => 'brands.php',
        'testimonials' => 'testimonials.php',
        'instagram_gallery' => 'instagram.php',
      ];
      foreach($sections as $sectionKey => $section){
        if(empty($section->is_enabled)) continue;
        $baseSectionKey = preg_replace('/_\d+$/', '', $sectionKey);
        if(isset($printSharedSections[$baseSectionKey])){
          echo '<div class="pr-builder-slot" data-homepage-section="' . htmlspecialchars($sectionKey, ENT_QUOTES, 'UTF-8') . '">';
          include(APPPATH . 'views/themes/shared/sections/' . $printSharedSections[$baseSectionKey]);
          echo '</div>';
        }
      }
      ?>
      <?php if($showCatalogue && ($services || $products)): ?>
      <?php $catalogueSlotKey = $showPrintServices ? 'featured_services' : 'featured_products'; ?>
      <div class="pr-builder-slot" data-homepage-section="<?= $catalogueSlotKey ?>"><section class="pr-section" id="products">
        <?php $catalogueSection = $sections['featured_services'] ?? ($sections['featured_products'] ?? null); ?>
        <div class="pr-heading"><div><div class="pr-kicker">PRINT SOMETHING THAT MATTERS</div><h2><?= htmlspecialchars(function_exists('sf_sec_title') ? sf_sec_title($catalogueSection, $themeMode === 'desk' ? 'Find the right print option.' : 'Your next big thing starts here.') : ($themeMode === 'desk' ? 'Find the right print option.' : 'Your next big thing starts here.')) ?></h2><p>Choose an available service or product, then share the details for review.</p></div></div>
        <?php if(count($catalogueFilters) > 1): ?><div class="pr-filters" role="group" aria-label="Filter print services"><?php foreach($catalogueFilters as $filterKey => $filterName): ?><button class="pr-filter" type="button" data-filter="<?= htmlspecialchars($filterKey) ?>" <?= strpos($filterKey, 'print-') === 0 ? 'data-print-category="' . (int)substr($filterKey, 6) . '"' : '' ?> aria-pressed="false"><?= htmlspecialchars($filterName) ?></button><?php endforeach; ?><button class="pr-filter" type="button" data-filter="all" aria-pressed="true">All options</button></div><?php endif; ?>
        <div class="pr-grid" id="pr-cards">
          <?php foreach($services as $s): $catId=(int)($s->category_id ?? 0); $desc=trim((string)($s->description ?? '')); $servicePrice=(float)($s->discount_price ?? 0)>0?(float)$s->discount_price:(float)($s->price ?? 0); $hasPrice=$showPrices && $servicePrice>0; ?>
          <article class="pr-card" data-category="service-<?= $catId ?>" data-print-categories="<?= htmlspecialchars(implode(' ', $cardPrintFilters[(int)$s->id] ?? [])) ?>"><div class="pr-card-media"><?= $renderImage($s->service_image ?? '', $s->service_name ?? 'Print service') ?></div><div class="pr-card-body"><span class="pr-tag"><?= htmlspecialchars($s->category_name ?? 'Print service') ?></span><h3><?= htmlspecialchars($s->service_name ?? '') ?></h3><?php if($desc): ?><p><?= htmlspecialchars($desc) ?></p><?php endif; ?><div class="pr-card-foot"><?php if($hasPrice): ?><span class="pr-price"><?= htmlspecialchars(number_format($servicePrice, 2)) ?> <?= htmlspecialchars($currency) ?></span><?php endif; ?><button class="pr-button" type="button" data-quote-service="<?= htmlspecialchars($s->service_name ?? '', ENT_QUOTES, 'UTF-8') ?>" data-category="<?= $catId ?>" data-print-category="<?= (int)($servicePrintCategory[(int)$s->id] ?? 0) ?>">Request details</button></div></div></article>
          <?php endforeach; ?>
          <?php foreach($products as $p): $catId=(int)($p->category_id ?? 0); $price=isset($p->effective_price)?$p->effective_price:($p->online_price ?: $p->sales_price); ?>
          <article class="pr-card" data-category="product-<?= $catId ?>" data-print-categories="print-<?= $printProductCategories[mb_strtolower(trim((string)($p->category_name ?? '')))] ?>"><div class="pr-card-media"><?= $renderImage($p->item_image ?? '', $p->item_name ?? 'Print product') ?></div><div class="pr-card-body"><span class="pr-tag"><?= htmlspecialchars($p->category_name ?? 'Product') ?></span><h3><?= htmlspecialchars($p->item_name ?? '') ?></h3><?php if(!empty($p->description)): ?><p><?= htmlspecialchars($p->description) ?></p><?php endif; ?><div class="pr-card-foot"><?php if($showPrices && (float)$price>0): ?><span class="pr-price"><?= htmlspecialchars(number_format((float)$price, 2)) ?> <?= htmlspecialchars($currency) ?></span><?php endif; ?><a class="pr-button" href="<?= base_url('store/' . rawurlencode($slug) . '/product/' . (int)$p->id) ?>">View product</a></div></div></article>
          <?php endforeach; ?>
        </div>
      </section></div>
      <?php endif; ?>
      </div>
      <section class="pr-section" id="process"><div class="pr-process"><div class="pr-process-grid"><div><div class="pr-kicker">A CLEAR PATH TO PRINT</div><h2>Good printing starts with the right details.</h2><p>We review your brief, confirm the specification and provide a quotation. Sending a brief does not authorize production or approve artwork.</p><a class="pr-button" href="#quote">Plan your project</a></div><div class="pr-steps"><div class="pr-step"><span class="pr-num">01</span><div><b>Tell us what you need</b><p>Choose an available option and share a brief or reference.</p></div></div><div class="pr-step"><span class="pr-num">02</span><div><b>Review your quotation</b><p>Confirm specification, charges and payment terms with the business.</p></div></div><div class="pr-step"><span class="pr-num">03</span><div><b>Artwork and production approvals</b><p>Any required artwork approval, payment clearance and production authorization are handled separately.</p></div></div><div class="pr-step"><span class="pr-num">04</span><div><b>Agree fulfilment details</b><p>Collection or delivery arrangements are confirmed with your quotation.</p></div></div></div></div></div></section>
      <section class="pr-section pr-quote" id="quote">
        <div class="pr-quote-intro"><div class="pr-kicker">YOUR PROJECT, YOUR SPECIFICATION</div><h2>Your Project,<br>Your Specification.</h2><p>Share the details you know. The print business can confirm material, finish, price and timing during quotation review.</p><p><strong>Have artwork?</strong> Attach a reference.<br><strong>Need design?</strong> Tell us what support you are looking for.</p><div class="pr-illustration"><svg viewBox="0 0 440 230" role="img" aria-label="A printed booklet, business card and colour swatches"><ellipse cx="220" cy="209" rx="169" ry="13" fill="black" opacity=".06"/><g transform="translate(65 18) rotate(-9 85 85)"><rect x="5" y="5" width="165" height="180" rx="3" fill="#d6b26c"/><rect width="165" height="180" rx="3" fill="var(--pr-primary)"/><text x="19" y="33" font-family="Arial" font-size="10" fill="white" letter-spacing="2">A NEW PROJECT</text><text x="18" y="83" font-family="Arial" font-size="29" font-weight="900" fill="white">IN GOOD</text><text x="18" y="119" font-family="Arial" font-size="29" font-weight="900" fill="white">PRINT.</text><path d="M19 146H143 M19 154H105" stroke="white"/></g><g transform="translate(225 87) rotate(10)"><rect width="155" height="95" rx="4" fill="white" stroke="#cfd8d0"/><text x="15" y="38" font-family="Arial" font-size="16" font-weight="bold" fill="var(--pr-primary)">MAKE YOUR MARK.</text><text x="15" y="66" font-family="Arial" font-size="10" fill="#66716a">PAPER · COLOUR · FINISH</text></g><g transform="translate(257 22)"><rect width="30" height="46" rx="3" fill="#db7255"/><rect x="36" width="30" height="46" rx="3" fill="#d6b26c"/><rect x="72" width="30" height="46" rx="3" fill="#315c48"/></g></svg><h3>A little detail. A better result.</h3><ul class="pr-checks"><li>Tell us quantity, size and intended use.</li><li>Share artwork or a reference you like.</li><li>Ask for guidance if you are unsure about materials.</li></ul></div></div>
        <form class="pr-form" id="pr-brief" enctype="multipart/form-data" novalidate>
          <input type="text" name="website" tabindex="-1" autocomplete="off" aria-hidden="true" style="position:absolute;left:-9999px;height:0;opacity:0">
          <div class="pr-fields">
            <div class="pr-field"><label for="pr-name">Your name *</label><input id="pr-name" name="name" autocomplete="name" required></div>
            <div class="pr-field"><label for="pr-phone">Phone / WhatsApp *</label><input id="pr-phone" name="phone" type="tel" autocomplete="tel" required></div>
            <div class="pr-field"><label for="pr-email">Email</label><input id="pr-email" name="email" type="email" autocomplete="email"></div>
            <div class="pr-field"><label for="pr-service">Service or product *</label><select id="pr-service" name="service" required><option value="">Choose an available option</option><?php foreach($services as $s): ?><option value="<?= htmlspecialchars($s->service_name, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($s->service_name) ?></option><?php endforeach; ?><?php foreach($products as $p): ?><option value="<?= htmlspecialchars($p->item_name, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($p->item_name) ?></option><?php endforeach; ?><option value="Custom project">Custom project</option></select></div>
            <?php if($printCategories): ?><div class="pr-field"><label for="pr-category">Print category</label><select id="pr-category" name="print_category_id"><option value="">Choose a category for specifications</option><?php foreach($printCategories as $cat): ?><option value="<?= (int)$cat->id ?>"><?= htmlspecialchars($cat->name) ?></option><?php endforeach; ?></select></div><?php endif; ?>
            <div class="pr-field"><label for="pr-qty">Quantity *</label><input id="pr-qty" name="qty" type="number" min="1" step="1" required></div>
            <div class="pr-field"><label for="pr-date">Requested date</label><input id="pr-date" name="needed_by" type="date" min="<?= date('Y-m-d') ?>"></div>
            <div class="pr-dynamic" id="pr-specfields"></div>
            <div class="pr-field"><label for="pr-design">Design needs</label><select id="pr-design" name="design_needs"><option value="supplied">I have artwork ready</option><option value="new_design">I need design support</option><option value="revise">I need existing artwork adjusted</option><option value="unsure">I am not sure yet</option></select></div>
            <div class="pr-field"><label for="pr-fulfilment">Fulfilment preference</label><select id="pr-fulfilment" name="fulfilment"><option value="collection">Collection</option><option value="delivery">Delivery (arrangement to be confirmed)</option><option value="undecided">To be discussed</option></select></div>
            <div class="pr-field full"><label for="pr-details">Project brief</label><textarea id="pr-details" name="details" placeholder="Material, finish, colours, print positions or other notes"></textarea></div>
            <div class="pr-field full"><label for="pr-file">Artwork or reference file</label><input id="pr-file" name="attachment" type="file" accept=".pdf,.png,.jpg,.jpeg,.svg,.ai,.eps,.psd,.tif,.tiff"></div>
          </div>
          <p class="pr-notice">Prices, production suitability and timing are confirmed after review. A submitted brief is a request for quotation only; it does not verify payment, approve artwork or authorize production.</p>
          <div class="pr-actions"><button class="pr-button" type="submit" id="pr-submit">Send quote request</button><?php if($waAllowed): ?><button class="pr-button secondary" type="button" id="pr-whatsapp">Continue on WhatsApp</button><?php endif; ?></div>
          <div id="pr-result" class="pr-result" role="status" aria-live="polite" hidden></div>
        </form>
      </section>
      <?php if($showFaqs && $faqs): $faqSection = $sections['faqs'] ?? null; ?><div class="pr-builder-slot" data-homepage-section="faqs"><section class="pr-section pr-faq" id="faq"><div class="pr-kicker">BEFORE YOU PRINT</div><h2><?= htmlspecialchars(function_exists('sf_sec_title') ? sf_sec_title($faqSection, 'Useful answers.') : 'Useful answers.') ?></h2><?php foreach($faqs as $faq): ?><details><summary><?= htmlspecialchars($faq->question ?? '') ?></summary><p><?= nl2br(htmlspecialchars($faq->answer ?? '')) ?></p></details><?php endforeach; ?></section></div><?php endif; ?>
      <?php if($showContact && (!empty($settings->store_phone)||!empty($settings->store_email)||!empty($settings->store_address)||!empty($business_hours))): $contactSection = $sections['contact_section'] ?? null; ?><div class="pr-builder-slot" data-homepage-section="contact_section"><section class="pr-section"><div class="pr-heading"><div><div class="pr-kicker">CONTACT</div><h2><?= htmlspecialchars(function_exists('sf_sec_title') ? sf_sec_title($contactSection, $business ?: 'Get in touch') : ($business ?: 'Get in touch')) ?></h2></div></div><div class="pr-contact"><?php if(!empty($settings->store_phone)): ?><a href="tel:<?= htmlspecialchars(preg_replace('/[^0-9+]/','',$settings->store_phone)) ?>"><?= htmlspecialchars($settings->store_phone) ?></a><?php endif; ?><?php if(!empty($settings->store_email)): ?><a href="mailto:<?= htmlspecialchars($settings->store_email) ?>"><?= htmlspecialchars($settings->store_email) ?></a><?php endif; ?><?php if(!empty($settings->store_address)): ?><span><?= nl2br(htmlspecialchars($settings->store_address)) ?></span><?php endif; ?><?php if(!empty($business_hours)): ?><span><?= htmlspecialchars(implode(' · ', $business_hours)) ?></span><?php endif; ?></div></section></div><?php endif; ?>
    </main>
  </div>
</div>
<style>.pr-builder-slots,.pr-builder-slot{display:contents}</style>
<script>
(function(){
  var container=document.getElementById('pr-builder-slots');
  if(!container)return;
  var order=<?= json_encode(array_keys($sections), JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>;
  var slots=Array.prototype.slice.call(document.querySelectorAll('[data-homepage-section]'));
  order.forEach(function(key){
    var slot=slots.find(function(item){return item.getAttribute('data-homepage-section')===key;});
    if(slot){container.appendChild(slot);slots=slots.filter(function(item){return item!==slot;});}
  });
})();
</script>
<script>
(function(){
 var schemas=<?= json_encode($specData, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_AMP|JSON_HEX_QUOT) ?>, form=document.getElementById('pr-brief'), select=document.getElementById('pr-service'), category=document.getElementById('pr-category'), target=document.getElementById('pr-specfields'), result=document.getElementById('pr-result');
 function fieldHtml(f){var req=f.required?' required':'';var label=f.label||f.key;var id='pr-spec-'+f.key;var input='';if(f.type==='select'){input='<select name="spec['+f.key+']" id="'+id+'"'+req+'><option value="">Choose…</option>'+(f.options||[]).map(function(o){return '<option value="'+String(o).replace(/[&<>"']/g,function(c){return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]})+'">'+String(o)+'</option>'}).join('')+'</select>';}else{input='<input name="spec['+f.key+']" id="'+id+'" type="'+(f.type==='number'?'number':'text')+'"'+(f.placeholder?' placeholder="'+String(f.placeholder).replace(/"/g,'&quot;')+'"':'')+req+'>';}return '<div class="pr-field"><label for="'+id+'">'+label+(f.required?' *':'')+'</label>'+input+'</div>';}
 function enhanceSelects(root){(root||form).querySelectorAll('.pr-field select').forEach(function(sel){if(sel.dataset.enhanced)return;sel.dataset.enhanced='1';sel.classList.add('pr-native-select');var wrap=document.createElement('div');wrap.className='pr-select-wrap';var trigger=document.createElement('button');trigger.type='button';trigger.className='pr-select-trigger';trigger.setAttribute('aria-label',sel.parentNode.querySelector('label')?sel.parentNode.querySelector('label').textContent:'Choose an option');trigger.setAttribute('aria-haspopup','listbox');trigger.setAttribute('aria-expanded','false');var list=document.createElement('div');list.className='pr-select-options';list.setAttribute('role','listbox');sel.parentNode.insertBefore(wrap,sel.nextSibling);wrap.appendChild(trigger);wrap.appendChild(list);function draw(){var option=sel.options[sel.selectedIndex];trigger.textContent=option?option.text:'Choose an option';list.innerHTML='';Array.prototype.forEach.call(sel.options,function(o){var b=document.createElement('button');b.type='button';b.className='pr-select-option';b.textContent=o.text;b.setAttribute('role','option');b.setAttribute('aria-selected',o.selected?'true':'false');b.disabled=o.disabled;b.addEventListener('click',function(){sel.value=o.value;sel.dispatchEvent(new Event('change',{bubbles:true}));draw();wrap.classList.remove('open');trigger.setAttribute('aria-expanded','false')});list.appendChild(b)})}trigger.addEventListener('click',function(){var open=!wrap.classList.contains('open');document.querySelectorAll('.pr-select-wrap.open').forEach(function(w){w.classList.remove('open');var t=w.querySelector('.pr-select-trigger');if(t)t.setAttribute('aria-expanded','false')});wrap.classList.toggle('open',open);trigger.setAttribute('aria-expanded',open?'true':'false')});sel.addEventListener('change',draw);draw()})}
 function specs(){var cat=category?category.value:'';target.innerHTML=(schemas[cat]||[]).map(fieldHtml).join('');enhanceSelects(target);}
 document.querySelectorAll('[data-filter]').forEach(function(b){b.addEventListener('click',function(){var f=b.dataset.filter,printCat=b.dataset.printCategory;document.querySelectorAll('[data-filter]').forEach(function(x){x.setAttribute('aria-pressed',x===b?'true':'false')});document.querySelectorAll('.pr-card').forEach(function(c){var match=f==='all'||(printCat?(' '+(c.dataset.printCategories||'')+' ').indexOf(' print-'+printCat+' ')!==-1:c.dataset.category===f);c.hidden=!match});if(printCat&&category){category.value=printCat;specs()}})});
 document.querySelectorAll('[data-quote-service]').forEach(function(b){b.addEventListener('click',function(){select.value=b.dataset.quoteService;select.dispatchEvent(new Event('change',{bubbles:true}));if(category&&b.dataset.printCategory){category.value=b.dataset.printCategory;category.dispatchEvent(new Event('change',{bubbles:true}))}document.getElementById('quote').scrollIntoView({behavior:'smooth'});var wrap=select.nextElementSibling;if(wrap)wrap.querySelector('.pr-select-trigger').focus()})});
 if(category)category.addEventListener('change',specs);select.addEventListener('change',specs);enhanceSelects(form);specs();
 function validate(){var valid=true;form.querySelectorAll('[required]').forEach(function(el){if(el.tagName==='SELECT'&&!el.value){var wrap=el.nextElementSibling;if(wrap&&wrap.classList.contains('pr-select-wrap')){wrap.classList.add('open');wrap.querySelector('.pr-select-trigger').setAttribute('aria-expanded','true');wrap.querySelector('.pr-select-trigger').focus()}valid=false;}else if(el.tagName!=='SELECT'&&!el.checkValidity()){el.reportValidity();valid=false;}});return valid;}
 function brief(){var fd=new FormData(form), d={};fd.forEach(function(v,k){if(typeof v==='string'&&k.indexOf('spec[')!==0)d[k]=v});d.spec=Object.fromEntries(Array.from(fd.entries()).filter(function(x){return x[0].indexOf('spec[')===0}).map(function(x){return [x[0].slice(5,-1),x[1]]}));return d;}
 function show(message,error,ref){result.hidden=false;result.className='pr-result'+(error?' error':'');result.textContent=message+(ref?' Reference: '+ref:'');}
 <?php if($waAllowed): ?>
 document.getElementById('pr-whatsapp').addEventListener('click',function(){if(!validate())return;var d=brief();if(!d.name||!d.phone||!d.service||!d.qty){show('Add your name, phone, selected option and quantity before continuing.',true);return;}var lines=['Print quote request', 'Name: '+d.name,'Phone: '+d.phone,'Option: '+d.service,'Quantity: '+d.qty];if(d.email)lines.push('Email: '+d.email);if(d.needed_by)lines.push('Requested date: '+d.needed_by);if(d.design_needs)lines.push('Design: '+d.design_needs);if(d.fulfilment)lines.push('Fulfilment: '+d.fulfilment);if(Object.keys(d.spec).length)lines.push('Specifications: '+JSON.stringify(d.spec));if(d.details)lines.push('Brief: '+d.details);window.open('https://wa.me/<?= $wa ?>?text='+encodeURIComponent(lines.join('\n')),'_blank','noopener');});
 <?php endif; ?>
 form.addEventListener('submit',function(e){e.preventDefault();if(!validate())return;var btn=document.getElementById('pr-submit');btn.disabled=true;var fd=new FormData(form);fd.append('<?= $csrfName ?>','<?= $csrfHash ?>');fetch('<?= base_url('store/' . rawurlencode($slug) . '/lead') ?>',{method:'POST',body:fd,credentials:'same-origin',headers:{'X-Requested-With':'XMLHttpRequest'}}).then(function(r){return r.json()}).then(function(res){if(res.csrf_hash){var token=document.querySelector('input[name="<?= $csrfName ?>"]');if(token)token.value=res.csrf_hash}if(res.status){show(res.message||'Your quote request has been received.',false,res.reference||'');form.reset();form.querySelectorAll('select').forEach(function(s){s.dispatchEvent(new Event('change'))});specs();enhanceSelects(form)}else{show(res.message||'Please review the highlighted details and try again.',true)} }).catch(function(){show('We could not send your request right now. Please try again.',true)}).finally(function(){btn.disabled=false})});
})();
</script>
