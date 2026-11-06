<?php
/**
 * Market Fresh — product detail.
 *
 * Everything stated here is a fact the platform recorded: the resolved catalogue
 * price, the selling unit, the category, the item code and the ledger stock
 * level. No rating, no review count and no delivery promise is invented — reviews
 * render from the shared partial only when this store switched them on.
 *
 * Variant selection, restock sign-up, the gallery, the share panel, add-ons and
 * reviews all stay on their shared partials, so checkout and payment behave
 * exactly as they do on every other theme.
 */
$this->load->view('themes/market_fresh/_helpers');

$p = $product ?? null;
if (!$p) return;

$slug = (string)($settings->store_slug ?? '');
$cur = $store_currency ?? null;
$storeName = trim((string)($store->store_name ?? '')) ?: 'Our store';
$productsUrl = base_url('store/' . $slug . '/products');

$waNumber = preg_replace('/[^0-9]/', '', (string)($settings->whatsapp_number ?? ''));
$allowBackorder = !empty($settings->allow_backorder);
$cartOn = !empty($cart_enabled);
$showPrices = !empty($show_prices);
$showStock = (int)($settings->show_sale_stock_labels ?? 1) === 1;

$price = mf_price($p);
$was = mf_was_price($p);
$save = $was > $price ? $was - $price : 0;
$savePct = $was > 0 && $save > 0 ? (int)round(($save / $was) * 100) : 0;

$stock = (int)($p->stock ?? 0);
$alertQty = (int)($p->alert_qty ?? 0);
$oos = $stock <= 0 && !$allowBackorder;
$low = !$oos && $alertQty > 0 && $stock <= $alertQty;
$isParent = ($p->item_group ?? '') === 'Variants';

$name = trim((string)($p->item_name ?? ''));
$unit = trim((string)($p->unit_name ?? ''));
$catName = trim((string)($p->category_name ?? ''));
$catId = (int)($p->category_id ?? 0);
$itemCode = trim((string)($p->item_code ?? ''));
$desc = trim((string)($p->description ?? ''));
$img = mf_image_url((string)($p->item_image ?? ''), 1000);
$maxQty = $oos ? 1 : ($allowBackorder || $stock <= 0 ? 99 : $stock);

$related = array_values(array_filter(
    (isset($related_products) && is_array($related_products)) ? $related_products : [],
    function($r) use ($p){ return is_object($r) && (int)$r->id !== (int)$p->id; }
));

$ctx = [
    'slug' => $slug,
    'cur' => $cur,
    'brandLabel' => $storeName,
    'showPrices' => $showPrices,
    'showStock' => $showStock,
    'waOn' => $waNumber !== '',
    'cartOn' => $cartOn,
    'backorder' => $allowBackorder,
];

$mfFacts = [];
if ($catName !== '') $mfFacts['Category'] = ['tag', $catId ? $productsUrl . '?category=' . $catId : '', $catName];
if ($unit !== '') $mfFacts['Sold per'] = ['text', '', $unit];
if ($itemCode !== '') $mfFacts['Item code'] = ['text', '', $itemCode];
if ($showStock) {
    $mfFacts['Availability'] = ['text', '', $oos ? 'Out of stock' : ($low ? 'Low stock — ' . $stock . ' left' : 'In stock')];
}
$mfFactIcons = [
    'Category' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7l9-4 9 4-9 4-9-4z"/><path d="M3 12l9 4 9-4"/><path d="M3 17l9 4 9-4"/></svg>',
    'Sold per' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2h12l2 6a4 4 0 0 1-8 0 4 4 0 0 1-8 0z"/><path d="M6 12v10h12V12"/></svg>',
    'Item code' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 5v14M8 5v14M12 5v14M16 5v14M20 5v14"/></svg>',
    'Availability' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
];
?>

