<?php
/**
 * Shared person avatar for customers, patients and buyers.
 *
 * Inputs:
 *   $avatar_url   Optional uploaded image path or URL.
 *   $avatar_name  Used for the accessible label and fallback identity.
 *   $avatar_size  Pixel size (default 36).
 *   $avatar_class Optional additional class names.
 */
$mp_avatar_raw_url = trim((string)($avatar_url ?? ''));
$mp_avatar_url = '';
if ($mp_avatar_raw_url !== '') {
    if (preg_match('/^https?:\/\//i', $mp_avatar_raw_url)) {
        $mp_avatar_url = $mp_avatar_raw_url;
    } else {
        $mp_avatar_path = ltrim($mp_avatar_raw_url, '/');
        if ($mp_avatar_path !== '' && defined('FCPATH') && file_exists(FCPATH . $mp_avatar_path)) {
            $mp_avatar_url = base_url($mp_avatar_path);
        }
    }
}
$mp_avatar_name = trim((string)($avatar_name ?? 'Patient')) ?: 'Patient';
$mp_avatar_size = max(24, (int)($avatar_size ?? 36));
$mp_avatar_class = trim((string)($avatar_class ?? ''));
$mp_avatar_label = htmlspecialchars($mp_avatar_name, ENT_QUOTES, 'UTF-8');
$mp_avatar_style = 'width:' . $mp_avatar_size . 'px;height:' . $mp_avatar_size . 'px;'
    . 'border-radius:50%;display:inline-flex;align-items:center;justify-content:center;'
    . 'flex:0 0 ' . $mp_avatar_size . 'px;overflow:hidden;background:#e5f4f1;color:#0f766e;'
    . 'vertical-align:middle;';
?>
<span class="mp-person-avatar <?= htmlspecialchars($mp_avatar_class, ENT_QUOTES, 'UTF-8'); ?>" style="<?= $mp_avatar_style; ?>" role="img" aria-label="<?= $mp_avatar_label; ?>">
  <?php if ($mp_avatar_url !== ''): ?>
    <img src="<?= htmlspecialchars($mp_avatar_url, ENT_QUOTES, 'UTF-8'); ?>" alt="" style="width:100%;height:100%;object-fit:cover;display:block;" onerror="this.style.display='none';this.nextElementSibling.style.display='inline-flex';">
  <?php endif; ?>
  <span class="mp-person-avatar-fallback" aria-hidden="true" style="<?= $mp_avatar_url !== '' ? 'display:none;' : 'display:inline-flex;'; ?>align-items:center;justify-content:center;width:100%;height:100%;">
    <svg viewBox="0 0 24 24" width="<?= max(14, (int)round($mp_avatar_size * .52)); ?>" height="<?= max(14, (int)round($mp_avatar_size * .52)); ?>" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" focusable="false" aria-hidden="true">
      <circle cx="12" cy="8" r="3.25"></circle>
      <path d="M5.5 19.25c.8-3.05 3.05-4.75 6.5-4.75s5.7 1.7 6.5 4.75"></path>
    </svg>
  </span>
</span>
