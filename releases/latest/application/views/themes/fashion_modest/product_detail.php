<?php
/**
 * Modest Studio — Product Detail
 * Two-column layout with sticky gallery, warm Lora typography and
 * related products grid.
 */
$slug = $settings->store_slug ?? '';
$cur = $store_currency ?? null;
$img = ($product->item_image && file_exists($product->item_image)) ? base_url($product->item_image) : '';
$hasDiscount = $product->original_price > $product->effective_price;
?>
<style>
  :root { --ms-ink:#1F2937; --ms-warm:#C2956A; --ms-cream:#FAF6F1; --ms-soft:#F0E9E0; --ms-sage:#8B9D83; }
  .theme-fashion_modest .mp-footer-space { height:0 !important; }

  .ms-container { max-width:1400px; margin:0 auto; padding:0 24px; }
  @media(max-width:767px){ .ms-container { padding:0 16px; } }

  .ms-breadcrumb { padding:20px 0 0; font-family:'Lora',serif; font-size:13px; color:#6B6B6B; }
  .ms-breadcrumb a { color:#6B6B6B; text-decoration:none; }
  .ms-breadcrumb a:hover { color:var(--ms-warm); }
  .ms-breadcrumb .sep { margin:0 8px; color:#CBD5E1; }

  .ms-pd { padding:32px 0 64px; }
  .ms-pd-layout { display:grid; grid-template-columns:1.1fr 1fr; gap:48px; align-items:start; }
  @media(max-width:1023px){ .ms-pd-layout { grid-template-columns:1fr; gap:32px; } }
  .ms-pd-gallery { position:sticky; top:90px; border-radius:12px; overflow:hidden; background:var(--ms-cream); aspect-ratio:4/5; }
  @media(max-width:1023px){ .ms-pd-gallery { position:static; } }
  .ms-pd-gallery img { width:100%; height:100%; object-fit:cover; }
  .ms-pd-gallery-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:var(--ms-soft); font-family:'Lora',serif; font-size:48px; font-weight:600; }

  .ms-pd-meta { padding-top:10px; }
  .ms-pd-kicker { font-family:'Lora',serif; font-size:12px; letter-spacing:0.06em; color:var(--ms-warm); font-weight:600; margin-bottom:10px; }
  .ms-pd-name { font-family:'Lora',serif; font-size:clamp(28px,3.5vw,42px); font-weight:600; margin-bottom:12px; color:var(--ms-ink); line-height:1.15; }
  .ms-pd-price { font-family:'Lora',serif; font-size:28px; font-weight:700; margin-bottom:6px; color:var(--ms-ink); }
  .ms-pd-price .old { font-size:18px; color:#94A3B8; text-decoration:line-through; margin-left:10px; font-weight:500; }
  .ms-pd-stock { font-family:'Lora',serif; font-size:14px; color:var(--ms-sage); margin-bottom:20px; font-weight:600; }
  .ms-pd-stock.out { color:#EF4444; }
  .ms-pd-desc { font-family:'Lora',serif; color:#4B5563; line-height:1.8; margin-bottom:28px; font-size:15px; }
  .ms-pd-qty { display:flex; align-items:center; gap:12px; margin-bottom:24px; }
  .ms-pd-qty button { width:44px; height:44px; border-radius:50%; border:1px solid var(--ms-soft); background:#fff; font-family:'Lora',serif; font-size:18px; cursor:pointer; color:var(--ms-ink); transition:border-color .2s; }
  .ms-pd-qty button:hover { border-color:var(--ms-warm); color:var(--ms-warm); }
  .ms-pd-qty span { font-family:'Lora',serif; font-size:18px; font-weight:600; min-width:30px; text-align:center; color:var(--ms-ink); }
  .ms-pd-actions { display:flex; gap:12px; flex-wrap:wrap; }
  .ms-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:15px 32px; border-radius:8px; font-family:'Lora',serif; font-size:14px; font-weight:600; transition:transform .15s, background .2s; cursor:pointer; border:none; text-decoration:none; }
  .ms-btn:active { transform:scale(0.98); }
  .ms-btn-primary { background:var(--ms-warm); color:#fff; }
  .ms-btn-primary:hover { background:#A67E52; }
  .ms-btn-dark { background:var(--ms-ink); color:#fff; }
  .ms-btn-dark:hover { background:#374151; }
  .ms-btn-outline { background:#fff; color:var(--ms-ink); border:1.5px solid var(--ms-ink); }
  .ms-btn-outline:hover { background:var(--ms-ink); color:#fff; }
  .ms-btn-wa { background:#25D366; color:#fff; }
  .ms-btn-wa:hover { background:#1DA851; }
  .ms-pd-actions .ms-btn { flex:1; min-width:180px; }

  .ms-pd-variants { margin-top:40px; }
  .ms-pd-related { margin-top:48px; }
  .ms-section-label { font-family:'Lora',serif; font-size:12px; letter-spacing:0.06em; color:var(--ms-warm); font-weight:600; margin-bottom:6px; }
  .ms-section-title { font-family:'Lora',serif; font-size:28px; margin:0 0 24px; font-weight:600; color:var(--ms-ink); }

  .ms-product-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:24px; }
  @media(max-width:1023px){ .ms-product-grid { grid-template-columns:repeat(3,1fr); } }
  @media(max-width:767px){ .ms-product-grid { grid-template-columns:repeat(2,1fr); gap:14px; } }
  .ms-product-card { position:relative; background:#fff; border-radius:12px; overflow:hidden; border:1px solid var(--ms-soft); transition:transform .2s, box-shadow .2s; cursor:pointer; text-decoration:none; color:inherit; }
  .ms-product-card:hover { transform:translateY(-4px); box-shadow:0 12px 32px rgba(31,41,55,0.1); }
  .ms-product-media { aspect-ratio:4/5; overflow:hidden; background:var(--ms-cream); }
  .ms-product-media img { width:100%; height:100%; object-fit:cover; transition:transform .45s ease; }
  .ms-product-card:hover .ms-product-media img { transform:scale(1.06); }
  .ms-product-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--ms-cream); color:var(--ms-soft); }
  .ms-product-placeholder span { font-family:'Lora',serif; font-size:36px; font-weight:600; }
  .ms-product-body { padding:18px; }
  .ms-product-name { font-family:'Lora',serif; font-size:14px; font-weight:500; margin-bottom:8px; line-height:1.4; color:var(--ms-ink); }
  .ms-product-price { font-family:'Lora',serif; font-size:17px; font-weight:700; color:var(--ms-ink); }
</style>

<div class="ms-container">
  <div class="ms-breadcrumb">
    <a href="<?= base_url('store/' . $slug); ?>">Home</a>
    <span class="sep">/</span>
    <a href="<?= base_url('store/' . $slug . '/products'); ?>">Collection</a>
    <span class="sep">/</span>
    <span><?= htmlspecialchars($product->item_name); ?></span>
  </div>

  <div class="ms-pd">
    <div class="ms-pd-layout">
      <div class="ms-pd-gallery">
        <?php if($img): ?>
        <img src="<?= $img; ?>" alt="<?= htmlspecialchars($product->item_name); ?>">
        <?php else: ?>
        <div class="ms-pd-gallery-placeholder"><?= htmlspecialchars(substr($product->item_name, 0, 1)); ?></div>
        <?php endif; ?>
      </div>
      <div class="ms-pd-meta">
        <div class="ms-pd-kicker"><?= htmlspecialchars($product->category_name ?? 'Collection'); ?></div>
        <h1 class="ms-pd-name"><?= htmlspecialchars($product->item_name); ?></h1>
        <div class="ms-pd-price">
          <?= sf_currency($product->effective_price, $cur); ?>
          <?php if($hasDiscount): ?>
          <span class="old"><?= sf_currency($product->original_price, $cur); ?></span>
          <?php endif; ?>
        </div>
        <div class="ms-pd-stock <?= (int)$product->stock <= 0 ? 'out' : ''; ?>">
          <?= (int)$product->stock > 0 ? 'In stock &middot; Crafted with care' : 'Out of stock'; ?>
        </div>
        <p class="ms-pd-desc"><?= nl2br(htmlspecialchars($product->description ?? '')); ?></p>

        <div class="ms-pd-qty">
          <button onclick="adjustDetailQty(-1)">-</button>
          <span id="detail-qty">1</span>
          <button onclick="adjustDetailQty(1)">+</button>
        </div>

        <div class="ms-pd-actions">
          <button class="ms-btn ms-btn-primary" onclick="addDetailToCart()">Add to Cart</button>
          <button class="ms-btn ms-btn-outline" onclick="addDetailToCart();window.location.href='<?= base_url('store/' . $slug . '/cart'); ?>'">Buy It Now</button>
          <?php if(!empty($settings->whatsapp_number)): ?>
          <button class="ms-btn ms-btn-wa" onclick="sendDetailWhatsApp()">Order via WhatsApp</button>
          <?php endif; ?>
        </div>

        <?php if(!empty($product_variants)): ?>
        <div class="ms-pd-variants">
          <div class="ms-section-label" style="margin-bottom:14px;">Available Variants</div>
          <div class="ms-product-grid" style="grid-template-columns:repeat(2,1fr);gap:14px;">
            <?php foreach($product_variants as $v):
              $vImg = ($v->item_image && file_exists($v->item_image)) ? base_url($v->item_image) : '';
            ?>
            <a href="<?= base_url('store/' . $slug . '/product/' . $v->id); ?>" class="ms-product-card">
              <div class="ms-product-media">
                <?php if($vImg): ?>
                <img src="<?= $vImg; ?>" alt="<?= htmlspecialchars($v->item_name); ?>" loading="lazy">
                <?php else: ?>
                <div class="ms-product-placeholder"><span><?= htmlspecialchars(substr($v->item_name, 0, 1)); ?></span></div>
                <?php endif; ?>
              </div>
              <div class="ms-product-body">
                <div class="ms-product-name"><?= htmlspecialchars($v->item_name); ?></div>
                <div class="ms-product-price"><?= sf_currency($v->effective_price, $cur); ?></div>
              </div>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <?php if(!empty($related_products)): ?>
        <div class="ms-pd-related">
          <div class="ms-section-label" style="margin-bottom:14px;">You May Also Like</div>
          <div class="ms-product-grid" style="grid-template-columns:repeat(2,1fr);gap:14px;">
            <?php foreach($related_products as $p):
              $pImg = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
            ?>
            <a href="<?= base_url('store/' . $slug . '/product/' . $p->id); ?>" class="ms-product-card">
              <div class="ms-product-media">
                <?php if($pImg): ?>
                <img src="<?= $pImg; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
                <?php else: ?>
                <div class="ms-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
                <?php endif; ?>
              </div>
              <div class="ms-product-body">
                <div class="ms-product-name"><?= htmlspecialchars($p->item_name); ?></div>
                <div class="ms-product-price"><?= sf_currency($p->sales_price, $cur); ?></div>
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
