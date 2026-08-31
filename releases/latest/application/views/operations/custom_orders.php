<!DOCTYPE html>
<html>
<head><?php $this->load->view('comman/code_css.php'); ?></head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar'); ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1><?= $page_title; ?><small>Feature Preview — Full engine coming in Phase 4</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li><li class="active"><?= $page_title; ?></li></ol>
  </section>
  <section class="content">
    <div class="row"><div class="col-md-12">
      <div class="box box-info">
        <div class="box-header with-border"><h3 class="box-title">Custom Orders</h3></div>
        <div class="box-body">
          <div class="row">
            <div class="col-lg-2 col-xs-6"><div class="small-box bg-gray"><div class="inner"><h3><?= $counts['new'] ?? 0; ?></h3><p>New</p></div><div class="icon"><i class="fa fa-file-o"></i></div></div></div>
            <div class="col-lg-2 col-xs-6"><div class="small-box bg-aqua"><div class="inner"><h3><?= $counts['quoted'] ?? 0; ?></h3><p>Quoted</p></div><div class="icon"><i class="fa fa-calculator"></i></div></div></div>
            <div class="col-lg-2 col-xs-6"><div class="small-box bg-yellow"><div class="inner"><h3><?= $counts['deposit_paid'] ?? 0; ?></h3><p>Deposit Paid</p></div><div class="icon"><i class="fa fa-money"></i></div></div></div>
            <div class="col-lg-2 col-xs-6"><div class="small-box bg-blue"><div class="inner"><h3><?= $counts['in_production'] ?? 0; ?></h3><p>In Production</p></div><div class="icon"><i class="fa fa-cogs"></i></div></div></div>
            <div class="col-lg-2 col-xs-6"><div class="small-box bg-green"><div class="inner"><h3><?= $counts['ready'] ?? 0; ?></h3><p>Ready</p></div><div class="icon"><i class="fa fa-check-circle"></i></div></div></div>
            <div class="col-lg-2 col-xs-6"><div class="small-box bg-teal"><div class="inner"><h3><?= $counts['delivered'] ?? 0; ?></h3><p>Delivered</p></div><div class="icon"><i class="fa fa-truck"></i></div></div></div>
          </div>
          <div class="row">
            <div class="col-md-12">
              <div class="box box-info">
                <div class="box-header with-border">
                  <h3 class="box-title">Custom Orders</h3>
                  <div class="box-tools pull-right">
                    <a href="<?= base_url('operations/custom_order'); ?>" class="btn btn-sm btn-success"><i class="fa fa-plus"></i> New Order</a>
                  </div>
                </div>
                <div class="box-body">
                  <div class="table-responsive">
                    <table id="orders-table" class="table table-bordered table-striped">
                      <thead><tr><th>#</th><th>Order #</th><th>Customer</th><th>Item</th><th>Status</th><th>Due Date</th><th>Total</th><th>Action</th></tr></thead>
                    </table>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div></div>
  </section>
</div>
</div>
<?php $this->load->view('comman/code_js.php'); ?>
<script src="<?= base_url(); ?>theme/plugins/DataTables-1.10.18/js/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>theme/plugins/DataTables-1.10.18/js/dataTables.bootstrap.min.js"></script>
<script>
$(function(){
  $('#orders-table').DataTable({
    processing: true, serverSide: true,
    ajax: { url: "<?= base_url('operations/custom_orders_ajax'); ?>", type: "POST",
      data: { "<?= $this->security->get_csrf_token_name(); ?>": "<?= $this->security->get_csrf_hash(); ?>" }
    },
    columnDefs: [{ orderable: false, targets: [7] }],
    autoWidth: false
  });
});
function delete_custom_order(id) {
  if(!confirm('Delete this custom order?')) return;
  $.post('<?= base_url('operations/custom_order_delete'); ?>', {
    id: id, "<?= $this->security->get_csrf_token_name(); ?>": "<?= $this->security->get_csrf_hash(); ?>"
  }, function(res){
    if(res.success) { toastr.success(res.message); $('#orders-table').DataTable().ajax.reload(); }
    else { toastr.error(res.message || 'Failed'); }
  }, 'json');
}
</script>
<script>$(".custom-orders-active-li").addClass("active");</script>
</body>
</html>
