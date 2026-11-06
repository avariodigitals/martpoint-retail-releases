<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $this->load->view('oil_gas/_styles'); ?>
<?php
$site_name = '';
foreach($sites as $site) if((int)$site->id === (int)$site_id) $site_name = $site->warehouse_name;
$who = function($name){ $name = trim((string)$name); if($name === '') return '—'; $parts = preg_split('/\s+/', $name); return $parts[0]; };

/* What money stands against each shell, so a deposit is never taken twice. */
$held = array();
foreach($open as $row) $held[(int)$row->cylinder_id] = (float)$row->held;

$away = array();
$home = array();
foreach($cylinders as $cylinder){
  $id = (int)$cylinder->id;
  $balance = $held[$id] ?? 0.0;
  if($cylinder->status === 'out' && $balance <= 0.004) $away[] = $cylinder;
  if($cylinder->status === 'in_shop' && $balance > 0.004) $home[] = $cylinder;
}
$modes = array('Cash', 'Bank transfer', 'Card');
?>

<div class="gas-hero">
  <div>
    <h2>Cylinder deposits &mdash; <?= htmlspecialchars($site_name ?: $site_label); ?></h2>
    <p>A deposit is money we hold against a shell that belongs to us. It never touches stock and it is never a sale: the gas inside went out through the till, the shell itself is only on loan. Take it when a full shell leaves, pay it back when the shell comes home.</p>
  </div>
  <div class="gas-hero-actions">
    <a href="<?= base_url('oil_gas/lpg?site='.(int)$site_id); ?>" class="gas-btn"><i class="fa fa-dashboard"></i> LPG board</a>
    <a href="<?= base_url('oil_gas/cylinders?site='.(int)$site_id); ?>" class="gas-btn"><i class="fa fa-circle-o"></i> Cylinder register</a>
    <a href="<?= base_url('oil_gas/exchange?site='.(int)$site_id); ?>" class="gas-btn"><i class="fa fa-exchange"></i> Exchange</a>
  </div>
</div>

<?php if(!empty($sites)): ?>
<div class="gas-sites">
  <?php foreach($sites as $site): ?>
    <a href="<?= base_url('oil_gas/deposits?site='.(int)$site->id); ?>" class="gas-site <?= (int)$site->id === (int)$site_id ? 'active' : ''; ?>"><?= htmlspecialchars($site->warehouse_name); ?></a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="gas-kpis">
  <div class="gas-kpi watch"><strong><?= mp_format_money($owed); ?></strong><span>Owed back to customers</span><small>Money held, not income</small></div>
  <div class="gas-kpi"><strong><?= count($open); ?></strong><span>Shells standing on a deposit</span><small>Each one is ours until it comes back</small></div>
  <div class="gas-kpi"><strong><?= count($ledger); ?></strong><span>Last movements booked</span><small>Newest first</small></div>
</div>

