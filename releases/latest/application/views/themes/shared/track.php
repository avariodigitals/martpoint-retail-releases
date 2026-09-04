<?php
/**
 * Shared Track Order page
 * Premium order tracking with timeline, order details and customer info.
 */
$slug = $settings->store_slug ?? '';
$visibleStatuses = $visible_statuses ?? ['pending','paid','processing','ready','completed'];
$statusOrder = $visibleStatuses;
$currentStep = array_search(($order->order_status ?? 'pending'), $statusOrder);
if($currentStep === false) $currentStep = 0;
$cur = $store_currency ?? null;
?>
<style>
  .mp-track-page { max-width:640px; margin:0 auto; padding:24px 16px 48px; }

  .mp-track-card { background:var(--mp-white); border-radius:var(--mp-radius); border:1px solid var(--mp-border); padding:32px 24px; margin-bottom:16px; }
  .mp-track-title { font-size:24px; font-weight:800; color:var(--mp-dark); margin-bottom:6px; }
  .mp-track-subtitle { font-size:14px; color:var(--mp-gray); margin-bottom:24px; line-height:1.5; }

  .mp-track-form { display:flex; flex-direction:column; gap:16px; }
  .mp-track-fields { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
  @media(max-width:600px){ .mp-track-fields { grid-template-columns:1fr; } }
  .mp-track-field label { font-size:13px; font-weight:600; color:var(--mp-gray); margin-bottom:6px; display:block; }
  .mp-track-field input { width:100%; padding:12px 14px; border:1px solid var(--mp-border); border-radius:var(--mp-radius-sm); font-size:14px; outline:none; transition:border-color .2s, box-shadow .2s; font-family:inherit; }
  .mp-track-field input:focus { border-color:var(--mp-primary); box-shadow:0 0 0 3px rgba(59,130,246,0.1); }
  .mp-track-btn { padding:14px 24px; border-radius:var(--mp-radius-sm); background:var(--mp-primary); color:#fff; font-weight:700; border:none; cursor:pointer; font-size:15px; transition:background .2s, transform .1s; align-self:flex-start; }
  .mp-track-btn:hover { background:var(--mp-primary-dark); }
  .mp-track-btn:active { transform:scale(0.98); }
  .mp-track-error { background:#FEE2E2; border:1px solid #FECACA; color:#991B1B; padding:12px 16px; border-radius:var(--mp-radius-sm); font-size:14px; margin-top:8px; }

  /* Result */
  .mp-track-result { margin-top:24px; }
  .mp-track-summary { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:24px; }
  @media(max-width:600px){ .mp-track-summary { grid-template-columns:1fr; } }
  .mp-track-summary-item { background:var(--mp-light-gray); border-radius:var(--mp-radius-sm); padding:14px; text-align:center; }
  .mp-track-summary-label { font-size:11px; text-transform:uppercase; letter-spacing:0.06em; color:var(--mp-gray); font-weight:600; margin-bottom:4px; }
  .mp-track-summary-value { font-size:15px; font-weight:700; color:var(--mp-dark); }

  /* Timeline */
  .mp-track-timeline { position:relative; padding-left:32px; margin-bottom:24px; }
  .mp-track-timeline::before { content:''; position:absolute; left:11px; top:8px; bottom:8px; width:2px; background:var(--mp-border); }
  .mp-track-step { position:relative; padding-bottom:24px; }
  .mp-track-step:last-child { padding-bottom:0; }
  .mp-track-dot { position:absolute; left:-32px; top:0; width:24px; height:24px; border-radius:50%; border:2px solid var(--mp-border); background:var(--mp-white); z-index:1; transition:all .3s; }
  .mp-track-step.active .mp-track-dot { border-color:var(--mp-primary); background:var(--mp-primary); }
  .mp-track-step.current .mp-track-dot { border-color:var(--mp-success); background:var(--mp-success); box-shadow:0 0 0 4px rgba(16,185,129,0.15); }
  .mp-track-status { font-size:15px; font-weight:700; color:var(--mp-dark); margin-bottom:2px; }
  .mp-track-step:not(.active) .mp-track-status { color:var(--mp-gray); }
  .mp-track-desc { font-size:13px; color:var(--mp-gray); }

  .mp-track-cancelled { background:#FEE2E2; color:#991B1B; padding:16px; border-radius:var(--mp-radius-sm); text-align:center; font-weight:700; font-size:16px; margin-bottom:16px; }

  /* Items */
  .mp-track-items-title { font-size:15px; font-weight:700; margin-bottom:12px; color:var(--mp-dark); }
  .mp-track-items { background:var(--mp-light-gray); border-radius:var(--mp-radius-sm); padding:12px 16px; }
  .mp-track-item { display:flex; align-items:center; gap:12px; padding:8px 0; border-bottom:1px solid var(--mp-border); }
  .mp-track-item:last-child { border-bottom:none; }
  .mp-track-item-name { flex:1; font-size:14px; font-weight:600; color:var(--mp-dark); }
  .mp-track-item-qty { font-size:13px; color:var(--mp-gray); }
  .mp-track-item-price { font-size:14px; font-weight:700; color:var(--mp-primary); white-space:nowrap; }

  /* Badges */
  .mp-track-badge { display:inline-block; padding:4px 12px; border-radius:20px; font-size:11px; font-weight:700; text-transform:uppercase; letter-spacing:0.04em; }
  .mp-track-badge-paid { background:#D1FAE5; color:#065F46; }
  .mp-track-badge-unpaid { background:#FEE2E2; color:#991B1B; }
  .mp-track-badge-processing { background:#DBEAFE; color:#1E40AF; }
</style>

<div class="mp-track-page">
  <div class="mp-track-card">
    <h1 class="mp-track-title">Track Your Order</h1>
    <p class="mp-track-subtitle">Enter your order number and the phone number used when placing the order.</p>

    <form method="post" action="<?= base_url('store/' . $slug . '/track'); ?>" class="mp-track-form">
      <input type="hidden" name="<?= $csrf_name; ?>" value="<?= $csrf_hash; ?>">
      <div class="mp-track-fields">
        <div class="mp-track-field">
          <label for="track-order-code">Order Number</label>
          <input type="text" name="order_code" id="track-order-code" value="<?= htmlspecialchars($this->input->post('order_code') ?? ''); ?>" placeholder="e.g. WEB-20260101-0001" required>
        </div>
        <div class="mp-track-field">
          <label for="track-phone">Phone Number</label>
          <input type="tel" name="customer_phone" id="track-phone" value="<?= htmlspecialchars($this->input->post('customer_phone') ?? ''); ?>" placeholder="Phone used for the order" required>
        </div>
      </div>
      <button type="submit" class="mp-track-btn">Track Order</button>
      <?php if(!empty($error)): ?>
        <div class="mp-track-error" role="alert"><?= htmlspecialchars($error); ?></div>
      <?php endif; ?>
    </form>

    <?php if($order): ?>
      <div class="mp-track-result">
        <div class="mp-track-summary">
          <div class="mp-track-summary-item">
            <div class="mp-track-summary-label">Order</div>
            <div class="mp-track-summary-value"><?= htmlspecialchars($order->order_code); ?></div>
          </div>
          <div class="mp-track-summary-item">
            <div class="mp-track-summary-label">Placed</div>
            <div class="mp-track-summary-value"><?= date('M j, Y', strtotime($order->created_at)); ?></div>
          </div>
          <div class="mp-track-summary-item">
            <div class="mp-track-summary-label">Total</div>
            <div class="mp-track-summary-value"><?= sf_currency($order->grand_total ?? 0, $cur); ?></div>
          </div>
        </div>

        <?php if($order->order_status === 'cancelled'): ?>
          <div class="mp-track-cancelled">This order has been cancelled</div>
        <?php else: ?>
          <div class="mp-track-timeline">
            <?php foreach($statusOrder as $i => $status):
              $info = $status_map[$status] ?? ['label' => ucfirst($status), 'desc' => ''];
              $active = $i <= $currentStep;
              $current = $i === $currentStep;
            ?>
            <div class="mp-track-step <?= $active ? 'active' : ''; ?> <?= $current ? 'current' : ''; ?>">
              <div class="mp-track-dot"></div>
              <div class="mp-track-status"><?= htmlspecialchars($info['label']); ?></div>
              <div class="mp-track-desc"><?= htmlspecialchars($info['desc']); ?></div>
            </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if(!empty($items)): ?>
          <h2 class="mp-track-items-title">Items in this order</h2>
          <div class="mp-track-items">
            <?php foreach($items as $item): ?>
              <div class="mp-track-item">
                <span class="mp-track-item-name"><?= htmlspecialchars($item->item_name); ?></span>
                <span class="mp-track-item-qty">x<?= (int)$item->qty; ?></span>
                <span class="mp-track-item-price"><?= sf_currency($item->unit_price ?? 0, $cur); ?></span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <?php if(!empty($can_testimonial) && $can_testimonial): ?>
        <div class="mp-track-testimonial" style="margin-top:28px;padding:24px;background:#F0FDF4;border-radius:var(--mp-radius-sm);border:1px solid #BBF7D0;">
          <?php if(!empty($testimonial_submitted) && $testimonial_submitted): ?>
            <div style="font-weight:700;color:#166534;font-size:15px;">Thank you! Your review has been submitted and is pending approval.</div>
          <?php else: ?>
            <h2 style="font-size:16px;font-weight:700;margin-bottom:6px;">How was your experience?</h2>
            <p style="font-size:13px;color:var(--mp-gray);margin-bottom:16px;">Leave a quick review. It will appear on the store after approval.</p>
            <form id="track-testimonial-form" onsubmit="submitTrackTestimonial(event)">
              <input type="hidden" name="<?= $csrf_name; ?>" value="<?= $csrf_hash; ?>">
              <input type="hidden" name="order_code" value="<?= htmlspecialchars($order->order_code ?? ''); ?>">
              <input type="hidden" name="customer_phone" value="<?= htmlspecialchars($order->customer_phone ?? ''); ?>">
              <div style="margin-bottom:12px;">
                <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Your Name</label>
                <input type="text" name="customer_name" value="<?= htmlspecialchars($order->customer_name ?? ''); ?>" required style="width:100%;padding:10px;border:1px solid var(--mp-border);border-radius:8px;font-size:14px;">
              </div>
              <div style="margin-bottom:12px;">
                <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Rating</label>
                <select name="rating" required style="width:100%;padding:10px;border:1px solid var(--mp-border);border-radius:8px;font-size:14px;">
                  <?php for($i=5;$i>=1;$i--): ?>
                  <option value="<?= $i; ?>"><?= $i; ?> Star<?= $i>1 ? 's' : ''; ?></option>
                  <?php endfor; ?>
                </select>
              </div>
              <div style="margin-bottom:12px;">
                <label style="display:block;font-size:13px;font-weight:600;margin-bottom:6px;">Your Review</label>
                <textarea name="testimonial_text" required rows="3" style="width:100%;padding:10px;border:1px solid var(--mp-border);border-radius:8px;font-size:14px;resize:vertical;"></textarea>
              </div>
              <button type="submit" class="mp-track-btn" id="track-testimonial-btn" style="background:#059669;">Submit Review</button>
              <div id="track-testimonial-message" style="margin-top:10px;font-size:13px;"></div>
            </form>
            <script>
            function submitTrackTestimonial(e){
              e.preventDefault();
              const btn = document.getElementById('track-testimonial-btn');
              const msg = document.getElementById('track-testimonial-message');
              const form = document.getElementById('track-testimonial-form');
              const data = new FormData(form);
              btn.disabled = true; btn.textContent = 'Submitting...'; msg.textContent = '';
              fetch('<?= base_url('store/' . $slug . '/track/testimonial'); ?>', {
                method:'POST',
                body: new URLSearchParams(data)
              })
              .then(r => r.json())
              .then(res => {
                if(res.csrf_hash) form.querySelector('input[type=hidden][name=<?= $csrf_name; ?>]').value = res.csrf_hash;
                if(res.status){
                  msg.innerHTML = '<span style="color:#166534;font-weight:600;">Thank you! Your review has been submitted and is pending approval.</span>';
                  form.reset();
                } else {
                  msg.innerHTML = '<span style="color:#991B1B;">' + (res.message || 'Could not submit review. Try again.') + '</span>';
                  btn.disabled = false; btn.textContent = 'Submit Review';
                }
              })
              .catch(() => {
                msg.innerHTML = '<span style="color:#991B1B;">Network error. Try again.</span>';
                btn.disabled = false; btn.textContent = 'Submit Review';
              });
            }
            </script>
          <?php endif; ?>
        </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</div>
