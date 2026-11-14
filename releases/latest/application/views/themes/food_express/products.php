<?php
$categories = $categories ?? [];
$products = $products ?? [];
$currentCategory = $category_id ?? null;
$searchTerm = $search ?? '';
$fxCategoriesTitle = $fxCopy('categories_title') ?: 'Find your favourites';
$fxAllImage = '';
$fxMenuLabel = 'All menu items';
if($currentCategory && !empty($categories)){
  foreach($categories as $fxCategoryLabel){
    if((int)$fxCategoryLabel->id === (int)$currentCategory){
      $fxMenuLabel = (string)$fxCategoryLabel->category_name;
      break;
    }
  }
}
foreach($products as $fxProductImageCandidate){
  if(!empty($fxProductImageCandidate->item_image) && sf_product_image_exists($fxProductImageCandidate->item_image)){
    $fxAllImage = preg_match('~^https?://~i', (string)$fxProductImageCandidate->item_image)
      ? $fxProductImageCandidate->item_image
      : base_url(ltrim($fxProductImageCandidate->item_image, '/'));
    break;
  }
}
?>
<div class="fx-section" style="padding-top:24px;">
  <div class="fx-section-inner">
    <?php if($searchTerm || $currentCategory): ?>
    <div class="fx-section-header" style="margin-bottom:16px;">
      <div>
        <?php if($searchTerm): ?>
        <div class="fx-section-overline">Search results for</div>
        <h1 class="fx-section-title">"<?= htmlspecialchars($searchTerm); ?>"</h1>
        <p class="fx-section-subtitle"><?= count($products); ?> items found</p>
        <?php elseif($currentCategory && !empty($categories)): 
          $cat = null; foreach($categories as $c){ if($c->id == $currentCategory){ $cat=$c; break; } } ?>
        <div class="fx-section-overline">Category</div>
        <h1 class="fx-section-title"><?= htmlspecialchars($cat->category_name ?? 'Category'); ?></h1>
        <p class="fx-section-subtitle"><?= count($products); ?> items</p>
        <?php endif; ?>
      </div>
    </div>
    <?php else: ?>
    <div class="fx-section-header" style="margin-bottom:24px;">
      <div>
        <div class="fx-section-overline">Our Menu</div>
        <h1 class="fx-section-title"><?= htmlspecialchars($fxCategoriesTitle); ?></h1>
        <p class="fx-section-subtitle">Browse categories or search for your craving</p>
      </div>
    </div>
    <?php endif; ?>

    <?php if(!empty($categories) && !$searchTerm): ?>
    <div class="fx-categories" style="margin-bottom:32px;">
      <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>" class="fx-cat-tile fx-cat-all" style="opacity:<?= !$currentCategory ? '1' : '0.5'; ?>">
        <div class="fx-cat-img-wrap">
          <?php if($fxAllImage): ?><img src="<?= htmlspecialchars($fxAllImage); ?>" alt="All menu items" class="fx-cat-img" loading="lazy"><?php else: ?><div class="fx-cat-img-placeholder"><span>ALL</span></div><?php endif; ?>
        </div>
        <div class="fx-cat-name">All</div>
      </a>
      <?php foreach(array_slice($categories, 0, 10) as $cat): ?>
      <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products?category=' . $cat->id); ?>" class="fx-cat-tile <?= ($cat->id == $currentCategory) ? 'active' : ''; ?>" style="opacity:<?= $currentCategory && $cat->id != $currentCategory ? '0.4' : '1'; ?>">
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
    <?php endif; ?>

    <?php if(!empty($products)): ?>
    <div class="fx-menu-rule"><span><?= htmlspecialchars($fxMenuLabel); ?></span><small><?= count($products); ?> <?= count($products) === 1 ? 'item' : 'items'; ?></small></div>
    <div class="fx-menu-list" aria-label="Menu items">
      <?php foreach($products as $p):
        $showSaleStockLabels = (int)($settings->show_sale_stock_labels ?? 1) === 1;
        $price = $p->effective_price ?? $p->sales_price ?? $p->price;
        $oldPrice = $p->original_price ?? $p->price;
        $hasDiscount = $showSaleStockLabels && $oldPrice > $price;
        $discountPct = $hasDiscount ? round((($oldPrice - $price) / $oldPrice) * 100) : 0;
        $pType = $p->product_type ?? 'physical';
        $isPhysical = $pType === 'physical';
        $outOfStock = $isPhysical && ($p->stock ?? 0) <= 0 && !($settings->allow_backorder ?? false);
      ?>
<?php $fxDescription = trim(strip_tags((string)($p->short_description ?? ($p->description ?? '')))); if (preg_match('/^Demo .* product from Zonat\.$/i', $fxDescription)) { $fxDescription = ''; } ?>
      <article class="fx-menu-item">
        <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/product/' . $p->id); ?>" class="fx-menu-item-image">
          <?php if(!empty($p->item_image) && sf_product_image_exists($p->item_image)): ?>
            <img src="<?= preg_match('~^https?://~i', (string)$p->item_image) ? $p->item_image : base_url(ltrim($p->item_image, '/')); ?>" class="fx-card-img" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy" decoding="async">
          <?php else: ?>
            <div class="fx-card-placeholder" aria-label="<?= htmlspecialchars($store->store_name ?? 'Restaurant'); ?>"><?= htmlspecialchars($store->store_name ?? 'Restaurant'); ?></div>
          <?php endif; ?>
          <?php if($hasDiscount && $discountPct > 0): ?>
            <span class="fx-card-badge sale">-<?= $discountPct; ?>%</span>
          <?php endif; ?>
        </a>
        <div class="fx-menu-item-body">
          <h3 class="fx-card-name"><?= htmlspecialchars($p->item_name); ?></h3>
          <?php if($fxDescription !== ''): ?><p class="fx-menu-item-description"><?= htmlspecialchars($fxDescription); ?></p><?php endif; ?>
          <div class="fx-card-price-row">
            <span class="fx-card-price"><?= sf_currency($price, $store_currency ?? null); ?></span>
            <?php if($hasDiscount): ?><span class="fx-card-old-price"><?= sf_currency($oldPrice, $store_currency ?? null); ?></span><?php endif; ?>
            <?php if($hasDiscount): ?><span class="fx-card-discount">-<?= $discountPct; ?>%</span><?php endif; ?>
          </div>
        </div>
        <div class="fx-menu-item-action">
          <button class="fx-card-add" onclick="event.stopPropagation(); addToCart(<?= $p->id; ?>,'<?= $pType; ?>','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock ?? 0; ?>);" <?= $outOfStock ? 'disabled' : ''; ?>>
            <?= $outOfStock ? 'Unavailable' : 'Add to order'; ?>
          </button>
        </div>
      </article>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div style="text-align:center; padding:60px 20px; color:var(--fx-muted);">
      <div class="fx-empty-mark" aria-hidden="true"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><circle cx="11" cy="11" r="6.5"/><path d="m16 16 5 5M8 11h6"/></svg></div>
      <h3 style="font-size:20px; font-weight:700; color:var(--fx-ink); margin-bottom:8px;">Nothing here yet</h3>
      <p>No menu items match your search. Try a different category or search term.</p>
      <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>" class="fx-card-add" style="display:inline-flex; margin-top:16px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
        Browse the menu
      </a>
    </div>
    <?php endif; ?>
  </div>
</div>
