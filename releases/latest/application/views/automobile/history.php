<div class="mp-page-head">
  <div>
    <h2>Vehicle History — <?= htmlspecialchars($customer->customer_name); ?></h2>
    <div class="mp-page-sub">Fittings, purchases and services recorded against this customer's vehicles</div>
  </div>
  <a href="<?= base_url('automobile/reminders'); ?>" class="mp-btn" style="margin-left:auto;">
    <i class="fa fa-bell-o"></i> Service Reminders
    <span class="label label-<?= !empty($reminders_on) ? 'success' : 'default'; ?>" style="margin-left:6px;"><?= !empty($reminders_on) ? 'On' : 'Off'; ?></span>
  </a>
  <a href="<?= base_url('automobile/list'); ?>" class="mp-btn">
    <i class="fa fa-arrow-left"></i> Back to Vehicles
  </a>
</div>

<?php include APPPATH . 'views/comman/code_flashdata.php'; ?>

<?php if (empty($vehicles)): ?>
  <div class="mp-card" style="margin-top:16px;">
    <div class="mp-card-body">
      <p class="text-muted" style="margin:0;">No vehicles are recorded for this customer yet. Add one from <a href="<?= base_url('automobile/add'); ?>">Vehicle Inventory</a> and pick this customer on it.</p>
    </div>
  </div>
<?php else: ?>
  <?php foreach ($vehicles as $v): ?>
    <?php
      $vid = (int) $v->id;
      $visits = $groups[$vid] ?? [];
      $svc_rows = $services[$vid] ?? [];
      $vlabel = $labels[$vid] ?? trim($v->make . ' ' . $v->model);
    ?>
    <div class="mp-card" style="margin-top:16px;">
      <div class="mp-card-body" style="padding-bottom:8px;">
        <h3 style="margin:0 0 4px; font-size:16px;"><i class="fa fa-car"></i> <?= htmlspecialchars($vlabel); ?></h3>
        <span class="text-muted"><?= count($visits); ?> billed visit<?= count($visits) === 1 ? '' : 's'; ?> · <?= count($svc_rows); ?> service record<?= count($svc_rows) === 1 ? '' : 's'; ?></span>
        <a href="<?= base_url('automobile/service_form/' . $vid); ?>" class="mp-btn" style="float:right; padding:6px 14px; font-size:13px;">
          <i class="fa fa-wrench"></i> Record Service
        </a>
        <a href="<?= base_url('automobile/parts/' . $vid); ?>" class="mp-btn" style="float:right; padding:6px 14px; font-size:13px; margin-right:8px;">
          <i class="fa fa-cubes"></i> Parts for this vehicle
        </a>
      </div>
    </div>

    <div class="mp-card" style="margin-top:8px;">
      <div class="mp-card-head"><h3 style="font-size:14px;">Service History</h3></div>
      <div class="mp-table-wrap box" style="border:0;">
        <table class="table table-striped table-hover">
          <thead>
            <tr>
              <th>Date</th>
              <th>Service</th>
              <th class="text-right">Mileage</th>
              <th>Technician</th>
              <th>Notes</th>
              <th>Next Due</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($svc_rows)): ?>
              <tr><td colspan="7" class="text-muted">No services recorded for this vehicle yet. Use <b>Record Service</b> to log fitting work, set the mileage, and schedule the next due date.</td></tr>
            <?php else: foreach ($svc_rows as $sr): ?>
              <tr>
                <td><?= htmlspecialchars((string) $sr->service_date); ?></td>
                <td><?= htmlspecialchars($service_types[$sr->service_type] ?? ucwords(str_replace('_', ' ', (string) $sr->service_type))); ?></td>
                <td class="text-right"><?= $sr->mileage_at_service !== null ? number_format((int) $sr->mileage_at_service) : '—'; ?></td>
                <td><?= htmlspecialchars(trim(explode(' ', (string) $sr->technician_name)[0]) ?: '—'); ?></td>
                <td><?= htmlspecialchars((string) ($sr->notes ?: '')); ?></td>
                <td>
                  <?php if (!empty($sr->next_due_date)): ?>
                    <?= htmlspecialchars((string) $sr->next_due_date); ?><?= (int)$sr->next_due_mileage > 0 ? ' · ' . number_format((int) $sr->next_due_mileage) . ' km' : ''; ?>
                  <?php else: ?>
                    <span class="text-muted">—</span>
                  <?php endif; ?>
                </td>
                <td style="white-space:nowrap;">
                  <a href="<?= base_url('automobile/service_form/' . $vid . '/' . (int) $sr->id); ?>" title="Edit"><i class="fa fa-pencil"></i></a>
                  &nbsp;<a href="<?= base_url('automobile/service_delete/' . (int) $sr->id); ?>" class="veh-svc-del" title="Remove"><i class="fa fa-trash text-danger"></i></a>
                </td>
              </tr>
            <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    </div>

    <?php if (!empty($visits)): ?>
    <div class="mp-table-wrap box" style="margin-top:8px;">
      <table class="table table-striped table-hover">
        <thead>
          <tr>
            <th>Date</th>
            <th>Billing Reference</th>
            <th>Fitted Items</th>
            <th class="text-right">Total</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($visits as $visit): ?>
            <?php $s = $visit['sale']; ?>
            <tr>
              <td><?= htmlspecialchars((string) ($s->sales_date ?: '')); ?></td>
              <td>
                <a href="<?= base_url('sales/invoice/' . (int) $s->id); ?>"><?= htmlspecialchars((string) ($s->sales_code ?: ('#' . (int) $s->id))); ?></a>
              </td>
              <td>
                <?php if (empty($visit['lines'])): ?>
                  <span class="text-muted">—</span>
                <?php else: ?>
                  <?php foreach ($visit['lines'] as $ln): ?>
                    <div><?= htmlspecialchars((string) ($ln->description ?: ($ln->item_name ?: 'Item'))); ?> × <?= rtrim(rtrim(number_format((float) $ln->sales_qty, 2, '.', ''), '0'), '.'); ?></div>
                  <?php endforeach; ?>
                <?php endif; ?>
              </td>
              <td class="text-right"><?= number_format((float) $s->grand_total, 2); ?></td>
              <td><span class="label label-<?= $s->sales_status === 'Final' ? 'success' : 'warning'; ?>"><?= htmlspecialchars((string) $s->sales_status); ?></span></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
    <?php endif; ?>
  <?php endforeach; ?>
<?php endif; ?>

<?php $this->load->view('components/dialog'); ?>
<script>
document.querySelectorAll('.veh-svc-del').forEach(function(a){
  a.addEventListener('click', function(e){
    e.preventDefault();
    var href = a.getAttribute('href');
    if (window.mpConfirmAsync) { mpConfirmAsync('Remove this service record?').then(function(ok){ if (ok) window.location = href; }); }
    else { window.location = href; }
  });
});
</script>
