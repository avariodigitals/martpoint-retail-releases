<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <title><?= htmlspecialchars($service->service_name); ?> | <?= htmlspecialchars($store->store_name ?? 'Store'); ?></title>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    :root { --primary:#3B82F6; --dark:#0F172A; --gray:#64748B; --light-gray:#F1F5F9; --border:#E2E8F0; --white:#fff; --radius-sm:8px; }
    * { margin:0; padding:0; box-sizing:border-box; }
    body { font-family:'Inter',sans-serif; background:#F8FAFC; color:var(--dark); }
    a { text-decoration:none; color:inherit; }
    img { max-width:100%; display:block; }
    .sf-header { background:var(--white); border-bottom:1px solid var(--border); position:sticky; top:0; z-index:100; }
    .sf-header-inner { max-width:600px; margin:0 auto; padding:12px 16px; display:flex; align-items:center; gap:12px; }
    .sf-back { font-size:22px; }
    .sf-img { width:100%; height:240px; object-fit:cover; background:var(--light-gray); max-width:600px; margin:0 auto; }
    .sf-info { max-width:600px; margin:0 auto; padding:16px; }
    .sf-name { font-size:22px; font-weight:700; margin-bottom:8px; }
    .sf-price { font-size:26px; font-weight:800; color:var(--primary); margin-bottom:8px; }
    .sf-meta { font-size:13px; color:var(--gray); margin-bottom:16px; }
    .sf-desc { font-size:14px; line-height:1.6; color:#334155; margin-bottom:24px; }
    .sf-section { max-width:600px; margin:0 auto; padding:16px; }
    .sf-section-title { font-size:16px; font-weight:700; margin-bottom:12px; }
    .sf-service-card { background:var(--white); border-radius:var(--radius-sm); border:1px solid var(--border); padding:12px; display:flex; gap:12px; align-items:center; margin-bottom:10px; }
    .sf-service-img { width:60px; height:60px; border-radius:8px; object-fit:cover; background:var(--light-gray); flex-shrink:0; }
    .sf-service-info { flex:1; }
    .sf-service-name { font-size:14px; font-weight:600; }
    .sf-service-price { font-size:15px; font-weight:700; color:var(--primary); }
    .sf-btn { width:100%; padding:14px; border-radius:var(--radius-sm); background:var(--primary); color:#fff; font-weight:700; border:none; cursor:pointer; font-size:16px; margin-bottom:12px; }
    .sf-whatsapp { width:100%; padding:14px; border-radius:var(--radius-sm); background:#25D366; color:#fff; font-weight:700; border:none; cursor:pointer; font-size:16px; }
    .sf-toast { position:fixed; top:16px; left:50%; transform:translateX(-50%) translateY(-80px); background:var(--dark); color:#fff; padding:12px 20px; border-radius:var(--radius-sm); font-size:14px; z-index:300; opacity:0; transition:all .3s ease; }
    .sf-toast.show { transform:translateX(-50%) translateY(0); opacity:1; }
    .sf-note-box { background:#EFF6FF; border:1px solid #BFDBFE; border-radius:var(--radius-sm); padding:12px; margin-bottom:16px; font-size:13px; color:#1E40AF; }
  </style>
</head>
<body>

<div class="sf-header">
  <div class="sf-header-inner">
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/services'); ?>" class="sf-back">&#8592;</a>
  </div>
</div>

<?php if($service->service_image && file_exists($service->service_image)): ?>
  <img src="<?= base_url($service->service_image); ?>" class="sf-img" alt="">
<?php else: ?>
  <div class="sf-img" style="display:flex;align-items:center;justify-content:center;color:#94A3B8;">No Image</div>
<?php endif; ?>

<div class="sf-info">
  <div class="sf-name"><?= htmlspecialchars($service->service_name); ?></div>
  <div class="sf-price"><?= store_number_format($service->effective_price); ?></div>
  <div class="sf-meta">
    <?= htmlspecialchars($service->service_duration ?: ''); ?> &middot; <?= ucfirst(str_replace('-',' ',$service->location_type)); ?> &middot; <?= htmlspecialchars($service->category_name ?: 'Uncategorized'); ?>
  </div>
  <?php if($service->requires_appointment): ?>
    <div class="sf-note-box">This service requires scheduling a date and time.</div>
  <?php endif; ?>
  <?php if($service->requires_note): ?>
    <div class="sf-note-box">Please provide details about your service request.</div>
  <?php endif; ?>
  <div class="sf-desc"><?= nl2br(htmlspecialchars($service->description ?? '')); ?></div>

  <button class="sf-btn" onclick="bookService()">Book This Service</button>
  <?php if($settings->whatsapp_number): ?>
  <button class="sf-whatsapp" onclick="sendWhatsApp()">Ask on WhatsApp</button>
  <?php endif; ?>
</div>

<?php if(!empty($related_services)): ?>
<div class="sf-section">
  <div class="sf-section-title">Related Services</div>
  <?php foreach($related_services as $s): ?>
  <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/service/' . $s->id); ?>" class="sf-service-card">
    <?php if($s->service_image && file_exists($s->service_image)): ?>
      <img src="<?= base_url($s->service_image); ?>" class="sf-service-img" alt="">
    <?php else: ?>
      <div class="sf-service-img" style="display:flex;align-items:center;justify-content:center;color:#94A3B8;font-size:10px;">No Image</div>
    <?php endif; ?>
    <div class="sf-service-info">
      <div class="sf-service-name"><?= htmlspecialchars($s->service_name); ?></div>
      <div class="sf-service-price"><?= store_number_format($s->effective_price ?? $s->price); ?></div>
    </div>
  </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="sf-toast" id="toast"></div>

<script>
  const STORE_ID = <?= $settings->store_id ?? 0; ?>;
  const CURRENCY = '<?= $CURRENCY ?? '&#8358;'; ?>';
  const service = {
    id: <?= $service->id; ?>,
    name: '<?= htmlspecialchars(addslashes($service->service_name)); ?>',
    price: <?= $service->effective_price ?? $service->price; ?>,
    image: '<?= $service->service_image; ?>',
    requires_appointment: <?= $service->requires_appointment ? 'true' : 'false'; ?>,
    requires_note: <?= $service->requires_note ? 'true' : 'false'; ?>
  };

  function showToast(msg){
    const t = document.getElementById('toast');
    t.textContent = msg; t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2500);
  }

  function bookService(){
    let cart = JSON.parse(localStorage.getItem('sf_cart_' + STORE_ID) || '[]');
    const key = 'service_' + service.id;
    const existing = cart.find(i => i.key === key);
    if(existing){
      existing.qty += 1;
    } else {
      cart.push({key, id: service.id, type: 'service', name: service.name, price: service.price, image: service.image, qty: 1, requires_appointment: service.requires_appointment, requires_note: service.requires_note});
    }
    localStorage.setItem('sf_cart_' + STORE_ID, JSON.stringify(cart));
    showToast(service.name + ' added to cart');
    setTimeout(() => {
      window.location.href = '<?= base_url('store/' . ($settings->store_slug ?? '') . '/cart'); ?>';
    }, 800);
  }

  function sendWhatsApp(){
    let msg = 'Hello, I am interested in the service: ' + service.name;
    const wnum = '<?= preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? ''); ?>';
    if(wnum) window.open('https://wa.me/' + wnum + '?text=' + encodeURIComponent(msg), '_blank');
  }
</script>

</body>
</html>
