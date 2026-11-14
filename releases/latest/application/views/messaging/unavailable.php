<?php require_once APPPATH.'libraries/Communication_copy.php';  $h=function($v){return htmlspecialchars((string)$v,ENT_QUOTES,'UTF-8');}; ?>
<section class="msg-card"><h2>Messaging connection needs attention</h2>
<p class="msg-alert" role="status"><?= $h(Communication_copy::message($m['message'] ?? 'Your messaging account is temporarily unavailable. Contact your Administrator.')); ?></p>
<div class="msg-grid"><div><h3>Balance not confirmed</h3><p>Your balance, payments and usage cannot be verified right now. This does not mean your balance is zero. Credit purchases and sending are temporarily unavailable.</p></div>
<div><h3>What to check</h3><ol><li>Ask your Administrator to check your messaging account.</li><li>Your Administrator can enable messaging and confirm your account settings.</li><li>Reload this page after your Administrator confirms the account is ready. Do not repeat a payment or campaign send without checking its history.</li></ol></div></div>
<a class="msg-btn" href="<?= base_url('messaging').(!empty($messaging_mobile)?'?screen=mobile':''); ?>">Check connection again</a>
</section>
