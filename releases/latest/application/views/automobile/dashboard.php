<style>
  .am-dashboard-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-top: 24px; }
  .mp-card-form { display: block; background: #fff; border: 1px solid #E5E7EB; border-radius: 12px; color: inherit; transition: transform .15s, box-shadow .15s; }
  .mp-card-form:hover { transform: translateY(-3px); box-shadow: 0 10px 28px rgba(0,0,0,.08); }
  @media(max-width:1023px){ .am-dashboard-grid { grid-template-columns: repeat(2, 1fr); } }
  @media(max-width:767px){ .am-dashboard-grid { grid-template-columns: 1fr; } }
</style>

<div class="mp-page-head">
  <div>
    <h2>Automobile</h2>
    <div class="mp-page-sub">Track vehicles from listing to sale</div>
  </div>
</div>

<div class="am-dashboard-grid">
  <a href="<?= base_url('automobile/list?status=available'); ?>" class="mp-card-form" style="text-decoration:none;">
    <div class="mp-card-body" style="text-align:center;">
      <i class="fa fa-car" style="font-size:28px; color:var(--mp-success); display:block; margin-bottom:8px;"></i>
      <h3><?= number_format($counts['available'] ?? 0); ?></h3>
      <p class="mp-muted">Available</p>
    </div>
  </a>
  <a href="<?= base_url('automobile/list?status=reserved'); ?>" class="mp-card-form" style="text-decoration:none;">
    <div class="mp-card-body" style="text-align:center;">
      <i class="fa fa-bookmark" style="font-size:28px; color:var(--mp-warning); display:block; margin-bottom:8px;"></i>
      <h3><?= number_format($counts['reserved'] ?? 0); ?></h3>
      <p class="mp-muted">Reserved</p>
    </div>
  </a>
  <a href="<?= base_url('automobile/list?status=sold'); ?>" class="mp-card-form" style="text-decoration:none;">
    <div class="mp-card-body" style="text-align:center;">
      <i class="fa fa-check-circle" style="font-size:28px; color:var(--mp-primary); display:block; margin-bottom:8px;"></i>
      <h3><?= number_format($counts['sold'] ?? 0); ?></h3>
      <p class="mp-muted">Sold</p>
    </div>
  </a>
  <a href="<?= base_url('automobile/add'); ?>" class="mp-card-form" style="text-decoration:none;">
    <div class="mp-card-body" style="text-align:center;">
      <i class="fa fa-plus-circle" style="font-size:28px; color:var(--mp-info); display:block; margin-bottom:8px;"></i>
      <h3>Add</h3>
      <p class="mp-muted">List a new vehicle</p>
    </div>
  </a>
</div>

<?php if (!empty($recent)): ?>
<div class="mp-card" style="margin-top:24px;">
  <div class="mp-card-head">
    <h3>Recent Vehicles</h3>
  </div>
  <div class="mp-card-body">
    <div class="mp-table-wrap box">
      <table class="table table-striped">
        <thead>
          <tr>
            <th>Code</th>
            <th>Vehicle</th>
            <th>Year</th>
            <th>Price</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recent as $v): ?>
          <tr>
            <td><?= htmlspecialchars($v->vehicle_code ?: '-'); ?></td>
            <td><?= htmlspecialchars($v->make . ' ' . $v->model); ?></td>
            <td><?= htmlspecialchars($v->year ?: '-'); ?></td>
            <td><?= number_format($v->price, 2); ?></td>
            <td><span class="label label-<?= $v->status === 'available' ? 'success' : ($v->status === 'reserved' ? 'warning' : 'primary'); ?>"><?= ucfirst($v->status); ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>
