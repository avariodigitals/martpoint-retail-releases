<?php
defined('BASEPATH') OR exit('No direct script access allowed');
$feature_label   = $feature_label ?? 'This feature';
$feature_key     = $feature_key ?? '';
$industry_label  = $industry_label ?? '';
$back_url        = $back_url ?? base_url('dashboard');
$enable_url      = $enable_url ?? base_url('business_profile');
$icon            = $icon ?? 'fa-lock';
$description     = $description ?? '';
?>
<!DOCTYPE html>
<html>
<head><?php $this->load->view('comman/code_css.php'); ?></head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar'); ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1><?= htmlspecialchars($feature_label); ?> <small>Not Activated</small></h1>
    <ol class="breadcrumb">
      <li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active"><?= htmlspecialchars($feature_label); ?></li>
    </ol>
  </section>
  <section class="content">
    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <div class="mp-feature-locked-card">
          <div class="mp-feature-locked-icon">
            <i class="fa <?= htmlspecialchars($icon); ?>"></i>
          </div>
          <div class="mp-feature-locked-status">
            <span class="mp-status-pill mp-status-pill--muted">
              <i class="fa fa-circle-o"></i> Not Activated
            </span>
            <?php if(!empty($industry_label)): ?>
            <span class="mp-status-pill mp-status-pill--neutral">
              <i class="fa fa-tag"></i> <?= htmlspecialchars($industry_label); ?>
            </span>
            <?php endif; ?>
          </div>
          <h2 class="mp-feature-locked-title"><?= htmlspecialchars($feature_label); ?> isn&rsquo;t turned on for this store</h2>
          <p class="mp-feature-locked-text">
            <?= !empty($description) ? htmlspecialchars($description) : 'This module is part of your business profile but hasn&rsquo;t been flagged as active for the current store. Enable it in Business Profile settings to start using it.'; ?>
          </p>
          <?php if(!empty($feature_key)): ?>
          <div class="mp-feature-locked-meta">
            <span class="mp-meta-key">Feature flag</span>
            <code class="mp-meta-val"><?= htmlspecialchars($feature_key); ?></code>
          </div>
          <?php endif; ?>
          <div class="mp-feature-locked-actions">
            <a href="<?= htmlspecialchars($enable_url); ?>" class="btn btn-mp-primary">
              <i class="fa fa-toggle-on"></i> Activate in Business Profile
            </a>
            <a href="<?= htmlspecialchars($back_url); ?>" class="btn btn-mp-ghost">
              <i class="fa fa-arrow-left"></i> Back
            </a>
          </div>
          <div class="mp-feature-locked-note">
            <i class="fa fa-info-circle"></i>
            Only store owners and admins can change business profile flags. If you believe this should already be active, contact your store administrator.
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
<?php $this->load->view('footer'); ?>
</div>
<?php $this->load->view('comman/code_js.php'); ?>
</body>
</html>
