<?php
$who = function($name){ $name = trim((string)$name); if($name === '') return '—'; $parts = preg_split('/\s+/', $name); return $parts[0]; };
$editing = !empty($edit);
$ready = array(); $away = array(); $live = array();
foreach($cylinders as $cylinder){
  if($cylinder->status === 'in_shop' && (int)$cylinder->is_full === 1) $ready[] = $cylinder;
  if($cylinder->status === 'out') $away[] = $cylinder;
  if($cylinder->status !== 'lost' && $cylinder->status !== 'scrapped') $live[] = $cylinder;
}
$off_road = (int)$counts['lost'] + (int)$counts['scrapped'];
$this->load->view('mobile/gas_head', [
	'gas_title' => 'Cylinder Register',
	'gas_sub' => 'Every shell the shop owns, the number stamped on it, its empty weight and who is holding it now',
	'store_name' => $store_name ?? ($SITE_TITLE ?? 'MartPoint'),
	'theme_link' => $theme_link,
]);
$this->load->view('mobile/gas_sites', ['sites'=>$sites, 'site_id'=>$site_id, 'gas_route'=>'cylinders']);
?>

<div class='stats'>
  <div class='stat'><strong><?= (int)$counts['total']; ?></strong><span>Shells on the register</span></div>
  <div class='stat'><strong><?= (int)$counts['full']; ?></strong><span>Full and ready here</span></div>
  <div class='stat <?= $counts['out'] ? 'watch' : ''; ?>"><strong><?= (int)$counts['out']; ?></strong><span>Out with customers</span></div>
  <div class='stat <?= $off_road ? 'alert' : ''; ?>"><strong><?= $off_road; ?></strong><span>Lost or off the road</span></div>
</div>

<?php if(!$site_id): ?>
  <div class='callout info'>Choose a <?= htmlspecialchars(strtolower($site_label)); ?> above — the register is kept shell by shell at the location that owns it.</div>
<?php endif; ?>

<?php if($can_edit): ?>
<div class='card'>
  <div class='section-title'><?= $editing ? 'Correct shell ' . htmlspecialchars($edit->cylinder_code) : 'Add a shell to the register'; ?></div>
  <?php if($editing): ?><p class='note' style='margin-top:0;'><a href='<?= base_url('oil_gas/cylinders?site='.(int)$site_id); ?>'>Stop editing</a></p><?php endif; ?>
  <?= form_open(base_url('oil_gas/save_cylinder')); ?>
  <input type='hidden' name='site_id' value='<?= (int)$site_id; ?>'>
  <?php if($editing): ?><input type='hidden' name='cylinder_id' value='<?= (int)$edit->id; ?>'><?php endif; ?>
  <div class='form-group'>
    <label>Number stamped on the shell <span class='req'>*</span></label>
    <input type='text' name='cylinder_code' required maxlength='60' value='<?= htmlspecialchars($editing ? (string)$edit->cylinder_code : ''); ?>' placeholder='e.g. 12-4471'>
  </div>
  <div class='form-row'>
    <div class='form-group'>
      <label>Filled with (net kg) <span class='req'>*</span></label>
      <input type='number' step='0.001' min='0.001' name='size_kg' required value='<?= htmlspecialchars($editing ? mp_format_litres($edit->size_kg) : '12.5'); ?>'>
    </div>
    <div class='form-group'>
      <label>Weighs empty (kg) <span class='req'>*</span></label>
      <input type='number' step='0.001' min='0.001' name='tare_kg' required value='<?= htmlspecialchars($editing ? mp_format_litres($edit->tare_kg) : ''); ?>' placeholder='5.3'>
    </div>
  </div>
  <p class='note'>The empty weight comes off the collar. It is subtracted from every gross weighing, so a shell without one can never be sold by weight.</p>
  <div class='form-group' style='margin-top:12px;'>
    <label>Holds</label>
    <select class='mp-select' name='item_id'>
      <option value='0'>Any gas</option>
      <?php foreach($items as $item): ?>
      <option value='<?= (int)$item->id; ?>' <?= ($editing && (int)$edit->item_id === (int)$item->id) ? 'selected' : ''; ?>><?= htmlspecialchars($item->name); ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class='form-group'>
    <label>Is it filled?</label>
    <select class='mp-select' name='is_full'>
      <option value='0' <?= ($editing && (int)$edit->is_full !== 1) ? 'selected' : ''; ?>>Empty — it must be weighed before it goes out</option>
      <option value='1' <?= ($editing && (int)$edit->is_full === 1) ? 'selected' : ''; ?>>Full — it arrived filled from another of our sites</option>
    </select>
    <span class='hint'>Only a shell the scale has proved, or one that arrived filled, can be handed to a customer.</span>
  </div>
  <div class='form-group'>
    <label>Note</label>
    <input type='text' name='note' maxlength='255' value='<?= htmlspecialchars($editing ? (string)$edit->note : ''); ?>' placeholder='Dent on the foot ring, valve changed in March'>
  </div>
  <button type='submit' class='btn btn-primary'><i class='fa fa-check'></i> <?= $editing ? 'Save the shell' : 'Register this shell'; ?></button>
  <?= form_close(); ?>
