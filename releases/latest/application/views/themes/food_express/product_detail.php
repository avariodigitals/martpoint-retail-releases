<?php
$product = $product ?? null;
if(!$product){ return; }

$showSaleStockLabels = (int)($settings->show_sale_stock_labels ?? 1) === 1;
$price = $product->effective_price ?? $product->sales_price ?? $product->price;
$fxBasePrice = (float)$price;
$oldPrice = $product->original_price ?? $product->price;
$hasDiscount = $showSaleStockLabels && $oldPrice > $price;
$discountPct = $hasDiscount ? round((($oldPrice - $price) / $oldPrice) * 100) : 0;
$pType = $product->product_type ?? 'physical';
$isPhysical = $pType === 'physical';
$outOfStock = $isPhysical && ($product->stock ?? 0) <= 0 && !($settings->allow_backorder ?? false);
$fxProductDescription = trim((string)($product->description ?? ''));
if (preg_match('/^Demo .* product from Zonat\.$/i', $fxProductDescription)) {
  $fxProductDescription = '';
}
$spiceCategory = implode(' / ', array_filter([
  $product->category_name ?? '',
  $product->category_path ?? '',
  $product->category ?? '',
]));
$foodKind = mp_food_product_kind(
  $product->item_name ?? '',
  $spiceCategory,
  $product->description ?? ''
);
$fulfilmentCopy = mp_food_product_fulfilment_copy($foodKind, $outOfStock);
$fxProductOutlets = !empty($outlets) && is_array($outlets) ? array_values($outlets) : [];
$showSpiceLevel = mp_food_spice_allowed(
  $product->item_name ?? '',
  $spiceCategory,
  $product->description ?? ''
);
?>
<div class="fx-section fx-meal-page" style="padding-top:16px;">
  <div class="fx-section-inner">
    <!-- Breadcrumb -->
    <nav style="margin-bottom:24px; font-size:13px; color:var(--fx-muted);" aria-label="Breadcrumb">
      <a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>" style="color:inherit;">Home</a>
      <span style="margin:0 8px; color:var(--fx-border);">/</span>
      <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>" style="color:inherit;">Menu</a>
      <span style="margin:0 8px; color:var(--fx-border);">/</span>
      <span style="color:var(--fx-ink); font-weight:600;"><?= htmlspecialchars($product->item_name); ?></span>
    </nav>

    <?php if(!empty($fxProductOutlets)): ?>
    <details class="fx-product-location" id="fx-product-location-picker">
      <summary>
        <span class="fx-product-location-pin" aria-hidden="true">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/><circle cx="12" cy="10" r="2.5"/></svg>
        </span>
        <span class="fx-product-location-copy"><small>Ordering from</small><strong id="fx-product-location-name">Choose a kitchen</strong></span>
        <span class="fx-product-location-change">Choose location</span>
        <svg class="fx-product-location-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
      </summary>
      <div class="fx-product-location-options" role="radiogroup" aria-label="Choose ordering location">
        <?php foreach($fxProductOutlets as $fxOutlet):
          $fxOutletId = (int)($fxOutlet->id ?? 0);
          $fxOutletName = (string)($fxOutlet->warehouse_name ?? 'Kitchen');
          $fxOutletAddress = trim(implode(', ', array_filter([(string)($fxOutlet->address ?? ''), (string)($fxOutlet->city ?? '')])));
        ?>
        <button type="button" class="fx-product-location-option" data-outlet-id="<?= $fxOutletId; ?>" data-outlet-name="<?= htmlspecialchars($fxOutletName, ENT_QUOTES, 'UTF-8'); ?>" onclick="selectFxProductOutlet(this, <?= $fxOutletId; ?>)" role="radio" aria-checked="false">
          <span><strong><?= htmlspecialchars($fxOutletName); ?></strong><?php if($fxOutletAddress): ?><small><?= htmlspecialchars($fxOutletAddress); ?></small><?php endif; ?></span>
          <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
        </button>
        <?php endforeach; ?>
      </div>
    </details>
    <?php endif; ?>

    <div class="fx-product-detail">
      <!-- Gallery -->
      <div class="fx-product-gallery">
        <div class="fx-product-hero-image">
          <?php if(!empty($product->item_image) && sf_product_image_exists($product->item_image)): ?>
            <img class="fx-main-img" src="<?= preg_match('~^https?://~i', (string)$product->item_image) ? $product->item_image : base_url(ltrim($product->item_image, '/')); ?>" alt="<?= htmlspecialchars($product->item_name); ?>">
          <?php else: ?>
            <div class="fx-card-placeholder" aria-label="<?= htmlspecialchars($store->store_name ?? 'Restaurant'); ?>"><?= htmlspecialchars($store->store_name ?? 'Restaurant'); ?></div>
          <?php endif; ?>
        </div>
        <?php if(!empty($product_gallery_images)): ?>
        <div class="fx-product-thumbnails">
          <?php foreach($product_gallery_images as $img): ?>
            <div class="fx-product-thumb" onclick="document.querySelector('.fx-main-img').src=this.querySelector('img').src">
              <img src="<?= base_url($img->image_path); ?>" alt="">
            </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
      </div>

      <!-- Info & Add to Cart -->
      <div class="fx-product-info">
        <h1 class="fx-product-title"><?= htmlspecialchars($product->item_name); ?></h1>

        <?php if($product->category_name): ?>
        <span class="fx-product-category"><?= htmlspecialchars($product->category_name); ?></span>
        <?php endif; ?>

        <?php if($fxProductDescription !== ''): ?><div class="fx-product-description">
          <?= nl2br(htmlspecialchars($fxProductDescription)); ?>
        </div><?php endif; ?>

        <div class="fx-product-price">
          <span class="fx-card-price" style="font-size:28px;"><?= sf_currency($price, $store_currency ?? null); ?></span>
          <?php if($hasDiscount): ?>
            <span class="fx-card-old-price" style="font-size:16px;"><?= sf_currency($oldPrice, $store_currency ?? null); ?></span>
            <span class="fx-card-discount">-<?= $discountPct; ?>%</span>
          <?php endif; ?>
        </div>

        <!-- Spice Level / Customization -->
        <?php if($showSpiceLevel): ?>
        <div class="fx-product-option fx-product-spice">
          <div class="fx-option-heading"><h2>Spice level</h2><small>Choose one</small></div>
          <div class="fx-spice-levels" role="radiogroup" aria-label="Spice level">
            <?php foreach(['mild' => 'Mild', 'medium' => 'Medium', 'hot' => 'Hot', 'extra' => 'Extra hot'] as $heat => $label): ?>
            <button type="button" class="fx-spice-btn <?= $heat === 'mild' ? 'active' : ''; ?>" data-spice="<?= $heat; ?>" onclick="selectSpice(this)" role="radio" aria-checked="<?= $heat === 'mild' ? 'true' : 'false'; ?>">
              <span class="fx-heat-mark" aria-hidden="true"><?= str_repeat('●', array_search($heat, ['mild','medium','hot','extra']) + 1); ?></span><span><?= $label; ?></span>
            </button>
            <?php endforeach; ?>
          </div>
          <input type="hidden" id="selected-spice" name="spice_level" value="mild">
        </div>
        <?php endif; ?>

        <!-- Addons -->
        <?php if(!empty($product_addons)): ?>
        <div class="fx-product-option fx-product-addons">
          <div class="fx-option-heading"><h2>Make it yours</h2><small>Optional extras</small></div>
          <?php foreach($product_addons as $addon):
            $addonName = $addon->name ?? $addon->addon_name ?? '';
            $addonMax = max(1, (int)($addon->max_qty ?? 1));
          ?>
          <div class="fx-extra-row">
            <label><input type="checkbox" class="fx-addon-checkbox" value="<?= (int)$addon->id; ?>" data-name="<?= htmlspecialchars($addonName, ENT_QUOTES, 'UTF-8'); ?>" data-price="<?= (float)$addon->price; ?>" onchange="updateAddons()"><span><?= htmlspecialchars($addonName); ?></span></label>
            <span class="fx-extra-price">+ <?= sf_currency($addon->price, $store_currency ?? null); ?></span>
            <?php if($addonMax > 1): ?><input type="number" class="fx-extra-qty" min="1" max="<?= $addonMax; ?>" value="1" disabled aria-label="Quantity for <?= htmlspecialchars($addonName, ENT_QUOTES, 'UTF-8'); ?>" onchange="updateAddons()"><?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
        <?php endif; ?>
        <div class="fx-product-option fx-product-instructions">
          <div class="fx-option-heading"><label for="fx-meal-note">Special instructions</label><small>Optional</small></div>
          <textarea id="fx-meal-note" maxlength="500" rows="2" placeholder="Anything you’d like us to know?"></textarea>
          <p>Requests depend on kitchen availability. Contact us about allergies before ordering.</p>
        </div>
        <div class="fx-purchase-bar">
        <!-- Quantity & Add to Cart -->
        <div class="fx-product-quantity">
          <div class="fx-quantity-control">
            <button type="button" onclick="changeQty(-1)" aria-label="Decrease quantity" style="width:44px;height:44px;display:flex;align-items:center;justify-content:center;background:var(--fx-cream);border:none;color:var(--fx-ink);font-size:18px;">−</button>
            <input type="number" id="product-qty" value="1" min="1" oninput="updateAddons()" max="<?= !empty($settings->allow_backorder) ? 999 : max(1, (int)($product->stock ?? 99)); ?>" style="width:60px;height:44px;text-align:center;border:none;background:var(--fx-white);font-size:16px;font-weight:600;color:var(--fx-ink);outline:none;">
            <button type="button" onclick="changeQty(1)" aria-label="Increase quantity" style="width:44px;height:44px;display:flex;align-items:center;justify-content:center;background:var(--fx-cream);border:none;color:var(--fx-ink);font-size:18px;">+</button>
          </div>
        </div>

        <button class="fx-card-add fx-product-add" onclick="addToCartDetail(<?= $product->id; ?>,'<?= $pType; ?>','<?= htmlspecialchars(addslashes($product->item_name)); ?>',<?= $price; ?>,'<?= $product->item_image; ?>',document.getElementById('product-qty').value,<?= $product->stock ?? 0; ?>);" <?= $outOfStock ? 'disabled' : ''; ?>>
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          <span><?= $outOfStock ? 'Unavailable' : 'Add to basket'; ?> · <span id="fx-purchase-total"><?= sf_currency($fxBasePrice, $store_currency ?? null); ?></span></span>
        </button>

        </div>

        <?php if(!empty($settings->whatsapp_number) && ($settings->allow_whatsapp ?? 1)): ?>
        <button class="fx-card-wa fx-product-whatsapp" onclick="orderProductOnWhatsApp('<?= htmlspecialchars(addslashes($product->item_name)); ?>',<?= $price; ?>);">
          <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.13 1.558 5.931L.157 24l6.305-1.654a11.882 11.882 0 0 0 5.587 1.396h.004c6.552 0 11.887-5.335 11.89-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
          Order on WhatsApp
        </button>
        <?php endif; ?>

        <!-- Meta info -->
        <div class="fx-product-meta">
          <?php if($isPhysical): ?>
          <div style="display:flex; align-items:center; gap:8px; margin-bottom:6px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--fx-green);"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><polyline points="3.27 6.96 12 12.01 20.73 6.96"/><line x1="12" y1="22.08" x2="12" y2="12"/></svg>
            <span><?= htmlspecialchars($fulfilmentCopy['availability']); ?></span>
          </div>
          <?php endif; ?>
          <div style="display:flex; align-items:center; gap:8px;">
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--fx-green);"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            <span><?= htmlspecialchars($fulfilmentCopy['timing']); ?></span>
          </div>
        </div>
      </div>
    </div>

    <?php $this->load->view('themes/food_express/_pairings', ['food_pairings' => $food_pairings ?? []]); ?>
  </div>
