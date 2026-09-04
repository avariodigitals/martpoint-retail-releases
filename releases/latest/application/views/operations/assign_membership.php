<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub"><?= $edit_membership ? 'Renew Membership' : 'Assign to Customer'; ?></div>
  </div>
</div>

<div class="mp-card-form">
  <div class="mp-card-head">
    <h3><?= $edit_membership ? 'Renew Membership' : 'Assign New Membership'; ?></h3>
    <a class="mp-card-link" href="<?= base_url('operations/customer_memberships'); ?>"><i class="fa fa-arrow-left"></i> Back</a>
  </div>
  <div class="mp-card-body">
    <form id="assign-form" onsubmit="return false;">
      <input type="hidden" name="membership_id" value="<?= $edit_membership ? $edit_membership->id : ''; ?>">
      <div class="mp-form-grid">
        <div class="mp-form-group">
          <label for="customer_id">Customer <span class="text-danger">*</span></label>
          <select class="form-control select2" id="customer_id" name="customer_id" style="width:100%;" required>
            <option value="">-- Select Customer --</option>
            <?php foreach($customers as $c): ?>
              <option value="<?= $c->id; ?>" <?= ($customer_id == $c->id || ($edit_membership && $edit_membership->customer_id == $c->id)) ? 'selected' : ''; ?>><?= $c->customer_name; ?> (<?= $c->mobile; ?>)</option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mp-form-group">
          <label for="plan_id">Plan <span class="text-danger">*</span></label>
          <select class="form-control select2" id="plan_id" name="plan_id" style="width:100%;" required>
            <option value="">-- Select Plan --</option>
            <?php foreach($plans as $p): ?>
              <option value="<?= $p->id; ?>" <?= ($edit_membership && $edit_membership->plan_id == $p->id) ? 'selected' : ''; ?> data-price="<?= $p->price; ?>" data-cycle="<?= $p->billing_cycle; ?>"><?= $p->plan_name; ?> — <?= $currency . ' ' . store_number_format($p->price); ?> / <?= $p->billing_cycle; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mp-form-group">
          <label for="start_date">Start Date <span class="text-danger">*</span></label>
          <input type="date" class="mp-form-control" id="start_date" name="start_date" value="<?= $edit_membership ? $edit_membership->start_date : date('Y-m-d'); ?>" required>
        </div>
        <div class="mp-form-group">
          <label for="amount_paid">Amount Paid</label>
          <div class="input-group">
            <span class="input-group-addon"><?= $currency; ?></span>
            <input type="number" step="0.01" class="mp-form-control" id="amount_paid" name="amount_paid" value="<?= $edit_membership ? $edit_membership->price : '0.00'; ?>">
          </div>
        </div>
        <div class="mp-form-group">
          <label for="payment_method">Payment Method</label>
          <select class="mp-form-control" id="payment_method" name="payment_method">
            <option value="cash">Cash</option>
            <option value="transfer">Bank Transfer</option>
            <option value="card">Card</option>
            <option value="mobile_money">Mobile Money</option>
          </select>
        </div>
        <div class="mp-form-group">
          <label for="notes">Notes</label>
          <input type="text" class="mp-form-control" id="notes" name="notes" placeholder="Optional notes">
        </div>
        <div class="mp-form-group full">
          <label class="mp-form-control" style="display:flex;align-items:center;gap:10px;padding:10px 14px;min-height:44px;">
            <input type="checkbox" name="auto_renew" id="auto_renew" value="1" <?= ($edit_membership && $edit_membership->auto_renew == 1) ? 'checked' : ''; ?> style="width:18px;height:18px;cursor:pointer;">
            <span>Auto-Renew (charges customer automatically when period ends)</span>
          </label>
        </div>
      </div>
      <div class="alert alert-info" id="plan-preview" style="display:none;margin-top:16px;">
        <h5><i class="fa fa-info-circle"></i> Plan Summary</h5>
        <p id="preview-text"></p>
      </div>
      <div class="mp-form-actions" style="margin-top:24px;">
        <button type="button" class="mp-btn-primary" id="btn-save"><i class="fa fa-save"></i> <?= $edit_membership ? 'Renew Membership' : 'Assign Membership'; ?></button>
        <a class="mp-btn-secondary" href="<?= base_url('operations/customer_memberships'); ?>">Cancel</a>
      </div>
    </form>
  </div>
</div>

<script>
$(function(){
  $('.select2').select2();

  $('#plan_id').on('change', function(){
    var opt = $(this).find('option:selected');
    var price = opt.data('price') || 0;
    var cycle = opt.data('cycle') || 'monthly';
    $('#amount_paid').val(price);
    if(price > 0){
      $('#preview-text').html('<b>Price:</b> <?= $currency; ?> ' + price + ' / ' + cycle);
      $('#plan-preview').show();
    } else {
      $('#plan-preview').hide();
    }
  });

  $('#btn-save').click(function(){
    if(!$('#customer_id').val()){ toastr.error('Select a customer'); return; }
    if(!$('#plan_id').val()){ toastr.error('Select a plan'); return; }
    if(!$('#start_date').val()){ toastr.error('Start date is required'); return; }

    var data = $('#assign-form').serialize();
    data += '&<?= csrf_token(); ?>=<?= csrf_hash(); ?>';

    $.post("<?= base_url('operations/membership_assign_save'); ?>", data, function(res){
      if(res.success){
        toastr.success(res.message);
        setTimeout(function(){ window.location.href = '<?= base_url('operations/customer_memberships'); ?>'; }, 800);
      } else {
        toastr.error(res.message || 'Failed to save');
      }
    }, 'json').fail(function(){ toastr.error('Server error'); });
  });
});
</script>
