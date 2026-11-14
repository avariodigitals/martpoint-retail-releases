<?php
/**
 * Market Fresh — homepage.
 *
 * Every word a visitor reads here comes from the merchant's own settings, the
 * homepage builder, or a real catalogue row. Nothing is invented: no delivery
 * promises, no ratings, no industry wording baked into the theme — this theme
 * serves supermarkets, butchers and frozen-food retailers alike.
 *
 * Section order and visibility belong to Online Store → Homepage Builder.
 */
$this->load->view('themes/market_fresh/_helpers');

$slug = (string)($settings->store_slug ?? '');
$cur = $store_currency ?? null;
$storeName = trim((string)($store->store_name ?? '')) ?: 'Our store';
$productsUrl = base_url('store/' . $slug . '/products');
$servicesUrl = base_url('store/' . $slug . '/services');
$contactUrl = base_url('store/' . $slug . '/contact');

$waNumber = preg_replace('/[^0-9]/', '', (string)($settings->whatsapp_number ?? ''));
$sellsProducts = !empty($sells_products);
$sellsServices = !empty($sells_services);

$ctx = [
    'slug' => $slug,
    'cur' => $cur,
    'brandLabel' => $storeName,
    'showPrices' => !empty($show_prices),
    'showStock' => (int)($settings->show_sale_stock_labels ?? 1) === 1,
    'waOn' => $waNumber !== '',
    'cartOn' => !empty($cart_enabled),
    'backorder' => !empty($settings->allow_backorder),
];

$sections = (isset($homepage_sections) && is_array($homepage_sections)) ? $homepage_sections : [];
uasort($sections, function($a, $b){ return ((int)($a->display_order ?? 0)) <=> ((int)($b->display_order ?? 0)); });

$hero = !empty($hero_banners) ? $hero_banners[0] : null;
$heroDesktopPath = trim((string)($hero->desktop_image ?? '')) !== '' ? (string)$hero->desktop_image : (string)($hero->mobile_image ?? '');
$heroMobilePath = trim((string)($hero->mobile_image ?? '')) !== '' ? (string)$hero->mobile_image : $heroDesktopPath;
$heroImg = mf_image_url($heroDesktopPath, 1400);
$heroMobileImg = mf_image_url($heroMobilePath, 900);
$cats = (isset($categories) && is_array($categories)) ? $categories : [];
$hours = (isset($business_hours) && is_array($business_hours)) ? $business_hours : [];

$mfWaIcon = '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.5 14.4c-.3-.1-1.8-.9-2-1-.3-.1-.5-.1-.7.2-.2.3-.7 1-.9 1.2-.2.2-.3.2-.6.1-.3-.2-1.3-.5-2.4-1.5-.9-.8-1.5-1.8-1.7-2.1-.2-.3 0-.5.1-.6l.5-.5c.1-.2.2-.3.3-.5.1-.2 0-.4 0-.5-.1-.2-.7-1.6-.9-2.2-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1.1 1-1.1 2.5s1.1 2.9 1.2 3.1c.2.2 2.1 3.2 5.1 4.5.7.3 1.3.5 1.7.6.7.2 1.4.2 1.9.1.6-.1 1.8-.7 2-1.4.2-.7.2-1.3.2-1.4-.1-.1-.3-.2-.6-.3m-5.4 7.4a9.9 9.9 0 0 1-5-1.4l-.4-.2-3.7 1 1-3.6-.2-.4a9.9 9.9 0 0 1-1.5-5.3C3.7 6.5 8.1 2 13.6 2c2.6 0 5.1 1 7 2.9a9.8 9.8 0 0 1 2.9 7c0 5.4-4.4 9.9-9.9 9.9m8.4-18.3A11.8 11.8 0 0 0 12 .5C5.5.5.2 5.8.2 12.4c0 2.1.5 4.1 1.6 5.9L.2 24l6.3-1.7a11.9 11.9 0 0 0 5.6 1.4c6.6 0 11.9-5.3 11.9-11.9 0-3.2-1.2-6.2-3.5-8.3"/></svg>';

/** Split a business-hours line into a day / time pair without assuming a format. */
$mfHourRow = function($line){
    $line = trim((string)$line);
    if ($line === '') return null;
    $parts = strpos($line, ':') !== false ? array_map('trim', explode(':', $line, 2)) : [$line, ''];
    return [ucfirst($parts[0]), $parts[1]];
};
?>

