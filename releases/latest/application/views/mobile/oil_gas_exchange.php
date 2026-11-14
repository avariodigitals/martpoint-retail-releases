<?php
$holders = array();
foreach($out_shells as $cylinder){
  $id = (int)$cylinder->customer_id;
  if(!isset($holders[$id])) $holders[$id] = array('name'=>'', 'shells'=>array());
  $holders[$id]['name'] = trim((string)$cylinder->customer_name) ?: 'a customer';
  $holders[$id]['shells'][] = $cylinder->cylinder_code;
}
$this->load->view('mobile/gas_head', [
	'gas_title' => 'Cylinder Exchange',
	'gas_sub' => 'A full shell goes out and the empty one comes back, as one action',
	'store_name' => $store_name ?? ($SITE_TITLE ?? 'MartPoint'),
	'theme_link' => $theme_link,
]);
$this->load->view('mobile/gas_sites', ['sites'=>$sites, 'site_id'=>$site_id, 'gas_route'=>'exchange']);
?>

<div class='stats'>
  <div class='stat'><strong><?= count($full_shells); ?></strong><span>Full shells to give out</span></div>
  <div class='stat <?= count($out_shells) ? 'watch' : ''; ?>"><strong><?= count($out_shells); ?></strong><span>Shells out with customers</span></div>
  <div class='stat <?= $counts['lost'] ? 'alert' : ''; ?>"><strong><?= (int)$counts['lost']; ?></strong><span>Written off as lost</span></div>
</div>

<div class='card'>
  <div class='section-title'>Swap two shells</div>
  <?php if(!$can_work): ?>
    <div class='empty'><i class='fa fa-lock'></i>You can see this <?= htmlspecialchars(strtolower($site_label)); ?> but not move shells at it.</div>
  <?php elseif(empty($full_shells) || empty($out_shells)): ?>
    <div class='empty'><i class='fa fa-exchange'></i><?= empty($full_shells)
      ? 'No full shell is ready at this location. Weigh a fill first.'
      : 'No shell of ours is out with a customer, so there is nothing to take back.'; ?></div>
  <?php else: ?>
  <?= form_open(base_url('oil_gas/save_exchange')); ?>
  <input type='hidden' name='site_id' value='<?= (int)$site_id; ?>'>
  <div class='form-group'>
    <label>Who is swapping <span class='req'>*</span></label>
    <select class='mp-select' name='customer_id' required>
      <option value='0'>Choose a customer</option>
      <?php foreach($customers as $customer): ?>
      <option value='<?= (int)$customer->id; ?>'><?= htmlspecialchars($customer->name); ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class='form-group'>
    <label>Shell coming back (empty) <span class='req'>*</span></label>
    <select class='mp-select' name='taken_id' required>
      <option value='0'>Choose the shell handed over</option>
      <?php foreach($out_shells as $cylinder): ?>
      <option value='<?= (int)$cylinder->id; ?>'><?= htmlspecialchars($cylinder->cylinder_code); ?>
        · <?= htmlspecialchars(trim((string)$cylinder->customer_name) ?: 'unassigned'); ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class='form-group'>
    <label>Shell going out (full) <span class='req'>*</span></label>
    <select class='mp-select' name='given_id' required>
      <option value='0'>Choose the shell given out</option>
      <?php foreach($full_shells as $cylinder): ?>
      <option value='<?= (int)$cylinder->id; ?>'><?= htmlspecialchars($cylinder->cylinder_code); ?>
        · <?= mp_format_litres($cylinder->size_kg); ?> kg</option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class='form-group'>
    <label>Note</label>
    <input type='text' name='note' maxlength='255' placeholder='Shell 12-4471 has a scorched base'>
  </div>
  <button type='submit' class='btn btn-primary'><i class='fa fa-exchange'></i> Exchange them</button>
  <?= form_close(); ?>
  <?php endif; ?>
  <p class='note'>Done as two separate steps this is where shells disappear, so the register moves both halves together and the number of shells in the shop never changes by accident.</p>
  <?php if($deposit_flag): ?><p class='note'>A deposit is not moved by an exchange. Pay the returned shell's deposit back and take one for the shell going out, both on the <a href='<?= base_url('oil_gas/deposits?site='.(int)$site_id); ?>'>deposits screen</a>.</p><?php endif; ?>
</div>

<div class='card'>
  <div class='section-title'>Who is holding what</div>
  <?php if(empty($holders)): ?>
    <div class='empty'><i class='fa fa-home'></i>Every shell we own is on the premises.</div>
  <?php else: foreach($holders as $row): ?>
    <div class='row'>
      <div class='name'><?= htmlspecialchars($row['name']); ?></div>
      <div class='meta'><?= htmlspecialchars(implode(', ', array_slice($row['shells'], 0, 12))); ?><?= count($row['shells']) > 12 ? ' · ' . (count($row['shells']) - 12) . ' more' : ''; ?></div>
    </div>
  <?php endforeach; endif; ?>
</div>

<?php $this->load->view('mobile/gas_foot', ['gas_nav'=>'more']); ?>
