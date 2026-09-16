<?php $this->load->view('admin/desktop/_styles'); ?>
<div class="mp-page-head">
  <div>
    <h2>Freezer Locations</h2>
    <div class="mp-page-sub">Add cold rooms, freezers, and chillers for temperature tracking.</div>
  </div>
  <a href="<?= base_url('butchery'); ?>" class="mp-qa-btn blue"><i class="fa fa-arrow-left"></i> Back</a>
</div>

<?php if (!empty($message)): ?>
<div class="alert alert-success" style="margin-top:20px;"><?= htmlspecialchars($message); ?></div>
<?php endif; ?>

<div class="mp-card-form" style="margin-bottom:20px;">
  <div class="mp-card-head"><h3>Add Freezer / Cold Room</h3></div>
  <div class="mp-card-body">
    <form method="post" action="<?= base_url('butchery/freezers'); ?>">
      <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

      <div class="mp-form-grid">
        <div class="mp-form-group">
          <label>Location Name <span class="text-danger">*</span></label>
          <input type="text" class="mp-form-control" name="location_name" placeholder="e.g. Main Freezer" required>
        </div>
        <div class="mp-form-group">
          <label>Location Code</label>
          <input type="text" class="mp-form-control" name="location_code" placeholder="e.g. FZ-01">
        </div>
        <div class="mp-form-group">
          <label>Min Temp (°C)</label>
          <input type="number" step="0.1" class="mp-form-control" name="temp_min" placeholder="-25">
        </div>
        <div class="mp-form-group">
          <label>Max Temp (°C)</label>
          <input type="number" step="0.1" class="mp-form-control" name="temp_max" placeholder="-18">
        </div>
        <div class="mp-form-group">
          <label>Capacity Volume</label>
          <input type="number" step="0.01" class="mp-form-control" name="capacity_volume" placeholder="0.00">
        </div>
      </div>

      <div style="margin-top:24px; text-align:right;">
        <button type="submit" class="mp-qa-btn blue"><i class="fa fa-save"></i> Save Freezer</button>
      </div>
    </form>
  </div>
</div>

<div class="mp-card-form">
  <div class="mp-card-head"><h3>Existing Freezers</h3></div>
  <div class="mp-card-body">
    <table class="mp-batch-items">
      <thead>
        <tr>
          <th>Name</th>
          <th>Code</th>
          <th>Min</th>
          <th>Max</th>
          <th>Capacity</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($freezers)): ?>
          <?php foreach ($freezers as $f): ?>
          <tr>
            <td><?= htmlspecialchars($f->location_name); ?></td>
            <td><?= htmlspecialchars($f->location_code ?: '---'); ?></td>
            <td><?= $f->temp_min !== null ? number_format($f->temp_min, 1) : '---'; ?> °C</td>
            <td><?= $f->temp_max !== null ? number_format($f->temp_max, 1) : '---'; ?> °C</td>
            <td><?= number_format($f->capacity_volume, 2); ?></td>
          </tr>
          <?php endforeach; ?>
        <?php else: ?>
        <tr><td colspan="5" class="text-center text-muted">No freezers added yet.</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
