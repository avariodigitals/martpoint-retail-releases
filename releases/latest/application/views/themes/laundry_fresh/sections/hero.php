<?php
$slug = $settings->store_slug ?? '';
$headline = $settings->store_headline ?? ($store->store_name ?? 'Laundry & Dry Cleaning');
$subheadline = $settings->store_subheadline ?? 'Professional laundry and dry-cleaning services, tailored to your needs.';
$banners = $hero_banners ?? [];
$banner = $banners[0] ?? null;
$bgImage = $banner && !empty($banner->desktop_image) ? base_url($banner->desktop_image) : null;
?>
<section class="laundry-hero">
  <div class="laundry-hero-media">
    <?php if($bgImage): ?>
      <img src="<?= $bgImage; ?>" alt="<?= htmlspecialchars($headline); ?>">
    <?php else: ?>
      <div class="laundry-hero-placeholder" style="background:linear-gradient(135deg, var(--mp-primary), var(--mp-secondary));"></div>
    <?php endif; ?>
    <div class="laundry-hero-overlay"></div>
  </div>
  <div class="laundry-hero-content">
    <h1 class="laundry-hero-title"><?= htmlspecialchars($headline); ?></h1>
    <p class="laundry-hero-subtitle"><?= htmlspecialchars($subheadline); ?></p>
    <div class="laundry-hero-actions">
      <a href="<?= base_url('store/' . $slug . '/services'); ?>" class="laundry-hero-btn primary">Schedule a Pickup</a>
      <a href="<?= base_url('store/' . $slug . '/services'); ?>" class="laundry-hero-btn secondary">View Services</a>
    </div>
  </div>
</section>
