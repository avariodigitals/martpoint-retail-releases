<?php
$slug = $settings->store_slug ?? '';
$cur = $store_currency ?? null;
if(empty($featured_vehicles)) return;
?>
<style>
  .vf-section { padding:72px 24px; background:var(--bg); }
  .vf-container { max-width:1280px; margin:0 auto; }
  .vf-head { display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:40px; }
  .vf-title { font-family:var(--mp-font); font-size:clamp(28px,3vw,38px); font-weight:800; color:var(--text); margin:0; }
  .vf-link { font-family:var(--mp-font); color:var(--primary); font-weight:600; text-decoration:none; }
  .vf-link:hover { color:var(--primary-dark, var(--primary)); }
  .vf-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:24px; }
  @media(max-width:1023px){ .vf-grid { grid-template-columns:repeat(2,1fr); } }
  @media(max-width:767px){ .vf-grid { grid-template-columns:1fr; } }
  .vf-card { background:var(--surface); border:1px solid var(--border); border-radius:18px; overflow:hidden; transition:transform .2s, box-shadow .2s; text-decoration:none; color:inherit; }
  .vf-card:hover { transform:translateY(-6px); box-shadow:0 18px 40px rgba(0,0,0,.1); }
  .vf-media { aspect-ratio:4/3; overflow:hidden; background:var(--bg); }
  .vf-media img { width:100%; height:100%; object-fit:cover; }
  .vf-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:var(--muted); font-size:40px; }
  .vf-body { padding:16px; }
  .vf-year { font-family:var(--mp-font); font-size:12px; font-weight:700; color:var(--primary); text-transform:uppercase; letter-spacing:0.04em; }
  .vf-name { font-family:var(--mp-font); font-size:17px; font-weight:700; color:var(--text); margin:4px 0 8px; }
  .vf-meta { font-family:var(--mp-font); font-size:13px; color:var(--muted); margin-bottom:12px; }
  .vf-price { font-family:var(--mp-font); font-size:20px; font-weight:800; color:var(--text); }
</style>

<section class="vf-section">
  <div class="vf-container">
    <div class="vf-head">
      <h2 class="vf-title">Featured Vehicles</h2>
      <a href="<?= base_url('store/' . $slug . '/vehicles'); ?>" class="vf-link">View all <i class="fa fa-arrow-right"></i></a>
    </div>
    <div class="vf-grid">
      <?php foreach($featured_vehicles as $v):
        $title = ($v->year ? $v->year . ' ' : '') . $v->make . ' ' . $v->model;
      ?>
      <a href="<?= base_url('store/' . $slug . '/vehicle/' . $v->id); ?>" class="vf-card">
        <div class="vf-media">
          <?php if(!empty($v->image_path) && file_exists(FCPATH . $v->image_path)): ?>
            <?= mp_image_tag($v->image_path, ['width' => 600, 'alt' => htmlspecialchars($title), 'style' => 'width:100%;height:100%;object-fit:cover;']); ?>
          <?php else: ?>
            <div class="vf-placeholder"><i class="fa fa-car"></i></div>
          <?php endif; ?>
        </div>
        <div class="vf-body">
          <div class="vf-year"><?= htmlspecialchars($v->year ?: 'Year N/A'); ?></div>
          <h3 class="vf-name"><?= htmlspecialchars($title); ?></h3>
          <div class="vf-meta"><?= htmlspecialchars(ucwords(str_replace('_',' ',$v->vehicle_condition))); ?> <?= $v->mileage ? '· ' . number_format($v->mileage) . ' km' : ''; ?></div>
          <div class="vf-price"><?= ($cur ? $cur->currency_code . ' ' : '') . number_format($v->price, 2); ?></div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
