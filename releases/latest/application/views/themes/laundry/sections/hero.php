<?php
$slug = $settings->store_slug ?? '';
$headline = $settings->store_headline ?? ($store->store_name ?? 'Laundry & Dry Cleaning');
$subheadline = $settings->store_subheadline ?? 'Schedule pickup, choose your service, and get fresh clothes delivered.';
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
      <a href="<?= base_url('store/' . $slug . '/services'); ?>" class="laundry-hero-btn primary">Book a Service</a>
      <?php if($settings->allow_whatsapp ?? false): ?>
      <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number); ?>" target="_blank" class="laundry-hero-btn secondary">Order on WhatsApp</a>
      <?php else: ?>
      <a href="<?= base_url('store/' . $slug . '/services'); ?>" class="laundry-hero-btn secondary">View Services</a>
      <?php endif; ?>
    </div>
    <div class="laundry-hero-trust">
      <span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 12l5 5L20 7"/></svg> Free Pickup & Delivery</span>
      <span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg> Same-Day Turnaround</span>
      <span><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg> Eco-Friendly Care</span>
    </div>
  </div>
</section>