<?php foreach ($sections as $sectionKey => $section): ?>
<?php
    if (empty($section->is_enabled)) continue;
    // The builder suffixes duplicated sections (hero_banner_2); both must render.
    $baseKey = preg_replace('/_\d+$/', '', (string)$sectionKey);

    switch ($baseKey):

        case 'hero_banner':
            $heroKicker = trim((string)($hero->banner_subtitle ?? '')) !== '' ? trim((string)$hero->banner_subtitle) : trim((string)($settings->store_subheadline ?? ''));
            $heroTitle = trim((string)($hero->banner_title ?? '')) !== '' ? trim((string)$hero->banner_title) : trim((string)($settings->store_headline ?? ''));
            if ($heroTitle === '') $heroTitle = $storeName;
            $heroLead = trim((string)($settings->store_description ?? ''));
            $servicesOnly = $sellsServices && !$sellsProducts;
            $heroBrowseUrl = trim((string)($hero->button_url ?? '')) !== ''
                ? (string)$hero->button_url
                : ($servicesOnly ? $servicesUrl : $productsUrl);
            $heroBrowseLabel = trim((string)($hero->button_text ?? '')) !== ''
                ? trim((string)$hero->button_text)
                : ($servicesOnly ? 'Browse services' : 'Browse products');
?>
<section class="mf-hero">
  <div class="mf-wrap">
    <div class="mf-hero-in">
      <div class="mf-hero-body">
        <?php if ($heroKicker !== ''): ?>
        <p class="mf-hero-kicker"><?= mf_e($heroKicker); ?></p>
        <?php endif; ?>
        <h1 class="mf-hero-title"><?= mf_e($heroTitle); ?></h1>
        <?php if ($heroLead !== ''): ?>
        <p class="mf-hero-lead"><?= nl2br(mf_e($heroLead)); ?></p>
        <?php endif; ?>

        <div class="mf-hero-actions">
          <a class="mf-btn mf-btn--primary" href="<?= mf_e($heroBrowseUrl); ?>"><?= mf_e($heroBrowseLabel); ?></a>
        </div>
      </div>

      <?php if ($heroImg !== ''): ?>
      <figure class="mf-hero-figure">
        <picture>
          <?php if ($heroMobileImg !== ''): ?><source media="(max-width: 700px)" srcset="<?= mf_e($heroMobileImg); ?>"><?php endif; ?>
          <img src="<?= mf_e($heroImg); ?>" alt="<?= mf_e($hero->banner_title ?? $storeName); ?>" width="1400" height="1050" fetchpriority="high" decoding="async">
        </picture>
      </figure>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php
            break;

        case 'trust_badges':
            $mfBadges = json_decode((string)($settings->trust_badges_json ?? ''), true);
            if (!is_array($mfBadges) || !$mfBadges) break;
            $mfBadges = array_values(array_filter($mfBadges, function($b){
                return is_array($b) && trim((string)($b['title'] ?? '')) !== '';
            }));
            if (!$mfBadges) break;
            $mfBadges = array_slice($mfBadges, 0, 4);
?>
<section class="mf-sec mf-sec--soft">
  <div class="mf-wrap">
    <div class="mf-trust">
      <?php foreach ($mfBadges as $b):
          $icon = trim((string)($b['icon'] ?? ''));
      ?>
      <div class="mf-trust-item">
        <span class="mf-trust-ico" aria-hidden="true"><?php
            // Merchant-chosen short glyphs only; anything longer is not an icon.
            if ($icon !== '' && mb_strlen($icon) <= 8) echo mf_e($icon);
            else echo '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
        ?></span>
        <h3 class="mf-trust-title"><?= mf_e($b['title']); ?></h3>
        <?php if (trim((string)($b['desc'] ?? '')) !== ''): ?>
        <p class="mf-trust-text"><?= mf_e($b['desc']); ?></p>
        <?php endif; ?>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php
            break;

        case 'promo_banner':
            $mfPromos = array_values(array_filter((isset($promo_banners) && is_array($promo_banners)) ? $promo_banners : [], function($p){
                return is_object($p) && (trim((string)($p->banner_title ?? '')) !== '' || trim((string)($p->banner_subtitle ?? '')) !== '' || trim((string)($p->description ?? '')) !== '');
            }));
            if (!$mfPromos) break;
            $mfPromos = array_slice($mfPromos, 0, 2);