</div>

<script>
function selectSpice(btn){
  document.querySelectorAll('.fx-spice-btn').forEach(b => {
    b.classList.remove('active');
    b.setAttribute('aria-checked', 'false');
  });
  btn.classList.add('active');
  btn.setAttribute('aria-checked', 'true');
  document.getElementById('selected-spice').value = btn.dataset.spice;
}

const fxMealBasePrice = <?= json_encode($fxBasePrice); ?>;
function changeQty(delta){
  const input = document.getElementById('product-qty');
  input.value = Math.max(1, Math.min(Number(input.max) || 999, (parseInt(input.value,10) || 1) + delta));
  updateAddons();
}
function fxSelectedExtras(){
  return Array.from(document.querySelectorAll('.fx-addon-checkbox:checked')).map(cb => {
    const quantity = cb.closest('.fx-extra-row').querySelector('.fx-extra-qty');
    const qty = quantity ? Math.max(1, Math.min(Number(quantity.max), parseInt(quantity.value,10)||1)) : 1;
    if(quantity) quantity.value = qty;
    return {id:Number(cb.value), name:cb.dataset.name, price:Number(cb.dataset.price)||0, qty:qty};
  });
}
function updateAddons(){
  document.querySelectorAll('.fx-extra-row').forEach(row => {
    const qty = row.querySelector('.fx-extra-qty');
    if(qty) qty.disabled = !row.querySelector('.fx-addon-checkbox').checked;
  });
  const input = document.getElementById('product-qty');
  const qty = Math.max(1, Math.min(Number(input.max)||999, parseInt(input.value,10)||1));
  input.value = qty;
  const total = (fxMealBasePrice + fxSelectedExtras().reduce((sum,a)=>sum+a.price*a.qty,0))*qty;
  document.getElementById('fx-purchase-total').textContent = formatMoney(total);
}
function addToCartDetail(id, type, name, price, image, qty, stock){
  updateAddons();
  const picker = document.getElementById('fx-product-location-picker');
  const saved = Number(localStorage.getItem('sf_outlet_' + STORE_ID) || 0);
  if(picker && !Array.from(picker.querySelectorAll('[data-outlet-id]')).some(el=>Number(el.dataset.outletId)===saved)){
    picker.open = true;
    picker.scrollIntoView({behavior:'smooth',block:'center'});
    showToast('Choose your kitchen first');
    return;
  }
  const spice = document.getElementById('selected-spice');
  addToCart(id, type, name, price, image, Number(document.getElementById('product-qty').value), stock, {
    addons:fxSelectedExtras(), spice_level:spice ? spice.value : '',
    note:document.getElementById('fx-meal-note').value.trim()
  });
}

