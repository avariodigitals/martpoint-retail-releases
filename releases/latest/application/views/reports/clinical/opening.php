<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/** Opening positions — imported balances awaiting review or approval. */
$this->load->view('reports/clinical/_chrome', array('from' => $from, 'to' => $to));
$r = $report;
?>
<?php cr_toolbar($meta, $key, $from, $to); ?>

<div class="cr-kpis">
  <?php
  cr_kpi('Awaiting review', number_format($r['count']), 'positions not yet approved',
      array('alert' => $r['count'] > 0));
  cr_kpi('Total value', cr_money($r['total']), 'carried from the previous system', array('money' => true));
  ?>
</div>

<p class="cr-note">
  <strong>These are migrated balances.</strong> Opening positions are the debts patients already
  owed when they moved onto MartPoint. They were imported as inert records — no charges, invoices
  or payments were created — and stay here until a person with the review grant checks and
  approves each one. Nothing appears on a patient's statement until it is approved.
</p>

<div class="cr-panel">
  <h3>Positions awaiting review (top 25)</h3>
  <table class="cr-table">
    <thead><tr><th>Patient</th><th>Patient code</th><th>Status</th><th class="num">Amount</th></tr></thead>
    <tbody>
    <?php if(empty($r['rows'])): ?>
      <tr><td colspan="4" class="cr-empty">Nothing is awaiting review.</td></tr>
    <?php else: foreach($r['rows'] as $o): ?>
      <tr>
        <td><?= htmlspecialchars($o['patient']); ?></td>
        <td><?= htmlspecialchars($o['patient_code'] ?: '—'); ?></td>
        <td><span class="cr-badge<?= in_array($o['status'], array('review','submitted'), true) ? ' warn' : ''; ?>"><?= htmlspecialchars($o['status'] ?: 'pending'); ?></span></td>
        <td class="num"><?= cr_money($o['amount']); ?></td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