<div class="mf-wrap">
  <nav class="mf-crumbs" aria-label="Breadcrumb">
    <a href="<?= mf_e(base_url('store/' . $slug)); ?>">Home</a>
    <span class="sep" aria-hidden="true">/</span>
    <a href="<?= mf_e($productsUrl); ?>">Products</a>
    <?php if ($catName !== ''): ?>
    <span class="sep" aria-hidden="true">/</span>
    <a href="<?= mf_e($catId ? $productsUrl . '?category=' . $catId : $productsUrl); ?>"><?= mf_e($catName); ?></a>
    <?php endif; ?>
    <span class="sep" aria-hidden="true">/</span>
    <span aria-current="page"><?= mf_e($name); ?></span>
  </nav>

  <div class="mf-detail">
    <div class="mf-detail-media">
      <?php if ($savePct > 0 && $showPrices): ?>
      <span class="mf-detail-flag"><?= $savePct; ?>% off</span>
      <?php endif; ?>
      <?php if ($img !== ''): ?>
      <img src="<?= mf_e($img); ?>" alt="<?= mf_e($name); ?>" width="1000" height="1000" fetchpriority="high" decoding="async">
      <?php else: ?>
      <?php mf_art_tile($storeName); ?>
      <?php endif; ?>
    </div>

    <div class="mf-detail-info">
      <?php if ($catName !== ''): ?>
      <a class="mf-detail-cat" href="<?= mf_e($catId ? $productsUrl . '?category=' . $catId : $productsUrl); ?>"><?= mf_e($catName); ?></a>
      <?php endif; ?>

      <h1 class="mf-detail-name"><?= mf_e($name); ?></h1>

      <?php if ($showPrices): ?>
      <p class="mf-detail-price">
        <span><?= sf_currency($price, $cur); ?></span>
        <?php if ($was > $price): ?>
        <span class="was"><?= sf_currency($was, $cur); ?></span>
        <?php if ($save > 0): ?>
        <span class="mf-detail-save">Save <?= sf_currency($save, $cur); ?></span>
        <?php endif; ?>
        <?php endif; ?>
        <?php if ($unit !== ''): ?>
        <span class="mf-note">per <?= mf_e($unit); ?></span>
        <?php endif; ?>
      </p>
      <?php else: ?>
      <p class="mf-detail-price"><span>Price on request</span></p>
      <?php endif; ?>

      <?php if ($showStock): ?>
      <p class="mf-p-stock<?= $oos ? ' mf-p-stock--out' : ($low ? ' mf-p-stock--low' : ''); ?>">
        <?= $oos ? 'Out of stock' : ($low ? 'Low stock — ' . (int)$stock . ' left' : 'In stock'); ?>
      </p>
      <?php endif; ?>

      <?php if ($desc !== ''): ?>
      <div class="mf-detail-desc"><?= nl2br(mf_e($desc)); ?></div>
      <?php endif; ?>

      <?php $this->load->view('themes/shared/variant_picker'); ?>

      <?php if ($cartOn): ?>
      <div class="mf-buy">
        <div class="mf-buy-row">
          <span class="mf-label" id="mf-qty-label" style="margin:0;">Quantity</span>
          <div class="mf-qty" role="group" aria-labelledby="mf-qty-label">
            <button type="button" id="mf-qty-minus" aria-label="Decrease quantity">&minus;</button>
            <span class="mf-qty-val" id="mf-detail-qty" aria-live="polite">1</span>
            <button type="button" id="mf-qty-plus" aria-label="Increase quantity">+</button>
          </div>
        </div>

        <div class="mf-buy-actions">
          <button type="button" class="mf-btn mf-btn--deep" id="mf-detail-add"<?= $oos ? ' disabled' : ''; ?>>
            <?php if ($oos): ?>Out of stock
            <?php elseif ($isParent): ?>Choose an option
            <?php else: ?>Add to cart<?php endif; ?>
          </button>
          <?php if ($waNumber !== ''): ?>
          <button type="button" class="mf-btn mf-btn--wa" id="mf-detail-wa">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.5 14.4c-.3-.1-1.8-.9-2-1-.3-.1-.5-.1-.7.2-.2.3-.7 1-.9 1.2-.2.2-.3.2-.6.1-.3-.2-1.3-.5-2.4-1.5-.9-.8-1.5-1.8-1.7-2.1-.2-.3 0-.5.1-.6l.5-.5c.1-.2.2-.3.3-.5.1-.2 0-.4 0-.5-.1-.2-.7-1.6-.9-2.2-.2-.6-.5-.5-.7-.5h-.6c-.2 0-.5.1-.8.4-.3.3-1.1 1-1.1 2.5s1.1 2.9 1.2 3.1c.2.2 2.1 3.2 5.1 4.5.7.3 1.3.5 1.7.6.7.2 1.4.2 1.9.1.6-.1 1.8-.7 2-1.4.2-.7.2-1.3.2-1.4-.1-.1-.3-.2-.6-.3m-5.4 7.4a9.9 9.9 0 0 1-5-1.4l-.4-.2-3.7 1 1-3.6-.2-.4a9.9 9.9 0 0 1-1.5-5.3C3.7 6.5 8.1 2 13.6 2c2.6 0 5.1 1 7 2.9a9.8 9.8 0 0 1 2.9 7c0 5.4-4.4 9.9-9.9 9.9m8.4-18.3A11.8 11.8 0 0 0 12 .5C5.5.5.2 5.8.2 12.4c0 2.1.5 4.1 1.6 5.9L.2 24l6.3-1.7a11.9 11.9 0 0 0 5.6 1.4c6.6 0 11.9-5.3 11.9-11.9 0-3.2-1.2-6.2-3.5-8.3"/></svg>
            <?= $oos ? 'Ask about availability' : 'Order on WhatsApp'; ?>
          </button>
          <?php endif; ?>
        </div>
      </div>
      <?php elseif ($waNumber !== ''): ?>
      <div class="mf-buy">
        <div class="mf-buy-actions">
          <button type="button" class="mf-btn mf-btn--wa" id="mf-detail-wa">Order on WhatsApp</button>
        </div>
      </div>
      <?php endif; ?>

      <?php if ($oos) $this->load->view('themes/shared/restock_subscribe', ['sf_item_id' => (int)$p->id]); ?>

      <?php if ($mfFacts): ?>
      <ul class="mf-facts">
        <?php foreach ($mfFacts as $label => $fact): ?>
        <li class="mf-fact">
          <span class="mf-fact-ico" aria-hidden="true"><?= $mfFactIcons[$label] ?? ''; ?></span>
          <span class="mf-fact-body">
            <span class="mf-fact-k"><?= mf_e($label); ?></span>
            <?php if ($fact[1] !== ''): ?>
            <a class="mf-fact-v" href="<?= mf_e($fact[1]); ?>"><?= mf_e($fact[2]); ?></a>
            <?php else: ?>
            <span class="mf-fact-v"><?= mf_e($fact[2]); ?></span>
            <?php endif; ?>
          </span>
        </li>
        <?php endforeach; ?>
      </ul>
      <?php endif; ?>
    </div>
  </div>
