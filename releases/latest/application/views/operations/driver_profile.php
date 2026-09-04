<?php $this->load->view('admin/desktop/_styles'); ?>
<?php $CI =& get_instance(); ?>
<style>
.mp-info-row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--mp-border);font-size:13px}
.mp-info-row:last-child{border-bottom:0}
.mp-info-row b{color:var(--mp-ink)}
.mp-info-row span{color:var(--mp-text);text-align:right}
.mp-profile-name{font-size:20px;font-weight:700;color:var(--mp-ink);text-align:center;margin:0}
.mp-profile-role{font-size:13px;color:var(--mp-muted);text-align:center;margin:2px 0 16px}
.mp-veh-grid{display:grid;grid-template-columns:repeat(4,1fr);gap:16px}
.mp-veh-grid .label{font-size:11px;font-weight:700;color:var(--mp-muted);text-transform:uppercase;letter-spacing:.04em}
.mp-veh-grid .value{font-size:14px;color:var(--mp-text);margin-top:4px}
</style>

<div class="mp-page-head">
  <div>
    <h2><?= htmlspecialchars($page_title); ?></h2>
    <div class="mp-page-sub">Driver profile, compliance and route history</div>
  </div>
  <a href="<?= base_url('operations/delivery_scheduling'); ?>" class="mp-qa-btn blue"><i class="fa fa-arrow-left"></i> Back to Schedules</a>
</div>

