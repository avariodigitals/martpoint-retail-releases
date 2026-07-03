<!DOCTYPE html>
<html>
<head>
<?php include"comman/code_css.php"; ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php include"sidebar.php"; ?>
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      <?=$page_title;?>
      <small></small>
    </h1>
    <ol class="breadcrumb">
      <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active"><?=$page_title;?></li>
    </ol>
  </section>
  <!-- Main content -->
  <section class="content">
    <!-- ********** ALERT MESSAGE START******* -->
    <?php $this->load->view('comman/code_flashdata');?>
    <!-- ********** ALERT MESSAGE END******* -->
    <div class="row">
      <div class="col-md-12">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title"><?=$page_title;?></h3>
          <div class="box-tools pull-right">
            <?php if($CI->permissions('service_packages_add')) { ?>
            <a class="btn btn-block btn-primary" href="<?php echo base_url('service_packages/add'); ?>">
              <i class="fa fa-plus"></i> New Package
            </a>
            <?php } ?>
          </div>
        </div>
        <div class="box-body">
          <table id="package_table" class="table table-bordered table-striped" width="100%">
            <thead>
              <tr>
                <th>#</th>
                <th>Code</th>
                <th>Package Name</th>
                <th>Pricing</th>
                <th>Price</th>
                <th>Type</th>
                <th>Expiry</th>
                <th>Status</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</section>
</div>
<!-- /.content-wrapper -->
<?php include"footer.php"; ?>
<div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->
<!-- SOUND CODE -->
<?php include"comman/code_js_sound.php"; ?>
<!-- TABLES CODE -->
<?php include"comman/code_js.php"; ?>
<script>
$(document).ready(function() {
  var table = $('#package_table').DataTable({
    "processing": true,
    "serverSide": true,
    "ajax": {
      url: "<?php echo base_url('service_packages/ajax_list'); ?>",
      type: "POST",
    },
    "columnDefs": [
      { "orderable": false, "targets": [0, 8] },
      { "className": "text-center", "targets": [0, 3, 5, 6, 7, 8] }
    ],
    "order": [[1, 'asc']]
  });
});

function update_status(id, status) {
  $.post("<?php echo base_url('service_packages/update_status'); ?>", { id: id, status: status, '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>' }, function(result) {
    if(result == 'success') {
      toastr['success']('Status updated.');
      $('#package_table').DataTable().ajax.reload();
    } else {
      toastr['error']('Failed to update status.');
    }
  });
}

function delete_package(id) {
  if(confirm("Are you sure?\nYou won't be able to revert this!")) {
    $.post("<?php echo base_url('service_packages/delete_package'); ?>", { q_id: id, '<?php echo $this->security->get_csrf_token_name(); ?>': '<?php echo $this->security->get_csrf_hash(); ?>' }, function(result) {
      if(result == 'success') {
        toastr['success']('Package deleted successfully.');
        $('#package_table').DataTable().ajax.reload();
      } else {
        toastr['error']('Failed to delete package.');
      }
    });
  }
}
</script>
</body>
</html>
