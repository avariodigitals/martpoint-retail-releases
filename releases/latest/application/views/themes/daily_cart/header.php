<?php
/**
 * Daily Cart — Mini Mart / Convenience Header
 * Friendly neighbourhood convenience store with deep blue and warm amber accents.
 * Poppins typography, rounded cards, quick essentials focus.
 */
$slug = $settings->store_slug ?? '';
$logo = $logo_url ?? null;
$social = $social_links ?? [];
?>
<style>
  :root {
    --dc-blue:#2563EB;
    --dc-blue-dark:#1D4ED8;
    --dc-blue-deep:#1E3A8A;
    --dc-amber:#F59E0B;
    --dc-amber-dark:#D97706;
    --dc-cream:#EFF6FF;
    --dc-soft:#DBEAFE;
    --dc-dark:#1E293B;
    --dc-gray:#64748B;
    --dc-border:#E2E8F0;
    --dc-white:#FFFFFF;
  }

  .theme-daily_cart .mp-topbar,
  .theme-daily_cart .mp-announcement,
  .theme-daily_cart .mp-nav,
  .theme-daily_cart .mp-header { display:none !important; }
  .theme-daily_cart .mp-mobile-menu-btn { display:none !important; }
  .theme-daily_cart .mp-footer-space { height:0; }

  .dc-announce { background:var(--dc-amber); color:var(--dc-dark); text-align:center; padding:11px 16px; font-family:'Poppins',sans-serif; font-size:13px; font-weight:600; letter-spacing:0.01em; }
  .dc-announce a { color:var(--dc-blue-deep); text-decoration:underline; font-weight:700; }
  .dc-announce strong { color:var(--dc-blue-deep); font-weight:700; }

  .dc-header { background:#fff; border-bottom:1px solid var(--dc-border); position:sticky; top:0; z-index:100; }
  .dc-header-inner { max-width:1400px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; gap:24px; padding:14px 24px; }
  .dc-nav-left { display:flex; align-items:center; gap:24px; flex:1; }
  .dc-nav-link { font-family:'Poppins',sans-serif; font-size:14px; font-weight:600; color:var(--dc-dark); text-decoration:none; transition:color .2s; }
  .dc-nav-link:hover { color:var(--dc-blue); }
  .dc-shop-menu { position:relative; }
  .dc-shop-menu > .dc-nav-link { display:flex; align-items:center; gap:6px; }
  .dc-shop-drop { position:absolute; top:calc(100% + 12px); left:0; background:#fff; border:1px solid var(--dc-border); border-radius:14px; box-shadow:0 20px 48px rgba(30,58,138,0.14); padding:10px; min-width:230px; display:none; z-index:150; }
  .dc-shop-menu:hover .dc-shop-drop, .dc-shop-menu:focus-within .dc-shop-drop { display:block; }
  .dc-shop-drop a { display:flex; align-items:center; justify-content:space-between; padding:10px 14px; font-family:'Poppins',sans-serif; font-size:14px; font-weight:600; color:var(--dc-dark); text-decoration:none; border-radius:8px; }
  .dc-shop-drop a:hover { background:var(--dc-cream); color:var(--dc-blue); }
  .dc-shop-drop a .cnt { font-size:11px; color:var(--dc-gray); font-weight:500; }
  .dc-shop-drop .all { border-top:1px solid var(--dc-border); margin-top:6px; padding-top:12px; color:var(--dc-blue); }
  .dc-logo { text-decoration:none; display:flex; align-items:center; gap:10px; flex-shrink:0; }
  .dc-logo img { max-height:42px; max-width:180px; object-fit:contain; }
  .dc-logo-text { font-family:'Poppins',sans-serif; font-size:24px; font-weight:800; color:var(--dc-blue); letter-spacing:-0.02em; }
  .dc-logo-tag { font-family:'Poppins',sans-serif; font-size:10px; color:var(--dc-amber); letter-spacing:0.12em; text-transform:uppercase; font-weight:600; }
  .dc-actions { display:flex; align-items:center; gap:8px; flex:1; justify-content:flex-end; }
  .dc-search { position:relative; max-width:280px; width:100%; }
  .dc-search input { width:100%; padding:11px 14px 11px 40px; border:1px solid var(--dc-border); border-radius:999px; font-family:'Poppins',sans-serif; font-size:14px; outline:none; background:var(--dc-cream); transition:border-color .2s, background .2s; color:var(--dc-dark); }
  .dc-search input:focus { border-color:var(--dc-blue); background:#fff; }
  .dc-search-icon { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--dc-blue); }
  .dc-icon-btn { width:44px; height:44px; display:flex; align-items:center; justify-content:center; transition:background .2s; position:relative; text-decoration:none; color:var(--dc-dark); border-radius:999px; }
  .dc-icon-btn:hover { background:var(--dc-cream); }
  .dc-icon-btn svg { width:22px; height:22px; stroke:currentColor; stroke-width:1.7; fill:none; stroke-linecap:round; stroke-linejoin:round; }
  .dc-cart-count { position:absolute; top:2px; right:2px; background:var(--dc-amber); color:#fff; font-size:10px; font-weight:700; width:18px; height:18px; border-radius:50%; display:flex; align-items:center; justify-content:center; }
  .dc-fresh-badge { display:flex; align-items:center; gap:6px; background:var(--dc-soft); border:1px solid #BFDBFE; border-radius:999px; padding:6px 14px; font-family:'Poppins',sans-serif; font-size:12px; font-weight:600; color:var(--dc-blue-dark); white-space:nowrap; }
  .dc-fresh-badge svg { width:15px; height:15px; color:var(--dc-amber); }

  .dc-mobile-drawer { display:none; position:fixed; top:0; left:0; bottom:0; width:300px; background:#fff; z-index:901; padding:24px; box-shadow:4px 0 24px rgba(0,0,0,0.1); overflow-y:auto; }
  .dc-mobile-drawer.open { display:block; }
  .dc-mobile-overlay { display:none; position:fixed; inset:0; background:rgba(30,58,138,0.35); z-index:900; }
  .dc-mobile-overlay.open { display:block; }
  .dc-drawer-title { font-family:'Poppins',sans-serif; font-size:22px; font-weight:800; margin-bottom:24px; color:var(--dc-blue); letter-spacing:-0.02em; }
  .dc-drawer-link { display:block; padding:14px 0; font-family:'Poppins',sans-serif; font-size:15px; font-weight:600; color:var(--dc-dark); text-decoration:none; border-bottom:1px solid var(--dc-border); }
  .dc-drawer-link:hover { color:var(--dc-blue); }
  .dc-drawer-section { margin-top:24px; }
  .dc-drawer-section-title { font-family:'Poppins',sans-serif; font-size:10px; text-transform:uppercase; letter-spacing:0.14em; color:var(--dc-amber); font-weight:700; margin-bottom:12px; }

  .dc-mobile { display:none; }
  @media(max-width:900px){
    .dc-header-inner { display:none; }
    .dc-mobile { display:block; }
    .dc-mobile-bar { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 16px; }
    .dc-mobile-menu-btn { background:none; border:none; padding:8px; cursor:pointer; color:var(--dc-dark); }
    .dc-mobile-menu-btn svg { width:24px; height:24px; stroke:currentColor; stroke-width:2; fill:none; }
    .dc-mobile-logo { font-family:'Poppins',sans-serif; font-size:20px; font-weight:800; color:var(--dc-blue); text-decoration:none; letter-spacing:-0.02em; }
    .dc-mobile-logo img { max-height:32px; max-width:130px; }
    .dc-mobile-actions { display:flex; align-items:center; gap:4px; }
    .dc-mobile-search { padding:0 16px 12px; position:relative; }
    .dc-mobile-search input { width:100%; padding:11px 14px 11px 40px; border:1px solid var(--dc-border); border-radius:999px; font-family:'Poppins',sans-serif; font-size:14px; outline:none; background:var(--dc-cream); color:var(--dc-dark); box-sizing:border-box; }
    .dc-mobile-search-icon { position:absolute; left:30px; top:22px; transform:translateY(-50%); color:var(--dc-blue); }
  }
</style>

<?php if(!empty($settings->announcement_bar)): ?>
<div class="dc-announce"><?= htmlspecialchars($settings->announcement_bar); ?></div>
<?php else: ?>
<div class="dc-announce">Quick essentials, daily &middot; <strong>Open late</strong> every day &middot; <a href="<?= base_url('store/' . $slug . '/products'); ?>">Shop Now &rarr;</a></div>
<?php endif; ?>

<div class="dc-header">
  <div class="dc-header-inner">
    <nav class="dc-nav-left">
      <div class="dc-shop-menu">
        <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="dc-nav-link">Shop <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></a>
        <?php if(!empty($categories)): ?>
        <div class="dc-shop-drop">
          <?php foreach($categories as $cat): ?>
          <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>"><?= htmlspecialchars($cat->category_name); ?><?php if(!empty($cat->item_count)): ?><span class="cnt"><?= $cat->item_count; ?></span><?php endif; ?></a>
          <?php endforeach; ?>
          <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="all">View All Products &rarr;</a>
        </div>
        <?php endif; ?>
      </div>
      <?php if(!empty($categories)): foreach(array_slice($categories, 0, 4) as $cat): ?>
      <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="dc-nav-link"><?= htmlspecialchars($cat->category_name); ?></a>
      <?php endforeach; endif; ?>
      <?php if($settings->allow_services ?? false): ?>
      <a href="<?= base_url('store/' . $slug . '/services'); ?>" class="dc-nav-link">Services</a>
      <?php endif; ?>
    </nav>

    <a href="<?= base_url('store/' . $slug); ?>" class="dc-logo">
      <?php if($logo): ?>
        <img src="<?= $logo; ?>" alt="<?= htmlspecialchars($store->store_name ?? 'Daily Cart'); ?>">
      <?php else: ?>
        <div>
          <div class="dc-logo-text"><?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Daily Cart')); ?></div>
          <?php if(!empty($settings->store_subheadline)): ?>
          <div class="dc-logo-tag"><?= htmlspecialchars($settings->store_subheadline); ?></div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </a>

    <div class="dc-actions">
      <div class="dc-fresh-badge">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        Open Daily
      </div>
      <div class="dc-search">
        <span class="dc-search-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
        <input type="text" id="search-input" placeholder="Search essentials..." onkeydown="if(event.key==='Enter')doSearch()">
      </div>
      <a href="<?= base_url('store/' . $slug . '/cart'); ?>" class="dc-icon-btn" aria-label="Cart">
        <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span class="dc-cart-count" id="cart-count">0</span>
      </a>
    </div>
  </div>
</div>

<div class="dc-mobile">
  <div class="dc-mobile-bar">
    <button class="dc-mobile-menu-btn" onclick="document.getElementById('dc-drawer').classList.add('open');document.getElementById('dc-overlay').classList.add('open');" aria-label="Menu">
      <svg viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <a href="<?= base_url('store/' . $slug); ?>" class="dc-mobile-logo">
      <?php if($logo): ?>
        <img src="<?= $logo; ?>" alt="<?= htmlspecialchars($store->store_name ?? 'Daily Cart'); ?>">
      <?php else: ?>
        <?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Daily Cart')); ?>
      <?php endif; ?>
    </a>
    <div class="dc-mobile-actions">
      <a href="<?= base_url('store/' . $slug . '/cart'); ?>" class="dc-icon-btn" aria-label="Cart">
        <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span class="dc-cart-count" id="cart-count-mobile">0</span>
      </a>
    </div>
  </div>
  <div class="dc-mobile-search">
    <span class="dc-mobile-search-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
    <input type="text" id="search-input-mobile" placeholder="Search essentials..." onkeydown="if(event.key==='Enter'){const u='<?= base_url('store/' . $slug . '/products'); ?>';location.href=u+'?search='+encodeURIComponent(this.value);}">
  </div>
</div>

<div class="dc-mobile-overlay" id="dc-overlay" onclick="document.getElementById('dc-drawer').classList.remove('open');this.classList.remove('open');"></div>
<div class="dc-mobile-drawer" id="dc-drawer">
  <div class="dc-drawer-title"><?= htmlspecialchars($store->store_name ?? 'Menu'); ?></div>
  <a href="<?= base_url('store/' . $slug); ?>" class="dc-drawer-link" onclick="document.getElementById('dc-drawer').classList.remove('open');document.getElementById('dc-overlay').classList.remove('open');">Home</a>
  <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="dc-drawer-link" onclick="document.getElementById('dc-drawer').classList.remove('open');document.getElementById('dc-overlay').classList.remove('open');">Shop All</a>
  <?php if(!empty($categories)): ?>
  <div class="dc-drawer-section">
    <div class="dc-drawer-section-title">Categories</div>
    <?php foreach($categories as $cat): ?>
    <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="dc-drawer-link" onclick="document.getElementById('dc-drawer').classList.remove('open');document.getElementById('dc-overlay').classList.remove('open');"><?= htmlspecialchars($cat->category_name); ?></a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <div class="dc-drawer-section">
    <div class="dc-drawer-section-title">Contact</div>
    <?php if(!empty($settings->store_phone)): ?>
    <a href="tel:<?= preg_replace('/[^0-9+]/', '', $settings->store_phone); ?>" class="dc-drawer-link"><?= htmlspecialchars($settings->store_phone); ?></a>
    <?php endif; ?>
    <?php if(!empty($settings->whatsapp_number)): ?>
    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number); ?>" target="_blank" class="dc-drawer-link" style="color:#25D366;">WhatsApp</a>
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
