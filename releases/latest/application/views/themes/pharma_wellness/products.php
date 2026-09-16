<?php
/**
 * Pharma Wellness — Products Listing
 * Warm wellness-focused product grid with search and category filters.
 */
$slug = $settings->store_slug ?? '';
$cur = $store_currency ?? null;
?>
<style>
  .theme-pharma_wellness .mp-topbar,
  .theme-pharma_wellness .mp-announcement,
  .theme-pharma_wellness .mp-nav,
  .theme-pharma_wellness .mp-header,
  .theme-pharma_wellness .mp-mobile-menu-btn,
  .theme-pharma_wellness .mp-footer-space { display:none !important; }

  :root {
    --pw-sage:#0D9488;
    --pw-sage-dark:#0F766E;
    --pw-coral:#F97066;
    --pw-coral-dark:#E85C50;
    --pw-cream:#FFFBF5;
    --pw-dark:#134E4A;
    --pw-gray:#78716C;
    --pw-border:#E8F0EE;
    --pw-white:#FFFFFF;
  }

  .pw-container { max-width:1400px; margin:0 auto; padding:0 24px; }
  @media(max-width:767px){ .pw-container { padding:0 16px; } }

  .pw-breadcrumb { padding:24px 0 0; font-family:'Poppins',sans-serif; font-size:13px; color:var(--pw-gray); }
  .pw-breadcrumb a { color:var(--pw-sage); text-decoration:none; }
  .pw-breadcrumb a:hover { color:var(--pw-sage-dark); }
  .pw-breadcrumb .sep { margin:0 8px; color:var(--pw-coral); }

  .pw-page { padding:36px 0 72px; }
  .pw-page-head { margin-bottom:32px; }
  .pw-section-label { font-family:'Poppins',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.16em; color:var(--pw-coral); font-weight:600; margin-bottom:8px; }
  .pw-section-title { font-family:'Poppins',sans-serif; font-size:32px; margin:0; font-weight:700; color:var(--pw-dark); letter-spacing:-0.01em; }

  .pw-filters { display:flex; gap:12px; align-items:center; margin-bottom:32px; flex-wrap:wrap; }
  .pw-search-bar { flex:1; max-width:420px; position:relative; }
  .pw-search-bar input { width:100%; padding:12px 14px 12px 42px; border:1px solid var(--pw-border); border-radius:24px; font-family:'Poppins',sans-serif; font-size:14px; background:var(--pw-white); outline:none; transition:border-color .2s; color:var(--pw-dark); }
  .pw-search-bar input:focus { border-color:var(--pw-sage); }
  .pw-search-icon { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--pw-sage); }
  .pw-filter-chip { padding:10px 20px; border-radius:24px; border:1px solid var(--pw-border); background:var(--pw-white); font-family:'Poppins',sans-serif; font-size:13px; font-weight:500; color:var(--pw-gray); cursor:pointer; transition:all .2s; text-decoration:none; display:inline-block; }
  .pw-filter-chip.active, .pw-filter-chip:hover { background:var(--pw-sage); color:#fff; border-color:var(--pw-sage); }

  .pw-product-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:20px; }
  @media(max-width:1023px){ .pw-product-grid { grid-template-columns:repeat(3,1fr); } }
  @media(max-width:767px){ .pw-product-grid { grid-template-columns:repeat(2,1fr); gap:12px; } }
  .pw-product-card { position:relative; background:var(--pw-white); border-radius:20px; overflow:hidden; border:1px solid var(--pw-border); transition:transform .25s, box-shadow .25s; cursor:pointer; }
  .pw-product-card:hover { transform:translateY(-6px); box-shadow:0 20px 40px rgba(13,148,136,0.12); }
  .pw-product-wishlist { position:absolute; top:12px; right:12px; width:36px; height:36px; border-radius:50%; background:#fff; display:flex; align-items:center; justify-content:center; z-index:2; transition:color .2s, transform .2s; box-shadow:0 2px 8px rgba(0,0,0,0.06); border:none; cursor:pointer; color:var(--pw-gray); }
  .pw-product-wishlist:hover { color:var(--pw-coral); transform:scale(1.08); }
  .pw-product-badge { position:absolute; top:12px; left:12px; background:var(--pw-coral); color:#fff; font-family:'Poppins',sans-serif; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; padding:5px 10px; border-radius:12px; z-index:2; }
  .pw-product-media { aspect-ratio:1/1; overflow:hidden; background:var(--pw-cream); }
  .pw-product-media img { width:100%; height:100%; object-fit:cover; transition:transform .4s ease; }
  .pw-product-card:hover .pw-product-media img { transform:scale(1.05); }
  .pw-product-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--pw-cream); color:var(--pw-sage); }
  .pw-product-placeholder span { font-family:'Poppins',sans-serif; font-size:36px; font-weight:700; }
  .pw-product-body { padding:18px; }
  .pw-product-brand { font-family:'Poppins',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.08em; color:var(--pw-sage); font-weight:500; margin-bottom:4px; }
  .pw-product-name { font-family:'Poppins',sans-serif; font-size:14px; font-weight:600; margin-bottom:10px; line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; min-height:40px; color:var(--pw-dark); }
  .pw-product-footer { display:flex; flex-direction:column; gap:8px; }
  .pw-product-price { font-family:'Poppins',sans-serif; font-size:18px; font-weight:700; color:var(--pw-dark); }
  .pw-product-price .old { font-family:'Poppins',sans-serif; font-size:13px; color:var(--pw-gray); text-decoration:line-through; margin-left:6px; font-weight:400; }
  .pw-card-actions { display:flex; gap:8px; }
  @media(max-width:767px){ .pw-card-actions { flex-direction:column; gap:6px; } }
  .pw-add-btn { flex:1; padding:11px 14px; border-radius:24px; background:var(--pw-sage); color:#fff; display:flex; align-items:center; justify-content:center; gap:6px; transition:background .2s, transform .15s; border:none; cursor:pointer; font-family:'Poppins',sans-serif; font-size:12px; font-weight:500; }
  .pw-add-btn:hover { background:var(--pw-sage-dark); }
  .pw-add-btn:active { transform:scale(0.97); }
  .pw-wa-btn { flex:1; padding:11px 14px; border-radius:24px; background:#25D366; color:#fff; display:flex; align-items:center; justify-content:center; gap:6px; transition:background .2s, transform .15s; border:none; cursor:pointer; font-family:'Poppins',sans-serif; font-size:12px; font-weight:500; }
  .pw-wa-btn:hover { background:#1FB855; }
  .pw-wa-btn:active { transform:scale(0.97); }
  .pw-product-stock { font-family:'Poppins',sans-serif; font-size:11px; color:#DC2626; font-weight:500; margin-top:6px; text-transform:uppercase; letter-spacing:0.06em; }
  .pw-product-stock.in { color:var(--pw-sage); }

  .pw-pagination { display:flex; justify-content:center; gap:8px; margin-top:44px; }
  .pw-page-btn { padding:10px 16px; border-radius:24px; border:1px solid var(--pw-border); background:var(--pw-white); font-family:'Poppins',sans-serif; font-size:14px; font-weight:500; color:var(--pw-gray); text-decoration:none; transition:all .2s; }
  .pw-page-btn:hover { border-color:var(--pw-sage); color:var(--pw-sage); }
  .pw-page-btn.active { background:var(--pw-sage); color:#fff; border-color:var(--pw-sage); }

  .pw-empty { text-align:center; padding:88px 20px; color:var(--pw-gray); }
  .pw-empty-icon { font-size:48px; margin-bottom:14px; color:var(--pw-sage); }
  .pw-empty-title { font-family:'Poppins',sans-serif; font-size:20px; font-weight:700; color:var(--pw-dark); margin-bottom:10px; }
  .pw-empty-text { font-family:'Poppins',sans-serif; margin-bottom:22px; }
  .pw-btn-sage { display:inline-flex; padding:14px 34px; border-radius:24px; background:var(--pw-sage); color:#fff; font-family:'Poppins',sans-serif; font-weight:600; font-size:13px; text-decoration:none; }

  .wa-order-modal { display:none; position:fixed; inset:0; z-index:9999; }
  .wa-order-modal.open { display:block; }
  .wa-order-modal-overlay { position:absolute; inset:0; background:rgba(13,148,136,0.4); }
  .wa-order-modal-card { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:440px; max-width:calc(100vw - 32px); max-height:90vh; overflow-y:auto; background:var(--pw-white); border-radius:24px; box-shadow:0 24px 60px rgba(13,148,136,0.25); border:1px solid var(--pw-border); }
  .wa-order-modal-header { display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid var(--pw-border); }
  .wa-order-modal-title { font-family:'Poppins',sans-serif; font-size:20px; font-weight:700; color:var(--pw-dark); margin:0; }
  .wa-order-modal-close { width:32px; height:32px; border-radius:12px; border:none; background:var(--pw-cream); color:var(--pw-dark); cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background .2s; }
  .wa-order-modal-close:hover { background:var(--pw-border); }
  .wa-order-modal-body { padding:24px; }
  .wa-order-modal-product { display:flex; gap:14px; align-items:center; padding:14px; background:var(--pw-cream); border-radius:16px; margin-bottom:20px; }
  .wa-order-modal-product img { width:56px; height:56px; border-radius:12px; object-fit:cover; flex-shrink:0; }
  .wa-order-modal-product-info { flex:1; min-width:0; }
  .wa-order-modal-product-name { font-family:'Poppins',sans-serif; font-size:14px; font-weight:600; color:var(--pw-dark); margin:0 0 4px; line-height:1.3; }
  .wa-order-modal-product-price { font-family:'Poppins',sans-serif; font-size:16px; font-weight:700; color:var(--pw-sage); }
  .wa-order-modal-fields { display:flex; flex-direction:column; gap:14px; }
  .wa-order-modal-fields label { font-family:'Poppins',sans-serif; font-size:11px; text-transform:uppercase; letter-spacing:0.08em; font-weight:600; color:var(--pw-dark); margin-bottom:5px; display:block; }
  .wa-order-modal-fields input, .wa-order-modal-fields textarea { width:100%; padding:12px 14px; border:1px solid var(--pw-border); border-radius:12px; font-family:'Poppins',sans-serif; font-size:14px; background:var(--pw-cream); outline:none; transition:border-color .2s; color:var(--pw-dark); box-sizing:border-box; }
  .wa-order-modal-fields input:focus, .wa-order-modal-fields textarea:focus { border-color:var(--pw-sage); background:#fff; }
  .wa-order-modal-fields textarea { resize:vertical; min-height:64px; }
  .wa-order-modal-actions { display:flex; gap:10px; margin-top:20px; }
  .wa-order-modal-send { flex:1; padding:14px; border-radius:24px; border:none; background:#25D366; color:#fff; font-family:'Poppins',sans-serif; font-size:13px; font-weight:600; cursor:pointer; transition:background .2s; display:flex; align-items:center; justify-content:center; gap:8px; }
  .wa-order-modal-send:hover { background:#1FB855; }
  .wa-order-modal-cancel { padding:14px 20px; border-radius:24px; border:1px solid var(--pw-border); background:#fff; color:var(--pw-dark); font-family:'Poppins',sans-serif; font-size:13px; font-weight:600; cursor:pointer; transition:background .2s; }
  .wa-order-modal-cancel:hover { background:var(--pw-cream); }
</style>

<div class="pw-container">
  <div class="pw-breadcrumb">
    <a href="<?= base_url('store/' . $slug); ?>">Home</a>
    <span class="sep">/</span>
    <span>Shop</span>
    <?php if(!empty($search)): ?>
    <span class="sep">/</span><span>"<?= htmlspecialchars($search); ?>"</span>
    <?php endif; ?>
  </div>

  <div class="pw-page">
    <div class="pw-page-head">
      <div class="pw-section-label">Wellness</div>
      <h1 class="pw-section-title"><?= !empty($search) ? 'Search: ' . htmlspecialchars($search) : 'All Products'; ?></h1>
    </div>

    <div class="pw-filters">
      <div class="pw-search-bar">
        <span class="pw-search-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
        <input type="text" placeholder="Search wellness products..." value="<?= htmlspecialchars($search ?? ''); ?>" onkeydown="if(event.key==='Enter'){const u=new URL(location.href);u.searchParams.set('search',this.value);u.searchParams.delete('page');location.href=u.href;}">
      </div>
      <?php if(!empty($categories)): ?>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pw-filter-chip <?= empty($category_id) ? 'active' : ''; ?>">All</a>
      <?php foreach($categories as $cat): ?>
      <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="pw-filter-chip <?= ($category_id ?? 0) == $cat->id ? 'active' : ''; ?>"><?= htmlspecialchars($cat->category_name); ?></a>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <?php if(!empty($products)): ?>
    <div class="pw-product-grid">
      <?php foreach($products as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $oldPrice = $p->original_price ?? $p->sales_price;
        $hasDiscount = $oldPrice > $price;
        $discountPct = $hasDiscount ? round((($oldPrice - $price) / $oldPrice) * 100) : 0;
        $img = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
      ?>
      <div class="pw-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, <?= $hasDiscount ? $oldPrice : 0; ?>)">
        <?php if($hasDiscount && $discountPct > 0): ?>
        <span class="pw-product-badge">-<?= $discountPct; ?>%</span>
        <?php endif; ?>
        <button class="pw-product-wishlist" onclick="event.stopPropagation();" aria-label="Wishlist"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></button>
        <div class="pw-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
          <?php else: ?>
          <div class="pw-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="pw-product-body">
          <?php if(!empty($p->category_name)): ?>
          <div class="pw-product-brand"><?= htmlspecialchars($p->category_name); ?></div>
          <?php endif; ?>
          <div class="pw-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="pw-product-footer">
            <div class="pw-product-price"><?= sf_currency($price, $cur); ?><?php if($hasDiscount): ?><span class="old"><?= sf_currency($oldPrice, $cur); ?></span><?php endif; ?></div>
            <div class="pw-card-actions">
              <button class="pw-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add to cart"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="pw-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $p->id; ?>,'<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',<?= $p->stock; ?>)" aria-label="Order via WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
            </div>
          </div>
          <?php if($p->stock <= 0 && !($settings->allow_backorder ?? false)): ?>
          <div class="pw-product-stock">Out of Stock</div>
          <?php else: ?>
          <div class="pw-product-stock in">In Stock</div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <?php if($total_pages > 1): ?>
    <div class="pw-pagination">
      <?php if($page > 1): ?>
        <a class="pw-page-btn" href="<?= base_url('store/' . $slug . '/products'); ?>?page=<?= $page-1; ?><?= !empty($category_id) ? '&category=' . $category_id : ''; ?><?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>">&laquo;</a>
      <?php endif; ?>
      <?php for($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
        <a class="pw-page-btn <?= $i == $page ? 'active' : ''; ?>" href="<?= base_url('store/' . $slug . '/products'); ?>?page=<?= $i; ?><?= !empty($category_id) ? '&category=' . $category_id : ''; ?><?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>"><?= $i; ?></a>
      <?php endfor; ?>
      <?php if($page < $total_pages): ?>
        <a class="pw-page-btn" href="<?= base_url('store/' . $slug . '/products'); ?>?page=<?= $page+1; ?><?= !empty($category_id) ? '&category=' . $category_id : ''; ?><?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>">&raquo;</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="pw-empty">
      <div class="pw-empty-icon"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg></div>
      <div class="pw-empty-title">No wellness products found</div>
      <p class="pw-empty-text">Try a different search or browse all products.</p>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pw-btn-sage">View All Products</a>
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
  const storeName = '<?= htmlspecialchars(addslashes($store->store_name ?? 'Wellness Pharmacy')); ?>';
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
