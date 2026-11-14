<?php $this->load->view('admin/desktop/_styles'); ?>
<div class="mp-page-head">
  <div>
    <h2>Cold Chain Temperature Log</h2>
    <div class="mp-page-sub">Record and monitor freezer temperatures.</div>
  </div>
  <a href="<?= base_url('butchery'); ?>" class="mp-qa-btn blue"><i class="fa fa-arrow-left"></i> Back</a>
</div>

<?php if (!empty($message)): ?>
<div class="alert alert-success" style="margin-top:20px;"><?= htmlspecialchars($message); ?></div>
<?php endif; ?>

<div class="mp-card-form" style="margin-bottom:20px;">
  <div class="mp-card-head"><h3>Record Temperature</h3></div>
  <div class="mp-card-body">
    <form method="post" action="<?= base_url('butchery/temperature'); ?>">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

      <div class="mp-form-grid">
        <div class="mp-form-group">
          <label>Freezer / Cold Room <span class="text-danger">*</span></label>
          <select class="mp-form-control" name="freezer_location_id" required>
            <option value="">-- Select --</option>
            <?php foreach ($freezers as $f): ?>
            <option value="<?= $f->id; ?>"><?= htmlspecialchars($f->location_name); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mp-form-group">
          <label>Temperature (°C) <span class="text-danger">*</span></label>
          <input type="number" step="0.01" class="mp-form-control" name="temperature_c" placeholder="-18.00" required>
        </div>
        <div class="mp-form-group">
          <label>Notes</label>
          <input type="text" class="mp-form-control" name="notes" placeholder="Optional">
        </div>
      </div>

      <div style="margin-top:24px; text-align:right;">
        <button type="submit" class="mp-qa-btn blue"><i class="fa fa-thermometer"></i> Record Temperature</button>
      </div>
    </form>
  </div>
</div>

<div class="mp-card-form">
  <div class="mp-card-head"><h3>Recent Readings</h3></div>
  <div class="mp-card-body">
    <table class="mp-batch-items">
      <thead>
        <tr>
          <th>Time</th>
          <th>Location</th>
          <th>Temp</th>
          <th>Status</th>
          <th>Notes</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($logs)): ?>
          <?php foreach ($logs as $l): ?>
          <tr>
            <td><?= date('Y-m-d H:i', strtotime($l->recorded_at)); ?></td>
            <td>
              <?php
              $loc = null;
              foreach ($freezers as $f) { if ($f->id == $l->freezer_location_id) { $loc = $f; break; } }
              echo $loc ? htmlspecialchars($loc->location_name) : '---';
              ?>
            </td>
            <td><?= number_format($l->temperature_c, 2); ?> °C</td>
            <td>
              <span class="label label-<?= $l->status === 'normal' ? 'success' : ($l->status === 'warning' ? 'warning' : 'danger'); ?>">
                <?= ucfirst($l->status); ?>
              </span>
            </td>
            <td><?= htmlspecialchars($l->notes ?: '---'); ?></td>
          </tr>
          <?php endforeach; ?>
        <?php else: ?>
        <tr><td colspan="5" class="text-center text-muted">No temperature readings yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
