<?php
$fxStoreProfile = function_exists('mp_get_store_profile') ? mp_get_store_profile((int)($settings->store_id ?? 0)) : [];
$fxIsRestaurant = in_array(strtolower(trim((string)($fxStoreProfile['industry_type'] ?? ''))), ['restaurant','fast_food','food_delivery','cafe','bakery','pizza_shop','shawarma','juice_bar','buka','canteen'], true);
$fxBrand = $fxCopy('brand_line') ?: 'Good food. Great mood.';
$fxTagline = $fxCopy('brand_tagline') ?: 'From first craving to first bite.';
$fxSearchPlaceholder = $fxCopy('search_placeholder') ?: 'What are you craving?';
$fxCategoriesOverline = $fxCopy('categories_overline') ?: 'Craving something specific?';
$fxCategoriesTitle = $fxCopy('categories_title') ?: 'Find your favourites';
$fxTrust1Title = $fxCopy('trust_1_title') ?: 'Fast delivery';
$fxTrust1Text = $fxCopy('trust_1_text') ?: 'Hot from the kitchen to your door';
$fxTrust2Title = $fxCopy('trust_2_title') ?: 'Order your way';
$fxTrust2Text = $fxCopy('trust_2_text') ?: 'Delivery, pickup or QR table order';
$fxTrust3Title = $fxCopy('trust_3_title') ?: 'Secure payments';
$fxTrust3Text = $fxCopy('trust_3_text') ?: 'Pay online or on delivery';
$fxExploreCta = $fxCopy('explore_cta') ?: 'Browse the menu';
$fxHeroBanner = !empty($hero_banners) && is_array($hero_banners) ? reset($hero_banners) : null;
$fxAssetUrl = function($value) {
    $value = trim((string)$value);
    if ($value === '') return '';
    return preg_match('~^https?://~i', $value) ? $value : base_url(ltrim($value, '/'));
};
// Do not inherit a legacy retail/beauty banner into a restaurant storefront.
// Restaurant hero media is explicit theme content. When the merchant has not
// selected a hero image yet, use a real featured menu image as the visual
// anchor so the first screen still speaks about food, not a generic poster.
$fxHeroImage = $fxValue('hero_image');
if (!$fxHeroImage && $fxIsRestaurant && !empty($featured_products) && is_array($featured_products) && function_exists('sf_product_image_exists')) {
    foreach ($featured_products as $fxHeroProduct) {
        $fxCandidate = trim((string)($fxHeroProduct->item_image ?? ''));
        if ($fxCandidate !== '' && sf_product_image_exists($fxCandidate) && !preg_match('~product-placeholders/|(?:^|/)(?:no_image|no-image|placeholder)\.[a-z]+$~i', $fxCandidate)) {
            $fxHeroImage = $fxCandidate;
            break;
        }
    }
}
if (!$fxHeroImage && $fxIsRestaurant && !empty($categories) && is_array($categories)) {
    // Prefer a substantial meal image for the first screen over a dessert,
    // drinks or appetiser tile when category photography is all we have.
    $fxHeroCategories = $categories;
    usort($fxHeroCategories, function($a, $b) {
        $score = function($category) {
            $name = strtolower(trim((string)($category->category_name ?? '')));
            if (strpos($name, 'main') !== false || strpos($name, 'meal') !== false || strpos($name, 'signature') !== false) return 0;
            if (strpos($name, 'brunch') !== false || strpos($name, 'appetizer') !== false) return 1;
            return 2;
        };
        return $score($a) <=> $score($b);
    });
    foreach ($fxHeroCategories as $fxHeroCategory) {
        $fxCandidate = trim((string)($fxHeroCategory->category_image ?? ''));
        if ($fxCandidate !== '' && is_file(FCPATH . ltrim($fxCandidate, '/'))) {
            $fxHeroImage = $fxCandidate;
            break;
        }
    }
}
$fxHeroMobileImage = $fxHeroImage;
$fxHeroTitle = trim((string)($fxHeroBanner->banner_title ?? '')) ?: $fxBrand;
$fxHeroSubtitle = trim((string)($fxHeroBanner->banner_subtitle ?? '')) ?: ($fxTagline . ' Choose your location, browse the menu and order from the kitchen closest to you.');
$fxHeroButton = trim((string)($fxHeroBanner->button_text ?? '')) ?: 'Order from the kitchen';
$fxOutlets = !empty($outlets) && is_array($outlets) ? array_values($outlets) : [];
$fxStoreDescription = trim((string)($settings->store_description ?? ''));
if ($fxIsRestaurant && preg_match('/catalogue|services|support|updates/i', $fxStoreDescription)) {
    $fxStoreDescription = '';
}
$fxLocationParts = array_filter([
    trim((string)($settings->store_city ?? '')),
    trim((string)($settings->store_state ?? '')),
    trim((string)($settings->store_address ?? ($store->address ?? ''))),
]);
$fxLocation = $fxLocationParts ? implode(', ', $fxLocationParts) : 'Choose your delivery location';

