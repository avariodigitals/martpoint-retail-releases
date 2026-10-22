<form class="garden-card" method="post" action="<?= base_url('garden/save_plant'); ?>">
<?php $this->load->view('garden/form_token'); ?><input type="hidden" name="item_id" value="<?= (int)$garden_item->id; ?>">
<h2><?= htmlspecialchars($garden_item->item_name); ?></h2>
<label><input type="checkbox" name="is_living" value="1" <?= $plant_attributes !== null ? 'checked' : ''; ?>> This is a living product</label>
<p class="garden-muted">Attributes are optional and do not create variants. Clearing the checkbox removes only plant attributes.</p>
<div class="garden-grid"><?php foreach ($garden_fields as $key=>$label): ?><label><?= htmlspecialchars($label); ?><textarea rows="2" maxlength="4000" name="attributes[<?= $key; ?>]"><?= htmlspecialchars($plant_attributes[$key] ?? ''); ?></textarea></label><?php endforeach; ?></div>
<button class="garden-button">Save Attributes</button></form>