function selectFxProductOutlet(element, outletId){
  const name = element?.dataset?.outletName || 'Selected location';
  localStorage.setItem('sf_outlet_' + STORE_ID, String(outletId));
  document.querySelectorAll('.fx-product-location-option').forEach(option => {
    const active = Number(option.dataset.outletId) === Number(outletId);
    option.classList.toggle('active', active);
    option.setAttribute('aria-checked', active ? 'true' : 'false');
  });
  document.querySelectorAll('[data-fx-location-value]').forEach(node => node.textContent = name);
  const label = document.getElementById('fx-product-location-name');
  if(label) label.textContent = name;
  const picker = document.getElementById('fx-product-location-picker');
  if(picker) picker.open = false;
  updateFxPairings(outletId);
}

function updateFxPairings(outletId){
  const section = document.getElementById('fx-pairings');
  if(!section) return;
  const panels = Array.from(section.querySelectorAll('[data-pairing-outlet]'));
  const active = panels.find(panel => Number(panel.dataset.pairingOutlet) === Number(outletId));
  panels.forEach(panel => panel.hidden = panel !== active);
  const prompt = document.getElementById('fx-pairings-location-prompt');
  if(prompt) prompt.hidden = !!active;
  const hasItems = active && active.querySelector('[data-pairing-item]');
  // No arbitrary same-category fallback when this kitchen has no complements.
  section.hidden = !!active && !hasItems;
}

