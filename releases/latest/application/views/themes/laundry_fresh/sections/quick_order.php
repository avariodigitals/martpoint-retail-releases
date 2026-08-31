<?php
$slug = $settings->store_slug ?? '';
$branches = $this->theme_engine->branches();
$serviceCats = $this->theme_engine->serviceCategories();
$online = $settings->allow_services ?? true;
?>
<section id="quick-order" class="mp-section laundry-quick-order">
  <div class="laundry-quick-order-card">
    <div class="laundry-quick-order-head">
      <h2 class="laundry-quick-order-title">Schedule a service</h2>
      <p class="laundry-quick-order-subtitle">Choose your preferred branch, service and time.</p>
    </div>

    <?php if(!$online): ?>
      <div class="laundry-empty-state">
        <p>Online ordering is not currently enabled.</p>
        <?php if(!empty($settings->store_phone)): ?>
          <a href="tel:<?= preg_replace('/[^0-9+]/', '', $settings->store_phone); ?>" class="laundry-hero-btn primary">Call <?= htmlspecialchars($settings->store_phone); ?></a>
        <?php endif; ?>
      </div>
    <?php else: ?>
      <form method="get" action="<?= base_url('store/' . $slug . '/services'); ?>" class="laundry-quick-order-form" id="quick-order-form" novalidate>
        <div class="laundry-quick-order-grid">

          <div class="laundry-quick-field">
            <label for="qo-order-type">Order type</label>
            <select name="order_type" id="qo-order-type">
              <option value="">Choose...</option>
              <option value="pickup">Pickup</option>
              <option value="dropoff">Drop-off</option>
            </select>
          </div>

          <div class="laundry-quick-field">
            <label for="qo-branch">Branch</label>
            <select name="branch" id="qo-branch" required>
              <option value="">Choose a branch...</option>
              <?php foreach($branches as $b): ?>
                <option value="<?= (int)$b->id; ?>"><?= htmlspecialchars($b->warehouse_name); ?></option>
              <?php endforeach; ?>
            </select>
            <?php if(empty($branches)): ?>
              <small class="laundry-quick-hint">No branches are available at the moment.</small>
            <?php endif; ?>
          </div>

          <div class="laundry-quick-field">
            <label for="qo-service-type">Service type</label>
            <select name="category" id="qo-service-type">
              <option value="">All services</option>
              <?php foreach($serviceCats as $c): ?>
                <option value="<?= (int)$c->id; ?>"><?= htmlspecialchars($c->category_name); ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="laundry-quick-field">
            <label for="qo-date">Preferred date</label>
            <input type="date" name="date" id="qo-date" min="<?= date('Y-m-d'); ?>">
          </div>

          <div class="laundry-quick-field">
            <label for="qo-time">Preferred time slot</label>
            <select name="time" id="qo-time">
              <option value="">Choose a time...</option>
              <option value="08:00-10:00">08:00 - 10:00</option>
              <option value="10:00-12:00">10:00 - 12:00</option>
              <option value="12:00-14:00">12:00 - 14:00</option>
              <option value="14:00-16:00">14:00 - 16:00</option>
              <option value="16:00-18:00">16:00 - 18:00</option>
            </select>
          </div>

          <div class="laundry-quick-field laundry-quick-full">
            <label for="qo-address">Address / collection notes</label>
            <input type="text" name="address" id="qo-address" placeholder="Optional address or note for the branch">
          </div>

        </div>

        <div class="laundry-quick-order-actions">
          <button type="submit" class="laundry-hero-btn primary">Continue Order</button>
        </div>
      </form>

      <div class="laundry-sticky-cta" aria-label="Schedule a pickup" data-target="#quick-order">
        <a href="#quick-order" class="laundry-hero-btn primary">Schedule a Pickup</a>
      </div>
    <?php endif; ?>
  </div>
</section>