/**
 * Map homepage_sections keys to our fx-* section renderers.
 * Each entry defines: enabled check, render callback, and default sort order.
 */
$fxSections = [
    'hero_banner' => [
        'enabled' => true, // Always show our custom hero search
        'render' => function() use ($fxHeroTitle, $fxHeroSubtitle, $fxHeroButton, $fxHeroImage, $fxHeroMobileImage, $fxLocation, $fxAssetUrl, $settings) { ?>
            <!-- Restaurant editorial hero -->
            <section class="fx-hero" id="fx-hero-search">
              <div class="fx-hero-inner">
                <div class="fx-hero-copy">
                  <div class="fx-hero-kicker"><?= htmlspecialchars($fxLocation); ?></div>
                  <h1 class="fx-hero-title"><?= htmlspecialchars($fxHeroTitle); ?></h1>
                  <p class="fx-hero-subtitle"><?= htmlspecialchars($fxHeroSubtitle); ?></p>
                  <div class="fx-hero-actions">
                    <a class="fx-hero-primary" href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>"><?= htmlspecialchars($fxHeroButton); ?><svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M5 12h14M13 6l6 6-6 6"/></svg></a>
                    <a class="fx-hero-secondary" href="#fx-menu-preview">See the menu</a>
                  </div>
                  <div class="fx-hero-note"><span class="fx-hero-rule"></span><span data-fx-location-value><?= htmlspecialchars($fxLocation); ?></span></div>
                </div>
                <div class="fx-hero-art" aria-label="Restaurant feature image">
                  <?php if($fxHeroImage): ?>
                    <picture>
                      <?php if($fxHeroMobileImage): ?><source media="(max-width: 680px)" srcset="<?= htmlspecialchars($fxAssetUrl($fxHeroMobileImage)); ?>"><?php endif; ?>
                      <img src="<?= htmlspecialchars($fxAssetUrl($fxHeroImage)); ?>" alt="" class="fx-hero-image">
                    </picture>
                  <?php else: ?>
                    <div class="fx-hero-art-placeholder"><span>Made fresh for your table</span><small>From our kitchen</small></div>
                  <?php endif; ?>
                  <span class="fx-hero-caption">Made to be remembered</span>
                  </div>
              </div>
            </section>
        <?php },
        'order' => 1,
    ],
    'outlet_picker' => [
        'enabled' => count($fxOutlets) > 1,
        'render' => function() use ($fxOutlets, $settings) { ?>
            <section class="fx-outlet-section" id="fx-outlet-picker" aria-labelledby="fx-outlet-title">
              <div class="fx-outlet-section-inner">
                <div class="fx-outlet-heading">
                  <div>
                    <div class="fx-section-overline">Choose your neighbourhood</div>
                    <h2 id="fx-outlet-title">Which kitchen should prepare your order?</h2>
                    <p>See the menu, pricing and fulfilment options for the location closest to you.</p>
                  </div>
                  <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/branches'); ?>" class="fx-view-all">View all locations <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg></a>
                </div>
                <div class="fx-location-grid" role="list" aria-label="Available ordering locations">
                  <?php foreach($fxOutlets as $outlet):
                    $outletId = (int)$outlet->id;
                    $outletAddress = trim(implode(', ', array_filter([(string)($outlet->address ?? ''), (string)($outlet->city ?? '')])));
                  ?>
                  <button type="button" class="fx-location-card" data-outlet-id="<?= $outletId; ?>" data-outlet-name="<?= htmlspecialchars($outlet->warehouse_name, ENT_QUOTES, 'UTF-8'); ?>" onclick="selectFxOutlet(this, <?= $outletId; ?>)" aria-pressed="false">
                    <span class="fx-location-card-pin"><svg width="19" height="19" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg></span>
                    <span class="fx-location-card-copy"><strong><?= htmlspecialchars($outlet->warehouse_name); ?></strong><small><?= htmlspecialchars($outletAddress ?: 'Delivery and pickup available'); ?></small></span>
                    <span class="fx-location-card-action">Choose <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg></span>
                  </button>
                  <?php endforeach; ?>
                </div>
              </div>
            </section>
            <script>
            function selectFxOutlet(element, outletId){
              const name = element?.dataset?.outletName || 'Selected location';
              localStorage.setItem('sf_outlet_' + STORE_ID, String(outletId));
              document.querySelectorAll('.fx-location-card').forEach(card => {
                const active = Number(card.dataset.outletId) === Number(outletId);
                card.classList.toggle('active', active);
                card.setAttribute('aria-pressed', active ? 'true' : 'false');
              });
              document.querySelectorAll('[data-fx-location-value]').forEach(node => node.textContent = name);
            }
            document.addEventListener('DOMContentLoaded', function(){
              const saved = Number(localStorage.getItem('sf_outlet_' + STORE_ID) || 0);
              if(saved){
                const card = document.querySelector('.fx-location-card[data-outlet-id="' + saved + '"]');
                if(card) selectFxOutlet(card, saved);
              }
            });
            </script>
        <?php },
        'order' => 2,
    ],
    'store_story' => [
        'enabled' => true,
        'render' => function() use ($fxStoreDescription, $settings) { ?>
            <section class="fx-story-section">
              <div class="fx-story-inner">
                <div class="fx-story-label">The kitchen</div>
                <div class="fx-story-copy">
                  <h2>A table worth<br><em>coming back to.</em></h2>
                  <div>
                    <p><?= htmlspecialchars($fxStoreDescription ?: 'Thoughtful food, made with care and served without the ceremony. Order for home, pick up on your way, or settle in for something worth sharing.'); ?></p>
                    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>" class="fx-text-link">Explore our menu <span>↗</span></a>
                  </div>
                </div>
              </div>
            </section>
        <?php },
        'order' => 3.5,
    ],
    'featured_categories' => [
        'enabled' => !empty($categories),
        'render' => function() use ($fxCategoriesOverline, $fxCategoriesTitle, $categories, $settings) { ?>
            <!-- Categories Section -->
            <div class="fx-section" style="background:var(--fx-white);">
              <div class="fx-section-inner">
                <div class="fx-section-header">
                  <div>
                    <div class="fx-section-overline"><?= htmlspecialchars($fxCategoriesOverline); ?></div>
                    <h2 class="fx-section-title"><?= htmlspecialchars($fxCategoriesTitle); ?></h2>
                  </div>
                  <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>" class="fx-view-all">
                    View all <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                  </a>
                </div>
                <div class="fx-categories">
                  <?php foreach(array_slice($categories, 0, 8) as $cat): ?>
                  <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products?category=' . $cat->id); ?>" class="fx-cat-tile">
                    <div class="fx-cat-img-wrap">
                      <?php if(!empty($cat->category_image) && file_exists($cat->category_image)): ?>
                        <img src="<?= base_url($cat->category_image); ?>" alt="" class="fx-cat-img" loading="lazy">
                      <?php else: ?>
                        <div class="fx-cat-img-placeholder" aria-hidden="true"><span><?= htmlspecialchars(mb_strtoupper(mb_substr($cat->category_name, 0, 1, 'UTF-8'), 'UTF-8')); ?></span></div>
                      <?php endif; ?>
                    </div>
                    <div class="fx-cat-name"><?= htmlspecialchars($cat->category_name); ?></div>
                    <?php if(isset($cat->item_count) && $cat->item_count > 0): ?>
                    <div class="fx-cat-count"><?= (int)$cat->item_count; ?> items</div>
                    <?php endif; ?>
                  </a>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
        <?php },
        'order' => 2.5,
    ],
    'featured_products' => [
        'enabled' => !empty($featured_products),
        'render' => function() use ($featured_products, $settings, $store_currency, $store) { ?>
            <!-- Featured Products Section -->
            <div class="fx-section" style="background:var(--fx-cream);">
              <div class="fx-section-inner">
                <div class="fx-section-header">
                  <div>
                    <div class="fx-section-overline">Customer favourites</div>
                    <h2 class="fx-section-title">Popular right now</h2>
                  </div>
                  <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>" class="fx-view-all">
                    View all <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                  </a>
                </div>
                <div class="fx-grid" id="fx-menu-preview">
                  <?php foreach(array_slice($featured_products, 0, 8) as $p):
                    $showSaleStockLabels = (int)($settings->show_sale_stock_labels ?? 1) === 1;
                    $price = $p->effective_price ?? $p->sales_price ?? $p->price;
                    $oldPrice = $p->original_price ?? $p->price;
                    $hasDiscount = $showSaleStockLabels && $oldPrice > $price;
                    $discountPct = $hasDiscount ? round((($oldPrice - $price) / $oldPrice) * 100) : 0;
                    $pType = $p->product_type ?? 'physical';
                    $isPhysical = $pType === 'physical';
                    $outOfStock = $isPhysical && ($p->stock ?? 0) <= 0 && !($settings->allow_backorder ?? false);
                    $rating = isset($p->rating) ? $p->rating : null;
                    $reviewCount = isset($p->review_count) ? $p->review_count : 0;
                  ?>
                  <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/product/' . $p->id); ?>" class="fx-card">
                    <div class="fx-card-img-wrap">
                      <?php if(!empty($p->item_image) && sf_product_image_exists($p->item_image)): ?>
                        <img src="<?= preg_match('~^https?://~i', (string)$p->item_image) ? $p->item_image : base_url(ltrim($p->item_image, '/')); ?>" class="fx-card-img" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy" decoding="async">
                      <?php else: ?>
                        <div class="fx-card-placeholder" aria-label="<?= htmlspecialchars($store->store_name ?? 'Restaurant'); ?>"><?= htmlspecialchars($store->store_name ?? 'Restaurant'); ?></div>
                      <?php endif; ?>
                      <?php if($hasDiscount && $discountPct > 0): ?>
                        <span class="fx-card-badge sale">-<?= $discountPct; ?>%</span>
                      <?php endif; ?>
                      <button class="fx-card-wishlist" onclick="event.stopPropagation(); event.preventDefault(); toggleWishlist(<?= $p->id; ?>);" aria-label="Add to wishlist">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                      </button>
                    </div>
                    <div class="fx-card-body">
                      <div class="fx-card-meta">
                        <?php if($rating): ?>
                        <div class="fx-card-rating">
                          <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                          <span><?= number_format($rating, 1); ?></span>
                          <?php if($reviewCount): ?>(<?= $reviewCount; ?>)<?php endif; ?>
                        </div>
                        <?php endif; ?>
                      </div>
                      <h3 class="fx-card-name"><?= htmlspecialchars($p->item_name); ?></h3>
                      <div class="fx-card-price-row">
                        <span class="fx-card-price"><?= sf_currency($price, $store_currency ?? null); ?></span>
                        <?php if($hasDiscount): ?><span class="fx-card-old-price"><?= sf_currency($oldPrice, $store_currency ?? null); ?></span><?php endif; ?>
                        <?php if($hasDiscount): ?><span class="fx-card-discount">-<?= $discountPct; ?>%</span><?php endif; ?>
                      </div>
                      <div class="fx-card-footer">
                        <button class="fx-card-add" onclick="event.stopPropagation(); event.preventDefault(); addToCart(<?= $p->id; ?>,'<?= $pType; ?>','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock ?? 0; ?>);" <?= $outOfStock ? 'disabled' : ''; ?>>
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                          <?= $outOfStock ? 'Unavailable' : 'Add'; ?>
                        </button>
                        <?php if(!empty($settings->whatsapp_number) && ($settings->allow_whatsapp ?? 1)): ?>
                        <button class="fx-card-wa" onclick="event.stopPropagation(); event.preventDefault(); orderProductOnWhatsApp('<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>);">
                          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.13 1.558 5.931L.157 24l6.305-1.654a11.882 11.882 0 0 0 5.587 1.396h.004c6.552 0 11.887-5.335 11.89-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
                          WhatsApp
                        </button>
                        <?php endif; ?>
                      </div>
                    </div>
                  </a>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
        <?php },
        'order' => 4,
    ],
    'best_sellers' => [
        'enabled' => !empty($best_sellers),
        'render' => function() use ($best_sellers, $settings, $store_currency, $store) { ?>
            <!-- Best Sellers Section -->
            <div class="fx-section" style="background:var(--fx-white);">
              <div class="fx-section-inner">
                <div class="fx-section-header">
                  <div>
                    <div class="fx-section-overline">Trending</div>
                    <h2 class="fx-section-title">Best sellers</h2>
                  </div>
                  <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>" class="fx-view-all">
                    View all <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                  </a>
                </div>
                <div class="fx-grid">
                  <?php foreach(array_slice($best_sellers, 0, 8) as $p):
                    $showSaleStockLabels = (int)($settings->show_sale_stock_labels ?? 1) === 1;
                    $price = $p->effective_price ?? $p->sales_price ?? $p->price;
                    $oldPrice = $p->original_price ?? $p->price;
                    $hasDiscount = $showSaleStockLabels && $oldPrice > $price;
                    $discountPct = $hasDiscount ? round((($oldPrice - $price) / $oldPrice) * 100) : 0;
                    $pType = $p->product_type ?? 'physical';
                    $isPhysical = $pType === 'physical';
                    $outOfStock = $isPhysical && ($p->stock ?? 0) <= 0 && !($settings->allow_backorder ?? false);
                  ?>
                  <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/product/' . $p->id); ?>" class="fx-card">
                    <div class="fx-card-img-wrap">
                      <?php if(!empty($p->item_image) && sf_product_image_exists($p->item_image)): ?>
                        <img src="<?= preg_match('~^https?://~i', (string)$p->item_image) ? $p->item_image : base_url(ltrim($p->item_image, '/')); ?>" class="fx-card-img" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy" decoding="async">
                      <?php else: ?>
                        <div class="fx-card-placeholder" aria-label="<?= htmlspecialchars($store->store_name ?? 'Restaurant'); ?>"><?= htmlspecialchars($store->store_name ?? 'Restaurant'); ?></div>
                      <?php endif; ?>
                      <span class="fx-card-badge bestseller">Best Seller</span>
                      <?php if($hasDiscount && $discountPct > 0): ?>
                        <span class="fx-card-badge sale">-<?= $discountPct; ?>%</span>
                      <?php endif; ?>
                    </div>
                    <div class="fx-card-body">
                      <h3 class="fx-card-name"><?= htmlspecialchars($p->item_name); ?></h3>
                      <div class="fx-card-price-row">
                        <span class="fx-card-price"><?= sf_currency($price, $store_currency ?? null); ?></span>
                        <?php if($hasDiscount): ?><span class="fx-card-old-price"><?= sf_currency($oldPrice, $store_currency ?? null); ?></span><?php endif; ?>
                      </div>
                      <div class="fx-card-footer">
                        <button class="fx-card-add" onclick="event.stopPropagation(); event.preventDefault(); addToCart(<?= $p->id; ?>,'<?= $pType; ?>','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock ?? 0; ?>);" <?= $outOfStock ? 'disabled' : ''; ?>>
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                          <?= $outOfStock ? 'Unavailable' : 'Add'; ?>
                        </button>
                      </div>
                    </div>
                  </a>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
        <?php },
        'order' => 5,
    ],
    'new_arrivals' => [
        'enabled' => !empty($new_arrivals),
        'render' => function() use ($new_arrivals, $settings, $store_currency, $store) { ?>
            <!-- New Arrivals Section -->
            <div class="fx-section" style="background:var(--fx-cream);">
              <div class="fx-section-inner">
                <div class="fx-section-header">
                  <div>
                    <div class="fx-section-overline">Just added to the menu</div>
                    <h2 class="fx-section-title">Worth a taste</h2>
                  </div>
                  <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>" class="fx-view-all">
                    View all <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                  </a>
                </div>
                <div class="fx-grid">
                  <?php foreach(array_slice($new_arrivals, 0, 8) as $p):
                    $showSaleStockLabels = (int)($settings->show_sale_stock_labels ?? 1) === 1;
                    $price = $p->effective_price ?? $p->sales_price ?? $p->price;
                    $oldPrice = $p->original_price ?? $p->price;
                    $hasDiscount = $showSaleStockLabels && $oldPrice > $price;
                    $discountPct = $hasDiscount ? round((($oldPrice - $price) / $oldPrice) * 100) : 0;
                    $pType = $p->product_type ?? 'physical';
                    $isPhysical = $pType === 'physical';
                    $outOfStock = $isPhysical && ($p->stock ?? 0) <= 0 && !($settings->allow_backorder ?? false);
                  ?>
                  <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/product/' . $p->id); ?>" class="fx-card">
                    <div class="fx-card-img-wrap">
                      <?php if(!empty($p->item_image) && sf_product_image_exists($p->item_image)): ?>
                        <img src="<?= preg_match('~^https?://~i', (string)$p->item_image) ? $p->item_image : base_url(ltrim($p->item_image, '/')); ?>" class="fx-card-img" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy" decoding="async">
                      <?php else: ?>
                        <div class="fx-card-placeholder" aria-label="<?= htmlspecialchars($store->store_name ?? 'Restaurant'); ?>"><?= htmlspecialchars($store->store_name ?? 'Restaurant'); ?></div>
                      <?php endif; ?>
                      <span class="fx-card-badge new">Just added</span>
                      <?php if($hasDiscount && $discountPct > 0): ?>
                        <span class="fx-card-badge sale">-<?= $discountPct; ?>%</span>
                      <?php endif; ?>
                    </div>
                    <div class="fx-card-body">
                      <h3 class="fx-card-name"><?= htmlspecialchars($p->item_name); ?></h3>
                      <div class="fx-card-price-row">
                        <span class="fx-card-price"><?= sf_currency($price, $store_currency ?? null); ?></span>
                        <?php if($hasDiscount): ?><span class="fx-card-old-price"><?= sf_currency($oldPrice, $store_currency ?? null); ?></span><?php endif; ?>
                      </div>
                      <div class="fx-card-footer">
                        <button class="fx-card-add" onclick="event.stopPropagation(); event.preventDefault(); addToCart(<?= $p->id; ?>,'<?= $pType; ?>','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock ?? 0; ?>);" <?= $outOfStock ? 'disabled' : ''; ?>>
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                          <?= $outOfStock ? 'Unavailable' : 'Add'; ?>
                        </button>
                      </div>
                    </div>
                  </a>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
        <?php },
        'order' => 6,
    ],
    'featured_services' => [
        'enabled' => !empty($featured_services) && $this->theme_engine->sellsServices() && !$fxIsRestaurant,
        'render' => function() use ($featured_services, $settings, $store_currency) { ?>
            <!-- Featured Services Section -->
            <div class="fx-section" style="background:var(--fx-white);">
              <div class="fx-section-inner">
                <div class="fx-section-header">
                  <div>
                    <div class="fx-section-overline">Beyond the menu</div>
                    <h2 class="fx-section-title">Experiences &amp; bookings</h2>
                  </div>
                  <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/services'); ?>" class="fx-view-all">
                    View all <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                  </a>
                </div>
                <div class="fx-grid">
                  <?php foreach(array_slice($featured_services, 0, 4) as $s):
                    $price = $s->effective_price ?? $s->price;
                    $oldPrice = $s->original_price ?? $s->price;
                    $hasDiscount = $oldPrice > $price;
                    $discountPct = $hasDiscount ? round((($oldPrice - $price) / $oldPrice) * 100) : 0;
                  ?>
                  <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/service/' . $s->id); ?>" class="fx-card">
                    <div class="fx-card-img-wrap">
                      <?php if($s->service_image && file_exists($s->service_image)): ?>
                        <img src="<?= base_url($s->service_image); ?>" class="fx-card-img" alt="<?= htmlspecialchars($s->service_name); ?>" loading="lazy">
                      <?php else: ?>
                        <div class="fx-card-placeholder" aria-hidden="true"><span class="fx-service-mark">SERVICE</span></div>
                      <?php endif; ?>
                      <?php if($hasDiscount && $discountPct > 0): ?>
                        <span class="fx-card-badge sale">-<?= $discountPct; ?>%</span>
                      <?php endif; ?>
                    </div>
                    <div class="fx-card-body">
                      <h3 class="fx-card-name"><?= htmlspecialchars($s->service_name); ?></h3>
                      <?php if($s->short_description): ?>
                      <p style="font-size:13px; color:var(--fx-muted); line-height:1.5; margin-bottom:10px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;"><?= htmlspecialchars($s->short_description); ?></p>
                      <?php endif; ?>
                      <div class="fx-card-price-row">
                        <span class="fx-card-price"><?= sf_currency($price, $store_currency ?? null); ?></span>
                        <?php if($hasDiscount): ?><span class="fx-card-old-price"><?= sf_currency($oldPrice, $store_currency ?? null); ?></span><?php endif; ?>
                      </div>
                      <div class="fx-card-footer">
                        <button class="fx-card-add" onclick="event.stopPropagation(); event.preventDefault();" style="background:var(--fx-cream); color:var(--fx-green); border:1px solid var(--fx-green);">
                          <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 4v8M8 12h8"/></svg>
                          Explore
                        </button>
                      </div>
                    </div>
                  </a>
                  <?php endforeach; ?>
                </div>
              </div>
            </div>
        <?php },
        'order' => 7,
    ],
    'store_info' => [
        'enabled' => true,
        'render' => function() use ($fxTrust1Title, $fxTrust1Text, $fxTrust2Title, $fxTrust2Text, $fxTrust3Title, $fxTrust3Text) { ?>
            <!-- Trust badges / value props -->
            <div class="fx-section" style="background:var(--fx-white);">
              <div class="fx-section-inner">
                  <div class="fx-benefits-grid">
                  <div style="padding:24px;">
                    <div class="fx-benefit-icon"><svg width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 17h11V6H3zM14 10h4l3 3v4h-7z"/><circle cx="7" cy="18" r="2"/><circle cx="18" cy="18" r="2"/></svg></div>
                    <h3 style="font-size:16px;font-weight:700;color:var(--fx-ink);margin-bottom:8px;"><?= htmlspecialchars($fxTrust1Title); ?></h3>
                    <p style="font-size:14px;color:var(--fx-muted);"><?= htmlspecialchars($fxTrust1Text); ?></p>
                  </div>
                  <div style="padding:24px;">
                    <div class="fx-benefit-icon"><svg width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="6" y="2.5" width="12" height="19" rx="3"/><path d="M10 18.5h4"/><path d="M9 6.5h6"/></svg></div>
                    <h3 style="font-size:16px;font-weight:700;color:var(--fx-ink);margin-bottom:8px;"><?= htmlspecialchars($fxTrust2Title); ?></h3>
                    <p style="font-size:14px;color:var(--fx-muted);"><?= htmlspecialchars($fxTrust2Text); ?></p>
                  </div>
                  <div style="padding:24px;">
                    <div class="fx-benefit-icon"><svg width="25" height="25" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 20 6v5c0 5-3.4 8.4-8 10-4.6-1.6-8-5-8-10V6z"/><path d="m9 12 2 2 4-4"/></svg></div>
                    <h3 style="font-size:16px;font-weight:700;color:var(--fx-ink);margin-bottom:8px;"><?= htmlspecialchars($fxTrust3Title); ?></h3>
                    <p style="font-size:14px;color:var(--fx-muted);"><?= htmlspecialchars($fxTrust3Text); ?></p>
                  </div>
                </div>
              </div>
            </div>
        <?php },
        'order' => 8,
    ],
];

