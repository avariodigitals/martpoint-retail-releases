<?php $this->load->view('garden/styles'); ?>
<div class="garden-workspace<?= !empty($garden_overview) ? ' garden-workspace-overview' : ''; ?>">
<?php if (empty($garden_overview)): ?><div class="garden-page-head"><div class="garden-title-group"><p class="garden-eyebrow">GARDEN CENTRE &amp; LANDSCAPING</p><h1><?= htmlspecialchars($page_title); ?></h1></div><span class="garden-date"><?= date('l, j F Y'); ?></span></div><?php endif; ?>
<?php $this->load->view('garden/notices'); ?>
<?= $garden_content; ?></div>
<?php $this->load->view('garden/scripts',['mobile'=>false]); ?>
