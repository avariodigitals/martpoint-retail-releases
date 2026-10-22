<?php
  $bmPrivacyBody = trim((string)($site_content['privacy_body'] ?? ''));
  $bmPrivacyParagraphs = preg_split('/\r\n\r\n|\r\r|\n\n/', $bmPrivacyBody);
?>
<section class="page-heading article-heading">
  <p class="eyebrow">LEGAL / PRIVACY</p>
  <h1><?= htmlspecialchars($site_content['privacy_heading'] ?? 'Privacy policy', ENT_QUOTES, 'UTF-8'); ?></h1>
  <p>How this store handles the information you provide when you browse, request a quotation or submit a service enquiry.</p>
</section>
<section class="section">
  <div class="article-body">
    <?php foreach($bmPrivacyParagraphs as $bmParagraph): ?>
      <?php if(trim($bmParagraph) !== ''): ?><p><?= nl2br(htmlspecialchars(trim($bmParagraph), ENT_QUOTES, 'UTF-8')); ?></p><?php endif; ?>
    <?php endforeach; ?>
    <div class="article-actions">
      <a class="button" href="<?= $bm_contact_url; ?>">Contact the store<span aria-hidden="true">↗</span></a>
      <a class="text-link" href="<?= $bm_base; ?>">Back to the store<span aria-hidden="true">↗</span></a>
    </div>
  </div>
</section>
