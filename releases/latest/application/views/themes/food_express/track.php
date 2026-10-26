<?php
$order = $order ?? null;
$orders = $orders ?? [];
?>
<div class="fx-section" style="padding-top:16px; padding-bottom:60px;">
  <div class="fx-section-inner">
    <h1 style="font-size:clamp(24px, 3vw, 30px); font-weight:800; color:var(--fx-ink); margin-bottom:8px;">Track Order</h1>
    <p style="color:var(--fx-muted); margin-bottom:32px;">Enter your order code or email to see real-time status</p>

    <?php if($order): ?>
    <!-- Order Found - Show Tracking -->
    <div style="max-width:600px;">
      <!-- Status Timeline -->
      <div style="background:var(--fx-white); border:1px solid var(--fx-border); border-radius:var(--fx-radius); padding:24px; margin-bottom:24px;">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:16px; margin-bottom:24px;">
          <div>
            <div style="font-size:13px; font-weight:600; color:var(--fx-muted); text-transform:uppercase; letter-spacing:0.05em;">Order Code</div>
            <div style="font-size:20px; font-weight:800; color:var(--fx-ink); font-family:monospace;"><?= htmlspecialchars($order->order_code); ?></div>
          </div>
          <div style="text-align:right;">
            <div class="fx-track-chip <?= $order->status; ?>">Status: <?= ucfirst($order->status); ?></div>
          </div>
        </div>

        <!-- Timeline Steps -->
        <div style="position:relative; padding-left:24px;">
          <div style="position:absolute; left:6px; top:0; bottom:0; width:2px; background:var(--fx-border);"></div>
          <?php 
            $statuses = [
              'pending' => ['Confirmed', 'Your order has been received'],
              'preparing' => ['Preparing', 'Our kitchen is cooking your meal'],
              'ready' => ['Ready for pickup', 'Your order is ready to collect'],
              'delivering' => ['Out for delivery', 'Your order is on the way'],
              'delivered' => ['Delivered', 'Enjoy your meal!'],
              'cancelled' => ['Cancelled', 'Order was cancelled'],
            ];
            $currentStatus = $order->status;
            $keys = array_keys($statuses);
            $currentIndex = array_search($currentStatus, $keys);
          ?>
          <?php foreach($statuses as $idx => $step): 
            $isCompleted = $idx <= $currentIndex;
            $isCurrent = $idx === $currentIndex;
            $isCancelled = $currentStatus === 'cancelled' && $idx > $currentIndex;
          ?>
          <div style="position:relative; padding-bottom:24px; display:flex; gap:16px;">
            <div style="position:relative; flex:0 0 28px; display:flex; align-items:flex-start;">
              <div style="position:absolute; left:50%; top:0; transform:translateX(-50%); width:14px; height:14px; border-radius:50%; border:3px solid <?= $isCompleted ? 'var(--fx-green)' : 'var(--fx-border)'; ?>; background:<?= $isCompleted ? 'var(--fx-green)' : 'var(--fx-white)'; ?>; z-index:2;"></div>
              <?php if($isCurrent && in_array($currentStatus, ['preparing','ready','delivering'])): ?>
              <div class="fx-track-pulse" style="position:absolute; left:50%; top:0; transform:translateX(-50%);"></div>
              <?php endif; ?>
            </div>
            <div style="flex:1;">
              <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                <strong style="font-size:15px; color:<?= $isCompleted ? 'var(--fx-ink)' : 'var(--fx-muted)'; ?>;"><?= $step[0]; ?></strong>
                <?php if($isCurrent): ?>
                <span class="fx-track-chip <?= $currentStatus; ?>" style="font-size:11px; padding:2px 8px;">Live</span>
                <?php endif; ?>
              </div>
              <div style="font-size:13px; color:var(--fx-muted);"><?= $step[1]; ?></div>
            </div>
          </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Order Details -->
      <div style="background:var(--fx-white); border:1px solid var(--fx-border); border-radius:var(--fx-radius); padding:24px; margin-bottom:24px;">
        <h2 style="font-size:16px; font-weight:700; color:var(--fx-ink); margin-bottom:16px; padding-bottom:12px; border-bottom:1px solid var(--fx-border);">Order Items</h2>
        <?php if(!empty($order_items)): foreach($order_items as $item): ?>
        <div style="display:flex; gap:12px; padding:12px 0; border-bottom:1px solid var(--fx-border);">
          <div style="flex:0 0 56px; aspect-ratio:1; border-radius:var(--fx-radius-sm); overflow:hidden; background:var(--fx-cream); border:1px solid var(--fx-border);">
            <?php if($item['image'] && file_exists($item['image'])): ?>
              <img src="<?= base_url($item['image']); ?>" alt="" style="width:100%;height:100%;object-fit:cover;">
            <?php else: ?>
              <div class="fx-card-placeholder" aria-label="<?= htmlspecialchars($store->store_name ?? 'Restaurant'); ?>"><?= htmlspecialchars($store->store_name ?? 'Restaurant'); ?></div>
            <?php endif; ?>
          </div>
          <div style="flex:1;">
            <div style="font-weight:600; color:var(--fx-ink);"><?= htmlspecialchars($item['name']); ?></div>
            <div style="font-size:13px; color:var(--fx-muted);">Qty: <?= $item['qty']; ?> × <?= sf_currency($item['price'], $store_currency ?? null); ?></div>
          </div>
          <div style="font-weight:700; color:var(--fx-green);"><?= sf_currency($item['price'] * $item['qty'], $store_currency ?? null); ?></div>
        </div>
        <?php endforeach; endif; ?>
      </div>

      <!-- Delivery Info -->
      <?php if(in_array($order->status, ['delivering', 'ready', 'preparing'])): ?>
      <div style="background:var(--fx-white); border:1px solid var(--fx-border); border-radius:var(--fx-radius); padding:24px;">
        <h2 style="font-size:16px; font-weight:700; color:var(--fx-ink); margin-bottom:16px;">Delivery Details</h2>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; font-size:14px;">
          <div>
            <div style="color:var(--fx-muted);">Deliver to</div>
            <div style="font-weight:600; color:var(--fx-ink);"><?= htmlspecialchars($order->delivery_address ?? $order->address); ?></div>
          </div>
          <div>
            <div style="color:var(--fx-muted);">Contact</div>
            <div style="font-weight:600; color:var(--fx-ink);"><?= htmlspecialchars($order->phone ?? $order->customer_phone); ?></div>
          </div>
        </div>
        <?php if($order->status === 'delivering' && !empty($order->driver_phone)): ?>
        <div style="margin-top:16px; padding:16px; background:var(--fx-cream); border-radius:var(--fx-radius-sm); display:flex; align-items:center; gap:12px;">
          <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color:var(--fx-green);"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
          <div>
            <div style="font-weight:600; color:var(--fx-ink);">Driver assigned</div>
            <div style="font-size:13px; color:var(--fx-muted);"><?= htmlspecialchars($order->driver_name); ?> — <a href="tel:<?= $order->driver_phone; ?>" style="color:var(--fx-green);"><?= htmlspecialchars($order->driver_phone); ?></a></div>
          </div>
        </div>
        <?php endif; ?>
      </div>
      <?php endif; ?>

      <div style="text-align:center; margin-top:24px;">
        <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>" class="fx-card-add" style="display:inline-flex; gap:8px;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
          Order Again
        </a>
      </div>
    </div>

    <?php else: ?>
    <!-- Search Form -->
    <div style="max-width:480px; background:var(--fx-white); border:1px solid var(--fx-border); border-radius:var(--fx-radius); padding:32px;">
      <form method="GET" action="<?= base_url('store/' . ($settings->store_slug ?? '') . '/track'); ?>">
        <div style="margin-bottom:16px;">
          <label style="display:block; font-size:13px; font-weight:600; color:var(--fx-ink); margin-bottom:6px;">Order Code <span style="color:var(--fx-danger);">*</span></label>
          <input type="text" name="code" required class="fx-input" style="width:100%; padding:14px 16px; border:2px solid var(--fx-border); border-radius:var(--fx-radius-sm); font-size:16px; font-family:monospace; letter-spacing:0.1em; background:var(--fx-white); color:var(--fx-ink); outline:none; text-transform:uppercase;" placeholder="FX-2024-ABC123">
        </div>
        <div style="margin-bottom:24px;">
          <label style="display:block; font-size:13px; font-weight:600; color:var(--fx-ink); margin-bottom:6px;">Email or Phone</label>
          <input type="text" name="contact" class="fx-input" style="width:100%; padding:14px 16px; border:2px solid var(--fx-border); border-radius:var(--fx-radius-sm); font-size:16px; background:var(--fx-white); color:var(--fx-ink); outline:none;" placeholder="john@example.com or +234 800 000 0000">
        </div>
        <button type="submit" class="fx-card-add" style="width:100%; padding:16px; font-size:16px;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
          Track Order
        </button>
      </form>

      <div style="margin-top:24px; padding-top:24px; border-top:1px solid var(--fx-border); text-align:center;">
        <p style="font-size:13px; color:var(--fx-muted);">Can't find your order? <a href="tel:<?= $settings->store_phone ?? ''; ?>" style="color:var(--fx-green); font-weight:600;">Call us</a> or <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? ''); ?>" target="_blank" style="color:#25D366; font-weight:600;">WhatsApp</a></p>
      </div>
    </div>
    <?php endif; ?>
  </div>
</div>
