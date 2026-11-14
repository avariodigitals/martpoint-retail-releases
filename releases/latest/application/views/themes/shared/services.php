<div class="mp-breadcrumb">
  <a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>">Home</a> &rsaquo; Services
</div>

<div class="mp-section">
  <div class="mp-section-title">Our Services</div>

  <?php if(!empty($services)): ?>
    <div class="mp-service-grid">
      <?php foreach($services as $s):
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

    <?php if($total_pages > 1): ?>
    <div class="mp-pagination">
      <?php if($page > 1): ?>
        <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/services'); ?>?page=<?= $page-1; ?><?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>">&laquo;</a>
      <?php endif; ?>
      <?php for($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
        <?php if($i == $page): ?>
          <span style="background:var(--mp-primary); color:#fff; border-color:var(--mp-primary);"><?= $i; ?></span>
        <?php else: ?>
          <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/services'); ?>?page=<?= $i; ?><?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>"><?= $i; ?></a>
        <?php endif; ?>
      <?php endfor; ?>
      <?php if($page < $total_pages): ?>
        <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/services'); ?>?page=<?= $page+1; ?><?= !empty($search) ? '&search=' . urlencode($search) : ''; ?>">&raquo;</a>
      <?php endif; ?>
    </div>
    <?php endif; ?>

  <?php else: ?>
    <div class="mp-empty">
      <div style="margin-bottom:12px; color:#CBD5E1;"><svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg></div>
      <div>No services found.</div>
    </div>
  <?php endif; ?>
</div>
