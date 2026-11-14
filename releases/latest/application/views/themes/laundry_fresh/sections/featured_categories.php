<?php
$slug = $settings->store_slug ?? '';
$cats = $this->theme_engine->serviceCategories();
?>
<?php if(!empty($cats)): ?>
<section class="mp-section laundry-categories">
  <div class="mp-section-title">Browse by Service</div>
  <div class="laundry-cat-grid">
    <?php foreach(array_slice($cats, 0, 8) as $cat):
      $catImg = $this->theme_engine->categoryImage($cat);
    ?>
    <a href="<?= base_url('store/' . $slug . '/services?category=' . $cat->id); ?>" class="laundry-cat-card">
      <?php if($catImg): ?>
        <img src="<?= $catImg; ?>" alt="<?= htmlspecialchars($cat->category_name); ?>" loading="lazy">
      <?php else: ?>
        <span class="laundry-cat-initial"><?= strtoupper(substr($cat->category_name, 0, 1)); ?></span>
      <?php endif; ?>
      <span class="laundry-cat-label"><?= htmlspecialchars($cat->category_name); ?></span>
    </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
