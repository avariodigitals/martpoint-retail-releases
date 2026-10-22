<?php
  $bmService = $service;
  $bmServiceName = $bmService->service_name ?? ($bmService->item_name ?? 'Service');
  $bmServiceImg = mp_building_image_url($bmService->service_image ?? ($bmService->item_image ?? ''));
  $bmServiceCategory = trim((string)($bmService->category_name ?? ''));
  $bmServiceUrl = $bm_contact_url . '?type=service&service_id=' . (int)$bmService->id;
?>
<section class="product-detail service-detail">
  <div class="detail-image">
    <?php if($bmServiceImg !== ''): ?>
    <img src="<?= htmlspecialchars($bmServiceImg, ENT_QUOTES, 'UTF-8'); ?>" loading="eager" alt="<?= htmlspecialchars($bmServiceName, ENT_QUOTES, 'UTF-8'); ?>">
    <?php else: ?>
    <div class="bm-media-fallback" role="img" aria-label="<?= htmlspecialchars($bmServiceName, ENT_QUOTES, 'UTF-8'); ?>"><span aria-hidden="true"><?= htmlspecialchars(mp_building_mark($bm_brand ?? 'M'), ENT_QUOTES, 'UTF-8'); ?></span></div>
    <?php endif; ?>
  </div>
  <div class="detail-copy">
    <p class="eyebrow"><?= htmlspecialchars($bmServiceCategory !== '' ? $bmServiceCategory : 'SERVICE', ENT_QUOTES, 'UTF-8'); ?> / SUPPORT</p>
    <h1><?= htmlspecialchars($bmServiceName, ENT_QUOTES, 'UTF-8'); ?></h1>
    <?php if(trim((string)($bmService->description ?? '')) !== ''): ?>
    <div class="detail-description"><?= nl2br(htmlspecialchars((string)$bmService->description, ENT_QUOTES, 'UTF-8')); ?></div>
    <?php endif; ?>

    <?php if(!empty($bm_show_prices) && isset($bmService->effective_price)): ?>
    <p class="detail-price"><span><?= sf_currency($bmService->effective_price, $store_currency ?? null); ?></span></p>
    <?php endif; ?>

    <div class="spec-list">
      <?php if($bmServiceCategory !== ''): ?><div><span>Category</span><strong><?= htmlspecialchars($bmServiceCategory, ENT_QUOTES, 'UTF-8'); ?></strong></div><?php endif; ?>
      <?php if(!empty($bmService->service_duration)): ?><div><span>Duration</span><strong><?= htmlspecialchars($bmService->service_duration, ENT_QUOTES, 'UTF-8'); ?></strong></div><?php endif; ?>
      <?php if(!empty($bmService->location_type)): ?><div><span>Delivery</span><strong><?= htmlspecialchars(ucfirst(str_replace('-', ' ', (string)$bmService->location_type)), ENT_QUOTES, 'UTF-8'); ?></strong></div><?php endif; ?>
      <div><span>Next step</span><strong>Confirm the site and scope with the team</strong></div>
    </div>

    <a class="button" href="<?= $bmServiceUrl; ?>">Request this service<span aria-hidden="true">↗</span></a>
    <p class="tiny">Service requests are reviewed by the team before any work is scheduled.</p>
  </div>
</section>

<?php if(!empty($related_services)): ?>
<section class="section pale">
  <div class="section-head">
    <div>
      <p class="eyebrow">RELATED SERVICES</p>
      <h2>Continue the conversation.</h2>
    </div>
    <p class="support"></p>
  </div>
  <div class="service-grid">
    <?php foreach($related_services as $bmRelated): ?>
    <?php
      $bmRelatedName = $bmRelated->service_name ?? ($bmRelated->item_name ?? 'Service');
      $bmRelatedUrl = base_url('store/' . ($settings->store_slug ?? '') . '/service/' . (int)$bmRelated->id);
    ?>
    <article class="service-card">
      <div class="service-card-body">
        <p class="eyebrow">SERVICE</p>
        <h3><a href="<?= $bmRelatedUrl; ?>"><?= htmlspecialchars($bmRelatedName, ENT_QUOTES, 'UTF-8'); ?></a></h3>
        <?php if(!empty($bmRelated->service_duration)): ?><div class="service-card-meta"><span><?= htmlspecialchars($bmRelated->service_duration, ENT_QUOTES, 'UTF-8'); ?></span></div><?php endif; ?>
        <a class="text-link" href="<?= $bmRelatedUrl; ?>">View service<span aria-hidden="true">↗</span></a>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
