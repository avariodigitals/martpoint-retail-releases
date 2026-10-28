<?php $this->load->view('messaging/_styles'); $h=function($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}; ?>
<div class="msg-wrap"><section class="msg-card">
<h1>Finish Central messaging setup</h1>
<p>The application files are installed, but the messaging database is not ready. Paid sends and credit purchases remain unavailable until setup completes.</p>
<?php if (!empty($setup_message) || !empty($schema_status['message'])): ?><p class="msg-alert" role="alert"><?= $h($setup_message ?: $schema_status['message']); ?></p><?php endif; ?>
<?php if (!empty($schema_status['missing_tables'])): ?><p class="msg-hint">Missing messaging tables: <?= $h(implode(', ', $schema_status['missing_tables'])); ?></p><?php endif; ?>
<?php if (!empty($schema_status['missing_columns'])): ?><p class="msg-alert">An existing messaging table is incomplete. Ask support to review its schema. This tool will not rewrite an existing ledger.</p><?php endif; ?>
<?php if (!empty($schema_status['can_repair'])): ?>
<p>Back up Central first. This applies the additive .208 messaging table setup only. It does not delete records, reset balances, enable paid channels, change provider keys or mark unrelated migrations complete.</p>
<?= form_open('messaging_admin/setup'); ?><button class="msg-btn" type="submit">Finish messaging database setup</button><?= form_close(); ?>
<?php endif; ?>
<p><a href="<?= base_url('messaging_admin'); ?>">Check setup again</a> · <a href="<?= base_url('fleet'); ?>">Fleet Manager</a></p>
</section></div>
