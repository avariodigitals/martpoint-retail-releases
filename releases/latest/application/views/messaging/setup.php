<?php $this->load->view('messaging/_styles'); $h=function($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}; ?>
<div class="msg-wrap"><section class="msg-card">
<h1>Finish Central messaging setup</h1>
<p>The application files are installed, but the messaging database is not ready. Paid sends and credit purchases remain unavailable until setup completes.</p>
<?php if (!empty($setup_message) || !empty($schema_status['message'])): ?><p class="msg-alert" role="alert"><?= $h($setup_message ?: $schema_status['message']); ?></p><?php endif; ?>
<?php if (!empty($schema_status['missing_tables'])): ?><p class="msg-hint">Missing messaging tables: <?= $h(implode(', ', $schema_status['missing_tables'])); ?></p><?php endif; ?>
<?php if (!empty($schema_status['missing_columns'])): ?><p class="msg-alert">An existing messaging table is incomplete. Ask support to review its schema. This tool will not rewrite an existing ledger.</p><p class="msg-hint">Missing columns: <?= $h(implode(', ',$schema_status['missing_columns'])); ?></p><?php endif; ?>
<p class="msg-hint">Customer inbox messages are kept separate from credit-billed dispatches. Setup does not rename, delete or alter the existing inbox table.</p>
<?php if (!empty($schema_status['legacy_import_pending'])): ?><p class="msg-alert"><?= (int)$schema_status['legacy_message_count']; ?> older billing-history records need a guarded copy into the separate dispatch table. Setup preserves their IDs and the original history, and refuses conflicting records. Account balances, purchases and ledger entries are not changed.</p><?php endif; ?>
<?php if (!empty($schema_status['can_repair'])): ?>
<p>Back up Central first. This applies the additive messaging table setup and, where required, the guarded billing-history copy. It does not delete records, reset balances, enable paid channels, change provider keys or mark unrelated migrations complete.</p>
<p class="msg-hint">Setup now runs one short checkpoint at a time. If a request is interrupted, Resume continues without recreating completed tables or changing balances.</p>
<?= form_open('messaging_admin/setup',array('id'=>'messaging-setup-form')); ?><button class="msg-btn" id="messaging-setup-button" type="submit">Resume messaging setup</button><p id="messaging-setup-progress" role="status" aria-live="polite" class="msg-hint"></p><?= form_close(); ?>
<script src="<?= $h(base_url('theme/js/messaging_setup.js?v='.app_version())); ?>"></script>
<?php endif; ?>
<p><a href="<?= base_url('messaging_admin'); ?>">Check setup again</a> · <a href="<?= base_url('fleet'); ?>">Fleet Manager</a></p>
</section></div>
