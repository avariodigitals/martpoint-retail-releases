<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/** Treatment plans — course progress and stalled episodes. */
$this->load->view('reports/clinical/_chrome', array('from' => $from, 'to' => $to));
$p = $report['progress'];
?>
<?php cr_toolbar($meta, $key, $from, $to); ?>

<?php cr_period_filter(base_url('clinical_reports/plans'), $from, $to); ?>

<div class="cr-kpis">
  <?php
  cr_kpi('Plans on file', number_format($report['total']), 'all treatment plans, every status');
  cr_kpi('Started in period', number_format($report['new']), 'new plans opened');
  cr_kpi('Delivered', $p['rate'] === null ? '—' : cr_number($p['rate'], 1) . '%',
      cr_number($p['delivered']) . ' of ' . cr_number($p['planned']) . ' units',
      array('alert' => $p['rate'] !== null && $p['rate'] < 50));
  cr_kpi('Stalled', number_format($report['stalled']), 'open over 30 days, under half delivered',
      array('alert' => $report['stalled'] > 0));
  ?>
</div>

<p class="cr-note">
  <strong>Why this matters.</strong> A physiotherapy patient buys a course, not a visit, so an
  unfinished plan is both an unpaid balance and a patient who stopped getting better. The
  <em>stalled</em> count is the one to act on: those plans have been open over a month with
  under half their units delivered.
</p>

<div class="cr-grid2">
  <div class="cr-panel">
    <h3>Course progress</h3>
    <table class="cr-table">
      <thead><tr><th>Measure</th><th class="num">Units</th></tr></thead>
      <tbody>
        <tr><td>Planned across all plans</td><td class="num"><?= cr_number($p['planned']); ?></td></tr>
        <tr><td>Delivered (completed sessions)</td><td class="num"><?= cr_number($p['delivered']); ?></td></tr>
        <tr><td>Outstanding</td><td class="num"><?= cr_number(max(0, $p['planned'] - $p['delivered'])); ?></td></tr>
        <tr>
          <td><strong>Delivered</strong></td>
          <td class="num"><strong><?= $p['rate'] === null ? '—' : cr_number($p['rate'], 1) . '%'; ?></strong></td>
        </tr>
      </tbody>
    </table>
  </div>

  <div class="cr-panel">
    <h3>Plans by status</h3>
    <table class="cr-table">
      <thead><tr><th>Status</th><th class="num">Plans</th></tr></thead>
      <tbody>
      <?php if(empty($report['by_status'])): ?>
        <tr><td colspan="2" class="cr-empty">No treatment plans on file yet.</td></tr>
      <?php else: foreach($report['by_status'] as $status => $n): ?>
        <tr>
          <td><span class="cr-badge"><?= htmlspecialchars(str_replace('_', ' ', $status)); ?></span></td>
          <td class="num"><?= number_format($n); ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>
</div>

<div class="cr-panel">
  <h3>Open plans needing attention</h3>
  <table class="cr-table">
    <thead>
      <tr>
        <th>Plan</th><th>Patient</th><th>Status</th>
        <th class="num">Planned</th><th class="num">Delivered</th><th class="num">Left</th>
        <th class="num">Progress</th><th class="num">Open</th>
      </tr>
    </thead>
    <tbody>
    <?php if(empty($report['open'])): ?>
      <tr><td colspan="8" class="cr-empty">No open treatment plans.</td></tr>
    <?php else: foreach($report['open'] as $x): ?>
      <tr>
        <td>
          <strong><?= htmlspecialchars((string)$x['plan_code']); ?></strong>
          <?php if(!empty($x['title'])): ?><div class="cr-sub"><?= htmlspecialchars($x['title']); ?></div><?php endif; ?>
        </td>
        <td><?= htmlspecialchars($x['patient']); ?></td>
        <td><span class="cr-badge"><?= htmlspecialchars(str_replace('_', ' ', (string)$x['status'])); ?></span></td>
        <td class="num"><?= cr_number($x['planned']); ?></td>
        <td class="num"><?= cr_number($x['delivered']); ?></td>
        <td class="num"><?= cr_number($x['remaining']); ?></td>
        <td class="num"><?= cr_bar($x['rate']); ?></td>
        <td class="num"><?= $x['age_days'] === null ? '—' : (int)$x['age_days'] . 'd'; ?></td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>

<?php if(!empty($report['by_clinician'])): ?>
<div class="cr-panel">
  <h3>Open caseload by clinician</h3>
  <table class="cr-table">
    <thead><tr><th>Clinician</th><th class="num">Open plans</th></tr></thead>
    <tbody>
    <?php foreach($report['by_clinician'] as $x): ?>
      <tr>
        <td><?= htmlspecialchars($x['name']); ?></td>
        <td class="num"><?= number_format($x['plans']); ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>
