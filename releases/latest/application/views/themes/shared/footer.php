<?php
$slug = $settings->store_slug ?? '';
$social = $social_links ?? [];
$footerStyle = $settings->footer_style ?? 'standard';
$aboutUs = $settings->footer_about_us ?? '';
?>

<div class="mp-footer-space"></div>

<!-- Footer -->
<div class="mp-footer" data-footer-style="<?= htmlspecialchars($footerStyle); ?>">
  <?php if($footerStyle === 'compact'): ?>
  <!-- Compact Centered Footer -->
  <div class="mp-footer-inner mp-footer-compact">
    <!-- Brand + About + Social (centered) -->
    <div class="mp-footer-compact-top">
      <div class="mp-footer-brand"><?= htmlspecialchars($store->store_name ?? 'Store'); ?></div>
      <?php if(!empty($aboutUs)): ?>
      <div class="mp-footer-desc"><?= nl2br(htmlspecialchars($aboutUs)); ?></div>
      <?php elseif(!empty($settings->store_description)): ?>
      <div class="mp-footer-desc"><?= nl2br(htmlspecialchars($settings->store_description)); ?></div>
      <?php endif; ?>
      <?php if(!empty($social)): ?>
      <div class="mp-footer-social">
        <?php if(!empty($social['instagram'])): ?><a href="<?= htmlspecialchars($social['instagram']); ?>" target="_blank" title="Instagram"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="5"/><circle cx="17.5" cy="6.5" r="1"/></svg></a><?php endif; ?>
        <?php if(!empty($social['facebook'])): ?><a href="<?= htmlspecialchars($social['facebook']); ?>" target="_blank" title="Facebook"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></a><?php endif; ?>
        <?php if(!empty($social['tiktok'])): ?><a href="<?= htmlspecialchars($social['tiktok']); ?>" target="_blank" title="TikTok"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/></svg></a><?php endif; ?>
        <?php if(!empty($social['x'])): ?><a href="<?= htmlspecialchars($social['x']); ?>" target="_blank" title="X"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a><?php endif; ?>
        <?php if(!empty($social['youtube'])): ?><a href="<?= htmlspecialchars($social['youtube']); ?>" target="_blank" title="YouTube"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg></a><?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
    <!-- Shop links row -->
    <div class="mp-footer-compact-row mp-footer-compact-shop">
      <span class="mp-footer-compact-label">Shop</span>
      <a href="<?= base_url('store/' . $slug); ?>">Home</a>
      <a href="<?= base_url('store/' . $slug . '/products'); ?>">All Products</a>
      <?php if($settings->allow_services ?? false): ?><a href="<?= base_url('store/' . $slug . '/services'); ?>">Services</a><?php endif; ?>
    </div>
    <!-- Support + Contact rows -->
    <div class="mp-footer-compact-row mp-footer-compact-support">
      <span class="mp-footer-compact-label">Support</span>
      <?php if(!empty($settings->whatsapp_number)): ?><a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number); ?>" target="_blank">WhatsApp Support</a><?php endif; ?>
    </div>
    <div class="mp-footer-compact-row mp-footer-compact-contact">
      <span class="mp-footer-compact-label">Contact</span>
      <?php if(!empty($settings->store_phone)): ?><span><?= htmlspecialchars($settings->store_phone); ?></span><?php endif; ?>
      <?php if(!empty($settings->store_email)): ?><span class="sep">|</span><span><?= htmlspecialchars($settings->store_email); ?></span><?php endif; ?>
      <?php if(!empty($settings->store_address)): ?>
        <span class="sep">|</span>
        <?php if(!empty($settings->footer_address_url ?? '')): ?>
          <a href="<?= htmlspecialchars($settings->footer_address_url); ?>" target="_blank"><?= htmlspecialchars($settings->store_address); ?></a>
        <?php else: ?>
          <span><?= htmlspecialchars($settings->store_address); ?></span>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>

  <?php else: ?>
  <!-- Standard / About-Focused 4-Column Footer -->
  <div class="mp-footer-inner">
    <div>
      <div class="mp-footer-brand"><?= htmlspecialchars($store->store_name ?? 'Store'); ?></div>
      <?php if(!empty($aboutUs)): ?>
      <div class="mp-footer-desc"><?= nl2br(htmlspecialchars($aboutUs)); ?></div>
      <?php else: ?>
      <div class="mp-footer-desc"><?= nl2br(htmlspecialchars($settings->store_description ?? '')); ?></div>
      <?php endif; ?>
      <?php if(!empty($social)): ?>
      <div class="mp-footer-social">
        <?php if(!empty($social['instagram'])): ?><a href="<?= htmlspecialchars($social['instagram']); ?>" target="_blank" title="Instagram"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="2" width="20" height="20" rx="5"/><circle cx="12" cy="12" r="5"/><circle cx="17.5" cy="6.5" r="1"/></svg></a><?php endif; ?>
        <?php if(!empty($social['facebook'])): ?><a href="<?= htmlspecialchars($social['facebook']); ?>" target="_blank" title="Facebook"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg></a><?php endif; ?>
        <?php if(!empty($social['tiktok'])): ?><a href="<?= htmlspecialchars($social['tiktok']); ?>" target="_blank" title="TikTok"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/></svg></a><?php endif; ?>
        <?php if(!empty($social['x'])): ?><a href="<?= htmlspecialchars($social['x']); ?>" target="_blank" title="X"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg></a><?php endif; ?>
        <?php if(!empty($social['youtube'])): ?><a href="<?= htmlspecialchars($social['youtube']); ?>" target="_blank" title="YouTube"><svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg></a><?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
    <div>
      <div class="mp-footer-heading">Shop</div>
      <ul class="mp-footer-links">
        <li><a href="<?= base_url('store/' . $slug); ?>">Home</a></li>
        <li><a href="<?= base_url('store/' . $slug . '/products'); ?>">All Products</a></li>
        <?php if($settings->allow_services ?? false): ?>
        <li><a href="<?= base_url('store/' . $slug . '/services'); ?>">Services</a></li>
        <?php endif; ?>
      </ul>
    </div>
    <div>
      <div class="mp-footer-heading">Support</div>
      <ul class="mp-footer-links">
        <?php if(!empty($settings->whatsapp_number)): ?>
        <li><a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number); ?>" target="_blank">WhatsApp Support</a></li>
        <?php endif; ?>
      </ul>
    </div>
    <div>
      <div class="mp-footer-heading">Contact</div>
      <?php if(!empty($settings->store_phone)): ?>
        <div class="mp-footer-contact-item"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg> <?= htmlspecialchars($settings->store_phone); ?></div>
      <?php endif; ?>
      <?php if(!empty($settings->store_email)): ?>
        <div class="mp-footer-contact-item"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg> <?= htmlspecialchars($settings->store_email); ?></div>
      <?php endif; ?>
      <?php if(!empty($settings->store_address)): ?>
        <div class="mp-footer-contact-item">
          <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
          <?php if(!empty($settings->footer_address_url ?? '')): ?>
            <a href="<?= htmlspecialchars($settings->footer_address_url); ?>" target="_blank"><?= nl2br(htmlspecialchars($settings->store_address)); ?></a>
          <?php else: ?>
            <?= nl2br(htmlspecialchars($settings->store_address)); ?>
          <?php endif; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="mp-footer-bottom">
    &copy; <?= date('Y'); ?> <?= htmlspecialchars($store->store_name ?? 'Store'); ?>. All rights reserved.
  </div>
