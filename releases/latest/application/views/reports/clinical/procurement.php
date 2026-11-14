<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/** Procurement — purchases of clinical supplies and aids. */
$this->load->view('reports/clinical/_chrome', array('from' => $from, 'to' => $to));
$r = $report;
$max = !empty($r['suppliers']) ? max(array_column($r['suppliers'], 'amount')) : 0;
?>
<?php cr_toolbar($meta, $key, $from, $to); ?>

<?php cr_period_filter(base_url('clinical_reports/procurement'), $from, $to); ?>

<div class="cr-kpis">
  <?php
  cr_kpi('Purchase orders', number_format($r['orders']), 'raised in the period');
  cr_kpi('Value', cr_money($r['value']), 'goods and supplies ordered', array('money' => true));
  cr_kpi('Paid', cr_money($r['paid']), 'settled against those orders', array('money' => true));
  cr_kpi('Outstanding', cr_money($r['balance']), 'still to pay suppliers',
      array('money' => true, 'alert' => $r['balance'] > 0));
  ?>
</div>

<p class="cr-note">
  <strong>Procurement here is purchasing.</strong> This is the same purchase ledger the retail
  side uses — in a clinic it carries therapy aids, consumables, linen and equipment.
  Supplier balances are payable, not patient debt.
</p>

<div class="cr-panel">
  <h3>Spend by supplier</h3>
  <table class="cr-table">
    <thead><tr><th>Supplier</th><th class="num">Orders</th><th class="num">Value</th><th style="width:30%">Share</th></tr></thead>
    <tbody>
    <?php if(empty($r['suppliers'])): ?>
      <tr><td colspan="4" class="cr-empty">No purchases recorded in this period.</td></tr>
    <?php else: foreach($r['suppliers'] as $s): ?>
      <tr>
        <td><?= htmlspecialchars($s['name']); ?></td>
        <td class="num"><?= number_format($s['n']); ?></td>
        <td class="num"><?= cr_money($s['amount']); ?></td>
        <td><?= cr_bar($max > 0 ? 100 * $s['amount'] / $max : null); ?></td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
