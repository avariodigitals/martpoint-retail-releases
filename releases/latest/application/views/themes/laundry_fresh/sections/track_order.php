<?php
$slug = $settings->store_slug ?? '';
?>
<section class="mp-section laundry-track-cta" id="track-order">
  <div class="laundry-track-cta-card">
    <div class="laundry-track-cta-text">
      <h2 class="laundry-track-cta-title">Track your order</h2>
      <p class="laundry-track-cta-subtitle">Enter your order number to see the current status and progress.</p>
    </div>
    <form method="post" action="<?= base_url('store/' . $slug . '/track'); ?>" class="laundry-track-cta-form">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
      <div class="laundry-track-cta-field">
        <input type="text" name="order_code" placeholder="Order number" aria-label="Order number" required>
      </div>
      <div class="laundry-track-cta-field">
        <input type="tel" name="customer_phone" placeholder="Phone number" aria-label="Phone number" required>
      </div>
      <button type="submit" class="laundry-hero-btn primary">Track Order</button>
    </form>
  </div>
</section>
