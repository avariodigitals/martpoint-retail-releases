<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/** Outstanding balances — aged patient debt. */
$this->load->view('reports/clinical/_chrome', array('from' => $from, 'to' => $to));
$r = $report;
$bucketMax = !empty($r['buckets']) ? max($r['buckets']) : 0;
$overdue = (float)$r['buckets']['1-30 days'] + (float)$r['buckets']['31-60 days']
	+ (float)$r['buckets']['61-90 days'] + (float)$r['buckets']['90+ days'];
$severe = (float)$r['buckets']['61-90 days'] + (float)$r['buckets']['90+ days'];
?>
<?php cr_toolbar($meta, $key, $from, $to); ?>

<div class="cr-kpis">
  <?php
  cr_kpi('Total outstanding', cr_money($r['total']), 'across all patient accounts', array('money' => true));
  cr_kpi('Accounts in debt', number_format($r['accounts']), 'patients with a balance');
  cr_kpi('Overdue (>30 days)', cr_money($overdue), 'aged beyond the current month', array('money' => true, 'alert' => $overdue > 0));
  cr_kpi('At risk (>60 days)', cr_money($severe), 'hard to collect', array('money' => true, 'alert' => $severe > 0));
  ?>
</div>

<p class="cr-note">
  <strong>Aging basis.</strong> Balances are the unpaid part of each patient account
  (invoice total minus amount paid) and are aged from the invoice date. This report reads
  patient-linked invoices only, so ordinary counter sales never appear as patient debt.
  A booked refund is a state change and does not move money — collect overdue balances
  outside the system and record the payment against the account.
</p>

<div class="cr-panel">
  <h3>Aged balances</h3>
  <table class="cr-table">
    <thead><tr><th>Age band</th><th class="num">Balance</th><th style="width:42%">Share</th></tr></thead>
    <tbody>
    <?php foreach($r['buckets'] as $label => $amt): ?>
      <tr>
        <td><?= htmlspecialchars($label); ?></td>
        <td class="num"><?= cr_money($amt); ?></td>
        <td><?= cr_bar($bucketMax > 0 ? 100 * (float)$amt / $bucketMax : null); ?></td>
      </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="cr-panel">
  <h3>Largest balances (top 25)</h3>
  <table class="cr-table">
    <thead>
      <tr><th>Invoice</th><th>Patient</th><th>Date</th><th class="num">Age</th><th class="num">Balance</th></tr>
    </thead>
    <tbody>
    <?php if(empty($r['top'])): ?>
      <tr><td colspan="5" class="cr-empty">No outstanding balances. Every patient account is settled.</td></tr>
    <?php else: foreach($r['top'] as $t): ?>
      <tr>
        <td><?= htmlspecialchars($t['code']); ?></td>
        <td>
          <?= htmlspecialchars($t['patient']); ?>
          <?php if(!empty($t['patient_code'])): ?><br><small style="color:var(--mp-muted)"><?= htmlspecialchars($t['patient_code']); ?></small><?php endif; ?>
        </td>
        <td><?= $t['date'] ? date('d M Y', strtotime($t['date'])) : '—'; ?></td>
        <td class="num">
          <?php
          $badge = 'cr-badge';
          if($t['age'] > 60)      { $badge .= ' bad'; }
          elseif($t['age'] > 30)  { $badge .= ' warn'; }
          ?>
          <span class="<?= $badge; ?>"><?= (int)$t['age']; ?>d</span>
        </td>
        <td class="num"><?= cr_money($t['balance']); ?></td>
      </tr>
    <?php endforeach; endif; ?>
    </tbody>
  </table>
</div>
