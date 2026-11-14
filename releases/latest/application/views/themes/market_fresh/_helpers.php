<?php
/**
 * Market Fresh — shared card + fact helpers.
 *
 * Loaded by every screen of this theme so a product card means the same thing
 * on the homepage, the catalogue, the detail page and the related rail. Cards
 * carry their facts in one JSON attribute; the click handling lives in the
 * theme header, so no product string is ever concatenated into inline JS.
 */
if (!function_exists('sf_product_image_exists')) $this->load->helper('storefront_product_image');

if (!function_exists('mf_e')) {
    function mf_e($value){
        return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
    }
}

/** The catalogue price the customer is actually charged, resolved exactly like
 *  Storefront_model::productPriceQuote() so a card never disagrees with the
 *  detail page or the cart. */
if (!function_exists('mf_price')) {
    function mf_price($row){
        $row = (object)$row;
        if (isset($row->effective_price)) return (float)$row->effective_price;
        $online = (float)($row->online_price ?? 0);
        if ($online > 0) return $online;
        $retail = (float)($row->mrp ?? 0);
        if ($retail > 0) return $retail;
        return (float)($row->sales_price ?? $row->price ?? 0);
    }
}

/** A struck-through price only when the platform really recorded a higher one. */
if (!function_exists('mf_was_price')) {
    function mf_was_price($row){
        $row = (object)$row;
        $was = isset($row->original_price) ? (float)$row->original_price : 0;
        $now = mf_price($row);
        return ($was > $now && $was > 0) ? $was : 0;
    }
}

if (!function_exists('mf_image_url')) {
    /** Theme_engine already swapped in the brand-only SVG for missing artwork,
     *  so anything that still fails the existence check gets no <img> at all. */
    function mf_image_url($path, $width = 600){
        $path = trim((string)$path);
        if ($path === '' || !sf_product_image_exists($path)) return '';
        return mp_minified_image_url($path, $width);
    }
}