?>
<section class="mf-sec">
  <div class="mf-wrap">
    <div style="display:grid;gap:var(--mf-space);grid-template-columns:repeat(auto-fit,minmax(300px,1fr));">
      <?php foreach ($mfPromos as $promo):
          $promoImg = mf_image_url((string)($promo->desktop_image ?? $promo->mobile_image ?? ''), 1000);
          $promoUrl = trim((string)($promo->banner_url ?? '')) !== '' ? (string)$promo->banner_url : $productsUrl;
      ?>
      <div class="mf-panel mf-panel--accent">
        <div class="mf-split">
          <div class="mf-split-body">
            <?php if (trim((string)($promo->banner_subtitle ?? '')) !== ''): ?>
            <p class="mf-kicker"><?= mf_e($promo->banner_subtitle); ?></p>
            <?php endif; ?>
            <?php if (trim((string)($promo->banner_title ?? '')) !== ''): ?>
            <h2 class="mf-title"><?= mf_e($promo->banner_title); ?></h2>
            <?php endif; ?>
            <?php if (trim((string)($promo->description ?? '')) !== ''): ?>
            <p class="mf-panel-text"><?= nl2br(mf_e($promo->description)); ?></p>
            <?php endif; ?>
            <a class="mf-btn mf-btn--ghost" href="<?= mf_e($promoUrl); ?>">View offer</a>
          </div>
          <?php if ($promoImg !== ''): ?>
          <div class="mf-split-media">
            <img src="<?= mf_e($promoImg); ?>" alt="<?= mf_e($promo->banner_title ?? ''); ?>" loading="lazy" decoding="async">
          </div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php
            break;

        case 'featured_categories':
            if (count($cats) < 2) break;
?>
<section class="mf-sec">
  <div class="mf-wrap">
    <header class="mf-sec-head">
      <div>
        <p class="mf-kicker">Browse</p>
        <h2 class="mf-title"><?= mf_e(sf_sec_title($section, 'Shop by category')); ?></h2>
      </div>
      <a class="mf-sec-link" href="<?= mf_e($productsUrl); ?>">View all</a>
    </header>
    <div class="mf-cats">
      <?php foreach (array_slice($cats, 0, 12) as $cat):
          $catImg = mf_image_url((string)($cat->category_image ?? ''), 500);
          $catCount = (int)($cat->item_count ?? 0);
      ?>
      <a class="mf-cat" href="<?= mf_e($productsUrl . '?category=' . (int)$cat->id); ?>">
        <span class="mf-cat-media">
          <?php if ($catImg !== ''): ?>
          <img src="<?= mf_e($catImg); ?>" alt="" loading="lazy" decoding="async">
          <?php else: ?>
          <?php mf_art_tile($storeName); ?>
          <?php endif; ?>
        </span>
        <span class="mf-cat-body">
          <span class="mf-cat-name"><?= mf_e($cat->category_name); ?></span>
          <span class="mf-cat-count"><?= $catCount . ' ' . ($catCount === 1 ? 'item' : 'items'); ?></span>
        </span>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php
            break;

        case 'featured_products':
            if (empty($featured_products)) break;
            $fpSub = trim((string)sf_sec_sub($section, ''));
?>
<section class="mf-sec mf-sec--soft">
  <div class="mf-wrap">
    <header class="mf-sec-head">
      <div>
        <p class="mf-kicker">Selected for you</p>
        <h2 class="mf-title"><?= mf_e(sf_sec_title($section, 'Featured products')); ?></h2>
        <?php if ($fpSub !== ''): ?><p class="mf-lead"><?= mf_e($fpSub); ?></p><?php endif; ?>
      </div>
      <a class="mf-sec-link" href="<?= mf_e($productsUrl); ?>">View all</a>
    </header>
    <?php mf_card_grid($featured_products, $ctx, 'product', 8); ?>
  </div>
</section>
<?php
            break;

        case 'featured_services':
            if (empty($featured_services) || empty($settings->allow_services)) break;
