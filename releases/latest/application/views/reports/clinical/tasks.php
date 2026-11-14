<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/** Ward task load — nursing and porter work raised, done and overdue. */
$this->load->view('reports/clinical/_chrome', array('from' => $from, 'to' => $to));
$r = $report;
?>
<?php cr_toolbar($meta, $key, $from, $to); ?>

<?php cr_period_filter(base_url('clinical_reports/tasks'), $from, $to); ?>

<div class="cr-kpis">
  <?php
  cr_kpi('Nursing open', number_format($r['nursing']['open']), 'assigned and not yet done',
      array('alert' => $r['nursing']['open'] > 0));
  cr_kpi('Nursing overdue', number_format($r['nursing']['overdue']), 'past the due time',
      array('alert' => $r['nursing']['overdue'] > 0));
  cr_kpi('Porter open', number_format($r['porter']['open']), 'patient movement requests waiting',
      array('alert' => $r['porter']['open'] > 0));
  cr_kpi('Porter done', number_format($r['porter']['done']), 'movements completed');
  ?>
</div>

<p class="cr-note">
  <strong>Why this matters.</strong> An overdue nursing observation and an uncollected porter
  request are both patient-safety signals, not paperwork. This report shows the load the ward
  is actually carrying so shifts can be staffed against it.
</p>

<div class="cr-grid2">
  <div class="cr-panel">
    <h3>Nursing tasks</h3>
    <table class="cr-table">
      <thead><tr><th>Status</th><th class="num">Tasks</th></tr></thead>
      <tbody>
      <?php if(empty($r['nursing']['by_type'])): ?>
        <tr><td colspan="2" class="cr-empty">No nursing tasks raised in this period.</td></tr>
      <?php else: foreach($r['nursing']['by_type'] as $label => $n): ?>
        <tr>
          <td><span class="cr-badge<?= $label === 'open' ? ' warn' : ''; ?>"><?= htmlspecialchars($label); ?></span></td>
          <td class="num"><?= number_format($n); ?></td>
        </tr>
      <?php endforeach; endif; ?>
        <tr><td><strong>Overdue right now</strong></td><td class="num"><strong><?= number_format($r['nursing']['overdue']); ?></strong></td></tr>
      </tbody>
    </table>
  </div>

  <div class="cr-panel">
    <h3>Porter movement tasks</h3>
    <table class="cr-table">
      <thead><tr><th>Task type</th><th class="num">Tasks</th></tr></thead>
      <tbody>
      <?php if(empty($r['porter']['by_type'])): ?>
        <tr><td colspan="2" class="cr-empty">No porter tasks recorded.</td></tr>
      <?php else: foreach($r['porter']['by_type'] as $label => $n): ?>
        <tr>
          <td><?= htmlspecialchars(str_replace('_', ' ', $label)); ?></td>
          <td class="num"><?= number_format($n); ?></td>
        </tr>
      <?php endforeach; endif; ?>
        <tr><td><strong>Open</strong></td><td class="num"><strong><?= number_format($r['porter']['open']); ?></strong></td></tr>
        <tr><td><strong>Completed</strong></td><td class="num"><strong><?= number_format($r['porter']['done']); ?></strong></td></tr>
      </tbody>
    </table>
  </div>
</div>
