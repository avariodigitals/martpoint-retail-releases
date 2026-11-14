<?php
$who = function($name){ $name = trim((string)$name); if($name === '') return '—'; $parts = preg_split('/\s+/', $name); return $parts[0]; };
$shells = array();
foreach($empty_shells as $cylinder){
  $shells[(int)$cylinder->id] = array(
    'tare'=>(float)$cylinder->tare_kg, 'size'=>(float)$cylinder->size_kg, 'code'=>(string)$cylinder->cylinder_code);
}
$this->load->view('mobile/gas_head', [
	'gas_title' => 'Refill a Cylinder',
	'gas_sub' => 'Weigh the filled shell. Its own weight comes off the register, so the gas charged for is a difference, not a figure from memory',
	'store_name' => $store_name ?? ($SITE_TITLE ?? 'MartPoint'),
	'theme_link' => $theme_link,
]);
$this->load->view('mobile/gas_sites', ['sites'=>$sites, 'site_id'=>$site_id, 'gas_route'=>'refill']);
?>

<div class='stats'>
  <div class='stat'><strong><span class='litre'><?= mp_format_litres($refilled_today); ?></span> kg</strong><span>Weighed into shells today</span></div>
  <div class='stat'><strong><?= count($empty_shells); ?></strong><span>Empty shells waiting</span></div>
  <div class='stat'><strong><?= count($full_shells); ?></strong><span>Full and ready to go out</span></div>
  <div class='stat <?= count($waiting) ? 'alert' : ''; ?>"><strong><?= count($waiting); ?></strong><span>Fills with no receipt in 7 days</span></div>
</div>

<div class='card'>
  <div class='section-title'>Record a weighing</div>
  <?php if(!$can_work): ?>
    <div class='empty'><i class='fa fa-lock'></i>You can see this <?= htmlspecialchars(strtolower($site_label)); ?> but not record work at it.</div>
  <?php elseif(empty($empty_shells)): ?>
    <div class='empty'><i class='fa fa-circle-o'></i>No empty shell is registered here to fill.
      <?php if($tracking_flag): ?><br><a href='<?= base_url('oil_gas/cylinders?site='.(int)$site_id); ?>'>Open the register</a><?php endif; ?></div>
  <?php else: ?>
  <?= form_open(base_url('oil_gas/save_refill')); ?>
  <input type='hidden' name='site_id' value='<?= (int)$site_id; ?>'>
  <div class='form-group'>
    <label>Which shell went on the scale <span class='req'>*</span></label>
    <select class='mp-select' id='cylinder_id' name='cylinder_id' required>
      <?php foreach($empty_shells as $cylinder): ?>
      <option value='<?= (int)$cylinder->id; ?>'><?= htmlspecialchars($cylinder->cylinder_code); ?>
        · <?= mp_format_litres($cylinder->size_kg); ?> kg · weighs empty <?= mp_format_litres($cylinder->tare_kg); ?> kg</option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class='form-group'>
    <label>Gross weight on the scale (kg) <span class='req'>*</span></label>
    <input type='number' step='0.001' min='0.001' id='gross_kg' name='gross_kg' required placeholder='17.8' inputmode='decimal'>
    <span class='hint' id='net_note'>The gas works out at the difference between this and the shell's own weight.</span>
  </div>
  <div class='form-group'>
    <label>Till receipt this was rung up on</label>
    <input type='text' id='sale_reference' name='sale_reference' maxlength='60' placeholder='leave blank if not sold yet'>
    <span class='hint'>The refill does not take gas out of stock — the till already did that. Enter the receipt so the two can be proved against each other.</span>
  </div>
  <div class='form-group'>
    <label>Note</label>
    <input type='text' name='note' maxlength='255' placeholder='Valve checked, no leak at the collar'>
  </div>
  <button type='submit' class='btn btn-primary'><i class='fa fa-balance-scale'></i> Weigh it in</button>
  <?= form_close(); ?>
  <?php endif; ?>
</div>

