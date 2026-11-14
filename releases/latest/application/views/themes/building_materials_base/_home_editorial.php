<?php
  // Banners are store-wide and may belong to a previously used industry.
  // Reuse one only when this theme's owner explicitly selects that source.
  $bmHeroBanner = ($bm_values['hero_banner_source'] ?? 'theme') === 'store_banner' && !empty($hero_banners[0]) ? $hero_banners[0] : null;
  $bmHeroImagePath = '';
  if($bmHeroBanner){
      $bmHeroImagePath = (string)($bmHeroBanner->desktop_image ?? '');
      if($bmHeroImagePath === '') $bmHeroImagePath = (string)($bmHeroBanner->mobile_image ?? '');
  }
  if($bmHeroImagePath === '') $bmHeroImagePath = (string)($bm_values['hero_image'] ?? '');
  $bmHeroImageUrl = mp_building_image_url($bmHeroImagePath);
  $bmHeroTitle = trim((string)($bmHeroBanner->banner_title ?? '')) !== '' ? $bmHeroBanner->banner_title : ($bm_values['hero_title'] ?? '');
  $bmHeroSupport = trim((string)($bmHeroBanner->banner_subtitle ?? '')) !== '' ? $bmHeroBanner->banner_subtitle : ($bm_values['hero_support'] ?? '');
  $bmHeroUrl = trim((string)($bmHeroBanner->button_url ?? ''));
  if($bmHeroUrl === '' || preg_match('~^\s*(javascript|data|vbscript):~i', $bmHeroUrl)) $bmHeroUrl = $bm_products_url;
  $bmHeroPrimary = trim((string)($bmHeroBanner->button_text ?? '')) !== '' ? $bmHeroBanner->button_text : ($bm_values['hero_primary_label'] ?? 'Explore systems');
  $bmHeroSecondaryUrl = !empty($site_content['contact_enabled']) ? $bm_contact_url : $bm_services_url;
  $bmSystemProducts = array_slice(!empty($featured_products) ? $featured_products : $all_products, 0, 3);
  $bmSystemTotal = count($bmSystemProducts);
  $bmHeroSideImage = mp_building_image_url($bm_values['hero_side_image'] ?? '');
  $bmHeroSideAlt = $bmHeroSideImage !== '' ? ($bm_values['hero_side_image_alt'] ?? 'Industrial door system') : 'Architectural illustration of an industrial sectional door';
  if($bmHeroSideImage === '') $bmHeroSideImage = base_url('theme/dist/img/building_materials/industrial-door.svg');
