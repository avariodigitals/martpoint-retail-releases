<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.mp-timeline{position:relative!important;padding-left:24px!important;margin:8px 0!important}
.mp-timeline:before{content:'';position:absolute;left:8px;top:4px;bottom:4px;width:2px;background:var(--mp-border)}
.mp-timeline .tl-item{position:relative!important;padding:0 0 16px 8px!important}
.mp-timeline .tl-item:before{content:'';position:absolute;left:-16px;top:4px;width:10px;height:10px;border-radius:50%;background:var(--mp-primary);border:2px solid var(--mp-surface)}
.mp-timeline .tl-item .tl-time{font-size:11px;color:var(--mp-muted)}
.mp-timeline .tl-item .tl-label{font-size:13px;font-weight:600;color:var(--mp-text)}
.mp-info-row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--mp-border);font-size:13px}
.mp-info-row:last-child{border-bottom:0}
.mp-info-row b{color:var(--mp-ink)}
.mp-info-row span{color:var(--mp-text);text-align:right}
.mp-status-btn{display:flex!important;align-items:center!important;gap:10px!important;width:100%!important;padding:12px 16px!important;border:1px solid var(--mp-border)!important;border-radius:10px!important;background:var(--mp-surface)!important;color:var(--mp-ink)!important;font-size:14px!important;font-weight:600!important;cursor:pointer!important;margin-bottom:8px!important;transition:all .15s ease!important;text-align:left!important}
.mp-status-btn:hover{background:var(--mp-bg)!important}
.mp-status-btn .dot{width:10px;height:10px;border-radius:50%;flex-shrink:0}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Delivery route detail & stop tracking</div>
  </div>
  <a href="<?= base_url('operations/delivery_scheduling'); ?>" class="mp-qa-btn blue"><i class="fa fa-arrow-left"></i> Back to Schedules</a>
</div>

<div style="display:grid;grid-template-columns:320px minmax(0,1fr);gap:24px;align-items:start;">
  <div>
    <div class="mp-card-form" style="margin-bottom:24px">
      <div class="mp-card-head"><h3>Route Info</h3></div>
      <div class="mp-card-body">
        <div class="mp-info-row"><b>Code</b><span><?= $schedule_code; ?></span></div>
        <div class="mp-info-row"><b>Route</b><span><?= $route_name ?: '-'; ?></span></div>
        <div class="mp-info-row"><b>Date</b><span><?= $schedule_date; ?></span></div>
        <div class="mp-info-row"><b>Driver</b><span><?= $driver_name ?: '<span class="text-muted">Unassigned</span>'; ?></span></div>
        <div class="mp-info-row"><b>Vehicle</b><span><?= $vehicle ?: '-'; ?></span></div>
        <div class="mp-info-row"><b>Status</b><span>
          <?php $badge = ['planned'=>'default','ready'=>'info','out_for_delivery'=>'warning','completed'=>'success','cancelled'=>'danger'][$status] ?? 'default'; ?>
          <span class="label label-<?= $badge; ?>"><?= ucwords(str_replace('_',' ',$status)); ?></span>
        </span></div>
        <?php if(!empty($notes)) { ?>
        <div class="mp-info-row"><b>Notes</b><span style="max-width:200px"><?= nl2br($notes); ?></span></div>
        <?php } ?>
      </div>
      <div class="mp-card-body" style="border-top:1px solid var(--mp-border)">
        <a href="<?= base_url('operations/delivery_schedule_form/'.$q_id); ?>" class="mp-btn-primary" style="width:100%;justify-content:center"><i class="fa fa-edit"></i> Edit Route</a>
        <?php if($status != 'completed' && $status != 'cancelled') { ?>
        <div style="margin-top:12px">
          <button type="button" class="mp-status-btn" data-toggle="collapse" data-target="#statusMenu">
            <span class="dot" style="background:var(--mp-warning)"></span> Update Status <i class="fa fa-caret-down" style="margin-left:auto"></i>
          </button>
          <div id="statusMenu" class="collapse">
            <button type="button" class="mp-status-btn" onclick="updateStatus('ready')"><span class="dot" style="background:var(--mp-primary)"></span> Ready</button>
            <button type="button" class="mp-status-btn" onclick="updateStatus('out_for_delivery')"><span class="dot" style="background:var(--mp-warning)"></span> Out for Delivery</button>
            <button type="button" class="mp-status-btn" onclick="updateStatus('completed')"><span class="dot" style="background:var(--mp-success)"></span> Completed</button>
            <button type="button" class="mp-status-btn" onclick="updateStatus('cancelled')"><span class="dot" style="background:var(--mp-danger)"></span> Cancelled</button>
          </div>
        </div>
        <?php } ?>
      </div>
    </div>
  </div>

  <div>
    <div class="mp-card-form" style="margin-bottom:24px">
      <div class="mp-card-head">
        <h3><i class="fa fa-list"></i> Delivery Stops</h3>
        <span class="mp-form-hint"><span class="label label-success"><?= count(array_filter($schedule_items, function($i){return $i->delivery_status=='delivered';})); ?></span> of <?= count($schedule_items); ?> delivered</span>
      </div>
      <div class="mp-card-body" style="padding:0!important">
        <?php if(empty($schedule_items)) { ?>
          <div class="mp-empty-state"><div class="mp-empty-icon"><i class="fa fa-inbox"></i></div><p>No orders assigned to this route.</p></div>
        <?php } else { ?>
        <div class="mp-dt-scroll">
          <table class="mp-static-table">
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
                <td class="text-center"><span class="label label-info"><?= $item->delivery_sequence; ?></span></td>
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
              <tr><td colspan="7" style="background:var(--mp-bg)"><small><b>Note:</b> <?= nl2br($item->delivery_notes); ?></small></td></tr>
              <?php } ?>
              <?php } ?>
            </tbody>
          </table>
        </div>
        <?php } ?>
      </div>
    </div>
  </div>
</div>

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
