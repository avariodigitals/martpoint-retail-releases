<?php
$who = function($name){ $name = trim((string)$name); if($name === '') return '—'; $parts = preg_split('/\s+/', $name); return $parts[0]; };
$this->load->view('mobile/gas_head', [
	'gas_title' => 'Forecourt',
	'gas_sub' => ($site_id ? get_warehouse_name($site_id) : $site_label) . ' · tanks, dips and the duties that prove them',
	'store_name' => $store_name ?? ($SITE_TITLE ?? 'MartPoint'),
	'theme_link' => $theme_link,
	'SITE_TITLE' => $SITE_TITLE ?? 'MartPoint',
]);
?>

<?php if(!empty($sites)): ?>
  <?php $this->load->view('mobile/gas_sites', ['sites'=>$sites, 'site_id'=>$site_id, 'gas_route'=>'forecourt']); ?>
<?php endif; ?>

<div class='stats'>
  <div class='stat <?= $duty ? 'watch' : ''; ?>'><strong><?= $duty ? 'Open' : 'Closed'; ?></strong><span>Duty here</span></div>
  <div class='stat'><strong><?= count($tanks); ?></strong><span>Tanks registered</span></div>
  <div class='stat <?= $summary['gaps'] ? 'alert' : ''; ?>'><strong><?= (int)$summary['gaps']; ?></strong><span>Dips waiting for a manager</span></div>
  <div class='stat <?= $summary['awaiting'] ? 'alert' : ''; ?>'><strong><?= (int)$summary['awaiting']; ?></strong><span>Duties waiting for a manager</span></div>
</div>

<div class='card'>
  <div class='section-title'>The duty</div>
  <?php if($duty): ?>
    <div class='row'>
      <div class='name'>Open since <?= show_date($duty->opened_at); ?></div>
      <div class='meta'>Read by <?= htmlspecialchars($who($duty->opened_by_name)); ?>. Close it by reading every meter again &mdash; that is what compares the pumps with the till.</div>
      <span class='flag open'>running</span>
    </div>
  <?php else: ?>
    <div class='row'>
      <div class='name'>No duty is running</div>
      <div class='meta'>Litres sold before a duty is opened are still rung up and taken from stock, but they have no meter reading to be proved against.</div>
    </div>
  <?php endif; ?>
  <div class='actions' style='margin-top:12px;'>
    <a class='action' href='<?= base_url('oil_gas/duty?site='.(int)$site_id); ?>'><i class='fa fa-exchange'></i> <?= $duty ? 'Close the duty' : 'Open a duty'; ?></a>
    <a class='action' href='<?= base_url('oil_gas/pumps?site='.(int)$site_id); ?>'><i class='fa fa-tachometer'></i> <?= count($pumps); ?> pump<?= count($pumps) === 1 ? '' : 's'; ?></a>
  </div>
</div>

<div class='card'>
  <div class='section-title'>What the tanks hold</div>
  <?php if(empty($tanks)): ?>
    <div class='empty'><i class='fa fa-database'></i>No tank is registered at this <?= htmlspecialchars(strtolower($site_label)); ?> yet.</div>
  <?php else: ?>
    <?php foreach($tanks as $tank): $pos = isset($positions[(int)$tank->id]) ? $positions[(int)$tank->id] : array('book'=>0.0,'dip'=>null,'gap'=>null);
      $pct = (float)$tank->capacity_litres > 0 ? min(100, $pos['book'] / (float)$tank->capacity_litres * 100) : 0; ?>
    <div class='row'>
      <div class='name'><?= htmlspecialchars($tank->tank_name); ?></div>
      <div class='meta'>
        <span class='litre'><?= mp_format_litres($pos['book']); ?></span> L in the book · <?= mp_format_litres($tank->capacity_litres); ?> L capacity
        <?php if($pos['dip'] !== null): ?>
          · last dip <?= mp_format_litres($pos['dip']); ?> L on <?= show_date($pos['dip_date']); ?>
        <?php endif; ?>
      </div>
      <div class='bar <?= $pos['book'] > (float)$tank->safe_fill_litres ? 'over' : ''; ?>'><i style='width:<?= (int)round($pct); ?>%'></i></div>
      <?php if($pos['gap'] !== null && abs($pos['gap']) >= 0.0001): ?>
        <span class='flag <?= $pos['gap'] < 0 ? 'held' : 'transit'; ?>'><?= ($pos['gap'] > 0 ? '+' : '') . mp_format_litres($pos['gap']); ?> L against the book</span>
      <?php elseif($pos['gap'] !== null): ?>
        <span class='flag ok'>dip matches the book</span>
      <?php endif; ?>
      <div class='actions' style='margin-top:10px;'>
        <a class='action' href='<?= base_url('oil_gas/tanks?site='.(int)$site_id.'&dip='.(int)$tank->id); ?>'><i class='fa fa-tint'></i> Read a dip</a>
        <a class='action' href='<?= base_url('oil_gas/fill?tank='.(int)$tank->id); ?>'><i class='fa fa-arrow-down'></i> Fill it</a>
      </div>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php if(!empty($duties_pending) || !empty($dip_gaps)): ?>
