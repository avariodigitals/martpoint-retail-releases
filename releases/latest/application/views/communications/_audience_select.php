<?php
$options=$audience_options[$audience_channel]??array();$selected=$selected_segment??'all';$group='';
?>
<label>Audience source<select name="segment_key" required data-audience-picker>
<?php if($options):foreach($options as $audienceKey=>$audience):if($group!==$audience['group']):if($group!==''): ?></optgroup><?php endif;$group=$audience['group']; ?><optgroup label="<?= $h($group); ?>"><?php endif; ?><option value="<?= $h($audienceKey); ?>" <?= $selected===$audienceKey?'selected':''; ?>><?= $h($audience['name']); ?> · <?= number_format($audience['eligible']); ?> eligible</option><?php endforeach; ?></optgroup><?php else:foreach($segments as $audienceKey=>$definition): ?><option value="<?= $h($audienceKey); ?>" <?= $selected===$audienceKey?'selected':''; ?>><?= $h($definition[0]); ?></option><?php endforeach;endif; ?>
</select></label><p class="muted">Counts include valid <?= $audience_channel==='sms'?'Nigerian mobile numbers':'email addresses'; ?> after duplicates and opt-outs are removed. Staff and suppliers are separate audiences.</p>
