<?php
$who = function($name){ $name = trim((string)$name); if($name === '') return '—'; $parts = preg_split('/\s+/', $name); return $parts[0]; };
$gap = (float)$refilled_today - (float)$sold_today;
$this->load->view('mobile/gas_head', [
	'gas_title' => 'LPG Board',
	'gas_sub' => 'What the scale said against what the till rang up, the shells that are out, and the money held against them',
	'store_name' => $store_name ?? ($SITE_TITLE ?? 'MartPoint'),
	'theme_link' => $theme_link,
]);
$this->load->view('mobile/gas_sites', ['sites'=>$sites, 'site_id'=>$site_id, 'gas_route'=>'lpg']);
?>

<div class='stats'>
  <div class='stat'><strong><?= (int)$counts['full']; ?></strong><span>Full shells ready to go</span></div>
  <div class='stat <?= $counts['out'] ? 'watch' : ''; ?>"><strong><?= (int)$counts['out']; ?></strong><span>Shells out with customers</span></div>
  <div class='stat'><strong><span class='litre'><?= mp_format_litres($refilled_today); ?></span> kg</strong><span>Weighed into shells today</span></div>
  <div class='stat <?= abs($gap) > 0.0001 ? 'alert' : ''; ?>"><strong><span class='litre'><?= mp_format_litres($sold_today); ?></span> kg</strong><span>The till rang up today</span></div>
  <?php if($deposit_flag): ?>
  <div class='stat <?= $counts['deposits'] > 0 ? 'watch' : ''; ?>"><strong><?= mp_format_money($counts['deposits']); ?></strong><span>Deposits we hold</span></div>
  <?php endif; ?>
  <div class='stat <?= $counts['waiting'] ? 'alert' : ''; ?>"><strong><?= (int)$counts['waiting']; ?></strong><span>Fills with no receipt in 7 days</span></div>
</div>

<?php if($site_id): ?>
  <?php if(abs($gap) > 0.0001): ?>
  <div class='callout warn'>The scale has put <span class='litre'><?= mp_format_litres($refilled_today); ?></span> kg into shells here today and the till has rang up <span class='litre'><?= mp_format_litres($sold_today); ?></span> kg. <?= $gap > 0
    ? 'That leaves ' . mp_format_litres($gap) . ' kg of gas filled but not yet sold.'
    : 'The till is ' . mp_format_litres(abs($gap)) . ' kg ahead of any recorded fill.'; ?> Nothing here moves stock — the till is what does.</div>
  <?php endif; ?>
<?php else: ?>
  <div class='callout info'>Choose a <?= htmlspecialchars(strtolower($site_label)); ?> above to see its own weighings and shells.</div>
<?php endif; ?>

<div class='card'>
  <div class='section-title'>LPG work</div>
  <div class='actions'>
    <?php if($refill_flag): ?><a class='action' href='<?= base_url('oil_gas/refill?site='.(int)$site_id); ?>'><i class='fa fa-balance-scale'></i> Weigh a fill</a><?php endif; ?>
    <?php if($tracking_flag): ?><a class='action' href='<?= base_url('oil_gas/cylinders?site='.(int)$site_id); ?>'><i class='fa fa-circle-o'></i> Cylinder register</a><?php endif; ?>
    <?php if($tracking_flag): ?><a class='action' href='<?= base_url('oil_gas/exchange?site='.(int)$site_id); ?>'><i class='fa fa-exchange'></i> Exchange</a><?php endif; ?>
    <?php if($deposit_flag): ?><a class='action' href='<?= base_url('oil_gas/deposits?site='.(int)$site_id); ?>'><i class='fa fa-money'></i> Deposits</a><?php endif; ?>
  </div>
  <p class='note'>A cylinder is filled by weight: the shell is weighed empty, weighed full, and the difference is the gas the customer pays for. This screen proves that weighing against the till — it never books the kilograms a second time.</p>
</div>

