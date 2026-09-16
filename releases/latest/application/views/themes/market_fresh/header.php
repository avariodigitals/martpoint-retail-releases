<?php
/**
 * Market Fresh — Supermarket Header
 * Vibrant produce-forward header with fresh green and warm orange accents.
 * Clean Inter typography, category-driven nav, quick search and cart.
 */
$slug = $settings->store_slug ?? '';
$logo = $logo_url ?? null;
$social = $social_links ?? [];
?>
<style>
  :root {
    --mf-green:#16A34A;
    --mf-green-dark:#15803D;
    --mf-green-deep:#14532D;
    --mf-orange:#F97316;
    --mf-orange-dark:#EA580C;
    --mf-cream:#F0FDF4;
    --mf-soft:#DCFCE7;
    --mf-dark:#1C2B25;
    --mf-gray:#5B6B63;
    --mf-border:#E3EDE7;
    --mf-white:#FFFFFF;
  }

  .theme-market_fresh .mp-topbar,
  .theme-market_fresh .mp-announcement,
  .theme-market_fresh .mp-nav,
  .theme-market_fresh .mp-header { display:none !important; }
  .theme-market_fresh .mp-mobile-menu-btn { display:none !important; }
  .theme-market_fresh .mp-footer-space { height:0; }

  .mf-announce { background:var(--mf-green-deep); color:#fff; text-align:center; padding:11px 16px; font-family:'Inter',sans-serif; font-size:13px; font-weight:500; letter-spacing:0.01em; }
  .mf-announce a { color:#BBF7D0; text-decoration:underline; }
  .mf-announce strong { color:#FED7AA; font-weight:600; }

  .mf-header { background:#fff; border-bottom:1px solid var(--mf-border); position:sticky; top:0; z-index:100; }
  .mf-header-inner { max-width:1400px; margin:0 auto; display:flex; align-items:center; justify-content:space-between; gap:24px; padding:14px 24px; }
  .mf-nav-left { display:flex; align-items:center; gap:26px; flex:1; }
  .mf-nav-link { font-family:'Inter',sans-serif; font-size:14px; font-weight:600; color:var(--mf-dark); text-decoration:none; transition:color .2s; }
  .mf-nav-link:hover { color:var(--mf-green); }
  .mf-shop-menu { position:relative; }
  .mf-shop-menu > .mf-nav-link { display:flex; align-items:center; gap:6px; }
  .mf-shop-drop { position:absolute; top:calc(100% + 12px); left:0; background:#fff; border:1px solid var(--mf-border); border-radius:14px; box-shadow:0 20px 48px rgba(20,83,45,0.14); padding:10px; min-width:230px; display:none; z-index:150; }
  .mf-shop-menu:hover .mf-shop-drop, .mf-shop-menu:focus-within .mf-shop-drop { display:block; }
  .mf-shop-drop a { display:flex; align-items:center; justify-content:space-between; padding:10px 14px; font-family:'Inter',sans-serif; font-size:14px; font-weight:600; color:var(--mf-dark); text-decoration:none; border-radius:8px; }
  .mf-shop-drop a:hover { background:var(--mf-cream); color:var(--mf-green); }
  .mf-shop-drop a .cnt { font-size:11px; color:var(--mf-gray); font-weight:500; }
  .mf-shop-drop .all { border-top:1px solid var(--mf-border); margin-top:6px; padding-top:12px; color:var(--mf-green); }
  .mf-logo { text-decoration:none; display:flex; align-items:center; gap:10px; flex-shrink:0; }
  .mf-logo img { max-height:42px; max-width:180px; object-fit:contain; }
  .mf-logo-text { font-family:'Inter',sans-serif; font-size:24px; font-weight:800; color:var(--mf-green); letter-spacing:-0.02em; }
  .mf-logo-tag { font-family:'Inter',sans-serif; font-size:10px; color:var(--mf-orange); letter-spacing:0.12em; text-transform:uppercase; font-weight:600; }
  .mf-actions { display:flex; align-items:center; gap:8px; flex:1; justify-content:flex-end; }
  .mf-search { position:relative; max-width:280px; width:100%; }
  .mf-search input { width:100%; padding:11px 14px 11px 40px; border:1px solid var(--mf-border); border-radius:999px; font-family:'Inter',sans-serif; font-size:14px; outline:none; background:var(--mf-cream); transition:border-color .2s, background .2s; color:var(--mf-dark); }
  .mf-search input:focus { border-color:var(--mf-green); background:#fff; }
  .mf-search-icon { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--mf-green); }
  .mf-icon-btn { width:44px; height:44px; display:flex; align-items:center; justify-content:center; transition:background .2s; position:relative; text-decoration:none; color:var(--mf-dark); border-radius:999px; }
  .mf-icon-btn:hover { background:var(--mf-cream); }
  .mf-icon-btn svg { width:22px; height:22px; stroke:currentColor; stroke-width:1.7; fill:none; stroke-linecap:round; stroke-linejoin:round; }
  .mf-cart-count { position:absolute; top:2px; right:2px; background:var(--mf-orange); color:#fff; font-size:10px; font-weight:700; width:18px; height:18px; border-radius:50%; display:flex; align-items:center; justify-content:center; }
  .mf-fresh-badge { display:flex; align-items:center; gap:6px; background:var(--mf-soft); border:1px solid #BBF7D0; border-radius:999px; padding:6px 14px; font-family:'Inter',sans-serif; font-size:12px; font-weight:600; color:var(--mf-green-dark); white-space:nowrap; }
  .mf-fresh-badge svg { width:15px; height:15px; color:var(--mf-orange); }

  /* Mobile drawer */
  .mf-mobile-drawer { display:none; position:fixed; top:0; left:0; bottom:0; width:300px; background:#fff; z-index:901; padding:24px; box-shadow:4px 0 24px rgba(0,0,0,0.1); overflow-y:auto; }
  .mf-mobile-drawer.open { display:block; }
  .mf-mobile-overlay { display:none; position:fixed; inset:0; background:rgba(20,83,45,0.35); z-index:900; }
  .mf-mobile-overlay.open { display:block; }
  .mf-drawer-title { font-family:'Inter',sans-serif; font-size:22px; font-weight:800; margin-bottom:24px; color:var(--mf-green); letter-spacing:-0.02em; }
  .mf-drawer-link { display:block; padding:14px 0; font-family:'Inter',sans-serif; font-size:15px; font-weight:600; color:var(--mf-dark); text-decoration:none; border-bottom:1px solid var(--mf-border); }
  .mf-drawer-link:hover { color:var(--mf-green); }
  .mf-drawer-section { margin-top:24px; }
  .mf-drawer-section-title { font-family:'Inter',sans-serif; font-size:10px; text-transform:uppercase; letter-spacing:0.14em; color:var(--mf-orange); font-weight:700; margin-bottom:12px; }

  .mf-mobile { display:none; }
  @media(max-width:900px){
    .mf-header-inner { display:none; }
    .mf-mobile { display:block; }
    .mf-mobile-bar { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:14px 16px; }
    .mf-mobile-menu-btn { background:none; border:none; padding:8px; cursor:pointer; color:var(--mf-dark); }
    .mf-mobile-menu-btn svg { width:24px; height:24px; stroke:currentColor; stroke-width:2; fill:none; }
    .mf-mobile-logo { font-family:'Inter',sans-serif; font-size:20px; font-weight:800; color:var(--mf-green); text-decoration:none; letter-spacing:-0.02em; }
    .mf-mobile-logo img { max-height:32px; max-width:130px; }
    .mf-mobile-actions { display:flex; align-items:center; gap:4px; }
    .mf-mobile-search { padding:0 16px 12px; }
    .mf-mobile-search input { width:100%; padding:11px 14px 11px 40px; border:1px solid var(--mf-border); border-radius:999px; font-family:'Inter',sans-serif; font-size:14px; outline:none; background:var(--mf-cream); color:var(--mf-dark); box-sizing:border-box; }
    .mf-mobile-search { position:relative; }
    .mf-mobile-search-icon { position:absolute; left:30px; top:22px; transform:translateY(-50%); color:var(--mf-green); }
  }
</style>

<?php if(!empty($settings->announcement_bar)): ?>
<div class="mf-announce"><?= htmlspecialchars($settings->announcement_bar); ?></div>
<?php else: ?>
<div class="mf-announce">Fresh deals daily &middot; <strong>Free delivery</strong> on orders over &#8358;20,000 &middot; <a href="<?= base_url('store/' . $slug . '/products'); ?>">Shop Now &rarr;</a></div>
<?php endif; ?>

<div class="mf-header">
  <div class="mf-header-inner">
    <nav class="mf-nav-left">
      <div class="mf-shop-menu">
        <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="mf-nav-link">Shop <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg></a>
        <?php if(!empty($categories)): ?>
        <div class="mf-shop-drop">
          <?php foreach($categories as $cat): ?>
          <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>"><?= htmlspecialchars($cat->category_name); ?><?php if(!empty($cat->item_count)): ?><span class="cnt"><?= $cat->item_count; ?></span><?php endif; ?></a>
          <?php endforeach; ?>
          <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="all">View All Products &rarr;</a>
        </div>
        <?php endif; ?>
      </div>
      <?php if(!empty($categories)): foreach(array_slice($categories, 0, 4) as $cat): ?>
      <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="mf-nav-link"><?= htmlspecialchars($cat->category_name); ?></a>
      <?php endforeach; endif; ?>
      <?php if($settings->allow_services ?? false): ?>
      <a href="<?= base_url('store/' . $slug . '/services'); ?>" class="mf-nav-link">Services</a>
      <?php endif; ?>
    </nav>

    <a href="<?= base_url('store/' . $slug); ?>" class="mf-logo">
      <?php if($logo): ?>
        <img src="<?= $logo; ?>" alt="<?= htmlspecialchars($store->store_name ?? 'Market Fresh'); ?>">
      <?php else: ?>
        <div>
          <div class="mf-logo-text"><?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Market Fresh')); ?></div>
          <?php if(!empty($settings->store_subheadline)): ?>
          <div class="mf-logo-tag"><?= htmlspecialchars($settings->store_subheadline); ?></div>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </a>

    <div class="mf-actions">
      <div class="mf-fresh-badge">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2a8 8 0 0 0-8 8c0 4 3 7 8 9 5-2 8-5 8-9a8 8 0 0 0-8-8z"/><path d="M12 6v6"/></svg>
        Fresh Daily
      </div>
      <div class="mf-search">
        <span class="mf-search-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
        <input type="text" id="search-input" placeholder="Search groceries..." onkeydown="if(event.key==='Enter')doSearch()">
      </div>
      <a href="<?= base_url('store/' . $slug . '/cart'); ?>" class="mf-icon-btn" aria-label="Cart">
        <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span class="mf-cart-count" id="cart-count">0</span>
      </a>
    </div>
  </div>
</div>

<div class="mf-mobile">
  <div class="mf-mobile-bar">
    <button class="mf-mobile-menu-btn" onclick="document.getElementById('mf-drawer').classList.add('open');document.getElementById('mf-overlay').classList.add('open');" aria-label="Menu">
      <svg viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <a href="<?= base_url('store/' . $slug); ?>" class="mf-mobile-logo">
      <?php if($logo): ?>
        <img src="<?= $logo; ?>" alt="<?= htmlspecialchars($store->store_name ?? 'Market Fresh'); ?>">
      <?php else: ?>
        <?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Market Fresh')); ?>
      <?php endif; ?>
    </a>
    <div class="mf-mobile-actions">
      <a href="<?= base_url('store/' . $slug . '/cart'); ?>" class="mf-icon-btn" aria-label="Cart">
        <svg viewBox="0 0 24 24"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span class="mf-cart-count" id="cart-count-mobile">0</span>
      </a>
    </div>
  </div>
  <div class="mf-mobile-search">
    <span class="mf-mobile-search-icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
    <input type="text" id="search-input-mobile" placeholder="Search groceries..." onkeydown="if(event.key==='Enter'){const u='<?= base_url('store/' . $slug . '/products'); ?>';location.href=u+'?search='+encodeURIComponent(this.value);}">
  </div>
</div>

<div class="mf-mobile-overlay" id="mf-overlay" onclick="document.getElementById('mf-drawer').classList.remove('open');this.classList.remove('open');"></div>
<div class="mf-mobile-drawer" id="mf-drawer">
  <div class="mf-drawer-title"><?= htmlspecialchars($store->store_name ?? 'Menu'); ?></div>
  <a href="<?= base_url('store/' . $slug); ?>" class="mf-drawer-link" onclick="document.getElementById('mf-drawer').classList.remove('open');document.getElementById('mf-overlay').classList.remove('open');">Home</a>
  <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="mf-drawer-link" onclick="document.getElementById('mf-drawer').classList.remove('open');document.getElementById('mf-overlay').classList.remove('open');">Shop All</a>
  <?php if(!empty($categories)): ?>
  <div class="mf-drawer-section">
    <div class="mf-drawer-section-title">Categories</div>
    <?php foreach($categories as $cat): ?>
    <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="mf-drawer-link" onclick="document.getElementById('mf-drawer').classList.remove('open');document.getElementById('mf-overlay').classList.remove('open');"><?= htmlspecialchars($cat->category_name); ?></a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>
  <div class="mf-drawer-section">
    <div class="mf-drawer-section-title">Contact</div>
    <?php if(!empty($settings->store_phone)): ?>
    <a href="tel:<?= preg_replace('/[^0-9+]/', '', $settings->store_phone); ?>" class="mf-drawer-link"><?= htmlspecialchars($settings->store_phone); ?></a>
    <?php endif; ?>
    <?php if(!empty($settings->whatsapp_number)): ?>
    <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number); ?>" target="_blank" class="mf-drawer-link" style="color:#25D366;">WhatsApp</a>
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
