<?php $this->load->view('marketing/desktop/_styles'); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title ?? 'Loyalty & Rewards'); ?></h2>
    <div class="mp-page-sub">Customer Loyalty &amp; Rewards</div>
  </div>
</div>

<div class="mp-section">
  <div class="mp-kpi-grid">
    <div class="mp-kpi-card sales">
      <div class="mp-kpi-icon"><i class="fa fa-users"></i></div>
      <div class="mp-kpi-label">Active Members</div>
      <div class="mp-kpi-value"><?= number_format((float)($stats['active_members'] ?? 0)); ?></div>
    </div>
    <div class="mp-kpi-card profit">
      <div class="mp-kpi-icon"><i class="fa fa-arrow-up"></i></div>
      <div class="mp-kpi-label">Points Issued</div>
      <div class="mp-kpi-value"><?= number_format((float)($stats['total_points_issued'] ?? 0), 0); ?></div>
    </div>
    <div class="mp-kpi-card debt">
      <div class="mp-kpi-icon"><i class="fa fa-arrow-down"></i></div>
      <div class="mp-kpi-label">Points Redeemed</div>
      <div class="mp-kpi-value"><?= number_format((float)($stats['total_points_redeemed'] ?? 0), 0); ?></div>
    </div>
    <div class="mp-kpi-card stock">
      <div class="mp-kpi-icon"><i class="fa fa-credit-card"></i></div>
      <div class="mp-kpi-label">Store Credit Outstanding</div>
      <div class="mp-kpi-value"><?= store_number_format($stats['store_credit_outstanding'] ?? 0); ?></div>
    </div>
    <div class="mp-kpi-card cash">
      <div class="mp-kpi-icon"><i class="fa fa-ticket"></i></div>
      <div class="mp-kpi-label">Gift Card Liability</div>
      <div class="mp-kpi-value"><?= store_number_format($stats['gift_card_liability'] ?? 0); ?></div>
    </div>
    <div class="mp-kpi-card target">
      <div class="mp-kpi-icon"><i class="fa fa-bullseye"></i></div>
      <div class="mp-kpi-label">Points Available</div>
      <div class="mp-kpi-value"><?= number_format((float)($stats['points_available'] ?? 0)); ?></div>
    </div>
  </div>
</div>

<div class="mp-card-form">
  <div class="mp-card-head"><h3>Quick Links</h3></div>
  <div class="mp-card-body">
    <div class="mp-quick-grid">
      <a href="<?= base_url('loyalty/settings'); ?>" class="mp-qa-btn purple"><i class="fa fa-cog"></i> Settings</a>
      <a href="<?= base_url('loyalty/tiers'); ?>" class="mp-qa-btn green"><i class="fa fa-sitemap"></i> Tiers</a>
      <a href="<?= base_url('loyalty/bonus_rules'); ?>" class="mp-qa-btn orange"><i class="fa fa-bolt"></i> Bonus Rules</a>
      <a href="<?= base_url('loyalty/product_points'); ?>" class="mp-qa-btn blue"><i class="fa fa-cubes"></i> Product Points</a>
      <a href="<?= base_url('loyalty/points_history'); ?>" class="mp-qa-btn teal"><i class="fa fa-history"></i> Points History</a>
      <a href="<?= base_url('gift_cards'); ?>" class="mp-qa-btn red"><i class="fa fa-ticket"></i> Gift Cards</a>
      <a href="<?= base_url('store_credit'); ?>" class="mp-qa-btn blue"><i class="fa fa-credit-card"></i> Store Credit</a>
      <a href="<?= base_url('loyalty/referral_program'); ?>" class="mp-qa-btn purple"><i class="fa fa-share-alt"></i> Referrals</a>
    </div>
  </div>
</div>

<script>
  $(".loyalty-active-li").addClass("active").closest(".mp-nav-group").addClass("open");
</script>
