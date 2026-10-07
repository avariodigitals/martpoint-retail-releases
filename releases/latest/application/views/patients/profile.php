<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); $patient_term = mp_label('customer'); ?>
<style>
.pp-head{display:flex;gap:18px;align-items:flex-start;flex-wrap:wrap;margin-bottom:20px}
.pp-avatar{width:64px;height:64px;border-radius:16px;background:var(--mp-primary,#2563EB);color:#fff;display:flex;align-items:center;justify-content:center;font-size:26px;font-weight:800}
.pp-meta{font-size:13px;color:var(--mp-muted);line-height:1.7}
.pp-badge{font-size:11px;font-weight:700;padding:4px 10px;border-radius:20px;display:inline-block}
.pp-badge.active{background:#D1FAE5;color:#065F46}
.pp-badge.inactive{background:#FEF3C7;color:#B45309}
.pp-badge.deceased{background:#E2E8F0;color:#475569}
.pp-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:16px}
.pp-card{background:var(--mp-surface,#fff);border:1px solid var(--mp-border);border-radius:14px;padding:18px}
.pp-card h4{margin:0 0 12px;font-size:13px;text-transform:uppercase;letter-spacing:.5px;color:var(--mp-muted)}
.pp-kv{display:grid;grid-template-columns:130px 1fr;gap:6px 12px;font-size:13px}
.pp-kv dt{color:var(--mp-muted)}
.pp-kv dd{margin:0}
.pp-money{font-size:20px;font-weight:800}
.pp-ok{color:#059669}.pp-due{color:#DC2626}
.pp-empty{font-size:13px;color:var(--mp-muted);padding:10px 0}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($patient->customer_name); ?></h2>
    <div class="mp-page-sub">
      <a href="<?= base_url('patients'); ?>">&larr; <?= htmlspecialchars($patient_term); ?> Register</a>
      &nbsp;·&nbsp; <span style="font-family:monospace;"><?= htmlspecialchars($patient->patient_code ?: '—'); ?></span>
      <?php if(function_exists('physio_can') && physio_can('portal_manage')): ?>
        &nbsp;·&nbsp; <a href="<?= base_url('patients/portal_access/'.$patient->id); ?>">Portal Access</a>
      <?php endif; ?>
    </div>
  </div>
  <div>
    <?php if($patient->deceased): ?><span class="pp-badge deceased">Deceased<?= $patient->deceased_date ? ' · ' . date('M j, Y', strtotime($patient->deceased_date)) : ''; ?></span>
    <?php else: ?><span class="pp-badge <?= $patient->status ? 'active' : 'inactive'; ?>"><?= $patient->status ? 'Active' : 'Inactive'; ?></span><?php endif; ?>
    <?php if($patient->deceased): ?>
      <div class="pp-meta" style="margin-top:8px;">
        Recorded by <b><?= htmlspecialchars($deceased_by ?: '—'); ?></b><?= $patient->updated_at ? ' · ' . date('M j, Y g:ia', strtotime($patient->updated_at)) : ''; ?>
        <?php if($patient->deceased_notes): ?><br>Reason: <?= htmlspecialchars($patient->deceased_notes); ?><?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="pp-head">
  <div class="pp-avatar"><?= strtoupper(substr($patient->customer_name, 0, 1)); ?></div>
  <div class="pp-meta">
    <?php if($patient->mobile): ?><div><i class="fa fa-phone"></i> <?= htmlspecialchars($patient->mobile); ?></div><?php endif; ?>
    <?php if($patient->email): ?><div><i class="fa fa-envelope"></i> <?= htmlspecialchars($patient->email); ?></div><?php endif; ?>
    <?php if($patient->address || $patient->city): ?><div><i class="fa fa-map-marker"></i> <?= htmlspecialchars(trim(($patient->address ?: '') . ' ' . ($patient->city ?: ''))); ?></div><?php endif; ?>
  </div>
</div>

<div class="pp-grid">
  <div class="pp-card">
    <h4>Demographics</h4>
    <dl class="pp-kv">
      <dt>Gender</dt><dd><?= $patient->gender ? ucfirst($patient->gender) : '—'; ?></dd>
      <dt>Date of Birth</dt><dd><?= $patient->dob ? date('M j, Y', strtotime($patient->dob)) : '—'; ?></dd>
      <dt>Marital Status</dt><dd><?= htmlspecialchars($patient->marital_status ?: '—'); ?></dd>
      <dt>Occupation</dt><dd><?= htmlspecialchars($patient->occupation ?: '—'); ?></dd>
      <dt>Blood Group</dt><dd><?= ($patient->blood_group && $patient->blood_group !== 'unknown') ? htmlspecialchars($patient->blood_group) : '—'; ?></dd>
    </dl>
  </div>

  <div class="pp-card">
    <h4>Next of Kin</h4>
    <dl class="pp-kv">
      <dt>Name</dt><dd><?= htmlspecialchars($patient->nok_name ?: '—'); ?></dd>
      <dt>Phone</dt><dd><?= htmlspecialchars($patient->nok_phone ?: '—'); ?></dd>
      <dt>Relationship</dt><dd><?= htmlspecialchars($patient->nok_relationship ?: '—'); ?></dd>
    </dl>
  </div>

  <div class="pp-card">
    <h4>Account (billing identity)</h4>
    <dl class="pp-kv">
      <dt>Billing Code</dt><dd><span style="font-family:monospace;"><?= htmlspecialchars($patient->customer_code ?: '—'); ?></span></dd>
      <dt>Advance / Prepaid</dt><dd class="pp-money pp-ok"><?= $CI->currency($patient->tot_advance ?? 0); ?></dd>
      <dt>Balance Due</dt><dd class="pp-money <?= ($patient->sales_due ?? 0) > 0 ? 'pp-due' : 'pp-ok'; ?>"><?= $CI->currency($patient->sales_due ?? 0); ?></dd>
      <dt>Portal</dt><dd><?= htmlspecialchars(ucfirst($patient->portal_status ?: 'none')); ?></dd>
    </dl>
    <div class="pp-empty" style="margin-top:8px;">Invoices, payments and prepaid sessions are managed on the linked billing account — clinical credit never lives outside the customer ledger.</div>

    <?php if(isset($reminder_pause)): ?>
    <div style="margin-top:10px;border-top:1px solid var(--mp-border);padding-top:10px;font-size:12px;">
      <strong>Debt reminders</strong>
      <?php if($reminder_pause): ?>
        <div class="pp-badge inactive" style="margin:6px 0">Paused</div>
        <?php if($reminder_pause->reason): ?><div style="color:var(--mp-muted)"><?= htmlspecialchars($reminder_pause->reason); ?></div><?php endif; ?>
        <?php if($reminder_pause->resume_at): ?><div style="color:var(--mp-muted)">Resumes <?= htmlspecialchars($reminder_pause->resume_at); ?></div><?php endif; ?>
        <?php if($can_reminder): ?><button class="mp-qa-btn" onclick="ppResumeReminder(<?= (int)$reminder_pause->id; ?>)">Resume reminders</button><?php endif; ?>
      <?php else: ?>
        <div class="pp-badge active" style="margin:6px 0">Active</div>
        <?php if($can_reminder): ?><button class="mp-qa-btn" onclick="ppPauseReminder(<?= (int)$patient->id; ?>)">Pause reminders</button><?php endif; ?>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>

  <div class="pp-card">
    <h4>Care Episodes</h4>
    <?php if(empty($episodes)): ?>
      <div class="pp-empty">No care episodes yet. Episodes open automatically at first check-in.</div>
    <?php else: ?>
      <table class="table" style="font-size:13px;">
        <thead><tr><th>Episode</th><th>Type</th><th>Started</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach($episodes as $e): ?>
          <tr>
            <td><span style="font-family:monospace;"><?= htmlspecialchars($e->episode_code ?: 'EP-'.$e->id); ?></span></td>
            <td><?= ucfirst($e->episode_type); ?></td>
            <td><?= $e->started_at ? date('M j, Y', strtotime($e->started_at)) : '—'; ?></td>
            <td><span class="pp-badge <?= $e->status === 'open' ? 'active' : 'deceased'; ?>"><?= ucfirst($e->status); ?></span></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <div class="pp-card">
    <h4>Recent Appointments</h4>
    <?php if(empty($appointments)): ?>
      <div class="pp-empty">No appointments on record.</div>
    <?php else: ?>
      <table class="table" style="font-size:13px;">
        <thead><tr><th>When</th><th>Service</th><th>Clinician</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach($appointments as $a): ?>
          <tr>
            <td><?= $a->scheduled_at ? date('M j, Y g:ia', strtotime($a->scheduled_at)) : '—'; ?></td>
            <td><?= htmlspecialchars($a->service_name ?: '—'); ?></td>
            <td><?= htmlspecialchars($a->staff_name ?: '—'); ?></td>
            <td><span class="pp-badge <?= in_array($a->status, ['completed','confirmed','checked_in']) ? 'active' : ($a->status === 'cancelled' || $a->status === 'no_show' ? 'deceased' : 'inactive'); ?>"><?= ucfirst(str_replace('_',' ',$a->status)); ?></span></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

  <div class="pp-card">
    <h4>Registration</h4>
    <dl class="pp-kv">
      <dt>Registered</dt><dd><?= $patient->created_date ? date('M j, Y', strtotime($patient->created_date)) : '—'; ?></dd>
      <dt>Registered By</dt><dd><?= htmlspecialchars($patient->created_by ?: '—'); ?></dd>
      <dt>Billing ID</dt><dd><?= (int)$patient->customer_id; ?></dd>
    </dl>
  </div>

<?php
/**
 * Split the event feed into history carried over from the clinic's previous
 * system (Smart Hospital 4.0) and actions taken inside MartPoint.
 *
 * Imported entries are inert narrative: they record what happened, with the
 * original date and source record, but carry no charges, invoices, wallet
 * movement or entitlements. Showing them separately means staff can tell
 * legacy history apart from their own audit trail.
 */
$imported = array();
$auditOnly = array();
foreach($events as $ev){
    if(strpos((string)$ev->event, 'sh4_') === 0){ $imported[] = $ev; }
    else { $auditOnly[] = $ev; }
}
$typeLabels = array(
    'sh4_opd'           => array('Outpatient visit', 'fa-stethoscope'),
    'sh4_ipd'           => array('Inpatient stay',  'fa-bed'),
    'sh4_timeline_note' => array('Clinical note',    'fa-file-text-o'),
    'sh4_bed_history'   => array('Bed allocation',   'fa-th'),
    'sh4_discharge'     => array('Discharge record', 'fa-sign-out'),
);
if(!empty($imported)){
    usort($imported, function($a, $b){ return strcmp((string)$b->created_at, (string)$a->created_at); });
}
?>
<div class="pp-grid" style="margin-top:16px;">
  <div class="pp-card" <?= empty($imported) ? '' : 'style="grid-column:1/-1;"'; ?>>
    <h4>Audit Log</h4>
    <?php if(empty($auditOnly)): ?>
      <div class="pp-empty">No audited events yet.</div>
    <?php else: ?>
      <table class="table" style="font-size:12px;">
        <thead><tr><th>Event</th><th>Reason</th><th>By</th><th>When</th></tr></thead>
        <tbody>
        <?php foreach($auditOnly as $ev): ?>
          <tr>
            <td><?= htmlspecialchars(mp_code_label($ev->event)); ?></td>
            <td><?= htmlspecialchars(mb_substr($ev->reason ?? '', 0, 80)); ?></td>
            <td><?= htmlspecialchars($ev->created_by_name ?: '—'); ?></td>
            <td><?= $ev->created_at ? date('M j, Y g:ia', strtotime($ev->created_at)) : '—'; ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>

<?php if(!empty($imported)): ?>
  <div class="pp-card" style="grid-column:1/-1;">
    <h4>Clinical history — imported from previous system</h4>
    <div class="pp-empty" style="padding:0 0 12px;">
      <?= count($imported); ?> record(s) carried over. These are historical notes only —
      no charges, invoices or balances were created from them.
    </div>
    <table class="table" style="font-size:12px;">
      <thead><tr><th style="width:150px;">Date</th><th style="width:170px;">Record</th><th>Detail</th><th style="width:130px;">Source</th></tr></thead>
      <tbody>
      <?php foreach($imported as $ev):
        $meta = json_decode((string)$ev->meta_json, true) ?: array();
        $label = $typeLabels[$ev->event][0] ?? ucfirst(str_replace('_', ' ', (string)$ev->event));
        $icon  = $typeLabels[$ev->event][1] ?? 'fa-history';
        $date  = !empty($meta['date']) ? $meta['date'] : substr((string)$ev->created_at, 0, 10);
        $body  = trim((string)($meta['body'] ?? ''));
      ?>
        <tr>
          <td><?= htmlspecialchars(date('M j, Y', strtotime($date))); ?></td>
          <td><i class="fa <?= htmlspecialchars($icon); ?>"></i> <?= htmlspecialchars($label); ?></td>
          <td>
            <?= htmlspecialchars(mb_substr($body, 0, 220)); ?>
            <?php if(!empty($ev->reason) && $ev->reason !== $label): ?>
              <div style="color:var(--mp-muted);font-size:11px;margin-top:2px;"><?= htmlspecialchars(mb_substr($ev->reason, 0, 90)); ?></div>
            <?php endif; ?>
          </td>
          <td style="color:var(--mp-muted);font-size:11px;">
            <?= htmlspecialchars((string)($meta['source_table'] ?? 'imported')); ?>
            <?php if(!empty($meta['source_id'])): ?>#<?= (int)$meta['source_id']; ?><?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endif; ?>
</div>

<script>$(".patients-active-li").addClass("active").closest(".mp-nav-group").addClass("open");
function ppPauseReminder(pid){
  var reason = prompt('Pause reason (required)'); if(!reason) return;
  var resume = prompt('Resume at (optional, Y-m-d H:i)','')||'';
  var fd = new FormData(); fd.append('scope','patient'); fd.append('target', pid); fd.append('reason', reason); fd.append('resume_at', resume);
  fetch('<?= base_url('debt_reminders/pause'); ?>', {method:'POST', body:fd})
    .then(r=>r.json()).then(d=>{ alert(d.message||''); if(d.status==='success') location.reload(); });
}
function ppResumeReminder(id){
  fetch('<?= base_url('debt_reminders/resume/'); ?>'+id, {method:'POST'})
    .then(r=>r.json()).then(d=>{ alert(d.message||''); if(d.status==='success') location.reload(); });
}
</script>
