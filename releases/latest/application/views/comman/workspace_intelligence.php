<?php
defined('BASEPATH') OR exit('No direct script access allowed');
if (empty($intel) || !is_array($intel)) return;
?>
<style>
.mp-workspace-intel{margin:18px 0 22px;border:1px solid var(--mp-border,var(--line,#d8e2dc));border-radius:14px;background:var(--mp-surface,#fff);color:var(--mp-ink,var(--ink,#172033));overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.04)}
.mp-workspace-intel-head{display:flex;justify-content:space-between;gap:12px;align-items:center;padding:15px 17px;border-bottom:1px solid var(--mp-border,var(--line,#d8e2dc))}.mp-workspace-intel-head h2{margin:0;font-size:16px;font-weight:800;color:inherit}.mp-workspace-intel-head h2 i{color:#ad6800;margin-right:6px}.mp-workspace-intel-head span{font-size:12px;color:var(--mp-muted,var(--muted,#667085))}.mp-workspace-intel-row{display:flex;gap:11px;align-items:flex-start;padding:13px 17px;border-bottom:1px solid var(--mp-border,var(--line,#d8e2dc))}.mp-workspace-intel-row:last-child{border-bottom:0}.mp-workspace-intel-icon{width:30px;height:30px;flex:0 0 30px;border-radius:9px;display:grid;place-items:center}.mp-workspace-intel-text{min-width:0;font-size:13px;line-height:1.5}.mp-workspace-intel-text a{display:inline-block;margin-top:5px;font-size:12px;font-weight:700;text-decoration:none}.mp-workspace-intel-text a:hover{text-decoration:underline}
</style>
<section class="mp-workspace-intel" aria-label="Intelligence Report">
  <div class="mp-workspace-intel-head"><h2><i class="fa fa-lightbulb-o" aria-hidden="true"></i> Intelligence Report</h2><span><?= count($intel); ?> insight<?= count($intel) === 1 ? '' : 's'; ?></span></div>
  <?php foreach($intel as $item):
    $tone = $item['tone'] ?? 'info';
    $color = $tone === 'warn' ? '#ad6800' : ($tone === 'good' ? '#16805a' : '#0e7490');
    $background = $tone === 'warn' ? '#fffbeb' : ($tone === 'good' ? '#eaf7ef' : '#ecfeff');
  ?>
  <div class="mp-workspace-intel-row">
    <span class="mp-workspace-intel-icon" style="color:<?= $color; ?>;background:<?= $background; ?>;"><i class="fa <?= htmlspecialchars($item['icon'] ?? 'fa-info-circle', ENT_QUOTES, 'UTF-8'); ?>" aria-hidden="true"></i></span>
    <div class="mp-workspace-intel-text"><?= htmlspecialchars($item['text'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
      <?php if(!empty($item['action']['url'])): ?><br><a href="<?= htmlspecialchars($item['action']['url'], ENT_QUOTES, 'UTF-8'); ?>" style="color:<?= $color; ?>;"><?= htmlspecialchars($item['action']['label'] ?? 'Open', ENT_QUOTES, 'UTF-8'); ?> &rarr;</a><?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</section>