<div style="display:grid;grid-template-columns:340px minmax(0,1fr);gap:24px;align-items:start;">
  <div>
    <div class="mp-card-form" style="margin-bottom:24px">
      <div class="mp-card-body">
        <h3 class="mp-profile-name"><?= htmlspecialchars($driver->name ?? 'Unknown'); ?></h3>
        <p class="mp-profile-role"><?= ucfirst(str_replace('_', ' ', $driver->employment_type ?? 'Contract')); ?> Driver</p>
        <div class="mp-info-row"><b>Status</b><span>
          <?php $badgeMap = ['active'=>'success','on_leave'=>'warning','inactive'=>'default','suspended'=>'danger'];
            $st = $driver->status ?? 'active';
            echo '<span class="label label-'.($badgeMap[$st]??'default').'">'.ucfirst(str_replace('_',' ',$st)).'</span>';
          ?>
        </span></div>
        <div class="mp-info-row"><b>Phone</b><span><?= htmlspecialchars($driver->phone ?: '-'); ?></span></div>
        <div class="mp-info-row"><b>Email</b><span><?= htmlspecialchars($driver->email ?: '-'); ?></span></div>
        <div class="mp-info-row"><b>Address</b><span><?= htmlspecialchars($driver->address ?: '-'); ?></span></div>
        <div class="mp-info-row"><b>Hire Date</b><span><?= is_valid_date($driver->hire_date) ? show_date($driver->hire_date) : '-'; ?></span></div>
        <div class="mp-info-row"><b>Emergency Contact</b><span>
          <?= htmlspecialchars(($driver->emergency_contact_name ?: '') . ($driver->emergency_contact_phone ? ' ('.$driver->emergency_contact_phone.')' : '')) ?: '-'; ?>
        </span></div>
      </div>
    </div>

    <div class="mp-card-form" style="margin-bottom:24px">
      <div class="mp-card-head"><h3><i class="fa fa-id-card-o"></i> Compliance</h3></div>
      <div class="mp-card-body">
        <div class="mp-info-row"><b>NIN</b><span><?= htmlspecialchars($driver->nin ?: '-'); ?></span></div>
        <div class="mp-info-row"><b>Driver's License</b><span><?= htmlspecialchars($driver->driver_license ?: '-'); ?></span></div>
        <div class="mp-info-row"><b>License Expiry</b><span>
          <?php
            if (is_valid_date($driver->license_expiry)) {
                $exp = strtotime($driver->license_expiry);
                $days = ceil(($exp - time()) / 86400);
                $cls = $days < 30 ? 'text-danger' : ($days < 90 ? 'text-warning' : 'text-success');
                echo '<span class="'.$cls.'">'.show_date($driver->license_expiry).' (' . ($days < 0 ? 'EXPIRED' : $days.' days left') . ')</span>';
            } else { echo '-'; }
          ?>
        </span></div>
      </div>
    </div>
  </div>

  <div>
    <div class="mp-kpi-grid" style="margin-bottom:24px">
      <div class="mp-kpi-card"><div class="mp-kpi-icon" style="background:rgba(0,87,255,.1);color:var(--mp-primary)"><i class="fa fa-truck"></i></div><div class="mp-kpi-label">Total Routes</div><div class="mp-kpi-value"><?= $stats['total_routes']; ?></div></div>
      <div class="mp-kpi-card"><div class="mp-kpi-icon" style="background:rgba(5,150,105,.1);color:var(--mp-success)"><i class="fa fa-check-circle"></i></div><div class="mp-kpi-label">Completed</div><div class="mp-kpi-value"><?= $stats['completed']; ?></div></div>
      <div class="mp-kpi-card"><div class="mp-kpi-icon" style="background:rgba(245,158,11,.1);color:var(--mp-warning)"><i class="fa fa-map-marker"></i></div><div class="mp-kpi-label">Total Stops</div><div class="mp-kpi-value"><?= $stats['total_stops']; ?></div></div>
      <div class="mp-kpi-card"><div class="mp-kpi-icon" style="background:rgba(124,58,237,.1);color:#7C3AED"><i class="fa fa-percent"></i></div><div class="mp-kpi-label">Delivery Rate</div><div class="mp-kpi-value"><?= $stats['total_stops'] > 0 ? round(($stats['delivered_stops'] / $stats['total_stops']) * 100) : 0; ?>%</div></div>
    </div>

    <div class="mp-card-form" style="margin-bottom:24px">
      <div class="mp-card-head"><h3><i class="fa fa-car"></i> Vehicle Details</h3></div>
      <div class="mp-card-body">
        <div class="mp-veh-grid">
          <div><div class="label">Make / Model</div><div class="value"><?= htmlspecialchars($driver->vehicle ?: '-'); ?></div></div>
          <div><div class="label">Type</div><div class="value"><?= ucfirst($driver->vehicle_type ?: '-'); ?></div></div>
          <div><div class="label">Color</div><div class="value"><?= htmlspecialchars($driver->vehicle_color ?: '-'); ?></div></div>
          <div><div class="label">Plate Number</div><div class="value"><?= htmlspecialchars($driver->license_plate ?: '-'); ?></div></div>
        </div>
      </div>
    </div>

    <div class="mp-card-form" style="margin-bottom:24px">
      <div class="mp-card-head"><h3><i class="fa fa-road"></i> Route History</h3></div>
      <div class="mp-card-body" style="padding:0!important">
        <div class="mp-dt-scroll">
          <table class="mp-static-table">
            <thead>
              <tr>
                <th>Route Name</th>
                <th>Date</th>
                <th>Status</th>
                <th>Stops</th>
                <th>Delivered</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              <?php
              $stBadge = ['planned'=>'default','ready'=>'info','out_for_delivery'=>'primary','completed'=>'success','cancelled'=>'danger'];
              if(!empty($routes)){ foreach($routes as $r){
              ?>
              <tr>
                <td><?= htmlspecialchars($r->route_name ?: $r->schedule_code); ?></td>
                <td><?= show_date($r->schedule_date); ?></td>
                <td><span class="label label-<?= $stBadge[$r->status]??'default'; ?>"><?= ucfirst(str_replace('_',' ',$r->status)); ?></span></td>
                <td><?= (int)($r->stops_count ?? 0); ?></td>
                <td><?= (int)($r->delivered_count ?? 0); ?> / <?= (int)($r->stops_count ?? 0); ?></td>
                <td><div class="mp-actions"><a href="<?= base_url('operations/delivery_schedule_view/'.$r->id); ?>" class="mp-edit"><i class="fa fa-eye"></i></a></div></td>
              </tr>
              <?php }} else { ?>
              <tr><td colspan="6" class="text-center text-muted">No routes assigned yet.</td></tr>
              <?php } ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <?php if(!empty($driver->notes)){ ?>
    <div class="mp-card-form" style="margin-bottom:0">
      <div class="mp-card-head"><h3><i class="fa fa-sticky-note-o"></i> Notes</h3></div>
      <div class="mp-card-body">
        <p style="margin:0;font-size:13px;line-height:1.6"><?= nl2br(htmlspecialchars($driver->notes)); ?></p>
      </div>
    </div>
    <?php } ?>
  </div>
</div>
