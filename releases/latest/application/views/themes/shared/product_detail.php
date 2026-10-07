<?php
$pType = $product->product_type ?? 'physical';
$isDigital = in_array($pType, ['digital','course','membership']);
$ctaLabel = [
  'digital'    => 'Get Instant Access',
  'course'     => 'Enroll Now',
  'membership' => 'Join Now',
  'service'    => 'Book Service',
  'physical'   => 'Add to Cart',
][$pType] ?? 'Add to Cart';
$badgeLabel = [
  'digital'    => 'Digital Download',
  'course'     => 'Online Course',
  'membership' => 'Membership',
  'service'    => 'Service',
  'physical'   => 'Product',
][$pType] ?? 'Product';
?>
<div class="mp-breadcrumb">
  <a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>">Home</a> &rsaquo;
  <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>">Shop</a> &rsaquo;
  <?= htmlspecialchars($product->item_name); ?>
</div>

<div class="mp-section" style="padding-top:24px;">
  <div style="display:grid; grid-template-columns:1fr; gap:24px;">
    <div>
      <?php if($product->item_image && file_exists($product->item_image)): ?>
        <img id="detail-img" src="<?= base_url($product->item_image); ?>" style="width:100%; max-height:480px; object-fit:cover; border-radius:var(--mp-radius-sm); background:var(--mp-light-gray);" alt="<?= htmlspecialchars($product->item_name); ?>">
      <?php else: ?>
        <div style="width:100%; height:360px; display:flex; align-items:center; justify-content:center; background:var(--mp-light-gray); border-radius:var(--mp-radius-sm); color:#94A3B8;">No Image</div>
      <?php endif; ?>
    </div>
    <div>
      <h1 style="font-size:28px; font-weight:800; margin-bottom:12px;"><?= htmlspecialchars($product->item_name); ?></h1>
      <div style="margin-bottom:16px;">
        <span id="detail-price" style="font-size:28px; font-weight:800; color:var(--mp-primary);"><?= sf_currency($product->effective_price, $store_currency ?? null); ?></span>
        <?php if($product->original_price > $product->effective_price): ?>
          <span style="font-size:18px; color:var(--mp-gray); text-decoration:line-through; margin-left:8px;"><?= sf_currency($product->original_price, $store_currency ?? null); ?></span>
        <?php endif; ?>
      </div>
      <?php $sfOos = ($pType === 'physical' && (int)$product->stock <= 0 && empty($settings->allow_backorder)); ?>
      <div style="font-size:14px; color:var(--mp-gray); margin-bottom:24px; display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <?php if($sfOos): ?>
        <span style="padding:3px 10px; border-radius:999px; background:rgba(239,68,68,.1); color:#B91C1C; font-weight:600; font-size:12px;">Out of stock</span>
        <?php else: ?>
        <span style="padding:3px 10px; border-radius:999px; background:rgba(5,150,105,.1); color:#047857; font-weight:600; font-size:12px;">Available</span>
        <?php endif; ?>
        <?php if($product->category_name): ?><span><?= htmlspecialchars($product->category_name); ?></span><?php endif; ?>
        <?php if($pType === 'physical'): ?>&middot; <span id="detail-stock"><?= (int)$product->stock; ?></span> in stock<?php endif; ?>
        <?php if($product->sold_count > 0): ?>
          &middot;
          <?php if($pType === 'course'): ?><?= number_format($product->sold_count); ?> student<?= $product->sold_count != 1 ? 's' : ''; ?> enrolled
          <?php elseif($pType === 'membership'): ?><?= number_format($product->sold_count); ?> member<?= $product->sold_count != 1 ? 's' : ''; ?> joined
          <?php else: ?><?= number_format($product->sold_count); ?> sold
          <?php endif; ?>
        <?php endif; ?>
      </div>
      <div style="font-size:15px; line-height:1.7; color:#334155; margin-bottom:32px;">
        <?= nl2br(htmlspecialchars($product->description ?? '')); ?>
      </div>

      <?php $this->load->view('themes/shared/variant_picker', ['sf_picker_mode' => 'select']); ?>

      <?php if($pType === 'physical'): ?>
      <div style="display:flex; align-items:center; gap:16px; margin-bottom:24px;">
        <button onclick="adjustDetailQty(-1)" style="width:44px; height:44px; border-radius:50%; border:1px solid var(--mp-border); background:#fff; font-size:20px; cursor:pointer;">-</button>
        <span id="detail-qty" style="font-size:20px; font-weight:700; min-width:30px; text-align:center;">1</span>
        <button onclick="adjustDetailQty(1)" style="width:44px; height:44px; border-radius:50%; border:1px solid var(--mp-border); background:#fff; font-size:20px; cursor:pointer;">+</button>
      </div>
      <?php endif; ?>

      <button id="detail-add-btn" onclick="addDetailToCart()" <?= $sfOos ? 'disabled' : ''; ?> style="width:100%; padding:16px; border-radius:var(--mp-radius-sm); background:var(--mp-primary); color:#fff; font-weight:700; border:none; cursor:pointer; font-size:16px; margin-bottom:12px; <?= $sfOos ? 'opacity:.5;cursor:not-allowed;' : ''; ?>"><?= $sfOos ? 'Out of Stock' : $ctaLabel; ?></button>
      <?php if($sfOos) $this->load->view('themes/shared/restock_subscribe', ['sf_item_id' => $product->id]); ?>
      <?php
      // Delivery information for physical products — from merchant shipping config
      if($pType === 'physical'):
        $sfShipMethods = array_values(array_filter(json_decode($settings->shipping_methods_json ?? '[]', true) ?: [], function($m){ return !empty($m['enabled']); }));
      ?>
      <?php if(!empty($settings->shipping_notice) || !empty($sfShipMethods) || !empty($settings->city_shipping_enabled)): ?>
      <div style="font-size:13px; color:var(--mp-gray); background:var(--mp-light-gray); border-radius:var(--mp-radius-sm); padding:12px 14px; margin-bottom:12px; line-height:1.6;">
        <strong style="color:var(--mp-dark);">Delivery</strong><br>
        <?php if(!empty($settings->city_shipping_enabled)): ?>
          Delivery fee is set by your city — you'll choose at checkout.
        <?php elseif(!empty($sfShipMethods)): ?>
          <?= htmlspecialchars(implode(' · ', array_map(function($m){
            return ($m['name'] ?? 'Delivery') . (!empty($m['quote']) ? ' (quoted after order)' : (((float)($m['fee'] ?? 0)) > 0 ? '' : ' — free'));
          }, $sfShipMethods))); ?>
        <?php endif; ?>
        <?php if(!empty($settings->shipping_notice)): ?><br><?= htmlspecialchars($settings->shipping_notice); ?><?php endif; ?>
      </div>
      <?php endif; ?>
      <?php endif; ?>
      <?php if($pType === 'physical' && !empty($settings->whatsapp_number)): ?>
      <button id="detail-wa-btn" onclick="sendDetailWhatsApp()" style="width:100%; padding:16px; border-radius:var(--mp-radius-sm); background:#25D366; color:#fff; font-weight:700; border:none; cursor:pointer; font-size:16px;"><?= $sfOos ? 'Ask about availability' : 'Order via WhatsApp'; ?></button>
      <?php endif; ?>
    </div>
  </div>
