<?php $this->load->view('admin/desktop/_styles'); ?>

<div class="mp-page-head">
  <div>
    <h2>Factory Settings</h2>
    <div class="mp-page-sub">Nylon / polythene module configuration</div>
  </div>
</div>

<div class="mp-form-grid" style="grid-template-columns:1fr 1fr;gap:20px;align-items:start;">
  <div class="mp-card-form" style="margin-bottom:0;">
    <div class="mp-card-head"><h3><i class="fa fa-cogs"></i> Factory Capabilities</h3></div>
    <div class="mp-card-body">
      <p class="mp-muted" style="font-size:13px;">Capabilities are controlled by feature flags on the <a href="<?= base_url('business_profile'); ?>">Business Profile</a> page — switch them to match what this factory actually does.</p>
      <table class="mp-static-table">
        <tr><td>In-house film extrusion (resin → rolls)</td><td><span class="label label-<?= $mode['extrusion']?'success':'default'; ?>"><?= $mode['extrusion']?'On':'Off'; ?></span></td></tr>
        <tr><td>Film-to-bag conversion</td><td><span class="label label-<?= $mode['conversion']?'success':'default'; ?>"><?= $mode['conversion']?'On':'Off'; ?></span></td></tr>
        <tr><td>Film roll trading (sell rolls as-is)</td><td><span class="label label-<?= $mode['trading']?'success':'default'; ?>"><?= $mode['trading']?'On':'Off'; ?></span></td></tr>
      </table>
      <p class="mp-muted" style="font-size:12px;margin-top:10px;">
        <strong>Extrude + convert:</strong> all stages available — resin in, bags out.<br>
        <strong>Convert only:</strong> jobs start from purchased film rolls; the extrusion stage is skipped.<br>
        <strong>Trade only:</strong> rolls are purchased and sold through the standard purchase &amp; sales modules — no conversion stages.
      </p>
    </div>
  </div>

  <div class="mp-card-form" style="margin-bottom:0;">
    <div class="mp-card-head"><h3><i class="fa fa-sliders"></i> Options</h3></div>
    <div class="mp-card-body">
      <?php if ($message): ?><p class="<?= strpos($message,'Could')===0?'text-danger':'text-success'; ?>" style="font-size:13px;"><?= htmlspecialchars($message); ?></p><?php endif; ?>
      <form method="post" action="<?= base_url('nylon/settings'); ?>">
        <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
        <input type="hidden" name="save_settings" value="1">
        <label>Default scrap item</label>
        <select name="default_scrap_item_id" class="form-control">
          <option value="">— none —</option>
          <?php foreach ($scrap_items as $s): ?><option value="<?= $s->item_id; ?>" <?= ($settings['default_scrap_item_id'] ?? 0) == $s->item_id ? 'selected' : ''; ?>><?= htmlspecialchars($s->item_name); ?></option><?php endforeach; ?>
        </select>
        <p class="mp-muted" style="font-size:12px;">Reusable scrap reported on stage logs is credited to this item when the operator does not pick one.</p>
        <label style="font-weight:400;margin-top:10px;"><input type="checkbox" name="require_deposit_before_job" value="1" <?= !empty($settings['require_deposit_before_job']) ? 'checked' : ''; ?>> Require a recorded deposit before a job can be created from an order</label>
        <label style="margin-top:10px;">Due-soon warning window (days)</label>
        <input type="number" name="notify_due_days" class="form-control" value="<?= (int)($settings['notify_due_days'] ?? 3); ?>" min="1" max="30">
        <div style="margin-top:14px;"><button type="submit" class="mp-qa-btn"><i class="fa fa-save"></i> Save Settings</button></div>
      </form>
    </div>
  </div>
</div>

<div class="mp-card-form" style="margin-top:20px;">
  <div class="mp-card-head"><h3><i class="fa fa-link"></i> Related Modules</h3></div>
  <div class="mp-card-body">
    <p class="mp-muted" style="font-size:13px;">Commercial documents stay in the standard modules so invoices, payments and ledgers keep working:
      quotations &amp; wholesale pricing (<a href="<?= base_url('quotation'); ?>">Quotations</a>),
      purchases of resin &amp; film rolls (<a href="<?= base_url('purchase'); ?>">Purchase</a>),
      invoices, deposits &amp; balance collection (<a href="<?= base_url('sales'); ?>">Sales</a>),
      delivery notes &amp; partial dispatches (order page → Record Dispatch, or <a href="<?= base_url('operations/delivery_scheduling'); ?>">Delivery Scheduling</a>).
      Stock corrections are visible under <a href="<?= base_url('stock_adjustment'); ?>">Stock Adjustments</a> with the job code as reference.</p>
  </div>
</div>
