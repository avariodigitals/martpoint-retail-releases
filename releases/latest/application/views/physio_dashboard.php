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
a.clinic-activity-item:hover .t{color:var(--mp-primary-dark);text-decoration:underline}
a.clinic-activity-item:hover i{background:#dcebe3}
.clinic-activity-item i{width:30px;height:30px;flex:0 0 30px;display:grid;place-items:center;border-radius:9px;background:#eef3f0;color:var(--mp-primary-dark);font-size:13px}
.clinic-activity-item .t{font-size:13.5px;font-weight:600;display:block}
.clinic-activity-item .m{font-size:12px;color:var(--mp-muted);margin-top:1px;display:block}
.clinic-empty{padding:18px 0;color:var(--mp-muted);font-size:13px}
.clinic-note{margin-top:18px;padding:13px 15px;border-left:3px solid #a96e24;background:#f8f0e3;color:#76572e;font-size:13px;border-radius:0 8px 8px 0}
.clinic-insights-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;margin:0 0 18px}
.clinic-chart{border:1px solid var(--mp-border);border-radius:12px;background:var(--mp-surface);box-shadow:var(--mp-shadow-sm);padding:18px}
.clinic-chart h2{margin:0 0 14px;font-size:13px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--mp-muted)}
.clinic-bar{margin:12px 0}.clinic-bar-head{display:flex;justify-content:space-between;gap:12px;font-size:13px;font-weight:600}.clinic-bar-head span:last-child{color:var(--mp-muted);font-weight:700}.clinic-bar-track{height:9px;margin-top:6px;border-radius:99px;background:#edf2ef;overflow:hidden}.clinic-bar-fill{height:100%;border-radius:99px;background:var(--mp-primary)}.clinic-bar-fill.blue{background:#477ca3}.clinic-chart-sub{margin:0;color:var(--mp-muted);font-size:12px}
.clinic-birthday-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(320px,1fr));gap:16px;margin:0 0 18px}.clinic-birthday-cards .clinic-birthdays{margin:0}.clinic-birthdays{border:1px solid var(--mp-border);border-radius:12px;background:var(--mp-surface);box-shadow:var(--mp-shadow-sm);overflow:hidden}.clinic-birthdays h2{margin:0;padding:15px 18px;border-bottom:1px solid var(--mp-border);font-size:13px;font-weight:700;letter-spacing:.06em;text-transform:uppercase;color:var(--mp-muted)}.clinic-birthday-list{display:grid;grid-template-columns:repeat(auto-fill,minmax(170px,1fr));gap:10px;padding:14px 18px}.clinic-birthday{display:flex;align-items:center;gap:10px;padding:10px;border:1px solid var(--mp-border);border-radius:9px}.clinic-birthday-avatar{width:30px;height:30px;display:grid;place-items:center;border-radius:50%;background:#e3efe9;color:var(--mp-primary-dark);font-weight:700;font-size:12px}.clinic-birthday strong{display:block;font-size:13px}.clinic-birthday small{display:block;margin-top:2px;color:var(--mp-muted);font-size:11px}.clinic-birthday-empty{padding:18px;color:var(--mp-muted);font-size:13px}.clinic-range{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin:0 0 20px;padding:12px 14px;border:1px solid var(--mp-border);border-radius:12px;background:var(--mp-surface)}.clinic-range-label{font-size:12px;color:var(--mp-muted)}.clinic-range-links{display:flex;gap:6px;flex-wrap:wrap}.clinic-range-links a{padding:7px 15px;border:1px solid transparent;border-radius:8px;color:var(--mp-muted);font-size:12px;font-weight:700;text-decoration:none}.clinic-range-links a:hover{background:#e3efe9;color:var(--mp-primary-dark)}.clinic-range-links a.active{background:#dff0e8;color:#104d40;border-color:#8dbba6}
@media(max-width:1100px){.clinic-columns{grid-template-columns:minmax(0,1fr)}}
@media(max-width:760px){
  .clinic-insights-grid{grid-template-columns:minmax(0,1fr)}.clinic-birthday-cards{grid-template-columns:minmax(0,1fr)}
  .clinic-workspace-grid{grid-template-columns:minmax(0,1fr)}
  .clinic-head h1{font-size:24px}
  .clinic-kpi-value{font-size:25px}
  .clinic-kpis{grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:10px}
}
</style>

<div class="clinic-head">
  <div>
    <h1>Clinic overview</h1>
    <p><?= htmlspecialchars(date('l, j F Y')); ?> &middot; <?= htmlspecialchars($clinic_range_info['label'] ?? 'Today'); ?> at a glance</p>
  </div>
  <div class="clinic-range" aria-label="Dashboard date range">
    <span class="clinic-range-label">Dashboard period: <strong><?= htmlspecialchars($clinic_range_info['label'] ?? 'Today'); ?></strong></span>
    <nav class="clinic-range-links">
      <?php foreach(array('Today'=>'Today','Yesterday'=>'Yesterday','7Days'=>'7 Days','30Days'=>'30 Days','ThisMonth'=>'This Month','LastMonth'=>'Last Month','ThisYear'=>'This Year') as $value=>$label): ?><a class="<?= ($clinic_range ?? 'Today') === $value ? 'active' : ''; ?>" href="<?= base_url('dashboard?range='.$value); ?>"><?= htmlspecialchars($label); ?></a><?php endforeach; ?>
    </nav>
  </div>
</div>

<div class="clinic-kpis">
<?php if($stats): ?>
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
<?php endif; ?>

<?php if(!empty($commercial)): ?>
  <?php $periodLabel = htmlspecialchars($clinic_range_info['label'] ?? 'Today'); ?>
  <a class="clinic-kpi money" href="<?= base_url('patient_billing'); ?>"><span class="clinic-kpi-top"><span class="clinic-kpi-label">Patient billing · <?= $periodLabel; ?></span><span class="clinic-kpi-icon"><i class="fa fa-file-text-o"></i></span></span><span class="clinic-kpi-value"><?= htmlspecialchars(mp_format_money($commercial['billed_today'])); ?></span><span class="clinic-kpi-sub"><?= (int)$commercial['invoices_today']; ?> final patient bills</span></a>
  <a class="clinic-kpi money" href="<?= base_url('patient_billing'); ?>"><span class="clinic-kpi-top"><span class="clinic-kpi-label">Payments received · <?= $periodLabel; ?></span><span class="clinic-kpi-icon"><i class="fa fa-money"></i></span></span><span class="clinic-kpi-value"><?= htmlspecialchars(mp_format_money($commercial['collected_today'])); ?></span><span class="clinic-kpi-sub">Recorded against final patient bills</span></a>
  <a class="clinic-kpi <?= $commercial['overdue_invoices'] ? 'alert' : ''; ?>" href="<?= base_url('patient_billing'); ?>"><span class="clinic-kpi-top"><span class="clinic-kpi-label">Overdue patient bills</span><span class="clinic-kpi-icon"><i class="fa fa-clock-o"></i></span></span><span class="clinic-kpi-value"><?= (int)$commercial['overdue_invoices']; ?></span><span class="clinic-kpi-sub">Past due date with an unpaid balance</span></a>
<?php endif; ?>
</div>

<?php
$analytics = isset($clinic_analytics) && is_array($clinic_analytics) ? $clinic_analytics : array();
$patient_max = max(1, (int)($analytics['inpatient'] ?? 0), (int)($analytics['outpatient'] ?? 0));
$finance_max = max(1, (float)($analytics['sales'] ?? 0), (float)($analytics['expenses'] ?? 0));
?>
<div class="clinic-insights-grid">
  <section class="clinic-chart" aria-label="Inpatient and outpatient activity">
    <h2>Patient activity</h2>
    <p class="clinic-chart-sub">Episodes opened in <?= htmlspecialchars($clinic_range_info['label'] ?? 'this period'); ?></p>
    <div class="clinic-bar"><div class="clinic-bar-head"><span>Outpatient</span><span><?= (int)($analytics['outpatient'] ?? 0); ?></span></div><div class="clinic-bar-track"><div class="clinic-bar-fill" style="width:<?= round(((int)($analytics['outpatient'] ?? 0) / $patient_max) * 100); ?>%"></div></div></div>
    <div class="clinic-bar"><div class="clinic-bar-head"><span>Inpatient</span><span><?= (int)($analytics['inpatient'] ?? 0); ?></span></div><div class="clinic-bar-track"><div class="clinic-bar-fill blue" style="width:<?= round(((int)($analytics['inpatient'] ?? 0) / $patient_max) * 100); ?>%"></div></div></div>
  </section>
  <section class="clinic-chart" aria-label="Sales and expenses">
    <h2>Sales vs expenses</h2>
    <p class="clinic-chart-sub">Financial movement in <?= htmlspecialchars($clinic_range_info['label'] ?? 'this period'); ?></p>
    <div class="clinic-bar"><div class="clinic-bar-head"><span>Sales</span><span><?= htmlspecialchars(mp_format_money($analytics['sales'] ?? 0)); ?></span></div><div class="clinic-bar-track"><div class="clinic-bar-fill" style="width:<?= round(((float)($analytics['sales'] ?? 0) / $finance_max) * 100); ?>%"></div></div></div>
    <div class="clinic-bar"><div class="clinic-bar-head"><span>Expenses</span><span><?= htmlspecialchars(mp_format_money($analytics['expenses'] ?? 0)); ?></span></div><div class="clinic-bar-track"><div class="clinic-bar-fill blue" style="width:<?= round(((float)($analytics['expenses'] ?? 0) / $finance_max) * 100); ?>%"></div></div></div>
  </section>
</div>

<?php
$birthday_cards = array(array(
	'label' => 'Staff birthdays',
	'title' => 'Staff birthdays · this week',
	'empty' => 'No staff birthdays this week.',
	'items' => isset($staff_birthdays) && is_array($staff_birthdays) ? $staff_birthdays : array(),
));
// Null means the viewer may not see the patient register — the card is omitted.
if(isset($patient_birthdays) && $patient_birthdays !== null){
	$birthday_cards[] = array(
		'label' => 'Patient birthdays',
		'title' => 'Patient birthdays · this week',
		'empty' => 'No patient birthdays this week.',
		'items' => $patient_birthdays,
	);
}
?>
<div class="clinic-birthday-cards">
  <?php foreach($birthday_cards as $card): ?>
  <section class="clinic-birthdays" aria-label="<?= htmlspecialchars($card['label']); ?>">
    <h2><?= htmlspecialchars($card['title']); ?></h2>
    <?php if($card['items']): ?><div class="clinic-birthday-list">
      <?php foreach($card['items'] as $birthday): ?><div class="clinic-birthday"><span class="clinic-birthday-avatar"><?= htmlspecialchars(strtoupper(substr($birthday['name'], 0, 1))); ?></span><span><strong><?= htmlspecialchars($birthday['name']); ?></strong><small><?= htmlspecialchars($birthday['date']); ?><?= $birthday['days'] === 0 ? ' · Today' : ' · in '.(int)$birthday['days'].' day'.((int)$birthday['days'] === 1 ? '' : 's'); ?></small></span></div><?php endforeach; ?>
    </div><?php else: ?><div class="clinic-birthday-empty"><?= htmlspecialchars($card['empty']); ?></div><?php endif; ?>
  </section>
  <?php endforeach; ?>
</div>

<?php $this->load->view('comman/workspace_intelligence', array('intel'=>$intel ?? array(), 'show_empty'=>true)); ?>
<?php $this->load->view('comman/license_usage_card'); ?>

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
          <?php if(!empty($a['url'])): ?>
            <a class="clinic-activity-item" href="<?= base_url($a['url']); ?>" style="text-decoration:none;color:inherit;">
              <i class="fa <?= htmlspecialchars($a['icon']); ?>" aria-hidden="true"></i>
              <span><span class="t"><?= htmlspecialchars($a['title']); ?></span><span class="m"><?= htmlspecialchars($a['meta']); ?></span></span>
            </a>
          <?php else: ?>
            <div class="clinic-activity-item">
              <i class="fa <?= htmlspecialchars($a['icon']); ?>" aria-hidden="true"></i>
              <span><span class="t"><?= htmlspecialchars($a['title']); ?></span><span class="m"><?= htmlspecialchars($a['meta']); ?></span></span>
            </div>
          <?php endif; ?>
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
