<div class="mp-breadcrumb">
  <a href="<?= base_url('store/' . ($settings->store_slug ?? '')); ?>">Home</a> &rsaquo;
  <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/services'); ?>">Services</a> &rsaquo;
  <?= htmlspecialchars($service->service_name); ?>
</div>

<div class="mp-section" style="padding-top:24px;">
  <div style="display:grid; grid-template-columns:1fr; gap:24px;">
    <div>
      <?php if($service->service_image && file_exists($service->service_image)): ?>
        <img src="<?= base_url($service->service_image); ?>" style="width:100%; max-height:480px; object-fit:cover; border-radius:var(--mp-radius-sm); background:var(--mp-light-gray);" alt="<?= htmlspecialchars($service->service_name); ?>">
      <?php else: ?>
        <div style="width:100%; height:360px; display:flex; align-items:center; justify-content:center; background:var(--mp-light-gray); border-radius:var(--mp-radius-sm); color:#94A3B8;">No Image</div>
      <?php endif; ?>
    </div>
    <div>
      <h1 style="font-size:28px; font-weight:800; margin-bottom:12px;"><?= htmlspecialchars($service->service_name); ?></h1>
      <div style="margin-bottom:16px;">
        <span style="font-size:28px; font-weight:800; color:var(--mp-primary);"><?= sf_currency($service->effective_price, $store_currency ?? null); ?></span>
      </div>
      <div style="font-size:14px; color:var(--mp-gray); margin-bottom:24px;">
        <?= $service->category_name ? 'Category: ' . htmlspecialchars($service->category_name) : ''; ?>
        <?= $service->service_duration ? ' &middot; Duration: ' . htmlspecialchars($service->service_duration) : ''; ?>
        <?= $service->location_type ? ' &middot; Location: ' . ucfirst(str_replace('-', ' ', $service->location_type)) : ''; ?>
      </div>
      <div style="font-size:15px; line-height:1.7; color:#334155; margin-bottom:32px;">
        <?= nl2br(htmlspecialchars($service->description ?? '')); ?>
      </div>
      <button onclick="addServiceToCart()" style="width:100%; padding:16px; border-radius:var(--mp-radius-sm); background:var(--mp-primary); color:#fff; font-weight:700; border:none; cursor:pointer; font-size:16px; margin-bottom:12px;">Book This Service</button>
    </div>
  </div>
</div>

<?php if(!empty($related_services)): ?>
<div class="mp-section" style="padding-top:0;">
  <div class="mp-section-title">Related Services</div>
  <div style="display:flex; flex-direction:column; gap:12px;">
    <?php foreach($related_services as $s): ?>
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/service/' . $s->id); ?>" class="mp-service-card">
      <?php if($s->service_image && file_exists($s->service_image)): ?>
        <img src="<?= base_url($s->service_image); ?>" class="mp-service-img" alt="">
      <?php else: ?>
        <div class="mp-service-img" style="display:flex;align-items:center;justify-content:center;color:#94A3B8;font-size:10px;">No Image</div>
      <?php endif; ?>
      <div class="mp-service-info">
        <div class="mp-service-name"><?= htmlspecialchars($s->service_name); ?></div>
        <div class="mp-service-price"><?= sf_currency($s->price, $store_currency ?? null); ?></div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<script>
  const serviceDetail = {
    id: <?= $service->id; ?>,
    name: '<?= htmlspecialchars(addslashes($service->service_name)); ?>',
    price: <?= $service->effective_price; ?>,
    image: '<?= $service->service_image; ?>',
    requires_appointment: <?= $service->requires_appointment ? 'true' : 'false'; ?>,
    requires_note: <?= $service->requires_note ? 'true' : 'false'; ?>
  };

  function addServiceToCart(){
    addToCart(serviceDetail.id, 'service', serviceDetail.name, serviceDetail.price, serviceDetail.image, 1, 999);
    setTimeout(() => { window.location.href = '<?= base_url('store/' . ($settings->store_slug ?? '') . '/cart'); ?>'; }, 600);
  }
</script>
