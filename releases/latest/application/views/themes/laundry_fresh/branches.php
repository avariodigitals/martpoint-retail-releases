<?php
$slug = $settings->store_slug ?? '';
?>
<section class="mp-section laundry-branches-page">
  <div class="laundry-branches-header">
    <h1 class="laundry-branches-title">Our Branches</h1>
    <p class="laundry-branches-subtitle">Find the location that works for you.</p>
  </div>

  <?php if(!empty($branches)): ?>
    <div class="laundry-branches-grid">
      <?php foreach($branches as $b): ?>
      <div class="laundry-branch-card">
        <div class="laundry-branch-name"><?= htmlspecialchars($b->warehouse_name); ?></div>
        <?php if(!empty($b->mobile)): ?>
          <div class="laundry-branch-detail">
            <span>Phone</span>
            <a href="tel:<?= preg_replace('/[^0-9+]/', '', $b->mobile); ?>"><?= htmlspecialchars($b->mobile); ?></a>
          </div>
        <?php endif; ?>
        <?php if(!empty($b->email)): ?>
          <div class="laundry-branch-detail">
            <span>Email</span>
            <a href="mailto:<?= htmlspecialchars($b->email); ?>"><?= htmlspecialchars($b->email); ?></a>
          </div>
        <?php endif; ?>
        <a href="<?= base_url('store/' . $slug . '/services'); ?>" class="laundry-service-btn">Book a Service</a>
      </div>
      <?php endforeach; ?>
    </div>
  <?php else: ?>
    <div class="laundry-empty-state">
      <p>No branch information is available at the moment.</p>
      <a href="<?= base_url('store/' . $slug); ?>" class="laundry-hero-btn primary">Back to home</a>
    </div>
  <?php endif; ?>
</section>
