<?php $fserv = $featured_services ?? []; ?>
<?php if(!empty($fserv)): ?>
<div class="mp-section">
  <div class="mp-section-title">
    Our Services
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/services'); ?>">View all services &rarr;</a>
  </div>
  <div class="mp-service-grid">
    <?php foreach($fserv as $s):
      $price = $s->effective_price ?? $s->price;
      $initial = strtoupper(substr($s->service_name, 0, 1));
    ?>
    <div class="mp-service-card-v">
      <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/service/' . $s->id); ?>">
        <div class="mp-service-img-wrap">
          <?php if($s->service_image && file_exists($s->service_image)): ?>
            <img src="<?= base_url($s->service_image); ?>" alt="<?= htmlspecialchars($s->service_name); ?>" loading="lazy">
          <?php else: ?>
            <div class="mp-card-placeholder">
              <svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2"><circle cx="32" cy="28" r="12"/><path d="M16 52c0-8.8 7.2-16 16-16s16 7.2 16 16"/></svg>
              <span><?= $initial; ?></span>
            </div>
          <?php endif; ?>
        </div>
      </a>
      <div class="mp-service-body">
        <div class="mp-service-name"><?= htmlspecialchars($s->service_name); ?></div>
        <div class="mp-service-meta">
          <?php if(!empty($s->service_duration)): ?><span><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> <?= htmlspecialchars($s->service_duration); ?></span><?php endif; ?>
          <span><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg> <?= ucfirst(str_replace('-',' ',$s->location_type)); ?></span>
        </div>
        <div class="mp-service-price"><?= sf_currency($price, $store_currency ?? null); ?></div>
        <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/service/' . $s->id); ?>" class="mp-service-btn">Book Service</a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>
