<?php $tyre = !empty($tyre_ctx); ?>
<div class="mp-page-head">
  <div>
    <h2><?= $tyre ? 'Customer Vehicles' : 'Vehicle Inventory'; ?></h2>
    <div class="mp-page-sub"><?= $tyre ? 'Vehicles on file for fitting services — plates, makes and models' : 'Manage available, reserved, and sold vehicles'; ?></div>
  </div>
  <a href="<?= base_url('automobile/add'); ?>" class="mp-btn green" style="margin-left:auto;">
    <i class="fa fa-plus"></i> <?= $tyre ? 'Add Customer Vehicle' : 'Add Vehicle'; ?>
  </a>
</div>

<?php if (!$tyre): ?>
<div class="mp-card" style="margin-top:16px;">
  <div class="mp-card-body">
    <form method="get" action="<?= base_url('automobile/list'); ?>" class="form-inline">
      <div class="form-group">
        <label>Filter by status:</label>
        <select name="status" class="form-control" onchange="this.form.submit()">
          <option value="">All</option>
          <option value="available" <?= $status === 'available' ? 'selected' : ''; ?>>Available</option>
          <option value="reserved" <?= $status === 'reserved' ? 'selected' : ''; ?>>Reserved</option>
          <option value="sold" <?= $status === 'sold' ? 'selected' : ''; ?>>Sold</option>
        </select>
      </div>
    </form>
  </div>
</div>
<?php endif; ?>

<div class="mp-table-wrap box" style="margin-top:16px;">
  <table class="table table-striped table-hover">
    <thead>
      <tr>
        <th>Image</th>
        <th>Code</th>
        <th>Make / Model</th>
        <th>Year</th>
        <?php if (!$tyre): ?><th>Condition</th><?php endif; ?>
        <th>Mileage</th>
        <?php if (!$tyre): ?><th>Price</th><th>Status</th><?php endif; ?>
        <th><?= $tyre ? 'Owner / Customer' : 'Customer'; ?></th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
      <?php if (!empty($vehicles)): ?>
        <?php foreach ($vehicles as $v): ?>
        <tr>
          <td>
            <?php if (!empty($v->image_path) && file_exists(FCPATH . $v->image_path)): ?>
              <img src="<?= base_url($v->image_path); ?>" style="width:60px; height:45px; object-fit:cover; border-radius:4px;">
            <?php else: ?>
              <span class="text-muted">No image</span>
            <?php endif; ?>
          </td>
          <td><?= htmlspecialchars($v->vehicle_code ?: '-'); ?></td>
          <td><?= htmlspecialchars($v->make . ' ' . $v->model); ?></td>
          <td><?= htmlspecialchars($v->year ?: '-'); ?></td>
          <?php if ($tyre): ?>
          <td><?= htmlspecialchars($v->license_plate ?: '-'); ?></td>
          <?php else: ?>
          <td><?= ucfirst($v->vehicle_condition); ?></td>
          <?php endif; ?>
          <td><?= $v->mileage ? number_format($v->mileage) . ' km' : '-'; ?></td>
          <?php if (!$tyre): ?>
          <td><?= number_format($v->price, 2); ?></td>
          <td>
            <span class="label label-<?= $v->status === 'available' ? 'success' : ($v->status === 'reserved' ? 'warning' : 'primary'); ?>">
              <?= ucfirst($v->status); ?>
            </span>
          </td>
          <?php endif; ?>
          <td><?= htmlspecialchars($v->customer_name ?: '-'); ?></td>
          <td>
            <a href="<?= base_url('automobile/edit/' . $v->id); ?>" class="btn btn-xs btn-primary" title="Edit"><i class="fa fa-pencil"></i></a>
            <?php if (!$tyre): ?>
            <?php if($v->status !== 'sold'): ?>
            <a href="<?= base_url('automobile/sell/' . $v->id); ?>" class="btn btn-xs btn-success" title="Sell"><i class="fa fa-check"></i></a>
            <?php else: ?>
            <a href="<?= base_url('automobile/receipt/' . $v->id); ?>" class="btn btn-xs btn-info" title="Receipt"><i class="fa fa-file-text-o"></i></a>
            <?php endif; ?>
            <?php endif; ?>
            <?php if (!empty($v->customer_id)): ?>
            <a href="<?= base_url('automobile/history/' . (int) $v->customer_id); ?>" class="btn btn-xs btn-default" title="Fitting history"><i class="fa fa-wrench"></i></a>
            <?php endif; ?>
            <a href="<?= base_url('automobile/parts/' . (int) $v->id); ?>" class="btn btn-xs btn-default" title="Parts for this vehicle"><i class="fa fa-cubes"></i></a>
            <a href="<?= base_url('automobile/delete/' . $v->id); ?>" class="btn btn-xs btn-danger veh-del" title="Delete"><i class="fa fa-trash"></i></a>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="<?= $tyre ? 8 : 10; ?>" class="text-center text-muted"><?= $tyre ? 'No customer vehicles yet. Add a vehicle when a customer brings it in for fitting.' : 'No vehicles found.'; ?></td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

<?php $this->load->view('components/dialog'); ?>
<script>
document.querySelectorAll('.veh-del').forEach(function(a){
  a.addEventListener('click', function(e){
    e.preventDefault();
    var href = a.getAttribute('href');
    if (window.mpConfirmAsync) { mpConfirmAsync('Delete this vehicle?').then(function(ok){ if (ok) window.location = href; }); }
    else { window.location = href; }
  });
});
</script>
