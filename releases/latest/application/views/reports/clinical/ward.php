<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/** Ward occupancy — bed utilisation by ward, admissions, length of stay. */
$this->load->view('reports/clinical/_chrome', array('from' => $from, 'to' => $to));
$r = $report;
?>
<?php cr_toolbar($meta, $key, $from, $to); ?>

<?php cr_period_filter(base_url('clinical_reports/ward'), $from, $to); ?>

<div class="cr-kpis">
  <?php
  cr_kpi('Beds', number_format($r['beds']['total']), 'across all wards');
  cr_kpi('Occupancy', ($r['beds']['rate'] === null ? '—' : $r['beds']['rate'] . '%'),
      $r['beds']['occupied'] . ' occupied · ' . $r['beds']['free'] . ' free',
      array('alert' => ($r['beds']['rate'] !== null && $r['beds']['rate'] >= 90)));
  cr_kpi('Admissions', number_format($r['admissions']['new']), 'in the period');
  cr_kpi('Average stay', ($r['los']['avg'] === null ? '—' : $r['los']['avg'] . ' days'),
      $r['los']['n'] . ' closed admission(s)');
  cr_kpi('Discharged', number_format($r['admissions']['discharged']), 'closed in the period');
  cr_kpi('Deceased', number_format($r['admissions']['deceased']), 'recorded in the period',
      array('alert' => $r['admissions']['deceased'] > 0));
  ?>
</div>

<?php if($r['beds']['rate'] !== null && $r['beds']['rate'] >= 90): ?>
<p class="cr-note" style="border-color:#e9d9bd;background:#fdf8f0">
  <strong>Ward is at <?= $r['beds']['rate']; ?>% occupancy.</strong> At this level a new
  admission has nowhere to go and a transfer request will be refused — consider discharging
  patients who are medically ready, or holding beds for scheduled admissions.
</p>
<?php endif; ?>

<div class="cr-panel">
  <h3>Utilisation by ward</h3>
  <table class="cr-table">
    <thead><tr><th>Ward</th><th class="num">Beds</th><th class="num">Occupied</th><th class="num">Free</th><th style="width:28%">Occupancy</th></tr></thead>
    <tbody>
    <?php if(empty($r['wards'])): ?>
      <tr><td colspan="5" class="cr-empty">No wards configured for this store.</td></tr>
    <?php else: foreach($r['wards'] as $w): ?>
      <tr>
        <td><?= htmlspecialchars($w['name']); ?></td>
        <td class="num"><?= number_format($w['total']); ?></td>
        <td class="num"><?= number_format($w['occupied']); ?></td>
        <td class="num"><?= number_format($w['free']); ?></td>
        <td><?= cr_bar($w['rate']); ?></td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<div class="cr-grid2">
  <div class="cr-panel">
    <h3>Admission outcomes in period</h3>
    <table class="cr-table">
      <tbody>
        <tr><td>Admitted</td><td class="num"><?= number_format($r['admissions']['new']); ?></td></tr>
        <tr><td>Discharged</td><td class="num"><?= number_format($r['admissions']['discharged']); ?></td></tr>
        <tr><td>Deceased</td><td class="num"><?= number_format($r['admissions']['deceased']); ?></td></tr>
        <tr><td>Currently admitted</td><td class="num"><?= number_format($r['admissions']['active']); ?></td></tr>
      </tbody>
    </table>
  </div>

  <div class="cr-panel">
    <h3>Throughput</h3>
    <table class="cr-table">
      <tbody>
        <tr><td>Average length of stay</td>
            <td class="num"><?= $r['los']['avg'] === null ? '—' : $r['los']['avg'] . ' days'; ?></td></tr>
        <tr><td>Closed admissions measured</td><td class="num"><?= number_format($r['los']['n']); ?></td></tr>
        <tr><td>Admissions per bed</td>
            <td class="num"><?= $r['turnover'] === null ? '—' : htmlspecialchars($r['turnover']); ?></td></tr>
      </tbody>
    </table>
  </div>
</div>
