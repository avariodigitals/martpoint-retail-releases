<?php $this->load->view('reports/desktop/_styles'); ?>

<div class="mp-page-head">
  <div>
    <h2>Access Denied</h2>
    <div class="mp-page-sub">You don't have permission to view this page</div>
  </div>
</div>

<div class="mp-report-results" style="margin-top:0;">
  <div class="box-body">
    <div class="mp-access-denied">
      <div class="mp-access-denied-icon"><i class="fa fa-lock"></i></div>
      <h3>Permission Required</h3>
      <p><?= htmlspecialchars($message ?? "You don't have permission to access this feature. Contact your administrator if you believe this is a mistake."); ?></p>
      <div class="mp-access-denied-actions">
        <a href="<?= base_url('dashboard'); ?>" class="mp-btn-primary"><i class="fa fa-arrow-left"></i> Go to Dashboard</a>
        <a href="javascript:history.back();" class="mp-btn-secondary">Go Back</a>
      </div>
    </div>
  </div>
</div>
