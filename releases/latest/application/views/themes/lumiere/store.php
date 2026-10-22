<?php
require_once APPPATH . 'views/themes/lumiere/_helpers.php';
$lmSlug = $settings->store_slug ?? '';
$lmHome = base_url('store/' . $lmSlug);
$lmCurrency = $store_currency ?? null;
$lmSections = $homepage_sections ?? [];
uasort($lmSections, function($a, $b){ return (int) ($a->display_order ?? 0) <=> (int) ($b->display_order ?? 0); });
$lmPriceById = [];
foreach ($all_products ?? [] as $lmAllProduct) $lmPriceById[(int)$lmAllProduct->id] = $lmAllProduct;
foreach (['featured_products','best_sellers','new_arrivals'] as $lmListKey) {
    foreach ($$lmListKey ?? [] as $lmListedProduct) {
        if (isset($lmPriceById[(int)$lmListedProduct->id])) {
            $lmListedProduct->effective_price = $lmPriceById[(int)$lmListedProduct->id]->effective_price;
            $lmListedProduct->original_price = $lmPriceById[(int)$lmListedProduct->id]->original_price;
        }
    }
}
$lmServices = !empty($sells_services) && !empty($settings->allow_services) ? ($featured_services ?? []) : [];
$lmHero = $lmSections['hero_banner'] ?? null;
$lmHeroCfg = json_decode($lmHero->config_json ?? '', true);
if (!is_array($lmHeroCfg)) $lmHeroCfg = [];
$lmHeroImage = !empty($lmHeroCfg['image_url']) && preg_match('~^https?://[a-zA-Z0-9:/?&%._=+#-]+$~', $lmHeroCfg['image_url']) ? $lmHeroCfg['image_url'] : '';
$lmHeroBanner = ($hero_banners ?? [])[0] ?? null;
if ($lmHeroBanner) {
    $lmHeroImage = lm_banner_image($lmHeroBanner->desktop_image ?? '') ?: $lmHeroImage;
}
$lmHeroMobileImage = $lmHeroBanner ? lm_banner_image($lmHeroBanner->mobile_image ?? '') : '';
$lmReferenceHeroImage = 'https://images.unsplash.com/photo-1531123897727-8f129e1688ce?auto=format&fit=crop&w=1800&q=85';
$lmHeroImage = $lmHeroImage ?: $lmReferenceHeroImage;
$lmHeroMobileImage = $lmHeroMobileImage ?: $lmHeroImage;
$lmHeroTitle = trim((string)($lmHeroBanner->banner_title ?? '')) ?: lm_section_copy($lmHero, "Your glow.\nOur passion.");
$lmHeroSubtitle = trim((string)($lmHeroBanner->banner_subtitle ?? '')) ?: lm_section_copy($lmHero, 'Thoughtfully selected skincare essentials and personalised beauty rituals to help you feel confident in your own skin.', 'subtitle');
$lmHeroButton = trim((string)($lmHeroBanner->button_text ?? '')) ?: 'Explore Skincare';
$lmHeroButtonUrl = lm_banner_link($lmHeroBanner->button_url ?? '', $lmSlug, $lmHome . '/products');
$lmPromoBanners = $promo_banners ?? [];
$lmPromoIndex = 0;
?>
<main id="home" class="lm-home">
<?php foreach ($lmSections as $lmKey => $lmSection):
  $lmType = preg_replace('/_\d+$/', '', $lmKey);
  switch ($lmType):
  case 'hero_banner': ?>
    <section class="hero" aria-labelledby="lm-hero-title">
      <div class="hero-image" role="img" aria-label="Portrait celebrating radiant skin" style="--lm-hero-desktop:url('<?= lm_escape($lmHeroImage); ?>');--lm-hero-mobile:url('<?= lm_escape($lmHeroMobileImage); ?>')"></div>
      <div class="wrap" style="width:100%"><div class="hero-copy">
        <div class="eyebrow"><?= lm_escape(lm_section_copy($lmSection, 'THE ART OF NATURALLY BEAUTIFUL SKIN', 'eyebrow')); ?></div>
        <h1 id="lm-hero-title"><?= nl2br(lm_escape($lmHeroTitle)); ?></h1>
        <p><?= lm_escape($lmHeroSubtitle); ?></p>
        <div class="hero-buttons"><?php if (!empty($sells_products)): ?><a class="btn" href="<?= lm_escape($lmHeroButtonUrl); ?>"><?= lm_escape($lmHeroButton); ?> <span>↗</span></a><?php endif; ?><?php if (!empty($sells_services) && !empty($settings->allow_services)): ?><a class="btn btn-outline" href="<?= lm_escape($lmHome . '/services'); ?>">Book a Treatment <span>↗</span></a><?php endif; ?></div>
      </div></div><div class="badge-hero">RADIANT<br>SKIN<br>STARTS<br>HERE ✳</div>
    </section>
  <?php break;
  case 'trust_badges':
    $lmBadges = json_decode($settings->trust_badges_json ?? '', true);
    if (!is_array($lmBadges) || !$lmBadges) $lmBadges = [
      ['title'=>'Authentic Products','subtitle'=>'Curated with care'], ['title'=>'Skin-first Care','subtitle'=>'Made for your routine'],
      ['title'=>'Easy Online Shopping','subtitle'=>'Simple & secure'], ['title'=>'Made for Your Glow','subtitle'=>'Every skin journey matters']
    ];
    $lmIcons = ['<path d="M16 3l11 5v8c0 7-5 11-11 13C10 27 5 23 5 16V8l11-5Z"/><path d="m11 16 4 4 7-8"/>','<path d="M16 4c6 6 9 11 9 16a9 9 0 1 1-18 0c0-5 3-10 9-16Z"/><path d="M12 22c1 2 3 3 5 3"/>','<path d="M5 11h22v17H5zM3 11l4-7h18l4 7M12 11v17M20 11v17"/>','<path d="M16 2l4 9 10 1-7 7 2 10-9-5-9 5 2-10-7-7 10-1z"/>'];
    ?>
    <div class="wrap benefits"><?php foreach (array_slice($lmBadges, 0, 4) as $lmIndex => $lmBadge):
      $lmTitle = is_array($lmBadge) ? ($lmBadge['title'] ?? $lmBadge['label'] ?? '') : (string) $lmBadge;
      $lmSub = is_array($lmBadge) ? ($lmBadge['subtitle'] ?? $lmBadge['description'] ?? $lmBadge['desc'] ?? '') : '';
      if (trim($lmTitle) === '') $lmTitle = ['Authentic Products','Skin-first Care','Easy Online Shopping','Made for Your Glow'][$lmIndex];
      if (trim($lmSub) === '') $lmSub = ['Curated with care','Made for your routine','Simple & secure','Every skin journey matters'][$lmIndex];
      ?><div class="benefit"><svg viewBox="0 0 32 32" aria-hidden="true"><?= $lmIcons[$lmIndex]; ?></svg><div><b><?= lm_escape($lmTitle); ?></b><small><?= lm_escape($lmSub); ?></small></div></div><?php endforeach; ?></div>
  <?php break;
  case 'featured_categories': if (empty($sells_products) || (isset($lmSections['featured_products']) && !empty($featured_products)) || empty($categories)) break; ?>
    <section class="section lm-category-section"><div class="wrap"><div class="heading"><div><div class="eyebrow">THE LUMIÈRE EDIT</div><h2><?= lm_escape(lm_section_copy($lmSection, 'Explore your ritual.')); ?></h2><p><?= lm_escape(lm_section_copy($lmSection, 'Find a routine that feels like yours.', 'subtitle')); ?></p></div><a class="text-link" href="<?= lm_escape($lmHome . '/products'); ?>">Shop All Products ↗</a></div><div class="categories"><a class="chip active" href="<?= lm_escape($lmHome . '/products'); ?>">Shop All</a><?php foreach (array_slice($categories, 0, 8) as $lmCategory): ?><a class="chip" href="<?= lm_escape($lmHome . '/products?category=' . (int)$lmCategory->id); ?>"><?= lm_escape($lmCategory->category_name); ?></a><?php endforeach; ?></div></div></section>
  <?php break;
  case 'featured_products':
  case 'best_sellers':
  case 'new_arrivals':
    $lmList = $lmType === 'best_sellers' ? ($best_sellers ?? []) : ($lmType === 'new_arrivals' ? ($new_arrivals ?? []) : ($featured_products ?? []));
    if (empty($sells_products) || !$lmList) break;
    $lmDefaultTitle = $lmType === 'best_sellers' ? 'Loved for a reason.' : ($lmType === 'new_arrivals' ? 'Just arrived.' : 'Good skin begins here.');
    ?>
    <section id="<?= $lmType === 'featured_products' ? 'products' : lm_escape($lmType); ?>" class="section"><div class="wrap">
      <div class="heading"><div><div class="eyebrow">THE LUMIÈRE EDIT</div><h2><?= lm_escape(lm_section_copy($lmSection, $lmDefaultTitle)); ?></h2><p><?= lm_escape(lm_section_copy($lmSection, 'Skincare staples worth making space for.', 'subtitle')); ?></p></div><a class="text-link" href="<?= lm_escape($lmHome . '/products'); ?>">Shop All Products ↗</a></div>
      <?php if ($lmType === 'featured_products' && isset($lmSections['featured_categories']) && !empty($categories)): ?><div class="categories" aria-label="Product categories"><a class="chip active" href="<?= lm_escape($lmHome . '/products'); ?>">Shop All</a><?php foreach (array_slice($categories, 0, 6) as $lmCategory): ?><a class="chip" href="<?= lm_escape($lmHome . '/products?category=' . (int) $lmCategory->id); ?>"><?= lm_escape($lmCategory->category_name); ?></a><?php endforeach; ?></div><?php endif; ?>
      <div class="products-grid" id="productList"><?php foreach (array_slice($lmList, 0, 8) as $lmProduct) lm_product_card($lmProduct, $lmSlug, $lmCurrency, $settings); ?></div>
    </div></section>
  <?php break;
  case 'featured_services': if (!$lmServices) break; ?>
    <section id="services" class="section service-section"><div class="wrap">
      <div class="heading"><div><div class="eyebrow">BEYOND THE EVERYDAY ROUTINE</div><h2><?= lm_escape(lm_section_copy($lmSection, 'A little time for you.')); ?></h2><p><?= lm_escape(lm_section_copy($lmSection, 'Personalised treatments to restore, refresh and reconnect.', 'subtitle')); ?></p></div><a class="text-link" href="<?= lm_escape($lmHome . '/services'); ?>">Explore Treatments ↗</a></div>
      <div class="services-grid"><?php foreach (array_slice($lmServices, 0, 3) as $lmService) lm_service_card($lmService, $lmSlug, $lmCurrency); ?></div>
    </div></section>
  <?php break;
  case 'promo_banner':
    $lmPromo = $lmPromoBanners[$lmPromoIndex] ?? null; $lmPromoIndex++;
    $lmPromoCfg = json_decode($lmSection->config_json ?? '', true);
    if (!is_array($lmPromoCfg)) $lmPromoCfg = [];
    $lmPromoCustom = !empty($lmPromoCfg['title']) || !empty($lmPromoCfg['subtitle']) || !empty($lmPromoCfg['image_url']);
    if (!$lmPromo && !$lmPromoCustom) {
        break;
    }
    $lmPromoImage = $lmPromo ? lm_banner_image($lmPromo->desktop_image ?? '') : '';
    if (!$lmPromoImage && !empty($lmPromoCfg['image_url']) && preg_match('~^https?://[a-zA-Z0-9:/?&%._=+#-]+$~', $lmPromoCfg['image_url'])) $lmPromoImage = $lmPromoCfg['image_url'];
    $lmPromoTitle = trim((string)($lmPromo->banner_title ?? '')) ?: lm_section_copy($lmSection, 'A little more to love.');
    $lmPromoSubtitle = trim((string)($lmPromo->banner_subtitle ?? '')) ?: lm_section_copy($lmSection, 'Discover a moment made for you.', 'subtitle');
    $lmPromoButton = trim((string)($lmPromo->button_text ?? '')) ?: 'Explore the Edit';
    $lmPromoUrl = lm_banner_link($lmPromo->button_url ?? '', $lmSlug, $lmHome . '/products'); ?>
    <section class="lm-promo-banner"><div class="wrap"><div class="lm-promo-copy"><div class="eyebrow"><?= lm_escape(lm_section_copy($lmSection, 'THE LUMIÈRE EDIT', 'eyebrow')); ?></div><h2><?= lm_escape($lmPromoTitle); ?></h2><p><?= lm_escape($lmPromoSubtitle); ?></p><a class="btn" href="<?= lm_escape($lmPromoUrl); ?>"><?= lm_escape($lmPromoButton); ?> ↗</a></div><?php if ($lmPromoImage): ?><img src="<?= lm_escape($lmPromoImage); ?>" alt="<?= lm_escape($lmPromoTitle); ?>" loading="lazy"><?php endif; ?></div></section>
  <?php break;
  case 'store_info': lm_editorial_section($lmSection, $lmHome); break;
  case 'testimonials': if (empty($testimonials)) break; ?>
    <section id="reviews" class="section"><div class="wrap"><div class="heading"><div><div class="eyebrow">THE LUMIÈRE EXPERIENCE</div><h2><?= lm_escape(lm_section_copy($lmSection, 'Your skin, your story.')); ?></h2><p><?= lm_escape(lm_section_copy($lmSection, 'Kind words from our customers.', 'subtitle')); ?></p></div></div><div class="testimonials"><?php foreach (array_slice($testimonials, 0, 3) as $lmReview): ?><div class="quote"><div class="stars">✧ ✧ ✧ ✧ ✧</div><p>“<?= lm_escape($lmReview->testimonial_text ?? $lmReview->message ?? ''); ?>”</p><b><?= lm_escape($lmReview->customer_name ?? $lmReview->author ?? 'Customer'); ?></b></div><?php endforeach; ?></div></div></section>
  <?php break;
  case 'brands': if (empty($brands)) break; ?>
    <section class="section lm-extra"><div class="wrap"><div class="heading"><div><div class="eyebrow">CURATED WITH CARE</div><h2><?= lm_escape(lm_section_copy($lmSection, 'Brands we love.')); ?></h2><p><?= lm_escape(lm_section_copy($lmSection, 'Thoughtful names for thoughtful care.', 'subtitle')); ?></p></div></div><div class="lm-brand-grid"><?php foreach (array_slice($brands, 0, 8) as $lmBrand): ?><div><?= lm_escape($lmBrand->brand_name ?? $lmBrand->name ?? ''); ?></div><?php endforeach; ?></div></div></section>
  <?php break;
  case 'instagram_gallery': if (empty($instagram_posts)) break; ?>
    <section class="section lm-extra"><div class="wrap"><div class="heading"><div><div class="eyebrow">GLOW JOURNAL</div><h2><?= lm_escape(lm_section_copy($lmSection, 'A little inspiration.')); ?></h2><p><?= lm_escape(lm_section_copy($lmSection, 'Moments from our community.', 'subtitle')); ?></p></div></div><div class="lm-journal-grid"><?php foreach (array_slice($instagram_posts, 0, 4) as $lmPost): $lmPostImage = $lmPost->media_url ?? $lmPost->image ?? ''; ?><a href="<?= lm_escape($lmPost->permalink ?? $lmPost->link ?? '#'); ?>" target="_blank" rel="noopener"><?php if ($lmPostImage): ?><img src="<?= lm_escape($lmPostImage); ?>" alt="<?= lm_escape($lmPost->caption ?? $lmPost->title ?? 'Journal image'); ?>" loading="lazy"><?php endif; ?><span><?= lm_escape(mb_strimwidth($lmPost->caption ?? $lmPost->title ?? 'View moment', 0, 60, '…', 'UTF-8')); ?></span></a><?php endforeach; ?></div></div></section>
  <?php break;
  case 'faqs': if (empty($faqs)) break; ?>
    <section class="section lm-extra lm-faq"><div class="wrap"><div class="heading"><div><div class="eyebrow">HERE TO HELP</div><h2><?= lm_escape(lm_section_copy($lmSection, 'Good to know.')); ?></h2><p><?= lm_escape(lm_section_copy($lmSection, 'Answers to the things you ask most.', 'subtitle')); ?></p></div></div><div class="lm-faq-list"><?php foreach ($faqs as $lmFaq): ?><details><summary><?= lm_escape($lmFaq->question ?? $lmFaq->faq_question ?? ''); ?></summary><p><?= nl2br(lm_escape($lmFaq->answer ?? $lmFaq->faq_answer ?? '')); ?></p></details><?php endforeach; ?></div></div></section>
  <?php break;
  case 'contact_section': if (empty($settings->store_phone) && empty($settings->store_email) && empty($settings->store_address)) break; ?>
    <section class="section lm-extra lm-contact"><div class="wrap"><div class="heading"><div><div class="eyebrow">SAY HELLO</div><h2><?= lm_escape(lm_section_copy($lmSection, 'We are here for you.')); ?></h2><p><?= lm_escape(lm_section_copy($lmSection, 'Questions about care, products or appointments? Get in touch.', 'subtitle')); ?></p></div></div><div class="lm-contact-grid"><?php if (!empty($settings->store_phone)): ?><a href="tel:<?= lm_escape(preg_replace('/[^0-9+]/', '', $settings->store_phone)); ?>"><small>CALL US</small><b><?= lm_escape($settings->store_phone); ?></b></a><?php endif; ?><?php if (!empty($settings->store_email)): ?><a href="mailto:<?= lm_escape($settings->store_email); ?>"><small>EMAIL US</small><b><?= lm_escape($settings->store_email); ?></b></a><?php endif; ?><?php if (!empty($settings->store_address)): ?><div><small>VISIT US</small><b><?= lm_escape($settings->store_address); ?></b></div><?php endif; ?></div></div></section>
  <?php break;
  case 'whatsapp_cta': $lmWa = preg_replace('/[^0-9]/', '', (string)($settings->whatsapp_number ?? '')); if (!$lmWa) break; ?>
    <section class="lm-whatsapp-cta"><div class="wrap"><div class="eyebrow">PERSONAL CARE</div><h2><?= lm_escape(lm_section_copy($lmSection, 'A little help finding your glow?')); ?></h2><p><?= lm_escape(lm_section_copy($lmSection, 'Speak with our team about products and treatments.', 'subtitle')); ?></p><a class="btn" href="https://wa.me/<?= lm_escape($lmWa); ?>" target="_blank" rel="noopener">Chat with us ↗</a></div></section>
  <?php break;
  case 'store_hours': if (empty($business_hours)) break; ?>
    <section class="section lm-extra lm-hours"><div class="wrap"><div class="heading"><div><div class="eyebrow">COME BY</div><h2><?= lm_escape(lm_section_copy($lmSection, 'Opening hours.')); ?></h2><p><?= lm_escape(lm_section_copy($lmSection, '', 'subtitle')); ?></p></div></div><div class="lm-hours-list"><?php foreach ($business_hours as $lmHour): ?><div><?= lm_escape($lmHour); ?></div><?php endforeach; ?></div></div></section>
  <?php break;
  case 'newsletter': ?>
    <section class="newsletter"><div class="wrap lm-newsletter-inner"><div><div class="eyebrow">GLOW NOTES</div><h2><?= lm_escape(lm_section_copy($lmSection, !empty($settings->newsletter_title) && $settings->newsletter_title !== 'Stay in the Loop' ? $settings->newsletter_title : 'Beautiful things, in your inbox.')); ?></h2><p><?= lm_escape(lm_section_copy($lmSection, !empty($settings->newsletter_subtitle) && $settings->newsletter_subtitle !== 'Subscribe for updates, deals and new arrivals.' ? $settings->newsletter_subtitle : 'Be first to hear about new arrivals, skincare tips and special moments.', 'subtitle')); ?></p></div><form data-source="lumiere" onsubmit="return mpNewsletterSubmit(event)"><label for="lm-newsletter-email" class="hide">Email address</label><input type="email" id="lm-newsletter-email" name="email" placeholder="Your email address" required><button type="submit">Join the List ↗</button></form></div></section>
  <?php break;
  endswitch;
endforeach; ?>
</main>
