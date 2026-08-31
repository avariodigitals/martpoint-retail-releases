<!DOCTYPE html>
<html>
<head>
<?php $this->load->view('comman/code_css');?>
<style>
  .installment-badge { display: inline-block; padding: 3px 8px; border-radius: 3px; font-size: 12px; font-weight: 600; }
  .badge-active { background: #00c0ef; color: #fff; }
  .badge-completed { background: #00a65a; color: #fff; }
  .badge-overdue { background: #dd4b39; color: #fff; }
  .badge-cancelled { background: #777; color: #fff; }
</style>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

<?php $this->load->view('sidebar');?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1>Installment Plans <small>Buy Now Pay Later</small></h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
        <li><a href="<?php echo $base_url; ?>installments">Installments</a></li>
        <li class="active">All Plans</li>
      </ol>
    </section>
    <section class="content">
      <div class="row">
        <div class="col-md-12">
          <div class="box box-info">
            <div class="box-header with-border">
              <h3 class="box-title"><i class="fa fa-calendar-check-o"></i> All Plans</h3>
              <div class="box-tools pull-right">
                <button class="btn btn-sm btn-warning" onclick="checkOverdue()">
                  <i class="fa fa-clock-o"></i> Check Overdue
                </button>
              </div>
            </div>
            <div class="box-body">
              <table id="installment_table" class="table table-bordered table-striped datatable">
                <thead>
                  <tr>
                    <th>Plan Code</th>
                    <th>Customer</th>
                    <th>Total</th>
                    <th>Down Payment</th>
                    <th>Schedule</th>
                    <th>Frequency</th>
                    <th>First Due</th>
                    <th>Status</th>
                    <th>Balance</th>
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
<?php $this->load->view('footer'); ?>
</div>
<?php $this->load->view('comman/code_js');?>
<script>
$(document).ready(function(){
  var table = $('#installment_table').DataTable({
    processing: true,
    serverSide: false,
    ajax: {
      url: "<?= base_url('installments/ajax_list'); ?>",
      type: "POST",
      data: function(d){
        d.<?= $this->security->get_csrf_token_name(); ?> = "<?= $this->security->get_csrf_hash(); ?>";
      }
    },
    columnDefs: [
      { orderable: false, targets: [9] }
    ]
  });
});

function checkOverdue(){
  $.post("<?= base_url('installments/check_overdue'); ?>", {
    <?= $this->security->get_csrf_token_name(); ?>: "<?= $this->security->get_csrf_hash(); ?>"
  }, function(res){
    var data = JSON.parse(res);
    toastr.info(data.overdue_updated + ' installment(s) marked overdue.', 'Overdue Check');
    $('#installment_table').DataTable().ajax.reload();
  });
}
</script>
<!-- Make sidebar menu highlighter/selector -->
<script>$(".installments-active-li").addClass("active");</script>
</body>
</html>
