<?php
/**
 * Urban Editorial — Fashion Header
 * Bold, magazine-style header with announcement marquee, centered logo
 * and uppercase navigation.
 */
$slug = $settings->store_slug ?? '';
$logo = $logo_url ?? null;
$social = $social_links ?? [];
?>
<style>
  .theme-urban_fashion .mp-topbar,
  .theme-urban_fashion .mp-announcement,
  .theme-urban_fashion .mp-nav,
  .theme-urban_fashion .mp-header { display:none !important; }
  .theme-urban_fashion .mp-mobile-menu-btn { display:none !important; }
  .theme-urban_fashion .mp-footer-space { height:0; }

  .ueh-marquee { background:#0A0A0A; color:#fff; padding:12px 0; overflow:hidden; white-space:nowrap; }
  .ueh-marquee-track { display:inline-flex; gap:48px; animation:uehMarquee 30s linear infinite; }
  .ueh-marquee-item { font-size:11px; font-weight:700; letter-spacing:0.16em; text-transform:uppercase; }
  .ueh-marquee-item .dot { color:#FF3B30; margin-right:14px; }
  @keyframes uehMarquee { from{transform:translateX(0);} to{transform:translateX(-50%);} }

  .ueh-header { background:#fff; border-bottom:1px solid #0A0A0A; position:sticky; top:0; z-index:100; }
  .ueh-header-inner { max-width:1400px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; gap:24px; padding:18px 24px; }
  .ueh-nav-left { display:flex; align-items:center; gap:32px; flex:1; }
  .ueh-nav-link { font-family:'Montserrat',sans-serif; font-size:11px; font-weight:700; color:#0A0A0A; text-decoration:none; text-transform:uppercase; letter-spacing:0.12em; transition:color .2s; }
  .ueh-nav-link:hover { color:#FF3B30; }
  .ueh-logo { text-decoration:none; display:flex; align-items:center; gap:10px; flex-shrink:0; }
  .ueh-logo img { max-height:40px; max-width:160px; object-fit:contain; }
  .ueh-logo-text { font-family:'Montserrat',sans-serif; font-size:22px; font-weight:800; color:#0A0A0A; text-transform:uppercase; letter-spacing:0.02em; }
  .ueh-logo-tag { font-size:9px; color:#999; letter-spacing:0.16em; text-transform:uppercase; }
  .ueh-actions { display:flex; align-items:center; gap:8px; flex:1; justify-content:flex-end; }
  .ueh-search { position:relative; max-width:220px; width:100%; }
  .ueh-search input { width:100%; padding:10px 14px 10px 36px; border:1px solid #E5E5E0; border-radius:0; font-size:12px; outline:none; background:#FAFAF8; transition:border-color .2s; text-transform:uppercase; letter-spacing:0.04em; }
  .ueh-search input:focus { border-color:#0A0A0A; background:#fff; }
  .ueh-search-icon { position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#999; }
  .ueh-icon-btn { width:42px; height:42px; display:flex; align-items:center; justify-content:center; transition:background .2s; position:relative; text-decoration:none; color:#0A0A0A; }
  .ueh-icon-btn:hover { background:#F5F5F0; }
  .ueh-icon-btn svg { width:22px; height:22px; stroke:currentColor; stroke-width:1.8; fill:none; stroke-linecap:round; stroke-linejoin:round; }
  .ueh-cart-count { position:absolute; top:2px; right:2px; background:#FF3B30; color:#fff; font-size:10px; font-weight:700; width:18px; height:18px; border-radius:50%; display:flex; align-items:center; justify-content:center; }

  /* Mobile drawer — hidden by default on ALL screens */
  .ueh-mobile-drawer { display:none; position:fixed; top:0; left:0; bottom:0; width:300px; background:#fff; z-index:901; padding:24px; box-shadow:4px 0 24px rgba(0,0,0,0.1); overflow-y:auto; }
  .ueh-mobile-drawer.open { display:block; }
  .ueh-mobile-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:900; }
  .ueh-mobile-overlay.open { display:block; }
  .ueh-drawer-title { font-family:'Montserrat',sans-serif; font-size:18px; font-weight:800; margin-bottom:24px; color:#0A0A0A; text-transform:uppercase; }
  .ueh-drawer-link { display:block; padding:14px 0; font-size:13px; font-weight:700; color:#0A0A0A; text-decoration:none; text-transform:uppercase; letter-spacing:0.08em; border-bottom:1px solid #F5F5F0; }
  .ueh-drawer-link:hover { color:#FF3B30; }
  .ueh-drawer-section { margin-top:24px; }
  .ueh-drawer-section-title { font-size:10px; text-transform:uppercase; letter-spacing:0.16em; color:#999; font-weight:700; margin-bottom:12px; }

  .ueh-mobile { display:none; }
  @media(max-width:900px){
    .ueh-header-inner { display:none; }
    .ueh-mobile { display:block; }
    .ueh-mobile-bar { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 16px; }
    .ueh-mobile-menu-btn { background:none; border:none; padding:8px; cursor:pointer; color:#0A0A0A; }
    .ueh-mobile-menu-btn svg { width:24px; height:24px; stroke:currentColor; stroke-width:2; fill:none; }
    .ueh-mobile-logo { font-family:'Montserrat',sans-serif; font-size:18px; font-weight:800; color:#0A0A0A; text-transform:uppercase; text-decoration:none; }
    .ueh-mobile-logo img { max-height:32px; max-width:120px; }
    .ueh-mobile-actions { display:flex; align-items:center; gap:4px; }
  }
</style>

<div class="ueh-marquee" aria-hidden="true">
  <div class="ueh-marquee-track">
    <span class="ueh-marquee-item"><span class="dot">●</span>Free Shipping Over ₦15,000</span>
    <span class="ueh-marquee-item"><span class="dot">●</span>New Season Drop</span>
    <span class="ueh-marquee-item"><span class="dot">●</span>Authentic Guaranteed</span>
    <span class="ueh-marquee-item"><span class="dot">●</span>Pay On Delivery Available</span>
    <span class="ueh-marquee-item"><span class="dot">●</span>Free Shipping Over ₦15,000</span>
    <span class="ueh-marquee-item"><span class="dot">●</span>New Season Drop</span>
    <span class="ueh-marquee-item"><span class="dot">●</span>Authentic Guaranteed</span>
    <span class="ueh-marquee-item"><span class="dot">●</span>Pay On Delivery Available</span>
  </div>
</div>

<div class="ueh-header">
  <div class="ueh-header-inner">
    <nav class="ueh-nav-left">
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="ueh-nav-link">Shop</a>
      <?php if(!empty($categories)): foreach(array_slice($categories, 0, 4) as $cat): ?>
      <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="ueh-nav-link"><?= htmlspecialchars($cat->category_name); ?></a>
      <?php endforeach; endif; ?>
      <?php if($settings->allow_services ?? false): ?>
      <a href="<?= base_url('store/' . $slug . '/services'); ?>" class="ueh-nav-link">Services</a>
      <?php endif; ?>
    </nav>

    <a href="<?= base_url('store/' . $slug); ?>" class="ueh-logo">
      <?php if($logo): ?>
        <img src="<?= $logo; ?>" alt="<?= htmlspecialchars($store->store_name ?? 'Store'); ?>">
      <?php else: ?>
        <div>
          <div class="ueh-logo-text"><?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Store')); ?></div>
          <?php if(!empty($settings->store_subheadline)): ?>
          <div class="ueh-logo-tag"><?= htmlspecialchars($settings->store_subheadline); ?></div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </a>

    <div class="ueh-actions">
      <div class="ueh-search">
        <span class="ueh-search-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
        <input type="text" id="search-input" placeholder="Search..." onkeydown="if(event.key==='Enter')doSearch()">
      </div>
      <a href="<?= base_url('store/' . $slug . '/cart'); ?>" class="ueh-icon-btn" aria-label="Cart">
        <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span class="ueh-cart-count" id="cart-count">0</span>
      </a>
    </div>
  </div>
</div>

<div class="ueh-mobile">
  <div class="ueh-mobile-bar">
    <button class="ueh-mobile-menu-btn" onclick="document.getElementById('ueh-drawer').classList.add('open');document.getElementById('ueh-overlay').classList.add('open');" aria-label="Menu">
      <svg viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <a href="<?= base_url('store/' . $slug); ?>" class="ueh-mobile-logo">
      <?php if($logo): ?>
        <img src="<?= $logo; ?>" alt="<?= htmlspecialchars($store->store_name ?? 'Store'); ?>">
      <?php else: ?>
        <?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Store')); ?>
      <?php endif; ?>
    </a>
    <div class="ueh-mobile-actions">
      <a href="<?= base_url('store/' . $slug . '/cart'); ?>" class="ueh-icon-btn" aria-label="Cart">
        <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span class="ueh-cart-count" id="cart-count-mobile">0</span>
      </a>
    </div>
  </div>
</div>

<div class="ueh-mobile-overlay" id="ueh-overlay" onclick="document.getElementById('ueh-drawer').classList.remove('open');this.classList.remove('open');"></div>
<div class="ueh-mobile-drawer" id="ueh-drawer">
  <div class="ueh-drawer-title"><?= htmlspecialchars($store->store_name ?? 'Menu'); ?></div>
  <a href="<?= base_url('store/' . $slug); ?>" class="ueh-drawer-link" onclick="document.getElementById('ueh-drawer').classList.remove('open');document.getElementById('ueh-overlay').classList.remove('open');">Home</a>
  <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="ueh-drawer-link" onclick="document.getElementById('ueh-drawer').classList.remove('open');document.getElementById('ueh-overlay').classList.remove('open');">All Products</a>
  <?php if(!empty($categories)): ?>
  <div class="ueh-drawer-section">
    <div class="ueh-drawer-section-title">Categories</div>
    <?php foreach($categories as $cat): ?>
    <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="ueh-drawer-link" onclick="document.getElementById('ueh-drawer').classList.remove('open');document.getElementById('ueh-overlay').classList.remove('open');"><?= htmlspecialchars($cat->category_name); ?></a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <?php if($settings->allow_services ?? false): ?>
  <a href="<?= base_url('store/' . $slug . '/services'); ?>" class="ueh-drawer-link" onclick="document.getElementById('ueh-drawer').classList.remove('open');document.getElementById('ueh-overlay').classList.remove('open');">Services</a>
  <?php endif; ?>
  <div class="ueh-drawer-section">
    <div class="ueh-drawer-section-title">Contact</div>
    <?php if(!empty($settings->store_phone)): ?>
    <a href="tel:<?= preg_replace('/[^0-9+]/', '', $settings->store_phone); ?>" class="ueh-drawer-link"><?= htmlspecialchars($settings->store_phone); ?></a>
    <?php endif; ?>
    <?php if(!empty($settings->whatsapp_number)): ?>
    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number); ?>" target="_blank" class="ueh-drawer-link" style="color:#25D366;">WhatsApp Support</a>
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
