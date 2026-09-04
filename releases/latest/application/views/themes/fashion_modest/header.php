<?php
/**
 * Modest Studio — Fashion Header
 * Warm, modest-wear focused header with announcement bar, centered logo,
 * icon actions and slim navigation. Lora serif throughout.
 */
$slug = $settings->store_slug ?? '';
$logo = $logo_url ?? null;
$social = $social_links ?? [];
?>
<style>
  /* Reset shared layout styles for this theme */
  .theme-fashion_modest .mp-topbar,
  .theme-fashion_modest .mp-announcement,
  .theme-fashion_modest .mp-nav { display:none !important; }
  .theme-fashion_modest .mp-header { display:none !important; }
  .theme-fashion_modest .mp-mobile-menu-btn { display:none !important; }
  .theme-fashion_modest .mp-footer-space { height:0 !important; }

  /* Modest Studio header */
  .msh-announce { background:var(--ms-ink, #1F2937); color:#fff; text-align:center; padding:10px 16px; font-family:'Lora',serif; font-size:13px; font-weight:500; letter-spacing:0.02em; }
  .msh-announce a { color:var(--ms-warm, #C2956A); text-decoration:underline; }
  .msh-header { background:#fff; border-bottom:1px solid var(--ms-soft, #F0E9E0); position:sticky; top:0; z-index:100; }
  .msh-header-inner { max-width:1400px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; gap:24px; padding:16px 24px; }
  .msh-nav-left { display:flex; align-items:center; gap:28px; flex:1; }
  .msh-nav-link { font-family:'Lora',serif; font-size:14px; font-weight:500; color:var(--ms-ink, #1F2937); text-decoration:none; transition:color .2s; }
  .msh-nav-link:hover { color:var(--ms-warm, #C2956A); }
  .msh-logo { text-decoration:none; display:flex; align-items:center; gap:10px; flex-shrink:0; }
  .msh-logo img { max-height:40px; max-width:160px; object-fit:contain; }
  .msh-logo-text { font-family:'Lora',serif; font-size:24px; font-weight:600; color:var(--ms-ink, #1F2937); letter-spacing:-0.01em; }
  .msh-logo-tag { font-family:'Lora',serif; font-size:11px; color:var(--ms-warm, #C2956A); letter-spacing:0.06em; font-weight:600; }
  .msh-actions { display:flex; align-items:center; gap:8px; flex:1; justify-content:flex-end; }
  .msh-search { position:relative; max-width:240px; width:100%; }
  .msh-search input { width:100%; padding:10px 14px 10px 38px; border:1px solid var(--ms-soft, #F0E9E0); border-radius:12px; font-family:'Lora',serif; font-size:13px; outline:none; background:var(--ms-cream, #FAF6F1); transition:border-color .2s; }
  .msh-search input:focus { border-color:var(--ms-warm, #C2956A); background:#fff; }
  .msh-search-icon { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94A3B8; }
  .msh-icon-btn { width:42px; height:42px; display:flex; align-items:center; justify-content:center; border-radius:50%; transition:background .2s; position:relative; text-decoration:none; color:var(--ms-ink, #1F2937); }
  .msh-icon-btn:hover { background:var(--ms-soft, #F0E9E0); }
  .msh-icon-btn svg { width:22px; height:22px; stroke:currentColor; stroke-width:1.8; fill:none; stroke-linecap:round; stroke-linejoin:round; }
  .msh-cart-count { position:absolute; top:0; right:0; background:var(--ms-warm, #C2956A); color:#fff; font-family:'Lora',serif; font-size:10px; font-weight:600; min-width:18px; height:18px; padding:0 5px; border-radius:999px; display:flex; align-items:center; justify-content:center; }

  /* Mobile drawer — hidden by default on ALL screens */
  .msh-mobile-drawer { display:none; position:fixed; top:0; left:0; bottom:0; width:300px; background:#fff; z-index:901; padding:24px; box-shadow:4px 0 24px rgba(31,41,55,0.1); overflow-y:auto; }
  .msh-mobile-drawer.open { display:block; }
  .msh-mobile-overlay { display:none; position:fixed; inset:0; background:rgba(31,41,55,0.4); z-index:900; }
  .msh-mobile-overlay.open { display:block; }
  .msh-drawer-title { font-family:'Lora',serif; font-size:20px; font-weight:600; margin-bottom:24px; color:var(--ms-ink, #1F2937); }
  .msh-drawer-link { display:block; padding:14px 0; font-family:'Lora',serif; font-size:15px; font-weight:500; color:var(--ms-ink, #1F2937); text-decoration:none; border-bottom:1px solid var(--ms-soft, #F0E9E0); }
  .msh-drawer-link:hover { color:var(--ms-warm, #C2956A); }
  .msh-drawer-section { margin-top:24px; }
  .msh-drawer-section-title { font-family:'Lora',serif; font-size:12px; letter-spacing:0.06em; color:var(--ms-warm, #C2956A); font-weight:600; margin-bottom:12px; }

  /* Mobile header */
  .msh-mobile { display:none; }
  @media(max-width:900px){
    .msh-header-inner { display:none; }
    .msh-mobile { display:block; }
    .msh-mobile-bar { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:12px 16px; }
    .msh-mobile-menu-btn { background:none; border:none; padding:8px; cursor:pointer; color:var(--ms-ink, #1F2937); }
    .msh-mobile-menu-btn svg { width:24px; height:24px; stroke:currentColor; stroke-width:2; fill:none; }
    .msh-mobile-logo { font-family:'Lora',serif; font-size:20px; font-weight:600; color:var(--ms-ink, #1F2937); text-decoration:none; }
    .msh-mobile-logo img { max-height:32px; max-width:120px; }
    .msh-mobile-actions { display:flex; align-items:center; gap:4px; }
  }
</style>

<?php if(!empty($settings->announcement_bar)): ?>
<div class="msh-announce"><?= htmlspecialchars($settings->announcement_bar); ?></div>
<?php else: ?>
<div class="msh-announce">Elegant modesty, delivered with care &middot; <a href="<?= base_url('store/' . $slug . '/products'); ?>">Shop now &rarr;</a></div>
<?php endif; ?>

<!-- Desktop Header -->
<div class="msh-header">
  <div class="msh-header-inner">
    <nav class="msh-nav-left">
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="msh-nav-link">Collection</a>
      <?php if(!empty($categories)): foreach(array_slice($categories, 0, 4) as $cat): ?>
      <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="msh-nav-link"><?= htmlspecialchars($cat->category_name); ?></a>
      <?php endforeach; endif; ?>
      <?php if($settings->allow_services ?? false): ?>
      <a href="<?= base_url('store/' . $slug . '/services'); ?>" class="msh-nav-link">Services</a>
      <?php endif; ?>
    </nav>

    <a href="<?= base_url('store/' . $slug); ?>" class="msh-logo">
      <?php if($logo): ?>
        <img src="<?= $logo; ?>" alt="<?= htmlspecialchars($store->store_name ?? 'Store'); ?>">
      <?php else: ?>
        <div>
          <div class="msh-logo-text"><?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Store')); ?></div>
          <?php if(!empty($settings->store_subheadline)): ?>
          <div class="msh-logo-tag"><?= htmlspecialchars($settings->store_subheadline); ?></div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </a>

    <div class="msh-actions">
      <div class="msh-search">
        <span class="msh-search-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
        <input type="text" id="search-input" placeholder="Search products..." onkeydown="if(event.key==='Enter')doSearch()">
      </div>
      <a href="<?= base_url('store/' . $slug . '/cart'); ?>" class="msh-icon-btn" aria-label="Cart">
        <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span class="msh-cart-count" id="cart-count">0</span>
      </a>
    </div>
  </div>
</div>

<!-- Mobile Header -->
<div class="msh-mobile">
  <div class="msh-mobile-bar">
    <button class="msh-mobile-menu-btn" onclick="document.getElementById('msh-drawer').classList.add('open');document.getElementById('msh-overlay').classList.add('open');" aria-label="Menu">
      <svg viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <a href="<?= base_url('store/' . $slug); ?>" class="msh-mobile-logo">
      <?php if($logo): ?>
        <img src="<?= $logo; ?>" alt="<?= htmlspecialchars($store->store_name ?? 'Store'); ?>">
      <?php else: ?>
        <?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Store')); ?>
      <?php endif; ?>
    </a>
    <div class="msh-mobile-actions">
      <a href="<?= base_url('store/' . $slug . '/cart'); ?>" class="msh-icon-btn" aria-label="Cart">
        <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span class="msh-cart-count" id="cart-count-mobile">0</span>
      </a>
    </div>
  </div>
</div>

<!-- Mobile Drawer -->
<div class="msh-mobile-overlay" id="msh-overlay" onclick="document.getElementById('msh-drawer').classList.remove('open');this.classList.remove('open');"></div>
<div class="msh-mobile-drawer" id="msh-drawer">
  <div class="msh-drawer-title"><?= htmlspecialchars($store->store_name ?? 'Menu'); ?></div>
  <a href="<?= base_url('store/' . $slug); ?>" class="msh-drawer-link" onclick="document.getElementById('msh-drawer').classList.remove('open');document.getElementById('msh-overlay').classList.remove('open');">Home</a>
  <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="msh-drawer-link" onclick="document.getElementById('msh-drawer').classList.remove('open');document.getElementById('msh-overlay').classList.remove('open');">All Products</a>
  <?php if(!empty($categories)): ?>
  <div class="msh-drawer-section">
    <div class="msh-drawer-section-title">Categories</div>
    <?php foreach($categories as $cat): ?>
    <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="msh-drawer-link" onclick="document.getElementById('msh-drawer').classList.remove('open');document.getElementById('msh-overlay').classList.remove('open');"><?= htmlspecialchars($cat->category_name); ?></a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <?php if($settings->allow_services ?? false): ?>
  <a href="<?= base_url('store/' . $slug . '/services'); ?>" class="msh-drawer-link" onclick="document.getElementById('msh-drawer').classList.remove('open');document.getElementById('msh-overlay').classList.remove('open');">Services</a>
  <?php endif; ?>
  <div class="msh-drawer-section">
    <div class="msh-drawer-section-title">Contact</div>
    <?php if(!empty($settings->store_phone)): ?>
    <a href="tel:<?= preg_replace('/[^0-9+]/', '', $settings->store_phone); ?>" class="msh-drawer-link"><?= htmlspecialchars($settings->store_phone); ?></a>
    <?php endif; ?>
    <?php if(!empty($settings->whatsapp_number)): ?>
    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number); ?>" target="_blank" class="msh-drawer-link" style="color:#25D366;">WhatsApp Support</a>
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
