<?php
/**
 * Intelligence Report card — shared by the retail and printing dashboards.
 * =========================================================================
 * Renders whatever `Intelligence_model` produced for this store. The model
 * already guarantees each item has tone/icon/text; this view only decides
 * presentation.
 *
 * Expects:  $intel          array of ['tone','icon','text','action'?]
 * Optional: $intel_title    heading text (defaults to "Intelligence Report")
 *           $intel_subtitle short line under the heading
 *
 * The card is skipped entirely when there is nothing to say. An empty
 * "Intelligence" panel trains the owner to ignore it, which defeats the point
 * of the whole feature — so silence is preferred to a placeholder.
 */
if (empty($intel) || !is_array($intel)) {
    return;
}

$intel_title    = $intel_title    ?? 'Intelligence Report';
$intel_subtitle = $intel_subtitle ?? 'Drawn from your own records — what needs attention, and what is trending.';

// Tone → colour. Kept to three states so the panel reads at a glance rather
// than becoming a paint chart; 'info' is deliberately neutral because most
// insights are observations, not alarms.
$intel_tone = function ($tone) {
    switch ($tone) {
        case 'good': return ['#15803D', '#F0FDF4'];
        case 'warn': return ['#B45309', '#FFFBEB'];
        default:     return ['#0E7490', '#ECFEFF'];
    }
};
?>
<div class="mp-section">
  <div class="mp-card">
    <div class="mp-card-head">
      <h3><i class="fa fa-lightbulb-o" style="color:#B45309;"></i> <?= htmlspecialchars($intel_title); ?></h3>
      <span class="mp-card-link" style="cursor:default;"><?= count($intel); ?> insight<?= count($intel) === 1 ? '' : 's'; ?></span>
    </div>
    <div class="mp-card-body" style="padding:4px 0 0;">
      <?php if (!empty($intel_subtitle)): ?>
      <div style="padding:0 20px 10px;font-size:12.5px;color:#64748B;line-height:1.5;"><?= htmlspecialchars($intel_subtitle); ?></div>
      <?php endif; ?>
      <?php foreach ($intel as $item):
        [$fg, $bg] = $intel_tone($item['tone'] ?? 'info');
        $action = $item['action'] ?? null;
      ?>
      <div style="display:flex;gap:12px;align-items:flex-start;padding:12px 20px;border-top:1px solid #F1F5F9;">
        <div style="flex:0 0 30px;height:30px;border-radius:8px;background:<?= $bg ?>;color:<?= $fg ?>;display:flex;align-items:center;justify-content:center;">
          <i class="fa <?= htmlspecialchars($item['icon'] ?? 'fa-info-circle'); ?>" style="font-size:13px;"></i>
        </div>
        <div style="flex:1;min-width:0;">
          <div style="font-size:13.5px;line-height:1.55;color:#0F172A;"><?= htmlspecialchars($item['text'] ?? ''); ?></div>
          <?php if (!empty($action['url'])): ?>
          <a href="<?= htmlspecialchars($action['url']); ?>" style="display:inline-block;margin-top:5px;font-size:12px;font-weight:600;color:<?= $fg ?>;">
            <?= htmlspecialchars($action['label'] ?? 'Open'); ?> &rarr;
          </a>
          <?php endif; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
