<?php
require_once APPPATH . 'views/themes/lumiere/_helpers.php';
$lmSlug = $settings->store_slug ?? '';
$lmHome = base_url('store/' . $lmSlug);
$lmBase = $lmHome . '/services';
$lmQuery = [];
if (!empty($category_id)) $lmQuery['category'] = (int) $category_id;
if (!empty($search)) $lmQuery['search'] = $search;
?>
<main class="lm-inner lm-services-page"><div class="wrap">
  <div class="lm-breadcrumb"><a href="<?= lm_escape($lmHome); ?>">Home</a><span>›</span>Our Services</div>
  <div class="lm-page-intro"><div class="eyebrow">BEYOND THE EVERYDAY ROUTINE</div><h1>A little time for you.</h1><p>Personalised treatments to restore, refresh and reconnect.</p></div>
  <form class="lm-search-form" action="<?= lm_escape($lmBase); ?>" method="get"><input type="search" name="search" value="<?= lm_escape($search ?? ''); ?>" placeholder="Search treatments" aria-label="Search treatments"><button class="btn" type="submit">Search ↗</button></form>
  <?php if (!empty($service_categories)): ?><div class="categories"><a class="chip <?= empty($category_id) ? 'active' : ''; ?>" href="<?= lm_escape($lmBase); ?>">All Treatments</a><?php foreach ($service_categories as $lmCategory): ?><a class="chip <?= (int) ($category_id ?? 0) === (int) $lmCategory->id ? 'active' : ''; ?>" href="<?= lm_escape($lmBase . '?category=' . (int) $lmCategory->id); ?>"><?= lm_escape($lmCategory->category_name); ?></a><?php endforeach; ?></div><?php endif; ?>
  <?php if (!empty($services)): ?><div class="services-grid"><?php foreach ($services as $lmService) lm_service_card($lmService, $lmSlug, $store_currency ?? null); ?></div>
  <?php else: ?><div class="lm-empty"><h2>No treatments found.</h2><p>Try another search or explore all services.</p><a class="btn" href="<?= lm_escape($lmBase); ?>">Explore Treatments ↗</a></div><?php endif; ?>
  <?php if (($total_pages ?? 1) > 1): ?><nav class="lm-pagination" aria-label="Service pages"><?php for ($lmPage = 1; $lmPage <= $total_pages; $lmPage++): $lmQuery['page'] = $lmPage; ?><a class="<?= $lmPage === (int) $page ? 'active' : ''; ?>" href="<?= lm_escape($lmBase . '?' . http_build_query($lmQuery)); ?>"><?= $lmPage; ?></a><?php endfor; ?></nav><?php endif; ?>
</div></main>
