<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <title><?= htmlspecialchars($product->item_name); ?> | <?= htmlspecialchars($store->store_name ?? 'Store'); ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root { --primary:#3B82F6; --dark:#0F172A; --gray:#64748B; --light-gray:#F1F5F9; --border:#E2E8F0; --white:#fff; --radius-sm:8px; }
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Inter',sans-serif; background:#F8FAFC; color:var(--dark); }
    a { text-decoration:none; color:inherit; }
    img { max-width:100%; display:block; }
    .sf-header { background:var(--white); border-bottom:1px solid var(--border); position:sticky; top:0; z-index:100; }
    .sf-header-inner { max-width:600px; margin:0 auto; padding:12px 16px; display:flex; align-items:center; gap:12px; }
    .sf-back { font-size:22px; }
    .sf-img { width:100%; height:280px; object-fit:cover; background:var(--light-gray); max-width:600px; margin:0 auto; }
    .sf-info { max-width:600px; margin:0 auto; padding:16px; }
    .sf-name { font-size:22px; font-weight:700; margin-bottom:8px; }
    .sf-price { font-size:26px; font-weight:800; color:var(--primary); margin-bottom:4px; }
    .sf-old { font-size:16px; color:var(--gray); text-decoration:line-through; margin-left:8px; }
    .sf-stock { font-size:13px; color:var(--gray); margin-bottom:16px; }
    .sf-desc { font-size:14px; line-height:1.6; color:#334155; margin-bottom:24px; }
    .sf-qty { display:flex; align-items:center; gap:12px; margin-bottom:16px; }
    .sf-qty button { width:40px; height:40px; border-radius:50%; border:1px solid var(--border); background:var(--white); font-size:18px; cursor:pointer; }
    .sf-qty span { font-size:18px; font-weight:700; min-width:30px; text-align:center; }
    .sf-add { width:100%; padding:14px; border-radius:var(--radius-sm); background:var(--primary); color:#fff; font-weight:700; border:none; cursor:pointer; font-size:16px; margin-bottom:12px; }
    .sf-add:disabled { background:#CBD5E1; cursor:not-allowed; }
    .sf-whatsapp { width:100%; padding:14px; border-radius:var(--radius-sm); background:#25D366; color:#fff; font-weight:700; border:none; cursor:pointer; font-size:16px; }
    .sf-section { max-width:600px; margin:0 auto; padding:16px; }
    .sf-section-title { font-size:16px; font-weight:700; margin-bottom:12px; }
    .sf-grid { display:grid; grid-template-columns:repeat(2, 1fr); gap:12px; }
    .sf-card { background:var(--white); border-radius:var(--radius-sm); border:1px solid var(--border); overflow:hidden; }
    .sf-card-img { width:100%; height:120px; object-fit:cover; background:var(--light-gray); }
    .sf-card-body { padding:10px; }
    .sf-card-name { font-size:13px; font-weight:600; line-height:1.3; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; margin-bottom:4px; }
    .sf-card-price { font-size:14px; font-weight:700; color:var(--primary); }
    .sf-toast { position:fixed; top:16px; left:50%; transform:translateX(-50%) translateY(-80px); background:var(--dark); color:#fff; padding:12px 20px; border-radius:var(--radius-sm); font-size:14px; z-index:300; opacity:0; transition:all .3s ease; }
    .sf-toast.show { transform:translateX(-50%) translateY(0); opacity:1; }
    @media(min-width:768px){ .sf-grid { grid-template-columns:repeat(3, 1fr); } }
  </style>
</head>
<body>

<div class="sf-header">
  <div class="sf-header-inner">
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>" class="sf-back">&#8592;</a>
  </div>
</div>

<?php if($product->item_image && file_exists($product->item_image)): ?>
  <img src="<?= base_url($product->item_image); ?>" class="sf-img" alt="<?= htmlspecialchars($product->item_name); ?>">
<?php else: ?>
  <div class="sf-img" style="display:flex;align-items:center;justify-content:center;color:#94A3B8;">No Image</div>
<?php endif; ?>

<div class="sf-info">
  <div class="sf-name"><?= htmlspecialchars($product->item_name); ?></div>
  <div style="margin-bottom:8px;">
    <span class="sf-price"><?= store_number_format($product->effective_price); ?></span>
    <?php if($product->original_price > $product->effective_price): ?>
      <span class="sf-old"><?= store_number_format($product->original_price); ?></span>
    <?php endif; ?>
  </div>
  <div class="sf-stock"><?= $product->category_name ? 'Category: ' . htmlspecialchars($product->category_name) : ''; ?> &middot; <?= (int)$product->stock; ?> in stock</div>
  <div class="sf-desc"><?= nl2br(htmlspecialchars($product->description ?? '')); ?></div>

  <div class="sf-qty">
    <button onclick="adjustQty(-1)">-</button>
    <span id="qty">1</span>
    <button onclick="adjustQty(1)">+</button>
  </div>

  <button class="sf-add" id="add-btn" onclick="addToCart()">Add to Cart</button>
  <?php if($settings->whatsapp_number): ?>
  <button class="sf-whatsapp" onclick="sendWhatsApp()">Order via WhatsApp</button>
  <?php endif; ?>
</div>

<?php if(!empty($related_products)): ?>
<div class="sf-section">
  <div class="sf-section-title">Related Products</div>
  <div class="sf-grid">
    <?php foreach($related_products as $p): ?>
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/product/' . $p->id); ?>" class="sf-card">
      <?php if($p->item_image && file_exists($p->item_image)): ?>
        <img src="<?= base_url($p->item_image); ?>" class="sf-card-img" alt="">
      <?php else: ?>
        <div class="sf-card-img" style="display:flex;align-items:center;justify-content:center;color:#94A3B8;font-size:10px;">No Image</div>
      <?php endif; ?>
      <div class="sf-card-body">
        <div class="sf-card-name"><?= htmlspecialchars($p->item_name); ?></div>
        <div class="sf-card-price"><?= store_number_format($p->sales_price); ?></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<div class="sf-toast" id="toast"></div>

<script>
  const STORE_ID = <?= $settings->store_id ?? 0; ?>;
  const CURRENCY = '<?= $CURRENCY ?? '&#8358;'; ?>';
  let qty = 1;
  const product = {
    id: <?= $product->id; ?>,
    name: '<?= htmlspecialchars(addslashes($product->item_name)); ?>',
    price: <?= $product->effective_price; ?>,
    image: '<?= $product->item_image; ?>',
    stock: <?= (int)$product->stock; ?>
  };

  function formatMoney(a){ return CURRENCY + a.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ','); }

  function showToast(msg){
    const t = document.getElementById('toast');
    t.textContent = msg; t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2500);
  }

  function adjustQty(d){ qty = Math.max(1, qty + d); document.getElementById('qty').textContent = qty; }

  function addToCart(){
    let cart = JSON.parse(localStorage.getItem('sf_cart_' + STORE_ID) || '[]');
    const key = 'product_' + product.id;
    const existing = cart.find(i => i.key === key);
    if(existing){
      if(existing.qty + qty > product.stock && !<?= $settings->allow_backorder ? 'true' : 'false'; ?>){
        showToast('Not enough stock'); return;
      }
      existing.qty += qty;
    } else {
      cart.push({key, id: product.id, type: 'product', name: product.name, price: product.price, image: product.image, qty: qty, stock: product.stock});
    }
    localStorage.setItem('sf_cart_' + STORE_ID, JSON.stringify(cart));
    showToast(product.name + ' added to cart');
  }

  function sendWhatsApp(){
    let msg = 'Hello, I am interested in: ' + product.name + ' — ' + formatMoney(product.price);
    const wnum = '<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? ''); ?>';
    if(wnum) window.open('https://wa.me/' + wnum + '?text=' + encodeURIComponent(msg), '_blank');
  }
</script>

</body>
</html>
