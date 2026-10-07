<?php $this->load->view('admin/desktop/_styles'); $CI =& get_instance(); ?>
<style>
.dr-box{background:var(--mp-surface,#fff)!important;border:1px solid var(--mp-border)!important;border-radius:14px!important;padding:14px!important;margin-bottom:14px!important}
.dr-box h4{font-size:11px!important;font-weight:800!important;text-transform:uppercase!important;letter-spacing:.5px!important;color:var(--mp-muted)!important;margin:0 0 10px 0!important}
.dr-inp{padding:6px 9px!important;border:1px solid var(--mp-border)!important;border-radius:8px!important;font-size:12px!important;margin-right:6px!important;background:var(--mp-bg,#fff)!important;color:inherit!important}
.dr-table{width:100%;border-collapse:collapse;font-size:12px}
.dr-table th{text-align:left;padding:5px 8px;border-bottom:1px solid var(--mp-border);font-size:10px;text-transform:uppercase;color:var(--mp-muted)}
.dr-table td{padding:6px 8px;border-bottom:1px solid #F1F5F9}
</style>

<div class="mp-page-head">
  <div><h2>Debt Reminders</h2>
  <div class="mp-page-sub">Clinic, patient and invoice-level reminders, routed through the notification outbox.</div></div>
</div>

<?php if($can_edit): ?>
<div class="dr-box">
  <h4>Clinic settings</h4>
  <form onsubmit="return drPost(event,'debt_reminders/save_config')">
    <label class="dr-inp" style="border:none"><input type="checkbox" name="enabled" value="1" <?= $config->enabled ? 'checked' : ''; ?>> Enable email reminders</label>
    <select class="dr-inp" name="frequency">
      <?php foreach(array('daily','3days','weekly','biweekly','monthly') as $f): ?>
        <option value="<?= $f; ?>" <?= $config->frequency === $f ? 'selected' : ''; ?>><?= $f; ?></option>
      <?php endforeach; ?>
    </select>
    <input class="dr-inp" name="grace_days" type="number" min="0" value="<?= (int)$config->grace_days; ?>" style="width:90px" title="Grace days"> days grace
    <input class="dr-inp" name="send_hour" type="number" min="0" max="23" value="<?= (int)$config->send_hour; ?>" style="width:70px" title="Send hour (0-23)"> send hour
    <input class="dr-inp" name="max_reminders" type="number" min="0" value="<?= (int)$config->max_reminders; ?>" style="width:90px" title="Max reminders, 0 = unlimited"> max
    <label class="dr-inp" style="border:none"><input type="checkbox" name="include_opening_debt" value="1" <?= $config->include_opening_debt ? 'checked' : ''; ?>> Include opening debt</label>
    <button class="mp-qa-btn green">Save</button>
  </form>
  <button class="mp-qa-btn blue" style="margin-top:8px" onclick="drSchedule()">Queue now (test)</button>
</div>
<?php endif; ?>

<div class="dr-box">
  <h4>Active pauses</h4>
  <table class="dr-table">
    <tr><th>Scope</th><th>Target</th><th>Reason</th><th>Paused at</th><th>Resume at</th><th>By</th><th></th></tr>
    <?php foreach($pauses as $p): ?>
    <tr>
      <td><?= htmlspecialchars($p->pause_scope); ?></td>
      <td><?= $p->patient_id ? 'patient #'.(int)$p->patient_id : ($p->invoice_id ? 'invoice #'.(int)$p->invoice_id : '— (clinic)'); ?></td>
      <td><?= htmlspecialchars($p->reason); ?></td>
      <td><?= htmlspecialchars($p->paused_at); ?></td>
      <td><?= htmlspecialchars($p->resume_at ?? 'manual'); ?></td>
      <td><?= htmlspecialchars($p->paused_by ?? '—'); ?></td>
      <td><?php if($can_edit): ?><button class="mp-qa-btn" onclick="drResume(<?= (int)$p->id; ?>)">Resume</button><?php endif; ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if(empty($pauses)): ?><tr><td colspan="7" style="color:var(--mp-muted)">No active pauses.</td></tr><?php endif; ?>
  </table>
</div>

<?php if($can_edit): ?>
<div class="dr-box">
  <h4>Pause sending</h4>
  <form onsubmit="return drPause(event)">
    <select class="dr-inp" name="scope">
      <option value="clinic">Clinic (all)</option>
      <option value="patient">Patient</option>
      <option value="invoice">Invoice</option>
    </select>
    <input class="dr-inp" name="target" placeholder="patient_id or invoice_id (leave blank for clinic)" style="width:220px">
    <input class="dr-inp" name="reason" placeholder="Reason" required>
    <input class="dr-inp" name="resume_at" placeholder="Resume at (optional, Y-m-d H:i)" style="width:180px">
    <button class="mp-qa-btn">Pause</button>
  </form>
</div>
<?php endif; ?>

<div class="dr-box">
  <h4>Recent activity</h4>
  <table class="dr-table">
    <tr><th>Action</th><th>Patient</th><th>Invoice</th><th>Reason</th><th>Actor</th><th>At</th></tr>
    <?php foreach($audit as $a): ?>
    <tr>
      <td><?= htmlspecialchars($a->action); ?></td>
      <td><?= $a->patient_id ? '#'.(int)$a->patient_id : '—'; ?></td>
      <td><?= $a->invoice_id ? '#'.(int)$a->invoice_id : '—'; ?></td>
      <td><?= htmlspecialchars($a->reason ?? '—'); ?></td>
      <td><?= htmlspecialchars($a->actor ?? '—'); ?></td>
      <td><?= htmlspecialchars($a->created_at); ?></td>
    </tr>
    <?php endforeach; ?>
    <?php if(empty($audit)): ?><tr><td colspan="6" style="color:var(--mp-muted)">No activity yet.</td></tr><?php endif; ?>
  </table>
</div>

<script>
function drPost(e, url){ e.preventDefault(); var fd = new FormData(e.target);
  fetch('<?= base_url(); ?>'+url, {method:'POST', body:fd}).then(r=>r.json())
    .then(d=>{ alert(d.message||''); if(d.status==='success') location.reload(); }); return false; }
function drPause(e){ e.preventDefault(); var fd = new FormData(e.target);
  if(fd.get('scope')!=='clinic' && !fd.get('target')){ alert('Target required for patient/invoice pause'); return false; }
  fetch('<?= base_url('debt_reminders/pause'); ?>', {method:'POST', body:fd}).then(r=>r.json())
    .then(d=>{ alert(d.message||''); if(d.status==='success') location.reload(); }); return false; }
function drResume(id){ fetch('<?= base_url('debt_reminders/resume/'); ?>'+id, {method:'POST'}).then(r=>r.json())
    .then(d=>{ alert(d.message||''); if(d.status==='success') location.reload(); }); }
function drSchedule(){ fetch('<?= base_url('debt_reminders/schedule_now'); ?>', {method:'POST'}).then(r=>r.json())
    .then(d=>{ alert(d.message||''); }); }
</script>
