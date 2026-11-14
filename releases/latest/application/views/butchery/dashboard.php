<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $has_butchery = mp_feature_enabled('meat_butchery_workflow'); ?>
<div class="mp-page-head">
  <div>
    <h2><?= $has_butchery ? 'Butchery &amp; Frozen Foods' : 'Cold Chain'; ?></h2>
    <div class="mp-page-sub"><?= $has_butchery ? 'Carcass receiving, cutting, and share management' : 'Freezer locations and temperature monitoring'; ?></div>
  </div>
</div>

<?php if ($has_butchery): ?>
<div class="mp-form-grid" style="grid-template-columns: repeat(3, 1fr); gap: 20px; margin: 24px 0;">
  <div class="mp-card-form" style="text-align:center;">
    <div class="mp-card-body">
      <h1 style="margin:0; color:var(--mp-primary);"><?= (int) $received_count; ?></h1>
      <p class="mp-muted">Carcasses Received</p>
    </div>
  </div>
  <div class="mp-card-form" style="text-align:center;">
    <div class="mp-card-body">
      <h1 style="margin:0; color:var(--mp-primary);"><?= (int) $completed_count; ?></h1>
      <p class="mp-muted">Completed Cuts</p>
    </div>
  </div>
  <div class="mp-card-form" style="text-align:center;">
    <div class="mp-card-body">
      <h1 style="margin:0; color:var(--mp-primary);"><?= (int) $shares_count; ?></h1>
      <p class="mp-muted">Active Shares</p>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="mp-form-grid" style="grid-template-columns: repeat(<?= $has_butchery ? 3 : 2; ?>, 1fr); gap: 20px;<?= $has_butchery ? '' : ' margin: 24px 0;'; ?>">
  <?php if ($has_butchery): ?>
  <a href="<?= base_url('butchery/receive'); ?>" class="mp-card-form" style="text-decoration:none;">
    <div class="mp-card-body" style="text-align:center;">
      <i class="fa fa-plus-circle" style="font-size:32px; color:var(--mp-primary); display:block; margin-bottom:12px;"></i>
      <h4>Receive Carcass</h4>
      <p class="mp-muted">Record a new carcass and assign a lot.</p>
    </div>
  </a>
  <a href="<?= base_url('butchery/receive'); ?>" class="mp-card-form" style="text-decoration:none;">
    <div class="mp-card-body" style="text-align:center;">
      <i class="fa fa-cut" style="font-size:32px; color:var(--mp-primary); display:block; margin-bottom:12px;"></i>
      <h4>Cutting Worksheet</h4>
      <p class="mp-muted">Find a carcass and record actual yields.</p>
    </div>
  </a>
  <a href="<?= base_url('butchery/shares'); ?>" class="mp-card-form" style="text-decoration:none;">
    <div class="mp-card-body" style="text-align:center;">
      <i class="fa fa-users" style="font-size:32px; color:var(--mp-primary); display:block; margin-bottom:12px;"></i>
      <h4>Carcass Shares</h4>
      <p class="mp-muted">Reserve and manage group-buy shares.</p>
    </div>
  </a>
  <?php endif; ?>
  <a href="<?= base_url('butchery/freezers'); ?>" class="mp-card-form" style="text-decoration:none;">
    <div class="mp-card-body" style="text-align:center;">
      <i class="fa fa-snowflake-o" style="font-size:32px; color:var(--mp-primary); display:block; margin-bottom:12px;"></i>
      <h4>Freezers</h4>
      <p class="mp-muted">Add and manage cold storage locations.</p>
    </div>
  </a>
  <a href="<?= base_url('butchery/temperature'); ?>" class="mp-card-form" style="text-decoration:none;">
    <div class="mp-card-body" style="text-align:center;">
      <i class="fa fa-thermometer" style="font-size:32px; color:var(--mp-primary); display:block; margin-bottom:12px;"></i>
      <h4>Temperature Log</h4>
      <p class="mp-muted">Record cold-chain temperature checks.</p>
    </div>
  </a>
</div>
