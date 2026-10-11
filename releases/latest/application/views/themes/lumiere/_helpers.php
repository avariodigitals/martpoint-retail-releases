<?php
defined('BASEPATH') OR exit('No direct script access allowed');

if (!function_exists('lm_escape')) {
    function lm_escape($value) { return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8'); }
}
if (!function_exists('lm_section_copy')) {
    function lm_section_copy($section, $default, $kind = 'title') {
        if (!$section) return $default;
        $config = json_decode($section->config_json ?? '', true);
        if (is_array($config) && !empty($config[$kind])) return $config[$kind];
        if ($kind === 'title') {
            $label = trim((string) ($section->section_label ?? ''));
            $generic = ['Hero Banner','Trust Badges','Promotional Banner','Featured Categories','Featured Products','Featured Services','Best Sellers','New Arrivals','Brands','Testimonials','Instagram Gallery','Store Information','FAQs','Contact Section','WhatsApp CTA','Store Hours','Newsletter CTA'];
            if ($label !== '' && !in_array($label, $generic, true)) return preg_replace('/\s*\(\d+\)$/', '', $label);
        }
        return $default;
    }
}
if (!function_exists('lm_editorial_section')) {
    function lm_editorial_section($section, $home) {
        $config = json_decode($section->config_json ?? '', true);
        $image = is_array($config) && !empty($config['image_url']) && preg_match('~^https?://[a-zA-Z0-9:/?&%._=+#-]+$~', $config['image_url']) ? $config['image_url'] : '';
        ?>
        <section id="story" class="editorial"><div class="editorial-visual" role="img" aria-label="Skincare products arranged in a calming setting"<?php if ($image): ?> style="background-image:url('<?= lm_escape($image); ?>')"<?php endif; ?>></div><div class="editorial-copy"><div class="eyebrow">A MOMENT TO FEEL GOOD</div><h2><?= lm_escape(lm_section_copy($section, 'Skincare that feels like self-care.')); ?></h2><p><?= lm_escape(lm_section_copy($section, 'Every beautiful routine begins with a little intention. Discover curated essentials and thoughtful rituals that make caring for your skin the best part of your day.', 'subtitle')); ?></p><a class="btn" href="<?= lm_escape($home . '/products'); ?>">Discover Your Ritual ↗</a></div></section>
        <?php
    }
}
if (!function_exists('lm_item_image')) {
    function lm_item_image($path, $width = 700) {
        if (!$path) return '';
        if (preg_match('~^https?://~i', $path)) return filter_var($path, FILTER_VALIDATE_URL) ? $path : '';
        return (is_file($path) || is_file(FCPATH . ltrim($path, '/'))) ? mp_minified_image_url($path, $width) : '';
    }
}
if (!function_exists('lm_banner_image')) {
    function lm_banner_image($path) {
        $path = trim((string) $path);
        if ($path === '') return '';
        if (preg_match('~^https?://~i', $path)) return filter_var($path, FILTER_VALIDATE_URL) ? $path : '';
        return is_file(FCPATH . ltrim($path, '/')) ? base_url(ltrim($path, '/')) : '';
    }
}
if (!function_exists('lm_banner_link')) {
    function lm_banner_link($url, $slug, $fallback) {
        $url = trim((string) $url);
        if ($url === '') return $fallback;
        if (preg_match('~^https?://~i', $url)) return filter_var($url, FILTER_VALIDATE_URL) ? $url : $fallback;
        if (strpos($url, '#') === 0) return base_url('store/' . $slug) . $url;
        if (strpos($url, '/') === 0) return base_url(ltrim($url, '/'));
        if (preg_match('~^(products|services|about|cart|checkout)([/?#].*)?$~i', $url)) return base_url('store/' . $slug . '/' . $url);
        if (preg_match('~^[a-zA-Z0-9/_?#=&.%-]+$~', $url)) return base_url($url);
        return $fallback;
    }
}
if (!function_exists('lm_product_card')) {
    function lm_product_card($product, $slug, $currency, $settings) {
        $id = (int) $product->id;
        $name = $product->item_name ?? 'Skincare essential';
        $imagePath = (string) ($product->item_image ?? '');
        $image = lm_item_image($imagePath);
        $price = (float) ($product->effective_price ?? $product->sales_price ?? 0);
        $old = (float) ($product->original_price ?? 0);
        $stock = (int) ($product->stock ?? 0);
        $type = $product->product_type ?? 'physical';
        $oos = $type === 'physical' && $stock <= 0 && empty($settings->allow_backorder);
        $needsDetail = !empty($product->item_group) && $product->item_group === 'Variants';
        $url = base_url('store/' . $slug . '/product/' . $id);
        ?>
        <article class="product">
          <div class="product-image">
            <a href="<?= lm_escape($url); ?>" aria-label="View <?= lm_escape($name); ?>">
              <?php if ($image): ?><img loading="lazy" src="<?= lm_escape($image); ?>" alt="<?= lm_escape($name); ?>"><?php else: ?><span class="lm-image-placeholder" aria-hidden="true">LUMIÈRE</span><?php endif; ?>
            </a>
            <?php if (!empty($product->is_new_arrival)): ?><span class="tag">New arrival</span><?php elseif ($old > $price && $old > 0): ?><span class="tag">Special offer</span><?php endif; ?>
            <?php if (!$oos && !$needsDetail && !in_array($type, ['course','membership'], true)): ?>
              <button type="button" class="quick-add" aria-label="Add <?= lm_escape($name); ?> to bag" data-lm-add data-id="<?= $id; ?>" data-type="<?= lm_escape($type); ?>" data-name="<?= lm_escape($name); ?>" data-price="<?= $price; ?>" data-image="<?= lm_escape($imagePath); ?>" data-stock="<?= $stock; ?>">+</button>
            <?php endif; ?>
          </div>
          <div class="product-label"><?= lm_escape($product->category_name ?? 'The Lumière edit'); ?></div>
          <h3><a href="<?= lm_escape($url); ?>"><?= lm_escape($name); ?></a></h3>
          <div class="price"><?= sf_currency($price, $currency); ?><?php if ($old > $price && $old > 0): ?><span class="old"><?= sf_currency($old, $currency); ?></span><?php endif; ?></div>
          <?php if ($oos): ?><div class="rating">Currently unavailable</div><?php endif; ?>
        </article>
        <?php
    }
}
if (!function_exists('lm_service_card')) {
    function lm_service_card($service, $slug, $currency) {
        $name = $service->service_name ?? $service->item_name ?? 'Treatment';
        $image = lm_item_image($service->service_image ?? $service->item_image ?? '', 850);
        $url = base_url('store/' . $slug . '/service/' . (int) $service->id);
        $price = (float) ($service->effective_price ?? $service->price ?? 0);
        ?>
        <article class="service">
          <a class="service-image" href="<?= lm_escape($url); ?>" aria-label="View <?= lm_escape($name); ?>">
            <?php if ($image): ?><img loading="lazy" src="<?= lm_escape($image); ?>" alt="<?= lm_escape($name); ?>"><?php else: ?><span class="lm-image-placeholder" aria-hidden="true">LUMIÈRE</span><?php endif; ?>
          </a>
          <div class="service-info">
            <h3><a href="<?= lm_escape($url); ?>"><?= lm_escape($name); ?></a></h3>
            <p><?= lm_escape(mb_strimwidth(strip_tags($service->description ?? ''), 0, 145, '…', 'UTF-8')); ?></p>
            <div class="service-bottom"><small><?= !empty($service->service_duration) ? lm_escape($service->service_duration) . ' · ' : ''; ?>From <?= sf_currency($price, $currency); ?></small><a href="<?= lm_escape($url); ?>">Book Treatment ↗</a></div>
          </div>
        </article>
        <?php
    }
}