<div class='card'>
  <div class='section-title'>Shells at this <?= htmlspecialchars(strtolower($site_label)); ?></div>
  <?php if(empty($shells)): ?>
    <div class='empty'><i class='fa fa-circle-o'></i>Nothing is registered here yet.<br>Add the shells with the number, size and empty weight stamped on them.</div>
  <?php else: foreach($shells as $cylinder): ?>
    <div class='row'>
      <div class='name'><?= htmlspecialchars($cylinder->cylinder_code); ?></div>
      <div class='meta'><?= mp_format_litres($cylinder->size_kg); ?> kg of gas, weighs empty <span class='litre'><?= mp_format_litres($cylinder->tare_kg); ?></span> kg
        · held by <?= htmlspecialchars(trim((string)$cylinder->customer_name) ?: 'the shop'); ?></div>
      <?php if($cylinder->status === 'out'): ?><span class='flag held'>out</span>
      <?php elseif($cylinder->status === 'lost'): ?><span class='flag held'>lost</span>
      <?php elseif($cylinder->status === 'scrapped'): ?><span class='flag transit'>off the road</span>
      <?php elseif((int)$cylinder->is_full === 1): ?><span class='flag open'>full, ready</span>
      <?php else: ?><span class='flag ok'>empty here</span><?php endif; ?>
    </div>
  <?php endforeach; endif; ?>
</div>

<div class='card'>
  <div class='section-title'>What the scale said today</div>
  <?php if(empty($refills)): ?>
    <div class='empty'><i class='fa fa-balance-scale'></i>Nothing has been weighed into a shell here today.</div>
  <?php else: foreach($refills as $fill): ?>
    <div class='row'>
      <div class='name'><?= htmlspecialchars((string)$fill->cylinder_code); ?></div>
      <div class='meta'>gross <span class='litre'><?= mp_format_litres($fill->gross_kg); ?></span> kg
        · shell <span class='litre'><?= mp_format_litres($fill->tare_kg); ?></span> kg
        · gas <span class='litre'><?= mp_format_litres($fill->net_kg); ?></span> kg
        · <?= htmlspecialchars($who($fill->created_by)); ?></div>
      <?php if($fill->linked_sale_id): ?><span class='flag ok'>rung up</span>
      <?php elseif(!empty($fill->sale_reference)): ?><span class='flag transit'>receipt <?= htmlspecialchars($fill->sale_reference); ?> not found</span>
      <?php else: ?><span class='flag held'>no receipt yet</span><?php endif; ?>
    </div>
  <?php endforeach; endif; ?>
</div>

<div class='card'>
  <div class='section-title'>Fills the till has not confirmed</div>
  <?php if(empty($waiting)): ?>
    <div class='empty'><i class='fa fa-check-circle'></i>Every fill in the last week has a receipt against it.</div>
  <?php else: foreach($waiting as $fill): ?>
    <div class='row'>
      <div class='name'><?= htmlspecialchars((string)$fill->cylinder_code); ?> · <span class='litre'><?= mp_format_litres($fill->net_kg); ?></span> kg</div>
      <div class='meta'><?= show_date($fill->refill_date); ?> at <?= htmlspecialchars((string)$fill->warehouse_name); ?>
        · <?= !empty($fill->sale_reference) ? 'receipt ' . htmlspecialchars($fill->sale_reference) . ' not found' : 'no receipt entered'; ?></div>
    </div>
  <?php endforeach; endif; ?>
</div>

<?php if($deposit_flag): ?>
<div class='card'>
  <div class='section-title'>Money held against shells</div>
  <?php if(empty($deposits)): ?>
    <div class='empty'><i class='fa fa-money'></i>No deposit is outstanding.</div>
  <?php else: foreach($deposits as $row): ?>
    <div class='row'>
      <div class='name'><?= htmlspecialchars(trim((string)$row->customer_name) ?: 'a customer'); ?></div>
      <div class='meta'>shell <?= htmlspecialchars((string)$row->cylinder_code); ?> · <span class='litre'><?= mp_format_money($row->held); ?></span> held</div>
      <span class='flag <?= $row->status === 'in_shop' ? 'ok' : 'transit'; ?>"><?= $row->status === 'in_shop' ? 'the shell is back — it can be paid back' : 'the shell is still out'; ?></span>
    </div>
  <?php endforeach; endif; ?>
</div>
<?php else: ?>
<div class='card'>
  <div class='section-title'>How a shell is proved</div>
  <p class='note' style='margin-top:0;'>A shell leaves with a named customer and comes back with a weighing. Deposits are switched off for this business, so custody is the only hold — which is why the register counts every shell and every one of its movements.</p>
  <p class='note'>To hold refundable money against a shell instead, switch on Refundable Cylinder Deposits under Business Profile &rarr; Capabilities.</p>
</div>
<?php endif; ?>

<?php $this->load->view('mobile/gas_foot', ['gas_nav'=>'more']); ?>
