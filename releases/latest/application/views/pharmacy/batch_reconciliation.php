<?php defined('BASEPATH') OR exit('No direct script access allowed'); $this->load->view('components/dialog'); ?>
<link rel="stylesheet" href="<?= base_url('theme/dist/css/pharmacy_directory.css?v='.filemtime(FCPATH.'theme/dist/css/pharmacy_directory.css')); ?>">
<?php
$qty = function ($value) { return function_exists('format_qty') ? format_qty((float)$value) : number_format((float)$value, 2); };
$warehouseName = function ($id) use ($warehouses) {
    foreach ($warehouses as $w) if ((int)$w->id === (int)$id) return (string)$w->warehouse_name;
    return 'the shop';
};
?>
<div class="pharma-workspace">
  <header class="pharma-hero">
    <div>
      <span class="pharma-kicker"><span class="pharma-pulse"></span> Stock ledger</span>
      <h1>Batch reconciliation</h1>
      <p>Where the product on the shelf and its batch records disagree — and the one entry that settles it.</p>
    </div>
    <div class="pharma-hero-actions">
      <a class="pharma-btn pharma-btn-light" href="<?= base_url(!empty($pharmacy_mobile) ? 'mobile/more' : 'dashboard'); ?>"><i class="fa fa-th-large"></i> More tools</a>
    </div>
  </header>

  <?php if(!$ready): ?>
    <div class="pharma-empty"><i class="fa fa-exclamation-triangle"></i><strong>Reconciliation is not available yet.</strong><span>Ask MartPoint support to apply the latest update so batch balances can be recorded.</span></div>
  <?php endif; ?>

  <?php if($rotation === 'MANUAL'): ?>
    <div class="pharma-banner" style="padding:12px 16px;border-radius:10px;margin:0 0 16px;background:#FEF3E2;color:#8A5A10;border:1px solid #F3D9A9;">
      Your shop is on <strong>Manual</strong> rotation, so the counter sells from the stock figures and ignores the batch ledger —
      nothing here is blocking a sale today. Balance these differences <strong>before</strong> switching to FEFO or FIFO, or the
      till will start refusing those same lines.
    </div>
  <?php else: ?>
    <div class="pharma-banner" style="padding:12px 16px;border-radius:10px;margin:0 0 16px;background:#E7F6EF;color:#166534;border:1px solid #B7E4C7;">
      Your shop sells from the batch ledger (<strong><?= htmlspecialchars($rotation); ?></strong>). A product on this list cannot
      be sold beyond what its batches hold, even when the stock figure says otherwise.
    </div>
  <?php endif; ?>

  <?php if((string)($message ?? '') !== ''): ?>
    <div class="pharma-banner" style="padding:12px 16px;border-radius:10px;margin:0 0 16px;background:<?= !empty($error) ? '#FDE8E8' : '#E7F6EF'; ?>;color:<?= !empty($error) ? '#9B2C2C' : '#166534'; ?>;border:1px solid <?= !empty($error) ? '#F5C2C7' : '#B7E4C7'; ?>;">
      <?= htmlspecialchars($message); ?>
    </div>
  <?php endif; ?>

  <div class="mp-card-form">
    <div class="mp-card-head">
      <h3><?= $gap_total === 1 ? 'One product to balance' : $gap_total . ' products to balance'; ?></h3>
      <div>
        <a class="btn btn-xs <?= $current_warehouse === 0 ? 'btn-primary' : 'btn-default'; ?>"
          href="<?= base_url('batch_reconciliation?q=' . urlencode($search)); ?>">All locations</a>
        <?php foreach(($warehouses ?? []) as $w): ?>
          <a class="btn btn-xs <?= (int)$current_warehouse === (int)$w->id ? 'btn-primary' : 'btn-default'; ?>"
            href="<?= base_url('batch_reconciliation?warehouse=' . (int)$w->id . '&q=' . urlencode($search)); ?>"><?= htmlspecialchars($w->warehouse_name); ?></a>
        <?php endforeach; ?>
      </div>
    </div>
    <div class="mp-card-body">
      <form method="get" action="<?= base_url('batch_reconciliation'); ?>" style="margin-bottom:12px;">
        <?php if($current_warehouse): ?><input type="hidden" name="warehouse" value="<?= (int)$current_warehouse; ?>"><?php endif; ?>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
          <input class="mp-form-control" type="text" name="q" maxlength="80" value="<?= htmlspecialchars($search); ?>"
            placeholder="Search a product by name" style="max-width:320px;">
          <button type="submit" class="btn btn-sm btn-default"><i class="fa fa-search"></i> Search</button>
          <?php if($search !== ''): ?><a class="btn btn-sm btn-default" href="<?= base_url('batch_reconciliation' . ($current_warehouse ? '?warehouse=' . (int)$current_warehouse : '')); ?>">Clear</a><?php endif; ?>
        </div>
      </form>
    </div>
    <div class="mp-card-body" style="padding:0!important;">
      <div class="mp-dt-scroll">
        <table class="table mp-dt-table" width="100%">
          <thead><tr>
            <th>Product</th><th>Your records say</th><th>Batches say</th><th>Difference</th><th>Held back</th><th></th>
          </tr></thead>
          <tbody>
            <?php foreach(($gaps ?? []) as $g): ?>
            <tr>
              <td><?= htmlspecialchars($g->item_name); ?><?= $current_warehouse ? '' : '' ?></td>
              <td><?= $qty($g->count_qty); ?><?= $current_warehouse === 0 ? ' <small style="color:#64748b;">(whole business)</small>' : ' <small style="color:#64748b;">(' . htmlspecialchars($warehouseName($current_warehouse)) . ')</small>' ?></td>
              <td><?= $qty($g->lot_qty); ?></td>
              <td><strong><?= (float)$g->signed_gap > 0 ? 'Records hold ' : 'Batches hold ' ?><?= $qty(abs((float)$g->gap_qty)); ?> more</strong></td>
              <td><small style="color:#64748b;"><?= $qty(max(0, (float)$g->ledger_qty - (float)$g->lot_qty)); ?> quarantined, expired or retired</small></td>
              <td class="text-right"><a class="btn btn-xs btn-info"
                href="<?= base_url('batch_reconciliation/item/' . (int)$g->item_id . '?warehouse=' . ($current_warehouse ?: (int)($warehouses[0]->id ?? 0))); ?>">Balance</a></td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($gaps)): ?>
            <tr><td colspan="6" style="text-align:center;color:var(--mp-muted,#64748b);padding:20px;">
              <?= $search !== '' ? 'No product by that name is out of balance.' : 'Nothing to balance — every product’s batches add up to its stock figure.'; ?>
            </td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
      <?php if(($pages ?? 1) > 1): ?>
      <div style="padding:12px 16px;text-align:center;">
        <?php $base = 'batch_reconciliation?warehouse=' . (int)$current_warehouse . '&q=' . urlencode($search); ?>
        <?php if($page > 1): ?><a class="btn btn-xs btn-default" href="<?= base_url($base . '&page=' . ($page-1)); ?>">Newer</a><?php endif; ?>
        <span style="color:var(--mp-muted,#64748b);">Page <?= (int)$page; ?> of <?= (int)$pages; ?></span>
        <?php if($page < $pages): ?><a class="btn btn-xs btn-default" href="<?= base_url($base . '&page=' . ($page+1)); ?>">Older</a><?php endif; ?>
      </div>
      <?php endif; ?>
    </div>
  </div>

  <?php if(!empty($history)): ?>
  <div class="mp-card-form">
    <div class="mp-card-head"><h3>Recent balances</h3></div>
    <div class="mp-card-body" style="padding:0!important;">
      <div class="mp-dt-scroll">
        <table class="table mp-dt-table" width="100%">
          <thead><tr><th>When</th><th>Product</th><th>Location</th><th>What was corrected</th><th>Moved</th><th>Why</th></tr></thead>
          <tbody>
            <?php foreach($history as $h): ?>
            <tr>
              <td><?= htmlspecialchars(show_date(substr((string)$h->created_at, 0, 10))); ?><?= $h->first_name ? '<br><small style="color:#64748b;">' . htmlspecialchars($h->first_name) . '</small>' : '' ?></td>
              <td><?= htmlspecialchars($h->item_name ?? ''); ?></td>
              <td><?= htmlspecialchars($h->warehouse_name ?? ''); ?></td>
              <td><?= $h->fix === 'counts' ? 'Stock figures, from the batches' : 'Batch ledger, from the count' ?></td>
              <td><?= $qty($h->delta_qty); ?></td>
              <td><small style="color:#64748b;"><?= htmlspecialchars($h->reason ?? ''); ?></small></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <?php endif; ?>
</div>