</div>



<?php if(!empty($related_products)): ?>
<div class="mp-section" style="padding-top:0;">
  <div class="mp-section-title">You May Also Like</div>
  <div class="mp-grid">
    <?php foreach($related_products as $p): ?>
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/product/' . $p->id); ?>" class="mp-card">
      <?php if($p->item_image && file_exists($p->item_image)): ?>
        <img src="<?= base_url($p->item_image); ?>" class="mp-card-img" alt="" loading="lazy">
      <?php else: ?>
        <div class="mp-card-img" style="display:flex;align-items:center;justify-content:center;color:#94A3B8;font-size:10px;">No Image</div>
      <?php endif; ?>
      <div class="mp-card-body">
        <div class="mp-card-name"><?= htmlspecialchars($p->item_name); ?></div>
        <div class="mp-card-price"><?= sf_currency($p->sales_price, $store_currency ?? null); ?></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<script>
  let detailQty = 1;
  const detailProduct = {
    id: <?= $product->id; ?>,
    type: '<?= $pType; ?>',
    name: '<?= htmlspecialchars(addslashes($product->item_name)); ?>',
    price: <?= $product->effective_price; ?>,
    image: '<?= $product->item_image; ?>',
    stock: <?= (int)$product->stock; ?>
  };

  function adjustDetailQty(d){
    detailQty = Math.max(1, detailQty + d);
    document.getElementById('detail-qty').textContent = detailQty;
  }

  const needsVariant = <?= (($product->item_group ?? '') === 'Variants') ? 'true' : 'false'; ?>;

  document.addEventListener('sf:variant-picked', function(e){
    const v = e.detail;
    if(!v) return;
    detailProduct.id = v.id;
    detailProduct.name = v.name;
    detailProduct.price = v.price;
    detailProduct.image = v.image;
    detailProduct.stock = v.stock;
    const img = document.getElementById('detail-img');
    if(img && v.image) img.src = '<?= base_url(); ?>' + v.image;
    const price = document.getElementById('detail-price');
    if(price) price.textContent = formatMoney(v.price);
    const stock = document.getElementById('detail-stock');
    if(stock) stock.textContent = v.stock;
    const vBtn = document.getElementById('detail-add-btn');
    const vOos = detailProduct.type !== 'service' && (detailProduct.type === 'product' || detailProduct.type === 'physical') && v.stock <= 0 && !<?= ($settings->allow_backorder ?? false) ? 'true' : 'false'; ?>;
    if(vBtn){ vBtn.disabled = vOos; vBtn.textContent = vOos ? 'Out of Stock' : '<?= $ctaLabel; ?>'; vBtn.style.opacity = vOos ? '.5' : ''; vBtn.style.cursor = vOos ? 'not-allowed' : ''; }
    const vWa = document.getElementById('detail-wa-btn'); if(vWa && detailProduct.type !== 'service') vWa.textContent = vOos ? 'Ask about availability' : 'Order via WhatsApp';
  });

  function addDetailToCart(){
    const p = window.sfPickedVariant || detailProduct;
    if(needsVariant && !window.sfPickedVariant){
      showToast('Please choose an option');
      return;
    }
    addToCart(p.id, detailProduct.type, p.name, p.price, p.image, detailQty, p.stock);
  }

  function sendDetailWhatsApp(){
    if(typeof detailProduct !== 'undefined' && detailProduct.stock !== undefined && detailProduct.stock <= 0 && (!detailProduct.type || detailProduct.type === 'product' || detailProduct.type === 'physical') && !<?= ($settings->allow_backorder ?? false) ? 'true' : 'false'; ?>){ const wnum2 = '<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? ''); ?>'; if(wnum2) window.open('https://wa.me/' + wnum2 + '?text=' + encodeURIComponent('Hello, is ' + detailProduct.name + ' back in stock?'), '_blank'); return; }
    let msg = 'Hello, I am interested in: ' + detailProduct.name + ' — ' + formatMoney(detailProduct.price);
    const wnum = '<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? ''); ?>';
    if(wnum) window.open('https://wa.me/' + wnum + '?text=' + encodeURIComponent(msg), '_blank');
  }

  // Funnel event — consent-gated inside mpTrackEvent.
  if(typeof mpTrackEvent === 'function') mpTrackEvent('view_item', detailProduct.id, detailProduct.price);
</script>
