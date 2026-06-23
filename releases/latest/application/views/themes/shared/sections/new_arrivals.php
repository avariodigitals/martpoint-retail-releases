<?php $na = $new_arrivals ?? []; ?>
<?php if(!empty($na)): ?>
<div class="mp-section">
  <div class="mp-section-title">
    New Arrivals
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>">View all &rarr;</a>
  </div>
  <div class="mp-grid">
    <?php foreach($na as $p):
      $price = $p->effective_price ?? $p->sales_price;
      $oldPrice = $p->original_price ?? $p->sales_price;
      $hasDiscount = $oldPrice > $price;
      $discountPct = $hasDiscount ? round((($oldPrice - $price) / $oldPrice) * 100) : 0;
    ?>
    <div class="mp-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, <?= $hasDiscount ? $oldPrice : 0; ?>)">
      <div class="mp-card-img-wrap">
        <?php if($p->item_image && file_exists($p->item_image)): ?>
          <img src="<?= base_url($p->item_image); ?>" class="mp-card-img" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
        <?php else: ?>
          <div class="mp-card-placeholder">
            <svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2"><rect x="8" y="12" width="48" height="40" rx="4"/><path d="M8 40l12-12 10 10 14-14 12 12"/></svg>
            <span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span>
          </div>
        <?php endif; ?>
        <span class="mp-card-badge new">New</span>
        <?php if($hasDiscount && $discountPct > 0): ?>
          <span class="mp-card-badge discount">-<?= $discountPct; ?>%</span>
        <?php endif; ?>
        <button class="mp-card-wishlist" onclick="event.stopPropagation();"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></button>
      </div>
      <div class="mp-card-body">
        <div class="mp-card-name"><?= htmlspecialchars($p->item_name); ?></div>
        <div style="display:flex;align-items:center;">
          <span class="mp-card-price"><?= sf_currency($price, $store_currency ?? null); ?></span>
          <?php if($hasDiscount): ?><span class="mp-card-old"><?= sf_currency($oldPrice, $store_currency ?? null); ?></span><?php endif; ?>
        </div>
        <?php if($p->stock <= 0 && !($settings->allow_backorder ?? false)): ?>
          <div class="mp-card-stock">Out of Stock</div>
        <?php endif; ?>
        <button class="mp-card-add" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)">
          <span>+</span> Add to Cart
        </button>
        <?php if(!empty($settings->whatsapp_number)): ?>
        <button class="mp-card-wa" onclick="event.stopPropagation();orderProductOnWhatsApp('<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>)">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.13 1.558 5.931L.157 24l6.305-1.654a11.882 11.882 0 0 0 5.587 1.396h.004c6.552 0 11.887-5.335 11.89-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg> Order on WhatsApp
        </button>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>
