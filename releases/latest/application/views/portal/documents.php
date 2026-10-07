<?php $this->load->view('portal/_head'); ?>
<div class="card">
  <h3>Your documents</h3>
  <p class="muted">Only consent documents you signed and reports your clinician released appear here.</p>
  <?php if(!$items): ?><p class="muted">Nothing available yet.</p><?php else: ?>
  <table>
    <tr><th>Document</th><th>Category</th><th></th></tr>
    <?php foreach($items as $d): ?>
    <tr><td><?= htmlspecialchars($d->title ?: 'Document '.$d->id) ?></td><td><?= htmlspecialchars($d->category) ?></td>
      <td><a href="<?= site_url('portal/document/'.$d->id) ?>">View</a></td></tr>
    <?php endforeach; ?>
  </table>
  <?php endif; ?>
</div>
<?php $this->load->view('portal/_foot'); ?>
