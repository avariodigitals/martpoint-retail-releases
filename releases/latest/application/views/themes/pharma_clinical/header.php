<?php
/**
 * Pharma Clinical — Pharmacy Header
 * Clean, clinical header with medical blue accents and trust indicators.
 */
$slug = $settings->store_slug ?? '';
$logo = $logo_url ?? null;
$social = $social_links ?? [];
?>
<style>
  .theme-pharma_clinical .mp-topbar,
  .theme-pharma_clinical .mp-announcement,
  .theme-pharma_clinical .mp-nav,
  .theme-pharma_clinical .mp-header { display:none !important; }
  .theme-pharma_clinical .mp-mobile-menu-btn { display:none !important; }
  .theme-pharma_clinical .mp-footer-space { height:0; }

  .pch-announce { background:#0F172A; color:#93C5FD; text-align:center; padding:12px 16px; font-family:'Inter',sans-serif; font-size:13px; font-weight:500; letter-spacing:0.02em; }
  .pch-announce a { color:#fff; text-decoration:underline; }
  .pch-header { background:#fff; border-bottom:1px solid #E2E8F0; position:sticky; top:0; z-index:100; }
  .pch-header-inner { max-width:1400px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; gap:24px; padding:16px 24px; }
  .pch-nav-left { display:flex; align-items:center; gap:28px; flex:1; }
  .pch-nav-link { font-family:'Inter',sans-serif; font-size:13px; font-weight:600; color:#0F172A; text-decoration:none; text-transform:uppercase; letter-spacing:0.08em; transition:color .2s; }
  .pch-nav-link:hover { color:#005EB8; }
  .pch-logo { text-decoration:none; display:flex; align-items:center; gap:10px; flex-shrink:0; }
  .pch-logo img { max-height:42px; max-width:180px; object-fit:contain; }
  .pch-logo-text { font-family:'Inter',sans-serif; font-size:24px; font-weight:800; color:#005EB8; letter-spacing:-0.02em; }
  .pch-logo-tag { font-family:'Inter',sans-serif; font-size:10px; color:#00A86B; letter-spacing:0.12em; text-transform:uppercase; font-weight:600; }
  .pch-actions { display:flex; align-items:center; gap:8px; flex:1; justify-content:flex-end; }
  .pch-search { position:relative; max-width:240px; width:100%; }
  .pch-search input { width:100%; padding:10px 14px 10px 38px; border:1px solid #E2E8F0; border-radius:8px; font-family:'Inter',sans-serif; font-size:13px; outline:none; background:#F8FAFC; transition:border-color .2s; }
  .pch-search input:focus { border-color:#005EB8; background:#fff; }
  .pch-search-icon { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#005EB8; }
  .pch-icon-btn { width:44px; height:44px; display:flex; align-items:center; justify-content:center; transition:background .2s; position:relative; text-decoration:none; color:#0F172A; border-radius:8px; }
  .pch-icon-btn:hover { background:#F1F5F9; }
  .pch-icon-btn svg { width:22px; height:22px; stroke:currentColor; stroke-width:1.6; fill:none; stroke-linecap:round; stroke-linejoin:round; }
  .pch-cart-count { position:absolute; top:2px; right:2px; background:#005EB8; color:#fff; font-size:10px; font-weight:700; width:18px; height:18px; border-radius:50%; display:flex; align-items:center; justify-content:center; }
  .pch-trust-badge { display:flex; align-items:center; gap:6px; background:#F0F9FF; border:1px solid #BAE6FD; border-radius:6px; padding:6px 12px; font-family:'Inter',sans-serif; font-size:11px; font-weight:600; color:#005EB8; white-space:nowrap; }
  .pch-trust-badge svg { width:14px; height:14px; color:#00A86B; }

  /* Mobile drawer */
  .pch-mobile-drawer { display:none; position:fixed; top:0; left:0; bottom:0; width:300px; background:#fff; z-index:901; padding:24px; box-shadow:4px 0 24px rgba(0,0,0,0.1); overflow-y:auto; }
  .pch-mobile-drawer.open { display:block; }
  .pch-mobile-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.4); z-index:900; }
  .pch-mobile-overlay.open { display:block; }
  .pch-drawer-title { font-family:'Inter',sans-serif; font-size:22px; font-weight:800; margin-bottom:24px; color:#005EB8; }
  .pch-drawer-link { display:block; padding:14px 0; font-family:'Inter',sans-serif; font-size:14px; font-weight:600; color:#0F172A; text-decoration:none; text-transform:uppercase; letter-spacing:0.08em; border-bottom:1px solid #E2E8F0; }
  .pch-drawer-link:hover { color:#005EB8; }
  .pch-drawer-section { margin-top:24px; }
  .pch-drawer-section-title { font-family:'Inter',sans-serif; font-size:10px; text-transform:uppercase; letter-spacing:0.16em; color:#00A86B; font-weight:700; margin-bottom:12px; }

  .pch-mobile { display:none; }
  @media(max-width:900px){
    .pch-header-inner { display:none; }
    .pch-mobile { display:block; }
    .pch-mobile-bar { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 16px; }
    .pch-mobile-menu-btn { background:none; border:none; padding:8px; cursor:pointer; color:#0F172A; }
    .pch-mobile-menu-btn svg { width:24px; height:24px; stroke:currentColor; stroke-width:2; fill:none; }
    .pch-mobile-logo { font-family:'Inter',sans-serif; font-size:20px; font-weight:800; color:#005EB8; text-decoration:none; }
    .pch-mobile-logo img { max-height:32px; max-width:130px; }
    .pch-mobile-actions { display:flex; align-items:center; gap:4px; }
  }
</style>

<?php if(!empty($settings->announcement_bar)): ?>
<div class="pch-announce"><?= htmlspecialchars($settings->announcement_bar); ?></div>
<?php else: ?>
<div class="pch-announce">Licensed Pharmacy &middot; Genuine Medicines &middot; Free Delivery on orders over &#8358;25,000 &middot; <a href="<?= base_url('store/' . $slug . '/products'); ?>">Shop Now &rarr;</a></div>
<?php endif; ?>

<div class="pch-header">
  <div class="pch-header-inner">
    <nav class="pch-nav-left">
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pch-nav-link">Shop</a>
      <?php if(!empty($categories)): foreach(array_slice($categories, 0, 4) as $cat): ?>
      <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="pch-nav-link"><?= htmlspecialchars($cat->category_name); ?></a>
      <?php endforeach; endif; ?>
      <?php if($settings->allow_services ?? false): ?>
      <a href="<?= base_url('store/' . $slug . '/services'); ?>" class="pch-nav-link">Services</a>
      <?php endif; ?>
    </nav>

    <a href="<?= base_url('store/' . $slug); ?>" class="pch-logo">
      <?php if($logo): ?>
        <img src="<?= $logo; ?>" alt="<?= htmlspecialchars($store->store_name ?? 'Pharmacy'); ?>">
      <?php else: ?>
        <div>
          <div class="pch-logo-text"><?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Pharmacy')); ?></div>
          <?php if(!empty($settings->store_subheadline)): ?>
          <div class="pch-logo-tag"><?= htmlspecialchars($settings->store_subheadline); ?></div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </a>

    <div class="pch-actions">
      <div class="pch-trust-badge">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
        Licensed Pharmacy
      </div>
      <div class="pch-search">
        <span class="pch-search-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
        <input type="text" id="search-input" placeholder="Search medicines..." onkeydown="if(event.key==='Enter')doSearch()">
      </div>
      <a href="<?= base_url('store/' . $slug . '/cart'); ?>" class="pch-icon-btn" aria-label="Cart">
        <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span class="pch-cart-count" id="cart-count">0</span>
      </a>
    </div>
  </div>
</div>

<div class="pch-mobile">
  <div class="pch-mobile-bar">
    <button class="pch-mobile-menu-btn" onclick="document.getElementById('pch-drawer').classList.add('open');document.getElementById('pch-overlay').classList.add('open');" aria-label="Menu">
      <svg viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <a href="<?= base_url('store/' . $slug); ?>" class="pch-mobile-logo">
      <?php if($logo): ?>
        <img src="<?= $logo; ?>" alt="<?= htmlspecialchars($store->store_name ?? 'Pharmacy'); ?>">
      <?php else: ?>
        <?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Pharmacy')); ?>
      <?php endif; ?>
    </a>
    <div class="pch-mobile-actions">
      <a href="<?= base_url('store/' . $slug . '/cart'); ?>" class="pch-icon-btn" aria-label="Cart">
        <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span class="pch-cart-count" id="cart-count-mobile">0</span>
      </a>
    </div>
  </div>
</div>

<div class="pch-mobile-overlay" id="pch-overlay" onclick="document.getElementById('pch-drawer').classList.remove('open');this.classList.remove('open');"></div>
<div class="pch-mobile-drawer" id="pch-drawer">
  <div class="pch-drawer-title"><?= htmlspecialchars($store->store_name ?? 'Menu'); ?></div>
  <a href="<?= base_url('store/' . $slug); ?>" class="pch-drawer-link" onclick="document.getElementById('pch-drawer').classList.remove('open');document.getElementById('pch-overlay').classList.remove('open');">Home</a>
  <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pch-drawer-link" onclick="document.getElementById('pch-drawer').classList.remove('open');document.getElementById('pch-overlay').classList.remove('open');">Shop</a>
  <?php if(!empty($categories)): ?>
  <div class="pch-drawer-section">
    <div class="pch-drawer-section-title">Categories</div>
    <?php foreach($categories as $cat): ?>
    <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="pch-drawer-link" onclick="document.getElementById('pch-drawer').classList.remove('open');document.getElementById('pch-overlay').classList.remove('open');"><?= htmlspecialchars($cat->category_name); ?></a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <div class="pch-drawer-section">
    <div class="pch-drawer-section-title">Contact</div>
    <?php if(!empty($settings->store_phone)): ?>
    <a href="tel:<?= preg_replace('/[^0-9+]/', '', $settings->store_phone); ?>" class="pch-drawer-link"><?= htmlspecialchars($settings->store_phone); ?></a>
    <?php endif; ?>
    <?php if(!empty($settings->whatsapp_number)): ?>
    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number); ?>" target="_blank" class="pch-drawer-link" style="color:#25D366;">WhatsApp</a>
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
