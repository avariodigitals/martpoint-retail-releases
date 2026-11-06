<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $this->load->view('oil_gas/_styles'); ?>
<?php
$site_name = '';
foreach($sites as $site) if((int)$site->id === (int)$site_id) $site_name = $site->warehouse_name;
$who = function($name){ $name = trim((string)$name); if($name === '') return '—'; $parts = preg_split('/\s+/', $name); return $parts[0]; };
$shells = array();
foreach($empty_shells as $cylinder){
	$shells[(int)$cylinder->id] = array(
		'tare'=>(float)$cylinder->tare_kg, 'size'=>(float)$cylinder->size_kg, 'code'=>(string)$cylinder->cylinder_code);
}
?>

<div class="gas-hero">
  <div>
    <h2>Weigh a fill &mdash; <?= htmlspecialchars($site_name ?: $site_label); ?></h2>
    <p>Weigh the cylinder when it is full and type that number in. The shell's own weight is taken from the register, so the gas a customer pays for is the difference between the two — not a figure somebody types from memory.</p>
  </div>
  <div class="gas-hero-actions">
    <a href="<?= base_url('oil_gas/lpg?site='.(int)$site_id); ?>" class="gas-btn"><i class="fa fa-dashboard"></i> LPG board</a>
    <a href="<?= base_url('oil_gas/cylinders?site='.(int)$site_id); ?>" class="gas-btn"><i class="fa fa-circle-o"></i> Cylinder register</a>
  </div>
</div>

<?php if(!empty($sites)): ?>
<div class="gas-sites">
  <?php foreach($sites as $site): ?>
    <a href="<?= base_url('oil_gas/refill?site='.(int)$site->id); ?>" class="gas-site <?= (int)$site->id === (int)$site_id ? 'active' : ''; ?>"><?= htmlspecialchars($site->warehouse_name); ?></a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="gas-kpis">
  <div class="gas-kpi"><strong><?= mp_format_litres($refilled_today); ?> kg</strong><span>Weighed into shells today</span><small>Gas that left the bulk at this <?= htmlspecialchars(strtolower($site_label)); ?></small></div>
  <div class="gas-kpi"><strong><?= count($empty_shells); ?></strong><span>Empty shells waiting</span><small>Registered here and ready for the scale</small></div>
  <div class="gas-kpi"><strong><?= count($full_shells); ?></strong><span>Full and ready to go out</span><small>Hand these to a customer on the register</small></div>
  <div class="gas-kpi <?= count($waiting) ? 'alert' : ''; ?>"><strong><?= count($waiting); ?></strong><span>Fills with no till receipt</span><small>In the last seven days</small></div>
</div>

