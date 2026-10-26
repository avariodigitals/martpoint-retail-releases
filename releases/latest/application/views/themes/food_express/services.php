<?php
$services = $services ?? [];
$categories = $service_categories ?? [];
$currentCategory = $category_id ?? null;
$searchTerm = $search ?? '';
?>
<div class="fx-section" style="padding-top:16px;">
  <div class="fx-section-inner">
    <div class="fx-section-header" style="margin-bottom:24px;">
      <div>
        <?php if($searchTerm): ?>
        <div class="fx-section-overline">Search results for</div>
        <h1 class="fx-section-title">"<?= htmlspecialchars($searchTerm); ?>"</h1>
        <p class="fx-section-subtitle"><?= count($services); ?> services found</p>
        <?php elseif($currentCategory && !empty($categories)): 
          $cat = null; foreach($categories as $c){ if($c->id == $currentCategory){ $cat=$c; break; } } ?>
        <div class="fx-section-overline">Category</div>
        <h1 class="fx-section-title"><?= htmlspecialchars($cat->category_name ?? 'Services'); ?></h1>
        <p class="fx-section-subtitle"><?= count($services); ?> services</p>
        <?php else: ?>
        <div class="fx-section-overline">Beyond the menu</div>
        <h1 class="fx-section-title">Experiences &amp; bookings</h1>
        <p class="fx-section-subtitle">Private dining, catering and other ways to enjoy the kitchen.</p>
        <?php endif; ?>
      </div>
    </div>

    <?php if(!empty($categories) && !$searchTerm): ?>
    <div class="fx-categories" style="margin-bottom:32px;">
      <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/services'); ?>" class="fx-cat-tile" style="opacity:<?= !$currentCategory ? '1' : '0.5'; ?>">
        <div class="fx-cat-img-wrap"><div class="fx-cat-img-placeholder"><span>ALL</span></div></div>
        <div class="fx-cat-name">All</div>
      </a>
      <?php foreach(array_slice($categories, 0, 10) as $cat): ?>
      <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/services?category=' . $cat->id); ?>" class="fx-cat-tile <?= ($cat->id == $currentCategory) ? 'active' : ''; ?>" style="opacity:<?= $currentCategory && $cat->id != $currentCategory ? '0.4' : '1'; ?>">
        <div class="fx-cat-img-wrap">
          <?php if(!empty($cat->category_image) && file_exists($cat->category_image)): ?>
            <img src="<?= base_url($cat->category_image); ?>" alt="" class="fx-cat-img" loading="lazy">
          <?php else: ?>
            <div class="fx-cat-img-placeholder" aria-hidden="true"><span><?= htmlspecialchars(mb_strtoupper(mb_substr($cat->category_name, 0, 1, 'UTF-8'), 'UTF-8')); ?></span></div>
          <?php endif; ?>
        </div>
        <div class="fx-cat-name"><?= htmlspecialchars($cat->category_name); ?></div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <?php if(!empty($services)): ?>
    <div class="fx-grid">
      <?php foreach($services as $s):
        $price = $s->effective_price ?? $s->price;
        $oldPrice = $s->original_price ?? $s->price;
        $hasDiscount = $oldPrice > $price;
        $discountPct = $hasDiscount ? round((($oldPrice - $price) / $oldPrice) * 100) : 0;
      ?>
      <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/service/' . $s->id); ?>" class="fx-card">
        <div class="fx-card-img-wrap">
          <?php if($s->service_image && file_exists($s->service_image)): ?>
            <img src="<?= base_url($s->service_image); ?>" class="fx-card-img" alt="<?= htmlspecialchars($s->service_name); ?>" loading="lazy">
          <?php else: ?>
            <div class="fx-card-placeholder" aria-hidden="true"><span class="fx-service-mark">SERVICE</span></div>
          <?php endif; ?>
          <?php if($hasDiscount && $discountPct > 0): ?>
            <span class="fx-card-badge sale">-<?= $discountPct; ?>%</span>
          <?php endif; ?>
        </div>
        <div class="fx-card-body">
          <h3 class="fx-card-name"><?= htmlspecialchars($s->service_name); ?></h3>
          <?php if($s->short_description): ?>
          <p style="font-size:13px; color:var(--fx-muted); line-height:1.5; margin-bottom:10px; display:-webkit-box; -webkit-line-clamp:2; -webkit-box-orient:vertical; overflow:hidden;"><?= htmlspecialchars($s->short_description); ?></p>
          <?php endif; ?>
          <div class="fx-card-price-row">
            <span class="fx-card-price"><?= sf_currency($price, $store_currency ?? null); ?></span>
            <?php if($hasDiscount): ?><span class="fx-card-old-price"><?= sf_currency($oldPrice, $store_currency ?? null); ?></span><?php endif; ?>
          </div>
          <div class="fx-card-footer">
            <button class="fx-card-add" onclick="event.stopPropagation(); event.preventDefault();" style="background:var(--fx-cream); color:var(--fx-green); border:1px solid var(--fx-green);">
              <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 4v8M8 12h8"/></svg>
              Book Now
            </button>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div style="text-align:center; padding:60px 20px; color:var(--fx-muted);">
      <div class="fx-empty-mark" aria-hidden="true"><svg width="30" height="30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><path d="M5 19h14M7 19v-7a5 5 0 0 1 10 0v7M9 8a3 3 0 0 1 6 0"/></svg></div>
      <h3 style="font-size:20px; font-weight:700; color:var(--fx-ink); margin-bottom:8px;">No services available</h3>
      <p>Check back soon or browse our meals.</p>
      <a href="<?= base_url('store/' . ($settings->store_slug ?? '') . '/products'); ?>" class="fx-card-add" style="display:inline-flex; margin-top:16px; gap:8px;">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Browse meals
      </a>
    </div>
    <?php endif; ?>
  </div>
</div>
