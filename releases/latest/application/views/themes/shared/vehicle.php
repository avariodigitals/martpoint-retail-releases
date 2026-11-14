<?php
$slug = $settings->store_slug ?? '';
$cur = $store_currency ?? null;
$title = ($vehicle->year ? $vehicle->year . ' ' : '') . $vehicle->make . ' ' . $vehicle->model;
$waNumber = preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? '');
$waMessage = 'Hi, I am interested in the ' . $title . ' listed at ' . base_url('store/' . $slug . '/vehicle/' . $vehicle->id) . ' for ' . number_format($vehicle->price, 2);
$waLink = 'https://wa.me/' . $waNumber . '?text=' . rawurlencode($waMessage);
$vehicleLink = base_url('store/' . $slug . '/vehicle/' . $vehicle->id);
$condition = ucwords(str_replace('_', ' ', $vehicle->vehicle_condition));
?>
<style>
  :root {
    --auto-dark:#0B1120;
    --auto-ink:#1F2937;
    --auto-muted:#6B7280;
    --auto-border:#E5E7EB;
    --auto-bg:#F8FAFC;
    --auto-white:#FFFFFF;
    --auto-red:#EF4444;
    --auto-red-dark:#B91C1C;
  }
  .mp-topbar, .mp-announcement, .mp-nav, .mp-header, .mp-mobile-menu-btn, .mp-footer-space { display:none !important; }

  .auto-header { background:var(--auto-dark); border-bottom:1px solid rgba(255,255,255,0.08); position:sticky; top:0; z-index:1000; }
  .auto-header-inner { max-width:1280px; margin:0 auto; padding:14px 24px; display:flex; align-items:center; justify-content:space-between; gap:16px; }
  .auto-header-logo { display:flex; align-items:center; gap:10px; font-family:var(--mp-font); font-weight:800; font-size:18px; color:#fff; text-decoration:none; }
  .auto-header-logo img { height:34px; width:auto; }
  .auto-header-nav { display:flex; align-items:center; gap:20px; }
  .auto-header-nav a { font-size:14px; font-weight:500; color:#CBD5E1; text-decoration:none; }
  .auto-header-nav a:hover { color:#fff; }
  .auto-header-wa { padding:8px 16px; border-radius:999px; background:#25D366; color:#fff; font-size:13px; font-weight:600; }
  .auto-header-wa:hover { background:#1FB855; color:#fff; }
  @media(max-width:767px){ .auto-header-nav a:not(.auto-header-wa) { display:none; } }

  .auto-breadcrumb { max-width:1280px; margin:0 auto; padding:24px 24px 0; font-size:13px; color:var(--auto-muted); }
  .auto-breadcrumb a { color:var(--auto-ink); text-decoration:none; }
  .auto-breadcrumb a:hover { color:var(--auto-red); }
  .auto-breadcrumb .sep { margin:0 8px; }

  .auto-detail { padding:32px 24px 80px; background:var(--auto-bg); }
  .auto-container { max-width:1280px; margin:0 auto; }
  .auto-detail-grid { display:grid; grid-template-columns:1.1fr 1fr; gap:48px; align-items:start; }
  @media(max-width:1023px){ .auto-detail-grid { grid-template-columns:1fr; } }

  .auto-detail-media { border-radius:20px; overflow:hidden; background:var(--auto-white); border:1px solid var(--auto-border); }
  .auto-detail-media img { width:100%; aspect-ratio:4/3; object-fit:cover; display:block; }
  .auto-detail-placeholder { width:100%; aspect-ratio:4/3; background:var(--auto-bg); display:flex; align-items:center; justify-content:center; color:var(--auto-muted); font-size:80px; }
  .auto-gallery-thumbs { display:grid; grid-template-columns:repeat(auto-fill, minmax(80px, 1fr)); gap:10px; padding:16px; background:var(--auto-white); }
  @media(max-width:480px){ .auto-gallery-thumbs { grid-template-columns:repeat(auto-fill, minmax(60px, 1fr)); } }
  .auto-gallery-thumb { border-radius:10px; overflow:hidden; border:1px solid var(--auto-border); cursor:pointer; opacity:.75; transition:opacity .15s, transform .15s; }
  .auto-gallery-thumb.active, .auto-gallery-thumb:hover { opacity:1; transform:scale(1.03); }
  .auto-gallery-thumb img { width:100%; aspect-ratio:1/1; object-fit:cover; }

  .auto-detail-info { display:flex; flex-direction:column; gap:18px; }
  .auto-detail-year { font-size:13px; font-weight:700; color:var(--auto-red); text-transform:uppercase; letter-spacing:0.04em; }
  .auto-detail-title { font-size:clamp(30px,4vw,44px); font-weight:800; color:var(--auto-ink); margin:0; line-height:1.1; }
  .auto-detail-price { font-size:34px; font-weight:800; color:var(--auto-red); }
  .auto-detail-condition { display:inline-flex; align-items:center; gap:6px; font-size:14px; font-weight:600; color:var(--auto-muted); }
  .auto-detail-condition .dot { width:8px; height:8px; border-radius:50%; background:var(--auto-red); }

  .auto-specs { display:grid; grid-template-columns:repeat(2,1fr); gap:12px; background:var(--auto-white); border:1px solid var(--auto-border); border-radius:16px; padding:20px; }
  .auto-spec { display:flex; justify-content:space-between; font-size:14px; padding:8px 0; border-bottom:1px solid var(--auto-border); }
  .auto-spec:nth-last-child(-n+2) { border-bottom:none; }
  .auto-spec .label { color:var(--auto-muted); }
  .auto-spec .value { font-weight:600; color:var(--auto-ink); text-align:right; }

  .auto-detail-desc { color:var(--auto-muted); font-size:15px; line-height:1.7; }
  .auto-actions { display:flex; gap:12px; flex-wrap:wrap; margin-top:8px; }
  .auto-btn { padding:14px 28px; border-radius:999px; font-size:15px; font-weight:600; text-align:center; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; gap:8px; }
  .auto-btn-dark { background:var(--auto-dark); color:#fff; }
  .auto-btn-dark:hover { background:#1F2937; color:#fff; }
  .auto-btn-wa { background:#25D366; color:#fff; }
  .auto-btn-wa:hover { background:#1FB855; color:#fff; }
  .auto-btn-outline { background:#fff; color:var(--auto-ink); border:1.5px solid var(--auto-border); }
  .auto-btn-outline:hover { background:var(--auto-ink); color:#fff; border-color:var(--auto-ink); }

  .auto-share { background:var(--auto-white); border:1px solid var(--auto-border); border-radius:12px; padding:16px; margin-top:10px; }
  .auto-share p { margin:0 0 8px; font-size:13px; color:var(--auto-muted); }
  .auto-share input { width:100%; padding:10px; border:1px solid var(--auto-border); border-radius:8px; font-size:13px; }
</style>

<header class="auto-header">
  <div class="auto-header-inner">
    <a class="auto-header-logo" href="<?= base_url('store/' . $slug); ?>">
      <?php if(!empty($logo_url) && strpos($logo_url,'default') === false): ?>
        <img src="<?= $logo_url; ?>" alt="<?= htmlspecialchars($store->store_name ?? 'Store'); ?>">
      <?php endif; ?>
      <span><?= htmlspecialchars($store->store_name ?? 'Store'); ?></span>
    </a>
    <nav class="auto-header-nav">
      <a href="<?= base_url('store/' . $slug); ?>">Home</a>
      <a href="<?= base_url('store/' . $slug . '/vehicles'); ?>">Vehicles</a>
      <?php if(!empty($waNumber)): ?>
      <a href="https://wa.me/<?= $waNumber; ?>" target="_blank" class="auto-header-wa"><i class="fa fa-whatsapp"></i></a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<div class="auto-breadcrumb">
  <a href="<?= base_url('store/' . $slug); ?>">Home</a>
  <span class="sep">/</span>
  <a href="<?= base_url('store/' . $slug . '/vehicles'); ?>">Vehicles</a>
  <span class="sep">/</span>
  <span><?= htmlspecialchars($title); ?></span>
</div>

<section class="auto-detail">
  <div class="auto-container">
    <div class="auto-detail-grid">
      <div class="auto-detail-media">
        <?php
          $gallery = !empty($vehicle->images) ? $vehicle->images : [];
          $primary = $gallery[0]->image_path ?? $vehicle->image_path ?? '';
        ?>
        <?php if(!empty($primary) && file_exists(FCPATH . $primary)): ?>
          <img id="auto-main-image" src="<?= base_url($primary); ?>" alt="<?= htmlspecialchars($title); ?>" style="width:100%; aspect-ratio:4/3; object-fit:cover;">
        <?php else: ?>
          <div class="auto-detail-placeholder"><i class="fa fa-car"></i></div>
        <?php endif; ?>
        <?php if (!empty($gallery)): ?>
        <div class="auto-gallery-thumbs">
          <?php foreach ($gallery as $g): ?>
          <div class="auto-gallery-thumb <?= $g->is_primary ? 'active' : ''; ?>" data-src="<?= base_url($g->image_path); ?>">
            <img src="<?= base_url($g->image_path); ?>" alt="<?= htmlspecialchars($title); ?>">
          </div>
          <?php endforeach; ?>
        </div>
        <script>
          document.querySelectorAll('.auto-gallery-thumb').forEach(function(t){ t.addEventListener('click', function(){ document.getElementById('auto-main-image').src = this.dataset.src; document.querySelectorAll('.auto-gallery-thumb').forEach(function(el){ el.classList.remove('active'); }); this.classList.add('active'); }); });
        </script>
        <?php endif; ?>
      </div>

      <div class="auto-detail-info">
        <div class="auto-detail-year"><?= htmlspecialchars($vehicle->year ?: 'Year N/A'); ?></div>
        <h1 class="auto-detail-title"><?= htmlspecialchars($title); ?></h1>
        <div class="auto-detail-price"><?= ($cur ? $cur->currency_code . ' ' : '') . number_format($vehicle->price, 2); ?></div>
        <div class="auto-detail-condition"><span class="dot"></span> <?= htmlspecialchars($condition); ?></div>

        <div class="auto-specs">
          <div class="auto-spec"><span class="label">Make</span><span class="value"><?= htmlspecialchars($vehicle->make); ?></span></div>
          <div class="auto-spec"><span class="label">Model</span><span class="value"><?= htmlspecialchars($vehicle->model); ?></span></div>
          <div class="auto-spec"><span class="label">Year</span><span class="value"><?= htmlspecialchars($vehicle->year ?: '-'); ?></span></div>
          <div class="auto-spec"><span class="label">Condition</span><span class="value"><?= htmlspecialchars($condition); ?></span></div>
          <div class="auto-spec"><span class="label">Body Type</span><span class="value"><?= htmlspecialchars($vehicle->body_type ?: '-'); ?></span></div>
          <div class="auto-spec"><span class="label">Engine</span><span class="value"><?= htmlspecialchars($vehicle->engine_capacity ?: '-'); ?></span></div>
          <div class="auto-spec"><span class="label">Drivetrain</span><span class="value"><?= htmlspecialchars($vehicle->drivetrain ?: '-'); ?></span></div>
          <div class="auto-spec"><span class="label">Trim Level</span><span class="value"><?= htmlspecialchars($vehicle->trim_level ?: '-'); ?></span></div>
          <div class="auto-spec"><span class="label">Color</span><span class="value"><?= htmlspecialchars($vehicle->color ?: '-'); ?></span></div>
          <div class="auto-spec"><span class="label">Mileage</span><span class="value"><?= $vehicle->mileage ? number_format($vehicle->mileage) . ' km' : '-'; ?></span></div>
          <div class="auto-spec"><span class="label">Fuel</span><span class="value"><?= htmlspecialchars($vehicle->fuel_type ?: '-'); ?></span></div>
          <div class="auto-spec"><span class="label">Transmission</span><span class="value"><?= htmlspecialchars($vehicle->transmission ?: '-'); ?></span></div>
          <div class="auto-spec"><span class="label">VIN</span><span class="value"><?= htmlspecialchars($vehicle->vin ?: '-'); ?></span></div>
          <div class="auto-spec"><span class="label">License Plate</span><span class="value"><?= htmlspecialchars($vehicle->license_plate ?: '-'); ?></span></div>
          <div class="auto-spec"><span class="label">Previous Owners</span><span class="value"><?= $vehicle->number_of_owners ? (int) $vehicle->number_of_owners : '-'; ?></span></div>
          <div class="auto-spec"><span class="label">Registered</span><span class="value"><?= !empty($vehicle->registration_date) ? date('M Y', strtotime($vehicle->registration_date)) : '-'; ?></span></div>
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
          <p>Share this vehicle on WhatsApp, Instagram, Facebook or in an ad.</p>
          <input type="text" id="vehicle-share-link" value="<?= $vehicleLink; ?>" readonly>
        </div>
      </div>
    </div>
  </div>
</section>

<script>
  function copyVehicleLink(){
    var input = document.getElementById('vehicle-share-link');
    input.select();
    input.setSelectionRange(0, 99999);
    if(navigator.clipboard){
      navigator.clipboard.writeText(input.value).then(function(){ alert('Link copied. Paste it in any ad or message.'); });
    } else {
      document.execCommand('copy');
      alert('Link copied. Paste it in any ad or message.');
    }
  }
</script>
