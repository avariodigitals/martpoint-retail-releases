<?php
$slug = $settings->store_slug ?? '';
$studio = ($theme_key ?? '') === 'landscape_studio';
$market = ($theme_key ?? '') === 'garden_marketplace';
$canRequest = mp_garden_has_mode($garden_profile,'landscaping') || mp_garden_has_mode($garden_profile,'maintenance');
$headline = $settings->store_headline ?: ($studio ? 'Thoughtful gardens. Beautiful outdoor spaces.' : ($market ? 'Find what your garden needs.' : 'A little more life, in every space.'));
?>
<section class="garden-store-hero"><div class="garden-store-inner"><div class="garden-store-eyebrow"><?= htmlspecialchars($settings->store_name ?? $store->store_name ?? ''); ?></div>
<h1><?= htmlspecialchars($headline); ?></h1>
<?php if (!empty($settings->store_subheadline)): ?><p><?= htmlspecialchars($settings->store_subheadline); ?></p><?php endif; ?>
<div class="garden-store-links">
<?php if (!empty($sells_products) && !empty($all_products)): ?><a href="<?= base_url('store/'.$slug.'/products'); ?>">Explore the Shop</a><?php endif; ?>
<?php if (!empty($sells_services) && !empty($featured_services)): ?><a href="<?= base_url('store/'.$slug.'/services'); ?>">Discover Services</a><?php endif; ?>
<?php if ($canRequest): ?><a href="<?= htmlspecialchars($garden_request_url); ?>"><?= $studio ? 'Discuss Your Space' : 'Request a Consultation'; ?></a><?php endif; ?>
</div>
<?php if ($market && !empty($sells_products)): ?><form class="garden-store-search" method="get" action="<?= base_url('store/'.$slug.'/products'); ?>"><input type="search" name="search" placeholder="Search plants, pots and supplies" aria-label="Search products"><button>Search</button></form><?php endif; ?>
</div></section>
<?php
// Keep merchant section choices and shared data; replace only garden service presentation.
foreach ($homepage_sections as $key=>$section) {
    if (!$section->is_enabled) continue;
    $baseKey = preg_replace('/_\d+$/','',$key);
    if ($baseKey === 'hero_banner') continue;
    if ($baseKey === 'featured_services') {
        if (!empty($sells_services) && !empty($featured_services)) $this->load->view('themes/garden/service_cards',['services'=>$featured_services]);
        continue;
    }
    $allowed = ['trust_badges'=>'trust_badges','promo_banner'=>'promo','featured_categories'=>'featured_categories','featured_products'=>'featured_products','best_sellers'=>'best_sellers','new_arrivals'=>'new_arrivals','store_info'=>'store_info','contact_section'=>'contact','whatsapp_cta'=>'whatsapp_cta','newsletter'=>'newsletter','store_hours'=>'store_hours','brands'=>'brands','testimonials'=>'testimonials','instagram_gallery'=>'instagram','faqs'=>'faqs'];
    if (isset($allowed[$baseKey])) include APPPATH.'views/themes/shared/sections/'.$allowed[$baseKey].'.php';
}
?>
