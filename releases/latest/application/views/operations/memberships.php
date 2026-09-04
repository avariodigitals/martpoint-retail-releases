<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Recurring Plans &amp; Benefits</div>
  </div>
  <div style="display:flex;gap:10px;">
    <a href="<?= base_url('operations/membership_plan'); ?>" class="mp-qa-btn green"><i class="fa fa-plus"></i> New Plan</a>
    <a href="<?= base_url('operations/customer_memberships'); ?>" class="mp-qa-btn green"><i class="fa fa-users"></i> Members</a>
    <a href="<?= base_url('operations/assign_membership'); ?>" class="mp-qa-btn blue"><i class="fa fa-user-plus"></i> Assign</a>
  </div>
</div>

<div class="mp-kpi-grid">
  <div class="mp-kpi-card success">
    <div class="mp-kpi-icon"><i class="fa fa-users"></i></div>
    <div class="mp-kpi-label">Active Members</div>
    <div class="mp-kpi-value"><?= $active_count; ?></div>
    <a href="<?= base_url('operations/customer_memberships'); ?>" class="mp-kpi-link">View Members <i class="fa fa-arrow-circle-right"></i></a>
  </div>
  <div class="mp-kpi-card warn">
    <div class="mp-kpi-icon"><i class="fa fa-clock-o"></i></div>
    <div class="mp-kpi-label">Expiring Soon (7 days)</div>
    <div class="mp-kpi-value"><?= $expiring_count; ?></div>
    <a href="<?= base_url('operations/customer_memberships'); ?>" class="mp-kpi-link">View Members <i class="fa fa-arrow-circle-right"></i></a>
  </div>
  <div class="mp-kpi-card primary">
    <div class="mp-kpi-icon"><i class="fa fa-id-card"></i></div>
    <div class="mp-kpi-label">Plans Available</div>
    <div class="mp-kpi-value"><?= count($plans); ?></div>
    <a href="<?= base_url('operations/membership_plan'); ?>" class="mp-kpi-link">Add Plan <i class="fa fa-arrow-circle-right"></i></a>
  </div>
</div>

<div class="mp-table-wrap">
  <div class="mp-card-head"><h3>Membership Plans</h3></div>
  <div class="mp-dt-scroll">
    <table id="plans-table" class="table mp-dt-table" width="100%">
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
</script>
