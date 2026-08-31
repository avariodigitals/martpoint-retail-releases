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
<?php if($CI->permissions('gift_cards_add')) { ?>
<div class="box-tools">
<a class="btn btn-block btn-info" href="<?=base_url();?>gift_cards/add"><i class="fa fa-plus"></i> Add Gift Card</a>
</div>
<?php } ?>
</div>
<div class="box-body">
<table id="example2" class="table table-bordered custom_hover" width="100%">
<thead class="bg-gray"><tr><th>#</th><th>Card Number</th><th>Customer</th><th>Initial Value</th><th>Balance</th><th>Issue Date</th><th>Expiry</th><th>Type</th><th>Status</th><th>Action</th></tr></thead>
<tbody></tbody>
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
<script>
function load_datatable(){
    var table = $('#example2').DataTable({
        "aLengthMenu": [[10, 25, 50, 100], [10, 25, 50, 100]],
        dom:'<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr>>>tip',
        "processing": true,
        "serverSide": true,
        "order": [],
        "responsive": true,
        "ajax": {"url": "<?=site_url('gift_cards/ajax_list')?>", "type": "POST"},
        "columnDefs": [{ "targets": [0,9], "orderable": false, }]
    });
}
$(document).ready(function() { load_datatable(); });
function cancel_card(id){
    swal({
        title: "Are you sure?",
        text: "Cancel this gift card?",
        type: "warning",
        showCancelButton: true,
        confirmButtonColor: "#DD6B55",
        confirmButtonText: "Yes, cancel it!",
        cancelButtonText: "No",
        closeOnConfirm: true
    }, function(isConfirm){
        if(isConfirm){
            $.post(base_url + 'gift_cards/cancel_card', {id:id}, function(res){
                if(res=='success'){ success_show('Card cancelled'); $('#example2').DataTable().ajax.reload(); }
                else{ error_show('Failed'); }
            });
        }
    });
}
</script>
</body>
</html>
