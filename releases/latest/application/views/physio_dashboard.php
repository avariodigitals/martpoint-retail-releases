<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * Clinic dashboard — physiotherapy & rehabilitation.
 *
 * Figures come from Dashboard::clinicStats(), which computes a figure only when
 * the viewer holds the matching clinical grant, reading the same table the
 * corresponding screen reads. Nothing here is a placeholder.
 */
$stats    = isset($clinic_stats) && is_array($clinic_stats) ? $clinic_stats : array();
$activity = isset($clinic_activity) && is_array($clinic_activity) ? $clinic_activity : array();

$workspaces = array();
if(!empty($clinic_access['queue']))        $workspaces[] = array('Care queue','Check-in, triage, vitals and handover.','care_queue','fa-list-ol');
if(!empty($clinic_access['appointments'])) $workspaces[] = array('Appointments & arrivals','Confirm bookings and manage today\'s arrivals.','appointments','fa-calendar');
if(!empty($clinic_access['patients']))     $workspaces[] = array('Patients','Open the clinical register and patient profiles.','patients','fa-address-book-o');
if(!empty($clinic_access['sessions']))     $workspaces[] = array('Physiotherapy sessions','Schedule, check in and complete treatment sessions.','sessions','fa-stethoscope');
if(!empty($clinic_access['nursing']))      $workspaces[] = array('Nursing station','Open observation and ward task queues.','inpatient/tasks','fa-heartbeat');
if(!empty($clinic_access['ward']))         $workspaces[] = array('Ward & bed board','Admissions, bed availability and discharge work.','inpatient/beds','fa-bed');
// Billing needs patient_billing_view; a funds-only role must land on the
// patient picker instead, otherwise the card is a locked door.
if(physio_can('patient_billing_view')){
	$workspaces[] = array('Patient accounts','Billing, patient funds and opening-position review.','patient_billing','fa-file-text-o');
} elseif(physio_can('patient_funds_view')){
	$workspaces[] = array('Patient accounts','Patient funds, ledger, entitlements and payment evidence.','patient_funds','fa-file-text-o');
}
if(!empty($clinic_access['procurement']))  $workspaces[] = array('Procurement','Therapy aids, supplier balances and purchase requests.','purchase','fa-truck');
?>
<style>
/* ===== CLINIC DASHBOARD ===== */
.clinic-head{display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:22px}
.clinic-head h1{margin:4px 0 5px;font:400 30px/1.15 'DM Serif Display',Georgia,serif;color:var(--mp-text)}
.clinic-head p{margin:0;color:var(--mp-muted);font-size:14px}
.clinic-eyebrow{color:var(--mp-primary);font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase}
.clinic-badge{display:inline-flex;align-items:center;gap:7px;padding:7px 13px;border-radius:999px;background:#e3efe9;color:#104d40;font-size:13px;font-weight:600;white-space:nowrap}

.clinic-kpis{display:grid;grid-template-columns:repeat(auto-fit,minmax(210px,1fr));gap:14px;margin-bottom:26px}
.clinic-kpi{display:block;padding:18px;border:1px solid var(--mp-border);border-radius:12px;background:var(--mp-surface);color:var(--mp-text);text-decoration:none;box-shadow:var(--mp-shadow-sm);transition:box-shadow .15s ease,border-color .15s ease}
.clinic-kpi:hover{border-color:#91b9a6;color:var(--mp-text);text-decoration:none;box-shadow:var(--mp-shadow)}
.clinic-kpi-top{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:12px}
.clinic-kpi-label{font-size:11px;font-weight:700;letter-spacing:.07em;text-transform:uppercase;color:var(--mp-muted)}
.clinic-kpi-icon{width:32px;height:32px;flex:0 0 32px;display:grid;place-items:center;border-radius:9px;background:#e3efe9;color:#104d40;font-size:15px}
.clinic-kpi-value{font-size:30px;font-weight:700;line-height:1.1;letter-spacing:-.01em;overflow-wrap:break-word}
.clinic-kpi-sub{margin-top:5px;font-size:12.5px;color:var(--mp-muted);display:block}
.clinic-kpi.alert .clinic-kpi-value{color:var(--mp-danger)}
.clinic-kpi.alert .clinic-kpi-icon{background:#f8e5e0;color:#a3452f}
.clinic-kpi.money .clinic-kpi-value{color:var(--mp-primary-dark)}

.clinic-columns{display:grid;grid-template-columns:minmax(0,1.55fr) minmax(0,1fr);gap:16px;align-items:start}
.clinic-panel{border:1px solid var(--mp-border);border-radius:12px;background:var(--mp-surface);box-shadow:var(--mp-shadow-sm);overflow:hidden}
.clinic-panel h2{margin:0;padding:15px 18px;border-bottom:1px solid var(--mp-border);font-size:13px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--mp-muted)}
.clinic-panel-body{padding:14px 18px}
.clinic-workspace-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
.clinic-workspace-link{display:flex;align-items:flex-start;gap:11px;padding:13px;border:1px solid var(--mp-border);border-radius:9px;background:var(--mp-surface);color:var(--mp-text);text-decoration:none}
.clinic-workspace-link:hover{border-color:#91b9a6;color:var(--mp-text);text-decoration:none;background:#f7fbf9}
.clinic-workspace-link i{margin-top:2px;color:var(--mp-primary);font-size:16px;flex:0 0 16px;text-align:center}
.clinic-workspace-link strong{display:block;font-size:13.5px;font-weight:600}
.clinic-workspace-link small{display:block;margin-top:2px;color:var(--mp-muted);font-size:12px;line-height:1.45}
.clinic-activity-item{display:flex;align-items:center;gap:11px;padding:10px 0;border-bottom:1px solid var(--mp-border)}
.clinic-activity-item:last-child{border-bottom:0}
.clinic-activity-item i{width:30px;height:30px;flex:0 0 30px;display:grid;place-items:center;border-radius:9px;background:#eef3f0;color:var(--mp-primary-dark);font-size:13px}
.clinic-activity-item .t{font-size:13.5px;font-weight:600;display:block}
.clinic-activity-item .m{font-size:12px;color:var(--mp-muted);margin-top:1px;display:block}
.clinic-empty{padding:18px 0;color:var(--mp-muted);font-size:13px}
.clinic-note{margin-top:18px;padding:13px 15px;border-left:3px solid #a96e24;background:#f8f0e3;color:#76572e;font-size:13px;border-radius:0 8px 8px 0}
@media(max-width:1100px){.clinic-columns{grid-template-columns:minmax(0,1fr)}}
@media(max-width:760px){
  .clinic-workspace-grid{grid-template-columns:minmax(0,1fr)}
  .clinic-head h1{font-size:24px}
  .clinic-kpi-value{font-size:25px}
  .clinic-kpis{grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px}
}
</style>

<div class="clinic-head">
  <div>
    <h1>Clinic overview</h1>
    <p><?= htmlspecialchars(date('l, j F Y')); ?> &middot; today at a glance</p>
  </div>
</div>

<?php if($stats): ?>
<div class="clinic-kpis">
  <?php foreach($stats as $s):
    $cls = 'clinic-kpi' . (!empty($s['alert']) ? ' alert' : '') . (!empty($s['money']) ? ' money' : ''); ?>
    <a class="<?= $cls; ?>" href="<?= base_url($s['url']); ?>">
      <span class="clinic-kpi-top">
        <span class="clinic-kpi-label"><?= htmlspecialchars($s['label']); ?></span>
        <span class="clinic-kpi-icon"><i class="fa <?= htmlspecialchars($s['icon']); ?>" aria-hidden="true"></i></span>
      </span>
      <span class="clinic-kpi-value"><?= htmlspecialchars((string)$s['value']); ?></span>
      <span class="clinic-kpi-sub"><?= htmlspecialchars($s['sub']); ?></span>
    </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="clinic-columns">
  <section class="clinic-panel">
    <h2>Clinical workspaces</h2>
    <div class="clinic-panel-body">
      <?php if($workspaces): ?>
        <div class="clinic-workspace-grid">
          <?php foreach($workspaces as $w): ?>
            <a class="clinic-workspace-link" href="<?= base_url($w[2]); ?>">
              <i class="fa <?= htmlspecialchars($w[3]); ?>" aria-hidden="true"></i>
              <span><strong><?= htmlspecialchars($w[0]); ?></strong><small><?= htmlspecialchars($w[1]); ?></small></span>
            </a>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="clinic-empty">No clinical workspace grants are assigned to this role. Ask a clinic administrator to update its clinical permissions.</div>
      <?php endif; ?>
    </div>
  </section>

  <section class="clinic-panel">
    <h2>Latest registrations</h2>
    <div class="clinic-panel-body">
      <?php if($activity): ?>
        <?php foreach($activity as $a): ?>
          <div class="clinic-activity-item">
            <i class="fa <?= htmlspecialchars($a['icon']); ?>" aria-hidden="true"></i>
            <span><span class="t"><?= htmlspecialchars($a['title']); ?></span><span class="m"><?= htmlspecialchars($a['meta']); ?></span></span>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="clinic-empty">No recent registrations to show.</div>
      <?php endif; ?>
    </div>
  </section>
</div>

<?php
/*
 * The "Business configuration lives under Administration…" note that used to
 * sit here has been removed. It explained a design decision to the end user,
 * who does not need to be told where their own sidebar items are, and it took
 * the place of information they do want. The Administration link is visible in
 * the rail whenever they hold the grant, which is the whole point.
 */
?>
