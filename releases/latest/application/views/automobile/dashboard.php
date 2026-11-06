<style>
  .am-dashboard-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-top: 24px; }
  .mp-card-form { display: block; background: #fff; border: 1px solid #E5E7EB; border-radius: 12px; color: inherit; transition: transform .15s, box-shadow .15s; }
  .mp-card-form:hover { transform: translateY(-3px); box-shadow: 0 10px 28px rgba(0,0,0,.08); }
  @media(max-width:1023px){ .am-dashboard-grid { grid-template-columns: repeat(2, 1fr); } }
  @media(max-width:767px){ .am-dashboard-grid { grid-template-columns: 1fr; } }
</style>
<?php
$tyre = !empty($tyre_ctx);
$am_total = array_sum([$counts['available'] ?? 0, $counts['reserved'] ?? 0, $counts['sold'] ?? 0]);
?>
<div class="mp-page-head">
  <div>
    <h2><?= $tyre ? 'Customer Vehicles' : 'Automobile'; ?></h2>
    <div class="mp-page-sub"><?= $tyre ? 'The vehicles your workshop keeps on file for fitting services' : 'Track vehicles from listing to sale'; ?></div>
  </div>
</div>

<div class="am-dashboard-grid">
<?php if ($tyre): ?>
  <a href="<?= base_url('automobile/list'); ?>" class="mp-card-form" style="text-decoration:none;">
    <div class="mp-card-body" style="text-align:center;">
      <i class="fa fa-car" style="font-size:28px; color:var(--mp-primary); display:block; margin-bottom:8px;"></i>
      <h3><?= number_format($am_total); ?></h3>
      <p class="mp-muted">Vehicles on file</p>
    </div>
  </a>
  <a href="<?= base_url('automobile/list?status=reserved'); ?>" class="mp-card-form" style="text-decoration:none;">
    <div class="mp-card-body" style="text-align:center;">
      <i class="fa fa-wrench" style="font-size:28px; color:var(--mp-warning); display:block; margin-bottom:8px;"></i>
      <h3><?= number_format($counts['reserved'] ?? 0); ?></h3>
      <p class="mp-muted">Currently in the workshop</p>
    </div>
  </a>
  <a href="<?= base_url('automobile/list?status=sold'); ?>" class="mp-card-form" style="text-decoration:none;">
    <div class="mp-card-body" style="text-align:center;">
      <i class="fa fa-user-circle" style="font-size:28px; color:var(--mp-info); display:block; margin-bottom:8px;"></i>
      <h3><?= number_format($counts['sold'] ?? 0); ?></h3>
      <p class="mp-muted">Sold or delivered to owner</p>
    </div>
  </a>
  <a href="<?= base_url('automobile/add'); ?>" class="mp-card-form" style="text-decoration:none;">
    <div class="mp-card-body" style="text-align:center;">
      <i class="fa fa-plus-circle" style="font-size:28px; color:var(--mp-success); display:block; margin-bottom:8px;"></i>
      <h3>Add</h3>
      <p class="mp-muted">Register a customer vehicle</p>
    </div>
  </a>
<?php else: ?>
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
<?php endif; ?>
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
            <?php if ($tyre): ?><th>Plate</th><?php else: ?><th>Price</th><?php endif; ?>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($recent as $v): ?>
          <tr>
            <td><?= htmlspecialchars($v->vehicle_code ?: '-'); ?></td>
            <td><?= htmlspecialchars($v->make . ' ' . $v->model); ?></td>
            <td><?= htmlspecialchars($v->year ?: '-'); ?></td>
            <?php if ($tyre): ?>
            <td><?= htmlspecialchars($v->license_plate ?: '-'); ?></td>
            <?php else: ?>
            <td><?= number_format($v->price, 2); ?></td>
            <?php endif; ?>
            <td><span class="label label-<?= $v->status === 'available' ? 'success' : ($v->status === 'reserved' ? 'warning' : 'primary'); ?>"><?= ucfirst($v->status); ?></span></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php endif; ?>
