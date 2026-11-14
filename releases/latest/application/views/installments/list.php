<?php $this->load->view('finance/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<?php
$stats = $CI->installments->get_dashboard_stats();
$store_name = $CI->session->userdata('store_name') ?: 'MartPoint';
?>

<div class="mp-page-head">
  <div>
    <h2>Installment Plans</h2>
    <div class="mp-page-sub">Buy Now Pay Later</div>
  </div>
  <button class="mp-qa-btn" onclick="checkOverdue()" title="Mark pending past-due installments as overdue"><i class="fa fa-clock-o"></i> Check Overdue</button>
</div>

<div class="mp-kpi-grid">
  <div class="mp-kpi-card">
    <div class="mp-kpi-label">Total Plans</div>
    <div class="mp-kpi-value"><?= number_format($stats['total_plans']); ?></div>
  </div>
  <div class="mp-kpi-card">
    <div class="mp-kpi-label">Active Plans</div>
    <div class="mp-kpi-value"><?= number_format($stats['active_plans']); ?></div>
  </div>
  <div class="mp-kpi-card">
    <div class="mp-kpi-label">Total Outstanding</div>
    <div class="mp-kpi-value"><?= store_number_format($stats['total_outstanding']); ?></div>
  </div>
  <div class="mp-kpi-card">
    <div class="mp-kpi-label">Overdue Payments</div>
    <div class="mp-kpi-value"><?= number_format($stats['overdue_count']); ?></div>
  </div>
</div>

<div class="mp-card">
  <div class="mp-card-head">
    <h3><i class="fa fa-calendar-check-o"></i> All Plans</h3>
  </div>
  <div class="mp-card-body">
    <div class="mp-dt-scroll">
      <table id="installment_table" class="table mp-dt-table" width="100%">
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
            <th class="text-center">Action</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<script>
$(document).ready(function(){
  $('#installment_table').DataTable({
    processing: true,
    serverSide: false,
    responsive: false,
    order: [],
    ajax: {
      url: "<?= site_url('installments/ajax_list'); ?>",
      type: "POST",
      data: function(d){
        d.<?= $this->security->get_csrf_token_name(); ?> = "<?= $this->security->get_csrf_hash(); ?>";
      }
    },
    columnDefs: [
      { orderable: false, targets: [9] },
      { className: "text-center", targets: [9] }
    ]
  });
});

function checkOverdue(){
  $.post("<?= base_url('installments/check_overdue'); ?>", {
    <?= $this->security->get_csrf_token_name(); ?>: "<?= $this->security->get_csrf_hash(); ?>"
  }, function(res){
    var data = (typeof res === 'object') ? res : JSON.parse(res);
    toastr.info(data.overdue_updated + ' installment(s) marked overdue.', 'Overdue Check');
    $('#installment_table').DataTable().ajax.reload();
  }).fail(function(){
    toastr.error('Failed to run overdue check.', 'Error');
  });
}
</script>
<script>$(".installments-active-li").addClass("active");</script>
