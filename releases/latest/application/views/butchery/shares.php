<?php $this->load->view('admin/desktop/_styles'); ?>
<div class="mp-page-head">
  <div>
    <h2>Carcass Shares</h2>
    <div class="mp-page-sub">Reserve fractions of a carcass for customers.</div>
  </div>
  <a href="<?= base_url('butchery'); ?>" class="mp-qa-btn blue"><i class="fa fa-arrow-left"></i> Back</a>
</div>

<?php if (!empty($message)): ?>
<div class="alert alert-success" style="margin-bottom:20px;"><?= htmlspecialchars($message); ?></div>
<?php endif; ?>

<div class="mp-card-form" style="margin-bottom:20px;">
  <div class="mp-card-head"><h3>New Share Reservation</h3></div>
  <div class="mp-card-body">
    <form method="post" action="<?= base_url('butchery/shares'); ?>">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

      <div class="mp-form-grid">
        <div class="mp-form-group">
          <label>Carcass</label>
          <select class="mp-form-control" name="carcass_item_id">
            <option value="">-- Select --</option>
            <?php foreach ($carcasses as $c): ?>
            <option value="<?= $c->id; ?>"><?= htmlspecialchars($c->carcass_name . ' · Lot ' . ($c->lot_number ?: '---')); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mp-form-group">
          <label>Customer <span class="text-danger">*</span></label>
          <input type="text" class="mp-form-control" name="customer_name" placeholder="Customer name" required>
        </div>
        <div class="mp-form-group">
          <label>Share Fraction <span class="text-danger">*</span></label>
          <select class="mp-form-control" name="share_fraction" required>
            <option value="1/2">1/2</option>
            <option value="1/4" selected>1/4</option>
            <option value="1/8">1/8</option>
            <option value="custom">Custom</option>
          </select>
        </div>
        <div class="mp-form-group">
          <label>Reserved Weight (kg)</label>
          <input type="number" step="0.01" class="mp-form-control" name="reserved_weight" placeholder="Auto from fraction">
        </div>
        <div class="mp-form-group">
          <label>Deposit / Amount</label>
          <input type="number" step="0.01" class="mp-form-control" name="deposit_amount" placeholder="0.00">
        </div>
        <div class="mp-form-group">
          <label>Status</label>
          <select class="mp-form-control" name="status">
            <option value="reserved">Reserved</option>
            <option value="paid">Paid</option>
            <option value="cut">Cut</option>
            <option value="delivered">Delivered</option>
          </select>
        </div>
      </div>

      <div style="margin-top:24px; text-align:right;">
        <button type="submit" class="mp-qa-btn blue"><i class="fa fa-save"></i> Reserve Share</button>
      </div>
    </form>
  </div>
</div>

<div class="mp-card-form">
  <div class="mp-card-head"><h3>Reservations</h3></div>
  <div class="mp-card-body">
    <table class="mp-batch-items">
      <thead>
        <tr>
          <th>Carcass</th>
          <th>Customer</th>
          <th>Fraction</th>
          <th>Weight</th>
          <th>Deposit</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($shares)): ?>
          <?php
          $carcass_map = [];
          foreach ($carcasses as $c) { $carcass_map[$c->id] = $c; }
          ?>
          <?php foreach ($shares as $s): ?>
          <?php $carcass = $carcass_map[$s->carcass_item_id] ?? null; ?>
          <tr>
            <td><?= $carcass ? htmlspecialchars($carcass->carcass_name) : '---'; ?></td>
            <td><?= htmlspecialchars($s->customer_name ?? '---'); ?></td>
            <td><?= htmlspecialchars($s->share_fraction); ?></td>
            <td><?= number_format($s->reserved_weight, 2); ?> kg</td>
            <td><?= number_format($s->deposit_amount, 2); ?></td>
            <td><span class="label label-<?= $s->status === 'delivered' ? 'success' : ($s->status === 'cancelled' ? 'danger' : 'info'); ?>"><?= ucfirst($s->status); ?></span></td>
          </tr>
          <?php endforeach; ?>
        <?php else: ?>
        <tr>
          <td colspan="6" class="text-center text-muted">No reservations yet.</td>
        </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
