<?php $this->load->view('reports/desktop/_styles'); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($feature_label); ?></h2>
    <div class="mp-page-sub">Feature not activated for this store</div>
  </div>
</div>

<div class="mp-report-results" style="margin-top:0;">
  <div class="box-body">
    <div class="mp-access-denied">
      <div class="mp-access-denied-icon" style="background:rgba(0,87,255,.08)!important;color:var(--mp-primary)!important;">
        <i class="fa <?= htmlspecialchars($icon); ?>"></i>
      </div>
      <h3><?= htmlspecialchars($feature_label); ?> isn&rsquo;t turned on</h3>
      <p><?= !empty($description) ? htmlspecialchars($description) : "This module is part of your business profile but hasn&rsquo;t been flagged as active for the current store. Enable it in Business Profile settings to start using it."; ?></p>
      <?php if(!empty($feature_key)): ?>
      <div style="margin-bottom:24px;font-size:13px;color:var(--mp-muted);">
        <span style="font-weight:600;">Feature flag:</span> <code><?= htmlspecialchars($feature_key); ?></code>
      </div>
      <?php endif; ?>
      <div class="mp-access-denied-actions">
        <a href="<?= htmlspecialchars($enable_url); ?>" class="mp-btn-primary"><i class="fa fa-toggle-on"></i> Activate in Business Profile</a>
        <a href="<?= htmlspecialchars($back_url); ?>" class="mp-btn-secondary"><i class="fa fa-arrow-left"></i> Back</a>
      </div>
    </div>
  </div>
</div>
