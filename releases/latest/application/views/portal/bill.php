<?php $this->load->view('portal/_head'); $due=(float)$sale->grand_total-(float)$sale->paid_amount; ?>
<div class="card">
  <h3>Invoice <?= htmlspecialchars($sale->sales_code) ?> — <?= htmlspecialchars($sale->sales_date) ?></h3>
  <table>
    <tr><th>Item</th><th>Qty</th><th>Amount</th></tr>
    <?php foreach($items as $i): ?>
    <tr><td><?= htmlspecialchars($i->item_name ?? $i->description ?? 'Item') ?></td><td><?= (float)($i->sales_qty ?? 1) ?></td><td><?= number_format((float)($i->total_cost ?? $i->total ?? 0),2) ?></td></tr>
    <?php endforeach; ?>
    <tr><th>Total</th><th></th><th><?= number_format((float)$sale->grand_total,2) ?></th></tr>
  </table>
</div>
<div class="card">
  <h3>Payments &amp; receipts</h3>
  <?php if(!$payments): ?><p class="muted">No payments recorded.</p><?php else: ?>
  <table>
    <tr><th>Date</th><th>Type</th><th>Amount</th></tr>
    <?php foreach($payments as $py): ?>
    <tr><td><?= htmlspecialchars($py->payment_date) ?></td><td><?= htmlspecialchars(str_replace('_',' ',$py->payment_type)) ?></td><td><?= number_format((float)$py->payment,2) ?></td></tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
<?php if($due > 0): ?>
<div class="card">
  <h3>Already paid? Submit your payment reference</h3>
  <p class="muted">Balance due: <strong><?= number_format($due,2) ?></strong>. If you paid by transfer or cash at the desk, enter the reference — the clinic verifies it before it counts.</p>
  <form method="post" action="<?= site_url('portal/submit_evidence/'.$sale->id) ?>">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
    <label>Amount</label><input name="amount" type="number" step="0.01" min="1" value="<?= htmlspecialchars(number_format($due,2,'.','')) ?>" required>
    <label>Payment reference</label><input name="payment_ref" required>
    <label>Channel</label><input name="channel" value="transfer" required>
    <div style="margin-top:12px"><button class="btn">Submit for verification</button></div>
  </form>
</div>
<?php endif; ?>
<?php $this->load->view('portal/_foot'); ?>
