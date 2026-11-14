<?php
/**
 * Skin Core — product detail. Markup only; CSS in header.php.
 */
include APPPATH . 'views/themes/skin_core/_skin.php';

$slug  = $settings->store_slug ?? '';
$cur   = $store_currency ?? null;
$waNum = ($settings->allow_whatsapp ?? 1) ? preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? '') : '';

$img     = ($product->item_image && file_exists($product->item_image)) ? mp_minified_image_url($product->item_image, 900) : '';
$hasDisc = $product->original_price > $product->effective_price;
$pct     = $hasDisc ? round((($product->original_price - $product->effective_price) / $product->original_price) * 100) : 0;
$inStock = (int)$product->stock > 0 || !empty($settings->allow_backorder);
?>

<div class="sk-wrap">
  <div class="sk-crumbs">
    <a href="<?= base_url('store/' . $slug); ?>">Home</a>
    <span class="sep">/</span>
    <a href="<?= base_url('store/' . $slug . '/products'); ?>">Shop</a>
    <span class="sep">/</span>
    <span><?= htmlspecialchars($product->item_name); ?></span>
  </div>

  <div class="sk-pd">
    <div class="sk-pd-layout">
      <div class="sk-pd-media">
        <?php if($hasDisc && $pct > 0): ?><span class="sk-pd-badge">−<?= $pct; ?>%</span><?php endif; ?>
        <?php if($img): ?>
        <img src="<?= $img; ?>" alt="<?= htmlspecialchars($product->item_name); ?>" loading="lazy" decoding="async">
        <?php else: ?>
        <div class="sk-pd-ph"><?= htmlspecialchars(substr($product->item_name, 0, 1)); ?></div>
        <?php endif; ?>
      </div>

      <div>
        <?php if(!empty($product->category_name) || !empty($product->brand_name)): ?>
        <div class="sk-kicker"><?= htmlspecialchars($product->category_name ?? ''); ?><?= !empty($product->brand_name) ? ' &mdash; ' . htmlspecialchars($product->brand_name) : ''; ?></div>
        <?php endif; ?>
        <h1 class="sk-pd-name"><?= htmlspecialchars($product->item_name); ?></h1>
        <div class="sk-pd-price">
          <?= sf_currency($product->effective_price, $cur); ?>
          <?php if($hasDisc): ?><span class="old"><?= sf_currency($product->original_price, $cur); ?></span><?php endif; ?>
        </div>
        <div class="sk-pd-stock <?= $inStock ? 'in' : 'out'; ?>"><?= $inStock ? 'In stock' : 'Out of stock'; ?></div>
        <?php if(!empty($product->description)): ?>
        <p class="sk-pd-desc"><?= nl2br(htmlspecialchars($product->description)); ?></p>
        <?php endif; ?>

        <?php if(!empty($product->sku) || !empty($product->unit_measure) || ($product->sold_count ?? 0) > 0): ?>
        <div class="sk-pd-tags">
          <?php if(!empty($product->sku)): ?><span class="sk-pd-tag">SKU <b><?= htmlspecialchars($product->sku); ?></b></span><?php endif; ?>
          <?php if(!empty($product->unit_measure)): ?><span class="sk-pd-tag">Size <b><?= htmlspecialchars($product->unit_measure); ?></b></span><?php endif; ?>
          <?php if(($product->sold_count ?? 0) > 0): ?><span class="sk-pd-tag"><b><?= number_format($product->sold_count); ?></b> sold</span><?php endif; ?>
        </div>
        <?php endif; ?>

                <?php $this->load->view('themes/shared/variant_picker'); ?>

<div class="sk-pd-qty">
          <button onclick="skDetailQty(-1)" aria-label="Decrease">&minus;</button>
          <span id="sk-detail-qty">1</span>
          <button onclick="skDetailQty(1)" aria-label="Increase">+</button>
        </div>

        <div class="sk-pd-actions">
          <button class="sk-btn sk-btn-accent" <?= $inStock ? '' : 'disabled'; ?> onclick="skDetailAdd()">Add to Cart</button>
          <button class="sk-btn sk-btn-ghost" <?= $inStock ? '' : 'disabled'; ?> onclick="if(skDetailAdd())window.location.href='<?= base_url('store/' . $slug . '/cart'); ?>'">Buy Now</button>
          <?php if($waNum): ?>
          <button class="sk-btn sk-btn-wa-full" onclick="<?= $inStock ? "skWaOrder(skProduct.id, skProduct.name, skProduct.price, skProduct.image, skQty)" : "window.open('https://wa.me/" . $waNum . "?text='+encodeURIComponent('Hello, is '+skProduct.name+' back in stock?'),'_blank')"; ?>"><svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.13 1.558 5.931L.157 24l6.305-1.654a11.882 11.882 0 0 0 5.587 1.396h.004c6.552 0 11.887-5.335 11.89-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg><?= $inStock ? 'WhatsApp' : 'Ask about availability'; ?></button>
          <?php endif; ?>
        </div>
        <?php if(!$inStock) $this->load->view('themes/shared/restock_subscribe', ['sf_item_id' => $product->id]); ?>

        <?php
        $skTrust = json_decode($settings->trust_badges_json ?? '', true);
        if(!empty($skTrust) && is_array($skTrust)):
        ?>
        <div class="sk-pd-trust">
          <?php foreach(array_slice($skTrust, 0, 3) as $b): ?>
          <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg><?= htmlspecialchars($b['title'] ?? ''); ?></span>
          <?php endforeach; ?>
        </div>
        <?php elseif($waNum): ?>
        <div class="sk-pd-trust">
          <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>WhatsApp support</span>
          <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>Pay on delivery available</span>
        </div>
        <?php endif; ?>

        <?php if(!empty($product->category_name) || !empty($product->brand_name) || !empty($product->unit_measure)): ?>
        <div class="sk-pd-specs">
          <div class="sk-pd-specs-title">Details</div>
          <?php if(!empty($product->category_name)): ?><div class="sk-pd-spec"><span>Category</span><b><?= htmlspecialchars($product->category_name); ?></b></div><?php endif; ?>
          <?php if(!empty($product->brand_name)): ?><div class="sk-pd-spec"><span>Brand</span><b><?= htmlspecialchars($product->brand_name); ?></b></div><?php endif; ?>
          <?php if(!empty($product->unit_measure)): ?><div class="sk-pd-spec"><span>Size</span><b><?= htmlspecialchars($product->unit_measure); ?></b></div><?php endif; ?>
        </div>
        <?php endif; ?>


        <?php if(!empty($related_products)): ?>
        <div class="sk-pd-extra">
          <div class="sk-pd-specs-title">You May Also Love</div>
          <div class="sk-grid">
            <?php foreach($related_products as $p): sk_card($p, $cur, $settings, $slug); endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
  var skQty = 1;
  var skProduct = {
    id: <?= $product->id; ?>,
    name: '<?= htmlspecialchars(addslashes($product->item_name)); ?>',
    price: <?= $product->effective_price; ?>,
    image: '<?= $product->item_image; ?>',
    stock: <?= (int)$product->stock; ?>
  };
  function skDetailQty(d){ skQty = Math.max(1, skQty + d); document.getElementById('sk-detail-qty').textContent = skQty; }
  function skDetailAdd(){ return addToCart(skProduct.id, 'product', skProduct.name, skProduct.price, skProduct.image, skQty, skProduct.stock); }
</script>
