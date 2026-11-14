<?php defined('BASEPATH') OR exit('No direct script access allowed'); ?>
<link rel="stylesheet" href="<?= base_url('theme/dist/css/pharmacy_directory.css?v='.filemtime(FCPATH.'theme/dist/css/pharmacy_directory.css')); ?>">
<?php
$qty = function ($value) { return function_exists('format_qty') ? format_qty((float)$value) : number_format((float)$value, 2); };
$money = function ($value) { return function_exists('store_number_format') ? store_number_format((float)$value) : number_format((float)$value, 2); };
$day = function ($value) { return $value ? show_date(substr((string)$value, 0, 10)) : '<span style="color:#94a3b8;">none</span>'; };
$link = function ($override) use ($tab, $filters) {
    $query = array_merge([
        'report' => $tab,
        'warehouse' => (int)$filters['warehouse'],
        'q' => (string)$filters['search'],
        'horizon' => (int)$filters['horizon'],
        'bucket' => (string)$filters['bucket'],
        'show' => (string)$filters['show'] === 'all' ? 'all' : '',
        'from' => (string)$filters['from'],
        'to' => (string)$filters['to'],
        'page' => 1,
    ], (array)$override);
    if ((int)($query['page'] ?? 1) <= 1) unset($query['page']);
    foreach ($query as $key => $value) {
        if ($key === 'report' || $key === 'horizon' || $key === 'from' || $key === 'to') continue;
        if ($value === '' || $value === 0 || $value === null) unset($query[$key]);
    }
    return base_url('batch_reports?' . http_build_query($query));
};
$label = ['hold' => 'On the shelf', 'expiring' => 'Running out of date', 'sold' => 'Sold by batch', 'ageing' => 'How long they have sat'];
?>
<div class="pharma-workspace">
  <header class="pharma-hero">
    <div>
      <span class="pharma-kicker"><span class="pharma-pulse"></span> Stock ledger</span>
      <h1>Batch ledger reports</h1>
      <p>Read straight off the batches — what you hold, when each one runs out, which sale it left on, and how long the rest have waited.</p>
    </div>
    <div class="pharma-hero-actions">
      <?php if(empty($pharmacy_mobile)): ?>
        <?php $this->load->view('components/export_btn', ['tableId' => 'batch-report']); ?>
      <?php endif; ?>
      <a class="pharma-btn pharma-btn-light" href="<?= base_url('batch_reconciliation'); ?>"><i class="fa fa-balance-scale"></i> Reconcile</a>
      <a class="pharma-btn pharma-btn-light" href="<?= base_url(!empty($pharmacy_mobile) ? 'mobile/more' : 'dashboard'); ?>"><i class="fa fa-th-large"></i> More tools</a>
    </div>
  </header>

  <?php if(!$ready): ?>
    <div class="pharma-empty"><i class="fa fa-exclamation-triangle"></i><strong>This report is not available yet.</strong><span>
      <?= $tab === 'sold' ? 'This shop has no record of which batch each sale came from yet — ask MartPoint support to apply the latest update.' : 'Ask MartPoint support to apply the latest update so the batch ledger can be reported on.'; ?></span></div>
  <?php endif; ?>

  <?php if($rotation === 'MANUAL'): ?>
    <div class="pharma-banner" style="padding:12px 16px;border-radius:10px;margin:0 0 16px;background:#FEF3E2;color:#8A5A10;border:1px solid #F3D9A9;">
      Your shop is on <strong>Manual</strong> rotation, so the counter sells from the stock figures and not from these batches.
      The batches below are what the records say is on the shelf — switch to FEFO or FIFO in Business Profile → Stock rotation
      to sell from them, and reconcile first so nothing stops at the till.
    </div>
  <?php else: ?>
    <div class="pharma-banner" style="padding:12px 16px;border-radius:10px;margin:0 0 16px;background:#E7F6EF;color:#166534;border:1px solid #B7E4C7;">
      Your shop sells from the batch ledger (<strong><?= htmlspecialchars($rotation); ?></strong>). Every quantity here is measured the
      same way the counter measures it, so a batch you can sell from here is a batch the till can hand over.
    </div>
  <?php endif; ?>

  <div style="display:flex;gap:8px;flex-wrap:wrap;margin:0 0 14px;">
    <?php foreach($tabs as $key => $meta): ?>
      <a class="btn btn-sm <?= $tab === $key ? 'btn-primary' : 'btn-default'; ?>" href="<?= $link(['report' => $key, 'page' => 1]); ?>"
        title="<?= htmlspecialchars($meta[1]); ?>"><i class="fa fa-<?= $key === 'hold' ? 'cubes' : ($key === 'expiring' ? 'calendar-times-o' : ($key === 'sold' ? 'file-text-o' : 'hourglass-half')); ?>"></i>
        <?= htmlspecialchars($meta[0]); ?></a>
    <?php endforeach; ?>
  </div>

  <div class="mp-card-form">
    <div class="mp-card-head">
      <h3><?= htmlspecialchars($label[$tab]); ?>
        <?php if((int)$row_total > count($rows)): ?>
          <small style="color:#64748b;font-weight:normal;">(<?= (int)$row_total; ?> in all)</small>
        <?php endif; ?>
      </h3>
      <div>
        <a class="btn btn-xs <?= (int)$filters['warehouse'] === 0 ? 'btn-primary' : 'btn-default'; ?>" href="<?= $link(['warehouse' => 0, 'page' => 1]); ?>">All locations</a>
        <?php foreach(($warehouses ?? []) as $w): ?>
          <a class="btn btn-xs <?= (int)$filters['warehouse'] === (int)$w->id ? 'btn-primary' : 'btn-default'; ?>"
            href="<?= $link(['warehouse' => (int)$w->id, 'page' => 1]); ?>"><?= htmlspecialchars($w->warehouse_name); ?></a>
        <?php endforeach; ?>
      </div>
    </div>

    <div class="mp-card-body">
      <form method="get" action="<?= base_url('batch_reports'); ?>" style="margin-bottom:6px;">
        <input type="hidden" name="report" value="<?= htmlspecialchars($tab); ?>">
        <?php if((int)$filters['warehouse']): ?><input type="hidden" name="warehouse" value="<?= (int)$filters['warehouse']; ?>"><?php endif; ?>
        <?php if($tab === 'expiring'): ?><input type="hidden" name="horizon" value="<?= (int)$filters['horizon']; ?>"><?php endif; ?>
        <?php if($tab === 'sold'): ?><input type="hidden" name="from" value="<?= htmlspecialchars($filters['from']); ?>"><input type="hidden" name="to" value="<?= htmlspecialchars($filters['to']); ?>"><?php endif; ?>
        <?php if($tab === 'ageing' && $filters['bucket'] !== ''): ?><input type="hidden" name="bucket" value="<?= htmlspecialchars($filters['bucket']); ?>"><?php endif; ?>
        <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end;">
          <input class="mp-form-control" type="text" name="q" maxlength="80" value="<?= htmlspecialchars($filters['search']); ?>"
            placeholder="Search a product, batch number or invoice" style="max-width:320px;">
          <?php if($tab === 'expiring'): ?>
            <label style="font-size:12px;color:#64748b;">Warn me about batches running out within
              <input class="mp-form-control" type="number" name="horizon" min="1" max="365" value="<?= (int)$filters['horizon']; ?>" style="width:78px;display:inline-block;"> days</label>
          <?php endif; ?>
          <?php if($tab === 'sold'): ?>
            <label style="font-size:12px;color:#64748b;">From <input class="mp-form-control" type="text" name="from" value="<?= htmlspecialchars($filters['from']); ?>" style="width:112px;display:inline-block;" placeholder="YYYY-MM-DD"></label>
            <label style="font-size:12px;color:#64748b;">to <input class="mp-form-control" type="text" name="to" value="<?= htmlspecialchars($filters['to']); ?>" style="width:112px;display:inline-block;" placeholder="YYYY-MM-DD"></label>
          <?php endif; ?>
          <?php if($tab === 'hold'): ?>
            <label style="font-size:12px;color:#64748b;"><input type="checkbox" name="show" value="all" <?= $filters['show'] === 'all' ? 'checked' : ''; ?>> include batches that are used up</label>
          <?php endif; ?>
          <button type="submit" class="btn btn-sm btn-default"><i class="fa fa-search"></i> Search</button>
          <?php if((string)$filters['search'] !== ''): ?><a class="btn btn-sm btn-default" href="<?= $link(['q' => '', 'page' => 1]); ?>">Clear</a><?php endif; ?>
        </div>
      </form>
      <p style="margin:6px 0 0;font-size:12px;color:#64748b;"><?= htmlspecialchars($tabs[$tab][1]); ?></p>
    </div>

    <?php if($tab === 'ageing' && !empty($buckets)): ?>
    <div class="mp-card-body" style="padding-top:0!important;">
      <div style="display:flex;gap:8px;flex-wrap:wrap;">
        <a class="btn btn-xs <?= $filters['bucket'] === '' ? 'btn-primary' : 'btn-default'; ?>" href="<?= $link(['bucket' => '', 'page' => 1]); ?>">All ages</a>
        <?php foreach($buckets as $key => $b): if (!$b['rows']) continue; ?>
          <a class="btn btn-xs <?= $filters['bucket'] === $key ? 'btn-primary' : 'btn-default'; ?>" href="<?= $link(['bucket' => $key, 'page' => 1]); ?>">
            <?= htmlspecialchars($b['label']); ?> — <?= (int)$b['rows']; ?> · <?= $qty($b['qty']); ?> · <?= $money($b['cost_value']); ?></a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <?php if(!empty($totals) && $tab !== 'sold'): ?>
    <div class="mp-card-body" style="padding-top:0!important;">
      <div style="display:flex;gap:18px;flex-wrap:wrap;font-size:13px;">
        <?php if($tab === 'hold'): ?>
          <span><strong><?= $qty($totals['sellable_qty'] ?? 0); ?></strong> can be sold now</span>
          <span><strong><?= $qty($totals['held_qty'] ?? 0); ?></strong> held back (recalled, expired, closed or empty)</span>
          <span><strong><?= $qty($totals['batch_rows'] ?? 0); ?></strong> batches listed</span>
          <span>Worth <strong><?= $money($totals['cost_value'] ?? 0); ?></strong> at batch cost</span>
        <?php elseif($tab === 'expiring'): ?>
          <span><strong><?= $qty($bands['expired_qty'] ?? 0); ?></strong> already past their date<?= !empty($bands['expired_value']) ? ' (' . $money($bands['expired_value']) . ' at cost)' : '' ?></span>
          <span><strong><?= $qty($bands['soon_qty'] ?? 0); ?></strong> run out by <?= show_date($bands['soon_until'] ?? $today); ?></span>
          <span><strong><?= $qty($totals['no_expiry_rows'] ?? 0); ?></strong> batches with no expiry date on the shelf</span>
        <?php else: ?>
          <span><strong><?= $qty($totals['sellable_qty'] ?? 0); ?></strong> can still be sold from these batches</span>
          <span><strong><?= $qty($totals['batch_rows'] ?? 0); ?></strong> batches listed · worth <strong><?= $money($totals['cost_value'] ?? 0); ?></strong></span>
        <?php endif; ?>
      </div>
      <?php if($tab === 'hold' && !empty($totals['unlocated_rows'])): $n = (int)$totals['unlocated_rows']; ?>
        <p style="margin:8px 0 0;font-size:12px;color:#8A5A10;">
          <?= $n; ?> batch record<?= $n === 1 ? '' : 's'; ?> on this business’s ledger <?= $n === 1 ? 'has' : 'have'; ?>
          no location, so no counter can sell from <?= $n === 1 ? 'it' : 'them' ?>.
        </p>
      <?php endif; ?>
      <?php if($tab === 'hold' && !empty($totals['serial_rows'])): $n = (int)$totals['serial_rows']; ?>
        <p style="margin:4px 0 0;font-size:12px;color:#64748b;">
          <?= $n; ?> unit<?= $n === 1 ? '' : 's'; ?> on this ledger <?= $n === 1 ? 'is' : 'are'; ?> tracked by serial number
          rather than by batch, so <?= $n === 1 ? 'it is' : 'they are' ?> not listed as a batch.
        </p>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if($tab === 'sold' && !empty($totals)): ?>
    <div class="mp-card-body" style="padding-top:0!important;">
      <div style="display:flex;gap:18px;flex-wrap:wrap;font-size:13px;">
        <span><strong><?= $qty($totals['units'] ?? 0); ?></strong> units handed over from batches</span>
        <span><strong><?= $qty($totals['returned'] ?? 0); ?></strong> of them came back</span>
        <span><?= (int)$totals['invoices']; ?> invoice<?= (int)$totals['invoices'] === 1 ? '' : 's'; ?> · <strong><?= $money($totals['cost_value'] ?? 0); ?></strong> at batch cost</span>
      </div>
    </div>
    <?php endif; ?>

    <div class="mp-card-body" style="padding:0!important;">
      <div class="mp-dt-scroll">
        <table class="table mp-dt-table" id="batch-report" width="100%">
          <?php if($tab === 'hold'): ?>
          <thead><tr><th>Product</th><th>Location</th><th>Batch</th><th>Expiry</th><th class="text-right">Original received</th><th class="text-right">Closing stock</th><th>Sellable now</th><th class="text-right">Cost value</th><th>Arrived</th></tr></thead>
          <tbody>
            <?php foreach($rows as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r->item_name); ?><br><small style="color:#94a3b8;"><?= htmlspecialchars($r->item_code); ?></small></td>
              <td><?= htmlspecialchars($r->warehouse_name ?: '—'); ?></td>
              <td><?= $r->batch_lot !== '' && $r->batch_lot !== null ? htmlspecialchars($r->batch_lot) : '<span style="color:#94a3b8;">no batch number</span>' ?></td>
              <td><?= $day($r->expire_date); ?></td>
              <td class="text-right"><?= $qty($r->original_qty ?? $r->qty); ?></td>
              <td class="text-right"><strong><?= $qty($r->qty); ?></strong></td>
              <td><?= (int)$r->sellable === 1 ? '<span style="color:#166534;font-weight:600;">Yes</span>' : '<span style="color:#9B2C2C;">' . htmlspecialchars($r->held_reason ?? 'Not sellable right now') . '</span>' ?></td>
              <td class="text-right"><?= $money($r->cost_value); ?></td>
              <td><?= $day($r->created_date); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($rows)): ?><tr><td colspan="9" style="text-align:center;color:#64748b;padding:20px;">
              <?= (string)$filters['search'] !== '' ? 'No batch matches that search.' : 'No batches are recorded for this location. Products received without a batch number is not a batch anyone can sell from.'; ?></td></tr><?php endif; ?>
          </tbody>
          <?php elseif($tab === 'expiring'): ?>
          <thead><tr><th>Expiry</th><th>Product</th><th>Location</th><th>Batch</th><th class="text-right">Quantity</th><th>Days</th><th class="text-right">Cost value</th><th>Still sellable?</th></tr></thead>
          <tbody>
            <?php foreach($rows as $r): $past = (int)$r->days_past > 0; ?>
            <tr<?= $past ? ' style="background:#FEF2F2;"' : ''; ?>>
              <td><?= $day($r->expire_date); ?></td>
              <td><?= htmlspecialchars($r->item_name); ?></td>
              <td><?= htmlspecialchars($r->warehouse_name ?: '—'); ?></td>
              <td><?= $r->batch_lot !== '' && $r->batch_lot !== null ? htmlspecialchars($r->batch_lot) : '<span style="color:#94a3b8;">no batch number</span>' ?></td>
              <td class="text-right"><?= $qty($r->qty); ?></td>
              <td><?php $dp = (int)$r->days_past; if ($dp > 0): ?><span style="color:#9B2C2C;font-weight:600;"><?= $dp; ?> day<?= $dp === 1 ? '' : 's'; ?> ago</span><?php elseif ($dp === 0): ?><span style="color:#8A5A10;font-weight:600;">today</span><?php else: ?><span style="color:#8A5A10;">in <?= abs($dp); ?> day<?= abs($dp) === 1 ? '' : 's'; ?></span><?php endif; ?></td>
              <td class="text-right"><?= $money($r->cost_value); ?></td>
              <td><?= (int)$r->sellable === 1 ? '<span style="color:#166534;">Yes, until its date</span>' : '<span style="color:#9B2C2C;">' . htmlspecialchars($r->held_reason ?? 'No') . '</span>' ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($rows)): ?><tr><td colspan="8" style="text-align:center;color:#64748b;padding:20px;">
              Nothing runs out inside <?= (int)$filters['horizon']; ?> days. Batches dated later than that are not listed here.</td></tr><?php endif; ?>
          </tbody>
          <?php elseif($tab === 'sold'): ?>
          <thead><tr><th>Date</th><th>Invoice</th><th>Product</th><th>Batch</th><th>Expiry</th><th class="text-right">Sold</th><th class="text-right">Returned</th><th>Location</th><th>Drawn by</th></tr></thead>
          <tbody>
            <?php foreach($rows as $r): ?>
            <tr>
              <td><?= $day($r->sales_date); ?></td>
              <td><?= htmlspecialchars($r->sales_code ?: ('#' . (int)$r->sales_id)); ?><br><small style="color:#94a3b8;"><?= htmlspecialchars($r->channel); ?></small></td>
              <td><?= htmlspecialchars($r->item_name); ?></td>
              <td><?= $r->batch_lot !== '' && $r->batch_lot !== null ? htmlspecialchars($r->batch_lot) : '<span style="color:#94a3b8;">no batch number</span>' ?></td>
              <td><?= $day($r->expire_date); ?></td>
              <td class="text-right"><?= $qty($r->qty_allocated); ?></td>
              <td class="text-right"><?= (float)$r->qty_returned > 0 ? '<span style="color:#8A5A10;font-weight:600;">' . $qty($r->qty_returned) . '</span>' : '—' ?></td>
              <td><?= htmlspecialchars($r->warehouse_name ?: '—'); ?></td>
              <td><small style="color:#64748b;"><?= htmlspecialchars($r->rotation_method ?: 'batch chosen'); ?></small></td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($rows)): ?><tr><td colspan="9" style="text-align:center;color:#64748b;padding:20px;">
              No sale in this period was drawn from a batch<?= (string)$filters['search'] !== '' ? ' matching that search' : '' ?>.
              Sales made while the shop was on Manual rotation have no batch behind them.</td></tr><?php endif; ?>
          </tbody>
          <?php else: ?>
          <thead><tr><th>Product</th><th>Location</th><th>Batch</th><th>Arrived</th><th class="text-right">Age</th><th>Bucket</th><th class="text-right">Quantity</th><th>Expiry</th><th class="text-right">Cost value</th></tr></thead>
          <tbody>
            <?php foreach($rows as $r): ?>
            <tr>
              <td><?= htmlspecialchars($r->item_name); ?></td>
              <td><?= htmlspecialchars($r->warehouse_name ?: '—'); ?></td>
              <td><?= $r->batch_lot !== '' && $r->batch_lot !== null ? htmlspecialchars($r->batch_lot) : '<span style="color:#94a3b8;">no batch number</span>' ?></td>
              <td><?= $day($r->created_date); ?></td>
              <td class="text-right"><?= (int)$r->age_days < 0 ? '<span style="color:#94a3b8;">unknown</span>' : (int)$r->age_days . ' days' ?></td>
              <td><?= htmlspecialchars($buckets[$r->bucket]['label'] ?? $r->bucket); ?></td>
              <td class="text-right"><?= $qty($r->qty); ?></td>
              <td><?= $day($r->expire_date); ?></td>
              <td class="text-right"><?= $money($r->cost_value); ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($rows)): ?><tr><td colspan="9" style="text-align:center;color:#64748b;padding:20px;">
              Nothing is waiting on the shelf<?= $filters['bucket'] !== '' ? ' in that age band' : '' ?>.</td></tr><?php endif; ?>
          </tbody>
          <?php endif; ?>
        </table>
      </div>

      <?php if(($pages ?? 1) > 1): ?>
      <div style="padding:12px 16px;text-align:center;">
        <?php if($filters['page'] > 1): ?><a class="btn btn-xs btn-default" href="<?= $link(['page' => $filters['page'] - 1]); ?>">Previous</a><?php endif; ?>
        <span style="color:var(--mp-muted,#64748b);">Page <?= (int)$filters['page']; ?> of <?= (int)$pages; ?></span>
        <?php if($filters['page'] < $pages): ?><a class="btn btn-xs btn-default" href="<?= $link(['page' => $filters['page'] + 1]); ?>">Next</a><?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>
</div>
