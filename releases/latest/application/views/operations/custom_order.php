<!DOCTYPE html>
<html>
<head><?php $this->load->view('comman/code_css.php'); ?></head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar'); ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1><?= $page_title; ?><small>Made-to-Order</small></h1>
    <ol class="breadcrumb"><li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li><li><a href="<?= base_url('operations/custom_orders'); ?>">Custom Orders</a></li><li class="active"><?= $page_title; ?></li></ol>
  </section>
  <section class="content">
    <div class="row">
      <div class="col-md-8">
        <div class="box box-warning">
          <div class="box-header with-border"><h3 class="box-title"><?= $edit_order ? 'Edit Order' : 'New Custom Order'; ?></h3></div>
          <form id="order-form">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
            <input type="hidden" name="id" value="<?= $edit_order->id ?? ''; ?>">
            <div class="box-body">
              <div class="row">
                <div class="form-group col-md-6">
                  <label>Customer <span class="text-danger">*</span></label>
                  <select class="form-control select2" id="customer_id" name="customer_id" required>
                    <option value="">-- Select Customer --</option>
                    <?php foreach($customers as $c): ?>
                    <option value="<?= $c->id; ?>" <?= ((isset($edit_order) && $edit_order->customer_id==$c->id) || (isset($preselect_customer_id) && $preselect_customer_id==$c->id))?'selected':''; ?>><?= htmlspecialchars($c->customer_name . ' (' . $c->mobile . ')'); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group col-md-6">
                  <label>Product Template <span class="text-danger">*</span></label>
                  <select class="form-control select2" id="item_id" name="item_id" required>
                    <option value="">-- Select Item --</option>
                    <?php foreach($items as $it): ?>
                    <option value="<?= $it->id; ?>" <?= (isset($edit_order) && $edit_order->item_id==$it->id)?'selected':''; ?> data-fields='<?= htmlspecialchars($it->custom_order_fields_json ?? '[]'); ?>' data-quote="<?= $it->requires_quote; ?>" data-deposit="<?= $it->requires_deposit; ?>" data-workflow="<?= $it->workflow_template_key ?? 'standard'; ?>"><?= htmlspecialchars($it->item_name); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
              <div class="row">
                <div class="form-group col-md-4">
                  <label>Order Date <span class="text-danger">*</span></label>
                  <input type="date" class="form-control" name="order_date" value="<?= isset($edit_order) ? $edit_order->order_date : date('Y-m-d'); ?>" required>
                </div>
                <div class="form-group col-md-4">
                  <label>Due Date</label>
                  <input type="date" class="form-control" name="due_date" value="<?= isset($edit_order) ? $edit_order->due_date : ''; ?>">
                </div>
                <div class="form-group col-md-4">
                  <label>Assigned Staff</label>
                  <select class="form-control select2" name="staff_id">
                    <option value="">-- Select --</option>
                    <?php foreach($staff as $s): ?>
                    <option value="<?= $s->id; ?>" <?= (isset($edit_order) && $edit_order->staff_id==$s->id)?'selected':''; ?>><?= htmlspecialchars($s->first_name . ' ' . $s->last_name); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <hr>
              <h4><i class="fa fa-pencil-square-o"></i> Customer Specifications <small class="text-muted">(fill what the customer wants)</small></h4>
              <div id="specs-container" class="row" style="margin-top:10px;">
                <div class="col-md-12 text-muted" id="specs-placeholder">Select a product template to load the required fields.</div>
              </div>

              <hr>
              <h4><i class="fa fa-money"></i> Pricing</h4>
              <div class="row">
                <div class="form-group col-md-3">
                  <label>Quoted Price</label>
                  <input type="text" class="form-control only_currency" id="quoted_price" name="quoted_price" value="<?= isset($edit_order) ? store_number_format($edit_order->quoted_price) : '0.00'; ?>">
                </div>
                <div class="form-group col-md-3">
                  <label>Deposit Required</label>
                  <input type="text" class="form-control only_currency" id="deposit_amount" name="deposit_amount" value="<?= isset($edit_order) ? store_number_format($edit_order->deposit_amount) : '0.00'; ?>">
                </div>
                <div class="form-group col-md-3">
                  <label>Deposit Paid</label>
                  <input type="text" class="form-control only_currency" id="deposit_paid" name="deposit_paid" value="<?= isset($edit_order) ? store_number_format($edit_order->deposit_paid) : '0.00'; ?>">
                </div>
                <div class="form-group col-md-3">
                  <label>Total Amount</label>
                  <input type="text" class="form-control only_currency" id="total_amount" name="total_amount" value="<?= isset($edit_order) ? store_number_format($edit_order->total_amount) : '0.00'; ?>">
                </div>
              </div>

              <hr>
              <div class="row">
                <div class="form-group col-md-4">
                  <label>Status</label>
                  <select class="form-control" name="status" id="status_select">
                    <?php
                    $wf = isset($edit_order) ? $edit_order->workflow_template_key : 'standard';
                    foreach(Custom_orders_model::get_workflow($wf) as $st):
                    ?>
                    <option value="<?= $st; ?>" <?= (isset($edit_order) && $edit_order->status==$st)?'selected':''; ?>><?= Custom_orders_model::status_label($st); ?></option>
                    <?php endforeach; ?>
                  </select>
                </div>
                <div class="form-group col-md-8">
                  <label>Notes</label>
                  <textarea class="form-control" rows="2" name="notes" placeholder="Special instructions, customer requests..."><?= isset($edit_order) ? htmlspecialchars($edit_order->notes) : ''; ?></textarea>
                </div>
              </div>

            </div>
            <div class="box-footer">
              <button type="button" id="btn-save" class="btn btn-success">Save Order</button>
              <a href="<?= base_url('operations/custom_orders'); ?>" class="btn btn-warning">Back to List</a>
            </div>
          </form>
        </div>
      </div>

      <?php if(isset($edit_order) && !empty($history)): ?>
      <div class="col-md-4">
        <div class="box box-default">
          <div class="box-header"><h3 class="box-title">Order History</h3></div>
          <div class="box-body">
            <ul class="timeline">
              <?php foreach($history as $h): ?>
              <li>
                <i class="fa fa-circle bg-gray"></i>
                <div class="timeline-item">
                  <span class="time"><i class="fa fa-clock-o"></i> <?= show_date($h->created_at); ?></span>
                  <h3 class="timeline-header" style="font-size:13px;"><?= Custom_orders_model::status_label($h->new_status); ?></h3>
                  <div class="timeline-body" style="font-size:12px;"><?= htmlspecialchars($h->note); ?><br><small class="text-muted">by <?= htmlspecialchars($h->changed_by_name); ?></small></div>
                </div>
              </li>
              <?php endforeach; ?>
            </ul>
          </div>
        </div>
      </div>
      <?php endif; ?>
    </div>
  </section>
