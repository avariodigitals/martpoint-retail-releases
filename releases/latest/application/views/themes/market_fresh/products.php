<?php
/**
 * Market Fresh — catalogue.
 *
 * One screen, one contract: the search box, the category chips, the sort control
 * and the pager all drive the same GET parameters the controller already reads,
 * so nothing here invents state the backend does not know about. Product cards
 * come from the shared renderer, so a card means the same thing on every screen.
 */
$this->load->view('themes/market_fresh/_helpers');

$slug = (string)($settings->store_slug ?? '');
$cur = $store_currency ?? null;
$storeName = trim((string)($store->store_name ?? '')) ?: 'Our store';

$waNumber = preg_replace('/[^0-9]/', '', (string)($settings->whatsapp_number ?? ''));
$categoryId = (int)($category_id ?? 0);
$search = trim((string)($search ?? ''));
$sort = (string)($sort ?? '');
$page = max(1, (int)($page ?? 1));
$limit = max(1, (int)($limit ?? 24));
$total = max(0, (int)($total ?? 0));
$totalPages = max(1, (int)($total_pages ?? 1));
$rows = (isset($products) && is_array($products)) ? $products : [];
$cats = (isset($categories) && is_array($categories)) ? $categories : [];

$activeCatName = '';
foreach ($cats as $cat) {
    if ((int)$cat->id === $categoryId) { $activeCatName = trim((string)$cat->category_name); break; }
}

$ctx = [
    'slug' => $slug,
    'cur' => $cur,
    'brandLabel' => $storeName,
    'showPrices' => !empty($show_prices),
    'showStock' => (int)($settings->show_sale_stock_labels ?? 1) === 1,
    'waOn' => $waNumber !== '',
    'cartOn' => !empty($cart_enabled),
    'backorder' => !empty($settings->allow_backorder),
];

/** Every control on this screen links through here, so no filter is ever dropped. */
$mfUrl = function($overrides = []) use ($slug, $categoryId, $search, $sort){
    $keep = ['category' => $categoryId, 'search' => $search, 'sort' => $sort, 'page' => null];
    foreach ($overrides as $key => $value) {
        if (array_key_exists($key, $keep)) $keep[$key] = $value;
    }
    $qs = [];
    foreach ($keep as $key => $value) {
        if ($value !== null && $value !== '' && $value !== 0) $qs[$key] = $value;
    }
    $url = base_url('store/' . $slug . '/products');
    return $qs ? $url . '?' . http_build_query($qs) : $url;
};

$mfSorts = [
    '' => 'Recommended',
    'newest' => 'Newest first',
    'price_asc' => 'Price: low to high',
    'price_desc' => 'Price: high to low',
    'name_asc' => 'Name: A to Z',
    'name_desc' => 'Name: Z to A',
];
if (!array_key_exists($sort, $mfSorts)) $sort = '';
$mfSortLabel = $mfSorts[$sort];

$from = $total === 0 ? 0 : (($page - 1) * $limit) + 1;
$to = min($total, $page * $limit);

$pageTitle = $search !== '' ? 'Search results' : ($activeCatName !== '' ? $activeCatName : 'All products');
?>

