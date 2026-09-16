<?php
$slug = $settings->store_slug ?? '';
$cur = $store_currency ?? null;
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

  .auto-page { padding:32px 0 72px; }
  .auto-page-head { margin-bottom:32px; }
  .auto-section-label { font-family:'Inter',sans-serif; font-size:12px; text-transform:uppercase; letter-spacing:0.14em; color:var(--auto-red); font-weight:600; margin-bottom:6px; }
  .auto-section-title { font-family:'Inter',sans-serif; font-size:32px; margin:0; font-weight:800; color:var(--auto-dark); letter-spacing:-0.02em; }

  .auto-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:24px; }
  @media(max-width:1023px){ .auto-grid { grid-template-columns:repeat(2,1fr); } }
  @media(max-width:767px){ .auto-grid { grid-template-columns:1fr; } }

  .auto-card { position:relative; background:var(--auto-white); border-radius:18px; overflow:hidden; border:1px solid var(--auto-border); transition:transform .25s, box-shadow .25s; }
  .auto-card:hover { transform:translateY(-6px); box-shadow:0 20px 48px rgba(17,24,39,0.12); }
  .auto-card-media { aspect-ratio:4/3; overflow:hidden; background:var(--auto-gray); }
  .auto-card-media img { width:100%; height:100%; object-fit:cover; transition:transform .4s ease; }
  .auto-card:hover .auto-card-media img { transform:scale(1.05); }
  .auto-card-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; background:var(--auto-gray); color:var(--auto-muted); font-size:48px; }
  .auto-card-body { padding:18px; }
  .auto-card-year { font-family:'Inter',sans-serif; font-size:12px; font-weight:700; color:var(--auto-red); text-transform:uppercase; letter-spacing:0.04em; margin-bottom:4px; }
  .auto-card-name { font-family:'Inter',sans-serif; font-size:18px; font-weight:700; margin:0 0 8px; color:var(--auto-ink); line-height:1.2; }
  .auto-card-meta { font-family:'Inter',sans-serif; font-size:13px; color:var(--auto-muted); margin-bottom:12px; }
  .auto-card-price { font-family:'Inter',sans-serif; font-size:22px; font-weight:800; color:var(--auto-dark); margin-bottom:14px; }
  .auto-card-actions { display:flex; gap:10px; }
  .auto-btn { flex:1; padding:12px 16px; border-radius:999px; font-family:'Inter',sans-serif; font-size:14px; font-weight:600; text-align:center; text-decoration:none; border:none; cursor:pointer; transition:background .2s; display:inline-flex; align-items:center; justify-content:center; gap:6px; }
  .auto-btn-primary { background:var(--auto-dark); color:#fff; }
  .auto-btn-primary:hover { background:#000; }
  .auto-btn-wa { background:#25D366; color:#fff; }
  .auto-btn-wa:hover { background:#1FB855; }

  .auto-empty { text-align:center; padding:80px 20px; color:var(--auto-muted); }
  .auto-empty h3 { font-size:24px; color:var(--auto-dark); margin-bottom:8px; }
</style>

<div class="auto-container">
  <nav class="auto-breadcrumb">
    <a href="<?= base_url('store/' . $slug); ?>">Home</a>
    <span class="sep">/</span>
    <span>Vehicles</span>
  </nav>

  <div class="auto-page">
    <div class="auto-page-head">
      <div class="auto-section-label">For Sale</div>
      <h1 class="auto-section-title">Available Vehicles</h1>
    </div>

    <?php if(!empty($vehicles)): ?>
    <div class="auto-grid">
      <?php foreach($vehicles as $v):
        $img = (!empty($v->image_path) && file_exists(FCPATH . $v->image_path)) ? base_url($v->image_path) : '';
        $wa = 'https://wa.me/' . preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? '') . '?text=' . rawurlencode('Hi, I am interested in the ' . ($v->year ? $v->year . ' ' : '') . $v->make . ' ' . $v->model . ' listed at ' . base_url('store/' . $slug . '/vehicle/' . $v->id));
      ?>
      <div class="auto-card">
        <a href="<?= base_url('store/' . $slug . '/vehicle/' . $v->id); ?>">
          <div class="auto-card-media">
            <?php if(!empty($v->image_path) && file_exists(FCPATH . $v->image_path)): ?>
              <?= mp_image_tag($v->image_path, ['width' => 600, 'alt' => htmlspecialchars(($v->year ? $v->year . ' ' : '') . $v->make . ' ' . $v->model), 'style' => 'width:100%;height:100%;object-fit:cover;']); ?>
            <?php else: ?>
              <div class="auto-card-placeholder"><i class="fa fa-car"></i></div>
            <?php endif; ?>
          </div>
        </a>
        <div class="auto-card-body">
          <div class="auto-card-year"><?= htmlspecialchars($v->year ?: 'Year N/A'); ?></div>
          <h2 class="auto-card-name"><?= htmlspecialchars($v->make . ' ' . $v->model); ?></h2>
          <div class="auto-card-meta"><?= htmlspecialchars(ucfirst($v->vehicle_condition)); ?> <?= $v->mileage ? '· ' . number_format($v->mileage) . ' km' : ''; ?></div>
          <div class="auto-card-price"><?= ($cur ? $cur->currency_code . ' ' : '') . number_format($v->price, 2); ?></div>
          <div class="auto-card-actions">
            <a href="<?= base_url('store/' . $slug . '/vehicle/' . $v->id); ?>" class="auto-btn auto-btn-primary">View Details</a>
            <?php if(!empty($settings->whatsapp_number)): ?>
            <a href="<?= $wa; ?>" target="_blank" class="auto-btn auto-btn-wa"><i class="fa fa-whatsapp"></i></a>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="auto-empty">
      <h3>No vehicles available right now</h3>
      <p>Check back soon for new stock.</p>
    </div>
    <?php endif; ?>
  </div>
</div>
