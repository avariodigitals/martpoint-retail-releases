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
<div class="box-header with-border"><h3 class="box-title">Points History</h3></div>
<div class="box-body">
<table id="example2" class="table table-bordered custom_hover" width="100%">
<thead class="bg-gray"><tr><th>#</th><th>Customer</th><th>Type</th><th>Points</th><th>Balance</th><th>Description</th><th>Date</th></tr></thead>
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
        dom:'<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr><"pull-right margin-left-10"B>>>tip',
        buttons: [
            { extend: 'copy', className: 'btn bg-teal color-palette btn-flat' },
            { extend: 'excel', className: 'btn bg-teal color-palette btn-flat' },
            { extend: 'pdf', className: 'btn bg-teal color-palette btn-flat' },
            { extend: 'csv', className: 'btn bg-teal color-palette btn-flat' },
        ],
        "processing": true,
        "serverSide": true,
        "order": [],
        "responsive": true,
        "ajax": {
            "url": "<?=site_url('loyalty/ajax_points_history')?>",
            "type": "POST",
            "data": { customer_id: "<?=$customer_id;?>" }
        },
        "columnDefs": [{ "targets": [0], "orderable": false, }]
    });
}
$(document).ready(function() { load_datatable(); });
</script>
</body>
</html>