<div class="gas-two">
  <div class="mp-card-form gas-card">
    <div class="mp-card-head"><h3>Record a weighing</h3></div>
    <div class="mp-card-body">
      <?php if(!$can_work): ?>
        <p class="gas-empty">You can see this <?= htmlspecialchars(strtolower($site_label)); ?> but not record work at it.</p>
      <?php elseif(empty($empty_shells)): ?>
        <p class="gas-empty">There is no empty shell registered here to fill. Add shells on the cylinder register first — a weighing without a shell behind it cannot be checked against anything.</p>
      <?php else: ?>
      <?= form_open(base_url('oil_gas/save_refill')); ?>
      <input type="hidden" name="site_id" value="<?= (int)$site_id; ?>">
      <div class="mp-form-group">
        <label for="cylinder_id">Which shell went on the scale <span class="text-danger">*</span></label>
        <select class="mp-form-control" id="cylinder_id" name="cylinder_id" required>
          <?php foreach($empty_shells as $cylinder): ?>
          <option value="<?= (int)$cylinder->id; ?>"><?= htmlspecialchars($cylinder->cylinder_code); ?>
            &middot; <?= mp_format_litres($cylinder->size_kg); ?> kg &middot; weighs empty <?= mp_format_litres($cylinder->tare_kg); ?> kg</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mp-form-group">
        <label for="gross_kg">Gross weight on the scale (kg) <span class="text-danger">*</span></label>
        <input class="mp-form-control" type="number" step="0.001" min="0.001" id="gross_kg" name="gross_kg" required placeholder="17.8">
        <span class="gas-note" id="net_note">The gas works out at the difference between this and the shell's own weight.</span>
      </div>
      <div class="mp-form-group">
        <label for="sale_reference">Till receipt this was rung up on</label>
        <input class="mp-form-control" type="text" id="sale_reference" name="sale_reference" maxlength="60" placeholder="leave blank if not sold yet">
        <span class="gas-note">The refill does not take gas out of stock — the till already did that. Enter the receipt so the two can be proved against each other.</span>
      </div>
      <div class="mp-form-group full">
        <label for="note">Note</label>
        <input class="mp-form-control" type="text" id="note" name="note" maxlength="255" placeholder="Valve checked, no leak at the collar">
      </div>
      <div class="mp-form-actions">
        <button type="submit" class="mp-btn-primary"><i class="fa fa-balance-scale"></i> Weigh it in</button>
      </div>
      <?= form_close(); ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="mp-card-form gas-card">
    <div class="mp-card-head"><h3>Fills the till has not confirmed</h3></div>
    <div class="mp-card-body" style="padding:0!important">
      <?php if(empty($waiting)): ?>
        <p class="gas-empty">Every fill in the last week is matched to a receipt. That is the number a gas business is judged on: what the scale said and what the till rang up are the same.</p>
      <?php else: ?>
      <div class="mp-dt-scroll">
        <table class="loc-table" style="width:100%;border-collapse:collapse">
          <thead><tr><th>Shell</th><th>Day</th><th>Gas</th><th>Receipt</th></tr></thead>
          <tbody>
            <?php foreach($waiting as $fill): ?>
            <tr>
              <td><strong><?= htmlspecialchars((string)$fill->cylinder_code); ?></strong>
                <div class="loc-sub"><?= htmlspecialchars((string)$fill->warehouse_name); ?></div></td>
              <td><?= show_date($fill->refill_date); ?></td>
              <td class="gas-qty"><span class="gas-litre"><?= mp_format_litres($fill->net_kg); ?></span> kg</td>
              <td><div class="loc-sub"><?= !empty($fill->sale_reference) ? htmlspecialchars($fill->sale_reference).' not found' : 'none entered' ?></div></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="mp-card-form">
  <div class="mp-card-head"><h3>Weighings today at this <?= htmlspecialchars(strtolower($site_label)); ?></h3></div>
  <div class="mp-card-body" style="padding:0!important">
    <?php if(empty($refills)): ?>
      <p class="gas-empty">Nothing has been weighed here today.</p>
    <?php else: ?>
    <div class="mp-dt-scroll">
      <table class="loc-table" style="width:100%;border-collapse:collapse">
        <thead><tr><th>Shell</th><th>Gross</th><th>Shell weight</th><th>Gas</th><th>Receipt</th><th>By</th></tr></thead>
        <tbody>
          <?php foreach($refills as $fill): ?>
          <tr>
            <td><strong><?= htmlspecialchars((string)$fill->cylinder_code); ?></strong>
              <div class="loc-sub"><?= $fill->refill_time ? htmlspecialchars($fill->refill_time) : ''; ?></div></td>
            <td class="gas-qty"><?= mp_format_litres($fill->gross_kg); ?> kg</td>
            <td class="gas-qty"><?= mp_format_litres($fill->tare_kg); ?> kg</td>
            <td class="gas-qty"><span class="gas-litre"><?= mp_format_litres($fill->net_kg); ?></span> kg</td>
            <td>
              <?php if($fill->linked_sale_id): ?><span class="gas-status ok">rung up</span>
              <?php elseif(!empty($fill->sale_reference)): ?><span class="gas-status held">receipt not found</span>
              <?php else: ?><span class="gas-status transit">no receipt yet</span><?php endif; ?>
            </td>
            <td><div class="loc-sub"><?= htmlspecialchars($who($fill->created_by)); ?></div></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
  var mpLpgShells = <?= json_encode($shells, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
  function mpLpgNet(){
    var shell = mpLpgShells[document.getElementById('cylinder_id').value];
    var gross = parseFloat(document.getElementById('gross_kg').value);
    var note = document.getElementById('net_note');
    if(!note || !shell) return;
    if(!gross || gross <= 0){ note.textContent = 'The gas works out at the difference between this and the shell\'s own weight.'; return; }
    var net = gross - shell.tare;
    if(net <= 0){ note.textContent = 'That is below the shell\'s own empty weight of ' + shell.tare + ' kg. Check the reading.'; return; }
    if(net > shell.size){ note.textContent = 'That is ' + (Math.round((net - shell.size) * 1000) / 1000) + ' kg more than this shell is filled to. Do not overfill it.'; return; }
    note.textContent = 'The gas works out at ' + (Math.round(net * 1000) / 1000) + ' kg — the ' + shell.code + ' shell weighs ' + shell.tare + ' kg empty.';
  }
  $(function(){
    $('.oil_gas-refill-active-li').addClass('active');
    $('#cylinder_id, #gross_kg').on('change input', mpLpgNet);
    mpLpgNet();
  });
</script>
