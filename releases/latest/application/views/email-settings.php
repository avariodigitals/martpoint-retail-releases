<!DOCTYPE html>
<html>
<head>
<!-- FORM CSS CODE -->
<?php include"comman/code_css.php"; ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">

 <?php include"sidebar.php"; ?>

  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
      <h1><?= $page_title ?></h1>
      <ol class="breadcrumb">
        <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
        <li class="active"><?= $page_title; ?></li>
      </ol>
    </section>

    <!-- Main content -->
    <section class="content">
      <div class="row">
        <div class="col-md-12">

          <!-- Intro Card -->
          <div class="callout callout-info">
            <h4><i class="fa fa-envelope"></i> Email Configuration</h4>
            <p>Configure how MartPoint sends emails for invoices, receipts, payment links, daily summaries, staff accounts, password resets and customer reminders.</p>
          </div>

          <!-- Nav tabs -->
          <div class="nav-tabs-custom">
            <ul class="nav nav-tabs">
              <li class="active"><a href="#tab-provider" data-toggle="tab"><i class="fa fa-plug"></i> Provider</a></li>
              <li><a href="#tab-sender" data-toggle="tab"><i class="fa fa-user"></i> Sender</a></li>
              <li><a href="#tab-smtp" data-toggle="tab"><i class="fa fa-server"></i> SMTP</a></li>
              <li><a href="#tab-resend" data-toggle="tab"><i class="fa fa-paper-plane"></i> Resend</a></li>
              <li><a href="#tab-test" data-toggle="tab"><i class="fa fa-vial"></i> Test</a></li>
              <li><a href="<?=base_url('email_settings/templates');?>"><i class="fa fa-file-text-o"></i> Templates</a></li>
              <li><a href="<?=base_url('email_settings/schedules');?>"><i class="fa fa-clock-o"></i> Scheduled Reports</a></li>
              <li><a href="<?=base_url('email_settings/logs');?>"><i class="fa fa-list-alt"></i> Logs</a></li>
            </ul>

            <div class="tab-content">
              <div class="tab-pane active" id="tab-provider">
                <div class="box box-primary">
                  <div class="box-header with-border">
                    <h3 class="box-title">Active Email Provider</h3>
                  </div>
                  <div class="box-body">
                    <p class="text-muted">Choose SMTP if you want to use your own mail server. Choose Resend if you want a modern email API provider.</p>
                    <div class="form-group">
                      <label>Active Provider</label>
                      <select class="form-control select2" id="email_provider" name="email_provider">
                        <option value="smtp" <?=($settings['provider']=='smtp'?'selected':'');?>>SMTP</option>
                        <option value="resend" <?=($settings['provider']=='resend'?'selected':'');?>>Resend</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <button type="button" class="btn btn-success" id="save-provider"><i class="fa fa-save"></i> Save Provider</button>
                    </div>
                  </div>
                </div>
              </div>

              <div class="tab-pane" id="tab-sender">
                <div class="box box-primary">
                  <div class="box-header with-border">
                    <h3 class="box-title">Sender Identity</h3>
                  </div>
                  <div class="box-body">
                    <div class="form-group">
                      <label>From Name</label>
                      <input type="text" class="form-control" id="email_from_name" value="<?=htmlspecialchars($settings['from_name']);?>" placeholder="MartPoint Retail">
                    </div>
                    <div class="form-group">
                      <label>From Email</label>
                      <input type="email" class="form-control" id="email_from_email" value="<?=htmlspecialchars($settings['from_email']);?>" placeholder="noreply@yourdomain.com">
                    </div>
                    <div class="form-group">
                      <label>Reply-To Email</label>
                      <input type="email" class="form-control" id="email_reply_to" value="<?=htmlspecialchars($settings['reply_to']);?>" placeholder="support@yourdomain.com">
                    </div>
                    <div class="form-group">
                      <button type="button" class="btn btn-success" id="save-sender"><i class="fa fa-save"></i> Save Sender</button>
                    </div>
                  </div>
                </div>
              </div>

              <div class="tab-pane" id="tab-smtp">
                <div class="box box-primary">
                  <div class="box-header with-border">
                    <h3 class="box-title">SMTP Configuration</h3>
                    <div class="box-tools pull-right">
                      <span class="badge <?=($settings['smtp_host']&&$settings['smtp_user']&&$settings['smtp_pass']?'bg-green':'bg-red');?>">
                        <?=($settings['smtp_host']&&$settings['smtp_user']&&$settings['smtp_pass']?'Configured':'Not Configured');?>
                      </span>
                    </div>
                  </div>
                  <div class="box-body">
                    <div class="form-group">
                      <label>SMTP Status</label>
                      <select class="form-control select2" id="smtp_status">
                        <option value="1" <?=($settings['smtp_status']==1?'selected':'');?>>Enable</option>
                        <option value="0" <?=($settings['smtp_status']==0?'selected':'');?>>Disable</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label>SMTP Host</label>
                      <input type="text" class="form-control" id="smtp_host" value="<?=htmlspecialchars($settings['smtp_host']);?>" placeholder="mail.yourdomain.com">
                    </div>
                    <div class="form-group">
                      <label>SMTP Port</label>
                      <select class="form-control select2" id="smtp_port">
                        <option value="" <?=empty($settings['smtp_port'])?'selected':'';?>>-- Select Port --</option>
                        <option value="25" <?=($settings['smtp_port']=='25'?'selected':'');?>>25 (Plain)</option>
                        <option value="465" <?=($settings['smtp_port']=='465'?'selected':'');?>>465 (SSL)</option>
                        <option value="587" <?=($settings['smtp_port']=='587'?'selected':'');?>>587 (TLS / STARTTLS)</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label>SMTP Username</label>
                      <input type="text" class="form-control" id="smtp_user" value="<?=htmlspecialchars($settings['smtp_user']);?>" placeholder="email@yourdomain.com">
                    </div>
                    <div class="form-group">
                      <label>SMTP Password</label>
                      <div class="input-group">
                        <input type="password" class="form-control" id="smtp_pass" value="<?=htmlspecialchars($settings['smtp_pass']);?>" placeholder="Leave blank to keep existing">
                        <span class="input-group-btn">
                          <button type="button" class="btn btn-default" id="btn-toggle-pass" title="Show/Hide Password"><i class="fa fa-eye" id="icon-pass"></i></button>
                        </span>
                      </div>
                      <small class="text-muted">Click the eye icon to reveal password.</small>
                    </div>
                    <div class="form-group">
                      <label>Encryption</label>
                      <select class="form-control select2" id="smtp_crypto">
                        <option value="" <?=empty($settings['smtp_crypto'])?'selected':'';?>>Auto-detect</option>
                        <option value="ssl" <?=($settings['smtp_crypto']=='ssl'?'selected':'');?>>SSL</option>
                        <option value="tls" <?=($settings['smtp_crypto']=='tls'?'selected':'');?>>TLS</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <button type="button" class="btn btn-success" id="save-smtp"><i class="fa fa-save"></i> Save SMTP</button>
                    </div>
                  </div>
                </div>
              </div>

              <div class="tab-pane" id="tab-resend">
                <div class="box box-primary">
                  <div class="box-header with-border">
                    <h3 class="box-title">Resend Configuration</h3>
                    <div class="box-tools pull-right">
                      <span class="badge <?=($settings['resend_api_key']&&$settings['resend_from_email']?'bg-green':'bg-red');?>">
                        <?=($settings['resend_api_key']&&$settings['resend_from_email']?'Configured':'Not Configured');?>
                      </span>
                    </div>
                  </div>
                  <div class="box-body">
                    <p class="text-muted">Sign up at <a href="https://resend.com" target="_blank">resend.com</a> to get your API key.</p>
                    <div class="form-group">
                      <label>Resend API Key</label>
                      <input type="text" class="form-control" id="resend_api_key" value="<?=htmlspecialchars($settings['resend_api_key']);?>" placeholder="re_xxxxxxxxxxxx">
                    </div>
                    <div class="form-group">
                      <label>Resend From Email</label>
                      <input type="email" class="form-control" id="resend_from_email" value="<?=htmlspecialchars($settings['resend_from_email']);?>" placeholder="onboarding@resend.dev">
                    </div>
                    <div class="form-group">
                      <label>Resend From Name</label>
                      <input type="text" class="form-control" id="resend_from_name" value="<?=htmlspecialchars($settings['resend_from_name']);?>" placeholder="MartPoint Retail">
                    </div>
                    <div class="form-group">
                      <button type="button" class="btn btn-success" id="save-resend"><i class="fa fa-save"></i> Save Resend</button>
                    </div>
                  </div>
                </div>
              </div>

              <div class="tab-pane" id="tab-test">
                <div class="box box-primary">
                  <div class="box-header with-border">
                    <h3 class="box-title">Send Test Email</h3>
                  </div>
                  <div class="box-body">
                    <div class="form-group">
                      <label>Recipient Email</label>
                      <div class="input-group">
                        <input type="email" class="form-control" id="test_email" placeholder="Enter email to test">
                        <span class="input-group-btn">
                          <button type="button" class="btn btn-info" id="btn-test-active"><i class="fa fa-paper-plane"></i> Test Active Provider</button>
                        </span>
                      </div>
                      <span id="test_result" style="display:block;margin-top:8px;" class="text-muted"></span>
                    </div>
                    <hr>
                    <div class="row">
                      <div class="col-md-6">
                        <button type="button" class="btn btn-default btn-block" id="btn-test-smtp"><i class="fa fa-server"></i> Test SMTP Only</button>
                      </div>
                      <div class="col-md-6">
                        <button type="button" class="btn btn-default btn-block" id="btn-test-resend"><i class="fa fa-paper-plane"></i> Test Resend Only</button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

            </div><!-- /.tab-content -->
          </div><!-- /.nav-tabs-custom -->

        </div>
      </div>
    </section>
    <!-- /.content -->
  </div>
  <!-- /.content-wrapper -->

 <?php include"footer.php"; ?>
  <div class="control-sidebar-bg"></div>
