<?php include(APPPATH.'views/themes/creator_focus/_nav.php'); ?>
<?php
$type = isset($_GET['type']) ? strtolower(trim($_GET['type'])) : '';
$allowed = ['course','digital','membership','service','physical'];
$activeType = in_array($type, $allowed) ? $type : '';
$filtered = array_filter($products, function($p) use ($activeType){ return $activeType === '' || ($p->product_type ?? 'physical') === $activeType; });
$types = [
  '' => 'All',
  'course' => 'Courses',
  'digital' => 'Downloads',
  'membership' => 'Memberships',
  'service' => 'Services',
  'physical' => 'Physical',
];
$isHeroDark = in_array($THEME, ['focus','bold']);
$heroText = $isHeroDark ? '#fff' : $c['text'];
$heroMuted = $isHeroDark ? 'rgba(255,255,255,.9)' : $c['muted'];
?>
<style>
.crf-page{background:<?= $c['surface'];?>;min-height:80vh;}
.crf-hero-sm{background:<?= $c['hero-gradient'];?>;color:<?= $heroText;?>;padding:48px 24px;text-align:center;}
.crf-hero-sm h1{font-size:32px;font-weight:900;margin-bottom:8px;color:<?= $heroText;?>;}
.crf-hero-sm p{font-size:16px;color:<?= $heroMuted;?>;}
.crf-catalog{max-width:1200px;margin:0 auto;padding:48px 24px;}
.crf-filters{display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin-bottom:28px;}
.crf-filter{border:1px solid <?= $c['border'];?>;padding:10px 20px;border-radius:999px;background:#fff;color:<?= $c['muted'];?>;font-size:13px;font-weight:700;cursor:pointer;text-decoration:none;transition:all .15s;}
.crf-filter:hover,.crf-filter.active{background:<?= $c['primary'];?>;color:#fff;border-color:<?= $c['primary'];?>;}
.crf-empty{text-align:center;padding:80px 24px;color:<?= $c['muted'];?>;}
/* Reuse crf-card classes from main.php by outputting them inline */
.crf-card{background:<?= $c['surface'];?>;border:1px solid <?= $c['border'];?>;border-radius:14px;overflow:hidden;transition:transform .2s,box-shadow .2s;display:flex;flex-direction:column;}
.crf-card:hover{transform:translateY(-4px);box-shadow:0 16px 40px rgba(0,0,0,.1);}
.crf-card-img{aspect-ratio:4/3;overflow:hidden;background:<?= $c['border'];?>;position:relative;}
.crf-card-img img{width:100%;height:100%;object-fit:cover;transition:transform .4s;}
.crf-card:hover .crf-card-img img{transform:scale(1.05);}
.crf-card-badge{position:absolute;top:12px;left:12px;padding:5px 12px;border-radius:999px;background:<?= $c['primary'];?>;color:#fff;font-size:10px;font-weight:800;text-transform:uppercase;}
.crf-card-body{padding:18px;flex:1;display:flex;flex-direction:column;}
.crf-card-name{font-size:16px;font-weight:800;margin-bottom:8px;color:<?= $text;?>;line-height:1.3;}
.crf-card-desc{font-size:13px;color:<?= $c['muted'];?>;margin-bottom:12px;line-height:1.5;flex:1;}
.crf-card-meta{display:flex;gap:8px;align-items:center;flex-wrap:wrap;margin-bottom:12px;font-size:12px;color:<?= $c['muted'];?>;}
.crf-pill{padding:3px 10px;border-radius:999px;background:rgba(5,150,105,.1);color:#047857;font-weight:700;}
.crf-card-footer{display:flex;align-items:center;justify-content:space-between;gap:10px;}
.crf-card-price{font-size:18px;font-weight:800;color:<?= $c['primary'];?>;}
.crf-card-old{font-size:13px;color:<?= $c['muted'];?>;text-decoration:line-through;margin-left:4px;}
.crf-card-btn{width:36px;height:36px;border-radius:50%;border:none;background:<?= $c['primary'];?>;color:#fff;display:flex;align-items:center;justify-content:center;font-size:18px;cursor:pointer;}
.crf-card-btn:hover{background:<?= $c['primary-dark'];?>;}
.crf-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:20px;}
.crf-pagination{display:flex;justify-content:center;gap:8px;margin-top:40px;}
.crf-pagination a,.crf-pagination span{padding:10px 16px;border-radius:8px;background:#fff;border:1px solid <?= $c['border'];?>;font-size:14px;font-weight:700;color:<?= $text;?>;text-decoration:none;}
.crf-pagination a:hover,.crf-pagination .current{background:<?= $c['primary'];?>;color:#fff;border-color:<?= $c['primary'];?>;}
@media(max-width:1024px){.crf-grid{grid-template-columns:repeat(2,1fr);}}
@media(max-width:640px){.crf-grid{grid-template-columns:1fr;}}
</style>

<div class="crf-page">
  <section class="crf-hero-sm">
    <h1><?= $activeType ? ucfirst($types[$activeType]) : 'Library'; ?></h1>
    <p><?= $activeType ? 'Browse our ' . strtolower($types[$activeType]) . ' collection' : 'Explore courses, downloads, memberships and more'; ?></p>
  </section>

  <div class="crf-catalog">
    <div class="crf-filters">
      <?php foreach($types as $k => $label):
        $url = base_url('store/' . $slug . '/products' . ($k ? '?type=' . $k : ''));
      ?>
      <a class="crf-filter <?= ($activeType === $k) ? 'active' : ''; ?>" href="<?= $url; ?>"><?= $label; ?></a>
      <?php endforeach; ?>
    </div>

    <?php if(empty($filtered)): ?>
      <div class="crf-empty">
        <h2 style="font-size:22px;font-weight:800;margin-bottom:10px;color:<?= $text;?>">Nothing here yet</h2>
        <p>This creator hasn't published any <?= $activeType ? $types[$activeType] : 'items'; ?>.</p>
        <a href="<?= base_url('store/' . $slug . '/products'); ?>" class="crf-filter active" style="margin-top:20px;display:inline-block;">Browse everything</a>
      </div>
    <?php else: ?>
      <div class="crf-grid">
        <?php foreach($filtered as $p):
          $price = $p->effective_price ?? $p->sales_price; $old = $p->original_price ?? $p->sales_price; $disc = $old > $price;
          $pt = $p->product_type ?? 'physical'; $badge = ['digital'=>'Download','course'=>'Course','membership'=>'Membership','service'=>'Service','physical'=>'Product'][$pt] ?? 'Product';
          $soldLabel = $pt === 'course' ? 'students' : ($pt === 'membership' ? 'members' : 'sold');
        ?>
        <a href="<?= base_url('store/' . $slug . '/product/' . $p->id); ?>" class="crf-card">
          <div class="crf-card-img">
            <?php if($p->item_image && file_exists($p->item_image)): ?>
              <?= mp_image_tag($p->item_image, ['width' => 600, 'alt' => htmlspecialchars($p->item_name)]); ?>
            <?php else: ?>
              <div style="width:100%;height:100%;display:flex;align-items:center;justify-content:center;background:<?= $c['border'];?>;color:<?= $c['muted'];?>;font-size:60px;font-weight:800;"><?= htmlspecialchars(substr($p->item_name,0,1)); ?></div>
            <?php endif; ?>
            <span class="crf-card-badge"><?= $badge; ?></span>
          </div>
          <div class="crf-card-body">
            <div class="crf-card-name"><?= htmlspecialchars($p->item_name); ?></div>
            <div class="crf-card-desc"><?= htmlspecialchars(strip_tags($p->description ?? '')); ?></div>
            <div class="crf-card-meta">
              <span class="crf-pill">Available</span>
              <?php if($p->sold_count > 0): ?><span><?= number_format($p->sold_count); ?> <?= $soldLabel; ?></span><?php endif; ?>
            </div>
            <div class="crf-card-footer">
              <div><span class="crf-card-price"><?= sf_currency($price, $store_currency ?? null); ?></span><?php if($disc): ?><span class="crf-card-old"><?= sf_currency($old, $store_currency ?? null); ?></span><?php endif; ?></div>
              <button class="crf-card-btn" onclick="event.preventDefault();event.stopPropagation();addToCart(<?= $p->id; ?>,'<?= $pt; ?>','<?= htmlspecialchars(addslashes($p->item_name)); ?>',<?= $price; ?>,'<?= $p->item_image; ?>',1,<?= $p->stock; ?>)">+</button>
            </div>
          </div>
        </a>
        <?php endforeach; ?>
      </div>

      <?php if($activeType === '' && $total_pages > 1): ?>
      <div class="crf-pagination">
        <?php for($i=1; $i<=$total_pages; $i++):
          $url = base_url('store/' . $slug . '/products?page=' . $i);
        ?>
        <?php if($i == $page): ?>
          <span class="current"><?= $i; ?></span>
        <?php else: ?>
          <a href="<?= $url; ?>"><?= $i; ?></a>
        <?php endif; ?>
        <?php endfor; ?>
      </div>
      <?php endif; ?>
    <?php endif; ?>
  </div>
</div>

<?php include(APPPATH.'views/themes/creator_focus/_footer.php'); ?>
