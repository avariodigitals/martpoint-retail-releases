<?php
  $cardProduct = $card_product ?? null;
  if(!$cardProduct) return;
  $cardIndex = max(0, (int)($card_index ?? 0));
  $cardTotal = max(1, (int)($card_total ?? 1));
  $cardCategory = trim((string)($cardProduct->category_name ?? ''));
  $cardDesc = trim(preg_replace('/\s+/u', ' ', strip_tags((string)($cardProduct->description ?? ''))));
  if(mb_strlen($cardDesc, 'UTF-8') > 165) $cardDesc = mb_substr($cardDesc, 0, 162, 'UTF-8') . '…';
  $cardImg = mp_building_image_url($cardProduct->item_image ?? '');
  $cardPrice = $cardProduct->effective_price ?? $cardProduct->sales_price ?? 0;
  $cardOld = $cardProduct->original_price ?? null;
  $cardUrl = base_url('store/' . ($settings->store_slug ?? '') . '/product/' . (int)$cardProduct->id);
  $cardStock = (int)($cardProduct->stock ?? 0);
  $cardPhysical = ($cardProduct->product_type ?? 'physical') === 'physical';
?>
<a class="product-card" href="<?= $cardUrl; ?>">
  <div class="photo-frame">
    <?php if($cardImg !== ''): ?>
    <img src="<?= htmlspecialchars($cardImg, ENT_QUOTES, 'UTF-8'); ?>" loading="<?= !empty($card_eager) ? 'eager' : 'lazy'; ?>" alt="<?= htmlspecialchars($cardProduct->item_name ?? '', ENT_QUOTES, 'UTF-8'); ?>">
    <?php else: ?>
    <div class="bm-media-fallback" role="img" aria-label="<?= htmlspecialchars($cardProduct->item_name ?? '', ENT_QUOTES, 'UTF-8'); ?>"><span aria-hidden="true"><?= htmlspecialchars(mp_building_mark($bm_brand ?? 'M'), ENT_QUOTES, 'UTF-8'); ?></span></div>
    <?php endif; ?>
    <span class="image-arrow" aria-hidden="true">↗</span>
  </div>
  <div class="meta-line">
    <span><?= $cardCategory !== '' ? htmlspecialchars($cardCategory, ENT_QUOTES, 'UTF-8') : 'System'; ?></span>
    <span><?= sprintf('%02d / %02d', $cardIndex + 1, $cardTotal); ?></span>
  </div>
  <h3><?= htmlspecialchars($cardProduct->item_name ?? '', ENT_QUOTES, 'UTF-8'); ?></h3>
  <?php if($cardDesc !== ''): ?><p><?= htmlspecialchars($cardDesc, ENT_QUOTES, 'UTF-8'); ?></p><?php endif; ?>
  <?php if(!empty($bm_show_prices)): ?>
  <div class="card-price">
    <span><?= sf_currency($cardPrice, $store_currency ?? null); ?></span>
    <?php if($cardOld !== null && (float)$cardOld > (float)$cardPrice): ?><s><?= sf_currency($cardOld, $store_currency ?? null); ?></s><?php endif; ?>
    <?php if($cardPhysical && $cardStock <= 0): ?><small>Out of stock</small><?php endif; ?>
  </div>
  <?php else: ?>
  <span class="card-request">Request quotation <span aria-hidden="true">↗</span></span>
  <?php endif; ?>
</a>
