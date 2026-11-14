<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.mp-order-row{display:grid!important;grid-template-columns:40px 1fr 1fr 80px 110px 44px!important;gap:10px!important;align-items:center!important;padding:10px 12px!important;background:var(--mp-bg)!important;border:1px solid var(--mp-border)!important;border-radius:10px!important;margin-bottom:8px!important}
.mp-order-row .seq{width:28px;height:28px;border-radius:50%;background:var(--mp-primary);color:#fff;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700}
.mp-order-row .seq-input{width:64px}
.mp-order-row .code{font-weight:700;font-size:13px}
.mp-order-row .muted{font-size:12px;color:var(--mp-muted)}
.mp-order-add{display:flex!important;gap:10px!important;align-items:center!important}
.mp-order-add select{flex:1}
.mp-guide li{margin-bottom:10px!important;font-size:13px!important;color:var(--mp-text)!important;line-height:1.5!important}
.mp-guide li b{color:var(--mp-ink)}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Build a delivery route and sequence the stops</div>
  </div>
  <a href="<?= base_url('operations/delivery_scheduling'); ?>" class="mp-qa-btn blue"><i class="fa fa-arrow-left"></i> Back to Schedules</a>
</div>

<div style="display:grid;grid-template-columns:minmax(0,1fr) 320px;gap:24px;align-items:start;">
  <div>
    <div class="mp-card-form" style="margin-bottom:24px">
      <div class="mp-card-head"><h3>Route Details</h3></div>
      <div class="mp-card-body">
        <form id="schedule-form" method="post">
          <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
          <input type="hidden" name="command" value="<?= isset($q_id) ? 'update' : 'save'; ?>">
          <input type="hidden" name="q_id" value="<?= isset($q_id) ? $q_id : ''; ?>">

          <div class="mp-form-grid">
            <div class="mp-form-group" style="grid-column:span 2">
              <label>Route Name</label>
              <input type="text" class="mp-form-control" name="route_name" value="<?= isset($route_name) ? $route_name : ''; ?>" placeholder="e.g. Lekki Phase 1 Morning Run">
            </div>
            <div class="mp-form-group">
              <label>Schedule Date <span class="text-danger">*</span></label>
              <input type="text" class="mp-form-control datepicker" name="schedule_date" value="<?= isset($schedule_date) ? $schedule_date : date('Y-m-d'); ?>" required>
            </div>
            <div class="mp-form-group">
              <label>Driver</label>
              <select class="mp-form-control" name="driver_id" id="driver_id">
                <option value="">-- Select Driver --</option>
                <?php foreach($drivers as $d) { ?>
                <option value="<?= $d->id; ?>" <?= (isset($driver_id) && $driver_id == $d->id) ? 'selected' : ''; ?>><?= $d->name; ?></option>
                <?php } ?>
              </select>
            </div>
            <div class="mp-form-group">
              <label>Vehicle</label>
              <input type="text" class="mp-form-control" name="vehicle" id="vehicle" value="<?= isset($vehicle) ? $vehicle : ''; ?>" placeholder="Vehicle">
            </div>
            <div class="mp-form-group">
              <label>Status</label>
              <select class="mp-form-control" name="status">
                <option value="planned" <?= (isset($status) && $status=='planned')?'selected':''; ?>>Planned</option>
                <option value="ready" <?= (isset($status) && $status=='ready')?'selected':''; ?>>Ready</option>
                <option value="out_for_delivery" <?= (isset($status) && $status=='out_for_delivery')?'selected':''; ?>>Out for Delivery</option>
                <option value="completed" <?= (isset($status) && $status=='completed')?'selected':''; ?>>Completed</option>
                <option value="cancelled" <?= (isset($status) && $status=='cancelled')?'selected':''; ?>>Cancelled</option>
              </select>
            </div>
            <div class="mp-form-group full">
              <label>Notes</label>
              <textarea class="mp-form-control" name="notes" rows="2" placeholder="Any special instructions for the driver"><?= isset($notes) ? $notes : ''; ?></textarea>
            </div>
          </div>

          <div class="mp-section-divider"><i class="fa fa-shopping-cart"></i> Orders on this Route</div>
          <p class="mp-form-hint" style="margin-bottom:12px">Search and add sales orders to this delivery route.</p>
          <div class="mp-order-add" style="margin-bottom:14px">
            <select class="mp-form-control" id="sale_select">
              <option value="">-- Select an order to add --</option>
              <?php foreach($pending_sales as $s) { ?>
              <option value="<?= $s->id; ?>" data-code="<?= $s->sales_code; ?>" data-customer="<?= $s->customer_name; ?>" data-phone="<?= $s->mobile; ?>" data-total="<?= $s->grand_total; ?>"><?= $s->sales_code; ?> — <?= $s->customer_name; ?> (<?= store_number_format($s->grand_total); ?>)</option>
              <?php } ?>
            </select>
            <button type="button" class="mp-qa-btn green" id="btn_add_sale"><i class="fa fa-plus"></i> Add</button>
          </div>

          <div id="orders_container">
            <?php if(!empty($schedule_items)) { ?>
              <?php foreach($schedule_items as $idx => $item) { ?>
              <div class="mp-order-row order-row">
                <input type="hidden" name="sales_id[]" value="<?= $item->sales_id; ?>">
                <div class="text-center"><span class="seq seq-badge"><?= $idx+1; ?></span></div>
                <div><div class="code"><?= $item->sales_code; ?></div><div class="muted"><?= $item->customer_name; ?></div></div>
                <div class="muted"><?= $item->address ?: 'No address'; ?></div>
                <div><input type="number" name="delivery_sequence[]" class="mp-form-control seq-input" value="<?= $item->delivery_sequence; ?>"></div>
                <div><span class="label label-<?= $item->delivery_status=='delivered'?'success':($item->delivery_status=='pending'?'default':'warning'); ?>"><?= ucfirst($item->delivery_status); ?></span></div>
                <div><div class="mp-actions"><button type="button" class="mp-delete" onclick="removeOrder(this)"><i class="fa fa-trash"></i></button></div></div>
              </div>
              <?php } ?>
            <?php } ?>
          </div>

          <div class="mp-form-actions" style="margin-top:20px">
            <button type="button" id="btn_save" class="mp-btn-primary"><i class="fa fa-save"></i> Save Route</button>
            <a href="<?= base_url('operations/delivery_scheduling'); ?>" class="mp-btn-secondary">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>

  <div class="mp-card-form" style="margin-bottom:0">
    <div class="mp-card-head"><h3><i class="fa fa-info-circle"></i> Quick Guide</h3></div>
    <div class="mp-card-body">
      <ul class="mp-guide" style="padding-left:18px;margin:0">
        <li><b>Route Name:</b> Give this run a name your team will recognise.</li>
        <li><b>Driver:</b> Pick who will handle this run. Add drivers from the main list.</li>
        <li><b>Orders:</b> Only unpaid / pending delivery sales appear in the dropdown.</li>
        <li><b>Sequence:</b> Drag or number the stops in the order the driver should visit.</li>
      </ul>
    </div>
  </div>
</div>

<script>
$(function() {
  $('.datepicker').datepicker({ format: 'yyyy-mm-dd', autoclose: true });

  var orderCount = <?= !empty($schedule_items) ? count($schedule_items) : 0; ?>;

  $('#btn_add_sale').click(function() {
    var opt = $('#sale_select option:selected');
    if (!opt.val()) return;
    orderCount++;
    var html = '<div class="mp-order-row order-row">' +
      '<input type="hidden" name="sales_id[]" value="' + opt.val() + '">' +
      '<div class="text-center"><span class="seq seq-badge">' + orderCount + '</span></div>' +
      '<div><div class="code">' + opt.data('code') + '</div><div class="muted">' + opt.data('customer') + '</div></div>' +
      '<div class="muted">' + (opt.data('phone') || 'No phone') + '</div>' +
      '<div><input type="number" name="delivery_sequence[]" class="mp-form-control seq-input" value="' + orderCount + '"></div>' +
      '<div><span class="label label-default">Pending</span></div>' +
      '<div><div class="mp-actions"><button type="button" class="mp-delete" onclick="removeOrder(this)"><i class="fa fa-trash"></i></button></div></div>' +
      '</div>';
    $('#orders_container').append(html);
    opt.remove();
    renumber();
  });

  window.removeOrder = function(btn) {
    var row = $(btn).closest('.order-row');
    var salesId = row.find('input[name="sales_id[]"]').val();
    var code = row.find('.code').text();
    var customer = row.find('.muted').first().text();
    $('#sale_select').append('<option value="' + salesId + '">' + code + ' — ' + customer + '</option>');
    row.remove();
    renumber();
  };

  function renumber() {
    $('.order-row').each(function(i) {
      $(this).find('.seq-badge').text(i + 1);
      $(this).find('.seq-input').val(i + 1);
    });
    orderCount = $('.order-row').length;
  }

  $('#btn_save').click(function() {
    if ($('.order-row').length === 0) {
      toastr['warning']('Please add at least one order.');
      return;
    }
    $.post("<?= base_url('operations/delivery_schedule_save'); ?>", $('#schedule-form').serialize(), function(res) {
      if (res == 'success') {
        toastr['success']('Route saved successfully.');
        setTimeout(function() { window.location = "<?= base_url('operations/delivery_scheduling'); ?>"; }, 800);
      } else {
        toastr['error'](res);
      }
    });
  });
});
</script>
