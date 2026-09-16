<?php
/* Creator theme shared header/navigation
   Include at the very top of any creator_focus/creator_bold/creator_studio view.
   Computes the theme palette and renders a premium Gumroad/Teachable-style header.
*/
$slug = $settings->store_slug ?? '';
$waNumber = preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? '');
$isFocus = !isset($THEME);
$THEME = $THEME ?? ($theme_key === 'creator_studio' ? 'studio' : ($theme_key === 'creator_bold' ? 'bold' : 'focus'));

$palettes = [
    'focus'  => ['primary' => '#7C3AED', 'primary-dark' => '#6D28D9', 'secondary' => '#EC4899', 'bg' => '#FFFFFF', 'surface' => '#F8FAFC', 'text' => '#0F172A', 'muted' => '#64748B', 'border' => '#E2E8F0', 'dark' => '#0F172A', 'hero-gradient' => 'linear-gradient(115deg, #0F172A 0%, #1E1B4B 40%, #4C1D95 100%)'],
    'bold'   => ['primary' => '#FF4D00', 'primary-dark' => '#E63D00', 'secondary' => '#FFD700', 'bg' => '#0A0A0A', 'surface' => '#141414', 'text' => '#FFFFFF', 'muted' => '#A1A1AA', 'border' => '#2A2A2A', 'dark' => '#0A0A0A', 'hero-gradient' => 'radial-gradient(circle at 30% 20%, #1F1F1F 0%, #0A0A0A 60%, #000000 100%)'],
    'studio' => ['primary' => '#C75D3A', 'primary-dark' => '#B34E2E', 'secondary' => '#E4A15A', 'bg' => '#FDFBF6', 'surface' => '#FFFFFF', 'text' => '#2C2A26', 'muted' => '#7A7569', 'border' => '#E8E3D8', 'dark' => '#2C2A26', 'hero-gradient' => 'linear-gradient(135deg, #FDFBF6 0%, #F5EFE4 55%, #F3E7D7 100%)'],
];
$c = $palettes[$THEME] ?? $palettes['focus'];
$isDark = in_array($THEME, ['bold']);
$text = $isDark ? '#fff' : $c['text'];
$logo = $logo_url ?? base_url('uploads/site/icon.webp');
?>
<style>
/* Hide generic storefront chrome */
.mp-topbar,.mp-announcement,.mp-header,.mp-nav,.mp-mobile-menu-btn,.mp-footer{display:none !important;}

/* Creator shared nav */
.crf-nav *,.crf-nav *::before,.crf-nav *::after{box-sizing:border-box;}
.crf-nav{position:sticky;top:0;z-index:1000;background:<?= $isDark?'rgba(10,10,10,.95)':'rgba(255,255,255,.96)';?>;backdrop-filter:blur(12px);border-bottom:1px solid var(--cr-border);}
.crf-nav a{color:inherit;text-decoration:none;}
.crf-nav-inner{max-width:1200px;margin:0 auto;padding:14px 24px;display:flex;align-items:center;justify-content:space-between;gap:20px;}
.crf-logo{display:flex;align-items:center;gap:10px;font-weight:800;font-size:17px;color:<?= $text;?>;}
.crf-logo img{height:38px;width:38px;object-fit:cover;border-radius:8px;}
.crf-links{display:flex;align-items:center;gap:28px;}
.crf-links a{font-size:14px;font-weight:600;color:<?= $c['muted'];?>;transition:color .15s;}
.crf-links a:hover{color:<?= $c['primary'];?>;}
.crf-cta{padding:10px 20px;border-radius:999px;background:<?= $c['primary'];?>;color:#fff !important;font-size:13px;font-weight:700;transition:background .15s;}
.crf-cta:hover{background:<?= $c['primary-dark'];?>;}
.crf-cart{position:relative;display:flex;align-items:center;gap:6px;font-size:14px;font-weight:600;color:<?= $text;?>;}
.crf-cart svg{width:20px;height:20px;}
.crf-cart-count{position:absolute;top:-8px;right:-10px;background:<?= $c['primary'];?>;color:#fff;font-size:10px;font-weight:700;width:18px;height:18px;border-radius:50%;display:flex;align-items:center;justify-content:center;}
.crf-menu-btn{display:none;background:none;border:none;color:<?= $text;?>;font-size:22px;cursor:pointer;}
.crf-mobile-nav{display:none;position:absolute;top:100%;left:0;right:0;background:<?= $isDark?'#141414':'#fff';?>;border-bottom:1px solid var(--cr-border);padding:16px 24px;flex-direction:column;gap:12px;box-shadow:0 10px 40px rgba(0,0,0,.08);}
.crf-mobile-nav.open{display:flex;}
.crf-mobile-nav a{font-size:15px;font-weight:700;color:<?= $text;?>;padding:8px 0;}
@media(max-width:900px){
  .crf-links{display:none;}
  .crf-menu-btn{display:block;}
}
</style>
<header class="crf-nav">
  <div class="crf-nav-inner">
    <a class="crf-logo" href="<?= base_url('store/' . $slug); ?>">
      <?php if($logo): ?><img src="<?= $logo; ?>" alt="<?= htmlspecialchars($store->store_name ?? ''); ?>"><?php endif; ?>
      <span><?= htmlspecialchars($store->store_name ?? 'Creator Store'); ?></span>
    </a>
    <nav class="crf-links">
      <a href="<?= base_url('store/' . $slug); ?>">Home</a>
      <a href="<?= base_url('store/' . $slug . '/products?type=course'); ?>">Courses</a>
      <a href="<?= base_url('store/' . $slug . '/products?type=digital'); ?>">Products</a>
      <a href="<?= base_url('store/' . $slug . '/products?type=membership'); ?>">Memberships</a>
      <a href="<?= base_url('store/' . $slug . '/account'); ?>">My Account</a>
      <a class="crf-cta" href="<?= base_url('store/' . $slug . '/products'); ?>">Shop Now</a>
    </nav>
    <div style="display:flex;align-items:center;gap:14px;">
      <a class="crf-cart" href="<?= base_url('store/' . $slug . '/cart'); ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span class="crf-cart-count" id="crfCartCount" style="display:none;">0</span>
      </a>
      <button class="crf-menu-btn" onclick="document.getElementById('crfMobileNav').classList.toggle('open')" aria-label="Menu">&#9776;</button>
    </div>
  </div>
  <div class="crf-mobile-nav" id="crfMobileNav">
    <a href="<?= base_url('store/' . $slug); ?>">Home</a>
    <a href="<?= base_url('store/' . $slug . '/products?type=course'); ?>">Courses</a>
    <a href="<?= base_url('store/' . $slug . '/products?type=digital'); ?>">Products</a>
    <a href="<?= base_url('store/' . $slug . '/products?type=membership'); ?>">Memberships</a>
    <a href="<?= base_url('store/' . $slug . '/account'); ?>">My Account</a>
    <a href="<?= base_url('store/' . $slug . '/cart'); ?>">Cart</a>
  </div>
</header>
<script>
function crfUpdateCartCount(){
  const s = window.STORE_ID || '';
  let cart = JSON.parse(localStorage.getItem('sf_cart_' + s) || '[]');
  let total = cart.reduce((a,i)=>a+(i.qty||0),0);
  const el = document.getElementById('crfCartCount');
  if(el){ el.textContent = total; el.style.display = total > 0 ? 'flex' : 'none'; }
}
if(typeof window.addToCart === 'function'){
  const oldAdd = window.addToCart;
  window.addToCart = function(){ oldAdd.apply(this, arguments); crfUpdateCartCount(); };
}
crfUpdateCartCount();
</script>
