<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/** Revenue & funds — charges, collections, funds held, service mix. */
$this->load->view('reports/clinical/_chrome', array('from' => $from, 'to' => $to));
$r = $report;
$svcMax = !empty($r['by_service']) ? max(array_column($r['by_service'], 'amount')) : 0;
$collectRate = (float)$r['charged'] > 0 ? round(100 * (float)$r['collected'] / (float)$r['charged'], 1) : null;
?>
<?php cr_toolbar($meta, $key, $from, $to); ?>

<?php cr_period_filter(base_url('clinical_reports/revenue'), $from, $to); ?>

<div class="cr-kpis">
  <?php
  cr_kpi('Charged', cr_money($r['charged']), 'posted to patient accounts', array('money' => true));
  cr_kpi('Collected', cr_money($r['collected']), 'recorded against those accounts', array('money' => true));
  cr_kpi('Collection rate', ($collectRate === null ? '—' : $collectRate . '%'),
      $collectRate === null ? 'nothing charged in period' : 'of the period\'s charges',
      array('alert' => ($collectRate !== null && $collectRate < 70)));
  cr_kpi('Funds held', cr_money($r['held']),
      number_format($r['held_reservations']) . ' active reservation(s)', array('money' => true));
  ?>
</div>

<p class="cr-note">
  <strong>What these figures mean.</strong> <em>Charged</em> is the total of patient-linked
  invoices raised in the period, and <em>collected</em> is what has been recorded as paid
  against them — so the gap is what is still owed, not a loss. <em>Funds held</em> is money a
  patient has paid in advance that is reserved against treatment plans but not yet consumed.
  Booking a refund changes a state; it does not move money.
</p>

<div class="cr-grid2">
  <div class="cr-panel">
    <h3>Funding taken in period</h3>
    <table class="cr-table">
      <tbody>
        <tr><td>Advance / deposit</td><td class="num"><?= cr_money($r['collected_advance']); ?></td></tr>
        <tr><td>Other funding</td><td class="num"><?= cr_money($r['collected_new']); ?></td></tr>
        <tr><td><strong>Total funding</strong></td><td class="num"><strong><?= cr_money($r['funding']); ?></strong></td></tr>
        <tr><td>Funds currently held</td><td class="num"><?= cr_money($r['held']); ?></td></tr>
      </tbody>
    </table>
  </div>

  <div class="cr-panel">
    <h3>Charged vs collected</h3>
    <table class="cr-table">
      <tbody>
        <tr><td>Charged</td><td class="num"><?= cr_money($r['charged']); ?></td></tr>
        <tr><td>Collected</td><td class="num"><?= cr_money($r['collected']); ?></td></tr>
        <tr><td>Still owed</td><td class="num"><?= cr_money(max(0, (float)$r['charged'] - (float)$r['collected'])); ?></td></tr>
      </tbody>
    </table>
  </div>
</div>

<div class="cr-panel">
  <h3>Service mix (posted charge lines)</h3>
  <table class="cr-table">
    <thead><tr><th>Service</th><th class="num">Lines</th><th class="num">Amount</th><th style="width:30%">Share</th></tr></thead>
    <tbody>
    <?php if(empty($r['by_service'])): ?>
      <tr><td colspan="4" class="cr-empty">
        No charge lines posted in this period. Charges created before the billing module was
        enabled are reflected in the totals above but have no item breakdown.
      </td></tr>
    <?php else: foreach($r['by_service'] as $s): ?>
      <tr>
        <td><?= htmlspecialchars($s['name']); ?></td>
        <td class="num"><?= number_format($s['n']); ?></td>
        <td class="num"><?= cr_money($s['amount']); ?></td>
        <td><?= cr_bar($svcMax > 0 ? 100 * $s['amount'] / $svcMax : null); ?></td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
