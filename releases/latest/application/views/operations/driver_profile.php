<!DOCTYPE html>
<html>
<head><?php $this->load->view('comman/code_css.php'); ?></head>
<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
<?php $this->load->view('sidebar'); ?>
<div class="content-wrapper">
  <section class="content-header">
    <h1><?= $page_title; ?></h1>
    <ol class="breadcrumb">
      <li><a href="<?= base_url('dashboard'); ?>"><i class="fa fa-dashboard"></i> Home</a></li>
      <li><a href="<?= base_url('operations/delivery_scheduling'); ?>">Delivery Scheduling</a></li>
      <li class="active">Driver Profile</li>
    </ol>
  </section>
  <section class="content">
    <div class="row">
      <!-- Driver Info -->
      <div class="col-md-4">
        <div class="box box-primary">
          <div class="box-body box-profile">
            <h3 class="profile-username text-center"><?= htmlspecialchars($driver->name ?? 'Unknown'); ?></h3>
            <p class="text-muted text-center"><?= ucfirst(str_replace('_', ' ', $driver->employment_type ?? 'Contract')); ?> Driver</p>
            <ul class="list-group list-group-unbordered">
              <li class="list-group-item"><b>Status</b>
                <span class="pull-right">
                  <?php $badgeMap = ['active'=>'success','on_leave'=>'warning','inactive'=>'default','suspended'=>'danger'];
                    $st = $driver->status ?? 'active';
                    echo '<span class="label label-'.($badgeMap[$st]??'default').'">'.ucfirst(str_replace('_',' ',$st)).'</span>';
                  ?>
                </span>
              </li>
              <li class="list-group-item"><b>Phone</b> <span class="pull-right"><?= htmlspecialchars($driver->phone ?: '-'); ?></span></li>
              <li class="list-group-item"><b>Email</b> <span class="pull-right"><?= htmlspecialchars($driver->email ?: '-'); ?></span></li>
              <li class="list-group-item"><b>Address</b> <span class="pull-right"><?= htmlspecialchars($driver->address ?: '-'); ?></span></li>
              <li class="list-group-item"><b>Hire Date</b> <span class="pull-right"><?= (!empty($driver->hire_date) && $driver->hire_date != '0000-00-00') ? show_date($driver->hire_date) : '-'; ?></span></li>
              <li class="list-group-item"><b>Emergency Contact</b>
                <span class="pull-right">
                  <?= htmlspecialchars(($driver->emergency_contact_name ?: '') . ($driver->emergency_contact_phone ? ' ('.$driver->emergency_contact_phone.')' : '')) ?: '-'; ?>
                </span>
              </li>
            </ul>
          </div>
        </div>

        <!-- Compliance -->
        <div class="box box-warning">
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-id-card-o"></i> Compliance</h3></div>
          <div class="box-body">
            <table class="table table-condensed">
              <tr><td><strong>NIN</strong></td><td><?= htmlspecialchars($driver->nin ?: '-'); ?></td></tr>
              <tr><td><strong>Driver's License</strong></td><td><?= htmlspecialchars($driver->driver_license ?: '-'); ?></td></tr>
              <tr><td><strong>License Expiry</strong></td>
                <td>
                  <?php
                    if (!empty($driver->license_expiry) && $driver->license_expiry != '0000-00-00') {
                        $exp = strtotime($driver->license_expiry);
                        $days = ceil(($exp - time()) / 86400);
                        $cls = $days < 30 ? 'text-danger' : ($days < 90 ? 'text-warning' : 'text-success');
                        echo '<span class="'.$cls.'">'.show_date($driver->license_expiry).' (' . ($days < 0 ? 'EXPIRED' : $days.' days left') . ')</span>';
                    } else { echo '-'; }
                  ?>
                </td>
              </tr>
            </table>
          </div>
        </div>
      </div>

      <!-- Vehicle & Stats -->
      <div class="col-md-8">
        <div class="row">
          <div class="col-md-3 col-sm-6">
            <div class="small-box bg-aqua">
              <div class="inner"><h3><?= $stats['total_routes']; ?></h3><p>Total Routes</p></div>
              <div class="icon"><i class="fa fa-truck"></i></div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="small-box bg-green">
              <div class="inner"><h3><?= $stats['completed']; ?></h3><p>Completed</p></div>
              <div class="icon"><i class="fa fa-check-circle"></i></div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="small-box bg-yellow">
              <div class="inner"><h3><?= $stats['total_stops']; ?></h3><p>Total Stops</p></div>
              <div class="icon"><i class="fa fa-map-marker"></i></div>
            </div>
          </div>
          <div class="col-md-3 col-sm-6">
            <div class="small-box bg-blue">
              <div class="inner"><h3><?= $stats['total_stops'] > 0 ? round(($stats['delivered_stops'] / $stats['total_stops']) * 100) : 0; ?>%</h3><p>Delivery Rate</p></div>
              <div class="icon"><i class="fa fa-percent"></i></div>
            </div>
          </div>
        </div>

        <div class="box box-info">
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-car"></i> Vehicle Details</h3></div>
          <div class="box-body">
            <div class="row">
              <div class="col-sm-3"><strong>Make / Model</strong><br><?= htmlspecialchars($driver->vehicle ?: '-'); ?></div>
              <div class="col-sm-3"><strong>Type</strong><br><?= ucfirst($driver->vehicle_type ?: '-'); ?></div>
              <div class="col-sm-3"><strong>Color</strong><br><?= htmlspecialchars($driver->vehicle_color ?: '-'); ?></div>
              <div class="col-sm-3"><strong>Plate Number</strong><br><?= htmlspecialchars($driver->license_plate ?: '-'); ?></div>
            </div>
          </div>
        </div>

        <!-- Route History -->
        <div class="box">
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-road"></i> Route History</h3></div>
          <div class="box-body table-responsive">
            <table class="table table-bordered table-striped">
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
                  <td><a href="<?= base_url('operations/delivery_schedule_view/'.$r->id); ?>" class="btn btn-xs btn-info"><i class="fa fa-eye"></i></a></td>
                </tr>
                <?php }} else { ?>
                <tr><td colspan="6" class="text-center text-muted">No routes assigned yet.</td></tr>
                <?php } ?>
              </tbody>
            </table>
          </div>
        </div>

        <?php if(!empty($driver->notes)){ ?>
        <div class="box box-default">
          <div class="box-header with-border"><h3 class="box-title"><i class="fa fa-sticky-note-o"></i> Notes</h3></div>
          <div class="box-body">
            <p><?= nl2br(htmlspecialchars($driver->notes)); ?></p>
          </div>
        </div>
        <?php } ?>
      </div>
    </div>
  </section>
</div>
<?php $this->load->view('footer'); ?>
</div>
<?php $this->load->view('comman/code_js.php'); ?>
</body>
</html>
