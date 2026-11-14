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
<li><a href="<?=base_url();?>gift_cards">Gift Cards</a></li>
<li class="active"><?=$page_title;?></li>
</ol>
</section>
<section class="content">
<div class="row">
<div class="col-md-8 col-md-offset-2">
<div class="box box-primary">
<div class="box-header with-border"><h3 class="box-title">Gift Card Details</h3></div>
<form class="form-horizontal" id="gift-card-form">
<input type="hidden" name="<?=$this->security->get_csrf_token_name();?>" value="<?=$this->security->get_csrf_hash();?>">
<input type="hidden" id="card_id" name="card_id" value="<?=isset($card)?$card->id:'';?>">
<div class="box-body">
<div class="form-group">
<label class="col-sm-3 control-label">Card Number</label>
<div class="col-sm-6">
<input type="text" class="form-control" name="card_number" value="<?=isset($card)?$card->card_number:'';?>" placeholder="Auto-generated if empty">
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label">Customer (Optional)</label>
<div class="col-sm-6">
<select class="form-control select2" name="customer_id" style="width:100%">
<option value="">-- Select Customer --</option>
<?=get_customers_select_list(isset($card)?$card->customer_id:'', get_current_store_id());?>
</select>
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label">Initial Value</label>
<div class="col-sm-6">
<input type="number" class="form-control only_currency" name="initial_value" value="<?=isset($card)?$card->initial_value:'';?>" required>
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label">Card Type</label>
<div class="col-sm-6">
<select class="form-control" name="card_type">
<option value="physical" <?=isset($card) && $card->card_type=='physical'?'selected':'';?>>Physical</option>
<option value="digital" <?=isset($card) && $card->card_type=='digital'?'selected':'';?>>Digital</option>
</select>
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label">Expiry (Days)</label>
<div class="col-sm-6">
<input type="number" class="form-control" name="expiry_days" value="" placeholder="0 = Never expires">
</div>
</div>
<div class="form-group">
<label class="col-sm-3 control-label">Notes</label>
<div class="col-sm-6">
<textarea class="form-control" name="notes" rows="3"><?=isset($card)?$card->notes:'';?></textarea>
</div>
</div>
</div>
<div class="box-footer">
<a href="<?=base_url();?>gift_cards" class="btn btn-default">Back</a>
<button type="button" class="btn btn-primary pull-right" onclick="save_card()">Save</button>
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
function save_card(){
    var form = $('#gift-card-form').serialize();
    var url = base_url + 'gift_cards/save';
    if($('#card_id').val()) url = base_url + 'gift_cards/update';
    $.post(url, form, function(res){
        if(res=='success'){ success_show('Gift card saved'); setTimeout(function(){ window.location = base_url+'gift_cards'; }, 1000); }
        else{ error_show('Failed: '+res); }
    });
}
</script>
</body>
</html>
