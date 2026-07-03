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
      <div class="col-md-8">
        <div class="box box-primary">
          <div class="box-header with-border"><h3 class="box-title">Route Details</h3></div>
          <form class="form-horizontal" id="schedule-form" method="post">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
            <input type="hidden" name="command" value="<?= isset($q_id) ? 'update' : 'save'; ?>">
            <input type="hidden" name="q_id" value="<?= isset($q_id) ? $q_id : ''; ?>">
            <div class="box-body">
              <div class="form-group">
                <label class="col-sm-3 control-label">Route Name</label>
                <div class="col-sm-9">
                  <input type="text" class="form-control" name="route_name" value="<?= isset($route_name) ? $route_name : ''; ?>" placeholder="e.g. Lekki Phase 1 Morning Run">
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-3 control-label">Schedule Date <span class="text-danger">*</span></label>
                <div class="col-sm-4">
                  <input type="text" class="form-control datepicker" name="schedule_date" value="<?= isset($schedule_date) ? $schedule_date : date('Y-m-d'); ?>" required>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-3 control-label">Driver</label>
                <div class="col-sm-4">
                  <select class="form-control" name="driver_id" id="driver_id">
                    <option value="">-- Select Driver --</option>
                    <?php foreach($drivers as $d) { ?>
                    <option value="<?= $d->id; ?>" <?= (isset($driver_id) && $driver_id == $d->id) ? 'selected' : ''; ?>><?= $d->name; ?></option>
                    <?php } ?>
                  </select>
                </div>
                <div class="col-sm-4">
                  <input type="text" class="form-control" name="vehicle" id="vehicle" value="<?= isset($vehicle) ? $vehicle : ''; ?>" placeholder="Vehicle">
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-3 control-label">Status</label>
                <div class="col-sm-4">
                  <select class="form-control" name="status">
                    <option value="planned" <?= (isset($status) && $status=='planned')?'selected':''; ?>>Planned</option>
                    <option value="ready" <?= (isset($status) && $status=='ready')?'selected':''; ?>>Ready</option>
                    <option value="out_for_delivery" <?= (isset($status) && $status=='out_for_delivery')?'selected':''; ?>>Out for Delivery</option>
                    <option value="completed" <?= (isset($status) && $status=='completed')?'selected':''; ?>>Completed</option>
                    <option value="cancelled" <?= (isset($status) && $status=='cancelled')?'selected':''; ?>>Cancelled</option>
                  </select>
                </div>
              </div>
              <div class="form-group">
                <label class="col-sm-3 control-label">Notes</label>
                <div class="col-sm-9">
                  <textarea class="form-control" name="notes" rows="2" placeholder="Any special instructions for the driver"><?= isset($notes) ? $notes : ''; ?></textarea>
                </div>
              </div>

              <hr>
              <h4><i class="fa fa-shopping-cart"></i> Orders on this Route</h4>
              <p class="text-muted">Search and add sales orders to this delivery route.</p>
              <div class="form-group">
                <div class="col-sm-12">
                  <div class="input-group">
                    <span class="input-group-addon"><i class="fa fa-search"></i></span>
                    <select class="form-control" id="sale_select">
                      <option value="">-- Select an order to add --</option>
                      <?php foreach($pending_sales as $s) { ?>
                      <option value="<?= $s->id; ?>" data-code="<?= $s->sales_code; ?>" data-customer="<?= $s->customer_name; ?>" data-phone="<?= $s->mobile; ?>" data-total="<?= $s->grand_total; ?>"><?= $s->sales_code; ?> — <?= $s->customer_name; ?> (<?= store_number_format($s->grand_total); ?>)</option>
                      <?php } ?>
                    </select>
                    <span class="input-group-btn">
                      <button type="button" class="btn btn-success" id="btn_add_sale"><i class="fa fa-plus"></i> Add</button>
                    </span>
                  </div>
                </div>
              </div>

              <div id="orders_container">
                <?php if(!empty($schedule_items)) { ?>
                  <?php foreach($schedule_items as $idx => $item) { ?>
                  <div class="row order-row" style="margin-bottom:8px; padding:8px; background:#f9f9f9; border-radius:6px;">
                    <input type="hidden" name="sales_id[]" value="<?= $item->sales_id; ?>">
                    <div class="col-sm-1 text-center"><span class="badge seq-badge"><?= $idx+1; ?></span></div>
                    <div class="col-sm-4"><b><?= $item->sales_code; ?></b><br><small class="text-muted"><?= $item->customer_name; ?></small></div>
                    <div class="col-sm-3"><small><?= $item->address ?: 'No address'; ?></small></div>
                    <div class="col-sm-2"><input type="number" name="delivery_sequence[]" class="form-control input-sm seq-input" value="<?= $item->delivery_sequence; ?>"></div>
                    <div class="col-sm-1"><span class="label label-<?= $item->delivery_status=='delivered'?'success':($item->delivery_status=='pending'?'default':'warning'); ?>"><?= ucfirst($item->delivery_status); ?></span></div>
                    <div class="col-sm-1"><button type="button" class="btn btn-xs btn-danger" onclick="removeOrder(this)"><i class="fa fa-trash"></i></button></div>
                  </div>
                  <?php } ?>
                <?php } ?>
              </div>
            </div>
            <div class="box-footer">
              <button type="button" id="btn_save" class="btn btn-primary"><i class="fa fa-save"></i> Save Route</button>
              <a href="<?= base_url('operations/delivery_scheduling'); ?>" class="btn btn-default">Cancel</a>
            </div>
          </form>
        </div>
      </div>
      <div class="col-md-4">
        <div class="box box-info">
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-info-circle"></i> Quick Guide</h3></div>
          <div class="box-body">
            <ul>
              <li><b>Route Name:</b> Give this run a name your team will recognise.</li>
              <li><b>Driver:</b> Pick who will handle this run. Add drivers from the main list.</li>
              <li><b>Orders:</b> Only unpaid / pending delivery sales appear in the dropdown.</li>
              <li><b>Sequence:</b> Drag or number the stops in the order the driver should visit.</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>
