<?php
/**
 * Daily Cart — Product Detail
 * Friendly mini mart product detail with blue and amber accents.
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
  .theme-daily_cart .mp-topbar,
  .theme-daily_cart .mp-announcement,
  .theme-daily_cart .mp-nav,
  .theme-daily_cart .mp-header,
  .theme-daily_cart .mp-mobile-menu-btn,
  .theme-daily_cart .mp-footer-space { display:none !important; }

  :root { --dc-blue:#2563EB; --dc-blue-dark:#1D4ED8; --dc-blue-deep:#1E3A8A; --dc-amber:#F59E0B; --dc-amber-dark:#D97706; --dc-cream:#EFF6FF; --dc-soft:#DBEAFE; --dc-dark:#1E293B; --dc-gray:#64748B; --dc-border:#E2E8F0; --dc-white:#FFFFFF; }
  .dc-container { max-width:1400px; margin:0 auto; padding:0 24px; }
  @media(max-width:767px){ .dc-container { padding:0 16px; } }

  .dc-breadcrumb { padding:24px 0 0; font-family:'Poppins',sans-serif; font-size:13px; color:var(--dc-gray); }
  .dc-breadcrumb a { color:var(--dc-blue); text-decoration:none; }
  .dc-breadcrumb a:hover { color:var(--dc-blue-dark); }
  .dc-breadcrumb .sep { margin:0 8px; color:var(--dc-amber); }

  .dc-detail { padding:36px 0 72px; }
  .dc-detail-grid { display:grid; grid-template-columns:1fr 1fr; gap:48px; }
  @media(max-width:1023px){ .dc-detail-grid { grid-template-columns:1fr; gap:32px; } }
  .dc-detail-media { position:relative; }
  .dc-detail-img { width:100%; aspect-ratio:1; object-fit:cover; border-radius:24px; background:var(--dc-cream); border:1px solid var(--dc-border); }
  .dc-detail-placeholder { width:100%; aspect-ratio:1; border-radius:24px; background:var(--dc-soft); display:flex; align-items:center; justify-content:center; color:var(--dc-blue); }
  .dc-detail-placeholder span { font-family:'Poppins',sans-serif; font-size:80px; font-weight:800; }
  .dc-detail-badge { position:absolute; top:16px; left:16px; background:var(--dc-amber); color:var(--dc-dark); font-family:'Poppins',sans-serif; font-size:13px; font-weight:700; padding:6px 14px; border-radius:999px; text-transform:uppercase; }

  .dc-detail-info { display:flex; flex-direction:column; gap:18px; }
  .dc-detail-brand { font-family:'Poppins',sans-serif; font-size:12px; text-transform:uppercase; letter-spacing:0.12em; color:var(--dc-blue); font-weight:600; }
  .dc-detail-title { font-family:'Poppins',sans-serif; font-size:clamp(26px,3vw,36px); font-weight:800; color:var(--dc-dark); margin:0; letter-spacing:-0.02em; line-height:1.15; }
  .dc-detail-rating { display:flex; align-items:center; gap:8px; font-family:'Poppins',sans-serif; font-size:14px; color:var(--dc-gray); }
  .dc-detail-stars { color:var(--dc-amber); letter-spacing:2px; }
  .dc-detail-price { display:flex; align-items:baseline; gap:12px; }
  .dc-detail-price-now { font-family:'Poppins',sans-serif; font-size:32px; font-weight:800; color:var(--dc-blue); }
  .dc-detail-price-old { font-family:'Poppins',sans-serif; font-size:18px; color:var(--dc-gray); text-decoration:line-through; }
  .dc-detail-price-save { font-family:'Poppins',sans-serif; font-size:13px; font-weight:700; color:#fff; background:var(--dc-amber); padding:4px 10px; border-radius:999px; }
  .dc-detail-desc { font-family:'Poppins',sans-serif; font-size:15px; line-height:1.7; color:var(--dc-gray); }
  .dc-detail-stock { font-family:'Poppins',sans-serif; font-size:13px; font-weight:600; display:inline-flex; align-items:center; gap:6px; }
  .dc-detail-stock.in { color:var(--dc-blue); }
  .dc-detail-stock.out { color:#DC2626; }
  .dc-detail-stock-dot { width:8px; height:8px; border-radius:50%; background:currentColor; }

  .dc-detail-qty { display:flex; align-items:center; gap:14px; }
  .dc-qty-control { display:flex; align-items:center; border:1px solid var(--dc-border); border-radius:999px; overflow:hidden; background:#fff; }
  .dc-qty-btn { width:44px; height:44px; border:none; background:var(--dc-cream); color:var(--dc-dark); font-size:18px; cursor:pointer; transition:background .2s; }
  .dc-qty-btn:hover { background:var(--dc-soft); }
  .dc-qty-input { width:60px; text-align:center; border:none; font-family:'Poppins',sans-serif; font-size:16px; font-weight:600; color:var(--dc-dark); background:transparent; outline:none; }

  .dc-detail-actions { display:flex; gap:10px; flex-wrap:wrap; }
  .dc-btn { display:inline-flex; align-items:center; justify-content:center; gap:8px; padding:14px 32px; border-radius:999px; font-family:'Poppins',sans-serif; font-size:14px; font-weight:600; transition:transform .15s, background .2s; cursor:pointer; border:none; text-decoration:none; }
  .dc-btn:active { transform:scale(0.98); }
  .dc-btn-blue { background:var(--dc-blue); color:#fff; }
  .dc-btn-blue:hover { background:var(--dc-blue-dark); }
  .dc-btn-wa { background:#25D366; color:#fff; }
  .dc-btn-wa:hover { background:#1FB855; }
  .dc-btn-outline { background:#fff; color:var(--dc-blue); border:1.5px solid var(--dc-blue); }
  .dc-btn-outline:hover { background:var(--dc-blue); color:#fff; }

  .dc-detail-meta { background:var(--dc-cream); border-radius:20px; padding:20px; display:grid; grid-template-columns:repeat(3,1fr); gap:16px; }
  @media(max-width:767px){ .dc-detail-meta { grid-template-columns:1fr; } }
  .dc-detail-meta-item { display:flex; align-items:center; gap:10px; font-family:'Poppins',sans-serif; font-size:13px; color:var(--dc-dark); }
  .dc-detail-meta-icon { width:36px; height:36px; border-radius:999px; background:rgba(37,99,235,0.08); display:flex; align-items:center; justify-content:center; color:var(--dc-blue); flex-shrink:0; }
  .dc-detail-meta-icon svg { width:18px; height:18px; }

  .dc-related { padding:48px 0; background:var(--dc-cream); }
  .dc-related-title { font-family:'Poppins',sans-serif; font-size:24px; font-weight:800; color:var(--dc-dark); margin-bottom:24px; }
  .dc-product-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:20px; }
  @media(max-width:1023px){ .dc-product-grid { grid-template-columns:repeat(3,1fr); } }
  @media(max-width:767px){ .dc-product-grid { grid-template-columns:repeat(2,1fr); gap:12px; } }
  .dc-product-card { position:relative; background:#fff; border-radius:20px; overflow:hidden; border:1px solid var(--dc-border); transition:transform .25s, box-shadow .25s; cursor:pointer; }
  .dc-product-card:hover { transform:translateY(-6px); box-shadow:0 16px 40px rgba(37,99,235,0.12); }
  .dc-product-media { aspect-ratio:1/1; overflow:hidden; background:var(--dc-cream); }
  .dc-product-media img { width:100%; height:100%; object-fit:cover; transition:transform .4s ease; }
  .dc-product-card:hover .dc-product-media img { transform:scale(1.05); }
  .dc-product-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--dc-soft); color:var(--dc-blue); }
  .dc-product-placeholder span { font-family:'Poppins',sans-serif; font-size:38px; font-weight:800; }
  .dc-product-body { padding:16px; }
  .dc-product-brand { font-family:'Poppins',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.08em; color:var(--dc-blue); font-weight:600; margin-bottom:4px; }
  .dc-product-name { font-family:'Poppins',sans-serif; font-size:15px; font-weight:600; margin-bottom:10px; line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; min-height:42px; color:var(--dc-dark); }
  .dc-product-price { font-family:'Poppins',sans-serif; font-size:18px; font-weight:800; color:var(--dc-dark); }
  .dc-add-btn { width:100%; margin-top:10px; padding:11px 14px; border-radius:999px; background:var(--dc-blue); color:#fff; display:flex; align-items:center; justify-content:center; gap:6px; transition:background .2s, transform .15s; border:none; cursor:pointer; font-family:'Poppins',sans-serif; font-size:13px; font-weight:600; }
  .dc-add-btn:hover { background:var(--dc-blue-dark); }
</style>

<div class="dc-container">
  <div class="dc-breadcrumb">
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

  <div class="dc-detail">
    <div class="dc-detail-grid">
      <div class="dc-detail-media">
        <?php if($hasDiscount && $discountPct > 0): ?>
        <div class="dc-detail-badge">-<?= $discountPct; ?>% Off</div>
        <?php endif; ?>
        <?php if($img): ?>
        <img class="dc-detail-img" src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" decoding="async">
        <?php else: ?>
        <div class="dc-detail-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
        <?php endif; ?>
      </div>

      <div class="dc-detail-info">
        <?php if(!empty($p->category_name)): ?>
        <div class="dc-detail-brand"><?= htmlspecialchars($p->category_name); ?></div>
        <?php endif; ?>
        <h1 class="dc-detail-title"><?= htmlspecialchars($p->item_name); ?></h1>
        <div class="dc-detail-rating">
          <span class="dc-detail-stars">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
          <span>4.8 (124 reviews)</span>
        </div>
        <div class="dc-detail-price">
          <span class="dc-detail-price-now"><?= sf_currency($price, $cur); ?></span>
          <?php if($hasDiscount): ?>
          <span class="dc-detail-price-old"><?= sf_currency($oldPrice, $cur); ?></span>
          <span class="dc-detail-price-save">Save <?= sf_currency($oldPrice - $price, $cur); ?></span>
          <?php endif; ?>
        </div>
        <div class="dc-detail-stock <?= $inStock ? 'in' : 'out'; ?>">
          <span class="dc-detail-stock-dot"></span>
          <?= $inStock ? 'In Stock' : 'Out of Stock'; ?>
        </div>
        <?php if(!empty($p->description)): ?>
        <div class="dc-detail-desc"><?= nl2br(htmlspecialchars($p->description)); ?></div>
        <?php endif; ?>

        <?php if($inStock || ($settings->allow_backorder ?? false)): ?>
        <div class="dc-detail-qty">
          <span style="font-family:'Poppins',sans-serif;font-size:14px;font-weight:600;color:var(--dc-dark);">Quantity</span>
          <div class="dc-qty-control">
            <button class="dc-qty-btn" onclick="adjustDetailQty(-1)">&minus;</button>
            <input type="number" id="detail-qty" class="dc-qty-input" value="1" min="1" max="<?= max(1, $p->stock); ?>">
            <button class="dc-qty-btn" onclick="adjustDetailQty(1)">+</button>
          </div>
        </div>
        <div class="dc-detail-actions">
          <button class="dc-btn dc-btn-blue" onclick="addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',parseInt(document.getElementById('detail-qty').value)||1,<?= $p->stock; ?>)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg> Add to Cart
          </button>
          <?php if($waNumber): ?>
          <button class="dc-btn dc-btn-wa" onclick="openWhatsAppOrderModal(<?= $p->id; ?>,'<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',<?= $p->stock; ?>)">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> Order on WhatsApp
          </button>
          <?php endif; ?>
        </div>
        <?php else: ?>
        <div class="dc-detail-actions">
          <button class="dc-btn dc-btn-outline" disabled style="opacity:0.6;cursor:not-allowed;">Out of Stock</button>
        </div>
        <?php endif; ?>

        <div class="dc-detail-meta">
          <div class="dc-detail-meta-item">
            <div class="dc-detail-meta-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="3" width="15" height="13"/><polygon points="16 8 20 8 23 11 23 16 16 16 16 8"/><circle cx="5.5" cy="18.5" r="2.5"/><circle cx="18.5" cy="18.5" r="2.5"/></svg></div>
            <span>Fast Pickup</span>
          </div>
          <div class="dc-detail-meta-item">
            <div class="dc-detail-meta-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg></div>
            <span>Trusted Brands</span>
          </div>
          <div class="dc-detail-meta-item">
            <div class="dc-detail-meta-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg></div>
            <span>Open Late</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<?php if(!empty($related_products)): ?>
<div class="dc-related">
  <div class="dc-container">
    <h2 class="dc-related-title">You May Also Like</h2>
    <div class="dc-product-grid">
      <?php foreach(array_slice($related_products, 0, 4) as $rp):
        $rPrice = $rp->effective_price ?? $rp->sales_price;
        $rImg = ($rp->item_image && file_exists($rp->item_image)) ? mp_minified_image_url($rp->item_image, 600) : '';
      ?>
      <a href="<?= base_url('store/' . $slug . '/product/' . $rp->id); ?>" class="dc-product-card">
        <div class="dc-product-media">
          <?php if($rImg): ?>
          <img src="<?= $rImg; ?>" alt="<?= htmlspecialchars($rp->item_name); ?>" loading="lazy" decoding="async">
          <?php else: ?>
          <div class="dc-product-placeholder"><span><?= htmlspecialchars(substr($rp->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="dc-product-body">
          <?php if(!empty($rp->category_name)): ?>
          <div class="dc-product-brand"><?= htmlspecialchars($rp->category_name); ?></div>
          <?php endif; ?>
          <div class="dc-product-name"><?= htmlspecialchars($rp->item_name); ?></div>
          <div class="dc-product-price"><?= sf_currency($rPrice, $cur); ?></div>
          <button class="dc-add-btn" onclick="event.preventDefault();event.stopPropagation();addToCart(<?= $rp->id; ?>,'product','<?= htmlspecialchars(addslashes($rp->item_name)); ?>',<?= $rPrice; ?>,'<?= $rp->item_image; ?>',1,<?= $rp->stock; ?>)" aria-label="Add to cart"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add to Cart</button>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<div id="wa-order-modal" style="display:none;position:fixed;inset:0;z-index:9999;">
  <div style="position:absolute;inset:0;background:rgba(30,58,138,0.4);" onclick="closeWhatsAppOrderModal()"></div>
  <div style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);width:440px;max-width:calc(100vw - 32px);max-height:90vh;overflow-y:auto;background:#fff;border-radius:20px;box-shadow:0 24px 60px rgba(37,99,235,0.25);border:1px solid var(--dc-border);">
    <div style="display:flex;align-items:center;justify-content:space-between;padding:20px 24px;border-bottom:1px solid var(--dc-border);">
      <h3 style="font-family:'Poppins',sans-serif;font-size:20px;font-weight:800;color:var(--dc-dark);margin:0;">Order via WhatsApp</h3>
      <button onclick="closeWhatsAppOrderModal()" style="width:32px;height:32px;border-radius:999px;border:none;background:var(--dc-cream);color:var(--dc-dark);cursor:pointer;display:flex;align-items:center;justify-content:center;" aria-label="Close"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div style="padding:24px;">
      <div style="display:flex;gap:14px;align-items:center;padding:14px;background:var(--dc-cream);border-radius:12px;margin-bottom:20px;">
        <img id="wa-modal-product-img" src="" alt="" style="width:56px;height:56px;border-radius:8px;object-fit:cover;flex-shrink:0;">
        <div style="flex:1;min-width:0;">
          <p id="wa-modal-product-name" style="font-family:'Poppins',sans-serif;font-size:15px;font-weight:600;color:var(--dc-dark);margin:0 0 4px;"></p>
          <div id="wa-modal-product-price" style="font-family:'Poppins',sans-serif;font-size:16px;font-weight:800;color:var(--dc-blue);"></div>
        </div>
      </div>
      <div style="display:flex;flex-direction:column;gap:14px;">
        <div><label style="font-family:'Poppins',sans-serif;font-size:11px;text-transform:uppercase;letter-spacing:0.08em;font-weight:600;color:var(--dc-dark);margin-bottom:5px;display:block;">Your Name</label><input type="text" id="wa-modal-name" placeholder="Enter your name" style="width:100%;padding:12px 14px;border:1px solid var(--dc-border);border-radius:10px;font-family:'Poppins',sans-serif;font-size:14px;background:var(--dc-cream);outline:none;color:var(--dc-dark);box-sizing:border-box;"></div>
        <div><label style="font-family:'Poppins',sans-serif;font-size:11px;text-transform:uppercase;letter-spacing:0.08em;font-weight:600;color:var(--dc-dark);margin-bottom:5px;display:block;">Phone Number</label><input type="tel" id="wa-modal-phone" placeholder="Enter your phone number" style="width:100%;padding:12px 14px;border:1px solid var(--dc-border);border-radius:10px;font-family:'Poppins',sans-serif;font-size:14px;background:var(--dc-cream);outline:none;color:var(--dc-dark);box-sizing:border-box;"></div>
        <div><label style="font-family:'Poppins',sans-serif;font-size:11px;text-transform:uppercase;letter-spacing:0.08em;font-weight:600;color:var(--dc-dark);margin-bottom:5px;display:block;">Quantity</label><input type="number" id="wa-modal-qty" value="1" min="1" style="width:100%;padding:12px 14px;border:1px solid var(--dc-border);border-radius:10px;font-family:'Poppins',sans-serif;font-size:14px;background:var(--dc-cream);outline:none;color:var(--dc-dark);box-sizing:border-box;"></div>
        <div><label style="font-family:'Poppins',sans-serif;font-size:11px;text-transform:uppercase;letter-spacing:0.08em;font-weight:600;color:var(--dc-dark);margin-bottom:5px;display:block;">Note (optional)</label><textarea id="wa-modal-note" placeholder="Any special requests..." style="width:100%;padding:12px 14px;border:1px solid var(--dc-border);border-radius:10px;font-family:'Poppins',sans-serif;font-size:14px;background:var(--dc-cream);outline:none;color:var(--dc-dark);box-sizing:border-box;resize:vertical;min-height:64px;"></textarea></div>
      </div>
      <div style="display:flex;gap:10px;margin-top:20px;">
        <button onclick="closeWhatsAppOrderModal()" style="padding:14px 20px;border-radius:999px;border:1px solid var(--dc-border);background:#fff;color:var(--dc-dark);font-family:'Poppins',sans-serif;font-size:14px;font-weight:600;cursor:pointer;">Cancel</button>
        <button onclick="sendWhatsAppOrder()" style="flex:1;padding:14px;border-radius:999px;border:none;background:#25D366;color:#fff;font-family:'Poppins',sans-serif;font-size:14px;font-weight:600;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> Send via WhatsApp</button>
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
  const storeName = '<?= htmlspecialchars(addslashes($store->store_name ?? 'Daily Cart')); ?>';
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
