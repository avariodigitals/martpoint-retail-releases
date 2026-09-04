<?php
/**
 * Modern Minimal — Fashion Header
 * Clean, Shopify-style header with announcement bar, centered logo,
 * icon actions and slim navigation.
 */
$slug = $settings->store_slug ?? '';
$logo = $logo_url ?? null;
$social = $social_links ?? [];
?>
<style>
  /* Reset shared layout styles for this theme */
  .theme-fashion_modern .mp-topbar,
  .theme-fashion_modern .mp-announcement,
  .theme-fashion_modern .mp-nav { display:none !important; }
  .theme-fashion_modern .mp-header { display:none !important; }
  .theme-fashion_modern .mp-mobile-menu-btn { display:none !important; }

  /* Modern header */
  .fmh-announce { background:#0F172A; color:#fff; text-align:center; padding:10px 16px; font-size:12px; font-weight:500; letter-spacing:0.02em; }
  .fmh-announce a { color:#fff; text-decoration:underline; }
  .fmh-header { background:#fff; border-bottom:1px solid #E2E8F0; position:sticky; top:0; z-index:100; }
  .fmh-header-inner { max-width:1400px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; gap:24px; padding:16px 24px; }
  .fmh-nav-left { display:flex; align-items:center; gap:28px; flex:1; }
  .fmh-nav-link { font-size:14px; font-weight:500; color:#334155; text-decoration:none; transition:color .2s; }
  .fmh-nav-link:hover { color:#0F172A; }
  .fmh-logo { text-decoration:none; display:flex; align-items:center; gap:10px; flex-shrink:0; }
  .fmh-logo img { max-height:40px; max-width:160px; object-fit:contain; }
  .fmh-logo-text { font-family:'Playfair Display',serif; font-size:24px; font-weight:700; color:#0F172A; letter-spacing:-0.02em; }
  .fmh-logo-tag { font-size:10px; color:#94A3B8; letter-spacing:0.1em; text-transform:uppercase; }
  .fmh-actions { display:flex; align-items:center; gap:8px; flex:1; justify-content:flex-end; }
  .fmh-search { position:relative; max-width:240px; width:100%; }
  .fmh-search input { width:100%; padding:10px 14px 10px 38px; border:1px solid #E2E8F0; border-radius:999px; font-size:13px; outline:none; background:#F8FAFC; transition:border-color .2s; }
  .fmh-search input:focus { border-color:#0F172A; background:#fff; }
  .fmh-search-icon { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94A3B8; }
  .fmh-icon-btn { width:42px; height:42px; display:flex; align-items:center; justify-content:center; border-radius:50%; transition:background .2s; position:relative; text-decoration:none; color:#0F172A; }
  .fmh-icon-btn:hover { background:#F1F5F9; }
  .fmh-icon-btn svg { width:22px; height:22px; stroke:currentColor; stroke-width:1.8; fill:none; stroke-linecap:round; stroke-linejoin:round; }
  .fmh-cart-count { position:absolute; top:2px; right:2px; background:#6366F1; color:#fff; font-size:10px; font-weight:700; width:18px; height:18px; border-radius:50%; display:flex; align-items:center; justify-content:center; }

  /* Mobile drawer — hidden by default on ALL screens */
  .fmh-mobile-drawer { display:none; position:fixed; top:0; left:0; bottom:0; width:300px; background:#fff; z-index:901; padding:24px; box-shadow:4px 0 24px rgba(0,0,0,0.1); overflow-y:auto; }
  .fmh-mobile-drawer.open { display:block; }
  .fmh-mobile-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:900; }
  .fmh-mobile-overlay.open { display:block; }
  .fmh-drawer-title { font-family:'Playfair Display',serif; font-size:20px; font-weight:700; margin-bottom:24px; color:#0F172A; }
  .fmh-drawer-link { display:block; padding:14px 0; font-size:15px; font-weight:500; color:#334155; text-decoration:none; border-bottom:1px solid #F1F5F9; }
  .fmh-drawer-link:hover { color:#0F172A; }
  .fmh-drawer-section { margin-top:24px; }
  .fmh-drawer-section-title { font-size:11px; text-transform:uppercase; letter-spacing:0.1em; color:#94A3B8; font-weight:700; margin-bottom:12px; }

  /* Mobile header */
  .fmh-mobile { display:none; }
  @media(max-width:900px){
    .fmh-header-inner { display:none; }
    .fmh-mobile { display:block; }
    .fmh-mobile-bar { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px 16px; }
    .fmh-mobile-menu-btn { background:none; border:none; padding:8px; cursor:pointer; color:#0F172A; }
    .fmh-mobile-menu-btn svg { width:24px; height:24px; stroke:currentColor; stroke-width:2; fill:none; }
    .fmh-mobile-logo { font-family:'Playfair Display',serif; font-size:20px; font-weight:700; color:#0F172A; text-decoration:none; }
    .fmh-mobile-logo img { max-height:32px; max-width:120px; }
    .fmh-mobile-actions { display:flex; align-items:center; gap:4px; }
  }
</style>

<?php if(!empty($settings->announcement_bar)): ?>
<div class="fmh-announce"><?= htmlspecialchars($settings->announcement_bar); ?></div>
<?php else: ?>
<div class="fmh-announce">Free shipping on orders over ₦15,000 &middot; <a href="<?= base_url('store/' . $slug . '/products'); ?>">Shop now &rarr;</a></div>
<?php endif; ?>

<!-- Desktop Header -->
<div class="fmh-header">
  <div class="fmh-header-inner">
    <nav class="fmh-nav-left">
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="fmh-nav-link">Shop</a>
      <?php if(!empty($categories)): foreach(array_slice($categories, 0, 4) as $cat): ?>
      <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="fmh-nav-link"><?= htmlspecialchars($cat->category_name); ?></a>
      <?php endforeach; endif; ?>
      <?php if($settings->allow_services ?? false): ?>
      <a href="<?= base_url('store/' . $slug . '/services'); ?>" class="fmh-nav-link">Services</a>
      <?php endif; ?>
    </nav>

    <a href="<?= base_url('store/' . $slug); ?>" class="fmh-logo">
      <?php if($logo): ?>
        <img src="<?= $logo; ?>" alt="<?= htmlspecialchars($store->store_name ?? 'Store'); ?>">
      <?php else: ?>
        <div>
          <div class="fmh-logo-text"><?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Store')); ?></div>
          <?php if(!empty($settings->store_subheadline)): ?>
          <div class="fmh-logo-tag"><?= htmlspecialchars($settings->store_subheadline); ?></div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </a>

    <div class="fmh-actions">
      <div class="fmh-search">
        <span class="fmh-search-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
        <input type="text" id="search-input" placeholder="Search products..." onkeydown="if(event.key==='Enter')doSearch()">
      </div>
      <a href="<?= base_url('store/' . $slug . '/cart'); ?>" class="fmh-icon-btn" aria-label="Cart">
        <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span class="fmh-cart-count" id="cart-count">0</span>
      </a>
    </div>
  </div>
</div>

<!-- Mobile Header -->
<div class="fmh-mobile">
  <div class="fmh-mobile-bar">
    <button class="fmh-mobile-menu-btn" onclick="document.getElementById('fmh-drawer').classList.add('open');document.getElementById('fmh-overlay').classList.add('open');" aria-label="Menu">
      <svg viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <a href="<?= base_url('store/' . $slug); ?>" class="fmh-mobile-logo">
      <?php if($logo): ?>
        <img src="<?= $logo; ?>" alt="<?= htmlspecialchars($store->store_name ?? 'Store'); ?>">
      <?php else: ?>
        <?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Store')); ?>
      <?php endif; ?>
    </a>
    <div class="fmh-mobile-actions">
      <a href="<?= base_url('store/' . $slug . '/cart'); ?>" class="fmh-icon-btn" aria-label="Cart">
        <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span class="fmh-cart-count" id="cart-count-mobile">0</span>
      </a>
    </div>
  </div>
</div>

<!-- Mobile Drawer -->
<div class="fmh-mobile-overlay" id="fmh-overlay" onclick="document.getElementById('fmh-drawer').classList.remove('open');this.classList.remove('open');"></div>
<div class="fmh-mobile-drawer" id="fmh-drawer">
  <div class="fmh-drawer-title"><?= htmlspecialchars($store->store_name ?? 'Menu'); ?></div>
  <a href="<?= base_url('store/' . $slug); ?>" class="fmh-drawer-link" onclick="document.getElementById('fmh-drawer').classList.remove('open');document.getElementById('fmh-overlay').classList.remove('open');">Home</a>
  <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="fmh-drawer-link" onclick="document.getElementById('fmh-drawer').classList.remove('open');document.getElementById('fmh-overlay').classList.remove('open');">All Products</a>
  <?php if(!empty($categories)): ?>
  <div class="fmh-drawer-section">
    <div class="fmh-drawer-section-title">Categories</div>
    <?php foreach($categories as $cat): ?>
    <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="fmh-drawer-link" onclick="document.getElementById('fmh-drawer').classList.remove('open');document.getElementById('fmh-overlay').classList.remove('open');"><?= htmlspecialchars($cat->category_name); ?></a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <?php if($settings->allow_services ?? false): ?>
  <a href="<?= base_url('store/' . $slug . '/services'); ?>" class="fmh-drawer-link" onclick="document.getElementById('fmh-drawer').classList.remove('open');document.getElementById('fmh-overlay').classList.remove('open');">Services</a>
  <?php endif; ?>
  <div class="fmh-drawer-section">
    <div class="fmh-drawer-section-title">Contact</div>
    <?php if(!empty($settings->store_phone)): ?>
    <a href="tel:<?= preg_replace('/[^0-9+]/', '', $settings->store_phone); ?>" class="fmh-drawer-link"><?= htmlspecialchars($settings->store_phone); ?></a>
    <?php endif; ?>
    <?php if(!empty($settings->whatsapp_number)): ?>
    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number); ?>" target="_blank" class="fmh-drawer-link" style="color:#25D366;">WhatsApp Support</a>
    <?php endif; ?>
  </div>
</div>

<script>
  // Sync cart count to mobile badge too
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