</div>
<?php $this->load->view('footer'); ?>
</div>
<?php $this->load->view('comman/code_js.php'); ?>
<script>
$(function(){
  $('.select2').select2();

  function renderSpecs(fieldsJson, existingValues) {
    var fields = [];
    try { fields = JSON.parse(fieldsJson || '[]'); } catch(e){}
    var $box = $('#specs-container');
    $box.empty();
    if(fields.length === 0){
      $box.html('<div class="col-md-12 text-muted">This product has no custom fields configured.</div>');
      return;
    }
    fields.forEach(function(f, idx){
      var val = existingValues[f.label] || '';
      var req = f.required ? ' required' : '';
      var html = '<div class="form-group col-md-4">';
      html += '<label>' + f.label + (f.required ? ' <span class="text-danger">*</span>' : '') + '</label>';
      if(f.type === 'textarea'){
        html += '<textarea class="form-control" name="spec_value[]" rows="2" placeholder="Enter ' + f.label + '"' + req + '>' + val + '</textarea>';
      } else if(f.type === 'select'){
        html += '<select class="form-control" name="spec_value[]"' + req + '><option value="">-- Select --</option>';
        (f.options || '').split(',').forEach(function(opt){ opt=opt.trim(); html += '<option value="' + opt + '"' + (val==opt?' selected':'') + '>' + opt + '</option>'; });
        html += '</select>';
      } else if(f.type === 'date'){
        html += '<input type="date" class="form-control" name="spec_value[]" value="' + val + '"' + req + '>';
      } else if(f.type === 'color'){
        html += '<input type="color" class="form-control" name="spec_value[]" value="' + (val || '#000000') + '" style="height:34px;">';
      } else {
        html += '<input type="' + f.type + '" class="form-control" name="spec_value[]" value="' + val + '" placeholder="Enter ' + f.label + '"' + req + '>';
      }
      html += '<input type="hidden" name="spec_label[]" value="' + f.label + '">';
      html += '</div>';
      $box.append(html);
    });
  }

  // Load specs on item change
  var existingSpecs = {};
  <?php if(isset($edit_order) && !empty($edit_order->specifications_json)): ?>
  try { existingSpecs = JSON.parse('<?= addslashes($edit_order->specifications_json); ?>'); } catch(e){}
  <?php endif; ?>

  $('#item_id').on('change', function(){
    var opt = $(this).find('option:selected');
    var fields = opt.data('fields');
    renderSpecs(fields, {});
    if(opt.data('quote')) $('#quoted_price').prop('readonly', false); else $('#quoted_price').prop('readonly', true);
    if(opt.data('deposit')) $('#deposit_amount').prop('readonly', false); else $('#deposit_amount').prop('readonly', true);
  });

  // Pre-select on edit
  if($('#item_id').val()){
    var opt = $('#item_id').find('option:selected');
    renderSpecs(opt.data('fields'), existingSpecs);
  }

  $('#btn-save').on('click', function(){
    var $btn = $(this); $btn.prop('disabled', true).text('Saving...');
    $.post('<?= base_url('operations/custom_order_save'); ?>', $('#order-form').serialize(), function(res){
      if(res.success){
        toastr.success(res.message);
        setTimeout(function(){ window.location.href = '<?= base_url('operations/custom_orders'); ?>'; }, 800);
      } else {
        toastr.error(res.message || 'Failed to save');
        $btn.prop('disabled', false).text('Save Order');
      }
    }, 'json').fail(function(){ toastr.error('Server error'); $btn.prop('disabled', false).text('Save Order'); });
  });
});
</script>
</body>
</html>
