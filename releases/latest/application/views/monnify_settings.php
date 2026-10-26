<?php
/* Monnify (Moniepoint) Settings — content-only view for mp_layout */
?>
<?php $this->load->view('admin/desktop/_styles'); ?>
<?php
  $s = $settings ?? null;
  $api_key = $s ? $s->api_key : '';
  $secret_key = $s ? $s->secret_key : '';
  $contract_code = $s ? $s->contract_code : '';
  $wallet_account_number = $s ? ($s->wallet_account_number ?? '') : '';
  $enabled = $s ? $s->enabled : 0;
  $disbursements_enabled = $s ? ($s->disbursements_enabled ?? 0) : 0;
  $test_mode = $s ? ($s->test_mode ?? 1) : 1;
?>
<?php include"comman/code_flashdata.php"; ?>

<div class="mp-page-head">
  <h1 class="mp-page-title">Monnify Settings</h1>
</div>

<div class="mp-card">
  <div class="mp-card-body">
    <form class="form-horizontal" id="monnify-settings-form">
      <div class="alert alert-info">
        <i class="fa fa-info-circle"></i> <strong>Webhook URL:</strong> <code><?=base_url('monnify/webhook');?></code><br>
        <small>Copy this URL into your Monnify Dashboard &gt; Developers &gt; Webhook URLs (Transaction Completion and Disbursement).<br>
        Whitelist Monnify's webhook IP <code>35.242.133.146</code> on your server for live notifications.</small>
      </div>

      <div class="form-group">
        <label class="col-sm-3 control-label">Enable Monnify</label>
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
            <option value="1" <?=($test_mode==1)?'selected':'';?>>Sandbox</option>
            <option value="0" <?=($test_mode==0)?'selected':'';?>>Live</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="col-sm-3 control-label">API Key <span class="text-danger">*</span></label>
        <div class="col-sm-6">
          <input type="text" class="form-control" id="api_key" name="api_key" value="<?=htmlspecialchars($api_key);?>" placeholder="MK_TEST_... or MK_PROD_...">
          <span id="api_key_msg" style="display:none" class="text-danger"></span>
        </div>
      </div>

      <div class="form-group">
        <label class="col-sm-3 control-label">Secret Key <span class="text-danger">*</span></label>
        <div class="col-sm-6">
          <input type="text" class="form-control" id="secret_key" name="secret_key" value="<?=htmlspecialchars($secret_key);?>" placeholder="Monnify Secret Key">
          <span id="secret_key_msg" style="display:none" class="text-danger"></span>
        </div>
      </div>

      <div class="form-group">
        <label class="col-sm-3 control-label">Contract Code <span class="text-danger">*</span></label>
        <div class="col-sm-6">
          <input type="text" class="form-control" id="contract_code" name="contract_code" value="<?=htmlspecialchars($contract_code);?>" placeholder="Contract code from Developers > API Keys & Contracts">
          <span id="contract_code_msg" style="display:none" class="text-danger"></span>
        </div>
      </div>

      <hr>
      <h4 class="text-center"><i class="fa fa-paper-plane"></i> Transfers / Disbursements</h4>
      <div class="alert alert-warning">
        <i class="fa fa-exclamation-triangle"></i>
        Disbursements require Monnify approval (Dashboard &gt; Settings &gt; Preferences &gt; Disbursements) and
        <strong>your server's static IP must be whitelisted</strong> before live transfers will process.
        OTP authorization is enabled by default on your Monnify account — contact
        <a href="mailto:integration-support@monnify.com">integration-support@monnify.com</a> to disable it for
        fully automated payouts.
      </div>

      <div class="form-group">
        <label class="col-sm-3 control-label">Enable Transfers</label>
        <div class="col-sm-6">
          <select class="form-control" id="disbursements_enabled" name="disbursements_enabled">
            <option value="0" <?=($disbursements_enabled==0)?'selected':'';?>>Disabled</option>
            <option value="1" <?=($disbursements_enabled==1)?'selected':'';?>>Enabled</option>
          </select>
        </div>
      </div>

      <div class="form-group">
        <label class="col-sm-3 control-label">Wallet Account Number</label>
        <div class="col-sm-6">
          <input type="text" class="form-control" id="wallet_account_number" name="wallet_account_number" value="<?=htmlspecialchars($wallet_account_number);?>" placeholder="Monnify wallet account number (source of transfers)">
          <span id="wallet_account_number_msg" style="display:none" class="text-danger"></span>
        </div>
      </div>

      <div class="col-sm-8 col-sm-offset-2 text-center">
        <div class="col-md-3 col-md-offset-3">
          <button type="button" id="save_settings" class="mp-btn-primary">Save Settings</button>
        </div>
        <div class="col-sm-3">
          <a href="<?=base_url('dashboard');?>"><button type="button" class="col-sm-3 mp-btn-secondary close_btn">Close</button></a>
        </div>
      </div>
    </form>
  </div>
</div>

<script src="<?php echo $theme_link; ?>js/monnify_settings.js"></script>
<script>$(".monnify-settings-active-li").addClass("active");$(".monnify-settings-active-li").closest(".mp-nav-group").addClass("open");</script>
