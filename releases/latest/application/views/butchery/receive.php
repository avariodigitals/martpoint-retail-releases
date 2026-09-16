<?php $this->load->view('admin/desktop/_styles'); ?>
<div class="mp-page-head">
  <div>
    <h2>Receive Carcass</h2>
    <div class="mp-page-sub">Record a whole carcass and create the parent lot.</div>
  </div>
  <a href="<?= base_url('butchery'); ?>" class="mp-qa-btn blue"><i class="fa fa-arrow-left"></i> Back</a>
</div>

<?php if (!empty($message)): ?>
<div class="alert alert-success" style="margin-top:20px;"><?= htmlspecialchars($message); ?></div>
<?php endif; ?>

<div class="mp-card-form">
  <div class="mp-card-head"><h3>New Carcass Receipt</h3></div>
  <div class="mp-card-body">
    <form method="post" action="<?= base_url('butchery/receive'); ?>">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

      <div class="mp-form-grid">
        <div class="mp-form-group">
          <label>Supplier <span class="text-danger">*</span></label>
          <input type="text" class="mp-form-control" name="supplier_name" placeholder="e.g. ABC Farms" required>
        </div>
        <div class="mp-form-group">
          <label>Carcass Item <span class="text-danger">*</span></label>
          <input type="text" class="mp-form-control" name="carcass_name" placeholder="e.g. Whole Cow" required>
        </div>
        <div class="mp-form-group">
          <label>Lot / Batch No.</label>
          <input type="text" class="mp-form-control" name="lot_number" placeholder="Auto or manual">
        </div>
        <div class="mp-form-group">
          <label>Live / Receiving Weight (kg)</label>
          <input type="number" step="0.01" class="mp-form-control" name="receiving_weight" placeholder="0.00">
        </div>
        <div class="mp-form-group">
          <label>Slaughter / Kill Date</label>
          <input type="date" class="mp-form-control" name="slaughter_date" value="<?= date('Y-m-d'); ?>">
        </div>
        <div class="mp-form-group">
          <label>Expected Yield %</label>
          <input type="number" step="0.01" class="mp-form-control" name="expected_yield_pct" placeholder="65.00">
        </div>
        <div class="mp-form-group">
          <label>Freezer / Cold Room</label>
          <select class="mp-form-control" name="freezer_location_id">
            <option value="">-- Select --</option>
            <?php foreach ($freezers as $f): ?>
            <option value="<?= $f->id; ?>"><?= htmlspecialchars($f->location_name); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mp-form-group">
          <label>Cutting Template</label>
          <select class="mp-form-control" name="carcass_template_id">
            <option value="">-- Select --</option>
            <?php foreach ($templates as $t): ?>
            <option value="<?= $t->id; ?>"><?= htmlspecialchars($t->template_name); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="mp-form-group" style="margin-top:20px;">
        <label>Notes</label>
        <textarea class="mp-form-control" name="notes" rows="2" placeholder="Any special instructions"></textarea>
      </div>

      <div style="margin-top:24px; text-align:right;">
        <button type="submit" class="mp-qa-btn blue"><i class="fa fa-save"></i> Save Carcass</button>
      </div>
    </form>
  </div>
</div>

<div class="mp-card-form" style="margin-top:24px;">
  <div class="mp-card-head"><h3>Received Carcasses</h3></div>
  <div class="mp-card-body">
    <table class="mp-batch-items">
      <thead>
        <tr>
          <th>Lot / Batch</th>
          <th>Carcass</th>
          <th>Supplier</th>
          <th>Weight (kg)</th>
          <th>Expected Yield</th>
          <th>Status</th>
          <th style="width:120px;">Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($carcasses)): ?>
          <?php foreach ($carcasses as $c): ?>
          <tr>
            <td><?= htmlspecialchars($c->lot_number ?: '---'); ?></td>
            <td><?= htmlspecialchars($c->carcass_name); ?></td>
            <td><?= htmlspecialchars($c->supplier_name ?: '---'); ?></td>
            <td><?= number_format($c->receiving_weight, 2); ?></td>
            <td><?= number_format($c->expected_yield_pct, 2); ?>%</td>
            <td><span class="label label-<?= $c->status === 'completed' ? 'success' : 'info'; ?>"><?= ucfirst($c->status); ?></span></td>
            <td><a href="<?= base_url('butchery/cut/' . $c->id); ?>" class="mp-qa-btn blue btn-xs"><i class="fa fa-cut"></i> Cut</a></td>
          </tr>
          <?php endforeach; ?>
        <?php else: ?>
        <tr><td colspan="7" class="text-center text-muted">No carcasses received yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
