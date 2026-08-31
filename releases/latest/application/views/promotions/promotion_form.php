<!DOCTYPE html>
<html>
<head>
<?php $this->load->view('comman/code_css.php');?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar.php');?>
<?php
if(!isset($promotion_name)){
    $promotion_id=$promotion_name=$promotion_code=$description='';
    $discount_type='Percentage'; $discount_value=''; $min_price_rule=''; $min_margin_pct='';
    $applies_to='all'; $category_id=''; $brand_id=''; $start_date=''; $end_date='';
    $linked_item_ids=array();
}
?>
<div class="content-wrapper">
<section class="content-header">
<h1><?=$page_title;?><small>Add / Update Promotion</small></h1>
<ol class="breadcrumb">
<li><a href="<?=base_url('dashboard');?>"><i class="fa fa-dashboard"></i> Home</a></li>
<li><a href="<?=base_url('promotions');?>"><?= $this->lang->line('promotion_list'); ?></a></li>
<li class="active"><?=$page_title;?></li>
</ol>
</section>
<section class="content">
<div class="row">
<div class="col-md-12">
<div class="box box-info">
<div class="box-header with-border"><h3 class="box-title">Promotion Details</h3></div>
<form class="form-horizontal" id="promotion-form" onkeypress="return event.keyCode != 13;">
<input type="hidden" name="<?=$this->security->get_csrf_token_name();?>" value="<?=$this->security->get_csrf_hash();?>">
<input type="hidden" id="base_url" value="<?=$base_url;?>">
<input type="hidden" name="command" value="<?=isset($promotion_id)?'update':'save';?>">
<input type="hidden" name="promotion_id" id="promotion_id" value="<?=$promotion_id ?? '';?>">

<div class="box-body">
<div class="form-group">
<label for="promotion_name" class="col-sm-2 control-label">Promotion Name <label class="text-danger">*</label></label>
<div class="col-sm-4">
<input type="text" class="form-control input-sm" id="promotion_name" name="promotion_name" value="<?=htmlspecialchars($promotion_name);?>" autofocus>
<span id="promotion_name_msg" style="display:none" class="text-danger"></span>
</div>
</div>

<div class="form-group">
<label for="promotion_code" class="col-sm-2 control-label">Promotion Code</label>
<div class="col-sm-4">
<input type="text" class="form-control input-sm" id="promotion_code" name="promotion_code" value="<?=htmlspecialchars($promotion_code);?>" placeholder="Optional e.g. SUMMER25">
</div>
</div>

<div class="form-group">
<label for="description" class="col-sm-2 control-label">Description</label>
<div class="col-sm-4">
<textarea class="form-control" id="description" name="description" rows="2"><?=htmlspecialchars($description);?></textarea>
</div>
</div>

<div class="form-group">
<label for="discount_type" class="col-sm-2 control-label">Discount Type <label class="text-danger">*</label></label>
<div class="col-sm-4">
<select class="form-control select2" id="discount_type" name="discount_type">
<option value="Percentage" <?=($discount_type=='Percentage')?'selected':'';?>>Percentage (%)</option>
<option value="Fixed" <?=($discount_type=='Fixed')?'selected':'';?>>Fixed Amount</option>
</select>
</div>
</div>

<div class="form-group">
<label for="discount_value" class="col-sm-2 control-label">Discount Value <label class="text-danger">*</label></label>
<div class="col-sm-4">
<input type="number" step="0.01" min="0" class="form-control input-sm only_currency" id="discount_value" name="discount_value" value="<?=htmlspecialchars($discount_value);?>">
</div>
</div>

<div class="form-group">
<label for="min_price_rule" class="col-sm-2 control-label"><?= $this->lang->line('min_price_rule'); ?></label>
<div class="col-sm-4">
<input type="number" step="0.01" min="0" class="form-control input-sm only_currency" id="min_price_rule" name="min_price_rule" value="<?=htmlspecialchars($min_price_rule);?>" placeholder="Never sell below this price">
<span class="help-block text-muted">Protects margin — discount will never drop the price below this amount.</span>
</div>
</div>

<div class="form-group">
<label for="min_margin_pct" class="col-sm-2 control-label"><?= $this->lang->line('min_margin_pct'); ?></label>
<div class="col-sm-4">
<input type="number" step="0.01" min="0" max="100" class="form-control input-sm" id="min_margin_pct" name="min_margin_pct" value="<?=htmlspecialchars($min_margin_pct);?>" placeholder="e.g. 20">
<span class="help-block text-muted">Discount will never drop below this % margin over cost price.</span>
</div>
</div>

