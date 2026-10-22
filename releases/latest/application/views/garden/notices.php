<?php if ($msg = $this->session->flashdata('garden_success')): ?><p class="garden-success" role="status"><?= htmlspecialchars($msg); ?></p><?php endif; ?>
<?php if ($msg = $this->session->flashdata('garden_error')): ?><p class="garden-error" role="alert"><?= htmlspecialchars($msg); ?></p><?php endif; ?>
