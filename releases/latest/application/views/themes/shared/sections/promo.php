<?php $promos = $promo_banners ?? []; ?>
<?php if(!empty($promos)): ?>
<div class="mp-section mp-promo-section">
  <div class="mp-section-title">Special Offers</div>
  <div class="mp-promo-grid">
    <?php foreach($promos as $p): ?>
    <a href="<?= htmlspecialchars($p->button_url ?: base_url('store/' . ($settings->store_slug ?? '') . '/products')); ?>" class="mp-promo-card">
      <img src="<?= $p->desktop_image ? base_url($p->desktop_image) : ''; ?>" alt="<?= htmlspecialchars($p->banner_title); ?>">
      <div class="mp-promo-overlay">
        <div class="mp-promo-title"><?= htmlspecialchars($p->banner_title); ?></div>
        <?php if($p->banner_subtitle): ?>
        <div class="mp-promo-subtitle"><?= htmlspecialchars($p->banner_subtitle); ?></div>
        <?php endif; ?>
        <?php if($p->button_text): ?>
        <span class="mp-promo-btn"><?= htmlspecialchars($p->button_text); ?></span>
        <?php endif; ?>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>