</div>

<!-- Mobile Bottom Nav -->
<div class="mp-mobile-nav">
  <div class="mp-mobile-nav-inner">
    <a href="<?= base_url('store/' . $slug); ?>" class="mp-mobile-nav-item">
      <span class="nav-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg></span>
      Home
    </a>
    <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="mp-mobile-nav-item">
      <span class="nav-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg></span>
      Shop
    </a>
    <button class="mp-mobile-nav-item" onclick="document.getElementById('search-input')?.focus(); if(document.getElementById('search-input')) document.getElementById('search-input').scrollIntoView({behavior:'smooth'});">
      <span class="nav-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
      Search
    </button>
    <a href="<?= base_url('store/' . $slug . '/cart'); ?>" class="mp-mobile-nav-item">
      <span class="nav-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg></span>
      Cart
    </a>
  </div>
</div>

<!-- Back to Top -->
<button class="mp-backtop" id="backtop" onclick="window.scrollTo({top:0,behavior:'smooth'});" aria-label="Back to top">
  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="18 15 12 9 6 15"/></svg>
</button>

<!-- Sticky WhatsApp -->
<?php if(!empty($settings->whatsapp_number)): ?>
<a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number); ?>" target="_blank" class="mp-sticky-wa" aria-label="WhatsApp">
  <svg width="26" height="26" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.13 1.558 5.931L.157 24l6.305-1.654a11.882 11.882 0 0 0 5.587 1.396h.004c6.552 0 11.887-5.335 11.89-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
</a>
<?php endif; ?>

<!-- Sticky Cart Bar -->
<div class="mp-sticky-cart" id="sticky-cart">
  <div class="mp-sticky-cart-inner">
    <div class="mp-sticky-cart-info">
      <span class="mp-sticky-cart-items"><span id="sticky-qty">0</span> items</span>
      <span class="mp-sticky-cart-total" id="sticky-total"><?= sf_currency(0, $store_currency ?? null); ?></span>
    </div>
    <a href="<?= base_url('store/' . $slug . '/cart'); ?>" class="mp-sticky-cart-btn">View Cart</a>
  </div>
</div>

<!-- Product Modal -->
<div class="mp-modal-overlay" id="product-modal">
  <div class="mp-modal">
    <div class="mp-modal-header">
      <div class="mp-modal-title" id="modal-title">Product</div>
      <button class="mp-modal-close" onclick="closeModal()">&times;</button>
    </div>
    <img id="modal-img" class="mp-modal-img" src="" alt="">
    <div class="mp-modal-price" id="modal-price"></div>
    <div class="mp-modal-desc" id="modal-desc"></div>
    <div class="mp-modal-qty">
      <button onclick="adjustModalQty(-1)">-</button>
      <span id="modal-qty">1</span>
      <button onclick="adjustModalQty(1)">+</button>
    </div>
    <button class="mp-modal-add" id="modal-add-btn" onclick="addModalToCart()">Add to Cart</button>
  </div>
</div>

<!-- Toast -->
<div class="mp-toast" id="toast"></div>
