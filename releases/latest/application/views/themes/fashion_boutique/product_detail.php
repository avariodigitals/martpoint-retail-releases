<?php
/**
 * Boutique Luxe — Product Detail
 * Two-column layout with sticky gallery, serif typography and
 * related products grid in an elegant gold-accented design.
 */
$slug = $settings->store_slug ?? '';
$cur = $store_currency ?? null;
$img = ($product->item_image && file_exists($product->item_image)) ? base_url($product->item_image) : '';
$hasDiscount = $product->original_price > $product->effective_price;
?>
<style>
  .theme-fashion_boutique .mp-topbar,
  .theme-fashion_boutique .mp-announcement,
  .theme-fashion_boutique .mp-nav,
  .theme-fashion_boutique .mp-header,
  .theme-fashion_boutique .mp-mobile-menu-btn,
  .theme-fashion_boutique .mp-footer-space { display:none; height:0; }

  :root {
    --bl-ink:#3D2817;
    --bl-gold:#C9A961;
    --bl-cream:#FBF7F0;
    --bl-soft:#F5EFE6;
  }

  .bl-container { max-width:1400px; margin:0 auto; padding:0 24px; }
  @media(max-width:767px){ .bl-container { padding:0 16px; } }

  .bl-breadcrumb { padding:24px 0 0; font-family:'Lora',serif; font-size:13px; color:#9B8B7A; }
  .bl-breadcrumb a { color:#9B8B7A; text-decoration:none; }
  .bl-breadcrumb a:hover { color:var(--bl-ink); }
  .bl-breadcrumb .sep { margin:0 8px; color:#C9BBA8; }

  .bl-pd { padding:36px 0 72px; }
  .bl-pd-layout { display:grid; grid-template-columns:1.1fr 1fr; gap:52px; align-items:start; }
  @media(max-width:1023px){ .bl-pd-layout { grid-template-columns:1fr; gap:36px; } }
  .bl-pd-gallery { position:sticky; top:90px; border-radius:4px; overflow:hidden; background:var(--bl-soft); aspect-ratio:4/5; border:1px solid var(--bl-soft); }
  @media(max-width:1023px){ .bl-pd-gallery { position:static; } }
  .bl-pd-gallery img { width:100%; height:100%; object-fit:cover; }
  .bl-pd-gallery-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:var(--bl-gold); font-family:'Playfair Display',serif; font-style:italic; font-size:52px; font-weight:700; }

  .bl-pd-meta { padding-top:12px; }
  .bl-pd-kicker { font-family:'Lora',serif; font-size:11px; text-transform:uppercase; letter-spacing:0.18em; color:var(--bl-gold); font-weight:600; margin-bottom:12px; }
  .bl-pd-name { font-family:'Playfair Display',serif; font-style:italic; font-size:clamp(28px,3.5vw,42px); font-weight:700; margin-bottom:14px; color:var(--bl-ink); line-height:1.12; }
  .bl-pd-price { font-family:'Playfair Display',serif; font-size:28px; font-weight:700; margin-bottom:8px; color:var(--bl-ink); }
  .bl-pd-price .old { font-family:'Lora',serif; font-size:18px; color:#9B8B7A; text-decoration:line-through; margin-left:12px; font-weight:500; }
  .bl-pd-stock { font-family:'Lora',serif; font-size:14px; color:#4A7C59; margin-bottom:24px; font-weight:600; }
  .bl-pd-stock.out { color:#B23A3A; }
  .bl-pd-desc { font-family:'Lora',serif; color:#6B5B4A; line-height:1.75; margin-bottom:30px; font-size:15px; }
  .bl-pd-qty { display:flex; align-items:center; gap:14px; margin-bottom:26px; }
  .bl-pd-qty button { width:44px; height:44px; border-radius:4px; border:1px solid var(--bl-soft); background:#fff; font-family:'Lora',serif; font-size:18px; cursor:pointer; color:var(--bl-ink); transition:border-color .2s; }
  .bl-pd-qty button:hover { border-color:var(--bl-gold); color:var(--bl-gold); }
  .bl-pd-qty span { font-family:'Playfair Display',serif; font-size:20px; font-weight:700; min-width:32px; text-align:center; color:var(--bl-ink); }
  .bl-pd-actions { display:flex; gap:12px; flex-wrap:wrap; }
  .bl-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:15px 34px; border-radius:2px; font-family:'Lora',serif; font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; transition:transform .15s, background .2s, color .2s; cursor:pointer; border:none; text-decoration:none; }
  .bl-btn:active { transform:scale(0.98); }
  .bl-btn-gold { background:var(--bl-gold); color:var(--bl-ink); }
  .bl-btn-gold:hover { background:#B8974F; }
  .bl-btn-ink { background:var(--bl-ink); color:#fff; }
  .bl-btn-ink:hover { background:#2D1B0F; }
  .bl-btn-outline { background:#fff; color:var(--bl-ink); border:1px solid var(--bl-ink); }
  .bl-btn-outline:hover { background:var(--bl-ink); color:#fff; }
  .bl-btn-wa { background:#25D366; color:#fff; }
  .bl-btn-wa:hover { background:#1DA851; }
  .bl-pd-actions .bl-btn { flex:1; min-width:180px; }

  .bl-pd-variants { margin-top:44px; }
  .bl-pd-related { margin-top:52px; }
  .bl-section-label { font-family:'Lora',serif; font-size:11px; text-transform:uppercase; letter-spacing:0.18em; color:var(--bl-gold); font-weight:600; margin-bottom:8px; }
  .bl-section-title { font-family:'Playfair Display',serif; font-style:italic; font-size:28px; margin:0 0 26px; font-weight:700; color:var(--bl-ink); }

  .bl-product-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:24px; }
  @media(max-width:1023px){ .bl-product-grid { grid-template-columns:repeat(3,1fr); } }
  @media(max-width:767px){ .bl-product-grid { grid-template-columns:repeat(2,1fr); gap:14px; } }
  .bl-product-card { position:relative; background:#fff; border-radius:4px; overflow:hidden; border:1px solid var(--bl-soft); transition:transform .25s, box-shadow .25s; cursor:pointer; text-decoration:none; color:inherit; }
  .bl-product-card:hover { transform:translateY(-4px); box-shadow:0 12px 36px rgba(61,40,23,0.12); }
  .bl-product-media { aspect-ratio:4/5; overflow:hidden; background:var(--bl-soft); }
  .bl-product-media img { width:100%; height:100%; object-fit:cover; transition:transform .5s ease; }
  .bl-product-card:hover .bl-product-media img { transform:scale(1.06); }
  .bl-product-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--bl-soft); color:var(--bl-gold); }
  .bl-product-placeholder span { font-family:'Playfair Display',serif; font-style:italic; font-size:38px; font-weight:700; }
  .bl-product-body { padding:20px; }
  .bl-product-name { font-family:'Lora',serif; font-size:14px; font-weight:600; margin-bottom:10px; line-height:1.4; color:var(--bl-ink); }
  .bl-product-price { font-family:'Playfair Display',serif; font-size:18px; font-weight:700; color:var(--bl-ink); }
</style>

<div class="bl-container">
  <div class="bl-breadcrumb">
    <a href="<?= base_url('store/' . $slug); ?>">Home</a>
    <span class="sep">/</span>
    <a href="<?= base_url('store/' . $slug . '/products'); ?>">Boutique</a>
    <span class="sep">/</span>
    <span><?= htmlspecialchars($product->item_name); ?></span>
  </div>

  <div class="bl-pd">
    <div class="bl-pd-layout">
      <div class="bl-pd-gallery">
        <?php if($img): ?>
        <img src="<?= $img; ?>" alt="<?= htmlspecialchars($product->item_name); ?>">
        <?php else: ?>
        <div class="bl-pd-gallery-placeholder"><?= htmlspecialchars(substr($product->item_name, 0, 1)); ?></div>
        <?php endif; ?>
      </div>
      <div class="bl-pd-meta">
        <div class="bl-pd-kicker"><?= htmlspecialchars($product->category_name ?? 'Collection'); ?></div>
        <h1 class="bl-pd-name"><?= htmlspecialchars($product->item_name); ?></h1>
        <div class="bl-pd-price">
          <?= sf_currency($product->effective_price, $cur); ?>
          <?php if($hasDiscount): ?>
          <span class="old"><?= sf_currency($product->original_price, $cur); ?></span>
          <?php endif; ?>
        </div>
        <div class="bl-pd-stock <?= (int)$product->stock <= 0 ? 'out' : ''; ?>">
          <?= (int)$product->stock > 0 ? 'In stock &middot; Ships within 24 hours' : 'Out of stock'; ?>
        </div>
        <p class="bl-pd-desc"><?= nl2br(htmlspecialchars($product->description ?? '')); ?></p>

        <div class="bl-pd-qty">
          <button onclick="adjustDetailQty(-1)">-</button>
          <span id="detail-qty">1</span>
          <button onclick="adjustDetailQty(1)">+</button>
        </div>

        <div class="bl-pd-actions">
          <button class="bl-btn bl-btn-gold" onclick="addDetailToCart()">Add to Cart</button>
          <button class="bl-btn bl-btn-outline" onclick="addDetailToCart();window.location.href='<?= base_url('store/' . $slug . '/cart'); ?>'">Buy It Now</button>
          <?php if(!empty($settings->whatsapp_number)): ?>
          <button class="bl-btn bl-btn-wa" onclick="sendDetailWhatsApp()">Order via WhatsApp</button>
          <?php endif; ?>
        </div>

        <?php if(!empty($product_variants)): ?>
        <div class="bl-pd-variants">
          <div class="bl-section-label" style="margin-bottom:16px;">Available Variants</div>
          <div class="bl-product-grid" style="grid-template-columns:repeat(2,1fr);gap:14px;">
            <?php foreach($product_variants as $v):
              $vImg = ($v->item_image && file_exists($v->item_image)) ? base_url($v->item_image) : '';
            ?>
            <a href="<?= base_url('store/' . $slug . '/product/' . $v->id); ?>" class="bl-product-card">
              <div class="bl-product-media">
                <?php if($vImg): ?>
                <img src="<?= $vImg; ?>" alt="<?= htmlspecialchars($v->item_name); ?>" loading="lazy">
                <?php else: ?>
                <div class="bl-product-placeholder"><span><?= htmlspecialchars(substr($v->item_name, 0, 1)); ?></span></div>
                <?php endif; ?>
              </div>
              <div class="bl-product-body">
                <div class="bl-product-name"><?= htmlspecialchars($v->item_name); ?></div>
                <div class="bl-product-price"><?= sf_currency($v->effective_price, $cur); ?></div>
              </div>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <?php if(!empty($related_products)): ?>
        <div class="bl-pd-related">
          <div class="bl-section-label" style="margin-bottom:16px;">You May Also Like</div>
          <div class="bl-product-grid" style="grid-template-columns:repeat(2,1fr);gap:14px;">
            <?php foreach($related_products as $p):
              $pImg = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
            ?>
            <a href="<?= base_url('store/' . $slug . '/product/' . $p->id); ?>" class="bl-product-card">
              <div class="bl-product-media">
                <?php if($pImg): ?>
                <img src="<?= $pImg; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
                <?php else: ?>
                <div class="bl-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
                <?php endif; ?>
              </div>
              <div class="bl-product-body">
                <div class="bl-product-name"><?= htmlspecialchars($p->item_name); ?></div>
                <div class="bl-product-price"><?= sf_currency($p->sales_price, $cur); ?></div>
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