/** Safe single-attribute JSON payload for the card click handler. */
if (!function_exists('mf_json_attr')) {
    function mf_json_attr($data){
        return mf_e(json_encode($data, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }
}

/**
 * A JavaScript literal for inside a <script> block.
 *
 * The HEX flags mean the output contains no quote, angle bracket or ampersand
 * character at all, so a product name can neither terminate the string nor
 * close the script element. Unlike mf_json_attr() this must NOT be HTML-escaped:
 * entities are not decoded inside a script, so escaping would corrupt the value.
 */
if (!function_exists('mf_js')) {
    function mf_js($value){
        return json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    }
}

/** Facts the theme states about an item, gathered in one place. */
if (!function_exists('mf_card_facts')) {
    function mf_card_facts($row, $kind = 'product'){
        $row = (object)$row;
        $isService = ($kind === 'service');
        $name = trim((string)($row->item_name ?? $row->service_name ?? ''));
        $id = (int)($row->id ?? 0);
        $stock = $isService ? null : (int)($row->stock ?? 0);
        $imagePath = (string)($row->item_image ?? $row->service_image ?? '');
        return [
            'id' => $id,
            'kind' => $isService ? 'service' : 'product',
            'name' => $name,
            'price' => mf_price($row),
            'was' => mf_was_price($row),
            'stock' => $stock,
            'alert_qty' => $isService ? 0 : (int)($row->alert_qty ?? 0),
            'image_path' => $imagePath,
            'image' => mf_image_url($imagePath, 600),
            'category' => trim((string)($row->category_name ?? '')),
            'unit' => trim((string)($row->unit_name ?? '')),
            'description' => trim((string)($row->description ?? '')),
            'is_new' => !$isService && !empty($row->is_new_arrival),
            'variants' => !$isService && (int)($row->variant_count ?? 0) > 0,
            'appointment' => $isService && !empty($row->requires_appointment),
        ];
    }
}

/** Brand-only artwork tile — the platform rule for a missing product image.
 *  Never a product name, a theme name, an icon or a stock photo. */
if (!function_exists('mf_art_tile')) {
    function mf_art_tile($brandLabel){
        $brandLabel = trim((string)$brandLabel) ?: 'Store';
        echo '<span class="mf-art" aria-hidden="true"><span>' . mf_e($brandLabel) . '</span></span>';
    }
}

/**
 * Render one product/service card.
 *
 * $ctx keys: slug, cur, brandLabel, showPrices, showStock, waOn, cartOn,
 *            flag (optional ribbon text).
 */
if (!function_exists('mf_card')) {
    function mf_card($row, $ctx, $kind = 'product'){
        $f = mf_card_facts($row, $kind);
        if ($f['id'] <= 0 || $f['name'] === '') return;

        $slug = (string)($ctx['slug'] ?? '');
        $cur = $ctx['cur'] ?? null;
        $showPrices = !empty($ctx['showPrices']);
        $waOn = !empty($ctx['waOn']);
        $oos = ($f['kind'] === 'product' && $f['stock'] !== null && $f['stock'] <= 0 && empty($ctx['backorder']));
        $low = ($f['kind'] === 'product' && !$oos && $f['alert_qty'] > 0 && $f['stock'] !== null && $f['stock'] <= $f['alert_qty']);

        $url = $f['kind'] === 'service'
            ? base_url('store/' . $slug . '/' . ($f['appointment'] ? 'appointment/' : 'service/') . $f['id'])
            : base_url('store/' . $slug . '/product/' . $f['id']);

        $flag = trim((string)($ctx['flag'] ?? ''));
        if ($flag === '' && $f['is_new']) $flag = 'New';
        if ($flag === '' && $f['was'] > 0 && $showPrices) {
            $pct = (int)round((1 - ($f['price'] / $f['was'])) * 100);
            if ($pct > 0) $flag = $pct . '% off';
        }

        $payload = [
            'id' => $f['id'],
            'type' => $f['kind'],
            'name' => $f['name'],
            'price' => $f['price'],
            'was' => $f['was'],
            'image' => $f['image_path'],
            'desc' => mb_substr($f['description'], 0, 400, 'UTF-8'),
            'stock' => $f['stock'] === null ? 999 : $f['stock'],
            'unit' => $f['unit'],
            'url' => $url,
            'variants' => $f['variants'],
            'oos' => $oos,
        ];

        $stockClass = $oos ? ' mf-p-stock--out' : ($low ? ' mf-p-stock--low' : '');
        $stockText = $oos ? 'Out of stock' : ($low ? 'Low stock' : 'In stock');
        ?>
<article class="mf-p" data-mf-item="<?= mf_json_attr($payload); ?>">
  <?php if ($flag !== ''): ?><span class="mf-p-flag<?= $f['is_new'] && $flag === 'New' ? ' mf-p-flag--fresh' : ''; ?>"><?= mf_e($flag); ?></span><?php endif; ?>
  <a class="mf-p-media" href="<?= mf_e($url); ?>" data-mf-action="view" aria-label="<?= mf_e($f['name']); ?>">
    <?php if ($f['image'] !== ''): ?>
    <img src="<?= mf_e($f['image']); ?>" alt="<?= mf_e($f['name']); ?>" loading="lazy" decoding="async">
    <?php else: ?>
    <?php mf_art_tile($ctx['brandLabel'] ?? ''); ?>
    <?php endif; ?>
  </a>
  <div class="mf-p-body">
    <?php if ($f['category'] !== ''): ?><div class="mf-p-cat"><?= mf_e($f['category']); ?></div><?php endif; ?>
    <a class="mf-p-name" href="<?= mf_e($url); ?>" data-mf-action="view"><?= mf_e($f['name']); ?></a>
    <?php if ($f['unit'] !== ''): ?><div class="mf-p-unit">Sold per <?= mf_e($f['unit']); ?></div><?php endif; ?>
    <div class="mf-p-price">
      <?php if ($showPrices): ?>
        <?= sf_currency($f['price'], $cur); ?>
        <?php if ($f['was'] > 0): ?><span class="was"><?= sf_currency($f['was'], $cur); ?></span><?php endif; ?>
      <?php else: ?>
        Price on request
      <?php endif; ?>
    </div>
    <?php if (!empty($ctx['showStock']) && $f['kind'] === 'product'): ?>
    <div class="mf-p-stock<?= $stockClass; ?>"><?= mf_e($stockText); ?></div>
    <?php endif; ?>
    <div class="mf-p-actions">
      <?php if ($f['kind'] === 'service'): ?>
      <a class="mf-btn mf-btn--deep mf-btn--sm mf-p-add" href="<?= mf_e($url); ?>"><?= $f['appointment'] ? 'Book this service' : 'View service'; ?></a>
      <?php elseif (!empty($ctx['cartOn'])): ?>
      <button type="button" class="mf-btn mf-btn--deep mf-btn--sm mf-p-add" data-mf-action="add"<?= $oos ? ' disabled' : ''; ?>>
        <?= $f['variants'] ? 'Choose options' : ($oos ? 'Out of stock' : 'Add to cart'); ?>
      </button>
      <?php else: ?>
      <a class="mf-btn mf-btn--outline mf-btn--sm mf-p-add" href="<?= mf_e($url); ?>">View details</a>
      <?php endif; ?>
      <?php if ($waOn): ?>
      <button type="button" class="mf-btn mf-btn--wa mf-p-wa" data-mf-action="wa"
        aria-label="<?= $oos ? mf_e('Ask about ' . $f['name'] . ' on WhatsApp') : mf_e('Order ' . $f['name'] . ' on WhatsApp'); ?>">
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.008-.57-.008-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.13 1.558 5.931L.157 24l6.305-1.654a11.882 11.882 0 0 0 5.587 1.396h.004c6.552 0 11.887-5.335 11.89-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>
      </button>
      <?php endif; ?>
    </div>
  </div>
</article>
<?php
    }
}

/** A row of cards, or an honest empty state when the merchant has nothing here. */
if (!function_exists('mf_card_grid')) {
    function mf_card_grid($rows, $ctx, $kind = 'product', $limit = 8){
        $rows = is_array($rows) ? array_slice(array_values($rows), 0, max(1, (int)$limit)) : [];
        $rows = array_filter($rows, function($r){ return is_object($r) && (int)($r->id ?? 0) > 0; });
        if (!$rows) return false;
        echo '<div class="mf-products">';
        foreach ($rows as $row) mf_card($row, $ctx, $kind);
        echo '</div>';
        return true;
    }
}
