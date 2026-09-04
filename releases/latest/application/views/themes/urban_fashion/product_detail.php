<?php
/**
 * Urban Editorial — Product Detail
 * Bold two-column layout with sharp corners and uppercase typography.
 */
$slug = $settings->store_slug ?? '';
$cur = $store_currency ?? null;
$img = ($product->item_image && file_exists($product->item_image)) ? base_url($product->item_image) : '';
$hasDiscount = $product->original_price > $product->effective_price;
?>
<style>
  .theme-urban_fashion .mp-footer-space { height:0; }
  .ue-container { max-width:1400px; margin:0 auto; padding:0 24px; }
  @media(max-width:767px){ .ue-container { padding:0 16px; } }

  .ue-breadcrumb { padding:20px 0 0; font-size:11px; color:#999; text-transform:uppercase; letter-spacing:0.08em; font-weight:600; }
  .ue-breadcrumb a { color:#999; text-decoration:none; }
  .ue-breadcrumb a:hover { color:#0A0A0A; }
  .ue-breadcrumb .sep { margin:0 8px; color:#E5E5E0; }

  .ue-pd { padding:32px 0 64px; }
  .ue-pd-layout { display:grid; grid-template-columns:1.1fr 1fr; gap:48px; align-items:start; }
  @media(max-width:1023px){ .ue-pd-layout { grid-template-columns:1fr; gap:32px; } }
  .ue-pd-gallery { position:sticky; top:90px; overflow:hidden; background:#F2F2F0; aspect-ratio:4/5; border-radius:0; }
  @media(max-width:1023px){ .ue-pd-gallery { position:static; } }
  .ue-pd-gallery img { width:100%; height:100%; object-fit:cover; }
  .ue-pd-gallery-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#CCC; font-family:'Montserrat',sans-serif; font-size:48px; font-weight:800; }

  .ue-pd-meta { padding-top:10px; }
  .ue-pd-kicker { font-size:11px; text-transform:uppercase; letter-spacing:0.18em; color:#FF3B30; font-weight:700; margin-bottom:14px; }
  .ue-pd-name { font-family:'Montserrat',sans-serif; font-size:clamp(28px,4vw,44px); font-weight:800; margin-bottom:18px; color:#0A0A0A; text-transform:uppercase; letter-spacing:-0.01em; line-height:1.05; }
  .ue-pd-price { font-size:26px; font-weight:800; margin-bottom:8px; color:#0A0A0A; }
  .ue-pd-price .old { font-size:18px; color:#999; text-decoration:line-through; margin-left:10px; font-weight:500; }
  .ue-pd-stock { font-size:12px; color:#059669; margin-bottom:24px; font-weight:700; text-transform:uppercase; letter-spacing:0.08em; }
  .ue-pd-stock.out { color:#EF4444; }
  .ue-pd-desc { color:#4A4A4A; line-height:1.7; margin-bottom:32px; font-size:15px; }
  .ue-pd-qty { display:flex; align-items:center; gap:16px; margin-bottom:24px; }
  .ue-pd-qty button { width:48px; height:48px; border-radius:0; border:1px solid #0A0A0A; background:#fff; font-size:20px; cursor:pointer; color:#0A0A0A; transition:background .2s; }
  .ue-pd-qty button:hover { background:#0A0A0A; color:#fff; }
  .ue-pd-qty span { font-family:'Montserrat',sans-serif; font-size:20px; font-weight:800; min-width:40px; text-align:center; color:#0A0A0A; }
  .ue-pd-actions { display:flex; gap:12px; flex-wrap:wrap; }
  .ue-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:16px 36px; font-size:12px; font-weight:700; transition:transform .15s, background .2s; cursor:pointer; border:none; text-decoration:none; text-transform:uppercase; letter-spacing:0.1em; }
  .ue-btn:active { transform:scale(0.98); }
  .ue-btn-primary { background:#FF3B30; color:#fff; border-radius:0; }
  .ue-btn-primary:hover { background:#DC2F26; }
  .ue-btn-dark { background:#0A0A0A; color:#fff; border-radius:0; }
  .ue-btn-dark:hover { background:#FF3B30; }
  .ue-btn-outline { background:#fff; color:#0A0A0A; border:1.5px solid #0A0A0A; border-radius:0; }
  .ue-btn-outline:hover { background:#0A0A0A; color:#fff; }
  .ue-btn-wa { background:#25D366; color:#fff; border-radius:0; }
  .ue-btn-wa:hover { background:#1DA851; }
  .ue-pd-actions .ue-btn { flex:1; min-width:180px; }

  .ue-pd-variants { margin-top:40px; }
  .ue-pd-related { margin-top:48px; }
  .ue-section-label { font-size:11px; text-transform:uppercase; letter-spacing:0.18em; color:#FF3B30; font-weight:700; margin-bottom:6px; }
  .ue-section-title { font-family:'Montserrat',sans-serif; font-size:24px; margin:0 0 24px; font-weight:800; text-transform:uppercase; letter-spacing:-0.01em; color:#0A0A0A; }

  .ue-product-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:14px; }
  .ue-product-card { position:relative; background:transparent; overflow:hidden; transition:transform .2s; cursor:pointer; border:none; text-decoration:none; color:inherit; }
  .ue-product-card:hover { transform:translateY(-6px); }
  .ue-product-media { aspect-ratio:4/5; overflow:hidden; background:#F2F2F0; }
  .ue-product-media img { width:100%; height:100%; object-fit:cover; transition:transform .45s ease; }
  .ue-product-card:hover .ue-product-media img { transform:scale(1.06); }
  .ue-product-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:#F2F2F0; color:#CCC; }
  .ue-product-placeholder span { font-family:'Montserrat',sans-serif; font-size:36px; font-weight:800; }
  .ue-product-body { padding:14px 2px; }
  .ue-product-name { font-size:13px; font-weight:500; margin-bottom:8px; line-height:1.35; color:#3A3A3A; }
  .ue-product-price { font-size:15px; font-weight:700; color:#0A0A0A; }
</style>

<div class="ue-container">
  <div class="ue-breadcrumb">
    <a href="<?= base_url('store/' . $slug); ?>">Home</a>
    <span class="sep">/</span>
    <a href="<?= base_url('store/' . $slug . '/products'); ?>">Shop</a>
    <span class="sep">/</span>
    <span><?= htmlspecialchars($product->item_name); ?></span>
  </div>

  <div class="ue-pd">
    <div class="ue-pd-layout">
      <div class="ue-pd-gallery">
        <?php if($img): ?>
        <img src="<?= $img; ?>" alt="<?= htmlspecialchars($product->item_name); ?>">
        <?php else: ?>
        <div class="ue-pd-gallery-placeholder"><?= htmlspecialchars(substr($product->item_name, 0, 1)); ?></div>
        <?php endif; ?>
      </div>
      <div class="ue-pd-meta">
        <div class="ue-pd-kicker"><?= htmlspecialchars($product->category_name ?? 'Collection'); ?></div>
        <h1 class="ue-pd-name"><?= htmlspecialchars($product->item_name); ?></h1>
        <div class="ue-pd-price">
          <?= sf_currency($product->effective_price, $cur); ?>
          <?php if($hasDiscount): ?>
          <span class="old"><?= sf_currency($product->original_price, $cur); ?></span>
          <?php endif; ?>
        </div>
        <div class="ue-pd-stock <?= (int)$product->stock <= 0 ? 'out' : ''; ?>">
          <?= (int)$product->stock > 0 ? 'In Stock &middot; Authentic Guaranteed' : 'Out of Stock'; ?>
        </div>
        <p class="ue-pd-desc"><?= nl2br(htmlspecialchars($product->description ?? '')); ?></p>

        <div class="ue-pd-qty">
          <button onclick="adjustDetailQty(-1)">-</button>
          <span id="detail-qty">1</span>
          <button onclick="adjustDetailQty(1)">+</button>
        </div>

        <div class="ue-pd-actions">
          <button class="ue-btn ue-btn-primary" onclick="addDetailToCart()">Add To Bag</button>
          <button class="ue-btn ue-btn-outline" onclick="addDetailToCart();window.location.href='<?= base_url('store/' . $slug . '/cart'); ?>'">Buy It Now</button>
          <?php if(!empty($settings->whatsapp_number)): ?>
          <button class="ue-btn ue-btn-wa" onclick="sendDetailWhatsApp()">Order Via WhatsApp</button>
          <?php endif; ?>
        </div>

        <?php if(!empty($product_variants)): ?>
        <div class="ue-pd-variants">
          <div class="ue-section-label" style="margin-bottom:14px;">Available Variants</div>
          <div class="ue-product-grid">
            <?php foreach($product_variants as $v):
              $vImg = ($v->item_image && file_exists($v->item_image)) ? base_url($v->item_image) : '';
            ?>
            <a href="<?= base_url('store/' . $slug . '/product/' . $v->id); ?>" class="ue-product-card">
              <div class="ue-product-media">
                <?php if($vImg): ?>
                <img src="<?= $vImg; ?>" alt="<?= htmlspecialchars($v->item_name); ?>" loading="lazy">
                <?php else: ?>
                <div class="ue-product-placeholder"><span><?= htmlspecialchars(substr($v->item_name, 0, 1)); ?></span></div>
                <?php endif; ?>
              </div>
              <div class="ue-product-body">
                <div class="ue-product-name"><?= htmlspecialchars($v->item_name); ?></div>
                <div class="ue-product-price"><?= sf_currency($v->effective_price, $cur); ?></div>
              </div>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <?php if(!empty($related_products)): ?>
        <div class="ue-pd-related">
          <div class="ue-section-label" style="margin-bottom:14px;">You May Also Like</div>
          <div class="ue-product-grid">
            <?php foreach($related_products as $p):
              $pImg = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
            ?>
            <a href="<?= base_url('store/' . $slug . '/product/' . $p->id); ?>" class="ue-product-card">
              <div class="ue-product-media">
                <?php if($pImg): ?>
                <img src="<?= $pImg; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
                <?php else: ?>
                <div class="ue-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
                <?php endif; ?>
              </div>
              <div class="ue-product-body">
                <div class="ue-product-name"><?= htmlspecialchars($p->item_name); ?></div>
                <div class="ue-product-price"><?= sf_currency($p->sales_price, $cur); ?></div>
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
  const detailProduct = { id: <?= $product->id; ?>, name: '<?= htmlspecialchars(addslashes($product->item_name)); ?>', price: <?= $product->effective_price; ?>, image: '<?= $product->item_image; ?>', stock: <?= (int)$product->stock; ?> };
  function adjustDetailQty(d){ detailQty = Math.max(1, detailQty + d); document.getElementById('detail-qty').textContent = detailQty; }
  function addDetailToCart(){ addToCart(detailProduct.id, 'product', detailProduct.name, detailProduct.price, detailProduct.image, detailQty, detailProduct.stock); }
  function sendDetailWhatsApp(){ let msg = 'Hello, I am interested in: ' + detailProduct.name + ' — ' + formatMoney(detailProduct.price); const wnum = '<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? ''); ?>'; if(wnum) window.open('https://wa.me/' + wnum + '?text=' + encodeURIComponent(msg), '_blank'); }
</script>
