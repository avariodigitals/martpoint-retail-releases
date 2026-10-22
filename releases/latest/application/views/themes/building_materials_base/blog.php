<?php
  $bmPost = $journal_post ?? null;
  $bmPostKind = $bmPost ? (string)($bmPost->kind ?? 'insight') : 'insight';
  $bmKindLabels = ['insight'=>'Insights','project'=>'Project','application'=>'Application'];
  $bmKindLabel = $bmKindLabels[$bmPostKind] ?? 'Insights';
  $bmKindBack = $bmPostKind === 'project' ? $bm_projects_url : ($bmPostKind === 'application' ? $bm_applications_url : $bm_insights_url);
  $bmKindBackLabel = $bmPostKind === 'project' ? 'All projects' : ($bmPostKind === 'application' ? 'All applications' : 'All insights');
?>
<?php if($bmPost): ?>
  <?php
    $bmMeta = json_decode((string)($bmPost->meta_json ?? ''), true);
    if(!is_array($bmMeta)) $bmMeta = [];
    $bmPostImage = mp_building_image_url($bmPost->image_url ?? '');
    $bmMetaBits = [];
    if(!empty($bmMeta['location'])) $bmMetaBits[] = $bmMeta['location'];
    if(!empty($bmMeta['sector'])) $bmMetaBits[] = $bmMeta['sector'];
    if(!empty($bmMeta['year'])) $bmMetaBits[] = $bmMeta['year'];
    $bmParagraphs = preg_split('/\r\n\r\n|\r\r|\n\n/', trim((string)($bmPost->body ?? '')));
  ?>
  <article class="article">
    <section class="page-heading article-heading">
      <p class="eyebrow"><?= htmlspecialchars(strtoupper($bmKindLabel), ENT_QUOTES, 'UTF-8'); ?> / <?= htmlspecialchars(date('Y', strtotime((string)($bmPost->published_at ?? 'now'))), ENT_QUOTES, 'UTF-8'); ?></p>
      <h1><?= htmlspecialchars($bmPost->title ?? '', ENT_QUOTES, 'UTF-8'); ?></h1>
      <?php if(trim((string)($bmPost->excerpt ?? '')) !== ''): ?><p><?= nl2br(htmlspecialchars((string)$bmPost->excerpt, ENT_QUOTES, 'UTF-8')); ?></p><?php endif; ?>
      <div class="article-meta">
        <span><?= htmlspecialchars(date('j F Y', strtotime((string)($bmPost->published_at ?? 'now'))), ENT_QUOTES, 'UTF-8'); ?></span>
        <?php if($bmMetaBits): ?><span><?= htmlspecialchars(implode(' · ', $bmMetaBits), ENT_QUOTES, 'UTF-8'); ?></span><?php endif; ?>
      </div>
    </section>

    <?php if($bmPostImage !== ''): ?>
    <div class="article-hero"><img src="<?= htmlspecialchars($bmPostImage, ENT_QUOTES, 'UTF-8'); ?>" loading="eager" alt="<?= htmlspecialchars($bmPost->title ?? '', ENT_QUOTES, 'UTF-8'); ?>"></div>
    <?php endif; ?>

    <div class="article-body">
      <?php foreach($bmParagraphs as $bmParagraph): ?>
        <?php if(trim($bmParagraph) !== ''): ?><p><?= nl2br(htmlspecialchars(trim($bmParagraph), ENT_QUOTES, 'UTF-8')); ?></p><?php endif; ?>
      <?php endforeach; ?>
      <?php if(!empty($bmMeta['systems'])):
        $bmSystems = array_filter(array_map('trim', explode(',', (string)$bmMeta['systems'])));
      ?>
      <div class="pill-row article-pills">
        <?php foreach($bmSystems as $bmSystem): ?><span><?= htmlspecialchars($bmSystem, ENT_QUOTES, 'UTF-8'); ?></span><?php endforeach; ?>
      </div>
      <?php endif; ?>
      <div class="article-actions">
        <a class="button" href="<?= $bm_contact_url . '?subject=' . rawurlencode($bmKindLabel . ': ' . ($bmPost->title ?? '')); ?>">Discuss a similar project<span aria-hidden="true">↗</span></a>
        <a class="text-link" href="<?= $bmKindBack; ?>"><?= htmlspecialchars($bmKindBackLabel, ENT_QUOTES, 'UTF-8'); ?><span aria-hidden="true">↗</span></a>
      </div>
    </div>
  </article>

