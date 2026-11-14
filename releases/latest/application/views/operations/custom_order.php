<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.mp-specs-grid{display:grid!important;grid-template-columns:repeat(auto-fill,minmax(220px,1fr))!important;gap:16px!important;margin-top:12px!important}
.mp-specs-grid .mp-form-group{margin:0!important}
.mp-section-divider{font-size:14px!important;font-weight:700!important;color:var(--mp-ink)!important;margin:24px 0 12px!important;padding-bottom:8px!important;border-bottom:1px solid var(--mp-border)!important;display:flex!important;align-items:center!important;gap:8px!important}
.mp-section-divider i{color:var(--mp-primary)!important}
.mp-timeline{list-style:none!important;padding:0!important;margin:0!important}
.mp-timeline li{position:relative!important;padding:0 0 20px 24px!important;border-left:2px solid var(--mp-border)!important}
.mp-timeline li:last-child{border-left-color:transparent!important;padding-bottom:0!important}
.mp-timeline li::before{content:''!important;position:absolute!important;left:-7px!important;top:2px!important;width:12px!important;height:12px!important;border-radius:50%!important;background:var(--mp-muted)!important;border:2px solid var(--mp-surface)!important}
.mp-timeline .tl-time{font-size:11px!important;color:var(--mp-muted)!important;font-weight:600!important}
.mp-timeline .tl-header{font-size:13px!important;font-weight:700!important;color:var(--mp-ink)!important;margin:2px 0 4px!important}
.mp-timeline .tl-body{font-size:12px!important;color:var(--mp-text)!important;line-height:1.5!important}
.mp-timeline .tl-body small{color:var(--mp-muted)!important}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Made-to-Order</div>
  </div>
  <a href="<?= base_url('operations/custom_orders'); ?>" class="mp-qa-btn blue"><i class="fa fa-arrow-left"></i> Back to List</a>
</div>

<div style="display:grid;grid-template-columns:minmax(0,1fr);gap:24px;">
  <div class="mp-card-form">
    <div class="mp-card-head"><h3><?= $edit_order ? 'Edit Order' : 'New Custom Order'; ?></h3></div>
    <div class="mp-card-body">
      <form id="order-form">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" name="id" value="<?= $edit_order->id ?? ''; ?>">

        <div class="mp-form-grid">
          <div class="mp-form-group">
            <label>Customer <span class="text-danger">*</span></label>
            <select class="mp-form-control select2" id="customer_id" name="customer_id" required>
              <option value="">-- Select Customer --</option>
              <?php foreach($customers as $c): ?>
              <option value="<?= $c->id; ?>" <?= ((isset($edit_order) && $edit_order->customer_id==$c->id) || (isset($preselect_customer_id) && $preselect_customer_id==$c->id))?'selected':''; ?>><?= htmlspecialchars($c->customer_name . ' (' . $c->mobile . ')'); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mp-form-group">
            <label>Product Template <span class="text-danger">*</span></label>
            <select class="mp-form-control select2" id="item_id" name="item_id" required>
              <option value="">-- Select Item --</option>
              <?php foreach($items as $it): ?>
              <option value="<?= $it->id; ?>" <?= (isset($edit_order) && $edit_order->item_id==$it->id)?'selected':''; ?> data-fields='<?= htmlspecialchars($it->custom_order_fields_json ?? '[]'); ?>' data-quote="<?= $it->requires_quote; ?>" data-deposit="<?= $it->requires_deposit; ?>" data-workflow="<?= $it->workflow_template_key ?? 'standard'; ?>"><?= htmlspecialchars($it->item_name); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="mp-form-grid" style="margin-top:20px;">
          <div class="mp-form-group">
            <label>Order Date <span class="text-danger">*</span></label>
            <input type="date" class="mp-form-control" name="order_date" value="<?= isset($edit_order) ? $edit_order->order_date : date('Y-m-d'); ?>" required>
          </div>
          <div class="mp-form-group">
            <label>Due Date</label>
            <input type="date" class="mp-form-control" name="due_date" value="<?= isset($edit_order) ? $edit_order->due_date : ''; ?>">
          </div>
          <div class="mp-form-group full">
            <label>Assigned Staff</label>
            <select class="mp-form-control select2" name="staff_id">
              <option value="">-- Select --</option>
              <?php foreach($staff as $s): ?>
              <option value="<?= $s->id; ?>" <?= (isset($edit_order) && $edit_order->staff_id==$s->id)?'selected':''; ?>><?= htmlspecialchars($s->first_name . ' ' . $s->last_name); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="mp-section-divider"><i class="fa fa-pencil-square-o"></i> Customer Specifications</div>
        <div id="specs-container" class="mp-specs-grid">
          <div class="mp-form-group" style="color:var(--mp-muted);font-size:13px" id="specs-placeholder">Select a product template to load the required fields.</div>
        </div>

        <div class="mp-section-divider"><i class="fa fa-money"></i> Pricing</div>
        <div class="mp-form-grid">
          <div class="mp-form-group"><label>Quoted Price</label><input type="text" class="mp-form-control only_currency" id="quoted_price" name="quoted_price" value="<?= isset($edit_order) ? store_number_format($edit_order->quoted_price) : '0.00'; ?>"></div>
          <div class="mp-form-group"><label>Deposit Required</label><input type="text" class="mp-form-control only_currency" id="deposit_amount" name="deposit_amount" value="<?= isset($edit_order) ? store_number_format($edit_order->deposit_amount) : '0.00'; ?>"></div>
          <div class="mp-form-group"><label>Deposit Paid</label><input type="text" class="mp-form-control only_currency" id="deposit_paid" name="deposit_paid" value="<?= isset($edit_order) ? store_number_format($edit_order->deposit_paid) : '0.00'; ?>"></div>
          <div class="mp-form-group"><label>Total Amount</label><input type="text" class="mp-form-control only_currency" id="total_amount" name="total_amount" value="<?= isset($edit_order) ? store_number_format($edit_order->total_amount) : '0.00'; ?>"></div>
        </div>

        <div class="mp-section-divider"><i class="fa fa-info-circle"></i> Status & Notes</div>
        <div class="mp-form-grid">
          <div class="mp-form-group">
            <label>Status</label>
            <select class="mp-form-control" name="status" id="status_select">
              <?php
              $wf = isset($edit_order) ? $edit_order->workflow_template_key : 'standard';
              foreach(Custom_orders_model::get_workflow($wf) as $st):
              ?>
              <option value="<?= $st; ?>" <?= (isset($edit_order) && $edit_order->status==$st)?'selected':''; ?>><?= Custom_orders_model::status_label($st); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mp-form-group full">
            <label>Notes</label>
            <textarea class="mp-form-control" rows="2" name="notes" placeholder="Special instructions, customer requests..."><?= isset($edit_order) ? htmlspecialchars($edit_order->notes) : ''; ?></textarea>
          </div>
        </div>

        <div class="mp-form-actions" style="margin-top:24px;">
          <button type="button" id="btn-save" class="mp-btn-primary"><i class="fa fa-check"></i> Save Order</button>
          <a href="<?= base_url('operations/custom_orders'); ?>" class="mp-btn-secondary">Back to List</a>
        </div>
      </form>
    </div>
  </div>

  <?php if(isset($edit_order) && !empty($history)): ?>
  <div class="mp-card-form">
    <div class="mp-card-head"><h3>Order History</h3></div>
    <div class="mp-card-body">
      <ul class="mp-timeline">
        <?php foreach($history as $h): ?>
        <li>
          <div class="tl-time"><i class="fa fa-clock-o"></i> <?= show_date($h->created_at); ?></div>
          <div class="tl-header"><?= Custom_orders_model::status_label($h->new_status); ?></div>
          <div class="tl-body"><?= htmlspecialchars($h->note); ?><br><small>by <?= htmlspecialchars($h->changed_by_name); ?></small></div>
        </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
  <?php endif; ?>
