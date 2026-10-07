<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/** Appointments & attendance — bookings, attendance rate, clinician load. */
$this->load->view('reports/clinical/_chrome', array('from' => $from, 'to' => $to));
$r = $report;
$att = $r['attendance'];
?>
<?php cr_toolbar($meta, $key, $from, $to); ?>

<?php cr_period_filter(base_url('clinical_reports/appointments'), $from, $to); ?>

<div class="cr-kpis">
  <?php
  cr_kpi('Appointments', number_format($r['total']), 'scheduled in the period');
  cr_kpi('Attended', number_format($att['arrived']), 'patient arrived or checked in');
  cr_kpi('Attendance rate', ($att['rate'] === null ? '—' : $att['rate'] . '%'),
      $att['rate'] === null ? 'no completed bookings yet' : 'of completed bookings',
      array('alert' => ($att['rate'] !== null && $att['rate'] < 70)));
  cr_kpi('No-shows', number_format($att['no_show']), 'recorded as no-show', array('alert' => $att['no_show'] > 0));
  ?>
</div>

<div class="cr-grid2">
  <div class="cr-panel">
    <h3>Bookings by status</h3>
    <table class="cr-table">
      <tbody>
      <?php if(empty($r['by_status'])): ?>
        <tr><td class="cr-empty">No bookings in this period.</td></tr>
      <?php else: $sMax = max($r['by_status']); foreach($r['by_status'] as $label => $n): ?>
        <tr>
          <td style="width:36%"><span class="cr-badge"><?= htmlspecialchars(str_replace('_',' ',$label)); ?></span></td>
          <td class="num" style="width:14%"><?= number_format($n); ?></td>
          <td><?= cr_bar($sMax > 0 ? 100 * $n / $sMax : null); ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

  <div class="cr-panel">
    <h3>Clinician workload</h3>
    <table class="cr-table">
      <thead><tr><th>Clinician</th><th class="num">Appointments</th><th style="width:32%">Share</th></tr></thead>
      <tbody>
      <?php if(empty($r['by_staff'])): ?>
        <tr><td colspan="3" class="cr-empty">No appointments assigned.</td></tr>
      <?php else: $wMax = max(array_column($r['by_staff'], 'n')); foreach($r['by_staff'] as $s): ?>
        <tr>
          <td><?= htmlspecialchars($s['name']); ?></td>
          <td class="num"><?= number_format($s['n']); ?></td>
          <td><?= cr_bar($wMax > 0 ? 100 * $s['n'] / $wMax : null); ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<p class="cr-note">
  <strong>How attendance is measured.</strong> A booking counts towards the attendance rate
  once it is completed, arrived, checked in, cancelled or marked no-show. The rate is the share
  of those bookings where the patient actually arrived, so a rising no-show count is visible
  before it erodes the treatment schedule.
</p>
