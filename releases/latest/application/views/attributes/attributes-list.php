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
<li class="active"><?=$page_title;?></li>
</ol>
</section>
<section class="content">
<div class="row">
<div class="col-md-12">
<div class="box box-primary">
<div class="box-header with-border">
<h3 class="box-title"><?= $this->lang->line('attributes_list'); ?></h3>
<a href="<?=base_url('attributes/add');?>" class="btn btn-info pull-right"><i class="fa fa-plus"></i> <?= $this->lang->line('attributes_add'); ?></a>
</div>
<div class="box-body">
<table class="table table-bordered table-hover" id="data-list-table">
<thead>
<tr class="bg-blue">
<th><input type="checkbox" id="select_all"></th>
<th>Type</th>
<th>Value</th>
<th>Sort</th>
<th>Status</th>
<th>Action</th>
</tr>
</thead>
<tbody id="tbodyid"></tbody>
</table>
</div>
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
$(document).ready(function(){
    $('#data-list-table').DataTable({
        "aLengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        "processing": true,
        "serverSide": true,
        "responsive": true,
        "order": [],
        "ajax": { url: base_url+"attributes/ajax_list", type: "POST" },
        "columnDefs": [{ "orderable": false, "targets": [0,5] }],
        "drawCallback": function(){
            $('.column_checkbox').iCheck({ checkboxClass: 'icheckbox_square-orange', radioClass: 'iradio_square-orange' });
        }
    });
    $("#select_all").on("click",function(){
        $(".column_checkbox").prop("checked", this.checked);
    });
});
function delete_attribute(id){
    if(!confirm("Delete this attribute?")) return;
    $.post(base_url+"attributes/delete",{q_id:id,<?=$this->security->get_csrf_token_name();?>:'<?=$this->security->get_csrf_hash();?>'},function(res){
        if(res.indexOf("success")!==-1){ $('#data-list-table').DataTable().ajax.reload(); }
        else { alert(res); }
    });
}
</script>
<script>$('.attributes-active-li').addClass('active');</script>
</body>
</html>
