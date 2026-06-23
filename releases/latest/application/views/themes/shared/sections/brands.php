<?php $brandList = $brands ?? []; ?>
<?php if(!empty($brandList)): ?>
<div class="mp-section">
  <div class="mp-section-title">Our Brands</div>
  <div style="display:flex; flex-wrap:wrap; gap:24px; justify-content:center; align-items:center;">
    <?php foreach($brandList as $b): ?>
    <?php if($b->brand_url): ?>
    <a href="<?= htmlspecialchars($b->brand_url); ?>" target="_blank" style="text-decoration:none;">
    <?php endif; ?>
      <div style="display:flex; align-items:center; justify-content:center; padding:16px 24px; background:var(--mp-white); border:1px solid var(--mp-border); border-radius:var(--mp-radius-sm); min-width:120px; transition:box-shadow .2s;">
        <?php if($b->brand_logo && file_exists($b->brand_logo)): ?>
          <img src="<?= base_url($b->brand_logo); ?>" alt="<?= htmlspecialchars($b->brand_name); ?>" style="max-height:40px; max-width:120px; object-fit:contain;">
        <?php else: ?>
          <span style="font-weight:700; color:var(--mp-dark); font-size:14px;"><?= htmlspecialchars($b->brand_name); ?></span>
        <?php endif; ?>
      </div>
    <?php if($b->brand_url): ?></a><?php endif; ?>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>
