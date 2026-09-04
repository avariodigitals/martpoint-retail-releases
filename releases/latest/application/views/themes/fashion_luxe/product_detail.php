<?php
/**
 * Fashion Luxe — Product Detail
 * Two-column layout with sticky gallery, serif typography and
 * related products grid in an elegant gold-accented design.
 */
$slug = $settings->store_slug ?? '';
$cur = $store_currency ?? null;
$img = ($product->item_image && file_exists($product->item_image)) ? base_url($product->item_image) : '';
$hasDiscount = $product->original_price > $product->effective_price;
?>
<style>
  .theme-fashion_luxe .mp-topbar,
  .theme-fashion_luxe .mp-announcement,
  .theme-fashion_luxe .mp-nav,
  .theme-fashion_luxe .mp-header,
  .theme-fashion_luxe .mp-mobile-menu-btn,
  .theme-fashion_luxe .mp-footer-space { display:none; height:0; }

  :root {
    --fl-ink:#1A1A1A;
    --fl-warm:#8B6D4B;
    --fl-gold:#C9A961;
    --fl-ivory:#F9F7F2;
    --fl-soft:#E8E4DC;
  }

  .fl-container { max-width:1400px; margin:0 auto; padding:0 24px; }
  @media(max-width:767px){ .fl-container { padding:0 16px; } }

  .fl-breadcrumb { padding:24px 0 0; font-family:'Lora',serif; font-size:13px; color:#8B6D4B; }
  .fl-breadcrumb a { color:#8B6D4B; text-decoration:none; }
  .fl-breadcrumb a:hover { color:var(--fl-ink); }
  .fl-breadcrumb .sep { margin:0 8px; color:#C9A961; }

  .fl-pd { padding:36px 0 72px; }
  .fl-pd-layout { display:grid; grid-template-columns:1.1fr 1fr; gap:52px; align-items:start; }
  @media(max-width:1023px){ .fl-pd-layout { grid-template-columns:1fr; gap:36px; } }
  .fl-pd-gallery { position:sticky; top:90px; border-radius:4px; overflow:hidden; background:var(--fl-soft); aspect-ratio:4/5; border:1px solid var(--fl-soft); }
  @media(max-width:1023px){ .fl-pd-gallery { position:static; } }
  .fl-pd-gallery img { width:100%; height:100%; object-fit:cover; }
  .fl-pd-gallery-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:var(--fl-gold); font-family:'Playfair Display',serif; font-style:italic; font-size:52px; font-weight:700; }

  .fl-pd-meta { padding-top:12px; }
  .fl-pd-kicker { font-family:'Lora',serif; font-size:11px; text-transform:uppercase; letter-spacing:0.18em; color:var(--fl-gold); font-weight:600; margin-bottom:12px; }
  .fl-pd-name { font-family:'Playfair Display',serif; font-style:italic; font-size:clamp(28px,3.5vw,42px); font-weight:700; margin-bottom:14px; color:var(--fl-ink); line-height:1.12; }
  .fl-pd-price { font-family:'Playfair Display',serif; font-size:28px; font-weight:700; margin-bottom:8px; color:var(--fl-ink); }
  .fl-pd-price .old { font-family:'Lora',serif; font-size:18px; color:#8B6D4B; text-decoration:line-through; margin-left:12px; font-weight:500; }
  .fl-pd-stock { font-family:'Lora',serif; font-size:14px; color:#4A7C59; margin-bottom:24px; font-weight:600; }
  .fl-pd-stock.out { color:#B23A3A; }
  .fl-pd-desc { font-family:'Lora',serif; color:#8B6D4B; line-height:1.75; margin-bottom:30px; font-size:15px; }
  .fl-pd-qty { display:flex; align-items:center; gap:14px; margin-bottom:26px; }
  .fl-pd-qty button { width:44px; height:44px; border-radius:4px; border:1px solid var(--fl-soft); background:#fff; font-family:'Lora',serif; font-size:18px; cursor:pointer; color:var(--fl-ink); transition:border-color .2s; }
  .fl-pd-qty button:hover { border-color:var(--fl-gold); color:var(--fl-gold); }
  .fl-pd-qty span { font-family:'Playfair Display',serif; font-size:20px; font-weight:700; min-width:32px; text-align:center; color:var(--fl-ink); }
  .fl-pd-actions { display:flex; gap:12px; flex-wrap:wrap; }
  .fl-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:15px 34px; border-radius:2px; font-family:'Lora',serif; font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; transition:transform .15s, background .2s, color .2s; cursor:pointer; border:none; text-decoration:none; }
  .fl-btn:active { transform:scale(0.98); }
  .fl-btn-gold { background:var(--fl-gold); color:var(--fl-ink); }
  .fl-btn-gold:hover { background:#B8974F; }
  .fl-btn-ink { background:var(--fl-ink); color:#fff; }
  .fl-btn-ink:hover { background:#8B6D4B; }
  .fl-btn-outline { background:#fff; color:var(--fl-ink); border:1px solid var(--fl-ink); }
  .fl-btn-outline:hover { background:var(--fl-ink); color:#fff; }
  .fl-btn-wa { background:#25D366; color:#fff; }
  .fl-btn-wa:hover { background:#1DA851; }
  .fl-pd-actions .fl-btn { flex:1; min-width:180px; }

  .fl-pd-variants { margin-top:44px; }
  .fl-pd-related { margin-top:52px; }
  .fl-section-label { font-family:'Lora',serif; font-size:11px; text-transform:uppercase; letter-spacing:0.18em; color:var(--fl-gold); font-weight:600; margin-bottom:8px; }
  .fl-section-title { font-family:'Playfair Display',serif; font-style:italic; font-size:28px; margin:0 0 26px; font-weight:700; color:var(--fl-ink); }

  .fl-product-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:24px; }
  @media(max-width:1023px){ .fl-product-grid { grid-template-columns:repeat(3,1fr); } }
  @media(max-width:767px){ .fl-product-grid { grid-template-columns:repeat(2,1fr); gap:14px; } }
  .fl-product-card { position:relative; background:#fff; border-radius:4px; overflow:hidden; border:1px solid var(--fl-soft); transition:transform .25s, box-shadow .25s; cursor:pointer; text-decoration:none; color:inherit; }
  .fl-product-card:hover { transform:translateY(-4px); box-shadow:0 12px 36px rgba(61,40,23,0.12); }
  .fl-product-media { aspect-ratio:4/5; overflow:hidden; background:var(--fl-soft); }
  .fl-product-media img { width:100%; height:100%; object-fit:cover; transition:transform .5s ease; }
  .fl-product-card:hover .fl-product-media img { transform:scale(1.06); }
  .fl-product-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--fl-soft); color:var(--fl-gold); }
  .fl-product-placeholder span { font-family:'Playfair Display',serif; font-style:italic; font-size:38px; font-weight:700; }
  .fl-product-body { padding:20px; }
  .fl-product-name { font-family:'Lora',serif; font-size:14px; font-weight:600; margin-bottom:10px; line-height:1.4; color:var(--fl-ink); }
  .fl-product-price { font-family:'Playfair Display',serif; font-size:18px; font-weight:700; color:var(--fl-ink); }
</style>

<div class="fl-container">
  <div class="fl-breadcrumb">
    <a href="<?= base_url('store/' . $slug); ?>">Home</a>
    <span class="sep">/</span>
    <a href="<?= base_url('store/' . $slug . '/products'); ?>">Fashion</a>
    <span class="sep">/</span>
    <span><?= htmlspecialchars($product->item_name); ?></span>
  </div>

  <div class="fl-pd">
    <div class="fl-pd-layout">
      <div class="fl-pd-gallery">
        <?php if($img): ?>
        <img src="<?= $img; ?>" alt="<?= htmlspecialchars($product->item_name); ?>">
        <?php else: ?>
        <div class="fl-pd-gallery-placeholder"><?= htmlspecialchars(substr($product->item_name, 0, 1)); ?></div>
        <?php endif; ?>
      </div>
      <div class="fl-pd-meta">
        <div class="fl-pd-kicker"><?= htmlspecialchars($product->category_name ?? 'Collection'); ?></div>
        <h1 class="fl-pd-name"><?= htmlspecialchars($product->item_name); ?></h1>
        <div class="fl-pd-price">
          <?= sf_currency($product->effective_price, $cur); ?>
          <?php if($hasDiscount): ?>
          <span class="old"><?= sf_currency($product->original_price, $cur); ?></span>
          <?php endif; ?>
        </div>
        <div class="fl-pd-stock <?= (int)$product->stock <= 0 ? 'out' : ''; ?>">
          <?= (int)$product->stock > 0 ? 'In stock &middot; Ships within 24 hours' : 'Out of stock'; ?>
        </div>
        <p class="fl-pd-desc"><?= nl2br(htmlspecialchars($product->description ?? '')); ?></p>

        <div class="fl-pd-qty">
          <button onclick="adjustDetailQty(-1)">-</button>
          <span id="detail-qty">1</span>
          <button onclick="adjustDetailQty(1)">+</button>
        </div>

        <div class="fl-pd-actions">
          <button class="fl-btn fl-btn-gold" onclick="addDetailToCart()">Add to Cart</button>
          <button class="fl-btn fl-btn-outline" onclick="addDetailToCart();window.location.href='<?= base_url('store/' . $slug . '/cart'); ?>'">Buy It Now</button>
          <?php if(!empty($settings->whatsapp_number)): ?>
          <button class="fl-btn fl-btn-wa" onclick="sendDetailWhatsApp()">Order via WhatsApp</button>
          <?php endif; ?>
        </div>

        <?php if(!empty($product_variants)): ?>
        <div class="fl-pd-variants">
          <div class="fl-section-label" style="margin-bottom:16px;">Available Variants</div>
          <div class="fl-product-grid" style="grid-template-columns:repeat(2,1fr);gap:14px;">
            <?php foreach($product_variants as $v):
              $vImg = ($v->item_image && file_exists($v->item_image)) ? base_url($v->item_image) : '';
            ?>
            <a href="<?= base_url('store/' . $slug . '/product/' . $v->id); ?>" class="fl-product-card">
              <div class="fl-product-media">
                <?php if($vImg): ?>
                <img src="<?= $vImg; ?>" alt="<?= htmlspecialchars($v->item_name); ?>" loading="lazy">
                <?php else: ?>
                <div class="fl-product-placeholder"><span><?= htmlspecialchars(substr($v->item_name, 0, 1)); ?></span></div>
                <?php endif; ?>
              </div>
              <div class="fl-product-body">
                <div class="fl-product-name"><?= htmlspecialchars($v->item_name); ?></div>
                <div class="fl-product-price"><?= sf_currency($v->effective_price, $cur); ?></div>
              </div>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <?php if(!empty($related_products)): ?>
        <div class="fl-pd-related">
          <div class="fl-section-label" style="margin-bottom:16px;">You May Also Like</div>
          <div class="fl-product-grid" style="grid-template-columns:repeat(2,1fr);gap:14px;">
            <?php foreach($related_products as $p):
              $pImg = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
            ?>
            <a href="<?= base_url('store/' . $slug . '/product/' . $p->id); ?>" class="fl-product-card">
              <div class="fl-product-media">
                <?php if($pImg): ?>
                <img src="<?= $pImg; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
                <?php else: ?>
                <div class="fl-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
                <?php endif; ?>
              </div>
              <div class="fl-product-body">
                <div class="fl-product-name"><?= htmlspecialchars($p->item_name); ?></div>
                <div class="fl-product-price"><?= sf_currency($p->sales_price, $cur); ?></div>
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
<style>
  /* Fashion Luxe premium overrides */
  .fl-pd-gallery { aspect-ratio:4/5; }
  .fl-pd-name { font-size:clamp(32px,4vw,48px); }
  .fl-btn-gold:hover { background:#B8974F; color:var(--fl-ink); }
  .fl-btn-ink:hover { background:var(--fl-gold); color:var(--fl-ink); }
  .fl-btn-outline:hover { background:var(--fl-gold); color:var(--fl-ink); border-color:var(--fl-gold); }
  .fl-product-card { background:#fff; border:1px solid var(--fl-soft); border-radius:2px; }
  .fl-product-card:hover { transform:translateY(-6px); box-shadow:0 20px 50px rgba(26,26,26,0.12); }
  .fl-product-body { padding:24px; }
  .fl-product-media { aspect-ratio:4/5; }
  .flh-header { background:var(--fl-ivory); border-bottom:1px solid var(--fl-soft); }
  .flh-header-inner { padding:22px 24px; }
  .flh-logo img { max-height:52px; max-width:220px; }
  .flh-logo-text { font-size:30px; }
  .flh-nav-link:hover { color:var(--fl-gold); }
  .flh-icon-btn:hover { color:var(--fl-gold); background:var(--fl-soft); }
</style>
