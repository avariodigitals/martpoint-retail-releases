<?php $links=messaging_workspace_menu_items();if($links): ?>
<div class="mp-nav-section"><div class="mp-nav-label">Communications</div>
<?php foreach($links as $link): ?>
<a href="<?= base_url($link['url_desktop']); ?>" class="mp-nav-item"><span class="mp-nav-icon"><i class="fa <?= htmlspecialchars($link['icon'],ENT_QUOTES,'UTF-8'); ?>"></i></span> <?= htmlspecialchars($link['title'],ENT_QUOTES,'UTF-8'); ?></a>
<?php endforeach; ?></div><?php endif; ?>
