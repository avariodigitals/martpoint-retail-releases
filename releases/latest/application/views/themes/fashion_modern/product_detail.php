<?php
/**
 * Modern Minimal — Product Detail
 * Two-column layout with sticky gallery, clean typography and
 * related products grid.
 */
$slug = $settings->store_slug ?? '';
$cur = $store_currency ?? null;
$img = ($product->item_image && file_exists($product->item_image)) ? base_url($product->item_image) : '';
$hasDiscount = $product->original_price > $product->effective_price;
?>
<style>
  .theme-fashion_modern .mp-footer-space { height:0; }
  .fm-container { max-width:1400px; margin:0 auto; padding:0 24px; }
  @media(max-width:767px){ .fm-container { padding:0 16px; } }

  .fm-breadcrumb { padding:20px 0 0; font-size:13px; color:#64748B; }
  .fm-breadcrumb a { color:#64748B; text-decoration:none; }
  .fm-breadcrumb a:hover { color:#0F172A; }
  .fm-breadcrumb .sep { margin:0 8px; color:#CBD5E1; }

  .fm-pd { padding:32px 0 64px; }
  .fm-pd-layout { display:grid; grid-template-columns:1.1fr 1fr; gap:48px; align-items:start; }
  @media(max-width:1023px){ .fm-pd-layout { grid-template-columns:1fr; gap:32px; } }
  .fm-pd-gallery { position:sticky; top:90px; border-radius:16px; overflow:hidden; background:#F8FAFC; aspect-ratio:4/5; }
  @media(max-width:1023px){ .fm-pd-gallery { position:static; } }
  .fm-pd-gallery img { width:100%; height:100%; object-fit:cover; }
  .fm-pd-gallery-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#CBD5E1; font-family:'Playfair Display',serif; font-size:48px; font-weight:700; }

  .fm-pd-meta { padding-top:10px; }
  .fm-pd-kicker { font-size:12px; text-transform:uppercase; letter-spacing:0.12em; color:#6366F1; font-weight:700; margin-bottom:10px; }
  .fm-pd-name { font-family:'Playfair Display',serif; font-size:clamp(28px,3.5vw,42px); font-weight:700; margin-bottom:12px; color:#0F172A; letter-spacing:-0.02em; line-height:1.1; }
  .fm-pd-price { font-size:28px; font-weight:800; margin-bottom:6px; color:#0F172A; }
  .fm-pd-price .old { font-size:18px; color:#94A3B8; text-decoration:line-through; margin-left:10px; font-weight:500; }
  .fm-pd-stock { font-size:14px; color:#059669; margin-bottom:20px; font-weight:600; }
  .fm-pd-stock.out { color:#EF4444; }
  .fm-pd-desc { color:#475569; line-height:1.7; margin-bottom:28px; font-size:15px; }
  .fm-pd-qty { display:flex; align-items:center; gap:12px; margin-bottom:24px; }
  .fm-pd-qty button { width:44px; height:44px; border-radius:50%; border:1px solid #E2E8F0; background:#fff; font-size:18px; cursor:pointer; color:#0F172A; transition:border-color .2s; }
  .fm-pd-qty button:hover { border-color:#0F172A; }
  .fm-pd-qty span { font-size:18px; font-weight:700; min-width:30px; text-align:center; color:#0F172A; }
  .fm-pd-actions { display:flex; gap:12px; flex-wrap:wrap; }
  .fm-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:15px 32px; border-radius:999px; font-size:14px; font-weight:600; transition:transform .15s, background .2s; cursor:pointer; border:none; text-decoration:none; }
  .fm-btn:active { transform:scale(0.98); }
  .fm-btn-primary { background:#6366F1; color:#fff; }
  .fm-btn-primary:hover { background:#4F46E5; }
  .fm-btn-dark { background:#0F172A; color:#fff; }
  .fm-btn-dark:hover { background:#1E293B; }
  .fm-btn-outline { background:#fff; color:#0F172A; border:1.5px solid #0F172A; }
  .fm-btn-outline:hover { background:#0F172A; color:#fff; }
  .fm-btn-wa { background:#25D366; color:#fff; }
  .fm-btn-wa:hover { background:#1DA851; }
  .fm-pd-actions .fm-btn { flex:1; min-width:180px; }

  .fm-pd-variants { margin-top:40px; }
  .fm-pd-related { margin-top:48px; }
  .fm-section-label { font-size:12px; text-transform:uppercase; letter-spacing:0.12em; color:#6366F1; font-weight:700; margin-bottom:6px; }
  .fm-section-title { font-family:'Playfair Display',serif; font-size:28px; margin:0 0 24px; font-weight:700; letter-spacing:-0.02em; color:#0F172A; }

  .fm-product-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:24px; }
  @media(max-width:1023px){ .fm-product-grid { grid-template-columns:repeat(3,1fr); } }
  @media(max-width:767px){ .fm-product-grid { grid-template-columns:repeat(2,1fr); gap:14px; } }
  .fm-product-card { position:relative; background:#fff; border-radius:16px; overflow:hidden; border:1px solid #E2E8F0; transition:transform .2s, box-shadow .2s; cursor:pointer; text-decoration:none; color:inherit; }
  .fm-product-card:hover { transform:translateY(-4px); box-shadow:0 16px 40px rgba(15,23,42,0.1); }
  .fm-product-media { aspect-ratio:4/5; overflow:hidden; background:#F8FAFC; }
  .fm-product-media img { width:100%; height:100%; object-fit:cover; transition:transform .45s ease; }
  .fm-product-card:hover .fm-product-media img { transform:scale(1.06); }
  .fm-product-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:#F8FAFC; color:#CBD5E1; }
  .fm-product-placeholder span { font-family:'Playfair Display',serif; font-size:36px; font-weight:700; }
  .fm-product-body { padding:18px; }
  .fm-product-name { font-size:14px; font-weight:600; margin-bottom:8px; line-height:1.35; color:#0F172A; }
  .fm-product-price { font-size:17px; font-weight:700; color:#0F172A; }
</style>

<div class="fm-container">
  <div class="fm-breadcrumb">
    <a href="<?= base_url('store/' . $slug); ?>">Home</a>
    <span class="sep">/</span>
    <a href="<?= base_url('store/' . $slug . '/products'); ?>">Shop</a>
    <span class="sep">/</span>
    <span><?= htmlspecialchars($product->item_name); ?></span>
  </div>

  <div class="fm-pd">
    <div class="fm-pd-layout">
      <div class="fm-pd-gallery">
        <?php if($img): ?>
        <img src="<?= $img; ?>" alt="<?= htmlspecialchars($product->item_name); ?>">
        <?php else: ?>
        <div class="fm-pd-gallery-placeholder"><?= htmlspecialchars(substr($product->item_name, 0, 1)); ?></div>
        <?php endif; ?>
      </div>
      <div class="fm-pd-meta">
        <div class="fm-pd-kicker"><?= htmlspecialchars($product->category_name ?? 'Collection'); ?></div>
        <h1 class="fm-pd-name"><?= htmlspecialchars($product->item_name); ?></h1>
        <div class="fm-pd-price">
          <?= sf_currency($product->effective_price, $cur); ?>
          <?php if($hasDiscount): ?>
          <span class="old"><?= sf_currency($product->original_price, $cur); ?></span>
          <?php endif; ?>
        </div>
        <div class="fm-pd-stock <?= (int)$product->stock <= 0 ? 'out' : ''; ?>">
          <?= (int)$product->stock > 0 ? 'In stock &middot; Ships within 24 hours' : 'Out of stock'; ?>
        </div>
        <p class="fm-pd-desc"><?= nl2br(htmlspecialchars($product->description ?? '')); ?></p>

        <div class="fm-pd-qty">
          <button onclick="adjustDetailQty(-1)">-</button>
          <span id="detail-qty">1</span>
          <button onclick="adjustDetailQty(1)">+</button>
        </div>

        <div class="fm-pd-actions">
          <button class="fm-btn fm-btn-primary" onclick="addDetailToCart()">Add to Cart</button>
          <button class="fm-btn fm-btn-outline" onclick="addDetailToCart();window.location.href='<?= base_url('store/' . $slug . '/cart'); ?>'">Buy It Now</button>
          <?php if(!empty($settings->whatsapp_number)): ?>
          <button class="fm-btn fm-btn-wa" onclick="sendDetailWhatsApp()">Order via WhatsApp</button>
          <?php endif; ?>
        </div>

        <?php if(!empty($product_variants)): ?>
        <div class="fm-pd-variants">
          <div class="fm-section-label" style="margin-bottom:14px;">Available Variants</div>
          <div class="fm-product-grid" style="grid-template-columns:repeat(2,1fr);gap:14px;">
            <?php foreach($product_variants as $v):
              $vImg = ($v->item_image && file_exists($v->item_image)) ? base_url($v->item_image) : '';
            ?>
            <a href="<?= base_url('store/' . $slug . '/product/' . $v->id); ?>" class="fm-product-card">
              <div class="fm-product-media">
                <?php if($vImg): ?>
                <img src="<?= $vImg; ?>" alt="<?= htmlspecialchars($v->item_name); ?>" loading="lazy">
                <?php else: ?>
                <div class="fm-product-placeholder"><span><?= htmlspecialchars(substr($v->item_name, 0, 1)); ?></span></div>
                <?php endif; ?>
              </div>
              <div class="fm-product-body">
                <div class="fm-product-name"><?= htmlspecialchars($v->item_name); ?></div>
                <div class="fm-product-price"><?= sf_currency($v->effective_price, $cur); ?></div>
              </div>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <?php if(!empty($related_products)): ?>
        <div class="fm-pd-related">
          <div class="fm-section-label" style="margin-bottom:14px;">You May Also Like</div>
          <div class="fm-product-grid" style="grid-template-columns:repeat(2,1fr);gap:14px;">
            <?php foreach($related_products as $p):
              $pImg = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
            ?>
            <a href="<?= base_url('store/' . $slug . '/product/' . $p->id); ?>" class="fm-product-card">
              <div class="fm-product-media">
                <?php if($pImg): ?>
                <img src="<?= $pImg; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
                <?php else: ?>
                <div class="fm-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
                <?php endif; ?>
              </div>
              <div class="fm-product-body">
                <div class="fm-product-name"><?= htmlspecialchars($p->item_name); ?></div>
                <div class="fm-product-price"><?= sf_currency($p->sales_price, $cur); ?></div>
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
