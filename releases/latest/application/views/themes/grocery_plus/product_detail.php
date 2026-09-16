<?php
/**
 * Grocery Plus — Product Detail
 * Premium supermarket product detail with emerald and gold accents.
 * Uses mp_minified_image_url() for all imagery.
 */
$slug = $settings->store_slug ?? '';
$cur = $store_currency ?? null;
$waNumber = preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? '');

$p = $product ?? null;
if(!$p) return;

$price = $p->effective_price ?? $p->sales_price;
$oldPrice = $p->original_price ?? $p->sales_price;
$hasDiscount = $oldPrice > $price;
$discountPct = $hasDiscount ? round((($oldPrice - $price) / $oldPrice) * 100) : 0;
$inStock = $p->stock > 0;
$img = ($p->item_image && file_exists($p->item_image)) ? mp_minified_image_url($p->item_image, 1000) : '';
?>
<style>
  .theme-grocery_plus .mp-topbar,
  .theme-grocery_plus .mp-announcement,
  .theme-grocery_plus .mp-nav,
  .theme-grocery_plus .mp-header,
  .theme-grocery_plus .mp-mobile-menu-btn,
  .theme-grocery_plus .mp-footer-space { display:none !important; }

  :root { --gp-emerald:#047857; --gp-emerald-dark:#065F46; --gp-emerald-deep:#064E3B; --gp-gold:#B45309; --gp-gold-light:#F59E0B; --gp-cream:#F5F5F0; --gp-soft:#ECFDF5; --gp-dark:#1F2937; --gp-gray:#6B7280; --gp-border:#E5E7EB; --gp-white:#FFFFFF; }
  .gp-container { max-width:1400px; margin:0 auto; padding:0 24px; }
  @media(max-width:767px){ .gp-container { padding:0 16px; } }

  .gp-breadcrumb { padding:24px 0 0; font-family:'Inter',sans-serif; font-size:13px; color:var(--gp-gray); }
  .gp-breadcrumb a { color:var(--gp-emerald); text-decoration:none; }
  .gp-breadcrumb a:hover { color:var(--gp-emerald-dark); }
  .gp-breadcrumb .sep { margin:0 8px; color:var(--gp-gold); }

  .gp-detail { padding:36px 0 72px; }
  .gp-detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:48px; }
  @media(max-width:1023px){ .gp-detail-grid { grid-template-columns:1fr; gap:32px; } }
  .gp-detail-media { position:relative; }
  .gp-detail-img { width:100%; aspect-ratio:1; object-fit:cover; border-radius:14px; background:var(--gp-cream); border:1px solid var(--gp-border); }
  .gp-detail-placeholder { width:100%; aspect-ratio:1; border-radius:14px; background:var(--gp-soft); display:flex; align-items:center; justify-content:center; color:var(--gp-emerald); }
  .gp-detail-placeholder span { font-family:'Lora',serif; font-size:80px; font-weight:700; }
  .gp-detail-badge { position:absolute; top:16px; left:16px; background:var(--gp-gold); color:#fff; font-family:'Inter',sans-serif; font-size:13px; font-weight:700; padding:6px 14px; border-radius:999px; text-transform:uppercase; }

  .gp-detail-info { display:flex; flex-direction:column; gap:18px; }
  .gp-detail-brand { font-family:'Inter',sans-serif; font-size:12px; text-transform:uppercase; letter-spacing:0.12em; color:var(--gp-emerald); font-weight:600; }
  .gp-detail-title { font-family:'Lora',serif; font-size:clamp(26px,3vw,36px); font-weight:700; color:var(--gp-dark); margin:0; line-height:1.15; }
  .gp-detail-rating { display:flex; align-items:center; gap:8px; font-family:'Inter',sans-serif; font-size:14px; color:var(--gp-gray); }
  .gp-detail-stars { color:var(--gp-gold-light); letter-spacing:2px; }
  .gp-detail-price { display:flex; align-items:baseline; gap:12px; }
  .gp-detail-price-now { font-family:'Inter',sans-serif; font-size:32px; font-weight:800; color:var(--gp-emerald); }
  .gp-detail-price-old { font-family:'Inter',sans-serif; font-size:18px; color:var(--gp-gray); text-decoration:line-through; }
  .gp-detail-price-save { font-family:'Inter',sans-serif; font-size:13px; font-weight:700; color:#fff; background:var(--gp-gold); padding:4px 10px; border-radius:999px; }
  .gp-detail-desc { font-family:'Inter',sans-serif; font-size:15px; line-height:1.7; color:var(--gp-gray); }
  .gp-detail-stock { font-family:'Inter',sans-serif; font-size:13px; font-weight:600; display:inline-flex; align-items:center; gap:6px; }
  .gp-detail-stock.in { color:var(--gp-emerald); }
  .gp-detail-stock.out { color:#DC2626; }
  .gp-detail-stock-dot { width:8px; height:8px; border-radius:50%; background:currentColor; }

  .gp-detail-qty { display:flex; align-items:center; gap:14px; }
  .gp-qty-control { display:flex; align-items:center; border:1px solid var(--gp-border); border-radius:999px; overflow:hidden; background:#fff; }
  .gp-qty-btn { width:44px; height:44px; border:none; background:var(--gp-cream); color:var(--gp-dark); font-size:18px; cursor:pointer; transition:background .2s; }
  .gp-qty-btn:hover { background:var(--gp-soft); }
  .gp-qty-input { width:60px; text-align:center; border:none; font-family:'Inter',sans-serif; font-size:16px; font-weight:600; color:var(--gp-dark); background:transparent; outline:none; }

  .gp-detail-actions { display:flex; gap:10px; flex-wrap:wrap; }
  .gp-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:14px 32px; border-radius:999px; font-family:'Inter',sans-serif; font-size:14px; font-weight:600; transition:transform .15s, background .2s; cursor:pointer; border:none; text-decoration:none; }
  .gp-btn:active { transform:scale(0.98); }
  .gp-btn-emerald { background:var(--gp-emerald); color:#fff; }
  .gp-btn-emerald:hover { background:var(--gp-emerald-dark); }
  .gp-btn-wa { background:#25D366; color:#fff; }
  .gp-btn-wa:hover { background:#1FB855; }
  .gp-btn-outline { background:#fff; color:var(--gp-emerald); border:1.5px solid var(--gp-emerald); }
  .gp-btn-outline:hover { background:var(--gp-emerald); color:#fff; }

  .gp-detail-meta { background:var(--gp-cream); border-radius:14px; padding:20px; display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }
  @media(max-width:767px){ .gp-detail-meta { grid-template-columns:1fr; } }
  .gp-detail-meta-item { display:flex; align-items:center; gap:10px; font-family:'Inter',sans-serif; font-size:13px; color:var(--gp-dark); }
  .gp-detail-meta-icon { width:36px; height:36px; border-radius:999px; background:rgba(4,120,87,0.08); display:flex; align-items:center; justify-content:center; color:var(--gp-emerald); flex-shrink:0; }
  .gp-detail-meta-icon svg { width:18px; height:18px; }

  .gp-related { padding:48px 0; background:var(--gp-cream); }
  .gp-related-title { font-family:'Lora',serif; font-size:24px; font-weight:700; color:var(--gp-dark); margin-bottom:24px; }
  .gp-product-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:24px; }
  @media(max-width:1023px){ .gp-product-grid { grid-template-columns:repeat(3,1fr); } }
  @media(max-width:767px){ .gp-product-grid { grid-template-columns:repeat(2,1fr); gap:14px; } }
  .gp-product-card { position:relative; background:#fff; border-radius:12px; overflow:hidden; border:1px solid var(--gp-border); transition:transform .25s, box-shadow .25s; cursor:pointer; }
  .gp-product-card:hover { transform:translateY(-5px); box-shadow:0 16px 40px rgba(4,120,87,0.12); }
  .gp-product-media { aspect-ratio:1/1; overflow:hidden; background:var(--gp-cream); }
  .gp-product-media img { width:100%; height:100%; object-fit:cover; transition:transform .4s ease; }
  .gp-product-card:hover .gp-product-media img { transform:scale(1.05); }
  .gp-product-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--gp-soft); color:var(--gp-emerald); }
  .gp-product-placeholder span { font-family:'Lora',serif; font-size:38px; font-weight:700; }
  .gp-product-body { padding:18px; }
  .gp-product-brand { font-family:'Inter',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.08em; color:var(--gp-emerald); font-weight:600; margin-bottom:4px; }
  .gp-product-name { font-family:'Lora',serif; font-size:16px; font-weight:600; margin-bottom:10px; line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; min-height:44px; color:var(--gp-dark); }
  .gp-product-price { font-family:'Inter',sans-serif; font-size:19px; font-weight:800; color:var(--gp-dark); }
  .gp-add-btn { width:100%; margin-top:10px; padding:12px 16px; border-radius:999px; background:var(--gp-emerald); color:#fff; display:flex; align-items:center; justify-content:center; gap:6px; transition:background .2s, transform .15s; border:none; cursor:pointer; font-family:'Inter',sans-serif; font-size:13px; font-weight:600; }
  .gp-add-btn:hover { background:var(--gp-emerald-dark); }
</style>

<div class="gp-container">
  <div class="gp-breadcrumb">
    <a href="<?= base_url('store/' . $slug); ?>">Home</a>
    <span class="sep">/</span>
    <a href="<?= base_url('store/' . $slug . '/products'); ?>">Products</a>
    <?php if(!empty($p->category_name)): ?>
    <span class="sep">/</span>
    <a href="<?= base_url('store/' . $slug . '/products?category=' . ($p->category_id ?? '')); ?>"><?= htmlspecialchars($p->category_name); ?></a>
    <?php endif; ?>
    <span class="sep">/</span>
    <span><?= htmlspecialchars($p->item_name); ?></span>
  </div>

  <div class="gp-detail">
    <div class="gp-detail-grid">
      <div class="gp-detail-media">
        <?php if($hasDiscount && $discountPct > 0): ?>
        <div class="gp-detail-badge">-<?= $discountPct; ?>% Off</div>
        <?php endif; ?>
        <?php if($img): ?>
        <img class="gp-detail-img" src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" decoding="async">
        <?php else: ?>
        <div class="gp-detail-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
        <?php endif; ?>
      </div>

      <div class="gp-detail-info">
        <?php if(!empty($p->category_name)): ?>
        <div class="gp-detail-brand"><?= htmlspecialchars($p->category_name); ?></div>
        <?php endif; ?>
        <h1 class="gp-detail-title"><?= htmlspecialchars($p->item_name); ?></h1>
        <div class="gp-detail-rating">
          <span class="gp-detail-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
          <span>4.8 (124 reviews)</span>
        </div>
        <div class="gp-detail-price">
          <span class="gp-detail-price-now"><?= sf_currency($price, $cur); ?></span>
          <?php if($hasDiscount): ?>
          <span class="gp-detail-price-old"><?= sf_currency($oldPrice, $cur); ?></span>
          <span class="gp-detail-price-save">Save <?= sf_currency($oldPrice - $price, $cur); ?></span>
          <?php endif; ?>
        </div>
        <div class="gp-detail-stock <?= $inStock ? 'in' : 'out'; ?>">
          <span class="gp-detail-stock-dot"></span>
          <?= $inStock ? 'In Stock' : 'Out of Stock'; ?>
        </div>
        <?php if(!empty($p->description)): ?>
        <div class="gp-detail-desc"><?= nl2br(htmlspecialchars($p->description)); ?></div>
        <?php endif; ?>

        <?php if($inStock || ($settings->allow_backorder ?? false)): ?>
        <div class="gp-detail-qty">
          <span style="font-family:'Inter',sans-serif;font-size:14px;font-weight:600;color:var(--gp-dark);">Quantity</span>
          <div class="gp-qty-control">
            <button class="gp-qty-btn" onclick="adjustDetailQty(-1)">&minus;</button>
            <input type="number" id="detail-qty" class="gp-qty-input" value="1" min="1" max="<?= max(1, $p->stock); ?>">
            <button class="gp-qty-btn" onclick="adjustDetailQty(1)">+</button>
          </div>
        </div>
        <div class="gp-detail-actions">
          <button class="gp-btn gp-btn-emerald" onclick="addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',parseInt(document.getElementById('detail-qty').value)||1,<?= $p->stock; ?>)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg> Add to Cart
          </button>
          <?php if($waNumber): ?>
          <button class="gp-btn gp-btn-wa" onclick="openWhatsAppOrderModal(<?= $p->id; ?>,'<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',<?= $p->stock; ?>)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> Order on WhatsApp
          </button>
          <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="gp-detail-actions">
          <button class="gp-btn gp-btn-outline" disabled style="opacity:0.6;cursor:not-allowed;">Out of Stock</button>
        </div>
        <?php endif; ?>

        <div class="gp-detail-meta">
          <div class="gp-detail-meta-item">
            <div class="gp-detail-meta-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg></div>
            <span>Same-Day Delivery</span>
          </div>
          <div class="gp-detail-meta-item">
            <div class="gp-detail-meta-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></div>
            <span>Quality Pledge</span>
          </div>
          <div class="gp-detail-meta-item">
            <div class="gp-detail-meta-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a8 8 0 0 0-8 8c0 4 3 7 8 9 5-2 8-5 8-9a8 8 0 0 0-8-8z"/><path d="M12 6v6"/></svg></div>
            <span>Fresh Sourcing</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php if(!empty($related_products)): ?>
<div class="gp-related">
  <div class="gp-container">
    <h2 class="gp-related-title">You May Also Like</h2>
    <div class="gp-product-grid">
      <?php foreach(array_slice($related_products, 0, 4) as $rp):
        $rPrice = $rp->effective_price ?? $rp->sales_price;
        $rImg = ($rp->item_image && file_exists($rp->item_image)) ? mp_minified_image_url($rp->item_image, 600) : '';
      ?>
      <a href="<?= base_url('store/' . $slug . '/product/' . $rp->id); ?>" class="gp-product-card">
        <div class="gp-product-media">
          <?php if($rImg): ?>
          <img src="<?= $rImg; ?>" alt="<?= htmlspecialchars($rp->item_name); ?>" loading="lazy" decoding="async">
          <?php else: ?>
          <div class="gp-product-placeholder"><span><?= htmlspecialchars(substr($rp->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="gp-product-body">
          <?php if(!empty($rp->category_name)): ?>
          <div class="gp-product-brand"><?= htmlspecialchars($rp->category_name); ?></div>
          <?php endif; ?>
          <div class="gp-product-name"><?= htmlspecialchars($rp->item_name); ?></div>
          <div class="gp-product-price"><?= sf_currency($rPrice, $cur); ?></div>
          <button class="gp-add-btn" onclick="event.preventDefault();event.stopPropagation();addToCart(<?= $rp->id; ?>,'product','<?= htmlspecialchars(addslashes($rp->item_name)); ?>',<?= $rPrice; ?>,'<?= $rp->item_image; ?>',1,<?= $rp->stock; ?>)" aria-label="Add to cart"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add to Cart</button>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<div id="wa-order-modal" style="display:none;position:fixed;inset:0;z-index:9999;">
  <div style="position:absolute;inset:0;background:rgba(6,78,59,0.4);" onclick="closeWhatsAppOrderModal()"></div>
  <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:440px;max-width:calc(100vw - 32px);max-height:90vh;overflow-y:auto;background:#fff;border-radius:14px;box-shadow:0 24px 60px rgba(4,120,87,0.25);border:1px solid var(--gp-border);">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-bottom:1px solid var(--gp-border);">
      <h3 style="font-family:'Lora',serif;font-size:20px;font-weight:700;color:var(--gp-dark);margin:0;">Order via WhatsApp</h3>
      <button onclick="closeWhatsAppOrderModal()" style="width:32px;height:32px;border-radius:999px;border:none;background:var(--gp-cream);color:var(--gp-dark);cursor:pointer;display:flex;align-items:center;justify-content:center;" aria-label="Close"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div style="padding:24px;">
      <div style="display:flex;gap:14px;align-items:center;padding:14px;background:var(--gp-cream);border-radius:12px;margin-bottom:20px;">
        <img id="wa-modal-product-img" src="" alt="" style="width:56px;height:56px;border-radius:8px;object-fit:cover;flex-shrink:0;">
        <div style="flex:1;min-width:0;">
          <p id="wa-modal-product-name" style="font-family:'Lora',serif;font-size:15px;font-weight:700;color:var(--gp-dark);margin:0 0 4px;"></p>
          <div id="wa-modal-product-price" style="font-family:'Inter',sans-serif;font-size:16px;font-weight:800;color:var(--gp-emerald);"></div>
        </div>
      </div>
      <div style="display:flex;flex-direction:column;gap:14px;">
        <div><label style="font-family:'Inter',sans-serif;font-size:11px;text-transform:uppercase;letter-spacing:0.08em;font-weight:600;color:var(--gp-dark);margin-bottom:5px;display:block;">Your Name</label><input type="text" id="wa-modal-name" placeholder="Enter your name" style="width:100%;padding:12px 14px;border:1px solid var(--gp-border);border-radius:10px;font-family:'Inter',sans-serif;font-size:14px;background:var(--gp-cream);outline:none;color:var(--gp-dark);box-sizing:border-box;"></div>
        <div><label style="font-family:'Inter',sans-serif;font-size:11px;text-transform:uppercase;letter-spacing:0.08em;font-weight:600;color:var(--gp-dark);margin-bottom:5px;display:block;">Phone Number</label><input type="tel" id="wa-modal-phone" placeholder="Enter your phone number" style="width:100%;padding:12px 14px;border:1px solid var(--gp-border);border-radius:10px;font-family:'Inter',sans-serif;font-size:14px;background:var(--gp-cream);outline:none;color:var(--gp-dark);box-sizing:border-box;"></div>
        <div><label style="font-family:'Inter',sans-serif;font-size:11px;text-transform:uppercase;letter-spacing:0.08em;font-weight:600;color:var(--gp-dark);margin-bottom:5px;display:block;">Quantity</label><input type="number" id="wa-modal-qty" value="1" min="1" style="width:100%;padding:12px 14px;border:1px solid var(--gp-border);border-radius:10px;font-family:'Inter',sans-serif;font-size:14px;background:var(--gp-cream);outline:none;color:var(--gp-dark);box-sizing:border-box;"></div>
        <div><label style="font-family:'Inter',sans-serif;font-size:11px;text-transform:uppercase;letter-spacing:0.08em;font-weight:600;color:var(--gp-dark);margin-bottom:5px;display:block;">Note (optional)</label><textarea id="wa-modal-note" placeholder="Any special requests..." style="width:100%;padding:12px 14px;border:1px solid var(--gp-border);border-radius:10px;font-family:'Inter',sans-serif;font-size:14px;background:var(--gp-cream);outline:none;color:var(--gp-dark);box-sizing:border-box;resize:vertical;min-height:64px;"></textarea></div>
      </div>
      <div style="display:flex;gap:10px;margin-top:20px;">
        <button onclick="closeWhatsAppOrderModal()" style="padding:14px 20px;border-radius:999px;border:1px solid var(--gp-border);background:#fff;color:var(--gp-dark);font-family:'Inter',sans-serif;font-size:14px;font-weight:600;cursor:pointer;">Cancel</button>
        <button onclick="sendWhatsAppOrder()" style="flex:1;padding:14px;border-radius:999px;border:none;background:#25D366;color:#fff;font-family:'Inter',sans-serif;font-size:14px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> Send via WhatsApp</button>
      </div>
    </div>
  </div>
</div>

<script>
function adjustDetailQty(delta){
  const input = document.getElementById('detail-qty');
  let val = parseInt(input.value) || 1;
  val = Math.max(1, val + delta);
  input.value = val;
}
let waOrderProduct = null;
function openWhatsAppOrderModal(id, name, price, image, stock){
  waOrderProduct = {id, name, price, image, stock};
  document.getElementById('wa-modal-product-name').textContent = name;
  document.getElementById('wa-modal-product-price').textContent = formatMoney(price);
  if(image) document.getElementById('wa-modal-product-img').src = '<?= base_url(); ?>' + image;
  document.getElementById('wa-modal-qty').value = parseInt(document.getElementById('detail-qty')?.value || 1);
  document.getElementById('wa-modal-name').value = '';
  document.getElementById('wa-modal-phone').value = '';
  document.getElementById('wa-modal-note').value = '';
  document.getElementById('wa-order-modal').style.display = 'block';
  document.body.style.overflow = 'hidden';
}
function closeWhatsAppOrderModal(){ document.getElementById('wa-order-modal').style.display = 'none'; document.body.style.overflow = ''; }
function sendWhatsAppOrder(){
  if(!waOrderProduct) return;
  const name = document.getElementById('wa-modal-name').value.trim();
  const phone = document.getElementById('wa-modal-phone').value.trim();
  const qty = parseInt(document.getElementById('wa-modal-qty').value) || 1;
  const note = document.getElementById('wa-modal-note').value.trim();
  if(!name || !phone){ showToast('Please enter your name and phone number'); return; }
  const storeName = '<?= htmlspecialchars(addslashes($store->store_name ?? 'Grocery Plus')); ?>';
  const waNumber = '<?= $waNumber; ?>';
  if(!waNumber){ showToast('WhatsApp ordering is not available'); return; }
  let msg = 'Hello, I would like to order from ' + storeName;
  msg += '\n\nProduct: ' + waOrderProduct.name;
  msg += '\nQuantity: ' + qty;
  msg += '\nPrice: ' + formatMoney(waOrderProduct.price * qty);
  msg += '\n\nMy Details:\nName: ' + name + '\nPhone: ' + phone;
  if(note) msg += '\nNote: ' + note;
  msg += '\n\nThank you.';
  window.open('https://wa.me/' + waNumber + '?text=' + encodeURIComponent(msg), '_blank');
  closeWhatsAppOrderModal();
  showToast('Opening WhatsApp with your order details...');
}
</script>
