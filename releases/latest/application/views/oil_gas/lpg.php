<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $this->load->view('oil_gas/_styles'); ?>
<?php
$site_name = '';
foreach($sites as $site) if((int)$site->id === (int)$site_id) $site_name = $site->warehouse_name;
// History feeds read as people, not accounts: first name only, never a username.
$who = function($name){ $name = trim((string)$name); if($name === '') return '—'; $parts = preg_split('/\s+/', $name); return $parts[0]; };
$gap = (float)$refilled_today - (float)$sold_today;
?>

<div class="gas-hero">
  <div>
    <h2>LPG &mdash; <?= htmlspecialchars($site_name ?: $site_label); ?></h2>
    <p>Cylinders are filled by weight: the shell is weighed empty, weighed full, and the difference is the gas. The till already took that gas out of stock, so this page only proves the weighing against the receipt — it never books kilograms twice.</p>
  </div>
  <div class="gas-hero-actions">
    <?php if($refill_flag): ?><a href="<?= base_url('oil_gas/refill?site='.(int)$site_id); ?>" class="gas-btn"><i class="fa fa-balance-scale"></i> Weigh a fill</a><?php endif; ?>
    <?php if($tracking_flag): ?><a href="<?= base_url('oil_gas/cylinders?site='.(int)$site_id); ?>" class="gas-btn"><i class="fa fa-circle-o"></i> Cylinder register</a>
    <a href="<?= base_url('oil_gas/exchange?site='.(int)$site_id); ?>" class="gas-btn"><i class="fa fa-exchange"></i> Exchange</a><?php endif; ?>
    <?php if($deposit_flag): ?><a href="<?= base_url('oil_gas/deposits?site='.(int)$site_id); ?>" class="gas-btn"><i class="fa fa-money"></i> Deposits</a><?php endif; ?>
  </div>
</div>

<?php if(!empty($sites)): ?>
<div class="gas-sites">
  <?php foreach($sites as $site): ?>
    <a href="<?= base_url('oil_gas/lpg?site='.(int)$site->id); ?>" class="gas-site <?= (int)$site->id === (int)$site_id ? 'active' : ''; ?>"><?= htmlspecialchars($site->warehouse_name); ?></a>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<div class="gas-kpis">
  <div class="gas-kpi"><strong><?= (int)$counts['full']; ?></strong><span>Full shells ready to go</span><small>of <?= (int)$counts['shells']; ?> shell<?= $counts['shells'] === 1 ? '' : 's'; ?> on the register</small></div>
  <div class="gas-kpi <?= $counts['out'] ? 'watch' : ''; ?>"><strong><?= (int)$counts['out']; ?></strong><span>Shells out with customers</span><small>Somebody is answerable for every one of them</small></div>
  <div class="gas-kpi"><strong><?= mp_format_litres($refilled_today); ?> kg</strong><span>Weighed into shells today</span><small>At this <?= htmlspecialchars(strtolower($site_label)); ?></small></div>
  <div class="gas-kpi <?= abs($gap) > 0.0001 ? 'alert' : ''; ?>"><strong><?= mp_format_litres($sold_today); ?> kg</strong><span>The till rang up today</span><small><?= abs($gap) > 0.0001
      ? ($gap > 0 ? mp_format_litres($gap).' kg filled but not yet on a receipt' : mp_format_litres(abs($gap)).' kg sold without a recorded fill')
      : 'The scale and the till agree'; ?></small></div>
  <?php if($deposit_flag): ?>
  <div class="gas-kpi <?= $counts['deposits'] > 0 ? 'watch' : ''; ?>"><strong><?= mp_format_money($counts['deposits']); ?></strong><span>Deposits we hold</span><small>Money owed back when shells come home</small></div>
  <?php endif; ?>
</div>

