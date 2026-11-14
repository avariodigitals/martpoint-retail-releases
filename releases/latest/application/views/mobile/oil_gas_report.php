<?php
$og = is_array($report ?? null) ? $report : array('sites'=>array(),'total'=>array(),'sections'=>array());
$og_sites_rows = is_array($og['sites'] ?? null) ? $og['sites'] : array();
$og_total = is_array($og['total'] ?? null) ? $og['total'] : array();
$og_sections = is_array($og['sections'] ?? null) ? $og['sections'] : array();

/* The same card table the desktop report prints, from one shared place, so
 * the two screens can never quote different numbers for one question. */
$og_cards = mp_oil_gas_report_cards();

$og_show = array();
foreach($og_cards as $key => $card) if(isset($og_sections[$key])) $og_show[$key] = $card;
$og_named = 0;
foreach($og_sites_rows as $id => $row) if((int)$id !== 0) $og_named++;

/* One location reads as a single line of figures, so the phone never has to
 * scroll a wide table. */
$og_line = function($row, $columns){
  $parts = array();
  foreach($columns as $column){
    $parts[] = $column['label'].': '.mp_oil_gas_report_figure($column['kind'], $row[$column['key']] ?? 0);
  }
  return htmlspecialchars(implode(' · ', $parts), ENT_QUOTES, 'UTF-8');
};

$this->load->view('mobile/gas_head', [
  'gas_title' => 'Company Report',
  'gas_sub' => 'Every ' . strtolower($site_label ?? 'location') . ' on one page, with a company line that is their sum',
  'store_name' => $store_name ?? ($SITE_TITLE ?? 'MartPoint'),
  'theme_link' => $theme_link,
]);
?>

<div class='stats'>
  <div class='stat'><strong><?= (int)$og_named; ?></strong><span><?= htmlspecialchars(strtolower($site_label ?? 'location')); ?>s with something to report</span></div>
  <div class='stat'><strong><?= count($og_show); ?></strong><span>Operation<?= count($og_show) === 1 ? '' : 's'; ?> on this page</span></div>
</div>

<div class='card'>
  <div class='section-title'>Pick the period</div>
  <form method='get' action='<?= base_url('oil_gas/report'); ?>'>
    <div class='form-row'>
      <div class='form-group'>
        <label>From</label>
        <input type='date' name='from' value="<?= htmlspecialchars((string)$from); ?>">
      </div>
      <div class='form-group'>
        <label>To</label>
        <input type='date' name='to' value="<?= htmlspecialchars((string)$to); ?>">
      </div>
    </div>
    <button type='submit' class='btn btn-primary'><i class='fa fa-calendar'></i> Show it</button>
  </form>
  <p class='note'>Showing <?= show_date((string)$from); ?> to <?= show_date((string)$to); ?><?php $og_quiet = max(0, count($sites) - $og_named); ?><?= $og_quiet > 0 ? ' · ' . (int)$og_quiet . ' of your location' . ($og_quiet === 1 ? '' : 's') . ' recorded nothing in this period' : ''; ?></p>
</div>

<?php if(!$og_sites_rows): ?>
  <div class='callout info'>Nothing was recorded anywhere in the company in this period. Widen the dates, or check that the sites are booked into the right location.</div>
<?php endif; ?>

<?php foreach($og_show as $key => $card): ?>
<div class='card'>
  <div class='section-title'><?= $card['title']; ?></div>
  <?php foreach($og_sites_rows as $id => $row): ?>
    <div class='row'>
      <div class='name'><?= htmlspecialchars((string)$row['name']); ?>
        <?php if((int)$id !== 0 && !empty($row['town'])): ?>
        <span class='flag open'><?= htmlspecialchars((string)$row['town']); ?></span>
        <?php endif; ?></div>
      <div class='meta'><?= $og_line($row, $card['columns']); ?></div>
      <?php if((int)$id === 0): ?><div class='meta'>Not tied to a location yet &mdash; put it on one from the Service work screen.</div><?php endif; ?>
    </div>
  <?php endforeach; ?>
  <div class='row'>
    <div class='name'>Whole company</div>
    <div class='meta'><?= $og_line($og_total, $card['columns']); ?></div>
  </div>
  <p class='note'><?= $card['blurb']; ?></p>
</div>
<?php endforeach; ?>

<?php if(count($og_show) < count($og_cards)): ?>
<div class='card'>
  <div class='section-title'>Not shown, because this company does not run it</div>
  <p class='note'>
    <?php $og_missing = array(); foreach($og_cards as $key => $card) if(!isset($og_sections[$key])) $og_missing[] = $card['title'];
      echo $og_missing ? htmlspecialchars(implode(', ', $og_missing)) . '. Turn those operations on under Business Profile &rarr; Capabilities and they join this report.' : 'Every operation this workspace knows about is on the page above.'; ?>
  </p>
</div>
<?php endif; ?>

<div class='actions' style='margin-bottom:12px'>
  <a class='action' href='<?= base_url('oil_gas'); ?>'><i class='fa fa-dashboard'></i> Workspace</a>
  <?php if(!empty($sites)): ?><a class='action' href='<?= base_url('oil_gas/jobs'); ?>'><i class='fa fa-wrench'></i> Service work</a><?php endif; ?>
</div>

<?php $this->load->view('mobile/gas_foot', ['gas_nav'=>'more']); ?>
