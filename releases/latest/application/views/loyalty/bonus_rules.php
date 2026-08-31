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
<div class="col-xs-12">
<div class="box box-primary">
<div class="box-header with-border">
<h3 class="box-title">Bonus Rules</h3>
<button class="btn btn-sm btn-primary pull-right" onclick="open_rule_modal()"><i class="fa fa-plus"></i> Add Rule</button>
</div>
<div class="box-body table-responsive">
<table class="table table-bordered table-striped">
<thead class="bg-gray"><tr><th>Name</th><th>Type</th><th>Multiplier</th><th>Bonus Points</th><th>Start</th><th>End</th><th>Status</th><th>Action</th></tr></thead>
<tbody>
<?php foreach($rules as $rule): ?>
<tr>
<td><?=$rule->rule_name;?></td>
<td><?=ucfirst(str_replace('_',' ',$rule->rule_type));?></td>
<td><?=$rule->multiplier;?>x</td>
<td><?=$rule->bonus_points;?></td>
<td><?=show_date($rule->start_date) ?: '-';?></td>
<td><?=show_date($rule->end_date) ?: '-';?></td>
<td><?=$rule->status ? '<span class="label label-success">Active</span>' : '<span class="label label-default">Inactive</span>';?></td>
<td>
<button class="btn btn-sm btn-primary" onclick="edit_rule(<?=$rule->id;?>,'<?=$rule->rule_name;?>','<?=$rule->rule_type;?>',<?=$rule->multiplier;?>,<?=$rule->bonus_points;?>,'<?=$rule->start_date;?>','<?=$rule->end_date;?>','<?=$rule->days_of_week;?>')"><i class="fa fa-edit"></i></button>
<button class="btn btn-sm btn-danger" onclick="delete_rule(<?=$rule->id;?>)"><i class="fa fa-trash"></i></button>
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

<div class="modal fade" id="rule-modal">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal">&times;</button>
<h4 class="modal-title">Bonus Rule</h4>
</div>
<div class="modal-body">
<form id="rule-form">
<input type="hidden" name="rule_id" id="rule_id">
<div class="form-group"><label>Rule Name</label><input type="text" class="form-control" name="rule_name" id="rule_name" required></div>
<div class="form-group">
<label>Rule Type</label>
<select class="form-control" name="rule_type" id="rule_type">
<option value="double_points_day">Double Points Day</option>
<option value="weekend_bonus">Weekend Bonus</option>
<option value="holiday_bonus">Holiday Bonus</option>
<option value="campaign_bonus">Campaign Bonus</option>
<option value="birthday_bonus">Birthday Bonus</option>
<option value="referral_bonus">Referral Bonus</option>
<option value="vip_bonus">VIP Bonus</option>
</select>
</div>
<div class="row">
<div class="col-md-6"><div class="form-group"><label>Multiplier</label><input type="number" step="0.01" class="form-control" name="multiplier" id="multiplier" value="2"></div></div>
<div class="col-md-6"><div class="form-group"><label>Bonus Points (fixed)</label><input type="number" class="form-control" name="bonus_points" id="bonus_points" value="0"></div></div>
</div>
<div class="row">
<div class="col-md-6"><div class="form-group"><label>Start Date</label><input type="date" class="form-control" name="start_date" id="start_date"></div></div>
<div class="col-md-6"><div class="form-group"><label>End Date</label><input type="date" class="form-control" name="end_date" id="end_date"></div></div>
</div>
<div class="form-group"><label>Days of Week (1=Sun, 7=Sat, comma separated)</label><input type="text" class="form-control" name="days_of_week" id="days_of_week" placeholder="e.g. 1,7"></div>
</form>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
<button type="button" class="btn btn-primary" onclick="save_rule()">Save</button>
</div>
</div>
</div>
</div>

<?php include APPPATH . "views/comman/code_js_sound.php"; ?>
<?php include APPPATH . "views/comman/code_js.php"; ?>
<script>
function open_rule_modal(){
    $('#rule-form')[0].reset(); $('#rule_id').val(''); $('#rule-modal').modal('show');
}
function edit_rule(id,name,type,multiplier,bonus_points,start_date,end_date,days_of_week){
    $('#rule_id').val(id); $('#rule_name').val(name); $('#rule_type').val(type); $('#multiplier').val(multiplier);
    $('#bonus_points').val(bonus_points); $('#start_date').val(start_date); $('#end_date').val(end_date); $('#days_of_week').val(days_of_week);
    $('#rule-modal').modal('show');
}
function save_rule(){
    var form = $('#rule-form').serialize();
    $.post(base_url + 'loyalty/save_bonus_rule', form, function(res){
        if(res=='success'){ success_show('Rule saved'); $('#rule-modal').modal('hide'); location.reload(); }
        else{ error_show('Failed'); }
    });
}
function delete_rule(id){
    swal({
        title: "Are you sure?",
        text: "Delete this rule?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel",
        closeOnConfirm: true
    }, function(isConfirm){
        if(isConfirm){
            $.post(base_url + 'loyalty/delete_bonus_rule/'+id, function(res){
                if(res=='success'){ success_show('Deleted'); location.reload(); }
                else{ error_show('Failed'); }
            });
        }
    });
}
</script>
</body>
</html>
