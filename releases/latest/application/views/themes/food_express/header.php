<?php
$slug = $settings->store_slug ?? '';
$headerProfile = function_exists('mp_get_store_profile') ? mp_get_store_profile((int)($settings->store_id ?? 0)) : [];
$headerIndustry = strtolower(trim((string)($headerProfile['industry_type'] ?? '')));
$headerIsRestaurant = in_array($headerIndustry, ['restaurant','fast_food','food_delivery','cafe','bakery','pizza_shop','shawarma','juice_bar','buka','canteen'], true);
$logo = $headerIsRestaurant ? null : ($logo_url ?? null);
$fxBrand = $fxCopy('brand_line') ?: 'Good food. Great mood.';
$fxTagline = $fxCopy('brand_tagline') ?: 'From first craving to first bite.';
$fxStoreName = $store->store_name ?? ($settings->store_headline ?: 'Food Express');
$fxLocationParts = array_filter([
  trim((string)($settings->store_city ?? '')),
  trim((string)($settings->store_state ?? '')),
  trim((string)($settings->store_address ?? ($store->address ?? ''))),
]);
$fxLocation = $fxLocationParts ? implode(', ', $fxLocationParts) : 'Choose your delivery location';
$fxHeaderTagline = $headerIsRestaurant ? $fxTagline : ($settings->store_subheadline ?: $fxTagline);
$cart_enabled = $this->theme_engine->cartEnabled();
?>
<?php if(!empty($settings->announcement_bar) && !$headerIsRestaurant): ?>
<div class="fx-announcement"><?= htmlspecialchars($settings->announcement_bar); ?></div>
<?php endif; ?>

<header class="fx-header <?= strpos(uri_string(), '/account') !== false ? 'fx-header--account' : ''; ?> <?= !empty($product) ? 'fx-header--meal' : ''; ?>" role="banner">
  <div class="fx-header-inner">
    <div class="fx-header-top">
      <button class="fx-mobile-menu-btn" onclick="toggleMobileMenu()" aria-label="Open menu" aria-expanded="false">
        <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>

      <a href="<?= base_url('store/' . $slug); ?>" class="fx-logo" aria-label="<?= htmlspecialchars($fxStoreName); ?> home">
        <?php if($logo): ?>
          <img src="<?= htmlspecialchars($logo); ?>" alt="<?= htmlspecialchars($fxStoreName); ?>" class="fx-logo-img">
        <?php else: ?>
          <svg class="fx-logo-img" viewBox="0 0 40 40" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
            <rect width="40" height="40" rx="14" fill="var(--fx-green-deep)"/>
            <path d="M11 24c1.8-6.5 16.2-6.5 18 0" stroke="var(--fx-yellow)" stroke-width="2.7" stroke-linecap="round"/>
            <circle cx="16" cy="17" r="2" fill="#fff"/><circle cx="24" cy="17" r="2" fill="#fff"/>
          </svg>
        <?php endif; ?>
        <div class="fx-logo-text">
          <span class="fx-logo-name"><?= htmlspecialchars($fxStoreName); ?></span>
          <span class="fx-logo-tagline"><?= htmlspecialchars($fxHeaderTagline); ?></span>
        </div>
      </a>

      <button type="button" class="fx-location" id="fx-header-location" aria-label="Choose delivery location" onclick="const picker=document.getElementById('fx-outlet-picker'); if(picker){picker.scrollIntoView({behavior:'smooth',block:'center'});}else{window.location.href='<?= base_url('store/' . $slug . '/branches'); ?>';}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>
        <span class="fx-location-copy"><span class="fx-location-label">Ordering from</span><span class="fx-location-value" data-fx-location-value><?= htmlspecialchars($fxLocation); ?></span></span>
        <svg class="fx-location-chevron" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m6 9 6 6 6-6"/></svg>
      </button>

      <div class="fx-header-actions">
        <?php if($settings->show_search ?? true): ?>
        <button type="button" class="fx-header-icon fx-header-search-toggle" aria-label="Search the menu" aria-expanded="false" aria-controls="fx-header-search-panel" onclick="const panel=document.getElementById('fx-header-search-panel');const open=panel.classList.toggle('is-open');this.setAttribute('aria-expanded',String(open));if(open)panel.querySelector('input').focus();"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m16 16 5 5"/></svg></button>
        <?php endif; ?>
        <nav class="fx-header-links" aria-label="Main navigation">
          <a href="<?= base_url('store/' . $slug); ?>">Home</a>
          <a href="<?= base_url('store/' . $slug . '/products'); ?>">Menu</a>
          <a href="<?= base_url('store/' . $slug . '/branches'); ?>">Locations</a>
        </nav>
        <a href="<?= base_url('store/' . $slug . '/account'); ?>" class="fx-header-account" aria-label="Your account">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.5"/><path d="M5 21c.5-4 3-6 7-6s6.5 2 7 6"/></svg><span>Account</span>
        </a>
        <?php if(!empty($cart_enabled)): ?>
        <a href="<?= base_url('store/' . $slug . '/cart'); ?>" class="fx-cart-btn" aria-label="Cart">
          <svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
          <span class="fx-cart-count" id="cart-count">0</span><span class="fx-cart-amount" id="header-cart-amount">0</span>
        </a>
        <?php endif; ?>
        <button type="button" class="fx-header-icon fx-header-menu" onclick="toggleMobileMenu()" aria-label="Open menu"><svg width="21" height="21" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16M4 12h16M4 18h16"/></svg></button>
      </div>
    </div>

    <?php if($settings->show_search ?? true): ?>
    <div class="fx-header-search" id="fx-header-search-panel" role="search">
      <svg class="fx-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
      <input type="text" id="search-input-mobile" placeholder="<?= $fxCopy('search_placeholder'); ?>" onkeydown="if(event.key==='Enter')doSearch(this.value)" aria-label="Search meals">
    </div>
    <?php endif; ?>
  </div>
