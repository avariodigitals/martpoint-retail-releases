<?php $this->load->view('admin/desktop/_styles'); ?>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Serialised equipment installed at <?= htmlspecialchars(mp_label('customer')); ?> sites &middot; warranty and calibration tracking</div>
  </div>
</div>

<div class="mp-card-form" style="margin-top:16px;">
  <div class="mp-card-body">
    <form method="get" action="<?= base_url('operations/equipment_register'); ?>" class="mp-form-grid" style="margin:0;">
      <div class="mp-form-group" style="grid-column:span 2;">
        <label>Search serial / model / <?= htmlspecialchars(strtolower(mp_label('customer'))); ?></label>
        <input type="text" name="search" class="mp-form-control" placeholder="e.g. HA3000-SN-2026-…" value="<?= htmlspecialchars($search ?? ''); ?>">
      </div>
      <div class="mp-form-group" style="display:flex;align-items:flex-end;gap:8px;">
        <button type="submit" class="mp-qa-btn blue"><i class="fa fa-search"></i> Find</button>
        <a href="<?= base_url('operations/equipment_register?calibration_due=1'); ?>" class="mp-qa-btn <?= !empty($calibration_due) ? 'purple' : 'blue'; ?>"><i class="fa fa-sliders"></i> Calibration Due</a>
        <a href="<?= base_url('operations/equipment_register?warranty_days=90'); ?>" class="mp-qa-btn <?= !empty($warranty_days) ? 'purple' : 'blue'; ?>"><i class="fa fa-shield"></i> Warranty ≤ 90d</a>
        <?php if(!empty($calibration_due) || !empty($warranty_days)): ?>
        <a href="<?= base_url('operations/equipment_register'); ?>" class="mp-qa-btn red"><i class="fa fa-times"></i> Clear</a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<div class="mp-table-wrap" style="margin-top:16px;">
  <div class="mp-card-head"><h3><i class="fa fa-microchip"></i> Installed Equipment</h3></div>
  <div class="mp-card-body" style="padding:0;">
    <div class="mp-dt-scroll">
      <table class="table mp-dt-table" style="margin:0;">
        <thead>
          <tr>
            <th>#</th>
            <th><?= mp_label('customer'); ?></th>
            <th>Equipment / Model</th>
            <th>Serial No</th>
            <th>Site</th>
            <th>Sold</th>
            <th>Installed</th>
            <th>Warranty</th>
            <th>Next Calibration</th>
            <th>Status</th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php if(!empty($equipment)): $i=1; foreach($equipment as $e):
            $warranty_ok = !empty($e->warranty_end) && $e->warranty_end >= date('Y-m-d');
            $cal_due = !empty($e->next_calibration_date) && $e->next_calibration_date <= date('Y-m-d', strtotime('+30 days'));
            $cal_overdue = !empty($e->next_calibration_date) && $e->next_calibration_date < date('Y-m-d');
          ?>
          <tr>
            <td><?= $i++; ?></td>
            <td><a href="<?= base_url('customers/profile/'.$e->customer_id); ?>"><?= htmlspecialchars($e->customer_name ?? '-'); ?></a></td>
            <td><strong><?= htmlspecialchars($e->item_name ?? $e->model ?? '-'); ?></strong></td>
            <td><code><?= htmlspecialchars($e->serial_number ?? '-'); ?></code></td>
            <td><?= htmlspecialchars($e->site_name ?? ($e->site_address ? trim($e->site_address.', '.$e->site_city) : '—')); ?></td>
            <td><?= !empty($e->sale_date) ? show_date($e->sale_date) : '—'; ?></td>
            <td><?= is_valid_date($e->install_date) ? show_date($e->install_date) : '<span class="text-muted">—</span>'; ?></td>
            <td>
              <?php if(!empty($e->warranty_end)): ?>
                <span class="mp-pill <?= $warranty_ok ? 'paid' : 'unpaid'; ?>"><?= show_date($e->warranty_end); ?></span>
              <?php else: ?><span class="text-muted">—</span><?php endif; ?>
            </td>
            <td>
              <?php if(!empty($e->next_calibration_date)): ?>
                <span class="mp-pill <?= $cal_overdue ? 'unpaid' : ($cal_due ? 'partial' : 'paid'); ?>"><?= show_date($e->next_calibration_date); ?></span>
              <?php else: ?><span class="text-muted">—</span><?php endif; ?>
            </td>
            <td><span class="mp-badge" style="background:var(--mp-surface);border:1px solid var(--mp-border);"><?= ucwords(str_replace('_',' ',$e->equipment_status ?? 'installed')); ?></span></td>
            <td>
              <a href="<?= base_url('operations/equipment_view/'.$e->id); ?>" class="mp-qa-btn teal" style="padding:5px 10px;"><i class="fa fa-eye"></i> View</a>
              <?php if($e->job_count > 0): ?><span class="mp-badge" title="Service jobs"><?= $e->job_count; ?> job<?= $e->job_count>1?'s':''; ?></span><?php endif; ?>
            </td>
          </tr>
          <?php endforeach; else: ?>
          <tr><td colspan="11" class="text-center text-muted" style="padding:40px;">No equipment on the register yet — serialised sales create entries automatically.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
