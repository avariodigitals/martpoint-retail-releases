<?php
/**
 * Market Fresh — header, and the chrome every screen shares.
 *
 * The shared layout renders this file on EVERY storefront page (home,
 * catalogue, product, cart, checkout, track, about, contact), which is what
 * makes the design system reach screens this theme does not own:
 *   • _theme_css.php  — tokens + the --mp-* bridge for shared cart/checkout/footer
 *   • the WhatsApp order dialog — one instance, three entry points
 *   • the mini-cart drawer
 *
 * Nothing here is invented. The announcement strip, shipping notice, search,
 * categories, prices, cart and WhatsApp all come from merchant settings, and
 * each is rendered only when the merchant has actually configured it.
 */
$slug     = $settings->store_slug ?? '';
$storeName = $store->store_name ?? ($settings->store_headline ?? 'Store');
$logo     = $logo_url ?? null;
$cats     = (isset($categories) && is_array($categories)) ? $categories : [];
$showCats = (int)($settings->show_categories ?? 1) === 1;
$showSearch = (int)($settings->show_search ?? 1) === 1;
$showPrices = !empty($show_prices);
$cartOn   = !empty($cart_enabled);
$svcOn    = !empty($settings->allow_services);

$waNumber = preg_replace('/[^0-9]/', '', (string)($settings->whatsapp_number ?? ''));
$waUrl    = $waNumber !== '' ? 'https://wa.me/' . $waNumber : '';

$announce = trim((string)($settings->announcement_bar ?? ''));
$notice   = trim((string)($settings->shipping_notice ?? ''));
$barText  = $announce !== '' ? $announce : $notice;

$productsUrl = base_url('store/' . $slug . '/products');
$homeUrl     = base_url('store/' . $slug);
$cartUrl     = base_url('store/' . $slug . '/cart');

// One JSON payload for the dialog: no product string is ever concatenated into
// an inline handler again, so a quote or a path can no longer break the JS.
$mfWa = [
    'number'    => $waNumber,
    'store'     => (string)$storeName,
    'currency'  => null,
    'cartKey'   => 'sf_cart_' . (int)($settings->store_id ?? 0),
    'cartUrl'   => $cartUrl,
    'productsUrl' => $productsUrl,
    'checkoutUrl' => base_url('store/' . $slug . '/checkout'),
];
?>
<?php $this->load->view('themes/market_fresh/_theme_css'); ?>
<style>
.mf-bar { background:var(--mf-bar-bg); color:var(--mf-bar-ink); text-align:center;
  padding:9px 16px; font-family:var(--mf-body); font-size:13px; line-height:1.5; }
.mf-bar-inner { max-width:1360px; margin:0 auto; }

.mf-head { position:sticky; top:0; z-index:400; background:var(--mf-card); border-bottom:1px solid var(--mf-line); }
.mf-head-in { max-width:1360px; margin:0 auto; display:flex; align-items:center; gap:clamp(14px,2vw,28px);
  padding:11px clamp(16px,3vw,32px); }

