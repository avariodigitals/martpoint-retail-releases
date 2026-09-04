<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<?php
  $statusLabels = [
    'pending' => 'label-warning', 'paid' => 'label-success', 'processing' => 'label-info',
    'ready' => 'label-primary', 'completed' => 'label-success', 'cancelled' => 'label-danger'
  ];
  $paymentLabels = [
    'unpaid' => 'label-warning', 'paid' => 'label-success', 'partially_paid' => 'label-info',
    'failed' => 'label-danger', 'refunded' => 'label-default'
  ];
  $statuses = ['all'=>'All','pending'=>'Pending','paid'=>'Paid','processing'=>'Processing','ready'=>'Ready','completed'=>'Completed','cancelled'=>'Cancelled'];
?>
<style>
.os-filter-tabs{display:flex!important;gap:4px!important;background:var(--mp-surface)!important;border:1px solid var(--mp-border)!important;border-radius:10px!important;padding:4px!important;flex-wrap:wrap!important}
.os-filter-tabs a{border:none!important;background:none!important;padding:7px 14px!important;border-radius:7px!important;font-size:13px!important;font-weight:600!important;color:var(--mp-muted)!important;cursor:pointer!important;text-decoration:none!important;transition:all .12s ease!important}
.os-filter-tabs a:hover{color:var(--mp-ink)!important;text-decoration:none!important}
.os-filter-tabs a.active{background:var(--mp-primary)!important;color:#fff!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub"><?= number_format((int)$total); ?> order(s) found</div>
  </div>
  <div class="os-filter-tabs">
    <?php foreach($statuses as $key => $label): ?>
    <a href="<?= base_url('online_store/orders'.($key!='all'?'?status='.$key:'')); ?>" class="<?= $current_status==$key ? 'active' : ''; ?>"><?= $label; ?></a>
    <?php endforeach; ?>
  </div>
</div>

<div class="mp-table-wrap">
  <div class="mp-card-head"><h3>Online Orders</h3></div>
  <div class="box-body">
    <div class="mp-dt-scroll">
      <table id="os-orders-table" class="table mp-dt-table" width="100%">
        <thead>
          <tr>
            <th>Order #</th><th>Date</th><th>Customer</th><th>Phone</th><th>Type</th>
            <th>Total</th><th>Payment</th><th>Status</th><th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if(!empty($orders)): foreach($orders as $o): ?>
          <tr>
            <td><a href="<?= base_url('online_store/order/'.$o->id); ?>"><strong><?= htmlspecialchars($o->order_code); ?></strong></a></td>
            <td><?= show_date($o->created_at); ?> <small class="row-meta"><?= date('H:i', strtotime($o->created_at)); ?></small></td>
            <td><?= htmlspecialchars($o->customer_name); ?></td>
            <td><?= htmlspecialchars($o->customer_phone); ?></td>
            <td><span class="label label-info"><?= ucfirst($o->order_type); ?></span></td>
            <td class="amt"><?= $CI->currency($o->grand_total); ?></td>
            <td><span class="label <?= $paymentLabels[$o->payment_status] ?? 'label-default'; ?>"><?= ucfirst(str_replace('_',' ',$o->payment_status)); ?></span></td>
            <td><span class="label <?= $statusLabels[$o->order_status] ?? 'label-default'; ?>"><?= ucfirst($o->order_status); ?></span></td>
            <td>
              <div class="mp-actions">
                <a href="<?= base_url('online_store/order/'.$o->id); ?>" class="mp-edit" title="View"><i class="fa fa-eye"></i></a>
              </div>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="9" class="mp-empty-state">No orders found.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
$(document).ready(function(){
  $('#os-orders-table').DataTable({
    "aLengthMenu": [[10,25,50,100],[10,25,50,100]],
    dom: '<"row margin-bottom-12"<"col-sm-12"<"pull-left"l><"pull-right"fr>>>t<"row mp-dt-footer"<"col-sm-5"i><"col-sm-7"p>>',
    "order": [[1,"desc"]],
    "responsive": false,
    "columnDefs": [{ "targets": [8], "orderable": false }]
  });
});
</script>
<script>$(".online_store-orders-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
