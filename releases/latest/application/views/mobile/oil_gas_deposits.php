<?php
$who = function($name){ $name = trim((string)$name); if($name === '') return '—'; $parts = preg_split('/\s+/', $name); return $parts[0]; };
$held = array();
foreach($open as $row) $held[(int)$row->cylinder_id] = (float)$row->held;
$away = array();
$home = array();
foreach($cylinders as $cylinder){
  $balance = $held[(int)$cylinder->id] ?? 0.0;
  if($cylinder->status === 'out' && $balance <= 0.004) $away[] = $cylinder;
  if($cylinder->status === 'in_shop' && $balance > 0.004) $home[] = $cylinder;
}
$this->load->view('mobile/gas_head', [
	'gas_title' => 'Cylinder Deposits',
	'gas_sub' => 'Money held against a shell that belongs to us. Never stock, never a sale',
	'store_name' => $store_name ?? ($SITE_TITLE ?? 'MartPoint'),
	'theme_link' => $theme_link,
]);
$this->load->view('mobile/gas_sites', ['sites'=>$sites, 'site_id'=>$site_id, 'gas_route'=>'deposits']);
?>

<div class='stats'>
  <div class='stat <?= $owed > 0 ? 'watch' : ''; ?>"><strong><?= mp_format_money($owed); ?></strong><span>Owed back to customers</span></div>
  <div class='stat'><strong><?= count($open); ?></strong><span>Shells standing on a deposit</span></div>
</div>

<?php if(!$site_id): ?>
  <div class='callout info'>Choose a <?= htmlspecialchars(strtolower($site_label)); ?> above — money is taken and refunded at the location that let the shell out.</div>
<?php endif; ?>

<?php if($can_work && $site_id): ?>
<div class='card'>
  <div class='section-title'>Take a deposit</div>
  <?php if(empty($away)): ?>
    <div class='empty'><i class='fa fa-money'></i>Every shell out of here already has money held against it, or none is out.</div>
  <?php else: ?>
  <?= form_open(base_url('oil_gas/save_deposit')); ?>
  <input type='hidden' name='site_id' value='<?= (int)$site_id; ?>'>
  <input type='hidden' name='event_type' value='paid'>
  <div class='form-group'>
    <label>Which shell <span class='req'>*</span></label>
    <select class='mp-select' name='cylinder_id' required>
      <?php foreach($away as $cylinder): ?>
      <option value='<?= (int)$cylinder->id; ?>'><?= htmlspecialchars($cylinder->cylinder_code); ?>
        · <?= htmlspecialchars(trim((string)$cylinder->customer_name) ?: 'customer not named'); ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class='form-group'>
    <label>Whose money <span class='req'>*</span></label>
    <select class='mp-select' name='customer_id' required>
      <option value='0'>Choose the customer</option>
      <?php foreach($customers as $customer): ?>
      <option value='<?= (int)$customer->id; ?>'><?= htmlspecialchars($customer->name); ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class='form-group'>
    <label>Amount <span class='req'>*</span></label>
    <input type='number' step='0.01' min='0.01' name='amount' required placeholder='5000.00' inputmode='decimal'>
  </div>
  <div class='form-group'>
    <label>Paid by</label>
    <select class='mp-select' name='payment_mode'>
      <option value='Cash'>Cash</option>
      <option value='Bank transfer'>Bank transfer</option>
      <option value='Card'>Card</option>
    </select>
  </div>
  <div class='form-group'>
    <label>Note</label>
    <input type='text' name='note' maxlength='255' placeholder='Paid in cash at the counter'>
  </div>
  <button type='submit' class='btn btn-primary'><i class='fa fa-lock'></i> Hold it</button>
  <p class='note'>Only shells that are out with somebody appear here, and only once. Stock does not move.</p>
  <?= form_close(); ?>
  <?php endif; ?>
</div>

