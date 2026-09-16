<?php
/**
 * Pharma Wellness — Pharmacy Header
 * Warm, wellness-focused header with sage teal accents and soft rounded design.
 */
$slug = $settings->store_slug ?? '';
$logo = $logo_url ?? null;
$social = $social_links ?? [];
?>
<style>
  .theme-pharma_wellness .mp-topbar,
  .theme-pharma_wellness .mp-announcement,
  .theme-pharma_wellness .mp-nav,
  .theme-pharma_wellness .mp-header { display:none !important; }
  .theme-pharma_wellness .mp-mobile-menu-btn { display:none !important; }
  .theme-pharma_wellness .mp-footer-space { height:0; }

  .pwh-announce { background:linear-gradient(90deg, #0D9488, #0F766E); color:#fff; text-align:center; padding:12px 16px; font-family:'Poppins',sans-serif; font-size:13px; font-weight:500; letter-spacing:0.02em; }
  .pwh-announce a { color:#FEF3C7; text-decoration:underline; }
  .pwh-header { background:#FFFBF5; border-bottom:1px solid #E8F0EE; position:sticky; top:0; z-index:100; }
  .pwh-header-inner { max-width:1400px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; gap:24px; padding:16px 24px; }
  .pwh-nav-left { display:flex; align-items:center; gap:28px; flex:1; }
  .pwh-nav-link { font-family:'Poppins',sans-serif; font-size:13px; font-weight:500; color:#134E4A; text-decoration:none; transition:color .2s; }
  .pwh-nav-link:hover { color:#0D9488; }
  .pwh-logo { text-decoration:none; display:flex; align-items:center; gap:10px; flex-shrink:0; }
  .pwh-logo img { max-height:40px; max-width:180px; object-fit:contain; }
  .pwh-logo-text { font-family:'Poppins',sans-serif; font-size:24px; font-weight:700; color:#0D9488; letter-spacing:-0.01em; }
  .pwh-logo-tag { font-family:'Poppins',sans-serif; font-size:10px; color:#F97066; letter-spacing:0.12em; text-transform:uppercase; font-weight:500; }
  .pwh-actions { display:flex; align-items:center; gap:8px; flex:1; justify-content:flex-end; }
  .pwh-search { position:relative; max-width:240px; width:100%; }
  .pwh-search input { width:100%; padding:10px 14px 10px 38px; border:1px solid #E8F0EE; border-radius:24px; font-family:'Poppins',sans-serif; font-size:13px; outline:none; background:#fff; transition:border-color .2s; }
  .pwh-search input:focus { border-color:#0D9488; }
  .pwh-search-icon { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#0D9488; }
  .pwh-icon-btn { width:44px; height:44px; display:flex; align-items:center; justify-content:center; transition:background .2s; position:relative; text-decoration:none; color:#134E4A; border-radius:50%; }
  .pwh-icon-btn:hover { background:#E8F0EE; }
  .pwh-icon-btn svg { width:22px; height:22px; stroke:currentColor; stroke-width:1.6; fill:none; stroke-linecap:round; stroke-linejoin:round; }
  .pwh-cart-count { position:absolute; top:2px; right:2px; background:#F97066; color:#fff; font-size:10px; font-weight:700; width:18px; height:18px; border-radius:50%; display:flex; align-items:center; justify-content:center; }
  .pwh-wellness-badge { display:flex; align-items:center; gap:6px; background:rgba(13,148,136,0.1); border:1px solid rgba(13,148,136,0.2); border-radius:24px; padding:6px 14px; font-family:'Poppins',sans-serif; font-size:11px; font-weight:500; color:#0D9488; white-space:nowrap; }
  .pwh-wellness-badge svg { width:14px; height:14px; color:#0D9488; }

  /* Mobile drawer */
  .pwh-mobile-drawer { display:none; position:fixed; top:0; left:0; bottom:0; width:300px; background:#FFFBF5; z-index:901; padding:24px; box-shadow:4px 0 24px rgba(0,0,0,0.08); overflow-y:auto; }
  .pwh-mobile-drawer.open { display:block; }
  .pwh-mobile-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.3); z-index:900; }
  .pwh-mobile-overlay.open { display:block; }
  .pwh-drawer-title { font-family:'Poppins',sans-serif; font-size:22px; font-weight:700; margin-bottom:24px; color:#0D9488; }
  .pwh-drawer-link { display:block; padding:14px 0; font-family:'Poppins',sans-serif; font-size:14px; font-weight:500; color:#134E4A; text-decoration:none; border-bottom:1px solid #E8F0EE; }
  .pwh-drawer-link:hover { color:#0D9488; }
  .pwh-drawer-section { margin-top:24px; }
  .pwh-drawer-section-title { font-family:'Poppins',sans-serif; font-size:10px; text-transform:uppercase; letter-spacing:0.14em; color:#F97066; font-weight:600; margin-bottom:12px; }

  .pwh-mobile { display:none; }
  @media(max-width:900px){
    .pwh-header-inner { display:none; }
    .pwh-mobile { display:block; }
    .pwh-mobile-bar { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 16px; }
    .pwh-mobile-menu-btn { background:none; border:none; padding:8px; cursor:pointer; color:#134E4A; }
    .pwh-mobile-menu-btn svg { width:24px; height:24px; stroke:currentColor; stroke-width:2; fill:none; }
    .pwh-mobile-logo { font-family:'Poppins',sans-serif; font-size:20px; font-weight:700; color:#0D9488; text-decoration:none; }
    .pwh-mobile-logo img { max-height:30px; max-width:130px; }
    .pwh-mobile-actions { display:flex; align-items:center; gap:4px; }
  }
</style>

<?php if(!empty($settings->announcement_bar)): ?>
<div class="pwh-announce"><?= htmlspecialchars($settings->announcement_bar); ?></div>
<?php else: ?>
<div class="pwh-announce">Your Wellness Journey Starts Here &middot; Free Delivery on orders over &#8358;25,000 &middot; <a href="<?= base_url('store/' . $slug . '/products'); ?>">Explore &rarr;</a></div>
<?php endif; ?>

<div class="pwh-header">
  <div class="pwh-header-inner">
    <nav class="pwh-nav-left">
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pwh-nav-link">Shop</a>
      <?php if(!empty($categories)): foreach(array_slice($categories, 0, 4) as $cat): ?>
      <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="pwh-nav-link"><?= htmlspecialchars($cat->category_name); ?></a>
      <?php endforeach; endif; ?>
      <?php if($settings->allow_services ?? false): ?>
      <a href="<?= base_url('store/' . $slug . '/services'); ?>" class="pwh-nav-link">Services</a>
      <?php endif; ?>
    </nav>

    <a href="<?= base_url('store/' . $slug); ?>" class="pwh-logo">
      <?php if($logo): ?>
        <img src="<?= $logo; ?>" alt="<?= htmlspecialchars($store->store_name ?? 'Wellness Pharmacy'); ?>">
      <?php else: ?>
        <div>
          <div class="pwh-logo-text"><?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Wellness Pharmacy')); ?></div>
          <?php if(!empty($settings->store_subheadline)): ?>
          <div class="pwh-logo-tag"><?= htmlspecialchars($settings->store_subheadline); ?></div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </a>

    <div class="pwh-actions">
      <div class="pwh-wellness-badge">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        Wellness Focused
      </div>
      <div class="pwh-search">
        <span class="pwh-search-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
        <input type="text" id="search-input" placeholder="Search wellness..." onkeydown="if(event.key==='Enter')doSearch()">
      </div>
      <a href="<?= base_url('store/' . $slug . '/cart'); ?>" class="pwh-icon-btn" aria-label="Cart">
        <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span class="pwh-cart-count" id="cart-count">0</span>
      </a>
    </div>
  </div>
</div>

<div class="pwh-mobile">
  <div class="pwh-mobile-bar">
    <button class="pwh-mobile-menu-btn" onclick="document.getElementById('pwh-drawer').classList.add('open');document.getElementById('pwh-overlay').classList.add('open');" aria-label="Menu">
      <svg viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <a href="<?= base_url('store/' . $slug); ?>" class="pwh-mobile-logo">
      <?php if($logo): ?>
        <img src="<?= $logo; ?>" alt="<?= htmlspecialchars($store->store_name ?? 'Wellness Pharmacy'); ?>">
      <?php else: ?>
        <?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Wellness Pharmacy')); ?>
      <?php endif; ?>
    </a>
    <div class="pwh-mobile-actions">
      <a href="<?= base_url('store/' . $slug . '/cart'); ?>" class="pwh-icon-btn" aria-label="Cart">
        <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span class="pwh-cart-count" id="cart-count-mobile">0</span>
      </a>
    </div>
  </div>
</div>

<div class="pwh-mobile-overlay" id="pwh-overlay" onclick="document.getElementById('pwh-drawer').classList.remove('open');this.classList.remove('open');"></div>
<div class="pwh-mobile-drawer" id="pwh-drawer">
  <div class="pwh-drawer-title"><?= htmlspecialchars($store->store_name ?? 'Menu'); ?></div>
  <a href="<?= base_url('store/' . $slug); ?>" class="pwh-drawer-link" onclick="document.getElementById('pwh-drawer').classList.remove('open');document.getElementById('pwh-overlay').classList.remove('open');">Home</a>
  <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pwh-drawer-link" onclick="document.getElementById('pwh-drawer').classList.remove('open');document.getElementById('pwh-overlay').classList.remove('open');">Shop</a>
  <?php if(!empty($categories)): ?>
  <div class="pwh-drawer-section">
    <div class="pwh-drawer-section-title">Categories</div>
    <?php foreach($categories as $cat): ?>
    <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="pwh-drawer-link" onclick="document.getElementById('pwh-drawer').classList.remove('open');document.getElementById('pwh-overlay').classList.remove('open');"><?= htmlspecialchars($cat->category_name); ?></a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <div class="pwh-drawer-section">
    <div class="pwh-drawer-section-title">Contact</div>
    <?php if(!empty($settings->store_phone)): ?>
    <a href="tel:<?= preg_replace('/[^0-9+]/', '', $settings->store_phone); ?>" class="pwh-drawer-link"><?= htmlspecialchars($settings->store_phone); ?></a>
    <?php endif; ?>
    <?php if(!empty($settings->whatsapp_number)): ?>
    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number); ?>" target="_blank" class="pwh-drawer-link" style="color:#25D366;">WhatsApp</a>
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
