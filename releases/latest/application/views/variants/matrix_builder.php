<!DOCTYPE html>
<html>
<head>
<?php $this->load->view('comman/code_css.php');?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar.php');?>
<div class="content-wrapper">
<section class="content-header">
<h1><?=$page_title;?><small>Generate size &times; colour combinations in one click</small></h1>
<ol class="breadcrumb">
<li><a href="<?=base_url('dashboard');?>"><i class="fa fa-dashboard"></i> Home</a></li>
<li><a href="<?=base_url('variants/view');?>"><?= $this->lang->line('variants_list'); ?></a></li>
<li class="active"><?=$page_title;?></li>
</ol>
</section>
<section class="content">
<div class="row">
<div class="col-md-12">
<div class="box box-info">
<div class="box-header with-border">
<h3 class="box-title"><i class="fa fa-th text-purple"></i> Variant Matrix Builder</h3>
</div>
<form class="form-horizontal" id="matrix-form" onkeypress="return event.keyCode != 13;">
<input type="hidden" name="<?=$this->security->get_csrf_token_name();?>" value="<?=$this->security->get_csrf_hash();?>">
<input type="hidden" id="base_url" value="<?=$base_url;?>">

<div class="box-body">
<div class="alert alert-info">
<i class="fa fa-info-circle"></i> Enter your sizes, colours and (optionally) materials below. MartPoint will generate every combination as a tracked variant — e.g. S / Red, S / Blue, M / Red, M / Blue.
</div>

<div class="form-group">
<label for="sizes" class="col-sm-2 control-label">Sizes <label class="text-danger">*</label></label>
<div class="col-sm-8">
<textarea class="form-control" id="sizes" name="sizes" rows="3" placeholder="S, M, L, XL, XXL&#10;or one per line">S, M, L, XL</textarea>
<span class="help-block text-muted">Separate with commas, semicolons or new lines.</span>
</div>
</div>

<div class="form-group">
<label for="colours" class="col-sm-2 control-label">Colours <label class="text-danger">*</label></label>
<div class="col-sm-8">
<textarea class="form-control" id="colours" name="colours" rows="3" placeholder="Red, Blue, Black, White&#10;or one per line">Red, Blue, Black</textarea>
</div>
</div>

<div class="form-group">
<label for="materials" class="col-sm-2 control-label">Materials <small class="text-muted">(optional)</small></label>
<div class="col-sm-8">
<textarea class="form-control" id="materials" name="materials" rows="2" placeholder="Cotton, Polyester, Silk (leave blank to skip)"></textarea>
</div>
</div>

<div class="form-group">
<label class="col-sm-2 control-label">Preview</label>
<div class="col-sm-8">
<div id="preview" class="well" style="min-height:60px;font-size:13px;color:#666;">
<span class="text-muted">Combinations will appear here as you type…</span>
</div>
</div>
</div>

</div>

<div class="box-footer">
<div class="col-sm-8 col-sm-offset-2 text-center">
<div class="col-md-3 col-md-offset-3">
<button type="button" id="generate" class="btn btn-block btn-success"><i class="fa fa-magic"></i> Generate Variants</button>
</div>
<div class="col-sm-3">
<a href="<?=base_url('variants/view');?>"><button type="button" class="btn btn-block btn-warning close_btn">Close</button></a>
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
function parse_list(val){
    return val.split(/[,;\n]/).map(function(s){return s.trim();}).filter(function(s){return s.length>0;});
}
function update_preview(){
    var sizes=parse_list($("#sizes").val());
    var colours=parse_list($("#colours").val());
    var materials=parse_list($("#materials").val());
    if(sizes.length===0 && colours.length===0){ $("#preview").html('<span class="text-muted">Enter sizes or colours to see combinations…</span>'); return; }
    if(sizes.length===0) sizes=[''];
    if(colours.length===0) colours=[''];
    if(materials.length===0) materials=[''];
    var combos=[];
    sizes.forEach(function(s){
        colours.forEach(function(c){
            materials.forEach(function(m){
                var parts=[];
                if(s) parts.push(s); if(c) parts.push(c); if(m) parts.push(m);
                if(parts.length) combos.push(parts.join(' / '));
            });
        });
    });
    var total=combos.length;
    var shown=combos.slice(0,30).map(function(x){return '<span class="label label-default" style="margin:2px;">'+$('<div>').text(x).html()+'</span>';}).join(' ');
    if(total>30) shown+=' <span class="text-muted">… and '+(total-30)+' more</span>';
    $("#preview").html('<b>'+total+'</b> combinations will be created:<br>'+shown);
}
$("#sizes,#colours,#materials").on("input",update_preview);
update_preview();

$("#generate").on("click",function(){
    if(!$("#sizes").val() && !$("#colours").val()){ alert("Please enter at least sizes or colours."); return; }
    if(!confirm("Generate all these variants now?")) return;
    var data=new FormData($("#matrix-form")[0]);
    $("#generate").attr('disabled',true).html('<i class="fa fa-spinner fa-spin"></i> Generating…');
    $.ajax({
        type:'POST', url:base_url+'variants/generate_matrix', data:data,
        cache:false, contentType:false, processData:false,
        success:function(res){
            $("#generate").attr('disabled',false).html('<i class="fa fa-magic"></i> Generate Variants');
            if(res.indexOf("success")!==-1){
                alert(res.split("<<<###>>>")[1] || "Variants generated successfully!");
                window.location.href=base_url+"variants/view";
            } else {
                alert(res);
            }
        },
        error:function(){ $("#generate").attr('disabled',false).html('<i class="fa fa-magic"></i> Generate Variants'); alert("Request failed. Please try again."); }
    });
});
</script>
<script>$(".<?php echo basename(__FILE__,'.php');?>-active-li").addClass("active");</script>
</body>
</html>
