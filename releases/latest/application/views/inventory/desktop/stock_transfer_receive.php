<?php $this->load->view('inventory/desktop/_styles'); ?>
<?php
$CI =& get_instance();
$site   = isset($site_label) && $site_label ? $site_label : 'Warehouse';
$status = (string)$transfer->receipt_status;
$line_count = is_array($lines) ? count($lines) : 0;
?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Load #<?= (int)$stocktransfer_id; ?> &mdash; <?= htmlspecialchars(get_warehouse_name($transfer->warehouse_from)); ?> to <?= htmlspecialchars(get_warehouse_name($transfer->warehouse_to)); ?></div>
  </div>
  <div class="mp-form-actions" style="margin:0;">
    <a href="<?= base_url('stock_transfer/view'); ?>" class="mp-btn-secondary"><i class="fa fa-arrow-left"></i> Back to List</a>
    <a href="<?= base_url('stock_transfer/info/'.$stocktransfer_id); ?>" class="mp-btn-secondary"><i class="fa fa-file-text-o"></i> Transfer Details</a>
  </div>
</div>

<?php if ($status === 'In Transit'): ?>
  <div class="alert alert-warning">
    <strong>Waiting to be counted in.</strong>
    This load left <?= htmlspecialchars($site); ?> "<?= htmlspecialchars(get_warehouse_name($transfer->warehouse_from)); ?>" and is not part of "<?= htmlspecialchars(get_warehouse_name($transfer->warehouse_to)); ?>" stock yet.
    Enter what physically arrived; only the counted quantity will be added.
  </div>
<?php elseif ($status === 'Awaiting Approval'): ?>
  <div class="alert alert-danger">
    <strong>Held for a manager.</strong>
    This load was counted short by more than the <?= $tolerance_text; ?>% allowed for transit loss, so none of it has been added to "<?= htmlspecialchars(get_warehouse_name($transfer->warehouse_to)); ?>" stock.
    A manager can accept the shortfall or send the load back to be counted again.
  </div>
<?php elseif ($status === 'Received'): ?>
  <div class="alert alert-success">
    <strong>Already counted in.</strong>
    Recorded on <?= show_date($transfer->received_date); ?><?php if (!empty($transfer->received_by)): ?> by <?= htmlspecialchars(explode(' ', trim($transfer->received_by))[0]); ?><?php endif; ?>.
    The counted quantity is now part of "<?= htmlspecialchars(get_warehouse_name($transfer->warehouse_to)); ?>" stock.
  </div>
<?php endif; ?>

<div class="mp-card-form">
  <div class="mp-card-head">
    <h3>What was sent</h3>
    <span class="mp-card-link"><?= $line_count; ?> line<?= $line_count === 1 ? '' : 's'; ?>, <?= format_qty($sent_total); ?> total</span>
  </div>
  <div class="mp-card-body">
    <div class="inv-meta-row">
      <div class="inv-meta-group">
        <span class="inv-meta-label">Sent on</span>
        <span class="inv-meta-value"><?= show_date($transfer->transfer_date); ?></span>
      </div>
      <div class="inv-meta-group">
        <span class="inv-meta-label">From <?= htmlspecialchars($site); ?></span>
        <span class="inv-meta-value"><?= htmlspecialchars(get_warehouse_name($transfer->warehouse_from)); ?></span>
      </div>
      <div class="inv-meta-group">
        <span class="inv-meta-label">To <?= htmlspecialchars($site); ?></span>
        <span class="inv-meta-value"><?= htmlspecialchars(get_warehouse_name($transfer->warehouse_to)); ?></span>
      </div>
      <?php if (!empty($transfer->note)): ?>
      <div class="inv-meta-group">
        <span class="inv-meta-label">Sender's note</span>
        <span class="inv-meta-value"><?= htmlspecialchars($transfer->note); ?></span>
      </div>
      <?php endif; ?>
    </div>

    <?= form_open(base_url('stock_transfer/save_receive'), array('id' => 'receive_form')); ?>
    <input type="hidden" name="stocktransfer_id" value="<?= (int)$stocktransfer_id; ?>">

    <div class="mp-dt-scroll" style="margin:16px 0;">
      <table class="table inv-item-table" style="width:100%;">
        <thead>
          <tr>
            <th style="width:40%"><?= $this->lang->line('item_name') ?: 'Item'; ?></th>
            <th style="width:15%">Sent</th>
            <th style="width:25%">Actually arrived</th>
            <th style="width:20%">Short by</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($lines as $l): ?>
          <tr data-sent="<?= (float)$l->transfer_qty; ?>">
            <td>
              <?= htmlspecialchars($l->item_name); ?>
              <?php if (!empty($l->unit_name)): ?><span class="text-muted" style="font-size:12px;"> (<?= htmlspecialchars($l->unit_name); ?>)</span><?php endif; ?>
            </td>
            <td><?= format_qty($l->transfer_qty); ?></td>
            <td>
              <?php if ($can_receive): ?>
              <input type="text" class="counted_qty" name="received[<?= (int)$l->id; ?>]"
                     value="<?= $l->received_qty !== null ? format_qty($l->received_qty) : format_qty($l->transfer_qty); ?>">
              <?php else: ?>
              <span><?= $l->received_qty !== null ? format_qty($l->received_qty) : '—' ?></span>
              <?php endif; ?>
            </td>
            <td class="short_cell">&mdash;</td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="mp-form-grid">
      <div class="mp-form-group">
        <label>Counted total</label>
        <div class="mp-form-control" style="display:flex;align-items:center;">
          <span id="counted_total" style="font-size:18px;font-weight:700;">&mdash;</span>
        </div>
      </div>
      <div class="mp-form-group">
        <label>Transit loss</label>
        <div class="mp-form-control" style="display:flex;align-items:center;">
          <span id="variance_note" class="text-muted"><?= $tolerance_text > 0 ? 'Up to ' . $tolerance_text . '% short posts automatically.' : 'No transit loss is allowed — any shortfall needs approval.'; ?></span>
        </div>
      </div>

      <?php if ($can_receive): ?>
      <div class="mp-form-group full">
        <label for="variance_reason">Why is it short? <span class="text-muted" style="font-weight:400;">Only needed if something is missing.</span></label>
        <textarea class="mp-form-control" id="variance_reason" name="variance_reason" rows="2"
                  placeholder="Leaked in transit, miscounted at the depot, meter reading off&hellip;"><?= htmlspecialchars((string)$transfer->variance_reason); ?></textarea>
      </div>
      <?php endif; ?>
    </div>

    <?php if ($can_receive): ?>
    <div class="mp-form-actions" style="margin-top:24px;">
      <button type="submit" class="mp-btn-primary"><i class="fa fa-check"></i> Save arrival count</button>
      <a href="<?= base_url('stock_transfer/view'); ?>" class="mp-btn-secondary"><i class="fa fa-times"></i> Close</a>
    </div>
    <?php elseif ($status !== 'Received'): ?>
    <p class="text-muted">You are not assigned to "<?= htmlspecialchars(get_warehouse_name($transfer->warehouse_to)); ?>", so this load cannot be counted in by you.</p>
    <?php endif; ?>
    <?= form_close(); ?>
  </div>
