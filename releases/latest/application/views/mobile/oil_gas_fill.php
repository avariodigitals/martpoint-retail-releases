<?php
$this->load->view('mobile/gas_head', [
	'gas_title' => 'Fill a Tank',
	'gas_sub' => 'Book the litres that physically went into a ground tank',
	'store_name' => $store_name ?? ($SITE_TITLE ?? 'MartPoint'),
	'theme_link' => $theme_link,
]);

$suggested = 0;
foreach($transfer_lines as $line){
	if($tank && (int)$line->item_id === (int)$tank->item_id){
		$suggested += ($line->received_qty !== null) ? (float)$line->received_qty : (float)$line->transfer_qty;
	}
}
?>

<?php if($transfer): ?>
  <div class='callout info'>Filling from load #<?= (int)$transfer->id; ?> &mdash; <?= htmlspecialchars(get_warehouse_name($transfer->warehouse_from)); ?> to <?= htmlspecialchars(get_warehouse_name($transfer->warehouse_to)); ?><?= $transfer->receipt_status === 'Received' ? ', counted in ' . show_date($transfer->received_date) : ', still ' . strtolower(str_replace('_',' ',$transfer->receipt_status)); ?>.</div>
<?php endif; ?>

<?php if($already_filled): ?>
  <div class='callout warn'>This load is already in a tank<?= $already_filled_tank ? ' ("' . htmlspecialchars($already_filled_tank->tank_name) . '")' : ''; ?>: <?= mp_format_litres($already_filled->litres); ?> L was booked. Add to it only if a second, separate quantity arrived.</div>
<?php endif; ?>

<?php if(!$tank): ?>
<div class='card'>
  <div class='section-title'>Which tank took the load?</div>
  <?php if(empty($tanks)): ?>
    <div class='empty'><i class='fa fa-database'></i>No tanks are registered yet. Add one at the <?= htmlspecialchars(strtolower($site_label)); ?> that received the load.</div>
  <?php else: foreach($tanks as $option): $pos = isset($positions[(int)$option->id]) ? $positions[(int)$option->id] : null; $room = max(0, (float)$option->safe_fill_litres - ($pos ? $pos['book'] : 0)); ?>
    <div class='row'>
      <div class='name'><?= htmlspecialchars($option->tank_name); ?></div>
      <div class='meta'><?= htmlspecialchars(get_warehouse_name($option->warehouse_id)); ?> · <?= mp_format_litres($pos ? $pos['book'] : 0); ?> L in it · <?= mp_format_litres($room); ?> L room</div>
      <div class='actions' style='margin-top:9px;'><a class='action' href='<?= base_url('oil_gas/fill?tank='.(int)$option->id.($transfer_id ? '&transfer='.(int)$transfer_id : '')); ?>'><i class='fa fa-arrow-down'></i> Fill this one</a></div>
    </div>
  <?php endforeach; endif; ?>
</div>
<?php else: ?>
<div class='card'>
  <div class='section-title'><?= htmlspecialchars($tank->tank_name); ?></div>
  <?php if(!$can_edit): ?>
    <p class='note' style='margin-top:0;'>Booking a fill changes what this <?= htmlspecialchars(strtolower($site_label)); ?> is holding, so it needs the location-edit permission.</p>
  <?php else: ?>
  <p class='note' style='margin-top:0;'>Holds <?= mp_format_litres($tank->capacity_litres); ?> L and may be filled no higher than <?= mp_format_litres($tank->safe_fill_litres); ?> L. It has <?= mp_format_litres(max(0, (float)$tank->safe_fill_litres - (float)$book)); ?> L of room.</p>
  <?= form_open(base_url('oil_gas/save_fill')); ?>
  <input type='hidden' name='site_id' value='<?= (int)$tank->warehouse_id; ?>'>
  <input type='hidden' name='tank_id' value='<?= (int)$tank->id; ?>'>
  <input type='hidden' name='transfer_id' value='<?= (int)$transfer_id; ?>'>
  <div class='form-group'>
    <label>Litres that went in <span class='req'>*</span></label>
    <input type='number' step='0.001' min='0.001' name='litres' required value='<?= $suggested > 0 ? mp_format_litres($suggested) : ''; ?>'
           placeholder='Off the tanker meter, or the dip before and after'>
    <?php if($suggested > 0): ?><span class='hint'>Prefilled from what was counted in on this load.</span><?php endif; ?>
  </div>
  <div class='form-group'>
    <label>Where it came from</label>
    <input type='text' name='reason' maxlength='255' value='<?= $transfer ? htmlspecialchars('Load #'.$transfer_id.' from '.get_warehouse_name($transfer->warehouse_from)) : ''; ?>' placeholder='e.g. Tanker MP-114, waybill 30281'>
  </div>
  <button type='submit' class='btn btn-primary'><i class='fa fa-arrow-down'></i> Book the fill</button>
  <?= form_close(); ?>
  <?php endif; ?>
</div>

<?php if(!empty($options) && $can_edit): ?>
<div class='card'>
  <div class='section-title'>Other tanks here</div>
  <?php foreach($options as $option): if((int)$option->id === (int)$tank->id) continue; ?>
  <div class='row'>
    <div class='name'><?= htmlspecialchars($option->tank_name); ?></div>
    <div class='meta'><?= mp_format_litres($option->book_litres); ?> L in it · <?= mp_format_litres($option->headroom); ?> L room</div>
    <div class='actions' style='margin-top:9px;'><a class='action' href='<?= base_url('oil_gas/fill?tank='.(int)$option->id.($transfer_id ? '&transfer='.(int)$transfer_id : '')); ?>'><i class='fa fa-exchange'></i> Use this one</a></div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<?php endif; ?>

<?php $this->load->view('mobile/gas_foot', ['gas_nav'=>'more']); ?>
