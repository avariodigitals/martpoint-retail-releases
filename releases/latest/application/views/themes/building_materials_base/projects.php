<?php
  $bmProjectRows = $projects ?? $bm_projects ?? [];
  $bmProjectsEmpty = empty($bmProjectRows);
?>
<section class="page-heading">
  <p class="eyebrow"><?= htmlspecialchars($bm_values['projects_eyebrow'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
  <?= mp_building_heading($bm_values['projects_title'] ?? '', 'h1'); ?>
  <p><?= nl2br(htmlspecialchars($bm_values['projects_intro'] ?? '', ENT_QUOTES, 'UTF-8')); ?></p>
</section>

<?php if(!$bmProjectsEmpty): ?>
<section class="section pale">
  <div class="project-grid">
    <?php foreach($bmProjectRows as $bmIndex => $bmProject): ?>
    <?php
      $bmMeta = json_decode((string)($bmProject->meta_json ?? ''), true);
      if(!is_array($bmMeta)) $bmMeta = [];
      $bmProjectImage = mp_building_image_url($bmProject->image_url ?? '');
      $bmProjectLink = $bm_insights_url . '/' . rawurlencode((string)($bmProject->slug ?? ''));
      $bmMetaBits = [];
      if(!empty($bmMeta['location'])) $bmMetaBits[] = $bmMeta['location'];
      if(!empty($bmMeta['sector'])) $bmMetaBits[] = $bmMeta['sector'];
      if(!empty($bmMeta['year'])) $bmMetaBits[] = $bmMeta['year'];
    ?>
    <article class="project-card">
      <a class="project-card-media" href="<?= $bmProjectLink; ?>" aria-label="<?= htmlspecialchars($bmProject->title ?? 'Project', ENT_QUOTES, 'UTF-8'); ?>">
        <?php if($bmProjectImage !== ''): ?>
        <img src="<?= htmlspecialchars($bmProjectImage, ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" alt="<?= htmlspecialchars($bmProject->title ?? '', ENT_QUOTES, 'UTF-8'); ?>">
        <?php else: ?>
        <div class="bm-media-fallback" role="img" aria-label="<?= htmlspecialchars($bmProject->title ?? '', ENT_QUOTES, 'UTF-8'); ?>"><span aria-hidden="true"><?= htmlspecialchars(mp_building_mark($bm_brand ?? 'M'), ENT_QUOTES, 'UTF-8'); ?></span></div>
        <?php endif; ?>
      </a>
      <div>
        <span><?= htmlspecialchars($bmMetaBits ? implode(' · ', $bmMetaBits) : 'Project', ENT_QUOTES, 'UTF-8'); ?></span>
        <h2><a href="<?= $bmProjectLink; ?>"><?= htmlspecialchars($bmProject->title ?? '', ENT_QUOTES, 'UTF-8'); ?></a></h2>
        <?php if(trim((string)($bmProject->excerpt ?? '')) !== ''): ?><p><?= nl2br(htmlspecialchars((string)$bmProject->excerpt, ENT_QUOTES, 'UTF-8')); ?></p><?php endif; ?>
        <a class="text-link" href="<?= $bmProjectLink; ?>">Read the project<span aria-hidden="true">↗</span></a>
      </div>
    </article>
    <?php endforeach; ?>
  </div>
</section>
<?php else: ?>
<section class="section pale">
  <div class="notice-panel">
    <div>
      <p class="eyebrow">PROJECTS</p>
      <h2>Project stories are being prepared.</h2>
      <p>In the meantime, explore the systems range or start a conversation about the space you are planning.</p>
    </div>
    <a class="button" href="<?= $bm_products_url; ?>">Explore systems<span aria-hidden="true">↗</span></a>
  </div>
</section>
<?php endif; ?>

<section class="section">
  <div class="notice-panel">
    <div>
      <p class="eyebrow">YOUR PROJECT</p>
      <h2>Document what makes your project unique.</h2>
      <p>Submit a brief, plan or drawing to begin a technical conversation.</p>
    </div>
    <a class="button" href="<?= $bm_contact_url; ?>">Start a project<span aria-hidden="true">↗</span></a>
  </div>
</section>
