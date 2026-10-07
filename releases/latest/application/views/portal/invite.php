<?php $this->load->view('portal/_head'); ?>
<div class="card" style="max-width:420px;margin:40px auto;">
  <h3><?= $kind === 'proxy' ? 'Caregiver access' : 'Patient portal' ?> — set your password</h3>
  <?php if(!empty($error)): ?><div class="err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <form method="post" action="<?= site_url('portal/invite/'.urlencode($token)) ?>">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
    <label>New password (8+ characters)</label>
    <input name="password" type="password" required minlength="8" autocomplete="new-password">
    <div style="margin-top:14px"><button class="btn" style="width:100%">Activate access</button></div>
  </form>
</div>
<?php $this->load->view('portal/_foot'); ?>
