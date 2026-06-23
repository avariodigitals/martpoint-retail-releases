<!DOCTYPE html>
<html>
<head>
<?php include"comman/code_css.php"; ?>
</head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php include"sidebar.php"; ?>
<?php
  $s = $settings ?? null;
  $secret_key = $s ? $s->secret_key : '';
  $public_key = $s ? $s->public_key : '';
  $enabled = $s ? $s->enabled : 0;
  $test_mode = $s ? ($s->test_mode ?? 1) : 1;
  $webhook_secret = $s ? ($s->webhook_secret ?? '') : '';
?>
<div class="content-wrapper">
  <section class="content-header">
    <h1>Paystack Settings<small>Configure Paystack Payment Gateway</small></h1>
    <ol class="breadcrumb">
      <li><a href="<?php echo $base_url; ?>dashboard"><i class="fa fa-dashboard"></i> Home</a></li>
      <li class="active">Paystack Settings</li>
    </ol>
  </section>
  <section class="content">
    <div class="row">
      <?php include"comman/code_flashdata.php"; ?>
      <div class="col-md-8 col-md-offset-2">
        <div class="box box-primary">
          <div class="box-header with-border">
            <h3 class="box-title"><i class="fa fa-credit-card"></i> Paystack Configuration</h3>
          </div>
          <form class="form-horizontal" id="paystack-settings-form">
            <div class="box-body">
              <div class="alert alert-info">
                <i class="fa fa-info-circle"></i> <strong>Webhook URL:</strong> <code><?=base_url('paystack/webhook');?></code><br>
                <small>Copy this URL into your Paystack Dashboard > Settings > Webhooks</small>
              </div>

              <div class="form-group">
                <label class="col-sm-3 control-label">Enable Paystack</label>
                <div class="col-sm-6">
                  <select class="form-control" id="enabled" name="enabled">
                    <option value="0" <?=($enabled==0)?'selected':'';?>>Disabled</option>
                    <option value="1" <?=($enabled==1)?'selected':'';?>>Enabled</option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-3 control-label">Mode</label>
                <div class="col-sm-6">
                  <select class="form-control" id="test_mode" name="test_mode">
                    <option value="1" <?=($test_mode==1)?'selected':'';?>>Test Mode</option>
                    <option value="0" <?=($test_mode==0)?'selected':'';?>>Live Mode</option>
                  </select>
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-3 control-label">Secret Key <span class="text-danger">*</span></label>
                <div class="col-sm-6">
                  <input type="text" class="form-control" id="secret_key" name="secret_key" value="<?=htmlspecialchars($secret_key);?>" placeholder="sk_test_... or sk_live_...">
                  <span id="secret_key_msg" style="display:none" class="text-danger"></span>
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-3 control-label">Public Key <span class="text-danger">*</span></label>
                <div class="col-sm-6">
                  <input type="text" class="form-control" id="public_key" name="public_key" value="<?=htmlspecialchars($public_key);?>" placeholder="pk_test_... or pk_live_...">
                  <span id="public_key_msg" style="display:none" class="text-danger"></span>
                </div>
              </div>

              <div class="form-group">
                <label class="col-sm-3 control-label">Webhook Secret (Optional)</label>
                <div class="col-sm-6">
                  <input type="text" class="form-control" id="webhook_secret" name="webhook_secret" value="<?=htmlspecialchars($webhook_secret);?>" placeholder="For webhook signature verification">
                </div>
              </div>

            </div>
            <div class="box-footer">
              <div class="col-sm-8 col-sm-offset-2 text-center">
                <div class="col-md-3 col-md-offset-3">
                  <button type="button" id="save_settings" class="btn btn-block btn-success">Save Settings</button>
                </div>
                <div class="col-sm-3">
                  <a href="<?=base_url('dashboard');?>"><button type="button" class="col-sm-3 btn btn-block btn-warning close_btn">Close</button></a>
                </div>
              </div>
            </div>
          </form>
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
<script src="<?php echo $theme_link; ?>js/paystack_settings.js"></script>
<script>$(".paystack_settings-active-li").addClass("active");</script>
</body>
</html>
