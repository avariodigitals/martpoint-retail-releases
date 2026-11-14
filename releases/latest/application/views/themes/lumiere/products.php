<?php
require_once APPPATH . 'views/themes/lumiere/_helpers.php';
$lmSlug = $settings->store_slug ?? '';
$lmHome = base_url('store/' . $lmSlug);
$lmBase = $lmHome . '/products';
$lmQuery = [];
if (!empty($category_id)) $lmQuery['category'] = (int) $category_id;
if (!empty($search)) $lmQuery['search'] = $search;
?>
<main class="lm-inner"><div class="wrap">
  <div class="lm-breadcrumb"><a href="<?= lm_escape($lmHome); ?>">Home</a><span>›</span>Shop Skincare</div>
  <div class="lm-page-intro"><div class="eyebrow">THE LUMIÈRE EDIT</div><h1>Good skin begins here.</h1><p>Thoughtfully selected essentials for a routine that feels like yours.</p></div>
  <form class="lm-search-form" action="<?= lm_escape($lmBase); ?>" method="get"><input type="search" name="search" value="<?= lm_escape($search ?? ''); ?>" placeholder="Search skincare products" aria-label="Search products"><button class="btn" type="submit">Search ↗</button></form>
  <div class="categories"><a class="chip <?= empty($category_id) ? 'active' : ''; ?>" href="<?= lm_escape($lmBase); ?>">Shop All</a><?php foreach ($categories ?? [] as $lmCategory): ?><a class="chip <?= (int) ($category_id ?? 0) === (int) $lmCategory->id ? 'active' : ''; ?>" href="<?= lm_escape($lmBase . '?category=' . (int) $lmCategory->id); ?>"><?= lm_escape($lmCategory->category_name); ?></a><?php endforeach; ?></div>
  <?php if (!empty($products)): ?><div class="products-grid"><?php foreach ($products as $lmProduct) lm_product_card($lmProduct, $lmSlug, $store_currency ?? null, $settings); ?></div>
  <?php else: ?><div class="lm-empty"><h2>No products found.</h2><p>Try another search or browse all skincare.</p><a class="btn" href="<?= lm_escape($lmBase); ?>">Shop All Products ↗</a></div><?php endif; ?>
  <?php if (($total_pages ?? 1) > 1): ?><nav class="lm-pagination" aria-label="Product pages"><?php for ($lmPage = 1; $lmPage <= $total_pages; $lmPage++): $lmQuery['page'] = $lmPage; ?><a class="<?= $lmPage === (int) $page ? 'active' : ''; ?>" href="<?= lm_escape($lmBase . '?' . http_build_query($lmQuery)); ?>"><?= $lmPage; ?></a><?php endfor; ?></nav><?php endif; ?>
</div></main>
