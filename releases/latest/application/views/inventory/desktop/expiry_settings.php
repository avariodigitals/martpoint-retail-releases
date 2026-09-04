<?php $this->load->view('inventory/desktop/_styles'); ?>

<div class="mp-page-head">
  <div>
    <h2>Expiry Settings</h2>
    <div class="mp-page-sub">Configure expiry alerts and blocking</div>
  </div>
</div>

<div class="mp-row" style="grid-template-columns:1fr 1fr;align-items:start;">
  <div class="mp-card">
    <div class="mp-card-head"><h3>Expiry Configuration</h3></div>
    <div class="mp-card-body">
      <?= form_open('#', ['id' => 'expiry-settings-form']); ?>
      <div class="mp-form-grid">
        <div class="mp-form-group">
          <label for="alert_before_days">Alert Before (Days)</label>
          <input type="number" class="mp-form-control" id="alert_before_days" name="alert_before_days" value="<?= $settings->alert_before_days; ?>" min="1" max="365">
          <p class="mp-form-hint">How many days before expiry to show warnings</p>
        </div>
        <div class="mp-form-group">
          <label for="alert_email">Alert Email Address</label>
          <input type="email" class="mp-form-control" id="alert_email" name="alert_email" value="<?= $settings->alert_email; ?>" placeholder="manager@store.com">
          <p class="mp-form-hint">Email to receive expiry alerts</p>
        </div>
      </div>
      <div style="display:flex;flex-direction:column;gap:12px;margin-top:20px;">
        <label style="display:flex;align-items:center;gap:10px;font-weight:500;cursor:pointer;">
          <input type="checkbox" id="stop_selling_expired" name="stop_selling_expired" value="1" <?= $settings->stop_selling_expired ? 'checked' : ''; ?> style="width:18px;height:18px;accent-color:var(--mp-primary);">
          Stop Selling Expired Items
        </label>
        <p class="mp-form-hint">Block expired items from being added to POS/Sales cart</p>
        <label style="display:flex;align-items:center;gap:10px;font-weight:500;cursor:pointer;margin-top:4px;">
          <input type="checkbox" id="email_alerts_enabled" name="email_alerts_enabled" value="1" <?= $settings->email_alerts_enabled ? 'checked' : ''; ?> style="width:18px;height:18px;accent-color:var(--mp-primary);">
          Enable Email Alerts
        </label>
      </div>
      <div class="mp-form-actions" style="margin-top:24px;">
        <button type="button" id="save-btn" class="mp-btn-primary">Save Settings</button>
        <button type="button" id="send-alert-btn" class="mp-btn-secondary"><i class="fa fa-envelope"></i> Send Email Alert Now</button>
      </div>
      <?= form_close(); ?>
    </div>
  </div>

  <div class="mp-card">
    <div class="mp-card-head"><h3>Current Status</h3></div>
    <div class="mp-card-body">
      <div style="border:1px solid var(--mp-border);border-radius:12px;padding:16px;background:rgba(220,38,38,.05);margin-bottom:16px;">
        <div style="display:flex;align-items:center;gap:12px;color:var(--mp-danger);font-weight:700;font-size:16px;"><i class="fa fa-ban"></i> Expired Items: <?= $this->expiry->count_expired(); ?></div>
        <p style="margin:6px 0 0;color:var(--mp-muted);font-size:13px;">Items that have passed their expiry date and should not be sold.</p>
      </div>
      <div style="border:1px solid var(--mp-border);border-radius:12px;padding:16px;background:rgba(245,158,11,.05);">
        <div style="display:flex;align-items:center;gap:12px;color:var(--mp-warning);font-weight:700;font-size:16px;"><i class="fa fa-warning"></i> Expiring Soon: <?= $this->expiry->count_expiring(); ?></div>
        <p style="margin:6px 0 0;color:var(--mp-muted);font-size:13px;">Items expiring within <?= $settings->alert_before_days; ?> days.</p>
      </div>
    </div>
  </div>
</div>

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
<script>$(".expiry-settings-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
