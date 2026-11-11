<!DOCTYPE html><html lang="en"><head>
<meta charset="utf-8">
<?php $this->load->view('comman/app_icons'); ?>
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?> — <?= htmlspecialchars($page_title); ?></title>
<link rel="stylesheet" href="<?= $theme_link; ?>css/font-awesome-4.7.0/css/font-awesome.min.css">
<style>
*{box-sizing:border-box}body{margin:0;background:#f5f7fa;color:#182637;font:14px/1.6 system-ui,sans-serif}.branch-header{padding:18px;background:#fff;border-bottom:1px solid #dde3eb}.store-name{font-size:12px;color:#64748b}h1{font-size:22px;margin:0}main{padding:18px 16px 160px;max-width:760px;margin:auto}.branch-card{background:#fff;border:1px solid #dde3eb;border-radius:12px;padding:18px;margin:14px 0}.branch-card h2{font-size:17px;margin:0 0 8px}.branch-card label{display:block;margin:14px 0}.branch-card input,.branch-card textarea{display:block;width:100%;border:1px solid #cbd5e1;border-radius:8px;font:inherit;font-size:16px;padding:11px}.branch-button{display:inline-block;min-height:44px;padding:10px 16px;background:#0057ff;color:#fff;border:0;border-radius:8px;text-decoration:none;font:inherit;cursor:pointer}.branch-muted{color:#64748b}.branch-error{color:#a03228}.branch-button:focus-visible,input:focus-visible,textarea:focus-visible{outline:3px solid #7da6ff;outline-offset:2px}
.mp-select{display:none!important}.mp-select-wrap{position:relative}.mp-select-trigger{display:flex;justify-content:space-between;align-items:center;padding:11px;border:1px solid #cbd5e1;border-radius:8px;background:#fff;cursor:pointer}.mp-select-options{display:none;max-height:220px;overflow-y:auto;border:1px solid #cbd5e1;border-radius:8px;background:#fff;margin-top:4px}.mp-select-options.open{display:block}.mp-select-option{padding:11px;border-bottom:1px solid #e2e8f0;cursor:pointer}.mp-select-option.active{background:#eff6ff;color:#0057ff}.branch-check{display:flex!important;align-items:center;gap:10px}.branch-check input{width:18px;height:18px;display:inline-block}
</style></head><body>
<header class="branch-header"><div class="store-name"><?= htmlspecialchars($store_name ?? $SITE_TITLE ?? 'MartPoint'); ?></div><h1><?= htmlspecialchars($page_title); ?></h1></header>
<main>
<?php if ($branch_form): ?>
<a href="<?= base_url('warehouse?screen=mobile'); ?>">← Branches</a>
<form class="branch-card" id="branch-form" action="<?= base_url('warehouse/save_or_update'); ?>" method="post">
<input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
<input type="hidden" name="command" value="<?= htmlspecialchars($command); ?>"><input type="hidden" name="q_id" value="<?= (int)($q_id ?? 0); ?>">
<p class="branch-muted">The business name and logo stay shared. Documents use the branch recorded on each transaction.</p>
<?php foreach (['warehouse_name'=>'Branch name','address'=>'Branch address','city'=>'City','state'=>'State / region','country'=>'Country','postcode'=>'Postcode','mobile'=>'Branch phone','email'=>'Branch email'] as $field=>$label): ?>
<label for="branch-<?= $field; ?>"><?= $label; ?><?= $field==='warehouse_name'?' *':''; ?>
<?php if ($field==='address'): ?><textarea id="branch-<?= $field; ?>" name="<?= $field; ?>" rows="3" maxlength="500"><?= htmlspecialchars($$field ?? '',ENT_QUOTES,'UTF-8'); ?></textarea>
<?php else: ?><input id="branch-<?= $field; ?>" name="<?= $field; ?>" value="<?= htmlspecialchars($$field ?? '',ENT_QUOTES,'UTF-8'); ?>" type="<?= $field==='email'?'email':($field==='mobile'?'tel':'text'); ?>" maxlength="<?= $field==='postcode'?30:($field==='warehouse_name'?200:100); ?>" <?= $field==='warehouse_name'?'required':''; ?>><?php endif; ?></label>
<?php endforeach; ?>
<label for="branch-type">Branch / fulfilment type
<select class="mp-select" id="branch-type" name="branch_type">
<?php foreach (['branch'=>'Branch','depot'=>'Depot','hub'=>'Distribution hub','store'=>'Retail store','partner'=>'Partner fulfilment hub','pickup_hub'=>'Pickup-only hub','outlet'=>'Outlet'] as $value=>$label): ?><option value="<?= htmlspecialchars($value); ?>" <?= (($branch_type ?? 'branch')===$value)?'selected':''; ?>><?= htmlspecialchars($label); ?></option><?php endforeach; ?>
</select></label>
<label for="stock-source">Stock / preparation source
<select class="mp-select" id="stock-source" name="stock_source_id">
<?php foreach (($source_location_options ?? [''=>'Use this branch’s own stock']) as $value=>$label): ?><option value="<?= htmlspecialchars((string)$value); ?>" <?= ((string)($stock_source_id ?? '')===(string)$value)?'selected':''; ?>><?= htmlspecialchars($label); ?></option><?php endforeach; ?>
</select><span class="branch-muted">Pickup-only hubs need a source branch that prepares the order.</span></label>
<input type="hidden" name="pickup_enabled" value="0"><label class="branch-check"><input type="checkbox" name="pickup_enabled" value="1" <?= !isset($pickup_enabled)||!empty($pickup_enabled)?'checked':''; ?>> Offer customer pickup here</label>
<input type="hidden" name="public_enabled" value="0"><label class="branch-check"><input type="checkbox" name="public_enabled" value="1" <?= !isset($public_enabled)||!empty($public_enabled)?'checked':''; ?>> Show this location in the online store</label>
<label for="delivery-fee">Location delivery fee<input id="delivery-fee" name="delivery_fee" type="number" min="0" step="0.01" value="<?= htmlspecialchars((string)($delivery_fee ?? '0')); ?>"></label>
<label for="business-hours">Business hours<input id="business-hours" name="business_hours" value="<?= htmlspecialchars($business_hours ?? '',ENT_QUOTES,'UTF-8'); ?>" maxlength="255"></label>
<label for="delivery-coverage">Delivery coverage / customer note<input id="delivery-coverage" name="delivery_coverage" value="<?= htmlspecialchars($delivery_coverage ?? '',ENT_QUOTES,'UTF-8'); ?>" maxlength="500"></label>
<p class="branch-muted">Partner hubs may hold their own stock. Pickup-only hubs do not hold stock and must link to a preparation source.</p>
<p id="branch-result" class="branch-error" role="status" aria-live="polite"></p><button class="branch-button" type="submit">Save branch</button></form>
<script>
document.querySelectorAll('select.mp-select').forEach(function(sel){var wrap=document.createElement('div');wrap.className='mp-select-wrap';sel.parentNode.insertBefore(wrap,sel);wrap.appendChild(sel);var trigger=document.createElement('button');trigger.type='button';trigger.className='mp-select-trigger';var label=document.createElement('span');var icon=document.createElement('span');icon.textContent='⌄';trigger.append(label,icon);var opts=document.createElement('div');opts.className='mp-select-options';Array.from(sel.options).forEach(function(option,index){var row=document.createElement('div');row.className='mp-select-option';row.textContent=option.text;if(index===sel.selectedIndex)row.classList.add('active');row.addEventListener('click',function(){sel.selectedIndex=index;label.textContent=option.text;opts.classList.remove('open');opts.querySelectorAll('.mp-select-option').forEach(function(item){item.classList.remove('active')});row.classList.add('active');sel.dispatchEvent(new Event('change',{bubbles:true}))});opts.appendChild(row)});label.textContent=sel.options[sel.selectedIndex]?sel.options[sel.selectedIndex].text:'Select';trigger.addEventListener('click',function(){opts.classList.toggle('open')});wrap.append(trigger,opts)});
document.getElementById('branch-form').addEventListener('submit',function(e){e.preventDefault();var form=this,button=form.querySelector('button[type=submit]'),message=document.getElementById('branch-result');button.disabled=true;message.textContent='';fetch(form.action,{method:'POST',body:new FormData(form),credentials:'same-origin'}).then(function(r){if(!r.ok)throw new Error('Unable to save branch. Please try again.');return r.text();}).then(function(text){if(text.trim()==='success')location.href=<?= json_encode(base_url('warehouse?screen=mobile')); ?>;else message.textContent=text;}).catch(function(error){message.textContent=error.message;}).finally(function(){button.disabled=false;});});
</script>
<?php else: ?>
<?php if (get_instance()->permissions('warehouse_add')): ?><a class="branch-button" href="<?= base_url('warehouse/add?screen=mobile'); ?>">+ Add branch</a><?php endif; ?>
<?php foreach ($rows as $row): $type=(string)($row->branch_type ?? 'branch'); $typeLabels=['partner'=>'Partner fulfilment hub','pickup_hub'=>'Pickup-only hub','hub'=>'Distribution hub','depot'=>'Depot','store'=>'Retail store','outlet'=>'Outlet','branch'=>'Branch']; ?><article class="branch-card"><h2><?= htmlspecialchars($row->warehouse_name); ?></h2><p><strong><?= htmlspecialchars($typeLabels[$type] ?? ucfirst(str_replace('_',' ',$type))); ?></strong></p><p><?= htmlspecialchars(trim(implode(', ',array_filter([$row->address ?? '',$row->city ?? '',$row->state ?? '',$row->country ?? '',$row->postcode ?? '']))) ?: 'Address not configured'); ?></p><p class="branch-muted"><?= htmlspecialchars($row->mobile ?? ''); ?> <?= htmlspecialchars($row->email ?? ''); ?> · <?= $row->status?'Active':'Inactive'; ?></p><?php if (get_instance()->permissions('warehouse_edit')): ?><a class="branch-button" href="<?= base_url('warehouse/edit/'.(int)$row->id.'?screen=mobile'); ?>">Edit branch</a><?php endif; ?></article><?php endforeach; ?>
<?php if (!$rows): ?><p>No branches have been added yet.</p><?php endif; ?>
<?php endif; ?>
</main>
<?php $this->load->view('warehouse/screen_mode'); ?>
<?php $this->load->view('mobile/bottom_nav',['active'=>'more']); ?>
<?php $this->load->view('mobile/chat'); ?>
</body></html>