/* Brand sits at the left, where a shopper expects to find it. */
.mf-brand { flex:0 1 auto; min-width:0; text-decoration:none; display:flex; align-items:center; }
.mf-brand img { max-height:44px; max-width:220px; width:auto; object-fit:contain; display:block; }
.mf-brand-text { display:block; min-width:0; }
.mf-brand-name { display:block; font-family:var(--mf-display); font-size:22px; font-weight:800; line-height:1.15;
  color:var(--mf-deep); letter-spacing:-.025em; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.mf-brand-tag { display:block; font-family:var(--mf-body); font-size:10px; font-weight:700; letter-spacing:.14em;
  text-transform:uppercase; color:var(--mf-accent-text); margin-top:2px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }

.mf-nav { display:flex; align-items:center; gap:22px; flex:0 0 auto; }
.mf-navlink { font-family:var(--mf-body); font-size:14px; font-weight:600; color:var(--mf-ink); text-decoration:none;
  white-space:nowrap; transition:color .18s; display:inline-flex; align-items:center; gap:5px; padding:6px 0; }
.mf-navlink:hover { color:var(--mf-accent-text); }
.mf-navlink svg { width:12px; height:12px; transition:transform .18s; }

.mf-drop { position:relative; }
.mf-drop:hover .mf-navlink svg, .mf-drop:focus-within .mf-navlink svg { transform:rotate(180deg); }
.mf-drop-panel { display:none; position:absolute; top:calc(100% + 8px); left:-14px; z-index:420; width:290px;
  background:var(--mf-card); border:1px solid var(--mf-line); border-radius:var(--mf-r); box-shadow:var(--mf-shadow-lift); padding:8px; }
.mf-drop:hover .mf-drop-panel, .mf-drop:focus-within .mf-drop-panel { display:block; }
.mf-drop-panel a { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:10px 12px;
  border-radius:10px; font-family:var(--mf-body); font-size:14px; font-weight:600; color:var(--mf-ink); text-decoration:none; }
.mf-drop-panel a:hover { background:var(--mf-soft); color:var(--mf-accent-text); }
.mf-drop-panel .n { font-size:12px; font-weight:500; color:var(--mf-muted); }
.mf-drop-all { margin-top:6px; padding-top:12px !important; border-top:1px solid var(--mf-line); border-radius:0 !important; color:var(--mf-accent-text) !important; }

.mf-tools { display:flex; align-items:center; gap:6px; margin-left:auto; flex:0 1 420px; min-width:0; justify-content:flex-end; }
/* Named -headsearch so it cannot collide with the catalogue toolbar's .mf-search,
   which this header reaches on every screen of the theme. */
.mf-headsearch { position:relative; flex:1 1 auto; min-width:0; max-width:300px; }
.mf-headsearch input { width:100%; padding:11px 16px 11px 40px; border:1px solid var(--mf-line); border-radius:999px;
  background:var(--mf-bg); font-family:var(--mf-body); font-size:14px; color:var(--mf-ink); outline:none; box-sizing:border-box;
  transition:border-color .18s,background .18s,box-shadow .18s; }
.mf-headsearch input::placeholder { color:var(--mf-muted); }
.mf-headsearch input:focus { border-color:var(--mf-accent); background:var(--mf-card); box-shadow:0 0 0 3px rgba(var(--mf-accent-rgb),.12); }
.mf-headsearch-ico { position:absolute; left:14px; top:50%; transform:translateY(-50%); color:var(--mf-muted); pointer-events:none; }
.mf-headsearch-ico svg { width:16px; height:16px; display:block; }

.mf-ico { position:relative; width:42px; height:42px; flex-shrink:0; display:flex; align-items:center; justify-content:center;
  border-radius:999px; border:none; background:none; cursor:pointer; color:var(--mf-ink); text-decoration:none; transition:background .18s,color .18s; }
.mf-ico:hover { background:var(--mf-soft); color:var(--mf-accent-text); }
.mf-ico svg { width:21px; height:21px; stroke:currentColor; stroke-width:1.7; fill:none; stroke-linecap:round; stroke-linejoin:round; }
/* WhatsApp keeps its own green — it is the one place a customer recognises it. */
.mf-ico--wa { color:var(--mf-wa-dark); }
.mf-count { position:absolute; top:2px; right:2px; min-width:18px; height:18px; padding:0 4px; border-radius:999px;
  background:var(--mf-accent); color:var(--mf-on-accent); font-family:var(--mf-body); font-size:10px; font-weight:700;
  line-height:18px; text-align:center; box-sizing:border-box; }
.mf-count:empty, .mf-count[data-zero="1"] { display:none; }

/* Burger: hidden on a full desktop, shown once the nav has no room. */
.mf-burger { display:none; width:42px; height:42px; flex:0 0 42px; border:none; background:none; border-radius:12px;
  cursor:pointer; color:var(--mf-ink); align-items:center; justify-content:center; }
.mf-burger:hover { background:var(--mf-soft); }
.mf-burger svg { width:24px; height:24px; stroke:currentColor; stroke-width:1.9; fill:none; stroke-linecap:round; }

/* Mobile */
.mf-m { display:none; }
.mf-m-bar { display:flex; align-items:center; gap:6px; padding:8px 12px; }
.mf-m-burger { width:40px; height:40px; flex:0 0 40px; border:none; background:none; border-radius:12px; cursor:pointer;
  color:var(--mf-ink); display:flex; align-items:center; justify-content:center; }
.mf-m-burger:hover { background:var(--mf-soft); }
.mf-m-burger svg { width:23px; height:23px; stroke:currentColor; stroke-width:1.9; fill:none; stroke-linecap:round; }
.mf-m-brand { flex:1 1 auto; min-width:0; display:flex; align-items:center; text-decoration:none; }
.mf-m-brand img { max-height:32px; max-width:100%; width:auto; object-fit:contain; object-position:left center; display:block; }
.mf-m-brand span { display:block; min-width:0; font-family:var(--mf-display); font-size:16px; font-weight:800;
  color:var(--mf-deep); letter-spacing:-.02em; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.mf-m-actions { display:flex; align-items:center; flex:0 0 auto; }
.mf-m-search { padding:0 12px 10px; }

.mf-menu { position:fixed; top:0; left:0; bottom:0; width:min(320px,86vw); background:var(--mf-card); z-index:1101;
  padding:20px; overflow-y:auto; transform:translateX(-102%); transition:transform .28s cubic-bezier(.4,0,.2,1);
  box-shadow:16px 0 48px rgba(var(--mf-deep-ink-rgb),.18); }
.mf-menu.open { transform:none; }
.mf-menu-head { display:flex; align-items:center; justify-content:space-between; gap:12px; margin-bottom:20px; }
.mf-menu-title { font-family:var(--mf-display); font-size:19px; font-weight:800; color:var(--mf-deep); }
.mf-menu-link { display:flex; align-items:center; justify-content:space-between; gap:10px; padding:13px 0;
  font-family:var(--mf-body); font-size:15px; font-weight:600; color:var(--mf-ink); text-decoration:none; border-bottom:1px solid var(--mf-line); }
.mf-menu-link:hover { color:var(--mf-accent-text); }
.mf-menu-group { margin-top:22px; }
.mf-menu-label { font-family:var(--mf-body); font-size:10px; font-weight:700; letter-spacing:.16em; text-transform:uppercase;
  color:var(--mf-muted); margin-bottom:6px; }
.mf-menu-contact { font-family:var(--mf-body); font-size:13px; line-height:1.7; color:var(--mf-muted); }
.mf-menu-contact a { color:var(--mf-accent-text); text-decoration:none; }

@media(max-width:1180px){ .mf-nav { gap:16px; } .mf-headsearch { max-width:220px; } .mf-brand-name { font-size:20px; } }
@media(max-width:1080px){ .mf-nav { display:none; } .mf-burger { display:flex; } }
@media(max-width:900px){
  .mf-head-in { display:none; }
  .mf-m { display:block; }
  .mf-ico { width:40px; height:40px; }
  .mf-ico svg { width:20px; height:20px; }
}
</style>

<?php if($barText !== ''): ?>
<div class="mf-bar"><div class="mf-bar-inner"><?= htmlspecialchars($barText, ENT_QUOTES, 'UTF-8'); ?></div></div>
<?php endif; ?>

<!-- Desktop / tablet landscape -->
<div class="mf-head">
  <div class="mf-head-in">
    <button class="mf-burger" type="button" id="mf-menu-btn-t" aria-label="Open menu" aria-haspopup="dialog">
      <svg viewBox="0 0 24 24" aria-hidden="true"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>

    <a class="mf-brand" href="<?= $homeUrl; ?>">
      <?php if($logo): ?>
        <img src="<?= htmlspecialchars($logo); ?>" alt="<?= htmlspecialchars($storeName); ?>">
      <?php else: ?>
        <span class="mf-brand-text">
          <span class="mf-brand-name"><?= htmlspecialchars($settings->store_headline ?: $storeName); ?></span>
          <?php if(!empty($settings->store_subheadline)): ?>
          <span class="mf-brand-tag"><?= htmlspecialchars($settings->store_subheadline); ?></span>
          <?php endif; ?>
        </span>
      <?php endif; ?>
    </a>

    <nav class="mf-nav" aria-label="Main">
      <?php if(!empty($sells_products) || $showCats): ?>
      <div class="mf-drop">
        <a class="mf-navlink" href="<?= $productsUrl; ?>">Shop
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
        </a>
        <?php if($showCats && $cats): ?>
        <div class="mf-drop-panel">
          <?php foreach(array_slice($cats, 0, 12) as $cat): ?>
          <a href="<?= $productsUrl; ?>?category=<?= (int)$cat->id; ?>">
            <span><?= htmlspecialchars($cat->category_name); ?></span>
            <?php if(!empty($cat->item_count)): ?><span class="n"><?= (int)$cat->item_count; ?></span><?php endif; ?>
          </a>
          <?php endforeach; ?>
          <a class="mf-drop-all" href="<?= $productsUrl; ?>"><span>Browse everything</span><span aria-hidden="true">&rarr;</span></a>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>
      <?php if($svcOn && !empty($sells_services)): ?>
      <a class="mf-navlink" href="<?= base_url('store/' . $slug . '/services'); ?>">Services</a>
      <?php endif; ?>
      <a class="mf-navlink" href="<?= base_url('store/' . $slug . '/track'); ?>">Track order</a>
      <a class="mf-navlink" href="<?= base_url('store/' . $slug . '/contact'); ?>">Contact</a>
    </nav>

    <div class="mf-tools">
      <?php if($showSearch): ?>
      <div class="mf-headsearch">
        <span class="mf-headsearch-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
        <input type="search" id="search-input" placeholder="Search products&hellip;" autocomplete="off"
               aria-label="Search products" onkeydown="if(event.key==='Enter'){event.preventDefault();doSearch(this.value);}">
      </div>
      <?php endif; ?>
      <?php if($waUrl !== ''): ?>
      <a class="mf-ico mf-ico--wa" href="<?= $waUrl; ?>" target="_blank" rel="noopener" aria-label="Chat with us on WhatsApp" title="Chat on WhatsApp">
        <svg viewBox="0 0 24 24" style="fill:currentColor;stroke:none"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.13 1.558 5.931L.157 24l6.305-1.654a11.882 11.882 0 0 0 5.587 1.396h.004c6.552 0 11.887-5.335 11.89-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
      </a>
      <?php endif; ?>
      <?php if($cartOn): ?>
      <button class="mf-ico" type="button" id="mf-cart-btn" aria-label="Open cart" aria-haspopup="dialog">
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span class="mf-count" id="cart-count" data-zero="1">0</span>
      </button>
      <?php else: ?>
      <a class="mf-ico" href="<?= $cartUrl; ?>" aria-label="Cart">
        <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
        <span class="mf-count" id="cart-count" data-zero="1">0</span>
      </a>
      <?php endif; ?>
    </div>
  </div>

  <!-- Phone / tablet portrait -->
  <div class="mf-m">
    <div class="mf-m-bar">
      <button class="mf-m-burger" type="button" id="mf-menu-btn" aria-label="Open menu" aria-haspopup="dialog">
        <svg viewBox="0 0 24 24" aria-hidden="true"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
      </button>
      <a class="mf-m-brand" href="<?= $homeUrl; ?>">
        <?php if($logo): ?><img src="<?= htmlspecialchars($logo); ?>" alt="<?= htmlspecialchars($storeName); ?>">
        <?php else: ?><span><?= htmlspecialchars($settings->store_headline ?: $storeName); ?></span><?php endif; ?>
      </a>
      <div class="mf-m-actions">
        <?php if($waUrl !== ''): ?>
        <a class="mf-ico mf-ico--wa" href="<?= $waUrl; ?>" target="_blank" rel="noopener" aria-label="Chat with us on WhatsApp">
          <svg viewBox="0 0 24 24" style="fill:currentColor;stroke:none"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.13 1.558 5.931L.157 24l6.305-1.654a11.882 11.882 0 0 0 5.587 1.396h.004c6.552 0 11.887-5.335 11.89-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
        </a>
        <?php endif; ?>
        <?php if($cartOn): ?>
        <button class="mf-ico" type="button" id="mf-cart-btn-m" aria-label="Open cart" aria-haspopup="dialog">
          <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
          <span class="mf-count" id="cart-count-mobile" data-zero="1">0</span>
        </button>
        <?php else: ?>
        <a class="mf-ico" href="<?= $cartUrl; ?>" aria-label="Cart">
          <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
          <span class="mf-count" id="cart-count-mobile" data-zero="1">0</span>
        </a>
        <?php endif; ?>
      </div>
    </div>
    <?php if($showSearch): ?>
    <div class="mf-m-search mf-headsearch">
      <span class="mf-headsearch-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg></span>
      <input type="search" id="search-input-mobile" placeholder="Search products&hellip;" autocomplete="off"
             aria-label="Search products" onkeydown="if(event.key==='Enter'){event.preventDefault();doSearch(this.value);}">
    </div>
    <?php endif; ?>
  </div>
</div>

<!-- Mobile menu -->
<div class="mf-drawer-bg" id="mf-menu-bg"></div>
<div class="mf-menu" id="mf-menu" role="dialog" aria-modal="true" aria-label="Menu">
  <div class="mf-menu-head">
    <div class="mf-menu-title"><?= htmlspecialchars($storeName); ?></div>
    <button class="mf-modal-x" type="button" id="mf-menu-x" aria-label="Close menu">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
  </div>
  <a class="mf-menu-link" href="<?= $homeUrl; ?>">Home</a>
  <?php if(!empty($sells_products) || $showCats): ?>
  <a class="mf-menu-link" href="<?= $productsUrl; ?>">Shop all</a>
  <?php endif; ?>
  <?php if($svcOn && !empty($sells_services)): ?>
  <a class="mf-menu-link" href="<?= base_url('store/' . $slug . '/services'); ?>">Services</a>
  <?php endif; ?>
  <a class="mf-menu-link" href="<?= base_url('store/' . $slug . '/contact'); ?>">Contact</a>
  <a class="mf-menu-link" href="<?= base_url('store/' . $slug . '/track'); ?>">Track an order</a>

  <?php if($showCats && $cats): ?>
  <div class="mf-menu-group">
    <div class="mf-menu-label">Categories</div>
    <?php foreach(array_slice($cats, 0, 20) as $cat): ?>
    <a class="mf-menu-link" href="<?= $productsUrl; ?>?category=<?= (int)$cat->id; ?>">
      <span><?= htmlspecialchars($cat->category_name); ?></span>
      <?php if(!empty($cat->item_count)): ?><span class="n" style="font-size:12px;color:var(--mf-muted);"><?= (int)$cat->item_count; ?></span><?php endif; ?>
    </a>
    <?php endforeach; ?>
  </div>
  <?php endif; ?>

  <div class="mf-menu-group">
    <div class="mf-menu-label">Reach us</div>
    <div class="mf-menu-contact">
      <?php if(!empty($settings->store_phone)): ?>
      <div><a href="tel:<?= htmlspecialchars(preg_replace('/[^0-9+]/', '', $settings->store_phone)); ?>"><?= htmlspecialchars($settings->store_phone); ?></a></div>
      <?php endif; ?>
      <?php if(!empty($settings->store_email)): ?>
      <div><a href="mailto:<?= htmlspecialchars($settings->store_email); ?>"><?= htmlspecialchars($settings->store_email); ?></a></div>
      <?php endif; ?>
      <?php if($waUrl !== ''): ?>
      <div><a href="<?= $waUrl; ?>" target="_blank" rel="noopener" style="color:var(--mf-wa-text);font-weight:600;">WhatsApp us</a></div>
      <?php endif; ?>
      <?php if(!empty($settings->store_address)): ?>
      <div style="margin-top:6px;"><?= nl2br(htmlspecialchars($settings->store_address)); ?></div>
      <?php endif; ?>
      <?php if(empty($settings->store_phone) && empty($settings->store_email) && $waUrl === '' && empty($settings->store_address)): ?>
      <div>Contact details are not published yet.</div>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Mini cart -->
<div class="mf-drawer-bg" id="mf-cart-bg"></div>
<div class="mf-drawer" id="mf-cart" role="dialog" aria-modal="true" aria-label="Your cart">
  <div class="mf-drawer-head">
    <div class="mf-modal-title">Your cart</div>
    <button class="mf-modal-x" type="button" id="mf-cart-x" aria-label="Close cart">
      <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
    </button>
  </div>
  <div class="mf-drawer-body" id="mf-cart-body"></div>
  <div class="mf-drawer-foot" id="mf-cart-foot"></div>
</div>

<!-- WhatsApp order dialog: one instance, used by the hero, product cards,
     the product page and the cart. -->
<div class="mf-modal" id="mf-wa" role="dialog" aria-modal="true" aria-labelledby="mf-wa-title">
  <div class="mf-modal-bg" id="mf-wa-bg"></div>
  <div class="mf-modal-card">
    <div class="mf-modal-head">
      <h2 class="mf-modal-title" id="mf-wa-title">Send this order on WhatsApp</h2>
      <button class="mf-modal-x" type="button" id="mf-wa-x" aria-label="Close">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
      </button>
    </div>
    <div class="mf-modal-body">
      <div id="mf-wa-single" class="mf-modal-line" hidden>
        <img id="mf-wa-img" src="" alt="">
        <div>
          <div class="mf-modal-line-name" id="mf-wa-name"></div>
          <div class="mf-modal-line-price" id="mf-wa-price"></div>
        </div>
      </div>
      <div id="mf-wa-list" class="mf-modal-summary" hidden></div>

      <div class="mf-modal-fields">
        <div>
          <label class="mf-label" for="mf-wa-qty">Quantity</label>
          <input class="mf-field" type="number" id="mf-wa-qty" value="1" min="1" step="1" inputmode="numeric">
        </div>
        <div>
          <label class="mf-label" for="mf-wa-note">Anything we should know? (optional)</label>
          <textarea class="mf-field" id="mf-wa-note" rows="2" maxlength="500" placeholder="Preferred pickup time, delivery area&hellip;"></textarea>
        </div>
      </div>

      <p class="mf-modal-note" id="mf-wa-note-help">
        Review the details above, then continue. WhatsApp opens with this message ready to send &mdash;
        we confirm availability and the final total in the chat. Nothing is charged here and your cart is kept.
      </p>

      <div class="mf-modal-actions">
        <button class="mf-btn mf-btn--outline" type="button" id="mf-wa-cancel">Cancel</button>
        <button class="mf-btn mf-btn--wa" type="button" id="mf-wa-send" style="flex:1;">
          <svg viewBox="0 0 24 24" style="fill:currentColor;stroke:none" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.13 1.558 5.931L.157 24l6.305-1.654a11.882 11.882 0 0 0 5.587 1.396h.004c6.552 0 11.887-5.335 11.89-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
          Continue on WhatsApp
        </button>
      </div>
    </div>
  </div>
</div>

<script>
/* Market Fresh chrome. Namespaced on window.mf so it can never collide with
   the platform's storefront script (which is loaded after this and redefines
   anything sharing a global name). */
(function(){
  /* JSON_HEX_TAG: these values sit in a script element, so a merchant-controlled
     store name must not be able to close it. Never spell the closing tag here. */
  var CFG = <?= json_encode($mfWa, JSON_HEX_TAG | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
  var SHOW_PRICES = <?= $showPrices ? 'true' : 'false' ?>;
  var CART_ON = <?= $cartOn ? 'true' : 'false' ?>;
  var BASE = <?= json_encode(base_url(), JSON_HEX_TAG | JSON_UNESCAPED_SLASHES); ?>;

  function $(id){ return document.getElementById(id); }
  function money(n){ return (typeof formatMoney === 'function') ? formatMoney(Number(n) || 0) : (Number(n) || 0).toFixed(2); }
  function esc(s){ var d = document.createElement('div'); d.textContent = String(s == null ? '' : s); return d.innerHTML; }
  function readCart(){
    try { var raw = JSON.parse(localStorage.getItem(CFG.cartKey) || '[]'); return Array.isArray(raw) ? raw : []; }
    catch(e){ return []; }
  }
  function unitTotal(i){ return (typeof sfCartUnitTotal === 'function') ? sfCartUnitTotal(i) : (Number(i.price) || 0); }
  function lineTotal(i){ return (typeof sfCartLineTotal === 'function') ? sfCartLineTotal(i) : unitTotal(i) * (Number(i.qty) || 0); }
  function imgUrl(p){
    if(!p) return '';
    if(/^https?:\/\//i.test(p)) return p;
    return BASE + String(p).replace(/^\/+/, '');
  }
  function lock(on){ document.body.style.overflow = on ? 'hidden' : ''; }

  /* ---------- panels ---------- */
  function openPanel(el, bg){ if(el) el.classList.add('open'); if(bg) bg.classList.add('open'); lock(true); }
  function closePanel(el, bg){ if(el) el.classList.remove('open'); if(bg) bg.classList.remove('open');
    if(!$('mf-menu').classList.contains('open') && !$('mf-cart').classList.contains('open') && !$('mf-wa').classList.contains('open')) lock(false); }

  var menu = $('mf-menu'), menuBg = $('mf-menu-bg'), cartEl = $('mf-cart'), cartBg = $('mf-cart-bg');
  ['mf-menu-btn','mf-menu-btn-t','mf-cart-btn','mf-cart-btn-m'].forEach(function(id){
    var b = $(id); if(!b) return;
    b.addEventListener('click', function(){
      if(id.indexOf('mf-menu-btn') === 0) openPanel(menu, menuBg); else renderCart(), openPanel(cartEl, cartBg);
    });
  });
  $('mf-menu-x').addEventListener('click', function(){ closePanel(menu, menuBg); });
  menuBg.addEventListener('click', function(){ closePanel(menu, menuBg); });
  $('mf-cart-x').addEventListener('click', function(){ closePanel(cartEl, cartBg); });
  cartBg.addEventListener('click', function(){ closePanel(cartEl, cartBg); });
  document.addEventListener('keydown', function(e){
    if(e.key !== 'Escape') return;
    closePanel(menu, menuBg); closePanel(cartEl, cartBg); mfWa.close();
  });

  /* ---------- mini cart (read-only; the cart page owns all changes) ---------- */
  function renderCart(){
    var items = readCart(), body = $('mf-cart-body'), foot = $('mf-cart-foot');
    var count = 0, total = 0;
    items.forEach(function(i){ count += Number(i.qty) || 0; total += lineTotal(i); });

    if(!items.length){
      body.innerHTML = '<div class="mf-empty" style="padding:48px 12px;border:none;background:none;">'
        + '<div class="mf-empty-ico"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg></div>'
        + '<div class="mf-empty-title">Your cart is empty</div>'
        + '<p class="mf-empty-text">Browse the shop and add what you need.</p></div>';
      foot.innerHTML = '<a class="mf-btn mf-btn--deep mf-btn--block" href="' + esc(CFG.productsUrl) + '">Start shopping</a>';
      return;
    }

    body.innerHTML = items.slice(0, 30).map(function(i){
      var img = imgUrl(i.image);
      return '<div class="mf-drawer-line">'
        + (img ? '<img src="' + esc(img) + '" alt="" loading="lazy">' : '<div class="mf-drawer-line" style="width:56px;height:56px;background:var(--mf-soft);border-radius:10px;flex:0 0 56px;"></div>')
        + '<div class="mf-drawer-line-info"><div class="mf-drawer-line-name">' + esc(i.name) + '</div>'
        + '<div class="mf-drawer-line-meta">' + (Number(i.qty) || 0) + ' &times; ' + (SHOW_PRICES ? esc(money(unitTotal(i))) : '&mdash;') + '</div></div>'
        + (SHOW_PRICES ? '<div class="mf-drawer-line-meta" style="font-weight:700;color:var(--mf-ink);">' + esc(money(lineTotal(i))) + '</div>' : '')
        + '</div>';
    }).join('');
    if(items.length > 30) body.innerHTML += '<div class="mf-modal-note">Plus ' + (items.length - 30) + ' more &mdash; see the cart for the full list.</div>';

    var html = '';
    if(SHOW_PRICES){
      html += '<div class="mf-modal-total"><span>' + count + (count === 1 ? ' item' : ' items') + '</span><span>' + esc(money(total)) + '</span></div>';
    } else {
      html += '<div class="mf-modal-total"><span>' + count + (count === 1 ? ' item' : ' items') + '</span></div>';
    }
    if(CART_ON){
      html += '<a class="mf-btn mf-btn--deep mf-btn--block" href="' + esc(CFG.cartUrl) + '">View cart &amp; checkout</a>';
    }
    if(CFG.number){
      html += '<button class="mf-btn mf-btn--wa mf-btn--block" type="button" id="mf-cart-wa">Order on WhatsApp</button>';
    }
    foot.innerHTML = html;
    var waBtn = $('mf-cart-wa');
    if(waBtn) waBtn.addEventListener('click', function(){ closePanel(cartEl, cartBg); mfWa.openCart(); });
  }

  // Keep both count badges in step with the platform's own counter.
  var src = $('cart-count');
  if(src && 'MutationObserver' in window){
    new MutationObserver(function(){
      var m = $('cart-count-mobile'); if(!m) return;
      m.textContent = src.textContent; m.setAttribute('data-zero', src.textContent === '0' ? '1' : '0');
    }).observe(src, {childList:true, characterData:true, subtree:true});
  }
  if(src) src.setAttribute('data-zero', (src.textContent || '0') === '0' ? '1' : '0');

  /* ---------- WhatsApp order dialog ---------- */
  var state = null, sending = false;
  var waEl = $('mf-wa'), waBg = $('mf-wa-bg');

  function fillList(){
    var items = readCart(), total = 0, lines = [];
    items.forEach(function(i, n){
      var lt = lineTotal(i); total += lt;
      lines.push('<div class="mf-modal-row"><span>' + (n + 1) + '. ' + esc(i.name)
        + (i.variant_name ? ' <span class="q">(' + esc(i.variant_name) + ')</span>' : '')
        + '</span><span class="q">' + (Number(i.qty) || 0) + ' &times; ' + (SHOW_PRICES ? esc(money(unitTotal(i))) : '&mdash;')
        + (SHOW_PRICES ? ' = ' + esc(money(lt)) : '') + '</span></div>');
    });
    if(SHOW_PRICES){
      lines.push('<div class="mf-modal-total"><span>Subtotal</span><span>' + esc(money(total)) + '</span></div>');
    }
    $('mf-wa-list').innerHTML = lines.join('');
    $('mf-wa-list').hidden = false;
    $('mf-wa-single').hidden = true;
    $('mf-wa-qty').closest('div').style.display = 'none';
    return {items: items, total: total};
  }

  var mfWa = {
    openProduct: function(p){
      if(!CFG.number){ if(typeof showToast === 'function') showToast('WhatsApp ordering is not set up for this store'); return; }
      state = {mode:'product', product:p};
      var img = $('mf-wa-img'), src = imgUrl(p.image);
      if(src){ img.src = src; img.style.display = ''; img.alt = p.name || ''; } else { img.removeAttribute('src'); img.style.display = 'none'; }
      $('mf-wa-name').textContent = p.name || '';
      $('mf-wa-price').textContent = (p.unit ? p.unit + ' \u00b7 ' : '') + (SHOW_PRICES ? money(p.price) : 'Price on request');
      $('mf-wa-single').hidden = false;
      $('mf-wa-list').hidden = true;
      $('mf-wa-list').innerHTML = '';
      var qtyWrap = $('mf-wa-qty').closest('div');
      qtyWrap.style.display = '';
      $('mf-wa-qty').value = Number(p.qty) > 0 ? Number(p.qty) : 1;
      $('mf-wa-note').value = p.note || '';
      openPanel(waEl, waBg);
    },
    openCart: function(){
      if(!CFG.number){ if(typeof showToast === 'function') showToast('WhatsApp ordering is not set up for this store'); return; }
      var filled = fillList();
      if(!filled.items.length){ if(typeof showToast === 'function') showToast('Your cart is empty'); return; }
      state = {mode:'cart', items:filled.items, total:filled.total};
      $('mf-wa-note').value = '';
      openPanel(waEl, waBg);
    },
    openGeneral: function(){
      if(!CFG.number){ if(typeof showToast === 'function') showToast('WhatsApp ordering is not set up for this store'); return; }
      // A greeting carrying the merchant's own store name, so the customer does
      // not land in an empty chat. Nothing else is claimed on their behalf.
      var msg = 'Hello' + (CFG.store ? ' ' + CFG.store : '') + '! I have a question.';
      var w = window.open('https://wa.me/' + CFG.number + '?text=' + encodeURIComponent(msg), '_blank', 'noopener');
      if(!w && typeof showToast === 'function') showToast('Your browser blocked the new tab. Allow pop-ups to continue on WhatsApp.');
    },
    close: function(){ state = null; closePanel(waEl, waBg); }
  };
  window.mfWhatsApp = mfWa;

  $('mf-wa-x').addEventListener('click', mfWa.close);
  $('mf-wa-cancel').addEventListener('click', mfWa.close);
  waBg.addEventListener('click', mfWa.close);

  $('mf-wa-qty').addEventListener('input', function(){
    if(!state || state.mode !== 'product') return;
    var q = Math.max(1, parseInt(this.value, 10) || 1);
    var p = state.product;
    $('mf-wa-price').textContent = (p.unit ? p.unit + ' \u00b7 ' : '')
      + (SHOW_PRICES ? money((Number(p.price) || 0) * q) + ' (' + q + ' \u00d7 ' + money(p.price) + ')' : 'Price on request');
  });

  $('mf-wa-send').addEventListener('click', function(){
    if(sending || !state) return;
    var note = $('mf-wa-note').value.trim().slice(0, 500);
    var lines = [], total = 0;

    if(state.mode === 'product'){
      var p = state.product, q = Math.max(1, parseInt($('mf-wa-qty').value, 10) || 1);
      var lt = (Number(p.price) || 0) * q; total = lt;
      lines.push('1. ' + p.name + (p.unit ? ' (' + p.unit + ')' : '') + (SHOW_PRICES ? ' \u2014 ' + q + ' \u00d7 ' + money(p.price) + ' = ' + money(lt) : ' \u2014 ' + q));
    } else {
      state.items.forEach(function(i, n){
        var lt = lineTotal(i); total += lt;
        lines.push((n + 1) + '. ' + i.name + (i.variant_name ? ' (' + i.variant_name + ')' : '')
          + (SHOW_PRICES ? ' \u2014 ' + (Number(i.qty) || 0) + ' \u00d7 ' + money(unitTotal(i)) + ' = ' + money(lt) : ' \u2014 ' + (Number(i.qty) || 0)));
      });
    }

    var msg = "Hello! I'd like to place an order.\n\nOrder details:\n" + lines.join('\n');
    if(SHOW_PRICES) msg += '\n\nSubtotal: ' + money(total);
    msg += '\n\nPlease confirm availability' + (state.mode === 'cart' ? ', delivery or pickup, and the final total' : ' and the final total') + '.';
    if(note) msg += '\n\nNote: ' + note;
    msg += '\n\nSent from ' + CFG.store + '.';

    sending = true;
    var btn = this, label = btn.innerHTML;
    btn.disabled = true; btn.style.opacity = '.7';
    // The cart is never cleared here: if WhatsApp does not open, the customer
    // still has everything they picked.
    var w = window.open('https://wa.me/' + CFG.number + '?text=' + encodeURIComponent(msg), '_blank', 'noopener');
    setTimeout(function(){
      sending = false; btn.disabled = false; btn.style.opacity = ''; btn.innerHTML = label;
      if(!w && typeof showToast === 'function') showToast('Your browser blocked the new tab. Allow pop-ups to continue on WhatsApp.');
      else { mfWa.close(); if(typeof showToast === 'function') showToast('WhatsApp opened with your order \u2014 send it to complete your request.'); }
    }, 600);
  });

  /* ---------- product cards ----------
     Every card carries its facts in one JSON attribute, so no product string is
     ever concatenated into an inline handler again. The listener is delegated,
     so cards on any screen of this theme behave identically. */
  function openCartDrawer(){ renderCart(); openPanel(cartEl, cartBg); }
  window.mfCart = { open: openCartDrawer };

  document.addEventListener('click', function(e){
    var trigger = e.target.closest ? e.target.closest('[data-mf-action]') : null;
    if(!trigger) return;
    var card = trigger.closest('[data-mf-item]');
    if(!card) return;
    var d;
    try { d = JSON.parse(card.getAttribute('data-mf-item')); } catch(err){ return; }
    if(!d || !d.id) return;

    var action = trigger.getAttribute('data-mf-action');
    if(action === 'view'){
      // Variant parents must be chosen on their own page; and a modified or
      // non-primary click belongs to the browser, not to the quick view.
      if(d.variants || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
      e.preventDefault();
      if(typeof openProductModal !== 'function'){ window.location.href = d.url; return; }
      openProductModal(d.id, d.name, d.price, d.image, d.desc, d.stock, d.was, d.type);
      return;
    }
    if(action === 'add'){
      e.preventDefault();
      if(d.variants){ window.location.href = d.url; return; }
      if(typeof addToCart !== 'function') return;
      // addToCart already refuses out-of-stock lines and says why.
      if(addToCart(d.id, d.type, d.name, d.price, d.image, 1, d.stock) !== false) openCartDrawer();
      return;
    }
    if(action === 'wa'){
      e.preventDefault();
      if(d.variants){ window.location.href = d.url; return; }
      mfWa.openProduct({
        id: d.id, name: d.name, price: d.price, image: d.image, unit: d.unit,
        note: d.oos ? 'Is this back in stock? Please let me know when it is available.' : ''
      });
    }
  });

  /* ---------- small-screen search hand-off ----------
     The shared bottom nav focuses #search-input, which is inside the desktop
     header. On a phone that element is hidden, so point at the visible field. */
  var mnav = document.querySelector('.mp-mobile-nav');
  if(mnav){
    mnav.addEventListener('click', function(e){
      var btn = e.target.closest ? e.target.closest('button.mp-mobile-nav-item') : null;
      if(!btn) return;
      var d = $('search-input');
      if(d && d.offsetParent !== null) return;
      var m = $('search-input-mobile');
      if(m){ m.focus(); m.scrollIntoView({behavior:'smooth', block:'center'}); }
    });
  }
})();
</script>
