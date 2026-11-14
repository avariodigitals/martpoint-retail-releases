<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $this->load->view('oil_gas/_styles'); ?>
<?php
$site_name = '';
foreach($sites as $site) if((int)$site->id === (int)$site_id) $site_name = $site->warehouse_name;
$editing = !empty($edit);
// History feeds read as people, not accounts: first name only, never a username.
$who = function($name){ $name = trim((string)$name); if($name === '') return '—'; $parts = preg_split('/\s+/', $name); return $parts[0]; };
$off_road = (int)$counts['lost'] + (int)$counts['scrapped'];
?>

<div class="gas-hero">
  <div>
    <h2>Cylinder register &mdash; <?= htmlspecialchars($site_name ?: $site_label); ?></h2>
    <p>Every shell the shop owns, the number stamped on it, the weight it weighs empty, and who is holding it now. A shell is not stock: it walks out with a customer and it is expected back.</p>
  </div>
  <div class="gas-hero-actions">
    <a href="<?= base_url('oil_gas/lpg?site='.(int)$site_id); ?>" class="gas-btn"><i class="fa fa-dashboard"></i> LPG board</a>
    <?php if($refill_flag): ?><a href="<?= base_url('oil_gas/refill?site='.(int)$site_id); ?>" class="gas-btn"><i class="fa fa-balance-scale"></i> Weigh a fill</a><?php endif; ?>
    <a href="<?= base_url('oil_gas/exchange?site='.(int)$site_id); ?>" class="gas-btn"><i class="fa fa-exchange"></i> Exchange</a>
    <?php if($deposit_flag): ?><a href="<?= base_url('oil_gas/deposits?site='.(int)$site_id); ?>" class="gas-btn"><i class="fa fa-money"></i> Deposits</a><?php endif; ?>
  </div>
</div>

<?php if(!empty($sites)): ?>
<div class="gas-sites">
  <?php foreach($sites as $site): ?>
    <a href="<?= base_url('oil_gas/cylinders?site='.(int)$site->id); ?>" class="gas-site <?= (int)$site->id === (int)$site_id ? 'active' : ''; ?>"><?= htmlspecialchars($site->warehouse_name); ?></a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="gas-kpis">
  <div class="gas-kpi"><strong><?= (int)$counts['total']; ?></strong><span>Shells on the register</span><small>Company-wide, including the ones off the road</small></div>
  <div class="gas-kpi"><strong><?= (int)$counts['full']; ?></strong><span>Full and ready here</span><small>Refilled, waiting to go out</small></div>
  <div class="gas-kpi <?= $counts['out'] ? 'watch' : ''; ?>"><strong><?= (int)$counts['out']; ?></strong><span>Out with customers</span><small>Each one is somebody's to bring back</small></div>
  <div class="gas-kpi <?= $off_road ? 'alert' : ''; ?>"><strong><?= $off_road; ?></strong><span>Lost or taken off the road</span><small>Any deposit against these stays owed</small></div>
</div>

