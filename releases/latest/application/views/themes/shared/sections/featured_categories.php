<?php $cats = $categories ?? []; ?>
<?php if(!empty($cats)): ?>
<div class="mp-section">
  <div class="mp-section-title">Shop by Category</div>
  <div class="mp-cat-circles">
    <?php foreach(array_slice($cats, 0, 10) as $cat):
      $initial = strtoupper(substr($cat->category_name, 0, 1));
      $catImg = !empty($cat->category_image) ? base_url($cat->category_image) : null;
    ?>
    <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products?category=' . $cat->id); ?>" class="mp-cat-circle">
      <div class="mp-cat-circle-img">
        <?php if($catImg): ?>
        <img src="<?= $catImg; ?>" alt="<?= htmlspecialchars($cat->category_name); ?>">
        <?php else: ?>
        <?= $initial; ?>
        <?php endif; ?>
      </div>
      <div class="mp-cat-circle-label"><?= htmlspecialchars($cat->category_name); ?></div>
    </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>
