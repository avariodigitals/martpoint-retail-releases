<?php $this->load->view('portal/_head'); $pw = $patient; ?>
<div class="card">
  <h3>Account statement</h3>
  <div class="bal">
    <div class="cell"><div class="n" style="color:var(--mp-bad)"><?= number_format((float)$pw['total_debt'],2) ?></div><div class="l">Total outstanding</div></div>
    <?php if($pw['wallet']): ?>
    <div class="cell"><div class="n"><?= number_format((float)$pw['wallet']['available'],2) ?></div><div class="l">Wallet (available)</div></div>
    <div class="cell"><div class="n"><?= number_format((float)$pw['wallet']['reserved'],2) ?></div><div class="l">Wallet (reserved)</div></div>
    <?php endif; ?>
  </div>
  <p class="muted" style="margin-top:10px">Wallet funds are separate from what you owe and are <strong>not</strong> counted against your debt.</p>
</div>

<?php if(!empty($admissions)): ?>
<div class="card">
  <h3>Admission accounts</h3>
  <?php foreach($admissions as $a): ?>
    <h4 style="margin:12px 0 4px"><?= htmlspecialchars($a['sales_code']) ?></h4>
    <table>
      <tr><th>Date</th><th>Type</th><th>Ref</th><th>Amount</th><th>Balance</th></tr>
      <?php foreach($a['lines'] as $ln): ?>
      <tr><td><?= htmlspecialchars($ln['date'] ?? '') ?></td><td><?= htmlspecialchars($ln['kind']) ?></td>
          <td><?= htmlspecialchars($ln['ref'] ?? '') ?></td><td><?= number_format((float)$ln['amount'],2) ?></td>
          <td><?= number_format((float)$ln['balance'],2) ?></td></tr>
      <?php endforeach; ?>
    </table>
    <p class="muted">Admission outstanding: <strong><?= number_format((float)$a['outstanding'],2) ?></strong></p>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="card">
  <h3>Invoices &amp; payments</h3>
  <?php if(empty($pw['invoices'])): ?>
    <p class="muted">No invoices yet.</p>
  <?php else: ?>
    <?php foreach($pw['invoices'] as $inv): ?>
    <h4 style="margin:12px 0 4px"><?= htmlspecialchars($inv['sales_code']) ?> <span class="muted">(<?= htmlspecialchars($inv['sales_status']) ?>)</span></h4>
    <table>
      <tr><th>Date</th><th>Type</th><th>Ref</th><th>Amount</th><th>Balance</th></tr>
      <?php foreach($inv['lines'] as $ln): ?>
      <tr><td><?= htmlspecialchars($ln['date'] ?? '') ?></td><td><?= htmlspecialchars($ln['kind']) ?></td>
          <td><?= htmlspecialchars($ln['ref'] ?? '') ?></td><td><?= number_format((float)$ln['amount'],2) ?></td>
          <td><?= number_format((float)$ln['balance'],2) ?></td></tr>
      <?php endforeach; ?>
    </table>
    <p class="muted">Invoice outstanding: <strong><?= number_format((float)$inv['outstanding'],2) ?></strong></p>
    <?php endforeach; ?>
  <?php endif; ?>
</div>
<?php $this->load->view('portal/_foot'); ?>