?>
<section class="mf-sec">
  <div class="mf-wrap">
    <header class="mf-sec-head">
      <div>
        <p class="mf-kicker">Services</p>
        <h2 class="mf-title"><?= mf_e(sf_sec_title($section, 'What we can do for you')); ?></h2>
      </div>
      <a class="mf-sec-link" href="<?= mf_e($servicesUrl); ?>">All services</a>
    </header>
    <?php mf_card_grid($featured_services, $ctx, 'service', 4); ?>
  </div>
</section>
<?php
            break;

        case 'best_sellers':
            if (empty($best_sellers)) break;
?>
<section class="mf-sec">
  <div class="mf-wrap">
    <header class="mf-sec-head">
      <div>
        <p class="mf-kicker">Most ordered</p>
        <h2 class="mf-title"><?= mf_e(sf_sec_title($section, 'Best sellers')); ?></h2>
      </div>
      <a class="mf-sec-link" href="<?= mf_e($productsUrl); ?>">View all</a>
    </header>
    <?php mf_card_grid($best_sellers, $ctx, 'product', 8); ?>
  </div>
</section>
<?php
            break;

        case 'new_arrivals':
            if (empty($new_arrivals)) break;
?>
<section class="mf-sec mf-sec--soft">
  <div class="mf-wrap">
    <header class="mf-sec-head">
      <div>
        <p class="mf-kicker">Recently added</p>
        <h2 class="mf-title"><?= mf_e(sf_sec_title($section, 'New arrivals')); ?></h2>
      </div>
      <a class="mf-sec-link" href="<?= mf_e($productsUrl); ?>">View all</a>
    </header>
    <?php mf_card_grid($new_arrivals, $ctx, 'product', 8); ?>
  </div>
</section>
<?php
            break;

        case 'brands':
            $mfBrandNames = [];
            foreach ((isset($brands) && is_array($brands)) ? $brands : [] as $brand) {
                $name = trim((string)(is_object($brand) ? ($brand->brand_name ?? $brand->name ?? '') : ''));
                if ($name !== '') $mfBrandNames[strtolower($name)] = $name;
            }
            if (!$mfBrandNames) break;
            $mfBrandNames = array_slice(array_values($mfBrandNames), 0, 18);
?>
<section class="mf-sec">
  <div class="mf-wrap">
    <header class="mf-sec-head mf-sec-head--center">
      <div>
        <h2 class="mf-title"><?= mf_e(sf_sec_title($section, 'Brands we stock')); ?></h2>
      </div>
    </header>
    <ul class="mf-brands">
      <?php foreach ($mfBrandNames as $name): ?>
      <li class="mf-brand-chip"><?= mf_e($name); ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
<?php
            break;

        case 'testimonials':
            $mfQuotes = array_values(array_filter((isset($testimonials) && is_array($testimonials)) ? $testimonials : [], function($t){
                return is_object($t) && trim((string)($t->testimonial_text ?? $t->message ?? '')) !== '';
            }));
            if (!$mfQuotes) break;
            $mfQuotes = array_slice($mfQuotes, 0, 3);
?>
<section class="mf-sec mf-sec--soft">
  <div class="mf-wrap">
    <header class="mf-sec-head mf-sec-head--center">
      <div>
        <h2 class="mf-title"><?= mf_e(sf_sec_title($section, 'What customers say')); ?></h2>
      </div>
    </header>
    <div class="mf-quotes">
      <?php foreach ($mfQuotes as $t):
          // Stars only when the merchant actually recorded a rating.
          $rating = max(0, min(5, (int)($t->rating ?? 0)));
          $who = trim((string)($t->customer_name ?? $t->author ?? ''));
          $role = trim((string)($t->role ?? $t->customer_role ?? ''));
      ?>
      <figure class="mf-quote">
        <?php if ($rating >= 1): ?>
        <p class="mf-quote-stars" aria-label="<?= $rating; ?> out of 5"><?= str_repeat('&#9733;', $rating); ?></p>
        <?php endif; ?>
        <blockquote class="mf-quote-text"><?= nl2br(mf_e($t->testimonial_text ?? $t->message)); ?></blockquote>
        <?php if ($who !== ''): ?>
        <figcaption>
          <span class="mf-quote-who"><?= mf_e($who); ?></span>
          <?php if ($role !== ''): ?><span class="mf-quote-role"><?= mf_e($role); ?></span><?php endif; ?>
        </figcaption>
        <?php endif; ?>
      </figure>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php
            break;

        case 'instagram_gallery':
            $mfPosts = array_values(array_filter((isset($instagram_posts) && is_array($instagram_posts)) ? $instagram_posts : [], function($p){
                return is_object($p) && trim((string)($p->media_url ?? $p->image ?? '')) !== '';
            }));
            if (!$mfPosts) break;
            $mfPosts = array_slice($mfPosts, 0, 10);