</div>

<?php if ($related): ?>
<section class="mf-sec mf-sec--soft">
  <div class="mf-wrap">
    <header class="mf-sec-head">
      <div>
        <p class="mf-kicker">More like this</p>
        <h2 class="mf-title">You may also like</h2>
      </div>
      <a class="mf-sec-link" href="<?= mf_e($productsUrl); ?>">View all</a>
    </header>
    <?php mf_card_grid($related, $ctx, 'product', 4); ?>
  </div>
</section>
<?php endif; ?>

<script>
(function(){
  var qtyEl = document.getElementById('mf-detail-qty');
  var addBtn = document.getElementById('mf-detail-add');
  var waBtn = document.getElementById('mf-detail-wa');
  var MAX = <?= (int)$maxQty; ?>;
  var qty = 1;

  function render(){
    if(!qtyEl) return;
    qtyEl.textContent = String(qty);
    var minus = document.getElementById('mf-qty-minus');
    var plus = document.getElementById('mf-qty-plus');
    if(minus) minus.disabled = qty <= 1;
    if(plus) plus.disabled = qty >= MAX;
  }
  function step(delta){
    var next = qty + delta;
    if(next < 1 || next > MAX) return;
    qty = next; render();
  }

  var minus = document.getElementById('mf-qty-minus');
  var plus = document.getElementById('mf-qty-plus');
  if(minus) minus.addEventListener('click', function(){ step(-1); });
  if(plus) plus.addEventListener('click', function(){ step(1); });
  render();

  if(addBtn && !addBtn.disabled){
    addBtn.addEventListener('click', function(){
      if(typeof addToCart !== 'function') return;
      // addToCart blocks a variant parent, an out-of-stock line and an
      // over-quantity line itself, and says why — so only a real success
      // should open the basket.
      var ok = addToCart(<?= (int)$p->id; ?>, 'product', <?= mf_js($name); ?>,
        <?= (float)$price; ?>, <?= mf_js((string)($p->item_image ?? '')); ?>, qty, <?= (int)$stock; ?>);
      if(ok !== false && window.mfCart) mfCart.open();
    });
  }

  if(waBtn && window.mfWhatsApp){
    waBtn.addEventListener('click', function(){
      mfWhatsApp.openProduct({
        id: <?= (int)$p->id; ?>,
        name: <?= mf_js($name); ?>,
        price: <?= (float)$price; ?>,
        image: <?= mf_js((string)($p->item_image ?? '')); ?>,
        unit: <?= mf_js($unit); ?>,
        qty: qty,
        note: <?= mf_js($oos ? 'Is this back in stock? Please let me know when it is available.' : ''); ?>
      });
    });
  }
})();
</script>