document.addEventListener('DOMContentLoaded', function(){
  const heatGroup = document.querySelector('.fx-spice-levels');
  if(heatGroup) heatGroup.addEventListener('keydown', function(event){
    if(!['ArrowRight','ArrowDown','ArrowLeft','ArrowUp'].includes(event.key)) return;
    event.preventDefault();
    const choices=Array.from(heatGroup.querySelectorAll('[role="radio"]'));
    const direction=['ArrowRight','ArrowDown'].includes(event.key)?1:-1;
    const index=(choices.indexOf(document.activeElement)+direction+choices.length)%choices.length;
    selectSpice(choices[index]);choices[index].focus();
  });
  const saved = Number(localStorage.getItem('sf_outlet_' + STORE_ID) || 0);
  const option = saved ? document.querySelector('.fx-product-location-option[data-outlet-id="' + saved + '"]') : null;
  if(option) selectFxProductOutlet(option, saved);
  else updateFxPairings(<?= count($fxProductOutlets) === 1 ? (int)$fxProductOutlets[0]->id : 0; ?>);
});
window.addEventListener('storage', function(event){
  if(event.key !== 'sf_outlet_' + STORE_ID) return;
  const outletId = Number(event.newValue || 0);
  const option = document.querySelector('.fx-product-location-option[data-outlet-id="' + outletId + '"]');
  if(option) selectFxProductOutlet(option, outletId);
  else updateFxPairings(0);
});
</script>
