<?php
$identity=$sender_identity??array('name'=>'','reply_to'=>'','saved_name'=>'','saved_reply'=>'','pinned_name'=>'','pinned_reply_to'=>'','can_edit'=>false);
$shownName=$identity['pinned_name']!==''?$identity['pinned_name']:$identity['name'];
$shownReply=$identity['pinned_reply_to']!==''?$identity['pinned_reply_to']:$identity['reply_to'];
?>
<section class="comm-card">
<h2>Who your emails come from</h2>
<div class="comm-split">
<div><span class="muted">Name in the inbox</span><div class="metric" style="font-size:20px"><?= $h($shownName?:$store_name); ?></div></div>
<div><span class="muted">Replies arrive at</span><div class="metric" style="font-size:20px"><?= $h($shownReply?:'Your business email'); ?></div></div>
</div>
<p class="muted" style="margin-top:16px">Your emails always leave through MartPoint’s verified sending address, which keeps them out of spam folders. You choose the name customers see and where their replies land.</p>
<?php if($identity['pinned_name']!=='' || $identity['pinned_reply_to']!==''): ?>
<div class="notice">Your Administrator has fixed <?= $identity['pinned_name']!==''?'the name customers see as “'.$h($identity['pinned_name']).'”':''; ?><?= ($identity['pinned_name']!=='' && $identity['pinned_reply_to']!=='')?' and':''; ?><?= $identity['pinned_reply_to']!==''?' the reply address as '.$h($identity['pinned_reply_to']):''; ?>. Your own details below are kept and used again as soon as they are released.</div>
<?php endif; ?>
</section>
<?php if($identity['can_edit']): ?>
<section class="comm-card">
<h2><?= ($identity['saved_name']!=='' || $identity['saved_reply']!=='')?'Change your sender details':'Set your sender details' ?></h2>
<?= form_open('communications/sender_identity'); ?>
<input type="hidden" name="screen" value="<?= $mobile?'mobile':'desktop'; ?>">
<label>Name customers see<input name="sender_name" maxlength="90" value="<?= $h($identity['saved_name']!==''?$identity['saved_name']:$identity['name']); ?>" placeholder="<?= $h($store_name); ?>" required></label>
<p class="muted">The name in the inbox list, not an email address. Leave it as your store name if you are unsure.</p>
<label>Replies arrive at<input name="sender_reply_to" type="email" maxlength="190" value="<?= $h($identity['saved_reply']); ?>" placeholder="<?= $h($identity['reply_to']?:'your business email'); ?>"></label>
<p class="muted">Leave empty for replies to reach the email address on your business profile.</p>
<button class="btn" style="margin-top:16px">Save sender details</button>
<?= form_close(); ?>
</section>
<?php else: ?>
<div class="notice">Only someone with access to Email settings can change these details.</div>
<?php endif; ?>