<div class="form-group">
<label for="applies_to" class="col-sm-2 control-label"><?= $this->lang->line('applies_to'); ?></label>
<div class="col-sm-4">
<select class="form-control select2" id="applies_to" name="applies_to">
<option value="all" <?=($applies_to=='all')?'selected':'';?>>All Items</option>
<option value="category" <?=($applies_to=='category')?'selected':'';?>>Specific Category (Collection)</option>
<option value="brand" <?=($applies_to=='brand')?'selected':'';?>>Specific Brand</option>
<option value="items" <?=($applies_to=='items')?'selected':'';?>>Specific Items</option>
</select>
</div>
</div>

<div class="form-group" id="category_row" style="display:none;">
<label for="category_id" class="col-sm-2 control-label">Category</label>
<div class="col-sm-4">
<select class="form-control select2" id="category_id" name="category_id">
<option value="">-Select-</option>
<?= get_categories_select_list(null,get_current_store_id());?>
</select>
</div>
</div>

<div class="form-group" id="brand_row" style="display:none;">
<label for="brand_id" class="col-sm-2 control-label">Brand</label>
<div class="col-sm-4">
<select class="form-control select2" id="brand_id" name="brand_id">
<option value="">-Select-</option>
<?= get_brands_select_list(null,get_current_store_id());?>
</select>
</div>
</div>

<div class="form-group" id="items_row" style="display:none;">
<label for="item_ids" class="col-sm-2 control-label">Select Items</label>
<div class="col-sm-6">
<select class="form-control select2" id="item_ids" name="item_ids[]" multiple="multiple" style="width:100%;">
<?php
$all_items = $this->db->select('id, item_name')->where('store_id', get_current_store_id())->where('status', 1)->where('service_bit', 0)->order_by('item_name','asc')->get('db_items')->result();
$linked = $linked_item_ids ?? array();
foreach($all_items as $it):
    $sel = in_array($it->id, $linked) ? 'selected' : '';
?>
<option value="<?=$it->id;?>" <?=$sel;?>><?=htmlspecialchars($it->item_name);?></option>
<?php endforeach; ?>
</select>
<span class="help-block text-muted">Select one or more items this promotion applies to.</span>
</div>
</div>

<div class="form-group">
<label for="start_date" class="col-sm-2 control-label"><?= $this->lang->line('start_date'); ?> <label class="text-danger">*</label></label>
<div class="col-sm-4">
<div class="input-group date"><div class="input-group-addon"><i class="fa fa-calendar"></i></div>
<input type="text" class="form-control pull-right datepicker" id="start_date" name="start_date" value="<?=htmlspecialchars($start_date);?>">
</div>
</div>
</div>

<div class="form-group">
<label for="end_date" class="col-sm-2 control-label"><?= $this->lang->line('end_date'); ?> <label class="text-danger">*</label></label>
<div class="col-sm-4">
<div class="input-group date"><div class="input-group-addon"><i class="fa fa-calendar"></i></div>
<input type="text" class="form-control pull-right datepicker" id="end_date" name="end_date" value="<?=htmlspecialchars($end_date);?>">
</div>
</div>
</div>

</div>

<div class="box-footer">
<div class="col-sm-8 col-sm-offset-2 text-center">
<div class="col-md-3 col-md-offset-3">
<button type="button" id="save" class="btn btn-block btn-success" title="Save"><?=isset($promotion_id)?'Update':'Save';?></button>
</div>
<div class="col-sm-3">
<a href="<?=base_url('promotions');?>"><button type="button" class="btn btn-block btn-warning close_btn">Close</button></a>
</div>
</div>
</div>
</form>
</div>
</div>
</div>
</section>
</div>
<?php $this->load->view('footer.php');?>
<div class="control-sidebar-bg"></div>
</div>
<?php $this->load->view('comman/code_js_sound.php');?>
<?php $this->load->view('comman/code_js.php');?>
<script type="text/javascript">
var base_url=$("#base_url").val();
function toggle_applies_rows(){
    var v=$("#applies_to").val();
    $("#category_row").hide(); $("#brand_row").hide(); $("#items_row").hide();
    if(v=="category"){ $("#category_row").show(); }
    if(v=="brand"){ $("#brand_row").show(); }
    if(v=="items"){ $("#items_row").show(); }
}
$("#applies_to").on("change",function(){ toggle_applies_rows(); });
toggle_applies_rows();

$("#save").on("click",function(){
    if(!$("#promotion_name").val()){ alert("Promotion Name is required."); return; }
    if(!$("#discount_value").val()){ alert("Discount Value is required."); return; }
    if(!$("#start_date").val() || !$("#end_date").val()){ alert("Start and End dates are required."); return; }
    var data=new FormData($("#promotion-form")[0]);
    $.ajax({
        type:'POST', url:base_url+'promotions/save', data:data,
        cache:false, contentType:false, processData:false,
        success:function(res){
            if(res.indexOf("success")!==-1){
                window.location.href=base_url+"promotions";
            } else {
                alert(res);
            }
        }
    });
});
</script>
<script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
</body>
</html>
