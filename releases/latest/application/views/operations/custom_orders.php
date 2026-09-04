<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Made-to-order workflow — track quotes, deposits, production and delivery</div>
  </div>
  <a href="<?= base_url('operations/custom_order'); ?>" class="mp-qa-btn green"><i class="fa fa-plus"></i> New Order</a>
</div>

<div class="mp-kpi-grid">
  <div class="mp-kpi-card"><div class="mp-kpi-icon" style="background:var(--mp-bg);color:var(--mp-muted)"><i class="fa fa-file-o"></i></div><div class="mp-kpi-label">New</div><div class="mp-kpi-value"><?= $counts['new'] ?? 0; ?></div></div>
  <div class="mp-kpi-card"><div class="mp-kpi-icon" style="background:rgba(0,87,255,.1);color:var(--mp-primary)"><i class="fa fa-calculator"></i></div><div class="mp-kpi-label">Quoted</div><div class="mp-kpi-value"><?= $counts['quoted'] ?? 0; ?></div></div>
  <div class="mp-kpi-card"><div class="mp-kpi-icon" style="background:rgba(245,158,11,.1);color:var(--mp-warning)"><i class="fa fa-money"></i></div><div class="mp-kpi-label">Deposit Paid</div><div class="mp-kpi-value"><?= $counts['deposit_paid'] ?? 0; ?></div></div>
  <div class="mp-kpi-card"><div class="mp-kpi-icon" style="background:rgba(0,87,255,.1);color:var(--mp-primary)"><i class="fa fa-cogs"></i></div><div class="mp-kpi-label">In Production</div><div class="mp-kpi-value"><?= $counts['in_production'] ?? 0; ?></div></div>
  <div class="mp-kpi-card"><div class="mp-kpi-icon" style="background:rgba(5,150,105,.1);color:var(--mp-success)"><i class="fa fa-check-circle"></i></div><div class="mp-kpi-label">Ready</div><div class="mp-kpi-value"><?= $counts['ready'] ?? 0; ?></div></div>
  <div class="mp-kpi-card"><div class="mp-kpi-icon" style="background:rgba(13,148,136,.1);color:#0D9488"><i class="fa fa-truck"></i></div><div class="mp-kpi-label">Delivered</div><div class="mp-kpi-value"><?= $counts['delivered'] ?? 0; ?></div></div>
</div>

<div class="mp-table-wrap">
  <div class="mp-card-head"><h3>Custom Orders</h3></div>
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table id="orders-table" class="table mp-dt-table" width="100%">
        <thead><tr><th>#</th><th>Order #</th><th>Customer</th><th>Item</th><th>Status</th><th>Due Date</th><th>Total</th><th>Action</th></tr></thead>
      </table>
    </div>
  </div>
</div>

<script>
$(function(){
  $('#orders-table').DataTable({
    processing: true, serverSide: true,
    ajax: { url: "<?= base_url('operations/custom_orders_ajax'); ?>", type: "POST" },
    columnDefs: [{ orderable: false, targets: [7] }],
    autoWidth: false
  });
});
function delete_custom_order(id) {
  if(!confirm('Delete this custom order?')) return;
  $.post('<?= base_url('operations/custom_order_delete'); ?>', { id: id }, function(res){
    if(res.success) { toastr.success(res.message); $('#orders-table').DataTable().ajax.reload(); }
    else { toastr.error(res.message || 'Failed'); }
  }, 'json');
}
</script>
