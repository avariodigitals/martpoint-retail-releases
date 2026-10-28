<?php $h=function($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}; ?>
<section class="msg-card"><h2>Messaging connection needs attention</h2>
<p class="msg-alert" role="status"><?= $h($m['message'] ?? 'MartPoint Central could not confirm your messaging account.'); ?></p>
<div class="msg-grid"><div><h3>Balance not confirmed</h3><p>Your balance, payments and usage cannot be verified right now. This does not mean your balance is zero. Credit purchases and sending controls are unavailable until Central responds.</p></div>
<div><h3>What to check</h3><ol><li>Ask your administrator to confirm this installation is connected to MartPoint Central.</li><li>On Central, finish the messaging database setup and configure the provider accounts.</li><li>Reload this page once Central is ready. Do not repeat a payment or campaign send without checking its history.</li></ol></div></div>
<a class="msg-btn" href="<?= base_url('messaging').(!empty($messaging_mobile)?'?screen=mobile':''); ?>">Check connection again</a>
</section>
