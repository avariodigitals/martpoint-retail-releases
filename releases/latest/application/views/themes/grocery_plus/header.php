<?php
/**
 * Grocery Plus — Premium Supermarket Header
 * Large-format grocery chain with deep emerald and warm gold accents.
 * Inter + Lora editorial feel, refined spacing, premium supermarket experience.
 */
$slug = $settings->store_slug ?? '';
$logo = $logo_url ?? null;
$social = $social_links ?? [];
?>
<!-- Inter pairs with the theme's Lora heading font -->
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
  :root {
    --gp-emerald:#047857;
    --gp-emerald-dark:#065F46;
    --gp-emerald-deep:#064E3B;
    --gp-gold:#B45309;
    --gp-gold-light:#F59E0B;
    --gp-cream:#F5F5F0;
    --gp-soft:#ECFDF5;
    --gp-dark:#1F2937;
    --gp-gray:#6B7280;
    --gp-border:#E5E7EB;
    --gp-white:#FFFFFF;
  }

  .theme-grocery_plus .mp-topbar,
  .theme-grocery_plus .mp-announcement,
  .theme-grocery_plus .mp-nav,
  .theme-grocery_plus .mp-header { display:none !important; }
  .theme-grocery_plus .mp-mobile-menu-btn { display:none !important; }
  .theme-grocery_plus .mp-footer-space { height:0; }

  .gp-announce { background:var(--gp-emerald-deep); color:#fff; text-align:center; padding:10px 16px; font-family:'Inter',sans-serif; font-size:13px; font-weight:500; letter-spacing:0.02em; }
  .gp-announce a { color:#D1FAE5; text-decoration:underline; }
  .gp-announce strong { color:#FCD34D; font-weight:600; }

  .gp-header { background:#fff; border-bottom:1px solid var(--gp-border); position:sticky; top:0; z-index:100; }
  .gp-header-inner { max-width:1400px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; gap:24px; padding:14px 24px; }
  .gp-nav-left { display:flex; align-items:center; gap:26px; flex:1; }
  .gp-nav-link { font-family:'Inter',sans-serif; font-size:14px; font-weight:600; color:var(--gp-dark); text-decoration:none; transition:color .2s; }
  .gp-nav-link:hover { color:var(--gp-emerald); }
  .gp-shop-menu { position:relative; }
  .gp-shop-menu > .gp-nav-link { display:flex; align-items:center; gap:6px; }
  .gp-shop-drop { position:absolute; top:calc(100% + 12px); left:0; background:#fff; border:1px solid var(--gp-border); border-radius:14px; box-shadow:0 20px 48px rgba(6,78,59,0.14); padding:10px; min-width:230px; display:none; z-index:150; }
  .gp-shop-menu:hover .gp-shop-drop, .gp-shop-menu:focus-within .gp-shop-drop { display:block; }
  .gp-shop-drop a { display:flex; align-items:center; justify-content:space-between; padding:10px 14px; font-family:'Inter',sans-serif; font-size:14px; font-weight:600; color:var(--gp-dark); text-decoration:none; border-radius:8px; }
  .gp-shop-drop a:hover { background:var(--gp-cream); color:var(--gp-emerald); }
  .gp-shop-drop a .cnt { font-size:11px; color:var(--gp-gray); font-weight:500; }
  .gp-shop-drop .all { border-top:1px solid var(--gp-border); margin-top:6px; padding-top:12px; color:var(--gp-emerald); }
  .gp-logo { text-decoration:none; display:flex; align-items:center; gap:10px; flex-shrink:0; }
  .gp-logo img { max-height:42px; max-width:180px; object-fit:contain; }
  .gp-logo-text { font-family:'Lora',serif; font-size:24px; font-weight:700; color:var(--gp-emerald); letter-spacing:-0.01em; }
  .gp-logo-tag { font-family:'Inter',sans-serif; font-size:9px; color:var(--gp-gold); letter-spacing:0.14em; text-transform:uppercase; font-weight:700; }
  .gp-actions { display:flex; align-items:center; gap:8px; flex:1; justify-content:flex-end; }
  .gp-search { position:relative; max-width:280px; width:100%; }
  .gp-search input { width:100%; padding:11px 14px 11px 40px; border:1px solid var(--gp-border); border-radius:999px; font-family:'Inter',sans-serif; font-size:14px; outline:none; background:var(--gp-cream); transition:border-color .2s, background .2s; color:var(--gp-dark); }
  .gp-search input:focus { border-color:var(--gp-emerald); background:#fff; }
  .gp-search-icon { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--gp-emerald); }
  .gp-icon-btn { width:44px; height:44px; display:flex; align-items:center; justify-content:center; transition:background .2s; position:relative; text-decoration:none; color:var(--gp-dark); border-radius:999px; }
  .gp-icon-btn:hover { background:var(--gp-cream); }
  .gp-icon-btn svg { width:22px; height:22px; stroke:currentColor; stroke-width:1.7; fill:none; stroke-linecap:round; stroke-linejoin:round; }
  .gp-cart-count { position:absolute; top:2px; right:2px; background:var(--gp-gold); color:#fff; font-size:10px; font-weight:700; width:18px; height:18px; border-radius:50%; display:flex; align-items:center; justify-content:center; }
  .gp-fresh-badge { display:flex; align-items:center; gap:6px; background:var(--gp-soft); border:1px solid #A7F3D0; border-radius:999px; padding:6px 14px; font-family:'Inter',sans-serif; font-size:12px; font-weight:600; color:var(--gp-emerald-dark); white-space:nowrap; }
  .gp-fresh-badge svg { width:15px; height:15px; color:var(--gp-gold); }

  .gp-mobile-drawer { display:none; position:fixed; top:0; left:0; bottom:0; width:300px; background:#fff; z-index:901; padding:24px; box-shadow:4px 0 24px rgba(0,0,0,0.1); overflow-y:auto; }
  .gp-mobile-drawer.open { display:block; }
  .gp-mobile-overlay { display:none; position:fixed; inset:0; background:rgba(6,78,59,0.35); z-index:900; }
  .gp-mobile-overlay.open { display:block; }
  .gp-drawer-title { font-family:'Lora',serif; font-size:22px; font-weight:700; margin-bottom:24px; color:var(--gp-emerald); }
  .gp-drawer-link { display:block; padding:14px 0; font-family:'Inter',sans-serif; font-size:15px; font-weight:600; color:var(--gp-dark); text-decoration:none; border-bottom:1px solid var(--gp-border); }
  .gp-drawer-link:hover { color:var(--gp-emerald); }
  .gp-drawer-section { margin-top:24px; }
  .gp-drawer-section-title { font-family:'Inter',sans-serif; font-size:10px; text-transform:uppercase; letter-spacing:0.14em; color:var(--gp-gold); font-weight:700; margin-bottom:12px; }

  .gp-mobile { display:none; }
  @media(max-width:900px){
    .gp-header-inner { display:none; }
    .gp-mobile { display:block; }
    .gp-mobile-bar { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 16px; }
    .gp-mobile-menu-btn { background:none; border:none; padding:8px; cursor:pointer; color:var(--gp-dark); }
    .gp-mobile-menu-btn svg { width:24px; height:24px; stroke:currentColor; stroke-width:2; fill:none; }
    .gp-mobile-logo { font-family:'Lora',serif; font-size:20px; font-weight:700; color:var(--gp-emerald); text-decoration:none; }
    .gp-mobile-logo img { max-height:32px; max-width:130px; }
    .gp-mobile-actions { display:flex; align-items:center; gap:4px; }
    .gp-mobile-search { padding:0 16px 12px; position:relative; }
    .gp-mobile-search input { width:100%; padding:11px 14px 11px 40px; border:1px solid var(--gp-border); border-radius:999px; font-family:'Inter',sans-serif; font-size:14px; outline:none; background:var(--gp-cream); color:var(--gp-dark); box-sizing:border-box; }
    .gp-mobile-search-icon { position:absolute; left:30px; top:22px; transform:translateY(-50%); color:var(--gp-emerald); }
  }
</style>

<?php if(!empty($settings->announcement_bar)): ?>
<div class="gp-announce"><?= htmlspecialchars($settings->announcement_bar); ?></div>
<?php else: ?>
<div class="gp-announce">Premium quality &middot; <strong>Same-day delivery</strong> on orders over &#8358;35,000 &middot; <a href="<?= base_url('store/' . $slug . '/products'); ?>">Shop Now &rarr;</a></div>
<?php endif; ?>

<div class="gp-header">
  <div class="gp-header-inner">
    <nav class="gp-nav-left">
      <div class="gp-shop-menu">
        <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="gp-nav-link">Shop <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></a>
        <?php if(!empty($categories)): ?>
        <div class="gp-shop-drop">
          <?php foreach($categories as $cat): ?>
          <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>"><?= htmlspecialchars($cat->category_name); ?><?php if(!empty($cat->item_count)): ?><span class="cnt"><?= $cat->item_count; ?></span><?php endif; ?></a>
          <?php endforeach; ?>
          <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="all">View All Products &rarr;</a>
        </div>
        <?php endif; ?>
      </div>
      <?php if(!empty($categories)): foreach(array_slice($categories, 0, 4) as $cat): ?>
      <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="gp-nav-link"><?= htmlspecialchars($cat->category_name); ?></a>
      <?php endforeach; endif; ?>
      <?php if($settings->allow_services ?? false): ?>
      <a href="<?= base_url('store/' . $slug . '/services'); ?>" class="gp-nav-link">Services</a>
      <?php endif; ?>
    </nav>

    <a href="<?= base_url('store/' . $slug); ?>" class="gp-logo">
      <?php if($logo): ?>
        <img src="<?= $logo; ?>" alt="<?= htmlspecialchars($store->store_name ?? 'Grocery Plus'); ?>">
      <?php else: ?>
        <div>
          <div class="gp-logo-text"><?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Grocery Plus')); ?></div>
          <?php if(!empty($settings->store_subheadline)): ?>
          <div class="gp-logo-tag"><?= htmlspecialchars($settings->store_subheadline); ?></div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </a>

    <div class="gp-actions">
      <div class="gp-fresh-badge">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a8 8 0 0 0-8 8c0 4 3 7 8 9 5-2 8-5 8-9a8 8 0 0 0-8-8z"/><path d="M12 6v6"/></svg>
        Premium
      </div>
      <div class="gp-search">
        <span class="gp-search-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
        <input type="text" id="search-input" placeholder="Search premium groceries..." onkeydown="if(event.key==='Enter')doSearch()">
      </div>
      <a href="<?= base_url('store/' . $slug . '/cart'); ?>" class="gp-icon-btn" aria-label="Cart">
        <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span class="gp-cart-count" id="cart-count">0</span>
      </a>
    </div>
  </div>
</div>

<div class="gp-mobile">
  <div class="gp-mobile-bar">
    <button class="gp-mobile-menu-btn" onclick="document.getElementById('gp-drawer').classList.add('open');document.getElementById('gp-overlay').classList.add('open');" aria-label="Menu">
      <svg viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <a href="<?= base_url('store/' . $slug); ?>" class="gp-mobile-logo">
      <?php if($logo): ?>
        <img src="<?= $logo; ?>" alt="<?= htmlspecialchars($store->store_name ?? 'Grocery Plus'); ?>">
      <?php else: ?>
        <?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Grocery Plus')); ?>
      <?php endif; ?>
    </a>
    <div class="gp-mobile-actions">
      <a href="<?= base_url('store/' . $slug . '/cart'); ?>" class="gp-icon-btn" aria-label="Cart">
        <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span class="gp-cart-count" id="cart-count-mobile">0</span>
      </a>
    </div>
  </div>
  <div class="gp-mobile-search">
    <span class="gp-mobile-search-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
    <input type="text" id="search-input-mobile" placeholder="Search groceries..." onkeydown="if(event.key==='Enter'){const u='<?= base_url('store/' . $slug . '/products'); ?>';location.href=u+'?search='+encodeURIComponent(this.value);}">
  </div>
</div>

<div class="gp-mobile-overlay" id="gp-overlay" onclick="document.getElementById('gp-drawer').classList.remove('open');this.classList.remove('open');"></div>
<div class="gp-mobile-drawer" id="gp-drawer">
  <div class="gp-drawer-title"><?= htmlspecialchars($store->store_name ?? 'Menu'); ?></div>
  <a href="<?= base_url('store/' . $slug); ?>" class="gp-drawer-link" onclick="document.getElementById('gp-drawer').classList.remove('open');document.getElementById('gp-overlay').classList.remove('open');">Home</a>
  <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="gp-drawer-link" onclick="document.getElementById('gp-drawer').classList.remove('open');document.getElementById('gp-overlay').classList.remove('open');">Shop All</a>
  <?php if(!empty($categories)): ?>
  <div class="gp-drawer-section">
    <div class="gp-drawer-section-title">Categories</div>
    <?php foreach($categories as $cat): ?>
    <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="gp-drawer-link" onclick="document.getElementById('gp-drawer').classList.remove('open');document.getElementById('gp-overlay').classList.remove('open');"><?= htmlspecialchars($cat->category_name); ?></a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <div class="gp-drawer-section">
    <div class="gp-drawer-section-title">Contact</div>
    <?php if(!empty($settings->store_phone)): ?>
    <a href="tel:<?= preg_replace('/[^0-9+]/', '', $settings->store_phone); ?>" class="gp-drawer-link"><?= htmlspecialchars($settings->store_phone); ?></a>
    <?php endif; ?>
    <?php if(!empty($settings->whatsapp_number)): ?>
    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number); ?>" target="_blank" class="gp-drawer-link" style="color:#25D366;">WhatsApp</a>
    <?php endif; ?>
  </div>
</div>

<script>
  (function(){
    const observer = new MutationObserver(function(){
      const main = document.getElementById('cart-count');
      const mobile = document.getElementById('cart-count-mobile');
      if(main && mobile) mobile.textContent = main.textContent;
    });
    const target = document.getElementById('cart-count');
    if(target) observer.observe(target, {childList:true, characterData:true, subtree:true});
  })();
</script>
