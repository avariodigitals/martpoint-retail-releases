<!DOCTYPE html>
<html>
   <head>
      <?php $this->load->view('comman/code_css.php');?>
   </head>
   <body class="hold-transition skin-blue sidebar-mini">
      <div class="wrapper">
         <?php $this->load->view('sidebar');?>

<div class="content-wrapper">
  <section class="content-header">
    <h1><?= $plan ? 'Edit Plan' : 'Create Plan'; ?></h1>
    <ol class="breadcrumb">
      <li><a href="<?=base_url('dashboard');?>"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="<?=base_url('subscription_plans');?>">Plans</a></li>
      <li class="active"><?= $plan ? 'Edit' : 'Create'; ?></li>
    </ol>
  </section>

  <section class="content">
    <div class="row">
      <div class="col-md-8 col-md-offset-2">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-sliders"></i> Plan Details</h3>
          </div>
          <form id="plan-form">
            <input type="hidden" name="id" value="<?= $plan ? $plan->id : ''; ?>">
            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>">
            <div class="box-body">
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Plan Name <span class="text-danger">*</span></label>
                    <input type="text" name="plan_name" class="form-control" value="<?= $plan ? htmlspecialchars($plan->plan_name) : ''; ?>" required>
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Plan Code <span class="text-danger">*</span></label>
                    <input type="text" name="plan_code" class="form-control" value="<?= $plan ? htmlspecialchars($plan->plan_code) : ''; ?>" required placeholder="e.g. basic, standard">
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label>Description</label>
                <textarea name="description" class="form-control" rows="2"><?= $plan ? htmlspecialchars($plan->description) : ''; ?></textarea>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Branch Limit</label>
                    <input type="number" name="branch_limit" class="form-control" min="1" value="<?= $plan ? (int)$plan->branch_limit : 1; ?>">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>User Limit</label>
                    <input type="number" name="user_limit" class="form-control" min="1" value="<?= $plan ? (int)$plan->user_limit : 3; ?>">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Product Limit</label>
                    <input type="number" name="product_limit" class="form-control" min="1" value="<?= $plan ? (int)$plan->product_limit : 500; ?>">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Service Limit</label>
                    <input type="number" name="service_limit" class="form-control" min="1" value="<?= $plan ? (int)$plan->service_limit : 100; ?>">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Media Storage (MB)</label>
                    <input type="number" name="media_storage_limit_mb" class="form-control" min="1" value="<?= $plan ? (int)$plan->media_storage_limit_mb : 2048; ?>">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Storefront Limit</label>
                    <input type="number" name="storefront_limit" class="form-control" min="1" value="<?= $plan ? (int)$plan->storefront_limit : 1; ?>">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Custom Domain Limit</label>
                    <input type="number" name="custom_domain_limit" class="form-control" min="1" value="<?= $plan ? (int)$plan->custom_domain_limit : 1; ?>">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Display Order</label>
                    <input type="number" name="display_order" class="form-control" min="0" value="<?= $plan ? (int)$plan->display_order : 0; ?>">
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="form-group">
                    <label>Status</label>
                    <select name="is_active" class="form-control">
                      <option value="1" <?= ($plan && $plan->is_active) ? 'selected' : ''; ?>>Active</option>
                      <option value="0" <?= ($plan && !$plan->is_active) ? 'selected' : ''; ?>>Inactive</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
            <div class="box-footer">
              <button type="submit" class="btn btn-primary"><i class="fa fa-check"></i> Save Plan</button>
              <a href="<?=base_url('subscription_plans');?>" class="btn btn-default">Cancel</a>
            </div>
          </form>
        </div>
      </div>
    </div>
  </section>
</div>

      </div>
      <?php $this->load->view('footer.php');?>
      <div class="control-sidebar-bg"></div>
      </div>
      <?php $this->load->view('comman/code_js_sound.php');?>
      <?php $this->load->view('comman/code_js.php');?>
<script>
var csrfName = '<?php echo $this->security->get_csrf_token_name(); ?>';
var csrfHash = '<?php echo $this->security->get_csrf_hash(); ?>';
function withCsrf(data){
  data = data || {};
  data[csrfName] = csrfHash;
  return data;
}
$('#plan-form').on('submit', function(e){
  e.preventDefault();
  var btn = $(this).find('button[type=submit]');
  btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
  $.post('<?=base_url("subscription_plans/save");?>', $(this).serialize(), function(res){
    try {
      var r = JSON.parse(res);
      if(r.status === 'success'){
        toastr.success(r.message);
        window.location.href = '<?=base_url("subscription_plans");?>';
      } else {
        toastr.error(r.message);
        btn.prop('disabled', false).html('<i class="fa fa-check"></i> Save Plan');
      }
    } catch(e) {
      toastr.error('Unexpected response');
      btn.prop('disabled', false).html('<i class="fa fa-check"></i> Save Plan');
    }
  }).fail(function(){
    toastr.error('Request failed');
    btn.prop('disabled', false).html('<i class="fa fa-check"></i> Save Plan');
  });
});
</script>
      <script>$(".subscription-plans-active-li").addClass("active");</script>
   </body>
</html>