?>
<section class="mf-sec">
  <div class="mf-wrap">
    <header class="mf-sec-head mf-sec-head--center">
      <div>
        <h2 class="mf-title"><?= mf_e(sf_sec_title($section, 'Follow us')); ?></h2>
      </div>
    </header>
    <div class="mf-gallery">
      <?php foreach ($mfPosts as $post):
          $media = trim((string)($post->media_url ?? $post->image));
          $link = trim((string)($post->permalink ?? $post->link ?? ''));
          $alt = trim((string)($post->caption ?? ''));
          $tile = '<img src="' . mf_e($media) . '" alt="' . mf_e($alt) . '" loading="lazy" decoding="async">';
      ?>
      <?php if ($link !== ''): ?>
      <a class="mf-gallery-item" href="<?= mf_e($link); ?>" target="_blank" rel="noopener noreferrer"><?= $tile; ?></a>
      <?php else: ?>
      <span class="mf-gallery-item"><?= $tile; ?></span>
      <?php endif; ?>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php
            break;

        case 'store_info':
            // Built only from channels this store really switched on — never a
            // generic delivery promise the merchant cannot keep.
            $mfSteps = [];
            if ($sellsProducts || $sellsServices) {
                $mfSteps[] = ['Browse and fill your basket', 'Search by name or pick a category, then add what you need. Your basket stays on this device until you send it.'];
            }
            if (!empty($cart_enabled)) {
                $mfSteps[] = ['Check out online', 'Review the basket, choose collection or delivery where this store offers it, and pay with the methods enabled here.'];
            }
            if ($waNumber !== '') {
                $mfSteps[] = ['Or send it on WhatsApp', 'One tap turns everything you picked into a message. You see the exact text before WhatsApp opens, and we reply to confirm availability and your total.'];
            }
            $aboutText = trim((string)($settings->store_description ?? ''));
            if (!$mfSteps && $aboutText === '') break;
?>
<section class="mf-sec">
  <div class="mf-wrap">
    <header class="mf-sec-head mf-sec-head--center">
      <div>
        <p class="mf-kicker">About <?= mf_e($storeName); ?></p>
        <h2 class="mf-title"><?= mf_e(sf_sec_title($section, 'How ordering works')); ?></h2>
        <?php if ($aboutText !== ''): ?><p class="mf-lead"><?= nl2br(mf_e($aboutText)); ?></p><?php endif; ?>
      </div>
    </header>
    <?php if ($mfSteps): ?>
    <ol class="mf-steps">
      <?php foreach ($mfSteps as $i => $step): ?>
      <li class="mf-step">
        <span class="mf-step-n"><?= $i + 1; ?></span>
        <h3 class="mf-step-title"><?= mf_e($step[0]); ?></h3>
        <p class="mf-step-text"><?= mf_e($step[1]); ?></p>
      </li>
      <?php endforeach; ?>
    </ol>
    <?php endif; ?>
  </div>
</section>
<?php
            break;

        case 'faqs':
            $mfFaqs = [];
            foreach ((isset($faqs) && is_array($faqs)) ? $faqs : [] as $faq) {
                if (!is_object($faq)) continue;
                $q = trim((string)($faq->question ?? $faq->faq_question ?? ''));
                $a = trim((string)($faq->answer ?? $faq->faq_answer ?? ''));
                if ($q !== '' && $a !== '') $mfFaqs[] = [$q, $a];
            }
            if (!$mfFaqs) break;
            $mfFaqs = array_slice($mfFaqs, 0, 12);
