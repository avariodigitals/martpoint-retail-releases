<?php
  $bmAboutImage = mp_building_image_url($bm_values['about_image'] ?? '');
  $bmAboutStory = trim((string)($bm_values['about_body'] ?? ''));
  // Shared retail descriptions may belong to an earlier industry. Keep the
  // public story theme-scoped, just like its headings and photography.
  if($bmAboutStory === '') $bmAboutStory = trim((string)($bm_values['home_feature_body'] ?? ''));
?>
<section class="interior-hero">
  <?php if($bmAboutImage !== ''): ?>
  <img src="<?= htmlspecialchars($bmAboutImage, ENT_QUOTES, 'UTF-8'); ?>" loading="eager" alt="">
  <?php else: ?>
  <div class="hero-pattern" aria-hidden="true"></div>
  <?php endif; ?>
  <div class="interior-copy">
    <p class="eyebrow inverse"><?= htmlspecialchars($bm_values['about_eyebrow'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
    <?= mp_building_heading($bm_values['about_title'] ?? '', 'h1'); ?>
    <p><?= nl2br(htmlspecialchars($bm_values['about_intro'] ?? '', ENT_QUOTES, 'UTF-8')); ?></p>
  </div>
</section>

<section class="section">
  <div class="section-head">
    <div>
      <p class="eyebrow">OUR APPROACH</p>
      <h2>Clarity at every stage.</h2>
    </div>
    <p class="support">Technical products should be useful to engineers and reassuring to decision-makers.</p>
  </div>
  <div class="intro-pair">
    <p><?= nl2br(htmlspecialchars($bmAboutStory, ENT_QUOTES, 'UTF-8')); ?></p>
    <div><a class="button" href="<?= $bm_contact_url; ?>">Talk to the team<span aria-hidden="true">↗</span></a></div>
  </div>
</section>

<section class="section pale">
  <div class="section-head">
    <div>
      <p class="eyebrow">HOW WE WORK</p>
      <h2>One informed conversation.</h2>
    </div>
    <p class="support">The same project context stays with the work from the first survey through handover.</p>
  </div>
  <div class="three-columns">
    <?php for($bmPrinciple = 1; $bmPrinciple <= 3; $bmPrinciple++): ?>
    <div>
      <span><?= sprintf('%02d', $bmPrinciple); ?></span>
      <h3><?= htmlspecialchars($bm_values['process_' . $bmPrinciple . '_title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></h3>
      <p><?= nl2br(htmlspecialchars($bm_values['process_' . $bmPrinciple . '_body'] ?? '', ENT_QUOTES, 'UTF-8')); ?></p>
    </div>
    <?php endfor; ?>
  </div>
</section>

<section class="split-feature">
  <div>
    <?php
      $media_url = mp_building_image_url($bm_values['home_feature_image'] ?? '');
      $media_alt = 'Technical guidance and project delivery';
      $media_class = '';
      $media_mark = $bm_brand ?? 'M';
      $this->load->view('themes/building_materials_base/_media', get_defined_vars());
    ?>
  </div>
  <div class="feature-copy">
    <p class="eyebrow">THROUGHOUT THE LIFECYCLE</p>
    <?= mp_building_heading($bm_values['home_feature_title'] ?? '', 'h2'); ?>
    <p><?= nl2br(htmlspecialchars($bm_values['home_feature_body'] ?? '', ENT_QUOTES, 'UTF-8')); ?></p>
    <div class="number-stack">
      <?php for($bmStep = 3; $bmStep <= 5; $bmStep++): ?>
      <div><span><?= sprintf('%02d', $bmStep); ?></span><?= htmlspecialchars($bm_values['process_' . $bmStep . '_title'] ?? '', ENT_QUOTES, 'UTF-8'); ?></div>
      <?php endfor; ?>
    </div>
    <a class="button" href="<?= $bm_services_url; ?>">Explore our services<span aria-hidden="true">↗</span></a>
  </div>
</section>
