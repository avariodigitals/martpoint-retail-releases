<?php
$slug = $settings->store_slug ?? '';
$cur = $store_currency ?? null;
?>
<style>
  .auto-featured { padding:48px 0; background:#F9FAFB; }
  .auto-featured-head { display:flex; justify-content:space-between; align-items:flex-end; margin-bottom:24px; }
  .auto-featured-title { font-family:'Inter',sans-serif; font-size:26px; font-weight:800; color:#111827; margin:0; }
  .auto-featured-link { font-family:'Inter',sans-serif; font-size:14px; font-weight:600; color:#EF4444; text-decoration:none; }
  .auto-featured-link:hover { color:#B91C1C; }
  .auto-featured-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:20px; }
  @media(max-width:1023px){ .auto-featured-grid { grid-template-columns:repeat(2,1fr); } }
  @media(max-width:767px){ .auto-featured-grid { grid-template-columns:1fr; } }
  .auto-fv-card { background:#fff; border-radius:16px; overflow:hidden; border:1px solid #E5E7EB; transition:transform .25s, box-shadow .25s; text-decoration:none; color:inherit; }
  .auto-fv-card:hover { transform:translateY(-4px); box-shadow:0 16px 40px rgba(17,24,39,0.1); }
  .auto-fv-media { aspect-ratio:16/10; overflow:hidden; background:#F3F4F6; }
  .auto-fv-media img { width:100%; height:100%; object-fit:cover; }
  .auto-fv-placeholder { width:100%; height:100%; display:flex; align-items:center; justify-content:center; color:#6B7280; font-size:40px; }
  .auto-fv-body { padding:16px; }
  .auto-fv-name { font-family:'Inter',sans-serif; font-size:16px; font-weight:700; color:#111827; margin:0 0 4px; }
  .auto-fv-meta { font-family:'Inter',sans-serif; font-size:12px; color:#6B7280; margin-bottom:10px; }
  .auto-fv-price { font-family:'Inter',sans-serif; font-size:18px; font-weight:800; color:#EF4444; }
</style>

<section class="auto-featured">
  <div class="auto-container" style="max-width:1200px; margin:0 auto; padding:0 24px;">
    <div class="auto-featured-head">
      <h2 class="auto-featured-title">Featured Vehicles</h2>
      <a href="<?= base_url('store/' . $slug . '/vehicles'); ?>" class="auto-featured-link">View all vehicles <i class="fa fa-arrow-right"></i></a>
    </div>
    <div class="auto-featured-grid">
      <?php foreach($featured_vehicles as $v):
        $img = (!empty($v->image_path) && file_exists(FCPATH . $v->image_path)) ? base_url($v->image_path) : '';
      ?>
      <a href="<?= base_url('store/' . $slug . '/vehicle/' . $v->id); ?>" class="auto-fv-card">
        <div class="auto-fv-media">
          <?php if(!empty($v->image_path) && file_exists(FCPATH . $v->image_path)): ?>
            <?= mp_image_tag($v->image_path, ['width' => 600, 'alt' => htmlspecialchars(($v->year ? $v->year . ' ' : '') . $v->make . ' ' . $v->model), 'style' => 'width:100%;height:100%;object-fit:cover;']); ?>
          <?php else: ?>
            <div class="auto-fv-placeholder"><i class="fa fa-car"></i></div>
          <?php endif; ?>
        </div>
        <div class="auto-fv-body">
          <h3 class="auto-fv-name"><?= htmlspecialchars(($v->year ? $v->year . ' ' : '') . $v->make . ' ' . $v->model); ?></h3>
          <div class="auto-fv-meta"><?= htmlspecialchars(ucfirst($v->vehicle_condition)); ?> <?= $v->mileage ? '· ' . number_format($v->mileage) . ' km' : ''; ?></div>
          <div class="auto-fv-price"><?= ($cur ? $cur->currency_code . ' ' : '') . number_format($v->price, 2); ?></div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</section>
