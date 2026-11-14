<?php
$fxFooterLine = $fxCopy('footer_line') ?: 'Fresh, tasty and always on time.';
$fxBrand = $fxCopy('brand_line') ?: 'Good food. Great mood.';
$fxTagline = $fxCopy('brand_tagline') ?: 'From first craving to first bite.';
$social = $social_links ?? [];
$footerProfile = function_exists('mp_get_store_profile') ? mp_get_store_profile((int)($settings->store_id ?? 0)) : [];
$footerIndustry = strtolower(trim((string)($footerProfile['industry_type'] ?? '')));
$footerIsRestaurant = in_array($footerIndustry, ['restaurant','fast_food','food_delivery','cafe','bakery','pizza_shop','shawarma','juice_bar','buka','canteen'], true);
$footerLogo = $footerIsRestaurant ? null : ($footer_logo ?? $store_logo ?? null);
$fxCurrent = (string) uri_string();
?>
<footer class="fx-footer" role="contentinfo">
  <div class="fx-footer-inner">
    <div class="fx-footer-col fx-footer-brand">
      <?php if($footerLogo): ?>
        <img src="<?= $footerLogo; ?>" alt="<?= htmlspecialchars($store->store_name ?? 'Logo'); ?>" style="height:40px; margin-bottom:12px;">
      <?php else: ?>
        <div class="fx-footer-logo"><?= htmlspecialchars($settings->store_headline ?: ($store->store_name ?? 'Food Express')); ?></div>
        <div class="fx-footer-tagline"><?= $fxTagline; ?></div>
      <?php endif; ?>
      <p class="fx-footer-desc"><?= $fxFooterLine; ?></p>
      <div class="fx-footer-social fx-footer-social-top" aria-label="Social links">
        <?php if(!empty($social['instagram'])): ?><a href="<?= htmlspecialchars($social['instagram']); ?>" target="_blank" aria-label="Instagram"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="5"/><circle cx="17.5" cy="6.5" r="1"/></svg></a><?php endif; ?>
        <?php if(!empty($social['facebook'])): ?><a href="<?= htmlspecialchars($social['facebook']); ?>" target="_blank" aria-label="Facebook"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></a><?php endif; ?>
        <?php if(!empty($social['x'])): ?><a href="<?= htmlspecialchars($social['x']); ?>" target="_blank" aria-label="X"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a><?php endif; ?>
      </div>
    </div>

    <div class="fx-footer-col">
      <h4>Quick Links</h4>
      <ul class="fx-footer-links">
        <li><a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>">All Meals</a></li>
        <li><a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/cart'); ?>">Cart</a></li>
        <li><a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/track'); ?>">Track Order</a></li>
        <?php if($this->theme_engine->sellsServices() && !$footerIsRestaurant): ?>
        <li><a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/services'); ?>">Experiences</a></li>
        <?php endif; ?>
        <li><a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/contact'); ?>">Contact</a></li>
      </ul>
    </div>

    <div class="fx-footer-col">
      <h4>Ordering</h4>
      <ul class="fx-footer-links">
        <li><a href="#">How to Order</a></li>
        <li><a href="#">Delivery Areas</a></li>
        <li><a href="#">Pickup Points</a></li>
        <li><a href="#">QR Table Order</a></li>
        <li><a href="#">Group Orders</a></li>
      </ul>
    </div>

    <div class="fx-footer-col">
      <h4>Connect</h4>
      <ul class="fx-footer-links">
        <?php if(!empty($settings->store_phone)): ?>
        <li><a href="tel:<?= preg_replace('/[^0-9+]/', '', $settings->store_phone); ?>"><?= htmlspecialchars($settings->store_phone); ?></a></li>
        <?php endif; ?>
        <?php if(!empty($settings->store_email)): ?>
        <li><a href="mailto:<?= htmlspecialchars($settings->store_email); ?>"><?= htmlspecialchars($settings->store_email); ?></a></li>
        <?php endif; ?>
        <?php if(!empty($settings->whatsapp_number)): ?>
        <li><a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number); ?>" target="_blank">WhatsApp</a></li>
        <?php endif; ?>
        <?php if(!empty($social['instagram'])): ?><li><a href="<?= htmlspecialchars($social['instagram']); ?>" target="_blank">Instagram</a></li><?php endif; ?>
        <?php if(!empty($social['facebook'])): ?><li><a href="<?= htmlspecialchars($social['facebook']); ?>" target="_blank">Facebook</a></li><?php endif; ?>
      </ul>
    </div>
  </div>

  <div class="fx-footer-bottom">
    <div class="fx-footer-legal">
      <span>&copy; <?= date('Y'); ?> <?= htmlspecialchars($store->store_name ?? 'Food Express'); ?>. All rights reserved.</span>
      <small>Powered by MartPoint</small>
    </div>
    <div class="fx-footer-social fx-footer-social-bottom">
      <?php if(!empty($social['instagram'])): ?><a href="<?= htmlspecialchars($social['instagram']); ?>" target="_blank" aria-label="Instagram"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="5"/><circle cx="17.5" cy="6.5" r="1"/></svg></a><?php endif; ?>
      <?php if(!empty($social['facebook'])): ?><a href="<?= htmlspecialchars($social['facebook']); ?>" target="_blank" aria-label="Facebook"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></a><?php endif; ?>
      <?php if(!empty($social['x'])): ?><a href="<?= htmlspecialchars($social['x']); ?>" target="_blank" aria-label="X"><svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a><?php endif; ?>
    </div>
  </div>
</footer>

<nav class="fx-mobile-nav" aria-label="Quick navigation">
  <a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>" class="<?= strpos($fxCurrent, '/products') === false && strpos($fxCurrent, '/cart') === false && strpos($fxCurrent, '/account') === false ? 'active' : ''; ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m3 11 9-8 9 8"/><path d="M5 10v10h14V10"/><path d="M9 20v-6h6v6"/></svg><span>Home</span>
  </a>
  <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>" class="<?= strpos($fxCurrent, '/products') !== false ? 'active' : ''; ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg><span>Search</span>
  </a>
  <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/account/orders'); ?>" class="<?= strpos($fxCurrent, '/account/orders') !== false ? 'active' : ''; ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M6 3h12v18H6z"/><path d="M9 7h6M9 11h6M9 15h4"/></svg><span>Orders</span>
  </a>
  <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/account'); ?>" class="<?= strpos($fxCurrent, '/account') !== false ? 'active' : ''; ?>">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.5"/><path d="M5 21c.5-4 3-6 7-6s6.5 2 7 6"/></svg><span>Profile</span>
  </a>
</nav>
