<!DOCTYPE html>
<html>
<head><?php $this->load->view('comman/code_css.php'); ?></head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar'); ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1><?= $page_title; ?><small>Recurring Plans &amp; Benefits</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li><li class="active"><?= $page_title; ?></li></ol>
  </section>
  <section class="content">
    <div class="row">
      <div class="col-lg-4 col-xs-6">
        <div class="small-box bg-green">
          <div class="inner">
            <h3><?= $active_count; ?></h3>
            <p>Active Members</p>
          </div>
          <div class="icon"><i class="fa fa-users"></i></div>
          <a href="<?= base_url('operations/customer_memberships'); ?>" class="small-box-footer">View Members <i class="fa fa-arrow-circle-right"></i></a>
        </div>
      </div>
      <div class="col-lg-4 col-xs-6">
        <div class="small-box bg-yellow">
          <div class="inner">
            <h3><?= $expiring_count; ?></h3>
            <p>Expiring Soon (7 days)</p>
          </div>
          <div class="icon"><i class="fa fa-clock-o"></i></div>
          <a href="<?= base_url('operations/customer_memberships'); ?>" class="small-box-footer">View Members <i class="fa fa-arrow-circle-right"></i></a>
        </div>
      </div>
      <div class="col-lg-4 col-xs-6">
        <div class="small-box bg-aqua">
          <div class="inner">
            <h3><?= count($plans); ?></h3>
            <p>Plans Available</p>
          </div>
          <div class="icon"><i class="fa fa-id-card"></i></div>
          <a href="<?= base_url('operations/membership_plan'); ?>" class="small-box-footer">Add Plan <i class="fa fa-arrow-circle-right"></i></a>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="box box-info">
          <div class="box-header with-border">
            <h3 class="box-title">Membership Plans</h3>
            <div class="box-tools pull-right">
              <a class="btn btn-sm btn-primary" href="<?= base_url('operations/membership_plan'); ?>"><i class="fa fa-plus"></i> New Plan</a>
              <a class="btn btn-sm btn-success" href="<?= base_url('operations/customer_memberships'); ?>"><i class="fa fa-users"></i> Members</a>
              <a class="btn btn-sm btn-info" href="<?= base_url('operations/assign_membership'); ?>"><i class="fa fa-user-plus"></i> Assign</a>
            </div>
          </div>
          <div class="box-body">
            <table id="plans-table" class="table table-bordered table-striped" width="100%">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Plan</th>
                  <th>Price</th>
                  <th>Cycle</th>
                  <th>Benefit</th>
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
</div>
<?php $this->load->view('comman/code_js.php'); ?>
<script>
$(function(){
  var table = $('#plans-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: { url: "<?= base_url('operations/membership_plans_ajax'); ?>", type: "POST" },
    columnDefs: [ { targets: [0,4,5,6], orderable: false },
                  { targets: 6, className: 'text-center' } ],
    pageLength: 10,
    language: { emptyTable: "No plans found. Create your first membership plan." }
  });
});

function delete_plan(id) {
  if (!confirm('Deactivate this plan? It will no longer appear for new signups.')) return;
  $.post("<?= base_url('operations/membership_plan_delete'); ?>", { id: id, <?= csrf_token(); ?>: "<?= csrf_hash(); ?>" }, function(res){
    if (res.success) { $('#plans-table').DataTable().ajax.reload(null, false); toastr.success(res.message); }
    else { toastr.error(res.message); }
  }, 'json');
}

function toggle_plan_status(id, status) {
  $.post("<?= base_url('operations/membership_plan_toggle_status'); ?>", { id: id, status: status, <?= csrf_token(); ?>: "<?= csrf_hash(); ?>" }, function(res){
    if (res.success) { $('#plans-table').DataTable().ajax.reload(null, false); toastr.success('Status updated.'); }
  }, 'json');
}

$(".memberships-active-li").addClass("active");
</script>
</body>
</html>
