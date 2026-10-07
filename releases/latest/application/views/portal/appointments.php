<?php $this->load->view('portal/_head'); ?>
<div class="card">
  <h3>Appointments</h3>
  <?php if(!$items): ?><p class="muted">No appointments yet.</p><?php else: ?>
  <table>
    <tr><th>When</th><th>Ref</th><th>Status</th></tr>
    <?php foreach($items as $a): ?>
    <tr><td><?= htmlspecialchars($a->scheduled_at) ?></td><td><?= htmlspecialchars($a->booking_ref) ?></td>
      <td><span class="badge <?= $a->status==='confirmed'||$a->status==='completed'?'b-ok':($a->status==='cancelled'||$a->status==='no_show'?'b-bad':'b-mut') ?>"><?= htmlspecialchars(str_replace('_',' ',$a->status)) ?></span></td></tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
<?php $this->load->view('portal/_foot'); ?>