</header>

<?php if(!empty($categories) && ($sells_products ?? true)): ?>
<nav class="fx-nav <?= preg_match('~/products$~', uri_string()) ? 'fx-nav-menu' : ''; ?>" role="navigation" aria-label="Categories">
  <div class="fx-nav-inner">
    <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="fx-nav-link active"><span class="fx-nav-all-mark" aria-hidden="true"></span>All</a>
    <?php foreach(array_slice($categories, 0, 12) as $cat): ?>
    <a href="<?= base_url('store/' . $slug . '/products?category=' . $cat->id); ?>" class="fx-nav-link"><?= htmlspecialchars($cat->category_name); ?></a>
    <?php endforeach; ?>
  </div>
</nav>
<?php endif; ?>

<div id="mobile-menu-overlay" style="display:none; position:fixed; inset:0; background:rgba(7,59,44,.48); z-index:900;" onclick="toggleMobileMenu()"></div>
<aside id="mobile-menu" style="display:none; position:fixed; top:0; left:0; bottom:0; width:min(86vw,340px); background:var(--fx-white); z-index:901; padding:28px 22px; box-shadow:12px 0 40px rgba(0,0,0,.18);">
  <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:28px;">
    <strong style="font-size:20px;color:var(--fx-green-deep);letter-spacing:-.04em;"><?= htmlspecialchars($fxStoreName); ?></strong>
    <button type="button" onclick="toggleMobileMenu()" aria-label="Close menu" style="border:0;background:transparent;color:var(--fx-muted);font-size:28px;line-height:1;">&times;</button>
  </div>
  <nav style="display:flex;flex-direction:column;gap:5px;">
    <a href="<?= base_url('store/' . $slug); ?>" onclick="toggleMobileMenu()" style="padding:13px 0;font-size:16px;font-weight:900;color:var(--fx-green-deep);text-decoration:none;">Home</a>
    <?php if($sells_products ?? true): ?><a href="<?= base_url('store/' . $slug . '/products'); ?>" onclick="toggleMobileMenu()" style="padding:13px 0;font-size:16px;font-weight:900;color:var(--fx-green-deep);text-decoration:none;">Browse the menu</a><?php endif; ?>
    <a href="<?= base_url('store/' . $slug . '/branches'); ?>" onclick="toggleMobileMenu()" style="padding:13px 0;font-size:16px;font-weight:900;color:var(--fx-green-deep);text-decoration:none;">Locations</a>
    <a href="<?= base_url('store/' . $slug . '/account'); ?>" style="padding:13px 0;font-size:16px;font-weight:900;text-decoration:none;">Your profile</a>
    <?php if($this->theme_engine->sellsServices() && !$headerIsRestaurant): ?><a href="<?= base_url('store/' . $slug . '/services'); ?>" onclick="toggleMobileMenu()" style="padding:13px 0;font-size:16px;font-weight:900;color:var(--fx-green-deep);text-decoration:none;">Experiences &amp; bookings</a><?php endif; ?>
    <?php if(!empty($cart_enabled)): ?><a href="<?= base_url('store/' . $slug . '/cart'); ?>" onclick="toggleMobileMenu()" style="padding:13px 0;font-size:16px;font-weight:900;color:var(--fx-green-deep);text-decoration:none;">Your basket</a><?php endif; ?>
    <a href="<?= base_url('store/' . $slug . '/track'); ?>" onclick="toggleMobileMenu()" style="padding:13px 0;font-size:16px;font-weight:900;color:var(--fx-green-deep);text-decoration:none;">Track order</a>
  </nav>
  <div style="height:1px;background:var(--fx-border);margin:20px 0;"></div>
  <div style="font-size:12px;color:var(--fx-muted);line-height:1.7;"><strong style="color:var(--fx-ink);">Delivering to</strong><br><?= htmlspecialchars($fxLocation); ?></div>
</aside>
<script>
document.addEventListener('DOMContentLoaded',function(){
  const outlets=<?= json_encode(array_map(function($outlet){return ['id'=>(int)$outlet->id,'name'=>(string)$outlet->warehouse_name];}, $outlets ?? []), JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;
  const saved=Number(localStorage.getItem('sf_outlet_'+STORE_ID)||0);
  const selected=outlets.find(outlet=>outlet.id===saved);
  if(selected) document.querySelectorAll('[data-fx-location-value]').forEach(el=>el.textContent=selected.name);
});
</script>