// Merge homepage_sections config: if a section exists in homepage_sections and is_enabled=0, disable it
// If display_order is set, use it
if (!empty($homepage_sections)) {
    foreach ($homepage_sections as $key => $section) {
        $baseKey = preg_replace('/_\d+$/', '', $key);
        if (isset($fxSections[$baseKey])) {
            if (!$section->is_enabled) {
                $fxSections[$baseKey]['enabled'] = false;
            }
            if (isset($section->display_order) && $section->display_order > 0) {
                $fxSections[$baseKey]['order'] = (int)$section->display_order;
            }
        }
    }
}

// Sort sections by order
uasort($fxSections, function($a, $b) {
    return $a['order'] <=> $b['order'];
});

// Tone follows the visible builder order, including when a section is disabled.
$fxVisibleSectionIndex = 0;
foreach ($fxSections as $sectionKey => $section) {
    if (!empty($section['enabled']) && is_callable($section['render'])) {
        $fxTone = ($fxVisibleSectionIndex++ % 2 === 0) ? 'white' : 'cream';
        echo '<div class="fx-home-section fx-home-section--' . $fxTone . '" data-home-section="' . htmlspecialchars($sectionKey, ENT_QUOTES, 'UTF-8') . '">';
        $section['render']();
        echo '</div>';
    }
}
?>
