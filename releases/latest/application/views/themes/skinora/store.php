<?php
require_once APPPATH . 'views/themes/skinora/_helpers.php';
$snSlug = $settings->store_slug ?? '';
$snHome = base_url('store/' . $snSlug);
$snCurrency = $store_currency ?? null;
$snSections = $homepage_sections ?? [];
$snEnabled = function($key) use ($snSections) { return isset($snSections[$key]); };
$snSection = function($key) use ($snSections) { return $snSections[$key] ?? null; };
$snHasServices = !empty($sells_services) && !empty($settings->allow_services);
$snHero = ($hero_banners ?? [])[0] ?? null;
$snHeroSection = $snSection('hero_banner');
$snHeroCfg = json_decode($snHeroSection->config_json ?? '', true);
if (!is_array($snHeroCfg)) $snHeroCfg = [];
$snHeroDesktop = $snHero ? sn_banner_image($snHero->desktop_image ?? '') : '';
$snHeroMobile = $snHero ? sn_banner_image($snHero->mobile_image ?? '') : '';
if (!$snHeroDesktop && !empty($snHeroCfg['image_url']) && filter_var($snHeroCfg['image_url'], FILTER_VALIDATE_URL)) $snHeroDesktop = $snHeroCfg['image_url'];
$snHeroDesktop = $snHeroDesktop ?: base_url('theme/img/skinora/hero.webp');
$snHeroMobile = $snHeroMobile ?: $snHeroDesktop;
$snHeroTitle = trim((string) ($snHero->banner_title ?? '')) ?: sn_copy($snHeroSection, "Beauty\nBeyond\nSkin Deep.");
$snHeroIsReferenceTitle = trim(preg_replace('/\s+/', ' ', $snHeroTitle)) === 'Beauty Beyond Skin Deep.';
$snHeroSubtitle = trim((string) ($snHero->banner_subtitle ?? '')) ?: sn_copy($snHeroSection, 'Considered skincare for every shade, every season, every version of you. Find your glow, one beautiful ritual at a time.', 'subtitle');
$snHeroButton = trim((string) ($snHero->button_text ?? '')) ?: 'EXPLORE THE COLLECTION';
$snHeroUrl = sn_link($snHero->button_url ?? '', $snSlug, $snHome . '/products');
$snBadges = json_decode($settings->trust_badges_json ?? '', true);
if (!is_array($snBadges) || !$snBadges) $snBadges = [['title'=>'Thoughtfully curated skincare'],['title'=>'Beauty for every skin tone'],['title'=>'Secure shopping experience'],['title'=>'Expert treatments & consultations']];
$snAll = $all_products ?? [];
$snById = [];
foreach ($snAll as $p) $snById[(int) $p->id] = $p;
$snProducts = [];
foreach (['featured_products','best_sellers','new_arrivals','all_products'] as $source) {
    if ($source !== 'all_products' && !$snEnabled($source)) continue;
    foreach ($$source ?? [] as $p) {
        $id = (int) $p->id;
        if (isset($snProducts[$id])) continue;
        if (isset($snById[$id])) {
            $p->effective_price = $snById[$id]->effective_price;
            $p->original_price = $snById[$id]->original_price;
        }
        $p->sn_bestseller = $source === 'best_sellers' || !empty($p->sold_count);
        $p->sn_new = $source === 'new_arrivals' || !empty($p->is_new_arrival);
        $snProducts[$id] = $p;
        if (count($snProducts) >= 8) break 2;
    }
}
$snProductSection = $snSection('featured_products') ?: ($snSection('best_sellers') ?: $snSection('new_arrivals'));
$snShowProducts = !empty($sells_products) && ($snEnabled('featured_products') || $snEnabled('best_sellers') || $snEnabled('new_arrivals'));
$snPromo = array_values($promo_banners ?? []);
$snPromoSection = $snSection('promo_banner');
$snPromoCfg = json_decode($snPromoSection->config_json ?? '', true);
if (!is_array($snPromoCfg)) $snPromoCfg = [];
$snStorySection = $snSection('store_info');
$snStoryCfg = json_decode($snStorySection->config_json ?? '', true);
if (!is_array($snStoryCfg)) $snStoryCfg = [];
$snStoryImage = !empty($snStoryCfg['image_url']) && filter_var($snStoryCfg['image_url'], FILTER_VALIDATE_URL) ? $snStoryCfg['image_url'] : base_url('theme/img/skinora/story.webp');
$snValueDefaults = [['title'=>'Beauty without compromise','subtitle'=>'Simple, thoughtful choices for the skin you live in every day.'],['title'=>'Every shade is celebrated','subtitle'=>'Our approach to skincare embraces the individuality of every complexion.'],['title'=>'Rituals worth returning to','subtitle'=>'Build a routine that feels as good as it looks.']];
$snValues = $snStoryCfg['values'] ?? [];
if (!is_array($snValues) || !$snValues) {
    $snValues = [];
    foreach ($snValueDefaults as $i => $default) {
        $badge = $snBadges[$i] ?? [];
        if (!is_array($badge)) $badge = [];
        $snValues[] = [
            'title' => trim((string) ($badge['title'] ?? '')) ?: $default['title'],
            'subtitle' => trim((string) ($badge['desc'] ?? $badge['subtitle'] ?? '')) ?: $default['subtitle'],
        ];
    }
}
$snCategoryImage = function($category) use ($snAll) {
    $image = sn_image($category->category_image ?? '', 500);
    if ($image) return $image;
    foreach ($snAll as $p) if (($p->category_name ?? '') === ($category->category_name ?? '')) {
        $image = sn_image($p->item_image ?? '', 500);
        if ($image) return $image;
    }
    return '';
};
?>
<main id="top" class="sn-home">
<?php if ($snEnabled('hero_banner')): ?>
<section class="hero" aria-labelledby="sn-hero-title"><div class="hero-copy"><div class="eyebrow"><?= sn_e(sn_copy($snHeroSection, 'THE ART OF LOVING YOUR SKIN', 'eyebrow')); ?></div><h1 id="sn-hero-title"><?= $snHeroIsReferenceTitle ? 'Beauty<br>Beyond<br><i>Skin Deep.</i>' : nl2br(sn_e($snHeroTitle)); ?></h1><p><?= sn_e($snHeroSubtitle); ?></p><div class="hero-buttons"><?php if (!empty($sells_products)): ?><a href="<?= sn_e($snHeroUrl); ?>" class="btn"><?= sn_e($snHeroButton); ?> <span>↗</span></a><?php endif; ?><?php if ($snHasServices): ?><a href="<?= sn_e($snHome . '/services'); ?>" class="btn outline">BOOK A TREATMENT</a><?php endif; ?></div></div><div class="hero-photo"><picture><source media="(max-width:600px)" srcset="<?= sn_e($snHeroMobile); ?>"><img src="<?= sn_e($snHeroDesktop); ?>" alt="<?= sn_e($snHeroTitle); ?>" fetchpriority="high"></picture><div class="roundel">GLOW<br>HEALTHY<br>CONFIDENT<br>EVERY DAY</div></div></section>
<?php endif; ?>
<?php if ($snEnabled('trust_badges')): ?><div class="mini-strip"><div class="container strip-inner"><?php foreach (array_slice($snBadges, 0, 4) as $i => $badge): if ($i === 3 && !$snHasServices) continue; $label = is_array($badge) ? ($badge['title'] ?? $badge['label'] ?? '') : $badge; $label = trim((string) $label) ?: ['Thoughtfully curated skincare','Beauty for every skin tone','Secure shopping experience','Expert treatments & consultations'][$i]; ?><div><b><?= ['✧','♡','↗','✺'][$i]; ?></b> <?= sn_e($label); ?></div><?php endforeach; ?></div></div><?php endif; ?>
<?php if (!empty($sells_products) && $snEnabled('featured_categories') && !empty($categories)): ?>
<section class="section" id="collections"><div class="container"><div class="section-heading"><div><div class="eyebrow">FIND YOUR PERFECT ROUTINE</div><h2 class="section-title"><?= sn_e(sn_copy($snSection('featured_categories'), 'The skincare edit.')); ?></h2><p class="lead"><?= sn_e(sn_copy($snSection('featured_categories'), 'Essentials that make taking care of your skin a pleasure.', 'subtitle')); ?></p></div><a href="<?= sn_e($snHome . '/products'); ?>" class="text-link">SHOP ALL PRODUCTS ↗</a></div><div class="category-scroller"><?php foreach (array_slice($categories, 0, 6) as $category): $image = $snCategoryImage($category); ?><a class="category-card" href="<?= sn_e($snHome . '/products?category=' . (int) $category->id); ?>"><?php if ($image): ?><img src="<?= sn_e($image); ?>" alt="<?= sn_e($category->category_name); ?>" loading="lazy"><?php else: ?><div class="sn-category-placeholder">✧</div><?php endif; ?><span><?= sn_e($category->category_name); ?> →</span></a><?php endforeach; ?></div></div></section>
<?php endif; ?>
<?php if ($snEnabled('promo_banner') || $snEnabled('promo_banner_2')): ?>
<section class="container sn-editorial" aria-label="Featured collections"><div class="editorial-grid<?= $snEnabled('promo_banner') && $snEnabled('promo_banner_2') ? '' : ' sn-editorial-single'; ?>">
<?php for ($i = 0; $i < 2; $i++):
    if (!$snEnabled($i ? 'promo_banner_2' : 'promo_banner')) continue;
    $promo = $snPromo[$i] ?? null;
    $promoSection = $i ? ($snSection('promo_banner_2') ?: $snPromoSection) : $snPromoSection;
    $promoCfg = json_decode($promoSection->config_json ?? '', true);
    if (!is_array($promoCfg)) $promoCfg = [];
    $defaultTitle = $i ? 'Skin in Balance.' : 'Glow Collection.';
    $title = trim((string) ($promo->banner_title ?? '')) ?: sn_copy($promoSection, $defaultTitle);
    $subtitle = trim((string) ($promo->banner_subtitle ?? '')) ?: sn_copy($promoSection, $i ? 'Care for your skin, not against it.' : 'A little more radiance. A lot more you.', 'subtitle');
    $image = $promo ? sn_banner_image($promo->desktop_image ?? '') : '';
    $mobileImage = $promo ? sn_banner_image($promo->mobile_image ?? '') : '';
    if (!$image && !empty($promoCfg['image_url']) && filter_var($promoCfg['image_url'], FILTER_VALIDATE_URL)) $image = $promoCfg['image_url'];
    $image = $image ?: ($i ? 'https://images.unsplash.com/photo-1608248543803-ba4f8c70ae0b?auto=format&fit=crop&w=900&q=85' : 'https://images.unsplash.com/photo-1601049541289-9b1b7bbbfe19?auto=format&fit=crop&w=1000&q=85');
    $mobileImage = $mobileImage ?: $image;
    $url = sn_link($promo->button_url ?? '', $snSlug, $snHome . '/products');
    $button = trim((string) ($promo->button_text ?? '')) ?: ($i ? 'SHOP THE ROUTINE' : 'DISCOVER THE EDIT');