<div class='card'>
  <div class='section-title'>Waiting for a manager</div>
  <?php foreach($duties_pending as $pending): ?>
    <div class='row'>
      <div class='name'>Duty closed <?= show_date($pending->closed_at); ?></div>
      <div class='meta'>
        Pumps measured <span class='litre'><?= mp_format_litres($pending->litres_metered); ?></span> L · till rang <span class='litre'><?= mp_format_litres($pending->litres_till); ?></span> L
        · <span class='litre' style='color:#991B1B;'><?= mp_format_litres($pending->variance_litres); ?> L</span> unexplained (<?= mp_format_percent($pending->variance_percent); ?>, over your <?= mp_format_percent($tolerance_duty); ?>)
      </div>
      <div class='meta'>Read and closed by <?= htmlspecialchars($who($pending->opened_by_name)); ?>.</div>
      <?php if($can_decide): ?>
      <?= form_open(base_url('oil_gas/decide')); ?>
      <input type='hidden' name='kind' value='duty'>
      <input type='hidden' name='target_id' value='<?= (int)$pending->id; ?>'>
      <input type='hidden' name='site_id' value='<?= (int)$pending->warehouse_id; ?>'>
      <div class='form-group' style='margin-top:10px;'>
        <label>Your note <span class='req'>*</span></label>
        <textarea name='decision_note' rows='2' required placeholder='Accepted: the pumps run 0.4% rich on this product.'></textarea>
      </div>
      <div class='btn-pair'>
        <button type='submit' name='decision' value='approve' class='btn btn-primary'>Accept</button>
        <button type='submit' name='decision' value='reject' class='btn btn-danger'>Reopen</button>
      </div>
      <?= form_close(); ?>
      <?php else: ?>
      <span class='flag held'>needs a manager</span>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>

  <?php foreach($dip_gaps as $gap): ?>
    <div class='row'>
      <div class='name'><?= htmlspecialchars($gap->tank_name); ?> &mdash; <?= get_warehouse_name($gap->warehouse_id); ?></div>
      <div class='meta'>
        Book <span class='litre'><?= mp_format_litres($gap->book_litres); ?></span> L · dip <span class='litre'><?= mp_format_litres($gap->level_litres); ?></span> L
        · <span class='litre' style='color:<?= $gap->gap < 0 ? '#991B1B' : '#92400E'; ?>;'><?= ($gap->gap > 0 ? '+' : '') . mp_format_litres($gap->gap); ?> L</span>
        (<?= mp_format_percent($gap->gap_percent); ?> of capacity, over your <?= mp_format_percent($tolerance_dip); ?>)
      </div>
      <?php if($can_decide): ?>
      <?= form_open(base_url('oil_gas/decide')); ?>
      <input type='hidden' name='kind' value='dip'>
      <input type='hidden' name='target_id' value='<?= (int)$gap->dip_id; ?>'>
      <input type='hidden' name='site_id' value='<?= (int)$gap->warehouse_id; ?>'>
      <div class='form-group' style='margin-top:10px;'>
        <label>Your note <span class='req'>*</span></label>
        <textarea name='decision_note' rows='2' required placeholder='Written off: weep at the filler valve.'></textarea>
      </div>
      <div class='btn-pair'>
        <button type='submit' name='decision' value='accept' class='btn btn-primary'>Adjust the book</button>
        <button type='submit' name='decision' value='reject' class='btn btn-danger'>Write off nothing</button>
      </div>
      <?= form_close(); ?>
      <?php else: ?>
      <span class='flag held'>nothing written off yet</span>
      <?php endif; ?>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class='card'>
  <div class='section-title'>Recent duties</div>
  <?php if(empty($duty_done)): ?>
    <div class='empty'><i class='fa fa-exchange'></i>No duty has been closed here yet.</div>
  <?php else: ?>
    <?php foreach($duty_done as $past): ?>
    <div class='row'>
      <div class='name'>Duty of <?= show_date($past->opened_at); ?></div>
      <div class='meta'>
        <?= htmlspecialchars($who($past->opened_by_name)); ?> · measured <span class='litre'><?= mp_format_litres($past->litres_metered); ?></span> L against a till of <span class='litre'><?= mp_format_litres($past->litres_till); ?></span> L
      </div>
      <?php if($past->status === 'open'): ?><span class='flag open'>running</span>
      <?php elseif($past->approval_status === 'pending'): ?><span class='flag held'>waiting for a manager</span>
      <?php elseif($past->approval_status === 'approved'): ?><span class='flag transit'>signed off</span>
      <?php else: ?><span class='flag ok'>settled</span><?php endif; ?>
    </div>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<?php $this->load->view('mobile/gas_foot', ['SITE_TITLE'=>$SITE_TITLE ?? 'MartPoint']); ?>
