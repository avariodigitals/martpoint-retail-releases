<?php $bh = $business_hours ?? []; ?>
<?php if(!empty($bh)): ?>
<div class="mp-section">
  <div class="mp-section-title">Store Hours</div>
  <div class="mp-hours-list">
    <?php foreach($bh as $line): ?>
    <div class="mp-hours-row"><?= htmlspecialchars($line); ?></div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>
