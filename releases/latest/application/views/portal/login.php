<?php $this->load->view('portal/_head'); ?>
<div class="card" style="max-width:420px;margin:40px auto;">
  <h3>Sign in</h3>
  <?php if(!empty($error)): ?><div class="err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if(!empty($ok)): ?><div class="flash"><?= htmlspecialchars($ok) ?></div><?php endif; ?>
  <form method="post" action="<?= site_url('portal/login') ?>">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name() ?>" value="<?= $this->security->get_csrf_hash() ?>">
    <label>Email or phone</label>
    <input name="identity" required autocomplete="username">
    <label>Password</label>
    <input name="password" type="password" required autocomplete="current-password">
    <div style="margin-top:14px"><button class="btn" style="width:100%">Sign in</button></div>
  </form>
  <p class="muted" style="margin-top:12px">First time? Use the invitation link the clinic sent you.</p>
</div>
<?php $this->load->view('portal/_foot'); ?>
