<?php $this->load->view('portal/_head'); ?>
<div class="card">
  <h3>Treatment entitlements</h3>
  <?php if(!$entitlements): ?><p class="muted">No packages yet.</p><?php else: ?>
  <table>
    <tr><th>#</th><th>Sessions used</th><th>Status</th></tr>
    <?php foreach($entitlements as $e): ?>
    <tr><td>PKG-<?= (int)$e->id ?></td><td><?= (float)$e->units_used ?> / <?= (float)$e->units_total ?></td>
      <td><span class="badge <?= $e->status==='active'?'b-ok':'b-mut' ?>"><?= htmlspecialchars($e->status) ?></span></td></tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
<div class="card">
  <h3>Sessions</h3>
  <?php if(!$sessions): ?><p class="muted">No sessions yet.</p><?php else: ?>
  <table>
    <tr><th>Date</th><th>Session</th><th>Status</th></tr>
    <?php foreach($sessions as $s): ?>
    <tr><td><?= htmlspecialchars($s->scheduled_at) ?></td><td><?= (int)$s->session_no ?> of <?= (float)$s->units_total ?></td>
      <td><span class="badge <?= $s->status==='completed'?'b-ok':'b-mut' ?>"><?= htmlspecialchars($s->status) ?></span></td></tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
<?php $this->load->view('portal/_foot'); ?>
