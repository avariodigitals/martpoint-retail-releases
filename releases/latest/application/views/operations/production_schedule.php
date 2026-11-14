<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.mp-pending-list{max-height:420px;overflow-y:auto}
.mp-pending-list table{font-size:12px}
.mp-pending-list .mp-actions a{width:28px;height:28px;font-size:11px}
.mp-filter-bar{display:flex;gap:10px;align-items:center;flex-wrap:wrap}
.mp-filter-bar .mp-form-control{width:auto;min-width:130px}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Bakery batch scheduling — plan, track and complete production runs</div>
  </div>
  <a href="<?= base_url('operations/production_batch'); ?>" class="mp-qa-btn green"><i class="fa fa-plus"></i> New Batch</a>
</div>

<div class="mp-kpi-grid">
  <div class="mp-kpi-card"><div class="mp-kpi-icon" style="background:var(--mp-bg);color:var(--mp-muted)"><i class="fa fa-calendar-o"></i></div><div class="mp-kpi-label">Planned</div><div class="mp-kpi-value"><?= $counts['planned'] ?? 0; ?></div></div>
  <div class="mp-kpi-card"><div class="mp-kpi-icon" style="background:rgba(0,87,255,.1);color:var(--mp-primary)"><i class="fa fa-cut"></i></div><div class="mp-kpi-label">Prepping</div><div class="mp-kpi-value"><?= $counts['prepping'] ?? 0; ?></div></div>
  <div class="mp-kpi-card"><div class="mp-kpi-icon" style="background:rgba(0,87,255,.1);color:var(--mp-primary)"><i class="fa fa-fire"></i></div><div class="mp-kpi-label">In Production</div><div class="mp-kpi-value"><?= $counts['in_production'] ?? 0; ?></div></div>
  <div class="mp-kpi-card"><div class="mp-kpi-icon" style="background:rgba(245,158,11,.1);color:var(--mp-warning)"><i class="fa fa-check-circle"></i></div><div class="mp-kpi-label">Ready</div><div class="mp-kpi-value"><?= $counts['ready'] ?? 0; ?></div></div>
  <div class="mp-kpi-card"><div class="mp-kpi-icon" style="background:rgba(5,150,105,.1);color:var(--mp-success)"><i class="fa fa-flag-checkered"></i></div><div class="mp-kpi-label">Completed</div><div class="mp-kpi-value"><?= $counts['completed'] ?? 0; ?></div></div>
</div>

<div style="display:grid;grid-template-columns:340px minmax(0,1fr);gap:24px;align-items:start;">
  <div class="mp-card-form" style="margin-bottom:0">
    <div class="mp-card-head"><h3>Pending Orders</h3></div>
    <div class="mp-card-body mp-pending-list" style="padding:0!important">
      <?php if(!empty($pending_items)){ ?>
      <table class="mp-static-table">
        <thead><tr><th>Order #</th><th>Item</th><th>Due</th><th></th></tr></thead>
        <tbody>
        <?php foreach($pending_items as $po){ ?>
          <tr>
            <td><span class="label label-default"><?= htmlspecialchars($po->order_code); ?></span></td>
            <td><?= htmlspecialchars($po->item_name ?: '-'); ?></td>
            <td><?= show_date($po->due_date); ?></td>
            <td><div class="mp-actions"><a class="mp-edit" href="<?= base_url('operations/production_batch?order_id='.$po->id); ?>" title="Batch"><i class="fa fa-plus"></i></a></div></td>
          </tr>
        <?php } ?>
        </tbody>
      </table>
      <?php } else { ?>
        <div class="mp-empty-state"><div class="mp-empty-icon"><i class="fa fa-inbox"></i></div><p>No pending orders to schedule.</p></div>
      <?php } ?>
    </div>
  </div>

  <div class="mp-table-wrap">
    <div class="mp-card-head" style="flex-wrap:wrap;gap:12px">
      <h3>Production Schedule</h3>
      <form method="get" class="mp-filter-bar">
        <input type="date" name="from" class="mp-form-control" value="<?= $date_from; ?>">
        <input type="date" name="to" class="mp-form-control" value="<?= $date_to; ?>">
        <select name="status" class="mp-form-control">
          <option value="">All Status</option>
          <?php foreach(Production_batches_model::get_statuses() as $st): ?>
          <option value="<?= $st; ?>" <?= ($status_filter==$st)?'selected':''; ?>><?= Production_batches_model::status_label($st); ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit" class="mp-btn-primary" style="padding:10px 16px"><i class="fa fa-filter"></i></button>
      </form>
    </div>
    <div class="box-body">
      <div class="mp-dt-scroll">
        <table id="batches-table" class="table mp-dt-table" width="100%">
          <thead><tr><th>#</th><th>Batch #</th><th>Name</th><th>Date</th><th>Status</th><th>Stock Check</th><th>Equipment</th><th>Staff</th><th>Action</th></tr></thead>
        </table>
      </div>
    </div>
  </div>
</div>

<script>
var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
var csrfHash = '<?= $this->security->get_csrf_hash(); ?>';
$(function(){
  var table = $('#batches-table').DataTable({
    processing: true, serverSide: true,
    ajax: { url: "<?= base_url('operations/production_batches_ajax'); ?>", type: "POST",
      data: function(d) { d[csrfName] = csrfHash; }
    },
    columnDefs: [{ orderable: false, targets: [4,5,8] }],
    autoWidth: false
  });

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
      title: confirmTitle, text: confirmText, icon: isBackward ? 'warning' : 'info',
      buttons: ['No, Cancel', confirmBtn], dangerMode: isBackward
    }).then(function(isConfirm){
      if(!isConfirm){ $sel.val(currentStatus); return; }
      $.post('<?= base_url('operations/production_batch_quick_status'); ?>', {
        id: batchId, status: newStatus, current_status: currentStatus
      }, function(res){
        if(res.success){
          toastr.success(res.message);
          $sel.data('current-status', newStatus);
          if(res.csrf_hash) csrfHash = res.csrf_hash;
          table.ajax.reload(null, false);
        } else {
          toastr.error(res.message || 'Failed to update status');
          $sel.val(currentStatus);
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
    title: 'Delete Production Batch?', text: 'This action cannot be undone.',
    icon: 'warning', buttons: ['No, Cancel', 'Yes, Delete'], dangerMode: true
  }).then(function(isConfirm){
    if(!isConfirm) return;
    $.post('<?= base_url('operations/production_batch_delete'); ?>', { id: id }, function(res){
      if(res.success) { toastr.success(res.message); $('#batches-table').DataTable().ajax.reload(); }
      else { toastr.error(res.message || 'Failed'); }
    }, 'json');
  });
}
</script>