?>
<section class="mf-sec mf-sec--soft">
  <div class="mf-wrap">
    <header class="mf-sec-head mf-sec-head--center">
      <div>
        <h2 class="mf-title"><?= mf_e(sf_sec_title($section, 'Frequently asked questions')); ?></h2>
      </div>
    </header>
    <div class="mf-faq">
      <?php foreach ($mfFaqs as $i => $pair): ?>
      <div class="mf-faq-item" data-mf-faq>
        <button type="button" class="mf-faq-q" aria-expanded="false" aria-controls="mf-faq-<?= $i; ?>">
          <span><?= mf_e($pair[0]); ?></span>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
        <div class="mf-faq-a" id="mf-faq-<?= $i; ?>" role="region">
          <p><?= nl2br(mf_e($pair[1])); ?></p>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>
<?php
            break;

        case 'contact_section':
            $contactRows = [];
            $phone = trim((string)($settings->store_phone ?? ''));
            if ($phone !== '') {
                $contactRows[] = ['phone', $phone, 'tel:' . preg_replace('/[^0-9+]/', '', $phone), false];
            }
            $email = trim((string)($settings->store_email ?? ''));
            if ($email !== '') {
                $contactRows[] = ['email', $email, 'mailto:' . $email, false];
            }
            if ($waNumber !== '') {
                $contactRows[] = ['wa', trim((string)$settings->whatsapp_number), 'https://wa.me/' . $waNumber, true];
            }
            $address = trim((string)($settings->store_address ?? ''));
            if ($address !== '') {
                $mapUrl = trim((string)($settings->footer_address_url ?? ''));
                $contactRows[] = ['pin', $address, $mapUrl !== '' ? $mapUrl : '', $mapUrl !== ''];
            }
            if (!$contactRows && !$hours) break;
            $mfContactIcons = [
                'phone' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg>',
                'email' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><polyline points="22,6 12,13 2,6"/></svg>',
                'pin'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>',
            ];
?>
<section class="mf-sec">
  <div class="mf-wrap">
    <header class="mf-sec-head">
      <div>
        <p class="mf-kicker">Get in touch</p>
        <h2 class="mf-title"><?= mf_e(sf_sec_title($section, 'Visit or contact us')); ?></h2>
      </div>
      <a class="mf-sec-link" href="<?= mf_e($contactUrl); ?>">Contact form</a>
    </header>
    <div class="mf-info">
      <?php if ($contactRows): ?>
      <div class="mf-info-card">
        <h3 class="mf-info-title">Contact details</h3>
        <ul class="mf-rows">
          <?php foreach ($contactRows as $row): ?>
          <li class="mf-row">
            <span class="mf-row-ico" aria-hidden="true"><?= $row[0] === 'wa' ? $mfWaIcon : $mfContactIcons[$row[0]]; ?></span>
            <?php if ($row[2] !== ''): ?>
            <a href="<?= mf_e($row[2]); ?>"<?= $row[3] ? ' target="_blank" rel="noopener noreferrer"' : ''; ?>><?= nl2br(mf_e($row[1])); ?></a>
            <?php else: ?>
            <span><?= nl2br(mf_e($row[1])); ?></span>
            <?php endif; ?>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>

      <?php if ($hours): ?>
      <div class="mf-info-card">
        <h3 class="mf-info-title">Opening hours</h3>
        <ul class="mf-rows">
          <?php foreach ($hours as $line):
              $pair = $mfHourRow($line);
              if (!$pair) continue;
          ?>
          <li class="mf-row-between">
            <span><?= mf_e($pair[0]); ?></span>
            <span><?= $pair[1] !== '' ? mf_e($pair[1]) : '&mdash;'; ?></span>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php endif; ?>
    </div>
  </div>
</section>
<?php
            break;

        case 'whatsapp_cta':
            if ($waNumber === '') break;
            $ctaSteps = ['Add anything you like to your basket.'];
            $ctaSteps[] = !empty($cart_enabled)
                ? 'Open the basket and choose "Order on WhatsApp".'
                : 'Choose "Order on WhatsApp" on any product.';
            $ctaSteps[] = 'We reply to confirm availability and your final total.';
