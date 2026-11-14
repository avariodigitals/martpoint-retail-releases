<?php
$slug = $settings->store_slug ?? '';
$cur = $store_currency ?? null;
$waNumber = preg_replace('/[^0-9]/', '', $settings->whatsapp_number ?? '');
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

  .auto-hero { background:var(--auto-dark); color:#fff; padding:64px 24px 48px; text-align:center; }
  .auto-hero h1 { font-family:var(--mp-font); font-size:clamp(32px,5vw,52px); font-weight:800; margin:0 0 12px; letter-spacing:-0.02em; }
  .auto-hero p { font-family:var(--mp-font); font-size:18px; color:#94A3B8; margin:0 0 24px; }
  .auto-hero-wa { display:inline-flex; align-items:center; gap:8px; padding:14px 28px; border-radius:999px; background:#25D366; color:#fff; font-weight:600; text-decoration:none; }
  .auto-hero-wa:hover { background:#1FB855; color:#fff; }

  .auto-list { padding:48px 24px; background:var(--auto-bg); }
  .auto-container { max-width:1280px; margin:0 auto; }
  .auto-list-head { display:flex; justify-content:space-between; align-items:flex-end; flex-wrap:wrap; gap:12px; margin-bottom:24px; }
  .auto-list-title { font-family:var(--mp-font); font-size:28px; font-weight:800; color:var(--auto-ink); margin:0; }
  .auto-count { font-size:14px; color:var(--auto-muted); }

  .auto-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:24px; }
  @media(max-width:1023px){ .auto-grid { grid-template-columns:repeat(2,1fr); } }
  @media(max-width:767px){ .auto-grid { grid-template-columns:1fr; } }

  .auto-card { background:var(--auto-white); border:1px solid var(--auto-border); border-radius:18px; overflow:hidden; transition:transform .2s, box-shadow .2s; }
  .auto-card:hover { transform:translateY(-5px); box-shadow:0 20px 44px rgba(11,17,32,0.1); }
  .auto-card-media { aspect-ratio:4/3; overflow:hidden; background:var(--auto-bg); }
  .auto-card-media img { width:100%; height:100%; object-fit:cover; }
  .auto-card-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:var(--auto-muted); font-size:48px; }
  .auto-card-body { padding:18px; }
  .auto-card-year { font-family:var(--mp-font); font-size:12px; font-weight:700; color:var(--auto-red); text-transform:uppercase; letter-spacing:0.04em; }
  .auto-card-name { font-family:var(--mp-font); font-size:18px; font-weight:700; color:var(--auto-ink); margin:4px 0 8px; }
  .auto-card-meta { font-size:13px; color:var(--auto-muted); margin-bottom:14px; }
  .auto-card-price { font-size:22px; font-weight:800; color:var(--auto-ink); margin-bottom:14px; }
  .auto-card-actions { display:flex; gap:10px; }
  .auto-btn { flex:1; padding:11px 16px; border-radius:999px; font-size:14px; font-weight:600; text-align:center; text-decoration:none; display:inline-flex; align-items:center; justify-content:center; gap:6px; }
  .auto-btn-dark { background:var(--auto-dark); color:#fff; }
  .auto-btn-dark:hover { background:#1F2937; color:#fff; }
  .auto-btn-wa { background:#25D366; color:#fff; }
  .auto-btn-wa:hover { background:#1FB855; color:#fff; }

  .auto-empty { text-align:center; padding:80px 20px; color:var(--auto-muted); }
  .auto-empty h3 { color:var(--auto-ink); font-size:24px; }
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

<section class="auto-hero">
  <h1>Vehicles for Sale</h1>
  <p>Browse our current stock and ask about any vehicle on WhatsApp.</p>
  <?php if(!empty($waNumber)): ?>
    <a href="https://wa.me/<?= $waNumber; ?>?text=<?= rawurlencode('Hi, I want to know about your available vehicles at ' . base_url('store/' . $slug . '/vehicles')); ?>" class="auto-hero-wa" target="_blank"><i class="fa fa-whatsapp"></i> Ask on WhatsApp</a>
  <?php endif; ?>
</section>

<section class="auto-list">
  <div class="auto-container">
    <div class="auto-list-head">
      <h2 class="auto-list-title">Available Vehicles</h2>
      <span class="auto-count"><?= count($vehicles); ?> vehicle<?= count($vehicles) !== 1 ? 's' : ''; ?></span>
    </div>

    <?php if(!empty($vehicles)): ?>
    <div class="auto-grid">
      <?php foreach($vehicles as $v):
        $title = ($v->year ? $v->year . ' ' : '') . $v->make . ' ' . $v->model;
        $wa = 'https://wa.me/' . $waNumber . '?text=' . rawurlencode('Hi, I am interested in the ' . $title . ' at ' . base_url('store/' . $slug . '/vehicle/' . $v->id));
      ?>
      <div class="auto-card">
        <a href="<?= base_url('store/' . $slug . '/vehicle/' . $v->id); ?>">
          <div class="auto-card-media">
            <?php if(!empty($v->image_path) && file_exists(FCPATH . $v->image_path)): ?>
              <?= mp_image_tag($v->image_path, ['width' => 600, 'alt' => htmlspecialchars($title), 'style' => 'width:100%;height:100%;object-fit:cover;']); ?>
            <?php else: ?>
              <div class="auto-card-placeholder"><i class="fa fa-car"></i></div>
            <?php endif; ?>
          </div>
        </a>
        <div class="auto-card-body">
          <div class="auto-card-year"><?= htmlspecialchars($v->year ?: 'Year N/A'); ?></div>
          <h3 class="auto-card-name"><?= htmlspecialchars($title); ?></h3>
          <div class="auto-card-meta"><?= htmlspecialchars(ucfirst(str_replace('_',' ',$v->vehicle_condition))); ?> <?= $v->mileage ? '· ' . number_format($v->mileage) . ' km' : ''; ?></div>
          <div class="auto-card-price"><?= ($cur ? $cur->currency_code . ' ' : '') . number_format($v->price, 2); ?></div>
          <div class="auto-card-actions">
            <a href="<?= base_url('store/' . $slug . '/vehicle/' . $v->id); ?>" class="auto-btn auto-btn-dark">View Details</a>
            <?php if(!empty($waNumber)): ?>
            <a href="<?= $wa; ?>" target="_blank" class="auto-btn auto-btn-wa"><i class="fa fa-whatsapp"></i> Request on WhatsApp</a>
            <?php endif; ?>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="auto-empty">
      <h3>No vehicles in stock</h3>
      <p>Check back soon for new arrivals.</p>
    </div>
    <?php endif; ?>
  </div>
</section>
