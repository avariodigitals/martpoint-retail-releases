<?php $this->load->view('portal/_head'); ?>
<div class="card" style="max-width:420px;margin:40px auto;">
  <h3><?= htmlspecialchars($title ?? 'Not permitted') ?></h3>
  <p class="muted">This item isn't available to your account.</p>
  <a class="btn ghost" href="<?= site_url('portal/home') ?>">Back to portal</a>
</div>
<?php $this->load->view('portal/_foot'); ?>
