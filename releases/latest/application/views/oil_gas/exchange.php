<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $this->load->view('oil_gas/_styles'); ?>
<?php
$site_name = '';
foreach($sites as $site) if((int)$site->id === (int)$site_id) $site_name = $site->warehouse_name;
$who = function($name){ $name = trim((string)$name); if($name === '') return '—'; $parts = preg_split('/\s+/', $name); return $parts[0]; };
$holders = array();
foreach($out_shells as $cylinder){
	if(!isset($holders[(int)$cylinder->customer_id])) $holders[(int)$cylinder->customer_id] = array('name'=>'', 'shells'=>array());
	$holders[(int)$cylinder->customer_id]['name'] = trim((string)$cylinder->customer_name) ?: 'a customer';
	$holders[(int)$cylinder->customer_id]['shells'][] = $cylinder->cylinder_code;
}
?>

<div class="gas-hero">
  <div>
    <h2>Cylinder exchange &mdash; <?= htmlspecialchars($site_name ?: $site_label); ?></h2>
    <p>A full shell goes out and the empty one comes back, as one action. Done as two separate steps this is where shells disappear, so the register moves both halves together and the number of shells in the shop never changes by accident.</p>
  </div>
  <div class="gas-hero-actions">
    <a href="<?= base_url('oil_gas/lpg?site='.(int)$site_id); ?>" class="gas-btn"><i class="fa fa-dashboard"></i> LPG board</a>
    <a href="<?= base_url('oil_gas/cylinders?site='.(int)$site_id); ?>" class="gas-btn"><i class="fa fa-circle-o"></i> Cylinder register</a>
    <?php if($deposit_flag): ?><a href="<?= base_url('oil_gas/deposits?site='.(int)$site_id); ?>" class="gas-btn"><i class="fa fa-money"></i> Deposits</a><?php endif; ?>
  </div>
</div>

<?php if(!empty($sites)): ?>
<div class="gas-sites">
  <?php foreach($sites as $site): ?>
    <a href="<?= base_url('oil_gas/exchange?site='.(int)$site->id); ?>" class="gas-site <?= (int)$site->id === (int)$site_id ? 'active' : ''; ?>"><?= htmlspecialchars($site->warehouse_name); ?></a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="gas-kpis">
  <div class="gas-kpi"><strong><?= count($full_shells); ?></strong><span>Full shells to give out</span><small>Registered here and weighed</small></div>
  <div class="gas-kpi"><strong><?= count($out_shells); ?></strong><span>Shells out with customers</span><small>Company-wide, each one expected back</small></div>
  <div class="gas-kpi <?= $counts['lost'] ? 'alert' : ''; ?>"><strong><?= (int)$counts['lost']; ?></strong><span>Written off as lost</span><small>Deposit held against these stays owed</small></div>
</div>

<div class="gas-two">
  <div class="mp-card-form gas-card">
    <div class="mp-card-head"><h3>Swap two shells</h3></div>
    <div class="mp-card-body">
      <?php if(!$can_work): ?>
        <p class="gas-empty">You can see this <?= htmlspecialchars(strtolower($site_label)); ?> but not move stock at it.</p>
      <?php elseif(empty($full_shells) || empty($out_shells)): ?>
        <p class="gas-empty"><?= empty($full_shells)
          ? 'No full shell is ready at this location. Weigh a fill first.'
          : 'No shell of ours is out with a customer, so there is nothing to take back.'; ?></p>
      <?php else: ?>
      <?= form_open(base_url('oil_gas/save_exchange')); ?>
      <input type="hidden" name="site_id" value="<?= (int)$site_id; ?>">
      <div class="mp-form-group full">
        <label for="customer_id">Who is swapping <span class="text-danger">*</span></label>
        <select class="mp-form-control" id="customer_id" name="customer_id" required>
          <option value="0">Choose a customer</option>
          <?php foreach($customers as $customer): ?>
          <option value="<?= (int)$customer->id; ?>"><?= htmlspecialchars($customer->name); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mp-form-group">
        <label for="taken_id">Shell coming back (empty) <span class="text-danger">*</span></label>
        <select class="mp-form-control" id="taken_id" name="taken_id" required>
          <option value="0">Choose the shell handed over</option>
          <?php foreach($out_shells as $cylinder): ?>
          <option value="<?= (int)$cylinder->id; ?>"><?= htmlspecialchars($cylinder->cylinder_code); ?>
            &middot; <?= htmlspecialchars(trim((string)$cylinder->customer_name) ?: 'unassigned'); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mp-form-group">
        <label for="given_id">Shell going out (full) <span class="text-danger">*</span></label>
        <select class="mp-form-control" id="given_id" name="given_id" required>
          <option value="0">Choose the shell given out</option>
          <?php foreach($full_shells as $cylinder): ?>
          <option value="<?= (int)$cylinder->id; ?>"><?= htmlspecialchars($cylinder->cylinder_code); ?>
            &middot; <?= mp_format_litres($cylinder->size_kg); ?> kg</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mp-form-group full">
        <label for="note">Note</label>
        <input class="mp-form-control" type="text" id="note" name="note" maxlength="255" placeholder="Shell 12-4471 has a scorched base">
      </div>
      <div class="mp-form-actions">
        <button type="submit" class="mp-btn-primary"><i class="fa fa-exchange"></i> Exchange them</button>
      </div>
      <p class="gas-note">A deposit is not moved by an exchange. Pay the returned shell's deposit back and take one for the shell going out, both on the deposits screen.</p>
      <?= form_close(); ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="mp-card-form gas-card">
    <div class="mp-card-head"><h3>Who is holding what</h3></div>
    <div class="mp-card-body" style="padding:0!important">
      <?php if(empty($holders)): ?>
        <p class="gas-empty">Every shell we own is on the premises.</p>
      <?php else: ?>
      <div class="mp-dt-scroll">
        <table class="loc-table" style="width:100%;border-collapse:collapse">
          <thead><tr><th>Customer</th><th>Shells out</th></tr></thead>
          <tbody>
            <?php foreach($holders as $row): ?>
            <tr>
              <td><strong><?= htmlspecialchars($row['name']); ?></strong></td>
              <td><div class="loc-sub"><?= htmlspecialchars(implode(', ', array_slice($row['shells'], 0, 12))); ?><?= count($row['shells']) > 12 ? ' · ' . (count($row['shells']) - 12) . ' more' : ''; ?></div></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
  $(function(){ $('.oil_gas-exchange-active-li').addClass('active'); });
</script>
