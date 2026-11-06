<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $this->load->view('oil_gas/_styles'); ?>
<?php
$suggested = 0;
foreach($transfer_lines as $line){
	if($tank && (int)$line->item_id === (int)$tank->item_id){
		$suggested += ($line->received_qty !== null) ? (float)$line->received_qty : (float)$line->transfer_qty;
	}
}
$headroom = $tank ? max(0, (float)$tank->safe_fill_litres - (float)$book) : 0;
?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Book the litres that physically went into a ground tank. This is the only place a tank gains volume, so a load that was never pumped in must not appear here.</div>
  </div>
  <div class="mp-form-actions" style="margin:0;">
    <a href="<?= base_url('oil_gas/forecourt'.($tank ? '?site='.(int)$tank->warehouse_id : '')); ?>" class="mp-btn-secondary"><i class="fa fa-arrow-left"></i> Forecourt</a>
  </div>
</div>

<?php if($transfer): ?>
<div class="alert alert-info">
  <strong>Filling from load #<?= (int)$transfer->id; ?></strong>
  &mdash; <?= htmlspecialchars(get_warehouse_name($transfer->warehouse_from)); ?> to <?= htmlspecialchars(get_warehouse_name($transfer->warehouse_to)); ?>,
  <?= $transfer->receipt_status === 'Received' ? 'counted in on ' . show_date($transfer->received_date) : 'still ' . strtolower(str_replace('_', ' ', $transfer->receipt_status)); ?>.
</div>
<?php endif; ?>

<?php if($already_filled): ?>
<div class="alert alert-warning">
  <strong>This load is already in a tank.</strong>
  <?= mp_format_litres($already_filled->litres); ?> L was booked<?= $already_filled_tank ? ' into "' . htmlspecialchars($already_filled_tank->tank_name) . '"' : ''; ?> here<?php if(!empty($already_filled->reason)): ?> &mdash; <?= htmlspecialchars($already_filled->reason); ?><?php endif; ?>.
  Add to it only if a second, separate quantity arrived.
</div>
<?php endif; ?>

<?php if(!$tank): ?>
<div class="mp-card-form">
  <div class="mp-card-head"><h3>Which tank took the load?</h3></div>
  <div class="mp-card-body" style="padding:0!important">
    <?php if(empty($tanks)): ?>
      <p class="gas-empty">No tanks are registered yet. Add one at the <?= htmlspecialchars(strtolower($site_label)); ?> that received the load.</p>
    <?php else: ?>
    <div class="mp-dt-scroll">
      <table class="loc-table">
        <thead><tr><th>Tank</th><th><?= htmlspecialchars($site_label); ?></th><th>Holds</th><th>Room left</th><th></th></tr></thead>
        <tbody>
          <?php foreach($tanks as $option): $pos = isset($positions[(int)$option->id]) ? $positions[(int)$option->id] : null; $room = max(0, (float)$option->safe_fill_litres - ($pos ? $pos['book'] : 0)); ?>
          <tr>
            <td><div class="loc-name"><?= htmlspecialchars($option->tank_name); ?></div></td>
            <td><?= htmlspecialchars(get_warehouse_name($option->warehouse_id)); ?></td>
            <td class="gas-qty"><?= mp_format_litres($option->capacity_litres); ?> L</td>
            <td class="gas-qty"><?= mp_format_litres($room); ?> L</td>
            <td><a class="mp-btn-secondary" style="padding:6px 10px;font-size:12px;" href="<?= base_url('oil_gas/fill?tank='.(int)$option->id.($transfer_id ? '&transfer='.(int)$transfer_id : '')); ?>">Fill this one</a></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php else: ?>
<div class="gas-two">
  <div class="mp-card-form gas-card">
    <div class="mp-card-head"><h3><?= htmlspecialchars($tank->tank_name); ?></h3></div>
    <div class="mp-card-body">
      <?php if(!$can_edit): ?>
        <p class="gas-empty">Booking a fill changes what this <?= htmlspecialchars(strtolower($site_label)); ?> is holding, so it needs the location-edit permission.</p>
      <?php else: ?>
      <?= form_open(base_url('oil_gas/save_fill')); ?>
      <input type="hidden" name="site_id" value="<?= (int)$tank->warehouse_id; ?>">
      <input type="hidden" name="tank_id" value="<?= (int)$tank->id; ?>">
      <input type="hidden" name="transfer_id" value="<?= (int)$transfer_id; ?>">
      <div class="gas-form">
        <div class="mp-form-group full">
          <label for="litres">Litres that went in <span class="text-danger">*</span></label>
          <input class="mp-form-control" type="number" step="0.001" min="0.001" id="litres" name="litres" required
                 value="<?= $suggested > 0 ? mp_format_litres($suggested) : ''; ?>" placeholder="Off the tanker's own meter, or the dip before and after">
          <div class="loc-sub">Holds <?= mp_format_litres($tank->capacity_litres); ?> L in total, and may be filled no higher than <?= mp_format_litres($tank->safe_fill_litres); ?> L.</div>
        </div>
        <div class="mp-form-group full">
          <label for="reason">Where it came from</label>
          <input class="mp-form-control" type="text" id="reason" name="reason" maxlength="255"
                 value="<?= $transfer ? htmlspecialchars('Load #'.$transfer_id.' from '.get_warehouse_name($transfer->warehouse_from)) : ''; ?>"
                 placeholder="e.g. Tanker MP-114, waybill 30281">
        </div>
      </div>
      <div class="mp-form-actions" style="margin-top:18px;">
        <button type="submit" class="mp-btn-primary"><i class="fa fa-arrow-down"></i> Book the fill</button>
      </div>
      <?= form_close(); ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="mp-card-form gas-card">
    <div class="mp-card-head"><h3>Tanks at this <?= htmlspecialchars(strtolower($site_label)); ?></h3></div>
    <div class="mp-card-body" style="padding:0!important">
      <?php if(empty($options)): ?>
        <p class="gas-empty">Nothing else at this site can take this product.</p>
      <?php else: ?>
      <table class="loc-table">
        <thead><tr><th>Tank</th><th>In it now</th><th>Room left</th><th></th></tr></thead>
        <tbody>
          <?php foreach($options as $option): ?>
          <tr>
            <td><div class="loc-name"><?= htmlspecialchars($option->tank_name); ?></div><?= (int)$option->id === (int)$tank->id ? '<span class="loc-chip">chosen</span>' : '' ?></td>
            <td class="gas-qty"><?= mp_format_litres($option->book_litres); ?> L</td>
            <td class="gas-qty"><?= mp_format_litres($option->headroom); ?> L</td>
            <td><?php if((int)$option->id !== (int)$tank->id): ?><a style="font-size:12px;" href="<?= base_url('oil_gas/fill?tank='.(int)$option->id.($transfer_id ? '&transfer='.(int)$transfer_id : '')); ?>">use this one</a><?php else: ?><?= mp_format_litres($headroom); ?> L room<?php endif; ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<script>
  $(function(){ $('.oil_gas-forecourt-active-li').addClass('active'); });
</script>
