<?php
/**
 * Pharma Clinical — Products Listing
 * Clinical-grade product grid with filter chips, search and pagination
 * in a professional medical-blue design.
 */
$slug = $settings->store_slug ?? '';
$cur = $store_currency ?? null;
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

  .pc-page { padding:36px 0 72px; }
  .pc-page-head { margin-bottom:32px; }
  .pc-section-label { font-family:'Inter',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.16em; color:var(--pc-blue); font-weight:600; margin-bottom:8px; }
  .pc-section-title { font-family:'Inter',sans-serif; font-size:32px; margin:0; font-weight:800; color:var(--pc-dark); letter-spacing:-0.01em; }

  .pc-filters { display:flex; gap:12px; align-items:center; margin-bottom:32px; flex-wrap:wrap; }
  .pc-search-bar { flex:1; max-width:420px; position:relative; }
  .pc-search-bar input { width:100%; padding:12px 14px 12px 42px; border:1px solid var(--pc-border); border-radius:8px; font-family:'Inter',sans-serif; font-size:14px; background:var(--pc-light); outline:none; transition:border-color .2s; color:var(--pc-dark); }
  .pc-search-bar input:focus { border-color:var(--pc-blue); background:#fff; }
  .pc-search-icon { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--pc-gray); }
  .pc-filter-chip { padding:10px 20px; border-radius:8px; border:1px solid var(--pc-border); background:#fff; font-family:'Inter',sans-serif; font-size:13px; font-weight:600; color:var(--pc-gray); cursor:pointer; transition:all .2s; text-decoration:none; display:inline-block; }
  .pc-filter-chip.active, .pc-filter-chip:hover { background:var(--pc-blue); color:#fff; border-color:var(--pc-blue); }

  .pc-product-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:20px; }
  @media(max-width:1023px){ .pc-product-grid { grid-template-columns:repeat(3,1fr); } }
  @media(max-width:767px){ .pc-product-grid { grid-template-columns:repeat(2,1fr); gap:12px; } }
  .pc-product-card { position:relative; background:#fff; border-radius:12px; overflow:hidden; border:1px solid var(--pc-border); transition:transform .25s, box-shadow .25s; cursor:pointer; }
  .pc-product-card:hover { transform:translateY(-4px); box-shadow:0 12px 32px rgba(0,94,184,0.1); }
  .pc-product-wishlist { position:absolute; top:12px; right:12px; width:36px; height:36px; border-radius:8px; background:#fff; display:flex; align-items:center; justify-content:center; z-index:2; transition:color .2s, transform .2s; box-shadow:0 2px 8px rgba(0,0,0,0.08); border:none; cursor:pointer; }
  .pc-product-wishlist:hover { color:var(--pc-blue); transform:scale(1.08); }
  .pc-product-badge { position:absolute; top:12px; left:12px; background:var(--pc-teal); color:#fff; font-family:'Inter',sans-serif; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; padding:5px 10px; border-radius:6px; z-index:2; }
  .pc-product-media { aspect-ratio:1/1; overflow:hidden; background:var(--pc-light); }
  .pc-product-media img { width:100%; height:100%; object-fit:cover; transition:transform .4s ease; }
  .pc-product-card:hover .pc-product-media img { transform:scale(1.05); }
  .pc-product-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--pc-light); color:var(--pc-blue); }
  .pc-product-placeholder span { font-family:'Inter',sans-serif; font-size:36px; font-weight:800; }
  .pc-product-body { padding:16px; }
  .pc-product-brand { font-family:'Inter',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.08em; color:var(--pc-teal); font-weight:600; margin-bottom:4px; }
  .pc-product-name { font-family:'Inter',sans-serif; font-size:14px; font-weight:600; margin-bottom:10px; line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; min-height:40px; color:var(--pc-dark); }
  .pc-product-footer { display:flex; flex-direction:column; gap:8px; }
  .pc-product-price { font-family:'Inter',sans-serif; font-size:18px; font-weight:800; color:var(--pc-dark); }
  .pc-product-price .old { font-family:'Inter',sans-serif; font-size:13px; color:var(--pc-gray); text-decoration:line-through; margin-left:6px; font-weight:500; }
  .pc-card-actions { display:flex; gap:8px; }
  @media(max-width:767px){ .pc-card-actions { flex-direction:column; gap:6px; } }
  .pc-add-btn { flex:1; padding:11px 14px; border-radius:8px; background:var(--pc-blue); color:#fff; display:flex; align-items:center; justify-content:center; gap:6px; transition:background .2s, transform .15s; border:none; cursor:pointer; font-family:'Inter',sans-serif; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; }
  .pc-add-btn:hover { background:var(--pc-blue-dark); }
  .pc-add-btn:active { transform:scale(0.97); }
  .pc-wa-btn { flex:1; padding:11px 14px; border-radius:8px; background:#25D366; color:#fff; display:flex; align-items:center; justify-content:center; gap:6px; transition:background .2s, transform .15s; border:none; cursor:pointer; font-family:'Inter',sans-serif; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; }
  .pc-wa-btn:hover { background:#1FB855; }
  .pc-wa-btn:active { transform:scale(0.97); }
  .pc-product-stock { font-family:'Inter',sans-serif; font-size:11px; color:#DC2626; font-weight:600; margin-top:6px; text-transform:uppercase; letter-spacing:0.06em; }
  .pc-product-stock.in { color:var(--pc-teal); }

  .pc-pagination { display:flex; justify-content:center; gap:8px; margin-top:44px; }
  .pc-page-btn { padding:10px 16px; border-radius:8px; border:1px solid var(--pc-border); background:#fff; font-family:'Inter',sans-serif; font-size:14px; font-weight:600; color:var(--pc-gray); text-decoration:none; transition:all .2s; }
  .pc-page-btn:hover { border-color:var(--pc-blue); color:var(--pc-blue); }
  .pc-page-btn.active { background:var(--pc-blue); color:#fff; border-color:var(--pc-blue); }

  .pc-empty { text-align:center; padding:88px 20px; color:var(--pc-gray); }
  .pc-empty-icon { font-size:48px; margin-bottom:14px; color:var(--pc-blue); }
  .pc-empty-title { font-family:'Inter',sans-serif; font-size:20px; font-weight:800; color:var(--pc-dark); margin-bottom:10px; }
  .pc-empty-text { font-family:'Inter',sans-serif; margin-bottom:22px; }
  .pc-btn-blue { display:inline-flex; padding:14px 34px; border-radius:8px; background:var(--pc-blue); color:#fff; font-family:'Inter',sans-serif; font-weight:600; font-size:13px; text-transform:uppercase; letter-spacing:0.08em; text-decoration:none; }

  .wa-order-modal { display:none; position:fixed; inset:0; z-index:9999; }
  .wa-order-modal.open { display:block; }
  .wa-order-modal-overlay { position:absolute; inset:0; background:rgba(15,23,42,0.5); }
  .wa-order-modal-card { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:440px; max-width:calc(100vw - 32px); max-height:90vh; overflow-y:auto; background:#fff; border-radius:16px; box-shadow:0 24px 60px rgba(15,23,42,0.25); border:1px solid var(--pc-border); }
  .wa-order-modal-header { display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid var(--pc-border); }
  .wa-order-modal-title { font-family:'Inter',sans-serif; font-size:20px; font-weight:800; color:var(--pc-dark); margin:0; }
  .wa-order-modal-close { width:32px; height:32px; border-radius:8px; border:none; background:var(--pc-light); color:var(--pc-dark); cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background .2s; }
  .wa-order-modal-close:hover { background:var(--pc-border); }
  .wa-order-modal-body { padding:24px; }
  .wa-order-modal-product { display:flex; gap:14px; align-items:center; padding:14px; background:var(--pc-light); border-radius:8px; margin-bottom:20px; }
  .wa-order-modal-product img { width:56px; height:56px; border-radius:8px; object-fit:cover; flex-shrink:0; }
  .wa-order-modal-product-info { flex:1; min-width:0; }
  .wa-order-modal-product-name { font-family:'Inter',sans-serif; font-size:14px; font-weight:600; color:var(--pc-dark); margin:0 0 4px; line-height:1.3; }
  .wa-order-modal-product-price { font-family:'Inter',sans-serif; font-size:16px; font-weight:800; color:var(--pc-blue); }
  .wa-order-modal-fields { display:flex; flex-direction:column; gap:14px; }
  .wa-order-modal-fields label { font-family:'Inter',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.08em; font-weight:600; color:var(--pc-dark); margin-bottom:5px; display:block; }
  .wa-order-modal-fields input, .wa-order-modal-fields textarea { width:100%; padding:12px 14px; border:1px solid var(--pc-border); border-radius:8px; font-family:'Inter',sans-serif; font-size:14px; background:var(--pc-light); outline:none; transition:border-color .2s; color:var(--pc-dark); box-sizing:border-box; }
  .wa-order-modal-fields input:focus, .wa-order-modal-fields textarea:focus { border-color:var(--pc-blue); background:#fff; }
  .wa-order-modal-fields textarea { resize:vertical; min-height:64px; }
  .wa-order-modal-actions { display:flex; gap:10px; margin-top:20px; }
  .wa-order-modal-send { flex:1; padding:14px; border-radius:8px; border:none; background:#25D366; color:#fff; font-family:'Inter',sans-serif; font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; cursor:pointer; transition:background .2s; display:flex; align-items:center; justify-content:center; gap:8px; }
  .wa-order-modal-send:hover { background:#1FB855; }
  .wa-order-modal-cancel { padding:14px 20px; border-radius:8px; border:1px solid var(--pc-border); background:#fff; color:var(--pc-dark); font-family:'Inter',sans-serif; font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; cursor:pointer; transition:background .2s; }
  .wa-order-modal-cancel:hover { background:var(--pc-light); }
</style>

<div class="pc-container">
  <div class="pc-breadcrumb">
    <a href="<?= base_url('store/' . $slug); ?>">Home</a>
    <span class="sep">/</span>
    <span>Shop</span>
    <?php if(!empty($search)): ?>
    <span class="sep">/</span><span>"<?= htmlspecialchars($search); ?>"</span>
    <?php endif; ?>
  </div>

  <div class="pc-page">
    <div class="pc-page-head">
      <div class="pc-section-label">Pharmacy</div>
      <h1 class="pc-section-title"><?= !empty($search) ? 'Search: ' . htmlspecialchars($search) : 'All Products'; ?></h1>
    </div>

    <div class="pc-filters">
      <div class="pc-search-bar">
        <span class="pc-search-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
        <input type="text" placeholder="Search medicines, vitamins, supplements..." value="<?= htmlspecialchars($search ?? ''); ?>" onkeydown="if(event.key==='Enter'){const u=new URL(location.href);u.searchParams.set('search',this.value);u.searchParams.delete('page');location.href=u.href;}">
      </div>
      <?php if(!empty($categories)): ?>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pc-filter-chip <?= empty($category_id) ? 'active' : ''; ?>">All</a>
      <?php foreach($categories as $cat): ?>
      <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="pc-filter-chip <?= ($category_id ?? 0) == $cat->id ? 'active' : ''; ?>"><?= htmlspecialchars($cat->category_name); ?></a>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <?php if(!empty($products)): ?>
    <div class="pc-product-grid">
      <?php foreach($products as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $oldPrice = $p->original_price ?? $p->sales_price;
        $hasDiscount = $oldPrice > $price;
        $discountPct = $hasDiscount ? round((($oldPrice - $price) / $oldPrice) * 100) : 0;
        $img = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
      ?>
      <div class="pc-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, <?= $hasDiscount ? $oldPrice : 0; ?>)">
        <?php if($hasDiscount && $discountPct > 0): ?>
        <span class="pc-product-badge">-<?= $discountPct; ?>%</span>
        <?php endif; ?>
        <button class="pc-product-wishlist" onclick="event.stopPropagation();" aria-label="Wishlist"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></button>
        <div class="pc-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
          <?php else: ?>
          <div class="pc-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="pc-product-body">
          <?php if(!empty($p->category_name)): ?>
          <div class="pc-product-brand"><?= htmlspecialchars($p->category_name); ?></div>
          <?php endif; ?>
          <div class="pc-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="pc-product-footer">
            <div class="pc-product-price"><?= sf_currency($price, $cur); ?><?php if($hasDiscount): ?><span class="old"><?= sf_currency($oldPrice, $cur); ?></span><?php endif; ?></div>
            <div class="pc-card-actions">
              <button class="pc-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add to cart"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="pc-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $p->id; ?>,'<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',<?= $p->stock; ?>)" aria-label="Order via WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
            </div>
          </div>
          <?php if($p->stock <= 0 && !($settings->allow_backorder ?? false)): ?>
          <div class="pc-product-stock">Out of Stock</div>
          <?php else: ?>
          <div class="pc-product-stock in">In Stock</div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <?php if($total_pages > 1): ?>
    <div class="pc-pagination">
      <?php if($page > 1): ?>
        <a class="pc-page-btn" href="<?= base_url('store/' . $slug . '/products'); ?>?page=<?= $page-1; ?><?= !empty($category_id) ? '&category=' . $category_id : ''; ?><?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>">&laquo;</a>
      <?php endif; ?>
      <?php for($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
        <a class="pc-page-btn <?= $i == $page ? 'active' : ''; ?>" href="<?= base_url('store/' . $slug . '/products'); ?>?page=<?= $i; ?><?= !empty($category_id) ? '&category=' . $category_id : ''; ?><?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>"><?= $i; ?></a>
      <?php endfor; ?>
      <?php if($page < $total_pages): ?>
        <a class="pc-page-btn" href="<?= base_url('store/' . $slug . '/products'); ?>?page=<?= $page+1; ?><?= !empty($category_id) ? '&category=' . $category_id : ''; ?><?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>">&raquo;</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="pc-empty">
      <div class="pc-empty-icon"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg></div>
      <div class="pc-empty-title">No products found</div>
      <p class="pc-empty-text">Try a different search or browse all products.</p>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pc-btn-blue">View All Products</a>
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
  const storeName = '<?= htmlspecialchars(addslashes($store->store_name ?? 'Pharmacy')); ?>';
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
