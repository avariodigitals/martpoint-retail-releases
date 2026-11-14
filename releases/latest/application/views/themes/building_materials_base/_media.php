<?php
  $mediaUrl = trim((string)($media_url ?? ''));
  $mediaAlt = trim((string)($media_alt ?? ''));
  $mediaClass = trim((string)($media_class ?? ''));
  $mediaMark = trim((string)($media_mark ?? ''));
  $mediaLoading = (string)($media_loading ?? 'lazy');
  $mediaSizes = trim((string)($media_sizes ?? ''));
?>
<?php if($mediaUrl !== ''): ?>
<img<?= $mediaClass !== '' ? ' class="' . htmlspecialchars($mediaClass, ENT_QUOTES, 'UTF-8') . '"' : ''; ?> src="<?= htmlspecialchars($mediaUrl, ENT_QUOTES, 'UTF-8'); ?>" loading="<?= $mediaLoading === 'eager' ? 'eager' : 'lazy'; ?>" alt="<?= htmlspecialchars($mediaAlt, ENT_QUOTES, 'UTF-8'); ?>"<?= $mediaSizes !== '' ? ' sizes="' . htmlspecialchars($mediaSizes, ENT_QUOTES, 'UTF-8') . '"' : ''; ?>>
<?php else: ?>
<div class="bm-media-fallback<?= $mediaClass !== '' ? ' ' . htmlspecialchars($mediaClass, ENT_QUOTES, 'UTF-8') : ''; ?>" role="img" aria-label="<?= htmlspecialchars($mediaAlt, ENT_QUOTES, 'UTF-8'); ?>">
  <span aria-hidden="true"><?= htmlspecialchars(mp_building_mark($mediaMark !== '' ? $mediaMark : ($bm_brand ?? 'M')), ENT_QUOTES, 'UTF-8'); ?></span>
</div>
<?php endif; ?>
