<?php $h = function($v){return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');}; ?>
<style>
.price-groups{max-width:1000px;margin:auto}.price-groups .pg-card{padding:20px;background:#fff;border:1px solid #e2e8f0;border-radius:14px;margin:16px 0}.price-groups h2{font-size:19px;margin:0 0 12px}.price-groups p,.price-groups small{color:#64748b}.price-groups label{display:block;margin:12px 0 5px}.price-groups input[type=text],.price-groups input[type=number]{width:100%;padding:10px;border:1px solid #cbd5e1;border-radius:8px;font:inherit}.price-groups .pg-options{display:flex;flex-wrap:wrap;gap:20px}.price-groups .pg-options label{display:flex;gap:8px;align-items:center}.price-groups button{background:var(--mp-primary,#155eef);color:#fff;border:0;border-radius:8px;padding:11px 18px;font:inherit;cursor:pointer}.price-groups summary{cursor:pointer;font-weight:650}.price-groups .pg-error{color:#b91c1c}.price-groups .pg-success{color:#15803d}.price-groups .pg-overview{display:flex;flex-wrap:wrap;gap:10px;margin:14px 0}.price-groups .pg-indicator{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:999px;font-size:12px;font-weight:700;background:#eff6ff;color:#1d4ed8}.price-groups .pg-indicator.active{background:#dcfce7;color:#166534}.price-groups .pg-indicator.inactive{background:#f1f5f9;color:#475569}.price-groups .pg-summary{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}.price-groups .pg-summary-name{font-size:16px}.price-groups .pg-created-note{margin:8px 0 0;color:#166534;font-weight:600}
</style>
<div class="price-groups">
<h1 style="font-size:24px">Customer Pricing Groups</h1>
<p>Set a percentage increase or decrease on Wholesale prices. Assign a group in the customer's Advanced pricing settings. Group prices work with automatic Retail markup on or off and stay the same across Retail and Wholesale modes.</p>
<p>Individual customer overrides take precedence. Automatic product discounts and promotions do not stack on group prices. Fixed-price bundles retain their bundle price.</p>
<?php if($this->session->flashdata('failed')): ?><p role="alert" class="pg-error"><?= $h($this->session->flashdata('failed')); ?></p><?php endif; ?>
<?php if($this->session->flashdata('success')): ?><p role="status" class="pg-success"><?= $h($this->session->flashdata('success')); ?></p><?php endif; ?>
<?php if(!$pricing_installed): ?><div class="pg-card">Apply database update 4.0.9.239 to enable customer pricing groups.</div><?php else: ?>
<?php $activeGroupCount = 0; foreach ((array)$groups as $groupRow) if (!empty($groupRow->active)) $activeGroupCount++; ?>
<div class="pg-overview" aria-label="Pricing group status">
  <span class="pg-indicator"><i class="fa fa-tags" aria-hidden="true"></i> <?= count((array)$groups); ?> <?= count((array)$groups) === 1 ? 'group created' : 'groups created'; ?></span>
  <span class="pg-indicator active"><i class="fa fa-check-circle" aria-hidden="true"></i> <?= $activeGroupCount; ?> active</span>
  <span class="pg-indicator inactive"><i class="fa fa-pause-circle" aria-hidden="true"></i> <?= count((array)$groups) - $activeGroupCount; ?> inactive</span>
</div>
<?php $renderGroup = function($g) use ($h, $can_manage) { ?>
<?php if($can_manage): ?><?= form_open('customer_groups/save'); ?>
<input type="hidden" name="id" value="<?= (int)($g->id ?? 0); ?>">
<label>Group name<input name="name" type="text" required maxlength="100" value="<?= $h($g->name ?? ''); ?>" placeholder="e.g. Contractor"></label>
<div class="pg-options">
<label><input type="radio" name="direction" value="Increase" <?= ($g->direction ?? 'Increase')==='Increase'?'checked':''; ?>> Increase Wholesale price</label>
<label><input type="radio" name="direction" value="Decrease" <?= ($g->direction ?? '')==='Decrease'?'checked':''; ?>> Decrease Wholesale price</label>
</div>
<label>Percentage<input type="number" name="percent" min="0" max="10000" step="0.0001" required value="<?= $h((float)($g->percent ?? 0)); ?>"></label>
<small>A decrease can be up to 100%. For example, Wholesale ₦12,000 with a 20% increase becomes ₦14,400 before tax.</small>
<label><input type="checkbox" name="active" value="1" <?= !isset($g->active) || $g->active?'checked':''; ?>> Active</label>
<p><small>Making a group inactive restores normal pricing for its customers. Previous invoices keep their original prices.</small></p>
<button type="submit">Save group</button><?= form_close(); ?>
<?php else: ?><p>Wholesale <?= $h($g->direction); ?> <?= $h((float)$g->percent); ?>% · <?= $g->active?'Active':'Inactive'; ?></p><?php endif; ?>
<?php }; ?>
<?php if($can_manage): ?><section class="pg-card"><h2>Create a group</h2><?php $renderGroup(null); ?></section><?php endif; ?>
<?php foreach($groups as $g): ?><details class="pg-card"><summary class="pg-summary"><span class="pg-summary-name"><?= $h($g->name); ?> · Wholesale <?= $g->direction==='Decrease'?'-':'+'; ?><?= $h((float)$g->percent); ?>%</span><span class="pg-indicator <?= !empty($g->active) ? 'active' : 'inactive'; ?>"><?= !empty($g->active) ? 'Active' : 'Inactive'; ?></span></summary><?php $renderGroup($g); ?></details><?php endforeach; ?>
<?php if(!$groups): ?><p>No groups yet. Create a group, then assign it to a customer.</p><?php endif; ?>
<?php endif; ?>
</div>
