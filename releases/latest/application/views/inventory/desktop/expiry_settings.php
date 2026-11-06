<?php $this->load->view('inventory/desktop/_styles'); ?>

<div class="mp-page-head">
  <div>
    <h2>Expiry Settings</h2>
    <div class="mp-page-sub">Configure expiry alerts and blocking</div>
  </div>
</div>

<?php
  // The daily expiry list is the batch report sent to the shop instead of
  // waiting for somebody to open it. It only exists where the batch ledger and
  // the alert columns are installed, so an un-upgraded server must not offer a
  // switch that can never fire.
  $batchAlert = $alerting ?? ['available' => false, 'stats' => [], 'today' => date('Y-m-d')];
  $stats = $batchAlert['stats'] ?? [];
  $qty = function ($value) { return function_exists('format_qty') ? format_qty((float)$value) : number_format((float)$value, 2); };
  $day = function ($date) { $stamp = strtotime((string)$date); return $stamp === false ? (string)$date : date('d M Y', $stamp); };
?>
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
        <div class="mp-form-group" style="margin-top:8px;">
          <label>Stock rotation for dated batches</label>
          <?php
            $rotationMethod = strtoupper((string)($settings->rotation_method ?? 'MANUAL'));
            $rotationLabels = ['MANUAL'=>'Manual — the clerk picks the batch','FEFO'=>'Earliest expiry first','FIFO'=>'Oldest received first'];
          ?>
          <p class="mp-form-hint" style="margin-top:6px;">
            Current rule: <strong><?= htmlspecialchars($rotationLabels[$rotationMethod] ?? $rotationMethod); ?></strong>
            — change it under <a href="<?= base_url('business_profile/capabilities'); ?>">Business Profile → Capabilities → Stock rotation</a>.
          </p>
          <p class="mp-form-hint">Automatic rotation requires purchase batches with correct received quantities, warehouse and expiry dates. Insufficient batch stock blocks the sale instead of guessing from total item stock.</p>
        </div>
        <label style="display:flex;align-items:center;gap:10px;font-weight:500;cursor:pointer;margin-top:4px;">
          <input type="checkbox" id="email_alerts_enabled" name="email_alerts_enabled" value="1" <?= $settings->email_alerts_enabled ? 'checked' : ''; ?> style="width:18px;height:18px;accent-color:var(--mp-primary);">
          Enable Email Alerts
        </label>
        <?php if (!empty($batchAlert['available'])): ?>
        <div class="mp-form-group" style="margin-top:16px;">
          <label for="alert_phone">WhatsApp number for the daily list</label>
          <input type="tel" class="mp-form-control" id="alert_phone" name="alert_phone" value="<?= htmlspecialchars((string)($settings->alert_phone ?? ''), ENT_QUOTES, 'UTF-8'); ?>" placeholder="+234 801 234 5678" maxlength="30">
          <p class="mp-form-hint">The same list, once a day, on WhatsApp — a number this shop can receive messages on.</p>
        </div>
        <label style="display:flex;align-items:center;gap:10px;font-weight:500;cursor:pointer;">
          <input type="checkbox" id="whatsapp_alerts_enabled" name="whatsapp_alerts_enabled" value="1" <?= !empty($settings->whatsapp_alerts_enabled) ? 'checked' : ''; ?> style="width:18px;height:18px;accent-color:var(--mp-primary);">
          Send the daily expiry list on WhatsApp
        </label>
        <p class="mp-form-hint" style="margin-top:10px;">
          A switched-on channel gets <strong>one list a day</strong>: every batch already past its date, and every batch that runs out inside
          <?= (int)($stats['horizon'] ?? $settings->alert_before_days); ?> days — the same batches as the batch ledger reports, so the message and the report can never disagree.
          <?php if (trim((string)($settings->last_alert_slot ?? '')) !== ''): ?>
            The last list went out on <?= htmlspecialchars($day($settings->last_alert_slot), ENT_QUOTES, 'UTF-8'); ?>.
          <?php else: ?>
            Nothing has gone out yet — the next run sends today's list if there is one.
          <?php endif; ?>
        </p>
        <?php else: ?>
        <p class="mp-form-hint" style="margin-top:10px;">Email alerts cover this shop's own expiry warnings. Daily batch lists are available with the batch ledger reports.</p>
        <?php endif; ?>
      </div>
      <div class="mp-form-actions" style="margin-top:24px;">
        <button type="button" id="save-btn" class="mp-btn-primary">Save Settings</button>
        <button type="button" id="send-alert-btn" class="mp-btn-secondary"><i class="fa fa-paper-plane"></i> <?= !empty($batchAlert['available']) ? 'Send today&rsquo;s list now' : 'Send Email Alert Now'; ?></button>
      </div>
      <?= form_close(); ?>
    </div>
  </div>

  <div class="mp-card">
    <div class="mp-card-head"><h3>Current Status</h3></div>
    <div class="mp-card-body">
      <?php if (!empty($batchAlert['available'])): ?>
      <div style="border:1px solid var(--mp-border);border-radius:12px;padding:16px;background:rgba(220,38,38,.05);margin-bottom:16px;">
        <div style="display:flex;align-items:center;gap:12px;color:var(--mp-danger);font-weight:700;font-size:16px;"><i class="fa fa-ban"></i> Already past their date: <?= $qty($stats['expired_units'] ?? 0); ?> unit(s)</div>
        <p style="margin:6px 0 0;color:var(--mp-muted);font-size:13px;">On the batch ledger, so this is what the till will not hand over.</p>
      </div>
      <div style="border:1px solid var(--mp-border);border-radius:12px;padding:16px;background:rgba(245,158,11,.05);">
        <div style="display:flex;align-items:center;gap:12px;color:var(--mp-warning);font-weight:700;font-size:16px;"><i class="fa fa-warning"></i> Out of date by <?= htmlspecialchars($day($stats['until'] ?? $batchAlert['today']), ENT_QUOTES, 'UTF-8'); ?>: <?= $qty($stats['soon_units'] ?? 0); ?> unit(s)</div>
        <p style="margin:6px 0 0;color:var(--mp-muted);font-size:13px;">Across <?= (int)($stats['batches'] ?? 0); ?> batch(es)<?= (int)($stats['omitted'] ?? 0) > 0 ? ', ' . (int)$stats['omitted'] . ' of them listed on the report rather than repeated here' : '' ?>.</p>
        <?php if ((int)($stats['no_expiry_rows'] ?? 0) > 0): ?>
        <p style="margin:6px 0 0;color:var(--mp-muted);font-size:13px;"><?= (int)$stats['no_expiry_rows']; ?> batch(es) carry no expiry date at all, so no alert can warn about them.</p>
        <?php endif; ?>
      </div>
      <?php else: ?>
      <div style="border:1px solid var(--mp-border);border-radius:12px;padding:16px;background:rgba(220,38,38,.05);margin-bottom:16px;">
        <div style="display:flex;align-items:center;gap:12px;color:var(--mp-danger);font-weight:700;font-size:16px;"><i class="fa fa-ban"></i> Expired Items: <?= $this->expiry->count_expired(); ?></div>
        <p style="margin:6px 0 0;color:var(--mp-muted);font-size:13px;">Items that have passed their expiry date and should not be sold.</p>
      </div>
      <div style="border:1px solid var(--mp-border);border-radius:12px;padding:16px;background:rgba(245,158,11,.05);">
        <div style="display:flex;align-items:center;gap:12px;color:var(--mp-warning);font-weight:700;font-size:16px;"><i class="fa fa-warning"></i> Expiring Soon: <?= $this->expiry->count_expiring(); ?></div>
        <p style="margin:6px 0 0;color:var(--mp-muted);font-size:13px;">Items expiring within <?= $settings->alert_before_days; ?> days.</p>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
