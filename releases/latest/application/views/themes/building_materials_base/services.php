<?php
  $bmServiceRows = $services ?? [];
  $bmServiceTotal = (int)($total ?? count($bmServiceRows));
  $bmServicePage = max(1, (int)($page ?? 1));
  $bmServicePages = max(1, (int)ceil($bmServiceTotal / max(1, (int)($limit ?? 24))));
  $bmServiceSearch = trim((string)($search ?? ''));
  $bmServiceImage = mp_building_image_url($bm_values['services_image'] ?? '');
  if($bmServiceImage === '' && !empty($bmServiceRows[0]->service_image)) $bmServiceImage = mp_building_image_url($bmServiceRows[0]->service_image);
  $bmServicePaginate = function($targetPage) use ($bmServiceSearch, $bm_services_url){
      $query = [];
      if($bmServiceSearch !== '') $query['search'] = $bmServiceSearch;
      if($targetPage > 1) $query['page'] = $targetPage;
      return $bm_services_url . ($query ? '?' . http_build_query($query) : '');
  };
?>
<section class="interior-hero">
  <?php if($bmServiceImage !== ''): ?>
  <img src="<?= htmlspecialchars($bmServiceImage, ENT_QUOTES, 'UTF-8'); ?>" loading="eager" alt="">
  <?php else: ?>
  <div class="hero-pattern" aria-hidden="true"></div>
  <?php endif; ?>
  <div class="interior-copy">
    <p class="eyebrow inverse"><?= htmlspecialchars($bm_values['services_eyebrow'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
    <?= mp_building_heading($bm_values['services_title'] ?? '', 'h1'); ?>
    <p><?= nl2br(htmlspecialchars($bm_values['services_intro'] ?? '', ENT_QUOTES, 'UTF-8')); ?></p>
  </div>
</section>

<section class="section">
  <div class="section-head">
    <div>
      <p class="eyebrow">HOW WE WORK</p>
      <h2>One continuous process.</h2>
    </div>
    <p class="support">The same project context stays connected from the first enquiry through service history.</p>
  </div>
  <div class="process">
    <?php for($bmStep = 1; $bmStep <= 5; $bmStep++): ?>
    <div>
      <span><?= sprintf('%02d', $bmStep); ?></span>
      <h3><?= htmlspecialchars($bm_values['process_' . $bmStep . '_title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h3>
      <p><?= nl2br(htmlspecialchars($bm_values['process_' . $bmStep . '_body'] ?? '', ENT_QUOTES, 'UTF-8')); ?></p>
    </div>
    <?php endfor; ?>
  </div>
</section>

<section class="section pale">
  <div class="section-head">
    <div>
      <p class="eyebrow">SERVICES</p>
      <h2>Support shaped around the site.</h2>
    </div>
    <form class="search-form search-form-inline" method="get" action="<?= $bm_services_url; ?>" role="search">
      <label class="sr-only" for="bm-service-search">Search services</label>
      <div class="search-controls">
        <input id="bm-service-search" type="search" name="search" value="<?= htmlspecialchars($bmServiceSearch, ENT_QUOTES, 'UTF-8'); ?>" placeholder="Search services" maxlength="120">
        <button class="button" type="submit">Search<span aria-hidden="true">↗</span></button>
      </div>
    </form>
  </div>

  <?php if($bmServiceRows): ?>
  <div class="service-grid">
    <?php foreach($bmServiceRows as $bmIndex => $bmService): ?>
    <?php
      $bmServiceImg = mp_building_image_url($bmService->service_image ?? ($bmService->item_image ?? ''));
      $bmServiceName = $bmService->service_name ?? ($bmService->item_name ?? 'Service');
      $bmServiceUrl = base_url('store/' . ($settings->store_slug ?? '') . '/service/' . (int)$bmService->id);
    ?>
    <article class="service-card">
      <a class="service-card-media" href="<?= $bmServiceUrl; ?>" aria-label="<?= htmlspecialchars($bmServiceName, ENT_QUOTES, 'UTF-8'); ?>">
        <?php if($bmServiceImg !== ''): ?>
        <img src="<?= htmlspecialchars($bmServiceImg, ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" alt="<?= htmlspecialchars($bmServiceName, ENT_QUOTES, 'UTF-8'); ?>">
        <?php else: ?>
        <div class="bm-media-fallback" role="img" aria-label="<?= htmlspecialchars($bmServiceName, ENT_QUOTES, 'UTF-8'); ?>"><span aria-hidden="true"><?= htmlspecialchars(mp_building_mark($bm_brand ?? 'M'), ENT_QUOTES, 'UTF-8'); ?></span></div>
        <?php endif; ?>
      </a>
      <div class="service-card-body">
        <p class="eyebrow">SERVICE / <?= sprintf('%02d', $bmIndex + 1); ?></p>
        <h3><a href="<?= $bmServiceUrl; ?>"><?= htmlspecialchars($bmServiceName, ENT_QUOTES, 'UTF-8'); ?></a></h3>
        <?php if(trim((string)($bmService->description ?? '')) !== ''): ?><p><?= htmlspecialchars(mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags((string)$bmService->description))), 0, 180, 'UTF-8'), ENT_QUOTES, 'UTF-8'); ?><?= mb_strlen(strip_tags((string)$bmService->description), 'UTF-8') > 180 ? '…' : ''; ?></p><?php endif; ?>
        <div class="service-card-meta">
          <?php if(!empty($bmService->service_duration)): ?><span><?= htmlspecialchars($bmService->service_duration, ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
          <?php if(!empty($bmService->location_type)): ?><span><?= htmlspecialchars(ucfirst(str_replace('-', ' ', (string)$bmService->location_type)), ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
          <?php if(!empty($bm_show_prices) && isset($bmService->effective_price)): ?><span><?= sf_currency($bmService->effective_price, $store_currency ?? null); ?></span><?php endif; ?>
        </div>
        <a class="text-link" href="<?= $bmServiceUrl; ?>">View service<span aria-hidden="true">↗</span></a>
      </div>
    </article>
    <?php endforeach; ?>
  </div>

  <?php if($bmServicePages > 1): ?>
  <nav class="pagination" aria-label="Service pages">
    <?php if($bmServicePage > 1): ?><a href="<?= $bmServicePaginate($bmServicePage - 1); ?>" rel="prev" aria-label="Previous page">←</a><?php endif; ?>
    <?php for($bmPager = max(1, $bmServicePage - 2); $bmPager <= min($bmServicePages, $bmServicePage + 2); $bmPager++): ?>
      <?php if($bmPager === $bmServicePage): ?><span aria-current="page"><?= $bmPager; ?></span><?php else: ?><a href="<?= $bmServicePaginate($bmPager); ?>"><?= $bmPager; ?></a><?php endif; ?>
    <?php endfor; ?>
    <?php if($bmServicePage < $bmServicePages): ?><a href="<?= $bmServicePaginate($bmServicePage + 1); ?>" rel="next" aria-label="Next page">→</a><?php endif; ?>
  </nav>
  <?php endif; ?>

  <?php else: ?>
  <div class="notice-panel notice-panel-inline">
    <div>
      <h2>No services are published yet.</h2>
      <p>Contact the team to discuss surveys, installation, maintenance or a service visit.</p>
    </div>
    <a class="button" href="<?= $bm_contact_url . '?type=service'; ?>">Request service<span aria-hidden="true">↗</span></a>
  </div>
  <?php endif; ?>
</section>

<section class="section">
  <div class="service-banner">
    <?php
      $media_url = $bmServiceImage;
      $media_alt = 'Service and maintenance support';
      $media_class = '';
      $media_mark = $bm_brand ?? 'M';
      $this->load->view('themes/building_materials_base/_media', get_defined_vars());
    ?>
    <div>
      <h2><?= htmlspecialchars($bm_values['service_banner_title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h2>
      <p><?= nl2br(htmlspecialchars($bm_values['service_banner_body'] ?? '', ENT_QUOTES, 'UTF-8')); ?></p>
      <a class="button" href="<?= $bm_contact_url . '?type=service'; ?>">Request service<span aria-hidden="true">↗</span></a>
    </div>
  </div>
</section>
