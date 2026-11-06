<?php $this->load->helper('form'); ?>
<?php
  $on = $cfg && (int) $cfg->enabled === 1;
  $emailOn = $cfg && (int) $cfg->email_enabled === 1;
  $smsOn = $cfg && (int) $cfg->sms_enabled === 1;
  $days = $cfg ? max(0, (int) $cfg->days_before) : 7;
?>
<style>
  .veh-remind .mp-card { background:#fff; border:1px solid #E5E7EB; border-radius:16px; overflow:hidden; margin-top:24px; }
  .veh-remind .mp-card-head { padding:16px 20px; border-bottom:1px solid #E5E7EB; background:#F9FAFB; }
  .veh-remind .mp-card-head h3 { margin:0; font-size:16px; font-weight:700; color:#111827; }
  .veh-remind .mp-card-body { padding:20px; }
  .veh-remind .form-group { display:flex; flex-direction:column; gap:6px; margin-bottom:16px; max-width:360px; }
  .veh-remind .form-group label { font-size:13px; font-weight:600; color:#374151; }
  .veh-remind .form-control { border-radius:10px; border:1px solid #D1D5DB; padding:10px 12px; font-size:14px; }
  .veh-remind .mp-chk { display:flex; align-items:flex-start; gap:10px; margin-bottom:14px; }
  .veh-remind .mp-chk input { margin-top:3px; }
  .veh-remind .mp-chk .t { font-size:14px; font-weight:600; color:#374151; }
  .veh-remind .mp-chk .s { font-size:12.5px; color:#6B7280; font-weight:400; }
  .veh-remind .mp-btn { display:inline-flex; align-items:center; gap:6px; padding:10px 22px; border-radius:8px; border:1px solid #D1D5DB; background:#fff; color:#374151; font-size:14px; font-weight:600; text-decoration:none; cursor:pointer; }
  .veh-remind .mp-btn.green { background:#10B981; border-color:#10B981; color:#fff; }
  .veh-remind .mp-preview { background:#F9FAFB; border:1px dashed #D1D5DB; border-radius:10px; padding:12px 14px; font-size:13px; color:#374151; }
  .veh-remind .status-chip { display:inline-block; padding:4px 12px; border-radius:999px; font-size:13px; font-weight:700; }
  .veh-remind .status-chip.on { background:#D1FAE5; color:#065F46; }
  .veh-remind .status-chip.off { background:#F3F4F6; color:#6B7280; }
</style>
<div class="veh-remind">
<div class="mp-page-head">
  <div>
    <h2>Service Reminders</h2>
    <div class="mp-page-sub">Automatic messages to customers whose vehicles are due back for service</div>
  </div>
  <a href="<?= base_url('automobile/list'); ?>" class="mp-btn" style="margin-left:auto;">
    <i class="fa fa-arrow-left"></i> Back to Vehicles
  </a>
</div>

<?php include APPPATH . 'views/comman/code_flashdata.php'; ?>

<div class="mp-card">
  <div class="mp-card-head"><h3>Current state</h3></div>
  <div class="mp-card-body">
    <p style="margin:0 0 8px;">
      <span class="status-chip <?= $on ? 'on' : 'off'; ?>"><?= $on ? 'On' : 'Off'; ?></span>
      <?php if ($on): ?>
        <span style="margin-left:10px;">
          Email <?= $emailOn ? 'on' : 'off'; ?> · SMS <?= $smsOn ? 'on' : 'off'; ?> ·
          reminding <b><?= (int) $due_count; ?></b> vehicle<?= $due_count === 1 ? '' : 's'; ?> due in the next <?= $days; ?> day(s)
        </span>
      <?php endif; ?>
    </p>
    <p class="text-muted" style="margin:0; font-size:13px;">
      Reminders only go to customers whose vehicle has a <b>next due date</b> written on the service record — the date is always set by your staff, never guessed.
      Each vehicle is messaged once per due date; correcting the due date on the record re-arms it. Customers on your suppression list are never sent anything.
    </p>
  </div>
</div>

<?php if ($can_edit): ?>
<?= form_open('automobile/reminders_save', ['class'=>'mp-form']); ?>
<div class="mp-card">
  <div class="mp-card-head"><h3>Settings</h3></div>
  <div class="mp-card-body">
    <div class="mp-chk">
      <input type="checkbox" name="enabled" value="1" <?= $on ? 'checked' : ''; ?> id="rem-en">
      <div><label class="t" for="rem-en">Turn reminders on for this store</label>
        <div class="s">Nothing is sent until you switch this on. The check runs once a day.</div></div>
    </div>
    <div class="form-group">
      <label for="rem-days">Remind how many days before the due date?</label>
      <input type="number" name="days_before" id="rem-days" class="form-control" min="0" max="90" value="<?= (int) $days; ?>">
      <span class="text-muted" style="font-size:12px;">0 means the day the service falls due.</span>
    </div>
    <div class="mp-chk">
      <input type="checkbox" name="email_enabled" value="1" <?= $emailOn ? 'checked' : ''; ?> id="rem-mail">
      <div><label class="t" for="rem-mail">Email</label>
        <div class="s">Uses the <b>Vehicle Service Due</b> template — you can rewrite or disable it from Email Templates settings.</div></div>
    </div>
    <div class="mp-chk">
      <input type="checkbox" name="sms_enabled" value="1" <?= $smsOn ? 'checked' : ''; ?> id="rem-sms">
      <div><label class="t" for="rem-sms">SMS</label>
        <div class="s">Sends from the store's connected SMS provider. Sample:<br>
          <span class="mp-preview">Hi Emeka, your Toyota Corolla (ABC-1234) is due for service on 12 Nov 2026 at <?= htmlspecialchars(get_store_name(get_current_store_id())); ?>. Reply here or call to book a slot.</span>
        </div></div>
    </div>
    <div class="mp-form-actions" style="margin-top:20px; display:flex; gap:12px;">
      <button type="submit" class="mp-btn green"><i class="fa fa-save"></i> Save</button>
      <a href="<?= base_url('automobile/list'); ?>" class="mp-btn">Cancel</a>
    </div>
  </div>
</div>
<?= form_close(); ?>
<?php else: ?>
<div class="mp-card">
  <div class="mp-card-body">
    <p class="text-muted" style="margin:0; font-size:13px;">You can see the reminder schedule, but only the role that manages customer reminders can change it — ask a store admin.</p>
  </div>
</div>
<?php endif; ?>

<div class="mp-card">
  <div class="mp-card-head"><h3>Every message is logged</h3></div>
  <div class="mp-card-body">
    <p style="margin:0; font-size:13.5px; color:#374151;">
      Emails appear in <a href="<?= base_url('email_logs'); ?>">Email Logs</a> like any other store email, SMS in the messaging logs.
      A vehicle that fails to send is retried on the next daily run; a vehicle that was messaged is not touched again until its due date changes.
    </p>
  </div>
</div>
</div>
