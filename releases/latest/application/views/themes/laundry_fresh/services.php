<?php
$slug = $settings->store_slug ?? '';
?>
<section class="mp-section laundry-services-page">
  <div class="laundry-services-header">
    <h1 class="laundry-services-title">Our Services</h1>
    <p class="laundry-services-subtitle">Browse our full laundry and dry-cleaning menu.</p>
  </div>

  <div class="laundry-services-toolbar">
    <form method="get" action="<?= base_url('store/' . $slug . '/services'); ?>" class="laundry-services-search">
      <input type="text" name="search" value="<?= htmlspecialchars($search ?? ''); ?>" placeholder="Search services..." aria-label="Search services">
      <button type="submit" class="laundry-hero-btn primary" aria-label="Search">Search</button>
    </form>
    <?php if(!empty($service_categories)): ?>
    <div class="laundry-services-cats">
      <a href="<?= base_url('store/' . $slug . '/services'); ?>" class="laundry-cat-pill <?= empty($category_id) ? 'active' : ''; ?>">All</a>
      <?php foreach($service_categories as $c): ?>
        <a href="<?= base_url('store/' . $slug . '/services'); ?>?category=<?= (int)$c->id; ?>" class="laundry-cat-pill <?= ($category_id ?? 0) == $c->id ? 'active' : ''; ?>"><?= htmlspecialchars($c->category_name); ?></a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
  </div>

  <?php if(!empty($services)): ?>
    <div class="laundry-service-grid">
      <?php foreach($services as $s):
        $price = $s->effective_price ?? ($s->price ?? 0);
        $hasPrice = $price > 0;
        $initial = strtoupper(substr($s->item_name, 0, 1));
        $imagePath = $s->item_image ?? ($s->service_image ?? '');
        $imageUrl = $imagePath && file_exists($imagePath) ? base_url($imagePath) : '';
      ?>
      <div class="laundry-service-card">
        <a href="<?= base_url('store/' . $slug . '/service/' . $s->id); ?>" class="laundry-service-img-wrap">
          <?php if($imageUrl): ?>
            <img src="<?= $imageUrl; ?>" alt="<?= htmlspecialchars($s->item_name); ?>" loading="lazy">
          <?php else: ?>
            <div class="laundry-service-placeholder"><?= $initial; ?></div>
          <?php endif; ?>
        </a>
        <div class="laundry-service-body">
          <div class="laundry-service-name"><?= htmlspecialchars($s->item_name); ?></div>
          <div class="laundry-service-meta">
            <?php if(!empty($s->service_duration)): ?>
              <span><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> <?= htmlspecialchars($s->service_duration); ?></span>
            <?php endif; ?>
          </div>
          <div class="laundry-service-price">
            <?php if($hasPrice): ?>
              From <?= sf_currency($price, $store_currency ?? null); ?>
            <?php else: ?>
              View Pricing
            <?php endif; ?>
          </div>
          <?php if($hasPrice): ?>
            <button type="button" class="laundry-service-btn" onclick="addToCart(<?= (int)$s->id; ?>, 'service', '<?= addslashes($s->item_name); ?>', <?= (float)$price; ?>, '<?= addslashes($imageUrl); ?>', 1)" aria-label="Add <?= addslashes($s->item_name); ?> to order">Add to Order</button>
          <?php else: ?>
            <a href="<?= base_url('store/' . $slug . '/service/' . $s->id); ?>" class="laundry-service-btn" aria-label="View pricing for <?= htmlspecialchars($s->item_name); ?>">View Pricing</a>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <?php if(($total_pages ?? 1) > 1): ?>
      <div class="mp-pagination">
        <?php for($p = 1; $p <= $total_pages; $p++):
          $qs = [];
          if($category_id) $qs[] = 'category=' . (int)$category_id;
          if($search) $qs[] = 'search=' . urlencode($search);
          $q = $qs ? '?' . implode('&', $qs) . ($p > 1 ? '&page=' . $p : '') : ($p > 1 ? '?page=' . $p : '');
          $url = base_url('store/' . $slug . '/services') . $q;
          $isCurrent = ($page ?? 1) == $p;
        ?>
          <?php if($isCurrent): ?>
            <span><?= $p; ?></span>
          <?php else: ?>
            <a href="<?= $url; ?>"><?= $p; ?></a>
          <?php endif; ?>
        <?php endfor; ?>
      </div>
    <?php endif; ?>
  <?php else: ?>
    <div class="laundry-empty-state">
      <p>No services found<?= $search ? ' for &ldquo;' . htmlspecialchars($search) . '&rdquo;' : ''; ?>.</p>
      <a href="<?= base_url('store/' . $slug); ?>" class="laundry-hero-btn primary">Back to home</a>
    </div>
  <?php endif; ?>
</section>
