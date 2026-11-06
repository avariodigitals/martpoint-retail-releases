<?php $this->load->view('inventory/desktop/_styles'); ?>
<?php $limit_text = rtrim(rtrim(number_format((float)$tolerance, 2), '0'), '.'); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">How much of a load may go missing between locations before a manager has to approve it</div>
  </div>
  <div class="mp-form-actions" style="margin:0;">
    <a href="<?= base_url('stock_transfer/view'); ?>" class="mp-btn-secondary"><i class="fa fa-arrow-left"></i> Back to Transfers</a>
  </div>
</div>

<div class="mp-card-form">
  <div class="mp-card-head">
    <h3>Transit loss limit</h3>
  </div>
  <div class="mp-card-body">
    <?= form_open(base_url('stock_transfer/save_tolerance')); ?>
    <div class="mp-form-grid">
      <div class="mp-form-group">
        <label for="tolerance">Percentage of the load allowed to go missing <span class="text-danger">*</span></label>
        <div class="input-group" style="width:100%;">
          <input type="text" class="form-control" id="tolerance" name="tolerance" inputmode="decimal"
                 value="<?= htmlspecialchars($limit_text); ?>">
          <span class="input-group-addon">%</span>
        </div>
        <span class="help-block">Fuel expands, leaks and sticks to the tank walls, so most operators allow a small figure. Set 0 to approve every shortfall.</span>
      </div>
    </div>

    <div class="alert alert-info">
      <strong>How this works.</strong>
      When a load is counted in, a shortfall up to <?= htmlspecialchars($limit_text); ?>% of what was sent is accepted and posts straight to the receiving location's stock.
      Anything wider is held as "Short &mdash; needs approval" and only lands after a manager accepts it.
    </div>

    <div class="mp-form-actions" style="margin-top:16px;">
      <button type="submit" class="mp-btn-primary"><i class="fa fa-check"></i> Save limit</button>
      <a href="<?= base_url('stock_transfer/view'); ?>" class="mp-btn-secondary"><i class="fa fa-times"></i> Close</a>
    </div>
    <?= form_close(); ?>
  </div>
</div>
