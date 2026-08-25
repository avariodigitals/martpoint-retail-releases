<!DOCTYPE html>
<html>
   <head>
      <?php $this->load->view('comman/code_css.php');?>
      <style>
        .box-body .form-control { min-height: 38px; padding: 8px 12px; }
        .box-body select.form-control { height: 38px; line-height: 1.4; }
        .box-body input[type="date"].form-control { height: 38px; }
      </style>
   </head>
   <body class="hold-transition skin-blue sidebar-mini">
      <div class="wrapper">
         <?php $this->load->view('sidebar');?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Subscription Control</h1>
    <ol class="breadcrumb">
      <li><a href="<?=base_url('dashboard');?>"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Subscription Control</li>
    </ol>
  </section>

  <section class="content">
    <div class="row">

      <!-- LEFT: Generate License -->
      <div class="col-md-6">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-cogs"></i> Generate License Key</h3>
          </div>
          <form id="generate-form">
            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>">
            <div class="box-body">
              <div class="form-group">
                <label>Plan Name <span class="text-danger">*</span></label>
                <select name="plan_name" class="form-control">
                  <option value="Basic">Basic</option>
                  <option value="Standard">Standard</option>
                  <option value="Premium">Premium</option>
                  <option value="Enterprise">Enterprise</option>
                </select>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Start Date <span class="text-danger">*</span></label>
                    <input type="date" name="subscription_start_date" class="form-control" value="<?=date('Y-m-d');?>">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>End Date <span class="text-danger">*</span></label>
                    <input type="date" name="subscription_end_date" class="form-control">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Branch Limit</label>
                    <input type="number" name="branch_limit" class="form-control" min="1" value="1">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>User Limit</label>
                    <input type="number" name="user_limit" class="form-control" min="1" value="5">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Product Limit</label>
                    <input type="number" name="product_limit" class="form-control" min="1" value="500">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Service Limit</label>
                    <input type="number" name="service_limit" class="form-control" min="1" value="100">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Media Storage (MB)</label>
                    <input type="number" name="media_storage_limit_mb" class="form-control" min="1" value="2048">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Storefront Limit</label>
                    <input type="number" name="storefront_limit" class="form-control" min="1" value="1">
                  </div>
                </div>
              </div>
              <div class="row">
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Custom Domain Limit</label>
                    <input type="number" name="custom_domain_limit" class="form-control" min="1" value="1">
                  </div>
                </div>
                <div class="col-md-6">
                  <div class="form-group">
                    <label>Support WhatsApp Number</label>
                    <input type="text" name="whatsapp_number" class="form-control" placeholder="e.g. 254712345678">
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label>Renewal Amount</label>
                <input type="text" name="renewal_amount" class="form-control" placeholder="e.g. 150.00">
              </div>
              <div class="form-group">
                <label>Client / Store Name</label>
                <input type="text" name="client_name" class="form-control" placeholder="Client or store name">
              </div>
              <div class="form-group" id="otp-group" style="display:none;">
                <label>OTP Code <span class="text-danger">*</span></label>
                <div class="input-group">
                  <input type="text" name="otp_code" id="gen_otp_code" class="form-control" placeholder="Enter 6-character OTP" maxlength="6" style="text-transform: uppercase;">
                  <span class="input-group-btn">
                    <button type="button" class="btn btn-default" onclick="requestOTP('generate')">Request OTP</button>
                  </span>
                </div>
                <p class="help-block text-muted">OTP sent to authorized email. Expires in 10 minutes.</p>
              </div>
            </div>
            <div class="box-footer">
              <button type="button" class="btn btn-warning btn-block" onclick="showGenerateOTP()">
                <i class="fa fa-key"></i> Generate License Key
              </button>
              <button type="submit" class="btn btn-primary btn-block" id="gen-submit-btn" style="display:none;">
                <i class="fa fa-check"></i> Confirm & Generate
              </button>
            </div>
          </form>
        </div>

        <!-- Generated Key Display -->
        <div class="box box-success" id="generated-key-box" style="display:none;">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-check-circle"></i> License Key Generated</h3>
          </div>
          <div class="box-body">
            <div class="form-group">
              <label>Copy this license key and use it to activate:</label>
              <div class="input-group">
                <input type="text" id="generated-key" class="form-control" readonly>
                <span class="input-group-btn">
                  <button type="button" class="btn btn-default" onclick="copyKey()"><i class="fa fa-copy"></i></button>
                </span>
              </div>
            </div>
            <table class="table table-bordered table-condensed">
              <tr><td style="width:35%"><strong>Plan</strong></td><td id="gen-plan"></td></tr>
              <tr><td><strong>Start</strong></td><td id="gen-start"></td></tr>
              <tr><td><strong>End</strong></td><td id="gen-end"></td></tr>
              <tr><td><strong>Branches</strong></td><td id="gen-branch"></td></tr>
              <tr><td><strong>Users</strong></td><td id="gen-user"></td></tr>
              <tr><td><strong>Products</strong></td><td id="gen-product"></td></tr>
              <tr><td><strong>Services</strong></td><td id="gen-service"></td></tr>
              <tr><td><strong>Media Storage (MB)</strong></td><td id="gen-media"></td></tr>
              <tr><td><strong>Storefronts</strong></td><td id="gen-storefront"></td></tr>
              <tr><td><strong>Custom Domains</strong></td><td id="gen-domain"></td></tr>
              <tr><td><strong>WhatsApp</strong></td><td id="gen-whatsapp"></td></tr>
              <tr><td><strong>Amount</strong></td><td id="gen-amount"></td></tr>
              <tr><td><strong>Client</strong></td><td id="gen-client"></td></tr>
            </table>
          </div>
        </div>
      </div>

      <!-- RIGHT: Current Subscription + Usage + Actions -->
      <div class="col-md-6">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-info-circle"></i> Current Subscription</h3>
          </div>
          <div class="box-body">
            <?php if($license && !empty($license->license_code)):
              $days = $this->license->days_left($license->subscription_end_date);
            ?>
            <table class="table table-bordered">
              <?php
                $lc = $license->license_code;
                $masked = strlen($lc) > 12 ? substr($lc, 0, 6) . '***' . substr($lc, -6) : $lc;
              ?>
              <tr><td style="width:35%"><strong>License Key</strong></td><td><code style="word-break:break-all;"><?= htmlspecialchars($masked); ?></code></td></tr>
              <tr><td><strong>Plan</strong></td><td><?=$license->plan_name;?></td></tr>
              <tr><td><strong>Start Date</strong></td><td><?=show_date($license->subscription_start_date);?></td></tr>
              <tr><td><strong>End Date</strong></td><td><?=show_date($license->subscription_end_date);?></td></tr>
              <tr><td><strong>Days Left</strong></td><td>
                <?php
                  if($days > 30) echo '<span class="label label-success">'.$days.' Days</span>';
                  elseif($days > 0) echo '<span class="label label-warning">'.$days.' Days</span>';
                  else echo '<span class="label label-danger">Expired</span>';
                ?>
              </td></tr>
              <tr><td><strong>Status</strong></td><td>
                <?php if($license->subscription_status === 'SUSPENDED'): ?>
                  <span class="label label-default">SUSPENDED</span> <small class="text-muted"><?=$license->suspension_reason;?></small>
                <?php elseif($days <= 0): ?>
                  <span class="label label-danger">EXPIRED</span>
                <?php elseif($days <= 30): ?>
                  <span class="label label-warning">EXPIRING SOON</span>
                <?php else: ?>
                  <span class="label label-success">ACTIVE</span>
                <?php endif; ?>
              </td></tr>
              <tr><td><strong>Client</strong></td><td><?= !empty($license->client_name) ? htmlspecialchars($license->client_name) : '<span class="text-muted">—</span>'; ?></td></tr>
              <tr><td><strong>Renewal Amount</strong></td><td><?= !empty($license->renewal_amount) ? store_number_format($license->renewal_amount) : '<span class="text-muted">—</span>'; ?></td></tr>
              <tr><td><strong>Last Renewal</strong></td><td><?= !empty($license->last_renewal_date) ? show_date($license->last_renewal_date) : '<span class="text-muted">—</span>'; ?></td></tr>
              <tr><td><strong>Activated By</strong></td><td><?=$license->activated_by;?></td></tr>
            </table>
            <?php else: ?>
              <div class="alert alert-warning">
                <strong>Not Activated</strong><br>
                This store has not been activated. Generate a license key and activate the subscription.
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Usage Stats -->
        <div class="box box-info">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-chart-pie"></i> Usage</h3>
          </div>
          <div class="box-body">
            <div class="row">
              <div class="col-md-6">
                <div class="info-box">
                  <span class="info-box-icon bg-aqua"><i class="fa fa-building"></i></span>
                  <div class="info-box-content">
                    <span class="info-box-text">Branches Used</span>
                    <span class="info-box-number"><?= $branch_used; ?> / <?= $license->branch_limit ?? 0; ?></span>
                    <?php if($branch_used > ($license->branch_limit ?? 0)): ?>
                      <span class="label label-danger"><i class="fa fa-exclamation-triangle"></i> Limit Exceeded</span>
                    <?php elseif($branch_used >= ($license->branch_limit ?? 0) - 1): ?>
                      <span class="label label-warning">Near Limit</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="info-box">
                  <span class="info-box-icon bg-green"><i class="fa fa-users"></i></span>
                  <div class="info-box-content">
                    <span class="info-box-text">Users Used</span>
                    <span class="info-box-number"><?= $user_used; ?> / <?= $license->user_limit ?? 0; ?></span>
                    <?php if($user_used > ($license->user_limit ?? 0)): ?>
                      <span class="label label-danger"><i class="fa fa-exclamation-triangle"></i> Limit Exceeded</span>
                    <?php elseif($user_used >= ($license->user_limit ?? 0) - 1): ?>
                      <span class="label label-warning">Near Limit</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="info-box">
                  <span class="info-box-icon bg-yellow"><i class="fa fa-cubes"></i></span>
                  <div class="info-box-content">
                    <span class="info-box-text">Products Used</span>
                    <span class="info-box-number"><?= $product_used; ?> / <?= $license->product_limit ?? 500; ?></span>
                    <?php if($product_used > ($license->product_limit ?? 500)): ?>
                      <span class="label label-danger"><i class="fa fa-exclamation-triangle"></i> Limit Exceeded</span>
                    <?php elseif($product_used >= ($license->product_limit ?? 500) - 10): ?>
                      <span class="label label-warning">Near Limit</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="info-box">
                  <span class="info-box-icon bg-purple"><i class="fa fa-wrench"></i></span>
                  <div class="info-box-content">
                    <span class="info-box-text">Services Used</span>
                    <span class="info-box-number"><?= $service_used; ?> / <?= $license->service_limit ?? 100; ?></span>
                    <?php if($service_used > ($license->service_limit ?? 100)): ?>
                      <span class="label label-danger"><i class="fa fa-exclamation-triangle"></i> Limit Exceeded</span>
                    <?php elseif($service_used >= ($license->service_limit ?? 100) - 5): ?>
                      <span class="label label-warning">Near Limit</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="info-box">
                  <span class="info-box-icon bg-red"><i class="fa fa-hdd-o"></i></span>
                  <div class="info-box-content">
                    <span class="info-box-text">Media Storage</span>
                    <span class="info-box-number"><?= $media_used; ?> MB / <?= $license->media_storage_limit_mb ?? 2048; ?> MB</span>
                    <?php if($media_used > ($license->media_storage_limit_mb ?? 2048)): ?>
                      <span class="label label-danger"><i class="fa fa-exclamation-triangle"></i> Limit Exceeded</span>
                    <?php elseif($media_used >= ($license->media_storage_limit_mb ?? 2048) * 0.9): ?>
                      <span class="label label-warning">Near Limit</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
              <div class="col-md-6">
                <div class="info-box">
                  <span class="info-box-icon bg-orange"><i class="fa fa-shopping-bag"></i></span>
                  <div class="info-box-content">
                    <span class="info-box-text">Storefronts Used</span>
                    <span class="info-box-number"><?= $storefront_used; ?> / <?= $license->storefront_limit ?? 1; ?></span>
                    <?php if($storefront_used > ($license->storefront_limit ?? 1)): ?>
                      <span class="label label-danger"><i class="fa fa-exclamation-triangle"></i> Limit Exceeded</span>
                    <?php elseif($storefront_used >= ($license->storefront_limit ?? 1)): ?>
                      <span class="label label-warning">Near Limit</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
            <div class="row">
              <div class="col-md-6">
                <div class="info-box">
                  <span class="info-box-icon bg-teal"><i class="fa fa-globe"></i></span>
                  <div class="info-box-content">
                    <span class="info-box-text">Custom Domains Used</span>
                    <span class="info-box-number"><?= $domain_used; ?> / <?= $license->custom_domain_limit ?? 1; ?></span>
                    <?php if($domain_used > ($license->custom_domain_limit ?? 1)): ?>
                      <span class="label label-danger"><i class="fa fa-exclamation-triangle"></i> Limit Exceeded</span>
                    <?php elseif($domain_used >= ($license->custom_domain_limit ?? 1)): ?>
                      <span class="label label-warning">Near Limit</span>
                    <?php endif; ?>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="box box-default">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-bolt"></i> Actions</h3>
          </div>
          <div class="box-body">
            <a href="<?=base_url('subscription_license/activate_form');?>" class="btn btn-block btn-success">
              <i class="fa fa-key"></i> Activate Subscription
            </a>
            <button type="button" class="btn btn-block btn-info" data-toggle="modal" data-target="#renewModal">
              <i class="fa fa-refresh"></i> Renew / Extend Subscription
            </button>
            <?php if($license && $license->subscription_status !== 'SUSPENDED'): ?>
            <button type="button" class="btn btn-block btn-warning" data-toggle="modal" data-target="#suspendModal">
              <i class="fa fa-ban"></i> Suspend Subscription
            </button>
            <?php elseif($license): ?>
            <button type="button" class="btn btn-block btn-info" onclick="reactivate()">
              <i class="fa fa-play"></i> Reactivate Subscription
            </button>
            <?php endif; ?>
          </div>
        </div>

        <!-- License History -->
        <div class="box box-default">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-history"></i> License History</h3>
          </div>
          <div class="box-body">
            <?php if(!empty($license_history)): ?>
            <table class="table table-bordered table-condensed">
              <thead><tr><th>License Key</th><th>Plan</th><th>Activated</th><th>Status</th><th></th></tr></thead>
              <tbody>
              <?php foreach($license_history as $h):
                $hMasked = strlen($h->license_code) > 12 ? substr($h->license_code, 0, 6) . '***' . substr($h->license_code, -6) : $h->license_code;
              ?>
              <tr>
                <td><code><?= htmlspecialchars($hMasked); ?></code></td>
                <td><?= htmlspecialchars($h->plan_name ?: '-'); ?></td>
                <td><?= !empty($h->activated_at) ? date('Y-m-d H:i', strtotime($h->activated_at)) : '-'; ?></td>
                <td>
                  <?php if($h->status === 'active'): ?>
                    <span class="label label-success">Active</span>
                  <?php elseif($h->status === 'deactivated'): ?>
                    <span class="label label-default">Deactivated</span>
                  <?php else: ?>
                    <span class="label label-warning"><?= ucfirst($h->status); ?></span>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if($h->status === 'active'): ?>
                  <button class="btn btn-xs btn-danger" onclick="deactivateHistory(<?= $h->id; ?>)">Deactivate</button>
                  <?php endif; ?>
                </td>
              </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
            <?php else: ?>
              <p class="text-muted">No license history found.</p>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- Renew / Extend Modal -->
