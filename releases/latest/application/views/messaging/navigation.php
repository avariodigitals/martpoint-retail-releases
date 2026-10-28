<?php $messagingLinks = messaging_workspace_menu_items(); ?>
<?php if ($messagingLinks): ?>
<div class="mp-nav-section"><div class="mp-nav-label">Messaging &amp; credits</div>
<?php foreach ($messagingLinks as $messagingLink): ?>
<a href="<?= base_url($messagingLink['url_desktop']); ?>" class="mp-nav-item"><span class="mp-nav-icon"><i class="fa <?= htmlspecialchars($messagingLink['icon'], ENT_QUOTES, 'UTF-8'); ?>"></i></span> <?= htmlspecialchars($messagingLink['title'], ENT_QUOTES, 'UTF-8'); ?></a>
<?php endforeach; ?>
</div>
<?php endif; ?>
