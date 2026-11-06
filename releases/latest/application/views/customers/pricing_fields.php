<?php if (mp_customer_pricing()->installed()): ?>
<div class="form-group mp-form-group">
<label for="pricing_group_id">Customer pricing group</label>
<select name="pricing_group_id" id="pricing_group_id" class="form-control">
<option value="">No group</option>
<?php foreach (mp_customer_pricing()->groups(get_current_store_id()) as $pg): ?>
<option value="<?= (int)$pg->id; ?>" <?= (int)($pricing_group_id ?? 0)===(int)$pg->id?'selected':''; ?>><?= htmlspecialchars($pg->name . (!$pg->active ? ' (inactive)' : '') . ' · Wholesale ' . ($pg->direction==='Decrease'?'-':'+') . (float)$pg->percent . '%', ENT_QUOTES, 'UTF-8'); ?></option>
<?php endforeach; ?>
</select>
</div>
<div class="form-group mp-form-group">
<label for="pricing_rule_mode">Customer price rule</label>
<select name="pricing_rule_mode" id="pricing_rule_mode" class="form-control">
<option value="inherit" <?= ($pricing_rule_mode ?? ((float)($price_level ?? 0)!=0?'custom':'inherit'))==='inherit'?'selected':''; ?>>Use assigned group (otherwise normal prices)</option>
<option value="custom" <?= ($pricing_rule_mode ?? ((float)($price_level ?? 0)!=0?'custom':'inherit'))==='custom'?'selected':''; ?>>Use individual percentage below</option>
</select>
<small>Group prices use Wholesale, even when automatic Retail markup is off. Individual percentages override the group. <a href="<?= base_url('customer_groups'); ?>">Manage groups</a></small>
</div>
<?php endif; ?>
