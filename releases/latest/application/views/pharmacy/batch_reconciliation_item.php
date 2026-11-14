<?php defined('BASEPATH') OR exit('No direct script access allowed'); $this->load->view('components/dialog'); ?>
<link rel="stylesheet" href="<?= base_url('theme/dist/css/pharmacy_directory.css?v='.filemtime(FCPATH.'theme/dist/css/pharmacy_directory.css')); ?>">
<?php
$qty = function ($value) { return function_exists('format_qty') ? format_qty((float)$value) : number_format((float)$value, 2); };
$f = $figures;
$gap = (float)$f['difference'];                       // batches minus records
$write_out = $gap < 0;                               // records hold more than the shelf can show
$needs_reason = $write_out;
?>
<div class="pharma-workspace">
  <header class="pharma-hero">
    <div>
      <span class="pharma-kicker"><span class="pharma-pulse"></span> Stock ledger</span>
      <h1>Balance <?= htmlspecialchars($f['item_name']); ?></h1>
      <p>One product, one location, one decision: which of the two figures is the truth.</p>
    </div>
    <div class="pharma-hero-actions">
      <a class="pharma-btn pharma-btn-light" href="<?= base_url('batch_reconciliation'); ?>"><i class="fa fa-list"></i> All balances</a>
    </div>
  </header>

  <?php if((string)($message ?? '') !== ''): ?>
    <div class="pharma-banner" style="padding:12px 16px;border-radius:10px;margin:0 0 16px;background:<?= !empty($error) ? '#FDE8E8' : '#E7F6EF'; ?>;color:<?= !empty($error) ? '#9B2C2C' : '#166534'; ?>;border:1px solid <?= !empty($error) ? '#F5C2C7' : '#B7E4C7'; ?>;">
      <?= htmlspecialchars($message); ?>
    </div>
  <?php endif; ?>

  <div class="mp-card-form" style="margin-bottom:18px;">
    <div class="mp-card-head"><h3>The two sides</h3></div>
    <div class="mp-card-body">
      <div class="row">
        <div class="col-md-4">
          <div class="mp-form-group" style="padding:14px;border:1px solid #E2E8F0;border-radius:10px;">
            <label style="color:#64748b;">Your records say</label>
            <div style="font-size:24px;font-weight:600;"><?= $qty($f['count_qty']); ?></div>
            <small style="color:#64748b;">From purchases, sales, returns and stock entries for this location.</small>
          </div>
        </div>
        <div class="col-md-4">
          <div class="mp-form-group" style="padding:14px;border:1px solid #E2E8F0;border-radius:10px;">
            <label style="color:#64748b;">The batches say</label>
            <div style="font-size:24px;font-weight:600;"><?= $qty($f['lot_qty']); ?></div>
            <small style="color:#64748b;">Sellable lots only<?= $f['held_back_qty'] > 0 ? ' · ' . $qty($f['held_back_qty']) . ' held back as quarantined, expired or retired' : ''; ?>.</small>
          </div>
        </div>
        <div class="col-md-4">
          <div class="mp-form-group" style="padding:14px;border:1px solid #F3D9A9;border-radius:10px;background:#FEF9EE;">
            <label style="color:#64748b;">The difference</label>
            <div style="font-size:24px;font-weight:600;"><?= $qty(abs($gap)); ?></div>
            <small style="color:#8A5A10;"><?= $gap > 0 ? 'The shelf holds more than the records show.' : ($gap < 0 ? 'The records show more than the shelf holds.' : 'Nothing to balance — the two sides agree.'); ?></small>
          </div>
        </div>
      </div>
      <p style="color:#64748b;margin:6px 0 0;">
        The whole business currently counts <?= $qty($f['store_count']); ?> of this product across every location.
        Rotation is <strong><?= htmlspecialchars($rotation); ?></strong><?= $rotation === 'MANUAL' ? ', so the counter is not blocked by this difference yet' : ', so the counter will only sell what the batches hold' ?>.
      </p>
    </div>
  </div>

  <?php if(!empty($can_manage) && abs($gap) > 0): ?>
  <div class="mp-card-form">
    <div class="mp-card-head"><h3>Settle it</h3></div>
    <div class="mp-card-body">
      <?= form_open(base_url('batch_reconciliation/balance'), ['id' => 'recon_balance_form']); ?>
      <input type="hidden" name="item_id" value="<?= (int)$f['item_id']; ?>">
      <input type="hidden" name="warehouse_id" value="<?= (int)$f['warehouse_id']; ?>">

      <div class="mp-form-group" style="margin-bottom:16px;">
        <label style="display:block;padding:12px 14px;border:1px solid #E2E8F0;border-radius:10px;cursor:pointer;">
          <input type="radio" name="fix" value="counts" style="margin-right:8px;">
          <strong>The batches are right</strong> — my records overstate or understate what is on the shelf.
          <div style="margin-top:6px;color:#64748b;font-weight:400;">
            Posts a stock entry of <?= $gap > 0 ? '+' : ''; ?><?= $qty($gap); ?> so the figures match the batches.
            This is the entry the counter is waiting for.
          </div>
        </label>
        <label style="display:block;padding:12px 14px;border:1px solid #E2E8F0;border-radius:10px;cursor:pointer;margin-top:8px;<?= $write_out ? 'opacity:.55;' : ''; ?>>
          <input type="radio" name="fix" value="batches" style="margin-right:8px;" <?= $write_out ? 'disabled' : ''; ?>>
          <strong>The count is right</strong> — this product is on the shelf but its batch was never recorded.
          <div style="margin-top:6px;color:#64748b;font-weight:400;">
            <?= $write_out ? 'Not available here: the shelf cannot hold more than the count, so the extra in your records has to be written out.'
              : 'Records the batch of ' . $qty(abs($gap)) . ' that is physically present, so batch rotation can sell it.' ?>
          </div>
        </label>
      </div>

      <div id="recon_counts_fields" style="display:none;">
        <?php if($needs_reason): ?>
        <div class="mp-form-group" style="margin-bottom:14px;">
          <label>Why is the extra leaving the shelf? <span style="color:#b91c1c;">*</span></label>
          <?php foreach($wastage_reasons as $code => $label): ?>
            <label style="display:inline-block;margin-right:14px;font-weight:400;">
              <input type="radio" name="reason_code" value="<?= htmlspecialchars($code); ?>"> <?= htmlspecialchars($label); ?>
            </label>
          <?php endforeach; ?>
          <small style="display:block;color:#64748b;">Recorded against the stock entry, so the write-out reads as a reason later.</small>
        </div>
        <?php else: ?>
          <input type="hidden" name="reason_code" value="">
        <?php endif; ?>
      </div>

      <div id="recon_batches_fields" style="display:none;">
        <div class="row">
          <div class="col-md-6">
            <div class="mp-form-group">
              <label for="recon_batch_lot">Batch / lot number on the packs <span style="color:#b91c1c;">*</span></label>
              <input class="mp-form-control" type="text" id="recon_batch_lot" name="batch_lot" maxlength="100" placeholder="e.g. AMX-2409">
              <small style="color:#64748b;">If this batch is already recorded at this location, its quantity is topped up instead of duplicated.</small>
            </div>
          </div>
          <div class="col-md-6">
            <div class="mp-form-group">
              <label for="recon_expire">Expiry date (if the pack shows one)</label>
              <input class="mp-form-control" type="date" id="recon_expire" name="expire_date">
              <small style="color:#64748b;">Needed for first-expiry selling. Leave blank when the pack carries no date.</small>
            </div>
          </div>
        </div>
      </div>

      <div class="mp-form-group" style="margin-bottom:14px;">
        <label for="recon_note">What happened? <span style="color:#b91c1c;">*</span></label>
        <input class="mp-form-control" type="text" id="recon_note" name="note" maxlength="255" required
          placeholder="e.g. Two strips found behind the counter, never entered from the July delivery">
        <small style="color:#64748b;">This is the record of the decision — write it the way you would explain it to an inspector.</small>
      </div>

      <button type="submit" class="btn btn-sm btn-primary"><i class="fa fa-check"></i> Record this balance</button>
      <a class="btn btn-sm btn-default" href="<?= base_url('batch_reconciliation'); ?>">Cancel</a>
      <?= form_close(); ?>
    </div>
  </div>
  <?php elseif(empty($can_manage)): ?>
    <div class="pharma-empty"><i class="fa fa-lock"></i><strong>Read only.</strong><span>You can see the difference but not settle it — ask a manager holding the stock reconciliation permission.</span></div>
  <?php endif; ?>

  <div class="mp-card-form">
    <div class="mp-card-head"><h3>Batches at this location</h3></div>
    <div class="mp-card-body" style="padding:0!important;">
      <div class="mp-dt-scroll">
        <table class="table mp-dt-table" width="100%">
          <thead><tr><th>Batch / lot</th><th>Expiry</th><th>Quantity</th><th>Can it be sold?</th></tr></thead>
          <tbody>
            <?php foreach(($lot_rows ?? []) as $lot): ?>
            <tr>
              <td><?= htmlspecialchars((string)($lot->batch_lot ?? '')) ?: '<em style="color:#64748b;">no number</em>'; ?></td>
              <td><?= !empty($lot->expire_date) ? htmlspecialchars(substr((string)$lot->expire_date, 0, 10)) : '—'; ?></td>
              <td><?= $qty($lot->qty); ?></td>
              <td><?= (int)$lot->sellable === 1 ? '<span class="label label-success">Yes</span>' : '<span class="label label-default">Held back</span>'; ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($lot_rows)): ?>
            <tr><td colspan="4" style="text-align:center;color:var(--mp-muted,#64748b);padding:20px;">No batch rows for this product at this location.</td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <?php if(!empty($history)): ?>
  <div class="mp-card-form">
    <div class="mp-card-head"><h3>Recent balances on this product</h3></div>
    <div class="mp-card-body" style="padding:0!important;">
      <div class="mp-dt-scroll">
        <table class="table mp-dt-table" width="100%">
          <thead><tr><th>When</th><th>Who</th><th>What was corrected</th><th>Moved</th><th>Why</th></tr></thead>
          <tbody>
            <?php foreach($history as $h): if((int)$h->item_id !== (int)$f['item_id']) continue; ?>
            <tr>
              <td><?= htmlspecialchars(show_date(substr((string)$h->created_at, 0, 10))); ?></td>
              <td><?= htmlspecialchars($h->first_name ?: ($h->last_name ?: 'Staff')); ?></td>
              <td><?= $h->fix === 'counts' ? 'Stock figures' : 'Batch ledger'; ?><?= $h->batch_lot ? ' · batch ' . htmlspecialchars($h->batch_lot) : ''; ?></td>
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
<script>
$(function(){
  var pending = false;
  function fields(){
    var fix = $('input[name=fix]:checked').val();
    $('#recon_counts_fields').toggle(fix === 'counts');
    $('#recon_batches_fields').toggle(fix === 'batches');
  }
  $('input[name=fix]').on('change', fields);
  fields();
  $('#recon_balance_form').on('submit', function(e){
    if(pending) return;
    e.preventDefault();
    var form = this, fix = $('input[name=fix]:checked').val();
    if(!fix){
      window.cqToast('Choose which figure is correct first.', 'warn');
      return;
    }
    var words = fix === 'counts'
      ? 'Correct your stock figures to match the batches? A stock entry is written, with your note, and the counter can sell the product again.'
      : 'Record this batch at this location so the ledger matches the count?';
    window.mpConfirmAsync(words, { title: 'Record this balance', okText: 'Record balance' }).then(function(ok){
      if(ok !== true) return;
      pending = true;
      $(form).find('button[type=submit]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Saving...');
      form.submit();
    });
  });
});
</script>
