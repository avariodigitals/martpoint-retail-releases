<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <title><?= htmlspecialchars($store->store_name ?? 'Store'); ?> | Online Store</title>
  <link rel="shortcut icon" href="<?= base_url('uploads/site/icon.webp'); ?>">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root { --primary:#3B82F6; --primary-dark:#2563EB; --success:#10B981; --warning:#F59E0B; --danger:#EF4444; --dark:#0F172A; --gray:#64748B; --light-gray:#F1F5F9; --border:#E2E8F0; --white:#fff; --radius:12px; --radius-sm:8px; }
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Inter',-apple-system,BlinkMacSystemFont,sans-serif; background:#F8FAFC; color:var(--dark); -webkit-font-smoothing:antialiased; }
    a { text-decoration:none; color:inherit; }
    img { max-width:100%; display:block; }

    /* Header */
    .sf-header { background:var(--white); border-bottom:1px solid var(--border); position:sticky; top:0; z-index:100; }
    .sf-header-inner { max-width:600px; margin:0 auto; padding:12px 16px; display:flex; align-items:center; justify-content:space-between; }
    .sf-logo { font-weight:800; font-size:18px; color:var(--primary); }
    .sf-cart-btn { position:relative; width:40px; height:40px; border-radius:50%; background:var(--light-gray); display:flex; align-items:center; justify-content:center; font-size:18px; }
    .sf-cart-count { position:absolute; top:-4px; right:-4px; background:var(--danger); color:#fff; font-size:11px; font-weight:700; width:20px; height:20px; border-radius:50%; display:flex; align-items:center; justify-content:center; }

    /* Search */
    .sf-search-wrap { padding:12px 16px; background:var(--white); border-bottom:1px solid var(--border); }
    .sf-search { max-width:600px; margin:0 auto; position:relative; }
    .sf-search input { width:100%; padding:12px 16px 12px 44px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:15px; background:var(--light-gray); border:none; outline:none; }
    .sf-search-icon { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--gray); font-size:16px; }

    /* Banner */
    .sf-banner { max-width:600px; margin:0 auto; padding:16px; }
    .sf-banner-img { width:100%; height:160px; object-fit:cover; border-radius:var(--radius); background:linear-gradient(135deg,#3B82F6,#8B5CF6); display:flex; align-items:center; justify-content:center; color:#fff; font-size:20px; font-weight:700; text-align:center; padding:20px; }

    /* Categories */
    .sf-categories { max-width:600px; margin:0 auto; padding:0 16px 12px; display:flex; gap:8px; overflow-x:auto; scrollbar-width:none; -ms-overflow-style:none; }
    .sf-categories::-webkit-scrollbar { display:none; }
    .sf-chip { white-space:nowrap; padding:8px 16px; border-radius:20px; background:var(--white); border:1px solid var(--border); font-size:13px; font-weight:500; color:var(--gray); }
    .sf-chip.active { background:var(--primary); color:#fff; border-color:var(--primary); }

    /* Section */
    .sf-section { max-width:600px; margin:0 auto; padding:16px; }
    .sf-section-title { font-size:16px; font-weight:700; margin-bottom:12px; display:flex; align-items:center; justify-content:space-between; }
    .sf-section-title a { font-size:13px; color:var(--primary); font-weight:600; }

    /* Product Grid */
    .sf-grid { display:grid; grid-template-columns:repeat(2, 1fr); gap:12px; }
    .sf-card { background:var(--white); border-radius:var(--radius-sm); overflow:hidden; border:1px solid var(--border); transition:transform .15s ease, box-shadow .15s ease; }
    .sf-card:active { transform:scale(.98); }
    .sf-card-img { width:100%; height:140px; object-fit:cover; background:var(--light-gray); }
    .sf-card-body { padding:10px; }
    .sf-card-name { font-size:13px; font-weight:600; color:var(--dark); line-height:1.3; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; margin-bottom:6px; }
    .sf-card-price { font-size:15px; font-weight:700; color:var(--primary); }
    .sf-card-old { font-size:12px; color:var(--gray); text-decoration:line-through; margin-left:4px; }
    .sf-card-stock { font-size:11px; color:var(--danger); font-weight:600; margin-top:4px; }
    .sf-card-add { width:100%; margin-top:8px; padding:8px; border:none; border-radius:6px; background:var(--primary); color:#fff; font-size:13px; font-weight:600; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:4px; }
    .sf-card-add:disabled { background:#CBD5E1; cursor:not-allowed; }

    /* Service Card */
    .sf-service-card { background:var(--white); border-radius:var(--radius-sm); border:1px solid var(--border); padding:12px; display:flex; gap:12px; align-items:center; }
    .sf-service-img { width:60px; height:60px; border-radius:8px; object-fit:cover; background:var(--light-gray); flex-shrink:0; }
    .sf-service-info { flex:1; min-width:0; }
    .sf-service-name { font-size:14px; font-weight:600; margin-bottom:4px; }
    .sf-service-meta { font-size:12px; color:var(--gray); }
    .sf-service-price { font-size:15px; font-weight:700; color:var(--primary); }

    /* Sticky Cart */
    .sf-sticky-cart { position:fixed; bottom:0; left:0; right:0; background:var(--white); border-top:1px solid var(--border); padding:12px 16px; z-index:200; display:none; }
    .sf-sticky-cart.show { display:block; }
    .sf-sticky-cart-inner { max-width:600px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; }
    .sf-sticky-cart-info { font-size:14px; }
    .sf-sticky-cart-info span { font-weight:700; }
    .sf-sticky-cart-btn { padding:10px 24px; border-radius:var(--radius-sm); background:var(--primary); color:#fff; font-weight:700; border:none; cursor:pointer; font-size:14px; }

    /* WhatsApp CTA */
    .sf-whatsapp { max-width:600px; margin:0 auto 80px; padding:16px; }
    .sf-whatsapp-btn { display:flex; align-items:center; justify-content:center; gap:8px; width:100%; padding:14px; border-radius:var(--radius-sm); background:#25D366; color:#fff; font-weight:700; font-size:15px; border:none; cursor:pointer; }

    /* Toast */
    .sf-toast { position:fixed; top:16px; left:50%; transform:translateX(-50%) translateY(-80px); background:var(--dark); color:#fff; padding:12px 20px; border-radius:var(--radius-sm); font-size:14px; font-weight:500; z-index:300; opacity:0; transition:all .3s ease; }
    .sf-toast.show { transform:translateX(-50%) translateY(0); opacity:1; }

    /* Contact */
    .sf-contact { max-width:600px; margin:0 auto; padding:16px; background:var(--white); border-top:1px solid var(--border); margin-bottom:80px; }
    .sf-contact-item { display:flex; align-items:center; gap:8px; font-size:13px; color:var(--gray); margin-bottom:8px; }
    .sf-contact-item:last-child { margin-bottom:0; }

    /* Empty */
    .sf-empty { text-align:center; padding:40px; color:var(--gray); font-size:14px; }

    /* Modal */
    .sf-modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:400; display:none; align-items:flex-end; justify-content:center; }
    .sf-modal-overlay.show { display:flex; }
    .sf-modal { background:var(--white); width:100%; max-width:600px; border-radius:var(--radius) var(--radius) 0 0; padding:20px; max-height:80vh; overflow-y:auto; }
    .sf-modal-header { display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; }
    .sf-modal-title { font-size:18px; font-weight:700; }
    .sf-modal-close { font-size:24px; color:var(--gray); background:none; border:none; cursor:pointer; }
    .sf-modal-img { width:100%; height:200px; object-fit:cover; border-radius:var(--radius-sm); margin-bottom:16px; background:var(--light-gray); }
    .sf-modal-price { font-size:22px; font-weight:700; color:var(--primary); margin-bottom:8px; }
    .sf-modal-desc { font-size:14px; color:var(--gray); line-height:1.5; margin-bottom:16px; }
    .sf-modal-qty { display:flex; align-items:center; gap:12px; margin-bottom:16px; }
    .sf-modal-qty button { width:36px; height:36px; border-radius:50%; border:1px solid var(--border); background:var(--white); font-size:18px; cursor:pointer; }
    .sf-modal-qty span { font-size:16px; font-weight:700; min-width:30px; text-align:center; }
    .sf-modal-add { width:100%; padding:14px; border-radius:var(--radius-sm); background:var(--primary); color:#fff; font-weight:700; border:none; cursor:pointer; font-size:16px; }

    /* Cart page */
    .sf-cart-item { background:var(--white); border-radius:var(--radius-sm); border:1px solid var(--border); padding:12px; display:flex; gap:12px; margin-bottom:8px; }
    .sf-cart-item-img { width:60px; height:60px; border-radius:6px; object-fit:cover; background:var(--light-gray); flex-shrink:0; }
    .sf-cart-item-info { flex:1; min-width:0; }
    .sf-cart-item-name { font-size:14px; font-weight:600; margin-bottom:4px; }
    .sf-cart-item-price { font-size:14px; font-weight:700; color:var(--primary); }
    .sf-cart-item-qty { display:flex; align-items:center; gap:8px; margin-top:8px; }
    .sf-cart-item-qty button { width:28px; height:28px; border-radius:50%; border:1px solid var(--border); background:var(--white); font-size:14px; cursor:pointer; }
    .sf-cart-item-remove { color:var(--danger); font-size:12px; cursor:pointer; margin-top:4px; display:inline-block; }
    .sf-cart-summary { background:var(--white); border-radius:var(--radius-sm); border:1px solid var(--border); padding:16px; margin-top:12px; }
    .sf-cart-row { display:flex; justify-content:space-between; font-size:14px; margin-bottom:8px; }
    .sf-cart-total { font-size:18px; font-weight:700; border-top:1px solid var(--border); padding-top:8px; margin-top:8px; }
    .sf-cart-checkout { width:100%; padding:14px; border-radius:var(--radius-sm); background:var(--primary); color:#fff; font-weight:700; border:none; cursor:pointer; font-size:16px; margin-top:12px; }
    .sf-cart-input { width:100%; padding:12px; border:1px solid var(--border); border-radius:var(--radius-sm); font-size:14px; margin-bottom:12px; outline:none; }
    .sf-cart-input:focus { border-color:var(--primary); }
    .sf-cart-label { font-size:13px; font-weight:600; color:var(--gray); margin-bottom:4px; display:block; }
    .sf-payment-options { display:flex; flex-direction:column; gap:8px; margin-bottom:16px; }
    .sf-payment-option { display:flex; align-items:center; gap:10px; padding:12px; border:1px solid var(--border); border-radius:var(--radius-sm); cursor:pointer; background:var(--white); }
    .sf-payment-option.selected { border-color:var(--primary); background:#EFF6FF; }
    .sf-payment-option input { width:18px; height:18px; }

    /* Footer space */
    .sf-footer-space { height:100px; }

    @media(min-width:768px){
      .sf-grid { grid-template-columns:repeat(3, 1fr); }
      .sf-modal-overlay { align-items:center; }
      .sf-modal { border-radius:var(--radius); max-height:90vh; }
    }
  </style>
</head>
<body>

<!-- Header -->
<div class="sf-header">
  <div class="sf-header-inner">
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>" class="sf-logo"><?= htmlspecialchars($store->store_name ?? 'Store'); ?></a>
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/cart'); ?>" class="sf-cart-btn">
      <span>&#128722;</span>
      <span class="sf-cart-count" id="cart-count">0</span>
    </a>
  </div>
</div>

<?php if($settings->show_search): ?>
<!-- Search -->
<div class="sf-search-wrap">
  <div class="sf-search">
    <span class="sf-search-icon">&#128269;</span>
    <input type="text" id="search-input" placeholder="Search products & services..." onkeydown="if(event.key==='Enter')doSearch()">
  </div>
</div>
<?php endif; ?>

<!-- Banner -->
<div class="sf-banner">
  <?php if($settings->store_banner && file_exists($settings->store_banner)): ?>
    <img src="<?= base_url($settings->store_banner); ?>" class="sf-banner-img" alt="Banner" style="display:block;">
  <?php else: ?>
    <div class="sf-banner-img"><?= htmlspecialchars($store->store_name ?? 'Welcome to our store'); ?></div>
  <?php endif; ?>
</div>

<?php if($settings->show_categories && !empty($categories)): ?>
<!-- Categories -->
<div class="sf-categories">
  <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>" class="sf-chip active">All</a>
  <?php foreach($categories as $cat): ?>
  <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products?category=' . $cat->id); ?>" class="sf-chip"><?= htmlspecialchars($cat->category_name); ?></a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Featured Products -->
<div class="sf-section">
  <div class="sf-section-title">
    Featured Products
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>">See all &rarr;</a>
  </div>
  <?php if(!empty($featured_products)): ?>
  <div class="sf-grid">
    <?php foreach($featured_products as $p): 
      $price = $p->effective_price ?? $p->sales_price;
      $oldPrice = $p->original_price ?? $p->sales_price;
      $hasDiscount = $oldPrice > $price;
    ?>
    <div class="sf-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, <?= $hasDiscount ? $oldPrice : 0; ?>)">
      <?php if($p->item_image && file_exists($p->item_image)): ?>
        <img src="<?= base_url($p->item_image); ?>" class="sf-card-img" alt="<?= htmlspecialchars($p->item_name); ?>">
      <?php else: ?>
        <div class="sf-card-img" style="display:flex;align-items:center;justify-content:center;color:#94A3B8;font-size:12px;">No Image</div>
      <?php endif; ?>
      <div class="sf-card-body">
        <div class="sf-card-name"><?= htmlspecialchars($p->item_name); ?></div>
        <div style="display:flex;align-items:center;">
          <span class="sf-card-price"><?= store_number_format($price); ?></span>
          <?php if($hasDiscount): ?><span class="sf-card-old"><?= store_number_format($oldPrice); ?></span><?php endif; ?>
        </div>
        <?php if($p->stock <= 0 && !$settings->allow_backorder): ?>
          <div class="sf-card-stock">Out of Stock</div>
        <?php endif; ?>
        <button class="sf-card-add" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)">
          <span>+</span> Add
        </button>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
    <div class="sf-empty">No products available yet.</div>
  <?php endif; ?>
</div>

<?php if($settings->allow_services && !empty($featured_services)): ?>
<!-- Featured Services -->
<div class="sf-section">
  <div class="sf-section-title">
    Services
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/services'); ?>">See all &rarr;</a>
  </div>
  <div style="display:flex;flex-direction:column;gap:10px;">
    <?php foreach($featured_services as $s): 
      $price = $s->effective_price ?? $s->price;
    ?>
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/service/' . $s->id); ?>" class="sf-service-card">
      <?php if($s->service_image && file_exists($s->service_image)): ?>
        <img src="<?= base_url($s->service_image); ?>" class="sf-service-img" alt="">
      <?php else: ?>
        <div class="sf-service-img" style="display:flex;align-items:center;justify-content:center;color:#94A3B8;font-size:10px;">No Image</div>
      <?php endif; ?>
      <div class="sf-service-info">
        <div class="sf-service-name"><?= htmlspecialchars($s->service_name); ?></div>
        <div class="sf-service-meta"><?= htmlspecialchars($s->service_duration ?: ''); ?> &middot; <?= ucfirst(str_replace('-',' ',$s->location_type)); ?></div>
        <div class="sf-service-price"><?= store_number_format($price); ?></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php if($settings->show_whatsapp_cta && $settings->whatsapp_number): ?>
<!-- WhatsApp CTA -->
<div class="sf-whatsapp">
  <button class="sf-whatsapp-btn" onclick="sendWhatsAppOrder()">
    <span>&#128172;</span> Order via WhatsApp
  </button>
</div>
<?php endif; ?>

<!-- Contact -->
<div class="sf-contact">
  <?php if($settings->store_phone): ?><div class="sf-contact-item">&#128222; <?= htmlspecialchars($settings->store_phone); ?></div><?php endif; ?>
  <?php if($settings->store_email): ?><div class="sf-contact-item">&#9993; <?= htmlspecialchars($settings->store_email); ?></div><?php endif; ?>
  <?php if($settings->store_address): ?><div class="sf-contact-item">&#127968; <?= htmlspecialchars($settings->store_address); ?></div><?php endif; ?>
</div>

<div class="sf-footer-space"></div>

<!-- Sticky Cart -->
<div class="sf-sticky-cart" id="sticky-cart">
  <div class="sf-sticky-cart-inner">
    <div class="sf-sticky-cart-info"><span id="sticky-qty">0</span> items &middot; <span id="sticky-total"><?= store_number_format(0); ?></span></div>
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/cart'); ?>" class="sf-sticky-cart-btn">View Cart</a>
  </div>
</div>

<!-- Product Modal -->
<div class="sf-modal-overlay" id="product-modal">
  <div class="sf-modal">
    <div class="sf-modal-header">
      <div class="sf-modal-title" id="modal-title">Product</div>
      <button class="sf-modal-close" onclick="closeModal()">&times;</button>
    </div>
    <img id="modal-img" class="sf-modal-img" src="" alt="">
    <div class="sf-modal-price" id="modal-price"></div>
    <div class="sf-modal-desc" id="modal-desc"></div>
    <div class="sf-modal-qty">
      <button onclick="adjustModalQty(-1)">-</button>
      <span id="modal-qty">1</span>
      <button onclick="adjustModalQty(1)">+</button>
    </div>
    <button class="sf-modal-add" id="modal-add-btn" onclick="addModalToCart()">Add to Cart</button>
  </div>
</div>

<!-- Toast -->
<div class="sf-toast" id="toast"></div>

<script>
  // Cart in localStorage
  const STORE_SLUG = '<?= $settings->store_slug ?? ''; ?>';
  const STORE_ID = <?= $settings->store_id ?? 0; ?>;
  const CURRENCY = '<?= $CURRENCY ?? '&#8358;'; ?>';
  let cart = JSON.parse(localStorage.getItem('sf_cart_' + STORE_ID) || '[]');
  let modalProduct = null;
  let modalQty = 1;

  function saveCart(){
    localStorage.setItem('sf_cart_' + STORE_ID, JSON.stringify(cart));
    updateCartUI();
  }

  function updateCartUI(){
    let qty = 0, total = 0;
    cart.forEach(i => { qty += i.qty; total += i.price * i.qty; });
    document.getElementById('cart-count').textContent = qty;
    document.getElementById('sticky-qty').textContent = qty;
    document.getElementById('sticky-total').textContent = formatMoney(total);
    const sticky = document.getElementById('sticky-cart');
    if(qty > 0) sticky.classList.add('show');
    else sticky.classList.remove('show');
  }

  function formatMoney(amount){
    return CURRENCY + amount.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ',');
  }

  function showToast(msg){
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2500);
  }

  function addToCart(id, type, name, price, image, qty, stock){
    if(type === 'product' && stock !== undefined && stock <= 0 && !<?= $settings->allow_backorder ? 'true' : 'false'; ?>){
      showToast('Out of stock'); return;
    }
    const key = type + '_' + id;
    const existing = cart.find(i => i.key === key);
    if(existing){
      if(type === 'product' && stock !== undefined && !<?= $settings->allow_backorder ? 'true' : 'false'; ?>){
        if(existing.qty + qty > stock){ showToast('Not enough stock'); return; }
      }
      existing.qty += qty;
    } else {
      cart.push({key, id, type, name, price, image, qty: qty, stock: stock ?? 999});
    }
    saveCart();
    showToast(name + ' added to cart');
  }

  function openProductModal(id, name, price, image, desc, stock, oldPrice){
    modalProduct = {id, name, price, image, desc, stock};
    modalQty = 1;
    document.getElementById('modal-title').textContent = name;
    document.getElementById('modal-price').innerHTML = formatMoney(price) + (oldPrice > 0 ? ' <span style="text-decoration:line-through;font-size:16px;color:#94A3B8;margin-left:8px;">' + formatMoney(oldPrice) + '</span>' : '');
    document.getElementById('modal-desc').textContent = desc || '';
    document.getElementById('modal-img').src = image ? '<?= base_url(); ?>' + image : '';
    document.getElementById('modal-qty').textContent = '1';
    document.getElementById('product-modal').classList.add('show');
  }

  function closeModal(){
    document.getElementById('product-modal').classList.remove('show');
  }

  function adjustModalQty(delta){
    modalQty = Math.max(1, modalQty + delta);
    document.getElementById('modal-qty').textContent = modalQty;
  }

  function addModalToCart(){
    if(!modalProduct) return;
    addToCart(modalProduct.id, 'product', modalProduct.name, modalProduct.price, modalProduct.image, modalQty, modalProduct.stock);
    closeModal();
  }

  function doSearch(){
    const q = document.getElementById('search-input').value.trim();
    if(q) window.location.href = '<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>?search=' + encodeURIComponent(q);
  }

  function sendWhatsAppOrder(){
    let msg = 'Hello, I would like to place an order from <?= htmlspecialchars($store->store_name ?? 'your store'); ?>.';
    if(cart.length > 0){
      msg += '\n\nItems:\n';
      let total = 0;
      cart.forEach(i => {
        msg += i.qty + ' x ' + i.name + ' — ' + formatMoney(i.price * i.qty) + '\n';
        total += i.price * i.qty;
      });
      msg += '\nTotal: ' + formatMoney(total);
    }
    msg += '\n\nThank you.';
    const phone = '<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? ''); ?>';
    if(phone){
      window.open('https://wa.me/' + phone + '?text=' + encodeURIComponent(msg), '_blank');
    } else {
      showToast('WhatsApp number not configured');
    }
  }

  document.addEventListener('click', function(e){
    const modal = document.getElementById('product-modal');
    if(e.target === modal) closeModal();
  });

  updateCartUI();
</script>

</body>
</html>
