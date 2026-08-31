<!DOCTYPE html>
<html>
<head>
<?php include APPPATH . "views/comman/code_css.php"; ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php include APPPATH . "views/sidebar.php"; ?>

<div class="content-wrapper">
  <section class="content-header">
    <h1><?=$page_title;?> <small>Customer Loyalty &amp; Rewards</small></h1>
  </section>

  <section class="content">
    <div class="mp-dashboard-wrapper">

      <!-- Business Overview -->
      <div class="mp-section">
        <div class="mp-section-title">Business Overview</div>
        <div class="mp-kpi-grid">
          <div class="mp-kpi-card sales">
            <div class="mp-kpi-label">Active Members</div>
            <div class="mp-kpi-value"><?=number_format($stats['active_members']);?></div>
          </div>
          <div class="mp-kpi-card profit">
            <div class="mp-kpi-label">Points Issued</div>
            <div class="mp-kpi-value"><?=number_format($stats['total_points_issued'],0);?></div>
          </div>
          <div class="mp-kpi-card debt">
            <div class="mp-kpi-label">Points Redeemed</div>
            <div class="mp-kpi-value"><?=number_format($stats['total_points_redeemed'],0);?></div>
          </div>
          <div class="mp-kpi-card stock">
            <div class="mp-kpi-label">Store Credit Outstanding</div>
            <div class="mp-kpi-value"><?=store_number_format($stats['store_credit_outstanding']);?></div>
          </div>
          <div class="mp-kpi-card summary">
            <div class="mp-kpi-label">Gift Card Liability</div>
            <div class="mp-kpi-value"><?=store_number_format($stats['gift_card_liability']);?></div>
          </div>
          <div class="mp-kpi-card">
            <div class="mp-kpi-label">Points Available to Redeem</div>
            <div class="mp-kpi-value"><?=number_format($stats['points_available']);?></div>
          </div>
        </div>
      </div>

      <!-- Quick Links -->
      <div class="mp-section">
        <div class="mp-card">
          <div class="mp-card-header"><div class="mp-card-title">Quick Links</div></div>
          <div class="mp-card-body">
            <div class="row">
              <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6" style="margin-bottom:12px;">
                <a href="<?=base_url();?>loyalty/settings" class="mp-quick-btn purple" style="display:flex;justify-content:center;width:100%;"><i class="fa fa-cog"></i> Loyalty Settings</a>
              </div>
              <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6" style="margin-bottom:12px;">
                <a href="<?=base_url();?>loyalty/tiers" class="mp-quick-btn green" style="display:flex;justify-content:center;width:100%;"><i class="fa fa-sitemap"></i> Customer Tiers</a>
              </div>
              <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6" style="margin-bottom:12px;">
                <a href="<?=base_url();?>loyalty/bonus_rules" class="mp-quick-btn orange" style="display:flex;justify-content:center;width:100%;"><i class="fa fa-bolt"></i> Bonus Rules</a>
              </div>
              <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6" style="margin-bottom:12px;">
                <a href="<?=base_url();?>loyalty/product_points" class="mp-quick-btn blue" style="display:flex;justify-content:center;width:100%;"><i class="fa fa-cubes"></i> Product Points</a>
              </div>
              <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6" style="margin-bottom:12px;">
                <a href="<?=base_url();?>loyalty/points_history" class="mp-quick-btn teal" style="display:flex;justify-content:center;width:100%;"><i class="fa fa-history"></i> Points History</a>
              </div>
              <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6" style="margin-bottom:12px;">
                <a href="<?=base_url();?>gift_cards" class="mp-quick-btn red" style="display:flex;justify-content:center;width:100%;"><i class="fa fa-ticket"></i> Gift Cards</a>
              </div>
              <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6" style="margin-bottom:12px;">
                <a href="<?=base_url();?>store_credit" class="mp-quick-btn" style="display:flex;justify-content:center;width:100%;background:#6366F1;color:#fff;"><i class="fa fa-credit-card"></i> Store Credit</a>
              </div>
              <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6" style="margin-bottom:12px;">
                <a href="<?=base_url();?>loyalty/referral_program" class="mp-quick-btn purple" style="display:flex;justify-content:center;width:100%;"><i class="fa fa-share-alt"></i> Referrals</a>
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </section>
</div>

<?php include APPPATH . "views/footer.php"; ?>
</div>

<?php include APPPATH . "views/comman/code_js_sound.php"; ?>
<?php include APPPATH . "views/comman/code_js.php"; ?>
<script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
</body>
</html>
