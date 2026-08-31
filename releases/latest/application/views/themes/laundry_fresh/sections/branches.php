<?php
$branches = $this->theme_engine->branches();
?>
<?php if(!empty($branches)): ?>
<section class="mp-section laundry-branches" id="branches">
  <div class="mp-section-title">Our Branches</div>
  <div class="laundry-branches-grid">
    <?php foreach($branches as $b): ?>
    <div class="laundry-branch-card">
      <div class="laundry-branch-name"><?= htmlspecialchars($b->warehouse_name); ?></div>
      <?php if(!empty($b->mobile)): ?>
      <div class="laundry-branch-detail">
        <a href="tel:<?= preg_replace('/[^0-9+]/', '', $b->mobile); ?>"><?= htmlspecialchars($b->mobile); ?></a>
      </div>
      <?php endif; ?>
      <?php if(!empty($b->email)): ?>
      <div class="laundry-branch-detail"><?= htmlspecialchars($b->email); ?></div>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