</div>
<!-- ./wrapper -->
<!-- SOUND CODE -->
<?php include"comman/code_js_sound.php"; ?>
<!-- TABLES CODE -->
<?php include"comman/code_js.php"; ?>

<script>
/* Helpers */
function showResult(el, msg, type) {
  el.removeClass('text-success text-danger text-muted').addClass(type).text(msg);
}

function csrfData() {
  return {
    '<?=$this->security->get_csrf_token_name();?>': '<?=$this->security->get_csrf_hash();?>'
  };
}

/* Save Provider */
$('#save-provider').on('click', function(){
  var btn = $(this); btn.attr('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
  $.post('<?=base_url("email_settings/save");?>', $.extend(csrfData(), {
    email_provider: $('#email_provider').val()
  }), function(res){
    btn.attr('disabled', false).html('<i class="fa fa-save"></i> Save Provider');
    if(res=='success') toastr['success']('Provider saved.'); else toastr['error']('Failed to save.');
  });
});

/* Save Sender */
$('#save-sender').on('click', function(){
  var btn = $(this); btn.attr('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
  $.post('<?=base_url("email_settings/save");?>', $.extend(csrfData(), {
    email_from_name:  $('#email_from_name').val(),
    email_from_email: $('#email_from_email').val(),
    email_reply_to:   $('#email_reply_to').val()
  }), function(res){
    btn.attr('disabled', false).html('<i class="fa fa-save"></i> Save Sender');
    if(res=='success') toastr['success']('Sender identity saved.'); else toastr['error']('Failed to save.');
  });
});

/* Save SMTP */
$('#save-smtp').on('click', function(){
  var btn = $(this); btn.attr('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
  $.post('<?=base_url("email_settings/save");?>', $.extend(csrfData(), {
    smtp_status: $('#smtp_status').val(),
    smtp_host:   $('#smtp_host').val(),
    smtp_port:   $('#smtp_port').val(),
    smtp_user:   $('#smtp_user').val(),
    smtp_pass:   $('#smtp_pass').val(),
    smtp_crypto: $('#smtp_crypto').val()
  }), function(res){
    btn.attr('disabled', false).html('<i class="fa fa-save"></i> Save SMTP');
    if(res=='success') toastr['success']('SMTP settings saved.'); else toastr['error']('Failed to save.');
  });
});

/* Toggle SMTP Password Visibility */
$('#btn-toggle-pass').on('click', function(){
  var input = $('#smtp_pass');
  var icon = $('#icon-pass');
  if(input.attr('type') === 'password'){
    input.attr('type', 'text');
    icon.removeClass('fa-eye').addClass('fa-eye-slash');
  } else {
    input.attr('type', 'password');
    icon.removeClass('fa-eye-slash').addClass('fa-eye');
  }
});

/* Save Resend */
$('#save-resend').on('click', function(){
  var btn = $(this); btn.attr('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
  $.post('<?=base_url("email_settings/save");?>', $.extend(csrfData(), {
    resend_api_key:     $('#resend_api_key').val(),
    resend_from_email:  $('#resend_from_email').val(),
    resend_from_name:   $('#resend_from_name').val()
  }), function(res){
    btn.attr('disabled', false).html('<i class="fa fa-save"></i> Save Resend');
    if(res=='success') toastr['success']('Resend settings saved.'); else toastr['error']('Failed to save.');
  });
});

/* Test Email */
function sendTest(provider, btnId) {
  var email = $('#test_email').val().trim();
  var resultEl = $('#test_result');
  if(!email){ showResult(resultEl, 'Please enter an email address.', 'text-danger'); return; }

  var btn = $(btnId);
  btn.attr('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
  showResult(resultEl, 'Sending test email...', 'text-muted');

  $.ajax({
    type: 'POST',
    url: '<?=base_url("email_settings/test_email");?>',
    data: $.extend(csrfData(), { test_email: email, provider: provider }),
    dataType: 'json',
    success: function(res){
      btn.attr('disabled', false);
      if(btnId=='#btn-test-active') btn.html('<i class="fa fa-paper-plane"></i> Test Active Provider');
      if(btnId=='#btn-test-smtp')  btn.html('<i class="fa fa-server"></i> Test SMTP Only');
      if(btnId=='#btn-test-resend')btn.html('<i class="fa fa-paper-plane"></i> Test Resend Only');

      if(res.status === 'success'){
        showResult(resultEl, res.message, 'text-success');
        toastr['success'](res.message);
      } else {
        showResult(resultEl, res.message, 'text-danger');
        toastr['error'](res.message);
      }
    },
    error: function(){
      btn.attr('disabled', false);
      if(btnId=='#btn-test-active') btn.html('<i class="fa fa-paper-plane"></i> Test Active Provider');
      if(btnId=='#btn-test-smtp')  btn.html('<i class="fa fa-server"></i> Test SMTP Only');
      if(btnId=='#btn-test-resend')btn.html('<i class="fa fa-paper-plane"></i> Test Resend Only');
      showResult(resultEl, 'Request failed. Check connection.', 'text-danger');
      toastr['error']('Request failed.');
    }
  });
}

$('#btn-test-active').on('click', function(){ sendTest('', '#btn-test-active'); });
$('#btn-test-smtp').on('click', function(){ sendTest('smtp', '#btn-test-smtp'); });
$('#btn-test-resend').on('click', function(){ sendTest('resend', '#btn-test-resend'); });
</script>
<!-- Make sidebar menu highlight -->
<script>$(".smtp-active-li").addClass("active");</script>

</body>
</html>
