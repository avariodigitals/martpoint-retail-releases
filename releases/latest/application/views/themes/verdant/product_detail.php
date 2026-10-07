<?php
/**
 * Verdant — product detail. Markup only; CSS/JS in header.php.
 */
include APPPATH . 'views/themes/verdant/_theme.php';

$slug  = $settings->store_slug ?? '';
$cur   = $store_currency ?? null;
$waNum = ($settings->allow_whatsapp ?? 1) ? preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? '') : '';

$img     = ($product->item_image && file_exists($product->item_image)) ? mp_minified_image_url($product->item_image, 900) : '';
$hasDisc = $product->original_price > $product->effective_price;
$pct     = $hasDisc ? round((($product->original_price - $product->effective_price) / $product->original_price) * 100) : 0;
$inStock = (int)$product->stock > 0 || !empty($settings->allow_backorder);
$vdTrust = json_decode($settings->trust_badges_json ?? '', true);
if(!is_array($vdTrust)) $vdTrust = [];
?>

<div class="vd-wrap">
  <nav class="vd-crumbs" aria-label="Breadcrumb">
    <a href="<?= base_url('store/' . $slug); ?>">Home</a>
    <span class="sep">/</span>
    <a href="<?= base_url('store/' . $slug . '/products'); ?>">Shop</a>
    <span class="sep">/</span>
    <span><?= htmlspecialchars($product->item_name); ?></span>
  </nav>

  <div class="vd-pd">
    <div class="vd-pd-layout">
      <div class="vd-pd-media">
        <?php if($hasDisc && $pct > 0): ?><span class="vd-tag vd-tag-sale">−<?= $pct; ?>%</span><?php endif; ?>
        <?php if($img): ?>
        <img src="<?= $img; ?>" alt="<?= htmlspecialchars($product->item_name); ?>" fetchpriority="high" decoding="async">
        <?php else: ?>
        <span class="vd-card-ph"><?= htmlspecialchars(mb_substr($product->item_name, 0, 1)); ?></span>
        <?php endif; ?>
      </div>

      <div class="vd-pd-copy">
        <?php if(!empty($product->category_name) || !empty($product->brand_name)): ?>
        <span class="vd-kicker"><?= htmlspecialchars($product->category_name ?? ''); ?><?= !empty($product->brand_name) ? ' · ' . htmlspecialchars($product->brand_name) : ''; ?></span>
        <?php endif; ?>
        <h1 class="vd-display vd-pd-name"><?= htmlspecialchars($product->item_name); ?></h1>
        <div class="vd-pd-price">
          <?= sf_currency($product->effective_price, $cur); ?>
          <?php if($hasDisc): ?><s><?= sf_currency($product->original_price, $cur); ?></s><?php endif; ?>
        </div>
        <span class="vd-pd-stock <?= $inStock ? 'in' : 'out'; ?>"><?= $inStock ? 'In stock' : 'Out of stock'; ?></span>

        <?php if(!empty($product->description)): ?>
        <p class="vd-pd-desc"><?= nl2br(htmlspecialchars($product->description)); ?></p>
        <?php endif; ?>

        <div class="vd-pd-buy">
          <div class="vd-pd-buy-row">
                    <?php $this->load->view('themes/shared/variant_picker'); ?>

<div class="vd-qty">
              <button type="button" onclick="vdDetailQty(-1)" aria-label="Decrease">&minus;</button>
              <span id="vd-detail-qty">1</span>
              <button type="button" onclick="vdDetailQty(1)" aria-label="Increase">+</button>
            </div>
            <button type="button" class="vd-btn vd-btn-primary" <?= $inStock ? '' : 'disabled'; ?> onclick="vdDetailAdd()">Add to bag</button>
          </div>
          <div class="vd-pd-buy-row">
            <button type="button" class="vd-btn vd-btn-ghost" <?= $inStock ? '' : 'disabled'; ?> onclick="if(vdDetailAdd())window.location.href='<?= base_url('store/' . $slug . '/cart'); ?>'">Buy now</button>
            <?php if($waNum): ?>
            <button type="button" class="vd-btn vd-btn-wa" onclick="<?= $inStock ? "vdWaOrder(vdProduct.id, vdProduct.name, vdProduct.price, vdProduct.image, vdQty)" : "window.open('https://wa.me/" . $waNum . "?text='+encodeURIComponent('Hello, is '+vdProduct.name+' back in stock?'),'_blank')"; ?>"><?= vd_wa_svg(); ?> <?= $inStock ? 'WhatsApp' : 'Ask about availability'; ?></button>
            <?php endif; ?>
          </div>
          <?php if(!$inStock) $this->load->view('themes/shared/restock_subscribe', ['sf_item_id' => $product->id]); ?>
        </div>

        <?php if(!empty($vdTrust)): ?>
        <div class="vd-pd-trust">
          <?php foreach(array_slice($vdTrust, 0, 3) as $b): ?>
          <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg><?= htmlspecialchars($b['title'] ?? ''); ?></span>
          <?php endforeach; ?>
        </div>
        <?php elseif($waNum): ?>
        <div class="vd-pd-trust">
          <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>WhatsApp support</span>
          <span><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"><polyline points="20 6 9 17 4 12"/></svg>Pay on delivery available</span>
        </div>
        <?php endif; ?>

        <?php if(!empty($product->category_name) || !empty($product->brand_name) || !empty($product->unit_measure) || !empty($product->sku)): ?>
        <div class="vd-pd-specs">
          <div class="vd-pd-specs-title">Details</div>
          <?php if(!empty($product->category_name)): ?><div class="vd-pd-spec"><span>Category</span><b><?= htmlspecialchars($product->category_name); ?></b></div><?php endif; ?>
          <?php if(!empty($product->brand_name)): ?><div class="vd-pd-spec"><span>Brand</span><b><?= htmlspecialchars($product->brand_name); ?></b></div><?php endif; ?>
          <?php if(!empty($product->unit_measure)): ?><div class="vd-pd-spec"><span>Size</span><b><?= htmlspecialchars($product->unit_measure); ?></b></div><?php endif; ?>
          <?php if(!empty($product->sku)): ?><div class="vd-pd-spec"><span>SKU</span><b><?= htmlspecialchars($product->sku); ?></b></div><?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    </div>


    <?php if(!empty($related_products)): ?>
    <div class="vd-pd-extra">
      <div class="vd-sec-head"><div><h2 class="vd-h3">You may also love</h2></div>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="vd-link">View all <?= vd_arrow_svg(); ?></a></div>
      <div class="vd-grid">
        <?php foreach($related_products as $p): vd_card($p, $cur, $settings, $slug); endforeach; ?>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
  var vdQty = 1;
  var vdProduct = {
    id: <?= $product->id; ?>,
    name: '<?= htmlspecialchars(addslashes($product->item_name)); ?>',
    price: <?= $product->effective_price; ?>,
    image: '<?= $product->item_image; ?>',
    stock: <?= (int)$product->stock; ?>
  };
  function vdDetailQty(d){ vdQty = Math.max(1, vdQty + d); document.getElementById('vd-detail-qty').textContent = vdQty; }
  function vdDetailAdd(){ return addToCart(vdProduct.id, 'product', vdProduct.name, vdProduct.price, vdProduct.image, vdQty, vdProduct.stock); }
</script>
