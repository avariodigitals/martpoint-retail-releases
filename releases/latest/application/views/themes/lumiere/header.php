<?php
require_once APPPATH . 'views/themes/lumiere/_helpers.php';
$lmSlug = $settings->store_slug ?? '';
$lmHome = base_url('store/' . $lmSlug);
$lmBrandName = trim((string)($store->store_name ?? '')) ?: 'Lumière';
$lmServices = !empty($sells_services) && !empty($settings->allow_services);
$lmAnnouncement = trim((string) ($settings->announcement_bar ?? ''));
if ($lmAnnouncement === '') $lmAnnouncement = 'COMPLIMENTARY DELIVERY ON ORDERS ABOVE ₦50,000 · GLOW BEAUTIFULLY, EVERY DAY';
$lmMarqueeItems = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string)($settings->marquee_items ?? ''))), 'strlen'));
$lmAnnouncementColor = trim((string)($settings->announcement_bar_color ?? ''));
if (!preg_match('/^#[0-9a-fA-F]{6}$/', $lmAnnouncementColor)) $lmAnnouncementColor = '#244735';
?>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= base_url('theme/css/lumiere-reference.css?v=2'); ?>">
<link rel="stylesheet" href="<?= base_url('theme/css/lumiere-martpoint.css?v=7'); ?>">
<?php if ($lmMarqueeItems): ?><div class="topbar lm-marquee" style="--lm-announcement-color:<?= lm_escape($lmAnnouncementColor); ?>" aria-label="Store announcements"><div class="lm-marquee-track"><?php for ($lmRepeat = 0; $lmRepeat < 2; $lmRepeat++): ?><div class="lm-marquee-group"<?= $lmRepeat ? ' aria-hidden="true"' : ''; ?>><?php foreach ($lmMarqueeItems as $lmMarqueeItem): ?><span><?= lm_escape($lmMarqueeItem); ?></span><i aria-hidden="true">✳</i><?php endforeach; ?></div><?php endfor; ?></div></div><?php else: ?><div class="topbar" style="background:<?= lm_escape($lmAnnouncementColor); ?>"><?= lm_escape($lmAnnouncement); ?></div><?php endif; ?>
<header class="header">
  <div class="wrap head-inner">
    <button type="button" class="icon-btn menu-toggle" id="lm-menu-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="lm-nav"><svg viewBox="0 0 24 24"><path d="M3 6h18M3 12h18M3 18h18"/></svg></button>
    <a class="brand<?= !empty($logo_url) ? ' brand-logo' : ''; ?>" href="<?= lm_escape($lmHome); ?>" aria-label="<?= lm_escape($lmBrandName); ?> home"><?php if (!empty($logo_url)): ?><img src="<?= lm_escape($logo_url); ?>" alt="<?= lm_escape($lmBrandName); ?> logo"><?php else: ?><strong><?= lm_escape($lmBrandName); ?></strong><span>SKINCARE &amp; BEAUTY</span><?php endif; ?></a>
    <nav class="nav" id="lm-nav" aria-label="Main navigation">
      <a href="<?= lm_escape($lmHome); ?>">Home</a>
      <?php if (!empty($sells_products)): ?><a href="<?= lm_escape($lmHome . '/products'); ?>">Shop Skincare</a><?php endif; ?>
      <?php if ($lmServices): ?><a href="<?= lm_escape($lmHome . '/services'); ?>">Our Services</a><?php endif; ?>
      <a href="<?= lm_escape($lmHome . '/about'); ?>">Our Story</a>
      <a href="<?= lm_escape($lmHome . '#reviews'); ?>">Reviews</a>
      <a href="#contact">Contact</a>
    </nav>
    <div class="actions">
      <button type="button" class="icon-btn search-btn" id="lm-search-toggle" aria-label="Search products"><svg viewBox="0 0 24 24"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 5 5"/></svg></button>
      <a class="icon-btn" href="<?= lm_escape($lmHome . '/account'); ?>" aria-label="Your account"><svg viewBox="0 0 24 24"><circle cx="12" cy="7" r="4"/><path d="M4 22v-3a8 8 0 0 1 16 0v3"/></svg></a>
      <?php if (!empty($cart_enabled)): ?><a class="icon-btn" href="<?= lm_escape($lmHome . '/cart'); ?>" aria-label="Shopping bag"><svg viewBox="0 0 24 24"><path d="M4 9h16l-1.3 12H5.3L4 9Z"/><path d="M9 10V6a3 3 0 0 1 6 0v4"/></svg><span class="count" id="cart-count">0</span></a><?php endif; ?>
    </div>
  </div>
</header>
<div class="overlay" id="lm-search-overlay" hidden></div>
<div class="dialog" id="lm-search-dialog" role="dialog" aria-modal="true" aria-labelledby="lm-search-title" hidden>
  <button type="button" class="close" id="lm-search-close" aria-label="Close search">×</button>
  <h2 id="lm-search-title">Find your glow.</h2>
  <form action="<?= lm_escape($lmHome . '/products'); ?>" method="get"><label for="lm-search-input">Search skincare products</label><input id="lm-search-input" name="search" placeholder="Try ‘serum’ or ‘moisturiser’" required><button class="btn" type="submit">Search Products ↗</button></form>
</div>
<script>
document.addEventListener('DOMContentLoaded', function(){
  var menu = document.getElementById('lm-menu-toggle'), nav = document.getElementById('lm-nav');
  if(menu && nav) menu.addEventListener('click', function(){ var open = nav.classList.toggle('open'); menu.setAttribute('aria-expanded', open ? 'true' : 'false'); });
  var dialog=document.getElementById('lm-search-dialog'), overlay=document.getElementById('lm-search-overlay');
  function closeSearch(){ dialog.hidden=true; overlay.hidden=true; dialog.classList.remove('open'); overlay.classList.remove('open'); }
  document.getElementById('lm-search-toggle').addEventListener('click',function(){dialog.hidden=false;overlay.hidden=false;requestAnimationFrame(function(){dialog.classList.add('open');overlay.classList.add('open');document.getElementById('lm-search-input').focus();});});
  document.getElementById('lm-search-close').addEventListener('click',closeSearch);
  overlay.addEventListener('click',closeSearch);
  document.addEventListener('keydown',function(e){if(e.key==='Escape') closeSearch();});
  document.addEventListener('click',function(e){var b=e.target.closest('[data-lm-add]');if(!b)return; if(typeof addToCart==='function')addToCart(Number(b.dataset.id),b.dataset.type,b.dataset.name,Number(b.dataset.price),b.dataset.image,1,Number(b.dataset.stock));});
});
</script>
