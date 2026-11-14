<?php
$slug = $settings->store_slug ?? '';
$price = $service->effective_price ?? ($service->price ?? 0);
$hasPrice = $price > 0;
$imagePath = $service->item_image ?? ($service->service_image ?? '');
$imageUrl = $imagePath && file_exists($imagePath) ? base_url($imagePath) : '';
$initial = strtoupper(substr($service->item_name, 0, 1));
?>
<section class="mp-section laundry-service-detail">
  <div class="laundry-detail-grid">
    <div class="laundry-detail-media">
      <?php if($imageUrl): ?>
        <img src="<?= $imageUrl; ?>" alt="<?= htmlspecialchars($service->item_name); ?>">
      <?php else: ?>
        <div class="laundry-detail-placeholder"><?= $initial; ?></div>
      <?php endif; ?>
    </div>
    <div class="laundry-detail-body">
      <a href="<?= base_url('store/' . $slug . '/services'); ?>" class="laundry-detail-back">&larr; All services</a>
      <h1 class="laundry-detail-name"><?= htmlspecialchars($service->item_name); ?></h1>
      <div class="laundry-detail-meta">
        <?php if(!empty($service->service_duration)): ?>
          <span class="laundry-detail-tag"><?= htmlspecialchars($service->service_duration); ?></span>
        <?php endif; ?>
        <?php if(!empty($service->category_name)): ?>
          <span class="laundry-detail-tag"><?= htmlspecialchars($service->category_name); ?></span>
        <?php endif; ?>
      </div>
      <div class="laundry-detail-price">
        <?php if($hasPrice): ?>
          <strong><?= sf_currency($price, $store_currency ?? null); ?></strong>
          <small>Price may vary after inspection.</small>
        <?php else: ?>
          <small>Price available on request.</small>
        <?php endif; ?>
      </div>
      <?php if(!empty($service->description)): ?>
        <div class="laundry-detail-desc"><?= nl2br(htmlspecialchars(strip_tags($service->description))); ?></div>
      <?php endif; ?>

      <?php if($hasPrice): ?>
        <div class="laundry-detail-qty">
          <label for="service-qty">Quantity</label>
          <div class="laundry-detail-qty-row">
            <button type="button" onclick="adjustServiceQty(-1)" aria-label="Decrease quantity">&minus;</button>
            <input type="number" id="service-qty" value="1" min="1" max="99" readonly>
            <button type="button" onclick="adjustServiceQty(1)" aria-label="Increase quantity">+</button>
          </div>
        </div>
        <button type="button" class="laundry-hero-btn primary" id="add-service-btn" onclick="addServiceToCart()" aria-label="Add to order">Add to Order</button>
        <a href="<?= base_url('store/' . $slug . '/cart'); ?>" class="laundry-hero-btn secondary" style="margin-top:12px; display:inline-block;">Continue to Cart</a>
      <?php else: ?>
        <?php if(!empty($settings->store_phone)): ?>
          <a href="tel:<?= preg_replace('/[^0-9+]/', '', $settings->store_phone); ?>" class="laundry-hero-btn primary">Call for Pricing</a>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>

  <?php if(!empty($related_services)): ?>
  <div class="laundry-detail-related">
    <h2 class="laundry-detail-related-title">You may also need</h2>
    <div class="laundry-service-grid">
      <?php foreach($related_services as $s):
        $rPrice = $s->effective_price ?? ($s->price ?? 0);
        $rHasPrice = $rPrice > 0;
        $rImagePath = $s->item_image ?? ($s->service_image ?? '');
        $rImageUrl = $rImagePath && file_exists($rImagePath) ? base_url($rImagePath) : '';
        $rInitial = strtoupper(substr($s->item_name, 0, 1));
      ?>
      <div class="laundry-service-card">
        <a href="<?= base_url('store/' . $slug . '/service/' . $s->id); ?>" class="laundry-service-img-wrap">
          <?php if($rImageUrl): ?>
            <img src="<?= $rImageUrl; ?>" alt="<?= htmlspecialchars($s->item_name); ?>" loading="lazy">
          <?php else: ?>
            <div class="laundry-service-placeholder"><?= $rInitial; ?></div>
          <?php endif; ?>
        </a>
        <div class="laundry-service-body">
          <div class="laundry-service-name"><?= htmlspecialchars($s->item_name); ?></div>
          <div class="laundry-service-price">
            <?php if($rHasPrice): ?>
              From <?= sf_currency($rPrice, $store_currency ?? null); ?>
            <?php else: ?>
              View Pricing
            <?php endif; ?>
          </div>
          <a href="<?= base_url('store/' . $slug . '/service/' . $s->id); ?>" class="laundry-service-btn" aria-label="View <?= htmlspecialchars($s->item_name); ?>">View</a>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
</section>

<script>
  function adjustServiceQty(delta){
    const input = document.getElementById('service-qty');
    let qty = parseInt(input.value, 10) || 1;
    qty = Math.max(1, Math.min(99, qty + delta));
    input.value = qty;
  }
  function addServiceToCart(){
    const qty = parseInt(document.getElementById('service-qty').value, 10) || 1;
    addToCart(<?= (int)$service->id; ?>, 'service', '<?= addslashes($service->item_name); ?>', <?= (float)$price; ?>, '<?= addslashes($imageUrl); ?>', qty);
  }
</script>
