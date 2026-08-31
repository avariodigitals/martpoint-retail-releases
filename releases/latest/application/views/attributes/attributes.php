<!DOCTYPE html>
<html>
<head>
<?php include(APPPATH."views/comman/code_css.php");?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php include(APPPATH."views/sidebar.php");?>
<div class="content-wrapper">
<section class="content-header">
<h1><?=$page_title;?><small></small></h1>
<ol class="breadcrumb">
<li><a href="<?=base_url('dashboard');?>"><i class="fa fa-dashboard"></i> Home</a></li>
<li><a href="<?=base_url('attributes');?>"><?= $this->lang->line('attributes_list'); ?></a></li>
<li class="active"><?=$page_title;?></li>
</ol>
</section>
<section class="content">
<div class="row">
<div class="col-md-12">
<div class="box box-info">
<div class="box-header with-border"><h3 class="box-title">Attribute Details</h3></div>
<form class="form-horizontal" id="attribute-form" onkeypress="return event.keyCode != 13;">
<input type="hidden" name="<?=$this->security->get_csrf_token_name();?>" value="<?=$this->security->get_csrf_hash();?>">
<input type="hidden" name="command" value="<?=isset($q_id)?'update':'save';?>">
<input type="hidden" name="q_id" value="<?=$q_id ?? '';?>">
<div class="box-body">
<div class="form-group">
<label for="attribute_type" class="col-sm-2 control-label">Attribute Type <span class="text-danger">*</span></label>
<div class="col-sm-4">
<input type="text" class="form-control" id="attribute_type" name="attribute_type" value="<?=htmlspecialchars($attribute_type ?? '');?>" placeholder="e.g. size, colour, length" required>
<span class="help-block text-muted">Use lowercase English names: size, colour, length, material, storage, shade</span>
</div>
</div>
<div class="form-group">
<label for="attribute_value" class="col-sm-2 control-label">Attribute Value <span class="text-danger">*</span></label>
<div class="col-sm-4">
<input type="text" class="form-control" id="attribute_value" name="attribute_value" value="<?=htmlspecialchars($attribute_value ?? '');?>" placeholder="e.g. S, Red, Short" required>
</div>
</div>
<div class="form-group">
<label for="sort_order" class="col-sm-2 control-label">Sort Order</label>
<div class="col-sm-4">
<input type="number" class="form-control" id="sort_order" name="sort_order" value="<?=htmlspecialchars($sort_order ?? '0');?>" placeholder="0">
</div>
</div>
</div>
<div class="box-footer">
<div class="col-sm-8 col-sm-offset-2 text-center">
<div class="col-md-3 col-md-offset-3">
<button type="button" id="save" class="btn btn-block btn-success"><?=isset($q_id)?'Update':'Save';?></button>
</div>
<div class="col-sm-3">
<a href="<?=base_url('attributes');?>"><button type="button" class="btn btn-block btn-warning close_btn">Close</button></a>
</div>
</div>
</div>
</form>
</div>
</div>
</div>
</section>
</div>
<?php include(APPPATH."views/footer.php");?>
<div class="control-sidebar-bg"></div>
</div>
<?php include(APPPATH."views/comman/code_js_sound.php");?>
<?php include(APPPATH."views/comman/code_js.php");?>
<script type="text/javascript">
var base_url="<?=$base_url;?>";
$("#save").on("click",function(){
    if(!$("#attribute_type").val() || !$("#attribute_value").val()){ alert("Attribute type and value are required."); return; }
    var data = new FormData($("#attribute-form")[0]);
    $.ajax({
        type:'POST', url:base_url+'attributes/save', data:data,
        cache:false, contentType:false, processData:false,
        success:function(res){
            if(res.indexOf("success")!==-1){ window.location.href = base_url+'attributes'; }
            else { alert(res); }
        }
    });
});
</script>
<script>$('.attributes-active-li').addClass('active');</script>
</body>
</html>
