<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $this->load->view('oil_gas/_styles'); ?>
<?php
$og = is_array($report ?? null) ? $report : array('sites'=>array(),'total'=>array(),'sections'=>array());
$og_sites_rows = is_array($og['sites'] ?? null) ? $og['sites'] : array();
$og_total = is_array($og['total'] ?? null) ? $og['total'] : array();
$og_sections = is_array($og['sections'] ?? null) ? $og['sections'] : array();

/* The card table lives in one helper so the phone screen can never quote a
 * different figure for the same question. */
$og_cards = mp_oil_gas_report_cards();

$og_show = array();
foreach($og_cards as $key => $card) if(isset($og_sections[$key])) $og_show[$key] = $card;
$og_named = 0;
foreach($og_sites_rows as $id => $row) if((int)$id !== 0) $og_named++;

$og_print = 'mp_oil_gas_report_figure';
?>

<div class="gas-hero">
  <div>
    <h2>Company report &mdash; every <?= htmlspecialchars(strtolower($site_label)); ?> on one page</h2>
    <p>The bottom line of each card is the sum of the locations above it, so head office and a site can never end up quoting two different numbers. Cards only appear for the operations this company actually runs.</p>
  </div>
  <div class="gas-hero-actions">
    <a href="<?= base_url('oil_gas'); ?>" class="gas-btn"><i class="fa fa-dashboard"></i> Workspace</a>
    <?php if(!empty($sites)): ?><a href="<?= base_url('oil_gas/jobs'); ?>" class="gas-btn"><i class="fa fa-wrench"></i> Service work</a><?php endif; ?>
  </div>
</div>

<div class="mp-card-form gas-card" style="margin-bottom:20px">
  <div class="mp-card-head"><h3>Pick the period</h3></div>
  <div class="mp-card-body">
    <form method="get" action="<?= base_url('oil_gas/report'); ?>">
      <div class="gas-form" style="grid-template-columns:minmax(0,1fr) minmax(0,1fr) auto;align-items:end">
        <div class="mp-form-group" style="margin:0">
          <label for="og_from">From</label>
          <input class="mp-form-control" type="date" id="og_from" name="from" value="<?= htmlspecialchars((string)$from); ?>">
        </div>
        <div class="mp-form-group" style="margin:0">
          <label for="og_to">To</label>
          <input class="mp-form-control" type="date" id="og_to" name="to" value="<?= htmlspecialchars((string)$to); ?>">
        </div>
        <div class="mp-form-actions" style="margin:0"><button type="submit" class="mp-qa-btn blue"><i class="fa fa-calendar"></i> Show it</button></div>
      </div>
    </form>
    <p class="gas-note">Showing <?= show_date((string)$from); ?> to <?= show_date((string)$to); ?> &middot; <?= (int)$og_named; ?> location<?= $og_named === 1 ? '' : 's'; ?> with something to report<?php $og_quiet = count($sites) - $og_named; ?><?= $og_quiet > 0 ? ' · ' . (int)$og_quiet . ' of your location' . ($og_quiet === 1 ? '' : 's') . ' recorded nothing in this period' : ''; ?></p>
  </div>
</div>

<?php if(!$og_sites_rows): ?>
  <div class="mp-card-form gas-card"><div class="mp-card-body">
    <p class="gas-empty">Nothing was recorded anywhere in the company in this period. Widen the dates, or check that the sites are booked into the right location.</p>
  </div></div>
<?php endif; ?>

<?php foreach($og_show as $key => $card): ?>
<div class="mp-card-form gas-card" style="margin-bottom:20px">
  <div class="mp-card-head"><h3><?= $card['title']; ?></h3></div>
  <div class="mp-card-body" style="padding:0!important">
    <div class="mp-dt-scroll">
      <table class="loc-table" style="width:100%;border-collapse:collapse">
        <thead>
          <tr>
            <th><?= htmlspecialchars($site_label); ?></th>
            <?php foreach($card['columns'] as $column): ?><th style="text-align:right"><?= $column['label']; ?></th><?php endforeach; ?>
          </tr>
        </thead>
        <tbody>
          <?php foreach($og_sites_rows as $id => $row): ?>
          <tr>
            <td>
              <div class="loc-name"><?= htmlspecialchars((string)$row['name']); ?></div>
              <?php if((int)$id === 0): ?><div class="loc-sub">Not tied to a location yet &mdash; put it on one from the Service work screen</div>
              <?php elseif(!empty($row['town'])): ?><div class="loc-sub"><?= htmlspecialchars((string)$row['town']); ?></div><?php endif; ?>
            </td>
            <?php foreach($card['columns'] as $column): ?>
            <td style="text-align:right" class="gas-qty"><?= $og_print($column['kind'], $row[$column['key']] ?? 0); ?></td>
            <?php endforeach; ?>
          </tr>
          <?php endforeach; ?>
        </tbody>
        <tfoot>
          <tr>
            <td><strong>Whole company</strong></td>
            <?php foreach($card['columns'] as $column): ?>
            <td style="text-align:right"><strong class="gas-litre"><?= $og_print($column['kind'], $og_total[$column['key']] ?? 0); ?></strong></td>
            <?php endforeach; ?>
          </tr>
        </tfoot>
      </table>
    </div>
    <p class="gas-note" style="padding:0 18px 16px"><?= $card['blurb']; ?></p>
  </div>
</div>
<?php endforeach; ?>

<?php if(count($og_show) < count($og_cards)): ?>
<div class="mp-card-form gas-card">
  <div class="mp-card-head"><h3>Not shown, because this company does not run it</h3></div>
  <div class="mp-card-body">
    <p class="gas-note" style="margin-top:0">
      <?php $og_missing = array(); foreach($og_cards as $key => $card) if(!isset($og_sections[$key])) $og_missing[] = $card['title'];
        echo $og_missing ? htmlspecialchars(implode(', ', $og_missing)) . '. Turn those operations on under Business Profile &rarr; Capabilities and they join this report.' : 'Every operation this workspace knows about is on the page above.'; ?>
    </p>
  </div>
</div>
<?php endif; ?>
