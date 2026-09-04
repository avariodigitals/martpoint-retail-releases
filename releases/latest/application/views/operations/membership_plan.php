<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub"><?= $edit_plan ? 'Edit Plan' : 'Create New Plan'; ?></div>
  </div>
  <a href="<?= base_url('operations/memberships'); ?>" class="mp-qa-btn blue"><i class="fa fa-arrow-left"></i> Back to Memberships</a>
</div>

<div class="mp-card-form">
  <div class="mp-card-head">
    <h3><?= $edit_plan ? 'Edit Membership Plan' : 'New Membership Plan'; ?></h3>
  </div>
  <div class="mp-card-body">
    <form id="plan-form" onsubmit="return false;">
      <input type="hidden" name="id" value="<?= $edit_plan ? $edit_plan->id : ''; ?>">
      <div class="mp-form-grid">
        <div class="mp-form-group">
          <label for="plan_name">Plan Name <span class="text-danger">*</span></label>
          <input type="text" class="mp-form-control" id="plan_name" name="plan_name" placeholder="e.g. Gold Gym Membership" value="<?= $edit_plan ? $edit_plan->plan_name : ''; ?>" required>
        </div>
        <div class="mp-form-group">
          <label for="plan_code">Plan Code</label>
          <input type="text" class="mp-form-control" id="plan_code" name="plan_code" placeholder="Auto-generated if empty" value="<?= $edit_plan ? $edit_plan->plan_code : ''; ?>">
        </div>
        <div class="mp-form-group full">
          <label for="description">Description</label>
          <textarea class="mp-form-control" id="description" name="description" rows="2" placeholder="What does this plan include?"><?= $edit_plan ? $edit_plan->description : ''; ?></textarea>
        </div>
        <div class="mp-form-group">
          <label for="price">Price <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-addon"><?= $currency; ?></span>
            <input type="number" step="0.01" class="form-control mp-form-control" id="price" name="price" value="<?= $edit_plan ? $edit_plan->price : '0.00'; ?>" required>
          </div>
        </div>
        <div class="mp-form-group">
          <label for="billing_cycle">Billing Cycle <span class="text-danger">*</span></label>
          <select class="mp-form-control" id="billing_cycle" name="billing_cycle" required>
            <option value="monthly" <?= ($edit_plan && $edit_plan->billing_cycle == 'monthly') ? 'selected' : ''; ?>>Monthly</option>
            <option value="quarterly" <?= ($edit_plan && $edit_plan->billing_cycle == 'quarterly') ? 'selected' : ''; ?>>Quarterly</option>
            <option value="annual" <?= ($edit_plan && $edit_plan->billing_cycle == 'annual') ? 'selected' : ''; ?>>Annual</option>
          </select>
        </div>
        <div class="mp-form-group">
          <label for="discount_percent">Member Discount (%)</label>
          <div class="input-group">
            <input type="number" step="0.01" class="form-control mp-form-control" id="discount_percent" name="discount_percent" value="<?= $edit_plan ? $edit_plan->discount_percent : '0.00'; ?>">
            <span class="input-group-addon">%</span>
          </div>
          <p class="mp-form-hint">Auto-applied at checkout for active members</p>
        </div>
        <div class="mp-form-group">
          <label for="free_services_per_period">Free Services Per Period</label>
          <input type="number" class="mp-form-control" id="free_services_per_period" name="free_services_per_period" value="<?= $edit_plan ? $edit_plan->free_services_per_period : '0'; ?>">
          <p class="mp-form-hint">Number of free service redemptions included</p>
        </div>
        <div class="mp-form-group">
          <label>&nbsp;</label>
          <label class="mp-form-control" style="display:flex;align-items:center;gap:10px;padding:10px 14px;min-height:44px;">
            <input type="checkbox" name="priority_booking" id="priority_booking" value="1" <?= ($edit_plan && $edit_plan->priority_booking == 1) ? 'checked' : ''; ?> style="width:18px;height:18px;cursor:pointer;">
            <span>Priority Booking (shows members first in appointment lists)</span>
          </label>
        </div>
      </div>
      <div class="mp-form-actions">
        <button type="button" class="mp-btn-primary" id="btn-save"><i class="fa fa-save"></i> <?= $edit_plan ? 'Update Plan' : 'Save Plan'; ?></button>
        <a href="<?= base_url('operations/memberships'); ?>" class="mp-btn-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

<script>
$(function(){
  $('#btn-save').click(function(){
    var form = $('#plan-form');
    if(!$('#plan_name').val().trim()){ toastr.error('Plan name is required'); return; }
    if(!$('#price').val() || parseFloat($('#price').val()) < 0){ toastr.error('Valid price is required'); return; }

    var data = form.serialize();
    data += '&<?= csrf_token(); ?>=<?= csrf_hash(); ?>';

    $.post("<?= base_url('operations/membership_plan_save'); ?>", data, function(res){
      if(res.success){
        toastr.success(res.message);
        setTimeout(function(){ window.location.href = '<?= base_url('operations/memberships'); ?>'; }, 800);
      } else {
        toastr.error(res.message || 'Failed to save plan');
      }
    }, 'json').fail(function(){ toastr.error('Server error'); });
  });
});
</script>