<?php if($can_edit): ?>
<div class="mp-card-form">
  <div class="mp-card-head"><h3><?= $editing ? 'Correct a shell' : 'Add a shell to the register'; ?></h3>
    <?php if($editing): ?><a class="mp-qa-btn" href="<?= base_url('oil_gas/cylinders?site='.(int)$site_id); ?>">Stop editing</a><?php endif; ?>
  </div>
  <div class="mp-card-body">
    <?= form_open(base_url('oil_gas/save_cylinder'), array('class'=>'gas-form')); ?>
    <input type="hidden" name="site_id" value="<?= (int)$site_id; ?>">
    <?php if($editing): ?><input type="hidden" name="cylinder_id" value="<?= (int)$edit->id; ?>"><?php endif; ?>
    <div class="mp-form-group">
      <label for="cylinder_code">Number stamped on the shell <span class="text-danger">*</span></label>
      <input class="mp-form-control" type="text" id="cylinder_code" name="cylinder_code" required maxlength="60"
        value="<?= htmlspecialchars($editing ? (string)$edit->cylinder_code : ''); ?>" placeholder="e.g. 12-4471">
    </div>
    <div class="mp-form-group">
      <label for="size_kg">Filled with (net kg) <span class="text-danger">*</span></label>
      <input class="mp-form-control" type="number" step="0.001" min="0.001" id="size_kg" name="size_kg" required
        value="<?= htmlspecialchars($editing ? mp_format_litres($edit->size_kg) : '12.5'); ?>">
      <span class="gas-note">The gas it is filled to, not the weight of the shell.</span>
    </div>
    <div class="mp-form-group">
      <label for="tare_kg">Weighs empty (kg) <span class="text-danger">*</span></label>
      <input class="mp-form-control" type="number" step="0.001" min="0.001" id="tare_kg" name="tare_kg" required
        value="<?= htmlspecialchars($editing ? mp_format_litres($edit->tare_kg) : ''); ?>" placeholder="5.3">
      <span class="gas-note">Read it off the collar. This is subtracted from every gross weighing.</span>
    </div>
    <div class="mp-form-group">
      <label for="item_id">Holds</label>
      <select class="mp-form-control" id="item_id" name="item_id">
        <option value="0">Any gas</option>
        <?php foreach($items as $item): ?>
        <option value="<?= (int)$item->id; ?>" <?= ($editing && (int)$edit->item_id === (int)$item->id) ? 'selected' : ''; ?>><?= htmlspecialchars($item->name); ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mp-form-group">
      <label for="is_full">Is it filled?</label>
      <select class="mp-form-control" id="is_full" name="is_full">
        <option value="0" <?= ($editing && (int)$edit->is_full !== 1) ? 'selected' : ''; ?>>Empty &mdash; it must be weighed before it goes out</option>
        <option value="1" <?= ($editing && (int)$edit->is_full === 1) ? 'selected' : ''; ?>>Full &mdash; it arrived filled from another of our sites</option>
      </select>
      <span class="gas-note">Only a shell the scale has proved, or one that arrived filled, can be handed to a customer.</span>
    </div>
    <div class="mp-form-group full">
      <label for="note">Note</label>
      <input class="mp-form-control" type="text" id="note" name="note" maxlength="255"
        value="<?= htmlspecialchars($editing ? (string)$edit->note : ''); ?>" placeholder="Dent on the foot ring, valve changed in March">
    </div>
    <div class="mp-form-actions">
      <button type="submit" class="mp-btn-primary"><i class="fa fa-check"></i> <?= $editing ? 'Save the shell' : 'Register this shell'; ?></button>
    </div>
    <?= form_close(); ?>
  </div>
</div>
<?php endif; ?>