</div>

<?php if ($can_receive): ?>
<script type="text/javascript">
(function () {
  var tolerance = <?= (float)$tolerance; ?>;
  var sentTotal = <?= (float)$sent_total; ?>;
  function num(v) { v = (v || '').toString().replace(/,/g, '').trim(); return v === '' ? NaN : parseFloat(v); }
  function fmt(v) { return v.toLocaleString(undefined, { maximumFractionDigits: 3 }); }

  function recompute() {
    var counted = 0, short_any = false;
    document.querySelectorAll('#receive_form tr[data-sent]').forEach(function (tr) {
      var sent = parseFloat(tr.getAttribute('data-sent')) || 0;
      var input = tr.querySelector('.counted_qty');
      var cell = tr.querySelector('.short_cell');
      if (!input || !cell) return;
      var val = num(input.value);
      if (isNaN(val)) { cell.textContent = '—'; cell.className = 'short_cell'; return; }
      counted += val;
      var diff = sent - val;
      if (diff > 0.0001) { cell.textContent = fmt(diff); cell.className = 'short_cell text-danger'; short_any = true; }
      else if (diff < -0.0001) { cell.textContent = '+' + fmt(-diff); cell.className = 'short_cell text-warning'; }
      else { cell.textContent = '—'; cell.className = 'short_cell text-muted'; }
    });
    var totalEl = document.getElementById('counted_total');
    totalEl.textContent = fmt(counted);
    totalEl.className = short_any ? 'text-danger' : 'text-success';

    var note = document.getElementById('variance_note');
    var short_pct = sentTotal > 0 ? ((sentTotal - counted) / sentTotal) * 100 : 0;
    if (short_pct > 0.0001 && short_pct > tolerance) {
      note.textContent = fmt(sentTotal - counted) + ' short (' + short_pct.toFixed(2) + '%) — past your ' + tolerance + '% limit, so a manager must approve it.';
      note.className = 'text-danger';
    } else if (short_pct > 0.0001) {
      note.textContent = fmt(sentTotal - counted) + ' short — within your ' + tolerance + '% transit loss, so it posts on save.';
      note.className = 'text-warning';
    } else if (counted > sentTotal + 0.0001) {
      note.textContent = 'More arrived than was sent. Save it as counted and check the figures with the sending site.';
      note.className = 'text-warning';
    } else {
      note.textContent = 'Everything matches. Saving adds the full load to this location\'s stock.';
      note.className = 'text-muted';
    }
  }

  document.getElementById('receive_form').addEventListener('input', recompute);
  recompute();
})();
</script>
<?php endif; ?>

<?php if ($status === 'Awaiting Approval' && $can_decide): ?>
<div class="mp-card-form" style="margin-top:16px;">
  <div class="mp-card-head"><h3>Manager decision</h3></div>
  <div class="mp-card-body">
    <?= form_open(base_url('stock_transfer/decide_variance')); ?>
    <input type="hidden" name="stocktransfer_id" value="<?= (int)$stocktransfer_id; ?>">
    <div class="mp-form-group full">
      <label for="decision_note">Your note <span class="text-danger">*</span> <span class="text-muted" style="font-weight:400;">This goes in the approval history, so write it in plain English.</span></label>
      <textarea class="mp-form-control" id="decision_note" name="decision_note" rows="2" required
                placeholder="Accepted: evaporative loss on a hot day, documented."></textarea>
    </div>
    <div class="mp-form-actions" style="margin-top:16px;">
      <button type="submit" name="decision" value="approve" class="mp-btn-primary"><i class="fa fa-thumbs-up"></i> Accept the loss and post the stock</button>
      <button type="submit" name="decision" value="reject" class="mp-btn-secondary"><i class="fa fa-thumbs-down"></i> Reject and send back for a recount</button>
    </div>
    <?= form_close(); ?>
  </div>
</div>
<?php endif; ?>
