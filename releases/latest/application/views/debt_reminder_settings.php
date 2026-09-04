<?php
/* Debt Reminder Settings — content-only view for mp_layout */
?>
<?php $this->load->view('admin/desktop/_styles'); ?>
<style>
.mp-toggle input[type="checkbox"] { width:44px; height:24px; -webkit-appearance:none; appearance:none; background:#CBD5E1; border-radius:12px; position:relative; cursor:pointer; outline:none; transition:background .2s; }
.mp-toggle input[type="checkbox"]:checked { background:#10B981; }
.mp-toggle input[type="checkbox"]::after { content:''; position:absolute; width:20px; height:20px; background:#fff; border-radius:50%; top:2px; left:2px; transition:left .2s; box-shadow:0 1px 3px rgba(0,0,0,0.2); }
.mp-toggle input[type="checkbox"]:checked::after { left:22px; }
/* Protect toggle from iCheck + mobile min-height overrides */
.mp-toggle input.mp-toggle-input {
  -webkit-appearance: none !important;
  appearance: none !important;
  min-height: 0 !important;
  width:44px; height:24px;
}
/* Channel checkboxes: keep native appearance, just protect from iCheck/mobile min-height */
.mp-channel-input {
  -webkit-appearance: checkbox !important;
  appearance: auto !important;
  min-height: 0 !important;
  width:18px !important;
  height:18px !important;
  cursor:pointer !important;
}
.mp-label { font-size:14px; font-weight:600; color:#334155; }
.mp-form-group { margin-bottom:20px; }
.mp-form-group label { display:block; font-size:13px; font-weight:600; color:#64748B; margin-bottom:6px; text-transform:uppercase; letter-spacing:.4px; }
.mp-form-group select, .mp-form-group input { width:100%; padding:10px 14px; border:1px solid #E2E8F0; border-radius:8px; font-size:14px; color:#0F172A; background:#fff; }
.mp-form-group select:focus, .mp-form-group input:focus { border-color:#3B82F6; outline:none; }
.mp-btn { padding:10px 20px; border-radius:8px; font-size:14px; font-weight:600; border:none; cursor:pointer; display:inline-flex; align-items:center; gap:8px; }
.mp-btn-green { background:#10B981; color:#fff; }
.mp-btn-green:hover { background:#059669; }
.mp-btn-blue { background:#3B82F6; color:#fff; }
.mp-btn-blue:hover { background:#2563EB; }
.mp-btn-orange { background:#F59E0B; color:#fff; }
.mp-btn-orange:hover { background:#D97706; }
.mp-help-text { font-size:12px; color:#94A3B8; margin-top:4px; }
.mp-badge { display:inline-block; padding:4px 10px; border-radius:12px; font-size:12px; font-weight:600; }
.mp-badge-green { background:#D1FAE5; color:#065F46; }
.mp-badge-red { background:#FEE2E2; color:#991B1B; }
.mp-table { width:100%; border-collapse:collapse; }
.mp-table th { background:#F8FAFC; color:#64748B; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:.4px; padding:12px 16px; text-align:left; border-bottom:1px solid #E2E8F0; }
.mp-table td { padding:12px 16px; border-bottom:1px solid #E2E8F0; font-size:14px; color:#334155; }
.mp-table tr:hover td { background:#F8FAFC; }
</style>

<div class="mp-page-head">
  <h1 class="mp-page-title"><?= $page_title ?></h1>
</div>

<!-- Settings Card -->
<div class="mp-card">
  <div class="mp-card-header">
    <div class="mp-card-title"><i class="fa fa-bell-o text-orange"></i> Debt Reminder Configuration</div>
    <div>
      <span class="mp-badge mp-badge-green" id="status-badge" style="<?= $settings->enabled == 1 ? '' : 'display:none;'; ?>"><i class="fa fa-check"></i> Active</span>
      <span class="mp-badge mp-badge-red" id="status-badge-paused" style="<?= $settings->enabled == 1 ? 'display:none;' : ''; ?>"><i class="fa fa-pause"></i> Paused</span>
    </div>
  </div>
  <div class="mp-card-body">
    <div class="row">
      <div class="col-md-6">
        <label class="mp-toggle mp-form-group" for="reminder_enabled" style="cursor:pointer;">
          <input type="checkbox" id="reminder_enabled" class="mp-toggle-input no-icheck" <?= $settings->enabled == 1 ? 'checked' : ''; ?>>
          <span class="mp-label">Enable Automatic Debt Reminders</span>
        </label>
        <p class="mp-help-text">When enabled, the system will automatically send reminders to customers with outstanding balances based on the frequency below.</p>
      </div>
      <div class="col-md-6">
        <div class="mp-form-group">
          <label>Reminder Frequency</label>
          <select id="reminder_frequency">
            <option value="daily" <?= $settings->frequency == 'daily' ? 'selected' : ''; ?>>Daily</option>
            <option value="3days" <?= $settings->frequency == '3days' ? 'selected' : ''; ?>>Every 3 Days</option>
            <option value="weekly" <?= $settings->frequency == 'weekly' ? 'selected' : ''; ?>>Weekly</option>
            <option value="biweekly" <?= $settings->frequency == 'biweekly' ? 'selected' : ''; ?>>Bi-Weekly</option>
            <option value="monthly" <?= $settings->frequency == 'monthly' ? 'selected' : ''; ?>>Monthly</option>
          </select>
          <p class="mp-help-text">How often reminders are sent to the same customer.</p>
        </div>
      </div>
    </div>

    <div class="row">
      <div class="col-md-6">
        <div class="mp-form-group">
          <label>Max Reminders Per Customer</label>
          <input type="number" id="max_reminders" value="<?= $settings->max_reminders; ?>" min="0" placeholder="0 = unlimited">
          <p class="mp-help-text">Set to 0 for unlimited reminders. Otherwise, the system will stop after this many reminders.</p>
        </div>
      </div>
      <div class="col-md-6">
        <div class="mp-form-group">
          <label>Channels</label>
          <div style="display:flex; gap:20px; margin-top:8px;">
            <label style="display:flex; align-items:center; gap:6px; font-size:14px; font-weight:500; color:#334155; text-transform:none; cursor:pointer;">
              <input type="checkbox" id="send_email" class="mp-channel-input no-icheck" <?= $settings->send_email == 1 ? 'checked' : ''; ?> style="width:18px; height:18px;"> Email
            </label>
            <label style="display:flex; align-items:center; gap:6px; font-size:14px; font-weight:500; color:#334155; text-transform:none; cursor:pointer;">
              <input type="checkbox" id="send_sms" class="mp-channel-input no-icheck" <?= $settings->send_sms == 1 ? 'checked' : ''; ?> style="width:18px; height:18px;"> SMS
            </label>
          </div>
          <p class="mp-help-text">At least one channel must be selected.</p>
        </div>
      </div>
    </div>

    <div style="margin-top:10px; display:flex; gap:10px; flex-wrap:wrap;">
      <button type="button" class="mp-btn mp-btn-green" id="btn-save-settings"><i class="fa fa-save"></i> Save Settings</button>
      <a href="<?=base_url('debt_reminder/customers');?>" class="mp-btn mp-btn-blue"><i class="fa fa-users"></i> Manage Per-Customer Settings</a>
      <button type="button" class="mp-btn mp-btn-orange" id="btn-trigger-now"><i class="fa fa-paper-plane"></i> Send Reminders Now</button>
    </div>
  </div>
</div>

<!-- Reminder History -->
<div class="mp-card">
  <div class="mp-card-header">
    <div class="mp-card-title"><i class="fa fa-history text-blue"></i> Recent Reminder Activity</div>
    <small class="text-muted"><?= $total_history; ?> total records</small>
  </div>
  <div class="mp-card-body">
    <?php if(!empty($history)): ?>
    <div class="table-responsive">
      <table class="mp-table">
        <thead>
          <tr>
            <th>Date</th>
            <th>Customer</th>
            <th>Amount Due</th>
            <th>Channel</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($history as $h): ?>
          <tr>
            <td><?= show_date($h->sent_at); ?> <?= date('H:i', strtotime($h->sent_at)); ?></td>
            <td><?= htmlspecialchars($h->customer_name); ?></td>
            <td><?= store_number_format($h->amount_due); ?></td>
            <td>
              <?php if($h->channel == 'email'): ?><span class="label label-primary"><i class="fa fa-envelope"></i> Email</span>
              <?php elseif($h->channel == 'sms'): ?><span class="label label-info"><i class="fa fa-comment"></i> SMS</span>
              <?php else: ?><span class="label label-default"><?= $h->channel; ?></span><?php endif; ?>
            </td>
            <td>
              <?php if($h->status == 'sent'): ?><span class="label label-success"><i class="fa fa-check"></i> Sent</span>
              <?php else: ?><span class="label label-danger" title="<?= htmlspecialchars($h->error_message); ?>"><i class="fa fa-times"></i> Failed</span><?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php else: ?>
      <p class="text-muted text-center" style="padding:20px;">No reminders have been sent yet.</p>
    <?php endif; ?>
  </div>
</div>

<script>
$(function(){
  if(typeof toastr !== 'undefined'){
    toastr.options = { positionClass: 'toast-top-right', closeButton: true, progressBar: true, timeOut: 3000 };
  }

  var csrfName = '<?= $this->security->get_csrf_token_name(); ?>';
  var csrfHash = '<?= $this->security->get_csrf_hash(); ?>';

  function updateBadge(enabled){
    if(enabled){
      $('#status-badge').show();
      $('#status-badge-paused').hide();
    } else {
      $('#status-badge').hide();
      $('#status-badge-paused').show();
    }
  }

  // Sync badge when toggle changes
  $('#reminder_enabled').on('change', function(){
    updateBadge(this.checked);
  });

  // Initialize badge from checkbox state on load
  updateBadge($('#reminder_enabled').is(':checked'));

  function showToast(type, msg){
    if(typeof toastr !== 'undefined'){
      toastr[type](msg);
    } else {
      alert((type === 'error' ? 'Error: ' : '') + msg);
    }
  }

  // SAVE BUTTON — validates then saves all settings
  $('#btn-save-settings').click(function(){
    var btn = $(this);
    var enabled = $('#reminder_enabled').is(':checked') ? 1 : 0;
    var frequency = $('#reminder_frequency').val();
    var maxReminders = $('#max_reminders').val();
    var sendEmail = $('#send_email').is(':checked') ? 1 : 0;
    var sendSms = $('#send_sms').is(':checked') ? 1 : 0;

    // Validation: at least one channel must be selected
    if(!sendEmail && !sendSms){
      showToast('error', 'At least one channel (Email or SMS) must be selected.');
      return;
    }

    // Validation: frequency
    if(!frequency){
      showToast('error', 'Please select a reminder frequency.');
      return;
    }

    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');

    var payload = {
      enabled: enabled,
      frequency: frequency,
      max_reminders: maxReminders,
      send_email: sendEmail,
      send_sms: sendSms
    };
    payload[csrfName] = csrfHash;

    $.ajax({
      url: '<?=base_url("debt_reminder/save_settings");?>',
      type: 'POST',
      dataType: 'json',
      data: payload,
      success: function(res){
        btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Settings');
        if(res && res.status === 'success'){
          updateBadge(enabled === 1);
          showToast('success', res.message || 'Settings saved successfully');
        } else {
          showToast('error', (res && res.message) ? res.message : 'Failed to save settings');
        }
      },
      error: function(xhr, status, error){
        btn.prop('disabled', false).html('<i class="fa fa-save"></i> Save Settings');
        if(xhr.status === 0){
          showToast('error', 'Network error. Please check your connection and try again.');
        } else if(xhr.status === 403){
          showToast('error', 'Security token expired. Please refresh the page and try again.');
        } else {
          showToast('error', 'Server error: ' + (error || status || 'Unknown error'));
        }
      }
    });
  });

  $('#btn-trigger-now').click(function(){
    if(typeof swal === 'undefined'){
      if(!confirm('This will immediately send debt reminders to all due customers. Continue?')) return;
      doTriggerNow();
    } else {
      swal({
        title: "Send Reminders Now?",
        text: "This will immediately send debt reminders to all due customers.",
        icon: "warning",
        buttons: true,
        dangerMode: true
      }).then(function(willSend){
        if(willSend) doTriggerNow();
      });
    }
  });

  function doTriggerNow(){
    var btn = $('#btn-trigger-now');
    btn.prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
    var payload = {};
    payload[csrfName] = csrfHash;
    $.ajax({
      url: '<?=base_url("debt_reminder/trigger_now");?>',
      type: 'POST',
      dataType: 'json',
      data: payload,
      success: function(res){
        btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Send Reminders Now');
        if(res && res.status === 'success'){
          showToast('success', res.message || 'Reminders sent');
          setTimeout(function(){ location.reload(); }, 2000);
        } else {
          showToast('error', (res && res.message) ? res.message : 'Failed to send reminders');
        }
      },
      error: function(xhr, status, error){
        btn.prop('disabled', false).html('<i class="fa fa-paper-plane"></i> Send Reminders Now');
        showToast('error', 'Server error: ' + (error || status || 'Unknown error'));
      }
    });
  }
});
</script>
