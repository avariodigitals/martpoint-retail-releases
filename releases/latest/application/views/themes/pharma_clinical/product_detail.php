<?php
/**
 * Pharma Clinical — Product Detail
 * Clean two-column layout with sticky gallery, medical-blue accents
 * and related products grid in a professional clinical design.
 */
$slug = $settings->store_slug ?? '';
$cur = $store_currency ?? null;
$img = ($product->item_image && file_exists($product->item_image)) ? base_url($product->item_image) : '';
$hasDiscount = $product->original_price > $product->effective_price;
?>
<style>
  .theme-pharma_clinical .mp-topbar,
  .theme-pharma_clinical .mp-announcement,
  .theme-pharma_clinical .mp-nav,
  .theme-pharma_clinical .mp-header,
  .theme-pharma_clinical .mp-mobile-menu-btn,
  .theme-pharma_clinical .mp-footer-space { display:none !important; }

  :root {
    --pc-blue:#005EB8;
    --pc-blue-dark:#004A94;
    --pc-teal:#00A86B;
    --pc-dark:#0F172A;
    --pc-gray:#64748B;
    --pc-light:#F1F5F9;
    --pc-border:#E2E8F0;
    --pc-accent:#38BDF8;
  }

  .pc-container { max-width:1400px; margin:0 auto; padding:0 24px; }
  @media(max-width:767px){ .pc-container { padding:0 16px; } }

  .pc-breadcrumb { padding:24px 0 0; font-family:'Inter',sans-serif; font-size:13px; color:var(--pc-gray); }
  .pc-breadcrumb a { color:var(--pc-blue); text-decoration:none; }
  .pc-breadcrumb a:hover { color:var(--pc-dark); }
  .pc-breadcrumb .sep { margin:0 8px; color:var(--pc-accent); }

  .pc-pd { padding:36px 0 72px; }
  .pc-pd-layout { display:grid; grid-template-columns:1.1fr 1fr; gap:48px; align-items:start; }
  @media(max-width:1023px){ .pc-pd-layout { grid-template-columns:1fr; gap:32px; } }
  .pc-pd-gallery { position:sticky; top:90px; border-radius:16px; overflow:hidden; background:var(--pc-light); aspect-ratio:1/1; border:1px solid var(--pc-border); }
  @media(max-width:1023px){ .pc-pd-gallery { position:static; } }
  .pc-pd-gallery img { width:100%; height:100%; object-fit:cover; }
  .pc-pd-gallery-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:var(--pc-blue); font-family:'Inter',sans-serif; font-size:52px; font-weight:800; }

  .pc-pd-meta { padding-top:8px; }
  .pc-pd-kicker { font-family:'Inter',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.16em; color:var(--pc-teal); font-weight:600; margin-bottom:12px; }
  .pc-pd-name { font-family:'Inter',sans-serif; font-size:clamp(28px,3vw,38px); font-weight:800; margin-bottom:14px; color:var(--pc-dark); line-height:1.12; letter-spacing:-0.01em; }
  .pc-pd-price { font-family:'Inter',sans-serif; font-size:26px; font-weight:800; margin-bottom:8px; color:var(--pc-dark); }
  .pc-pd-price .old { font-family:'Inter',sans-serif; font-size:16px; color:var(--pc-gray); text-decoration:line-through; margin-left:12px; font-weight:500; }
  .pc-pd-stock { font-family:'Inter',sans-serif; font-size:14px; color:var(--pc-teal); margin-bottom:24px; font-weight:600; }
  .pc-pd-stock.out { color:#DC2626; }
  .pc-pd-desc { font-family:'Inter',sans-serif; color:var(--pc-gray); line-height:1.75; margin-bottom:28px; font-size:15px; }
  .pc-pd-qty { display:flex; align-items:center; gap:14px; margin-bottom:26px; }
  .pc-pd-qty button { width:44px; height:44px; border-radius:8px; border:1px solid var(--pc-border); background:#fff; font-family:'Inter',sans-serif; font-size:18px; cursor:pointer; color:var(--pc-dark); transition:border-color .2s; }
  .pc-pd-qty button:hover { border-color:var(--pc-blue); color:var(--pc-blue); }
  .pc-pd-qty span { font-family:'Inter',sans-serif; font-size:20px; font-weight:800; min-width:32px; text-align:center; color:var(--pc-dark); }
  .pc-pd-actions { display:flex; gap:12px; flex-wrap:wrap; }
  .pc-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:15px 34px; border-radius:8px; font-family:'Inter',sans-serif; font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.08em; transition:transform .15s, background .2s, color .2s; cursor:pointer; border:none; text-decoration:none; }
  .pc-btn:active { transform:scale(0.98); }
  .pc-btn-blue { background:var(--pc-blue); color:#fff; }
  .pc-btn-blue:hover { background:var(--pc-blue-dark); }
  .pc-btn-outline { background:#fff; color:var(--pc-blue); border:1px solid var(--pc-blue); }
  .pc-btn-outline:hover { background:var(--pc-blue); color:#fff; }
  .pc-btn-wa { background:#25D366; color:#fff; }
  .pc-btn-wa:hover { background:#1DA851; }
  .pc-btn-teal { background:var(--pc-teal); color:#fff; }
  .pc-btn-teal:hover { background:var(--pc-teal-dark); }
  .pc-pd-actions .pc-btn { flex:1; min-width:160px; }

  .pc-pd-variants { margin-top:40px; }
  .pc-pd-related { margin-top:48px; }
  .pc-section-label { font-family:'Inter',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.16em; color:var(--pc-teal); font-weight:600; margin-bottom:8px; }
  .pc-section-title { font-family:'Inter',sans-serif; font-size:24px; margin:0 0 24px; font-weight:800; color:var(--pc-dark); letter-spacing:-0.01em; }

  .pc-product-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:20px; }
  @media(max-width:1023px){ .pc-product-grid { grid-template-columns:repeat(3,1fr); } }
  @media(max-width:767px){ .pc-product-grid { grid-template-columns:repeat(2,1fr); gap:12px; } }
  .pc-product-card { position:relative; background:#fff; border-radius:12px; overflow:hidden; border:1px solid var(--pc-border); transition:transform .25s, box-shadow .25s; cursor:pointer; text-decoration:none; color:inherit; }
  .pc-product-card:hover { transform:translateY(-4px); box-shadow:0 12px 32px rgba(0,94,184,0.1); }
  .pc-product-media { aspect-ratio:1/1; overflow:hidden; background:var(--pc-light); }
  .pc-product-media img { width:100%; height:100%; object-fit:cover; transition:transform .4s ease; }
  .pc-product-card:hover .pc-product-media img { transform:scale(1.05); }
  .pc-product-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--pc-light); color:var(--pc-blue); }
  .pc-product-placeholder span { font-family:'Inter',sans-serif; font-size:36px; font-weight:800; }
  .pc-product-body { padding:16px; }
  .pc-product-name { font-family:'Inter',sans-serif; font-size:14px; font-weight:600; margin-bottom:8px; line-height:1.4; color:var(--pc-dark); }
  .pc-product-price { font-family:'Inter',sans-serif; font-size:16px; font-weight:800; color:var(--pc-dark); }
</style>

<div class="pc-container">
  <div class="pc-breadcrumb">
    <a href="<?= base_url('store/' . $slug); ?>">Home</a>
    <span class="sep">/</span>
    <a href="<?= base_url('store/' . $slug . '/products'); ?>">Shop</a>
    <span class="sep">/</span>
    <span><?= htmlspecialchars($product->item_name); ?></span>
  </div>

  <div class="pc-pd">
    <div class="pc-pd-layout">
      <div class="pc-pd-gallery">
        <?php if($img): ?>
        <img src="<?= $img; ?>" alt="<?= htmlspecialchars($product->item_name); ?>">
        <?php else: ?>
        <div class="pc-pd-gallery-placeholder"><?= htmlspecialchars(substr($product->item_name, 0, 1)); ?></div>
        <?php endif; ?>
      </div>
      <div class="pc-pd-meta">
        <div class="pc-pd-kicker"><?= htmlspecialchars($product->category_name ?? 'Medicine'); ?></div>
        <h1 class="pc-pd-name"><?= htmlspecialchars($product->item_name); ?></h1>
        <div class="pc-pd-price">
          <?= sf_currency($product->effective_price, $cur); ?>
          <?php if($hasDiscount): ?>
          <span class="old"><?= sf_currency($product->original_price, $cur); ?></span>
          <?php endif; ?>
        </div>
        <div class="pc-pd-stock <?= (int)$product->stock <= 0 ? 'out' : ''; ?>">
          <?= (int)$product->stock > 0 ? 'In Stock &middot; Ships within 24 hours' : 'Out of Stock'; ?>
        </div>
        <p class="pc-pd-desc"><?= nl2br(htmlspecialchars($product->description ?? '')); ?></p>

        <div class="pc-pd-qty">
          <button onclick="adjustDetailQty(-1)">-</button>
          <span id="detail-qty">1</span>
          <button onclick="adjustDetailQty(1)">+</button>
        </div>

        <div class="pc-pd-actions">
          <button class="pc-btn pc-btn-blue" onclick="addDetailToCart()">Add to Cart</button>
          <button class="pc-btn pc-btn-outline" onclick="addDetailToCart();window.location.href='<?= base_url('store/' . $slug . '/cart'); ?>'">Buy It Now</button>
          <?php if(!empty($settings->whatsapp_number)): ?>
          <button class="pc-btn pc-btn-wa" onclick="sendDetailWhatsApp()">Order via WhatsApp</button>
          <?php endif; ?>
        </div>

        <?php if(!empty($product_variants)): ?>
        <div class="pc-pd-variants">
          <div class="pc-section-label" style="margin-bottom:16px;">Available Variants</div>
          <div class="pc-product-grid" style="grid-template-columns:repeat(2,1fr);gap:12px;">
            <?php foreach($product_variants as $v):
              $vImg = ($v->item_image && file_exists($v->item_image)) ? base_url($v->item_image) : '';
            ?>
            <a href="<?= base_url('store/' . $slug . '/product/' . $v->id); ?>" class="pc-product-card">
              <div class="pc-product-media">
                <?php if($vImg): ?>
                <img src="<?= $vImg; ?>" alt="<?= htmlspecialchars($v->item_name); ?>" loading="lazy">
                <?php else: ?>
                <div class="pc-product-placeholder"><span><?= htmlspecialchars(substr($v->item_name, 0, 1)); ?></span></div>
                <?php endif; ?>
              </div>
              <div class="pc-product-body">
                <div class="pc-product-name"><?= htmlspecialchars($v->item_name); ?></div>
                <div class="pc-product-price"><?= sf_currency($v->effective_price, $cur); ?></div>
              </div>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <?php if(!empty($related_products)): ?>
        <div class="pc-pd-related">
          <div class="pc-section-label" style="margin-bottom:16px;">You May Also Need</div>
          <div class="pc-product-grid" style="grid-template-columns:repeat(2,1fr);gap:12px;">
            <?php foreach($related_products as $p):
              $pImg = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
            ?>
            <a href="<?= base_url('store/' . $slug . '/product/' . $p->id); ?>" class="pc-product-card">
              <div class="pc-product-media">
                <?php if($pImg): ?>
                <img src="<?= $pImg; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
                <?php else: ?>
                <div class="pc-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
                <?php endif; ?>
              </div>
              <div class="pc-product-body">
                <div class="pc-product-name"><?= htmlspecialchars($p->item_name); ?></div>
                <div class="pc-product-price"><?= sf_currency($p->sales_price, $cur); ?></div>
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
