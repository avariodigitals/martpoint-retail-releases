<!DOCTYPE html>
<html>
<head>
<?php include"comman/code_css.php"; ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php include"sidebar.php"; ?>

<div class="content-wrapper">
  <section class="content-header">
    <h1>Expiry Settings <small>Configure expiry alerts and blocking</small></h1>
    <ol class="breadcrumb">
      <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Expiry Settings</li>
    </ol>
  </section>
  <section class="content">
    <div class="row">
      <div class="col-md-6">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title">Expiry Configuration</h3>
          </div>
          <?= form_open('#', array('id' => 'expiry-settings-form')); ?>
          <div class="box-body">
            <div class="form-group">
              <label for="alert_before_days">Alert Before (Days)</label>
              <input type="number" class="form-control" id="alert_before_days" name="alert_before_days" value="<?= $settings->alert_before_days; ?>" min="1" max="365">
              <small class="text-muted">How many days before expiry to show warnings</small>
            </div>
            <div class="form-group">
              <div class="checkbox">
                <label>
                  <input type="checkbox" id="stop_selling_expired" name="stop_selling_expired" value="1" <?= $settings->stop_selling_expired ? 'checked' : ''; ?>>
                  <strong>Stop Selling Expired Items</strong>
                </label>
              </div>
              <small class="text-muted">Block expired items from being added to POS/Sales cart</small>
            </div>
            <div class="form-group">
              <div class="checkbox">
                <label>
                  <input type="checkbox" id="email_alerts_enabled" name="email_alerts_enabled" value="1" <?= $settings->email_alerts_enabled ? 'checked' : ''; ?>>
                  <strong>Enable Email Alerts</strong>
                </label>
              </div>
            </div>
            <div class="form-group">
              <label for="alert_email">Alert Email Address</label>
              <input type="email" class="form-control" id="alert_email" name="alert_email" value="<?= $settings->alert_email; ?>" placeholder="manager@store.com">
              <small class="text-muted">Email to receive expiry alerts</small>
            </div>
          </div>
          <div class="box-footer">
            <button type="button" id="save-btn" class="btn btn-primary">Save Settings</button>
            <button type="button" id="send-alert-btn" class="btn btn-warning" style="margin-left:10px;"><i class="fa fa-envelope"></i> Send Email Alert Now</button>
          </div>
          <?= form_close(); ?>
        </div>
      </div>
      <div class="col-md-6">
        <div class="box box-info">
          <div class="box-header with-border">
            <h3 class="box-title">Current Status</h3>
          </div>
          <div class="box-body">
            <div class="alert alert-danger">
              <h4><i class="icon fa fa-ban"></i> Expired Items: <?= $this->expiry->count_expired(); ?></h4>
              Items that have passed their expiry date and should not be sold.
            </div>
            <div class="alert alert-warning">
              <h4><i class="icon fa fa-warning"></i> Expiring Soon: <?= $this->expiry->count_expiring(); ?></h4>
              Items expiring within <?= $settings->alert_before_days; ?> days.
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</div>

<?php include"footer.php"; ?>
<div class="control-sidebar-bg"></div>
</div>
<?php include"comman/code_js_sound.php"; ?>
<?php include"comman/code_js.php"; ?>
<script>
$(function(){
  $('#save-btn').on('click', function(){
    var data = $('#expiry-settings-form').serialize();
    $.post('<?= base_url("expiry_settings/save"); ?>', data, function(res){
      if(res == 'success'){
        toastr['success']('Settings saved successfully!');
      } else {
        toastr['error']('Failed to save settings.');
      }
    });
  });

  $('#send-alert-btn').on('click', function(){
    $(this).attr('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
    $.post('<?= base_url("expiry_settings/send_email_alert"); ?>', function(res){
      if(res == 'success'){
        toastr['success']('Email alert sent successfully!');
      } else {
        toastr['warning'](res);
      }
      $('#send-alert-btn').attr('disabled', false).html('<i class="fa fa-envelope"></i> Send Email Alert Now');
    });
  });
});
</script>
<script>$('.expiry_settings-active-li').addClass('active');</script>
</body>
</html>
