<!DOCTYPE html>
<html>
<head>
<?php include APPPATH . "views/comman/code_css.php"; ?>
<style>
/* Only custom styles for loyalty quick-link buttons - do NOT override Bootstrap/AdminLTE */
.lp-link-btn { display:block; border-radius:6px; padding:14px 8px; text-align:center; color:#fff !important; margin-bottom:15px; text-decoration:none !important; transition:opacity .15s; }
.lp-link-btn:hover { opacity:0.85; color:#fff !important; text-decoration:none !important; }
.lp-link-btn i { font-size:22px; margin-bottom:4px; display:block; }
.lp-link-btn span { font-size:12px; font-weight:600; }
</style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php include APPPATH . "views/sidebar.php"; ?>
<div class="content-wrapper">
<section class="content-header">
<h1><?=$page_title;?> <small>Customer Loyalty & Rewards</small></h1>
<ol class="breadcrumb">
<li><a href="<?=base_url();?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
<li class="active"><?=$page_title;?></li>
</ol>
</section>
<section class="content">

<!-- Stat Cards -->
<div class="row">
  <div class="col-lg-4 col-xs-6">
    <div class="small-box bg-purple">
      <div class="inner"><h3><?=number_format($stats['active_members']);?></h3><p>Active Members</p></div>
      <div class="icon"><i class="fa fa-users"></i></div>
    </div>
  </div>
  <div class="col-lg-4 col-xs-6">
    <div class="small-box bg-green">
      <div class="inner"><h3><?=number_format($stats['total_points_issued'],0);?></h3><p>Points Issued</p></div>
      <div class="icon"><i class="fa fa-star"></i></div>
    </div>
  </div>
  <div class="col-lg-4 col-xs-6">
    <div class="small-box bg-orange">
      <div class="inner"><h3><?=number_format($stats['total_points_redeemed'],0);?></h3><p>Points Redeemed</p></div>
      <div class="icon"><i class="fa fa-gift"></i></div>
    </div>
  </div>
  <div class="col-lg-4 col-xs-6">
    <div class="small-box bg-aqua">
      <div class="inner"><h3><?=store_number_format($stats['store_credit_outstanding']);?></h3><p>Store Credit Outstanding</p></div>
      <div class="icon"><i class="fa fa-credit-card"></i></div>
    </div>
  </div>
  <div class="col-lg-4 col-xs-6">
    <div class="small-box bg-teal">
      <div class="inner"><h3><?=store_number_format($stats['gift_card_liability']);?></h3><p>Gift Card Liability</p></div>
      <div class="icon"><i class="fa fa-ticket"></i></div>
    </div>
  </div>
  <div class="col-lg-4 col-xs-6">
    <div class="small-box bg-red">
      <div class="inner"><h3><?=number_format($stats['points_available']);?></h3><p>Points Available to Redeem</p></div>
      <div class="icon"><i class="fa fa-trophy"></i></div>
    </div>
  </div>
</div>

<!-- Quick Links -->
<div class="row">
  <div class="col-md-12">
    <div class="box box-primary">
      <div class="box-header with-border"><h3 class="box-title">Quick Links</h3></div>
      <div class="box-body">
        <div class="row">
          <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6"><a href="<?=base_url();?>loyalty/settings" class="lp-link-btn bg-purple"><i class="fa fa-cog"></i><span>Loyalty Settings</span></a></div>
          <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6"><a href="<?=base_url();?>loyalty/tiers" class="lp-link-btn bg-green"><i class="fa fa-sitemap"></i><span>Customer Tiers</span></a></div>
          <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6"><a href="<?=base_url();?>loyalty/bonus_rules" class="lp-link-btn bg-orange"><i class="fa fa-bolt"></i><span>Bonus Rules</span></a></div>
          <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6"><a href="<?=base_url();?>loyalty/product_points" class="lp-link-btn bg-aqua"><i class="fa fa-cubes"></i><span>Product Points</span></a></div>
          <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6"><a href="<?=base_url();?>loyalty/points_history" class="lp-link-btn bg-teal"><i class="fa fa-history"></i><span>Points History</span></a></div>
          <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6"><a href="<?=base_url();?>gift_cards" class="lp-link-btn bg-maroon"><i class="fa fa-ticket"></i><span>Gift Cards</span></a></div>
          <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6"><a href="<?=base_url();?>store_credit" class="lp-link-btn bg-red"><i class="fa fa-credit-card"></i><span>Store Credit</span></a></div>
          <div class="col-lg-3 col-md-3 col-sm-4 col-xs-6"><a href="<?=base_url();?>loyalty/referral_program" class="lp-link-btn bg-navy"><i class="fa fa-share-alt"></i><span>Referrals</span></a></div>
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
</body>
</html>
