<?php
$slug = $settings->store_slug ?? '';
$statusOrder = ['pending','paid','processing','ready','completed'];
$currentStep = array_search(($order->order_status ?? 'pending'), $statusOrder);
if($currentStep === false) $currentStep = 0;
?>
<section class="mp-section laundry-track-page">
  <div class="laundry-track-card">
    <h1 class="laundry-track-title">Track your order</h1>
    <p class="laundry-track-subtitle">Enter your order number and the phone number used when placing the order.</p>

    <form method="post" action="<?= base_url('store/' . $slug . '/track'); ?>" class="laundry-track-form">
      <input type="hidden" name="<?= $csrf_name; ?>" value="<?= $csrf_hash; ?>">
      <div class="laundry-track-fields">
        <div class="laundry-quick-field">
          <label for="track-order-code">Order number</label>
          <input type="text" name="order_code" id="track-order-code" value="<?= htmlspecialchars($this->input->post('order_code') ?? ''); ?>" placeholder="e.g. WEB-20260101-0001" required>
        </div>
        <div class="laundry-quick-field">
          <label for="track-phone">Phone number</label>
          <input type="tel" name="customer_phone" id="track-phone" value="<?= htmlspecialchars($this->input->post('customer_phone') ?? ''); ?>" placeholder="Phone number used for the order" required>
        </div>
      </div>
      <div class="laundry-track-actions">
        <button type="submit" class="laundry-hero-btn primary">Track Order</button>
      </div>
      <?php if(!empty($error)): ?>
        <div class="laundry-track-error" role="alert"><?= htmlspecialchars($error); ?></div>
      <?php endif; ?>
    </form>

    <?php if($order): ?>
      <div class="laundry-track-result">
        <div class="laundry-track-summary">
          <div>
            <span class="laundry-track-label">Order</span>
            <strong><?= htmlspecialchars($order->order_code); ?></strong>
          </div>
          <div>
            <span class="laundry-track-label">Placed</span>
            <strong><?= date('M j, Y', strtotime($order->created_at)); ?></strong>
          </div>
          <div>
            <span class="laundry-track-label">Total</span>
            <strong><?= sf_currency($order->grand_total ?? 0, $store_currency ?? null); ?></strong>
          </div>
        </div>

        <?php if($order->order_status === 'cancelled'): ?>
          <div class="laundry-track-cancelled">Order cancelled</div>
        <?php else: ?>
          <div class="laundry-track-timeline">
            <?php foreach($statusOrder as $i => $status):
              $info = $status_map[$status] ?? ['label' => ucfirst($status), 'desc' => ''];
              $active = $i <= $currentStep;
              $current = $i === $currentStep;
            ?>
            <div class="laundry-track-step <?= $active ? 'active' : ''; ?> <?= $current ? 'current' : ''; ?>">
              <div class="laundry-track-dot"></div>
              <div class="laundry-track-info">
                <div class="laundry-track-status"><?= htmlspecialchars($info['label']); ?></div>
                <div class="laundry-track-desc"><?= htmlspecialchars($info['desc']); ?></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if(!empty($items)): ?>
          <h2 class="laundry-track-items-title">Items</h2>
          <div class="laundry-track-items">
            <?php foreach($items as $item): ?>
              <div class="laundry-track-item">
                <span class="laundry-track-item-name"><?= htmlspecialchars($item->item_name); ?></span>
                <span class="laundry-track-item-qty">x<?= (int)$item->qty; ?></span>
                <span class="laundry-track-item-price"><?= sf_currency($item->unit_price ?? 0, $store_currency ?? null); ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</section>
