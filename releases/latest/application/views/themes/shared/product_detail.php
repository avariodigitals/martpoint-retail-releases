<div class="mp-breadcrumb">
  <a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>">Home</a> &rsaquo;
  <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>">Shop</a> &rsaquo;
  <?= htmlspecialchars($product->item_name); ?>
</div>

<div class="mp-section" style="padding-top:24px;">
  <div style="display:grid; grid-template-columns:1fr; gap:24px;">
    <div>
      <?php if($product->item_image && file_exists($product->item_image)): ?>
        <img src="<?= base_url($product->item_image); ?>" style="width:100%; max-height:480px; object-fit:cover; border-radius:var(--mp-radius-sm); background:var(--mp-light-gray);" alt="<?= htmlspecialchars($product->item_name); ?>">
      <?php else: ?>
        <div style="width:100%; height:360px; display:flex; align-items:center; justify-content:center; background:var(--mp-light-gray); border-radius:var(--mp-radius-sm); color:#94A3B8;">No Image</div>
      <?php endif; ?>
    </div>
    <div>
      <h1 style="font-size:28px; font-weight:800; margin-bottom:12px;"><?= htmlspecialchars($product->item_name); ?></h1>
      <div style="margin-bottom:16px;">
        <span style="font-size:28px; font-weight:800; color:var(--mp-primary);"><?= sf_currency($product->effective_price, $store_currency ?? null); ?></span>
        <?php if($product->original_price > $product->effective_price): ?>
          <span style="font-size:18px; color:var(--mp-gray); text-decoration:line-through; margin-left:8px;"><?= sf_currency($product->original_price, $store_currency ?? null); ?></span>
        <?php endif; ?>
      </div>
      <div style="font-size:14px; color:var(--mp-gray); margin-bottom:24px;">
        <?= $product->category_name ? 'Category: ' . htmlspecialchars($product->category_name) : ''; ?> &middot; <?= (int)$product->stock; ?> in stock
      </div>
      <div style="font-size:15px; line-height:1.7; color:#334155; margin-bottom:32px;">
        <?= nl2br(htmlspecialchars($product->description ?? '')); ?>
      </div>

      <div style="display:flex; align-items:center; gap:16px; margin-bottom:24px;">
        <button onclick="adjustDetailQty(-1)" style="width:44px; height:44px; border-radius:50%; border:1px solid var(--mp-border); background:#fff; font-size:20px; cursor:pointer;">-</button>
        <span id="detail-qty" style="font-size:20px; font-weight:700; min-width:30px; text-align:center;">1</span>
        <button onclick="adjustDetailQty(1)" style="width:44px; height:44px; border-radius:50%; border:1px solid var(--mp-border); background:#fff; font-size:20px; cursor:pointer;">+</button>
      </div>

      <button id="detail-add-btn" onclick="addDetailToCart()" style="width:100%; padding:16px; border-radius:var(--mp-radius-sm); background:var(--mp-primary); color:#fff; font-weight:700; border:none; cursor:pointer; font-size:16px; margin-bottom:12px;">Add to Cart</button>
      <?php if(!empty($settings->whatsapp_number)): ?>
      <button onclick="sendDetailWhatsApp()" style="width:100%; padding:16px; border-radius:var(--mp-radius-sm); background:#25D366; color:#fff; font-weight:700; border:none; cursor:pointer; font-size:16px;">Order via WhatsApp</button>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if(!empty($related_products)): ?>
<div class="mp-section" style="padding-top:0;">
  <div class="mp-section-title">Related Products</div>
  <div class="mp-grid">
    <?php foreach($related_products as $p): ?>
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/product/' . $p->id); ?>" class="mp-card">
      <?php if($p->item_image && file_exists($p->item_image)): ?>
        <img src="<?= base_url($p->item_image); ?>" class="mp-card-img" alt="">
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
    name: '<?= htmlspecialchars(addslashes($product->item_name)); ?>',
    price: <?= $product->effective_price; ?>,
    image: '<?= $product->item_image; ?>',
    stock: <?= (int)$product->stock; ?>
  };

  function adjustDetailQty(d){
    detailQty = Math.max(1, detailQty + d);
    document.getElementById('detail-qty').textContent = detailQty;
  }

  function addDetailToCart(){
    addToCart(detailProduct.id, 'product', detailProduct.name, detailProduct.price, detailProduct.image, detailQty, detailProduct.stock);
  }

  function sendDetailWhatsApp(){
    let msg = 'Hello, I am interested in: ' + detailProduct.name + ' — ' + formatMoney(detailProduct.price);
    const wnum = '<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? ''); ?>';
    if(wnum) window.open('https://wa.me/' + wnum + '?text=' + encodeURIComponent(msg), '_blank');
  }
</script>
