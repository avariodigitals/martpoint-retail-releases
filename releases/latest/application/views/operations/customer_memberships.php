<!DOCTYPE html>
<html>
<head><?php $this->load->view('comman/code_css.php'); ?></head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar'); ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1><?= $page_title; ?><small>Active Members &amp; Renewals</small></h1>
    <ol class="breadcrumb">
      <li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="<?= base_url('operations/memberships'); ?>">Memberships</a></li>
      <li class="active"><?= $page_title; ?></li>
    </ol>
  </section>
  <section class="content">
    <div class="row">
      <div class="col-lg-6 col-xs-6">
        <div class="small-box bg-green">
          <div class="inner"><h3><?= $active_count; ?></h3><p>Active Members</p></div>
          <div class="icon"><i class="fa fa-users"></i></div>
        </div>
      </div>
      <div class="col-lg-6 col-xs-6">
        <div class="small-box bg-yellow">
          <div class="inner"><h3><?= $expiring_count; ?></h3><p>Expiring Soon</p></div>
          <div class="icon"><i class="fa fa-clock-o"></i></div>
        </div>
      </div>
    </div>
    <div class="row">
      <div class="col-md-12">
        <div class="box box-info">
          <div class="box-header with-border">
            <h3 class="box-title">Customer Memberships</h3>
            <div class="box-tools pull-right">
              <a class="btn btn-sm btn-primary" href="<?= base_url('operations/assign_membership'); ?>"><i class="fa fa-user-plus"></i> Assign Membership</a>
              <a class="btn btn-sm btn-default" href="<?= base_url('operations/memberships'); ?>"><i class="fa fa-cog"></i> Plans</a>
            </div>
          </div>
          <div class="box-body">
            <table id="members-table" class="table table-bordered table-striped" width="100%">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Customer</th>
                  <th>Plan</th>
                  <th>Period</th>
                  <th>Status</th>
                  <th>Auto-Renew</th>
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
  $('#members-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: { url: "<?= base_url('operations/customer_memberships_ajax'); ?>", type: "POST" },
    columnDefs: [ { targets: [0,4,5,6], orderable: false },
                  { targets: 6, className: 'text-center' } ],
    pageLength: 25,
    order: [[3, 'asc']],
    language: { emptyTable: "No memberships found. Assign one to get started." }
  });
});

function cancel_membership(id) {
  if (!confirm('Cancel this membership? The customer will lose benefits immediately.')) return;
  $.post("<?= base_url('operations/membership_cancel'); ?>", { id: id, <?= csrf_token(); ?>: "<?= csrf_hash(); ?>" }, function(res){
    if (res.success) { $('#members-table').DataTable().ajax.reload(null, false); toastr.success(res.message); }
    else { toastr.error(res.message); }
  }, 'json');
}

$(".memberships-active-li").addClass("active");
</script>
</body>
</html>
