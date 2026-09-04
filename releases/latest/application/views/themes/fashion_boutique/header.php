<?php
/**
 * Boutique Luxe — Fashion Header
 * Elegant serif-driven header with gold accents and refined spacing.
 */
$slug = $settings->store_slug ?? '';
$logo = $logo_url ?? null;
$social = $social_links ?? [];
?>
<style>
  .theme-fashion_boutique .mp-topbar,
  .theme-fashion_boutique .mp-announcement,
  .theme-fashion_boutique .mp-nav,
  .theme-fashion_boutique .mp-header { display:none !important; }
  .theme-fashion_boutique .mp-mobile-menu-btn { display:none !important; }
  .theme-fashion_boutique .mp-footer-space { height:0; }

  .blh-announce { background:#3D2817; color:#C9A961; text-align:center; padding:12px 16px; font-family:'Lora',serif; font-size:13px; font-weight:500; letter-spacing:0.04em; }
  .blh-announce a { color:#fff; text-decoration:underline; }
  .blh-header { background:#fff; border-bottom:1px solid #F5EFE6; position:sticky; top:0; z-index:100; }
  .blh-header-inner { max-width:1400px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; gap:24px; padding:20px 24px; }
  .blh-nav-left { display:flex; align-items:center; gap:32px; flex:1; }
  .blh-nav-link { font-family:'Lora',serif; font-size:11px; font-weight:600; color:#3D2817; text-decoration:none; text-transform:uppercase; letter-spacing:0.14em; transition:color .2s; }
  .blh-nav-link:hover { color:#C9A961; }
  .blh-logo { text-decoration:none; display:flex; align-items:center; gap:10px; flex-shrink:0; }
  .blh-logo img { max-height:44px; max-width:180px; object-fit:contain; }
  .blh-logo-text { font-family:'Playfair Display',serif; font-size:26px; font-weight:700; color:#3D2817; font-style:italic; letter-spacing:-0.01em; }
  .blh-logo-tag { font-family:'Lora',serif; font-size:10px; color:#C9A961; letter-spacing:0.14em; text-transform:uppercase; }
  .blh-actions { display:flex; align-items:center; gap:8px; flex:1; justify-content:flex-end; }
  .blh-search { position:relative; max-width:220px; width:100%; }
  .blh-search input { width:100%; padding:10px 14px 10px 36px; border:1px solid #F5EFE6; border-radius:2px; font-family:'Lora',serif; font-size:13px; outline:none; background:#FBF7F0; transition:border-color .2s; }
  .blh-search input:focus { border-color:#C9A961; background:#fff; }
  .blh-search-icon { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#C9A961; }
  .blh-icon-btn { width:44px; height:44px; display:flex; align-items:center; justify-content:center; transition:background .2s; position:relative; text-decoration:none; color:#3D2817; }
  .blh-icon-btn:hover { background:#FBF7F0; }
  .blh-icon-btn svg { width:22px; height:22px; stroke:currentColor; stroke-width:1.6; fill:none; stroke-linecap:round; stroke-linejoin:round; }
  .blh-cart-count { position:absolute; top:2px; right:2px; background:#C9A961; color:#fff; font-size:10px; font-weight:700; width:18px; height:18px; border-radius:50%; display:flex; align-items:center; justify-content:center; }

  /* Mobile drawer — hidden by default on ALL screens */
  .blh-mobile-drawer { display:none; position:fixed; top:0; left:0; bottom:0; width:300px; background:#fff; z-index:901; padding:24px; box-shadow:4px 0 24px rgba(0,0,0,0.1); overflow-y:auto; }
  .blh-mobile-drawer.open { display:block; }
  .blh-mobile-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:900; }
  .blh-mobile-overlay.open { display:block; }
  .blh-drawer-title { font-family:'Playfair Display',serif; font-size:22px; font-weight:700; font-style:italic; margin-bottom:24px; color:#3D2817; }
  .blh-drawer-link { display:block; padding:14px 0; font-family:'Lora',serif; font-size:14px; font-weight:600; color:#3D2817; text-decoration:none; text-transform:uppercase; letter-spacing:0.1em; border-bottom:1px solid #F5EFE6; }
  .blh-drawer-link:hover { color:#C9A961; }
  .blh-drawer-section { margin-top:24px; }
  .blh-drawer-section-title { font-family:'Lora',serif; font-size:10px; text-transform:uppercase; letter-spacing:0.16em; color:#C9A961; font-weight:700; margin-bottom:12px; }

  .blh-mobile { display:none; }
  @media(max-width:900px){
    .blh-header-inner { display:none; }
    .blh-mobile { display:block; }
    .blh-mobile-bar { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 16px; }
    .blh-mobile-menu-btn { background:none; border:none; padding:8px; cursor:pointer; color:#3D2817; }
    .blh-mobile-menu-btn svg { width:24px; height:24px; stroke:currentColor; stroke-width:2; fill:none; }
    .blh-mobile-logo { font-family:'Playfair Display',serif; font-size:20px; font-weight:700; color:#3D2817; font-style:italic; text-decoration:none; }
    .blh-mobile-logo img { max-height:34px; max-width:130px; }
    .blh-mobile-actions { display:flex; align-items:center; gap:4px; }
  }
</style>

<?php if(!empty($settings->announcement_bar)): ?>
<div class="blh-announce"><?= htmlspecialchars($settings->announcement_bar); ?></div>
<?php else: ?>
<div class="blh-announce">Complimentary shipping on orders over ₦15,000 &middot; <a href="<?= base_url('store/' . $slug . '/products'); ?>">Explore &rarr;</a></div>
<?php endif; ?>

<div class="blh-header">
  <div class="blh-header-inner">
    <nav class="blh-nav-left">
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="blh-nav-link">Boutique</a>
      <?php if(!empty($categories)): foreach(array_slice($categories, 0, 4) as $cat): ?>
      <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="blh-nav-link"><?= htmlspecialchars($cat->category_name); ?></a>
      <?php endforeach; endif; ?>
      <?php if($settings->allow_services ?? false): ?>
      <a href="<?= base_url('store/' . $slug . '/services'); ?>" class="blh-nav-link">Services</a>
      <?php endif; ?>
    </nav>

    <a href="<?= base_url('store/' . $slug); ?>" class="blh-logo">
      <?php if($logo): ?>
        <img src="<?= $logo; ?>" alt="<?= htmlspecialchars($store->store_name ?? 'Store'); ?>">
      <?php else: ?>
        <div>
          <div class="blh-logo-text"><?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Store')); ?></div>
          <?php if(!empty($settings->store_subheadline)): ?>
          <div class="blh-logo-tag"><?= htmlspecialchars($settings->store_subheadline); ?></div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </a>

    <div class="blh-actions">
      <div class="blh-search">
        <span class="blh-search-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
        <input type="text" id="search-input" placeholder="Search..." onkeydown="if(event.key==='Enter')doSearch()">
      </div>
      <a href="<?= base_url('store/' . $slug . '/cart'); ?>" class="blh-icon-btn" aria-label="Cart">
        <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span class="blh-cart-count" id="cart-count">0</span>
      </a>
    </div>
  </div>
</div>

<div class="blh-mobile">
  <div class="blh-mobile-bar">
    <button class="blh-mobile-menu-btn" onclick="document.getElementById('blh-drawer').classList.add('open');document.getElementById('blh-overlay').classList.add('open');" aria-label="Menu">
      <svg viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <a href="<?= base_url('store/' . $slug); ?>" class="blh-mobile-logo">
      <?php if($logo): ?>
        <img src="<?= $logo; ?>" alt="<?= htmlspecialchars($store->store_name ?? 'Store'); ?>">
      <?php else: ?>
        <?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Store')); ?>
      <?php endif; ?>
    </a>
    <div class="blh-mobile-actions">
      <a href="<?= base_url('store/' . $slug . '/cart'); ?>" class="blh-icon-btn" aria-label="Cart">
        <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span class="blh-cart-count" id="cart-count-mobile">0</span>
      </a>
    </div>
  </div>
</div>

<div class="blh-mobile-overlay" id="blh-overlay" onclick="document.getElementById('blh-drawer').classList.remove('open');this.classList.remove('open');"></div>
<div class="blh-mobile-drawer" id="blh-drawer">
  <div class="blh-drawer-title"><?= htmlspecialchars($store->store_name ?? 'Menu'); ?></div>
  <a href="<?= base_url('store/' . $slug); ?>" class="blh-drawer-link" onclick="document.getElementById('blh-drawer').classList.remove('open');document.getElementById('blh-overlay').classList.remove('open');">Home</a>
  <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="blh-drawer-link" onclick="document.getElementById('blh-drawer').classList.remove('open');document.getElementById('blh-overlay').classList.remove('open');">Boutique</a>
  <?php if(!empty($categories)): ?>
  <div class="blh-drawer-section">
    <div class="blh-drawer-section-title">Categories</div>
    <?php foreach($categories as $cat): ?>
    <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="blh-drawer-link" onclick="document.getElementById('blh-drawer').classList.remove('open');document.getElementById('blh-overlay').classList.remove('open');"><?= htmlspecialchars($cat->category_name); ?></a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <div class="blh-drawer-section">
    <div class="blh-drawer-section-title">Contact</div>
    <?php if(!empty($settings->store_phone)): ?>
    <a href="tel:<?= preg_replace('/[^0-9+]/', '', $settings->store_phone); ?>" class="blh-drawer-link"><?= htmlspecialchars($settings->store_phone); ?></a>
    <?php endif; ?>
    <?php if(!empty($settings->whatsapp_number)): ?>
    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number); ?>" target="_blank" class="blh-drawer-link" style="color:#25D366;">WhatsApp</a>
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
