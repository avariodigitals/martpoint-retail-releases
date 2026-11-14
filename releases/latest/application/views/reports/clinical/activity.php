<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/** Clinical activity — encounters, queue, assessments, plans. */
$this->load->view('reports/clinical/_chrome', array('from' => $from, 'to' => $to));
$r = $report;
?>
<?php cr_toolbar($meta, $key, $from, $to); ?>

<?php cr_period_filter(base_url('clinical_reports/activity'), $from, $to); ?>

<div class="cr-kpis">
  <?php
  cr_kpi('Encounters', number_format($r['encounters']), 'patients checked in during the period');
  cr_kpi('Open care queue', number_format($r['queue_open']), 'waiting right now',
      array('alert' => $r['queue_open'] > 0));
  cr_kpi('Assessments', number_format($r['assessments']['total']),
      $r['assessments']['finalized'] . ' finalised · ' . $r['assessments']['draft'] . ' in draft');
  cr_kpi('Active plans', number_format($r['plans']['active']),
      number_format($r['plans']['new']) . ' started in the period');
  ?>
</div>

<div class="cr-grid2">
  <div class="cr-panel">
    <h3>Care queue right now</h3>
    <table class="cr-table">
      <thead><tr><th>Stage</th><th class="num">Patients</th><th style="width:36%">Share</th></tr></thead>
      <tbody>
      <?php if(empty($r['queue_stages'])): ?>
        <tr><td colspan="3" class="cr-empty">Queue is clear.</td></tr>
      <?php else: $qMax = max($r['queue_stages']); foreach($r['queue_stages'] as $stage => $n): ?>
        <tr>
          <td><span class="cr-badge"><?= htmlspecialchars(str_replace('_',' ',$stage)); ?></span></td>
          <td class="num"><?= number_format($n); ?></td>
          <td><?= cr_bar($qMax > 0 ? 100 * $n / $qMax : null); ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

  <div class="cr-panel">
    <h3>Assessment throughput</h3>
    <table class="cr-table">
      <tbody>
        <tr><td>Assessments created</td><td class="num"><?= number_format($r['assessments']['total']); ?></td></tr>
        <tr><td>Finalised</td><td class="num"><?= number_format($r['assessments']['finalized']); ?></td></tr>
        <tr><td>Still in draft</td><td class="num"><?= number_format($r['assessments']['draft']); ?></td></tr>
        <tr><td>Encounters in period</td><td class="num"><?= number_format($r['encounters']); ?></td></tr>
        <tr><td>New treatment plans</td><td class="num"><?= number_format($r['plans']['new']); ?></td></tr>
        <tr><td>Active treatment plans</td><td class="num"><?= number_format($r['plans']['active']); ?></td></tr>
      </tbody>
    </table>
  </div>
</div>

<p class="cr-note">
  A high number of assessments left in draft usually means clinicians are documenting the
  encounter but not signing it off — those records are not usable as the clinical history
  or as the basis of a treatment plan until they are finalised.
</p>
