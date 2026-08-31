<!DOCTYPE html>
<html>
<head><?php $this->load->view('comman/code_css.php'); ?></head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar'); ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1><?= $page_title; ?><small>Bakery Batch Scheduling</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li><li class="active"><?= $page_title; ?></li></ol>
  </section>
  <section class="content">
    <div class="row">
      <div class="col-lg-2 col-xs-6"><div class="small-box bg-gray"><div class="inner"><h3><?= $counts['planned'] ?? 0; ?></h3><p>Planned</p></div><div class="icon"><i class="fa fa-calendar-o"></i></div></div></div>
      <div class="col-lg-2 col-xs-6"><div class="small-box bg-aqua"><div class="inner"><h3><?= $counts['prepping'] ?? 0; ?></h3><p>Prepping</p></div><div class="icon"><i class="fa fa-cut"></i></div></div></div>
      <div class="col-lg-2 col-xs-6"><div class="small-box bg-blue"><div class="inner"><h3><?= $counts['in_production'] ?? 0; ?></h3><p>In Production</p></div><div class="icon"><i class="fa fa-fire"></i></div></div></div>
      <div class="col-lg-2 col-xs-6"><div class="small-box bg-yellow"><div class="inner"><h3><?= $counts['ready'] ?? 0; ?></h3><p>Ready</p></div><div class="icon"><i class="fa fa-check-circle"></i></div></div></div>
      <div class="col-lg-2 col-xs-6"><div class="small-box bg-green"><div class="inner"><h3><?= $counts['completed'] ?? 0; ?></h3><p>Completed</p></div><div class="icon"><i class="fa fa-flag-checkered"></i></div></div></div>
    </div>

    <div class="row">
      <div class="col-md-4">
        <div class="box box-warning">
          <div class="box-header with-border"><h3 class="box-title">Pending Orders</h3><small class="text-muted"> waiting to be scheduled</small></div>
          <div class="box-body" style="max-height:400px;overflow-y:auto;">
            <?php if(!empty($pending_items)){ ?>
            <table class="table table-condensed table-striped">
              <thead class="bg-gray"><tr><th>Order #</th><th>Item</th><th>Due</th><th></th></tr></thead>
              <tbody>
              <?php foreach($pending_items as $po){ ?>
                <tr>
                  <td><span class="label label-default"><?= htmlspecialchars($po->order_code); ?></span></td>
                  <td><?= htmlspecialchars($po->item_name ?: '-'); ?></td>
                  <td><?= show_date($po->due_date); ?></td>
                  <td><a href="<?= base_url('operations/production_batch?order_id='.$po->id); ?>" class="btn btn-xs btn-success">Batch</a></td>
                </tr>
              <?php } ?>
              </tbody>
            </table>
            <?php } else { ?>
              <p class="text-muted text-center">No pending orders to schedule.</p>
            <?php } ?>
          </div>
        </div>
      </div>
      <div class="col-md-8">
        <div class="box box-info">
          <div class="box-header with-border">
            <h3 class="box-title">Production Schedule</h3>
            <div class="box-tools pull-right">
              <form method="get" class="form-inline" style="display:inline;">
                <input type="date" name="from" class="form-control input-sm" value="<?= $date_from; ?>" style="width:130px;">
                <input type="date" name="to" class="form-control input-sm" value="<?= $date_to; ?>" style="width:130px;">
                <select name="status" class="form-control input-sm" style="width:120px;">
                  <option value="">All Status</option>
                  <?php foreach(Production_batches_model::get_statuses() as $st): ?>
                  <option value="<?= $st; ?>" <?= ($status_filter==$st)?'selected':''; ?>><?= Production_batches_model::status_label($st); ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-filter"></i></button>
              </form>
              <a href="<?= base_url('operations/production_batch'); ?>" class="btn btn-sm btn-success"><i class="fa fa-plus"></i> New Batch</a>
            </div>
          </div>
          <div class="box-body">
            <div class="table-responsive">
              <table id="batches-table" class="table table-bordered table-striped">
                <thead><tr><th>#</th><th>Batch #</th><th>Name</th><th>Date</th><th>Status</th><th>Stock Check</th><th>Equipment</th><th>Staff</th><th>Action</th></tr></thead>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
