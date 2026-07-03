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
<h1><?=$page_title;?><small>Configure Loyalty Program</small></h1>
<ol class="breadcrumb">
<li><a href="<?=base_url();?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
<li class="active"><?=$page_title;?></li>
</ol>
</section>
<section class="content">
<div class="row">
<div class="col-md-12">
<div class="box box-primary">
<div class="box-header with-border"><h3 class="box-title">Loyalty Settings</h3></div>
<form class="form-horizontal" id="loyalty-settings-form" method="post">
<input type="hidden" name="<?=$this->security->get_csrf_token_name();?>" value="<?=$this->security->get_csrf_hash();?>">
<div class="box-body">
<div class="form-group">
<label class="col-sm-3 control-label">Enable Loyalty Program</label>
<div class="col-sm-6">
<select class="form-control" name="loyalty_enabled">
<option value="1" <?=($settings->loyalty_enabled==1)?'selected':'';?>>Yes</option>
<option value="0" <?=($settings->loyalty_enabled==0)?'selected':'';?>>No</option>
</select>
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label">Earning Type</label>
<div class="col-sm-6">
<select class="form-control" name="earning_type" id="earning_type">
<option value="spend_based" <?=($settings->earning_type=='spend_based')?'selected':'';?>>Spend Based (e.g. every NGN 1,000 = 1 point)</option>
<option value="percentage_based" <?=($settings->earning_type=='percentage_based')?'selected':'';?>>Percentage Based (e.g. 2% of purchase)</option>
<option value="product_specific" <?=($settings->earning_type=='product_specific')?'selected':'';?>>Product Specific</option>
<option value="service_specific" <?=($settings->earning_type=='service_specific')?'selected':'';?>>Service Specific</option>
</select>
</div>
</div>
<div class="form-group spend-based">
<label class="col-sm-3 control-label">Spend Amount</label>
<div class="col-sm-3">
<input type="number" class="form-control" name="spend_amount" value="<?=$settings->spend_amount;?>" placeholder="1000">
</div>
<div class="col-sm-3">
<input type="number" class="form-control" name="points_earned" value="<?=$settings->points_earned;?>" placeholder="Points Earned">
</div>
</div>
<div class="form-group percentage-based" style="display:none;">
<label class="col-sm-3 control-label">Percentage Rate (%)</label>
<div class="col-sm-6">
<input type="number" step="0.01" class="form-control" name="percentage_rate" value="<?=$settings->percentage_rate;?>" placeholder="2">
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label">Redemption Rate (Points = NGN)</label>
<div class="col-sm-6">
<input type="number" step="0.01" class="form-control" name="redemption_rate" value="<?=$settings->redemption_rate;?>" placeholder="10">
<p class="help-block">e.g. 100 points = NGN 1,000 discount</p>
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label">Minimum Redemption Points</label>
<div class="col-sm-6">
<input type="number" class="form-control" name="minimum_redemption_points" value="<?=$settings->minimum_redemption_points;?>" placeholder="100">
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label">Maximum Redemption Per Sale</label>
<div class="col-sm-6">
<input type="number" class="form-control" name="maximum_redemption_per_sale" value="<?=$settings->maximum_redemption_per_sale;?>" placeholder="0 = Unlimited">
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label">Allow Partial Redemption</label>
<div class="col-sm-6">
<select class="form-control" name="allow_partial_redemption">
<option value="1" <?=($settings->allow_partial_redemption==1)?'selected':'';?>>Yes</option>
<option value="0" <?=($settings->allow_partial_redemption==0)?'selected':'';?>>No</option>
</select>
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label">Tier Calculation Based On</label>
<div class="col-sm-6">
<select class="form-control" name="tier_calculation">
<option value="lifetime_spend" <?=($settings->tier_calculation=='lifetime_spend')?'selected':'';?>>Lifetime Spend</option>
<option value="points" <?=($settings->tier_calculation=='points')?'selected':'';?>>Points Balance</option>
</select>
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label">PayPlan Points Timing</label>
<div class="col-sm-6">
<select class="form-control" name="flexpay_points_timing">
<option value="full_payment" <?=($settings->flexpay_points_timing=='full_payment')?'selected':'';?>>Only After Full Payment</option>
<option value="immediately" <?=($settings->flexpay_points_timing=='immediately')?'selected':'';?>>Immediately After Deposit</option>
<option value="disabled" <?=($settings->flexpay_points_timing=='disabled')?'selected':'';?>>Disabled</option>
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
<div class="row">
<div class="col-md-12">
<div class="box box-info">
<div class="box-header with-border"><h3 class="box-title">Customer Tiers</h3>
<button class="btn btn-sm btn-primary pull-right" onclick="open_tier_modal()"><i class="fa fa-plus"></i> Add Tier</button>
</div>
<div class="box-body table-responsive">
<table class="table table-bordered table-striped">
<thead class="bg-gray"><tr><th>Name</th><th>Min. Spend</th><th>Min. Points</th><th>Discount %</th><th>Bonus Points %</th><th>Priority Service</th><th>Birthday Reward</th><th>Action</th></tr></thead>
<tbody>
<?php foreach($tiers as $tier): ?>
<tr>
<td><strong><?=$tier->tier_name;?></strong></td>
<td><?=store_number_format($tier->minimum_spend);?></td>
<td><?=$tier->minimum_points;?></td>
<td><?=$tier->discount_percentage;?>%</td>
<td><?=$tier->bonus_points_percentage;?>%</td>
<td><?=$tier->priority_service ? '<span class="label label-success">Yes</span>' : '<span class="label label-default">No</span>';?></td>
<td><?=ucfirst($tier->birthday_reward_type);?> (<?=store_number_format($tier->birthday_reward_value);?>)</td>
<td>
<button class="btn btn-sm btn-primary" onclick="edit_tier(<?=$tier->id;?>,'<?=$tier->tier_name;?>',<?=$tier->minimum_spend;?>,<?=$tier->minimum_points;?>,<?=$tier->discount_percentage;?>,<?=$tier->bonus_points_percentage;?>,<?=$tier->priority_service;?>,'<?=$tier->birthday_reward_type;?>',<?=$tier->birthday_reward_value;?>,<?=$tier->sort_order;?>)"><i class="fa fa-edit"></i></button>
<button class="btn btn-sm btn-danger" onclick="delete_tier(<?=$tier->id;?>)"><i class="fa fa-trash"></i></button>
</td>
</tr>
<?php endforeach; ?>
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

