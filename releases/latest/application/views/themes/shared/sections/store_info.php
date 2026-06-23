<?php $desc = trim($settings->store_description ?? ''); ?>
<?php if(!empty($desc)): ?>
<div class="mp-store-info">
  <div class="mp-section mp-store-info-inner">
    <div class="mp-section-title">About Us</div>
    <p style="font-size:16px; line-height:1.8; color:#334155;"><?= nl2br(htmlspecialchars($desc)); ?></p>
  </div>
</div>
<?php endif; ?>
