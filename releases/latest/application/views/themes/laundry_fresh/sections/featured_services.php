<?php
$slug = $settings->store_slug ?? '';
$fserv = $featured_services ?? [];
?>
<?php if(!empty($fserv)): ?>
<section class="mp-section laundry-services">
  <div class="mp-section-title">
    Popular Services
    <a href="<?= base_url('store/' . $slug . '/services'); ?>">View all services &rarr;</a>
  </div>
  <div class="laundry-service-grid">
    <?php foreach($fserv as $s):
      $price = $s->effective_price ?? ($s->price ?? 0);
      $initial = strtoupper(substr($s->service_name, 0, 1));
      $hasPrice = $price > 0;
      $imagePath = $s->service_image ?? '';
      $imageUrl = $imagePath && file_exists($imagePath) ? base_url($imagePath) : '';
    ?>
    <div class="laundry-service-card">
      <a href="<?= base_url('store/' . $slug . '/service/' . $s->id); ?>" class="laundry-service-img-wrap">
        <?php if($imageUrl): ?>
          <img src="<?= $imageUrl; ?>" alt="<?= htmlspecialchars($s->service_name); ?>" loading="lazy">
        <?php else: ?>
          <div class="laundry-service-placeholder"><?= $initial; ?></div>
        <?php endif; ?>
      </a>
      <div class="laundry-service-body">
        <div class="laundry-service-name"><?= htmlspecialchars($s->service_name); ?></div>
        <div class="laundry-service-meta">
          <?php if(!empty($s->service_duration)): ?>
            <span><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> <?= htmlspecialchars($s->service_duration); ?></span>
          <?php endif; ?>
          <?php if(!empty($s->description)): ?>
            <span><?= htmlspecialchars(substr(strip_tags($s->description), 0, 45)); ?><?= strlen(strip_tags($s->description)) > 45 ? '...' : ''; ?></span>
          <?php endif; ?>
        </div>
        <div class="laundry-service-price">
          <?php if($hasPrice): ?>
            <?= sf_currency($price, $store_currency ?? null); ?>
          <?php else: ?>
            View Pricing
          <?php endif; ?>
        </div>
        <?php if($hasPrice): ?>
          <button type="button" class="laundry-service-btn" onclick="addToCart(<?= (int)$s->id; ?>, 'service', '<?= addslashes($s->service_name); ?>', <?= (float)$price; ?>, '<?= addslashes($imageUrl); ?>', 1)" aria-label="Add <?= addslashes($s->service_name); ?> to order">Add to Order</button>
        <?php else: ?>
          <a href="<?= base_url('store/' . $slug . '/service/' . $s->id); ?>" class="laundry-service-btn" aria-label="View pricing for <?= htmlspecialchars($s->service_name); ?>">View Pricing</a>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
