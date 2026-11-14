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
<h1><?=$page_title;?></h1>
<ol class="breadcrumb">
<li><a href="<?=base_url();?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
<li class="active"><?=$page_title;?></li>
</ol>
</section>
<section class="content">
<div class="row">
<div class="col-xs-12">
<div class="box box-primary">
<div class="box-header with-border">
<h3 class="box-title">Customer Tiers</h3>
<div class="box-tools">
<a class="btn btn-block btn-info" href="<?=base_url();?>loyalty/settings"><i class="fa fa-cog"></i> Manage in Settings</a>
</div>
</div>
<div class="box-body">
<table class="table table-bordered table-striped">
<thead class="bg-gray"><tr><th>#</th><th>Tier Name</th><th>Min Spend</th><th>Min Points</th><th>Discount %</th><th>Bonus %</th><th>Priority</th><th>Birthday</th><th>Status</th></tr></thead>
<tbody>
<?php $i=1; foreach($tiers as $tier){ ?>
<tr>
<td><?=$i++;?></td>
<td><?=$tier->tier_name;?></td>
<td><?=$tier->minimum_spend;?></td>
<td><?=$tier->minimum_points;?></td>
<td><?=$tier->discount_percentage;?>%</td>
<td><?=$tier->bonus_points_percentage;?>%</td>
<td><?=$tier->priority_service ? 'Yes' : 'No';?></td>
<td><?=$tier->birthday_reward_type;?> (<?=$tier->birthday_reward_value;?>)</td>
<td><span class="label label-success">Active</span></td>
</tr>
<?php } if(empty($tiers)){ ?>
<tr><td colspan="9" class="text-center text-danger">No tiers found. <a href="<?=base_url();?>loyalty/settings">Create tiers in Settings</a></td></tr>
<?php } ?>
</tbody>
</table>
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
