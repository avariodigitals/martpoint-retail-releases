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
<li><a href="<?=base_url();?>loyalty">Loyalty</a></li>
<li class="active"><?=$page_title;?></li>
</ol>
</section>
<section class="content">
<div class="row">
<div class="col-md-8 col-md-offset-2">
<div class="box box-primary">
<div class="box-header with-border"><h3 class="box-title">Referral Program Settings</h3></div>
<form class="form-horizontal" id="referral-form">
<input type="hidden" name="<?=$this->security->get_csrf_token_name();?>" value="<?=$this->security->get_csrf_hash();?>">
<div class="box-body">
<div class="form-group">
<label class="col-sm-3 control-label">Enable Referral Program</label>
<div class="col-sm-6">
<select class="form-control" name="referral_enabled">
<option value="1" <?=isset($settings->referral_enabled) && $settings->referral_enabled==1?'selected':'';?>>Yes</option>
<option value="0" <?=isset($settings->referral_enabled) && $settings->referral_enabled==0?'selected':'';?>>No</option>
</select>
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label">Referrer Reward Type</label>
<div class="col-sm-6">
<select class="form-control" name="referrer_reward_type">
<option value="points" <?=isset($settings->referrer_reward_type) && $settings->referrer_reward_type=='points'?'selected':'';?>>Points</option>
<option value="credit" <?=isset($settings->referrer_reward_type) && $settings->referrer_reward_type=='credit'?'selected':'';?>>Store Credit</option>
<option value="discount" <?=isset($settings->referrer_reward_type) && $settings->referrer_reward_type=='discount'?'selected':'';?>>Discount %</option>
</select>
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label">Referrer Reward Value</label>
<div class="col-sm-6">
<input type="number" class="form-control" name="referrer_reward_value" value="<?=isset($settings->referrer_reward_value)?$settings->referrer_reward_value:100;?>">
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label">New Customer Reward Type</label>
<div class="col-sm-6">
<select class="form-control" name="new_customer_reward_type">
<option value="points" <?=isset($settings->new_customer_reward_type) && $settings->new_customer_reward_type=='points'?'selected':'';?>>Points</option>
<option value="credit" <?=isset($settings->new_customer_reward_type) && $settings->new_customer_reward_type=='credit'?'selected':'';?>>Store Credit</option>
<option value="discount" <?=isset($settings->new_customer_reward_type) && $settings->new_customer_reward_type=='discount'?'selected':'';?>>Discount %</option>
</select>
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label">New Customer Reward Value</label>
<div class="col-sm-6">
<input type="number" class="form-control" name="new_customer_reward_value" value="<?=isset($settings->new_customer_reward_value)?$settings->new_customer_reward_value:50;?>">
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label">Approval Required</label>
<div class="col-sm-6">
<select class="form-control" name="referral_approval_required">
<option value="1" <?=isset($settings->referral_approval_required) && $settings->referral_approval_required==1?'selected':'';?>>Yes</option>
<option value="0" <?=isset($settings->referral_approval_required) && $settings->referral_approval_required==0?'selected':'';?>>No</option>
</select>
</div>
</div>
</div>
<div class="box-footer">
<button type="button" class="btn btn-primary pull-right" onclick="save_settings()"><i class="fa fa-save"></i> Save Settings</button>
</div>
</form>
</div>
</div>
</div>
</section>
</div>
<?php include APPPATH . "views/footer.php"; ?>
</div>
<?php include APPPATH . "views/comman/code_js_sound.php"; ?>
<?php include APPPATH . "views/comman/code_js.php"; ?>
<script>
function save_settings(){
    var form = $('#referral-form').serialize();
    $.post(base_url + 'loyalty/save_referral_settings', form, function(res){
        if(res=='success'){ success_show('Settings saved'); }
        else{ error_show('Failed: ' + res); console.log('save_referral_settings response:', res); }
    });
}
</script>
</body>
</html>
