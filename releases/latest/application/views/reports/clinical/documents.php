<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/** Documents & consent — record completeness and outstanding signatures. */
$this->load->view('reports/clinical/_chrome', array('from' => $from, 'to' => $to));
?>
<?php cr_toolbar($meta, $key, $from, $to); ?>

<?php cr_period_filter(base_url('clinical_reports/documents'), $from, $to); ?>

<div class="cr-kpis">
  <?php
  cr_kpi('Documents on file', number_format($report['total']), 'all document records');
  cr_kpi('Added in period', number_format($report['in_period']), 'created within the selected dates');
  cr_kpi('Awaiting signature', number_format($report['unsigned']), 'draft or unsigned consent',
      array('alert' => $report['unsigned'] > 0));
  cr_kpi('Coverage', $report['coverage'] === null ? '—' : cr_number($report['coverage'], 1) . '%',
      number_format($report['patients_with_docs']) . ' of ' . number_format($report['patients_total']) . ' patients have a file',
      array('alert' => $report['coverage'] !== null && $report['coverage'] < 90));
  ?>
</div>

<p class="cr-note">
  <strong>Why this matters.</strong> Consent and signature evidence is the one part of a
  clinical record that only matters when it is missing &mdash; and by then the patient has gone
  home. The list at the bottom names every patient with no document on file at all, so the gap
  can be closed while they are still coming in for treatment.
</p>

<div class="cr-grid2">
  <div class="cr-panel">
    <h3>Documents by status</h3>
    <table class="cr-table">
      <thead><tr><th>Status</th><th class="num">Documents</th><th class="num">Share</th></tr></thead>
      <tbody>
      <?php if(empty($report['by_status'])): ?>
        <tr><td colspan="3" class="cr-empty">No documents on file yet.</td></tr>
      <?php else:
        $total = max(1, $report['total']);
        foreach($report['by_status'] as $status => $n): ?>
        <tr>
          <td><span class="cr-badge<?= in_array($status, array('draft','pending'), true) ? ' warn' : ''; ?>"><?= htmlspecialchars(str_replace('_', ' ', $status)); ?></span></td>
          <td class="num"><?= number_format($n); ?></td>
          <td class="num"><?= cr_bar(round(100 * $n / $total, 1)); ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

  <div class="cr-panel">
    <h3>Outstanding work</h3>
    <table class="cr-table">
      <thead><tr><th>Measure</th><th class="num">Documents</th></tr></thead>
      <tbody>
        <tr>
          <td>Awaiting signature</td>
          <td class="num"><?= number_format($report['unsigned']); ?></td>
        </tr>
        <tr>
          <td>Not yet released to the patient</td>
          <td class="num"><?= number_format($report['awaiting_release']); ?></td>
        </tr>
        <tr>
          <td><strong>Patients with no document at all</strong></td>
          <td class="num"><strong><?= number_format(max(0, $report['patients_total'] - $report['patients_with_docs'])); ?></strong></td>
        </tr>
      </tbody>
    </table>
  </div>
</div>

<div class="cr-grid2">
  <div class="cr-panel">
    <h3>Documents by category</h3>
    <table class="cr-table">
      <thead><tr><th>Category</th><th class="num">Documents</th></tr></thead>
      <tbody>
      <?php if(empty($report['by_category'])): ?>
        <tr><td colspan="2" class="cr-empty">No documents recorded.</td></tr>
      <?php else: foreach($report['by_category'] as $cat => $n): ?>
        <tr>
          <td><?= htmlspecialchars(str_replace('_', ' ', $cat)); ?></td>
          <td class="num"><?= number_format($n); ?></td>
        </tr>
      <?php endforeach; endif; ?>
      </tbody>
    </table>
  </div>

  <?php if(!empty($report['trend'])): ?>
  <div class="cr-panel">
    <h3>Documents created by month</h3>
    <table class="cr-table">
      <thead><tr><th>Month</th><th class="num">Documents</th></tr></thead>
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
</div>

<div class="cr-panel">
  <h3>Patients with no document on file</h3>
  <table class="cr-table">
    <thead><tr><th>Patient</th><th>Code</th><th class="num">Documents</th></tr></thead>
    <tbody>
    <?php if(empty($report['gaps'])): ?>
      <tr><td colspan="3" class="cr-empty">Every active patient has at least one document on file.</td></tr>
    <?php else: foreach($report['gaps'] as $x): ?>
      <tr>
        <td><?= htmlspecialchars($x['patient']); ?></td>
        <td><?= htmlspecialchars((string)$x['patient_code']); ?></td>
        <td class="num"><?= number_format($x['docs']); ?></td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
