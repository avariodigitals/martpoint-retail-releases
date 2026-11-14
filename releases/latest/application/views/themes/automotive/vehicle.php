<?php
$slug = $settings->store_slug ?? '';
$cur = $store_currency ?? null;
$img = (!empty($vehicle->image_path) && file_exists(FCPATH . $vehicle->image_path)) ? base_url($vehicle->image_path) : '';
$title = ($vehicle->year ? $vehicle->year . ' ' : '') . $vehicle->make . ' ' . $vehicle->model;
$waNumber = preg_replace('/[^0-9]/', '', $whatsapp_number ?? '');
$waMessage = 'Hi, I am interested in the ' . $title . ' listed at ' . base_url('store/' . $slug . '/vehicle/' . $vehicle->id) . ' for ' . number_format($vehicle->price, 2);
$waLink = 'https://wa.me/' . $waNumber . '?text=' . rawurlencode($waMessage);
$vehicleLink = base_url('store/' . $slug . '/vehicle/' . $vehicle->id);
?>
<style>
  .theme-automotive .mp-topbar,
  .theme-automotive .mp-announcement,
  .theme-automotive .mp-nav,
  .theme-automotive .mp-header,
  .theme-automotive .mp-mobile-menu-btn,
  .theme-automotive .mp-footer-space { display:none !important; }

  :root {
    --auto-dark:#111827;
    --auto-red:#EF4444;
    --auto-red-dark:#B91C1C;
    --auto-gray:#F3F4F6;
    --auto-ink:#1F2937;
    --auto-muted:#6B7280;
    --auto-border:#E5E7EB;
    --auto-white:#FFFFFF;
  }

  .auto-container { max-width:1200px; margin:0 auto; padding:0 24px; }
  @media(max-width:767px){ .auto-container { padding:0 16px; } }

  .auto-breadcrumb { padding:24px 0 0; font-family:'Inter',sans-serif; font-size:13px; color:var(--auto-muted); }
  .auto-breadcrumb a { color:var(--auto-dark); text-decoration:none; }
  .auto-breadcrumb a:hover { color:var(--auto-red); }
  .auto-breadcrumb .sep { margin:0 8px; color:var(--auto-muted); }

  .auto-detail { padding:32px 0 72px; }
  .auto-detail-grid { display:grid; grid-template-columns:1.1fr 1fr; gap:40px; }
  @media(max-width:1023px){ .auto-detail-grid { grid-template-columns:1fr; gap:28px; } }

  .auto-detail-media { position:relative; }
  .auto-detail-img { width:100%; aspect-ratio:4/3; object-fit:cover; border-radius:20px; background:var(--auto-gray); border:1px solid var(--auto-border); }
  .auto-detail-placeholder { width:100%; aspect-ratio:4/3; border-radius:20px; background:var(--auto-gray); display:flex; align-items:center; justify-content:center; color:var(--auto-muted); font-size:80px; }

  .auto-detail-info { display:flex; flex-direction:column; gap:18px; }
  .auto-detail-year { font-family:'Inter',sans-serif; font-size:12px; font-weight:700; color:var(--auto-red); text-transform:uppercase; letter-spacing:0.04em; }
  .auto-detail-title { font-family:'Inter',sans-serif; font-size:clamp(28px,3vw,40px); font-weight:800; color:var(--auto-dark); margin:0; line-height:1.1; }
  .auto-detail-price { font-family:'Inter',sans-serif; font-size:32px; font-weight:800; color:var(--auto-red); }
  .auto-detail-desc { font-family:'Inter',sans-serif; font-size:15px; line-height:1.7; color:var(--auto-muted); }

  .auto-specs { display:grid; grid-template-columns:repeat(2,1fr); gap:14px; background:var(--auto-gray); border-radius:16px; padding:18px; }
  @media(max-width:767px){ .auto-specs { grid-template-columns:1fr; } }
  .auto-spec { display:flex; justify-content:space-between; font-family:'Inter',sans-serif; font-size:14px; }
  .auto-spec .label { color:var(--auto-muted); }
  .auto-spec .value { font-weight:600; color:var(--auto-ink); }

  .auto-actions { display:flex; gap:12px; flex-wrap:wrap; margin-top:8px; }
  .auto-btn { padding:14px 28px; border-radius:999px; font-family:'Inter',sans-serif; font-size:15px; font-weight:600; text-align:center; text-decoration:none; border:none; cursor:pointer; transition:background .2s; display:inline-flex; align-items:center; justify-content:center; gap:8px; }
  .auto-btn-primary { background:var(--auto-dark); color:#fff; }
  .auto-btn-primary:hover { background:#000; }
  .auto-btn-wa { background:#25D366; color:#fff; }
  .auto-btn-wa:hover { background:#1FB855; }
  .auto-btn-outline { background:#fff; color:var(--auto-dark); border:1.5px solid var(--auto-dark); }
  .auto-btn-outline:hover { background:var(--auto-dark); color:#fff; }

  .auto-share { margin-top:14px; font-family:'Inter',sans-serif; font-size:13px; color:var(--auto-muted); }
  .auto-share input { width:100%; padding:10px 12px; border:1px solid var(--auto-border); border-radius:10px; margin-top:6px; font-size:13px; }
</style>

<div class="auto-container">
  <nav class="auto-breadcrumb">
    <a href="<?= base_url('store/' . $slug); ?>">Home</a>
    <span class="sep">/</span>
    <a href="<?= base_url('store/' . $slug . '/vehicles'); ?>">Vehicles</a>
    <span class="sep">/</span>
    <span><?= htmlspecialchars($title); ?></span>
  </nav>

  <div class="auto-detail">
    <div class="auto-detail-grid">
      <div class="auto-detail-media">
        <?php if(!empty($vehicle->image_path) && file_exists(FCPATH . $vehicle->image_path)): ?>
          <?= mp_image_tag($vehicle->image_path, ['width' => 1200, 'alt' => htmlspecialchars($title), 'class' => 'auto-detail-img']); ?>
        <?php else: ?>
          <div class="auto-detail-placeholder"><i class="fa fa-car"></i></div>
        <?php endif; ?>
      </div>

      <div class="auto-detail-info">
        <div class="auto-detail-year"><?= htmlspecialchars($vehicle->year ?: 'Year N/A'); ?></div>
        <h1 class="auto-detail-title"><?= htmlspecialchars($title); ?></h1>
        <div class="auto-detail-price"><?= ($cur ? $cur->currency_code . ' ' : '') . number_format($vehicle->price, 2); ?></div>

        <div class="auto-specs">
          <div class="auto-spec"><span class="label">Make</span><span class="value"><?= htmlspecialchars($vehicle->make); ?></span></div>
          <div class="auto-spec"><span class="label">Model</span><span class="value"><?= htmlspecialchars($vehicle->model); ?></span></div>
          <div class="auto-spec"><span class="label">Year</span><span class="value"><?= htmlspecialchars($vehicle->year ?: '-'); ?></span></div>
          <div class="auto-spec"><span class="label">Condition</span><span class="value"><?= htmlspecialchars(ucfirst($vehicle->vehicle_condition)); ?></span></div>
          <div class="auto-spec"><span class="label">Mileage</span><span class="value"><?= $vehicle->mileage ? number_format($vehicle->mileage) . ' km' : '-'; ?></span></div>
          <div class="auto-spec"><span class="label">Color</span><span class="value"><?= htmlspecialchars($vehicle->color ?: '-'); ?></span></div>
          <div class="auto-spec"><span class="label">Fuel</span><span class="value"><?= htmlspecialchars($vehicle->fuel_type ?: '-'); ?></span></div>
          <div class="auto-spec"><span class="label">Transmission</span><span class="value"><?= htmlspecialchars($vehicle->transmission ?: '-'); ?></span></div>
          <div class="auto-spec"><span class="label">VIN</span><span class="value"><?= htmlspecialchars($vehicle->vin ?: '-'); ?></span></div>
          <div class="auto-spec"><span class="label">License Plate</span><span class="value"><?= htmlspecialchars($vehicle->license_plate ?: '-'); ?></span></div>
        </div>

        <?php if(!empty($vehicle->description)): ?>
        <div class="auto-detail-desc"><?= nl2br(htmlspecialchars($vehicle->description)); ?></div>
        <?php endif; ?>

        <div class="auto-actions">
          <?php if(!empty($waNumber)): ?>
            <a href="<?= $waLink; ?>" target="_blank" class="auto-btn auto-btn-wa"><i class="fa fa-whatsapp"></i> Ask on WhatsApp</a>
          <?php endif; ?>
          <?php if(!empty($settings->store_phone)): ?>
            <a href="tel:<?= preg_replace('/[^0-9+]/', '', $settings->store_phone); ?>" class="auto-btn auto-btn-outline"><i class="fa fa-phone"></i> Call</a>
          <?php endif; ?>
          <button type="button" class="auto-btn auto-btn-outline" onclick="copyVehicleLink()"><i class="fa fa-link"></i> Copy Link</button>
        </div>

        <div class="auto-share">
          Share this vehicle on WhatsApp or social media.
          <input type="text" id="vehicle-share-link" value="<?= $vehicleLink; ?>" readonly>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
  function copyVehicleLink(){
    var input = document.getElementById('vehicle-share-link');
    input.select();
    input.setSelectionRange(0, 99999);
    if(navigator.clipboard){
      navigator.clipboard.writeText(input.value).then(function(){
        alert('Link copied to clipboard. Paste it in WhatsApp, Facebook, Instagram or any ad.');
      });
    } else {
      document.execCommand('copy');
      alert('Link copied to clipboard. Paste it in WhatsApp, Facebook, Instagram or any ad.');
    }
  }
</script>
