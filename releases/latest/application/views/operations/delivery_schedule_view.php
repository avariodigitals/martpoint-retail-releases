<!DOCTYPE html>
<html>
<head><?php $this->load->view('comman/code_css.php'); ?></head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar'); ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1><?= $page_title; ?></h1>
    <ol class="breadcrumb">
      <li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="<?= base_url('operations/delivery_scheduling'); ?>">Delivery Scheduling</a></li>
      <li class="active"><?= $page_title; ?></li>
    </ol>
  </section>
  <section class="content">
    <div class="row">
      <div class="col-md-3">
        <div class="box box-primary">
          <div class="box-header with-border"><h3 class="box-title">Route Info</h3></div>
          <div class="box-body">
            <p><b>Code:</b> <?= $schedule_code; ?></p>
            <p><b>Route:</b> <?= $route_name ?: '-'; ?></p>
            <p><b>Date:</b> <?= $schedule_date; ?></p>
            <p><b>Driver:</b> <?= $driver_name ?: '<span class="text-muted">Unassigned</span>'; ?></p>
            <p><b>Vehicle:</b> <?= $vehicle ?: '-'; ?></p>
            <p><b>Status:</b>
              <?php $badge = ['planned'=>'default','ready'=>'info','out_for_delivery'=>'warning','completed'=>'success','cancelled'=>'danger'][$status] ?? 'default'; ?>
              <span class="label label-<?= $badge; ?>"><?= ucwords(str_replace('_',' ',$status)); ?></span>
            </p>
            <?php if(!empty($notes)) { ?><p><b>Notes:</b> <?= nl2br($notes); ?></p><?php } ?>
          </div>
          <div class="box-footer">
            <a href="<?= base_url('operations/delivery_schedule_form/'.$q_id); ?>" class="btn btn-primary btn-block"><i class="fa fa-edit"></i> Edit Route</a>
            <?php if($status != 'completed' && $status != 'cancelled') { ?>
            <div class="btn-group btn-block" style="margin-top:8px;">
              <button type="button" class="btn btn-warning dropdown-toggle btn-block" data-toggle="dropdown">Update Status <span class="caret"></span></button>
              <ul class="dropdown-menu btn-block">
                <li><a href="#" onclick="updateStatus('ready')">Ready</a></li>
                <li><a href="#" onclick="updateStatus('out_for_delivery')">Out for Delivery</a></li>
                <li><a href="#" onclick="updateStatus('completed')">Completed</a></li>
                <li><a href="#" onclick="updateStatus('cancelled')">Cancelled</a></li>
              </ul>
            </div>
            <?php } ?>
          </div>
        </div>
      </div>
      <div class="col-md-9">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-list"></i> Delivery Stops</h3>
            <div class="box-tools pull-right">
              <span class="badge bg-green"><?= count(array_filter($schedule_items, function($i){return $i->delivery_status=='delivered';})); ?></span> of <?= count($schedule_items); ?> delivered
            </div>
          </div>
          <div class="box-body table-responsive no-padding">
            <?php if(empty($schedule_items)) { ?>
              <div class="alert alert-info text-center">No orders assigned to this route.</div>
            <?php } else { ?>
            <table class="table table-hover table-bordered">
              <thead>
                <tr>
                  <th style="width:40px;">#</th>
                  <th>Order</th>
                  <th>Customer</th>
                  <th>Address / Phone</th>
                  <th>Status</th>
                  <th>Delivered</th>
                  <th style="width:120px;">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($schedule_items as $idx => $item) {
                  $status_color = ['pending'=>'default','out_for_delivery'=>'warning','delivered'=>'success','failed'=>'danger','cancelled'=>'danger'][$item->delivery_status] ?? 'default';
                ?>
                <tr>
                  <td class="text-center"><span class="badge bg-blue"><?= $item->delivery_sequence; ?></span></td>
                  <td><b><?= $item->sales_code; ?></b></td>
                  <td><?= $item->customer_name; ?></td>
                  <td>
                    <?= !empty($item->address) ? nl2br($item->address) : '<span class="text-muted">No address</span>'; ?><br>
                    <small class="text-muted"><?= $item->phone ?: '-'; ?></small>
                  </td>
                  <td><span class="label label-<?= $status_color; ?>"><?= ucwords(str_replace('_',' ',$item->delivery_status)); ?></span></td>
                  <td><?= !empty($item->delivered_at) ? date('h:i A', strtotime($item->delivered_at)) : '-'; ?></td>
                  <td>
                    <?php if($item->delivery_status != 'delivered' && $item->delivery_status != 'cancelled') { ?>
                    <div class="btn-group">
                      <button type="button" class="btn btn-xs btn-success dropdown-toggle" data-toggle="dropdown"><i class="fa fa-check"></i> Mark</button>
                      <ul class="dropdown-menu">
                        <li><a href="#" onclick="markItem(<?= $item->id; ?>, 'out_for_delivery')">Out for Delivery</a></li>
                        <li><a href="#" onclick="markItem(<?= $item->id; ?>, 'delivered')">Delivered</a></li>
                        <li><a href="#" onclick="markItem(<?= $item->id; ?>, 'failed')">Failed</a></li>
                      </ul>
                    </div>
                    <?php } else { ?>
                    <span class="text-muted"><i class="fa fa-check-circle"></i> Done</span>
                    <?php } ?>
                  </td>
                </tr>
                <?php if(!empty($item->delivery_notes)) { ?>
                <tr class="bg-gray-light"><td colspan="7"><small><b>Note:</b> <?= nl2br($item->delivery_notes); ?></small></td></tr>
                <?php } ?>
                <?php } ?>
              </tbody>
            </table>
            <?php } ?>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
</div>
<?php $this->load->view('comman/code_js.php'); ?>
<script>
function updateStatus(status) {
  $.post("<?= base_url('operations/delivery_schedule_status'); ?>", {
    id: <?= $q_id; ?>,
    status: status,
    '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
  }, function(res) {
    if (res == 'success') { toastr['success']('Status updated.'); location.reload(); }
    else { toastr['error']('Failed.'); }
  });
}

function markItem(itemId, status) {
  var notes = '';
  if (status == 'failed') {
    notes = prompt('Reason for failure?');
    if (notes === null) return;
  }
  $.post("<?= base_url('operations/ajax_mark_delivered'); ?>", {
    item_id: itemId,
    status: status,
    notes: notes,
    '<?= $this->security->get_csrf_token_name(); ?>': '<?= $this->security->get_csrf_hash(); ?>'
  }, function(res) {
    if (res == 'success') { toastr['success']('Updated.'); location.reload(); }
    else { toastr['error']('Failed.'); }
  });
}
</script>
</body>
</html>