<?php else: ?>
  <?php
    $bmPosts = [];
    foreach(($journal_posts ?? []) as $bmCandidate){
        $bmCandidateKind = (string)($bmCandidate->kind ?? 'insight');
        if($bmCandidateKind === '' || $bmCandidateKind === 'insight') $bmPosts[] = $bmCandidate;
    }
    $bmHasMore = count($bmPosts) > 12;
    $bmPosts = array_slice($bmPosts, 0, 12);
    $bmJournalPage = max(1, (int)($journal_page ?? 1));
  ?>
  <section class="page-heading">
    <p class="eyebrow"><?= htmlspecialchars($bm_values['insights_eyebrow'] ?? '', ENT_QUOTES, 'UTF-8'); ?></p>
    <?= mp_building_heading($bm_values['insights_title'] ?? '', 'h1'); ?>
    <p><?= nl2br(htmlspecialchars($bm_values['insights_intro'] ?? '', ENT_QUOTES, 'UTF-8')); ?></p>
  </section>

  <section class="section pale">
    <?php if($bmPosts): ?>
    <div class="insight-grid">
      <?php foreach($bmPosts as $bmIndex => $bmInsight): ?>
      <?php
        $bmInsightImage = mp_building_image_url($bmInsight->image_url ?? '');
        $bmInsightUrl = $bm_insights_url . '/' . rawurlencode((string)($bmInsight->slug ?? ''));
      ?>
      <article class="insight">
        <a href="<?= $bmInsightUrl; ?>" class="insight-media" aria-label="<?= htmlspecialchars($bmInsight->title ?? 'Article', ENT_QUOTES, 'UTF-8'); ?>">
          <?php if($bmInsightImage !== ''): ?>
          <img src="<?= htmlspecialchars($bmInsightImage, ENT_QUOTES, 'UTF-8'); ?>" loading="lazy" alt="<?= htmlspecialchars($bmInsight->title ?? '', ENT_QUOTES, 'UTF-8'); ?>">
          <?php else: ?>
          <div class="bm-media-fallback" role="img" aria-label="<?= htmlspecialchars($bmInsight->title ?? '', ENT_QUOTES, 'UTF-8'); ?>"><span aria-hidden="true"><?= htmlspecialchars(mp_building_mark($bm_brand ?? 'M'), ENT_QUOTES, 'UTF-8'); ?></span></div>
          <?php endif; ?>
        </a>
        <div>
          <p class="eyebrow">GUIDE / <?= sprintf('%02d', (($bmJournalPage - 1) * 12) + $bmIndex + 1); ?></p>
          <h2><a href="<?= $bmInsightUrl; ?>"><?= htmlspecialchars($bmInsight->title ?? '', ENT_QUOTES, 'UTF-8'); ?></a></h2>
          <?php if(trim((string)($bmInsight->excerpt ?? '')) !== ''): ?><p><?= nl2br(htmlspecialchars((string)$bmInsight->excerpt, ENT_QUOTES, 'UTF-8')); ?></p><?php endif; ?>
          <a class="text-link" href="<?= $bmInsightUrl; ?>">Read the article<span aria-hidden="true">↗</span></a>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

    <?php if($bmJournalPage > 1 || $bmHasMore): ?>
    <nav class="pagination" aria-label="Insight pages">
      <?php if($bmJournalPage > 1): ?><a href="<?= $bm_insights_url . '?page=' . ($bmJournalPage - 1); ?>" rel="prev">← Previous</a><?php endif; ?>
      <span>Page <?= $bmJournalPage; ?></span>
      <?php if($bmHasMore): ?><a href="<?= $bm_insights_url . '?page=' . ($bmJournalPage + 1); ?>" rel="next">Next →</a><?php endif; ?>
    </nav>
    <?php endif; ?>

    <?php else: ?>
    <div class="notice-panel notice-panel-inline">
      <div>
        <p class="eyebrow">INSIGHTS</p>
        <h2>New guidance is on the way.</h2>
        <p>Speak with the team about specification, site conditions or planning a technical quotation.</p>
      </div>
      <a class="button" href="<?= $bm_contact_url; ?>">Ask a technical question<span aria-hidden="true">↗</span></a>
    </div>
    <?php endif; ?>
  </section>

  <section class="section">
    <div class="notice-panel">
      <div>
        <p class="eyebrow">RESOURCE LIBRARY</p>
        <h2>Need a specific technical document?</h2>
        <p>Share the product family or application you are evaluating.</p>
      </div>
      <a class="button" href="<?= $bm_contact_url; ?>">Request documentation<span aria-hidden="true">↗</span></a>
    </div>
  </section>
<?php endif; ?>