</div>
<?php $this->load->view('comman/code_js.php'); ?>
<script src="<?= base_url(); ?>theme/plugins/datepicker/bootstrap-datepicker.js"></script>
<script>
$(function() {
  $('.datepicker').datepicker({ format: 'yyyy-mm-dd', autoclose: true });

  var orderCount = <?= !empty($schedule_items) ? count($schedule_items) : 0; ?>;

  $('#btn_add_sale').click(function() {
    var opt = $('#sale_select option:selected');
    if (!opt.val()) return;
    orderCount++;
    var html = '<div class="row order-row" style="margin-bottom:8px; padding:8px; background:#f9f9f9; border-radius:6px;">' +
      '<input type="hidden" name="sales_id[]" value="' + opt.val() + '">' +
      '<div class="col-sm-1 text-center"><span class="badge seq-badge">' + orderCount + '</span></div>' +
      '<div class="col-sm-4"><b>' + opt.data('code') + '</b><br><small class="text-muted">' + opt.data('customer') + '</small></div>' +
      '<div class="col-sm-3"><small>' + (opt.data('phone') || 'No phone') + '</small></div>' +
      '<div class="col-sm-2"><input type="number" name="delivery_sequence[]" class="form-control input-sm seq-input" value="' + orderCount + '"></div>' +
      '<div class="col-sm-1"><span class="label label-default">Pending</span></div>' +
      '<div class="col-sm-1"><button type="button" class="btn btn-xs btn-danger" onclick="removeOrder(this)"><i class="fa fa-trash"></i></button></div>' +
      '</div>';
    $('#orders_container').append(html);
    opt.remove();
    renumber();
  });

  window.removeOrder = function(btn) {
    var row = $(btn).closest('.order-row');
    var salesId = row.find('input[name="sales_id[]"]').val();
    var code = row.find('b').text();
    var customer = row.find('.text-muted').text();
    var total = '';
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
</body>
</html>
