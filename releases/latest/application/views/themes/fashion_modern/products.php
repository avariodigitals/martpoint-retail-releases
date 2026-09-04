<?php
/**
 * Modern Minimal — Products Listing
 * Shopify-style product grid with filter chips, search and pagination.
 */
$slug = $settings->store_slug ?? '';
$cur = $store_currency ?? null;
?>
<style>
  .theme-fashion_modern .mp-footer-space { height:0; }
  .fm-container { max-width:1400px; margin:0 auto; padding:0 24px; }
  @media(max-width:767px){ .fm-container { padding:0 16px; } }

  .fm-breadcrumb { padding:20px 0 0; font-size:13px; color:#64748B; }
  .fm-breadcrumb a { color:#64748B; text-decoration:none; }
  .fm-breadcrumb a:hover { color:#0F172A; }
  .fm-breadcrumb .sep { margin:0 8px; color:#CBD5E1; }

  .fm-page { padding:32px 0 64px; }
  .fm-page-head { margin-bottom:28px; }
  .fm-section-label { font-size:12px; text-transform:uppercase; letter-spacing:0.12em; color:#6366F1; font-weight:700; margin-bottom:6px; }
  .fm-section-title { font-family:'Playfair Display',serif; font-size:clamp(28px,3.5vw,40px); margin:0; font-weight:700; letter-spacing:-0.02em; color:#0F172A; }

  .fm-filters { display:flex; gap:12px; align-items:center; margin-bottom:32px; flex-wrap:wrap; }
  .fm-search-bar { flex:1; max-width:420px; position:relative; }
  .fm-search-bar input { width:100%; padding:12px 14px 12px 42px; border:1.5px solid #E2E8F0; border-radius:999px; font-size:14px; background:#F8FAFC; outline:none; transition:border-color .2s; }
  .fm-search-bar input:focus { border-color:#0F172A; background:#fff; }
  .fm-search-icon { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#94A3B8; }
  .fm-filter-chip { padding:10px 18px; border-radius:999px; border:1px solid #E2E8F0; background:#fff; font-size:13px; font-weight:600; color:#64748B; cursor:pointer; transition:all .2s; text-decoration:none; display:inline-block; }
  .fm-filter-chip.active, .fm-filter-chip:hover { background:#0F172A; color:#fff; border-color:#0F172A; }

  .fm-product-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:24px; }
  @media(max-width:1023px){ .fm-product-grid { grid-template-columns:repeat(3,1fr); } }
  @media(max-width:767px){ .fm-product-grid { grid-template-columns:repeat(2,1fr); gap:14px; } }
  .fm-product-card { position:relative; background:#fff; border-radius:16px; overflow:hidden; border:1px solid #E2E8F0; transition:transform .2s, box-shadow .2s; cursor:pointer; }
  .fm-product-card:hover { transform:translateY(-4px); box-shadow:0 16px 40px rgba(15,23,42,0.1); }
  .fm-product-wishlist { position:absolute; top:14px; right:14px; width:36px; height:36px; border-radius:50%; background:#fff; display:flex; align-items:center; justify-content:center; z-index:2; transition:color .2s, transform .2s; box-shadow:0 2px 8px rgba(0,0,0,0.06); border:none; cursor:pointer; }
  .fm-product-wishlist:hover { color:#6366F1; transform:scale(1.1); }
  .fm-product-badge { position:absolute; top:14px; left:14px; background:#6366F1; color:#fff; font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:0.06em; padding:5px 10px; border-radius:999px; z-index:2; }
  .fm-product-media { aspect-ratio:4/5; overflow:hidden; background:#F8FAFC; }
  .fm-product-media img { width:100%; height:100%; object-fit:cover; transition:transform .45s ease; }
  .fm-product-card:hover .fm-product-media img { transform:scale(1.06); }
  .fm-product-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:#F8FAFC; color:#CBD5E1; }
  .fm-product-placeholder span { font-family:'Playfair Display',serif; font-size:36px; font-weight:700; }
  .fm-product-body { padding:18px; }
  .fm-product-brand { font-size:11px; text-transform:uppercase; letter-spacing:0.08em; color:#94A3B8; font-weight:600; margin-bottom:4px; }
  .fm-product-name { font-size:14px; font-weight:600; margin-bottom:10px; line-height:1.35; display:-webkit-box; -webkit-line-clamp:2; line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; min-height:38px; color:#0F172A; }
  .fm-product-footer { display:flex; flex-direction:column; gap:8px; }
  .fm-product-price { font-size:17px; font-weight:700; color:#0F172A; }
  .fm-product-price .old { font-size:13px; color:#94A3B8; text-decoration:line-through; margin-left:6px; font-weight:500; }
  .fm-card-actions { display:flex; gap:8px; }
  .fm-add-btn { flex:1; padding:11px 14px; border-radius:999px; background:#0F172A; color:#fff; display:flex; align-items:center; justify-content:center; gap:6px; transition:background .2s, transform .15s; border:none; cursor:pointer; font-size:13px; font-weight:600; }
  .fm-add-btn:hover { background:#6366F1; }
  .fm-add-btn:active { transform:scale(0.97); }
  .fm-product-stock { font-size:11px; color:#EF4444; font-weight:600; margin-top:6px; text-transform:uppercase; letter-spacing:0.04em; }

  .fm-pagination { display:flex; justify-content:center; gap:8px; margin-top:40px; }
  .fm-page-btn { padding:8px 14px; border-radius:999px; border:1px solid #E2E8F0; background:#fff; font-size:14px; font-weight:600; color:#64748B; text-decoration:none; transition:all .2s; }
  .fm-page-btn:hover { border-color:#0F172A; color:#0F172A; }
  .fm-page-btn.active { background:#0F172A; color:#fff; border-color:#0F172A; }

  .fm-empty { text-align:center; padding:80px 20px; color:#64748B; }
  .fm-empty-icon { font-size:48px; margin-bottom:12px; color:#CBD5E1; }
  .fm-empty-title { font-size:18px; font-weight:700; color:#0F172A; margin-bottom:8px; }
  .fm-empty-text { margin-bottom:20px; }
  .fm-btn-dark { display:inline-flex; padding:14px 32px; border-radius:999px; background:#0F172A; color:#fff; font-weight:600; font-size:14px; text-decoration:none; }

  .fm-wa-btn { flex:1; padding:11px 14px; border-radius:999px; background:#25D366; color:#fff; display:flex; align-items:center; justify-content:center; gap:6px; transition:background .2s, transform .15s; border:none; cursor:pointer; font-size:13px; font-weight:600; }
  .fm-wa-btn:hover { background:#1FB855; }
  .fm-wa-btn:active { transform:scale(0.97); }

  .wa-order-modal { display:none; position:fixed; inset:0; z-index:9999; }
  .wa-order-modal.open { display:block; }
  .wa-order-modal-overlay { position:absolute; inset:0; background:rgba(15,23,42,0.5); }
  .wa-order-modal-card { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:460px; max-width:calc(100vw - 32px); max-height:90vh; overflow-y:auto; background:#fff; border-radius:16px; box-shadow:0 24px 60px rgba(0,0,0,0.2); }
  .wa-order-modal-header { display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid #E2E8F0; }
  .wa-order-modal-title { font-family:'Playfair Display',serif; font-size:20px; font-weight:700; color:#0F172A; margin:0; }
  .wa-order-modal-close { width:32px; height:32px; border-radius:50%; border:none; background:#F1F5F9; color:#0F172A; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background .2s; }
  .wa-order-modal-close:hover { background:#E2E8F0; }
  .wa-order-modal-body { padding:24px; }
  .wa-order-modal-product { display:flex; gap:14px; align-items:center; padding:14px; background:#F8FAFC; border-radius:12px; margin-bottom:20px; }
  .wa-order-modal-product img { width:56px; height:56px; border-radius:12px; object-fit:cover; flex-shrink:0; }
  .wa-order-modal-product-info { flex:1; min-width:0; }
  .wa-order-modal-product-name { font-size:14px; font-weight:600; color:#0F172A; margin:0 0 4px; line-height:1.3; }
  .wa-order-modal-product-price { font-size:15px; font-weight:700; color:#4F46E5; }
  .wa-order-modal-fields { display:flex; flex-direction:column; gap:14px; }
  .wa-order-modal-fields label { font-size:12px; font-weight:600; color:#0F172A; margin-bottom:4px; display:block; }
  .wa-order-modal-fields input, .wa-order-modal-fields textarea { width:100%; padding:12px 14px; border:1.5px solid #E2E8F0; border-radius:12px; font-size:14px; background:#F8FAFC; outline:none; transition:border-color .2s; box-sizing:border-box; }
  .wa-order-modal-fields input:focus, .wa-order-modal-fields textarea:focus { border-color:#4F46E5; background:#fff; }
  .wa-order-modal-fields textarea { resize:vertical; min-height:64px; }
  .wa-order-modal-actions { display:flex; gap:10px; margin-top:20px; }
  .wa-order-modal-send { flex:1; padding:14px; border-radius:12px; border:none; background:#25D366; color:#fff; font-size:14px; font-weight:600; cursor:pointer; transition:background .2s; display:flex; align-items:center; justify-content:center; gap:8px; }
  .wa-order-modal-send:hover { background:#1FB855; }
  .wa-order-modal-cancel { padding:14px 20px; border-radius:12px; border:1.5px solid #E2E8F0; background:#fff; color:#0F172A; font-size:14px; font-weight:600; cursor:pointer; transition:background .2s; }
  .wa-order-modal-cancel:hover { background:#F8FAFC; }
</style>

<div class="fm-container">
  <div class="fm-breadcrumb">
    <a href="<?= base_url('store/' . $slug); ?>">Home</a>
    <span class="sep">/</span>
    <span>Shop</span>
    <?php if(!empty($search)): ?>
    <span class="sep">/</span><span>"<?= htmlspecialchars($search); ?>"</span>
    <?php endif; ?>
  </div>

  <div class="fm-page">
    <div class="fm-page-head">
      <div class="fm-section-label">Shop</div>
      <h1 class="fm-section-title"><?= !empty($search) ? 'Search: ' . htmlspecialchars($search) : 'All Products'; ?></h1>
    </div>

    <div class="fm-filters">
      <div class="fm-search-bar">
        <span class="fm-search-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
        <input type="text" placeholder="Search products..." value="<?= htmlspecialchars($search ?? ''); ?>" onkeydown="if(event.key==='Enter'){const u=new URL(location.href);u.searchParams.set('search',this.value);u.searchParams.delete('page');location.href=u.href;}">
      </div>
      <?php if(!empty($categories)): ?>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="fm-filter-chip <?= empty($category_id) ? 'active' : ''; ?>">All</a>
      <?php foreach($categories as $cat): ?>
      <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="fm-filter-chip <?= ($category_id ?? 0) == $cat->id ? 'active' : ''; ?>"><?= htmlspecialchars($cat->category_name); ?></a>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <?php if(!empty($products)): ?>
    <div class="fm-product-grid">
      <?php foreach($products as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $oldPrice = $p->original_price ?? $p->sales_price;
        $hasDiscount = $oldPrice > $price;
        $discountPct = $hasDiscount ? round((($oldPrice - $price) / $oldPrice) * 100) : 0;
        $img = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
      ?>
      <div class="fm-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, <?= $hasDiscount ? $oldPrice : 0; ?>)">
        <?php if($hasDiscount && $discountPct > 0): ?>
        <span class="fm-product-badge">-<?= $discountPct; ?>%</span>
        <?php endif; ?>
        <button class="fm-product-wishlist" onclick="event.stopPropagation();"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></button>
        <div class="fm-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
          <?php else: ?>
          <div class="fm-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="fm-product-body">
          <?php if(!empty($p->category_name)): ?>
          <div class="fm-product-brand"><?= htmlspecialchars($p->category_name); ?></div>
          <?php endif; ?>
          <div class="fm-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="fm-product-footer">
            <div class="fm-product-price"><?= sf_currency($price, $cur); ?><?php if($hasDiscount): ?><span class="old"><?= sf_currency($oldPrice, $cur); ?></span><?php endif; ?></div>
            <div class="fm-card-actions">
              <button class="fm-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add to cart"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="fm-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $p->id; ?>,'<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',<?= $p->stock; ?>)" aria-label="Order via WhatsApp"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
            </div>
          </div>
          <?php if($p->stock <= 0 && !($settings->allow_backorder ?? false)): ?>
          <div class="fm-product-stock">Out of Stock</div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <?php if($total_pages > 1): ?>
    <div class="fm-pagination">
      <?php if($page > 1): ?>
        <a class="fm-page-btn" href="<?= base_url('store/' . $slug . '/products'); ?>?page=<?= $page-1; ?><?= !empty($category_id) ? '&category=' . $category_id : ''; ?><?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>">&laquo;</a>
      <?php endif; ?>
      <?php for($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
        <a class="fm-page-btn <?= $i == $page ? 'active' : ''; ?>" href="<?= base_url('store/' . $slug . '/products'); ?>?page=<?= $i; ?><?= !empty($category_id) ? '&category=' . $category_id : ''; ?><?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>"><?= $i; ?></a>
      <?php endfor; ?>
      <?php if($page < $total_pages): ?>
        <a class="fm-page-btn" href="<?= base_url('store/' . $slug . '/products'); ?>?page=<?= $page+1; ?><?= !empty($category_id) ? '&category=' . $category_id : ''; ?><?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>">&raquo;</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="fm-empty">
      <div class="fm-empty-icon"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg></div>
      <div class="fm-empty-title">No products found</div>
      <p class="fm-empty-text">Try a different search or browse all products.</p>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="fm-btn-dark">View All Products</a>
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
  const storeName = '<?= htmlspecialchars(addslashes($store->store_name ?? 'Store')); ?>';
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
