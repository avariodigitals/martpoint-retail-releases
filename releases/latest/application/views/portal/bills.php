<?php $this->load->view('portal/_head'); ?>
<div class="card">
  <h3>Bills</h3>
  <?php if(!$items): ?><p class="muted">No bills yet.</p><?php else: ?>
  <table>
    <tr><th>Invoice</th><th>Date</th><th>Total</th><th>Paid</th><th>Status</th><th></th></tr>
    <?php foreach($items as $b): $due = (float)$b->grand_total - (float)$b->paid_amount; ?>
    <tr>
      <td><?= htmlspecialchars($b->sales_code) ?></td><td><?= htmlspecialchars($b->sales_date) ?></td>
      <td><?= number_format((float)$b->grand_total,2) ?></td><td><?= number_format((float)$b->paid_amount,2) ?></td>
      <td><span class="badge <?= $b->payment_status==='Paid'?'b-ok':($due>0?'b-bad':'b-mut') ?>"><?= $b->payment_status==='Paid'?'Paid':'Due '.number_format($due,2) ?></span></td>
      <td><a href="<?= site_url('portal/bill/'.$b->id) ?>">Open</a></td>
    </tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
<?php $this->load->view('portal/_foot'); ?>
