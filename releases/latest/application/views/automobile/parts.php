<?php
  $vehicle_label = '';
  if (!empty($vehicle)) {
    $vehicle_label = trim($vehicle->make . ' ' . $vehicle->model);
    if (!empty($vehicle->year)) $vehicle_label .= ' (' . (int) $vehicle->year . ')';
    if (!empty($vehicle->license_plate)) $vehicle_label .= ' · ' . $vehicle->license_plate;
  }
  $searched = trim((string) ($ctx['make'] ?? '')) !== '';
  $result_count = is_countable($items) ? count($items) : 0;
?>
<div class="mp-page-head">
  <div>
    <h2><?= $vehicle_label !== '' ? 'Parts for ' . htmlspecialchars($vehicle_label) : 'Find Parts by Vehicle'; ?></h2>
    <div class="mp-page-sub">Parts your store stocks that are recorded as fitting this vehicle</div>
  </div>
  <div style="margin-left:auto; display:flex; gap:8px;">
    <?php if (!empty($vehicle)): ?>
      <a href="<?= base_url('automobile/history/' . (int) $vehicle->customer_id); ?>" class="mp-btn">
        <i class="fa fa-clock-o"></i> Vehicle History
      </a>
    <?php endif; ?>
    <a href="<?= base_url('automobile/list'); ?>" class="mp-btn"><i class="fa fa-arrow-left"></i> Back to Vehicles</a>
  </div>
</div>

<div class="mp-card" style="margin-top:16px;">
  <div class="mp-card-body">
    <form method="get" action="<?= base_url('automobile/parts'); ?>">
      <div style="display:flex; gap:14px; flex-wrap:wrap; align-items:flex-end;">
        <div class="form-group" style="flex:1 1 180px;">
          <label for="parts_make">Make</label>
          <input class="form-control" id="parts_make" name="make" type="text" value="<?= htmlspecialchars((string) ($ctx['make'] ?? '')); ?>" placeholder="e.g. Toyota">
        </div>
        <div class="form-group" style="flex:1 1 180px;">
          <label for="parts_model">Model</label>
          <input class="form-control" id="parts_model" name="model" type="text" value="<?= htmlspecialchars((string) ($ctx['model'] ?? '')); ?>" placeholder="e.g. Corolla">
        </div>
        <div class="form-group" style="flex:0 1 110px;">
          <label for="parts_year">Year</label>
          <input class="form-control" id="parts_year" name="year" type="text" inputmode="numeric" value="<?= htmlspecialchars((string) ($ctx['year'] ?? '')); ?>" placeholder="2016">
        </div>
        <div class="form-group" style="flex:1 1 160px;">
          <label for="parts_engine">Engine <span class="text-muted">(only when you type it)</span></label>
          <input class="form-control" id="parts_engine" name="engine" type="text" value="<?= htmlspecialchars((string) ($ctx['engine'] ?? '')); ?>" placeholder="e.g. 2.0 D-4D">
        </div>
        <div class="form-group" style="flex:0 0 auto;">
          <button type="submit" class="mp-btn green"><i class="fa fa-search"></i> Search Parts</button>
        </div>
      </div>
      <p class="text-muted" style="margin:12px 0 0;">Blank fields widen the search: leaving Model or Year empty shows every part recorded for the make you typed.</p>
    </form>
  </div>
</div>

<?php if (!$searched): ?>
  <div class="mp-card" style="margin-top:16px;">
    <div class="mp-card-body">
      <p class="text-muted" style="margin:0;">Type a vehicle above, or open a customer's vehicle and use <b>Parts for this vehicle</b>.</p>
    </div>
  </div>
<?php elseif ($result_count === 0): ?>
  <div class="mp-card" style="margin-top:16px;">
    <div class="mp-card-body">
      <h3 style="margin:0 0 6px; font-size:15px;">Nothing recorded as fitting this vehicle yet</h3>
      <p class="text-muted" style="margin:0;">
        Fitments are written on the part: open <a href="<?= base_url('items'); ?>"><?= htmlspecialchars(function_exists('mp_label') ? mp_label('item') : 'Item'); ?></a>,
        pick the part, and list the vehicles it fits under <b>Fits These Vehicles</b>. Parts will show here as soon as you save one.
      </p>
    </div>
  </div>
<?php else: ?>
  <div class="mp-table-wrap box" style="margin-top:16px;">
    <table class="table table-striped table-hover">
      <thead>
        <tr>
          <th>Part</th>
          <th>Part No</th>
          <th>Fits</th>
          <th class="text-right">Price</th>
          <th class="text-right">In stock</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($items as $it):
          $iid = (int) $it->id;
          $rows = $fitments[$iid] ?? [];
          $fits = [];
          foreach ($rows as $f) {
            $phrase = mb_convert_case(trim($f->make . ' ' . (string) $f->model), MB_CASE_TITLE);
            if (!empty($f->year_from) || !empty($f->year_to)) {
              $phrase .= ' ' . ($f->year_from ?: 'from') . '–' . ($f->year_to ?: 'later');
            }
            if (!empty($f->note)) $phrase .= ' — ' . $f->note;
            $fits[] = $phrase;
          }
          $qty = $stock[$iid] ?? 0;
        ?>
        <tr>
          <td>
            <a href="<?= base_url('items/update/' . $iid); ?>"><?= htmlspecialchars($it->item_name); ?></a>
            <div class="text-muted" style="font-size:12px;"><?= htmlspecialchars((string) $it->item_code); ?></div>
          </td>
          <td>
            <?= htmlspecialchars((string) ($it->manufacturer_part_no ?: '—')); ?>
            <?php if (!empty($it->oem_reference)): ?>
              <div class="text-muted" style="font-size:12px;">OEM <?= htmlspecialchars((string) $it->oem_reference); ?></div>
            <?php endif; ?>
          </td>
          <td>
            <?php if (empty($fits)): ?>
              <span class="text-muted">—</span>
            <?php else: foreach (array_slice($fits, 0, 3) as $line): ?>
              <div style="font-size:13px;"><?= htmlspecialchars($line); ?></div>
            <?php endforeach; if (count($fits) > 3): ?>
              <div class="text-muted" style="font-size:12px;">+ <?= count($fits) - 3; ?> more</div>
            <?php endif; endif; ?>
          </td>
          <td class="text-right"><?= number_format((float) $it->sales_price, 2); ?></td>
          <td class="text-right">
            <?php if ($qty > 0): ?>
              <?= rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.'); ?>
            <?php else: ?>
              <span class="label label-warning">Out of stock</span>
            <?php endif; ?>
          </td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
    <?php if ($result_count >= 100): ?>
      <div class="box-footer text-muted" style="font-size:13px;">Showing the first 100 matches — add the model or year to narrow it down.</div>
    <?php endif; ?>
  </div>
<?php endif; ?>