?><a href="<?= sn_e($url); ?>" class="editorial-banner<?= $i ? ' alt' : ''; ?>"><picture><source media="(max-width:600px)" srcset="<?= sn_e($mobileImage); ?>"><img src="<?= sn_e($image); ?>" alt="<?= sn_e($title); ?>" loading="lazy"></picture><div class="content"><span class="eyebrow"><?= sn_e(sn_copy($promoSection, $i ? 'TARGETED CARE' : 'THE DAILY ESSENTIALS', 'eyebrow')); ?></span><h3><?= sn_e($title); ?></h3><p><?= sn_e($subtitle); ?></p><span class="inline-link"><?= sn_e($button); ?> ↗</span></div></a><?php endfor; ?>
</div></section><?php endif; ?>
<?php if ($snShowProducts && $snProducts): ?><section class="section products-section" id="products"><div class="container"><div class="section-heading"><div><div class="eyebrow">THE SKINORA SELECTION</div><h2 class="section-title"><?= sn_e(sn_copy($snProductSection, 'Your shelf, elevated.')); ?></h2><p class="lead"><?= sn_e(sn_copy($snProductSection, 'A curated collection of feel-good skincare favourites.', 'subtitle')); ?></p></div><a href="<?= sn_e($snHome . '/products'); ?>" class="text-link">SHOP ALL PRODUCTS ↗</a></div><div class="tabs" id="sn-filters" role="group" aria-label="Filter products"><button type="button" class="tab active" data-sn-filter="all">All</button><?php if ($snEnabled('best_sellers')): ?><button type="button" class="tab" data-sn-filter="bestsellers">Bestsellers</button><?php endif; ?><?php if ($snEnabled('new_arrivals')): ?><button type="button" class="tab" data-sn-filter="new">New arrivals</button><?php endif; ?><?php foreach (array_slice($categories ?? [], 0, 5) as $category): ?><button type="button" class="tab" data-sn-filter="<?= sn_e(mb_strtolower($category->category_name, 'UTF-8')); ?>"><?= sn_e($category->category_name); ?></button><?php endforeach; ?></div><div class="product-grid" id="sn-product-grid"><?php foreach ($snProducts as $product) sn_product_card($product, $snSlug, $snCurrency, $settings); ?></div><div class="empty hidden" id="sn-empty">No products match this collection. <a href="<?= sn_e($snHome . '/products'); ?>">Browse all products ↗</a></div></div></section><?php endif; ?>
<?php if ($snEnabled('trust_badges')): ?><section class="section values"><div class="container"><div class="value-grid"><?php foreach (array_slice($snValues, 0, 3) as $i => $value): ?><div class="value"><b><?= sprintf('%02d.', $i + 1); ?></b><h4><?= sn_e($value['title'] ?? ''); ?></h4><p><?= sn_e($value['subtitle'] ?? ''); ?></p></div><?php endforeach; ?></div></div></section><?php endif; ?>
<?php if ($snEnabled('store_info')): ?><section class="story" id="story"><div class="story-image"><img src="<?= sn_e($snStoryImage); ?>" alt="Skincare portrait" loading="lazy"></div><div class="story-copy"><div class="eyebrow"><?= sn_e(sn_copy($snStorySection, 'MORE THAN A SKINCARE ROUTINE', 'eyebrow')); ?></div><h2><?= nl2br(sn_e(sn_copy($snStorySection, "Real skin.\nReal radiance.\nReal you."))); ?></h2><p><?= sn_e(sn_copy($snStorySection, 'We believe caring for your skin should feel personal, empowering and beautifully uncomplicated. From your first cleanser to your favourite glow ritual, make every moment yours.', 'subtitle')); ?></p><a href="<?= sn_e($snHome . '/products'); ?>" class="btn">FIND YOUR NEXT FAVOURITE ↗</a></div></section><?php endif; ?>
<?php if ($snHasServices && $snEnabled('featured_services') && !empty($featured_services)): ?><section id="services" class="section services-section"><div class="container"><div class="section-heading"><div><div class="eyebrow">BEAUTY, BEYOND THE BOTTLE</div><h2 class="section-title"><?= sn_e(sn_copy($snSection('featured_services'), 'A little time for you.')); ?></h2><p class="lead"><?= sn_e(sn_copy($snSection('featured_services'), 'Treatments and guidance tailored to your skin goals.', 'subtitle')); ?></p></div><a href="<?= sn_e($snHome . '/services'); ?>" class="text-link">VIEW TREATMENTS ↗</a></div><div class="service-grid"><?php foreach (array_slice($featured_services, 0, 3) as $service) sn_service_card($service, $snSlug, $snCurrency); ?></div></div></section><?php endif; ?>
<?php if ($snEnabled('testimonials') && !empty($testimonials)): ?><section class="section sn-extra"><div class="container"><div class="section-heading"><div><div class="eyebrow">THE SKINORA EXPERIENCE</div><h2 class="section-title"><?= sn_e(sn_copy($snSection('testimonials'), 'Words from our community.')); ?></h2></div></div><div class="sn-quote-grid"><?php foreach (array_slice($testimonials, 0, 3) as $quote): ?><blockquote><div>✧ ✧ ✧ ✧ ✧</div><p>“<?= sn_e($quote->testimonial_text ?? $quote->message ?? ''); ?>”</p><cite><?= sn_e($quote->customer_name ?? $quote->author ?? 'Customer'); ?></cite></blockquote><?php endforeach; ?></div></div></section><?php endif; ?>
<?php if ($snEnabled('brands') && !empty($brands)): ?><section class="section sn-extra"><div class="container"><div class="eyebrow">CURATED WITH CARE</div><h2 class="section-title"><?= sn_e(sn_copy($snSection('brands'), 'Brands we love.')); ?></h2><div class="sn-brand-grid"><?php foreach (array_slice($brands, 0, 8) as $brand): ?><span><?= sn_e($brand->brand_name ?? $brand->name ?? ''); ?></span><?php endforeach; ?></div></div></section><?php endif; ?>
<?php if ($snEnabled('instagram_gallery') && !empty($instagram_posts)): ?><section class="section sn-extra"><div class="container"><div class="eyebrow">GLOW JOURNAL</div><h2 class="section-title"><?= sn_e(sn_copy($snSection('instagram_gallery'), 'A little inspiration.')); ?></h2><div class="sn-instagram-grid"><?php foreach (array_slice($instagram_posts, 0, 4) as $post): $image = $post->media_url ?? $post->image ?? ''; if (!$image) continue; ?><a href="<?= sn_e($post->permalink ?? $post->link ?? '#'); ?>" target="_blank" rel="noopener"><img src="<?= sn_e($image); ?>" alt="<?= sn_e($post->caption ?? 'Skinora moment'); ?>" loading="lazy"></a><?php endforeach; ?></div></div></section><?php endif; ?>
<?php if ($snEnabled('faqs') && !empty($faqs)): ?><section class="section sn-extra" id="faqs"><div class="container"><div class="eyebrow">HERE TO HELP</div><h2 class="section-title"><?= sn_e(sn_copy($snSection('faqs'), 'Good to know.')); ?></h2><div class="sn-faq-list"><?php foreach ($faqs as $faq): ?><details><summary><?= sn_e($faq->question ?? $faq->faq_question ?? ''); ?></summary><p><?= nl2br(sn_e($faq->answer ?? $faq->faq_answer ?? '')); ?></p></details><?php endforeach; ?></div></div></section><?php endif; ?>
<?php if ($snEnabled('newsletter')): ?><section class="newsletter" id="newsletter"><div class="eyebrow">WELCOME TO THE INNER CIRCLE</div><h2><?= sn_e(sn_copy($snSection('newsletter'), !empty($settings->newsletter_title) && $settings->newsletter_title !== 'Stay in the Loop' ? $settings->newsletter_title : 'Good skin. Good stories.')); ?></h2><p><?= sn_e(sn_copy($snSection('newsletter'), !empty($settings->newsletter_subtitle) && $settings->newsletter_subtitle !== 'Subscribe for updates, deals and new arrivals.' ? $settings->newsletter_subtitle : 'New arrivals, skincare tips and lovely little surprises in your inbox.', 'subtitle')); ?></p><form data-source="skinora" onsubmit="return mpNewsletterSubmit(event)"><input type="email" name="email" placeholder="Your email address" required aria-label="Email address"><button class="btn" type="submit">JOIN THE LIST ↗</button></form></section><?php endif; ?>
<?php if ($snEnabled('contact_section') && (!empty($settings->store_email) || !empty($settings->store_phone))): ?><section class="section sn-extra sn-contact"><div class="container"><div class="eyebrow">SAY HELLO</div><h2 class="section-title"><?= sn_e(sn_copy($snSection('contact_section'), 'We are here for you.')); ?></h2><?php if (!empty($settings->store_email)): ?><a href="mailto:<?= sn_e($settings->store_email); ?>"><?= sn_e($settings->store_email); ?></a><?php endif; ?><?php if (!empty($settings->store_phone)): ?><a href="tel:<?= sn_e(preg_replace('/[^0-9+]/', '', $settings->store_phone)); ?>"><?= sn_e($settings->store_phone); ?></a><?php endif; ?></div></section><?php endif; ?>
<?php if ($snEnabled('whatsapp_cta') && !empty($settings->whatsapp_number)): $wa = preg_replace('/[^0-9]/', '', $settings->whatsapp_number); ?><section class="section sn-whatsapp"><div class="container"><div class="eyebrow">PERSONAL CARE</div><h2 class="section-title"><?= sn_e(sn_copy($snSection('whatsapp_cta'), 'A little help finding your glow?')); ?></h2><a class="btn" href="https://wa.me/<?= sn_e($wa); ?>" target="_blank" rel="noopener">CHAT WITH US ↗</a></div></section><?php endif; ?>
<?php if ($snEnabled('store_hours') && !empty($business_hours)): ?><section class="section sn-extra"><div class="container"><div class="eyebrow">COME BY</div><h2 class="section-title"><?= sn_e(sn_copy($snSection('store_hours'), 'Opening hours.')); ?></h2><div class="sn-hours"><?php foreach ($business_hours as $hour): ?><p><?= sn_e($hour); ?></p><?php endforeach; ?></div></div></section><?php endif; ?>
</main>
<script>document.addEventListener('DOMContentLoaded',function(){var tabs=document.querySelectorAll('#sn-filters [data-sn-filter]'),cards=document.querySelectorAll('#sn-product-grid .product-card'),empty=document.getElementById('sn-empty');tabs.forEach(function(tab){tab.addEventListener('click',function(){tabs.forEach(function(t){t.classList.toggle('active',t===tab)});var filter=tab.dataset.snFilter,visible=0;cards.forEach(function(card){var match=filter==='all'||(filter==='bestsellers'&&card.dataset.bestseller==='1')||(filter==='new'&&card.querySelector('.tag')&&card.querySelector('.tag').textContent.trim()==='NEW')||card.dataset.category===filter;card.classList.toggle('hidden',!match);if(match)visible++});if(empty)empty.classList.toggle('hidden',visible>0)})})});</script>