$(function(){
  $('#save-btn').on('click', function(){
    var data = $('#expiry-settings-form').serialize();
    $.post('<?= base_url("expiry_settings/save"); ?>', data, function(res){
      var text = $.trim(res || '');
      if(text == 'success'){
        toastr['success']('Settings saved successfully!');
      } else if(text.indexOf('failed:') === 0){
        toastr['error'](text.substring(7));
      } else {
        toastr['error']('Could not save. Check the alert email and WhatsApp number, then try again.');
      }
    });
  });

  $('#send-alert-btn').on('click', function(){
    var btn = $(this), label = btn.html();
    btn.attr('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Sending...');
    $.post('<?= base_url("expiry_settings/send_email_alert"); ?>', function(res){
      var text = $.trim(res || '');
      if(text == 'success'){
        toastr['success']('The expiry list went out.');
      } else if(text.indexOf('Failed') === 0 || text.indexOf('failed') === 0){
        toastr['error'](text);
      } else {
        toastr['warning'](text || 'Nothing was sent.');
      }
      btn.attr('disabled', false).html(label);
    }).fail(function(){
      toastr['error']('Could not reach the server. Try again.');
      btn.attr('disabled', false).html(label);
    });
  });
});
</script>
<script>$(".expiry-settings-active-li").addClass("active").closest(".mp-nav-group").addClass("open");</script>