<div class='card'>
  <div class='section-title'>Refund a deposit</div>
  <?php if(empty($home)): ?>
    <div class='empty'><i class='fa fa-home'></i>Nothing to pay back here yet. A deposit is only refunded once the shell is physically back in the shop.</div>
  <?php else: ?>
  <?= form_open(base_url('oil_gas/save_deposit')); ?>
  <input type='hidden' name='site_id' value='<?= (int)$site_id; ?>'>
  <input type='hidden' name='event_type' value='refunded'>
  <div class='form-group'>
    <label>Which shell came home <span class='req'>*</span></label>
    <select class='mp-select' id='back_cylinder' name='cylinder_id' required>
      <option value='0' data-held='0'>Choose the shell</option>
      <?php foreach($home as $cylinder): ?>
      <option value='<?= (int)$cylinder->id; ?>' data-held='<?= htmlspecialchars($held[(int)$cylinder->id]); ?>'>
        <?= htmlspecialchars($cylinder->cylinder_code); ?> · <?= mp_format_money($held[(int)$cylinder->id]); ?> held</option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class='form-group'>
    <label>Pay it back to <span class='req'>*</span></label>
    <select class='mp-select' name='customer_id' required>
      <option value='0'>Choose the customer</option>
      <?php foreach($customers as $customer): ?>
      <option value='<?= (int)$customer->id; ?>'><?= htmlspecialchars($customer->name); ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class='form-group'>
    <label>Amount <span class='req'>*</span></label>
    <input type='number' step='0.01' min='0.01' id='back_amount' name='amount' required>
    <span class='hint' id='back_hint'>Pick a shell to see what is held against it.</span>
  </div>
  <div class='form-group'>
    <label>Paid by</label>
    <select class='mp-select' name='payment_mode'>
      <option value='Cash'>Cash</option>
      <option value='Bank transfer'>Bank transfer</option>
      <option value='Card'>Card</option>
    </select>
  </div>
  <div class='form-group'>
    <label>Note</label>
    <input type='text' name='note' maxlength='255' placeholder='Shell returned sound, deposit paid in cash'>
  </div>
  <button type='submit' class='btn btn-primary'><i class='fa fa-unlock'></i> Refund it</button>
  <p class='note'>The money only goes back to whoever paid it, and never more than is held.</p>
  <?= form_close(); ?>
  <?php endif; ?>
</div>
<?php endif; ?>

<div class='card'>
  <div class='section-title'>Deposits owed right now</div>
  <?php if(empty($open)): ?>
    <div class='empty'><i class='fa fa-check-circle'></i>We hold nobody's money.</div>
  <?php else: foreach($open as $row): ?>
    <div class='row'>
      <div class='name'><?= htmlspecialchars(trim((string)$row->customer_name) ?: 'a customer'); ?>
        <span class='flag <?= $row->status === 'in_shop' ? 'ok' : 'transit'; ?>'><?= $row->status === 'in_shop' ? 'back — ready to refund' : 'still out' ?></span></div>
      <div class='meta'>shell <?= htmlspecialchars((string)$row->cylinder_code); ?> at <?= htmlspecialchars((string)$row->warehouse_name); ?></div>
      <div class='meta'><span class='litre'><?= mp_format_money($row->held); ?></span> held</div>
    </div>
  <?php endforeach; endif; ?>
</div>

<div class='card'>
  <div class='section-title'>Last deposit movements</div>
  <?php if(empty($ledger)): ?>
    <div class='empty'><i class='fa fa-money'></i>No deposit has been taken or refunded here yet.</div>
  <?php else: foreach($ledger as $row): ?>
    <div class='row'>
      <div class='name'><?= htmlspecialchars((string)$row->cylinder_code); ?>
        <span class='flag <?= $row->event_type === 'paid' ? 'held' : 'ok'; ?>"><?= $row->event_type === 'paid' ? 'money held' : 'money paid back' ?></span></div>
      <div class='meta'><?= show_date($row->deposit_date); ?> · <?= htmlspecialchars(trim((string)$row->customer_name) ?: 'a customer'); ?>
        · <span class='litre'><?= mp_format_money($row->amount); ?></span></div>
      <div class='meta'><?= htmlspecialchars($who($row->created_by)); ?><?= $row->payment_mode ? ' · ' . htmlspecialchars($row->payment_mode) : ''; ?><?= $row->note ? ' · ' . htmlspecialchars($row->note) : ''; ?></div>
    </div>
  <?php endforeach; endif; ?>
</div>

<script>
(function(){
  /* Refunding more than is held is refused by the books, so stop it at the form. */
  var pick = document.getElementById('back_cylinder');
  var amount = document.getElementById('back_amount');
  var hint = document.getElementById('back_hint');
  if(!pick || !amount || !hint) return;
  pick.addEventListener('change', function(){
    var option = pick.options[pick.selectedIndex];
    var held = parseFloat(option && option.getAttribute('data-held')) || 0;
    if(held > 0){
      amount.value = held.toFixed(2);
      amount.setAttribute('max', held);
      hint.textContent = 'Held against this shell: ' + held.toFixed(2) + '. Refund less if only part of it is being returned.';
    }else{
      amount.value = '';
      amount.removeAttribute('max');
      hint.textContent = 'Pick a shell to see what is held against it.';
    }
  });
})();
</script>

<?php $this->load->view('mobile/gas_foot', ['gas_nav'=>'more']); ?>