<?php $this->load->view('footer'); ?>
</div>
<?php $this->load->view('comman/code_js.php'); ?>
<script src="<?= base_url(); ?>theme/plugins/DataTables-1.10.18/js/jquery.dataTables.min.js"></script>
<script src="<?= base_url(); ?>theme/plugins/DataTables-1.10.18/js/dataTables.bootstrap.min.js"></script>
<script>
var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
var csrfHash = '<?= $this->security->get_csrf_hash(); ?>';
$(function(){
  var table = $('#batches-table').DataTable({
    processing: true, serverSide: true,
    ajax: { url: "<?= base_url('operations/production_batches_ajax'); ?>", type: "POST",
      data: function(d) {
        d[csrfName] = csrfHash;
      }
    },
    columnDefs: [{ orderable: false, targets: [4,5,8] }],
    autoWidth: false
  });

  // Handle status dropdown change
  $('#batches-table tbody').on('change', '.batch-status-select', function(){
    var $sel = $(this);
    var batchId = $sel.data('batch-id');
    var currentStatus = $sel.data('current-status');
    var newStatus = $sel.val();
    if(newStatus === currentStatus) return;

    var statusOrder = <?= json_encode(array_values(Production_batches_model::get_statuses())); ?>;
    var currIdx = statusOrder.indexOf(currentStatus);
    var newIdx = statusOrder.indexOf(newStatus);
    var isBackward = newIdx < currIdx;

    var confirmTitle = isBackward ? 'Move Status Backward?' : 'Change Status?';
    var confirmText = isBackward
      ? 'You are moving this batch BACKWARD from "' + currentStatus + '" to "' + newStatus + '". This usually requires manager approval.'
      : 'Change batch status to "' + newStatus + '"?';
    var confirmBtn = isBackward ? 'Yes, Move Backward' : 'Yes, Change';

    swal({
      title: confirmTitle,
      text: confirmText,
      icon: isBackward ? 'warning' : 'info',
      buttons: ['No, Cancel', confirmBtn],
      dangerMode: isBackward
    }).then(function(isConfirm){
      if(!isConfirm){
        $sel.val(currentStatus); // revert
        return;
      }
      $.post('<?= base_url('operations/production_batch_quick_status'); ?>', {
        id: batchId,
        status: newStatus,
        current_status: currentStatus,
        "<?= $this->security->get_csrf_token_name(); ?>": "<?= $this->security->get_csrf_hash(); ?>"
      }, function(res){
        if(res.success){
          toastr.success(res.message);
          $sel.data('current-status', newStatus);
          if(res.csrf_hash) csrfHash = res.csrf_hash;
          table.ajax.reload(null, false);
        } else {
          toastr.error(res.message || 'Failed to update status');
          $sel.val(currentStatus); // revert
        }
      }, 'json').fail(function(xhr){
        toastr.error('Server error: ' + (xhr.responseText ? xhr.responseText.substring(0,200) : 'Could not reach server'));
        $sel.val(currentStatus);
      });
    });
  });
});
function delete_production_batch(id) {
  swal({
    title: 'Delete Production Batch?',
    text: 'This action cannot be undone.',
    icon: 'warning',
    buttons: ['No, Cancel', 'Yes, Delete'],
    dangerMode: true
  }).then(function(isConfirm){
    if(!isConfirm) return;
    $.post('<?= base_url('operations/production_batch_delete'); ?>', {
      id: id, "<?= $this->security->get_csrf_token_name(); ?>": "<?= $this->security->get_csrf_hash(); ?>"
    }, function(res){
      if(res.success) { toastr.success(res.message); $('#batches-table').DataTable().ajax.reload(); }
      else { toastr.error(res.message || 'Failed'); }
    }, 'json');
  });
}
</script>
</body>
</html>
