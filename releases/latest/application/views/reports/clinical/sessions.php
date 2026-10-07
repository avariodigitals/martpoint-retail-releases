<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/** Treatment sessions — delivered volume, units, unposted fees, clinician load. */
$this->load->view('reports/clinical/_chrome', array('from' => $from, 'to' => $to));
$r = $report;
$completed = (int)($r['by_status']['completed'] ?? 0);
$cancelled = (int)($r['by_status']['cancelled'] ?? 0);
$done = $completed + $cancelled;
?>
<?php cr_toolbar($meta, $key, $from, $to); ?>

<?php cr_period_filter(base_url('clinical_reports/sessions'), $from, $to); ?>

<div class="cr-kpis">
  <?php
  cr_kpi('Sessions scheduled', number_format($r['total']), 'in the period');
  cr_kpi('Completed', number_format($completed),
      $done > 0 ? round(100 * $completed / $done) . '% of closed sessions' : 'none closed yet');
  cr_kpi('Units delivered', number_format($r['units']), 'billable session units');
  cr_kpi('Fees posted', cr_money($r['fee_posted']), 'posted through billing', array('money' => true));
  cr_kpi('Not yet posted', number_format($r['not_posted']),
      $r['not_posted'] > 0 ? 'completed but never charged' : 'every completed session is posted',
      array('alert' => $r['not_posted'] > 0));
  ?>
</div>

<?php if($r['not_posted'] > 0): ?>
<p class="cr-note" style="border-color:#e9d9bd;background:#fdf8f0">
  <strong><?= number_format($r['not_posted']); ?> completed session(s) have no charge posted.</strong>
  These sessions were delivered but never billed — open the sessions screen and post the fee,
  or the revenue will not appear on the patient's account.
</p>
<?php endif; ?>

<div class="cr-grid2">
  <div class="cr-panel">
    <h3>Sessions by status</h3>
    <table class="cr-table">
      <tbody>
      <?php if(empty($r['by_status'])): ?>
        <tr><td class="cr-empty">No sessions in this period.</td></tr>
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
    <h3>Caseload by clinician</h3>
    <table class="cr-table">
      <thead><tr><th>Clinician</th><th class="num">Sessions</th><th class="num">Units</th><th style="width:26%">Share</th></tr></thead>
      <tbody>
      <?php if(empty($r['by_clinician'])): ?>
        <tr><td colspan="4" class="cr-empty">No sessions assigned.</td></tr>
      <?php else: $cMax = max(array_column($r['by_clinician'], 'n')); foreach($r['by_clinician'] as $s): ?>
        <tr>
          <td><?= htmlspecialchars($s['name']); ?></td>
          <td class="num"><?= number_format($s['n']); ?></td>
          <td class="num"><?= number_format($s['units']); ?></td>
          <td><?= cr_bar($cMax > 0 ? 100 * $s['n'] / $cMax : null); ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>