</div>
<?php endif; ?>

<?php if($can_work && $site_id): ?>
<div class='card'>
  <div class='section-title'>Send a full shell out</div>
  <?php if(empty($ready)): ?>
    <div class='empty'><i class='fa fa-ban'></i>No full shell is ready here. Weigh a fill first.</div>
  <?php else: ?>
  <?= form_open(base_url('oil_gas/save_custody')); ?>
  <input type='hidden' name='action' value='issue'>
  <input type='hidden' name='site_id' value='<?= (int)$site_id; ?>'>
  <div class='form-group'>
    <label>Which shell <span class='req'>*</span></label>
    <select class='mp-select' name='cylinder_id' required>
      <?php foreach($ready as $cylinder): ?>
      <option value='<?= (int)$cylinder->id; ?>'><?= htmlspecialchars($cylinder->cylinder_code); ?> · <?= mp_format_litres($cylinder->size_kg); ?> kg</option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class='form-group'>
    <label>Hand to <span class='req'>*</span></label>
    <select class='mp-select' name='customer_id' required>
      <option value='0'>Choose a customer</option>
      <?php foreach($customers as $customer): ?>
      <option value='<?= (int)$customer->id; ?>'><?= htmlspecialchars($customer->name); ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class='form-group'>
    <label>Note</label>
    <input type='text' name='note' maxlength='255' placeholder='Delivered to the restaurant back door'>
  </div>
  <button type='submit' class='btn btn-primary'><i class='fa fa-sign-out'></i> Send it out</button>
  <p class='note'>Say who is holding it. The shop still owns the shell — somebody is answerable for it until it comes back.</p>
  <?= form_close(); ?>
  <?php endif; ?>
</div>

<div class='card'>
  <div class='section-title'>Take a shell back</div>
  <?php if(empty($away)): ?>
    <div class='empty'><i class='fa fa-home'></i>No shell of ours is out at this <?= htmlspecialchars(strtolower($site_label)); ?>.</div>
  <?php else: ?>
  <?= form_open(base_url('oil_gas/save_custody')); ?>
  <input type='hidden' name='action' value='return'>
  <input type='hidden' name='site_id' value='<?= (int)$site_id; ?>'>
  <div class='form-group'>
    <label>Which shell came home <span class='req'>*</span></label>
    <select class='mp-select' name='cylinder_id' required>
      <?php foreach($away as $cylinder): ?>
      <option value='<?= (int)$cylinder->id; ?>'><?= htmlspecialchars($cylinder->cylinder_code); ?> · <?= htmlspecialchars(trim((string)$cylinder->customer_name) ?: 'customer not named'); ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class='form-group'>
    <label>Note</label>
    <input type='text' name='note' maxlength='255' placeholder='Valve scuffed but sound'>
  </div>
  <button type='submit' class='btn btn-ghost'><i class='fa fa-sign-in'></i> Take it back</button>
  <p class='note'>A returned shell is counted as empty. Refilling it is a separate act, so nobody can hand out a shell the scale has never proved.</p>
  <?= form_close(); ?>
  <?php endif; ?>
</div>

<?php if(!empty($live)): ?>
<div class='card'>
  <div class='section-title'>Write a shell off</div>
  <?= form_open(base_url('oil_gas/save_custody')); ?>
  <input type='hidden' name='site_id' value='<?= (int)$site_id; ?>'>
  <div class='form-group'>
    <label>Which shell <span class='req'>*</span></label>
    <select class='mp-select' name='cylinder_id' required>
      <?php foreach($live as $cylinder): ?>
      <option value='<?= (int)$cylinder->id; ?>'><?= htmlspecialchars($cylinder->cylinder_code); ?> · <?= $cylinder->status === 'out' ? 'out with ' . htmlspecialchars(trim((string)$cylinder->customer_name) ?: 'a customer') : 'in the shop'; ?></option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class='form-group'>
    <label>What happened <span class='req'>*</span></label>
    <input type='text' name='note' maxlength='255' required placeholder='Failed the hydrostatic test, stamp expired'>
  </div>
  <div class='btn-pair'>
    <button type='submit' name='action' value='lost' class='btn btn-danger'><i class='fa fa-question'></i> Lost</button>
    <button type='submit' name='action' value='scrapped' class='btn btn-ghost'><i class='fa fa-times'></i> Off the road</button>
  </div>
  <p class='note'>Any deposit held against this shell stays owed until it is settled.</p>
  <?= form_close(); ?>
