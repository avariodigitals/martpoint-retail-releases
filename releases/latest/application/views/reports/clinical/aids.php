<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/** Therapy aids & material — consumables used against patient work. */
$this->load->view('reports/clinical/_chrome', array('from' => $from, 'to' => $to));
$r = $report;
$max = !empty($r['items']) ? max(array_column($r['items'], 'amount')) : 0;
?>
<?php cr_toolbar($meta, $key, $from, $to); ?>

<?php cr_period_filter(base_url('clinical_reports/aids'), $from, $to); ?>

<div class="cr-kpis">
  <?php
  cr_kpi('Items used', number_format(count($r['items'])), 'distinct aids and material');
  cr_kpi('Charge lines', number_format($r['lines']), 'lines raised against patient work');
  cr_kpi('Quantity', number_format($r['qty'], 2), 'total units consumed');
  cr_kpi('Value', cr_money($r['value']), 'charged to patients', array('money' => true));
  ?>
</div>

<p class="cr-note">
  <strong>What this shows.</strong> Every aid or material charged against a patient's treatment,
  ranked by value. Use it to see which consumables actually drive the clinic's cost of treatment —
  and to spot an item billed far more often than stock suggests is being consumed.
  Stock quantities on hand are on the Inventory screens.
</p>

<div class="cr-panel">
  <h3>Aids &amp; material by value</h3>
  <table class="cr-table">
    <thead>
      <tr><th>Item</th><th>Code</th><th class="num">Qty</th><th class="num">Lines</th><th class="num">Value</th><th style="width:22%">Share</th></tr>
    </thead>
    <tbody>
    <?php if(empty($r['items'])): ?>
      <tr><td colspan="6" class="cr-empty">No aids or material charged in this period.</td></tr>
    <?php else: foreach($r['items'] as $i): ?>
      <tr>
        <td><?= htmlspecialchars($i['name']); ?></td>
        <td><?= htmlspecialchars($i['code'] ?: '—'); ?></td>
        <td class="num"><?= number_format($i['qty'], 2); ?></td>
        <td class="num"><?= number_format($i['lines']); ?></td>
        <td class="num"><?= cr_money($i['amount']); ?></td>
        <td><?= cr_bar($max > 0 ? 100 * $i['amount'] / $max : null); ?></td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