<div class="mp-card-form">
  <div class="mp-card-head"><h3>Every shell at this <?= htmlspecialchars(strtolower($site_label)); ?></h3></div>
  <div class="mp-card-body" style="padding:0!important">
    <?php if(empty($cylinders)): ?>
      <p class="gas-empty">Nothing is registered here yet. Add the shells with the number, size and empty weight stamped on them — without the empty weight, a gross weighing cannot be turned into the gas a customer pays for.</p>
    <?php else: ?>
    <div class="mp-dt-scroll">
      <table class="loc-table" style="width:100%;border-collapse:collapse">
        <thead>
          <tr><th>Shell</th><th>Size</th><th>Weighs empty</th><th>State</th><th>Who holds it</th><th>Last moved</th><th>Work</th></tr>
        </thead>
        <tbody>
          <?php foreach($cylinders as $cylinder): ?>
          <tr>
            <td><strong><?= htmlspecialchars($cylinder->cylinder_code); ?></strong>
              <div class="loc-sub"><?= htmlspecialchars(trim((string)$cylinder->product) ?: 'no gas chosen'); ?></div>
              <?php if(!empty($cylinder->note)): ?><div class="loc-sub"><?= htmlspecialchars($cylinder->note); ?></div><?php endif; ?>
            </td>
            <td class="gas-qty"><?= mp_format_litres($cylinder->size_kg); ?> kg</td>
            <td class="gas-qty"><?= mp_format_litres($cylinder->tare_kg); ?> kg</td>
            <td>
              <?php if($cylinder->status === 'out'): ?><span class="gas-status held">out</span>
              <?php elseif($cylinder->status === 'lost'): ?><span class="gas-status held">lost</span>
              <?php elseif($cylinder->status === 'scrapped'): ?><span class="gas-status transit">off the road</span>
              <?php elseif((int)$cylinder->is_full === 1): ?><span class="gas-status open">full, ready</span>
              <?php else: ?><span class="gas-status ok">empty here</span><?php endif; ?>
            </td>
            <td><div class="loc-sub"><?= htmlspecialchars(trim((string)$cylinder->customer_name) ?: 'the shop'); ?></div></td>
            <td><div class="loc-sub"><?= $cylinder->last_movement_at ? show_date($cylinder->last_movement_at) : 'not moved yet'; ?></div></td>
            <td>
              <?php if($can_work): ?>
              <?php if($cylinder->status === 'in_shop' && (int)$cylinder->is_full === 1): ?>
                <?= form_open(base_url('oil_gas/save_custody')); ?>
                <input type="hidden" name="action" value="issue">
                <input type="hidden" name="cylinder_id" value="<?= (int)$cylinder->id; ?>">
                <input type="hidden" name="site_id" value="<?= (int)$cylinder->warehouse_id; ?>">
                <select class="mp-form-control" name="customer_id" style="width:150px;display:inline-block" required>
                  <option value="0">Hand to&hellip;</option>
                  <?php foreach($customers as $customer): ?>
                  <option value="<?= (int)$customer->id; ?>"><?= htmlspecialchars($customer->name); ?></option>
                  <?php endforeach; ?>
                </select>
                <button type="submit" class="mp-btn-secondary" style="margin-top:6px;"><i class="fa fa-sign-out"></i> Send it out</button>
                <?= form_close(); ?>
              <?php elseif($cylinder->status === 'in_shop'): ?>
                <a class="mp-qa-btn" href="<?= base_url('oil_gas/refill?site='.(int)$cylinder->warehouse_id); ?>">Weigh a fill</a>
              <?php elseif($cylinder->status === 'out'): ?>
                <?= form_open(base_url('oil_gas/save_custody')); ?>
                <input type="hidden" name="action" value="return">
                <input type="hidden" name="cylinder_id" value="<?= (int)$cylinder->id; ?>">
                <input type="hidden" name="site_id" value="<?= (int)$cylinder->warehouse_id; ?>">
                <button type="submit" class="mp-btn-secondary"><i class="fa fa-sign-in"></i> Take it back</button>
                <?= form_close(); ?>
              <?php else: ?>
                <span class="text-muted">—</span>
              <?php endif; ?>
              <?php if($cylinder->status !== 'lost' && $cylinder->status !== 'scrapped'): ?>
              <?= form_open(base_url('oil_gas/save_custody')); ?>
              <input type="hidden" name="cylinder_id" value="<?= (int)$cylinder->id; ?>">
              <input type="hidden" name="site_id" value="<?= (int)$cylinder->warehouse_id; ?>">
              <input type="text" name="note" maxlength="255" placeholder="what happened?" style="width:140px;">
              <button type="submit" name="action" value="lost" class="mp-btn-secondary" style="margin-top:4px;">Lost</button>
              <button type="submit" name="action" value="scrapped" class="mp-btn-secondary" style="margin-top:4px;">Off the road</button>
              <?= form_close(); ?>
              <?php endif; ?>
              <?php endif; ?>
              <a href="<?= base_url('oil_gas/cylinders?site='.(int)$site_id.'&shell='.(int)$cylinder->id); ?>">History</a>
              <?php if($can_edit): ?>
              <a href="<?= base_url('oil_gas/cylinders?site='.(int)$site_id.'&edit='.(int)$cylinder->id); ?>">Correct</a>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<?php if(!empty($shell)): ?>
<div class="mp-card-form">
  <div class="mp-card-head"><h3>The life of shell <?= htmlspecialchars($shell->cylinder_code); ?></h3>
    <a class="mp-qa-btn" href="<?= base_url('oil_gas/cylinders?site='.(int)$site_id); ?>">Close</a>
  </div>
  <div class="mp-card-body" style="padding:0!important">
    <?php if(empty($history)): ?>
      <p class="gas-empty">Nothing has happened to this shell yet.</p>
    <?php else: ?>
    <div class="mp-dt-scroll">
      <table class="loc-table" style="width:100%;border-collapse:collapse">
        <thead><tr><th>When</th><th>What</th><th>Gas</th><th>Who</th><th>Why</th></tr></thead>
        <tbody>
          <?php foreach($history as $line): ?>
          <tr>
            <td><div class="loc-sub"><?= show_date($line->created_at); ?></div></td>
            <td><strong><?= mp_lpg_movement_words($line->movement_type); ?></strong>
              <div class="loc-sub"><?= htmlspecialchars((string)$line->warehouse_name); ?></div></td>
            <td class="gas-qty"><?= $line->net_kg === null ? '—' : mp_format_litres($line->net_kg).' kg' ?></td>
            <td><div class="loc-sub"><?= htmlspecialchars(trim((string)$line->customer_name) ?: $who($line->created_by)); ?></div></td>
            <td><div class="loc-sub"><?= htmlspecialchars((string)$line->reason); ?></div></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>
<?php endif; ?>

<script>
  $(function(){ $('.oil_gas-cylinders-active-li').addClass('active'); });
</script>
