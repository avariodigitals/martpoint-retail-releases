<!DOCTYPE html>
<html>
<head><?php $this->load->view('comman/code_css.php'); ?></head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar'); ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1><?= $page_title; ?><small><?= $edit_plan ? 'Edit Plan' : 'Create New Plan'; ?></small></h1>
    <ol class="breadcrumb">
      <li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="<?= base_url('operations/memberships'); ?>">Memberships</a></li>
      <li class="active"><?= $page_title; ?></li>
    </ol>
  </section>
  <section class="content">
    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <div class="box box-info">
          <div class="box-header with-border">
            <h3 class="box-title"><?= $edit_plan ? 'Edit Membership Plan' : 'New Membership Plan'; ?></h3>
            <div class="box-tools pull-right">
              <a class="btn btn-sm btn-default" href="<?= base_url('operations/memberships'); ?>"><i class="fa fa-arrow-left"></i> Back</a>
            </div>
          </div>
          <form id="plan-form" onsubmit="return false;">
            <div class="box-body">
              <input type="hidden" name="id" value="<?= $edit_plan ? $edit_plan->id : ''; ?>">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="plan_name">Plan Name <span class="text-red">*</span></label>
                    <input type="text" class="form-control" id="plan_name" name="plan_name" placeholder="e.g. Gold Gym Membership" value="<?= $edit_plan ? $edit_plan->plan_name : ''; ?>" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="plan_code">Plan Code</label>
                    <input type="text" class="form-control" id="plan_code" name="plan_code" placeholder="Auto-generated if empty" value="<?= $edit_plan ? $edit_plan->plan_code : ''; ?>">
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label for="description">Description</label>
                <textarea class="form-control" id="description" name="description" rows="2" placeholder="What does this plan include?"><?= $edit_plan ? $edit_plan->description : ''; ?></textarea>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="price">Price <span class="text-red">*</span></label>
                    <div class="input-group">
                      <span class="input-group-addon"><?= $currency; ?></span>
                      <input type="number" step="0.01" class="form-control" id="price" name="price" value="<?= $edit_plan ? $edit_plan->price : '0.00'; ?>" required>
                    </div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="billing_cycle">Billing Cycle <span class="text-red">*</span></label>
                    <select class="form-control" id="billing_cycle" name="billing_cycle" required>
                      <option value="monthly" <?= ($edit_plan && $edit_plan->billing_cycle == 'monthly') ? 'selected' : ''; ?>>Monthly</option>
                      <option value="quarterly" <?= ($edit_plan && $edit_plan->billing_cycle == 'quarterly') ? 'selected' : ''; ?>>Quarterly</option>
                      <option value="annual" <?= ($edit_plan && $edit_plan->billing_cycle == 'annual') ? 'selected' : ''; ?>>Annual</option>
                    </select>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label for="discount_percent">Member Discount (%)</label>
                    <div class="input-group">
                      <input type="number" step="0.01" class="form-control" id="discount_percent" name="discount_percent" value="<?= $edit_plan ? $edit_plan->discount_percent : '0.00'; ?>">
                      <span class="input-group-addon">%</span>
                    </div>
                    <small class="text-muted">Auto-applied at checkout for active members</small>
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label for="free_services_per_period">Free Services Per Period</label>
                    <input type="number" class="form-control" id="free_services_per_period" name="free_services_per_period" value="<?= $edit_plan ? $edit_plan->free_services_per_period : '0'; ?>">
                    <small class="text-muted">Number of free service redemptions included</small>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>&nbsp;</label>
                    <div class="checkbox icheck">
                      <label>
                        <input type="checkbox" name="priority_booking" id="priority_booking" value="1" <?= ($edit_plan && $edit_plan->priority_booking == 1) ? 'checked' : ''; ?>>
                        Priority Booking (shows members first in appointment lists)
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="box-footer">
              <button type="button" class="btn btn-primary" id="btn-save"><i class="fa fa-save"></i> <?= $edit_plan ? 'Update Plan' : 'Save Plan'; ?></button>
              <a class="btn btn-default" href="<?= base_url('operations/memberships'); ?>">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>
</div>
</div>
<?php $this->load->view('comman/code_js.php'); ?>
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
$(".memberships-active-li").addClass("active");
</script>
</body>
</html>
