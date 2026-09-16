<div class="mp-page-head">
  <div>
    <h2>Vehicle Inventory</h2>
    <div class="mp-page-sub">Manage available, reserved, and sold vehicles</div>
  </div>
  <a href="<?= base_url('automobile/add'); ?>" class="mp-btn green" style="margin-left:auto;">
    <i class="fa fa-plus"></i> Add Vehicle
  </a>
</div>

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

<div class="mp-table-wrap box" style="margin-top:16px;">
  <table class="table table-striped table-hover">
    <thead>
      <tr>
        <th>Image</th>
        <th>Code</th>
        <th>Make / Model</th>
        <th>Year</th>
        <th>Condition</th>
        <th>Mileage</th>
        <th>Price</th>
        <th>Status</th>
        <th>Customer</th>
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
          <td><?= ucfirst($v->vehicle_condition); ?></td>
          <td><?= $v->mileage ? number_format($v->mileage) . ' km' : '-'; ?></td>
          <td><?= number_format($v->price, 2); ?></td>
          <td>
            <span class="label label-<?= $v->status === 'available' ? 'success' : ($v->status === 'reserved' ? 'warning' : 'primary'); ?>">
              <?= ucfirst($v->status); ?>
            </span>
          </td>
          <td><?= htmlspecialchars($v->customer_name ?: '-'); ?></td>
          <td>
            <a href="<?= base_url('automobile/edit/' . $v->id); ?>" class="btn btn-xs btn-primary" title="Edit"><i class="fa fa-pencil"></i></a>
            <?php if($v->status !== 'sold'): ?>
            <a href="<?= base_url('automobile/sell/' . $v->id); ?>" class="btn btn-xs btn-success" title="Sell"><i class="fa fa-check"></i></a>
            <?php else: ?>
            <a href="<?= base_url('automobile/receipt/' . $v->id); ?>" class="btn btn-xs btn-info" title="Receipt"><i class="fa fa-file-text-o"></i></a>
            <?php endif; ?>
            <a href="<?= base_url('automobile/delete/' . $v->id); ?>" class="btn btn-xs btn-danger" title="Delete" onclick="return confirm('Delete this vehicle?')"><i class="fa fa-trash"></i></a>
          </td>
        </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td colspan="10" class="text-center text-muted">No vehicles found.</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
