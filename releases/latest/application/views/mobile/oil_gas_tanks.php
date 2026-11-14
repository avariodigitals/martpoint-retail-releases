<?php
$this->load->view('mobile/gas_head', [
	'gas_title' => 'Tanks',
	'gas_sub' => 'What each tank at this ' . strtolower($site_label) . ' holds, and the dip that proves it',
	'store_name' => $store_name ?? ($SITE_TITLE ?? 'MartPoint'),
	'theme_link' => $theme_link,
]);
$this->load->view('mobile/gas_sites', ['sites'=>$sites, 'site_id'=>$site_id, 'gas_route'=>'tanks']);

$editing = !empty($edit);
$dip_tank = $this->input->get('dip') ? (int)$this->input->get('dip', TRUE) : 0;
?>

<?php if(!$can_edit): ?>
  <div class='callout info'>You can read the tanks here. Registering or changing one needs the location-edit permission.</div>
<?php endif; ?>

<div class='card'>
  <div class='section-title'>Tanks here (<?= count($tanks); ?>)</div>
  <?php if(empty($tanks)): ?>
    <div class='empty'><i class='fa fa-database'></i>No tanks yet. Add the ground tank as it is built.</div>
  <?php else: ?>
    <?php foreach($tanks as $tank): $pos = isset($positions[(int)$tank->id]) ? $positions[(int)$tank->id] : array('book'=>0.0,'dip'=>null,'gap'=>null); ?>
    <div class='row'>
      <div class='name'><?= htmlspecialchars($tank->tank_name); ?></div>
      <div class='meta'>
        <?= $tank->item_id ? htmlspecialchars(get_item_name($tank->item_id)) : 'Any product' ?>
        · holds <?= mp_format_litres($tank->capacity_litres); ?> L
      </div>
      <div class='meta'><span class='litre'><?= mp_format_litres($pos['book']); ?></span> L in the book · fill no higher than <?= mp_format_litres($tank->safe_fill_litres); ?> L</div>
      <?php if($pos['gap'] !== null && abs($pos['gap']) >= 0.0001): ?>
        <span class='flag <?= $pos['gap'] < 0 ? 'held' : 'transit'; ?>'><?= ($pos['gap'] > 0 ? '+' : '') . mp_format_litres($pos['gap']); ?> L against the book</span>
      <?php endif; ?>
      <div class='actions' style='margin-top:10px;'>
        <a class='action' href='<?= base_url('oil_gas/fill?tank='.(int)$tank->id); ?>'><i class='fa fa-arrow-down'></i> Fill</a>
        <?php if($can_edit): ?>
        <a class='action' href='<?= base_url('oil_gas/tanks?site='.(int)$site_id.'&dip='.(int)$tank->id); ?>'><i class='fa fa-tint'></i> Dip</a>
        <?php endif; ?>
      </div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php if($can_edit && !empty($tanks) && $dip_tank > 0): ?>
<?php foreach($tanks as $tank): if((int)$tank->id !== $dip_tank) continue; $pos = isset($positions[(int)$tank->id]) ? $positions[(int)$tank->id] : array('book'=>0.0); ?>
<div class='card'>
  <div class='section-title'>Dip <?= htmlspecialchars($tank->tank_name); ?></div>
  <p class='note' style='margin-top:0;'>A dip is evidence, not a booking. Inside <?= mp_format_percent(mp_forecourt_tank_variance_percent(get_current_store_id())); ?> of the tank the book is corrected at once; past that a manager has to sign.</p>
  <?= form_open(base_url('oil_gas/save_dip')); ?>
  <input type='hidden' name='site_id' value='<?= (int)$site_id; ?>'>
  <input type='hidden' name='tank_id' value='<?= (int)$tank->id; ?>'>
  <div class='form-group'>
    <label>Dipped litres <span class='req'>*</span> <span style='font-weight:400;color:var(--mp-muted);'>(book says <?= mp_format_litres($pos['book']); ?> L)</span></label>
    <input type='number' step='0.001' min='0' name='level_litres' required placeholder='What the dip tape or gauge says'>
  </div>
  <div class='form-group'>
    <label>Note</label>
    <input type='text' name='note' maxlength='255' placeholder='Water past 40mm, temperature 31°C'>
  </div>
  <button type='submit' class='btn btn-primary'><i class='fa fa-tint'></i> Record this dip</button>
  <?= form_close(); ?>
</div>
<?php endforeach; ?>
<?php endif; ?>

<?php if($can_edit): ?>
<div class='card'>
  <div class='section-title'><?= $editing ? 'Change this tank' : 'Add a tank' ?></div>
  <?= form_open(base_url('oil_gas/save_tank')); ?>
  <input type='hidden' name='site_id' value='<?= (int)$site_id; ?>'>
  <input type='hidden' name='tank_id' value='<?= $editing ? (int)$edit->id : 0; ?>'>
  <input type='hidden' name='warehouse_id' value='<?= (int)$site_id; ?>'>
  <div class='form-group'>
    <label>Tank name <span class='req'>*</span></label>
    <input type='text' name='tank_name' required maxlength='120' value='<?= htmlspecialchars($editing ? $edit->tank_name : 'Tank 1 — Petrol'); ?>'>
  </div>
  <div class='form-row'>
    <div class='form-group'>
      <label>Capacity in litres <span class='req'>*</span></label>
      <input type='number' step='0.001' min='1' name='capacity_litres' required value='<?= $editing ? mp_format_litres($edit->capacity_litres) : ''; ?>'>
    </div>
    <div class='form-group'>
      <label>Fill no higher than</label>
      <input type='number' step='0.001' min='0' name='safe_fill_litres' value='<?= $editing ? mp_format_litres($edit->safe_fill_litres) : ''; ?>'>
      <span class='hint'>Blank means 95% of capacity.</span>
    </div>
  </div>
  <div class='form-group'>
    <label>Holds</label>
    <select class='mp-select' name='item_id'>
      <option value='0'>Any product</option>
      <?php foreach($items as $item): ?>
      <option value='<?= (int)$item->id; ?>' <?= ($editing && (int)$edit->item_id === (int)$item->id) ? 'selected' : ''; ?>><?= htmlspecialchars($item->name); ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class='form-group'>
    <label>Note</label>
    <input type='text' name='note' maxlength='255' value='<?= htmlspecialchars($editing ? (string)$edit->note : ''); ?>'>
  </div>
  <button type='submit' class='btn btn-primary'><i class='fa fa-check'></i> Save tank</button>
  <?= form_close(); ?>
</div>
<?php endif; ?>

<?php $this->load->view('mobile/gas_foot', ['gas_nav'=>'more']); ?>
