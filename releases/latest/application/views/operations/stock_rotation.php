<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">FEFO shelf rotation guide &middot; Put the oldest / soonest-expiring batches in front</div>
  </div>
  <a href="<?= base_url('operations/stock_rotation?date=' . date('Y-m-d')); ?>" class="mp-qa-btn blue" onclick="window.print(); return false;"><i class="fa fa-print"></i> Print List</a>
</div>

<div class="mp-card-form" style="margin-top:16px;">
  <div class="mp-card-body">
    <form method="get" action="<?= base_url('operations/stock_rotation'); ?>" class="mp-form-grid" style="margin:0;">
      <div class="mp-form-group">
        <label>Received Date</label>
        <input type="date" name="date" class="mp-form-control" value="<?= htmlspecialchars($date); ?>">
      </div>
      <div class="mp-form-group">
        <label>OR Purchase #</label>
        <input type="text" name="purchase_id" class="mp-form-control" placeholder="e.g. 1023" value="<?= $purchase_id ? $purchase_id : ''; ?>">
      </div>
      <div class="mp-form-group" style="display:flex;align-items:flex-end;">
        <button type="submit" class="mp-qa-btn green"><i class="fa fa-filter"></i> Load Rotation</button>
      </div>
    </form>
  </div>
</div>

<?php if(empty($rotations)): ?>
<div class="mp-card-form" style="margin-top:16px;">
  <div class="mp-card-body" style="text-align:center;padding:48px;">
    <i class="fa fa-check-circle" style="font-size:48px;color:var(--mp-success);"></i>
    <h3 style="margin-top:16px;">Nothing to rotate today</h3>
    <p class="mp-form-hint">No received items with expiry data found for this filter.</p>
  </div>
</div>
<?php else: ?>

<div class="mp-table-wrap" style="margin-top:16px;">
  <div class="mp-card-head"><h3><i class="fa fa-refresh"></i> Rotation Tasks</h3></div>
  <div class="mp-card-body" style="padding:0;">
    <div class="mp-dt-scroll">
      <table class="table mp-dt-table" style="margin:0;">
        <thead>
          <tr>
            <th style="width:80px;">Image</th>
            <th><?= mp_label('item'); ?></th>
            <th>New Batch Received</th>
            <th style="min-width:220px;">Older Batches (Front Shelf)</th>
            <th>Instruction</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach($rotations as $r): ?>
          <tr>
            <td>
              <?php $img = !empty($r['item_image']) && file_exists($r['item_image']) ? base_url(return_item_image_thumb($r['item_image'])) : base_url('theme/images/no_image.png'); ?>
              <img src="<?= $img; ?>" alt="" style="width:60px;height:60px;object-fit:cover;border-radius:8px;border:1px solid var(--mp-border);">
            </td>
            <td>
              <strong><?= htmlspecialchars($r['item_name'] ?? 'Unknown'); ?></strong><br>
              <small class="text-muted">GRN: <?= htmlspecialchars($r['purchase_code'] ?? '-'); ?> &middot; <?= show_date($r['purchase_date']); ?></small>
            </td>
            <td>
              <div class="mp-kpi-card success" style="padding:10px 14px;margin:0;min-width:160px;">
                <div class="mp-form-hint" style="margin-bottom:2px;"><strong>Batch:</strong> <?= htmlspecialchars($r['new_batch']->batch_lot ?? '-'); ?></div>
                <div class="mp-form-hint" style="margin-bottom:2px;"><strong>Barcode:</strong> <?= htmlspecialchars($r['new_batch']->barcode ?? '-'); ?></div>
                <div class="mp-form-hint" style="margin-bottom:2px;"><strong>Qty:</strong> <?= store_number_format($r['new_batch']->received_qty, 0); ?> <?= htmlspecialchars($r['unit_name'] ?? ''); ?></div>
                <div class="mp-form-hint" style="color:var(--mp-success);"><strong>Exp:</strong> <?= is_valid_date($r['new_batch']->expire_date) ? show_date($r['new_batch']->expire_date) : '-'; ?></div>
              </div>
            </td>
            <td>
              <?php if(empty($r['old_batches'])): ?>
                <span class="text-muted">No older batches in stock.</span>
              <?php else: ?>
                <?php foreach(array_slice($r['old_batches'], 0, 3) as $b): ?>
                <div style="background:var(--mp-surface);border:1px solid var(--mp-border);border-left:4px solid var(--mp-warning);border-radius:8px;padding:8px 12px;margin-bottom:8px;">
                  <div style="font-size:13px;"><strong>Batch:</strong> <?= htmlspecialchars($b->batch_lot ?? '-'); ?> &middot; <?= is_valid_date($b->expire_date) ? show_date($b->expire_date) : '-'; ?></div>
                  <div style="font-size:12px;color:var(--mp-muted);">Qty: <?= store_number_format($b->qty, 0); ?> &middot; Barcode: <?= htmlspecialchars($b->barcode ?? '-'); ?></div>
                </div>
                <?php endforeach; ?>
                <?php if(count($r['old_batches']) > 3): ?>
                <small class="text-muted">+ <?= count($r['old_batches']) - 3; ?> more batch(es)</small>
                <?php endif; ?>
              <?php endif; ?>
            </td>
            <td style="vertical-align:middle;">
              <?php if(empty($r['old_batches'])): ?>
                <span class="mp-badge" style="background:var(--mp-success);color:#fff;"><i class="fa fa-check"></i> Put on shelf</span>
              <?php else: ?>
                <span class="mp-badge" style="background:var(--mp-warning);color:#111827;"><i class="fa fa-arrow-up"></i> Move these older batches to the FRONT</span><br>
                <span class="mp-form-hint" style="display:block;margin-top:6px;">Place the new batch BEHIND them.</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<div class="mp-card-form" style="margin-top:16px;">
  <div class="mp-card-body" style="padding:20px;">
    <h4 style="margin-top:0;"><i class="fa fa-info-circle"></i> Why this matters</h4>
    <p class="mp-form-hint" style="margin:0;">
      Selling the soonest-expiring batch first (FEFO) reduces waste and keeps shelves compliant.
      The POS is already enforcing this order, but the physical stock must match. Always pull older stock forward when a new delivery arrives.
    </p>
  </div>
</div>

<?php endif; ?>