<div class="gas-two">
  <div class="mp-card-form gas-card">
    <div class="mp-card-head">
      <h3>Shells at this <?= htmlspecialchars(strtolower($site_label)); ?></h3>
      <?php if($tracking_flag): ?><a class="mp-qa-btn" href="<?= base_url('oil_gas/cylinders?site='.(int)$site_id); ?>">Open the register</a><?php endif; ?>
    </div>
    <div class="mp-card-body" style="padding:0!important">
      <?php if(empty($shells)): ?>
        <p class="gas-empty">No cylinder is registered here yet. Put the shells on the register with the number stamped on them, their size and their empty weight — the empty weight is what turns a gross reading into a sale.</p>
      <?php else: ?>
      <div class="mp-dt-scroll">
        <table class="loc-table" style="width:100%;border-collapse:collapse">
          <thead><tr><th>Shell</th><th>Size</th><th>Weighs empty</th><th>State</th><th>Who holds it</th></tr></thead>
          <tbody>
            <?php foreach($shells as $cylinder): ?>
            <tr>
              <td><strong><?= htmlspecialchars($cylinder->cylinder_code); ?></strong>
                <div class="loc-sub"><?= htmlspecialchars((string)$cylinder->product ?: 'No product yet'); ?></div></td>
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
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <div class="mp-card-form gas-card">
    <div class="mp-card-head">
      <h3>What the scale said today</h3>
      <?php if($refill_flag): ?><a class="mp-qa-btn" href="<?= base_url('oil_gas/refill?site='.(int)$site_id); ?>">Weigh a fill</a><?php endif; ?>
    </div>
    <div class="mp-card-body" style="padding:0!important">
      <?php if(empty($refills)): ?>
        <p class="gas-empty">Nothing has been weighed into a shell here today. Record each fill as it happens — a day of refills remembered by hand is a day of gas nobody can account for.</p>
      <?php else: ?>
      <div class="mp-dt-scroll">
        <table class="loc-table" style="width:100%;border-collapse:collapse">
          <thead><tr><th>Shell</th><th>Gross</th><th>Shell weight</th><th>Gas</th><th>Receipt</th></tr></thead>
          <tbody>
            <?php foreach($refills as $fill): ?>
            <tr>
              <td><strong><?= htmlspecialchars((string)$fill->cylinder_code); ?></strong>
                <div class="loc-sub"><?= $fill->refill_time ? htmlspecialchars($fill->refill_time) : ''; ?> · <?= htmlspecialchars($who($fill->created_by)); ?></div></td>
              <td class="gas-qty"><?= mp_format_litres($fill->gross_kg); ?> kg</td>
              <td class="gas-qty"><?= mp_format_litres($fill->tare_kg); ?> kg</td>
              <td class="gas-qty"><span class="gas-litre"><?= mp_format_litres($fill->net_kg); ?></span> kg</td>
              <td>
                <?php if($fill->linked_sale_id): ?><span class="gas-status ok">rung up</span>
                <?php elseif(!empty($fill->sale_reference)): ?><span class="gas-status held">receipt <?= htmlspecialchars($fill->sale_reference); ?> not found</span>
                <?php else: ?><span class="gas-status held">no receipt yet</span><?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<div class="gas-two">
  <div class="mp-card-form gas-card">
    <div class="mp-card-head"><h3>Fills the till has not confirmed</h3></div>
    <div class="mp-card-body" style="padding:0!important">
      <?php if(empty($waiting)): ?>
        <p class="gas-empty">Every fill in the last week has a till receipt against it. That is the proof a cooking-gas business needs: gas weighed out and gas paid for are the same number.</p>
      <?php else: ?>
      <div class="mp-dt-scroll">
        <table class="loc-table" style="width:100%;border-collapse:collapse">
          <thead><tr><th>Shell</th><th>Day</th><th>Gas</th><th>Site</th><th>Receipt</th></tr></thead>
          <tbody>
            <?php foreach($waiting as $fill): ?>
            <tr>
              <td><strong><?= htmlspecialchars((string)$fill->cylinder_code); ?></strong></td>
              <td><?= show_date($fill->refill_date); ?></td>
              <td class="gas-qty"><span class="gas-litre"><?= mp_format_litres($fill->net_kg); ?></span> kg</td>
              <td><div class="loc-sub"><?= htmlspecialchars((string)$fill->warehouse_name); ?></div></td>
              <td><div class="loc-sub"><?= !empty($fill->sale_reference) ? htmlspecialchars($fill->sale_reference) : 'none entered' ?></div></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <p class="gas-note" style="padding:0 16px 14px;">Ring these up at the till, or enter the receipt number on the refill if it was already sold. Nothing here moves stock.</p>
      <?php endif; ?>
    </div>
  </div>

  <?php if($deposit_flag): ?>
  <div class="mp-card-form gas-card">
    <div class="mp-card-head">
      <h3>Money held against shells</h3>
      <a class="mp-qa-btn" href="<?= base_url('oil_gas/deposits?site='.(int)$site_id); ?>">Deposits</a>
    </div>
    <div class="mp-card-body" style="padding:0!important">
      <?php if(empty($deposits)): ?>
        <p class="gas-empty">No deposit is outstanding. A deposit is money we hold against a shell that is out at a customer — it is never counted as gas or as a sale.</p>
      <?php else: ?>
      <div class="mp-dt-scroll">
        <table class="loc-table" style="width:100%;border-collapse:collapse">
          <thead><tr><th>Customer</th><th>Shell</th><th>Held</th></tr></thead>
          <tbody>
            <?php foreach($deposits as $row): ?>
            <tr>
              <td><strong><?= htmlspecialchars(trim((string)$row->customer_name) ?: 'a customer'); ?></strong>
                <div class="loc-sub"><?= $row->status === 'in_shop' ? 'the shell is back — it can be paid back' : 'the shell is still out'; ?></div></td>
              <td><div class="loc-sub"><?= htmlspecialchars((string)$row->cylinder_code); ?></div></td>
              <td class="gas-qty"><span class="gas-litre"><?= mp_format_money($row->held); ?></span></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <?php endif; ?>
    </div>
  </div>
  <?php else: ?>
  <div class="mp-card-form gas-card">
    <div class="mp-card-head"><h3>How a shell is proved</h3></div>
    <div class="mp-card-body">
      <p class="gas-note">A shell leaves with a named customer and comes back with a weighing. Deposits are switched off for this business, so custody is the only hold — which is exactly why the register counts every shell and every one of its movements.</p>
      <p class="gas-note">To hold refundable money against a shell instead, switch on Refundable Cylinder Deposits under Business Profile &rarr; Capabilities.</p>
    </div>
  </div>
  <?php endif; ?>
</div>

<script>
  $(function(){ $('.oil_gas-lpg-active-li').addClass('active'); });
</script>
