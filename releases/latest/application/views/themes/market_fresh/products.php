<?php
/**
 * Market Fresh — Products Listing
 * Vibrant supermarket product grid with fresh green and warm orange accents.
 * Uses mp_minified_image_url() for all imagery.
 */
$slug = $settings->store_slug ?? '';
$cur = $store_currency ?? null;
?>
<style>
  .theme-market_fresh .mp-topbar,
  .theme-market_fresh .mp-announcement,
  .theme-market_fresh .mp-nav,
  .theme-market_fresh .mp-header,
  .theme-market_fresh .mp-mobile-menu-btn,
  .theme-market_fresh .mp-footer-space { display:none !important; }

  :root {
    --mf-green:#16A34A;
    --mf-green-dark:#15803D;
    --mf-green-deep:#14532D;
    --mf-orange:#F97316;
    --mf-orange-dark:#EA580C;
    --mf-cream:#F0FDF4;
    --mf-soft:#DCFCE7;
    --mf-dark:#1C2B25;
    --mf-gray:#5B6B63;
    --mf-border:#E3EDE7;
    --mf-white:#FFFFFF;
  }

  .mf-container { max-width:1400px; margin:0 auto; padding:0 24px; }
  @media(max-width:767px){ .mf-container { padding:0 16px; } }

  .mf-breadcrumb { padding:24px 0 0; font-family:'Inter',sans-serif; font-size:13px; color:var(--mf-gray); }
  .mf-breadcrumb a { color:var(--mf-green); text-decoration:none; }
  .mf-breadcrumb a:hover { color:var(--mf-green-dark); }
  .mf-breadcrumb .sep { margin:0 8px; color:var(--mf-orange); }

  .mf-page { padding:36px 0 72px; }
  .mf-page-head { margin-bottom:32px; }
  .mf-section-label { font-family:'Inter',sans-serif; font-size:12px; text-transform:uppercase; letter-spacing:0.14em; color:var(--mf-orange); font-weight:600; margin-bottom:6px; }
  .mf-section-title { font-family:'Inter',sans-serif; font-size:30px; margin:0; font-weight:800; color:var(--mf-dark); letter-spacing:-0.02em; }

  .mf-filters { display:flex; gap:12px; align-items:center; margin-bottom:32px; flex-wrap:wrap; }
  .mf-search-bar { flex:1; max-width:420px; position:relative; }
  .mf-search-bar input { width:100%; padding:12px 14px 12px 42px; border:1px solid var(--mf-border); border-radius:999px; font-family:'Inter',sans-serif; font-size:14px; background:var(--mf-white); outline:none; transition:border-color .2s; color:var(--mf-dark); }
  .mf-search-bar input:focus { border-color:var(--mf-green); }
  .mf-search-icon { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--mf-green); }
  .mf-filter-chip { padding:10px 20px; border-radius:999px; border:1px solid var(--mf-border); background:var(--mf-white); font-family:'Inter',sans-serif; font-size:13px; font-weight:600; color:var(--mf-gray); cursor:pointer; transition:all .2s; text-decoration:none; display:inline-block; }
  .mf-filter-chip.active, .mf-filter-chip:hover { background:var(--mf-green); color:#fff; border-color:var(--mf-green); }

  .mf-product-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:20px; }
  @media(max-width:1023px){ .mf-product-grid { grid-template-columns:repeat(3,1fr); } }
  @media(max-width:767px){ .mf-product-grid { grid-template-columns:repeat(2,1fr); gap:12px; } }
  .mf-product-card { position:relative; background:var(--mf-white); border-radius:16px; overflow:hidden; border:1px solid var(--mf-border); transition:transform .25s, box-shadow .25s; cursor:pointer; }
  .mf-product-card:hover { transform:translateY(-6px); box-shadow:0 16px 40px rgba(22,163,74,0.12); }
  .mf-product-wishlist { position:absolute; top:12px; right:12px; width:36px; height:36px; border-radius:999px; background:#fff; display:flex; align-items:center; justify-content:center; z-index:2; transition:color .2s, transform .2s; box-shadow:0 2px 8px rgba(0,0,0,0.06); border:none; cursor:pointer; color:var(--mf-gray); }
  .mf-product-wishlist:hover { color:var(--mf-orange); transform:scale(1.08); }
  .mf-product-badge { position:absolute; top:12px; left:12px; background:var(--mf-orange); color:#fff; font-family:'Inter',sans-serif; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.04em; padding:5px 11px; border-radius:999px; z-index:2; }
  .mf-product-media { aspect-ratio:1/1; overflow:hidden; background:var(--mf-cream); }
  .mf-product-media img { width:100%; height:100%; object-fit:cover; transition:transform .4s ease; }
  .mf-product-card:hover .mf-product-media img { transform:scale(1.05); }
  .mf-product-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--mf-soft); color:var(--mf-green); }
  .mf-product-placeholder span { font-family:'Inter',sans-serif; font-size:38px; font-weight:800; }
  .mf-product-body { padding:16px; }
  .mf-product-brand { font-family:'Inter',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.08em; color:var(--mf-green); font-weight:600; margin-bottom:4px; }
  .mf-product-name { font-family:'Inter',sans-serif; font-size:15px; font-weight:600; margin-bottom:10px; line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; min-height:42px; color:var(--mf-dark); }
  .mf-product-footer { display:flex; flex-direction:column; gap:8px; }
  .mf-product-price { font-family:'Inter',sans-serif; font-size:18px; font-weight:800; color:var(--mf-dark); }
  .mf-product-price .old { font-family:'Inter',sans-serif; font-size:13px; color:var(--mf-gray); text-decoration:line-through; margin-left:6px; font-weight:400; }
  .mf-card-actions { display:flex; flex-direction:column; gap:8px; }
  .mf-add-btn { width:100%; padding:11px 14px; border-radius:999px; background:var(--mf-green); color:#fff; display:flex; align-items:center; justify-content:center; gap:6px; transition:background .2s, transform .15s; border:none; cursor:pointer; font-family:'Inter',sans-serif; font-size:13px; font-weight:600; }
  .mf-add-btn:hover { background:var(--mf-green-dark); }
  .mf-add-btn:active { transform:scale(0.97); }
  .mf-wa-btn { width:100%; padding:11px 14px; border-radius:999px; background:#25D366; color:#fff; display:flex; align-items:center; justify-content:center; gap:6px; transition:background .2s, transform .15s; border:none; cursor:pointer; font-family:'Inter',sans-serif; font-size:13px; font-weight:600; }
  .mf-wa-btn:hover { background:#1FB855; }
  .mf-wa-btn:active { transform:scale(0.97); }
  .mf-product-stock { font-family:'Inter',sans-serif; font-size:11px; color:#DC2626; font-weight:600; margin-top:6px; text-transform:uppercase; letter-spacing:0.04em; }
  .mf-product-stock.in { color:var(--mf-green); }

  .mf-pagination { display:flex; justify-content:center; gap:8px; margin-top:44px; }
  .mf-page-btn { padding:10px 16px; border-radius:999px; border:1px solid var(--mf-border); background:var(--mf-white); font-family:'Inter',sans-serif; font-size:14px; font-weight:600; color:var(--mf-gray); text-decoration:none; transition:all .2s; }
  .mf-page-btn:hover { border-color:var(--mf-green); color:var(--mf-green); }
  .mf-page-btn.active { background:var(--mf-green); color:#fff; border-color:var(--mf-green); }

  .mf-empty { text-align:center; padding:88px 20px; color:var(--mf-gray); }
  .mf-empty-icon { font-size:48px; margin-bottom:14px; color:var(--mf-green); }
  .mf-empty-title { font-family:'Inter',sans-serif; font-size:22px; font-weight:800; color:var(--mf-dark); margin-bottom:10px; }
  .mf-empty-text { font-family:'Inter',sans-serif; margin-bottom:22px; font-size:15px; }
  .mf-empty .mf-btn-orange { padding:14px 34px; border-radius:999px; background:var(--mf-orange); color:#fff; font-family:'Inter',sans-serif; font-weight:600; font-size:14px; text-decoration:none; display:inline-block; }

  .wa-order-modal { display:none; position:fixed; inset:0; z-index:9999; }
  .wa-order-modal.open { display:block; }
  .wa-order-modal-overlay { position:absolute; inset:0; background:rgba(20,83,45,0.4); }
  .wa-order-modal-card { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:440px; max-width:calc(100vw - 32px); max-height:90vh; overflow-y:auto; background:#fff; border-radius:20px; box-shadow:0 24px 60px rgba(22,163,74,0.25); border:1px solid var(--mf-border); }
  .wa-order-modal-header { display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid var(--mf-border); }
  .wa-order-modal-title { font-family:'Inter',sans-serif; font-size:20px; font-weight:800; color:var(--mf-dark); margin:0; }
  .wa-order-modal-close { width:32px; height:32px; border-radius:999px; border:none; background:var(--mf-cream); color:var(--mf-dark); cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background .2s; }
  .wa-order-modal-close:hover { background:var(--mf-border); }
  .wa-order-modal-body { padding:24px; }
  .wa-order-modal-product { display:flex; gap:14px; align-items:center; padding:14px; background:var(--mf-cream); border-radius:12px; margin-bottom:20px; }
  .wa-order-modal-product img { width:56px; height:56px; border-radius:8px; object-fit:cover; flex-shrink:0; }
  .wa-order-modal-product-info { flex:1; min-width:0; }
  .wa-order-modal-product-name { font-family:'Inter',sans-serif; font-size:15px; font-weight:600; color:var(--mf-dark); margin:0 0 4px; line-height:1.3; }
  .wa-order-modal-product-price { font-family:'Inter',sans-serif; font-size:16px; font-weight:800; color:var(--mf-green); }
  .wa-order-modal-fields { display:flex; flex-direction:column; gap:14px; }
  .wa-order-modal-fields label { font-family:'Inter',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.08em; font-weight:600; color:var(--mf-dark); margin-bottom:5px; display:block; }
  .wa-order-modal-fields input, .wa-order-modal-fields textarea { width:100%; padding:12px 14px; border:1px solid var(--mf-border); border-radius:10px; font-family:'Inter',sans-serif; font-size:14px; background:var(--mf-cream); outline:none; transition:border-color .2s; color:var(--mf-dark); box-sizing:border-box; }
  .wa-order-modal-fields input:focus, .wa-order-modal-fields textarea:focus { border-color:var(--mf-green); background:#fff; }
  .wa-order-modal-fields textarea { resize:vertical; min-height:64px; }
  .wa-order-modal-actions { display:flex; gap:10px; margin-top:20px; }
  .wa-order-modal-send { flex:1; padding:14px; border-radius:999px; border:none; background:#25D366; color:#fff; font-family:'Inter',sans-serif; font-size:14px; font-weight:600; cursor:pointer; transition:background .2s; display:flex; align-items:center; justify-content:center; gap:8px; }
  .wa-order-modal-send:hover { background:#1FB855; }
  .wa-order-modal-cancel { padding:14px 20px; border-radius:999px; border:1px solid var(--mf-border); background:#fff; color:var(--mf-dark); font-family:'Inter',sans-serif; font-size:14px; font-weight:600; cursor:pointer; transition:background .2s; }
  .wa-order-modal-cancel:hover { background:var(--mf-cream); }

  /* Small-screen polish */
  @media(max-width:767px){
    .mf-search-bar { max-width:100%; flex-basis:100%; }
  }
  @media(max-width:480px){
    .mf-product-body { padding:12px; }
    .mf-product-name { font-size:14px; min-height:38px; }
    .mf-product-price { font-size:16px; }
    .mf-add-btn, .mf-wa-btn { padding:10px 12px; font-size:12px; }
    .mf-page-head .mf-section-title { font-size:24px; }
  }
</style>

<div class="mf-container">
  <div class="mf-breadcrumb">
    <a href="<?= base_url('store/' . $slug); ?>">Home</a>
    <span class="sep">/</span>
    <span>All Products</span>
    <?php if(!empty($search)): ?>
    <span class="sep">/</span><span>"<?= htmlspecialchars($search); ?>"</span>
    <?php endif; ?>
  </div>

  <div class="mf-page">
    <div class="mf-page-head">
      <div class="mf-section-label">Groceries</div>
      <h1 class="mf-section-title"><?= !empty($search) ? 'Search: ' . htmlspecialchars($search) : 'All Products'; ?></h1>
    </div>

    <div class="mf-filters">
      <div class="mf-search-bar">
        <span class="mf-search-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
        <input type="text" placeholder="Search groceries..." value="<?= htmlspecialchars($search ?? ''); ?>" onkeydown="if(event.key==='Enter'){const u=new URL(location.href);u.searchParams.set('search',this.value);u.searchParams.delete('page');location.href=u.href;}">
      </div>
      <?php if(!empty($categories)): ?>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="mf-filter-chip <?= empty($category_id) ? 'active' : ''; ?>">All</a>
      <?php foreach($categories as $cat): ?>
      <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="mf-filter-chip <?= ($category_id ?? 0) == $cat->id ? 'active' : ''; ?>"><?= htmlspecialchars($cat->category_name); ?></a>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <?php if(!empty($products)): ?>
    <div class="mf-product-grid">
      <?php foreach($products as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $oldPrice = $p->original_price ?? $p->sales_price;
        $hasDiscount = $oldPrice > $price;
        $discountPct = $hasDiscount ? round((($oldPrice - $price) / $oldPrice) * 100) : 0;
        $img = ($p->item_image && file_exists($p->item_image)) ? mp_minified_image_url($p->item_image, 600) : '';
      ?>
      <div class="mf-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, <?= $hasDiscount ? $oldPrice : 0; ?>)">
        <?php if($hasDiscount && $discountPct > 0): ?>
        <span class="mf-product-badge">-<?= $discountPct; ?>%</span>
        <?php endif; ?>
        <button class="mf-product-wishlist" onclick="event.stopPropagation();" aria-label="Wishlist"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></button>
        <div class="mf-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy" decoding="async">
          <?php else: ?>
          <div class="mf-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="mf-product-body">
          <?php if(!empty($p->category_name)): ?>
          <div class="mf-product-brand"><?= htmlspecialchars($p->category_name); ?></div>
          <?php endif; ?>
          <div class="mf-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="mf-product-footer">
            <div class="mf-product-price"><?= sf_currency($price, $cur); ?><?php if($hasDiscount): ?><span class="old"><?= sf_currency($oldPrice, $cur); ?></span><?php endif; ?></div>
            <div class="mf-card-actions">
              <button class="mf-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add to cart"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="mf-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $p->id; ?>,'<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',<?= $p->stock; ?>)" aria-label="Order via WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
            </div>
          </div>
          <?php if($p->stock <= 0 && !($settings->allow_backorder ?? false)): ?>
          <div class="mf-product-stock">Out of Stock</div>
          <?php else: ?>
          <div class="mf-product-stock in">In Stock</div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <?php if($total_pages > 1): ?>
    <div class="mf-pagination">
      <?php if($page > 1): ?>
        <a class="mf-page-btn" href="<?= base_url('store/' . $slug . '/products'); ?>?page=<?= $page-1; ?><?= !empty($category_id) ? '&category=' . $category_id : ''; ?><?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>">&laquo;</a>
      <?php endif; ?>
      <?php for($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
        <a class="mf-page-btn <?= $i == $page ? 'active' : ''; ?>" href="<?= base_url('store/' . $slug . '/products'); ?>?page=<?= $i; ?><?= !empty($category_id) ? '&category=' . $category_id : ''; ?><?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>"><?= $i; ?></a>
      <?php endfor; ?>
      <?php if($page < $total_pages): ?>
        <a class="mf-page-btn" href="<?= base_url('store/' . $slug . '/products'); ?>?page=<?= $page+1; ?><?= !empty($category_id) ? '&category=' . $category_id : ''; ?><?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>">&raquo;</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="mf-empty">
      <div class="mf-empty-icon"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg></div>
      <div class="mf-empty-title">No products found</div>
      <p class="mf-empty-text">Try a different search or browse all products.</p>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="mf-btn-orange">View All Products</a>
    </div>
    <?php endif; ?>
  </div>
</div>

<div class="wa-order-modal" id="wa-order-modal">
  <div class="wa-order-modal-overlay" onclick="closeWhatsAppOrderModal()"></div>
  <div class="wa-order-modal-card">
    <div class="wa-order-modal-header">
      <h3 class="wa-order-modal-title">Order via WhatsApp</h3>
      <button class="wa-order-modal-close" onclick="closeWhatsAppOrderModal()" aria-label="Close"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>
    </div>
    <div class="wa-order-modal-body">
      <div class="wa-order-modal-product">
        <img id="wa-modal-product-img" src="" alt="">
        <div class="wa-order-modal-product-info">
          <p class="wa-order-modal-product-name" id="wa-modal-product-name"></p>
          <div class="wa-order-modal-product-price" id="wa-modal-product-price"></div>
        </div>
      </div>
      <div class="wa-order-modal-fields">
        <div>
          <label for="wa-modal-name">Your Name</label>
          <input type="text" id="wa-modal-name" placeholder="Enter your name">
        </div>
        <div>
          <label for="wa-modal-phone">Phone Number</label>
          <input type="tel" id="wa-modal-phone" placeholder="Enter your phone number">
        </div>
        <div>
          <label for="wa-modal-qty">Quantity</label>
          <input type="number" id="wa-modal-qty" value="1" min="1">
        </div>
        <div>
          <label for="wa-modal-note">Note (optional)</label>
          <textarea id="wa-modal-note" placeholder="Any special requests..."></textarea>
        </div>
      </div>
      <div class="wa-order-modal-actions">
        <button class="wa-order-modal-cancel" onclick="closeWhatsAppOrderModal()">Cancel</button>
        <button class="wa-order-modal-send" onclick="sendWhatsAppOrder()"><svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> Send via WhatsApp</button>
      </div>
    </div>
  </div>
</div>

<script>
let waOrderProduct = null;
function openWhatsAppOrderModal(id, name, price, image, stock){
  waOrderProduct = {id, name, price, image, stock};
  document.getElementById('wa-modal-product-name').textContent = name;
  document.getElementById('wa-modal-product-price').textContent = formatMoney(price);
  if(image) document.getElementById('wa-modal-product-img').src = '<?= base_url(); ?>' + image;
  document.getElementById('wa-modal-qty').value = 1;
  document.getElementById('wa-modal-name').value = '';
  document.getElementById('wa-modal-phone').value = '';
  document.getElementById('wa-modal-note').value = '';
  document.getElementById('wa-order-modal').classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeWhatsAppOrderModal(){
  document.getElementById('wa-order-modal').classList.remove('open');
  document.body.style.overflow = '';
}
function sendWhatsAppOrder(){
  if(!waOrderProduct) return;
  const name = document.getElementById('wa-modal-name').value.trim();
  const phone = document.getElementById('wa-modal-phone').value.trim();
  const qty = parseInt(document.getElementById('wa-modal-qty').value) || 1;
  const note = document.getElementById('wa-modal-note').value.trim();
  if(!name || !phone){ showToast('Please enter your name and phone number'); return; }
  const storeName = '<?= htmlspecialchars(addslashes($store->store_name ?? 'Market Fresh')); ?>';
  const waNumber = '<?= preg_replace("/[^0-9]/", "", $settings->whatsapp_number ?? ""); ?>';
  if(!waNumber){ showToast('WhatsApp ordering is not available'); return; }
  let msg = 'Hello, I would like to order from ' + storeName;
  msg += '\n\nProduct: ' + waOrderProduct.name;
  msg += '\nQuantity: ' + qty;
  msg += '\nPrice: ' + formatMoney(waOrderProduct.price * qty);
  msg += '\n\nMy Details:';
  msg += '\nName: ' + name;
  msg += '\nPhone: ' + phone;
  if(note) msg += '\nNote: ' + note;
  msg += '\n\nThank you.';
  window.open('https://wa.me/' + waNumber + '?text=' + encodeURIComponent(msg), '_blank');
  closeWhatsAppOrderModal();
  showToast('Opening WhatsApp with your order details...');
}
</script>
