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
<li><a href="<?=base_url();?>store_credit">Store Credit</a></li>
<li class="active"><?=$page_title;?></li>
</ol>
</section>
<section class="content">
<div class="row">
<div class="col-md-8 col-md-offset-2">
<div class="box box-primary">
<div class="box-header with-border"><h3 class="box-title">Issue Store Credit</h3></div>
<form class="form-horizontal" id="store-credit-form">
<input type="hidden" name="<?=$this->security->get_csrf_token_name();?>" value="<?=$this->security->get_csrf_hash();?>">
<div class="box-body">
<div class="form-group">
<label class="col-sm-3 control-label">Customer</label>
<div class="col-sm-6">
<select class="form-control select2" name="customer_id" style="width:100%" required>
<option value="">-- Select Customer --</option>
<?=get_customers_select_list('', get_current_store_id());?>
</select>
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label">Amount</label>
<div class="col-sm-6">
<input type="number" class="form-control only_currency" name="amount" required>
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label">Source</label>
<div class="col-sm-6">
<select class="form-control" name="source">
<option value="refund">Refund</option>
<option value="return">Product Return</option>
<option value="compensation">Compensation</option>
<option value="manual">Manual Credit</option>
<option value="promotion">Promotion</option>
<option value="loyalty_conversion">Loyalty Conversion</option>
</select>
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label">Expiry (Days)</label>
<div class="col-sm-6">
<input type="number" class="form-control" name="expiry_days" placeholder="0 = Never expires">
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label">Notes</label>
<div class="col-sm-6">
<textarea class="form-control" name="notes" rows="3"></textarea>
</div>
</div>
</div>
<div class="box-footer">
<a href="<?=base_url();?>store_credit" class="btn btn-default">Back</a>
<button type="button" class="btn btn-primary pull-right" onclick="save_credit()">Issue Credit</button>
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
function save_credit(){
    var form = $('#store-credit-form').serialize();
    $.post(base_url + 'store_credit/save', form, function(res){
        if(res=='success'){ success_show('Store credit issued'); setTimeout(function(){ window.location = base_url+'store_credit'; }, 1000); }
        else{ error_show('Failed: '+res); }
    });
}
</script>
</body>
</html>