<div class="mf-wrap">
  <nav class="mf-crumbs" aria-label="Breadcrumb">
    <a href="<?= mf_e(base_url('store/' . $slug)); ?>">Home</a>
    <span class="sep" aria-hidden="true">/</span>
    <a href="<?= mf_e($mfUrl(['category' => null, 'search' => null, 'page' => null])); ?>">Products</a>
    <?php if ($activeCatName !== '' || $search !== ''): ?>
    <span class="sep" aria-hidden="true">/</span>
    <span aria-current="page"><?= mf_e($activeCatName !== '' ? $activeCatName : '“' . $search . '”'); ?></span>
    <?php endif; ?>
  </nav>

  <header class="mf-sec" style="padding-bottom:0;">
    <p class="mf-kicker">Catalogue</p>
    <h1 class="mf-title"><?= mf_e($pageTitle); ?></h1>
    <p class="mf-note" role="status">
      <?php if ($total === 0): ?>
        No matches yet
      <?php else: ?>
        Showing <?= $from; ?>&ndash;<?= $to; ?> of <?= $total; ?> <?= $total === 1 ? 'item' : 'items'; ?>
      <?php endif; ?>
    </p>
  </header>

  <div class="mf-toolbar">
    <form class="mf-toolbar-row" action="<?= mf_e(base_url('store/' . $slug . '/products')); ?>" method="get" role="search">
      <?php if ($categoryId): ?>
      <input type="hidden" name="category" value="<?= $categoryId; ?>">
      <?php endif; ?>
      <?php if ($sort !== ''): ?>
      <input type="hidden" name="sort" value="<?= mf_e($sort); ?>">
      <?php endif; ?>
      <div class="mf-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><line x1="20" y1="20" x2="16.5" y2="16.5"/></svg>
        <label class="mf-sr-only" for="mf-catalogue-search">Search products</label>
        <input id="mf-catalogue-search" type="search" name="search" value="<?= mf_e($search); ?>" placeholder="Search this store" autocomplete="off">
        <?php if ($search !== ''): ?>
        <a class="mf-search-clear" href="<?= mf_e($mfUrl(['search' => null, 'page' => null])); ?>" aria-label="Clear search">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
        </a>
        <?php endif; ?>
      </div>

      <div class="mf-sort">
        <span class="mf-label" id="mf-sort-label">Sort by</span>
        <div class="mf-select" data-mf-select>
          <button type="button" class="mf-select-btn" aria-haspopup="listbox" aria-expanded="false" aria-labelledby="mf-sort-label">
            <span data-mf-select-value><?= mf_e($mfSortLabel); ?></span>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="6 9 12 15 18 9"/></svg>
          </button>
          <div class="mf-select-list" role="listbox" aria-labelledby="mf-sort-label" tabindex="-1">
            <?php foreach ($mfSorts as $value => $label): ?>
            <a class="mf-select-opt" role="option" aria-selected="<?= $value === $sort ? 'true' : 'false'; ?>"
               href="<?= mf_e($mfUrl(['sort' => $value === '' ? null : $value, 'page' => null])); ?>">
              <span><?= mf_e($label); ?></span>
              <?php if ($value === $sort): ?>
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="20 6 9 17 4 12"/></svg>
              <?php endif; ?>
            </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    </form>

    <?php if (count($cats) > 1): ?>
    <nav aria-label="Product categories">
      <div class="mf-chips">
        <a class="mf-chip<?= $categoryId === 0 ? ' is-active' : ''; ?>" href="<?= mf_e($mfUrl(['category' => null, 'page' => null])); ?>">All</a>
        <?php foreach (array_slice($cats, 0, 40) as $cat):
            $catCount = (int)($cat->item_count ?? 0);
        ?>
        <a class="mf-chip<?= $categoryId === (int)$cat->id ? ' is-active' : ''; ?>" href="<?= mf_e($mfUrl(['category' => (int)$cat->id, 'page' => null])); ?>">
          <?= mf_e($cat->category_name); ?><?php if ($catCount > 0): ?> <span class="n"><?= $catCount; ?></span><?php endif; ?>
        </a>
        <?php endforeach; ?>
      </div>
    </nav>
    <?php endif; ?>
  </div>

  <?php if ($rows): ?>
  <section class="mf-sec" style="padding-top:0;">
    <?php mf_card_grid($rows, $ctx, 'product', $limit); ?>

    <?php if ($totalPages > 1):
        // Windowed pager: first, last and the pages around the current one.
        $wanted = [1, $totalPages];
        for ($i = $page - 2; $i <= $page + 2; $i++) {
            if ($i >= 1 && $i <= $totalPages) $wanted[] = $i;
        }
        $wanted = array_values(array_unique($wanted));
        sort($wanted);
    ?>
    <nav class="mf-pager" aria-label="Pagination">
      <a class="mf-page-btn" href="<?= mf_e($mfUrl(['page' => max(1, $page - 1)])); ?>"<?= $page <= 1 ? ' aria-disabled="true" tabindex="-1"' : ' rel="prev"'; ?>>
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
        <span class="mf-sr-only">Previous page</span>
      </a>

      <?php $prevShown = 0; foreach ($wanted as $i): ?>
        <?php if ($prevShown && $i - $prevShown > 1): ?><span class="mf-page-gap" aria-hidden="true">&hellip;</span><?php endif; ?>
        <?php $prevShown = $i; ?>
        <a class="mf-page-btn" href="<?= mf_e($mfUrl(['page' => $i])); ?>"<?= $i === $page ? ' aria-current="page"' : ''; ?>><?= $i; ?></a>
      <?php endforeach; ?>

      <a class="mf-page-btn" href="<?= mf_e($mfUrl(['page' => min($totalPages, $page + 1)])); ?>"<?= $page >= $totalPages ? ' aria-disabled="true" tabindex="-1"' : ' rel="next"'; ?>>
        <span class="mf-sr-only">Next page</span>
        <svg viewBox="0 0 24 24" width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>
      </a>
    </nav>
    <?php endif; ?>
  </section>

  <?php else: ?>
  <section class="mf-sec" style="padding-top:0;">
    <div class="mf-empty">
      <span class="mf-empty-ico" aria-hidden="true">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><line x1="20" y1="20" x2="16.5" y2="16.5"/></svg>
      </span>
      <h2 class="mf-empty-title">Nothing matches that yet</h2>
      <p class="mf-empty-text">
        <?= $search !== ''
            ? 'We could not find anything for “' . mf_e($search) . '”. Try a shorter word, or browse the whole catalogue.'
            : 'This category has no published items right now. Browse the whole catalogue instead.'; ?>
      </p>
      <a class="mf-btn mf-btn--primary" href="<?= mf_e($mfUrl(['category' => null, 'search' => null, 'page' => null])); ?>">Browse all products</a>
      <?php if ($waNumber !== ''): ?>
      <button type="button" class="mf-btn mf-btn--wa" data-mf-wa="general">Ask us on WhatsApp</button>
      <?php endif; ?>
    </div>
  </section>
  <?php endif; ?>
</div>

<script>
(function(){
  /* Custom sort list — the platform rule is no native <select> popping outside
     the app container, so the list opens inline and closes on Escape or a tap
     anywhere else. Options are real links, so they work without JS too. */
  document.querySelectorAll('[data-mf-select]').forEach(function(sel){
    var btn = sel.querySelector('.mf-select-btn');
    if(!btn) return;
    function close(){ sel.classList.remove('open'); btn.setAttribute('aria-expanded', 'false'); }
    btn.addEventListener('click', function(e){
      e.preventDefault();
      var open = sel.classList.toggle('open');
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
    sel.addEventListener('keydown', function(e){ if(e.key === 'Escape') close(); });
    document.addEventListener('click', function(e){ if(!sel.contains(e.target)) close(); });
  });

  var waBtns = document.querySelectorAll('[data-mf-wa]');
  Array.prototype.forEach.call(waBtns, function(btn){
    btn.addEventListener('click', function(){
      if(!window.mfWhatsApp) return;
      if(btn.getAttribute('data-mf-wa') === 'cart') mfWhatsApp.openCart();
      else mfWhatsApp.openGeneral();
    });
  });
})();
</script>
