<?php
/**
 * Pharma Care — Pharmacy Header
 * Warm community pharmacy header with deep teal, amber accents,
 * Lora serif logo and a trusted, neighbourhood feel.
 */
$slug = $settings->store_slug ?? '';
$logo = $logo_url ?? null;
$social = $social_links ?? [];
?>
<style>
  :root {
    --pcr-teal:#0F766E;
    --pcr-teal-dark:#115E59;
    --pcr-amber:#D97706;
    --pcr-amber-dark:#B45309;
    --pcr-cream:#FFFBF5;
    --pcr-cream-dark:#FEF7ED;
    --pcr-dark:#1C1917;
    --pcr-gray:#57534E;
    --pcr-border:#E7E5E4;
    --pcr-white:#FFFFFF;
  }

  .theme-pharma_care .mp-topbar,
  .theme-pharma_care .mp-announcement,
  .theme-pharma_care .mp-nav,
  .theme-pharma_care .mp-header { display:none !important; }
  .theme-pharma_care .mp-mobile-menu-btn { display:none !important; }
  .theme-pharma_care .mp-footer-space { height:0; }

  .pcr-announce { background:var(--pcr-teal); color:#fff; text-align:center; padding:12px 16px; font-family:'Inter',sans-serif; font-size:13px; font-weight:500; letter-spacing:0.02em; }
  .pcr-announce a { color:#FEF3C7; text-decoration:underline; }
  .pcr-header { background:#FFFBF5; border-bottom:1px solid #E7E5E4; position:sticky; top:0; z-index:100; }
  .pcr-header-inner { max-width:1400px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; gap:24px; padding:16px 24px; }
  .pcr-nav-left { display:flex; align-items:center; gap:28px; flex:1; }
  .pcr-nav-link { font-family:'Inter',sans-serif; font-size:13px; font-weight:600; color:#1C1917; text-decoration:none; transition:color .2s; }
  .pcr-nav-link:hover { color:#0F766E; }
  .pcr-logo { text-decoration:none; display:flex; align-items:center; gap:10px; flex-shrink:0; }
  .pcr-logo img { max-height:40px; max-width:180px; object-fit:contain; }
  .pcr-logo-text { font-family:'Lora',serif; font-size:26px; font-weight:700; color:#0F766E; letter-spacing:-0.01em; }
  .pcr-logo-tag { font-family:'Inter',sans-serif; font-size:10px; color:#D97706; letter-spacing:0.12em; text-transform:uppercase; font-weight:600; }
  .pcr-actions { display:flex; align-items:center; gap:8px; flex:1; justify-content:flex-end; }
  .pcr-search { position:relative; max-width:240px; width:100%; }
  .pcr-search input { width:100%; padding:10px 14px 10px 38px; border:1px solid #E7E5E4; border-radius:8px; font-family:'Inter',sans-serif; font-size:13px; outline:none; background:#fff; transition:border-color .2s; }
  .pcr-search input:focus { border-color:#0F766E; }
  .pcr-search-icon { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#0F766E; }
  .pcr-icon-btn { width:44px; height:44px; display:flex; align-items:center; justify-content:center; transition:background .2s; position:relative; text-decoration:none; color:#1C1917; border-radius:8px; }
  .pcr-icon-btn:hover { background:#F5F5F4; }
  .pcr-icon-btn svg { width:22px; height:22px; stroke:currentColor; stroke-width:1.6; fill:none; stroke-linecap:round; stroke-linejoin:round; }
  .pcr-cart-count { position:absolute; top:2px; right:2px; background:#D97706; color:#fff; font-size:10px; font-weight:700; width:18px; height:18px; border-radius:50%; display:flex; align-items:center; justify-content:center; }
  .pcr-care-badge { display:flex; align-items:center; gap:6px; background:rgba(15,118,110,0.08); border:1px solid rgba(15,118,110,0.15); border-radius:8px; padding:6px 12px; font-family:'Inter',sans-serif; font-size:11px; font-weight:600; color:#0F766E; white-space:nowrap; }
  .pcr-care-badge svg { width:14px; height:14px; color:#D97706; }

  /* Mobile drawer */
  .pcr-mobile-drawer { display:none; position:fixed; top:0; left:0; bottom:0; width:300px; background:#FFFBF5; z-index:901; padding:24px; box-shadow:4px 0 24px rgba(0,0,0,0.1); overflow-y:auto; }
  .pcr-mobile-drawer.open { display:block; }
  .pcr-mobile-overlay { display:none; position:fixed; inset:0; background:rgba(0,0,0,0.35); z-index:900; }
  .pcr-mobile-overlay.open { display:block; }
  .pcr-drawer-title { font-family:'Lora',serif; font-size:24px; font-weight:700; margin-bottom:24px; color:#0F766E; }
  .pcr-drawer-link { display:block; padding:14px 0; font-family:'Inter',sans-serif; font-size:14px; font-weight:600; color:#1C1917; text-decoration:none; border-bottom:1px solid #E7E5E4; }
  .pcr-drawer-link:hover { color:#0F766E; }
  .pcr-drawer-section { margin-top:24px; }
  .pcr-drawer-section-title { font-family:'Inter',sans-serif; font-size:10px; text-transform:uppercase; letter-spacing:0.14em; color:#D97706; font-weight:700; margin-bottom:12px; }

  .pcr-mobile { display:none; }
  @media(max-width:900px){
    .pcr-header-inner { display:none; }
    .pcr-mobile { display:block; }
    .pcr-mobile-bar { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 16px; }
    .pcr-mobile-menu-btn { background:none; border:none; padding:8px; cursor:pointer; color:#1C1917; }
    .pcr-mobile-menu-btn svg { width:24px; height:24px; stroke:currentColor; stroke-width:2; fill:none; }
    .pcr-mobile-logo { font-family:'Lora',serif; font-size:22px; font-weight:700; color:#0F766E; text-decoration:none; }
    .pcr-mobile-logo img { max-height:30px; max-width:130px; }
    .pcr-mobile-actions { display:flex; align-items:center; gap:4px; }
  }
</style>

<?php if(!empty($settings->announcement_bar)): ?>
<div class="pcr-announce"><?= htmlspecialchars($settings->announcement_bar); ?></div>
<?php else: ?>
<div class="pcr-announce">Caring for your family's health since day one &middot; Free Delivery on orders over &#8358;25,000 &middot; <a href="<?= base_url('store/' . $slug . '/products'); ?>">Shop Now &rarr;</a></div>
<?php endif; ?>

<div class="pcr-header">
  <div class="pcr-header-inner">
    <nav class="pcr-nav-left">
      <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pcr-nav-link">Shop</a>
      <?php if(!empty($categories)): foreach(array_slice($categories, 0, 4) as $cat): ?>
      <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="pcr-nav-link"><?= htmlspecialchars($cat->category_name); ?></a>
      <?php endforeach; endif; ?>
      <?php if($settings->allow_services ?? false): ?>
      <a href="<?= base_url('store/' . $slug . '/services'); ?>" class="pcr-nav-link">Services</a>
      <?php endif; ?>
    </nav>

    <a href="<?= base_url('store/' . $slug); ?>" class="pcr-logo">
      <?php if($logo): ?>
        <img src="<?= $logo; ?>" alt="<?= htmlspecialchars($store->store_name ?? 'Pharma Care'); ?>">
      <?php else: ?>
        <div>
          <div class="pcr-logo-text"><?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Pharma Care')); ?></div>
          <?php if(!empty($settings->store_subheadline)): ?>
          <div class="pcr-logo-tag"><?= htmlspecialchars($settings->store_subheadline); ?></div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </a>

    <div class="pcr-actions">
      <div class="pcr-care-badge">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
        Family Care
      </div>
      <div class="pcr-search">
        <span class="pcr-search-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
        <input type="text" id="search-input" placeholder="Search health products..." onkeydown="if(event.key==='Enter')doSearch()">
      </div>
      <a href="<?= base_url('store/' . $slug . '/cart'); ?>" class="pcr-icon-btn" aria-label="Cart">
        <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span class="pcr-cart-count" id="cart-count">0</span>
      </a>
    </div>
  </div>
</div>

<div class="pcr-mobile">
  <div class="pcr-mobile-bar">
    <button class="pcr-mobile-menu-btn" onclick="document.getElementById('pcr-drawer').classList.add('open');document.getElementById('pcr-overlay').classList.add('open');" aria-label="Menu">
      <svg viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <a href="<?= base_url('store/' . $slug); ?>" class="pcr-mobile-logo">
      <?php if($logo): ?>
        <img src="<?= $logo; ?>" alt="<?= htmlspecialchars($store->store_name ?? 'Pharma Care'); ?>">
      <?php else: ?>
        <?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Pharma Care')); ?>
      <?php endif; ?>
    </a>
    <div class="pcr-mobile-actions">
      <a href="<?= base_url('store/' . $slug . '/cart'); ?>" class="pcr-icon-btn" aria-label="Cart">
        <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span class="pcr-cart-count" id="cart-count-mobile">0</span>
      </a>
    </div>
  </div>
</div>

<div class="pcr-mobile-overlay" id="pcr-overlay" onclick="document.getElementById('pcr-drawer').classList.remove('open');this.classList.remove('open');"></div>
<div class="pcr-mobile-drawer" id="pcr-drawer">
  <div class="pcr-drawer-title"><?= htmlspecialchars($store->store_name ?? 'Menu'); ?></div>
  <a href="<?= base_url('store/' . $slug); ?>" class="pcr-drawer-link" onclick="document.getElementById('pcr-drawer').classList.remove('open');document.getElementById('pcr-overlay').classList.remove('open');">Home</a>
  <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="pcr-drawer-link" onclick="document.getElementById('pcr-drawer').classList.remove('open');document.getElementById('pcr-overlay').classList.remove('open');">Shop</a>
  <?php if(!empty($categories)): ?>
  <div class="pcr-drawer-section">
    <div class="pcr-drawer-section-title">Categories</div>
    <?php foreach($categories as $cat): ?>
    <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="pcr-drawer-link" onclick="document.getElementById('pcr-drawer').classList.remove('open');document.getElementById('pcr-overlay').classList.remove('open');"><?= htmlspecialchars($cat->category_name); ?></a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <div class="pcr-drawer-section">
    <div class="pcr-drawer-section-title">Contact</div>
    <?php if(!empty($settings->store_phone)): ?>
    <a href="tel:<?= preg_replace('/[^0-9+]/', '', $settings->store_phone); ?>" class="pcr-drawer-link"><?= htmlspecialchars($settings->store_phone); ?></a>
    <?php endif; ?>
    <?php if(!empty($settings->whatsapp_number)): ?>
    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number); ?>" target="_blank" class="pcr-drawer-link" style="color:#25D366;">WhatsApp</a>
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
