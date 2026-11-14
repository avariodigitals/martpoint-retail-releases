<?php
$this->load->view('mobile/gas_head', [
	'gas_title' => 'Pumps',
	'gas_sub' => 'Every dispenser a duty has to read at this ' . strtolower($site_label),
	'store_name' => $store_name ?? ($SITE_TITLE ?? 'MartPoint'),
	'theme_link' => $theme_link,
]);
$this->load->view('mobile/gas_sites', ['sites'=>$sites, 'site_id'=>$site_id, 'gas_route'=>'pumps']);

$editing = !empty($edit);
?>

<?php if(!$can_edit): ?>
  <div class='callout info'>Registering a pump changes what a duty measures, so it needs the location-edit permission.</div>
<?php endif; ?>

<div class='card'>
  <div class='section-title'>Pumps here (<?= count($pumps); ?>)</div>
  <?php if(empty($pumps)): ?>
    <div class='empty'><i class='fa fa-tachometer'></i>No pump is registered here yet, so a duty cannot be opened.</div>
  <?php else: ?>
    <?php foreach($pumps as $pump): ?>
    <div class='row'>
      <div class='name'><?= htmlspecialchars($pump->pump_name); ?></div>
      <div class='meta'>
        Draws from <?= htmlspecialchars((string)$pump->tank_name ?: 'no tank'); ?>
        · <?= $pump->item_id ? htmlspecialchars(get_item_name($pump->item_id)) : '—' ?>
      </div>
      <div class='meta'>Meter stands at <span class='litre'><?= mp_format_litres(isset($meters[(int)$pump->id]) ? $meters[(int)$pump->id] : $pump->opening_meter); ?></span> L</div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php if($can_edit): ?>
<div class='card'>
  <div class='section-title'><?= $editing ? 'Change this pump' : 'Add a pump' ?></div>
  <?php if(empty($tanks)): ?>
    <p class='note' style='margin-top:0;'>Add the tank this pump draws from first &mdash; a pump without a tank cannot be reconciled.</p>
  <?php else: ?>
  <?= form_open(base_url('oil_gas/save_pump')); ?>
  <input type='hidden' name='site_id' value='<?= (int)$site_id; ?>'>
  <input type='hidden' name='pump_id' value='<?= $editing ? (int)$edit->id : 0; ?>'>
  <input type='hidden' name='warehouse_id' value='<?= (int)$site_id; ?>'>
  <div class='form-group'>
    <label>Pump name <span class='req'>*</span></label>
    <input type='text' name='pump_name' required maxlength='120' value='<?= htmlspecialchars($editing ? $edit->pump_name : 'Pump 1'); ?>'>
  </div>
  <div class='form-group'>
    <label>Draws from <span class='req'>*</span></label>
    <select class='mp-select' name='tank_id' required>
      <?php foreach($tanks as $tank): $pos = isset($positions[(int)$tank->id]) ? $positions[(int)$tank->id] : array('book'=>0.0); ?>
      <option value='<?= (int)$tank->id; ?>' <?= ($editing && (int)$edit->tank_id === (int)$tank->id) ? 'selected' : ''; ?>>
        <?= htmlspecialchars($tank->tank_name); ?> — <?= mp_format_litres($pos['book']); ?> L in it
      </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class='form-group'>
    <label>Gives out</label>
    <select class='mp-select' name='item_id'>
      <option value='0'>Same as its tank</option>
      <?php foreach($items as $item): ?>
      <option value='<?= (int)$item->id; ?>' <?= ($editing && (int)$edit->item_id === (int)$item->id) ? 'selected' : ''; ?>><?= htmlspecialchars($item->name); ?></option>
      <?php endforeach; ?>
    </select>
    <span class='hint'>It has to match the tank, or the litres leave the wrong product.</span>
  </div>
  <div class='form-group'>
    <label>Meter face today (litres)</label>
    <input type='number' step='0.001' min='0' name='opening_meter' value='<?= $editing ? mp_format_litres($edit->opening_meter) : ''; ?>'>
    <span class='hint'>A meter may only be set above a reading this pump has already reported.</span>
  </div>
  <button type='submit' class='btn btn-primary'><i class='fa fa-check'></i> Save pump</button>
  <?= form_close(); ?>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php $this->load->view('mobile/gas_foot', ['gas_nav'=>'more']); ?>
