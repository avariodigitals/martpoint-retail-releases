<?php
require_once APPPATH . 'views/themes/lumiere/_helpers.php';
$lmSlug = $settings->store_slug ?? '';
$lmHome = base_url('store/' . $lmSlug);
$lmName = $service->service_name ?? $service->item_name ?? 'Treatment';
$lmImagePath = $service->service_image ?? $service->item_image ?? '';
$lmImage = lm_item_image($lmImagePath, 1000);
$lmPrice = (float) ($service->effective_price ?? $service->price ?? 0);
?>
<main class="lm-inner"><div class="wrap">
  <div class="lm-breadcrumb"><a href="<?= lm_escape($lmHome); ?>">Home</a><span>›</span><a href="<?= lm_escape($lmHome . '/services'); ?>">Our Services</a><span>›</span><?= lm_escape($lmName); ?></div>
  <div class="lm-detail"><div class="lm-detail-image"><?php if ($lmImage): ?><img src="<?= lm_escape($lmImage); ?>" alt="<?= lm_escape($lmName); ?>"><?php else: ?><span class="lm-image-placeholder">LUMIÈRE</span><?php endif; ?></div>
    <div class="lm-detail-copy"><div class="eyebrow">BEYOND THE EVERYDAY ROUTINE</div><h1><?= lm_escape($lmName); ?></h1><div class="lm-detail-price">From <?= sf_currency($lmPrice, $store_currency ?? null); ?></div><p class="lm-detail-description"><?= nl2br(lm_escape($service->description ?? '')); ?></p>
      <div class="lm-service-meta"><?php if (!empty($service->service_duration)): ?><span><?= lm_escape($service->service_duration); ?></span><?php endif; ?><?php if (!empty($service->location_type)): ?><span><?= lm_escape(ucfirst(str_replace('-', ' ', $service->location_type))); ?></span><?php endif; ?></div>
      <a class="btn" href="<?= lm_escape($lmHome . '/appointment/' . (int)$service->id); ?>">Request an Appointment ↗</a><p class="lm-detail-note">Choose your preferred time. We will confirm it with you.</p>
    </div></div>
  <?php if (!empty($related_services)): ?><section class="section lm-related"><div class="heading"><div><div class="eyebrow">MORE WAYS TO FEEL GOOD</div><h2>Explore treatments.</h2></div><a class="text-link" href="<?= lm_escape($lmHome . '/services'); ?>">All Treatments ↗</a></div><div class="services-grid"><?php foreach (array_slice($related_services, 0, 3) as $lmService): if ((int)$lmService->id !== (int)$service->id) lm_service_card($lmService, $lmSlug, $store_currency ?? null); endforeach; ?></div></section><?php endif; ?>
</div></main>
