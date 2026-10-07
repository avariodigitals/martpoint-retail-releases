<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/** Investigations — requests, pending worklist, turnaround. */
$this->load->view('reports/clinical/_chrome', array('from' => $from, 'to' => $to));
$r = $report;
$ta = $r['avg_turnaround'];
?>
<?php cr_toolbar($meta, $key, $from, $to); ?>

<?php cr_period_filter(base_url('clinical_reports/investigations'), $from, $to); ?>

<div class="cr-kpis">
  <?php
  cr_kpi('Requests', number_format($r['total']), 'raised in the period');
  cr_kpi('Still open', number_format($r['pending']), 'awaiting result or review',
      array('alert' => $r['pending'] > 0));
  cr_kpi('Open over 7 days', number_format($r['overdue']), 'chase the lab or the clinician',
      array('alert' => $r['overdue'] > 0));
  cr_kpi('Average turnaround', ($ta === null ? '—' : $ta['days'] . ' days'),
      $ta === null ? 'no results received yet' : 'from request to result (' . $ta['n'] . ' measured)');
  ?>
</div>

<?php if($r['overdue'] > 0): ?>
<p class="cr-note" style="border-color:#e9d9bd;background:#fdf8f0">
  <strong><?= number_format($r['overdue']); ?> investigation(s) have been open for more than a week.</strong>
  An investigation with no result holds up the assessment and the treatment plan behind it —
  check whether the result has come back on paper without being entered.
</p>
<?php endif; ?>

<div class="cr-grid2">
  <div class="cr-panel">
    <h3>Requests by status</h3>
    <table class="cr-table">
      <tbody>
      <?php if(empty($r['by_status'])): ?>
        <tr><td class="cr-empty">No investigations requested in this period.</td></tr>
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
    <h3>Requests by category</h3>
    <table class="cr-table">
      <tbody>
      <?php if(empty($r['by_category'])): ?>
        <tr><td class="cr-empty">Nothing requested.</td></tr>
      <?php else: $cMax = max($r['by_category']); foreach($r['by_category'] as $label => $n): ?>
        <tr>
          <td style="width:46%"><?= htmlspecialchars($label); ?></td>
          <td class="num" style="width:14%"><?= number_format($n); ?></td>
          <td><?= cr_bar($cMax > 0 ? 100 * $n / $cMax : null); ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
