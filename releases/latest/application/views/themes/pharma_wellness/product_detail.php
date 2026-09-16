<?php
/**
 * Pharma Wellness — Product Detail
 * Two-column layout with rounded gallery, Poppins typography,
 * soft sage-teal and coral accents, and wellness-focused CTAs.
 */
$slug = $settings->store_slug ?? '';
$cur = $store_currency ?? null;
$img = ($product->item_image && file_exists($product->item_image)) ? base_url($product->item_image) : '';
$hasDiscount = $product->original_price > $product->effective_price;
?>
<style>
  .theme-pharma_wellness .mp-topbar,
  .theme-pharma_wellness .mp-announcement,
  .theme-pharma_wellness .mp-nav,
  .theme-pharma_wellness .mp-header,
  .theme-pharma_wellness .mp-mobile-menu-btn,
  .theme-pharma_wellness .mp-footer-space { display:none !important; }

  :root {
    --pw-sage:#0D9488;
    --pw-sage-dark:#0F766E;
    --pw-coral:#F97066;
    --pw-coral-dark:#E85C50;
    --pw-cream:#FFFBF5;
    --pw-dark:#134E4A;
    --pw-gray:#78716C;
    --pw-border:#E8F0EE;
    --pw-white:#FFFFFF;
  }

  .pw-container { max-width:1400px; margin:0 auto; padding:0 24px; }
  @media(max-width:767px){ .pw-container { padding:0 16px; } }

  .pw-breadcrumb { padding:24px 0 0; font-family:'Poppins',sans-serif; font-size:13px; color:var(--pw-gray); }
  .pw-breadcrumb a { color:var(--pw-sage); text-decoration:none; }
  .pw-breadcrumb a:hover { color:var(--pw-sage-dark); }
  .pw-breadcrumb .sep { margin:0 8px; color:var(--pw-coral); }

  .pw-pd { padding:36px 0 72px; }
  .pw-pd-layout { display:grid; grid-template-columns:1.1fr 1fr; gap:48px; align-items:start; }
  @media(max-width:1023px){ .pw-pd-layout { grid-template-columns:1fr; gap:32px; } }
  .pw-pd-gallery { position:sticky; top:90px; border-radius:24px; overflow:hidden; background:var(--pw-cream); aspect-ratio:1/1; border:1px solid var(--pw-border); }
  @media(max-width:1023px){ .pw-pd-gallery { position:static; } }
  .pw-pd-gallery img { width:100%; height:100%; object-fit:cover; }
  .pw-pd-gallery-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:var(--pw-sage); font-family:'Poppins',sans-serif; font-size:52px; font-weight:700; }

  .pw-pd-meta { padding-top:8px; }
  .pw-pd-kicker { font-family:'Poppins',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.16em; color:var(--pw-coral); font-weight:600; margin-bottom:12px; }
  .pw-pd-name { font-family:'Poppins',sans-serif; font-size:clamp(28px,3vw,38px); font-weight:700; margin-bottom:14px; color:var(--pw-dark); line-height:1.12; letter-spacing:-0.01em; }
  .pw-pd-price { font-family:'Poppins',sans-serif; font-size:26px; font-weight:700; margin-bottom:8px; color:var(--pw-dark); }
  .pw-pd-price .old { font-family:'Poppins',sans-serif; font-size:16px; color:var(--pw-gray); text-decoration:line-through; margin-left:12px; font-weight:400; }
  .pw-pd-stock { font-family:'Poppins',sans-serif; font-size:14px; color:var(--pw-sage); margin-bottom:24px; font-weight:600; }
  .pw-pd-stock.out { color:#DC2626; }
  .pw-pd-desc { font-family:'Poppins',sans-serif; color:var(--pw-gray); line-height:1.75; margin-bottom:28px; font-size:15px; }
  .pw-pd-qty { display:flex; align-items:center; gap:14px; margin-bottom:26px; }
  .pw-pd-qty button { width:44px; height:44px; border-radius:12px; border:1px solid var(--pw-border); background:#fff; font-family:'Poppins',sans-serif; font-size:18px; cursor:pointer; color:var(--pw-dark); transition:border-color .2s; }
  .pw-pd-qty button:hover { border-color:var(--pw-sage); color:var(--pw-sage); }
  .pw-pd-qty span { font-family:'Poppins',sans-serif; font-size:20px; font-weight:700; min-width:32px; text-align:center; color:var(--pw-dark); }
  .pw-pd-actions { display:flex; gap:12px; flex-wrap:wrap; }
  .pw-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:15px 34px; border-radius:24px; font-family:'Poppins',sans-serif; font-size:13px; font-weight:600; transition:transform .15s, background .2s, color .2s; cursor:pointer; border:none; text-decoration:none; }
  .pw-btn:active { transform:scale(0.98); }
  .pw-btn-sage { background:var(--pw-sage); color:#fff; }
  .pw-btn-sage:hover { background:var(--pw-sage-dark); }
  .pw-btn-outline { background:#fff; color:var(--pw-sage); border:1.5px solid var(--pw-sage); }
  .pw-btn-outline:hover { background:var(--pw-sage); color:#fff; }
  .pw-btn-wa { background:#25D366; color:#fff; }
  .pw-btn-wa:hover { background:#1DA851; }
  .pw-btn-coral { background:var(--pw-coral); color:#fff; }
  .pw-btn-coral:hover { background:var(--pw-coral-dark); }
  .pw-pd-actions .pw-btn { flex:1; min-width:160px; }

  .pw-pd-variants { margin-top:40px; }
  .pw-pd-related { margin-top:48px; }
  .pw-section-label { font-family:'Poppins',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.16em; color:var(--pw-coral); font-weight:600; margin-bottom:8px; }
  .pw-section-title { font-family:'Poppins',sans-serif; font-size:24px; margin:0 0 24px; font-weight:700; color:var(--pw-dark); letter-spacing:-0.01em; }

  .pw-product-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:20px; }
  @media(max-width:1023px){ .pw-product-grid { grid-template-columns:repeat(3,1fr); } }
  @media(max-width:767px){ .pw-product-grid { grid-template-columns:repeat(2,1fr); gap:12px; } }
  .pw-product-card { position:relative; background:var(--pw-white); border-radius:20px; overflow:hidden; border:1px solid var(--pw-border); transition:transform .25s, box-shadow .25s; cursor:pointer; text-decoration:none; color:inherit; }
  .pw-product-card:hover { transform:translateY(-6px); box-shadow:0 20px 40px rgba(13,148,136,0.12); }
  .pw-product-media { aspect-ratio:1/1; overflow:hidden; background:var(--pw-cream); }
  .pw-product-media img { width:100%; height:100%; object-fit:cover; transition:transform .4s ease; }
  .pw-product-card:hover .pw-product-media img { transform:scale(1.05); }
  .pw-product-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--pw-cream); color:var(--pw-sage); }
  .pw-product-placeholder span { font-family:'Poppins',sans-serif; font-size:36px; font-weight:700; }
  .pw-product-body { padding:16px; }
  .pw-product-name { font-family:'Poppins',sans-serif; font-size:14px; font-weight:600; margin-bottom:8px; line-height:1.4; color:var(--pw-dark); }
  .pw-product-price { font-family:'Poppins',sans-serif; font-size:16px; font-weight:700; color:var(--pw-dark); }
</style>

<div class="pw-container">
  <div class="pw-breadcrumb">
    <a href="<?= base_url('store/' . $slug); ?>">Home</a>
    <span class="sep">/</span>
    <a href="<?= base_url('store/' . $slug . '/products'); ?>">Shop</a>
    <span class="sep">/</span>
    <span><?= htmlspecialchars($product->item_name); ?></span>
  </div>

  <div class="pw-pd">
    <div class="pw-pd-layout">
      <div class="pw-pd-gallery">
        <?php if($img): ?>
        <img src="<?= $img; ?>" alt="<?= htmlspecialchars($product->item_name); ?>">
        <?php else: ?>
        <div class="pw-pd-gallery-placeholder"><?= htmlspecialchars(substr($product->item_name, 0, 1)); ?></div>
        <?php endif; ?>
      </div>
      <div class="pw-pd-meta">
        <div class="pw-pd-kicker"><?= htmlspecialchars($product->category_name ?? 'Wellness'); ?></div>
        <h1 class="pw-pd-name"><?= htmlspecialchars($product->item_name); ?></h1>
        <div class="pw-pd-price">
          <?= sf_currency($product->effective_price, $cur); ?>
          <?php if($hasDiscount): ?>
          <span class="old"><?= sf_currency($product->original_price, $cur); ?></span>
          <?php endif; ?>
        </div>
        <div class="pw-pd-stock <?= (int)$product->stock <= 0 ? 'out' : ''; ?>">
          <?= (int)$product->stock > 0 ? 'In Stock &middot; Ships within 24 hours' : 'Out of Stock'; ?>
        </div>
        <p class="pw-pd-desc"><?= nl2br(htmlspecialchars($product->description ?? '')); ?></p>

        <div class="pw-pd-qty">
          <button onclick="adjustDetailQty(-1)">-</button>
          <span id="detail-qty">1</span>
          <button onclick="adjustDetailQty(1)">+</button>
        </div>

        <div class="pw-pd-actions">
          <button class="pw-btn pw-btn-sage" onclick="addDetailToCart()">Add to Cart</button>
          <button class="pw-btn pw-btn-outline" onclick="addDetailToCart();window.location.href='<?= base_url('store/' . $slug . '/cart'); ?>'">Buy It Now</button>
          <?php if(!empty($settings->whatsapp_number)): ?>
          <button class="pw-btn pw-btn-wa" onclick="sendDetailWhatsApp()">Order via WhatsApp</button>
          <?php endif; ?>
        </div>

        <?php if(!empty($product_variants)): ?>
        <div class="pw-pd-variants">
          <div class="pw-section-label" style="margin-bottom:16px;">Available Variants</div>
          <div class="pw-product-grid" style="grid-template-columns:repeat(2,1fr);gap:12px;">
            <?php foreach($product_variants as $v):
              $vImg = ($v->item_image && file_exists($v->item_image)) ? base_url($v->item_image) : '';
            ?>
            <a href="<?= base_url('store/' . $slug . '/product/' . $v->id); ?>" class="pw-product-card">
              <div class="pw-product-media">
                <?php if($vImg): ?>
                <img src="<?= $vImg; ?>" alt="<?= htmlspecialchars($v->item_name); ?>" loading="lazy">
                <?php else: ?>
                <div class="pw-product-placeholder"><span><?= htmlspecialchars(substr($v->item_name, 0, 1)); ?></span></div>
                <?php endif; ?>
              </div>
              <div class="pw-product-body">
                <div class="pw-product-name"><?= htmlspecialchars($v->item_name); ?></div>
                <div class="pw-product-price"><?= sf_currency($v->effective_price, $cur); ?></div>
              </div>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <?php if(!empty($related_products)): ?>
        <div class="pw-pd-related">
          <div class="pw-section-label" style="margin-bottom:16px;">You May Also Like</div>
          <div class="pw-product-grid" style="grid-template-columns:repeat(2,1fr);gap:12px;">
            <?php foreach($related_products as $p):
              $pImg = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
            ?>
            <a href="<?= base_url('store/' . $slug . '/product/' . $p->id); ?>" class="pw-product-card">
              <div class="pw-product-media">
                <?php if($pImg): ?>
                <img src="<?= $pImg; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
                <?php else: ?>
                <div class="pw-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
                <?php endif; ?>
              </div>
              <div class="pw-product-body">
                <div class="pw-product-name"><?= htmlspecialchars($p->item_name); ?></div>
                <div class="pw-product-price"><?= sf_currency($p->sales_price, $cur); ?></div>
              </div>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
  let detailQty = 1;
  const detailProduct = {
    id: <?= $product->id; ?>,
    name: '<?= htmlspecialchars(addslashes($product->item_name)); ?>',
    price: <?= $product->effective_price; ?>,
    image: '<?= $product->item_image; ?>',
    stock: <?= (int)$product->stock; ?>
  };
  function adjustDetailQty(d){ detailQty = Math.max(1, detailQty + d); document.getElementById('detail-qty').textContent = detailQty; }
  function addDetailToCart(){ addToCart(detailProduct.id, 'product', detailProduct.name, detailProduct.price, detailProduct.image, detailQty, detailProduct.stock); }
  function sendDetailWhatsApp(){
    let msg = 'Hello, I am interested in: ' + detailProduct.name + ' — ' + formatMoney(detailProduct.price);
    const wnum = '<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? ''); ?>';
    if(wnum) window.open('https://wa.me/' + wnum + '?text=' + encodeURIComponent(msg), '_blank');
  }
</script>
