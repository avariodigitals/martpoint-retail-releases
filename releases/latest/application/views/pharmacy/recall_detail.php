<?php defined('BASEPATH') OR exit('No direct script access allowed'); $this->load->view('components/dialog'); ?>
<link rel="stylesheet" href="<?= base_url('theme/dist/css/pharmacy_directory.css?v='.filemtime(FCPATH.'theme/dist/css/pharmacy_directory.css')); ?>">
<div class="pharma-workspace">
  <header class="pharma-hero">
    <div>
      <span class="pharma-kicker"><span class="pharma-pulse"></span> <?= $recall->status === 'active' ? 'Batch frozen' : 'Recall closed'; ?></span>
      <h1>Recall <?= htmlspecialchars($recall->recall_code); ?></h1>
      <p><?= htmlspecialchars($recall->reason); ?><?= !empty($recall->notes) ? ' — ' . htmlspecialchars($recall->notes) : ''; ?></p>
    </div>
    <div class="pharma-hero-actions">
      <a class="pharma-btn pharma-btn-light" href="<?= base_url('recall'); ?>"><i class="fa fa-arrow-left"></i> All recalls</a>
    </div>
  </header>

  <?php if((string)($message ?? '') !== ''): ?>
    <div style="padding:12px 16px;border-radius:10px;margin:0 0 16px;background:<?= !empty($error) ? '#FDE8E8' : '#E7F6EF'; ?>;color:<?= !empty($error) ? '#9B2C2C' : '#166534'; ?>;border:1px solid <?= !empty($error) ? '#F5C2C7' : '#B7E4C7'; ?>;">
      <?= htmlspecialchars($message); ?>
    </div>
  <?php endif; ?>

  <div class="mp-card-form" style="margin-bottom:20px;">
    <div class="mp-card-head"><h3>Frozen units</h3></div>
    <div class="mp-card-body" style="padding:0!important;">
      <div class="mp-dt-scroll">
        <table class="table mp-dt-table" width="100%">
          <thead><tr><th>Medicine</th><th>Location</th><th>Batch</th><th>Expiry</th><th>Qty held</th><th>State</th></tr></thead>
          <tbody>
            <?php foreach(($lots ?? []) as $lot): ?>
            <tr>
              <td><?= htmlspecialchars($lot->item_name ?? 'Item #' . (int)$lot->item_id); ?></td>
              <td><?= htmlspecialchars($lot->warehouse_name ?? ('Warehouse #' . (int)$lot->warehouse_id)); ?></td>
              <td><?= htmlspecialchars($lot->batch_lot ?? ''); ?></td>
              <td><?= !empty($lot->expire_date) ? show_date((string)$lot->expire_date) : '—'; ?></td>
              <td><?= rtrim(rtrim(number_format((float)$lot->qty, 2, '.', ''), '0'), '.') ?: '0'; ?></td>
              <td>
                <?php if((int)$lot->status === 3): ?><?= $lot->previously_sellable ? '<span class="label label-danger">Frozen</span>' : '<span class="label label-default">Already sold out</span>'; ?>
                <?php elseif((int)$lot->status === 1): ?><span class="label label-success">Sellable again</span>
                <?php elseif((int)$lot->status === 2): ?><span class="label label-warning">Under return review</span>
                <?php else: ?><span class="label label-default">Sold / consumed</span><?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($lots)): ?><tr><td colspan="6" style="text-align:center;color:var(--mp-muted,#64748b);padding:20px;">No stock units were frozen by this recall.</td></tr><?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="mp-card-form" style="margin-bottom:20px;">
    <div class="mp-card-head"><h3>Invoices this batch reached</h3></div>
    <div class="mp-card-body" style="padding:0!important;">
      <div class="mp-dt-scroll">
        <table class="table mp-dt-table" width="100%">
          <thead><tr><th>Invoice</th><th>Date</th><th>Patient</th><th>Phone</th><th>Qty dispensed</th></tr></thead>
          <tbody>
            <?php foreach(($sales ?? []) as $s): ?>
            <tr>
              <td><a href="<?= base_url('sales/details/' . (int)$s->sales_id); ?>"><?= htmlspecialchars($s->sales_code); ?></a></td>
              <td><?= show_date((string)$s->created_date); ?></td>
              <td><?= htmlspecialchars($s->customer_name ?: 'Walk-in'); ?></td>
              <td><?= htmlspecialchars($s->mobile ?? ''); ?></td>
              <td><?= rtrim(rtrim(number_format((float)$s->qty, 2, '.', ''), '0'), '.') ?: '0'; ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if(empty($sales)): ?>
            <tr><td colspan="5" style="text-align:center;color:var(--mp-muted,#64748b);padding:20px;">
              No dispensing records link to these stock units. Records kept before lot tracking was switched on are not listed here.
            </td></tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <?php if(!empty($ready) && $recall->status === 'active' && (int)($strays ?? 0) > 0 && $this->permissions('recall_add')): ?>
  <div class="mp-card-form" style="margin-bottom:20px;border-left:4px solid #d97706;">
    <div class="mp-card-head"><h3>New stock of this batch is still sellable</h3></div>
    <div class="mp-card-body">
      <p style="margin:0 0 12px;color:var(--mp-muted,#64748b);"><?= (int)$strays; ?> stock unit(s) of batch <?= htmlspecialchars($recall->batch_lot); ?> arrived after this recall was frozen. Freeze them too before closing the recall.</p>
      <?= form_open(base_url('recall/create'), ['id' => 'recall_freeze_more_form']); ?>
      <input type="hidden" name="batch_lot" value="<?= htmlspecialchars($recall->batch_lot); ?>">
      <input type="hidden" name="item_id" value="<?= (int)$recall->item_id; ?>">
      <input type="hidden" name="reason" value="<?= htmlspecialchars($recall->reason); ?>">
      <input type="hidden" name="recall_notes" value="Units received while <?= htmlspecialchars($recall->recall_code); ?> was open.">
      <button type="submit" class="btn btn-sm btn-danger"><i class="fa fa-snowflake-o"></i> Freeze the new units</button>
      <?= form_close(); ?>
    </div>
  </div>
  <?php endif; ?>

  <?php if(!empty($ready) && $recall->status === 'active' && $this->permissions('recall_add')): ?>
  <div class="mp-card-form">
    <div class="mp-card-head"><h3>Close this recall</h3></div>
    <div class="mp-card-body">
      <p style="margin:0 0 12px;color:var(--mp-muted,#64748b);">Closing returns the frozen units to the till and the online store. Only close a recall once the batch is confirmed safe — or once the frozen stock has been destroyed or returned to the supplier.</p>
      <?= form_open(base_url('recall/close_recall/' . (int)$recall->id), ['id' => 'recall_close_form']); ?>
      <button type="submit" class="btn btn-sm btn-warning"><i class="fa fa-flask"></i> Release stock and close recall</button>
      <?= form_close(); ?>
    </div>
  </div>
  <?php endif; ?>
</div>
<script>
$(function(){
  function guard(selector, message, opts, busy){
    var pending = false;
    $(selector).on('submit', function(e){
      if(pending) return;
      e.preventDefault();
      var form = this;
      window.mpConfirmAsync(message, opts).then(function(ok){
        if(ok !== true) return;
        pending = true;
        $(form).find('button[type=submit]').prop('disabled', true).html('<i class="fa fa-spinner fa-spin"></i> ' + busy);
        form.submit();
      });
    });
  }
  guard('#recall_freeze_more_form', 'Freeze the newly received units of this batch as well? They will stop selling at the till, transfers and the online store.',
    { title: 'Freeze the new units', okText: 'Freeze them' }, 'Freezing...');
  guard('#recall_close_form', 'Release this batch back for sale? Only do this when the batch is confirmed safe, or when the frozen stock has already been destroyed or returned.',
    { title: 'Close this recall', okText: 'Release stock' }, 'Releasing...');
});
</script>
