<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Active Members &amp; Renewals</div>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap;">
    <a class="mp-qa-btn green" href="<?= base_url('operations/assign_membership'); ?>"><i class="fa fa-user-plus"></i> Assign Membership</a>
    <a class="mp-qa-btn blue" href="<?= base_url('operations/memberships'); ?>"><i class="fa fa-cog"></i> Plans</a>
  </div>
</div>

<div class="mp-kpi-grid">
  <div class="mp-kpi-card profit">
    <div class="mp-kpi-icon"><i class="fa fa-users"></i></div>
    <div class="mp-kpi-label">Active Members</div>
    <div class="mp-kpi-value"><?= $active_count; ?></div>
  </div>
  <div class="mp-kpi-card warn">
    <div class="mp-kpi-icon"><i class="fa fa-clock-o"></i></div>
    <div class="mp-kpi-label">Expiring Soon</div>
    <div class="mp-kpi-value"><?= $expiring_count; ?></div>
  </div>
</div>

<div class="mp-table-wrap">
  <div class="mp-card-head">
    <h3>Customer Memberships</h3>
  </div>
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table id="members-table" class="table mp-dt-table" width="100%">
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
</script>
