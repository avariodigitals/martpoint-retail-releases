<?php
  $bmApplicationRows = $applications ?? $bm_applications ?? [];
  $bmApplicationsEmpty = empty($bmApplicationRows);
?>
<section class="page-heading">
  <p class="eyebrow"><?= htmlspecialchars($bm_values['applications_eyebrow'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
  <?= mp_building_heading($bm_values['applications_title'] ?? '', 'h1'); ?>
  <p><?= nl2br(htmlspecialchars($bm_values['applications_intro'] ?? '', ENT_QUOTES, 'UTF-8')); ?></p>
</section>

<?php if(!$bmApplicationsEmpty): ?>
<section class="section application-list">
  <?php foreach($bmApplicationRows as $bmIndex => $bmApplication): ?>
  <?php
    $bmMeta = json_decode((string)($bmApplication->meta_json ?? ''), true);
    if(!is_array($bmMeta)) $bmMeta = [];
    $bmApplicationImage = mp_building_image_url($bmApplication->image_url ?? '');
    $bmPills = [];
    if(!empty($bmMeta['systems'])){
        foreach(explode(',', (string)$bmMeta['systems']) as $bmPill){
            $bmPill = trim($bmPill);
            if($bmPill !== '') $bmPills[] = $bmPill;
        }
    }
    if(!empty($bmMeta['location'])) $bmPills[] = $bmMeta['location'];
    if(!empty($bmMeta['sector'])) $bmPills[] = $bmMeta['sector'];
    $bmApplicationLink = $bm_insights_url . '/' . rawurlencode((string)($bmApplication->slug ?? ''));
  ?>
  <article id="application-<?= (int)$bmApplication->id; ?>" class="application-row">
    <div class="application-media">
      <?php if($bmApplicationImage !== ''): ?>
      <img src="<?= htmlspecialchars($bmApplicationImage, ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" alt="<?= htmlspecialchars($bmApplication->title ?? '', ENT_QUOTES, 'UTF-8'); ?>">
      <?php else: ?>
      <div class="bm-media-fallback" role="img" aria-label="<?= htmlspecialchars($bmApplication->title ?? '', ENT_QUOTES, 'UTF-8'); ?>"><span aria-hidden="true"><?= htmlspecialchars(mp_building_mark($bm_brand ?? 'M'), ENT_QUOTES, 'UTF-8'); ?></span></div>
      <?php endif; ?>
    </div>
    <div class="application-content">
      <p class="eyebrow">APPLICATION / <?= sprintf('%02d', $bmIndex + 1); ?></p>
      <h2><?= htmlspecialchars($bmApplication->title ?? '', ENT_QUOTES, 'UTF-8'); ?></h2>
      <?php if(trim((string)($bmApplication->excerpt ?? '')) !== ''): ?>
      <p><?= nl2br(htmlspecialchars((string)$bmApplication->excerpt, ENT_QUOTES, 'UTF-8')); ?></p>
      <?php endif; ?>
      <?php if($bmPills): ?>
      <div class="pill-row">
        <?php foreach($bmPills as $bmPill): ?><span><?= htmlspecialchars($bmPill, ENT_QUOTES, 'UTF-8'); ?></span><?php endforeach; ?>
      </div>
      <?php endif; ?>
      <div class="application-actions">
        <a class="button" href="<?= $bm_contact_url . '?subject=' . rawurlencode('Application: ' . ($bmApplication->title ?? '')); ?>">Discuss this application<span aria-hidden="true">↗</span></a>
        <a class="text-link" href="<?= $bmApplicationLink; ?>">Read the full entry<span aria-hidden="true">↗</span></a>
      </div>
    </div>
  </article>
  <?php endforeach; ?>
</section>
<?php else: ?>
<section class="section">
  <div class="notice-panel">
    <div>
      <p class="eyebrow">APPLICATIONS</p>
      <h2>Application notes are being prepared.</h2>
      <p>Browse the systems catalogue, or tell the team about the environment you are designing for.</p>
    </div>
    <a class="button" href="<?= $bm_contact_url; ?>">Discuss your application<span aria-hidden="true">↗</span></a>
  </div>
</section>
<?php endif; ?>
