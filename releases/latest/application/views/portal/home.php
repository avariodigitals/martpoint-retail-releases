<?php $this->load->view('portal/_head'); ?>
<div class="card">
  <h3>Welcome</h3>
  <p class="muted" style="margin:0">Patient <strong><?= htmlspecialchars($patient_code) ?></strong><?= $p['kind']==='proxy' ? ' — caregiver access ('.htmlspecialchars(implode(', ', $p['scopes'])).')' : '' ?></p>
</div>
<?php if($funds): $b=$funds['balances']; ?>
<div class="card">
  <h3>Balances</h3>
  <div class="bal">
    <div class="cell"><div class="n"><?= number_format((float)$b['available'],2) ?></div><div class="l">Available funds</div></div>
    <div class="cell"><div class="n"><?= number_format((float)$b['reserved'],2) ?></div><div class="l">Reserved</div></div>
    <div class="cell"><div class="n"><?= number_format((float)$b['pending'],2) ?></div><div class="l">Pending verification</div></div>
    <div class="cell"><div class="n" style="color:var(--mp-bad)"><?= number_format((float)$b['debt'],2) ?></div><div class="l">Outstanding</div></div>
  </div>
  <?php if($funds['openings']): ?>
  <table style="margin-top:12px">
    <tr><th>Imported item</th><th>Amount</th><th>Status</th></tr>
    <?php foreach($funds['openings'] as $o): ?>
    <tr><td><?= htmlspecialchars($o->position_type) ?></td><td><?= number_format((float)$o->amount,2) ?></td>
      <td><?= $o->status==='approved' ? '<span class="badge b-ok">Confirmed</span>' : '<span class="badge b-warn">Under review</span>' ?></td></tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
<?php endif; ?>
<?php $this->load->view('portal/_foot'); ?>