</div>
<?php endif; ?>
<?php endif; ?>

<div class='card'>
  <div class='section-title'>Every shell at this <?= htmlspecialchars(strtolower($site_label)); ?></div>
  <?php if(empty($cylinders)): ?>
    <div class='empty'><i class='fa fa-circle-o'></i>Nothing is registered here yet.</div>
  <?php else: foreach($cylinders as $cylinder): ?>
    <div class='row'>
      <div class='name'><?= htmlspecialchars($cylinder->cylinder_code); ?>
        <span class='flag <?= $cylinder->status === 'out' || $cylinder->status === 'lost' ? 'held' : ($cylinder->status === 'scrapped' ? 'transit' : ((int)$cylinder->is_full === 1 ? 'open' : 'ok')); ?>'><?= $cylinder->status === 'out' ? 'out' : ($cylinder->status === 'lost' ? 'lost' : ($cylinder->status === 'scrapped' ? 'off the road' : ((int)$cylinder->is_full === 1 ? 'full, ready' : 'empty here'))) ?></span>
      </div>
      <div class='meta'><?= htmlspecialchars(trim((string)$cylinder->product) ?: 'no gas chosen'); ?>
        · <span class='litre'><?= mp_format_litres($cylinder->size_kg); ?></span> kg of gas, weighs empty <span class='litre'><?= mp_format_litres($cylinder->tare_kg); ?></span> kg</div>
      <div class='meta'>held by <?= htmlspecialchars(trim((string)$cylinder->customer_name) ?: 'the shop'); ?>
        · <?= $cylinder->last_movement_at ? 'moved ' . show_date($cylinder->last_movement_at) : 'not moved yet'; ?></div>
      <?php if(!empty($cylinder->note)): ?><div class='meta'><?= htmlspecialchars($cylinder->note); ?></div><?php endif; ?>
      <div class='meta' style='margin-top:6px;'>
        <a href='<?= base_url('oil_gas/cylinders?site='.(int)$site_id.'&shell='.(int)$cylinder->id); ?>'>History</a>
        <?php if($can_edit): ?> &middot; <a href='<?= base_url('oil_gas/cylinders?site='.(int)$site_id.'&edit='.(int)$cylinder->id); ?>'>Correct</a><?php endif; ?>
        <?php if($cylinder->status === 'in_shop' && (int)$cylinder->is_full !== 1 && $refill_flag): ?> &middot; <a href='<?= base_url('oil_gas/refill?site='.(int)$site_id); ?>'>Weigh a fill</a><?php endif; ?>
      </div>
    </div>
  <?php endforeach; endif; ?>
</div>

<?php if(!empty($shell)): ?>
<div class='card'>
  <div class='section-title'>The life of shell <?= htmlspecialchars($shell->cylinder_code); ?></div>
  <?php if(empty($history)): ?>
    <div class='empty'><i class='fa fa-history'></i>Nothing has happened to this shell yet.</div>
  <?php else: foreach($history as $line): ?>
    <div class='row'>
      <div class='name'><?= mp_lpg_movement_words($line->movement_type); ?></div>
      <div class='meta'><?= show_date($line->created_at); ?> at <?= htmlspecialchars((string)$line->warehouse_name); ?>
        <?= $line->net_kg === null ? '' : ' · <span class="litre">' . mp_format_litres($line->net_kg) . '</span> kg'; ?></div>
      <div class='meta'><?= htmlspecialchars(trim((string)$line->customer_name) ?: $who($line->created_by)); ?><?= $line->reason ? ' · ' . htmlspecialchars($line->reason) : ''; ?></div>
    </div>
  <?php endforeach; endif; ?>
  <p class='note'><a href='<?= base_url('oil_gas/cylinders?site='.(int)$site_id); ?>'>Back to the register</a></p>
</div>
<?php endif; ?>

<?php $this->load->view('mobile/gas_foot', ['gas_nav'=>'more']); ?>
