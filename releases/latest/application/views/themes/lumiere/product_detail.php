<?php
require_once APPPATH . 'views/themes/lumiere/_helpers.php';
$lmSlug = $settings->store_slug ?? '';
$lmHome = base_url('store/' . $lmSlug);
$lmImage = lm_item_image($product->item_image ?? '', 1000);
$lmPrice = (float) ($product->effective_price ?? $product->sales_price ?? 0);
$lmOld = (float) ($product->original_price ?? 0);
$lmStock = (int) ($product->stock ?? 0);
$lmType = $product->product_type ?? 'physical';
$lmAvailable = $lmType !== 'physical' || $lmStock > 0 || !empty($settings->allow_backorder);
?>
<main class="lm-inner"><div class="wrap">
  <div class="lm-breadcrumb"><a href="<?= lm_escape($lmHome); ?>">Home</a><span>›</span><a href="<?= lm_escape($lmHome . '/products'); ?>">Shop Skincare</a><span>›</span><?= lm_escape($product->item_name); ?></div>
  <div class="lm-detail">
    <div class="lm-detail-image"><?php if ($lmImage): ?><img id="lm-detail-image" src="<?= lm_escape($lmImage); ?>" alt="<?= lm_escape($product->item_name); ?>"><?php else: ?><span class="lm-image-placeholder">LUMIÈRE</span><?php endif; ?></div>
    <div class="lm-detail-copy">
      <div class="eyebrow"><?= lm_escape($product->category_name ?? 'THE LUMIÈRE EDIT'); ?></div>
      <h1><?= lm_escape($product->item_name); ?></h1>
      <div class="lm-detail-price" id="lm-detail-price"><?= sf_currency($lmPrice, $store_currency ?? null); ?><?php if ($lmOld > $lmPrice): ?><span class="old"><?= sf_currency($lmOld, $store_currency ?? null); ?></span><?php endif; ?></div>
      <p class="lm-detail-description"><?= nl2br(lm_escape($product->description ?? '')); ?></p>
      <div class="lm-availability"><?= $lmAvailable ? 'Available to order' : 'Currently unavailable'; ?></div>
      <?php $this->load->view('themes/shared/variant_picker', ['sf_picker_mode' => 'select']); ?>
      <div class="lm-detail-buy"><div class="lm-quantity"><button type="button" id="lm-qty-minus" aria-label="Decrease quantity">−</button><span id="lm-qty">1</span><button type="button" id="lm-qty-plus" aria-label="Increase quantity">+</button></div><button type="button" class="btn" id="lm-detail-add" <?= $lmAvailable ? '' : 'disabled'; ?>><?= $lmAvailable ? 'Add to Bag ↗' : 'Out of Stock'; ?></button></div>
      <?php if (!$lmAvailable) $this->load->view('themes/shared/restock_subscribe', ['sf_item_id' => $product->id]); ?>
      <p class="lm-detail-note">Secure checkout · Carefully considered care</p>
    </div>
  </div>
  <?php if (!empty($related_products)): ?><section class="section lm-related"><div class="heading"><div><div class="eyebrow">COMPLETE YOUR ROUTINE</div><h2>You may also love.</h2></div><a class="text-link" href="<?= lm_escape($lmHome . '/products'); ?>">Shop All Products ↗</a></div><div class="products-grid"><?php foreach (array_slice($related_products, 0, 4) as $lmProduct): if ((int) $lmProduct->id !== (int) $product->id) lm_product_card($lmProduct, $lmSlug, $store_currency ?? null, $settings); endforeach; ?></div></section><?php endif; ?>
</div></main>
<script>
(function(){
  var item=<?= json_encode(['id'=>(int)$product->id,'type'=>$lmType,'name'=>$product->item_name,'price'=>$lmPrice,'image'=>$product->item_image ?? '','stock'=>$lmStock], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
  var qty=1, itemType=item.type, needsVariant=<?= (($product->item_group ?? '') === 'Variants') ? 'true' : 'false'; ?>;
  document.getElementById('lm-qty-minus').addEventListener('click',function(){qty=Math.max(1,qty-1);document.getElementById('lm-qty').textContent=qty;});
  document.getElementById('lm-qty-plus').addEventListener('click',function(){qty++;document.getElementById('lm-qty').textContent=qty;});
  document.addEventListener('sf:variant-picked',function(e){if(!e.detail)return; item=e.detail; var image=document.getElementById('lm-detail-image');if(image&&item.image)image.src='<?= base_url(); ?>'+item.image; document.getElementById('lm-detail-price').textContent=formatMoney(item.price); var button=document.getElementById('lm-detail-add');button.disabled=item.stock<=0&&!<?= !empty($settings->allow_backorder) ? 'true' : 'false'; ?>;button.textContent=button.disabled?'Out of Stock':'Add to Bag ↗';});
  document.getElementById('lm-detail-add').addEventListener('click',function(){if(needsVariant&&!window.sfPickedVariant){showToast('Please choose an option first');return;}var p=window.sfPickedVariant||item;addToCart(p.id,itemType,p.name,p.price,p.image,qty,p.stock);});
  if(typeof mpTrackEvent==='function')mpTrackEvent('view_item',item.id,item.price);
})();
</script>
