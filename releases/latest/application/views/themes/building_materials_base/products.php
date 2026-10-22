<?php
  $bmProductTotal = (int)($total ?? count($products ?? []));
  $bmPage = max(1, (int)($page ?? 1));
  $bmTotalPages = max(1, (int)ceil($bmProductTotal / max(1, (int)($limit ?? 24))));
  $bmSearch = trim((string)($search ?? ''));
  $bmCategoryId = (int)($category_id ?? 0);
  $bmPaginate = function($targetPage) use ($bmSearch, $bmCategoryId, $bm_products_url){
      $query = [];
      if($bmCategoryId) $query['category'] = $bmCategoryId;
      if($bmSearch !== '') $query['search'] = $bmSearch;
      if($targetPage > 1) $query['page'] = $targetPage;
      return $bm_products_url . ($query ? '?' . http_build_query($query) : '');
  };
?>
<section class="page-heading">
  <p class="eyebrow"><?= htmlspecialchars($bm_values['systems_eyebrow'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
  <?= mp_building_heading($bm_values['systems_title'] ?? '', 'h1'); ?>
  <p><?= nl2br(htmlspecialchars($bm_values['systems_intro'] ?? '', ENT_QUOTES, 'UTF-8')); ?></p>
</section>

<section class="section pale">
  <div class="filterbar" role="group" aria-label="Filter systems by category">
    <a href="<?= $bm_products_url; ?>" class="<?= $bmCategoryId === 0 ? 'active' : ''; ?>"<?= $bmCategoryId === 0 ? ' aria-current="page"' : ''; ?>>All systems</a>
    <?php foreach(($categories ?? []) as $bmCategory): ?>
    <a href="<?= $bm_products_url . '?category=' . (int)$bmCategory->id; ?>" class="<?= $bmCategoryId === (int)$bmCategory->id ? 'active' : ''; ?>"<?= $bmCategoryId === (int)$bmCategory->id ? ' aria-current="page"' : ''; ?>><?= htmlspecialchars($bmCategory->category_name ?? '', ENT_QUOTES, 'UTF-8'); ?></a>
    <?php endforeach; ?>
  </div>

  <form class="search-form" method="get" action="<?= $bm_products_url; ?>" role="search">
    <?php if($bmCategoryId): ?><input type="hidden" name="category" value="<?= $bmCategoryId; ?>"><?php endif; ?>
    <label for="bm-product-search">Search the catalogue</label>
    <div class="search-controls">
      <input id="bm-product-search" type="search" name="search" value="<?= htmlspecialchars($bmSearch, ENT_QUOTES, 'UTF-8'); ?>" placeholder="System name or category" maxlength="120">
      <button class="button" type="submit">Search<span aria-hidden="true">↗</span></button>
      <?php if($bmSearch !== ''): ?><a class="search-clear" href="<?= $bm_products_url . ($bmCategoryId ? '?category=' . $bmCategoryId : ''); ?>">Clear search</a><?php endif; ?>
    </div>
  </form>

  <div class="results-line">
    <span><?= $bmProductTotal; ?> <?= $bmProductTotal === 1 ? 'system' : 'systems'; ?><?= $bmSearch !== '' ? ' matching “' . htmlspecialchars($bmSearch, ENT_QUOTES, 'UTF-8') . '”' : ''; ?></span>
    <?php if($bmTotalPages > 1): ?><span>Page <?= $bmPage; ?> of <?= $bmTotalPages; ?></span><?php endif; ?>
  </div>

  <?php if(!empty($products)): ?>
  <div class="card-grid">
    <?php foreach($products as $bmIndex => $bmProduct): ?>
      <?php
        $card_product = $bmProduct;
        $card_index = ((max(1, (int)($page ?? 1)) - 1) * max(1, (int)($limit ?? 24))) + $bmIndex;
        $card_total = max(1, $bmProductTotal);
        $card_eager = $bmIndex < 3;
        $this->load->view('themes/building_materials_base/_product_card', get_defined_vars());
      ?>
    <?php endforeach; ?>
  </div>

  <?php if($bmTotalPages > 1): ?>
  <nav class="pagination" aria-label="Catalogue pages">
    <?php if($bmPage > 1): ?><a href="<?= $bmPaginate($bmPage - 1); ?>" rel="prev" aria-label="Previous page">←</a><?php endif; ?>
    <?php for($bmPager = max(1, $bmPage - 2); $bmPager <= min($bmTotalPages, $bmPage + 2); $bmPager++): ?>
      <?php if($bmPager === $bmPage): ?>
        <span aria-current="page"><?= $bmPager; ?></span>
      <?php else: ?>
        <a href="<?= $bmPaginate($bmPager); ?>"><?= $bmPager; ?></a>
      <?php endif; ?>
    <?php endfor; ?>
    <?php if($bmPage < $bmTotalPages): ?><a href="<?= $bmPaginate($bmPage + 1); ?>" rel="next" aria-label="Next page">→</a><?php endif; ?>
  </nav>
  <?php endif; ?>

  <?php else: ?>
  <div class="notice-panel notice-panel-inline empty-panel">
    <div>
      <p class="eyebrow">NO MATCHES</p>
      <h2>Nothing in the catalogue matches that search.</h2>
      <p><?= $bmSearch !== '' ? 'Try a broader term, or clear the search to see the full range.' : 'The catalogue is being updated. Speak with the team about the system you need.'; ?></p>
    </div>
    <?php if($bmSearch !== '' || $bmCategoryId): ?>
    <a class="button" href="<?= $bm_products_url; ?>">View the full catalogue<span aria-hidden="true">↗</span></a>
    <?php else: ?>
    <a class="button" href="<?= $bm_contact_url; ?>">Discuss a requirement<span aria-hidden="true">↗</span></a>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</section>

<section class="section">
  <div class="notice-panel">
    <div>
      <p class="eyebrow">SPECIFICATION SUPPORT</p>
      <h2>Not sure what the project requires?</h2>
      <p>Share the application, dimensions and any drawings. The team will help organise the brief for a technical quotation.</p>
    </div>
    <a class="button" href="<?= $bm_contact_url; ?>">Start a specification<span aria-hidden="true">↗</span></a>
  </div>
</section>