<div class="modal fade" id="renewModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h4 class="modal-title">Renew / Extend Subscription</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>New License Key <span class="text-danger">*</span></label>
          <input type="text" id="renew_license_key" class="form-control" placeholder="Paste the new license key">
          <p class="help-block text-muted">Generate a new license key with updated dates first.</p>
        </div>
        <div class="form-group">
          <label>OTP Code <span class="text-danger">*</span></label>
          <div class="input-group">
            <input type="text" id="renew_otp_code" class="form-control" placeholder="Enter 6-character OTP" maxlength="6" style="text-transform: uppercase;">
            <span class="input-group-btn">
              <button type="button" class="btn btn-default" onclick="requestOTP('renew')">Request OTP</button>
            </span>
          </div>
          <p class="help-block text-muted">OTP sent to authorized email. Expires in 10 minutes.</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-success" onclick="extendSub()">Renew / Extend</button>
      </div>
    </div>
  </div>
</div>

<!-- Suspend Modal -->
<div class="modal fade" id="suspendModal" tabindex="-1" role="dialog">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
        <h4 class="modal-title">Suspend Subscription</h4>
      </div>
      <div class="modal-body">
        <div class="form-group">
          <label>Suspension Reason</label>
          <textarea id="suspend_reason" class="form-control" rows="3"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-warning" onclick="suspend()">Suspend</button>
      </div>
    </div>
  </div>
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
function suspend(){
  var reason = $('#suspend_reason').val();
  $.post('<?=base_url("subscription_license/suspend");?>', withCsrf({reason: reason}), function(res){
    try {
      var r = JSON.parse(res);
      if(r.status === 'success'){ location.reload(); } else { toastr.error(r.message); }
    } catch(e) { toastr.error('Unexpected response'); }
  }).fail(function(){ toastr.error('Request failed'); });
}
function reactivate(){
  if(typeof swal === 'undefined'){
    if(!confirm('Reactivate this subscription?')) return;
    doReactivate();
    return;
  }
  swal({
    title: "Reactivate Subscription?",
    text: "Are you sure you want to reactivate this subscription?",
    icon: "warning",
    buttons: true,
    dangerMode: true
  }).then(function(willReactivate){
    if(willReactivate) doReactivate();
  });
}
function doReactivate(){
  $.post('<?=base_url("subscription_license/reactivate");?>', withCsrf({}), function(res){
    try {
      var r = JSON.parse(res);
      if(r.status === 'success'){ location.reload(); } else { toastr.error(r.message); }
    } catch(e) { toastr.error('Unexpected response'); }
  }).fail(function(){ toastr.error('Request failed'); });
}
function extendSub(){
  var key = $('#renew_license_key').val().trim();
  if(!key){ toastr.warning('Please enter a license key'); return; }
  var otp = $('#renew_otp_code').val().trim();
  if(!otp){ toastr.warning('Please enter OTP'); return; }
  $.post('<?=base_url("subscription_license/extend");?>', withCsrf({license_code: key, otp_code: otp}), function(res){
    try {
      var r = JSON.parse(res);
      if(r.status === 'success'){
        toastr.success(r.message);
        location.reload();
      } else {
        toastr.error(r.message);
      }
    } catch(e) { toastr.error('Unexpected response'); }
  }).fail(function(){ toastr.error('Request failed'); });
}
function showGenerateOTP(){
  $('#otp-group').show();
  $('#gen-submit-btn').show();
  $("button[onclick='showGenerateOTP()']").hide();
  requestOTP('generate');
}
function requestOTP(type){
  $.post('<?=base_url("subscription_license/request_otp");?>', withCsrf({otp_type: type}), function(res){
    try {
      var r = JSON.parse(res);
      if(r.status === 'success'){
        toastr.success(r.message);
      } else {
        toastr.error(r.message || 'Failed to send OTP');
      }
    } catch(e) {
      toastr.error('Unexpected response. Please try again.');
    }
  }).fail(function(){
    toastr.error('Request failed. Please check your connection.');
  });
}
function deactivateHistory(id){
  if(typeof swal === 'undefined'){
    if(!confirm('Deactivate this license?')) return;
    doDeactivateHistory(id);
    return;
  }
  swal({
    title: "Deactivate License?",
    text: "Are you sure you want to deactivate this license?",
    icon: "warning",
    buttons: true,
    dangerMode: true
  }).then(function(willDeactivate){
    if(willDeactivate) doDeactivateHistory(id);
  });
}
function doDeactivateHistory(id){
  $.post('<?=base_url("subscription_license/deactivate_history");?>', withCsrf({history_id: id}), function(res){
    try {
      var r = JSON.parse(res);
      if(r.status === 'success'){ location.reload(); } else { toastr.error(r.message); }
    } catch(e) { toastr.error('Unexpected response'); }
  }).fail(function(){ toastr.error('Request failed'); });
}
$(function(){
  toastr.options = { positionClass: 'toast-top-right', closeButton: true, progressBar: true, timeOut: 4000 };
  $('#generate-form').on('submit', function(e){
    e.preventDefault();
    var btn = $('#gen-submit-btn');
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Generating...');
    $.post('<?=base_url("subscription_license/generate_license");?>', $(this).serialize(), function(res){
      try {
        var r = JSON.parse(res);
        if(r.status === 'success'){
          $('#generated-key-box').show();
          $('#generated-key').val(r.license_key);
          $('#gen-plan').text(r.data.plan_name);
          $('#gen-start').text(r.data.subscription_start_date);
          $('#gen-end').text(r.data.subscription_end_date);
          $('#gen-branch').text(r.data.branch_limit);
          $('#gen-user').text(r.data.user_limit);
          $('#gen-product').text(r.data.product_limit);
          $('#gen-service').text(r.data.service_limit);
          $('#gen-media').text(r.data.media_storage_limit_mb);
          $('#gen-storefront').text(r.data.storefront_limit);
          $('#gen-domain').text(r.data.custom_domain_limit);
          $('#gen-whatsapp').text(r.data.whatsapp_number || '—');
          $('#gen-amount').text(r.data.renewal_amount || '—');
          $('#gen-client').text(r.data.client_name || '—');
          btn.prop('disabled', false).html('<i class="fa fa-check"></i> Confirm & Generate');
        } else if(r.status === 'otp_required'){
          toastr.warning(r.message);
          showGenerateOTP();
          btn.prop('disabled', false).html('<i class="fa fa-check"></i> Confirm & Generate');
        } else {
          toastr.error(r.message);
          btn.prop('disabled', false).html('<i class="fa fa-check"></i> Confirm & Generate');
        }
      } catch(err) {
        toastr.error('Unexpected response. Please try again.');
        btn.prop('disabled', false).html('<i class="fa fa-check"></i> Confirm & Generate');
      }
    }).fail(function(){
      toastr.error('Request failed. Please try again.');
      btn.prop('disabled', false).html('<i class="fa fa-check"></i> Confirm & Generate');
    });
  });
});
function copyKey(){
  var el = document.getElementById('generated-key');
  el.select();
  document.execCommand('copy');
  toastr.success('License key copied to clipboard!');
}
</script>
      <!-- Make sidebar menu highlighter/selector -->
      <script>$(".subscription-license-active-li").addClass("active");</script>
   </body>
</html>
