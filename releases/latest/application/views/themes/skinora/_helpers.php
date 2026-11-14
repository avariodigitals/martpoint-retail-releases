<?php
defined('BASEPATH') OR exit('No direct script access allowed');
require_once APPPATH . 'views/themes/lumiere/_helpers.php';
if (!function_exists('sn_e')) {
    function sn_e($value) { return lm_escape($value); }
    function sn_copy($section, $default, $kind = 'title') {
        if ($kind === 'title' && $section) {
            $config = json_decode($section->config_json ?? '', true);
            if (is_array($config) && !empty($config['title'])) return $config['title'];
            $label = preg_replace('/\s*\(\d+\)$/', '', trim((string) ($section->section_label ?? '')));
            if (in_array($label, ['Hero Banner','Trust Badges','Promotional Banner','Featured Categories','Featured Products','Featured Services','Best Sellers','New Arrivals','Brands','Testimonials','Instagram Gallery','Store Information','FAQs','Contact Section','WhatsApp CTA','Store Hours','Newsletter CTA'], true)) return $default;
        }
        return lm_section_copy($section, $default, $kind);
    }
    function sn_image($path, $width = 700) { return lm_item_image($path, $width); }
    function sn_banner_image($path) { return lm_banner_image($path); }
    function sn_link($url, $slug, $fallback) { return lm_banner_link($url, $slug, $fallback); }
    function sn_product_card($product, $slug, $currency, $settings) {
        $id = (int) $product->id;
        $name = $product->item_name ?? 'Skincare essential';
        $path = (string) ($product->item_image ?? '');
        $image = sn_image($path);
        $price = (float) ($product->effective_price ?? $product->sales_price ?? 0);
        $old = (float) ($product->original_price ?? 0);
        $stock = (int) ($product->stock ?? 0);
        $type = $product->product_type ?? 'physical';
        $variant = ($product->item_group ?? '') === 'Variants';
        $unavailable = !$variant && $type === 'physical' && $stock <= 0 && empty($settings->allow_backorder);
        $url = base_url('store/' . $slug . '/product/' . $id);
        $category = $product->category_name ?? 'Skincare';
        ?>
        <article class="product-card" data-category="<?= sn_e(mb_strtolower($category, 'UTF-8')); ?>" data-bestseller="<?= !empty($product->is_featured) || !empty($product->sold_count) ? '1' : '0'; ?>">
          <div class="product-photo"><a href="<?= sn_e($url); ?>" aria-label="View <?= sn_e($name); ?>"><?php if ($image): ?><img src="<?= sn_e($image); ?>" alt="<?= sn_e($name); ?>" loading="lazy"><?php else: ?><span class="sn-image-placeholder">SKINORA</span><?php endif; ?></a>
            <?php if (!empty($product->is_new_arrival)): ?><span class="tag">NEW</span><?php elseif ($old > $price && $old > 0): ?><span class="tag sale">SPECIAL OFFER</span><?php elseif (!empty($product->is_featured)): ?><span class="tag">FAVOURITE</span><?php endif; ?>
            <?php if (!$unavailable && !$variant && !in_array($type, ['course', 'membership'], true)): ?><button type="button" class="add-btn" aria-label="Add <?= sn_e($name); ?> to bag" data-sn-add data-id="<?= $id; ?>" data-type="<?= sn_e($type); ?>" data-name="<?= sn_e($name); ?>" data-price="<?= $price; ?>" data-image="<?= sn_e($path); ?>" data-stock="<?= $stock; ?>">+</button><?php endif; ?>
          </div>
          <div class="product-info"><div class="meta"><?= sn_e($category); ?></div><h3><a href="<?= sn_e($url); ?>"><?= sn_e($name); ?></a></h3><div class="price"><?= $variant && $price <= 0 ? 'Choose options' : sf_currency($price, $currency); ?><?php if ($old > $price && $old > 0): ?><span class="old"><?= sf_currency($old, $currency); ?></span><?php endif; ?></div><?php if ($unavailable): ?><div class="rating">Currently unavailable</div><?php endif; ?></div>
        </article>
        <?php
    }
    function sn_service_card($service, $slug, $currency) {
        $name = $service->service_name ?? $service->item_name ?? 'Treatment';
        $image = sn_image($service->service_image ?? $service->item_image ?? '', 850);
        $url = base_url('store/' . $slug . '/service/' . (int) $service->id);
        $price = (float) ($service->effective_price ?? $service->price ?? 0);
        ?><article class="service-card"><a href="<?= sn_e($url); ?>" aria-label="View <?= sn_e($name); ?>"><?php if ($image): ?><img src="<?= sn_e($image); ?>" alt="<?= sn_e($name); ?>" loading="lazy"><?php else: ?><div class="sn-image-placeholder">SKINORA</div><?php endif; ?></a><div><h3><a href="<?= sn_e($url); ?>"><?= sn_e($name); ?></a></h3><p><?= sn_e(mb_strimwidth(strip_tags($service->description ?? ''), 0, 145, '…', 'UTF-8')); ?></p><div class="service-footer"><span><?= !empty($service->service_duration) ? sn_e($service->service_duration) . ' · ' : ''; ?><?= $price > 0 ? sf_currency($price, $currency) : 'Price on request'; ?></span><a href="<?= sn_e($url); ?>">BOOK NOW ↗</a></div></div></article><?php
    }
}
