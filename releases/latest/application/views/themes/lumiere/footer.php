<?php
require_once APPPATH . 'views/themes/lumiere/_helpers.php';
require_once APPPATH . 'helpers/storefront_footer_helper.php';
$lmFooterLogo = mp_storefront_footer_logo_url($settings ?? null, $logo_url ?? '');
$showMpPoweredBy = mp_storefront_show_powered_by($settings ?? null);
$lmSlug = $settings->store_slug ?? '';
$lmHome = base_url('store/' . $lmSlug);
$lmBrandName = trim((string)($store->store_name ?? '')) ?: 'Lumière';
$lmEmail = trim((string) ($settings->store_email ?? ''));
$lmPhone = trim((string) ($settings->store_phone ?? ''));
$lmWa = preg_replace('/[^0-9]/', '', (string) ($settings->whatsapp_number ?? ''));
$lmAbout = trim(strip_tags((string) ($settings->footer_about_us ?? '')));
if ($lmAbout === '') $lmAbout = 'A softer approach to skincare. Discover products and beauty rituals designed to make every day feel a little brighter.';
$lmHasReviews = !empty($testimonials) && !empty($homepage_sections['testimonials']);
?>
<footer id="contact" class="footer"><div class="wrap">
  <div class="footer-grid">
    <div><?php if ($lmFooterLogo): ?><img src="<?= lm_escape($lmFooterLogo); ?>" alt="<?= lm_escape($lmBrandName); ?> footer logo" style="display:block;width:auto;height:auto;max-width:180px;max-height:54px;object-fit:contain;margin:0 0 14px;background:#fff;padding:6px 10px;border-radius:6px;"><?php endif; ?><a class="brand<?= !empty($logo_url) ? ' brand-logo' : ''; ?>" href="<?= lm_escape($lmHome); ?>" aria-label="<?= lm_escape($lmBrandName); ?> home"><?php if (!empty($logo_url)): ?><img src="<?= lm_escape($logo_url); ?>" alt="<?= lm_escape($lmBrandName); ?> logo"><?php else: ?><strong><?= lm_escape($lmBrandName); ?></strong><span>SKINCARE &amp; BEAUTY</span><?php endif; ?></a><p><?= lm_escape($lmAbout); ?></p></div>
    <div><h4>Explore</h4><?php if (!empty($sells_products)): ?><a href="<?= lm_escape($lmHome . '/products'); ?>">Shop Skincare</a><a href="<?= lm_escape($lmHome . '/products?sort=newest'); ?>">New Arrivals</a><?php endif; ?><?php if (!empty($sells_services) && !empty($settings->allow_services)): ?><a href="<?= lm_escape($lmHome . '/services'); ?>">Our Treatments</a><?php endif; ?><a href="<?= lm_escape($lmHome . '/about'); ?>">Our Story</a></div>
    <div><h4>Help</h4><?= $footer_site_links ?? ''; ?><?php if (!empty($cart_enabled)): ?><a href="<?= lm_escape($lmHome . '/cart'); ?>">Your Bag</a><a href="<?= lm_escape($lmHome . '/checkout'); ?>">Checkout</a><?php endif; ?><a href="<?= lm_escape($lmHome . '/account/orders'); ?>">Your Orders</a><?php if ($lmHasReviews): ?><a href="<?= lm_escape($lmHome . '#reviews'); ?>">Reviews</a><?php endif; ?></div>
    <div><h4>Say Hello</h4><p>For skincare questions, personalised recommendations or appointment assistance.</p><?php if ($lmEmail): ?><a href="mailto:<?= lm_escape($lmEmail); ?>"><?= lm_escape($lmEmail); ?></a><?php endif; ?><?php if ($lmPhone): ?><a href="tel:<?= lm_escape(preg_replace('/[^0-9+]/', '', $lmPhone)); ?>"><?= lm_escape($lmPhone); ?></a><?php endif; ?><?php if ($lmWa): ?><a href="https://wa.me/<?= lm_escape($lmWa); ?>" target="_blank" rel="noopener">WhatsApp ↗</a><?php endif; ?></div>
  </div>
  <div class="footer-bottom"><span>© <?= date('Y'); ?> <?= lm_escape($store->store_name ?? 'Lumière Skincare & Beauty'); ?>. All rights reserved.</span><span>Secure checkout · Carefully considered care<?php if ($showMpPoweredBy): ?> · Powered by MartPoint<?php endif; ?></span></div>
</div></footer>
<div class="mp-toast lm-toast" id="toast" role="status" aria-live="polite"></div>
