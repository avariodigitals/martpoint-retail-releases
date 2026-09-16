<?php
/**
 * Pharma Care — Product Detail
 * Warm community pharmacy product page with Lora headings,
 * deep teal, amber accents, and pharmacist-first CTAs.
 */
$slug = $settings->store_slug ?? '';
$cur = $store_currency ?? null;
$img = ($product->item_image && file_exists($product->item_image)) ? base_url($product->item_image) : '';
$hasDiscount = $product->original_price > $product->effective_price;
?>
<style>
  .theme-pharma_care .mp-topbar,
  .theme-pharma_care .mp-announcement,
  .theme-pharma_care .mp-nav,
  .theme-pharma_care .mp-header,
  .theme-pharma_care .mp-mobile-menu-btn,
  .theme-pharma_care .mp-footer-space { display:none !important; }

  :root {
    --pcr-teal:#0F766E;
    --pcr-teal-dark:#115E59;
    --pcr-amber:#D97706;
    --pcr-amber-dark:#B45309;
    --pcr-cream:#FFFBF5;
    --pcr-cream-dark:#FEF7ED;
    --pcr-dark:#1C1917;
    --pcr-gray:#57534E;
    --pcr-border:#E7E5E4;
    --pcr-white:#FFFFFF;
  }

  .pcr-container { max-width:1400px; margin:0 auto; padding:0 24px; }
  @media(max-width:767px){ .pcr-container { padding:0 16px; } }

  .pcr-breadcrumb { padding:24px 0 0; font-family:'Inter',sans-serif; font-size:13px; color:var(--pcr-gray); }
  .pcr-breadcrumb a { color:var(--pcr-teal); text-decoration:none; }
  .pcr-breadcrumb a:hover { color:var(--pcr-teal-dark); }
  .pcr-breadcrumb .sep { margin:0 8px; color:var(--pcr-amber); }

  .pcr-pd { padding:36px 0 72px; }
  .pcr-pd-layout { display:grid; grid-template-columns:1.1fr 1fr; gap:48px; align-items:start; }
  @media(max-width:1023px){ .pcr-pd-layout { grid-template-columns:1fr; gap:32px; } }
  .pcr-pd-gallery { position:sticky; top:90px; border-radius:16px; overflow:hidden; background:var(--pcr-cream); aspect-ratio:1/1; border:1px solid var(--pcr-border); }
  @media(max-width:1023px){ .pcr-pd-gallery { position:static; } }
  .pcr-pd-gallery img { width:100%; height:100%; object-fit:cover; }
  .pcr-pd-gallery-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:var(--pcr-teal); font-family:'Lora',serif; font-size:52px; font-weight:700; }

  .pcr-pd-meta { padding-top:8px; }
  .pcr-pd-kicker { font-family:'Inter',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.16em; color:var(--pcr-amber); font-weight:600; margin-bottom:12px; }
  .pcr-pd-name { font-family:'Lora',serif; font-size:clamp(28px,3vw,38px); font-weight:700; margin-bottom:14px; color:var(--pcr-dark); line-height:1.1; letter-spacing:-0.01em; }
  .pcr-pd-price { font-family:'Inter',sans-serif; font-size:26px; font-weight:700; margin-bottom:8px; color:var(--pcr-dark); }
  .pcr-pd-price .old { font-family:'Inter',sans-serif; font-size:16px; color:var(--pcr-gray); text-decoration:line-through; margin-left:12px; font-weight:400; }
  .pcr-pd-stock { font-family:'Inter',sans-serif; font-size:14px; color:var(--pcr-teal); margin-bottom:24px; font-weight:600; }
  .pcr-pd-stock.out { color:#DC2626; }
  .pcr-pd-desc { font-family:'Inter',sans-serif; color:var(--pcr-gray); line-height:1.75; margin-bottom:28px; font-size:15px; }
  .pcr-pd-qty { display:flex; align-items:center; gap:14px; margin-bottom:26px; }
  .pcr-pd-qty button { width:44px; height:44px; border-radius:8px; border:1px solid var(--pcr-border); background:#fff; font-family:'Inter',sans-serif; font-size:18px; cursor:pointer; color:var(--pcr-dark); transition:border-color .2s, color .2s; }
  .pcr-pd-qty button:hover { border-color:var(--pcr-teal); color:var(--pcr-teal); }
  .pcr-pd-qty span { font-family:'Inter',sans-serif; font-size:20px; font-weight:700; min-width:32px; text-align:center; color:var(--pcr-dark); }
  .pcr-pd-actions { display:flex; gap:12px; flex-wrap:wrap; }
  .pcr-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:15px 34px; border-radius:8px; font-family:'Inter',sans-serif; font-size:13px; font-weight:600; transition:transform .15s, background .2s, color .2s; cursor:pointer; border:none; text-decoration:none; }
  .pcr-btn:active { transform:scale(0.98); }
  .pcr-btn-amber { background:var(--pcr-amber); color:#fff; }
  .pcr-btn-amber:hover { background:var(--pcr-amber-dark); }
  .pcr-btn-outline { background:#fff; color:var(--pcr-teal); border:1.5px solid var(--pcr-teal); }
  .pcr-btn-outline:hover { background:var(--pcr-teal); color:#fff; }
  .pcr-btn-wa { background:#25D366; color:#fff; }
  .pcr-btn-wa:hover { background:#1DA851; }
  .pcr-btn-teal { background:var(--pcr-teal); color:#fff; }
  .pcr-btn-teal:hover { background:var(--pcr-teal-dark); }
  .pcr-pd-actions .pcr-btn { flex:1; min-width:160px; }

  .pcr-pd-variants { margin-top:40px; }
  .pcr-pd-related { margin-top:48px; }
  .pcr-section-label { font-family:'Inter',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.16em; color:var(--pcr-amber); font-weight:600; margin-bottom:8px; }
  .pcr-section-title { font-family:'Lora',serif; font-size:24px; margin:0 0 24px; font-weight:700; color:var(--pcr-dark); letter-spacing:-0.01em; }

  .pcr-product-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:20px; }
  @media(max-width:1023px){ .pcr-product-grid { grid-template-columns:repeat(3,1fr); } }
  @media(max-width:767px){ .pcr-product-grid { grid-template-columns:repeat(2,1fr); gap:12px; } }
  .pcr-product-card { position:relative; background:#fff; border-radius:16px; overflow:hidden; border:1px solid var(--pcr-border); transition:transform .25s, box-shadow .25s; cursor:pointer; text-decoration:none; color:inherit; }
  .pcr-product-card:hover { transform:translateY(-6px); box-shadow:0 16px 40px rgba(15,118,110,0.12); }
  .pcr-product-media { aspect-ratio:1/1; overflow:hidden; background:var(--pcr-cream); }
  .pcr-product-media img { width:100%; height:100%; object-fit:cover; transition:transform .4s ease; }
  .pcr-product-card:hover .pcr-product-media img { transform:scale(1.05); }
  .pcr-product-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--pcr-cream); color:var(--pcr-teal); }
  .pcr-product-placeholder span { font-family:'Lora',serif; font-size:40px; font-weight:700; }
  .pcr-product-body { padding:16px; }
  .pcr-product-name { font-family:'Lora',serif; font-size:15px; font-weight:600; margin-bottom:8px; line-height:1.4; color:var(--pcr-dark); }
  .pcr-product-price { font-family:'Inter',sans-serif; font-size:16px; font-weight:700; color:var(--pcr-dark); }
</style>

<div class="pcr-container">
  <div class="pcr-breadcrumb">
    <a href="<?= base_url('store/' . $slug); ?>">Home</a>
    <span class="sep">/</span>
    <a href="<?= base_url('store/' . $slug . '/products'); ?>">Shop</a>
    <span class="sep">/</span>
    <span><?= htmlspecialchars($product->item_name); ?></span>
  </div>

  <div class="pcr-pd">
    <div class="pcr-pd-layout">
      <div class="pcr-pd-gallery">
        <?php if($img): ?>
        <img src="<?= $img; ?>" alt="<?= htmlspecialchars($product->item_name); ?>">
        <?php else: ?>
        <div class="pcr-pd-gallery-placeholder"><?= htmlspecialchars(substr($product->item_name, 0, 1)); ?></div>
        <?php endif; ?>
      </div>
      <div class="pcr-pd-meta">
        <div class="pcr-pd-kicker"><?= htmlspecialchars($product->category_name ?? 'Family Health'); ?></div>
        <h1 class="pcr-pd-name"><?= htmlspecialchars($product->item_name); ?></h1>
        <div class="pcr-pd-price">
          <?= sf_currency($product->effective_price, $cur); ?>
          <?php if($hasDiscount): ?>
          <span class="old"><?= sf_currency($product->original_price, $cur); ?></span>
          <?php endif; ?>
        </div>
        <div class="pcr-pd-stock <?= (int)$product->stock <= 0 ? 'out' : ''; ?>">
          <?= (int)$product->stock > 0 ? 'In Stock &middot; Available for pickup or delivery' : 'Out of Stock'; ?>
        </div>
        <p class="pcr-pd-desc"><?= nl2br(htmlspecialchars($product->description ?? '')); ?></p>

        <div class="pcr-pd-qty">
          <button onclick="adjustDetailQty(-1)">-</button>
          <span id="detail-qty">1</span>
          <button onclick="adjustDetailQty(1)">+</button>
        </div>

        <div class="pcr-pd-actions">
          <button class="pcr-btn pcr-btn-amber" onclick="addDetailToCart()">Add to Cart</button>
          <button class="pcr-btn pcr-btn-outline" onclick="addDetailToCart();window.location.href='<?= base_url('store/' . $slug . '/cart'); ?>'">Buy It Now</button>
          <?php if(!empty($settings->whatsapp_number)): ?>
          <button class="pcr-btn pcr-btn-wa" onclick="sendDetailWhatsApp()">Order via WhatsApp</button>
          <?php endif; ?>
        </div>

        <?php if(!empty($product_variants)): ?>
        <div class="pcr-pd-variants">
          <div class="pcr-section-label" style="margin-bottom:16px;">Available Variants</div>
          <div class="pcr-product-grid" style="grid-template-columns:repeat(2,1fr);gap:12px;">
            <?php foreach($product_variants as $v):
              $vImg = ($v->item_image && file_exists($v->item_image)) ? base_url($v->item_image) : '';
            ?>
            <a href="<?= base_url('store/' . $slug . '/product/' . $v->id); ?>" class="pcr-product-card">
              <div class="pcr-product-media">
                <?php if($vImg): ?>
                <img src="<?= $vImg; ?>" alt="<?= htmlspecialchars($v->item_name); ?>" loading="lazy">
                <?php else: ?>
                <div class="pcr-product-placeholder"><span><?= htmlspecialchars(substr($v->item_name, 0, 1)); ?></span></div>
                <?php endif; ?>
              </div>
              <div class="pcr-product-body">
                <div class="pcr-product-name"><?= htmlspecialchars($v->item_name); ?></div>
                <div class="pcr-product-price"><?= sf_currency($v->effective_price, $cur); ?></div>
              </div>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endif; ?>

        <?php if(!empty($related_products)): ?>
        <div class="pcr-pd-related">
          <div class="pcr-section-label" style="margin-bottom:16px;">You May Also Like</div>
          <div class="pcr-product-grid" style="grid-template-columns:repeat(2,1fr);gap:12px;">
            <?php foreach($related_products as $p):
              $pImg = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
            ?>
            <a href="<?= base_url('store/' . $slug . '/product/' . $p->id); ?>" class="pcr-product-card">
              <div class="pcr-product-media">
                <?php if($pImg): ?>
                <img src="<?= $pImg; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
                <?php else: ?>
                <div class="pcr-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
                <?php endif; ?>
              </div>
              <div class="pcr-product-body">
                <div class="pcr-product-name"><?= htmlspecialchars($p->item_name); ?></div>
                <div class="pcr-product-price"><?= sf_currency($p->sales_price, $cur); ?></div>
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