</div>

<script>
$(function(){
  $('.select2').select2();

  function renderSpecs(fieldsJson, existingValues) {
    var fields = [];
    try { fields = JSON.parse(fieldsJson || '[]'); } catch(e){}
    var $box = $('#specs-container');
    $box.empty();
    if(fields.length === 0){
      $box.html('<div class="mp-form-group" style="color:var(--mp-muted);font-size:13px">This product has no custom fields configured.</div>');
      return;
    }
    fields.forEach(function(f, idx){
      var val = existingValues[f.label] || '';
      var req = f.required ? ' required' : '';
      var html = '<div class="mp-form-group">';
      html += '<label>' + f.label + (f.required ? ' <span class="text-danger">*</span>' : '') + '</label>';
      if(f.type === 'textarea'){
        html += '<textarea class="mp-form-control" name="spec_value[]" rows="2" placeholder="Enter ' + f.label + '"' + req + '>' + val + '</textarea>';
      } else if(f.type === 'select'){
        html += '<select class="mp-form-control" name="spec_value[]"' + req + '><option value="">-- Select --</option>';
        (f.options || '').split(',').forEach(function(opt){ opt=opt.trim(); html += '<option value="' + opt + '"' + (val==opt?' selected':'') + '>' + opt + '</option>'; });
        html += '</select>';
      } else if(f.type === 'date'){
        html += '<input type="date" class="mp-form-control" name="spec_value[]" value="' + val + '"' + req + '>';
      } else if(f.type === 'color'){
        html += '<input type="color" class="mp-form-control" name="spec_value[]" value="' + (val || '#000000') + '" style="height:42px;padding:4px">';
      } else {
        html += '<input type="' + f.type + '" class="mp-form-control" name="spec_value[]" value="' + val + '" placeholder="Enter ' + f.label + '"' + req + '>';
      }
      html += '<input type="hidden" name="spec_label[]" value="' + f.label + '">';
      html += '</div>';
      $box.append(html);
    });
  }

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

  if($('#item_id').val()){
    var opt = $('#item_id').find('option:selected');
    renderSpecs(opt.data('fields'), existingSpecs);
  }

  $('#btn-save').on('click', function(){
    var $btn = $(this); $btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
    $.post('<?= base_url('operations/custom_order_save'); ?>', $('#order-form').serialize(), function(res){
      if(res.success){
        toastr.success(res.message);
        setTimeout(function(){ window.location.href = '<?= base_url('operations/custom_orders'); ?>'; }, 800);
      } else {
        toastr.error(res.message || 'Failed to save');
        $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Save Order');
      }
    }, 'json').fail(function(){ toastr.error('Server error'); $btn.prop('disabled', false).html('<i class="fa fa-check"></i> Save Order'); });
  });
});
</script>