?>
<section class="mf-sec">
  <div class="mf-wrap">
    <div class="mf-panel mf-panel--wa">
      <div class="mf-split">
        <div class="mf-split-body">
          <p class="mf-kicker">WhatsApp ordering</p>
          <h2 class="mf-title"><?= mf_e(sf_sec_title($section, 'Prefer to order by message?')); ?></h2>
          <p class="mf-panel-text">Send your whole basket to us on WhatsApp. You see the exact message before it opens, and nothing is charged on this site.</p>
          <div class="mf-hero-actions">
            <?php if (!empty($cart_enabled)): ?>
            <button type="button" class="mf-btn mf-btn--wa" data-mf-wa="cart"><?= $mfWaIcon; ?> Send my basket</button>
            <?php endif; ?>
            <button type="button" class="mf-btn mf-btn--ghost" data-mf-wa="general">Ask a question</button>
          </div>
        </div>
        <div class="mf-split-body">
          <ol class="mf-steps mf-steps--plain">
            <?php foreach ($ctaSteps as $i => $text): ?>
            <li class="mf-step">
              <span class="mf-step-n"><?= $i + 1; ?></span>
              <p class="mf-step-text"><?= mf_e($text); ?></p>
            </li>
            <?php endforeach; ?>
          </ol>
        </div>
      </div>
    </div>
  </div>
</section>
<?php
            break;

        case 'newsletter':
            // Three sources, in the order a merchant would expect: the copy they
            // typed on this section, then the site-wide newsletter setting. Not
            // sf_sec_title(), because that would fall through to the generic
            // section label ("Newsletter") and hide the site-wide setting.
            $newsCfg = json_decode((string)($section->config_json ?? ''), true);
            $newsCfg = is_array($newsCfg) ? $newsCfg : [];
            $newsTitle = trim((string)($newsCfg['title'] ?? ''));
            if ($newsTitle === '') $newsTitle = trim((string)($settings->newsletter_title ?? ''));
            if ($newsTitle === '') $newsTitle = 'Stay in touch';
            $newsSub = trim((string)($newsCfg['subtitle'] ?? ''));
            if ($newsSub === '') $newsSub = trim((string)($settings->newsletter_subtitle ?? ''));
            if ($newsSub === '') $newsSub = 'News and updates from ' . $storeName . ', straight to your inbox.';
?>
<section class="mf-sec">
  <div class="mf-wrap">
    <div class="mf-panel mf-panel--deep">
      <div class="mf-split-body">
        <div class="mf-news">
          <h2 class="mf-title"><?= mf_e($newsTitle); ?></h2>
          <p class="mf-panel-text"><?= mf_e($newsSub); ?></p>
          <form class="mf-news-form" action="<?= mf_e(base_url('store/' . $slug . '/subscribe')); ?>" method="post" data-source="newsletter" onsubmit="return mpNewsletterSubmit(event)">
            <label class="mf-sr-only" for="mf-news-email">Email address</label>
            <input id="mf-news-email" type="email" name="email" placeholder="you@example.com" autocomplete="email" required>
            <button type="submit" class="mf-btn mf-btn--primary">Subscribe</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>
<?php
            break;

        case 'store_hours':
            if (!$hours) break;
?>
<section class="mf-sec mf-sec--soft">
  <div class="mf-wrap">
    <header class="mf-sec-head mf-sec-head--center">
      <div>
        <h2 class="mf-title"><?= mf_e(sf_sec_title($section, 'Opening hours')); ?></h2>
      </div>
    </header>
    <div class="mf-info-card" style="max-width:640px;margin:0 auto;">
      <ul class="mf-rows">
        <?php foreach ($hours as $line):
            $pair = $mfHourRow($line);
            if (!$pair) continue;
        ?>
        <li class="mf-row-between">
          <span><?= mf_e($pair[0]); ?></span>
          <span><?= $pair[1] !== '' ? mf_e($pair[1]) : '&mdash;'; ?></span>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</section>
<?php
            break;

    endswitch;
?>
<?php endforeach; ?>

<script>
(function(){
  function on(list, fn){ Array.prototype.forEach.call(list, fn); }

  /* FAQ disclosure — no native dialogs, keyboard and screen-reader friendly. */
  on(document.querySelectorAll('[data-mf-faq] .mf-faq-q'), function(btn){
    btn.addEventListener('click', function(){
      var item = btn.closest('[data-mf-faq]');
      var open = item.classList.toggle('open');
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  });

  /* Hero and WhatsApp-section entry points. */
  on(document.querySelectorAll('[data-mf-wa]'), function(btn){
    btn.addEventListener('click', function(){
      if(!window.mfWhatsApp) return;
      if(btn.getAttribute('data-mf-wa') === 'cart') mfWhatsApp.openCart();
      else mfWhatsApp.openGeneral();
    });
  });
})();
</script>
