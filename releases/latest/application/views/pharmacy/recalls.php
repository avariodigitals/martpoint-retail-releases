<?php defined('BASEPATH') OR exit('No direct script access allowed'); $this->load->view('components/dialog'); ?>
<link rel="stylesheet" href="<?= base_url('theme/dist/css/pharmacy_directory.css?v='.filemtime(FCPATH.'theme/dist/css/pharmacy_directory.css')); ?>">
<div class="pharma-workspace">
  <header class="pharma-hero">
    <div>
      <span class="pharma-kicker"><span class="pharma-pulse"></span> Medicine safety</span>
      <h1>Recalls &amp; quarantine</h1>
      <p>Freeze a batch everywhere it is kept — the till, transfers and online orders all stop selling it until the recall is closed.</p>
    </div>
    <div class="pharma-hero-actions">
      <a class="pharma-btn pharma-btn-light" href="<?= base_url('pharmacy_workspace/more'); ?>"><i class="fa fa-th-large"></i> More tools</a>
    </div>
  </header>

  <?php if(!$ready): ?>
    <div class="pharma-empty"><i class="fa fa-exclamation-triangle"></i><strong>Recalls are not available yet.</strong><span>Ask MartPoint support to apply the latest update so the recall records can be created.</span></div>
  <?php endif; ?>

  <?php if((string)($message ?? '') !== ''): ?>
    <div class="pharma-banner" style="padding:12px 16px;border-radius:10px;margin:0 0 16px;background:<?= !empty($error) ? '#FDE8E8' : '#E7F6EF'; ?>;color:<?= !empty($error) ? '#9B2C2C' : '#166534'; ?>;border:1px solid <?= !empty($error) ? '#F5C2C7' : '#B7E4C7'; ?>;">
      <?= htmlspecialchars($message); ?>
    </div>
  <?php endif; ?>

  <?php if(!empty($ready) && $this->permissions('recall_add')): $pre_batch = (string)$this->input->get('batch'); ?>
  <div class="mp-card-form" style="margin-bottom:20px;">
    <div class="mp-card-head"><h3>Freeze a batch</h3></div>
    <div class="mp-card-body">
      <?= form_open(base_url('recall/create'), ['id' => 'recall_create_form']); ?>
      <div class="row">
        <div class="col-md-4">
          <div class="mp-form-group">
            <label for="recall_batch_lot">Batch / lot number <span style="color:#b91c1c;">*</span></label>
            <input class="mp-form-control" type="text" id="recall_batch_lot" name="batch_lot" maxlength="100" required
              value="<?= htmlspecialchars($pre_batch); ?>" placeholder="e.g. AMX-2409">
            <small style="color:var(--mp-muted,#64748b);">Type it exactly as printed on the pack. Every location holding this batch is frozen.</small>
          </div>
        </div>
        <div class="col-md-4">
          <div class="mp-form-group">
            <label for="recall_reason">Why is it being recalled? <span style="color:#b91c1c;">*</span></label>
            <input class="mp-form-control" type="text" id="recall_reason" name="reason" maxlength="255" required
              placeholder="e.g. Supplier recall — tablet hardness out of spec">
          </div>
        </div>
        <div class="col-md-4">
          <div class="mp-form-group">
            <label for="recall_notes">Notes (optional)</label>
            <input class="mp-form-control" type="text" id="recall_notes" name="recall_notes" maxlength="1000"
              placeholder="e.g. Return destroyed stock on 15 Nov">
          </div>
        </div>
      </div>
      <button type="submit" class="btn btn-sm btn-danger">
        <i class="fa fa-snowflake"></i> Freeze batch
      </button>
      <?= form_close(); ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="mp-card-form">
    <div class="mp-card-head">
      <h3><?= $current_status === 'closed' ? 'Closed recalls' : ($current_status === 'all' ? 'All recalls' : 'Open recalls'); ?></h3>
      <div>
        <a class="btn btn-xs <?= $current_status === 'active' ? 'btn-primary' : 'btn-default'; ?>" href="<?= base_url('recall?status=active'); ?>">Open</a>
        <a class="btn btn-xs <?= $current_status === 'closed' ? 'btn-primary' : 'btn-default'; ?>" href="<?= base_url('recall?status=closed'); ?>">Closed</a>
        <a class="btn btn-xs <?= $current_status === 'all' ? 'btn-primary' : 'btn-default'; ?>" href="<?= base_url('recall?status=all'); ?>">All</a>
      </div>
    </div>
    <div class="mp-card-body" style="padding:0!important;">
      <div class="mp-dt-scroll">
        <table class="table mp-dt-table" width="100%">
          <thead><tr><th>Recall</th><th>Medicine</th><th>Batch</th><th>Reason</th><th>Frozen</th><th>Status</th><th>Opened</th><th></th></tr></thead>
          <tbody>
            <?php foreach(($recalls ?? []) as $r): ?>
            <tr>
              <td><code><?= htmlspecialchars($r->recall_code); ?></code></td>
              <td><?= htmlspecialchars($r->item_name ?? 'Item #' . (int)$r->item_id); ?></td>
              <td><?= htmlspecialchars($r->batch_lot ?? ''); ?></td>
              <td><?= htmlspecialchars($r->reason); ?></td>
              <td><?= (int)$r->unit_count; ?> unit<?php if((int)$r->unit_count === 1){ echo 's'; } ?> · <?= rtrim(rtrim(number_format((float)$r->frozen_qty, 2, '.', ''), '0'), '.') ?: '0'; ?> still held</td>
              <td><?= $r->status === 'active' ? '<span class="label label-danger">Frozen</span>' : '<span class="label label-default">Closed</span>'; ?></td>
              <td><?= !empty($r->initiated_at) ? show_date(substr((string)$r->initiated_at, 0, 10)) : ''; ?><?= !empty($r->initiated_by) ? '<br><small>' . htmlspecialchars($r->initiated_by) . '</small>' : ''; ?></td>
              <td class="text-right"><a class="btn btn-xs btn-info" href="<?= base_url('recall/view/' . (int)$r->id); ?>">Open</a></td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($recalls)): ?>
            <tr><td colspan="9" style="text-align:center;color:var(--mp-muted,#64748b);padding:20px;">
              <?= $current_status === 'closed' ? 'No closed recalls yet.' : 'No recalls on this filter. Freeze a batch above when a supplier or regulator asks for one.'; ?>
            </td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
<script>
$(function(){
  var pending = false;
  $('#recall_create_form').on('submit', function(e){
    if(pending) return;
    e.preventDefault();
    var form = this;
    window.mpConfirmAsync('Freeze every unit of this batch across all locations? The till, transfers and online orders will stop selling it until the recall is closed.',
      { title: 'Freeze this batch', okText: 'Freeze batch' }).then(function(ok){
      if(ok !== true) return;
      pending = true;
      $(form).find('button[type=submit]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> Freezing...');
      form.submit();
    });
  });
});
</script>