<!-- Tier Modal -->
<div class="modal fade" id="tier-modal">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal">&times;</button>
<h4 class="modal-title">Customer Tier</h4>
</div>
<div class="modal-body">
<form id="tier-form">
<input type="hidden" name="<?=$this->security->get_csrf_token_name();?>" value="<?=$this->security->get_csrf_hash();?>">
<input type="hidden" name="tier_id" id="tier_id">
<div class="form-group"><label>Tier Name</label><input type="text" class="form-control" name="tier_name" id="tier_name" required></div>
<div class="row">
<div class="col-md-6"><div class="form-group"><label>Minimum Spend</label><input type="number" class="form-control" name="minimum_spend" id="minimum_spend" value="0"></div></div>
<div class="col-md-6"><div class="form-group"><label>Minimum Points</label><input type="number" class="form-control" name="minimum_points" id="minimum_points" value="0"></div></div>
</div>
<div class="row">
<div class="col-md-6"><div class="form-group"><label>Discount %</label><input type="number" step="0.01" class="form-control" name="discount_percentage" id="discount_percentage" value="0"></div></div>
<div class="col-md-6"><div class="form-group"><label>Bonus Points %</label><input type="number" step="0.01" class="form-control" name="bonus_points_percentage" id="bonus_points_percentage" value="0"></div></div>
</div>
<div class="form-group">
<label>Birthday Reward Type</label>
<select class="form-control" name="birthday_reward_type" id="birthday_reward_type">
<option value="discount">Discount %</option>
<option value="voucher">Voucher Amount</option>
<option value="points">Bonus Points</option>
<option value="product">Free Product</option>
</select>
</div>
<div class="form-group"><label>Birthday Reward Value</label><input type="number" class="form-control" name="birthday_reward_value" id="birthday_reward_value" value="0"></div>
<div class="form-group"><label>Sort Order</label><input type="number" class="form-control" name="sort_order" id="sort_order" value="0"></div>
<div class="form-group">
<label><input type="checkbox" name="priority_service" id="priority_service" value="1"> Priority Service</label>
</div>
</form>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
<button type="button" class="btn btn-primary" onclick="save_tier()">Save</button>
</div>
</div>
</div>
</div>

<?php include APPPATH . "views/comman/code_js_sound.php"; ?>
<?php include APPPATH . "views/comman/code_js.php"; ?>
<script>
function save_settings(){
    var form = $('#loyalty-settings-form').serialize();
    $.post(base_url + 'loyalty/save_settings', form, function(res){
        if(res=='success'){ success_show('Settings saved successfully'); }
        else{ error_show('Failed: ' + res); console.log('save_settings response:', res); }
    });
}
function open_tier_modal(){
    $('#tier-form')[0].reset(); $('#tier_id').val(''); $('#tier-modal').modal('show');
}
function edit_tier(id,name,min_spend,min_points,discount,bonus,priority,reward_type,reward_value,sort){
    $('#tier_id').val(id); $('#tier_name').val(name); $('#minimum_spend').val(min_spend); $('#minimum_points').val(min_points);
    $('#discount_percentage').val(discount); $('#bonus_points_percentage').val(bonus); $('#priority_service').prop('checked', priority==1);
    $('#birthday_reward_type').val(reward_type); $('#birthday_reward_value').val(reward_value); $('#sort_order').val(sort);
    $('#tier-modal').modal('show');
}
function save_tier(){
    var form = $('#tier-form').serialize();
    $.post(base_url + 'loyalty/save_tier', form, function(res){
        if(res=='success'){ success_show('Tier saved'); $('#tier-modal').modal('hide'); location.reload(); }
        else{ error_show('Failed: ' + res); console.log('save_tier response:', res); }
    });
}
function delete_tier(id){
    swal({
        title: "Are you sure?",
        text: "This tier will be deleted.",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel",
        closeOnConfirm: true
    }, function(isConfirm){
        if(isConfirm){
            $.post(base_url + 'loyalty/delete_tier/'+id, function(res){
                if(res=='success'){ success_show('Tier deleted'); location.reload(); }
                else{ error_show('Failed'); }
            });
        }
    });
}
$('#earning_type').on('change', function(){
    if($(this).val()=='spend_based'){ $('.spend-based').show(); $('.percentage-based').hide(); }
    else if($(this).val()=='percentage_based'){ $('.spend-based').hide(); $('.percentage-based').show(); }
    else{ $('.spend-based').hide(); $('.percentage-based').hide(); }
});
if($('#earning_type').val()=='percentage_based'){ $('.spend-based').hide(); $('.percentage-based').show(); }
</script>
</body>
</html>
