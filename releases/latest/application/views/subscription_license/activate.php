<!DOCTYPE html>
<html>
   <head>
      <?php $this->load->view('comman/code_css.php');?>
      <style>
        .box-body .form-control { min-height: 38px; padding: 8px 12px; }
        .box-body input[type="text"].form-control { height: 38px; }
      </style>
   </head>
   <body class="hold-transition skin-blue sidebar-mini">
      <div class="wrapper">
         <?php $this->load->view('sidebar');?>

<div class="content-wrapper">
  <section class="content-header">
    <h1><?= ($license && !empty($license->license_code)) ? 'Renew Subscription' : 'Activate MartPoint Retail'; ?></h1>
    <ol class="breadcrumb">
      <li><a href="<?=base_url('dashboard');?>"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="<?=base_url('subscription_license');?>">Subscription Control</a></li>
      <li class="active"><?= ($license && !empty($license->license_code)) ? 'Renew' : 'Activate'; ?></li>
    </ol>
  </section>

  <section class="content">
    <div class="row">
      <div class="col-md-6 col-md-offset-3">

        <!-- Activation Form -->
        <div class="box box-primary" id="activation-box">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-key"></i> Enter License Key</h3>
          </div>
          <form id="activation-form">
            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name();?>" value="<?php echo $this->security->get_csrf_hash();?>">
            <div class="box-body">
              <div class="form-group">
                <label>License Key <span class="text-danger">*</span></label>
                <input type="text" name="license_code" id="license_code" class="form-control" placeholder="Paste your license key here (e.g. MP-XXXX...-XXXX)" required>
                <p class="help-block text-muted">Your license key was generated in License Management.</p>
              </div>
              <div class="form-group" id="act-otp-group" style="display:none;">
                <label>OTP Code <span class="text-danger">*</span></label>
                <div class="input-group">
                  <input type="text" name="otp_code" id="act_otp_code" class="form-control" placeholder="Enter 6-character OTP" maxlength="6" style="text-transform: uppercase;">
                  <span class="input-group-btn">
                    <button type="button" class="btn btn-default" onclick="requestActOTP()">Request OTP</button>
                  </span>
                </div>
                <p class="help-block text-muted">OTP sent to authorized email. Expires in 10 minutes.</p>
              </div>
            </div>
            <div class="box-footer">
              <button type="submit" class="btn btn-primary btn-block">
                <i class="fa fa-check"></i> Activate Subscription
              </button>
            </div>
          </form>
        </div>

        <!-- Activation Success Result (hidden initially) -->
        <div class="box box-success" id="activation-result" style="display:none;">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-check-circle"></i> Subscription Activated Successfully</h3>
          </div>
          <div class="box-body">
            <table class="table table-bordered">
              <tr><td style="width:35%"><strong>Plan</strong></td><td id="res-plan"></td></tr>
              <tr><td><strong>Start Date</strong></td><td id="res-start"></td></tr>
              <tr><td><strong>End Date</strong></td><td id="res-end"></td></tr>
              <tr><td><strong>Branch Limit</strong></td><td id="res-branch"></td></tr>
              <tr><td><strong>User Limit</strong></td><td id="res-user"></td></tr>
              <tr><td><strong>Days Left</strong></td><td id="res-days"></td></tr>
            </table>
          </div>
          <div class="box-footer">
            <a href="<?=base_url('subscription_license');?>" class="btn btn-success btn-block">
              <i class="fa fa-arrow-right"></i> Go to License Management
            </a>
          </div>
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
function requestActOTP(){
  $.post('<?=base_url("subscription_license/request_otp");?>', withCsrf({otp_type: 'activate'}), function(res){
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
$(function(){
  toastr.options = { positionClass: 'toast-top-right', closeButton: true, progressBar: true, timeOut: 4000 };
  $('#activation-form').on('submit', function(e){
    e.preventDefault();
    var btn = $(this).find('button[type=submit]');
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Activating...');
    $.post('<?=base_url("subscription_license/save_activation");?>', $(this).serialize(), function(res){
      try {
        var r = JSON.parse(res);
        if(r.status === 'success'){
          $('#activation-box').hide();
          $('#activation-result').show();
          var d = r.data;
          $('#res-plan').text(d.plan_name);
          $('#res-start').text(d.subscription_start_date);
          $('#res-end').text(d.subscription_end_date);
          $('#res-branch').text(d.branch_limit);
          $('#res-user').text(d.user_limit);
          $('#res-days').html('<span class="label label-success">' + d.days_left + ' Days</span>');
        } else if(r.status === 'otp_required'){
          toastr.warning(r.message);
          $('#act-otp-group').show();
          btn.prop('disabled', false).html('<i class="fa fa-check"></i> Activate Subscription');
        } else {
          toastr.error(r.message);
          btn.prop('disabled', false).html('<i class="fa fa-check"></i> Activate Subscription');
        }
      } catch(err) {
        toastr.error('Unexpected response. Please try again.');
        btn.prop('disabled', false).html('<i class="fa fa-check"></i> Activate Subscription');
      }
    }).fail(function(){
      toastr.error('Request failed. Please try again.');
      btn.prop('disabled', false).html('<i class="fa fa-check"></i> Activate Subscription');
    });
  });
});
</script>
      <!-- Make sidebar menu highlighter/selector -->
      <script>$(".subscription-license-active-li").addClass("active");</script>
   </body>
</html>
