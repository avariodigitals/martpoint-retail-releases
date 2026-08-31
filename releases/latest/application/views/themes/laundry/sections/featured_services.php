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
      $price = $s->effective_price ?? $s->price;
      $initial = strtoupper(substr($s->service_name, 0, 1));
    ?>
    <div class="laundry-service-card">
      <a href="<?= base_url('store/' . $slug . '/service/' . $s->id); ?>" class="laundry-service-img-wrap">
        <?php if($s->service_image && file_exists($s->service_image)): ?>
          <img src="<?= base_url($s->service_image); ?>" alt="<?= htmlspecialchars($s->service_name); ?>" loading="lazy">
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
        </div>
        <div class="laundry-service-price"><?= sf_currency($price, $store_currency ?? null); ?></div>
        <a href="<?= base_url('store/' . $slug . '/service/' . $s->id); ?>" class="laundry-service-btn">Book Now</a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