<div class="gas-two">
  <div class="mp-card-form gas-card">
    <div class="mp-card-head"><h3>Take a deposit</h3></div>
    <div class="mp-card-body">
      <?php if(!$can_work): ?>
        <p class="gas-empty">You can see this <?= htmlspecialchars(strtolower($site_label)); ?> but you cannot book money against its shells.</p>
      <?php elseif(empty($away)): ?>
        <p class="gas-empty">Every shell out of this <?= htmlspecialchars(strtolower($site_label)); ?> already has money held against it, or none is out. Hand a refilled shell over first.</p>
      <?php else: ?>
      <?= form_open(base_url('oil_gas/save_deposit')); ?>
      <input type="hidden" name="site_id" value="<?= (int)$site_id; ?>">
      <input type="hidden" name="event_type" value="paid">
      <div class="mp-form-group full">
        <label for="pay_cylinder">Which shell <span class="text-danger">*</span></label>
        <select class="mp-form-control" id="pay_cylinder" name="cylinder_id" required>
          <option value="0">Choose the shell that went out</option>
          <?php foreach($away as $cylinder): ?>
          <option value="<?= (int)$cylinder->id; ?>"><?= htmlspecialchars($cylinder->cylinder_code); ?>
            &middot; <?= htmlspecialchars(trim((string)$cylinder->customer_name) ?: 'customer not named'); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mp-form-group">
        <label for="pay_customer">Whose money <span class="text-danger">*</span></label>
        <select class="mp-form-control" id="pay_customer" name="customer_id" required>
          <option value="0">Choose the customer</option>
          <?php foreach($customers as $customer): ?>
          <option value="<?= (int)$customer->id; ?>"><?= htmlspecialchars($customer->name); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mp-form-group">
        <label for="pay_amount">Amount <span class="text-danger">*</span></label>
        <input class="mp-form-control" type="number" id="pay_amount" name="amount" step="0.01" min="0.01" required placeholder="5000.00">
      </div>
      <div class="mp-form-group">
        <label for="pay_mode">Paid by</label>
        <select class="mp-form-control" id="pay_mode" name="payment_mode">
          <?php foreach($modes as $mode): ?><option value="<?= htmlspecialchars($mode); ?>"><?= htmlspecialchars($mode); ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="mp-form-group full">
        <label for="pay_note">Note</label>
        <input class="mp-form-control" type="text" id="pay_note" name="note" maxlength="255" placeholder="Paid in cash at the counter">
      </div>
      <div class="mp-form-actions">
        <button type="submit" class="mp-btn-primary"><i class="fa fa-lock"></i> Hold it</button>
      </div>
      <p class="gas-note">Only shells that are out with somebody appear here, and only once. Stock does not move.</p>
      <?= form_close(); ?>
      <?php endif; ?>
    </div>
  </div>

  <div class="mp-card-form gas-card">
    <div class="mp-card-head"><h3>Refund a deposit</h3></div>
    <div class="mp-card-body">
      <?php if(!$can_work): ?>
        <p class="gas-empty">You can see this <?= htmlspecialchars(strtolower($site_label)); ?> but you cannot pay money back against its shells.</p>
      <?php elseif(empty($home)): ?>
        <p class="gas-empty">Nothing to pay back at this <?= htmlspecialchars(strtolower($site_label)); ?> yet. A deposit is only refunded once the shell is physically back in the shop.</p>
      <?php else: ?>
      <?= form_open(base_url('oil_gas/save_deposit')); ?>
      <input type="hidden" name="site_id" value="<?= (int)$site_id; ?>">
      <input type="hidden" name="event_type" value="refunded">
      <div class="mp-form-group full">
        <label for="back_cylinder">Which shell came home <span class="text-danger">*</span></label>
        <select class="mp-form-control" id="back_cylinder" name="cylinder_id" required>
          <option value="0">Choose the shell</option>
          <?php foreach($home as $cylinder): ?>
          <option value="<?= (int)$cylinder->id; ?>" data-held="<?= htmlspecialchars($held[(int)$cylinder->id]); ?>">
            <?= htmlspecialchars($cylinder->cylinder_code); ?> &middot; <?= mp_format_money($held[(int)$cylinder->id]); ?> held</option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mp-form-group">
        <label for="back_customer">Pay it back to <span class="text-danger">*</span></label>
        <select class="mp-form-control" id="back_customer" name="customer_id" required>
          <option value="0">Choose the customer</option>
          <?php foreach($customers as $customer): ?>
          <option value="<?= (int)$customer->id; ?>"><?= htmlspecialchars($customer->name); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="mp-form-group">
        <label for="back_amount">Amount <span class="text-danger">*</span></label>
        <input class="mp-form-control" type="number" id="back_amount" name="amount" step="0.01" min="0.01" required>
        <span class="loc-sub" id="back_hint">Pick a shell to see what is held against it.</span>
      </div>
      <div class="mp-form-group">
        <label for="back_mode">Paid by</label>
        <select class="mp-form-control" id="back_mode" name="payment_mode">
          <?php foreach($modes as $mode): ?><option value="<?= htmlspecialchars($mode); ?>"><?= htmlspecialchars($mode); ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="mp-form-group full">
        <label for="back_note">Note</label>
        <input class="mp-form-control" type="text" id="back_note" name="note" maxlength="255" placeholder="Shell returned sound, deposit paid in cash">
      </div>
      <div class="mp-form-actions">
        <button type="submit" class="mp-btn-primary"><i class="fa fa-unlock"></i> Refund it</button>
      </div>
      <p class="gas-note">The money only goes back to whoever paid it, and never more than is held.</p>
      <?= form_close(); ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="mp-card-form gas-card">
  <div class="mp-card-head"><h3>Deposits owed right now</h3><a class="mp-qa-btn" href="<?= base_url('oil_gas/cylinders?site='.(int)$site_id); ?>">Cylinder register</a></div>
  <div class="mp-card-body" style="padding:0!important">
    <?php if(empty($open)): ?>
      <p class="gas-empty">We hold nobody's money. Every shell is either in the shop with its deposit paid back, or out on a deal with no deposit taken.</p>
    <?php else: ?>
    <div class="mp-dt-scroll">
      <table class="loc-table" style="width:100%;border-collapse:collapse">
        <thead><tr><th>Shell</th><th>Customer</th><th><?= htmlspecialchars($site_label); ?></th><th>Held</th><th>Where the shell is</th></tr></thead>
        <tbody>
          <?php foreach($open as $row): ?>
          <tr>
            <td><strong><?= htmlspecialchars($row->cylinder_code); ?></strong></td>
            <td><?= htmlspecialchars(trim((string)$row->customer_name) ?: 'a customer'); ?></td>
            <td><div class="loc-sub"><?= htmlspecialchars($row->warehouse_name); ?></div></td>
            <td class="gas-qty"><?= mp_format_money($row->held); ?></td>
            <td>
              <?php
                $where = array('in_shop'=>'Back in the shop — ready to refund','out'=>'Out with the customer','lost'=>'Written off as lost','scrapped'=>'Off the road');
                $state = (string)$row->status;
              ?>
              <span class="gas-status <?= $state === 'in_shop' ? 'ok' : ($state === 'out' ? 'transit' : 'held'); ?>"><?= htmlspecialchars($where[$state] ?? 'Moved'); ?></span>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<div class="mp-card-form gas-card">
  <div class="mp-card-head"><h3>Last deposit movements</h3></div>
  <div class="mp-card-body" style="padding:0!important">
    <?php if(empty($ledger)): ?>
      <p class="gas-empty">No deposit has been taken or refunded here yet.</p>
    <?php else: ?>
    <div class="mp-dt-scroll">
      <table class="loc-table" style="width:100%;border-collapse:collapse">
        <thead><tr><th>Date</th><th>Shell</th><th>Customer</th><th>Booked</th><th>Amount</th><th>By</th></tr></thead>
        <tbody>
          <?php foreach($ledger as $row): ?>
          <tr>
            <td><strong><?= show_date($row->deposit_date); ?></strong><div class="loc-sub"><?= htmlspecialchars($row->warehouse_name); ?></div></td>
            <td><strong><?= htmlspecialchars($row->cylinder_code); ?></strong></td>
            <td><?= htmlspecialchars(trim((string)$row->customer_name) ?: 'a customer'); ?></td>
            <td><span class="gas-status <?= $row->event_type === 'paid' ? 'held' : 'ok'; ?>"><?= $row->event_type === 'paid' ? 'Money held' : 'Money paid back'; ?></span></td>
            <td class="gas-qty"><?= mp_format_money($row->amount); ?></td>
            <td><div class="loc-sub"><?= htmlspecialchars($who($row->created_by)); ?><?= $row->payment_mode ? ' · '.htmlspecialchars($row->payment_mode) : ''; ?></div>
              <?php if($row->note): ?><div class="loc-sub"><?= htmlspecialchars($row->note); ?></div><?php endif; ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
  $(function(){
    $('.oil_gas-deposits-active-li').addClass('active');
    /* Refunding more than is held is refused by the books, so stop it at the form. */
    $('#back_cylinder').on('change', function(){
      var option = $(this).find('option:selected');
      var held = parseFloat(option.data('held')) || 0;
      if(held > 0){
        $('#back_amount').val(held.toFixed(2)).attr('max', held);
        $('#back_hint').text('Held against this shell: ' + held.toFixed(2) + '. Refund less if only part of it is being returned.');
      }else{
        $('#back_amount').val('').removeAttr('max');
        $('#back_hint').text('Pick a shell to see what is held against it.');
      }
    });
  });
</script>
