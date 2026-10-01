<?php
// Shared variant picker. Two modes:
//  - select (default when $sf_picker_mode === 'select'): chips call
//    sfPickVariant() which updates window.sfPickedVariant and fires
//    'sf:variant-picked' — the theme's detail JS swaps price/image/stock
//    and targets add-to-cart at the chosen child item.
//  - nav ($sf_picker_mode === 'nav'): chips are plain links to each
//    variant's own product page — needs no theme JS at all.
// Also emits window.SF_PARENT_IDS so addToCart() can block ordering a
// variant parent directly even on pages that wire no picker JS.
// Expects: $product_variants, $product, $settings, $store_currency
$sfAllowBackorder = !empty($settings->allow_backorder);
if(empty($product_variants)) return;
$sfMode = $sf_picker_mode ?? 'nav';
?>
<style>
/* Self-contained so the picker works in any theme detail template, with or
   without the shared layout. Scoped to .sf-variant-picker. */
.sf-variant-picker { margin:0 0 20px; }
.sf-variant-picker-label { font-size:13px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; opacity:.65; margin-bottom:10px; }
.sf-variant-chips { display:flex; flex-wrap:wrap; gap:8px; }
.sf-variant-chip { display:inline-flex; flex-direction:column; gap:2px; padding:10px 16px; border:1.5px solid rgba(15,23,42,.15); border-radius:10px; background:#fff; color:inherit; cursor:pointer; font-family:inherit; transition:border-color .15s, background .15s; text-align:left; text-decoration:none; }
.sf-variant-chip:hover:not(:disabled) { border-color:var(--mp-primary, #059669); }
.sf-variant-chip.active { border-color:var(--mp-primary, #059669); background:rgba(5,150,105,.07); }
.sf-variant-chip.oos { opacity:.45; cursor:not-allowed; }
.sf-variant-chip-name { font-size:14px; font-weight:600; }
.sf-variant-chip-price { font-size:12px; opacity:.65; }
</style>
<div class="sf-variant-picker" role="group" aria-label="Product options">
  <div class="sf-variant-picker-label">Choose an option</div>
  <div class="sf-variant-chips">
    <?php foreach($product_variants as $v):
      $sfOos = ($v->stock <= 0) && !$sfAllowBackorder;
      $sfUrl = base_url('store/' . ($settings->store_slug ?? '') . '/product/' . $v->id);
      if($sfMode === 'select'): ?>
    <button type="button"
      class="sf-variant-chip <?= !empty($v->is_current) ? 'active' : ''; ?><?= $sfOos ? ' oos' : ''; ?>"
      <?= $sfOos ? 'disabled aria-disabled="true"' : ''; ?>
      data-id="<?= (int)$v->id; ?>"
      data-name="<?= htmlspecialchars($v->item_name, ENT_QUOTES); ?>"
      data-price="<?= (float)$v->effective_price; ?>"
      data-image="<?= htmlspecialchars($v->item_image ?? '', ENT_QUOTES); ?>"
      data-stock="<?= (int)$v->stock; ?>"
      onclick="sfPickVariant(this)">
      <span class="sf-variant-chip-name"><?= htmlspecialchars($v->item_name); ?></span>
      <span class="sf-variant-chip-price"><?= $sfOos ? 'Out of stock' : sf_currency($v->effective_price, $store_currency ?? null); ?></span>
    </button>
    <?php else: ?>
    <a href="<?= $sfUrl; ?>" class="sf-variant-chip <?= !empty($v->is_current) ? 'active' : ''; ?><?= $sfOos ? ' oos' : ''; ?>">
      <span class="sf-variant-chip-name"><?= htmlspecialchars($v->item_name); ?></span>
      <span class="sf-variant-chip-price"><?= $sfOos ? 'Out of stock' : sf_currency($v->effective_price, $store_currency ?? null); ?></span>
    </a>
    <?php endif; endforeach; ?>
  </div>
</div>
<?php if(($product->item_group ?? '') === 'Variants'): ?>
<script>window.SF_PARENT_IDS = window.SF_PARENT_IDS || {}; SF_PARENT_IDS[<?= (int)$product->id; ?>] = 1;</script>
<?php endif; ?>