?>
<?php if($bmSectionBase === 'hero_banner'): ?>
<section class="home-hero<?= $bmHeroImageUrl === '' ? ' home-hero-plain' : ''; ?>">
  <?php if($bmHeroImageUrl !== ''): ?>
  <div class="hero-media"><img src="<?= htmlspecialchars($bmHeroImageUrl, ENT_QUOTES, 'UTF-8'); ?>" loading="eager" alt=""></div>
  <div class="hero-scrim"></div>
  <?php else: ?>
  <div class="hero-pattern" aria-hidden="true"></div>
  <?php endif; ?>
  <div class="hero-copy">
    <p class="eyebrow inverse"><?= htmlspecialchars($bm_values['hero_eyebrow'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
    <?= mp_building_heading($bmHeroTitle, $bmHeroTag); ?>
    <p><?= nl2br(htmlspecialchars($bmHeroSupport, ENT_QUOTES, 'UTF-8')); ?></p>
    <div class="hero-actions">
      <a class="light-button" href="<?= htmlspecialchars($bmHeroUrl, ENT_QUOTES, 'UTF-8'); ?>"><?= htmlspecialchars($bmHeroPrimary, ENT_QUOTES, 'UTF-8'); ?><span aria-hidden="true">↗</span></a>
      <?php if(!empty($bm_values['hero_secondary_label'])): ?>
      <a class="line-button" href="<?= $bmHeroSecondaryUrl; ?>"><?= htmlspecialchars($bm_values['hero_secondary_label'], ENT_QUOTES, 'UTF-8'); ?><span aria-hidden="true">↗</span></a>
      <?php endif; ?>
    </div>
  </div>
  <figure class="hero-visual">
    <img src="<?= htmlspecialchars($bmHeroSideImage, ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($bmHeroSideAlt, ENT_QUOTES, 'UTF-8'); ?>" width="560" height="500" fetchpriority="high">
  </figure>
</section>
<?php endif; ?>
<?php if($bmSectionBase === 'perspective'): ?>
<section class="section narrow">
  <div class="section-head">
    <div>
      <p class="eyebrow"><?= htmlspecialchars($bm_values['home_perspective_eyebrow'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
      <?= mp_building_heading($bm_values['home_perspective_title'] ?? '', 'h2'); ?>
    </div>
    <p class="support"><?= nl2br(htmlspecialchars($bm_values['home_perspective_support'] ?? '', ENT_QUOTES, 'UTF-8')); ?></p>
  </div>
  <div class="intro-pair">
    <p><?= nl2br(htmlspecialchars($bm_values['home_perspective_statement'] ?? '', ENT_QUOTES, 'UTF-8')); ?></p>
    <div><a class="button" href="<?= $bm_about_url; ?>">Learn how we work<span aria-hidden="true">↗</span></a></div>
  </div>
</section>
<?php endif; ?>
<?php if(in_array($bmSectionBase, ['featured_products','best_sellers','new_arrivals'], true)): ?>
<section class="section pale">
  <div class="section-head">
    <div>
      <p class="eyebrow"><?= htmlspecialchars($bm_values['home_systems_eyebrow'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
      <h2><?= htmlspecialchars($bm_values['home_systems_title'] ?? 'Performance by design.', ENT_QUOTES, 'UTF-8'); ?></h2>
    </div>
    <p class="support"><?= nl2br(htmlspecialchars($bm_values['home_systems_support'] ?? '', ENT_QUOTES, 'UTF-8')); ?></p>
  </div>
  <?php if($bmSystemProducts): ?>
  <div class="card-grid">
    <?php foreach($bmSystemProducts as $bmIndex => $bmProduct): ?>
      <?php
        $card_product = $bmProduct;
        $card_index = $bmIndex;
        $card_total = $bmSystemTotal;
        $card_eager = $bmIndex === 0;
        $this->load->view('themes/building_materials_base/_product_card', get_defined_vars());
      ?>
    <?php endforeach; ?>
  </div>
  <div class="section-action"><a class="button" href="<?= $bm_products_url; ?>">View all systems<span aria-hidden="true">↗</span></a></div>
  <?php else: ?>
  <div class="notice-panel notice-panel-inline">
    <div><h2>Systems are being prepared.</h2><p>Browse the full catalogue or speak with the team about the opening you are planning.</p></div>
    <a class="button" href="<?= $bm_products_url; ?>">Browse the catalogue<span aria-hidden="true">↗</span></a>
  </div>
  <?php endif; ?>
</section>
<?php endif; ?>
<?php if($bmSectionBase === 'store_info'): ?>
<section class="split-feature">
  <div>
    <?php
      $media_url = mp_building_image_url($bm_values['home_feature_image'] ?? '');
      $media_alt = 'Project delivery and technical support';
      $media_class = '';
      $media_mark = $bm_brand ?? 'M';
      $this->load->view('themes/building_materials_base/_media', get_defined_vars());
    ?>
  </div>
  <div class="feature-copy">
    <p class="eyebrow"><?= htmlspecialchars($bm_values['home_feature_eyebrow'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
    <?= mp_building_heading($bm_values['home_feature_title'] ?? '', 'h2'); ?>
    <p><?= nl2br(htmlspecialchars($bm_values['home_feature_body'] ?? '', ENT_QUOTES, 'UTF-8')); ?></p>
    <div class="number-stack">
      <?php for($bmStep = 1; $bmStep <= 3; $bmStep++): ?>
      <div><span><?= sprintf('%02d', $bmStep); ?></span><?= htmlspecialchars($bm_values['process_' . $bmStep . '_title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endfor; ?>
    </div>
    <a class="button" href="<?= $bm_services_url; ?>">Explore our services<span aria-hidden="true">↗</span></a>
  </div>
</section>

<?php if(!empty($bm_applications)): ?>
<section class="section">
  <div class="section-head">
    <div>
      <p class="eyebrow"><?= htmlspecialchars($bm_values['home_applications_eyebrow'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
      <h2><?= htmlspecialchars($bm_values['home_applications_title'] ?? 'Built for the place.', ENT_QUOTES, 'UTF-8'); ?></h2>
    </div>
    <p class="support"><?= nl2br(htmlspecialchars($bm_values['home_applications_support'] ?? '', ENT_QUOTES, 'UTF-8')); ?></p>
  </div>
  <div class="tiles<?= count($bm_applications) === 1 ? ' tiles-single' : ''; ?>">
    <?php foreach(array_slice($bm_applications, 0, 2) as $bmApplication): ?>
    <?php $bmApplicationImage = mp_building_image_url($bmApplication->image_url ?? ''); ?>
    <a href="<?= $bm_applications_url . '#application-' . (int)$bmApplication->id; ?>" class="tile">
      <?php if($bmApplicationImage !== ''): ?>
      <img src="<?= htmlspecialchars($bmApplicationImage, ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" alt="<?= htmlspecialchars($bmApplication->title ?? '', ENT_QUOTES, 'UTF-8'); ?>">
      <?php else: ?>
      <div class="bm-media-fallback" role="img" aria-label="<?= htmlspecialchars($bmApplication->title ?? '', ENT_QUOTES, 'UTF-8'); ?>"><span aria-hidden="true"><?= htmlspecialchars(mp_building_mark($bm_brand ?? 'M'), ENT_QUOTES, 'UTF-8'); ?></span></div>
      <?php endif; ?>
      <span><?= htmlspecialchars($bmApplication->title ?? '', ENT_QUOTES, 'UTF-8'); ?> <span aria-hidden="true">↗</span></span>
    </a>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>
<?php endif; ?>
<?php if($bmSectionBase === 'contact_section' && !empty($site_content['contact_enabled'])): ?>
<section class="section dark-call">
  <div>
  <p class="eyebrow inverse"><?= htmlspecialchars($bm_values['home_call_eyebrow'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
  <?= mp_building_heading($bm_values['home_call_title'] ?? '', 'h2'); ?>
  <p><?= nl2br(htmlspecialchars($bm_values['home_call_body'] ?? '', ENT_QUOTES, 'UTF-8')); ?></p>
  </div>
  <a class="light-button" href="<?= $bm_contact_url; ?>"><?= htmlspecialchars($bm_values['home_call_cta'] ?? 'Submit an enquiry', ENT_QUOTES, 'UTF-8'); ?><span aria-hidden="true">↗</span></a>
</section>
<?php endif; ?>
