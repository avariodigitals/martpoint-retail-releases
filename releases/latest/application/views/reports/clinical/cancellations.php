<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/** Cancellations & no-shows — where the clinic loses bookable capacity. */
$this->load->view('reports/clinical/_chrome', array('from' => $from, 'to' => $to));
$a = $report['appointments'];
$s = $report['sessions'];
?>
<?php cr_toolbar($meta, $key, $from, $to); ?>

<?php cr_period_filter(base_url('clinical_reports/cancellations'), $from, $to); ?>

<div class="cr-kpis">
  <?php
  cr_kpi('Appointments', number_format($a['total']), 'booked in this period');
  cr_kpi('Still to come', number_format($a['upcoming']), 'booked ahead of today');
  cr_kpi('Cancelled', number_format($a['cancelled']), 'called off before the visit',
      array('alert' => $a['cancelled'] > 0));
  cr_kpi('No-shows', number_format($a['no_show']), 'never arrived, never told us',
      array('alert' => $a['no_show'] > 0));
  cr_kpi('Loss rate', $a['rate'] === null ? '—' : cr_number($a['rate'], 1) . '%',
      'cancelled plus no-show, of all bookings',
      array('alert' => $a['rate'] !== null && $a['rate'] >= 10));
  ?>
</div>

<p class="cr-note">
  <strong>Why this matters.</strong> A cancelled appointment or a patient who never arrived is
  a therapist's hour that can never be sold again &mdash; it is lost before it ever reaches
  billing, which is why the revenue report will not show it. Watch the loss rate rather than the
  raw count: it is the only number here that is comparable month to month.
</p>

<div class="cr-grid2">
  <div class="cr-panel">
    <h3>Appointments by outcome</h3>
    <table class="cr-table">
      <thead><tr><th>Outcome</th><th class="num">Appointments</th><th class="num">Share</th></tr></thead>
      <tbody>
        <?php
        $total = max(1, $a['total']);
        $outcomes = array(
          'Attended / completed' => $a['completed'],
          'Cancelled'            => $a['cancelled'],
          'No-show'              => $a['no_show'],
        );
        foreach($outcomes as $label => $n): ?>
        <tr>
          <td><?= htmlspecialchars($label); ?></td>
          <td class="num"><?= number_format($n); ?></td>
          <td class="num"><?= cr_bar(round(100 * $n / $total, 1)); ?></td>
        </tr>
        <?php endforeach; ?>
        <tr>
          <td><strong>Total bookings</strong></td>
          <td class="num"><strong><?= number_format($a['total']); ?></strong></td>
          <td class="num"></td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="cr-panel">
    <h3>Why appointments were cancelled</h3>
    <table class="cr-table">
      <thead><tr><th>Reason recorded</th><th class="num">Appointments</th></tr></thead>
      <tbody>
      <?php if(empty($a['by_reason'])): ?>
        <tr><td colspan="2" class="cr-empty">No cancellation reasons were recorded in this period.</td></tr>
      <?php else: foreach($a['by_reason'] as $reason => $n): ?>
        <tr>
          <td><?= htmlspecialchars($reason); ?></td>
          <td class="num"><?= number_format($n); ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="cr-grid2">
  <div class="cr-panel">
    <h3>Patients losing the most appointments</h3>
    <table class="cr-table">
      <thead><tr><th>Patient</th><th class="num">Lost</th></tr></thead>
      <tbody>
      <?php if(empty($a['by_patient'])): ?>
        <tr><td colspan="2" class="cr-empty">No patient has lost an appointment in this period.</td></tr>
      <?php else: foreach($a['by_patient'] as $x): ?>
        <tr>
          <td><?= htmlspecialchars($x['name']); ?></td>
          <td class="num"><?= number_format($x['n']); ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

  <div class="cr-panel">
    <h3>Treatment sessions</h3>
    <table class="cr-table">
      <thead><tr><th>Measure</th><th class="num">Sessions</th></tr></thead>
      <tbody>
        <tr><td>Scheduled in period</td><td class="num"><?= number_format($s['total']); ?></td></tr>
        <tr><td>Cancelled</td><td class="num"><?= number_format($s['cancelled']); ?></td></tr>
        <tr><td>Reversed after delivery</td><td class="num"><?= number_format($s['reversed']); ?></td></tr>
        <tr>
          <td><strong>Cancellation rate</strong></td>
          <td class="num"><strong><?= $s['rate'] === null ? '—' : cr_number($s['rate'], 1) . '%'; ?></strong></td>
        </tr>
      </tbody>
    </table>
    <?php if(!empty($s['by_clinician'])): ?>
    <h3 style="margin-top:18px;">Cancellations by clinician</h3>
    <table class="cr-table">
      <thead><tr><th>Clinician</th><th class="num">Cancelled</th></tr></thead>
      <tbody>
      <?php foreach($s['by_clinician'] as $x): ?>
        <tr>
          <td><?= htmlspecialchars($x['name']); ?></td>
          <td class="num"><?= number_format($x['n']); ?></td>
        </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php endif; ?>
  </div>
</div>

<?php if(!empty($report['trend'])): ?>
<div class="cr-panel">
  <h3>Bookings by month</h3>
  <table class="cr-table">
    <thead><tr><th>Month</th><th class="num">Bookings</th></tr></thead>
    <tbody>
    <?php foreach($report['trend'] as $ym => $n): ?>
      <tr>
        <td><?= htmlspecialchars(date('F Y', strtotime($ym . '-01'))); ?></td>
        <td class="num"><?= number_format($n); ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
