<?php
/**
 * Fashion Luxe — Products Listing
 * Serif-driven product grid with filter chips, search and pagination
 * in an elegant gold-accented design.
 */
$slug = $settings->store_slug ?? '';
$cur = $store_currency ?? null;
?>
<style>
  .theme-fashion_luxe .mp-topbar,
  .theme-fashion_luxe .mp-announcement,
  .theme-fashion_luxe .mp-nav,
  .theme-fashion_luxe .mp-header,
  .theme-fashion_luxe .mp-mobile-menu-btn,
  .theme-fashion_luxe .mp-footer-space { display:none; height:0; }

  :root {
    --fl-ink:#1A1A1A;
    --fl-warm:#8B6D4B;
    --fl-gold:#C9A961;
    --fl-ivory:#F9F7F2;
    --fl-soft:#E8E4DC;
  }

  .fl-container { max-width:1400px; margin:0 auto; padding:0 24px; }
  @media(max-width:767px){ .fl-container { padding:0 16px; } }

  .fl-breadcrumb { padding:24px 0 0; font-family:'Lora',serif; font-size:13px; color:#8B6D4B; }
  .fl-breadcrumb a { color:#8B6D4B; text-decoration:none; }
  .fl-breadcrumb a:hover { color:var(--fl-ink); }
  .fl-breadcrumb .sep { margin:0 8px; color:#C9A961; }

  .fl-page { padding:36px 0 72px; }
  .fl-page-head { margin-bottom:32px; }
  .fl-section-label { font-family:'Lora',serif; font-size:11px; text-transform:uppercase; letter-spacing:0.18em; color:var(--fl-gold); font-weight:600; margin-bottom:8px; }
  .fl-section-title { font-family:'Playfair Display',serif; font-style:italic; font-size:32px; margin:0; font-weight:700; color:var(--fl-ink); }

  .fl-filters { display:flex; gap:12px; align-items:center; margin-bottom:36px; flex-wrap:wrap; }
  .fl-search-bar { flex:1; max-width:420px; position:relative; }
  .fl-search-bar input { width:100%; padding:12px 14px 12px 42px; border:1px solid var(--fl-soft); border-radius:4px; font-family:'Lora',serif; font-size:14px; background:var(--fl-ivory); outline:none; transition:border-color .2s; color:var(--fl-ink); }
  .fl-search-bar input:focus { border-color:var(--fl-gold); background:#fff; }
  .fl-search-icon { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:#8B6D4B; }
  .fl-filter-chip { padding:10px 20px; border-radius:2px; border:1px solid var(--fl-soft); background:#fff; font-family:'Lora',serif; font-size:13px; font-weight:600; color:#8B6D4B; cursor:pointer; transition:all .2s; text-decoration:none; display:inline-block; }
  .fl-filter-chip.active, .fl-filter-chip:hover { background:var(--fl-ink); color:#fff; border-color:var(--fl-ink); }

  .fl-product-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:24px; }
  @media(max-width:1023px){ .fl-product-grid { grid-template-columns:repeat(3,1fr); } }
  @media(max-width:767px){ .fl-product-grid { grid-template-columns:repeat(2,1fr); gap:14px; } }
  .fl-product-card { position:relative; background:#fff; border-radius:4px; overflow:hidden; border:1px solid var(--fl-soft); transition:transform .25s, box-shadow .25s; cursor:pointer; }
  .fl-product-card:hover { transform:translateY(-4px); box-shadow:0 12px 36px rgba(61,40,23,0.12); }
  .fl-product-wishlist { position:absolute; top:14px; right:14px; width:36px; height:36px; border-radius:4px; background:#fff; display:flex; align-items:center; justify-content:center; z-index:2; transition:color .2s, transform .2s; box-shadow:0 2px 8px rgba(0,0,0,0.06); border:none; cursor:pointer; }
  .fl-product-wishlist:hover { color:var(--fl-gold); transform:scale(1.08); }
  .fl-product-badge { position:absolute; top:14px; left:14px; background:var(--fl-gold); color:var(--fl-ink); font-family:'Lora',serif; font-size:10px; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; padding:5px 10px; border-radius:2px; z-index:2; }
  .fl-product-media { aspect-ratio:4/5; overflow:hidden; background:var(--fl-soft); }
  .fl-product-media img { width:100%; height:100%; object-fit:cover; transition:transform .5s ease; }
  .fl-product-card:hover .fl-product-media img { transform:scale(1.06); }
  .fl-product-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--fl-soft); color:var(--fl-gold); }
  .fl-product-placeholder span { font-family:'Playfair Display',serif; font-style:italic; font-size:38px; font-weight:700; }
  .fl-product-body { padding:20px; }
  .fl-product-brand { font-family:'Lora',serif; font-size:11px; text-transform:uppercase; letter-spacing:0.1em; color:#8B6D4B; font-weight:600; margin-bottom:5px; }
  .fl-product-name { font-family:'Lora',serif; font-size:14px; font-weight:600; margin-bottom:12px; line-height:1.4; display:-webkit-box; -webkit-line-clamp:2; line-clamp:2; -webkit-box-orient:vertical; overflow:hidden; min-height:40px; color:var(--fl-ink); }
  .fl-product-footer { display:flex; flex-direction:column; gap:8px; }
  .fl-product-price { font-family:'Playfair Display',serif; font-size:18px; font-weight:700; color:var(--fl-ink); }
  .fl-product-price .old { font-family:'Lora',serif; font-size:13px; color:#8B6D4B; text-decoration:line-through; margin-left:6px; font-weight:500; }
  .fl-card-actions { display:flex; gap:8px; }
  .fl-add-btn { flex:1; padding:11px 14px; border-radius:2px; background:var(--fl-ink); color:#fff; display:flex; align-items:center; justify-content:center; gap:6px; transition:background .2s, transform .15s; border:none; cursor:pointer; font-family:'Lora',serif; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; }
  .fl-add-btn:hover { background:var(--fl-gold); color:var(--fl-ink); }
  .fl-add-btn:active { transform:scale(0.97); }
  .fl-product-stock { font-family:'Lora',serif; font-size:11px; color:#B23A3A; font-weight:600; margin-top:8px; text-transform:uppercase; letter-spacing:0.06em; }

  .fl-pagination { display:flex; justify-content:center; gap:8px; margin-top:44px; }
  .fl-page-btn { padding:8px 16px; border-radius:2px; border:1px solid var(--fl-soft); background:#fff; font-family:'Lora',serif; font-size:14px; font-weight:600; color:#8B6D4B; text-decoration:none; transition:all .2s; }
  .fl-page-btn:hover { border-color:var(--fl-ink); color:var(--fl-ink); }
  .fl-page-btn.active { background:var(--fl-ink); color:#fff; border-color:var(--fl-ink); }

  .fl-empty { text-align:center; padding:88px 20px; color:#8B6D4B; }
  .fl-empty-icon { font-size:48px; margin-bottom:14px; color:#C9A961; }
  .fl-empty-title { font-family:'Playfair Display',serif; font-style:italic; font-size:20px; font-weight:700; color:var(--fl-ink); margin-bottom:10px; }
  .fl-empty-text { font-family:'Lora',serif; margin-bottom:22px; }
  .fl-btn-ink { display:inline-flex; padding:14px 34px; border-radius:2px; background:var(--fl-ink); color:#fff; font-family:'Lora',serif; font-weight:600; font-size:13px; text-transform:uppercase; letter-spacing:0.08em; text-decoration:none; }

  .fl-wa-btn { flex:1; padding:11px 14px; border-radius:2px; background:#25D366; color:#fff; display:flex; align-items:center; justify-content:center; gap:6px; transition:background .2s, transform .15s; border:none; cursor:pointer; font-family:'Lora',serif; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; }
  .fl-wa-btn:hover { background:#1FB855; }
  .fl-wa-btn:active { transform:scale(0.97); }

  .wa-order-modal { display:none; position:fixed; inset:0; z-index:9999; }
  .wa-order-modal.open { display:block; }
  .wa-order-modal-overlay { position:absolute; inset:0; background:rgba(61,40,23,0.5); }
  .wa-order-modal-card { position:absolute; top:50%; left:50%; transform:translate(-50%,-50%); width:440px; max-width:calc(100vw - 32px); max-height:90vh; overflow-y:auto; background:#fff; border-radius:4px; box-shadow:0 24px 60px rgba(61,40,23,0.25); border:1px solid var(--fl-soft); }
  .wa-order-modal-header { display:flex; align-items:center; justify-content:space-between; padding:20px 24px; border-bottom:1px solid var(--fl-soft); }
  .wa-order-modal-title { font-family:'Playfair Display',serif; font-style:italic; font-size:20px; font-weight:700; color:var(--fl-ink); margin:0; }
  .wa-order-modal-close { width:32px; height:32px; border-radius:2px; border:none; background:var(--fl-soft); color:var(--fl-ink); cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background .2s; }
  .wa-order-modal-close:hover { background:#F9F7F2; }
  .wa-order-modal-body { padding:24px; }
  .wa-order-modal-product { display:flex; gap:14px; align-items:center; padding:14px; background:var(--fl-ivory); border-radius:4px; margin-bottom:20px; }
  .wa-order-modal-product img { width:56px; height:56px; border-radius:4px; object-fit:cover; flex-shrink:0; }
  .wa-order-modal-product-info { flex:1; min-width:0; }
  .wa-order-modal-product-name { font-family:'Lora',serif; font-size:14px; font-weight:600; color:var(--fl-ink); margin:0 0 4px; line-height:1.3; }
  .wa-order-modal-product-price { font-family:'Playfair Display',serif; font-size:16px; font-weight:700; color:var(--fl-gold); }
  .wa-order-modal-fields { display:flex; flex-direction:column; gap:14px; }
  .wa-order-modal-fields label { font-family:'Lora',serif; font-size:11px; text-transform:uppercase; letter-spacing:0.1em; font-weight:600; color:var(--fl-ink); margin-bottom:5px; display:block; }
  .wa-order-modal-fields input, .wa-order-modal-fields textarea { width:100%; padding:12px 14px; border:1px solid var(--fl-soft); border-radius:4px; font-family:'Lora',serif; font-size:14px; background:var(--fl-ivory); outline:none; transition:border-color .2s; color:var(--fl-ink); box-sizing:border-box; }
  .wa-order-modal-fields input:focus, .wa-order-modal-fields textarea:focus { border-color:var(--fl-gold); background:#fff; }
  .wa-order-modal-fields textarea { resize:vertical; min-height:64px; }
  .wa-order-modal-actions { display:flex; gap:10px; margin-top:20px; }
  .wa-order-modal-send { flex:1; padding:14px; border-radius:4px; border:none; background:#25D366; color:#fff; font-family:'Lora',serif; font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; cursor:pointer; transition:background .2s; display:flex; align-items:center; justify-content:center; gap:8px; }
  .wa-order-modal-send:hover { background:#1FB855; }
  .wa-order-modal-cancel { padding:14px 20px; border-radius:4px; border:1px solid var(--fl-soft); background:#fff; color:var(--fl-ink); font-family:'Lora',serif; font-size:13px; font-weight:600; text-transform:uppercase; letter-spacing:0.06em; cursor:pointer; transition:background .2s; }
  .wa-order-modal-cancel:hover { background:var(--fl-soft); }
</style>

<div class="fl-container">
  <div class="fl-breadcrumb">
    <a href="<?= base_url('store/' . $slug); ?>">Home</a>
    <span class="sep">/</span>
    <span>Fashion</span>
    <?php if(!empty($search)): ?>
    <span class="sep">/</span><span>"<?= htmlspecialchars($search); ?>"</span>
    <?php endif; ?>
  </div>

  <div class="fl-page">
    <div class="fl-page-head">
      <div class="fl-section-label">Fashion</div>
      <h1 class="fl-section-title"><?= !empty($search) ? 'Search: ' . htmlspecialchars($search) : 'All Products'; ?></h1>
    </div>

    <div class="fl-filters">
      <div class="fl-search-bar">
        <span class="fl-search-icon"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
        <input type="text" placeholder="Search products..." value="<?= htmlspecialchars($search ?? ''); ?>" onkeydown="if(event.key==='Enter'){const u=new URL(location.href);u.searchParams.set('search',this.value);u.searchParams.delete('page');location.href=u.href;}">
      </div>
      <?php if(!empty($categories)): ?>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="fl-filter-chip <?= empty($category_id) ? 'active' : ''; ?>">All</a>
      <?php foreach($categories as $cat): ?>
      <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="fl-filter-chip <?= ($category_id ?? 0) == $cat->id ? 'active' : ''; ?>"><?= htmlspecialchars($cat->category_name); ?></a>
      <?php endforeach; ?>
      <?php endif; ?>
    </div>

    <?php if(!empty($products)): ?>
    <div class="fl-product-grid">
      <?php foreach($products as $p):
        $price = $p->effective_price ?? $p->sales_price;
        $oldPrice = $p->original_price ?? $p->sales_price;
        $hasDiscount = $oldPrice > $price;
        $discountPct = $hasDiscount ? round((($oldPrice - $price) / $oldPrice) * 100) : 0;
        $img = ($p->item_image && file_exists($p->item_image)) ? base_url($p->item_image) : '';
      ?>
      <div class="fl-product-card" onclick="openProductModal(<?= $p->id; ?>, '<?= htmlspecialchars(addslashes($p->item_name)); ?>', <?= $price; ?>, '<?= $p->item_image; ?>', '<?= htmlspecialchars(addslashes($p->description ?? '')); ?>', <?= $p->stock; ?>, <?= $hasDiscount ? $oldPrice : 0; ?>)">
        <?php if($hasDiscount && $discountPct > 0): ?>
        <span class="fl-product-badge">-<?= $discountPct; ?>%</span>
        <?php endif; ?>
        <button class="fl-product-wishlist" onclick="event.stopPropagation();"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg></button>
        <div class="fl-product-media">
          <?php if($img): ?>
          <img src="<?= $img; ?>" alt="<?= htmlspecialchars($p->item_name); ?>" loading="lazy">
          <?php else: ?>
          <div class="fl-product-placeholder"><span><?= htmlspecialchars(substr($p->item_name, 0, 1)); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="fl-product-body">
          <?php if(!empty($p->category_name)): ?>
          <div class="fl-product-brand"><?= htmlspecialchars($p->category_name); ?></div>
          <?php endif; ?>
          <div class="fl-product-name"><?= htmlspecialchars($p->item_name); ?></div>
          <div class="fl-product-footer">
            <div class="fl-product-price"><?= sf_currency($price, $cur); ?><?php if($hasDiscount): ?><span class="old"><?= sf_currency($oldPrice, $cur); ?></span><?php endif; ?></div>
            <div class="fl-card-actions">
              <button class="fl-add-btn" onclick="event.stopPropagation();addToCart(<?= $p->id; ?>,'product','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)" aria-label="Add to cart"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Add</button>
              <button class="fl-wa-btn" onclick="event.stopPropagation();openWhatsAppOrderModal(<?= $p->id; ?>,'<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',<?= $p->stock; ?>)" aria-label="Order via WhatsApp"><svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347"/></svg> WhatsApp</button>
            </div>
          </div>
          <?php if($p->stock <= 0 && !($settings->allow_backorder ?? false)): ?>
          <div class="fl-product-stock">Out of Stock</div>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <?php if($total_pages > 1): ?>
    <div class="fl-pagination">
      <?php if($page > 1): ?>
        <a class="fl-page-btn" href="<?= base_url('store/' . $slug . '/products'); ?>?page=<?= $page-1; ?><?= !empty($category_id) ? '&category=' . $category_id : ''; ?><?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>">&laquo;</a>
      <?php endif; ?>
      <?php for($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
        <a class="fl-page-btn <?= $i == $page ? 'active' : ''; ?>" href="<?= base_url('store/' . $slug . '/products'); ?>?page=<?= $i; ?><?= !empty($category_id) ? '&category=' . $category_id : ''; ?><?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>"><?= $i; ?></a>
      <?php endfor; ?>
      <?php if($page < $total_pages): ?>
        <a class="fl-page-btn" href="<?= base_url('store/' . $slug . '/products'); ?>?page=<?= $page+1; ?><?= !empty($category_id) ? '&category=' . $category_id : ''; ?><?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>">&raquo;</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php else: ?>
    <div class="fl-empty">
      <div class="fl-empty-icon"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg></div>
      <div class="fl-empty-title">No products found</div>
      <p class="fl-empty-text">Try a different search or browse all products.</p>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="fl-btn-ink">View All Products</a>
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
<style>
  /* Fashion Luxe premium overrides */
  .fl-product-card { background:#fff; border:1px solid var(--fl-soft); border-radius:2px; }
  .fl-product-card:hover { transform:translateY(-6px); box-shadow:0 20px 50px rgba(26,26,26,0.12); }
  .fl-product-body { padding:24px; }
  .fl-product-media { aspect-ratio:4/5; }
  .fl-btn-ink:hover { background:var(--fl-gold); color:var(--fl-ink); }
  .flh-header { background:var(--fl-ivory); border-bottom:1px solid var(--fl-soft); }
  .flh-header-inner { padding:22px 24px; }
  .flh-logo img { max-height:52px; max-width:220px; }
  .flh-logo-text { font-size:30px; }
  .flh-nav-link:hover { color:var(--fl-gold); }
  .flh-icon-btn:hover { color:var(--fl-gold); background:var(--fl-soft); }
</style>