<div class='card'>
  <div class='section-title'>Weighings today at this <?= htmlspecialchars(strtolower($site_label)); ?></div>
  <?php if(empty($refills)): ?>
    <div class='empty'><i class='fa fa-balance-scale'></i>Nothing has been weighed here today.</div>
  <?php else: foreach($refills as $fill): ?>
    <div class='row'>
      <div class='name'><?= htmlspecialchars((string)$fill->cylinder_code); ?>
        <?php if($fill->linked_sale_id): ?><span class='flag ok'>rung up</span>
        <?php elseif(!empty($fill->sale_reference)): ?><span class='flag held'>receipt not found</span>
        <?php else: ?><span class='flag transit'>no receipt yet</span><?php endif; ?></div>
      <div class='meta'>gross <span class='litre'><?= mp_format_litres($fill->gross_kg); ?></span> kg
        · shell <span class='litre'><?= mp_format_litres($fill->tare_kg); ?></span> kg
        · gas <span class='litre'><?= mp_format_litres($fill->net_kg); ?></span> kg</div>
      <div class='meta'><?= $fill->refill_time ? htmlspecialchars($fill->refill_time) . ' · ' : ''; ?><?= htmlspecialchars($who($fill->created_by)); ?></div>
    </div>
  <?php endforeach; endif; ?>
</div>

<div class='card'>
  <div class='section-title'>Fills the till has not confirmed</div>
  <?php if(empty($waiting)): ?>
    <div class='empty'><i class='fa fa-check-circle'></i>Every fill in the last week is matched to a receipt.</div>
  <?php else: foreach($waiting as $fill): ?>
    <div class='row'>
      <div class='name'><?= htmlspecialchars((string)$fill->cylinder_code); ?> · <span class='litre'><?= mp_format_litres($fill->net_kg); ?></span> kg</div>
      <div class='meta'><?= show_date($fill->refill_date); ?> at <?= htmlspecialchars((string)$fill->warehouse_name); ?>
        · <?= !empty($fill->sale_reference) ? 'receipt ' . htmlspecialchars($fill->sale_reference) . ' not found' : 'no receipt entered'; ?></div>
    </div>
  <?php endforeach; endif; ?>
  <p class='note'>Ring these up at the till, or enter the receipt number on the refill if it was already sold. Nothing here moves stock.</p>
</div>

<?php if(!empty($full_shells)): ?>
<div class='card'>
  <div class='section-title'>Full shells ready to go out</div>
  <?php foreach($full_shells as $cylinder): ?>
    <div class='row'>
      <div class='name'><?= htmlspecialchars($cylinder->cylinder_code); ?></div>
      <div class='meta'><span class='litre'><?= mp_format_litres($cylinder->size_kg); ?></span> kg of gas
        · <?= htmlspecialchars(trim((string)$cylinder->product) ?: 'no gas chosen'); ?></div>
    </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<script>
var mpLpgShells = <?= json_encode($shells, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
function mpLpgNet(){
  var pick = document.getElementById('cylinder_id');
  var box = document.getElementById('gross_kg');
  var note = document.getElementById('net_note');
  if(!pick || !box || !note) return;
  var shell = mpLpgShells[pick.value];
  var gross = parseFloat(box.value);
  if(!shell){ note.textContent = 'Choose the shell that went on the scale.'; return; }
  if(!gross || gross <= 0){ note.textContent = "The gas works out at the difference between this and the shell's own weight."; return; }
  var net = gross - shell.tare;
  if(net <= 0){ note.textContent = 'That is below the shell\'s own empty weight of ' + shell.tare + ' kg. Check the reading.'; return; }
  if(net > shell.size){ note.textContent = 'That is ' + (Math.round((net - shell.size) * 1000) / 1000) + ' kg more than this shell is filled to. Do not overfill it.'; return; }
  note.textContent = 'The gas works out at ' + (Math.round(net * 1000) / 1000) + ' kg — the ' + shell.code + ' shell weighs ' + shell.tare + ' kg empty.';
}
document.addEventListener('input', function(e){ if(e.target && e.target.id === 'gross_kg') mpLpgNet(); });
document.addEventListener('change', mpLpgNet);
mpLpgNet();
</script>

<?php $this->load->view('mobile/gas_foot', ['gas_nav'=>'more']); ?>
