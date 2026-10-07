<?php $this->load->view('portal/_head'); $b=$balances; ?>
<div class="card">
  <h3>Your funds</h3>
  <div class="bal">
    <div class="cell"><div class="n"><?= number_format((float)$b['available'],2) ?></div><div class="l">Available</div></div>
    <div class="cell"><div class="n"><?= number_format((float)$b['reserved'],2) ?></div><div class="l">Reserved for sessions</div></div>
    <div class="cell"><div class="n"><?= number_format((float)$b['pending'],2) ?></div><div class="l">Pending verification</div></div>
    <div class="cell"><div class="n" style="color:var(--mp-bad)"><?= number_format((float)$b['debt'],2) ?></div><div class="l">Outstanding debt</div></div>
  </div>
  <p class="muted" style="margin-top:10px">Retail store credit is separate and shown at the clinic desk.</p>
</div>
<div class="card">
  <h3>Imported balances</h3>
  <?php if(!$openings): ?><p class="muted">Nothing imported.</p><?php else: ?>
  <table>
    <tr><th>Item</th><th>Amount</th><th>Units</th><th>Status</th></tr>
    <?php foreach($openings as $o): ?>
    <tr><td><?= htmlspecialchars(str_replace('_',' ',$o->position_type)) ?></td>
      <td><?= number_format((float)$o->amount,2) ?></td><td><?= (float)$o->units ?></td>
      <td><?= $o->status==='approved' ? '<span class="badge b-ok">Confirmed</span>' : ($o->status==='rejected' ? '<span class="badge b-bad">Rejected</span>' : '<span class="badge b-warn">Under review</span>') ?></td></tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
<?php $this->load->view('portal/_foot'); ?>
